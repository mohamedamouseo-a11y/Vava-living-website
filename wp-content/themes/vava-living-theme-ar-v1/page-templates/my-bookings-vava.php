<?php
/**
 * Template Name: VAVA — My Bookings (AR / EN)
 * Template Post Type: page
 *
 * @package VAVA_Living
 */
defined( 'ABSPATH' ) || exit;

$lang  = vava_current_language();
$is_en = 'en' === $lang;
$token = isset( $_GET['vava_magic'] ) ? sanitize_text_field( wp_unslash( $_GET['vava_magic'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$requested_view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'overview'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$view = in_array( $requested_view, array( 'overview', 'bookings', 'products', 'profile' ), true ) ? $requested_view : 'overview'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$context = function_exists( 'vava_customer_access_context' ) ? vava_customer_access_context( $token ) : array();
$activation_token = isset( $_GET['vava_activate'] ) ? sanitize_text_field( wp_unslash( $_GET['vava_activate'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$activation_user  = $activation_token && function_exists( 'vava_customer_activation_user' ) ? vava_customer_activation_user( $activation_token ) : null;
$current_customer = 'account' === ( $context['type'] ?? '' ) && ! empty( $context['user'] ) ? $context['user'] : null;

if ( 'account' === ( $context['type'] ?? '' ) ) {
	$bookings = vava_customer_find_bookings_for_user( absint( $context['user_id'] ) );
} elseif ( 'legacy' === ( $context['type'] ?? '' ) ) {
	$bookings = vava_booking_find_customer_bookings( (string) $context['email'] );
} else {
	$bookings = array();
}

$digital_order_ids = array_values( array_filter( $bookings, static function ( $order_id ): bool { return function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( absint( $order_id ) ); } ) );
$bookings = array_values( array_filter( $bookings, static function ( $order_id ): bool { return ! function_exists( 'vava_digital_products_is_order' ) || ! vava_digital_products_is_order( absint( $order_id ) ); } ) );
$digital_records = array();
foreach ( $digital_order_ids as $order_id ) {
	$uid = function_exists( 'vava_digital_products_order_uid' ) ? vava_digital_products_order_uid( absint( $order_id ) ) : '';
	$product = function_exists( 'vava_digital_product_data' ) ? vava_digital_product_data( $uid, $lang ) : array();
	$access = function_exists( 'vava_digital_products_order_access_status' ) ? vava_digital_products_order_access_status( absint( $order_id ) ) : 'pending';
	$payment = function_exists( 'vava_booking_effective_payment_status' ) ? vava_booking_effective_payment_status( absint( $order_id ) ) : (string) get_post_meta( $order_id, '_vava_booking_payment_status', true );
	$cover = (string) ( $product['cover_url'] ?? '' );
	$order_status = (string) get_post_meta( $order_id, '_vava_booking_status', true );
	$payment_method = (string) get_post_meta( $order_id, '_vava_booking_payment_method', true );
	$digital_records[] = array(
		'id' => absint( $order_id ),
		'uid' => $uid,
		'title' => (string) ( $product['title'] ?? get_post_meta( $order_id, '_vava_booking_service_title', true ) ),
		'description' => (string) ( $product['card_description'] ?? $product['description'] ?? '' ),
		'category' => (string) ( $product['category'] ?? ( $is_en ? 'Digital product' : 'منتج رقمي' ) ),
		'cover' => $cover,
		'price' => vava_booking_format_price_label( (string) get_post_meta( $order_id, '_vava_booking_service_price', true ), (string) get_post_meta( $order_id, '_vava_booking_service_currency', true ), $lang ),
		'access' => $access,
		'payment' => $payment,
		'payment_label' => $is_en ? ucfirst( str_replace( '_', ' ', $payment ) ) : vava_booking_payment_status_label( $payment ),
		'payment_method' => $payment_method,
		'payment_method_label' => $is_en ? ucfirst( $payment_method ) : vava_booking_payment_method_label( $payment_method ),
		'order_status' => $order_status,
		'order_status_label' => $is_en ? ucfirst( str_replace( '_', ' ', $order_status ) ) : vava_booking_status_label( $order_status ),
		'viewer_url' => function_exists( 'vava_digital_products_viewer_url' ) ? vava_digital_products_viewer_url( $uid, $lang ) : '#',
		'created_at' => (string) get_post_meta( $order_id, '_vava_booking_created_at', true ),
		'created_date' => (string) ( get_post_meta( $order_id, '_vava_booking_created_at', true ) ?: get_the_date( 'Y-m-d', $order_id ) ),
		'customer' => (array) get_post_meta( $order_id, '_vava_booking_customer', true ),
	);
}

$request_sent           = ! empty( $_GET['request_sent'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$activation_sent        = ! empty( $_GET['activation_sent'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$login_link_sent        = ! empty( $_GET['login_link_sent'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$account_activated      = ! empty( $_GET['account_activated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$account_error          = isset( $_GET['account_error'] ) ? sanitize_key( wp_unslash( $_GET['account_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$receipt_updated        = ! empty( $_GET['receipt_updated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$receipt_error          = isset( $_GET['receipt_error'] ) ? sanitize_text_field( wp_unslash( $_GET['receipt_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$customer_cancelled     = ! empty( $_GET['customer_cancelled'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$cancellation_requested = ! empty( $_GET['cancellation_requested'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$selected_booking_id    = isset( $_GET['booking'] ) ? absint( $_GET['booking'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$profile_updated        = ! empty( $_GET['profile_updated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$email_verification_sent= ! empty( $_GET['email_verification_sent'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$email_changed          = ! empty( $_GET['email_changed'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$profile_error          = isset( $_GET['profile_error'] ) ? sanitize_key( wp_unslash( $_GET['profile_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$password_updated       = ! empty( $_GET['password_updated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$password_error         = isset( $_GET['password_error'] ) ? sanitize_key( wp_unslash( $_GET['password_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$status_map_en = array(
	'pending' => 'Pending', 'pending_payment' => 'Awaiting payment', 'pending_bank_review' => 'Awaiting transfer review',
	'confirmed' => 'Confirmed', 'paid' => 'Paid', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
	'bank_rejected' => 'Transfer rejected', 'payment_failed' => 'Payment failed', 'payment_error' => 'Payment error',
	'customer_cancelled' => 'Cancelled by customer', 'cancellation_requested' => 'Cancellation requested',
);
$payment_map_en = array(
	'pending' => 'Pending', 'pending_bank_review' => 'Awaiting transfer review', 'paid' => 'Paid', 'unpaid' => 'Unpaid',
	'rejected' => 'Rejected', 'failed' => 'Failed', 'refunded' => 'Refunded', 'partially_refunded' => 'Partially refunded',
	'refund_pending' => 'Refund pending', 'cancelled' => 'Cancelled',
);
$method_map_en = array( 'paymob' => 'Online payment', 'bank' => 'Bank transfer', 'cash' => 'Pay later' );

$records = array();
foreach ( $bookings as $booking_id ) {
	$customer       = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
	$status         = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
	$payment_status = vava_booking_effective_payment_status( $booking_id );
	$method         = (string) get_post_meta( $booking_id, '_vava_booking_payment_method', true );
	$uid            = (string) get_post_meta( $booking_id, '_vava_booking_service_uid', true );
	$service        = vava_booking_resolve_service( $uid, $lang );
	$title          = $service ? (string) $service['title'] : (string) get_post_meta( $booking_id, '_vava_booking_service_title', true );
	$description    = $service ? trim( wp_strip_all_tags( (string) ( $service['description'] ?? '' ) ) ) : '';
	$detail_url     = $service ? vava_booking_service_detail_url( $service, $lang ) : '#';
	$date           = (string) get_post_meta( $booking_id, '_vava_booking_date', true );
	$time           = vava_booking_format_time_12h( (string) get_post_meta( $booking_id, '_vava_booking_time', true ) );
	$duration       = function_exists( 'vava_booking_display_duration_for_booking' ) ? vava_booking_display_duration_for_booking( $booking_id, $lang ) : absint( get_post_meta( $booking_id, '_vava_booking_duration', true ) ) . ( $is_en ? ' minutes' : ' دقيقة' );
	$price          = vava_booking_format_price_label( (string) get_post_meta( $booking_id, '_vava_booking_service_price', true ), (string) get_post_meta( $booking_id, '_vava_booking_service_currency', true ), $lang );
	$image_id       = $service ? absint( $service['image_id'] ?? 0 ) : absint( get_post_meta( $booking_id, '_vava_booking_service_image_id', true ) );
	$image_url      = $image_id ? (string) wp_get_attachment_image_url( $image_id, 'large' ) : '';
	$receipt        = vava_booking_get_receipt( $booking_id, false );
	$receipt_url    = vava_booking_receipt_public_url( $booking_id );
	$receipt_mime   = strtolower( (string) ( $receipt['mime'] ?? '' ) );
	$can_replace    = vava_booking_customer_can_replace_receipt( $booking_id );
	$status_label   = $is_en ? ( $status_map_en[ $status ] ?? ucfirst( str_replace( '_', ' ', $status ) ) ) : vava_booking_status_label( $status );
	$payment_label  = $is_en ? ( $payment_map_en[ $payment_status ] ?? ucfirst( str_replace( '_', ' ', $payment_status ) ) ) : vava_booking_payment_status_label( $payment_status );
	$method_label   = $is_en ? ( $method_map_en[ $method ] ?? ucfirst( $method ) ) : vava_booking_payment_method_label( $method );
	$bucket = 'other';
	if ( in_array( $status, array( 'confirmed', 'completed', 'paid' ), true ) ) { $bucket = 'confirmed'; }
	elseif ( in_array( $status, array( 'cancelled', 'customer_cancelled', 'bank_rejected', 'payment_failed', 'payment_error' ), true ) || in_array( $payment_status, array( 'rejected', 'failed', 'cancelled' ), true ) ) { $bucket = 'rejected'; }
	elseif ( 'pending_bank_review' === $status || 'pending_bank_review' === $payment_status || in_array( $status, array( 'pending', 'pending_payment', 'cancellation_requested' ), true ) ) { $bucket = 'review'; }
	$records[] = array(
		'id' => (int) $booking_id, 'customer' => $customer, 'status' => $status, 'status_label' => $status_label,
		'payment_status' => $payment_status, 'payment_status_label' => $payment_label, 'method' => $method,
		'method_label' => $method_label, 'bucket' => $bucket, 'title' => $title, 'description' => $description,
		'detail_url' => $detail_url, 'date' => $date, 'time' => $time, 'duration' => $duration, 'price' => $price,
		'image_url' => $image_url, 'receipt' => $receipt, 'receipt_url' => $receipt_url,
		'receipt_mime' => $receipt_mime, 'can_replace_receipt' => $can_replace,
		'cancel_mode' => vava_booking_customer_cancel_mode( $booking_id ),
		'refund_status' => vava_booking_refund_status( $booking_id ),
		'created_at' => (string) get_post_meta( $booking_id, '_vava_booking_created_at', true ),
	);
}
if ( ! $selected_booking_id || ! in_array( $selected_booking_id, array_column( $records, 'id' ), true ) ) {
	$selected_booking_id = ! empty( $records[0]['id'] ) ? absint( $records[0]['id'] ) : 0;
}

$error_messages = array(
	'login' => $is_en ? 'The email or password is incorrect.' : 'البريد أو كلمة المرور غير صحيحة.',
	'verify' => $is_en ? 'Please verify your email first. A new activation link was sent.' : 'يرجى تفعيل البريد أولًا. تم إرسال رابط تفعيل جديد.',
	'rate' => $is_en ? 'Too many attempts. Please try again later.' : 'محاولات كثيرة. يرجى المحاولة لاحقًا.',
	'password' => $is_en ? 'Use matching passwords of at least 10 characters.' : 'استخدم كلمتي مرور متطابقتين لا تقلان عن 10 أحرف.',
	'token' => $is_en ? 'The secure login link is invalid or expired.' : 'رابط الدخول الآمن غير صالح أو انتهت صلاحيته.',
);
$profile_error_messages = array(
	'auth' => $is_en ? 'Please sign in again.' : 'يرجى تسجيل الدخول مرة أخرى.',
	'save' => $is_en ? 'The profile could not be saved.' : 'تعذر حفظ بيانات الملف الشخصي.',
	'avatar_size' => $is_en ? 'The profile image must be smaller than 2 MB.' : 'يجب ألا يتجاوز حجم الصورة الشخصية 2 ميجابايت.',
	'avatar_type' => $is_en ? 'Use a JPG, PNG or WEBP image.' : 'استخدم صورة JPG أو PNG أو WEBP.',
	'avatar_upload' => $is_en ? 'The profile image could not be uploaded.' : 'تعذر رفع الصورة الشخصية.',
	'email' => $is_en ? 'The new email is invalid or already in use.' : 'البريد الجديد غير صالح أو مستخدم بالفعل.',
	'email_send' => $is_en ? 'The verification message could not be sent.' : 'تعذر إرسال رسالة التحقق إلى البريد الجديد.',
	'email_token' => $is_en ? 'The email verification link is invalid or expired.' : 'رابط تأكيد البريد غير صالح أو انتهت صلاحيته.',
);

$GLOBALS['vava_page_data_name']       = $is_en ? 'my-bookings-en.html' : 'my-bookings.html';
$GLOBALS['vava_active_nav']            = '';
$GLOBALS['vava_internal_body_classes'] = array( 'vava-my-bookings-page' );
get_header( 'page' );
?>
<main class="vava-my-bookings" dir="<?php echo esc_attr( $is_en ? 'ltr' : 'rtl' ); ?>" data-my-bookings-root data-active-section="<?php echo esc_attr( 'products' === $view ? 'products' : 'bookings' ); ?>" data-account-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
	<section class="vava-my-bookings-hero">
		<div class="container">
			<span><?php echo esc_html( $context ? ( $is_en ? 'Your personal VAVA space' : 'مساحتك الشخصية في VAVA' ) : ( $is_en ? 'Secure customer portal' : 'بوابة عملاء آمنة' ) ); ?></span>
			<h1><?php echo esc_html( $context ? ( $is_en ? 'My account' : 'حسابي' ) : ( $is_en ? 'Your VAVA account' : 'حسابك في VAVA' ) ); ?></h1>
			<p><?php echo esc_html( $context ? ( $is_en ? 'Manage your bookings and digital products securely from one place.' : 'أدر حجوزاتك ومنتجاتك الرقمية وتابع تفاصيلها من مكان واحد.' ) : ( $is_en ? 'Access your bookings and digital products securely at any time.' : 'ادخل إلى حجوزاتك ومنتجاتك الرقمية بأمان في أي وقت.' ) ); ?></p>
		</div>
	</section>

	<section class="vava-my-bookings-shell">
		<div class="container<?php echo $context ? ' is-account-dashboard-container' : ''; ?>">
			<?php if ( $account_error && isset( $error_messages[ $account_error ] ) ) : ?><div class="vava-my-bookings-notice is-error"><?php echo esc_html( $error_messages[ $account_error ] ); ?></div><?php endif; ?>
			<?php if ( $profile_error && isset( $profile_error_messages[ $profile_error ] ) ) : ?><div class="vava-my-bookings-notice is-error"><?php echo esc_html( $profile_error_messages[ $profile_error ] ); ?></div><?php endif; ?>
			<?php if ( $request_sent || $activation_sent || $login_link_sent ) : ?><div class="vava-my-bookings-notice is-success"><?php echo esc_html( $is_en ? 'If the email is eligible, a secure message has been sent.' : 'إذا كان البريد مؤهلًا، فقد تم إرسال رسالة آمنة إليه.' ); ?></div><?php endif; ?>
			<?php if ( $account_activated ) : ?><div class="vava-my-bookings-notice is-success"><?php echo esc_html( $is_en ? 'Your account is active and your previous bookings are now linked.' : 'تم تفعيل حسابك وربط حجوزاتك السابقة به.' ); ?></div><?php endif; ?>
			<?php if ( $profile_updated ) : ?><div class="vava-my-bookings-notice is-success"><?php echo esc_html( $is_en ? 'Your profile was updated.' : 'تم تحديث ملفك الشخصي.' ); ?></div><?php endif; ?>
			<?php if ( $email_verification_sent ) : ?><div class="vava-my-bookings-notice is-success"><?php echo esc_html( $is_en ? 'A verification link was sent to the new email.' : 'تم إرسال رابط تحقق إلى البريد الجديد.' ); ?></div><?php endif; ?>
			<?php if ( $email_changed ) : ?><div class="vava-my-bookings-notice is-success"><?php echo esc_html( $is_en ? 'Your account email was changed successfully.' : 'تم تغيير بريد الحساب بنجاح.' ); ?></div><?php endif; ?>

			<?php if ( $activation_user instanceof WP_User ) : ?>
				<section class="vava-customer-auth-card is-activation">
					<div><small><?php echo esc_html( $is_en ? 'Email verified' : 'تأكيد البريد' ); ?></small><h2><?php echo esc_html( $is_en ? 'Create your account password' : 'أنشئ كلمة مرور حسابك' ); ?></h2><p><?php echo esc_html( $is_en ? 'Set a strong password to access your bookings whenever you need.' : 'حدد كلمة مرور قوية للدخول إلى حجوزاتك في أي وقت.' ); ?></p></div>
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post"><input type="hidden" name="action" value="vava_customer_activate"/><input type="hidden" name="lang" value="<?php echo esc_attr( $lang ); ?>"/><input type="hidden" name="token" value="<?php echo esc_attr( $activation_token ); ?>"/><?php wp_nonce_field( 'vava_customer_activate' ); ?><label><span><?php echo esc_html( $is_en ? 'New password' : 'كلمة المرور الجديدة' ); ?></span><input type="password" name="password" minlength="10" required autocomplete="new-password"/></label><label><span><?php echo esc_html( $is_en ? 'Confirm password' : 'تأكيد كلمة المرور' ); ?></span><input type="password" name="password_confirm" minlength="10" required autocomplete="new-password"/></label><button type="submit"><?php echo esc_html( $is_en ? 'Activate account' : 'تفعيل الحساب' ); ?></button></form>
				</section>
			<?php elseif ( ! $context ) : ?>
				<section class="vava-customer-auth-grid">
					<article class="vava-customer-auth-card is-primary"><div><small><?php echo esc_html( $is_en ? 'Customer login' : 'دخول العملاء' ); ?></small><h2><?php echo esc_html( $is_en ? 'Welcome back' : 'مرحبًا بعودتك' ); ?></h2><p><?php echo esc_html( $is_en ? 'Sign in with the verified email and password linked to your bookings.' : 'سجّل الدخول بالبريد الموثق وكلمة المرور المرتبطين بحجوزاتك.' ); ?></p></div><form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post"><input type="hidden" name="action" value="vava_customer_login"/><input type="hidden" name="lang" value="<?php echo esc_attr( $lang ); ?>"/><?php wp_nonce_field( 'vava_customer_login' ); ?><label><span><?php echo esc_html( $is_en ? 'Email' : 'البريد الإلكتروني' ); ?></span><input type="email" name="email" required autocomplete="email"/></label><label><span><?php echo esc_html( $is_en ? 'Password' : 'كلمة المرور' ); ?></span><input type="password" name="password" required autocomplete="current-password"/></label><label class="vava-customer-remember"><input type="checkbox" name="remember" value="1"/><span><?php echo esc_html( $is_en ? 'Keep me signed in' : 'تذكر تسجيل الدخول' ); ?></span></label><button type="submit"><?php echo esc_html( $is_en ? 'Sign in' : 'تسجيل الدخول' ); ?></button><a class="vava-customer-lost" href="<?php echo esc_url( wp_lostpassword_url( vava_customer_account_url( $lang ) ) ); ?>"><?php echo esc_html( $is_en ? 'Forgot password?' : 'نسيت كلمة المرور؟' ); ?></a></form></article>
					<div class="vava-customer-auth-stack">
						<article class="vava-customer-auth-card"><div><small><?php echo esc_html( $is_en ? 'Passwordless access' : 'دخول بدون كلمة مرور' ); ?></small><h3><?php echo esc_html( $is_en ? 'Send a secure login link' : 'أرسل رابط دخول آمن' ); ?></h3></div><form class="vava-customer-async-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" data-customer-async-form><input type="hidden" name="action" value="vava_customer_request_magic_login"/><input type="hidden" name="lang" value="<?php echo esc_attr( $lang ); ?>"/><?php wp_nonce_field( 'vava_customer_request_magic_login' ); ?><label><span><?php echo esc_html( $is_en ? 'Account email' : 'بريد الحساب' ); ?></span><input type="email" name="email" required autocomplete="email"/></label><button type="submit" class="is-secondary" data-idle-label="<?php echo esc_attr( $is_en ? 'Send login link' : 'إرسال رابط الدخول' ); ?>" data-loading-label="<?php echo esc_attr( $is_en ? 'Sending…' : 'جارٍ الإرسال…' ); ?>"><?php echo esc_html( $is_en ? 'Send login link' : 'إرسال رابط الدخول' ); ?></button><p class="vava-customer-form-status" role="status" aria-live="polite" hidden></p></form></article>
						<article class="vava-customer-auth-card"><div><small><?php echo esc_html( $is_en ? 'Previous bookings' : 'حجوزات سابقة' ); ?></small><h3><?php echo esc_html( $is_en ? 'Activate an account for old bookings' : 'فعّل حسابًا لحجوزاتك القديمة' ); ?></h3><p><?php echo esc_html( $is_en ? 'Use the same email entered in your previous booking.' : 'استخدم نفس البريد الذي أدخلته في الحجز السابق.' ); ?></p></div><form class="vava-customer-async-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" data-customer-async-form><input type="hidden" name="action" value="vava_customer_request_activation"/><input type="hidden" name="lang" value="<?php echo esc_attr( $lang ); ?>"/><?php wp_nonce_field( 'vava_customer_request_activation' ); ?><label><span><?php echo esc_html( $is_en ? 'Booking email' : 'البريد المستخدم في الحجز' ); ?></span><input type="email" name="email" required autocomplete="email"/></label><button type="submit" class="is-secondary" data-idle-label="<?php echo esc_attr( $is_en ? 'Send activation email' : 'إرسال رسالة التفعيل' ); ?>" data-loading-label="<?php echo esc_attr( $is_en ? 'Sending…' : 'جارٍ الإرسال…' ); ?>"><?php echo esc_html( $is_en ? 'Send activation email' : 'إرسال رسالة التفعيل' ); ?></button><p class="vava-customer-form-status" role="status" aria-live="polite" hidden></p></form></article>
					</div>
				</section>
			<?php else : ?>
				<?php if ( $current_customer instanceof WP_User ) :
					$avatar_url = function_exists( 'vava_customer_avatar_url' ) ? vava_customer_avatar_url( $current_customer->ID, 'thumbnail' ) : '';
					?>
				<?php endif; ?>

				<?php if ( 'profile' === $view && $current_customer instanceof WP_User ) :
					$profile_whatsapp = (string) get_user_meta( $current_customer->ID, '_vava_customer_whatsapp', true );
					$preferred_language = (string) get_user_meta( $current_customer->ID, '_vava_customer_preferred_language', true );
					$pending_email = (string) get_user_meta( $current_customer->ID, '_vava_customer_pending_email', true );
					?>
					<section class="vava-customer-profile-card">
						<?php if ( $password_updated ) : ?><div class="vava-my-bookings-notice is-success"><?php echo esc_html( $is_en ? 'Your password was changed successfully.' : 'تم تغيير كلمة المرور بنجاح.' ); ?></div><?php endif; ?>
						<?php if ( $password_error ) : ?><div class="vava-my-bookings-notice is-error"><?php echo esc_html( $is_en ? 'Check your current password and use matching new passwords of at least 10 characters.' : 'تحقق من كلمة المرور الحالية، واستخدم كلمتي مرور جديدتين متطابقتين لا تقلان عن 10 أحرف.' ); ?></div><?php endif; ?>
						<header><div><small><?php echo esc_html( $is_en ? 'Customer profile' : 'الملف الشخصي' ); ?></small><h2><?php echo esc_html( $is_en ? 'Manage your account details' : 'إدارة بيانات حسابك' ); ?></h2><p><?php echo esc_html( $is_en ? 'Update your name, phone, profile image and preferred language.' : 'حدّث اسمك ورقمك وصورتك الشخصية ولغتك المفضلة.' ); ?></p></div><a href="<?php echo esc_url( vava_customer_account_url( $lang ) ); ?>"><?php echo esc_html( $is_en ? 'Back to account' : 'العودة إلى حسابي' ); ?></a></header>
						<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="vava_customer_profile_update"/><input type="hidden" name="lang" value="<?php echo esc_attr( $lang ); ?>"/><?php wp_nonce_field( 'vava_customer_profile_update' ); ?>
							<div class="vava-customer-profile-avatar"><span data-profile-avatar-preview><?php if ( $avatar_url ) : ?><img src="<?php echo esc_url( $avatar_url ); ?>" alt=""/><?php else : ?><?php echo esc_html( strtoupper( substr( $current_customer->display_name ?: $current_customer->user_email, 0, 1 ) ) ); ?><?php endif; ?></span><label><b><?php echo esc_html( $is_en ? 'Profile image' : 'الصورة الشخصية' ); ?></b><small><?php echo esc_html( $is_en ? 'JPG, PNG or WEBP — maximum 2 MB.' : 'JPG أو PNG أو WEBP — بحد أقصى 2 ميجابايت.' ); ?></small><input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" data-profile-avatar-input/></label><?php if ( $avatar_url ) : ?><label class="vava-profile-remove-avatar"><input type="checkbox" name="remove_avatar" value="1"/><span><?php echo esc_html( $is_en ? 'Remove current image' : 'حذف الصورة الحالية' ); ?></span></label><?php endif; ?></div>
							<div class="vava-customer-profile-grid"><label><span><?php echo esc_html( $is_en ? 'First name' : 'الاسم الأول' ); ?></span><input type="text" name="first_name" value="<?php echo esc_attr( $current_customer->first_name ); ?>"/></label><label><span><?php echo esc_html( $is_en ? 'Last name' : 'الاسم الأخير' ); ?></span><input type="text" name="last_name" value="<?php echo esc_attr( $current_customer->last_name ); ?>"/></label><label><span><?php echo esc_html( $is_en ? 'Display name' : 'الاسم الظاهر' ); ?></span><input type="text" name="display_name" value="<?php echo esc_attr( $current_customer->display_name ); ?>" required/></label><label><span><?php echo esc_html( $is_en ? 'Phone / WhatsApp' : 'رقم الهاتف / WhatsApp' ); ?></span><input type="tel" name="whatsapp" dir="ltr" value="<?php echo esc_attr( $profile_whatsapp ); ?>"/></label><label><span><?php echo esc_html( $is_en ? 'Preferred language' : 'اللغة المفضلة' ); ?></span><select name="preferred_language"><option value="ar" <?php selected( $preferred_language ?: $lang, 'ar' ); ?>>العربية</option><option value="en" <?php selected( $preferred_language ?: $lang, 'en' ); ?>>English</option></select></label><label><span><?php echo esc_html( $is_en ? 'Current email' : 'البريد الحالي' ); ?></span><input type="email" value="<?php echo esc_attr( $current_customer->user_email ); ?>" readonly/></label><label class="is-full"><span><?php echo esc_html( $is_en ? 'New email (optional)' : 'البريد الجديد (اختياري)' ); ?></span><input type="email" name="new_email" value="<?php echo esc_attr( $pending_email ); ?>" placeholder="<?php echo esc_attr( $is_en ? 'A verification link will be sent before the change is applied.' : 'سيُرسل رابط تحقق قبل اعتماد البريد الجديد.' ); ?>"/><small><?php echo esc_html( $is_en ? 'The email changes only after verification.' : 'لن يتغير البريد إلا بعد التحقق منه.' ); ?></small></label></div>
							<button type="submit" class="vava-profile-save"><?php echo esc_html( $is_en ? 'Save profile' : 'حفظ الملف الشخصي' ); ?></button>
						</form>
						<section class="vava-profile-password-card">
							<header><small><?php echo esc_html( $is_en ? 'Account security' : 'أمان الحساب' ); ?></small><h3><?php echo esc_html( $is_en ? 'Change password' : 'تغيير كلمة المرور' ); ?></h3><p><?php echo esc_html( $is_en ? 'Confirm your current password before choosing a new one.' : 'أكّد كلمة المرور الحالية قبل اختيار كلمة مرور جديدة.' ); ?></p></header>
							<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post"><input type="hidden" name="action" value="vava_customer_password_update"/><input type="hidden" name="lang" value="<?php echo esc_attr( $lang ); ?>"/><?php wp_nonce_field( 'vava_customer_password_update' ); ?><div class="vava-customer-profile-grid"><label class="is-full"><span><?php echo esc_html( $is_en ? 'Current password' : 'كلمة المرور الحالية' ); ?></span><input type="password" name="current_password" required autocomplete="current-password"/></label><label><span><?php echo esc_html( $is_en ? 'New password' : 'كلمة المرور الجديدة' ); ?></span><input type="password" name="new_password" minlength="10" required autocomplete="new-password"/></label><label><span><?php echo esc_html( $is_en ? 'Confirm new password' : 'تأكيد كلمة المرور الجديدة' ); ?></span><input type="password" name="new_password_confirm" minlength="10" required autocomplete="new-password"/></label></div><button type="submit" class="vava-profile-save"><?php echo esc_html( $is_en ? 'Update password' : 'تحديث كلمة المرور' ); ?></button></form>
						</section>
					</section>
				<?php else : ?>
					<?php include locate_template( 'template-parts/account-dashboard-vava.php', false, false ); ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php get_footer( 'page' ); ?>
