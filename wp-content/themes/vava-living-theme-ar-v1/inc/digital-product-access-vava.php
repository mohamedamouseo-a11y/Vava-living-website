<?php
/**
 * Product-by-product authorized print and download permissions for VAVA.
 * Existing products default to view-only. Private PDFs never get public URLs.
 */
defined( 'ABSPATH' ) || exit;

/** Allowed modes: protected reading, printing, download, or both. */
function vava_digital_access_mode( string $uid ): string {
    $uid = sanitize_key( $uid );
    if ( '' === $uid ) { return 'view'; }
    $page_id = function_exists( 'vava_selections_page_id' ) ? absint( vava_selections_page_id() ) : 0;
    $map = $page_id ? get_post_meta( $page_id, '_vava_digital_product_access_modes', true ) : array();
    $mode = is_array( $map ) ? sanitize_key( (string) ( $map[ $uid ] ?? 'view' ) ) : 'view';
    return in_array( $mode, array( 'view', 'print', 'download', 'print_download' ), true ) ? $mode : 'view';
}
function vava_digital_access_can( string $uid, string $action ): bool {
    $mode = vava_digital_access_mode( $uid );
    return 'print' === $action
        ? in_array( $mode, array( 'print', 'print_download' ), true )
        : ( 'download' === $action && in_array( $mode, array( 'download', 'print_download' ), true ) );
}
function vava_digital_access_product_exists( int $page_id, string $uid ): bool {
    $uid = sanitize_key( $uid );
    if ( ! $uid || ! $page_id ) { return false; }
    if ( function_exists( 'vava_digital_products_file_record' ) && vava_digital_products_file_record( $uid, $page_id ) ) { return true; }
    foreach ( array( 'ar', 'en' ) as $lang ) {
        $stored = get_post_meta( $page_id, '_vava_selections_products_' . $lang, true );
        foreach ( (array) ( (array) $stored )['digital'] ?? array() as $entry ) {
            if ( sanitize_key( (string) ( $entry['uid'] ?? '' ) ) === $uid ) { return true; }
        }
    }
    return false;
}
/** Save permission independently from PDF file processing and bilingual fields. */
function vava_digital_access_admin_save(): void {
    $page_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    $uid = isset( $_POST['uid'] ) ? sanitize_key( wp_unslash( $_POST['uid'] ) ) : '';
    $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
    $mode = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : '';
    if ( ! $page_id || ! current_user_can( 'edit_page', $page_id ) ||
        ! function_exists( 'vava_selections_is_page' ) || ! vava_selections_is_page( $page_id ) ||
        ! wp_verify_nonce( $nonce, 'vava_digital_product_admin_' . $page_id ) ) {
        wp_send_json_error( array( 'message' => 'غير مصرح بتغيير صلاحيات المنتج.' ), 403 );
    }
    if ( ! vava_digital_access_product_exists( $page_id, $uid ) ||
        ! in_array( $mode, array( 'view', 'print', 'download', 'print_download' ), true ) ) {
        wp_send_json_error( array( 'message' => 'المنتج أو نوع الصلاحية غير صالح.' ), 422 );
    }
    $map = get_post_meta( $page_id, '_vava_digital_product_access_modes', true );
    $map = is_array( $map ) ? $map : array();
    if ( 'view' === $mode ) { unset( $map[ $uid ] ); }
    else { $map[ $uid ] = $mode; }
    if ( ! update_post_meta( $page_id, '_vava_digital_product_access_modes', $map ) &&
        $map !== (array) get_post_meta( $page_id, '_vava_digital_product_access_modes', true ) ) {
        wp_send_json_error( array( 'message' => 'تعذر حفظ صلاحية المنتج.' ), 500 );
    }
    wp_send_json_success( array(
        'mode' => vava_digital_access_mode( $uid ),
        'message' => 'تم حفظ صلاحية المنتج بنجاح.',
    ) );
}
add_action( 'wp_ajax_vava_digital_access_admin_save', 'vava_digital_access_admin_save' );

/** Show a per-product settings field in the existing VAVA Selections PDF editor. */
function vava_digital_access_admin_field( string $uid, string $lang = 'ar' ): void {
    $uid = sanitize_key( $uid );
    $mode = vava_digital_access_mode( $uid );
    $is_en = 'en' === $lang;
    $options = array(
        'view' => $is_en ? 'View only — protected (default)' : 'مشاهدة فقط — محمي (الافتراضي)',
        'print' => $is_en ? 'View and print' : 'مشاهدة وطباعة',
        'download' => $is_en ? 'View and download' : 'مشاهدة وتحميل',
        'print_download' => $is_en ? 'View, print and download' : 'مشاهدة وطباعة وتحميل',
    );
    echo '<div class="vava-digital-access-box" data-vava-digital-access-box>'
        . '<label><span>' . esc_html( $is_en ? 'Customer file permissions' : 'صلاحيات الملف للعميل' ) . '</span>'
        . '<select data-vava-digital-access-mode data-last-mode="' . esc_attr( $mode ) . '">';
    foreach ( $options as $key => $label ) {
        echo '<option value="' . esc_attr( $key ) . '"' . selected( $mode, $key, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select></label>'
        . '<p class="description">' . esc_html( $is_en
            ? 'Existing products stay view-only unless you change this option. Print-only can still allow saving through the browser PDF viewer.'
            : 'تظل المنتجات الحالية للمشاهدة فقط حتى تغيّري هذا الإعداد. قد يسمح عارض PDF في المتصفح بحفظ نسخة حتى في وضع الطباعة.' ) . '</p>'
        . '<p class="vava-digital-access-feedback" data-vava-digital-access-feedback role="status" aria-live="polite"></p></div>';
}

/**
 * Private PDF stream: first check isolated verified customer account, payment,
 * order access, per-product mode, nonce, then the realpath guarded private file.
 */
function vava_digital_access_stream(): void {
    $uid = isset( $_GET['product'] ) ? sanitize_key( wp_unslash( $_GET['product'] ) ) : '';
    $mode = isset( $_GET['mode'] ) ? sanitize_key( wp_unslash( $_GET['mode'] ) ) : '';
    $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
    $user = function_exists( 'vava_customer_current_verified_user' ) ? vava_customer_current_verified_user() : null;
    if ( ! in_array( $mode, array( 'print', 'download' ), true ) ||
        ! $user instanceof WP_User ||
        ! wp_verify_nonce( $nonce, 'vava_digital_access_' . $uid . '_' . $mode . '_' . $user->ID ) ||
        ! vava_digital_access_can( $uid, $mode ) ||
        ! function_exists( 'vava_digital_products_user_can_view' ) ||
        ! vava_digital_products_user_can_view( $user->ID, $uid ) ) {
        status_header( 403 ); nocache_headers(); exit( 'Access denied.' );
    }
    $record = vava_digital_products_file_record( $uid );
    $file = vava_digital_products_private_file_path( $record );
    if ( ! $file || ! is_readable( $file ) || ( 'ready' !== (string) ( $record['processing_status'] ?? '' ) ) ||
        ! is_file( $file ) || ! filesize( $file ) || '%PDF-' !== (string) file_get_contents( $file, false, null, 0, 5 ) ) {
        status_header( 404 ); nocache_headers(); exit( 'PDF unavailable.' );
    }

    $order_id = vava_digital_products_latest_order( $uid, $user->ID );
    if ( ! $order_id ) { status_header( 403 ); exit; }
    $filename = sanitize_file_name( 'VAVA-' . $uid . '-order-' . $order_id . '.pdf' );
    while ( ob_get_level() ) { ob_end_clean(); }
    @set_time_limit( 180 );
    header( 'Content-Type: application/pdf' );
    header( 'Content-Disposition: ' . ( 'download' === $mode ? 'attachment' : 'inline' ) . '; filename="' . $filename . '"' );
    header( 'Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0' );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );
    header( 'X-Content-Type-Options: nosniff' );
    header( 'X-Robots-Tag: noindex, nofollow, noarchive' );
    header( 'Referrer-Policy: no-referrer' );
    header( 'Content-Security-Policy: frame-ancestors \'self\'' );
    $total = filesize( $file );
    // Range requests are important for Safari/iOS PDF viewers and large workbooks.
    $start = 0;
    $end = $total - 1;
    $range = isset( $_SERVER['HTTP_RANGE'] ) ? trim( (string) $_SERVER['HTTP_RANGE'] ) : '';
    header( 'Accept-Ranges: bytes' );
    if ( $range ) {
        if ( ! preg_match( '/^bytes=(\d*)-(\d*)$/', $range, $m ) || ( '' === $m[1] && '' === $m[2] ) ) {
            status_header( 416 ); header( 'Content-Range: bytes */' . $total ); exit;
        }
        if ( '' === $m[1] ) { $start = max( 0, $total - (int) $m[2] ); }
        else { $start = (int) $m[1]; }
        if ( '' !== $m[2] && '' !== $m[1] ) { $end = min( $end, (int) $m[2] ); }
        if ( $start >= $total || $start > $end ) {
            status_header( 416 ); header( 'Content-Range: bytes */' . $total ); exit;
        }
        status_header( 206 );
        header( 'Content-Range: bytes ' . $start . '-' . $end . '/' . $total );
    }
    $length = $end - $start + 1;
    header( 'Content-Length: ' . $length );
    if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'HEAD' === $_SERVER['REQUEST_METHOD'] ) { exit; }
    $handle = fopen( $file, 'rb' );
    if ( ! $handle ) { exit; }
    fseek( $handle, $start );
    $remaining = $length;
    while ( $remaining > 0 && ! feof( $handle ) && ! connection_aborted() ) {
        $chunk = fread( $handle, min( 1024 * 512, $remaining ) );
        if ( false === $chunk || '' === $chunk ) { break; }
        echo $chunk; // Authorized binary PDF output.
        $remaining -= strlen( $chunk );
        if ( function_exists( 'flush' ) ) { flush(); }
    }
    fclose( $handle );
    exit;
}
add_action( 'admin_post_vava_digital_access_pdf', 'vava_digital_access_stream' );
add_action( 'admin_post_nopriv_vava_digital_access_pdf', 'vava_digital_access_stream' );

function vava_digital_access_url( string $uid, string $mode, WP_User $user ): string {
    $uid = sanitize_key( $uid );
    if ( ! in_array( $mode, array( 'print', 'download' ), true ) || ! vava_digital_access_can( $uid, $mode ) ) { return ''; }
    return add_query_arg( array(
        'action' => 'vava_digital_access_pdf',
        'product' => $uid,
        'mode' => $mode,
        '_wpnonce' => wp_create_nonce( 'vava_digital_access_' . $uid . '_' . $mode . '_' . $user->ID ),
    ), admin_url( 'admin-post.php' ) );
}
