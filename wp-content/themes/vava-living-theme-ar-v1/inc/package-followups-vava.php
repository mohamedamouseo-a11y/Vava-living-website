<?php
/**
 * VAVA package follow-up entitlements and independently scheduled sessions.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Explicitly configured session entitlements. No default for unrelated packages.
 * Administrators may set _vava_package_followup_total on the parent order.
 */
/**
 * Derive included follow-up sessions from the configured VAVA plan, not its title.
 * The first 90-minute (or 30-minute follow-up package) appointment is booked at checkout.
 * The remainder is independently bookable from the verified customer account.
 */
function vava_package_followup_plan_count( array $plan ): int {
    if ( isset( $plan['followup_sessions'] ) && is_numeric( $plan['followup_sessions'] ) ) {
        return min( 20, max( 0, absint( $plan['followup_sessions'] ) ) );
    }
    foreach ( (array) ( $plan['features'] ?? array() ) as $feature ) {
        if ( empty( $feature['visible'] ) || empty( $feature['enabled'] ) ) { continue; }
        $label = wp_strip_all_tags( (string) ( $feature['text'] ?? '' ) );
        if ( ! preg_match( '/(?:جلس(?:ة|ات)\\s*(?:متابعة|المتابعة|ضبط\\s*الإيقاع)|rhythm\\s*adjustment|follow[ -]?up)/iu', $label ) ) { continue; }
        $value = strtr( (string) ( $feature['value'] ?? '' ), array( '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9' ) );
        if ( preg_match( '/\\d+/', $value, $matches ) ) { return min( 20, absint( $matches[0] ) ); }
    }
    return 0;
}

/** Match the saved catalogue package to a comparison plan, despite Arabic alef variants. */
function vava_package_followup_normalize_title( string $title ): string {
    $title = strtr( $title, array( 'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا' ) );
    $title = preg_replace( '/[\x{064B}-\x{065F}\x{0640}]/u', '', $title );
    $title = function_exists( 'mb_strtolower' ) ? mb_strtolower( $title ) : strtolower( $title );
    return trim( (string) preg_replace( '/[^\p{L}\p{N}]+/u', ' ', (string) $title ) );
}

function vava_package_followup_total( int $parent_id ): int {
    if ( 'vava_booking' !== get_post_type( $parent_id ) ) { return 0; }
    $stored = get_post_meta( $parent_id, '_vava_package_followup_total', true );
    if ( '' !== (string) $stored ) { return min( 20, max( 0, absint( $stored ) ) ); }

    $uid = sanitize_key( (string) get_post_meta( $parent_id, '_vava_booking_service_uid', true ) );
    $kind = (string) get_post_meta( $parent_id, '_vava_booking_service_kind', true );
    if ( ! $uid || ! in_array( $kind, array( 'session', 'package' ), true ) ||
         ! function_exists( 'vava_booking_paths_page_id' ) || ! function_exists( 'vava_paths_data' ) ) { return 0; }
    $data = vava_paths_data( vava_booking_paths_page_id(), 'ar' );
    $plans = (array) ( $data['compare']['plans'] ?? array() );
    $catalogue = (array) ( $data['packages'] ?? array() );
    $lookup_title = '';

    if ( 'session' === $kind ) {
        // Only catalogue-listed purchases can obtain session credits.
        $found = false;
        foreach ( $catalogue as $item ) {
            if ( $uid !== sanitize_key( (string) ( $item['uid'] ?? '' ) ) ) { continue; }
            $found = true;
            $category = sanitize_key( (string) ( $item['category'] ?? '' ) );
            $lookup_title = (string) ( $item['title'] ?? '' );
            // A 3-session follow-up bundle books its first appointment at checkout.
            if ( 'session-7' === $uid && 'followup' === $category ) { return 2; }
            if ( 'comprehensive' !== $category ) { return 0; }
            break;
        }
        if ( ! $found || ! $lookup_title ) { return 0; }
    }

    foreach ( $plans as $plan ) {
        $plan_uid = sanitize_key( (string) ( $plan['uid'] ?? '' ) );
        $matches = 'package' === $kind ? ( $uid === $plan_uid ) :
            ( vava_package_followup_normalize_title( $lookup_title ) === vava_package_followup_normalize_title( (string) ( $plan['title'] ?? '' ) ) );
        if ( $matches ) { return vava_package_followup_plan_count( (array) $plan ); }
    }
    return 0;
}

/** Freeze each new purchase's allowance; later catalogue edits cannot change it. */
function vava_package_followup_snapshot( int $booking_id ): void {
    if ( 'vava_booking' !== get_post_type( $booking_id ) || metadata_exists( 'post', $booking_id, '_vava_package_followup_total' ) ) { return; }
    $uid = sanitize_key( (string) get_post_meta( $booking_id, '_vava_booking_service_uid', true ) );
    $kind = (string) get_post_meta( $booking_id, '_vava_booking_service_kind', true );
    if ( 'package' === $kind || ( 'session' === $kind && vava_package_followup_total( $booking_id ) > 0 ) ) {
        update_post_meta( $booking_id, '_vava_package_followup_total', vava_package_followup_total( $booking_id ) );
    }
}

/** Only completed payment/approved parent bookings can grant follow-up access. */
function vava_package_followup_eligible( int $parent_id ): bool {
    if ( ! vava_package_followup_total( $parent_id ) ) { return false; }
    $status = (string) get_post_meta( $parent_id, '_vava_booking_status', true );
    $payment = function_exists( 'vava_booking_effective_payment_status' )
        ? (string) vava_booking_effective_payment_status( $parent_id )
        : (string) get_post_meta( $parent_id, '_vava_booking_payment_status', true );
    return in_array( $status, array( 'paid', 'confirmed', 'completed' ), true ) && 'paid' === $payment
        && ! in_array( (string) get_post_meta( $parent_id, '_vava_booking_refund_status', true ), array( 'pending', 'completed', 'refunded' ), true );
}

/** A package-credit child is not a separately chargeable purchase. */
function vava_package_followup_is_child( int $booking_id ): bool {
    return $booking_id > 0 && 'vava_booking' === get_post_type( $booking_id )
        && absint( get_post_meta( $booking_id, '_vava_package_parent_booking_id', true ) ) > 0;
}

/** Child appointments are ordinary vava_booking records linked to one purchase. */
function vava_package_followup_children( int $parent_id ): array {
    if ( $parent_id < 1 ) { return array(); }
    $ids = get_posts( array(
        'post_type' => 'vava_booking', 'post_status' => 'publish', 'posts_per_page' => -1,
        'fields' => 'ids', 'no_found_rows' => true,
        'meta_query' => array( array( 'key' => '_vava_package_parent_booking_id', 'value' => $parent_id ) ),
    ) );
    return array_values( array_map( 'absint', $ids ) );
}

/** Cancelled children release entitlement; all other states consume it. */
function vava_package_followup_balance( int $parent_id ): array {
    $total = vava_package_followup_total( $parent_id );
    $used = 0;
    foreach ( vava_package_followup_children( $parent_id ) as $child_id ) {
        $status = (string) get_post_meta( $child_id, '_vava_booking_status', true );
        if ( ! in_array( $status, array( 'cancelled', 'customer_cancelled', 'bank_rejected', 'payment_failed' ), true ) ) {
            $used++;
        }
    }
    return array( 'total' => $total, 'used' => $used, 'remaining' => max( 0, $total - $used ) );
}

/** Verify actual ownership, never trust a client-supplied email or booking ID. */
function vava_package_followup_customer_owns( int $parent_id, int $user_id ): bool {
    return $user_id > 0
        && (int) get_post_meta( $parent_id, '_vava_booking_user_id', true ) === $user_id
        && 'vava_booking' === get_post_type( $parent_id );
}

/** Follow-up time must use the existing shared availability and capacity rules. */
function vava_package_followup_service( int $parent_id ): array {
    return array(
        'uid' => 'package-followup-' . $parent_id,
        'kind' => 'session',
        'category' => 'followup',
        'title' => 'جلسة متابعة الباقة',
        'booking_duration' => 30,
        'duration' => '30 دقيقة',
        'enabled' => true,
        'booking_enabled' => true,
        'availability' => '',
    );
}

/**
 * Book one paid entitlement using a short per-parent mutex to prevent
 * simultaneous requests spending the same credit twice.
 */
function vava_package_followup_create( int $parent_id, int $user_id, string $date, string $time ) {
    if ( ! vava_package_followup_customer_owns( $parent_id, $user_id ) || ! vava_package_followup_eligible( $parent_id ) || ( function_exists( 'vava_customer_current_verified_user' ) && ! vava_customer_current_verified_user() ) ) {
        return new WP_Error( 'not_allowed', 'الباقة غير مؤهلة أو لا تخص هذا الحساب.' );
    }
    $lock = 'vava_pkg_followup_lock_' . $parent_id;
    if ( ! add_option( $lock, time(), '', 'no' ) ) {
        $created = (int) get_option( $lock );
        if ( $created && $created < time() - 90 ) {
            delete_option( $lock );
        }
        if ( ! add_option( $lock, time(), '', 'no' ) ) {
            return new WP_Error( 'try_again', 'هناك حجز قيد التنفيذ؛ حاول مرة أخرى.' );
        }
    }
    try {
        if ( ! vava_package_followup_eligible( $parent_id ) ) {
            return new WP_Error( 'not_allowed', 'الباقة غير مؤهلة للحجز.' );
        }
        // A repeated request for the exact same appointment must not spend a second credit.
        foreach ( vava_package_followup_children( $parent_id ) as $existing_id ) {
            $existing_status = (string) get_post_meta( $existing_id, '_vava_booking_status', true );
            if ( in_array( $existing_status, array( 'cancelled', 'customer_cancelled', 'bank_rejected', 'payment_failed' ), true ) ) { continue; }
            if ( absint( get_post_meta( $existing_id, '_vava_booking_user_id', true ) ) !== $user_id ) { continue; }
            if ( $date === (string) get_post_meta( $existing_id, '_vava_booking_date', true ) && $time === (string) get_post_meta( $existing_id, '_vava_booking_time', true ) ) {
                return $existing_id;
            }
        }
        if ( vava_package_followup_balance( $parent_id )['remaining'] < 1 ) {
            return new WP_Error( 'no_balance', 'لا توجد جلسات متابعة متبقية.' );
        }
        $service = vava_package_followup_service( $parent_id );
        $shared = vava_booking_shared_data( vava_booking_page_id() );
        // Date/time selection must be in the future and after the first
        // appointment, including the initial session's full duration.
        try {
            $timezone = new DateTimeZone( (string) ( $shared['timezone'] ?: wp_timezone_string() ) );
            $selected_dt = new DateTimeImmutable( $date . ' ' . $time, $timezone );
            $first_date = (string) get_post_meta( $parent_id, '_vava_booking_date', true );
            $first_time = (string) get_post_meta( $parent_id, '_vava_booking_time', true );
            if ( ! $first_date || ! $first_time ) { return new WP_Error( 'missing_first_session', 'موعد الجلسة الأولى غير مكتمل.' ); }
            $first_dt = new DateTimeImmutable( $first_date . ' ' . $first_time, $timezone );
            $first_end = $first_dt->modify( '+' . max( 10, absint( get_post_meta( $parent_id, '_vava_booking_duration', true ) ) ) . ' minutes' );
            $min_notice = max( 0, absint( $shared['min_notice_hours'] ?? 0 ) );
            if ( $selected_dt <= ( new DateTimeImmutable( 'now', $timezone ) )->modify( '+' . $min_notice . ' hours' ) || $selected_dt < $first_end ) {
                return new WP_Error( 'slot_unavailable', 'اختر موعدًا بعد الجلسة الأولى وفي وقت متاح لاحقًا.' );
            }
        } catch ( Exception $e ) { return new WP_Error( 'slot_unavailable', 'موعد غير صالح.' ); }
        if ( ! preg_match( '/^\\d{4}-\\d{2}-\\d{2}$/', $date ) || ! preg_match( '/^(?:[01]\\d|2[0-3]):[0-5]\\d$/', $time ) || ! in_array( $time, array_column( vava_booking_available_slots( $service, $date, $shared ), 'value' ), true ) ) {
            return new WP_Error( 'slot_unavailable', 'الموعد غير متاح. اختر موعدًا آخر.' );
        }
        $customer = (array) get_post_meta( $parent_id, '_vava_booking_customer', true );
        $lang = (string) get_post_meta( $parent_id, '_vava_booking_language', true ) === 'en' ? 'en' : 'ar';
        $child = wp_insert_post( array(
            'post_type' => 'vava_booking',
            'post_status' => 'publish',
            'post_title' => sprintf( '%s — %s — %s %s', $customer['name'] ?? 'VAVA', $service['title'], $date, $time ),
        ), true );
        if ( is_wp_error( $child ) ) { return $child; }
        $meta = array(
            '_vava_package_parent_booking_id' => $parent_id,
            '_vava_booking_user_id' => $user_id,
            '_vava_booking_status' => 'confirmed',
            '_vava_booking_payment_status' => 'paid',
            '_vava_booking_payment_method' => 'package_credit',
            '_vava_booking_service_uid' => $service['uid'],
            '_vava_booking_service_category' => 'followup',
            '_vava_booking_service_kind' => 'session',
            '_vava_booking_service_title' => $service['title'],
            '_vava_booking_service_price' => '0',
            '_vava_booking_service_currency' => (string) get_post_meta( $parent_id, '_vava_booking_service_currency', true ),
            '_vava_booking_date' => $date,
            '_vava_booking_time' => $time,
            '_vava_booking_duration' => 30,
            '_vava_booking_service_duration' => 30,
            '_vava_booking_customer' => $customer,
            '_vava_booking_customer_email' => (string) get_post_meta( $parent_id, '_vava_booking_customer_email', true ),
            '_vava_booking_language' => $lang,
            '_vava_booking_created_at' => current_time( 'mysql' ),
        );
        foreach ( $meta as $key => $value ) { update_post_meta( $child, $key, $value ); }
        // Demo parent bookings are internal tests: never create Google events or send email.
        if ( 'vava_followup_demo_20261019' === (string) get_post_meta( $parent_id, '_vava_followup_test_marker', true ) ) {
            update_post_meta( $child, '_vava_followup_test_marker', 'vava_followup_demo_20261019' );
        } else {
            // Do not block the customer's HTTP confirmation while SMTP/Google services respond.
            vava_package_followup_queue_confirmation( $child );
            if ( function_exists( 'vava_booking_automation_queue_processing' ) ) { vava_booking_automation_queue_processing( $child ); }
        }
        return $child;
    } finally {
        delete_option( $lock );
    }
}

/**
 * Email confirmation is a background job, never a blocking SMTP call in booking POST.
 * A booking has only one initial confirmation (Google Meet sends a separate update later).
 */
function vava_package_followup_queue_confirmation( int $child_id ): void {
    if ( ! vava_package_followup_is_child( $child_id ) ) { return; }
    if ( get_post_meta( $child_id, '_vava_followup_confirmation_sent_at', true ) ||
         'sent' === (string) get_post_meta( $child_id, '_vava_booking_notification_sent', true ) ) { return; }
    $args = array( $child_id );
    if ( wp_next_scheduled( 'vava_package_followup_send_confirmation_event', $args ) ) { return; }
    $scheduled = wp_schedule_single_event( time() + 5, 'vava_package_followup_send_confirmation_event', $args, true );
    update_post_meta( $child_id, '_vava_followup_confirmation_state',
        is_wp_error( $scheduled ) || ! $scheduled ? 'queue_failed' : 'queued' );
}
function vava_package_followup_send_confirmation_handler( int $child_id ): void {
    if ( ! vava_package_followup_is_child( $child_id ) ) { return; }
    $parent_id = absint( get_post_meta( $child_id, '_vava_package_parent_booking_id', true ) );
    if ( 'vava_followup_demo_20261019' === (string) get_post_meta( $parent_id, '_vava_followup_test_marker', true ) ) { return; }
    $status = (string) get_post_meta( $child_id, '_vava_booking_status', true );
    if ( ! in_array( $status, array( 'confirmed', 'paid' ), true ) ) { return; }
    if ( 'sent' === (string) get_post_meta( $child_id, '_vava_booking_notification_sent', true ) ||
         get_post_meta( $child_id, '_vava_followup_confirmation_sent_at', true ) ) {
        update_post_meta( $child_id, '_vava_followup_confirmation_state', 'sent' );
        return;
    }
    if ( function_exists( 'vava_mail_notifications_enabled' ) && ! vava_mail_notifications_enabled( 'bookings' ) ) {
        update_post_meta( $child_id, '_vava_followup_confirmation_state', 'disabled' );
        return;
    }
    $result = function_exists( 'vava_booking_send_details_email' )
        ? vava_booking_send_details_email( $child_id, false, false )
        : new WP_Error( 'mailer_unavailable', 'Booking email sender unavailable.' );
    if ( ! is_wp_error( $result ) && $result &&
         'sent' === (string) get_post_meta( $child_id, '_vava_booking_notification_sent', true ) ) {
        update_post_meta( $child_id, '_vava_followup_confirmation_sent_at', current_time( 'mysql', true ) );
        update_post_meta( $child_id, '_vava_followup_confirmation_state', 'sent' );
        delete_post_meta( $child_id, '_vava_followup_confirmation_last_error' );
        return;
    }
    $attempts = absint( get_post_meta( $child_id, '_vava_followup_confirmation_attempts', true ) ) + 1;
    update_post_meta( $child_id, '_vava_followup_confirmation_attempts', $attempts );
    update_post_meta( $child_id, '_vava_followup_confirmation_last_error',
        is_wp_error( $result ) ? sanitize_text_field( $result->get_error_message() ) : 'Email not accepted by sender.' );
    if ( $attempts >= 4 ) {
        update_post_meta( $child_id, '_vava_followup_confirmation_state', 'failed' );
        return;
    }
    update_post_meta( $child_id, '_vava_followup_confirmation_state', 'retrying' );
    $args = array( $child_id );
    if ( ! wp_next_scheduled( 'vava_package_followup_send_confirmation_event', $args ) ) {
        wp_schedule_single_event( time() + min( 1800, 60 * ( 2 ** ( $attempts - 1 ) ) ),
            'vava_package_followup_send_confirmation_event', $args );
    }
}
add_action( 'vava_package_followup_send_confirmation_event', 'vava_package_followup_send_confirmation_handler', 10, 1 );

/** Only verified logged-in owner can schedule; GET forms never change data. */
function vava_package_followup_handle_request(): void {
    if ( ! is_user_logged_in() ) { wp_die( 'Authentication required.', '', array( 'response' => 403 ) ); }
    $parent_id = isset( $_POST['parent_id'] ) ? absint( $_POST['parent_id'] ) : 0;
    check_admin_referer( 'vava_package_followup_' . $parent_id );
    $date = isset( $_POST['followup_date'] ) ? sanitize_text_field( wp_unslash( $_POST['followup_date'] ) ) : '';
    $time = isset( $_POST['followup_time'] ) ? sanitize_text_field( wp_unslash( $_POST['followup_time'] ) ) : '';
    $result = vava_package_followup_create( $parent_id, get_current_user_id(), $date, $time );
    if ( isset( $_POST['vava_followup_async'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['vava_followup_async'] ) ) ) {
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ), 422 );
        }
        wp_send_json_success( array( 'booking_id' => absint( $result ), 'message' => 'تم تأكيد جلسة المتابعة بنجاح.' ) );
    }
    $lang = get_post_meta( $parent_id, '_vava_booking_language', true ) === 'en' ? 'en' : 'ar';
    $target = vava_customer_account_url( $lang, array( 'view' => 'bookings', 'package_booking' => $parent_id, 'package_date' => $date ) );
    $result_code = is_wp_error( $result ) ? sanitize_key( $result->get_error_code() ) : 'success';
    // Preserve the result if WordPress or another plugin canonicalizes the return URL.
    set_transient( 'vava_followup_result_' . get_current_user_id() . '_' . $parent_id, $result_code, 5 * MINUTE_IN_SECONDS );
    $target = add_query_arg( array( 'package_result' => $result_code ), $target );
    wp_safe_redirect( $target . '#vava-booking-' . $parent_id );
    exit;
}
add_action( 'admin_post_vava_package_followup_book', 'vava_package_followup_handle_request' );
// WordPress AJAX endpoint for in-account confirmation; authenticated customers only.
add_action( 'wp_ajax_vava_package_followup_book', 'vava_package_followup_handle_request' );

/** Render a server-side date/slot picker within the existing account booking card. */
function vava_package_followup_render_account( int $parent_id, string $lang ): void {
    if ( ! is_user_logged_in() || ( function_exists( 'vava_customer_current_verified_user' ) && ! vava_customer_current_verified_user() ) || ! vava_package_followup_customer_owns( $parent_id, get_current_user_id() ) ) { return; }
    $total = vava_package_followup_total( $parent_id );
    if ( ! $total ) { return; }
    $balance = vava_package_followup_balance( $parent_id );
    $is_en = 'en' === $lang;
    $result_code = isset( $_GET['package_result'], $_GET['package_booking'] ) && absint( $_GET['package_booking'] ) === $parent_id
        ? sanitize_key( wp_unslash( $_GET['package_result'] ) ) : '';
    if ( ! $result_code ) {
        $result_code = sanitize_key( (string) get_transient( 'vava_followup_result_' . get_current_user_id() . '_' . $parent_id ) );
    }
    if ( $result_code ) {
        delete_transient( 'vava_followup_result_' . get_current_user_id() . '_' . $parent_id );
    }
    $result_messages = array(
        'success' => array( 'تم تأكيد جلسة المتابعة بنجاح.', 'Your follow-up was booked successfully.' ),
        'slot_unavailable' => array( 'الموعد لم يعد متاحًا أو لا يستوفي شروط المتابعة. اختر وقتًا آخر بعد الجلسة الأساسية.', 'The time is unavailable or too early. Choose another time after your initial session.' ),
        'not_allowed' => array( 'تعذر الحجز: تحقق من الحساب واعتماد دفع الباقة.', 'Booking not permitted: check your account and package payment.' ),
        'no_balance' => array( 'لا توجد جلسات متابعة متبقية.', 'No follow-up credits remain.' ),
        'try_again' => array( 'هناك حجز آخر قيد التنفيذ. حدّث الصفحة قبل المحاولة مجددًا.', 'Another booking is processing. Refresh before retrying.' ),
        'missing_first_session' => array( 'موعد الجلسة الأساسية غير مكتمل. تواصل مع الدعم.', 'The initial appointment is incomplete. Contact support.' ),
    );
    echo '<section class="vava-package-followups" style="padding:22px;border:1px solid #d5dcc9;background:linear-gradient(150deg,#fffefa,#f5f7ee);border-radius:18px;margin-block:16px;box-shadow:0 6px 24px rgba(76,83,52,.05)">';
    echo '<h4>' . esc_html( $is_en ? 'Package follow-up sessions' : 'جلسات متابعة الباقة' ) . '</h4>';
    if ( $result_code ) {
        $result_message = $result_messages[ $result_code ] ?? array( 'تعذر تأكيد المتابعة. راجع الموعد وحاول مجددًا.', 'Could not book the follow-up. Review the time and retry.' );
        echo '<p role="status" aria-live="polite" style="padding:12px;border-radius:9px;border:1px solid ' . ( 'success' === $result_code ? '#a5b793' : '#d5a69a' ) . ';background:' . ( 'success' === $result_code ? '#eef5e8' : '#fff0e9' ) . '">' . esc_html( $result_message[ $is_en ? 1 : 0 ] ) . '</p>';
    }
    echo '<p>' . esc_html( ( $is_en ? 'Remaining: ' : 'الجلسات المتبقية: ' ) . $balance['remaining'] . ' / ' . $balance['total'] ) . '</p>';
    $children = vava_package_followup_children( $parent_id );
    if ( $children ) {
        echo '<ul class="vava-followup-history" style="margin:14px 0;padding-inline-start:22px">';
        foreach ( $children as $child_id ) {
            $child_status = (string) get_post_meta( $child_id, '_vava_booking_status', true );
            $child_date = (string) get_post_meta( $child_id, '_vava_booking_date', true );
            $child_time = (string) get_post_meta( $child_id, '_vava_booking_time', true );
            $status_text = function_exists( 'vava_booking_status_label' ) ? vava_booking_status_label( $child_status ) : $child_status;
            echo '<li>' . esc_html( $child_date . ' ' . $child_time . ' — ' . $status_text );
            $meeting = esc_url_raw( (string) get_post_meta( $child_id, '_vava_booking_meeting_url', true ) );
            if ( $meeting && 'https' === strtolower( (string) wp_parse_url( $meeting, PHP_URL_SCHEME ) ) && 'meet.google.com' === strtolower( (string) wp_parse_url( $meeting, PHP_URL_HOST ) ) ) {
                echo ' — <a href="' . esc_url( $meeting ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $is_en ? 'Join Google Meet' : 'دخول Google Meet' ) . '</a>';
            }
            echo '</li>';
        }
        echo '</ul>';
    }
    if ( ! vava_package_followup_eligible( $parent_id ) ) {
        echo '<p>' . esc_html( $is_en ? 'Available once payment is approved.' : 'تتاح المتابعات بعد اعتماد الدفع.' ) . '</p></section>';
        return;
    }
    if ( $balance['remaining'] < 1 ) { echo '</section>'; return; }
    $date = isset( $_GET['package_date'] ) && absint( $_GET['package_booking'] ?? 0 ) === $parent_id ? sanitize_text_field( wp_unslash( $_GET['package_date'] ) ) : '';
    $service = vava_package_followup_service( $parent_id );
    $shared = vava_booking_shared_data( vava_booking_page_id() );
    $max_days = max( 1, absint( $shared['max_days'] ?? 60 ) );
    $latest_day = wp_date( 'Y-m-d', strtotime( '+' . $max_days . ' days', current_time( 'timestamp' ) ) );
    $date_outside_window = $date && ( $date < wp_date( 'Y-m-d' ) || $date > $latest_day );
    $slots = $date && ! $date_outside_window ? vava_booking_available_slots( $service, $date, $shared ) : array();
    // Apply the same first-session and minimum-notice rules before displaying choices.
    if ( $slots ) {
        try {
            $timezone = new DateTimeZone( (string) ( $shared['timezone'] ?: wp_timezone_string() ) );
            $first_date = (string) get_post_meta( $parent_id, '_vava_booking_date', true );
            $first_time = (string) get_post_meta( $parent_id, '_vava_booking_time', true );
            $first_end = ( new DateTimeImmutable( $first_date . ' ' . $first_time, $timezone ) )->modify( '+' . max( 10, absint( get_post_meta( $parent_id, '_vava_booking_duration', true ) ) ) . ' minutes' );
            $earliest = ( new DateTimeImmutable( 'now', $timezone ) )->modify( '+' . max( 0, absint( $shared['min_notice_hours'] ?? 0 ) ) . ' hours' );
            $slots = array_values( array_filter( $slots, static function ( $slot ) use ( $date, $timezone, $first_end, $earliest ) {
                $candidate = new DateTimeImmutable( $date . ' ' . (string) ( $slot['value'] ?? '' ), $timezone );
                return $candidate >= $first_end && $candidate > $earliest;
            } ) );
        } catch ( Exception $e ) { $slots = array(); }
    }
    $base = vava_customer_account_url( $lang, array( 'view' => 'bookings', 'package_booking' => $parent_id ) );
    echo '<form method="get" action="' . esc_url( $base ) . '">';
    echo '<input type="hidden" name="page_id" value="' . esc_attr( (string) get_queried_object_id() ) . '"><input type="hidden" name="vava_lang" value="' . esc_attr( $lang ) . '">';
    echo '<input type="hidden" name="view" value="bookings"><input type="hidden" name="package_booking" value="' . esc_attr( (string) $parent_id ) . '">';
    echo '<label>' . esc_html( $is_en ? 'Choose a date' : 'اختر يوم المتابعة' ) . ' <input required type="date" name="package_date" value="' . esc_attr( $date ) . '" min="' . esc_attr( wp_date( 'Y-m-d' ) ) . '" max="' . esc_attr( $latest_day ) . '"></label> ';
    echo '<button type="submit">' . esc_html( $is_en ? 'Show times' : 'عرض المواعيد' ) . '</button></form>';
    if ( $date ) {
        if ( ! $slots ) { echo '<p>' . esc_html( $date_outside_window ? ( $is_en ? 'This date is outside the bookable window. Choose a date through ' . $latest_day . '.' : 'التاريخ خارج فترة الحجز المتاحة. اختر موعدًا حتى ' . $latest_day . '.' ) : ( $is_en ? 'No available times on this date.' : 'لا توجد مواعيد متاحة في هذا اليوم.' ) ) . '</p>'; }
        else {
            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
            echo '<input type="hidden" name="action" value="vava_package_followup_book">';
            echo '<input type="hidden" name="parent_id" value="' . esc_attr( (string) $parent_id ) . '">';
            echo '<input type="hidden" name="followup_date" value="' . esc_attr( $date ) . '">';
            wp_nonce_field( 'vava_package_followup_' . $parent_id );
            echo '<label>' . esc_html( $is_en ? 'Available time' : 'الوقت المتاح' ) . ' <select name="followup_time" required>';
            foreach ( $slots as $slot ) { echo '<option value="' . esc_attr( $slot['value'] ) . '">' . esc_html( $slot['label'] ) . '</option>'; }
            echo '</select></label> <button type="submit">' . esc_html( $is_en ? 'Book follow-up at no extra charge' : 'تأكيد جلسة المتابعة بدون دفع إضافي' ) . '</button></form>';
        }
    }
    echo '</section>';
}
