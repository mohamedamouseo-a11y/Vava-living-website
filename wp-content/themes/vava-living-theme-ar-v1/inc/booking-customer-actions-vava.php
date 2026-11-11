<?php
/**
 * VAVA customer cancellation/rescheduling requests, approvals and queued notices.
 * No customer action deletes a booking or changes a paid appointment without review.
 */
defined( 'ABSPATH' ) || exit;

function vava_customer_action_is_future( int $booking_id ): bool {
    if ( 'vava_booking' !== get_post_type( $booking_id ) ) { return false; }
    $date = (string) get_post_meta( $booking_id, '_vava_booking_date', true );
    $time = (string) get_post_meta( $booking_id, '_vava_booking_time', true );
    try {
        $tz = wp_timezone();
        $d = new DateTimeImmutable( $date . ' ' . $time, $tz );
        return $d > new DateTimeImmutable( 'now', $tz );
    } catch ( Exception $e ) { return false; }
}
function vava_customer_reschedule_mode( int $booking_id, bool $ignore_pending = false ): bool {
    if ( function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( $booking_id ) ) { return false; }
    $status = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
    if ( ! in_array( $status, array( 'confirmed', 'paid', 'pending', 'pending_bank_review' ), true ) ) { return false; }
    // Past, still-confirmed sessions may request an exception for VAVA review.
    // This never reschedules an appointment without administrator approval.
    if ( ! vava_customer_action_is_future( $booking_id ) && ! in_array( $status, array( 'confirmed', 'paid' ), true ) ) { return false; }
    $parent = absint( get_post_meta( $booking_id, '_vava_package_parent_booking_id', true ) );
    if ( $parent && in_array( (string) get_post_meta( $parent, '_vava_booking_status', true ),
        array( 'cancellation_requested', 'cancelled', 'customer_cancelled' ), true ) ) { return false; }
    $request = (array) get_post_meta( $booking_id, '_vava_booking_reschedule_request', true );
    return $ignore_pending || 'pending' !== (string) ( $request['status'] ?? '' );
}
function vava_customer_reschedule_slots( int $booking_id, string $date, bool $ignore_pending = false ): array {
    if ( ! vava_customer_reschedule_mode( $booking_id, $ignore_pending ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) { return array(); }
    $shared = vava_booking_shared_data( vava_booking_page_id() );
    if ( function_exists( 'vava_package_followup_is_child' ) && vava_package_followup_is_child( $booking_id ) ) {
        $parent = absint( get_post_meta( $booking_id, '_vava_package_parent_booking_id', true ) );
        $service = vava_package_followup_service( $parent );
    } else {
        $uid = sanitize_key( (string) get_post_meta( $booking_id, '_vava_booking_service_uid', true ) );
        $service = $uid ? vava_booking_resolve_service( $uid, 'ar' ) : null;
    }
    if ( ! is_array( $service ) || ! $service ) { return array(); }
    $slots = vava_booking_available_slots( $service, $date, $shared );
    if ( ! $slots ) { return array(); }
    try {
        $tz = new DateTimeZone( (string) ( $shared['timezone'] ?: wp_timezone_string() ) );
        $earliest = ( new DateTimeImmutable( 'now', $tz ) )->modify( '+' . absint( $shared['min_notice_hours'] ?? 0 ) . ' hours' );
        $parent = absint( get_post_meta( $booking_id, '_vava_package_parent_booking_id', true ) );
        $first_end = null;
        if ( $parent ) {
            $first_end = ( new DateTimeImmutable( get_post_meta( $parent, '_vava_booking_date', true ) . ' ' . get_post_meta( $parent, '_vava_booking_time', true ), $tz ) )
                ->modify( '+' . max( 10, absint( get_post_meta( $parent, '_vava_booking_duration', true ) ) ) . ' minutes' );
        }
        return array_values( array_filter( $slots, static function ( array $slot ) use ( $date, $tz, $earliest, $first_end ): bool {
            $candidate = new DateTimeImmutable( $date . ' ' . (string) ( $slot['value'] ?? '' ), $tz );
            return $candidate > $earliest && ( ! $first_end || $candidate >= $first_end );
        } ) );
    } catch ( Exception $e ) { return array(); }
}
function vava_customer_action_notice_queue( int $booking_id, string $event ): void {
    if ( ! in_array( $event, array( 'cancel_request', 'cancelled', 'cancel_rejected', 'reschedule_request', 'reschedule_approved', 'reschedule_rejected' ), true ) ) { return; }
    foreach ( array( 'customer', 'admin' ) as $target ) {
        if ( get_post_meta( $booking_id, '_vava_action_notice_' . $event . '_' . $target . '_sent_at', true ) ) { continue; }
        $args = array( $booking_id, $event, $target );
        if ( ! wp_next_scheduled( 'vava_customer_action_notice_send', $args ) ) {
            wp_schedule_single_event( time() + ( 'customer' === $target ? 5 : 10 ),
                'vava_customer_action_notice_send', $args );
        }
    }
}
function vava_customer_action_notice_send( int $booking_id, string $event, string $target ): void {
    if ( 'vava_booking' !== get_post_type( $booking_id ) || ! in_array( $target, array( 'customer', 'admin' ), true ) ) { return; }
    // A queued email may run after the administrator has already resolved the request.
    // Never send a stale "pending" message after the decision is finalized.
    $booking_status = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
    $reschedule_request = (array) get_post_meta( $booking_id, '_vava_booking_reschedule_request', true );
    $required_status = array(
        'cancel_request' => 'cancellation_requested',
        'cancelled' => 'cancelled',
        'cancel_rejected' => 'rejected',
        'reschedule_request' => 'pending',
        'reschedule_approved' => 'approved',
        'reschedule_rejected' => 'rejected',
    );
    if ( 'cancel_request' === $event && 'cancellation_requested' !== $booking_status ) { return; }
    if ( 'cancelled' === $event && ! in_array( $booking_status, array( 'cancelled', 'customer_cancelled' ), true ) ) { return; }
    if ( 'cancel_rejected' === $event && 'rejected' !== (string) get_post_meta( $booking_id, '_vava_booking_cancellation_request_status', true ) ) { return; }
    if ( in_array( $event, array( 'reschedule_request', 'reschedule_approved', 'reschedule_rejected' ), true )
        && (string) ( $reschedule_request['status'] ?? '' ) !== (string) ( $required_status[ $event ] ?? '' ) ) { return; }
    $key = '_vava_action_notice_' . sanitize_key( $event ) . '_' . $target;
    if ( get_post_meta( $booking_id, $key . '_sent_at', true ) ) { return; }
    if ( function_exists( 'vava_mail_notifications_enabled' ) && ! vava_mail_notifications_enabled( 'bookings' ) ) {
        update_post_meta( $booking_id, $key . '_state', 'disabled' );
        return;
    }
    $email = 'admin' === $target
        ? sanitize_email( (string) get_option( 'admin_email' ) )
        : ( function_exists( 'vava_booking_customer_email' ) ? vava_booking_customer_email( $booking_id ) : '' );
    if ( ! is_email( $email ) ) { update_post_meta( $booking_id, $key . '_state', 'missing_email' ); return; }
    if ( 'admin' === $target && function_exists( 'vava_booking_customer_email' ) &&
         strtolower( $email ) === strtolower( vava_booking_customer_email( $booking_id ) ) ) { return; }

    $request = (array) get_post_meta( $booking_id, '_vava_booking_reschedule_request', true );
    $name = sanitize_text_field( (string) ( (array) get_post_meta( $booking_id, '_vava_booking_customer', true ) )['name'] ?? '' );
    $status = array(
        'cancel_request' => array( 'تم استلام طلب إلغاء الحجز للمراجعة', 'Cancellation request received for review' ),
        'cancelled' => array( 'تم إلغاء الحجز', 'Your booking has been cancelled' ),
        'cancel_rejected' => array( 'لم تتم الموافقة على طلب الإلغاء، والحجز ما زال قائمًا', 'Cancellation request declined; your booking remains active' ),
        'reschedule_request' => array( 'تم استلام طلب تغيير الموعد للمراجعة', 'Rescheduling request received for review' ),
        'reschedule_approved' => array( 'تم اعتماد الموعد الجديد', 'Your new appointment has been approved' ),
        'reschedule_rejected' => array( 'لم تتم الموافقة على تغيير الموعد', 'Your rescheduling request was not approved' ),
    );
    $titles = $status[ $event ] ?? $status['cancel_request'];
    $date = (string) get_post_meta( $booking_id, '_vava_booking_date', true );
    $time = (string) get_post_meta( $booking_id, '_vava_booking_time', true );
    $requested = ! empty( $request['date'] ) && ! empty( $request['time'] )
        ? sanitize_text_field( $request['date'] . ' ' . $request['time'] ) : '';
    $details = 'الحجز / Booking: #' . $booking_id . "\n"
        . 'العميل / Customer: ' . $name . "\n"
        . 'الموعد الحالي / Current appointment: ' . $date . ' ' . $time . "\n"
        . ( $requested ? 'الموعد المطلوب / Requested appointment: ' . $requested . "\n" : '' )
        . ( in_array( $event, array( 'cancel_request', 'cancelled' ), true ) && get_post_meta( $booking_id, '_vava_booking_customer_cancel_reason', true )
            ? 'سبب الإلغاء / Cancellation reason: ' . sanitize_textarea_field( (string) get_post_meta( $booking_id, '_vava_booking_customer_cancel_reason', true ) ) . "\n" : '' );
    $body = "العربية\n" . $titles[0] . "\n" . $details
        . "\n-----------------------\nEnglish\n" . $titles[1] . "\n" . $details
        . "\n\n" . ( 'admin' === $target && function_exists( 'vava_booking_admin_details_url' )
            ? vava_booking_admin_details_url( $booking_id )
            : ( function_exists( 'vava_customer_account_url' ) ? vava_customer_account_url( 'ar', array( 'view' => 'bookings' ) ) : home_url( '/' ) ) );
    $sent = wp_mail( $email, 'VAVA #' . $booking_id . ' — ' . $titles[0] . ' / ' . $titles[1], $body );
    if ( $sent ) {
        update_post_meta( $booking_id, $key . '_sent_at', current_time( 'mysql', true ) );
        update_post_meta( $booking_id, $key . '_state', 'sent' );
        return;
    }
    $attempts = absint( get_post_meta( $booking_id, $key . '_attempts', true ) ) + 1;
    update_post_meta( $booking_id, $key . '_attempts', $attempts );
    update_post_meta( $booking_id, $key . '_state', $attempts < 4 ? 'retrying' : 'failed' );
    if ( $attempts < 4 ) {
        wp_schedule_single_event( time() + ( 60 * ( 2 ** ( $attempts - 1 ) ) ),
            'vava_customer_action_notice_send', array( $booking_id, $event, $target ) );
    }
}
add_action( 'vava_customer_action_notice_send', 'vava_customer_action_notice_send', 10, 3 );

function vava_customer_reschedule_request_handler(): void {
    $booking_id = isset( $_POST['booking'] ) ? absint( $_POST['booking'] ) : 0;
    $token = isset( $_POST['magic_token'] ) ? sanitize_text_field( wp_unslash( $_POST['magic_token'] ) ) : '';
    $lang = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
    if ( ! $booking_id || ! vava_customer_can_access_booking( $booking_id, $token ) ) {
        wp_die( 'Booking access denied.', '', array( 'response' => 403 ) );
    }
    check_admin_referer( 'vava_booking_reschedule_' . $booking_id );
    $date = isset( $_POST['reschedule_date'] ) ? sanitize_text_field( wp_unslash( $_POST['reschedule_date'] ) ) : '';
    $time = isset( $_POST['reschedule_time'] ) ? sanitize_text_field( wp_unslash( $_POST['reschedule_time'] ) ) : '';
    $result = 'invalid';
    if ( vava_customer_reschedule_mode( $booking_id ) &&
        in_array( $time, array_column( vava_customer_reschedule_slots( $booking_id, $date ), 'value' ), true ) ) {
        $old_date = (string) get_post_meta( $booking_id, '_vava_booking_date', true );
        $old_time = (string) get_post_meta( $booking_id, '_vava_booking_time', true );
        if ( $date !== $old_date || $time !== $old_time ) {
            foreach ( array( 'reschedule_request', 'reschedule_approved', 'reschedule_rejected' ) as $notice_event ) {
                foreach ( array( 'customer', 'admin' ) as $target ) {
                    delete_post_meta( $booking_id, '_vava_action_notice_' . $notice_event . '_' . $target . '_sent_at' );
                    delete_post_meta( $booking_id, '_vava_action_notice_' . $notice_event . '_' . $target . '_attempts' );
                }
            }
            update_post_meta( $booking_id, '_vava_booking_reschedule_request', array(
                'date' => $date, 'time' => $time, 'original_date' => $old_date, 'original_time' => $old_time,
                'status' => 'pending', 'requested_at' => current_time( 'mysql', true ),
                'requested_by' => get_current_user_id(),
                'previous_appointment_passed' => ! vava_customer_action_is_future( $booking_id ),
            ) );
            if ( function_exists( 'vava_booking_append_action_log' ) ) {
                vava_booking_append_action_log( $booking_id, 'reschedule_requested',
                    $old_date . ' ' . $old_time . ' → ' . $date . ' ' . $time,
                    (string) get_post_meta( $booking_id, '_vava_booking_status', true ),
                    (string) get_post_meta( $booking_id, '_vava_booking_status', true ),
                    vava_booking_effective_payment_status( $booking_id ), vava_booking_effective_payment_status( $booking_id ) );
            }
            vava_customer_action_notice_queue( $booking_id, 'reschedule_request' );
            $result = 'pending';
        }
    }
    $url = vava_customer_account_url( $lang, array( 'view' => 'bookings', 'booking' => $booking_id, 'reschedule_result' => $result ) );
    if ( $token ) { $url = add_query_arg( 'vava_magic', rawurlencode( $token ), $url ); }
    wp_safe_redirect( $url . '#vava-booking-' . $booking_id );
    exit;
}
add_action( 'admin_post_vava_booking_reschedule_request', 'vava_customer_reschedule_request_handler' );
add_action( 'admin_post_nopriv_vava_booking_reschedule_request', 'vava_customer_reschedule_request_handler' );

function vava_customer_reschedule_admin_handler(): void {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Not allowed', '', array( 'response' => 403 ) ); }
    $booking_id = isset( $_POST['booking'] ) ? absint( $_POST['booking'] ) : 0;
    check_admin_referer( 'vava_booking_reschedule_admin_' . $booking_id );
    $decision = isset( $_POST['decision'] ) ? sanitize_key( wp_unslash( $_POST['decision'] ) ) : '';
    $request = (array) get_post_meta( $booking_id, '_vava_booking_reschedule_request', true );
    $result = 'invalid';
    if ( 'pending' === (string) ( $request['status'] ?? '' ) && in_array( $decision, array( 'approve', 'reject' ), true ) ) {
        if ( 'approve' === $decision ) {
            $date = sanitize_text_field( (string) ( $request['date'] ?? '' ) );
            $time = sanitize_text_field( (string) ( $request['time'] ?? '' ) );
            $old_date = (string) get_post_meta( $booking_id, '_vava_booking_date', true );
            $old_time = (string) get_post_meta( $booking_id, '_vava_booking_time', true );
            if ( ! vava_customer_reschedule_mode( $booking_id, true ) ||
                ! in_array( $time, array_column( vava_customer_reschedule_slots( $booking_id, $date, true ), 'value' ), true ) ||
                $old_date !== (string) ( $request['original_date'] ?? '' ) ||
                $old_time !== (string) ( $request['original_time'] ?? '' ) ) {
                $result = 'slot_unavailable';
            } elseif ( get_post_meta( $booking_id, '_vava_booking_google_event_id', true ) ) {
                // An existing Google event must be synchronized before approving a new time.
                $result = 'calendar_sync_required';
            } else {
                update_post_meta( $booking_id, '_vava_booking_date', $date );
                update_post_meta( $booking_id, '_vava_booking_time', $time );
                $request['status'] = 'approved';
                $request['resolved_at'] = current_time( 'mysql', true );
                $request['resolved_by'] = get_current_user_id();
                update_post_meta( $booking_id, '_vava_booking_reschedule_request', $request );
                if ( function_exists( 'vava_booking_append_action_log' ) ) {
                    vava_booking_append_action_log( $booking_id, 'reschedule_approved',
                        $old_date . ' ' . $old_time . ' → ' . $date . ' ' . $time,
                        (string) get_post_meta( $booking_id, '_vava_booking_status', true ),
                        (string) get_post_meta( $booking_id, '_vava_booking_status', true ),
                        vava_booking_effective_payment_status( $booking_id ), vava_booking_effective_payment_status( $booking_id ) );
                }
                vava_customer_action_notice_queue( $booking_id, 'reschedule_approved' );
                $result = 'approved';
            }
        } else {
            $request['status'] = 'rejected';
            $request['resolved_at'] = current_time( 'mysql', true );
            $request['resolved_by'] = get_current_user_id();
            update_post_meta( $booking_id, '_vava_booking_reschedule_request', $request );
            vava_customer_action_notice_queue( $booking_id, 'reschedule_rejected' );
            $result = 'rejected';
        }
    }
    $url = function_exists( 'vava_booking_admin_details_url' )
        ? vava_booking_admin_details_url( $booking_id ) : admin_url( 'post.php?post=' . $booking_id . '&action=edit' );
    wp_safe_redirect( add_query_arg( 'reschedule_result', $result, $url ) );
    exit;
}
add_action( 'admin_post_vava_booking_reschedule_admin', 'vava_customer_reschedule_admin_handler' );

function vava_customer_reschedule_admin_card( int $booking_id ): void {
    $request = (array) get_post_meta( $booking_id, '_vava_booking_reschedule_request', true );
    if ( 'pending' !== (string) ( $request['status'] ?? '' ) || ! current_user_can( 'manage_options' ) ) { return; }
    echo '<section class="vava-booking-detail-card" style="border:2px solid #9ca982;padding:18px;margin:16px 0">';
    $result = isset( $_GET['reschedule_result'] ) ? sanitize_key( wp_unslash( $_GET['reschedule_result'] ) ) : '';
    if ( 'calendar_sync_required' === $result ) {
        echo '<p role="alert" style="color:#a14839">الحدث مرتبط بالفعل بتقويم Google. حدّث موعد الحدث على التقويم أولًا قبل اعتماد تغيير موعد الحجز.</p>';
    } elseif ( 'slot_unavailable' === $result ) {
        echo '<p role="alert" style="color:#a14839">الموعد المطلوب لم يعد متاحًا؛ يجب مراجعة طلب العميل واختيار موعد آخر.</p>';
    }
    echo '<h3>طلب تغيير موعد من العميل — بانتظار المراجعة</h3><p><strong>الموعد المطلوب:</strong> '
        . esc_html( (string) ( $request['date'] ?? '' ) . ' ' . (string) ( $request['time'] ?? '' ) ) . '</p>';
    if ( ! empty( $request['previous_appointment_passed'] ) ) {
        echo '<p role="alert" style="background:#fcf2e5;border:1px solid #ebcf9e;padding:12px;border-radius:9px">طلب استثنائي: الموعد الأصلي فات. راجعي سجل الجلسة والتواصل مع العميل قبل الموافقة على تغيير الموعد.</p>';
    }
    foreach ( array( 'approve' => 'اعتماد الموعد الجديد', 'reject' => 'رفض التغيير' ) as $decision => $label ) {
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin:6px">';
        echo '<input type="hidden" name="action" value="vava_booking_reschedule_admin"><input type="hidden" name="decision" value="' . esc_attr( $decision ) . '">';
        echo '<input type="hidden" name="booking" value="' . esc_attr( (string) $booking_id ) . '">';
        wp_nonce_field( 'vava_booking_reschedule_admin_' . $booking_id );
        echo '<button class="button button-primary" type="submit">' . esc_html( $label ) . '</button></form>';
    }
    echo '<p style="font-size:12px">يُراجع توفر الموعد مجددًا عند الاعتماد. الاجتماعات الموجودة في Google Calendar تحتاج مزامنة قبل تغيير الموعد.</p>';
    echo '</section>';
}

function vava_customer_reschedule_account_card( int $booking_id, string $lang, string $magic_token = '' ): void {
    if ( ! function_exists( 'vava_customer_can_access_booking' ) || ! vava_customer_can_access_booking( $booking_id, $magic_token ) ) { return; }
    $is_en = 'en' === $lang;
    $request = (array) get_post_meta( $booking_id, '_vava_booking_reschedule_request', true );
    $current_status = (string) ( $request['status'] ?? '' );
    if ( 'pending' === $current_status ) {
        echo '<p role="status" class="vava-account-reschedule-status" style="padding:12px;background:#f4f1e8;border-radius:10px">'
            . esc_html( $is_en ? 'Rescheduling requested: awaiting VAVA approval. Your current appointment stays confirmed until approved.' : 'طلب تغيير الموعد قيد مراجعة الإدارة. موعدك الحالي يظل مؤكدًا لحين الموافقة.' )
            . '</p>';
        return;
    }
    if ( 'approved' === $current_status || 'rejected' === $current_status ) {
        $approved = 'approved' === $current_status;
        echo '<p class="vava-account-reschedule-status" role="status" style="padding:10px 12px;border:1px solid #d6d9c7;border-radius:10px">'
            . esc_html( $approved
                ? ( $is_en ? 'Your previous appointment change was approved. Your updated appointment is shown above.' : 'تم اعتماد طلب تغيير الموعد السابق، والموعد المحدّث ظاهر في تفاصيل الحجز.' )
                : ( $is_en ? 'Your previous date change request was declined. You can submit a new request for review.' : 'تم رفض طلب تعديل الموعد السابق، ويمكنك تقديم طلب جديد للمراجعة.' ) )
            . '</p>';
    }
    if ( ! vava_customer_reschedule_mode( $booking_id ) ) { return; }
    $is_past_booking = ! vava_customer_action_is_future( $booking_id );
    $selected_id = isset( $_GET['reschedule_booking'] ) ? absint( $_GET['reschedule_booking'] ) : 0;
    $date = $selected_id === $booking_id && isset( $_GET['reschedule_date'] )
        ? sanitize_text_field( wp_unslash( $_GET['reschedule_date'] ) ) : '';
    $shared = vava_booking_shared_data( vava_booking_page_id() );
    $max = wp_date( 'Y-m-d', strtotime( '+' . max( 1, absint( $shared['max_days'] ?? 60 ) ) . ' days' ) );
    $base = vava_customer_account_url( $lang );
    echo '<details id="vava-reschedule-' . esc_attr( (string) $booking_id ) . '" class="vava-account-reschedule" style="margin:10px 0;padding:12px;border:1px solid #d5dcc9;border-radius:12px"'
        . ( $selected_id === $booking_id ? ' open' : '' ) . '><summary style="cursor:pointer;font-weight:600">'
        . esc_html( $is_en ? 'Request a date/time change' : 'طلب تعديل تاريخ ووقت الجلسة' ) . '</summary>';
    echo '<p style="font-size:12px">' . esc_html( $is_past_booking
        ? ( $is_en ? 'This appointment date has passed. You can request an exceptional reschedule, but VAVA must review whether the session was provided. Your existing booking will not change without approval.' : 'موعد هذه الجلسة فات بالفعل. يمكنك طلب تعديل استثنائي، لكن الإدارة ستراجع أولًا هل تم تقديم الجلسة أم لا. لن يتغير الحجز قبل الموافقة.' )
        : ( $is_en ? 'Choose an available time. VAVA will review the request before your appointment changes.' : 'اختاري موعدًا متاحًا. لن يتغير حجزك إلا بعد موافقة إدارة VAVA.' ) ) . '</p>';
    echo '<form method="get" action="' . esc_url( $base ) . '">';
    echo '<input type="hidden" name="page_id" value="' . esc_attr( (string) vava_booking_my_bookings_page_id() ) . '">';
    echo '<input type="hidden" name="vava_lang" value="' . esc_attr( $lang ) . '">';
    echo '<input type="hidden" name="view" value="bookings"><input type="hidden" name="reschedule_booking" value="' . esc_attr( (string) $booking_id ) . '">';
    if ( $magic_token ) { echo '<input type="hidden" name="vava_magic" value="' . esc_attr( $magic_token ) . '">'; }
    echo '<label>' . esc_html( $is_en ? 'New date' : 'التاريخ الجديد' )
        . ' <input required type="date" name="reschedule_date" min="' . esc_attr( wp_date( 'Y-m-d' ) )
        . '" max="' . esc_attr( $max ) . '" value="' . esc_attr( $date ) . '"></label> ';
    echo '<button type="submit">' . esc_html( $is_en ? 'Show times' : 'عرض المواعيد' ) . '</button></form>';
    if ( $date ) {
        $slots = vava_customer_reschedule_slots( $booking_id, $date );
        if ( ! $slots ) {
            echo '<p>' . esc_html( $is_en ? 'No eligible times on this date; choose another date.' : 'لا توجد مواعيد متاحة في هذا اليوم؛ اختاري يومًا آخر.' ) . '</p>';
        } else {
            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:10px">';
            echo '<input type="hidden" name="action" value="vava_booking_reschedule_request">';
            echo '<input type="hidden" name="booking" value="' . esc_attr( (string) $booking_id ) . '">';
            echo '<input type="hidden" name="reschedule_date" value="' . esc_attr( $date ) . '">';
            echo '<input type="hidden" name="lang" value="' . esc_attr( $lang ) . '">';
            if ( $magic_token ) { echo '<input type="hidden" name="magic_token" value="' . esc_attr( $magic_token ) . '">'; }
            wp_nonce_field( 'vava_booking_reschedule_' . $booking_id );
            echo '<label>' . esc_html( $is_en ? 'Available time' : 'الوقت المتاح' ) . ' <select required name="reschedule_time">';
            foreach ( $slots as $slot ) { echo '<option value="' . esc_attr( $slot['value'] ) . '">' . esc_html( $slot['label'] ) . '</option>'; }
            echo '</select></label> <button type="submit">' . esc_html( $is_en ? 'Send for approval' : 'إرسال طلب التعديل للإدارة' ) . '</button></form>';
        }
    }
    echo '</details>';
}
