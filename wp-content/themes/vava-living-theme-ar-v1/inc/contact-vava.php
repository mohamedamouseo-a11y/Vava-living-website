<?php
/**
 * Bilingual VAVA Contact page, live editor, flexible form builder, and secure form handling.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

function vava_contact_template_slug(): string {
	return 'page-templates/contact-vava.php';
}

function vava_contact_is_page( int $post_id ): bool {
	if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
		return false;
	}
	$template = (string) get_post_meta( $post_id, '_wp_page_template', true );
	$post     = get_post( $post_id );
	if ( vava_contact_template_slug() === $template ) {
		return true;
	}
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return in_array( $post->post_name, array( 'contact', 'contact-us', 'vava-contact' ), true )
		|| in_array( trim( (string) $post->post_title ), array( 'تواصل معنا', 'تواصل', 'Contact', 'Contact Us' ), true );
}


/** Return the canonical Contact page ID used by shared communication settings. */
function vava_contact_page_id(): int {
	$cached = wp_cache_get( 'vava_contact_page_id', 'vava' );
	if ( false !== $cached ) {
		return absint( $cached );
	}
	$ids = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 20,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'meta_key'       => '_wp_page_template',
			'meta_value'     => vava_contact_template_slug(),
		)
	);
	$page_id = $ids ? absint( $ids[0] ) : 0;
	if ( ! $page_id ) {
		foreach ( array( 'contact', 'contact-us', 'vava-contact' ) as $slug ) {
			$page = get_page_by_path( $slug, OBJECT, 'page' );
			if ( $page instanceof WP_Post ) {
				$page_id = (int) $page->ID;
				break;
			}
		}
	}
	wp_cache_set( 'vava_contact_page_id', $page_id, 'vava', HOUR_IN_SECONDS );
	return $page_id;
}

function vava_contact_mail_settings_defaults(): array {
	return array(
		'phone'                 => '',
		'whatsapp'              => '',
		'whatsapp_label_ar'     => 'تواصل عبر WhatsApp',
		'whatsapp_label_en'     => 'Chat on WhatsApp',
		'notify_contact'        => 1,
		'notify_bookings'       => 1,
		'notify_products'       => 1,
		'notify_admin'          => 1,
	);
}

function vava_contact_mail_settings( int $post_id = 0 ): array {
	$post_id  = $post_id > 0 ? $post_id : vava_contact_page_id();
	$defaults = vava_contact_mail_settings_defaults();
	$saved    = $post_id ? get_post_meta( $post_id, '_vava_contact_mail_settings', true ) : array();
	$data     = is_array( $saved ) ? array_replace( $defaults, $saved ) : $defaults;
	$data['phone']             = sanitize_text_field( (string) ( $data['phone'] ?? '' ) );
	$data['whatsapp']          = sanitize_text_field( (string) ( $data['whatsapp'] ?? '' ) );
	$data['whatsapp_label_ar'] = sanitize_text_field( (string) ( $data['whatsapp_label_ar'] ?? $defaults['whatsapp_label_ar'] ) );
	$data['whatsapp_label_en'] = sanitize_text_field( (string) ( $data['whatsapp_label_en'] ?? $defaults['whatsapp_label_en'] ) );
	foreach ( array( 'contact', 'bookings', 'products', 'admin' ) as $channel ) {
		$key          = 'notify_' . $channel;
		$data[ $key ] = empty( $data[ $key ] ) ? 0 : 1;
	}
	return $data;
}

function vava_mail_notifications_enabled( string $channel ): bool {
	$channel = sanitize_key( $channel );
	if ( ! in_array( $channel, array( 'contact', 'bookings', 'products', 'admin' ), true ) ) {
		return true;
	}
	$settings = vava_contact_mail_settings();
	return ! empty( $settings[ 'notify_' . $channel ] );
}

function vava_mail_admin_recipient(): string {
	$email = sanitize_email( (string) get_option( 'admin_email' ) );
	return is_email( $email ) ? $email : '';
}

function vava_mail_sender_name(): string {
	$name = trim( wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES ) );
	return '' !== $name ? $name : 'VAVA Living';
}

function vava_contact_whatsapp_url( string $number = '' ): string {
	if ( '' === $number ) {
		$number = (string) ( vava_contact_mail_settings()['whatsapp'] ?? '' );
	}
	$digits = preg_replace( '/\D+/', '', $number );
	return $digits ? 'https://wa.me/' . $digits : '';
}

/** Replace WordPress as the visible sender name for all site mail. */
function vava_contact_wp_mail_from_name( string $name ): string {
	return vava_mail_sender_name();
}
add_filter( 'wp_mail_from_name', 'vava_contact_wp_mail_from_name', 30 );

function vava_contact_title_defaults( array $defaults, int $post_id ): array {
	if ( vava_contact_is_page( $post_id ) ) {
		$defaults['ar'] = 'تواصل معنا';
		$defaults['en'] = 'Contact Us';
	}
	return $defaults;
}
add_filter( 'vava_page_title_defaults', 'vava_contact_title_defaults', 10, 2 );

function vava_contact_advanced_editor( bool $is_advanced, int $post_id ): bool {
	return $is_advanced || vava_contact_is_page( $post_id );
}
add_filter( 'vava_is_advanced_page_editor', 'vava_contact_advanced_editor', 10, 2 );

function vava_contact_text_meta_key( string $lang ): string {
	return '_vava_contact_text_' . ( 'en' === $lang ? 'en' : 'ar' );
}

function vava_contact_default_field_schema(): array {
	return array(
		array( 'id' => 'name', 'type' => 'text', 'required' => 1, 'visible' => 1, 'width' => 'half', 'protected' => 1 ),
		array( 'id' => 'email', 'type' => 'email', 'required' => 1, 'visible' => 1, 'width' => 'half', 'protected' => 1 ),
		array( 'id' => 'subject', 'type' => 'text', 'required' => 0, 'visible' => 1, 'width' => 'full', 'protected' => 1 ),
		array( 'id' => 'message', 'type' => 'textarea', 'required' => 1, 'visible' => 1, 'width' => 'full', 'protected' => 1 ),
	);
}

function vava_contact_default_field_texts( string $lang = 'ar' ): array {
	if ( 'en' === $lang ) {
		return array(
			'name' => array( 'label' => 'Name', 'placeholder' => 'Enter your full name', 'options' => array() ),
			'email' => array( 'label' => 'Email address', 'placeholder' => 'you@example.com', 'options' => array() ),
			'subject' => array( 'label' => 'Message subject', 'placeholder' => 'What would you like to discuss?', 'options' => array() ),
			'message' => array( 'label' => 'Message', 'placeholder' => 'Write your message here…', 'options' => array() ),
		);
	}
	return array(
		'name' => array( 'label' => 'الاسم', 'placeholder' => 'اكتب اسمك الكامل', 'options' => array() ),
		'email' => array( 'label' => 'البريد الإلكتروني', 'placeholder' => 'you@example.com', 'options' => array() ),
		'subject' => array( 'label' => 'موضوع الرسالة', 'placeholder' => 'ما الموضوع الذي ترغب في مناقشته؟', 'options' => array() ),
		'message' => array( 'label' => 'الرسالة', 'placeholder' => 'اكتب رسالتك هنا…', 'options' => array() ),
	);
}


function vava_contact_default_guide_schema(): array {
	return array(
		array( 'id' => 'guide_1', 'visible' => 1, 'field_ids' => array( 'subject' ) ),
		array( 'id' => 'guide_2', 'visible' => 1, 'field_ids' => array( 'message' ) ),
		array( 'id' => 'guide_3', 'visible' => 1, 'field_ids' => array() ),
	);
}

function vava_contact_sanitize_guide_schema( $schema, array $field_schema ): array {
	$source      = is_array( $schema ) ? $schema : array();
	$allowed_ids = array_map( static function ( array $field ): string { return (string) $field['id']; }, $field_schema );
	$clean       = array();
	$seen        = array();
	foreach ( array_slice( $source, 0, 12 ) as $index => $item ) {
		if ( ! is_array( $item ) ) { continue; }
		$id = sanitize_key( (string) ( $item['id'] ?? '' ) );
		if ( '' === $id ) { $id = 'guide_' . ( $index + 1 ); }
		if ( isset( $seen[ $id ] ) ) { continue; }
		$seen[ $id ] = true;
		$field_ids = array();
		foreach ( array_slice( (array) ( $item['field_ids'] ?? array() ), 0, 24 ) as $field_id ) {
			$field_id = sanitize_key( (string) $field_id );
			if ( in_array( $field_id, $allowed_ids, true ) && ! in_array( $field_id, $field_ids, true ) ) {
				$field_ids[] = $field_id;
			}
		}
		$clean[] = array(
			'id'        => $id,
			'visible'   => isset( $item['visible'] ) && empty( $item['visible'] ) ? 0 : 1,
			'field_ids' => $field_ids,
		);
	}
	return array_values( $clean );
}

function vava_contact_sanitize_guide_texts( $cards, string $lang, array $guide_schema ): array {
	$source   = is_array( $cards ) ? $cards : array();
	$defaults = vava_contact_text_defaults( $lang )['guide']['cards'] ?? array();
	$by_id    = array();
	$legacy   = array_values( $source );
	foreach ( $source as $key => $card ) {
		if ( ! is_array( $card ) ) { continue; }
		$id = sanitize_key( (string) ( $card['id'] ?? ( is_string( $key ) ? $key : '' ) ) );
		if ( '' !== $id ) { $by_id[ $id ] = $card; }
	}
	$clean = array();
	foreach ( $guide_schema as $index => $definition ) {
		$id       = (string) $definition['id'];
		$fallback = is_array( $defaults[ $index ] ?? null ) ? $defaults[ $index ] : array();
		$current  = is_array( $by_id[ $id ] ?? null ) ? $by_id[ $id ] : ( is_array( $legacy[ $index ] ?? null ) ? $legacy[ $index ] : $fallback );
		$clean[]  = array(
			'id'    => $id,
			'title' => sanitize_text_field( (string) ( $current['title'] ?? $fallback['title'] ?? ( 'en' === $lang ? 'Guide card' : 'بطاقة إرشادية' ) ) ),
			'body'  => sanitize_textarea_field( (string) ( $current['body'] ?? $fallback['body'] ?? '' ) ),
		);
	}
	return $clean;
}

function vava_contact_guide_cards_for_display( array $text, array $shared ): array {
	$schema = (array) ( $shared['guide_card_schema'] ?? array() );
	$cards  = (array) ( $text['guide']['cards'] ?? array() );
	$by_id  = array();
	foreach ( $cards as $card ) {
		if ( is_array( $card ) && ! empty( $card['id'] ) ) { $by_id[ (string) $card['id'] ] = $card; }
	}
	$output = array();
	foreach ( $schema as $definition ) {
		if ( empty( $definition['visible'] ) ) { continue; }
		$id   = (string) ( $definition['id'] ?? '' );
		$copy = (array) ( $by_id[ $id ] ?? array() );
		if ( '' === $id || ( '' === trim( (string) ( $copy['title'] ?? '' ) ) && '' === trim( (string) ( $copy['body'] ?? '' ) ) ) ) { continue; }
		$output[] = array(
			'id'        => $id,
			'title'     => (string) ( $copy['title'] ?? '' ),
			'body'      => (string) ( $copy['body'] ?? '' ),
			'field_ids' => array_values( array_filter( array_map( 'sanitize_key', (array) ( $definition['field_ids'] ?? array() ) ) ) ),
		);
	}
	return $output;
}

function vava_contact_text_defaults( string $lang = 'ar' ): array {
	if ( 'en' === $lang ) {
		return array(
			'hero' => array(
				'eyebrow' => 'Contact',
				'title'    => 'Welcome',
				'intro'    => 'If something is inviting you to connect—an inquiry, an idea, a wish to collaborate, or simply something you would like to share with VAVA—we would be happy to hear from you.',
				'note'     => 'This space is open to sincere human connection, questions, suggestions, and collaborations aligned with VAVA’s vision.',
			),
			'form' => array(
				'title'          => 'Send a message',
				'name_label'     => 'Name',
				'email_label'    => 'Email address',
				'subject_label'  => 'Message subject',
				'message_label'  => 'Message',
				'submit_label'   => 'Send',
				'social_eyebrow' => 'Follow us or connect through',
				'success'        => 'Your message has been sent successfully. We will get back to you as soon as possible.',
				'error'          => 'We could not send your message. Please review the fields and try again.',
				'hold_idle'      => 'Press and hold to continue',
				'hold_active'    => 'Keep holding…',
				'hold_verified'  => 'Verification completed',
				'hold_error'     => 'Verification was interrupted. Please try again.',
				'email_invalid'  => 'Please enter a valid email address.',
				'field_texts'    => vava_contact_default_field_texts( 'en' ),
			),
			'guide' => array(
				'eyebrow' => 'What should I write?',
				'title'    => 'For a clearer, more personal message',
				'intro'    => 'Your message does not need to be long. Share only what helps us understand the most suitable doorway within VAVA.',
				'cards'    => array(
					array( 'title' => 'Message subject', 'body' => 'For example: choosing the right package, a question before booking, a digital product inquiry, or a collaboration request.' ),
					array( 'title' => 'Message content', 'body' => 'Share your current need, what you are looking for, and whether a session, a digital product, or guidance would suit you best.' ),
					array( 'title' => 'Helpful details', 'body' => 'You may mention a convenient contact time, a specific question, or your current stage without sharing more than you wish.' ),
				),
			),
		);
	}

	return array(
		'hero' => array(
			'eyebrow' => 'تواصل',
			'title'    => 'أهلًا بك',
			'intro'    => 'إذا كان هناك شيء يدعوك للتواصل، فأهلًا بك. سواء كان استفسارًا، أو فكرة، أو رغبة في التعاون، أو مشاركة شيء وجد طريقه إليك من خلال VAVA، يسعدنا أن نسمع منك.',
			'note'     => 'هذه المساحة مفتوحة للتواصل الإنساني الصادق، وللأسئلة، والمقترحات، والتعاونات التي تنسجم مع رؤية VAVA.',
		),
		'form' => array(
			'title'          => 'إرسال الرسالة',
			'name_label'     => 'الاسم',
			'email_label'    => 'البريد الإلكتروني',
			'subject_label'  => 'موضوع الرسالة',
			'message_label'  => 'الرسالة',
			'submit_label'   => 'إرسال',
			'social_eyebrow' => 'تابعونا أو تواصلوا عبر',
			'success'        => 'تم إرسال رسالتك بنجاح، وسنعود إليك في أقرب وقت ممكن.',
			'error'          => 'تعذر إرسال الرسالة. راجع الحقول وحاول مرة أخرى.',
			'hold_idle'      => 'اضغط مطولًا للمتابعة',
			'hold_active'    => 'استمر في الضغط…',
			'hold_verified'  => 'تم التحقق بنجاح',
			'hold_error'     => 'لم يكتمل التحقق. حاول مرة أخرى.',
			'email_invalid'  => 'يرجى إدخال بريد إلكتروني بصيغة صحيحة.',
			'field_texts'    => vava_contact_default_field_texts( 'ar' ),
		),
		'guide' => array(
			'eyebrow' => 'ماذا أكتب؟',
			'title'    => 'رسالة أوضح، لنساعدك بشكل أفضل',
			'intro'    => 'لا تحتاج رسالتك إلى أن تكون طويلة. يكفي أن تشاركنا ما تبحث عنه، وسنساعدك على الوصول إلى الخيار الأنسب.',
			'cards'    => array(
				array( 'title' => 'موضوع الرسالة', 'body' => 'ابدأ بسبب تواصلك، مثل: استفسار عن خدمة أو منتج، المساعدة في اختيار الخيار المناسب، أو رغبة في التعاون.' ),
				array( 'title' => 'محتوى الرسالة', 'body' => 'اشرح باختصار ما تحتاجه، أو السؤال الذي تبحث عن إجابته، أو ما تأمل أن تساعدك VAVA فيه.' ),
				array( 'title' => 'تفاصيل تساعدنا', 'body' => 'يمكنك إضافة أي تفاصيل ترى أنها مفيدة، مثل الوقت المناسب للتواصل، أو أي معلومة تعتقد أنها ستساعدنا على خدمتك بشكل أفضل.' ),
			),
		),
	);
}

function vava_contact_sanitize_schema( $schema ): array {
	$allowed_types = array( 'text', 'email', 'tel', 'select', 'textarea' );
	$items         = is_array( $schema ) ? $schema : array();
	$seen          = array();
	$core          = array();
	$additional    = array();
	$protected     = array(
		'name'    => array( 'type' => 'text', 'required' => 1, 'visible' => 1, 'protected' => 1 ),
		'email'   => array( 'type' => 'email', 'required' => 1, 'visible' => 1, 'protected' => 1 ),
		'subject' => array( 'type' => 'text', 'required' => 0, 'visible' => 1, 'protected' => 1 ),
		'message' => array( 'type' => 'textarea', 'required' => 1, 'visible' => 1, 'protected' => 1 ),
	);

	foreach ( array_slice( $items, 0, 24 ) as $item ) {
		if ( ! is_array( $item ) ) { continue; }
		$id = sanitize_key( (string) ( $item['id'] ?? '' ) );
		if ( '' === $id || isset( $seen[ $id ] ) ) { continue; }
		$seen[ $id ] = true;
		$type = sanitize_key( (string) ( $item['type'] ?? 'text' ) );
		$type = in_array( $type, $allowed_types, true ) ? $type : 'text';
		$row  = array(
			'id'        => $id,
			'type'      => $type,
			'required'  => empty( $item['required'] ) ? 0 : 1,
			'visible'   => isset( $item['visible'] ) && empty( $item['visible'] ) ? 0 : 1,
			'width'     => 'half' === (string) ( $item['width'] ?? '' ) ? 'half' : 'full',
			'protected' => 0,
		);
		if ( isset( $protected[ $id ] ) ) {
			$row        = array_merge( $row, $protected[ $id ] );
			$core[ $id ] = $row;
		} else {
			$additional[] = $row;
		}
	}

	foreach ( vava_contact_default_field_schema() as $default ) {
		$id = (string) $default['id'];
		if ( ! isset( $core[ $id ] ) ) {
			$core[ $id ] = $default;
		}
	}

	// Core fields always keep a safe, predictable structure. Added fields are
	// permanently placed between the subject and the message field.
	return array_values(
		array_merge(
			array( $core['name'], $core['email'], $core['subject'] ),
			$additional,
			array( $core['message'] )
		)
	);
}

function vava_contact_sanitize_field_texts( $texts, string $lang, array $schema ): array {
	$defaults = vava_contact_default_field_texts( $lang );
	$source   = is_array( $texts ) ? $texts : array();
	$clean    = array();
	foreach ( $schema as $field ) {
		$id      = (string) $field['id'];
		$current = isset( $source[ $id ] ) && is_array( $source[ $id ] ) ? $source[ $id ] : ( $defaults[ $id ] ?? array() );
		$label   = sanitize_text_field( (string) ( $current['label'] ?? ( 'en' === $lang ? 'Field' : 'حقل' ) ) );
		$holder  = sanitize_text_field( (string) ( $current['placeholder'] ?? '' ) );
		$options = array();
		foreach ( array_slice( (array) ( $current['options'] ?? array() ), 0, 30 ) as $option ) {
			$option = sanitize_text_field( (string) $option );
			if ( '' !== $option ) { $options[] = $option; }
		}
		$clean[ $id ] = array( 'label' => $label, 'placeholder' => $holder, 'options' => array_values( $options ) );
	}
	return $clean;
}

function vava_contact_shared_defaults(): array {
	return array(
		'hero_image_id'     => 0,
		'field_schema'      => vava_contact_default_field_schema(),
		'guide_card_schema' => vava_contact_default_guide_schema(),
		'hold_enabled'      => 1,
		'hold_duration'     => 4,
	);
}

function vava_contact_text_data( int $post_id, string $lang ): array {
	$lang       = 'en' === $lang ? 'en' : 'ar';
	$defaults   = vava_contact_text_defaults( $lang );
	$saved      = get_post_meta( $post_id, vava_contact_text_meta_key( $lang ), true );
	$saved      = is_array( $saved ) ? $saved : array();
	$data       = array_replace_recursive( $defaults, $saved );
	$saved_form = isset( $saved['form'] ) && is_array( $saved['form'] ) ? $saved['form'] : array();
	if ( empty( $saved_form['field_texts'] ) ) {
		$map = array( 'name' => 'name_label', 'email' => 'email_label', 'subject' => 'subject_label', 'message' => 'message_label' );
		foreach ( $map as $id => $legacy_key ) {
			if ( isset( $saved_form[ $legacy_key ] ) && '' !== trim( (string) $saved_form[ $legacy_key ] ) ) {
				$data['form']['field_texts'][ $id ]['label'] = sanitize_text_field( (string) $saved_form[ $legacy_key ] );
			}
		}
	}
	$shared = vava_contact_shared_data( $post_id );
	$data['form']['field_texts'] = vava_contact_sanitize_field_texts( $data['form']['field_texts'] ?? array(), $lang, $shared['field_schema'] );
	$data['guide']['cards'] = vava_contact_sanitize_guide_texts( $data['guide']['cards'] ?? array(), $lang, $shared['guide_card_schema'] );
	return $data;
}

function vava_contact_shared_data( int $post_id ): array {
	$defaults = vava_contact_shared_defaults();
	$saved    = get_post_meta( $post_id, '_vava_contact_shared', true );
	$data     = is_array( $saved ) ? array_replace_recursive( $defaults, $saved ) : $defaults;
	$data['hero_image_id']   = absint( $data['hero_image_id'] ?? 0 );
	$data['field_schema']      = vava_contact_sanitize_schema( $data['field_schema'] ?? array() );
	$data['guide_card_schema'] = vava_contact_sanitize_guide_schema( $data['guide_card_schema'] ?? array(), $data['field_schema'] );
	$data['hold_enabled']      = empty( $data['hold_enabled'] ) ? 0 : 1;
	$data['hold_duration']     = max( 3, min( 8, absint( $data['hold_duration'] ?? 4 ) ) );
	return $data;
}

function vava_contact_image_url( int $attachment_id, string $fallback_asset = 'assets/images/contact-hero.png', string $size = 'full' ): string {
	if ( $attachment_id > 0 ) {
		$url = wp_get_attachment_image_url( $attachment_id, $size );
		if ( $url ) { return (string) $url; }
	}
	return vava_asset_uri( $fallback_asset );
}

function vava_contact_sections( string $lang = 'ar' ): array {
	return 'en' === $lang
		? array( 'hero' => 'Hero', 'form' => 'Contact form', 'guide' => 'Message guide', 'mail' => 'Email settings' )
		: array( 'hero' => 'الهيرو', 'form' => 'نموذج التواصل', 'guide' => 'دليل الرسالة', 'mail' => 'إعدادات البريد' );
}

function vava_contact_section_icon( string $section ): string {
	if ( 'form' === $section ) { return '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m5 8 7 5 7-5"/></svg>'; }
	if ( 'guide' === $section ) { return '<svg viewBox="0 0 24 24"><path d="M6 4h12v16H6z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>'; }
	if ( 'mail' === $section ) { return '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m5 8 7 5 7-5"/><path d="M16.5 16.5h4M18.5 14.5v4"/></svg>'; }
	return '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="3"/><circle cx="9" cy="9" r="2"/><path d="m6 17 5-5 3 3 2-2 2 2"/></svg>';
}

function vava_contact_admin_text( string $key, string $lang = 'ar' ): string {
	$texts = array(
		'meta_title' => array( 'ar' => 'إعدادات صفحة تواصل معنا', 'en' => 'Contact Page Settings' ),
		'fields_language' => array( 'ar' => 'لغة الحقول', 'en' => 'Fields language' ),
		'update' => array( 'ar' => 'تحديث', 'en' => 'Update' ),
		'live_preview' => array( 'ar' => 'معاينة مباشرة', 'en' => 'Live preview' ),
		'shared' => array( 'ar' => 'إعدادات مشتركة بين اللغتين', 'en' => 'Settings shared between both languages' ),
		'hero_image' => array( 'ar' => 'صورة الهيرو', 'en' => 'Hero image' ),
		'choose_replace' => array( 'ar' => 'اختيار أو استبدال', 'en' => 'Choose or replace' ),
		'delete_file' => array( 'ar' => 'حذف الملف', 'en' => 'Delete file' ),
		'recipient_email' => array( 'ar' => 'البريد المستلم للرسائل', 'en' => 'Message recipient email' ),
		'recipient_help' => array( 'ar' => 'تُرسل رسائل النموذج إلى هذا البريد ولا يتم تخزين محتواها داخل قاعدة البيانات.', 'en' => 'Form messages are sent to this address and their content is not stored in the database.' ),
		'hero_eyebrow' => array( 'ar' => 'النص الصغير', 'en' => 'Small text' ),
		'hero_title' => array( 'ar' => 'العنوان الرئيسي', 'en' => 'Main title' ),
		'hero_intro' => array( 'ar' => 'المقدمة', 'en' => 'Introduction' ),
		'hero_note' => array( 'ar' => 'النص الداعم', 'en' => 'Supporting text' ),
		'copy_settings' => array( 'ar' => 'نصوص النموذج', 'en' => 'Form copy' ),
		'copy_choose' => array( 'ar' => 'اختر النص المراد تعديله', 'en' => 'Choose text to edit' ),
		'copy_value' => array( 'ar' => 'النص', 'en' => 'Text' ),
		'field_builder' => array( 'ar' => 'بناء وترتيب حقول النموذج', 'en' => 'Build and order form fields' ),
		'add_field' => array( 'ar' => 'إضافة حقل', 'en' => 'Add field' ),
		'field_label' => array( 'ar' => 'عنوان الحقل', 'en' => 'Field label' ),
		'field_placeholder' => array( 'ar' => 'النص الإرشادي', 'en' => 'Placeholder' ),
		'field_type' => array( 'ar' => 'نوع الحقل', 'en' => 'Field type' ),
		'field_width' => array( 'ar' => 'عرض الحقل', 'en' => 'Field width' ),
		'field_required' => array( 'ar' => 'إلزامي', 'en' => 'Required' ),
		'field_visible' => array( 'ar' => 'ظاهر', 'en' => 'Visible' ),
		'field_options' => array( 'ar' => 'خيارات القائمة — خيار في كل سطر', 'en' => 'Select options — one per line' ),
		'protected_field' => array( 'ar' => 'حقل أساسي — غير قابل للحذف', 'en' => 'Core field — cannot be deleted' ),
		'delete_field' => array( 'ar' => 'حذف الحقل', 'en' => 'Delete field' ),
		'half_width' => array( 'ar' => 'نصف صف', 'en' => 'Half row' ),
		'full_width' => array( 'ar' => 'صف كامل', 'en' => 'Full row' ),
		'type_text' => array( 'ar' => 'حقل نصي', 'en' => 'Text field' ),
		'type_tel' => array( 'ar' => 'رقم تواصل', 'en' => 'Phone field' ),
		'type_select' => array( 'ar' => 'قائمة اختيار', 'en' => 'Select list' ),
		'type_textarea' => array( 'ar' => 'نص متعدد الأسطر', 'en' => 'Textarea' ),
		'hold_settings' => array( 'ar' => 'حماية الضغط المطول', 'en' => 'Press-and-hold protection' ),
		'hold_enabled' => array( 'ar' => 'تفعيل الضغط المطول قبل الإرسال', 'en' => 'Enable press-and-hold before sending' ),
		'hold_duration' => array( 'ar' => 'مدة الضغط بالثواني', 'en' => 'Hold duration in seconds' ),
		'guide_eyebrow' => array( 'ar' => 'النص الصغير', 'en' => 'Small text' ),
		'guide_title' => array( 'ar' => 'عنوان الدليل', 'en' => 'Guide title' ),
		'guide_intro' => array( 'ar' => 'مقدمة الدليل', 'en' => 'Guide introduction' ),
		'guide_builder' => array( 'ar' => 'بطاقات دليل الرسالة', 'en' => 'Message guide cards' ),
		'guide_builder_help' => array( 'ar' => 'أضف بطاقات إرشادية واربط كل بطاقة بحقل واحد أو مجموعة حقول.', 'en' => 'Add guidance cards and link each card to one or more form fields.' ),
		'add_guide_card' => array( 'ar' => 'إضافة بطاقة', 'en' => 'Add card' ),
		'card_title' => array( 'ar' => 'عنوان البطاقة', 'en' => 'Card title' ),
		'card_body' => array( 'ar' => 'وصف البطاقة', 'en' => 'Card description' ),
		'card_fields' => array( 'ar' => 'الحقول المرتبطة', 'en' => 'Linked fields' ),
		'card_general' => array( 'ar' => 'إرشاد عام بدون ربط', 'en' => 'General guidance without a field link' ),
		'card_visible' => array( 'ar' => 'إظهار البطاقة', 'en' => 'Show card' ),
		'delete_card' => array( 'ar' => 'حذف البطاقة', 'en' => 'Delete card' ),
		'mail_settings_title' => array( 'ar' => 'إعدادات البريد والتواصل', 'en' => 'Email and contact settings' ),
		'mail_settings_intro' => array( 'ar' => 'تحكم في بيانات التواصل وأنواع الرسائل التي يسمح للموقع بإرسالها.', 'en' => 'Manage public contact details and the email categories the site may send.' ),
		'mail_system_data' => array( 'ar' => 'بيانات الإرسال والاستقبال', 'en' => 'Sending and receiving details' ),
		'mail_public_contact' => array( 'ar' => 'بيانات التواصل الظاهرة للزوار', 'en' => 'Public contact details' ),
		'whatsapp_label_ar_field' => array( 'ar' => 'نص زر واتساب بالعربية', 'en' => 'Arabic WhatsApp button text' ),
		'whatsapp_label_en_field' => array( 'ar' => 'نص زر واتساب بالإنجليزية', 'en' => 'English WhatsApp button text' ),
		'mail_preview_title' => array( 'ar' => 'معاينة بيانات التواصل', 'en' => 'Contact details preview' ),
		'mail_preview_contact' => array( 'ar' => 'تواصل معنا', 'en' => 'Contact us' ),
		'mail_preview_notifications' => array( 'ar' => 'الإشعارات المفعّلة', 'en' => 'Enabled notifications' ),
		'sender_name' => array( 'ar' => 'اسم مرسل الرسائل', 'en' => 'Email sender name' ),
		'admin_email' => array( 'ar' => 'البريد المستلم للرسائل', 'en' => 'Recipient email' ),
		'from_general_settings' => array( 'ar' => 'تُستخدم هذه القيمة تلقائيًا من الإعدادات العامة في WordPress.', 'en' => 'This value is used automatically from WordPress General Settings.' ),
		'phone_number' => array( 'ar' => 'رقم التواصل', 'en' => 'Contact number' ),
		'whatsapp_number' => array( 'ar' => 'رقم WhatsApp', 'en' => 'WhatsApp number' ),
		'whatsapp_label' => array( 'ar' => 'نص زر WhatsApp', 'en' => 'WhatsApp button text' ),
		'notification_controls' => array( 'ar' => 'التحكم في إشعارات البريد', 'en' => 'Email notification controls' ),
		'notify_contact' => array( 'ar' => 'رسائل نموذج التواصل', 'en' => 'Contact form messages' ),
		'notify_bookings' => array( 'ar' => 'إشعارات الحجوزات', 'en' => 'Booking notifications' ),
		'notify_products' => array( 'ar' => 'إشعارات طلبات المنتجات', 'en' => 'Digital product notifications' ),
		'notify_admin' => array( 'ar' => 'الإشعارات الإدارية الصادرة من الموقع', 'en' => 'Administrative site notifications' ),
		'toggle_on' => array( 'ar' => 'مفعّل', 'en' => 'On' ),
		'toggle_off' => array( 'ar' => 'متوقف', 'en' => 'Off' ),
	);
	$lang = 'en' === $lang ? 'en' : 'ar';
	return (string) ( $texts[ $key ][ $lang ] ?? $texts[ $key ]['ar'] ?? $key );
}

function vava_contact_render_text_field( string $name, string $value, string $label, string $preview, string $type = 'text' ): void {
	$id = sanitize_html_class( ltrim( $name, '_' ) );
	?>
	<div class="vava-field<?php echo 'textarea' === $type ? ' vava-field-full' : ''; ?>">
		<label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
		<?php if ( 'textarea' === $type ) : ?><textarea class="widefat" data-contact-preview="<?php echo esc_attr( $preview ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="5"><?php echo esc_textarea( $value ); ?></textarea><?php else : ?><input class="widefat" data-contact-preview="<?php echo esc_attr( $preview ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>"/><?php endif; ?>
	</div>
	<?php
}

function vava_contact_render_hero_fields( int $post_id, string $lang ): void {
	$data = vava_contact_text_data( $post_id, $lang );
	$hero = (array) $data['hero'];
	$pre  = '_vava_contact_' . $lang . '_hero_';
	vava_contact_render_text_field( $pre . 'eyebrow', (string) $hero['eyebrow'], vava_contact_admin_text( 'hero_eyebrow', $lang ), 'hero-eyebrow' );
	vava_contact_render_text_field( $pre . 'title', (string) $hero['title'], vava_contact_admin_text( 'hero_title', $lang ), 'hero-title' );
	vava_contact_render_text_field( $pre . 'intro', (string) $hero['intro'], vava_contact_admin_text( 'hero_intro', $lang ), 'hero-intro', 'textarea' );
	vava_contact_render_text_field( $pre . 'note', (string) $hero['note'], vava_contact_admin_text( 'hero_note', $lang ), 'hero-note', 'textarea' );
}

function vava_contact_form_copy_options( string $lang ): array {
	return 'en' === $lang ? array(
		'title' => 'Form title', 'field:name' => 'Name field label', 'field:email' => 'Email field label', 'field:subject' => 'Message subject field label', 'field:message' => 'Message field label',
		'submit_label' => 'Submit button text', 'social_eyebrow' => 'Social links label', 'hold_idle' => 'Hold prompt', 'hold_active' => 'Text while holding',
		'hold_verified' => 'Verified text', 'hold_error' => 'Hold verification error', 'email_invalid' => 'Invalid email message',
		'success' => 'Success message', 'error' => 'Failure message',
	) : array(
		'title' => 'عنوان النموذج', 'field:name' => 'عنوان حقل الاسم', 'field:email' => 'عنوان حقل البريد', 'field:subject' => 'عنوان حقل موضوع الرسالة', 'field:message' => 'عنوان حقل الرسالة',
		'submit_label' => 'نص زر الإرسال', 'social_eyebrow' => 'عبارة روابط التواصل', 'hold_idle' => 'نص الضغط المطول', 'hold_active' => 'النص أثناء الضغط',
		'hold_verified' => 'نص اكتمال التحقق', 'hold_error' => 'رسالة تعذر التحقق', 'email_invalid' => 'رسالة البريد غير الصحيح',
		'success' => 'رسالة نجاح الإرسال', 'error' => 'رسالة فشل الإرسال',
	);
}

function vava_contact_render_form_hold_controls( array $shared, string $lang ): void {
	$is_en      = 'en' === $lang;
	$is_primary = 'ar' === $lang;
	$enabled    = ! empty( $shared['hold_enabled'] );
	$toggle_id  = 'vava_contact_hold_enabled_' . $lang;
	?>
	<div class="vava-contact-copy-hold" data-contact-copy-hold>
		<div class="vava-contact-toggle-row<?php echo $enabled ? ' is-enabled' : ' is-disabled'; ?>" data-vava-toggle-row>
			<div><strong><?php echo esc_html( vava_contact_admin_text( 'hold_enabled', $lang ) ); ?></strong><p><?php echo esc_html( $is_en ? 'Adds a verification step before the message is sent.' : 'يضيف خطوة تحقق قبل إرسال الرسالة.' ); ?></p></div>
			<label class="vava-contact-toggle" for="<?php echo esc_attr( $toggle_id ); ?>">
				<input id="<?php echo esc_attr( $toggle_id ); ?>" <?php echo $is_primary ? 'name="_vava_contact_hold_enabled" ' : ''; ?>type="checkbox" value="1" <?php checked( $enabled ); ?> data-vava-toggle data-vava-hold-enabled-control<?php echo $is_primary ? ' data-vava-hold-primary' : ''; ?>/>
				<span class="vava-contact-toggle-track" aria-hidden="true"><i></i></span>
				<em data-toggle-label-on-ar="<?php echo esc_attr( vava_contact_admin_text( 'toggle_on', 'ar' ) ); ?>" data-toggle-label-off-ar="<?php echo esc_attr( vava_contact_admin_text( 'toggle_off', 'ar' ) ); ?>" data-toggle-label-on-en="<?php echo esc_attr( vava_contact_admin_text( 'toggle_on', 'en' ) ); ?>" data-toggle-label-off-en="<?php echo esc_attr( vava_contact_admin_text( 'toggle_off', 'en' ) ); ?>"><?php echo esc_html( $enabled ? vava_contact_admin_text( 'toggle_on', $lang ) : vava_contact_admin_text( 'toggle_off', $lang ) ); ?></em>
			</label>
		</div>
		<label class="vava-contact-hold-duration<?php echo $enabled ? '' : ' is-hidden'; ?>" data-contact-hold-duration><span><?php echo esc_html( vava_contact_admin_text( 'hold_duration', $lang ) ); ?></span><input max="8" min="3" <?php echo $is_primary ? 'name="_vava_contact_hold_duration" ' : ''; ?>type="number" value="<?php echo esc_attr( (string) $shared['hold_duration'] ); ?>" data-vava-hold-duration-control<?php echo $is_primary ? ' data-vava-hold-duration-primary' : ''; ?>/></label>
	</div>
	<?php
}

function vava_contact_render_form_fields( int $post_id, string $lang ): void {
	$data   = vava_contact_text_data( $post_id, $lang );
	$form   = (array) $data['form'];
	$shared = vava_contact_shared_data( $post_id );
	$schema = $shared['field_schema'];
	$copy   = array( 'title', 'submit_label', 'social_eyebrow', 'hold_idle', 'hold_active', 'hold_verified', 'hold_error', 'email_invalid', 'success', 'error' );
	?>
	<div class="vava-contact-form-copy-card vava-field-full" data-contact-copy-editor data-language="<?php echo esc_attr( $lang ); ?>">
		<div class="vava-contact-card-heading"><div><h3><?php echo esc_html( vava_contact_admin_text( 'copy_settings', $lang ) ); ?></h3><p><?php echo esc_html( 'en' === $lang ? 'Choose any interface text or status message, then edit it from one focused field.' : 'اختر أي نص أو رسالة حالة، ثم عدّلها من حقل واحد منظم.' ); ?></p></div></div>
		<?php vava_contact_render_form_hold_controls( $shared, $lang ); ?>
		<div class="vava-contact-selector-row"><label><strong><?php echo esc_html( vava_contact_admin_text( 'copy_choose', $lang ) ); ?></strong><select class="widefat vava-contact-styled-select" data-contact-copy-select><?php foreach ( vava_contact_form_copy_options( $lang ) as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></label><label><strong><?php echo esc_html( vava_contact_admin_text( 'copy_value', $lang ) ); ?></strong><input class="widefat" data-contact-copy-input-short type="text"/><textarea class="widefat" data-contact-copy-input-long hidden rows="5"></textarea></label></div>
		<?php foreach ( $copy as $key ) : ?><input data-contact-copy-value="<?php echo esc_attr( $key ); ?>" name="_vava_contact_<?php echo esc_attr( $lang ); ?>_form_<?php echo esc_attr( $key ); ?>" type="hidden" value="<?php echo esc_attr( (string) ( $form[ $key ] ?? '' ) ); ?>"/><?php endforeach; ?>
	</div>
	<div class="vava-contact-builder-card vava-field-full" data-contact-builder-wrap data-language="<?php echo esc_attr( $lang ); ?>">
		<div class="vava-contact-card-heading"><div><h3><?php echo esc_html( vava_contact_admin_text( 'field_builder', $lang ) ); ?></h3><p><?php echo esc_html( 'en' === $lang ? 'Core fields stay protected. Drag only added fields from their grip and place them before the message field.' : 'الحقول الأساسية محمية. اسحب الحقول الإضافية فقط من المقبض وضعها قبل حقل الرسالة.' ); ?></p></div><button class="button button-secondary vava-contact-add-button" data-contact-add-field type="button"><span aria-hidden="true">＋</span><?php echo esc_html( vava_contact_admin_text( 'add_field', $lang ) ); ?></button></div>
		<div class="vava-contact-field-builder" data-contact-builder></div>
		<textarea hidden name="_vava_contact_<?php echo esc_attr( $lang ); ?>_field_texts_json" data-contact-field-texts-json><?php echo esc_textarea( wp_json_encode( $form['field_texts'] ?? array(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></textarea>
		<script type="application/json" data-contact-initial-schema><?php echo wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
	</div>
	<?php
}

function vava_contact_render_guide_fields( int $post_id, string $lang ): void {
	$data   = vava_contact_text_data( $post_id, $lang );
	$guide  = (array) $data['guide'];
	$shared = vava_contact_shared_data( $post_id );
	$pre    = '_vava_contact_' . $lang . '_guide_';
	vava_contact_render_text_field( $pre . 'eyebrow', (string) $guide['eyebrow'], vava_contact_admin_text( 'guide_eyebrow', $lang ), 'guide-eyebrow' );
	vava_contact_render_text_field( $pre . 'title', (string) $guide['title'], vava_contact_admin_text( 'guide_title', $lang ), 'guide-title' );
	vava_contact_render_text_field( $pre . 'intro', (string) $guide['intro'], vava_contact_admin_text( 'guide_intro', $lang ), 'guide-intro', 'textarea' );
	?>
	<div class="vava-contact-guide-builder-card vava-field-full" data-contact-guide-builder-wrap data-language="<?php echo esc_attr( $lang ); ?>">
		<div class="vava-contact-card-heading"><div><h3><?php echo esc_html( vava_contact_admin_text( 'guide_builder', $lang ) ); ?></h3><p><?php echo esc_html( vava_contact_admin_text( 'guide_builder_help', $lang ) ); ?></p></div><button class="button button-secondary vava-contact-add-button" data-contact-add-guide-card type="button"><span aria-hidden="true">＋</span><?php echo esc_html( vava_contact_admin_text( 'add_guide_card', $lang ) ); ?></button></div>
		<div class="vava-contact-guide-builder" data-contact-guide-builder></div>
		<textarea hidden name="_vava_contact_<?php echo esc_attr( $lang ); ?>_guide_cards_json" data-contact-guide-texts-json><?php echo esc_textarea( wp_json_encode( $guide['cards'] ?? array(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></textarea>
		<?php if ( 'ar' === $lang ) : ?><textarea hidden name="_vava_contact_guide_schema_json" data-contact-guide-schema-json><?php echo esc_textarea( wp_json_encode( $shared['guide_card_schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></textarea><?php else : ?><textarea hidden data-contact-guide-schema-json><?php echo esc_textarea( wp_json_encode( $shared['guide_card_schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></textarea><?php endif; ?>
	</div>
	<?php
}

function vava_contact_render_media_field( int $attachment_id ): void {
	$current_url = vava_contact_image_url( $attachment_id, 'assets/images/contact-hero.png', 'medium_large' );
	$fallback    = vava_asset_uri( 'assets/images/contact-hero.png' );
	?>
	<div class="vava-admin-field vava-admin-field-media vava-admin-field-wide vava-contact-media-field" data-contact-media-field data-fallback-url="<?php echo esc_url( $fallback ); ?>"><label for="vava_contact_hero_image_id"><strong<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'hero_image', 'ar' ), vava_contact_admin_text( 'hero_image', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'hero_image', 'ar' ) ); ?></strong></label><div class="vava-media-field" data-media-type="image"><input class="vava-media-id" data-contact-media-id data-media-url="<?php echo esc_url( $current_url ); ?>" id="vava_contact_hero_image_id" name="_vava_contact_hero_image_id" type="hidden" value="<?php echo esc_attr( (string) $attachment_id ); ?>"/><div class="vava-media-dropzone" role="button" tabindex="0"><div class="vava-media-preview"><img alt="" src="<?php echo esc_url( $current_url ); ?>"/></div></div><div class="vava-media-actions"><button class="button vava-media-select" type="button"<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'choose_replace', 'ar' ), vava_contact_admin_text( 'choose_replace', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'choose_replace', 'ar' ) ); ?></button><button class="button vava-media-remove" type="button"<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'delete_file', 'ar' ), vava_contact_admin_text( 'delete_file', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'delete_file', 'ar' ) ); ?></button></div></div></div>
	<?php
}

function vava_contact_social_items(): array {
	$home_id = absint( get_option( 'page_on_front' ) );
	return $home_id && function_exists( 'vava_home_footer_social' ) ? vava_home_footer_social( $home_id ) : array();
}

function vava_contact_render_social_icons( bool $links = true ): void {
	foreach ( vava_contact_social_items() as $item ) {
		$platform = sanitize_key( (string) ( $item['platform'] ?? '' ) );
		$href     = function_exists( 'vava_home_social_href' ) ? vava_home_social_href( (array) $item ) : '';
		$label    = function_exists( 'vava_home_social_label' ) ? vava_home_social_label( $platform ) : ucfirst( $platform );
		$icon     = function_exists( 'vava_home_social_icon_svg' ) ? vava_home_social_icon_svg( $platform ) : '';
		if ( '' === $icon ) { continue; }
		if ( $links && $href ) {
			$external = 0 === strpos( $href, 'http://' ) || 0 === strpos( $href, 'https://' );
			printf( '<a aria-label="%1$s" href="%2$s"%3$s title="%1$s"><i>%4$s</i></a>', esc_attr( $label ), esc_url( $href, array( 'http', 'https', 'mailto', 'tel' ) ), $external ? ' rel="noopener noreferrer" target="_blank"' : '', $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			printf( '<i aria-label="%1$s" title="%1$s">%2$s</i>', esc_attr( $label ), $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}

function vava_contact_render_preview_form_fields( array $schema, array $texts ): void {
	foreach ( $schema as $field ) {
		if ( empty( $field['visible'] ) ) { continue; }
		$id   = (string) $field['id'];
		$copy = (array) ( $texts[ $id ] ?? array() );
		?><label class="<?php echo 'full' === $field['width'] ? 'is-wide ' : ''; ?><?php echo 'textarea' === $field['type'] ? 'is-message' : ''; ?>" data-preview-field-id="<?php echo esc_attr( $id ); ?>"><span><?php echo esc_html( (string) ( $copy['label'] ?? '' ) ); ?></span><i></i></label><?php
	}
}

function vava_contact_render_preview( WP_Post $post, string $section, string $lang ): void {
	$text   = vava_contact_text_data( (int) $post->ID, $lang );
	$shared = vava_contact_shared_data( (int) $post->ID );
	$is_en  = 'en' === $lang;
	?>
	<aside class="vava-live-preview" data-preview-language="<?php echo esc_attr( $lang ); ?>" data-preview-section="<?php echo esc_attr( $section ); ?>" data-contact-preview-panel dir="<?php echo $is_en ? 'ltr' : 'rtl'; ?>"><header class="vava-live-preview-header"><div><strong><?php echo esc_html( vava_contact_admin_text( 'live_preview', $lang ) ); ?></strong><span><?php echo esc_html( vava_contact_sections( $lang )[ $section ] ?? '' ); ?></span></div><span class="vava-live-preview-dot" aria-hidden="true"></span></header><div class="vava-preview-viewport"><div class="vava-preview-stage"><div class="vava-preview-canvas vava-contact-preview vava-contact-preview-<?php echo esc_attr( $section ); ?>" data-preview-design-width="900">
	<?php if ( 'hero' === $section ) : ?><div class="vava-contact-preview-hero-copy"><span data-preview-output="hero-eyebrow"><?php echo esc_html( (string) $text['hero']['eyebrow'] ); ?></span><h3 data-preview-output="hero-title"><?php echo esc_html( (string) $text['hero']['title'] ); ?></h3><p data-preview-output="hero-intro"><?php echo esc_html( (string) $text['hero']['intro'] ); ?></p><small data-preview-output="hero-note"><?php echo esc_html( (string) $text['hero']['note'] ); ?></small></div><div class="vava-contact-preview-hero-image" data-preview-image="hero" style="background-image:url('<?php echo esc_url( vava_contact_image_url( (int) $shared['hero_image_id'], 'assets/images/contact-hero.png', 'medium_large' ) ); ?>')"></div>
	<?php elseif ( 'form' === $section ) : ?><div class="vava-contact-preview-form-card"><h3 data-preview-output="form-title"><?php echo esc_html( (string) $text['form']['title'] ); ?></h3><div class="vava-contact-preview-form-grid" data-contact-preview-fields><?php vava_contact_render_preview_form_fields( $shared['field_schema'], $text['form']['field_texts'] ); ?></div><?php if ( $shared['hold_enabled'] ) : ?><div class="vava-contact-preview-hold"><b data-preview-output="form-hold-idle"><?php echo esc_html( (string) $text['form']['hold_idle'] ); ?></b><span></span></div><?php endif; ?><button type="button" data-preview-output="form-submit-label"><?php echo esc_html( (string) $text['form']['submit_label'] ); ?></button><div class="vava-contact-preview-statuses"><p class="is-success" data-preview-output="form-success"><?php echo esc_html( (string) $text['form']['success'] ); ?></p><p class="is-error" data-preview-output="form-error"><?php echo esc_html( (string) $text['form']['error'] ); ?></p></div><div class="vava-contact-preview-social"><b data-preview-output="form-social-eyebrow"><?php echo esc_html( (string) $text['form']['social_eyebrow'] ); ?></b><div class="social"><?php vava_contact_render_social_icons( false ); ?></div></div></div>
	<?php else : ?><div class="vava-contact-preview-guide"><span data-preview-output="guide-eyebrow"><?php echo esc_html( (string) $text['guide']['eyebrow'] ); ?></span><h3 data-preview-output="guide-title"><?php echo esc_html( (string) $text['guide']['title'] ); ?></h3><p data-preview-output="guide-intro"><?php echo esc_html( (string) $text['guide']['intro'] ); ?></p><div class="vava-contact-preview-guide-list" data-contact-preview-guide-cards><?php foreach ( vava_contact_guide_cards_for_display( $text, $shared ) as $card ) : ?><article data-preview-guide-id="<?php echo esc_attr( $card['id'] ); ?>"><strong><?php echo esc_html( $card['title'] ); ?></strong><p><?php echo esc_html( $card['body'] ); ?></p></article><?php endforeach; ?></div></div><?php endif; ?>
	</div></div></div></aside>
	<?php
}


function vava_contact_render_toggle( string $name, bool $enabled, string $label_ar, string $label_en, string $description_ar = '', string $description_en = '' ): void {
	$id = sanitize_html_class( ltrim( $name, '_' ) );
	?>
	<div class="vava-contact-toggle-row" data-vava-toggle-row>
		<div><strong<?php echo vava_admin_i18n_attributes( $label_ar, $label_en ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $label_ar ); ?></strong><?php if ( $description_ar || $description_en ) : ?><p<?php echo vava_admin_i18n_attributes( $description_ar, $description_en ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $description_ar ); ?></p><?php endif; ?></div>
		<label class="vava-contact-toggle" for="<?php echo esc_attr( $id ); ?>">
			<input id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="checkbox" value="1" <?php checked( $enabled ); ?> data-vava-toggle/>
			<span class="vava-contact-toggle-track" aria-hidden="true"><i></i></span>
			<em data-toggle-label-on-ar="<?php echo esc_attr( vava_contact_admin_text( 'toggle_on', 'ar' ) ); ?>" data-toggle-label-off-ar="<?php echo esc_attr( vava_contact_admin_text( 'toggle_off', 'ar' ) ); ?>" data-toggle-label-on-en="<?php echo esc_attr( vava_contact_admin_text( 'toggle_on', 'en' ) ); ?>" data-toggle-label-off-en="<?php echo esc_attr( vava_contact_admin_text( 'toggle_off', 'en' ) ); ?>"><?php echo esc_html( $enabled ? vava_contact_admin_text( 'toggle_on', 'ar' ) : vava_contact_admin_text( 'toggle_off', 'ar' ) ); ?></em>
		</label>
	</div>
	<?php
}

function vava_contact_render_mail_preview( int $post_id, string $lang ): void {
	$settings = vava_contact_mail_settings( $post_id );
	$is_en    = 'en' === $lang;
	$channels = array(
		'contact'  => vava_contact_admin_text( 'notify_contact', $lang ),
		'bookings' => vava_contact_admin_text( 'notify_bookings', $lang ),
		'products' => vava_contact_admin_text( 'notify_products', $lang ),
		'admin'    => vava_contact_admin_text( 'notify_admin', $lang ),
	);
	?>
	<aside class="vava-live-preview" data-preview-language="<?php echo esc_attr( $lang ); ?>" data-preview-section="mail" data-contact-preview-panel dir="<?php echo $is_en ? 'ltr' : 'rtl'; ?>"><header class="vava-live-preview-header"><div><strong><?php echo esc_html( vava_contact_admin_text( 'live_preview', $lang ) ); ?></strong><span><?php echo esc_html( vava_contact_sections( $lang )['mail'] ); ?></span></div><span class="vava-live-preview-dot" aria-hidden="true"></span></header><div class="vava-preview-viewport"><div class="vava-preview-stage"><div class="vava-preview-canvas vava-contact-preview vava-contact-preview-mail" data-preview-design-width="900">
		<div class="vava-contact-mail-preview-card"><small>VAVA MAIL</small><h3><?php echo esc_html( vava_contact_admin_text( 'mail_preview_title', $lang ) ); ?></h3><div class="vava-contact-mail-preview-phone"><span><?php echo esc_html( vava_contact_admin_text( 'phone_number', $lang ) ); ?></span><strong dir="ltr" data-mail-preview-phone-output><?php echo esc_html( (string) $settings['phone'] ); ?></strong></div><button type="button" data-mail-preview-whatsapp-output><?php echo esc_html( (string) $settings[ $is_en ? 'whatsapp_label_en' : 'whatsapp_label_ar' ] ); ?></button><a href="#" onclick="return false;"><?php echo esc_html( vava_contact_admin_text( 'mail_preview_contact', $lang ) ); ?></a><div class="vava-contact-mail-preview-notifications"><b><?php echo esc_html( vava_contact_admin_text( 'mail_preview_notifications', $lang ) ); ?></b><?php foreach ( $channels as $channel => $label ) : ?><p data-mail-preview-channel="<?php echo esc_attr( $channel ); ?>"><span><?php echo esc_html( $label ); ?></span><em data-mail-preview-channel-state data-toggle-label-on-ar="<?php echo esc_attr( vava_contact_admin_text( 'toggle_on', 'ar' ) ); ?>" data-toggle-label-off-ar="<?php echo esc_attr( vava_contact_admin_text( 'toggle_off', 'ar' ) ); ?>" data-toggle-label-on-en="<?php echo esc_attr( vava_contact_admin_text( 'toggle_on', 'en' ) ); ?>" data-toggle-label-off-en="<?php echo esc_attr( vava_contact_admin_text( 'toggle_off', 'en' ) ); ?>"><?php echo esc_html( ! empty( $settings[ 'notify_' . $channel ] ) ? vava_contact_admin_text( 'toggle_on', $lang ) : vava_contact_admin_text( 'toggle_off', $lang ) ); ?></em></p><?php endforeach; ?></div></div>
	</div></div></div></aside>
	<?php
}

function vava_contact_render_mail_settings( int $post_id ): void {
	$settings   = vava_contact_mail_settings( $post_id );
	$site_name  = vava_mail_sender_name();
	$admin_mail = vava_mail_admin_recipient();
	?>
	<div class="vava-contact-mail-settings" data-vava-mail-settings dir="rtl">
		<header><small>VAVA MAIL</small><h2<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'mail_settings_title', 'ar' ), vava_contact_admin_text( 'mail_settings_title', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'mail_settings_title', 'ar' ) ); ?></h2><p<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'mail_settings_intro', 'ar' ), vava_contact_admin_text( 'mail_settings_intro', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'mail_settings_intro', 'ar' ) ); ?></p></header>
		<div class="vava-contact-mail-grid">
			<section class="vava-contact-mail-card is-system">
				<h3<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'mail_system_data', 'ar' ), vava_contact_admin_text( 'mail_system_data', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'mail_system_data', 'ar' ) ); ?></h3>
				<div class="vava-contact-readonly-field"><span<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'sender_name', 'ar' ), vava_contact_admin_text( 'sender_name', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'sender_name', 'ar' ) ); ?></span><strong><?php echo esc_html( $site_name ); ?></strong><small<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'from_general_settings', 'ar' ), vava_contact_admin_text( 'from_general_settings', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'from_general_settings', 'ar' ) ); ?></small></div>
				<div class="vava-contact-readonly-field"><span<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'admin_email', 'ar' ), vava_contact_admin_text( 'admin_email', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'admin_email', 'ar' ) ); ?></span><strong dir="ltr"><?php echo esc_html( $admin_mail ?: '—' ); ?></strong><small<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'from_general_settings', 'ar' ), vava_contact_admin_text( 'from_general_settings', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'from_general_settings', 'ar' ) ); ?></small></div>
			</section>
			<section class="vava-contact-mail-card">
				<h3<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'mail_public_contact', 'ar' ), vava_contact_admin_text( 'mail_public_contact', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'mail_public_contact', 'ar' ) ); ?></h3>
				<label><span<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'phone_number', 'ar' ), vava_contact_admin_text( 'phone_number', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'phone_number', 'ar' ) ); ?></span><input class="widefat" dir="ltr" name="_vava_contact_mail_phone" type="text" value="<?php echo esc_attr( (string) $settings['phone'] ); ?>" placeholder="+966 11 123 4567" data-mail-preview-phone-input/></label>
				<label><span<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'whatsapp_number', 'ar' ), vava_contact_admin_text( 'whatsapp_number', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'whatsapp_number', 'ar' ) ); ?></span><input class="widefat" dir="ltr" name="_vava_contact_mail_whatsapp" type="text" value="<?php echo esc_attr( (string) $settings['whatsapp'] ); ?>" placeholder="+966 5X XXX XXXX"/></label>
				<div class="vava-contact-mail-language-fields">
					<label data-mail-language-field="ar"><span>نص زر واتساب</span><input class="widefat" dir="rtl" name="_vava_contact_mail_whatsapp_label_ar" type="text" value="<?php echo esc_attr( (string) $settings['whatsapp_label_ar'] ); ?>" data-mail-preview-whatsapp-label="ar"/></label>
					<label data-mail-language-field="en" hidden><span>WhatsApp button text</span><input class="widefat" dir="ltr" name="_vava_contact_mail_whatsapp_label_en" type="text" value="<?php echo esc_attr( (string) $settings['whatsapp_label_en'] ); ?>" data-mail-preview-whatsapp-label="en"/></label>
				</div>
			</section>
		</div>
		<section class="vava-contact-mail-card is-notifications">
			<h3<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'notification_controls', 'ar' ), vava_contact_admin_text( 'notification_controls', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'notification_controls', 'ar' ) ); ?></h3>
			<div class="vava-contact-toggle-list">
				<?php vava_contact_render_toggle( '_vava_contact_notify_contact', ! empty( $settings['notify_contact'] ), 'رسائل نموذج التواصل', 'Contact form messages', 'إرسال رسالة للإدارة ونسخة تأكيد للزائر.', 'Send the administration message and the visitor confirmation copy.' ); ?>
				<?php vava_contact_render_toggle( '_vava_contact_notify_bookings', ! empty( $settings['notify_bookings'] ), 'إشعارات الحجوزات', 'Booking notifications', 'رسائل الحجز والدفع وتحديث الحالة والاسترداد.', 'Booking, payment, status and refund emails.' ); ?>
				<?php vava_contact_render_toggle( '_vava_contact_notify_products', ! empty( $settings['notify_products'] ), 'إشعارات طلبات المنتجات', 'Digital product notifications', 'رسائل استلام الطلب والتفعيل والاسترداد.', 'Order receipt, activation and refund emails.' ); ?>
				<?php vava_contact_render_toggle( '_vava_contact_notify_admin', ! empty( $settings['notify_admin'] ), 'الإشعارات الإدارية الصادرة من الموقع', 'Administrative site notifications', 'التنبيهات العامة المرسلة إلى مدير الموقع.', 'General alerts sent to the site administrator.' ); ?>
			</div>
		</section>
	</div>
	<?php
}

function vava_contact_add_meta_boxes( string $post_type, WP_Post $post ): void {
	if ( 'page' !== $post_type || ! vava_contact_is_page( (int) $post->ID ) ) { return; }
	remove_meta_box( 'postdivrich', 'page', 'normal' );
	remove_meta_box( 'postimagediv', 'page', 'side' );
	add_meta_box( 'vava_homepage_settings', vava_contact_admin_text( 'meta_title', 'ar' ), 'vava_contact_render_settings', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'vava_contact_add_meta_boxes', 10, 2 );

function vava_contact_render_settings( WP_Post $post ): void {
	wp_nonce_field( 'vava_contact_save', 'vava_contact_nonce' );
	$sections_ar = vava_contact_sections( 'ar' );
	$sections_en = vava_contact_sections( 'en' );
	$shared      = vava_contact_shared_data( (int) $post->ID );
	?>
	<div class="vava-homepage-admin vava-contact-admin" data-active-language="ar" data-active-section="hero" data-settings-title-ar="<?php echo esc_attr( vava_contact_admin_text( 'meta_title', 'ar' ) ); ?>" data-settings-title-en="<?php echo esc_attr( vava_contact_admin_text( 'meta_title', 'en' ) ); ?>">
		<input data-vava-active-language-input name="_vava_admin_active_language" type="hidden" value="ar"/>
		<?php vava_render_bilingual_page_identity( $post, (string) get_permalink( $post ) ); ?>
		<div class="vava-admin-toolbar">
			<div class="vava-section-tabs" role="tablist">
				<?php foreach ( $sections_ar as $id => $label ) : ?><button aria-selected="<?php echo 'hero' === $id ? 'true' : 'false'; ?>" class="vava-section-tab<?php echo 'hero' === $id ? ' is-active' : ''; ?>" data-section="<?php echo esc_attr( $id ); ?>" role="tab" type="button"><span class="vava-tab-icon" aria-hidden="true"><?php echo vava_contact_section_icon( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span<?php echo vava_admin_i18n_attributes( $label, $sections_en[ $id ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $label ); ?></span></button><?php endforeach; ?>
			</div>
			<div class="vava-toolbar-actions"><div class="vava-language-switch" role="group"><button class="is-active" data-language="ar" type="button"><span>العربية</span><small>AR</small></button><button data-language="en" type="button"><span>English</span><small>EN</small></button></div><button class="button vava-homepage-update-button" data-vava-submit type="button"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20 12a8 8 0 1 1-2.35-5.65"/><path d="M20 4v6h-6"/></svg><span<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'update', 'ar' ), vava_contact_admin_text( 'update', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'update', 'ar' ) ); ?></span></button></div>
		</div>
		<div class="vava-section-panels">
			<?php foreach ( $sections_ar as $section => $label ) : ?>
				<section class="vava-section-panel<?php echo 'hero' === $section ? ' is-active' : ''; ?>" data-section-panel="<?php echo esc_attr( $section ); ?>">
					<?php if ( 'mail' === $section ) : ?>
						<?php vava_contact_render_mail_preview( (int) $post->ID, 'ar' ); ?>
						<?php vava_contact_render_mail_preview( (int) $post->ID, 'en' ); ?>
						<?php vava_contact_render_mail_settings( (int) $post->ID ); ?>
					<?php else : ?>
						<?php foreach ( array( 'ar', 'en' ) as $lang ) : ?>
							<div class="vava-language-pane<?php echo 'ar' === $lang ? ' is-active' : ''; ?>" data-language-pane="<?php echo esc_attr( $lang ); ?>" dir="<?php echo 'en' === $lang ? 'ltr' : 'rtl'; ?>">
								<div class="vava-editor-workspace">
									<?php vava_contact_render_preview( $post, $section, $lang ); ?>
									<div class="vava-editor-controls"><div class="vava-fields-grid"><?php if ( 'hero' === $section ) { vava_contact_render_hero_fields( (int) $post->ID, $lang ); } elseif ( 'form' === $section ) { vava_contact_render_form_fields( (int) $post->ID, $lang ); } else { vava_contact_render_guide_fields( (int) $post->ID, $lang ); } ?></div></div>
								</div>
							</div>
						<?php endforeach; ?>
						<?php if ( 'hero' === $section ) : ?>
							<div class="vava-shared-fields"><h3<?php echo vava_admin_i18n_attributes( vava_contact_admin_text( 'shared', 'ar' ), vava_contact_admin_text( 'shared', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_contact_admin_text( 'shared', 'ar' ) ); ?></h3><?php vava_contact_render_media_field( (int) $shared['hero_image_id'] ); ?></div>
						<?php elseif ( 'form' === $section ) : ?>
							<textarea hidden name="_vava_contact_field_schema_json" data-contact-schema-json><?php echo esc_textarea( wp_json_encode( $shared['field_schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></textarea>
						<?php endif; ?>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

function vava_contact_json_post( string $key ) {
	if ( ! isset( $_POST[ $key ] ) ) { return null; }
	$value = json_decode( wp_unslash( (string) $_POST[ $key ] ), true );
	return is_array( $value ) ? $value : null;
}

/* VAVA_CONTACT_GUIDE_CARD_PERSISTENCE_V2 */
function vava_contact_save_meta( int $post_id, WP_Post $post ): void {
	if ( ! isset( $_POST['vava_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vava_contact_nonce'] ) ), 'vava_contact_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $post_id ) || 'page' !== $post->post_type || ! vava_contact_is_page( $post_id ) || ! current_user_can( 'edit_page', $post_id ) ) { return; }
	vava_save_bilingual_page_titles( $post_id );

	$shared = vava_contact_shared_data( $post_id );
	$schema_json = vava_contact_json_post( '_vava_contact_field_schema_json' );
	if ( is_array( $schema_json ) ) { $shared['field_schema'] = vava_contact_sanitize_schema( $schema_json ); }
	$guide_schema_json = vava_contact_json_post( '_vava_contact_guide_schema_json' );
	if ( is_array( $guide_schema_json ) ) { $shared['guide_card_schema'] = vava_contact_sanitize_guide_schema( $guide_schema_json, $shared['field_schema'] ); }
	if ( isset( $_POST['_vava_contact_hero_image_id'] ) ) { $shared['hero_image_id'] = absint( $_POST['_vava_contact_hero_image_id'] ); }
	$shared['hold_enabled'] = isset( $_POST['_vava_contact_hold_enabled'] ) ? 1 : 0;
	$shared['hold_duration'] = isset( $_POST['_vava_contact_hold_duration'] ) ? max( 3, min( 8, absint( $_POST['_vava_contact_hold_duration'] ) ) ) : 4;
	update_post_meta( $post_id, '_vava_contact_shared', $shared );

	$mail_settings = vava_contact_mail_settings( $post_id );
	$mail_settings['phone']             = isset( $_POST['_vava_contact_mail_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['_vava_contact_mail_phone'] ) ) : (string) $mail_settings['phone'];
	$mail_settings['whatsapp']          = isset( $_POST['_vava_contact_mail_whatsapp'] ) ? sanitize_text_field( wp_unslash( $_POST['_vava_contact_mail_whatsapp'] ) ) : (string) $mail_settings['whatsapp'];
	$mail_settings['whatsapp_label_ar'] = isset( $_POST['_vava_contact_mail_whatsapp_label_ar'] ) ? sanitize_text_field( wp_unslash( $_POST['_vava_contact_mail_whatsapp_label_ar'] ) ) : (string) $mail_settings['whatsapp_label_ar'];
	$mail_settings['whatsapp_label_en'] = isset( $_POST['_vava_contact_mail_whatsapp_label_en'] ) ? sanitize_text_field( wp_unslash( $_POST['_vava_contact_mail_whatsapp_label_en'] ) ) : (string) $mail_settings['whatsapp_label_en'];
	foreach ( array( 'contact', 'bookings', 'products', 'admin' ) as $channel ) {
		$mail_settings[ 'notify_' . $channel ] = isset( $_POST[ '_vava_contact_notify_' . $channel ] ) ? 1 : 0;
	}
	update_post_meta( $post_id, '_vava_contact_mail_settings', $mail_settings );

	foreach ( array( 'ar', 'en' ) as $lang ) {
		$current = vava_contact_text_data( $post_id, $lang );
		$hero = array();
		foreach ( array( 'eyebrow', 'title', 'intro', 'note' ) as $field ) { $key = '_vava_contact_' . $lang . '_hero_' . $field; $hero[ $field ] = isset( $_POST[ $key ] ) ? ( in_array( $field, array( 'intro', 'note' ), true ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ) : (string) ( $current['hero'][ $field ] ?? '' ); }
		$form = (array) $current['form'];
		foreach ( array( 'title', 'submit_label', 'social_eyebrow', 'hold_idle', 'hold_active', 'hold_verified', 'hold_error', 'email_invalid' ) as $field ) { $key = '_vava_contact_' . $lang . '_form_' . $field; if ( isset( $_POST[ $key ] ) ) { $form[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $key ] ) ); } }
		foreach ( array( 'success', 'error' ) as $field ) { $key = '_vava_contact_' . $lang . '_form_' . $field; if ( isset( $_POST[ $key ] ) ) { $form[ $field ] = sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ); } }
		$field_texts_json = vava_contact_json_post( '_vava_contact_' . $lang . '_field_texts_json' );
		if ( is_array( $field_texts_json ) ) { $form['field_texts'] = vava_contact_sanitize_field_texts( $field_texts_json, $lang, $shared['field_schema'] ); }
		$form['name_label']    = (string) ( $form['field_texts']['name']['label'] ?? $form['name_label'] ?? '' );
		$form['email_label']   = (string) ( $form['field_texts']['email']['label'] ?? $form['email_label'] ?? '' );
		$form['subject_label'] = (string) ( $form['field_texts']['subject']['label'] ?? $form['subject_label'] ?? '' );
		$form['message_label'] = (string) ( $form['field_texts']['message']['label'] ?? $form['message_label'] ?? '' );
		$guide = array( 'eyebrow' => '', 'title' => '', 'intro' => '', 'cards' => array() );
		foreach ( array( 'eyebrow', 'title', 'intro' ) as $field ) { $key = '_vava_contact_' . $lang . '_guide_' . $field; $guide[ $field ] = isset( $_POST[ $key ] ) ? ( 'intro' === $field ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ) : (string) ( $current['guide'][ $field ] ?? '' ); }
		$guide_cards_json       = vava_contact_json_post( '_vava_contact_' . $lang . '_guide_cards_json' );
		$guide_cards_direct_key = '_vava_contact_' . $lang . '_guide_cards';
		$guide_cards_source     = is_array( $guide_cards_json ) ? $guide_cards_json : ( $current['guide']['cards'] ?? array() );

		// The visual card editor is rendered by JavaScript. Keep a normal named
		// input fallback as the authoritative source whenever it is present so a
		// stale cache, interrupted input event, or browser submission timing cannot
		// silently discard the card title/description.
		if ( isset( $_POST[ $guide_cards_direct_key ] ) && is_array( $_POST[ $guide_cards_direct_key ] ) ) {
			$direct_cards       = wp_unslash( $_POST[ $guide_cards_direct_key ] );
			$guide_cards_source = array();
			foreach ( $direct_cards as $direct_id => $direct_card ) {
				if ( ! is_array( $direct_card ) ) { continue; }
				$id = sanitize_key( (string) $direct_id );
				if ( '' === $id ) { continue; }
				$guide_cards_source[] = array(
					'id'    => $id,
					'title' => isset( $direct_card['title'] ) ? (string) $direct_card['title'] : '',
					'body'  => isset( $direct_card['body'] ) ? (string) $direct_card['body'] : '',
				);
			}
		}

		$guide['cards'] = vava_contact_sanitize_guide_texts( $guide_cards_source, $lang, $shared['guide_card_schema'] );
		update_post_meta( $post_id, vava_contact_text_meta_key( $lang ), array( 'hero' => $hero, 'form' => $form, 'guide' => $guide ) );
	}
}
add_action( 'save_post_page', 'vava_contact_save_meta', 30, 2 );

function vava_contact_use_block_editor( bool $use_block_editor, WP_Post $post ): bool { return vava_contact_is_page( (int) $post->ID ) ? false : $use_block_editor; }
add_filter( 'use_block_editor_for_post', 'vava_contact_use_block_editor', 10, 2 );

function vava_contact_admin_body_class( string $classes ): string {
	global $post;
	$post_id = $post instanceof WP_Post ? (int) $post->ID : ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $post_id && vava_contact_is_page( $post_id ) ) { $classes .= ' vava-homepage-classic vava-contact-classic'; }
	return $classes;
}
add_filter( 'admin_body_class', 'vava_contact_admin_body_class' );

function vava_contact_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) { return; }
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || ! vava_contact_is_page( $post_id ) ) { return; }
	wp_enqueue_media();
	wp_enqueue_style( 'vava-homepage-admin', get_theme_file_uri( 'assets/css/admin-homepage.css' ), array(), vava_asset_version( 'assets/css/admin-homepage.css' ) );
	wp_enqueue_style( 'vava-contact-admin', get_theme_file_uri( 'assets/css/admin-contact.css' ), array( 'vava-homepage-admin' ), vava_asset_version( 'assets/css/admin-contact.css' ) );
	wp_enqueue_script( 'vava-contact-admin', get_theme_file_uri( 'assets/js/admin-contact.js' ), array( 'jquery', 'jquery-ui-sortable' ), vava_asset_version( 'assets/js/admin-contact.js' ), true );
	wp_localize_script( 'vava-contact-admin', 'VAVA_CONTACT_ADMIN', array(
		'labels' => array(
			'ar' => array( 'label' => 'عنوان الحقل', 'placeholder' => 'النص الإرشادي', 'type' => 'نوع الحقل', 'width' => 'عرض الحقل', 'required' => 'إلزامي', 'visible' => 'ظاهر', 'options' => 'خيارات القائمة — خيار في كل سطر', 'protected' => 'حقل أساسي — غير قابل للحذف', 'delete' => 'حذف الحقل', 'half' => 'نصف صف', 'full' => 'صف كامل', 'text' => 'حقل نصي', 'tel' => 'رقم تواصل', 'select' => 'قائمة اختيار', 'textarea' => 'نص متعدد الأسطر' ),
			'en' => array( 'label' => 'Field label', 'placeholder' => 'Placeholder', 'type' => 'Field type', 'width' => 'Field width', 'required' => 'Required', 'visible' => 'Visible', 'options' => 'Select options — one per line', 'protected' => 'Core field — cannot be deleted', 'delete' => 'Delete field', 'half' => 'Half row', 'full' => 'Full row', 'text' => 'Text field', 'tel' => 'Phone field', 'select' => 'Select list', 'textarea' => 'Textarea' ),
		),
	) );
}
add_action( 'admin_enqueue_scripts', 'vava_contact_admin_assets' );

function vava_contact_remote_address(): string {
	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
}

function vava_contact_form_rate_key( string $bucket = 'cooldown' ): string {
	return 'vava_contact_' . sanitize_key( $bucket ) . '_' . md5( wp_salt( 'nonce' ) . '|' . vava_contact_remote_address() );
}

function vava_contact_attempt_blocked(): bool {
	return absint( get_transient( vava_contact_form_rate_key( 'attempts' ) ) ) >= 8 || (bool) get_transient( vava_contact_form_rate_key( 'cooldown' ) );
}

function vava_contact_record_attempt(): void {
	$key   = vava_contact_form_rate_key( 'attempts' );
	$count = absint( get_transient( $key ) );
	set_transient( $key, $count + 1, HOUR_IN_SECONDS );
}

function vava_contact_hold_key( string $kind, string $token ): string {
	return 'vava_contact_' . sanitize_key( $kind ) . '_' . md5( wp_salt( 'auth' ) . '|' . $token );
}

function vava_contact_hold_start(): void {
	$page_id = isset( $_POST['pageId'] ) ? absint( $_POST['pageId'] ) : 0;
	$nonce   = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! $page_id || ! vava_contact_is_page( $page_id ) || ! wp_verify_nonce( $nonce, 'vava_contact_hold_' . $page_id ) || vava_contact_attempt_blocked() ) {
		wp_send_json_error( array( 'message' => 'blocked' ), 403 );
	}
	$shared = vava_contact_shared_data( $page_id );
	if ( empty( $shared['hold_enabled'] ) ) { wp_send_json_success( array( 'disabled' => true ) ); }
	$challenge = wp_generate_password( 40, false, false );
	set_transient( vava_contact_hold_key( 'challenge', $challenge ), array( 'page' => $page_id, 'ip' => vava_contact_remote_address(), 'started' => microtime( true ) ), 2 * MINUTE_IN_SECONDS );
	wp_send_json_success( array( 'challenge' => $challenge, 'duration' => (int) $shared['hold_duration'] ) );
}
add_action( 'wp_ajax_vava_contact_hold_start', 'vava_contact_hold_start' );
add_action( 'wp_ajax_nopriv_vava_contact_hold_start', 'vava_contact_hold_start' );

function vava_contact_hold_verify(): void {
	$page_id   = isset( $_POST['pageId'] ) ? absint( $_POST['pageId'] ) : 0;
	$nonce     = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	$challenge = isset( $_POST['challenge'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', (string) wp_unslash( $_POST['challenge'] ) ) : '';
	if ( ! $page_id || ! vava_contact_is_page( $page_id ) || ! wp_verify_nonce( $nonce, 'vava_contact_hold_' . $page_id ) || '' === $challenge ) { wp_send_json_error( array( 'message' => 'invalid' ), 403 ); }
	$key  = vava_contact_hold_key( 'challenge', $challenge );
	$data = get_transient( $key );
	delete_transient( $key );
	$shared = vava_contact_shared_data( $page_id );
	if ( ! is_array( $data ) || (int) ( $data['page'] ?? 0 ) !== $page_id || (string) ( $data['ip'] ?? '' ) !== vava_contact_remote_address() || microtime( true ) - (float) ( $data['started'] ?? 0 ) < ( (int) $shared['hold_duration'] - 0.15 ) ) { wp_send_json_error( array( 'message' => 'too_fast' ), 403 ); }
	$token = wp_generate_password( 48, false, false );
	set_transient( vava_contact_hold_key( 'verified', $token ), array( 'page' => $page_id, 'ip' => vava_contact_remote_address(), 'issued' => time() ), 10 * MINUTE_IN_SECONDS );
	wp_send_json_success( array( 'token' => $token ) );
}
add_action( 'wp_ajax_vava_contact_hold_verify', 'vava_contact_hold_verify' );
add_action( 'wp_ajax_nopriv_vava_contact_hold_verify', 'vava_contact_hold_verify' );

function vava_contact_validate_hold_token( int $page_id, string $token ): bool {
	if ( '' === $token ) { return false; }
	$key  = vava_contact_hold_key( 'verified', $token );
	$data = get_transient( $key );
	delete_transient( $key );
	return is_array( $data ) && (int) ( $data['page'] ?? 0 ) === $page_id && (string) ( $data['ip'] ?? '' ) === vava_contact_remote_address();
}

function vava_contact_sanitize_submission( array $schema, array $texts, array $submitted, bool &$valid ): array {
	$values = array();
	$valid  = true;
	foreach ( $schema as $field ) {
		if ( empty( $field['visible'] ) ) { continue; }
		$id    = (string) $field['id'];
		$type  = (string) $field['type'];
		$raw   = isset( $submitted[ $id ] ) ? wp_unslash( $submitted[ $id ] ) : '';
		$value = 'textarea' === $type ? sanitize_textarea_field( (string) $raw ) : sanitize_text_field( (string) $raw );
		$limit = 'textarea' === $type ? 4000 : 300;
		if ( function_exists( 'mb_substr' ) ) { $value = mb_substr( $value, 0, $limit ); } else { $value = substr( $value, 0, $limit ); }
		if ( ! empty( $field['required'] ) && '' === trim( $value ) ) { $valid = false; }
		if ( 'email' === $type && ! is_email( $value ) ) { $valid = false; }
		if ( 'select' === $type ) {
			$options = array_map( 'strval', (array) ( $texts[ $id ]['options'] ?? array() ) );
			if ( '' !== $value && ! in_array( $value, $options, true ) ) { $valid = false; }
		}
		$values[ $id ] = $value;
	}
	return $values;
}

function vava_contact_email_html( int $page_id, string $lang, array $schema, array $texts, array $values, bool $sender_copy = false ): string {
	$is_en  = 'en' === $lang;
	$dir    = $is_en ? 'ltr' : 'rtl';
	$title  = $sender_copy
		? ( $is_en ? 'We received your message' : 'استلمنا رسالتك' )
		: ( $is_en ? 'A new message from the Contact page' : 'رسالة جديدة من صفحة تواصل معنا' );
	$intro  = $sender_copy
		? ( $is_en ? 'Thank you for contacting VAVA Living. Your message has reached our team, and we will get back to you as soon as possible. Below is a copy of the details you sent.' : 'شكرًا لتواصلك مع VAVA Living. وصلت رسالتك إلى فريقنا، وسنعود إليك في أقرب وقت ممكن. وهذه نسخة من التفاصيل التي أرسلتها.' )
		: ( $is_en ? 'The following details were submitted through the VAVA Living contact form.' : 'تم إرسال التفاصيل التالية من خلال نموذج التواصل في موقع VAVA Living.' );
	$footer = $sender_copy
		? ( $is_en ? 'Contact message receipt confirmation' : 'تأكيد استلام رسالة التواصل' )
		: ( $is_en ? 'Automated contact notification' : 'إشعار تلقائي من نموذج التواصل' );
	$rows   = '';
	foreach ( $schema as $field ) {
		if ( empty( $field['visible'] ) ) { continue; }
		$id    = (string) $field['id'];
		$label = (string) ( $texts[ $id ]['label'] ?? $id );
		$value = (string) ( $values[ $id ] ?? '' );
		if ( '' === $value ) { continue; }
		$rows .= '<div style="padding:16px 18px;border:1px solid #ece4d9;border-radius:14px;background:#fff;margin:0 0 10px"><div style="font-size:12px;color:#6f815f;font-weight:700;margin-bottom:7px">' . esc_html( $label ) . '</div><div style="font-size:15px;line-height:1.8;color:#514d43;white-space:pre-wrap">' . nl2br( esc_html( $value ) ) . '</div></div>';
	}
	$page_url = vava_localized_page_url( $page_id, $lang );
	$date     = wp_date( 'Y-m-d H:i', current_time( 'timestamp' ) );
	return '<!doctype html><html dir="' . esc_attr( $dir ) . '"><body style="margin:0;background:#f5f0e8;font-family:Arial,Tahoma,sans-serif;color:#514d43"><div style="max-width:720px;margin:30px auto;padding:0 16px"><div style="overflow:hidden;border:1px solid #e7ded1;border-radius:24px;background:#fbf8f2;box-shadow:0 18px 50px rgba(73,67,55,.08)"><div style="padding:30px;text-align:center;background:linear-gradient(135deg,#f6ede1,#eef1e9)"><div style="font-family:Georgia,serif;font-size:38px;letter-spacing:7px;color:#626446">VAVA</div><div style="font-size:11px;color:#9a8a73;margin-top:5px">LIVING</div></div><div style="padding:30px"><h1 style="margin:0 0 12px;text-align:center;font-family:Georgia,serif;font-size:28px;font-weight:500;color:#4b473b">' . esc_html( $title ) . '</h1><p style="margin:0 0 25px;text-align:center;color:#7b7469;line-height:1.8">' . esc_html( $intro ) . '</p>' . $rows . '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:18px"><div style="padding:14px;border-radius:12px;background:#f0f3ec"><strong style="display:block;font-size:11px;color:#6f815f">' . esc_html( $is_en ? 'Language' : 'اللغة' ) . '</strong><span>' . esc_html( $is_en ? 'English' : 'العربية' ) . '</span></div><div style="padding:14px;border-radius:12px;background:#f8ece7"><strong style="display:block;font-size:11px;color:#a45e4d">' . esc_html( $is_en ? 'Sent at' : 'وقت الإرسال' ) . '</strong><span>' . esc_html( $date ) . '</span></div></div><div style="margin-top:10px;padding:14px;border-radius:12px;background:#fff;border:1px solid #ece4d9"><strong style="display:block;font-size:11px;color:#6f815f;margin-bottom:6px">' . esc_html( $is_en ? 'Source page' : 'رابط الصفحة' ) . '</strong><a href="' . esc_url( $page_url ) . '" style="color:#cf7d65;text-decoration:none">' . esc_html( $page_url ) . '</a></div></div><div style="padding:18px;text-align:center;border-top:1px solid #e7ded1;color:#8a8278;font-size:12px">VAVA Living — ' . esc_html( $footer ) . '</div></div></div></body></html>';
}

function vava_contact_handle_form(): void {
	$page_id = isset( $_POST['vava_contact_page_id'] ) ? absint( $_POST['vava_contact_page_id'] ) : 0;
	$lang    = isset( $_POST['vava_contact_lang'] ) ? vava_normalize_language( sanitize_key( wp_unslash( $_POST['vava_contact_lang'] ) ) ) : 'ar';
	$url     = $page_id ? vava_localized_page_url( $page_id, $lang ) : home_url( '/' );
	$status  = 'error';
	$redirect = static function () use ( &$status, $url ): void { wp_safe_redirect( add_query_arg( 'vava_contact', $status, $url ) . '#contact-form' ); exit; };
	if ( ! $page_id || ! vava_contact_is_page( $page_id ) || ! isset( $_POST['vava_contact_form_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vava_contact_form_nonce'] ) ), 'vava_contact_form_' . $page_id ) ) { vava_contact_record_attempt(); $redirect(); }
	if ( ! empty( $_POST['company_website'] ) || vava_contact_attempt_blocked() ) { vava_contact_record_attempt(); $redirect(); }
	$loaded = isset( $_POST['vava_contact_loaded_at'] ) ? absint( $_POST['vava_contact_loaded_at'] ) : 0;
	if ( ! $loaded || time() - $loaded < 2 || $loaded > time() + MINUTE_IN_SECONDS ) { vava_contact_record_attempt(); $redirect(); }
	$shared = vava_contact_shared_data( $page_id );
	if ( ! empty( $shared['hold_enabled'] ) ) {
		$token = isset( $_POST['vava_contact_hold_token'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', (string) wp_unslash( $_POST['vava_contact_hold_token'] ) ) : '';
		if ( ! vava_contact_validate_hold_token( $page_id, $token ) ) { vava_contact_record_attempt(); $redirect(); }
	}
	$text      = vava_contact_text_data( $page_id, $lang );
	$submitted = isset( $_POST['vava_field'] ) && is_array( $_POST['vava_field'] ) ? $_POST['vava_field'] : array();
	$valid     = false;
	$values    = vava_contact_sanitize_submission( $shared['field_schema'], $text['form']['field_texts'], $submitted, $valid );
	if ( ! $valid ) { vava_contact_record_attempt(); $redirect(); }
	$name  = (string) ( $values['name'] ?? '' );
	$email = sanitize_email( (string) ( $values['email'] ?? '' ) );
	if ( '' === $name || ! is_email( $email ) || false !== strpos( $email, "\n" ) || false !== strpos( $email, "\r" ) ) { vava_contact_record_attempt(); $redirect(); }
	$duplicate_source = strtolower( $email ) . '|' . implode( '|', array_map( 'strval', $values ) );
	$duplicate_key    = 'vava_contact_duplicate_' . md5( wp_salt( 'secure_auth' ) . '|' . $duplicate_source );
	if ( get_transient( $duplicate_key ) ) { vava_contact_record_attempt(); $redirect(); }
	$mail_enabled = vava_mail_notifications_enabled( 'contact' );
	$recipient    = vava_mail_admin_recipient();
	if ( $mail_enabled && ! is_email( $recipient ) ) { vava_contact_record_attempt(); $redirect(); }
	$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$subject_value = trim( (string) ( $values['subject'] ?? '' ) );
	$mail_subject  = sprintf( '[%s] %s', $site_name ?: 'VAVA Living', $subject_value ?: ( 'en' === $lang ? 'New contact message' : 'رسالة تواصل جديدة' ) );
	$body          = vava_contact_email_html( $page_id, $lang, $shared['field_schema'], $text['form']['field_texts'], $values );
	$headers       = array( 'Content-Type: text/html; charset=UTF-8', 'Reply-To: ' . $email );
	$mail_sent = true;
	if ( $mail_enabled ) {
		$mail_sent = wp_mail( $recipient, $mail_subject, $body, $headers );
		if ( $mail_sent ) {
			$sender_subject = sprintf( '[%s] %s', $site_name ?: 'VAVA Living', 'en' === $lang ? 'We received your message' : 'استلمنا رسالتك' );
			wp_mail( $email, $sender_subject, vava_contact_email_html( $page_id, $lang, $shared['field_schema'], $text['form']['field_texts'], $values, true ), array( 'Content-Type: text/html; charset=UTF-8' ) );
		}
	}
	if ( $mail_sent ) {
		set_transient( vava_contact_form_rate_key( 'cooldown' ), 1, 45 );
		set_transient( $duplicate_key, 1, 10 * MINUTE_IN_SECONDS );
		$status = 'success';
	} else {
		vava_contact_record_attempt();
	}
	$redirect();
}
add_action( 'admin_post_vava_contact_submit', 'vava_contact_handle_form' );
add_action( 'admin_post_nopriv_vava_contact_submit', 'vava_contact_handle_form' );

function vava_contact_assign_or_create_page(): void {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'vava_contact_page_migrated_v1' ) ) { return; }
	$page = get_page_by_path( 'contact', OBJECT, 'page' );
	if ( ! $page ) {
		$page_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'تواصل معنا', 'post_name' => 'contact' ), true );
		$page = ! is_wp_error( $page_id ) ? get_post( $page_id ) : null;
	}
	if ( $page instanceof WP_Post ) {
		update_post_meta( (int) $page->ID, '_wp_page_template', vava_contact_template_slug() );
		update_post_meta( (int) $page->ID, vava_page_title_meta_key( 'ar' ), 'تواصل معنا' );
		update_post_meta( (int) $page->ID, vava_page_title_meta_key( 'en' ), 'Contact Us' );
	}
	update_option( 'vava_contact_page_migrated_v1', 1, false );
}
add_action( 'admin_init', 'vava_contact_assign_or_create_page', 40 );
