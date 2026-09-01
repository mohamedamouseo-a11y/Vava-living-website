<?php
/**
 * Native booking questionnaires for VAVA Living.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'vava_booking_questionnaire_defaults' ) ) {
	function vava_booking_questionnaire_defaults(): array {
		$radio = static function ( string $id, string $ar, string $en, array $options, bool $required = true ): array {
			return array( 'id' => $id, 'type' => 'radio', 'required' => $required ? 1 : 0, 'label' => array( 'ar' => $ar, 'en' => $en ), 'options' => $options );
		};
		$text = static function ( string $id, string $ar, string $en, bool $required = true, string $type = 'text' ): array {
			return array( 'id' => $id, 'type' => $type, 'required' => $required ? 1 : 0, 'label' => array( 'ar' => $ar, 'en' => $en ), 'options' => array() );
		};
		$checkbox = static function ( string $id, string $ar, string $en, array $options, bool $required = false ): array {
			return array( 'id' => $id, 'type' => 'checkboxes', 'required' => $required ? 1 : 0, 'label' => array( 'ar' => $ar, 'en' => $en ), 'options' => $options );
		};
		$yes_no = array( array( 'value' => 'yes', 'ar' => 'نعم', 'en' => 'Yes' ), array( 'value' => 'no', 'ar' => 'لا', 'en' => 'No' ) );
		$better_same = array(
			array( 'value' => 'much_better', 'ar' => 'أفضل بكثير', 'en' => 'Much better' ),
			array( 'value' => 'better', 'ar' => 'أفضل', 'en' => 'Better' ),
			array( 'value' => 'same', 'ar' => 'نفس الشيء', 'en' => 'About the same' ),
			array( 'value' => 'worse', 'ar' => 'أسوأ', 'en' => 'Worse' ),
			array( 'value' => 'unsure', 'ar' => 'غير متأكد/ة', 'en' => 'Not sure' ),
		);

		return array(
			'beginning' => array(
				'enabled' => 1,
				'title' => array( 'ar' => 'استبيان بداية الرحلة', 'en' => 'Journey Start Questionnaire' ),
				'description' => array( 'ar' => 'يظهر للجلسات الشاملة عندما تكون إجابة «هل سبق لك تجربة VAVA؟» هي «لا».', 'en' => 'Shown for comprehensive sessions when “Have you tried VAVA before?” is answered “No”.' ),
				'groups' => array(
					'basics' => array(
						'label' => array( 'ar' => 'البيانات الأساسية', 'en' => 'Basic information' ),
						'fields' => array(
							$text( 'full_name', 'الاسم الكريم (ثنائي)', 'Full name', true ),
							$text( 'age', 'العمر', 'Age', true, 'number' ),
							$radio( 'gender', 'الجنس', 'Gender', array( array( 'value' => 'male', 'ar' => 'ذكر', 'en' => 'Male' ), array( 'value' => 'female', 'ar' => 'أنثى', 'en' => 'Female' ) ) ),
							$text( 'height', 'الطول', 'Height', false, 'number' ),
							$text( 'weight', 'الوزن', 'Weight', false, 'number' ),
							$text( 'city_country', 'المدينة / الدولة', 'City / Country', true ),
							$radio( 'ayurveda_knowledge', 'كيف تصف معرفتك الحالية بالأيورفيدا؟', 'How would you describe your current Ayurveda knowledge?', array(
								array( 'value' => 'first_time', 'ar' => 'هذه أول مرة أتعرف عليها', 'en' => 'This is my first introduction' ),
								array( 'value' => 'basic', 'ar' => 'معرفة بسيطة من قراءة أو محتوى', 'en' => 'Basic knowledge from reading or content' ),
								array( 'value' => 'good', 'ar' => 'معرفة جيدة', 'en' => 'Good knowledge' ),
								array( 'value' => 'applied', 'ar' => 'أطبقها في حياتي', 'en' => 'I apply it in my life' ),
							) ),
							$text( 'ayurveda_experience', 'إذا كانت لديك تجربة سابقة مع الأيورفيدا، شاركها باختصار', 'If you have previous Ayurveda experience, briefly share it', false, 'textarea' ),
						),
					),
					'health' => array(
						'label' => array( 'ar' => 'الحالة الصحية والسلامة', 'en' => 'Health and safety' ),
						'fields' => array(
							$text( 'diagnoses', 'هل لديك أي تشخيصات أو أمراض حالية أو مزمنة؟', 'Do you have current or chronic diagnoses or conditions?', false, 'textarea' ),
							$text( 'diagnoses_started', 'متى بدأت هذه التشخيصات أو الأمراض؟', 'When did these diagnoses or conditions begin?', false ),
							$checkbox( 'current_symptoms', 'هل تعاني حاليًا من أي من الأعراض التالية؟', 'Do you currently experience any of the following symptoms?', array(
								array( 'value' => 'digestive', 'ar' => 'اضطرابات هضمية', 'en' => 'Digestive issues' ),
								array( 'value' => 'fatigue', 'ar' => 'تعب أو انخفاض في الطاقة', 'en' => 'Fatigue or low energy' ),
								array( 'value' => 'anxiety', 'ar' => 'قلق أو توتر', 'en' => 'Anxiety or stress' ),
								array( 'value' => 'headache', 'ar' => 'صداع متكرر', 'en' => 'Recurring headaches' ),
								array( 'value' => 'hormonal', 'ar' => 'اضطرابات هرمونية', 'en' => 'Hormonal issues' ),
								array( 'value' => 'pain', 'ar' => 'آلام في العضلات أو المفاصل', 'en' => 'Muscle or joint pain' ),
								array( 'value' => 'other', 'ar' => 'أخرى', 'en' => 'Other' ),
							) ),
							$text( 'symptoms_started', 'متى بدأت هذه الأعراض؟', 'When did these symptoms begin?', false ),
							$text( 'allergies', 'هل لديك حساسيات؟ (أطعمة / الجو / الحيوانات / غيرها)', 'Do you have allergies? (food / environment / animals / other)', false, 'textarea' ),
							$text( 'medications', 'هل تستخدم حاليًا أي أدوية أو مكملات غذائية أو أعشاب؟', 'Do you currently use medications, supplements, or herbs?', false, 'textarea' ),
							$text( 'recent_surgery', 'هل سبق لك إجراء عملية جراحية خلال آخر 6 أشهر؟ اذكر النوع والتاريخ', 'Have you had surgery during the last 6 months? State type and date', false, 'textarea' ),
							$radio( 'women_status', 'خاص بالنساء: هل أنت حاليًا؟', 'For women: are you currently?', array(
								array( 'value' => 'pregnant', 'ar' => 'حامل', 'en' => 'Pregnant' ),
								array( 'value' => 'postpartum', 'ar' => 'نفساء', 'en' => 'Postpartum' ),
								array( 'value' => 'breastfeeding', 'ar' => 'مرضعة', 'en' => 'Breastfeeding' ),
								array( 'value' => 'not_applicable', 'ar' => 'لا ينطبق', 'en' => 'Not applicable' ),
							), false ),
						),
					),
					'experience' => array(
						'label' => array( 'ar' => 'الخبرة السابقة', 'en' => 'Previous experience' ),
						'fields' => array(
							$text( 'booking_reason', 'ما الذي دفعك لحجز الجلسة في هذا الوقت؟', 'What prompted you to book this session now?', true, 'textarea' ),
							$text( 'focus_topic', 'هل يوجد جانب أو موضوع ترغب في التركيز عليه خلال الجلسة؟', 'Is there a topic you would like to focus on during the session?', false, 'textarea' ),
							$text( 'expected_result', 'ما النتيجة التي تتمنى الخروج بها من الجلسة؟', 'What outcome do you hope to achieve from the session?', true, 'textarea' ),
						),
					),
					'goals' => array(
						'label' => array( 'ar' => 'أهداف الجلسة والتفضيلات', 'en' => 'Session goals and preferences' ),
						'fields' => array(
							$radio( 'learning_style', 'أي طريقة تساعدك أكثر على فهم المعلومات أثناء الجلسة؟', 'Which way helps you understand information best during the session?', array(
								array( 'value' => 'verbal', 'ar' => 'الشرح بالكلام', 'en' => 'Verbal explanation' ),
								array( 'value' => 'visual', 'ar' => 'الصور والرسومات', 'en' => 'Images and diagrams' ),
								array( 'value' => 'examples', 'ar' => 'الأمثلة والتطبيقات', 'en' => 'Examples and practical application' ),
								array( 'value' => 'mixed', 'ar' => 'مزيج من أكثر من طريقة', 'en' => 'A mix of methods' ),
								array( 'value' => 'none', 'ar' => 'لا يوجد تفضيل', 'en' => 'No preference' ),
							), false ),
							$text( 'additional_information', 'أي معلومات إضافية تساعدنا على فهم حالتك قبل الجلسة', 'Any additional information that helps us understand your situation before the session', false, 'textarea' ),
						),
					),
				),
			),
			'midpoint' => array(
				'enabled' => 1,
				'title' => array( 'ar' => 'استبيان منتصف الرحلة', 'en' => 'Mid-Journey Questionnaire' ),
				'description' => array( 'ar' => 'يُقدَّم لأي عميل سابق أو جديد يحجز جلسة متابعة.', 'en' => 'Shown to every new or returning client who books a follow-up session.' ),
				'groups' => array(
					'after_session' => array(
						'label' => array( 'ar' => 'تقييم الحالة بعد الجلسة', 'en' => 'Post-session assessment' ),
						'fields' => array(
							$text( 'full_name', 'الاسم الكريم (ثنائي)', 'Full name', true ),
							$text( 'overall_health', 'كيف كانت صحتك بشكل عام من بعد جلستنا؟', 'How has your overall health been since our session?', true, 'textarea' ),
							$text( 'biggest_change', 'ما أكثر تغير لاحظته في جسمك أو نفسيتك أو حياتك اليومية؟', 'What is the biggest change you noticed in your body, emotions, or daily life?', true, 'textarea' ),
							$text( 'symptoms_change', 'كيف صارت الأعراض التي ذكرتها في أول جلسة؟', 'How have the symptoms mentioned in the first session changed?', true, 'textarea' ),
						),
					),
					'recommendations' => array(
						'label' => array( 'ar' => 'تطبيق التوصيات', 'en' => 'Applying recommendations' ),
						'fields' => array(
							$radio( 'recommendations_applied', 'إلى أي درجة قدرت على تطبيق التوصيات؟', 'To what extent were you able to apply the recommendations?', array(
								array( 'value' => 'all', 'ar' => 'كلها', 'en' => 'All of them' ), array( 'value' => 'most', 'ar' => 'معظمها', 'en' => 'Most of them' ), array( 'value' => 'some', 'ar' => 'القليل منها', 'en' => 'A few' ), array( 'value' => 'none', 'ar' => 'لم أتمكن من تطبيقها', 'en' => 'I could not apply them' ),
							) ),
							$text( 'easiest', 'ما أسهل شيء كان عليك؟', 'What was easiest for you?', false, 'textarea' ),
							$text( 'hardest', 'ما أصعب شيء كان عليك؟', 'What was hardest for you?', false, 'textarea' ),
							$text( 'change_after_recommendations', 'هل لاحظت أي تغيير بعد تطبيق التوصيات؟', 'Did you notice any change after applying the recommendations?', false, 'textarea' ),
						),
					),
					'wellbeing' => array(
						'label' => array( 'ar' => 'الحالة اليومية', 'en' => 'Daily wellbeing' ),
						'fields' => array(
							$radio( 'sleep', 'كيف كان نومك؟', 'How has your sleep been?', $better_same ),
							$radio( 'mood', 'كيف كان مزاجك؟', 'How has your mood been?', $better_same ),
							$radio( 'focus', 'كيف كان تركيزك؟', 'How has your focus been?', $better_same ),
							$radio( 'digestion', 'كيف كان هضمك؟', 'How has your digestion been?', $better_same ),
							$radio( 'appetite', 'كيف كانت شهيتك؟', 'How has your appetite been?', $better_same ),
							$radio( 'energy', 'كيف كانت طاقتك؟', 'How has your energy been?', $better_same ),
							$radio( 'breathing_comfort', 'كيف كان تنفسك وراحتك؟', 'How have your breathing and comfort been?', $better_same, false ),
						),
					),
					'next_session' => array(
						'label' => array( 'ar' => 'الجلسة القادمة', 'en' => 'Next session' ),
						'fields' => array(
							$text( 'new_changes', 'هل حدث شيء جديد منذ الجلسة السابقة؟', 'Has anything new happened since the previous session?', false, 'textarea' ),
							$text( 'next_focus', 'ما أكثر شيء ترغب في التركيز عليه في الجلسة القادمة؟', 'What would you most like to focus on in the next session?', true, 'textarea' ),
							$text( 'questions', 'هل يوجد أي شيء ترغب في قوله أو السؤال عنه؟', 'Is there anything you would like to share or ask?', false, 'textarea' ),
							$text( 'daily_activity', 'كيف كان نشاطك اليومي؟', 'How has your daily activity been?', false, 'textarea' ),
							$text( 'additional_notes', 'ملاحظات إضافية', 'Additional notes', false, 'textarea' ),
						),
					),
				),
			),
			'impact' => array(
				'enabled' => 1,
				'title' => array( 'ar' => 'استبيان أثر الرحلة', 'en' => 'Journey Impact Questionnaire' ),
				'description' => array( 'ar' => 'يظهر بعد إنهاء الخدمة أو الخدمات بالكامل لأي عميل حجز جلسة استشارية.', 'en' => 'Shown after the full service journey is completed for any consultation client.' ),
				'groups' => array(
					'impact_assessment' => array(
						'label' => array( 'ar' => 'تقييم أثر الخدمة', 'en' => 'Service impact assessment' ),
						'fields' => array(
							$text( 'full_name', 'الاسم الكريم (ثنائي)', 'Full name', true ),
							$radio( 'journey_overall', 'كيف كانت رحلتك بشكل عام من بعد جلستنا؟', 'How has your journey been overall since our session?', $better_same ),
							$text( 'biggest_impact', 'ما أكثر شيء لاحظته في جسمك أو نفسيتك أو حياتك اليومية؟', 'What was the biggest impact you noticed in your body, emotions, or daily life?', true, 'textarea' ),
							$radio( 'symptoms_result', 'كيف صارت الأعراض التي ناقشناها في أول جلسة؟', 'How have the symptoms discussed in the first session changed?', $better_same ),
						),
					),
					'results' => array(
						'label' => array( 'ar' => 'النتائج المحققة', 'en' => 'Achieved results' ),
						'fields' => array(
							$radio( 'recommendations_level', 'إلى أي درجة قدرت على تطبيق التوصيات؟', 'To what extent did you apply the recommendations?', array(
								array( 'value' => 'all', 'ar' => 'كلها', 'en' => 'All' ), array( 'value' => 'most', 'ar' => 'معظمها', 'en' => 'Most' ), array( 'value' => 'some', 'ar' => 'القليل منها', 'en' => 'Some' ), array( 'value' => 'none', 'ar' => 'لم أتمكن من تطبيقها', 'en' => 'Could not apply them' ),
							) ),
							$text( 'easiest', 'ما أسهل شيء كان عليك؟', 'What was easiest for you?', false, 'textarea' ),
							$text( 'hardest', 'ما أصعب شيء كان عليك؟', 'What was hardest for you?', false, 'textarea' ),
							$text( 'recommendations_effect', 'هل لاحظت تغييرًا بعد تطبيق التوصيات؟ اشرح باختصار', 'Did you notice a change after applying the recommendations? Briefly explain', false, 'textarea' ),
						),
					),
					'lifestyle' => array(
						'label' => array( 'ar' => 'التغيير في نمط الحياة', 'en' => 'Lifestyle changes' ),
						'fields' => array(
							$radio( 'sleep', 'كيف كان نومك؟', 'How has your sleep been?', $better_same ),
							$radio( 'mood', 'كيف كان مزاجك؟', 'How has your mood been?', $better_same ),
							$radio( 'focus', 'كيف كان تركيزك؟', 'How has your focus been?', $better_same ),
							$radio( 'digestion', 'كيف كان هضمك؟', 'How has your digestion been?', $better_same ),
							$radio( 'energy', 'كيف كانت طاقتك؟', 'How has your energy been?', $better_same ),
							$radio( 'breathing_comfort', 'كيف كان تنفسك وراحتك؟', 'How have your breathing and comfort been?', $better_same, false ),
						),
					),
					'satisfaction' => array(
						'label' => array( 'ar' => 'الرضا العام والتوصيات المستقبلية', 'en' => 'Overall satisfaction and future recommendations' ),
						'fields' => array(
							$text( 'new_changes', 'هل حدث شيء جديد منذ بداية الرحلة؟', 'Has anything new happened since the journey began?', false, 'textarea' ),
							$text( 'future_focus', 'ما أكثر شيء ترغب في التركيز عليه مستقبلًا؟', 'What would you most like to focus on next?', true, 'textarea' ),
							$radio( 'recommend_service', 'هل توصي بخدمات VAVA للآخرين؟', 'Would you recommend VAVA services to others?', $yes_no ),
							$text( 'final_message', 'هل يوجد أي شيء ترغب في قوله أو السؤال عنه؟', 'Is there anything you would like to share or ask?', false, 'textarea' ),
						),
					),
				),
			),
		);
	}
}

function vava_booking_questionnaire_settings( int $page_id = 0 ): array {
	$page_id = $page_id ?: ( function_exists( 'vava_booking_page_id' ) ? vava_booking_page_id() : 0 );
	$stored = $page_id ? get_post_meta( $page_id, '_vava_booking_questionnaires', true ) : array();
	return array_replace_recursive( vava_booking_questionnaire_defaults(), is_array( $stored ) ? $stored : array() );
}

function vava_booking_questionnaire_service_category( array $service ): string {
	$category = sanitize_key( (string) ( $service['category'] ?? '' ) );
	if ( in_array( $category, array( 'comprehensive', 'followup', 'quick' ), true ) ) { return $category; }
	$service_text = (string) ( $service['title'] ?? '' ) . ' ' . (string) ( $service['session_type'] ?? '' ) . ' ' . (string) ( $service['uid'] ?? '' );
	$haystack = function_exists( 'mb_strtolower' ) ? mb_strtolower( $service_text ) : strtolower( $service_text );
	if ( preg_match( '/متابعة|follow.?up|followup/u', $haystack ) ) { return 'followup'; }
	if ( preg_match( '/شامل|comprehensive|balance|healing/u', $haystack ) ) { return 'comprehensive'; }
	return 'quick';
}

function vava_booking_questionnaire_mode_for_service( array $service, int $page_id = 0 ): string {
	$settings = vava_booking_questionnaire_settings( $page_id );
	$category = vava_booking_questionnaire_service_category( $service );
	if ( 'followup' === $category && ! empty( $settings['midpoint']['enabled'] ) ) { return 'midpoint'; }
	if ( 'comprehensive' === $category && ! empty( $settings['beginning']['enabled'] ) ) { return 'beginning'; }
	return 'none';
}

function vava_booking_questionnaire_type_for_booking( array $service, string $previous, int $page_id = 0 ): string {
	$mode = vava_booking_questionnaire_mode_for_service( $service, $page_id );
	if ( 'midpoint' === $mode ) { return 'midpoint'; }
	if ( 'beginning' === $mode && 'no' === $previous ) { return 'beginning'; }
	return 'none';
}

function vava_booking_questionnaire_field_map( array $questionnaire ): array {
	$map = array();
	foreach ( (array) ( $questionnaire['groups'] ?? array() ) as $group_key => $group ) {
		foreach ( (array) ( $group['fields'] ?? array() ) as $field ) {
			$id = sanitize_key( (string) ( $field['id'] ?? '' ) );
			if ( $id ) { $field['_group'] = $group_key; $map[ $id ] = $field; }
		}
	}
	return $map;
}

function vava_booking_questionnaire_option_label( array $field, $value, string $lang ): string {
	$values = is_array( $value ) ? $value : array( $value );
	$labels = array();
	foreach ( $values as $item ) {
		$matched = false;
		foreach ( (array) ( $field['options'] ?? array() ) as $option ) {
			if ( (string) ( $option['value'] ?? '' ) === (string) $item ) {
				$labels[] = (string) ( $option[ $lang ] ?? $option['ar'] ?? $item );
				$matched = true;
				break;
			}
		}
		if ( ! $matched && '' !== trim( (string) $item ) ) { $labels[] = (string) $item; }
	}
	return implode( '، ', $labels );
}

function vava_booking_questionnaire_sanitize_answers( string $type, array $raw_answers, string $lang = 'ar', bool $validate = true ) {
	$settings = vava_booking_questionnaire_settings();
	if ( ! isset( $settings[ $type ] ) ) { return new WP_Error( 'invalid_questionnaire', 'نوع الاستبيان غير صحيح.' ); }
	$questionnaire = $settings[ $type ];
	$map = vava_booking_questionnaire_field_map( $questionnaire );
	$clean = array();
	$missing = array();
	foreach ( $map as $id => $field ) {
		$value = $raw_answers[ $id ] ?? '';
		if ( 'checkboxes' === (string) ( $field['type'] ?? '' ) ) {
			$value = is_array( $value ) ? array_values( array_filter( array_map( 'sanitize_text_field', wp_unslash( $value ) ) ) ) : array();
		} else {
			$value = is_scalar( $value ) ? sanitize_textarea_field( wp_unslash( (string) $value ) ) : '';
		}
		if ( $validate && ! empty( $field['required'] ) && ( is_array( $value ) ? ! $value : '' === trim( (string) $value ) ) ) { $missing[] = $id; }
		$has_value = is_array( $value ) ? ! empty( $value ) : '' !== trim( (string) $value );
		if ( $has_value ) { $clean[ $id ] = $value; }
	}
	if ( $missing ) { return new WP_Error( 'questionnaire_required', 'يرجى استكمال الحقول الإلزامية في الاستبيان.', array( 'fields' => $missing ) ); }
	return array(
		'type' => $type,
		'title' => (string) ( $questionnaire['title'][ $lang ] ?? $questionnaire['title']['ar'] ?? '' ),
		'language' => $lang,
		'completed_at' => current_time( 'mysql' ),
		'answers' => $clean,
		'snapshot' => $questionnaire,
	);
}

function vava_booking_questionnaire_field_placeholder( string $id, string $type, string $lang ): string {
	$placeholders = array(
		'full_name' => array( 'ar' => 'اكتب الاسم الكريم', 'en' => 'Enter full name' ),
		'age' => array( 'ar' => 'مثال: 35', 'en' => 'e.g. 35' ),
		'height' => array( 'ar' => 'مثال: 175', 'en' => 'e.g. 175' ),
		'weight' => array( 'ar' => 'مثال: 70', 'en' => 'e.g. 70' ),
		'city_country' => array( 'ar' => 'مثال: القاهرة، مصر', 'en' => 'Select city / country' ),
	);
	if ( isset( $placeholders[ $id ][ $lang ] ) ) { return (string) $placeholders[ $id ][ $lang ]; }
	if ( 'textarea' === $type ) { return 'en' === $lang ? 'Write your answer here…' : 'اكتب إجابتك هنا…'; }
	return 'en' === $lang ? 'Enter your answer' : 'اكتب إجابتك';
}

function vava_booking_questionnaire_field_unit( string $id, string $lang ): string {
	$units = array(
		'age' => array( 'ar' => 'سنة', 'en' => 'years' ),
		'height' => array( 'ar' => 'سم', 'en' => 'cm' ),
		'weight' => array( 'ar' => 'كجم', 'en' => 'kg' ),
	);
	return (string) ( $units[ $id ][ $lang ] ?? '' );
}

function vava_booking_questionnaire_option_presentation( string $field_id, string $value, string $lang ): array {
	$items = array(
		'gender' => array(
			'male' => array( 'icon' => '♂', 'ar' => '', 'en' => '' ),
			'female' => array( 'icon' => '♀', 'ar' => '', 'en' => '' ),
		),
		'ayurveda_knowledge' => array(
			'first_time' => array( 'icon' => '⌁', 'ar' => 'أتعرف عليها لأول مرة', 'en' => 'I’m beginning to learn' ),
			'basic' => array( 'icon' => '▤', 'ar' => 'قرأت أو شاهدت بعض المحتوى', 'en' => 'I’ve read or watched content' ),
			'good' => array( 'icon' => '❧', 'ar' => 'لدي فهم جيد وأتعمق في التعلم', 'en' => 'I understand the basics well' ),
			'applied' => array( 'icon' => '◉', 'ar' => 'أطبقها بانتظام في حياتي', 'en' => 'It is part of my routine' ),
		),
	);
	$item = $items[ $field_id ][ $value ] ?? array();
	return array(
		'icon' => (string) ( $item['icon'] ?? '' ),
		'description' => (string) ( $item[ $lang ] ?? '' ),
	);
}

function vava_booking_questionnaire_group_intro( string $type, string $group_key, string $lang ): string {
	$copy = array(
		'beginning' => array(
			'basics' => array( 'ar' => 'يرجى ملء بياناتك الأساسية بدقة لمساعدتنا في تصميم تجربتك.', 'en' => 'Please provide your details so we can personalize your experience.' ),
			'health' => array( 'ar' => 'هذه المعلومات تساعد فريق VAVA على تقديم تجربة آمنة ومناسبة لك.', 'en' => 'This information helps the VAVA team prepare a safe, suitable experience.' ),
			'experience' => array( 'ar' => 'شاركنا ما تتوقعه من الجلسة وما ترغب في التركيز عليه.', 'en' => 'Tell us what you expect and what you would like to focus on.' ),
			'goals' => array( 'ar' => 'اختر الطريقة التي تناسبك وأضف أي تفاصيل تساعدنا على الاستعداد.', 'en' => 'Choose what suits you and add anything that helps us prepare.' ),
		),
		'midpoint' => array(
			'after_session' => array( 'ar' => 'ساعدنا على فهم التغيرات التي حدثت منذ جلستك السابقة.', 'en' => 'Help us understand what has changed since your previous session.' ),
			'recommendations' => array( 'ar' => 'أخبرنا بما تمكنت من تطبيقه وما كان سهلًا أو صعبًا.', 'en' => 'Tell us what you applied and what felt easy or difficult.' ),
			'wellbeing' => array( 'ar' => 'قيّم حالتك اليومية باختيارات واضحة وسريعة.', 'en' => 'Rate your daily wellbeing using quick, clear choices.' ),
			'next_session' => array( 'ar' => 'شاركنا أولوياتك وأسئلتك قبل الجلسة القادمة.', 'en' => 'Share your priorities and questions before the next session.' ),
		),
		'impact' => array(
			'impact_assessment' => array( 'ar' => 'قيّم الأثر العام الذي لاحظته بعد رحلتك مع VAVA.', 'en' => 'Assess the overall impact you noticed after your VAVA journey.' ),
			'results' => array( 'ar' => 'ساعدنا على فهم النتائج ومدى تطبيق التوصيات.', 'en' => 'Help us understand the results and how you applied the recommendations.' ),
			'lifestyle' => array( 'ar' => 'قيّم التغيرات التي لاحظتها في نمط حياتك اليومي.', 'en' => 'Rate the changes you noticed in your daily lifestyle.' ),
			'satisfaction' => array( 'ar' => 'شاركنا تقييمك وما ترغب في التركيز عليه مستقبلًا.', 'en' => 'Share your feedback and what you would like to focus on next.' ),
		),
	);
	return (string) ( $copy[ $type ][ $group_key ][ $lang ] ?? '' );
}

function vava_booking_questionnaire_render_field( array $field, string $lang, array $defaults = array() ): void {
	$id = sanitize_key( (string) ( $field['id'] ?? '' ) );
	if ( ! $id ) { return; }
	$label = (string) ( $field['label'][ $lang ] ?? $field['label']['ar'] ?? $id );
	$type = (string) ( $field['type'] ?? 'text' );
	$required = ! empty( $field['required'] );
	$value = (string) ( $defaults[ $id ] ?? '' );
	$name = 'questionnaire_answers[' . $id . ']';
	$placeholder = vava_booking_questionnaire_field_placeholder( $id, $type, $lang );
	$unit = vava_booking_questionnaire_field_unit( $id, $lang );
	$options = (array) ( $field['options'] ?? array() );
	$option_count = count( $options );
	$has_visual_options = in_array( $id, array( 'gender', 'ayurveda_knowledge' ), true );
	?>
	<div class="vava-questionnaire-field is-<?php echo esc_attr( $type ); ?> field-<?php echo esc_attr( $id ); ?>" data-questionnaire-field-wrap="<?php echo esc_attr( $id ); ?>" data-questionnaire-field-id="<?php echo esc_attr( $id ); ?>">
		<label class="vava-questionnaire-label"><?php echo esc_html( $label ); ?><?php if ( $required ) : ?><span>*</span><?php endif; ?></label>
		<?php if ( 'textarea' === $type ) : ?>
			<textarea name="<?php echo esc_attr( $name ); ?>" rows="4" placeholder="<?php echo esc_attr( $placeholder ); ?>" data-questionnaire-field="<?php echo esc_attr( $id ); ?>"<?php echo $required ? ' required' : ''; ?>><?php echo esc_textarea( $value ); ?></textarea>
		<?php elseif ( 'radio' === $type ) : ?>
			<div class="vava-questionnaire-options option-count-<?php echo esc_attr( (string) $option_count ); ?><?php echo $has_visual_options ? ' has-visual-options' : ' is-compact-options'; ?>" role="radiogroup" data-option-count="<?php echo esc_attr( (string) $option_count ); ?>">
				<?php foreach ( $options as $option ) : $option_value = (string) ( $option['value'] ?? '' ); $presentation = vava_booking_questionnaire_option_presentation( $id, $option_value, $lang ); ?>
					<label class="vava-questionnaire-option-card option-<?php echo esc_attr( sanitize_html_class( $option_value ) ); ?>" data-option-value="<?php echo esc_attr( $option_value ); ?>">
						<input type="radio" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $option_value ); ?>" data-questionnaire-field="<?php echo esc_attr( $id ); ?>"<?php checked( $value, $option_value ); ?><?php echo $required ? ' required' : ''; ?>/>
						<i class="vava-questionnaire-option-marker" aria-hidden="true"></i>
						<?php if ( $presentation['icon'] ) : ?><span class="vava-questionnaire-option-icon" aria-hidden="true"><?php echo esc_html( $presentation['icon'] ); ?></span><?php endif; ?>
						<span class="vava-questionnaire-option-copy"><b><?php echo esc_html( (string) ( $option[ $lang ] ?? $option['ar'] ?? $option_value ) ); ?></b><?php if ( $presentation['description'] ) : ?><small><?php echo esc_html( $presentation['description'] ); ?></small><?php endif; ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		<?php elseif ( 'checkboxes' === $type ) : ?>
			<div class="vava-questionnaire-options is-multiple option-count-<?php echo esc_attr( (string) $option_count ); ?>" data-option-count="<?php echo esc_attr( (string) $option_count ); ?>">
				<?php foreach ( $options as $option ) : $option_value = (string) ( $option['value'] ?? '' ); ?>
					<label class="vava-questionnaire-option-card option-<?php echo esc_attr( sanitize_html_class( $option_value ) ); ?>"><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( $option_value ); ?>" data-questionnaire-field="<?php echo esc_attr( $id ); ?>"/><i class="vava-questionnaire-option-marker" aria-hidden="true"></i><span class="vava-questionnaire-option-copy"><b><?php echo esc_html( (string) ( $option[ $lang ] ?? $option['ar'] ?? $option_value ) ); ?></b></span></label>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="vava-questionnaire-input-shell<?php echo $unit ? ' has-unit' : ''; ?>">
				<input type="<?php echo 'number' === $type ? 'number' : 'text'; ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" data-questionnaire-field="<?php echo esc_attr( $id ); ?>"<?php echo 'number' === $type ? ' inputmode="decimal" min="0"' : ''; ?><?php echo $required ? ' required' : ''; ?>/>
				<?php if ( $unit ) : ?><span><?php echo esc_html( $unit ); ?></span><?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

function vava_booking_questionnaire_render_frontend( string $type, string $lang, array $defaults = array() ): void {
	$settings = vava_booking_questionnaire_settings();
	if ( empty( $settings[ $type ] ) ) { return; }
	$questionnaire = $settings[ $type ];
	$groups = (array) ( $questionnaire['groups'] ?? array() );
	$title = (string) ( $questionnaire['title'][ $lang ] ?? $questionnaire['title']['ar'] ?? '' );
	?>
	<section class="vava-booking-step vava-booking-step--questionnaire is-locked" data-booking-step="2" data-questionnaire-stage data-questionnaire-type="<?php echo esc_attr( $type ); ?>" data-questionnaire-language="<?php echo esc_attr( $lang ); ?>" dir="<?php echo esc_attr( 'en' === $lang ? 'ltr' : 'rtl' ); ?>" hidden>
		<header class="vava-booking-stage-intro"><h2><?php echo esc_html( $title ); ?></h2><p><?php echo esc_html( 'en' === $lang ? 'Your answers help us prepare a safer and more personalized experience.' : 'إجاباتك تساعدنا على تجهيز تجربة أكثر دقة وملاءمة لك.' ); ?></p></header>
		<input type="hidden" name="questionnaire_type" value="<?php echo esc_attr( $type ); ?>" data-questionnaire-type-input/>
		<div class="vava-questionnaire-layout">
			<aside class="vava-questionnaire-summary-card">
				<span class="vava-questionnaire-summary-icon">▤</span>
				<h3><?php echo esc_html( 'en' === $lang ? 'Your answers summary' : 'ملخص إجاباتك' ); ?></h3>
				<p><?php echo esc_html( 'en' === $lang ? 'Your answers will be included with the booking details sent to the VAVA team.' : 'سيتم تضمين إجاباتك مع بيانات الحجز المرسلة إلى فريق VAVA.' ); ?></p>
				<div data-questionnaire-live-summary></div>
				<footer><span aria-hidden="true">⌾</span><?php echo esc_html( 'en' === $lang ? 'Your information is private and secure.' : 'بياناتك آمنة وسرية.' ); ?></footer>
			</aside>
			<div class="vava-questionnaire-wizard">
				<nav class="vava-questionnaire-steps" aria-label="<?php echo esc_attr( 'en' === $lang ? 'Questionnaire sections' : 'أقسام الاستبيان' ); ?>">
					<?php $group_index = 0; foreach ( $groups as $group_key => $group ) : $group_index++; ?>
						<button type="button" class="<?php echo 1 === $group_index ? 'is-active' : ''; ?>" data-questionnaire-group-button="<?php echo esc_attr( $group_key ); ?>"><span><?php echo esc_html( (string) $group_index ); ?></span><b><?php echo esc_html( (string) ( $group['label'][ $lang ] ?? $group['label']['ar'] ?? $group_key ) ); ?></b></button>
					<?php endforeach; ?>
				</nav>
				<div class="vava-questionnaire-groups">
					<?php $group_index = 0; $group_count = count( $groups ); foreach ( $groups as $group_key => $group ) : $group_index++; ?>
						<section class="vava-questionnaire-group<?php echo 1 === $group_index ? ' is-active' : ''; ?>" data-questionnaire-group="<?php echo esc_attr( $group_key ); ?>" data-questionnaire-group-index="<?php echo esc_attr( (string) $group_index ); ?>"<?php echo 1 === $group_index ? '' : ' hidden'; ?>>
							<header><small><?php echo esc_html( sprintf( 'en' === $lang ? 'Section %1$d of %2$d' : 'القسم %1$d من %2$d', $group_index, $group_count ) ); ?></small><h3><?php echo esc_html( (string) ( $group['label'][ $lang ] ?? $group['label']['ar'] ?? '' ) ); ?></h3><?php $group_intro = vava_booking_questionnaire_group_intro( $type, (string) $group_key, $lang ); if ( $group_intro ) : ?><p><?php echo esc_html( $group_intro ); ?></p><?php endif; ?></header>
							<div class="vava-questionnaire-fields">
								<?php foreach ( (array) ( $group['fields'] ?? array() ) as $field ) { vava_booking_questionnaire_render_field( $field, $lang, $defaults ); } ?>
							</div>
							<footer>
								<button type="button" class="vava-booking-stage-button is-back" data-questionnaire-prev<?php echo 1 === $group_index ? ' disabled' : ''; ?>><?php echo esc_html( 'en' === $lang ? 'Previous' : 'السابق' ); ?></button>
								<div class="vava-questionnaire-progress"><span><?php echo esc_html( sprintf( '%d%%', (int) round( $group_index / max( 1, $group_count ) * 100 ) ) ); ?></span><i><b style="width:<?php echo esc_attr( (string) ( $group_index / max( 1, $group_count ) * 100 ) ); ?>%"></b></i></div>
								<?php if ( $group_index < $group_count ) : ?><button type="button" class="vava-booking-stage-button is-primary" data-questionnaire-next><?php echo esc_html( 'en' === $lang ? 'Next' : 'التالي' ); ?> <span>←</span></button><?php else : ?><button type="button" class="vava-booking-stage-button is-primary" data-questionnaire-finish><?php echo esc_html( 'en' === $lang ? 'Choose appointment' : 'اختيار الموعد' ); ?> <span>←</span></button><?php endif; ?>
							</footer>
						</section>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<div class="vava-booking-error" data-questionnaire-error role="alert"></div>
	</section>
	<?php
}

function vava_booking_questionnaire_render_admin_panel( WP_Post $post ): void {
	$settings = vava_booking_questionnaire_settings( (int) $post->ID );
	$icons = array( 'beginning' => '🌱', 'midpoint' => '≋', 'impact' => '★' );
	?>
	<section class="vava-booking-admin-panel vava-questionnaire-admin-panel" data-booking-panel="questionnaires">
		<div class="vava-booking-admin-card">
			<div class="vava-booking-card-heading"><div><small data-vava-i18n-ar="إعدادات الاستبيانات" data-vava-i18n-en="Questionnaire settings">إعدادات الاستبيانات</small><h3 data-vava-i18n-ar="الاستبيانات الثلاثة" data-vava-i18n-en="Three booking questionnaires">الاستبيانات الثلاثة</h3><p data-vava-i18n-ar="فتح استبيان واحد يغلق الباقي. يمكن تعديل الأسئلة وخياراتها بالعربية والإنجليزية." data-vava-i18n-en="Opening one questionnaire closes the others. Questions and options are editable in Arabic and English.">فتح استبيان واحد يغلق الباقي. يمكن تعديل الأسئلة وخياراتها بالعربية والإنجليزية.</p></div><span class="vava-booking-source-badge" data-vava-i18n-ar="متزامن مع الترجمة العربية والإنجليزية" data-vava-i18n-en="Synchronized with Arabic and English translation">متزامن مع الترجمة العربية والإنجليزية</span></div>
			<div class="vava-questionnaire-admin-accordions" data-questionnaire-admin-accordions>
				<?php $q_index = 0; foreach ( $settings as $type => $questionnaire ) : $q_index++; ?>
					<article class="vava-questionnaire-admin-accordion<?php echo 1 === $q_index ? ' is-open' : ''; ?>" data-questionnaire-admin-accordion>
						<header><button type="button" data-questionnaire-admin-toggle aria-expanded="<?php echo 1 === $q_index ? 'true' : 'false'; ?>"><span><?php echo esc_html( $icons[ $type ] ?? '▤' ); ?></span><div><h4 class="vava-questionnaire-lang-copy" data-questionnaire-lang-copy="ar"><?php echo esc_html( (string) $questionnaire['title']['ar'] ); ?></h4><h4 class="vava-questionnaire-lang-copy" data-questionnaire-lang-copy="en" hidden><?php echo esc_html( (string) $questionnaire['title']['en'] ); ?></h4><p class="vava-questionnaire-lang-copy" data-questionnaire-lang-copy="ar"><?php echo esc_html( (string) $questionnaire['description']['ar'] ); ?></p><p class="vava-questionnaire-lang-copy" data-questionnaire-lang-copy="en" hidden><?php echo esc_html( (string) $questionnaire['description']['en'] ); ?></p></div><i>⌄</i></button><label class="vava-booking-admin-switch"><input type="hidden" name="vava_questionnaires[<?php echo esc_attr( $type ); ?>][enabled]" value="0"/><input type="checkbox" name="vava_questionnaires[<?php echo esc_attr( $type ); ?>][enabled]" value="1"<?php checked( ! empty( $questionnaire['enabled'] ) ); ?>/><span></span><b data-vava-i18n-ar="مفعّل" data-vava-i18n-en="Enabled">مفعّل</b></label></header>
						<div class="vava-questionnaire-admin-body"<?php echo 1 === $q_index ? '' : ' hidden'; ?>>
							<?php foreach ( array( 'ar', 'en' ) as $lang ) : ?>
								<div class="vava-questionnaire-admin-language" data-questionnaire-admin-lang="<?php echo esc_attr( $lang ); ?>"<?php echo 'ar' === $lang ? '' : ' hidden'; ?> dir="<?php echo 'en' === $lang ? 'ltr' : 'rtl'; ?>">
									<div class="vava-booking-admin-grid"><label class="vava-booking-admin-field"><span><?php echo esc_html( 'en' === $lang ? 'Questionnaire title' : 'عنوان الاستبيان' ); ?></span><input name="vava_questionnaires[<?php echo esc_attr( $type ); ?>][title][<?php echo esc_attr( $lang ); ?>]" value="<?php echo esc_attr( (string) $questionnaire['title'][ $lang ] ); ?>"/></label><label class="vava-booking-admin-field is-full"><span><?php echo esc_html( 'en' === $lang ? 'Display rule description' : 'وصف شرط الظهور' ); ?></span><textarea name="vava_questionnaires[<?php echo esc_attr( $type ); ?>][description][<?php echo esc_attr( $lang ); ?>]" rows="2"><?php echo esc_textarea( (string) $questionnaire['description'][ $lang ] ); ?></textarea></label></div>
									<div class="vava-questionnaire-group-accordions">
										<?php $g_index = 0; foreach ( (array) $questionnaire['groups'] as $group_key => $group ) : $g_index++; ?>
											<article class="vava-questionnaire-group-accordion<?php echo 1 === $g_index ? ' is-open' : ''; ?>" data-questionnaire-group-admin>
												<button type="button" data-questionnaire-group-toggle aria-expanded="<?php echo 1 === $g_index ? 'true' : 'false'; ?>"><b><?php echo esc_html( (string) ( $group['label'][ $lang ] ?? $group_key ) ); ?></b><span><?php echo esc_html( count( (array) ( $group['fields'] ?? array() ) ) . ( 'en' === $lang ? ' fields' : ' حقول' ) ); ?></span><i>⌄</i></button>
												<div class="vava-questionnaire-group-admin-body"<?php echo 1 === $g_index ? '' : ' hidden'; ?>>
													<label class="vava-booking-admin-field is-full"><span><?php echo esc_html( 'en' === $lang ? 'Section title' : 'عنوان القسم' ); ?></span><input name="vava_questionnaires[<?php echo esc_attr( $type ); ?>][groups][<?php echo esc_attr( $group_key ); ?>][label][<?php echo esc_attr( $lang ); ?>]" value="<?php echo esc_attr( (string) $group['label'][ $lang ] ); ?>"/></label>
													<div class="vava-questionnaire-field-admin-list">
														<?php foreach ( (array) ( $group['fields'] ?? array() ) as $field_index => $field ) : ?>
															<div class="vava-questionnaire-field-admin-row"><span><?php echo esc_html( (string) ( $field['id'] ?? '' ) ); ?></span><label><input type="hidden" name="vava_questionnaires[<?php echo esc_attr( $type ); ?>][groups][<?php echo esc_attr( $group_key ); ?>][fields][<?php echo esc_attr( (string) $field_index ); ?>][required]" value="0"/><input type="checkbox" name="vava_questionnaires[<?php echo esc_attr( $type ); ?>][groups][<?php echo esc_attr( $group_key ); ?>][fields][<?php echo esc_attr( (string) $field_index ); ?>][required]" value="1"<?php checked( ! empty( $field['required'] ) ); ?>/><?php echo esc_html( 'en' === $lang ? 'Required' : 'إجباري' ); ?></label><input type="hidden" name="vava_questionnaires[<?php echo esc_attr( $type ); ?>][groups][<?php echo esc_attr( $group_key ); ?>][fields][<?php echo esc_attr( (string) $field_index ); ?>][id]" value="<?php echo esc_attr( (string) $field['id'] ); ?>"/><input type="hidden" name="vava_questionnaires[<?php echo esc_attr( $type ); ?>][groups][<?php echo esc_attr( $group_key ); ?>][fields][<?php echo esc_attr( (string) $field_index ); ?>][type]" value="<?php echo esc_attr( (string) $field['type'] ); ?>"/><input name="vava_questionnaires[<?php echo esc_attr( $type ); ?>][groups][<?php echo esc_attr( $group_key ); ?>][fields][<?php echo esc_attr( (string) $field_index ); ?>][label][<?php echo esc_attr( $lang ); ?>]" value="<?php echo esc_attr( (string) $field['label'][ $lang ] ); ?>"/><?php if ( ! empty( $field['options'] ) ) : ?><div class="vava-questionnaire-option-admin-list"><small><?php echo esc_html( 'en' === $lang ? 'Answer options' : 'خيارات الإجابة' ); ?></small><?php foreach ( (array) $field['options'] as $option_index => $option ) : ?><label><input type="hidden" name="vava_questionnaires[<?php echo esc_attr( $type ); ?>][groups][<?php echo esc_attr( $group_key ); ?>][fields][<?php echo esc_attr( (string) $field_index ); ?>][options][<?php echo esc_attr( (string) $option_index ); ?>][value]" value="<?php echo esc_attr( (string) ( $option['value'] ?? '' ) ); ?>"/><input name="vava_questionnaires[<?php echo esc_attr( $type ); ?>][groups][<?php echo esc_attr( $group_key ); ?>][fields][<?php echo esc_attr( (string) $field_index ); ?>][options][<?php echo esc_attr( (string) $option_index ); ?>][<?php echo esc_attr( $lang ); ?>]" value="<?php echo esc_attr( (string) ( $option[ $lang ] ?? '' ) ); ?>"/></label><?php endforeach; ?></div><?php endif; ?></div>
														<?php endforeach; ?>
													</div>
												</div>
											</article>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

function vava_booking_questionnaire_recursive_sanitize( $value ) {
	if ( is_array( $value ) ) { $clean = array(); foreach ( $value as $key => $item ) { $clean[ sanitize_key( (string) $key ) ] = vava_booking_questionnaire_recursive_sanitize( $item ); } return $clean; }
	return sanitize_textarea_field( (string) $value );
}

function vava_booking_questionnaire_save_settings( int $post_id, WP_Post $post ): void {
	if ( 'page' !== $post->post_type || ! function_exists( 'vava_booking_is_page' ) || ! vava_booking_is_page( $post_id ) || ! isset( $_POST['vava_booking_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vava_booking_nonce'] ) ), 'vava_booking_save' ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
	if ( ! isset( $_POST['vava_questionnaires'] ) || ! is_array( $_POST['vava_questionnaires'] ) ) { return; }
	$raw = wp_unslash( $_POST['vava_questionnaires'] );
	$defaults = vava_booking_questionnaire_defaults();
	$clean = array();
	foreach ( $defaults as $type => $questionnaire ) {
		$incoming = isset( $raw[ $type ] ) && is_array( $raw[ $type ] ) ? $raw[ $type ] : array();
		$clean[ $type ] = array_replace_recursive( $questionnaire, vava_booking_questionnaire_recursive_sanitize( $incoming ) );
		$clean[ $type ]['enabled'] = ! empty( $incoming['enabled'] ) ? 1 : 0;
		foreach ( (array) $questionnaire['groups'] as $group_key => $group ) {
			foreach ( (array) $group['fields'] as $index => $field ) {
				$clean[ $type ]['groups'][ $group_key ]['fields'][ $index ]['id'] = $field['id'];
				$clean[ $type ]['groups'][ $group_key ]['fields'][ $index ]['type'] = $field['type'];
				$clean[ $type ]['groups'][ $group_key ]['fields'][ $index ]['required'] = ! empty( $incoming['groups'][ $group_key ]['fields'][ $index ]['required'] ) ? 1 : 0;
				$clean_options = array();
				foreach ( (array) ( $field['options'] ?? array() ) as $option_index => $default_option ) {
					$incoming_option = (array) ( $incoming['groups'][ $group_key ]['fields'][ $index ]['options'][ $option_index ] ?? array() );
					$clean_options[] = array(
						'value' => (string) ( $default_option['value'] ?? '' ),
						'ar' => sanitize_text_field( (string) ( $incoming_option['ar'] ?? $default_option['ar'] ?? '' ) ),
						'en' => sanitize_text_field( (string) ( $incoming_option['en'] ?? $default_option['en'] ?? '' ) ),
					);
				}
				$clean[ $type ]['groups'][ $group_key ]['fields'][ $index ]['options'] = $clean_options;
			}
		}
	}
	update_post_meta( $post_id, '_vava_booking_questionnaires', $clean );
}
add_action( 'save_post_page', 'vava_booking_questionnaire_save_settings', 35, 2 );

function vava_booking_questionnaire_booking_data( int $booking_id ): array {
	$data = get_post_meta( $booking_id, '_vava_booking_questionnaire', true );
	return is_array( $data ) ? $data : array();
}

function vava_booking_questionnaire_impact_data( int $booking_id ): array {
	$data = get_post_meta( $booking_id, '_vava_booking_impact_questionnaire', true );
	return is_array( $data ) ? $data : array();
}

function vava_booking_questionnaire_all_booking_data( int $booking_id ): array {
	$list = array();
	$booking_data = vava_booking_questionnaire_booking_data( $booking_id );
	$impact_data = vava_booking_questionnaire_impact_data( $booking_id );
	if ( $booking_data ) { $list[] = $booking_data; }
	if ( $impact_data ) { $list[] = $impact_data; }
	return $list;
}

function vava_booking_questionnaire_render_answer_rows( array $data, string $lang = 'ar' ): string {
	$type = sanitize_key( (string) ( $data['type'] ?? '' ) );
	$questionnaire = (array) ( $data['snapshot'] ?? array() );
	if ( ! $questionnaire ) { $settings = vava_booking_questionnaire_settings(); $questionnaire = (array) ( $settings[ $type ] ?? array() ); }
	$answers = (array) ( $data['answers'] ?? array() );
	$map = vava_booking_questionnaire_field_map( $questionnaire );
	ob_start();
	foreach ( $answers as $id => $answer ) {
		$field = (array) ( $map[ $id ] ?? array() );
		$label = (string) ( $field['label'][ $lang ] ?? $field['label']['ar'] ?? $id );
		$value = $field ? vava_booking_questionnaire_option_label( $field, $answer, $lang ) : ( is_array( $answer ) ? implode( '، ', $answer ) : (string) $answer );
		if ( '' === trim( $value ) ) { continue; }
		?><tr><td style="padding:9px 10px;border-bottom:1px solid #ece7dc;color:#777168;font-size:12px;vertical-align:top;text-align:right;width:46%;"><?php echo esc_html( $label ); ?></td><td style="padding:9px 10px;border-bottom:1px solid #ece7dc;color:#45433d;font-size:12px;font-weight:600;vertical-align:top;text-align:right;"><?php echo nl2br( esc_html( $value ) ); ?></td></tr><?php
	}
	return (string) ob_get_clean();
}

function vava_booking_questionnaire_admin_email_html( int $booking_id, array $provided_data = array() ): string {
	$data = $provided_data ?: vava_booking_questionnaire_booking_data( $booking_id );
	$customer = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
	$service = (string) get_post_meta( $booking_id, '_vava_booking_service_title', true );
	$date = (string) get_post_meta( $booking_id, '_vava_booking_date', true );
	$time = function_exists( 'vava_booking_format_time_12h' ) ? vava_booking_format_time_12h( (string) get_post_meta( $booking_id, '_vava_booking_time', true ) ) : (string) get_post_meta( $booking_id, '_vava_booking_time', true );
	$method = function_exists( 'vava_booking_payment_method_label' ) ? vava_booking_payment_method_label( (string) get_post_meta( $booking_id, '_vava_booking_payment_method', true ) ) : '';
	$price = function_exists( 'vava_booking_format_price_label' ) ? vava_booking_format_price_label( (string) get_post_meta( $booking_id, '_vava_booking_service_price', true ), (string) get_post_meta( $booking_id, '_vava_booking_service_currency', true ), 'ar' ) : '';
	$duration = function_exists( 'vava_booking_display_duration_for_booking' ) ? vava_booking_display_duration_for_booking( $booking_id, 'ar' ) : absint( get_post_meta( $booking_id, '_vava_booking_duration', true ) ) . ' دقيقة';
	$title = (string) ( $data['title'] ?? '' );
	$rows = $data ? vava_booking_questionnaire_render_answer_rows( $data, 'ar' ) : '';
	ob_start(); ?>
<!doctype html><html dir="rtl" lang="ar"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0;background:#f4f0e8;font-family:Tahoma,Arial,sans-serif;color:#4e4b43;direction:rtl;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td align="center" style="padding:24px 10px;"><table role="presentation" width="820" style="width:100%;max-width:820px;background:#fffdf9;border:1px solid #ddd5c7;border-radius:18px;overflow:hidden;"><tr><td style="padding:24px 30px;text-align:center"><div style="font-family:Georgia,serif;font-size:38px;letter-spacing:4px;color:#777b54">VAVA</div><small style="letter-spacing:5px;color:#9a9388">LIVING</small></td></tr><tr><td style="padding:0 30px 20px"><div style="border:1px solid #ded7cb;border-radius:15px;padding:18px 20px"><h1 style="margin:0 0 6px;font-size:26px;color:#45433d">إشعار حجز جديد للإدارة</h1><p style="margin:0;color:#777168"><?php echo esc_html( $title ? 'تم استلام حجز جديد ويتضمن ' . $title . '.' : 'تم استلام حجز جديد.' ); ?></p></div></td></tr><tr><td style="padding:0 30px 20px"><table width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e1dbcf;border-radius:14px;overflow:hidden"><tr><td colspan="6" style="padding:14px 16px;font-size:18px;font-weight:bold;color:#555a3d">بيانات الحجز</td></tr><tr><?php foreach ( array( array( 'اسم العميل', (string) ( $customer['name'] ?? '' ) ), array( 'نوع الجلسة', $service ), array( 'المدة', $duration ), array( 'الموعد', trim( $date . ' — ' . $time ) ), array( 'طريقة الدفع', $method ), array( 'قيمة الحجز', $price ) ) as $cell ) : ?><td style="padding:14px 8px;border-top:1px solid #eee8df;text-align:center"><small style="display:block;color:#8d867b;margin-bottom:5px"><?php echo esc_html( $cell[0] ); ?></small><strong style="font-size:12px;color:#535046"><?php echo esc_html( $cell[1] ); ?></strong></td><?php endforeach; ?></tr></table></td></tr><?php if ( $data ) : ?><tr><td style="padding:0 30px 20px"><table width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e1dbcf;border-radius:14px;overflow:hidden"><tr><td colspan="2" style="padding:15px 16px;background:#f1eee5;font-size:18px;font-weight:bold;color:#555a3d"><?php echo esc_html( 'إجابات ' . $title ); ?></td></tr><?php echo $rows; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></table></td></tr><tr><td style="padding:16px 30px;background:#747a4e;color:#fff;text-align:center">تم تضمين جميع إجابات الاستبيان مع بيانات الحجز في إشعار الإدارة.</td></tr><?php endif; ?></table></td></tr></table></body></html>
	<?php return (string) ob_get_clean();
}

function vava_booking_questionnaire_render_admin_details( int $booking_id ): void {
	$datasets = vava_booking_questionnaire_all_booking_data( $booking_id );
	if ( ! $datasets ) { return; }
	foreach ( $datasets as $data ) {
		$title = (string) ( $data['title'] ?? 'الاستبيان' );
		$questionnaire = (array) ( $data['snapshot'] ?? array() );
		$map = vava_booking_questionnaire_field_map( $questionnaire );
		?>
		<section class="vava-booking-detail-card vava-booking-questionnaire-detail"><h3><?php echo esc_html( 'إجابات ' . $title ); ?></h3><p class="vava-booking-questionnaire-meta"><?php echo esc_html( 'اكتمل في: ' . (string) ( $data['completed_at'] ?? '—' ) ); ?></p><dl>
			<?php foreach ( (array) ( $data['answers'] ?? array() ) as $id => $answer ) : $field = (array) ( $map[ $id ] ?? array() ); $label = (string) ( $field['label']['ar'] ?? $id ); $value = $field ? vava_booking_questionnaire_option_label( $field, $answer, 'ar' ) : ( is_array( $answer ) ? implode( '، ', $answer ) : (string) $answer ); ?><div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo nl2br( esc_html( $value ) ); ?></dd></div><?php endforeach; ?>
		</dl></section>
		<?php
	}
}

function vava_booking_questionnaire_impact_eligible( int $booking_id ): bool {
	if ( 'vava_booking' !== get_post_type( $booking_id ) || 'completed' !== (string) get_post_meta( $booking_id, '_vava_booking_status', true ) ) { return false; }
	$settings = vava_booking_questionnaire_settings();
	return ! empty( $settings['impact']['enabled'] ) && ! vava_booking_questionnaire_impact_data( $booking_id );
}

function vava_booking_questionnaire_render_impact_form( int $booking_id, string $lang, string $legacy_token = '' ): void {
	if ( ! vava_booking_questionnaire_impact_eligible( $booking_id ) ) { return; }
	$settings = vava_booking_questionnaire_settings();
	$q = $settings['impact'];
	$customer = (array) get_post_meta( $booking_id, '_vava_booking_customer', true );
	$defaults = array( 'full_name' => (string) ( $customer['name'] ?? '' ) );
	?>
	<section id="vava-impact-questionnaire-<?php echo esc_attr( (string) $booking_id ); ?>" class="vava-impact-questionnaire" data-impact-questionnaire tabindex="-1">
		<header><span>★</span><div><small><?php echo esc_html( 'en' === $lang ? 'After completing your journey' : 'بعد اكتمال رحلتك' ); ?></small><h4><?php echo esc_html( (string) $q['title'][ $lang ] ); ?></h4><p><?php echo esc_html( (string) $q['description'][ $lang ] ); ?></p></div></header>
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post"><input type="hidden" name="action" value="vava_booking_submit_impact_questionnaire"/><input type="hidden" name="booking" value="<?php echo esc_attr( (string) $booking_id ); ?>"/><input type="hidden" name="lang" value="<?php echo esc_attr( $lang ); ?>"/><input type="hidden" name="magic_token" value="<?php echo esc_attr( $legacy_token ); ?>"/><?php wp_nonce_field( 'vava_booking_impact_' . $booking_id ); ?>
			<div class="vava-impact-questionnaire-groups"><?php $impact_group_index = 0; foreach ( (array) $q['groups'] as $group ) : $impact_group_index++; ?><details<?php echo 1 === $impact_group_index ? ' open' : ''; ?>><summary><?php echo esc_html( (string) $group['label'][ $lang ] ); ?></summary><div class="vava-questionnaire-fields"><?php foreach ( (array) $group['fields'] as $field ) { vava_booking_questionnaire_render_field( $field, $lang, $defaults ); } ?></div></details><?php endforeach; ?></div>
			<button type="submit"><?php echo esc_html( 'en' === $lang ? 'Submit journey impact questionnaire' : 'إرسال استبيان أثر الرحلة' ); ?></button>
		</form>
	</section>
	<?php
}

function vava_booking_submit_impact_questionnaire(): void {
	$booking_id = isset( $_POST['booking'] ) ? absint( $_POST['booking'] ) : 0;
	$lang = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$token = isset( $_POST['magic_token'] ) ? sanitize_text_field( wp_unslash( $_POST['magic_token'] ) ) : '';
	if ( ! $booking_id || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'vava_booking_impact_' . $booking_id ) || ( function_exists( 'vava_customer_can_access_booking' ) && ! vava_customer_can_access_booking( $booking_id, $token ) ) || ! vava_booking_questionnaire_impact_eligible( $booking_id ) ) { wp_die( esc_html( 'en' === $lang ? 'This questionnaire is unavailable.' : 'هذا الاستبيان غير متاح.' ), '', array( 'response' => 403 ) ); }
	$raw = isset( $_POST['questionnaire_answers'] ) && is_array( $_POST['questionnaire_answers'] ) ? $_POST['questionnaire_answers'] : array();
	$data = vava_booking_questionnaire_sanitize_answers( 'impact', $raw, $lang, true );
	if ( is_wp_error( $data ) ) { wp_die( esc_html( $data->get_error_message() ), '', array( 'response' => 422 ) ); }
	update_post_meta( $booking_id, '_vava_booking_impact_questionnaire', $data );
	$admin_email = sanitize_email( (string) get_option( 'admin_email' ) );
	if ( $admin_email ) { wp_mail( $admin_email, sprintf( 'VAVA — استبيان أثر الرحلة للحجز #%d', $booking_id ), vava_booking_questionnaire_admin_email_html( $booking_id, $data ), array( 'Content-Type: text/html; charset=UTF-8' ) ); }
	$url = function_exists( 'vava_customer_account_url' ) ? vava_customer_account_url( $lang, array( 'view' => 'bookings', 'impact_submitted' => 1 ) ) : home_url( '/' );
	if ( $token ) { $url = add_query_arg( 'vava_magic', rawurlencode( $token ), $url ); }
	wp_safe_redirect( $url . '#vava-booking-' . $booking_id );
	exit;
}
add_action( 'admin_post_vava_booking_submit_impact_questionnaire', 'vava_booking_submit_impact_questionnaire' );
add_action( 'admin_post_nopriv_vava_booking_submit_impact_questionnaire', 'vava_booking_submit_impact_questionnaire' );

/* VAVA_IMPACT_DIRECT_LINK_V1 */
