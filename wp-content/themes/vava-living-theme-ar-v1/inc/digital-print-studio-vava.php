<?php
/**
 * Branded printing workflow for purchased VAVA digital PDF products.
 * All HTML and PDF requests are separately authenticated and authorized.
 */
defined( 'ABSPATH' ) || exit;

function vava_digital_access_print_preview_url( string $uid, WP_User $user, string $lang = 'ar' ): string {
    $uid = sanitize_key( $uid );
    if ( '' === $uid || ! vava_digital_access_can( $uid, 'print' ) ||
        ! vava_digital_products_user_can_view( $user->ID, $uid ) ) { return ''; }
    return add_query_arg( array(
        'action' => 'vava_digital_access_print_preview',
        'product' => $uid,
        'lang' => 'en' === $lang ? 'en' : 'ar',
        '_wpnonce' => wp_create_nonce( 'vava_digital_print_preview_' . $uid . '_' . $user->ID ),
    ), admin_url( 'admin-post.php' ) );
}

function vava_digital_access_print_preview(): void {
    $uid = isset( $_GET['product'] ) ? sanitize_key( wp_unslash( $_GET['product'] ) ) : '';
    $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
    $lang = isset( $_GET['lang'] ) && 'en' === sanitize_key( wp_unslash( $_GET['lang'] ) ) ? 'en' : 'ar';
    $is_en = 'en' === $lang;
    $user = function_exists( 'vava_customer_current_verified_user' ) ? vava_customer_current_verified_user() : null;
    if ( ! $user instanceof WP_User || '' === $uid ||
        ! wp_verify_nonce( $nonce, 'vava_digital_print_preview_' . $uid . '_' . $user->ID ) ||
        ! vava_digital_access_can( $uid, 'print' ) ||
        ! vava_digital_products_user_can_view( $user->ID, $uid ) ) {
        status_header( 403 ); nocache_headers(); exit( 'Access denied.' );
    }
    $record = vava_digital_products_file_record( $uid );
    $file = vava_digital_products_private_file_path( $record );
    if ( ! $file || ! is_readable( $file ) || 'ready' !== (string) ( $record['processing_status'] ?? '' ) ||
        ! is_file( $file ) || ! filesize( $file ) ) {
        status_header( 404 ); nocache_headers(); exit( 'PDF unavailable.' );
    }
    $order_id = vava_digital_products_latest_order( $uid, $user->ID );
    $print_url = vava_digital_access_url( $uid, 'print', $user );
    $download_url = vava_digital_access_url( $uid, 'download', $user );
    if ( ! $print_url || ! $order_id ) { status_header( 403 ); nocache_headers(); exit( 'Access denied.' ); }
    $product = function_exists( 'vava_digital_product_data' ) ? vava_digital_product_data( $uid, $lang ) : array();
    if ( ! $product ) {
        $page_id = function_exists( 'vava_selections_page_id' ) ? absint( vava_selections_page_id() ) : 0;
        $stored = $page_id ? get_post_meta( $page_id, '_vava_selections_products_' . $lang, true ) : array();
        foreach ( (array) ( $stored['digital'] ?? array() ) as $entry ) {
            if ( sanitize_key( (string) ( $entry['uid'] ?? '' ) ) === $uid ) { $product = $entry; break; }
        }
    }
    $title = sanitize_text_field( (string) ( $product['title'] ?? '' ) );
    if ( '' === $title ) { $title = $is_en ? 'My digital product' : 'منتجي الرقمي'; }
    $page_count = absint( $record['page_count'] ?? 0 );
    $filesize = function_exists( 'vava_digital_products_format_bytes' ) ? vava_digital_products_format_bytes( (int) filesize( $file ) ) : '';
    $cover_url = function_exists( 'vava_digital_products_cover_url' ) ? vava_digital_products_cover_url( $uid ) : '';
    $reader_url = vava_digital_products_viewer_url( $uid, $lang );
    $library_url = vava_customer_account_url( $lang, array( 'view' => 'products' ) );
    $style_url = add_query_arg(
        'ver', function_exists( 'vava_asset_version' ) ? vava_asset_version( 'assets/css/digital-print-studio-vava.css' ) : '1',
        get_theme_file_uri( 'assets/css/digital-print-studio-vava.css' )
    );
    nocache_headers();
    header( 'X-Robots-Tag: noindex, nofollow, noarchive' );
    header( 'Referrer-Policy: no-referrer' );
    header( 'X-Content-Type-Options: nosniff' );
    header( 'X-Frame-Options: SAMEORIGIN' );
    header( "Content-Security-Policy: default-src 'self'; script-src 'none'; style-src 'self'; img-src 'self' data:; frame-src 'self'; frame-ancestors 'self'; base-uri 'none'; form-action 'self'" );
    require get_template_directory() . '/template-parts/digital-print-studio-vava.php';
    exit;
}
add_action( 'admin_post_vava_digital_access_print_preview', 'vava_digital_access_print_preview' );
add_action( 'admin_post_nopriv_vava_digital_access_print_preview', 'vava_digital_access_print_preview' );
