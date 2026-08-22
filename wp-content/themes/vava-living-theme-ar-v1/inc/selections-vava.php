<?php
/**
 * Bilingual “VAVA Selections” page, editor, previews, and data model.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

function vava_selections_template_slug(): string {
	return 'page-templates/selections-vava.php';
}

function vava_selections_page_id(): int {
	$ids = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => vava_selections_template_slug(),
		)
	);
	if ( isset( $ids[0] ) ) { return absint( $ids[0] ); }
	$pages = get_posts( array( 'post_type' => 'page', 'post_status' => array( 'publish', 'draft', 'private' ), 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true ) );
	foreach ( $pages as $page_id ) { if ( vava_selections_is_page( absint( $page_id ) ) ) { return absint( $page_id ); } }
	return 0;
}

function vava_selections_is_page( int $post_id ): bool {
	if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
		return false;
	}
	$template = (string) get_post_meta( $post_id, '_wp_page_template', true );
	$post     = get_post( $post_id );
	if ( vava_selections_template_slug() === $template ) {
		return true;
	}
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return in_array( $post->post_name, array( 'vava-selections', 'selections-vava', 'vava-shop' ), true )
		|| in_array( trim( (string) $post->post_title ), array( 'مختارات VAVA', 'مختارات فافا', 'VAVA Selections' ), true );
}

function vava_selections_title_defaults( array $defaults, int $post_id ): array {
	if ( vava_selections_is_page( $post_id ) ) {
		$defaults['ar'] = 'مختارات VAVA';
		$defaults['en'] = 'VAVA Selections';
	}
	return $defaults;
}
add_filter( 'vava_page_title_defaults', 'vava_selections_title_defaults', 10, 2 );

function vava_selections_text_meta_key( string $lang ): string {
	return '_vava_selections_text_' . ( 'en' === $lang ? 'en' : 'ar' );
}

function vava_selections_products_meta_key( string $lang ): string {
	return '_vava_selections_products_' . ( 'en' === $lang ? 'en' : 'ar' );
}

function vava_selections_text_defaults( string $lang = 'ar' ): array {
	if ( 'en' === $lang ) {
		return array(
			'hero' => array(
				'eyebrow' => 'VAVA Selections',
				'title'    => 'Curated Selections for a Life Lived with Awareness',
				'intro'    => 'Not everything we bring into our lives carries the same meaning. Some things simply pass through, while others become part of how we live, care for ourselves, and move through the spaces we share.',
				'note'     => 'At VAVA, selections are not chosen only by category, but by the impact they may bring into life. What gathers VAVA selections together is not the type of product, but the vision behind it.',
			),
			'collections' => array(
				'digital' => array(
					'title'       => 'Digital Products',
					'description' => 'Thoughtful digital guides and resources designed to support understanding, balance, and conscious daily practice.',
					'button_text' => 'Explore Digital Products',
				),
				'tangible' => array(
					'title'       => 'Tangible Selections',
					'description' => 'Coming soon',
					'button_text' => 'Explore Tangible Selections',
				),
			),
			'empty' => array(
				'digital'  => 'Digital products will be added here soon.',
				'tangible' => 'Tangible selections will be added here soon.',
			),
		);
	}

	return array(
		'hero' => array(
			'eyebrow' => 'مختارات VAVA',
			'title'    => 'مختارات لحياة تُعاش بوعي',
			'intro'    => 'في VAVA، لا نجمع المنتجات بحسب فئتها، بل نختارها بحسب الرؤية التي تقف خلفها، والأثر الذي يمكن أن تضيفه إلى الحياة. لذلك قد تجد هنا موارد رقمية، أومنتجات للعناية الشخصية، أوقطعًا للمنزل، أوأعمالًا حرفية أوغيرها...يجمعها جميعًا مقصدٌ واحد: دعم علاقة أكثر وعيًا بالحياة، وما نشاركه فيها',
			'note'     => '',
		),
		'collections' => array(
			'digital' => array(
				'title'       => 'منتجات رقمية',
				'description' => 'أدلة وموارد رقمية مختارة بعناية لدعم الفهم والتوازن والممارسة اليومية الواعية.',
				'button_text' => 'استعرض المنتجات الرقمية',
			),
			'tangible' => array(
				'title'       => 'مختارات ملموسة',
				'description' => 'قريبًا',
				'button_text' => 'استعرض المختارات الملموسة',
			),
		),
		'empty' => array(
			'digital'  => 'ستتم إضافة المنتجات الرقمية هنا قريبًا.',
			'tangible' => 'ستتم إضافة المختارات الملموسة هنا قريبًا.',
		),
	);
}

function vava_selections_default_shared(): array {
	return array(
		'hero_image_id' => 0,
		'collection_images' => array(
			'digital'  => 0,
			'tangible' => 0,
		),
		'products' => array(
			'digital' => array(
				array(
					'uid'            => 'digital-ayurveda-intro',
					'image_id'       => 0,
					'fallback_asset' => 'assets/images/product-cover-ayurveda.png',
					'price'          => '99',
					'enabled'        => 1,
				),
				array(
					'uid'            => 'digital-balanced-nutrition',
					'image_id'       => 0,
					'fallback_asset' => 'assets/images/product-cover-nutrition.png',
					'price'          => '66',
					'enabled'        => 1,
				),
				array(
					'uid'            => 'digital-balance-bundle',
					'image_id'       => 0,
					'fallback_asset' => 'assets/images/product-cover-bundle.png',
					'price'          => '155',
					'enabled'        => 1,
				),
				array(
					'uid'            => 'digital-dosha-food',
					'image_id'       => 0,
					'fallback_asset' => 'assets/images/product-cover-dosha.png',
					'price'          => '',
					'enabled'        => 1,
				),
			),
			'tangible' => array(),
		),
	);
}

function vava_selections_default_products_text( string $lang = 'ar' ): array {
	if ( 'en' === $lang ) {
		return array(
			'digital' => array(
				array( 'uid' => 'digital-ayurveda-intro', 'title' => 'Introduction to Ayurvedic Wisdom', 'description' => 'A simple guide to understanding the foundations of Ayurveda and applying them in everyday life.', 'currency' => 'SAR', 'button_text' => 'View Details' ),
				array( 'uid' => 'digital-balanced-nutrition', 'title' => 'Balanced Ayurvedic Nutrition', 'description' => 'A practical guide to nourishing the body in a way that supports its nature and restores balance.', 'currency' => 'SAR', 'button_text' => 'View Details' ),
				array( 'uid' => 'digital-balance-bundle', 'title' => 'Essential Balance Bundle', 'description' => 'Everything needed to begin a journey toward balance — in one place.', 'currency' => 'SAR', 'button_text' => 'View Details' ),
				array( 'uid' => 'digital-dosha-food', 'title' => 'Concise Dosha Food Resource', 'description' => 'A concise practical reference to help understand foods that may suit each dosha with ease and awareness.', 'currency' => 'To be confirmed', 'button_text' => 'View Details' ),
			),
			'tangible' => array(),
		);
	}

	return array(
		'digital' => array(
			array( 'uid' => 'digital-ayurveda-intro', 'title' => 'مدخل إلى الحكمة اليورفيدية', 'description' => 'دليلك البسيط لفهم أساسيات اليورفيدا وتطبيقها في حياتك اليومية.', 'currency' => 'ر.س', 'button_text' => 'عرض التفاصيل' ),
			array( 'uid' => 'digital-balanced-nutrition', 'title' => 'الغذاء اليورفيدي المتوازن', 'description' => 'دليل عملي لتغذية الجسم بما يناسب طبيعته ويعيد التوازن.', 'currency' => 'ر.س', 'button_text' => 'عرض التفاصيل' ),
			array( 'uid' => 'digital-balance-bundle', 'title' => 'حزمة التوازن الأساسية', 'description' => 'كل ما يلزم لبداية رحلة التوازن — في مكان واحد.', 'currency' => 'ر.س', 'button_text' => 'عرض التفاصيل' ),
			array( 'uid' => 'digital-dosha-food', 'title' => 'ملف الأغذية اليورفيدي المختصر', 'description' => 'دليل عملي مختصر يساعدك على فهم الأطعمة المناسبة لكل دوشا بسهولة ووعي.', 'currency' => 'يُحدد لاحقًا', 'button_text' => 'عرض التفاصيل' ),
		),
		'tangible' => array(),
	);
}

function vava_selections_text_data( int $post_id, string $lang ): array {
	$lang     = 'en' === $lang ? 'en' : 'ar';
	$defaults = vava_selections_text_defaults( $lang );
	$saved    = get_post_meta( $post_id, vava_selections_text_meta_key( $lang ), true );
	$data     = is_array( $saved ) ? array_replace_recursive( $defaults, $saved ) : $defaults;

	// The closing section was retired in 1.7.4. Ignore legacy values even
	// before the one-time cleanup runs so they can never return to the editor.
	unset( $data['closing'] );

	return $data;
}

function vava_selections_shared_data( int $post_id ): array {
	$defaults = vava_selections_default_shared();
	$saved    = get_post_meta( $post_id, '_vava_selections_shared', true );
	if ( ! is_array( $saved ) ) {
		return $defaults;
	}

	$data = array_replace_recursive( $defaults, $saved );

	// Product groups are ordered lists, not associative settings. When an editor
	// intentionally removes or reorders products, the saved list must replace the
	// first-run defaults exactly; recursive numeric merging would restore deleted
	// default products by their old indexes.
	foreach ( array( 'digital', 'tangible' ) as $group ) {
		if ( isset( $saved['products'][ $group ] ) && is_array( $saved['products'][ $group ] ) ) {
			$data['products'][ $group ] = array_values( $saved['products'][ $group ] );
		}
	}

	return $data;
}

function vava_selections_products_text_data( int $post_id, string $lang ): array {
	$lang     = 'en' === $lang ? 'en' : 'ar';
	$defaults = vava_selections_default_products_text( $lang );
	$saved    = get_post_meta( $post_id, vava_selections_products_meta_key( $lang ), true );
	if ( ! is_array( $saved ) ) {
		return $defaults;
	}
	foreach ( array( 'digital', 'tangible' ) as $group ) {
		if ( ! isset( $saved[ $group ] ) || ! is_array( $saved[ $group ] ) ) {
			$saved[ $group ] = $defaults[ $group ] ?? array();
		}
	}
	return $saved;
}

function vava_selections_product_text_map( array $items ): array {
	$map = array();
	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$uid = sanitize_key( (string) ( $item['uid'] ?? '' ) );
		if ( '' !== $uid ) {
			$map[ $uid ] = $item;
		}
	}
	return $map;
}

function vava_selections_products( int $post_id, string $group, string $lang, bool $enabled_only = false ): array {
	$group  = 'tangible' === $group ? 'tangible' : 'digital';
	$shared = vava_selections_shared_data( $post_id );
	$text   = vava_selections_products_text_data( $post_id, $lang );
	$map    = vava_selections_product_text_map( (array) ( $text[ $group ] ?? array() ) );
	$result = array();
	foreach ( array_values( (array) ( $shared['products'][ $group ] ?? array() ) ) as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$uid = sanitize_key( (string) ( $item['uid'] ?? '' ) );
		if ( '' === $uid || ( $enabled_only && empty( $item['enabled'] ) ) ) {
			continue;
		}
		$localized = $map[ $uid ] ?? array();
		$result[]  = array_merge(
			array(
				'uid'         => $uid,
				'title'       => '',
				'description' => '',
				'currency'    => '',
				'button_text' => '',
			),
			$item,
			$localized,
			array(
				'uid'   => $uid,
				'group' => $group,
			)
		);
	}
	return $result;
}

function vava_selections_image_url( int $attachment_id, string $fallback_asset = '', string $size = 'full' ): string {
	if ( $attachment_id > 0 ) {
		$url = wp_get_attachment_image_url( $attachment_id, $size );
		if ( $url ) {
			return (string) $url;
		}
	}
	return '' !== $fallback_asset ? vava_asset_uri( $fallback_asset ) : '';
}

function vava_selections_product_url( array $product, string $lang, int $selections_page_id = 0 ): string {
	$uid = sanitize_key( (string) ( $product['uid'] ?? '' ) );
	if ( '' === $uid ) {
		return '#';
	}

	$base_url = $selections_page_id > 0 ? vava_localized_page_url( $selections_page_id, $lang ) : '';
	if ( '' === $base_url ) {
		$base_url = home_url( '/' );
	}

	return add_query_arg( 'vava_product', $uid, $base_url );
}

function vava_selections_sections( string $lang = 'ar' ): array {
	if ( 'en' === $lang ) {
		return array(
			'hero'        => 'Hero',
			'collections' => 'Product types',
			'digital'     => 'Digital products',
			'tangible'    => 'Tangible selections',
		);
	}
	return array(
		'hero'        => 'الهيرو',
		'collections' => 'أنواع المنتجات',
		'digital'     => 'المنتجات الرقمية',
		'tangible'    => 'المختارات الملموسة',
	);
}

function vava_selections_section_icon( string $section ): string {
	$icons = array(
		'hero'        => '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="3"/><circle cx="9" cy="9" r="2"/><path d="m6 17 5-5 3 3 2-2 2 2"/></svg>',
		'collections' => '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="8" height="16" rx="2"/><rect x="13" y="4" width="8" height="16" rx="2"/></svg>',
		'digital'     => '<svg viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>',
		'tangible'    => '<svg viewBox="0 0 24 24"><path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 7v10l8 4 8-4V7M12 11v10"/></svg>',
	);
	return $icons[ $section ] ?? $icons['hero'];
}

function vava_selections_admin_text( string $key, string $lang = 'ar' ): string {
	$texts = array(
		'meta_title'      => array( 'ar' => 'إعدادات صفحة مختارات VAVA', 'en' => 'VAVA Selections Page Settings' ),
		'fields_language' => array( 'ar' => 'لغة الحقول', 'en' => 'Fields language' ),
		'update'          => array( 'ar' => 'تحديث', 'en' => 'Update' ),
		'live_preview'    => array( 'ar' => 'معاينة مباشرة', 'en' => 'Live preview' ),
		'shared'          => array( 'ar' => 'إعدادات مشتركة بين اللغتين', 'en' => 'Settings shared between both languages' ),
		'choose_replace'  => array( 'ar' => 'اختيار أو استبدال', 'en' => 'Choose or replace' ),
		'delete_file'     => array( 'ar' => 'حذف الملف', 'en' => 'Delete file' ),
		'hero_image'      => array( 'ar' => 'صورة الهيرو', 'en' => 'Hero image' ),
		'digital_image'   => array( 'ar' => 'صورة المنتجات الرقمية', 'en' => 'Digital products image' ),
		'tangible_image'  => array( 'ar' => 'صورة المختارات الملموسة', 'en' => 'Tangible selections image' ),
		'add_product'     => array( 'ar' => 'إضافة منتج', 'en' => 'Add product' ),
		'product'         => array( 'ar' => 'منتج', 'en' => 'Product' ),
		'new_product'     => array( 'ar' => 'منتج جديد', 'en' => 'New product' ),
		'title'           => array( 'ar' => 'العنوان', 'en' => 'Title' ),
		'description'     => array( 'ar' => 'الوصف المختصر', 'en' => 'Short description' ),
		'button_text'     => array( 'ar' => 'نص الزر', 'en' => 'Button text' ),
		'price'           => array( 'ar' => 'السعر', 'en' => 'Price' ),
		'currency'        => array( 'ar' => 'العملة أو نص السعر', 'en' => 'Currency or price text' ),
		'product_image'   => array( 'ar' => 'صورة المنتج', 'en' => 'Product image' ),
		'enabled'         => array( 'ar' => 'إظهار المنتج', 'en' => 'Show product' ),
		'delete_product'  => array( 'ar' => 'حذف المنتج', 'en' => 'Delete product' ),
		'empty_preview'   => array( 'ar' => 'لا توجد منتجات ظاهرة في هذا القسم حاليًا.', 'en' => 'There are no visible products in this section yet.' ),
		'product_types'   => array( 'ar' => 'أنواع المنتجات', 'en' => 'Product types' ),
		'digital_type'    => array( 'ar' => 'المنتجات الرقمية', 'en' => 'Digital products' ),
		'tangible_type'   => array( 'ar' => 'المختارات الملموسة', 'en' => 'Tangible selections' ),
		'shared_images'   => array( 'ar' => 'الصور والإعدادات المشتركة', 'en' => 'Shared images and settings' ),
	);
	$lang = 'en' === $lang ? 'en' : 'ar';
	return (string) ( $texts[ $key ][ $lang ] ?? $texts[ $key ]['ar'] ?? $key );
}

function vava_selections_add_meta_boxes( string $post_type, WP_Post $post ): void {
	if ( 'page' !== $post_type || ! vava_selections_is_page( (int) $post->ID ) ) {
		return;
	}
	remove_meta_box( 'postdivrich', 'page', 'normal' );
	remove_meta_box( 'postimagediv', 'page', 'side' );
	add_meta_box( 'vava_homepage_settings', vava_selections_admin_text( 'meta_title', 'ar' ), 'vava_selections_render_settings', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'vava_selections_add_meta_boxes', 10, 2 );

function vava_selections_render_text_field( string $name, string $value, string $label, string $preview, string $type = 'text' ): void {
	$id    = sanitize_html_class( ltrim( $name, '_' ) );
	$class = 'textarea' === $type ? ' vava-field-full' : '';
	?>
	<div class="vava-field<?php echo esc_attr( $class ); ?>">
		<label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
		<?php if ( 'textarea' === $type ) : ?>
			<textarea class="widefat" data-selections-preview="<?php echo esc_attr( $preview ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="5"><?php echo esc_textarea( $value ); ?></textarea>
		<?php else : ?>
			<input class="widefat" data-selections-preview="<?php echo esc_attr( $preview ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>"/>
		<?php endif; ?>
	</div>
	<?php
}

function vava_selections_render_media_field( string $name, int $attachment_id, string $fallback_asset, string $label_ar, string $label_en, string $preview_key ): void {
	$id          = sanitize_html_class( ltrim( $name, '_' ) );
	$fallback    = '' !== $fallback_asset ? vava_asset_uri( $fallback_asset ) : '';
	$current_url = vava_selections_image_url( $attachment_id, $fallback_asset, 'medium_large' );
	?>
	<div class="vava-admin-field vava-admin-field-media vava-admin-field-wide vava-selections-media-field" data-selections-media-field data-fallback-url="<?php echo esc_url( $fallback ); ?>" data-preview-key="<?php echo esc_attr( $preview_key ); ?>">
		<label for="<?php echo esc_attr( $id ); ?>"><strong<?php echo vava_admin_i18n_attributes( $label_ar, $label_en ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $label_ar ); ?></strong></label>
		<div class="vava-media-field" data-media-type="image">
			<input class="vava-media-id" data-selections-media-id data-media-url="<?php echo esc_url( $current_url ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="hidden" value="<?php echo esc_attr( (string) $attachment_id ); ?>"/>
			<div class="vava-media-dropzone" role="button" tabindex="0"><div class="vava-media-preview"><?php if ( $current_url ) : ?><img alt="" src="<?php echo esc_url( $current_url ); ?>"/><?php else : ?><div class="vava-media-empty"><strong><?php echo esc_html( $label_ar ); ?></strong></div><?php endif; ?></div></div>
			<div class="vava-media-actions"><button class="button vava-media-select" type="button"<?php echo vava_admin_i18n_attributes( vava_selections_admin_text( 'choose_replace', 'ar' ), vava_selections_admin_text( 'choose_replace', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_selections_admin_text( 'choose_replace', 'ar' ) ); ?></button><button class="button vava-media-remove" type="button"<?php echo vava_admin_i18n_attributes( vava_selections_admin_text( 'delete_file', 'ar' ), vava_selections_admin_text( 'delete_file', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_selections_admin_text( 'delete_file', 'ar' ) ); ?></button></div>
		</div>
	</div>
	<?php
}

function vava_selections_render_hero_fields( int $post_id, string $lang ): void {
	$data = vava_selections_text_data( $post_id, $lang );
	$hero = $data['hero'];
	$pre  = '_vava_selections_' . $lang . '_hero_';
	vava_selections_render_text_field( $pre . 'eyebrow', (string) $hero['eyebrow'], 'en' === $lang ? 'Small text' : 'النص الصغير', 'hero-eyebrow' );
	vava_selections_render_text_field( $pre . 'title', (string) $hero['title'], 'en' === $lang ? 'Main title' : 'العنوان الرئيسي', 'hero-title' );
	vava_selections_render_text_field( $pre . 'intro', (string) $hero['intro'], 'en' === $lang ? 'Introduction' : 'المقدمة', 'hero-intro', 'textarea' );
	vava_selections_render_text_field( $pre . 'note', (string) $hero['note'], 'en' === $lang ? 'Supporting text' : 'النص الداعم', 'hero-note', 'textarea' );
}

function vava_selections_render_collection_fields( int $post_id, string $lang ): void {
	$data      = vava_selections_text_data( $post_id, $lang );
	$shared    = vava_selections_shared_data( $post_id );
	$digital   = (array) ( $data['collections']['digital'] ?? array() );
	$tangible  = (array) ( $data['collections']['tangible'] ?? array() );
	$prefixes  = array(
		'digital'  => '_vava_selections_' . $lang . '_collection_digital_',
		'tangible' => '_vava_selections_' . $lang . '_collection_tangible_',
	);
	?>
	<div class="vava-selections-types-editor" data-collection-editor="merged">
		<header class="vava-selections-types-heading">
			<div>
				<h3><?php echo esc_html( vava_selections_admin_text( 'product_types', $lang ) ); ?></h3>
				<p><?php echo esc_html( 'en' === $lang ? 'Each product type now keeps its text and shared image together in one compact card.' : 'تظهر إعدادات كل نوع وصورته المشتركة داخل بطاقة واحدة مختصرة.' ); ?></p>
			</div>
		</header>
		<div class="vava-selections-type-cards">
			<section class="vava-selections-type-card" data-product-type-card="digital">
				<header><strong><?php echo esc_html( vava_selections_admin_text( 'digital_type', $lang ) ); ?></strong></header>
				<?php vava_selections_render_text_field( $prefixes['digital'] . 'title', (string) ( $digital['title'] ?? '' ), vava_selections_admin_text( 'title', $lang ), 'collection-digital-title' ); ?>
				<?php vava_selections_render_text_field( $prefixes['digital'] . 'description', (string) ( $digital['description'] ?? '' ), vava_selections_admin_text( 'description', $lang ), 'collection-digital-description', 'textarea' ); ?>
				<?php vava_selections_render_text_field( $prefixes['digital'] . 'button_text', (string) ( $digital['button_text'] ?? '' ), vava_selections_admin_text( 'button_text', $lang ), 'collection-digital-button' ); ?>
				<?php vava_selections_render_media_field( '_vava_selections_collection_digital_image_id', absint( $shared['collection_images']['digital'] ?? 0 ), 'assets/images/store-2.png', vava_selections_admin_text( 'digital_image', 'ar' ), vava_selections_admin_text( 'digital_image', 'en' ), 'collection-digital' ); ?>
			</section>
			<section class="vava-selections-type-card" data-product-type-card="tangible">
				<header><strong><?php echo esc_html( vava_selections_admin_text( 'tangible_type', $lang ) ); ?></strong></header>
				<?php vava_selections_render_text_field( $prefixes['tangible'] . 'title', (string) ( $tangible['title'] ?? '' ), vava_selections_admin_text( 'title', $lang ), 'collection-tangible-title' ); ?>
				<?php vava_selections_render_text_field( $prefixes['tangible'] . 'description', (string) ( $tangible['description'] ?? '' ), vava_selections_admin_text( 'description', $lang ), 'collection-tangible-description', 'textarea' ); ?>
				<?php vava_selections_render_text_field( $prefixes['tangible'] . 'button_text', (string) ( $tangible['button_text'] ?? '' ), vava_selections_admin_text( 'button_text', $lang ), 'collection-tangible-button' ); ?>
				<?php vava_selections_render_media_field( '_vava_selections_collection_tangible_image_id', absint( $shared['collection_images']['tangible'] ?? 0 ), 'assets/images/vava_shop_bottles.jpg', vava_selections_admin_text( 'tangible_image', 'ar' ), vava_selections_admin_text( 'tangible_image', 'en' ), 'collection-tangible' ); ?>
			</section>
		</div>
	</div>
	<?php
}

function vava_selections_render_product_item( int $post_id, string $group, string $lang, array $product, int $index, bool $template = false ): void {
	$uid          = $template ? '__UID__' : sanitize_key( (string) ( $product['uid'] ?? '' ) );
	$index_value  = $template ? '__INDEX__' : (string) $index;
	$name_base    = '_vava_selections_products_' . $lang . '[' . $group . '][' . $index_value . ']';
	$image_url    = vava_selections_image_url( absint( $product['image_id'] ?? 0 ), (string) ( $product['fallback_asset'] ?? '' ), 'medium' );
	$collapsed    = ! $template && $index > 0;
	$product_title = trim( (string) ( $product['title'] ?? '' ) );
	$header_title = '' !== $product_title ? $product_title : vava_selections_admin_text( 'new_product', $lang );
	?>
	<article class="vava-repeater-item vava-selections-product-item<?php echo $collapsed ? ' is-collapsed' : ''; ?>" data-product-group="<?php echo esc_attr( $group ); ?>" data-product-language="<?php echo esc_attr( $lang ); ?>" data-product-uid="<?php echo esc_attr( $uid ); ?>" data-selections-product-item>
		<input data-product-uid-input name="<?php echo esc_attr( $name_base . '[uid]' ); ?>" type="hidden" value="<?php echo esc_attr( $uid ); ?>"/>
		<header class="vava-repeater-item-header">
			<strong data-product-header-title data-empty-title="<?php echo esc_attr( vava_selections_admin_text( 'new_product', $lang ) ); ?>"><?php echo esc_html( $header_title ); ?></strong>
			<div class="vava-repeater-actions">
				<label class="vava-selections-visibility-switch" aria-label="<?php echo esc_attr( vava_selections_admin_text( 'enabled', $lang ) ); ?>" title="<?php echo esc_attr( vava_selections_admin_text( 'enabled', $lang ) ); ?>">
					<input data-product-shared-field="enabled" name="<?php echo esc_attr( $name_base . '[enabled]' ); ?>" type="checkbox" value="1" <?php checked( ! empty( $product['enabled'] ) ); ?>/>
					<i aria-hidden="true"></i>
					<span class="screen-reader-text"><?php echo esc_html( vava_selections_admin_text( 'enabled', $lang ) ); ?></span>
				</label>
				<button class="vava-icon-button vava-icon-button-danger" data-product-remove type="button" aria-label="<?php echo esc_attr( vava_selections_admin_text( 'delete_product', $lang ) ); ?>" title="<?php echo esc_attr( vava_selections_admin_text( 'delete_product', $lang ) ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="m7 7 1 13h8l1-13"/></svg></button>
				<button class="vava-icon-button vava-repeater-toggle" type="button" aria-expanded="<?php echo $collapsed ? 'false' : 'true'; ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m6 15 6-6 6 6"/></svg></button>
			</div>
		</header>
		<div class="vava-repeater-item-body">
			<div class="vava-repeater-field vava-selections-product-field"><label><span><?php echo esc_html( vava_selections_admin_text( 'title', $lang ) ); ?></span><input data-product-local-field="title" name="<?php echo esc_attr( $name_base . '[title]' ); ?>" type="text" value="<?php echo esc_attr( (string) ( $product['title'] ?? '' ) ); ?>"/></label></div>
			<div class="vava-repeater-field vava-selections-product-field"><label><span><?php echo esc_html( vava_selections_admin_text( 'button_text', $lang ) ); ?></span><input data-product-local-field="button_text" name="<?php echo esc_attr( $name_base . '[button_text]' ); ?>" type="text" value="<?php echo esc_attr( (string) ( $product['button_text'] ?? '' ) ); ?>"/></label></div>
			<div class="vava-repeater-field vava-repeater-field-wide vava-selections-product-field"><label><span><?php echo esc_html( vava_selections_admin_text( 'description', $lang ) ); ?></span><textarea data-product-local-field="description" name="<?php echo esc_attr( $name_base . '[description]' ); ?>" rows="4"><?php echo esc_textarea( (string) ( $product['description'] ?? '' ) ); ?></textarea></label></div>
			<div class="vava-repeater-field vava-selections-product-field"><label><span><?php echo esc_html( vava_selections_admin_text( 'price', $lang ) ); ?></span><input data-product-shared-field="price" name="<?php echo esc_attr( $name_base . '[price]' ); ?>" type="text" value="<?php echo esc_attr( (string) ( $product['price'] ?? '' ) ); ?>"/></label></div>
			<div class="vava-repeater-field vava-selections-product-field"><label><span><?php echo esc_html( vava_selections_admin_text( 'currency', $lang ) ); ?></span><input data-product-local-field="currency" name="<?php echo esc_attr( $name_base . '[currency]' ); ?>" type="text" value="<?php echo esc_attr( (string) ( $product['currency'] ?? '' ) ); ?>"/></label></div>
			<div class="vava-product-assets-grid<?php echo 'digital' === $group ? ' has-protected-file' : ''; ?>">
				<?php if ( 'digital' !== $group ) : ?><div class="vava-repeater-field vava-repeater-field-wide vava-selections-product-media" data-product-media-field data-fallback-url="<?php echo esc_url( ! empty( $product['fallback_asset'] ) ? vava_asset_uri( (string) $product['fallback_asset'] ) : '' ); ?>">
					<label><span><?php echo esc_html( vava_selections_admin_text( 'product_image', $lang ) ); ?></span></label>
					<input data-product-image-id data-product-shared-field="image_id" name="<?php echo esc_attr( $name_base . '[image_id]' ); ?>" type="hidden" value="<?php echo esc_attr( (string) absint( $product['image_id'] ?? 0 ) ); ?>"/>
					<input data-product-fallback-asset name="<?php echo esc_attr( $name_base . '[fallback_asset]' ); ?>" type="hidden" value="<?php echo esc_attr( (string) ( $product['fallback_asset'] ?? '' ) ); ?>"/>
					<div class="vava-selections-product-image-preview" data-product-image-preview><?php if ( $image_url ) : ?><img alt="" src="<?php echo esc_url( $image_url ); ?>"/><?php else : ?><span><?php echo esc_html( vava_selections_admin_text( 'product_image', $lang ) ); ?></span><?php endif; ?></div>
					<div class="vava-media-actions"><button class="button" data-product-image-select type="button"><?php echo esc_html( vava_selections_admin_text( 'choose_replace', $lang ) ); ?></button><button class="button" data-product-image-remove type="button"><?php echo esc_html( vava_selections_admin_text( 'delete_file', $lang ) ); ?></button></div>
				</div><?php else : ?>
					<input data-product-image-id data-product-shared-field="image_id" name="<?php echo esc_attr( $name_base . '[image_id]' ); ?>" type="hidden" value="<?php echo esc_attr( (string) absint( $product['image_id'] ?? 0 ) ); ?>"/>
					<input data-product-fallback-asset name="<?php echo esc_attr( $name_base . '[fallback_asset]' ); ?>" type="hidden" value="<?php echo esc_attr( (string) ( $product['fallback_asset'] ?? '' ) ); ?>"/>
				<?php endif; ?>
				<?php if ( 'digital' === $group && ! $template && function_exists( 'vava_digital_products_render_admin_file_field' ) ) { vava_digital_products_render_admin_file_field( $post_id, $uid, $lang ); } ?>
			</div>
		</div>
	</article>
	<?php
}

function vava_selections_render_products_editor( int $post_id, string $group, string $lang ): void {
	$products = vava_selections_products( $post_id, $group, $lang, false );
	?>
	<div class="vava-admin-repeaters vava-dynamic-repeater vava-selections-products-editor" data-products-editor data-products-group="<?php echo esc_attr( $group ); ?>" data-products-language="<?php echo esc_attr( $lang ); ?>">
		<div class="vava-repeater-heading"><div><h3><?php echo esc_html( vava_selections_sections( $lang )[ $group ] ); ?></h3><p class="description"><?php echo esc_html( 'en' === $lang ? 'Product images, prices, and visibility are synchronized between both languages.' : 'صور المنتجات والأسعار وحالة الظهور متزامنة بين اللغتين.' ); ?></p></div><button class="button button-secondary" data-product-add type="button"><?php echo esc_html( vava_selections_admin_text( 'add_product', $lang ) ); ?></button></div>
		<div class="vava-repeater-list" data-products-list><?php foreach ( $products as $index => $product ) { vava_selections_render_product_item( $post_id, $group, $lang, $product, $index ); } ?></div>
		<template data-product-template><?php vava_selections_render_product_item( $post_id, $group, $lang, array( 'uid' => '__UID__', 'title' => '', 'description' => '', 'currency' => 'en' === $lang ? 'SAR' : 'ر.س', 'button_text' => 'en' === $lang ? 'View Details' : 'عرض التفاصيل', 'price' => '', 'image_id' => 0, 'fallback_asset' => '', 'enabled' => 1 ), 0, true ); ?></template>
	</div>
	<?php
}

function vava_selections_render_preview_product_card( array $product, string $lang, string $group = 'digital' ): void {
	$is_digital = 'digital' === $group;
	$pdf_cover  = $is_digital && function_exists( 'vava_digital_products_cover_url' ) ? vava_digital_products_cover_url( (string) ( $product['uid'] ?? '' ) ) : '';
	$image      = $pdf_cover ?: vava_selections_image_url( absint( $product['image_id'] ?? 0 ), (string) ( $product['fallback_asset'] ?? '' ), 'medium' );
	?>
	<article class="vava-selections-preview-product<?php echo $is_digital ? ' is-digital-cover' : ''; ?>" data-preview-product data-product-group="<?php echo esc_attr( $group ); ?>" data-product-uid="<?php echo esc_attr( (string) ( $product['uid'] ?? '' ) ); ?>">
		<?php if ( $is_digital ) : ?><div class="vava-selections-preview-product-image vava-preview-pdf-cover"<?php if ( $image ) : ?> style="background-image:url('<?php echo esc_url( $image ); ?>')"<?php endif; ?>></div><?php endif; ?>
		<?php if ( ! $is_digital ) : ?>
			<div class="vava-selections-preview-product-image"<?php if ( $image ) : ?> style="background-image:url('<?php echo esc_url( $image ); ?>')"<?php endif; ?>></div>
		<?php endif; ?>
		<div class="vava-selections-preview-product-content">
			<h4 data-preview-product-field="title"><?php echo esc_html( (string) ( $product['title'] ?? '' ) ); ?></h4>
			<p data-preview-product-field="description"><?php echo esc_html( (string) ( $product['description'] ?? '' ) ); ?></p>
			<div class="vava-selections-preview-product-meta">
				<strong data-preview-product-field="button_text"><?php echo esc_html( (string) ( $product['button_text'] ?? '' ) ); ?></strong>
				<span class="vava-selections-preview-product-price<?php echo '' === trim( (string) ( $product['price'] ?? '' ) ) ? ' is-price-text' : ''; ?>"><?php if ( '' !== trim( (string) ( $product['price'] ?? '' ) ) ) : ?><b data-preview-product-field="price"><?php echo esc_html( (string) ( $product['price'] ?? '' ) ); ?></b><em data-preview-product-field="currency"><?php echo esc_html( (string) ( $product['currency'] ?? '' ) ); ?></em><?php else : ?><em data-preview-product-field="currency"><?php echo esc_html( (string) ( $product['currency'] ?? '' ) ); ?></em><?php endif; ?></span>
			</div>
		</div>
	</article>
	<?php
}

function vava_selections_render_preview( WP_Post $post, string $section, string $lang ): void {
	$text   = vava_selections_text_data( (int) $post->ID, $lang );
	$shared = vava_selections_shared_data( (int) $post->ID );
	$is_en  = 'en' === $lang;
	?>
	<aside class="vava-live-preview" data-preview-language="<?php echo esc_attr( $lang ); ?>" data-preview-section="<?php echo esc_attr( $section ); ?>" data-selections-preview-panel dir="<?php echo $is_en ? 'ltr' : 'rtl'; ?>">
		<header class="vava-live-preview-header"><div><strong><?php echo esc_html( vava_selections_admin_text( 'live_preview', $lang ) ); ?></strong><span><?php echo esc_html( vava_selections_sections( $lang )[ $section ] ?? '' ); ?></span></div><span class="vava-live-preview-dot" aria-hidden="true"></span></header>
		<div class="vava-preview-viewport"><div class="vava-preview-stage"><div class="vava-preview-canvas vava-selections-preview vava-selections-preview-<?php echo esc_attr( $section ); ?>" data-preview-design-width="900">
		<?php if ( 'hero' === $section ) : $hero_image = vava_selections_image_url( absint( $shared['hero_image_id'] ?? 0 ), 'assets/images/store-2.png', 'medium_large' ); ?>
			<div class="vava-selections-preview-copy"><span data-preview-output="hero-eyebrow"><?php echo esc_html( (string) $text['hero']['eyebrow'] ); ?></span><h3 data-preview-output="hero-title"><?php echo esc_html( (string) $text['hero']['title'] ); ?></h3><p data-preview-output="hero-intro"><?php echo esc_html( (string) $text['hero']['intro'] ); ?></p><small data-preview-output="hero-note"><?php echo esc_html( (string) $text['hero']['note'] ); ?></small></div><div class="vava-selections-preview-hero-image" data-preview-image="hero" style="background-image:url('<?php echo esc_url( $hero_image ); ?>')"></div>
		<?php elseif ( 'collections' === $section ) : ?>
			<div class="vava-selections-preview-collections">
			<?php foreach ( array( 'digital', 'tangible' ) as $group ) : $fallback = 'digital' === $group ? 'assets/images/store-2.png' : 'assets/images/vava_shop_bottles.jpg'; $image = vava_selections_image_url( absint( $shared['collection_images'][ $group ] ?? 0 ), $fallback, 'medium_large' ); ?>
				<article class="vava-selections-preview-collection"><div class="vava-selections-preview-collection-image" data-preview-image="collection-<?php echo esc_attr( $group ); ?>" style="background-image:url('<?php echo esc_url( $image ); ?>')"></div><div><h3 data-preview-output="collection-<?php echo esc_attr( $group ); ?>-title"><?php echo esc_html( (string) $text['collections'][ $group ]['title'] ); ?></h3><p data-preview-output="collection-<?php echo esc_attr( $group ); ?>-description"><?php echo esc_html( (string) $text['collections'][ $group ]['description'] ); ?></p><b data-preview-output="collection-<?php echo esc_attr( $group ); ?>-button"><?php echo esc_html( (string) $text['collections'][ $group ]['button_text'] ); ?></b></div></article>
			<?php endforeach; ?>
			</div>
		<?php elseif ( in_array( $section, array( 'digital', 'tangible' ), true ) ) : $products = vava_selections_products( (int) $post->ID, $section, $lang, true ); ?>
			<div class="vava-selections-preview-products-head"><span data-preview-output="collection-<?php echo esc_attr( $section ); ?>-title"><?php echo esc_html( (string) $text['collections'][ $section ]['title'] ); ?></span><h3 data-preview-output="collection-<?php echo esc_attr( $section ); ?>-description"><?php echo esc_html( (string) $text['collections'][ $section ]['description'] ); ?></h3></div><div class="vava-selections-preview-products" data-preview-products="<?php echo esc_attr( $section ); ?>"><?php if ( $products ) : foreach ( $products as $product ) { vava_selections_render_preview_product_card( $product, $lang, $section ); } else : ?><p class="vava-selections-preview-empty"><?php echo esc_html( vava_selections_admin_text( 'empty_preview', $lang ) ); ?></p><?php endif; ?></div>
		<?php endif; ?>
		</div></div></div>
	</aside>
	<?php
}

function vava_selections_render_settings( WP_Post $post ): void {
	wp_nonce_field( 'vava_selections_save', 'vava_selections_nonce' );
	$sections_ar = vava_selections_sections( 'ar' );
	$sections_en = vava_selections_sections( 'en' );
	$shared      = vava_selections_shared_data( (int) $post->ID );
	?>
	<div class="vava-homepage-admin vava-selections-admin" data-active-language="ar" data-active-section="hero" data-settings-title-ar="<?php echo esc_attr( vava_selections_admin_text( 'meta_title', 'ar' ) ); ?>" data-settings-title-en="<?php echo esc_attr( vava_selections_admin_text( 'meta_title', 'en' ) ); ?>">
		<input data-vava-active-language-input name="_vava_admin_active_language" type="hidden" value="ar"/>
		<?php vava_render_bilingual_page_identity( $post, (string) get_permalink( $post ) ); ?>
		<div class="vava-admin-toolbar"><div class="vava-section-tabs" role="tablist"><?php foreach ( $sections_ar as $id => $label ) : ?><button aria-selected="<?php echo 'hero' === $id ? 'true' : 'false'; ?>" class="vava-section-tab<?php echo 'hero' === $id ? ' is-active' : ''; ?>" data-section="<?php echo esc_attr( $id ); ?>" role="tab" type="button"><span class="vava-tab-icon" aria-hidden="true"><?php echo vava_selections_section_icon( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span<?php echo vava_admin_i18n_attributes( $label, $sections_en[ $id ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $label ); ?></span></button><?php endforeach; ?></div><div class="vava-toolbar-actions"><div class="vava-language-switch" role="group" aria-label="<?php echo esc_attr( vava_selections_admin_text( 'fields_language', 'ar' ) ); ?>"><button class="is-active" data-language="ar" type="button"><span>العربية</span><small>AR</small></button><button data-language="en" type="button"><span>English</span><small>EN</small></button></div><button class="button vava-homepage-update-button" data-vava-submit type="button"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20 12a8 8 0 1 1-2.35-5.65"/><path d="M20 4v6h-6"/></svg><span<?php echo vava_admin_i18n_attributes( vava_selections_admin_text( 'update', 'ar' ), vava_selections_admin_text( 'update', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_selections_admin_text( 'update', 'ar' ) ); ?></span></button></div></div>
		<div class="vava-section-panels">
		<?php foreach ( $sections_ar as $section => $label ) : ?>
			<section class="vava-section-panel<?php echo 'hero' === $section ? ' is-active' : ''; ?>" data-section-panel="<?php echo esc_attr( $section ); ?>">
			<?php foreach ( array( 'ar', 'en' ) as $lang ) : ?>
				<div class="vava-language-pane<?php echo 'ar' === $lang ? ' is-active' : ''; ?>" data-language-pane="<?php echo esc_attr( $lang ); ?>" dir="<?php echo 'en' === $lang ? 'ltr' : 'rtl'; ?>"><div class="vava-editor-workspace"><?php vava_selections_render_preview( $post, $section, $lang ); ?><div class="vava-editor-controls">
				<?php if ( 'hero' === $section ) : ?><div class="vava-fields-grid"><?php vava_selections_render_hero_fields( (int) $post->ID, $lang ); ?></div>
				<?php elseif ( 'collections' === $section ) : ?><?php vava_selections_render_collection_fields( (int) $post->ID, $lang ); ?>
				<?php elseif ( in_array( $section, array( 'digital', 'tangible' ), true ) ) : ?><?php vava_selections_render_products_editor( (int) $post->ID, $section, $lang ); ?><?php endif; ?>
				</div></div></div>
			<?php endforeach; ?>
			<?php if ( 'hero' === $section ) : ?><div class="vava-shared-fields"><h3<?php echo vava_admin_i18n_attributes( vava_selections_admin_text( 'shared', 'ar' ), vava_selections_admin_text( 'shared', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_selections_admin_text( 'shared', 'ar' ) ); ?></h3><?php vava_selections_render_media_field( '_vava_selections_hero_image_id', absint( $shared['hero_image_id'] ?? 0 ), 'assets/images/store-2.png', vava_selections_admin_text( 'hero_image', 'ar' ), vava_selections_admin_text( 'hero_image', 'en' ), 'hero' ); ?></div>
			<?php endif; ?>
			</section>
		<?php endforeach; ?>
		</div>
	</div>
	<?php
}

function vava_selections_sanitize_uid( string $uid, string $group, int $index ): string {
	$uid = sanitize_key( $uid );
	if ( '' !== $uid && '__uid__' !== strtolower( $uid ) ) {
		return $uid;
	}
	return sanitize_key( $group . '-' . wp_generate_uuid4() . '-' . $index );
}

function vava_selections_raw_products( string $lang, string $group ): array {
	$key = '_vava_selections_products_' . $lang;
	if ( ! isset( $_POST[ $key ][ $group ] ) || ! is_array( $_POST[ $key ][ $group ] ) ) {
		return array();
	}
	return array_values( wp_unslash( $_POST[ $key ][ $group ] ) );
}

function vava_selections_sanitize_local_product( array $item, string $uid ): array {
	return array(
		'uid'         => $uid,
		'title'       => sanitize_text_field( (string) ( $item['title'] ?? '' ) ),
		'description' => sanitize_textarea_field( (string) ( $item['description'] ?? '' ) ),
		'currency'    => sanitize_text_field( (string) ( $item['currency'] ?? '' ) ),
		'button_text' => sanitize_text_field( (string) ( $item['button_text'] ?? '' ) ),
	);
}

function vava_selections_save_meta( int $post_id, WP_Post $post ): void {
	if ( ! isset( $_POST['vava_selections_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vava_selections_nonce'] ) ), 'vava_selections_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || 'page' !== $post->post_type || ! vava_selections_is_page( $post_id ) || ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	vava_save_bilingual_page_titles( $post_id );
	foreach ( array( 'ar', 'en' ) as $lang ) {
		$current = vava_selections_text_data( $post_id, $lang );
		$hero    = array();
		foreach ( array( 'eyebrow', 'title', 'intro', 'note' ) as $field ) {
			$key            = '_vava_selections_' . $lang . '_hero_' . $field;
			$hero[ $field ] = isset( $_POST[ $key ] ) ? ( in_array( $field, array( 'intro', 'note' ), true ) ? sanitize_textarea_field( (string) wp_unslash( $_POST[ $key ] ) ) : sanitize_text_field( (string) wp_unslash( $_POST[ $key ] ) ) ) : (string) ( $current['hero'][ $field ] ?? '' );
		}
		$collections = array();
		foreach ( array( 'digital', 'tangible' ) as $group ) {
			$collections[ $group ] = array();
			foreach ( array( 'title', 'description', 'button_text' ) as $field ) {
				$key = '_vava_selections_' . $lang . '_collection_' . $group . '_' . $field;
				$collections[ $group ][ $field ] = isset( $_POST[ $key ] ) ? ( 'description' === $field ? sanitize_textarea_field( (string) wp_unslash( $_POST[ $key ] ) ) : sanitize_text_field( (string) wp_unslash( $_POST[ $key ] ) ) ) : (string) ( $current['collections'][ $group ][ $field ] ?? '' );
			}
		}
		update_post_meta( $post_id, vava_selections_text_meta_key( $lang ), array( 'hero' => $hero, 'collections' => $collections, 'empty' => (array) ( $current['empty'] ?? array() ) ) );
	}

	$shared = vava_selections_shared_data( $post_id );
	if ( isset( $_POST['_vava_selections_hero_image_id'] ) ) {
		$shared['hero_image_id'] = absint( $_POST['_vava_selections_hero_image_id'] );
	}
	foreach ( array( 'digital', 'tangible' ) as $group ) {
		$key = '_vava_selections_collection_' . $group . '_image_id';
		if ( isset( $_POST[ $key ] ) ) {
			$shared['collection_images'][ $group ] = absint( $_POST[ $key ] );
		}
	}

	$active_language = isset( $_POST['_vava_admin_active_language'] ) ? vava_normalize_language( sanitize_key( wp_unslash( $_POST['_vava_admin_active_language'] ) ) ) : 'ar';
	$other_language  = 'en' === $active_language ? 'ar' : 'en';
	$existing_text   = array(
		'ar' => vava_selections_products_text_data( $post_id, 'ar' ),
		'en' => vava_selections_products_text_data( $post_id, 'en' ),
	);
	$new_text = array( 'ar' => array( 'digital' => array(), 'tangible' => array() ), 'en' => array( 'digital' => array(), 'tangible' => array() ) );
	foreach ( array( 'digital', 'tangible' ) as $group ) {
		$raw_active = vava_selections_raw_products( $active_language, $group );
		$raw_other  = vava_selections_raw_products( $other_language, $group );
		if ( ! $raw_active && $raw_other ) {
			$raw_active = $raw_other;
		}
		$other_map = array();
		foreach ( $raw_other as $item ) {
			if ( is_array( $item ) ) {
				$uid = sanitize_key( (string) ( $item['uid'] ?? '' ) );
				if ( $uid ) { $other_map[ $uid ] = $item; }
			}
		}
		$existing_maps = array(
			'ar' => vava_selections_product_text_map( (array) ( $existing_text['ar'][ $group ] ?? array() ) ),
			'en' => vava_selections_product_text_map( (array) ( $existing_text['en'][ $group ] ?? array() ) ),
		);
		$shared['products'][ $group ] = array();
		foreach ( array_slice( $raw_active, 0, 24 ) as $index => $raw_item ) {
			if ( ! is_array( $raw_item ) ) { continue; }
			$uid = vava_selections_sanitize_uid( (string) ( $raw_item['uid'] ?? '' ), $group, $index );
			$shared['products'][ $group ][] = array(
				'uid'            => $uid,
				'image_id'       => absint( $raw_item['image_id'] ?? 0 ),
				'fallback_asset' => sanitize_text_field( (string) ( $raw_item['fallback_asset'] ?? '' ) ),
				'price'          => sanitize_text_field( (string) ( $raw_item['price'] ?? '' ) ),
				'enabled'        => empty( $raw_item['enabled'] ) ? 0 : 1,
			);
			$active_local = vava_selections_sanitize_local_product( $raw_item, $uid );
			$other_raw    = isset( $other_map[ $uid ] ) && is_array( $other_map[ $uid ] ) ? $other_map[ $uid ] : array();
			if ( ! $other_raw && isset( $existing_maps[ $other_language ][ $uid ] ) ) {
				$other_raw = $existing_maps[ $other_language ][ $uid ];
			}
			if ( ! $other_raw ) {
				$other_raw = $active_local;
			}
			$other_local = vava_selections_sanitize_local_product( $other_raw, $uid );
			$new_text[ $active_language ][ $group ][] = $active_local;
			$new_text[ $other_language ][ $group ][]  = $other_local;
		}
	}
	update_post_meta( $post_id, '_vava_selections_shared', $shared );
	update_post_meta( $post_id, vava_selections_products_meta_key( 'ar' ), $new_text['ar'] );
	update_post_meta( $post_id, vava_selections_products_meta_key( 'en' ), $new_text['en'] );
}
add_action( 'save_post_page', 'vava_selections_save_meta', 30, 2 );

/**
 * Remove retired closing-section values from existing VAVA Selections pages.
 */
function vava_selections_remove_legacy_closing_data(): void {
	if ( get_option( 'vava_selections_closing_removed_v1' ) ) {
		return;
	}

	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	foreach ( $pages as $page_id ) {
		$page_id = absint( $page_id );
		if ( ! vava_selections_is_page( $page_id ) ) {
			continue;
		}

		foreach ( array( 'ar', 'en' ) as $lang ) {
			$key  = vava_selections_text_meta_key( $lang );
			$data = get_post_meta( $page_id, $key, true );
			if ( is_array( $data ) && array_key_exists( 'closing', $data ) ) {
				unset( $data['closing'] );
				update_post_meta( $page_id, $key, $data );
			}
		}
	}

	update_option( 'vava_selections_closing_removed_v1', 1, false );
}
add_action( 'admin_init', 'vava_selections_remove_legacy_closing_data', 9 );

/**
 * Remove the retired per-product WordPress page references from saved data.
 */
function vava_selections_remove_linked_product_pages(): void {
	if ( get_option( 'vava_selections_linked_pages_removed_v1' ) ) {
		return;
	}

	$page_ids = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	foreach ( $page_ids as $page_id ) {
		$page_id = absint( $page_id );
		if ( ! vava_selections_is_page( $page_id ) ) {
			continue;
		}

		$shared = get_post_meta( $page_id, '_vava_selections_shared', true );
		if ( ! is_array( $shared ) || empty( $shared['products'] ) || ! is_array( $shared['products'] ) ) {
			continue;
		}

		$changed = false;
		foreach ( array( 'digital', 'tangible' ) as $group ) {
			if ( empty( $shared['products'][ $group ] ) || ! is_array( $shared['products'][ $group ] ) ) {
				continue;
			}
			foreach ( $shared['products'][ $group ] as &$product ) {
				if ( is_array( $product ) && array_key_exists( 'page_id', $product ) ) {
					unset( $product['page_id'] );
					$changed = true;
				}
			}
			unset( $product );
		}

		if ( $changed ) {
			update_post_meta( $page_id, '_vava_selections_shared', $shared );
		}
	}

	update_option( 'vava_selections_linked_pages_removed_v1', 1, false );
}
add_action( 'admin_init', 'vava_selections_remove_linked_product_pages', 10 );

function vava_selections_use_block_editor( bool $use_block_editor, WP_Post $post ): bool {
	return vava_selections_is_page( (int) $post->ID ) ? false : $use_block_editor;
}
add_filter( 'use_block_editor_for_post', 'vava_selections_use_block_editor', 10, 2 );

function vava_selections_admin_body_class( string $classes ): string {
	global $post;
	$post_id = $post instanceof WP_Post ? (int) $post->ID : ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $post_id && vava_selections_is_page( $post_id ) ) {
		$classes .= ' vava-homepage-classic vava-selections-classic';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'vava_selections_admin_body_class' );

function vava_selections_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) {
		return;
	}
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || ! vava_selections_is_page( $post_id ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'vava-homepage-admin', get_theme_file_uri( 'assets/css/admin-homepage.css' ), array(), vava_asset_version( 'assets/css/admin-homepage.css' ) );
	wp_enqueue_style( 'vava-selections-admin', get_theme_file_uri( 'assets/css/admin-selections.css' ), array( 'vava-homepage-admin' ), vava_asset_version( 'assets/css/admin-selections.css' ) );
	wp_enqueue_script( 'vava-selections-admin', get_theme_file_uri( 'assets/js/admin-selections.js' ), array( 'jquery' ), vava_asset_version( 'assets/js/admin-selections.js' ), true );
	wp_localize_script( 'vava-selections-admin', 'vavaSelectionsAdmin', array( 'postId' => $post_id ) );
}
add_action( 'admin_enqueue_scripts', 'vava_selections_admin_assets' );

function vava_selections_assign_or_create_page(): void {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'vava_selections_page_migrated_v1' ) ) {
		return;
	}
	$page = get_page_by_path( 'vava-selections', OBJECT, 'page' );
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
			if ( $candidate instanceof WP_Post && vava_selections_is_page( (int) $candidate->ID ) ) {
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
				'post_title'  => 'مختارات VAVA',
				'post_name'   => 'vava-selections',
			),
			true
		);
		if ( ! is_wp_error( $page_id ) ) {
			$page = get_post( $page_id );
		}
	}
	if ( $page instanceof WP_Post ) {
		update_post_meta( $page->ID, '_wp_page_template', vava_selections_template_slug() );
		update_post_meta( $page->ID, vava_page_title_meta_key( 'ar' ), 'مختارات VAVA' );
		update_post_meta( $page->ID, vava_page_title_meta_key( 'en' ), 'VAVA Selections' );
		update_option( 'vava_selections_page_migrated_v1', (int) $page->ID, false );
	}
}
add_action( 'admin_init', 'vava_selections_assign_or_create_page', 8 );
