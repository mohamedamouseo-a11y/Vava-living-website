<?php
/**
 * Final QA corrections discovered after VAVA final-delivery review.
 *
 * This patch is intentionally surgical. It corrects only verified live-data
 * issues and does not replace unprovided approved consultation/product copy.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'VAVA_FINAL_QA_PATCH_VERSION' ) ) {
	define( 'VAVA_FINAL_QA_PATCH_VERSION', '1.22.58' );
}

/** Update a package/session in a saved Paths payload by UID. */
function vava_final_qa_update_session( array &$data, string $uid, callable $callback ): bool {
	if ( empty( $data['packages'] ) || ! is_array( $data['packages'] ) ) { return false; }
	foreach ( $data['packages'] as &$session ) {
		if ( ! is_array( $session ) || sanitize_key( (string) ( $session['uid'] ?? '' ) ) !== sanitize_key( $uid ) ) { continue; }
		$callback( $session );
		unset( $session );
		return true;
	}
	unset( $session );
	return false;
}

/** Normalize the requested brand casing in the About story. */
function vava_final_qa_fix_about_brand_casing(): void {
	$page_id = function_exists( 'vava_final_delivery_page_id' )
		? vava_final_delivery_page_id( 'page-templates/about-vava.php', 'vava_about_is_page' )
		: 0;
	if ( ! $page_id || ! function_exists( 'vava_about_meta_key' ) ) { return; }

	foreach ( array( 'ar', 'en' ) as $lang ) {
		$key = vava_about_meta_key( '_vava_about_story_content', $lang );
		if ( ! metadata_exists( 'post', $page_id, $key ) ) { continue; }
		$value = (string) get_post_meta( $page_id, $key, true );
		$fixed = preg_replace( '/\b(?:living\s+vava|vava\s+living)\b/iu', 'VAVA Living', $value );
		if ( is_string( $fixed ) && $fixed !== $value ) {
			update_post_meta( $page_id, $key, $fixed );
		}
	}
}

/** Correct the verified English/Arabic follow-up package metadata and English FAQ. */
function vava_final_qa_fix_paths(): void {
	$page_id = function_exists( 'vava_final_delivery_page_id' )
		? vava_final_delivery_page_id( 'page-templates/paths-vava.php', 'vava_paths_is_page' )
		: 0;
	if ( ! $page_id ) { return; }

	$ar = get_post_meta( $page_id, '_vava_paths_data_ar', true );
	$en = get_post_meta( $page_id, '_vava_paths_data_en', true );
	$ar = is_array( $ar ) ? $ar : array();
	$en = is_array( $en ) ? $en : array();

	// Follow-up Package: identify robustly instead of depending on one saved UID.
	// The live record may keep a different UID after earlier admin/sync operations.
	$update_followup_package = static function ( array &$data, string $lang ): bool {
		if ( empty( $data['packages'] ) || ! is_array( $data['packages'] ) ) { return false; }

		foreach ( $data['packages'] as &$session ) {
			if ( ! is_array( $session ) ) { continue; }

			$uid      = sanitize_key( (string) ( $session['uid'] ?? '' ) );
			$category = sanitize_key( (string) ( $session['category'] ?? '' ) );
			$title    = trim( wp_strip_all_tags( (string) ( $session['title'] ?? '' ) ) );
			$price    = preg_replace( '/[^0-9.]/', '', (string) ( $session['price'] ?? '' ) );
			$booking  = trim( wp_strip_all_tags( (string) ( $session['booking_text'] ?? '' ) ) );

			$is_named_package = (bool) preg_match( '/(?:باقة\s*المتابعة|follow[\s-]*up\s*package)/iu', $title );
			$is_package_cta   = (bool) preg_match( '/(?:احجز\s*الباقة|book\s*(?:the\s*)?package)/iu', $booking );
			$is_target = 'session-7' === $uid
				|| ( 'followup' === $category && $is_named_package )
				|| ( 'followup' === $category && '790' === $price && $is_package_cta );

			if ( ! $is_target ) { continue; }

			$is_en = 'en' === $lang;
			$type_value = $is_en ? 'Follow-up package' : 'باقة متابعة';
			$type_label = $is_en ? 'Session type' : 'نوع الجلسة';

			$session['session_type'] = $type_value;
			$session['meta_1']       = $type_value;

			$basics = array_values( (array) ( $session['basics'] ?? array() ) );
			$type_row_found = false;
			foreach ( $basics as &$basic ) {
				if ( ! is_array( $basic ) ) { continue; }
				$key   = sanitize_key( (string) ( $basic['key'] ?? '' ) );
				$label = trim( (string) ( $basic['label'] ?? '' ) );
				if ( 'session_type' === $key || preg_match( '/(?:نوع\s*الجلسة|session\s*type)/iu', $label ) ) {
					$basic['key']   = 'session_type';
					$basic['label'] = $type_label;
					$basic['value'] = $type_value;
					$type_row_found = true;
				}
			}
			unset( $basic );

			if ( ! $type_row_found ) {
				array_unshift( $basics, array(
					'key'   => 'session_type',
					'label' => $type_label,
					'value' => $type_value,
					'icon'  => 'person',
				) );
			}
			$session['basics'] = $basics;
			$session['basics_initialized'] = 1;

			if ( $is_en ) {
				$session['audience_title'] = 'Ideal for You If You...';
				$session['outcomes_title'] = "What's Included";

				// Keep the already-correct order stable; swap only when the old live
				// reversed structure is still detected.
				$audience_text = wp_json_encode( (array) ( $session['audience'] ?? array() ) );
				$outcomes_text = wp_json_encode( (array) ( $session['outcomes'] ?? array() ) );
				$looks_reversed = false !== stripos( (string) $audience_text, 'Three follow-up consultations' )
					&& false !== stripos( (string) $outcomes_text, 'Prefer regular follow-up' );
				if ( $looks_reversed ) {
					$tmp = $session['audience'];
					$session['audience'] = $session['outcomes'];
					$session['outcomes'] = $tmp;
				}
			}

			unset( $session );
			return true;
		}
		unset( $session );
		return false;
	};

	$update_followup_package( $ar, 'ar' );
	$update_followup_package( $en, 'en' );

	// Keep the English service name and FAQ destination consistent with the
	// approved Arabic "جلسة استفسارات" service.
	vava_final_qa_update_session( $en, 'session-8', static function ( array &$session ): void {
		$session['title'] = 'Inquiry Session';
	} );

	if ( isset( $en['faq']['items'][0]['answer'] ) ) {
		$answer = (string) $en['faq']['items'][0]['answer'];
		$answer = preg_replace( '/\bQuick\s+consultations?\b/iu', 'Inquiry Session', $answer );
		$answer = preg_replace( '/\bGuided\s+Session\b/iu', 'Inquiry Session', (string) $answer );
		$en['faq']['items'][0]['answer'] = $answer;
	}

	// Remove only the unapproved 48-hour upgrade sentence discovered on live.
	if ( isset( $en['compare']['guidance_html'] ) ) {
		$guidance = (string) $en['compare']['guidance_html'];
		if ( preg_match( '/48\s*hours?|pay(?:ing)?\s+only\s+the\s+difference/iu', wp_strip_all_tags( $guidance ) ) ) {
			$en['compare']['guidance_html'] = '';
		}
	}
	if ( isset( $ar['compare']['guidance_html'] ) ) {
		$guidance = (string) $ar['compare']['guidance_html'];
		if ( preg_match( '/48\s*ساعة|دفع\s+الفرق/u', wp_strip_all_tags( $guidance ) ) ) {
			$ar['compare']['guidance_html'] = '';
		}
	}

	update_post_meta( $page_id, '_vava_paths_data_ar', $ar );
	update_post_meta( $page_id, '_vava_paths_data_en', $en );
}

/** Replace the accidental English "New field" label with the phone field label. */
function vava_final_qa_fix_contact_phone_label(): void {
	$page_id = function_exists( 'vava_contact_page_id' ) ? vava_contact_page_id() : 0;
	if ( ! $page_id ) { return; }

	$ar = get_post_meta( $page_id, '_vava_contact_text_ar', true );
	$en = get_post_meta( $page_id, '_vava_contact_text_en', true );
	if ( ! is_array( $ar ) || ! is_array( $en ) ) { return; }

	$ar_fields = (array) ( $ar['form']['field_texts'] ?? array() );
	$en_fields = (array) ( $en['form']['field_texts'] ?? array() );
	foreach ( $ar_fields as $field_id => $ar_field ) {
		if ( ! is_array( $ar_field ) || 'رقم الهاتف' !== trim( (string) ( $ar_field['label'] ?? '' ) ) ) { continue; }
		$current = is_array( $en_fields[ $field_id ] ?? null ) ? $en_fields[ $field_id ] : array();
		$label = trim( (string) ( $current['label'] ?? '' ) );
		if ( '' === $label || 'New field' === $label || 'Field' === $label ) {
			$current['label'] = 'Phone number';
		}
		if ( '' === trim( (string) ( $current['placeholder'] ?? '' ) ) ) {
			$current['placeholder'] = trim( (string) ( $ar_field['placeholder'] ?? '' ) );
		}
		$current['options'] = array_values( (array) ( $current['options'] ?? array() ) );
		$en_fields[ $field_id ] = $current;
	}
	$en['form']['field_texts'] = $en_fields;
	update_post_meta( $page_id, '_vava_contact_text_en', $en );
}

/** Fill only the verified blank English bundle title using existing product titles. */
function vava_final_qa_fix_selections_bundle_title(): void {
	$page_id = function_exists( 'vava_selections_page_id' ) ? vava_selections_page_id() : 0;
	if ( ! $page_id ) { return; }

	$products = get_post_meta( $page_id, '_vava_selections_products_en', true );
	if ( ! is_array( $products ) || empty( $products['digital'] ) || ! is_array( $products['digital'] ) ) { return; }

	$titles = array();
	foreach ( $products['digital'] as $product ) {
		if ( ! is_array( $product ) ) { continue; }
		$uid = sanitize_key( (string) ( $product['uid'] ?? '' ) );
		$titles[ $uid ] = trim( (string) ( $product['title'] ?? '' ) );
	}
	$harmony = (string) ( $titles['journey-back-to-harmony'] ?? '' );
	$balance = (string) ( $titles['journey-back-to-balance'] ?? '' );
	if ( '' === $harmony || '' === $balance ) { return; }

	foreach ( $products['digital'] as &$product ) {
		if ( ! is_array( $product ) || 'product-msvzz1pj-nhoobr' !== sanitize_key( (string) ( $product['uid'] ?? '' ) ) ) { continue; }
		if ( '' === trim( (string) ( $product['title'] ?? '' ) ) ) {
			// Derived from the two already-saved English product titles; this avoids
			// inventing new product naming while eliminating the blank live card.
			$product['title'] = 'Bundle: ' . $harmony . ' + ' . $balance;
		}
		break;
	}
	unset( $product );
	update_post_meta( $page_id, '_vava_selections_products_en', $products );
}

/** Run the QA patch once. */
function vava_final_qa_run_migration(): void {
	if ( get_option( 'vava_final_qa_patch_version' ) === VAVA_FINAL_QA_PATCH_VERSION ) { return; }
	if ( wp_installing() ) { return; }

	vava_final_qa_fix_about_brand_casing();
	vava_final_qa_fix_paths();
	vava_final_qa_fix_contact_phone_label();
	vava_final_qa_fix_selections_bundle_title();

	update_option( 'vava_final_qa_patch_version', VAVA_FINAL_QA_PATCH_VERSION, false );
}
add_action( 'init', 'vava_final_qa_run_migration', 90 );
