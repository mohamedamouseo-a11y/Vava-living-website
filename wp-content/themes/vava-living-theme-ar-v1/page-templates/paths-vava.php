<?php
/**
 * Template Name: VAVA — Paths (AR / EN)
 * Template Post Type: page
 */
defined( 'ABSPATH' ) || exit;
$lang = vava_current_language();
$GLOBALS['vava_page_data_name'] = 'en' === $lang ? 'paths-vava-en.html' : 'paths-vava.html';
$GLOBALS['vava_active_nav'] = 'paths';
$GLOBALS['vava_internal_body_classes'] = array( 'paths-page' );
get_header( 'page' );
?>
<main class="subpage vava-paths-wordpress-page">
<?php vava_paths_render_frontend( get_queried_object_id(), $lang ); ?>
</main>
<?php get_footer( 'page' ); ?>
