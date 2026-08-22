<?php
/**
 * Protected digital-product checkout, manual approval and customer library.
 *
 * Reuses the existing VAVA booking/payment infrastructure while keeping
 * digital-product orders distinct from appointment bookings.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

function vava_digital_products_checkout_template_slug(): string {
	return 'page-templates/digital-product-checkout-vava.php';
}

function vava_digital_products_viewer_template_slug(): string {
	return 'page-templates/digital-library-viewer-vava.php';
}

function vava_digital_products_page_id_by_template( string $template ): int {
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
	return isset( $ids[0] ) ? absint( $ids[0] ) : 0;
}

function vava_digital_products_checkout_page_id(): int {
	return vava_digital_products_page_id_by_template( vava_digital_products_checkout_template_slug() );
}

function vava_digital_products_viewer_page_id(): int {
	return vava_digital_products_page_id_by_template( vava_digital_products_viewer_template_slug() );
}

function vava_digital_products_checkout_url( string $uid, string $lang = 'ar' ): string {
	$page_id = vava_digital_products_checkout_page_id();
	$url     = $page_id ? get_permalink( $page_id ) : home_url( '/digital-checkout/' );
	$url     = function_exists( 'vava_language_url' ) ? vava_language_url( $lang, $url ) : $url;
	return (string) add_query_arg( 'product', sanitize_key( $uid ), $url );
}

function vava_digital_products_viewer_url( string $uid, string $lang = 'ar' ): string {
	$page_id = vava_digital_products_viewer_page_id();
	$url     = $page_id ? get_permalink( $page_id ) : home_url( '/digital-reader/' );
	$url     = function_exists( 'vava_language_url' ) ? vava_language_url( $lang, $url ) : $url;
	return (string) add_query_arg( 'product', sanitize_key( $uid ), $url );
}

/** Create the two functional pages without exposing them as editable product pages. */
function vava_digital_products_create_system_pages(): void {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$pages = array(
		array(
			'slug'     => 'digital-checkout',
			'title'    => 'شراء منتج رقمي',
			'template' => vava_digital_products_checkout_template_slug(),
		),
		array(
			'slug'     => 'digital-reader',
			'title'    => 'قارئ المنتجات الرقمية',
			'template' => vava_digital_products_viewer_template_slug(),
		),
	);
	foreach ( $pages as $config ) {
		$page = get_page_by_path( $config['slug'], OBJECT, 'page' );
		if ( ! $page instanceof WP_Post ) {
			$page_id = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => $config['title'],
					'post_name'   => $config['slug'],
				)
			);
			$page = $page_id && ! is_wp_error( $page_id ) ? get_post( $page_id ) : null;
		}
		if ( $page instanceof WP_Post ) {
			update_post_meta( $page->ID, '_wp_page_template', $config['template'] );
		}
	}
}
add_action( 'admin_init', 'vava_digital_products_create_system_pages', 18 );

function vava_digital_products_is_order( int $order_id ): bool {
	return $order_id > 0
		&& 'vava_booking' === get_post_type( $order_id )
		&& 'digital_product' === (string) get_post_meta( $order_id, '_vava_booking_order_type', true );
}

function vava_digital_products_order_uid( int $order_id ): string {
	$uid = (string) get_post_meta( $order_id, '_vava_digital_product_uid', true );
	if ( '' === $uid ) { $uid = (string) get_post_meta( $order_id, '_vava_booking_service_uid', true ); }
	return sanitize_key( $uid );
}

function vava_digital_products_order_access_status( int $order_id ): string {
	$status = sanitize_key( (string) get_post_meta( $order_id, '_vava_digital_access_status', true ) );
	return $status ?: 'pending';
}

function vava_digital_products_find_orders_for_user( int $user_id ): array {
	if ( ! $user_id ) { return array(); }
	$ids = get_posts(
		array(
			'post_type'      => 'vava_booking',
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'meta_query'     => array(
				'relation' => 'AND',
				array( 'key' => '_vava_booking_user_id', 'value' => $user_id ),
				array( 'key' => '_vava_booking_order_type', 'value' => 'digital_product' ),
			),
		)
	);
	return array_values( array_filter( array_map( 'absint', $ids ) ) );
}

function vava_digital_products_latest_order( string $uid, int $user_id = 0, string $email = '' ): int {
	$uid = sanitize_key( $uid );
	if ( '' === $uid ) { return 0; }
	$meta_query = array(
		'relation' => 'AND',
		array( 'key' => '_vava_booking_order_type', 'value' => 'digital_product' ),
		array( 'key' => '_vava_digital_product_uid', 'value' => $uid ),
	);
	if ( $user_id ) {
		$meta_query[] = array( 'key' => '_vava_booking_user_id', 'value' => $user_id );
	} elseif ( is_email( $email ) ) {
		$meta_query[] = array( 'key' => '_vava_booking_customer_email', 'value' => strtolower( sanitize_email( $email ) ) );
	} else {
		return 0;
	}
	$ids = get_posts(
		array(
			'post_type'      => 'vava_booking',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'meta_query'     => $meta_query,
		)
	);
	return isset( $ids[0] ) ? absint( $ids[0] ) : 0;
}

function vava_digital_products_user_can_view( int $user_id, string $uid ): bool {
	$order_id = vava_digital_products_latest_order( $uid, $user_id );
	if ( ! $order_id ) { return false; }
	return 'active' === vava_digital_products_order_access_status( $order_id )
		&& 'paid' === ( function_exists( 'vava_booking_effective_payment_status' ) ? vava_booking_effective_payment_status( $order_id ) : (string) get_post_meta( $order_id, '_vava_booking_payment_status', true ) );
}

/** Private file metadata stored on the single Selections page. */
function vava_digital_products_file_map( int $page_id = 0 ): array {
	if ( ! $page_id && function_exists( 'vava_selections_page_id' ) ) { $page_id = vava_selections_page_id(); }
	$data = $page_id ? get_post_meta( $page_id, '_vava_digital_product_files', true ) : array();
	return is_array( $data ) ? $data : array();
}

function vava_digital_products_file_record( string $uid, int $page_id = 0 ): array {
	$map = vava_digital_products_file_map( $page_id );
	$uid = sanitize_key( $uid );
	return isset( $map[ $uid ] ) && is_array( $map[ $uid ] ) ? $map[ $uid ] : array();
}

function vava_digital_products_private_root(): array {
	$uploads = wp_upload_dir();
	$relative = 'vava-private-products';
	$path = trailingslashit( (string) $uploads['basedir'] ) . $relative;
	if ( ! is_dir( $path ) ) { wp_mkdir_p( $path ); }
	if ( is_dir( $path ) ) {
		$guard = "Options -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n";
		if ( ! is_file( $path . '/.htaccess' ) ) { @file_put_contents( $path . '/.htaccess', $guard ); } // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( ! is_file( $path . '/index.php' ) ) { @file_put_contents( $path . '/index.php', "<?php\n// Silence is golden.\n" ); } // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
	return array( 'path' => $path, 'relative' => $relative, 'basedir' => (string) $uploads['basedir'] );
}

function vava_digital_products_private_file_path( array $record ): string {
	$relative = ltrim( (string) ( $record['relative_path'] ?? '' ), '/\\' );
	if ( '' === $relative || false !== strpos( $relative, '..' ) ) { return ''; }
	$root = vava_digital_products_private_root();
	$path = trailingslashit( $root['basedir'] ) . $relative;
	$real_root = realpath( $root['path'] );
	$real_file = is_file( $path ) ? realpath( $path ) : false;
	$root_prefix = $real_root ? trailingslashit( $real_root ) : '';
	return $root_prefix && $real_file && 0 === strpos( $real_file, $root_prefix ) ? $real_file : '';
}

function vava_digital_products_format_bytes( int $bytes ): string {
	if ( $bytes <= 0 ) { return '—'; }
	$units = array( 'B', 'KB', 'MB', 'GB' );
	$power = min( count( $units ) - 1, (int) floor( log( $bytes, 1024 ) ) );
	return number_format_i18n( $bytes / pow( 1024, $power ), $power ? 1 : 0 ) . ' ' . $units[ $power ];
}


function vava_digital_products_pages_root(): array {
	$root = vava_digital_products_private_root();
	$path = trailingslashit( $root['path'] ) . 'pages';
	if ( ! is_dir( $path ) ) { wp_mkdir_p( $path ); }
	if ( is_dir( $path ) && ! is_file( $path . '/.htaccess' ) ) {
		@file_put_contents( $path . '/.htaccess', "Options -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
	return array( 'path' => $path, 'relative' => trailingslashit( $root['relative'] ) . 'pages' );
}

function vava_digital_products_private_pages_dir( array $record ): string {
	$relative = ltrim( (string) ( $record['pages_relative_dir'] ?? '' ), '/\\' );
	if ( '' === $relative || false !== strpos( $relative, '..' ) ) { return ''; }
	$root = vava_digital_products_private_root();
	$path = trailingslashit( $root['basedir'] ) . $relative;
	$real_root = realpath( $root['path'] );
	$real_dir  = is_dir( $path ) ? realpath( $path ) : false;
	$root_prefix = $real_root ? trailingslashit( $real_root ) : '';
	return $root_prefix && $real_dir && 0 === strpos( $real_dir, $root_prefix ) ? $real_dir : '';
}

function vava_digital_products_private_page_path( array $record, int $page ): string {
	$dir = vava_digital_products_private_pages_dir( $record );
	if ( ! $dir || $page < 1 ) { return ''; }
	$path = trailingslashit( $dir ) . sprintf( 'page-%04d.jpg', $page );
	return is_file( $path ) ? $path : '';
}

function vava_digital_products_remove_directory( string $dir ): void {
	if ( ! is_dir( $dir ) ) { return; }
	$items = scandir( $dir );
	if ( ! is_array( $items ) ) { return; }
	foreach ( $items as $item ) {
		if ( '.' === $item || '..' === $item ) { continue; }
		$path = $dir . DIRECTORY_SEPARATOR . $item;
		if ( is_dir( $path ) ) { vava_digital_products_remove_directory( $path ); }
		else { @unlink( $path ); } // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
	@rmdir( $dir ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
}

function vava_digital_products_update_file_record( int $post_id, string $uid, array $changes, string $fingerprint = '' ): array {
	$map = vava_digital_products_file_map( $post_id );
	$current = isset( $map[ $uid ] ) && is_array( $map[ $uid ] ) ? $map[ $uid ] : array();
	if ( $fingerprint && (string) ( $current['fingerprint'] ?? '' ) !== $fingerprint ) { return $current; }
	$map[ $uid ] = array_merge( $current, $changes );
	update_post_meta( $post_id, '_vava_digital_product_files', $map );
	return $map[ $uid ];
}

function vava_digital_products_schedule_processing( int $post_id, string $uid, string $fingerprint ): void {
	wp_clear_scheduled_hook( 'vava_digital_products_process_pdf', array( $post_id, $uid, $fingerprint ) );
	wp_schedule_single_event( time() + 2, 'vava_digital_products_process_pdf', array( $post_id, $uid, $fingerprint ) );
	if ( function_exists( 'spawn_cron' ) ) { spawn_cron(); }
}

function vava_digital_products_store_uploaded_pdf( int $post_id, string $uid, array $file ) {
	$uid = sanitize_key( $uid );
	if ( ! $post_id || '' === $uid ) { return new WP_Error( 'invalid_product', 'Invalid product.' ); }
	$error = absint( $file['error'] ?? UPLOAD_ERR_NO_FILE );
	if ( UPLOAD_ERR_OK !== $error ) { return new WP_Error( 'upload_error', 'تعذر رفع الملف.' ); }
	$tmp = (string) ( $file['tmp_name'] ?? '' );
	$name = sanitize_file_name( (string) ( $file['name'] ?? '' ) );
	$size = absint( $file['size'] ?? 0 );
	if ( ! is_uploaded_file( $tmp ) || $size <= 0 || $size > 50 * MB_IN_BYTES ) { return new WP_Error( 'file_size', 'ملف PDF غير صالح أو يتجاوز 50 ميجابايت.' ); }
	$checked = wp_check_filetype_and_ext( $tmp, $name, array( 'pdf' => 'application/pdf' ) );
	if ( 'pdf' !== strtolower( (string) ( $checked['ext'] ?? '' ) ) ) { return new WP_Error( 'file_type', 'يُسمح بملفات PDF فقط.' ); }
	$root = vava_digital_products_private_root();
	$fingerprint = wp_generate_password( 24, false, false );
	$filename = $uid . '-' . $fingerprint . '.pdf';
	$target = trailingslashit( $root['path'] ) . $filename;
	if ( ! @move_uploaded_file( $tmp, $target ) ) { return new WP_Error( 'move_failed', 'تعذر حفظ ملف PDF في التخزين المحمي.' ); } // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	@chmod( $target, 0640 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	$old = vava_digital_products_file_record( $uid, $post_id );
	if ( $old ) { vava_digital_products_delete_private_record( $old ); }
	$record = array(
		'relative_path'       => trailingslashit( $root['relative'] ) . $filename,
		'original_name'       => $name,
		'size'                => $size,
		'updated_at'          => current_time( 'mysql' ),
		'fingerprint'         => $fingerprint,
		'processing_status'   => 'queued',
		'processing_progress' => 1,
		'processing_message'  => 'بانتظار تجهيز صفحات العرض المحمية.',
		'page_count'          => 0,
	);
	$map = vava_digital_products_file_map( $post_id );
	$map[ $uid ] = $record;
	update_post_meta( $post_id, '_vava_digital_product_files', $map );
	vava_digital_products_schedule_processing( $post_id, $uid, $fingerprint );
	return $record;
}

/** Queue files uploaded by earlier patch versions for protected page conversion. */
function vava_digital_products_maybe_migrate_legacy_files(): void {
	if ( ! current_user_can( 'edit_pages' ) || ! function_exists( 'vava_selections_page_id' ) ) { return; }
	$post_id = absint( vava_selections_page_id() );
	if ( ! $post_id ) { return; }
	$map = vava_digital_products_file_map( $post_id );
	if ( ! $map ) { return; }
	$changed = false;
	$queue   = array();
	foreach ( $map as $raw_uid => $record ) {
		$uid = sanitize_key( (string) $raw_uid );
		if ( '' === $uid || ! is_array( $record ) || ! vava_digital_products_private_file_path( $record ) ) { continue; }
		$fingerprint = sanitize_key( (string) ( $record['fingerprint'] ?? '' ) );
		$status      = sanitize_key( (string) ( $record['processing_status'] ?? '' ) );
		if ( $fingerprint && $status ) { continue; }
		$fingerprint = $fingerprint ?: wp_generate_password( 24, false, false );
		$map[ $uid ] = array_merge( $record, array(
			'fingerprint'         => $fingerprint,
			'processing_status'   => 'queued',
			'processing_progress' => 1,
			'processing_message'  => 'بانتظار تجهيز صفحات العرض المحمية.',
			'page_count'          => 0,
		) );
		$queue[] = array( $uid, $fingerprint );
		$changed = true;
	}
	if ( ! $changed ) { return; }
	update_post_meta( $post_id, '_vava_digital_product_files', $map );
	foreach ( $queue as $item ) { vava_digital_products_schedule_processing( $post_id, $item[0], $item[1] ); }
}
add_action( 'admin_init', 'vava_digital_products_maybe_migrate_legacy_files', 25 );

function vava_digital_products_convert_with_imagick( string $pdf, string $dir, int $post_id, string $uid, string $fingerprint ): int {
	if ( ! class_exists( 'Imagick' ) ) { return 0; }
	$probe = new Imagick();
	$probe->pingImage( $pdf );
	$count = (int) $probe->getNumberImages();
	$probe->clear();
	if ( $count < 1 ) { return 0; }
	for ( $index = 0; $index < $count; $index++ ) {
		$image = new Imagick();
		$image->setResolution( 150, 150 );
		$image->readImage( $pdf . '[' . $index . ']' );
		$image->setImageBackgroundColor( '#fffdf8' );
		if ( method_exists( $image, 'mergeImageLayers' ) ) { $image = $image->mergeImageLayers( Imagick::LAYERMETHOD_FLATTEN ); }
		$image->setImageFormat( 'jpeg' );
		$image->setImageCompression( Imagick::COMPRESSION_JPEG );
		$image->setImageCompressionQuality( 88 );
		if ( $image->getImageWidth() > 1800 ) { $image->resizeImage( 1800, 0, Imagick::FILTER_LANCZOS, 1 ); }
		$image->stripImage();
		$image->writeImage( trailingslashit( $dir ) . sprintf( 'page-%04d.jpg', $index + 1 ) );
		$image->clear();
		vava_digital_products_update_file_record( $post_id, $uid, array( 'processing_progress' => min( 96, (int) round( ( $index + 1 ) / $count * 94 ) + 2 ), 'processing_message' => sprintf( 'جارٍ تجهيز الصفحة %d من %d.', $index + 1, $count ) ), $fingerprint );
	}
	return $count;
}

function vava_digital_products_convert_with_pdftoppm( string $pdf, string $dir ): int {
	$binary = '';
	foreach ( array( '/usr/bin/pdftoppm', '/usr/local/bin/pdftoppm' ) as $candidate ) { if ( is_executable( $candidate ) ) { $binary = $candidate; break; } }
	if ( ! $binary || ! function_exists( 'proc_open' ) ) { return 0; }
	$prefix = trailingslashit( $dir ) . 'raw';
	$command = escapeshellarg( $binary ) . ' -jpeg -r 150 -jpegopt quality=88 ' . escapeshellarg( $pdf ) . ' ' . escapeshellarg( $prefix );
	$process = proc_open( $command, array( array( 'pipe', 'r' ), array( 'pipe', 'w' ), array( 'pipe', 'w' ) ), $pipes );
	if ( ! is_resource( $process ) ) { return 0; }
	foreach ( $pipes as $pipe ) { fclose( $pipe ); }
	$status = proc_close( $process );
	if ( 0 !== $status ) { return 0; }
	$files = glob( $prefix . '-*.jpg' );
	if ( ! is_array( $files ) || ! $files ) { return 0; }
	natsort( $files );
	$page = 1;
	foreach ( $files as $file ) { @rename( $file, trailingslashit( $dir ) . sprintf( 'page-%04d.jpg', $page++ ) ); } // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	return $page - 1;
}

function vava_digital_products_process_pdf( int $post_id, string $uid, string $fingerprint ): void {
	if ( function_exists( 'wp_raise_memory_limit' ) ) { wp_raise_memory_limit( 'admin' ); }
	if ( function_exists( 'set_time_limit' ) ) { @set_time_limit( 0 ); } // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	$record = vava_digital_products_file_record( $uid, $post_id );
	if ( ! $record || (string) ( $record['fingerprint'] ?? '' ) !== $fingerprint ) { return; }
	$pdf = vava_digital_products_private_file_path( $record );
	if ( ! $pdf ) { vava_digital_products_update_file_record( $post_id, $uid, array( 'processing_status' => 'failed', 'processing_message' => 'ملف PDF الأصلي غير متاح.' ), $fingerprint ); return; }
	$pages_root = vava_digital_products_pages_root();
	$folder = $uid . '-' . $fingerprint;
	$dir = trailingslashit( $pages_root['path'] ) . $folder;
	if ( is_dir( $dir ) ) { vava_digital_products_remove_directory( $dir ); }
	wp_mkdir_p( $dir );
	vava_digital_products_update_file_record( $post_id, $uid, array( 'processing_status' => 'processing', 'processing_progress' => 3, 'processing_message' => 'جارٍ تحويل PDF إلى صفحات مشاهدة محمية.', 'pages_relative_dir' => trailingslashit( $pages_root['relative'] ) . $folder ), $fingerprint );
	try {
		$count = vava_digital_products_convert_with_imagick( $pdf, $dir, $post_id, $uid, $fingerprint );
		if ( ! $count ) { $count = vava_digital_products_convert_with_pdftoppm( $pdf, $dir ); }
		if ( ! $count ) { throw new RuntimeException( 'خادم الاستضافة لا يدعم تحويل PDF حاليًا. يلزم تفعيل Imagick مع Ghostscript أو pdftoppm.' ); }
		vava_digital_products_update_file_record( $post_id, $uid, array( 'processing_status' => 'ready', 'processing_progress' => 100, 'processing_message' => 'الملف جاهز للمشاهدة المحمية.', 'page_count' => $count, 'processed_at' => current_time( 'mysql' ) ), $fingerprint );
	} catch ( Throwable $error ) {
		vava_digital_products_remove_directory( $dir );
		vava_digital_products_update_file_record( $post_id, $uid, array( 'processing_status' => 'failed', 'processing_progress' => 0, 'processing_message' => sanitize_text_field( $error->getMessage() ), 'page_count' => 0 ), $fingerprint );
	}
}
add_action( 'vava_digital_products_process_pdf', 'vava_digital_products_process_pdf', 10, 3 );

/** Return a capability-protected thumbnail URL for the first processed PDF page. */
function vava_digital_products_admin_thumbnail_url( int $post_id, string $uid, array $record = array() ): string {
	$uid = sanitize_key( $uid );
	if ( ! $post_id || '' === $uid ) { return ''; }
	$record = $record ?: vava_digital_products_file_record( $uid, $post_id );
	if ( 'ready' !== (string) ( $record['processing_status'] ?? '' ) || ! vava_digital_products_private_page_path( $record, 1 ) ) { return ''; }
	return add_query_arg(
		array(
			'action'  => 'vava_digital_private_pdf_thumbnail',
			'post_id' => $post_id,
			'uid'     => $uid,
			'_wpnonce'=> wp_create_nonce( 'vava_digital_private_pdf_thumbnail_' . $post_id . '_' . $uid ),
		),
		admin_url( 'admin-ajax.php' )
	);
}

function vava_digital_products_admin_record_payload( int $post_id, string $uid, array $record = array() ): array {
	$record = $record ?: vava_digital_products_file_record( $uid, $post_id );
	$record['admin_thumbnail_url'] = vava_digital_products_admin_thumbnail_url( $post_id, $uid, $record );
	return $record;
}

function vava_digital_products_admin_pdf_thumbnail(): void {
	$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$uid     = isset( $_GET['uid'] ) ? sanitize_key( wp_unslash( $_GET['uid'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$nonce   = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || '' === $uid || ! current_user_can( 'edit_page', $post_id ) || ! wp_verify_nonce( $nonce, 'vava_digital_private_pdf_thumbnail_' . $post_id . '_' . $uid ) ) {
		status_header( 403 ); exit;
	}
	$record = vava_digital_products_file_record( $uid, $post_id );
	$path   = vava_digital_products_private_page_path( $record, 1 );
	if ( ! $path ) { status_header( 404 ); exit; }
	while ( ob_get_level() ) { ob_end_clean(); }
	header( 'Content-Type: image/jpeg' );
	header( 'Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0' );
	header( 'X-Content-Type-Options: nosniff' );
	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
	exit;
}
add_action( 'wp_ajax_vava_digital_private_pdf_thumbnail', 'vava_digital_products_admin_pdf_thumbnail' );

/** Public product-cover URL generated from page one; the original PDF remains private. */
function vava_digital_products_cover_url( string $uid, int $post_id = 0 ): string {
	$uid = sanitize_key( $uid );
	$post_id = $post_id > 0 ? $post_id : ( function_exists( 'vava_selections_page_id' ) ? vava_selections_page_id() : 0 );
	$record = $uid ? vava_digital_products_file_record( $uid, $post_id ) : array();
	if ( 'ready' !== (string) ( $record['processing_status'] ?? '' ) || ! vava_digital_products_private_page_path( $record, 1 ) ) { return ''; }
	return add_query_arg( array( 'action' => 'vava_digital_product_cover', 'product' => $uid, 'v' => sanitize_key( (string) ( $record['fingerprint'] ?? '' ) ) ), admin_url( 'admin-ajax.php' ) );
}

function vava_digital_products_public_cover(): void {
	$uid = isset( $_GET['product'] ) ? sanitize_key( wp_unslash( $_GET['product'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$version = isset( $_GET['v'] ) ? sanitize_key( wp_unslash( $_GET['v'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$record = $uid ? vava_digital_products_file_record( $uid ) : array();
	$path = vava_digital_products_private_page_path( $record, 1 );
	if ( ! $path || 'ready' !== (string) ( $record['processing_status'] ?? '' ) || '' === $version || ! hash_equals( sanitize_key( (string) ( $record['fingerprint'] ?? '' ) ), $version ) ) { status_header( 404 ); exit; }
	while ( ob_get_level() ) { ob_end_clean(); }
	header( 'Content-Type: image/jpeg' );
	header( 'Cache-Control: public, max-age=86400, immutable' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Robots-Tag: noindex, noarchive' );
	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
	exit;
}
add_action( 'wp_ajax_vava_digital_product_cover', 'vava_digital_products_public_cover' );
add_action( 'wp_ajax_nopriv_vava_digital_product_cover', 'vava_digital_products_public_cover' );

/** Render the protected-PDF field inside each digital product editor card. */
function vava_digital_products_render_admin_file_field( int $post_id, string $uid, string $lang = 'ar' ): void {
	$uid = sanitize_key( $uid );
	if ( '' === $uid || false !== strpos( $uid, '__' ) ) { return; }
	$record = vava_digital_products_admin_record_payload( $post_id, $uid );
	$exists = '' !== vava_digital_products_private_file_path( $record );
	$is_en  = 'en' === $lang;
	$status = sanitize_key( (string) ( $record['processing_status'] ?? ( $exists ? 'queued' : 'empty' ) ) );
	$progress = absint( $record['processing_progress'] ?? 0 );
	$message = (string) ( $record['processing_message'] ?? '' );
	$thumbnail = (string) ( $record['admin_thumbnail_url'] ?? '' );
	?>
	<div class="vava-repeater-field vava-repeater-field-wide vava-protected-pdf-field vava-admin-field-media" data-vava-private-pdf-field data-product-uid="<?php echo esc_attr( $uid ); ?>" data-post-id="<?php echo esc_attr( (string) $post_id ); ?>" data-processing-status="<?php echo esc_attr( $status ); ?>">
		<label><span><?php echo esc_html( $is_en ? 'Protected PDF file' : 'ملف PDF المحمي' ); ?></span></label>
		<div class="vava-media-field vava-private-file-upload">
			<div class="vava-media-dropzone vava-private-file-dropzone" role="button" tabindex="0">
				<div class="vava-media-preview">
					<div class="vava-private-file-state<?php echo $exists ? ' has-file' : ''; ?><?php echo $thumbnail ? ' has-thumbnail' : ''; ?>">
						<?php if ( $thumbnail ) : ?><img class="vava-private-file-thumbnail" data-private-file-thumbnail src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $is_en ? 'First PDF page thumbnail' : 'صورة مصغرة لأول صفحة من الملف' ); ?>"/><?php else : ?><span class="dashicons dashicons-pdf" data-private-file-icon aria-hidden="true"></span><?php endif; ?>
						<div class="vava-private-file-caption"><strong><?php echo esc_html( $exists ? ( $is_en ? 'Protected PDF selected' : 'تم اختيار ملف PDF المحمي' ) : ( $is_en ? 'Choose a protected PDF' : 'اختر ملف PDF المحمي' ) ); ?></strong></div>
					</div>
				</div>
				<input class="vava-private-file-input" type="file" accept="application/pdf,.pdf"/>
			</div>
			<?php $show_progress = in_array( $status, array( 'queued', 'processing', 'failed' ), true ); ?>
		<div class="vava-upload-progress<?php echo $show_progress ? ' is-active' : ''; ?><?php echo 'failed' === $status ? ' is-error' : ''; ?>" data-vava-upload-progress aria-live="polite"<?php echo $show_progress ? '' : ' hidden'; ?>>
				<div class="vava-upload-progress-head"><strong data-upload-progress-label><?php echo esc_html( $message ?: ( $is_en ? 'Ready to upload' : 'جاهز للرفع' ) ); ?></strong><span data-upload-progress-percent><?php echo esc_html( (string) $progress ); ?>%</span></div>
				<div class="vava-upload-progress-track"><i data-upload-progress-bar style="width:<?php echo esc_attr( (string) $progress ); ?>%"></i></div>
				<small data-upload-progress-meta></small>
			</div>
			<div class="vava-media-actions">
				<button class="button vava-media-select" type="button" data-private-file-select><?php echo esc_html( $exists ? ( $is_en ? 'Replace' : 'استبدال' ) : ( $is_en ? 'Choose PDF' : 'اختيار ملف PDF' ) ); ?></button>
				<?php if ( $exists ) : ?><button class="button vava-media-remove vava-private-file-delete" type="button" data-private-file-delete><?php echo esc_html( $is_en ? 'Delete' : 'حذف' ); ?></button><?php endif; ?>
				<?php if ( 'failed' === $status ) : ?><button class="button" type="button" data-private-file-reprocess><?php echo esc_html( $is_en ? 'Retry processing' : 'إعادة تجهيز الملف' ); ?></button><?php endif; ?>
			</div>
			<p class="description vava-private-file-note"><span class="dashicons dashicons-lock" aria-hidden="true"></span><span><?php echo esc_html( $is_en ? 'The original PDF stays private. Customers receive protected page-by-page viewing only after manual approval.' : 'يبقى ملف PDF الأصلي في التخزين الخاص، ويشاهد العميل صفحاته المحمية فقط بعد اعتماد التحويل يدويًا.' ); ?></span></p>
		</div>
	</div>
	<?php
}

function vava_digital_products_delete_private_record( array $record ): void {
	$path = vava_digital_products_private_file_path( $record );
	if ( $path ) { @unlink( $path ); } // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	$pages = vava_digital_products_private_pages_dir( $record );
	if ( $pages ) { vava_digital_products_remove_directory( $pages ); }
}

/** Save uploads selected from the advanced Selections editor (fallback for non-AJAX browsers). */
function vava_digital_products_save_private_files( int $post_id, WP_Post $post ): void {
	if ( 'page' !== $post->post_type || ! function_exists( 'vava_selections_is_page' ) || ! vava_selections_is_page( $post_id ) ) { return; }
	if ( ! isset( $_POST['vava_selections_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vava_selections_nonce'] ) ), 'vava_selections_save' ) ) { return; }
	if ( ! current_user_can( 'edit_page', $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) { return; }
	$files = isset( $_FILES['vava_digital_product_pdf'] ) && is_array( $_FILES['vava_digital_product_pdf'] ) ? $_FILES['vava_digital_product_pdf'] : array();
	if ( empty( $files['name'] ) || ! is_array( $files['name'] ) ) { return; }
	foreach ( $files['name'] as $language => $language_names ) {
		if ( ! is_array( $language_names ) ) { continue; }
		foreach ( $language_names as $raw_uid => $name ) {
			$uid = sanitize_key( (string) $raw_uid );
			$file = array(
				'name' => $name,
				'tmp_name' => $files['tmp_name'][ $language ][ $raw_uid ] ?? '',
				'size' => $files['size'][ $language ][ $raw_uid ] ?? 0,
				'error' => $files['error'][ $language ][ $raw_uid ] ?? UPLOAD_ERR_NO_FILE,
				'type' => $files['type'][ $language ][ $raw_uid ] ?? 'application/pdf',
			);
			if ( UPLOAD_ERR_NO_FILE !== absint( $file['error'] ) ) { vava_digital_products_store_uploaded_pdf( $post_id, $uid, $file ); }
		}
	}
}
add_action( 'save_post_page', 'vava_digital_products_save_private_files', 60, 2 );


/** AJAX upload and processing controls for protected PDFs. */
function vava_digital_products_admin_verify_request(): array {
	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$uid = isset( $_POST['uid'] ) ? sanitize_key( wp_unslash( $_POST['uid'] ) ) : '';
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! $post_id || '' === $uid || ! wp_verify_nonce( $nonce, 'vava_digital_product_admin_' . $post_id ) || ! current_user_can( 'edit_page', $post_id ) ) {
		wp_send_json_error( array( 'message' => 'غير مصرح بتنفيذ هذا الإجراء.' ), 403 );
	}
	return array( $post_id, $uid );
}

function vava_digital_products_admin_upload_pdf(): void {
	list( $post_id, $uid ) = vava_digital_products_admin_verify_request();
	$file = isset( $_FILES['file'] ) && is_array( $_FILES['file'] ) ? $_FILES['file'] : array();
	$record = vava_digital_products_store_uploaded_pdf( $post_id, $uid, $file );
	if ( is_wp_error( $record ) ) { wp_send_json_error( array( 'message' => $record->get_error_message() ), 422 ); }
	wp_send_json_success( array( 'record' => vava_digital_products_admin_record_payload( $post_id, $uid, $record ), 'message' => 'تم رفع PDF وبدأ تجهيز صفحات المشاهدة المحمية.' ) );
}
add_action( 'wp_ajax_vava_digital_private_pdf_upload', 'vava_digital_products_admin_upload_pdf' );

function vava_digital_products_admin_delete_pdf(): void {
	list( $post_id, $uid ) = vava_digital_products_admin_verify_request();
	$map = vava_digital_products_file_map( $post_id );
	if ( isset( $map[ $uid ] ) && is_array( $map[ $uid ] ) ) { vava_digital_products_delete_private_record( $map[ $uid ] ); }
	unset( $map[ $uid ] );
	update_post_meta( $post_id, '_vava_digital_product_files', $map );
	wp_send_json_success( array( 'message' => 'تم حذف الملف المحمي.' ) );
}
add_action( 'wp_ajax_vava_digital_private_pdf_delete', 'vava_digital_products_admin_delete_pdf' );

function vava_digital_products_admin_pdf_status(): void {
	list( $post_id, $uid ) = vava_digital_products_admin_verify_request();
	$record = vava_digital_products_file_record( $uid, $post_id );
	wp_send_json_success( array( 'record' => vava_digital_products_admin_record_payload( $post_id, $uid, $record ) ) );
}
add_action( 'wp_ajax_vava_digital_private_pdf_status', 'vava_digital_products_admin_pdf_status' );

function vava_digital_products_admin_reprocess_pdf(): void {
	list( $post_id, $uid ) = vava_digital_products_admin_verify_request();
	$record = vava_digital_products_file_record( $uid, $post_id );
	$fingerprint = (string) ( $record['fingerprint'] ?? '' );
	if ( ! $fingerprint || ! vava_digital_products_private_file_path( $record ) ) { wp_send_json_error( array( 'message' => 'ملف PDF الأصلي غير متاح.' ), 404 ); }
	vava_digital_products_update_file_record( $post_id, $uid, array( 'processing_status' => 'queued', 'processing_progress' => 1, 'processing_message' => 'بانتظار إعادة تجهيز صفحات المشاهدة.' ), $fingerprint );
	vava_digital_products_schedule_processing( $post_id, $uid, $fingerprint );
	wp_send_json_success( array( 'message' => 'بدأت إعادة تجهيز الملف.' ) );
}
add_action( 'wp_ajax_vava_digital_private_pdf_reprocess', 'vava_digital_products_admin_reprocess_pdf' );

/** Product order status used by the detail CTA and customer library. */
function vava_digital_products_frontend_state( string $uid ): array {
	$user = function_exists( 'vava_customer_current_verified_user' ) ? vava_customer_current_verified_user() : null;
	if ( ! $user instanceof WP_User ) { return array( 'state' => 'guest', 'order_id' => 0 ); }
	$order_id = vava_digital_products_latest_order( $uid, $user->ID );
	if ( ! $order_id ) { return array( 'state' => 'available', 'order_id' => 0 ); }
	$access  = vava_digital_products_order_access_status( $order_id );
	$payment = function_exists( 'vava_booking_effective_payment_status' ) ? vava_booking_effective_payment_status( $order_id ) : '';
	if ( 'active' === $access && 'paid' === $payment ) { return array( 'state' => 'active', 'order_id' => $order_id ); }
	if ( in_array( $payment, array( 'pending', 'pending_bank_review' ), true ) ) { return array( 'state' => 'pending', 'order_id' => $order_id ); }
	if ( in_array( $payment, array( 'rejected', 'failed', 'cancelled' ), true ) ) { return array( 'state' => 'rejected', 'order_id' => $order_id ); }
	return array( 'state' => $access ?: 'pending', 'order_id' => $order_id );
}

function vava_digital_products_render_purchase_action( array $data, string $lang = 'ar' ): void {
	$uid = sanitize_key( (string) ( $data['uid'] ?? '' ) );
	if ( '' === $uid ) { return; }
	$is_en  = 'en' === $lang;
	$state  = vava_digital_products_frontend_state( $uid );
	$record = vava_digital_products_file_record( $uid );
	$file_ready = 'ready' === (string) ( $record['processing_status'] ?? '' ) && absint( $record['page_count'] ?? 0 ) > 0;
	if ( 'active' === $state['state'] ) {
		?><a class="vava-product-purchase-button is-view" href="<?php echo esc_url( vava_digital_products_viewer_url( $uid, $lang ) ); ?>"><span><?php echo esc_html( $is_en ? 'Open product' : 'مشاهدة المنتج' ); ?></span><i aria-hidden="true">◉</i></a><?php
	} elseif ( 'pending' === $state['state'] ) {
		?><a class="vava-product-purchase-button is-pending" href="<?php echo esc_url( function_exists( 'vava_customer_account_url' ) ? vava_customer_account_url( $lang, array( 'view' => 'products' ) ) : '#' ); ?>"><span><?php echo esc_html( $is_en ? 'Payment under review' : 'التحويل قيد المراجعة' ); ?></span><i aria-hidden="true">◷</i></a><?php
	} else {
		?><a class="vava-product-purchase-button" href="<?php echo esc_url( vava_digital_products_checkout_url( $uid, $lang ) ); ?>"><span><?php echo esc_html( $is_en ? 'Buy now' : 'اشترِ الآن' ); ?></span><i aria-hidden="true">▣</i></a><?php
	}
	?><p class="vava-product-access-note"><span aria-hidden="true">◇</span><?php echo esc_html( $file_ready ? ( $is_en ? 'Protected online viewing after manual payment approval — no direct download link.' : 'مشاهدة محمية داخل الموقع بعد اعتماد التحويل يدويًا — بدون رابط تحميل مباشر.' ) : ( $is_en ? 'The protected PDF will become available after it is uploaded by the VAVA team.' : 'سيصبح الملف متاحًا بعد رفع ملف PDF المحمي من لوحة التحكم.' ) ); ?></p><?php
}

function vava_digital_products_checkout_customer_payload(): array {
	$country = isset( $_POST['whatsapp_country'] ) ? strtoupper( sanitize_key( wp_unslash( $_POST['whatsapp_country'] ) ) ) : ( isset( $_POST['country_iso'] ) ? strtoupper( sanitize_key( wp_unslash( $_POST['country_iso'] ) ) ) : 'SA' );
	$local   = isset( $_POST['whatsapp_local'] ) ? sanitize_text_field( wp_unslash( $_POST['whatsapp_local'] ) ) : '';
	$phone   = function_exists( 'vava_booking_normalize_whatsapp' ) ? vava_booking_normalize_whatsapp( $country, $local ) : preg_replace( '/[^0-9+]/', '', $local );
	$previous = isset( $_POST['previous'] ) ? sanitize_key( wp_unslash( $_POST['previous'] ) ) : '';
	if ( ! in_array( $previous, array( 'yes', 'no' ), true ) ) {
		$previous = '';
	}
	return array(
		'name'        => isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '',
		'email'       => isset( $_POST['customer_email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['customer_email'] ) ) ) : '',
		'whatsapp'    => $phone,
		'whatsapp_country' => $country,
		'whatsapp_local'   => function_exists( 'vava_booking_phone_digits' ) ? vava_booking_phone_digits( $local ) : preg_replace( '/\D+/', '', $local ),
		'previous'    => $previous,
		'notes'       => isset( $_POST['customer_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['customer_notes'] ) ) : '',
	);
}

function vava_digital_products_send_order_received( int $order_id ): void {
	if ( function_exists( 'vava_mail_notifications_enabled' ) && ! vava_mail_notifications_enabled( 'products' ) ) { return; }
	$customer = (array) get_post_meta( $order_id, '_vava_booking_customer', true );
	$email    = sanitize_email( (string) ( $customer['email'] ?? '' ) );
	$lang     = 'en' === get_post_meta( $order_id, '_vava_booking_language', true ) ? 'en' : 'ar';
	$title    = (string) get_post_meta( $order_id, '_vava_booking_service_title', true );
	if ( is_email( $email ) ) {
		$subject = 'en' === $lang ? 'Your VAVA digital product order was received' : 'تم استلام طلب المنتج الرقمي من VAVA';
		$message = 'en' === $lang
			? "We received your order for {$title}. Your transfer is now awaiting manual review. The product will appear in My Digital Products after approval.\n\nOrder #{$order_id}\n" . vava_customer_account_url( $lang, array( 'view' => 'products' ) )
			: "تم استلام طلبك للمنتج: {$title}. التحويل الآن بانتظار المراجعة اليدوية، وسيظهر المنتج داخل «منتجاتي الرقمية» بعد الاعتماد.\n\nرقم الطلب: #{$order_id}\n" . vava_customer_account_url( $lang, array( 'view' => 'products' ) );
		wp_mail( $email, $subject, $message );
	}
	$admin = get_option( 'admin_email' );
	if ( is_email( $admin ) ) {
		wp_mail( $admin, 'طلب منتج رقمي بانتظار اعتماد التحويل #' . $order_id, "المنتج: {$title}\nالعميل: " . (string) ( $customer['name'] ?? '' ) . "\n" . vava_booking_admin_details_url( $order_id ) );
	}
}

function vava_digital_products_send_order_status_update( int $order_id, string $event, string $note = '' ): void {
	if ( function_exists( 'vava_mail_notifications_enabled' ) && ! vava_mail_notifications_enabled( 'products' ) ) { return; }
	$customer = (array) get_post_meta( $order_id, '_vava_booking_customer', true );
	$email    = sanitize_email( (string) ( $customer['email'] ?? '' ) );
	if ( ! is_email( $email ) ) { return; }
	$lang  = 'en' === get_post_meta( $order_id, '_vava_booking_language', true ) ? 'en' : 'ar';
	$title = (string) get_post_meta( $order_id, '_vava_booking_service_title', true );
	$url   = vava_customer_account_url( $lang, array( 'view' => 'products' ) );
	if ( in_array( $event, array( 'bank_approved', 'access_restored' ), true ) ) {
		$subject = 'en' === $lang ? 'Your VAVA digital product is now available' : 'تم تفعيل منتجك الرقمي في VAVA';
		$message = 'en' === $lang ? "Your payment was approved and {$title} is now available in My Digital Products.\n\n{$url}" : "تم اعتماد التحويل وتفعيل المنتج «{$title}» داخل صفحة منتجاتي الرقمية.\n\n{$url}";
	} elseif ( 'bank_rejected' === $event ) {
		$subject = 'en' === $lang ? 'Digital product payment review update' : 'تحديث مراجعة تحويل المنتج الرقمي';
		$message = 'en' === $lang ? "The transfer for {$title} could not be approved." : "تعذر اعتماد التحويل الخاص بالمنتج «{$title}».";
		if ( $note ) { $message .= "\n\n" . $note; }
	} elseif ( 'access_revoked' === $event ) {
		$subject = 'en' === $lang ? 'Digital product access update' : 'تحديث صلاحية المنتج الرقمي';
		$message = 'en' === $lang ? "Access to {$title} has been paused. Contact VAVA if you need help." : "تم إيقاف صلاحية مشاهدة المنتج «{$title}». تواصل مع VAVA عند الحاجة للمساعدة.";
	} else {
		$subject = 'en' === $lang ? 'Your VAVA digital product order' : 'طلب المنتج الرقمي في VAVA';
		$message = 'en' === $lang ? "Order update for {$title}.\n\n{$url}" : "تحديث على طلب المنتج «{$title}».\n\n{$url}";
	}
	wp_mail( $email, $subject, $message );
	update_post_meta( $order_id, '_vava_booking_last_details_email_sent_at', current_time( 'mysql' ) );
}

/** AJAX checkout: creates a digital order in the same vava_booking admin workflow. */
function vava_digital_products_checkout_submit(): void {
	check_ajax_referer( 'vava_digital_checkout', 'nonce' );
	$lang = isset( $_POST['lang'] ) && 'en' === sanitize_key( wp_unslash( $_POST['lang'] ) ) ? 'en' : 'ar';
	$uid  = isset( $_POST['product'] ) ? sanitize_key( wp_unslash( $_POST['product'] ) ) : '';
	$product = function_exists( 'vava_digital_product_data' ) ? vava_digital_product_data( $uid, $lang ) : array();
	if ( ! $product ) { wp_send_json_error( array( 'message' => 'المنتج غير متاح.' ), 404 ); }
	$customer = vava_digital_products_checkout_customer_payload();
	if ( '' === $customer['name'] || ! is_email( $customer['email'] ) || '' === $customer['whatsapp'] || '' === $customer['previous'] ) { wp_send_json_error( array( 'message' => 'يرجى استكمال بيانات العميل بشكل صحيح.' ), 422 ); }
	$page_id = function_exists( 'vava_booking_page_id' ) ? vava_booking_page_id() : 0;
	$shared  = function_exists( 'vava_booking_shared_data' ) ? vava_booking_shared_data( $page_id ) : array();
	if ( empty( $shared['payment_methods']['bank'] ) || ! function_exists( 'vava_booking_bank_is_ready' ) || ! vava_booking_bank_is_ready( $shared ) ) { wp_send_json_error( array( 'message' => 'التحويل البنكي غير متاح حاليًا.' ), 422 ); }
	$transfer = function_exists( 'vava_booking_bank_transfer_payload' ) ? vava_booking_bank_transfer_payload() : array();
	foreach ( array( 'transfer_name', 'from_bank', 'from_account', 'reference', 'transfer_date', 'transfer_time', 'amount' ) as $required ) {
		if ( '' === trim( (string) ( $transfer[ $required ] ?? '' ) ) ) { wp_send_json_error( array( 'message' => 'يرجى استكمال بيانات التحويل البنكي.' ), 422 ); }
	}
	$file = isset( $_FILES['bank_receipt'] ) && is_array( $_FILES['bank_receipt'] ) ? $_FILES['bank_receipt'] : array();
	$receipt = function_exists( 'vava_booking_store_bank_receipt' ) ? vava_booking_store_bank_receipt( $file, 0 ) : new WP_Error( 'receipt', 'تعذر حفظ الإيصال.' );
	if ( is_wp_error( $receipt ) ) { wp_send_json_error( array( 'message' => $receipt->get_error_message() ), 422 ); }

	$order_id = wp_insert_post(
		array(
			'post_type'   => 'vava_booking',
			'post_status' => 'publish',
			'post_title'  => sprintf( '%s — %s — Digital #%s', $customer['name'], (string) $product['title'], wp_date( 'Ymd-His' ) ),
		),
		true
	);
	if ( is_wp_error( $order_id ) ) {
		if ( function_exists( 'vava_booking_delete_receipt_attachment' ) ) { vava_booking_delete_receipt_attachment( $receipt ); }
		wp_send_json_error( array( 'message' => $order_id->get_error_message() ), 500 );
	}
	$currency = 'en' === $lang ? 'SAR' : 'ر.س';
	$meta = array(
		'_vava_booking_order_type'       => 'digital_product',
		'_vava_digital_product_uid'      => $uid,
		'_vava_digital_access_status'    => 'pending',
		'_vava_booking_status'           => 'pending_bank_review',
		'_vava_booking_payment_status'   => 'pending_bank_review',
		'_vava_booking_service_uid'      => $uid,
		'_vava_booking_service_kind'     => 'digital_product',
		'_vava_booking_service_title'    => (string) $product['title'],
		'_vava_booking_service_image_id' => 0,
		'_vava_booking_service_price'    => (string) ( $product['price'] ?? '' ),
		'_vava_booking_service_currency' => $currency,
		'_vava_booking_date'             => '',
		'_vava_booking_time'             => '',
		'_vava_booking_duration'         => 0,
		'_vava_booking_customer'         => $customer,
		'_vava_booking_customer_email'   => strtolower( (string) $customer['email'] ),
		'_vava_booking_customer_phone'   => preg_replace( '/\D+/', '', (string) $customer['whatsapp'] ),
		'_vava_booking_payment_method'   => 'bank',
		'_vava_booking_language'         => $lang,
		'_vava_booking_created_at'       => current_time( 'mysql' ),
		'_vava_booking_bank_transfer'    => $transfer,
		'_vava_booking_bank_receipt'     => $receipt,
	);
	foreach ( $meta as $key => $value ) { update_post_meta( $order_id, $key, $value ); }
	if ( ! empty( $receipt['attachment_id'] ) ) { wp_update_post( array( 'ID' => absint( $receipt['attachment_id'] ), 'post_parent' => $order_id ) ); }
	if ( function_exists( 'vava_customer_prepare_account_for_booking' ) ) { vava_customer_prepare_account_for_booking( $order_id, $customer, $lang ); }
	vava_digital_products_send_order_received( $order_id );
	wp_send_json_success(
		array(
			'orderId'   => $order_id,
			'title'     => 'en' === $lang ? 'Your order was received' : 'تم استلام طلبك',
			'message'   => 'en' === $lang ? 'The transfer is awaiting manual review. The product will appear in My Digital Products after approval.' : 'التحويل الآن بانتظار المراجعة اليدوية، وسيظهر المنتج داخل «منتجاتي الرقمية» بعد الاعتماد.',
			'accountUrl'=> vava_customer_account_url( $lang, array( 'view' => 'products' ) ),
		)
	);
}
add_action( 'wp_ajax_vava_digital_checkout_submit', 'vava_digital_products_checkout_submit' );
add_action( 'wp_ajax_nopriv_vava_digital_checkout_submit', 'vava_digital_products_checkout_submit' );

/** Old PDF stream endpoint is intentionally disabled. */
function vava_digital_products_stream_file(): void {
	status_header( 410 );
	nocache_headers();
	header( 'Content-Type: text/plain; charset=utf-8' );
	exit( 'The direct PDF stream has been disabled. Use the protected VAVA reader.' );
}
add_action( 'admin_post_vava_digital_product_stream', 'vava_digital_products_stream_file' );
add_action( 'admin_post_nopriv_vava_digital_product_stream', 'vava_digital_products_stream_file' );

function vava_digital_products_stream_url( string $uid, int $user_id ): string {
	return vava_digital_products_viewer_url( $uid, function_exists( 'vava_current_language' ) ? vava_current_language() : 'ar' );
}

function vava_digital_products_reader_signature( string $uid, int $page, int $user_id, int $order_id, int $expires, string $fingerprint ): string {
	$payload = implode( '|', array( sanitize_key( $uid ), $page, $user_id, $order_id, $expires, $fingerprint ) );
	return hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
}

function vava_digital_products_reader_page_url( string $uid, int $page, int $user_id, int $order_id, string $fingerprint ): string {
	$expires = time() + 180;
	return add_query_arg(
		array(
			'action' => 'vava_digital_product_page',
			'product' => sanitize_key( $uid ),
			'page' => max( 1, $page ),
			'order' => $order_id,
			'expires' => $expires,
			'token' => vava_digital_products_reader_signature( $uid, $page, $user_id, $order_id, $expires, $fingerprint ),
		),
		admin_url( 'admin-post.php' )
	);
}

function vava_digital_products_reader_page_token(): void {
	$uid = isset( $_POST['product'] ) ? sanitize_key( wp_unslash( $_POST['product'] ) ) : '';
	$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 0;
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	$user = function_exists( 'vava_customer_current_verified_user' ) ? vava_customer_current_verified_user() : null;
	if ( ! $user instanceof WP_User || ! wp_verify_nonce( $nonce, 'vava_digital_reader_' . $uid . '_' . $user->ID ) || ! vava_digital_products_user_can_view( $user->ID, $uid ) ) {
		wp_send_json_error( array( 'message' => 'صلاحية المشاهدة غير متاحة.' ), 403 );
	}
	$order_id = vava_digital_products_latest_order( $uid, $user->ID );
	$record = vava_digital_products_file_record( $uid );
	$count = absint( $record['page_count'] ?? 0 );
	$fingerprint = (string) ( $record['fingerprint'] ?? '' );
	if ( 'ready' !== (string) ( $record['processing_status'] ?? '' ) || ! $count || $page < 1 || $page > $count || ! vava_digital_products_private_page_path( $record, $page ) ) {
		wp_send_json_error( array( 'message' => 'الصفحة غير جاهزة للمشاهدة.' ), 404 );
	}
	wp_send_json_success( array( 'url' => vava_digital_products_reader_page_url( $uid, $page, $user->ID, $order_id, $fingerprint ), 'page' => $page, 'count' => $count ) );
}
add_action( 'wp_ajax_vava_digital_reader_page_token', 'vava_digital_products_reader_page_token' );
add_action( 'wp_ajax_nopriv_vava_digital_reader_page_token', 'vava_digital_products_reader_page_token' );

function vava_digital_products_output_watermarked_page( string $path, WP_User $user, int $order_id ): void {
	$customer = sanitize_text_field( ( $user->user_email ?: $user->display_name ) . ' · #' . $order_id );
	$logo_path = get_theme_file_path( 'assets/images/vava-logo.png' );
	if ( function_exists( 'imagecreatefromjpeg' ) && function_exists( 'imagejpeg' ) ) {
		$image = @imagecreatefromjpeg( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( $image ) {
			$width = imagesx( $image ); $height = imagesy( $image );
			$text_color = imagecolorallocatealpha( $image, 83, 91, 57, 110 );
			$brand_color = imagecolorallocatealpha( $image, 83, 91, 57, 112 );
			$logo = is_readable( $logo_path ) && function_exists( 'imagecreatefrompng' ) ? @imagecreatefrompng( $logo_path ) : false; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$step_y = max( 520, (int) floor( $height / 2 ) );
			for ( $y = 55; $y < $height; $y += $step_y ) {
				$offset = (int) ( ( $y / $step_y ) % 2 ) * max( 60, (int) floor( $width * .18 ) );
				for ( $x = 25 + $offset; $x < $width; $x += max( 860, (int) floor( $width * .88 ) ) ) {
					imagestring( $image, 3, $x, $y, $customer, $text_color );
					imagestring( $image, 5, $x + 26, $y + 24, 'VAVA LIVING', $brand_color );
					if ( $logo ) {
						$lw = imagesx( $logo ); $lh = imagesy( $logo ); $target_w = max( 42, (int) floor( $width * .045 ) ); $target_h = max( 18, (int) round( $lh * $target_w / max( 1, $lw ) ) );
						$temp_logo = imagecreatetruecolor( $target_w, $target_h );
						imagealphablending( $temp_logo, false ); imagesavealpha( $temp_logo, true );
						$transparent = imagecolorallocatealpha( $temp_logo, 255, 255, 255, 127 ); imagefill( $temp_logo, 0, 0, $transparent );
						imagecopyresampled( $temp_logo, $logo, 0, 0, 0, 0, $target_w, $target_h, $lw, $lh );
						imagecopymerge( $image, $temp_logo, $x + 5, $y + 40, 0, 0, $target_w, $target_h, 12 );
						imagedestroy( $temp_logo );
					}
				}
			}
			if ( $logo ) { imagedestroy( $logo ); }
			header( 'Content-Type: image/jpeg' ); imagejpeg( $image, null, 88 ); imagedestroy( $image ); return;
		}
	}
	if ( class_exists( 'Imagick' ) ) {
		try {
			$image = new Imagick( $path ); $draw = new ImagickDraw();
			$draw->setFillColor( new ImagickPixel( 'rgba(83,91,57,0.075)' ) );
			$draw->setFontSize( max( 10, (int) round( $image->getImageWidth() / 92 ) ) );
			$brand = new ImagickDraw(); $brand->setFillColor( new ImagickPixel( 'rgba(83,91,57,0.085)' ) ); $brand->setFontSize( max( 12, (int) round( $image->getImageWidth() / 78 ) ) );
			$logo = is_readable( $logo_path ) ? new Imagick( $logo_path ) : null;
			if ( $logo ) { $logo->setImageOpacity( .07 ); $logo->thumbnailImage( max( 44, (int) round( $image->getImageWidth() * .045 ) ), 0 ); }
			$step_y = max( 520, (int) floor( $image->getImageHeight() / 2 ) );
			for ( $y = 60; $y < $image->getImageHeight(); $y += $step_y ) {
				$offset = (int) ( ( $y / $step_y ) % 2 ) * max( 60, (int) floor( $image->getImageWidth() * .18 ) );
				for ( $x = 25 + $offset; $x < $image->getImageWidth(); $x += max( 860, (int) floor( $image->getImageWidth() * .88 ) ) ) {
					$image->annotateImage( $draw, $x, $y, -24, $customer );
					$image->annotateImage( $brand, $x + 34, $y + 34, -24, 'VAVA LIVING' );
					if ( $logo ) { $image->compositeImage( $logo, Imagick::COMPOSITE_OVER, $x + 8, $y + 52 ); }
				}
			}
			$image->setImageFormat( 'jpeg' ); $image->setImageCompressionQuality( 88 ); header( 'Content-Type: image/jpeg' ); echo $image->getImagesBlob(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( $logo ) { $logo->clear(); } $image->clear(); return;
		} catch ( Throwable $error ) {}
	}
	header( 'Content-Type: image/jpeg' ); readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
}

function vava_digital_products_serve_reader_page(): void {
	$uid = isset( $_GET['product'] ) ? sanitize_key( wp_unslash( $_GET['product'] ) ) : '';
	$page = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 0;
	$order_id = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0;
	$expires = isset( $_GET['expires'] ) ? absint( $_GET['expires'] ) : 0;
	$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
	$user = function_exists( 'vava_customer_current_verified_user' ) ? vava_customer_current_verified_user() : null;
	$record = vava_digital_products_file_record( $uid );
	$fingerprint = (string) ( $record['fingerprint'] ?? '' );
	$valid = $user instanceof WP_User
		&& $expires >= time()
		&& $expires <= time() + 300
		&& $order_id === vava_digital_products_latest_order( $uid, $user->ID )
		&& vava_digital_products_user_can_view( $user->ID, $uid )
		&& hash_equals( vava_digital_products_reader_signature( $uid, $page, $user->ID, $order_id, $expires, $fingerprint ), $token );
	if ( ! $valid ) { status_header( 403 ); nocache_headers(); exit( 'Access denied.' ); }
	$path = vava_digital_products_private_page_path( $record, $page );
	if ( ! $path ) { status_header( 404 ); nocache_headers(); exit( 'Page unavailable.' ); }
	while ( ob_get_level() ) { ob_end_clean(); }
	header( 'Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Content-Security-Policy: default-src \'none\'' );
	vava_digital_products_output_watermarked_page( $path, $user, $order_id );
	exit;
}
add_action( 'admin_post_vava_digital_product_page', 'vava_digital_products_serve_reader_page' );
add_action( 'admin_post_nopriv_vava_digital_product_page', 'vava_digital_products_serve_reader_page' );

/** Product-specific admin detail workspace. */
function vava_digital_products_render_admin_order_details( int $order_id, bool $overlay = false ): void {
	$uid              = vava_digital_products_order_uid( $order_id );
	$lang             = 'en' === get_post_meta( $order_id, '_vava_booking_language', true ) ? 'en' : 'ar';
	$product          = vava_digital_product_data( $uid, $lang );
	$customer         = (array) get_post_meta( $order_id, '_vava_booking_customer', true );
	$status           = (string) get_post_meta( $order_id, '_vava_booking_status', true );
	$payment          = vava_booking_effective_payment_status( $order_id );
	$access           = vava_digital_products_order_access_status( $order_id );
	$transfer         = (array) get_post_meta( $order_id, '_vava_booking_bank_transfer', true );
	$receipt          = vava_booking_get_receipt( $order_id, false );
	$receipt_url      = vava_booking_admin_receipt_url( $order_id, false );
	$mime             = strtolower( (string) ( $receipt['mime'] ?? '' ) );
	$record           = vava_digital_products_file_record( $uid );
	$file_ok          = 'ready' === (string) ( $record['processing_status'] ?? '' ) && absint( $record['page_count'] ?? 0 ) > 0;
	$amount           = vava_booking_format_price_label( (string) get_post_meta( $order_id, '_vava_booking_service_price', true ), (string) get_post_meta( $order_id, '_vava_booking_service_currency', true ), 'ar' );
	$review_note      = (string) get_post_meta( $order_id, '_vava_booking_review_note', true );
	$refund_status    = vava_booking_refund_status( $order_id );
	$refund_remaining = vava_booking_refund_remaining( $order_id );
	$access_labels    = array( 'active' => 'الوصول مفعّل', 'revoked' => 'الوصول موقوف', 'rejected' => 'الوصول مرفوض', 'pending' => 'بانتظار التفعيل' );
	?>
	<article class="vava-booking-reader-article vava-booking-reader-article--workspace vava-digital-order-workspace" dir="rtl" data-booking-id="<?php echo esc_attr( (string) $order_id ); ?>">
		<header class="vava-booking-reader-header vava-booking-workspace-header">
			<div class="vava-booking-reader-title"><small>طلب منتج رقمي</small><h2>تفاصيل الطلب <b>#<?php echo esc_html( (string) $order_id ); ?></b></h2><p><?php echo esc_html( (string) ( $product['title'] ?? get_post_meta( $order_id, '_vava_booking_service_title', true ) ) ); ?></p></div>
			<div class="vava-booking-admin-detail-statuses"><span class="vava-booking-admin-status is-<?php echo esc_attr( sanitize_html_class( $payment ) ); ?>"><?php echo esc_html( vava_booking_payment_status_label( $payment ) ); ?></span><span class="vava-booking-admin-status is-<?php echo esc_attr( sanitize_html_class( $access ) ); ?>"><?php echo esc_html( $access_labels[ $access ] ?? $access ); ?></span></div>
			<?php if ( $overlay ) : ?><button class="vava-booking-reader-close-top" type="button" data-vava-booking-close aria-label="إغلاق">×</button><?php endif; ?>
		</header>
		<div class="vava-booking-admin-workspace">
			<aside class="vava-booking-admin-summary-rail">
				<section class="vava-booking-detail-card is-accent"><h3>ملخص الطلب</h3><?php vava_booking_admin_detail_row( 'رقم الطلب', '#' . $order_id, 'ltr' ); vava_booking_admin_detail_row( 'نوع الطلب', 'منتج رقمي' ); vava_booking_admin_detail_row( 'حالة الطلب', vava_booking_status_label( $status ) ); vava_booking_admin_detail_row( 'حالة الدفع', vava_booking_payment_status_label( $payment ) ); vava_booking_admin_detail_row( 'الإجمالي', $amount ); vava_booking_admin_detail_row( 'صلاحية المشاهدة', $access_labels[ $access ] ?? $access ); if ( $refund_status ) { vava_booking_admin_detail_row( 'حالة الاسترداد', vava_booking_refund_status_label( $refund_status ) ); } ?></section>
				<?php if ( $receipt_url ) : ?><section class="vava-booking-detail-card vava-booking-admin-receipt-card"><h3>إيصال التحويل</h3><a href="<?php echo esc_url( $receipt_url ); ?>" target="_blank" rel="noopener"><?php if ( 0 === strpos( $mime, 'image/' ) ) : ?><img src="<?php echo esc_url( $receipt_url ); ?>" alt="إيصال التحويل"><?php else : ?><span class="vava-booking-refund-document">PDF</span><?php endif; ?><span>عرض الإيصال بالحجم الكامل</span></a></section><?php endif; ?>
			</aside>
			<main class="vava-booking-admin-workspace-main">
				<div class="vava-booking-admin-primary-grid">
					<section class="vava-booking-detail-card"><h3>المنتج والملف</h3><?php vava_booking_admin_detail_row( 'المنتج', (string) ( $product['title'] ?? '' ) ); vava_booking_admin_detail_row( 'معرّف المنتج', $uid, 'ltr' ); vava_booking_admin_detail_row( 'الملف المحمي', $file_ok ? 'متاح ومحمي' : ( vava_digital_products_private_file_path( $record ) ? 'الملف مرفوع وقيد التجهيز' : 'لم يتم رفع الملف بعد' ) ); vava_booking_admin_detail_row( 'حالة المعالجة', (string) ( $record['processing_message'] ?? '—' ) ); vava_booking_admin_detail_row( 'عدد الصفحات', $file_ok ? (string) absint( $record['page_count'] ?? 0 ) : '—' ); ?></section>
					<section class="vava-booking-detail-card"><h3>معلومات العميل</h3><?php vava_booking_admin_detail_row( 'الاسم', (string) ( $customer['name'] ?? '' ) ); vava_booking_admin_detail_row( 'رقم الجوال', (string) ( $customer['whatsapp'] ?? '' ), 'ltr' ); vava_booking_admin_detail_row( 'البريد الإلكتروني', (string) ( $customer['email'] ?? '' ), 'ltr' ); vava_booking_admin_detail_row( 'سبق تجربة VAVA', 'yes' === (string) ( $customer['previous'] ?? '' ) ? 'نعم' : ( 'no' === (string) ( $customer['previous'] ?? '' ) ? 'لا' : '—' ) ); vava_booking_admin_detail_row( 'ملاحظات العميل', (string) ( $customer['notes'] ?? '' ) ); ?></section>
					<section class="vava-booking-detail-card"><h3>الدفع والتحويل</h3><?php vava_booking_admin_detail_row( 'طريقة الدفع', 'تحويل بنكي' ); vava_booking_admin_detail_row( 'اسم المحوّل', (string) ( $transfer['transfer_name'] ?? '' ) ); vava_booking_admin_detail_row( 'البنك المحوّل منه', (string) ( $transfer['from_bank'] ?? '' ) ); vava_booking_admin_detail_row( 'مرجع العملية', (string) ( $transfer['reference'] ?? '' ), 'ltr' ); vava_booking_admin_detail_row( 'المبلغ', (string) ( $transfer['amount'] ?? $amount ) ); ?></section>
				</div>
				<?php vava_booking_render_refund_overview( $order_id, $status, $refund_remaining ); ?>
				<div class="vava-booking-admin-secondary-grid"><section class="vava-booking-detail-card vava-booking-admin-note"><h3>ملاحظات الإدارة</h3><p>ملاحظة داخلية لا تظهر للعميل.</p><textarea name="action_note" rows="4" placeholder="اكتب ملاحظة إدارية..."><?php echo esc_textarea( $review_note ); ?></textarea><button type="button" class="vava-booking-save-note" data-booking-id="<?php echo esc_attr( (string) $order_id ); ?>">حفظ الملاحظة</button></section><section class="vava-booking-detail-card vava-booking-action-history"><h3>سجل النشاط</h3><?php vava_booking_render_action_history( $order_id ); ?></section></div>
				<?php vava_booking_render_refund_panel( $order_id, 'h3' ); ?>
			</main>
		</div>
		<footer class="vava-booking-reader-actions vava-booking-workspace-actions"><div><?php vava_booking_render_action_buttons( $order_id ); ?></div><?php if ( $overlay ) : ?><button type="button" class="vava-booking-reader-close-secondary" data-vava-booking-close>إغلاق التفاصيل</button><?php else : ?><a class="vava-booking-reader-close-secondary" href="<?php echo esc_url( vava_booking_admin_list_url( $order_id ) ); ?>">العودة إلى منتجات VAVA</a><?php endif; ?></footer>
	</article>
	<?php
}

/** Scripts for private-file inputs on the Selections editor. */
function vava_digital_products_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || ! function_exists( 'vava_selections_is_page' ) || ! vava_selections_is_page( $post_id ) ) { return; }
	wp_enqueue_script( 'vava-admin-digital-products', get_theme_file_uri( 'assets/js/admin-digital-products.js' ), array( 'jquery' ), vava_asset_version( 'assets/js/admin-digital-products.js' ), true );
	wp_localize_script( 'vava-admin-digital-products', 'VAVA_DIGITAL_PRODUCTS_ADMIN', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'postId'  => $post_id,
		'nonce'   => wp_create_nonce( 'vava_digital_product_admin_' . $post_id ),
		'labels'  => array(
			'uploading' => 'جارٍ رفع ملف PDF…',
			'processing'=> 'جارٍ تجهيز صفحات المشاهدة المحمية…',
			'ready'     => 'الملف جاهز للمشاهدة المحمية.',
			'failed'    => 'فشل تجهيز الملف.',
			'deleted'   => 'تم حذف الملف المحمي.',
		),
	) );
}
add_action( 'admin_enqueue_scripts', 'vava_digital_products_admin_assets', 45 );
