<?php
/**
 * Theme bootstrap for VAVA Living.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'VAVA_THEME_VERSION' ) ) {
	define( 'VAVA_THEME_VERSION', '1.22.60' );
}

if ( ! defined( 'VAVA_LANGUAGE_COOKIE' ) ) {
	define( 'VAVA_LANGUAGE_COOKIE', 'vava_site_language' );
}

/* Load the VAVA admin identity before the user-screen safety return below. */
require_once get_template_directory() . '/inc/admin-brand-vava.php';

/**
 * Hard-isolate the native WordPress user-management screens.
 *
 * These requests do not need any VAVA page editor, booking, product, reader,
 * migration or page-assignment module. The check deliberately runs before the
 * first require_once so a failure inside any optional VAVA module cannot take
 * down users.php or user-edit.php.
 */
$vava_admin_request_script = '';
if ( ! empty( $_SERVER['SCRIPT_NAME'] ) ) {
	$vava_admin_request_script = basename( str_replace( '\\', '/', (string) $_SERVER['SCRIPT_NAME'] ) );
} elseif ( ! empty( $_SERVER['PHP_SELF'] ) ) {
	$vava_admin_request_script = basename( str_replace( '\\', '/', (string) $_SERVER['PHP_SELF'] ) );
} elseif ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
	$vava_admin_request_path   = (string) parse_url( (string) $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	$vava_admin_request_script = basename( str_replace( '\\', '/', $vava_admin_request_path ) );
}

if (
	defined( 'WP_ADMIN' ) && WP_ADMIN
	&& in_array( $vava_admin_request_script, array( 'users.php', 'user-edit.php', 'profile.php' ), true )
) {
	return;
}

require_once get_template_directory() . '/inc/homepage-metaboxes.php';
require_once get_template_directory() . '/inc/about-vava-metaboxes.php';
require_once get_template_directory() . '/inc/paths-vava.php';
require_once get_template_directory() . '/inc/paths-vava-advanced.php';
// Emergency hard recovery: load known-stable modules under new filenames to bypass stale OPcache and broken prior replacements.
require_once get_template_directory() . '/inc/booking-vava-safe-v4r10.php';
require_once get_template_directory() . '/inc/booking-questionnaires-vava.php';
require_once get_template_directory() . '/inc/customer-account-vava-safe-v4r10.php';
require_once get_template_directory() . '/inc/selections-vava.php';
require_once get_template_directory() . '/inc/digital-products-vava.php';
require_once get_template_directory() . '/inc/digital-products-commerce-vava.php';
require_once get_template_directory() . '/inc/journal-vava.php';
require_once get_template_directory() . '/inc/article-i18n-vava.php';
require_once get_template_directory() . '/inc/contact-vava.php';
require_once get_template_directory() . '/inc/legal-vava.php';
require_once get_template_directory() . '/inc/final-delivery-v1.php';
require_once get_template_directory() . '/inc/final-qa-v2.php';
require_once get_template_directory() . '/inc/client-final-round-v3.php';


/**
 * Keep the native WordPress Users screen isolated from VAVA page editors.
 *
 * The VAVA modules do not need to run migrations, page assignment or editor
 * preparation while users.php is loading. Removing those request-specific
 * hooks prevents a failure in any advanced-page module from taking down the
 * core member-management screen while leaving the registered customer role
 * and all front-end account functions available.
 */
function vava_users_admin_screen_safety_guard(): void {
	global $pagenow;
	if ( 'users.php' !== (string) $pagenow ) { return; }

	$admin_init_hooks = array(
		array( 'vava_booking_redirect_legacy_details_url', 0 ),
		array( 'vava_customer_restrict_admin', 1 ),
		array( 'vava_paths_prepare_admin_editor', 1 ),
		array( 'vava_paths_assign_or_create_page', 5 ),
		array( 'vava_selections_assign_or_create_page', 8 ),
		array( 'vava_journal_assign_or_create_page', 8 ),
		array( 'vava_selections_remove_legacy_closing_data', 9 ),
		array( 'vava_digital_products_create_system_pages', 18 ),
		array( 'vava_digital_products_maybe_migrate_legacy_files', 25 ),
		array( 'vava_about_migrate_text_model', 30 ),
		array( 'vava_booking_prepare_advanced_editor', 35 ),
		array( 'vava_contact_assign_or_create_page', 40 ),
		array( 'vava_paths_maybe_sync_existing_sessions', 40 ),
		array( 'vava_legal_assign_or_create_pages', 42 ),
		array( 'vava_booking_assign_or_create_my_bookings_page', 45 ),
		array( 'vava_paths_refresh_session_links_after_migration', 45 ),
		array( 'vava_paths_maybe_repair_empty_session_rows', 46 ),
		array( 'vava_homepage_assign_existing_page', 10 ),
		array( 'vava_about_assign_or_create_page', 10 ),
		array( 'vava_booking_assign_or_create_page', 10 ),
		array( 'vava_booking_maybe_flush_legacy_rewrites', 10 ),
		array( 'vava_paths_maybe_flush_session_rewrites', 10 )
	);
	foreach ( $admin_init_hooks as $hook ) {
		if ( function_exists( $hook[0] ) ) { remove_action( 'admin_init', $hook[0], $hook[1] ); }
	}

	if ( function_exists( 'vava_booking_admin_dashboard_header' ) ) { remove_action( 'all_admin_notices', 'vava_booking_admin_dashboard_header' ); }
	if ( function_exists( 'vava_booking_admin_review_notice' ) ) { remove_action( 'admin_notices', 'vava_booking_admin_review_notice' ); }
}
add_action( 'admin_init', 'vava_users_admin_screen_safety_guard', -999 );

/**
 * Normalize a supported site language.
 */
function vava_normalize_language( $language ): string {
	return 'en' === strtolower( (string) $language ) ? 'en' : 'ar';
}

/**
 * Detect whether the current request already carries a saved language choice.
 */
function vava_has_language_preference(): bool {
	return isset( $_GET['vava_lang'] ) || isset( $_COOKIE[ VAVA_LANGUAGE_COOKIE ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/**
 * Return the globally selected site language.
 *
 * Query string wins for the current request, then the persistent cookie.
 */
function vava_current_language(): string {
	if ( isset( $_GET['vava_lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$requested = sanitize_key( wp_unslash( $_GET['vava_lang'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( in_array( $requested, array( 'ar', 'en' ), true ) ) {
			return $requested;
		}
	}

	if ( isset( $_COOKIE[ VAVA_LANGUAGE_COOKIE ] ) ) {
		$saved = sanitize_key( wp_unslash( $_COOKIE[ VAVA_LANGUAGE_COOKIE ] ) );
		if ( in_array( $saved, array( 'ar', 'en' ), true ) ) {
			return $saved;
		}
	}

	return 'ar';
}

/**
 * Persist a language query choice before template output begins.
 */
function vava_capture_language_choice(): void {
	if ( ! isset( $_GET['vava_lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$language = sanitize_key( wp_unslash( $_GET['vava_lang'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! in_array( $language, array( 'ar', 'en' ), true ) ) {
		return;
	}

	$expires = time() + YEAR_IN_SECONDS;
	$path    = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
	$domain  = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

	setcookie(
		VAVA_LANGUAGE_COOKIE,
		$language,
		array(
			'expires'  => $expires,
			'path'     => $path,
			'domain'   => $domain,
			'secure'   => is_ssl(),
			'httponly' => false,
			'samesite' => 'Lax',
		)
	);

	// Make the preference available during the same PHP request.
	$_COOKIE[ VAVA_LANGUAGE_COOKIE ] = $language;
}
add_action( 'init', 'vava_capture_language_choice', 1 );

/**
 * Build a same-page language switch URL without creating /en/ duplicates.
 */
function vava_language_url( string $language, string $url = '' ): string {
	$language = vava_normalize_language( $language );

	if ( ! $url ) {
		$object_id = get_queried_object_id();
		$url       = $object_id ? get_permalink( $object_id ) : home_url( '/' );
	}

	$url = remove_query_arg( array( 'vava_lang', 'from' ), $url );
	if ( is_page_template( 'page-templates/my-bookings-vava.php' ) && isset( $_GET['vava_magic'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$magic_token = preg_replace( '/[^a-f0-9]/i', '', sanitize_text_field( wp_unslash( $_GET['vava_magic'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 48 === strlen( $magic_token ) ) {
			$url = add_query_arg( 'vava_magic', rawurlencode( $magic_token ), $url );
		}
	}
	return (string) add_query_arg(
		array(
			'vava_lang' => $language,
			'from'      => 'lang',
		),
		$url
	);
}

/**
 * Build a WordPress page URL that explicitly opens in the requested VAVA language.
 *
 * Page selection is a shared functional setting. The linked page ID is therefore
 * stored once, while this helper applies the active language at render time.
 */
function vava_localized_page_url( int $page_id, string $language = '' ): string {
	$language = $language ? vava_normalize_language( $language ) : vava_current_language();
	$url      = $page_id > 0 ? (string) get_permalink( $page_id ) : '';
	if ( '' === $url ) {
		return '';
	}
	$url = function_exists( 'vava_normalize_internal_url' ) ? vava_normalize_internal_url( $url ) : $url;
	return vava_language_url( $language, $url );
}

/**
 * Reconcile duplicated AR/EN controls into one shared functional setting.
 *
 * Text content remains language-specific. Structural choices are rendered in
 * both language panes for convenience, but are saved as one canonical value.
 */
function vava_reconcile_shared_setting( $arabic_value, $english_value, $stored_value, string $active_language = 'ar' ) {
	$active_language = vava_normalize_language( $active_language );
	$compare          = static function ( $left, $right ): bool {
		return maybe_serialize( $left ) === maybe_serialize( $right );
	};

	if ( $compare( $arabic_value, $english_value ) ) {
		return $arabic_value;
	}

	$arabic_changed  = ! $compare( $arabic_value, $stored_value );
	$english_changed = ! $compare( $english_value, $stored_value );
	if ( $arabic_changed && ! $english_changed ) {
		return $arabic_value;
	}
	if ( $english_changed && ! $arabic_changed ) {
		return $english_value;
	}

	return 'en' === $active_language ? $english_value : $arabic_value;
}

/**
 * Render the shared AR/EN switch used by the homepage and future page templates.
 */
function vava_render_language_switch( string $aria_label = '' ): void {
	$current    = vava_current_language();
	$aria_label = $aria_label ?: ( 'en' === $current ? 'Choose language' : 'اختيار اللغة' );
	?>
	<div aria-label="<?php echo esc_attr( $aria_label ); ?>" class="language-switch" role="group">
		<a class="language-option<?php echo 'ar' === $current ? ' is-active' : ''; ?>" data-vava-language="ar" href="<?php echo esc_url( vava_language_url( 'ar' ) ); ?>"<?php echo 'ar' === $current ? ' aria-current="true"' : ''; ?>><span class="language-code">AR</span></a>
		<a class="language-option<?php echo 'en' === $current ? ' is-active' : ''; ?>" data-vava-language="en" href="<?php echo esc_url( vava_language_url( 'en' ) ); ?>"<?php echo 'en' === $current ? ' aria-current="true"' : ''; ?>><span class="language-code">EN</span></a>
	</div>
	<?php
}

/**
 * Make the bilingual homepage render from the global language state.
 */
function vava_filter_homepage_language( $language, $page_id = 0 ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	return vava_current_language();
}
add_filter( 'vava_homepage_render_language', 'vava_filter_homepage_language', 10, 2 );

/**
 * Keep WordPress-generated language attributes aligned with the selected language.
 */
function vava_filter_language_attributes( string $output, string $doctype = 'html' ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( is_admin() ) {
		return $output;
	}
	$language = vava_current_language();
	$dir      = 'en' === $language ? 'ltr' : 'rtl';
	return sprintf( 'lang="%s" dir="%s"', esc_attr( $language ), esc_attr( $dir ) );
}
add_filter( 'language_attributes', 'vava_filter_language_attributes', 10, 2 );

/**
 * Add reusable language classes to every frontend page.
 */
function vava_language_body_classes( array $classes ): array {
	$language = vava_current_language();
	$classes[] = 'vava-language-' . $language;
	$classes[] = 'en' === $language ? 'vava-dir-ltr' : 'vava-dir-rtl';
	$classes[] = 'vava-unified-font';
	return array_values( array_unique( $classes ) );
}
add_filter( 'body_class', 'vava_language_body_classes' );


/**
 * Return the shared meta key used for a translated WordPress page title.
 */
function vava_page_title_meta_key( string $language ): string {
	return '_vava_page_title_' . vava_normalize_language( $language );
}

/**
 * Return sensible first-run titles for a managed VAVA page.
 */
function vava_page_title_defaults( int $post_id ): array {
	$post       = get_post( $post_id );
	$post_title = $post instanceof WP_Post ? trim( (string) $post->post_title ) : '';
	$defaults   = array(
		'ar' => $post_title,
		'en' => $post_title,
	);

	if ( function_exists( 'vava_homepage_is_home_page' ) && vava_homepage_is_home_page( $post_id ) ) {
		$defaults['ar'] = $post_title ?: 'الصفحة الرئيسية';
		$defaults['en'] = 'Home';
	} elseif ( function_exists( 'vava_about_is_page' ) && vava_about_is_page( $post_id ) ) {
		$defaults['ar'] = $post_title ?: 'عن VAVA';
		$defaults['en'] = 'About VAVA';
	} elseif ( function_exists( 'vava_selections_is_page' ) && vava_selections_is_page( $post_id ) ) {
		$defaults['ar'] = $post_title ?: 'مختارات VAVA';
		$defaults['en'] = 'VAVA Selections';
	}

	/**
	 * Filter the default bilingual title values for a page.
	 *
	 * @param array $defaults Arabic and English defaults.
	 * @param int   $post_id  Page ID.
	 */
	return (array) apply_filters( 'vava_page_title_defaults', $defaults, $post_id );
}

/**
 * Return escaped bilingual data attributes for admin interface labels.
 */
function vava_admin_i18n_attributes( string $arabic, string $english ): string {
	return ' data-vava-i18n-ar="' . esc_attr( $arabic ) . '" data-vava-i18n-en="' . esc_attr( $english ) . '"';
}

/** Return escaped bilingual placeholder attributes for admin inputs. */
function vava_admin_i18n_placeholder_attributes( string $arabic, string $english ): string {
	return ' placeholder="' . esc_attr( $arabic ) . '" data-vava-i18n-placeholder-ar="' . esc_attr( $arabic ) . '" data-vava-i18n-placeholder-en="' . esc_attr( $english ) . '"';
}

/**
 * Safely render saved rich text on the frontend and live previews.
 */
function vava_richtext_output( $value ): string {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	return (string) wp_kses_post( wpautop( $value ) );
}

/**
 * Render the shared compact rich-text editor used by advanced VAVA page settings.
 */
function vava_render_richtext_editor( array $args ): void {
	$name       = (string) ( $args['name'] ?? '' );
	$id         = (string) ( $args['id'] ?? sanitize_html_class( ltrim( $name, '_' ) ) );
	$value      = (string) ( $args['value'] ?? '' );
	$dir        = 'ltr' === ( $args['dir'] ?? 'rtl' ) ? 'ltr' : 'rtl';
	$preview    = (string) ( $args['preview'] ?? '' );
	$preview_ns = (string) ( $args['preview_namespace'] ?? 'home' );
	$class      = trim( 'vava-richtext-source ' . (string) ( $args['class'] ?? '' ) );
	$attrs      = '';
	if ( $preview ) {
		$attribute = 'about' === $preview_ns ? 'data-about-preview' : 'data-vava-preview-field';
		$attrs    .= ' ' . $attribute . '="' . esc_attr( $preview ) . '"';
	}
	?>
	<div class="vava-richtext-control" data-vava-richtext dir="<?php echo esc_attr( $dir ); ?>">
		<textarea class="<?php echo esc_attr( $class ); ?>"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> hidden id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<div class="vava-richtext-toolbar" role="toolbar" aria-label="<?php echo esc_attr( 'ltr' === $dir ? 'Text formatting' : 'تنسيق النص' ); ?>">
			<select class="vava-richtext-format" aria-label="<?php echo esc_attr( 'ltr' === $dir ? 'Paragraph style' : 'نمط الفقرة' ); ?>">
				<option value="p"><?php echo esc_html( 'ltr' === $dir ? 'Paragraph' : 'فقرة' ); ?></option>
				<option value="h3"><?php echo esc_html( 'ltr' === $dir ? 'Heading' : 'عنوان فرعي' ); ?></option>
				<option value="blockquote"><?php echo esc_html( 'ltr' === $dir ? 'Quote' : 'اقتباس' ); ?></option>
			</select>
			<button type="button" data-rte-command="bold" aria-label="<?php echo esc_attr( 'ltr' === $dir ? 'Bold' : 'عريض' ); ?>" title="<?php echo esc_attr( 'ltr' === $dir ? 'Bold' : 'عريض' ); ?>"><strong>B</strong></button>
			<button type="button" data-rte-command="italic" aria-label="<?php echo esc_attr( 'ltr' === $dir ? 'Italic' : 'مائل' ); ?>" title="<?php echo esc_attr( 'ltr' === $dir ? 'Italic' : 'مائل' ); ?>"><em>I</em></button>
			<button type="button" data-rte-command="underline" aria-label="<?php echo esc_attr( 'ltr' === $dir ? 'Underline' : 'تسطير' ); ?>" title="<?php echo esc_attr( 'ltr' === $dir ? 'Underline' : 'تسطير' ); ?>"><u>U</u></button>
			<button type="button" data-rte-command="insertUnorderedList" aria-label="<?php echo esc_attr( 'ltr' === $dir ? 'Bulleted list' : 'قائمة نقطية' ); ?>" title="<?php echo esc_attr( 'ltr' === $dir ? 'Bulleted list' : 'قائمة نقطية' ); ?>">•≡</button>
			<button type="button" data-rte-command="insertOrderedList" aria-label="<?php echo esc_attr( 'ltr' === $dir ? 'Numbered list' : 'قائمة مرقمة' ); ?>" title="<?php echo esc_attr( 'ltr' === $dir ? 'Numbered list' : 'قائمة مرقمة' ); ?>">1≡</button>
			<button type="button" data-rte-command="justifyRight" aria-label="<?php echo esc_attr( 'ltr' === $dir ? 'Align right' : 'محاذاة لليمين' ); ?>" title="<?php echo esc_attr( 'ltr' === $dir ? 'Align right' : 'محاذاة لليمين' ); ?>">⇥</button>
			<button type="button" data-rte-command="justifyCenter" aria-label="<?php echo esc_attr( 'ltr' === $dir ? 'Align center' : 'توسيط' ); ?>" title="<?php echo esc_attr( 'ltr' === $dir ? 'Align center' : 'توسيط' ); ?>">≡</button>
			<button type="button" data-rte-command="justifyLeft" aria-label="<?php echo esc_attr( 'ltr' === $dir ? 'Align left' : 'محاذاة لليسار' ); ?>" title="<?php echo esc_attr( 'ltr' === $dir ? 'Align left' : 'محاذاة لليسار' ); ?>">⇤</button>
			<button type="button" data-rte-link aria-label="<?php echo esc_attr( 'ltr' === $dir ? 'Insert link' : 'إضافة رابط' ); ?>" title="<?php echo esc_attr( 'ltr' === $dir ? 'Insert link' : 'إضافة رابط' ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.1.1l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1"/><path d="M14 11a5 5 0 0 0-7.1-.1l-2 2A5 5 0 0 0 12 20l1.1-1.1"/></svg></button>
			<button type="button" data-rte-command="removeFormat" aria-label="<?php echo esc_attr( 'ltr' === $dir ? 'Clear formatting' : 'مسح التنسيق' ); ?>" title="<?php echo esc_attr( 'ltr' === $dir ? 'Clear formatting' : 'مسح التنسيق' ); ?>">Tx</button>
		</div>
		<div class="vava-richtext-editor" contenteditable="true" data-vava-richtext-editor dir="<?php echo esc_attr( $dir ); ?>"><?php echo vava_richtext_output( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	</div>
	<?php
}

/**
 * Return a translated page title with a safe fallback to the canonical WP title.
 */
function vava_bilingual_page_title( int $post_id, string $language = '' ): string {
	$language = $language ? vava_normalize_language( $language ) : vava_current_language();
	$key      = vava_page_title_meta_key( $language );
	$value    = trim( (string) get_post_meta( $post_id, $key, true ) );
	if ( '' !== $value ) {
		return $value;
	}

	$defaults = vava_page_title_defaults( $post_id );
	return trim( (string) ( $defaults[ $language ] ?? $defaults['ar'] ?? '' ) );
}

/**
 * Render the common bilingual title/permalink card used by managed page templates.
 */
function vava_render_bilingual_page_identity( WP_Post $post, string $permalink = '' ): void {
	$permalink = $permalink ?: (string) get_permalink( $post );
	if ( ! $permalink ) {
		$permalink = home_url( '/' );
	}
	$title_ar = vava_bilingual_page_title( (int) $post->ID, 'ar' );
	$title_en = vava_bilingual_page_title( (int) $post->ID, 'en' );
	?>
	<section class="vava-page-identity-card" aria-label="بيانات الصفحة الأساسية" data-vava-i18n-aria-ar="بيانات الصفحة الأساسية" data-vava-i18n-aria-en="Basic page information">
		<div class="vava-page-identity-field vava-page-title-field">
			<div class="vava-page-title-pane is-active" data-vava-page-title-pane="ar" dir="rtl">
				<label for="vava_page_title_ar"><strong>عنوان الصفحة بالعربية</strong></label>
				<input class="widefat" data-vava-page-title data-vava-page-title-language="ar" id="vava_page_title_ar" name="_vava_page_title_ar" type="text" value="<?php echo esc_attr( $title_ar ); ?>"/>
			</div>
			<div class="vava-page-title-pane" data-vava-page-title-pane="en" dir="ltr">
				<label for="vava_page_title_en"><strong>English page title</strong></label>
				<input class="widefat" data-vava-page-title data-vava-page-title-language="en" id="vava_page_title_en" name="_vava_page_title_en" type="text" value="<?php echo esc_attr( $title_en ); ?>"/>
			</div>
		</div>
		<div class="vava-page-identity-field">
			<label for="vava_page_permalink"><strong data-vava-i18n-ar="الرابط الدائم" data-vava-i18n-en="Permalink">الرابط الدائم</strong></label>
			<div class="vava-page-permalink-control">
				<input class="widefat" dir="ltr" id="vava_page_permalink" readonly title="<?php echo esc_attr( $permalink ); ?>" type="text" value="<?php echo esc_url( $permalink ); ?>"/>
				<a class="button button-secondary" href="<?php echo esc_url( $permalink ); ?>" target="_blank" rel="noopener noreferrer" aria-label="فتح الصفحة في تبويب جديد" data-vava-i18n-aria-ar="فتح الصفحة في تبويب جديد" data-vava-i18n-aria-en="Open page in a new tab">
					<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M14 5h5v5"/><path d="m10 14 9-9"/><path d="M19 13v6H5V5h6"/></svg>
				</a>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Save both translated titles from a managed page editor.
 */
function vava_save_bilingual_page_titles( int $post_id ): void {
	foreach ( array( 'ar', 'en' ) as $language ) {
		$field = '_vava_page_title_' . $language;
		if ( ! array_key_exists( $field, $_POST ) ) {
			continue;
		}
		$value = sanitize_text_field( (string) wp_unslash( $_POST[ $field ] ) );
		if ( '' === $value ) {
			$defaults = vava_page_title_defaults( $post_id );
			$value    = (string) ( $defaults[ $language ] ?? '' );
		}
		update_post_meta( $post_id, vava_page_title_meta_key( $language ), $value );
	}
}

/**
 * Keep WordPress' canonical post_title aligned with the Arabic page title.
 */
function vava_filter_submitted_page_title( array $data, array $postarr ): array {
	if ( 'page' !== ( $data['post_type'] ?? '' ) || ! isset( $_POST['_vava_page_title_ar'] ) ) {
		return $data;
	}
	$title = sanitize_text_field( (string) wp_unslash( $_POST['_vava_page_title_ar'] ) );
	if ( '' !== $title ) {
		$data['post_title'] = $title;
	}
	return $data;
}
add_filter( 'wp_insert_post_data', 'vava_filter_submitted_page_title', 20, 2 );

/**
 * Determine whether a page is managed by the bilingual title system.
 */
function vava_page_has_bilingual_titles( int $post_id ): bool {
	if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
		return false;
	}
	return metadata_exists( 'post', $post_id, vava_page_title_meta_key( 'ar' ) )
		|| metadata_exists( 'post', $post_id, vava_page_title_meta_key( 'en' ) )
		|| ( function_exists( 'vava_homepage_is_home_page' ) && vava_homepage_is_home_page( $post_id ) )
		|| ( function_exists( 'vava_about_is_page' ) && vava_about_is_page( $post_id ) )
		|| ( function_exists( 'vava_paths_is_page' ) && vava_paths_is_page( $post_id ) )
		|| ( function_exists( 'vava_selections_is_page' ) && vava_selections_is_page( $post_id ) )
		|| ( function_exists( 'vava_journal_is_page' ) && vava_journal_is_page( $post_id ) )
		|| ( function_exists( 'vava_contact_is_page' ) && vava_contact_is_page( $post_id ) )
		|| ( function_exists( 'vava_legal_is_page' ) && vava_legal_is_page( $post_id ) );
}

/**
 * Use the selected language for frontend page titles and breadcrumbs.
 */
function vava_filter_frontend_page_title( string $title, int $post_id = 0 ): string {
	if ( is_admin() || ! vava_page_has_bilingual_titles( $post_id ) ) {
		return $title;
	}
	return vava_bilingual_page_title( $post_id );
}
add_filter( 'the_title', 'vava_filter_frontend_page_title', 20, 2 );

/**
 * Translate the browser/document title for managed VAVA pages.
 */
function vava_filter_bilingual_document_title( array $parts ): array {
	if ( is_admin() || ! is_page() ) {
		return $parts;
	}
	$post_id = get_queried_object_id();
	if ( vava_page_has_bilingual_titles( $post_id ) ) {
		$parts['title'] = vava_bilingual_page_title( $post_id );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'vava_filter_bilingual_document_title', 20 );

/**
 * Resolve the translated title of a linked WordPress page menu item.
 */
function vava_nav_menu_item_title_for_language( $menu_item, string $language, string $fallback = '' ): string {
	if ( $menu_item instanceof WP_Post && 'page' === (string) ( $menu_item->object ?? '' ) ) {
		$page_id = absint( $menu_item->object_id ?? 0 );
		if ( vava_page_has_bilingual_titles( $page_id ) ) {
			return vava_bilingual_page_title( $page_id, $language );
		}
	}
	return $fallback ?: ( $menu_item instanceof WP_Post ? (string) $menu_item->title : '' );
}

/**
 * Make WordPress menus switch linked page labels with the global AR/EN state.
 */
function vava_filter_nav_menu_item_title( string $title, $item, $args = null, int $depth = 0 ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $title;
	}
	return vava_nav_menu_item_title_for_language( $item, vava_current_language(), $title );
}
add_filter( 'nav_menu_item_title', 'vava_filter_nav_menu_item_title', 20, 4 );

/** Render the WordPress-managed menu used by all internal page headers. */
function vava_render_internal_header_menu( string $language = '', string $active_key = '' ): void {
	$language  = $language ? vava_normalize_language( $language ) : vava_current_language();
	$menu_id   = function_exists( 'vava_home_internal_header_menu_id' ) ? vava_home_internal_header_menu_id() : 0;
	if ( ! $menu_id ) {
		$locations = get_nav_menu_locations();
		$menu_id   = absint( $locations['primary_internal'] ?? 0 );
	}
	$items      = $menu_id ? wp_get_nav_menu_items( $menu_id, array( 'update_post_term_cache' => false ) ) : array();
	$current_id = get_queried_object_id();
	if ( is_array( $items ) && $items ) {
		foreach ( $items as $item ) {
			if ( ! $item instanceof WP_Post || absint( $item->menu_item_parent ?? 0 ) ) { continue; }
			$title  = vava_nav_menu_item_title_for_language( $item, $language, (string) $item->title );
			$url    = ( 'page' === (string) ( $item->object ?? '' ) && absint( $item->object_id ?? 0 ) )
				? vava_localized_page_url( absint( $item->object_id ), $language )
				: vava_normalize_internal_url( (string) $item->url );
			$active = ( 'page' === (string) ( $item->object ?? '' ) && absint( $item->object_id ?? 0 ) === $current_id )
				|| untrailingslashit( $url ) === untrailingslashit( vava_normalize_internal_url( (string) get_permalink( $current_id ) ) );
			printf( '<a class="%1$s" href="%2$s">%3$s</a>', esc_attr( $active ? 'active current-menu-item' : '' ), esc_url( $url ), esc_html( $title ) );
		}
		return;
	}

	// A missing internal menu must never fall back to homepage section anchors.
	// Render the core WordPress pages directly so About and Paths always navigate
	// to real pages while the homepage keeps its separate one-page navigation.
	$home_id     = absint( get_option( 'page_on_front' ) );
	$about_pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-templates/about-vava.php',
			'no_found_rows'  => true,
		)
	);
	$paths_pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-templates/paths-vava.php',
			'no_found_rows'  => true,
		)
	);
	$selections_pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-templates/selections-vava.php',
			'no_found_rows'  => true,
		)
	);
	$journal_pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-templates/journal-vava.php',
			'no_found_rows'  => true,
		)
	);
	$contact_pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-templates/contact-vava.php',
			'no_found_rows'  => true,
		)
	);
	$about      = $about_pages[0] ?? null;
	$paths      = $paths_pages[0] ?? null;
	$selections = $selections_pages[0] ?? null;
	$journal    = $journal_pages[0] ?? null;
	$contact    = $contact_pages[0] ?? null;
	$fallback_items = array(
		array(
			'id'    => $home_id,
			'url'   => vava_language_url( $language, home_url( '/' ) ),
			'label' => 'en' === $language ? 'Home' : 'الصفحة الرئيسية',
		),
	);
	if ( $about instanceof WP_Post ) {
		$fallback_items[] = array(
			'id'    => (int) $about->ID,
			'url'   => vava_localized_page_url( (int) $about->ID, $language ),
			'label' => vava_bilingual_page_title( (int) $about->ID, $language ),
		);
	}
	if ( $paths instanceof WP_Post ) {
		$fallback_items[] = array(
			'id'    => (int) $paths->ID,
			'url'   => vava_localized_page_url( (int) $paths->ID, $language ),
			'label' => vava_bilingual_page_title( (int) $paths->ID, $language ),
		);
	}
	if ( $selections instanceof WP_Post ) {
		$fallback_items[] = array(
			'id'    => (int) $selections->ID,
			'url'   => vava_localized_page_url( (int) $selections->ID, $language ),
			'label' => vava_bilingual_page_title( (int) $selections->ID, $language ),
		);
	}
	if ( $journal instanceof WP_Post ) {
		$fallback_items[] = array(
			'id'    => (int) $journal->ID,
			'url'   => vava_localized_page_url( (int) $journal->ID, $language ),
			'label' => vava_bilingual_page_title( (int) $journal->ID, $language ),
		);
	}
	if ( $contact instanceof WP_Post ) {
		$fallback_items[] = array(
			'id'    => (int) $contact->ID,
			'url'   => vava_localized_page_url( (int) $contact->ID, $language ),
			'label' => vava_bilingual_page_title( (int) $contact->ID, $language ),
		);
	}
	foreach ( $fallback_items as $item ) {
		$url    = vava_normalize_internal_url( (string) $item['url'] );
		$active = (int) $item['id'] > 0 && (int) $item['id'] === $current_id;
		printf( '<a class="%1$s" href="%2$s">%3$s</a>', esc_attr( $active ? 'active current-menu-item' : '' ), esc_url( $url ), esc_html( (string) $item['label'] ) );
	}
}


/**
 * Determine whether the current page editor uses the advanced VAVA interface.
 */
function vava_is_advanced_page_editor( int $post_id = 0 ): bool {
	if ( ! $post_id ) {
		global $post;
		$post_id = $post instanceof WP_Post ? (int) $post->ID : ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	$template_slug      = $post_id > 0 ? (string) get_page_template_slug( $post_id ) : '';
	$is_custom_template = '' !== $template_slug && 0 === strpos( $template_slug, 'page-templates/' );
	$is_advanced        = $post_id > 0 && (
		$is_custom_template
		|| ( function_exists( 'vava_homepage_is_home_page' ) && vava_homepage_is_home_page( $post_id ) )
		|| ( function_exists( 'vava_about_is_page' ) && vava_about_is_page( $post_id ) )
		|| ( function_exists( 'vava_paths_is_page' ) && vava_paths_is_page( $post_id ) )
		|| ( function_exists( 'vava_selections_is_page' ) && vava_selections_is_page( $post_id ) )
		|| ( function_exists( 'vava_journal_is_page' ) && vava_journal_is_page( $post_id ) )
		|| ( function_exists( 'vava_contact_is_page' ) && vava_contact_is_page( $post_id ) )
		|| ( function_exists( 'vava_legal_is_page' ) && vava_legal_is_page( $post_id ) )
	);
	return (bool) apply_filters( 'vava_is_advanced_page_editor', $is_advanced, $post_id );
}

/** Hide WordPress Screen Options and Help on advanced VAVA page editors only. */
function vava_advanced_page_editor_screen_cleanup(): void {
	if ( ! vava_is_advanced_page_editor() ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen ) {
		$screen->remove_help_tabs();
	}
}
add_action( 'current_screen', 'vava_advanced_page_editor_screen_cleanup', 50 );

function vava_advanced_page_hide_screen_options( bool $show_screen, $screen = null ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	return vava_is_advanced_page_editor() ? false : $show_screen;
}
add_filter( 'screen_options_show_screen', 'vava_advanced_page_hide_screen_options', 50, 2 );

function vava_advanced_page_admin_body_class( string $classes ): string {
	if ( vava_is_advanced_page_editor() ) {
		$classes .= ' vava-advanced-page-editor';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'vava_advanced_page_admin_body_class', 50 );


/**
 * Remove WordPress-native metaboxes that are not used by VAVA advanced pages.
 *
 * The custom editors are the single source of truth for these pages. Keeping
 * Discussion, Comments, Slug, Author and other generic boxes below the custom
 * interface creates visual noise and can confuse content editors.
 */
function vava_advanced_page_remove_native_meta_boxes( string $post_type, WP_Post $post ): void {
	if ( 'page' !== $post_type || ! vava_is_advanced_page_editor( (int) $post->ID ) ) {
		return;
	}

	$boxes = array(
		'postdivrich'     => array( 'normal', 'advanced' ),
		'postimagediv'    => array( 'side' ),
		'commentstatusdiv'=> array( 'normal' ),
		'commentsdiv'     => array( 'normal' ),
		'slugdiv'         => array( 'normal' ),
		'authordiv'       => array( 'normal' ),
		'trackbacksdiv'   => array( 'normal' ),
		'postcustom'      => array( 'normal' ),
		'postexcerpt'     => array( 'normal' ),
		'pageparentdiv'   => array( 'side' ),
	);

	foreach ( $boxes as $box_id => $contexts ) {
		foreach ( $contexts as $context ) {
			remove_meta_box( $box_id, 'page', $context );
		}
	}
}
add_action( 'add_meta_boxes', 'vava_advanced_page_remove_native_meta_boxes', 100, 2 );

/**
 * Configure supported WordPress features.
 */
function vava_living_setup(): void {
	load_theme_textdomain( 'vava-living', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 140,
			'width'       => 300,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	register_nav_menus(
		array(
			'primary_internal' => __( 'القائمة الرئيسية للصفحات الداخلية', 'vava-living' ),
			'footer_primary'   => __( 'قائمة الفوتر', 'vava-living' ),
			'footer_policy'    => __( 'قائمة روابط السياسات', 'vava-living' ),
		)
	);
}
add_action( 'after_setup_theme', 'vava_living_setup' );

/**
 * Collapse the retired per-language menu locations into the three shared locations.
 *
 * Linked WordPress pages already change their labels and language query at render
 * time, so duplicating one location for Arabic and another for English is neither
 * necessary nor desirable. Existing assignments are preserved by preferring the
 * former Arabic location and falling back to the former English location.
 */
function vava_migrate_shared_menu_locations(): void {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$locations = is_array( $locations ) ? $locations : array();
	$original  = $locations;
	$mapping   = array(
		'primary_internal' => array( 'primary_ar', 'primary_en' ),
		'footer_primary'   => array( 'footer_primary_ar', 'footer_primary_en' ),
		'footer_policy'    => array( 'footer_policy_ar', 'footer_policy_en' ),
	);

	foreach ( $mapping as $shared => $legacy_locations ) {
		if ( empty( $locations[ $shared ] ) ) {
			foreach ( $legacy_locations as $legacy ) {
				if ( ! empty( $locations[ $legacy ] ) ) {
					$locations[ $shared ] = absint( $locations[ $legacy ] );
					break;
				}
			}
		}
		foreach ( $legacy_locations as $legacy ) {
			unset( $locations[ $legacy ] );
		}
	}

	if ( $locations !== $original ) {
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}
add_action( 'after_setup_theme', 'vava_migrate_shared_menu_locations', 30 );

/**
 * Return a cache-safe asset version.
 */
function vava_asset_version( string $relative_path ): string {
	$file = get_theme_file_path( $relative_path );
	return is_file( $file ) ? (string) filemtime( $file ) : VAVA_THEME_VERSION;
}

/**
 * Return a deterministic cache-busting version for a Paths image attachment.
 *
 * The version is derived from the attachment's underlying file modification
 * time (falling back to the attachment's last-modified timestamp), so the URL
 * stays cacheable while unchanged and changes whenever the image is replaced
 * or updated, even if the base attachment URL stays the same.
 */
function vava_paths_image_cache_bust( int $attachment_id ): string {
	if ( $attachment_id <= 0 ) {
		return '';
	}
	$version = '';
	$file    = get_attached_file( $attachment_id );
	if ( is_string( $file ) && is_file( $file ) ) {
		$mtime = (int) @filemtime( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( $mtime > 0 ) {
			$version = (string) $mtime;
		}
	}
	if ( '' === $version ) {
		$modified = get_post_field( 'post_modified', $attachment_id );
		$version  = $modified ? (string) strtotime( (string) $modified ) : (string) $attachment_id;
	}
	return '' === $version ? '' : '?v=' . rawurlencode( $version );
}

/**
 * Enqueue language persistence on every frontend page and page-specific assets.
 */
function vava_living_enqueue_assets(): void {
	wp_enqueue_style(
		'vava-theme-meta',
		get_stylesheet_uri(),
		array(),
		vava_asset_version( 'style.css' )
	);

	wp_enqueue_script(
		'vava-site-language',
		get_theme_file_uri( 'assets/js/site-language.js' ),
		array(),
		vava_asset_version( 'assets/js/site-language.js' ),
		true
	);
	wp_localize_script(
		'vava-site-language',
		'VAVA_SITE_LANGUAGE',
		array(
			'current'             => vava_current_language(),
			'cookieName'          => VAVA_LANGUAGE_COOKIE,
			'storageKey'          => 'vavaSiteLanguage',
			'hasServerPreference' => vava_has_language_preference(),
		)
	);

	// Load the approved bilingual design assets on managed VAVA pages.
	$is_homepage = is_front_page() || is_page_template( 'page-templates/homepage.php' );
	$is_about    = is_page_template( 'page-templates/about-vava.php' );
	$is_paths    = is_page_template( 'page-templates/paths-vava.php' );
	$is_selections     = is_page_template( 'page-templates/selections-vava.php' );
	$is_digital_product = is_page_template( 'page-templates/digital-product-vava.php' );
	$is_digital_checkout = is_page_template( 'page-templates/digital-product-checkout-vava.php' );
	$is_digital_viewer   = is_page_template( 'page-templates/digital-library-viewer-vava.php' );
	$is_journal    = is_page_template( 'page-templates/journal-vava.php' );
	$is_contact    = is_page_template( 'page-templates/contact-vava.php' );
	$is_booking      = is_page_template( 'page-templates/booking-vava.php' );
	$is_my_bookings  = is_page_template( 'page-templates/my-bookings-vava.php' ) || ( function_exists( 'vava_booking_my_bookings_page_id' ) && get_queried_object_id() === vava_booking_my_bookings_page_id() );
	$is_legal        = is_page_template( 'page-templates/legal-vava.php' ) || ( is_page() && function_exists( 'vava_legal_is_page' ) && vava_legal_is_page( get_queried_object_id() ) );
	if ( ! $is_homepage && ! $is_about && ! $is_paths && ! $is_selections && ! $is_digital_product && ! $is_digital_checkout && ! $is_digital_viewer && ! $is_journal && ! $is_contact && ! $is_booking && ! $is_my_bookings && ! $is_legal ) {
		return;
	}

	$language_style = 'en' === vava_current_language() ? 'assets/css/styles-en.css' : 'assets/css/styles-ar.css';

	wp_enqueue_style(
		'vava-language',
		get_theme_file_uri( $language_style ),
		array( 'vava-theme-meta' ),
		vava_asset_version( $language_style )
	);

	wp_enqueue_style(
		'vava-typography',
		get_theme_file_uri( 'assets/css/typography.css' ),
		array( 'vava-language' ),
		vava_asset_version( 'assets/css/typography.css' )
	);

	if ( $is_homepage ) {
		wp_enqueue_style(
			'vava-home-journal-concept2',
			get_theme_file_uri( 'assets/css/home-journal-concept2.css' ),
			array( 'vava-typography' ),
			vava_asset_version( 'assets/css/home-journal-concept2.css' )
		);
	}

	if ( $is_about ) {
		wp_enqueue_style(
			'vava-about-wordpress',
			get_theme_file_uri( 'assets/css/about-wordpress.css' ),
			array( 'vava-typography' ),
			vava_asset_version( 'assets/css/about-wordpress.css' )
		);
	}

	if ( $is_selections ) {
		wp_enqueue_style(
			'vava-selections-wordpress',
			get_theme_file_uri( 'assets/css/selections-wordpress.css' ),
			array( 'vava-typography' ),
			vava_asset_version( 'assets/css/selections-wordpress.css' )
		);
	}

	if ( $is_journal ) {
		wp_enqueue_style(
			'vava-journal-wordpress',
			get_theme_file_uri( 'assets/css/journal-wordpress.css' ),
			array( 'vava-typography' ),
			vava_asset_version( 'assets/css/journal-wordpress.css' )
		);
	}

	if ( $is_contact ) {
		wp_enqueue_style(
			'vava-contact-wordpress',
			get_theme_file_uri( 'assets/css/contact-wordpress.css' ),
			array( 'vava-typography' ),
			vava_asset_version( 'assets/css/contact-wordpress.css' )
		);
	}

	if ( $is_legal ) {
		wp_enqueue_style(
			'vava-legal-wordpress',
			get_theme_file_uri( 'assets/css/legal-wordpress.css' ),
			array( 'vava-typography' ),
			vava_asset_version( 'assets/css/legal-wordpress.css' )
		);
	}


	if ( $is_booking ) {
		wp_enqueue_style(
			'vava-booking',
			get_theme_file_uri( 'assets/css/booking-vava.css' ),
			array( 'vava-typography' ),
			vava_asset_version( 'assets/css/booking-vava.css' )
		);
	}

	if ( $is_about || $is_paths || $is_selections || $is_digital_product || $is_digital_checkout || $is_digital_viewer || $is_journal || $is_contact || $is_booking || $is_my_bookings || $is_legal ) {
		wp_enqueue_style(
			'vava-internal-wordpress',
			get_theme_file_uri( 'assets/css/internal-wordpress.css' ),
			array( $is_about ? 'vava-about-wordpress' : ( $is_selections ? 'vava-selections-wordpress' : ( $is_digital_product ? 'vava-typography' : ( $is_journal ? 'vava-journal-wordpress' : ( $is_contact ? 'vava-contact-wordpress' : ( $is_legal ? 'vava-legal-wordpress' : ( $is_booking ? 'vava-booking' : 'vava-typography' ) ) ) ) ) ) ),
			vava_asset_version( 'assets/css/internal-wordpress.css' )
		);
	}

	if ( $is_selections || $is_digital_product || $is_digital_checkout || $is_digital_viewer || $is_my_bookings ) {
		wp_enqueue_style(
			'vava-digital-products-commerce',
			get_theme_file_uri( 'assets/css/digital-products-commerce.css' ),
			array( 'vava-internal-wordpress' ),
			vava_asset_version( 'assets/css/digital-products-commerce.css' )
		);
	}

	if ( $is_digital_product ) {
		wp_enqueue_style(
			'vava-digital-product-wordpress',
			get_theme_file_uri( 'assets/css/digital-product-wordpress.css' ),
			array( 'vava-internal-wordpress' ),
			vava_asset_version( 'assets/css/digital-product-wordpress.css' )
		);
	}

	if ( $is_paths ) {
		wp_enqueue_style(
			'vava-paths-journey',
			get_theme_file_uri( 'assets/css/paths-journey.css' ),
			array( 'vava-internal-wordpress' ),
			vava_asset_version( 'assets/css/paths-journey.css' )
		);
	}

	// Final responsive guard: loaded after page-specific CSS so managed VAVA
	// pages cannot create horizontal viewport scrolling on phones.
	wp_enqueue_style(
		'vava-frontend-mobile-fixes',
		get_theme_file_uri( 'assets/css/frontend-mobile-fixes-1.22.60.css' ),
		array( 'vava-typography' ),
		vava_asset_version( 'assets/css/frontend-mobile-fixes-1.22.60.css' )
	);

	wp_enqueue_script(
		'vava-main',
		get_theme_file_uri( 'assets/js/main.js' ),
		array( 'vava-site-language' ),
		vava_asset_version( 'assets/js/main.js' ),
		true
	);

	if ( $is_legal ) {
		wp_enqueue_script(
			'vava-legal-wordpress',
			get_theme_file_uri( 'assets/js/legal-vava.js' ),
			array( 'vava-main' ),
			vava_asset_version( 'assets/js/legal-vava.js' ),
			true
		);
	}

	if ( $is_paths ) {
		wp_enqueue_script(
			'vava-paths-journey',
			get_theme_file_uri( 'assets/js/paths-journey.js' ),
			array( 'vava-main' ),
			vava_asset_version( 'assets/js/paths-journey.js' ),
			true
		);
	}

	if ( $is_selections ) {
		wp_enqueue_script(
			'vava-selections',
			get_theme_file_uri( 'assets/js/selections.js' ),
			array( 'vava-main' ),
			vava_asset_version( 'assets/js/selections.js' ),
			true
		);
	}

	if ( $is_journal ) {
		wp_enqueue_script(
			'vava-journal',
			get_theme_file_uri( 'assets/js/journal.js' ),
			array( 'vava-main' ),
			vava_asset_version( 'assets/js/journal.js' ),
			true
		);
		wp_localize_script(
			'vava-journal',
			'VAVA_JOURNAL',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'vava_journal_frontend' ),
				'pageId'  => get_queried_object_id(),
				'lang'    => vava_current_language(),
			)
		);
	}

	if ( $is_contact ) {
		wp_enqueue_script(
			'vava-contact',
			get_theme_file_uri( 'assets/js/contact.js' ),
			array( 'vava-main' ),
			vava_asset_version( 'assets/js/contact.js' ),
			true
		);
		$page_id = get_queried_object_id();
		wp_localize_script(
			'vava-contact',
			'VAVA_CONTACT',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'vava_contact_hold_' . $page_id ),
				'pageId'  => $page_id,
				'lang'    => vava_current_language(),
			)
		);
	}

	if ( $is_digital_checkout ) {
		wp_enqueue_script( 'vava-digital-product-checkout', get_theme_file_uri( 'assets/js/digital-product-checkout.js' ), array( 'vava-main' ), vava_asset_version( 'assets/js/digital-product-checkout.js' ), true );
		wp_localize_script( 'vava-digital-product-checkout', 'VAVA_DIGITAL_CHECKOUT', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'vava_digital_checkout' ),
		) );
	}

	if ( $is_digital_viewer ) {
		wp_enqueue_script( 'vava-digital-library-viewer', get_theme_file_uri( 'assets/js/digital-library-viewer.js' ), array( 'vava-main' ), vava_asset_version( 'assets/js/digital-library-viewer.js' ), true );
		$reader_uid = isset( $_GET['product'] ) ? sanitize_key( wp_unslash( $_GET['product'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$reader_user = function_exists( 'vava_customer_current_verified_user' ) ? vava_customer_current_verified_user() : null;
		$reader_record = function_exists( 'vava_digital_products_file_record' ) ? vava_digital_products_file_record( $reader_uid ) : array();
		$reader_order = $reader_user instanceof WP_User && function_exists( 'vava_digital_products_latest_order' ) ? vava_digital_products_latest_order( $reader_uid, $reader_user->ID ) : 0;
		wp_localize_script( 'vava-digital-library-viewer', 'VAVA_DIGITAL_READER', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'uid' => $reader_uid,
			'userId' => $reader_user instanceof WP_User ? $reader_user->ID : 0,
			'pageCount' => absint( $reader_record['page_count'] ?? 0 ),
			'nonce' => $reader_user instanceof WP_User ? wp_create_nonce( 'vava_digital_reader_' . $reader_uid . '_' . $reader_user->ID ) : '',
			'watermark' => $reader_user instanceof WP_User ? trim( (string) ( $reader_user->user_email ?: $reader_user->display_name ) . ' · #' . $reader_order ) : '',
			'brand' => 'VAVA LIVING',
			'logoUrl' => get_theme_file_uri( 'assets/images/vava-logo.png' ),
			'labels' => array( 'error' => 'en' === vava_current_language() ? 'Could not load the protected page.' : 'تعذر تحميل الصفحة المحمية.' ),
		) );
	}

	if ( $is_booking ) {
		$page_id = get_queried_object_id();
		$lang = vava_current_language();
		$shared = vava_booking_shared_data( $page_id );
		$text = vava_booking_text_data( $page_id, $lang );
		$service_id = isset( $_GET['service'] ) ? sanitize_text_field( wp_unslash( $_GET['service'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$service = vava_booking_resolve_service( $service_id, $lang );
		wp_enqueue_script( 'vava-booking', get_theme_file_uri( 'assets/js/booking-vava.js' ), array( 'vava-main' ), vava_asset_version( 'assets/js/booking-vava.js' ), true );
		wp_localize_script( 'vava-booking', 'VAVA_BOOKING', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'vava_booking_frontend' ),
			'lang' => $lang,
			'locale' => 'en' === $lang ? 'en-US' : 'ar-SA',
			'today' => current_time( 'Y-m-d' ),
			'service' => $service ? $service['uid'] : '',
			'maxDays' => absint( $shared['max_days'] ?? 60 ),
			'workingDays' => $service ? vava_booking_effective_working_days( $service, $shared ) : (array) ( $shared['working_days'] ?? array() ),
			'loading' => 'en' === $lang ? 'Loading available times…' : 'جارٍ تحميل المواعيد المتاحة…',
			'noSlots' => (string) $text['no_slots'],
			'processing' => (string) $text['processing'],
			'error' => (string) $text['validation_error'],
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'vava_living_enqueue_assets' );

/**
 * VAVA_SITE_UNIFIED_CAIRO_FONT_V1
 * Load the approved Cairo typography override after every frontend stylesheet.
 *
 * Loading at a late priority keeps the same calm body typeface across every
 * public VAVA page, including page-specific components with legacy font rules.
 */
function vava_enqueue_unified_frontend_font(): void {
	if ( is_admin() ) {
		return;
	}
	wp_enqueue_style(
		'vava-unified-frontend-font',
		get_theme_file_uri( 'assets/css/site-font-unified.css' ),
		array(),
		vava_asset_version( 'assets/css/site-font-unified.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'vava_enqueue_unified_frontend_font', 999 );

/** Load shared rich-text and success-toast behavior on advanced page editors. */
function vava_advanced_admin_common_assets( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || ! vava_is_advanced_page_editor( $post_id ) ) { return; }
	wp_enqueue_style( 'vava-admin-shared', get_theme_file_uri( 'assets/css/admin-vava-shared.css' ), array(), vava_asset_version( 'assets/css/admin-vava-shared.css' ) );
	wp_enqueue_script( 'vava-admin-common', get_theme_file_uri( 'assets/js/admin-vava-common.js' ), array( 'jquery' ), vava_asset_version( 'assets/js/admin-vava-common.js' ), true );
	$message      = isset( $_GET['message'] ) ? absint( $_GET['message'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$show_success = in_array( $message, array( 1, 4, 6, 7, 8, 9, 10 ), true );
	if ( $show_success ) {
		wp_add_inline_style( 'vava-admin-shared', '#message.updated,.notice.notice-success.is-dismissible{display:none!important}' );
	}
	wp_localize_script( 'vava-admin-common', 'vavaAdminCommon', array(
		'showSuccess' => $show_success,
		'viewUrl' => get_permalink( $post_id ),
	) );
}
add_action( 'admin_enqueue_scripts', 'vava_advanced_admin_common_assets', 30 );

/**
 * Build a theme asset URL.
 */
function vava_asset_uri( string $relative_path ): string {
	return get_theme_file_uri( ltrim( $relative_path, '/' ) );
}

/**
 * Build a future WordPress page URL from its approved slug.
 *
 * The global language cookie keeps the selected language across navigation.
 */
function vava_page_url( string $slug = '' ): string {
	$slug = trim( $slug, '/' );
	if ( '' === $slug ) {
		return home_url( '/' );
	}

	// Resolve the real WordPress page instead of assuming pretty permalinks are enabled.
	// The production VAVA site currently uses plain ?page_id= URLs, so hard-coded
	// /about-vava/ style URLs return 404 even when the page exists.
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $page instanceof WP_Post && 'publish' === get_post_status( $page ) ) {
		$url = get_permalink( $page );
		if ( $url ) {
			return vava_language_url( vava_current_language(), (string) $url );
		}
	}

	// Keep a backward-compatible fallback for a slug that is not a WordPress page.
	return home_url( '/' . $slug . '/' );
}

/**
 * Normalize a same-site URL without duplicating a subdirectory installation path.
 *
 * WordPress menu items already contain the full home path. Re-appending their
 * parsed path to home_url() turns /vavaliving/ into /vavaliving/vavaliving/ on
 * local or subdirectory installs. This helper first removes the configured home
 * base path, then removes the obsolete /en/ prefix used by older theme builds.
 */
function vava_normalize_internal_url( string $url ): string {
	$url = trim( $url );
	if ( '' === $url || str_starts_with( $url, '#' ) || preg_match( '#^(?:mailto:|tel:)#i', $url ) ) {
		return $url;
	}

	$home   = wp_parse_url( home_url( '/' ) );
	$target = wp_parse_url( $url );
	if ( ! is_array( $home ) || ! is_array( $target ) ) {
		return $url;
	}

	$target_host = strtolower( (string) ( $target['host'] ?? '' ) );
	$home_host   = strtolower( (string) ( $home['host'] ?? '' ) );
	$legacy_local_target = in_array( $target_host, array( 'localhost', '127.0.0.1', '::1' ), true )
		|| ( '' !== $target_host && ( str_ends_with( $target_host, '.local' ) || str_ends_with( $target_host, '.test' ) ) );

	if ( '' !== $target_host && ( '' === $home_host || $target_host !== $home_host ) && ! $legacy_local_target ) {
		return $url;
	}

	if ( '' !== $target_host && ! $legacy_local_target && ( isset( $home['port'] ) || isset( $target['port'] ) ) ) {
		$resolved_port = static function ( array $parts ): int {
			if ( isset( $parts['port'] ) ) {
				return absint( $parts['port'] );
			}
			return 'https' === strtolower( (string) ( $parts['scheme'] ?? '' ) ) ? 443 : 80;
		};
		if ( $resolved_port( $home ) !== $resolved_port( $target ) ) {
			return $url;
		}
	}

	$home_path   = '/' . trim( (string) ( $home['path'] ?? '' ), '/' );
	$home_path   = '/' === $home_path ? '' : untrailingslashit( $home_path );
	$target_path = '/' . ltrim( (string) ( $target['path'] ?? '/' ), '/' );

	if ( $legacy_local_target ) {
		$legacy_base_paths = (array) apply_filters( 'vava_legacy_local_base_paths', array( '/vavaliving' ) );
		foreach ( $legacy_base_paths as $legacy_base_path ) {
			$legacy_base_path = '/' . trim( (string) $legacy_base_path, '/' );
			if ( '/' === $legacy_base_path ) {
				continue;
			}
			if ( $target_path === $legacy_base_path || $target_path === trailingslashit( $legacy_base_path ) ) {
				$target_path = '/';
				break;
			}
			if ( str_starts_with( $target_path, trailingslashit( $legacy_base_path ) ) ) {
				$target_path = '/' . ltrim( substr( $target_path, strlen( $legacy_base_path ) ), '/' );
				break;
			}
		}
	}

	if ( '' !== $home_path ) {
		if ( $target_path === $home_path ) {
			$relative_path = '/';
		} elseif ( str_starts_with( $target_path, trailingslashit( $home_path ) ) ) {
			$relative_path = substr( $target_path, strlen( $home_path ) );
		} elseif ( '' !== $target_host ) {
			// Same host, but outside this WordPress installation.
			return $url;
		} else {
			$relative_path = $target_path;
		}
	} else {
		$relative_path = $target_path;
	}

	$relative_path = preg_replace( '#^/en(?=/|$)#i', '', $relative_path ) ?: '/';

	// Repair legacy/manual same-site links such as /about-vava/ when WordPress is
	// configured with plain permalinks. This also protects custom Header/Footer
	// menu URLs saved before the current permalink configuration.
	$page_path  = trim( $relative_path, '/' );
	$page       = '' !== $page_path ? get_page_by_path( $page_path, OBJECT, 'page' ) : null;
	$normalized = ( $page instanceof WP_Post && 'publish' === get_post_status( $page ) )
		? (string) get_permalink( $page )
		: home_url( '/' . ltrim( $relative_path, '/' ) );

	if ( ! empty( $target['query'] ) ) {
		$query_args = array();
		parse_str( (string) $target['query'], $query_args );
		if ( $query_args ) {
			$normalized = (string) add_query_arg( $query_args, $normalized );
		}
	}
	if ( ! empty( $target['fragment'] ) ) {
		$normalized .= '#' . $target['fragment'];
	}

	return (string) $normalized;
}

/**
 * Backward-compatible helper: English now uses the same homepage URL.
 */
function vava_english_home_url(): string {
	return (string) apply_filters( 'vava_english_home_url', vava_language_url( 'en', home_url( '/' ) ) );
}
