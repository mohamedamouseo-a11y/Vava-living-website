<?php
/**
 * Template Name: VAVA — Protected Digital Reader (AR / EN)
 * Template Post Type: page
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

$lang      = vava_current_language();
$is_en     = 'en' === $lang;
$uid       = isset( $_GET['product'] ) ? sanitize_key( wp_unslash( $_GET['product'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$product   = function_exists( 'vava_digital_product_data' ) ? vava_digital_product_data( $uid, $lang ) : array();
$user      = function_exists( 'vava_customer_current_verified_user' ) ? vava_customer_current_verified_user() : null;
$record    = function_exists( 'vava_digital_products_file_record' ) ? vava_digital_products_file_record( $uid ) : array();
$file_ok   = function_exists( 'vava_digital_products_private_file_path' ) && '' !== vava_digital_products_private_file_path( $record );
$ready     = $file_ok && 'ready' === (string) ( $record['processing_status'] ?? '' ) && absint( $record['page_count'] ?? 0 ) > 0;
$can_view  = $user instanceof WP_User && function_exists( 'vava_digital_products_user_can_view' ) && vava_digital_products_user_can_view( $user->ID, $uid );
$order_id  = $can_view && function_exists( 'vava_digital_products_latest_order' ) ? vava_digital_products_latest_order( $uid, $user->ID ) : 0;
$access_mode = function_exists( 'vava_digital_access_mode' ) ? vava_digital_access_mode( $uid ) : 'view';
$print_url = $can_view && $ready && function_exists( 'vava_digital_access_url' ) ? vava_digital_access_url( $uid, 'print', $user ) : '';
$download_url = $can_view && $ready && function_exists( 'vava_digital_access_url' ) ? vava_digital_access_url( $uid, 'download', $user ) : '';
/* Authorized purchasers can read an unpublished product without listing it publicly. */
if ( ! $product && $can_view ) {
	$selections_id = function_exists( 'vava_selections_page_id' ) ? absint( vava_selections_page_id() ) : 0;
	$stored = $selections_id ? get_post_meta( $selections_id, '_vava_selections_products_' . $lang, true ) : array();
	foreach ( (array) ( $stored['digital'] ?? array() ) as $entry ) {
		if ( sanitize_key( (string) ( $entry['uid'] ?? '' ) ) !== $uid ) { continue; }
		$product = array(
			'title' => (string) ( $entry['title'] ?? '' ) ?: ( $is_en ? 'My digital product' : 'منتجي الرقمي' ),
			'category' => (string) ( $entry['category'] ?? '' ),
			'card_description' => (string) ( $entry['description'] ?? '' ),
			'cover_url' => function_exists( 'vava_digital_products_cover_url' ) ? vava_digital_products_cover_url( $uid ) : '',
		);
		break;
	}
}

$account_url = function_exists( 'vava_customer_account_url' ) ? vava_customer_account_url( $lang, array( 'view' => 'products' ) ) : home_url( '/' );
$cover_url = (string) ( $product['cover_url'] ?? '' );
$page_count = absint( $record['page_count'] ?? 0 );

$GLOBALS['vava_internal_body_classes'] = array( 'vava-protected-reader-page' );
$GLOBALS['vava_page_data_name'] = $is_en ? 'digital-reader-en.html' : 'digital-reader-ar.html';

get_header( 'page' );
?>
<main class="vava-protected-reader" dir="<?php echo esc_attr( $is_en ? 'ltr' : 'rtl' ); ?>" data-protected-reader data-product-uid="<?php echo esc_attr( $uid ); ?>" data-page-count="<?php echo esc_attr( (string) $page_count ); ?>">
	<section class="vava-protected-reader-head">
		<div class="container">
			<div class="vava-reader-title-row">
				<div><?php if ( ! empty( $product['category'] ) ) : ?><span><?php echo esc_html( (string) $product['category'] ); ?></span><?php endif; ?><h1><?php echo esc_html( (string) ( $product['title'] ?? ( $is_en ? 'Protected product' : 'المنتج المحمي' ) ) ); ?></h1><p><?php echo esc_html( $is_en ? 'Page-by-page protected viewing linked to your approved VAVA account.' : 'مشاهدة محمية صفحة بصفحة مرتبطة بحسابك المعتمد في VAVA.' ); ?></p></div>
				<div class="vava-reader-security"><i aria-hidden="true">◇</i><div><strong><?php echo esc_html( $access_mode === 'view' ? ( $is_en ? 'Viewing access only' : 'صلاحية مشاهدة فقط' ) : ( $is_en ? 'Your approved product access' : 'صلاحية استخدام المنتج بعد الشراء' ) ); ?></strong><small><?php echo esc_html( 'view' === $access_mode ? ( $is_en ? 'The original PDF is never sent to the browser.' : 'لا يتم إرسال ملف PDF الأصلي إلى المتصفح.' ) : ( $is_en ? 'Print and download options follow the permissions set by VAVA.' : 'تظهر الطباعة والتحميل حسب الصلاحيات التي تحددها إدارة VAVA.' ) ); ?></small></div></div>
			</div>
		</div>
	</section>

	<section class="vava-protected-reader-shell">
		<div class="container">
			<?php if ( ! $product ) : ?>
				<div class="vava-reader-state is-error"><h2><?php echo esc_html( $is_en ? 'Product not found' : 'المنتج غير موجود' ); ?></h2><a href="<?php echo esc_url( $account_url ); ?>"><?php echo esc_html( $is_en ? 'Return to your library' : 'العودة إلى المكتبة' ); ?></a></div>
			<?php elseif ( ! $user instanceof WP_User ) : ?>
				<div class="vava-reader-state is-locked"><span aria-hidden="true">⌁</span><h2><?php echo esc_html( $is_en ? 'Sign in to continue' : 'سجّل الدخول للمتابعة' ); ?></h2><p><?php echo esc_html( $is_en ? 'Use the verified account linked to your approved order.' : 'استخدم الحساب الموثق المرتبط بطلبك المعتمد.' ); ?></p><a href="<?php echo esc_url( $account_url ); ?>"><?php echo esc_html( $is_en ? 'Customer login' : 'دخول العملاء' ); ?></a></div>
			<?php elseif ( ! $can_view ) : ?>
				<div class="vava-reader-state is-locked"><span aria-hidden="true">⌁</span><h2><?php echo esc_html( $is_en ? 'Access is not active yet' : 'صلاحية المشاهدة غير مفعّلة بعد' ); ?></h2><p><?php echo esc_html( $is_en ? 'The product appears here after the VAVA team approves the bank transfer.' : 'يظهر المنتج هنا بعد اعتماد التحويل البنكي من فريق VAVA.' ); ?></p><a href="<?php echo esc_url( $account_url ); ?>"><?php echo esc_html( $is_en ? 'Check order status' : 'متابعة حالة الطلب' ); ?></a></div>
			<?php elseif ( ! $file_ok ) : ?>
				<div class="vava-reader-state is-pending"><span aria-hidden="true">⇧</span><h2><?php echo esc_html( $is_en ? 'The protected file has not been uploaded yet' : 'لم يتم رفع الملف المحمي بعد' ); ?></h2><p><?php echo esc_html( $is_en ? 'The VAVA team can upload it from the product editor in VAVA Selections.' : 'يمكن لفريق VAVA رفعه من داخل بطاقة المنتج في صفحة مختارات VAVA بلوحة التحكم.' ); ?></p></div>
			<?php elseif ( ! $ready ) : ?>
				<div class="vava-reader-state is-pending"><span aria-hidden="true">◷</span><h2><?php echo esc_html( $is_en ? 'The protected pages are being prepared' : 'جارٍ تجهيز صفحات المشاهدة المحمية' ); ?></h2><p><?php echo esc_html( (string) ( $record['processing_message'] ?? ( $is_en ? 'Please try again shortly.' : 'يرجى المحاولة مرة أخرى بعد قليل.' ) ) ); ?></p><a href="<?php echo esc_url( $account_url ); ?>"><?php echo esc_html( $is_en ? 'Back to library' : 'العودة إلى المكتبة' ); ?></a></div>
			<?php else : ?>
				<div class="vava-reader-workspace">
					<aside class="vava-reader-product-card">
						<?php if ( $cover_url ) : ?><img src="<?php echo esc_url( $cover_url ); ?>" alt="<?php echo esc_attr( (string) $product['title'] ); ?>"/><?php endif; ?>
						<h2><?php echo esc_html( (string) $product['title'] ); ?></h2>
						<p><?php echo esc_html( (string) ( $product['card_description'] ?? '' ) ); ?></p>
						<div><span><?php echo esc_html( $is_en ? 'Account' : 'الحساب' ); ?></span><strong dir="ltr"><?php echo esc_html( $user->user_email ); ?></strong></div>
						<div><span><?php echo esc_html( $is_en ? 'Order' : 'الطلب' ); ?></span><strong>#<?php echo esc_html( (string) $order_id ); ?></strong></div>
						<div><span><?php echo esc_html( $is_en ? 'Pages' : 'الصفحات' ); ?></span><strong><?php echo esc_html( (string) $page_count ); ?></strong></div>
						<p class="vava-reader-watermark-note"><span aria-hidden="true">◎</span><?php echo esc_html( $is_en ? 'The VAVA logo, your account, and order number are watermarked on every page.' : 'يظهر شعار VAVA وبيانات حسابك ورقم الطلب كعلامة مائية على كل صفحة.' ); ?></p>
					</aside>
					<section class="vava-reader-frame-card" data-vava-canvas-reader>
						<header class="vava-reader-brand-strip">
							<div><span class="vava-reader-live-dot"></span><strong><?php echo esc_html( $is_en ? 'Protected VAVA reader' : 'قارئ VAVA المحمي' ); ?></strong></div>
						</header>
						<div class="vava-reader-frame-wrap" data-reader-frame-wrap oncontextmenu="return false">
							<div class="vava-reader-loading" data-reader-loading><span></span><strong><?php echo esc_html( $is_en ? 'Loading protected page…' : 'جارٍ تحميل الصفحة المحمية…' ); ?></strong></div>
							<canvas data-reader-canvas aria-label="<?php echo esc_attr( (string) $product['title'] ); ?>"></canvas>
							<div class="vava-reader-error" data-reader-error hidden></div>
						</div>
						<footer class="vava-reader-control-dock">
							<a class="vava-reader-library-button" href="<?php echo esc_url( $account_url ); ?>"><span aria-hidden="true">↩</span><?php echo esc_html( $is_en ? 'Back to library' : 'العودة إلى المكتبة' ); ?></a>
							<div class="vava-reader-page-controls"><button type="button" data-reader-prev><?php echo esc_html( $is_en ? 'Previous' : 'السابق' ); ?></button><span><b data-reader-current>1</b> / <b><?php echo esc_html( (string) $page_count ); ?></b></span><button type="button" data-reader-next><?php echo esc_html( $is_en ? 'Next' : 'التالي' ); ?></button></div>
							<div class="vava-reader-zoom-controls"><button type="button" data-reader-zoom-out aria-label="<?php echo esc_attr( $is_en ? 'Zoom out' : 'تصغير' ); ?>">−</button><span data-reader-zoom-label>100%</span><button type="button" class="vava-reader-fit-page" data-reader-fit-page title="<?php echo esc_attr( $is_en ? 'Fit the full page within the reader' : 'عرض الصفحة كاملة داخل القارئ' ); ?>"><?php echo esc_html( $is_en ? 'Fit page' : 'ملاءمة الصفحة' ); ?></button><button type="button" data-reader-zoom-in aria-label="<?php echo esc_attr( $is_en ? 'Zoom in' : 'تكبير' ); ?>">+</button></div>
							<button class="vava-reader-fullscreen-button" type="button" data-reader-fullscreen data-enter-label="<?php echo esc_attr( $is_en ? 'Full screen' : 'ملء الشاشة' ); ?>" data-exit-label="<?php echo esc_attr( $is_en ? 'Close' : 'إغلاق' ); ?>"><?php echo esc_html( $is_en ? 'Full screen' : 'ملء الشاشة' ); ?></button>
						</footer>
						<?php if ( $print_url || $download_url ) : ?>
						<div class="vava-reader-file-actions" aria-label="<?php echo esc_attr( $is_en ? 'Purchased PDF options' : 'خيارات المنتج الرقمي المشتَرى' ); ?>">
							<?php if ( $print_url ) : ?><a class="vava-reader-print-action" href="<?php echo esc_url( $print_url ); ?>" target="_blank" rel="noopener noreferrer"><span aria-hidden="true">▤</span><?php echo esc_html( $is_en ? 'Open PDF to print' : 'فتح PDF للطباعة' ); ?></a><?php endif; ?>
							<?php if ( $download_url ) : ?><a class="vava-reader-download-action" href="<?php echo esc_url( $download_url ); ?>"><span aria-hidden="true">⇩</span><?php echo esc_html( $is_en ? 'Download PDF' : 'تحميل ملف PDF' ); ?></a><?php endif; ?>
							<p><?php echo esc_html( $is_en ? 'Print using your device PDF viewer. A downloaded or printed copy can be saved outside VAVA.' : 'يمكنك الطباعة من عارض PDF في جهازك. النسخ المطبوعة أو المحملة يمكن الاحتفاظ بها خارج VAVA.' ); ?></p>
						</div>
						<?php endif; ?>
					</section>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php get_footer( 'page' ); ?>
