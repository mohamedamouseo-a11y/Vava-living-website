<?php
/** Bilingual fields and rendering helpers for standard posts and ordinary pages. */
defined( 'ABSPATH' ) || exit;

/** Use the classic post screen while the language metabox owns title and content. */
function vava_article_use_classic_editor( $use_block_editor, $post ) {
	return $post instanceof WP_Post && vava_article_is_managed_post( $post ) ? false : $use_block_editor;
}
add_filter( 'use_block_editor_for_post', 'vava_article_use_classic_editor', PHP_INT_MAX, 2 );

function vava_article_use_classic_editor_for_post_type( $use_block_editor, $post_type = '' ) {
	return 'post' === (string) $post_type ? false : $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'vava_article_use_classic_editor_for_post_type', PHP_INT_MAX, 2 );

function vava_article_remove_native_editor(): void {
	remove_post_type_support( 'post', 'editor' );
	remove_post_type_support( 'post', 'title' );
}
add_action( 'init', 'vava_article_remove_native_editor', 100 );

function vava_article_meta_key( string $field, string $lang ): string {
	return '_vava_article_' . sanitize_key( $field ) . '_' . ( 'en' === $lang ? 'en' : 'ar' );
}

/** Only default/template-less pages use the general bilingual page editor. */
function vava_article_is_managed_post( $post ): bool {
	$post = get_post( $post );
	if ( ! $post instanceof WP_Post ) { return false; }
	if ( 'post' === $post->post_type ) { return true; }
	if ( 'page' !== $post->post_type ) { return false; }
	$template = (string) get_page_template_slug( $post );
	return '' === $template || 'default' === $template;
}

function vava_article_category_meta_key( string $field, string $lang ): string {
	return '_vava_article_category_' . sanitize_key( $field ) . '_' . ( 'en' === $lang ? 'en' : 'ar' );
}

function vava_article_category_value( $term, string $field, string $lang ): string {
	$term = get_term( $term, 'category' );
	if ( ! $term instanceof WP_Term ) { return ''; }
	$value = (string) get_term_meta( $term->term_id, vava_article_category_meta_key( $field, $lang ), true );
	if ( '' !== trim( $value ) ) { return $value; }
	if ( 'ar' === $lang ) {
		if ( 'description' === $field ) { return (string) $term->description; }
		if ( 'slug' === $field ) { return (string) $term->slug; }
		return (string) $term->name;
	}
	return '';
}

function vava_article_category_language_fields( string $context, $term = null ): void {
	$is_edit = $term instanceof WP_Term;
	wp_nonce_field( 'vava_article_category_languages', '_vava_article_category_nonce' );
	$values = array();
	foreach ( array( 'ar', 'en' ) as $lang ) {
		$values[ $lang ] = array(
			'name'        => $is_edit ? vava_article_category_value( $term, 'name', $lang ) : '',
			'slug'        => $is_edit ? vava_article_category_value( $term, 'slug', $lang ) : '',
			'description' => $is_edit ? vava_article_category_value( $term, 'description', $lang ) : '',
		);
	}
	?>
	<?php if ( $is_edit ) : ?><tr class="form-field vava-category-i18n-row"><th colspan="2" scope="row"><?php endif; ?>
	<div class="vava-category-i18n" data-vava-category-language="ar">
		<div class="vava-category-i18n__toolbar">
			<div><h2 data-vava-category-heading><?php echo esc_html( $is_edit ? 'تحرير تصنيف الأقسام' : 'إضافة تصنيف أقسام' ); ?></h2><p data-vava-category-intro>أدخل بيانات التصنيف باللغة المختارة.</p></div>
			<div class="vava-category-i18n__actions">
				<div class="vava-category-i18n__switch" role="tablist" aria-label="لغة حقول التصنيف">
					<button type="button" class="is-active" data-vava-category-lang="ar" aria-selected="true">AR</button>
					<button type="button" data-vava-category-lang="en" aria-selected="false">EN</button>
				</div>
				<div class="vava-category-i18n__submit" data-vava-category-submit></div>
			</div>
		</div>
		<?php foreach ( $values as $lang => $row ) : foreach ( $row as $field => $value ) : ?>
			<input type="hidden" name="vava_category[<?php echo esc_attr( $lang ); ?>][<?php echo esc_attr( $field ); ?>]" value="<?php echo esc_attr( $value ); ?>" data-vava-category-store="<?php echo esc_attr( $lang . '-' . $field ); ?>">
		<?php endforeach; endforeach; ?>
		<input type="hidden" name="vava_category[ar][parent]" value="<?php echo esc_attr( $is_edit ? (string) $term->parent : '0' ); ?>" data-vava-category-store="ar-parent">
		<input type="hidden" name="vava_category[en][parent]" value="<?php echo esc_attr( $is_edit ? (string) $term->parent : '0' ); ?>" data-vava-category-store="en-parent">
		<section class="vava-category-i18n__pane is-active" data-vava-category-fields dir="rtl">
			<div class="form-field"><label for="vava-category-name" data-vava-category-label="name">اسم التصنيف</label><input id="vava-category-name" type="text" class="regular-text" data-vava-category-field="name" value="<?php echo esc_attr( $values['ar']['name'] ); ?>" required><p class="description" data-vava-category-help="name">الاسم كما سيظهر في الموقع.</p></div>
			<div class="form-field"><label for="vava-category-slug" data-vava-category-label="slug">الاسم اللطيف</label><input id="vava-category-slug" type="text" class="regular-text" data-vava-category-field="slug" value="<?php echo esc_attr( $values['ar']['slug'] ); ?>" dir="ltr"><p class="description" data-vava-category-help="slug">“slug” هو الرابط اللطيف للاسم، ويتكون عادةً من حروف صغيرة وأرقام وشرطات.</p></div>
			<div class="form-field"><label for="vava-category-parent" data-vava-category-label="parent">التصنيف الأب</label><?php wp_dropdown_categories( array( 'taxonomy' => 'category', 'hide_empty' => false, 'hierarchical' => true, 'show_option_none' => 'بدون', 'name' => 'vava_category_visible_parent', 'id' => 'vava-category-parent', 'selected' => $is_edit ? (int) $term->parent : 0, 'exclude' => $is_edit ? (int) $term->term_id : 0 ) ); ?><p class="description" data-vava-category-help="parent">يمكن أن تكون التصنيفات هرمية، والتصنيف الأب مشترك بين اللغتين.</p></div>
			<div class="form-field"><label for="vava-category-description" data-vava-category-label="description">الوصف</label><textarea id="vava-category-description" rows="5" data-vava-category-field="description"><?php echo esc_textarea( $values['ar']['description'] ); ?></textarea></div>
		</section>
	</div>
	<?php if ( $is_edit ) : ?></th></tr><?php endif; ?>
	<?php
}
add_action( 'category_add_form_fields', static function (): void { vava_article_category_language_fields( 'add' ); }, 1 );
add_action( 'category_edit_form_fields', static function ( WP_Term $term ): void { vava_article_category_language_fields( 'edit', $term ); }, 1 );

function vava_article_save_category_languages( int $term_id ): void {
	if ( ! isset( $_POST['_vava_article_category_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_vava_article_category_nonce'] ) ), 'vava_article_category_languages' ) || ! current_user_can( 'manage_categories' ) ) { return; }
	$data = isset( $_POST['vava_category'] ) && is_array( $_POST['vava_category'] ) ? wp_unslash( $_POST['vava_category'] ) : array();
	foreach ( array( 'ar', 'en' ) as $lang ) {
		$row = isset( $data[ $lang ] ) && is_array( $data[ $lang ] ) ? $data[ $lang ] : array();
		update_term_meta( $term_id, vava_article_category_meta_key( 'name', $lang ), sanitize_text_field( $row['name'] ?? '' ) );
		update_term_meta( $term_id, vava_article_category_meta_key( 'slug', $lang ), sanitize_title( $row['slug'] ?? '' ) );
		update_term_meta( $term_id, vava_article_category_meta_key( 'description', $lang ), sanitize_textarea_field( $row['description'] ?? '' ) );
	}
	$ar = isset( $data['ar'] ) && is_array( $data['ar'] ) ? $data['ar'] : array();
	if ( ! empty( $ar['name'] ) ) {
		remove_action( 'edited_category', 'vava_article_save_category_languages' );
		wp_update_term( $term_id, 'category', array( 'name' => sanitize_text_field( $ar['name'] ), 'slug' => sanitize_title( $ar['slug'] ?? '' ), 'parent' => absint( $ar['parent'] ?? 0 ), 'description' => sanitize_textarea_field( $ar['description'] ?? '' ) ) );
		add_action( 'edited_category', 'vava_article_save_category_languages' );
	}
}
add_action( 'created_category', 'vava_article_save_category_languages' );
add_action( 'edited_category', 'vava_article_save_category_languages' );

add_filter( 'manage_edit-category_columns', static function ( array $columns ): array {
	$result = array();
	foreach ( $columns as $key => $label ) {
		$result[ $key ] = $label;
		if ( 'name' === $key ) { $result['vava_name_en'] = 'English name'; }
	}
	return $result;
} );
add_filter( 'manage_category_custom_column', static function ( string $output, string $column, int $term_id ): string {
	return 'vava_name_en' === $column ? esc_html( vava_article_category_value( $term_id, 'name', 'en' ) ?: '—' ) : $output;
}, 10, 3 );

function vava_article_localized_value( $post, string $field, string $lang = '' ): string {
	$post = get_post( $post );
	if ( ! $post instanceof WP_Post ) { return ''; }
	$lang = $lang ? vava_normalize_language( $lang ) : vava_current_language();
	$value = (string) get_post_meta( $post->ID, vava_article_meta_key( $field, $lang ), true );
	if ( '' !== trim( wp_strip_all_tags( $value ) ) ) { return $value; }
	$fallback = (string) get_post_meta( $post->ID, vava_article_meta_key( $field, 'ar' ), true );
	if ( '' !== trim( wp_strip_all_tags( $fallback ) ) ) { return $fallback; }
	$map = array( 'title' => 'post_title', 'content' => 'post_content', 'excerpt' => 'post_excerpt' );
	return isset( $map[ $field ] ) ? (string) $post->{$map[ $field ]} : '';
}

add_action( 'add_meta_boxes_post', static function (): void {
	add_meta_box( 'vava-article-languages', 'محتوى المقال بالعربية والإنجليزية', 'vava_article_render_language_box', 'post', 'normal', 'high' );
} );
add_action( 'add_meta_boxes_page', static function ( WP_Post $post ): void {
	if ( vava_article_is_managed_post( $post ) ) {
		add_meta_box( 'vava-article-languages', 'محتوى الصفحة بالعربية والإنجليزية', 'vava_article_render_language_box', 'page', 'normal', 'high' );
	}
} );

function vava_article_render_language_box( WP_Post $post ): void {
	$is_page = 'page' === $post->post_type;
	wp_nonce_field( 'vava_article_languages_' . $post->ID, '_vava_article_nonce' );
	?>
	<div class="vava-article-language-editor" data-vava-article-language="ar">
		<header class="vava-article-toolbar">
			<div class="vava-article-toolbar__heading">
				<h1><?php echo esc_html( $is_page ? 'تحرير الصفحة' : 'تحرير المقال' ); ?></h1>
			</div>
			<div class="vava-article-toolbar__actions">
				<div class="vava-article-language-switch" role="tablist" aria-label="لغة حقول المقال">
					<button type="button" class="is-active" data-vava-article-language-button="ar" role="tab" aria-selected="true">AR</button>
					<button type="button" data-vava-article-language-button="en" role="tab" aria-selected="false">EN</button>
				</div>
				<button type="button" class="vava-article-update" data-vava-article-update><?php echo 'publish' === $post->post_status ? 'تحديث' : 'نشر'; ?></button>
				<?php if ( current_user_can( 'delete_post', $post->ID ) && EMPTY_TRASH_DAYS ) : ?>
					<a class="vava-article-delete" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>" aria-label="<?php echo esc_attr( $is_page ? 'نقل الصفحة إلى سلة المهملات' : 'نقل المقال إلى سلة المهملات' ); ?>" title="<?php echo esc_attr( $is_page ? 'حذف الصفحة' : 'حذف المقال' ); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span><span>حذف</span></a>
				<?php endif; ?>
			</div>
		</header>
	<?php
	foreach ( array( 'ar' => 'العربية', 'en' => 'English' ) as $lang => $label ) {
		$title = vava_article_localized_value( $post, 'title', $lang );
		$content = vava_article_localized_value( $post, 'content', $lang );
		?>
		<section class="vava-article-language-pane<?php echo 'ar' === $lang ? ' is-active' : ''; ?>" data-vava-article-language-pane="<?php echo esc_attr( $lang ); ?>" dir="<?php echo 'en' === $lang ? 'ltr' : 'rtl'; ?>" role="tabpanel">
		<p class="vava-article-title-field"><label><?php echo esc_html( 'en' === $lang ? ( $is_page ? 'Page title' : 'Article title' ) : ( $is_page ? 'عنوان الصفحة' : 'عنوان المقال' ) ); ?></label><input class="widefat" name="vava_article[<?php echo esc_attr( $lang ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php echo esc_attr( 'en' === $lang ? ( $is_page ? 'Enter the page title' : 'Enter the article title' ) : ( $is_page ? 'اكتب عنوان الصفحة' : 'اكتب عنوان المقال' ) ); ?>"></p>
		<div class="vava-article-content-field"><label><?php echo esc_html( 'en' === $lang ? ( $is_page ? 'Page content' : 'Article content' ) : ( $is_page ? 'محتوى الصفحة' : 'محتوى المقال' ) ); ?></label>
		<?php wp_editor( $content, 'vava_article_content_' . $lang, array( 'textarea_name' => 'vava_article[' . $lang . '][content]', 'textarea_rows' => 18, 'media_buttons' => true ) ); ?>
		<div class="vava-article-word-count"><span data-vava-word-count="<?php echo esc_attr( $lang ); ?>">0</span> <?php echo esc_html( 'en' === $lang ? 'words' : 'كلمة' ); ?></div></div></section>
	<?php }
	?></div><?php
}

function vava_article_save_languages( int $post_id ): void {
	if ( ! isset( $_POST['_vava_article_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_vava_article_nonce'] ) ), 'vava_article_languages_' . $post_id ) ) { return; }
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) || ! vava_article_is_managed_post( $post_id ) ) { return; }
	$data = isset( $_POST['vava_article'] ) && is_array( $_POST['vava_article'] ) ? wp_unslash( $_POST['vava_article'] ) : array();
	foreach ( array( 'ar', 'en' ) as $lang ) {
		$row = isset( $data[ $lang ] ) && is_array( $data[ $lang ] ) ? $data[ $lang ] : array();
		update_post_meta( $post_id, vava_article_meta_key( 'title', $lang ), sanitize_text_field( $row['title'] ?? '' ) );
		update_post_meta( $post_id, vava_article_meta_key( 'content', $lang ), wp_kses_post( $row['content'] ?? '' ) );
	}

	/* Keep WordPress lists, search and legacy fallbacks aligned with Arabic. */
	$ar = isset( $data['ar'] ) && is_array( $data['ar'] ) ? $data['ar'] : array();
	$native = array(
		'ID'           => $post_id,
		'post_title'   => sanitize_text_field( $ar['title'] ?? '' ),
		'post_content' => wp_kses_post( $ar['content'] ?? '' ),
	);
	$post_type = get_post_type( $post_id );
	remove_action( 'save_post_' . $post_type, 'vava_article_save_languages' );
	wp_update_post( wp_slash( $native ) );
	add_action( 'save_post_' . $post_type, 'vava_article_save_languages' );
}
add_action( 'save_post_post', 'vava_article_save_languages' );
add_action( 'save_post_page', 'vava_article_save_languages' );

function vava_article_admin_assets( string $hook ): void {
	$screen = get_current_screen();
	$is_post = in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && $screen && in_array( $screen->post_type, array( 'post', 'page' ), true ) && ( 'post' === $screen->post_type || ! isset( $GLOBALS['post'] ) || vava_article_is_managed_post( $GLOBALS['post'] ) );
	$is_category = in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) && $screen && 'category' === $screen->taxonomy;
	if ( ! $is_post && ! $is_category ) { return; }
	wp_enqueue_style( 'vava-article-language-editor', get_template_directory_uri() . '/assets/css/admin-article-i18n-vava.css', array(), '1.22.28' );
	wp_enqueue_script( 'vava-article-language-editor', get_template_directory_uri() . '/assets/js/admin-article-i18n-vava.js', array(), '1.22.28', true );
	$category_names = array();
	foreach ( get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) ) as $category ) {
		if ( $category instanceof WP_Term ) { $category_names[ $category->term_id ] = array( 'ar' => vava_article_category_value( $category, 'name', 'ar' ), 'en' => vava_article_category_value( $category, 'name', 'en' ) ); }
	}
	wp_localize_script(
		'vava-article-language-editor',
		'vavaArticleEditor',
		array(
			'initialStatus' => $is_post && isset( $_GET['message'] ) && in_array( (int) $_GET['message'], array( 1, 6 ), true ) ? 'saved' : '',
			'isPublished'   => isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post && 'publish' === $GLOBALS['post']->post_status,
			'categoryNames' => $category_names,
			'contentType'   => $is_post && $screen ? (string) $screen->post_type : 'post',
		)
	);
}
add_action( 'admin_enqueue_scripts', 'vava_article_admin_assets', 1000 );

/** Keep ordinary page editing focused on the VAVA language editor. */
function vava_article_remove_page_side_boxes(): void {
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) { return; }
	remove_meta_box( 'commentstatusdiv', 'page', 'normal' );
	remove_meta_box( 'commentsdiv', 'page', 'normal' );
	remove_meta_box( 'slugdiv', 'page', 'normal' );
	remove_meta_box( 'authordiv', 'page', 'normal' );
}
add_action( 'add_meta_boxes_page', 'vava_article_remove_page_side_boxes', PHP_INT_MAX );
