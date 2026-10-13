<?php
/**
 * VAVA package follow-up entitlements (phase 1).
 * No booking creation or calendar side effects: the module is safe to include
 * before the scheduling UI is enabled.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Explicitly configured session entitlements. No default for unrelated packages.
 * Administrators may set _vava_package_followup_total on the parent order.
 */
function vava_package_followup_total( int $parent_id ): int {
    if ( 'vava_booking' !== get_post_type( $parent_id ) ) { return 0; }
    if ( 'package' !== (string) get_post_meta( $parent_id, '_vava_booking_service_kind', true ) ) { return 0; }
    $stored = get_post_meta( $parent_id, '_vava_package_followup_total', true );
    if ( '' !== (string) $stored ) { return min( 20, max( 0, absint( $stored ) ) ); }
    // The agreed Sustainable Harmony offer contains two 30-minute follow-ups.
    $name = (string) get_post_meta( $parent_id, '_vava_booking_service_title', true );
    return false !== strpos( $name, 'التناغم المستدام' ) ? 2 : 0;
}

/** Only completed payment/approved parent bookings can grant follow-up access. */
function vava_package_followup_eligible( int $parent_id ): bool {
    if ( ! vava_package_followup_total( $parent_id ) ) { return false; }
    $status = (string) get_post_meta( $parent_id, '_vava_booking_status', true );
    $payment = function_exists( 'vava_booking_effective_payment_status' )
        ? (string) vava_booking_effective_payment_status( $parent_id )
        : (string) get_post_meta( $parent_id, '_vava_booking_payment_status', true );
    return in_array( $status, array( 'paid', 'confirmed', 'completed' ), true ) && 'paid' === $payment;
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
    if ( ! vava_package_followup_customer_owns( $parent_id, $user_id ) || ! vava_package_followup_eligible( $parent_id ) ) {
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
        if ( ! vava_package_followup_eligible( $parent_id ) || vava_package_followup_balance( $parent_id )['remaining'] < 1 ) {
            return new WP_Error( 'no_balance', 'لا توجد جلسات متابعة متبقية.' );
        }
        $service = vava_package_followup_service( $parent_id );
        $shared = vava_booking_shared_data( vava_booking_page_id() );
        if ( ! in_array( $time, array_column( vava_booking_available_slots( $service, $date, $shared ), 'value' ), true ) ) {
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
            '_vava_booking_customer' => $customer,
            '_vava_booking_customer_email' => (string) get_post_meta( $parent_id, '_vava_booking_customer_email', true ),
            '_vava_booking_language' => $lang,
            '_vava_booking_created_at' => current_time( 'mysql' ),
        );
        foreach ( $meta as $key => $value ) { update_post_meta( $child, $key, $value ); }
        if ( function_exists( 'vava_booking_send_confirmation' ) ) { vava_booking_send_confirmation( $child ); }
        return $child;
    } finally {
        delete_option( $lock );
    }
}

/** Only verified logged-in owner can schedule; GET forms never change data. */
function vava_package_followup_handle_request(): void {
    if ( ! is_user_logged_in() ) { wp_die( 'Authentication required.', '', array( 'response' => 403 ) ); }
    $parent_id = isset( $_POST['parent_id'] ) ? absint( $_POST['parent_id'] ) : 0;
    check_admin_referer( 'vava_package_followup_' . $parent_id );
    $date = isset( $_POST['followup_date'] ) ? sanitize_text_field( wp_unslash( $_POST['followup_date'] ) ) : '';
    $time = isset( $_POST['followup_time'] ) ? sanitize_text_field( wp_unslash( $_POST['followup_time'] ) ) : '';
    $result = vava_package_followup_create( $parent_id, get_current_user_id(), $date, $time );
    $lang = get_post_meta( $parent_id, '_vava_booking_language', true ) === 'en' ? 'en' : 'ar';
    $target = vava_customer_account_url( $lang, array( 'view' => 'bookings', 'package_booking' => $parent_id ) );
    $target = add_query_arg( is_wp_error( $result ) ? array( 'package_result' => sanitize_key( $result->get_error_code() ) ) : array( 'package_result' => 'success' ), $target );
    wp_safe_redirect( $target . '#vava-booking-' . $parent_id );
    exit;
}
add_action( 'admin_post_vava_package_followup_book', 'vava_package_followup_handle_request' );

/** Render a server-side date/slot picker within the existing account booking card. */
function vava_package_followup_render_account( int $parent_id, string $lang ): void {
    if ( ! is_user_logged_in() || ! vava_package_followup_customer_owns( $parent_id, get_current_user_id() ) ) { return; }
    $total = vava_package_followup_total( $parent_id );
    if ( ! $total ) { return; }
    $balance = vava_package_followup_balance( $parent_id );
    $is_en = 'en' === $lang;
    echo '<section class="vava-package-followups" style="padding:16px;border:1px solid #d7d7d7;border-radius:12px;margin-block:12px">';
    echo '<h4>' . esc_html( $is_en ? 'Package follow-up sessions' : 'جلسات متابعة الباقة' ) . '</h4>';
    echo '<p>' . esc_html( ( $is_en ? 'Remaining: ' : 'الجلسات المتبقية: ' ) . $balance['remaining'] . ' / ' . $balance['total'] ) . '</p>';
    if ( ! vava_package_followup_eligible( $parent_id ) ) {
        echo '<p>' . esc_html( $is_en ? 'Available once payment is approved.' : 'تتاح المتابعات بعد اعتماد الدفع.' ) . '</p></section>';
        return;
    }
    if ( $balance['remaining'] < 1 ) { echo '</section>'; return; }
    $date = isset( $_GET['package_date'] ) && absint( $_GET['package_booking'] ?? 0 ) === $parent_id ? sanitize_text_field( wp_unslash( $_GET['package_date'] ) ) : '';
    $service = vava_package_followup_service( $parent_id );
    $shared = vava_booking_shared_data( vava_booking_page_id() );
    $slots = $date ? vava_booking_available_slots( $service, $date, $shared ) : array();
    $base = vava_customer_account_url( $lang, array( 'view' => 'bookings', 'package_booking' => $parent_id ) );
    echo '<form method="get" action="' . esc_url( $base ) . '">';
    echo '<input type="hidden" name="view" value="bookings"><input type="hidden" name="package_booking" value="' . esc_attr( (string) $parent_id ) . '">';
    echo '<label>' . esc_html( $is_en ? 'Choose a date' : 'اختر يوم المتابعة' ) . ' <input required type="date" name="package_date" value="' . esc_attr( $date ) . '" min="' . esc_attr( wp_date( 'Y-m-d' ) ) . '"></label> ';
    echo '<button type="submit">' . esc_html( $is_en ? 'Show times' : 'عرض المواعيد' ) . '</button></form>';
    if ( $date ) {
        if ( ! $slots ) { echo '<p>' . esc_html( $is_en ? 'No available times on this date.' : 'لا توجد مواعيد متاحة في هذا اليوم.' ) . '</p>'; }
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
