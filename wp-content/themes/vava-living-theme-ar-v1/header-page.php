<?php
/** Shared bilingual header for all VAVA internal pages. */
defined( 'ABSPATH' ) || exit;
$lang       = vava_current_language();
$is_en      = 'en' === $lang;
$dir        = $is_en ? 'ltr' : 'rtl';
$data_page  = isset( $GLOBALS['vava_page_data_name'] ) ? sanitize_file_name( (string) $GLOBALS['vava_page_data_name'] ) : ( $is_en ? 'internal-en.html' : 'internal.html' );
$active_nav = isset( $GLOBALS['vava_active_nav'] ) ? sanitize_key( (string) $GLOBALS['vava_active_nav'] ) : '';
$menu_label = $is_en ? 'Open menu' : 'فتح القائمة';
$lang_label = $is_en ? 'Choose language' : 'اختيار اللغة';
?>
<!doctype html>
<html lang="<?php echo esc_attr( $lang ); ?>" dir="<?php echo esc_attr( $dir ); ?>" data-page="<?php echo esc_attr( $data_page ); ?>">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>"/>
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<?php wp_head(); ?>
</head>
<?php
$body_classes = array( 'vava-internal-page', 'vava-internal-page-' . $lang );
if ( ! empty( $GLOBALS['vava_internal_body_classes'] ) && is_array( $GLOBALS['vava_internal_body_classes'] ) ) {
	$body_classes = array_merge( $body_classes, array_map( 'sanitize_html_class', $GLOBALS['vava_internal_body_classes'] ) );
}
?>
<body <?php body_class( $body_classes ); ?>>
<?php wp_body_open(); ?>
<header class="fixed-header compact" id="header">
<a aria-label="VAVA Living" class="brand-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img alt="VAVA Living logo" class="logo" src="<?php echo esc_url( vava_asset_uri( 'assets/images/vava-logo.png' ) ); ?>"/></a>
<button aria-label="<?php echo esc_attr( $menu_label ); ?>" class="burger" id="burger" type="button"><span></span></button>
<nav id="nav"><?php vava_render_internal_header_menu( $lang, $active_nav ); ?></nav>
<div class="header-tools"><?php vava_render_language_switch( $lang_label ); ?>
<?php if ( function_exists( 'vava_customer_header_icon' ) ) { vava_customer_header_icon( $lang ); } ?></div>
</header>
