<?php
/** General bilingual template for ordinary WordPress pages. */
defined( 'ABSPATH' ) || exit;

$page_id = get_queried_object_id();
$lang    = vava_current_language();
$title   = function_exists( 'vava_article_localized_value' ) ? vava_article_localized_value( $page_id, 'title', $lang ) : get_the_title( $page_id );
$content = function_exists( 'vava_article_localized_value' ) ? vava_article_localized_value( $page_id, 'content', $lang ) : (string) get_post_field( 'post_content', $page_id );

$GLOBALS['vava_page_data_name']        = 'en' === $lang ? 'page-en.html' : 'page.html';
$GLOBALS['vava_active_nav']            = '';
$GLOBALS['vava_internal_body_classes'] = array( 'vava-general-page' );
wp_enqueue_style( 'vava-general-page', get_template_directory_uri() . '/assets/css/general-page.css', array(), '1.22.24' );
get_header( 'page' );
?>
<main class="vava-general-page-main">
	<section class="section vava-general-page-hero">
		<div class="container"><div class="eyebrow">VAVA Living</div><h1><?php echo esc_html( $title ); ?></h1></div>
	</section>
	<section class="section vava-general-page-content-section">
		<div class="container"><article class="vava-general-page-content vava-richtext-content"><?php echo apply_filters( 'the_content', $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></article></div>
	</section>
</main>
<?php get_footer( 'page' ); ?>
