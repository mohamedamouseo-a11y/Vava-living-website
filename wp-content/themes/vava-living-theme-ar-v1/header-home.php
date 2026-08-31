<?php
/**
 * Dedicated bilingual header for the VAVA homepage.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

$lang = function_exists( 'vava_current_language' ) ? vava_current_language() : 'ar';
$is_en = 'en' === $lang;
$dir   = $is_en ? 'ltr' : 'rtl';

$labels = $is_en
	? array(
		'home'          => 'Home',
		'about'         => 'About VAVA',
		'paths'         => 'VAVA Paths',
		'shop'          => 'VAVA Picks',
		'testimonials'  => 'Stories',
		'journal'       => 'Journal',
		'contact'       => 'Contact us',
		'home_aria'     => 'Return to the homepage',
		'menu_aria'     => 'Open menu',
		'language_aria' => 'Choose language',
		'next_aria'     => 'Go to the next section',
		'discover'      => 'Discover more',
		'scroll_hint'   => 'Swipe or scroll between spaces',
		'intro'         => 'Where life flourishes',
	)
	: array(
		'home'          => 'الرئيسية',
		'about'         => 'عن VAVA',
		'paths'         => 'مسارات VAVA',
		'shop'          => 'مختارات VAVA',
		'testimonials'  => 'التجارب',
		'journal'       => 'المجلة',
		'contact'       => 'تواصل معنا',
		'home_aria'     => 'العودة إلى الصفحة الرئيسية',
		'menu_aria'     => 'فتح القائمة',
		'language_aria' => 'اختيار اللغة',
		'next_aria'     => 'انتقال للقسم التالي',
		'discover'      => 'اكتشف المزيد',
		'scroll_hint'   => 'اسحبي / مرري للتنقل بين المساحات',
		'intro'         => 'حيث تزدهر الحياة',
	);
?>
<!doctype html>
<html lang="<?php echo esc_attr( $lang ); ?>" dir="<?php echo esc_attr( $dir ); ?>" data-page="<?php echo esc_attr( $is_en ? 'index-en.html' : 'index.html' ); ?>">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>"/>
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<?php wp_head(); ?>
</head>
<body <?php body_class( array( 'vava-homepage', 'vava-homepage-' . $lang ) ); ?>>
<?php wp_body_open(); ?>
<div aria-hidden="true" class="intro-loader">
<span class="intro-petal p1"></span><span class="intro-petal p2"></span><span class="intro-petal p3"></span><span class="intro-petal p4"></span><span class="intro-petal p5"></span>
<div class="intro-mark">
<img alt="VAVA Living" src="<?php echo esc_url( vava_asset_uri( 'assets/images/vava-logo.png' ) ); ?>"/>
<div class="intro-line"></div>
<div class="intro-text"><?php echo esc_html( $labels['intro'] ); ?></div>
</div>
</div>
<div class="app" id="app">
<header class="fixed-header compact" id="header">
<a aria-label="<?php echo esc_attr( $labels['home_aria'] ); ?>" class="home-logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img alt="VAVA Living logo" class="logo" src="<?php echo esc_url( vava_asset_uri( 'assets/images/vava-logo.png' ) ); ?>"/></a>
<button aria-label="<?php echo esc_attr( $labels['menu_aria'] ); ?>" class="burger" id="burger" type="button"><span></span></button>
<nav id="nav"><?php
// Keep the homepage navbar identical to internal pages: same WordPress-managed
// order, same bilingual/mixed labels (e.g. "عن VAVA"), and same real page URLs.
if ( function_exists( 'vava_render_internal_header_menu' ) ) {
	vava_render_internal_header_menu( $lang, 'home' );
} else {
	?>
<a class="active" href="<?php echo esc_url( function_exists( 'vava_language_url' ) ? vava_language_url( $lang, home_url( '/' ) ) : home_url( '/' ) ); ?>"><?php echo esc_html( $labels['home'] ); ?></a><a href="<?php echo esc_url( function_exists( 'vava_client_page_url' ) ? vava_client_page_url( 'about' ) : vava_page_url( 'about-vava' ) ); ?>"><?php echo esc_html( $labels['about'] ); ?></a><a href="<?php echo esc_url( function_exists( 'vava_client_page_url' ) ? vava_client_page_url( 'paths' ) : vava_page_url( 'paths-vava' ) ); ?>"><?php echo esc_html( $labels['paths'] ); ?></a><a href="<?php echo esc_url( function_exists( 'vava_client_page_url' ) ? vava_client_page_url( 'selections' ) : home_url( '/' ) ); ?>"><?php echo esc_html( $labels['shop'] ); ?></a><a href="<?php echo esc_url( function_exists( 'vava_client_page_url' ) ? vava_client_page_url( 'journal' ) : vava_page_url( 'journal' ) ); ?>"><?php echo esc_html( $labels['journal'] ); ?></a><a href="<?php echo esc_url( function_exists( 'vava_client_page_url' ) ? vava_client_page_url( 'contact' ) : vava_page_url( 'contact' ) ); ?>"><?php echo esc_html( $labels['contact'] ); ?></a>
	<?php
}
?></nav>
<div class="header-tools">
<?php vava_render_language_switch( $labels['language_aria'] ); ?>
<?php if ( function_exists( 'vava_customer_header_icon' ) ) { vava_customer_header_icon( $lang ); } ?>
</div>
</header><button aria-label="<?php echo esc_attr( $labels['next_aria'] ); ?>" class="scroll-cue" id="scrollCue" type="button"><span class="scroll-cue__mouse"><span></span></span><small><?php echo esc_html( $labels['discover'] ); ?></small></button>
<div class="progress" id="progress"></div>
<div class="scroll-hint"><span class="mouse"></span><span><?php echo esc_html( $labels['scroll_hint'] ); ?></span></div>
<main class="stage" id="stage">
