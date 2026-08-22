<?php
/** Shared footer content for every VAVA page. */
defined( 'ABSPATH' ) || exit;

$lang    = function_exists( 'vava_current_language' ) ? vava_current_language() : 'ar';
$home_id = absint( get_option( 'page_on_front' ) );

$footer_tagline = function_exists( 'vava_home_field_language' ) ? vava_home_field_language( $home_id, '_vava_home_footer_tagline', $lang ) : ( 'en' === $lang ? 'a space for returning' : 'مساحة للعودة' );
$footer_copy    = function_exists( 'vava_home_field_language' ) ? vava_home_field_language( $home_id, '_vava_home_footer_copyright', $lang ) : ( 'en' === $lang ? 'All rights reserved © VAVA Living 2026' : 'جميع الحقوق محفوظة © VAVA Living 2026' );
$document_label = function_exists( 'vava_home_field_language' ) ? vava_home_field_language( $home_id, '_vava_home_footer_document_label', $lang ) : ( 'en' === $lang ? 'Freelance Document:' : 'وثيقة العمل الحر:' );
$document_no    = function_exists( 'vava_home_field' ) ? vava_home_field( $home_id, '_vava_home_footer_document_number', '686388076FL' ) : '686388076FL';
$primary_links  = function_exists( 'vava_home_footer_links' ) ? vava_home_footer_links( $home_id, 'primary', $lang ) : array();
$policy_links   = function_exists( 'vava_home_footer_links' ) ? vava_home_footer_links( $home_id, 'policy', $lang ) : array();
$social_links   = function_exists( 'vava_home_footer_social' ) ? vava_home_footer_social( $home_id ) : array();

$normalize_internal_url = static function ( string $url ): string {
	return function_exists( 'vava_normalize_internal_url' ) ? vava_normalize_internal_url( $url ) : $url;
};
?>
<footer class="footer page-footer">
	<div class="footer-logo"><img alt="VAVA Living" src="<?php echo esc_url( vava_asset_uri( 'assets/images/vava-logo.png' ) ); ?>"/><div class="footer-tagline"><?php echo esc_html( $footer_tagline ); ?></div></div>
	<div class="footer-center">
		<div class="footer-main-links"><?php foreach ( $primary_links as $link ) : if ( empty( $link['label'] ) ) { continue; } ?><a href="<?php echo esc_url( $normalize_internal_url( (string) ( $link['url'] ?? '#' ) ) ); ?>"><?php echo esc_html( (string) $link['label'] ); ?></a><?php endforeach; ?></div>
		<div class="footer-policy-links"><?php foreach ( $policy_links as $link ) : if ( empty( $link['label'] ) ) { continue; } ?><a href="<?php echo esc_url( $normalize_internal_url( (string) ( $link['url'] ?? '#' ) ) ); ?>"><?php echo esc_html( (string) $link['label'] ); ?></a><?php endforeach; ?></div>
		<div aria-hidden="true" class="footer-divider"><span class="footer-divider-mark"></span></div>
		<div class="footer-meta"><div class="footer-copy"><?php echo esc_html( $footer_copy ); ?></div><div class="footer-document"><?php echo esc_html( $document_label ); ?> <span><?php echo esc_html( (string) $document_no ); ?></span></div></div>
	</div>
	<div class="social">
	<?php foreach ( $social_links as $item ) :
		$platform = sanitize_key( (string) ( $item['platform'] ?? '' ) );
		$icon     = function_exists( 'vava_home_social_icon_svg' ) ? vava_home_social_icon_svg( $platform ) : '';
		if ( ! $icon ) { continue; }
		$label = function_exists( 'vava_home_social_label' ) ? vava_home_social_label( $platform ) : ucfirst( $platform );
		$url   = function_exists( 'vava_home_social_href' ) ? vava_home_social_href( is_array( $item ) ? $item : array() ) : '';
		if ( $url ) : $external = 0 === strpos( $url, 'http://' ) || 0 === strpos( $url, 'https://' ); ?>
		<a aria-label="<?php echo esc_attr( $label ); ?>" href="<?php echo esc_url( $url, array( 'http', 'https', 'mailto', 'tel' ) ); ?>"<?php if ( $external ) : ?> rel="noopener noreferrer" target="_blank"<?php endif; ?> title="<?php echo esc_attr( $label ); ?>"><i><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></i></a>
		<?php else : ?><i aria-label="<?php echo esc_attr( $label ); ?>" title="<?php echo esc_attr( $label ); ?>"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></i><?php endif; ?>
	<?php endforeach; ?>
	</div>
</footer>
