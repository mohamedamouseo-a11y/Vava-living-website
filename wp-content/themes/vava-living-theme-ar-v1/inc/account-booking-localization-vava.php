<?php
/**
 * Localize customer booking titles from canonical AR/EN catalogues.
 * Historical titles can have only one locale; preserve stored booking metadata.
 */
defined( 'ABSPATH' ) || exit;

function vava_account_booking_localized_title( int $booking_id, string $language = 'ar' ): string {
    $language = 'en' === $language ? 'en' : 'ar';
    $uid = sanitize_key( (string) get_post_meta( $booking_id, '_vava_booking_service_uid', true ) );
    $saved = trim( (string) get_post_meta( $booking_id, '_vava_booking_service_title', true ) );
    if ( str_starts_with( $uid, 'package-followup-' ) ) {
        return 'en' === $language ? 'Package follow-up session' : 'جلسة متابعة الباقة';
    }
    if ( $uid && function_exists( 'vava_booking_resolve_service' ) ) {
        $service = vava_booking_resolve_service( $uid, $language );
        if ( is_array( $service ) && ! empty( $service['title'] ) ) {
            return sanitize_text_field( (string) $service['title'] );
        }
    }
    if ( $uid && function_exists( 'vava_digital_product_data' ) ) {
        $product = vava_digital_product_data( $uid, $language );
        if ( is_array( $product ) && ! empty( $product['title'] ) ) {
            return sanitize_text_field( (string) $product['title'] );
        }
    }
    $localized = trim( (string) get_post_meta( $booking_id, '_vava_booking_service_title_' . $language, true ) );
    if ( $localized && ( 'ar' === $language || ! preg_match( '/[\x{0600}-\x{06FF}]/u', $localized ) ) ) {
        return sanitize_text_field( $localized );
    }
    if ( $saved && function_exists( 'vava_booking_services' ) ) {
        $other_lang = 'ar' === $language ? 'en' : 'ar';
        $original_services = (array) vava_booking_services( $other_lang );
        $localized_services = (array) vava_booking_services( $language );
        foreach ( $original_services as $key => $service ) {
            if ( ! is_array( $service ) || trim( (string) ( $service['title'] ?? '' ) ) !== $saved ) { continue; }
            if ( ! empty( $localized_services[ $key ]['title'] ) ) {
                return sanitize_text_field( (string) $localized_services[ $key ]['title'] );
            }
        }
    }
    if ( 'en' === $language && preg_match( '/[\x{0600}-\x{06FF}]/u', $saved ) ) {
        $kind = sanitize_key( (string) get_post_meta( $booking_id, '_vava_booking_service_kind', true ) );
        return 'digital_product' === $kind ? 'Digital product' : 'Booked session';
    }
    return $saved ?: ( 'en' === $language ? 'Booked session' : 'جلسة محجوزة' );
}

function vava_account_booking_localized_status( string $status, string $language = 'ar' ): string {
    $status = sanitize_key( $status );
    if ( 'en' !== $language ) {
        return function_exists( 'vava_booking_status_label' ) ? vava_booking_status_label( $status ) : $status;
    }
    $labels = array(
        'pending' => 'Pending', 'pending_payment' => 'Awaiting payment',
        'pending_bank_review' => 'Awaiting transfer review', 'confirmed' => 'Confirmed',
        'paid' => 'Paid', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
        'customer_cancelled' => 'Cancelled by customer', 'cancellation_requested' => 'Cancellation requested',
        'bank_rejected' => 'Transfer rejected', 'payment_failed' => 'Payment failed',
        'payment_error' => 'Payment error', 'refund_pending' => 'Refund pending',
        'partially_refunded' => 'Partially refunded', 'refunded' => 'Refunded',
    );
    return $labels[ $status ] ?? ucwords( str_replace( '_', ' ', $status ) );
}
