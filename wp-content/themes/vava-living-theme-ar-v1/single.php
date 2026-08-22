<?php
/** Localized standard article template. */
defined( 'ABSPATH' ) || exit;
$lang = function_exists( 'vava_current_language' ) ? vava_current_language() : 'ar';
$is_en = 'en' === $lang;
$GLOBALS['vava_active_nav'] = 'journal';
$GLOBALS['vava_internal_body_classes'] = array( 'vava-single-article' );
get_header( 'page' );
while ( have_posts() ) : the_post();
	$title = function_exists( 'vava_article_localized_value' ) ? vava_article_localized_value( get_post(), 'title', $lang ) : get_the_title();
	$content = function_exists( 'vava_article_localized_value' ) ? vava_article_localized_value( get_post(), 'content', $lang ) : get_the_content(); ?>
	<main class="vava-journal-single" dir="<?php echo esc_attr( $is_en ? 'ltr' : 'rtl' ); ?>"><article class="container" style="max-width:920px;padding:80px 24px"><header><p><?php echo esc_html( get_the_date() ); ?></p><h1><?php echo esc_html( $title ); ?></h1></header><?php if ( has_post_thumbnail() ) : ?><figure><?php the_post_thumbnail( 'full', array( 'alt' => $title ) ); ?></figure><?php endif; ?><div class="vava-journal-reader-content"><?php echo apply_filters( 'the_content', $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></article></main>
<?php endwhile; get_footer( 'page' );
