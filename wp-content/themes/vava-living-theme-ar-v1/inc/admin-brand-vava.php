<?php
/** VAVA administration identity. @package VAVALiving */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function vava_admin_brand_register_scheme(): void {
	wp_admin_css_color( 'vava', 'VAVA', add_query_arg( 'ver', defined( 'VAVA_THEME_VERSION' ) ? VAVA_THEME_VERSION : '1.22.50', get_template_directory_uri() . '/assets/css/admin-brand-vava.css' ), array( '#59613d', '#788052', '#d77b61', '#f3ece7' ), array( 'base' => '#59613d', 'focus' => '#d77b61', 'current' => '#ffffff' ) );
}
add_action( 'admin_init', 'vava_admin_brand_register_scheme' );

function vava_admin_brand_default_for_unset( $result, string $option, $user ) {
	return 'vava';
}
add_filter( 'get_user_option_admin_color', 'vava_admin_brand_default_for_unset', 10, 3 );

function vava_admin_brand_new_user( int $user_id ): void { update_user_meta( $user_id, 'admin_color', 'vava' ); }
add_action( 'user_register', 'vava_admin_brand_new_user' );

function vava_admin_brand_migrate_once(): void {
	if ( get_option( 'vava_admin_brand_default_v2' ) ) { return; }
	foreach ( get_users( array( 'fields' => 'ids' ) ) as $user_id ) {
		update_user_meta( $user_id, 'admin_color', 'vava' );
	}
	update_option( 'vava_admin_brand_default_v2', wp_date( 'c' ), false );
}
add_action( 'admin_init', 'vava_admin_brand_migrate_once', 20 );

function vava_admin_brand_is_active(): bool { return true; }
add_filter( 'show_admin_color_scheme_picker', '__return_false', 999 );

function vava_admin_brand_contact_methods( array $methods ): array {
	$methods['vava_whatsapp'] = is_rtl() ? 'رقم WhatsApp' : 'WhatsApp number';
	return $methods;
}
add_filter( 'user_contactmethods', 'vava_admin_brand_contact_methods', 20 );

function vava_admin_brand_avatar_url( int $user_id, int $size = 160 ): string {
	$attachment_id = absint( get_user_meta( $user_id, '_vava_admin_avatar_id', true ) );
	$url = $attachment_id ? wp_get_attachment_image_url( $attachment_id, array( $size, $size ) ) : '';
	return $url ? (string) $url : get_avatar_url( $user_id, array( 'size' => $size ) );
}

function vava_admin_brand_avatar_override( $avatar, $id_or_email, $size, $default, $alt ) {
	$user = false;
	if ( $id_or_email instanceof WP_User ) { $user = $id_or_email; }
	elseif ( is_numeric( $id_or_email ) ) { $user = get_user_by( 'id', absint( $id_or_email ) ); }
	elseif ( $id_or_email instanceof WP_Comment ) { $user = get_user_by( 'id', (int) $id_or_email->user_id ); }
	elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) { $user = get_user_by( 'id', (int) $id_or_email->user_id ); }
	elseif ( is_string( $id_or_email ) ) { $user = get_user_by( 'email', $id_or_email ); }
	if ( ! $user || ! get_user_meta( $user->ID, '_vava_admin_avatar_id', true ) ) { return $avatar; }
	$url = vava_admin_brand_avatar_url( $user->ID, (int) $size );
	return '<img alt="' . esc_attr( $alt ) . '" src="' . esc_url( $url ) . '" class="avatar avatar-' . absint( $size ) . ' photo" height="' . absint( $size ) . '" width="' . absint( $size ) . '" loading="lazy" decoding="async" />';
}
add_filter( 'get_avatar', 'vava_admin_brand_avatar_override', 20, 5 );

function vava_admin_brand_profile_avatar( WP_User $user ): void {
	if ( ! current_user_can( 'edit_user', $user->ID ) ) { return; }
	$avatar_id = absint( get_user_meta( $user->ID, '_vava_admin_avatar_id', true ) );
	?>
	<table class="form-table vava-native-avatar-table" role="presentation"><tr class="vava-avatar-row"><th><label><?php echo esc_html( is_rtl() ? 'صورة الحساب' : 'Profile photo' ); ?></label></th><td>
		<div class="vava-avatar-control"><img data-vava-avatar-preview src="<?php echo esc_url( vava_admin_brand_avatar_url( $user->ID ) ); ?>" alt=""><div><input type="hidden" name="vava_admin_avatar_id" value="<?php echo esc_attr( (string) $avatar_id ); ?>" data-vava-avatar-id><button type="button" class="button button-secondary" data-vava-avatar-pick><?php echo esc_html( is_rtl() ? 'رفع صورة خاصة' : 'Upload a photo' ); ?></button><button type="button" class="button button-link-delete" data-vava-avatar-remove><?php echo esc_html( is_rtl() ? 'إزالة الصورة' : 'Remove photo' ); ?></button><p class="description"><?php echo esc_html( is_rtl() ? 'اختر صورة من جهازك أو مكتبة الوسائط.' : 'Choose an image from your device or Media Library.' ); ?></p></div></div>
	</td></tr></table>
	<?php
}
add_action( 'show_user_profile', 'vava_admin_brand_profile_avatar', 8 );
add_action( 'edit_user_profile', 'vava_admin_brand_profile_avatar', 8 );

function vava_admin_brand_save_profile_avatar( int $user_id ): void {
	if ( ! current_user_can( 'edit_user', $user_id ) || ! isset( $_POST['vava_admin_avatar_id'] ) ) { return; }
	$attachment_id = absint( wp_unslash( $_POST['vava_admin_avatar_id'] ) );
	if ( $attachment_id && ! wp_attachment_is_image( $attachment_id ) ) { return; }
	update_user_meta( $user_id, '_vava_admin_avatar_id', $attachment_id );
}
add_action( 'personal_options_update', 'vava_admin_brand_save_profile_avatar' );
add_action( 'edit_user_profile_update', 'vava_admin_brand_save_profile_avatar' );

/** Ensure VAVA administrators can upload a profile image through the native media modal. */
function vava_admin_brand_profile_upload_cap( array $allcaps ): array {
	if ( ! empty( $allcaps['manage_options'] ) ) {
		$allcaps['upload_files'] = true;
	}
	return $allcaps;
}
add_filter( 'user_has_cap', 'vava_admin_brand_profile_upload_cap', 20 );

function vava_admin_brand_profile_image_mimes( array $mimes ): array {
	if ( current_user_can( 'manage_options' ) ) {
		$mimes['jpg|jpeg|jpe'] = 'image/jpeg';
		$mimes['png']          = 'image/png';
		$mimes['gif']          = 'image/gif';
		$mimes['webp']         = 'image/webp';
	}
	return $mimes;
}
add_filter( 'upload_mimes', 'vava_admin_brand_profile_image_mimes', 20 );
function vava_admin_brand_body_class( string $classes ): string { return vava_admin_brand_is_active() ? $classes . ' vava-admin-brand' : $classes; }
add_filter( 'admin_body_class', 'vava_admin_brand_body_class' );

function vava_admin_brand_assets(): void {
	if ( ! vava_admin_brand_is_active() ) { return; }
	wp_enqueue_style( 'vava-admin-typography', 'https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800&display=swap', array(), null );
	wp_enqueue_style( 'vava-admin-brand-ui', get_template_directory_uri() . '/assets/css/admin-brand-vava-ui.css', array( 'vava-admin-typography' ), '1.22.42' );
	wp_enqueue_script( 'vava-admin-brand-ui', get_template_directory_uri() . '/assets/js/admin-brand-vava.js', array( 'jquery' ), '1.22.42', true );
	wp_enqueue_script( 'vava-admin-brand-dashboard-fix', get_template_directory_uri() . '/assets/js/admin-brand-vava-dashboard-fix.js', array(), '1.22.17', true );
	if ( in_array( get_current_screen() ? get_current_screen()->base : '', array( 'profile', 'user-edit' ), true ) ) { wp_enqueue_media(); }
	$user = wp_get_current_user();
	$targets = function_exists( 'vava_admin_curated_page_targets' ) ? vava_admin_curated_page_targets() : array();
	$booking_page_id = absint( $targets['vava-edit-booking-page'] ?? 0 );
	$journal_page_id = absint( $targets['vava-edit-journal'] ?? 0 );
	$operational_templates = array( 'page-templates/booking-vava.php', 'page-templates/my-bookings-vava.php', 'page-templates/digital-product-checkout-vava.php', 'page-templates/digital-library-viewer-vava.php', 'page-templates/paths-vava.php', 'page-templates/selections-vava.php', 'page-templates/journal-vava.php' );
	$site_page_ids = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true ) );
	$site_page_ids = array_values( array_filter( $site_page_ids, static function ( $page_id ) use ( $operational_templates ) { return ! in_array( get_page_template_slug( $page_id ), $operational_templates, true ); } ) );
	$published_pages = count( $site_page_ids );
	$appointment_ids = post_type_exists( 'vava_booking' ) ? get_posts( array( 'post_type' => 'vava_booking', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true, 'meta_query' => array( 'relation' => 'OR', array( 'key' => '_vava_booking_order_type', 'compare' => 'NOT EXISTS' ), array( 'key' => '_vava_booking_order_type', 'value' => 'digital_product', 'compare' => '!=' ) ) ) ) : array();
	$product_order_ids = post_type_exists( 'vava_booking' ) ? get_posts( array( 'post_type' => 'vava_booking', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true, 'meta_key' => '_vava_booking_order_type', 'meta_value' => 'digital_product' ) ) : array();
	$pending_statuses = array( 'pending', 'pending_payment', 'pending_bank_review' );
	$pending_bookings = count( array_filter( $appointment_ids, static function ( $post_id ) use ( $pending_statuses ) { return in_array( (string) get_post_meta( $post_id, '_vava_booking_status', true ), $pending_statuses, true ); } ) );
	$new_product_orders = count( array_filter( $product_order_ids, static function ( $post_id ) use ( $pending_statuses ) { return in_array( (string) get_post_meta( $post_id, '_vava_booking_status', true ), $pending_statuses, true ); } ) );
	$recent_booking_id = $appointment_ids ? absint( $appointment_ids[0] ) : 0;
	$recent_product_id = $product_order_ids ? absint( $product_order_ids[0] ) : 0;
	$recent_booking_text = $recent_booking_id ? get_the_title( $recent_booking_id ) : ( is_rtl() ? 'لا يوجد نشاط حديث' : 'No recent activity' );
	$recent_product_text = $recent_product_id ? get_the_title( $recent_product_id ) : ( is_rtl() ? 'لا توجد طلبات حديثة' : 'No recent orders' );
	$post_counts = wp_count_posts( 'post' );
	$journal_articles = $post_counts ? absint( $post_counts->publish ?? 0 ) : 0;
	$recent_article = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'DESC' ) );
	$recent_article_text = $recent_article ? get_the_title( $recent_article[0] ) : ( is_rtl() ? 'لا توجد مقالات منشورة' : 'No published articles' );
	wp_localize_script( 'vava-admin-brand-ui', 'vavaAdminBrand', array(
		'logo' => get_template_directory_uri() . '/assets/images/vava-logo.png', 'treeImage' => get_template_directory_uri() . '/assets/images/vava-approved-tree-with-leaf-platforms-v3.png', 'backgroundImage' => get_template_directory_uri() . '/assets/images/vava-approved-dashboard-background-v1.png', 'displayName' => $user->display_name,
		'avatar' => vava_admin_brand_avatar_url( $user->ID, 160 ), 'profileUrl' => get_edit_profile_url( $user->ID ), 'logoutUrl' => wp_logout_url(),
		'productsMenuUrl' => add_query_arg( array( 'post_type' => 'vava_booking', 'vava_order_scope' => 'products' ), admin_url( 'edit.php' ) ),
		'homeUrl' => home_url( '/' ),
		'roleText' => is_rtl() ? 'مدير الموقع' : 'Site administrator', 'statusText' => is_rtl() ? 'الحساب نشط' : 'Active account', 'logoutText' => is_rtl() ? 'تسجيل الخروج' : 'Log out', 'loginText' => is_rtl() ? 'تسجيل الدخول' : 'Log in',
		'dashboard' => array(
			'pages' => admin_url( 'edit.php?post_type=page' ),
			'bookings' => admin_url( 'edit.php?post_type=vava_booking' ),
			'orders' => add_query_arg( array( 'post_type' => 'vava_booking', 'vava_order_scope' => 'products' ), admin_url( 'edit.php' ) ),
			'journal' => $journal_page_id ? admin_url( 'post.php?post=' . $journal_page_id . '&action=edit' ) : admin_url( 'edit.php' ),
			'booking' => $booking_page_id ? admin_url( 'post.php?post=' . $booking_page_id . '&action=edit' ) : admin_url( 'options-general.php' ),
			'stats' => array(
				'pages' => array( 'primary' => $published_pages, 'activity' => is_rtl() ? 'صفحات الموقع الظاهرة في هذه القائمة فقط' : 'Only pages shown in this section' ),
				'bookings' => array( 'primary' => count( $appointment_ids ), 'secondary' => $pending_bookings, 'activity' => $recent_booking_text ),
				'orders' => array( 'primary' => count( $product_order_ids ), 'secondary' => $new_product_orders, 'activity' => $recent_product_text ),
				'journal' => array( 'primary' => $journal_articles, 'activity' => $recent_article_text ),
				'booking' => array( 'hideStats' => true, 'activity' => is_rtl() ? 'إعدادات صفحة الحجز متاحة' : 'Booking page settings available' ),
			),
		),
	) );
}
add_action( 'admin_enqueue_scripts', 'vava_admin_brand_assets', 999 );

function vava_admin_brand_hide_bar( bool $show ): bool { return is_admin() && vava_admin_brand_is_active() ? false : $show; }
add_filter( 'show_admin_bar', 'vava_admin_brand_hide_bar', 999 );

/** Keep the future custom dashboard visually clean without affecting other screens. */
function vava_admin_brand_dashboard_screen_options( bool $show ): bool {
	global $pagenow;
	return vava_admin_brand_is_active() && 'index.php' === (string) $pagenow ? false : $show;
}
add_filter( 'screen_options_show_screen', 'vava_admin_brand_dashboard_screen_options', 999 );

function vava_admin_brand_dashboard_help(): void {
	if ( vava_admin_brand_is_active() ) {
		$screen = get_current_screen();
		if ( $screen ) { $screen->remove_help_tabs(); }
	}
}
add_action( 'current_screen', 'vava_admin_brand_dashboard_help', 999 );

/** Curate the WordPress navigation around the VAVA content workflow. */
function vava_admin_brand_curate_menu(): void {
	global $menu, $submenu;
	remove_menu_page( 'themes.php' );
	remove_menu_page( 'edit-comments.php' );
	remove_menu_page( 'tools.php' );
	remove_submenu_page( 'index.php', 'update-core.php' );
	add_submenu_page( 'edit.php?post_type=page', is_rtl() ? 'القوائم' : 'Menus', is_rtl() ? 'القوائم' : 'Menus', 'edit_theme_options', 'nav-menus.php' );
	add_submenu_page( 'options-general.php', is_rtl() ? 'التحديثات' : 'Updates', is_rtl() ? 'التحديثات' : 'Updates', 'update_core', 'update-core.php' );

	/* Keep the Journal page first under Posts and use the approved short label. */
	if ( isset( $submenu['edit.php'] ) && is_array( $submenu['edit.php'] ) ) {
		$journal = array();
		$others  = array();
		foreach ( $submenu['edit.php'] as $item ) {
			$slug = isset( $item[2] ) ? (string) $item[2] : '';
			if ( false !== strpos( $slug, 'taxonomy=post_tag' ) ) {
				continue;
			}
			if ( 'vava-edit-journal' === $slug ) {
				$item[0]   = is_rtl() ? 'الإعدادات' : 'Settings';
				$journal[] = $item;
			} else {
				$others[] = $item;
			}
		}
		$submenu['edit.php'] = array_merge( $journal, $others );
	}

	/* Present WordPress users as VAVA customers without changing capabilities. */
	foreach ( $menu as &$menu_item ) {
		if ( isset( $menu_item[2] ) && 'users.php' === (string) $menu_item[2] ) {
			$menu_item[0] = is_rtl() ? 'العملاء' : 'Customers';
		}
	}
	unset( $menu_item );
	if ( isset( $submenu['users.php'] ) && is_array( $submenu['users.php'] ) ) {
		foreach ( $submenu['users.php'] as &$user_item ) {
			$slug = isset( $user_item[2] ) ? (string) $user_item[2] : '';
			if ( 'users.php' === $slug ) { $user_item[0] = is_rtl() ? 'كافة العملاء' : 'All Customers'; }
			if ( 'user-new.php' === $slug ) { $user_item[0] = is_rtl() ? 'إضافة عميل جديد' : 'Add New Customer'; }
		}
		unset( $user_item );
	}
}
add_action( 'admin_menu', 'vava_admin_brand_curate_menu', 9999 );

/**
 * Keep both VAVA operational groups visible on the isolated customer screens.
 *
 * functions.php intentionally returns early on the native user screens, so the
 * booking module which normally registers these menu nodes is not loaded for
 * those requests. Register lightweight navigation-only equivalents here; the
 * destination request loads the full module and its normal callbacks.
 */
function vava_admin_brand_customer_screen_operational_menus(): void {
	global $pagenow;
	if ( ! in_array( (string) $pagenow, array( 'users.php', 'user-edit.php', 'profile.php' ), true ) ) { return; }

	$capability    = 'manage_options';
	$bookings_slug = 'edit.php?post_type=vava_booking';
	$products_url  = add_query_arg( array( 'post_type' => 'vava_booking', 'vava_order_scope' => 'products' ), admin_url( 'edit.php' ) );

	/*
	 * This is a navigation-only fallback. Keep the native admin path relative
	 * and do not register a callback, otherwise WordPress converts it into an
	 * admin.php?page=... plugin-page URL on the isolated customer screens.
	 */
	add_menu_page( 'حجوزات VAVA', 'حجوزات VAVA', $capability, $bookings_slug, '', 'dashicons-calendar-alt', 26 );
	add_submenu_page( $bookings_slug, 'مسارات VAVA', 'مسارات VAVA', $capability, 'vava-edit-paths', '__return_null' );

	add_menu_page( 'منتجات VAVA', 'منتجات VAVA', $capability, 'vava-products-orders', '__return_null', 'dashicons-products', 27 );
	add_submenu_page( 'vava-products-orders', 'مختارات VAVA', 'مختارات VAVA', $capability, 'vava-edit-selections', '__return_null' );

	global $submenu;
	if ( isset( $submenu['vava-products-orders'][0] ) ) {
		$submenu['vava-products-orders'][0][0] = 'طلبات المنتجات';
		$submenu['vava-products-orders'][0][2] = $products_url;
	}
}
add_action( 'admin_menu', 'vava_admin_brand_customer_screen_operational_menus', 90 );

function vava_admin_brand_customer_wording( $translated, $text, $domain ) {
	global $pagenow;
	if ( ! is_admin() || ! in_array( (string) $pagenow, array( 'users.php', 'user-new.php', 'user-edit.php' ), true ) ) { return $translated; }
	$map = array(
		'Users' => 'العملاء', 'All Users' => 'كافة العملاء', 'Add New User' => 'إضافة عميل جديد',
		'User' => 'عميل', 'user' => 'عميل', 'Member' => 'عميل', 'member' => 'عميل',
		'Add User' => 'إضافة عميل', 'New User' => 'عميل جديد',
		'New user created.' => 'تم إنشاء العميل الجديد.',
		'Edit User' => 'تحرير العميل', 'Edit Member' => 'تحرير العميل', 'Edit member' => 'تحرير العميل', 'User updated.' => 'تم تحديث بيانات العميل.',
		'Update User' => 'تحديث بيانات العميل', 'View User' => 'عرض العميل',
		'Delete Users' => 'حذف العملاء', 'Delete User' => 'حذف العميل',
		'Search Users' => 'بحث في العملاء', 'Number of users per page:' => 'عدد العملاء في الصفحة:',
		'User added.' => 'تمت إضافة العميل.', 'User deleted.' => 'تم حذف العميل.',
		'Users deleted.' => 'تم حذف العملاء.', 'User cannot be added to this site.' => 'لا يمكن إضافة العميل إلى هذا الموقع.',
	);
	return isset( $map[ $text ] ) ? $map[ $text ] : $translated;
}
add_filter( 'gettext', 'vava_admin_brand_customer_wording', 999, 3 );

/** Cover contextual labels used by some WordPress screens and extensions. */
function vava_admin_brand_customer_wording_context( $translated, $text, $context, $domain ) {
	return vava_admin_brand_customer_wording( $translated, $text, $domain );
}
add_filter( 'gettext_with_context', 'vava_admin_brand_customer_wording_context', 999, 4 );

/** Keep the editorial and VAVA workflows grouped in a predictable order. */
function vava_admin_brand_order_menu(): void {
	global $menu;
	if ( ! is_array( $menu ) ) { return; }

	$wanted = array(
		'index.php',
		'edit.php',
		'edit.php?post_type=page',
		'edit.php?post_type=vava_booking',
		'vava-products-orders',
		'upload.php',
		'plugins.php',
		'users.php',
		'options-general.php',
	);
	$found = array();
	$rest  = array();
	foreach ( $menu as $item ) {
		$slug = isset( $item[2] ) ? (string) $item[2] : '';
		if ( in_array( $slug, $wanted, true ) ) { $found[ $slug ] = $item; }
		elseif ( 0 !== strpos( $slug, 'separator' ) ) { $rest[] = $item; }
	}

	$rebuilt = array();
	foreach ( array( 'index.php', 'edit.php', 'edit.php?post_type=page' ) as $slug ) {
		if ( isset( $found[ $slug ] ) ) { $rebuilt[] = $found[ $slug ]; }
	}
	$rebuilt[] = array( '', 'read', 'separator-vava-content', '', 'wp-menu-separator vava-menu-separator vava-menu-separator-content' );
	foreach ( array( 'edit.php?post_type=vava_booking', 'vava-products-orders' ) as $slug ) {
		if ( isset( $found[ $slug ] ) ) { $rebuilt[] = $found[ $slug ]; }
	}
	$rebuilt[] = array( '', 'read', 'separator-vava-system', '', 'wp-menu-separator vava-menu-separator vava-menu-separator-system' );
	foreach ( array( 'upload.php', 'plugins.php', 'users.php', 'options-general.php' ) as $slug ) {
		if ( isset( $found[ $slug ] ) ) { $rebuilt[] = $found[ $slug ]; }
	}
	foreach ( $rest as $item ) {
		$slug = isset( $item[2] ) ? (string) $item[2] : '';
		if ( ! in_array( $slug, $wanted, true ) ) { $rebuilt[] = $item; }
	}
	$menu = array_values( $rebuilt );
}
add_action( 'admin_menu', 'vava_admin_brand_order_menu', 10000 );

function vava_admin_brand_moved_menu_parent( string $parent_file ): string {
	global $pagenow;
	return 'nav-menus.php' === (string) $pagenow ? 'edit.php?post_type=page' : ( 'update-core.php' === (string) $pagenow ? 'options-general.php' : $parent_file );
}
add_filter( 'parent_file', 'vava_admin_brand_moved_menu_parent', 999 );

function vava_admin_brand_remove_footer(): void {
	remove_filter( 'admin_footer_text', 'update_footer', 10 );
}
add_action( 'admin_init', 'vava_admin_brand_remove_footer', 999 );
add_filter( 'admin_footer_text', '__return_empty_string', 999 );
add_filter( 'update_footer', '__return_empty_string', 999 );

function vava_admin_brand_dashboard_cleanup(): void {
	if ( ! vava_admin_brand_is_active() ) { return; }
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_action( 'welcome_panel', 'wp_welcome_panel' );
}
add_action( 'wp_dashboard_setup', 'vava_admin_brand_dashboard_cleanup', 999 );

/** Apply the VAVA identity to the WordPress authentication screen. */
function vava_admin_brand_login_assets(): void {
	wp_enqueue_style( 'vava-login-typography', 'https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800&display=swap', array(), null );
	wp_enqueue_style( 'vava-login-ui', get_template_directory_uri() . '/assets/css/login-vava.css', array( 'vava-login-typography' ), '1.22.34' );
	wp_enqueue_script( 'vava-login-ui', get_template_directory_uri() . '/assets/js/login-vava.js', array(), '1.22.33', true );
	wp_localize_script( 'vava-login-ui', 'vavaLoginGuard', array(
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'vava_login_hold' ),
		'duration' => 3,
		'rtl'      => is_rtl(),
	) );
}
add_action( 'login_enqueue_scripts', 'vava_admin_brand_login_assets', 999 );

function vava_admin_brand_login_logo_url(): string { return home_url( '/' ); }
add_filter( 'login_headerurl', 'vava_admin_brand_login_logo_url' );

function vava_admin_brand_login_logo_title(): string { return get_bloginfo( 'name' ); }
add_filter( 'login_headertext', 'vava_admin_brand_login_logo_title' );

function vava_admin_brand_login_body_class( array $classes ): array {
	$classes[] = 'vava-login';
	if ( isset( $_GET['loggedout'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['loggedout'] ) ) ) {
		$classes[] = 'vava-login-logged-out';
	}
	return $classes;
}
add_filter( 'login_body_class', 'vava_admin_brand_login_body_class' );

/** Replace the generic WordPress logged-out notice with the approved VAVA message. */
function vava_admin_brand_login_message( string $message ): string {
	if ( isset( $_GET['loggedout'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['loggedout'] ) ) ) {
		$success_text = is_rtl() ? 'تم تسجيل الخروج بنجاح.' : 'You have logged out successfully.';
		return '<div class="message vava-login-success" role="status"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><span>' . esc_html( $success_text ) . '</span></div>';
	}

	return $message;
}
add_filter( 'login_message', 'vava_admin_brand_login_message', 999 );

/** Give every authentication error the same VAVA component used by success notices. */
function vava_admin_brand_login_errors( string $message ): string {
	if ( '' === trim( wp_strip_all_tags( $message ) ) ) { return $message; }
	return '<span class="vava-login-notice-content"><span class="dashicons dashicons-warning" aria-hidden="true"></span><span>' . wp_kses_post( $message ) . '</span></span>';
}
add_filter( 'login_errors', 'vava_admin_brand_login_errors', 999 );

function vava_admin_brand_login_remote_address(): string {
	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
}

function vava_admin_brand_login_hold_key( string $kind, string $value ): string {
	return 'vava_login_' . sanitize_key( $kind ) . '_' . md5( wp_salt( 'auth' ) . '|' . $value );
}

/** Start a short-lived, IP-bound press-and-hold challenge. */
function vava_admin_brand_login_hold_start(): void {
	check_ajax_referer( 'vava_login_hold', 'nonce' );
	$challenge = wp_generate_password( 40, false, false );
	set_transient( vava_admin_brand_login_hold_key( 'challenge', $challenge ), array(
		'ip'      => vava_admin_brand_login_remote_address(),
		'started' => microtime( true ),
	), 2 * MINUTE_IN_SECONDS );
	wp_send_json_success( array( 'challenge' => $challenge, 'duration' => 3 ) );
}
add_action( 'wp_ajax_nopriv_vava_login_hold_start', 'vava_admin_brand_login_hold_start' );

/** Verify elapsed hold time and issue a one-use login token. */
function vava_admin_brand_login_hold_verify(): void {
	check_ajax_referer( 'vava_login_hold', 'nonce' );
	$challenge = isset( $_POST['challenge'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', (string) wp_unslash( $_POST['challenge'] ) ) : '';
	$key       = vava_admin_brand_login_hold_key( 'challenge', $challenge );
	$data      = $challenge ? get_transient( $key ) : false;
	if ( $challenge ) { delete_transient( $key ); }
	if ( ! is_array( $data ) || (string) ( $data['ip'] ?? '' ) !== vava_admin_brand_login_remote_address() || microtime( true ) - (float) ( $data['started'] ?? 0 ) < 2.85 ) {
		wp_send_json_error( array( 'message' => 'invalid' ), 403 );
	}
	$token = wp_generate_password( 48, false, false );
	set_transient( vava_admin_brand_login_hold_key( 'verified', $token ), vava_admin_brand_login_remote_address(), 10 * MINUTE_IN_SECONDS );
	wp_send_json_success( array( 'token' => $token ) );
}
add_action( 'wp_ajax_nopriv_vava_login_hold_verify', 'vava_admin_brand_login_hold_verify' );

/** Render the guard inside the native login form, immediately before submit. */
function vava_admin_brand_login_guard_markup(): void {
	$is_rtl = is_rtl();
	?>
	<input type="hidden" name="vava_login_hold_token" value="" data-vava-login-token>
	<button class="vava-login-hold" type="button" data-vava-login-hold data-idle="<?php echo esc_attr( $is_rtl ? 'اضغط مطولًا للاستمرار' : 'Press and hold to continue' ); ?>" data-active="<?php echo esc_attr( $is_rtl ? 'استمر في الضغط للتحقق' : 'Keep holding to verify' ); ?>" data-verified="<?php echo esc_attr( $is_rtl ? 'تم التحقق — يمكنك الدخول' : 'Verified — you can log in' ); ?>" data-error="<?php echo esc_attr( $is_rtl ? 'لم يكتمل التحقق، حاول مرة أخرى' : 'Verification incomplete. Try again.' ); ?>">
		<span class="vava-login-hold-icon dashicons dashicons-shield" aria-hidden="true"></span>
		<span class="vava-login-hold-copy"><strong data-vava-login-hold-label><?php echo esc_html( $is_rtl ? 'اضغط مطولًا للاستمرار' : 'Press and hold to continue' ); ?></strong><small><?php echo esc_html( $is_rtl ? 'خطوة حماية قبل تسجيل الدخول' : 'A security step before login' ); ?></small></span>
		<span class="vava-login-hold-percent" data-vava-login-hold-percent>0%</span>
	</button>
	<?php
}
add_action( 'login_form', 'vava_admin_brand_login_guard_markup' );

/** Reject direct or replayed login posts that bypass the VAVA guard. */
function vava_admin_brand_validate_login_guard( $user, string $username, string $password ) {
	$request_uri = (string) ( $_SERVER['REQUEST_URI'] ?? '' );
	if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) || false === strpos( $request_uri, 'wp-login.php' ) || isset( $_POST['interim-login'] ) ) { return $user; }
	$token = isset( $_POST['vava_login_hold_token'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', (string) wp_unslash( $_POST['vava_login_hold_token'] ) ) : '';
	$key   = vava_admin_brand_login_hold_key( 'verified', $token );
	$ip    = $token ? get_transient( $key ) : false;
	if ( $token ) { delete_transient( $key ); }
	if ( ! is_string( $ip ) || $ip !== vava_admin_brand_login_remote_address() ) {
		return new WP_Error( 'vava_login_guard', is_rtl() ? 'أكمل خطوة «اضغط مطولًا للاستمرار» قبل تسجيل الدخول.' : 'Complete the press-and-hold step before logging in.' );
	}
	return $user;
}
add_filter( 'authenticate', 'vava_admin_brand_validate_login_guard', 5, 3 );
