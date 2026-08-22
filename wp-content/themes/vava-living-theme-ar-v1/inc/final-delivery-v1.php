<?php
/**
 * Final-delivery patch for the approved VAVA content and UX adjustments.
 *
 * The migration is deliberately idempotent and runs once per installation.
 * It updates only values explicitly supplied in the final-delivery brief while
 * preserving live service/package copy and availability that depend on the
 * separately approved consultation/availability files.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'VAVA_FINAL_DELIVERY_PATCH_VERSION' ) ) {
	define( 'VAVA_FINAL_DELIVERY_PATCH_VERSION', '1.22.55' );
}

/** Locate a VAVA page by template, with a slug/title-aware callback fallback. */
function vava_final_delivery_page_id( string $template, string $checker = '' ): int {
	$ids = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => $template,
		)
	);
	if ( isset( $ids[0] ) ) {
		return absint( $ids[0] );
	}

	if ( '' !== $checker && function_exists( $checker ) ) {
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		foreach ( $pages as $page_id ) {
			if ( call_user_func( $checker, absint( $page_id ) ) ) {
				return absint( $page_id );
			}
		}
	}
	return 0;
}

/** Update a localized About meta value using the existing key convention. */
function vava_final_delivery_about_meta( int $page_id, string $base, string $lang, $value ): void {
	$key = function_exists( 'vava_about_meta_key' ) ? vava_about_meta_key( $base, $lang ) : ( 'en' === $lang ? $base . '_en' : $base );
	update_post_meta( $page_id, $key, $value );
}

/** Apply the About VAVA changes explicitly approved in the delivery brief. */
function vava_final_delivery_migrate_about(): void {
	$page_id = vava_final_delivery_page_id( 'page-templates/about-vava.php', 'vava_about_is_page' );
	if ( ! $page_id ) { return; }

	vava_final_delivery_about_meta( $page_id, '_vava_about_hero_title', 'ar', 'مساحة لإحياء الحياة' );
	vava_final_delivery_about_meta( $page_id, '_vava_about_hero_lead', 'ar', 'في فافا ليفينق، نؤمن بأن ازدهارنا لا ينفصل عن ازدهار الحياة من حولنا.' );
	vava_final_delivery_about_meta(
		$page_id,
		'_vava_about_hero_note',
		'ar',
		"متجذّرة في الأيورفيدا، ومنفتحة على علوم وحِكم تكاملية أخرى، تنمو فافا كمساحة للحياة الواعية، والمعرفة، والتجارب، والاتصال الأعمق بالحياة بكل ما فيها.\n\nوما يبدأ اليوم كمساحة رقمية، هو بداية نظام حيّ ينمو… ليصبح يومًا ما مكانًا يُعاش، ويُختبر، وتلتقي فيه هذه الرؤية على أرض الواقع."
	);

	// The brief explicitly removes this explanatory sentence from the story area.
	vava_final_delivery_about_meta( $page_id, '_vava_about_story_intro', 'ar', '' );
	vava_final_delivery_about_meta( $page_id, '_vava_about_story_intro', 'en', '' );

	// Keep the stored story itself, changing only the requested VAVA Living wording.
	foreach ( array( 'ar', 'en' ) as $lang ) {
		if ( function_exists( 'vava_about_story_content' ) ) {
			$story = vava_about_story_content( $page_id, $lang );
			if ( '' !== trim( $story ) ) {
				$story = str_ireplace( array( 'Living VAVA', 'living vava' ), 'VAVA Living', $story );
				vava_final_delivery_about_meta( $page_id, '_vava_about_story_content', $lang, $story );
			}
		}
	}

	// Remove the eyebrow only; leave the invitation copy and the three buttons intact.
	vava_final_delivery_about_meta( $page_id, '_vava_about_invite_eyebrow', 'ar', '' );
	vava_final_delivery_about_meta( $page_id, '_vava_about_invite_eyebrow', 'en', '' );
}

/** Return the saved paths array without replacing approved live-only content. */
function vava_final_delivery_saved_paths( int $page_id, string $lang ): array {
	$key   = '_vava_paths_data_' . ( 'en' === $lang ? 'en' : 'ar' );
	$saved = get_post_meta( $page_id, $key, true );
	if ( is_array( $saved ) ) { return $saved; }
	return function_exists( 'vava_paths_data' ) ? vava_paths_data( $page_id, $lang ) : array();
}

/** Update one package in-place by UID. */
function vava_final_delivery_update_package( array &$data, string $uid, array $changes ): bool {
	if ( empty( $data['packages'] ) || ! is_array( $data['packages'] ) ) { return false; }
	foreach ( $data['packages'] as &$package ) {
		if ( ! is_array( $package ) || sanitize_key( (string) ( $package['uid'] ?? '' ) ) !== sanitize_key( $uid ) ) { continue; }
		$package = array_replace( $package, $changes );
		unset( $package );
		return true;
	}
	unset( $package );
	return false;
}

/** Locate the discovery package using the established UID/title helper. */
function vava_final_delivery_discovery_index( array $packages ): int {
	foreach ( $packages as $index => $package ) {
		if ( ! is_array( $package ) ) { continue; }
		if ( function_exists( 'vava_paths_is_discovery_session' ) && vava_paths_is_discovery_session( $package ) ) {
			return (int) $index;
		}
		$title = trim( (string) ( $package['title'] ?? '' ) );
		if ( in_array( $title, array( 'جلسة استكشافية', 'Discovery Session' ), true ) ) { return (int) $index; }
	}
	return -1;
}

/** Apply consultation/session/FAQ changes while retaining unprovided package data. */
function vava_final_delivery_migrate_paths(): void {
	$page_id = vava_final_delivery_page_id( 'page-templates/paths-vava.php', 'vava_paths_is_page' );
	if ( ! $page_id ) { return; }

	$ar = vava_final_delivery_saved_paths( $page_id, 'ar' );
	$en = vava_final_delivery_saved_paths( $page_id, 'en' );

	$individual_description = 'تنطلق استشارات VAVA في مرحلتها الحالية من الأيورفيدا، لتساعدك على فهم نفسك واحتياجك بصورة أشمل. وتتنوع بين استفسارات سريعة، وجلسات شاملة، ومتابعات داعمة.';
	$programs_description   = 'تقدّم VAVA برامج موجهة تجمع بين المعرفة والممارسة، لدعم تحولٍ تدريجي يمكن أن يُعاش ويُدمج في تفاصيل الحياة.';
	$workshops_description  = 'بعض الأشياء تحتاج أن تُعاش. لذلك تنمو في VAVA مساحة للورش والتجارب متعددة الحواس، تجمع بين الاستكشاف، والمشاركة، والحضور.';

	if ( isset( $ar['consultation'] ) && is_array( $ar['consultation'] ) ) {
		$ar['consultation']['description'] = $individual_description;
		// Remove the sentence explaining the in-page flow when it exists in either legacy note field.
		foreach ( array( 'note', 'intro_note' ) as $note_key ) {
			if ( isset( $ar['consultation'][ $note_key ] ) ) {
				$ar['consultation'][ $note_key ] = preg_replace( '/بمجرد اختيار الاستشارات الفردية[^.。]*[.。]?/u', '', (string) $ar['consultation'][ $note_key ] );
				$ar['consultation'][ $note_key ] = trim( (string) $ar['consultation'][ $note_key ] );
			}
		}
	}

	if ( ! empty( $ar['pathways'] ) && is_array( $ar['pathways'] ) ) {
		foreach ( $ar['pathways'] as &$pathway ) {
			if ( ! is_array( $pathway ) ) { continue; }
			switch ( sanitize_key( (string) ( $pathway['uid'] ?? '' ) ) ) {
				case 'individual': $pathway['description'] = $individual_description; break;
				case 'programs':   $pathway['description'] = $programs_description; break;
				case 'workshops':  $pathway['description'] = $workshops_description; break;
			}
		}
		unset( $pathway );
	}

	// Session inquiries: visible duration 15–20 min and real booking duration 20 min.
	vava_final_delivery_update_package(
		$ar,
		'session-8',
		array(
			'title'            => 'جلسة استفسارات',
			'category'         => 'quick',
			'duration'         => '15–20 دقيقة',
			'booking_duration' => 20,
		)
	);
	vava_final_delivery_update_package(
		$en,
		'session-8',
		array(
			'category'         => 'quick',
			'duration'         => '15–20 minutes',
			'booking_duration' => 20,
		)
	);

	// Discovery: free, no longer than 15 minutes. Preserve its existing live availability.
	$discovery_overview = 'إذا كنت جديدًا على VAVA ولا تعرف من أين تبدأ، فهذه الجلسة تساعدك على استكشاف الخدمات الاستشارية، وفهم الفرق بينها، واختيار الخيار الأنسب لاحتياجك.';
	$index = vava_final_delivery_discovery_index( (array) ( $ar['packages'] ?? array() ) );
	if ( $index >= 0 ) {
		$ar['packages'][ $index ] = array_replace(
			$ar['packages'][ $index ],
			array(
				'title'            => 'جلسة استكشافية',
				'category'         => 'quick',
				'price'            => 'مجانية',
				'currency'         => '',
				'duration'         => '10–15 دقيقة',
				'booking_duration' => 15,
				'overview'         => $discovery_overview,
				'booking_enabled'  => 1,
			)
		);
	}
	$en_index = vava_final_delivery_discovery_index( (array) ( $en['packages'] ?? array() ) );
	if ( $en_index >= 0 ) {
		$en['packages'][ $en_index ]['category']         = 'quick';
		$en['packages'][ $en_index ]['duration']         = '10–15 minutes';
		$en['packages'][ $en_index ]['booking_duration'] = 15;
		$en['packages'][ $en_index ]['booking_enabled']  = 1;
	}

	// The follow-up session wording is fully supplied in the final brief.
	vava_final_delivery_update_package(
		$ar,
		'session-5',
		array(
			'overview' => 'جلسة مخصصة لمراجعة تقدمك، والإجابة عن استفساراتك، وتعزيز فهمك، وتحديث التوصيات بما يتناسب مع احتياجاتك الحالية.',
			'outcomes_title' => 'ماذا تشمل؟',
			'outcomes' => array(
				array( 'text' => 'مراجعة التقدم منذ آخر جلسة.' ),
				array( 'text' => 'تقييم المستجدات والتحديات.' ),
				array( 'text' => 'تحديث التوصيات عند الحاجة.' ),
				array( 'text' => 'تعزيز المفاهيم والإجابة عن الاستفسارات.' ),
			),
			'audience_title' => 'مناسبة لك إذا كنت...',
			'audience' => array(
				array( 'text' => 'سبق لك حضور جلسة شاملة.' ),
				array( 'text' => 'ترغب في متابعة تقدمك وتحديث خطتك.' ),
			),
		)
	);

	// Exact FAQ revisions supplied in the brief; untouched FAQ rows remain as-is.
	if ( isset( $ar['faq']['items'] ) && is_array( $ar['faq']['items'] ) ) {
		$answers = array(
			0 => "أرغب في أن أبدأ بنفسي وبالوتيرة التي تناسبني. → المنتجات الرقمية.\nلدي استفسار محدد ولا أحتاج جلسة كاملة. → جلسة استفسارات.\nأحتاج جلسة شاملة لفهم حالتي ووضع خطة. → نحو البداية.\nأرغب بجلسة شاملة ومرافقة أثناء التطبيق. → إحدى الباقات الشاملة (ويختلف بينها في المتابعة والدعم).\nأحتاج مراجعة أو تعديلًا لخطتي. → جلسات المتابعة.",
			3 => "لا لأنها من صميم الفطرة الإنسانية فهي لغة يألفها الكيان الإنساني الهدف هو تبسيط الحياة وليس تعقيدها.\nكل التوصيات تُبنى حسب: نمط حياتك-تركيبتك الأيورفيدية-قدرتك على الالتزام",
			6 => 'يمكنك حجز جلسة استكشافية مدتها 10-15د متوفرة في قسم الاستشارات السريعة أو إرسال رسالة عبرقسم التواصل أو الواتساب',
			8 => 'أبدًا. في الجلسات نشرح كل ما تحتاجه بطريقة بسيطة وواضحة، أما المنتجات الرقمية فقد صُممت لتناسب مختلف مستويات المعرفة، سواء كنت تبدأ من الصفر أو لديك معرفة سابقة',
		);
		foreach ( $answers as $faq_index => $answer ) {
			if ( isset( $ar['faq']['items'][ $faq_index ] ) && is_array( $ar['faq']['items'][ $faq_index ] ) ) {
				$ar['faq']['items'][ $faq_index ]['answer'] = $answer;
			}
		}
	}

	update_post_meta( $page_id, '_vava_paths_data_ar', $ar );
	update_post_meta( $page_id, '_vava_paths_data_en', $en );
}

/** Fix the global booking fallback so no confirmation falls back to 60 minutes. */
function vava_final_delivery_migrate_booking(): void {
	$page_id = function_exists( 'vava_booking_page_id' ) ? vava_booking_page_id() : vava_final_delivery_page_id( 'page-templates/booking-vava.php', 'vava_booking_is_page' );
	if ( ! $page_id ) { return; }
	$shared = get_post_meta( $page_id, '_vava_booking_shared', true );
	$shared = is_array( $shared ) ? $shared : array();
	$shared['default_duration'] = 90;
	update_post_meta( $page_id, '_vava_booking_shared', $shared );
}

/** Apply the Selections opening, tangible coming-soon copy, and retain products. */
function vava_final_delivery_migrate_selections(): void {
	$page_id = function_exists( 'vava_selections_page_id' ) ? vava_selections_page_id() : vava_final_delivery_page_id( 'page-templates/selections-vava.php', 'vava_selections_is_page' );
	if ( ! $page_id ) { return; }

	$ar = get_post_meta( $page_id, '_vava_selections_text_ar', true );
	$en = get_post_meta( $page_id, '_vava_selections_text_en', true );
	$ar = is_array( $ar ) ? $ar : ( function_exists( 'vava_selections_text_defaults' ) ? vava_selections_text_defaults( 'ar' ) : array() );
	$en = is_array( $en ) ? $en : ( function_exists( 'vava_selections_text_defaults' ) ? vava_selections_text_defaults( 'en' ) : array() );

	$ar['hero'] = array_replace(
		(array) ( $ar['hero'] ?? array() ),
		array(
			'title' => 'مختارات لحياة تُعاش بوعي',
			'intro' => 'في VAVA، لا نجمع المنتجات بحسب فئتها، بل نختارها بحسب الرؤية التي تقف خلفها، والأثر الذي يمكن أن تضيفه إلى الحياة. لذلك قد تجد هنا موارد رقمية، أومنتجات للعناية الشخصية، أوقطعًا للمنزل، أوأعمالًا حرفية أوغيرها...يجمعها جميعًا مقصدٌ واحد: دعم علاقة أكثر وعيًا بالحياة، وما نشاركه فيها',
			'note'  => '',
		)
	);
	if ( ! isset( $ar['collections'] ) || ! is_array( $ar['collections'] ) ) { $ar['collections'] = array(); }
	if ( ! isset( $ar['collections']['tangible'] ) || ! is_array( $ar['collections']['tangible'] ) ) { $ar['collections']['tangible'] = array(); }
	$ar['collections']['tangible']['description'] = 'قريبًا';
	if ( ! isset( $en['collections'] ) || ! is_array( $en['collections'] ) ) { $en['collections'] = array(); }
	if ( ! isset( $en['collections']['tangible'] ) || ! is_array( $en['collections']['tangible'] ) ) { $en['collections']['tangible'] = array(); }
	$en['collections']['tangible']['description'] = 'Coming soon';

	update_post_meta( $page_id, '_vava_selections_text_ar', $ar );
	update_post_meta( $page_id, '_vava_selections_text_en', $en );
}

/** Hide journal content until the owner publishes it later. */
function vava_final_delivery_migrate_journal(): void {
	$page_id = vava_final_delivery_page_id( 'page-templates/journal-vava.php', 'vava_journal_is_page' );
	if ( ! $page_id ) { return; }

	$shared = get_post_meta( $page_id, '_vava_journal_shared', true );
	$shared = is_array( $shared ) ? $shared : array();
	$shared['show_articles'] = 0;
	update_post_meta( $page_id, '_vava_journal_shared', $shared );

	foreach ( array( 'ar', 'en' ) as $lang ) {
		$key  = '_vava_journal_text_' . $lang;
		$text = get_post_meta( $page_id, $key, true );
		$text = is_array( $text ) ? $text : ( function_exists( 'vava_journal_text_defaults' ) ? vava_journal_text_defaults( $lang ) : array() );
		if ( ! isset( $text['articles'] ) || ! is_array( $text['articles'] ) ) { $text['articles'] = array(); }
		$text['articles']['intro'] = '';
		$text['articles']['empty'] = 'en' === $lang ? 'Coming soon' : 'قريبًا';
		update_post_meta( $page_id, $key, $text );
	}
}

/** Remove the legacy package dropdown and apply the approved contact guidance. */
function vava_final_delivery_migrate_contact(): void {
	$page_id = function_exists( 'vava_contact_page_id' ) ? vava_contact_page_id() : vava_final_delivery_page_id( 'page-templates/contact-vava.php', 'vava_contact_is_page' );
	if ( ! $page_id ) { return; }

	$shared = get_post_meta( $page_id, '_vava_contact_shared', true );
	$shared = is_array( $shared ) ? $shared : array();
	$schema = isset( $shared['field_schema'] ) && is_array( $shared['field_schema'] ) ? $shared['field_schema'] : ( function_exists( 'vava_contact_default_field_schema' ) ? vava_contact_default_field_schema() : array() );
	$removed = array();
	$schema = array_values( array_filter( $schema, static function ( $field ) use ( &$removed ): bool {
		if ( ! is_array( $field ) ) { return false; }
		$id   = sanitize_key( (string) ( $field['id'] ?? '' ) );
		$type = sanitize_key( (string) ( $field['type'] ?? '' ) );
		$is_package = 'select' === $type && ( false !== strpos( $id, 'package' ) || false !== strpos( $id, 'pakage' ) || false !== strpos( $id, 'selected' ) );
		if ( $is_package ) { $removed[] = $id; return false; }
		return true;
	} ) );
	$shared['field_schema'] = $schema;
	if ( isset( $shared['guide_card_schema'] ) && is_array( $shared['guide_card_schema'] ) && $removed ) {
		foreach ( $shared['guide_card_schema'] as &$guide ) {
			if ( ! is_array( $guide ) ) { continue; }
			$guide['field_ids'] = array_values( array_diff( (array) ( $guide['field_ids'] ?? array() ), $removed ) );
		}
		unset( $guide );
	}
	update_post_meta( $page_id, '_vava_contact_shared', $shared );

	$ar = get_post_meta( $page_id, '_vava_contact_text_ar', true );
	$ar = is_array( $ar ) ? $ar : ( function_exists( 'vava_contact_text_defaults' ) ? vava_contact_text_defaults( 'ar' ) : array() );
	$ar['guide'] = array_replace(
		(array) ( $ar['guide'] ?? array() ),
		array(
			'eyebrow' => 'ماذا أكتب؟',
			'title'    => 'رسالة أوضح، لنساعدك بشكل أفضل',
			'intro'    => 'لا تحتاج رسالتك إلى أن تكون طويلة. يكفي أن تشاركنا ما تبحث عنه، وسنساعدك على الوصول إلى الخيار الأنسب.',
			'cards'    => array(
				array( 'id' => 'guide_1', 'title' => 'موضوع الرسالة', 'body' => 'ابدأ بسبب تواصلك، مثل: استفسار عن خدمة أو منتج، المساعدة في اختيار الخيار المناسب، أو رغبة في التعاون.' ),
				array( 'id' => 'guide_2', 'title' => 'محتوى الرسالة', 'body' => 'اشرح باختصار ما تحتاجه، أو السؤال الذي تبحث عن إجابته، أو ما تأمل أن تساعدك VAVA فيه.' ),
				array( 'id' => 'guide_3', 'title' => 'تفاصيل تساعدنا', 'body' => 'يمكنك إضافة أي تفاصيل ترى أنها مفيدة، مثل الوقت المناسب للتواصل، أو أي معلومة تعتقد أنها ستساعدنا على خدمتك بشكل أفضل.' ),
			),
		)
	);
	if ( isset( $ar['form']['field_texts'] ) && is_array( $ar['form']['field_texts'] ) ) {
		foreach ( $removed as $field_id ) { unset( $ar['form']['field_texts'][ $field_id ] ); }
	}
	update_post_meta( $page_id, '_vava_contact_text_ar', $ar );

	$en = get_post_meta( $page_id, '_vava_contact_text_en', true );
	if ( is_array( $en ) && isset( $en['form']['field_texts'] ) && is_array( $en['form']['field_texts'] ) ) {
		foreach ( $removed as $field_id ) { unset( $en['form']['field_texts'][ $field_id ] ); }
		update_post_meta( $page_id, '_vava_contact_text_en', $en );
	}
}

/** Apply the supplied social destinations and requested display order. */
function vava_final_delivery_migrate_social(): void {
	$page_id = function_exists( 'vava_homepage_settings_page_id' ) ? vava_homepage_settings_page_id() : absint( get_option( 'page_on_front' ) );
	if ( ! $page_id ) { return; }
	update_post_meta(
		$page_id,
		'_vava_home_footer_social',
		array(
			array( 'platform' => 'instagram', 'url' => 'https://www.instagram.com/thevavaliving?igsh=cWE4NWtzYmF4eXMw' ),
			array( 'platform' => 'tiktok', 'url' => 'https://www.tiktok.com/@thevavaliving?_r=1&_t=ZS-98ePKhdYIU8' ),
			array( 'platform' => 'whatsapp', 'url' => 'https://wa.me/966573992224' ),
			array( 'platform' => 'email', 'url' => 'Thevavaliving@gmail.com' ),
		)
	);
}

/** Run once after WordPress has loaded the theme and its helper modules. */
function vava_final_delivery_run_migration(): void {
	if ( get_option( 'vava_final_delivery_patch_version' ) === VAVA_FINAL_DELIVERY_PATCH_VERSION ) { return; }
	if ( wp_installing() ) { return; }

	vava_final_delivery_migrate_about();
	vava_final_delivery_migrate_paths();
	vava_final_delivery_migrate_booking();
	vava_final_delivery_migrate_selections();
	vava_final_delivery_migrate_journal();
	vava_final_delivery_migrate_contact();
	vava_final_delivery_migrate_social();

	update_option( 'vava_final_delivery_patch_version', VAVA_FINAL_DELIVERY_PATCH_VERSION, false );
}
add_action( 'init', 'vava_final_delivery_run_migration', 80 );

/** Load final visual refinements after the theme's page-specific styles. */
function vava_final_delivery_enqueue_styles(): void {
	if ( is_admin() ) { return; }
	$relative = 'assets/css/final-delivery-v1.css';
	$path     = get_theme_file_path( $relative );
	$version  = is_file( $path ) ? (string) filemtime( $path ) : VAVA_FINAL_DELIVERY_PATCH_VERSION;
	wp_enqueue_style( 'vava-final-delivery-v1', get_theme_file_uri( $relative ), array(), $version );
}
add_action( 'wp_enqueue_scripts', 'vava_final_delivery_enqueue_styles', 1001 );
