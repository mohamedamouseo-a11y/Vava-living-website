<?php
/**
 * Template Name: VAVA — Legal Page (AR / EN)
 * Template Post Type: page
 *
 * @package VAVA_Living
 */
defined( 'ABSPATH' ) || exit;
// VAVA_BOOKING_POLICY_PAGE_V1

$page_id = get_queried_object_id();
$lang    = vava_current_language();
$is_en   = 'en' === $lang;
$type    = vava_legal_normalize_type( vava_legal_page_type( $page_id ) );
$data    = vava_legal_text_data( $page_id, $lang );
$dir     = $is_en ? 'ltr' : 'rtl';

$extract_sections = static function ( string $html ): array {
	$html = trim( $html );
	if ( '' === $html ) {
		return array();
	}

	$matches = array();
	preg_match_all( '/<h2\b[^>]*>(.*?)<\/h2>/is', $html, $matches, PREG_OFFSET_CAPTURE );
	if ( empty( $matches[0] ) ) {
		return array(
			array(
				'title' => '',
				'body'  => $html,
			),
		);
	}

	$sections = array();
	$count    = count( $matches[0] );
	for ( $index = 0; $index < $count; $index++ ) {
		$heading_html = (string) $matches[0][ $index ][0];
		$heading_at   = (int) $matches[0][ $index ][1];
		$body_start   = $heading_at + strlen( $heading_html );
		$body_end     = $index + 1 < $count ? (int) $matches[0][ $index + 1 ][1] : strlen( $html );
		$title        = trim( wp_strip_all_tags( (string) $matches[1][ $index ][0] ) );
		$body         = trim( substr( $html, $body_start, max( 0, $body_end - $body_start ) ) );
		$sections[]   = array(
			'title' => $title,
			'body'  => $body,
		);
	}

	return $sections;
};

$sections = $extract_sections( (string) $data['content'] );
if ( ! $sections ) {
	$sections[] = array(
		'title' => (string) $data['title'],
		'body'  => '<p>' . esc_html( (string) $data['intro'] ) . '</p>',
	);
}

$copy = array(
	'ar' => array(
		'on_page'          => 'في هذه الصفحة',
		'commitment'       => 'التزامنا تجاهك',
		'commitment_text'  => 'نقدّم هذه الصفحة بلغة واضحة، ونحافظ على تحديث محتواها لتبقى حقوقك ومسؤولياتك مفهومة في كل وقت.',
		'protection'       => 'حمايتك أولًا',
		'protection_text'  => 'نلتزم بالشفافية والأمان واحترام حقوقك في كل ما نقدمه.',
		'privacy_title'    => 'خصوصيتك، راحة بالك',
		'terms_title'      => 'تجربة واضحة وموثوقة',
		'booking_title'    => 'موعدك بخطوات واضحة',
		'privacy_text'     => 'نؤمن أن الثقة تُبنى على الشفافية، ونسعى دائمًا إلى حماية بياناتك ومنحك تحكمًا واضحًا في اختياراتك.',
		'terms_text'       => 'نوضح القواعد والحقوق والمسؤوليات بلغة مباشرة حتى تكون تجربتك مع VAVA أكثر راحة وثقة.',
		'booking_text'     => 'نوضح مراحل الحجز والتأكيد والتعديل والإلغاء حتى تكون تفاصيل موعدك معروفة قبل الإرسال.',
		'principles'       => array(
			array( 'title' => 'شفافية تامة', 'text' => 'معلومات واضحة ومفهومة دون تعقيد.' ),
			array( 'title' => 'تحكم أفضل', 'text' => 'خيارات تساعدك على إدارة تجربتك بثقة.' ),
			array( 'title' => 'حماية مستمرة', 'text' => 'مراجعة دورية واهتمام دائم بالأمان.' ),
		),
		'contact_title'    => 'لأي استفسار حول هذه الصفحة',
		'contact_text'     => 'نحن هنا لمساعدتك والرد على أسئلتك بكل وضوح واهتمام.',
		'contact_button'   => 'تواصل معنا',
		'email_label'      => 'البريد الإلكتروني',
		'phone_label'      => 'رقم التواصل',
		'quote_privacy'    => 'نستخدم معلوماتك فقط بما يخدم تجربتك ويلبي احتياجاتك، وبما يتوافق مع الأنظمة المعمول بها.',
		'quote_terms'      => 'نحن هنا لنبني تجربة واضحة تقوم على الثقة والاحترام المتبادل.',
		'quote_booking'    => 'كل خطوة في الحجز مصممة لتمنحك وضوحًا أكبر قبل تأكيد موعدك.',
	),
	'en' => array(
		'on_page'          => 'On this page',
		'commitment'       => 'Our commitment to you',
		'commitment_text'  => 'We present this page in clear language and keep it updated so your rights and responsibilities remain easy to understand.',
		'protection'       => 'Protection first',
		'protection_text'  => 'We are committed to transparency, security and respect for your rights in everything we provide.',
		'privacy_title'    => 'Your privacy, your peace of mind',
		'terms_title'      => 'A clear and trusted experience',
		'booking_title'    => 'Your appointment, clearly planned',
		'privacy_text'     => 'Trust grows through transparency. We work to protect your information and give you clear control over your choices.',
		'terms_text'       => 'We explain rights, rules and responsibilities directly so your VAVA experience stays comfortable and trusted.',
		'booking_text'     => 'We explain booking, confirmation, changes and cancellation so appointment details are clear before submission.',
		'principles'       => array(
			array( 'title' => 'Clear transparency', 'text' => 'Straightforward information without unnecessary complexity.' ),
			array( 'title' => 'Better control', 'text' => 'Practical choices that help you manage your experience.' ),
			array( 'title' => 'Ongoing protection', 'text' => 'Regular review and continuous attention to security.' ),
		),
		'contact_title'    => 'Questions about this page?',
		'contact_text'     => 'We are here to help and answer your questions clearly and thoughtfully.',
		'contact_button'   => 'Contact us',
		'email_label'      => 'Email',
		'phone_label'      => 'Contact number',
		'quote_privacy'    => 'We use information only to support your experience and needs, in line with applicable requirements.',
		'quote_terms'      => 'We build this experience on clarity, trust and mutual respect.',
		'quote_booking'    => 'Every booking step is designed to give you greater clarity before your appointment is confirmed.',
	),
);
$labels = $copy[ $lang ];
$feature_key = in_array( $type, array( 'privacy', 'terms', 'booking' ), true ) ? $type : 'privacy';

$icon = static function ( string $name ): string {
	$paths = array(
		'shield'   => '<path d="M12 3 5.5 5.5v5.7c0 4.2 2.8 7.4 6.5 9.1 3.7-1.7 6.5-4.9 6.5-9.1V5.5L12 3Z"/><path d="m9.3 11.8 1.7 1.7 3.9-4"/>',
		'user'     => '<circle cx="12" cy="8" r="3.2"/><path d="M5.8 20c.7-4 2.9-6 6.2-6s5.5 2 6.2 6"/>',
		'lock'     => '<rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7.5a4 4 0 0 1 8 0V10"/>',
		'people'   => '<circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.4"/><path d="M3.5 20c.5-4.1 2.4-6.1 5.5-6.1 3.2 0 5.1 2 5.6 6.1M14 14.5c2.8-.4 4.9 1.2 5.7 4.6"/>',
		'cookie'   => '<path d="M20 12a8 8 0 1 1-8-8c.2 2.7 2.3 4.8 5 5 .2 1.6 1.4 2.8 3 3Z"/><circle cx="8.5" cy="10" r=".7"/><circle cx="11.5" cy="15" r=".7"/><circle cx="7.5" cy="16" r=".7"/>',
		'calendar' => '<rect x="4" y="5.5" width="16" height="14" rx="2"/><path d="M8 3v5M16 3v5M4 10h16"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>',
		'phone'    => '<path d="M7.2 3.8 10 8.1 8.2 10c1.1 2.5 3.1 4.5 5.6 5.6l1.9-1.8 4.4 2.7-.7 3.2c-.2.8-.9 1.4-1.8 1.4C9.6 20.5 3.5 14.4 2.9 6.4c0-.8.5-1.6 1.4-1.8l2.9-.8Z"/>',
		'whatsapp' => '<path d="M20.5 11.7a8.3 8.3 0 0 1-12.3 7.2L3.5 20l1.2-4.5A8.3 8.3 0 1 1 20.5 11.7Z"/><path d="M8.2 7.5c.3-.3.8-.2 1 .2l1 2c.2.3.1.7-.1.9l-.7.8c.8 1.6 2.1 2.9 3.8 3.7l.7-.8c.3-.3.7-.3 1-.1l2 1c.4.2.5.7.3 1-.5.9-1.5 1.5-2.5 1.3-4.6-.9-8.1-4.4-9-9-.2-1 .4-2 1.3-2.5.4-.2.9-.1 1.2.2Z"/>',
		'document' => '<path d="M7 3h7l4 4v14H7z"/><path d="M14 3v5h5M9.5 12h5M9.5 16h5"/>',
	);
	$path = $paths[ $name ] ?? $paths['document'];
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $path . '</svg>';
};

$section_icons = array( 'document', 'user', 'people', 'cookie', 'lock', 'shield', 'calendar', 'mail' );
$hero_image    = get_theme_file_uri( 'assets/images/contact-section-visual.jpg' );
$contact_url   = function_exists( 'vava_page_url' ) ? vava_page_url( 'contact' ) : home_url( '/' );
$contact_url   = function_exists( 'vava_language_url' ) ? vava_language_url( $lang, $contact_url ) : $contact_url;
$contact_settings  = function_exists( 'vava_contact_mail_settings' ) ? vava_contact_mail_settings() : array();
$contact_phone     = trim( (string) ( $contact_settings['phone'] ?? '' ) );
$contact_phone_uri = preg_replace( '/[^0-9+]/', '', $contact_phone );
$whatsapp_url      = function_exists( 'vava_contact_whatsapp_url' ) ? vava_contact_whatsapp_url( (string) ( $contact_settings['whatsapp'] ?? '' ) ) : '';
$whatsapp_label    = trim( (string) ( $contact_settings[ $is_en ? 'whatsapp_label_en' : 'whatsapp_label_ar' ] ?? '' ) );
if ( '' === $whatsapp_label ) { $whatsapp_label = $is_en ? 'Chat on WhatsApp' : 'تواصل عبر WhatsApp'; }

$GLOBALS['vava_page_data_name']         = $is_en ? 'policies-en.html' : 'policies.html';
$GLOBALS['vava_active_nav']             = '';
$GLOBALS['vava_internal_body_classes']  = array( 'vava-legal-page', 'vava-legal-page-' . $type );
get_header( 'page' );
?>
<main class="vava-legal-main" dir="<?php echo esc_attr( $dir ); ?>" data-vava-legal-page data-legal-type="<?php echo esc_attr( $type ); ?>">
	<span class="vava-legal-blob is-sage" aria-hidden="true"></span>
	<span class="vava-legal-blob is-coral" aria-hidden="true"></span>

	<section class="vava-legal-hero">
		<div class="vava-legal-container vava-legal-hero-grid">
			<figure class="vava-legal-hero-visual"><img src="<?php echo esc_url( $hero_image ); ?>" alt=""/></figure>
			<div class="vava-legal-hero-copy">
				<span class="vava-legal-eyebrow"><?php echo esc_html( (string) $data['eyebrow'] ); ?></span>
				<span class="vava-legal-lotus" aria-hidden="true">❀</span>
				<h1><?php echo esc_html( (string) $data['title'] ); ?></h1>
				<p><?php echo esc_html( (string) $data['intro'] ); ?></p>
				<div class="vava-legal-updated"><strong><?php echo esc_html( (string) $data['updated_label'] ); ?></strong><span><?php echo esc_html( (string) $data['updated_value'] ); ?></span></div>
			</div>
		</div>
	</section>

	<section class="vava-legal-body">
		<div class="vava-legal-container vava-legal-layout">
			<div class="vava-legal-content-column">
				<section class="vava-legal-commitment-card">
					<div><small><?php echo esc_html( $labels['commitment'] ); ?></small><p><?php echo esc_html( $labels['commitment_text'] ); ?></p></div>
					<div class="vava-legal-protection"><span><?php echo $icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><strong><?php echo esc_html( $labels['protection'] ); ?></strong><p><?php echo esc_html( $labels['protection_text'] ); ?></p></div>
				</section>

				<?php if ( isset( $sections[0] ) ) : $section = $sections[0]; ?>
				<section id="vava-legal-section-1" class="vava-legal-section-card is-visual" data-legal-section>
					<figure><img src="<?php echo esc_url( $hero_image ); ?>" alt=""/><span><?php echo $icon( $section_icons[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></figure>
					<div class="vava-legal-section-copy"><small>01</small><h2><?php echo esc_html( $section['title'] ); ?></h2><div class="vava-richtext-content"><?php echo wp_kses_post( $section['body'] ); ?></div></div>
				</section>
				<?php endif; ?>

				<?php if ( isset( $sections[1] ) ) : $section = $sections[1]; ?>
				<section id="vava-legal-section-2" class="vava-legal-section-card is-quote" data-legal-section>
					<div class="vava-legal-section-copy"><small>02</small><h2><?php echo esc_html( $section['title'] ); ?></h2><div class="vava-richtext-content"><?php echo wp_kses_post( $section['body'] ); ?></div></div>
					<blockquote><span>“</span><p><?php echo esc_html( $labels[ 'quote_' . $feature_key ] ); ?></p><span>”</span></blockquote>
				</section>
				<?php endif; ?>

				<?php if ( isset( $sections[2] ) ) : $section = $sections[2]; ?>
				<section id="vava-legal-section-3" class="vava-legal-section-card is-wide" data-legal-section>
					<span class="vava-legal-section-icon"><?php echo $icon( $section_icons[2] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<div class="vava-legal-section-copy"><small>03</small><h2><?php echo esc_html( $section['title'] ); ?></h2><div class="vava-richtext-content"><?php echo wp_kses_post( $section['body'] ); ?></div></div>
				</section>
				<?php endif; ?>

				<?php if ( count( $sections ) > 3 ) : ?>
				<div class="vava-legal-card-grid">
					<?php foreach ( array_slice( $sections, 3, 3, true ) as $section_index => $section ) : $display_number = $section_index + 1; ?>
					<section id="vava-legal-section-<?php echo esc_attr( (string) $display_number ); ?>" class="vava-legal-mini-card" data-legal-section>
						<span><?php echo $icon( $section_icons[ $section_index % count( $section_icons ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<small><?php echo esc_html( str_pad( (string) $display_number, 2, '0', STR_PAD_LEFT ) ); ?></small>
						<h2><?php echo esc_html( $section['title'] ); ?></h2>
						<div class="vava-richtext-content"><?php echo wp_kses_post( $section['body'] ); ?></div>
					</section>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<section class="vava-legal-principles">
					<div><h2><?php echo esc_html( $labels[ $feature_key . '_title' ] ); ?></h2><p><?php echo esc_html( $labels[ $feature_key . '_text' ] ); ?></p></div>
					<div class="vava-legal-principles-list">
						<?php foreach ( $labels['principles'] as $principle_index => $principle ) : ?><article><span><?php echo $icon( array( 'shield', 'user', 'lock' )[ $principle_index ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><strong><?php echo esc_html( $principle['title'] ); ?></strong><small><?php echo esc_html( $principle['text'] ); ?></small></article><?php endforeach; ?>
					</div>
				</section>

				<?php if ( count( $sections ) > 6 ) : ?>
				<div class="vava-legal-compact-list">
					<?php foreach ( array_slice( $sections, 6, null, true ) as $section_index => $section ) : $display_number = $section_index + 1; ?>
					<section id="vava-legal-section-<?php echo esc_attr( (string) $display_number ); ?>" data-legal-section>
						<span><?php echo esc_html( str_pad( (string) $display_number, 2, '0', STR_PAD_LEFT ) ); ?></span>
						<div><h2><?php echo esc_html( $section['title'] ); ?></h2><div class="vava-richtext-content"><?php echo wp_kses_post( $section['body'] ); ?></div></div>
					</section>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

			</div>

			<aside class="vava-legal-toc" data-vava-legal-toc>
				<header><span><?php echo $icon( 'document' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><h2><?php echo esc_html( $labels['on_page'] ); ?></h2></header>
				<nav>
					<?php foreach ( $sections as $index => $section ) : ?><a class="<?php echo 0 === $index ? 'is-active' : ''; ?>" href="#vava-legal-section-<?php echo esc_attr( (string) ( $index + 1 ) ); ?>"<?php echo 0 === $index ? ' aria-current="true"' : ''; ?>><i></i><span><?php echo esc_html( $section['title'] ?: (string) $data['title'] ); ?></span></a><?php endforeach; ?>
				</nav>
				<div class="vava-legal-toc-note"><span><?php echo $icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><strong><?php echo esc_html( $labels['protection'] ); ?></strong><p><?php echo esc_html( $labels['protection_text'] ); ?></p></div>
				<div class="vava-legal-toc-contact">
					<strong><?php echo esc_html( $is_en ? 'Contact VAVA' : 'تواصل مع VAVA' ); ?></strong>
					<a class="is-contact-page" href="<?php echo esc_url( $contact_url ); ?>"><span><?php echo $icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><b><?php echo esc_html( $labels['contact_button'] ); ?></b></a>
					<?php if ( $contact_phone && $contact_phone_uri ) : ?><a class="is-phone" href="tel:<?php echo esc_attr( $contact_phone_uri ); ?>"><span><?php echo $icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><small><?php echo esc_html( $labels['phone_label'] ); ?></small><b dir="ltr"><?php echo esc_html( $contact_phone ); ?></b></a><?php endif; ?>
					<?php if ( $whatsapp_url ) : ?><a class="is-whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener"><span><?php echo $icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><b><?php echo esc_html( $whatsapp_label ); ?></b></a><?php endif; ?>
				</div>
			</aside>
		</div>
	</section>
</main>
<?php get_footer( 'page' ); ?>
