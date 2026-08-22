<?php
/**
 * Front-end VAVA customer accounts and booking ownership.
 *
 * @package VAVA_Living
 */
defined( 'ABSPATH' ) || exit;

function vava_customer_role_slug(): string {
	return 'vava_customer';
}

function vava_customer_register_role(): void {
	if ( ! get_role( vava_customer_role_slug() ) ) {
		add_role(
			vava_customer_role_slug(),
			'VAVA Customer',
			array(
				'read'         => true,
				'upload_files' => false,
			)
		);
	}
}
add_action( 'after_switch_theme', 'vava_customer_register_role' );
add_action( 'init', 'vava_customer_register_role', 4 );

function vava_customer_is_customer( $user = null ): bool {
	$user = $user instanceof WP_User ? $user : ( $user ? get_userdata( absint( $user ) ) : wp_get_current_user() );
	return $user instanceof WP_User && in_array( vava_customer_role_slug(), (array) $user->roles, true );
}

function vava_customer_is_verified( int $user_id ): bool {
	return $user_id > 0 && '1' === (string) get_user_meta( $user_id, '_vava_customer_email_verified', true );
}

function vava_customer_account_url( string $lang = 'ar', array $args = array() ): string {
	$url = function_exists( 'vava_booking_my_bookings_url' ) ? vava_booking_my_bookings_url( $lang ) : home_url( '/' );
	return $args ? add_query_arg( $args, $url ) : $url;
}

function vava_customer_unique_login( string $email ): string {
	$base = sanitize_user( strstr( $email, '@', true ) ?: 'vava-customer', true );
	$base = $base ?: 'vava-customer';
	$login = $base;
	$index = 1;
	while ( username_exists( $login ) ) {
		$login = $base . '-' . $index;
		$index++;
	}
	return $login;
}

function vava_customer_user_by_email( string $email ): ?WP_User {
	$email = strtolower( sanitize_email( $email ) );
	$user  = $email ? get_user_by( 'email', $email ) : false;
	return $user instanceof WP_User ? $user : null;
}

function vava_customer_claim_bookings( int $user_id, string $email ): int {
	if ( ! $user_id || ! function_exists( 'vava_booking_find_customer_bookings' ) ) { return 0; }
	$email = strtolower( sanitize_email( $email ) );
	$count = 0;
	foreach ( vava_booking_find_customer_bookings( $email ) as $booking_id ) {
		$current_owner = absint( get_post_meta( $booking_id, '_vava_booking_user_id', true ) );
		if ( $current_owner && $current_owner !== $user_id ) { continue; }
		update_post_meta( $booking_id, '_vava_booking_user_id', $user_id );
		delete_post_meta( $booking_id, '_vava_booking_pending_user_id' );
		$count++;
	}
	return $count;
}

function vava_customer_find_bookings_for_user( int $user_id ): array {
	if ( ! $user_id ) { return array(); }
	$ids = get_posts(
		array(
			'post_type'      => 'vava_booking',
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'meta_key'       => '_vava_booking_user_id',
			'meta_value'     => $user_id,
		)
	);
	return array_values( array_filter( array_map( 'absint', $ids ) ) );
}

function vava_customer_activation_token( int $user_id, string $lang = 'ar', bool $renew = true ): string {
	if ( ! $user_id ) { return ''; }
	unset( $renew );
	$token = wp_generate_password( 48, false, false );
	update_user_meta( $user_id, '_vava_customer_activation_hash', hash( 'sha256', $token ) );
	update_user_meta( $user_id, '_vava_customer_activation_expires', time() + DAY_IN_SECONDS );
	update_user_meta( $user_id, '_vava_customer_activation_language', 'en' === $lang ? 'en' : 'ar' );
	return $token;
}

function vava_customer_activation_link( int $user_id, string $lang = 'ar', bool $renew = true ): string {
	$token = vava_customer_activation_token( $user_id, $lang, $renew );
	return $token ? vava_customer_account_url( $lang, array( 'vava_activate' => rawurlencode( $token ) ) ) : '';
}

function vava_customer_activation_user( string $token ): ?WP_User {
	$token = sanitize_text_field( $token );
	if ( ! $token ) { return null; }
	$users = get_users(
		array(
			'number'     => 1,
			'count_total'=> false,
			'meta_key'   => '_vava_customer_activation_hash',
			'meta_value' => hash( 'sha256', $token ),
		)
	);
	$user = $users[0] ?? null;
	if ( ! $user instanceof WP_User ) { return null; }
	if ( absint( get_user_meta( $user->ID, '_vava_customer_activation_expires', true ) ) < time() ) { return null; }
	return $user;
}

function vava_customer_send_activation( int $user_id, string $lang = 'ar', bool $force = false ): bool {
	$user = get_userdata( $user_id );
	if ( ! $user instanceof WP_User || ! is_email( $user->user_email ) || vava_customer_is_verified( $user_id ) ) { return false; }
	$last_sent = absint( get_user_meta( $user_id, '_vava_customer_activation_sent_at', true ) );
	if ( ! $force && $last_sent > time() - 10 * MINUTE_IN_SECONDS ) { return true; }
	$link = vava_customer_activation_link( $user_id, $lang, true );
	if ( ! $link ) { return false; }
	$is_en   = 'en' === $lang;
	$subject = $is_en ? 'Activate your VAVA customer account' : 'تفعيل حسابك في VAVA';
	$message = $is_en
		? "Your booking was received. Verify your email and set a password to access your bookings at any time.\n\nActivation link (valid for 24 hours):\n" . $link . "\n\nIf you did not make a booking, you can ignore this message."
		: "تم استلام حجزك. فعّل بريدك وحدد كلمة مرور حتى تتمكن من دخول حجوزاتك في أي وقت.\n\nرابط التفعيل صالح لمدة 24 ساعة:\n" . $link . "\n\nإذا لم تكن قد أجريت حجزًا، يمكنك تجاهل هذه الرسالة.";
	$sent = wp_mail( $user->user_email, $subject, $message );
	update_user_meta( $user_id, '_vava_customer_activation_sent_at', time() );
	return (bool) $sent;
}

function vava_customer_ensure_account( string $email, string $name = '', string $lang = 'ar' ): ?WP_User {
	$email = strtolower( sanitize_email( $email ) );
	if ( ! $email || ! is_email( $email ) ) { return null; }
	$user = vava_customer_user_by_email( $email );
	if ( ! $user ) {
		$user_id = wp_insert_user(
			array(
				'user_login'   => vava_customer_unique_login( $email ),
				'user_email'   => $email,
				'user_pass'    => wp_generate_password( 32, true, true ),
				'display_name' => sanitize_text_field( $name ?: strstr( $email, '@', true ) ),
				'role'         => vava_customer_role_slug(),
			)
		);
		if ( is_wp_error( $user_id ) ) { return null; }
		$user = get_userdata( (int) $user_id );
		update_user_meta( (int) $user_id, '_vava_customer_email_verified', '0' );
		update_user_meta( (int) $user_id, '_vava_customer_created_from_booking', current_time( 'mysql' ) );
	} elseif ( ! vava_customer_is_customer( $user ) ) {
		$user->add_role( vava_customer_role_slug() );
		if ( '' === (string) get_user_meta( $user->ID, '_vava_customer_email_verified', true ) ) {
			// Existing WordPress users have already verified ownership of their account credentials.
			update_user_meta( $user->ID, '_vava_customer_email_verified', '1' );
		}
	}
	if ( $user instanceof WP_User && $name && ! $user->display_name ) {
		wp_update_user( array( 'ID' => $user->ID, 'display_name' => sanitize_text_field( $name ) ) );
	}
	return $user instanceof WP_User ? $user : null;
}

function vava_customer_prepare_account_for_booking( int $booking_id, array $customer = array(), string $lang = 'ar' ): int {
	if ( ! $booking_id ) { return 0; }
	if ( ! $customer ) { $customer = (array) get_post_meta( $booking_id, '_vava_booking_customer', true ); }
	$email = strtolower( sanitize_email( (string) ( $customer['email'] ?? '' ) ) );
	$name  = sanitize_text_field( (string) ( $customer['name'] ?? '' ) );
	$user  = vava_customer_ensure_account( $email, $name, $lang );
	if ( ! $user ) { return 0; }
	update_post_meta( $booking_id, '_vava_booking_user_id', $user->ID );
	$whatsapp = sanitize_text_field( (string) ( $customer['whatsapp'] ?? '' ) );
	if ( $whatsapp && ! get_user_meta( $user->ID, '_vava_customer_whatsapp', true ) ) { update_user_meta( $user->ID, '_vava_customer_whatsapp', $whatsapp ); }
	if ( ! get_user_meta( $user->ID, '_vava_customer_preferred_language', true ) ) { update_user_meta( $user->ID, '_vava_customer_preferred_language', 'en' === $lang ? 'en' : 'ar' ); }
	if ( $name && ( ! $user->first_name || ! $user->last_name ) ) {
		$parts = preg_split( '/\s+/u', trim( $name ), 2 );
		if ( ! $user->first_name && ! empty( $parts[0] ) ) { update_user_meta( $user->ID, 'first_name', sanitize_text_field( $parts[0] ) ); }
		if ( ! $user->last_name && ! empty( $parts[1] ) ) { update_user_meta( $user->ID, 'last_name', sanitize_text_field( $parts[1] ) ); }
	}
	vava_customer_claim_bookings( $user->ID, $email );
	if ( ! vava_customer_is_verified( $user->ID ) ) { vava_customer_send_activation( $user->ID, $lang ); }
	return $user->ID;
}

function vava_customer_current_verified_user(): ?WP_User {
	if ( ! is_user_logged_in() ) { return null; }
	$user = wp_get_current_user();
	return vava_customer_is_customer( $user ) && vava_customer_is_verified( $user->ID ) ? $user : null;
}

function vava_customer_access_context( string $legacy_token = '' ): array {
	$user = vava_customer_current_verified_user();
	if ( $user ) {
		return array( 'type' => 'account', 'user_id' => $user->ID, 'email' => strtolower( $user->user_email ), 'user' => $user );
	}
	if ( $legacy_token && function_exists( 'vava_booking_magic_payload' ) ) {
		$payload = vava_booking_magic_payload( $legacy_token );
		if ( $payload ) { return array( 'type' => 'legacy', 'user_id' => 0, 'email' => strtolower( (string) $payload['email'] ), 'payload' => $payload ); }
	}
	return array();
}

function vava_customer_can_access_booking( int $booking_id, string $legacy_token = '' ): bool {
	if ( ! $booking_id || 'vava_booking' !== get_post_type( $booking_id ) ) { return false; }
	$context = vava_customer_access_context( $legacy_token );
	if ( ! $context ) { return false; }
	if ( 'account' === $context['type'] ) {
		return absint( get_post_meta( $booking_id, '_vava_booking_user_id', true ) ) === absint( $context['user_id'] );
	}
	return strtolower( (string) $context['email'] ) === ( function_exists( 'vava_booking_customer_email' ) ? vava_booking_customer_email( $booking_id ) : '' );
}

function vava_customer_rate_limit_key( string $action, string $email = '' ): string {
	$ip = sanitize_text_field( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	return 'vava_customer_rate_' . substr( hash( 'sha256', $action . '|' . strtolower( $email ) . '|' . $ip ), 0, 36 );
}

function vava_customer_login_handler(): void {
	check_admin_referer( 'vava_customer_login' );
	$lang     = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$email    = isset( $_POST['email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : '';
	$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
	$redirect = vava_customer_account_url( $lang );
	$key      = vava_customer_rate_limit_key( 'login', $email );
	$attempts = absint( get_transient( $key ) );
	if ( $attempts >= 8 ) {
		wp_safe_redirect( add_query_arg( 'account_error', 'rate', $redirect ) ); exit;
	}
	set_transient( $key, $attempts + 1, 15 * MINUTE_IN_SECONDS );
	$user = wp_authenticate( $email, $password );
	if ( is_wp_error( $user ) || ! $user instanceof WP_User || ! vava_customer_is_customer( $user ) ) {
		wp_safe_redirect( add_query_arg( 'account_error', 'login', $redirect ) ); exit;
	}
	if ( ! vava_customer_is_verified( $user->ID ) ) {
		vava_customer_send_activation( $user->ID, $lang );
		wp_safe_redirect( add_query_arg( 'account_error', 'verify', $redirect ) ); exit;
	}
	delete_transient( $key );
	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, ! empty( $_POST['remember'] ), is_ssl() );
	wp_safe_redirect( $redirect ); exit;
}
add_action( 'admin_post_nopriv_vava_customer_login', 'vava_customer_login_handler' );
add_action( 'admin_post_vava_customer_login', 'vava_customer_login_handler' );

function vava_customer_set_password_handler(): void {
	check_admin_referer( 'vava_customer_activate' );
	$lang     = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$token    = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
	$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
	$confirm  = isset( $_POST['password_confirm'] ) ? (string) wp_unslash( $_POST['password_confirm'] ) : '';
	$user     = vava_customer_activation_user( $token );
	$redirect = vava_customer_account_url( $lang, array( 'vava_activate' => rawurlencode( $token ) ) );
	if ( ! $user || strlen( $password ) < 10 || $password !== $confirm ) {
		wp_safe_redirect( add_query_arg( 'account_error', 'password', $redirect ) ); exit;
	}
	wp_set_password( $password, $user->ID );
	update_user_meta( $user->ID, '_vava_customer_email_verified', '1' );
	delete_user_meta( $user->ID, '_vava_customer_activation_hash' );
	delete_user_meta( $user->ID, '_vava_customer_activation_expires' );
	vava_customer_claim_bookings( $user->ID, $user->user_email );
	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true, is_ssl() );
	wp_safe_redirect( vava_customer_account_url( $lang, array( 'account_activated' => '1' ) ) ); exit;
}
add_action( 'admin_post_nopriv_vava_customer_activate', 'vava_customer_set_password_handler' );
add_action( 'admin_post_vava_customer_activate', 'vava_customer_set_password_handler' );

function vava_customer_process_activation_request( string $email, string $lang = 'ar' ): void {
	$email = strtolower( sanitize_email( $email ) );
	$lang  = 'en' === $lang ? 'en' : 'ar';
	$key   = vava_customer_rate_limit_key( 'activate', $email );
	if ( ! $email || ! is_email( $email ) || get_transient( $key ) || ! function_exists( 'vava_booking_find_customer_bookings' ) ) { return; }
	set_transient( $key, 1, 10 * MINUTE_IN_SECONDS );
	$bookings = vava_booking_find_customer_bookings( $email );
	if ( ! $bookings ) { return; }
	$customer = (array) get_post_meta( $bookings[0], '_vava_booking_customer', true );
	$user = vava_customer_ensure_account( $email, (string) ( $customer['name'] ?? '' ), $lang );
	if ( ! $user ) { return; }
	vava_customer_claim_bookings( $user->ID, $email );
	if ( vava_customer_is_verified( $user->ID ) ) {
		vava_customer_send_magic_login( $user->ID, $lang );
	} else {
		vava_customer_send_activation( $user->ID, $lang, true );
	}
}

function vava_customer_request_activation_handler(): void {
	check_admin_referer( 'vava_customer_request_activation' );
	$lang  = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$email = isset( $_POST['email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : '';
	vava_customer_process_activation_request( $email, $lang );
	wp_safe_redirect( vava_customer_account_url( $lang, array( 'activation_sent' => '1' ) ) ); exit;
}
add_action( 'admin_post_nopriv_vava_customer_request_activation', 'vava_customer_request_activation_handler' );
add_action( 'admin_post_vava_customer_request_activation', 'vava_customer_request_activation_handler' );

function vava_customer_request_activation_ajax_handler(): void {
	check_ajax_referer( 'vava_customer_request_activation' );
	$lang  = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$email = isset( $_POST['email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : '';
	if ( ! $email || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => 'en' === $lang ? 'Enter a valid email address.' : 'أدخل بريدًا إلكترونيًا صحيحًا.' ), 422 );
	}
	vava_customer_process_activation_request( $email, $lang );
	wp_send_json_success( array( 'message' => 'en' === $lang ? 'If this email is linked to bookings, a secure message has been sent.' : 'إذا كان البريد مرتبطًا بحجوزات، فقد أرسلنا إليه رسالة آمنة.' ) );
}
add_action( 'wp_ajax_nopriv_vava_customer_request_activation', 'vava_customer_request_activation_ajax_handler' );
add_action( 'wp_ajax_vava_customer_request_activation', 'vava_customer_request_activation_ajax_handler' );

function vava_customer_magic_key( string $token ): string {
	return 'vava_customer_login_' . substr( hash( 'sha256', $token ), 0, 40 );
}

function vava_customer_send_magic_login( int $user_id, string $lang = 'ar' ): bool {
	$user = get_userdata( $user_id );
	if ( ! $user instanceof WP_User || ! vava_customer_is_verified( $user_id ) ) { return false; }
	$token = wp_generate_password( 48, false, false );
	set_transient( vava_customer_magic_key( $token ), array( 'user_id' => $user_id, 'lang' => $lang ), 15 * MINUTE_IN_SECONDS );
	$link = vava_customer_account_url( $lang, array( 'vava_login_token' => rawurlencode( $token ) ) );
	$subject = 'en' === $lang ? 'Your VAVA secure login link' : 'رابط الدخول الآمن إلى حساب VAVA';
	$message = 'en' === $lang
		? "Use this one-time link to sign in. It expires in 15 minutes:\n\n" . $link
		: "استخدم هذا الرابط لمرة واحدة لتسجيل الدخول. تنتهي صلاحيته خلال 15 دقيقة:\n\n" . $link;
	return (bool) wp_mail( $user->user_email, $subject, $message );
}

function vava_customer_process_magic_login_request( string $email, string $lang = 'ar' ): void {
	$email = strtolower( sanitize_email( $email ) );
	$lang  = 'en' === $lang ? 'en' : 'ar';
	$key   = vava_customer_rate_limit_key( 'magic', $email );
	if ( ! $email || ! is_email( $email ) || get_transient( $key ) ) { return; }
	set_transient( $key, 1, 5 * MINUTE_IN_SECONDS );
	$user = vava_customer_user_by_email( $email );
	if ( $user && vava_customer_is_customer( $user ) ) {
		if ( vava_customer_is_verified( $user->ID ) ) { vava_customer_send_magic_login( $user->ID, $lang ); }
		else { vava_customer_send_activation( $user->ID, $lang, true ); }
	}
}

function vava_customer_request_magic_login_handler(): void {
	check_admin_referer( 'vava_customer_request_magic_login' );
	$lang  = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$email = isset( $_POST['email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : '';
	vava_customer_process_magic_login_request( $email, $lang );
	wp_safe_redirect( vava_customer_account_url( $lang, array( 'login_link_sent' => '1' ) ) ); exit;
}
add_action( 'admin_post_nopriv_vava_customer_request_magic_login', 'vava_customer_request_magic_login_handler' );
add_action( 'admin_post_vava_customer_request_magic_login', 'vava_customer_request_magic_login_handler' );

function vava_customer_request_magic_login_ajax_handler(): void {
	check_ajax_referer( 'vava_customer_request_magic_login' );
	$lang  = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$email = isset( $_POST['email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : '';
	if ( ! $email || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => 'en' === $lang ? 'Enter a valid email address.' : 'أدخل بريدًا إلكترونيًا صحيحًا.' ), 422 );
	}
	vava_customer_process_magic_login_request( $email, $lang );
	wp_send_json_success( array( 'message' => 'en' === $lang ? 'If this email is eligible, a secure login message has been sent.' : 'إذا كان البريد مؤهلًا، فقد أرسلنا إليه رابط دخول آمن.' ) );
}
add_action( 'wp_ajax_nopriv_vava_customer_request_magic_login', 'vava_customer_request_magic_login_ajax_handler' );
add_action( 'wp_ajax_vava_customer_request_magic_login', 'vava_customer_request_magic_login_ajax_handler' );

function vava_customer_consume_magic_login(): void {
	if ( is_admin() || empty( $_GET['vava_login_token'] ) ) { return; } // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$token = sanitize_text_field( wp_unslash( $_GET['vava_login_token'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$key = vava_customer_magic_key( $token );
	$payload = get_transient( $key );
	delete_transient( $key );
	$lang = is_array( $payload ) && 'en' === ( $payload['lang'] ?? '' ) ? 'en' : 'ar';
	if ( is_array( $payload ) && ! empty( $payload['user_id'] ) && vava_customer_is_verified( absint( $payload['user_id'] ) ) ) {
		wp_set_current_user( absint( $payload['user_id'] ) );
		wp_set_auth_cookie( absint( $payload['user_id'] ), true, is_ssl() );
		wp_safe_redirect( vava_customer_account_url( $lang ) ); exit;
	}
	wp_safe_redirect( vava_customer_account_url( $lang, array( 'account_error' => 'token' ) ) ); exit;
}
add_action( 'template_redirect', 'vava_customer_consume_magic_login', 3 );


/** V4R10 — customer profile, avatar and verified email changes. */
function vava_customer_avatar_id( int $user_id ): int {
	return absint( get_user_meta( $user_id, '_vava_customer_avatar_id', true ) );
}

function vava_customer_avatar_url( int $user_id, string $size = 'thumbnail' ): string {
	$attachment_id = vava_customer_avatar_id( $user_id );
	if ( ! $attachment_id ) { return ''; }
	$url = wp_get_attachment_image_url( $attachment_id, $size );
	return $url ? (string) $url : '';
}

function vava_customer_profile_error_redirect( string $lang, string $code ): void {
	wp_safe_redirect( vava_customer_account_url( $lang, array( 'view' => 'profile', 'profile_error' => sanitize_key( $code ) ) ) );
	exit;
}

function vava_customer_send_email_change_verification( int $user_id, string $new_email, string $lang ): bool {
	$new_email = strtolower( sanitize_email( $new_email ) );
	if ( ! $user_id || ! is_email( $new_email ) ) { return false; }
	$token = wp_generate_password( 48, false, false );
	update_user_meta( $user_id, '_vava_customer_pending_email', $new_email );
	update_user_meta( $user_id, '_vava_customer_email_change_hash', hash( 'sha256', $token ) );
	update_user_meta( $user_id, '_vava_customer_email_change_expires', time() + DAY_IN_SECONDS );
	$link = vava_customer_account_url( $lang, array( 'vava_email_change' => rawurlencode( $token ) ) );
	$is_en = 'en' === $lang;
	$subject = $is_en ? 'Confirm your new VAVA email' : 'تأكيد بريدك الجديد في VAVA';
	$message = $is_en
		? "Confirm this email address for your VAVA customer account. The link is valid for 24 hours:\n\n{$link}\n\nIf you did not request this change, ignore this message."
		: "أكد هذا البريد ليصبح البريد الجديد لحسابك في VAVA. الرابط صالح لمدة 24 ساعة:\n\n{$link}\n\nإذا لم تطلب هذا التغيير، تجاهل الرسالة.";
	return (bool) wp_mail( $new_email, $subject, $message );
}

function vava_customer_profile_handler(): void {
	check_admin_referer( 'vava_customer_profile_update' );
	$user = vava_customer_current_verified_user();
	$lang = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	if ( ! $user ) { vava_customer_profile_error_redirect( $lang, 'auth' ); }

	$first_name   = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
	$last_name    = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
	$display_name = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
	$whatsapp     = isset( $_POST['whatsapp'] ) ? preg_replace( '/[^0-9+]/', '', (string) wp_unslash( $_POST['whatsapp'] ) ) : '';
	$preferred    = isset( $_POST['preferred_language'] ) && 'en' === sanitize_key( wp_unslash( $_POST['preferred_language'] ) ) ? 'en' : 'ar';
	$new_email    = isset( $_POST['new_email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['new_email'] ) ) ) : '';

	if ( '' === $display_name ) { $display_name = trim( $first_name . ' ' . $last_name ); }
	if ( '' === $display_name ) { $display_name = $user->display_name ?: $user->user_email; }
	$result = wp_update_user(
		array(
			'ID'           => $user->ID,
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'display_name' => $display_name,
		)
	);
	if ( is_wp_error( $result ) ) { vava_customer_profile_error_redirect( $lang, 'save' ); }
	update_user_meta( $user->ID, '_vava_customer_whatsapp', $whatsapp );
	update_user_meta( $user->ID, '_vava_customer_preferred_language', $preferred );

	if ( ! empty( $_POST['remove_avatar'] ) ) {
		$old_avatar = vava_customer_avatar_id( $user->ID );
		if ( $old_avatar ) { wp_delete_attachment( $old_avatar, true ); }
		delete_user_meta( $user->ID, '_vava_customer_avatar_id' );
	}

	if ( isset( $_FILES['avatar'] ) && is_array( $_FILES['avatar'] ) && ! empty( $_FILES['avatar']['tmp_name'] ) ) {
		$file = $_FILES['avatar'];
		if ( (int) ( $file['size'] ?? 0 ) > 2 * MB_IN_BYTES ) { vava_customer_profile_error_redirect( $lang, 'avatar_size' ); }
		$checked = wp_check_filetype_and_ext( (string) $file['tmp_name'], (string) $file['name'] );
		if ( ! in_array( (string) ( $checked['type'] ?? '' ), array( 'image/jpeg', 'image/png', 'image/webp' ), true ) ) { vava_customer_profile_error_redirect( $lang, 'avatar_type' ); }
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$old_avatar = vava_customer_avatar_id( $user->ID );
		$attachment_id = media_handle_upload( 'avatar', 0, array(), array( 'test_form' => false ) );
		if ( is_wp_error( $attachment_id ) ) { vava_customer_profile_error_redirect( $lang, 'avatar_upload' ); }
		update_user_meta( $user->ID, '_vava_customer_avatar_id', absint( $attachment_id ) );
		if ( $old_avatar && $old_avatar !== absint( $attachment_id ) ) { wp_delete_attachment( $old_avatar, true ); }
	}

	if ( $new_email && $new_email !== strtolower( $user->user_email ) ) {
		$existing = email_exists( $new_email );
		if ( ! is_email( $new_email ) || ( $existing && absint( $existing ) !== $user->ID ) ) { vava_customer_profile_error_redirect( $lang, 'email' ); }
		if ( ! vava_customer_send_email_change_verification( $user->ID, $new_email, $lang ) ) { vava_customer_profile_error_redirect( $lang, 'email_send' ); }
		wp_safe_redirect( vava_customer_account_url( $lang, array( 'view' => 'profile', 'email_verification_sent' => '1' ) ) );
		exit;
	}

	wp_safe_redirect( vava_customer_account_url( $lang, array( 'view' => 'profile', 'profile_updated' => '1' ) ) );
	exit;
}
add_action( 'admin_post_vava_customer_profile_update', 'vava_customer_profile_handler' );

/** Change a verified customer's password from inside the profile screen. */
function vava_customer_password_update_handler(): void {
	check_admin_referer( 'vava_customer_password_update' );
	$user = vava_customer_current_verified_user();
	$lang = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$redirect = vava_customer_account_url( $lang, array( 'view' => 'profile' ) );
	if ( ! $user ) {
		wp_safe_redirect( add_query_arg( 'password_error', 'auth', $redirect ) );
		exit;
	}
	$current = isset( $_POST['current_password'] ) ? (string) wp_unslash( $_POST['current_password'] ) : '';
	$new = isset( $_POST['new_password'] ) ? (string) wp_unslash( $_POST['new_password'] ) : '';
	$confirm = isset( $_POST['new_password_confirm'] ) ? (string) wp_unslash( $_POST['new_password_confirm'] ) : '';
	if ( ! wp_check_password( $current, $user->user_pass, $user->ID ) || strlen( $new ) < 10 || $new !== $confirm ) {
		wp_safe_redirect( add_query_arg( 'password_error', 'invalid', $redirect ) );
		exit;
	}
	wp_set_password( $new, $user->ID );
	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true, is_ssl() );
	wp_safe_redirect( add_query_arg( 'password_updated', '1', $redirect ) );
	exit;
}
add_action( 'admin_post_vava_customer_password_update', 'vava_customer_password_update_handler' );

function vava_customer_consume_email_change(): void {
	if ( is_admin() || empty( $_GET['vava_email_change'] ) ) { return; } // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$token = sanitize_text_field( wp_unslash( $_GET['vava_email_change'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$users = get_users(
		array(
			'number'      => 1,
			'count_total' => false,
			'meta_key'    => '_vava_customer_email_change_hash',
			'meta_value'  => hash( 'sha256', $token ),
		)
	);
	$user = $users[0] ?? null;
	$lang = function_exists( 'vava_current_language' ) ? vava_current_language() : 'ar';
	if ( ! $user instanceof WP_User || absint( get_user_meta( $user->ID, '_vava_customer_email_change_expires', true ) ) < time() ) {
		wp_safe_redirect( vava_customer_account_url( $lang, array( 'view' => 'profile', 'profile_error' => 'email_token' ) ) );
		exit;
	}
	$new_email = strtolower( sanitize_email( (string) get_user_meta( $user->ID, '_vava_customer_pending_email', true ) ) );
	if ( ! is_email( $new_email ) || ( email_exists( $new_email ) && absint( email_exists( $new_email ) ) !== $user->ID ) ) {
		wp_safe_redirect( vava_customer_account_url( $lang, array( 'view' => 'profile', 'profile_error' => 'email' ) ) );
		exit;
	}
	$updated = wp_update_user( array( 'ID' => $user->ID, 'user_email' => $new_email ) );
	if ( is_wp_error( $updated ) ) {
		wp_safe_redirect( vava_customer_account_url( $lang, array( 'view' => 'profile', 'profile_error' => 'email' ) ) );
		exit;
	}
	foreach ( vava_customer_find_bookings_for_user( $user->ID ) as $booking_id ) {
		$customer = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
		$customer['email'] = $new_email;
		update_post_meta( $booking_id, '_vava_booking_customer', $customer );
		update_post_meta( $booking_id, '_vava_booking_customer_email', $new_email );
	}
	delete_user_meta( $user->ID, '_vava_customer_pending_email' );
	delete_user_meta( $user->ID, '_vava_customer_email_change_hash' );
	delete_user_meta( $user->ID, '_vava_customer_email_change_expires' );
	wp_safe_redirect( vava_customer_account_url( $lang, array( 'view' => 'profile', 'email_changed' => '1' ) ) );
	exit;
}
add_action( 'template_redirect', 'vava_customer_consume_email_change', 4 );

function vava_customer_header_icon( string $lang = 'ar' ): void {
	$is_logged = (bool) vava_customer_current_verified_user();
	$label = $is_logged ? ( 'en' === $lang ? 'My account' : 'حسابي' ) : ( 'en' === $lang ? 'Customer login' : 'دخول العملاء' );
	?>
	<a class="vava-account-link<?php echo $is_logged ? ' is-logged-in' : ''; ?>" href="<?php echo esc_url( vava_customer_account_url( $lang ) ); ?>" aria-label="<?php echo esc_attr( $label ); ?>" title="<?php echo esc_attr( $label ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"></circle><path d="M5.5 20c.6-4 3-6 6.5-6s5.9 2 6.5 6"></path></svg>
	</a>
	<?php
}

function vava_customer_restrict_admin(): void {
	if ( ! is_user_logged_in() || wp_doing_ajax() || ! vava_customer_is_customer() ) { return; }
	if ( current_user_can( 'edit_posts' ) || current_user_can( 'manage_options' ) ) { return; }
	wp_safe_redirect( vava_customer_account_url( function_exists( 'vava_current_language' ) ? vava_current_language() : 'ar' ) );
	exit;
}
add_action( 'admin_init', 'vava_customer_restrict_admin', 1 );
add_filter( 'show_admin_bar', static function ( bool $show ): bool {
	return is_user_logged_in() && vava_customer_is_customer() && ! current_user_can( 'edit_posts' ) ? false : $show;
} );
