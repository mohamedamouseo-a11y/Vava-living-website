<?php
/** Inside the authenticated VAVA customer account; no public contact redirect. */
defined( 'ABSPATH' ) || exit;
$customer = $account_user instanceof WP_User && vava_customer_is_customer( $account_user ) &&
    vava_customer_is_verified( $account_user->ID ) ? $account_user : null;
$notices = array(
    'invalid' => array( 'Check the subject and message fields and try again.', 'راجعي الموضوع والرسالة وحاولي مرة أخرى.' ),
    'security' => array( 'Your session expired. Refresh this page and send again.', 'انتهت صلاحية الجلسة. حدّثي الصفحة وأرسلي الرسالة مجددًا.' ),
    'error' => array( 'Could not save your message. Please try again.', 'تعذر حفظ الرسالة. حاولي مرة أخرى.' ),
    'rate_limited' => array( 'Please wait a few seconds before sending another message.', 'انتظري ثواني قبل إرسال رسالة جديدة.' ),
    'login' => array( 'Please sign in to your verified customer account.', 'سجّلي دخولك إلى حساب العميل الموثّق.' ),
);
$result = isset( $_GET['support_result'] ) ? sanitize_key( wp_unslash( $_GET['support_result'] ) ) : '';
$ticket_id = isset( $_GET['support_ticket'] ) ? absint( $_GET['support_ticket'] ) : 0;
if ( $ticket_id && ( ! $customer || (int) get_post_field( 'post_author', $ticket_id ) !== $customer->ID ) ) { $ticket_id = 0; }
?>
<section class="vava-account-support" id="vava-account-support">
    <header class="vava-account-section-heading">
        <div><small><?php echo esc_html( $is_en ? 'Direct, secure customer support' : 'دعم آمن ومباشر من حسابك' ); ?></small>
        <h2><?php echo esc_html( $is_en ? 'Support and help' : 'الدعم والمساعدة' ); ?></h2></div>
    </header>
    <?php if ( 'saved' === $result && $ticket_id ) : ?>
        <div class="vava-support-feedback is-success" role="status">
            <strong><?php echo esc_html( $is_en ? 'Your request has been saved.' : 'تم حفظ رسالة الدعم بنجاح.' ); ?></strong>
            <p><?php echo esc_html( sprintf( $is_en ? 'Ticket #%d is now available below. An email notification is being processed separately.' : 'تذكرتك رقم #%d ظاهرة بالأسفل. إشعار البريد بيتم إرساله بشكل منفصل.', $ticket_id ) ); ?></p>
        </div>
    <?php elseif ( isset( $notices[ $result ] ) ) : ?>
        <div class="vava-support-feedback is-error" role="alert"><?php echo esc_html( $notices[ $result ][ $is_en ? 0 : 1 ] ); ?></div>
    <?php endif; ?>
    <?php if ( ! $customer ) : ?>
        <div class="vava-support-feedback" role="status"><?php echo esc_html( $is_en ? 'To send support messages and view their status, sign in with a verified VAVA customer account.' : 'لإرسال رسالة دعم ومتابعة حالتها، سجّلي الدخول بحساب عميل VAVA موثّق.' ); ?></div>
    <?php else : ?>
        <div class="vava-support-layout">
            <div class="vava-support-card">
                <h3><?php echo esc_html( $is_en ? 'Send a message' : 'إرسال رسالة للدعم' ); ?></h3>
                <p><?php echo esc_html( $is_en ? 'Your message is saved under your account first. You can review its status below, even if email delivery is delayed.' : 'رسالتك بتتحفظ في حسابك أولًا، وتقدري تتابعي حالتها هنا حتى لو الإيميل اتأخر.' ); ?></p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-vava-support-form>
                    <input type="hidden" name="action" value="vava_customer_support_submit">
                    <input type="hidden" name="lang" value="<?php echo esc_attr( $lang ); ?>">
                    <?php wp_nonce_field( 'vava_customer_support_submit' ); ?>
                    <div class="vava-support-trap" aria-hidden="true"><label>Website <input type="text" name="website" autocomplete="off" tabindex="-1"></label></div>
                    <label for="vava-support-subject"><?php echo esc_html( $is_en ? 'Subject' : 'موضوع الرسالة' ); ?> <span>*</span></label>
                    <input type="text" id="vava-support-subject" name="subject" required minlength="3" maxlength="160" autocomplete="off"
                        placeholder="<?php echo esc_attr( $is_en ? 'How can we help?' : 'إزاي نقدر نساعدك؟' ); ?>">
                    <label for="vava-support-message"><?php echo esc_html( $is_en ? 'Message' : 'تفاصيل الرسالة' ); ?> <span>*</span></label>
                    <textarea id="vava-support-message" name="message" required minlength="10" maxlength="4000" rows="6"
                        placeholder="<?php echo esc_attr( $is_en ? 'Describe the issue or question so we can help.' : 'اكتبي تفاصيل المشكلة أو الاستفسار هنا.' ); ?>"></textarea>
                    <div class="vava-support-identity"><span><?php echo esc_html( $is_en ? 'Reply email' : 'بريد الرد' ); ?></span><strong dir="ltr"><?php echo esc_html( $customer->user_email ); ?></strong></div>
                    <button type="submit" class="vava-support-send"><?php echo esc_html( $is_en ? 'Send support request' : 'إرسال الرسالة للدعم' ); ?></button>
                </form>
            </div>
            <div class="vava-support-card vava-support-history">
                <h3><?php echo esc_html( $is_en ? 'My support requests' : 'رسائل الدعم السابقة' ); ?></h3>
                <?php $tickets = vava_support_customer_tickets( $customer->ID ); ?>
                <?php if ( ! $tickets ) : ?>
                    <div class="vava-support-empty"><?php echo esc_html( $is_en ? 'You have not sent any support requests yet.' : 'مافيش رسائل دعم سابقة حتى الآن.' ); ?></div>
                <?php else : ?>
                    <div class="vava-support-ticket-list">
                    <?php foreach ( $tickets as $ticket ) :
                        $state = (string) get_post_meta( $ticket->ID, '_vava_support_status', true );
                        $notice_state = (string) get_post_meta( $ticket->ID, '_vava_support_notice_admin_state', true );
                        $labels = array(
                            'new' => $is_en ? 'Received' : 'تم الاستلام',
                            'in_progress' => $is_en ? 'In progress' : 'قيد المعالجة',
                            'resolved' => $is_en ? 'Resolved' : 'تم الحل',
                        );
                        $mail_label = 'sent' === $notice_state
                            ? ( $is_en ? 'Notification accepted by email service' : 'تم قبول الإشعار من نظام البريد' )
                            : ( in_array( $notice_state, array( 'failed', 'missing_email', 'disabled', 'queue_failed' ), true )
                                ? ( $is_en ? 'Email notification needs attention; the request is saved.' : 'إشعار البريد يحتاج مراجعة، لكن التذكرة محفوظة.' )
                                : ( $is_en ? 'Email notification pending' : 'إشعار البريد قيد الإرسال' ) );
                        ?>
                        <article class="vava-support-ticket" <?php echo $ticket_id === (int) $ticket->ID ? 'data-new-support-ticket' : ''; ?>>
                            <div class="vava-support-ticket-head"><strong>#<?php echo esc_html( (string) $ticket->ID ); ?> — <?php echo esc_html( $ticket->post_title ); ?></strong>
                                <span data-status="<?php echo esc_attr( $state ); ?>"><?php echo esc_html( $labels[ $state ] ?? $labels['new'] ); ?></span></div>
                            <small><?php echo esc_html( wp_date( 'Y-m-d H:i', strtotime( $ticket->post_date ) ) ); ?></small>
                            <p><?php echo esc_html( $ticket->post_content ); ?></p>
                            <small class="vava-support-email-state"><?php echo esc_html( $mail_label ); ?></small>
                        </article>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
