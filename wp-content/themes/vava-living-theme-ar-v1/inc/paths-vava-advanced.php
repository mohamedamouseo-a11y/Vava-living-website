<?php
/**
 * Advanced single-page Paths management and session detail pages.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

function vava_paths_advanced_sections( string $lang ): array {
	return 'en' === $lang ? array(
		'hero'       => 'Hero',
		'pathways'   => 'Main pathways',
		'sessions'   => 'Individual sessions',
		'questions'  => 'Frequently asked questions',
		'comparison' => 'Package comparison',
	) : array(
		'hero'       => 'الهيرو',
		'pathways'   => 'المسارات الأساسية',
		'sessions'   => 'جلسات الاستشارات',
		'questions'  => 'الأسئلة الشائعة',
		'comparison' => 'مقارنة الباقات',
	);
}

function vava_paths_adv_name( string $lang, array $path ): string {
	$name = 'vava_paths[' . ( 'en' === $lang ? 'en' : 'ar' ) . ']';
	foreach ( $path as $part ) { $name .= '[' . $part . ']'; }
	return $name;
}

function vava_paths_adv_value( array $data, array $path, $fallback = '' ) {
	return vava_paths_array_value( $data, $path, $fallback );
}

function vava_paths_adv_text( array $data, string $lang, array $path, string $label, bool $textarea = false, string $class = '' ): void {
	$value = vava_paths_adv_value( $data, $path, '' );
	$full  = $textarea ? ' vava-field-full' : '';
	?>
	<label class="vava-field<?php echo esc_attr( $full . ' ' . $class ); ?>"><span><?php echo esc_html( $label ); ?></span>
	<?php if ( $textarea ) : ?><textarea class="widefat vava-paths-field" name="<?php echo esc_attr( vava_paths_adv_name( $lang, $path ) ); ?>" rows="4"><?php echo esc_textarea( (string) $value ); ?></textarea>
	<?php else : ?><input class="widefat vava-paths-field" name="<?php echo esc_attr( vava_paths_adv_name( $lang, $path ) ); ?>" type="text" value="<?php echo esc_attr( (string) $value ); ?>"/><?php endif; ?>
	</label>
	<?php
}

function vava_paths_adv_hidden( string $lang, array $path, string $value ): void {
	?><input type="hidden" name="<?php echo esc_attr( vava_paths_adv_name( $lang, $path ) ); ?>" value="<?php echo esc_attr( $value ); ?>"/><?php
}

function vava_paths_adv_toggle( array $data, string $lang, array $path, string $label, string $help = '' ): void {
	$checked = ! empty( vava_paths_adv_value( $data, $path, false ) );
	$name    = vava_paths_adv_name( $lang, $path );
	?>
	<label class="vava-advanced-toggle"><input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="0"/><input class="vava-paths-field" type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $checked ); ?>/><span class="vava-advanced-toggle-ui"></span><b><?php echo esc_html( $label ); ?></b><?php if ( $help ) : ?><small><?php echo esc_html( $help ); ?></small><?php endif; ?></label>
	<?php
}

function vava_paths_adv_select( array $data, string $lang, array $path, string $label, array $options ): void {
	$value = (string) vava_paths_adv_value( $data, $path, '' );
	?><label class="vava-field"><span><?php echo esc_html( $label ); ?></span><select class="widefat vava-paths-field" name="<?php echo esc_attr( vava_paths_adv_name( $lang, $path ) ); ?>"><?php foreach ( $options as $key => $text ) : ?><option value="<?php echo esc_attr( (string) $key ); ?>" <?php selected( $value, (string) $key ); ?>><?php echo esc_html( $text ); ?></option><?php endforeach; ?></select></label><?php
}

function vava_paths_adv_media( array $data, string $lang, array $path, string $label ): void {
	$id  = absint( vava_paths_adv_value( $data, $path, 0 ) );
	$url = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	?>
	<div class="vava-field vava-field-full vava-session-media" data-vava-session-media><span><?php echo esc_html( $label ); ?></span>
		<input type="hidden" class="vava-paths-field" data-session-media-id name="<?php echo esc_attr( vava_paths_adv_name( $lang, $path ) ); ?>" value="<?php echo esc_attr( (string) $id ); ?>"/>
		<div class="vava-session-media-preview"><?php if ( $url ) : ?><img src="<?php echo esc_url( $url ); ?>" alt=""/><?php else : ?><em><?php echo esc_html( 'en' === $lang ? 'No image selected' : 'لم يتم اختيار صورة' ); ?></em><?php endif; ?></div>
		<div class="vava-session-media-actions"><button type="button" class="button" data-session-media-select><?php echo esc_html( 'en' === $lang ? 'Choose image' : 'اختيار صورة' ); ?></button><button type="button" class="button-link-delete" data-session-media-remove><?php echo esc_html( 'en' === $lang ? 'Remove' : 'إزالة' ); ?></button></div>
	</div>
	<?php
}

function vava_paths_adv_delete_button( string $attr, string $lang ): void {
	$label = 'en' === $lang ? 'Delete' : 'حذف';
	?><button type="button" class="vava-guide-delete" <?php echo esc_attr( $attr ); ?> title="<?php echo esc_attr( $label ); ?>" aria-label="<?php echo esc_attr( $label ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5m4-5v5"/></svg><span class="screen-reader-text"><?php echo esc_html( $label ); ?></span></button><?php
}

function vava_paths_adv_builder_heading( string $lang, string $title_ar, string $title_en, string $help_ar, string $help_en, string $button_attr = '', string $button_ar = '', string $button_en = '' ): void {
	$title = 'en' === $lang ? $title_en : $title_ar;
	$help  = 'en' === $lang ? $help_en : $help_ar;
	?>
	<div class="vava-guide-builder-heading">
		<div><h3><?php echo esc_html( $title ); ?></h3><p><?php echo esc_html( $help ); ?></p></div>
		<?php if ( $button_attr ) : ?><button type="button" class="button vava-guide-add-button" <?php echo esc_attr( $button_attr ); ?>><span aria-hidden="true">＋</span><?php echo esc_html( 'en' === $lang ? $button_en : $button_ar ); ?></button><?php endif; ?>
	</div>
	<?php
}

function vava_paths_adv_page_select( array $data, string $lang, array $path, string $label ): void {
	$value = absint( vava_paths_adv_value( $data, $path, 0 ) );
	$pages = get_pages( array( 'sort_column' => 'post_title', 'sort_order' => 'ASC' ) );
	?><label class="vava-field"><span><?php echo esc_html( $label ); ?></span><select class="widefat vava-paths-field" name="<?php echo esc_attr( vava_paths_adv_name( $lang, $path ) ); ?>"><option value="0"><?php echo esc_html( 'en' === $lang ? 'Choose a page' : 'اختر صفحة' ); ?></option><?php foreach ( $pages as $page ) : ?><option value="<?php echo esc_attr( (string) $page->ID ); ?>" <?php selected( $value, (int) $page->ID ); ?>><?php echo esc_html( get_the_title( $page ) ); ?></option><?php endforeach; ?></select></label><?php
}

function vava_paths_adv_faq_repeater( array $items, string $lang ): void {
	$items = array_values( $items );
	?>
	<div class="vava-guide-nested-builder vava-booking-faq-builder" data-vava-repeater data-repeater-base="faq.items" data-no-sort="1">
		<?php vava_paths_adv_builder_heading( $lang, 'أسئلة المرحلة الثانية', 'Stage-two questions', 'كل سؤال وإجابة يظهران أسفل أقسام الجلسات في المرحلة الثانية بنظام الأسئلة الشائعة.', 'Each question and answer appears below the session categories in stage two as an FAQ accordion.', 'data-repeater-add', 'إضافة سؤال', 'Add question' ); ?>
		<div class="vava-guide-nested-list" data-repeater-items>
		<?php foreach ( $items as $index => $item ) : $base = array( 'faq', 'items', $index ); ?>
			<article class="vava-guide-subcard vava-faq-admin-card<?php echo 0 === $index ? ' is-open' : ''; ?>" data-repeater-row data-vava-subaccordion>
				<header><button type="button" class="vava-guide-subcard-toggle" data-subaccordion-toggle aria-expanded="<?php echo 0 === $index ? 'true' : 'false'; ?>"><strong><?php echo esc_html( (string) ( $item['question'] ?? '' ) ); ?></strong><span class="vava-advanced-chevron" aria-hidden="true"></span></button><?php vava_paths_adv_delete_button( 'data-repeater-remove', $lang ); ?></header>
				<div class="vava-guide-subcard-body"><label class="vava-field"><span><?php echo esc_html( 'en' === $lang ? 'Question' : 'السؤال' ); ?></span><input class="widefat vava-paths-field" data-field-key="question" name="<?php echo esc_attr( vava_paths_adv_name( $lang, array_merge( $base, array( 'question' ) ) ) ); ?>" type="text" value="<?php echo esc_attr( (string) ( $item['question'] ?? '' ) ); ?>"/></label><label class="vava-field vava-field-full"><span><?php echo esc_html( 'en' === $lang ? 'Answer' : 'الإجابة' ); ?></span><textarea class="widefat vava-paths-field" data-field-key="answer" name="<?php echo esc_attr( vava_paths_adv_name( $lang, array_merge( $base, array( 'answer' ) ) ) ); ?>" rows="4"><?php echo esc_textarea( (string) ( $item['answer'] ?? '' ) ); ?></textarea></label></div>
			</article>
		<?php endforeach; ?>
		</div>
		<template data-repeater-template><article class="vava-guide-subcard vava-faq-admin-card is-open" data-repeater-row data-vava-subaccordion><header><button type="button" class="vava-guide-subcard-toggle" data-subaccordion-toggle aria-expanded="true"><strong><?php echo esc_html( 'en' === $lang ? 'New question' : 'سؤال جديد' ); ?></strong><span class="vava-advanced-chevron" aria-hidden="true"></span></button><?php vava_paths_adv_delete_button( 'data-repeater-remove', $lang ); ?></header><div class="vava-guide-subcard-body"><label class="vava-field"><span><?php echo esc_html( 'en' === $lang ? 'Question' : 'السؤال' ); ?></span><input class="widefat vava-paths-field" data-field-key="question" type="text"/></label><label class="vava-field vava-field-full"><span><?php echo esc_html( 'en' === $lang ? 'Answer' : 'الإجابة' ); ?></span><textarea class="widefat vava-paths-field" data-field-key="answer" rows="4"></textarea></label></div></article></template>
	</div>
	<?php
}

function vava_paths_adv_comparison_features( array $items, string $lang, array $base ): void {
	$items = array_values( $items );
	?>
	<div class="vava-guide-nested-builder vava-comparison-feature-builder" data-vava-repeater data-repeater-base="<?php echo esc_attr( implode( '.', $base ) ); ?>" data-no-sort="1">
		<?php vava_paths_adv_builder_heading( $lang, 'عناصر المقارنة', 'Comparison items', 'استخدم أيقونة الحالة لتحديد ما إذا كانت الميزة متوفرة ✓ أو غير متوفرة ×. يظل العنصر ظاهرًا في الحالتين.', 'Use the status control to mark the item as available ✓ or unavailable ×. The item remains visible in both states.', 'data-repeater-add', 'إضافة عنصر', 'Add item' ); ?>
		<div class="vava-guide-nested-list" data-repeater-items>
		<?php foreach ( $items as $index => $item ) : $path = array_merge( $base, array( $index ) ); $visible = ! isset( $item['visible'] ) || ! empty( $item['visible'] ); ?>
			<article class="vava-guide-subcard vava-comparison-feature-card<?php echo 0 === $index ? ' is-open' : ''; ?>" data-repeater-row data-vava-subaccordion>
			<header><button type="button" class="vava-guide-subcard-toggle" data-subaccordion-toggle aria-expanded="<?php echo 0 === $index ? 'true' : 'false'; ?>"><strong><?php echo esc_html( (string) ( $item['text'] ?? '' ) ); ?></strong><span class="vava-advanced-chevron" aria-hidden="true"></span></button><label class="vava-eye-toggle" title="<?php echo esc_attr( 'en' === $lang ? 'Available ✓ / unavailable ×' : 'متوفر ✓ / غير متوفر ×' ); ?>"><input type="hidden" data-field-key="visible" name="<?php echo esc_attr( vava_paths_adv_name( $lang, array_merge( $path, array( 'visible' ) ) ) ); ?>" value="0"/><input class="vava-paths-field" data-field-key="visible" type="checkbox" name="<?php echo esc_attr( vava_paths_adv_name( $lang, array_merge( $path, array( 'visible' ) ) ) ); ?>" value="1" <?php checked( $visible ); ?>/><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.6"/></svg></label><?php vava_paths_adv_delete_button( 'data-repeater-remove', $lang ); ?></header>
			<div class="vava-guide-subcard-body"><input type="hidden" data-field-key="enabled" name="<?php echo esc_attr( vava_paths_adv_name( $lang, array_merge( $path, array( 'enabled' ) ) ) ); ?>" value="1"/><label class="vava-field"><span><?php echo esc_html( 'en' === $lang ? 'Item' : 'العنصر' ); ?></span><input class="widefat vava-paths-field" data-field-key="text" name="<?php echo esc_attr( vava_paths_adv_name( $lang, array_merge( $path, array( 'text' ) ) ) ); ?>" type="text" value="<?php echo esc_attr( (string) ( $item['text'] ?? '' ) ); ?>"/></label><label class="vava-field"><span><?php echo esc_html( 'en' === $lang ? 'Optional value' : 'قيمة اختيارية' ); ?></span><input class="widefat vava-paths-field" data-field-key="value" name="<?php echo esc_attr( vava_paths_adv_name( $lang, array_merge( $path, array( 'value' ) ) ) ); ?>" type="text" value="<?php echo esc_attr( (string) ( $item['value'] ?? '' ) ); ?>"/></label></div>
			</article>
		<?php endforeach; ?>
		</div>
		<template data-repeater-template><article class="vava-guide-subcard vava-comparison-feature-card is-open" data-repeater-row data-vava-subaccordion><header><button type="button" class="vava-guide-subcard-toggle" data-subaccordion-toggle aria-expanded="true"><strong><?php echo esc_html( 'en' === $lang ? 'New comparison item' : 'عنصر مقارنة جديد' ); ?></strong><span class="vava-advanced-chevron" aria-hidden="true"></span></button><label class="vava-eye-toggle"><input type="hidden" data-field-key="visible" value="0"/><input class="vava-paths-field" data-field-key="visible" type="checkbox" value="1" checked/><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.6"/></svg></label><?php vava_paths_adv_delete_button( 'data-repeater-remove', $lang ); ?></header><div class="vava-guide-subcard-body"><input type="hidden" data-field-key="enabled" value="1"/><label class="vava-field"><span><?php echo esc_html( 'en' === $lang ? 'Item' : 'العنصر' ); ?></span><input class="widefat vava-paths-field" data-field-key="text" type="text"/></label><label class="vava-field"><span><?php echo esc_html( 'en' === $lang ? 'Optional value' : 'قيمة اختيارية' ); ?></span><input class="widefat vava-paths-field" data-field-key="value" type="text"/></label></div></article></template>
	</div>
	<?php
}

function vava_paths_adv_simple_repeater( array $items, string $lang, array $base, string $title, array $fields ): void {
	$items      = array_values( $items );
	$field_keys = array_keys( $fields );
	$is_compact = 1 === count( $field_keys );
	$title_key  = in_array( 'title', $field_keys, true ) ? 'title' : ( in_array( 'question', $field_keys, true ) ? 'question' : $field_keys[0] );
	$body_key   = in_array( 'description', $field_keys, true ) ? 'description' : ( in_array( 'answer', $field_keys, true ) ? 'answer' : '' );
	?>
	<div class="vava-advanced-repeater<?php echo $is_compact ? ' vava-compact-list-builder' : ' vava-content-accordion-builder'; ?>" data-vava-repeater data-repeater-base="<?php echo esc_attr( implode( '.', $base ) ); ?>" data-no-sort="1">
		<div class="vava-repeater-title"><div><h4><?php echo esc_html( $title ); ?></h4><p><?php echo esc_html( $is_compact ? ( 'en' === $lang ? 'Add concise items without unnecessary labels or ordering controls.' : 'أضف العناصر بشكل مختصر بدون عناوين أو تحكم غير ضروري في الترتيب.' ) : ( 'en' === $lang ? 'Open one item to edit its description. The list stays short and clear.' : 'افتح عنصرًا واحدًا لتعديل وصفه، وتظل القائمة مختصرة وواضحة.' ) ); ?></p></div><button type="button" class="button vava-guide-add-button" data-repeater-add><span aria-hidden="true">＋</span><?php echo esc_html( 'en' === $lang ? 'Add' : 'إضافة' ); ?></button></div>
		<div class="vava-repeater-items" data-repeater-items>
		<?php foreach ( $items as $index => $item ) : $path = array_merge( $base, array( $index ) ); ?>
			<?php if ( $is_compact ) : $key = $field_keys[0]; $name = vava_paths_adv_name( $lang, array_merge( $path, array( $key ) ) ); ?>
				<div class="vava-compact-list-row" data-repeater-row><input class="widefat vava-paths-field" data-field-key="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" type="text" value="<?php echo esc_attr( (string) ( $item[ $key ] ?? '' ) ); ?>" aria-label="<?php echo esc_attr( $title ); ?>"/><?php vava_paths_adv_delete_button( 'data-repeater-remove', $lang ); ?></div>
			<?php else : $open = 0 === $index; ?>
				<article class="vava-guide-subcard vava-content-accordion-item<?php echo $open ? ' is-open' : ''; ?>" data-repeater-row data-vava-subaccordion>
					<header><button type="button" class="vava-guide-subcard-toggle" data-subaccordion-toggle aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"><strong><?php echo esc_html( (string) ( $item[ $title_key ] ?? ( 'en' === $lang ? 'New item' : 'عنصر جديد' ) ) ); ?></strong><span class="vava-advanced-chevron" aria-hidden="true"></span></button><?php vava_paths_adv_delete_button( 'data-repeater-remove', $lang ); ?></header>
					<div class="vava-guide-subcard-body"><label class="vava-field"><span><?php echo esc_html( $fields[ $title_key ]['label'] ?? $title_key ); ?></span><input class="widefat vava-paths-field" data-field-key="<?php echo esc_attr( $title_key ); ?>" name="<?php echo esc_attr( vava_paths_adv_name( $lang, array_merge( $path, array( $title_key ) ) ) ); ?>" type="text" value="<?php echo esc_attr( (string) ( $item[ $title_key ] ?? '' ) ); ?>"/></label><?php if ( $body_key ) : ?><label class="vava-field vava-field-full"><span><?php echo esc_html( $fields[ $body_key ]['label'] ?? $body_key ); ?></span><textarea class="widefat vava-paths-field" data-field-key="<?php echo esc_attr( $body_key ); ?>" name="<?php echo esc_attr( vava_paths_adv_name( $lang, array_merge( $path, array( $body_key ) ) ) ); ?>" rows="4"><?php echo esc_textarea( (string) ( $item[ $body_key ] ?? '' ) ); ?></textarea></label><?php endif; ?></div>
				</article>
			<?php endif; ?>
		<?php endforeach; ?>
		</div>
		<template data-repeater-template>
		<?php if ( $is_compact ) : $key = $field_keys[0]; ?>
			<div class="vava-compact-list-row" data-repeater-row><input class="widefat vava-paths-field" data-field-key="<?php echo esc_attr( $key ); ?>" type="text" aria-label="<?php echo esc_attr( $title ); ?>"/><?php vava_paths_adv_delete_button( 'data-repeater-remove', $lang ); ?></div>
		<?php else : ?>
			<article class="vava-guide-subcard vava-content-accordion-item is-open" data-repeater-row data-vava-subaccordion><header><button type="button" class="vava-guide-subcard-toggle" data-subaccordion-toggle aria-expanded="true"><strong><?php echo esc_html( 'en' === $lang ? 'New item' : 'عنصر جديد' ); ?></strong><span class="vava-advanced-chevron" aria-hidden="true"></span></button><?php vava_paths_adv_delete_button( 'data-repeater-remove', $lang ); ?></header><div class="vava-guide-subcard-body"><label class="vava-field"><span><?php echo esc_html( $fields[ $title_key ]['label'] ?? $title_key ); ?></span><input class="widefat vava-paths-field" data-field-key="<?php echo esc_attr( $title_key ); ?>" type="text"/></label><?php if ( $body_key ) : ?><label class="vava-field vava-field-full"><span><?php echo esc_html( $fields[ $body_key ]['label'] ?? $body_key ); ?></span><textarea class="widefat vava-paths-field" data-field-key="<?php echo esc_attr( $body_key ); ?>" rows="4"></textarea></label><?php endif; ?></div></article>
		<?php endif; ?>
		</template>
	</div>
	<?php
}


/** Session detail basic-information editor matching the approved olive tile concept. */
function vava_paths_adv_session_basic_repeater( array $items, string $lang, array $base ): void {
	$items = array_values( array_filter( $items, static function ( $item ): bool {
		$item = is_array( $item ) ? $item : array();
		$key  = sanitize_key( (string) ( $item['key'] ?? '' ) );
		return 'duration' !== $key && 'duration' !== vava_paths_session_basic_key( (string) ( $item['label'] ?? '' ) );
	} ) );
	$options = array(
		'clock'    => 'en' === $lang ? 'Duration / clock' : 'مدة / ساعة',
		'person'   => 'en' === $lang ? 'Session type / person' : 'نوع الجلسة / شخص',
		'location' => 'en' === $lang ? 'Location' : 'المكان',
		'price'    => 'en' === $lang ? 'Price' : 'السعر',
		'calendar' => 'en' === $lang ? 'Calendar' : 'تقويم',
		'video'    => 'en' === $lang ? 'Online / video' : 'أونلاين / فيديو',
		'leaf'     => 'en' === $lang ? 'Leaf' : 'ورقة',
		'info'     => 'en' === $lang ? 'General information' : 'معلومة عامة',
	);
	?>
	<div class="vava-session-basic-builder vava-guide-nested-builder" data-vava-repeater data-session-basic-builder data-repeater-base="<?php echo esc_attr( implode( '.', $base ) ); ?>">
		<?php vava_paths_adv_builder_heading( $lang, 'المعلومات الأساسية', 'Basic information', 'أضف ورتّب المعلومات التي تظهر في صفحة الجلسة. هذه البطاقات هي مصدر نوع الجلسة والمكان والسعر، أما المدة فتُحدد تلقائيًا من تصنيف الجلسة.', 'Add and reorder the information shown on the session page. These tiles control session type, location and price; duration is assigned automatically by the session category.', 'data-repeater-add', 'إضافة معلومة', 'Add information' ); ?>
		<div class="vava-session-basic-list" data-repeater-items>
		<?php foreach ( $items as $index => $item ) :
			$path  = array_merge( $base, array( $index ) );
			$label = (string) ( $item['label'] ?? '' );
			$key   = sanitize_key( (string) ( $item['key'] ?? '' ) );
			if ( ! $key || 'custom' === $key ) { $key = vava_paths_session_basic_key( $label ); }
			$icon  = vava_paths_session_basic_icon( $label, (string) ( $item['icon'] ?? '' ) );
			?>
			<article class="vava-session-basic-card" data-repeater-row data-session-basic-row data-basic-key="<?php echo esc_attr( $key ); ?>">
				<span class="vava-repeater-handle" aria-label="<?php echo esc_attr( 'en' === $lang ? 'Drag to reorder' : 'اسحب لإعادة الترتيب' ); ?>"><i></i><i></i><i></i></span>
				<label class="vava-session-basic-icon-picker" title="<?php echo esc_attr( 'en' === $lang ? 'Choose icon' : 'اختيار الأيقونة' ); ?>">
					<span class="screen-reader-text"><?php echo esc_html( 'en' === $lang ? 'Icon' : 'الأيقونة' ); ?></span>
					<select class="vava-paths-field" data-field-key="icon" name="<?php echo esc_attr( vava_paths_adv_name( $lang, array_merge( $path, array( 'icon' ) ) ) ); ?>"><?php foreach ( $options as $option_key => $option_label ) : ?><option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $icon, $option_key ); ?>><?php echo esc_html( $option_label ); ?></option><?php endforeach; ?></select>
					<span data-basic-icon-preview><?php echo vava_paths_session_basic_icon_svg( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</label>
				<div class="vava-session-basic-fields">
					<label class="vava-session-basic-title"><span><?php echo esc_html( 'en' === $lang ? 'Title' : 'العنوان' ); ?></span><input class="widefat vava-paths-field" data-field-key="label" name="<?php echo esc_attr( vava_paths_adv_name( $lang, array_merge( $path, array( 'label' ) ) ) ); ?>" type="text" value="<?php echo esc_attr( $label ); ?>"/></label>
					<label class="vava-session-basic-value"><span><?php echo esc_html( 'en' === $lang ? 'Value' : 'القيمة' ); ?></span><input class="widefat vava-paths-field" data-field-key="value" name="<?php echo esc_attr( vava_paths_adv_name( $lang, array_merge( $path, array( 'value' ) ) ) ); ?>" type="text" value="<?php echo esc_attr( (string) ( $item['value'] ?? '' ) ); ?>"/></label>
					<input type="hidden" data-field-key="key" name="<?php echo esc_attr( vava_paths_adv_name( $lang, array_merge( $path, array( 'key' ) ) ) ); ?>" value="<?php echo esc_attr( $key ); ?>"/>
				</div>
				<?php vava_paths_adv_delete_button( 'data-repeater-remove', $lang ); ?>
			</article>
		<?php endforeach; ?>
		</div>
		<template data-repeater-template>
			<article class="vava-session-basic-card" data-repeater-row data-session-basic-row data-basic-key="custom">
				<span class="vava-repeater-handle" aria-label="<?php echo esc_attr( 'en' === $lang ? 'Drag to reorder' : 'اسحب لإعادة الترتيب' ); ?>"><i></i><i></i><i></i></span>
				<label class="vava-session-basic-icon-picker" title="<?php echo esc_attr( 'en' === $lang ? 'Choose icon' : 'اختيار الأيقونة' ); ?>"><span class="screen-reader-text"><?php echo esc_html( 'en' === $lang ? 'Icon' : 'الأيقونة' ); ?></span><select class="vava-paths-field" data-field-key="icon"><?php foreach ( $options as $option_key => $option_label ) : ?><option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( 'info', $option_key ); ?>><?php echo esc_html( $option_label ); ?></option><?php endforeach; ?></select><span data-basic-icon-preview><?php echo vava_paths_session_basic_icon_svg( 'info' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></label>
				<div class="vava-session-basic-fields"><label class="vava-session-basic-title"><span><?php echo esc_html( 'en' === $lang ? 'Title' : 'العنوان' ); ?></span><input class="widefat vava-paths-field" data-field-key="label" type="text"/></label><label class="vava-session-basic-value"><span><?php echo esc_html( 'en' === $lang ? 'Value' : 'القيمة' ); ?></span><input class="widefat vava-paths-field" data-field-key="value" type="text"/></label><input type="hidden" data-field-key="key" value="custom"/></div>
				<?php vava_paths_adv_delete_button( 'data-repeater-remove', $lang ); ?>
			</article>
		</template>
	</div>
	<?php
}

function vava_paths_adv_pathway_card( array $data, string $lang, int $index, bool $open = false ): void {
	$base = array( 'pathways', $index );
	$item = (array) vava_paths_adv_value( $data, $base, array() );
	$status_label = 'coming' === (string) ( $item['status'] ?? '' ) ? ( 'en' === $lang ? 'Coming soon' : 'قريبًا' ) : ( 'en' === $lang ? 'Active' : 'متاح' );
	?>
	<article class="vava-advanced-card vava-guide-card vava-pathway-admin-card<?php echo $open ? ' is-open' : ''; ?>" data-pathway-card data-vava-accordion>
		<header class="vava-guide-card-head"><span class="vava-repeater-handle" aria-label="<?php echo esc_attr( 'en' === $lang ? 'Drag to reorder' : 'اسحب لإعادة الترتيب' ); ?>"><i></i><i></i><i></i></span><button type="button" class="vava-advanced-accordion-toggle vava-guide-toggle" data-advanced-accordion-toggle aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"><span class="vava-guide-summary"><b><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></b><small><?php echo esc_html( $status_label ); ?><?php echo ! empty( $item['featured'] ) ? ' · ' . esc_html( 'en' === $lang ? 'Highlighted' : 'مميز' ) : ''; ?></small></span><span class="vava-advanced-chevron" aria-hidden="true">⌄</span></button><div class="vava-guide-actions"><?php vava_paths_adv_toggle( $data, $lang, array_merge( $base, array( 'enabled' ) ), 'en' === $lang ? 'Enabled' : 'مفعّل' ); ?></div></header>
		<?php vava_paths_adv_hidden( $lang, array_merge( $base, array( 'uid' ) ), (string) ( $item['uid'] ?? 'path-' . ( $index + 1 ) ) ); ?>
		<div class="vava-advanced-accordion-body vava-guide-card-body"><div class="vava-fields-grid">
		<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'title' ) ), 'en' === $lang ? 'Title' : 'العنوان' ); ?>
		<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'badge' ) ), 'en' === $lang ? 'Badge' : 'الشارة' ); ?>
		<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'description' ) ), 'en' === $lang ? 'Description' : 'الوصف', true ); ?>
		<?php vava_paths_adv_media( $data, $lang, array_merge( $base, array( 'image_id' ) ), 'en' === $lang ? 'Pathway image' : 'صورة المسار' ); ?>
		<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'button_text' ) ), 'en' === $lang ? 'Button text' : 'نص الزر' ); ?>
		<?php vava_paths_adv_select( $data, $lang, array_merge( $base, array( 'status' ) ), 'en' === $lang ? 'Status' : 'الحالة', array( 'active' => 'en' === $lang ? 'Active' : 'متاح', 'coming' => 'en' === $lang ? 'Coming soon' : 'قريبًا' ) ); ?>
		</div><div class="vava-advanced-footer-toggle"><?php vava_paths_adv_toggle( $data, $lang, array_merge( $base, array( 'featured' ) ), 'en' === $lang ? 'Highlight pathway' : 'تمييز المسار' ); ?></div></div>
	</article>
	<?php
}

function vava_paths_adv_session_card( array $data, string $lang, int $index, bool $open = false ): void {
	$base = array( 'packages', $index );
	$item = (array) vava_paths_adv_value( $data, $base, array() );
	$uid      = (string) ( $item['uid'] ?? 'session-' . ( $index + 1 ) );
	$category = vava_paths_session_category( $item );
	$category_labels = 'en' === $lang
		? array( 'quick' => 'Quick consultations', 'followup' => 'Follow-up sessions', 'comprehensive' => 'Comprehensive sessions' )
		: array( 'quick' => 'استشارات سريعة', 'followup' => 'جلسات متابعة', 'comprehensive' => 'جلسات شاملة' );
	$category_label = (string) ( $category_labels[ $category ] ?? $category_labels['comprehensive'] );
	?>
	<article class="vava-advanced-card vava-guide-card vava-session-editor<?php echo $open ? ' is-open' : ''; ?>" data-session-editor data-repeater-row data-vava-accordion data-session-index="<?php echo esc_attr( (string) $index ); ?>">
		<header class="vava-session-editor-head vava-guide-card-head"><span class="vava-repeater-handle" aria-label="<?php echo esc_attr( 'en' === $lang ? 'Drag to reorder' : 'اسحب لإعادة الترتيب' ); ?>"><i></i><i></i><i></i></span>
			<button type="button" class="vava-advanced-accordion-toggle vava-guide-toggle" data-advanced-accordion-toggle aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"><span class="vava-guide-summary"><b><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></b><small data-session-category-summary><?php echo esc_html( $category_label ); ?></small></span><span class="vava-advanced-chevron">⌄</span></button>
			<div class="vava-advanced-card-actions vava-guide-actions"><?php vava_paths_adv_toggle( $data, $lang, array_merge( $base, array( 'enabled' ) ), 'en' === $lang ? 'Enabled' : 'مفعّلة' ); ?><?php vava_paths_adv_delete_button( 'data-session-remove', $lang ); ?></div>
		</header>
		<?php vava_paths_adv_hidden( $lang, array_merge( $base, array( 'uid' ) ), $uid ); ?>
		<div class="vava-advanced-accordion-body vava-guide-card-body">
		<div class="vava-session-tabs" role="tablist"><button type="button" class="is-active" data-session-tab="card"><?php echo esc_html( 'en' === $lang ? 'Card' : 'البطاقة' ); ?></button><button type="button" data-session-tab="details"><?php echo esc_html( 'en' === $lang ? 'Details page' : 'صفحة التفاصيل' ); ?></button><button type="button" data-session-tab="journey"><?php echo esc_html( 'en' === $lang ? 'Session content' : 'محتوى الجلسة' ); ?></button><button type="button" data-session-tab="booking"><?php echo esc_html( 'en' === $lang ? 'Booking' : 'الحجز' ); ?></button></div>
		<div class="vava-session-tab-panel is-active" data-session-panel="card"><div class="vava-fields-grid">
		<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'title' ) ), 'en' === $lang ? 'Session title' : 'عنوان الجلسة' ); ?>
		<?php vava_paths_adv_select( $data, $lang, array_merge( $base, array( 'category' ) ), 'en' === $lang ? 'Session category' : 'تصنيف الجلسة', array( 'quick' => 'en' === $lang ? 'Quick consultations — 15–20 minutes' : 'استشارات سريعة — 15–20 دقيقة', 'followup' => 'en' === $lang ? 'Follow-up sessions — 30 minutes' : 'جلسات متابعة — 30 دقيقة', 'comprehensive' => 'en' === $lang ? 'Comprehensive sessions — 90 minutes' : 'جلسات شاملة — 90 دقيقة' ) ); ?>
		<?php vava_paths_adv_hidden( $lang, array_merge( $base, array( 'price' ) ), (string) ( $item['price'] ?? '' ) ); ?>
		<?php vava_paths_adv_hidden( $lang, array_merge( $base, array( 'currency' ) ), (string) ( $item['currency'] ?? '' ) ); ?>
		<?php vava_paths_adv_hidden( $lang, array_merge( $base, array( 'duration' ) ), (string) ( $item['duration'] ?? '' ) ); ?>
		<?php vava_paths_adv_hidden( $lang, array_merge( $base, array( 'session_type' ) ), (string) ( $item['session_type'] ?? '' ) ); ?>
		<?php vava_paths_adv_hidden( $lang, array_merge( $base, array( 'location' ) ), (string) ( $item['location'] ?? '' ) ); ?>
		<?php vava_paths_adv_hidden( $lang, array_merge( $base, array( 'basics_initialized' ) ), '1' ); ?>
		</div><div class="vava-advanced-footer-toggle"><?php vava_paths_adv_toggle( $data, $lang, array_merge( $base, array( 'featured' ) ), 'en' === $lang ? 'Featured session' : 'جلسة مميزة' ); ?></div><?php vava_paths_adv_session_basic_repeater( (array) ( $item['basics'] ?? array() ), $lang, array_merge( $base, array( 'basics' ) ) ); ?></div>
		<div class="vava-session-tab-panel" data-session-panel="details"><div class="vava-fields-grid">
		<?php vava_paths_adv_media( $data, $lang, array_merge( $base, array( 'image_id' ) ), 'en' === $lang ? 'Hero image' : 'الصورة الرئيسية' ); ?>
		<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'availability' ) ), 'en' === $lang ? 'Availability' : 'حالة الحجز' ); ?>
		<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'overview_title' ) ), 'en' === $lang ? 'Overview title' : 'عنوان وصف الجلسة' ); ?>
		<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'overview' ) ), 'en' === $lang ? 'Overview' : 'وصف الجلسة', true ); ?>
		</div></div>
		<div class="vava-session-tab-panel" data-session-panel="journey"><div class="vava-fields-grid">
		<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'audience_title' ) ), 'en' === $lang ? 'Suitable-for title' : 'عنوان مناسبة لك إذا' ); ?>
		<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'outcomes_title' ) ), 'en' === $lang ? 'What’s included title' : 'عنوان ماذا تشمل' ); ?>
		</div>
		<?php vava_paths_adv_simple_repeater( (array) ( $item['audience'] ?? array() ), $lang, array_merge( $base, array( 'audience' ) ), 'en' === $lang ? 'Suitable for you if' : 'مناسبة لك إذا', array( 'text' => array( 'label' => 'en' === $lang ? 'Item' : 'العنصر' ) ) ); ?>
		<?php vava_paths_adv_simple_repeater( (array) ( $item['outcomes'] ?? array() ), $lang, array_merge( $base, array( 'outcomes' ) ), 'en' === $lang ? 'What’s included' : 'ماذا تشمل', array( 'text' => array( 'label' => 'en' === $lang ? 'Item' : 'العنصر' ) ) ); ?>
</div>
		<div class="vava-session-tab-panel vava-session-booking-panel" data-session-panel="booking">
			<div class="vava-advanced-footer-toggle vava-booking-enabled-row"><?php vava_paths_adv_toggle( $data, $lang, array_merge( $base, array( 'booking_enabled' ) ), 'en' === $lang ? 'Accept bookings for this service' : 'السماح بالحجز لهذه الخدمة' ); ?></div>
			<div class="vava-field vava-field-full vava-auto-booking-note"><span><?php echo esc_html( 'en' === $lang ? 'Booking destination' : 'وجهة الحجز' ); ?></span><p><?php echo esc_html( 'en' === $lang ? 'The button is connected automatically to the new booking page using this session’s unique ID.' : 'الزر مرتبط تلقائيًا بصفحة الحجز الجديدة باستخدام المعرّف الفريد لهذه الجلسة.' ); ?></p></div>
			<div class="vava-fields-grid vava-booking-copy-grid">
			<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'booking_text' ) ), 'en' === $lang ? 'Booking button text' : 'نص زر الحجز', false, 'vava-field-full' ); ?>
			<?php vava_paths_adv_hidden( $lang, array_merge( $base, array( 'booking_url' ) ), (string) ( $item['booking_url'] ?? '' ) ); ?>
			<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'return_text' ) ), 'en' === $lang ? 'Back button text' : 'نص زر العودة', false, 'vava-field-full' ); ?>
			<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'link_text' ) ), 'en' === $lang ? 'Details button text' : 'نص زر التفاصيل', false, 'vava-field-full' ); ?>
			</div>
		</div>
		</div>
	</article>
	<?php
}

function vava_paths_adv_faq_section( array $data, string $lang ): void {
	$faq = (array) ( $data['faq'] ?? array() );
	?>
	<div class="vava-advanced-section-card vava-guide-builder-card">
		<?php vava_paths_adv_builder_heading( $lang, 'الأسئلة الشائعة', 'Frequently asked questions', 'أضف سؤالًا وإجابة فقط. تظهر هذه العناصر أسفل أقسام الجلسات في المرحلة الثانية ولا تؤثر على ترتيب الجلسات.', 'Add a question and answer only. These items appear below the session categories in stage two and never affect session ordering.' ); ?>
		<?php vava_paths_adv_faq_repeater( (array) ( $faq['items'] ?? array() ), $lang ); ?>
	</div>
	<?php
}

function vava_paths_adv_comparison_plan( array $data, string $lang, int $index, bool $open = false ): void {
	$base = array( 'compare', 'plans', $index );
	$item = (array) vava_paths_adv_value( $data, $base, array() );
	?>
	<article class="vava-advanced-card vava-guide-card vava-comparison-plan<?php echo $open ? ' is-open' : ''; ?>" data-comparison-plan data-repeater-row data-vava-accordion><header class="vava-guide-card-head"><span class="vava-repeater-handle"><i></i><i></i><i></i></span><button type="button" class="vava-advanced-accordion-toggle vava-guide-toggle" data-advanced-accordion-toggle aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"><span class="vava-guide-summary"><b><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></b><small><?php echo esc_html( (string) ( $item['price'] ?? '' ) ); ?></small></span><span class="vava-advanced-chevron">⌄</span></button><div class="vava-guide-actions"><?php vava_paths_adv_toggle( $data, $lang, array_merge( $base, array( 'enabled' ) ), 'en' === $lang ? 'Enabled' : 'مفعّلة' ); ?><?php vava_paths_adv_delete_button( 'data-comparison-remove', $lang ); ?></div></header><?php vava_paths_adv_hidden( $lang, array_merge( $base, array( 'uid' ) ), (string) ( $item['uid'] ?? 'plan-' . ( $index + 1 ) ) ); ?><div class="vava-advanced-accordion-body vava-guide-card-body"><div class="vava-fields-grid">
	<?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'badge' ) ), 'en' === $lang ? 'Badge' : 'الشارة' ); ?><?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'title' ) ), 'en' === $lang ? 'Title' : 'العنوان' ); ?><?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'description' ) ), 'en' === $lang ? 'Description' : 'الوصف', true ); ?><?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'core_label' ) ), 'en' === $lang ? 'Core section label' : 'عنوان العناصر الأساسية' ); ?><?php vava_paths_adv_text( $data, $lang, array_merge( $base, array( 'price' ) ), 'en' === $lang ? 'Price' : 'السعر' ); ?><?php vava_paths_adv_hidden( $lang, array_merge( $base, array( 'button_url' ) ), (string) ( $item['button_url'] ?? '' ) ); ?><?php vava_paths_adv_hidden( $lang, array_merge( $base, array( 'button_new_tab' ) ), '0' ); ?></div><p class="vava-booking-auto-note"><?php echo esc_html( 'en' === $lang ? 'The package button opens the new booking wizard automatically. The package UID is used as the stable link.' : 'زر الباقة يفتح صفحة الحجز الجديدة تلقائيًا باستخدام معرّف الباقة الثابت.' ); ?></p><div class="vava-advanced-footer-toggle"><?php vava_paths_adv_toggle( $data, $lang, array_merge( $base, array( 'featured' ) ), 'en' === $lang ? 'Featured plan' : 'الخطة المميزة' ); ?><?php vava_paths_adv_toggle( $data, $lang, array_merge( $base, array( 'booking_enabled' ) ), 'en' === $lang ? 'Accept bookings for this package' : 'السماح بالحجز لهذه الباقة' ); ?></div>
	<?php vava_paths_adv_comparison_features( (array) ( $item['features'] ?? array() ), $lang, array_merge( $base, array( 'features' ) ) ); ?></div>
	</article><?php
}

function vava_paths_render_advanced_settings( WP_Post $post ): void {
	wp_nonce_field( 'vava_paths_advanced_save', 'vava_paths_advanced_nonce' );
	$sections_ar = vava_paths_advanced_sections( 'ar' ); $sections_en = vava_paths_advanced_sections( 'en' );
	?>
	<div class="vava-homepage-admin vava-paths-admin vava-paths-advanced-admin" data-active-language="ar" data-active-section="hero" data-settings-title-ar="إعدادات صفحة مسارات VAVA" data-settings-title-en="VAVA Paths page settings"><input type="hidden" name="_vava_admin_active_language" value="ar" data-vava-active-language-input/><?php vava_paths_render_page_identity( $post ); ?>
	<div class="vava-admin-toolbar"><div class="vava-section-tabs" role="tablist"><?php foreach ( $sections_ar as $id => $label ) : ?><button aria-selected="<?php echo 'hero' === $id ? 'true' : 'false'; ?>" class="vava-section-tab<?php echo 'hero' === $id ? ' is-active' : ''; ?>" data-section="<?php echo esc_attr( $id ); ?>" type="button"><span class="vava-tab-icon"><?php echo vava_paths_section_icon( 'pathways' === $id ? 'future' : ( 'sessions' === $id ? 'packages' : ( 'questions' === $id ? 'faq' : $id ) ) ); // phpcs:ignore ?></span><span data-vava-i18n-ar="<?php echo esc_attr( $label ); ?>" data-vava-i18n-en="<?php echo esc_attr( $sections_en[ $id ] ); ?>"><?php echo esc_html( $label ); ?></span></button><?php endforeach; ?></div><div class="vava-toolbar-actions"><div class="vava-language-switch"><button class="is-active" data-language="ar" type="button"><span>العربية</span><small>AR</small></button><button data-language="en" type="button"><span>English</span><small>EN</small></button></div><button class="button vava-homepage-update-button" data-vava-submit type="button"><span data-vava-i18n-ar="تحديث" data-vava-i18n-en="Update">تحديث</span></button></div></div>
	<div class="vava-section-panels"><?php foreach ( array_keys( $sections_ar ) as $section ) : ?><section class="vava-section-panel<?php echo 'hero' === $section ? ' is-active' : ''; ?>" data-section-panel="<?php echo esc_attr( $section ); ?>"><?php foreach ( array( 'ar', 'en' ) as $lang ) : $data = vava_paths_data( (int) $post->ID, $lang ); $preview = array( 'pathways' => 'pathways', 'sessions' => 'packages', 'questions' => 'questions' )[ $section ] ?? $section; ?><div class="vava-language-pane<?php echo 'ar' === $lang ? ' is-active' : ''; ?>" data-language-pane="<?php echo esc_attr( $lang ); ?>" dir="<?php echo 'en' === $lang ? 'ltr' : 'rtl'; ?>"><div class="vava-editor-workspace"><?php vava_paths_render_preview( $preview, $data, $lang, (int) $post->ID ); ?><div class="vava-editor-controls">
	<?php if ( 'hero' === $section ) : ?><div class="vava-advanced-section-card vava-hero-copy-card"><div class="vava-advanced-section-heading"><div><b><?php echo esc_html( 'en' === $lang ? 'Hero content' : 'محتوى الهيرو' ); ?></b><p><?php echo esc_html( 'en' === $lang ? 'Write the three paragraphs in one multiline field, separated by blank lines.' : 'اكتب الفقرات الثلاث داخل حقل واحد، وافصل بينها بسطر فارغ.' ); ?></p></div></div><div class="vava-fields-grid"><?php vava_paths_adv_text( $data, $lang, array( 'hero', 'eyebrow' ), 'en' === $lang ? 'Small text' : 'النص الصغير' ); ?><?php vava_paths_adv_text( $data, $lang, array( 'hero', 'title' ), 'en' === $lang ? 'Title' : 'العنوان' ); ?><?php vava_paths_adv_text( $data, $lang, array( 'hero', 'content' ), 'en' === $lang ? 'Hero body' : 'نص الهيرو', true, 'vava-hero-content-field' ); ?></div></div>
	<?php elseif ( 'pathways' === $section ) : ?><div class="vava-advanced-section-card vava-guide-builder-card"><?php vava_paths_adv_builder_heading( $lang, 'إدارة المسارات الثلاثة الأساسية', 'Manage the three main pathways', 'فعّل أو عطّل ورتّب وعدّل محتوى كل مسار.', 'Enable, disable, reorder and edit each pathway.' ); ?><div class="vava-pathway-admin-grid vava-guide-builder-list" data-pathway-sort><?php for ( $i = 0; $i < 3; $i++ ) { vava_paths_adv_pathway_card( $data, $lang, $i, 0 === $i ); } ?></div></div>
	<?php elseif ( 'sessions' === $section ) : ?><div class="vava-advanced-section-card vava-guide-builder-card"><?php vava_paths_adv_builder_heading( $lang, 'الجلسات وصفحات تفاصيلها', 'Sessions and detail pages', 'أضف الجلسات ورتّبها وعدّل بطاقتها وصفحة تفاصيلها والحجز.', 'Add, reorder and edit session cards, detail pages and booking settings.', 'data-session-add', 'إضافة جلسة', 'Add session' ); ?><div class="vava-session-editors vava-guide-builder-list" data-session-list><?php foreach ( array_values( (array) ( $data['packages'] ?? array() ) ) as $i => $unused ) { vava_paths_adv_session_card( $data, $lang, $i, 0 === $i ); } ?></div><template data-session-template><article class="vava-advanced-card vava-guide-card vava-session-editor" data-session-editor data-repeater-row data-new-session><header class="vava-session-editor-head vava-guide-card-head"><span class="vava-repeater-handle" aria-label="<?php echo esc_attr( 'en' === $lang ? 'Drag to reorder' : 'اسحب لإعادة الترتيب' ); ?>"><i></i><i></i><i></i></span><b><?php echo esc_html( 'en' === $lang ? 'New session' : 'جلسة جديدة' ); ?></b><?php vava_paths_adv_delete_button( 'data-session-remove', $lang ); ?></header><div class="vava-fields-grid"><label class="vava-field"><span><?php echo esc_html( 'en' === $lang ? 'Title' : 'العنوان' ); ?></span><input class="widefat vava-paths-field" data-session-field="title"/></label><label class="vava-field vava-field-full"><span><?php echo esc_html( 'en' === $lang ? 'Description' : 'الوصف' ); ?></span><textarea class="widefat vava-paths-field" data-session-field="description"></textarea></label></div></article></template></div>
	<?php elseif ( 'questions' === $section ) : vava_paths_adv_faq_section( $data, $lang ); ?>
	<?php else : ?>
		<div class="vava-advanced-section-card"><div class="vava-advanced-section-heading"><div><b><?php echo esc_html( 'en' === $lang ? 'Comparison heading' : 'عنوان المقارنة' ); ?></b><p><?php echo esc_html( 'en' === $lang ? 'Control the heading shown above the comparison package cards.' : 'تحكم في العنوان والوصف الظاهرين أعلى كروت مقارنة الباقات.' ); ?></p></div></div><div class="vava-fields-grid"><?php vava_paths_adv_text( $data, $lang, array( 'compare', 'title' ), 'en' === $lang ? 'Title' : 'العنوان' ); ?><?php vava_paths_adv_text( $data, $lang, array( 'compare', 'back_text' ), 'en' === $lang ? 'Back button text' : 'نص زر العودة' ); ?><?php vava_paths_adv_text( $data, $lang, array( 'compare', 'intro' ), 'en' === $lang ? 'Description' : 'الوصف', true ); ?></div></div>
		<div class="vava-advanced-section-card vava-guide-builder-card"><?php vava_paths_adv_builder_heading( $lang, 'باقات المقارنة', 'Comparison packages', 'أضف الباقات وعدّل السعر وعناصر المقارنة وأزرار الحجز.', 'Add packages and edit price, comparison items and booking buttons.', 'data-comparison-add', 'إضافة باقة', 'Add package' ); ?><div class="vava-comparison-editors vava-guide-builder-list" data-comparison-list><?php foreach ( array_values( (array) ( $data['compare']['plans'] ?? array() ) ) as $i => $unused ) { vava_paths_adv_comparison_plan( $data, $lang, $i, 0 === $i ); } ?></div></div>
		<?php $guidance_sessions = array( '' => ( 'en' === $lang ? 'Choose a session' : 'اختر جلسة' ) ); foreach ( array_values( (array) ( $data['packages'] ?? array() ) ) as $guidance_session ) { $guidance_uid = sanitize_key( (string) ( $guidance_session['uid'] ?? '' ) ); if ( $guidance_uid ) { $guidance_sessions[ $guidance_uid ] = (string) ( $guidance_session['title'] ?? $guidance_uid ); } } ?>
		<div class="vava-advanced-section-card vava-comparison-guidance-settings"><div class="vava-advanced-section-heading"><div><b><?php echo esc_html( 'en' === $lang ? 'Message below comparison packages' : 'الرسالة أسفل باقات المقارنة' ); ?></b><p><?php echo esc_html( 'en' === $lang ? 'Write the complete message and choose the session opened by its link. Use {session} to place the linked session title exactly where you want it.' : 'اكتب نص الرسالة بالكامل وحدد الجلسة التي يفتحها الرابط. استخدم {session} لوضع اسم الجلسة المرتبط في المكان المطلوب داخل الرسالة.' ); ?></p></div></div><div class="vava-fields-grid"><?php vava_paths_adv_text( $data, $lang, array( 'compare', 'guidance_html' ), 'en' === $lang ? 'Message text' : 'نص الرسالة', true ); ?><?php vava_paths_adv_select( $data, $lang, array( 'compare', 'guidance_session_uid' ), 'en' === $lang ? 'Linked session' : 'الجلسة المرتبطة بالرابط', $guidance_sessions ); ?></div></div>
	<?php endif; ?>
	</div></div></div><?php endforeach; ?><?php if ( 'hero' === $section ) : ?><div class="vava-shared-fields"><div class="vava-fields-grid"><?php vava_paths_render_media_field( $post ); ?></div></div><?php endif; ?></section><?php endforeach; ?></div></div>
	<?php
}

function vava_paths_advanced_replace_metabox( string $post_type, WP_Post $post ): void {
	if ( 'page' !== $post_type || ! vava_paths_is_page( (int) $post->ID ) ) { return; }
	remove_meta_box( 'vava_homepage_settings', 'page', 'normal' );
	add_meta_box( 'vava_homepage_settings', 'إعدادات صفحة مسارات VAVA', 'vava_paths_render_advanced_settings', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'vava_paths_advanced_replace_metabox', 100, 2 );

function vava_paths_recursive_sanitize( $value, string $key = '' ) {
	if ( is_array( $value ) ) {
		$result = array(); foreach ( $value as $k => $v ) { $result[ $k ] = vava_paths_recursive_sanitize( $v, (string) $k ); } return $result;
	}
	if ( is_bool( $value ) ) { return $value; }
	$value = is_scalar( $value ) ? (string) $value : '';
	if ( preg_match( '/(?:url|link)$/', $key ) ) { return esc_url_raw( $value ); }
	if ( in_array( $key, array( 'content','description','overview','intro','sidebar_intro','lead_1','lead_2','note','answer','best_text','whatsapp_message','guidance_html' ), true ) ) { return wp_kses_post( $value ); }
	return sanitize_text_field( $value );
}

/** Whether a stored session contains editor content rather than structure-only fields. */
function vava_paths_session_has_meaningful_content( array $session ): bool {
	foreach ( array(
		'title', 'badge', 'description', 'availability', 'overview_title', 'overview',
		'audience_title', 'outcomes_title',
		'booking_text', 'return_text', 'link_text', 'whatsapp_message',
	) as $key ) {
		if ( '' !== trim( wp_strip_all_tags( (string) ( $session[ $key ] ?? '' ) ) ) ) {
			return true;
		}
	}

	$has_nested_text = static function ( $value ) use ( &$has_nested_text ): bool {
		if ( is_array( $value ) ) {
			foreach ( $value as $nested ) {
				if ( $has_nested_text( $nested ) ) { return true; }
			}
			return false;
		}
		return is_scalar( $value ) && '' !== trim( wp_strip_all_tags( (string) $value ) );
	};
	foreach ( array( 'audience', 'outcomes' ) as $key ) {
		if ( $has_nested_text( $session[ $key ] ?? array() ) ) { return true; }
	}
	return false;
}

/** Stable cross-language repair signature for the existing VAVA sessions. */
function vava_paths_session_price_signature( array $session ): string {
	$candidates = array( (string) ( $session['price'] ?? '' ) );
	foreach ( array_values( (array) ( $session['basics'] ?? array() ) ) as $basic ) {
		$basic = (array) $basic;
		$key   = sanitize_key( (string) ( $basic['key'] ?? '' ) );
		if ( 'price' === $key || 'price' === vava_paths_session_basic_key( (string) ( $basic['label'] ?? '' ) ) ) {
			$candidates[] = (string) ( $basic['value'] ?? '' );
		}
	}
	foreach ( $candidates as $candidate ) {
		$latin = strtr( $candidate, array(
			'٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
			'۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
		) );
		if ( preg_match( '/[0-9][0-9,.\s]*/u', $latin, $match ) ) {
			$digits = preg_replace( '/[^0-9]/', '', (string) $match[0] );
			if ( '' !== $digits ) { return 'price:' . ltrim( $digits, '0' ); }
		}
	}
	return '';
}

/** Copy functional values from the authoritative language to its translation. */
function vava_paths_align_shared_item_values( array &$source, array &$translation, string $group ): void {
	$defaults = array(
		'enabled'         => 0,
		'featured'        => 0,
		'image_id'        => 0,
		'booking_enabled' => 1,
		'category'        => 'packages' === $group ? 'comprehensive' : 0,
	);
	foreach ( $defaults as $key => $default ) {
		$value = array_key_exists( $key, $source )
			? $source[ $key ]
			: ( array_key_exists( $key, $translation ) ? $translation[ $key ] : $default );
		$source[ $key ]      = $value;
		$translation[ $key ] = $value;
	}
}

/**
 * Align a bilingual repeater by its stable UID.
 *
 * The previous index/max-count implementation recreated a deleted row and
 * shifted every translation after it. The active language is authoritative:
 * missing UIDs are translations to create, and extra UIDs are deleted rows.
 */
function vava_paths_align_uid_group( array &$source_data, array &$translation_data, string $group ): void {
	$source      = array_values( (array) ( $source_data[ $group ] ?? array() ) );
	$translation = array_values( (array) ( $translation_data[ $group ] ?? array() ) );
	$translation_by_uid = array();
	foreach ( $translation as $index => $item ) {
		$item = (array) $item;
		$uid  = sanitize_key( (string) ( $item['uid'] ?? '' ) );
		if ( '' !== $uid ) { $translation_by_uid[ $uid ][] = $index; }
	}

	$used_translation = array();
	$aligned_source    = array();
	$aligned_target    = array();
	foreach ( $source as $item ) {
		$item = (array) $item;
		$uid  = sanitize_key( (string) ( $item['uid'] ?? '' ) );
		if ( '' === $uid ) { $uid = sanitize_key( wp_generate_uuid4() ); }

		$translated = array();
		if ( ! empty( $translation_by_uid[ $uid ] ) ) {
			// Keep the last duplicate when repairing data written by the old
			// max-count loop; it contains the non-shifted final translation.
			while ( $translation_by_uid[ $uid ] ) {
				$candidate = array_pop( $translation_by_uid[ $uid ] );
				if ( ! isset( $used_translation[ $candidate ] ) ) {
					$translated = (array) $translation[ $candidate ];
					$used_translation[ $candidate ] = true;
					break;
				}
			}
		}

		$item['uid']       = $uid;
		$translated['uid'] = $uid;
		vava_paths_align_shared_item_values( $item, $translated, $group );
		$aligned_source[] = $item;
		$aligned_target[] = $translated;
	}
	$source_data[ $group ]      = $aligned_source;
	$translation_data[ $group ] = $aligned_target;
}

/**
 * Repair the structure-only session created by the old bilingual save loop.
 *
 * Prices are used only for this one-time repair because they are language
 * independent and remained intact even after the old code shifted UIDs.
 * Returns the language that represented the user's deletion.
 */
function vava_paths_repair_empty_session_placeholders( array &$ar, array &$en ): string {
	$ar_sessions = array_map( static fn( $item ): array => (array) $item, array_values( (array) ( $ar['packages'] ?? array() ) ) );
	$en_sessions = array_map( static fn( $item ): array => (array) $item, array_values( (array) ( $en['packages'] ?? array() ) ) );
	$ar_clean     = array_values( array_filter( $ar_sessions, 'vava_paths_session_has_meaningful_content' ) );
	$en_clean     = array_values( array_filter( $en_sessions, 'vava_paths_session_has_meaningful_content' ) );
	$ar_removed   = count( $ar_sessions ) - count( $ar_clean );
	$en_removed   = count( $en_sessions ) - count( $en_clean );
	if ( 0 === $ar_removed && 0 === $en_removed ) { return ''; }

	$authoritative = $ar_removed > 0 ? 'ar' : 'en';
	$source        = 'en' === $authoritative ? $en_clean : $ar_clean;
	$translation   = 'en' === $authoritative ? $ar_clean : $en_clean;
	if ( ! $source && $translation ) {
		$authoritative = 'en' === $authoritative ? 'ar' : 'en';
		$source        = $translation;
		$translation   = array();
	}
	if ( ! $source ) { return ''; }

	$source_signature_count = array();
	$target_by_signature    = array();
	$target_by_uid          = array();
	foreach ( $source as $item ) {
		$signature = vava_paths_session_price_signature( (array) $item );
		if ( $signature ) { $source_signature_count[ $signature ] = ( $source_signature_count[ $signature ] ?? 0 ) + 1; }
	}
	foreach ( $translation as $index => $item ) {
		$item      = (array) $item;
		$signature = vava_paths_session_price_signature( $item );
		$uid       = sanitize_key( (string) ( $item['uid'] ?? '' ) );
		if ( $signature ) { $target_by_signature[ $signature ][] = $index; }
		if ( $uid ) { $target_by_uid[ $uid ][] = $index; }
	}

	$used    = array();
	$rebuilt = array();
	foreach ( array_values( $source ) as $source_index => &$item ) {
		$item      = (array) $item;
		$uid       = sanitize_key( (string) ( $item['uid'] ?? '' ) );
		$signature = vava_paths_session_price_signature( $item );
		$match     = null;

		if (
			$signature
			&& 1 === (int) ( $source_signature_count[ $signature ] ?? 0 )
			&& 1 === count( $target_by_signature[ $signature ] ?? array() )
		) {
			$candidate = (int) $target_by_signature[ $signature ][0];
			if ( ! isset( $used[ $candidate ] ) ) { $match = $candidate; }
		}
		if ( null === $match && $uid && ! empty( $target_by_uid[ $uid ] ) ) {
			foreach ( array_reverse( $target_by_uid[ $uid ] ) as $candidate ) {
				if ( ! isset( $used[ $candidate ] ) ) { $match = (int) $candidate; break; }
			}
		}
		if ( null === $match && isset( $translation[ $source_index ] ) && ! isset( $used[ $source_index ] ) ) {
			$match = $source_index;
		}
		if ( null === $match ) {
			foreach ( array_keys( $translation ) as $candidate ) {
				if ( ! isset( $used[ $candidate ] ) ) { $match = (int) $candidate; break; }
			}
		}

		$translated = null === $match ? array() : (array) $translation[ $match ];
		if ( null !== $match ) { $used[ $match ] = true; }
		if ( '' === $uid ) { $uid = sanitize_key( wp_generate_uuid4() ); }
		$item['uid']       = $uid;
		$translated['uid'] = $uid;
		vava_paths_align_shared_item_values( $item, $translated, 'packages' );
		$rebuilt[] = $translated;
	}
	unset( $item );

	if ( 'en' === $authoritative ) {
		$en['packages'] = array_values( $source );
		$ar['packages'] = $rebuilt;
	} else {
		$ar['packages'] = array_values( $source );
		$en['packages'] = $rebuilt;
	}
	return $authoritative;
}

function vava_paths_align_shared_structure( array &$ar, array &$en, string $authoritative_language = 'ar' ): void {
	$authoritative_language = 'en' === $authoritative_language ? 'en' : 'ar';
	if ( 'en' === $authoritative_language ) {
		vava_paths_align_uid_group( $en, $ar, 'pathways' );
		vava_paths_align_uid_group( $en, $ar, 'packages' );
	} else {
		vava_paths_align_uid_group( $ar, $en, 'pathways' );
		vava_paths_align_uid_group( $ar, $en, 'packages' );
	}


	$guidance_session_uid = sanitize_key( (string) ( $ar['compare']['guidance_session_uid'] ?? $en['compare']['guidance_session_uid'] ?? 'session-2' ) );
	$ar['compare']['guidance_session_uid'] = $guidance_session_uid;
	$en['compare']['guidance_session_uid'] = $guidance_session_uid;

	$ar['compare']['plans'] = array_values( (array) ( $ar['compare']['plans'] ?? array() ) );
	$en['compare']['plans'] = array_values( (array) ( $en['compare']['plans'] ?? array() ) );
	$count = max( count( $ar['compare']['plans'] ), count( $en['compare']['plans'] ) );
	for ( $i = 0; $i < $count; $i++ ) {
		$ar['compare']['plans'][ $i ] = (array) ( $ar['compare']['plans'][ $i ] ?? array() );
		$en['compare']['plans'][ $i ] = (array) ( $en['compare']['plans'][ $i ] ?? array() );
		$uid = sanitize_key( (string) ( $ar['compare']['plans'][ $i ]['uid'] ?? $en['compare']['plans'][ $i ]['uid'] ?? wp_generate_uuid4() ) );
		if ( '' === $uid ) { $uid = sanitize_key( wp_generate_uuid4() ); }
		$ar['compare']['plans'][ $i ]['uid'] = $uid;
		$en['compare']['plans'][ $i ]['uid'] = $uid;
		unset( $ar['compare']['plans'][ $i ]['duration'], $en['compare']['plans'][ $i ]['duration'] );
		foreach ( array( 'enabled', 'featured', 'booking_enabled', 'button_new_tab' ) as $shared ) {
			$default = 'booking_enabled' === $shared ? 1 : 0;
			$value = $ar['compare']['plans'][ $i ][ $shared ] ?? $en['compare']['plans'][ $i ][ $shared ] ?? $default;
			$ar['compare']['plans'][ $i ][ $shared ] = $value;
			$en['compare']['plans'][ $i ][ $shared ] = $value;
		}
		$ar_features = array_values( (array) ( $ar['compare']['plans'][ $i ]['features'] ?? array() ) );
		$en_features = array_values( (array) ( $en['compare']['plans'][ $i ]['features'] ?? array() ) );
		$feature_count = max( count( $ar_features ), count( $en_features ) );
		for ( $feature_index = 0; $feature_index < $feature_count; $feature_index++ ) {
			$ar_features[ $feature_index ] = (array) ( $ar_features[ $feature_index ] ?? array() );
			$en_features[ $feature_index ] = (array) ( $en_features[ $feature_index ] ?? array() );
			foreach ( array( 'visible' ) as $shared ) {
				$value = $ar_features[ $feature_index ][ $shared ] ?? $en_features[ $feature_index ][ $shared ] ?? 1;
				$ar_features[ $feature_index ][ $shared ] = $value;
				$en_features[ $feature_index ][ $shared ] = $value;
			}
		}
		$ar['compare']['plans'][ $i ]['features'] = $ar_features;
		$en['compare']['plans'][ $i ]['features'] = $en_features;
	}
}

function vava_paths_advanced_save( int $post_id, WP_Post $post ): void {
	if ( ! isset( $_POST['vava_paths_advanced_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vava_paths_advanced_nonce'] ) ), 'vava_paths_advanced_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_page', $post_id ) || ! vava_paths_is_page( $post_id ) ) { return; }
	vava_save_bilingual_page_titles( $post_id );
	$raw = isset( $_POST['vava_paths'] ) && is_array( $_POST['vava_paths'] ) ? wp_unslash( $_POST['vava_paths'] ) : array();
	$active_language = isset( $_POST['_vava_admin_active_language'] ) ? vava_normalize_language( sanitize_key( wp_unslash( $_POST['_vava_admin_active_language'] ) ) ) : 'ar';
	$ar = vava_paths_recursive_sanitize( (array) ( $raw['ar'] ?? array() ) ); $en = vava_paths_recursive_sanitize( (array) ( $raw['en'] ?? array() ) );
	$ar = array_replace( vava_paths_defaults( 'ar' ), $ar ); $en = array_replace( vava_paths_defaults( 'en' ), $en );
	$ar = vava_paths_normalize_session_categories( $ar, 'ar' );
	$en = vava_paths_normalize_session_categories( $en, 'en' );
	$ar = vava_paths_normalize_session_basic_data( $ar, 'ar' );
	$en = vava_paths_normalize_session_basic_data( $en, 'en' );
	$ar = vava_paths_normalize_comparison_data( $ar );
	$en = vava_paths_normalize_comparison_data( $en );
	vava_paths_align_shared_structure( $ar, $en, $active_language );
	// Category is shared, while its display label is localized; normalize again after alignment.
	$ar = vava_paths_normalize_session_categories( $ar, 'ar' );
	$en = vava_paths_normalize_session_categories( $en, 'en' );
	update_post_meta( $post_id, vava_paths_meta_key( 'ar' ), $ar ); update_post_meta( $post_id, vava_paths_meta_key( 'en' ), $en );
	if ( isset( $_POST['_vava_paths_hero_image_id'] ) ) { update_post_meta( $post_id, '_vava_paths_hero_image_id', absint( $_POST['_vava_paths_hero_image_id'] ) ); }
	vava_paths_sync_session_posts( $post_id, $ar, $en );
}

function vava_paths_disable_legacy_save(): void { remove_action( 'save_post_page', 'vava_paths_save_meta', 20 ); add_action( 'save_post_page', 'vava_paths_advanced_save', 20, 2 ); }
add_action( 'init', 'vava_paths_disable_legacy_save', 30 );

function vava_paths_register_session_type(): void {
	register_post_type( 'vava_session', array( 'labels' => array( 'name' => 'VAVA Sessions', 'singular_name' => 'VAVA Session' ), 'public' => true, 'show_ui' => false, 'has_archive' => false, 'rewrite' => array( 'slug' => 'vava-session', 'with_front' => false ), 'supports' => array( 'title' ), 'show_in_rest' => false ) );
}
add_action( 'init', 'vava_paths_register_session_type', 8 );

function vava_paths_maybe_flush_session_rewrites(): void {
	if ( get_option( 'vava_session_rewrite_version' ) === '1' ) { return; }
	vava_paths_register_session_type(); flush_rewrite_rules( false ); update_option( 'vava_session_rewrite_version', '1', false );
}
add_action( 'admin_init', 'vava_paths_maybe_flush_session_rewrites' );

function vava_paths_maybe_sync_existing_sessions(): void {
	if ( get_option( 'vava_paths_session_sync_version' ) === '1' ) { return; }
	$pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => 1, 'meta_key' => '_wp_page_template', 'meta_value' => vava_paths_template_slug() ) );
	if ( ! $pages ) { return; }
	$page_id = (int) $pages[0]->ID; $ar = vava_paths_data( $page_id, 'ar' ); $en = vava_paths_data( $page_id, 'en' ); vava_paths_align_shared_structure( $ar, $en ); vava_paths_sync_session_posts( $page_id, $ar, $en ); update_option( 'vava_paths_session_sync_version', '1', false );
}
add_action( 'admin_init', 'vava_paths_maybe_sync_existing_sessions', 40 );

/** Persist the category-based display and operational duration for existing sessions. */
function vava_paths_maybe_migrate_category_durations_v12250(): void {
	if ( '1.22.50' === (string) get_option( 'vava_paths_category_duration_version', '' ) ) { return; }
	if ( ! current_user_can( 'edit_pages' ) ) { return; }
	$page_ids = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_wp_page_template', 'meta_value' => vava_paths_template_slug(), 'no_found_rows' => true ) );
	foreach ( $page_ids as $page_id ) {
		$page_id = absint( $page_id );
		$ar = vava_paths_data( $page_id, 'ar' );
		$en = vava_paths_data( $page_id, 'en' );
		vava_paths_align_shared_structure( $ar, $en, 'ar' );
		$ar = vava_paths_normalize_session_categories( $ar, 'ar' );
		$en = vava_paths_normalize_session_categories( $en, 'en' );
		update_post_meta( $page_id, vava_paths_meta_key( 'ar' ), $ar );
		update_post_meta( $page_id, vava_paths_meta_key( 'en' ), $en );
		vava_paths_sync_session_posts( $page_id, $ar, $en );
	}
	update_option( 'vava_paths_category_duration_version', '1.22.50', false );
}
add_action( 'admin_init', 'vava_paths_maybe_migrate_category_durations_v12250', 42 );

/** Refresh stored session links after moving the site away from localhost. */
function vava_paths_refresh_session_links_after_migration(): void {
	if ( '2' === (string) get_option( 'vava_paths_session_link_refresh_version', '' ) ) {
		return;
	}

	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'numberposts'    => -1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => vava_paths_template_slug(),
			'suppress_filters' => false,
		)
	);

	foreach ( $pages as $page ) {
		$page_id = (int) $page->ID;
		$ar      = vava_paths_data( $page_id, 'ar' );
		$en      = vava_paths_data( $page_id, 'en' );
		vava_paths_align_shared_structure( $ar, $en );
		vava_paths_sync_session_posts( $page_id, $ar, $en );
	}

	vava_paths_register_session_type();
	flush_rewrite_rules( false );
	update_option( 'vava_paths_session_link_refresh_version', '2', false );
}
add_action( 'admin_init', 'vava_paths_refresh_session_links_after_migration', 45 );


function vava_paths_sync_session_posts( int $page_id, array &$ar, array &$en ): void {
	$seen = array();
	foreach ( array_values( (array) ( $ar['packages'] ?? array() ) ) as $i => $session ) {
		$uid = sanitize_key( (string) ( $session['uid'] ?? '' ) ); if ( ! $uid ) { continue; } $seen[] = $uid;
		$posts = get_posts( array( 'post_type' => 'vava_session', 'post_status' => 'any', 'numberposts' => 1, 'meta_query' => array( array( 'key' => '_vava_session_source_page', 'value' => $page_id ), array( 'key' => '_vava_session_uid', 'value' => $uid ) ) ) );
		$post_id = $posts ? (int) $posts[0]->ID : 0; $title = sanitize_text_field( (string) ( $session['title'] ?? 'VAVA Session' ) );
		$payload = array( 'post_type' => 'vava_session', 'post_status' => ! empty( $session['enabled'] ) ? 'publish' : 'draft', 'post_title' => $title, 'post_name' => sanitize_title( $title . '-' . substr( $uid, 0, 8 ) ) );
		if ( $post_id ) { $payload['ID'] = $post_id; $post_id = wp_update_post( $payload, true ); } else { $post_id = wp_insert_post( $payload, true ); }
		if ( is_wp_error( $post_id ) ) { continue; }
		update_post_meta( $post_id, '_vava_session_source_page', $page_id ); update_post_meta( $post_id, '_vava_session_uid', $uid );
		$url = get_permalink( $post_id ); if ( isset( $ar['packages'][ $i ] ) ) { $ar['packages'][ $i ]['link_url'] = $url; } if ( isset( $en['packages'][ $i ] ) ) { $en['packages'][ $i ]['link_url'] = $url; }
	}
	update_post_meta( $page_id, vava_paths_meta_key( 'ar' ), $ar ); update_post_meta( $page_id, vava_paths_meta_key( 'en' ), $en );
	$orphans = get_posts( array( 'post_type' => 'vava_session', 'post_status' => 'any', 'numberposts' => -1, 'meta_key' => '_vava_session_source_page', 'meta_value' => $page_id ) ); foreach ( $orphans as $orphan ) { $uid = (string) get_post_meta( $orphan->ID, '_vava_session_uid', true ); if ( $uid && ! in_array( $uid, $seen, true ) ) { wp_trash_post( $orphan->ID ); } }
}

/** One-time cleanup for the empty session row produced by releases before 1.18.0. */
function vava_paths_maybe_repair_empty_session_rows(): void {
	if ( '1.18.0' === (string) get_option( 'vava_paths_session_structure_repair_version', '' ) ) { return; }
	if ( ! current_user_can( 'edit_pages' ) ) { return; }

	$page_ids = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'any',
		'numberposts'    => -1,
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'relation' => 'OR',
			array( 'key' => vava_paths_meta_key( 'ar' ), 'compare' => 'EXISTS' ),
			array( 'key' => vava_paths_meta_key( 'en' ), 'compare' => 'EXISTS' ),
		),
	) );
	if ( ! $page_ids ) { return; }

	foreach ( $page_ids as $page_id ) {
		$ar = get_post_meta( (int) $page_id, vava_paths_meta_key( 'ar' ), true );
		$en = get_post_meta( (int) $page_id, vava_paths_meta_key( 'en' ), true );
		if ( ! is_array( $ar ) || ! is_array( $en ) ) { continue; }

		$authoritative_language = vava_paths_repair_empty_session_placeholders( $ar, $en );
		if ( '' === $authoritative_language ) { continue; }

		$ar = vava_paths_normalize_session_categories( $ar, 'ar' );
		$en = vava_paths_normalize_session_categories( $en, 'en' );
		$ar = vava_paths_normalize_session_basic_data( $ar, 'ar' );
		$en = vava_paths_normalize_session_basic_data( $en, 'en' );
		$ar = vava_paths_normalize_comparison_data( $ar );
		$en = vava_paths_normalize_comparison_data( $en );
		vava_paths_align_shared_structure( $ar, $en, $authoritative_language );
		update_post_meta( (int) $page_id, vava_paths_meta_key( 'ar' ), $ar );
		update_post_meta( (int) $page_id, vava_paths_meta_key( 'en' ), $en );
		vava_paths_sync_session_posts( (int) $page_id, $ar, $en );
	}

	update_option( 'vava_paths_session_structure_repair_version', '1.18.0', false );
}
add_action( 'admin_init', 'vava_paths_maybe_repair_empty_session_rows', 46 );

function vava_paths_session_data_from_post( int $post_id, string $lang ): array {
	$page_id = absint( get_post_meta( $post_id, '_vava_session_source_page', true ) ); $uid = (string) get_post_meta( $post_id, '_vava_session_uid', true ); if ( ! $page_id || ! $uid ) { return array(); }
	$data = vava_paths_data( $page_id, $lang ); foreach ( (array) ( $data['packages'] ?? array() ) as $session ) { if ( (string) ( $session['uid'] ?? '' ) === $uid ) { $session['_source_page'] = $page_id; return $session; } } return array();
}

function vava_paths_session_template( string $template ): string { return is_singular( 'vava_session' ) ? get_theme_file_path( 'single-vava_session.php' ) : $template; }
add_filter( 'template_include', 'vava_paths_session_template', 99 );

function vava_paths_session_assets(): void {
	if ( ! is_singular( 'vava_session' ) ) { return; }
	$lang = vava_current_language(); $language_style = 'en' === $lang ? 'assets/css/styles-en.css' : 'assets/css/styles-ar.css';
	wp_enqueue_style( 'vava-language', get_theme_file_uri( $language_style ), array( 'vava-theme-meta' ), vava_asset_version( $language_style ) );
	wp_enqueue_style( 'vava-typography', get_theme_file_uri( 'assets/css/typography.css' ), array( 'vava-language' ), vava_asset_version( 'assets/css/typography.css' ) );
	wp_enqueue_style( 'vava-internal-wordpress', get_theme_file_uri( 'assets/css/internal-wordpress.css' ), array( 'vava-typography' ), vava_asset_version( 'assets/css/internal-wordpress.css' ) );
	wp_enqueue_style( 'vava-session-details', get_theme_file_uri( 'assets/css/session-details.css' ), array( 'vava-internal-wordpress' ), vava_asset_version( 'assets/css/session-details.css' ) );
	wp_enqueue_script( 'vava-main', get_theme_file_uri( 'assets/js/main.js' ), array( 'vava-site-language' ), vava_asset_version( 'assets/js/main.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'vava_paths_session_assets', 40 );

function vava_paths_advanced_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; } $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore
	if ( ! $post_id || ! vava_paths_is_page( $post_id ) ) { return; }
	wp_enqueue_style( 'vava-paths-admin-advanced', get_theme_file_uri( 'assets/css/admin-paths-advanced.css' ), array( 'vava-paths-admin' ), vava_asset_version( 'assets/css/admin-paths-advanced.css' ) );
	wp_enqueue_script( 'vava-paths-admin-advanced', get_theme_file_uri( 'assets/js/admin-paths-advanced.js' ), array( 'vava-paths-admin', 'jquery-ui-sortable' ), vava_asset_version( 'assets/js/admin-paths-advanced.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'vava_paths_advanced_admin_assets', 30 );
