<?php
/**
 * Bilingual “About VAVA” page template fields and editor.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

function vava_about_template_slug(): string {
	return 'page-templates/about-vava.php';
}

function vava_about_is_page( int $post_id ): bool {
	if ( $post_id <= 0 ) {
		return false;
	}
	$template = get_page_template_slug( $post_id );
	$slug     = (string) get_post_field( 'post_name', $post_id );
	$title    = trim( (string) get_post_field( 'post_title', $post_id ) );
	return vava_about_template_slug() === $template
		|| 'about-vava' === $slug
		|| in_array( $title, array( 'عن VAVA', 'عن فافا', 'About VAVA' ), true );
}

function vava_about_meta_key( string $base, string $lang ): string {
	return 'en' === $lang ? $base . '_en' : $base;
}

function vava_about_defaults( string $lang = 'ar' ): array {
	if ( 'en' === $lang ) {
		return array(
			'_vava_about_hero_eyebrow' => 'About VAVA Living',
			'_vava_about_hero_title' => 'A Space for Returning',
			'_vava_about_hero_lead' => 'VAVA Living is a space for returning... A return to the innate way of living woven into our nature.',
			'_vava_about_hero_note' => 'At VAVA Living, we believe that our flourishing cannot be separated from the flourishing of life around us. Rooted in Ayurveda and open to other integrative sciences and wisdom traditions, VAVA grows as a space for conscious living, knowledge, experiences, and deeper connection with life in all its forms.',
			'_vava_about_hero_tag_1' => 'Conscious Living',
			'_vava_about_hero_tag_2' => 'Knowledge & Experiences',
			'_vava_about_hero_tag_3' => 'Deeper Connection',
			'_vava_about_story_eyebrow' => 'The story and the human behind the vision',
			'_vava_about_story_title' => 'Hello, I am Norah',
			'_vava_about_story_intro' => '',
			'_vava_about_story_items' => array(
				array( 'text' => 'For a long time, my curiosity reached beyond what appears on the surface. I was not satisfied with what could be seen; I wanted to understand what fatigue hides, what symptoms whisper, and what life tries to express through our different experiences.', 'style' => 'soft' ),
				array( 'text' => 'My journey did not begin with study or work, but with a deeply personal search for balance, harmony, and an understanding beyond quick fixes and ready-made formulas.', 'style' => 'normal' ),
				array( 'text' => 'Again and again, I returned to one conviction: understanding is deeper than fixing, and the human being—like every living presence—deserves to be seen as a whole, not as separate parts.', 'style' => 'normal' ),
				array( 'text' => 'When I discovered Ayurveda and went deeper into it, I found more than knowledge. I found a different way to understand the human being, life, and the relationship between them.', 'style' => 'normal' ),
				array( 'text' => 'Over time, this understanding was no longer only a personal experience; it became the way I approach life, the human being, and everything around us. From here, VAVA Living was born.', 'style' => 'whisper' ),
			),
			'_vava_about_why_eyebrow' => 'The meaning, not only the word',
			'_vava_about_why_title' => 'Why VAVA?',
			'_vava_about_why_items' => array(
				array( 'text' => 'The name VAVA is inspired by the word VAVAVOOM—that feeling that is difficult to explain, yet can be sensed.', 'style' => 'poetic' ),
				array( 'text' => 'The feeling of life when it is alive, present, and filled with something that is hard to name, but real.', 'style' => 'normal' ),
				array( 'text' => 'For us, VAVA is more than a name. It is the feeling we hope flows gently through our sessions, experiences, selections, and everything we create to leave a softer effect on life around us.', 'style' => 'soft' ),
			),
			'_vava_about_features_eyebrow' => 'What shapes the experience',
			'_vava_about_features_title' => 'VAVA Living Features',
			'_vava_about_features_intro' => 'These are not promotional features as much as they are a way of holding the space: calm, safe, flexible, and able to grow with people and life around them.',
			'_vava_about_feature_items' => array(
				array( 'title' => 'Privacy & Holding Space', 'text' => 'A safe space where your journey is respected with quiet confidentiality.' ),
				array( 'title' => 'Designed Around You', 'text' => 'An experience that begins from your need, not from ready-made templates.' ),
				array( 'title' => 'Flexible & Gentle', 'text' => 'We meet you where you are, in the time and rhythm that suits you.' ),
				array( 'title' => 'A Living Platform', 'text' => 'VAVA is not sessions alone; it is a growing space for experiences, tools, and selections that support more conscious living.' ),
			),
			'_vava_about_vision_eyebrow' => 'A vision growing gently',
			'_vava_about_vision_title' => 'The VAVA Vision',
			'_vava_about_vision_intro' => 'What you see today is only the beginning; a vision that grows in the same way it believes in life: gently, consciously, and with respect for life in all its forms.',
			'_vava_about_vision_items' => array(
				array( 'title' => 'Today', 'text' => 'VAVA begins through individual guidance, thoughtful selections, and knowledge that can be lived in daily details.' ),
				array( 'title' => 'Over time', 'text' => 'The vision grows to include programs, experiences, workshops, and wider spaces for learning, connection, and conscious living.' ),
				array( 'title' => 'The impact', 'text' => 'What we create is not meant to serve only today, but to leave a softer and more balanced effect on life around us.' ),
			),
			'_vava_about_vision_dream' => '',
			'_vava_about_invite_eyebrow' => '',
			'_vava_about_invite_title' => 'If something here touches you...',
			'_vava_about_invite_description' => 'Welcome. Whether you came to explore a pathway, read, shop, connect, or simply come closer to this world, VAVA is an open space for you.',
			'_vava_about_invite_button_1_text' => 'Explore VAVA Pathways',
			'_vava_about_invite_button_1_url' => vava_page_url( 'paths-vava' ),
			'_vava_about_invite_button_2_text' => 'Journal',
			'_vava_about_invite_button_2_url' => vava_page_url( 'journal' ),
			'_vava_about_invite_button_3_text' => 'Contact',
			'_vava_about_invite_button_3_url' => vava_page_url( 'contact' ),
		);
	}

	return array(
		'_vava_about_hero_eyebrow' => 'عن VAVA Living',
		'_vava_about_hero_title' => 'مساحة لإحياء الحياة',
		'_vava_about_hero_lead' => 'في فافا ليفينق، نؤمن بأن ازدهارنا لا ينفصل عن ازدهار الحياة من حولنا.',
		'_vava_about_hero_note' => "متجذّرة في الأيورفيدا، ومنفتحة على علوم وحِكم تكاملية أخرى، تنمو فافا كمساحة للحياة الواعية، والمعرفة، والتجارب، والاتصال الأعمق بالحياة بكل ما فيها.\n\nوما يبدأ اليوم كمساحة رقمية، هو بداية نظام حيّ ينمو… ليصبح يومًا ما مكانًا يُعاش، ويُختبر، وتلتقي فيه هذه الرؤية على أرض الواقع.",
		'_vava_about_hero_tag_1' => 'حياة واعية',
		'_vava_about_hero_tag_2' => 'معرفة وتجارب',
		'_vava_about_hero_tag_3' => 'اتصال أعمق',
		'_vava_about_story_eyebrow' => 'القصة والإنسان خلف الرؤية',
		'_vava_about_story_title' => 'أهلًا، معكم نورة...',
		'_vava_about_story_intro' => '',
		'_vava_about_story_items' => array(
			array( 'text' => 'منذ وقت طويل، كان فضولي يتجاوز الظاهر. لم أكن أكتفي بما يُرى، بل كنت أبحث عمّا يُخفيه التعب، وما تقوله الأعراض بصوت خافت، وما تحاول الحياة أن تعبّر عنه عبر تجاربنا المختلفة.', 'style' => 'soft' ),
			array( 'text' => 'رحلتي لم تبدأ من الدراسة أو العمل، بل من بحث شخصي عميق عن التوازن، والتناغم، وفهم ما هو أبعد من الحلول السريعة والقوالب الجاهزة.', 'style' => 'normal' ),
			array( 'text' => 'وفي كل مرة، كنت أعود إلى قناعة واحدة: أن الفهم أعمق من الإصلاح، وأن الإنسان—وكل كيان—يستحق أن يُرى ككلّ، لا كأجزاء منفصلة.', 'style' => 'normal' ),
			array( 'text' => 'حين تعرّفت على الأيورفيدا وتعمّقت فيها، لم أجد مجرد معرفة... بل وجدت طريقة مختلفة لفهم الإنسان، والحياة، وما بينهما.', 'style' => 'normal' ),
			array( 'text' => 'فهمًا يذكّرنا أن أجسادنا ليست منفصلة عن أفكارنا، وأن علاقتنا بالغذاء، والراحة، والإبداع، والبيئة، والإيقاع الذي نعيش به... كلها أجزاء من قصتنا الممتدة.', 'style' => 'whisper' ),
			array( 'text' => 'ومع الوقت، لم يعد هذا الفهم تجربة شخصية فقط، بل أصبح الطريقة التي أقترب بها من الحياة، ومن الإنسان، ومن كل ما يحيط بنا. ومن هنا وُلدت VAVA Living.', 'style' => 'whisper' ),
		),
		'_vava_about_why_eyebrow' => 'المعنى لا الكلمة فقط',
		'_vava_about_why_title' => 'لماذا VAVA؟',
		'_vava_about_why_items' => array(
			array( 'text' => 'اسم VAVA مستوحى من كلمة VAVAVOOM—ذلك الإحساس الذي لا يُشرح بسهولة... لكنه يُستشعر.', 'style' => 'poetic' ),
			array( 'text' => 'الإحساس بالحياة حين تكون نابضة، حاضرة، ومليئة بشيء يصعب تسميته... لكنه حقيقي.', 'style' => 'normal' ),
			array( 'text' => 'ليس مجرد طاقة أو حماس، بل ذلك الشعور الذي يظهر حين تتناغم الأشياء في مكانها الطبيعي؛ حين يصبح الداخل والخارج أكثر انسجامًا، وتشعر أن شيئًا عاد إلى مكانه الطبيعي.', 'style' => 'normal' ),
			array( 'text' => 'بالنسبة لنا، VAVA ليست مجرد اسم—بل الإحساس الذي نأمل أن ينساب بهدوء عبر جلساتنا، وتجاربنا، ومختاراتنا، وكل ما نخلقه ليترك أثرًا ألطف في الحياة من حولنا.', 'style' => 'soft' ),
		),
		'_vava_about_features_eyebrow' => 'ما يميز التجربة',
		'_vava_about_features_title' => 'مميزات VAVA Living',
		'_vava_about_features_intro' => 'ليست مميزات تسويقية بقدر ما هي طريقة في بناء المساحة: هادئة، آمنة، مرنة، وتنمو مع احتياج الإنسان والحياة من حوله.',
		'_vava_about_feature_items' => array(
			array( 'title' => 'خصوصية واحتواء', 'text' => 'مساحة آمنة تُحترم فيها رحلتك بكل سرية وهدوء.' ),
			array( 'title' => 'مصممة لك', 'text' => 'تجربة تنبع من احتياجك، لا من قوالب جاهزة.' ),
			array( 'title' => 'مرونة وانسياب', 'text' => 'نلتقيك حيث أنت، في الوقت والإيقاع الذي يناسبك.' ),
			array( 'title' => 'منصة حيّة', 'text' => 'VAVA ليست جلسات فقط، بل مساحة تنمو لتجارب وأدوات ومختارات تدعم أسلوب حياة أكثر وعيًا.' ),
		),
		'_vava_about_vision_eyebrow' => 'رؤية تنمو بهدوء',
		'_vava_about_vision_title' => 'رؤية VAVA',
		'_vava_about_vision_intro' => 'ما تراه اليوم هو البداية فقط؛ رؤية تنمو بطريقة تشبه ما تؤمن به: بلطف، ووعي، واحترام للحياة بكل أشكالها.',
		'_vava_about_vision_items' => array(
			array( 'title' => 'اليوم', 'text' => 'تبدأ VAVA عبر الإرشاد الفردي، والمختارات المدروسة، والمعرفة التي يمكن أن تُعاش في تفاصيل الحياة اليومية.' ),
			array( 'title' => 'مع الوقت', 'text' => 'تنمو الرؤية لتشمل برامج، وتجارب، وورش، ومساحات أوسع للتعلّم، والتواصل، والعيش الواعي.' ),
			array( 'title' => 'الأثر', 'text' => 'ما نخلقه لا نريده أن يخدمنا اليوم فقط، بل أن يترك أثرًا أكثر لطفًا واتزانًا في الحياة من حولنا وفيما يأتي بعدنا.' ),
		),
		'_vava_about_vision_dream' => 'والحلم الأكبر؟ أن تصبح VAVA يومًا ما مكانًا حيًّا يُعاش على أرض الواقع—مساحة يلتقي فيها الإنسان، والمعرفة، والطبيعة، والحياة، في تناغم أكثر صدقًا، ووعيًا، واستدامة.',
		'_vava_about_invite_eyebrow' => '',
		'_vava_about_invite_title' => 'إن شعرت أن شيئًا هنا يلامسك...',
		'_vava_about_invite_description' => 'فأهلًا بك. سواء جئت لتستكشف مسارًا، أو تقرأ، أو تتسوّق، أو تتواصل، أو ببساطة لتقترب أكثر من هذا العالم—VAVA مساحة مفتوحة لك.',
		'_vava_about_invite_button_1_text' => 'استكشف مسارات VAVA',
		'_vava_about_invite_button_1_url' => vava_page_url( 'paths-vava' ),
		'_vava_about_invite_button_2_text' => 'المجلة',
		'_vava_about_invite_button_2_url' => vava_page_url( 'journal' ),
		'_vava_about_invite_button_3_text' => 'التواصل',
		'_vava_about_invite_button_3_url' => vava_page_url( 'contact' ),
	);
}

function vava_about_shared_defaults(): array {
	return array(
		'_vava_about_hero_image_id'  => 0,
		'_vava_about_story_image_id' => 0,
		'_vava_about_why_image_id'   => 0,
	);
}

function vava_about_field( int $post_id, string $base, string $lang = 'ar', $fallback = null ) {
	$key   = vava_about_meta_key( $base, $lang );
	if ( metadata_exists( 'post', $post_id, $key ) ) {
		return get_post_meta( $post_id, $key, true );
	}
	$defaults = vava_about_defaults( $lang );
	return null !== $fallback ? $fallback : ( $defaults[ $base ] ?? '' );
}

function vava_about_shared_field( int $post_id, string $key, $fallback = null ) {
	if ( metadata_exists( 'post', $post_id, $key ) ) {
		return get_post_meta( $post_id, $key, true );
	}
	$defaults = vava_about_shared_defaults();
	return null !== $fallback ? $fallback : ( $defaults[ $key ] ?? '' );
}

/**
 * Return a single text block, migrating the former repeater structure on read.
 */
function vava_about_combined_text( int $post_id, string $content_base, string $legacy_base, string $lang = 'ar' ): string {
	$content_key = vava_about_meta_key( $content_base, $lang );
	if ( metadata_exists( 'post', $post_id, $content_key ) ) {
		return trim( (string) get_post_meta( $post_id, $content_key, true ) );
	}

	$legacy = vava_about_field( $post_id, $legacy_base, $lang, array() );
	if ( ! is_array( $legacy ) ) {
		return trim( (string) $legacy );
	}

	$paragraphs = array();
	foreach ( $legacy as $item ) {
		$text = is_array( $item ) ? trim( (string) ( $item['text'] ?? '' ) ) : trim( (string) $item );
		if ( '' !== $text ) {
			$paragraphs[] = $text;
		}
	}
	return implode( "\n\n", $paragraphs );
}

function vava_about_story_content( int $post_id, string $lang = 'ar' ): string {
	return vava_about_combined_text( $post_id, '_vava_about_story_content', '_vava_about_story_items', $lang );
}

function vava_about_why_content( int $post_id, string $lang = 'ar' ): string {
	return vava_about_combined_text( $post_id, '_vava_about_why_content', '_vava_about_why_items', $lang );
}

function vava_about_optional_image_url( int $attachment_id, string $size = 'full' ): string {
	if ( $attachment_id <= 0 ) {
		return '';
	}
	$url = wp_get_attachment_image_url( $attachment_id, $size );
	return $url ? (string) $url : '';
}

function vava_about_admin_text( string $key, string $lang = 'ar' ): string {
	$texts = array(
		'meta_box_title'        => array( 'ar' => 'إعدادات صفحة عن VAVA', 'en' => 'About VAVA Page Settings' ),
		'intro_title'           => array( 'ar' => 'محتوى صفحة «عن VAVA» يُدار من هنا.', 'en' => 'Manage the “About VAVA” page content here.' ),
		'intro_description'     => array( 'ar' => 'العربية والإنجليزية داخل نفس صفحة WordPress، والصور مشتركة، ويمكن حفظ جميع التغييرات معًا.', 'en' => 'Arabic and English are managed in the same WordPress page, shared images stay synchronized, and all changes can be saved together.' ),
		'sections_aria'         => array( 'ar' => 'أقسام صفحة عن VAVA', 'en' => 'About VAVA page sections' ),
		'fields_language'       => array( 'ar' => 'لغة الحقول', 'en' => 'Fields language' ),
		'update'                => array( 'ar' => 'تحديث', 'en' => 'Update' ),
		'live_preview'          => array( 'ar' => 'معاينة مباشرة', 'en' => 'Live preview' ),
		'shared_files'          => array( 'ar' => 'ملفات مشتركة بين اللغتين', 'en' => 'Files shared between both languages' ),
		'hero_image'            => array( 'ar' => 'صورة الهيرو', 'en' => 'Hero image' ),
		'story_image'           => array( 'ar' => 'صورة قصة VAVA', 'en' => 'VAVA story image' ),
		'why_image'             => array( 'ar' => 'صورة قسم لماذا VAVA؟', 'en' => 'Why VAVA? section image' ),
		'choose_image'          => array( 'ar' => 'اختيار أو استبدال الصورة', 'en' => 'Choose or replace image' ),
		'shared_image_help'     => array( 'ar' => 'الصورة مشتركة بين العربية والإنجليزية', 'en' => 'The image is shared between Arabic and English' ),
		'clear_image'           => array( 'ar' => 'حذف الملف', 'en' => 'Delete file' ),
		'choose_replace'        => array( 'ar' => 'اختيار أو استبدال', 'en' => 'Choose or replace' ),
		'drag_image'            => array( 'ar' => 'اسحب الصورة وأفلتها هنا', 'en' => 'Drag and drop the image here' ),
		'choose_library'        => array( 'ar' => 'أو اضغط للاختيار من مكتبة الوسائط', 'en' => 'Or click to choose from the media library' ),
		'repeater_help'         => array( 'ar' => 'يمكن السحب لإعادة ترتيب البطاقات.', 'en' => 'Drag to reorder the cards.' ),
		'button_settings'       => array( 'ar' => 'الزر %d', 'en' => 'Button %d' ),
		'buttons_block'         => array( 'ar' => 'الأزرار', 'en' => 'Buttons' ),
		'button_text'           => array( 'ar' => 'نص الزر', 'en' => 'Button text' ),
		'link_type'             => array( 'ar' => 'نوع الرابط', 'en' => 'Link type' ),
		'destination'           => array( 'ar' => 'الوجهة', 'en' => 'Destination' ),
		'add_item'              => array( 'ar' => 'إضافة عنصر', 'en' => 'Add item' ),
		'delete'                => array( 'ar' => 'حذف', 'en' => 'Delete' ),
		'field_title'           => array( 'ar' => 'العنوان', 'en' => 'Title' ),
		'field_description'     => array( 'ar' => 'الوصف', 'en' => 'Description' ),
		'field_text'            => array( 'ar' => 'النص', 'en' => 'Text' ),
		'field_format'          => array( 'ar' => 'التنسيق', 'en' => 'Formatting' ),
		'style_normal'          => array( 'ar' => 'نص عادي', 'en' => 'Normal text' ),
		'style_soft'            => array( 'ar' => 'بطاقة ناعمة', 'en' => 'Soft card' ),
		'style_whisper'         => array( 'ar' => 'نص هادئ جانبي', 'en' => 'Quiet side text' ),
		'style_poetic'          => array( 'ar' => 'نص شعري', 'en' => 'Poetic text' ),
		'feature_cards'         => array( 'ar' => 'بطاقات المميزات', 'en' => 'Feature cards' ),
		'vision_cards'          => array( 'ar' => 'بطاقات الرؤية', 'en' => 'Vision cards' ),
		'button_link'           => array( 'ar' => 'رابط الزر %d', 'en' => 'Button %d link' ),
		'wordpress_page'        => array( 'ar' => 'صفحة', 'en' => 'Page' ),
		'manual_link'           => array( 'ar' => 'رابط يدوي', 'en' => 'Manual link' ),
		'choose_page'           => array( 'ar' => 'اختر الصفحة', 'en' => 'Choose page' ),
	);
	$lang = 'en' === $lang ? 'en' : 'ar';
	return (string) ( $texts[ $key ][ $lang ] ?? $texts[ $key ]['ar'] ?? $key );
}

function vava_about_image_url( int $attachment_id, string $fallback_asset, string $size = 'full' ): string {
	if ( $attachment_id > 0 ) {
		$url = wp_get_attachment_image_url( $attachment_id, $size );
		if ( $url ) {
			return (string) $url;
		}
	}
	return vava_asset_uri( $fallback_asset );
}

function vava_about_sections( string $lang = 'ar' ): array {
	$is_en = 'en' === $lang;
	return array(
		'hero'     => $is_en ? 'Hero' : 'الهيرو',
		'story'    => $is_en ? 'VAVA Story' : 'قصة VAVA',
		'why'      => $is_en ? 'Why VAVA?' : 'لماذا VAVA؟',
		'features' => $is_en ? 'VAVA Features' : 'مميزات VAVA',
		'vision'   => $is_en ? 'Vision' : 'الرؤية',
		'invite'   => $is_en ? 'Closing Invitation' : 'الدعوة الختامية',
	);
}

function vava_about_section_icon( string $section ): string {
	$icons = array(
		'hero' => '<svg viewBox="0 0 32 32"><rect x="3.5" y="5" width="25" height="22" rx="3"/><circle cx="11" cy="12" r="2.3"/><path d="m6 23 7-7 5 5 3-3 5 5"/></svg>',
		'story' => '<svg viewBox="0 0 32 32"><path d="M6 5h15a5 5 0 0 1 5 5v17H11a5 5 0 0 1-5-5Z"/><path d="M11 11h10M11 16h10M11 21h7"/></svg>',
		'why' => '<svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="11"/><path d="M13 12a3.5 3.5 0 1 1 5.5 2.9C17 16 16 16.8 16 19"/><path d="M16 23h.01"/></svg>',
		'features' => '<svg viewBox="0 0 32 32"><path d="m16 4 3.5 7 7.5 1-5.5 5.3 1.4 7.7-6.9-3.7L9.1 25l1.4-7.7L5 12l7.5-1Z"/></svg>',
		'vision' => '<svg viewBox="0 0 32 32"><path d="M4 16s4.5-8 12-8 12 8 12 8-4.5 8-12 8S4 16 4 16Z"/><circle cx="16" cy="16" r="3.5"/></svg>',
		'invite' => '<svg viewBox="0 0 32 32"><rect x="4" y="7" width="24" height="18" rx="3"/><path d="m6 10 10 8 10-8"/></svg>',
	);
	return $icons[ $section ] ?? $icons['hero'];
}

function vava_about_add_meta_boxes( string $post_type, WP_Post $post ): void {
	if ( 'page' !== $post_type || ! vava_about_is_page( (int) $post->ID ) ) {
		return;
	}
	remove_meta_box( 'postdivrich', 'page', 'normal' );
	remove_meta_box( 'postimagediv', 'page', 'side' );
	add_meta_box( 'vava_homepage_settings', vava_about_admin_text( 'meta_box_title', 'ar' ), 'vava_about_render_settings', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'vava_about_add_meta_boxes', 10, 2 );


function vava_about_render_page_identity( WP_Post $post ): void {
	vava_render_bilingual_page_identity( $post, (string) ( get_permalink( $post ) ?: vava_page_url( 'about-vava' ) ) );
}

function vava_about_scalar_fields( string $section, string $lang = 'ar' ): array {
	$is_en = 'en' === $lang;
	$map = array(
		'hero' => array(
			array( '_vava_about_hero_eyebrow', $is_en ? 'Small text' : 'النص الصغير', 'text', 'hero-eyebrow' ),
			array( '_vava_about_hero_title', $is_en ? 'Main title' : 'العنوان الرئيسي', 'text', 'hero-title' ),
			array( '_vava_about_hero_lead', $is_en ? 'Introduction' : 'المقدمة', 'textarea', 'hero-lead' ),
			array( '_vava_about_hero_note', $is_en ? 'Note' : 'الملاحظة', 'richtext', 'hero-note' ),
		),
		'story' => array(
			array( '_vava_about_story_eyebrow', $is_en ? 'Small text' : 'النص الصغير', 'text', 'story-eyebrow' ),
			array( '_vava_about_story_title', $is_en ? 'Title' : 'العنوان', 'text', 'story-title' ),
			array( '_vava_about_story_intro', $is_en ? 'Introduction' : 'المقدمة', 'textarea', 'story-intro' ),
			array( '_vava_about_story_content', $is_en ? 'Story text' : 'نص القصة', 'richtext', 'story-content' ),
		),
		'why' => array(
			array( '_vava_about_why_eyebrow', $is_en ? 'Small text' : 'النص الصغير', 'text', 'why-eyebrow' ),
			array( '_vava_about_why_title', $is_en ? 'Title' : 'العنوان', 'text', 'why-title' ),
			array( '_vava_about_why_content', $is_en ? 'Why VAVA? text' : 'نص لماذا VAVA؟', 'richtext', 'why-content' ),
		),
		'features' => array(
			array( '_vava_about_features_eyebrow', $is_en ? 'Small text' : 'النص الصغير', 'text', 'features-eyebrow' ),
			array( '_vava_about_features_title', $is_en ? 'Title' : 'العنوان', 'text', 'features-title' ),
			array( '_vava_about_features_intro', $is_en ? 'Description' : 'الوصف', 'textarea', 'features-intro' ),
		),
		'vision' => array(
			array( '_vava_about_vision_eyebrow', $is_en ? 'Small text' : 'النص الصغير', 'text', 'vision-eyebrow' ),
			array( '_vava_about_vision_title', $is_en ? 'Title' : 'العنوان', 'text', 'vision-title' ),
			array( '_vava_about_vision_intro', $is_en ? 'Description' : 'الوصف', 'textarea', 'vision-intro' ),
			array( '_vava_about_vision_dream', $is_en ? 'Closing phrase' : 'العبارة الختامية', 'richtext', 'vision-dream' ),
		),
		'invite' => array(
			array( '_vava_about_invite_eyebrow', $is_en ? 'Small text' : 'النص الصغير', 'text', 'invite-eyebrow' ),
			array( '_vava_about_invite_title', $is_en ? 'Title' : 'العنوان', 'text', 'invite-title' ),
			array( '_vava_about_invite_description', $is_en ? 'Description' : 'الوصف', 'richtext', 'invite-description' ),
		),
	);
	return $map[ $section ] ?? array();
}

function vava_about_render_field( WP_Post $post, array $field, string $lang ): void {
	list( $base, $label, $type, $preview ) = $field;
	$key = vava_about_meta_key( $base, $lang );
	if ( '_vava_about_story_content' === $base ) {
		$value = vava_about_story_content( (int) $post->ID, $lang );
	} elseif ( '_vava_about_why_content' === $base ) {
		$value = vava_about_why_content( (int) $post->ID, $lang );
	} else {
		$value = vava_about_field( (int) $post->ID, $base, $lang );
	}
	$is_content = in_array( $base, array( '_vava_about_story_content', '_vava_about_why_content' ), true );
	$full       = in_array( $type, array( 'textarea', 'richtext' ), true ) ? ' vava-field-full' : '';
	$full      .= $is_content ? ' vava-about-single-text-field' : '';
	?>
	<div class="vava-field<?php echo esc_attr( $full ); ?>"><label for="<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
	<?php if ( 'richtext' === $type ) : ?>
		<?php vava_render_richtext_editor( array( 'name' => $key, 'id' => $key, 'value' => (string) $value, 'dir' => 'en' === $lang ? 'ltr' : 'rtl', 'preview' => $preview, 'preview_namespace' => 'about' ) ); ?>
	<?php elseif ( 'textarea' === $type ) : ?>
		<textarea class="widefat vava-about-plain-textarea" data-about-preview="<?php echo esc_attr( $preview ); ?>" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" rows="5"><?php echo esc_textarea( (string) $value ); ?></textarea>
	<?php else : ?>
		<input class="widefat" data-about-preview="<?php echo esc_attr( $preview ); ?>" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="text" value="<?php echo esc_attr( (string) $value ); ?>"/>
	<?php endif; ?>
	</div>
	<?php
}

function vava_about_fixed_items( int $post_id, string $base, string $lang, int $count ): array {
	$items    = vava_about_field( $post_id, $base, $lang, array() );
	$defaults = vava_about_defaults( $lang );
	$fallback = isset( $defaults[ $base ] ) && is_array( $defaults[ $base ] ) ? array_values( $defaults[ $base ] ) : array();
	$items    = is_array( $items ) ? array_values( $items ) : array();

	for ( $index = 0; $index < $count; $index++ ) {
		if ( ! isset( $items[ $index ] ) || ! is_array( $items[ $index ] ) ) {
			$items[ $index ] = isset( $fallback[ $index ] ) && is_array( $fallback[ $index ] ) ? $fallback[ $index ] : array( 'title' => '', 'text' => '' );
		}
	}

	return array_slice( $items, 0, $count );
}

function vava_about_render_fixed_card_item( string $key, array $item, int $index, string $lang, bool $open = false ): void {
	$title       = (string) ( $item['title'] ?? '' );
	$toggle_id   = sanitize_html_class( $key . '_accordion_' . $index );
	$drag_label  = 'en' === $lang ? 'Drag to reorder' : 'اسحب لإعادة الترتيب';
	$toggle_text = 'en' === $lang ? 'Open or close card settings' : 'فتح أو إغلاق إعدادات البطاقة';
	?>
	<div class="vava-repeater-item vava-about-fixed-card<?php echo $open ? ' is-open' : ''; ?>" data-about-repeat-item data-about-accordion-item>
		<div class="vava-about-fixed-card-header">
			<div class="vava-repeater-handle" aria-label="<?php echo esc_attr( $drag_label ); ?>" title="<?php echo esc_attr( $drag_label ); ?>"><span aria-hidden="true">⋮⋮</span></div>
			<button aria-controls="<?php echo esc_attr( $toggle_id ); ?>" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" class="vava-about-fixed-card-toggle" data-about-accordion-toggle type="button">
				<strong data-about-accordion-title><?php echo esc_html( $title ); ?></strong>
				<svg aria-hidden="true" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg>
				<span class="screen-reader-text"><?php echo esc_html( $toggle_text ); ?></span>
			</button>
		</div>
		<div class="vava-about-fixed-card-body" data-about-accordion-body id="<?php echo esc_attr( $toggle_id ); ?>"<?php echo $open ? '' : ' hidden'; ?>>
			<div class="vava-repeater-fields">
				<label><span><?php echo esc_html( vava_about_admin_text( 'field_title', $lang ) ); ?></span><input class="widefat" name="<?php echo esc_attr( $key . '[' . $index . '][title]' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"/></label>
				<label><span><?php echo esc_html( vava_about_admin_text( 'field_description', $lang ) ); ?></span><?php vava_render_richtext_editor( array( 'name' => $key . '[' . $index . '][text]', 'id' => sanitize_html_class( $key . '_' . $index . '_text' ), 'value' => (string) ( $item['text'] ?? '' ), 'dir' => 'en' === $lang ? 'ltr' : 'rtl', 'class' => 'vava-about-card-richtext' ) ); ?></label>
			</div>
		</div>
	</div>
	<?php
}

function vava_about_render_fixed_cards( WP_Post $post, string $base, string $lang, string $title, string $preview_list, int $count ): void {
	$key   = vava_about_meta_key( $base, $lang );
	$items = vava_about_fixed_items( (int) $post->ID, $base, $lang, $count );
	?>
	<div class="vava-repeater-card vava-about-fixed-cards" data-about-repeater data-fixed-count="<?php echo esc_attr( (string) $count ); ?>" data-kind="cards" data-preview-list="<?php echo esc_attr( $preview_list ); ?>">
		<input name="<?php echo esc_attr( $key . '_present' ); ?>" type="hidden" value="1"/>
		<div class="vava-repeater-heading"><div><h3><?php echo esc_html( $title ); ?></h3><p><?php echo esc_html( vava_about_admin_text( 'repeater_help', $lang ) ); ?></p></div></div>
		<div class="vava-repeater-list" data-about-repeat-list><?php foreach ( $items as $index => $item ) { vava_about_render_fixed_card_item( $key, $item, $index, $lang, 0 === $index ); } ?></div>
	</div>
	<?php
}

function vava_about_render_media_field( WP_Post $post, string $key, string $label_ar, string $label_en, string $fallback ): void {
	$attachment_id = absint( vava_about_shared_field( (int) $post->ID, $key, 0 ) );
	$id            = sanitize_html_class( ltrim( $key, '_' ) );
	$fallback_url = '' !== $fallback ? vava_asset_uri( $fallback ) : '';
	$current_url  = vava_homepage_media_current_url( $attachment_id, 'image', $fallback_url );
	?>
	<div class="vava-admin-field vava-admin-field-media vava-admin-field-wide vava-about-media-field" data-about-media-field data-fallback-url="<?php echo esc_url( $fallback_url ); ?>">
		<label for="<?php echo esc_attr( $id ); ?>"><strong<?php echo vava_admin_i18n_attributes( $label_ar, $label_en ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $label_ar ); ?></strong></label>
		<div class="vava-media-field" data-media-type="image">
			<input class="vava-media-id" data-about-media-id data-fallback-url="<?php echo esc_url( $fallback_url ); ?>" data-media-url="<?php echo esc_url( $current_url ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $key ); ?>" type="hidden" value="<?php echo esc_attr( (string) $attachment_id ); ?>"/>
			<div class="vava-media-dropzone" role="button" tabindex="0">
				<div class="vava-media-preview"><?php echo vava_homepage_media_preview_markup( $attachment_id, 'image' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<div class="vava-upload-progress" aria-hidden="true"><span></span></div>
			</div>
			<div class="vava-media-actions">
				<button class="button vava-media-select" type="button"<?php echo vava_admin_i18n_attributes( vava_about_admin_text( 'choose_replace', 'ar' ), vava_about_admin_text( 'choose_replace', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_about_admin_text( 'choose_replace', 'ar' ) ); ?></button>
				<button class="button vava-media-remove" type="button"<?php echo vava_admin_i18n_attributes( vava_about_admin_text( 'clear_image', 'ar' ), vava_about_admin_text( 'clear_image', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_about_admin_text( 'clear_image', 'ar' ) ); ?></button>
			</div>
		</div>
	</div>
	<?php
}

function vava_about_shared_link_values( int $post_id, int $button, string $lang = 'ar' ): array {
	$base           = '_vava_about_invite_button_' . $button . '_url';
	$canonical_key  = vava_about_meta_key( $base, 'ar' );
	$english_key    = vava_about_meta_key( $base, 'en' );
	$type           = (string) get_post_meta( $post_id, $canonical_key . '_type', true );
	$page_id        = absint( get_post_meta( $post_id, $canonical_key . '_page_id', true ) );
	$manual         = (string) get_post_meta( $post_id, $canonical_key . '_manual', true );

	$english_type   = (string) get_post_meta( $post_id, $english_key . '_type', true );
	$english_page   = absint( get_post_meta( $post_id, $english_key . '_page_id', true ) );
	$english_manual = (string) get_post_meta( $post_id, $english_key . '_manual', true );
	$canonical_empty = ! in_array( $type, array( 'page', 'manual' ), true )
		|| ( 'page' === $type && ! $page_id )
		|| ( 'manual' === $type && '' === trim( $manual ) );
	$english_ready = ( 'page' === $english_type && $english_page )
		|| ( 'manual' === $english_type && '' !== trim( $english_manual ) );
	if ( $canonical_empty && $english_ready ) {
		$type    = $english_type;
		$page_id = $english_page;
		$manual  = $english_manual;
	}

	$default = (string) vava_about_field( $post_id, $base, $lang );
	if ( ! in_array( $type, array( 'page', 'manual' ), true ) ) {
		$detected = $default ? url_to_postid( $default ) : 0;
		$type     = $detected ? 'page' : 'manual';
		$page_id  = $page_id ?: absint( $detected );
	}
	if ( '' === $manual ) {
		$manual = $default;
	}

	return array(
		'type'      => $type,
		'page_id'   => $page_id,
		'manual'    => $manual,
		'url'       => 'page' === $type && $page_id ? vava_localized_page_url( $page_id, $lang ) : $manual,
	);
}

function vava_about_render_link_fields( WP_Post $post, string $lang ): void {
	$pages = get_pages( array( 'post_status' => array( 'publish', 'draft', 'private' ), 'sort_column' => 'post_title' ) );
	?>
	<div class="vava-about-button-settings vava-about-buttons-block">
		<div class="vava-about-buttons-block-heading"><h3><?php echo esc_html( vava_about_admin_text( 'buttons_block', $lang ) ); ?></h3></div>
	<?php
	for ( $i = 1; $i <= 3; $i++ ) {
		$text_base  = '_vava_about_invite_button_' . $i . '_text';
		$text_key   = vava_about_meta_key( $text_base, $lang );
		$base       = '_vava_about_invite_button_' . $i . '_url';
		$key        = vava_about_meta_key( $base, $lang );
		$type_key   = $key . '_type';
		$page_key   = $key . '_page_id';
		$manual_key = $key . '_manual';
		$values     = vava_about_shared_link_values( (int) $post->ID, $i, $lang );
		$type       = (string) $values['type'];
		$page_id    = absint( $values['page_id'] );
		$manual     = (string) $values['manual'];
		?>
		<div class="vava-about-button-config vava-about-link-field" data-vava-shared-setting="about-invite-button-<?php echo esc_attr( (string) $i ); ?>">
			<div class="vava-about-button-row">
				<label><span><?php echo esc_html( vava_about_admin_text( 'button_text', $lang ) ); ?></span><input class="widefat" data-about-preview="invite-button-<?php echo esc_attr( (string) $i ); ?>" name="<?php echo esc_attr( $text_key ); ?>" type="text" value="<?php echo esc_attr( (string) vava_about_field( (int) $post->ID, $text_base, $lang ) ); ?>"/></label>
				<label><span><?php echo esc_html( vava_about_admin_text( 'link_type', $lang ) ); ?></span><select data-about-link-type name="<?php echo esc_attr( $type_key ); ?>"><option value="page" <?php selected( $type, 'page' ); ?>><?php echo esc_html( vava_about_admin_text( 'wordpress_page', $lang ) ); ?></option><option value="manual" <?php selected( $type, 'manual' ); ?>><?php echo esc_html( vava_about_admin_text( 'manual_link', $lang ) ); ?></option></select></label>
				<label class="vava-about-button-destination"><span><?php echo esc_html( vava_about_admin_text( 'destination', $lang ) ); ?></span><select data-about-page-choice name="<?php echo esc_attr( $page_key ); ?>"><option value="0"><?php echo esc_html( vava_about_admin_text( 'choose_page', $lang ) ); ?></option><?php foreach ( $pages as $page ) : ?><option value="<?php echo esc_attr( (string) $page->ID ); ?>" <?php selected( $page_id, (int) $page->ID ); ?>><?php echo esc_html( vava_bilingual_page_title( (int) $page->ID, $lang ) ); ?></option><?php endforeach; ?></select><input class="widefat" data-about-manual-url dir="ltr" name="<?php echo esc_attr( $manual_key ); ?>" type="url" value="<?php echo esc_attr( $manual ); ?>"/></label>
			</div>
		</div>
		<?php
	}
	?>
	</div>
	<?php
}

function vava_about_render_preview( WP_Post $post, string $section, string $lang ): void {
	$is_en = 'en' === $lang;
	?>
	<aside class="vava-live-preview" data-about-preview-panel data-preview-language="<?php echo esc_attr( $lang ); ?>" data-preview-section="<?php echo esc_attr( $section ); ?>" dir="<?php echo $is_en ? 'ltr' : 'rtl'; ?>"><header class="vava-live-preview-header"><div><strong><?php echo esc_html( $is_en ? 'Live preview' : 'معاينة مباشرة' ); ?></strong><span><?php echo esc_html( vava_about_sections( $lang )[ $section ] ?? '' ); ?></span></div><span class="vava-live-preview-dot" aria-hidden="true"></span></header><div class="vava-preview-viewport"><div class="vava-preview-stage"><div class="vava-preview-canvas vava-about-preview vava-about-preview-<?php echo esc_attr( $section ); ?>" data-preview-design-width="900" dir="<?php echo $is_en ? 'ltr' : 'rtl'; ?>">
	<?php if ( 'hero' === $section ) : ?>
		<div class="vava-about-preview-copy"><span data-preview-output="hero-eyebrow"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_hero_eyebrow', $lang ) ); ?></span><h3 data-preview-output="hero-title"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_hero_title', $lang ) ); ?></h3><div data-preview-output="hero-lead"><?php echo vava_richtext_output( vava_about_field( $post->ID, '_vava_about_hero_lead', $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><small data-preview-output="hero-note"><?php echo vava_richtext_output( vava_about_field( $post->ID, '_vava_about_hero_note', $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></small></div><div class="vava-about-preview-image" data-about-preview-image="hero" style="background-image:url('<?php echo esc_url( vava_about_image_url( absint( vava_about_shared_field( $post->ID, '_vava_about_hero_image_id', 0 ) ), 'assets/images/about-hero.png', 'medium_large' ) ); ?>')"></div>
	<?php elseif ( 'story' === $section ) : $story_preview_image = vava_about_optional_image_url( absint( vava_about_shared_field( $post->ID, '_vava_about_story_image_id', 0 ) ), 'medium_large' ); ?>
		<div class="vava-about-preview-side"><span data-preview-output="story-eyebrow"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_story_eyebrow', $lang ) ); ?></span><h3 data-preview-output="story-title"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_story_title', $lang ) ); ?></h3><div data-preview-output="story-intro"><?php echo vava_richtext_output( vava_about_field( $post->ID, '_vava_about_story_intro', $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div class="vava-about-preview-story-image<?php echo '' === $story_preview_image ? ' is-empty' : ''; ?>" data-about-preview-image="story"<?php if ( '' !== $story_preview_image ) : ?> style="background-image:url('<?php echo esc_url( $story_preview_image ); ?>')"<?php endif; ?>></div></div><div class="vava-about-preview-text-block soft" data-preview-output="story-content"><?php echo vava_richtext_output( vava_about_story_content( (int) $post->ID, $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<?php elseif ( 'why' === $section ) : ?>
		<div class="vava-about-preview-image" data-about-preview-image="why" style="background-image:url('<?php echo esc_url( vava_about_image_url( absint( vava_about_shared_field( $post->ID, '_vava_about_why_image_id', 0 ) ), 'assets/images/about-why-vava.png', 'medium_large' ) ); ?>')"></div><div class="vava-about-preview-copy"><span data-preview-output="why-eyebrow"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_why_eyebrow', $lang ) ); ?></span><h3 data-preview-output="why-title"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_why_title', $lang ) ); ?></h3><div class="vava-about-preview-text-block soft" data-preview-output="why-content"><?php echo vava_richtext_output( vava_about_why_content( (int) $post->ID, $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div>
	<?php elseif ( 'features' === $section ) : $feature_items = vava_about_fixed_items( (int) $post->ID, '_vava_about_feature_items', $lang, 4 ); ?>
		<div class="vava-about-preview-heading"><span data-preview-output="features-eyebrow"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_features_eyebrow', $lang ) ); ?></span><h3 data-preview-output="features-title"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_features_title', $lang ) ); ?></h3><div data-preview-output="features-intro"><?php echo vava_richtext_output( vava_about_field( $post->ID, '_vava_about_features_intro', $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div><div class="vava-about-preview-cards vava-about-preview-feature-grid" data-preview-repeat-output="features"><?php foreach ( $feature_items as $item ) : ?><article class="vava-about-preview-feature-card"><strong><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></strong><div><?php echo vava_richtext_output( (string) ( $item['text'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></article><?php endforeach; ?></div>
	<?php elseif ( 'vision' === $section ) : $vision_items = vava_about_fixed_items( (int) $post->ID, '_vava_about_vision_items', $lang, 3 ); ?>
		<div class="vava-about-preview-vision-shell"><div class="vava-about-preview-heading"><span data-preview-output="vision-eyebrow"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_vision_eyebrow', $lang ) ); ?></span><h3 data-preview-output="vision-title"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_vision_title', $lang ) ); ?></h3><div data-preview-output="vision-intro"><?php echo vava_richtext_output( vava_about_field( $post->ID, '_vava_about_vision_intro', $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div><div class="vava-about-preview-vision-steps" data-preview-repeat-output="vision"><?php foreach ( $vision_items as $item ) : ?><article class="vava-about-preview-vision-step"><strong><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></strong><div><?php echo vava_richtext_output( (string) ( $item['text'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></article><?php endforeach; ?></div><div class="vava-about-preview-dream" data-preview-output="vision-dream"><?php echo vava_richtext_output( vava_about_field( $post->ID, '_vava_about_vision_dream', $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div>
	<?php else : ?>
		<div class="vava-about-preview-invite-panel"><span data-preview-output="invite-eyebrow"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_invite_eyebrow', $lang ) ); ?></span><h3 data-preview-output="invite-title"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_invite_title', $lang ) ); ?></h3><div class="vava-about-preview-invite-description" data-preview-output="invite-description"><?php echo vava_richtext_output( vava_about_field( $post->ID, '_vava_about_invite_description', $lang ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div class="vava-about-preview-btn-row"><b class="is-primary" data-preview-output="invite-button-1"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_invite_button_1_text', $lang ) ); ?></b><b class="is-secondary" data-preview-output="invite-button-2"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_invite_button_2_text', $lang ) ); ?></b><b class="is-coral" data-preview-output="invite-button-3"><?php echo esc_html( vava_about_field( $post->ID, '_vava_about_invite_button_3_text', $lang ) ); ?></b></div></div>
	<?php endif; ?>
	</div></div></div></aside>
	<?php
}

function vava_about_render_settings( WP_Post $post ): void {
	wp_nonce_field( 'vava_about_save', 'vava_about_nonce' );
	$sections_ar = vava_about_sections( 'ar' );
	$sections_en = vava_about_sections( 'en' );
	?>
	<div class="vava-homepage-admin vava-about-admin" data-active-section="hero" data-active-language="ar" data-settings-title-ar="<?php echo esc_attr( vava_about_admin_text( 'meta_box_title', 'ar' ) ); ?>" data-settings-title-en="<?php echo esc_attr( vava_about_admin_text( 'meta_box_title', 'en' ) ); ?>">
		<input type="hidden" name="_vava_admin_active_language" value="ar" data-vava-active-language-input/>
		<?php vava_about_render_page_identity( $post ); ?>
		<div class="vava-admin-toolbar"><div class="vava-section-tabs" role="tablist" aria-label="<?php echo esc_attr( vava_about_admin_text( 'sections_aria', 'ar' ) ); ?>" data-vava-i18n-aria-ar="<?php echo esc_attr( vava_about_admin_text( 'sections_aria', 'ar' ) ); ?>" data-vava-i18n-aria-en="<?php echo esc_attr( vava_about_admin_text( 'sections_aria', 'en' ) ); ?>"><?php foreach ( $sections_ar as $id => $label ) : ?><button aria-selected="<?php echo 'hero' === $id ? 'true' : 'false'; ?>" class="vava-section-tab <?php echo 'hero' === $id ? 'is-active' : ''; ?>" data-section="<?php echo esc_attr( $id ); ?>" type="button" role="tab"><span class="vava-tab-icon" aria-hidden="true"><?php echo vava_about_section_icon( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span<?php echo vava_admin_i18n_attributes( $label, $sections_en[ $id ] ?? $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $label ); ?></span></button><?php endforeach; ?></div><div class="vava-toolbar-actions"><div class="vava-language-switch" role="group" aria-label="<?php echo esc_attr( vava_about_admin_text( 'fields_language', 'ar' ) ); ?>" data-vava-i18n-aria-ar="<?php echo esc_attr( vava_about_admin_text( 'fields_language', 'ar' ) ); ?>" data-vava-i18n-aria-en="<?php echo esc_attr( vava_about_admin_text( 'fields_language', 'en' ) ); ?>"><button class="is-active" data-language="ar" type="button"><span>العربية</span><small>AR</small></button><button data-language="en" type="button"><span>English</span><small>EN</small></button></div><button class="button vava-homepage-update-button" data-vava-submit type="button"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20 12a8 8 0 1 1-2.35-5.65"/><path d="M20 4v6h-6"/></svg><span<?php echo vava_admin_i18n_attributes( vava_about_admin_text( 'update', 'ar' ), vava_about_admin_text( 'update', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_about_admin_text( 'update', 'ar' ) ); ?></span></button></div></div>
		<div class="vava-section-panels">
		<?php foreach ( $sections_ar as $section => $label ) : ?><section class="vava-section-panel <?php echo 'hero' === $section ? 'is-active' : ''; ?>" data-section-panel="<?php echo esc_attr( $section ); ?>">
			<?php foreach ( array( 'ar', 'en' ) as $lang ) : ?><div class="vava-language-pane <?php echo 'ar' === $lang ? 'is-active' : ''; ?>" data-language-pane="<?php echo esc_attr( $lang ); ?>" dir="<?php echo 'en' === $lang ? 'ltr' : 'rtl'; ?>"><div class="vava-editor-workspace"><?php vava_about_render_preview( $post, $section, $lang ); ?><div class="vava-editor-controls"><div class="vava-fields-grid"><?php foreach ( vava_about_scalar_fields( $section, $lang ) as $field ) { vava_about_render_field( $post, $field, $lang ); } ?></div>
			<?php if ( 'features' === $section ) { vava_about_render_fixed_cards( $post, '_vava_about_feature_items', $lang, vava_about_admin_text( 'feature_cards', $lang ), 'features', 4 ); } ?>
			<?php if ( 'vision' === $section ) { vava_about_render_fixed_cards( $post, '_vava_about_vision_items', $lang, vava_about_admin_text( 'vision_cards', $lang ), 'vision', 3 ); } ?>
			<?php if ( 'invite' === $section ) { vava_about_render_link_fields( $post, $lang ); } ?>
			</div></div></div><?php endforeach; ?>
			<?php if ( 'hero' === $section ) : ?><div class="vava-shared-fields"><h3<?php echo vava_admin_i18n_attributes( vava_about_admin_text( 'shared_files', 'ar' ), vava_about_admin_text( 'shared_files', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_about_admin_text( 'shared_files', 'ar' ) ); ?></h3><div class="vava-fields-grid"><?php vava_about_render_media_field( $post, '_vava_about_hero_image_id', vava_about_admin_text( 'hero_image', 'ar' ), vava_about_admin_text( 'hero_image', 'en' ), 'assets/images/about-hero.png' ); ?></div></div><?php elseif ( 'story' === $section ) : ?><div class="vava-shared-fields"><h3<?php echo vava_admin_i18n_attributes( vava_about_admin_text( 'shared_files', 'ar' ), vava_about_admin_text( 'shared_files', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_about_admin_text( 'shared_files', 'ar' ) ); ?></h3><div class="vava-fields-grid"><?php vava_about_render_media_field( $post, '_vava_about_story_image_id', vava_about_admin_text( 'story_image', 'ar' ), vava_about_admin_text( 'story_image', 'en' ), '' ); ?></div></div><?php elseif ( 'why' === $section ) : ?><div class="vava-shared-fields"><h3<?php echo vava_admin_i18n_attributes( vava_about_admin_text( 'shared_files', 'ar' ), vava_about_admin_text( 'shared_files', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_about_admin_text( 'shared_files', 'ar' ) ); ?></h3><div class="vava-fields-grid"><?php vava_about_render_media_field( $post, '_vava_about_why_image_id', vava_about_admin_text( 'why_image', 'ar' ), vava_about_admin_text( 'why_image', 'en' ), 'assets/images/about-why-vava.png' ); ?></div></div><?php endif; ?>
		</section><?php endforeach; ?>
		</div>
	</div>
	<?php
}

function vava_about_sanitize_fixed_items( $raw, array $fallback, int $count ): array {
	$raw      = is_array( $raw ) ? array_values( $raw ) : array();
	$fallback = array_values( $fallback );
	$result   = array();

	for ( $index = 0; $index < $count; $index++ ) {
		$item = isset( $raw[ $index ] ) && is_array( $raw[ $index ] ) ? $raw[ $index ] : ( $fallback[ $index ] ?? array() );
		$result[] = array(
			'title' => sanitize_text_field( (string) ( $item['title'] ?? '' ) ),
			'text'  => wp_kses_post( (string) ( $item['text'] ?? '' ) ),
		);
	}

	return $result;
}

function vava_about_save_meta( int $post_id, WP_Post $post ): void {
	if ( ! isset( $_POST['vava_about_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vava_about_nonce'] ) ), 'vava_about_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $post_id ) || 'page' !== $post->post_type || ! current_user_can( 'edit_page', $post_id ) ) { return; }

	vava_save_bilingual_page_titles( $post_id );

	// Removed legacy hero and repeater fields.
	foreach ( array( 'ar', 'en' ) as $lang ) {
		delete_post_meta( $post_id, vava_about_meta_key( '_vava_about_hero_small', $lang ) );
		delete_post_meta( $post_id, vava_about_meta_key( '_vava_about_hero_phrases', $lang ) );
		delete_post_meta( $post_id, vava_about_meta_key( '_vava_about_story_items', $lang ) );
		delete_post_meta( $post_id, vava_about_meta_key( '_vava_about_why_items', $lang ) );
		for ( $tag = 1; $tag <= 3; $tag++ ) {
			delete_post_meta( $post_id, vava_about_meta_key( '_vava_about_hero_tag_' . $tag, $lang ) );
		}
	}

	foreach ( array_keys( vava_about_sections( 'ar' ) ) as $section ) {
		foreach ( vava_about_scalar_fields( $section, 'ar' ) as $field ) {
			$base = $field[0];
			$type = $field[2];
			foreach ( array( 'ar', 'en' ) as $lang ) {
				$key = vava_about_meta_key( $base, $lang );
				if ( array_key_exists( $key, $_POST ) ) {
					$raw = wp_unslash( $_POST[ $key ] );
					update_post_meta( $post_id, $key, 'richtext' === $type ? wp_kses_post( (string) $raw ) : ( 'textarea' === $type ? sanitize_textarea_field( (string) $raw ) : sanitize_text_field( (string) $raw ) ) );
				}
			}
		}
	}

	foreach ( array( '_vava_about_hero_image_id', '_vava_about_story_image_id', '_vava_about_why_image_id' ) as $key ) {
		if ( array_key_exists( $key, $_POST ) ) { update_post_meta( $post_id, $key, absint( $_POST[ $key ] ) ); }
	}

	$fixed_groups = array(
		'_vava_about_feature_items' => 4,
		'_vava_about_vision_items'  => 3,
	);
	foreach ( $fixed_groups as $base => $count ) {
		foreach ( array( 'ar', 'en' ) as $lang ) {
			$key = vava_about_meta_key( $base, $lang );
			if ( isset( $_POST[ $key . '_present' ] ) ) {
				$raw      = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : array();
				$defaults = vava_about_defaults( $lang );
				$fallback = isset( $defaults[ $base ] ) && is_array( $defaults[ $base ] ) ? $defaults[ $base ] : array();
				update_post_meta( $post_id, $key, vava_about_sanitize_fixed_items( $raw, $fallback, $count ) );
			}
		}
	}

	$active_language = isset( $_POST['_vava_admin_active_language'] ) ? vava_normalize_language( sanitize_key( wp_unslash( $_POST['_vava_admin_active_language'] ) ) ) : 'ar';
	for ( $i = 1; $i <= 3; $i++ ) {
		$base = '_vava_about_invite_button_' . $i . '_url';
		foreach ( array( 'ar', 'en' ) as $lang ) {
			$text_key = vava_about_meta_key( '_vava_about_invite_button_' . $i . '_text', $lang );
			if ( array_key_exists( $text_key, $_POST ) ) {
				update_post_meta( $post_id, $text_key, sanitize_text_field( (string) wp_unslash( $_POST[ $text_key ] ) ) );
			}
		}

		$ar_key        = vava_about_meta_key( $base, 'ar' );
		$en_key        = vava_about_meta_key( $base, 'en' );
		$stored_values = vava_about_shared_link_values( $post_id, $i, 'ar' );
		$ar_type       = isset( $_POST[ $ar_key . '_type' ] ) && 'page' === sanitize_key( wp_unslash( $_POST[ $ar_key . '_type' ] ) ) ? 'page' : 'manual';
		$en_type       = isset( $_POST[ $en_key . '_type' ] ) && 'page' === sanitize_key( wp_unslash( $_POST[ $en_key . '_type' ] ) ) ? 'page' : 'manual';
		$ar_page       = isset( $_POST[ $ar_key . '_page_id' ] ) ? absint( $_POST[ $ar_key . '_page_id' ] ) : absint( $stored_values['page_id'] );
		$en_page       = isset( $_POST[ $en_key . '_page_id' ] ) ? absint( $_POST[ $en_key . '_page_id' ] ) : absint( $stored_values['page_id'] );
		$ar_manual     = isset( $_POST[ $ar_key . '_manual' ] ) ? esc_url_raw( (string) wp_unslash( $_POST[ $ar_key . '_manual' ] ) ) : (string) $stored_values['manual'];
		$en_manual     = isset( $_POST[ $en_key . '_manual' ] ) ? esc_url_raw( (string) wp_unslash( $_POST[ $en_key . '_manual' ] ) ) : (string) $stored_values['manual'];
		$type          = (string) vava_reconcile_shared_setting( $ar_type, $en_type, (string) $stored_values['type'], $active_language );
		$page_id       = absint( vava_reconcile_shared_setting( $ar_page, $en_page, absint( $stored_values['page_id'] ), $active_language ) );
		$manual        = (string) vava_reconcile_shared_setting( $ar_manual, $en_manual, (string) $stored_values['manual'], $active_language );

		foreach ( array( 'ar', 'en' ) as $lang ) {
			$key      = vava_about_meta_key( $base, $lang );
			$resolved = 'page' === $type && $page_id ? vava_localized_page_url( $page_id, $lang ) : $manual;
			update_post_meta( $post_id, $key . '_type', $type );
			update_post_meta( $post_id, $key . '_page_id', $page_id );
			update_post_meta( $post_id, $key . '_manual', $manual );
			update_post_meta( $post_id, $key, esc_url_raw( (string) $resolved ) );
		}
	}
}
add_action( 'save_post_page', 'vava_about_save_meta', 10, 2 );


/**
 * Convert the previous repeaters into single text fields once, without losing content.
 */
function vava_about_migrate_text_model(): void {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'vava_about_text_model_v1' ) ) {
		return;
	}

	$page_id = absint( get_option( 'vava_about_page_migrated_v2' ) );
	if ( ! $page_id ) {
		$page = get_page_by_path( 'about-vava', OBJECT, 'page' );
		$page_id = $page instanceof WP_Post ? (int) $page->ID : 0;
	}
	if ( ! $page_id ) {
		return;
	}

	foreach ( array( 'ar', 'en' ) as $lang ) {
		$story_key = vava_about_meta_key( '_vava_about_story_content', $lang );
		$why_key   = vava_about_meta_key( '_vava_about_why_content', $lang );
		if ( ! metadata_exists( 'post', $page_id, $story_key ) ) {
			update_post_meta( $page_id, $story_key, vava_about_story_content( $page_id, $lang ) );
		}
		if ( ! metadata_exists( 'post', $page_id, $why_key ) ) {
			update_post_meta( $page_id, $why_key, vava_about_why_content( $page_id, $lang ) );
		}

		delete_post_meta( $page_id, vava_about_meta_key( '_vava_about_story_items', $lang ) );
		delete_post_meta( $page_id, vava_about_meta_key( '_vava_about_why_items', $lang ) );
		delete_post_meta( $page_id, vava_about_meta_key( '_vava_about_hero_phrases', $lang ) );
		for ( $tag = 1; $tag <= 3; $tag++ ) {
			delete_post_meta( $page_id, vava_about_meta_key( '_vava_about_hero_tag_' . $tag, $lang ) );
		}
	}

	update_option( 'vava_about_text_model_v1', $page_id, false );
}
add_action( 'admin_init', 'vava_about_migrate_text_model', 30 );

function vava_about_button_url( int $post_id, int $button, string $lang ): string {
	$values = vava_about_shared_link_values( $post_id, $button, $lang );
	$url    = (string) ( $values['url'] ?? '' );
	return function_exists( 'vava_normalize_internal_url' ) ? vava_normalize_internal_url( $url ) : $url;
}

function vava_about_use_block_editor( bool $use_block_editor, WP_Post $post ): bool {
	return vava_about_is_page( (int) $post->ID ) ? false : $use_block_editor;
}
add_filter( 'use_block_editor_for_post', 'vava_about_use_block_editor', 10, 2 );

function vava_about_admin_body_class( string $classes ): string {
	global $post;
	$post_id = $post instanceof WP_Post ? (int) $post->ID : ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 );
	if ( $post_id && vava_about_is_page( $post_id ) ) { $classes .= ' vava-homepage-classic vava-about-classic'; }
	return $classes;
}
add_filter( 'admin_body_class', 'vava_about_admin_body_class' );

function vava_about_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) { return; }
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	if ( ! $post_id || ! vava_about_is_page( $post_id ) ) { return; }
	wp_enqueue_media();
	wp_enqueue_style( 'vava-homepage-admin', get_theme_file_uri( 'assets/css/admin-homepage.css' ), array(), vava_asset_version( 'assets/css/admin-homepage.css' ) );
	wp_enqueue_style( 'vava-about-admin', get_theme_file_uri( 'assets/css/admin-about.css' ), array( 'vava-homepage-admin' ), vava_asset_version( 'assets/css/admin-about.css' ) );
	wp_enqueue_script( 'vava-about-admin', get_theme_file_uri( 'assets/js/admin-about.js' ), array( 'jquery', 'jquery-ui-sortable' ), vava_asset_version( 'assets/js/admin-about.js' ), true );
	wp_localize_script( 'vava-about-admin', 'vavaAboutAdmin', array(
		'uploadUrl'   => admin_url( 'async-upload.php' ),
		'uploadNonce' => wp_create_nonce( 'media-form' ),
		'postId'      => $post_id,
		'maxImageMb'  => 20,
	) );
}
add_action( 'admin_enqueue_scripts', 'vava_about_admin_assets' );


function vava_about_document_title( array $parts ): array {
	if ( is_page_template( vava_about_template_slug() ) ) {
		$parts['title'] = vava_bilingual_page_title( get_queried_object_id(), vava_current_language() );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'vava_about_document_title' );

function vava_about_assign_or_create_page(): void {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'vava_about_page_migrated_v2' ) ) { return; }

	$page = get_page_by_path( 'about-vava', OBJECT, 'page' );
	if ( ! $page ) {
		$candidates = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);
		foreach ( $candidates as $candidate ) {
			if ( $candidate instanceof WP_Post && vava_about_is_page( (int) $candidate->ID ) ) {
				$page = $candidate;
				break;
			}
		}
	}

	if ( ! $page ) {
		$page_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'عن VAVA',
				'post_name'   => 'about-vava',
			),
			true
		);
		if ( ! is_wp_error( $page_id ) ) { $page = get_post( $page_id ); }
	}

	if ( $page instanceof WP_Post ) {
		if ( 'about-vava' !== $page->post_name ) {
			$updated_id = wp_update_post( array( 'ID' => $page->ID, 'post_name' => 'about-vava' ), true );
			if ( ! is_wp_error( $updated_id ) ) { $page = get_post( $page->ID ); }
		}
		update_post_meta( $page->ID, '_wp_page_template', vava_about_template_slug() );
		update_option( 'vava_about_page_migrated_v2', (int) $page->ID, false );
	}
}
add_action( 'admin_init', 'vava_about_assign_or_create_page' );
