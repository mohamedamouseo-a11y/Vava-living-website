<?php
/**
 * Bilingual Paths VAVA page template, advanced settings, and renderer.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

function vava_paths_template_slug(): string {
	return 'page-templates/paths-vava.php';
}

function vava_paths_is_page( int $post_id ): bool {
	if ( $post_id <= 0 ) {
		return false;
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return false;
	}

	$template = trim( (string) get_post_meta( $post_id, '_wp_page_template', true ) );
	if ( vava_paths_template_slug() === $template || 'paths-vava.php' === basename( str_replace( '\\', '/', $template ) ) ) {
		return true;
	}

	$slug = sanitize_title( (string) $post->post_name );
	if ( in_array( $slug, array( 'paths-vava', 'vava-paths' ), true ) ) {
		return true;
	}

	// Final compatibility fallback for an existing VAVA Paths page whose template/slug was changed by migration.
	return metadata_exists( 'post', $post_id, '_vava_paths_data_ar' ) || metadata_exists( 'post', $post_id, '_vava_paths_data_en' );
}

/** Resolve the page being edited from any WordPress editor request. */
function vava_paths_admin_post_id(): int {
	global $post;
	if ( $post instanceof WP_Post ) {
		return (int) $post->ID;
	}
	if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	if ( isset( $_POST['post_ID'] ) ) {
		return absint( $_POST['post_ID'] );
	}
	return 0;
}

function vava_paths_title_defaults( array $defaults, int $post_id ): array {
	if ( vava_paths_is_page( $post_id ) ) {
		$defaults['ar'] = 'مسارات VAVA';
		$defaults['en'] = 'VAVA Paths';
	}
	return $defaults;
}
add_filter( 'vava_page_title_defaults', 'vava_paths_title_defaults', 10, 2 );

function vava_paths_defaults_all(): array {
	static $defaults = null;
	if ( is_array( $defaults ) ) {
		return $defaults;
	}
	$file = get_theme_file_path( 'assets/data/paths-vava-defaults.json' );
	if ( ! is_file( $file ) ) {
		$defaults = array( 'ar' => array(), 'en' => array() );
		return $defaults;
	}
	$decoded  = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$defaults = is_array( $decoded ) ? $decoded : array( 'ar' => array(), 'en' => array() );
	return $defaults;
}

function vava_paths_defaults( string $lang ): array {
	$all  = vava_paths_defaults_all();
	$lang = 'en' === $lang ? 'en' : 'ar';
	return isset( $all[ $lang ] ) && is_array( $all[ $lang ] ) ? $all[ $lang ] : array();
}

function vava_paths_meta_key( string $lang ): string {
	return '_vava_paths_data_' . ( 'en' === $lang ? 'en' : 'ar' );
}

function vava_paths_shared_setting_paths(): array {
	$paths = array(
		array( 'closing', 'button_1_url' ),
		array( 'closing', 'button_2_url' ),
		array( 'compare', 'guidance_session_uid' ),
	);
	for ( $pathway = 0; $pathway < 3; $pathway++ ) {
		$paths[] = array( 'pathways', $pathway, 'image_id' );
	}
	for ( $package = 0; $package < 8; $package++ ) {
		$paths[] = array( 'packages', $package, 'featured' );
		$paths[] = array( 'packages', $package, 'link_url' );
		$paths[] = array( 'packages', $package, 'category' );
	}
	for ( $plan = 0; $plan < 3; $plan++ ) {
		$paths[] = array( 'compare', 'plans', $plan, 'featured' );
		for ( $feature = 0; $feature < 6; $feature++ ) {
			$paths[] = array( 'compare', 'plans', $plan, 'features', $feature, 'visible' );
		}
	}
	return $paths;
}

function vava_paths_array_value( array $data, array $path, $fallback = null ) {
	$value = $data;
	foreach ( $path as $segment ) {
		if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
			return $fallback;
		}
		$value = $value[ $segment ];
	}
	return $value;
}

function vava_paths_array_set( array &$data, array $path, $value ): void {
	$cursor =& $data;
	foreach ( $path as $index => $segment ) {
		if ( $index === count( $path ) - 1 ) {
			$cursor[ $segment ] = $value;
			return;
		}
		if ( ! isset( $cursor[ $segment ] ) || ! is_array( $cursor[ $segment ] ) ) {
			$cursor[ $segment ] = array();
		}
		$cursor =& $cursor[ $segment ];
	}
}

/**
 * Merge saved Paths settings without restoring deleted repeater rows.
 * Numeric lists are replaced as complete lists; associative settings keep the
 * usual recursive fallback to defaults.
 */
function vava_paths_merge_saved_data( array $defaults, array $saved ): array {
	foreach ( $saved as $key => $value ) {
		if ( is_array( $value ) && isset( $defaults[ $key ] ) && is_array( $defaults[ $key ] ) ) {
			$is_list = array() === $value || array_keys( $value ) === range( 0, count( $value ) - 1 );
			$defaults[ $key ] = $is_list ? array_values( $value ) : vava_paths_merge_saved_data( $defaults[ $key ], $value );
		} else {
			$defaults[ $key ] = $value;
		}
	}
	return $defaults;
}

function vava_paths_apply_shared_settings( array $localized, array $canonical ): array {
	foreach ( vava_paths_shared_setting_paths() as $path ) {
		$value = vava_paths_array_value( $canonical, $path, null );
		if ( null !== $value ) {
			vava_paths_array_set( $localized, $path, $value );
		}
	}
	return $localized;
}

/** Normalize comparison packages to one source of truth for price and comparison-item availability. */
function vava_paths_normalize_comparison_data( array $data ): array {
	$data['compare'] = is_array( $data['compare'] ?? null ) ? $data['compare'] : array();
	unset(
		$data['compare']['button_text'],
		$data['compare']['button_target'],
		$data['compare']['button_page_id'],
		$data['compare']['whatsapp_number'],
		$data['compare']['whatsapp_message']
	);
	$data['compare']['guidance_session_uid'] = sanitize_key( (string) ( $data['compare']['guidance_session_uid'] ?? 'session-2' ) );
	if ( empty( $data['compare']['plans'] ) || ! is_array( $data['compare']['plans'] ) ) {
		return $data;
	}
	foreach ( $data['compare']['plans'] as &$plan ) {
		$plan = is_array( $plan ) ? $plan : array();
		unset( $plan['basics'], $plan['duration'] );
		$features = array();
		foreach ( array_values( (array) ( $plan['features'] ?? array() ) ) as $feature ) {
			$feature = is_array( $feature ) ? $feature : array();
			$feature['text']    = sanitize_text_field( (string) ( $feature['text'] ?? '' ) );
			$feature['value']   = sanitize_text_field( (string) ( $feature['value'] ?? '' ) );
			$feature['visible'] = ! isset( $feature['visible'] ) || ! empty( $feature['visible'] ) ? 1 : 0;
			// Keep legacy templates from hiding unavailable items; visible now stores availability status.
			$feature['enabled'] = 1;
			if ( '' === trim( $feature['text'] ) && '' === trim( $feature['value'] ) ) { continue; }
			$features[] = $feature;
		}
		$plan['features'] = $features;
		$plan['booking_enabled'] = ! isset( $plan['booking_enabled'] ) ? 1 : (int) ! empty( $plan['booking_enabled'] );
	}
	unset( $plan );
	return $data;
}

/** Resolve a stable semantic key for a session basic-information label. */
function vava_paths_session_basic_key( string $label ): string {
	$label = trim( wp_strip_all_tags( $label ) );
	if ( '' === $label ) { return 'custom'; }
	if ( preg_match( '/(?:^|\s)(?:المدة|مدة الجلسة|duration)(?:\s|$)/iu', $label ) ) { return 'duration'; }
	if ( preg_match( '/(?:نوع\s*الجلسة|session\s*type)/iu', $label ) ) { return 'session_type'; }
	if ( preg_match( '/(?:المكان|الموقع|location|venue)/iu', $label ) ) { return 'location'; }
	if ( preg_match( '/(?:السعر|الاستثمار|price|investment)/iu', $label ) ) { return 'price'; }
	return 'custom';
}

/** Return the icon key used by a session basic-information item. */
function vava_paths_session_basic_icon( string $label, string $icon = '' ): string {
	$allowed = array( 'clock', 'person', 'location', 'price', 'calendar', 'video', 'leaf', 'info' );
	$icon = sanitize_key( $icon );
	if ( in_array( $icon, $allowed, true ) ) { return $icon; }
	$key = vava_paths_session_basic_key( $label );
	return array(
		'duration'     => 'clock',
		'session_type' => 'person',
		'location'     => 'location',
		'price'        => 'price',
	)[ $key ] ?? 'info';
}

/** Inline icon used by both the admin preview and the session details page. */
function vava_paths_session_basic_icon_svg( string $icon ): string {
	$icon = vava_paths_session_basic_icon( '', $icon );
	$paths = array(
		'clock'    => '<circle cx="12" cy="12" r="8.25"/><path d="M12 7.5v5l3.2 1.9"/>',
		'person'   => '<circle cx="12" cy="8" r="3.2"/><path d="M5.8 19c.8-3.7 2.9-5.5 6.2-5.5s5.4 1.8 6.2 5.5"/>',
		'location' => '<path d="M12 20s6-5.1 6-11a6 6 0 1 0-12 0c0 5.9 6 11 6 11Z"/><circle cx="12" cy="9" r="2"/>',
		'price'    => '<path d="M4.5 7.5V4.8h6.4l8.6 8.6-6.1 6.1-8.9-8.9V7.5Z"/><circle cx="8.2" cy="8.2" r="1.2"/>',
		'calendar' => '<rect x="4" y="5.5" width="16" height="14" rx="2"/><path d="M8 3.8v3.4m8-3.4v3.4M4 9.5h16"/>',
		'video'    => '<rect x="3.5" y="6" width="12" height="12" rx="2"/><path d="m15.5 10 5-3v10l-5-3"/>',
		'leaf'     => '<path d="M19 4.5C12 4.8 7.6 8.1 7.2 13.2c-.2 2.7 1.7 5 4.5 5.1 5.2.2 7.4-5.3 7.3-13.8Z"/><path d="M5 20c2.2-4.9 5.7-8.6 10.4-11.1"/>',
		'info'     => '<circle cx="12" cy="12" r="8.25"/><path d="M12 10.7v5.1M12 8h.01"/>',
	);
	return '<svg viewBox="0 0 24 24" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round">' . $paths[ $icon ] . '</g></svg>';
}

/**
 * Normalize session basic information and keep the legacy detail fields in sync.
 * The repeater is the canonical editing source; legacy keys remain populated for
 * the existing cards, detail pages and booking templates.
 */
function vava_paths_normalize_session_basic_data( array $data, string $lang = 'ar' ): array {
	$lang = 'en' === $lang ? 'en' : 'ar';
	if ( empty( $data['packages'] ) || ! is_array( $data['packages'] ) ) { return $data; }
	foreach ( $data['packages'] as &$session ) {
		$session = is_array( $session ) ? $session : array();

		// These fields are no longer part of the session card or details page.
		unset( $session['badge'], $session['description'] );

		$basics_initialized = ! empty( $session['basics_initialized'] );
		$basics_source      = array_values( (array) ( $session['basics'] ?? array() ) );

		// One-time compatibility for old records created before the basic-info builder.
		// Once initialized, an intentionally empty/deleted list must remain empty.
		if ( ! $basics_initialized && ! $basics_source ) {
			$labels = array(
				'session_type' => array( 'ar' => 'نوع الجلسة', 'en' => 'Session type', 'icon' => 'person' ),
				'location'     => array( 'ar' => 'المكان', 'en' => 'Location', 'icon' => 'location' ),
				'price'        => array( 'ar' => 'السعر', 'en' => 'Price', 'icon' => 'price' ),
			);
			foreach ( $labels as $legacy_key => $meta ) {
				$value = 'price' === $legacy_key
					? trim( (string) ( $session['price'] ?? '' ) . ' ' . (string) ( $session['currency'] ?? '' ) )
					: (string) ( $session[ $legacy_key ] ?? '' );
				if ( '' !== trim( $value ) ) {
					$basics_source[] = array( 'key' => $legacy_key, 'label' => $meta[ $lang ], 'value' => $value, 'icon' => $meta['icon'] );
				}
			}
		}

		$basics        = array();
		$seen_semantic = array();
		foreach ( $basics_source as $basic ) {
			$basic = is_array( $basic ) ? $basic : array();
			$label = sanitize_text_field( (string) ( $basic['label'] ?? '' ) );
			$value = sanitize_text_field( (string) ( $basic['value'] ?? '' ) );
			if ( '' === trim( $label ) && '' === trim( $value ) ) { continue; }

			$key = sanitize_key( (string) ( $basic['key'] ?? '' ) );
			if ( ! in_array( $key, array( 'duration', 'session_type', 'location', 'price', 'custom' ), true ) ) {
				$key = vava_paths_session_basic_key( $label );
			}
			if ( 'custom' === $key ) {
				$detected = vava_paths_session_basic_key( $label );
				if ( 'custom' !== $detected ) { $key = $detected; }
			}
			if ( 'duration' === $key ) { continue; }

			// Type, location and price are singleton facts. Keep the first
			// row and discard any duplicate created by the old recursive default merge.
			if ( 'custom' !== $key ) {
				if ( isset( $seen_semantic[ $key ] ) ) { continue; }
				$seen_semantic[ $key ] = true;
			}

			$basic['label'] = $label;
			$basic['value'] = $value;
			$basic['key']   = $key;
			$basic['icon']  = vava_paths_session_basic_icon( $label, (string) ( $basic['icon'] ?? '' ) );
			$basics[]       = $basic;

			if ( 'session_type' === $key ) { $session['session_type'] = $value; }
			if ( 'location' === $key ) { $session['location'] = $value; }
			if ( 'price' === $key && '' !== $value ) {
				$latin = strtr( $value, array( '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9','۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9' ) );
				if ( preg_match( '/([0-9][0-9,.\s]*)/u', $latin, $match ) ) {
					$session['price'] = preg_replace( '/\s+/u', '', $match[1] );
					$currency = trim( str_replace( $match[0], '', $latin ) );
					$session['currency'] = '' !== $currency ? $currency : (string) ( $session['currency'] ?? '' );
				}
			}
		}

		// Deleted semantic rows must not survive through hidden legacy values.
		if ( $basics_initialized ) {
			if ( empty( $seen_semantic['session_type'] ) ) { $session['session_type'] = ''; }
			if ( empty( $seen_semantic['location'] ) ) { $session['location'] = ''; }
			if ( empty( $seen_semantic['price'] ) ) { $session['price'] = ''; $session['currency'] = ''; }
		}

		$session['booking_enabled']   = ! isset( $session['booking_enabled'] ) ? 1 : (int) ! empty( $session['booking_enabled'] );
		$session['basics_initialized'] = 1;
		$card_values = array_values( array_unique( array_filter( array_map( static function ( $basic ) {
			return isset( $basic['key'] ) && 'price' === $basic['key'] ? '' : trim( (string) ( $basic['value'] ?? '' ) );
		}, $basics ) ) ) );
		$session['meta_1'] = (string) ( $card_values[0] ?? '' );
		$session['meta_2'] = (string) ( $card_values[1] ?? '' );
		$session['meta_3'] = (string) ( $card_values[2] ?? '' );
		$session['basics'] = $basics;
	}
	unset( $session );
	return $data;
}

/** Resolve the stable display category for an individual consultation session. */
function vava_paths_session_category( array $session ): string {
	$category = sanitize_key( (string) ( $session['category'] ?? '' ) );
	if ( in_array( $category, array( 'quick', 'followup', 'comprehensive' ), true ) ) {
		return $category;
	}
	$uid = sanitize_key( (string) ( $session['uid'] ?? '' ) );
	if ( 'session-8' === $uid ) { return 'quick'; }
	if ( in_array( $uid, array( 'session-5', 'session-6', 'session-7' ), true ) ) { return 'followup'; }
	if ( in_array( $uid, array( 'session-1', 'session-2', 'session-3', 'session-4' ), true ) ) { return 'comprehensive'; }
	$haystack = strtolower( wp_strip_all_tags( implode( ' ', array(
		(string) ( $session['title'] ?? '' ),
		(string) ( $session['meta_1'] ?? '' ),
		(string) ( $session['session_type'] ?? '' ),
	) ) ) );
	if ( preg_match( '/(?:استشارة\s*سريعة|quick\s*consult)/iu', $haystack ) ) { return 'quick'; }
	if ( preg_match( '/(?:متابعة|follow[ -]?up)/iu', $haystack ) ) { return 'followup'; }
	return 'comprehensive';
}

/** Customer-facing duration label defined by the consultation category. */
function vava_paths_session_category_duration( string $category, string $lang ): string {
	$is_en = 'en' === $lang;
	return array(
		'quick'         => $is_en ? '15–20 minutes' : '15–20 دقيقة',
		'followup'      => $is_en ? '30 minutes' : '30 دقيقة',
		'comprehensive' => $is_en ? '90 minutes' : '90 دقيقة',
	)[ $category ] ?? ( $is_en ? '90 minutes' : '90 دقيقة' );
}

/** Operational duration used by availability and overlap protection. */
function vava_paths_session_category_booking_minutes( string $category ): int {
	return array(
		'quick'         => 20,
		'followup'      => 30,
		'comprehensive' => 90,
	)[ $category ] ?? 90;
}

/** Identify the free discovery session without depending on a translated title. */
function vava_paths_is_discovery_session( array $session ): bool {
	$uid = sanitize_key( (string) ( $session['uid'] ?? '' ) );
	if ( in_array( $uid, array( 'b5ec91a1-3987-4df6-8111-a0c8c5ea409d', 'discovery-session', 'free-discovery-session' ), true ) ) {
		return true;
	}
	$title = strtolower( wp_strip_all_tags( (string) ( $session['title'] ?? '' ) ) );
	return (bool) preg_match( '/(?:جلسة\s*استكشافية|discovery\s*session)/iu', $title );
}

/** Customer-facing duration at service level. Discovery is intentionally shorter. */
function vava_paths_session_display_duration( array $session, string $lang = 'ar' ): string {
	$lang = 'en' === $lang ? 'en' : 'ar';
	if ( vava_paths_is_discovery_session( $session ) ) {
		return 'en' === $lang ? '15 minutes' : '15 دقيقة';
	}
	return vava_paths_session_category_duration( vava_paths_session_category( $session ), $lang );
}

/** Operational duration at service level. */
function vava_paths_session_booking_minutes( array $session ): int {
	if ( vava_paths_is_discovery_session( $session ) ) {
		return 15;
	}
	return vava_paths_session_category_booking_minutes( vava_paths_session_category( $session ) );
}

/** Keep every session synchronized with its category and remove the duplicated duration tile. */
function vava_paths_normalize_session_categories( array $data, string $lang = 'ar' ): array {
	$lang = 'en' === $lang ? 'en' : 'ar';
	if ( empty( $data['packages'] ) || ! is_array( $data['packages'] ) ) { return $data; }
	foreach ( $data['packages'] as &$session ) {
		$session = is_array( $session ) ? $session : array();
		$category = vava_paths_session_category( $session );
		$session['category']         = $category;
		$session['duration']         = vava_paths_session_display_duration( $session, $lang );
		$session['booking_duration'] = vava_paths_session_booking_minutes( $session );
		$session['basics'] = array_values( array_filter( (array) ( $session['basics'] ?? array() ), static function ( $basic ): bool {
			$basic = is_array( $basic ) ? $basic : array();
			$key   = sanitize_key( (string) ( $basic['key'] ?? '' ) );
			return 'duration' !== $key && 'duration' !== vava_paths_session_basic_key( (string) ( $basic['label'] ?? '' ) );
		} ) );
	}
	unset( $session );
	return $data;
}

/** Group enabled sessions by their fixed consultation category. */
function vava_paths_group_sessions( array $packages ): array {
	$groups = array( 'quick' => array(), 'followup' => array(), 'comprehensive' => array() );
	foreach ( $packages as $package ) {
		$package = is_array( $package ) ? $package : array();
		$category = vava_paths_session_category( $package );
		$groups[ $category ][] = $package;
	}
	return $groups;
}

/** Find the session details page connected to a comparison card. */
function vava_paths_comparison_session( array $plan, array $packages ): array {
	$session_uid = sanitize_key( (string) ( $plan['session_uid'] ?? '' ) );
	if ( $session_uid ) {
		foreach ( $packages as $package ) {
			if ( $session_uid === sanitize_key( (string) ( $package['uid'] ?? '' ) ) ) { return (array) $package; }
		}
	}
	$title = strtolower( wp_strip_all_tags( (string) ( $plan['title'] ?? '' ) ) );
	$rules = array(
		'session-3' => '/(?:خريطة\s*التوازن|balance\s*map)/iu',
		'session-1' => '/(?:رحلة\s*التوازن|balance\s*journey)/iu',
		'session-4' => '/(?:التشافي\s*العميق|deep\s*healing)/iu',
		'session-2' => '/(?:شاملة\s*مفردة|single\s*comprehensive)/iu',
	);
	foreach ( $rules as $uid => $pattern ) {
		if ( ! preg_match( $pattern, $title ) ) { continue; }
		foreach ( $packages as $package ) {
			if ( $uid === sanitize_key( (string) ( $package['uid'] ?? '' ) ) ) { return (array) $package; }
		}
	}
	return array();
}

/** Find the session selected for the guidance message below package comparison cards. */
function vava_paths_comparison_guidance_session( array $compare, array $packages ): array {
	$selected_uid = sanitize_key( (string) ( $compare['guidance_session_uid'] ?? 'session-2' ) );
	foreach ( $packages as $package ) {
		if ( $selected_uid && $selected_uid === sanitize_key( (string) ( $package['uid'] ?? '' ) ) ) {
			return (array) $package;
		}
	}
	foreach ( $packages as $package ) {
		if ( 'session-2' === sanitize_key( (string) ( $package['uid'] ?? '' ) ) ) {
			return (array) $package;
		}
	}
	return $packages ? (array) reset( $packages ) : array();
}

/** Resolve a session details URL even before the stored link has been refreshed. */
function vava_paths_session_details_url( array $session, string $lang ): string {
	$stored = trim( (string) ( $session['link_url'] ?? '' ) );
	if ( '' !== $stored ) {
		return vava_paths_resolve_url( $stored );
	}
	$uid = sanitize_key( (string) ( $session['uid'] ?? '' ) );
	if ( $uid ) {
		$posts = get_posts( array(
			'post_type'      => 'vava_session',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_vava_session_uid',
			'meta_value'     => $uid,
		) );
		if ( ! empty( $posts[0] ) ) {
			return add_query_arg( 'vava_lang', 'en' === $lang ? 'en' : 'ar', get_permalink( (int) $posts[0] ) );
		}
	}
	return '';
}

/** Render the comparison guidance message with one admin-selected session link. */
function vava_paths_comparison_guidance_html( array $compare, array $packages, string $lang ): string {
	$message = trim( (string) ( $compare['guidance_html'] ?? '' ) );
	if ( '' === $message ) { return ''; }
	$session = vava_paths_comparison_guidance_session( $compare, $packages );
	$url     = $session ? vava_paths_session_details_url( $session, $lang ) : '';
	$title   = trim( (string) ( $session['title'] ?? '' ) );
	if ( '' === $url || '' === $title ) { return wp_kses_post( $message ); }

	$link = '<a class="vava-paths-guidance-session-link" href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a>';
	if ( false !== strpos( $message, '{session}' ) ) {
		return wp_kses_post( str_replace( '{session}', $link, $message ) );
	}
	$replaced = 0;
	$message  = preg_replace( '/<a\b[^>]*>.*?<\/a>/isu', $link, $message, 1, $replaced );
	if ( $replaced ) { return wp_kses_post( (string) $message ); }
	$message = preg_replace( '/' . preg_quote( $title, '/' ) . '/u', $link, $message, 1, $replaced );
	if ( $replaced ) { return wp_kses_post( (string) $message ); }
	return wp_kses_post( $message . ' ' . $link );
}

/** Keep old package-button labels compatible with the approved booking wording. */
function vava_paths_comparison_booking_label( array $plan, string $lang ): string {
	unset( $plan );
	return 'en' === $lang ? 'Book package' : 'حجز الباقة';
}

function vava_paths_data( int $post_id, string $lang ): array {
	$lang              = 'en' === $lang ? 'en' : 'ar';
	$defaults          = vava_paths_defaults( $lang );
	$saved             = get_post_meta( $post_id, vava_paths_meta_key( $lang ), true );
	$localized         = is_array( $saved ) ? vava_paths_merge_saved_data( $defaults, $saved ) : $defaults;
	$canonical_saved   = get_post_meta( $post_id, vava_paths_meta_key( 'ar' ), true );
	$english_saved     = get_post_meta( $post_id, vava_paths_meta_key( 'en' ), true );
	$canonical_default = vava_paths_defaults( 'ar' );
	if ( is_array( $canonical_saved ) ) {
		$canonical = vava_paths_merge_saved_data( $canonical_default, $canonical_saved );
	} elseif ( is_array( $english_saved ) ) {
		$canonical = vava_paths_merge_saved_data( vava_paths_defaults( 'en' ), $english_saved );
	} else {
		$canonical = $canonical_default;
	}
	$localized = vava_paths_apply_shared_settings( $localized, $canonical );
	$localized = vava_paths_normalize_session_categories( $localized, $lang );
	$localized = vava_paths_normalize_session_basic_data( $localized, $lang );
	$saved_content = is_array( $saved ) ? trim( (string) vava_paths_array_value( $saved, array( 'hero', 'content' ), '' ) ) : '';
	if ( '' === $saved_content ) {
		$parts = array_filter( array_map( 'trim', array(
			(string) vava_paths_array_value( $localized, array( 'hero', 'lead_1' ), '' ),
			(string) vava_paths_array_value( $localized, array( 'hero', 'lead_2' ), '' ),
			(string) vava_paths_array_value( $localized, array( 'hero', 'note' ), '' ),
		) ) );
		$localized['hero']['content'] = implode( "\n\n", $parts );
	}
	return vava_paths_normalize_comparison_data( $localized );
}

/** Return the single multiline hero body, with legacy-field fallback. */
function vava_paths_hero_content( array $hero ): string {
	$content = trim( (string) ( $hero['content'] ?? '' ) );
	if ( '' !== $content ) { return $content; }
	$parts = array_filter( array_map( 'trim', array( (string) ( $hero['lead_1'] ?? '' ), (string) ( $hero['lead_2'] ?? '' ), (string) ( $hero['note'] ?? '' ) ) ) );
	return implode( "\n\n", $parts );
}

function vava_paths_sections( string $lang ): array {
	if ( 'en' === $lang ) {
		return array(
			'hero'       => 'Hero',
			'packages'   => 'Consultations',
			'questions'  => 'Frequently asked questions',
			'comparison' => 'Package comparison',
			'future'     => 'Upcoming pathways',
			'faq'        => 'Frequently asked questions',
			'closing'    => 'Closing invitation',
		);
	}
	return array(
		'hero'       => 'الهيرو',
		'packages'   => 'الاستشارات',
		'questions'  => 'الأسئلة الشائعة',
		'comparison' => 'مقارنة الباقات',
		'future'     => 'المسارات القادمة',
		'faq'        => 'الأسئلة الشائعة',
		'closing'    => 'الدعوة الختامية',
	);
}

function vava_paths_section_icon( string $section ): string {
	$icons = array(
		'hero'       => '<svg viewBox="0 0 24 24"><path d="M4 19V5h16v14H4Z"/><path d="m7 15 3-3 2 2 3-4 2 3"/></svg>',
		'packages'   => '<svg viewBox="0 0 24 24"><path d="M5 4h14v16H5z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>',
		'comparison' => '<svg viewBox="0 0 24 24"><path d="M7 4v16M17 4v16M4 8h6M14 12h6M4 16h6"/></svg>',
		'future'     => '<svg viewBox="0 0 24 24"><path d="M12 3v18M5 8h14M7 16h10"/></svg>',
		'faq'        => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.7 9a2.5 2.5 0 0 1 4.8 1c0 2-2.5 2-2.5 4M12 17h.01"/></svg>',
		'closing'    => '<svg viewBox="0 0 24 24"><path d="M4 12h16M14 6l6 6-6 6"/></svg>',
	);
	return $icons[ $section ] ?? '';
}

/** VAVA_PATHS_STAGE_ICON_CLEANUP_V1: connected-nodes icon for the guided stages. */
function vava_paths_stage_icon(): string {
	return '<svg class="vava-paths-stage-icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M4 12h16"/><circle cx="4" cy="12" r="2.25"/><circle cx="12" cy="12" r="2.25"/><circle cx="20" cy="12" r="2.25"/></svg>';
}

function vava_paths_get( array $data, array $path, $fallback = '' ) {
	$value = $data;
	foreach ( $path as $segment ) {
		if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
			return $fallback;
		}
		$value = $value[ $segment ];
	}
	return $value;
}

function vava_paths_resolve_url( string $url ): string {
	$url = trim( $url );
	if ( '' === $url || str_starts_with( $url, '#' ) || preg_match( '#^(?:mailto:|tel:)#i', $url ) ) {
		return $url;
	}
	if ( preg_match( '#^https?://#i', $url ) ) {
		$resolved = function_exists( 'vava_normalize_internal_url' ) ? vava_normalize_internal_url( $url ) : $url;
		$home     = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
		$target   = wp_parse_url( $resolved, PHP_URL_HOST );
		return $home && $target && strtolower( (string) $home ) === strtolower( (string) $target ) ? vava_language_url( vava_current_language(), $resolved ) : $resolved;
	}
	$fragment = wp_parse_url( $url, PHP_URL_FRAGMENT );
	$path     = (string) wp_parse_url( $url, PHP_URL_PATH );
	$slug     = basename( $path, '.html' );
	$slug     = preg_replace( '/-en$/', '', $slug );
	if ( in_array( $slug, array( 'index', '' ), true ) ) {
		$resolved = home_url( '/' );
	} else {
		$resolved = vava_page_url( $slug );
	}
	$resolved = vava_language_url( vava_current_language(), $resolved );
	return $fragment ? $resolved . '#' . $fragment : $resolved;
}

function vava_paths_compare_icon( int $index ): string {
	$paths = array(
		'<path d="M12 19c0-3.5 1.7-5.9 4.6-7.8M12 19c0-3.5-1.7-5.9-4.6-7.8M12 19v-6.2M7.4 7.6c1.2-1.6 2.8-2.4 4.6-2.6 1.8.2 3.4 1 4.6 2.6-.5 2.2-2 3.9-4.6 5-2.6-1.1-4.1-2.8-4.6-5Z"/>',
		'<path d="M12 4.5c1.8 2.8 3.1 4.7 3.9 5.6 1.3 1.4 2.8 2.3 4.6 2.9-1.8.6-3.3 1.5-4.6 2.9-.8.9-2.1 2.8-3.9 5.6-1.8-2.8-3.1-4.7-3.9-5.6-1.3-1.4-2.8-2.3-4.6-2.9 1.8-.6 3.3-1.5 4.6-2.9.8-.9 2.1-2.8 3.9-5.6Z"/>',
		'<path d="M12 21V7M12 7c0-2.2 1.8-4 4-4M12 7c0-2.2-1.8-4-4-4M12 11c1.4-1.7 3.2-2.5 5.5-2.5M12 11c-1.4-1.7-3.2-2.5-5.5-2.5M8.8 21c0-2.5 1.4-4.5 3.2-5.5M15.2 21c0-2.5-1.4-4.5-3.2-5.5"/>',
	);
	$path = $paths[ $index ] ?? $paths[0];
	return '<svg fill="none" viewBox="0 0 24 24"><g stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6">' . $path . '</g></svg>';
}

function vava_paths_compare_final_url( array $compare, string $lang ): string {
	$target = (string) ( $compare['button_target'] ?? 'contact' );
	if ( 'whatsapp' === $target ) {
		$number  = preg_replace( '/\D+/', '', (string) ( $compare['whatsapp_number'] ?? '' ) );
		$message = trim( wp_strip_all_tags( (string) ( $compare['whatsapp_message'] ?? '' ) ) );
		if ( $number ) {
			return 'https://wa.me/' . $number . ( $message ? '?text=' . rawurlencode( $message ) : '' );
		}
	}
	$page_id = absint( $compare['button_page_id'] ?? 0 );
	if ( $page_id && 'page' === get_post_type( $page_id ) ) {
		return vava_language_url( $lang, (string) get_permalink( $page_id ) );
	}
	$contact_pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_key'       => '_wp_page_template',
		'meta_value'     => 'page-templates/contact-vava.php',
		'fields'         => 'ids',
	) );
	if ( $contact_pages ) {
		return vava_language_url( $lang, (string) get_permalink( (int) $contact_pages[0] ) );
	}
	return vava_language_url( $lang, home_url( '/' ) );
}

function vava_paths_render_frontend( int $post_id, string $lang ): void {
	$lang         = 'en' === $lang ? 'en' : 'ar';
	$is_en        = 'en' === $lang;
	$data         = vava_paths_data( $post_id, $lang );
	$hero         = $data['hero'] ?? array();
	$consultation = $data['consultation'] ?? array();
	$packages     = isset( $data['packages'] ) && is_array( $data['packages'] ) ? array_values( array_filter( $data['packages'], static fn( $item ) => ! isset( $item['enabled'] ) || ! empty( $item['enabled'] ) ) ) : array();
	$compare      = $data['compare'] ?? array();
	$future       = isset( $data['future'] ) && is_array( $data['future'] ) ? array_values( $data['future'] ) : array();
	$pathways     = isset( $data['pathways'] ) && is_array( $data['pathways'] ) ? array_values( $data['pathways'] ) : array();
	$faq          = $data['faq'] ?? array();
	$image_id     = absint( get_post_meta( $post_id, '_vava_paths_hero_image_id', true ) );
	$image_url    = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
	if ( $image_url ) {
		$image_url .= vava_paths_image_cache_bust( $image_id );
	}
	if ( ! $image_url ) {
		$image_url = get_theme_file_uri( 'assets/images/paths-hero.webp' );
	}

	$labels = $is_en ? array(
		'stage'              => 'Stage',
		'paths_title'        => 'VAVA Paths',
		'paths_intro'        => 'Many paths, one shared vision for a more conscious and harmonious life.',
		'individual'         => 'Individual consultations',
		'programs'           => 'Programs',
		'workshops'          => 'Workshops',
		'choose_path'        => 'Choose this path',
		'coming_soon'        => 'Coming soon',
		'recommended'        => 'Recommended',
		'journey_hint'       => '',
		'back_paths'         => 'Back to VAVA Paths',
		'view_details'       => 'View details',
		'comparison_title'   => 'Compare packages',
		'comparison_intro'   => 'Compare the package cards and choose the option closest to your needs.',
		'restart'            => 'Start over',
	) : array(
		'stage'              => 'المرحلة',
		'paths_title'        => 'مسارات VAVA',
		'paths_intro'        => 'تتعدد الطرق، وتلتقي في رؤية واحدة لحياة أكثر وعيًا وتناغمًا.',
		'individual'         => 'الاستشارات الفردية',
		'programs'           => 'البرامج',
		'workshops'          => 'الورش',
		'choose_path'        => 'اختر هذا المسار',
		'coming_soon'        => 'قريبًا',
		'recommended'        => 'الموصى به',
		'journey_hint'       => '',
		'back_paths'         => 'العودة إلى مسارات VAVA',
		'view_details'       => 'عرض التفاصيل',
		'comparison_title'   => 'مقارنة الباقات',
		'comparison_intro'   => 'قارن الباقات من خلال الكروت واختر الخيار الأقرب إلى احتياجاتك.',
		'restart'            => 'البدء من جديد',
	);

	$program_title       = (string) ( $future[0]['title'] ?? $labels['programs'] );
	$program_description = (string) ( $future[0]['description'] ?? '' );
	$workshop_description = (string) ( $future[1]['description'] ?? '' );
	$faq_items           = array_values( (array) ( $faq['items'] ?? array() ) );
	$session_groups      = vava_paths_group_sessions( $packages );
	$category_definitions = $is_en ? array(
		'quick' => array( 'title' => 'Quick consultations', 'duration' => '15–20 minutes', 'intro' => 'A focused consultation for a clear and direct starting point.' ),
		'followup' => array( 'title' => 'Follow-up sessions', 'duration' => '30 minutes', 'intro' => 'Focused follow-up options to review progress and adjust the next steps.' ),
		'comprehensive' => array( 'title' => 'Comprehensive sessions', 'duration' => '90 minutes', 'intro' => 'Deep sessions and packages designed for integrated, longer-lasting support.' ),
	) : array(
		'quick' => array( 'title' => 'استشارات سريعة', 'duration' => '15–20 دقيقة', 'intro' => 'استشارة مركزة لبداية واضحة ومباشرة.' ),
		'followup' => array( 'title' => 'جلسات متابعة', 'duration' => '30 دقيقة', 'intro' => 'خيارات متابعة مركزة لمراجعة التقدم وتعديل الخطوات التالية.' ),
		'comprehensive' => array( 'title' => 'جلسات شاملة', 'duration' => '90 دقيقة', 'intro' => 'جلسات وباقات عميقة لدعم متكامل وتغيير أكثر استدامة.' ),
	);
	?>
	<span class="blob sage"></span><span class="blob cream"></span><span class="leaf-line vava-inline-paths-vava-1"></span>
	<section class="section vava-paths-hero-section">
		<div class="container hero-grid">
			<div class="hero-copy">
				<div class="eyebrow"><?php echo esc_html( (string) ( $hero['eyebrow'] ?? '' ) ); ?></div>
				<h1><?php echo esc_html( (string) ( $hero['title'] ?? '' ) ); ?></h1>
				<div class="vava-paths-hero-body"><?php echo vava_richtext_output( vava_paths_hero_content( $hero ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			</div>
			<div class="visual-card vava-inline-paths-vava-2" style="background-image:url('<?php echo esc_url( $image_url ); ?>')"></div>
		</div>
	</section>

	<section class="vava-paths-journey" data-paths-journey data-page-id="<?php echo esc_attr( (string) $post_id ); ?>" data-lang="<?php echo esc_attr( $lang ); ?>">
		<div class="vava-paths-journey-decoration vava-paths-journey-decoration-coral" aria-hidden="true"></div>
		<div class="vava-paths-journey-decoration vava-paths-journey-decoration-sage" aria-hidden="true"></div>

		<div class="vava-paths-stage is-active" data-paths-stage="1" aria-hidden="false">
			<div class="container vava-paths-stage-container">
				<header class="vava-paths-stage-heading">
					<span class="vava-paths-stage-pill"><?php echo esc_html( $labels['stage'] . ' 1' ); ?><?php echo vava_paths_stage_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h2><?php echo esc_html( $labels['paths_title'] ); ?></h2>
					<span class="vava-paths-heading-divider" aria-hidden="true"><i></i></span>
					<p><?php echo esc_html( $labels['paths_intro'] ); ?></p>
				</header>
				<div class="vava-paths-entry-grid">
					<?php
					$default_paths = array(
						array( 'uid' => 'individual', 'enabled' => true, 'featured' => true, 'badge' => $labels['recommended'], 'title' => $labels['individual'], 'description' => (string) ( $consultation['note'] ?? '' ), 'button_text' => $labels['choose_path'], 'status' => 'active' ),
						array( 'uid' => 'programs', 'enabled' => true, 'featured' => false, 'badge' => $labels['coming_soon'], 'title' => $program_title ?: $labels['programs'], 'description' => $program_description, 'button_text' => $labels['coming_soon'], 'status' => 'coming' ),
						array( 'uid' => 'workshops', 'enabled' => true, 'featured' => false, 'badge' => $labels['coming_soon'], 'title' => $labels['workshops'], 'description' => $workshop_description, 'button_text' => $labels['coming_soon'], 'status' => 'coming' ),
					);
					$render_paths = $pathways ?: $default_paths;
					foreach ( $render_paths as $path_index => $pathway ) : if ( isset( $pathway['enabled'] ) && empty( $pathway['enabled'] ) ) { continue; } $active_path = 'active' === (string) ( $pathway['status'] ?? '' ) || 'individual' === (string) ( $pathway['uid'] ?? '' ); ?>
					<article class="vava-paths-entry-card<?php echo ! empty( $pathway['featured'] ) ? ' is-featured' : ''; ?><?php echo $active_path ? '' : ' is-coming-soon'; ?>" data-pathway="<?php echo esc_attr( (string) ( $pathway['uid'] ?? $path_index ) ); ?>">
						<?php if ( ! empty( $pathway['badge'] ) ) : ?><span class="vava-paths-entry-tag"><?php echo esc_html( (string) $pathway['badge'] ); ?></span><?php endif; ?>
						<?php $pathway_image_id = absint( $pathway['image_id'] ?? 0 ); $pathway_image_url = $pathway_image_id ? wp_get_attachment_image_url( $pathway_image_id, 'medium_large' ) : ''; ?>
						<?php if ( $pathway_image_url ) : ?><div class="vava-paths-entry-image"><img src="<?php echo esc_url( $pathway_image_url ); ?>" alt="<?php echo esc_attr( (string) ( $pathway['title'] ?? '' ) ); ?>"/></div><?php else : ?><div class="vava-paths-entry-icon" aria-hidden="true"><?php echo vava_paths_compare_icon( $path_index % 3 ); // phpcs:ignore ?></div><?php endif; ?>
						<h3><?php echo esc_html( (string) ( $pathway['title'] ?? '' ) ); ?></h3>
						<div class="vava-paths-entry-description"><?php echo vava_richtext_output( (string) ( $pathway['description'] ?? '' ) ); // phpcs:ignore ?></div>
						<?php if ( $active_path ) : ?><button class="vava-paths-stage-button vava-paths-stage-button-primary" type="button" data-paths-stage-target="2"><?php echo esc_html( (string) ( $pathway['button_text'] ?? $labels['choose_path'] ) ); ?><span aria-hidden="true">←</span></button><?php else : ?><span class="vava-paths-stage-button is-disabled" aria-disabled="true"><?php echo esc_html( (string) ( $pathway['button_text'] ?? $labels['coming_soon'] ) ); ?></span><?php endif; ?>
					</article>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<div class="vava-paths-stage vava-paths-category-stage" data-paths-stage="2" aria-hidden="true" hidden>
			<div class="container vava-paths-stage-container">
				<header class="vava-paths-stage-heading">
					<span class="vava-paths-stage-pill"><?php echo esc_html( $labels['stage'] . ' 2' ); ?><?php echo vava_paths_stage_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h2><?php echo esc_html( $is_en ? 'Choose the session type' : 'اختر نوع الجلسات' ); ?></h2>
					<span class="vava-paths-heading-divider" aria-hidden="true"><i></i></span>
					<p><?php echo esc_html( $is_en ? 'Choose one of the three categories to view its sessions in the next stage.' : 'اختر واحدًا من الأقسام الثلاثة لعرض جلساته في المرحلة التالية.' ); ?></p>
				</header>
				<nav class="vava-paths-progress vava-paths-category-progress" aria-label="<?php echo esc_attr( $is_en ? 'Individual consultation stages' : 'مراحل الاستشارات الفردية' ); ?>">
					<span class="is-complete"><i>1</i><?php echo esc_html( $is_en ? 'Choose path' : 'اختيار المسار' ); ?></span>
					<span class="is-active"><i>2</i><?php echo esc_html( $is_en ? 'Choose session type' : 'اختيار نوع الجلسات' ); ?></span>
					<span><i>3</i><?php echo esc_html( $is_en ? 'View sessions' : 'عرض الجلسات' ); ?></span>
				</nav>
				<div class="vava-paths-category-grid">
					<?php foreach ( array( 'quick', 'followup', 'comprehensive' ) as $category_index => $category_key ) : $definition = $category_definitions[ $category_key ]; ?>
					<button class="vava-paths-category-card" type="button" data-session-category-card="<?php echo esc_attr( $category_key ); ?>" data-paths-category-select="<?php echo esc_attr( $category_key ); ?>" data-category-title="<?php echo esc_attr( $definition['title'] ); ?>" data-category-duration="<?php echo esc_attr( $definition['duration'] ); ?>" data-category-intro="<?php echo esc_attr( $definition['intro'] ); ?>">
						<span class="vava-paths-category-title"><?php echo esc_html( $definition['title'] ); ?></span>
						<span class="vava-paths-category-duration"><?php echo esc_html( $definition['duration'] ); ?></span>
						<span class="vava-paths-category-action"><?php echo esc_html( $is_en ? 'View sessions' : 'عرض الجلسات' ); ?><span aria-hidden="true">←</span></span>
					</button>
					<?php endforeach; ?>
				</div>
				<?php if ( $faq_items ) : ?>
				<section class="vava-paths-stage-faq">
					<h3><?php echo esc_html( $is_en ? 'Frequently asked questions' : 'أسئلة شائعة' ); ?></h3>
					<div class="vava-paths-stage-faq-list">
						<?php foreach ( $faq_items as $faq_index => $item ) : ?>
						<details<?php echo 0 === $faq_index ? ' open' : ''; ?>><summary><?php echo esc_html( (string) ( $item['question'] ?? '' ) ); ?></summary><div><?php echo vava_richtext_output( (string) ( $item['answer'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></details>
						<?php endforeach; ?>
					</div>
				</section>
				<?php endif; ?>
				<div class="vava-paths-stage-actions"><button class="vava-paths-stage-button vava-paths-stage-button-secondary" type="button" data-paths-stage-target="1"><?php echo esc_html( $labels['back_paths'] ); ?></button></div>
			</div>
		</div>

		<div class="vava-paths-stage vava-paths-sessions-stage" data-paths-stage="3" aria-hidden="true" hidden>
			<div class="container vava-paths-stage-container">
				<header class="vava-paths-stage-heading">
					<h2 data-selected-category-title></h2>
					<span class="vava-paths-stage-pill vava-paths-stage-duration" data-selected-category-duration></span>
					<span class="vava-paths-heading-divider" aria-hidden="true"><i></i></span>
					<p data-selected-category-intro></p>
				</header>
				<nav class="vava-paths-progress vava-paths-category-progress" aria-label="<?php echo esc_attr( $is_en ? 'Individual consultation stages' : 'مراحل الاستشارات الفردية' ); ?>">
					<span class="is-complete"><i>1</i><?php echo esc_html( $is_en ? 'Choose path' : 'اختيار المسار' ); ?></span>
					<span class="is-complete"><i>2</i><?php echo esc_html( $is_en ? 'Choose session type' : 'اختيار نوع الجلسات' ); ?></span>
					<span class="is-active"><i>3</i><?php echo esc_html( $is_en ? 'View sessions' : 'عرض الجلسات' ); ?></span>
				</nav>
				<div class="vava-paths-session-grid vava-paths-category-results" data-paths-session-grid>
					<?php foreach ( $packages as $package ) : $category = vava_paths_session_category( $package ); ?>
						<article class="vava-paths-session-card vava-paths-session-card-simple<?php echo ! empty( $package['featured'] ) ? ' is-featured' : ''; ?>" data-paths-session-card data-session-category="<?php echo esc_attr( $category ); ?>">
							<h3><?php echo esc_html( (string) ( $package['title'] ?? '' ) ); ?></h3>
							<div class="vava-paths-session-simple-footer">
								<div class="vava-paths-session-price"><strong><?php echo esc_html( (string) ( $package['price'] ?? '' ) ); ?></strong><small><?php echo esc_html( (string) ( $package['currency'] ?? '' ) ); ?></small></div>
								<a href="<?php echo esc_url( vava_paths_resolve_url( (string) ( $package['link_url'] ?? '' ) ) ); ?>"><?php echo esc_html( (string) ( $package['link_text'] ?? $labels['view_details'] ) ); ?></a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>

				<section class="vava-paths-inline-comparison" data-comprehensive-comparison hidden>
					<header><h3><?php echo esc_html( (string) ( $compare['title'] ?? $labels['comparison_title'] ) ); ?></h3><p><?php echo esc_html( (string) ( $compare['intro'] ?? $labels['comparison_intro'] ) ); ?></p></header>
					<div class="vava-paths-comparison-grid">
						<?php foreach ( array_values( array_filter( (array) ( $compare['plans'] ?? array() ), static fn( $item ) => ! isset( $item['enabled'] ) || ! empty( $item['enabled'] ) ) ) as $index => $plan ) : ?>
						<article class="vava-paths-comparison-card<?php echo ! empty( $plan['featured'] ) ? ' is-featured' : ''; ?>">
							<?php if ( ! empty( $plan['badge'] ) ) : ?><span class="vava-paths-comparison-badge"><?php echo esc_html( (string) $plan['badge'] ); ?></span><?php endif; ?>
							<div class="vava-paths-comparison-icon" aria-hidden="true"><?php echo vava_paths_compare_icon( $index ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
							<h3><?php echo esc_html( (string) ( $plan['title'] ?? '' ) ); ?></h3>
							<p class="vava-paths-comparison-description"><?php echo wp_kses_post( (string) ( $plan['description'] ?? '' ) ); ?></p>
							<div class="vava-paths-comparison-core"><?php echo esc_html( (string) ( $plan['core_label'] ?? '' ) ); ?></div>
							<ul><?php foreach ( array_values( (array) ( $plan['features'] ?? array() ) ) as $feature ) : $available = ! isset( $feature['visible'] ) || ! empty( $feature['visible'] ); ?><li class="<?php echo $available ? 'is-available' : 'is-unavailable'; ?>"><span class="<?php echo $available ? 'is-yes' : 'is-no'; ?>"><?php echo $available ? '✓' : '×'; ?></span><b><?php echo esc_html( (string) ( $feature['text'] ?? '' ) ); ?></b><?php if ( ! empty( $feature['value'] ) ) : ?><em><?php echo esc_html( (string) $feature['value'] ); ?></em><?php endif; ?></li><?php endforeach; ?></ul>
							<div class="vava-paths-comparison-action-row"><div class="vava-paths-comparison-action-price"><strong><?php echo esc_html( (string) ( $plan['price'] ?? '' ) ); ?></strong></div><?php if ( ! isset( $plan['booking_enabled'] ) || ! empty( $plan['booking_enabled'] ) ) : $plan_booking_url = function_exists( 'vava_booking_url_for_service' ) ? vava_booking_url_for_service( (string) ( $plan['uid'] ?? '' ), $lang ) : '#'; ?><a class="vava-paths-plan-book-button" href="<?php echo esc_url( $plan_booking_url ); ?>"><?php echo esc_html( vava_paths_comparison_booking_label( (array) $plan, $lang ) ); ?></a><?php endif; ?></div>
						</article>
						<?php endforeach; ?>
					</div>
					<?php $guidance_message = vava_paths_comparison_guidance_html( (array) $compare, $packages, $lang ); if ( $guidance_message ) : ?><div class="vava-paths-guidance-note"><?php echo $guidance_message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
				</section>
				<div class="vava-paths-stage-actions"><button class="vava-paths-stage-button vava-paths-stage-button-secondary" type="button" data-paths-stage-target="2"><?php echo esc_html( (string) ( $compare['back_text'] ?? ( $is_en ? 'Back to session types' : 'العودة إلى أنواع الجلسات' ) ) ); ?></button><button class="vava-paths-stage-button vava-paths-stage-button-quiet" type="button" data-paths-stage-target="1"><?php echo esc_html( $labels['restart'] ); ?></button></div>
			</div>
		</div>

	</section>
	<?php
}


function vava_paths_render_page_identity( WP_Post $post ): void {
	vava_render_bilingual_page_identity( $post, (string) ( get_permalink( $post ) ?: vava_page_url( 'paths-vava' ) ) );
}

function vava_paths_name( string $lang, array $path ): string {
	$name = 'vava_paths[' . $lang . ']';
	foreach ( $path as $part ) {
		$name .= '[' . $part . ']';
	}
	return $name;
}

function vava_paths_id( string $lang, array $path ): string {
	return sanitize_html_class( 'vava_paths_' . $lang . '_' . implode( '_', array_map( 'strval', $path ) ) );
}

function vava_paths_render_text_field( array $data, string $lang, array $path, string $label, bool $rich = false, bool $full = false ): void {
	$value = vava_paths_get( $data, $path, '' );
	$name  = vava_paths_name( $lang, $path );
	$id    = vava_paths_id( $lang, $path );
	?>
	<div class="vava-field<?php echo $full || $rich ? ' vava-field-full' : ''; ?>">
		<label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
		<?php if ( $rich ) : ?>
			<?php vava_render_richtext_editor( array( 'name' => $name, 'id' => $id, 'value' => (string) $value, 'dir' => 'en' === $lang ? 'ltr' : 'rtl', 'class' => 'vava-paths-field' ) ); ?>
		<?php else : ?>
			<input class="widefat vava-paths-field" data-paths-field id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="text" value="<?php echo esc_attr( (string) $value ); ?>"/>
		<?php endif; ?>
	</div>
	<?php
}

function vava_paths_render_checkbox( array $data, string $lang, array $path, string $label ): void {
	$name = vava_paths_name( $lang, $path );
	$id   = vava_paths_id( $lang, $path );
	$value = (bool) vava_paths_get( $data, $path, false );
	?><label class="vava-paths-checkbox" for="<?php echo esc_attr( $id ); ?>"><input class="vava-paths-field" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="checkbox" value="1" <?php checked( $value ); ?>/><span><?php echo esc_html( $label ); ?></span></label><?php
}

function vava_paths_render_media_field( WP_Post $post ): void {
	$attachment_id = absint( get_post_meta( $post->ID, '_vava_paths_hero_image_id', true ) );
	$id            = 'vava_paths_hero_image_id';
	$fallback_url  = get_theme_file_uri( 'assets/images/paths-hero.webp' );
	$current_url   = vava_homepage_media_current_url( $attachment_id, 'image', $fallback_url );
	?>
	<div class="vava-admin-field vava-admin-field-media vava-admin-field-wide vava-paths-media-field" data-paths-media-field data-fallback-url="<?php echo esc_url( $fallback_url ); ?>">
		<label for="<?php echo esc_attr( $id ); ?>"><strong<?php echo vava_admin_i18n_attributes( 'صورة هيرو مسارات VAVA', 'VAVA Paths hero image' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>صورة هيرو مسارات VAVA</strong></label>
		<div class="vava-media-field" data-media-type="image">
			<input class="vava-media-id" data-paths-media-id data-fallback-url="<?php echo esc_url( $fallback_url ); ?>" data-media-url="<?php echo esc_url( $current_url ); ?>" id="<?php echo esc_attr( $id ); ?>" name="_vava_paths_hero_image_id" type="hidden" value="<?php echo esc_attr( (string) $attachment_id ); ?>"/>
			<div class="vava-media-dropzone" role="button" tabindex="0">
				<div class="vava-media-preview"><?php echo vava_homepage_media_preview_markup( $attachment_id, 'image' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<div class="vava-upload-progress" aria-hidden="true"><span></span></div>
			</div>
			<div class="vava-media-actions">
				<button class="button button-secondary vava-media-select" type="button"<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'choose_replace', 'ar' ), vava_homepage_admin_text( 'choose_replace', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'choose_replace', 'ar' ) ); ?></button>
				<button class="button button-secondary vava-media-remove" type="button"<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'delete_file', 'ar' ), vava_homepage_admin_text( 'delete_file', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'delete_file', 'ar' ) ); ?></button>
			</div>
		</div>
	</div>
	<?php
}

function vava_paths_render_preview( string $section, array $data, string $lang, int $post_id = 0 ): void {
	$is_en     = 'en' === $lang;
	$hero      = $data['hero'] ?? array();
	$packages  = array_values( (array) ( $data['packages'] ?? array() ) );
	$plans     = array_values( (array) vava_paths_get( $data, array( 'compare', 'plans' ), array() ) );
	$future    = array_values( (array) ( $data['future'] ?? array() ) );
	$pathways  = array_values( (array) ( $data['pathways'] ?? array() ) );
	$faq_items = array_values( (array) vava_paths_get( $data, array( 'faq', 'items' ), array() ) );
	$image_id  = $post_id > 0 ? absint( get_post_meta( $post_id, '_vava_paths_hero_image_id', true ) ) : 0;
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium_large' ) : '';
	if ( ! $image_url ) {
		$image_url = get_theme_file_uri( 'assets/images/paths-hero.webp' );
	}
	?>
	<aside class="vava-live-preview vava-paths-preview" data-paths-preview-root data-preview-language="<?php echo esc_attr( $lang ); ?>" data-preview-section="<?php echo esc_attr( $section ); ?>" dir="<?php echo $is_en ? 'ltr' : 'rtl'; ?>">
		<header class="vava-live-preview-header">
			<div><strong><?php echo esc_html( $is_en ? 'Live preview' : 'معاينة مباشرة' ); ?></strong><span><?php echo esc_html( 'pathways' === $section ? ( $is_en ? 'Main pathways' : 'المسارات الأساسية' ) : ( vava_paths_sections( $lang )[ $section ] ?? '' ) ); ?></span></div>
			<span class="vava-live-preview-dot" aria-hidden="true"></span>
		</header>
		<div class="vava-preview-viewport">
			<div class="vava-preview-stage">
				<div class="vava-preview-canvas vava-paths-preview-canvas vava-paths-preview-<?php echo esc_attr( $section ); ?>" data-preview-design-width="900" dir="<?php echo $is_en ? 'ltr' : 'rtl'; ?>">
				<?php if ( 'hero' === $section ) : ?>
					<div class="vava-paths-preview-hero-copy">
						<span data-paths-preview="hero.eyebrow"><?php echo esc_html( (string) ( $hero['eyebrow'] ?? '' ) ); ?></span>
						<h2 data-paths-preview="hero.title"><?php echo esc_html( (string) ( $hero['title'] ?? '' ) ); ?></h2>
						<div class="vava-paths-preview-hero-body" data-paths-preview-html="hero.content"><?php echo vava_richtext_output( vava_paths_hero_content( $hero ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					</div>
					<div class="vava-paths-preview-hero-image" data-paths-preview-image="hero" style="background-image:url('<?php echo esc_url( $image_url ); ?>')"></div>
				<?php elseif ( 'packages' === $section ) : ?>
					<div class="vava-paths-preview-heading-block vava-paths-preview-consultation-head">
						<span data-paths-preview="consultation.eyebrow"><?php echo esc_html( (string) vava_paths_get( $data, array( 'consultation', 'eyebrow' ) ) ); ?></span>
						<h3 data-paths-preview="consultation.title"><?php echo esc_html( (string) vava_paths_get( $data, array( 'consultation', 'title' ) ) ); ?></h3>
						<div data-paths-preview-html="consultation.description"><?php echo vava_richtext_output( (string) vava_paths_get( $data, array( 'consultation', 'description' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<div class="vava-paths-preview-support" data-paths-preview-html="consultation.note"><?php echo vava_richtext_output( (string) vava_paths_get( $data, array( 'consultation', 'note' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					</div>
					<div class="vava-paths-preview-package-guidance" data-paths-preview-html="consultation.intro_note"><?php echo vava_richtext_output( (string) vava_paths_get( $data, array( 'consultation', 'intro_note' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<div class="vava-paths-preview-package-grid">
					<?php foreach ( $packages as $index => $item ) : ?>
						<article class="vava-paths-preview-package-card is-simple<?php echo ! empty( $item['featured'] ) ? ' featured' : ''; ?>" data-paths-preview-class="packages.<?php echo esc_attr( (string) $index ); ?>.featured">
							<strong data-paths-preview="packages.<?php echo esc_attr( (string) $index ); ?>.title"><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></strong>
							<div class="vava-paths-preview-package-bottom"><div class="vava-paths-preview-price"><b data-paths-preview="packages.<?php echo esc_attr( (string) $index ); ?>.price"><?php echo esc_html( (string) ( $item['price'] ?? '' ) ); ?></b><span data-paths-preview="packages.<?php echo esc_attr( (string) $index ); ?>.currency"><?php echo esc_html( (string) ( $item['currency'] ?? '' ) ); ?></span></div><span class="vava-paths-preview-details-link" data-paths-preview="packages.<?php echo esc_attr( (string) $index ); ?>.link_text"><?php echo esc_html( (string) ( $item['link_text'] ?? '' ) ); ?></span></div>
						</article>
					<?php endforeach; ?>
					</div>
					<div class="vava-session-focused-preview" data-session-focused-preview hidden><div class="vava-session-focused-hero"><div data-session-preview-image></div><div><span data-session-preview-badge></span><h3 data-session-preview-title></h3><p data-session-preview-description></p><div class="vava-session-focused-facts" data-session-preview-facts></div></div></div><div class="vava-session-focused-content" data-session-preview-content></div><div class="vava-session-focused-actions" data-session-preview-actions></div></div>
				<?php elseif ( 'comparison' === $section ) : ?>
					<div class="vava-paths-preview-compare-wrap">
						<div class="vava-paths-preview-heading-block"><h3 data-paths-preview="compare.title"><?php echo esc_html( (string) vava_paths_get( $data, array( 'compare', 'title' ) ) ); ?></h3><div data-paths-preview-html="compare.intro"><?php echo vava_richtext_output( (string) vava_paths_get( $data, array( 'compare', 'intro' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div>
						<div class="vava-paths-preview-compare-grid">
						<?php foreach ( $plans as $index => $item ) : ?>
							<article class="vava-paths-preview-compare-plan<?php echo ! empty( $item['featured'] ) ? ' featured' : ''; ?>" data-paths-preview-class="compare.plans.<?php echo esc_attr( (string) $index ); ?>.featured">
								<span class="vava-paths-preview-compare-badge" data-paths-preview="compare.plans.<?php echo esc_attr( (string) $index ); ?>.badge"><?php echo esc_html( (string) ( $item['badge'] ?? '' ) ); ?></span>
								<div class="vava-paths-preview-compare-icon" aria-hidden="true"><?php echo vava_paths_compare_icon( (int) $index ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
								<strong class="vava-paths-preview-compare-title" data-paths-preview="compare.plans.<?php echo esc_attr( (string) $index ); ?>.title"><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></strong>
								<div class="vava-paths-preview-compare-description" data-paths-preview-html="compare.plans.<?php echo esc_attr( (string) $index ); ?>.description"><?php echo vava_richtext_output( (string) ( $item['description'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
									<div class="vava-paths-preview-core" data-paths-preview="compare.plans.<?php echo esc_attr( (string) $index ); ?>.core_label"><?php echo esc_html( (string) ( $item['core_label'] ?? '' ) ); ?></div>
									<ul class="vava-paths-preview-feature-list"><?php foreach ( array_values( (array) ( $item['features'] ?? array() ) ) as $feature_index => $feature ) : $available = ! isset( $feature['visible'] ) || ! empty( $feature['visible'] ); ?><li class="<?php echo $available ? 'is-available' : 'is-unavailable'; ?>"><span class="vava-paths-preview-feature-mark <?php echo $available ? 'yes' : 'no'; ?>"><?php echo $available ? '✓' : '×'; ?></span><span><?php echo esc_html( (string) ( $feature['text'] ?? '' ) ); ?></span><?php if ( ! empty( $feature['value'] ) ) : ?><b><?php echo esc_html( (string) $feature['value'] ); ?></b><?php endif; ?></li><?php endforeach; ?></ul>
									<div class="vava-paths-preview-plan-action"><div class="vava-paths-preview-plan-price"><b data-paths-preview="compare.plans.<?php echo esc_attr( (string) $index ); ?>.price"><?php echo esc_html( (string) ( $item['price'] ?? '' ) ); ?></b></div><span class="vava-paths-preview-plan-button" data-paths-preview="compare.plans.<?php echo esc_attr( (string) $index ); ?>.button_text"><?php echo esc_html( vava_paths_comparison_booking_label( (array) $item, $lang ) ); ?></span></div>
							</article>
						<?php endforeach; ?>
						</div>
						<div class="vava-paths-preview-guidance-note" data-paths-preview-html="compare.guidance_html"><?php echo vava_paths_comparison_guidance_html( (array) ( $data['compare'] ?? array() ), array_values( (array) ( $data['packages'] ?? array() ) ), $lang ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					</div>
				<?php elseif ( 'pathways' === $section ) : ?>
					<div class="vava-paths-preview-future-grid vava-paths-preview-pathways-grid"><?php foreach ( $pathways as $index => $item ) : $preview_image_id = absint( $item['image_id'] ?? 0 ); $preview_image_url = $preview_image_id ? wp_get_attachment_image_url( $preview_image_id, 'medium' ) : ''; ?><article class="<?php echo ! empty( $item['featured'] ) ? 'featured' : ''; ?>" data-paths-preview-class="pathways.<?php echo esc_attr( (string) $index ); ?>.featured"><?php if ( $preview_image_url ) : ?><img class="vava-paths-preview-pathway-image" src="<?php echo esc_url( $preview_image_url ); ?>" alt=""/><?php endif; ?><span data-paths-preview="pathways.<?php echo esc_attr( (string) $index ); ?>.badge"><?php echo esc_html( (string) ( $item['badge'] ?? '' ) ); ?></span><strong data-paths-preview="pathways.<?php echo esc_attr( (string) $index ); ?>.title"><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></strong><div data-paths-preview-html="pathways.<?php echo esc_attr( (string) $index ); ?>.description"><?php echo vava_richtext_output( (string) ( $item['description'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><b data-paths-preview="pathways.<?php echo esc_attr( (string) $index ); ?>.button_text"><?php echo esc_html( (string) ( $item['button_text'] ?? '' ) ); ?></b></article><?php endforeach; ?></div>
				<?php elseif ( 'future' === $section ) : ?>
					<div class="vava-paths-preview-future-grid"><?php foreach ( $future as $index => $item ) : ?><article><span data-paths-preview="future.<?php echo esc_attr( (string) $index ); ?>.tag"><?php echo esc_html( (string) ( $item['tag'] ?? '' ) ); ?></span><strong data-paths-preview="future.<?php echo esc_attr( (string) $index ); ?>.title"><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></strong><div data-paths-preview-html="future.<?php echo esc_attr( (string) $index ); ?>.description"><?php echo vava_richtext_output( (string) ( $item['description'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></article><?php endforeach; ?></div>
				<?php elseif ( 'questions' === $section ) : ?>
					<div class="vava-paths-admin-faq-preview" data-question-preview-canvas>
						<header><h3><?php echo esc_html( $is_en ? 'Frequently asked questions' : 'أسئلة شائعة' ); ?></h3><p><?php echo esc_html( $is_en ? 'The questions below appear under the session categories in stage two.' : 'تظهر الأسئلة التالية أسفل أقسام الجلسات في المرحلة الثانية.' ); ?></p></header>
						<div class="vava-paths-preview-faq" data-question-preview-faq><?php foreach ( $faq_items as $index => $item ) : ?><article><div class="vava-paths-preview-faq-question"><span><?php echo esc_html( (string) ( $item['question'] ?? '' ) ); ?></span><b><?php echo 0 === $index ? '−' : '+'; ?></b></div><?php if ( 0 === $index ) : ?><div class="vava-paths-preview-faq-answer"><?php echo vava_richtext_output( (string) ( $item['answer'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?></article><?php endforeach; ?></div>
					</div>
				<?php elseif ( 'faq' === $section ) : ?>
					<div class="vava-paths-admin-faq-preview"><header><h3><?php echo esc_html( $is_en ? 'Frequently asked questions' : 'الأسئلة الشائعة' ); ?></h3><p><?php echo esc_html( $is_en ? 'These questions appear under the session categories in stage two.' : 'تظهر هذه الأسئلة أسفل أقسام الجلسات في المرحلة الثانية.' ); ?></p></header><div class="vava-paths-preview-faq"><?php foreach ( $faq_items as $index => $item ) : ?><article><div class="vava-paths-preview-faq-question"><span data-paths-preview="faq.items.<?php echo esc_attr( (string) $index ); ?>.question"><?php echo esc_html( (string) ( $item['question'] ?? '' ) ); ?></span><b><?php echo 0 === $index ? '−' : '+'; ?></b></div><?php if ( 0 === $index ) : ?><div class="vava-paths-preview-faq-answer" data-paths-preview-html="faq.items.<?php echo esc_attr( (string) $index ); ?>.answer"><?php echo vava_richtext_output( (string) ( $item['answer'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?></article><?php endforeach; ?></div></div>
				<?php else : ?>
					<div class="vava-paths-preview-closing"><h3 data-paths-preview="closing.title"><?php echo esc_html( (string) vava_paths_get( $data, array( 'closing', 'title' ) ) ); ?></h3><div data-paths-preview-html="closing.description"><?php echo vava_richtext_output( (string) vava_paths_get( $data, array( 'closing', 'description' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div class="vava-paths-preview-closing-note" data-paths-preview-html="closing.note"><?php echo vava_richtext_output( (string) vava_paths_get( $data, array( 'closing', 'note' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div class="btn-row"><span data-paths-preview="closing.button_1_text"><?php echo esc_html( (string) vava_paths_get( $data, array( 'closing', 'button_1_text' ) ) ); ?></span><span class="is-secondary" data-paths-preview="closing.button_2_text"><?php echo esc_html( (string) vava_paths_get( $data, array( 'closing', 'button_2_text' ) ) ); ?></span></div></div>
				<?php endif; ?>
				</div>
			</div>
		</div>
	</aside>
	<?php
}

function vava_paths_render_package_item( array $data, string $lang, int $index, bool $open ): void {
	$item = $data['packages'][ $index ] ?? array();
	$base = array( 'packages', $index );
	$number = str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT );
	?>
	<div class="vava-paths-accordion-item<?php echo $open ? ' is-open' : ''; ?>" data-paths-accordion-item>
		<button class="vava-paths-accordion-head" type="button" data-paths-accordion-toggle aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"><span class="vava-repeater-handle">⋮⋮</span><span class="vava-paths-item-number"><?php echo esc_html( $number ); ?></span><strong><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></strong><span class="vava-paths-chevron">⌄</span></button>
		<div class="vava-paths-accordion-body">
			<div class="vava-fields-grid">
				<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'badge' ) ), 'en' === $lang ? 'Badge' : 'الشارة' ); ?>
				<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'title' ) ), 'en' === $lang ? 'Title' : 'العنوان' ); ?>
				<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'description' ) ), 'en' === $lang ? 'Description' : 'الوصف', true, true ); ?>
				<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'meta_1' ) ), 'en' === $lang ? 'Meta 1' : 'المعلومة الأولى' ); ?>
				<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'meta_2' ) ), 'en' === $lang ? 'Meta 2' : 'المعلومة الثانية' ); ?>
				<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'meta_3' ) ), 'en' === $lang ? 'Meta 3' : 'المعلومة الثالثة' ); ?>
				<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'price' ) ), 'en' === $lang ? 'Price' : 'السعر' ); ?>
				<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'currency' ) ), 'en' === $lang ? 'Currency' : 'العملة' ); ?>
				<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'link_text' ) ), 'en' === $lang ? 'Link text' : 'نص الرابط' ); ?>
				<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'link_url' ) ), 'en' === $lang ? 'Link URL' : 'رابط التفاصيل', false, true ); ?>
			</div>
			<?php vava_paths_render_checkbox( $data, $lang, array_merge( $base, array( 'featured' ) ), 'en' === $lang ? 'Featured package' : 'باقة مميزة' ); ?>
		</div>
	</div>
	<?php
}

function vava_paths_render_compare_plan( array $data, string $lang, int $index, bool $open ): void {
	$item = $data['compare']['plans'][ $index ] ?? array();
	$base = array( 'compare', 'plans', $index );
	$number = str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT );
	?>
	<div class="vava-paths-accordion-item<?php echo $open ? ' is-open' : ''; ?>" data-paths-accordion-item>
		<button class="vava-paths-accordion-head" type="button" data-paths-accordion-toggle aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"><span class="vava-repeater-handle">⋮⋮</span><span class="vava-paths-item-number"><?php echo esc_html( $number ); ?></span><strong><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></strong><span class="vava-paths-chevron">⌄</span></button>
		<div class="vava-paths-accordion-body"><div class="vava-fields-grid">
			<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'badge' ) ), 'en' === $lang ? 'Badge' : 'الشارة' ); ?>
			<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'title' ) ), 'en' === $lang ? 'Title' : 'العنوان' ); ?>
			<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'description' ) ), 'en' === $lang ? 'Description' : 'الوصف', true, true ); ?>
			<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'core_label' ) ), 'en' === $lang ? 'Core label' : 'عنوان العناصر الأساسية' ); ?>
			<?php foreach ( array_values( (array) ( $item['features'] ?? array() ) ) as $feature_index => $feature ) : ?>
				<div class="vava-paths-feature-row vava-field-full"><span><?php echo esc_html( (string) ( $feature_index + 1 ) ); ?></span><?php vava_paths_render_checkbox( $data, $lang, array_merge( $base, array( 'features', $feature_index, 'enabled' ) ), 'en' === $lang ? 'Included' : 'مشمول' ); ?><input class="widefat vava-paths-field" name="<?php echo esc_attr( vava_paths_name( $lang, array_merge( $base, array( 'features', $feature_index, 'text' ) ) ) ); ?>" type="text" value="<?php echo esc_attr( (string) ( $feature['text'] ?? '' ) ); ?>"/><input class="widefat vava-paths-field" name="<?php echo esc_attr( vava_paths_name( $lang, array_merge( $base, array( 'features', $feature_index, 'value' ) ) ) ); ?>" type="text" value="<?php echo esc_attr( (string) ( $feature['value'] ?? '' ) ); ?>" placeholder="<?php echo esc_attr( 'en' === $lang ? 'Optional value' : 'قيمة اختيارية' ); ?>"/></div>
			<?php endforeach; ?>
			<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'investment_label' ) ), 'en' === $lang ? 'Investment label' : 'عنوان الاستثمار' ); ?>
			<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'price' ) ), 'en' === $lang ? 'Price' : 'السعر' ); ?>
			<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'best_label' ) ), 'en' === $lang ? 'Best for label' : 'عنوان الأفضل لمن' ); ?>
			<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'best_text' ) ), 'en' === $lang ? 'Best for text' : 'وصف الأفضل لمن', true, true ); ?>
		</div><?php vava_paths_render_checkbox( $data, $lang, array_merge( $base, array( 'featured' ) ), 'en' === $lang ? 'Featured plan' : 'الخطة المميزة' ); ?></div>
	</div>
	<?php
}

function vava_paths_render_simple_fixed_item( array $data, string $lang, string $group, int $index, bool $open ): void {
	if ( 'faq' === $group ) {
		$item = $data['faq']['items'][ $index ] ?? array();
		$base = array( 'faq', 'items', $index );
	} else {
		$item = $data[ $group ][ $index ] ?? array();
		$base = array( $group, $index );
	}
	$number = str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT );
	$title_key = 'faq' === $group ? 'question' : 'title';
	$body_key  = 'faq' === $group ? 'answer' : 'description';
	?>
	<div class="vava-paths-accordion-item<?php echo $open ? ' is-open' : ''; ?>" data-paths-accordion-item>
		<button class="vava-paths-accordion-head" type="button" data-paths-accordion-toggle aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"><span class="vava-repeater-handle">⋮⋮</span><span class="vava-paths-item-number"><?php echo esc_html( $number ); ?></span><strong><?php echo esc_html( (string) ( $item[ $title_key ] ?? '' ) ); ?></strong><span class="vava-paths-chevron">⌄</span></button>
		<div class="vava-paths-accordion-body"><div class="vava-fields-grid">
		<?php if ( 'future' === $group ) { vava_paths_render_text_field( $data, $lang, array_merge( $base, array( 'tag' ) ), 'en' === $lang ? 'Tag' : 'الشارة' ); } ?>
		<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( $title_key ) ), 'en' === $lang ? ( 'faq' === $group ? 'Question' : 'Title' ) : ( 'faq' === $group ? 'السؤال' : 'العنوان' ), false, true ); ?>
		<?php vava_paths_render_text_field( $data, $lang, array_merge( $base, array( $body_key ) ), 'en' === $lang ? ( 'faq' === $group ? 'Answer' : 'Description' ) : ( 'faq' === $group ? 'الإجابة' : 'الوصف' ), true, true ); ?>
		</div></div>
	</div>
	<?php
}

function vava_paths_render_settings( WP_Post $post ): void {
	wp_nonce_field( 'vava_paths_save', 'vava_paths_nonce' );
	$sections_ar = vava_paths_sections( 'ar' );
	$sections_en = vava_paths_sections( 'en' );
	?>
	<div class="vava-homepage-admin vava-paths-admin" data-active-language="ar" data-active-section="hero" data-settings-title-ar="إعدادات صفحة مسارات VAVA" data-settings-title-en="VAVA Paths page settings">
		<input type="hidden" name="_vava_admin_active_language" value="ar" data-vava-active-language-input/>
		<?php vava_paths_render_page_identity( $post ); ?>
		<div class="vava-admin-toolbar"><div class="vava-section-tabs" role="tablist"><?php foreach ( $sections_ar as $id => $label ) : ?><button aria-selected="<?php echo 'hero' === $id ? 'true' : 'false'; ?>" class="vava-section-tab<?php echo 'hero' === $id ? ' is-active' : ''; ?>" data-section="<?php echo esc_attr( $id ); ?>" type="button"><span class="vava-tab-icon"><?php echo vava_paths_section_icon( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span data-vava-i18n-ar="<?php echo esc_attr( $label ); ?>" data-vava-i18n-en="<?php echo esc_attr( $sections_en[ $id ] ?? $label ); ?>"><?php echo esc_html( $label ); ?></span></button><?php endforeach; ?></div><div class="vava-toolbar-actions"><div class="vava-language-switch"><button class="is-active" data-language="ar" type="button"><span>العربية</span><small>AR</small></button><button data-language="en" type="button"><span>English</span><small>EN</small></button></div><button class="button vava-homepage-update-button" data-vava-submit type="button"><svg viewBox="0 0 24 24"><path d="M20 12a8 8 0 1 1-2.35-5.65"/><path d="M20 4v6h-6"/></svg><span data-vava-i18n-ar="تحديث" data-vava-i18n-en="Update">تحديث</span></button></div></div>
		<div class="vava-section-panels">
		<?php foreach ( array_keys( $sections_ar ) as $section ) : ?><section class="vava-section-panel<?php echo 'hero' === $section ? ' is-active' : ''; ?>" data-section-panel="<?php echo esc_attr( $section ); ?>">
			<?php foreach ( array( 'ar', 'en' ) as $lang ) : $data = vava_paths_data( (int) $post->ID, $lang ); ?><div class="vava-language-pane<?php echo 'ar' === $lang ? ' is-active' : ''; ?>" data-language-pane="<?php echo esc_attr( $lang ); ?>" dir="<?php echo 'en' === $lang ? 'ltr' : 'rtl'; ?>"><div class="vava-editor-workspace"><?php vava_paths_render_preview( $section, $data, $lang, (int) $post->ID ); ?><div class="vava-editor-controls">
			<?php if ( 'hero' === $section ) : ?><div class="vava-fields-grid"><?php vava_paths_render_text_field( $data, $lang, array( 'hero', 'eyebrow' ), 'en' === $lang ? 'Small text' : 'النص الصغير' ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'hero', 'title' ), 'en' === $lang ? 'Main title' : 'العنوان الرئيسي' ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'hero', 'content' ), 'en' === $lang ? 'Hero body' : 'نص الهيرو', true, true ); ?></div>
			<?php elseif ( 'packages' === $section ) : ?><div class="vava-fields-grid"><?php vava_paths_render_text_field( $data, $lang, array( 'consultation', 'eyebrow' ), 'en' === $lang ? 'Small text' : 'النص الصغير' ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'consultation', 'title' ), 'en' === $lang ? 'Section title' : 'عنوان القسم' ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'consultation', 'description' ), 'en' === $lang ? 'Introduction' : 'المقدمة', true, true ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'consultation', 'note' ), 'en' === $lang ? 'Supporting text' : 'النص الداعم', true, true ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'consultation', 'intro_note' ), 'en' === $lang ? 'Package guidance' : 'إرشاد اختيار الباقة', true, true ); ?></div><div class="vava-paths-fixed-list" data-paths-list-pattern="[packages]"><h3><?php echo 'en' === $lang ? 'Fixed consultation cards' : 'بطاقات الاستشارات الثابتة'; ?></h3><?php for ( $i = 0; $i < 8; $i++ ) { vava_paths_render_package_item( $data, $lang, $i, 0 === $i ); } ?></div>
			<?php elseif ( 'comparison' === $section ) : ?><div class="vava-fields-grid"><?php vava_paths_render_text_field( $data, $lang, array( 'compare', 'title' ), 'en' === $lang ? 'Section title' : 'عنوان القسم' ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'compare', 'back_text' ), 'en' === $lang ? 'Back button text' : 'نص زر العودة' ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'compare', 'intro' ), 'en' === $lang ? 'Introduction' : 'المقدمة', true, true ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'compare', 'guidance_html' ), 'en' === $lang ? 'Guidance note' : 'ملاحظة الإرشاد', true, true ); ?></div><div class="vava-paths-fixed-list" data-paths-list-pattern="[compare][plans]"><h3><?php echo 'en' === $lang ? 'Fixed comparison plans' : 'خطط المقارنة الثابتة'; ?></h3><?php for ( $i = 0; $i < 3; $i++ ) { vava_paths_render_compare_plan( $data, $lang, $i, 0 === $i ); } ?></div>
			<?php elseif ( 'future' === $section ) : ?><div class="vava-paths-fixed-list" data-paths-list-pattern="[future]"><h3><?php echo 'en' === $lang ? 'Fixed upcoming pathway cards' : 'بطاقات المسارات القادمة الثابتة'; ?></h3><?php for ( $i = 0; $i < 3; $i++ ) { vava_paths_render_simple_fixed_item( $data, $lang, 'future', $i, 0 === $i ); } ?></div>
			<?php elseif ( 'faq' === $section ) : ?><div class="vava-fields-grid"><?php vava_paths_render_text_field( $data, $lang, array( 'faq', 'eyebrow' ), 'en' === $lang ? 'Small text' : 'النص الصغير' ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'faq', 'title' ), 'en' === $lang ? 'Title' : 'العنوان' ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'faq', 'intro' ), 'en' === $lang ? 'Introduction' : 'المقدمة', true, true ); ?></div><div class="vava-paths-fixed-list" data-paths-list-pattern="[faq][items]"><h3><?php echo 'en' === $lang ? 'Fixed FAQ items' : 'أسئلة الجلسات الثابتة'; ?></h3><?php for ( $i = 0; $i < 8; $i++ ) { vava_paths_render_simple_fixed_item( $data, $lang, 'faq', $i, 0 === $i ); } ?></div>
			<?php else : ?><div class="vava-fields-grid"><?php vava_paths_render_text_field( $data, $lang, array( 'closing', 'title' ), 'en' === $lang ? 'Title' : 'العنوان' ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'closing', 'description' ), 'en' === $lang ? 'Description' : 'الوصف', true, true ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'closing', 'note' ), 'en' === $lang ? 'Supporting note' : 'النص الداعم', true, true ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'closing', 'button_1_text' ), 'en' === $lang ? 'First button text' : 'نص الزر الأول' ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'closing', 'button_1_url' ), 'en' === $lang ? 'First button URL' : 'رابط الزر الأول' ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'closing', 'button_2_text' ), 'en' === $lang ? 'Second button text' : 'نص الزر الثاني' ); ?><?php vava_paths_render_text_field( $data, $lang, array( 'closing', 'button_2_url' ), 'en' === $lang ? 'Second button URL' : 'رابط الزر الثاني' ); ?></div><?php endif; ?>
			</div></div></div><?php endforeach; ?>
			<?php if ( 'hero' === $section ) : ?><div class="vava-shared-fields"><div class="vava-fields-grid"><?php vava_paths_render_media_field( $post ); ?></div></div><?php endif; ?>
		</section><?php endforeach; ?>
		</div>
	</div>
	<?php
}

function vava_paths_add_meta_boxes( string $post_type, WP_Post $post ): void {
	if ( 'page' !== $post_type || ! vava_paths_is_page( (int) $post->ID ) ) {
		return;
	}
	remove_meta_box( 'postdivrich', 'page', 'normal' );
	add_meta_box( 'vava_homepage_settings', 'إعدادات صفحة مسارات VAVA', 'vava_paths_render_settings', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'vava_paths_add_meta_boxes', 10, 2 );

function vava_paths_sanitize_against( $raw, $schema, string $key = '' ) {
	if ( is_array( $schema ) ) {
		$raw = is_array( $raw ) ? $raw : array();
		$result = array();
		foreach ( $schema as $schema_key => $schema_value ) {
			$result[ $schema_key ] = vava_paths_sanitize_against( $raw[ $schema_key ] ?? null, $schema_value, (string) $schema_key );
		}
		return $result;
	}
	if ( is_bool( $schema ) ) {
		return ! empty( $raw );
	}
	$value = is_scalar( $raw ) ? (string) $raw : '';
	if ( str_contains( $key, 'url' ) ) {
		return esc_url_raw( $value );
	}
	$rich_keys = array( 'content', 'lead_1', 'lead_2', 'note', 'description', 'intro', 'intro_note', 'guidance_html', 'best_text', 'answer' );
	return in_array( $key, $rich_keys, true ) ? wp_kses_post( $value ) : sanitize_text_field( $value );
}

function vava_paths_save_meta( int $post_id, WP_Post $post ): void {
	if ( ! isset( $_POST['vava_paths_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vava_paths_nonce'] ) ), 'vava_paths_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $post_id ) || 'page' !== $post->post_type || ! current_user_can( 'edit_page', $post_id ) || ! vava_paths_is_page( $post_id ) ) { return; }
	vava_save_bilingual_page_titles( $post_id );
	$raw_all         = isset( $_POST['vava_paths'] ) && is_array( $_POST['vava_paths'] ) ? wp_unslash( $_POST['vava_paths'] ) : array();
	$active_language = isset( $_POST['_vava_admin_active_language'] ) ? vava_normalize_language( sanitize_key( wp_unslash( $_POST['_vava_admin_active_language'] ) ) ) : 'ar';
	$ar_defaults     = vava_paths_defaults( 'ar' );
	$en_defaults     = vava_paths_defaults( 'en' );
	$ar_raw          = isset( $raw_all['ar'] ) && is_array( $raw_all['ar'] ) ? $raw_all['ar'] : array();
	$en_raw          = isset( $raw_all['en'] ) && is_array( $raw_all['en'] ) ? $raw_all['en'] : array();
	$ar_data         = vava_paths_sanitize_against( $ar_raw, $ar_defaults );
	$en_data         = vava_paths_sanitize_against( $en_raw, $en_defaults );
	$stored_shared   = vava_paths_data( $post_id, 'ar' );

	foreach ( vava_paths_shared_setting_paths() as $path ) {
		$stored_value = vava_paths_array_value( $stored_shared, $path, null );
		$ar_value     = vava_paths_array_value( $ar_data, $path, $stored_value );
		$en_value     = vava_paths_array_value( $en_data, $path, $stored_value );
		$value        = vava_reconcile_shared_setting( $ar_value, $en_value, $stored_value, $active_language );
		vava_paths_array_set( $ar_data, $path, $value );
		vava_paths_array_set( $en_data, $path, $value );
	}

	update_post_meta( $post_id, vava_paths_meta_key( 'ar' ), $ar_data );
	update_post_meta( $post_id, vava_paths_meta_key( 'en' ), $en_data );
	if ( array_key_exists( '_vava_paths_hero_image_id', $_POST ) ) {
		update_post_meta( $post_id, '_vava_paths_hero_image_id', absint( $_POST['_vava_paths_hero_image_id'] ) );
	}
}
add_action( 'save_post_page', 'vava_paths_save_meta', 20, 2 );

function vava_paths_use_block_editor( $use_block_editor, $post ) {
	if ( $post instanceof WP_Post && vava_paths_is_page( (int) $post->ID ) ) {
		return false;
	}
	return $use_block_editor;
}
add_filter( 'use_block_editor_for_post', 'vava_paths_use_block_editor', PHP_INT_MAX, 2 );

/**
 * Disable the block editor at post-type decision time only for the current
 * VAVA Paths edit request. The optional second argument prevents fatal errors
 * on WordPress/Gutenberg versions that pass a single filter argument.
 */
function vava_paths_use_block_editor_for_post_type( $use_block_editor, $post_type = '' ) {
	if ( 'page' !== (string) $post_type ) {
		return $use_block_editor;
	}
	$post_id = vava_paths_admin_post_id();
	return $post_id && vava_paths_is_page( $post_id ) ? false : $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'vava_paths_use_block_editor_for_post_type', PHP_INT_MAX, 2 );

/** Force the classic VAVA settings screen before WordPress chooses an editor. */
function vava_paths_prepare_admin_editor(): void {
	$post_id = vava_paths_admin_post_id();
	if ( $post_id && vava_paths_is_page( $post_id ) ) {
		remove_post_type_support( 'page', 'editor' );
	}
}
add_action( 'admin_init', 'vava_paths_prepare_admin_editor', 1 );

function vava_paths_admin_body_class( string $classes ): string {
	global $post;
	$post_id = $post instanceof WP_Post ? (int) $post->ID : ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $post_id && vava_paths_is_page( $post_id ) ) {
		$classes .= ' vava-homepage-classic vava-paths-classic';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'vava_paths_admin_body_class' );

function vava_paths_advanced_editor_filter( bool $is_advanced, int $post_id ): bool {
	return $is_advanced || vava_paths_is_page( $post_id );
}
add_filter( 'vava_is_advanced_page_editor', 'vava_paths_advanced_editor_filter', 10, 2 );

function vava_paths_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) { return; }
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || ! vava_paths_is_page( $post_id ) ) { return; }
	wp_enqueue_media();
	wp_enqueue_style( 'vava-homepage-admin', get_theme_file_uri( 'assets/css/admin-homepage.css' ), array(), vava_asset_version( 'assets/css/admin-homepage.css' ) );
	wp_enqueue_style( 'vava-about-admin', get_theme_file_uri( 'assets/css/admin-about.css' ), array( 'vava-homepage-admin' ), vava_asset_version( 'assets/css/admin-about.css' ) );
	wp_enqueue_style( 'vava-paths-admin', get_theme_file_uri( 'assets/css/admin-paths.css' ), array( 'vava-about-admin' ), vava_asset_version( 'assets/css/admin-paths.css' ) );
	wp_enqueue_script( 'vava-paths-admin', get_theme_file_uri( 'assets/js/admin-paths.js' ), array( 'jquery', 'jquery-ui-sortable' ), vava_asset_version( 'assets/js/admin-paths.js' ), true );
	wp_localize_script(
		'vava-paths-admin',
		'vavaPathsAdmin',
		array(
			'uploadUrl'   => admin_url( 'async-upload.php' ),
			'uploadNonce' => wp_create_nonce( 'media-form' ),
			'postId'      => $post_id,
			'maxImageMb'  => 20,
		)
	);
}
add_action( 'admin_enqueue_scripts', 'vava_paths_admin_assets' );

function vava_paths_document_title( array $parts ): array {
	if ( is_page_template( vava_paths_template_slug() ) ) {
		$parts['title'] = vava_bilingual_page_title( get_queried_object_id(), vava_current_language() );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'vava_paths_document_title' );

function vava_paths_assign_or_create_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$page = get_page_by_path( 'paths-vava', OBJECT, 'page' );
	if ( ! $page ) {
		$page_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'مسارات VAVA', 'post_name' => 'paths-vava' ), true );
		if ( ! is_wp_error( $page_id ) ) { $page = get_post( $page_id ); }
	}
	if ( $page instanceof WP_Post ) {
		update_post_meta( $page->ID, '_wp_page_template', vava_paths_template_slug() );
		if ( ! metadata_exists( 'post', $page->ID, vava_page_title_meta_key( 'ar' ) ) ) { update_post_meta( $page->ID, vava_page_title_meta_key( 'ar' ), 'مسارات VAVA' ); }
		if ( ! metadata_exists( 'post', $page->ID, vava_page_title_meta_key( 'en' ) ) ) { update_post_meta( $page->ID, vava_page_title_meta_key( 'en' ), 'VAVA Paths' ); }
	}
}
add_action( 'admin_init', 'vava_paths_assign_or_create_page', 5 );
