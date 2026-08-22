<?php
// VAVA_BOOKING_POLICY_PAGE_V1
/**
 * Bilingual privacy, terms, and booking-policy pages with a shared advanced editor.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

/** Shared template used by both managed legal pages. */
function vava_legal_template_slug(): string {
	return 'page-templates/legal-vava.php';
}

/** Normalize a supported legal page type. */
function vava_legal_normalize_type( $type ): string {
	$type = sanitize_key( (string) $type );
	return in_array( $type, array( 'privacy', 'terms', 'booking' ), true ) ? $type : 'privacy';
}

/** Resolve the managed legal type for a page. */
function vava_legal_page_type( int $post_id ): string {
	if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
		return '';
	}
	$type = sanitize_key( (string) get_post_meta( $post_id, '_vava_legal_type', true ) );
	if ( in_array( $type, array( 'privacy', 'terms', 'booking' ), true ) ) {
		return $type;
	}
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return '';
	}
	$slug = sanitize_title( (string) $post->post_name );
	if ( in_array( $slug, array( 'privacy-policy', 'privacy' ), true ) ) {
		return 'privacy';
	}
	if ( in_array( $slug, array( 'terms', 'terms-and-conditions', 'terms-conditions' ), true ) ) {
		return 'terms';
	}
	if ( in_array( $slug, array( 'booking-policy', 'booking-terms', 'reservation-policy' ), true ) ) {
		return 'booking';
	}
	return '';
}

/** Whether a page is one of the managed legal pages. */
function vava_legal_is_page( int $post_id ): bool {
	if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
		return false;
	}
	$template = (string) get_post_meta( $post_id, '_wp_page_template', true );
	return vava_legal_template_slug() === $template || '' !== vava_legal_page_type( $post_id );
}

/** Bilingual WordPress page title defaults. */
function vava_legal_title_defaults( array $defaults, int $post_id ): array {
	if ( ! vava_legal_is_page( $post_id ) ) {
		return $defaults;
	}
	$type = vava_legal_page_type( $post_id );
	if ( 'terms' === $type ) {
		$defaults['ar'] = 'الشروط والأحكام';
		$defaults['en'] = 'Terms & Conditions';
	} elseif ( 'booking' === $type ) {
		$defaults['ar'] = 'سياسة الحجز';
		$defaults['en'] = 'Booking Policy';
	} else {
		$defaults['ar'] = 'سياسة الخصوصية';
		$defaults['en'] = 'Privacy Policy';
	}
	return $defaults;
}
add_filter( 'vava_page_title_defaults', 'vava_legal_title_defaults', 10, 2 );

/** Opt the legal pages into the advanced page editor. */
function vava_legal_advanced_editor( bool $is_advanced, int $post_id ): bool {
	return $is_advanced || vava_legal_is_page( $post_id );
}
add_filter( 'vava_is_advanced_page_editor', 'vava_legal_advanced_editor', 10, 2 );

/** Meta key for one language copy. */
function vava_legal_text_meta_key( string $lang ): string {
	return '_vava_legal_text_' . vava_normalize_language( $lang );
}

/** First-run copy for a legal page. */
function vava_legal_defaults( string $type, string $lang = 'ar' ): array {
	$type = vava_legal_normalize_type( $type );
	$lang = vava_normalize_language( $lang );

	if ( 'terms' === $type && 'en' === $lang ) {
		return array(
			'eyebrow'       => 'Use of VAVA Living',
			'title'         => 'Terms & Conditions',
			'intro'         => 'These terms explain the general rules for using the VAVA Living website, its content, digital products, and communication services.',
			'updated_label' => 'Last updated',
			'updated_value' => 'July 2026',
			'content'       => '<h2>Acceptance of these terms</h2><p>By browsing or using this website, you agree to use it responsibly and in accordance with these terms and applicable law.</p><h2>Website content</h2><p>VAVA Living content is provided for general informational and educational purposes. It is not a substitute for professional medical, legal, financial, or other specialised advice.</p><h2>Digital products and purchases</h2><p>Product descriptions, prices, delivery methods, and usage conditions shown at the time of purchase form part of the applicable order. Digital files may not be redistributed, resold, or reproduced without written permission.</p><h2>Intellectual property</h2><p>Unless otherwise stated, the website design, written content, visual identity, images, downloadable materials, and trademarks belong to VAVA Living or are used with permission.</p><h2>External links</h2><p>The website may contain links to third-party services. VAVA Living is not responsible for their content, availability, or privacy practices.</p><h2>Limitation of responsibility</h2><p>We work to keep the website accurate and available, but we cannot guarantee uninterrupted access or that every item will remain error-free at all times.</p><h2>Changes to these terms</h2><p>We may update these terms when the website or its services change. The latest version will always appear on this page.</p><h2>Contact</h2><p>Questions about these terms can be sent through the Contact Us page.</p>',
		);
	}

	if ( 'terms' === $type ) {
		return array(
			'eyebrow'       => 'استخدام VAVA Living',
			'title'         => 'الشروط والأحكام',
			'intro'         => 'توضح هذه الشروط القواعد العامة لاستخدام موقع VAVA Living ومحتواه ومنتجاته الرقمية وخدمات التواصل المتاحة من خلاله.',
			'updated_label' => 'آخر تحديث',
			'updated_value' => 'يوليو 2026',
			'content'       => '<h2>الموافقة على الشروط</h2><p>باستخدام الموقع أو تصفحه، فإنك توافق على استخدامه بصورة مسؤولة وبما يتوافق مع هذه الشروط والأنظمة المعمول بها.</p><h2>محتوى الموقع</h2><p>يُقدَّم محتوى VAVA Living لأغراض معرفية وتثقيفية عامة، ولا يُعد بديلًا عن الاستشارة الطبية أو القانونية أو المالية أو أي استشارة متخصصة.</p><h2>المنتجات الرقمية والمشتريات</h2><p>تُعد أوصاف المنتجات والأسعار ووسائل التسليم وشروط الاستخدام الظاهرة عند الشراء جزءًا من الطلب. لا يجوز إعادة توزيع الملفات الرقمية أو بيعها أو نسخها دون إذن كتابي.</p><h2>الملكية الفكرية</h2><p>ما لم يُذكر خلاف ذلك، فإن تصميم الموقع والمحتوى المكتوب والهوية البصرية والصور والمواد القابلة للتحميل والعلامات تخص VAVA Living أو تُستخدم بإذن.</p><h2>الروابط الخارجية</h2><p>قد يتضمن الموقع روابط لخدمات خارجية، ولا تتحمل VAVA Living مسؤولية محتواها أو توفرها أو ممارسات الخصوصية الخاصة بها.</p><h2>حدود المسؤولية</h2><p>نعمل على الحفاظ على دقة الموقع واستمرارية توفره، لكن لا يمكن ضمان عدم انقطاع الخدمة أو خلو جميع المواد من الأخطاء في كل وقت.</p><h2>تحديث الشروط</h2><p>قد يتم تحديث هذه الشروط عند تطوير الموقع أو خدماته، وتبقى النسخة الأحدث منشورة في هذه الصفحة.</p><h2>التواصل</h2><p>يمكن إرسال أي سؤال متعلق بهذه الشروط من خلال صفحة تواصل معنا.</p>',
		);
	}

	if ( 'booking' === $type && 'en' === $lang ) {
		return array(
			'eyebrow'       => 'Booking with clarity',
			'title'         => 'Booking Policy',
			'intro'         => 'This policy explains how VAVA Living appointments are requested, reviewed, confirmed, changed, cancelled, and handled when payment is involved.',
			'updated_label' => 'Last updated',
			'updated_value' => 'August 2026',
			'content'       => '<h2>Booking request</h2><p>Submitting the booking form creates a booking request. The appointment becomes confirmed only after the required payment or transfer review is completed and VAVA Living confirms the booking.</p><h2>Appointment availability</h2><p>Available times are shown according to the configured working schedule and existing bookings. A time may become unavailable before the booking is completed if another confirmed booking uses it.</p><h2>Payment and bank transfers</h2><p>Where payment is required, the booking remains pending until the payment is completed or the bank-transfer receipt is reviewed. Entered transfer information must be accurate and match the uploaded receipt.</p><h2>Changes and rescheduling</h2><p>Requests to change an appointment are subject to availability and must be made through the available account or contact channels. A requested change is not final until it is confirmed by VAVA Living.</p><h2>Cancellation</h2><p>Cancellation availability depends on the booking status and the time remaining before the appointment. Any refund eligibility is reviewed according to the payment status, cancellation timing, and the service conditions shown at booking.</p><h2>Late arrival and missed appointments</h2><p>Late arrival may reduce the available session time. A missed appointment may be treated as used unless VAVA Living confirms another arrangement.</p><h2>Service information</h2><p>The service title, duration, price, location, and any preparation requirements shown during booking form part of the booking details.</p><h2>Contact</h2><p>Questions about a booking or this policy can be sent through the Contact Us page.</p>',
		);
	}

	if ( 'booking' === $type ) {
		return array(
			'eyebrow'       => 'حجز واضح ومطمئن',
			'title'         => 'سياسة الحجز',
			'intro'         => 'توضح هذه السياسة آلية طلب المواعيد ومراجعتها وتأكيدها وتعديلها وإلغائها، وكيفية التعامل مع الحجوزات المرتبطة بالدفع.',
			'updated_label' => 'آخر تحديث',
			'updated_value' => 'أغسطس 2026',
			'content'       => '<h2>طلب الحجز</h2><p>إرسال نموذج الحجز ينشئ طلب حجز، ولا يصبح الموعد مؤكدًا إلا بعد استكمال الدفع المطلوب أو مراجعة إيصال التحويل وتأكيد الحجز من VAVA Living.</p><h2>توافر المواعيد</h2><p>تظهر المواعيد وفق جدول العمل المحدد والحجوزات القائمة، وقد يصبح الموعد غير متاح قبل إتمام الحجز إذا تم تأكيده لعميل آخر.</p><h2>الدفع والتحويل البنكي</h2><p>عند اشتراط الدفع، يظل الحجز قيد الانتظار حتى اكتمال الدفع أو مراجعة إيصال التحويل البنكي. يجب أن تكون بيانات التحويل صحيحة ومطابقة للإيصال المرفوع.</p><h2>تعديل الموعد</h2><p>تخضع طلبات تعديل الموعد للتوافر، ويتم تقديمها من خلال الحساب أو وسائل التواصل المتاحة. ولا يُعد التعديل نهائيًا قبل تأكيده من VAVA Living.</p><h2>إلغاء الحجز</h2><p>تعتمد إمكانية الإلغاء على حالة الحجز والمدة المتبقية قبل الموعد. وتُراجع أهلية استرداد المبلغ وفق حالة الدفع وتوقيت الإلغاء وشروط الخدمة الظاهرة وقت الحجز.</p><h2>التأخر وعدم الحضور</h2><p>قد يؤدي التأخر إلى تقليل الوقت المتاح للجلسة، وقد يُعامل عدم الحضور باعتباره استخدامًا للموعد ما لم تؤكد VAVA Living ترتيبًا آخر.</p><h2>بيانات الخدمة</h2><p>يُعد اسم الخدمة ومدتها وسعرها ومكانها وأي متطلبات تحضيرية ظاهرة أثناء الحجز جزءًا من تفاصيل الحجز.</p><h2>التواصل</h2><p>يمكن إرسال الأسئلة المتعلقة بالحجز أو بهذه السياسة من خلال صفحة تواصل معنا.</p>',
		);
	}

	if ( 'en' === $lang ) {
		return array(
			'eyebrow'       => 'Privacy and trust',
			'title'         => 'Privacy Policy',
			'intro'         => 'This policy explains what information VAVA Living may receive, why it is used, and the choices available to you when using the website.',
			'updated_label' => 'Last updated',
			'updated_value' => 'July 2026',
			'content'       => '<h2>Information we may receive</h2><p>We may receive information you choose to provide through contact forms, purchases, subscriptions, or direct communication, such as your name, email address, phone number, and message details.</p><h2>How information is used</h2><p>Information is used only as reasonably necessary to respond to enquiries, deliver requested products or services, improve the website, maintain security, and meet legal obligations.</p><h2>Technical information and cookies</h2><p>The website may use essential cookies and limited technical data, such as browser type, device information, and pages visited, to keep the site working and understand general performance.</p><h2>Sharing information</h2><p>We do not sell personal information. Limited information may be processed by trusted service providers that help operate the website, email, payments, analytics, or hosting, subject to appropriate safeguards.</p><h2>Data retention and security</h2><p>We keep information only for as long as it is reasonably needed for its purpose. We use practical administrative and technical measures to reduce unauthorised access, loss, or misuse.</p><h2>Your choices</h2><p>You may ask to access, correct, or delete personal information that you have provided, subject to applicable requirements and necessary record keeping.</p><h2>External services</h2><p>Links to third-party websites are governed by their own privacy policies. Please review those policies before providing information.</p><h2>Contact</h2><p>Privacy questions or requests can be sent through the Contact Us page.</p>',
		);
	}

	return array(
		'eyebrow'       => 'الخصوصية والثقة',
		'title'         => 'سياسة الخصوصية',
		'intro'         => 'توضح هذه السياسة المعلومات التي قد تستقبلها VAVA Living، ولماذا تُستخدم، والخيارات المتاحة لك عند استخدام الموقع.',
		'updated_label' => 'آخر تحديث',
		'updated_value' => 'يوليو 2026',
		'content'       => '<h2>المعلومات التي قد نستقبلها</h2><p>قد نستقبل المعلومات التي تختار تقديمها عبر نماذج التواصل أو المشتريات أو الاشتراك أو التواصل المباشر، مثل الاسم والبريد الإلكتروني ورقم التواصل وتفاصيل الرسالة.</p><h2>كيف نستخدم المعلومات</h2><p>تُستخدم المعلومات بالقدر اللازم للرد على الاستفسارات، وتقديم المنتجات أو الخدمات المطلوبة، وتحسين الموقع، والمحافظة على الأمان، والوفاء بالالتزامات النظامية.</p><h2>المعلومات التقنية وملفات الارتباط</h2><p>قد يستخدم الموقع ملفات ارتباط أساسية وبيانات تقنية محدودة، مثل نوع المتصفح والجهاز والصفحات التي تمت زيارتها، لضمان عمل الموقع وفهم أدائه بصورة عامة.</p><h2>مشاركة المعلومات</h2><p>لا نبيع المعلومات الشخصية. قد تعالج جهات موثوقة قدرًا محدودًا من البيانات لمساعدتنا في تشغيل الموقع أو البريد أو المدفوعات أو التحليلات أو الاستضافة، مع تطبيق وسائل الحماية المناسبة.</p><h2>الاحتفاظ بالبيانات والأمان</h2><p>نحتفظ بالمعلومات للمدة اللازمة بصورة معقولة لتحقيق الغرض الذي جُمعت من أجله، ونطبق إجراءات إدارية وتقنية مناسبة للحد من الوصول غير المصرح به أو الفقد أو إساءة الاستخدام.</p><h2>خياراتك</h2><p>يمكنك طلب الوصول إلى معلوماتك أو تصحيحها أو حذفها، وفق المتطلبات المطبقة وما يلزم من حفظ السجلات.</p><h2>الخدمات الخارجية</h2><p>تخضع المواقع الخارجية المرتبطة بالموقع لسياسات الخصوصية الخاصة بها، وننصح بمراجعتها قبل تقديم أي معلومات.</p><h2>التواصل</h2><p>يمكن إرسال الأسئلة والطلبات المتعلقة بالخصوصية من خلال صفحة تواصل معنا.</p>',
	);
}

/** Read and normalize saved legal copy. */
function vava_legal_text_data( int $post_id, string $lang = 'ar' ): array {
	$lang     = vava_normalize_language( $lang );
	$type     = vava_legal_normalize_type( vava_legal_page_type( $post_id ) );
	$defaults = vava_legal_defaults( $type, $lang );
	$saved    = get_post_meta( $post_id, vava_legal_text_meta_key( $lang ), true );
	$saved    = is_array( $saved ) ? $saved : array();
	return wp_parse_args( $saved, $defaults );
}

/** Small admin-label dictionary. */
function vava_legal_admin_text( string $key, string $lang = 'ar' ): string {
	$labels = array(
		'ar' => array(
			'meta_title' => 'إعدادات الصفحة القانونية', 'hero' => 'المقدمة', 'content' => 'المحتوى', 'update' => 'تحديث',
			'eyebrow' => 'النص الصغير', 'title' => 'العنوان الرئيسي', 'intro' => 'المقدمة', 'updated_label' => 'عنوان تاريخ التحديث',
			'updated_value' => 'قيمة تاريخ التحديث', 'body' => 'محتوى الصفحة', 'preview' => 'معاينة مباشرة',
		),
		'en' => array(
			'meta_title' => 'Legal page settings', 'hero' => 'Introduction', 'content' => 'Content', 'update' => 'Update',
			'eyebrow' => 'Eyebrow', 'title' => 'Main heading', 'intro' => 'Introduction', 'updated_label' => 'Updated-date label',
			'updated_value' => 'Updated-date value', 'body' => 'Page content', 'preview' => 'Live preview',
		),
	);
	$lang = vava_normalize_language( $lang );
	return (string) ( $labels[ $lang ][ $key ] ?? $key );
}

/** Human-readable legal page title for one language. */
function vava_legal_type_label( string $type, string $lang = 'ar' ): string {
	$type = vava_legal_normalize_type( $type );
	$lang = vava_normalize_language( $lang );
	$labels = array(
		'privacy' => array( 'ar' => 'سياسة الخصوصية', 'en' => 'Privacy Policy' ),
		'terms'   => array( 'ar' => 'الشروط والأحكام', 'en' => 'Terms & Conditions' ),
		'booking' => array( 'ar' => 'سياسة الحجز', 'en' => 'Booking Policy' ),
	);
	return (string) $labels[ $type ][ $lang ];
}

/** Preview card shown in the sidebar of the advanced editor. */
function vava_legal_render_preview( WP_Post $post, string $section, string $lang ): void {
	$data = vava_legal_text_data( (int) $post->ID, $lang );
	$type = vava_legal_page_type( (int) $post->ID );
	?>
	<div class="vava-live-preview" data-legal-preview-panel data-preview-section="<?php echo esc_attr( $section ); ?>" data-preview-language="<?php echo esc_attr( $lang ); ?>"<?php echo ( 'hero' === $section && 'ar' === $lang ) ? '' : ' hidden'; ?>>
		<header class="vava-live-preview-heading"><span class="vava-status-dot"></span><div><strong<?php echo esc_html( vava_legal_admin_text( 'preview', $lang ) ); ?></strong><small><?php echo esc_html( vava_legal_type_label( $type, $lang ) ); ?></small></div></header>
		<div class="vava-preview-viewport"><div class="vava-preview-stage"><div class="vava-preview-canvas vava-legal-admin-preview" data-preview-design-width="850" dir="<?php echo 'en' === $lang ? 'ltr' : 'rtl'; ?>">
			<section class="vava-legal-preview-hero"><span data-legal-preview="eyebrow"><?php echo esc_html( (string) $data['eyebrow'] ); ?></span><h2 data-legal-preview="title"><?php echo esc_html( (string) $data['title'] ); ?></h2><p data-legal-preview="intro"><?php echo esc_html( (string) $data['intro'] ); ?></p><div class="vava-legal-preview-updated"><b data-legal-preview="updated_label"><?php echo esc_html( (string) $data['updated_label'] ); ?></b><span data-legal-preview="updated_value"><?php echo esc_html( (string) $data['updated_value'] ); ?></span></div></section>
			<section class="vava-legal-preview-content" data-legal-preview="content"><?php echo wp_kses_post( (string) $data['content'] ); ?></section>
		</div></div></div>
	</div>
	<?php
}

/** Render language-specific fields. */
function vava_legal_render_fields( int $post_id, string $section, string $lang ): void {
	$data = vava_legal_text_data( $post_id, $lang );
	$dir  = 'en' === $lang ? 'ltr' : 'rtl';
	if ( 'hero' === $section ) {
		foreach ( array( 'eyebrow', 'title', 'intro', 'updated_label', 'updated_value' ) as $field ) {
			$is_long = 'intro' === $field;
			?>
			<div class="vava-admin-field<?php echo $is_long ? ' vava-field-full' : ''; ?>">
				<label for="vava_legal_<?php echo esc_attr( $lang . '_' . $field ); ?>"><strong><?php echo esc_html( vava_legal_admin_text( $field, $lang ) ); ?></strong></label>
				<?php if ( $is_long ) : ?>
				<textarea class="widefat" data-legal-field="<?php echo esc_attr( $field ); ?>" dir="<?php echo esc_attr( $dir ); ?>" id="vava_legal_<?php echo esc_attr( $lang . '_' . $field ); ?>" name="_vava_legal_<?php echo esc_attr( $lang . '_' . $field ); ?>" rows="4"><?php echo esc_textarea( (string) $data[ $field ] ); ?></textarea>
				<?php else : ?>
				<input class="widefat" data-legal-field="<?php echo esc_attr( $field ); ?>" dir="<?php echo esc_attr( $dir ); ?>" id="vava_legal_<?php echo esc_attr( $lang . '_' . $field ); ?>" name="_vava_legal_<?php echo esc_attr( $lang . '_' . $field ); ?>" type="text" value="<?php echo esc_attr( (string) $data[ $field ] ); ?>"/>
				<?php endif; ?>
			</div>
			<?php
		}
		return;
	}
	?>
	<div class="vava-admin-field vava-field-full vava-legal-content-field">
		<label><strong><?php echo esc_html( vava_legal_admin_text( 'body', $lang ) ); ?></strong></label>
		<?php vava_render_richtext_editor( array(
			'name' => '_vava_legal_' . $lang . '_content',
			'id' => 'vava_legal_' . $lang . '_content',
			'value' => (string) $data['content'],
			'dir' => $dir,
			'class' => 'vava-legal-richtext-source',
		) ); ?>
	</div>
	<?php
}

/** Render the complete advanced editor. */
function vava_legal_render_settings( WP_Post $post ): void {
	wp_nonce_field( 'vava_legal_save', 'vava_legal_nonce' );
	?>
	<div class="vava-homepage-admin vava-legal-admin" data-active-language="ar" data-active-section="hero" data-settings-title-ar="<?php echo esc_attr( vava_legal_admin_text( 'meta_title', 'ar' ) ); ?>" data-settings-title-en="<?php echo esc_attr( vava_legal_admin_text( 'meta_title', 'en' ) ); ?>">
		<input data-vava-active-language-input name="_vava_admin_active_language" type="hidden" value="ar"/>
		<?php vava_render_bilingual_page_identity( $post, (string) get_permalink( $post ) ); ?>
		<div class="vava-admin-toolbar"><div class="vava-section-tabs" role="tablist">
			<button aria-selected="true" class="vava-section-tab is-active" data-section="hero" role="tab" type="button"><span class="dashicons dashicons-welcome-write-blog" aria-hidden="true"></span><span<?php echo vava_admin_i18n_attributes( vava_legal_admin_text( 'hero', 'ar' ), vava_legal_admin_text( 'hero', 'en' ) ); ?>><?php echo esc_html( vava_legal_admin_text( 'hero', 'ar' ) ); ?></span></button>
			<button aria-selected="false" class="vava-section-tab" data-section="content" role="tab" type="button"><span class="dashicons dashicons-media-document" aria-hidden="true"></span><span<?php echo vava_admin_i18n_attributes( vava_legal_admin_text( 'content', 'ar' ), vava_legal_admin_text( 'content', 'en' ) ); ?>><?php echo esc_html( vava_legal_admin_text( 'content', 'ar' ) ); ?></span></button>
		</div><div class="vava-toolbar-actions"><div class="vava-language-switch" role="group"><button class="is-active" data-language="ar" type="button"><span>العربية</span><small>AR</small></button><button data-language="en" type="button"><span>English</span><small>EN</small></button></div><button class="button vava-homepage-update-button" data-vava-submit type="button"><span class="dashicons dashicons-update" aria-hidden="true"></span><span<?php echo vava_admin_i18n_attributes( vava_legal_admin_text( 'update', 'ar' ), vava_legal_admin_text( 'update', 'en' ) ); ?>><?php echo esc_html( vava_legal_admin_text( 'update', 'ar' ) ); ?></span></button></div></div>
		<div class="vava-section-panels">
		<?php foreach ( array( 'hero', 'content' ) as $section ) : ?>
			<section class="vava-section-panel<?php echo 'hero' === $section ? ' is-active' : ''; ?>" data-section-panel="<?php echo esc_attr( $section ); ?>">
			<?php foreach ( array( 'ar', 'en' ) as $lang ) : ?>
				<div class="vava-language-pane<?php echo 'ar' === $lang ? ' is-active' : ''; ?>" data-language-pane="<?php echo esc_attr( $lang ); ?>" dir="<?php echo 'en' === $lang ? 'ltr' : 'rtl'; ?>">
					<div class="vava-editor-workspace"><?php vava_legal_render_preview( $post, $section, $lang ); ?><div class="vava-editor-controls"><div class="vava-fields-grid"><?php vava_legal_render_fields( (int) $post->ID, $section, $lang ); ?></div></div></div>
				</div>
			<?php endforeach; ?>
			</section>
		<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/** Register the legal editor meta box. */
function vava_legal_add_meta_boxes( string $post_type, WP_Post $post ): void {
	if ( 'page' !== $post_type || ! vava_legal_is_page( (int) $post->ID ) ) {
		return;
	}
	remove_meta_box( 'postdivrich', 'page', 'normal' );
	remove_meta_box( 'postimagediv', 'page', 'side' );
	add_meta_box( 'vava_homepage_settings', vava_legal_admin_text( 'meta_title', 'ar' ), 'vava_legal_render_settings', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'vava_legal_add_meta_boxes', 10, 2 );

/** Save legal page fields. */
function vava_legal_save_meta( int $post_id, WP_Post $post ): void {
	if ( ! isset( $_POST['vava_legal_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vava_legal_nonce'] ) ), 'vava_legal_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || 'page' !== $post->post_type || ! vava_legal_is_page( $post_id ) || ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}
	vava_save_bilingual_page_titles( $post_id );
	foreach ( array( 'ar', 'en' ) as $lang ) {
		$current = vava_legal_text_data( $post_id, $lang );
		$clean   = array();
		foreach ( array( 'eyebrow', 'title', 'updated_label', 'updated_value' ) as $field ) {
			$key             = '_vava_legal_' . $lang . '_' . $field;
			$clean[ $field ] = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : (string) $current[ $field ];
		}
		$key            = '_vava_legal_' . $lang . '_intro';
		$clean['intro'] = isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : (string) $current['intro'];
		$key              = '_vava_legal_' . $lang . '_content';
		$clean['content'] = isset( $_POST[ $key ] ) ? wp_kses_post( wp_unslash( $_POST[ $key ] ) ) : (string) $current['content'];
		update_post_meta( $post_id, vava_legal_text_meta_key( $lang ), $clean );
	}
}
add_action( 'save_post_page', 'vava_legal_save_meta', 30, 2 );

/** Disable Gutenberg for managed legal pages. */
function vava_legal_use_block_editor( bool $use_block_editor, WP_Post $post ): bool {
	return vava_legal_is_page( (int) $post->ID ) ? false : $use_block_editor;
}
add_filter( 'use_block_editor_for_post', 'vava_legal_use_block_editor', 10, 2 );

/** Legal editor body class. */
function vava_legal_admin_body_class( string $classes ): string {
	global $post;
	$post_id = $post instanceof WP_Post ? (int) $post->ID : ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $post_id && vava_legal_is_page( $post_id ) ) {
		$classes .= ' vava-homepage-classic vava-legal-classic';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'vava_legal_admin_body_class' );

/** Load legal-page editor assets. */
function vava_legal_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || ! vava_legal_is_page( $post_id ) ) {
		return;
	}
	wp_enqueue_style( 'vava-homepage-admin', get_theme_file_uri( 'assets/css/admin-homepage.css' ), array(), vava_asset_version( 'assets/css/admin-homepage.css' ) );
	wp_enqueue_style( 'vava-legal-admin', get_theme_file_uri( 'assets/css/admin-legal.css' ), array( 'vava-homepage-admin' ), vava_asset_version( 'assets/css/admin-legal.css' ) );
	wp_enqueue_script( 'vava-legal-admin', get_theme_file_uri( 'assets/js/admin-legal.js' ), array( 'jquery' ), vava_asset_version( 'assets/js/admin-legal.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'vava_legal_admin_assets' );

/** Create/assign all managed legal pages on the first admin visit after the patch. */
function vava_legal_assign_or_create_pages(): void {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'vava_legal_pages_migrated_v2' ) ) {
		return;
	}
	$configs = array(
		'privacy' => array( 'slug' => 'privacy-policy', 'title_ar' => 'سياسة الخصوصية', 'title_en' => 'Privacy Policy' ),
		'terms'   => array( 'slug' => 'terms-and-conditions', 'title_ar' => 'الشروط والأحكام', 'title_en' => 'Terms & Conditions' ),
		'booking' => array( 'slug' => 'booking-policy', 'title_ar' => 'سياسة الحجز', 'title_en' => 'Booking Policy' ),
	);
	$created = array();
	foreach ( $configs as $type => $config ) {
		$page = get_page_by_path( $config['slug'], OBJECT, 'page' );
		if ( ! $page ) {
			$candidates = get_posts( array(
				'post_type' => 'page', 'post_status' => array( 'publish', 'draft', 'private' ), 'posts_per_page' => -1,
				'meta_key' => '_vava_legal_type', 'meta_value' => $type, 'no_found_rows' => true,
			) );
			$page = $candidates[0] ?? null;
		}
		if ( ! $page ) {
			$page_id = wp_insert_post( array(
				'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $config['title_ar'], 'post_name' => $config['slug'],
			), true );
			$page = ! is_wp_error( $page_id ) ? get_post( $page_id ) : null;
		}
		if ( $page instanceof WP_Post ) {
			update_post_meta( (int) $page->ID, '_vava_legal_type', $type );
			update_post_meta( (int) $page->ID, '_wp_page_template', vava_legal_template_slug() );
			update_post_meta( (int) $page->ID, vava_page_title_meta_key( 'ar' ), $config['title_ar'] );
			update_post_meta( (int) $page->ID, vava_page_title_meta_key( 'en' ), $config['title_en'] );
			foreach ( array( 'ar', 'en' ) as $lang ) {
				if ( ! metadata_exists( 'post', (int) $page->ID, vava_legal_text_meta_key( $lang ) ) ) {
					update_post_meta( (int) $page->ID, vava_legal_text_meta_key( $lang ), vava_legal_defaults( $type, $lang ) );
				}
			}
			$created[ $type ] = (int) $page->ID;
		}
	}
	if ( count( $created ) === count( $configs ) ) {
		update_option( 'vava_legal_pages_migrated_v2', $created, false );
	}
}
add_action( 'admin_init', 'vava_legal_assign_or_create_pages', 42 );

/** Public URL for a managed legal page in the requested language. */
function vava_legal_page_url( string $type, string $lang = 'ar' ): string {
	$type = vava_legal_normalize_type( $type );
	$lang = function_exists( 'vava_normalize_language' ) ? vava_normalize_language( $lang ) : ( 'en' === $lang ? 'en' : 'ar' );
	$ids  = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_vava_legal_type',
			'meta_value'     => $type,
		)
	);
	$page_id = isset( $ids[0] ) ? absint( $ids[0] ) : 0;
	$slugs = array( 'privacy' => 'privacy-policy', 'terms' => 'terms-and-conditions', 'booking' => 'booking-policy' );
	$slug  = (string) $slugs[ $type ];
	if ( ! $page_id ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );
		$page_id = $page instanceof WP_Post ? (int) $page->ID : 0;
	}
	$url = $page_id ? get_permalink( $page_id ) : home_url( '/' . $slug . '/' );
	return function_exists( 'vava_language_url' ) ? (string) vava_language_url( $lang, $url ) : (string) $url;
}
