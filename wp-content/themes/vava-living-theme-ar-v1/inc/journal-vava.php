<?php
/**
 * Bilingual VAVA Journal page, advanced editor, article query, and AJAX pagination.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

function vava_journal_template_slug(): string {
	return 'page-templates/journal-vava.php';
}

function vava_journal_is_page( int $post_id ): bool {
	if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
		return false;
	}
	$template = (string) get_post_meta( $post_id, '_wp_page_template', true );
	$post     = get_post( $post_id );
	if ( vava_journal_template_slug() === $template ) {
		return true;
	}
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return in_array( $post->post_name, array( 'journal', 'vava-journal', 'magazine' ), true )
		|| in_array( trim( (string) $post->post_title ), array( 'المجلة', 'مجلة VAVA', 'VAVA Journal', 'Journal' ), true );
}

function vava_journal_title_defaults( array $defaults, int $post_id ): array {
	if ( vava_journal_is_page( $post_id ) ) {
		$defaults['ar'] = 'المجلة';
		$defaults['en'] = 'Journal';
	}
	return $defaults;
}
add_filter( 'vava_page_title_defaults', 'vava_journal_title_defaults', 10, 2 );

function vava_journal_advanced_editor( bool $is_advanced, int $post_id ): bool {
	return $is_advanced || vava_journal_is_page( $post_id );
}
add_filter( 'vava_is_advanced_page_editor', 'vava_journal_advanced_editor', 10, 2 );

function vava_journal_text_meta_key( string $lang ): string {
	return '_vava_journal_text_' . ( 'en' === $lang ? 'en' : 'ar' );
}

function vava_journal_text_defaults( string $lang = 'ar' ): array {
	if ( 'en' === $lang ) {
		return array(
			'hero' => array(
				'eyebrow' => 'Journal',
				'title'    => 'A space for exploration',
				'intro'    => 'Not all knowledge is received in the same way. Some of it is read, some is heard, and some begins as a small idea that changes the way we look at life.',
				'note'     => 'The Journal is a space where we share what we learn, reflect upon, and explore through the journey of conscious living. You may find articles, reflections, resources, and ideas that invite you to pause, notice, and move closer to life in all its depth.',
			),
			'articles' => array(
				'title'     => 'From the Journal',
				'intro'     => '',
				'read_more' => 'Read article',
				'empty'     => 'Coming soon',
			),
		);
	}

	return array(
		'hero' => array(
			'eyebrow' => 'المجلة',
			'title'    => 'مساحة للاستكشاف',
			'intro'    => 'ليست كل المعرفة تُكتسب بالطريقة نفسها. فبعضها يُقرأ، وبعضها يُسمع، وبعضها يبدأ بفكرة صغيرة تغيّر الطريقة التي ننظر بها إلى الحياة.',
			'note'     => 'المجلة هي مساحة نشارك فيها ما نتعلّمه، ونتأمله، ونختبره خلال رحلتنا مع الحياة الواعية. قد تجد هنا مقالات، وتأملات، وموارد، وأفكارًا تدعوك للتوقف، والملاحظة، والاقتراب أكثر من الحياة بكل ما فيها.',
		),
		'articles' => array(
			'title'     => 'من المجلة',
			'intro'     => '',
			'read_more' => 'قراءة المقال',
			'empty'     => 'قريبًا',
		),
	);
}

function vava_journal_shared_defaults(): array {
	return array(
		'hero_image_id'    => 0,
		'category_ids'     => array(),
		'category_order'   => array(),
		'display_mode'     => 'priority',
		'posts_per_page'   => 8,
		'featured_post_id' => 0,
		'show_articles'     => 0,
	);
}

function vava_journal_text_data( int $post_id, string $lang ): array {
	$lang     = 'en' === $lang ? 'en' : 'ar';
	$defaults = vava_journal_text_defaults( $lang );
	$saved    = get_post_meta( $post_id, vava_journal_text_meta_key( $lang ), true );
	return is_array( $saved ) ? array_replace_recursive( $defaults, $saved ) : $defaults;
}

function vava_journal_shared_data( int $post_id ): array {
	$defaults = vava_journal_shared_defaults();
	$saved    = get_post_meta( $post_id, '_vava_journal_shared', true );
	$data     = is_array( $saved ) ? array_replace_recursive( $defaults, $saved ) : $defaults;
	$data['hero_image_id']    = absint( $data['hero_image_id'] ?? 0 );
	$data['category_ids']     = array_values( array_unique( array_filter( array_map( 'absint', (array) ( $data['category_ids'] ?? array() ) ) ) ) );
	$raw_order                = array_values( array_unique( array_filter( array_map( 'absint', (array) ( $data['category_order'] ?? array() ) ) ) ) );
	$data['category_order']   = array_values( array_intersect( $raw_order, $data['category_ids'] ) );
	foreach ( $data['category_ids'] as $category_id ) {
		if ( ! in_array( $category_id, $data['category_order'], true ) ) {
			$data['category_order'][] = $category_id;
		}
	}
	$data['display_mode']     = in_array( (string) ( $data['display_mode'] ?? '' ), array( 'priority', 'random' ), true ) ? (string) $data['display_mode'] : 'priority';
	$data['posts_per_page']   = max( 1, min( 24, absint( $data['posts_per_page'] ?? 8 ) ) );
	$data['featured_post_id'] = absint( $data['featured_post_id'] ?? 0 );
	$data['show_articles']     = empty( $data['show_articles'] ) ? 0 : 1;
	if ( $data['featured_post_id'] > 0 ) {
		$featured = get_post( $data['featured_post_id'] );
		if ( ! $featured instanceof WP_Post || 'post' !== $featured->post_type || 'publish' !== $featured->post_status ) {
			$data['featured_post_id'] = 0;
		}
	}
	return $data;
}

function vava_journal_image_url( int $attachment_id, string $fallback_asset = '', string $size = 'full' ): string {
	if ( $attachment_id > 0 ) {
		$url = wp_get_attachment_image_url( $attachment_id, $size );
		if ( $url ) {
			return (string) $url;
		}
	}
	return '' !== $fallback_asset ? vava_asset_uri( $fallback_asset ) : '';
}

function vava_journal_fallback_article_image( int $post_id, int $index = 0 ): string {
	$assets = array(
		'assets/images/journal-approved-summer.webp',
		'assets/images/journal-seasonal-food.webp',
		'assets/images/journal-approved-presence.webp',
		'assets/images/journal-approved-nature.webp',
		'assets/images/journal-summer-rhythm.webp',
		'assets/images/journal-nature-lessons.webp',
	);
	$position = absint( $post_id + $index ) % count( $assets );
	return vava_asset_uri( $assets[ $position ] );
}

function vava_journal_sections( string $lang = 'ar' ): array {
	return 'en' === $lang
		? array( 'hero' => 'Hero', 'articles' => 'Articles' )
		: array( 'hero' => 'الهيرو', 'articles' => 'المقالات' );
}

function vava_journal_section_icon( string $section ): string {
	if ( 'articles' === $section ) {
		return '<svg viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>';
	}
	return '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="3"/><circle cx="9" cy="9" r="2"/><path d="m6 17 5-5 3 3 2-2 2 2"/></svg>';
}

function vava_journal_admin_text( string $key, string $lang = 'ar' ): string {
	$texts = array(
		'meta_title'        => array( 'ar' => 'إعدادات صفحة المجلة', 'en' => 'Journal Page Settings' ),
		'fields_language'   => array( 'ar' => 'لغة الحقول', 'en' => 'Fields language' ),
		'update'            => array( 'ar' => 'تحديث', 'en' => 'Update' ),
		'live_preview'      => array( 'ar' => 'معاينة مباشرة', 'en' => 'Live preview' ),
		'shared'            => array( 'ar' => 'إعدادات مشتركة بين اللغتين', 'en' => 'Settings shared between both languages' ),
		'choose_replace'    => array( 'ar' => 'اختيار أو استبدال', 'en' => 'Choose or replace' ),
		'delete_file'       => array( 'ar' => 'حذف الملف', 'en' => 'Delete file' ),
		'hero_image'        => array( 'ar' => 'صورة الهيرو', 'en' => 'Hero image' ),
		'categories'        => array( 'ar' => 'أقسام المقالات المعروضة', 'en' => 'Displayed article categories' ),
		'categories_help'   => array( 'ar' => 'يمكنك اختيار قسم واحد أو عدة أقسام. عدم اختيار أي قسم يعني عرض المقالات من جميع الأقسام.', 'en' => 'Choose one or multiple categories. Leaving all unchecked displays posts from every category.' ),
		'posts_per_page'    => array( 'ar' => 'عدد المقالات في الصفحة الواحدة', 'en' => 'Articles per page' ),
		'pagination_help'   => array( 'ar' => 'العدد يخص المقالات العادية فقط؛ المقال المميز ثابت ومستبعد من الترقيم.', 'en' => 'This count applies only to regular articles; the featured article stays fixed and is excluded from pagination.' ),
		'featured_article'  => array( 'ar' => 'المقال المميز الثابت', 'en' => 'Fixed featured article' ),
		'featured_help'     => array( 'ar' => 'اختر مقالًا ليظل ظاهرًا في جميع صفحات المجلة. لن يُحتسب ضمن عدد المقالات ولن يتكرر داخل النتائج.', 'en' => 'Choose an article to keep visible on every Journal page. It is not counted in the page size and will not repeat in the results.' ),
		'featured_auto'     => array( 'ar' => 'تلقائي — أحدث مقال مطابق للأقسام', 'en' => 'Automatic — latest article matching the categories' ),
		'featured_search'   => array( 'ar' => 'ابحث بعنوان المقال…', 'en' => 'Search article title…' ),
		'featured_no_match' => array( 'ar' => 'لا توجد نتائج مطابقة.', 'en' => 'No matching articles.' ),
		'no_categories'     => array( 'ar' => 'لا توجد أقسام مقالات مضافة حتى الآن.', 'en' => 'No post categories exist yet.' ),
		'display_mode'       => array( 'ar' => 'طريقة توزيع المقالات', 'en' => 'Article distribution mode' ),
		'mode_priority'      => array( 'ar' => 'حسب أولوية الأقسام', 'en' => 'By category priority' ),
		'mode_priority_help' => array( 'ar' => 'اسحب الأقسام المختارة لترتيب أي قسم يظهر أولًا.', 'en' => 'Drag selected categories to decide which category appears first.' ),
		'mode_random'        => array( 'ar' => 'عرض عشوائي من الأقسام', 'en' => 'Random mix from categories' ),
		'mode_random_help'   => array( 'ar' => 'يتم مزج مقالات الأقسام المختارة في ترتيب ثابت ومتوازن داخل التصفح.', 'en' => 'Posts from the selected categories are mixed in a stable, balanced order across pagination.' ),
		'category_priority'  => array( 'ar' => 'أولوية الأقسام المختارة', 'en' => 'Selected category priority' ),
		'category_priority_empty' => array( 'ar' => 'اختر قسمًا واحدًا على الأقل ليظهر ترتيب الأولوية هنا.', 'en' => 'Select at least one category to arrange its priority here.' ),
		'content_settings'   => array( 'ar' => 'النصوص الأساسية لقسم المقالات', 'en' => 'Core article section copy' ),
		'content_settings_help' => array( 'ar' => 'تحكم في عنوان القسم ونص رابط المقال.', 'en' => 'Control the section title and article-link text.' ),
		'explanatory_copy'   => array( 'ar' => 'عرض المقالات ورسالة عدم وجود مقالات', 'en' => 'Article visibility and empty-state message' ),
		'explanatory_copy_help' => array( 'ar' => 'تحكم في ظهور جميع المقالات والمقال المميز، وحدد الرسالة التي تظهر عند إيقاف العرض أو عدم وجود نتائج.', 'en' => 'Control all article output, including the featured article, and set the message shown when output is disabled or no results exist.' ),
		'show_articles'      => array( 'ar' => 'عرض المقالات في صفحة المجلة', 'en' => 'Show articles on the Journal page' ),
		'show_articles_help' => array( 'ar' => 'عند التعطيل يختفي المقال المميز وشبكة المقالات والأقسام، وتظهر رسالة عدم وجود مقالات فقط.', 'en' => 'When disabled, the featured article, article grid, and categories are hidden, and only the no-articles message is shown.' ),
		'hero_eyebrow'      => array( 'ar' => 'النص الصغير', 'en' => 'Small text' ),
		'hero_title'        => array( 'ar' => 'العنوان الرئيسي', 'en' => 'Main title' ),
		'hero_intro'        => array( 'ar' => 'المقدمة', 'en' => 'Introduction' ),
		'hero_note'         => array( 'ar' => 'النص الداعم', 'en' => 'Supporting text' ),
		'articles_title'    => array( 'ar' => 'عنوان قسم المقالات', 'en' => 'Articles section title' ),
		'articles_intro'    => array( 'ar' => 'وصف قسم المقالات', 'en' => 'Articles section description' ),
		'read_more'         => array( 'ar' => 'نص رابط المقال', 'en' => 'Article link text' ),
		'empty_message'     => array( 'ar' => 'رسالة عدم وجود مقالات', 'en' => 'No articles message' ),
	);
	$lang = 'en' === $lang ? 'en' : 'ar';
	return (string) ( $texts[ $key ][ $lang ] ?? $texts[ $key ]['ar'] ?? $key );
}

/**
 * Build the category constraint shared by the featured and paginated queries.
 */
function vava_journal_category_tax_query( array $category_ids ): array {
	if ( empty( $category_ids ) ) {
		return array();
	}
	return array(
		array(
			'taxonomy'         => 'category',
			'field'            => 'term_id',
			'terms'            => $category_ids,
			'include_children' => true,
			'operator'         => 'IN',
		),
	);
}

/**
 * Resolve the fixed featured article. A manually selected published post wins;
 * otherwise the latest article matching the selected categories is used.
 */
function vava_journal_featured_post_id( int $page_id, string $lang = 'ar' ): int {
	static $resolved = array();
	$lang      = vava_normalize_language( $lang );
	$cache_key = $page_id . ':' . $lang;
	if ( array_key_exists( $cache_key, $resolved ) ) {
		return (int) $resolved[ $cache_key ];
	}

	$shared   = vava_journal_shared_data( $page_id );
	$selected = absint( $shared['featured_post_id'] ?? 0 );
	if ( $selected > 0 ) {
		$resolved[ $cache_key ] = $selected;
		return $selected;
	}

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 1,
		'fields'              => 'ids',
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'suppress_filters'    => false,
	);
	$tax_query = vava_journal_category_tax_query( (array) $shared['category_ids'] );
	if ( $tax_query ) {
		$args['tax_query'] = $tax_query;
	}
	$args = (array) apply_filters( 'vava_journal_featured_query_args', $args, $lang, $page_id );
	$ids  = get_posts( $args );
	$resolved[ $cache_key ] = ! empty( $ids ) ? absint( $ids[0] ) : 0;
	return (int) $resolved[ $cache_key ];
}

function vava_journal_featured_post( int $page_id, string $lang = 'ar' ): ?WP_Post {
	$post_id = vava_journal_featured_post_id( $page_id, $lang );
	$post    = $post_id > 0 ? get_post( $post_id ) : null;
	return $post instanceof WP_Post && 'post' === $post->post_type && 'publish' === $post->post_status ? $post : null;
}

function vava_journal_candidate_post_ids( int $page_id, string $lang = 'ar' ): array {
	$shared      = vava_journal_shared_data( $page_id );
	$featured_id = vava_journal_featured_post_id( $page_id, $lang );
	$args        = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => -1,
		'fields'              => 'ids',
		'orderby'             => array( 'date' => 'DESC', 'ID' => 'DESC' ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'suppress_filters'    => false,
	);
	if ( $featured_id > 0 ) {
		$args['post__not_in'] = array( $featured_id );
	}
	$tax_query = vava_journal_category_tax_query( (array) $shared['category_ids'] );
	if ( $tax_query ) {
		$args['tax_query'] = $tax_query;
	}
	$ids = get_posts( (array) apply_filters( 'vava_journal_candidate_query_args', $args, vava_normalize_language( $lang ), $page_id ) );
	return array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
}

function vava_journal_ordered_post_ids( int $page_id, string $lang = 'ar' ): array {
	$shared = vava_journal_shared_data( $page_id );
	$ids    = vava_journal_candidate_post_ids( $page_id, $lang );
	if ( count( $ids ) < 2 ) {
		return $ids;
	}

	if ( 'random' === $shared['display_mode'] ) {
		usort(
			$ids,
			static function ( int $left, int $right ) use ( $page_id ): int {
				return strcmp( hash( 'sha256', $page_id . ':journal:' . $left ), hash( 'sha256', $page_id . ':journal:' . $right ) );
			}
		);
		return $ids;
	}

	$order = (array) $shared['category_order'];
	if ( empty( $order ) ) {
		return $ids;
	}
	$ranks = array_flip( array_values( $order ) );
	usort(
		$ids,
		static function ( int $left, int $right ) use ( $ranks ): int {
			$left_terms  = wp_get_post_categories( $left );
			$right_terms = wp_get_post_categories( $right );
			$left_rank   = PHP_INT_MAX;
			$right_rank  = PHP_INT_MAX;
			foreach ( $left_terms as $term_id ) {
				if ( isset( $ranks[ $term_id ] ) ) { $left_rank = min( $left_rank, (int) $ranks[ $term_id ] ); }
			}
			foreach ( $right_terms as $term_id ) {
				if ( isset( $ranks[ $term_id ] ) ) { $right_rank = min( $right_rank, (int) $ranks[ $term_id ] ); }
			}
			if ( $left_rank !== $right_rank ) { return $left_rank <=> $right_rank; }
			$left_date  = (string) get_post_field( 'post_date', $left );
			$right_date = (string) get_post_field( 'post_date', $right );
			if ( $left_date !== $right_date ) { return strcmp( $right_date, $left_date ); }
			return $right <=> $left;
		}
	);
	return $ids;
}

function vava_journal_query_args( int $page_id, int $paged = 1, string $lang = 'ar' ): array {
	$shared      = vava_journal_shared_data( $page_id );
	$ordered_ids = vava_journal_ordered_post_ids( $page_id, $lang );
	$args        = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => (int) $shared['posts_per_page'],
		'paged'               => max( 1, $paged ),
		'post__in'            => $ordered_ids ?: array( 0 ),
		'orderby'             => 'post__in',
		'ignore_sticky_posts' => true,
		'suppress_filters'    => false,
	);
	return (array) apply_filters( 'vava_journal_query_args', $args, vava_normalize_language( $lang ), $page_id );
}

function vava_journal_excerpt( WP_Post $article ): string {
	$excerpt = trim( (string) get_the_excerpt( $article ) );
	if ( '' === $excerpt ) {
		$excerpt = wp_trim_words( wp_strip_all_tags( strip_shortcodes( (string) $article->post_content ) ), 22, '…' );
	}
	return $excerpt;
}

function vava_journal_article_data( WP_Post $article, string $lang, int $index = 0 ): array {
	$categories = get_the_category( $article->ID );
	$image       = get_the_post_thumbnail_url( $article, 'large' );
	return array(
		'id'          => (int) $article->ID,
		'title'       => get_the_title( $article ),
		'excerpt'     => vava_journal_excerpt( $article ),
		'category'    => $categories ? (string) $categories[0]->name : '',
		'category_ids'=> array_values( array_map( static fn( $term ) => (int) $term->term_id, $categories ) ),
		'url'         => vava_language_url( $lang, (string) get_permalink( $article ) ),
		'image'       => $image ? (string) $image : vava_journal_fallback_article_image( (int) $article->ID, $index ),
		'date'        => get_the_date( 'd.m.Y', $article ),
	);
}

function vava_journal_render_article_card( WP_Post $article, string $lang, int $index = 0, string $read_more = '', bool $is_featured = false ): void {
	$item           = vava_journal_article_data( $article, $lang, $index );
	$read_more      = $read_more ?: ( 'en' === $lang ? 'Read article' : 'قراءة المقال' );
	$featured_label = 'en' === $lang ? 'Featured article' : 'مقال مميز';
	$card_classes   = 'journal-card vava-journal-card' . ( $is_featured ? ' is-featured' : '' );
	$link_classes   = 'journal-card-link vava-journal-card-link' . ( $is_featured ? ' is-featured' : '' );
	?>
	<a class="<?php echo esc_attr( $link_classes ); ?>" href="<?php echo esc_url( $item['url'] ); ?>" data-journal-article data-journal-post-id="<?php echo esc_attr( (string) $item['id'] ); ?>">
		<article class="<?php echo esc_attr( $card_classes ); ?>">
			<div class="thumb vava-journal-card-thumb" style="background-image:url('<?php echo esc_url( $item['image'] ); ?>')">
				<?php if ( $is_featured ) : ?><span class="vava-journal-featured-badge"><?php echo esc_html( $featured_label ); ?></span><?php endif; ?>
			</div>
			<div class="vava-journal-card-content">
				<h3><?php echo esc_html( $item['title'] ); ?></h3>
				<?php if ( $item['excerpt'] ) : ?><p class="small"><?php echo esc_html( $item['excerpt'] ); ?></p><?php endif; ?>
				<div class="vava-journal-card-footer">
					<?php if ( $item['category'] ) : ?><span class="vava-journal-card-category"><?php echo esc_html( $item['category'] ); ?></span><?php else : ?><span aria-hidden="true"></span><?php endif; ?>
					<span class="vava-journal-read-more"><span class="vava-journal-read-more-label"><?php echo esc_html( $read_more ); ?></span><span aria-hidden="true">←</span></span>
				</div>
			</div>
		</article>
	</a>
	<?php
}

function vava_journal_pagination_numbers( int $current, int $total ): array {
	if ( $total <= 7 ) {
		return range( 1, $total );
	}
	$numbers = array( 1 );
	$start   = max( 2, $current - 1 );
	$end     = min( $total - 1, $current + 1 );
	if ( $start > 2 ) {
		$numbers[] = 0;
	}
	for ( $i = $start; $i <= $end; $i++ ) {
		$numbers[] = $i;
	}
	if ( $end < $total - 1 ) {
		$numbers[] = 0;
	}
	$numbers[] = $total;
	return $numbers;
}

function vava_journal_render_pagination( int $current, int $total, string $lang ): void {
	if ( $total <= 1 ) {
		return;
	}
	$is_en        = 'en' === $lang;
	$previous     = $is_en ? 'Previous' : 'السابق';
	$next         = $is_en ? 'Next' : 'التالي';
	?>
	<nav class="vava-journal-pagination" aria-label="<?php echo esc_attr( $is_en ? 'Article pages' : 'صفحات المقالات' ); ?>" data-journal-pagination>
		<div class="vava-journal-pagination-controls">
			<button class="vava-journal-page-arrow vava-journal-page-previous" data-journal-page="<?php echo esc_attr( (string) max( 1, $current - 1 ) ); ?>" type="button"<?php disabled( 1 === $current ); ?> aria-label="<?php echo esc_attr( $is_en ? 'Previous page' : 'الصفحة السابقة' ); ?>"><span aria-hidden="true">‹</span><b><?php echo esc_html( $previous ); ?></b></button>
			<div class="vava-journal-page-numbers">
			<?php foreach ( vava_journal_pagination_numbers( $current, $total ) as $number ) : ?>
				<?php if ( 0 === $number ) : ?><span class="vava-journal-page-ellipsis" aria-hidden="true">…</span><?php else : ?>
				<button class="vava-journal-page-number<?php echo $number === $current ? ' is-current' : ''; ?>" data-journal-page="<?php echo esc_attr( (string) $number ); ?>" type="button"<?php echo $number === $current ? ' aria-current="page" disabled' : ''; ?>><?php echo esc_html( (string) $number ); ?></button>
				<?php endif; ?>
			<?php endforeach; ?>
			</div>
			<button class="vava-journal-page-arrow vava-journal-page-next" data-journal-page="<?php echo esc_attr( (string) min( $total, $current + 1 ) ); ?>" type="button"<?php disabled( $current === $total ); ?> aria-label="<?php echo esc_attr( $is_en ? 'Next page' : 'الصفحة التالية' ); ?>"><b><?php echo esc_html( $next ); ?></b><span aria-hidden="true">›</span></button>
		</div>
	</nav>
	<?php
}

/* VAVA_JOURNAL_ARTICLE_VISIBILITY_V1 */
function vava_journal_render_articles_results( int $page_id, int $paged, string $lang ): array {
	$text     = vava_journal_text_data( $page_id, $lang );
	$shared  = vava_journal_shared_data( $page_id );
	if ( empty( $shared['show_articles'] ) ) {
		return array(
			'html'        => '<div class="vava-journal-empty is-display-disabled"><p>' . esc_html( (string) ( $text['articles']['empty'] ?? '' ) ) . '</p></div>',
			'currentPage' => 1,
			'totalPages'  => 1,
		);
	}
	$featured = vava_journal_featured_post( $page_id, $lang );
	$query    = new WP_Query( vava_journal_query_args( $page_id, $paged, $lang ) );
	ob_start();
	?>
	<?php if ( $featured || $query->have_posts() ) : ?>
	<div class="vava-journal-editorial-layout" data-journal-editorial-layout>
		<?php if ( $featured ) : ?>
		<div class="vava-journal-featured-column" data-journal-featured>
			<?php vava_journal_render_article_card( $featured, $lang, 0, (string) ( $text['articles']['read_more'] ?? '' ), true ); ?>
		</div>
		<?php endif; ?>
		<div class="journal-grid vava-journal-grid<?php echo $featured ? '' : ' is-full-width'; ?>" data-journal-grid>
		<?php if ( $query->have_posts() ) : ?>
			<?php $index = 0; while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php vava_journal_render_article_card( get_post(), $lang, $index + 1, (string) ( $text['articles']['read_more'] ?? '' ), false ); $index++; ?>
			<?php endwhile; wp_reset_postdata(); ?>
		<?php elseif ( ! $featured ) : ?>
			<div class="vava-journal-empty"><p><?php echo esc_html( (string) ( $text['articles']['empty'] ?? '' ) ); ?></p></div>
		<?php endif; ?>
		</div>
	</div>
	<?php else : ?>
	<div class="vava-journal-empty"><p><?php echo esc_html( (string) ( $text['articles']['empty'] ?? '' ) ); ?></p></div>
	<?php endif; ?>
	<?php vava_journal_render_pagination( max( 1, $paged ), max( 1, (int) $query->max_num_pages ), $lang ); ?>
	<?php
	wp_reset_postdata();
	return array(
		'html'        => (string) ob_get_clean(),
		'currentPage' => max( 1, $paged ),
		'totalPages'  => max( 1, (int) $query->max_num_pages ),
	);
}

function vava_journal_ajax_load_articles(): void {
	check_ajax_referer( 'vava_journal_frontend', 'nonce' );
	$page_id = isset( $_POST['pageId'] ) ? absint( $_POST['pageId'] ) : 0;
	$paged   = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
	$lang    = isset( $_POST['lang'] ) ? vava_normalize_language( sanitize_key( wp_unslash( $_POST['lang'] ) ) ) : vava_current_language();
	if ( ! $page_id || ! vava_journal_is_page( $page_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid journal page.' ), 400 );
	}
	wp_send_json_success( vava_journal_render_articles_results( $page_id, $paged, $lang ) );
}
add_action( 'wp_ajax_vava_journal_load_articles', 'vava_journal_ajax_load_articles' );
add_action( 'wp_ajax_nopriv_vava_journal_load_articles', 'vava_journal_ajax_load_articles' );

/**
 * Return every article available to the inline reader in the exact Journal order.
 * The fixed featured article comes first and is followed by the configured regular
 * article order. Duplicate IDs are removed defensively.
 */
function vava_journal_reader_post_ids( int $page_id, string $lang = 'ar' ): array {
	$featured_id = vava_journal_featured_post_id( $page_id, $lang );
	$regular_ids = vava_journal_ordered_post_ids( $page_id, $lang );
	$all_ids     = $featured_id > 0 ? array_merge( array( $featured_id ), $regular_ids ) : $regular_ids;
	return array_values( array_unique( array_filter( array_map( 'absint', $all_ids ) ) ) );
}

/**
 * Resolve the previous and next Journal articles around a current post.
 */
function vava_journal_reader_neighbors( int $page_id, int $post_id, string $lang = 'ar' ): array {
	$ids      = vava_journal_reader_post_ids( $page_id, $lang );
	$position = array_search( $post_id, $ids, true );
	if ( false === $position ) {
		return array( 'previous' => 0, 'next' => 0 );
	}
	return array(
		'previous' => $position > 0 ? absint( $ids[ $position - 1 ] ) : 0,
		'next'     => $position < count( $ids ) - 1 ? absint( $ids[ $position + 1 ] ) : 0,
	);
}

/**
 * Render a previous/next destination used by the olive reader toolbar.
 */
function vava_journal_render_reader_destination( int $post_id, string $direction, string $lang ): void {
	$is_en = 'en' === $lang;
	if ( $post_id <= 0 ) {
		?><span class="vava-journal-reader-destination is-empty" aria-hidden="true"></span><?php
		return;
	}
	$post  = get_post( $post_id );
	$title = $post instanceof WP_Post ? get_the_title( $post ) : '';
	$label = 'previous' === $direction
		? ( $is_en ? 'Previous article' : 'المقال السابق' )
		: ( $is_en ? 'Next article' : 'المقال التالي' );
	$icon  = 'previous' === $direction ? '‹' : '›';
	?>
	<button class="vava-journal-reader-destination is-<?php echo esc_attr( $direction ); ?>" type="button" data-journal-reader-nav data-journal-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
		<?php if ( 'previous' === $direction ) : ?><span class="vava-journal-reader-arrow" aria-hidden="true"><?php echo esc_html( $icon ); ?></span><?php endif; ?>
		<span class="vava-journal-reader-destination-copy"><small><?php echo esc_html( $label ); ?></small><b><?php echo esc_html( $title ); ?></b></span>
		<?php if ( 'next' === $direction ) : ?><span class="vava-journal-reader-arrow" aria-hidden="true"><?php echo esc_html( $icon ); ?></span><?php endif; ?>
	</button>
	<?php
}

/**
 * Build the approved inline article reader: editorial content/image composition
 * plus one olive navigation strip. The strip is the only close control.
 */
function vava_journal_render_reader_article( int $page_id, WP_Post $article, string $lang ): array {
	$lang       = vava_normalize_language( $lang );
	$item       = vava_journal_article_data( $article, $lang, 0 );
	$neighbors  = vava_journal_reader_neighbors( $page_id, (int) $article->ID, $lang );
	$content    = apply_filters( 'the_content', get_the_content( null, false, $article ) );
	$tags      = get_the_tags( $article->ID );
	$tag_names = array();
	foreach ( is_array( $tags ) ? $tags : array() as $tag ) {
		if ( $tag instanceof WP_Term && ! isset( $tag_names[ $tag->term_id ] ) ) {
			$tag_names[ $tag->term_id ] = $tag->name;
		}
		if ( count( $tag_names ) >= 8 ) {
			break;
		}
	}
	if ( '' === trim( wp_strip_all_tags( (string) $content ) ) ) {
		$content = '<p>' . esc_html( vava_journal_excerpt( $article ) ) . '</p>';
	}
	$close_label = 'en' === $lang ? 'Close article' : 'إغلاق المقال';
	ob_start();
	?>
	<article class="vava-journal-reader-article" data-journal-reader-article data-post-id="<?php echo esc_attr( (string) $article->ID ); ?>">
		<div class="vava-journal-reader-body">
			<div class="vava-journal-reader-copy">
				<?php if ( $item['category'] ) : ?><span class="vava-journal-reader-category"><?php echo esc_html( $item['category'] ); ?></span><?php endif; ?>
				<h2 id="vava-journal-reader-title"><?php echo esc_html( (string) $item['title'] ); ?></h2>
				<div class="vava-journal-reader-content"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php if ( $tag_names ) : ?>
				<div class="vava-journal-reader-tags-block" aria-label="<?php echo esc_attr( 'en' === $lang ? 'Article tags' : 'وسوم المقال' ); ?>">
					<strong><?php echo esc_html( 'en' === $lang ? 'Tags' : 'الوسوم' ); ?></strong>
					<div class="vava-journal-reader-terms">
						<?php foreach ( $tag_names as $tag_name ) : ?><span><?php echo esc_html( (string) $tag_name ); ?></span><?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>
			</div>
			<figure class="vava-journal-reader-media"><img src="<?php echo esc_url( (string) $item['image'] ); ?>" alt="<?php echo esc_attr( (string) $item['title'] ); ?>" loading="eager" decoding="async"></figure>
		</div>
		<nav class="vava-journal-reader-toolbar" aria-label="<?php echo esc_attr( 'en' === $lang ? 'Article navigation' : 'التنقل بين المقالات' ); ?>">
			<?php vava_journal_render_reader_destination( (int) $neighbors['previous'], 'previous', $lang ); ?>
			<button class="vava-journal-reader-close" type="button" data-journal-reader-close><span><?php echo esc_html( $close_label ); ?></span><span aria-hidden="true">×</span></button>
			<?php vava_journal_render_reader_destination( (int) $neighbors['next'], 'next', $lang ); ?>
		</nav>
	</article>
	<?php
	return array(
		'html'    => (string) ob_get_clean(),
		'postId'  => (int) $article->ID,
		'title'   => (string) $item['title'],
		'previous'=> (int) $neighbors['previous'],
		'next'    => (int) $neighbors['next'],
	);
}

/**
 * AJAX endpoint for opening an article inside the Journal page.
 */
function vava_journal_ajax_load_article(): void {
	check_ajax_referer( 'vava_journal_frontend', 'nonce' );
	$page_id = isset( $_POST['pageId'] ) ? absint( $_POST['pageId'] ) : 0;
	$post_id = isset( $_POST['postId'] ) ? absint( $_POST['postId'] ) : 0;
	$lang    = isset( $_POST['lang'] ) ? vava_normalize_language( sanitize_key( wp_unslash( $_POST['lang'] ) ) ) : vava_current_language();
	if ( ! $page_id || ! vava_journal_is_page( $page_id ) || ! $post_id ) {
		wp_send_json_error( array( 'message' => 'Invalid Journal article request.' ), 400 );
	}
	$allowed = vava_journal_reader_post_ids( $page_id, $lang );
	if ( ! in_array( $post_id, $allowed, true ) ) {
		wp_send_json_error( array( 'message' => 'Article is not available in this Journal.' ), 404 );
	}
	$article = get_post( $post_id );
	if ( ! $article instanceof WP_Post || 'post' !== $article->post_type || 'publish' !== $article->post_status ) {
		wp_send_json_error( array( 'message' => 'Article is unavailable.' ), 404 );
	}
	wp_send_json_success( vava_journal_render_reader_article( $page_id, $article, $lang ) );
}
add_action( 'wp_ajax_vava_journal_load_article', 'vava_journal_ajax_load_article' );
add_action( 'wp_ajax_nopriv_vava_journal_load_article', 'vava_journal_ajax_load_article' );

function vava_journal_add_meta_boxes( string $post_type, WP_Post $post ): void {
	if ( 'page' !== $post_type || ! vava_journal_is_page( (int) $post->ID ) ) {
		return;
	}
	remove_meta_box( 'postdivrich', 'page', 'normal' );
	remove_meta_box( 'postimagediv', 'page', 'side' );
	add_meta_box( 'vava_homepage_settings', vava_journal_admin_text( 'meta_title', 'ar' ), 'vava_journal_render_settings', 'page', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'vava_journal_add_meta_boxes', 10, 2 );

function vava_journal_render_text_field( string $name, string $value, string $label, string $preview, string $type = 'text' ): void {
	$id    = sanitize_html_class( ltrim( $name, '_' ) );
	$class = 'textarea' === $type ? ' vava-field-full' : '';
	?>
	<div class="vava-field<?php echo esc_attr( $class ); ?>">
		<label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
		<?php if ( 'textarea' === $type ) : ?>
			<textarea class="widefat" data-journal-preview="<?php echo esc_attr( $preview ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="5"><?php echo esc_textarea( $value ); ?></textarea>
		<?php else : ?>
			<input class="widefat" data-journal-preview="<?php echo esc_attr( $preview ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>"/>
		<?php endif; ?>
	</div>
	<?php
}

function vava_journal_render_media_field( string $name, int $attachment_id, string $fallback_asset, string $label_ar, string $label_en, string $preview_key ): void {
	$id          = sanitize_html_class( ltrim( $name, '_' ) );
	$fallback    = vava_asset_uri( $fallback_asset );
	$current_url = vava_journal_image_url( $attachment_id, $fallback_asset, 'medium_large' );
	?>
	<div class="vava-admin-field vava-admin-field-media vava-admin-field-wide vava-journal-media-field" data-journal-media-field data-fallback-url="<?php echo esc_url( $fallback ); ?>" data-preview-key="<?php echo esc_attr( $preview_key ); ?>">
		<label for="<?php echo esc_attr( $id ); ?>"><strong<?php echo vava_admin_i18n_attributes( $label_ar, $label_en ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $label_ar ); ?></strong></label>
		<div class="vava-media-field" data-media-type="image">
			<input class="vava-media-id" data-journal-media-id data-media-url="<?php echo esc_url( $current_url ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="hidden" value="<?php echo esc_attr( (string) $attachment_id ); ?>"/>
			<div class="vava-media-dropzone" role="button" tabindex="0"><div class="vava-media-preview"><img alt="" src="<?php echo esc_url( $current_url ); ?>"/></div></div>
			<div class="vava-media-actions"><button class="button vava-media-select" type="button"<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'choose_replace', 'ar' ), vava_journal_admin_text( 'choose_replace', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'choose_replace', 'ar' ) ); ?></button><button class="button vava-media-remove" type="button"<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'delete_file', 'ar' ), vava_journal_admin_text( 'delete_file', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'delete_file', 'ar' ) ); ?></button></div>
		</div>
	</div>
	<?php
}

function vava_journal_render_hero_fields( int $post_id, string $lang ): void {
	$data = vava_journal_text_data( $post_id, $lang );
	$hero = (array) $data['hero'];
	$pre  = '_vava_journal_' . $lang . '_hero_';
	vava_journal_render_text_field( $pre . 'eyebrow', (string) $hero['eyebrow'], vava_journal_admin_text( 'hero_eyebrow', $lang ), 'hero-eyebrow' );
	vava_journal_render_text_field( $pre . 'title', (string) $hero['title'], vava_journal_admin_text( 'hero_title', $lang ), 'hero-title' );
	$intro = trim( (string) $hero['intro'] );
	$note  = trim( (string) $hero['note'] );
	if ( '' !== $note && false === strpos( $intro, $note ) ) { $intro = trim( $intro . "\n\n" . $note ); }
	vava_journal_render_text_field( $pre . 'intro', $intro, vava_journal_admin_text( 'hero_intro', $lang ), 'hero-intro', 'textarea' );
}

function vava_journal_render_articles_fields( int $post_id, string $lang ): void {
	$data     = vava_journal_text_data( $post_id, $lang );
	$articles = (array) $data['articles'];
	$shared   = vava_journal_shared_data( $post_id );
	$pre      = '_vava_journal_' . $lang . '_articles_';
	?>
	<div class="vava-journal-copy-settings" data-journal-copy-settings>
		<section class="vava-journal-copy-card vava-journal-copy-card-primary">
			<header class="vava-journal-copy-heading"><span class="vava-journal-copy-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg></span><div><h3<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'content_settings', 'ar' ), vava_journal_admin_text( 'content_settings', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'content_settings', $lang ) ); ?></h3><p<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'content_settings_help', 'ar' ), vava_journal_admin_text( 'content_settings_help', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'content_settings_help', $lang ) ); ?></p></div></header>
			<div class="vava-journal-copy-row">
				<div class="vava-journal-copy-field"><span class="vava-journal-field-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 5h14v14H5z"/><path d="M9 9h6M9 13h4"/></svg></span><?php vava_journal_render_text_field( $pre . 'title', (string) $articles['title'], vava_journal_admin_text( 'articles_title', $lang ), 'articles-title' ); ?></div>
				<div class="vava-journal-copy-field"><span class="vava-journal-field-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.5.5l2-2a5 5 0 0 0-7-7l-1.1 1.1"/><path d="M14 11a5 5 0 0 0-7.5-.5l-2 2a5 5 0 0 0 7 7l1.1-1.1"/></svg></span><?php vava_journal_render_text_field( $pre . 'read_more', (string) $articles['read_more'], vava_journal_admin_text( 'read_more', $lang ), 'articles-read-more' ); ?></div>
			</div>
		</section>
		<section class="vava-journal-copy-card vava-journal-copy-card-supporting vava-journal-visibility-card">
			<header class="vava-journal-copy-heading"><span class="vava-journal-copy-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19.5V5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2.5"/><path d="M8 7h6M8 11h6"/></svg></span><div><h3<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'explanatory_copy', 'ar' ), vava_journal_admin_text( 'explanatory_copy', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'explanatory_copy', $lang ) ); ?></h3><p<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'explanatory_copy_help', 'ar' ), vava_journal_admin_text( 'explanatory_copy_help', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'explanatory_copy_help', $lang ) ); ?></p></div></header>
			<label class="vava-journal-show-articles-toggle">
				<input data-journal-show-articles name="_vava_journal_show_articles" type="checkbox" value="1" <?php checked( ! empty( $shared['show_articles'] ) ); ?>/>
				<span class="vava-journal-toggle-ui" aria-hidden="true"></span>
				<span><strong<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'show_articles', 'ar' ), vava_journal_admin_text( 'show_articles', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'show_articles', $lang ) ); ?></strong><small<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'show_articles_help', 'ar' ), vava_journal_admin_text( 'show_articles_help', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'show_articles_help', $lang ) ); ?></small></span>
			</label>
			<div class="vava-journal-copy-row vava-journal-copy-row-single">
				<div class="vava-journal-copy-field"><?php vava_journal_render_text_field( $pre . 'empty', (string) $articles['empty'], vava_journal_admin_text( 'empty_message', $lang ), 'articles-empty', 'textarea' ); ?></div>
			</div>
		</section>
	</div>
	<?php
}

function vava_journal_categories(): array {
	$categories = get_categories( array( 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
	return is_array( $categories ) ? $categories : array();
}

function vava_journal_render_query_fields( int $post_id ): void {
	$shared     = vava_journal_shared_data( $post_id );
	$categories = vava_journal_categories();
	$posts      = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => -1,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'suppress_filters'    => false,
		)
	);
	$category_map = array();
	foreach ( $categories as $category ) { $category_map[ (int) $category->term_id ] = $category; }
	$selected_featured_id = (int) $shared['featured_post_id'];
	$selected_featured_label = vava_journal_admin_text( 'featured_auto', 'ar' );
	if ( $selected_featured_id > 0 ) {
		$selected_featured_post = get_post( $selected_featured_id );
		if ( $selected_featured_post instanceof WP_Post ) { $selected_featured_label = get_the_title( $selected_featured_post ); }
	}
	?>
	<div class="vava-journal-query-settings is-stacked" data-journal-query-settings>
		<div class="vava-admin-field vava-journal-featured-count-card">
			<div class="vava-journal-featured-field">
				<label for="vava_journal_featured_post_id"><strong<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'featured_article', 'ar' ), vava_journal_admin_text( 'featured_article', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'featured_article', 'ar' ) ); ?></strong></label>
				<div class="vava-journal-featured-picker" data-journal-featured-picker>
					<select class="vava-journal-featured-native" data-journal-featured-post id="vava_journal_featured_post_id" name="_vava_journal_featured_post_id" tabindex="-1" aria-hidden="true"><option value="0"<?php selected( 0, $selected_featured_id ); ?>><?php echo esc_html( vava_journal_admin_text( 'featured_auto', 'ar' ) ); ?></option><?php foreach ( $posts as $article ) : if ( ! $article instanceof WP_Post ) { continue; } ?><option value="<?php echo esc_attr( (string) $article->ID ); ?>" <?php selected( $selected_featured_id, (int) $article->ID ); ?>><?php echo esc_html( get_the_title( $article ) ); ?></option><?php endforeach; ?></select>
					<button aria-expanded="false" class="vava-journal-featured-trigger" data-journal-featured-trigger title="<?php echo esc_attr( $selected_featured_label ); ?>" type="button"><span data-journal-featured-label><?php echo esc_html( $selected_featured_label ); ?></span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg></button>
					<div class="vava-journal-featured-popover" data-journal-featured-popover hidden><div class="vava-journal-featured-search-wrap"><svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><input autocomplete="off" data-journal-featured-search type="search"<?php echo vava_admin_i18n_placeholder_attributes( vava_journal_admin_text( 'featured_search', 'ar' ), vava_journal_admin_text( 'featured_search', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>/></div><div class="vava-journal-featured-options" role="listbox"><button class="vava-journal-featured-option<?php echo 0 === $selected_featured_id ? ' is-selected' : ''; ?>" data-journal-featured-option data-search="<?php echo esc_attr( vava_journal_admin_text( 'featured_auto', 'ar' ) . ' ' . vava_journal_admin_text( 'featured_auto', 'en' ) ); ?>" data-title-ar="<?php echo esc_attr( vava_journal_admin_text( 'featured_auto', 'ar' ) ); ?>" data-title-en="<?php echo esc_attr( vava_journal_admin_text( 'featured_auto', 'en' ) ); ?>" data-value="0" type="button"><span class="vava-journal-featured-option-title"><?php echo esc_html( vava_journal_admin_text( 'featured_auto', 'ar' ) ); ?></span></button><?php foreach ( $posts as $article ) : if ( ! $article instanceof WP_Post ) { continue; } $title = get_the_title( $article ); ?><button class="vava-journal-featured-option<?php echo (int) $article->ID === $selected_featured_id ? ' is-selected' : ''; ?>" data-journal-featured-option data-search="<?php echo esc_attr( $title ); ?>" data-title="<?php echo esc_attr( $title ); ?>" data-value="<?php echo esc_attr( (string) $article->ID ); ?>" type="button"><span class="vava-journal-featured-option-title"><?php echo esc_html( $title ); ?></span><small><?php echo esc_html( get_the_date( 'Y/m/d', $article ) ); ?></small></button><?php endforeach; ?></div><p class="vava-journal-featured-no-match" data-journal-featured-no-match hidden<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'featured_no_match', 'ar' ), vava_journal_admin_text( 'featured_no_match', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'featured_no_match', 'ar' ) ); ?></p></div>
				</div>
				<p class="description"<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'featured_help', 'ar' ), vava_journal_admin_text( 'featured_help', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'featured_help', 'ar' ) ); ?></p>
			</div>
			<div class="vava-journal-count-field">
				<label for="vava_journal_posts_per_page"><strong<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'posts_per_page', 'ar' ), vava_journal_admin_text( 'posts_per_page', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'posts_per_page', 'ar' ) ); ?></strong></label>
				<input data-journal-posts-per-page id="vava_journal_posts_per_page" max="24" min="1" name="_vava_journal_posts_per_page" type="number" value="<?php echo esc_attr( (string) $shared['posts_per_page'] ); ?>"/>
				<p class="description"<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'pagination_help', 'ar' ), vava_journal_admin_text( 'pagination_help', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'pagination_help', 'ar' ) ); ?></p>
			</div>
		</div>
		<div class="vava-admin-field vava-journal-categories-field">
			<div class="vava-journal-query-heading"><div><label><strong<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'categories', 'ar' ), vava_journal_admin_text( 'categories', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'categories', 'ar' ) ); ?></strong></label><p class="description"<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'categories_help', 'ar' ), vava_journal_admin_text( 'categories_help', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'categories_help', 'ar' ) ); ?></p></div></div>
			<?php if ( $categories ) : ?>
			<div class="vava-journal-category-grid">
				<?php foreach ( $categories as $category ) : ?>
				<label class="vava-journal-category-option" data-category-id="<?php echo esc_attr( (string) $category->term_id ); ?>"><input data-journal-category type="checkbox" name="_vava_journal_category_ids[]" value="<?php echo esc_attr( (string) $category->term_id ); ?>" <?php checked( in_array( (int) $category->term_id, $shared['category_ids'], true ) ); ?>/><span><strong><?php echo esc_html( $category->name ); ?></strong><small<?php echo vava_admin_i18n_attributes( 'عدد المقالات: ' . (int) $category->count, 'Posts: ' . (int) $category->count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( 'عدد المقالات: ' . (int) $category->count ); ?></small></span></label>
				<?php endforeach; ?>
			</div>
			<div class="vava-journal-display-mode">
				<h4<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'display_mode', 'ar' ), vava_journal_admin_text( 'display_mode', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'display_mode', 'ar' ) ); ?></h4>
				<div class="vava-journal-mode-options">
					<label class="vava-journal-mode-option"><input data-journal-display-mode type="radio" name="_vava_journal_display_mode" value="priority" <?php checked( 'priority', $shared['display_mode'] ); ?>/><span><strong<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'mode_priority', 'ar' ), vava_journal_admin_text( 'mode_priority', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'mode_priority', 'ar' ) ); ?></strong><small<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'mode_priority_help', 'ar' ), vava_journal_admin_text( 'mode_priority_help', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'mode_priority_help', 'ar' ) ); ?></small></span></label>
					<label class="vava-journal-mode-option"><input data-journal-display-mode type="radio" name="_vava_journal_display_mode" value="random" <?php checked( 'random', $shared['display_mode'] ); ?>/><span><strong<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'mode_random', 'ar' ), vava_journal_admin_text( 'mode_random', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'mode_random', 'ar' ) ); ?></strong><small<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'mode_random_help', 'ar' ), vava_journal_admin_text( 'mode_random_help', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'mode_random_help', 'ar' ) ); ?></small></span></label>
				</div>
			</div>
			<div class="vava-journal-category-priority" data-journal-priority-wrap>
				<h4<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'category_priority', 'ar' ), vava_journal_admin_text( 'category_priority', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'category_priority', 'ar' ) ); ?></h4>
				<input data-journal-category-order name="_vava_journal_category_order" type="hidden" value="<?php echo esc_attr( implode( ',', $shared['category_order'] ) ); ?>"/>
				<div class="vava-journal-priority-list" data-journal-priority-list>
				<?php foreach ( $shared['category_order'] as $term_id ) : if ( empty( $category_map[ $term_id ] ) ) { continue; } $category = $category_map[ $term_id ]; ?>
					<div class="vava-journal-priority-item" data-category-id="<?php echo esc_attr( (string) $term_id ); ?>"><span class="vava-journal-priority-handle" aria-hidden="true">⋮⋮</span><strong><?php echo esc_html( $category->name ); ?></strong><small><?php echo esc_html( (string) $category->count ); ?></small></div>
				<?php endforeach; ?>
				</div>
				<p class="vava-journal-priority-empty" data-journal-priority-empty<?php echo empty( $shared['category_order'] ) ? '' : ' hidden'; ?><?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'category_priority_empty', 'ar' ), vava_journal_admin_text( 'category_priority_empty', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'category_priority_empty', 'ar' ) ); ?></p>
			</div>
			<?php else : ?><p class="vava-journal-no-categories"<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'no_categories', 'ar' ), vava_journal_admin_text( 'no_categories', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'no_categories', 'ar' ) ); ?></p><?php endif; ?>
		</div>
	</div>
	<?php
}

function vava_journal_preview_posts( int $post_id, string $lang, int $limit = 8 ): array {
	$args                   = vava_journal_query_args( $post_id, 1, $lang );
	$args['posts_per_page'] = max( 1, min( 12, $limit ) );
	$args['no_found_rows']  = true;
	$posts                  = get_posts( $args );
	$items                  = array();
	foreach ( $posts as $index => $article ) {
		if ( $article instanceof WP_Post ) {
			$items[] = vava_journal_article_data( $article, $lang, $index );
		}
	}
	return $items;
}

function vava_journal_render_preview_card( array $article, string $read_more, int $index = 0, string $lang = 'ar', bool $is_featured = false ): void {
	$featured_label = 'en' === $lang ? 'Featured article' : 'مقال مميز';
	?>
	<article class="vava-journal-preview-card<?php echo $is_featured ? ' is-featured' : ''; ?>" data-preview-article>
		<div class="vava-journal-preview-thumb" style="background-image:url('<?php echo esc_url( (string) $article['image'] ); ?>')"><?php if ( $is_featured ) : ?><span class="vava-journal-preview-featured"><?php echo esc_html( $featured_label ); ?></span><?php endif; ?></div>
		<div class="vava-journal-preview-card-body"><h4><?php echo esc_html( (string) $article['title'] ); ?></h4><p><?php echo esc_html( (string) $article['excerpt'] ); ?></p><div class="vava-journal-preview-card-footer"><span class="vava-journal-preview-category"><?php echo esc_html( (string) $article['category'] ); ?></span><b data-preview-read-more><?php echo esc_html( $read_more ); ?><i aria-hidden="true">←</i></b></div></div>
	</article>
	<?php
}

function vava_journal_render_preview( WP_Post $post, string $section, string $lang ): void {
	$text   = vava_journal_text_data( (int) $post->ID, $lang );
	$shared = vava_journal_shared_data( (int) $post->ID );
	$is_en  = 'en' === $lang;
	?>
	<aside class="vava-live-preview" data-preview-language="<?php echo esc_attr( $lang ); ?>" data-preview-section="<?php echo esc_attr( $section ); ?>" data-journal-preview-panel dir="<?php echo $is_en ? 'ltr' : 'rtl'; ?>">
		<header class="vava-live-preview-header"><div><strong><?php echo esc_html( vava_journal_admin_text( 'live_preview', $lang ) ); ?></strong><span><?php echo esc_html( vava_journal_sections( $lang )[ $section ] ?? '' ); ?></span></div><span class="vava-live-preview-dot" aria-hidden="true"></span></header>
		<div class="vava-preview-viewport"><div class="vava-preview-stage"><div class="vava-preview-canvas vava-journal-preview vava-journal-preview-<?php echo esc_attr( $section ); ?>" data-preview-design-width="900">
		<?php if ( 'hero' === $section ) : $hero_image = vava_journal_image_url( (int) $shared['hero_image_id'], 'assets/images/journal-hero.webp', 'medium_large' ); ?>
			<div class="vava-journal-preview-hero-copy"><span data-preview-output="hero-eyebrow"><?php echo esc_html( (string) $text['hero']['eyebrow'] ); ?></span><h3 data-preview-output="hero-title"><?php echo esc_html( (string) $text['hero']['title'] ); ?></h3><p data-preview-output="hero-intro"><?php echo esc_html( trim( (string) $text['hero']['intro'] . ( ! empty( $text['hero']['note'] ) ? "\n\n" . (string) $text['hero']['note'] : '' ) ) ); ?></p></div><div class="vava-journal-preview-hero-image" data-preview-image="hero" style="background-image:url('<?php echo esc_url( $hero_image ); ?>')"></div>
		<?php else :
			$featured_post = vava_journal_featured_post( (int) $post->ID, $lang );
			$featured_item = $featured_post ? vava_journal_article_data( $featured_post, $lang, 0 ) : array();
			$posts         = vava_journal_preview_posts( (int) $post->ID, $lang, min( 6, (int) $shared['posts_per_page'] ) );
		?>
			<div class="vava-journal-preview-articles-head" data-preview-articles-head<?php echo empty( $shared['show_articles'] ) ? ' hidden' : ''; ?>><h3 data-preview-output="articles-title"><?php echo esc_html( (string) $text['articles']['title'] ); ?></h3></div>
			<div class="vava-journal-preview-editorial" data-preview-editorial-layout>
				<div class="vava-journal-preview-featured-column" data-preview-featured-column><?php if ( ! empty( $shared['show_articles'] ) && $featured_item ) { vava_journal_render_preview_card( $featured_item, (string) $text['articles']['read_more'], 0, $lang, true ); } ?></div>
				<div class="vava-journal-preview-grid<?php echo ! empty( $shared['show_articles'] ) && $featured_item ? '' : ' is-full-width'; ?>" data-preview-articles-grid><?php if ( empty( $shared['show_articles'] ) ) : ?><p class="vava-journal-preview-empty" data-preview-output="articles-empty"><?php echo esc_html( (string) $text['articles']['empty'] ); ?></p><?php elseif ( $posts ) : foreach ( $posts as $preview_index => $article ) { vava_journal_render_preview_card( $article, (string) $text['articles']['read_more'], (int) $preview_index + 1, $lang, false ); } elseif ( ! $featured_item ) : ?><p class="vava-journal-preview-empty" data-preview-output="articles-empty"><?php echo esc_html( (string) $text['articles']['empty'] ); ?></p><?php endif; ?></div>
			</div>
			<div class="vava-journal-preview-pagination" data-preview-pagination<?php echo empty( $shared['show_articles'] ) ? ' hidden' : ''; ?>><button type="button"><span>‹</span><b><?php echo esc_html( $is_en ? 'Previous' : 'السابق' ); ?></b></button><i class="is-current">1</i><i>2</i><i>3</i><button type="button"><b><?php echo esc_html( $is_en ? 'Next' : 'التالي' ); ?></b><span>›</span></button></div>
		<?php endif; ?>
		</div></div></div>
	</aside>
	<?php
}

function vava_journal_render_settings( WP_Post $post ): void {
	wp_nonce_field( 'vava_journal_save', 'vava_journal_nonce' );
	$sections_ar = vava_journal_sections( 'ar' );
	$sections_en = vava_journal_sections( 'en' );
	$shared      = vava_journal_shared_data( (int) $post->ID );
	?>
	<div class="vava-homepage-admin vava-journal-admin" data-active-language="ar" data-active-section="hero" data-settings-title-ar="<?php echo esc_attr( vava_journal_admin_text( 'meta_title', 'ar' ) ); ?>" data-settings-title-en="<?php echo esc_attr( vava_journal_admin_text( 'meta_title', 'en' ) ); ?>">
		<input data-vava-active-language-input name="_vava_admin_active_language" type="hidden" value="ar"/>
		<?php vava_render_bilingual_page_identity( $post, (string) get_permalink( $post ) ); ?>
		<div class="vava-admin-toolbar"><div class="vava-section-tabs" role="tablist"><?php foreach ( $sections_ar as $id => $label ) : ?><button aria-selected="<?php echo 'hero' === $id ? 'true' : 'false'; ?>" class="vava-section-tab<?php echo 'hero' === $id ? ' is-active' : ''; ?>" data-section="<?php echo esc_attr( $id ); ?>" role="tab" type="button"><span class="vava-tab-icon" aria-hidden="true"><?php echo vava_journal_section_icon( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span<?php echo vava_admin_i18n_attributes( $label, $sections_en[ $id ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $label ); ?></span></button><?php endforeach; ?></div><div class="vava-toolbar-actions"><div class="vava-language-switch" role="group" aria-label="<?php echo esc_attr( vava_journal_admin_text( 'fields_language', 'ar' ) ); ?>"><button class="is-active" data-language="ar" type="button"><span>العربية</span><small>AR</small></button><button data-language="en" type="button"><span>English</span><small>EN</small></button></div><button class="button vava-homepage-update-button" data-vava-submit type="button"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M20 12a8 8 0 1 1-2.35-5.65"/><path d="M20 4v6h-6"/></svg><span<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'update', 'ar' ), vava_journal_admin_text( 'update', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'update', 'ar' ) ); ?></span></button></div></div>
		<div class="vava-section-panels">
		<?php foreach ( $sections_ar as $section => $label ) : ?>
			<section class="vava-section-panel<?php echo 'hero' === $section ? ' is-active' : ''; ?>" data-section-panel="<?php echo esc_attr( $section ); ?>">
			<?php foreach ( array( 'ar', 'en' ) as $lang ) : ?>
				<div class="vava-language-pane<?php echo 'ar' === $lang ? ' is-active' : ''; ?>" data-language-pane="<?php echo esc_attr( $lang ); ?>" dir="<?php echo 'en' === $lang ? 'ltr' : 'rtl'; ?>"><div class="vava-editor-workspace"><?php vava_journal_render_preview( $post, $section, $lang ); ?><div class="vava-editor-controls"><div class="vava-fields-grid"><?php if ( 'hero' === $section ) { vava_journal_render_hero_fields( (int) $post->ID, $lang ); } else { vava_journal_render_articles_fields( (int) $post->ID, $lang ); } ?></div></div></div></div>
			<?php endforeach; ?>
			<?php if ( 'hero' === $section ) : ?><div class="vava-shared-fields"><h3<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'shared', 'ar' ), vava_journal_admin_text( 'shared', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'shared', 'ar' ) ); ?></h3><?php vava_journal_render_media_field( '_vava_journal_hero_image_id', (int) $shared['hero_image_id'], 'assets/images/journal-hero.webp', vava_journal_admin_text( 'hero_image', 'ar' ), vava_journal_admin_text( 'hero_image', 'en' ), 'hero' ); ?></div>
			<?php else : ?><div class="vava-shared-fields"><h3<?php echo vava_admin_i18n_attributes( vava_journal_admin_text( 'shared', 'ar' ), vava_journal_admin_text( 'shared', 'en' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( vava_journal_admin_text( 'shared', 'ar' ) ); ?></h3><?php vava_journal_render_query_fields( (int) $post->ID ); ?></div><?php endif; ?>
			</section>
		<?php endforeach; ?>
		</div>
	</div>
	<?php
}

function vava_journal_save_meta( int $post_id, WP_Post $post ): void {
	if ( ! isset( $_POST['vava_journal_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vava_journal_nonce'] ) ), 'vava_journal_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || 'page' !== $post->post_type || ! vava_journal_is_page( $post_id ) || ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	vava_save_bilingual_page_titles( $post_id );
	foreach ( array( 'ar', 'en' ) as $lang ) {
		$current = vava_journal_text_data( $post_id, $lang );
		$hero    = array();
		foreach ( array( 'eyebrow', 'title', 'intro' ) as $field ) {
			$key            = '_vava_journal_' . $lang . '_hero_' . $field;
			$hero[ $field ] = isset( $_POST[ $key ] ) ? ( in_array( $field, array( 'intro', 'note' ), true ) ? sanitize_textarea_field( (string) wp_unslash( $_POST[ $key ] ) ) : sanitize_text_field( (string) wp_unslash( $_POST[ $key ] ) ) ) : (string) ( $current['hero'][ $field ] ?? '' );
		}
		$hero['note'] = '';
		$articles = array( 'intro' => '' );
		foreach ( array( 'title', 'read_more', 'empty' ) as $field ) {
			$key                = '_vava_journal_' . $lang . '_articles_' . $field;
			$articles[ $field ] = isset( $_POST[ $key ] ) ? ( 'empty' === $field ? sanitize_textarea_field( (string) wp_unslash( $_POST[ $key ] ) ) : sanitize_text_field( (string) wp_unslash( $_POST[ $key ] ) ) ) : (string) ( $current['articles'][ $field ] ?? '' );
		}
		update_post_meta( $post_id, vava_journal_text_meta_key( $lang ), array( 'hero' => $hero, 'articles' => $articles ) );
	}

	$shared = vava_journal_shared_data( $post_id );
	$shared['show_articles'] = isset( $_POST['_vava_journal_show_articles'] ) ? 1 : 0;
	if ( isset( $_POST['_vava_journal_hero_image_id'] ) ) {
		$shared['hero_image_id'] = absint( $_POST['_vava_journal_hero_image_id'] );
	}
	$category_ids = isset( $_POST['_vava_journal_category_ids'] ) && is_array( $_POST['_vava_journal_category_ids'] ) ? array_values( array_unique( array_filter( array_map( 'absint', wp_unslash( $_POST['_vava_journal_category_ids'] ) ) ) ) ) : array();
	$category_ids = array_values( array_filter( $category_ids, static function ( $term_id ) { return term_exists( $term_id, 'category' ); } ) );
	$shared['category_ids'] = $category_ids;
	if ( isset( $_POST['_vava_journal_posts_per_page'] ) ) {
		$shared['posts_per_page'] = max( 1, min( 24, absint( $_POST['_vava_journal_posts_per_page'] ) ) );
	}
	if ( isset( $_POST['_vava_journal_featured_post_id'] ) ) {
		$featured_id = absint( $_POST['_vava_journal_featured_post_id'] );
		$featured    = $featured_id > 0 ? get_post( $featured_id ) : null;
		$shared['featured_post_id'] = $featured instanceof WP_Post && 'post' === $featured->post_type && 'publish' === $featured->post_status ? $featured_id : 0;
	}
	$shared['display_mode'] = isset( $_POST['_vava_journal_display_mode'] ) && 'random' === sanitize_key( wp_unslash( $_POST['_vava_journal_display_mode'] ) ) ? 'random' : 'priority';
	$order_raw = isset( $_POST['_vava_journal_category_order'] ) ? sanitize_text_field( wp_unslash( $_POST['_vava_journal_category_order'] ) ) : '';
	$order_ids = array_values( array_unique( array_filter( array_map( 'absint', preg_split( '/\s*,\s*/', $order_raw ) ?: array() ) ) ) );
	$shared['category_order'] = array_values( array_intersect( $order_ids, $shared['category_ids'] ) );
	foreach ( $shared['category_ids'] as $category_id ) { if ( ! in_array( $category_id, $shared['category_order'], true ) ) { $shared['category_order'][] = $category_id; } }
	update_post_meta( $post_id, '_vava_journal_shared', $shared );
}
add_action( 'save_post_page', 'vava_journal_save_meta', 30, 2 );

function vava_journal_use_block_editor( bool $use_block_editor, WP_Post $post ): bool {
	return vava_journal_is_page( (int) $post->ID ) ? false : $use_block_editor;
}
add_filter( 'use_block_editor_for_post', 'vava_journal_use_block_editor', 10, 2 );

function vava_journal_admin_body_class( string $classes ): string {
	global $post;
	$post_id = $post instanceof WP_Post ? (int) $post->ID : ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $post_id && vava_journal_is_page( $post_id ) ) {
		$classes .= ' vava-homepage-classic vava-journal-classic';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'vava_journal_admin_body_class' );

function vava_journal_admin_preview_dataset( string $lang ): array {
	$posts = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => -1,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'suppress_filters'    => false,
		)
	);
	$items = array();
	foreach ( $posts as $index => $article ) {
		if ( $article instanceof WP_Post ) {
			$items[] = vava_journal_article_data( $article, $lang, $index );
		}
	}
	return $items;
}

function vava_journal_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) {
		return;
	}
	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $post_id || ! vava_journal_is_page( $post_id ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'vava-homepage-admin', get_theme_file_uri( 'assets/css/admin-homepage.css' ), array(), vava_asset_version( 'assets/css/admin-homepage.css' ) );
	wp_enqueue_style( 'vava-journal-admin', get_theme_file_uri( 'assets/css/admin-journal.css' ), array( 'vava-homepage-admin' ), vava_asset_version( 'assets/css/admin-journal.css' ) );
	wp_enqueue_script( 'vava-journal-admin', get_theme_file_uri( 'assets/js/admin-journal.js' ), array( 'jquery', 'jquery-ui-sortable' ), vava_asset_version( 'assets/js/admin-journal.js' ), true );
	wp_localize_script(
		'vava-journal-admin',
		'vavaJournalAdmin',
		array(
			'postId'  => $post_id,
			'posts'   => array( 'ar' => vava_journal_admin_preview_dataset( 'ar' ), 'en' => vava_journal_admin_preview_dataset( 'en' ) ),
			'empty'   => array( 'ar' => vava_journal_text_data( $post_id, 'ar' )['articles']['empty'], 'en' => vava_journal_text_data( $post_id, 'en' )['articles']['empty'] ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'vava_journal_admin_assets' );

function vava_journal_assign_or_create_page(): void {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'vava_journal_page_migrated_v1' ) ) {
		return;
	}
	$page = get_page_by_path( 'journal', OBJECT, 'page' );
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
			if ( $candidate instanceof WP_Post && vava_journal_is_page( (int) $candidate->ID ) ) {
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
				'post_title'  => 'المجلة',
				'post_name'   => 'journal',
			),
			true
		);
		if ( ! is_wp_error( $page_id ) ) {
			$page = get_post( $page_id );
		}
	}
	if ( $page instanceof WP_Post ) {
		update_post_meta( $page->ID, '_wp_page_template', vava_journal_template_slug() );
		update_post_meta( $page->ID, vava_page_title_meta_key( 'ar' ), 'المجلة' );
		update_post_meta( $page->ID, vava_page_title_meta_key( 'en' ), 'Journal' );
		update_option( 'vava_journal_page_migrated_v1', (int) $page->ID, false );
	}
}
add_action( 'admin_init', 'vava_journal_assign_or_create_page', 8 );
