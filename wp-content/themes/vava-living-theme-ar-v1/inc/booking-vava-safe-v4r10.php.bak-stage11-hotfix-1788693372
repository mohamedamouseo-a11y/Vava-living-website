<?php
// VAVA_BOOKING_POLICY_CONSENT_V1
// VAVA_BOOKING_DISPLAY_TIME_12H_V1R16
// VAVA_BOOKING_REVIEW_FOOTER_PREVIEW_V1R8
// VAVA_BOOKING_REVIEW_SETTINGS_PREVIEW_V1R7
// VAVA_BOOKING_ADMIN_COLUMNS_WIZARD_REVIEW_V1R5
// VAVA_BOOKING_ADMIN_REDESIGN_DRAWER_NONCE_FIX_B1_B2_V1R3
/**
 * VAVA booking wizard, availability and Paymob-ready checkout.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

function vava_booking_template_slug(): string {
	return 'page-templates/booking-vava.php';
}

function vava_booking_is_page( int $post_id ): bool {
	return $post_id > 0 && vava_booking_template_slug() === get_page_template_slug( $post_id );
}

function vava_booking_page_id(): int {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => vava_booking_template_slug(),
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	return isset( $pages[0] ) ? absint( $pages[0] ) : 0;
}

/** Return the bundled country list used by the WhatsApp country selector. */
function vava_booking_country_calling_codes(): array {
	static $countries = null;
	if ( is_array( $countries ) ) { return $countries; }
	$path = get_theme_file_path( 'assets/data/country-calling-codes.json' );
	$decoded = is_readable( $path ) ? json_decode( (string) file_get_contents( $path ), true ) : array();
	$countries = array();
	foreach ( is_array( $decoded ) ? $decoded : array() as $country ) {
		$iso = strtoupper( sanitize_key( (string) ( $country['iso'] ?? '' ) ) );
		$dial = '+' . preg_replace( '/\D+/', '', (string) ( $country['dial'] ?? '' ) );
		if ( 2 !== strlen( $iso ) || '+' === $dial ) { continue; }
		$countries[] = array(
			'iso'  => $iso,
			'dial' => $dial,
			'flag' => sanitize_text_field( (string) ( $country['flag'] ?? '' ) ),
			'ar'   => sanitize_text_field( (string) ( $country['ar'] ?? $iso ) ),
			'en'   => sanitize_text_field( (string) ( $country['en'] ?? $iso ) ),
		);
	}
	return $countries;
}

function vava_booking_country_dial_code( string $iso ): string {
	$iso = strtoupper( sanitize_key( $iso ) );
	foreach ( vava_booking_country_calling_codes() as $country ) {
		if ( $iso === (string) $country['iso'] ) { return (string) $country['dial']; }
	}
	return '';
}

function vava_booking_phone_digits( string $value ): string {
	$value = strtr( $value, array(
		'٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
		'۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
	) );
	return (string) preg_replace( '/\D+/', '', $value );
}

/** Build and validate a normalized E.164-like WhatsApp number. */
function vava_booking_normalize_whatsapp( string $country_iso, string $local_number, string $fallback = '' ): string {
	$dial_digits = vava_booking_phone_digits( vava_booking_country_dial_code( $country_iso ) );
	$local_digits = vava_booking_phone_digits( $local_number );
	if ( 0 === strpos( $local_digits, '00' ) ) { $local_digits = substr( $local_digits, 2 ); }
	if ( $dial_digits && 0 === strpos( $local_digits, $dial_digits ) ) {
		$local_digits = substr( $local_digits, strlen( $dial_digits ) );
	}
	$local_digits = ltrim( $local_digits, '0' );
	$full = $dial_digits . $local_digits;
	if ( '' === $full && '' !== $fallback ) { $full = vava_booking_phone_digits( $fallback ); }
	$length = strlen( $full );
	return $length >= 7 && $length <= 15 ? '+' . $full : '';
}

function vava_booking_text_defaults( string $lang ): array {
	if ( 'en' === $lang ) {
		return array(
			'eyebrow' => 'A calm, clear booking experience',
			'title' => 'Book your VAVA session',
			'intro' => 'Complete the four steps below. Your selected service stays connected to the VAVA Paths page.',
			'steps' => array( 'Service & details', 'Choose a time', 'Payment method', 'Review & confirm' ),
			'selected_service' => 'Selected service',
			'change_service' => 'Back to VAVA Paths',
			'fields_title' => 'Your details',
			'fields' => array(
				'name' => array( 'label' => 'Full name', 'placeholder' => 'Write your full name', 'required' => 1 ),
				'email' => array( 'label' => 'Email', 'placeholder' => 'name@example.com', 'required' => 1 ),
				'whatsapp' => array( 'label' => 'WhatsApp number', 'placeholder' => 'Country code + number', 'required' => 1 ),
				'previous' => array( 'label' => 'Have you tried VAVA before?', 'placeholder' => 'Choose an option', 'required' => 0, 'yes' => 'Yes', 'no' => 'No' ),
				'notes' => array( 'label' => 'What would you like support with?', 'placeholder' => 'Share any helpful context', 'required' => 0 ),
			),
			'continue' => 'Choose appointment',
			'appointment_title' => 'Choose your appointment',
			'appointment_intro' => 'Available times follow the fixed working days and hours configured by VAVA.',
			'choose_date' => 'Choose a day',
			'choose_time' => 'Choose a time',
			'no_slots' => 'No available times on this day. Please choose another date.',
			'back' => 'Back',
			'continue_payment' => 'Review and pay',
			'confirm_title' => 'Confirm your booking',
			'summary_service' => 'Service',
			'summary_customer' => 'Your details',
			'summary_appointment' => 'Appointment',
			'payment_title' => 'Payment method',
			'paymob_label' => 'Pay securely online',
			'paymob_note' => 'Secure card payment through Paymob.',
			'bank_label' => 'Bank transfer',
			'bank_note' => 'Your booking remains pending until the transfer is reviewed.',
			'bank_received_title' => 'Your bank transfer was received',
			'bank_received_message' => 'Your booking is waiting for the VAVA team to review the transfer receipt.',
			'cash_label' => 'Pay later',
			'cash_note' => 'Available only when enabled by VAVA.',
			'submit' => 'Confirm booking',
			'processing' => 'Creating your booking…',
				'review_title' => 'Review and confirm your booking',
				'review_intro' => 'Check every detail before creating the booking.',
				'final_confirm' => 'Final booking confirmation',
				'edit' => 'Edit',
			'success_title' => 'Your booking has been received',
			'success_message' => 'We saved your appointment and will send the booking details to you.',
			'payment_success' => 'Payment completed successfully. Your booking is confirmed.',
			'payment_failed' => 'Payment was not completed. You can return and try again.',
			'payment_pending' => 'Your payment is still being verified. Refresh this page shortly.',
			'invalid_service' => 'This service is unavailable or no longer accepts bookings.',
			'validation_error' => 'Please complete the required fields before continuing.',
			'slot_unavailable' => 'This appointment is no longer available. Please select another time.',
			'preparation_title' => 'Before your session',
			'preparation_message' => 'Complete the preparation form before your appointment.',
			'preparation_url' => '',
			'consent_text' => 'I agree to the Terms & Conditions, Privacy Policy, and Booking Policy.',
		);
	}
	return array(
		'eyebrow' => 'تجربة حجز هادئة وواضحة',
		'title' => 'حجز جلسة مع VAVA',
		'intro' => 'أربع خطوات واضحة لإتمام الحجز، مع بقاء الخدمة المختارة مرتبطة مباشرة بصفحة مسارات VAVA.',
		'steps' => array( 'الخدمة وبياناتك', 'اختيار الموعد', 'طريقة الدفع', 'مراجعة وتأكيد الحجز' ),
		'selected_service' => 'الخدمة المختارة',
		'change_service' => 'العودة إلى مسارات VAVA',
		'fields_title' => 'بيانات الحجز',
		'fields' => array(
			'name' => array( 'label' => 'الاسم الكامل', 'placeholder' => 'الاسم الكامل', 'required' => 1 ),
			'email' => array( 'label' => 'البريد الإلكتروني', 'placeholder' => 'name@example.com', 'required' => 1 ),
			'whatsapp' => array( 'label' => 'رقم WhatsApp', 'placeholder' => 'مفتاح الدولة + الرقم', 'required' => 1 ),
			'previous' => array( 'label' => 'هل سبق لك تجربة VAVA؟', 'placeholder' => 'تحديد خيار', 'required' => 0, 'yes' => 'نعم', 'no' => 'لا' ),
			'notes' => array( 'label' => 'الاحتياج أو الملاحظات', 'placeholder' => 'أي تفاصيل تساعدنا على فهم الاحتياج', 'required' => 0 ),
		),
		'continue' => 'اختيار الموعد',
		'appointment_title' => 'اختيار الموعد المناسب',
		'appointment_intro' => 'تظهر المواعيد وفق أيام وساعات العمل الثابتة المحددة من لوحة التحكم.',
		'choose_date' => 'اختيار اليوم',
		'choose_time' => 'اختيار الوقت',
		'no_slots' => 'لا توجد مواعيد متاحة في هذا اليوم. يمكن اختيار تاريخ آخر.',
		'back' => 'رجوع',
		'continue_payment' => 'مراجعة الحجز والدفع',
		'confirm_title' => 'تأكيد الحجز',
		'summary_service' => 'الخدمة',
		'summary_customer' => 'بيانات العميل',
		'summary_appointment' => 'الموعد',
		'payment_title' => 'طريقة الدفع',
		'paymob_label' => 'الدفع الإلكتروني الآمن',
		'paymob_note' => 'الدفع بالبطاقة من خلال بوابة Paymob الآمنة.',
		'bank_label' => 'تحويل بنكي',
		'bank_note' => 'يظل الحجز قيد المراجعة حتى تأكيد التحويل.',
		'bank_received_title' => 'تم استلام بيانات التحويل البنكي',
		'bank_received_message' => 'تم حفظ الحجز وإيصال التحويل، والحالة الآن بانتظار مراجعة فريق VAVA.',
		'cash_label' => 'الدفع لاحقًا',
		'cash_note' => 'تظهر هذه الطريقة فقط عند تفعيلها من الإعدادات.',
		'submit' => 'تأكيد الحجز',
		'processing' => 'جارٍ إنشاء الحجز…',
			'review_title' => 'مراجعة وتأكيد الحجز',
			'review_intro' => 'راجع جميع التفاصيل قبل إنشاء الحجز نهائيًا.',
			'final_confirm' => 'تأكيد الحجز النهائي',
			'edit' => 'تعديل',
		'success_title' => 'تم استلام حجزك',
		'success_message' => 'تم حفظ الموعد، وسيتم إرسال تفاصيل الحجز إليك.',
		'payment_success' => 'تم الدفع بنجاح وتأكيد الحجز.',
		'payment_failed' => 'لم تكتمل عملية الدفع. يمكنك العودة والمحاولة مرة أخرى.',
			'payment_pending' => 'عملية الدفع قيد التحقق حاليًا. ستُحدَّث الحالة تلقائيًا بعد قليل.',
		'invalid_service' => 'هذه الخدمة غير متاحة أو لا تستقبل حجوزات حاليًا.',
		'validation_error' => 'يرجى استكمال الحقول المطلوبة قبل المتابعة.',
		'slot_unavailable' => 'هذا الموعد لم يعد متاحًا. يرجى اختيار وقت آخر.',
			'preparation_title' => 'قبل الجلسة',
			'preparation_message' => 'يرجى استكمال الاستبيان التحضيري قبل موعد الجلسة.',
			'preparation_url' => '',
			'consent_text' => 'أوافق على الشروط والأحكام وسياسة الخصوصية وسياسة الحجز.',
	);
}

function vava_booking_shared_defaults(): array {
	return array(
		'timezone' => wp_timezone_string() ?: 'Asia/Riyadh',
		'working_days' => array( 'sun' => 1, 'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 0, 'sat' => 0 ),
		'working_hours' => array(
			'sun' => array( 'start' => '10:00', 'end' => '18:00' ),
			'mon' => array( 'start' => '10:00', 'end' => '18:00' ),
			'tue' => array( 'start' => '10:00', 'end' => '18:00' ),
			'wed' => array( 'start' => '10:00', 'end' => '18:00' ),
			'thu' => array( 'start' => '10:00', 'end' => '18:00' ),
			'fri' => array( 'start' => '10:00', 'end' => '18:00' ),
			'sat' => array( 'start' => '10:00', 'end' => '18:00' ),
		),
		'slot_interval' => 30,
		'default_duration' => 90,
		'min_notice_hours' => 0,
		'max_days' => 60,
		'payment_methods' => array( 'paymob' => 1, 'bank' => 1, 'cash' => 0 ),
		'paymob' => array(
			'secret_key' => '',
			'public_key' => '',
			'integration_ids' => '',
			'hmac_secret' => '',
			'base_url' => 'https://ksa.paymob.com',
		),
		'bank_transfer' => array(
			'bank_name' => '',
			'beneficiary_name' => '',
			'account_number' => '',
			'iban' => '',
			'swift' => '',
			'currency' => 'SAR',
			'instructions_ar' => 'بعد إتمام التحويل، أدخل بيانات العملية وارفع الإيصال لإرسال الحجز للمراجعة.',
			'instructions_en' => 'After completing the transfer, enter the transaction details and upload the receipt for review.',
			'review_hours' => 24,
		),
	);
}

/** Replace only the exact legacy feminine defaults without touching custom copy. */
function vava_booking_neutralize_arabic_copy( array $data ): array {
	$legacy = array(
		'احجزي جلستك مع VAVA' => 'حجز جلسة مع VAVA',
		'أكملي الخطوات الثلاث التالية، وتظل الخدمة المختارة مرتبطة مباشرة بصفحة مسارات VAVA.' => 'ثلاث خطوات واضحة لإتمام الحجز، مع بقاء الخدمة المختارة مرتبطة مباشرة بصفحة مسارات VAVA.',
		'بياناتك' => 'بيانات الحجز',
		'اكتبي الاسم الكامل' => 'الاسم الكامل',
		'اختاري من القائمة' => 'تحديد خيار',
		'ما الاحتياج الذي ترغبين في دعمه؟' => 'الاحتياج أو الملاحظات',
		'اكتبي أي تفاصيل تساعدنا على فهم احتياجك' => 'أي تفاصيل تساعدنا على فهم الاحتياج',
		'اختاري الموعد المناسب' => 'اختيار الموعد المناسب',
		'اختاري اليوم' => 'اختيار اليوم',
		'اختاري الوقت' => 'اختيار الوقت',
		'تُحسب المواعيد المتاحة تلقائيًا حسب مدة الخدمة وتقويم الحجز.' => 'تظهر المواعيد وفق أيام وساعات العمل الثابتة المحددة من لوحة التحكم.',
		'لا توجد مواعيد متاحة في هذا اليوم. اختاري تاريخًا آخر.' => 'لا توجد مواعيد متاحة في هذا اليوم. يمكن اختيار تاريخ آخر.',
		'عملية الدفع قيد التحقق حاليًا. أعيدي تحميل الصفحة بعد قليل.' => 'عملية الدفع قيد التحقق حاليًا. ستُحدَّث الحالة تلقائيًا بعد قليل.',
		'أكملي الحقول المطلوبة قبل المتابعة.' => 'يرجى استكمال الحقول المطلوبة قبل المتابعة.',
		'هذا الموعد لم يعد متاحًا. اختاري وقتًا آخر.' => 'هذا الموعد لم يعد متاحًا. يرجى اختيار وقت آخر.',
		'أكملي الاستبيان التحضيري قبل موعد الجلسة.' => 'يرجى استكمال الاستبيان التحضيري قبل موعد الجلسة.',
	);
	$walk = static function ( $value ) use ( &$walk, $legacy ) {
		if ( is_array( $value ) ) { return array_map( $walk, $value ); }
		return is_string( $value ) && isset( $legacy[ $value ] ) ? $legacy[ $value ] : $value;
	};
	return $walk( $data );
}

function vava_booking_text_data( int $page_id, string $lang ): array {
	$lang = 'en' === $lang ? 'en' : 'ar';
	$saved = get_post_meta( $page_id, '_vava_booking_' . $lang, true );
	$data = array_replace_recursive( vava_booking_text_defaults( $lang ), is_array( $saved ) ? $saved : array() );
	return 'ar' === $lang ? vava_booking_neutralize_arabic_copy( $data ) : $data;
}

/** Render the configurable consent copy while preserving the three legal links. */
function vava_booking_consent_html( string $copy, string $terms_url, string $privacy_url, string $booking_policy_url, string $lang = 'ar' ): string {
	$lang = 'en' === $lang ? 'en' : 'ar';
	$labels = 'en' === $lang
		? array( 'terms' => 'Terms & Conditions', 'privacy' => 'Privacy Policy', 'booking' => 'Booking Policy' )
		: array( 'terms' => 'الشروط والأحكام', 'privacy' => 'سياسة الخصوصية', 'booking' => 'سياسة الحجز' );
	$default = 'en' === $lang
		? 'I agree to the Terms & Conditions, Privacy Policy, and Booking Policy.'
		: 'أوافق على الشروط والأحكام وسياسة الخصوصية وسياسة الحجز.';
	$copy = trim( $copy ) ?: $default;
	$html = esc_html( $copy );
	$links = array(
		'terms'  => '<a href="' . esc_url( $terms_url ) . '" target="_blank" rel="noopener">' . esc_html( $labels['terms'] ) . '</a>',
		'privacy'=> '<a href="' . esc_url( $privacy_url ) . '" target="_blank" rel="noopener">' . esc_html( $labels['privacy'] ) . '</a>',
		'booking'=> '<a href="' . esc_url( $booking_policy_url ) . '" target="_blank" rel="noopener">' . esc_html( $labels['booking'] ) . '</a>',
	);
	$tokens = array( '{terms}' => $links['terms'], '{privacy}' => $links['privacy'], '{booking_policy}' => $links['booking'] );
	$html = strtr( $html, $tokens );
	foreach ( $labels as $key => $label ) {
		if ( false === strpos( $html, $links[ $key ] ) ) {
			$html = preg_replace( '/' . preg_quote( esc_html( $label ), '/' ) . '/u', $links[ $key ], $html, 1 );
		}
	}
	$allowed = array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ) );
	return wp_kses( $html, $allowed );
}

function vava_booking_shared_data( int $page_id ): array {
	$saved = get_post_meta( $page_id, '_vava_booking_shared', true );
	$data = array_replace_recursive( vava_booking_shared_defaults(), is_array( $saved ) ? $saved : array() );
	$data['bank_transfer']['iban'] = vava_booking_protected_iban();
	return $data;
}

/**
 * The booking IBAN is intentionally sourced only from wp-config.php.
 * Database and request values are never trusted for this high-impact field.
 */
function vava_booking_protected_iban(): string {
	if ( ! defined( 'VAVA_BOOKING_IBAN' ) ) { return ''; }
	return strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) VAVA_BOOKING_IBAN ) );
}

function vava_booking_paths_page_id(): int {
	$pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/paths-vava.php', 'fields' => 'ids', 'no_found_rows' => true ) );
	return isset( $pages[0] ) ? absint( $pages[0] ) : 0;
}

function vava_booking_slug( string $value ): string {
	$value = preg_replace( '/\.html?$/i', '', basename( wp_parse_url( $value, PHP_URL_PATH ) ?: $value ) );
	$value = sanitize_title( $value );
	return $value;
}

function vava_booking_service_duration_minutes( array $service, int $fallback = 90 ): int {
	$duration = (string) ( $service['duration'] ?? '' );
	$latin = strtr( $duration, array( '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9' ) );
	if ( preg_match( '/(\d{1,3})/', $latin, $match ) ) {
		$value = absint( $match[1] );
		if ( $value >= 10 && $value <= 480 ) { return $value; }
	}
	return max( 10, $fallback );
}

/** Resolve a stable consultation category from the service payload. */
function vava_booking_service_category( array $service ): string {
	$category = sanitize_key( (string) ( $service['category'] ?? '' ) );
	if ( in_array( $category, array( 'quick', 'followup', 'comprehensive' ), true ) ) { return $category; }
	return function_exists( 'vava_paths_session_category' ) ? vava_paths_session_category( $service ) : 'comprehensive';
}

/** Customer-facing duration label from the category source of truth. */
function vava_booking_service_display_duration( array $service, string $lang = 'ar' ): string {
	$lang = 'en' === $lang ? 'en' : 'ar';
	if ( function_exists( 'vava_paths_session_display_duration' ) ) {
		return vava_paths_session_display_duration( $service, $lang );
	}
	$category = vava_booking_service_category( $service );
	$is_en = 'en' === $lang;
	return array( 'quick' => $is_en ? '15–20 minutes' : '15–20 دقيقة', 'followup' => $is_en ? '30 minutes' : '30 دقيقة', 'comprehensive' => $is_en ? '90 minutes' : '90 دقيقة' )[ $category ] ?? ( $is_en ? '90 minutes' : '90 دقيقة' );
}

/** Resolve the service duration used by availability and overlap protection. */
function vava_booking_effective_duration( array $service, array $shared ): int {
	if ( function_exists( 'vava_paths_session_booking_minutes' ) ) { return vava_paths_session_booking_minutes( $service ); }
	if ( ! empty( $service['booking_duration'] ) ) { return max( 10, absint( $service['booking_duration'] ) ); }
	$category = vava_booking_service_category( $service );
	if ( function_exists( 'vava_paths_session_category_booking_minutes' ) ) { return vava_paths_session_category_booking_minutes( $category ); }
	return array( 'quick' => 20, 'followup' => 30, 'comprehensive' => 90 )[ $category ] ?? max( 10, absint( $shared['default_duration'] ?? 90 ) );
}

/** Recover the consultation category for current and legacy bookings. */
function vava_booking_category_for_booking( int $booking_id ): string {
	$category = sanitize_key( (string) get_post_meta( $booking_id, '_vava_booking_service_category', true ) );
	if ( in_array( $category, array( 'quick', 'followup', 'comprehensive' ), true ) ) { return $category; }

	$lang = 'en' === get_post_meta( $booking_id, '_vava_booking_language', true ) ? 'en' : 'ar';
	$uid  = sanitize_key( (string) get_post_meta( $booking_id, '_vava_booking_service_uid', true ) );
	if ( $uid ) {
		$service = vava_booking_resolve_service( $uid, $lang );
		if ( $service ) { return vava_booking_service_category( $service ); }
	}

	$title = strtolower( wp_strip_all_tags( (string) get_post_meta( $booking_id, '_vava_booking_service_title', true ) ) );
	if ( preg_match( '/(?:استشار(?:ة|ات)\s*سريعة|quick\s*(?:consult|session))/iu', $title ) ) { return 'quick'; }
	if ( preg_match( '/(?:متابعة|follow[ -]?up)/iu', $title ) ) { return 'followup'; }
	if ( preg_match( '/(?:شاملة|التوازن|التشافي|باقة|comprehensive|balance|healing|package)/iu', $title ) ) { return 'comprehensive'; }

	$minutes = absint( get_post_meta( $booking_id, '_vava_booking_duration', true ) );
	if ( $minutes > 0 && $minutes <= 20 ) { return 'quick'; }
	if ( 30 === $minutes ) { return 'followup'; }
	if ( $minutes >= 60 ) { return 'comprehensive'; }
	return '';
}

/** Display duration for stored bookings, including safe recovery for legacy records. */
function vava_booking_display_duration_for_booking( int $booking_id, string $lang = 'ar' ): string {
	$lang = 'en' === $lang ? 'en' : 'ar';
	$uid  = sanitize_key( (string) get_post_meta( $booking_id, '_vava_booking_service_uid', true ) );
	if ( $uid ) {
		$service = vava_booking_resolve_service( $uid, $lang );
		if ( $service ) { return vava_booking_service_display_duration( $service, $lang ); }
	}
	$category = vava_booking_category_for_booking( $booking_id );
	if ( $category ) { return vava_booking_service_display_duration( array( 'category' => $category ), $lang ); }
	$minutes = absint( get_post_meta( $booking_id, '_vava_booking_duration', true ) );
	if ( 60 === $minutes ) { $minutes = 90; }
	return $minutes ? $minutes . ( 'en' === $lang ? ' minutes' : ' دقيقة' ) : '—';
}

/** One-time persistence upgrade for existing booking category and operational duration. */
function vava_booking_maybe_migrate_category_durations_v12250(): void {
	if ( '1.22.50' === (string) get_option( 'vava_booking_category_duration_version', '' ) ) { return; }
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$ids = get_posts( array( 'post_type' => 'vava_booking', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true ) );
	foreach ( $ids as $booking_id ) {
		$booking_id = absint( $booking_id );
		if ( ! $booking_id || ( function_exists( 'vava_booking_order_is_product' ) && vava_booking_order_is_product( $booking_id ) ) ) { continue; }
		$category = vava_booking_category_for_booking( $booking_id );
		if ( ! $category ) { continue; }
		$minutes = function_exists( 'vava_paths_session_category_booking_minutes' ) ? vava_paths_session_category_booking_minutes( $category ) : ( 'comprehensive' === $category ? 90 : 30 );
		update_post_meta( $booking_id, '_vava_booking_service_category', $category );
		update_post_meta( $booking_id, '_vava_booking_duration', $minutes );
	}
	update_option( 'vava_booking_category_duration_version', '1.22.50', false );
}
add_action( 'admin_init', 'vava_booking_maybe_migrate_category_durations_v12250', 80 );

/** All services use the same shared working-day map. */
function vava_booking_effective_working_days( array $service, array $shared ): array {
	unset( $service );
	$days = array_replace( array_fill_keys( array( 'sun','mon','tue','wed','thu','fri','sat' ), 0 ), (array) ( $shared['working_days'] ?? array() ) );
	$days['fri'] = 0;
	$days['sat'] = 0;
	return $days;
}

function vava_booking_services( string $lang = 'ar' ): array {
	$page_id = vava_booking_paths_page_id();
	if ( ! $page_id || ! function_exists( 'vava_paths_data' ) ) { return array(); }
	$data = vava_paths_data( $page_id, $lang );
	$services = array();
	foreach ( array_values( (array) ( $data['packages'] ?? array() ) ) as $index => $item ) {
		$item = is_array( $item ) ? $item : array();
		$uid = sanitize_key( (string) ( $item['uid'] ?? '' ) );
		if ( ! $uid ) { $uid = 'session-' . substr( md5( (string) ( $item['title'] ?? $index ) ), 0, 12 ); }
		$legacy = array_filter( array_unique( array(
			vava_booking_slug( (string) ( $item['booking_url'] ?? '' ) ),
			vava_booking_slug( (string) ( $item['link_url'] ?? '' ) ),
			vava_booking_slug( (string) ( $item['title'] ?? '' ) ),
		) ) );
		$services[ $uid ] = array(
			'uid' => $uid,
			'kind' => 'session',
			'category' => sanitize_key( (string) ( $item['category'] ?? '' ) ),
			'title' => (string) ( $item['title'] ?? '' ),
			'description' => (string) ( $item['description'] ?? '' ),
			'image_id' => absint( $item['image_id'] ?? 0 ),
			'price' => (string) ( $item['price'] ?? '' ),
			'currency' => (string) ( $item['currency'] ?? ( 'en' === $lang ? 'SAR' : 'ر.س' ) ),
			'duration' => vava_booking_service_display_duration( $item, $lang ),
			'booking_duration' => absint( $item['booking_duration'] ?? ( function_exists( 'vava_paths_session_category_booking_minutes' ) ? vava_paths_session_category_booking_minutes( vava_booking_service_category( $item ) ) : 0 ) ),
			'session_type' => (string) ( $item['session_type'] ?? $item['badge'] ?? '' ),
			'location' => (string) ( $item['location'] ?? '' ),
			'basics' => array_values( (array) ( $item['basics'] ?? array() ) ),
			'enabled' => ! isset( $item['enabled'] ) || ! empty( $item['enabled'] ),
			'booking_enabled' => ! isset( $item['booking_enabled'] ) || ! empty( $item['booking_enabled'] ),
			'availability' => (string) ( $item['availability'] ?? '' ),
			'detail_url' => (string) ( $item['link_url'] ?? '' ),
			'legacy' => $legacy,
		);
	}
	foreach ( array_values( (array) ( $data['compare']['plans'] ?? array() ) ) as $index => $item ) {
		$item = is_array( $item ) ? $item : array();
		$uid = sanitize_key( (string) ( $item['uid'] ?? '' ) );
		if ( ! $uid ) { $uid = 'package-' . substr( md5( (string) ( $item['title'] ?? $index ) ), 0, 12 ); }
		$legacy = array_filter( array_unique( array( vava_booking_slug( (string) ( $item['button_url'] ?? '' ) ), vava_booking_slug( (string) ( $item['title'] ?? '' ) ) ) ) );
		$services[ $uid ] = array(
			'uid' => $uid,
			'kind' => 'package',
			'category' => sanitize_key( (string) ( $item['category'] ?? 'comprehensive' ) ),
			'title' => (string) ( $item['title'] ?? '' ),
			'description' => (string) ( $item['description'] ?? $item['core_label'] ?? '' ),
			'image_id' => absint( $item['image_id'] ?? 0 ),
			'price' => (string) ( $item['price'] ?? '' ),
			'currency' => (string) ( $item['currency'] ?? ( 'en' === $lang ? 'SAR' : 'ر.س' ) ),
			'duration' => vava_booking_service_display_duration( array_merge( $item, array( 'category' => 'comprehensive' ) ), $lang ),
			'booking_duration' => 90,
			'session_type' => 'en' === $lang ? 'VAVA package' : 'باقة VAVA',
			'location' => '',
			'basics' => array(),
			'enabled' => ! isset( $item['enabled'] ) || ! empty( $item['enabled'] ),
			'booking_enabled' => ! isset( $item['booking_enabled'] ) || ! empty( $item['booking_enabled'] ),
			'availability' => '',
			'detail_url' => (string) ( $item['details_url'] ?? $item['link_url'] ?? '' ),
			'legacy' => $legacy,
		);
	}
	return $services;
}

function vava_booking_resolve_service( string $identifier, string $lang = 'ar' ): ?array {
	$identifier = sanitize_key( vava_booking_slug( $identifier ) ?: $identifier );
	$services = vava_booking_services( $lang );
	if ( isset( $services[ $identifier ] ) ) { return $services[ $identifier ]; }
	foreach ( $services as $service ) {
		if ( in_array( $identifier, (array) $service['legacy'], true ) ) { return $service; }
	}
	$legacy_map = array(
		'balance-journey-package' => array( 'رحلة-التوازن', 'balance-journey' ),
		'balance-map-package' => array( 'خريطة-التوازن', 'balance-map' ),
		'deep-healing-package' => array( 'التشافي-العميق', 'deep-healing' ),
		'comprehensive-session' => array( 'جلسة-شاملة', 'comprehensive' ),
		'rhythm-followup-session' => array( 'متابعة-كاملة', 'follow-up' ),
		'mini-followup-session' => array( 'متابعة-مصغرة', 'mini' ),
		'followup-package' => array( '3-جلسات', 'followup' ),
		'quick-consultation' => array( 'استشارة-سريعة', 'quick' ),
	);
	if ( isset( $legacy_map[ $identifier ] ) ) {
		foreach ( $services as $service ) {
			$haystack = sanitize_title( $service['title'] . ' ' . implode( ' ', (array) $service['legacy'] ) );
			foreach ( $legacy_map[ $identifier ] as $needle ) {
				if ( false !== strpos( $haystack, sanitize_title( $needle ) ) ) { return $service; }
			}
		}
	}
	return null;
}

function vava_booking_url_for_service( string $uid, string $lang = 'ar' ): string {
	$page_id = vava_booking_page_id();
	if ( ! $page_id ) { return '#'; }
	return add_query_arg( array( 'service' => sanitize_key( $uid ), 'vava_lang' => 'en' === $lang ? 'en' : 'ar' ), get_permalink( $page_id ) );
}


/** Return the public details page for the selected VAVA session in the active language. */
function vava_booking_service_detail_url( array $service, string $lang = 'ar' ): string {
	$lang = 'en' === $lang ? 'en' : 'ar';
	if ( 'package' === (string) ( $service['kind'] ?? '' ) ) {
		$paths_id  = vava_booking_paths_page_id();
		$paths_url = $paths_id ? vava_localized_page_url( $paths_id, $lang ) : home_url( '/' );
		return $paths_url . '#vava-path-stage-5';
	}
	$uid = sanitize_key( (string) ( $service['uid'] ?? '' ) );
	if ( $uid ) {
		$posts = get_posts(
			array(
				'post_type'      => 'vava_session',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => '_vava_session_uid',
				'meta_value'     => $uid,
			)
		);
		if ( ! empty( $posts[0] ) ) {
			return add_query_arg( 'vava_lang', $lang, get_permalink( (int) $posts[0] ) );
		}
	}
	$stored = trim( (string) ( $service['detail_url'] ?? '' ) );
	if ( $stored && '#' !== $stored ) {
		return add_query_arg( 'vava_lang', $lang, $stored );
	}
	$paths_id = vava_booking_paths_page_id();
	return $paths_id ? vava_localized_page_url( $paths_id, $lang ) : home_url( '/' );
}

/** Pick one aligned service sample for the live admin preview. */
function vava_booking_preview_service_sample( string $lang ): array {
	$lang = 'en' === $lang ? 'en' : 'ar';
	$services = vava_booking_services( $lang );
	$service = $services ? reset( $services ) : array();
	$defaults = 'en' === $lang ? array(
		'title' => 'Balance Journey Package',
		'description' => 'A connected VAVA session designed around your selected path.',
		'duration' => '90 minutes',
		'session_type' => 'Individual session',
		'price' => '1190 SAR',
	) : array(
		'title' => 'باقة رحلة التوازن',
		'description' => 'جلسة متصلة مباشرة ببيانات صفحة مسارات VAVA.',
		'duration' => '90 دقيقة',
		'session_type' => 'جلسة فردية',
		'price' => '1190 ر.س',
	);
	$service = is_array( $service ) ? $service : array();
	return array(
		'title' => trim( (string) ( $service['title'] ?? '' ) ) ?: $defaults['title'],
		'description' => trim( wp_strip_all_tags( (string) ( $service['description'] ?? '' ) ) ) ?: $defaults['description'],
		'duration' => trim( (string) ( $service['duration'] ?? '' ) ) ?: $defaults['duration'],
		'session_type' => trim( (string) ( $service['session_type'] ?? '' ) ) ?: $defaults['session_type'],
		'price' => vava_booking_format_price_label( (string) ( $service['price'] ?? '' ), (string) ( $service['currency'] ?? '' ), $lang ) ?: $defaults['price'],
	);
}

function vava_booking_service_is_available( array $service, array $shared ): bool {
	unset( $shared );
	if ( empty( $service['enabled'] ) || empty( $service['booking_enabled'] ) ) { return false; }
	return ! preg_match( '/(?:غير\s*متاح|مغلق|unavailable|closed)/iu', (string) ( $service['availability'] ?? '' ) );
}

/** Payment readiness is separate from whether a method is enabled. */
function vava_booking_paymob_missing_fields( array $shared ): array {
	$config = (array) ( $shared['paymob'] ?? array() );
	$missing = array();
	foreach ( array( 'secret_key' => 'Secret Key', 'public_key' => 'Public Key', 'integration_ids' => 'Integration IDs', 'hmac_secret' => 'HMAC Secret' ) as $key => $label ) {
		if ( empty( $config[ $key ] ) ) { $missing[] = $label; }
	}
	return $missing;
}

function vava_booking_paymob_is_ready( array $shared ): bool {
	return empty( vava_booking_paymob_missing_fields( $shared ) );
}

function vava_booking_bank_missing_fields( array $shared ): array {
	$config = (array) ( $shared['bank_transfer'] ?? array() );
	$missing = array();
	foreach ( array( 'bank_name' => 'اسم البنك', 'beneficiary_name' => 'اسم المستفيد', 'iban' => 'IBAN' ) as $key => $label ) {
		if ( '' === trim( (string) ( $config[ $key ] ?? '' ) ) ) { $missing[] = $label; }
	}
	return $missing;
}

function vava_booking_bank_is_ready( array $shared ): bool {
	return empty( vava_booking_bank_missing_fields( $shared ) );
}

function vava_booking_admin_capability(): string {
	return 'manage_options';
}

function vava_booking_status_label( string $status ): string {
	$labels = array(
		'pending' => 'بانتظار الاعتماد',
		'pending_payment' => 'بانتظار الدفع الإلكتروني',
		'pending_bank_review' => 'بانتظار مراجعة التحويل البنكي',
		'confirmed' => 'مؤكد',
		'paid' => 'مدفوع',
		'completed' => 'مكتمل',
		'payment_failed' => 'فشل الدفع',
		'payment_error' => 'خطأ في الدفع',
		'bank_rejected' => 'تحويل بنكي مرفوض',
		'cancelled' => 'ملغي',
		'refund_pending' => 'بانتظار إرجاع المبلغ',
		'partially_refunded' => 'مسترد جزئيًا',
		'refunded' => 'تم رد المبلغ',
		'customer_cancelled' => 'ملغي بواسطة العميل',
		'cancellation_requested' => 'طلب إلغاء من العميل',
	);
	return $labels[ $status ] ?? ( $status ?: 'غير محدد' );
}

function vava_booking_payment_status_label( string $status ): string {
	$labels = array(
		'unpaid' => 'غير مدفوع',
		'pending' => 'قيد الانتظار',
		'pending_bank_review' => 'بانتظار مراجعة التحويل',
		'paid' => 'مدفوع',
		'failed' => 'فشل الدفع',
		'rejected' => 'مرفوض',
		'cancelled' => 'ملغي',
		'refund_pending' => 'بانتظار إرجاع المبلغ',
		'partially_refunded' => 'مسترد جزئيًا',
		'refunded' => 'تم رد المبلغ',
	);
	return $labels[ $status ] ?? ( $status ?: 'غير محدد' );
}

function vava_booking_payment_method_label( string $method ): string {
	return array( 'paymob' => 'Paymob', 'bank' => 'تحويل بنكي', 'cash' => 'الدفع لاحقًا', 'free' => 'جلسة مجانية' )[ $method ] ?? $method;
}

function vava_booking_effective_payment_status( int $booking_id ): string {
	$status = (string) get_post_meta( $booking_id, '_vava_booking_payment_status', true );
	if ( $status ) { return $status; }
	$booking_status = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
	if ( in_array( $booking_status, array( 'confirmed', 'paid' ), true ) ) { return 'paid'; }
	if ( 'pending_bank_review' === $booking_status ) { return 'pending_bank_review'; }
	if ( in_array( $booking_status, array( 'payment_failed', 'payment_error' ), true ) ) { return 'failed'; }
	if ( 'bank_rejected' === $booking_status ) { return 'rejected'; }
	return 'pending';
}

function vava_booking_register_post_type(): void {
	$cap = vava_booking_admin_capability();
	register_post_type( 'vava_booking', array(
		'labels' => array(
			'name' => 'حجوزات VAVA', 'singular_name' => 'حجز VAVA', 'menu_name' => 'حجوزات VAVA',
			'all_items' => 'حجوزات VAVA', 'edit_item' => 'تفاصيل الحجز', 'view_item' => 'تفاصيل الحجز',
			'search_items' => 'بحث في الحجوزات', 'not_found' => 'لا توجد حجوزات بعد',
		),
		'public' => false,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 26,
		'menu_icon' => 'dashicons-calendar-alt',
		'supports' => array(),
		'capabilities' => array(
			'edit_post' => $cap, 'read_post' => $cap, 'delete_post' => $cap,
			'edit_posts' => $cap, 'edit_others_posts' => $cap, 'publish_posts' => $cap,
			'read_private_posts' => $cap, 'delete_posts' => $cap, 'delete_private_posts' => $cap,
			'delete_published_posts' => $cap, 'delete_others_posts' => $cap,
			'edit_private_posts' => $cap, 'edit_published_posts' => $cap, 'create_posts' => 'do_not_allow',
		),
		'map_meta_cap' => false,
	) );
}
add_action( 'init', 'vava_booking_register_post_type' );

/** Separate session bookings from product orders while keeping the shared payment engine. */
function vava_booking_admin_scope(): string {
	return isset( $_GET['vava_order_scope'] ) && 'products' === sanitize_key( wp_unslash( $_GET['vava_order_scope'] ) ) ? 'products' : 'bookings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

function vava_booking_order_is_product( int $post_id ): bool {
	$type = sanitize_key( (string) get_post_meta( $post_id, '_vava_booking_order_type', true ) );
	return in_array( $type, array( 'digital_product', 'tangible_product', 'physical_product' ), true );
}

function vava_booking_register_products_menu(): void {
	$hook = add_menu_page(
		'منتجات VAVA',
		'منتجات VAVA',
		vava_booking_admin_capability(),
		'vava-products-orders',
		'vava_booking_products_menu_fallback',
		'dashicons-products',
		27
	);
	if ( $hook ) {
		add_action( 'load-' . $hook, 'vava_booking_products_menu_redirect' );
	}
}
add_action( 'admin_menu', 'vava_booking_register_products_menu', 99 );

/**
 * Keep the VAVA advanced pages reachable from their operational admin areas.
 * The pages remain normal WordPress pages; only their sidebar placement and
 * the Pages list are curated to reduce accidental edits or deletion.
 */
function vava_admin_advanced_page_id_by_template( string $template ): int {
	$ids = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_key'       => '_wp_page_template',
		'meta_value'     => $template,
	) );
	return $ids ? absint( $ids[0] ) : 0;
}

function vava_admin_advanced_page_edit_url( int $page_id ): string {
	return $page_id ? admin_url( 'post.php?post=' . $page_id . '&action=edit' ) : admin_url( 'edit.php?post_type=page' );
}

function vava_admin_register_curated_page_links(): void {
	$capability = 'edit_pages';

	add_submenu_page( 'vava-products-orders', 'مختارات VAVA', 'مختارات VAVA', $capability, 'vava-edit-selections', 'vava_admin_curated_page_link_fallback' );
	add_submenu_page( 'edit.php?post_type=vava_booking', 'مسارات VAVA', 'مسارات VAVA', $capability, 'vava-edit-paths', 'vava_admin_curated_page_link_fallback' );
	add_submenu_page( 'options-general.php', 'صفحة الحجز', 'صفحة الحجز', $capability, 'vava-edit-booking-page', 'vava_admin_curated_page_link_fallback' );
	add_submenu_page( 'edit.php', 'صفحة المجلة', 'صفحة المجلة', $capability, 'vava-edit-journal', 'vava_admin_curated_page_link_fallback' );
}
add_action( 'admin_menu', 'vava_admin_register_curated_page_links', 120 );

/** Replace redirecting placeholder URLs with the real editor URLs in the sidebar. */
function vava_admin_wire_curated_page_links(): void {
	global $submenu;
	/* The automatically-created first child inherits the synthetic parent slug.
	 * Give that child the real scoped orders URL, while leaving the parent as a
	 * pure accordion trigger in the branded sidebar. */
	if ( ! empty( $submenu['vava-products-orders'] ) && is_array( $submenu['vava-products-orders'] ) ) {
		$products_url = add_query_arg(
			array( 'post_type' => 'vava_booking', 'vava_order_scope' => 'products' ),
			admin_url( 'edit.php' )
		);
		foreach ( $submenu['vava-products-orders'] as &$products_item ) {
			if ( isset( $products_item[2] ) && 'vava-products-orders' === (string) $products_item[2] ) {
				$products_item[0] = 'طلبات المنتجات';
				$products_item[2] = $products_url;
				break;
			}
		}
		unset( $products_item );
	}
	$targets = vava_admin_curated_page_targets();
	$parents = array(
		'vava-products-orders'              => 'vava-edit-selections',
		'edit.php?post_type=vava_booking'   => 'vava-edit-paths',
		'options-general.php'               => 'vava-edit-booking-page',
		'edit.php'                          => 'vava-edit-journal',
	);
	foreach ( $parents as $parent => $slug ) {
		if ( empty( $submenu[ $parent ] ) || empty( $targets[ $slug ] ) ) { continue; }
		foreach ( $submenu[ $parent ] as &$item ) {
			if ( isset( $item[2] ) && $slug === $item[2] ) {
				$item[2] = vava_admin_advanced_page_edit_url( absint( $targets[ $slug ] ) );
			}
		}
		unset( $item );
	}
}
add_action( 'admin_menu', 'vava_admin_wire_curated_page_links', 999 );

function vava_admin_curated_page_targets(): array {
	return array(
		'vava-edit-selections'   => function_exists( 'vava_selections_page_id' ) ? vava_selections_page_id() : vava_admin_advanced_page_id_by_template( 'page-templates/selections-vava.php' ),
		'vava-edit-paths'        => vava_admin_advanced_page_id_by_template( 'page-templates/paths-vava.php' ),
		'vava-edit-booking-page' => function_exists( 'vava_booking_page_id' ) ? vava_booking_page_id() : vava_admin_advanced_page_id_by_template( 'page-templates/booking-vava.php' ),
		'vava-edit-journal'      => vava_admin_advanced_page_id_by_template( 'page-templates/journal-vava.php' ),
	);
}

function vava_admin_redirect_curated_page_link(): void {
	if ( ! isset( $_GET['page'] ) || ! current_user_can( 'edit_pages' ) ) { return; } // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$slug    = sanitize_key( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$targets = vava_admin_curated_page_targets();
	if ( ! array_key_exists( $slug, $targets ) ) { return; }
	wp_safe_redirect( vava_admin_advanced_page_edit_url( absint( $targets[ $slug ] ) ) );
	exit;
}
add_action( 'admin_init', 'vava_admin_redirect_curated_page_link', 2 );

function vava_admin_curated_page_link_fallback(): void {
	echo '<div class="wrap"><p>' . esc_html__( 'Redirecting to the page editor…' ) . '</p></div>';
}

function vava_admin_hide_operational_pages_from_pages_list( WP_Query $query ): void {
	global $pagenow;
	if ( ! is_admin() || ! $query->is_main_query() || 'edit.php' !== $pagenow || 'page' !== $query->get( 'post_type' ) ) { return; }

	$templates = array(
		'page-templates/booking-vava.php',
		'page-templates/my-bookings-vava.php',
		'page-templates/digital-product-checkout-vava.php',
		'page-templates/digital-library-viewer-vava.php',
		'page-templates/paths-vava.php',
		'page-templates/selections-vava.php',
		'page-templates/journal-vava.php',
	);
	$hidden_ids = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array( array( 'key' => '_wp_page_template', 'value' => $templates, 'compare' => 'IN' ) ),
	) );
	if ( $hidden_ids ) {
		$query->set( 'post__not_in', array_values( array_unique( array_merge( (array) $query->get( 'post__not_in' ), array_map( 'absint', $hidden_ids ) ) ) ) );
	}
}
add_action( 'pre_get_posts', 'vava_admin_hide_operational_pages_from_pages_list', 20 );

function vava_booking_products_menu_redirect(): void {
	if ( ! current_user_can( vava_booking_admin_capability() ) ) { wp_die( esc_html__( 'You are not allowed to access this page.' ) ); }
	wp_safe_redirect( add_query_arg( array( 'post_type' => 'vava_booking', 'vava_order_scope' => 'products' ), admin_url( 'edit.php' ) ) );
	exit;
}

function vava_booking_products_menu_fallback(): void {
	printf( '<div class="wrap"><p><a class="button button-primary" href="%s">%s</a></p></div>', esc_url( add_query_arg( array( 'post_type' => 'vava_booking', 'vava_order_scope' => 'products' ), admin_url( 'edit.php' ) ) ), esc_html__( 'Open VAVA products' ) );
}

function vava_booking_products_parent_file( $parent_file ): string {
	$scope = vava_booking_admin_scope();
	if ( isset( $_GET['page'], $_GET['booking'] ) && 'vava-booking-details' === sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$scope = vava_booking_order_is_product( absint( $_GET['booking'] ) ) ? 'products' : 'bookings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	return 'products' === $scope ? 'vava-products-orders' : (string) $parent_file;
}
add_filter( 'parent_file', 'vava_booking_products_parent_file', 60 );

function vava_booking_products_submenu_highlight( $submenu_file ): string {
	$scope = vava_booking_admin_scope();
	if ( isset( $_GET['page'], $_GET['booking'] ) && 'vava-booking-details' === sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$scope = vava_booking_order_is_product( absint( $_GET['booking'] ) ) ? 'products' : 'bookings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	return 'products' === $scope
		? add_query_arg( array( 'post_type' => 'vava_booking', 'vava_order_scope' => 'products' ), admin_url( 'edit.php' ) )
		: (string) $submenu_file;
}
add_filter( 'submenu_file', 'vava_booking_products_submenu_highlight', 60 );

/** Reassert the scoped product-order menu after all core and theme menu filters. */
function vava_booking_products_final_menu_context( $value ): string {
	if ( 'products' !== vava_booking_admin_scope() ) { return (string) $value; }
	return 'vava-products-orders';
}
add_filter( 'parent_file', 'vava_booking_products_final_menu_context', PHP_INT_MAX );

function vava_booking_products_final_submenu_context( $value ): string {
	if ( 'products' !== vava_booking_admin_scope() ) { return (string) $value; }
	return add_query_arg(
		array( 'post_type' => 'vava_booking', 'vava_order_scope' => 'products' ),
		admin_url( 'edit.php' )
	);
}
add_filter( 'submenu_file', 'vava_booking_products_final_submenu_context', PHP_INT_MAX );

/** Make moved WordPress pages highlight their new operational parent and child. */
function vava_admin_curated_page_menu_context(): array {
	global $pagenow;
	if ( 'post.php' !== (string) $pagenow || empty( $_GET['post'] ) ) { return array(); } // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$post_id = absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	foreach ( vava_admin_curated_page_targets() as $slug => $target_id ) {
		if ( $post_id === absint( $target_id ) ) {
			$map = array(
				'vava-edit-selections'   => array( 'vava-products-orders', 'vava-edit-selections' ),
				'vava-edit-paths'        => array( 'edit.php?post_type=vava_booking', 'vava-edit-paths' ),
				'vava-edit-booking-page' => array( 'options-general.php', 'vava-edit-booking-page' ),
				'vava-edit-journal'      => array( 'edit.php', 'vava-edit-journal' ),
			);
			return $map[ $slug ] ?? array();
		}
	}
	return array();
}

function vava_admin_curated_parent_file( $parent_file ): string {
	$context = vava_admin_curated_page_menu_context();
	return $context ? $context[0] : (string) $parent_file;
}
add_filter( 'parent_file', 'vava_admin_curated_parent_file', 999 );

function vava_admin_curated_submenu_file( $submenu_file ): string {
	$context = vava_admin_curated_page_menu_context();
	return $context ? vava_admin_advanced_page_edit_url( absint( $_GET['post'] ) ) : (string) $submenu_file; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}
add_filter( 'submenu_file', 'vava_admin_curated_submenu_file', 999 );

function vava_booking_day_key( DateTimeImmutable $date ): string {
	return array( 0 => 'sun', 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat' )[ (int) $date->format( 'w' ) ];
}

/**
 * Format a stored 24-hour booking time for display without changing its value.
 *
 * Examples: 00:00 => 12:00 am, 13:00 => 1:00 pm.
 */
function vava_booking_format_time_12h( string $time ): string {
	$time = trim( $time );
	if ( '' === $time || ! preg_match( '/^(?:([01]?\d|2[0-3])):([0-5]\d)(?::[0-5]\d)?$/', $time, $matches ) ) {
		return $time;
	}
	$hour = (int) $matches[1];
	return sprintf( '%d:%s %s', $hour % 12 ?: 12, $matches[2], $hour < 12 ? 'am' : 'pm' );
}

/** Convert clock fragments inside a stored date-time string for display only. */
function vava_booking_format_datetime_12h( string $value ): string {
	$value = trim( $value );
	if ( '' === $value ) { return ''; }
	return (string) preg_replace_callback(
		'/(?<!\d)([01]?\d|2[0-3]):([0-5]\d)(?::[0-5]\d)?(?!\d)/',
		static function( array $matches ): string {
			return vava_booking_format_time_12h( $matches[1] . ':' . $matches[2] );
		},
		$value
	);
}

function vava_booking_slot_is_reserved( string $date, string $time, int $duration ): bool {
	$bookings = get_posts( array(
		'post_type' => 'vava_booking', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true,
		'meta_query' => array(
			array( 'key' => '_vava_booking_date', 'value' => $date ),
			array( 'key' => '_vava_booking_status', 'value' => array( 'pending', 'pending_payment', 'pending_bank_review', 'paid', 'confirmed', 'cancellation_requested' ), 'compare' => 'IN' ),
		),
	) );
	$start = strtotime( $date . ' ' . $time );
	$end = $start + ( $duration * MINUTE_IN_SECONDS );
	foreach ( $bookings as $booking_id ) {
		$other_time = (string) get_post_meta( $booking_id, '_vava_booking_time', true );
		$other_duration = max( 10, absint( get_post_meta( $booking_id, '_vava_booking_duration', true ) ) );
		$other_start = strtotime( $date . ' ' . $other_time );
		$other_end = $other_start + ( $other_duration * MINUTE_IN_SECONDS );
		if ( $start < $other_end && $end > $other_start ) { return true; }
	}
	return false;
}

/** Maximum accepted bookings per day for each consultation category. */
function vava_booking_daily_capacity( array $service ): int {
	$category = vava_booking_service_category( $service );
	return array( 'comprehensive' => 1, 'followup' => 2, 'quick' => 2 )[ $category ] ?? 1;
}

/** Count active bookings for the selected consultation category and date. */
function vava_booking_daily_category_count( array $service, string $date ): int {
	$category = vava_booking_service_category( $service );
	$bookings = get_posts( array(
		'post_type' => 'vava_booking', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true,
		'meta_query' => array(
			array( 'key' => '_vava_booking_date', 'value' => $date ),
			array( 'key' => '_vava_booking_status', 'value' => array( 'pending', 'pending_payment', 'pending_bank_review', 'paid', 'confirmed', 'cancellation_requested' ), 'compare' => 'IN' ),
		),
	) );
	$count = 0;
	foreach ( $bookings as $booking_id ) {
		if ( $category === vava_booking_category_for_booking( (int) $booking_id ) ) { $count++; }
	}
	return $count;
}

/** Whether the selected category still has room for another booking that day. */
function vava_booking_day_has_capacity( array $service, string $date ): bool {
	return vava_booking_daily_category_count( $service, $date ) < vava_booking_daily_capacity( $service );
}

function vava_booking_available_slots( array $service, string $date_string, array $shared ): array {
	try { $timezone = new DateTimeZone( (string) ( $shared['timezone'] ?: wp_timezone_string() ) ); } catch ( Exception $e ) { $timezone = wp_timezone(); }
	$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $date_string, $timezone );
	if ( ! $date || $date->format( 'Y-m-d' ) !== $date_string ) { return array(); }
	$today = new DateTimeImmutable( 'today', $timezone );
	$max = $today->modify( '+' . max( 1, absint( $shared['max_days'] ?? 60 ) ) . ' days' );
	if ( $date < $today || $date > $max ) { return array(); }
	$key = vava_booking_day_key( $date );
	$working_days = vava_booking_effective_working_days( $service, $shared );
	$working_hours = (array) ( $shared['working_hours'] ?? array() );
	if ( empty( $working_days[ $key ] ) ) { return array(); }
	if ( ! vava_booking_day_has_capacity( $service, $date_string ) ) { return array(); }
	$hours = (array) ( $working_hours[ $key ] ?? array() );
	$start_string = (string) ( $hours['start'] ?? '10:00' );
	$end_string = (string) ( $hours['end'] ?? '18:00' );
	$start = new DateTimeImmutable( $date_string . ' ' . $start_string, $timezone );
	$end = new DateTimeImmutable( $date_string . ' ' . $end_string, $timezone );
	$interval = max( 10, absint( $shared['slot_interval'] ?? 30 ) );
	$duration = vava_booking_effective_duration( $service, $shared );
	$slots = array();
	for ( $cursor = $start; $cursor->modify( '+' . $duration . ' minutes' ) <= $end; $cursor = $cursor->modify( '+' . $interval . ' minutes' ) ) {
		$time = $cursor->format( 'H:i' );
		if ( vava_booking_slot_is_reserved( $date_string, $time, $duration ) ) { continue; }
		$slots[] = array( 'value' => $time, 'label' => vava_booking_format_time_12h( $time ) );
	}
	return $slots;
}

function vava_booking_ajax_slots(): void {
	check_ajax_referer( 'vava_booking_frontend', 'nonce' );
	$lang = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$uid = isset( $_POST['service'] ) ? sanitize_key( wp_unslash( $_POST['service'] ) ) : '';
	$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
	$service = vava_booking_resolve_service( $uid, $lang );
	$page_id = vava_booking_page_id();
	$shared = vava_booking_shared_data( $page_id );
	if ( ! $service || ! vava_booking_service_is_available( $service, $shared ) ) { wp_send_json_error( array( 'message' => vava_booking_text_data( $page_id, $lang )['invalid_service'] ), 404 ); }
	wp_send_json_success( array( 'slots' => vava_booking_available_slots( $service, $date, $shared ) ) );
}
add_action( 'wp_ajax_vava_booking_slots', 'vava_booking_ajax_slots' );
add_action( 'wp_ajax_nopriv_vava_booking_slots', 'vava_booking_ajax_slots' );

/** Return availability for the seven calendar days currently visible. */
function vava_booking_ajax_dates(): void {
	check_ajax_referer( 'vava_booking_frontend', 'nonce' );
	$lang    = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$uid     = isset( $_POST['service'] ) ? sanitize_key( wp_unslash( $_POST['service'] ) ) : '';
	$start   = isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : '';
	$service = vava_booking_resolve_service( $uid, $lang );
	$shared  = vava_booking_shared_data( vava_booking_page_id() );
	try { $timezone = new DateTimeZone( (string) ( $shared['timezone'] ?: wp_timezone_string() ) ); } catch ( Exception $e ) { $timezone = wp_timezone(); }
	$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $start, $timezone );
	if ( ! $service || ! $date || $date->format( 'Y-m-d' ) !== $start ) { wp_send_json_error( array( 'message' => 'Invalid availability request.' ), 400 ); }
	$availability = array();
	for ( $offset = 0; $offset < 7; $offset++ ) {
		$value = $date->modify( '+' . $offset . ' days' )->format( 'Y-m-d' );
		$availability[ $value ] = ! empty( vava_booking_available_slots( $service, $value, $shared ) );
	}
	wp_send_json_success( array( 'availability' => $availability ) );
}
add_action( 'wp_ajax_vava_booking_dates', 'vava_booking_ajax_dates' );
add_action( 'wp_ajax_nopriv_vava_booking_dates', 'vava_booking_ajax_dates' );

function vava_booking_numeric_price( string $price ): float {
	$latin = strtr( $price, array( '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9' ) );
	if ( preg_match( '/([0-9][0-9,.]*)/', $latin, $match ) ) { return (float) str_replace( ',', '', $match[1] ); }
	return 0.0;
}

/** A free service completes booking without sending the customer to a payment gateway. */
function vava_booking_service_is_free( array $service ): bool {
	$price = trim( wp_strip_all_tags( (string) ( $service['price'] ?? '' ) ) );
	if ( preg_match( '/(?:مجاني(?:ة)?|free)/iu', $price ) ) { return true; }
	return '' !== $price && vava_booking_numeric_price( $price ) <= 0.0;
}

/** Format a booking price once, even when the stored price already includes its currency. */
function vava_booking_format_price_label( string $price, string $currency = '', string $lang = '' ): string {
	$price    = trim( wp_strip_all_tags( $price ) );
	$currency = trim( wp_strip_all_tags( $currency ) );
	$lang     = $lang ? ( 'en' === $lang ? 'en' : 'ar' ) : vava_current_language();

	if ( '' === $price ) { return ''; }
	if ( preg_match( '/(?:مجاني(?:ة)?|free)/iu', $price ) || ( preg_match( '/^[0٠]+(?:[.,]0+)?$/u', $price ) && vava_booking_numeric_price( $price ) <= 0.0 ) ) {
		return 'en' === $lang ? 'Free' : 'مجانية';
	}
	if ( '' === $currency ) { $currency = 'en' === $lang ? 'SAR' : 'ر.س'; }

	$patterns = array( '/\bSAR\b/iu', '/ر\s*\.?\s*س\s*\.?/u', '/ريال(?:\s+سعودي)?/u' );
	$numeric  = trim( preg_replace( $patterns, '', $price ) );
	$numeric  = trim( preg_replace( '/\s{2,}/u', ' ', $numeric ) );
	if ( '' === $numeric ) { $numeric = $price; }
	if ( preg_match( '/^[0-9٠-٩][0-9٠-٩,.]*$/u', $numeric ) ) {
		$numeric = vava_booking_format_amount( vava_booking_numeric_price( $numeric ) );
	}

	return trim( $numeric . ' ' . $currency );
}

/** Show decimals only when the amount actually contains a fractional value. */
function vava_booking_format_amount( float $amount ): string {
	$decimals = abs( $amount - round( $amount ) ) < 0.00001 ? 0 : 2;
	return number_format_i18n( $amount, $decimals );
}

function vava_booking_paymob_checkout( int $booking_id, array $service, array $customer, array $shared ) {
	$config = (array) ( $shared['paymob'] ?? array() );
	foreach ( array( 'secret_key', 'public_key', 'integration_ids' ) as $required ) {
		if ( empty( $config[ $required ] ) ) { return new WP_Error( 'paymob_not_configured', 'Paymob is not configured yet.' ); }
	}
	$base = untrailingslashit( esc_url_raw( (string) ( $config['base_url'] ?? 'https://ksa.paymob.com' ) ) );
	$amount = max( 1, (int) round( vava_booking_numeric_price( (string) $service['price'] ) * 100 ) );
	$currency = strtoupper( preg_replace( '/[^A-Za-z]/', '', (string) $service['currency'] ) );
	if ( strlen( $currency ) !== 3 ) { $currency = 'SAR'; }
	$integration_ids = array_values( array_filter( array_map( 'absint', preg_split( '/[\s,]+/', (string) $config['integration_ids'], -1, PREG_SPLIT_NO_EMPTY ) ?: array() ) ) );
	if ( ! $integration_ids ) { return new WP_Error( 'paymob_not_configured', 'Paymob payment methods are not configured.' ); }
	$names = preg_split( '/\s+/', trim( (string) $customer['name'] ), 2 );
	$first_name = (string) ( $names[0] ?? 'VAVA' );
	$last_name = (string) ( $names[1] ?? '-' );
	$country = 'EGP' === $currency ? 'EG' : ( 'SAR' === $currency ? 'SA' : 'NA' );
	$reference = 'vava-booking-' . $booking_id;
	$notification_url = add_query_arg( 'action', 'vava_paymob_webhook', admin_url( 'admin-post.php' ) );
	$redirection_url = add_query_arg(
		array( 'action' => 'vava_paymob_return', 'booking' => $booking_id, 'service' => (string) $service['uid'] ),
		admin_url( 'admin-post.php' )
	);
	$billing = array(
		'apartment' => 'NA', 'email' => (string) $customer['email'], 'floor' => 'NA', 'first_name' => $first_name,
		'street' => 'NA', 'building' => 'NA', 'phone_number' => (string) $customer['whatsapp'], 'shipping_method' => 'NA',
		'postal_code' => 'NA', 'city' => 'NA', 'country' => $country, 'last_name' => $last_name, 'state' => 'NA',
	);
	$payload = array(
		'amount' => $amount,
		'currency' => $currency,
		'payment_methods' => $integration_ids,
		'items' => array( array( 'name' => (string) $service['title'], 'amount' => $amount, 'description' => wp_strip_all_tags( (string) $service['description'] ), 'quantity' => 1 ) ),
		'billing_data' => $billing,
		'customer' => array( 'first_name' => $first_name, 'last_name' => $last_name, 'email' => (string) $customer['email'] ),
		'special_reference' => $reference,
		'notification_url' => $notification_url,
		'redirection_url' => $redirection_url,
	);
	$response = wp_remote_post(
		$base . '/v1/intention/',
		array(
			'timeout' => 30,
			'headers' => array( 'Authorization' => 'Token ' . (string) $config['secret_key'], 'Content-Type' => 'application/json' ),
			'body' => wp_json_encode( $payload ),
		)
	);
	if ( is_wp_error( $response ) ) { return $response; }
	$status_code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	$client_secret = (string) ( $body['client_secret'] ?? '' );
	if ( $status_code < 200 || $status_code >= 300 || ! $client_secret ) {
		$message = is_array( $body ) ? wp_json_encode( $body ) : 'Paymob intention creation failed.';
		return new WP_Error( 'paymob_intention_failed', $message );
	}
	$intention_id = sanitize_text_field( (string) ( $body['id'] ?? '' ) );
	$order_id = sanitize_text_field( (string) ( $body['intention_order_id'] ?? $body['order']['id'] ?? $intention_id ) );
	update_post_meta( $booking_id, '_vava_paymob_intention_id', $intention_id );
	update_post_meta( $booking_id, '_vava_paymob_order_id', $order_id );
	update_post_meta( $booking_id, '_vava_paymob_reference', $reference );
	$checkout_url = $base . '/unifiedcheckout/?publicKey=' . rawurlencode( (string) $config['public_key'] ) . '&clientSecret=' . rawurlencode( $client_secret );
	return array( 'url' => $checkout_url, 'intention_id' => $intention_id, 'order_id' => $order_id );
}

function vava_booking_private_receipt_dir(): string {
	$preferred = defined( 'VAVA_PRIVATE_STORAGE_DIR' ) && VAVA_PRIVATE_STORAGE_DIR ? trailingslashit( (string) VAVA_PRIVATE_STORAGE_DIR ) . 'booking-receipts' : trailingslashit( dirname( ABSPATH ) ) . 'vava-private-booking-receipts';
	if ( ! is_dir( $preferred ) ) { wp_mkdir_p( $preferred ); }
	$dir = is_dir( $preferred ) && is_writable( $preferred ) ? $preferred : trailingslashit( WP_CONTENT_DIR ) . 'vava-private/booking-receipts';
	if ( ! is_dir( $dir ) ) { wp_mkdir_p( $dir ); }
	return $dir;
}

function vava_booking_receipt_allowed_mimes(): array {
	return array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png' => 'image/png',
		'webp' => 'image/webp',
		'pdf' => 'application/pdf',
	);
}

/** Upload a bank receipt to WordPress uploads and create a real Media attachment. */
function vava_booking_store_bank_receipt( array $file, int $parent_post_id = 0 ) {
	if ( empty( $file['tmp_name'] ) || UPLOAD_ERR_OK !== (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
		return new WP_Error( 'receipt_required', 'يرجى رفع إيصال التحويل البنكي.' );
	}
	$size = absint( $file['size'] ?? 0 );
	if ( $size < 1 || $size > 5 * MB_IN_BYTES ) { return new WP_Error( 'receipt_size', 'حجم إيصال التحويل يجب ألا يتجاوز 5MB.' ); }
	$original = sanitize_file_name( (string) ( $file['name'] ?? 'transfer-receipt' ) );
	$allowed_check = array( 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'pdf' => 'application/pdf' );
	$checked = wp_check_filetype_and_ext( $file['tmp_name'], $original, $allowed_check );
	$ext = strtolower( (string) ( $checked['ext'] ?? '' ) );
	$mime = strtolower( (string) ( $checked['type'] ?? '' ) );
	if ( ! $ext || ! isset( $allowed_check[ $ext ] ) || $allowed_check[ $ext ] !== $mime ) { return new WP_Error( 'receipt_type', 'نوع إيصال التحويل غير مدعوم. استخدم JPG أو PNG أو WEBP أو PDF.' ); }

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$file['name'] = 'vava-transfer-' . wp_generate_password( 20, false, false ) . '.' . $ext;
	$uploaded = wp_handle_upload( $file, array( 'test_form' => false, 'mimes' => vava_booking_receipt_allowed_mimes() ) );
	if ( ! empty( $uploaded['error'] ) || empty( $uploaded['file'] ) || empty( $uploaded['url'] ) ) {
		return new WP_Error( 'receipt_upload', (string) ( $uploaded['error'] ?? 'تعذر رفع إيصال التحويل إلى الموقع.' ) );
	}
	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => (string) ( $uploaded['type'] ?? $mime ),
			'post_title' => sanitize_text_field( pathinfo( $original, PATHINFO_FILENAME ) ?: 'VAVA transfer receipt' ),
			'post_content' => '',
			'post_status' => 'inherit',
		),
		(string) $uploaded['file'],
		$parent_post_id,
		true
	);
	if ( is_wp_error( $attachment_id ) ) {
		@unlink( (string) $uploaded['file'] );
		return $attachment_id;
	}
	$metadata = wp_generate_attachment_metadata( (int) $attachment_id, (string) $uploaded['file'] );
	if ( $metadata ) { wp_update_attachment_metadata( (int) $attachment_id, $metadata ); }
	update_post_meta( (int) $attachment_id, '_vava_booking_receipt_original_name', $original );
	return array(
		'attachment_id' => (int) $attachment_id,
		'url' => esc_url_raw( (string) $uploaded['url'] ),
		'mime' => (string) ( $uploaded['type'] ?? $mime ),
		'original' => $original,
		'size' => $size,
		'uploaded_at' => current_time( 'mysql' ),
		'uploaded_by' => get_current_user_id(),
	);
}

function vava_booking_delete_receipt_attachment( array $receipt ): void {
	$attachment_id = absint( $receipt['attachment_id'] ?? 0 );
	if ( $attachment_id ) { wp_delete_attachment( $attachment_id, true ); }
}

/** Convert an existing private receipt into a WordPress Media attachment when the file still exists. */
function vava_booking_maybe_migrate_legacy_receipt( int $booking_id, array $receipt ): array {
	if ( absint( $receipt['attachment_id'] ?? 0 ) ) { return $receipt; }
	$filename = basename( (string) ( $receipt['file'] ?? '' ) );
	if ( ! $filename ) { return $receipt; }
	$legacy_path = trailingslashit( vava_booking_private_receipt_dir() ) . $filename;
	if ( ! is_file( $legacy_path ) || ! is_readable( $legacy_path ) ) { return $receipt; }
	$upload_dir = wp_upload_dir();
	if ( ! empty( $upload_dir['error'] ) ) { return $receipt; }
	$original = sanitize_file_name( (string) ( $receipt['original'] ?? $filename ) );
	$target_name = wp_unique_filename( (string) $upload_dir['path'], 'vava-transfer-' . wp_generate_password( 16, false, false ) . '-' . $original );
	$target_path = trailingslashit( (string) $upload_dir['path'] ) . $target_name;
	if ( ! @copy( $legacy_path, $target_path ) ) { return $receipt; }
	$mime = (string) ( $receipt['mime'] ?? wp_check_filetype( $target_name )['type'] ?? 'application/octet-stream' );
	$attachment_id = wp_insert_attachment( array( 'post_mime_type' => $mime, 'post_title' => sanitize_text_field( pathinfo( $original, PATHINFO_FILENAME ) ), 'post_status' => 'inherit' ), $target_path, $booking_id, true );
	if ( is_wp_error( $attachment_id ) ) { @unlink( $target_path ); return $receipt; }
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$metadata = wp_generate_attachment_metadata( (int) $attachment_id, $target_path );
	if ( $metadata ) { wp_update_attachment_metadata( (int) $attachment_id, $metadata ); }
	$receipt['attachment_id'] = (int) $attachment_id;
	$receipt['url'] = esc_url_raw( trailingslashit( (string) $upload_dir['url'] ) . $target_name );
	$receipt['migrated_at'] = current_time( 'mysql' );
	update_post_meta( $booking_id, '_vava_booking_bank_receipt', $receipt );
	return $receipt;
}

function vava_booking_get_receipt( int $booking_id, bool $migrate_legacy = true ): array {
	$receipt = (array) get_post_meta( $booking_id, '_vava_booking_bank_receipt', true );
	if ( $migrate_legacy && $receipt ) { $receipt = vava_booking_maybe_migrate_legacy_receipt( $booking_id, $receipt ); }
	$attachment_id = absint( $receipt['attachment_id'] ?? 0 );
	if ( $attachment_id ) {
		$url = wp_get_attachment_url( $attachment_id );
		if ( $url ) { $receipt['url'] = esc_url_raw( $url ); }
		$mime = get_post_mime_type( $attachment_id );
		if ( $mime ) { $receipt['mime'] = (string) $mime; }
	}
	return $receipt;
}

function vava_booking_receipt_public_url( int $booking_id ): string {
	$receipt = vava_booking_get_receipt( $booking_id, true );
	$attachment_id = absint( $receipt['attachment_id'] ?? 0 );
	return $attachment_id ? (string) wp_get_attachment_url( $attachment_id ) : '';
}

function vava_booking_bank_transfer_payload(): array {
	return array(
		'transfer_name' => isset( $_POST['bank_transfer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_transfer_name'] ) ) : '',
		'from_bank' => isset( $_POST['bank_from_bank'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_from_bank'] ) ) : '',
		'from_account' => isset( $_POST['bank_from_account'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_from_account'] ) ) : '',
		'reference' => isset( $_POST['bank_reference'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_reference'] ) ) : '',
		'transfer_date' => isset( $_POST['bank_transfer_date'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_transfer_date'] ) ) : '',
		'transfer_time' => isset( $_POST['bank_transfer_time'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_transfer_time'] ) ) : '',
		'amount' => isset( $_POST['bank_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_amount'] ) ) : '',
		'notes' => isset( $_POST['bank_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bank_notes'] ) ) : '',
	);
}

function vava_booking_ajax_submit(): void {
	check_ajax_referer( 'vava_booking_frontend', 'nonce' );
	$page_id = vava_booking_page_id();
	$lang = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$text = vava_booking_text_data( $page_id, $lang );
	$shared = vava_booking_shared_data( $page_id );
	$uid = isset( $_POST['service'] ) ? sanitize_key( wp_unslash( $_POST['service'] ) ) : '';
	$service = vava_booking_resolve_service( $uid, $lang );
	if ( ! $service || ! vava_booking_service_is_available( $service, $shared ) ) { wp_send_json_error( array( 'message' => $text['invalid_service'] ), 400 ); }
	$whatsapp_country = isset( $_POST['whatsapp_country'] ) ? strtoupper( sanitize_key( wp_unslash( $_POST['whatsapp_country'] ) ) ) : '';
	$whatsapp_local = isset( $_POST['whatsapp_local'] ) ? sanitize_text_field( wp_unslash( $_POST['whatsapp_local'] ) ) : '';
	$whatsapp_fallback = isset( $_POST['whatsapp'] ) ? sanitize_text_field( wp_unslash( $_POST['whatsapp'] ) ) : '';
	$customer = array(
		'name' => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
		'email' => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
		'whatsapp' => vava_booking_normalize_whatsapp( $whatsapp_country, $whatsapp_local, $whatsapp_fallback ),
		'whatsapp_country' => $whatsapp_country,
		'whatsapp_local' => vava_booking_phone_digits( $whatsapp_local ),
		'previous' => isset( $_POST['previous'] ) ? sanitize_key( wp_unslash( $_POST['previous'] ) ) : '',
		'notes' => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
	);
	$terms_accepted = ! empty( $_POST['terms'] );
	$invalid_customer = ! $terms_accepted;
	foreach ( array( 'name','email','whatsapp','previous','notes' ) as $field_key ) {
		if ( ! empty( $text['fields'][ $field_key ]['required'] ) && '' === trim( (string) ( $customer[ $field_key ] ?? '' ) ) ) { $invalid_customer = true; }
	}
	if ( $customer['email'] && ! is_email( $customer['email'] ) ) { $invalid_customer = true; }
	if ( $invalid_customer ) { wp_send_json_error( array( 'message' => $text['validation_error'] ), 422 ); }
	$questionnaire_data = array();
	if ( function_exists( 'vava_booking_questionnaire_type_for_booking' ) ) {
		$required_questionnaire = vava_booking_questionnaire_type_for_booking( $service, (string) $customer['previous'], $page_id );
		$submitted_questionnaire = isset( $_POST['questionnaire_type'] ) ? sanitize_key( wp_unslash( $_POST['questionnaire_type'] ) ) : 'none';
		if ( 'none' !== $required_questionnaire ) {
			if ( $submitted_questionnaire !== $required_questionnaire ) { wp_send_json_error( array( 'message' => 'يرجى استكمال الاستبيان المطلوب قبل اختيار الموعد.' ), 422 ); }
			$raw_questionnaire_answers = isset( $_POST['questionnaire_answers'] ) && is_array( $_POST['questionnaire_answers'] ) ? $_POST['questionnaire_answers'] : array();
			$questionnaire_data = vava_booking_questionnaire_sanitize_answers( $required_questionnaire, $raw_questionnaire_answers, $lang, true );
			if ( is_wp_error( $questionnaire_data ) ) { wp_send_json_error( array( 'message' => $questionnaire_data->get_error_message(), 'fields' => $questionnaire_data->get_error_data()['fields'] ?? array() ), 422 ); }
		} elseif ( 'none' !== $submitted_questionnaire ) {
			$submitted_questionnaire = 'none';
		}
	}
	$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
	$time = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '';
	$available = array_column( vava_booking_available_slots( $service, $date, $shared ), 'value' );
	if ( ! in_array( $time, $available, true ) ) { wp_send_json_error( array( 'message' => $text['slot_unavailable'] ), 409 ); }
	$is_free_service = vava_booking_service_is_free( $service );
	$method = $is_free_service ? 'free' : ( isset( $_POST['payment_method'] ) ? sanitize_key( wp_unslash( $_POST['payment_method'] ) ) : 'paymob' );
	if ( ! $is_free_service && empty( $shared['payment_methods'][ $method ] ) ) { wp_send_json_error( array( 'message' => $text['validation_error'] ), 422 ); }
	if ( 'paymob' === $method && ! vava_booking_paymob_is_ready( $shared ) ) { wp_send_json_error( array( 'message' => 'Paymob is enabled but its configuration is incomplete.' ), 422 ); }
	if ( 'bank' === $method && ! vava_booking_bank_is_ready( $shared ) ) { wp_send_json_error( array( 'message' => 'بيانات الحساب البنكي غير مكتملة في إعدادات الحجز.' ), 422 ); }
	if ( 'paymob' === $method && ( ! $customer['name'] || ! is_email( $customer['email'] ) || ! $customer['whatsapp'] ) ) { wp_send_json_error( array( 'message' => $text['validation_error'] ), 422 ); }

	$bank_transfer = array();
	$bank_receipt = array();
	if ( 'bank' === $method ) {
		$bank_transfer = vava_booking_bank_transfer_payload();
		foreach ( array( 'transfer_name', 'from_bank', 'from_account', 'reference', 'transfer_date', 'transfer_time', 'amount' ) as $required_key ) {
			if ( '' === trim( (string) ( $bank_transfer[ $required_key ] ?? '' ) ) ) { wp_send_json_error( array( 'message' => 'يرجى استكمال بيانات التحويل البنكي المطلوبة.' ), 422 ); }
		}
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $bank_transfer['transfer_date'] ) ) { wp_send_json_error( array( 'message' => 'تاريخ التحويل البنكي غير صحيح.' ), 422 ); }
		if ( ! preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string) $bank_transfer['transfer_time'] ) ) { wp_send_json_error( array( 'message' => 'وقت التحويل البنكي غير صحيح.' ), 422 ); }
		$file = isset( $_FILES['bank_receipt'] ) && is_array( $_FILES['bank_receipt'] ) ? $_FILES['bank_receipt'] : array();
		$bank_receipt = vava_booking_store_bank_receipt( $file, 0 );
		if ( is_wp_error( $bank_receipt ) ) { wp_send_json_error( array( 'message' => $bank_receipt->get_error_message() ), 422 ); }
	}

	$duration = vava_booking_effective_duration( $service, $shared );
	$booking_id = wp_insert_post( array( 'post_type' => 'vava_booking', 'post_status' => 'publish', 'post_title' => sprintf( '%s — %s — %s %s', $customer['name'], $service['title'], $date, $time ) ), true );
	if ( is_wp_error( $booking_id ) ) {
		vava_booking_delete_receipt_attachment( $bank_receipt );
		wp_send_json_error( array( 'message' => $booking_id->get_error_message() ), 500 );
	}
	$status = 'free' === $method ? 'confirmed' : ( 'paymob' === $method ? 'pending_payment' : ( 'bank' === $method ? 'pending_bank_review' : 'pending' ) );
	$payment_status = 'free' === $method ? 'paid' : ( 'paymob' === $method ? 'pending' : ( 'bank' === $method ? 'pending_bank_review' : 'unpaid' ) );
	$meta = array(
		'_vava_booking_status' => $status, '_vava_booking_payment_status' => $payment_status, '_vava_booking_service_uid' => $service['uid'], '_vava_booking_service_category' => vava_booking_service_category( $service ), '_vava_booking_service_kind' => $service['kind'], '_vava_booking_service_title' => $service['title'], '_vava_booking_service_image_id' => absint( $service['image_id'] ?? 0 ), '_vava_booking_service_price' => $service['price'], '_vava_booking_service_currency' => $service['currency'], '_vava_booking_date' => $date, '_vava_booking_time' => $time, '_vava_booking_duration' => $duration, '_vava_booking_customer' => $customer, '_vava_booking_customer_email' => strtolower( (string) $customer['email'] ), '_vava_booking_customer_phone' => preg_replace( '/\D+/', '', (string) $customer['whatsapp'] ), '_vava_booking_payment_method' => $method, '_vava_booking_language' => $lang, '_vava_booking_created_at' => current_time( 'mysql' ),
	);
	if ( 'bank' === $method ) { $meta['_vava_booking_bank_transfer'] = $bank_transfer; $meta['_vava_booking_bank_receipt'] = $bank_receipt; }
	foreach ( $meta as $key => $value ) { update_post_meta( $booking_id, $key, $value ); }
	if ( ! empty( $questionnaire_data ) ) { update_post_meta( $booking_id, '_vava_booking_questionnaire', $questionnaire_data ); }
	if ( 'bank' === $method && ! empty( $bank_receipt['attachment_id'] ) ) {
		wp_update_post( array( 'ID' => absint( $bank_receipt['attachment_id'] ), 'post_parent' => $booking_id ) );
	}
	if ( 'paymob' !== $method && function_exists( 'vava_customer_prepare_account_for_booking' ) ) {
		vava_customer_prepare_account_for_booking( (int) $booking_id, $customer, $lang );
	}
	if ( 'paymob' === $method ) {
		$checkout = vava_booking_paymob_checkout( $booking_id, $service, $customer, $shared );
		if ( is_wp_error( $checkout ) ) { update_post_meta( $booking_id, '_vava_booking_status', 'payment_error' ); update_post_meta( $booking_id, '_vava_booking_payment_status', 'failed' ); wp_send_json_error( array( 'message' => $checkout->get_error_message(), 'bookingId' => $booking_id ), 502 ); }
		wp_send_json_success( array( 'redirect' => $checkout['url'], 'bookingId' => $booking_id ) );
	}
	if ( 'bank' === $method ) {
		vava_booking_send_bank_received( $booking_id );
		wp_send_json_success( array( 'title' => $text['bank_received_title'], 'message' => $text['bank_received_message'], 'bookingId' => $booking_id, 'status' => $status, 'myBookingsUrl' => vava_booking_my_bookings_url( $lang ) ) );
	}
	vava_booking_send_confirmation( $booking_id );
	wp_send_json_success( array( 'title' => $text['success_title'], 'message' => $text['success_message'], 'bookingId' => $booking_id, 'status' => $status, 'myBookingsUrl' => vava_booking_my_bookings_url( $lang ) ) );
}
add_action( 'wp_ajax_vava_booking_submit', 'vava_booking_ajax_submit' );
add_action( 'wp_ajax_nopriv_vava_booking_submit', 'vava_booking_ajax_submit' );

/** Notify the customer and site owner that a bank transfer awaits review. */
function vava_booking_send_bank_received( int $booking_id ): void {
	if ( function_exists( 'vava_mail_notifications_enabled' ) && ! vava_mail_notifications_enabled( 'bookings' ) ) { return; }
	if ( ! $booking_id || get_post_meta( $booking_id, '_vava_booking_bank_received_notification_sent', true ) ) { return; }
	vava_booking_send_details_email( $booking_id, false, false );
	$admin_email = sanitize_email( (string) get_option( 'admin_email' ) );
	if ( $admin_email ) {
		$customer = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
		$message = "Bank transfer awaiting review.\nBooking #" . $booking_id . "\nCustomer: " . (string) ( $customer['name'] ?? '' ) . "\nDuration: " . vava_booking_display_duration_for_booking( $booking_id, 'en' ) . "\n" . admin_url( 'edit.php?post_type=vava_booking&vava_booking_open=' . $booking_id );
		wp_mail( $admin_email, sprintf( 'VAVA Bank Transfer #%d — Review required', $booking_id ), $message );
	}
	update_post_meta( $booking_id, '_vava_booking_bank_received_notification_sent', current_time( 'mysql' ) );
}

function vava_booking_send_bank_rejected( int $booking_id, string $note = '' ): void {
	if ( function_exists( 'vava_mail_notifications_enabled' ) && ! vava_mail_notifications_enabled( 'bookings' ) ) { return; }
	$customer = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
	$email = sanitize_email( (string) ( $customer['email'] ?? '' ) );
	if ( ! $email ) { return; }
	$lang = 'en' === get_post_meta( $booking_id, '_vava_booking_language', true ) ? 'en' : 'ar';
	$subject = 'en' === $lang ? 'Bank transfer review update' : 'تحديث مراجعة التحويل البنكي';
	$message = 'en' === $lang ? 'The bank transfer attached to your VAVA booking could not be approved.' : 'تعذر اعتماد التحويل البنكي المرفق بحجز VAVA.';
	if ( $note ) { $message .= "\n\n" . ( 'en' === $lang ? 'Review note: ' : 'ملاحظة المراجعة: ' ) . $note; }
	$message .= "\n\n" . ( 'en' === $lang ? 'Booking number: #' : 'رقم الحجز: #' ) . $booking_id;
	$message .= "\n\n" . ( 'en' === $lang ? 'Upload a replacement receipt: ' : 'رفع إيصال بديل: ' ) . ( function_exists( 'vava_customer_account_url' ) ? vava_customer_account_url( $lang ) : vava_booking_my_bookings_url( $lang ) );
	wp_mail( $email, $subject, $message );
}


/** V4R9 — email-ready booking payload, refunds and safe admin utilities. */
function vava_booking_refund_status_label( string $status ): string {
	$labels = array(
		'' => 'غير مطلوب',
		'pending' => 'بانتظار إرجاع المبلغ',
		'partial' => 'تم رد جزء من المبلغ',
		'completed' => 'تم رد المبلغ',
	);
	return $labels[ $status ] ?? $status;
}

function vava_booking_refund_status( int $booking_id ): string {
	return sanitize_key( (string) get_post_meta( $booking_id, '_vava_booking_refund_status', true ) );
}

function vava_booking_paid_or_refunding( int $booking_id ): bool {
	$payment = vava_booking_effective_payment_status( $booking_id );
	if ( in_array( $payment, array( 'paid', 'refund_pending', 'partially_refunded', 'refunded' ), true ) || (bool) get_post_meta( $booking_id, '_vava_booking_paid_at', true ) ) {
		return true;
	}
	/* Legacy bookings approved before V4R9 may not have _vava_booking_paid_at. */
	foreach ( vava_booking_action_log( $booking_id ) as $entry ) {
		if ( 'approve_bank' === (string) ( $entry['action'] ?? '' ) || 'paid' === (string) ( $entry['new_payment_status'] ?? '' ) ) {
			return true;
		}
	}
	return false;
}

function vava_booking_refund_remaining( int $booking_id ): float {
	$total = (float) vava_booking_numeric_price( (string) get_post_meta( $booking_id, '_vava_booking_service_price', true ) );
	$refunded = (float) get_post_meta( $booking_id, '_vava_booking_refunded_total', true );
	return max( 0, round( $total - $refunded, 2 ) );
}

function vava_booking_email_public_url( string $url ): string {
	$url = trim( $url );
	if ( ! $url ) { return ''; }
	$parts = wp_parse_url( $url );
	if ( ! empty( $parts['path'] ) ) {
		$site_host = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
		$url_host = (string) ( $parts['host'] ?? '' );
		if ( ! $url_host || in_array( strtolower( $url_host ), array( 'localhost', '127.0.0.1' ), true ) || ( $site_host && strtolower( $site_host ) !== strtolower( $url_host ) ) ) {
			$url = home_url( (string) $parts['path'] );
		}
	}
	return set_url_scheme( $url, 'https' );
}

function vava_booking_email_logo_url(): string {
	/* Never use a stale custom-logo URL from the old localhost database. */
	return vava_booking_email_public_url( trailingslashit( get_template_directory_uri() ) . 'assets/images/vava-logo.png' );
}

function vava_booking_email_service_image_url( int $booking_id ): string {
	$image_id = absint( get_post_meta( $booking_id, '_vava_booking_service_image_id', true ) );
	$lang     = 'en' === get_post_meta( $booking_id, '_vava_booking_language', true ) ? 'en' : 'ar';
	if ( ! $image_id && function_exists( 'vava_booking_resolve_service' ) ) {
		$uid     = (string) get_post_meta( $booking_id, '_vava_booking_service_uid', true );
		$service = $uid ? vava_booking_resolve_service( $uid, $lang ) : null;
		if ( is_array( $service ) ) { $image_id = absint( $service['image_id'] ?? 0 ); }
	}
	$url = $image_id ? (string) wp_get_attachment_image_url( $image_id, 'large' ) : '';
	if ( ! $url ) {
		$url = trailingslashit( get_template_directory_uri() ) . 'assets/images/paths-hero.webp';
	}
	return vava_booking_email_public_url( $url );
}

function vava_booking_email_html( int $booking_id, string $lang = 'ar' ): string {
	$is_en    = 'en' === $lang;
	$dir      = $is_en ? 'ltr' : 'rtl';
	$align    = $is_en ? 'left' : 'right';
	$opposite = $is_en ? 'right' : 'left';
	$customer = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
	$service  = (string) get_post_meta( $booking_id, '_vava_booking_service_title', true );
	$date     = (string) get_post_meta( $booking_id, '_vava_booking_date', true );
	$time     = vava_booking_format_time_12h( (string) get_post_meta( $booking_id, '_vava_booking_time', true ) );
	$duration = vava_booking_display_duration_for_booking( $booking_id, $lang );
	$method   = (string) get_post_meta( $booking_id, '_vava_booking_payment_method', true );
	$status   = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
	$payment  = vava_booking_effective_payment_status( $booking_id );
	$price    = vava_booking_format_price_label( (string) get_post_meta( $booking_id, '_vava_booking_service_price', true ), (string) get_post_meta( $booking_id, '_vava_booking_service_currency', true ), $lang );
	$account_url = function_exists( 'vava_customer_account_url' ) ? vava_customer_account_url( $lang ) : vava_booking_my_bookings_url( $lang );
	$image       = vava_booking_email_service_image_url( $booking_id );
	$support_email = sanitize_email( (string) get_option( 'admin_email' ) );
	$labels = $is_en ? array(
		'web'=>'Web version','title'=>'Your booking details are ready','hello'=>'Hello','intro'=>'Thank you for booking with VAVA Living. Your booking was received successfully, and we look forward to welcoming you.','number'=>'Booking number','status'=>'Booking status','date'=>'Booking date','time'=>'Booking time','duration'=>'Duration','amount'=>'Booking total','details'=>'Booking details','service'=>'Service','customer'=>'Customer information','name'=>'Name','email'=>'Email','phone'=>'WhatsApp','notes'=>'Customer notes','method'=>'Payment method','payment_status'=>'Payment status','cta'=>'View booking details','manage'=>'Manage your booking, upload a receipt, or request cancellation securely through your VAVA account.','waiting'=>'We are looking forward to welcoming you','support_title'=>'Need help?','support'=>'Our team is happy to help at any time.','minutes'=>'minutes','no_notes'=>'No notes','ready'=>'Your VAVA booking is ready','footer'=>'A calmer, more balanced journey starts here.'
	) : array(
		'web'=>'نسخة ويب','title'=>'تفاصيل حجزك جاهزة','hello'=>'مرحبًا','intro'=>'شكرًا لحجزك مع VAVA Living. تم استلام حجزك بنجاح، ونحن في انتظارك!','number'=>'رقم الحجز','status'=>'حالة الحجز','date'=>'تاريخ الحجز','time'=>'وقت الحجز','duration'=>'المدة','amount'=>'إجمالي الحجز','details'=>'تفاصيل الحجز','service'=>'الخدمة','customer'=>'معلومات العميل','name'=>'الاسم','email'=>'البريد الإلكتروني','phone'=>'رقم الجوال','notes'=>'ملاحظات العميل','method'=>'طريقة الدفع','payment_status'=>'حالة الدفع','cta'=>'عرض تفاصيل الحجز','manage'=>'يمكنك إدارة حجزك ورفع الإيصال أو طلب الإلغاء بأمان من خلال حسابك في VAVA Living.','waiting'=>'نحن بانتظارك','support_title'=>'تحتاج لمساعدة؟','support'=>'فريقنا سعيد بمساعدتك في أي وقت.','minutes'=>'دقيقة','no_notes'=>'لا توجد ملاحظات','ready'=>'حجزك مع VAVA أصبح جاهزًا','footer'=>'رحلتك نحو توازن وهدوء أكبر تبدأ من هنا.'
	);
	$e = static function( $value ): string { return esc_html( (string) $value ); };
	$customer_notes = trim( (string) ( $customer['notes'] ?? '' ) );
	$summary = array(
		array( $labels['status'], vava_booking_status_label( $status ) ),
		array( $labels['date'], $date ),
		array( $labels['time'], $time, 'ltr' ),
		array( $labels['amount'], $price ),
	);
	$booking_rows = array(
		array( $labels['service'], $service, '' ),
		array( $labels['duration'], $duration, '' ),
		array( $labels['method'], vava_booking_payment_method_label( $method ), '' ),
		array( $labels['payment_status'], vava_booking_payment_status_label( $payment ), '' ),
		array( $labels['amount'], $price, '' ),
	);
	$customer_rows = array(
		array( $labels['name'], (string) ( $customer['name'] ?? '' ), '' ),
		array( $labels['email'], (string) ( $customer['email'] ?? '' ), 'ltr' ),
		array( $labels['phone'], (string) ( $customer['whatsapp'] ?? '' ), 'ltr' ),
		array( $labels['notes'], $customer_notes ?: $labels['no_notes'], '' ),
	);
	ob_start();
	?>
<!doctype html>
<html dir="<?php echo esc_attr( $dir ); ?>" lang="<?php echo esc_attr( $lang ); ?>">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light only"></head>
<body style="margin:0;padding:0;background:#f4f0e8;font-family:Tahoma,Arial,sans-serif;color:#4e4b43;direction:<?php echo esc_attr( $dir ); ?>;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin:0;padding:0;background:#f4f0e8;"><tr><td align="center" style="padding:28px 10px;">
<table role="presentation" width="720" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:720px;margin:0 auto;background:#fffdf9;border:1px solid #ddd5c7;border-radius:18px;overflow:hidden;">
<tr><td style="padding:18px 28px 5px;text-align:<?php echo esc_attr( $opposite ); ?>;font-size:11px;color:#90897e;"><?php echo $e( $labels['web'] ); ?></td></tr>
<tr><td align="center" style="padding:0 20px 20px;"><div style="font-family:Georgia,'Times New Roman',serif;font-size:34px;line-height:1;letter-spacing:3px;color:#747a4e;">VAVA</div><div style="margin-top:5px;font-size:9px;letter-spacing:5px;color:#9b9488;">LIVING</div></td></tr>
<tr><td style="padding:0 28px 22px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border-collapse:separate;border-spacing:0;border:1px solid #e3dcd0;border-radius:16px;overflow:hidden;">
<tr>
<?php if ( ! $is_en ) : ?>
<td width="48%" valign="top" style="padding:0;background:#eee9df;"><img src="<?php echo esc_url( $image ); ?>" width="318" height="286" alt="<?php echo esc_attr( $service ); ?>" style="display:block;width:100%;height:286px;object-fit:cover;border:0;outline:none;text-decoration:none;"></td>
<td width="52%" valign="middle" dir="rtl" style="padding:28px 26px;text-align:right;background:#fffdf9;">
<?php else : ?>
<td width="52%" valign="middle" dir="ltr" style="padding:28px 26px;text-align:left;background:#fffdf9;">
<?php endif; ?>
<div style="font-size:13px;color:#777b54;margin-bottom:7px;"><?php echo $e( $labels['hello'] . ' ' . ( $customer['name'] ?? '' ) ); ?></div>
<h1 style="margin:0 0 10px;font-size:29px;line-height:1.35;font-weight:600;color:#45433d;"><?php echo $e( $labels['title'] ); ?></h1>
<p style="margin:0;line-height:1.9;color:#777168;font-size:13px;"><?php echo $e( $labels['intro'] ); ?></p>
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:18px;border:1px solid #e4d8cb;border-radius:13px;"><tr><td style="padding:9px 26px;text-align:center;"><span style="display:block;font-size:10px;color:#8e887d;"><?php echo $e( $labels['number'] ); ?></span><strong style="display:block;font-size:27px;color:#d87b61;direction:ltr;">#<?php echo $e( $booking_id ); ?></strong></td></tr></table>
<?php if ( ! $is_en ) : ?></td><?php else : ?></td><td width="48%" valign="top" style="padding:0;background:#eee9df;"><img src="<?php echo esc_url( $image ); ?>" width="318" height="286" alt="<?php echo esc_attr( $service ); ?>" style="display:block;width:100%;height:286px;object-fit:cover;border:0;outline:none;text-decoration:none;"></td><?php endif; ?>
</tr></table></td></tr>
<tr><td style="padding:0 28px 20px;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border:1px solid #dcd5c8;border-radius:14px;overflow:hidden;"><tr>
	<?php foreach ( $summary as $index => $cell ) : ?><td width="25%" valign="top" style="padding:16px 6px;text-align:center;<?php echo $index ? ( $is_en ? 'border-left:1px solid #ece6dd;' : 'border-right:1px solid #ece6dd;' ) : ''; ?>"><span style="display:block;margin-bottom:6px;font-size:10px;color:#8e887d;"><?php echo $e( $cell[0] ); ?></span><strong dir="<?php echo esc_attr( (string) ( $cell[2] ?? $dir ) ); ?>" style="font-size:12px;color:#5d6243;"><?php echo $e( $cell[1] ); ?></strong></td><?php endforeach; ?>
</tr></table></td></tr>
<tr><td style="padding:0 28px 20px;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
<td width="49%" valign="top" style="border:1px solid #e2dbd0;border-radius:14px;padding:19px;"><h3 style="margin:0 0 13px;font-size:17px;color:#555246;text-align:<?php echo esc_attr( $align ); ?>;"><?php echo $e( $labels['details'] ); ?></h3><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
<?php foreach ( $booking_rows as $row ) : ?><tr><td style="padding:8px 0;border-bottom:1px solid #eee8df;color:#8a8479;font-size:11px;text-align:<?php echo esc_attr( $align ); ?>;"><?php echo $e( $row[0] ); ?></td><td style="padding:8px 0;border-bottom:1px solid #eee8df;color:#4f4c44;font-size:11px;font-weight:bold;text-align:<?php echo esc_attr( $opposite ); ?>;"><?php echo $e( $row[1] ); ?></td></tr><?php endforeach; ?>
</table></td><td width="2%"></td>
<td width="49%" valign="top" style="border:1px solid #e2dbd0;border-radius:14px;padding:19px;"><h3 style="margin:0 0 13px;font-size:17px;color:#555246;text-align:<?php echo esc_attr( $align ); ?>;"><?php echo $e( $labels['customer'] ); ?></h3><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
<?php foreach ( $customer_rows as $row ) : ?><tr><td style="padding:8px 0;border-bottom:1px solid #eee8df;color:#8a8479;font-size:11px;text-align:<?php echo esc_attr( $align ); ?>;"><?php echo $e( $row[0] ); ?></td><td dir="<?php echo esc_attr( $row[2] ?: $dir ); ?>" style="padding:8px 0;border-bottom:1px solid #eee8df;color:#4f4c44;font-size:11px;text-align:<?php echo esc_attr( $opposite ); ?>;"><?php echo $e( $row[1] ); ?></td></tr><?php endforeach; ?>
</table></td></tr></table></td></tr>
<tr><td style="padding:0 28px 18px;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f6f4ed;border:1px solid #e5dfd4;border-radius:14px;"><tr><td style="padding:17px 20px;text-align:center;"><strong style="display:block;color:#777b54;font-size:15px;margin-bottom:5px;"><?php echo $e( $labels['waiting'] ); ?></strong><span style="color:#847f75;font-size:11px;line-height:1.7;"><?php echo $e( $labels['support'] ); ?></span></td></tr></table></td></tr>
<tr><td style="padding:0 28px 12px;"><a href="<?php echo esc_url( $account_url ); ?>" style="display:block;padding:15px 22px;border-radius:12px;background:#747a4e;color:#ffffff;text-align:center;text-decoration:none;font-weight:bold;font-size:14px;"><?php echo $e( $labels['cta'] ); ?></a><p style="margin:11px 0 0;text-align:center;color:#8b857b;font-size:10px;line-height:1.7;"><?php echo $e( $labels['manage'] ); ?></p></td></tr>
<tr><td style="padding:17px 28px 21px;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e4ddd2;border-radius:14px;"><tr><td style="padding:17px;text-align:center;"><strong style="display:block;margin-bottom:5px;color:#5d6243;font-size:14px;"><?php echo $e( $labels['support_title'] ); ?></strong><span style="color:#817b72;font-size:11px;"><?php echo $e( $labels['support'] ); ?><?php if ( $support_email ) : ?><br><a href="mailto:<?php echo esc_attr( $support_email ); ?>" style="color:#747a4e;text-decoration:none;"><?php echo $e( $support_email ); ?></a><?php endif; ?></span></td></tr></table></td></tr>
<tr><td style="padding:14px 20px;text-align:center;background:#747a4e;color:#ffffff;font-size:10px;">VAVA Living &nbsp;—&nbsp; <?php echo $e( $labels['footer'] ); ?></td></tr>
<tr><td style="padding:10px 20px;text-align:center;background:#fffdf9;color:#999287;font-size:9px;">© <?php echo $e( wp_date( 'Y' ) ); ?> VAVA Living</td></tr>
</table></td></tr></table>
</body></html>
	<?php
	return (string) ob_get_clean();
}

function vava_booking_send_details_email( int $booking_id, bool $force = false, bool $send_admin = true ) {
	if ( function_exists( 'vava_mail_notifications_enabled' ) && ! vava_mail_notifications_enabled( 'bookings' ) ) { return true; }
	if ( ! $booking_id || 'vava_booking' !== get_post_type( $booking_id ) ) { return new WP_Error( 'invalid_booking', 'الحجز غير موجود.' ); }
	if ( ! $force && get_post_meta( $booking_id, '_vava_booking_notification_sent', true ) ) { return true; }
	$customer = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
	$email = sanitize_email( (string) ( $customer['email'] ?? '' ) );
	if ( ! $email || ! is_email( $email ) ) { return new WP_Error( 'invalid_email', 'لا يوجد بريد عميل صالح داخل الحجز.' ); }
	$lang = 'en' === get_post_meta( $booking_id, '_vava_booking_language', true ) ? 'en' : 'ar';
	$subject = 'en' === $lang ? 'Your VAVA booking details #' . $booking_id : 'تفاصيل حجزك مع VAVA #' . $booking_id;
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	$sent = wp_mail( $email, $subject, vava_booking_email_html( $booking_id, $lang ), $headers );
	update_post_meta( $booking_id, '_vava_booking_notification_sent', $sent ? 'sent' : 'attempted' );
	if ( $force ) {
		update_post_meta( $booking_id, '_vava_booking_last_details_email_sent_at', current_time( 'mysql' ) );
		update_post_meta( $booking_id, '_vava_booking_last_details_email_sent_by', get_current_user_id() );
	}
	if ( $send_admin ) {
		$admin_email = sanitize_email( (string) get_option( 'admin_email' ) );
		if ( $admin_email && $admin_email !== $email ) {
			wp_mail( $admin_email, sprintf( 'VAVA Booking #%d — %s', $booking_id, (string) get_post_meta( $booking_id, '_vava_booking_service_title', true ) ), function_exists( 'vava_booking_questionnaire_admin_email_html' ) ? vava_booking_questionnaire_admin_email_html( $booking_id ) : vava_booking_email_html( $booking_id, $lang ), $headers );
		}
	}
	return $sent ? true : new WP_Error( 'email_failed', 'تعذر إرسال البريد. راجع إعدادات SMTP.' );
}

function vava_booking_send_refund_update( int $booking_id, float $amount, bool $completed ): void {
	$is_digital = function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( $booking_id );
	$channel    = $is_digital ? 'products' : 'bookings';
	if ( function_exists( 'vava_mail_notifications_enabled' ) && ! vava_mail_notifications_enabled( $channel ) ) { return; }
	$customer = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
	$email    = sanitize_email( (string) ( $customer['email'] ?? '' ) );
	if ( ! is_email( $email ) ) { return; }
	$lang = 'en' === get_post_meta( $booking_id, '_vava_booking_language', true ) ? 'en' : 'ar';
	if ( $is_digital ) {
		$subject = 'en' === $lang ? 'VAVA digital order refund update #' . $booking_id : 'تحديث استرداد مبلغ طلب المنتج #' . $booking_id;
		$message = 'en' === $lang
			? sprintf( 'A refund of %.2f has been recorded for digital order #%d. Refund status: %s.', $amount, $booking_id, $completed ? 'completed' : 'partially refunded' )
			: sprintf( 'تم تسجيل استرداد مبلغ %.2f لطلب المنتج #%d. حالة الاسترداد: %s.', $amount, $booking_id, $completed ? 'تم رد المبلغ بالكامل وإيقاف الوصول' : 'استرداد جزئي' );
	} else {
		$subject = 'en' === $lang ? 'VAVA refund update #' . $booking_id : 'تحديث استرداد مبلغ الحجز #' . $booking_id;
		$message = 'en' === $lang
			? sprintf( 'A refund of %.2f has been recorded for booking #%d. Refund status: %s.', $amount, $booking_id, $completed ? 'completed' : 'partially refunded' )
			: sprintf( 'تم تسجيل استرداد مبلغ %.2f للحجز #%d. حالة الاسترداد: %s.', $amount, $booking_id, $completed ? 'تم رد المبلغ' : 'استرداد جزئي' );
	}
	wp_mail( $email, $subject, $message );
}

/** Send the initial booking-details email after a non-card booking or successful Paymob payment. */
function vava_booking_send_confirmation( int $booking_id ): void {
	vava_booking_send_details_email( $booking_id, false, true );
}

function vava_booking_paymob_scalar( $value ): string {
	if ( is_bool( $value ) ) { return $value ? 'true' : 'false'; }
	return (string) $value;
}

function vava_booking_verify_paymob_hmac( array $obj, string $received_hmac, string $secret ): bool {
	if ( ! $secret || ! $received_hmac ) { return false; }
	$order = (array) ( $obj['order'] ?? array() );
	$source = (array) ( $obj['source_data'] ?? array() );
	$values = array(
		$obj['amount_cents'] ?? '', $obj['created_at'] ?? '', $obj['currency'] ?? '', $obj['error_occured'] ?? '',
		$obj['has_parent_transaction'] ?? '', $obj['id'] ?? '', $obj['integration_id'] ?? '', $obj['is_3d_secure'] ?? '',
		$obj['is_auth'] ?? '', $obj['is_capture'] ?? '', $obj['is_refunded'] ?? '', $obj['is_standalone_payment'] ?? '',
		$obj['is_voided'] ?? '', $order['id'] ?? '', $obj['owner'] ?? '', $obj['pending'] ?? '',
		$source['pan'] ?? '', $source['sub_type'] ?? '', $source['type'] ?? '', $obj['success'] ?? '',
	);
	$string = implode( '', array_map( 'vava_booking_paymob_scalar', $values ) );
	return hash_equals( strtolower( $received_hmac ), hash_hmac( 'sha512', $string, $secret ) );
}

function vava_booking_find_by_paymob_order( string $order_id, string $reference = '' ): int {
	if ( $order_id ) {
		$found = get_posts( array( 'post_type' => 'vava_booking', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_vava_paymob_order_id', 'meta_value' => $order_id, 'no_found_rows' => true ) );
		if ( ! empty( $found[0] ) ) { return absint( $found[0] ); }
	}
	if ( preg_match( '/vava-booking-(\d+)/', $reference, $match ) ) { return absint( $match[1] ); }
	return 0;
}

/** HMAC-verified server callback: this is the payment-status source of truth. */
function vava_booking_paymob_webhook(): void {
	$page_id = vava_booking_page_id();
	$shared = vava_booking_shared_data( $page_id );
	$raw = file_get_contents( 'php://input' );
	$body = json_decode( (string) $raw, true );
	$body = is_array( $body ) ? $body : array();
	$obj = isset( $body['obj'] ) && is_array( $body['obj'] ) ? $body['obj'] : $body;
	$received_hmac = isset( $_GET['hmac'] ) ? sanitize_text_field( wp_unslash( $_GET['hmac'] ) ) : sanitize_text_field( (string) ( $body['hmac'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$secret = (string) ( $shared['paymob']['hmac_secret'] ?? '' );
	if ( ! vava_booking_verify_paymob_hmac( $obj, $received_hmac, $secret ) ) { status_header( 403 ); wp_send_json_error( array( 'message' => 'Invalid HMAC' ), 403 ); }
	$order = (array) ( $obj['order'] ?? array() );
	$order_id = sanitize_text_field( (string) ( $order['id'] ?? '' ) );
	$reference = sanitize_text_field( (string) ( $order['merchant_order_id'] ?? $obj['special_reference'] ?? '' ) );
	$booking_id = vava_booking_find_by_paymob_order( $order_id, $reference );
	$success = ! empty( $obj['success'] ) && empty( $obj['pending'] ) && empty( $obj['error_occured'] );
	if ( $booking_id ) {
		update_post_meta( $booking_id, '_vava_booking_status', $success ? 'confirmed' : 'payment_failed' );
		update_post_meta( $booking_id, '_vava_booking_payment_status', $success ? 'paid' : 'failed' );
		update_post_meta( $booking_id, '_vava_paymob_transaction_id', sanitize_text_field( (string) ( $obj['id'] ?? '' ) ) );
		update_post_meta( $booking_id, '_vava_paymob_callback', $obj );
		if ( $success ) {
			if ( function_exists( 'vava_customer_prepare_account_for_booking' ) ) {
				vava_customer_prepare_account_for_booking( $booking_id, (array) get_post_meta( $booking_id, '_vava_booking_customer', true ), 'en' === get_post_meta( $booking_id, '_vava_booking_language', true ) ? 'en' : 'ar' );
			}
			vava_booking_send_confirmation( $booking_id );
		}
	}
	wp_send_json_success( array( 'booking' => $booking_id, 'status' => $success ? 'paid' : 'payment_failed' ) );
}
add_action( 'admin_post_vava_paymob_webhook', 'vava_booking_paymob_webhook' );
add_action( 'admin_post_nopriv_vava_paymob_webhook', 'vava_booking_paymob_webhook' );

/** Browser return is UX only; it reads the status written by the verified webhook. */
function vava_booking_paymob_return(): void {
	$booking_id = isset( $_GET['booking'] ) ? absint( $_GET['booking'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$service_uid = $booking_id ? (string) get_post_meta( $booking_id, '_vava_booking_service_uid', true ) : '';
	$status = $booking_id ? (string) get_post_meta( $booking_id, '_vava_booking_status', true ) : '';
	$view_status = 'paid' === $status ? 'success' : ( 'payment_failed' === $status || 'payment_error' === $status ? 'failed' : 'pending' );
	$redirect = add_query_arg( array( 'booking_status' => $view_status, 'booking' => $booking_id, 'service' => $service_uid ), get_permalink( vava_booking_page_id() ) );
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_post_vava_paymob_return', 'vava_booking_paymob_return' );
add_action( 'admin_post_nopriv_vava_paymob_return', 'vava_booking_paymob_return' );

function vava_booking_admin_field( array $data, string $lang, array $path, string $label, bool $textarea = false ): void {
	$value = vava_paths_array_value( $data, $path, '' );
	$name = 'vava_booking[' . $lang . ']'; foreach ( $path as $part ) { $name .= '[' . $part . ']'; }
	?><label class="vava-booking-admin-field<?php echo $textarea ? ' is-full' : ''; ?>"><span><?php echo esc_html( $label ); ?></span><?php if ( $textarea ) : ?><textarea name="<?php echo esc_attr( $name ); ?>" rows="4"><?php echo esc_textarea( (string) $value ); ?></textarea><?php else : ?><input name="<?php echo esc_attr( $name ); ?>" type="text" value="<?php echo esc_attr( (string) $value ); ?>"/><?php endif; ?></label><?php
}

// VAVA_BOOKING_SETTINGS_AND_STAGE_ONE_POLISH_V1R10
function vava_booking_render_live_preview( WP_Post $post ): void {
	$text = vava_booking_text_data( (int) $post->ID, 'ar' );
	$service_ar = vava_booking_preview_service_sample( 'ar' );
	$service_en = vava_booking_preview_service_sample( 'en' );
	?>
	<aside class="vava-live-preview vava-booking-live-preview is-sidebar-active" data-booking-admin-preview data-preview-language="ar" data-preview-service-ar="<?php echo esc_attr( wp_json_encode( $service_ar, JSON_UNESCAPED_UNICODE ) ); ?>" data-preview-service-en="<?php echo esc_attr( wp_json_encode( $service_en, JSON_UNESCAPED_UNICODE ) ); ?>" dir="rtl">
		<header class="vava-live-preview-header"><div><strong data-preview-header>معاينة مباشرة</strong><span data-preview-section-label>الخدمة وبياناتك</span></div><span class="vava-live-preview-dot" aria-hidden="true"></span></header>
		<div class="vava-preview-viewport"><div class="vava-preview-stage"><div class="vava-preview-canvas vava-booking-preview-canvas">
			<section class="vava-booking-preview-page">
				<div class="vava-booking-preview-progress"><span class="is-active" data-preview-step="1"><b>1</b><em data-preview-step-label="1"><?php echo esc_html( (string) $text['steps'][0] ); ?></em></span><i></i><span data-preview-step="2"><b>2</b><em data-preview-step-label="2"><?php echo esc_html( (string) $text['steps'][1] ); ?></em></span><i></i><span data-preview-step="3"><b>3</b><em data-preview-step-label="3"><?php echo esc_html( (string) $text['steps'][2] ); ?></em></span><i></i><span data-preview-step="4"><b>4</b><em data-preview-step-label="4"><?php echo esc_html( (string) $text['steps'][3] ); ?></em></span></div>
				<div class="vava-booking-preview-heading"><small data-preview-eyebrow><?php echo esc_html( (string) $text['eyebrow'] ); ?></small><h3 data-preview-title><?php echo esc_html( (string) $text['title'] ); ?></h3><p data-preview-intro><?php echo esc_html( (string) $text['intro'] ); ?></p></div>
				<div class="vava-booking-preview-step is-active" data-preview-pane="1">
					<div class="vava-booking-preview-service">
						<div class="vava-booking-preview-service-aside"><span><small data-preview-service-label="duration">المدة</small><b data-preview-service-value="duration"><?php echo esc_html( $service_ar['duration'] ); ?></b></span><span><small data-preview-service-label="session_type">النوع</small><b data-preview-service-value="session_type"><?php echo esc_html( $service_ar['session_type'] ); ?></b></span><span><small data-preview-service-label="price">السعر</small><b data-preview-service-value="price"><?php echo esc_html( $service_ar['price'] ); ?></b></span></div>
						<div class="vava-booking-preview-service-main"><div class="vava-booking-preview-image"></div><div><small data-preview-selected-service><?php echo esc_html( (string) $text['selected_service'] ); ?></small><strong data-preview-service-title><?php echo esc_html( $service_ar['title'] ); ?></strong><p data-preview-service-description><?php echo esc_html( $service_ar['description'] ); ?></p></div></div>
					</div>
					<div class="vava-booking-preview-form"><h4 data-preview-fields-title><?php echo esc_html( (string) $text['fields_title'] ); ?></h4><div><label><span data-preview-name-label><?php echo esc_html( (string) $text['fields']['name']['label'] ); ?></span><i></i></label><label><span data-preview-email-label><?php echo esc_html( (string) $text['fields']['email']['label'] ); ?></span><i></i></label><div class="vava-booking-preview-whatsapp"><span data-preview-whatsapp-label><?php echo esc_html( (string) $text['fields']['whatsapp']['label'] ); ?></span><div><i data-preview-whatsapp-country>🇸🇦 المملكة العربية السعودية (+966)</i><i data-preview-whatsapp-placeholder>اكتب الرقم بدون كود الدولة</i></div></div><label><span data-preview-previous-label><?php echo esc_html( (string) $text['fields']['previous']['label'] ); ?></span><i></i></label><label class="is-wide"><span data-preview-notes-label><?php echo esc_html( (string) $text['fields']['notes']['label'] ); ?></span><i></i></label></div></div>
				</div>
				<div class="vava-booking-preview-step" data-preview-pane="2"><div class="vava-booking-preview-appointment"><div><small data-preview-calendar-month>يوليو 2026</small><div class="vava-booking-preview-calendar"><span>26</span><span class="is-active">27</span><span>28</span><span>29</span><span>30</span></div></div><div><h4 data-preview-appointment-title><?php echo esc_html( (string) $text['appointment_title'] ); ?></h4><div class="vava-booking-preview-times" dir="ltr"><span>10:00 am</span><span class="is-active">11:30 am</span><span>1:00 pm</span><span>3:30 pm</span></div></div></div></div>
				<div class="vava-booking-preview-step" data-preview-pane="3"><div class="vava-booking-preview-confirm"><div class="vava-booking-preview-summary"><h4 data-preview-confirm-title><?php echo esc_html( (string) $text['confirm_title'] ); ?></h4><p><b data-preview-summary-service><?php echo esc_html( (string) $text['summary_service'] ); ?></b><span data-preview-summary-service-value><?php echo esc_html( $service_ar['title'] ); ?></span></p><p><b data-preview-summary-customer><?php echo esc_html( (string) $text['summary_customer'] ); ?></b><span data-preview-summary-customer-value>الاسم وبيانات التواصل</span></p><p><b data-preview-summary-appointment><?php echo esc_html( (string) $text['summary_appointment'] ); ?></b><span data-preview-summary-appointment-value>27 يوليو — 11:30 am</span></p></div><div class="vava-booking-preview-payment"><h4 data-preview-payment-title><?php echo esc_html( (string) $text['payment_title'] ); ?></h4><label class="is-active"><i></i><span><b data-preview-paymob-label><?php echo esc_html( (string) $text['paymob_label'] ); ?></b><small data-preview-paymob-note><?php echo esc_html( (string) $text['paymob_note'] ); ?></small></span></label><label><i></i><span><b data-preview-bank-label><?php echo esc_html( (string) $text['bank_label'] ); ?></b><small data-preview-bank-note><?php echo esc_html( (string) $text['bank_note'] ); ?></small></span></label></div></div><div class="vava-booking-preview-message" data-preview-message-box hidden><small data-preview-message-label>عنوان نجاح الحجز</small><p data-preview-message-value><?php echo esc_html( (string) $text['success_title'] ); ?></p></div></div>
				<div class="vava-booking-preview-step" data-preview-pane="4"><div class="vava-booking-preview-review-grid"><article><h4 data-preview-review-title="customer">تفاصيل العميل</h4><p><span data-preview-review-label="name">الاسم الكامل</span><b>Ahmed Hemdan</b></p><p><span data-preview-review-label="email">البريد الإلكتروني</span><b dir="ltr">name@example.com</b></p><p><span data-preview-review-label="phone">رقم الجوال</span><b dir="ltr">+966 50 000 0000</b></p></article><article><h4 data-preview-review-title="service">تفاصيل الخدمة والموعد</h4><p><span data-preview-review-label="service">الخدمة</span><b data-preview-review-service-value><?php echo esc_html( $service_ar['title'] ); ?></b></p><p><span data-preview-review-label="appointment">الموعد</span><b data-preview-review-appointment-value>27 يوليو — 11:30 am</b></p><p><span data-preview-review-label="duration">المدة</span><b data-preview-review-duration-value><?php echo esc_html( $service_ar['duration'] ); ?></b></p></article><article><h4 data-preview-review-title="payment">تفاصيل الدفع</h4><p><span data-preview-review-label="method">طريقة الدفع</span><b data-preview-review-method-value><?php echo esc_html( (string) $text['bank_label'] ); ?></b></p><p><span data-preview-review-label="status">الحالة</span><b data-preview-review-status-value>بانتظار مراجعة التحويل</b></p><p><span data-preview-review-label="total">الإجمالي</span><b data-preview-review-total-value><?php echo esc_html( $service_ar['price'] ); ?></b></p></article><article><h4 data-preview-review-title="transfer">تفاصيل التحويل</h4><p><span data-preview-review-label="bank">البنك</span><b data-preview-review-bank-value>البنك الأهلي</b></p><p><span data-preview-review-label="amount">المبلغ</span><b data-preview-review-amount-value><?php echo esc_html( $service_ar['price'] ); ?></b></p><p><span data-preview-review-label="receipt">الإيصال</span><b dir="ltr">receipt.pdf</b></p></article></div><div class="vava-booking-preview-review-footer"><span data-preview-review-consent>☑ <?php echo esc_html( (string) $text['consent_text'] ); ?></span><strong data-preview-review-final-total>الإجمالي النهائي <b><?php echo esc_html( $service_ar['price'] ); ?></b></strong><div><button type="button" data-preview-review-submit>إرسال الحجز لمراجعة التحويل</button><div class="vava-booking-preview-secondary-actions"><button type="button" class="is-secondary" data-preview-review-paths>العودة إلى مسارات VAVA</button><button type="button" class="is-secondary" data-preview-review-back>رجوع للمرحلة السابقة</button></div></div></div></div>
				<div class="vava-booking-preview-actions"><button type="button" data-preview-primary><?php echo esc_html( (string) $text['continue'] ); ?></button></div>
			</section>
		</div></div></div>
	</aside>
	<?php
}

function vava_booking_render_customer_fields( array $text, string $lang ): void {
	$descriptions = array(
		'name' => array( 'ar' => 'اسم العميل المستخدم في الحجز.', 'en' => 'Customer name used for the booking.' ),
		'email' => array( 'ar' => 'البريد الذي يستقبل تفاصيل الحجز.', 'en' => 'Email that receives booking details.' ),
		'whatsapp' => array( 'ar' => 'رقم التواصل والمتابعة.', 'en' => 'Contact and follow-up number.' ),
		'previous' => array( 'ar' => 'معرفة ما إذا كانت هناك تجربة سابقة.', 'en' => 'Whether the customer has tried VAVA before.' ),
		'notes' => array( 'ar' => 'ملاحظات اختيارية تساعد الفريق قبل الجلسة.', 'en' => 'Optional context for the VAVA team.' ),
	);
	foreach ( array( 'name','email','whatsapp','previous','notes' ) as $field ) :
		$label = (string) ( $text['fields'][ $field ]['label'] ?? $field );
		?>
		<article class="vava-booking-field-card is-compact">
			<div class="vava-booking-field-card-copy"><strong><?php echo esc_html( $label ); ?></strong><small><?php echo esc_html( $descriptions[ $field ][ $lang ] ); ?></small></div>
			<label class="vava-booking-field-switch"><input name="vava_booking[<?php echo esc_attr( $lang ); ?>][fields][<?php echo esc_attr( $field ); ?>][required]" type="hidden" value="0"/><input name="vava_booking[<?php echo esc_attr( $lang ); ?>][fields][<?php echo esc_attr( $field ); ?>][required]" type="checkbox" value="1" <?php checked( ! empty( $text['fields'][ $field ]['required'] ) ); ?>/><i aria-hidden="true"></i></label>
		</article>
		<?php
	endforeach;
}

/** Keep approved public copy intact while the streamlined editor exposes only operational settings. */
function vava_booking_render_preserved_copy_inputs( array $text, string $lang ): void {
	$scalar_keys = array(
		'eyebrow','title','intro','selected_service','change_service','fields_title','continue',
		'appointment_title','appointment_intro','choose_date','choose_time','no_slots','back','continue_payment',
		'confirm_title','summary_service','summary_customer','summary_appointment','payment_title',
		'paymob_label','paymob_note','bank_label','bank_note','cash_label','cash_note','submit','processing',
		'review_title','review_intro','final_confirm','edit'
	);
	foreach ( $scalar_keys as $key ) {
		printf( '<input type="hidden" name="vava_booking[%1$s][%2$s]" value="%3$s"/>', esc_attr( $lang ), esc_attr( $key ), esc_attr( (string) ( $text[ $key ] ?? '' ) ) );
	}
	foreach ( (array) ( $text['steps'] ?? array() ) as $index => $value ) {
		printf( '<input type="hidden" name="vava_booking[%1$s][steps][%2$d]" value="%3$s"/>', esc_attr( $lang ), absint( $index ), esc_attr( (string) $value ) );
	}
	foreach ( array( 'name','email','whatsapp','previous','notes' ) as $field ) {
		foreach ( array( 'label','placeholder','yes','no' ) as $key ) {
			if ( ! array_key_exists( $key, (array) ( $text['fields'][ $field ] ?? array() ) ) ) { continue; }
			printf( '<input type="hidden" name="vava_booking[%1$s][fields][%2$s][%3$s]" value="%4$s"/>', esc_attr( $lang ), esc_attr( $field ), esc_attr( $key ), esc_attr( (string) $text['fields'][ $field ][ $key ] ) );
		}
	}
}

function vava_booking_admin_i18n( string $ar, string $en, string $tag = 'span', string $class = '' ): void {
	$allowed = array( 'span', 'small', 'strong', 'h3', 'h4', 'p', 'b' );
	if ( ! in_array( $tag, $allowed, true ) ) { $tag = 'span'; }
	printf(
		'<%1$s%2$s data-booking-i18n data-i18n-ar="%3$s" data-i18n-en="%4$s">%5$s</%1$s>',
		esc_attr( $tag ),
		$class ? ' class="' . esc_attr( $class ) . '"' : '',
		esc_attr( $ar ),
		esc_attr( $en ),
		esc_html( $ar )
	);
}

function vava_booking_render_availability_admin_panel( array $shared ): void {
	$settings = array(
		'timezone' => array( 'ar' => 'المنطقة الزمنية', 'en' => 'Time zone', 'type' => 'text', 'min' => '', 'suffix_ar' => '', 'suffix_en' => '', 'placeholder_ar' => '+03:00', 'placeholder_en' => '+03:00' ),
		'slot_interval' => array( 'ar' => 'الفاصل بين المواعيد بالدقائق', 'en' => 'Time between appointments in minutes', 'type' => 'number', 'min' => '10', 'suffix_ar' => 'دقيقة', 'suffix_en' => 'min', 'placeholder_ar' => '30', 'placeholder_en' => '30' ),
		'default_duration' => array( 'ar' => 'المدة الافتراضية بالدقائق', 'en' => 'Default duration in minutes', 'type' => 'number', 'min' => '10', 'suffix_ar' => 'دقيقة', 'suffix_en' => 'min', 'placeholder_ar' => '90', 'placeholder_en' => '90' ),
		'max_days' => array( 'ar' => 'عدد الأيام المتاحة مستقبلًا', 'en' => 'Number of future booking days', 'type' => 'number', 'min' => '1', 'suffix_ar' => 'يوم', 'suffix_en' => 'days', 'placeholder_ar' => '60', 'placeholder_en' => '60' ),
	);
	$days = array(
		'sun' => array( 'ar' => 'الأحد', 'en' => 'Sunday' ),
		'mon' => array( 'ar' => 'الاثنين', 'en' => 'Monday' ),
		'tue' => array( 'ar' => 'الثلاثاء', 'en' => 'Tuesday' ),
		'wed' => array( 'ar' => 'الأربعاء', 'en' => 'Wednesday' ),
		'thu' => array( 'ar' => 'الخميس', 'en' => 'Thursday' ),
		'fri' => array( 'ar' => 'الجمعة', 'en' => 'Friday' ),
		'sat' => array( 'ar' => 'السبت', 'en' => 'Saturday' ),
	);
	?>
	<section class="vava-booking-admin-panel" data-booking-panel="availability">
		<div class="vava-booking-admin-card">
			<div class="vava-booking-card-heading"><div>
				<?php vava_booking_admin_i18n( 'إعدادات مشتركة بين اللغتين', 'Shared settings for both languages', 'small' ); ?>
				<?php vava_booking_admin_i18n( 'المواعيد والتوافر', 'Availability', 'h3' ); ?>
				<?php vava_booking_admin_i18n( 'اختر نوع الإعداد من القائمة ثم عدّل قيمته في الحقل المجاور. جميع الجلسات والباقات تستخدم جدول العمل العام نفسه.', 'Choose a setting from the list, then edit its value in the adjacent field. All sessions and packages use the same working schedule.', 'p' ); ?>
			</div></div>

			<div class="vava-booking-select-editor" data-availability-select-editor>
				<label class="vava-booking-select-editor-choice">
					<?php vava_booking_admin_i18n( 'نوع الإعداد', 'Setting type', 'span' ); ?>
					<select data-availability-setting-select>
						<?php foreach ( $settings as $key => $config ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" data-booking-i18n data-i18n-ar="<?php echo esc_attr( $config['ar'] ); ?>" data-i18n-en="<?php echo esc_attr( $config['en'] ); ?>"><?php echo esc_html( $config['ar'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<div class="vava-booking-select-editor-fields">
					<?php $index = 0; foreach ( $settings as $key => $config ) : $value = (string) ( $shared[ $key ] ?? '' ); ?>
						<label class="vava-booking-admin-field vava-booking-select-editor-field<?php echo 0 === $index ? ' is-active' : ''; ?>" data-availability-setting-field="<?php echo esc_attr( $key ); ?>">
							<?php vava_booking_admin_i18n( $config['ar'], $config['en'], 'span' ); ?>
							<span class="vava-booking-value-with-unit">
								<input name="vava_booking_shared[<?php echo esc_attr( $key ); ?>]" type="<?php echo esc_attr( $config['type'] ); ?>" <?php echo '' !== $config['min'] ? 'min="' . esc_attr( $config['min'] ) . '"' : ''; ?> value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $config['placeholder_ar'] ); ?>" data-placeholder-ar="<?php echo esc_attr( $config['placeholder_ar'] ); ?>" data-placeholder-en="<?php echo esc_attr( $config['placeholder_en'] ); ?>" data-setting-input/>
								<?php if ( '' !== $config['suffix_ar'] || '' !== $config['suffix_en'] ) : ?>
									<small data-booking-i18n data-i18n-ar="<?php echo esc_attr( $config['suffix_ar'] ); ?>" data-i18n-en="<?php echo esc_attr( $config['suffix_en'] ); ?>"><?php echo esc_html( $config['suffix_ar'] ); ?></small>
								<?php endif; ?>
							</span>
						</label>
					<?php $index++; endforeach; ?>
				</div>
			</div>

			<div class="vava-booking-subsection">
				<div class="vava-booking-subsection-heading">
					<?php vava_booking_admin_i18n( 'أيام وساعات العمل', 'Working days and hours', 'h4' ); ?>
					<?php vava_booking_admin_i18n( 'فعّل أيام العمل وحدد وقت البداية والنهاية لكل يوم. هذا الجدول يُطبّق على جميع الخدمات.', 'Enable working days and set the start and end time for each day. This schedule applies to all services.', 'p' ); ?>
				</div>
				<div class="vava-booking-days">
					<?php foreach ( $days as $key => $label ) : ?>
						<?php $working_day_enabled = ! empty( $shared['working_days'][ $key ] ); ?>
						<div class="vava-booking-day <?php echo $working_day_enabled ? 'is-open' : 'is-closed'; ?>" data-working-day-row>
							<div class="vava-booking-day-heading">
								<?php vava_booking_admin_i18n( $label['ar'], $label['en'], 'strong' ); ?>
								<label class="vava-booking-field-switch vava-booking-day-switch" data-title-ar="تفعيل أو إغلاق <?php echo esc_attr( $label['ar'] ); ?>" data-title-en="Enable or close <?php echo esc_attr( $label['en'] ); ?>" title="تفعيل أو إغلاق <?php echo esc_attr( $label['ar'] ); ?>">
									<input type="hidden" name="vava_booking_shared[working_days][<?php echo esc_attr( $key ); ?>]" value="0"/>
									<input name="vava_booking_shared[working_days][<?php echo esc_attr( $key ); ?>]" type="checkbox" value="1" <?php checked( $working_day_enabled ); ?> data-working-day-toggle data-vava-i18n-aria-ar="تفعيل أو إغلاق <?php echo esc_attr( $label['ar'] ); ?>" data-vava-i18n-aria-en="Enable or close <?php echo esc_attr( $label['en'] ); ?>" aria-label="تفعيل أو إغلاق <?php echo esc_attr( $label['ar'] ); ?>"/>
									<i aria-hidden="true"></i>
								</label>
							</div>
							<label>
								<?php vava_booking_admin_i18n( 'من', 'From', 'span' ); ?>
								<input name="vava_booking_shared[working_hours][<?php echo esc_attr( $key ); ?>][start]" type="time" value="<?php echo esc_attr( (string) $shared['working_hours'][ $key ]['start'] ); ?>" data-working-day-time <?php if ( ! $working_day_enabled ) : ?>readonly aria-disabled="true" tabindex="-1"<?php endif; ?>/>
							</label>
							<label>
								<?php vava_booking_admin_i18n( 'إلى', 'To', 'span' ); ?>
								<input name="vava_booking_shared[working_hours][<?php echo esc_attr( $key ); ?>][end]" type="time" value="<?php echo esc_attr( (string) $shared['working_hours'][ $key ]['end'] ); ?>" data-working-day-time <?php if ( ! $working_day_enabled ) : ?>readonly aria-disabled="true" tabindex="-1"<?php endif; ?>/>
							</label>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

function vava_booking_render_payment_admin_panel( array $shared, bool $can_manage_payments, array $paymob_missing, array $bank_missing, array $bank ): void {
	$methods = array(
		'paymob' => array( 'ar' => 'Paymob', 'en' => 'Paymob', 'desc_ar' => 'الدفع الإلكتروني الآمن', 'desc_en' => 'Secure online payment' ),
		'bank' => array( 'ar' => 'تحويل بنكي', 'en' => 'Bank transfer', 'desc_ar' => 'رفع بيانات وإيصال التحويل للمراجعة', 'desc_en' => 'Submit transfer details and receipt for review' ),
		'cash' => array( 'ar' => 'الدفع لاحقًا', 'en' => 'Pay later', 'desc_ar' => 'تأكيد الحجز دون تحصيل إلكتروني', 'desc_en' => 'Confirm without online collection' ),
	);
	?>
	<section class="vava-booking-admin-panel" data-booking-panel="payment">
		<div class="vava-booking-admin-card">
			<div class="vava-booking-card-heading vava-booking-payment-heading">
				<div>
					<?php vava_booking_admin_i18n( 'إعدادات مالية مشتركة بين اللغتين', 'Shared payment settings', 'small' ); ?>
					<?php vava_booking_admin_i18n( 'طرق الدفع وPaymob والتحويل البنكي', 'Payment methods, Paymob and bank transfer', 'h3' ); ?>
					<?php vava_booking_admin_i18n( 'فعّل طريقة الدفع ثم اضغط على بطاقتها لفتح إعداداتها. مراجعة التحويلات تتم من صفحة حجوزات VAVA المستقلة.', 'Enable a payment method, then open its card to edit its settings. Bank transfers are reviewed from the separate VAVA Bookings page.', 'p' ); ?>
				</div>
				<a class="button vava-booking-manage-bookings" href="<?php echo esc_url( admin_url( 'edit.php?post_type=vava_booking' ) ); ?>"><span aria-hidden="true">▣</span><?php vava_booking_admin_i18n( 'فتح حجوزات VAVA', 'Open VAVA Bookings', 'b' ); ?></a>
			</div>
			<?php if ( ! $can_manage_payments ) : ?>
				<div class="vava-booking-security-note"><?php vava_booking_admin_i18n( 'الإعدادات محمية', 'Protected settings', 'strong' ); ?><?php vava_booking_admin_i18n( 'لا يمكن تعديل بيانات الدفع أو الحساب البنكي من هذا الحساب.', 'This account cannot edit payment or bank account settings.', 'p' ); ?></div>
			<?php else : ?>
				<div class="vava-booking-payment-accordions" data-payment-accordions>
					<?php foreach ( $methods as $key => $method ) : $enabled = ! empty( $shared['payment_methods'][ $key ] ); ?>
						<article class="vava-booking-payment-method-card<?php echo 'paymob' === $key ? ' is-open' : ''; ?>" data-payment-method-card="<?php echo esc_attr( $key ); ?>">
							<header class="vava-booking-payment-method-head">
								<button type="button" data-payment-accordion aria-expanded="<?php echo 'paymob' === $key ? 'true' : 'false'; ?>">
									<span class="vava-booking-payment-method-icon" aria-hidden="true"><?php echo 'paymob' === $key ? 'P' : ( 'bank' === $key ? '⌂' : '◷' ); ?></span>
									<span class="vava-booking-payment-method-copy"><?php vava_booking_admin_i18n( $method['ar'], $method['en'], 'strong' ); ?><?php vava_booking_admin_i18n( $method['desc_ar'], $method['desc_en'], 'small' ); ?></span>
									<span class="vava-booking-payment-method-state <?php echo $enabled ? 'is-enabled' : 'is-disabled'; ?>" data-payment-status data-enabled-ar="مفعلة" data-disabled-ar="معطلة" data-enabled-en="Enabled" data-disabled-en="Disabled"><?php echo $enabled ? 'مفعلة' : 'معطلة'; ?></span>
									<b class="vava-booking-payment-chevron" aria-hidden="true">⌄</b>
								</button>
								<label class="vava-booking-switch" data-title-ar="تفعيل أو تعطيل" data-title-en="Enable or disable" title="تفعيل أو تعطيل">
									<input type="hidden" name="vava_booking_shared[payment_methods][<?php echo esc_attr( $key ); ?>]" value="0"/>
									<input name="vava_booking_shared[payment_methods][<?php echo esc_attr( $key ); ?>]" type="checkbox" value="1" <?php checked( $enabled ); ?> data-payment-enabled/>
									<i></i>
								</label>
							</header>
							<div class="vava-booking-payment-method-body">
								<?php if ( 'paymob' === $key ) : ?>
									<div class="vava-booking-subsection-heading"><?php vava_booking_admin_i18n( 'ربط Paymob', 'Paymob connection', 'h4' ); ?><?php vava_booking_admin_i18n( 'تظهر Paymob في صفحة الحجز بعد تفعيلها واستكمال بيانات الربط التالية.', 'Paymob appears on the booking page after it is enabled and the following connection details are complete.', 'p' ); ?></div>
									<?php if ( $enabled && $paymob_missing ) : ?><div class="vava-booking-config-warning"><?php vava_booking_admin_i18n( 'ربط Paymob غير مكتمل', 'Paymob connection is incomplete', 'strong' ); ?><p><?php vava_booking_admin_i18n( 'الحقول الناقصة:', 'Missing fields:', 'span' ); ?> <?php echo esc_html( implode( '، ', $paymob_missing ) ); ?></p></div><?php endif; ?>
									<div class="vava-booking-admin-grid">
										<label class="vava-booking-admin-field is-full"><?php vava_booking_admin_i18n( 'المفتاح السري لـ Paymob (sk_*)', 'Paymob Secret Key (sk_*)', 'span' ); ?><input autocomplete="new-password" name="vava_booking_shared[paymob][secret_key]" type="password" value="" data-placeholder-ar="محفوظ — اتركه فارغًا للإبقاء عليه" data-placeholder-en="Saved — leave blank to keep it" placeholder="محفوظ — اتركه فارغًا للإبقاء عليه"/></label>
										<label class="vava-booking-admin-field is-full"><?php vava_booking_admin_i18n( 'المفتاح العام لـ Paymob (pk_*)', 'Paymob Public Key (pk_*)', 'span' ); ?><input name="vava_booking_shared[paymob][public_key]" value="<?php echo esc_attr( (string) $shared['paymob']['public_key'] ); ?>"/></label>
										<label class="vava-booking-admin-field is-full"><?php vava_booking_admin_i18n( 'معرّفات التكامل — افصل بينها بفاصلة', 'Integration IDs — comma separated', 'span' ); ?><input name="vava_booking_shared[paymob][integration_ids]" value="<?php echo esc_attr( (string) $shared['paymob']['integration_ids'] ); ?>"/></label>
										<label class="vava-booking-admin-field"><?php vava_booking_admin_i18n( 'منطقة Paymob', 'Paymob Region', 'span' ); ?><select name="vava_booking_shared[paymob][base_url]"><option value="https://ksa.paymob.com" <?php selected( (string) $shared['paymob']['base_url'], 'https://ksa.paymob.com' ); ?>>KSA</option><option value="https://accept.paymob.com" <?php selected( (string) $shared['paymob']['base_url'], 'https://accept.paymob.com' ); ?>>Egypt</option><option value="https://uae.paymob.com" <?php selected( (string) $shared['paymob']['base_url'], 'https://uae.paymob.com' ); ?>>UAE</option><option value="https://oman.paymob.com" <?php selected( (string) $shared['paymob']['base_url'], 'https://oman.paymob.com' ); ?>>Oman</option></select></label>
										<label class="vava-booking-admin-field is-full"><?php vava_booking_admin_i18n( 'مفتاح HMAC السري', 'HMAC Secret', 'span' ); ?><input autocomplete="new-password" name="vava_booking_shared[paymob][hmac_secret]" type="password" value="" data-placeholder-ar="محفوظ — اتركه فارغًا للإبقاء عليه" data-placeholder-en="Saved — leave blank to keep it" placeholder="محفوظ — اتركه فارغًا للإبقاء عليه"/></label>
										<label class="vava-booking-admin-field is-full"><?php vava_booking_admin_i18n( 'رابط استقبال نتيجة العملية', 'Transaction Processed Callback URL', 'span' ); ?><input readonly dir="ltr" value="<?php echo esc_attr( admin_url( 'admin-post.php?action=vava_paymob_webhook' ) ); ?>"/></label>
										<p class="vava-booking-auto-note"><?php vava_booking_admin_i18n( 'يجب أن تكون المفاتيح ومعرّفات التكامل وHMAC من نفس وضع الاختبار أو التشغيل الفعلي.', 'Keys, Integration IDs and HMAC must all belong to the same Test or Live mode.', 'span' ); ?></p>
									</div>
								<?php elseif ( 'bank' === $key ) : ?>
									<div class="vava-booking-subsection-heading"><?php vava_booking_admin_i18n( 'إعدادات التحويل البنكي', 'Bank transfer settings', 'h4' ); ?><?php vava_booking_admin_i18n( 'تُعرض هذه البيانات للعميل عند اختيار التحويل البنكي. لا تُدخل أي كلمة مرور أو OTP أو PIN.', 'These details are shown when bank transfer is selected. Never enter a password, OTP or PIN.', 'p' ); ?></div>
									<?php if ( $enabled && $bank_missing ) : ?><div class="vava-booking-config-warning"><?php vava_booking_admin_i18n( 'بيانات التحويل البنكي غير مكتملة', 'Bank transfer details are incomplete', 'strong' ); ?><p><?php vava_booking_admin_i18n( 'أكمل اسم البنك واسم المستفيد وIBAN.', 'Complete the bank name, beneficiary name and IBAN.', 'span' ); ?></p></div><?php endif; ?>
									<div class="vava-booking-admin-grid">
										<label class="vava-booking-admin-field"><?php vava_booking_admin_i18n( 'اسم البنك', 'Bank name', 'span' ); ?><input name="vava_booking_shared[bank_transfer][bank_name]" value="<?php echo esc_attr( (string) ( $bank['bank_name'] ?? '' ) ); ?>"/></label>
										<label class="vava-booking-admin-field"><?php vava_booking_admin_i18n( 'اسم المستفيد', 'Beneficiary name', 'span' ); ?><input name="vava_booking_shared[bank_transfer][beneficiary_name]" value="<?php echo esc_attr( (string) ( $bank['beneficiary_name'] ?? '' ) ); ?>"/></label>
										<label class="vava-booking-admin-field"><?php vava_booking_admin_i18n( 'رقم الحساب', 'Account number', 'span' ); ?><input dir="ltr" name="vava_booking_shared[bank_transfer][account_number]" value="<?php echo esc_attr( (string) ( $bank['account_number'] ?? '' ) ); ?>"/></label>
										<div class="vava-booking-admin-field vava-booking-protected-iban"><?php vava_booking_admin_i18n( 'IBAN محمي', 'Protected IBAN', 'span' ); ?><strong dir="ltr"><?php echo esc_html( vava_booking_mask_account_value( vava_booking_protected_iban() ) ?: 'غير مضبوط' ); ?></strong></div>
										<label class="vava-booking-admin-field"><?php vava_booking_admin_i18n( 'SWIFT — اختياري', 'SWIFT — optional', 'span' ); ?><input dir="ltr" name="vava_booking_shared[bank_transfer][swift]" value="<?php echo esc_attr( (string) ( $bank['swift'] ?? '' ) ); ?>"/></label>
										<label class="vava-booking-admin-field"><?php vava_booking_admin_i18n( 'العملة', 'Currency', 'span' ); ?><input maxlength="3" dir="ltr" name="vava_booking_shared[bank_transfer][currency]" value="<?php echo esc_attr( (string) ( $bank['currency'] ?? 'SAR' ) ); ?>"/></label>
										<label class="vava-booking-admin-field"><?php vava_booking_admin_i18n( 'مدة المراجعة المتوقعة بالساعات', 'Expected review time in hours', 'span' ); ?><input min="1" name="vava_booking_shared[bank_transfer][review_hours]" type="number" value="<?php echo esc_attr( (string) ( $bank['review_hours'] ?? 24 ) ); ?>"/></label>
										<label class="vava-booking-admin-field is-full vava-booking-localized-value" data-localized-value="ar"><?php vava_booking_admin_i18n( 'تعليمات التحويل', 'Transfer instructions', 'span' ); ?><textarea name="vava_booking_shared[bank_transfer][instructions_ar]" rows="4"><?php echo esc_textarea( (string) ( $bank['instructions_ar'] ?? '' ) ); ?></textarea></label>
										<label class="vava-booking-admin-field is-full vava-booking-localized-value" data-localized-value="en" hidden><?php vava_booking_admin_i18n( 'تعليمات التحويل', 'Transfer instructions', 'span' ); ?><textarea dir="ltr" name="vava_booking_shared[bank_transfer][instructions_en]" rows="4"><?php echo esc_textarea( (string) ( $bank['instructions_en'] ?? '' ) ); ?></textarea></label>
									</div>
									<p class="vava-booking-auto-note"><?php vava_booking_admin_i18n( 'الـIBAN ثابت ومحمي خارج قاعدة البيانات. تعديل رقم الحساب يُسجَّل ويُرسل تنبيهًا إلى بريد الإدارة.', 'The IBAN is protected outside the database. Account-number changes are logged and emailed to the administrator.', 'span' ); ?></p>
								<?php else : ?>
									<div class="vava-booking-subsection-heading"><?php vava_booking_admin_i18n( 'الدفع لاحقًا', 'Pay later', 'h4' ); ?><?php vava_booking_admin_i18n( 'عند تفعيل هذه الطريقة يمكن للعميل إرسال الحجز دون دفع إلكتروني، ويظل الحجز بانتظار المتابعة.', 'When enabled, customers can submit a booking without online payment and it remains pending follow-up.', 'p' ); ?></div>
								<?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

function vava_booking_render_settings( WP_Post $post ): void {
	wp_nonce_field( 'vava_booking_save', 'vava_booking_nonce' );
	$shared = vava_booking_shared_data( (int) $post->ID );
	$can_manage_payments = current_user_can( vava_booking_admin_capability() );
	$paymob_missing = vava_booking_paymob_missing_fields( $shared );
	$bank_missing = vava_booking_bank_missing_fields( $shared );
	$bank = (array) ( $shared['bank_transfer'] ?? array() );
	?>
	<div class="vava-homepage-admin vava-booking-admin" data-booking-admin data-active-language="ar" data-active-section="customer" data-settings-title-ar="إعدادات صفحة الحجز" data-settings-title-en="Booking page settings">
		<input type="hidden" name="_vava_admin_active_language" value="ar" data-vava-active-language-input/>
		<?php if ( function_exists( 'vava_paths_render_page_identity' ) ) { vava_paths_render_page_identity( $post ); } ?>
		<div class="vava-booking-postbox-actions"><div class="vava-language-switch vava-booking-language" role="group" aria-label="Language"><button type="button" class="is-active" data-booking-lang="ar"><b>AR</b><span>العربية</span></button><button type="button" data-booking-lang="en"><b>EN</b><span>English</span></button></div><button type="submit" class="button button-primary vava-homepage-update-button vava-booking-update-button"><span>تحديث</span></button></div>
		<header class="vava-admin-toolbar vava-booking-admin-toolbar"><nav class="vava-section-tabs vava-booking-admin-tabs" aria-label="أقسام إعدادات الحجز"><button type="button" class="is-active" data-booking-tab="customer"><span>1</span><b>بيانات العميل</b></button><button type="button" data-booking-tab="availability"><span>2</span><b>المواعيد والتوافر</b></button><button type="button" data-booking-tab="payment"><span>3</span><b>طرق الدفع</b></button><button type="button" data-booking-tab="messages"><span>4</span><b>الرسائل التشغيلية</b></button><button type="button" data-booking-tab="questionnaires"><span>5</span><b>الاستبيانات</b></button></nav></header>
		<div class="vava-editor-workspace vava-booking-editor-workspace">
			<div class="vava-editor-controls vava-booking-editor-controls">
				<div class="vava-section-panels vava-booking-section-panels">
				<?php foreach ( array( 'ar', 'en' ) as $lang ) : $text = vava_booking_text_data( (int) $post->ID, $lang ); ?>
					<div class="vava-booking-language-pane<?php echo 'ar' === $lang ? ' is-active' : ''; ?>" data-booking-lang-pane="<?php echo esc_attr( $lang ); ?>" dir="<?php echo 'en' === $lang ? 'ltr' : 'rtl'; ?>">
						<?php vava_booking_render_preserved_copy_inputs( $text, $lang ); ?>
						<section class="vava-booking-admin-panel is-active" data-booking-panel="customer"><div class="vava-booking-admin-card"><div class="vava-booking-card-heading"><div><small><?php echo 'en' === $lang ? 'Step 1 operational settings' : 'الإعدادات التشغيلية للخطوة الأولى'; ?></small><h3><?php echo 'en' === $lang ? 'Customer fields' : 'حقول بيانات العميل'; ?></h3><p><?php echo 'en' === $lang ? 'The public design and approved copy are fixed. Choose only which customer fields are required.' : 'تصميم الصفحة ونصوصها المعتمدة ثابتة. حدّد فقط الحقول الإلزامية المطلوبة لإتمام الحجز.'; ?></p></div><span class="vava-booking-source-badge"><?php echo 'en' === $lang ? 'Service data comes from VAVA Paths' : 'بيانات الخدمة من مسارات VAVA'; ?></span></div><div class="vava-booking-customer-requirements"><?php vava_booking_render_customer_fields( $text, $lang ); ?></div></div></section>
						<?php vava_booking_render_messages_admin_panel( $text, $lang ); ?>
					</div>
				<?php endforeach; ?>
				<?php vava_booking_render_availability_admin_panel( $shared ); ?>
				<?php vava_booking_render_payment_admin_panel( $shared, $can_manage_payments, $paymob_missing, $bank_missing, $bank ); ?>
				<?php if ( function_exists( 'vava_booking_questionnaire_render_admin_panel' ) ) { vava_booking_questionnaire_render_admin_panel( $post ); } ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}


function vava_booking_render_messages_admin_panel( array $text, string $lang ): void {
	$settings = array(
		'success_title' => array( 'ar' => 'عنوان نجاح الحجز', 'en' => 'Booking success title', 'textarea' => false ),
		'success_message' => array( 'ar' => 'رسالة نجاح الحجز', 'en' => 'Booking success message', 'textarea' => true ),
		'bank_received_title' => array( 'ar' => 'عنوان انتظار مراجعة التحويل', 'en' => 'Bank transfer review title', 'textarea' => false ),
		'bank_received_message' => array( 'ar' => 'رسالة انتظار مراجعة التحويل', 'en' => 'Bank transfer review message', 'textarea' => true ),
		'payment_success' => array( 'ar' => 'رسالة نجاح الدفع', 'en' => 'Payment success message', 'textarea' => true ),
		'payment_failed' => array( 'ar' => 'رسالة فشل الدفع', 'en' => 'Payment failure message', 'textarea' => true ),
		'payment_pending' => array( 'ar' => 'رسالة انتظار التحقق من الدفع', 'en' => 'Payment verification pending message', 'textarea' => true ),
		'invalid_service' => array( 'ar' => 'رسالة الخدمة غير المتاحة', 'en' => 'Unavailable service message', 'textarea' => true ),
		'validation_error' => array( 'ar' => 'رسالة الحقول المطلوبة', 'en' => 'Required fields message', 'textarea' => true ),
		'slot_unavailable' => array( 'ar' => 'رسالة الموعد غير المتاح', 'en' => 'Unavailable appointment message', 'textarea' => true ),
		'preparation_title' => array( 'ar' => 'عنوان الاستبيان التحضيري', 'en' => 'Preparation form title', 'textarea' => false ),
		'preparation_message' => array( 'ar' => 'رسالة الاستبيان التحضيري', 'en' => 'Preparation form message', 'textarea' => true ),
		'preparation_url' => array( 'ar' => 'رابط الاستبيان التحضيري', 'en' => 'Preparation form URL', 'textarea' => false ),
		'consent_text' => array( 'ar' => 'نص الموافقة على السياسات', 'en' => 'Policy consent text', 'textarea' => true ),
	);
	?>
	<section class="vava-booking-admin-panel" data-booking-panel="messages">
		<div class="vava-booking-admin-card">
			<div class="vava-booking-card-heading"><div>
				<small><?php echo 'en' === $lang ? 'Messages and general copy' : 'الرسائل والإعدادات العامة'; ?></small>
				<h3><?php echo 'en' === $lang ? 'Status messages and preparation form' : 'رسائل الحالة والاستبيان التحضيري'; ?></h3>
				<p><?php echo 'en' === $lang ? 'Choose one title or message, then edit only its corresponding field.' : 'اختر عنوانًا أو رسالة من القائمة، ثم عدّل الحقل المرتبط بها فقط.'; ?></p>
			</div></div>
			<div class="vava-booking-select-editor is-messages" data-message-select-editor>
				<label class="vava-booking-select-editor-choice">
					<span><?php echo 'en' === $lang ? 'Message or title' : 'العنوان أو الرسالة'; ?></span>
					<select data-message-setting-select>
						<?php foreach ( $settings as $key => $config ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( 'en' === $lang ? $config['en'] : $config['ar'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<div class="vava-booking-select-editor-fields">
					<?php $index = 0; foreach ( $settings as $key => $config ) :
						$value = (string) ( $text[ $key ] ?? '' );
						$name = 'vava_booking[' . $lang . '][' . $key . ']';
						$label = 'en' === $lang ? $config['en'] : $config['ar'];
						?>
						<label class="vava-booking-admin-field vava-booking-select-editor-field<?php echo $config['textarea'] ? ' is-full' : ''; ?><?php echo 0 === $index ? ' is-active' : ''; ?>" data-message-setting-field="<?php echo esc_attr( $key ); ?>">
							<span><?php echo esc_html( $label ); ?></span>
							<?php if ( $config['textarea'] ) : ?>
								<textarea name="<?php echo esc_attr( $name ); ?>" rows="5" data-message-setting-input><?php echo esc_textarea( $value ); ?></textarea>
							<?php else : ?>
								<input name="<?php echo esc_attr( $name ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" data-message-setting-input/>
							<?php endif; ?>
							<?php if ( 'consent_text' === $key ) : ?><small><?php echo esc_html( 'en' === $lang ? 'The three policy names are linked automatically. You may also use {terms}, {privacy}, and {booking_policy}.' : 'يتم ربط أسماء السياسات الثلاث تلقائيًا، ويمكن أيضًا استخدام {terms} و{privacy} و{booking_policy}.' ); ?></small><?php endif; ?>
						</label>
					<?php $index++; endforeach; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

function vava_booking_current_admin_post_id(): int {
	if ( isset( $_GET['post'] ) ) { return absint( $_GET['post'] ); } // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	global $post;
	return $post instanceof WP_Post ? (int) $post->ID : 0;
}

function vava_booking_prepare_advanced_editor(): void {
	$post_id = vava_booking_current_admin_post_id();
	if ( $post_id && vava_booking_is_page( $post_id ) ) { remove_post_type_support( 'page', 'editor' ); remove_post_type_support( 'page', 'thumbnail' ); }
}
add_action( 'admin_init', 'vava_booking_prepare_advanced_editor', 35 );

function vava_booking_add_meta_box( string $post_type, WP_Post $post ): void {
	if ( 'page' !== $post_type || ! vava_booking_is_page( (int) $post->ID ) ) { return; }
	foreach ( array( 'postimagediv','pageparentdiv','slugdiv','authordiv','commentsdiv','commentstatusdiv','trackbacksdiv','revisionsdiv' ) as $box ) { remove_meta_box( $box, 'page', 'side' ); remove_meta_box( $box, 'page', 'normal' ); }
	remove_meta_box( 'vava_live_preview_box', 'page', 'side' );
	remove_meta_box( 'vava_homepage_settings', 'page', 'normal' );
	add_meta_box( 'vava_live_preview_box', 'معاينة مباشرة', 'vava_booking_render_live_preview', 'page', 'side', 'high' );
	add_meta_box( 'vava_homepage_settings', 'إعدادات صفحة الحجز', 'vava_booking_render_settings', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'vava_booking_add_meta_box', 90, 2 );

function vava_booking_admin_body_class( string $classes ): string {
	$post_id = vava_booking_current_admin_post_id();
	if ( $post_id && vava_booking_is_page( $post_id ) ) { $classes .= ' vava-homepage-classic vava-booking-classic'; }
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && 'edit-vava_booking' === $screen->id ) {
		$classes .= ' vava-booking-admin-screen';
		if ( 'products' === vava_booking_admin_scope() ) { $classes .= ' vava-products-admin-screen'; }
	}
	if ( $screen && false !== strpos( (string) $screen->id, 'vava-booking-details' ) ) {
		$classes .= ' vava-booking-admin-screen vava-booking-details-screen';
		$details_id = isset( $_GET['booking'] ) ? absint( $_GET['booking'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $details_id && vava_booking_order_is_product( $details_id ) ) { $classes .= ' vava-products-admin-screen'; }
	}
	return $classes;
}
add_filter( 'admin_body_class', 'vava_booking_admin_body_class', 40 );

function vava_booking_hide_screen_options( bool $show, WP_Screen $screen ): bool {
	if ( 'edit-vava_booking' === $screen->id || false !== strpos( (string) $screen->id, 'vava-booking-details' ) ) { return false; }
	$post_id = vava_booking_current_admin_post_id();
	return $post_id && vava_booking_is_page( $post_id ) ? false : $show;
}
add_filter( 'screen_options_show_screen', 'vava_booking_hide_screen_options', 40, 2 );

function vava_booking_hide_contextual_help( string $old_help, string $screen_id, WP_Screen $screen ): string {
	$post_id = vava_booking_current_admin_post_id();
	return $post_id && vava_booking_is_page( $post_id ) ? '' : $old_help;
}
add_filter( 'contextual_help', 'vava_booking_hide_contextual_help', 40, 3 );

function vava_booking_recursive_sanitize( $value, string $key = '' ) {
	if ( is_array( $value ) ) { $clean = array(); foreach ( $value as $k => $v ) { $clean[ $k ] = vava_booking_recursive_sanitize( $v, (string) $k ); } return $clean; }
	if ( in_array( $key, array( 'required','enabled','sun','mon','tue','wed','thu','fri','sat','paymob','bank','cash' ), true ) ) { return ! empty( $value ) ? 1 : 0; }
	if ( in_array( $key, array( 'secret_key','hmac_secret' ), true ) ) { return sanitize_text_field( (string) $value ); }
	if ( in_array( $key, array( 'slot_interval','default_duration','min_notice_hours','max_days','review_hours' ), true ) ) { return absint( $value ); }
	if ( 'currency' === $key ) { return strtoupper( substr( preg_replace( '/[^A-Za-z]/', '', (string) $value ), 0, 3 ) ); }
	if ( preg_match( '/(?:_url|base_url)$/', $key ) ) { return esc_url_raw( (string) $value ); }
	return sanitize_textarea_field( (string) $value );
}

function vava_booking_mask_account_value( string $value ): string {
	$value = preg_replace( '/\s+/', '', $value );
	$length = strlen( $value );
	if ( $length <= 6 ) { return str_repeat( '*', max( 0, $length - 2 ) ) . substr( $value, -2 ); }
	return substr( $value, 0, 2 ) . str_repeat( '*', $length - 6 ) . substr( $value, -4 );
}

function vava_booking_audit_bank_settings( array $old, array $new ): void {
	$tracked = array( 'bank_name', 'beneficiary_name', 'account_number', 'iban', 'swift', 'currency' );
	$changes = array();
	foreach ( $tracked as $key ) {
		$before = trim( (string) ( $old[ $key ] ?? '' ) );
		$after = trim( (string) ( $new[ $key ] ?? '' ) );
		if ( $before === $after ) { continue; }
		$changes[ $key ] = array(
			'before' => in_array( $key, array( 'account_number', 'iban' ), true ) ? vava_booking_mask_account_value( $before ) : $before,
			'after' => in_array( $key, array( 'account_number', 'iban' ), true ) ? vava_booking_mask_account_value( $after ) : $after,
		);
	}
	if ( ! $changes ) { return; }
	$log = get_option( '_vava_booking_bank_audit', array() );
	$log = is_array( $log ) ? $log : array();
	array_unshift( $log, array( 'time' => current_time( 'mysql' ), 'user_id' => get_current_user_id(), 'changes' => $changes ) );
	update_option( '_vava_booking_bank_audit', array_slice( $log, 0, 50 ), false );
	if ( isset( $changes['iban'] ) || isset( $changes['account_number'] ) ) {
		$admin_email = sanitize_email( (string) get_option( 'admin_email' ) );
		if ( $admin_email ) {
			$user = wp_get_current_user();
			if ( ! function_exists( 'vava_mail_notifications_enabled' ) || vava_mail_notifications_enabled( 'admin' ) ) { wp_mail( $admin_email, 'VAVA — Bank account settings changed', "تم تعديل IBAN أو رقم الحساب في إعدادات الحجز.\nالمستخدم: " . ( $user->display_name ?: $user->user_login ) . "\nالتاريخ: " . current_time( 'mysql' ) ); }
		}
	}
}

function vava_booking_save_meta( int $post_id, WP_Post $post ): void {
	if ( 'page' !== $post->post_type || ! vava_booking_is_page( $post_id ) || ! isset( $_POST['vava_booking_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vava_booking_nonce'] ) ), 'vava_booking_save' ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
	if ( function_exists( 'vava_save_bilingual_page_titles' ) ) { vava_save_bilingual_page_titles( $post_id ); }
	if ( isset( $_POST['vava_booking'] ) && is_array( $_POST['vava_booking'] ) ) {
		$raw = wp_unslash( $_POST['vava_booking'] );
		foreach ( array( 'ar', 'en' ) as $lang ) {
			if ( isset( $raw[ $lang ] ) && is_array( $raw[ $lang ] ) ) {
				$current_text = get_post_meta( $post_id, '_vava_booking_' . $lang, true );
				$current_text = is_array( $current_text ) ? $current_text : array();
				$clean_text = vava_booking_recursive_sanitize( $raw[ $lang ] );
				update_post_meta( $post_id, '_vava_booking_' . $lang, array_replace_recursive( $current_text, $clean_text ) );
			}
		}
	}
	if ( isset( $_POST['vava_booking_shared'] ) && is_array( $_POST['vava_booking_shared'] ) ) {
		$raw = wp_unslash( $_POST['vava_booking_shared'] );
		$defaults = vava_booking_shared_defaults();
		$raw = array_intersect_key( $raw, $defaults );
		$clean = vava_booking_recursive_sanitize( $raw );
		$current = vava_booking_shared_data( $post_id );
		foreach ( array_keys( $defaults['working_days'] ) as $day ) { $clean['working_days'][ $day ] = ! empty( $raw['working_days'][ $day ] ) ? 1 : 0; }

		if ( current_user_can( vava_booking_admin_capability() ) ) {
			$raw_paymob = isset( $raw['paymob'] ) && is_array( $raw['paymob'] ) ? $raw['paymob'] : array();
			if ( ! isset( $clean['paymob'] ) || ! is_array( $clean['paymob'] ) ) { $clean['paymob'] = array(); }
			foreach ( array( 'secret_key','hmac_secret' ) as $secret_key ) { if ( empty( $raw_paymob[ $secret_key ] ) ) { $clean['paymob'][ $secret_key ] = (string) ( $current['paymob'][ $secret_key ] ?? '' ); } }
			foreach ( array_keys( $defaults['payment_methods'] ) as $method ) { $clean['payment_methods'][ $method ] = ! empty( $raw['payment_methods'][ $method ] ) ? 1 : 0; }
			$next_bank = array_replace_recursive( $defaults['bank_transfer'], (array) ( $clean['bank_transfer'] ?? array() ) );
			$next_bank['iban'] = '';
			$clean['bank_transfer']['iban'] = '';
			vava_booking_audit_bank_settings( (array) ( $current['bank_transfer'] ?? array() ), $next_bank );
		} else {
			$clean['payment_methods'] = (array) ( $current['payment_methods'] ?? $defaults['payment_methods'] );
			$clean['paymob'] = (array) ( $current['paymob'] ?? $defaults['paymob'] );
			$clean['bank_transfer'] = (array) ( $current['bank_transfer'] ?? $defaults['bank_transfer'] );
		}
		update_post_meta( $post_id, '_vava_booking_shared', array_replace_recursive( $defaults, $clean ) );
	}
}
add_action( 'save_post_page', 'vava_booking_save_meta', 30, 2 );

function vava_booking_admin_columns( array $columns ): array {
	return array(
		'vava_booking_number' => 'رقم الطلب',
		'vava_booking_created' => 'تاريخ الإنشاء',
		'vava_booking_customer' => 'العميل',
		'vava_booking_service' => 'الخدمة / المنتج',
		'vava_booking_appointment' => 'الموعد / النوع',
		'vava_booking_amount' => 'القيمة',
		'vava_booking_state' => 'الحالة والدفع',
		'vava_booking_receipt' => 'إثبات التحويل',
		'vava_booking_actions' => 'الإجراءات',
	);
}
add_filter( 'manage_vava_booking_posts_columns', 'vava_booking_admin_columns' );

/** The redesigned booking screen has row-level actions only; bulk selection is intentionally disabled. */
function vava_booking_disable_bulk_actions( array $actions ): array { return array(); }
add_filter( 'bulk_actions-edit-vava_booking', 'vava_booking_disable_bulk_actions', 100 );

/** Determine whether the booked appointment has already ended. */
function vava_booking_appointment_has_ended( int $booking_id ): bool {
	$date = trim( (string) get_post_meta( $booking_id, '_vava_booking_date', true ) );
	$time = trim( (string) get_post_meta( $booking_id, '_vava_booking_time', true ) );
	if ( '' === $date || '' === $time ) { return false; }
	try {
		$start = new DateTimeImmutable( $date . ' ' . $time, wp_timezone() );
		$now   = new DateTimeImmutable( 'now', wp_timezone() );
	} catch ( Exception $exception ) {
		return false;
	}
	$duration = max( 0, absint( get_post_meta( $booking_id, '_vava_booking_duration', true ) ) );
	$end      = $duration > 0 ? $start->modify( '+' . $duration . ' minutes' ) : $start;
	return $end <= $now;
}

/** Return the actions that are valid for the current booking state. */
function vava_booking_allowed_admin_actions( int $booking_id ): array {
	$status     = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
	$method     = (string) get_post_meta( $booking_id, '_vava_booking_payment_method', true );
	$is_digital = function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( $booking_id );
	$access     = $is_digital && function_exists( 'vava_digital_products_order_access_status' ) ? vava_digital_products_order_access_status( $booking_id ) : '';
	$actions    = array();

	if ( 'cash' === $method && 'pending' === $status && ! $is_digital ) {
		$actions['approve_cash'] = array( 'label' => 'اعتماد الحجز', 'class' => 'is-approve' );
	}
	if ( 'bank' === $method && 'pending_bank_review' === $status ) {
		$actions['approve_bank'] = array( 'label' => $is_digital ? 'اعتماد التحويل وتفعيل المنتج' : 'اعتماد التحويل', 'class' => 'is-approve' );
		$actions['reject_bank'] = array( 'label' => 'رفض التحويل', 'class' => 'is-reject' );
	}
	if ( $is_digital ) {
		if ( 'active' === $access && 'paid' === vava_booking_effective_payment_status( $booking_id ) ) {
			$actions['revoke_access'] = array( 'label' => 'إيقاف صلاحية المشاهدة', 'class' => 'is-cancel' );
		} elseif ( 'revoked' === $access && 'paid' === vava_booking_effective_payment_status( $booking_id ) ) {
			$actions['restore_access'] = array( 'label' => 'إعادة تفعيل المشاهدة', 'class' => 'is-approve' );
		}
		if ( in_array( $status, array( 'pending', 'pending_payment', 'pending_bank_review' ), true ) ) {
			$actions['cancel_booking'] = array( 'label' => 'إلغاء الطلب', 'class' => 'is-cancel' );
		}
	} elseif ( 'completed' === $status ) {
		$actions['restore_confirmed'] = array( 'label' => 'إعادة الحجز إلى مؤكد', 'class' => 'is-restore' );
	} elseif ( 'cancellation_requested' === $status ) {
		$actions['approve_customer_cancel'] = array( 'label' => 'اعتماد طلب الإلغاء', 'class' => 'is-cancel' );
		$actions['reject_customer_cancel'] = array( 'label' => 'رفض طلب الإلغاء', 'class' => 'is-approve' );
	} elseif ( in_array( $status, array( 'pending', 'pending_payment', 'pending_bank_review', 'confirmed', 'paid' ), true ) ) {
		if ( in_array( $status, array( 'confirmed', 'paid' ), true ) && vava_booking_appointment_has_ended( $booking_id ) ) {
			$actions['mark_completed'] = array( 'label' => 'تحديد الحجز كمكتمل', 'class' => 'is-complete' );
		}
		$actions['cancel_booking'] = array( 'label' => 'إلغاء الحجز', 'class' => 'is-cancel' );
	}
	if ( ( $is_digital || in_array( $status, array( 'cancelled', 'customer_cancelled' ), true ) ) && vava_booking_paid_or_refunding( $booking_id ) && vava_booking_refund_remaining( $booking_id ) > 0 ) {
		$actions['refund_booking'] = array( 'label' => 'إرجاع المبلغ', 'class' => 'is-refund' );
	}
	if ( is_email( vava_booking_customer_email( $booking_id ) ) ) {
		$actions['resend_details'] = array( 'label' => $is_digital ? 'إعادة إرسال رابط منتجاتي الرقمية' : 'إعادة إرسال تفاصيل الحجز', 'class' => 'is-email' );
	}
	return $actions;
}

/**
 * Canonical URL for the isolated booking details admin page.
 *
 * The details screen is registered as its own top-level WordPress admin page.
 * This avoids the CPT submenu-parent resolution that rejected the earlier
 * edit.php?page= route before the callback could run.
 */
function vava_booking_admin_details_url( int $booking_id ): string {
	return add_query_arg(
		array(
			'page'    => 'vava-booking-details',
			'booking' => absint( $booking_id ),
		),
		admin_url( 'admin.php' )
	);
}

function vava_booking_admin_list_url( int $open_booking = 0 ): string {
	$scope = $open_booking > 0 ? ( vava_booking_order_is_product( $open_booking ) ? 'products' : 'bookings' ) : vava_booking_admin_scope();
	$args  = array( 'post_type' => 'vava_booking' );
	if ( 'products' === $scope ) { $args['vava_order_scope'] = 'products'; }
	$url = add_query_arg( $args, admin_url( 'edit.php' ) );
	return $url;
}

/** Register a real admin page; hide only its menu node, never unregister it. */
function vava_booking_register_details_page(): void {
	add_menu_page(
		'تفاصيل الطلب',
		'تفاصيل الطلب',
		vava_booking_admin_capability(),
		'vava-booking-details',
		'vava_booking_render_details_page',
		'dashicons-calendar-alt',
		99
	);
}
add_action( 'admin_menu', 'vava_booking_register_details_page', 20 );

/** Hide the dedicated route visually while keeping WordPress registration intact. */
function vava_booking_hide_details_menu_css(): void {
	?>
	<style id="vava-booking-details-menu-guard">
		#toplevel_page_vava-booking-details { display: none !important; }
	</style>
	<?php
}
add_action( 'admin_head', 'vava_booking_hide_details_menu_css', 2 );

/** Redirect all obsolete route variants to the canonical admin.php page. */
function vava_booking_redirect_legacy_details_url(): void {
	global $pagenow;

	$is_old_plugin_route = 'edit.php' === $pagenow
		&& isset( $_GET['page'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		&& 'vava-booking-details' === sanitize_key( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$is_old_query_route = 'edit.php' === $pagenow
		&& isset( $_GET['post_type'], $_GET['vava_booking_screen'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		&& 'vava_booking' === sanitize_key( wp_unslash( $_GET['post_type'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		&& 'details' === sanitize_key( wp_unslash( $_GET['vava_booking_screen'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( ! $is_old_plugin_route && ! $is_old_query_route ) {
		return;
	}
	if ( ! current_user_can( vava_booking_admin_capability() ) ) {
		return;
	}

	$booking_id = isset( $_GET['booking'] ) ? absint( $_GET['booking'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	wp_safe_redirect( $booking_id ? vava_booking_admin_details_url( $booking_id ) : vava_booking_admin_list_url() );
	exit;
}
add_action( 'admin_init', 'vava_booking_redirect_legacy_details_url', 0 );

/** Keep the VAVA Bookings parent highlighted while viewing the hidden route. */
function vava_booking_details_parent_file( $parent_file ): string {
	if ( isset( $_GET['page'] ) && 'vava-booking-details' === sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return 'edit.php?post_type=vava_booking';
	}
	return (string) $parent_file;
}
add_filter( 'parent_file', 'vava_booking_details_parent_file', 50 );

function vava_booking_render_details_page(): void {
	if ( ! current_user_can( vava_booking_admin_capability() ) ) {
		wp_die( 'غير مسموح.', '', array( 'response' => 403 ) );
	}

	$booking_id = isset( $_GET['booking'] ) ? absint( $_GET['booking'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $booking_id || 'vava_booking' !== get_post_type( $booking_id ) ) {
		wp_die( 'الحجز غير موجود.', '', array( 'response' => 404 ) );
	}

	$is_product = vava_booking_order_is_product( $booking_id )
		|| ( function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( $booking_id ) );

	try {
		if ( $is_product ) {
			if ( ! function_exists( 'vava_digital_products_render_admin_order_details' ) ) {
				throw new RuntimeException( 'Digital product order renderer is unavailable.' );
			}
			echo '<div class="wrap vava-booking-details-page vava-products-details-page" dir="rtl" data-vava-booking-details-page data-booking-id="' . esc_attr( (string) $booking_id ) . '">';
			vava_digital_products_render_admin_order_details( $booking_id, false );
			echo '</div>';
		} else {
			vava_booking_render_admin_fullpage_content( $booking_id );
		}
	} catch ( Throwable $error ) {
		error_log( sprintf( 'VAVA order details #%d: %s in %s:%d', $booking_id, $error->getMessage(), $error->getFile(), $error->getLine() ) );
		$list_url = vava_booking_admin_list_url( $booking_id );
		$title    = $is_product ? 'تعذر عرض تفاصيل طلب المنتج.' : 'تعذر عرض تفاصيل الحجز.';
		echo '<div class="wrap vava-booking-details-recovery" dir="rtl"><div class="notice notice-error"><p><strong>' . esc_html( $title ) . '</strong> تم تسجيل الخطأ دون التأثير على بقية لوحة التحكم.</p></div><p><a class="button button-primary" href="' . esc_url( $list_url ) . '">' . esc_html( $is_product ? 'العودة إلى منتجات VAVA' : 'العودة إلى حجوزات VAVA' ) . '</a></p></div>';
	}
}

function vava_booking_admin_status_group( string $status ): string {
	if ( 'completed' === $status ) { return 'completed'; }
	if ( in_array( $status, array( 'confirmed', 'paid' ), true ) ) { return 'confirmed'; }
	if ( in_array( $status, array( 'pending', 'pending_payment', 'pending_bank_review', 'cancellation_requested' ), true ) ) { return 'pending'; }
	if ( in_array( $status, array( 'cancelled', 'customer_cancelled', 'bank_rejected', 'payment_failed', 'payment_error' ), true ) ) { return 'cancelled'; }
	return 'other';
}

function vava_booking_admin_counts( string $scope = '' ): array {
	global $wpdb;
	$scope  = $scope ?: vava_booking_admin_scope();
	$counts = array( 'all' => 0, 'confirmed' => 0, 'completed' => 0, 'pending' => 0, 'cancelled' => 0 );
	$type_clause = 'products' === $scope
		? "AND type_pm.meta_value IN ('digital_product','tangible_product','physical_product')"
		: "AND (type_pm.meta_value IS NULL OR type_pm.meta_value NOT IN ('digital_product','tangible_product','physical_product'))";
	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT status_pm.meta_value AS booking_status, COUNT(DISTINCT p.ID) AS total
		FROM {$wpdb->posts} p
		LEFT JOIN {$wpdb->postmeta} status_pm ON status_pm.post_id = p.ID AND status_pm.meta_key = %s
		LEFT JOIN {$wpdb->postmeta} type_pm ON type_pm.post_id = p.ID AND type_pm.meta_key = %s
		WHERE p.post_type = %s AND p.post_status = %s {$type_clause}
		GROUP BY status_pm.meta_value",
		'_vava_booking_status', '_vava_booking_order_type', 'vava_booking', 'publish'
	), ARRAY_A );
	foreach ( (array) $rows as $row ) {
		$total = absint( $row['total'] ?? 0 );
		$counts['all'] += $total;
		$group = vava_booking_admin_status_group( (string) ( $row['booking_status'] ?? '' ) );
		if ( isset( $counts[ $group ] ) ) { $counts[ $group ] += $total; }
	}
	return $counts;
}

function vava_booking_admin_view_url( string $view ): string {
	$args = array( 'post_type' => 'vava_booking' );
	if ( 'products' === vava_booking_admin_scope() ) { $args['vava_order_scope'] = 'products'; }
	if ( 'all' !== $view ) { $args['vava_booking_view'] = $view; }
	$lookup = isset( $_GET['vava_booking_lookup'] ) ? sanitize_text_field( wp_unslash( $_GET['vava_booking_lookup'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( '' !== $lookup ) { $args['vava_booking_lookup'] = $lookup; }
	return add_query_arg( $args, admin_url( 'edit.php' ) );
}

function vava_booking_admin_dashboard_header(): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-vava_booking' !== $screen->id ) { return; }
	$scope = vava_booking_admin_scope();
	$counts = vava_booking_admin_counts( $scope );
	$current = isset( $_GET['vava_booking_view'] ) ? sanitize_key( wp_unslash( $_GET['vava_booking_view'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$is_products = 'products' === $scope;
	$cards = array(
		'all' => array( 'label' => $is_products ? 'كل المنتجات' : 'كل الحجوزات', 'caption' => $is_products ? 'إجمالي طلبات المنتجات' : 'إجمالي الحجوزات', 'icon' => $is_products ? 'products' : 'calendar-alt' ),
		'confirmed' => array( 'label' => $is_products ? 'مفعّلة أو مكتملة' : 'مؤكدة', 'caption' => $is_products ? 'طلبات منتجات معتمدة' : 'حجوزات جاهزة', 'icon' => 'yes-alt' ),
		'pending' => array( 'label' => 'بانتظار التأكيد', 'caption' => $is_products ? 'طلبات تحتاج مراجعة' : 'تحتاج إجراء', 'icon' => 'clock' ),
		'cancelled' => array( 'label' => 'ملغاة أو مرفوضة', 'caption' => $is_products ? 'طلبات منتجات غير نشطة' : 'حجوزات غير نشطة', 'icon' => 'dismiss' ),
	);
	if ( ! $is_products ) {
		$cards = array_slice( $cards, 0, 2, true ) + array( 'completed' => array( 'label' => 'مكتملة', 'caption' => 'خدمات انتهت بالكامل', 'icon' => 'awards' ) ) + array_slice( $cards, 2, null, true );
	}
	if ( ! isset( $cards[ $current ] ) ) { $current = 'all'; }
	?>
	<?php $lookup = isset( $_GET['vava_booking_lookup'] ) ? sanitize_text_field( wp_unslash( $_GET['vava_booking_lookup'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
	<section class="vava-booking-dashboard" dir="rtl">
		<div class="vava-booking-dashboard-heading">
			<div><span><?php echo esc_html( $is_products ? 'إدارة المنتجات' : 'إدارة الحجوزات' ); ?></span><h1><?php echo esc_html( $is_products ? 'منتجات VAVA' : 'حجوزات VAVA' ); ?></h1><p><?php echo esc_html( $is_products ? 'إدارة طلبات المنتجات الرقمية والمادية واعتماد التحويلات والصلاحيات من مكان مستقل.' : 'إدارة حجوزات الجلسات ومتابعة المواعيد وإجراءات الدفع من مكان واحد.' ); ?></p></div>
			<form class="vava-booking-dashboard-search" method="get" action="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">
				<input type="hidden" name="post_type" value="vava_booking"/>
				<?php if ( $is_products ) : ?><input type="hidden" name="vava_order_scope" value="products"/><?php endif; ?>
				<?php if ( 'all' !== $current ) : ?><input type="hidden" name="vava_booking_view" value="<?php echo esc_attr( $current ); ?>"/><?php endif; ?>
				<label><span class="dashicons dashicons-search" aria-hidden="true"></span><input type="search" name="vava_booking_lookup" value="<?php echo esc_attr( $lookup ); ?>" placeholder="<?php echo esc_attr( $is_products ? 'ابحث باسم العميل أو رقم الطلب' : 'ابحث باسم العميل أو رقم الحجز' ); ?>" aria-label="<?php echo esc_attr( $is_products ? 'البحث باسم العميل أو رقم الطلب' : 'البحث باسم العميل أو رقم الحجز' ); ?>"/></label>
				<button type="submit" class="button button-primary">بحث</button>
				<?php if ( '' !== $lookup ) : $clear_args = array( 'post_type' => 'vava_booking' ); if ( $is_products ) { $clear_args['vava_order_scope'] = 'products'; } if ( 'all' !== $current ) { $clear_args['vava_booking_view'] = $current; } ?><a class="button vava-booking-dashboard-search-clear" href="<?php echo esc_url( add_query_arg( $clear_args, admin_url( 'edit.php' ) ) ); ?>">مسح</a><?php endif; ?>
			</form>
		</div>
		<div class="vava-booking-stat-grid">
			<?php foreach ( $cards as $key => $card ) : ?>
			<?php $is_active_card = $current === $key; ?>
			<a class="vava-booking-stat-card <?php echo $is_active_card ? 'is-active' : ''; ?>" href="<?php echo esc_url( vava_booking_admin_view_url( $key ) ); ?>" data-vava-stat-card="<?php echo esc_attr( $key ); ?>"<?php echo $is_active_card ? ' aria-current="page"' : ''; ?>>
				<span class="dashicons dashicons-<?php echo esc_attr( $card['icon'] ); ?>" aria-hidden="true"></span>
				<div class="vava-booking-stat-copy"><strong data-vava-count-key="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( (string) ( $counts[ $key ] ?? 0 ) ); ?></strong><small><?php echo esc_html( $card['label'] ); ?></small><em><?php echo esc_html( $card['caption'] ); ?></em></div>
				<?php if ( $is_active_card ) : ?><span class="vava-booking-stat-current"><?php echo esc_html( $is_products ? 'يتم عرض هذه الطلبات' : 'يتم عرض هذه الحجوزات' ); ?></span><?php endif; ?>
			</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}
add_action( 'all_admin_notices', 'vava_booking_admin_dashboard_header' );

function vava_booking_admin_apply_filters( WP_Query $query ): void {
	if ( ! is_admin() || ! $query->is_main_query() || 'vava_booking' !== $query->get( 'post_type' ) ) { return; }
	$query->set( 's', '' );
	$query->set( 'posts_per_page', 10 );
	$request_page = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : max( 1, absint( $query->get( 'paged' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$query->set( 'paged', $request_page );
	$scope = vava_booking_admin_scope();
	$view = isset( $_GET['vava_booking_view'] ) ? sanitize_key( wp_unslash( $_GET['vava_booking_view'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$lookup = isset( $_GET['vava_booking_lookup'] ) ? sanitize_text_field( wp_unslash( $_GET['vava_booking_lookup'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$group_statuses = array(
		'confirmed' => array( 'confirmed', 'paid' ),
		'completed' => array( 'completed' ),
		'pending' => array( 'pending', 'pending_payment', 'pending_bank_review', 'cancellation_requested' ),
		'cancelled' => array( 'cancelled', 'customer_cancelled', 'bank_rejected', 'payment_failed', 'payment_error' ),
	);
	$type_clause = 'products' === $scope
		? array( 'key' => '_vava_booking_order_type', 'value' => array( 'digital_product', 'tangible_product', 'physical_product' ), 'compare' => 'IN' )
		: array( 'relation' => 'OR', array( 'key' => '_vava_booking_order_type', 'compare' => 'NOT EXISTS' ), array( 'key' => '_vava_booking_order_type', 'value' => array( 'digital_product', 'tangible_product', 'physical_product' ), 'compare' => 'NOT IN' ) );
	$meta_query = array( 'relation' => 'AND', $type_clause );
	if ( isset( $group_statuses[ $view ] ) ) { $meta_query[] = array( 'key' => '_vava_booking_status', 'value' => $group_statuses[ $view ], 'compare' => 'IN' ); }
	$query->set( 'meta_query', $meta_query );
	if ( '' !== $lookup ) { $query->set( 'vava_booking_lookup', $lookup ); }
	$query->set( 'orderby', 'date' );
	$query->set( 'order', 'DESC' );
}
add_action( 'pre_get_posts', 'vava_booking_admin_apply_filters' );

function vava_booking_admin_lookup_where( string $where, WP_Query $query ): string {
	global $wpdb;
	if ( ! is_admin() || ! $query->is_main_query() || 'vava_booking' !== $query->get( 'post_type' ) ) { return $where; }
	$lookup = trim( (string) $query->get( 'vava_booking_lookup' ) );
	if ( '' === $lookup ) { return $where; }
	$like = '%' . $wpdb->esc_like( $lookup ) . '%';
	$customer_match = $wpdb->prepare( "EXISTS (SELECT 1 FROM {$wpdb->postmeta} vava_lookup_pm WHERE vava_lookup_pm.post_id = {$wpdb->posts}.ID AND vava_lookup_pm.meta_key IN ('_vava_booking_customer','_vava_booking_customer_email') AND vava_lookup_pm.meta_value LIKE %s)", $like );
	$booking_number = ltrim( $lookup, '# ' );
	if ( ctype_digit( $booking_number ) ) {
		$id_match = $wpdb->prepare( "{$wpdb->posts}.ID = %d", absint( $booking_number ) );
		$where .= " AND ({$id_match} OR {$customer_match})";
	} else {
		$where .= " AND ({$customer_match})";
	}
	return $where;
}

add_filter( 'posts_where', 'vava_booking_admin_lookup_where', 20, 2 );

function vava_booking_admin_numeric_pagination( string $which ): void {
	if ( 'bottom' !== $which ) { return; }
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-vava_booking' !== $screen->id ) { return; }
	global $wp_query;
	$total_pages = max( 1, absint( $wp_query->max_num_pages ?? 1 ) );
	if ( $total_pages <= 1 ) { return; }
	$current = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : max( 1, absint( $wp_query->get( 'paged' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$args = array( 'post_type' => 'vava_booking' );
	if ( 'products' === vava_booking_admin_scope() ) { $args['vava_order_scope'] = 'products'; }
	$view = isset( $_GET['vava_booking_view'] ) ? sanitize_key( wp_unslash( $_GET['vava_booking_view'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$lookup = isset( $_GET['vava_booking_lookup'] ) ? sanitize_text_field( wp_unslash( $_GET['vava_booking_lookup'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $view ) { $args['vava_booking_view'] = $view; }
	if ( $lookup ) { $args['vava_booking_lookup'] = $lookup; }
	$start = max( 1, $current - 2 );
	$end = min( $total_pages, $current + 2 );
	if ( $current <= 3 ) { $end = min( $total_pages, 5 ); }
	if ( $current >= $total_pages - 2 ) { $start = max( 1, $total_pages - 4 ); }
	?>
	<nav class="vava-booking-numeric-pagination" aria-label="صفحات الحجوزات">
		<?php if ( $current > 1 ) : ?><a href="<?php echo esc_url( add_query_arg( array_merge( $args, array( 'paged' => $current - 1 ) ), admin_url( 'edit.php' ) ) ); ?>" aria-label="الصفحة السابقة">‹</a><?php endif; ?>
		<?php if ( $start > 1 ) : ?><a href="<?php echo esc_url( add_query_arg( array_merge( $args, array( 'paged' => 1 ) ), admin_url( 'edit.php' ) ) ); ?>">1</a><?php if ( $start > 2 ) : ?><span>…</span><?php endif; ?><?php endif; ?>
		<?php for ( $page = $start; $page <= $end; $page++ ) : ?>
			<?php if ( $page === $current ) : ?><strong aria-current="page"><?php echo esc_html( (string) $page ); ?></strong><?php else : ?><a href="<?php echo esc_url( add_query_arg( array_merge( $args, array( 'paged' => $page ) ), admin_url( 'edit.php' ) ) ); ?>"><?php echo esc_html( (string) $page ); ?></a><?php endif; ?>
		<?php endfor; ?>
		<?php if ( $end < $total_pages ) : ?><?php if ( $end < $total_pages - 1 ) : ?><span>…</span><?php endif; ?><a href="<?php echo esc_url( add_query_arg( array_merge( $args, array( 'paged' => $total_pages ) ), admin_url( 'edit.php' ) ) ); ?>"><?php echo esc_html( (string) $total_pages ); ?></a><?php endif; ?>
		<?php if ( $current < $total_pages ) : ?><a href="<?php echo esc_url( add_query_arg( array_merge( $args, array( 'paged' => $current + 1 ) ), admin_url( 'edit.php' ) ) ); ?>" aria-label="الصفحة التالية">›</a><?php endif; ?>
	</nav>
	<?php
}
add_action( 'manage_posts_extra_tablenav', 'vava_booking_admin_numeric_pagination', 20 );

function vava_booking_admin_created_parts( int $post_id ): array {
	$created = (string) get_post_meta( $post_id, '_vava_booking_created_at', true );
	if ( ! $created ) { $created = (string) get_post_field( 'post_date', $post_id ); }
	$timestamp = $created ? strtotime( $created ) : false;
	return $timestamp ? array( 'date' => wp_date( 'Y-m-d', $timestamp ), 'time' => vava_booking_format_time_12h( wp_date( 'H:i', $timestamp ) ) ) : array( 'date' => '', 'time' => '' );
}

function vava_booking_admin_created_label( int $post_id ): string {
	$parts = vava_booking_admin_created_parts( $post_id );
	return trim( $parts['date'] . ( $parts['time'] ? ' · ' . $parts['time'] : '' ) );
}

function vava_booking_admin_service_thumbnail( int $post_id ): string {
	$image_id = absint( get_post_meta( $post_id, '_vava_booking_service_image_id', true ) );
	return $image_id ? (string) wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
}

function vava_booking_render_state_cell( int $post_id ): void {
	$status = (string) get_post_meta( $post_id, '_vava_booking_status', true );
	$payment_status = vava_booking_effective_payment_status( $post_id );
	$method = (string) get_post_meta( $post_id, '_vava_booking_payment_method', true );
	?>
	<div class="vava-booking-state-stack" data-booking-state="<?php echo esc_attr( (string) $post_id ); ?>">
		<span class="vava-booking-admin-status is-<?php echo esc_attr( sanitize_html_class( $status ) ); ?>"><?php echo esc_html( vava_booking_status_label( $status ) ); ?></span>
		<span class="vava-booking-admin-status is-<?php echo esc_attr( sanitize_html_class( $payment_status ) ); ?>"><?php echo esc_html( vava_booking_payment_status_label( $payment_status ) ); ?></span>
		<small><?php echo esc_html( vava_booking_payment_method_label( $method ) ); ?></small>
	</div>
	<?php
}

// V1.14 — fixed inline select keeps every booking action inside the table.
function vava_booking_render_row_actions( int $post_id ): void {
	$actions = vava_booking_allowed_admin_actions( $post_id );
	unset( $actions['refund_booking'], $actions['resend_details'] );
	?>
	<div class="vava-booking-row-actions" data-booking-actions="<?php echo esc_attr( (string) $post_id ); ?>">
		<label class="screen-reader-text" for="vava-booking-action-<?php echo esc_attr( (string) $post_id ); ?>">اختر إجراء الحجز</label>
		<div class="vava-booking-action-picker" data-vava-action-picker>
			<button type="button" class="vava-booking-action-picker-trigger" data-vava-action-picker-trigger aria-expanded="false"><span data-vava-action-picker-label>اختر الإجراء</span><svg aria-hidden="true" viewBox="0 0 20 20"><path d="m5 7 5 5 5-5"/></svg></button>
			<div class="vava-booking-action-picker-menu" data-vava-action-picker-menu role="listbox" hidden>
				<button type="button" role="option" data-vava-action-value="view_details" data-url="<?php echo esc_url( vava_booking_admin_details_url( $post_id ) ); ?>">عرض التفاصيل</button>
				<?php foreach ( $actions as $decision => $definition ) : ?><button type="button" role="option" data-vava-action-value="<?php echo esc_attr( $decision ); ?>"><?php echo esc_html( (string) ( $definition['label'] ?? '' ) ); ?></button><?php endforeach; ?>
			</div>
			<select id="vava-booking-action-<?php echo esc_attr( (string) $post_id ); ?>" class="vava-booking-action-select" data-booking-id="<?php echo esc_attr( (string) $post_id ); ?>" tabindex="-1" aria-hidden="true">
				<option value="">اختر الإجراء</option>
				<option value="view_details" data-url="<?php echo esc_url( vava_booking_admin_details_url( $post_id ) ); ?>">عرض التفاصيل</option>
				<?php foreach ( $actions as $decision => $definition ) : ?><option value="<?php echo esc_attr( $decision ); ?>"><?php echo esc_html( (string) ( $definition['label'] ?? '' ) ); ?></option><?php endforeach; ?>
			</select>
		</div>
		<button type="button" class="button vava-booking-action-execute" data-vava-booking-action-execute data-booking-id="<?php echo esc_attr( (string) $post_id ); ?>">تنفيذ</button>
	</div>
	<?php
}

function vava_booking_admin_receipt_url( int $booking_id, bool $download = false ): string {
	$receipt = vava_booking_get_receipt( $booking_id, true );
	$attachment_id = absint( $receipt['attachment_id'] ?? 0 );
	if ( $attachment_id ) {
		$url = wp_get_attachment_url( $attachment_id );
		return $url ? (string) $url : '';
	}
	$filename = basename( (string) ( $receipt['file'] ?? '' ) );
	if ( ! $filename ) { return ''; }
	$path = trailingslashit( vava_booking_private_receipt_dir() ) . $filename;
	if ( ! is_file( $path ) ) { return ''; }
	$action = $download ? 'vava_booking_download_receipt' : 'vava_booking_view_receipt';
	return wp_nonce_url( admin_url( 'admin-post.php?action=' . $action . '&booking=' . $booking_id ), 'vava_booking_receipt_' . $booking_id );
}

function vava_booking_render_receipt_cell( int $post_id ): void {
	$method = (string) get_post_meta( $post_id, '_vava_booking_payment_method', true );
	$receipt = vava_booking_get_receipt( $post_id, true );
	$url = vava_booking_admin_receipt_url( $post_id, false );
	if ( 'bank' !== $method ) {
		echo '<div class="vava-booking-receipt-cell is-empty"><span>—</span><small>غير مطلوب</small></div>';
		return;
	}
	if ( ! $url ) {
		echo '<div class="vava-booking-receipt-cell is-empty is-unavailable"><span>!</span><small>الملف غير متاح</small></div>';
		return;
	}
	$mime = strtolower( (string) ( $receipt['mime'] ?? '' ) );
	$label = (string) ( $receipt['original'] ?? 'إيصال التحويل' );
	echo '<div class="vava-booking-receipt-cell">';
	if ( 0 === strpos( $mime, 'image/' ) ) {
		echo '<a class="vava-booking-receipt-preview" href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="عرض إيصال التحويل"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $label ) . '"></a>';
	} else {
		echo '<a class="vava-booking-receipt-preview is-document" href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="عرض إيصال التحويل"><span class="dashicons dashicons-media-document" aria-hidden="true"></span><b>PDF</b></a>';
	}
	echo '<small>عرض الإيصال</small></div>';
}

function vava_booking_customer_can_replace_receipt( int $booking_id ): bool {
	if ( ! $booking_id || 'bank' !== get_post_meta( $booking_id, '_vava_booking_payment_method', true ) ) { return false; }
	$status = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
	$payment = vava_booking_effective_payment_status( $booking_id );
	$receipt_url = vava_booking_receipt_public_url( $booking_id );
	return ! $receipt_url
		|| in_array( $status, array( 'pending_bank_review', 'bank_rejected', 'payment_error' ), true )
		|| in_array( $payment, array( 'pending_bank_review', 'rejected', 'failed' ), true );
}

function vava_booking_replace_receipt( int $booking_id, array $file, int $user_id = 0 ) {
	if ( ! $booking_id || 'vava_booking' !== get_post_type( $booking_id ) ) { return new WP_Error( 'invalid_booking', 'الحجز غير موجود.' ); }
	if ( 'bank' !== get_post_meta( $booking_id, '_vava_booking_payment_method', true ) ) { return new WP_Error( 'invalid_method', 'هذا الحجز لا يستخدم التحويل البنكي.' ); }
	$new_receipt = vava_booking_store_bank_receipt( $file, $booking_id );
	if ( is_wp_error( $new_receipt ) ) { return $new_receipt; }
	$old_receipt = vava_booking_get_receipt( $booking_id, false );
	$history = (array) get_post_meta( $booking_id, '_vava_booking_bank_receipt_history', true );
	if ( $old_receipt ) {
		$old_receipt['replaced_at'] = current_time( 'mysql' );
		$old_receipt['replaced_by'] = $user_id;
		$history[] = $old_receipt;
		update_post_meta( $booking_id, '_vava_booking_bank_receipt_history', array_slice( $history, -20 ) );
	}
	update_post_meta( $booking_id, '_vava_booking_bank_receipt', $new_receipt );
	$old_status = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
	$old_payment = vava_booking_effective_payment_status( $booking_id );
	update_post_meta( $booking_id, '_vava_booking_status', 'pending_bank_review' );
	update_post_meta( $booking_id, '_vava_booking_payment_status', 'pending_bank_review' );
	update_post_meta( $booking_id, '_vava_booking_receipt_replaced_at', current_time( 'mysql' ) );
	update_post_meta( $booking_id, '_vava_booking_receipt_replaced_by', $user_id );
	vava_booking_append_action_log( $booking_id, 'receipt_replaced', 'تم رفع أو استبدال إثبات التحويل.', $old_status, 'pending_bank_review', $old_payment, 'pending_bank_review' );
	return $new_receipt;
}

function vava_booking_admin_column_value( string $column, int $post_id ): void {
	$customer = (array) get_post_meta( $post_id, '_vava_booking_customer', true );
	$is_digital = function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( $post_id );
	switch ( $column ) {
		case 'vava_booking_number':
			echo '<div class="vava-booking-cell vava-booking-cell--number"><a class="vava-booking-admin-number" href="' . esc_url( vava_booking_admin_details_url( $post_id ) ) . '">#' . esc_html( (string) $post_id ) . '</a></div>';
			break;
		case 'vava_booking_created':
			$created = vava_booking_admin_created_parts( $post_id );
			echo '<div class="vava-booking-cell vava-booking-cell--created"><strong class="vava-booking-ltr">' . esc_html( $created['date'] ?: '—' ) . '</strong><small class="vava-booking-ltr">' . esc_html( $created['time'] ) . '</small></div>';
			break;
		case 'vava_booking_customer':
			echo '<div class="vava-booking-cell vava-booking-cell--customer"><strong dir="auto">' . esc_html( (string) ( $customer['name'] ?? '—' ) ) . '</strong><small class="vava-booking-ltr">' . esc_html( (string) ( $customer['whatsapp'] ?? '' ) ) . '</small><small class="vava-booking-ltr">' . esc_html( (string) ( $customer['email'] ?? '' ) ) . '</small></div>';
			break;
		case 'vava_booking_service':
			$kind = $is_digital ? 'منتج رقمي' : (string) get_post_meta( $post_id, '_vava_booking_service_kind', true );
			$duration = vava_booking_display_duration_for_booking( $post_id, 'ar' );
			echo '<div class="vava-booking-service-cell is-text-only"><div><strong>' . esc_html( (string) get_post_meta( $post_id, '_vava_booking_service_title', true ) ) . '</strong><small>' . esc_html( $kind ) . '</small>' . ( ! $is_digital && '—' !== $duration ? '<small>' . esc_html( $duration ) . '</small>' : '' ) . '</div></div>';
			break;
		case 'vava_booking_appointment':
			if ( $is_digital ) {
				$access = function_exists( 'vava_digital_products_order_access_status' ) ? vava_digital_products_order_access_status( $post_id ) : 'pending';
				$access_label = array( 'active' => 'المشاهدة مفعّلة', 'pending' => 'بانتظار التفعيل', 'revoked' => 'المشاهدة موقوفة', 'rejected' => 'مرفوض' );
				echo '<div class="vava-booking-cell vava-booking-cell--appointment"><strong>منتج رقمي</strong><small>' . esc_html( $access_label[ $access ] ?? $access ) . '</small></div>';
			} else {
				echo '<div class="vava-booking-cell vava-booking-cell--appointment"><strong class="vava-booking-ltr">' . esc_html( (string) get_post_meta( $post_id, '_vava_booking_date', true ) ) . '</strong><small class="vava-booking-ltr">' . esc_html( vava_booking_format_time_12h( (string) get_post_meta( $post_id, '_vava_booking_time', true ) ) ) . '</small></div>';
			}
			break;
		case 'vava_booking_amount':
			echo '<div class="vava-booking-cell vava-booking-cell--amount"><strong dir="auto">' . esc_html( vava_booking_format_price_label( (string) get_post_meta( $post_id, '_vava_booking_service_price', true ), (string) get_post_meta( $post_id, '_vava_booking_service_currency', true ), 'ar' ) ) . '</strong></div>';
			break;
		case 'vava_booking_state':
			vava_booking_render_state_cell( $post_id );
			break;
		case 'vava_booking_receipt':
			vava_booking_render_receipt_cell( $post_id );
			break;
		case 'vava_booking_actions':
			vava_booking_render_row_actions( $post_id );
			break;
	}
}
add_action( 'manage_vava_booking_posts_custom_column', 'vava_booking_admin_column_value', 10, 2 );

function vava_booking_admin_row_actions( array $actions, WP_Post $post ): array {
	if ( 'vava_booking' !== $post->post_type ) { return $actions; }
	return array();
}
add_filter( 'post_row_actions', 'vava_booking_admin_row_actions', 10, 2 );

function vava_booking_add_admin_details_box(): void {
	remove_meta_box( 'submitdiv', 'vava_booking', 'side' );
	add_meta_box( 'vava_booking_details', 'تفاصيل الحجز وإدارته', 'vava_booking_render_admin_details', 'vava_booking', 'normal', 'high' );
}
add_action( 'add_meta_boxes_vava_booking', 'vava_booking_add_admin_details_box' );

function vava_booking_admin_detail_row( string $label, string $value, string $direction = '' ): void {
	?><div class="vava-booking-detail-row"><span><?php echo esc_html( $label ); ?></span><strong<?php echo $direction ? ' dir="' . esc_attr( $direction ) . '"' : ''; ?>><?php echo esc_html( $value ?: '—' ); ?></strong></div><?php
}

function vava_booking_action_log( int $booking_id ): array {
	$log = get_post_meta( $booking_id, '_vava_booking_action_log', true );
	return is_array( $log ) ? array_values( $log ) : array();
}

function vava_booking_append_action_log( int $booking_id, string $action, string $note, string $old_status, string $new_status, string $old_payment, string $new_payment ): void {
	$log = vava_booking_action_log( $booking_id );
	$log[] = array(
		'action' => sanitize_key( $action ),
		'note' => sanitize_textarea_field( $note ),
		'old_status' => sanitize_key( $old_status ),
		'new_status' => sanitize_key( $new_status ),
		'old_payment_status' => sanitize_key( $old_payment ),
		'new_payment_status' => sanitize_key( $new_payment ),
		'user_id' => get_current_user_id(),
		'time' => current_time( 'mysql' ),
	);
	if ( count( $log ) > 100 ) { $log = array_slice( $log, -100 ); }
	update_post_meta( $booking_id, '_vava_booking_action_log', $log );
}

function vava_booking_action_label( string $action ): string {
	$labels = array(
		'approve_cash' => 'اعتماد حجز الدفع لاحقًا', 'approve_bank' => 'اعتماد التحويل البنكي', 'reject_bank' => 'رفض التحويل البنكي',
		'cancel_booking' => 'إلغاء الحجز', 'customer_cancelled' => 'إلغاء بواسطة العميل', 'customer_cancel_requested' => 'طلب إلغاء بواسطة العميل',
		'approve_customer_cancel' => 'اعتماد طلب إلغاء العميل', 'reject_customer_cancel' => 'رفض طلب إلغاء العميل',
		'note_saved' => 'حفظ ملاحظة إدارية', 'resend_details' => 'إعادة إرسال التفاصيل', 'refund_recorded' => 'تسجيل استرداد مبلغ',
		'mark_completed' => 'تحديد الحجز كمكتمل', 'restore_confirmed' => 'إعادة الحجز إلى مؤكد',
		'revoke_access' => 'إيقاف صلاحية المشاهدة', 'restore_access' => 'إعادة تفعيل المشاهدة',
	);
	return $labels[ $action ] ?? $action;
}

/** Send a separate status-change email; this is never blocked by the initial booking email. */
function vava_booking_send_status_update( int $booking_id, string $event, string $note = '' ): void {
	if ( function_exists( 'vava_mail_notifications_enabled' ) && ! vava_mail_notifications_enabled( 'bookings' ) ) { return; }
	$customer = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
	$email = sanitize_email( (string) ( $customer['email'] ?? '' ) );
	if ( ! $email ) { return; }
	$lang = 'en' === get_post_meta( $booking_id, '_vava_booking_language', true ) ? 'en' : 'ar';
	$service = (string) get_post_meta( $booking_id, '_vava_booking_service_title', true );
	$date = (string) get_post_meta( $booking_id, '_vava_booking_date', true );
	$time = vava_booking_format_time_12h( (string) get_post_meta( $booking_id, '_vava_booking_time', true ) );
	$method = (string) get_post_meta( $booking_id, '_vava_booking_payment_method', true );
	$status = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
	$events_ar = array(
		'approved' => array( 'تم اعتماد حجزك مع VAVA', 'تم اعتماد الحجز وأصبح مؤكدًا.' ),
		'bank_approved' => array( 'تم اعتماد التحويل البنكي', 'تم اعتماد التحويل البنكي وتأكيد حجزك.' ),
		'bank_rejected' => array( 'تعذر اعتماد التحويل البنكي', 'تم رفض التحويل البنكي المرفق بالحجز.' ),
		'cancelled' => array( 'تم إلغاء حجزك مع VAVA', 'تم إلغاء الحجز وأصبح الموعد متاحًا للحجز من جديد.' ),
		'customer_cancelled' => array( 'تم إلغاء حجزك مع VAVA', 'تم إلغاء الحجز بناءً على طلبك وأصبح الموعد متاحًا للحجز من جديد.' ),
		'cancellation_requested' => array( 'تم استلام طلب إلغاء الحجز', 'تم إرسال طلب الإلغاء إلى فريق VAVA للمراجعة.' ),
		'cancellation_request_rejected' => array( 'تعذر اعتماد طلب الإلغاء', 'لم يتم اعتماد طلب إلغاء الحجز، وما زال الحجز قائمًا.' ),
		'completed' => array( 'اكتملت خدمتك مع VAVA', 'تم تسجيل الحجز كمكتمل، ويمكنك الآن تعبئة استبيان أثر الرحلة من صفحة حسابك.' ),
		'restored_confirmed' => array( 'تم تحديث حالة حجزك', 'تمت إعادة حالة الحجز من مكتمل إلى مؤكد.' ),
	);
	$events_en = array(
		'approved' => array( 'Your VAVA booking is approved', 'Your booking has been approved and confirmed.' ),
		'bank_approved' => array( 'Your bank transfer is approved', 'Your bank transfer was approved and your booking is confirmed.' ),
		'bank_rejected' => array( 'Your bank transfer was not approved', 'The bank transfer attached to the booking was rejected.' ),
		'cancelled' => array( 'Your VAVA booking was cancelled', 'Your booking has been cancelled.' ),
		'customer_cancelled' => array( 'Your VAVA booking was cancelled', 'Your booking was cancelled at your request and the appointment is available again.' ),
		'cancellation_requested' => array( 'Your cancellation request was received', 'Your cancellation request has been sent to the VAVA team for review.' ),
		'cancellation_request_rejected' => array( 'Your cancellation request was not approved', 'The cancellation request was not approved and the booking remains active.' ),
		'completed' => array( 'Your VAVA service is complete', 'Your booking was marked as completed. You can now complete the journey impact questionnaire from your account.' ),
		'restored_confirmed' => array( 'Your booking status was updated', 'Your booking was moved from completed back to confirmed.' ),
	);
	$copy = 'en' === $lang ? ( $events_en[ $event ] ?? $events_en['cancelled'] ) : ( $events_ar[ $event ] ?? $events_ar['cancelled'] );
	$lines = array(
		$copy[1], '',
		( 'en' === $lang ? 'Booking number: #' : 'رقم الحجز: #' ) . $booking_id,
		( 'en' === $lang ? 'Service: ' : 'الخدمة: ' ) . $service,
		( 'en' === $lang ? 'Appointment: ' : 'الموعد: ' ) . $date . ' ' . $time,
		( 'en' === $lang ? 'Payment method: ' : 'طريقة الدفع: ' ) . vava_booking_payment_method_label( $method ),
		( 'en' === $lang ? 'Booking status: ' : 'حالة الحجز: ' ) . vava_booking_status_label( $status ),
	);
	if ( $note ) { $lines[] = ''; $lines[] = ( 'en' === $lang ? 'Administration note: ' : 'ملاحظة الإدارة: ' ) . $note; }
	wp_mail( $email, $copy[0], implode( "\n", $lines ) );
}

function vava_booking_admin_service_location( int $booking_id ): string {
	$uid = (string) get_post_meta( $booking_id, '_vava_booking_service_uid', true );
	$lang = 'en' === get_post_meta( $booking_id, '_vava_booking_language', true ) ? 'en' : 'ar';
	$service = $uid ? vava_booking_resolve_service( $uid, $lang ) : null;
	return is_array( $service ) ? (string) ( $service['location'] ?? '' ) : '';
}

function vava_booking_render_action_buttons( int $booking_id, string $context = 'drawer' ): void {
	$actions = vava_booking_allowed_admin_actions( $booking_id );
	if ( ! $actions ) { echo '<p class="vava-booking-no-actions">لا توجد إجراءات مطلوبة للحالة الحالية.</p>'; return; }
	foreach ( $actions as $decision => $definition ) :
		if ( 'refund_booking' === $decision ) : ?>
			<button type="button" class="vava-booking-action-button is-refund" data-vava-refund-toggle data-booking-id="<?php echo esc_attr( (string) $booking_id ); ?>">إرجاع المبلغ</button>
		<?php else : ?>
			<button type="button" class="vava-booking-action-button <?php echo esc_attr( (string) ( $definition['class'] ?? '' ) ); ?>" data-vava-booking-action data-booking-id="<?php echo esc_attr( (string) $booking_id ); ?>" data-decision="<?php echo esc_attr( $decision ); ?>" data-action-context="<?php echo esc_attr( $context ); ?>"><?php echo esc_html( (string) ( $definition['label'] ?? '' ) ); ?></button>
		<?php endif;
	endforeach;
}

function vava_booking_render_action_history( int $booking_id ): void {
	$entries = array();
	$created = (string) get_post_meta( $booking_id, '_vava_booking_created_at', true );
	if ( ! $created ) { $created = (string) get_post_field( 'post_date', $booking_id ); }
	$entries[] = array( 'action' => 'created', 'label' => 'تم إنشاء الحجز', 'time' => $created, 'status' => (string) get_post_meta( $booking_id, '_vava_booking_status', true ), 'note' => '' );
	foreach ( vava_booking_action_log( $booking_id ) as $entry ) {
		$entry['label'] = vava_booking_action_label( (string) ( $entry['action'] ?? '' ) );
		$entry['status'] = (string) ( $entry['new_status'] ?? '' );
		$entries[] = $entry;
	}
	$entries = array_reverse( $entries );
	?>
	<div class="vava-booking-action-history-list">
	<?php foreach ( $entries as $entry ) : $user = ! empty( $entry['user_id'] ) ? get_userdata( absint( $entry['user_id'] ) ) : null; ?>
		<article><i aria-hidden="true"></i><div><strong><?php echo esc_html( (string) ( $entry['label'] ?? 'تحديث الحجز' ) ); ?></strong><small><?php echo esc_html( vava_booking_format_datetime_12h( (string) ( $entry['time'] ?? '' ) ) ); ?><?php echo $user ? ' — ' . esc_html( (string) $user->display_name ) : ''; ?></small><?php if ( ! empty( $entry['note'] ) ) : ?><p><?php echo esc_html( (string) $entry['note'] ); ?></p><?php endif; ?></div><span><?php echo esc_html( vava_booking_status_label( (string) ( $entry['status'] ?? '' ) ) ); ?></span></article>
	<?php endforeach; ?>
	</div>
	<?php
}

function vava_booking_refund_history( int $booking_id ): array {
	$history = get_post_meta( $booking_id, '_vava_booking_refund_history', true );
	return is_array( $history ) ? array_values( $history ) : array();
}

function vava_booking_render_refund_overview( int $booking_id, string $status, float $remaining ): void {
	$refund_status = vava_booking_refund_status( $booking_id );
	$refunded_total = (float) get_post_meta( $booking_id, '_vava_booking_refunded_total', true );
	$eligible = isset( vava_booking_allowed_admin_actions( $booking_id )['refund_booking'] );
	$paid = vava_booking_paid_or_refunding( $booking_id );
	$history = array_reverse( vava_booking_refund_history( $booking_id ) );
	$is_digital = function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( $booking_id );
	?>
	<section class="vava-booking-detail-card vava-booking-refund-overview <?php echo $eligible ? 'is-eligible' : ''; ?>">
		<header><div><small>الاسترداد</small><h3>حالة إرجاع المبلغ</h3></div><span class="vava-booking-refund-state is-<?php echo esc_attr( sanitize_html_class( $refund_status ?: 'none' ) ); ?>"><?php echo esc_html( vava_booking_refund_status_label( $refund_status ) ); ?></span></header>
		<div class="vava-booking-refund-metrics">
			<div><span>تم رده</span><strong><?php echo esc_html( vava_booking_format_amount( $refunded_total ) ); ?></strong></div>
			<div><span>المتبقي</span><strong><?php echo esc_html( vava_booking_format_amount( $remaining ) ); ?></strong></div>
		</div>
		<?php if ( $eligible ) : ?>
			<p><?php echo esc_html( $is_digital ? 'يمكن تسجيل استرداد مبلغ الطلب كاملًا أو جزئيًا مع توثيق العملية.' : 'الحجز مدفوع وملغي، ويمكن تسجيل رد المبلغ كاملًا أو جزئيًا مع إثبات العملية.' ); ?></p>
			<button type="button" class="vava-booking-refund-card-button" data-vava-refund-toggle data-booking-id="<?php echo esc_attr( (string) $booking_id ); ?>">إرجاع المبلغ</button>
		<?php elseif ( ! $is_digital && $paid && ! in_array( $status, array( 'cancelled', 'customer_cancelled' ), true ) ) : ?>
			<p>يصبح إجراء إرجاع المبلغ متاحًا تلقائيًا بعد إلغاء الحجز المدفوع.</p>
		<?php elseif ( $history ) : ?>
			<p><?php echo esc_html( $is_digital ? 'تم تسجيل عمليات استرداد لهذا الطلب.' : 'تم تسجيل عمليات استرداد لهذا الحجز.' ); ?></p>
		<?php else : ?>
			<p><?php echo esc_html( $is_digital ? 'لا يوجد استرداد مسجل لهذا الطلب.' : 'لا يوجد استرداد مطلوب لهذا الحجز.' ); ?></p>
		<?php endif; ?>
		<?php if ( $history ) : ?><div class="vava-booking-refund-history"><?php foreach ( array_slice( $history, 0, 5 ) as $item ) : $user = ! empty( $item['user_id'] ) ? get_userdata( absint( $item['user_id'] ) ) : null; ?><article><div><strong><?php echo esc_html( vava_booking_format_amount( (float) ( $item['amount'] ?? 0 ) ) ); ?></strong><span><?php echo esc_html( (string) ( $item['method'] ?? '' ) ); ?></span></div><small><?php echo esc_html( vava_booking_format_datetime_12h( (string) ( $item['date'] ?? $item['time'] ?? '' ) ) ); ?><?php echo $user ? ' — ' . esc_html( (string) $user->display_name ) : ''; ?></small><?php if ( ! empty( $item['reference'] ) ) : ?><em>مرجع: <?php echo esc_html( (string) $item['reference'] ); ?></em><?php endif; ?></article><?php endforeach; ?></div><?php endif; ?>
	</section>
	<?php
}


function vava_booking_render_refund_panel( int $booking_id, string $heading_tag = 'h3' ): void {
	$remaining = vava_booking_refund_remaining( $booking_id );
	if ( $remaining <= 0 || ! isset( vava_booking_allowed_admin_actions( $booking_id )['refund_booking'] ) ) {
		return;
	}
	$heading_tag = in_array( $heading_tag, array( 'h2', 'h3' ), true ) ? $heading_tag : 'h3';
	$is_digital  = function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( $booking_id );
	?>
	<section class="vava-booking-refund-panel<?php echo $is_digital ? ' is-product-refund' : ''; ?>" data-vava-refund-panel hidden>
		<header><div><small>استرداد يدوي موثق</small><<?php echo esc_html( $heading_tag ); ?>>إرجاع المبلغ للعميل</<?php echo esc_html( $heading_tag ); ?>></div><button type="button" data-vava-refund-close aria-label="إغلاق">×</button></header>
		<div class="vava-booking-refund-grid">
			<label><span>نوع الاسترداد</span><select data-refund-type><option value="full">استرداد كامل</option><option value="partial">استرداد جزئي</option></select></label>
			<label><span>المبلغ المسترد</span><input type="number" step="0.01" min="0.01" max="<?php echo esc_attr( (string) $remaining ); ?>" value="<?php echo esc_attr( (string) $remaining ); ?>" data-refund-amount/></label>
			<label><span>تاريخ الاسترداد</span><input type="date" value="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" data-refund-date/></label>
			<label><span>طريقة إرجاع المبلغ</span><input type="text" placeholder="تحويل بنكي" data-refund-method/></label>
			<label><span>الرقم المرجعي</span><input type="text" placeholder="رقم مرجع العملية" data-refund-reference/></label>
			<label class="vava-booking-refund-upload"><span>إثبات الاسترداد</span><span class="vava-booking-upload-control"><input type="file" accept=".jpg,.jpeg,.png,.webp,.pdf" data-refund-proof/><b>اختيار ملف</b><em data-refund-proof-name>لم يتم اختيار ملف</em></span></label>
			<label class="is-full"><span>ملاحظة داخلية</span><textarea rows="3" placeholder="أضف تفاصيل داخلية عن عملية الاسترداد..." data-refund-note></textarea></label>
		</div>
		<footer><span>المتبقي للاسترداد: <b><?php echo esc_html( vava_booking_format_amount( $remaining ) ); ?></b></span><button type="button" class="vava-booking-refund-submit" data-vava-booking-refund-submit data-booking-id="<?php echo esc_attr( (string) $booking_id ); ?>">تسجيل إرجاع المبلغ</button></footer>
	</section>
	<?php
}


/** V4R10 — approved full-page booking workspace. */
function vava_booking_render_admin_fullpage_content( int $post_id ): void {
	$customer         = (array) get_post_meta( $post_id, '_vava_booking_customer', true );
	$receipt          = vava_booking_get_receipt( $post_id, true );
	$receipt_mime     = strtolower( (string) ( $receipt['mime'] ?? '' ) );
	$transfer         = (array) get_post_meta( $post_id, '_vava_booking_bank_transfer', true );
	$status           = (string) get_post_meta( $post_id, '_vava_booking_status', true );
	$payment_status   = vava_booking_effective_payment_status( $post_id );
	$method           = (string) get_post_meta( $post_id, '_vava_booking_payment_method', true );
	$receipt_url      = vava_booking_admin_receipt_url( $post_id, false );
	$service_title    = (string) get_post_meta( $post_id, '_vava_booking_service_title', true );
	$service_kind     = (string) get_post_meta( $post_id, '_vava_booking_service_kind', true );
	$location         = vava_booking_admin_service_location( $post_id );
	$amount           = vava_booking_format_price_label( (string) get_post_meta( $post_id, '_vava_booking_service_price', true ), (string) get_post_meta( $post_id, '_vava_booking_service_currency', true ), 'ar' );
	$review_note      = (string) get_post_meta( $post_id, '_vava_booking_review_note', true );
	$refund_status    = vava_booking_refund_status( $post_id );
	$refund_remaining = vava_booking_refund_remaining( $post_id );
	$last_email       = (string) get_post_meta( $post_id, '_vava_booking_last_details_email_sent_at', true );
	$created_at       = (string) get_post_meta( $post_id, '_vava_booking_created_at', true );
	if ( ! $created_at ) { $created_at = (string) get_post_field( 'post_date', $post_id ); }
	$date             = (string) get_post_meta( $post_id, '_vava_booking_date', true );
	$time             = vava_booking_format_time_12h( (string) get_post_meta( $post_id, '_vava_booking_time', true ) );
	$duration         = vava_booking_display_duration_for_booking( $post_id, 'ar' );
	$paid_at          = (string) get_post_meta( $post_id, '_vava_booking_paid_at', true );
	?>
	<div class="wrap vava-booking-details-page" dir="rtl" data-vava-booking-details-page data-booking-id="<?php echo esc_attr( (string) $post_id ); ?>">
		<div class="vava-booking-details-page-toolbar">
			<a class="vava-booking-back-link" href="<?php echo esc_url( vava_booking_admin_list_url( $post_id ) ); ?>"><span aria-hidden="true">→</span> <?php echo esc_html( vava_booking_order_is_product( $post_id ) ? 'العودة إلى منتجات VAVA' : 'العودة إلى الحجوزات' ); ?></a>
			<div><span>إدارة الحجوزات</span><h1>تفاصيل الحجز <b>#<?php echo esc_html( (string) $post_id ); ?></b></h1><p><?php echo esc_html( $service_title ); ?></p></div>
			<div class="vava-booking-details-page-statuses"><span class="vava-booking-admin-status is-<?php echo esc_attr( sanitize_html_class( $status ) ); ?>"><?php echo esc_html( vava_booking_status_label( $status ) ); ?></span><span class="vava-booking-admin-status is-<?php echo esc_attr( sanitize_html_class( $payment_status ) ); ?>"><?php echo esc_html( vava_booking_payment_status_label( $payment_status ) ); ?></span></div>
		</div>

		<div class="vava-booking-details-page-grid" data-vava-booking-detail-content>
			<aside class="vava-booking-details-summary">
				<section class="vava-booking-detail-card is-accent"><h2>ملخص الحجز</h2><?php vava_booking_admin_detail_row( 'رقم الحجز', '#' . $post_id, 'ltr' ); vava_booking_admin_detail_row( 'تاريخ الإنشاء', vava_booking_format_datetime_12h( $created_at ), 'ltr' ); vava_booking_admin_detail_row( 'حالة الحجز', vava_booking_status_label( $status ) ); vava_booking_admin_detail_row( 'طريقة الدفع', vava_booking_payment_method_label( $method ) ); vava_booking_admin_detail_row( 'الإجمالي', $amount ); if ( $refund_status ) { vava_booking_admin_detail_row( 'حالة الاسترداد', vava_booking_refund_status_label( $refund_status ) ); } ?></section>
				<?php if ( 'bank' === $method ) : ?>
				<section class="vava-booking-detail-card vava-booking-admin-receipt-card"><h2>إيصال التحويل</h2><?php if ( $receipt_url ) : ?><a class="vava-booking-receipt-full" href="<?php echo esc_url( $receipt_url ); ?>" target="_blank" rel="noopener"><?php if ( 0 === strpos( $receipt_mime, 'image/' ) ) : ?><img src="<?php echo esc_url( $receipt_url ); ?>" alt="إيصال التحويل"><?php else : ?><span class="vava-booking-refund-document">PDF</span><?php endif; ?><strong>عرض الإيصال بالحجم الكامل</strong></a><?php else : ?><p class="vava-booking-empty-copy">لا يوجد إيصال متاح.</p><?php endif; ?></section>
				<?php endif; ?>
			</aside>

			<main class="vava-booking-details-main">
				<div class="vava-booking-details-primary">
					<section class="vava-booking-detail-card"><header><span class="dashicons dashicons-money-alt" aria-hidden="true"></span><h2>الدفع وإثبات التحويل</h2></header><?php vava_booking_admin_detail_row( 'طريقة الدفع', vava_booking_payment_method_label( $method ) ); vava_booking_admin_detail_row( 'حالة الدفع', vava_booking_payment_status_label( $payment_status ) ); vava_booking_admin_detail_row( 'الإجمالي', $amount ); vava_booking_admin_detail_row( 'تاريخ الدفع', vava_booking_format_datetime_12h( $paid_at ), 'ltr' ); if ( 'bank' === $method ) { vava_booking_admin_detail_row( 'اسم المحوِّل', (string) ( $transfer['transfer_name'] ?? '' ) ); vava_booking_admin_detail_row( 'البنك المحوّل منه', (string) ( $transfer['from_bank'] ?? '' ) ); vava_booking_admin_detail_row( 'مرجع العملية', (string) ( $transfer['reference'] ?? '' ), 'ltr' ); } ?></section>
					<section class="vava-booking-detail-card"><header><span class="dashicons dashicons-admin-users" aria-hidden="true"></span><h2>معلومات العميل</h2></header><?php vava_booking_admin_detail_row( 'الاسم', (string) ( $customer['name'] ?? '' ) ); vava_booking_admin_detail_row( 'رقم الجوال', (string) ( $customer['whatsapp'] ?? '' ), 'ltr' ); vava_booking_admin_detail_row( 'البريد الإلكتروني', (string) ( $customer['email'] ?? '' ), 'ltr' ); vava_booking_admin_detail_row( 'سبق تجربة VAVA', (string) ( $customer['previous'] ?? '' ) ); vava_booking_admin_detail_row( 'ملاحظات العميل', (string) ( $customer['notes'] ?? '' ) ); ?></section>
					<section class="vava-booking-detail-card"><header><span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span><h2>الخدمة والموعد</h2></header><?php vava_booking_admin_detail_row( 'الخدمة', $service_title ); vava_booking_admin_detail_row( 'نوع الخدمة', $service_kind ); vava_booking_admin_detail_row( 'التاريخ', $date, 'ltr' ); vava_booking_admin_detail_row( 'الوقت', $time, 'ltr' ); vava_booking_admin_detail_row( 'المدة', $duration ); if ( $location ) { vava_booking_admin_detail_row( 'المكان', $location ); } ?></section>
				</div>
				<?php if ( function_exists( 'vava_booking_questionnaire_render_admin_details' ) ) { vava_booking_questionnaire_render_admin_details( $post_id ); } ?>

				<?php vava_booking_render_refund_overview( $post_id, $status, $refund_remaining ); ?>

				<div class="vava-booking-details-secondary">
					<section class="vava-booking-detail-card vava-booking-action-history"><header><span class="dashicons dashicons-backup" aria-hidden="true"></span><h2>سجل النشاط</h2></header><?php vava_booking_render_action_history( $post_id ); ?><?php if ( $last_email ) : ?><p class="vava-booking-last-email">آخر إعادة إرسال: <?php echo esc_html( vava_booking_format_datetime_12h( $last_email ) ); ?></p><?php endif; ?></section>
					<section class="vava-booking-detail-card vava-booking-admin-note"><header><span class="dashicons dashicons-edit-page" aria-hidden="true"></span><h2>ملاحظات الإدارة</h2></header><p>ملاحظة داخلية لا تظهر للعميل.</p><textarea name="action_note" rows="5" placeholder="اكتب ملاحظة إدارية..."><?php echo esc_textarea( $review_note ); ?></textarea><button type="button" class="vava-booking-save-note" data-booking-id="<?php echo esc_attr( (string) $post_id ); ?>">حفظ الملاحظة</button></section>
				</div>
				<?php vava_booking_render_refund_panel( $post_id, 'h2' ); ?>
			</main>
		</div>

		<footer class="vava-booking-details-actions"><div><?php vava_booking_render_action_buttons( $post_id, 'page' ); ?></div><a href="<?php echo esc_url( vava_booking_admin_list_url( $post_id ) ); ?>"><?php echo esc_html( vava_booking_order_is_product( $post_id ) ? 'العودة إلى منتجات VAVA' : 'العودة إلى قائمة الحجوزات' ); ?></a></footer>
	</div>
	<?php
}

function vava_booking_render_admin_details_content( int $post_id, bool $overlay = false ): void {
	if ( function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( $post_id ) && function_exists( 'vava_digital_products_render_admin_order_details' ) ) {
		vava_digital_products_render_admin_order_details( $post_id, $overlay );
		return;
	}
	$customer = (array) get_post_meta( $post_id, '_vava_booking_customer', true );
	$receipt = vava_booking_get_receipt( $post_id, true );
	$receipt_mime = strtolower( (string) ( $receipt['mime'] ?? '' ) );
	$transfer = (array) get_post_meta( $post_id, '_vava_booking_bank_transfer', true );
	$status = (string) get_post_meta( $post_id, '_vava_booking_status', true );
	$payment_status = vava_booking_effective_payment_status( $post_id );
	$method = (string) get_post_meta( $post_id, '_vava_booking_payment_method', true );
	$receipt_url = vava_booking_admin_receipt_url( $post_id, false );
	$service_title = (string) get_post_meta( $post_id, '_vava_booking_service_title', true );
	$service_kind = (string) get_post_meta( $post_id, '_vava_booking_service_kind', true );
	$location = vava_booking_admin_service_location( $post_id );
	$amount = vava_booking_format_price_label( (string) get_post_meta( $post_id, '_vava_booking_service_price', true ), (string) get_post_meta( $post_id, '_vava_booking_service_currency', true ), 'ar' );
	$review_note = (string) get_post_meta( $post_id, '_vava_booking_review_note', true );
	$refund_status = vava_booking_refund_status( $post_id );
	$refund_remaining = vava_booking_refund_remaining( $post_id );
	$last_email = (string) get_post_meta( $post_id, '_vava_booking_last_details_email_sent_at', true );
	$created_at = (string) get_post_meta( $post_id, '_vava_booking_created_at', true );
	$date = (string) get_post_meta( $post_id, '_vava_booking_date', true );
	$time = vava_booking_format_time_12h( (string) get_post_meta( $post_id, '_vava_booking_time', true ) );
	$duration = vava_booking_display_duration_for_booking( $post_id, 'ar' );
	?>
	<article class="vava-booking-reader-article vava-booking-reader-article--workspace" dir="rtl" data-booking-id="<?php echo esc_attr( (string) $post_id ); ?>">
		<header class="vava-booking-reader-header vava-booking-workspace-header">
			<div class="vava-booking-reader-title"><small>تفاصيل الحجز</small><h2>تفاصيل الحجز <b>#<?php echo esc_html( (string) $post_id ); ?></b></h2><p><?php echo esc_html( $service_title ); ?></p></div>
			<div class="vava-booking-admin-detail-statuses"><span class="vava-booking-admin-status is-<?php echo esc_attr( sanitize_html_class( $payment_status ) ); ?>"><?php echo esc_html( vava_booking_payment_status_label( $payment_status ) ); ?></span><span class="vava-booking-admin-status is-<?php echo esc_attr( sanitize_html_class( $status ) ); ?>"><?php echo esc_html( vava_booking_status_label( $status ) ); ?></span></div>
			<?php if ( $overlay ) : ?><button class="vava-booking-reader-close-top" type="button" data-vava-booking-close aria-label="إغلاق">×</button><?php endif; ?>
		</header>
		<div class="vava-booking-admin-workspace">
			<aside class="vava-booking-admin-summary-rail">
				<section class="vava-booking-detail-card is-accent"><h3>ملخص الحجز</h3><?php vava_booking_admin_detail_row( 'رقم الحجز', '#' . $post_id, 'ltr' ); vava_booking_admin_detail_row( 'تاريخ الحجز', $created_at ? wp_date( 'Y-m-d', strtotime( $created_at ) ) : '', 'ltr' ); vava_booking_admin_detail_row( 'وقت الحجز', $created_at ? vava_booking_format_time_12h( wp_date( 'H:i', strtotime( $created_at ) ) ) : '', 'ltr' ); vava_booking_admin_detail_row( 'حالة الحجز', vava_booking_status_label( $status ) ); vava_booking_admin_detail_row( 'طريقة الدفع', vava_booking_payment_method_label( $method ) ); vava_booking_admin_detail_row( 'الإجمالي', $amount ); if ( $refund_status ) { vava_booking_admin_detail_row( 'حالة الاسترداد', vava_booking_refund_status_label( $refund_status ) ); } ?></section>
				<?php if ( 'bank' === $method ) : ?><section class="vava-booking-detail-card vava-booking-admin-receipt-card"><h3>إيصال التحويل</h3><?php if ( $receipt_url ) : ?><a href="<?php echo esc_url( $receipt_url ); ?>" target="_blank" rel="noopener"><?php if ( 0 === strpos( $receipt_mime, 'image/' ) ) : ?><img src="<?php echo esc_url( $receipt_url ); ?>" alt="إيصال التحويل"><?php else : ?><span class="vava-booking-refund-document">PDF</span><?php endif; ?><span>عرض الإيصال بالحجم الكامل</span></a><?php else : ?><p>لا يوجد إيصال متاح.</p><?php endif; ?></section><?php endif; ?>
			</aside>
			<main class="vava-booking-admin-workspace-main">
				<div class="vava-booking-admin-primary-grid">
					<section class="vava-booking-detail-card"><h3>الدفع وإثبات التحويل</h3><?php vava_booking_admin_detail_row( 'طريقة الدفع', vava_booking_payment_method_label( $method ) ); vava_booking_admin_detail_row( 'حالة الدفع', vava_booking_payment_status_label( $payment_status ) ); vava_booking_admin_detail_row( 'الإجمالي', $amount ); vava_booking_admin_detail_row( 'تاريخ الدفع', vava_booking_format_datetime_12h( (string) get_post_meta( $post_id, '_vava_booking_paid_at', true ) ), 'ltr' ); if ( 'bank' === $method ) { vava_booking_admin_detail_row( 'اسم المحوِّل', (string) ( $transfer['transfer_name'] ?? '' ) ); vava_booking_admin_detail_row( 'البنك المحوّل منه', (string) ( $transfer['from_bank'] ?? '' ) ); vava_booking_admin_detail_row( 'مرجع العملية', (string) ( $transfer['reference'] ?? '' ), 'ltr' ); } ?></section>
					<section class="vava-booking-detail-card"><h3>معلومات العميل</h3><?php vava_booking_admin_detail_row( 'الاسم', (string) ( $customer['name'] ?? '' ) ); vava_booking_admin_detail_row( 'رقم الجوال', (string) ( $customer['whatsapp'] ?? '' ), 'ltr' ); vava_booking_admin_detail_row( 'البريد الإلكتروني', (string) ( $customer['email'] ?? '' ), 'ltr' ); vava_booking_admin_detail_row( 'سبق تجربة VAVA', (string) ( $customer['previous'] ?? '' ) ); vava_booking_admin_detail_row( 'ملاحظات العميل', (string) ( $customer['notes'] ?? '' ) ); ?></section>
					<section class="vava-booking-detail-card"><h3>الخدمة والموعد</h3><?php vava_booking_admin_detail_row( 'الخدمة', $service_title ); vava_booking_admin_detail_row( 'نوع الخدمة', $service_kind ); vava_booking_admin_detail_row( 'التاريخ', $date, 'ltr' ); vava_booking_admin_detail_row( 'الوقت', $time, 'ltr' ); vava_booking_admin_detail_row( 'المدة', $duration ); if ( $location ) { vava_booking_admin_detail_row( 'المكان', $location ); } ?></section>
				</div>
				<?php if ( function_exists( 'vava_booking_questionnaire_render_admin_details' ) ) { vava_booking_questionnaire_render_admin_details( $post_id ); } ?>
				<?php vava_booking_render_refund_overview( $post_id, $status, $refund_remaining ); ?>
				<div class="vava-booking-admin-secondary-grid">
					<section class="vava-booking-detail-card vava-booking-admin-note"><h3>ملاحظات الإدارة</h3><p>ملاحظة داخلية لا تظهر للعميل.</p><textarea name="action_note" rows="4" placeholder="اكتب ملاحظة إدارية..."><?php echo esc_textarea( $review_note ); ?></textarea><button type="button" class="button vava-booking-save-note" data-booking-id="<?php echo esc_attr( (string) $post_id ); ?>">حفظ الملاحظة</button></section>
					<section class="vava-booking-detail-card vava-booking-action-history"><h3>سجل النشاط</h3><?php vava_booking_render_action_history( $post_id ); ?><?php if ( $last_email ) : ?><p class="vava-booking-last-email">آخر إعادة إرسال: <?php echo esc_html( vava_booking_format_datetime_12h( $last_email ) ); ?></p><?php endif; ?></section>
				</div>
				<?php vava_booking_render_refund_panel( $post_id, 'h3' ); ?>
			</main>
		</div>
		<footer class="vava-booking-reader-actions vava-booking-workspace-actions"><div><?php vava_booking_render_action_buttons( $post_id ); ?></div><?php if ( $overlay ) : ?><button type="button" class="vava-booking-reader-close-secondary" data-vava-booking-close>إغلاق التفاصيل</button><?php endif; ?></footer>
	</article><?php
}

function vava_booking_render_admin_details( WP_Post $post ): void {
	vava_booking_render_admin_details_content( (int) $post->ID, false );
}

function vava_booking_ajax_admin_details(): void {
	check_ajax_referer( 'vava_booking_admin_overlay', 'nonce' );
	if ( ! current_user_can( vava_booking_admin_capability() ) ) { wp_send_json_error( array( 'message' => 'غير مسموح.' ), 403 ); }
	$booking_id = isset( $_POST['booking'] ) ? absint( $_POST['booking'] ) : 0;
	if ( ! $booking_id || 'vava_booking' !== get_post_type( $booking_id ) ) { wp_send_json_error( array( 'message' => 'الحجز غير موجود.' ), 404 ); }
	ob_start();
	vava_booking_render_admin_details_content( $booking_id, true );
	wp_send_json_success( array( 'html' => ob_get_clean(), 'bookingId' => $booking_id ) );
}
add_action( 'wp_ajax_vava_booking_admin_details', 'vava_booking_ajax_admin_details' );

function vava_booking_process_admin_action( int $booking_id, string $decision, string $note = '' ): string {
	$allowed = vava_booking_allowed_admin_actions( $booking_id );
	if ( ! isset( $allowed[ $decision ] ) || 'refund_booking' === $decision ) { return 'not_allowed'; }
	$is_digital = function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( $booking_id );
	$old_status = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
	$old_payment = vava_booking_effective_payment_status( $booking_id );
	if ( 'resend_details' === $decision ) {
		if ( $is_digital && function_exists( 'vava_digital_products_send_order_status_update' ) ) {
			vava_digital_products_send_order_status_update( $booking_id, 'resend_details' );
		} else {
			$sent = vava_booking_send_details_email( $booking_id, true, false );
			if ( is_wp_error( $sent ) ) { return 'email_failed'; }
		}
		vava_booking_append_action_log( $booking_id, 'resend_details', '', $old_status, $old_status, $old_payment, $old_payment );
		return 'resend_details';
	}
	$new_status = $old_status;
	$new_payment = $old_payment;
	$event = '';
	if ( 'approve_cash' === $decision ) {
		$new_status = 'confirmed'; $new_payment = 'unpaid'; $event = 'approved';
	} elseif ( 'approve_bank' === $decision ) {
		$new_status = 'confirmed'; $new_payment = 'paid'; $event = 'bank_approved';
		update_post_meta( $booking_id, '_vava_booking_paid_at', current_time( 'mysql' ) );
		if ( $is_digital ) {
			update_post_meta( $booking_id, '_vava_digital_access_status', 'active' );
			update_post_meta( $booking_id, '_vava_digital_access_activated_at', current_time( 'mysql' ) );
			update_post_meta( $booking_id, '_vava_digital_access_activated_by', get_current_user_id() );
		}
	} elseif ( 'reject_bank' === $decision ) {
		$new_status = 'bank_rejected'; $new_payment = 'rejected'; $event = 'bank_rejected';
		if ( $is_digital ) { update_post_meta( $booking_id, '_vava_digital_access_status', 'rejected' ); }
	} elseif ( 'revoke_access' === $decision && $is_digital ) {
		update_post_meta( $booking_id, '_vava_digital_access_status', 'revoked' );
		update_post_meta( $booking_id, '_vava_digital_access_revoked_at', current_time( 'mysql' ) );
		$event = 'access_revoked';
	} elseif ( 'restore_access' === $decision && $is_digital ) {
		update_post_meta( $booking_id, '_vava_digital_access_status', 'active' );
		update_post_meta( $booking_id, '_vava_digital_access_activated_at', current_time( 'mysql' ) );
		$event = 'access_restored';
	} elseif ( 'cancel_booking' === $decision || 'approve_customer_cancel' === $decision ) {
		$new_status = 'cancelled'; $event = 'cancelled';
		if ( $is_digital ) {
			$new_payment = 'cancelled';
			update_post_meta( $booking_id, '_vava_digital_access_status', 'revoked' );
		} elseif ( vava_booking_paid_or_refunding( $booking_id ) ) {
			$new_payment = 'refund_pending';
			update_post_meta( $booking_id, '_vava_booking_refund_status', 'pending' );
			update_post_meta( $booking_id, '_vava_booking_refund_requested_at', current_time( 'mysql' ) );
		} else { $new_payment = 'cancelled'; }
		if ( 'approve_customer_cancel' === $decision ) {
			update_post_meta( $booking_id, '_vava_booking_cancellation_request_status', 'approved' );
			update_post_meta( $booking_id, '_vava_booking_cancellation_request_resolved_at', current_time( 'mysql' ) );
		}
	} elseif ( 'reject_customer_cancel' === $decision ) {
		$previous = (string) get_post_meta( $booking_id, '_vava_booking_pre_cancel_request_status', true );
		$new_status = in_array( $previous, array( 'confirmed', 'paid', 'pending', 'pending_payment', 'pending_bank_review' ), true ) ? $previous : ( 'paid' === $old_payment ? 'confirmed' : 'pending' );
		$event = 'cancellation_request_rejected';
		update_post_meta( $booking_id, '_vava_booking_cancellation_request_status', 'rejected' );
		update_post_meta( $booking_id, '_vava_booking_cancellation_request_resolved_at', current_time( 'mysql' ) );
	} elseif ( 'mark_completed' === $decision ) {
		$new_status = 'completed';
		$event = 'completed';
		update_post_meta( $booking_id, '_vava_booking_completed_at', current_time( 'mysql' ) );
		update_post_meta( $booking_id, '_vava_booking_completed_by', get_current_user_id() );
	} elseif ( 'restore_confirmed' === $decision ) {
		$new_status = 'confirmed';
		$event = 'restored_confirmed';
		delete_post_meta( $booking_id, '_vava_booking_completed_at' );
		delete_post_meta( $booking_id, '_vava_booking_completed_by' );
	}
	update_post_meta( $booking_id, '_vava_booking_status', $new_status );
	update_post_meta( $booking_id, '_vava_booking_payment_status', $new_payment );
	update_post_meta( $booking_id, '_vava_booking_review_note', $note );
	update_post_meta( $booking_id, '_vava_booking_reviewed_by', get_current_user_id() );
	update_post_meta( $booking_id, '_vava_booking_reviewed_at', current_time( 'mysql' ) );
	vava_booking_append_action_log( $booking_id, $decision, $note, $old_status, $new_status, $old_payment, $new_payment );
	if ( $is_digital && function_exists( 'vava_digital_products_send_order_status_update' ) ) {
		vava_digital_products_send_order_status_update( $booking_id, $event, $note );
	} else {
		vava_booking_send_status_update( $booking_id, $event, $note );
	}
	return $decision;
}

function vava_booking_ajax_admin_action(): void {
	check_ajax_referer( 'vava_booking_admin_overlay', 'nonce' );
	if ( ! current_user_can( vava_booking_admin_capability() ) ) { wp_send_json_error( array( 'message' => 'غير مسموح.' ), 403 ); }
	$booking_id = isset( $_POST['booking'] ) ? absint( $_POST['booking'] ) : 0;
	$decision = isset( $_POST['decision'] ) ? sanitize_key( wp_unslash( $_POST['decision'] ) ) : '';
	$note = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';
	if ( ! $booking_id || 'vava_booking' !== get_post_type( $booking_id ) ) { wp_send_json_error( array( 'message' => 'الحجز غير موجود.' ), 404 ); }
	if ( 'save_note' === $decision ) {
		$old_status = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
		$old_payment = vava_booking_effective_payment_status( $booking_id );
		update_post_meta( $booking_id, '_vava_booking_review_note', $note );
		vava_booking_append_action_log( $booking_id, 'note_saved', $note, $old_status, $old_status, $old_payment, $old_payment );
		$result = 'note_saved';
	} else {
		$result = vava_booking_process_admin_action( $booking_id, $decision, $note );
		if ( 'not_allowed' === $result ) { wp_send_json_error( array( 'message' => 'هذا الإجراء غير متاح للحالة الحالية.' ), 409 ); }
		if ( 'email_failed' === $result ) { wp_send_json_error( array( 'message' => 'تعذر إرسال البريد. راجع إعدادات SMTP.' ), 502 ); }
	}
	ob_start(); vava_booking_render_admin_details_content( $booking_id, true ); $details_html = ob_get_clean();
	ob_start(); vava_booking_render_state_cell( $booking_id ); $state_html = ob_get_clean();
	ob_start(); vava_booking_render_row_actions( $booking_id ); $actions_html = ob_get_clean();
	$messages = array(
		'approve_cash' => 'تم اعتماد الحجز وتأكيده.',
		'approve_bank' => ( function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( $booking_id ) ) ? 'تم اعتماد التحويل وتفعيل المنتج داخل منتجاتي الرقمية.' : 'تم اعتماد التحويل البنكي وتأكيد الحجز.',
		'reject_bank' => ( function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( $booking_id ) ) ? 'تم رفض التحويل ولم يتم تفعيل المنتج.' : 'تم رفض التحويل البنكي وتحرير الموعد.',
		'cancel_booking' => 'تم إلغاء الحجز وتحرير الموعد.',
		'approve_customer_cancel' => 'تم اعتماد طلب الإلغاء وتحرير الموعد.',
		'reject_customer_cancel' => 'تم رفض طلب الإلغاء والإبقاء على الحجز.',
		'mark_completed' => 'تم تحديد الحجز كمكتمل وإتاحة استبيان أثر الرحلة للعميل.',
		'restore_confirmed' => 'تمت إعادة الحجز إلى حالة مؤكد.',
		'note_saved' => 'تم حفظ الملاحظة الإدارية.',
		'resend_details' => ( function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( $booking_id ) ) ? 'تمت إعادة إرسال رابط منتجاتي الرقمية للعميل.' : 'تمت إعادة إرسال تفاصيل الحجز للعميل.',
			'revoke_access' => 'تم إيقاف صلاحية مشاهدة المنتج.',
			'restore_access' => 'تمت إعادة تفعيل صلاحية مشاهدة المنتج.',
		'email_failed' => 'تعذر إرسال البريد. راجع إعدادات SMTP.',
	);
	wp_send_json_success( array(
		'bookingId' => $booking_id,
		'result' => $result,
		'message' => $messages[ $result ] ?? 'تم تحديث الحجز.',
		'html' => $details_html,
		'stateHtml' => $state_html,
		'actionsHtml' => $actions_html,
		'counts' => vava_booking_admin_counts( vava_booking_order_is_product( $booking_id ) ? 'products' : 'bookings' ),
		'status' => (string) get_post_meta( $booking_id, '_vava_booking_status', true ),
		'group' => vava_booking_admin_status_group( (string) get_post_meta( $booking_id, '_vava_booking_status', true ) ),
	) );
}
add_action( 'wp_ajax_vava_booking_admin_action', 'vava_booking_ajax_admin_action' );


function vava_booking_ajax_admin_refund(): void {
	check_ajax_referer( 'vava_booking_admin_overlay', 'nonce' );
	if ( ! current_user_can( vava_booking_admin_capability() ) ) { wp_send_json_error( array( 'message' => 'غير مسموح.' ), 403 ); }
	$booking_id = isset( $_POST['booking'] ) ? absint( $_POST['booking'] ) : 0;
	if ( ! $booking_id || 'vava_booking' !== get_post_type( $booking_id ) ) { wp_send_json_error( array( 'message' => 'الحجز غير موجود.' ), 404 ); }
	if ( ! isset( vava_booking_allowed_admin_actions( $booking_id )['refund_booking'] ) ) { wp_send_json_error( array( 'message' => 'الاسترداد غير متاح للحالة الحالية.' ), 409 ); }
	$remaining = vava_booking_refund_remaining( $booking_id );
	$amount = isset( $_POST['amount'] ) ? round( (float) wp_unslash( $_POST['amount'] ), 2 ) : 0;
	$type = isset( $_POST['refund_type'] ) ? sanitize_key( wp_unslash( $_POST['refund_type'] ) ) : 'full';
	if ( 'full' === $type ) { $amount = $remaining; }
	if ( $amount <= 0 || $amount > $remaining + 0.001 ) { wp_send_json_error( array( 'message' => 'قيمة الاسترداد غير صحيحة.' ), 422 ); }
	$entry = array(
		'amount' => $amount,
		'type' => 'full' === $type ? 'full' : 'partial',
		'date' => isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : wp_date( 'Y-m-d' ),
		'method' => isset( $_POST['method'] ) ? sanitize_text_field( wp_unslash( $_POST['method'] ) ) : '',
		'reference' => isset( $_POST['reference'] ) ? sanitize_text_field( wp_unslash( $_POST['reference'] ) ) : '',
		'note' => isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '',
		'user_id' => get_current_user_id(), 'time' => current_time( 'mysql' ),
	);
	if ( isset( $_FILES['proof'] ) && ! empty( $_FILES['proof']['tmp_name'] ) ) {
		$proof = vava_booking_store_bank_receipt( $_FILES['proof'], $booking_id );
		if ( is_wp_error( $proof ) ) { wp_send_json_error( array( 'message' => $proof->get_error_message() ), 422 ); }
		$entry['proof'] = $proof;
	}
	$history = (array) get_post_meta( $booking_id, '_vava_booking_refund_history', true );
	$history[] = $entry;
	update_post_meta( $booking_id, '_vava_booking_refund_history', array_slice( $history, -50 ) );
	$total = round( (float) get_post_meta( $booking_id, '_vava_booking_refunded_total', true ) + $amount, 2 );
	update_post_meta( $booking_id, '_vava_booking_refunded_total', $total );
	$completed  = vava_booking_refund_remaining( $booking_id ) <= 0.001;
	$is_digital = function_exists( 'vava_digital_products_is_order' ) && vava_digital_products_is_order( $booking_id );
	$old_status = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
	$new_status = $old_status;
	if ( $completed && $is_digital ) {
		$new_status = 'cancelled';
		update_post_meta( $booking_id, '_vava_digital_access_status', 'revoked' );
		update_post_meta( $booking_id, '_vava_digital_access_revoked_at', current_time( 'mysql' ) );
		update_post_meta( $booking_id, '_vava_digital_access_revoked_by', get_current_user_id() );
		update_post_meta( $booking_id, '_vava_booking_status', $new_status );
	}
	update_post_meta( $booking_id, '_vava_booking_refund_status', $completed ? 'completed' : 'partial' );
	update_post_meta( $booking_id, '_vava_booking_payment_status', $completed ? 'refunded' : 'partially_refunded' );
	update_post_meta( $booking_id, '_vava_booking_refunded_at', current_time( 'mysql' ) );
	update_post_meta( $booking_id, '_vava_booking_refunded_by', get_current_user_id() );
	vava_booking_append_action_log( $booking_id, 'refund_recorded', $entry['note'], $old_status, $new_status, 'refund_pending', $completed ? 'refunded' : 'partially_refunded' );
	vava_booking_send_refund_update( $booking_id, $amount, $completed );
	ob_start(); vava_booking_render_admin_details_content( $booking_id, true ); $details_html = ob_get_clean();
	ob_start(); vava_booking_render_state_cell( $booking_id ); $state_html = ob_get_clean();
	ob_start(); vava_booking_render_row_actions( $booking_id ); $actions_html = ob_get_clean();
	wp_send_json_success( array( 'message' => $completed ? 'تم تسجيل رد المبلغ بالكامل.' : 'تم تسجيل الاسترداد الجزئي.', 'html' => $details_html, 'stateHtml' => $state_html, 'actionsHtml' => $actions_html, 'bookingId' => $booking_id, 'counts' => vava_booking_admin_counts( vava_booking_order_is_product( $booking_id ) ? 'products' : 'bookings' ) ) );
}
add_action( 'wp_ajax_vava_booking_admin_refund', 'vava_booking_ajax_admin_refund' );

/** Fallback endpoint for older links; current UI uses AJAX to avoid nested-form nonce conflicts. */
function vava_booking_admin_action(): void {
	$booking_id = isset( $_POST['booking'] ) ? absint( $_POST['booking'] ) : 0;
	if ( ! $booking_id || 'vava_booking' !== get_post_type( $booking_id ) || ! current_user_can( vava_booking_admin_capability() ) ) { wp_die( esc_html__( 'غير مسموح.', 'vava-living' ), '', array( 'response' => 403 ) ); }
	$nonce = isset( $_POST['vava_booking_action_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['vava_booking_action_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'vava_booking_action_' . $booking_id ) ) { wp_die( esc_html__( 'انتهت صلاحية الطلب. أعد تحميل الصفحة وحاول مرة أخرى.', 'vava-living' ), '', array( 'response' => 403 ) ); }
	$decision = isset( $_POST['decision'] ) ? sanitize_key( wp_unslash( $_POST['decision'] ) ) : '';
	$note = isset( $_POST['action_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['action_note'] ) ) : '';
	$result = vava_booking_process_admin_action( $booking_id, $decision, $note );
	wp_safe_redirect( add_query_arg( 'vava_booking_action_result', $result, vava_booking_admin_details_url( $booking_id ) ) );
	exit;
}
add_action( 'admin_post_vava_booking_admin_action', 'vava_booking_admin_action' );

/** Keep compatibility with the earlier bank-review form while routing it through the unified workflow. */
function vava_booking_review_transfer(): void {
	$booking_id = isset( $_POST['booking'] ) ? absint( $_POST['booking'] ) : 0;
	if ( ! $booking_id || 'vava_booking' !== get_post_type( $booking_id ) || ! current_user_can( vava_booking_admin_capability() ) ) { wp_die( esc_html__( 'غير مسموح.', 'vava-living' ), '', array( 'response' => 403 ) ); }
	check_admin_referer( 'vava_booking_review_' . $booking_id );
	$legacy = isset( $_POST['decision'] ) ? sanitize_key( wp_unslash( $_POST['decision'] ) ) : '';
	$decision = 'approve' === $legacy ? 'approve_bank' : ( 'reject' === $legacy ? 'reject_bank' : '' );
	$note = isset( $_POST['review_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['review_note'] ) ) : '';
	$result = vava_booking_process_admin_action( $booking_id, $decision, $note );
	wp_safe_redirect( add_query_arg( 'vava_booking_action_result', $result, vava_booking_admin_details_url( $booking_id ) ) );
	exit;
}
add_action( 'admin_post_vava_booking_review_transfer', 'vava_booking_review_transfer' );

function vava_booking_view_receipt(): void {
	$booking_id = isset( $_GET['booking'] ) ? absint( $_GET['booking'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $booking_id || 'vava_booking' !== get_post_type( $booking_id ) || ! current_user_can( vava_booking_admin_capability() ) ) { wp_die( 'غير مسموح.', '', array( 'response' => 403 ) ); }
	check_admin_referer( 'vava_booking_receipt_' . $booking_id );
	$receipt = (array) get_post_meta( $booking_id, '_vava_booking_bank_receipt', true );
	$filename = basename( (string) ( $receipt['file'] ?? '' ) );
	$path = trailingslashit( vava_booking_private_receipt_dir() ) . $filename;
	if ( ! $filename || ! is_file( $path ) ) { wp_die( 'الإيصال غير موجود.', '', array( 'response' => 404 ) ); }
	$mime = (string) ( $receipt['mime'] ?? 'application/octet-stream' );
	$original = sanitize_file_name( (string) ( $receipt['original'] ?? $filename ) );
	nocache_headers();
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Content-Type: ' . $mime );
	header( 'Content-Length: ' . (string) filesize( $path ) );
	header( 'Content-Disposition: inline; filename="' . rawurlencode( $original ?: $filename ) . '"' );
	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
	exit;
}
add_action( 'admin_post_vava_booking_view_receipt', 'vava_booking_view_receipt' );

function vava_booking_download_receipt(): void {
	$booking_id = isset( $_GET['booking'] ) ? absint( $_GET['booking'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $booking_id || 'vava_booking' !== get_post_type( $booking_id ) || ! current_user_can( vava_booking_admin_capability() ) ) { wp_die( 'غير مسموح.', '', array( 'response' => 403 ) ); }
	check_admin_referer( 'vava_booking_receipt_' . $booking_id );
	$receipt = (array) get_post_meta( $booking_id, '_vava_booking_bank_receipt', true );
	$filename = basename( (string) ( $receipt['file'] ?? '' ) );
	$path = trailingslashit( vava_booking_private_receipt_dir() ) . $filename;
	if ( ! $filename || ! is_file( $path ) ) { wp_die( 'الإيصال غير موجود.', '', array( 'response' => 404 ) ); }
	nocache_headers();
	header( 'Content-Type: ' . (string) ( $receipt['mime'] ?? 'application/octet-stream' ) );
	header( 'Content-Length: ' . (string) filesize( $path ) );
	header( 'Content-Disposition: attachment; filename="' . rawurlencode( (string) ( $receipt['original'] ?? $filename ) ) . '"' );
	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
	exit;
}
add_action( 'admin_post_vava_booking_download_receipt', 'vava_booking_download_receipt' );

function vava_booking_redirect_legacy_edit_screen(): void {
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $post_id && 'vava_booking' === get_post_type( $post_id ) ) {
		wp_safe_redirect( vava_booking_admin_list_url( $post_id ) );
		exit;
	}
}
add_action( 'load-post.php', 'vava_booking_redirect_legacy_edit_screen', 1 );

function vava_booking_admin_review_notice(): void {
	if ( empty( $_GET['vava_booking_action_result'] ) ) { return; } // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$result = sanitize_key( wp_unslash( $_GET['vava_booking_action_result'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$messages = array(
		'approve_cash' => 'تم اعتماد الحجز وتأكيده.',
		'approve_bank' => 'تم اعتماد التحويل البنكي وتنفيذ الإجراء المرتبط بالطلب.',
		'reject_bank' => 'تم رفض التحويل البنكي.',
		'cancel_booking' => 'تم إلغاء الحجز وتحرير الموعد.',
		'revoke_access' => 'تم إيقاف صلاحية مشاهدة المنتج.',
			'restore_access' => 'تمت إعادة تفعيل صلاحية مشاهدة المنتج.',
			'not_allowed' => 'هذا الإجراء غير متاح للحالة الحالية.',
	);
	$message = $messages[ $result ] ?? 'لم يتم تنفيذ الإجراء.';
	$is_error = in_array( $result, array( 'not_allowed', 'invalid' ), true );
	echo '<div class="notice notice-' . ( $is_error ? 'error' : 'success' ) . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
}
add_action( 'admin_notices', 'vava_booking_admin_review_notice' );




/** Customer-facing booking history portal. */
function vava_booking_my_bookings_template_slug(): string {
	return 'page-templates/my-bookings-vava.php';
}

function vava_booking_my_bookings_page_id(): int {
	$stored = absint( get_option( '_vava_booking_my_bookings_page_ready' ) );
	if ( $stored && 'page' === get_post_type( $stored ) && vava_booking_my_bookings_template_slug() === get_page_template_slug( $stored ) ) { return $stored; }
	$pages = get_posts( array( 'post_type' => 'page', 'post_status' => array( 'publish', 'draft', 'private' ), 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true, 'meta_key' => '_wp_page_template', 'meta_value' => vava_booking_my_bookings_template_slug() ) );
	return ! empty( $pages[0] ) ? absint( $pages[0] ) : 0;
}

function vava_booking_my_bookings_url( string $lang = 'ar' ): string {
	$page_id = vava_booking_my_bookings_page_id();
	$url = $page_id ? get_permalink( $page_id ) : home_url( '/my-bookings/' );
	return add_query_arg( 'vava_lang', 'en' === $lang ? 'en' : 'ar', $url );
}

function vava_booking_assign_or_create_my_bookings_page(): void {
	$page_id = vava_booking_my_bookings_page_id();
	if ( ! $page_id ) {
		$page_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'حجوزاتي', 'post_name' => 'my-bookings' ), true );
		if ( ! is_wp_error( $page_id ) ) {
			update_post_meta( (int) $page_id, '_wp_page_template', vava_booking_my_bookings_template_slug() );
			update_post_meta( (int) $page_id, '_vava_page_title_ar', 'حجوزاتي' );
			update_post_meta( (int) $page_id, '_vava_page_title_en', 'My bookings' );
		}
	}
	if ( $page_id && ! is_wp_error( $page_id ) ) { update_option( '_vava_booking_my_bookings_page_ready', (int) $page_id, false ); }
}
add_action( 'admin_init', 'vava_booking_assign_or_create_my_bookings_page', 45 );

/**
 * Allow the local Magic Link helper only in development-like environments.
 * The helper is still protected by the booking administration capability.
 */
function vava_booking_test_magic_link_enabled(): bool {
	$host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
	$is_local_host = in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true ) || '.local' === substr( $host, -6 ) || '.test' === substr( $host, -5 );
	$is_debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
	$is_local_environment = function_exists( 'wp_get_environment_type' ) && 'local' === wp_get_environment_type();
	return (bool) apply_filters( 'vava_booking_test_magic_link_enabled', $is_local_host || $is_debug || $is_local_environment );
}

function vava_booking_register_test_magic_link_page(): void {
	if ( ! vava_booking_test_magic_link_enabled() || ! current_user_can( vava_booking_admin_capability() ) ) { return; }
	add_submenu_page(
		'edit.php?post_type=vava_booking',
		'رابط حجوزاتي التجريبي',
		'رابط حجوزاتي التجريبي',
		vava_booking_admin_capability(),
		'vava-booking-test-magic-link',
		'vava_booking_render_test_magic_link_page'
	);
}
add_action( 'admin_menu', 'vava_booking_register_test_magic_link_page', 30 );

function vava_booking_render_test_magic_link_page(): void {
	if ( ! vava_booking_test_magic_link_enabled() || ! current_user_can( vava_booking_admin_capability() ) ) {
		wp_die( esc_html__( 'غير مسموح.', 'vava-living' ), '', array( 'response' => 403 ) );
	}

	$email = '';
	$lang = 'ar';
	$link = '';
	$booking_count = 0;
	$error = '';

	if ( 'POST' === strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
		check_admin_referer( 'vava_booking_generate_test_magic_link' );
		$email = isset( $_POST['email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : '';
		$lang = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
		if ( ! $email || ! is_email( $email ) ) {
			$error = 'أدخل بريدًا إلكترونيًا صحيحًا.';
		} else {
			$booking_count = count( vava_booking_find_customer_bookings( $email ) );
			$link = vava_booking_create_magic_link( $email, $lang, 30 * MINUTE_IN_SECONDS );
		}
	}
	?>
	<div class="wrap vava-booking-test-link-wrap" dir="rtl">
		<h1>توليد رابط تجريبي لحجوزاتي</h1>
		<p class="description">أداة للمشرفين في بيئة التطوير فقط. تُنشئ نفس رابط الدخول الآمن دون إرسال بريد، وصلاحيته 30 دقيقة.</p>
		<?php if ( $error ) : ?><div class="notice notice-error inline"><p><?php echo esc_html( $error ); ?></p></div><?php endif; ?>
		<div class="vava-booking-test-link-card">
			<form method="post">
				<?php wp_nonce_field( 'vava_booking_generate_test_magic_link' ); ?>
				<div class="vava-booking-test-link-grid">
					<label><span>البريد المستخدم في الحجوزات</span><input type="email" name="email" value="<?php echo esc_attr( $email ); ?>" placeholder="name@example.com" required dir="ltr"/></label>
					<label><span>لغة صفحة حجوزاتي</span><select name="lang"><option value="ar"<?php selected( $lang, 'ar' ); ?>>العربية</option><option value="en"<?php selected( $lang, 'en' ); ?>>English</option></select></label>
				</div>
				<button class="button button-primary button-hero" type="submit">توليد رابط لمدة 30 دقيقة</button>
			</form>
			<?php if ( $link ) : ?>
				<div class="vava-booking-test-link-result">
					<div><strong>تم إنشاء الرابط</strong><span>عدد الحجوزات المرتبطة بالبريد: <?php echo esc_html( (string) $booking_count ); ?></span></div>
					<label><span>الرابط التجريبي</span><input id="vava-booking-test-magic-url" type="text" readonly value="<?php echo esc_attr( $link ); ?>" dir="ltr"/></label>
					<div class="vava-booking-test-link-actions"><button class="button" id="vava-booking-copy-test-link" type="button">نسخ الرابط</button><a class="button button-primary" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener">فتح صفحة حجوزاتي</a></div>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<style>
	.vava-booking-test-link-wrap{max-width:980px}.vava-booking-test-link-card{margin-top:22px;padding:28px;border:1px solid #d9ddca;border-radius:20px;background:#fff;box-shadow:0 18px 48px rgba(64,67,42,.08)}.vava-booking-test-link-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(190px,1fr);gap:16px;margin-bottom:20px}.vava-booking-test-link-card label{display:grid;gap:8px;font-weight:700}.vava-booking-test-link-card input,.vava-booking-test-link-card select{width:100%;min-height:46px;padding:8px 12px;border:1px solid #cfd5bd;border-radius:10px;box-sizing:border-box}.vava-booking-test-link-result{display:grid;gap:15px;margin-top:26px;padding:20px;border-radius:16px;background:#f3f5eb;border:1px solid #d9ddca}.vava-booking-test-link-result>div:first-child{display:flex;justify-content:space-between;gap:16px;align-items:center}.vava-booking-test-link-result span{color:#65704b}.vava-booking-test-link-actions{display:flex;gap:10px;flex-wrap:wrap}@media(max-width:720px){.vava-booking-test-link-grid{grid-template-columns:1fr}.vava-booking-test-link-result>div:first-child{align-items:flex-start;flex-direction:column}}
	</style>
	<script>
	document.addEventListener('DOMContentLoaded',function(){var button=document.getElementById('vava-booking-copy-test-link');var field=document.getElementById('vava-booking-test-magic-url');if(!button||!field){return;}button.addEventListener('click',function(){var done=function(){button.textContent='تم النسخ';window.setTimeout(function(){button.textContent='نسخ الرابط';},1600);};if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(field.value).then(done).catch(function(){field.select();document.execCommand('copy');done();});}else{field.select();document.execCommand('copy');done();}});});
	</script>
	<?php
}

function vava_booking_magic_transient_key( string $token ): string {
	return 'vava_booking_magic_' . substr( hash( 'sha256', $token ), 0, 40 );
}

function vava_booking_create_magic_link( string $email, string $lang = 'ar', int $ttl = HOUR_IN_SECONDS ): string {
	$email = strtolower( sanitize_email( $email ) );
	if ( ! $email || ! is_email( $email ) ) { return vava_booking_my_bookings_url( $lang ); }
	$token = bin2hex( random_bytes( 24 ) );
	set_transient( vava_booking_magic_transient_key( $token ), array( 'email' => $email, 'lang' => 'en' === $lang ? 'en' : 'ar', 'created' => time() ), max( 10 * MINUTE_IN_SECONDS, $ttl ) );
	return add_query_arg( 'vava_magic', rawurlencode( $token ), vava_booking_my_bookings_url( $lang ) );
}

function vava_booking_magic_payload( string $token ): array {
	$token = preg_replace( '/[^a-f0-9]/i', '', $token );
	if ( 48 !== strlen( $token ) ) { return array(); }
	$payload = get_transient( vava_booking_magic_transient_key( $token ) );
	if ( ! is_array( $payload ) || empty( $payload['email'] ) || ! is_email( $payload['email'] ) ) { return array(); }
	$payload['token'] = $token;
	return $payload;
}

function vava_booking_customer_email( int $booking_id ): string {
	$email = strtolower( sanitize_email( (string) get_post_meta( $booking_id, '_vava_booking_customer_email', true ) ) );
	if ( $email ) { return $email; }
	$customer = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
	$email = strtolower( sanitize_email( (string) ( $customer['email'] ?? '' ) ) );
	if ( $email ) { update_post_meta( $booking_id, '_vava_booking_customer_email', $email ); }
	return $email;
}

function vava_booking_find_customer_bookings( string $email ): array {
	$email = strtolower( sanitize_email( $email ) );
	if ( ! $email ) { return array(); }
	$ids = get_posts(
		array(
			'post_type' => 'vava_booking',
			'post_status' => 'publish',
			'posts_per_page' => 100,
			'fields' => 'ids',
			'orderby' => 'date',
			'order' => 'DESC',
			'no_found_rows' => true,
			'meta_query' => array(
				'relation' => 'OR',
				array( 'key' => '_vava_booking_customer_email', 'value' => $email, 'compare' => '=' ),
				array( 'key' => '_vava_booking_customer', 'value' => $email, 'compare' => 'LIKE' ),
			),
		)
	);
	return array_values( array_filter( array_map( 'absint', $ids ), static function( int $booking_id ) use ( $email ): bool { return $email === vava_booking_customer_email( $booking_id ); } ) );
}

function vava_booking_request_magic_link(): void {
	check_admin_referer( 'vava_booking_request_magic_link' );
	$lang = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$email = isset( $_POST['email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : '';
	$redirect = vava_booking_my_bookings_url( $lang );
	$ip = sanitize_text_field( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$rate_key = 'vava_booking_magic_rate_' . substr( hash( 'sha256', $email . '|' . $ip ), 0, 32 );
	if ( $email && is_email( $email ) && ! get_transient( $rate_key ) ) {
		set_transient( $rate_key, 1, MINUTE_IN_SECONDS );
		$bookings = vava_booking_find_customer_bookings( $email );
		if ( $bookings ) {
			$link = ( function_exists( 'vava_customer_account_url' ) ? vava_customer_account_url( $lang ) : vava_booking_my_bookings_url( $lang ) );
			$subject = 'en' === $lang ? 'Your secure VAVA bookings link' : 'رابط متابعة حجوزاتك مع VAVA';
			$message = 'en' === $lang ? "Use this secure link to view your VAVA bookings. The link expires in one hour:\n\n" . $link : "استخدم الرابط الآمن التالي لعرض ومتابعة حجوزاتك. تنتهي صلاحية الرابط خلال ساعة:\n\n" . $link;
			wp_mail( $email, $subject, $message );
		}
	}
	wp_safe_redirect( add_query_arg( 'request_sent', '1', $redirect ) );
	exit;
}
add_action( 'admin_post_vava_booking_request_magic_link', 'vava_booking_request_magic_link' );
add_action( 'admin_post_nopriv_vava_booking_request_magic_link', 'vava_booking_request_magic_link' );

function vava_booking_public_replace_receipt(): void {
	$booking_id = isset( $_POST['booking'] ) ? absint( $_POST['booking'] ) : 0;
	$token = isset( $_POST['magic_token'] ) ? sanitize_text_field( wp_unslash( $_POST['magic_token'] ) ) : '';
	$lang = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	if ( ! $booking_id || ( function_exists( 'vava_customer_can_access_booking' ) ? ! vava_customer_can_access_booking( $booking_id, $token ) : true ) ) { wp_die( esc_html( 'en' === $lang ? 'You are not allowed to access this booking.' : 'لا تملك صلاحية الوصول إلى هذا الحجز.' ), '', array( 'response' => 403 ) ); }
	check_admin_referer( 'vava_booking_public_receipt_' . $booking_id );
	if ( ! vava_booking_customer_can_replace_receipt( $booking_id ) ) { wp_die( esc_html( 'en' === $lang ? 'This receipt cannot be replaced at the current booking status.' : 'لا يمكن استبدال الإيصال في حالة الحجز الحالية.' ), '', array( 'response' => 409 ) ); }
	$file = isset( $_FILES['receipt'] ) && is_array( $_FILES['receipt'] ) ? $_FILES['receipt'] : array();
	$result = vava_booking_replace_receipt( $booking_id, $file, get_current_user_id() );
	$redirect = $token ? add_query_arg( 'vava_magic', rawurlencode( $token ), vava_booking_my_bookings_url( $lang ) ) : ( function_exists( 'vava_customer_account_url' ) ? vava_customer_account_url( $lang ) : vava_booking_my_bookings_url( $lang ) );
	if ( is_wp_error( $result ) ) {
		wp_safe_redirect( add_query_arg( 'receipt_error', rawurlencode( $result->get_error_message() ), $redirect ) );
		exit;
	}
	delete_post_meta( $booking_id, '_vava_booking_bank_received_notification_sent' );
	vava_booking_send_bank_received( $booking_id );
	wp_safe_redirect( add_query_arg( array( 'receipt_updated' => '1', 'booking' => $booking_id ), $redirect ) );
	exit;
}
add_action( 'admin_post_vava_booking_public_replace_receipt', 'vava_booking_public_replace_receipt' );
add_action( 'admin_post_nopriv_vava_booking_public_replace_receipt', 'vava_booking_public_replace_receipt' );


function vava_booking_customer_cancel_mode( int $booking_id ): string {
	if ( ! $booking_id || 'vava_booking' !== get_post_type( $booking_id ) ) { return ''; }
	$status = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
	$payment = vava_booking_effective_payment_status( $booking_id );
	if ( in_array( $status, array( 'completed', 'cancelled', 'customer_cancelled', 'cancellation_requested', 'bank_rejected', 'payment_failed', 'payment_error' ), true ) ) { return ''; }
	if ( in_array( $status, array( 'pending', 'pending_payment', 'pending_bank_review' ), true ) && 'paid' !== $payment ) { return 'cancel'; }
	if ( in_array( $status, array( 'confirmed', 'paid' ), true ) || 'paid' === $payment ) { return 'request'; }
	return '';
}

function vava_booking_notify_admin_customer_cancellation( int $booking_id, string $mode, string $reason ): void {
	if ( function_exists( 'vava_mail_notifications_enabled' ) && ! vava_mail_notifications_enabled( 'bookings' ) ) { return; }
	$admin_email = sanitize_email( (string) get_option( 'admin_email' ) );
	if ( ! $admin_email ) { return; }
	$customer = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
	$subject = 'request' === $mode ? 'طلب إلغاء حجز من العميل #' . $booking_id : 'إلغاء حجز بواسطة العميل #' . $booking_id;
	$lines = array(
		'رقم الحجز: #' . $booking_id,
		'العميل: ' . (string) ( $customer['name'] ?? '' ),
		'البريد: ' . vava_booking_customer_email( $booking_id ),
		'الخدمة: ' . (string) get_post_meta( $booking_id, '_vava_booking_service_title', true ),
		'الموعد: ' . (string) get_post_meta( $booking_id, '_vava_booking_date', true ) . ' ' . vava_booking_format_time_12h( (string) get_post_meta( $booking_id, '_vava_booking_time', true ) ),
	);
	if ( $reason ) { $lines[] = 'السبب: ' . $reason; }
	wp_mail( $admin_email, $subject, implode( "\n", $lines ) );
}

function vava_booking_public_cancel(): void {
	$booking_id = isset( $_POST['booking'] ) ? absint( $_POST['booking'] ) : 0;
	$token = isset( $_POST['magic_token'] ) ? sanitize_text_field( wp_unslash( $_POST['magic_token'] ) ) : '';
	$lang = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$reason = isset( $_POST['reason'] ) ? sanitize_textarea_field( wp_unslash( $_POST['reason'] ) ) : '';
	if ( ! $booking_id || ( function_exists( 'vava_customer_can_access_booking' ) ? ! vava_customer_can_access_booking( $booking_id, $token ) : true ) ) {
		wp_die( esc_html( 'en' === $lang ? 'You are not allowed to access this booking.' : 'لا تملك صلاحية الوصول إلى هذا الحجز.' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'vava_booking_public_cancel_' . $booking_id );
	$mode = vava_booking_customer_cancel_mode( $booking_id );
	if ( ! $mode ) {
		wp_die( esc_html( 'en' === $lang ? 'This booking cannot be cancelled at its current status.' : 'لا يمكن إلغاء هذا الحجز في حالته الحالية.' ), '', array( 'response' => 409 ) );
	}
	$old_status = (string) get_post_meta( $booking_id, '_vava_booking_status', true );
	$old_payment = vava_booking_effective_payment_status( $booking_id );
	$now = current_time( 'mysql' );
	update_post_meta( $booking_id, '_vava_booking_customer_cancel_reason', $reason );
	update_post_meta( $booking_id, '_vava_booking_customer_cancel_session_email', vava_booking_customer_email( $booking_id ) );
	$redirect = add_query_arg( 'booking', $booking_id, $token ? add_query_arg( 'vava_magic', rawurlencode( $token ), vava_booking_my_bookings_url( $lang ) ) : ( function_exists( 'vava_customer_account_url' ) ? vava_customer_account_url( $lang ) : vava_booking_my_bookings_url( $lang ) ) );
	if ( 'cancel' === $mode ) {
		update_post_meta( $booking_id, '_vava_booking_customer_cancelled_at', $now );
		update_post_meta( $booking_id, '_vava_booking_status', 'customer_cancelled' );
		update_post_meta( $booking_id, '_vava_booking_payment_status', 'cancelled' );
		vava_booking_append_action_log( $booking_id, 'customer_cancelled', $reason, $old_status, 'customer_cancelled', $old_payment, 'cancelled' );
		vava_booking_send_status_update( $booking_id, 'customer_cancelled', $reason );
		vava_booking_notify_admin_customer_cancellation( $booking_id, 'cancel', $reason );
		wp_safe_redirect( add_query_arg( 'customer_cancelled', '1', $redirect ) );
		exit;
	}
	update_post_meta( $booking_id, '_vava_booking_pre_cancel_request_status', $old_status );
	update_post_meta( $booking_id, '_vava_booking_status', 'cancellation_requested' );
	update_post_meta( $booking_id, '_vava_booking_cancellation_request_status', 'pending' );
	update_post_meta( $booking_id, '_vava_booking_cancellation_requested_at', $now );
	vava_booking_append_action_log( $booking_id, 'customer_cancel_requested', $reason, $old_status, 'cancellation_requested', $old_payment, $old_payment );
	vava_booking_send_status_update( $booking_id, 'cancellation_requested', $reason );
	vava_booking_notify_admin_customer_cancellation( $booking_id, 'request', $reason );
	wp_safe_redirect( add_query_arg( 'cancellation_requested', '1', $redirect ) );
	exit;
}
add_action( 'admin_post_vava_booking_public_cancel', 'vava_booking_public_cancel' );
add_action( 'admin_post_nopriv_vava_booking_public_cancel', 'vava_booking_public_cancel' );

function vava_booking_my_bookings_assets(): void {
	$page_id = get_queried_object_id();
	$is_my_bookings = $page_id && ( vava_booking_my_bookings_template_slug() === get_page_template_slug( $page_id ) || $page_id === vava_booking_my_bookings_page_id() );
	if ( ! $is_my_bookings ) { return; }

	$language_style = 'en' === vava_current_language() ? 'assets/css/styles-en.css' : 'assets/css/styles-ar.css';
	wp_enqueue_style( 'vava-theme-meta', get_stylesheet_uri(), array(), vava_asset_version( 'style.css' ) );
	wp_enqueue_style( 'vava-language', get_theme_file_uri( $language_style ), array( 'vava-theme-meta' ), vava_asset_version( $language_style ) );
	wp_enqueue_style( 'vava-typography', get_theme_file_uri( 'assets/css/typography.css' ), array( 'vava-language' ), vava_asset_version( 'assets/css/typography.css' ) );
	wp_enqueue_style( 'vava-internal-wordpress', get_theme_file_uri( 'assets/css/internal-wordpress.css' ), array( 'vava-typography' ), vava_asset_version( 'assets/css/internal-wordpress.css' ) );
	wp_enqueue_style( 'vava-my-bookings', get_theme_file_uri( 'assets/css/my-bookings-vava.css' ), array( 'vava-internal-wordpress' ), vava_asset_version( 'assets/css/my-bookings-vava.css' ) );
	wp_enqueue_script( 'vava-site-language', get_theme_file_uri( 'assets/js/site-language.js' ), array(), vava_asset_version( 'assets/js/site-language.js' ), true );
	wp_enqueue_script( 'vava-main', get_theme_file_uri( 'assets/js/main.js' ), array( 'vava-site-language' ), vava_asset_version( 'assets/js/main.js' ), true );
	wp_enqueue_script( 'vava-my-bookings', get_theme_file_uri( 'assets/js/my-bookings-vava.js' ), array(), vava_asset_version( 'assets/js/my-bookings-vava.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'vava_booking_my_bookings_assets', 45 );

function vava_booking_admin_assets( string $hook ): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	$is_details_page = $screen && false !== strpos( (string) $screen->id, 'vava-booking-details' );
	if ( $screen && ( 'vava_booking' === $screen->post_type || $is_details_page ) ) {
		wp_enqueue_style( 'vava-bookings-admin', get_theme_file_uri( 'assets/css/admin-bookings-vava.css' ), array(), vava_asset_version( 'assets/css/admin-bookings-vava.css' ) );
		wp_enqueue_script( 'vava-bookings-admin', get_theme_file_uri( 'assets/js/admin-bookings-vava.js' ), array(), vava_asset_version( 'assets/js/admin-bookings-vava.js' ), true );
		wp_localize_script( 'vava-bookings-admin', 'VavaBookingsAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'vava_booking_admin_overlay' ),
			'openBooking' => 0,
			'loading' => 'جارٍ تحميل تفاصيل الحجز…',
			'error' => 'تعذر تحميل تفاصيل الحجز.',
			'confirmApprove' => 'هل تريد تنفيذ هذا الإجراء على الحجز؟',
			'confirmCancel' => 'هل أنت متأكد من إلغاء الحجز؟ سيصبح الموعد متاحًا من جديد.',
			'confirmReject' => 'هل أنت متأكد من رفض التحويل؟ سيصبح الموعد متاحًا من جديد.',
		) );
		return;
	}
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || ! vava_booking_is_page( $post_id ) ) { return; }
	wp_enqueue_style( 'vava-homepage-admin', get_theme_file_uri( 'assets/css/admin-homepage.css' ), array(), vava_asset_version( 'assets/css/admin-homepage.css' ) );
	wp_enqueue_style( 'vava-booking-admin', get_theme_file_uri( 'assets/css/admin-booking-vava.css' ), array( 'vava-homepage-admin' ), vava_asset_version( 'assets/css/admin-booking-vava.css' ) );
	wp_enqueue_script( 'vava-booking-admin', get_theme_file_uri( 'assets/js/admin-booking-vava.js' ), array( 'jquery' ), vava_asset_version( 'assets/js/admin-booking-vava.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'vava_booking_admin_assets' );

function vava_booking_use_block_editor( bool $use, WP_Post $post ): bool {
	return vava_booking_is_page( (int) $post->ID ) ? false : $use;
}
add_filter( 'use_block_editor_for_post', 'vava_booking_use_block_editor', 30, 2 );

function vava_booking_assign_or_create_page(): void {
	$stored_id = absint( get_option( '_vava_booking_page_ready' ) );
	if ( $stored_id && 'page' === get_post_type( $stored_id ) && vava_booking_is_page( $stored_id ) ) { return; }
	$page_id = vava_booking_page_id();
	if ( ! $page_id ) {
		$page_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'الحجز', 'post_name' => 'booking' ) );
		if ( ! is_wp_error( $page_id ) ) { update_post_meta( $page_id, '_wp_page_template', vava_booking_template_slug() ); }
	}
	if ( $page_id && ! is_wp_error( $page_id ) ) { update_option( '_vava_booking_page_ready', (int) $page_id, false ); }
}
add_action( 'admin_init', 'vava_booking_assign_or_create_page' );

/** Keep legacy static booking URLs compatible when the request reaches WordPress. */
function vava_booking_legacy_rewrites(): void {
	add_rewrite_rule( '^pages-(?:ar|en)/booking\.html$', 'index.php?pagename=booking', 'top' );
}
add_action( 'init', 'vava_booking_legacy_rewrites', 9 );

function vava_booking_maybe_flush_legacy_rewrites(): void {
	if ( '1' === get_option( '_vava_booking_rewrite_v1' ) ) { return; }
	vava_booking_legacy_rewrites();
	flush_rewrite_rules( false );
	update_option( '_vava_booking_rewrite_v1', '1', false );
}
add_action( 'admin_init', 'vava_booking_maybe_flush_legacy_rewrites' );


/* VAVA_BOOKING_FINAL_LAYOUT_AND_SIDEBAR_PREVIEW_V1R9 */

/* VAVA_BOOKING_RECEIPT_ADMIN_SUCCESS_TOAST_V1R15 */

/* VAVA_BOOKING_COMPLETED_PAGINATION_IMPACT_V1 */
