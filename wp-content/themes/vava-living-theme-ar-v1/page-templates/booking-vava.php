<?php
/**
 * Template Name: VAVA — Booking (AR / EN)
 * Template Post Type: page
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;
// VAVA_BOOKING_POLICY_CONSENT_V1
// VAVA_BOOKING_SETTINGS_AND_STAGE_ONE_POLISH_V1R10
// VAVA_BOOKING_REVIEW_FOOTER_PREVIEW_V1R8
// VAVA_BOOKING_REVIEW_REFERENCE_SETTINGS_PREVIEW_V1R7
// VAVA_BOOKING_PAYMENT_REFERENCE_MATCH_V1R6
// VAVA_BOOKING_WIZARD_PAYMENT_REVIEW_V1R5
// VAVA_BOOKING_WIZARD_B1_B2_V1R1
$page_id = get_queried_object_id();
$lang = vava_current_language();
$is_en = 'en' === $lang;
$text = vava_booking_text_data( $page_id, $lang );
$shared = vava_booking_shared_data( $page_id );
$identifier = isset( $_GET['service'] ) ? sanitize_text_field( wp_unslash( $_GET['service'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$service = $identifier ? vava_booking_resolve_service( $identifier, $lang ) : null;
$available = $service && vava_booking_service_is_available( $service, $shared );
$paymob_enabled = ! empty( $shared['payment_methods']['paymob'] );
$paymob_ready = $paymob_enabled && vava_booking_paymob_is_ready( $shared );
$bank_enabled = ! empty( $shared['payment_methods']['bank'] );
$bank_ready = $bank_enabled && vava_booking_bank_is_ready( $shared );
$bank_config = (array) ( $shared['bank_transfer'] ?? array() );
$booking_countries = function_exists( 'vava_booking_country_calling_codes' ) ? vava_booking_country_calling_codes() : array();
$booking_currency_raw = (string) ( $service['currency'] ?? ( $bank_config['currency'] ?? 'SAR' ) );
$booking_currency = strtoupper( preg_replace( '/[^A-Za-z]/', '', $booking_currency_raw ) );
$default_country_iso = ( 'EGP' === $booking_currency || false !== strpos( $booking_currency_raw, 'ج.م' ) ) ? 'EG' : 'SA';
$whatsapp_required = ! empty( $text['fields']['whatsapp']['required'] );
$whatsapp_country_label = $is_en ? 'Country and calling code' : 'الدولة ومفتاح الاتصال';
$whatsapp_number_label = $is_en ? 'Phone number' : 'رقم الهاتف';
$whatsapp_number_placeholder = $is_en ? 'Enter number without country code' : 'اكتب الرقم بدون كود الدولة';
$whatsapp_help = $is_en ? 'The country code is added automatically when your number is saved.' : 'يُضاف مفتاح الدولة تلقائيًا عند حفظ رقم WhatsApp.';
$is_free_service = $service && function_exists( 'vava_booking_service_is_free' ) ? vava_booking_service_is_free( $service ) : false;
$default_payment = $is_free_service ? 'free' : ( $paymob_ready ? 'paymob' : ( $bank_ready ? 'bank' : ( ! empty( $shared['payment_methods']['cash'] ) ? 'cash' : '' ) ) );
$has_payment_method = $is_free_service || $paymob_ready || $bank_ready || ! empty( $shared['payment_methods']['cash'] );
$paths_id = vava_booking_paths_page_id();
$paths_url = $paths_id ? vava_localized_page_url( $paths_id, $lang ) : home_url( '/' );
$service_detail_url = $service ? vava_booking_service_detail_url( $service, $lang ) : $paths_url;
$my_bookings_url = function_exists( 'vava_booking_my_bookings_url' ) ? vava_booking_my_bookings_url( $lang ) : $paths_url;
$status = isset( $_GET['booking_status'] ) ? sanitize_key( wp_unslash( $_GET['booking_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$booking_id = isset( $_GET['booking'] ) ? absint( $_GET['booking'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$stored_status = $booking_id ? (string) get_post_meta( $booking_id, '_vava_booking_status', true ) : '';
if ( 'success' === $status && ! in_array( $stored_status, array( 'paid', 'confirmed' ), true ) ) { $status = ''; }
if ( 'pending' === $status && ! in_array( $stored_status, array( 'pending_payment', 'pending' ), true ) ) { $status = ''; }
if ( 'failed' === $status && ! in_array( $stored_status, array( 'payment_failed', 'payment_error' ), true ) ) { $status = ''; }
$image = $service && ! empty( $service['image_id'] ) ? wp_get_attachment_image_url( (int) $service['image_id'], 'large' ) : '';
$duration_minutes = $service ? vava_booking_effective_duration( $service, $shared ) : max( 10, absint( $shared['default_duration'] ?? 90 ) );
$duration_label   = $service && function_exists( 'vava_booking_service_display_duration' ) ? vava_booking_service_display_duration( $service, $lang ) : $duration_minutes . ' ' . ( 'en' === $lang ? 'minutes' : 'دقيقة' );
$price_label = $service ? vava_booking_format_price_label( (string) $service['price'], (string) $service['currency'], $lang ) : '';
$ui = $is_en ? array(
	'page_title' => 'Book your VAVA session',
	'page_intro' => 'A calm four-step booking experience.',
	'step_one' => 'Selected service + your details',
	'service_details' => 'Service details',
	'customer_details' => 'Your details',
	'privacy' => 'Your information is used only to complete and manage this booking.',
	'terms' => 'I agree to the terms, privacy policy and booking policy.',
	'duration' => 'Duration',
	'type' => 'Type',
	'location' => 'Location',
	'price' => 'Price',
	'selected_date' => 'Date',
	'selected_time' => 'Time',
	'timezone' => 'Times are shown in',
	'duration_note' => 'Session duration',
	'minutes' => 'minutes',
	'secure_payment' => 'All payments are secure and encrypted.',
	'total' => 'Total',
	'not_selected' => 'Not selected yet',
	'cards' => 'Card / Mada / Apple Pay',
	'protected' => 'Privacy protected',
	'flexible' => 'Clear booking steps',
	'trusted' => 'Secure payment',
	'care' => 'A thoughtfully designed experience',
	'online_unavailable' => 'Online payment is temporarily unavailable.',
	'bank_account_title' => 'VAVA bank account',
	'bank_form_title' => 'Transfer details',
	'bank_name' => 'Bank',
	'beneficiary' => 'Beneficiary',
	'account_number' => 'Account number',
	'iban' => 'IBAN',
	'swift' => 'SWIFT',
	'transfer_name' => 'Transfer sender name',
	'from_bank' => 'Sending bank',
	'from_account' => 'Sender account number',
	'reference' => 'Transaction reference',
	'transfer_date' => 'Transfer date',
	'transfer_time' => 'Transfer time',
	'amount' => 'Transferred amount',
	'receipt' => 'Transfer receipt',
	'bank_notes' => 'Notes (optional)',
	'receipt_help' => 'JPG, PNG, WEBP or PDF — maximum 5MB.',
	'choose_file' => 'Choose file',
	'no_file' => 'No file selected',
	'step2_page_title' => 'Book your session at VAVA Living',
	'step2_page_intro' => 'Your journey toward balance starts here.',
	'step3_page_title' => 'Choose a payment method',
	'step3_page_intro' => 'Choose the most suitable payment method and complete its details.',
	'step4_page_title' => 'Review and confirm your booking',
	'step4_page_intro' => 'Check all booking details before final confirmation.',
	'continue_review' => 'Continue to review',
	'final_terms' => 'I agree to the terms, privacy policy and booking policy.',
	'final_confirm' => 'Final booking confirmation',
	'edit' => 'Edit',
	'receipt_name' => 'Receipt file',
	'payment_status_expected' => 'Expected payment status',
	'ideal_time' => 'Choose your ideal appointment',
	'booking_summary' => 'Booking summary',
	'total_duration' => 'Total duration',
	'service_kind' => 'Service type',
	'details_link' => 'View details',
	'makkah_time' => 'All appointments follow the configured VAVA time zone.',
	'unavailable_note' => 'Unavailable times are caused only by existing bookings, maintenance, or configured non-working days.',
	'choose_payment' => 'Choose a payment method',
	'online_payment_title' => 'Online payment (Mada / Visa / Mastercard)',
	'online_payment_note' => 'Instant, secure payment through Paymob.',
	'bank_payment_note' => 'Transfer the amount to our bank account.',
	'later_payment_note' => 'A payment link will be sent to your email.',
	'confirm_pay' => 'Confirm booking and pay now',
	'confirm_bank' => 'Submit booking for transfer review',
	'confirm_later' => 'Confirm booking',
	'payment_terms' => 'By confirming, you agree to the terms, privacy policy, and booking policy.',
	'map_link' => 'View on map',
	'tax_note' => 'Inclusive of applicable VAT',
	'encrypted_note' => 'All payment transactions are encrypted and 100% secure',
	'trust_experience' => 'A calm, tailored experience',
	'trust_experience_note' => 'Every detail is designed for your comfort',
	'trust_experts' => 'Trusted specialists',
	'trust_experts_note' => 'A team with deep expertise',
	'trust_products' => 'Natural, safe products',
	'trust_products_note' => 'Carefully selected products',
	'trust_privacy' => 'Your privacy matters',
	'trust_privacy_note' => 'Your data meets high security standards',
) : array(
	'page_title' => 'حجز جلسة مع VAVA',
	'page_intro' => 'تجربة حجز واضحة وهادئة في أربع خطوات.',
	'step_one' => 'الخدمة المختارة + بيانات الحجز',
	'service_details' => 'تفاصيل الخدمة',
	'customer_details' => 'بيانات الحجز',
	'privacy' => 'تُستخدم البيانات فقط لإتمام الحجز والتواصل بخصوصه.',
	'terms' => 'الموافقة على الشروط وسياسة الخصوصية وسياسة الحجز.',
	'duration' => 'المدة',
	'type' => 'النوع',
	'location' => 'المكان',
	'price' => 'السعر',
	'selected_date' => 'التاريخ',
	'selected_time' => 'الوقت',
	'timezone' => 'جميع المواعيد حسب المنطقة الزمنية',
	'duration_note' => 'مدة الجلسة',
	'minutes' => 'دقيقة',
	'secure_payment' => 'جميع عمليات الدفع آمنة ومشفرة.',
	'total' => 'الإجمالي',
	'not_selected' => 'لم يتم الاختيار بعد',
	'cards' => 'بطاقة / مدى / Apple Pay',
	'protected' => 'خصوصية محفوظة',
	'flexible' => 'خطوات حجز واضحة',
	'trusted' => 'دفع آمن',
	'care' => 'تجربة مصممة بعناية',
	'online_unavailable' => 'الدفع الإلكتروني غير متاح مؤقتًا حتى يكتمل الربط.',
	'bank_account_title' => 'بيانات حساب VAVA البنكي',
	'bank_form_title' => 'بيانات التحويل',
	'bank_name' => 'اسم البنك',
	'beneficiary' => 'اسم المستفيد',
	'account_number' => 'رقم الحساب',
	'iban' => 'رقم IBAN',
	'swift' => 'رمز SWIFT',
	'transfer_name' => 'اسم المحوِّل',
	'from_bank' => 'البنك المحوَّل منه',
	'from_account' => 'رقم الحساب المحوَّل منه',
	'reference' => 'رقم مرجع العملية',
	'transfer_date' => 'تاريخ التحويل',
	'transfer_time' => 'وقت التحويل',
	'amount' => 'المبلغ المحوَّل',
	'receipt' => 'إيصال التحويل',
	'bank_notes' => 'ملاحظات اختيارية',
	'receipt_help' => 'JPG أو PNG أو WEBP أو PDF — بحد أقصى 5MB.',
	'choose_file' => 'اختر الملف',
	'no_file' => 'لم يتم اختيار ملف',
	'step2_page_title' => 'حجز جلستك في VAVA Living',
	'step2_page_intro' => 'رحلتك نحو التوازن تبدأ من هنا',
	'step3_page_title' => 'اختر طريقة الدفع',
	'step3_page_intro' => 'اختر الطريقة المناسبة لإتمام الدفع وتأكيد الحجز',
	'step4_page_title' => 'مراجعة وتأكيد الحجز',
	'step4_page_intro' => 'راجع جميع بيانات الحجز قبل التأكيد النهائي',
	'continue_review' => 'متابعة إلى المراجعة',
	'final_terms' => 'أوافق على الشروط والأحكام وسياسة الخصوصية وسياسة الحجز.',
	'final_confirm' => 'تأكيد الحجز النهائي',
	'edit' => 'تعديل',
	'receipt_name' => 'ملف الإيصال',
	'payment_status_expected' => 'حالة الدفع المتوقعة',
	'ideal_time' => 'اختر موعدك المثالي',
	'booking_summary' => 'ملخص الحجز',
	'total_duration' => 'المدة الإجمالية',
	'service_kind' => 'نوع الخدمة',
	'details_link' => 'عرض التفاصيل',
	'makkah_time' => 'جميع المواعيد وفق المنطقة الزمنية المحددة في لوحة التحكم',
	'unavailable_note' => 'المواعيد غير المتاحة تكون فقط بسبب حجز قائم أو صيانة أو يوم معطّل من لوحة التحكم.',
	'choose_payment' => 'اختر طريقة الدفع',
	'online_payment_title' => 'دفع إلكتروني (بطاقة مدى / فيزا / ماستركارد)',
	'online_payment_note' => 'دفع فوري وآمن عبر بوابة Paymob',
	'bank_payment_note' => 'حوّل المبلغ إلى حسابنا البنكي',
	'later_payment_note' => 'سيتم إرسال رابط الدفع إلى بريدك الإلكتروني',
	'confirm_pay' => 'أكد الحجز وادفع الآن',
	'confirm_bank' => 'إرسال الحجز لمراجعة التحويل',
	'confirm_later' => 'تأكيد الحجز',
	'payment_terms' => 'بالضغط على الزر أعلاه، فأنت توافق على الشروط والأحكام وسياسة الخصوصية وسياسة الحجز.',
	'map_link' => 'عرض على الخريطة',
	'tax_note' => 'شامل ضريبة القيمة المضافة',
	'encrypted_note' => 'جميع معاملات الدفع مشفرة وآمنة 100%',
	'trust_experience' => 'تجربة راقية ومصممة بعناية',
	'trust_experience_note' => 'لكل تفاصيل راحتك معنا',
	'trust_experts' => 'متخصصون معتمدون',
	'trust_experts_note' => 'فريق مؤهل بخبرة عالية',
	'trust_products' => 'منتجات طبيعية وآمنة',
	'trust_products_note' => 'مختارة بعناية لصحتك',
	'trust_privacy' => 'خصوصيتك تهمنا',
	'trust_privacy_note' => 'حماية بياناتك بأعلى معايير الأمان',
);
$service_location = $service && ! empty( $service['location'] ) ? (string) $service['location'] : 'VAVA Living';
$service_type_value = $service ? trim( (string) ( $service['session_type'] ?? '' ) ) : '';
$service_kind_label = '' !== $service_type_value
	? $service_type_value
	: ( $service && 'package' === (string) ( $service['kind'] ?? '' ) ? ( $is_en ? 'VAVA package' : 'باقة VAVA' ) : ( $is_en ? 'Individual session' : 'جلسة فردية' ) );
$map_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $service_location );
$terms_url = function_exists( 'vava_legal_page_url' ) ? vava_legal_page_url( 'terms', $lang ) : home_url( '/terms-and-conditions/' );
$privacy_url = function_exists( 'vava_legal_page_url' ) ? vava_legal_page_url( 'privacy', $lang ) : home_url( '/privacy-policy/' );
$booking_policy_url = function_exists( 'vava_legal_page_url' ) ? vava_legal_page_url( 'booking', $lang ) : home_url( '/booking-policy/' );
$consent_html = function_exists( 'vava_booking_consent_html' )
	? vava_booking_consent_html( (string) ( $text['consent_text'] ?? '' ), $terms_url, $privacy_url, $booking_policy_url, $lang )
	: esc_html( (string) ( $text['consent_text'] ?? $ui['final_terms'] ) );
$questionnaire_mode = ( $service && function_exists( 'vava_booking_questionnaire_mode_for_service' ) ) ? vava_booking_questionnaire_mode_for_service( $service, $page_id ) : 'none';
$has_questionnaire_stage = in_array( $questionnaire_mode, array( 'beginning', 'midpoint' ), true );
$questionnaire_settings = function_exists( 'vava_booking_questionnaire_settings' ) ? vava_booking_questionnaire_settings( $page_id ) : array();
$questionnaire_title = $has_questionnaire_stage ? (string) ( $questionnaire_settings[ $questionnaire_mode ]['title'][ $lang ] ?? ( $is_en ? 'Journey questionnaire' : 'استبيان الرحلة' ) ) : '';
$questionnaire_intro = $has_questionnaire_stage ? (string) ( $questionnaire_settings[ $questionnaire_mode ]['description'][ $lang ] ?? '' ) : '';
$schedule_step = $has_questionnaire_stage ? 3 : 2;
$payment_step = $has_questionnaire_stage ? 4 : 3;
$review_step = $has_questionnaire_stage ? 5 : 4;
$booking_steps = $has_questionnaire_stage
	? array( (string) $text['steps'][0], $questionnaire_title, (string) $text['steps'][1], (string) $text['steps'][2], (string) $text['steps'][3] )
	: (array) $text['steps'];
$GLOBALS['vava_page_data_name'] = $is_en ? 'booking-en.html' : 'booking.html';
$GLOBALS['vava_active_nav'] = 'paths';
$GLOBALS['vava_internal_body_classes'] = array( 'vava-booking-page' );
get_header( 'page' );
?>
<main class="vava-booking-main" data-booking-root data-current-step="1" data-booking-language="<?php echo esc_attr( $lang ); ?>" dir="<?php echo esc_attr( $is_en ? 'ltr' : 'rtl' ); ?>">
	<section class="vava-booking-hero">
		<div class="container">
			<div class="vava-booking-hero-copy">
				<h1 data-booking-hero-title data-step-1-title="<?php echo esc_attr( (string) $text['title'] ); ?>"<?php if ( $has_questionnaire_stage ) : ?> data-step-2-title="<?php echo esc_attr( $questionnaire_title ); ?>" data-step-3-title="<?php echo esc_attr( $ui['step2_page_title'] ); ?>" data-step-4-title="<?php echo esc_attr( $ui['step3_page_title'] ); ?>" data-step-5-title="<?php echo esc_attr( $text['review_title'] ); ?>"<?php else : ?> data-step-2-title="<?php echo esc_attr( $ui['step2_page_title'] ); ?>" data-step-3-title="<?php echo esc_attr( $ui['step3_page_title'] ); ?>" data-step-4-title="<?php echo esc_attr( $text['review_title'] ); ?>"<?php endif; ?>><?php echo esc_html( (string) $text['title'] ); ?></h1>
				<p data-booking-hero-intro data-step-1-intro="<?php echo esc_attr( (string) $text['intro'] ); ?>"<?php if ( $has_questionnaire_stage ) : ?> data-step-2-intro="<?php echo esc_attr( $questionnaire_intro ); ?>" data-step-3-intro="<?php echo esc_attr( $ui['step2_page_intro'] ); ?>" data-step-4-intro="<?php echo esc_attr( $ui['step3_page_intro'] ); ?>" data-step-5-intro="<?php echo esc_attr( $text['review_intro'] ); ?>"<?php else : ?> data-step-2-intro="<?php echo esc_attr( $ui['step2_page_intro'] ); ?>" data-step-3-intro="<?php echo esc_attr( $ui['step3_page_intro'] ); ?>" data-step-4-intro="<?php echo esc_attr( $text['review_intro'] ); ?>"<?php endif; ?>><?php echo esc_html( (string) $text['intro'] ); ?></p>
			</div>
		</div>
	</section>
	<section class="vava-booking-shell">
		<div class="container">
			<?php if ( 'success' === $status ) : ?>
				<div class="vava-booking-state is-success"><span>✓</span><div><h2><?php echo esc_html( (string) $text['success_title'] ); ?></h2><p><?php echo esc_html( (string) $text['payment_success'] ); ?></p></div></div>
			<?php elseif ( 'pending' === $status ) : ?>
				<div class="vava-booking-state is-pending" data-booking-pending><span>…</span><div><h2><?php echo esc_html( (string) $text['processing'] ); ?></h2><p><?php echo esc_html( (string) $text['payment_pending'] ); ?></p></div></div>
			<?php elseif ( 'failed' === $status ) : ?>
				<div class="vava-booking-state is-error"><span>!</span><div><h2><?php echo esc_html( (string) $text['payment_failed'] ); ?></h2><a href="<?php echo esc_url( remove_query_arg( array( 'booking_status', 'booking' ) ) ); ?>"><?php echo esc_html( (string) $text['back'] ); ?></a></div></div>
			<?php endif; ?>

			<?php if ( ! in_array( $status, array( 'success', 'pending' ), true ) ) : ?>
				<span data-booking-progress-home hidden></span>
				<div class="vava-booking-progress<?php echo count( $booking_steps ) > 4 ? ' is-five-steps' : ' is-four-steps'; ?>" aria-label="<?php echo esc_attr( $is_en ? 'Booking steps' : 'خطوات الحجز' ); ?>">
					<?php foreach ( $booking_steps as $index => $label ) : ?>
						<button type="button" class="vava-booking-progress-step<?php echo 0 === $index ? ' is-active' : ''; ?>" data-progress-step="<?php echo esc_attr( (string) ( $index + 1 ) ); ?>"<?php echo 0 === $index ? '' : ' disabled'; ?>>
							<span><?php echo esc_html( (string) ( $index + 1 ) ); ?></span><strong><?php echo esc_html( (string) $label ); ?></strong>
						</button>
					<?php endforeach; ?>
				</div>

				<?php if ( ! $available ) : ?>
					<div class="vava-booking-state is-error"><span>!</span><div><h2><?php echo esc_html( (string) $text['invalid_service'] ); ?></h2><a class="btn primary" href="<?php echo esc_url( $paths_url ); ?>"><?php echo esc_html( (string) $text['change_service'] ); ?></a></div></div>
				<?php else : ?>
					<form class="vava-booking-form" data-booking-form data-questionnaire-mode="<?php echo esc_attr( $questionnaire_mode ); ?>" data-max-steps="<?php echo esc_attr( (string) $review_step ); ?>" data-schedule-step="<?php echo esc_attr( (string) $schedule_step ); ?>" data-payment-step="<?php echo esc_attr( (string) $payment_step ); ?>" data-review-step="<?php echo esc_attr( (string) $review_step ); ?>" enctype="multipart/form-data" novalidate>
						<input name="service" type="hidden" value="<?php echo esc_attr( (string) $service['uid'] ); ?>"/>
						<input name="lang" type="hidden" value="<?php echo esc_attr( $lang ); ?>"/>
						<div class="vava-booking-workspace">
							<section class="vava-booking-step vava-booking-step--details is-active" data-booking-step="1">
								<header class="vava-booking-panel-heading"><span class="vava-booking-step-icon">1</span><div><h2><?php echo esc_html( (string) $text['steps'][0] ); ?></h2></div></header>
								<div class="vava-booking-service-compact">
									<aside class="vava-booking-service-aside" aria-label="<?php echo esc_attr( $is_en ? 'Selected service summary' : 'ملخص الخدمة المختارة' ); ?>">
										<?php if ( $service['duration'] ) : ?><span><i>◷</i><small><?php echo esc_html( $ui['duration'] ); ?></small><b><?php echo esc_html( (string) $service['duration'] ); ?></b></span><?php endif; ?>
										<?php if ( $service['session_type'] ) : ?><span><i>◇</i><small><?php echo esc_html( $ui['type'] ); ?></small><b><?php echo esc_html( (string) $service['session_type'] ); ?></b></span><?php endif; ?>
										<?php if ( $service['location'] ) : ?><span><i>⌖</i><small><?php echo esc_html( $ui['location'] ); ?></small><b><?php echo esc_html( (string) $service['location'] ); ?></b></span><?php endif; ?>
										<span class="is-price"><i>◇</i><small><?php echo esc_html( $ui['price'] ); ?></small><b><?php echo esc_html( $price_label ); ?></b></span>
									</aside>
									<div class="vava-booking-service-main">
										<?php if ( $image ) : ?><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( (string) $service['title'] ); ?>"/><?php endif; ?>
										<div class="vava-booking-service-copy"><span><?php echo esc_html( (string) $text['selected_service'] ); ?></span><h3><?php echo esc_html( (string) $service['title'] ); ?></h3><p><?php echo esc_html( (string) $service['description'] ); ?></p></div>
									</div>
								</div>
								<div class="vava-booking-section-title"><h3><?php echo esc_html( (string) $text['fields_title'] ); ?></h3></div>
								<div class="vava-booking-fields">
									<?php foreach ( array( 'name', 'email' ) as $key ) : $field = (array) $text['fields'][ $key ]; ?>
										<label><span><?php echo esc_html( (string) $field['label'] ); ?><?php echo ! empty( $field['required'] ) ? ' *' : ''; ?></span><input name="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( (string) $field['placeholder'] ); ?>" type="<?php echo 'email' === $key ? 'email' : 'text'; ?>"<?php echo ! empty( $field['required'] ) ? ' required' : ''; ?>/></label>
									<?php endforeach; ?>
									<fieldset class="vava-booking-whatsapp-group is-full" data-whatsapp-field>
										<legend><?php echo esc_html( (string) $text['fields']['whatsapp']['label'] ); ?><?php echo $whatsapp_required ? ' *' : ''; ?></legend>
										<input data-whatsapp-combined name="whatsapp" type="hidden" value=""/>
										<div class="vava-booking-whatsapp-controls">
											<div class="vava-booking-country-field">
												<span class="vava-booking-field-label"><?php echo esc_html( $whatsapp_country_label ); ?><?php echo $whatsapp_required ? ' *' : ''; ?></span>
												<div class="vava-booking-country-picker" data-country-picker>
													<select class="vava-booking-country-native" data-whatsapp-country name="whatsapp_country" autocomplete="country" tabindex="-1" aria-hidden="true">
														<?php foreach ( $booking_countries as $country ) : $country_name = $is_en ? (string) $country['en'] : (string) $country['ar']; ?>
															<option value="<?php echo esc_attr( (string) $country['iso'] ); ?>" data-dial="<?php echo esc_attr( (string) $country['dial'] ); ?>"<?php selected( $default_country_iso, (string) $country['iso'] ); ?>><?php echo esc_html( $country_name . ' (' . (string) $country['dial'] . ')' ); ?></option>
														<?php endforeach; ?>
													</select>
													<button class="vava-booking-country-trigger" data-country-trigger type="button" aria-haspopup="listbox" aria-expanded="false">
														<img data-country-selected-flag src="<?php echo esc_url( get_theme_file_uri( 'assets/images/country-flags/' . strtolower( $default_country_iso ) . '.png' ) ); ?>" alt=""/>
														<span data-country-selected-name><?php
															foreach ( $booking_countries as $country ) {
																if ( $default_country_iso === (string) $country['iso'] ) {
																	echo esc_html( $is_en ? (string) $country['en'] : (string) $country['ar'] );
																	break;
																}
															}
														?></span>
														<b data-country-selected-dial dir="ltr"><?php echo esc_html( vava_booking_country_dial_code( $default_country_iso ) ); ?></b>
														<i aria-hidden="true">⌄</i>
													</button>
													<div class="vava-booking-country-menu" data-country-menu hidden>
														<label class="vava-booking-country-search"><span class="screen-reader-text"><?php echo esc_html( $is_en ? 'Search countries' : 'البحث في الدول' ); ?></span><input data-country-search type="search" autocomplete="off" placeholder="<?php echo esc_attr( $is_en ? 'Search country or code' : 'ابحث باسم الدولة أو المفتاح' ); ?>"/></label>
														<div class="vava-booking-country-options" data-country-options role="listbox" aria-label="<?php echo esc_attr( $whatsapp_country_label ); ?>">
															<?php foreach ( $booking_countries as $country ) : $country_name = $is_en ? (string) $country['en'] : (string) $country['ar']; $country_iso = strtolower( (string) $country['iso'] ); ?>
																<button type="button" role="option" data-country-option data-iso="<?php echo esc_attr( (string) $country['iso'] ); ?>" data-dial="<?php echo esc_attr( (string) $country['dial'] ); ?>" data-search="<?php echo esc_attr( strtolower( $country_name . ' ' . (string) $country['iso'] . ' ' . (string) $country['dial'] ) ); ?>" aria-selected="<?php echo $default_country_iso === (string) $country['iso'] ? 'true' : 'false'; ?>">
																	<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/country-flags/' . $country_iso . '.png' ) ); ?>" alt=""/>
																	<span><?php echo esc_html( $country_name ); ?></span>
																	<b dir="ltr"><?php echo esc_html( (string) $country['dial'] ); ?></b>
																</button>
															<?php endforeach; ?>
														</div>
													</div>
												</div>
											</div>
											<label class="vava-booking-phone-field"><span><?php echo esc_html( $whatsapp_number_label ); ?><?php echo $whatsapp_required ? ' *' : ''; ?></span><input data-whatsapp-local name="whatsapp_local" type="tel" inputmode="numeric" autocomplete="tel-national" maxlength="15" placeholder="<?php echo esc_attr( $whatsapp_number_placeholder ); ?>"<?php echo $whatsapp_required ? ' required' : ''; ?>/></label>
										</div>
										<small class="vava-booking-whatsapp-help"><?php echo esc_html( $whatsapp_help ); ?></small>
									</fieldset>
									<?php $field = (array) $text['fields']['previous']; ?>
									<fieldset class="vava-booking-choice-toggle" data-vava-choice-toggle>
										<legend><?php echo esc_html( (string) $field['label'] ); ?><?php echo ! empty( $field['required'] ) ? ' *' : ''; ?></legend>
										<div role="radiogroup" aria-label="<?php echo esc_attr( (string) $field['label'] ); ?>">
											<label><input type="radio" name="previous" value="yes"<?php echo ! empty( $field['required'] ) ? ' required' : ''; ?>/><span><?php echo esc_html( (string) $field['yes'] ); ?></span></label>
											<label><input type="radio" name="previous" value="no"<?php echo ! empty( $field['required'] ) ? ' required' : ''; ?>/><span><?php echo esc_html( (string) $field['no'] ); ?></span></label>
										</div>
									</fieldset>
									<?php $field = (array) $text['fields']['notes']; ?>
									<label class="is-full"><span><?php echo esc_html( (string) $field['label'] ); ?></span><textarea name="notes" placeholder="<?php echo esc_attr( (string) $field['placeholder'] ); ?>" rows="4"<?php echo ! empty( $field['required'] ) ? ' required' : ''; ?>></textarea></label>
								</div>
								<p class="vava-booking-privacy">⌕ <?php echo esc_html( $ui['privacy'] ); ?></p>
								<div class="vava-booking-actions"><button class="btn primary" data-next-step="<?php echo esc_attr( (string) ( $has_questionnaire_stage ? 2 : $schedule_step ) ); ?>" data-step-one-next type="button"><?php echo esc_html( (string) $text['continue'] ); ?> <span>←</span></button></div>
							</section>

						<?php if ( $has_questionnaire_stage && function_exists( 'vava_booking_questionnaire_render_frontend' ) ) { vava_booking_questionnaire_render_frontend( $questionnaire_mode, $lang ); } ?>

						<section class="vava-booking-step vava-booking-step--stage vava-booking-step--schedule is-locked" data-booking-step="<?php echo esc_attr( (string) $schedule_step ); ?>" hidden>
							<div class="vava-booking-stage-layout vava-booking-stage-layout--schedule">
								<div class="vava-booking-stage-card vava-booking-calendar-card">
									<header class="vava-booking-card-title"><h2><?php echo esc_html( $ui['ideal_time'] ); ?></h2></header>
									<div class="vava-booking-calendar-toolbar" aria-label="<?php echo esc_attr( $is_en ? 'Appointment date navigation' : 'التنقل بين تواريخ المواعيد' ); ?>">
										<button class="vava-booking-month-nav is-prev" type="button" data-booking-date-prev aria-label="<?php echo esc_attr( $is_en ? 'Previous dates' : 'التواريخ السابقة' ); ?>"><span aria-hidden="true">‹</span></button>
										<strong data-booking-month></strong>
										<button class="vava-booking-month-nav is-next" type="button" data-booking-date-next aria-label="<?php echo esc_attr( $is_en ? 'Next dates' : 'التواريخ التالية' ); ?>"><span aria-hidden="true">›</span></button>
									</div>
									<div class="vava-booking-date-strip">
										<div class="vava-booking-dates" data-booking-dates></div>
									</div>
									<p class="vava-booking-timezone-note"><span aria-hidden="true">◷</span><?php echo esc_html( $ui['makkah_time'] ); ?>: <b><?php echo esc_html( (string) $shared['timezone'] ); ?></b></p>
									<div class="vava-booking-times" data-booking-times><p class="vava-booking-empty"><?php echo esc_html( (string) $text['no_slots'] ); ?></p></div>
									<p class="vava-booking-availability-note"><span aria-hidden="true">ⓘ</span><?php echo esc_html( $ui['unavailable_note'] ); ?></p>
									<div class="vava-booking-stage-actions">
										<button class="vava-booking-stage-button is-back" data-prev-step="1" type="button"><span aria-hidden="true">→</span><?php echo esc_html( (string) $text['back'] ); ?></button>
										<button class="vava-booking-stage-button is-primary" data-next-step="<?php echo esc_attr( (string) $payment_step ); ?>" data-next-schedule type="button" disabled><?php echo esc_html( (string) $text['continue_payment'] ); ?><span aria-hidden="true">←</span></button>
									</div>
								</div>

								<aside class="vava-booking-stage-card vava-booking-summary-card vava-booking-summary-card--schedule">
									<header class="vava-booking-summary-header"><span aria-hidden="true">▣</span><h2><?php echo esc_html( $ui['booking_summary'] ); ?></h2></header>
									<div class="vava-booking-summary-service">
										<?php if ( $image ) : ?><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( (string) $service['title'] ); ?>"/><?php else : ?><div class="vava-booking-summary-image-placeholder">VAVA</div><?php endif; ?>
										<div><h3><?php echo esc_html( (string) $service['title'] ); ?></h3><p><?php echo esc_html( $service_kind_label ); ?></p><p><span aria-hidden="true">◷</span><?php echo esc_html( $duration_label ); ?></p><a href="<?php echo esc_url( $service_detail_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $ui['details_link'] ); ?></a></div>
									</div>
									<dl class="vava-booking-summary-facts">
										<div><dt><span aria-hidden="true">⌖</span><?php echo esc_html( $ui['location'] ); ?></dt><dd><?php echo esc_html( $service_location ); ?></dd></div>
									</dl>
									<div class="vava-booking-summary-total"><span><?php echo esc_html( $ui['total'] ); ?><small><?php echo esc_html( $ui['tax_note'] ); ?></small></span><strong><?php echo esc_html( $price_label ); ?></strong></div>
									<div class="vava-booking-summary-assurances">
										<span><i aria-hidden="true">♧</i><b><?php echo esc_html( $ui['care'] ); ?></b><small><?php echo esc_html( $is_en ? 'Designed around your journey' : 'مصممة لرحلتك' ); ?></small></span>
										<span><i aria-hidden="true">▣</i><b><?php echo esc_html( $ui['trusted'] ); ?></b><small><?php echo esc_html( $is_en ? 'Your data is protected' : 'بياناتك محمية' ); ?></small></span>
										<span><i aria-hidden="true">◇</i><b><?php echo esc_html( $is_en ? 'Transparent pricing' : 'أسعار شفافة' ); ?></b><small><?php echo esc_html( $is_en ? 'No hidden fees' : 'بدون رسوم مخفية' ); ?></small></span>
									</div>
								</aside>
							</div>
						</section>

						<section class="vava-booking-step vava-booking-step--stage vava-booking-step--payment is-locked" data-booking-step="<?php echo esc_attr( (string) $payment_step ); ?>" hidden>
							<header class="vava-booking-stage-intro"><h2><?php echo esc_html( $ui['step3_page_title'] ); ?></h2><p><?php echo esc_html( $ui['step3_page_intro'] ); ?></p></header>
							<div class="vava-booking-progress-slot" data-booking-progress-slot="<?php echo esc_attr( (string) $payment_step ); ?>"></div>
							<div class="vava-booking-stage-card vava-booking-payment-card vava-booking-payment-card--wide">
								<header class="vava-booking-card-title vava-booking-payment-card-title"><h2><?php echo esc_html( $is_en ? 'Available payment methods' : 'طرق الدفع المتاحة' ); ?></h2></header>
								<div class="vava-booking-payment-options">
									<?php if ( $is_free_service ) : ?>
									<label class="vava-booking-payment-choice is-free is-selected"><input checked name="payment_method" type="radio" value="free"/><span class="vava-booking-radio"></span><span class="vava-booking-payment-icon">✓</span><span class="vava-booking-payment-copy"><strong><?php echo esc_html( $is_en ? 'Free session' : 'جلسة مجانية' ); ?></strong><small><?php echo esc_html( $is_en ? 'No payment is required for this booking.' : 'لا يتطلب هذا الحجز أي عملية دفع.' ); ?></small></span></label>
									<?php else : ?>
									<?php if ( $bank_enabled ) : ?><label class="vava-booking-payment-choice is-bank<?php echo $bank_ready ? '' : ' is-disabled'; ?>"><input <?php checked( 'bank', $default_payment ); ?> name="payment_method" type="radio" value="bank" <?php disabled( ! $bank_ready ); ?>/><span class="vava-booking-radio"></span><span class="vava-booking-payment-icon">▥</span><span class="vava-booking-payment-copy"><strong><?php echo esc_html( (string) $text['bank_label'] ); ?></strong><small><?php echo esc_html( $bank_ready ? $ui['bank_payment_note'] : ( $is_en ? 'Bank account details are not configured yet.' : 'بيانات الحساب البنكي غير مكتملة حاليًا.' ) ); ?></small></span></label><?php endif; ?>
									<?php if ( ! empty( $shared['payment_methods']['cash'] ) ) : ?><label class="vava-booking-payment-choice is-cash"><input <?php checked( 'cash', $default_payment ); ?> name="payment_method" type="radio" value="cash"/><span class="vava-booking-radio"></span><span class="vava-booking-payment-icon">◷</span><span class="vava-booking-payment-copy"><strong><?php echo esc_html( (string) $text['cash_label'] ); ?></strong><small><?php echo esc_html( $ui['later_payment_note'] ); ?></small></span></label><?php endif; ?>
									<?php if ( $paymob_enabled ) : ?><label class="vava-booking-payment-choice is-paymob<?php echo $paymob_ready ? '' : ' is-disabled'; ?>"><input <?php checked( 'paymob', $default_payment ); ?> name="payment_method" type="radio" value="paymob" <?php disabled( ! $paymob_ready ); ?>/><span class="vava-booking-radio"></span><span class="vava-booking-payment-icon is-paymob">P</span><span class="vava-booking-payment-copy"><strong><?php echo esc_html( $ui['online_payment_title'] ); ?></strong><small><?php echo esc_html( $paymob_ready ? $ui['online_payment_note'] : $ui['online_unavailable'] ); ?></small></span><b class="vava-paymob-wordmark">Paymob</b></label><?php endif; ?>
									<?php if ( ! $has_payment_method ) : ?><p class="vava-booking-no-payment"><?php echo esc_html( (string) $text['validation_error'] ); ?></p><?php endif; ?>
									<?php endif; ?>
								</div>

								<?php if ( $bank_ready && ! $is_free_service ) : ?>
									<section class="vava-booking-bank-transfer" data-bank-transfer-panel hidden>
										<div class="vava-booking-payment-overview">
											<aside class="vava-booking-payment-summary">
												<header><span aria-hidden="true">▤</span><h3><?php echo esc_html( $is_en ? 'Payment summary' : 'ملخص الدفع' ); ?></h3></header>
												<dl>
													<div><dt><?php echo esc_html( $is_en ? 'Service' : 'الخدمة' ); ?></dt><dd><?php echo esc_html( (string) $service['title'] ); ?></dd></div>
													<div><dt><?php echo esc_html( $is_en ? 'Date' : 'التاريخ' ); ?></dt><dd data-summary-date><?php echo esc_html( $ui['not_selected'] ); ?></dd></div>
													<div><dt><?php echo esc_html( $is_en ? 'Time' : 'الوقت' ); ?></dt><dd data-summary-time></dd></div>
													<div><dt><?php echo esc_html( $ui['duration'] ); ?></dt><dd><?php echo esc_html( $duration_label ); ?></dd></div>
												</dl>
												<div class="vava-booking-payment-summary-total"><span><?php echo esc_html( $ui['total'] ); ?></span><strong><?php echo esc_html( $price_label ); ?></strong><small><?php echo esc_html( $ui['tax_note'] ); ?></small></div>
											</aside>

											<div class="vava-booking-bank-account">
												<div class="vava-booking-bank-heading"><span class="vava-booking-bank-heading-icon" aria-hidden="true">▥</span><div><h3><?php echo esc_html( $ui['bank_account_title'] ); ?></h3><p><?php echo esc_html( (string) ( $bank_config[ $is_en ? 'instructions_en' : 'instructions_ar' ] ?? '' ) ); ?></p></div></div>
												<dl>
													<div class="is-bank-name"><span class="vava-booking-bank-detail-icon" aria-hidden="true">▥</span><dt><?php echo esc_html( $ui['bank_name'] ); ?></dt><dd><?php echo esc_html( (string) $bank_config['bank_name'] ); ?></dd></div>
													<div class="is-beneficiary"><span class="vava-booking-bank-detail-icon" aria-hidden="true">♙</span><dt><?php echo esc_html( $ui['beneficiary'] ); ?></dt><dd><?php echo esc_html( (string) $bank_config['beneficiary_name'] ); ?></dd></div>
													<?php if ( ! empty( $bank_config['account_number'] ) ) : ?><div class="is-account"><span class="vava-booking-bank-detail-icon" aria-hidden="true">▤</span><dt><?php echo esc_html( $ui['account_number'] ); ?></dt><dd dir="ltr"><?php echo esc_html( (string) $bank_config['account_number'] ); ?></dd></div><?php endif; ?>
													<div class="is-iban"><span class="vava-booking-bank-detail-icon" aria-hidden="true">▧</span><dt><?php echo esc_html( $ui['iban'] ); ?></dt><dd dir="ltr"><?php echo esc_html( (string) $bank_config['iban'] ); ?></dd></div>
													<?php if ( ! empty( $bank_config['swift'] ) ) : ?><div class="is-swift"><span class="vava-booking-bank-detail-icon" aria-hidden="true">◎</span><dt><?php echo esc_html( $ui['swift'] ); ?></dt><dd dir="ltr"><?php echo esc_html( (string) $bank_config['swift'] ); ?></dd></div><?php endif; ?>
												</dl>
												<p class="vava-booking-bank-hint"><span aria-hidden="true">ⓘ</span><?php echo esc_html( $is_en ? 'Transfer the amount to the account above, then complete the transfer details and attach the receipt.' : 'يرجى تحويل المبلغ إلى الحساب أعلاه، ثم استكمال بيانات التحويل وإرفاق الإيصال.' ); ?></p>
											</div>
										</div>

										<div class="vava-booking-bank-form">
											<div class="vava-booking-bank-heading"><span class="vava-booking-bank-heading-icon" aria-hidden="true">▤</span><div><h3><?php echo esc_html( $ui['bank_form_title'] ); ?></h3><p><?php echo esc_html( $is_en ? 'Complete the following details after transferring the amount.' : 'يرجى تعبئة البيانات التالية بعد تحويل المبلغ.' ); ?></p></div></div>
											<div class="vava-booking-bank-fields">
												<label><span><?php echo esc_html( $ui['transfer_name'] ); ?> *</span><input data-bank-required name="bank_transfer_name" type="text"/></label>
												<label><span><?php echo esc_html( $ui['from_bank'] ); ?> *</span><input data-bank-required name="bank_from_bank" type="text"/></label>
												<label><span><?php echo esc_html( $ui['from_account'] ); ?> *</span><input data-bank-required dir="ltr" name="bank_from_account" type="text"/></label>
												<label><span><?php echo esc_html( $ui['amount'] ); ?> *</span><div class="vava-booking-amount-input"><input data-bank-required min="0" name="bank_amount" step="0.01" type="number"/><b><?php echo esc_html( (string) ( $bank_config['currency'] ?? $service['currency'] ) ); ?></b></div></label>
												<label><span><?php echo esc_html( $ui['transfer_date'] ); ?> *</span><input data-bank-required name="bank_transfer_date" type="date"/></label>
												<label><span><?php echo esc_html( $ui['transfer_time'] ); ?> *</span><input data-bank-required name="bank_transfer_time" type="time"/></label>
												<label><span><?php echo esc_html( $ui['reference'] ); ?> *</span><input data-bank-required name="bank_reference" type="text"/></label>
												<label class="vava-booking-receipt"><span><?php echo esc_html( $ui['receipt'] ); ?> *</span><span class="vava-booking-file-control"><input accept=".jpg,.jpeg,.png,.webp,.pdf" data-bank-required name="bank_receipt" type="file"/><span class="vava-booking-file-trigger"><i aria-hidden="true">↥</i><?php echo esc_html( $ui['choose_file'] ); ?></span><span class="vava-booking-file-name" data-bank-receipt-name><?php echo esc_html( $ui['no_file'] ); ?></span></span><small><?php echo esc_html( $ui['receipt_help'] ); ?></small><span class="vava-booking-upload-progress" data-booking-upload-progress hidden aria-live="polite"><span><b data-booking-upload-label><?php echo esc_html( $is_en ? 'Ready to upload' : 'جاهز للرفع' ); ?></b><em data-booking-upload-percent>0%</em></span><i><b data-booking-upload-bar></b></i><small data-booking-upload-meta></small></span></label>
											</div>
										</div>
									</section>
								<?php endif; ?>
								<div class="vava-booking-error" data-booking-error role="alert"></div>
								<div class="vava-booking-payment-footer"><button class="vava-booking-stage-button is-back" data-prev-step="<?php echo esc_attr( (string) $schedule_step ); ?>" type="button"><span aria-hidden="true">→</span><?php echo esc_html( (string) $text['back'] ); ?></button><p><span aria-hidden="true">▢</span><?php echo esc_html( $is_en ? 'Your data is secure and is never shared with third parties.' : 'بياناتك آمنة ولن تتم مشاركتها مع أي جهة خارجية.' ); ?></p><button class="vava-booking-stage-button is-primary" data-next-step="<?php echo esc_attr( (string) $review_step ); ?>" data-next-payment type="button"<?php echo $has_payment_method ? '' : ' disabled'; ?>><?php echo esc_html( $ui['continue_review'] ); ?><span aria-hidden="true">←</span></button></div>
							</div>
						</section>

						<section class="vava-booking-step vava-booking-step--stage vava-booking-step--review is-locked" data-booking-step="<?php echo esc_attr( (string) $review_step ); ?>" hidden>
							<header class="vava-booking-stage-intro"><h2><?php echo esc_html( $text['review_title'] ); ?></h2><p><?php echo esc_html( $text['review_intro'] ); ?></p></header>
							<div class="vava-booking-progress-slot" data-booking-progress-slot="<?php echo esc_attr( (string) $review_step ); ?>"></div>
							<div class="vava-booking-review-layout">
								<section class="vava-booking-stage-card vava-booking-final-card is-customer">
									<header><span class="vava-booking-final-icon" aria-hidden="true">♙</span><h3><?php echo esc_html( $is_en ? 'Customer details' : 'تفاصيل العميل' ); ?></h3></header>
									<dl><div><dt><?php echo esc_html( $is_en ? 'Full name' : 'الاسم الكامل' ); ?></dt><dd data-summary-name><?php echo esc_html( $ui['not_selected'] ); ?></dd></div><div><dt><?php echo esc_html( $is_en ? 'Email' : 'البريد الإلكتروني' ); ?></dt><dd data-summary-email dir="ltr"></dd></div><div><dt><?php echo esc_html( $is_en ? 'Mobile number' : 'رقم الجوال' ); ?></dt><dd data-summary-whatsapp dir="ltr"></dd></div><div><dt><?php echo esc_html( $is_en ? 'Previous VAVA experience' : 'تجربة VAVA السابقة' ); ?></dt><dd data-review-previous></dd></div><div><dt><?php echo esc_html( $is_en ? 'Notes' : 'الملاحظات' ); ?></dt><dd data-review-notes></dd></div></dl>
								</section>
								<?php if ( $has_questionnaire_stage ) : ?><section class="vava-booking-stage-card vava-booking-final-card is-questionnaire" data-review-questionnaire-card hidden><header><span class="vava-booking-final-icon" aria-hidden="true">▤</span><h3><?php echo esc_html( 'en' === $lang ? 'Questionnaire summary' : 'ملخص إجابات الاستبيان' ); ?></h3></header><dl data-review-questionnaire-summary></dl></section><?php endif; ?>
								<section class="vava-booking-stage-card vava-booking-final-card is-service">
									<header><span class="vava-booking-final-icon" aria-hidden="true">▣</span><h3><?php echo esc_html( $is_en ? 'Service and appointment details' : 'تفاصيل الخدمة والموعد' ); ?></h3></header>
									<dl><div><dt><?php echo esc_html( (string) $text['summary_service'] ); ?></dt><dd><?php echo esc_html( (string) $service['title'] ); ?></dd></div><div><dt><?php echo esc_html( $ui['service_kind'] ); ?></dt><dd><?php echo esc_html( $service_kind_label ); ?></dd></div><div><dt><?php echo esc_html( (string) $text['summary_appointment'] ); ?></dt><dd><b data-summary-date><?php echo esc_html( $ui['not_selected'] ); ?></b><small data-summary-time></small></dd></div><div><dt><?php echo esc_html( $ui['location'] ); ?></dt><dd><?php echo esc_html( $service_location ); ?></dd></div><div><dt><?php echo esc_html( $ui['duration'] ); ?></dt><dd><?php echo esc_html( $duration_label ); ?></dd></div></dl>
								</section>
								<section class="vava-booking-stage-card vava-booking-final-card is-payment">
									<header><span class="vava-booking-final-icon" aria-hidden="true">▤</span><h3><?php echo esc_html( $is_en ? 'Payment details' : 'تفاصيل الدفع' ); ?></h3></header>
									<dl><div><dt><?php echo esc_html( $is_en ? 'Payment method' : 'طريقة الدفع' ); ?></dt><dd data-review-payment-method></dd></div><div><dt><?php echo esc_html( $ui['payment_status_expected'] ); ?></dt><dd><span class="vava-booking-review-status" data-review-payment-status></span></dd></div><div><dt><?php echo esc_html( $ui['total'] ); ?></dt><dd><?php echo esc_html( $price_label ); ?></dd></div></dl>
								</section>
								<section class="vava-booking-stage-card vava-booking-final-card is-transfer" data-review-bank hidden>
									<header><span class="vava-booking-final-icon" aria-hidden="true">▥</span><h3><?php echo esc_html( $is_en ? 'Transfer details' : 'تفاصيل التحويل' ); ?></h3></header>
									<dl><div><dt><?php echo esc_html( $ui['transfer_name'] ); ?></dt><dd data-review-bank-name></dd></div><div><dt><?php echo esc_html( $ui['from_bank'] ); ?></dt><dd data-review-bank-from></dd></div><div><dt><?php echo esc_html( $ui['from_account'] ); ?></dt><dd data-review-bank-account dir="ltr"></dd></div><div><dt><?php echo esc_html( $ui['amount'] ); ?></dt><dd data-review-bank-amount></dd></div><div><dt><?php echo esc_html( $ui['transfer_date'] ); ?></dt><dd data-review-bank-date dir="ltr"></dd></div><div><dt><?php echo esc_html( $ui['transfer_time'] ); ?></dt><dd data-review-bank-time dir="ltr"></dd></div><div><dt><?php echo esc_html( $ui['reference'] ); ?></dt><dd data-review-bank-reference></dd></div><div><dt><?php echo esc_html( $ui['receipt_name'] ); ?></dt><dd class="vava-booking-review-file" data-review-bank-receipt></dd></div></dl>
								</section>
							</div>
							<div class="vava-booking-review-footer">
								<label class="vava-booking-consent vava-booking-final-consent"><input name="terms" type="checkbox" value="1" required/><span><?php echo $consent_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><small><?php echo esc_html( $is_en ? 'Your information is secure and is not shared with third parties.' : 'معلوماتك آمنة تمامًا ولن تتم مشاركتها مع أي طرف ثالث.' ); ?></small></label>
								<section class="vava-booking-review-total-card"><span><?php echo esc_html( $is_en ? 'Final total' : 'الإجمالي النهائي' ); ?></span><strong><?php echo esc_html( $price_label ); ?></strong><small><?php echo esc_html( $is_en ? 'Includes all applicable fees.' : 'شامل جميع الضرائب والرسوم المطبقة.' ); ?></small></section>
								<div class="vava-booking-final-actions"><button class="vava-booking-confirm-button" data-booking-submit data-label-paymob="<?php echo esc_attr( $ui['confirm_pay'] ); ?>" data-label-bank="<?php echo esc_attr( $ui['confirm_bank'] ); ?>" data-label-cash="<?php echo esc_attr( $text['final_confirm'] ); ?>" data-label-free="<?php echo esc_attr( $is_en ? 'Confirm free booking' : 'تأكيد الحجز المجاني' ); ?>" type="submit"<?php echo $has_payment_method ? '' : ' disabled'; ?>><span aria-hidden="true">✓</span><b data-submit-label><?php echo esc_html( $text['final_confirm'] ); ?></b></button><div class="vava-booking-final-secondary-actions"><a class="vava-booking-stage-button is-back is-paths" href="<?php echo esc_url( $paths_url ); ?>"><span aria-hidden="true"><?php echo esc_html( $is_en ? '←' : '→' ); ?></span><?php echo esc_html( (string) $text['change_service'] ); ?></a><button class="vava-booking-stage-button is-back" data-prev-step="<?php echo esc_attr( (string) $payment_step ); ?>" type="button"><span aria-hidden="true">→</span><?php echo esc_html( $is_en ? 'Previous step' : 'رجوع للمرحلة السابقة' ); ?></button></div></div>
							</div>
							<div class="vava-booking-error" data-booking-final-error role="alert"></div>
						</section>
						</div>
					</form>
					<aside class="vava-booking-success-toast" data-booking-success dir="<?php echo esc_attr( $is_en ? 'ltr' : 'rtl' ); ?>" role="status" aria-live="polite" aria-atomic="true" hidden>
						<div class="vava-booking-success-toast-wave" aria-hidden="true"></div>
						<button class="vava-booking-success-toast-close" data-booking-success-close type="button" aria-label="<?php echo esc_attr( $is_en ? 'Close success message' : 'إغلاق رسالة النجاح' ); ?>">×</button>
						<div class="vava-booking-success-toast-check" aria-hidden="true"><svg viewBox="0 0 64 64"><path d="M16 33.5 27 44l22-25"/></svg></div>
						<div class="vava-booking-success-toast-content">
							<strong data-success-title><?php echo esc_html( (string) $text['success_title'] ); ?></strong>
							<span data-success-message><?php echo esc_html( (string) $text['success_message'] ); ?></span>
							<dl class="vava-booking-success-meta"><div><dt><?php echo esc_html( $is_en ? 'Booking number' : 'رقم الحجز' ); ?></dt><dd data-success-booking-number>—</dd></div><div><dt><?php echo esc_html( $is_en ? 'Status' : 'الحالة' ); ?></dt><dd data-success-booking-status>—</dd></div></dl>
							<a class="vava-booking-success-toast-view" data-success-my-bookings href="<?php echo esc_url( $my_bookings_url ); ?>"><span><?php echo esc_html( $is_en ? 'Track my bookings' : 'متابعة حجوزاتي' ); ?></span><i class="vava-booking-success-toast-arrow" aria-hidden="true">←</i></a>
						</div>
					</aside>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php get_footer( 'page' ); ?>

<?php /* VAVA_BOOKING_PAYMENT_STAGE_APPROVED_LAYOUT_V1R13 */ ?>

<?php /* VAVA_BOOKING_RECEIPT_PORTAL_I18N_V1R16 */ ?>
