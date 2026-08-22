<?php
/**
 * Template Name: VAVA — Digital Product (AR / EN)
 * Template Post Type: page
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

$page_id = get_queried_object_id();
$lang    = vava_current_language();
$is_en   = 'en' === $lang;
$uid     = vava_digital_product_uid_for_page( $page_id );
$product = vava_digital_product_data( $uid, $lang );

if ( ! $product ) {
	status_header( 404 );
	nocache_headers();
}

$catalog        = vava_digital_products_catalog();
$cover_url      = (string) ( $product['cover_url'] ?? '' );
$selections     = get_page_by_path( 'vava-selections', OBJECT, 'page' );
$selections_url = $selections instanceof WP_Post ? vava_localized_page_url( (int) $selections->ID, $lang ) : vava_language_url( $lang, home_url( '/' ) );
$format_label   = 'PDF';
$language_value = $is_en ? 'Arabic' : 'العربية';
$usage_value    = $is_en ? 'Personal use' : 'استخدام شخصي';
$pages_value    = trim( (string) ( $product['pages'] ?? '' ) );

$labels = $is_en ? array(
	'back'        => 'Back to digital products',
	'answer'      => 'The question this guide answers',
	'inside'      => 'What you will find inside',
	'ideal'       => 'Ideal for you if you',
	'details'     => 'Product details',
	'format'      => 'Format',
	'language'    => 'Content language',
	'pages'       => 'Pages',
	'usage'       => 'License',
	'currency'    => 'SAR',
	'rights'      => 'This digital guide is intended for personal use only. It may not be copied, republished, modified, distributed, or resold without prior written permission from VAVA Living.',
	'disclaimer'  => 'This content is educational and is not a substitute for an individual assessment or professional consultation when needed.',
) : array(
	'back'        => 'العودة إلى المنتجات الرقمية',
	'answer'      => 'السؤال الذي يجيب عنه الدليل',
	'inside'      => 'ماذا ستجد داخل الدليل؟',
	'ideal'       => 'مناسب لك إذا كنت',
	'details'     => 'تفاصيل المنتج',
	'format'      => 'الصيغة',
	'language'    => 'لغة المحتوى',
	'pages'       => 'عدد الصفحات',
	'usage'       => 'نوع الاستخدام',
	'currency'    => 'ر.س',
	'rights'      => 'هذا الدليل الرقمي معد للاستخدام الشخصي فقط، ولا يجوز نسخه أو إعادة نشره أو تعديله أو توزيعه أو إعادة بيعه دون الحصول على إذن كتابي مسبق من VAVA Living.',
	'disclaimer'  => 'تم إعداد هذا المحتوى لأغراض تثقيفية، ولا يعد بديلًا عن التقييم أو الاستشارة الفردية عند الحاجة.',
);

$GLOBALS['vava_page_data_name']        = $is_en ? 'store-en.html' : 'store.html';
$GLOBALS['vava_active_nav']            = 'selections';
$GLOBALS['vava_internal_body_classes'] = array( 'store-page', 'vava-digital-product-page', 'vava-digital-product-' . sanitize_html_class( $uid ) );
get_header( 'page' );
?>
<main>
	<span class="blob sage"></span><span class="blob cream"></span><span class="leaf-line vava-inline-store-1"></span>

	<section class="section vava-digital-product-hero">
		<div class="container">
			<a class="vava-product-back" href="<?php echo esc_url( $selections_url ); ?>#vava-selection-panel-digital">
				<span aria-hidden="true"><?php echo $is_en ? '←' : '→'; ?></span>
				<?php echo esc_html( $labels['back'] ); ?>
			</a>

			<div class="vava-product-detail-grid">
				<div class="vava-product-copy">
					<span class="eyebrow"><?php echo esc_html( (string) ( $product['category'] ?? '' ) ); ?></span>
					<h1><?php echo esc_html( (string) ( $product['title'] ?? '' ) ); ?></h1>
					<p class="vava-product-description"><?php echo esc_html( (string) ( $product['description'] ?? '' ) ); ?></p>

					<div class="vava-product-question">
						<span><?php echo esc_html( $labels['answer'] ); ?></span>
						<strong><?php echo esc_html( (string) ( $product['question'] ?? '' ) ); ?></strong>
					</div>
				</div>

				<aside class="vava-product-summary">
					<?php if ( $cover_url ) : ?>
						<div class="vava-product-cover"><img src="<?php echo esc_url( $cover_url ); ?>" alt="<?php echo esc_attr( (string) ( $product['title'] ?? '' ) ); ?>"/></div>
					<?php endif; ?>
					<div class="vava-product-price"><strong><?php echo esc_html( (string) ( $product['price'] ?? '' ) ); ?></strong><span><?php echo esc_html( $labels['currency'] ); ?></span></div>
					<?php if ( function_exists( 'vava_digital_products_render_purchase_action' ) ) { vava_digital_products_render_purchase_action( array_merge( $product, array( 'uid' => $uid ) ), $lang ); } ?>
					<a class="btn secondary vava-product-return" href="<?php echo esc_url( $selections_url ); ?>#vava-selection-panel-digital"><?php echo esc_html( $labels['back'] ); ?></a>
				</aside>
			</div>
		</div>
	</section>

	<section class="section vava-product-content-section">
		<div class="container">
			<div class="vava-product-content-grid">
				<article class="vava-product-content-card">
					<h2><?php echo esc_html( $labels['inside'] ); ?></h2>
					<ul class="vava-product-list">
						<?php foreach ( (array) ( $product['inside'] ?? array() ) as $item ) : ?>
							<li><?php echo esc_html( (string) $item ); ?></li>
						<?php endforeach; ?>
					</ul>
				</article>

				<article class="vava-product-content-card">
					<h2><?php echo esc_html( $labels['ideal'] ); ?></h2>
					<ul class="vava-product-list">
						<?php foreach ( (array) ( $product['ideal'] ?? array() ) as $item ) : ?>
							<li><?php echo esc_html( (string) $item ); ?></li>
						<?php endforeach; ?>
					</ul>
				</article>
			</div>

			<div class="vava-product-details-card">
				<h2><?php echo esc_html( $labels['details'] ); ?></h2>
				<div class="vava-product-facts">
					<div><span><?php echo esc_html( $labels['format'] ); ?></span><strong><?php echo esc_html( $format_label ); ?></strong></div>
					<div><span><?php echo esc_html( $labels['language'] ); ?></span><strong><?php echo esc_html( $language_value ); ?></strong></div>
					<?php if ( '' !== $pages_value ) : ?><div><span><?php echo esc_html( $labels['pages'] ); ?></span><strong><?php echo esc_html( $pages_value ); ?></strong></div><?php endif; ?>
					<div><span><?php echo esc_html( $labels['usage'] ); ?></span><strong><?php echo esc_html( $usage_value ); ?></strong></div>
				</div>
				<p class="vava-product-rights"><?php echo esc_html( $labels['rights'] ); ?></p>
				<p class="vava-product-disclaimer"><?php echo esc_html( $labels['disclaimer'] ); ?></p>
			</div>
		</div>
	</section>
</main>
<?php get_footer( 'page' ); ?>
