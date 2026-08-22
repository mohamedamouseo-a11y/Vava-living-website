<?php
/**
 * Managed VAVA digital product catalogue and inline bilingual product reader.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product page template slug.
 */
function vava_digital_product_template_slug(): string {
	return 'page-templates/digital-product-vava.php';
}

/**
 * Canonical digital product catalogue.
 *
 * The supplied client descriptions are kept as the source of truth for the
 * marketing copy. Functional values (slug, price, cover and page count) are
 * shared between both languages.
 */
function vava_digital_products_catalog(): array {
	return array(
		'doshas-at-a-glance' => array(
			'uid'         => 'doshas-at-a-glance',
			'slug'        => 'doshas-at-a-glance',
			'price'       => '39',
			'cover_asset' => 'assets/images/digital-products/doshas-at-a-glance.webp',
			'pages'       => '4',
			'ar' => array(
				'title'       => 'الدوشات في لمحة',
				'category'    => 'مرجع سريع',
				'card_description' => 'مرجع مختصر يجمع أهم المعلومات الأساسية عن كل دوشا للرجوع إليها بسهولة.',
				'question'    => 'ماذا يناسب كل دوشا بسرعة؟',
				'description' => 'مرجع عملي مختصر يجمع أهم المعلومات الأساسية عن كل دوشا في مكان واحد، ليسهل الرجوع إليها كلما احتجت. يساعدك على تذكر السمات، وعلامات الاتزان والاختلال، والأغذية، والمذاقات، والأوقات، والمواسم، والزيوت، والنشاطات الداعمة لكل دوشا، في تصميم واضح وسهل الاستخدام.',
				'inside'      => array(
					'ملخص للدوشات الثلاث.',
					'السمات الشخصية في الاتزان والاختلال.',
					'العلامات الجسدية والسلوكية.',
					'الأغذية والمذاقات المناسبة لكل دوشا.',
					'المواسم وأوقات اليوم.',
					'الزيوت والنشاطات الداعمة للتوازن.',
				),
				'ideal'       => array(
					'تبحث عن مرجع سريع وسهل الرجوع إليه.',
					'تعرف نوعك الدوشي وتريد تذكيرًا عمليًا بما يناسبه.',
					'تفضل المعلومات المختصرة والواضحة.',
				),
			),
			'en' => array(
				'title'       => 'Doshas at a Glance',
				'category'    => 'Quick Reference',
				'card_description' => 'A concise reference gathering the essential information about each dosha in one easy place.',
				'question'    => 'What supports each dosha at a glance?',
				'description' => 'A concise Ayurvedic reference that brings together the essential information about each dosha in one place, making it easy to revisit whenever you need it. Quickly review personality traits, signs of balance and imbalance, supportive foods, tastes, daily and seasonal rhythms, recommended oils, and lifestyle practices in a clear, easy-to-use format.',
				'inside'      => array(
					'A quick overview of the three doshas.',
					'Personality traits in both balance and imbalance.',
					'Common physical and behavioral signs.',
					'Supportive foods and tastes for each dosha.',
					'Seasonal and daily rhythms.',
					'Recommended oils and lifestyle practices.',
				),
				'ideal'       => array(
					'Want a quick and practical Ayurvedic reference.',
					'Already know your dosha and would like an easy reminder of what supports it.',
					'Prefer concise, easy-to-use information you can return to anytime.',
				),
			),
		),
		'understanding-the-doshas' => array(
			'uid'         => 'understanding-the-doshas',
			'slug'        => 'understanding-the-doshas',
			'price'       => '69',
			'cover_asset' => 'assets/images/digital-products/understanding-the-doshas.webp',
			'pages'       => '21',
			'ar' => array(
				'title'       => 'الدوشات عن قرب',
				'category'    => 'دليل عملي',
				'card_description' => 'دليل عملي لفهم الدوشات وصفاتها وعلاقتها بالجسد والعقل والحياة اليومية.',
				'question'    => 'لماذا تختلف الدوشات وكيف تعمل؟',
				'description' => 'دليل يساعدك على فهم الدوشات بصورة أعمق، وكيف تنعكس صفاتها في الجسد والعقل والحياة اليومية. ستتعرف على العناصر التي تكوّن كل دوشا، وعلاقتها بالحواس، والمواسم، وأوقات اليوم، مع إرشادات عملية تساعدك على فهم الاختلال ودعم العودة إلى التوازن بوعي.',
				'inside'      => array(
					'شرح العناصر التي تكوّن كل دوشا.',
					'صفات كل دوشا ودورها في الجسم والعقل.',
					'العلاقة بين الدوشات والحواس.',
					'الدوشات عبر المواسم وأوقات اليوم.',
					'طرق عملية لدعم وتهدئة كل دوشا.',
					'شرح أوسع لعلامات الاتزان والاختلال.',
				),
				'ideal'       => array(
					'ترغب في فهم الدوشات بعمق.',
					'تريد تطبيق الأيورفيدا بطريقة عملية في حياتك اليومية.',
					'تبحث عن فهم الإشارات قبل البدء بتغيير أسلوب حياتك.',
				),
			),
			'en' => array(
				'title'       => 'Understanding the Doshas',
				'category'    => 'Practical Guide',
				'card_description' => 'A practical guide to the doshas, their qualities, and their influence on body, mind, and daily life.',
				'question'    => 'Why are the doshas different, and how do they work?',
				'description' => 'A practical guide designed to help you develop a deeper understanding of the three doshas and how they influence your body, mind, and everyday life. Explore the elements that shape each dosha, their connection to the senses, seasons, and daily rhythms, along with practical guidance to help you recognize imbalance and support a more balanced way of living.',
				'inside'      => array(
					'An introduction to the elements that form each dosha.',
					'The qualities and roles of each dosha in the body and mind.',
					'The relationship between the doshas and the senses.',
					'How the doshas relate to the seasons and the daily cycle.',
					'Practical ways to support and balance each dosha.',
					'A deeper understanding of balanced and imbalanced doshic patterns.',
				),
				'ideal'       => array(
					'Want to understand the doshas beyond the basics.',
					'Would like to apply Ayurvedic principles in everyday life.',
					'Prefer understanding your body\'s signals before making lifestyle changes.',
				),
			),
		),
		'listening-to-my-body' => array(
			'uid'         => 'listening-to-my-body',
			'slug'        => 'listening-to-my-body',
			'price'       => '39',
			'cover_asset' => 'assets/images/digital-products/listening-to-my-body.webp',
			'pages'       => '8',
			'ar' => array(
				'title'       => 'جسدي عن قرب',
				'category'    => 'دفتر تأمل وملاحظة',
				'card_description' => 'دفتر تأمل وملاحظة لمدة 7 أيام يساعدك على الإصغاء إلى إشارات جسدك.',
				'question'    => 'ماذا يحاول جسدي أن يخبرني؟',
				'description' => 'رحلة هادئة تمتد لسبعة أيام، تدعوك إلى التوقف والإصغاء إلى لغة جسدك. من خلال أسئلة وتأملات وتمارين ملاحظة بسيطة، تبدأ باكتشاف الأنماط التي تتكرر في حياتك، وتقترب من فهم ما يحاول جسدك أن يخبرك به، خطوة بعد أخرى.',
				'inside'      => array(
					'مقدمة تمهد لرحلة الإصغاء إلى الجسد.',
					'صفحات يومية لتدوين الملاحظات لمدة 7 أيام.',
					'أسئلة تساعدك على ربط الإشارات بعاداتك اليومية.',
					'مراجعة وتأمل بعد أسبوع من الملاحظة.',
					'صفحة خاصة لكتابة رسالة إلى جسدك.',
					'خاتمة تشجعك على مواصلة الإصغاء برفق ووعي.',
				),
				'ideal'       => array(
					'ترغب في التوقف والإنصات إلى جسدك بعيدًا عن الاستعجال.',
					'تلاحظ إشارات أو أعراضًا متكررة وتود فهمها بصورة أعمق.',
					'تستمتع بالكتابة والتأمل واكتشاف الأنماط التي تتكرر في حياتك.',
				),
			),
			'en' => array(
				'title'       => 'Listening to My Body',
				'category'    => 'Reflection Journal',
				'card_description' => 'A seven-day reflection journal that helps you slow down and listen to your body.',
				'question'    => 'What is my body trying to tell me?',
				'description' => 'A gentle seven-day journey that invites you to slow down and listen to your body\'s language. Through guided reflections, simple observation exercises, and daily journaling, you\'ll begin to notice recurring patterns and develop a deeper awareness of what your body may be trying to communicate.',
				'inside'      => array(
					'A gentle introduction to body awareness.',
					'Seven days of guided observation pages.',
					'Reflection prompts connecting body signals with daily habits.',
					'A weekly review after seven days of journaling.',
					'A dedicated page to write a letter to your body.',
					'A thoughtful closing to encourage continued self-observation.',
				),
				'ideal'       => array(
					'Want to slow down and reconnect with your body.',
					'Notice recurring symptoms and would like to understand them more deeply.',
					'Enjoy journaling, reflection, and mindful self-discovery.',
				),
			),
		),
		'ayurvedic-food-reference' => array(
			'uid'         => 'ayurvedic-food-reference',
			'slug'        => 'ayurvedic-food-reference',
			'price'       => '39',
			'cover_asset' => 'assets/images/digital-products/ayurvedic-food-reference.webp',
			'pages'       => '',
			'ar' => array(
				'title'       => 'قائمة الأغذية المختصرة',
				'category'    => 'مرجع غذائي',
				'card_description' => 'مرجع غذائي سريع يجمع الأغذية والمشروبات والمذاقات المناسبة لكل دوشا.',
				'question'    => 'ماذا أختار عندما أقف أمام الطعام؟',
				'description' => 'اختيار الطعام لا يحتاج إلى أن يكون معقدًا. يجمع هذا المرجع أبرز الأغذية والمشروبات المناسبة لكل دوشا في قوائم واضحة وسهلة الرجوع، لتساعدك على اتخاذ خيارات غذائية أكثر وعيًا وفق حالتك واحتياجاتك اليومية، دون الحاجة للبحث في مصادر متعددة.',
				'inside'      => array(
					'الأغذية المناسبة لكل دوشا حسب الفئات الغذائية.',
					'الأغذية التي يفضل الاعتدال فيها أو تجنبها.',
					'المذاقات الداعمة لكل دوشا.',
					'المشروبات والأعشاب المناسبة.',
					'أوقات ومواسم كل دوشا.',
					'إرشادات غذائية مختصرة تساعدك على الاختيار بسهولة.',
				),
				'ideal'       => array(
					'ترغب في معرفة ما يناسب حالتك عند اختيار الطعام.',
					'تبحث عن مرجع غذائي سريع وسهل الاستخدام.',
					'تريد تطبيق مبادئ الأيورفيدا في وجباتك اليومية بطريقة عملية.',
				),
			),
			'en' => array(
				'title'       => 'Ayurvedic Food Reference',
				'category'    => 'Nutrition Reference',
				'card_description' => 'A quick food reference for supportive foods, drinks, herbs, and tastes for each dosha.',
				'question'    => 'What should I choose when deciding what to eat?',
				'description' => 'Making food choices doesn\'t have to be complicated. This practical reference brings together supportive foods, drinks, herbs, and tastes for each dosha in one easy-to-use guide, helping you make everyday food choices with greater confidence.',
				'inside'      => array(
					'Supportive foods for each dosha across different food groups.',
					'Foods to enjoy in moderation or limit.',
					'Recommended tastes for each dosha.',
					'Herbs and beverages that support balance.',
					'Seasonal and daily rhythm references.',
					'Practical nutrition tips for everyday use.',
				),
				'ideal'       => array(
					'Want a simple Ayurvedic food reference.',
					'Would like more confidence when choosing meals.',
					'Prefer practical guidance over lengthy explanations.',
				),
			),
		),
		'journey-back-to-harmony' => array(
			'uid'         => 'journey-back-to-harmony',
			'slug'        => 'journey-back-to-harmony',
			'price'       => '119',
			'cover_asset' => 'assets/images/digital-products/journey-back-to-harmony.webp',
			'pages'       => '53',
			'ar' => array(
				'title'       => 'رحلة العودة إلى التناغم',
				'category'    => 'دليل تطبيقي',
				'card_description' => 'دليل تطبيقي يحول مبادئ الأيورفيدا إلى ممارسة واعية في الحياة اليومية.',
				'question'    => 'كيف أعيش الأيورفيدا كأسلوب حياة؟',
				'description' => 'رحلة عملية تساعدك على رؤية الحياة من خلال عدسة الأيورفيدا، وتطبيق مبادئها في واقعك اليومي خطوة بخطوة. ينتقل بك الدليل من فهم طبيعتك، إلى ملاحظة الإيقاعات التي تؤثر فيك، ثم اختيار استجابات تناسبك، ليصبح الوعي ممارسة تعيشها، لا مجرد معرفة تقرؤها.',
				'inside'      => array(
					'مدخل مبسط لفلسفة الأيورفيدا.',
					'فهم العناصر الخمسة والدوشات.',
					'التعرف على طبيعتك الأساسية وحالتك الحالية.',
					'تأثير الوقت والمواسم وإيقاع الحياة.',
					'تمارين للتأمل والملاحظة.',
					'خطوات عملية للاستجابة لما يحتاجه جسدك.',
					'اختبار البراكروتي والفيكروتي للاستخدام الشخصي.',
				),
				'ideal'       => array(
					'تبحث عن نقطة بداية واضحة لفهم الأيورفيدا.',
					'ترغب في تحويل المعرفة إلى ممارسة يومية.',
					'تحب التعلم من خلال التأمل والتجربة والتطبيق.',
				),
			),
			'en' => array(
				'title'       => 'The Journey Back to Harmony',
				'category'    => 'Practical Workbook',
				'card_description' => 'A practical workbook for turning Ayurvedic principles into conscious everyday practice.',
				'question'    => 'How can I live Ayurveda as a way of life?',
				'description' => 'A guided journey that introduces Ayurveda as a way of understanding yourself and the rhythms of life. Through reflection, observation, and practical exercises, this workbook helps you move beyond learning concepts to gradually applying them in your everyday life.',
				'inside'      => array(
					'An accessible introduction to Ayurvedic philosophy.',
					'The five elements and the three doshas.',
					'Understanding your constitution and current state.',
					'Daily and seasonal rhythms.',
					'Reflection exercises and observation prompts.',
					'Practical steps for applying what you learn.',
					'Prakriti and Vikruti self-assessment tools.',
				),
				'ideal'       => array(
					'Are looking for a practical introduction to Ayurveda.',
					'Want to turn knowledge into everyday practice.',
					'Enjoy learning through reflection and guided exercises.',
				),
			),
		),
		'journey-back-to-balance' => array(
			'uid'         => 'journey-back-to-balance',
			'slug'        => 'journey-back-to-balance',
			'price'       => '89',
			'cover_asset' => 'assets/images/digital-products/journey-back-to-balance.webp',
			'pages'       => '46',
			'ar' => array(
				'title'       => 'رحلة العودة إلى التوازن',
				'category'    => 'دليل غذائي',
				'card_description' => 'دليل غذائي عملي لفهم استجابة جسدك للطعام واختيار ما يناسب حالتك.',
				'question'    => 'كيف أبني علاقة واعية مع الغذاء؟',
				'description' => 'دليل عملي يساعدك على بناء علاقة أكثر وعيًا مع الغذاء من منظور أيورفيدي. بدلًا من التركيز على ماذا تأكل فقط، يعرّفك على العوامل التي تؤثر في استجابة جسدك للطعام، لتتعلم كيف تختار ما يناسب طبيعتك وحالتك الحالية بثقة ووضوح.',
				'inside'      => array(
					'مقدمة لفهم التغذية في الأيورفيدا.',
					'اختبار يساعدك على التعرف على حالتك الحالية.',
					'تأثير الوقت والمواسم والبيئة على الغذاء.',
					'المذاقات الستة وكيفية استخدامها.',
					'مبادئ الهضم (Agni) ودوره في التوازن.',
					'قوائم غذائية مفصلة لكل دوشا.',
					'أمثلة عملية لتطبيق التغذية الأيورفيدية في يومك.',
				),
				'ideal'       => array(
					'تريد فهم الغذاء بما يتجاوز السعرات والقوائم التقليدية.',
					'ترغب في اختيار الطعام بما يتوافق مع طبيعتك.',
					'تبحث عن مرجع عملي يساعدك في بناء عادات غذائية أكثر وعيًا.',
				),
			),
			'en' => array(
				'title'       => 'The Journey Back to Balance',
				'category'    => 'Nutrition Guide',
				'card_description' => 'A practical nutrition guide to understanding your body’s response to food and choosing with awareness.',
				'question'    => 'How can I build a more mindful relationship with food?',
				'description' => 'A practical guide to understanding food through the Ayurvedic perspective. Rather than focusing only on what you eat, it explores the many factors that influence how your body responds to food, helping you make choices that reflect both your constitution and your current needs.',
				'inside'      => array(
					'An introduction to Ayurvedic nutrition.',
					'A self-assessment to identify your current doshic state.',
					'The influence of time, seasons, and environment.',
					'The six tastes and how to use them.',
					'Understanding Agni (digestive fire).',
					'Detailed food recommendations for each dosha.',
					'Practical examples for applying Ayurvedic nutrition.',
				),
				'ideal'       => array(
					'Want to understand food beyond calories and meal plans.',
					'Would like to choose foods that suit your unique constitution.',
					'Are looking for practical guidance to build more mindful eating habits.',
				),
			),
		),
	);
}

/**
 * Resolve one catalogue item and merge its localized fields.
 */
function vava_digital_product_data( string $uid, string $lang = 'ar' ): array {
	$catalog = vava_digital_products_catalog();
	$uid     = sanitize_key( $uid );
	$lang    = 'en' === $lang ? 'en' : 'ar';
	if ( ! isset( $catalog[ $uid ] ) ) {
		return array();
	}
	$item = $catalog[ $uid ];
	$data = array_merge( $item, (array) ( $item[ $lang ] ?? array() ) );
	$data['cover_url'] = function_exists( 'vava_digital_products_cover_url' ) ? vava_digital_products_cover_url( $uid ) : '';
	if ( '' === $data['cover_url'] ) { $data['cover_url'] = ! empty( $data['cover_asset'] ) ? vava_asset_uri( (string) $data['cover_asset'] ) : ''; }
	return $data;
}

/**
 * Return the catalogue uid assigned to a product page.
 */
function vava_digital_product_uid_for_page( int $post_id ): string {
	$uid = sanitize_key( (string) get_post_meta( $post_id, '_vava_digital_product_uid', true ) );
	if ( $uid ) {
		return $uid;
	}
	$post = get_post( $post_id );
	if ( $post instanceof WP_Post ) {
		$slug = sanitize_key( (string) $post->post_name );
		if ( isset( vava_digital_products_catalog()[ $slug ] ) ) {
			return $slug;
		}
	}
	return '';
}

/**
 * Detect a managed digital product page.
 */
function vava_digital_product_is_page( int $post_id ): bool {
	if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
		return false;
	}
	$template = (string) get_post_meta( $post_id, '_wp_page_template', true );
	return vava_digital_product_template_slug() === $template || '' !== vava_digital_product_uid_for_page( $post_id );
}

/**
 * Provide translated WordPress page titles to the shared title system.
 */
function vava_digital_product_title_defaults( array $defaults, int $post_id ): array {
	if ( ! vava_digital_product_is_page( $post_id ) ) {
		return $defaults;
	}
	$uid = vava_digital_product_uid_for_page( $post_id );
	$ar  = vava_digital_product_data( $uid, 'ar' );
	$en  = vava_digital_product_data( $uid, 'en' );
	if ( $ar ) {
		$defaults['ar'] = (string) ( $ar['title'] ?? $defaults['ar'] );
	}
	if ( $en ) {
		$defaults['en'] = (string) ( $en['title'] ?? $defaults['en'] );
	}
	return $defaults;
}
add_filter( 'vava_page_title_defaults', 'vava_digital_product_title_defaults', 12, 2 );

/**
 * Build the shared card rows used by the Selections page.
 */
function vava_digital_products_card_shared_defaults(): array {
	$rows = array();
	foreach ( vava_digital_products_catalog() as $uid => $item ) {
		$rows[] = array(
			'uid'            => $uid,
			'image_id'       => 0,
			'fallback_asset' => (string) $item['cover_asset'],
			'price'          => (string) $item['price'],
			'enabled'        => 1,
		);
	}
	return $rows;
}

/**
 * Build localized card text used by the Selections page and its editor.
 */
function vava_digital_products_card_text_defaults( string $lang = 'ar' ): array {
	$lang = 'en' === $lang ? 'en' : 'ar';
	$rows = array();
	foreach ( vava_digital_products_catalog() as $uid => $item ) {
		$local = (array) ( $item[ $lang ] ?? array() );
		$rows[] = array(
				'uid'              => $uid,
				'title'            => (string) ( $local['title'] ?? '' ),
				'description'      => (string) ( $local['card_description'] ?? $local['description'] ?? '' ),
				'full_description' => (string) ( $local['description'] ?? '' ),
				'currency'         => 'en' === $lang ? 'SAR' : 'ر.س',
				'button_text'      => 'en' === $lang ? 'View Details' : 'عرض التفاصيل',
		);
	}
	return $rows;
}

/**
 * Remove the product pages created by the retired page-per-product approach.
 * Only pages carrying the code-managed product marker or template are removed.
 */
function vava_digital_products_remove_generated_pages(): void {
	$statuses = array( 'publish', 'draft', 'private', 'pending', 'future', 'trash' );
	$ids      = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => $statuses,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_vava_digital_product_uid',
		)
	);
	$template_ids = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => $statuses,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_wp_page_template',
			'meta_value'     => vava_digital_product_template_slug(),
		)
	);
	$ids = array_values( array_unique( array_filter( array_map( 'absint', array_merge( $ids, $template_ids ) ) ) ) );
	foreach ( $ids as $page_id ) {
		wp_delete_post( $page_id, true );
	}
}

/**
 * Replace the old linked-page catalogue with the six inline-reader products.
 * Existing hero and tangible-selection settings remain untouched.
 */
function vava_digital_products_migrate_inline_reader(): void {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'vava_digital_products_inline_reader_migrated_v3' ) ) {
		return;
	}

	vava_digital_products_remove_generated_pages();

	$selections_page = get_page_by_path( 'vava-selections', OBJECT, 'page' );
	if ( ! $selections_page instanceof WP_Post ) {
		$stored_id       = absint( get_option( 'vava_selections_page_migrated_v1' ) );
		$selections_page = $stored_id ? get_post( $stored_id ) : null;
	}
	if ( $selections_page instanceof WP_Post ) {
		$page_id                              = (int) $selections_page->ID;
		$shared                               = vava_selections_shared_data( $page_id );
		$shared['products']['digital']        = vava_digital_products_card_shared_defaults();
		update_post_meta( $page_id, '_vava_selections_shared', $shared );

		foreach ( array( 'ar', 'en' ) as $lang ) {
			$text            = vava_selections_products_text_data( $page_id, $lang );
			$text['digital'] = vava_digital_products_card_text_defaults( $lang );
			update_post_meta( $page_id, vava_selections_products_meta_key( $lang ), $text );
		}
	}

	update_option( 'vava_digital_products_inline_reader_migrated_v3', time(), false );
}
add_action( 'admin_init', 'vava_digital_products_migrate_inline_reader', 12 );

/**
 * Labels used by the inline product reader.
 */
function vava_digital_product_reader_labels( string $lang = 'ar' ): array {
	if ( 'en' === $lang ) {
		return array(
			'question'   => 'The question this guide answers',
			'inside'     => 'What you will find inside',
			'ideal'      => 'Ideal for you if you',
			'details'    => 'Product details',
			'format'     => 'Format',
			'language'   => 'Content language',
			'pages'      => 'Pages',
			'usage'      => 'License',
			'language_value' => 'Arabic',
			'usage_value'    => 'Personal use',
			'previous'   => 'Previous product',
			'next'       => 'Next product',
			'close'      => 'Close product',
			'rights'     => 'This digital guide is intended for personal use only. It may not be copied, republished, modified, distributed, or resold without prior written permission from VAVA Living.',
			'disclaimer' => 'This content is educational and is not a substitute for an individual assessment or professional consultation when needed.',
		);
	}
	return array(
		'question'   => 'السؤال الذي يجيب عنه الدليل',
		'inside'     => 'ماذا ستجد داخل الدليل؟',
		'ideal'      => 'مناسب لك إذا كنت',
		'details'    => 'تفاصيل المنتج',
		'format'     => 'الصيغة',
		'language'   => 'لغة المحتوى',
		'pages'      => 'عدد الصفحات',
		'usage'      => 'نوع الاستخدام',
		'language_value' => 'العربية',
		'usage_value'    => 'استخدام شخصي',
		'previous'   => 'المنتج السابق',
		'next'       => 'المنتج التالي',
		'close'      => 'إغلاق المنتج',
		'rights'     => 'هذا الدليل الرقمي معد للاستخدام الشخصي فقط، ولا يجوز نسخه أو إعادة نشره أو تعديله أو توزيعه أو إعادة بيعه دون الحصول على إذن كتابي مسبق من VAVA Living.',
		'disclaimer' => 'تم إعداد هذا المحتوى لأغراض تثقيفية، ولا يعد بديلًا عن التقييم أو الاستشارة الفردية عند الحاجة.',
	);
}

/**
 * Merge the catalogue detail with editable card values from the Selections page.
 */
function vava_digital_product_reader_data( array $card, string $lang = 'ar' ): array {
	$uid  = sanitize_key( (string) ( $card['uid'] ?? '' ) );
	$data = vava_digital_product_data( $uid, $lang );
	if ( ! $data ) {
		$data = array(
			'uid'          => $uid,
			'title'        => (string) ( $card['title'] ?? '' ),
			'category'     => '',
			'question'     => '',
			'description'  => (string) ( $card['description'] ?? '' ),
			'inside'       => array(),
			'ideal'        => array(),
			'pages'        => '',
			'cover_asset'  => (string) ( $card['fallback_asset'] ?? '' ),
			'group'        => (string) ( $card['group'] ?? 'digital' ),
		);
	}
	if ( '' !== trim( (string) ( $card['title'] ?? '' ) ) ) {
		$data['title'] = (string) $card['title'];
	}
	$full_description = trim( (string) ( $card['full_description'] ?? '' ) );
	if ( '' !== $full_description ) {
		$data['description'] = $full_description;
	}
	$data['group']      = (string) ( $card['group'] ?? $data['group'] ?? 'digital' );
	$data['price']      = (string) ( $card['price'] ?? $data['price'] ?? '' );
	$data['currency']   = (string) ( $card['currency'] ?? ( 'en' === $lang ? 'SAR' : 'ر.س' ) );
	$pdf_cover = function_exists( 'vava_digital_products_cover_url' ) ? vava_digital_products_cover_url( $uid ) : '';
	$data['cover_url'] = $pdf_cover ?: vava_selections_image_url( absint( $card['image_id'] ?? 0 ), (string) ( $card['fallback_asset'] ?? $data['cover_asset'] ?? '' ) );
	return $data;
}

/**
 * Render one previous/next destination in the olive product toolbar.
 */
function vava_digital_product_render_reader_destination( array $card, string $direction, string $lang ): void {
	$uid = sanitize_key( (string) ( $card['uid'] ?? '' ) );
	if ( '' === $uid ) {
		?><span class="vava-product-reader-destination is-empty" aria-hidden="true"></span><?php
		return;
	}
	$data  = vava_digital_product_reader_data( $card, $lang );
	$label = vava_digital_product_reader_labels( $lang )[ 'previous' === $direction ? 'previous' : 'next' ];
	$icon  = 'previous' === $direction ? '‹' : '›';
	?>
	<button class="vava-product-reader-destination is-<?php echo esc_attr( $direction ); ?>" type="button" data-vava-product-nav data-product-uid="<?php echo esc_attr( $uid ); ?>">
		<?php if ( 'previous' === $direction ) : ?><span class="vava-product-reader-arrow" aria-hidden="true"><?php echo esc_html( $icon ); ?></span><?php endif; ?>
		<span class="vava-product-reader-destination-copy"><small><?php echo esc_html( $label ); ?></small><b><?php echo esc_html( (string) ( $data['title'] ?? '' ) ); ?></b></span>
		<?php if ( 'next' === $direction ) : ?><span class="vava-product-reader-arrow" aria-hidden="true"><?php echo esc_html( $icon ); ?></span><?php endif; ?>
	</button>
	<?php
}

/**
 * Render one complete hidden product panel inside the shared inline reader.
 */
function vava_digital_product_render_reader_article( array $card, array $previous, array $next, string $lang = 'ar' ): void {
	$data = vava_digital_product_reader_data( $card, $lang );
	if ( ! $data ) {
		return;
	}
	$uid          = sanitize_key( (string) ( $data['uid'] ?? $card['uid'] ?? '' ) );
	$labels       = vava_digital_product_reader_labels( $lang );
	$title_id     = 'vava-product-reader-title-' . sanitize_html_class( $uid );
	$pages_value  = trim( (string) ( $data['pages'] ?? '' ) );
	$currency     = trim( (string) ( $data['currency'] ?? '' ) );
	?>
	<article class="vava-product-reader-article" data-vava-product-reader-article data-product-group="<?php echo esc_attr( (string) ( $data['group'] ?? 'digital' ) ); ?>" data-product-uid="<?php echo esc_attr( $uid ); ?>" data-product-title="<?php echo esc_attr( (string) ( $data['title'] ?? '' ) ); ?>" hidden>
		<div class="vava-product-reader-body">
			<div class="vava-product-reader-copy">
				<?php if ( ! empty( $data['category'] ) ) : ?><span class="vava-product-reader-category"><?php echo esc_html( (string) $data['category'] ); ?></span><?php endif; ?>
				<h2 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( (string) ( $data['title'] ?? '' ) ); ?></h2>
				<p class="vava-product-reader-description"><?php echo esc_html( (string) ( $data['description'] ?? '' ) ); ?></p>

				<?php if ( '' !== trim( (string) ( $data['question'] ?? '' ) ) ) : ?>
				<div class="vava-product-reader-question"><span><?php echo esc_html( $labels['question'] ); ?></span><strong><?php echo esc_html( (string) $data['question'] ); ?></strong></div>
				<?php endif; ?>

				<?php if ( ! empty( $data['inside'] ) || ! empty( $data['ideal'] ) ) : ?>
				<div class="vava-product-reader-sections">
					<?php if ( ! empty( $data['inside'] ) ) : ?><section><h3><?php echo esc_html( $labels['inside'] ); ?></h3><ul><?php foreach ( (array) $data['inside'] as $item ) : ?><li><?php echo esc_html( (string) $item ); ?></li><?php endforeach; ?></ul></section><?php endif; ?>
					<?php if ( ! empty( $data['ideal'] ) ) : ?><section><h3><?php echo esc_html( $labels['ideal'] ); ?></h3><ul><?php foreach ( (array) $data['ideal'] as $item ) : ?><li><?php echo esc_html( (string) $item ); ?></li><?php endforeach; ?></ul></section><?php endif; ?>
				</div>
				<?php endif; ?>

				<?php if ( 'tangible' !== (string) ( $data['group'] ?? 'digital' ) ) : ?>
				<div class="vava-product-reader-details">
					<h3><?php echo esc_html( $labels['details'] ); ?></h3>
					<div class="vava-product-reader-facts">
						<div><span><?php echo esc_html( $labels['format'] ); ?></span><strong>PDF</strong></div>
						<div><span><?php echo esc_html( $labels['language'] ); ?></span><strong><?php echo esc_html( $labels['language_value'] ); ?></strong></div>
						<?php if ( '' !== $pages_value ) : ?><div><span><?php echo esc_html( $labels['pages'] ); ?></span><strong><?php echo esc_html( $pages_value ); ?></strong></div><?php endif; ?>
						<div><span><?php echo esc_html( $labels['usage'] ); ?></span><strong><?php echo esc_html( $labels['usage_value'] ); ?></strong></div>
					</div>
					<p class="vava-product-reader-rights"><?php echo esc_html( $labels['rights'] ); ?></p>
					<p class="vava-product-reader-disclaimer"><?php echo esc_html( $labels['disclaimer'] ); ?></p>
				</div>
				<?php endif; ?>
			</div>

			<aside class="vava-product-reader-media">
				<?php if ( ! empty( $data['cover_url'] ) ) : ?><div class="vava-product-reader-cover"><img src="<?php echo esc_url( (string) $data['cover_url'] ); ?>" alt="<?php echo esc_attr( (string) ( $data['title'] ?? '' ) ); ?>" loading="eager" decoding="async"></div><?php endif; ?>
				<div class="vava-product-reader-price<?php echo '' === trim( (string) ( $data['price'] ?? '' ) ) ? ' is-price-text' : ''; ?>"><?php if ( '' !== trim( (string) ( $data['price'] ?? '' ) ) ) : ?><strong><?php echo esc_html( (string) $data['price'] ); ?></strong><?php endif; ?><?php if ( '' !== $currency ) : ?><span><?php echo esc_html( $currency ); ?></span><?php endif; ?></div>
				<?php if ( 'tangible' !== (string) ( $data['group'] ?? 'digital' ) && function_exists( 'vava_digital_products_render_purchase_action' ) ) { vava_digital_products_render_purchase_action( $data, $lang ); } ?>
			</aside>
		</div>
		<nav class="vava-product-reader-toolbar" aria-label="<?php echo esc_attr( 'en' === $lang ? 'Product navigation' : 'التنقل بين المنتجات' ); ?>">
			<?php vava_digital_product_render_reader_destination( $previous, 'previous', $lang ); ?>
			<button class="vava-product-reader-close" type="button" data-vava-product-reader-close><span><?php echo esc_html( $labels['close'] ); ?></span><span aria-hidden="true">×</span></button>
			<?php vava_digital_product_render_reader_destination( $next, 'next', $lang ); ?>
		</nav>
	</article>
	<?php
}
