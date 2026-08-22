<?php
/**
 * Homepage page-template meta boxes.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

function vava_homepage_template_slug(): string {
	return 'page-templates/homepage.php';
}

function vava_homepage_is_home_page( int $post_id ): bool {
	if ( $post_id <= 0 ) {
		return false;
	}
	$template = get_page_template_slug( $post_id );
	$title    = (string) get_post_field( 'post_title', $post_id );
	return vava_homepage_template_slug() === $template
		|| (int) get_option( 'page_on_front' ) === $post_id
		|| 'الصفحة الرئيسية' === trim( (string) $title );
}

/** Arabic defaults retain the existing meta keys for backward compatibility. */
function vava_homepage_defaults(): array {
	return array(
		'_vava_home_hero_eyebrow'             => 'VAVA Living',
		'_vava_home_hero_title'               => 'مساحة للعودة...',
		'_vava_home_hero_description'         => "عودة إلى الحياة الفطرية المنسوجة في طبيعتنا.\nعودة إلى فهم يرى أن الاتزان ليس رحلة تخص الإنسان وحده، بل تناغمًا فطريًا نتشاركه مع المخلوقات، والطبيعة، والعوالم المترابطة التي نتشارك الوجود فيها.",
		'_vava_home_hero_button_text'         => 'اكتشف VAVA',
		'_vava_home_hero_button_url'          => home_url( '/about-vava/' ),
		'_vava_home_hero_media_type'          => 'video',
		'_vava_home_hero_image_id'            => 0,
		'_vava_home_hero_video_id'            => 0,
		'_vava_home_hero_poster_id'           => 0,
		'_vava_home_hero_video_url'           => vava_asset_uri( 'assets/videos/home-hero-video.mp4' ),
		'_vava_home_paths_title'              => 'مسارات VAVA',
		'_vava_home_paths_description'        => 'لكل إنسان إيقاعه، ولكل مرحلة احتياجها، ولكل رحلة بابها الخاص. ولهذا تنمو مسارات VAVA بأشكال مختلفة—بعضها متاح اليوم، وبعضها لا يزال في طور التشكل. لكنها جميعًا تنطلق من النية نفسها: أن تدعم حياة أكثر وعيًا، واتزانًا واتصالًا.',
		'_vava_home_paths_button_text'        => 'استكشف مسارات VAVA',
		'_vava_home_paths_button_url'         => home_url( '/paths-vava/' ),
		'_vava_home_paths_image_id'           => 0,
		'_vava_home_paths_image_alt'          => 'دفتر VAVA وكوب دافئ وسط الزهور في ضوء هادئ',
		'_vava_home_shop_eyebrow'             => 'VAVA Shop',
		'_vava_home_shop_title'               => 'متجر VAVA',
		'_vava_home_shop_subtitle'            => 'مختارات لحياة تُعاش بوعي.',
		'_vava_home_shop_description'         => 'ما يوجد في متجر VAVA ليس مجرد منتجات... بل امتدادات مدروسة تدعم أسلوب حياة أكثر حضورًا، واتزانًا واتصالًا.',
		'_vava_home_shop_small_text'          => 'من الأدلة الرقمية والمعارف العملية المتاحة اليوم، إلى المختارات المستقبلية التي ستنمو مع الرؤية—كل ما يُقدَّم هنا يُختار بعناية ليكون جزءًا من تجربة عيش أكثر وعيًا ومعنى.',
		'_vava_home_shop_button_text'         => 'تسّوق الآن',
		'_vava_home_shop_button_url'          => home_url( '/store/' ),
		'_vava_home_testimonials_label'       => 'تجارب حقيقية',
		'_vava_home_testimonials_title'       => 'تجارب من عاشوا VAVA',
		'_vava_home_testimonials_intro'       => 'تجارب كاملة تُعرض بهدوء، مع مساحة للقراءة والتوسّع عند الحاجة دون ازدحام بصري.',
		'_vava_home_journal_title'            => 'المجلة',
		'_vava_home_journal_subtitle'         => 'مساحة للاستكشاف',
		'_vava_home_journal_description'      => 'أفكار ملهمة، وموارد بعناية، وخواطر من القلب. نشارك ما يثري الحياة البسيطة والهادفة، لنلهمك في رحلتك نحو نمط حياة أكثر وعيًا واتزانًا وجمالًا.',
		'_vava_home_journal_small_text'       => 'قد تجد هنا مقالات، وتأملات، وموارد، وأفكارًا تنمو مع الرؤية وتدعوك للتوقف، والملاحظة، والاقتراب أكثر من الحياة بكل ما فيها.',
		'_vava_home_journal_visual_caption'  => 'رحلة هادئة داخل مدونة VAVA',
		'_vava_home_journal_image_id'        => 0,
		'_vava_home_journal_button_text'      => 'استكشف المجلة',
		'_vava_home_journal_button_url'       => home_url( '/journal/' ),
		'_vava_home_contact_title'            => 'تواصل',
		'_vava_home_contact_description'      => 'إذا كان هناك شيء يدعوك للتواصل—استفسار، فكرة، رغبة في التعاون، أو مجرد رغبة في مشاركة شيء مع VAVA—سنكون سعداء بسماعك.',
		'_vava_home_contact_button_text'      => 'تواصل معنا',
		'_vava_home_contact_button_url'       => home_url( '/contact/' ),
		'_vava_home_contact_image_id'         => 0,
		'_vava_home_footer_tagline'           => 'مساحة للعودة',
		'_vava_home_footer_copyright'         => 'جميع الحقوق محفوظة © VAVA Living 2026',
		'_vava_home_footer_document_label'    => 'وثيقة العمل الحر:',
		'_vava_home_footer_document_number'   => '686388076FL',
	);
}

function vava_homepage_english_defaults(): array {
	return array(
		'_vava_home_hero_eyebrow_en'           => 'VAVA Living',
		'_vava_home_hero_title_en'             => 'A Space to Return...',
		'_vava_home_hero_description_en'       => "A return to the natural life woven into our being.\nA return to a view of balance as a harmony shared with living beings, nature, and the interconnected worlds around us.",
		'_vava_home_hero_button_text_en'       => 'Discover VAVA',
		'_vava_home_hero_button_url_en'        => home_url( '/en/about-vava/' ),
		'_vava_home_paths_title_en'            => 'VAVA Paths',
		'_vava_home_paths_description_en'      => 'Every person has a rhythm, every stage has a need, and every journey has its own doorway. VAVA Paths grow in different forms, all rooted in the same intention: to support a life of greater awareness, balance, and connection.',
		'_vava_home_paths_button_text_en'      => 'Explore VAVA Paths',
		'_vava_home_paths_button_url_en'       => home_url( '/en/paths-vava/' ),
		'_vava_home_paths_image_alt_en'        => 'VAVA journal and a warm cup among flowers in soft light',
		'_vava_home_shop_eyebrow_en'           => 'VAVA Shop',
		'_vava_home_shop_title_en'             => 'VAVA Shop',
		'_vava_home_shop_subtitle_en'          => 'Curated choices for a consciously lived life.',
		'_vava_home_shop_description_en'       => 'What you find in the VAVA shop is more than products; it is a thoughtful extension of a more present, balanced, and connected way of living.',
		'_vava_home_shop_small_text_en'        => 'From digital guides and practical knowledge available today to future selections that will grow with the vision, everything is chosen with care.',
		'_vava_home_shop_button_text_en'       => 'Shop Now',
		'_vava_home_shop_button_url_en'        => home_url( '/en/store/' ),
		'_vava_home_testimonials_label_en'     => 'Real Experiences',
		'_vava_home_testimonials_title_en'     => 'Stories from Those Who Lived VAVA',
		'_vava_home_testimonials_intro_en'     => 'Complete stories displayed calmly, with room to read and expand without visual clutter.',
		'_vava_home_journal_title_en'          => 'Journal',
		'_vava_home_journal_subtitle_en'       => 'A Space for Discovery',
		'_vava_home_journal_description_en'    => 'Inspiring ideas, carefully chosen resources, and reflections from the heart—shared to enrich a simple, purposeful life and support a more aware, balanced journey.',
		'_vava_home_journal_small_text_en'     => 'Here you may find articles, reflections, resources, and ideas that grow with the vision and invite you to pause, notice, and come closer to life.',
		'_vava_home_journal_visual_caption_en'=> 'A calm journey inside the VAVA Journal',
		'_vava_home_journal_button_text_en'    => 'Explore the Journal',
		'_vava_home_journal_button_url_en'     => home_url( '/en/journal/' ),
		'_vava_home_contact_title_en'          => 'Connect',
		'_vava_home_contact_description_en'    => 'If something is inviting you to connect—an inquiry, an idea, a wish to collaborate, or simply something you would like to share with VAVA—we would be happy to hear from you.',
		'_vava_home_contact_button_text_en'    => 'Contact Us',
		'_vava_home_contact_button_url_en'     => home_url( '/en/contact/' ),
		'_vava_home_footer_tagline_en'         => 'A Space to Return',
		'_vava_home_footer_copyright_en'       => 'All rights reserved © VAVA Living 2026',
		'_vava_home_footer_document_label_en'  => 'Freelance document:',
	);
}

function vava_homepage_testimonial_defaults(): array {
	return array(
		array( 'text' => 'كنت مؤمنة دائمًا بالتغيير، لكن أبدًا ما تخيلت أن الروتين اليومي وطبيعة جسمي أو اللي صار عليه جسمي حاليًا، أو إنه أشرب دافي يفرق عن لما أشرب بارد، وإنه فيه فرق لما آكل الخضار زي ما هي وبين أعملها شوربة أو أطبخها. عالم جديد ما أعرف عنه رغم إني قارئة وأغلب الأمور تكون مرت علي حتى لو ما فهمتها. بعد الجلسة اللي عملتها مع كوتش نورة لقيت ضالتي هنا، شدني العلم الجديد هذا وفعلاً كثير من أموري تحسنت: أنظم نومي وساعات جسمي ومواعيد أكلي ورياضتي. بتحدد أمور كثير في حياتي ونفسيتي وإبداعي. كان فعلاً شيء فارق في حياتي. شكرًا كوتش نورة.', 'author' => '— تجربة عميلة 1' ),
		array( 'text' => 'يسعدك ربي كوتش نورة. الجلسة معاك أخذتني في رحلة مع سماع صوتك الهادئ كإيقاع مريح إلى العمق في فهم ما يجول بها ويعيقها. حقيقي أثمرت الجلسة معاك بفهم الدوشا، وأسلوبك السهل والممتع في إيصال المعلومة بالزحام. حقيقي أوجزتي وأوصلتي المعلومة. الله يجعلك من خير إلى خير، وهنيئًا لعملائك بك كوتش نورة. سبحان من وضع فيك السماحة والقبول.', 'author' => '— تجربة عميلة 2' ),
		array( 'text' => 'الجلسة كانت رائعة، عرفتني على طبيعة دوشتي أكثر وكيف أتعامل معها. التقيد بمواعيد الطعام والرياضة كثير حسّن مزاجي، وبنفس الوقت كان عندي تشنج بالرقبة وخلال أيام اتحسن من خلال استخدام زيت السمسم وحركات رياضية بسيطة والتوصيات اللي قدمتيها لي. شكرًا كثير.', 'author' => '— تجربة عميلة 4' ),
		array( 'text' => 'أولًا شكرًا كونك شخص لطيف ربي أعطاه القبول وتدخلين القلب بلطف، وكنت محظوظة أني أخذت معك جلسة. ثانيًا حبيت أشكرك من كل قلبي على جلسة الأيورفيدا اللي كانت نقلة نوعية باتصالي بجسمي. عرفت كيف أكون أهدأ بتغييرات بسيطة جدًا بنظامي الغذائي.', 'author' => '— تجربة عميلة 5' ),
		array( 'text' => 'بصراحة الجلسة كانت أكثر من رائعة. دخلت معك وأنا ما عندي أي خلفية بالأيورفيدا، بنيتي لي المفاهيم الأساسية بطريقة مبسطة وواضحة جدًا. حقيقي بعد الجلسة صرت أشوف كل شيء كأنه عناصر، صرت أفهم جسمي بطريقة ما كنت أفهمها قبل.', 'author' => '— تجربة عميلة 6' ),
		array( 'text' => 'تجربة الأيورفيدا بالنسبة لي ما كانت مجرد جلسة، كانت مساحة وعي وعودة أهدأ لنفسي وجسدي. أكثر شيء لمسني هو شعور التوازن والاتصال الداخلي، وكأني بدأت أسمع احتياجاتي بشكل أوضح وأهدأ.', 'author' => '— تجربة عميلة 7' ),
	);
}

function vava_homepage_testimonial_english_defaults(): array {
	$items = array();
	for ( $i = 1; $i <= 6; $i++ ) {
		$items[] = array( 'text' => '', 'author' => '— Client Experience ' . $i );
	}
	return $items;
}

function vava_homepage_journal_defaults(): array {
	return array(
		array( 'title' => 'كيف نعيش مع إيقاع الصيف؟', 'label' => 'قراءة هادئة', 'url' => home_url( '/article-summer-rhythm/' ), 'image_id' => 0, 'class' => 'tea vava-inline-index-2' ),
		array( 'title' => 'تأمل قصير للحضور', 'label' => 'مساحة تأمل', 'url' => home_url( '/article-presence-reflection/' ), 'image_id' => 0, 'class' => 'note vava-inline-index-3' ),
		array( 'title' => 'ما الذي يمكن أن نتعلمه من الطبيعة؟', 'label' => 'فكرة للاستكشاف', 'url' => home_url( '/article-nature-lessons/' ), 'image_id' => 0, 'class' => 'green vava-inline-index-4' ),
	);
}

function vava_homepage_journal_english_defaults(): array {
	return array(
		array( 'title' => 'How Do We Live with the Rhythm of Summer?', 'label' => 'A Gentle Read', 'url' => home_url( '/en/article-summer-rhythm/' ) ),
		array( 'title' => 'A Short Reflection on Presence', 'label' => 'A Space to Reflect', 'url' => home_url( '/en/article-presence-reflection/' ) ),
		array( 'title' => 'What Can Nature Teach Us?', 'label' => 'An Idea to Explore', 'url' => home_url( '/en/article-nature-lessons/' ) ),
	);
}

function vava_homepage_footer_primary_defaults( string $lang = 'ar' ): array {
	if ( 'en' === $lang ) {
		return array(
			array( 'label' => 'Home', 'url' => home_url( '/en/' ) ),
			array( 'label' => 'VAVA Paths', 'url' => home_url( '/en/paths-vava/' ) ),
			array( 'label' => 'Shop', 'url' => home_url( '/en/store/' ) ),
			array( 'label' => 'Journal', 'url' => home_url( '/en/journal/' ) ),
			array( 'label' => 'About VAVA', 'url' => home_url( '/en/about-vava/' ) ),
			array( 'label' => 'Contact', 'url' => home_url( '/en/contact/' ) ),
		);
	}
	return array(
		array( 'label' => 'الرئيسية', 'url' => home_url( '/' ) ),
		array( 'label' => 'مسارات VAVA', 'url' => home_url( '/paths-vava/' ) ),
		array( 'label' => 'المتجر', 'url' => home_url( '/store/' ) ),
		array( 'label' => 'المجلة', 'url' => home_url( '/journal/' ) ),
		array( 'label' => 'عن VAVA', 'url' => home_url( '/about-vava/' ) ),
		array( 'label' => 'تواصل معنا', 'url' => home_url( '/contact/' ) ),
	);
}

function vava_homepage_footer_policy_defaults( string $lang = 'ar' ): array {
	if ( 'en' === $lang ) {
		return array(
			array( 'label' => 'FAQ', 'url' => home_url( '/en/faq/' ) ),
			array( 'label' => 'Privacy Policy', 'url' => home_url( '/en/privacy/' ) ),
			array( 'label' => 'Terms & Conditions', 'url' => home_url( '/en/policies/' ) ),
		);
	}
	return array(
		array( 'label' => 'الأسئلة الشائعة', 'url' => home_url( '/faq/' ) ),
		array( 'label' => 'سياسة الخصوصية', 'url' => home_url( '/privacy/' ) ),
		array( 'label' => 'الشروط والأحكام', 'url' => home_url( '/policies/' ) ),
	);
}


function vava_home_field( int $post_id, string $key, $fallback = '' ) {
	return metadata_exists( 'post', $post_id, $key ) ? get_post_meta( $post_id, $key, true ) : $fallback;
}

function vava_home_language_key( string $key, string $lang ): string {
	return 'en' === $lang ? $key . '_en' : $key;
}

function vava_home_language_defaults( string $lang ): array {
	return 'en' === $lang ? vava_homepage_english_defaults() : vava_homepage_defaults();
}

function vava_home_field_language( int $post_id, string $key, string $lang = 'ar', $fallback = '' ) {
	$key      = vava_home_language_key( $key, $lang );
	$defaults = vava_home_language_defaults( $lang );
	return vava_home_field( $post_id, $key, $defaults[ $key ] ?? $fallback );
}

/**
 * Return the single Shop/VAVA Selections description while preserving legacy
 * content that was previously stored in the removed "additional text" field.
 */
function vava_home_shop_description( int $post_id, string $lang = 'ar' ): string {
	$description = trim( (string) vava_home_field_language( $post_id, '_vava_home_shop_description', $lang ) );
	$legacy      = trim( (string) vava_home_field_language( $post_id, '_vava_home_shop_small_text', $lang ) );
	if ( '' !== $legacy && false === mb_strpos( wp_strip_all_tags( $description ), wp_strip_all_tags( $legacy ) ) ) {
		$description = trim( $description . "\n\n" . $legacy );
	}
	return $description;
}

function vava_home_testimonials( int $post_id, string $lang = 'ar' ): array {
	$key      = 'en' === $lang ? '_vava_home_testimonials_items_en' : '_vava_home_testimonials_items';
	$value    = get_post_meta( $post_id, $key, true );
	$fallback = 'en' === $lang ? vava_homepage_testimonial_english_defaults() : vava_homepage_testimonial_defaults();
	return is_array( $value ) ? $value : $fallback;
}

function vava_home_journal_settings( int $post_id, string $lang = 'ar' ): array {
	$mode = (string) get_post_meta( $post_id, '_vava_home_journal_query_mode', true );
	if ( ! in_array( $mode, array( 'latest', 'random' ), true ) ) {
		$mode = (string) get_post_meta( $post_id, '_vava_home_journal_query_mode_en', true );
	}
	$mode = in_array( $mode, array( 'latest', 'random' ), true ) ? $mode : 'latest';

	$latest = absint( get_post_meta( $post_id, '_vava_home_journal_latest_category', true ) );
	if ( ! $latest ) {
		$latest = absint( get_post_meta( $post_id, '_vava_home_journal_latest_category_en', true ) );
	}

	$random = get_post_meta( $post_id, '_vava_home_journal_random_categories', true );
	if ( ! is_array( $random ) || ! $random ) {
		$english_random = get_post_meta( $post_id, '_vava_home_journal_random_categories_en', true );
		if ( is_array( $english_random ) ) {
			$random = $english_random;
		}
	}
	$random = is_array( $random ) ? array_values( array_filter( array_map( 'absint', $random ) ) ) : array();

	return array(
		'mode'              => $mode,
		'latest_category'   => $latest,
		'random_categories' => $random,
	);
}

function vava_home_journal_items_from_settings( int $post_id, string $lang, array $settings ): array {
	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'suppress_filters'    => false,
	);

	if ( 'random' === ( $settings['mode'] ?? 'latest' ) ) {
		$args['orderby'] = 'rand';
		if ( ! empty( $settings['random_categories'] ) ) {
			$args['category__in'] = array_values( array_filter( array_map( 'absint', (array) $settings['random_categories'] ) ) );
		}
	} else {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
		if ( ! empty( $settings['latest_category'] ) ) {
			$args['cat'] = absint( $settings['latest_category'] );
		}
	}

	/**
	 * Filter the homepage journal query, for example to connect a multilingual plugin.
	 *
	 * @param array  $args Query arguments.
	 * @param string $lang Homepage language code.
	 * @param int    $post_id Homepage page ID.
	 */
	$args    = apply_filters( 'vava_homepage_journal_query_args', $args, $lang, $post_id );
	$posts   = get_posts( $args );
	$items   = array();
	$classes = array( 'tea vava-inline-index-2', 'note vava-inline-index-3', 'green vava-inline-index-4' );

	foreach ( $posts as $index => $article ) {
		$categories = get_the_category( $article->ID );
		$label      = $categories ? (string) $categories[0]->name : '';
		$items[]    = array(
			'title'    => get_the_title( $article ),
			'label'    => $label,
			'url'      => get_permalink( $article ),
			'image_id' => get_post_thumbnail_id( $article ),
			'class'    => $classes[ $index ] ?? '',
		);
	}

	return $items;
}

function vava_home_journal_items( int $post_id, string $lang = 'ar' ): array {
	return vava_home_journal_items_from_settings( $post_id, $lang, vava_home_journal_settings( $post_id, $lang ) );
}


/** Meta key for the shared internal-pages header menu selected from Homepage > Hero. */
function vava_home_internal_header_menu_meta_key(): string {
	return '_vava_home_internal_header_menu_id';
}

/**
 * Find the managed homepage used as the source of shared site settings.
 */
function vava_homepage_settings_page_id(): int {
	$front_page_id = absint( get_option( 'page_on_front' ) );
	if ( $front_page_id && vava_homepage_is_home_page( $front_page_id ) ) {
		return $front_page_id;
	}

	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'private', 'draft' ),
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => vava_homepage_template_slug(),
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);

	return ! empty( $pages[0] ) && $pages[0] instanceof WP_Post ? absint( $pages[0]->ID ) : 0;
}

/**
 * Return the menu selected for all internal-page headers.
 *
 * The homepage setting is authoritative. The registered WordPress menu
 * location remains a safe fallback for existing installations and first run.
 */
function vava_home_internal_header_menu_id( int $homepage_id = 0 ): int {
	$homepage_id = $homepage_id > 0 ? $homepage_id : vava_homepage_settings_page_id();
	$menu_id     = $homepage_id ? absint( get_post_meta( $homepage_id, vava_home_internal_header_menu_meta_key(), true ) ) : 0;

	if ( $menu_id && wp_get_nav_menu_object( $menu_id ) ) {
		return $menu_id;
	}

	$locations = get_nav_menu_locations();
	$menu_id   = absint( $locations['primary_internal'] ?? 0 );
	return $menu_id && wp_get_nav_menu_object( $menu_id ) ? $menu_id : 0;
}

/**
 * Return top-level links for the internal-header menu shown in the Hero preview.
 */
function vava_homepage_internal_header_preview_items( int $homepage_id, string $lang = 'ar' ): array {
	$lang    = 'en' === $lang ? 'en' : 'ar';
	$menu_id = vava_home_internal_header_menu_id( $homepage_id );
	$items   = $menu_id ? wp_get_nav_menu_items( $menu_id, array( 'update_post_term_cache' => false ) ) : array();
	$output  = array();

	if ( ! is_array( $items ) ) {
		return $output;
	}

	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post || absint( $item->menu_item_parent ?? 0 ) ) {
			continue;
		}

		$url = ( 'page' === (string) ( $item->object ?? '' ) && absint( $item->object_id ?? 0 ) && function_exists( 'vava_localized_page_url' ) )
			? vava_localized_page_url( absint( $item->object_id ), $lang )
			: (string) $item->url;

		$output[] = array(
			'label' => function_exists( 'vava_nav_menu_item_title_for_language' )
				? vava_nav_menu_item_title_for_language( $item, $lang, (string) $item->title )
				: (string) $item->title,
			'url'   => $url,
		);
	}

	return $output;
}

function vava_home_footer_menu_meta_key( string $group, string $lang = 'ar' ): string {
	return '_vava_home_footer_' . $group . '_menu_id' . ( 'en' === $lang ? '_en' : '' );
}

function vava_home_footer_menu_id( int $post_id, string $group, string $lang = 'ar' ): int {
	$shared = absint( get_post_meta( $post_id, vava_home_footer_menu_meta_key( $group, 'ar' ), true ) );
	if ( $shared ) {
		return $shared;
	}
	return absint( get_post_meta( $post_id, vava_home_footer_menu_meta_key( $group, 'en' ), true ) );
}

function vava_home_footer_links( int $post_id, string $group, string $lang = 'ar' ): array {
	$menu_id = vava_home_footer_menu_id( $post_id, $group, $lang );
	if ( $menu_id ) {
		$menu_items = wp_get_nav_menu_items( $menu_id, array( 'update_post_term_cache' => false ) );
		if ( is_array( $menu_items ) ) {
			$links = array();
			foreach ( $menu_items as $menu_item ) {
				if ( ! $menu_item instanceof WP_Post || 'draft' === $menu_item->post_status ) {
					continue;
				}
				$is_page = 'page' === (string) ( $menu_item->object ?? '' ) && absint( $menu_item->object_id ?? 0 );
				$links[] = array(
					'label' => vava_nav_menu_item_title_for_language( $menu_item, $lang, (string) $menu_item->title ),
					'url'   => $is_page ? vava_localized_page_url( absint( $menu_item->object_id ), $lang ) : (string) $menu_item->url,
				);
			}
			return $links;
		}
	}

	// Backward-compatible fallback until a WordPress menu is selected and saved.
	$key   = '_vava_home_footer_' . $group . '_items' . ( 'en' === $lang ? '_en' : '' );
	$value = get_post_meta( $post_id, $key, true );
	if ( ( ! is_array( $value ) || ! $value ) && 'en' === $lang ) {
		$value = get_post_meta( $post_id, '_vava_home_footer_' . $group . '_items', true );
	}
	if ( is_array( $value ) && $value ) {
		return $value;
	}
	return 'policy' === $group ? vava_homepage_footer_policy_defaults( $lang ) : vava_homepage_footer_primary_defaults( $lang );
}

function vava_homepage_social_platforms(): array {
	return array(
		'instagram' => array(
			'label' => 'Instagram',
			'icon'  => '<svg aria-hidden="true" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="5"></rect><circle cx="12" cy="12" r="3.5"></circle><circle cx="17" cy="7" r="1" fill="currentColor" stroke="none"></circle></svg>',
		),
		'facebook' => array(
			'label' => 'Facebook',
			'icon'  => '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M14 8.2h2.2V4.5h-2.8c-3 0-4.7 1.8-4.7 4.9v2.1H6.5v3.8h2.2v4.2h4.1v-4.2h3l.5-3.8h-3.5V9.8c0-1 .4-1.6 1.2-1.6Z" fill="currentColor" stroke="none"></path></svg>',
		),
		'youtube' => array(
			'label' => 'YouTube',
			'icon'  => '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M21 9.2a4 4 0 0 0-.7-2.1c-.5-.6-1.1-.8-1.9-.9C15.7 6 12 6 12 6s-3.7 0-6.4.2c-.8.1-1.5.3-1.9.9A4 4 0 0 0 3 9.2a25 25 0 0 0 0 5.6c.1.8.3 1.5.7 2.1.5.6 1.2.8 2 .9 2.7.2 6.3.2 6.3.2s3.7 0 6.4-.2c.8-.1 1.5-.3 1.9-.9.4-.6.6-1.3.7-2.1a25 25 0 0 0 0-5.6Z"></path><path d="M10.2 9.4v5.2l4.7-2.6-4.7-2.6Z" fill="currentColor" stroke="none"></path></svg>',
		),
		'tiktok' => array(
			'label' => 'TikTok',
			'icon'  => '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M14 4v10.2a4.7 4.7 0 1 1-4-4.65"></path><path d="M14 4c.5 2.8 2.2 4.5 5 5"></path></svg>',
		),
		'x' => array(
			'label' => 'X',
			'icon'  => '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 4l14 16M19 4 5 20"></path></svg>',
		),
		'linkedin' => array(
			'label' => 'LinkedIn',
			'icon'  => '<svg aria-hidden="true" viewBox="0 0 24 24"><rect x="4" y="9" width="4" height="11"></rect><circle cx="6" cy="5.5" r="2"></circle><path d="M12 20V9h4v1.7c1-1.4 2.2-2.1 3.8-2.1 2.7 0 4.2 1.8 4.2 5V20h-4v-5.7c0-1.6-.6-2.4-1.9-2.4-1.4 0-2.1 1-2.1 2.8V20Z" transform="translate(-2 0)"></path></svg>',
		),
		'whatsapp' => array(
			'label' => 'WhatsApp',
			'icon'  => '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20 11.5a8 8 0 0 1-11.7 7.1L4 20l1.4-4.1A8 8 0 1 1 20 11.5Z"></path><path d="M9 8.5c.4 2.8 2 4.5 4.8 5.1.8.2 1.4-.1 1.8-.8l.4-.8-2.3-1.1-.7.8c-1.2-.5-2-1.3-2.5-2.5l.7-.7-1-2.2-.8.3c-.5.2-.6.8-.4 1.9Z"></path></svg>',
		),
		'email' => array(
			'label' => 'Email',
			'icon'  => '<svg aria-hidden="true" viewBox="0 0 24 24"><rect x="4" y="6" width="16" height="12" rx="3"></rect><path d="M5.5 8.2 12 13l6.5-4.8"></path></svg>',
		),
	);
}

function vava_homepage_footer_social_defaults(): array {
	return array(
		array( 'platform' => 'instagram', 'url' => '' ),
		array( 'platform' => 'tiktok', 'url' => '' ),
		array( 'platform' => 'whatsapp', 'url' => '' ),
		array( 'platform' => 'email', 'url' => '' ),
	);
}

function vava_home_social_icon_svg( string $platform ): string {
	$platforms = vava_homepage_social_platforms();
	return (string) ( $platforms[ $platform ]['icon'] ?? '' );
}

function vava_home_social_label( string $platform ): string {
	$platforms = vava_homepage_social_platforms();
	return (string) ( $platforms[ $platform ]['label'] ?? ucfirst( $platform ) );
}

/** Normalize a social value without treating email addresses as web URLs. */
function vava_home_normalize_social_value( string $platform, string $value ): string {
	$platform = sanitize_key( $platform );
	$value    = trim( $value );
	if ( 'email' === $platform ) {
		$value = preg_replace( '#^(?:mailto:|https?://)+#i', '', $value );
		$value = ltrim( (string) $value, '/' );
		return sanitize_email( $value );
	}
	return esc_url_raw( $value, array( 'http', 'https', 'mailto', 'tel' ) );
}

function vava_home_footer_social( int $post_id ): array {
	$value     = get_post_meta( $post_id, '_vava_home_footer_social', true );
	$platforms = vava_homepage_social_platforms();
	if ( ! is_array( $value ) ) {
		return vava_homepage_footer_social_defaults();
	}

	$items = array();
	foreach ( $value as $key => $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		// V1R4 stored platforms as associative keys with an enabled flag.
		$platform = isset( $item['platform'] ) ? sanitize_key( (string) $item['platform'] ) : sanitize_key( (string) $key );
		if ( ! isset( $platforms[ $platform ] ) || ( isset( $item['enabled'] ) && empty( $item['enabled'] ) ) ) {
			continue;
		}
		$items[] = array(
			'platform' => $platform,
			'url'      => vava_home_normalize_social_value( $platform, (string) ( $item['url'] ?? '' ) ),
		);
	}

	return $items;
}

function vava_home_social_href( array $item ): string {
	$platform = sanitize_key( (string) ( $item['platform'] ?? '' ) );
	$value    = vava_home_normalize_social_value( $platform, (string) ( $item['url'] ?? '' ) );
	if ( 'email' === $platform ) {
		return $value && is_email( $value ) ? 'mailto:' . $value : '';
	}
	return $value;
}

function vava_home_image_url( int $attachment_id, string $fallback_asset, string $size = 'full' ): string {
	if ( $attachment_id > 0 ) {
		$url = wp_get_attachment_image_url( $attachment_id, $size );
		if ( $url ) {
			return $url;
		}
	}
	return vava_asset_uri( $fallback_asset );
}

function vava_homepage_admin_text( string $key, string $lang = 'ar' ): string {
	$texts = array(
		'meta_box_title'       => array( 'ar' => 'إعدادات الصفحة الرئيسية', 'en' => 'Homepage Settings' ),
		'intro_title'          => array( 'ar' => 'محتوى الصفحة الرئيسية يُدار من الحقول التالية.', 'en' => 'Manage the homepage content using the fields below.' ),
		'intro_description'    => array( 'ar' => 'يمكن تعديل أكثر من تبويب وأكثر من لغة، ثم حفظ جميع التغييرات معًا بضغطة تحديث واحدة.', 'en' => 'Edit multiple tabs and both languages, then save all changes together with one update.' ),
		'sections_aria'        => array( 'ar' => 'أقسام الصفحة الرئيسية', 'en' => 'Homepage sections' ),
		'fields_language'      => array( 'ar' => 'لغة الحقول', 'en' => 'Fields language' ),
		'update'               => array( 'ar' => 'تحديث', 'en' => 'Update' ),
		'shared_settings'      => array( 'ar' => 'ملفات وإعدادات مشتركة بين اللغتين', 'en' => 'Files and settings shared between both languages' ),
		'choose_replace'       => array( 'ar' => 'اختيار أو استبدال', 'en' => 'Choose or replace' ),
		'delete_file'          => array( 'ar' => 'حذف الملف', 'en' => 'Delete file' ),
		'drag_image'           => array( 'ar' => 'اسحب الصورة وأفلتها هنا', 'en' => 'Drag and drop the image here' ),
		'drag_video'           => array( 'ar' => 'اسحب ملف الفيديو وأفلته هنا', 'en' => 'Drag and drop the video here' ),
		'choose_library'       => array( 'ar' => 'أو اضغط للاختيار من مكتبة الوسائط', 'en' => 'Or click to choose from the media library' ),
		'button_text'          => array( 'ar' => 'نص الزر', 'en' => 'Button text' ),
		'link_type'            => array( 'ar' => 'نوع الرابط', 'en' => 'Link type' ),
		'internal_page'        => array( 'ar' => 'صفحة', 'en' => 'Page' ),
		'manual_link'          => array( 'ar' => 'رابط يدوي', 'en' => 'Manual link' ),
		'choose_page'          => array( 'ar' => '— اختر صفحة —', 'en' => '— Choose a page —' ),
		'untitled'             => array( 'ar' => '(بدون عنوان)', 'en' => '(Untitled)' ),
		'video'                => array( 'ar' => 'فيديو', 'en' => 'Video' ),
		'image'                => array( 'ar' => 'صورة', 'en' => 'Image' ),
		'testimonials_heading' => array( 'ar' => 'تجارب العملاء', 'en' => 'Customer testimonials' ),
		'add_testimonial'      => array( 'ar' => 'أضف تجربة جديدة', 'en' => 'Add testimonial' ),
		'testimonial'          => array( 'ar' => 'التجربة', 'en' => 'Testimonial' ),
		'testimonial_text'     => array( 'ar' => 'نص التجربة', 'en' => 'Testimonial text' ),
		'testimonial_author'   => array( 'ar' => 'اسم/وصف العميل', 'en' => 'Customer name/description' ),
		'journal_source'       => array( 'ar' => 'مصدر بطاقات المقالات', 'en' => 'Article cards source' ),
		'journal_help'         => array( 'ar' => 'العنوان والرابط والتصنيف والصورة البارزة تُجلب تلقائيًا من المقالات.', 'en' => 'Title, link, category, and featured image are loaded automatically from posts.' ),
		'display_method'       => array( 'ar' => 'طريقة العرض', 'en' => 'Display method' ),
		'latest_posts'         => array( 'ar' => 'آخر 3 مقالات من تصنيف محدد', 'en' => 'Latest 3 posts from a selected category' ),
		'random_posts'         => array( 'ar' => '3 مقالات عشوائية من تصنيف أو عدة تصنيفات', 'en' => '3 random posts from one or more categories' ),
		'category'             => array( 'ar' => 'التصنيف', 'en' => 'Category' ),
		'choose_category'      => array( 'ar' => '— اختر تصنيفًا —', 'en' => '— Choose a category —' ),
		'random_categories'    => array( 'ar' => 'التصنيفات المتاحة للاختيار العشوائي', 'en' => 'Categories available for random selection' ),
		'multi_select_help'    => array( 'ar' => 'يمكن اختيار أكثر من تصنيف باستخدام Ctrl أو Command.', 'en' => 'Select multiple categories using Ctrl or Command.' ),
		'internal_header_menu' => array( 'ar' => 'القائمة الرئيسية في هيدر الصفحات الداخلية', 'en' => 'Internal pages header menu' ),
		'internal_header_help' => array( 'ar' => 'اختر القائمة التي ستظهر في هيدر جميع الصفحات الداخلية. هيدر الصفحة الرئيسية وروابط أقسامها لن يتغيرا.', 'en' => 'Choose the menu shown in every internal-page header. The homepage header and its section links remain unchanged.' ),
		'footer_primary_menu'  => array( 'ar' => 'القائمة الرئيسية في الفوتر', 'en' => 'Primary footer menu' ),
		'footer_policy_menu'   => array( 'ar' => 'قائمة روابط السياسات', 'en' => 'Policy links menu' ),
		'choose_wp_menu'       => array( 'ar' => '— اختر قائمة من WordPress —', 'en' => '— Choose a WordPress menu —' ),
		'manage_wp_menus'      => array( 'ar' => 'إدارة قوائم WordPress', 'en' => 'Manage WordPress menus' ),
		'document_label'       => array( 'ar' => 'عنوان الوثيقة', 'en' => 'Document label' ),
		'document_number'      => array( 'ar' => 'رقم الوثيقة', 'en' => 'Document number' ),
		'social_heading'       => array( 'ar' => 'أيقونات وروابط التواصل الاجتماعي', 'en' => 'Social media icons and links' ),
		'social_help'          => array( 'ar' => 'اختر المنصة ثم أضف رابطها. تتغير الأيقونة تلقائيًا في لوحة التحكم والفوتر.', 'en' => 'Choose a platform and add its link. The icon updates automatically in the dashboard and footer.' ),
		'order'                => array( 'ar' => 'الترتيب', 'en' => 'Order' ),
		'platform'             => array( 'ar' => 'المنصة', 'en' => 'Platform' ),
		'link'                 => array( 'ar' => 'الرابط', 'en' => 'Link' ),
		'action'               => array( 'ar' => 'الإجراء', 'en' => 'Action' ),
		'add_platform'         => array( 'ar' => 'إضافة منصة جديدة', 'en' => 'Add platform' ),
	);
	$lang = 'en' === $lang ? 'en' : 'ar';
	return (string) ( $texts[ $key ][ $lang ] ?? $texts[ $key ]['ar'] ?? $key );
}

function vava_homepage_field_label( string $base_key, string $lang = 'ar', string $fallback = '' ): string {
	$labels = array(
		'_vava_home_hero_eyebrow'          => array( 'ar' => 'النص الصغير', 'en' => 'Small text' ),
		'_vava_home_hero_title'            => array( 'ar' => 'العنوان الرئيسي', 'en' => 'Main title' ),
		'_vava_home_hero_description'      => array( 'ar' => 'الوصف', 'en' => 'Description' ),
		'_vava_home_hero_button_text'      => array( 'ar' => 'نص الزر', 'en' => 'Button text' ),
		'_vava_home_hero_media_type'       => array( 'ar' => 'نوع خلفية الهيرو', 'en' => 'Hero background type' ),
		'_vava_home_hero_image_id'         => array( 'ar' => 'صورة الهيرو', 'en' => 'Hero image' ),
		'_vava_home_hero_video_id'         => array( 'ar' => 'فيديو الهيرو', 'en' => 'Hero video' ),
		'_vava_home_hero_poster_id'        => array( 'ar' => 'صورة بوستر الفيديو', 'en' => 'Video poster image' ),
		'_vava_home_paths_title'           => array( 'ar' => 'العنوان', 'en' => 'Title' ),
		'_vava_home_paths_description'     => array( 'ar' => 'الوصف', 'en' => 'Description' ),
		'_vava_home_paths_button_text'     => array( 'ar' => 'نص الزر', 'en' => 'Button text' ),
		'_vava_home_paths_image_id'        => array( 'ar' => 'الصورة الجمالية', 'en' => 'Visual image' ),
		'_vava_home_shop_eyebrow'          => array( 'ar' => 'النص الصغير', 'en' => 'Small text' ),
		'_vava_home_shop_title'            => array( 'ar' => 'العنوان', 'en' => 'Title' ),
		'_vava_home_shop_subtitle'         => array( 'ar' => 'العنوان الفرعي', 'en' => 'Subtitle' ),
		'_vava_home_shop_description'      => array( 'ar' => 'الوصف', 'en' => 'Description' ),
		'_vava_home_shop_button_text'      => array( 'ar' => 'نص الزر', 'en' => 'Button text' ),
		'_vava_home_testimonials_label'    => array( 'ar' => 'العنوان الصغير', 'en' => 'Small title' ),
		'_vava_home_testimonials_title'    => array( 'ar' => 'العنوان', 'en' => 'Title' ),
		'_vava_home_testimonials_intro'    => array( 'ar' => 'الوصف', 'en' => 'Description' ),
		'_vava_home_journal_title'         => array( 'ar' => 'العنوان', 'en' => 'Title' ),
		'_vava_home_journal_subtitle'      => array( 'ar' => 'العنوان الفرعي', 'en' => 'Subtitle' ),
		'_vava_home_journal_description'   => array( 'ar' => 'الوصف', 'en' => 'Description' ),
		'_vava_home_journal_small_text'    => array( 'ar' => 'النص الإضافي', 'en' => 'Additional text' ),
		'_vava_home_journal_visual_caption'=> array( 'ar' => 'عبارة البلوك الزجاجي', 'en' => 'Glass card caption' ),
		'_vava_home_journal_image_id'      => array( 'ar' => 'صورة المجلة', 'en' => 'Journal image' ),
		'_vava_home_journal_button_text'   => array( 'ar' => 'نص الزر', 'en' => 'Button text' ),
		'_vava_home_contact_title'         => array( 'ar' => 'العنوان', 'en' => 'Title' ),
		'_vava_home_contact_description'   => array( 'ar' => 'الوصف', 'en' => 'Description' ),
		'_vava_home_contact_button_text'   => array( 'ar' => 'نص الزر', 'en' => 'Button text' ),
		'_vava_home_contact_image_id'      => array( 'ar' => 'صورة قسم التواصل', 'en' => 'Contact section image' ),
		'_vava_about_hero_image_id'        => array( 'ar' => 'صورة الهيرو', 'en' => 'Hero image' ),
		'_vava_about_why_image_id'         => array( 'ar' => 'صورة قسم لماذا VAVA؟', 'en' => 'Why VAVA? section image' ),
		'_vava_home_footer_tagline'        => array( 'ar' => 'عبارة أسفل الشعار', 'en' => 'Tagline below the logo' ),
		'_vava_home_footer_copyright'      => array( 'ar' => 'نص حقوق النشر', 'en' => 'Copyright text' ),
		'_vava_home_footer_document_label' => array( 'ar' => 'عنوان الوثيقة', 'en' => 'Document label' ),
		'_vava_home_footer_document_number'=> array( 'ar' => 'رقم الوثيقة', 'en' => 'Document number' ),
	);
	$lang = 'en' === $lang ? 'en' : 'ar';
	return (string) ( $labels[ $base_key ][ $lang ] ?? $fallback );
}

function vava_homepage_sections( string $lang = 'ar' ): array {
	$is_en = 'en' === $lang;
	return array(
		'hero'         => array( 'label' => $is_en ? 'Hero' : 'الهيرو', 'label_ar' => 'الهيرو', 'label_en' => 'Hero' ),
		'paths'        => array( 'label' => $is_en ? 'VAVA Pathways' : 'مسارات VAVA', 'label_ar' => 'مسارات VAVA', 'label_en' => 'VAVA Pathways' ),
		'shop'         => array( 'label' => $is_en ? 'VAVA Selections' : 'مختارات VAVA', 'label_ar' => 'مختارات VAVA', 'label_en' => 'VAVA Selections' ),
		'testimonials' => array( 'label' => $is_en ? 'Testimonials' : 'التجارب', 'label_ar' => 'التجارب', 'label_en' => 'Testimonials' ),
		'journal'      => array( 'label' => $is_en ? 'Journal' : 'المجلة', 'label_ar' => 'المجلة', 'label_en' => 'Journal' ),
		'contact'      => array( 'label' => $is_en ? 'Contact' : 'تواصل', 'label_ar' => 'تواصل', 'label_en' => 'Contact' ),
		'footer'       => array( 'label' => $is_en ? 'Footer' : 'الفوتر', 'label_ar' => 'الفوتر', 'label_en' => 'Footer' ),
	);
}

/** Return trusted inline SVG icons for the homepage settings tabs. */
function vava_homepage_section_icon_svg( string $section ): string {
	$icons = array(
		'hero' => '<svg viewBox="0 0 32 32"><rect x="3.5" y="5" width="25" height="22" rx="3"/><circle cx="11" cy="12" r="2.3"/><path d="m6 23 7-7 5 5 3-3 5 5"/></svg>',
		'paths' => '<svg viewBox="0 0 32 32"><circle cx="7" cy="24" r="2.5"/><circle cx="25" cy="8" r="2.5"/><path d="M9.5 23c5-1 3-7 8-8s3-5 5-6"/><path d="m18 6 4-2 4 2"/></svg>',
		'shop' => '<svg viewBox="0 0 32 32"><path d="M7 11h18l-1.5 16h-15Z"/><path d="M12 12V9a4 4 0 0 1 8 0v3"/><path d="M11 17h10"/></svg>',
		'testimonials' => '<svg viewBox="0 0 32 32"><circle cx="11" cy="11" r="4"/><circle cx="22" cy="12" r="3.5"/><path d="M4 26c.6-5 3-8 7-8s6.5 3 7 8"/><path d="M18 26c.4-4 2-6.5 5-6.5 2.7 0 4.5 2.2 5 6.5"/></svg>',
		'journal' => '<svg viewBox="0 0 32 32"><path d="M5 6h9a4 4 0 0 1 4 4v17a5 5 0 0 0-5-4H5Z"/><path d="M27 6h-9a4 4 0 0 0-4 4v17a5 5 0 0 1 5-4h8Z"/></svg>',
		'contact' => '<svg viewBox="0 0 32 32"><rect x="4" y="7" width="24" height="18" rx="3"/><path d="m6 10 10 8 10-8"/></svg>',
		'footer' => '<svg viewBox="0 0 32 32"><rect x="4" y="5" width="24" height="22" rx="3"/><path d="M4 20h24"/><path d="M9 24h5m4 0h5"/></svg>',
	);
	return $icons[ $section ] ?? $icons['hero'];
}

function vava_homepage_button_sections(): array {
	return array(
		'hero'    => '_vava_home_hero_button_url',
		'paths'   => '_vava_home_paths_button_url',
		'shop'    => '_vava_home_shop_button_url',
		'journal' => '_vava_home_journal_button_url',
		'contact' => '_vava_home_contact_button_url',
	);
}

function vava_homepage_button_text_key( string $section ): string {
	$buttons = vava_homepage_button_sections();
	return isset( $buttons[ $section ] ) ? str_replace( '_button_url', '_button_text', $buttons[ $section ] ) : '';
}

function vava_homepage_link_key( string $legacy_url_key, string $part, string $lang ): string {
	$key = str_replace( '_button_url', '_button_' . $part, $legacy_url_key );
	return vava_home_language_key( $key, $lang );
}

function vava_homepage_link_values( int $post_id, string $section, string $lang ): array {
	$buttons     = vava_homepage_button_sections();
	$legacy_base = $buttons[ $section ] ?? '';
	if ( ! $legacy_base ) {
		return array( 'type' => 'manual', 'page_id' => 0, 'manual_url' => '', 'url' => '' );
	}

	$legacy_key = vava_home_language_key( $legacy_base, $lang );
	$defaults   = vava_home_language_defaults( $lang );
	$legacy_url = (string) vava_home_field( $post_id, $legacy_key, $defaults[ $legacy_key ] ?? '' );
	$type_key   = vava_homepage_link_key( $legacy_base, 'link_type', 'ar' );
	$page_key   = vava_homepage_link_key( $legacy_base, 'page_id', 'ar' );
	$manual_key = vava_homepage_link_key( $legacy_base, 'manual_url', 'ar' );
	$type       = (string) get_post_meta( $post_id, $type_key, true );
	$page_id    = absint( get_post_meta( $post_id, $page_key, true ) );
	$manual_url = (string) get_post_meta( $post_id, $manual_key, true );

	$english_type   = (string) get_post_meta( $post_id, vava_homepage_link_key( $legacy_base, 'link_type', 'en' ), true );
	$english_page   = absint( get_post_meta( $post_id, vava_homepage_link_key( $legacy_base, 'page_id', 'en' ), true ) );
	$english_manual = (string) get_post_meta( $post_id, vava_homepage_link_key( $legacy_base, 'manual_url', 'en' ), true );
	$canonical_empty = ! in_array( $type, array( 'page', 'manual' ), true )
		|| ( 'page' === $type && ! $page_id )
		|| ( 'manual' === $type && '' === trim( $manual_url ) );
	$english_ready = ( 'page' === $english_type && $english_page )
		|| ( 'manual' === $english_type && '' !== trim( $english_manual ) );
	if ( $canonical_empty && $english_ready ) {
		$type       = $english_type;
		$page_id    = $english_page;
		$manual_url = $english_manual;
	}

	if ( ! in_array( $type, array( 'page', 'manual' ), true ) ) {
		$detected = $legacy_url ? url_to_postid( $legacy_url ) : 0;
		$type     = $detected ? 'page' : 'manual';
		$page_id  = $page_id ?: absint( $detected );
	}
	if ( '' === $manual_url ) {
		$manual_url = $legacy_url;
	}
	$url = 'page' === $type && $page_id ? vava_localized_page_url( $page_id, $lang ) : $manual_url;
	return array(
		'type'       => $type,
		'page_id'    => $page_id,
		'manual_url' => $manual_url,
		'url'        => $url ?: $legacy_url,
	);
}

function vava_home_button_url( int $post_id, string $section, string $lang = 'ar' ): string {
	$values = vava_homepage_link_values( $post_id, $section, $lang );
	$url    = (string) $values['url'];
	return function_exists( 'vava_normalize_internal_url' ) ? vava_normalize_internal_url( $url ) : $url;
}

function vava_homepage_section_fields( string $section ): array {
	$map = array(
		'hero' => array(
			array( '_vava_home_hero_eyebrow', 'النص الصغير', 'text', true ),
			array( '_vava_home_hero_title', 'العنوان الرئيسي', 'text', true ),
			array( '_vava_home_hero_description', 'الوصف', 'textarea', true ),
			array( '_vava_home_hero_button_text', 'نص الزر', 'text', true ),
			array( '_vava_home_hero_media_type', 'نوع خلفية الهيرو', 'media_type', false ),
			array( '_vava_home_hero_image_id', 'صورة الهيرو', 'image', false ),
			array( '_vava_home_hero_video_id', 'فيديو الهيرو', 'video', false ),
			array( '_vava_home_hero_poster_id', 'صورة بوستر الفيديو', 'image', false ),
		),
		'paths' => array(
			array( '_vava_home_paths_title', 'العنوان', 'text', true ),
			array( '_vava_home_paths_description', 'الوصف', 'textarea', true ),
			array( '_vava_home_paths_button_text', 'نص الزر', 'text', true ),
			array( '_vava_home_paths_image_id', 'الصورة الجمالية', 'image', false ),
		),
		'shop' => array(
			array( '_vava_home_shop_eyebrow', 'النص الصغير', 'text', true ),
			array( '_vava_home_shop_title', 'العنوان', 'text', true ),
			array( '_vava_home_shop_subtitle', 'العنوان الفرعي', 'text', true ),
			array( '_vava_home_shop_description', 'الوصف', 'textarea', true ),
			array( '_vava_home_shop_button_text', 'نص الزر', 'text', true ),
			array( '_vava_home_shop_image_id', 'صورة مختارات فافا', 'image', false ),
		),
		'testimonials' => array(
			array( '_vava_home_testimonials_label', 'العنوان الصغير', 'text', true ),
			array( '_vava_home_testimonials_title', 'العنوان', 'text', true ),
			array( '_vava_home_testimonials_intro', 'الوصف', 'textarea', true ),
		),
		'journal' => array(
			array( '_vava_home_journal_title', 'العنوان', 'text', true ),
			array( '_vava_home_journal_subtitle', 'العنوان الفرعي', 'text', true ),
			array( '_vava_home_journal_description', 'الوصف', 'textarea', true ),
			array( '_vava_home_journal_visual_caption', 'عبارة البلوك الزجاجي', 'text', true ),
			array( '_vava_home_journal_button_text', 'نص الزر', 'text', true ),
			array( '_vava_home_journal_image_id', 'صورة المجلة', 'image', false ),
		),
		'contact' => array(
			array( '_vava_home_contact_title', 'العنوان', 'text', true ),
			array( '_vava_home_contact_description', 'الوصف', 'textarea', true ),
			array( '_vava_home_contact_button_text', 'نص الزر', 'text', true ),
			array( '_vava_home_contact_image_id', 'صورة قسم التواصل', 'image', false ),
		),
		'footer' => array(
			array( '_vava_home_footer_tagline', 'عبارة أسفل الشعار', 'text', true ),
			array( '_vava_home_footer_copyright', 'نص حقوق النشر', 'text', true ),
			array( '_vava_home_footer_document_label', 'عنوان الوثيقة', 'text', true ),
			array( '_vava_home_footer_document_number', 'رقم الوثيقة', 'text', false ),
		),
	);
	return $map[ $section ] ?? array();
}

function vava_homepage_add_meta_boxes( string $post_type, WP_Post $post ): void {
	if ( 'page' !== $post_type || ! vava_homepage_is_home_page( (int) $post->ID ) ) {
		return;
	}
	remove_meta_box( 'postdivrich', 'page', 'normal' );
	remove_meta_box( 'postimagediv', 'page', 'side' );
	add_meta_box( 'vava_homepage_settings', vava_homepage_admin_text( 'meta_box_title', 'ar' ), 'vava_homepage_render_settings', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'vava_homepage_add_meta_boxes', 10, 2 );

function vava_homepage_locked_postbox_classes( array $classes ): array {
	$classes[] = 'vava-homepage-locked-postbox';
	return array_values( array_unique( $classes ) );
}
add_filter( 'postbox_classes_page_vava_homepage_settings', 'vava_homepage_locked_postbox_classes' );

function vava_homepage_media_preview_markup( int $attachment_id, string $media_type ): string {
	if ( $attachment_id <= 0 ) {
		$icon = 'video' === $media_type
			? '<svg viewBox="0 0 48 48"><rect x="5" y="9" width="38" height="30" rx="5"/><path d="m20 17 12 7-12 7Z"/></svg>'
			: '<svg viewBox="0 0 48 48"><rect x="5" y="7" width="38" height="34" rx="5"/><circle cx="17" cy="18" r="4"/><path d="m9 36 11-11 8 8 5-5 7 8"/></svg>';
		$text_ar = 'video' === $media_type ? vava_homepage_admin_text( 'drag_video', 'ar' ) : vava_homepage_admin_text( 'drag_image', 'ar' );
		$text_en = 'video' === $media_type ? vava_homepage_admin_text( 'drag_video', 'en' ) : vava_homepage_admin_text( 'drag_image', 'en' );
		return '<div class="vava-media-empty">' . $icon . '<strong' . vava_admin_i18n_attributes( $text_ar, $text_en ) . '>' . esc_html( $text_ar ) . '</strong><span' . vava_admin_i18n_attributes( vava_homepage_admin_text( 'choose_library', 'ar' ), vava_homepage_admin_text( 'choose_library', 'en' ) ) . '>' . esc_html( vava_homepage_admin_text( 'choose_library', 'ar' ) ) . '</span></div>';
	}
	$url = wp_get_attachment_url( $attachment_id );
	if ( ! $url ) {
		return vava_homepage_media_preview_markup( 0, $media_type );
	}
	if ( 'video' === $media_type ) {
		return '<video controls preload="metadata" src="' . esc_url( $url ) . '"></video><span class="vava-media-file-name">' . esc_html( get_the_title( $attachment_id ) ) . '</span>';
	}
	$image = wp_get_attachment_image_url( $attachment_id, 'medium_large' );
	return '<img alt="" src="' . esc_url( $image ?: $url ) . '"/><span class="vava-media-file-name">' . esc_html( get_the_title( $attachment_id ) ) . '</span>';
}

function vava_homepage_media_fallback_url( string $key ): string {
	$fallbacks = array(
		'_vava_home_hero_image_id'  => vava_asset_uri( 'assets/images/home-hero-video-poster.jpg' ),
		'_vava_home_hero_video_id'  => vava_asset_uri( 'assets/videos/home-hero-video.mp4' ),
		'_vava_home_hero_poster_id' => vava_asset_uri( 'assets/images/home-hero-video-poster.jpg' ),
		'_vava_home_paths_image_id'   => vava_asset_uri( 'assets/images/home-paths-vava-visual.webp' ),
		'_vava_home_journal_image_id' => vava_asset_uri( 'assets/images/home-journal-editorial.webp' ),
		'_vava_home_contact_image_id' => vava_asset_uri( 'assets/images/contact-section-visual.jpg' ),
		'_vava_about_hero_image_id'   => vava_asset_uri( 'assets/images/about-hero.png' ),
		'_vava_about_why_image_id'    => vava_asset_uri( 'assets/images/about-why-vava.png' ),
	);
	return (string) ( $fallbacks[ $key ] ?? '' );
}

function vava_homepage_media_current_url( int $attachment_id, string $media_type, string $fallback = '' ): string {
	if ( $attachment_id > 0 ) {
		$url = 'image' === $media_type ? wp_get_attachment_image_url( $attachment_id, 'medium_large' ) : wp_get_attachment_url( $attachment_id );
		if ( $url ) {
			return (string) $url;
		}
	}
	return $fallback;
}

function vava_homepage_render_media_field( string $key, string $label, string $media_type, int $attachment_id, string $conditional = '' ): void {
	$id               = sanitize_html_class( ltrim( $key, '_' ) );
	$conditional_attr = $conditional ? ' data-hero-media="' . esc_attr( $conditional ) . '"' : '';
	$fallback_url     = vava_homepage_media_fallback_url( $key );
	$current_url      = vava_homepage_media_current_url( $attachment_id, $media_type, $fallback_url );
	?>
	<div class="vava-admin-field vava-admin-field-media vava-admin-field-wide"<?php echo $conditional_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> >
		<label for="<?php echo esc_attr( $id ); ?>"><strong<?php echo vava_admin_i18n_attributes( vava_homepage_field_label( $key, 'ar', $label ), vava_homepage_field_label( $key, 'en', $label ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_field_label( $key, 'ar', $label ) ); ?></strong></label>
		<div class="vava-media-field" data-media-type="<?php echo esc_attr( $media_type ); ?>">
			<input class="vava-media-id" data-fallback-url="<?php echo esc_url( $fallback_url ); ?>" data-media-url="<?php echo esc_url( $current_url ); ?>" data-vava-preview-media="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $key ); ?>" type="hidden" value="<?php echo esc_attr( (string) $attachment_id ); ?>"/>
			<div class="vava-media-dropzone" data-media-type="<?php echo esc_attr( $media_type ); ?>" data-target="<?php echo esc_attr( $id ); ?>" role="button" tabindex="0">
				<div class="vava-media-preview"><?php echo vava_homepage_media_preview_markup( $attachment_id, $media_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<div class="vava-upload-progress" aria-hidden="true"><span></span></div>
			</div>
			<div class="vava-media-actions">
				<button class="button button-secondary vava-media-select" data-media-type="<?php echo esc_attr( $media_type ); ?>" data-target="<?php echo esc_attr( $id ); ?>" type="button"<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'choose_replace', 'ar' ), vava_homepage_admin_text( 'choose_replace', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'choose_replace', 'ar' ) ); ?></button>
				<button class="button button-secondary vava-media-remove" data-media-type="<?php echo esc_attr( $media_type ); ?>" data-target="<?php echo esc_attr( $id ); ?>" type="button"<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'delete_file', 'ar' ), vava_homepage_admin_text( 'delete_file', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'delete_file', 'ar' ) ); ?></button>
			</div>
		</div>
	</div>
	<?php
}

function vava_homepage_render_link_selector( WP_Post $post, string $section, string $lang ): void {
	$buttons = vava_homepage_button_sections();
	if ( empty( $buttons[ $section ] ) ) {
		return;
	}
	$legacy_base = $buttons[ $section ];
	$text_base   = vava_homepage_button_text_key( $section );
	$text_key    = vava_home_language_key( $text_base, $lang );
	$defaults    = vava_home_language_defaults( $lang );
	$text_value  = vava_home_field( $post->ID, $text_key, $defaults[ $text_key ] ?? '' );
	$values      = vava_homepage_link_values( $post->ID, $section, $lang );
	$type_key    = vava_homepage_link_key( $legacy_base, 'link_type', $lang );
	$page_key    = vava_homepage_link_key( $legacy_base, 'page_id', $lang );
	$manual_key  = vava_homepage_link_key( $legacy_base, 'manual_url', $lang );
	$uid         = sanitize_html_class( $section . '_' . $lang . '_button' );
	$pages       = get_pages( array( 'post_status' => array( 'publish', 'draft', 'private' ), 'sort_column' => 'post_title', 'sort_order' => 'ASC' ) );
	$dir         = 'en' === $lang ? 'ltr' : 'rtl';
	?>
	<div class="vava-admin-field vava-button-control vava-admin-field-wide" data-link-selector data-preview-link-section="<?php echo esc_attr( $section ); ?>" data-vava-shared-setting="homepage-link-<?php echo esc_attr( $section ); ?>">
		<div class="vava-button-control-row">
			<label for="<?php echo esc_attr( $uid . '_text' ); ?>"><strong><?php echo esc_html( vava_homepage_admin_text( 'button_text', $lang ) ); ?></strong></label>
			<input class="widefat" data-vava-preview-field="<?php echo esc_attr( $text_base ); ?>" dir="<?php echo esc_attr( $dir ); ?>" id="<?php echo esc_attr( $uid . '_text' ); ?>" name="<?php echo esc_attr( $text_key ); ?>" type="text" value="<?php echo esc_attr( (string) $text_value ); ?>"/>
			<label for="<?php echo esc_attr( $uid . '_type' ); ?>"><strong><?php echo esc_html( vava_homepage_admin_text( 'link_type', $lang ) ); ?></strong></label>
			<select data-link-type-control id="<?php echo esc_attr( $uid . '_type' ); ?>" name="<?php echo esc_attr( $type_key ); ?>">
				<option value="page" <?php selected( 'page', $values['type'] ); ?>><?php echo esc_html( vava_homepage_admin_text( 'internal_page', $lang ) ); ?></option>
				<option value="manual" <?php selected( 'manual', $values['type'] ); ?>><?php echo esc_html( vava_homepage_admin_text( 'manual_link', $lang ) ); ?></option>
			</select>
			<div class="vava-link-pane" data-link-pane="page">
				<select class="widefat" aria-label="<?php echo esc_attr( vava_homepage_admin_text( 'choose_page', $lang ) ); ?>" name="<?php echo esc_attr( $page_key ); ?>">
					<option value="0"><?php echo esc_html( vava_homepage_admin_text( 'choose_page', $lang ) ); ?></option>
					<?php foreach ( $pages as $page ) : ?><option data-permalink="<?php echo esc_url( get_permalink( $page->ID ) ); ?>" value="<?php echo esc_attr( (string) $page->ID ); ?>" <?php selected( (int) $values['page_id'], (int) $page->ID ); ?>><?php echo esc_html( vava_bilingual_page_title( (int) $page->ID, $lang ) ?: vava_homepage_admin_text( 'untitled', $lang ) ); ?></option><?php endforeach; ?>
				</select>
			</div>
			<div class="vava-link-pane" data-link-pane="manual">
				<input class="widefat" aria-label="<?php echo esc_attr( vava_homepage_admin_text( 'manual_link', $lang ) ); ?>" dir="ltr" name="<?php echo esc_attr( $manual_key ); ?>" placeholder="https://example.com/" type="url" value="<?php echo esc_attr( (string) $values['manual_url'] ); ?>"/>
			</div>
		</div>
	</div>
	<?php
}

function vava_homepage_render_field( WP_Post $post, array $field, string $lang = 'ar' ): void {
	list( $base_key, $label, $type, $translatable ) = $field;
	$label = vava_homepage_field_label( $base_key, $lang, $label );
	$key      = $translatable ? vava_home_language_key( $base_key, $lang ) : $base_key;
	$defaults = $translatable ? vava_home_language_defaults( $lang ) : vava_homepage_defaults();
	$value    = vava_home_field( $post->ID, $key, $defaults[ $key ] ?? '' );
	if ( '_vava_home_shop_description' === $base_key ) { $value = vava_home_shop_description( (int) $post->ID, $lang ); }
	$id       = sanitize_html_class( ltrim( $key, '_' ) );
	$dir      = 'en' === $lang && $translatable ? 'ltr' : 'rtl';

	if ( in_array( $type, array( 'image', 'video' ), true ) ) {
		$conditional = '';
		if ( '_vava_home_hero_image_id' === $base_key ) {
			$conditional = 'image';
		} elseif ( in_array( $base_key, array( '_vava_home_hero_video_id', '_vava_home_hero_poster_id' ), true ) ) {
			$conditional = 'video';
		}
		vava_homepage_render_media_field( $key, $label, $type, absint( $value ), $conditional );
		return;
	}
	if ( 'media_type' === $type ) {
		$current = in_array( $value, array( 'image', 'video' ), true ) ? $value : 'video';
		?>
		<div class="vava-admin-field vava-admin-field-media-type vava-admin-field-wide">
			<div class="vava-media-type-switch" role="radiogroup" aria-label="<?php echo esc_attr( vava_homepage_field_label( $base_key, 'ar', $label ) ); ?>">
				<label><input <?php checked( 'video', $current ); ?> data-vava-preview-field="<?php echo esc_attr( $base_key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="radio" value="video"/><span><svg viewBox="0 0 28 28"><rect x="3" y="5" width="22" height="18" rx="3"/><path d="m11 10 7 4-7 4Z"/></svg><span<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'video', 'ar' ), vava_homepage_admin_text( 'video', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'video', 'ar' ) ); ?></span></span></label>
				<label><input <?php checked( 'image', $current ); ?> data-vava-preview-field="<?php echo esc_attr( $base_key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="radio" value="image"/><span><svg viewBox="0 0 28 28"><rect x="3" y="4" width="22" height="20" rx="3"/><circle cx="10" cy="10" r="2"/><path d="m6 21 6-6 4 4 3-3 4 5"/></svg><span<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'image', 'ar' ), vava_homepage_admin_text( 'image', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'image', 'ar' ) ); ?></span></span></label>
			</div>
		</div>
		<?php
		return;
	}
	?>
	<div class="vava-admin-field vava-admin-field-<?php echo esc_attr( $type ); ?>">
		<label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
		<?php if ( 'textarea' === $type ) : ?>
			<?php vava_render_richtext_editor( array( 'name' => $key, 'id' => $id, 'value' => (string) $value, 'dir' => $dir, 'preview' => $base_key, 'preview_namespace' => 'home' ) ); ?>
		<?php else : ?>
			<input class="widefat" data-vava-preview-field="<?php echo esc_attr( $base_key ); ?>" dir="<?php echo esc_attr( $dir ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $key ); ?>" type="text" value="<?php echo esc_attr( (string) $value ); ?>"/>
		<?php endif; ?>
	</div>
	<?php
}

function vava_homepage_render_testimonial_item( string $key, array $item, $index, string $lang, bool $template = false ): void {
	$index_value = $template ? '__INDEX__' : (string) absint( $index );
	$number      = $template ? '1' : (string) ( absint( $index ) + 1 );
	$dir         = 'en' === $lang ? 'ltr' : 'rtl';
	?>
	<?php $collapsed = ! $template && absint( $index ) > 0; ?>
	<article class="vava-repeater-item<?php echo $collapsed ? ' is-collapsed' : ''; ?>" data-repeater-item>
		<header class="vava-repeater-item-header">
			<button class="vava-repeater-drag" type="button" aria-label="<?php echo esc_attr( 'en' === $lang ? 'Reorder testimonial' : 'إعادة ترتيب التجربة' ); ?>" title="<?php echo esc_attr( 'en' === $lang ? 'Drag to reorder' : 'اسحب لإعادة الترتيب' ); ?>"><span aria-hidden="true">⋮⋮</span></button>
			<strong><span data-testimonial-card-title><?php echo esc_html( (string) ( $item['author'] ?? '' ) ?: vava_homepage_admin_text( 'testimonial', $lang ) . ' ' . $number ); ?></span><span class="vava-testimonial-card-number" data-repeater-number><?php echo esc_html( $number ); ?></span></strong>
			<div class="vava-repeater-actions">
				<button class="vava-icon-button vava-repeater-toggle" type="button" aria-expanded="<?php echo $collapsed ? 'false' : 'true'; ?>" aria-label="<?php echo esc_attr( $collapsed ? ( 'en' === $lang ? 'Expand testimonial' : 'فتح التجربة' ) : ( 'en' === $lang ? 'Collapse testimonial' : 'طي التجربة' ) ); ?>" title="<?php echo esc_attr( $collapsed ? ( 'en' === $lang ? 'Expand testimonial' : 'فتح التجربة' ) : ( 'en' === $lang ? 'Collapse testimonial' : 'طي التجربة' ) ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m6 15 6-6 6 6"/></svg></button>
				<button class="vava-icon-button vava-icon-button-danger vava-repeater-remove" type="button" aria-label="<?php echo esc_attr( 'en' === $lang ? 'Delete testimonial' : 'حذف التجربة' ); ?>" title="<?php echo esc_attr( 'en' === $lang ? 'Delete testimonial' : 'حذف التجربة' ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="m7 7 1 13h8l1-13"/><path d="M10 11v5M14 11v5"/></svg></button>
			</div>
		</header>
		<div class="vava-repeater-item-body">
			<div class="vava-repeater-field vava-repeater-field-wide">
				<label><?php echo esc_html( vava_homepage_admin_text( 'testimonial_text', $lang ) ); ?></label>
				<?php vava_render_richtext_editor( array( 'name' => $key . '[' . $index_value . '][text]', 'id' => sanitize_html_class( $key . '_' . $index_value . '_text' ), 'value' => (string) ( $item['text'] ?? '' ), 'dir' => $dir, 'class' => 'vava-testimonial-richtext' ) ); ?>
			</div>
			<div class="vava-repeater-field">
				<label><?php echo esc_html( vava_homepage_admin_text( 'testimonial_author', $lang ) ); ?></label>
				<input class="widefat" dir="<?php echo esc_attr( $dir ); ?>" data-testimonial-field="author" name="<?php echo esc_attr( $key . '[' . $index_value . '][author]' ); ?>" type="text" value="<?php echo esc_attr( (string) ( $item['author'] ?? '' ) ); ?>"/>
			</div>
		</div>
	</article>
	<?php
}

function vava_homepage_render_testimonials( WP_Post $post, string $lang ): void {
	$items = vava_home_testimonials( $post->ID, $lang );
	$key   = 'en' === $lang ? '_vava_home_testimonials_items_en' : '_vava_home_testimonials_items';
	?>
	<div class="vava-admin-repeaters vava-dynamic-repeater" data-repeater data-repeater-kind="testimonials" data-name-base="<?php echo esc_attr( $key ); ?>">
		<input type="hidden" name="<?php echo esc_attr( $key . '_present' ); ?>" value="1"/>
		<div class="vava-repeater-heading"><h3><?php echo esc_html( vava_homepage_admin_text( 'testimonials_heading', $lang ) ); ?></h3><button class="button vava-repeater-add vava-testimonial-add-button" type="button"><span aria-hidden="true">＋</span><?php echo esc_html( vava_homepage_admin_text( 'add_testimonial', $lang ) ); ?></button></div>
		<div class="vava-repeater-list" data-repeater-list>
			<?php foreach ( $items as $index => $item ) { vava_homepage_render_testimonial_item( $key, is_array( $item ) ? $item : array(), $index, $lang ); } ?>
		</div>
		<template data-repeater-template><?php vava_homepage_render_testimonial_item( $key, array( 'text' => '', 'author' => '' ), '__INDEX__', $lang, true ); ?></template>
	</div>
	<?php
}

function vava_homepage_render_journal( WP_Post $post, string $lang ): void {
	$settings   = vava_home_journal_settings( $post->ID, $lang );
	$suffix     = 'en' === $lang ? '_en' : '';
	$mode_key   = '_vava_home_journal_query_mode' . $suffix;
	$latest_key = '_vava_home_journal_latest_category' . $suffix;
	$random_key = '_vava_home_journal_random_categories' . $suffix;
	$categories = get_categories( array( 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
	$uid        = 'vava_journal_' . $lang;
	?>
	<div class="vava-admin-repeaters vava-journal-query" data-journal-language="<?php echo esc_attr( $lang ); ?>" data-journal-query data-vava-shared-setting="journal-query">
		<h3><?php echo esc_html( vava_homepage_admin_text( 'journal_source', $lang ) ); ?></h3>
		<p class="description"><?php echo esc_html( vava_homepage_admin_text( 'journal_help', $lang ) ); ?></p>
		<div class="vava-journal-query-grid">
			<div class="vava-admin-field">
				<label for="<?php echo esc_attr( $uid . '_mode' ); ?>"><strong><?php echo esc_html( vava_homepage_admin_text( 'display_method', $lang ) ); ?></strong></label>
				<select class="widefat" data-journal-mode id="<?php echo esc_attr( $uid . '_mode' ); ?>" name="<?php echo esc_attr( $mode_key ); ?>">
					<option value="latest" <?php selected( 'latest', $settings['mode'] ); ?>><?php echo esc_html( vava_homepage_admin_text( 'latest_posts', $lang ) ); ?></option>
					<option value="random" <?php selected( 'random', $settings['mode'] ); ?>><?php echo esc_html( vava_homepage_admin_text( 'random_posts', $lang ) ); ?></option>
				</select>
			</div>
			<div class="vava-admin-field" data-journal-pane="latest">
				<label for="<?php echo esc_attr( $uid . '_latest' ); ?>"><strong><?php echo esc_html( vava_homepage_admin_text( 'category', $lang ) ); ?></strong></label>
				<select class="widefat" data-journal-latest id="<?php echo esc_attr( $uid . '_latest' ); ?>" name="<?php echo esc_attr( $latest_key ); ?>">
					<option value="0"><?php echo esc_html( vava_homepage_admin_text( 'choose_category', $lang ) ); ?></option>
					<?php foreach ( $categories as $category ) : ?><option value="<?php echo esc_attr( (string) $category->term_id ); ?>" <?php selected( (int) $settings['latest_category'], (int) $category->term_id ); ?>><?php echo esc_html( $category->name ); ?></option><?php endforeach; ?>
				</select>
			</div>
			<div class="vava-admin-field" data-journal-pane="random">
				<label for="<?php echo esc_attr( $uid . '_random' ); ?>"><strong><?php echo esc_html( vava_homepage_admin_text( 'random_categories', $lang ) ); ?></strong></label>
				<select class="widefat vava-multiple-select" data-journal-random id="<?php echo esc_attr( $uid . '_random' ); ?>" multiple name="<?php echo esc_attr( $random_key ); ?>[]" size="6">
					<?php foreach ( $categories as $category ) : ?><option value="<?php echo esc_attr( (string) $category->term_id ); ?>" <?php selected( in_array( (int) $category->term_id, $settings['random_categories'], true ) ); ?>><?php echo esc_html( $category->name ); ?></option><?php endforeach; ?>
				</select>
				<p class="description"><?php echo esc_html( vava_homepage_admin_text( 'multi_select_help', $lang ) ); ?></p>
			</div>
		</div>
	</div>
	<?php
}


/** Render the one shared internal-pages header menu selector in the Hero tab. */
function vava_homepage_render_internal_header_menu_select( WP_Post $post ): void {
	$key   = vava_home_internal_header_menu_meta_key();
	$value = vava_home_internal_header_menu_id( $post->ID );
	$menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );
	?>
	<div class="vava-admin-field vava-admin-field-wide vava-footer-menu-field vava-internal-header-menu-field" data-vava-internal-header-menu-field>
		<label for="vava_internal_header_menu"><strong<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'internal_header_menu', 'ar' ), vava_homepage_admin_text( 'internal_header_menu', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'internal_header_menu', 'ar' ) ); ?></strong></label>
		<select class="widefat" data-internal-header-menu-select id="vava_internal_header_menu" name="<?php echo esc_attr( $key ); ?>">
			<option value="0"<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'choose_wp_menu', 'ar' ), vava_homepage_admin_text( 'choose_wp_menu', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'choose_wp_menu', 'ar' ) ); ?></option>
			<?php foreach ( $menus as $nav_menu ) : ?>
				<option value="<?php echo esc_attr( (string) $nav_menu->term_id ); ?>" <?php selected( $value, (int) $nav_menu->term_id ); ?>><?php echo esc_html( $nav_menu->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description"><a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><span<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'manage_wp_menus', 'ar' ), vava_homepage_admin_text( 'manage_wp_menus', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'manage_wp_menus', 'ar' ) ); ?></span></a></p>
	</div>
	<?php
}

function vava_homepage_render_footer_menu_select( WP_Post $post, string $lang, string $group ): void {
	$key     = vava_home_footer_menu_meta_key( $group, $lang );
	$value   = vava_home_footer_menu_id( $post->ID, $group, $lang );
	$menus   = wp_get_nav_menus( array( 'orderby' => 'name' ) );
	$title   = 'policy' === $group ? vava_homepage_admin_text( 'footer_policy_menu', $lang ) : vava_homepage_admin_text( 'footer_primary_menu', $lang );
	$uid     = sanitize_html_class( $group . '_' . $lang . '_menu' );
	?>
	<div class="vava-admin-field vava-footer-menu-field">
		<label for="<?php echo esc_attr( $uid ); ?>"><strong><?php echo esc_html( $title ); ?></strong></label>
		<select class="widefat" data-footer-menu-group="<?php echo esc_attr( $group ); ?>" data-vava-shared-setting="footer-menu-<?php echo esc_attr( $group ); ?>" id="<?php echo esc_attr( $uid ); ?>" name="<?php echo esc_attr( $key ); ?>">
			<option value="0"><?php echo esc_html( vava_homepage_admin_text( 'choose_wp_menu', $lang ) ); ?></option>
			<?php foreach ( $menus as $menu ) : ?><option value="<?php echo esc_attr( (string) $menu->term_id ); ?>" <?php selected( $value, (int) $menu->term_id ); ?>><?php echo esc_html( $menu->name ); ?></option><?php endforeach; ?>
		</select>
		<p class="description"><a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php echo esc_html( vava_homepage_admin_text( 'manage_wp_menus', $lang ) ); ?></a></p>
	</div>
	<?php
}

function vava_homepage_render_document_row( WP_Post $post, string $lang ): void {
	$label_key = vava_home_language_key( '_vava_home_footer_document_label', $lang );
	$defaults  = vava_home_language_defaults( $lang );
	$label     = vava_home_field( $post->ID, $label_key, $defaults[ $label_key ] ?? '' );
	$number    = vava_home_field( $post->ID, '_vava_home_footer_document_number', vava_homepage_defaults()['_vava_home_footer_document_number'] );
	$uid       = 'vava_footer_document_' . $lang;
	$dir       = 'en' === $lang ? 'ltr' : 'rtl';
	?>
	<div class="vava-admin-field vava-document-control vava-admin-field-wide">
		<div class="vava-document-control-row">
			<label for="<?php echo esc_attr( $uid . '_label' ); ?>"><strong><?php echo esc_html( vava_homepage_admin_text( 'document_label', $lang ) ); ?></strong></label>
			<input class="widefat" data-vava-preview-field="_vava_home_footer_document_label" dir="<?php echo esc_attr( $dir ); ?>" id="<?php echo esc_attr( $uid . '_label' ); ?>" name="<?php echo esc_attr( $label_key ); ?>" type="text" value="<?php echo esc_attr( (string) $label ); ?>"/>
			<label for="<?php echo esc_attr( $uid . '_number' ); ?>"><strong><?php echo esc_html( vava_homepage_admin_text( 'document_number', $lang ) ); ?></strong></label>
			<input class="widefat" data-vava-document-number data-vava-preview-field="_vava_home_footer_document_number" dir="ltr" id="<?php echo esc_attr( $uid . '_number' ); ?>" type="text" value="<?php echo esc_attr( (string) $number ); ?>"/>
		</div>
	</div>
	<?php
}

function vava_homepage_render_social_item( array $item, $index, bool $template = false ): void {
	$platforms   = vava_homepage_social_platforms();
	$index_value = $template ? '__INDEX__' : (string) absint( $index );
	$platform    = sanitize_key( (string) ( $item['platform'] ?? 'instagram' ) );
	if ( ! isset( $platforms[ $platform ] ) ) {
		$platform = 'instagram';
	}
	?>
	<article class="vava-repeater-item vava-social-repeater-item" data-repeater-item>
		<div class="vava-social-platform-cell">
			<span class="vava-social-platform-icon" data-social-icon><?php echo vava_home_social_icon_svg( $platform ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<select aria-label="المنصة" data-social-platform data-social-preview-field="platform" name="_vava_home_footer_social[<?php echo esc_attr( $index_value ); ?>][platform]">
				<?php foreach ( $platforms as $platform_key => $platform_data ) : ?><option value="<?php echo esc_attr( $platform_key ); ?>" <?php selected( $platform, $platform_key ); ?>><?php echo esc_html( $platform_data['label'] ); ?></option><?php endforeach; ?>
			</select>
		</div>
		<input class="vava-social-url-input" aria-label="رابط المنصة" dir="ltr" name="_vava_home_footer_social[<?php echo esc_attr( $index_value ); ?>][url]" data-social-preview-field="url" placeholder="<?php echo esc_attr( 'email' === $platform ? 'name@example.com' : 'https://' ); ?>" type="<?php echo esc_attr( 'email' === $platform ? 'email' : 'text' ); ?>" value="<?php echo esc_attr( vava_home_normalize_social_value( $platform, (string) ( $item['url'] ?? '' ) ) ); ?>"/>
		<button class="vava-icon-button vava-icon-button-danger vava-repeater-remove" type="button" aria-label="حذف رابط المنصة" title="حذف رابط المنصة"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="m7 7 1 13h8l1-13"/><path d="M10 11v5M14 11v5"/></svg></button>
	</article>
	<?php
}

function vava_homepage_render_social( WP_Post $post ): void {
	$items = vava_home_footer_social( $post->ID );
	?>
	<div class="vava-shared-fields vava-dynamic-repeater vava-social-repeater" data-repeater data-repeater-kind="social" data-name-base="_vava_home_footer_social">
		<input type="hidden" name="_vava_home_footer_social_present" value="1"/>
		<div class="vava-repeater-heading"><div><h3<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'social_heading', 'ar' ), vava_homepage_admin_text( 'social_heading', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'social_heading', 'ar' ) ); ?></h3><p class="description"<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'social_help', 'ar' ), vava_homepage_admin_text( 'social_help', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'social_help', 'ar' ) ); ?></p></div></div>
		<div class="vava-social-table">
			<div class="vava-social-table-header" aria-hidden="true"><span<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'platform', 'ar' ), vava_homepage_admin_text( 'platform', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'platform', 'ar' ) ); ?></span><span<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'link', 'ar' ), vava_homepage_admin_text( 'link', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'link', 'ar' ) ); ?></span><span<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'action', 'ar' ), vava_homepage_admin_text( 'action', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'action', 'ar' ) ); ?></span></div>
			<div class="vava-repeater-list vava-social-repeater-list" data-repeater-list><?php foreach ( $items as $index => $item ) { vava_homepage_render_social_item( is_array( $item ) ? $item : array(), $index ); } ?></div>
		</div>
		<button class="button button-secondary vava-repeater-add vava-social-add" type="button"><span aria-hidden="true">＋</span> <span<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'add_platform', 'ar' ), vava_homepage_admin_text( 'add_platform', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'add_platform', 'ar' ) ); ?></span></button>
		<template data-repeater-template><?php vava_homepage_render_social_item( array( 'platform' => 'instagram', 'url' => '' ), '__INDEX__', true ); ?></template>
	</div>
	<?php
}

function vava_homepage_render_hero_shared_fields( WP_Post $post ): void {
	$fields = vava_homepage_section_fields( 'hero' );
	?>
	<?php vava_homepage_render_internal_header_menu_select( $post ); ?>
	<div class="vava-hero-media-direct" data-hero-media-controller>
		<div class="vava-fields-grid">
			<?php foreach ( $fields as $field ) { if ( ! empty( $field[3] ) || 'media_type' !== $field[2] ) { continue; } vava_homepage_render_field( $post, $field, 'ar' ); } ?>
		</div>
		<div class="vava-hero-media-grid">
			<?php foreach ( $fields as $field ) { if ( ! empty( $field[3] ) || 'media_type' === $field[2] ) { continue; } vava_homepage_render_field( $post, $field, 'ar' ); } ?>
		</div>
	</div>
	<?php
}

function vava_homepage_preview_journal_image_url( array $item ): string {
	$image_id = absint( $item['image_id'] ?? 0 );
	if ( $image_id ) {
		$url = wp_get_attachment_image_url( $image_id, 'medium_large' );
		if ( $url ) {
			return (string) $url;
		}
	}
	return '';
}

function vava_homepage_render_live_preview( WP_Post $post, string $section, string $lang ): void {
	$dir        = 'en' === $lang ? 'ltr' : 'rtl';
	$lang_label = 'en' === $lang ? 'English preview' : 'معاينة عربية';
	$asset      = static function( string $path ): string { return get_theme_file_uri( ltrim( $path, '/' ) ); };
	?>
	<aside class="vava-live-preview vava-live-preview-v1r8" data-live-preview data-preview-language="<?php echo esc_attr( $lang ); ?>" data-preview-section="<?php echo esc_attr( $section ); ?>" dir="<?php echo esc_attr( $dir ); ?>">
		<header class="vava-live-preview-header"><div><strong><?php echo esc_html( 'en' === $lang ? 'Live preview' : 'معاينة مباشرة' ); ?></strong><span><?php echo esc_html( $lang_label ); ?></span></div><span class="vava-live-preview-dot" aria-hidden="true"></span></header>
		<div class="vava-preview-viewport"><div class="vava-preview-stage"><div class="vava-preview-canvas vava-preview-frontend vava-preview-frontend-<?php echo esc_attr( $section ); ?>" data-preview-design-width="900">
		<?php if ( 'hero' === $section ) :
			$type              = (string) vava_home_field( $post->ID, '_vava_home_hero_media_type', 'video' );
			$type              = in_array( $type, array( 'image', 'video' ), true ) ? $type : 'video';
			$image_id          = absint( vava_home_field( $post->ID, '_vava_home_hero_image_id', 0 ) );
			$video_id          = absint( vava_home_field( $post->ID, '_vava_home_hero_video_id', 0 ) );
			$poster_id         = absint( vava_home_field( $post->ID, '_vava_home_hero_poster_id', 0 ) );
			$image_url         = vava_homepage_media_current_url( $image_id, 'image', vava_homepage_media_fallback_url( '_vava_home_hero_image_id' ) );
			$video_url         = vava_homepage_media_current_url( $video_id, 'video', vava_homepage_media_fallback_url( '_vava_home_hero_video_id' ) );
			$poster_url        = vava_homepage_media_current_url( $poster_id, 'image', vava_homepage_media_fallback_url( '_vava_home_hero_poster_id' ) );
			$header_menu_items = vava_homepage_internal_header_preview_items( $post->ID, $lang );
			?>
			<header class="vava-fe-internal-header-preview" data-preview-internal-header>
				<a aria-label="VAVA Living" class="vava-fe-internal-brand" href="#"><img alt="VAVA Living" src="<?php echo esc_url( $asset( 'assets/images/vava-logo.png' ) ); ?>"/></a>
				<nav aria-label="<?php echo esc_attr( 'en' === $lang ? 'Internal pages navigation preview' : 'معاينة قائمة الصفحات الداخلية' ); ?>" data-preview-internal-header-menu>
					<?php if ( $header_menu_items ) : ?>
						<?php foreach ( $header_menu_items as $header_link ) : ?><a href="<?php echo esc_url( (string) $header_link['url'] ); ?>"><?php echo esc_html( (string) $header_link['label'] ); ?></a><?php endforeach; ?>
					<?php else : ?>
						<span class="vava-fe-internal-menu-empty"><?php echo esc_html( 'en' === $lang ? 'No menu selected' : 'لم يتم اختيار قائمة' ); ?></span>
					<?php endif; ?>
				</nav>
				<div aria-label="<?php echo esc_attr( 'en' === $lang ? 'Language preview' : 'معاينة اللغة' ); ?>" class="vava-fe-internal-language-preview" dir="ltr"><span class="<?php echo 'ar' === $lang ? 'is-active' : ''; ?>">AR</span><span class="<?php echo 'en' === $lang ? 'is-active' : ''; ?>">EN</span></div>
			</header>
			<section class="vava-fe-section vava-fe-hero">
				<div class="vava-preview-hero-media vava-fe-hero-media" data-preview-hero-type="<?php echo esc_attr( $type ); ?>">
					<img alt="" data-preview-hero-image src="<?php echo esc_url( $image_url ); ?>"/>
					<video autoplay data-preview-hero-video loop muted playsinline poster="<?php echo esc_url( $poster_url ); ?>"><source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4"/></video>
					<span class="vava-fe-hero-overlay"></span>
				</div>
				<div class="vava-fe-hero-copy">
					<div class="vava-fe-eyebrow" data-preview-output="_vava_home_hero_eyebrow"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_hero_eyebrow', $lang ) ); ?></div>
					<h2 data-preview-output="_vava_home_hero_title"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_hero_title', $lang ) ); ?></h2>
					<div class="vava-richtext-content" data-preview-output="_vava_home_hero_description"><?php echo vava_richtext_output( vava_home_field_language( $post->ID, '_vava_home_hero_description', $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<a class="vava-fe-btn vava-fe-btn-secondary" data-preview-button="hero" data-preview-output="_vava_home_hero_button_text" href="<?php echo esc_url( vava_home_button_url( $post->ID, 'hero', $lang ) ); ?>"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_hero_button_text', $lang ) ); ?></a>
				</div>
			</section>
		<?php elseif ( 'paths' === $section ) :
			$image_id  = absint( vava_home_field( $post->ID, '_vava_home_paths_image_id', 0 ) );
			$image_url = vava_homepage_media_current_url( $image_id, 'image', vava_homepage_media_fallback_url( '_vava_home_paths_image_id' ) );
			?>
			<section class="vava-fe-section vava-fe-paths">
				<div class="vava-fe-paths-botanical" aria-hidden="true"><svg viewBox="0 0 360 250"><g fill="none" stroke="currentColor" stroke-linecap="round"><path d="M20 235C70 168 110 112 150 24"/><path d="M82 151C55 130 43 102 48 75C76 83 95 103 100 133"/><path d="M118 96C96 70 94 42 105 18C132 34 145 58 139 86"/><path d="M146 53C166 34 188 27 209 31C199 54 179 68 151 68"/></g></svg></div>
				<div class="vava-fe-paths-wave" aria-hidden="true"><svg preserveAspectRatio="none" viewBox="0 0 1200 560"><path d="M0 108C151 254 314 345 493 350C655 355 716 322 846 359C977 397 1041 484 1200 523V560H0Z" fill="rgba(225,184,162,.19)"/><path d="M0 224C174 347 355 416 535 412C690 408 767 384 894 421C1016 457 1096 508 1200 535V560H0Z" fill="rgba(242,222,199,.38)"/><path d="M0 150C154 286 317 368 494 370C658 372 730 343 858 381C989 420 1053 496 1200 532" fill="none" stroke="rgba(197,147,94,.68)" stroke-width="2"/></svg></div>
				<div class="vava-fe-paths-grid">
					<div class="vava-fe-paths-copy">
						<div class="vava-fe-divider vava-fe-divider-top" aria-hidden="true"><span></span><b>✧</b><span></span></div>
						<h2 data-preview-output="_vava_home_paths_title"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_paths_title', $lang ) ); ?></h2>
						<div class="vava-fe-divider vava-fe-divider-bottom" aria-hidden="true"><span></span><b>∞</b><span></span></div>
						<div class="vava-richtext-content" data-preview-output="_vava_home_paths_description"><?php echo vava_richtext_output( vava_home_field_language( $post->ID, '_vava_home_paths_description', $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<a class="vava-fe-btn vava-fe-btn-primary" data-preview-button="paths" data-preview-output="_vava_home_paths_button_text" href="<?php echo esc_url( vava_home_button_url( $post->ID, 'paths', $lang ) ); ?>"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_paths_button_text', $lang ) ); ?><span aria-hidden="true">⌁</span></a>
					</div>
					<figure class="vava-fe-paths-visual"><img alt="" data-preview-paths-image src="<?php echo esc_url( $image_url ); ?>"/></figure>
				</div>
			</section>
		<?php elseif ( 'shop' === $section ) :
			$shop_image_id  = absint( vava_home_field( $post->ID, '_vava_home_shop_image_id', 0 ) );
			$shop_image_url = vava_homepage_media_current_url( $shop_image_id, 'image', $asset( 'assets/images/store-2.png' ) );
			?>
			<section class="vava-fe-section vava-fe-shop">
				<div class="vava-fe-shop-blob vava-fe-shop-blob-a" aria-hidden="true"></div><div class="vava-fe-shop-blob vava-fe-shop-blob-b" aria-hidden="true"></div>
				<div class="vava-fe-shop-grid">
					<figure class="vava-fe-shop-art"><img alt="" src="<?php echo esc_url( $shop_image_url ); ?>" data-preview-image="shop"/></figure>
					<div class="vava-fe-shop-copy">
						<div class="vava-fe-eyebrow" data-preview-output="_vava_home_shop_eyebrow"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_shop_eyebrow', $lang ) ); ?></div>
						<h2 data-preview-output="_vava_home_shop_title"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_shop_title', $lang ) ); ?></h2>
						<h3 data-preview-output="_vava_home_shop_subtitle"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_shop_subtitle', $lang ) ); ?></h3>
						<div class="vava-richtext-content" data-preview-output="_vava_home_shop_description"><?php echo vava_richtext_output( vava_home_shop_description( (int) $post->ID, $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<a class="vava-fe-btn vava-fe-btn-coral" data-preview-button="shop" data-preview-output="_vava_home_shop_button_text" href="<?php echo esc_url( vava_home_button_url( $post->ID, 'shop', $lang ) ); ?>"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_shop_button_text', $lang ) ); ?></a>
					</div>
				</div>
			</section>
		<?php elseif ( 'testimonials' === $section ) :
			$items       = vava_home_testimonials( $post->ID, $lang );
			$active_item = ! empty( $items ) && is_array( $items[0] ) ? $items[0] : array( 'text' => '', 'author' => '' );
			?>
			<section class="vava-fe-section vava-fe-testimonials vava-fe-testimonials-single-preview">
				<article class="vava-fe-testimonial-single" data-preview-testimonial-card>
					<span class="vava-fe-testimonial-quote" aria-hidden="true">“</span>
					<div class="vava-fe-testimonial-text"><div class="vava-fe-testimonial-text-inner vava-richtext-content" data-preview-testimonial-text><?php echo vava_richtext_output( (string) ( $active_item['text'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div>
					<div class="vava-fe-testimonial-meta">
						<span class="vava-fe-testimonial-stars" aria-hidden="true">★★★★★</span>
						<span class="vava-fe-testimonial-person"><span class="vava-fe-testimonial-avatar" aria-hidden="true"></span><strong data-preview-testimonial-author><?php echo esc_html( (string) ( $active_item['author'] ?? '' ) ); ?></strong></span>
					</div>
				</article>
			</section>
		<?php elseif ( 'journal' === $section ) :
			$journal_image_id  = absint( vava_home_field( $post->ID, '_vava_home_journal_image_id', 0 ) );
			$journal_image_url = vava_home_image_url( $journal_image_id, 'assets/images/home-journal-editorial.webp', 'large' );
			$feature_labels    = 'en' === $lang ? array( 'Articles', 'Resources', 'Reflections' ) : array( 'مقالات', 'موارد', 'خواطر' );
			?>
			<section class="vava-fe-section vava-fe-journal">
				<div class="vava-fe-journal-grid">
					<div class="vava-fe-journal-copy"><h2 data-preview-output="_vava_home_journal_title"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_journal_title', $lang ) ); ?></h2><h3 data-preview-output="_vava_home_journal_subtitle"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_journal_subtitle', $lang ) ); ?></h3><span class="vava-fe-journal-divider"></span><div class="vava-richtext-content" data-preview-output="_vava_home_journal_description"><?php echo vava_richtext_output( vava_home_field_language( $post->ID, '_vava_home_journal_description', $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><a class="vava-fe-btn vava-fe-btn-primary" data-preview-button="journal" data-preview-output="_vava_home_journal_button_text" href="<?php echo esc_url( vava_home_button_url( $post->ID, 'journal', $lang ) ); ?>"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_journal_button_text', $lang ) ); ?></a></div>
					<div class="vava-fe-journal-visual"><figure><img alt="" data-preview-journal-image src="<?php echo esc_url( $journal_image_url ); ?>"/></figure><div class="vava-fe-journal-glass"><div><i>▤</i><strong><?php echo esc_html( $feature_labels[0] ); ?></strong></div><div><i>⌁</i><strong><?php echo esc_html( $feature_labels[1] ); ?></strong></div><div><i>✎</i><strong><?php echo esc_html( $feature_labels[2] ); ?></strong></div><small data-preview-output="_vava_home_journal_visual_caption"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_journal_visual_caption', $lang ) ); ?></small></div></div>
				</div>
			</section>
		<?php elseif ( 'contact' === $section ) :
			$contact_image_id  = absint( vava_home_field( $post->ID, '_vava_home_contact_image_id', 0 ) );
			$contact_image_url = vava_home_image_url( $contact_image_id, 'assets/images/contact-section-visual.jpg', 'large' );
			?>
			<section class="vava-fe-section vava-fe-contact">
				<div class="vava-fe-contact-glow" aria-hidden="true"></div>
				<div class="vava-fe-contact-copy"><div class="vava-fe-eyebrow">VAVA Living</div><h2 data-preview-output="_vava_home_contact_title"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_contact_title', $lang ) ); ?></h2><div class="vava-richtext-content" data-preview-output="_vava_home_contact_description"><?php echo vava_richtext_output( vava_home_field_language( $post->ID, '_vava_home_contact_description', $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><a class="vava-fe-btn vava-fe-btn-coral" data-preview-button="contact" data-preview-output="_vava_home_contact_button_text" href="<?php echo esc_url( vava_home_button_url( $post->ID, 'contact', $lang ) ); ?>"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_contact_button_text', $lang ) ); ?></a></div>
				<figure class="vava-fe-contact-visual"><img alt="" data-preview-contact-image src="<?php echo esc_url( $contact_image_url ); ?>"/></figure>
			</section>
		<?php elseif ( 'footer' === $section ) :
			$primary_links = vava_home_footer_links( $post->ID, 'primary', $lang );
			$policy_links  = vava_home_footer_links( $post->ID, 'policy', $lang );
			$social_items  = vava_home_footer_social( $post->ID );
			?>
			<footer class="vava-fe-footer">
				<div class="vava-fe-footer-logo"><img alt="VAVA Living" src="<?php echo esc_url( $asset( 'assets/images/vava-logo.png' ) ); ?>"/><div data-preview-output="_vava_home_footer_tagline"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_footer_tagline', $lang ) ); ?></div></div>
				<div class="vava-fe-footer-center"><nav data-preview-footer-menu="primary"><?php foreach ( $primary_links as $link ) : ?><a href="#"><?php echo esc_html( (string) ( $link['label'] ?? '' ) ); ?></a><?php endforeach; ?></nav><nav data-preview-footer-menu="policy"><?php foreach ( $policy_links as $link ) : ?><a href="#"><?php echo esc_html( (string) ( $link['label'] ?? '' ) ); ?></a><?php endforeach; ?></nav><div class="vava-fe-footer-divider"><span>✦</span></div><div class="vava-fe-footer-meta"><span data-preview-output="_vava_home_footer_copyright"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_footer_copyright', $lang ) ); ?></span><span><b data-preview-output="_vava_home_footer_document_label"><?php echo esc_html( vava_home_field_language( $post->ID, '_vava_home_footer_document_label', $lang ) ); ?></b> <em data-preview-output="_vava_home_footer_document_number"><?php echo esc_html( (string) vava_home_field( $post->ID, '_vava_home_footer_document_number', vava_homepage_defaults()['_vava_home_footer_document_number'] ) ); ?></em></span></div></div>
				<div class="vava-fe-social" data-preview-social-list><?php foreach ( $social_items as $social ) : ?><span title="<?php echo esc_attr( (string) ( vava_homepage_social_platforms()[ $social['platform'] ]['label'] ?? '' ) ); ?>"><?php echo vava_home_social_icon_svg( (string) $social['platform'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php endforeach; ?></div>
			</footer>
		<?php endif; ?>
		</div></div></div>
	</aside>
	<?php
}

function vava_homepage_render_page_identity( WP_Post $post ): void {
	vava_render_bilingual_page_identity( $post, (string) get_permalink( $post ) );
}

function vava_homepage_render_settings( WP_Post $post ): void {
	wp_nonce_field( 'vava_homepage_save', 'vava_homepage_nonce' );
	$sections        = vava_homepage_sections();
	$document_number = vava_home_field( $post->ID, '_vava_home_footer_document_number', vava_homepage_defaults()['_vava_home_footer_document_number'] );
	?>
	<div class="vava-homepage-admin" data-active-section="hero" data-active-language="ar" data-settings-title-ar="<?php echo esc_attr( vava_homepage_admin_text( 'meta_box_title', 'ar' ) ); ?>" data-settings-title-en="<?php echo esc_attr( vava_homepage_admin_text( 'meta_box_title', 'en' ) ); ?>">
		<input type="hidden" name="_vava_admin_active_language" value="ar" data-vava-active-language-input/>
		<?php vava_homepage_render_page_identity( $post ); ?>
		<input data-vava-document-number-source name="_vava_home_footer_document_number" type="hidden" value="<?php echo esc_attr( (string) $document_number ); ?>"/>
		<div class="vava-admin-toolbar">
			<div class="vava-section-tabs" role="tablist" aria-label="<?php echo esc_attr( vava_homepage_admin_text( 'sections_aria', 'ar' ) ); ?>" data-vava-i18n-aria-ar="<?php echo esc_attr( vava_homepage_admin_text( 'sections_aria', 'ar' ) ); ?>" data-vava-i18n-aria-en="<?php echo esc_attr( vava_homepage_admin_text( 'sections_aria', 'en' ) ); ?>">
			<?php foreach ( $sections as $id => $section ) : ?><button aria-selected="<?php echo 'hero' === $id ? 'true' : 'false'; ?>" class="vava-section-tab <?php echo 'hero' === $id ? 'is-active' : ''; ?>" data-section="<?php echo esc_attr( $id ); ?>" type="button" role="tab"><span class="vava-tab-icon" aria-hidden="true"><?php echo vava_homepage_section_icon_svg( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span<?php echo vava_admin_i18n_attributes( (string) $section['label_ar'], (string) $section['label_en'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $section['label_ar'] ); ?></span></button><?php endforeach; ?>
			</div>
			<div class="vava-toolbar-actions">
				<div class="vava-language-switch" role="group" aria-label="<?php echo esc_attr( vava_homepage_admin_text( 'fields_language', 'ar' ) ); ?>" data-vava-i18n-aria-ar="<?php echo esc_attr( vava_homepage_admin_text( 'fields_language', 'ar' ) ); ?>" data-vava-i18n-aria-en="<?php echo esc_attr( vava_homepage_admin_text( 'fields_language', 'en' ) ); ?>"><button class="is-active" data-language="ar" type="button"><span>العربية</span><small>AR</small></button><button data-language="en" type="button"><span>English</span><small>EN</small></button></div>
				<button class="button vava-homepage-update-button" data-vava-submit type="button"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20 12a8 8 0 1 1-2.35-5.65"/><path d="M20 4v6h-6"/></svg><span<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'update', 'ar' ), vava_homepage_admin_text( 'update', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'update', 'ar' ) ); ?></span></button>
			</div>
		</div>
		<div class="vava-section-panels">
		<?php foreach ( $sections as $section_id => $section ) : ?>
			<section class="vava-section-panel vava-section-panel--<?php echo esc_attr( $section_id ); ?> <?php echo 'hero' === $section_id ? 'is-active' : ''; ?>" data-section-panel="<?php echo esc_attr( $section_id ); ?>">
				<?php foreach ( array( 'ar', 'en' ) as $lang ) : ?>
				<div class="vava-language-pane <?php echo 'ar' === $lang ? 'is-active' : ''; ?>" data-language-pane="<?php echo esc_attr( $lang ); ?>" dir="<?php echo 'en' === $lang ? 'ltr' : 'rtl'; ?>">
					<div class="vava-editor-workspace">
						<?php vava_homepage_render_live_preview( $post, $section_id, $lang ); ?>
						<div class="vava-editor-controls">
					<div class="vava-fields-grid">
					<?php
					$button_text_key = vava_homepage_button_text_key( $section_id );
					foreach ( vava_homepage_section_fields( $section_id ) as $field ) {
						if ( empty( $field[3] ) || $field[0] === $button_text_key || ( 'footer' === $section_id && '_vava_home_footer_document_label' === $field[0] ) ) {
							continue;
						}
						vava_homepage_render_field( $post, $field, $lang );
					}
					if ( isset( vava_homepage_button_sections()[ $section_id ] ) ) {
						vava_homepage_render_link_selector( $post, $section_id, $lang );
					}
					if ( 'footer' === $section_id ) {
						vava_homepage_render_document_row( $post, $lang );
						vava_homepage_render_footer_menu_select( $post, $lang, 'primary' );
						vava_homepage_render_footer_menu_select( $post, $lang, 'policy' );
					}
					?>
					</div>
					<?php if ( 'testimonials' === $section_id ) { vava_homepage_render_testimonials( $post, $lang ); } ?>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
				<?php
				if ( 'hero' === $section_id ) {
					vava_homepage_render_hero_shared_fields( $post );
				} elseif ( 'footer' !== $section_id ) {
					$shared = array_filter( vava_homepage_section_fields( $section_id ), static function( $field ) { return empty( $field[3] ); } );
					if ( $shared ) : ?><div class="vava-shared-fields"><h3<?php echo vava_admin_i18n_attributes( vava_homepage_admin_text( 'shared_settings', 'ar' ), vava_homepage_admin_text( 'shared_settings', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_homepage_admin_text( 'shared_settings', 'ar' ) ); ?></h3><div class="vava-fields-grid"><?php foreach ( $shared as $field ) { vava_homepage_render_field( $post, $field, 'ar' ); } ?></div></div><?php endif;
				}
				if ( 'footer' === $section_id ) {
					vava_homepage_render_social( $post );
				}
				?>
			</section>
		<?php endforeach; ?>
		</div>
	</div>
	<?php
}

function vava_homepage_sanitize_field( $raw, string $type ) {
	if ( in_array( $type, array( 'image', 'video' ), true ) ) { return absint( $raw ); }
	if ( 'media_type' === $type ) { return in_array( $raw, array( 'image', 'video' ), true ) ? $raw : 'video'; }
	if ( 'url' === $type ) { return esc_url_raw( (string) $raw ); }
	if ( 'textarea' === $type ) { return wp_kses_post( (string) $raw ); }
	return sanitize_text_field( (string) $raw );
}

function vava_homepage_save_meta( int $post_id, WP_Post $post ): void {
	if ( ! isset( $_POST['vava_homepage_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vava_homepage_nonce'] ) ), 'vava_homepage_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $post_id ) || 'page' !== $post->post_type || ! current_user_can( 'edit_page', $post_id ) ) { return; }

	vava_save_bilingual_page_titles( $post_id );

	// Every AR/EN field remains in the form. The active tab/language is visual only.
	foreach ( array_keys( vava_homepage_sections() ) as $section ) {
		foreach ( vava_homepage_section_fields( $section ) as $field ) {
			list( $base_key, , $type, $translatable ) = $field;
			$langs = $translatable ? array( 'ar', 'en' ) : array( 'ar' );
			foreach ( $langs as $lang ) {
				$key = $translatable ? vava_home_language_key( $base_key, $lang ) : $base_key;
				if ( array_key_exists( $key, $_POST ) ) {
					update_post_meta( $post_id, $key, vava_homepage_sanitize_field( wp_unslash( $_POST[ $key ] ), $type ) );
				}
			}
		}
	}

	// The old additional-text field was merged into the single description editor.
	foreach ( array( 'ar', 'en' ) as $shop_lang ) {
		$description_key = vava_home_language_key( '_vava_home_shop_description', $shop_lang );
		if ( array_key_exists( $description_key, $_POST ) ) {
			update_post_meta( $post_id, vava_home_language_key( '_vava_home_shop_small_text', $shop_lang ), '' );
		}
	}

	$active_language = isset( $_POST['_vava_admin_active_language'] ) ? vava_normalize_language( sanitize_key( wp_unslash( $_POST['_vava_admin_active_language'] ) ) ) : 'ar';


	$internal_menu_key = vava_home_internal_header_menu_meta_key();
	if ( array_key_exists( $internal_menu_key, $_POST ) ) {
		$internal_menu_id = absint( $_POST[ $internal_menu_key ] );
		if ( $internal_menu_id && ! wp_get_nav_menu_object( $internal_menu_id ) ) {
			$internal_menu_id = 0;
		}
		update_post_meta( $post_id, $internal_menu_key, $internal_menu_id );

		// Keep Appearance > Menus aligned with the Homepage setting without
		// making the menu location the source of truth.
		if ( $internal_menu_id ) {
			$locations = get_theme_mod( 'nav_menu_locations', array() );
			$locations = is_array( $locations ) ? $locations : array();
			$locations['primary_internal'] = $internal_menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	foreach ( vava_homepage_button_sections() as $section => $legacy_base ) {
		$canonical_type_key   = vava_homepage_link_key( $legacy_base, 'link_type', 'ar' );
		$canonical_page_key   = vava_homepage_link_key( $legacy_base, 'page_id', 'ar' );
		$canonical_manual_key = vava_homepage_link_key( $legacy_base, 'manual_url', 'ar' );
		$english_type_key     = vava_homepage_link_key( $legacy_base, 'link_type', 'en' );
		$english_page_key     = vava_homepage_link_key( $legacy_base, 'page_id', 'en' );
		$english_manual_key   = vava_homepage_link_key( $legacy_base, 'manual_url', 'en' );

		$stored_type   = (string) get_post_meta( $post_id, $canonical_type_key, true );
		$stored_page   = absint( get_post_meta( $post_id, $canonical_page_key, true ) );
		$stored_manual = (string) get_post_meta( $post_id, $canonical_manual_key, true );
		$ar_type       = isset( $_POST[ $canonical_type_key ] ) && 'page' === sanitize_key( wp_unslash( $_POST[ $canonical_type_key ] ) ) ? 'page' : 'manual';
		$en_type       = isset( $_POST[ $english_type_key ] ) && 'page' === sanitize_key( wp_unslash( $_POST[ $english_type_key ] ) ) ? 'page' : 'manual';
		$ar_page       = isset( $_POST[ $canonical_page_key ] ) ? absint( $_POST[ $canonical_page_key ] ) : $stored_page;
		$en_page       = isset( $_POST[ $english_page_key ] ) ? absint( $_POST[ $english_page_key ] ) : $stored_page;
		$ar_manual     = isset( $_POST[ $canonical_manual_key ] ) ? esc_url_raw( (string) wp_unslash( $_POST[ $canonical_manual_key ] ) ) : $stored_manual;
		$en_manual     = isset( $_POST[ $english_manual_key ] ) ? esc_url_raw( (string) wp_unslash( $_POST[ $english_manual_key ] ) ) : $stored_manual;

		$type       = (string) vava_reconcile_shared_setting( $ar_type, $en_type, $stored_type ?: 'manual', $active_language );
		$page_id    = absint( vava_reconcile_shared_setting( $ar_page, $en_page, $stored_page, $active_language ) );
		$manual_url = (string) vava_reconcile_shared_setting( $ar_manual, $en_manual, $stored_manual, $active_language );

		foreach ( array( 'ar', 'en' ) as $lang ) {
			$type_key   = vava_homepage_link_key( $legacy_base, 'link_type', $lang );
			$page_key   = vava_homepage_link_key( $legacy_base, 'page_id', $lang );
			$manual_key = vava_homepage_link_key( $legacy_base, 'manual_url', $lang );
			$legacy_key = vava_home_language_key( $legacy_base, $lang );
			$resolved   = 'page' === $type && $page_id ? vava_localized_page_url( $page_id, $lang ) : $manual_url;
			update_post_meta( $post_id, $type_key, $type );
			update_post_meta( $post_id, $page_key, $page_id );
			update_post_meta( $post_id, $manual_key, $manual_url );
			update_post_meta( $post_id, $legacy_key, esc_url_raw( (string) $resolved ) );
		}
	}

	foreach ( array( 'ar', 'en' ) as $lang ) {
		$testimonials_key = 'en' === $lang ? '_vava_home_testimonials_items_en' : '_vava_home_testimonials_items';
		if ( isset( $_POST[ $testimonials_key . '_present' ] ) ) {
			$submitted = isset( $_POST[ $testimonials_key ] ) && is_array( $_POST[ $testimonials_key ] ) ? wp_unslash( $_POST[ $testimonials_key ] ) : array();
			$items     = array();
			foreach ( $submitted as $item ) {
				if ( ! is_array( $item ) ) { continue; }
				$text_value   = wp_kses_post( (string) ( $item['text'] ?? '' ) );
				$author_value = sanitize_text_field( (string) ( $item['author'] ?? '' ) );
				if ( '' === $text_value && '' === $author_value ) { continue; }
				$items[] = array( 'text' => $text_value, 'author' => $author_value );
			}
			update_post_meta( $post_id, $testimonials_key, $items );
		}
	}

	$stored_journal = vava_home_journal_settings( $post_id, 'ar' );
	$ar_mode   = isset( $_POST['_vava_home_journal_query_mode'] ) && 'random' === sanitize_key( wp_unslash( $_POST['_vava_home_journal_query_mode'] ) ) ? 'random' : 'latest';
	$en_mode   = isset( $_POST['_vava_home_journal_query_mode_en'] ) && 'random' === sanitize_key( wp_unslash( $_POST['_vava_home_journal_query_mode_en'] ) ) ? 'random' : 'latest';
	$ar_latest = isset( $_POST['_vava_home_journal_latest_category'] ) ? absint( $_POST['_vava_home_journal_latest_category'] ) : (int) $stored_journal['latest_category'];
	$en_latest = isset( $_POST['_vava_home_journal_latest_category_en'] ) ? absint( $_POST['_vava_home_journal_latest_category_en'] ) : (int) $stored_journal['latest_category'];
	$ar_random = isset( $_POST['_vava_home_journal_random_categories'] ) && is_array( $_POST['_vava_home_journal_random_categories'] ) ? array_values( array_filter( array_map( 'absint', wp_unslash( $_POST['_vava_home_journal_random_categories'] ) ) ) ) : (array) $stored_journal['random_categories'];
	$en_random = isset( $_POST['_vava_home_journal_random_categories_en'] ) && is_array( $_POST['_vava_home_journal_random_categories_en'] ) ? array_values( array_filter( array_map( 'absint', wp_unslash( $_POST['_vava_home_journal_random_categories_en'] ) ) ) ) : (array) $stored_journal['random_categories'];
	$mode      = (string) vava_reconcile_shared_setting( $ar_mode, $en_mode, $stored_journal['mode'], $active_language );
	$latest    = absint( vava_reconcile_shared_setting( $ar_latest, $en_latest, $stored_journal['latest_category'], $active_language ) );
	$random    = (array) vava_reconcile_shared_setting( $ar_random, $en_random, $stored_journal['random_categories'], $active_language );
	foreach ( array( '', '_en' ) as $suffix ) {
		update_post_meta( $post_id, '_vava_home_journal_query_mode' . $suffix, $mode );
		update_post_meta( $post_id, '_vava_home_journal_latest_category' . $suffix, $latest );
		update_post_meta( $post_id, '_vava_home_journal_random_categories' . $suffix, $random );
	}

	foreach ( array( 'primary', 'policy' ) as $group ) {
		$ar_key  = vava_home_footer_menu_meta_key( $group, 'ar' );
		$en_key  = vava_home_footer_menu_meta_key( $group, 'en' );
		$stored  = vava_home_footer_menu_id( $post_id, $group, 'ar' );
		$ar_menu = array_key_exists( $ar_key, $_POST ) ? absint( $_POST[ $ar_key ] ) : $stored;
		$en_menu = array_key_exists( $en_key, $_POST ) ? absint( $_POST[ $en_key ] ) : $stored;
		$menu_id = absint( vava_reconcile_shared_setting( $ar_menu, $en_menu, $stored, $active_language ) );
		update_post_meta( $post_id, $ar_key, $menu_id );
		update_post_meta( $post_id, $en_key, $menu_id );
	}

	if ( isset( $_POST['_vava_home_footer_social_present'] ) ) {
		$submitted = isset( $_POST['_vava_home_footer_social'] ) && is_array( $_POST['_vava_home_footer_social'] ) ? wp_unslash( $_POST['_vava_home_footer_social'] ) : array();
		$platforms = vava_homepage_social_platforms();
		$items     = array();
		foreach ( $submitted as $item ) {
			if ( ! is_array( $item ) ) { continue; }
			$platform = sanitize_key( (string) ( $item['platform'] ?? '' ) );
			if ( ! isset( $platforms[ $platform ] ) ) { continue; }
			$items[] = array(
				'platform' => $platform,
				'url'      => vava_home_normalize_social_value( $platform, (string) ( $item['url'] ?? '' ) ),
			);
		}
		update_post_meta( $post_id, '_vava_home_footer_social', $items );
	}
}
add_action( 'save_post_page', 'vava_homepage_save_meta', 10, 2 );

function vava_homepage_use_block_editor( bool $use_block_editor, WP_Post $post ): bool {
	return vava_homepage_is_home_page( (int) $post->ID ) ? false : $use_block_editor;
}
add_filter( 'use_block_editor_for_post', 'vava_homepage_use_block_editor', 10, 2 );

function vava_homepage_admin_body_class( string $classes ): string {
	global $post;
	$post_id = $post instanceof WP_Post ? (int) $post->ID : ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 );
	if ( $post_id && vava_homepage_is_home_page( $post_id ) ) {
		$classes .= ' vava-homepage-classic';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'vava_homepage_admin_body_class' );

function vava_homepage_journal_preview_payload( array $items ): array {
	$payload = array();
	foreach ( $items as $item ) {
		$payload[] = array(
			'title' => (string) ( $item['title'] ?? '' ),
			'label' => (string) ( $item['label'] ?? '' ),
			'url'   => (string) ( $item['url'] ?? '' ),
			'image' => vava_homepage_preview_journal_image_url( $item ),
		);
	}
	return $payload;
}

function vava_homepage_ajax_journal_preview(): void {
	check_ajax_referer( 'vava_homepage_live_preview', 'nonce' );
	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	if ( ! $post_id || ! current_user_can( 'edit_page', $post_id ) ) {
		wp_send_json_error( array( 'message' => 'غير مصرح بعرض المعاينة.' ), 403 );
	}
	$lang = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$mode = isset( $_POST['mode'] ) && 'random' === sanitize_key( wp_unslash( $_POST['mode'] ) ) ? 'random' : 'latest';
	$settings = array(
		'mode'              => $mode,
		'latest_category'   => isset( $_POST['latest_category'] ) ? absint( $_POST['latest_category'] ) : 0,
		'random_categories' => isset( $_POST['random_categories'] ) && is_array( $_POST['random_categories'] ) ? array_values( array_filter( array_map( 'absint', wp_unslash( $_POST['random_categories'] ) ) ) ) : array(),
	);
	wp_send_json_success( array( 'items' => vava_homepage_journal_preview_payload( vava_home_journal_items_from_settings( $post_id, $lang, $settings ) ) ) );
}
add_action( 'wp_ajax_vava_homepage_journal_preview', 'vava_homepage_ajax_journal_preview' );

function vava_homepage_admin_menu_preview_data(): array {
	$data = array();
	foreach ( wp_get_nav_menus( array( 'orderby' => 'name' ) ) as $menu ) {
		$items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );
		$data[ (string) $menu->term_id ] = array();
		if ( ! is_array( $items ) ) {
			continue;
		}
		foreach ( $items as $item ) {
			if ( $item instanceof WP_Post && 'draft' !== $item->post_status ) {
				$data[ (string) $menu->term_id ][] = array(
					'label'    => (string) $item->title,
					'label_ar' => vava_nav_menu_item_title_for_language( $item, 'ar', (string) $item->title ),
					'label_en' => vava_nav_menu_item_title_for_language( $item, 'en', (string) $item->title ),
					'url'      => (string) $item->url,
					'parent'   => absint( $item->menu_item_parent ?? 0 ),
				);
			}
		}
	}
	return $data;
}

function vava_homepage_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) { return; }
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	if ( $post_id && ! vava_homepage_is_home_page( $post_id ) ) { return; }
	wp_enqueue_media();
	wp_enqueue_style( 'vava-homepage-admin', get_theme_file_uri( 'assets/css/admin-homepage.css' ), array(), vava_asset_version( 'assets/css/admin-homepage.css' ) );
	wp_enqueue_style( 'vava-home-journal-admin-concept2', get_theme_file_uri( 'assets/css/admin-home-journal-concept2.css' ), array( 'vava-homepage-admin' ), vava_asset_version( 'assets/css/admin-home-journal-concept2.css' ) );
	wp_enqueue_script( 'vava-homepage-admin', get_theme_file_uri( 'assets/js/admin-homepage.js' ), array( 'jquery', 'jquery-ui-sortable' ), vava_asset_version( 'assets/js/admin-homepage.js' ), true );
	wp_localize_script( 'vava-homepage-admin', 'vavaHomepageAdmin', array(
		'uploadUrl'   => admin_url( 'async-upload.php' ),
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'previewNonce' => wp_create_nonce( 'vava_homepage_live_preview' ),
		'uploadNonce' => wp_create_nonce( 'media-form' ),
		'postId'      => $post_id,
		'maxImageMb'  => 20,
		'maxVideoMb'  => 200,
		'socialPlatforms' => vava_homepage_social_platforms(),
		'menus'           => vava_homepage_admin_menu_preview_data(),
	) );
}
add_action( 'admin_enqueue_scripts', 'vava_homepage_admin_assets' );

function vava_homepage_assign_existing_page(): void {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'vava_homepage_template_migrated_v2' ) ) { return; }
	$pages  = get_pages( array( 'post_status' => array( 'publish', 'draft', 'private' ), 'sort_column' => 'ID', 'sort_order' => 'ASC' ) );
	$target = null;
	foreach ( $pages as $page ) { if ( 'الصفحة الرئيسية' === trim( $page->post_title ) ) { $target = $page; break; } }
	if ( $target instanceof WP_Post ) {
		update_post_meta( $target->ID, '_wp_page_template', vava_homepage_template_slug() );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $target->ID );
		update_option( 'vava_homepage_template_migrated_v2', (int) $target->ID, false );
	}
}
add_action( 'admin_init', 'vava_homepage_assign_existing_page' );
