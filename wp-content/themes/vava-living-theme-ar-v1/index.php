<?php
/**
 * Temporary fallback while internal pages are converted one by one.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>"/>
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<main style="max-width:760px;margin:100px auto;padding:24px;text-align:center;font-family:Arial,sans-serif;direction:rtl">
	<h1><?php esc_html_e( 'قالب هذه الصفحة لم يتم تحويله بعد.', 'vava-living' ); ?></h1>
	<p><?php esc_html_e( 'يتم تحويل الصفحات الداخلية بقوالب مستقلة صفحة بعد صفحة.', 'vava-living' ); ?></p>
	<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'العودة إلى الصفحة الرئيسية', 'vava-living' ); ?></a></p>
</main>
<?php wp_footer(); ?>
</body>
</html>
