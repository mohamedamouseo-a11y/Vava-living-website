<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
define( 'VAVA_GOOGLE_MEET_SETTINGS_OPTION', '_vava_google_meet_settings' );
define( 'VAVA_GOOGLE_MEET_TOKENS_OPTION', '_vava_google_meet_tokens' );
function vava_google_meet_capability(): string {
    if ( function_exists( 'vava_booking_admin_capability' ) ) { return vava_booking_admin_capability(); }
    return 'manage_options';
}
function vava_google_meet_get_settings(): array {
    $defaults = array( 'enabled' => 0, 'client_id' => '', 'client_secret' => '', 'calendar_id' => 'primary', 'automation_enabled' => 1, 'reminder_enabled' => 1, 'reminder_hours' => 24 );
    $saved = get_option( VAVA_GOOGLE_MEET_SETTINGS_OPTION, array() );
    if ( ! is_array( $saved ) ) { $saved = array(); }
    return array_merge( $defaults, $saved );
}
function vava_google_meet_get_tokens(): array {
    $tokens = get_option( VAVA_GOOGLE_MEET_TOKENS_OPTION, array() );
    if ( ! is_array( $tokens ) ) { return array(); }
    return $tokens;
}
function vava_google_meet_is_connected(): bool {
    $tokens = vava_google_meet_get_tokens();
    return ! empty( $tokens['refresh_token'] );
}
function vava_google_meet_refresh_access_token(): string {
    $tokens = vava_google_meet_get_tokens();
    $settings = vava_google_meet_get_settings();
    if ( empty( $tokens['refresh_token'] ) || empty( $settings['client_id'] ) || empty( $settings['client_secret'] ) ) { return ''; }
    if ( ! empty( $tokens['access_token'] ) && ! empty( $tokens['expires_at'] ) && time() < ( (int) $tokens['expires_at'] - 60 ) ) {
        return (string) $tokens['access_token'];
    }
    $resp = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
        'body' => array( 'client_id' => $settings['client_id'], 'client_secret' => $settings['client_secret'], 'refresh_token' => $tokens['refresh_token'], 'grant_type' => 'refresh_token' ),
        'timeout' => 30,
    ) );
    if ( is_wp_error( $resp ) ) { return ''; }
    $body = json_decode( wp_remote_retrieve_body( $resp ), true );
    if ( ! is_array( $body ) || empty( $body['access_token'] ) ) { return ''; }
    $tokens['access_token'] = $body['access_token'];
    $tokens['expires_at'] = time() + (int) ( $body['expires_in'] ?? 3600 );
    if ( ! empty( $body['refresh_token'] ) ) { $tokens['refresh_token'] = $body['refresh_token']; }
    update_option( VAVA_GOOGLE_MEET_TOKENS_OPTION, $tokens, false );
    return (string) $body['access_token'];
}

function vava_google_meet_register_menu(): void {
add_submenu_page( 'edit.php?post_type=vava_booking', 'إعدادات Google Meet', 'Google Meet', vava_google_meet_capability(), 'vava-google-meet-settings', 'vava_google_meet_render_settings_page' );
}
add_action( 'admin_menu', 'vava_google_meet_register_menu' );

add_filter( 'parent_file', function( $parent_file ) {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( $screen && isset( $_GET['page'] ) && 'vava-google-meet-settings' === $_GET['page'] ) {
        return 'edit.php?post_type=vava_booking';
    }
    return $parent_file;
} );
add_filter( 'submenu_file', function( $submenu_file ) {
    if ( isset( $_GET['page'] ) && 'vava-google-meet-settings' === $_GET['page'] ) {
        return 'vava-google-meet-settings';
    }
    return $submenu_file;
} );
function vava_google_meet_get_auth_url(): string {
$settings = vava_google_meet_get_settings();
if ( empty( $settings['client_id'] ) ) { return '#'; }
$state = wp_create_nonce( 'vava_gm_oauth_state_' . get_current_user_id() );
return add_query_arg( array(
'client_id' => $settings['client_id'], 'redirect_uri' => admin_url( 'admin-post.php?action=vava_google_meet_oauth_callback' ),
'response_type' => 'code', 'scope' => 'https://www.googleapis.com/auth/calendar.events',
'access_type' => 'offline', 'prompt' => 'consent', 'include_granted_scopes' => 'true', 'state' => $state,
), 'https://accounts.google.com/o/oauth2/v2/auth' );
}
function vava_google_meet_render_settings_page(): void {
if ( ! current_user_can( vava_google_meet_capability() ) ) { wp_die( 'Unauthorized.' ); }
$settings = vava_google_meet_get_settings();
$connected = vava_google_meet_is_connected();
$token_check = $connected ? vava_google_meet_refresh_access_token() : '';
$needs_reconnect = $connected && ! $token_check;
$notice = isset( $_GET['vava_gm_notice'] ) ? sanitize_key( $_GET['vava_gm_notice'] ) : '';
$redirect_uri = admin_url( 'admin-post.php?action=vava_google_meet_oauth_callback' );
?>
<div class="wrap" dir="rtl">
<h1>إعدادات Google Meet</h1>
<?php if ( 'saved' === $notice ) : ?><div class="notice notice-success"><p>تم حفظ الإعدادات.</p></div><?php endif; ?>
<?php if ( 'connected' === $notice ) : ?><div class="notice notice-success"><p>تم ربط حساب Google بنجاح.</p></div><?php endif; ?>
<?php if ( 'disconnected' === $notice ) : ?><div class="notice notice-info"><p>تم فصل حساب Google.</p></div><?php endif; ?>
<?php if ( 'error' === $notice ) : ?><div class="notice notice-error"><p>حدث خطأ أثناء العملية.</p></div><?php endif; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width:720px;">
<?php wp_nonce_field( 'vava_save_google_meet_settings', '_vava_gm_nonce' ); ?>
<input type="hidden" name="action" value="vava_save_google_meet_settings">
<table class="form-table">
<tr><th scope="row">تفعيل التكامل</th><td><label><input type="checkbox" name="vava_gm_enabled" value="1" <?php checked( $settings['enabled'], 1 ); ?>> تفعيل Google Meet</label></td></tr>
<tr><th scope="row">Client ID</th><td><input type="text" name="vava_gm_client_id" value="<?php echo esc_attr( $settings['client_id'] ); ?>" class="regular-text" dir="ltr"></td></tr>
<tr><th scope="row">Client Secret</th><td><input type="password" name="vava_gm_client_secret" value="" class="regular-text" dir="ltr" autocomplete="new-password"><p class="description">اتركه فارغًا للاحتفاظ بالقيمة المحفوظة.</p></td></tr>
<tr><th scope="row">Calendar ID</th><td><input type="text" name="vava_gm_calendar_id" value="<?php echo esc_attr( $settings['calendar_id'] ); ?>" class="regular-text" dir="ltr" placeholder="primary"></td></tr>
<tr><th scope="row">التوليد التلقائي</th><td><label><input type="checkbox" name="vava_gm_automation_enabled" value="1" <?php checked( $settings['automation_enabled'], 1 ); ?>> توليد رابط الاجتماع تلقائيًا عند تأكيد الحجز</label></td></tr>
<tr><th scope="row">التذكير</th><td><label><input type="checkbox" name="vava_gm_reminder_enabled" value="1" <?php checked( $settings['reminder_enabled'], 1 ); ?>> إرسال تذكير قبل الجلسة</label><br><input type="number" name="vava_gm_reminder_hours" value="<?php echo esc_attr( $settings['reminder_hours'] ); ?>" min="1" max="168" class="small-text"> ساعة قبل الموعد</td></tr>
</table>
<p><strong>Redirect URI:</strong> <code dir="ltr"><?php echo esc_html( $redirect_uri ); ?></code></p>
<p><strong>الحالة:</strong> <?php echo $needs_reconnect ? '<span style="color:#b45309;">⚠ يحتاج إعادة ربط — تعذّر تجديد تفويض Google</span>' : ( $connected ? '<span style="color:green;">✓ متصل (تم التحقق من صلاحية الوصول)</span>' : '<span style="color:#b8860b;">غير متصل</span>' ); ?></p>
<?php submit_button( 'حفظ الإعدادات' ); ?>
</form>
<hr>
<?php if ( $connected ) : ?>
<p><a href="<?php echo esc_url( vava_google_meet_get_auth_url() ); ?>" class="button button-primary"><?php echo $needs_reconnect ? 'إعادة ربط حساب Google' : 'تجديد تفويض حساب Google'; ?></a></p>
<p class="description">إعادة التفويض لا تحذف الحجوزات أو إعدادات التقويم المحفوظة.</p>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
<?php wp_nonce_field( 'vava_google_meet_disconnect', '_vava_gm_disconnect_nonce' ); ?>
<input type="hidden" name="action" value="vava_google_meet_disconnect">
<?php submit_button( 'فصل حساب Google', 'secondary' ); ?>
</form>
<?php else : ?>
<a href="<?php echo esc_url( vava_google_meet_get_auth_url() ); ?>" class="button button-primary">ربط حساب Google</a>
<?php endif; ?>
</div>
<?php
}

function vava_google_meet_save_settings_handler(): void {
if ( ! isset( $_POST['_vava_gm_nonce'] ) || ! wp_verify_nonce( $_POST['_vava_gm_nonce'], 'vava_save_google_meet_settings' ) ) { wp_die( 'Nonce failed.' ); }
if ( ! current_user_can( vava_google_meet_capability() ) ) { wp_die( 'Unauthorized.' ); }
$current = vava_google_meet_get_settings();
$new = array(
'enabled' => isset( $_POST['vava_gm_enabled'] ) ? 1 : 0,
'client_id' => sanitize_text_field( (string) ( $_POST['vava_gm_client_id'] ?? '' ) ),
'client_secret' => ( '' === (string) ( $_POST['vava_gm_client_secret'] ?? '' ) ) ? $current['client_secret'] : sanitize_text_field( (string) $_POST['vava_gm_client_secret'] ),
'calendar_id' => sanitize_text_field( (string) ( $_POST['vava_gm_calendar_id'] ?? 'primary' ) ) ?: 'primary',
'automation_enabled' => isset( $_POST['vava_gm_automation_enabled'] ) ? 1 : 0,
'reminder_enabled' => isset( $_POST['vava_gm_reminder_enabled'] ) ? 1 : 0,
'reminder_hours' => max( 1, min( 168, absint( $_POST['vava_gm_reminder_hours'] ?? 24 ) ) ),
);
update_option( VAVA_GOOGLE_MEET_SETTINGS_OPTION, $new, false );
wp_safe_redirect( add_query_arg( 'vava_gm_notice', 'saved', admin_url( 'edit.php?post_type=vava_booking&page=vava-google-meet-settings' ) ) ); exit;
}
add_action( 'admin_post_vava_save_google_meet_settings', 'vava_google_meet_save_settings_handler' );
function vava_google_meet_oauth_callback_handler(): void {
if ( ! current_user_can( vava_google_meet_capability() ) ) { wp_die( 'Unauthorized.' ); }
$state = (string) ( $_GET['state'] ?? '' );
if ( ! wp_verify_nonce( $state, 'vava_gm_oauth_state_' . get_current_user_id() ) ) { wp_die( 'Invalid state.' ); }
$code = (string) ( $_GET['code'] ?? '' );
if ( ! $code ) { wp_safe_redirect( add_query_arg( 'vava_gm_notice', 'error', admin_url( 'edit.php?post_type=vava_booking&page=vava-google-meet-settings' ) ) ); exit; }
$settings = vava_google_meet_get_settings();
$resp = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
'body' => array( 'code' => $code, 'client_id' => $settings['client_id'], 'client_secret' => $settings['client_secret'],
'redirect_uri' => admin_url( 'admin-post.php?action=vava_google_meet_oauth_callback' ), 'grant_type' => 'authorization_code' ),
'timeout' => 30,
) );
if ( is_wp_error( $resp ) ) { wp_safe_redirect( add_query_arg( 'vava_gm_notice', 'error', admin_url( 'edit.php?post_type=vava_booking&page=vava-google-meet-settings' ) ) ); exit; }
$body = json_decode( wp_remote_retrieve_body( $resp ), true );
if ( ! is_array( $body ) || empty( $body['refresh_token'] ) ) { wp_safe_redirect( add_query_arg( 'vava_gm_notice', 'error', admin_url( 'edit.php?post_type=vava_booking&page=vava-google-meet-settings' ) ) ); exit; }
update_option( VAVA_GOOGLE_MEET_TOKENS_OPTION, array(
'access_token' => $body['access_token'] ?? '', 'refresh_token' => $body['refresh_token'],
'expires_at' => time() + (int) ( $body['expires_in'] ?? 3600 ),
), false );
wp_safe_redirect( add_query_arg( 'vava_gm_notice', 'connected', admin_url( 'edit.php?post_type=vava_booking&page=vava-google-meet-settings' ) ) ); exit;
}
add_action( 'admin_post_vava_google_meet_oauth_callback', 'vava_google_meet_oauth_callback_handler' );
function vava_google_meet_disconnect_handler(): void {
if ( ! isset( $_POST['_vava_gm_disconnect_nonce'] ) || ! wp_verify_nonce( $_POST['_vava_gm_disconnect_nonce'], 'vava_google_meet_disconnect' ) ) { wp_die( 'Nonce failed.' ); }
if ( ! current_user_can( vava_google_meet_capability() ) ) { wp_die( 'Unauthorized.' ); }
delete_option( VAVA_GOOGLE_MEET_TOKENS_OPTION );
wp_safe_redirect( add_query_arg( 'vava_gm_notice', 'disconnected', admin_url( 'edit.php?post_type=vava_booking&page=vava-google-meet-settings' ) ) ); exit;
}
add_action( 'admin_post_vava_google_meet_disconnect', 'vava_google_meet_disconnect_handler' );
function vava_google_meet_resolve_event( string $event_id ): string {
$settings = vava_google_meet_get_settings();
$token = vava_google_meet_refresh_access_token();
if ( ! $token || ! $event_id ) { return ''; }
$cal_id = rawurlencode( $settings['calendar_id'] ?: 'primary' );
$resp = wp_remote_get( 'https://www.googleapis.com/calendar/v3/calendars/' . $cal_id . '/events/' . rawurlencode( $event_id ), array(
'headers' => array( 'Authorization' => 'Bearer ' . $token ), 'timeout' => 20,
) );
if ( is_wp_error( $resp ) ) { return ''; }
$body = json_decode( wp_remote_retrieve_body( $resp ), true );
if ( ! is_array( $body ) ) { return ''; }
if ( ! empty( $body['hangoutLink'] ) ) { return $body['hangoutLink']; }
if ( ! empty( $body['conferenceData']['entryPoints'] ) ) {
foreach ( $body['conferenceData']['entryPoints'] as $ep ) {
if ( 'video' === ( $ep['entryPointType'] ?? '' ) && ! empty( $ep['uri'] ) ) { return $ep['uri']; }
}
}
return '';
}
function vava_google_meet_persist_meeting( int $booking_id, string $url, string $event_id ): void {
$existing_created = (string) get_post_meta( $booking_id, '_vava_booking_meeting_created_at', true );
update_post_meta( $booking_id, '_vava_booking_meeting_provider', 'google_meet' );
update_post_meta( $booking_id, '_vava_booking_meeting_url', esc_url_raw( $url ) );
update_post_meta( $booking_id, '_vava_booking_meeting_status', 'assigned' );
update_post_meta( $booking_id, '_vava_booking_meeting_updated_at', current_time( 'mysql', true ) );
update_post_meta( $booking_id, '_vava_booking_meeting_updated_by', get_current_user_id() );
if ( ! $existing_created ) { update_post_meta( $booking_id, '_vava_booking_meeting_created_at', current_time( 'mysql', true ) ); }
if ( $event_id ) { update_post_meta( $booking_id, '_vava_booking_google_event_id', $event_id ); }
if ( function_exists( 'vava_booking_append_action_log' ) ) {
vava_booking_append_action_log( $booking_id, 'meeting_generated', '', '', '', '', '' );
}
}
function vava_booking_generate_google_meet( int $booking_id ): array {
if ( get_post_type( $booking_id ) !== 'vava_booking' ) { return array( 'ok' => false, 'message' => 'Invalid booking.' ); }
if ( function_exists( 'vava_booking_order_is_product' ) && vava_booking_order_is_product( $booking_id ) ) { return array( 'ok' => false, 'message' => 'Product orders cannot have meetings.' ); }
$settings = vava_google_meet_get_settings();
if ( empty( $settings['enabled'] ) || ! vava_google_meet_is_connected() ) { return array( 'ok' => false, 'message' => 'Google Meet not configured or connected.' ); }
$existing_url = esc_url_raw( (string) get_post_meta( $booking_id, '_vava_booking_meeting_url', true ) );
$existing_event = (string) get_post_meta( $booking_id, '_vava_booking_google_event_id', true );
if ( $existing_url && filter_var( $existing_url, FILTER_VALIDATE_URL ) ) {
$host = strtolower( (string) wp_parse_url( $existing_url, PHP_URL_HOST ) );
if ( 'meet.google.com' === $host ) { return array( 'ok' => true, 'url' => $existing_url, 'event_id' => $existing_event, 'status' => 'existing' ); }
}
if ( $existing_event ) {
$resolved = vava_google_meet_resolve_event( $existing_event );
if ( $resolved ) {
vava_google_meet_persist_meeting( $booking_id, $resolved, $existing_event );
return array( 'ok' => true, 'url' => $resolved, 'event_id' => $existing_event, 'status' => 'resolved' );
}
update_post_meta( $booking_id, '_vava_booking_meeting_status', 'generating' );
return array( 'ok' => false, 'message' => 'Meeting link pending generation.', 'event_id' => $existing_event, 'status' => 'generating' );
}
$lock_key = 'vava_gm_gen_lock_' . $booking_id;
if ( get_transient( $lock_key ) ) { return array( 'ok' => false, 'message' => 'Generation already in progress.' ); }
set_transient( $lock_key, 1, 60 );
$date = (string) get_post_meta( $booking_id, '_vava_booking_date', true );
$time = (string) get_post_meta( $booking_id, '_vava_booking_time', true );
$duration_min = (int) get_post_meta( $booking_id, '_vava_booking_service_duration', true );
if ( $duration_min < 1 ) { $duration_min = 60; }
if ( ! $date || ! $time ) { delete_transient( $lock_key ); return array( 'ok' => false, 'message' => 'Missing date/time.' ); }
$tz = function_exists( 'vava_booking_timezone' ) ? vava_booking_timezone() : wp_timezone_string();
try { $start_dt = new DateTime( $date . ' ' . $time, new DateTimeZone( $tz ) ); } catch ( Exception $e ) { delete_transient( $lock_key ); return array( 'ok' => false, 'message' => 'Invalid date/time.' ); }
$end_dt = clone $start_dt; $end_dt->modify( '+' . $duration_min . ' minutes' );
if ( $start_dt <= new DateTime( 'now', new DateTimeZone( $tz ) ) ) { delete_transient( $lock_key ); return array( 'ok' => false, 'message' => 'Cannot generate meeting for past sessions.' ); }
$service_title = (string) get_post_meta( $booking_id, '_vava_booking_service_title', true );
$request_id = 'vava-' . $booking_id . '-' . wp_generate_uuid4();
$payload = array(
'summary' => 'VAVA — ' . $service_title,
'description' => 'Booking #' . $booking_id,
'start' => array( 'dateTime' => $start_dt->format( 'c' ), 'timeZone' => $tz ),
'end' => array( 'dateTime' => $end_dt->format( 'c' ), 'timeZone' => $tz ),
'conferenceData' => array( 'createRequest' => array( 'requestId' => $request_id, 'conferenceSolutionKey' => array( 'type' => 'hangoutsMeet' ) ) ),
);
$token = vava_google_meet_refresh_access_token();
if ( ! $token ) { delete_transient( $lock_key ); return array( 'ok' => false, 'message' => 'Unable to obtain access token.' ); }
$cal_id = rawurlencode( $settings['calendar_id'] ?: 'primary' );
$api_url = 'https://www.googleapis.com/calendar/v3/calendars/' . $cal_id . '/events?conferenceDataVersion=1&sendUpdates=none';
$resp = wp_remote_post( $api_url, array(
'headers' => array( 'Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json' ),
'body' => wp_json_encode( $payload ), 'timeout' => 30,
) );
delete_transient( $lock_key );
if ( is_wp_error( $resp ) ) { return array( 'ok' => false, 'message' => 'API request failed.' ); }
$code = wp_remote_retrieve_response_code( $resp );
$body = json_decode( wp_remote_retrieve_body( $resp ), true );
if ( $code < 200 || $code >= 300 || ! is_array( $body ) ) { return array( 'ok' => false, 'message' => 'Google API error: ' . ( $body['error']['message'] ?? 'unknown' ) ); }
$event_id = (string) ( $body['id'] ?? '' );
$meet_url = '';
if ( ! empty( $body['hangoutLink'] ) ) { $meet_url = $body['hangoutLink']; }
if ( ! $meet_url && ! empty( $body['conferenceData']['entryPoints'] ) ) {
foreach ( $body['conferenceData']['entryPoints'] as $ep ) {
if ( 'video' === ( $ep['entryPointType'] ?? '' ) && ! empty( $ep['uri'] ) ) { $meet_url = $ep['uri']; break; }
}
}
if ( $event_id ) { update_post_meta( $booking_id, '_vava_booking_google_event_id', $event_id ); }
if ( ! empty( $body['htmlLink'] ) ) { update_post_meta( $booking_id, '_vava_booking_google_event_html_url', esc_url_raw( $body['htmlLink'] ) ); }
if ( $meet_url ) {
$parsed = wp_parse_url( $meet_url );
if ( 'https' === strtolower( $parsed['scheme'] ?? '' ) && 'meet.google.com' === strtolower( $parsed['host'] ?? '' ) ) {
vava_google_meet_persist_meeting( $booking_id, $meet_url, $event_id );
return array( 'ok' => true, 'url' => $meet_url, 'event_id' => $event_id, 'status' => 'created' );
}
}
update_post_meta( $booking_id, '_vava_booking_meeting_status', 'generating' );
return array( 'ok' => false, 'message' => 'Meeting link pending.', 'event_id' => $event_id, 'status' => 'generating' );
}

function vava_booking_generate_google_meet_admin_post(): void {
if ( ! isset( $_POST['_wpnonce'], $_POST['booking_id'] ) ) { wp_die( 'Invalid.' ); }
$booking_id = absint( $_POST['booking_id'] );
if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'vava_booking_generate_meet_' . $booking_id ) ) { wp_die( 'Nonce failed.' ); }
if ( ! current_user_can( vava_google_meet_capability() ) ) { wp_die( 'Unauthorized.' ); }
$result = vava_booking_generate_google_meet( $booking_id );
$notice = $result['ok'] ? 'meet_generated' : 'meet_error';
$args = array( $notice => '1' );
if ( ! $result['ok'] && ! empty( $result['status'] ) && 'generating' === $result['status'] ) { $args = array( 'meet_pending' => '1' ); }
wp_safe_redirect( add_query_arg( $args, function_exists( 'vava_booking_admin_details_url' ) ? vava_booking_admin_details_url( $booking_id ) : admin_url( 'edit.php?post_type=vava_booking' ) ) ); exit;
}
add_action( 'admin_post_vava_booking_generate_google_meet', 'vava_booking_generate_google_meet_admin_post' );
function vava_booking_automation_queue_processing( int $booking_id ): void {
$settings = vava_google_meet_get_settings();
if ( empty( $settings['enabled'] ) || empty( $settings['automation_enabled'] ) ) { return; }
if ( ! vava_google_meet_is_connected() ) { return; }
if ( function_exists( 'vava_booking_order_is_product' ) && vava_booking_order_is_product( $booking_id ) ) { return; }
if ( ! wp_next_scheduled( 'vava_booking_automation_process_booking', array( $booking_id ) ) ) {
wp_schedule_single_event( time() + 5, 'vava_booking_automation_process_booking', array( $booking_id ) );
}
}
function vava_booking_automation_process_booking_handler( int $booking_id ): void {
$post = get_post( $booking_id );
if ( ! $post || 'vava_booking' !== $post->post_type ) { return; }
if ( function_exists( 'vava_booking_order_is_product' ) && vava_booking_order_is_product( $booking_id ) ) { return; }
$status = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
$payment = function_exists( 'vava_booking_effective_payment_status' ) ? vava_booking_effective_payment_status( $booking_id ) : '';
if ( ! in_array( $status, array( 'confirmed', 'paid' ), true ) && 'paid' !== $payment ) { return; }
if ( in_array( $status, array( 'cancelled', 'customer_cancelled', 'cancellation_requested', 'bank_rejected', 'payment_failed', 'refunded' ), true ) ) { return; }
$date = (string) get_post_meta( $booking_id, '_vava_booking_date', true );
if ( ! $date ) { return; }
$tz = function_exists( 'vava_booking_timezone' ) ? vava_booking_timezone() : wp_timezone_string();
try { $booking_dt = new DateTime( $date, new DateTimeZone( $tz ) ); } catch ( Exception $e ) { return; }
$booking_time = (string) get_post_meta( $booking_id, '_vava_booking_time', true );
if ( ! preg_match( '/^(?:[01]\\d|2[0-3]):[0-5]\\d$/', $booking_time ) ) { return; }
try { $booking_dt = new DateTime( $date . ' ' . $booking_time, new DateTimeZone( $tz ) ); } catch ( Exception $e ) { return; }
if ( $booking_dt <= new DateTime( 'now', new DateTimeZone( $tz ) ) ) { return; }
$auto_sent = (string) get_post_meta( $booking_id, '_vava_booking_meeting_auto_email_sent_at', true );
if ( $auto_sent ) { return; }
$result = vava_booking_generate_google_meet( $booking_id );
if ( ! $result['ok'] ) {
update_post_meta( $booking_id, '_vava_booking_meeting_auto_email_last_attempt_at', current_time( 'mysql', true ) );
return;
}
if ( function_exists( 'vava_booking_send_details_email' ) ) {
$email_ok = vava_booking_send_details_email( $booking_id, true, false );
if ( ! is_wp_error( $email_ok ) && $email_ok ) {
update_post_meta( $booking_id, '_vava_booking_meeting_auto_email_sent_at', current_time( 'mysql', true ) );
if ( function_exists( 'vava_booking_append_action_log' ) ) {
vava_booking_append_action_log( $booking_id, 'meeting_auto_sent', '', '', '', '', '' );
}
} else {
update_post_meta( $booking_id, '_vava_booking_meeting_auto_email_last_attempt_at', current_time( 'mysql', true ) );
}
}
vava_booking_maybe_schedule_reminder( $booking_id );
}
add_action( 'vava_booking_automation_process_booking', 'vava_booking_automation_process_booking_handler' );
function vava_booking_maybe_schedule_reminder( int $booking_id ): void {
$settings = vava_google_meet_get_settings();
if ( empty( $settings['reminder_enabled'] ) ) { return; }
$url = esc_url_raw( (string) get_post_meta( $booking_id, '_vava_booking_meeting_url', true ) );
if ( ! $url ) { return; }
$already_scheduled = (string) get_post_meta( $booking_id, '_vava_booking_meeting_reminder_scheduled_at', true );
if ( $already_scheduled ) { return; }
$date = (string) get_post_meta( $booking_id, '_vava_booking_date', true );
$time = (string) get_post_meta( $booking_id, '_vava_booking_time', true );
if ( ! $date || ! $time ) { return; }
$tz = function_exists( 'vava_booking_timezone' ) ? vava_booking_timezone() : wp_timezone_string();
try { $session_dt = new DateTime( $date . ' ' . $time, new DateTimeZone( $tz ) ); } catch ( Exception $e ) { return; }
$hours = max( 1, min( 168, (int) $settings['reminder_hours'] ) );
$reminder_ts = $session_dt->getTimestamp() - ( $hours * 3600 );
if ( $reminder_ts <= time() ) { return; }
update_post_meta( $booking_id, '_vava_booking_meeting_reminder_scheduled_at', current_time( 'mysql', true ) );
if ( ! wp_next_scheduled( 'vava_booking_meeting_reminder_event', array( $booking_id ) ) ) {
wp_schedule_single_event( $reminder_ts, 'vava_booking_meeting_reminder_event', array( $booking_id ) );
}
}
function vava_booking_meeting_reminder_handler( int $booking_id ): void {
$post = get_post( $booking_id );
if ( ! $post || 'vava_booking' !== $post->post_type ) { return; }
if ( function_exists( 'vava_booking_order_is_product' ) && vava_booking_order_is_product( $booking_id ) ) { return; }
$status = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
if ( in_array( $status, array( 'cancelled', 'customer_cancelled', 'cancellation_requested', 'bank_rejected', 'payment_failed', 'refunded' ), true ) ) { return; }
$already_sent = (string) get_post_meta( $booking_id, '_vava_booking_meeting_reminder_sent_at', true );
if ( $already_sent ) { return; }
$url = esc_url_raw( (string) get_post_meta( $booking_id, '_vava_booking_meeting_url', true ) );
if ( ! $url ) {
$result = vava_booking_generate_google_meet( $booking_id );
if ( $result['ok'] ) { $url = $result['url']; }
}
if ( ! $url ) { update_post_meta( $booking_id, '_vava_booking_meeting_reminder_last_attempt_at', current_time( 'mysql', true ) ); return; }
$date = (string) get_post_meta( $booking_id, '_vava_booking_date', true );
$time = (string) get_post_meta( $booking_id, '_vava_booking_time', true );
$tz = function_exists( 'vava_booking_timezone' ) ? vava_booking_timezone() : wp_timezone_string();
try { $session_dt = new DateTime( $date . ' ' . $time, new DateTimeZone( $tz ) ); } catch ( Exception $e ) { return; }
if ( $session_dt <= new DateTime( 'now', new DateTimeZone( $tz ) ) ) { return; }
$customer = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
$email = sanitize_email( (string) ( $customer['email'] ?? '' ) );
if ( ! $email ) { return; }
$lang = (string) get_post_meta( $booking_id, '_vava_booking_language', true ) ?: 'ar';
$is_en = 'en' === $lang;
$service = (string) get_post_meta( $booking_id, '_vava_booking_service_title', true );
$questionnaire_exists = ! empty( get_post_meta( $booking_id, '_vava_booking_questionnaire', true ) );
$subject = $is_en ? 'Session Reminder — VAVA' : 'تذكير بالجلسة — VAVA';
$body_lines = array();
$body_lines[] = $is_en ? "Hello {$customer['name']}," : "مرحبًا {$customer['name']}،";
$body_lines[] = '';
$body_lines[] = $is_en ? "Your upcoming session:" : "جلستك القادمة:";
$body_lines[] = $service;
$body_lines[] = $date . ' — ' . $time;
$body_lines[] = '';
$body_lines[] = $is_en ? "Join meeting: {$url}" : "رابط الاجتماع: {$url}";
$body_lines[] = '';
$body_lines[] = $is_en ? "Booking #{$booking_id}" : "رقم الحجز #{$booking_id}";
if ( $questionnaire_exists ) {
$body_lines[] = '';
$body_lines[] = $is_en ? 'Your pre-session questionnaire has been received.' : 'تم استلام استبيان ما قبل الجلسة.';
}
$sent = wp_mail( $email, $subject, implode( "\n", $body_lines ) );
if ( $sent ) {
update_post_meta( $booking_id, '_vava_booking_meeting_reminder_sent_at', current_time( 'mysql', true ) );
if ( function_exists( 'vava_booking_append_action_log' ) ) {
vava_booking_append_action_log( $booking_id, 'meeting_reminder_sent', '', '', '', '', '' );
}
} else {
update_post_meta( $booking_id, '_vava_booking_meeting_reminder_last_attempt_at', current_time( 'mysql', true ) );
}
}
add_action( 'vava_booking_meeting_reminder_event', 'vava_booking_meeting_reminder_handler' );
function vava_booking_automation_hourly_handler(): void {
$settings = vava_google_meet_get_settings();
if ( empty( $settings['enabled'] ) || empty( $settings['automation_enabled'] ) ) { return; }
if ( ! vava_google_meet_is_connected() ) { return; }
$args = array(
'post_type' => 'vava_booking', 'post_status' => 'publish', 'posts_per_page' => 100,
'meta_query' => array(
'relation' => 'AND',
array( 'key' => '_vava_booking_status', 'value' => array( 'confirmed', 'paid' ), 'compare' => 'IN' ),
array( 'key' => '_vava_booking_meeting_auto_email_sent_at', 'compare' => 'NOT EXISTS' ),
array( 'key' => '_vava_booking_date', 'value' => wp_date( 'Y-m-d' ), 'compare' => '>=', 'type' => 'DATE' ),
),
'orderby' => 'date', 'order' => 'DESC',
);
$bookings = get_posts( $args );
foreach ( $bookings as $post ) {
if ( function_exists( 'vava_booking_order_is_product' ) && vava_booking_order_is_product( $post->ID ) ) { continue; }
$date = (string) get_post_meta( $post->ID, '_vava_booking_date', true );
if ( ! $date ) { continue; }
$tz = function_exists( 'vava_booking_timezone' ) ? vava_booking_timezone() : wp_timezone_string();
try { $dt = new DateTime( $date, new DateTimeZone( $tz ) ); } catch ( Exception $e ) { continue; }
$booking_time = (string) get_post_meta( $post->ID, '_vava_booking_time', true );
try { $dt = new DateTime( $date . ' ' . $booking_time, new DateTimeZone( $tz ) ); } catch ( Exception $e ) { continue; }
if ( $dt <= new DateTime( 'now', new DateTimeZone( $tz ) ) ) { continue; }
vava_booking_automation_process_booking_handler( $post->ID );
}
$rem_args = array(
'post_type' => 'vava_booking', 'post_status' => 'publish', 'posts_per_page' => 20,
'meta_query' => array(
'relation' => 'AND',
array( 'key' => '_vava_booking_meeting_reminder_scheduled_at', 'compare' => 'EXISTS' ),
array( 'key' => '_vava_booking_meeting_reminder_sent_at', 'compare' => 'NOT EXISTS' ),
array( 'key' => '_vava_booking_meeting_url', 'compare' => 'EXISTS' ),
array( 'key' => '_vava_booking_date', 'value' => wp_date( 'Y-m-d' ), 'compare' => '>=', 'type' => 'DATE' ),
),
);
$rem_bookings = get_posts( $rem_args );
foreach ( $rem_bookings as $post ) {
vava_booking_meeting_reminder_handler( $post->ID );
}
}
add_action( 'vava_booking_automation_hourly', 'vava_booking_automation_hourly_handler' );
if ( ! wp_next_scheduled( 'vava_booking_automation_hourly' ) ) {
wp_schedule_event( time(), 'hourly', 'vava_booking_automation_hourly' );
}
function vava_booking_automation_on_status_change( int $booking_id, string $new_status ): void {
if ( in_array( $new_status, array( 'confirmed', 'paid' ), true ) ) {
vava_booking_automation_queue_processing( $booking_id );
}
}
add_action( 'vava_booking_status_changed', 'vava_booking_automation_on_status_change', 10, 2 );
