<?php
/**
 * Template Name: VAVA — Selections (AR / EN)
 * Template Post Type: page
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

$page_id  = get_queried_object_id();
$lang     = vava_current_language();
$text     = vava_selections_text_data( $page_id, $lang );
$shared   = vava_selections_shared_data( $page_id );
$hero     = (array) ( $text['hero'] ?? array() );
$hero_img = vava_selections_image_url( absint( $shared['hero_image_id'] ?? 0 ), 'assets/images/store-2.png' );

$GLOBALS['vava_page_data_name']       = 'en' === $lang ? 'store-en.html' : 'store.html';
$GLOBALS['vava_active_nav']           = 'selections';
$GLOBALS['vava_internal_body_classes'] = array( 'store-page', 'vava-selections-page' );
get_header( 'page' );
?>
<main>
	<span class="blob sage"></span><span class="blob cream"></span><span class="leaf-line vava-inline-store-1"></span>
	<section class="section vava-selections-hero" id="selections-hero">
		<div class="container hero-grid">
			<div class="hero-copy">
				<div class="eyebrow"><?php echo esc_html( (string) ( $hero['eyebrow'] ?? '' ) ); ?></div>
				<h1><?php echo esc_html( (string) ( $hero['title'] ?? '' ) ); ?></h1>
				<p><?php echo esc_html( (string) ( $hero['intro'] ?? '' ) ); ?></p>
				<p class="small"><?php echo esc_html( (string) ( $hero['note'] ?? '' ) ); ?></p>
			</div>
			<div class="visual-card vava-selections-hero-visual" style="background-image:url('<?php echo esc_url( $hero_img ); ?>')"></div>
		</div>
	</section>

	<section class="section vava-selections-browser" id="vava-selections">
		<div class="container">
			<div class="vava-selection-blocks" data-vava-selection-blocks>
			<?php foreach ( array( 'digital', 'tangible' ) as $group ) :
				$collection = (array) ( $text['collections'][ $group ] ?? array() );
				$fallback   = 'digital' === $group ? 'assets/images/store-2.png' : 'assets/images/vava_shop_bottles.jpg';
				$image      = vava_selections_image_url( absint( $shared['collection_images'][ $group ] ?? 0 ), $fallback );
			?>
				<article class="vava-selection-block" data-selection-block="<?php echo esc_attr( $group ); ?>">
					<div class="vava-selection-block-image" style="background-image:url('<?php echo esc_url( $image ); ?>')" role="img" aria-label="<?php echo esc_attr( (string) ( $collection['title'] ?? '' ) ); ?>"></div>
					<div class="vava-selection-block-copy">
						<h2><?php echo esc_html( (string) ( $collection['title'] ?? '' ) ); ?></h2>
						<p><?php echo esc_html( 'tangible' === $group ? ( 'en' === $lang ? 'Coming soon' : 'قريبًا' ) : (string) ( $collection['description'] ?? '' ) ); ?></p>
						<button aria-controls="vava-selection-panel-<?php echo esc_attr( $group ); ?>" aria-expanded="false" class="btn <?php echo 'digital' === $group ? 'primary' : 'secondary'; ?> vava-selection-toggle" data-selection-toggle="<?php echo esc_attr( $group ); ?>" type="button"><?php echo esc_html( (string) ( $collection['button_text'] ?? '' ) ); ?></button>
					</div>
				</article>
			<?php endforeach; ?>
			</div>

			<div class="vava-selection-panels">
			<?php foreach ( array( 'digital', 'tangible' ) as $group ) :
				$products   = vava_selections_products( $page_id, $group, $lang, true );
				$collection = (array) ( $text['collections'][ $group ] ?? array() );
				// VAVA_SELECTIONS_TANGIBLE_COMING_SOON_V1
				$is_tangible_coming_soon = 'tangible' === $group && empty( $products );
			?>
				<section aria-hidden="true" class="vava-selection-panel<?php echo $is_tangible_coming_soon ? ' is-tangible-coming-soon' : ''; ?>" data-selection-panel="<?php echo esc_attr( $group ); ?>" hidden id="vava-selection-panel-<?php echo esc_attr( $group ); ?>" tabindex="-1">
					<?php if ( ! $is_tangible_coming_soon ) : ?>
					<div class="vava-selection-panel-heading"><span class="eyebrow"><?php echo esc_html( (string) ( $collection['title'] ?? '' ) ); ?></span><h2><?php echo esc_html( (string) ( $collection['description'] ?? '' ) ); ?></h2></div>
					<?php endif; ?>
					<?php if ( $products ) : ?>
					<div class="product-grid<?php echo 'digital' === $group ? ' vava-digital-product-grid' : ''; ?>">
					<?php foreach ( $products as $product ) :
						$pdf_cover = 'digital' === $group && function_exists( 'vava_digital_products_cover_url' ) ? vava_digital_products_cover_url( (string) ( $product['uid'] ?? '' ), $page_id ) : '';
						$image = $pdf_cover ?: vava_selections_image_url( absint( $product['image_id'] ?? 0 ), (string) ( $product['fallback_asset'] ?? '' ) );
						$url   = vava_selections_product_url( $product, $lang, $page_id );
					?>
						<?php if ( 'digital' === $group ) : ?>
						<article class="product vava-digital-product-card">
							<div class="product-thumb vava-digital-product-cover"><?php if ( $image ) : ?><img alt="<?php echo esc_attr( (string) ( $product['title'] ?? '' ) ); ?>" src="<?php echo esc_url( $image ); ?>"/><?php else : ?><span>VAVA</span><?php endif; ?></div>
							<div class="vava-digital-product-card-body">
								<h3><?php echo esc_html( (string) ( $product['title'] ?? '' ) ); ?></h3>
								<p class="small"><?php echo esc_html( (string) ( $product['description'] ?? '' ) ); ?></p>
								<div class="product-meta vava-digital-product-card-meta">
									<a class="details-link vava-digital-product-card-button" data-vava-product-open data-product-uid="<?php echo esc_attr( (string) ( $product['uid'] ?? '' ) ); ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( (string) ( $product['button_text'] ?? '' ) ); ?></a>
									<span class="price-tag<?php echo '' === trim( (string) ( $product['price'] ?? '' ) ) ? ' price-text' : ''; ?>"><?php if ( '' !== trim( (string) ( $product['price'] ?? '' ) ) ) : ?><strong><?php echo esc_html( (string) $product['price'] ); ?></strong> <span><?php echo esc_html( (string) ( $product['currency'] ?? '' ) ); ?></span><?php else : ?><?php echo esc_html( (string) ( $product['currency'] ?? '' ) ); ?><?php endif; ?></span>
								</div>
							</div>
						</article>
						<?php else : ?>
						<article class="product">
							<div class="product-thumb"><?php if ( $image ) : ?><img alt="<?php echo esc_attr( (string) ( $product['title'] ?? '' ) ); ?>" src="<?php echo esc_url( $image ); ?>"/><?php endif; ?></div>
							<h3><?php echo esc_html( (string) ( $product['title'] ?? '' ) ); ?></h3>
							<p class="small"><?php echo esc_html( (string) ( $product['description'] ?? '' ) ); ?></p>
							<div class="product-meta">
								<span class="price-tag<?php echo '' === trim( (string) ( $product['price'] ?? '' ) ) ? ' price-text' : ''; ?>"><?php if ( '' !== trim( (string) ( $product['price'] ?? '' ) ) ) : ?><strong><?php echo esc_html( (string) $product['price'] ); ?></strong> <span><?php echo esc_html( (string) ( $product['currency'] ?? '' ) ); ?></span><?php else : ?><?php echo esc_html( (string) ( $product['currency'] ?? '' ) ); ?><?php endif; ?></span>
								<a class="details-link" data-vava-product-open data-product-uid="<?php echo esc_attr( (string) ( $product['uid'] ?? '' ) ); ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( (string) ( $product['button_text'] ?? '' ) ); ?></a>
							</div>
						</article>
						<?php endif; ?>
					<?php endforeach; ?>
					</div>
					<?php elseif ( $is_tangible_coming_soon ) : ?>
					<div class="vava-tangible-coming-soon" role="status" aria-live="polite">
						<?php
						$coming_soon_artwork_rel  = 'assets/images/vava-tangible-coming-soon-transparent-v2.webp';
						$coming_soon_artwork_path = get_theme_file_path( $coming_soon_artwork_rel );
						$coming_soon_artwork_url  = get_theme_file_uri( $coming_soon_artwork_rel );
						if ( is_readable( $coming_soon_artwork_path ) ) {
							$coming_soon_artwork_url = add_query_arg( 'ver', (string) filemtime( $coming_soon_artwork_path ), $coming_soon_artwork_url );
						}
						?>
						<div class="vava-tangible-coming-soon-visual" aria-hidden="true">
							<img alt="" decoding="async" loading="lazy" src="<?php echo esc_url( $coming_soon_artwork_url ); ?>"/>
						</div>
						<div class="vava-tangible-coming-soon-copy">
							<h2><?php echo esc_html( 'en' === $lang ? 'Coming soon' : 'قريبًا' ); ?></h2>
							<p><?php echo esc_html( 'en' === $lang ? 'We are preparing thoughtful tangible selections for you.' : 'نعمل على تجهيز مختارات ملموسة لكم' ); ?></p>
						</div>
					</div>
					<?php else : ?>
					<div class="vava-selection-empty"><?php echo esc_html( (string) ( $text['empty'][ $group ] ?? '' ) ); ?></div>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>
			</div>
		</div>
	</section>


	<?php
	$reader_products = array_merge(
		vava_selections_products( $page_id, 'digital', $lang, true ),
		vava_selections_products( $page_id, 'tangible', $lang, true )
	);
	?>
	<?php if ( $reader_products ) : ?>
	<div class="vava-product-reader" data-vava-product-reader hidden aria-hidden="true">
		<div class="vava-product-reader-backdrop" data-vava-product-reader-close aria-hidden="true"></div>
		<div class="vava-product-reader-dialog" data-vava-product-reader-dialog role="dialog" aria-modal="true" tabindex="-1">
			<?php foreach ( $reader_products as $reader_index => $reader_product ) :
				$reader_previous = $reader_index > 0 ? $reader_products[ $reader_index - 1 ] : array();
				$reader_next     = $reader_index + 1 < count( $reader_products ) ? $reader_products[ $reader_index + 1 ] : array();
				vava_digital_product_render_reader_article( $reader_product, $reader_previous, $reader_next, $lang );
			endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</main>
<?php get_footer( 'page' ); ?>
