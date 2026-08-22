<?php
/**
 * Single VAVA session details.
 *
 * @package VAVA_Living
 */
defined( 'ABSPATH' ) || exit;
$lang    = vava_current_language();
$is_en   = 'en' === $lang;
$post_id = get_queried_object_id();
$session = vava_paths_session_data_from_post( $post_id, $lang );
$source   = absint( $session['_source_page'] ?? 0 );
$category = function_exists( 'vava_paths_session_category' ) ? vava_paths_session_category( $session ) : 'comprehensive';
$return   = $source ? add_query_arg( 'vava_lang', $lang, get_permalink( $source ) ) . '#vava-path-stage-3-' . $category : home_url( '/' );
$default_return_labels = array(
	'ar' => array(
		'quick'         => 'العودة إلى الاستشارات السريعة',
		'followup'      => 'العودة إلى جلسات المتابعة',
		'comprehensive' => 'العودة إلى الجلسات الشاملة',
	),
	'en' => array(
		'quick'         => 'Back to quick consultations',
		'followup'      => 'Back to follow-up sessions',
		'comprehensive' => 'Back to comprehensive sessions',
	),
);
$saved_return_text = trim( (string) ( $session['return_text'] ?? '' ) );
$legacy_return_texts = array( 'العودة إلى كل الباقات', 'العودة إلى الجلسات', 'Back to all packages', 'Back to sessions' );
$return_text = ( '' === $saved_return_text || in_array( $saved_return_text, $legacy_return_texts, true ) )
	? ( $default_return_labels[ $lang ][ $category ] ?? $default_return_labels[ $lang ]['comprehensive'] )
	: $saved_return_text;
$image_id = absint( $session['image_id'] ?? 0 );
$image = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : get_theme_file_uri( 'assets/images/paths-hero.webp' );
$basics = array_values( (array) ( $session['basics'] ?? array() ) );
/* Duration remains visible in session details, but is always derived from the category. */
$duration_value = function_exists( 'vava_paths_session_display_duration' )
	? vava_paths_session_display_duration( $session, $lang )
	: trim( (string) ( $session['duration'] ?? '' ) );
$basics = array_values( array_filter( $basics, static function ( $fact ): bool {
	$fact = is_array( $fact ) ? $fact : array();
	$key  = sanitize_key( (string) ( $fact['key'] ?? '' ) );
	$label = trim( (string) ( $fact['label'] ?? '' ) );
	return 'duration' !== $key && ! preg_match( '/^(?:المدة|مدة الجلسة|duration)$/iu', $label );
} ) );
if ( '' !== $duration_value ) {
	array_unshift( $basics, array(
		'key'   => 'duration',
		'label' => $is_en ? 'Duration' : 'المدة',
		'value' => $duration_value,
		'icon'  => 'clock',
	) );
}
$audience = array_values( (array) ( $session['audience'] ?? array() ) );
$outcomes = array_values( (array) ( $session['outcomes'] ?? array() ) );
$booking_allowed = ! isset( $session['booking_enabled'] ) || ! empty( $session['booking_enabled'] );
$GLOBALS['vava_active_nav'] = 'paths';
$GLOBALS['vava_page_data_name'] = $is_en ? 'comprehensive-session-en.html' : 'comprehensive-session.html';
$GLOBALS['vava_internal_body_classes'] = array( 'vava-session-page' );
get_header( 'page' );
?>
<main class="vava-session-detail" dir="<?php echo esc_attr( $is_en ? 'ltr' : 'rtl' ); ?>">
	<section class="vava-session-hero">
		<div class="vava-session-hero-media"><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( (string) ( $session['title'] ?? '' ) ); ?>"/></div>
		<div class="vava-session-hero-copy">
			<nav class="vava-session-breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $is_en ? 'Home' : 'الرئيسية' ); ?></a><span>‹</span><a href="<?php echo esc_url( $return ); ?>"><?php echo esc_html( $is_en ? 'VAVA Paths' : 'مسارات VAVA' ); ?></a><span>‹</span><b><?php echo esc_html( $is_en ? 'Session details' : 'تفاصيل الجلسة' ); ?></b></nav>
			<h1><?php echo esc_html( (string) ( $session['title'] ?? '' ) ); ?></h1>
			<?php $hero_tags = array_values( array_unique( array_filter( array_map( 'trim', array( (string) ( $session['session_type'] ?? '' ), (string) $duration_value ) ) ) ) ); ?>
			<?php if ( $hero_tags ) : ?><div class="vava-session-tags"><?php foreach ( $hero_tags as $tag ) : ?><span><?php echo esc_html( $tag ); ?></span><?php endforeach; ?></div><?php endif; ?>
			<div class="vava-session-hero-actions"><?php if ( $booking_allowed ) : ?><a class="vava-session-btn is-primary" href="<?php echo esc_url( function_exists( 'vava_booking_url_for_service' ) ? vava_booking_url_for_service( (string) ( $session['uid'] ?? '' ), $lang ) : (string) ( $session['booking_url'] ?? '#' ) ); ?>"><?php echo esc_html( (string) ( $session['booking_text'] ?? ( $is_en ? 'Book session' : 'احجز الجلسة' ) ) ); ?></a><?php endif; ?><a class="vava-session-btn is-secondary" href="<?php echo esc_url( $return ); ?>">← <?php echo esc_html( $return_text ); ?></a></div>
		</div>
		<aside class="vava-session-facts">
			<?php foreach ( $basics as $fact ) : $fact_label = trim( (string) ( $fact['label'] ?? '' ) ); $fact_value = trim( (string) ( $fact['value'] ?? '' ) ); if ( '' === $fact_label && '' === $fact_value ) { continue; } $fact_icon = vava_paths_session_basic_icon( $fact_label, (string) ( $fact['icon'] ?? '' ) ); ?><div class="vava-session-fact"><i><?php echo vava_paths_session_basic_icon_svg( $fact_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></i><span><?php echo esc_html( $fact_label ); ?></span><strong><?php echo esc_html( $fact_value ); ?></strong></div><?php endforeach; ?>
			<?php if ( ! empty( $session['availability'] ) ) : ?><b class="vava-session-availability">✓ <?php echo esc_html( (string) $session['availability'] ); ?></b><?php endif; ?>
		</aside>
	</section>

	<section class="vava-session-summary-grid<?php echo 'followup' === $category ? ' is-followup-approved' : ''; ?>">
		<article><span>✦</span><h2><?php echo esc_html( (string) ( $session['overview_title'] ?? ( $is_en ? 'Session overview' : 'وصف الجلسة' ) ) ); ?></h2><div><?php echo vava_richtext_output( (string) ( $session['overview'] ?? '' ) ); // phpcs:ignore ?></div></article>
		<article><span>♙</span><h2><?php echo esc_html( (string) ( $session['audience_title'] ?? ( $is_en ? 'Suitable for you if…' : 'مناسبة لك إذا كنت...' ) ) ); ?></h2><ul><?php foreach ( $audience as $item ) : ?><li><?php echo esc_html( (string) ( $item['text'] ?? '' ) ); ?></li><?php endforeach; ?></ul></article>
		<article><span>✦</span><h2><?php echo esc_html( (string) ( $session['outcomes_title'] ?? ( $is_en ? 'What does it include?' : 'ماذا تشمل؟' ) ) ); ?></h2><ul><?php foreach ( $outcomes as $item ) : ?><li><?php echo esc_html( (string) ( $item['text'] ?? '' ) ); ?></li><?php endforeach; ?></ul></article>
	</section>

</main>
<?php get_footer( 'page' ); ?>
