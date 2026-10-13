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
    return false !== mb_strpos( $name, 'التناغم المستدام' ) ? 2 : 0;
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
