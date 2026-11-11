<?php
/**
 * VAVA customer support tickets.
 * Persist first, queue notifications after commit, and preserve independent
 * customer/admin authentication. Does not rely on the public contact form.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'init', static function (): void {
    register_post_type( 'vava_support_ticket', array(
        'label' => 'VAVA Support Tickets',
        'public' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'show_ui' => false,
        'show_in_rest' => false,
        'rewrite' => false,
        'supports' => array( 'title', 'editor', 'author' ),
    ) );
}, 8 );

function vava_support_customer_tickets( int $user_id, int $limit = 20 ): array {
    if ( $user_id < 1 ) { return array(); }
    return get_posts( array(
        'post_type' => 'vava_support_ticket',
        'post_status' => 'private',
        'author' => $user_id,
        'posts_per_page' => max( 1, min( 50, $limit ) ),
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => true,
        'suppress_filters' => false,
    ) );
}

function vava_support_queue_notification( int $ticket_id, string $target ): bool {
    if ( 'vava_support_ticket' !== get_post_type( $ticket_id ) || ! in_array( $target, array( 'admin', 'customer' ), true ) ) { return false; }
    if ( get_post_meta( $ticket_id, '_vava_support_notice_' . $target . '_sent_at', true ) ) { return true; }
    $args = array( $ticket_id, $target );
    if ( wp_next_scheduled( 'vava_support_notification_event', $args ) ) { return true; }
    $result = wp_schedule_single_event( time() + ( 'admin' === $target ? 4 : 9 ),
        'vava_support_notification_event', $args, true );
    $queued = ! is_wp_error( $result ) && $result;
    update_post_meta( $ticket_id, '_vava_support_notice_' . $target . '_state', $queued ? 'queued' : 'queue_failed' );
    return $queued;
}

function vava_support_notification_handler( int $ticket_id, string $target ): void {
    if ( 'vava_support_ticket' !== get_post_type( $ticket_id ) || ! in_array( $target, array( 'admin', 'customer' ), true ) ) { return; }
    $base = '_vava_support_notice_' . $target;
    if ( get_post_meta( $ticket_id, $base . '_sent_at', true ) ) { return; }
    $ticket = get_post( $ticket_id );
    if ( ! $ticket ) { return; }
    $owner = get_userdata( (int) $ticket->post_author );
    if ( ! $owner instanceof WP_User || ! vava_customer_is_customer( $owner ) ) { return; }
    if ( function_exists( 'vava_mail_notifications_enabled' ) && ! vava_mail_notifications_enabled( 'contact' ) ) {
        update_post_meta( $ticket_id, $base . '_state', 'disabled' );
        return;
    }
    $to = 'admin' === $target
        ? ( function_exists( 'vava_mail_admin_recipient' ) ? vava_mail_admin_recipient() : sanitize_email( (string) get_option( 'admin_email' ) ) )
        : sanitize_email( $owner->user_email );
    if ( ! is_email( $to ) ) { update_post_meta( $ticket_id, $base . '_state', 'missing_email' ); return; }
    $subject = 'admin' === $target
        ? 'VAVA — رسالة دعم جديدة / New support ticket #' . $ticket_id
        : 'VAVA — تم استلام طلب الدعم / Support request received #' . $ticket_id;
    $customer_name = sanitize_text_field( $owner->display_name ?: $owner->user_login );
    $support_url = 'admin' === $target
        ? admin_url( 'admin.php?page=vava-support-messages&ticket=' . $ticket_id )
        : vava_customer_account_url( 'ar', array( 'view' => 'support' ) );
    $body = "VAVA Living\n"
        . ( 'admin' === $target
            ? "رسالة جديدة من حساب عميل / A new customer support ticket\n"
            : "تم حفظ رسالتك وسيقوم فريق VAVA بمراجعتها. / Your message is saved and will be reviewed.\n" )
        . "رقم التذكرة / Ticket: #" . $ticket_id . "\n"
        . "الاسم / Name: " . $customer_name . "\n"
        . ( 'admin' === $target ? "البريد / Email: " . sanitize_email( $owner->user_email ) . "\n" : '' )
        . "الموضوع / Subject: " . sanitize_text_field( $ticket->post_title ) . "\n\n"
        . ( 'admin' === $target ? sanitize_textarea_field( $ticket->post_content ) . "\n\n" : '' )
        . $support_url;
    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
    if ( 'admin' === $target && is_email( $owner->user_email ) ) { $headers[] = 'Reply-To: ' . sanitize_email( $owner->user_email ); }
    $ok = wp_mail( $to, $subject, $body, $headers );
    if ( $ok ) {
        update_post_meta( $ticket_id, $base . '_sent_at', current_time( 'mysql', true ) );
        update_post_meta( $ticket_id, $base . '_state', 'sent' );
        return;
    }
    $attempts = absint( get_post_meta( $ticket_id, $base . '_attempts', true ) ) + 1;
    update_post_meta( $ticket_id, $base . '_attempts', $attempts );
    update_post_meta( $ticket_id, $base . '_state', $attempts >= 4 ? 'failed' : 'retrying' );
    if ( $attempts < 4 ) {
        $args = array( $ticket_id, $target );
        if ( ! wp_next_scheduled( 'vava_support_notification_event', $args ) ) {
            wp_schedule_single_event( time() + min( 1800, 60 * ( 2 ** ( $attempts - 1 ) ) ), 'vava_support_notification_event', $args );
        }
    }
}
add_action( 'vava_support_notification_event', 'vava_support_notification_handler', 10, 2 );

function vava_support_submission_handler(): void {
    $lang = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
    $account_url = vava_customer_account_url( $lang, array( 'view' => 'support' ) );
    $redirect = static function ( string $status, int $ticket_id = 0 ) use ( $account_url ): void {
        $params = array( 'support_result' => $status );
        if ( $ticket_id ) { $params['support_ticket'] = $ticket_id; }
        wp_safe_redirect( add_query_arg( $params, $account_url ) . '#vava-account-support' );
        exit;
    };
    $user = function_exists( 'vava_customer_current_verified_user' ) ? vava_customer_current_verified_user() : null;
    if ( ! $user instanceof WP_User ) { $redirect( 'login' ); }
    if ( ! isset( $_POST['_wpnonce'] ) ||
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'vava_customer_support_submit' ) ) { $redirect( 'security' ); }
    if ( ! empty( $_POST['website'] ) ) { $redirect( 'invalid' ); }
    $subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
    $subject = trim( function_exists( 'mb_substr' ) ? mb_substr( $subject, 0, 160 ) : substr( $subject, 0, 160 ) );
    $message = trim( function_exists( 'mb_substr' ) ? mb_substr( $message, 0, 4000 ) : substr( $message, 0, 4000 ) );
    if ( strlen( $subject ) < 3 || strlen( $message ) < 10 ) { $redirect( 'invalid' ); }
    $fingerprint = hash_hmac( 'sha256', strtolower( $subject ) . "\n" . $message, wp_salt( 'secure_auth' ) );
    $duplicate = 'vava_support_duplicate_' . $user->ID . '_' . substr( $fingerprint, 0, 24 );
    $prior = absint( get_transient( $duplicate ) );
    if ( $prior && 'vava_support_ticket' === get_post_type( $prior ) &&
         (int) get_post_field( 'post_author', $prior ) === $user->ID ) { $redirect( 'saved', $prior ); }
    $rate = 'vava_support_rate_' . $user->ID;
    if ( get_transient( $rate ) ) { $redirect( 'rate_limited' ); }
    $ticket_id = wp_insert_post( array(
        'post_type' => 'vava_support_ticket', 'post_status' => 'private',
        'post_title' => $subject, 'post_content' => $message, 'post_author' => $user->ID,
    ), true );
    if ( is_wp_error( $ticket_id ) || ! $ticket_id ) { $redirect( 'error' ); }
    update_post_meta( $ticket_id, '_vava_support_status', 'new' );
    update_post_meta( $ticket_id, '_vava_support_created_at', current_time( 'mysql', true ) );
    update_post_meta( $ticket_id, '_vava_support_language', $lang );
    set_transient( $duplicate, (int) $ticket_id, 10 * MINUTE_IN_SECONDS );
    set_transient( $rate, 1, 15 );
    vava_support_queue_notification( (int) $ticket_id, 'admin' );
    vava_support_queue_notification( (int) $ticket_id, 'customer' );
    $redirect( 'saved', (int) $ticket_id );
}
add_action( 'admin_post_vava_customer_support_submit', 'vava_support_submission_handler' );
add_action( 'admin_post_nopriv_vava_customer_support_submit', 'vava_support_submission_handler' );

function vava_support_admin_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You do not have permission.' ), '', array( 'response' => 403 ) ); }
    $selected = isset( $_GET['ticket'] ) ? absint( $_GET['ticket'] ) : 0;
    echo '<div class="wrap" dir="rtl"><h1>رسائل دعم العملاء — VAVA</h1>';
    if ( isset( $_GET['updated'] ) ) { echo '<div class="notice notice-success"><p>تم تحديث حالة التذكرة.</p></div>'; }
    if ( $selected && 'vava_support_ticket' === get_post_type( $selected ) ) {
        $ticket = get_post( $selected );
        $owner = get_userdata( (int) $ticket->post_author );
        $current = (string) get_post_meta( $selected, '_vava_support_status', true );
        echo '<div class="postbox" style="padding:20px;max-width:860px"><h2>التذكرة #' . esc_html( (string) $selected ) . ' — ' . esc_html( $ticket->post_title ) . '</h2>';
        echo '<p><strong>العميل:</strong> ' . esc_html( $owner ? $owner->display_name : '' ) . ' | ' . esc_html( $owner ? $owner->user_email : '' ) . '</p>';
        echo '<p><strong>الحالة:</strong> ' . esc_html( $current ) . '</p>';
        echo '<div style="white-space:pre-wrap;line-height:1.9;border:1px solid #ddd;padding:16px;background:#fff">' . esc_html( $ticket->post_content ) . '</div>';
        foreach ( array( 'in_progress' => 'قيد المعالجة', 'resolved' => 'تم الحل', 'new' => 'إعادة فتح' ) as $state => $label ) {
            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin:12px 0 4px 10px">';
            echo '<input type="hidden" name="action" value="vava_support_update_status"><input type="hidden" name="ticket" value="' . esc_attr( (string) $selected ) . '">';
            echo '<input type="hidden" name="status" value="' . esc_attr( $state ) . '">';
            wp_nonce_field( 'vava_support_update_status_' . $selected );
            echo '<button type="submit" class="button button-secondary">' . esc_html( $label ) . '</button></form>';
        }
        echo '</div>';
    }
    $tickets = get_posts( array( 'post_type' => 'vava_support_ticket', 'post_status' => 'private', 'posts_per_page' => 100,
        'orderby' => 'date', 'order' => 'DESC' ) );
    echo '<table class="widefat striped" style="max-width:1100px"><thead><tr><th>رقم</th><th>الموضوع</th><th>العميل</th><th>التاريخ</th><th>الحالة</th><th>البريد للإدارة</th></tr></thead><tbody>';
    foreach ( $tickets as $ticket ) {
        $owner = get_userdata( (int) $ticket->post_author );
        $url = admin_url( 'admin.php?page=vava-support-messages&ticket=' . (int) $ticket->ID );
        echo '<tr><td>#' . esc_html( (string) $ticket->ID ) . '</td><td><a href="' . esc_url( $url ) . '">' . esc_html( $ticket->post_title ) . '</a></td>';
        echo '<td>' . esc_html( $owner ? $owner->display_name : '' ) . '</td><td>' . esc_html( (string) $ticket->post_date ) . '</td>';
        echo '<td>' . esc_html( (string) get_post_meta( $ticket->ID, '_vava_support_status', true ) ) . '</td>';
        echo '<td>' . esc_html( (string) get_post_meta( $ticket->ID, '_vava_support_notice_admin_state', true ) ) . '</td></tr>';
    }
    if ( ! $tickets ) { echo '<tr><td colspan="6">لا توجد رسائل دعم حتى الآن.</td></tr>'; }
    echo '</tbody></table></div>';
}
add_action( 'admin_menu', static function (): void {
    add_menu_page( 'رسائل دعم العملاء', 'رسائل دعم VAVA', 'manage_options',
        'vava-support-messages', 'vava_support_admin_page', 'dashicons-sos', 56 );
}, 25 );

add_action( 'admin_post_vava_support_update_status', static function (): void {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Not allowed', '', array( 'response' => 403 ) ); }
    $id = isset( $_POST['ticket'] ) ? absint( $_POST['ticket'] ) : 0;
    check_admin_referer( 'vava_support_update_status_' . $id );
    $status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';
    if ( 'vava_support_ticket' !== get_post_type( $id ) ||
        ! in_array( $status, array( 'new', 'in_progress', 'resolved' ), true ) ) { wp_die( 'Invalid ticket', '', array( 'response' => 400 ) ); }
    update_post_meta( $id, '_vava_support_status', $status );
    update_post_meta( $id, '_vava_support_updated_at', current_time( 'mysql', true ) );
    wp_safe_redirect( admin_url( 'admin.php?page=vava-support-messages&ticket=' . $id . '&updated=1' ) );
    exit;
} );
