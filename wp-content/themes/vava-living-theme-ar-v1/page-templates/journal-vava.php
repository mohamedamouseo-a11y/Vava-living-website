<?php
/**
 * Template Name: VAVA — Journal (AR / EN)
 * Template Post Type: page
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

$page_id  = get_queried_object_id();
$lang     = vava_current_language();
$text     = vava_journal_text_data( $page_id, $lang );
$shared   = vava_journal_shared_data( $page_id );
$hero     = (array) ( $text['hero'] ?? array() );
$articles = (array) ( $text['articles'] ?? array() );
$hero_img = vava_journal_image_url( (int) $shared['hero_image_id'], 'assets/images/journal-hero.webp' );
$results  = ! empty( $shared['show_articles'] ) ? vava_journal_render_articles_results( $page_id, 1, $lang ) : array( 'html' => '' );

$GLOBALS['vava_page_data_name']        = 'en' === $lang ? 'journal-en.html' : 'journal.html';
$GLOBALS['vava_active_nav']            = 'journal';
$GLOBALS['vava_internal_body_classes'] = array( 'journal-page', 'vava-journal-page' );
get_header( 'page' );
?>
<main>
	<span class="blob sage"></span><span class="blob cream"></span><span class="leaf-line vava-inline-journal-1"></span>
	<section class="section vava-journal-hero" id="journal-hero">
		<div class="container hero-grid">
			<div class="hero-copy">
				<div class="eyebrow"><?php echo esc_html( (string) ( $hero['eyebrow'] ?? '' ) ); ?></div>
				<h1><?php echo esc_html( (string) ( $hero['title'] ?? '' ) ); ?></h1>
				<p><?php echo esc_html( (string) ( $hero['intro'] ?? '' ) ); ?></p>
			</div>
			<div class="visual-card vava-journal-hero-visual" style="background-image:url('<?php echo esc_url( $hero_img ); ?>')"></div>
		</div>
	</section>

	<section class="section vava-journal-articles" id="journal-articles">
		<div class="container">
			<?php if ( ! empty( $shared['show_articles'] ) ) : ?>
			<div class="open-text journal-section-head">
				<h2><?php echo esc_html( (string) ( $articles['title'] ?? '' ) ); ?></h2>
			</div>
			<div class="vava-journal-results" data-vava-journal-results data-page-id="<?php echo esc_attr( (string) $page_id ); ?>" data-language="<?php echo esc_attr( $lang ); ?>" data-current-page="1" aria-live="polite">
				<?php echo $results['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div class="vava-journal-loader" data-journal-loader hidden aria-hidden="true"><span></span><span></span><span></span></div>
			<?php else : ?>
			<article class="vava-journal-coming-soon" role="status" aria-live="polite">
				<span class="eyebrow"><?php echo esc_html( 'en' === $lang ? 'VAVA Journal' : 'مجلة VAVA' ); ?></span>
				<h2><?php echo esc_html( 'en' === $lang ? 'Coming soon' : 'قريبًا' ); ?></h2>
			</article>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( ! empty( $shared['show_articles'] ) ) : ?>
	<div class="vava-journal-reader" data-journal-reader hidden aria-hidden="true">
		<div class="vava-journal-reader-backdrop" aria-hidden="true"></div>
		<div class="vava-journal-reader-dialog" data-journal-reader-dialog role="dialog" aria-modal="true" aria-labelledby="vava-journal-reader-title" tabindex="-1">
			<div class="vava-journal-reader-loading" data-journal-reader-loading aria-live="polite"><span></span><span></span><span></span></div>
			<div class="vava-journal-reader-output" data-journal-reader-output></div>
		</div>
	</div>
	<?php endif; ?>
</main>
<?php get_footer( 'page' ); ?>
