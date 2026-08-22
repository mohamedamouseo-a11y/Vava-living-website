<?php
/**
 * Compatibility router for the configured static front page.
 *
 * The homepage content lives in the selectable page template so the page can
 * own its meta boxes. This file only delegates WordPress' front-page request.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

$page_id       = get_queried_object_id();
$template_slug = $page_id ? get_page_template_slug( $page_id ) : '';

if ( vava_homepage_template_slug() === $template_slug ) {
	require get_theme_file_path( vava_homepage_template_slug() );
	return;
}

require get_theme_file_path( 'index.php' );
