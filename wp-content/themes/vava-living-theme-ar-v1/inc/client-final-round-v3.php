<?php
/**
 * VAVA Living — Client requested programming/content fixes 1.22.59.
 *
 * Focused production-safe fixes only: reliable page navigation, exact copy
 * cleanup, discovery duration normalization and frontend styles for editable
 * pathway imagery. Client how-to questions are intentionally not injected into
 * WordPress; they are answered separately in the handover message.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'VAVA_CLIENT_FINAL_ROUND_VERSION' ) ) {
	define( 'VAVA_CLIENT_FINAL_ROUND_VERSION', '1.22.59' );
}

/**
 * Resolve an approved VAVA page by its WordPress template rather than by a
 * guessed pretty permalink. This remains safe when production uses ?page_id=.
 */
function vava_client_page_url( string $key, string $language = '' ): string {
	$language = $language ?: ( function_exists( 'vava_current_language' ) ? vava_current_language() : 'ar' );
	$map = array(
		'about'      => array( 'template' => 'page-templates/about-vava.php', 'slug' => 'about-vava' ),
		'paths'      => array( 'template' => 'page-templates/paths-vava.php', 'slug' => 'paths-vava' ),
		'selections' => array( 'template' => 'page-templates/selections-vava.php', 'slug' => 'selections-vava' ),
		'journal'    => array( 'template' => 'page-templates/journal-vava.php', 'slug' => 'journal' ),
		'contact'    => array( 'template' => 'page-templates/contact-vava.php', 'slug' => 'contact' ),
	);
	$key = sanitize_key( $key );
	if ( ! isset( $map[ $key ] ) ) { return function_exists( 'vava_language_url' ) ? vava_language_url( $language, home_url( '/' ) ) : home_url( '/' ); }

	static $resolved = array();
	$cache_key = $key . ':' . $language;
	if ( isset( $resolved[ $cache_key ] ) ) { return $resolved[ $cache_key ]; }

	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_wp_page_template',
		'meta_value'     => $map[ $key ]['template'],
		'no_found_rows'  => true,
	) );
	$page_id = isset( $pages[0] ) ? absint( $pages[0] ) : 0;
	if ( $page_id && function_exists( 'vava_localized_page_url' ) ) {
		$resolved[ $cache_key ] = vava_localized_page_url( $page_id, $language );
		return $resolved[ $cache_key ];
	}
	if ( $page_id ) {
		$url = (string) get_permalink( $page_id );
		$resolved[ $cache_key ] = function_exists( 'vava_language_url' ) ? vava_language_url( $language, $url ) : $url;
		return $resolved[ $cache_key ];
	}

	$resolved[ $cache_key ] = function_exists( 'vava_page_url' ) ? vava_page_url( $map[ $key ]['slug'] ) : home_url( '/' );
	return $resolved[ $cache_key ];
}

/** Return the first page ID using a VAVA page template. */
function vava_client_page_id_by_template( string $template ): int {
	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_wp_page_template',
		'meta_value'     => $template,
		'no_found_rows'  => true,
	) );
	return isset( $pages[0] ) ? absint( $pages[0] ) : 0;
}

/** Remove only the exact retired journal phrase, never arbitrary client copy. */
function vava_client_remove_retired_enlightenment_phrase( $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $key => $item ) { $value[ $key ] = vava_client_remove_retired_enlightenment_phrase( $item ); }
		return $value;
	}
	if ( ! is_string( $value ) ) { return $value; }
	$normalized = trim( wp_strip_all_tags( $value ) );
	$retired = array( 'مساحة للتنوير', 'A space for enlightenment', 'Space for enlightenment' );
	return in_array( $normalized, $retired, true ) ? '' : $value;
}

/** Find and normalize the free discovery session in a saved Paths payload. */
function vava_client_fix_discovery_session_duration( array $data, string $lang ): array {
	$walk = static function ( &$node ) use ( &$walk, $lang ): void {
		if ( ! is_array( $node ) ) { return; }
		$title = trim( wp_strip_all_tags( (string) ( $node['title'] ?? '' ) ) );
		$uid   = sanitize_key( (string) ( $node['uid'] ?? '' ) );
		$is_discovery = ( function_exists( 'vava_paths_is_discovery_session' ) && vava_paths_is_discovery_session( $node ) )
			|| (bool) preg_match( '/(?:جلسة\s*استكشافية|discovery\s*session)/iu', $title . ' ' . $uid );
		if ( $is_discovery ) {
			$node['duration']         = 'en' === $lang ? '15 minutes' : '15 دقيقة';
			$node['booking_duration'] = 15;
			if ( isset( $node['duration_minutes'] ) ) { $node['duration_minutes'] = 15; }
		}
		foreach ( $node as &$child ) { if ( is_array( $child ) ) { $walk( $child ); } }
		unset( $child );
	};
	$walk( $data );
	return $data;
}

/**
 * One-time safe content migration. It updates only values explicitly requested
 * in the final client notes and never rewrites unrelated content.
 */
function vava_client_final_round_migrate(): void {
	$option = 'vava_client_final_round_version';
	if ( (string) get_option( $option, '' ) === VAVA_CLIENT_FINAL_ROUND_VERSION ) { return; }

	// Homepage: retire the exact old Journal subtitle if it was saved in meta.
	$home_id = absint( get_option( 'page_on_front' ) );
	if ( ! $home_id ) { $home_id = vava_client_page_id_by_template( 'page-templates/homepage.php' ); }
	if ( $home_id ) {
		foreach ( array( '_vava_home_journal_subtitle', '_vava_home_journal_subtitle_en' ) as $meta_key ) {
			$current = get_post_meta( $home_id, $meta_key, true );
			$clean   = vava_client_remove_retired_enlightenment_phrase( $current );
			if ( $clean !== $current ) { update_post_meta( $home_id, $meta_key, $clean ); }
		}
	}

	// Journal: clean the same exact retired phrase from saved bilingual arrays.
	$journal_id = vava_client_page_id_by_template( 'page-templates/journal-vava.php' );
	if ( $journal_id && function_exists( 'vava_journal_text_meta_key' ) ) {
		foreach ( array( 'ar', 'en' ) as $lang ) {
			$key     = vava_journal_text_meta_key( $lang );
			$current = get_post_meta( $journal_id, $key, true );
			if ( is_array( $current ) ) {
				$clean = vava_client_remove_retired_enlightenment_phrase( $current );
				if ( $clean !== $current ) { update_post_meta( $journal_id, $key, $clean ); }
			}
		}
	}

	// Paths: persist the exact 15-minute duration for the discovery session.
	$paths_id = vava_client_page_id_by_template( 'page-templates/paths-vava.php' );
	if ( $paths_id && function_exists( 'vava_paths_meta_key' ) ) {
		foreach ( array( 'ar', 'en' ) as $lang ) {
			$key     = vava_paths_meta_key( $lang );
			$current = get_post_meta( $paths_id, $key, true );
			if ( is_array( $current ) ) {
				$clean = vava_client_fix_discovery_session_duration( $current, $lang );
				// The client requested the helper sentence under FAQ to be removed.
				if ( isset( $clean['faq'] ) && is_array( $clean['faq'] ) && ! empty( $clean['faq']['intro'] ) ) {
					$clean['faq']['intro'] = '';
				}
				if ( $clean !== $current ) { update_post_meta( $paths_id, $key, $clean ); }
			}
		}
	}


	update_option( $option, VAVA_CLIENT_FINAL_ROUND_VERSION, false );
}
add_action( 'init', 'vava_client_final_round_migrate', 40 );

/** Add final-round presentation styles and admin helper behavior. */
function vava_client_final_round_enqueue_frontend(): void {
	wp_enqueue_style( 'vava-client-final-round', get_theme_file_uri( 'assets/css/client-final-round-v3.css' ), array(), VAVA_CLIENT_FINAL_ROUND_VERSION );
}
add_action( 'wp_enqueue_scripts', 'vava_client_final_round_enqueue_frontend', 30 );
