<?php
/**
 * Unified VAVA customer account dashboard.
 *
 * Loaded from page-templates/my-bookings-vava.php while its account variables
 * are still in scope.
 *
 * @package VAVA_Living
 */
defined( 'ABSPATH' ) || exit;

$account_user   = $current_customer instanceof WP_User ? $current_customer : null;
$account_name   = $account_user ? ( $account_user->display_name ?: $account_user->user_email ) : (string) ( $context['email'] ?? '' );
$account_email  = $account_user ? $account_user->user_email : (string) ( $context['email'] ?? '' );
$active_section = 'products' === $view ? 'products' : 'bookings';
$impact_request_booking = isset( $_GET['impact_booking'] ) ? absint( wp_unslash( $_GET['impact_booking'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$account_url  = vava_customer_account_url( $lang, array( 'view' => 'overview' ) );
$bookings_url = vava_customer_account_url( $lang, array( 'view' => 'bookings' ) ) . '#vava-account-bookings';
$products_url = vava_customer_account_url( $lang, array( 'view' => 'products' ) ) . '#vava-account-products';
$profile_url  = vava_customer_account_url( $lang, array( 'view' => 'profile' ) );

$bookings_explore_url = get_permalink( 23 );
if ( ! $bookings_explore_url ) {
	$paths_pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-templates/paths-vava.php',
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	$bookings_explore_url = ! empty( $paths_pages[0] ) ? get_permalink( (int) $paths_pages[0] ) : '';
}
if ( ! $bookings_explore_url ) {
	$bookings_explore_url = function_exists( 'vava_page_url' ) ? vava_page_url( 'paths-vava' ) : home_url( '/' );
}
$products_explore_url = get_permalink( 27 );
if ( ! $products_explore_url ) {
	$products_explore_url = function_exists( 'vava_page_url' ) ? vava_page_url( 'vava-selections' ) : home_url( '/' );
}

$active_bookings = count(
	array_filter(
		$records,
		static function ( array $record ): bool {
			return in_array( (string) ( $record['status'] ?? '' ), array( 'confirmed', 'paid' ), true );
		}
	)
);
$completed_bookings = count(
	array_filter(
		$records,
		static function ( array $record ): bool {
			return 'completed' === (string) ( $record['status'] ?? '' );
		}
	)
);
$review_bookings = count(
	array_filter(
		$records,
		static function ( array $record ): bool {
			return 'review' === (string) ( $record['bucket'] ?? '' );
		}
	)
);
$cancelled_bookings = count(
	array_filter(
		$records,
		static function ( array $record ): bool {
			return 'rejected' === (string) ( $record['bucket'] ?? '' );
		}
	)
);

$available_products = count(
	array_filter(
		$digital_records,
		static function ( array $record ): bool {
			return 'active' === (string) ( $record['access'] ?? '' ) && 'paid' === (string) ( $record['payment'] ?? '' );
		}
	)
);
$review_products = count(
	array_filter(
		$digital_records,
		static function ( array $record ): bool {
			return ! ( 'active' === (string) ( $record['access'] ?? '' ) && 'paid' === (string) ( $record['payment'] ?? '' ) )
				&& ! in_array( (string) ( $record['payment'] ?? '' ), array( 'rejected', 'failed', 'cancelled' ), true )
				&& ! in_array( (string) ( $record['access'] ?? '' ), array( 'rejected', 'revoked' ), true );
		}
	)
);
$unavailable_products = count( $digital_records ) - $available_products - $review_products;
$unavailable_products = max( 0, $unavailable_products );

$format_date = static function ( string $date ) use ( $is_en ): string {
	if ( '' === trim( $date ) ) {
		return '—';
	}
	$timestamp = strtotime( $date );
	if ( ! $timestamp ) {
		return $date;
	}
	return wp_date( $is_en ? 'M j, Y' : 'Y-m-d', $timestamp );
};

$product_status = static function ( array $record ) use ( $is_en ): array {
	$payment = (string) ( $record['payment'] ?? '' );
	$access  = (string) ( $record['access'] ?? 'pending' );
	if ( 'active' === $access && 'paid' === $payment ) {
		return array( 'available', $is_en ? 'Available' : 'متاح', '✓' );
	}
	if ( in_array( $payment, array( 'rejected', 'failed', 'cancelled' ), true ) || 'rejected' === $access ) {
		return array( 'rejected', $is_en ? 'Not approved' : 'غير معتمد', '×' );
	}
	if ( 'revoked' === $access ) {
		return array( 'paused', $is_en ? 'Access paused' : 'الوصول موقوف', '!' );
	}
	return array( 'review', $is_en ? 'Under review' : 'قيد المراجعة', '◷' );
};
?>

<?php if ( $receipt_updated ) : ?><div class="vava-my-bookings-notice is-success"><?php echo esc_html( $is_en ? 'The new receipt was uploaded successfully.' : 'تم رفع الإيصال الجديد بنجاح.' ); ?></div><?php endif; ?>
<?php if ( $receipt_error ) : ?><div class="vava-my-bookings-notice is-error"><?php echo esc_html( $receipt_error ); ?></div><?php endif; ?>
<?php if ( $customer_cancelled ) : ?><div class="vava-my-bookings-notice is-success"><?php echo esc_html( $is_en ? 'The booking was cancelled.' : 'تم إلغاء الحجز.' ); ?></div><?php endif; ?>
<?php if ( $cancellation_requested ) : ?><div class="vava-my-bookings-notice is-success"><?php echo esc_html( $is_en ? 'Your cancellation request was sent for review.' : 'تم إرسال طلب الإلغاء للمراجعة.' ); ?></div><?php endif; ?>
<?php if ( ! empty( $_GET['impact_submitted'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?><div class="vava-my-bookings-notice is-success"><?php echo esc_html( $is_en ? 'Your journey impact questionnaire was submitted successfully.' : 'تم إرسال استبيان أثر الرحلة بنجاح.' ); ?></div><?php endif; ?>

<div class="vava-account-dashboard" data-vava-account-dashboard data-active-section="<?php echo esc_attr( $active_section ); ?>">
	<div class="vava-account-dashboard-grid">
		<main class="vava-account-dashboard-main">
			<nav class="vava-account-tabs" aria-label="<?php echo esc_attr( $is_en ? 'Account sections' : 'أقسام الحساب' ); ?>">
				<a class="<?php echo 'bookings' === $active_section ? 'is-active' : ''; ?>" href="<?php echo esc_url( $bookings_url ); ?>"<?php echo 'bookings' === $active_section ? ' aria-current="page"' : ''; ?>><span>▣</span><?php echo esc_html( $is_en ? 'Bookings' : 'الحجوزات' ); ?></a>
				<a class="<?php echo 'products' === $active_section ? 'is-active' : ''; ?>" href="<?php echo esc_url( $products_url ); ?>"<?php echo 'products' === $active_section ? ' aria-current="page"' : ''; ?>><span>▤</span><?php echo esc_html( $is_en ? 'Digital products' : 'المنتجات الرقمية' ); ?></a>
			</nav>

			<?php if ( 'bookings' === $active_section ) : ?>
				<?php if ( $records ) : ?><section class="vava-account-stats is-bookings" aria-label="<?php echo esc_attr( $is_en ? 'Booking filters' : 'فلاتر الحجوزات' ); ?>" data-account-filter-group="bookings">
					<button type="button" class="is-active" data-account-filter="all" aria-pressed="true"><span>▣</span><div><small><?php echo esc_html( $is_en ? 'Total bookings' : 'إجمالي الحجوزات' ); ?></small><strong><?php echo esc_html( (string) count( $records ) ); ?></strong><em><?php echo esc_html( $is_en ? 'bookings' : 'حجز' ); ?></em></div></button>
					<button type="button" data-account-filter="active" aria-pressed="false"><span>✓</span><div><small><?php echo esc_html( $is_en ? 'Active bookings' : 'الحجوزات النشطة' ); ?></small><strong><?php echo esc_html( (string) $active_bookings ); ?></strong><em><?php echo esc_html( $is_en ? 'bookings' : 'حجوزات' ); ?></em></div></button>
					<button type="button" data-account-filter="completed" aria-pressed="false"><span>★</span><div><small><?php echo esc_html( $is_en ? 'Completed' : 'الحجوزات المكتملة' ); ?></small><strong><?php echo esc_html( (string) $completed_bookings ); ?></strong><em><?php echo esc_html( $is_en ? 'bookings' : 'حجوزات' ); ?></em></div></button>
					<button type="button" data-account-filter="review" aria-pressed="false"><span>◷</span><div><small><?php echo esc_html( $is_en ? 'Under review' : 'قيد المراجعة' ); ?></small><strong><?php echo esc_html( (string) $review_bookings ); ?></strong><em><?php echo esc_html( $is_en ? 'bookings' : 'حجوزات' ); ?></em></div></button>
					<button type="button" data-account-filter="cancelled" aria-pressed="false"><span>×</span><div><small><?php echo esc_html( $is_en ? 'Cancelled' : 'ملغاة أو مرفوضة' ); ?></small><strong><?php echo esc_html( (string) $cancelled_bookings ); ?></strong><em><?php echo esc_html( $is_en ? 'bookings' : 'حجوزات' ); ?></em></div></button>
				</section><?php endif; ?>

				<section id="vava-account-bookings" class="vava-account-collection vava-account-bookings-section">
					<header class="vava-account-section-heading"><div><small><?php echo esc_html( $is_en ? 'Your appointments' : 'مواعيدك القادمة والسابقة' ); ?></small><h2><?php echo esc_html( $is_en ? 'My bookings' : 'حجوزاتي' ); ?></h2></div></header>
					<?php if ( ! $records ) : ?>
						<div class="vava-account-empty"><span>▣</span><h3><?php echo esc_html( $is_en ? 'No bookings yet' : 'لا توجد حجوزات حتى الآن' ); ?></h3><p><?php echo esc_html( $is_en ? 'Your future bookings will appear here automatically.' : 'ستظهر حجوزاتك القادمة هنا تلقائيًا.' ); ?></p></div>
					<?php else : ?>
						<div id="vava-account-bookings-list" class="vava-account-card-list">
							<?php foreach ( $records as $index => $record ) :
								$panel_id = 'vava-booking-order-details-' . absint( $record['id'] );
								$filter_key = 'other';
								if ( 'rejected' === (string) ( $record['bucket'] ?? '' ) ) { $filter_key = 'cancelled'; }
								elseif ( 'review' === (string) ( $record['bucket'] ?? '' ) ) { $filter_key = 'review'; }
								elseif ( 'completed' === (string) ( $record['status'] ?? '' ) ) { $filter_key = 'completed'; }
								elseif ( in_array( (string) ( $record['status'] ?? '' ), array( 'confirmed', 'paid' ), true ) ) { $filter_key = 'active'; }
								?>
								<article class="vava-account-order-card is-booking<?php echo 0 === $index ? ' is-featured' : ''; ?>" id="vava-booking-<?php echo esc_attr( (string) $record['id'] ); ?>" data-account-card data-account-filter-key="<?php echo esc_attr( $filter_key ); ?>">
									<figure><?php if ( $record['image_url'] ) : ?><img src="<?php echo esc_url( $record['image_url'] ); ?>" alt="<?php echo esc_attr( $record['title'] ); ?>"/><?php else : ?><span>VAVA</span><?php endif; ?><b class="is-<?php echo esc_attr( $record['bucket'] ); ?>"><?php echo esc_html( $record['status_label'] ); ?></b></figure>
									<div class="vava-account-order-copy">
										<div class="vava-account-order-number"><small><?php echo esc_html( $is_en ? 'Booking number' : 'رقم الحجز' ); ?></small><strong>#<?php echo esc_html( (string) $record['id'] ); ?></strong></div>
										<h3><?php echo esc_html( $record['title'] ); ?></h3>
										<?php if ( $record['description'] ) : ?><p><?php echo esc_html( $record['description'] ); ?></p><?php endif; ?>
										<div class="vava-account-order-facts">
											<span><small><?php echo esc_html( $is_en ? 'Date' : 'التاريخ' ); ?></small><b dir="ltr"><?php echo esc_html( $format_date( $record['date'] ) ); ?></b></span>
											<span><small><?php echo esc_html( $is_en ? 'Time' : 'الوقت' ); ?></small><b dir="ltr"><?php echo esc_html( $record['time'] ?: '—' ); ?></b></span>
											<span><small><?php echo esc_html( $is_en ? 'Duration' : 'المدة' ); ?></small><b><?php echo esc_html( function_exists( 'vava_booking_display_duration_for_booking' ) ? vava_booking_display_duration_for_booking( (int) $record['id'], $lang ) : ( $record['duration'] ?: '—' ) ); ?></b></span>
											<span><small><?php echo esc_html( $is_en ? 'Payment' : 'طريقة الدفع' ); ?></small><b><?php echo esc_html( $record['method_label'] ?: '—' ); ?></b></span>
										</div>
										<?php
										$impact_eligible = function_exists( 'vava_booking_questionnaire_impact_eligible' ) && vava_booking_questionnaire_impact_eligible( absint( $record['id'] ) );
										$impact_open     = $impact_eligible && absint( $impact_request_booking ) === absint( $record['id'] );
										$impact_url      = '';
										if ( $impact_eligible ) {
											$impact_url = add_query_arg( array( 'view' => 'bookings', 'impact_booking' => absint( $record['id'] ) ), vava_customer_account_url( $lang ) );
											if ( $token ) { $impact_url = add_query_arg( 'vava_magic', rawurlencode( $token ), $impact_url ); }
											$impact_url .= '#vava-impact-questionnaire-' . absint( $record['id'] );
										}
										?>
										<div class="vava-account-order-actions">
											<button type="button" class="vava-account-secondary-action" data-account-details-toggle aria-expanded="<?php echo $impact_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $panel_id ); ?>"><?php echo esc_html( $is_en ? 'View details' : 'عرض التفاصيل' ); ?></button>
											<?php if ( $impact_eligible ) : ?><a class="vava-account-impact-action" href="<?php echo esc_url( $impact_url ); ?>"><?php echo esc_html( $is_en ? 'Journey impact questionnaire' : 'تعبئة استبيان أثر الرحلة' ); ?></a><?php elseif ( $record['cancel_mode'] ) : ?><button type="button" class="vava-account-primary-action" data-cancel-booking="<?php echo esc_attr( (string) $record['id'] ); ?>" data-cancel-mode="<?php echo esc_attr( $record['cancel_mode'] ); ?>"><?php echo esc_html( $is_en ? 'Manage booking' : 'إدارة الحجز' ); ?></button><?php elseif ( '#' !== $record['detail_url'] ) : ?><a class="vava-account-primary-action" href="<?php echo esc_url( $record['detail_url'] ); ?>"><?php echo esc_html( $is_en ? 'Service details' : 'تفاصيل الخدمة' ); ?></a><?php endif; ?>
										</div>
										<div id="<?php echo esc_attr( $panel_id ); ?>" class="vava-account-order-details<?php echo $impact_open ? ' is-impact-open' : ''; ?>" data-account-details-panel<?php echo $impact_open ? '' : ' hidden'; ?>>
											<div><small><?php echo esc_html( $is_en ? 'Booking status' : 'حالة الحجز' ); ?></small><b><?php echo esc_html( $record['status_label'] ); ?></b></div>
											<div><small><?php echo esc_html( $is_en ? 'Payment status' : 'حالة الدفع' ); ?></small><b><?php echo esc_html( $record['payment_status_label'] ); ?></b></div>
											<div><small><?php echo esc_html( $is_en ? 'Price' : 'السعر' ); ?></small><b><?php echo esc_html( $record['price'] ); ?></b></div>
											<div><small><?php echo esc_html( $is_en ? 'Customer' : 'العميل' ); ?></small><b><?php echo esc_html( (string) ( $record['customer']['name'] ?? $account_name ) ); ?></b></div>
											<?php if ( $record['receipt_url'] ) : ?><a href="<?php echo esc_url( $record['receipt_url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $is_en ? 'View payment receipt' : 'عرض إثبات التحويل' ); ?></a><?php endif; ?>
											<?php if ( function_exists( 'vava_booking_questionnaire_render_impact_form' ) ) { vava_booking_questionnaire_render_impact_form( absint( $record['id'] ), $lang, (string) $token ); } ?>
										</div>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
						<div class="vava-account-filter-empty" data-account-filter-empty hidden><span>◌</span><h3><?php echo esc_html( $is_en ? 'No bookings match this filter' : 'لا توجد حجوزات مطابقة لهذا الفلتر' ); ?></h3></div>
						<nav class="vava-account-pagination" data-account-pagination aria-label="<?php echo esc_attr( $is_en ? 'Booking pages' : 'صفحات الحجوزات' ); ?>"></nav>
					<?php endif; ?>
				</section>
			<?php else : ?>
				<section class="vava-account-stats is-products" aria-label="<?php echo esc_attr( $is_en ? 'Digital product filters' : 'فلاتر المنتجات الرقمية' ); ?>" data-account-filter-group="products">
					<button type="button" class="is-active" data-account-filter="all" aria-pressed="true"><span>▤</span><div><small><?php echo esc_html( $is_en ? 'Total products' : 'إجمالي المنتجات' ); ?></small><strong><?php echo esc_html( (string) count( $digital_records ) ); ?></strong><em><?php echo esc_html( $is_en ? 'products' : 'منتج' ); ?></em></div></button>
					<button type="button" data-account-filter="available" aria-pressed="false"><span>✓</span><div><small><?php echo esc_html( $is_en ? 'Available now' : 'متاحة للمشاهدة' ); ?></small><strong><?php echo esc_html( (string) $available_products ); ?></strong><em><?php echo esc_html( $is_en ? 'products' : 'منتجات' ); ?></em></div></button>
					<button type="button" data-account-filter="review" aria-pressed="false"><span>◷</span><div><small><?php echo esc_html( $is_en ? 'Under review' : 'قيد المراجعة' ); ?></small><strong><?php echo esc_html( (string) $review_products ); ?></strong><em><?php echo esc_html( $is_en ? 'orders' : 'طلبات' ); ?></em></div></button>
					<button type="button" data-account-filter="unavailable" aria-pressed="false"><span>×</span><div><small><?php echo esc_html( $is_en ? 'Unavailable' : 'غير متاحة' ); ?></small><strong><?php echo esc_html( (string) $unavailable_products ); ?></strong><em><?php echo esc_html( $is_en ? 'products' : 'منتجات' ); ?></em></div></button>
				</section>

				<section id="vava-account-products" class="vava-account-collection vava-account-products-section">
					<header class="vava-account-section-heading"><div><small><?php echo esc_html( $is_en ? 'Protected digital library' : 'مكتبتك الرقمية المحمية' ); ?></small><h2><?php echo esc_html( $is_en ? 'My digital products' : 'منتجاتي الرقمية' ); ?></h2></div></header>
					<?php if ( ! $digital_records ) : ?>
						<div class="vava-account-empty is-product"><span>▤</span><h3><?php echo esc_html( $is_en ? 'No digital products yet' : 'لا توجد منتجات رقمية حتى الآن' ); ?></h3><p><?php echo esc_html( $is_en ? 'Approved purchases will appear here automatically.' : 'ستظهر المنتجات المعتمدة هنا تلقائيًا.' ); ?></p></div>
					<?php else : ?>
						<div id="vava-account-products-list" class="vava-account-card-list">
							<?php foreach ( $digital_records as $index => $product_record ) :
								list( $state_class, $state_label, $state_icon ) = $product_status( $product_record );
								$is_active_product = 'available' === $state_class;
								$product_filter_key = 'available' === $state_class ? 'available' : ( 'review' === $state_class ? 'review' : 'unavailable' );
								$panel_id = 'vava-product-order-details-' . absint( $product_record['id'] );
								?>
								<article class="vava-account-order-card is-product<?php echo 0 === $index ? ' is-featured' : ''; ?>" id="vava-product-<?php echo esc_attr( (string) $product_record['id'] ); ?>" data-account-card data-account-filter-key="<?php echo esc_attr( $product_filter_key ); ?>">
									<figure><?php if ( $product_record['cover'] ) : ?><img src="<?php echo esc_url( $product_record['cover'] ); ?>" alt="<?php echo esc_attr( $product_record['title'] ); ?>"/><?php else : ?><span>VAVA</span><?php endif; ?><b class="is-<?php echo esc_attr( $state_class ); ?>"><?php echo esc_html( $state_icon . ' ' . $state_label ); ?></b></figure>
									<div class="vava-account-order-copy">
										<div class="vava-account-order-number"><small><?php echo esc_html( $is_en ? 'Order number' : 'رقم الطلب' ); ?></small><strong>#<?php echo esc_html( (string) $product_record['id'] ); ?></strong></div>
										<small class="vava-account-product-category"><?php echo esc_html( $product_record['category'] ); ?></small>
										<h3><?php echo esc_html( $product_record['title'] ); ?></h3>
										<?php if ( $product_record['description'] ) : ?><p><?php echo esc_html( $product_record['description'] ); ?></p><?php endif; ?>
										<div class="vava-account-order-facts">
											<span><small><?php echo esc_html( $is_en ? 'Purchase date' : 'تاريخ الشراء' ); ?></small><b dir="ltr"><?php echo esc_html( $format_date( $product_record['created_date'] ) ); ?></b></span>
											<span><small><?php echo esc_html( $is_en ? 'Price' : 'السعر' ); ?></small><b><?php echo esc_html( $product_record['price'] ); ?></b></span>
											<span><small><?php echo esc_html( $is_en ? 'Payment' : 'حالة الدفع' ); ?></small><b><?php echo esc_html( $product_record['payment_label'] ); ?></b></span>
											<span><small><?php echo esc_html( $is_en ? 'Access' : 'حالة الوصول' ); ?></small><b><?php echo esc_html( $state_label ); ?></b></span>
										</div>
										<div class="vava-account-order-actions">
											<button type="button" class="vava-account-secondary-action" data-account-details-toggle aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>"><?php echo esc_html( $is_en ? 'Order details' : 'متابعة الطلب' ); ?></button>
											<?php if ( $is_active_product ) : ?><a class="vava-account-primary-action" href="<?php echo esc_url( $product_record['viewer_url'] ); ?>"><?php echo esc_html( $is_en ? 'Open product' : 'فتح المنتج' ); ?></a><?php else : ?><span class="vava-account-disabled-action"><?php echo esc_html( $is_en ? 'Awaiting approval' : 'بانتظار الاعتماد' ); ?></span><?php endif; ?>
										</div>
										<div id="<?php echo esc_attr( $panel_id ); ?>" class="vava-account-order-details is-product" data-account-details-panel hidden>
											<div><small><?php echo esc_html( $is_en ? 'Order status' : 'حالة الطلب' ); ?></small><b><?php echo esc_html( $product_record['order_status_label'] ); ?></b></div>
											<div><small><?php echo esc_html( $is_en ? 'Review status' : 'حالة المراجعة' ); ?></small><b><?php echo esc_html( $state_label ); ?></b></div>
											<div><small><?php echo esc_html( $is_en ? 'Payment method' : 'طريقة الدفع' ); ?></small><b><?php echo esc_html( $product_record['payment_method_label'] ?: '—' ); ?></b></div>
											<div><small><?php echo esc_html( $is_en ? 'Product ID' : 'معرّف المنتج' ); ?></small><b dir="ltr"><?php echo esc_html( $product_record['uid'] ); ?></b></div>
											<p><?php echo esc_html( $product_record['description'] ?: ( $is_en ? 'Your purchase and access status are shown here.' : 'تظهر هنا تفاصيل الشراء وحالة اعتماد الوصول للمنتج.' ) ); ?></p>
										</div>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
						<div class="vava-account-filter-empty is-product" data-account-filter-empty hidden><span>◌</span><h3><?php echo esc_html( $is_en ? 'No products match this filter' : 'لا توجد منتجات مطابقة لهذا الفلتر' ); ?></h3></div>
						<nav class="vava-account-pagination is-products" data-account-pagination aria-label="<?php echo esc_attr( $is_en ? 'Digital product pages' : 'صفحات المنتجات الرقمية' ); ?>"></nav>
					<?php endif; ?>
				</section>
			<?php endif; ?>
		</main>

		<aside class="vava-account-sidebar" aria-label="<?php echo esc_attr( $is_en ? 'Customer account navigation' : 'التنقل داخل حساب العميل' ); ?>">
			<div class="vava-account-sidebar-profile">
				<span class="vava-customer-avatar"><?php if ( $avatar_url ) : ?><img src="<?php echo esc_url( $avatar_url ); ?>" alt=""/><?php else : ?><?php echo esc_html( strtoupper( substr( $account_name ?: $account_email, 0, 1 ) ) ); ?><?php endif; ?></span>
				<strong><?php echo esc_html( $account_name ); ?></strong>
				<small dir="ltr"><?php echo esc_html( $account_email ); ?></small>
			</div>
			<nav>
				<a class="<?php echo 'bookings' === $active_section ? 'is-active' : ''; ?>" href="<?php echo esc_url( $bookings_url ); ?>"><span>▣</span><?php echo esc_html( $is_en ? 'My bookings' : 'حجوزاتي' ); ?><b><?php echo esc_html( (string) count( $records ) ); ?></b></a>
				<a class="<?php echo 'products' === $active_section ? 'is-active is-product' : 'is-product'; ?>" href="<?php echo esc_url( $products_url ); ?>"><span>▤</span><?php echo esc_html( $is_en ? 'My digital products' : 'منتجاتي الرقمية' ); ?><b><?php echo esc_html( (string) count( $digital_records ) ); ?></b></a>
				<a href="<?php echo esc_url( $profile_url ); ?>"><span>◎</span><?php echo esc_html( $is_en ? 'Account details' : 'بيانات الحساب' ); ?></a>
				<a href="<?php echo esc_url( function_exists( 'vava_page_url' ) ? vava_page_url( 'contact' ) : home_url( '/' ) ); ?>"><span>♡</span><?php echo esc_html( $is_en ? 'Support and help' : 'الدعم والمساعدة' ); ?></a>
				<a class="is-logout" href="<?php echo esc_url( wp_logout_url( $account_url ) ); ?>"><span>↪</span><?php echo esc_html( $is_en ? 'Sign out' : 'تسجيل الخروج' ); ?></a>
			</nav>
			<?php if ( 'products' === $active_section ) : ?>
				<div class="vava-account-sidebar-cta is-products"><span>▤</span><h3><?php echo esc_html( $is_en ? 'Your digital library is always with you' : 'مكتبتك الرقمية معك دائمًا' ); ?></h3><p><?php echo esc_html( $is_en ? 'Explore more carefully selected VAVA guides and digital resources.' : 'استكشف المزيد من أدلة وموارد VAVA المختارة بعناية.' ); ?></p><a href="<?php echo esc_url( $products_explore_url ); ?>"><?php echo esc_html( $is_en ? 'Explore digital products' : 'استكشف المنتجات الرقمية' ); ?></a></div>
			<?php else : ?>
				<div class="vava-account-sidebar-cta is-bookings"><span>❀</span><h3><?php echo esc_html( $is_en ? 'Your next step starts here' : 'خطوتك القادمة تبدأ من هنا' ); ?></h3><p><?php echo esc_html( $is_en ? 'Explore VAVA paths and choose the journey that suits your next step.' : 'استكشف مسارات VAVA واختر الرحلة المناسبة لخطوتك القادمة.' ); ?></p><a href="<?php echo esc_url( $bookings_explore_url ); ?>"><?php echo esc_html( $is_en ? 'Explore paths' : 'استكشف المسارات' ); ?></a></div>
			<?php endif; ?>
		</aside>
	</div>
</div>

<?php
$cancel_nonces = array();
foreach ( $records as $record ) {
	if ( $record['cancel_mode'] ) {
		$cancel_nonces[ (string) $record['id'] ] = wp_create_nonce( 'vava_booking_public_cancel_' . $record['id'] );
	}
}
?>
<script type="application/json" data-booking-cancel-nonces><?php echo wp_json_encode( $cancel_nonces ); ?></script>
<div class="vava-my-bookings-cancel-modal" data-booking-cancel-modal hidden><div class="vava-my-bookings-cancel-backdrop" data-booking-cancel-close></div><section><button type="button" class="vava-my-bookings-cancel-close" data-booking-cancel-close>×</button><small><?php echo esc_html( $is_en ? 'Booking action' : 'إجراء على الحجز' ); ?></small><h2 data-booking-cancel-title></h2><p data-booking-cancel-copy></p><form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post"><input type="hidden" name="action" value="vava_booking_public_cancel"/><input type="hidden" name="booking" data-booking-cancel-id/><input type="hidden" name="lang" value="<?php echo esc_attr( $lang ); ?>"/><input type="hidden" name="magic_token" value="<?php echo esc_attr( $token ); ?>"/><span data-booking-cancel-nonce></span><label><span><?php echo esc_html( $is_en ? 'Reason (optional)' : 'سبب الإلغاء (اختياري)' ); ?></span><textarea name="reason" rows="4"></textarea></label><div class="vava-my-bookings-cancel-actions"><button type="button" data-booking-cancel-close><?php echo esc_html( $is_en ? 'Back' : 'تراجع' ); ?></button><button type="submit" class="is-danger" data-booking-cancel-submit><?php echo esc_html( $is_en ? 'Confirm' : 'تأكيد' ); ?></button></div></form></section></div>

<?php /* VAVA_ACCOUNT_IMPACT_DIRECT_ACTION_V1 */ ?>
