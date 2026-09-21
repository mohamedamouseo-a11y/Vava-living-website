<?php
/**
 * Template Name: الصفحة الرئيسية
 * Template Post Type: page
 *
 * Arabic homepage with page-level meta boxes.
 *
 * @package VAVA_Living
 */

defined( 'ABSPATH' ) || exit;

$page_id  = get_queried_object_id();
$lang     = (string) apply_filters( 'vava_homepage_render_language', 'ar', $page_id );
$lang     = 'en' === $lang ? 'en' : 'ar';
$defaults = vava_homepage_defaults();

$hero_eyebrow     = vava_home_field_language( $page_id, '_vava_home_hero_eyebrow', $lang );
$hero_title       = vava_home_field_language( $page_id, '_vava_home_hero_title', $lang );
$hero_description = vava_home_field_language( $page_id, '_vava_home_hero_description', $lang );
$hero_button_text = vava_home_field_language( $page_id, '_vava_home_hero_button_text', $lang );
$hero_button_url  = vava_home_button_url( $page_id, 'hero', $lang );
$hero_media_type  = (string) vava_home_field( $page_id, '_vava_home_hero_media_type', 'video' );
$hero_media_type  = in_array( $hero_media_type, array( 'image', 'video' ), true ) ? $hero_media_type : 'video';
$hero_image_id    = absint( vava_home_field( $page_id, '_vava_home_hero_image_id', 0 ) );
$hero_image_url   = vava_home_image_url( $hero_image_id, 'assets/images/home-hero-video-poster.jpg' );
$hero_poster_id   = absint( vava_home_field( $page_id, '_vava_home_hero_poster_id', 0 ) );
$hero_poster_url  = vava_home_image_url( $hero_poster_id, 'assets/images/home-hero-video-poster.jpg' );
$hero_video_id    = absint( vava_home_field( $page_id, '_vava_home_hero_video_id', 0 ) );
$hero_video_url   = $hero_video_id ? wp_get_attachment_url( $hero_video_id ) : '';
$hero_video_url   = $hero_video_url ?: vava_home_field( $page_id, '_vava_home_hero_video_url', $defaults['_vava_home_hero_video_url'] );

$paths_title       = vava_home_field_language( $page_id, '_vava_home_paths_title', $lang );
$paths_description = vava_home_field_language( $page_id, '_vava_home_paths_description', $lang );
$paths_button_text = vava_home_field_language( $page_id, '_vava_home_paths_button_text', $lang );
$paths_button_url  = vava_home_button_url( $page_id, 'paths', $lang );
$paths_image_id    = absint( vava_home_field( $page_id, '_vava_home_paths_image_id', 0 ) );
$paths_image_url   = vava_home_image_url( $paths_image_id, 'assets/images/home-paths-vava-visual.webp' ) . vava_paths_image_cache_bust( $paths_image_id );
$paths_image_alt   = $paths_image_id ? (string) get_post_meta( $paths_image_id, '_wp_attachment_image_alt', true ) : '';
$paths_image_alt   = $paths_image_alt ?: ( 'en' === $lang ? 'VAVA journal and a warm cup among flowers in soft light' : 'دفتر VAVA وكوب دافئ وسط الزهور في ضوء هادئ' );

$shop_eyebrow     = vava_home_field_language( $page_id, '_vava_home_shop_eyebrow', $lang );
$shop_title       = vava_home_field_language( $page_id, '_vava_home_shop_title', $lang );
$shop_subtitle    = vava_home_field_language( $page_id, '_vava_home_shop_subtitle', $lang );
$shop_description = function_exists( 'vava_home_shop_description' ) ? vava_home_shop_description( $page_id, $lang ) : vava_home_field_language( $page_id, '_vava_home_shop_description', $lang );
$shop_button_text = vava_home_field_language( $page_id, '_vava_home_shop_button_text', $lang );
$shop_button_url  = vava_home_button_url( $page_id, 'shop', $lang );
$shop_image_id    = absint( vava_home_field( $page_id, '_vava_home_shop_image_id', 0 ) );
$shop_image_url   = vava_home_image_url( $shop_image_id, 'assets/images/store-2.png', 'large' );

$testimonials_label = vava_home_field_language( $page_id, '_vava_home_testimonials_label', $lang );
$testimonials_title = vava_home_field_language( $page_id, '_vava_home_testimonials_title', $lang );
$testimonials_intro = vava_home_field_language( $page_id, '_vava_home_testimonials_intro', $lang );
$testimonials       = vava_home_testimonials( $page_id, $lang );

$journal_title          = vava_home_field_language( $page_id, '_vava_home_journal_title', $lang );
$journal_subtitle       = vava_home_field_language( $page_id, '_vava_home_journal_subtitle', $lang );
if ( in_array( trim( wp_strip_all_tags( (string) $journal_subtitle ) ), array( 'مساحة للتنوير', 'A space for enlightenment', 'A Space for Enlightenment' ), true ) ) { $journal_subtitle = ''; }
$journal_description    = vava_home_field_language( $page_id, '_vava_home_journal_description', $lang );
$journal_visual_caption = vava_home_field_language( $page_id, '_vava_home_journal_visual_caption', $lang );
$journal_button_text    = vava_home_field_language( $page_id, '_vava_home_journal_button_text', $lang );
$journal_button_url     = vava_home_button_url( $page_id, 'journal', $lang );
$journal_image_id       = absint( vava_home_field( $page_id, '_vava_home_journal_image_id', 0 ) );
$journal_image_url      = vava_home_image_url( $journal_image_id, 'assets/images/home-journal-editorial.webp', 'large' );
$journal_image_alt      = $journal_image_id ? (string) get_post_meta( $journal_image_id, '_wp_attachment_image_alt', true ) : '';
$journal_image_alt      = $journal_image_alt ?: ( 'en' === $lang ? 'VAVA Living journal, warm drink, and flowers in soft natural light' : 'دفتر VAVA Living ومشروب دافئ وزهور في ضوء طبيعي هادئ' );
$journal_features       = 'en' === $lang
    ? array( 'Articles', 'Resources', 'Reflections' )
    : array( 'مقالات', 'موارد', 'خواطر' );

$contact_title       = vava_home_field_language( $page_id, '_vava_home_contact_title', $lang );
$contact_description = vava_home_field_language( $page_id, '_vava_home_contact_description', $lang );
$contact_button_text = vava_home_field_language( $page_id, '_vava_home_contact_button_text', $lang );
$contact_button_url  = vava_home_button_url( $page_id, 'contact', $lang );
$contact_image_id    = absint( vava_home_field( $page_id, '_vava_home_contact_image_id', 0 ) );
$contact_image_url   = vava_home_image_url( $contact_image_id, 'assets/images/contact-section-visual.jpg', 'large' );
$contact_image_alt   = $contact_image_id ? (string) get_post_meta( $contact_image_id, '_wp_attachment_image_alt', true ) : '';
$contact_image_alt   = $contact_image_alt ?: ( 'en' === $lang ? 'VAVA contact visual' : 'صورة قسم التواصل في VAVA' );

get_header( 'home' );
?>
<section class="section hero active" id="hero">
<?php if ( 'image' === $hero_media_type ) : ?>
<img aria-hidden="true" class="hero-video" decoding="async" fetchpriority="high" src="<?php echo esc_url( $hero_image_url ); ?>"/>
<?php else : ?>
<video aria-hidden="true" autoplay="" class="hero-video" loop="" muted="" playsinline="" poster="<?php echo esc_url( $hero_poster_url ); ?>" preload="auto">
<source src="<?php echo esc_url( (string) $hero_video_url ); ?>" type="video/mp4"/>
</video>
<?php endif; ?>
<span class="blob b1"></span><span class="blob b2"></span><span class="leaf l1"></span><div class="grain"></div>
<div class="wrap">
<div aria-label="<?php echo esc_attr( 'en' === $lang ? 'VAVA flowers hero image' : 'صورة زهور VAVA في الهيرو' ); ?>" class="photo oval slide-photo hero-photo"></div>
<div class="reveal">
<div class="eyebrow"><?php echo esc_html( $hero_eyebrow ); ?></div>
<h1><?php echo esc_html( $hero_title ); ?></h1>
<div class="vava-richtext-content"><?php echo vava_richtext_output( $hero_description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
<div class="actions">
<a class="btn secondary" href="<?php echo esc_url( $hero_button_url ); ?>"><?php echo esc_html( $hero_button_text ); ?></a>
</div>
</div>
</div>
</section>
<section class="section about-section paths-showcase-section" id="paths">
<div aria-hidden="true" class="paths-botanical-shadow">
<svg viewBox="0 0 360 250"><g fill="none" stroke="currentColor" stroke-linecap="round"><path d="M20 235C70 168 110 112 150 24"/><path d="M82 151C55 130 43 102 48 75C76 83 95 103 100 133"/><path d="M118 96C96 70 94 42 105 18C132 34 145 58 139 86"/><path d="M146 53C166 34 188 27 209 31C199 54 179 68 151 68"/><path d="M64 184C44 175 25 158 16 138C42 137 63 148 76 168"/></g></svg>
</div>
<div aria-hidden="true" class="paths-wave-art">
<svg preserveAspectRatio="none" viewBox="0 0 1200 560">
<path d="M0 108C151 254 314 345 493 350C655 355 716 322 846 359C977 397 1041 484 1200 523V560H0Z" fill="rgba(225,184,162,.19)"/>
<path d="M0 224C174 347 355 416 535 412C690 408 767 384 894 421C1016 457 1096 508 1200 535V560H0Z" fill="rgba(242,222,199,.36)"/>
<path d="M0 150C154 286 317 368 494 370C658 372 730 343 858 381C989 420 1053 496 1200 532" fill="none" stroke="rgba(197,147,94,.68)" stroke-width="2"/>
</svg>
</div>
<div class="paths-showcase reveal">
<div class="paths-showcase-copy">
<div aria-hidden="true" class="paths-divider paths-divider-top">
<span></span>
<svg viewBox="0 0 42 42"><path d="M21 8c4 5 6 9 6 13a6 6 0 0 1-12 0c0-4 2-8 6-13Zm-1 17c-6 1-10 0-13-3 5-3 9-4 13-2m2 5c6 1 10 0 13-3-5-3-9-4-13-2M21 26v8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.35"/></svg>
<span></span>
</div>
<h2><?php echo esc_html( $paths_title ); ?></h2>
<div aria-hidden="true" class="paths-divider paths-divider-bottom">
<span></span>
<svg viewBox="0 0 58 26"><path d="M29 13c-6-8-12-8-18 0 6 8 12 8 18 0Zm0 0c6-8 12-8 18 0-6 8-12 8-18 0ZM5 13h6m36 0h6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25"/></svg>
<span></span>
</div>
<div class="vava-richtext-content"><?php echo vava_richtext_output( $paths_description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
<a class="btn primary paths-showcase-cta" href="<?php echo esc_url( $paths_button_url ); ?>"><span><?php echo esc_html( $paths_button_text ); ?></span><svg aria-hidden="true" viewBox="0 0 36 36"><path d="M26 8C17 9 11 14 10 24c8 1 14-5 16-16Z M10 25c4-5 8-8 14-12M10 25v5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.45"/></svg></a>
</div>
<figure class="paths-showcase-visual">
<img alt="<?php echo esc_attr( $paths_image_alt ); ?>" decoding="async" loading="lazy" src="<?php echo esc_url( $paths_image_url ); ?>"/>
</figure>
</div>
</section>
<section class="section shop-section" id="shop">
<span class="blob b2"></span><span class="leaf l1"></span><div class="grain"></div>
<div class="shop-flow reveal">
<div aria-label="VAVA shop visual" class="shop-art vava-inline-index-1" style="--shop-img:url('<?php echo esc_url( $shop_image_url ); ?>') !important;"></div>
<div class="shop-copy">
<div class="eyebrow"><?php echo esc_html( $shop_eyebrow ); ?></div>
<h2><?php echo esc_html( $shop_title ); ?></h2>
<h3><?php echo esc_html( $shop_subtitle ); ?></h3>
<div class="vava-richtext-content"><?php echo vava_richtext_output( $shop_description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
<div class="actions"><a class="btn coral" href="<?php echo esc_url( $shop_button_url ); ?>"><?php echo esc_html( $shop_button_text ); ?></a></div>
</div>
</div>
</section>
<section class="section testimonial-section" id="testimonials">
<div class="single reveal testimonial-layout">
<div class="section-label"><?php echo esc_html( $testimonials_label ); ?></div>
<h2><?php echo esc_html( $testimonials_title ); ?></h2>
<div class="testimonial-intro vava-richtext-content"><?php echo vava_richtext_output( $testimonials_intro ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
<div class="testimonial-carousel" dir="<?php echo esc_attr( 'en' === $lang ? 'ltr' : 'rtl' ); ?>">
<button aria-label="<?php echo esc_attr( 'en' === $lang ? 'Previous story' : 'التجربة السابقة' ); ?>" class="testimonial-arrow prev" type="button">‹</button>
<div class="testimonial-viewport">
<div class="testimonial-track">
<?php foreach ( $testimonials as $testimonial_index => $testimonial ) : ?>
<article class="quote testimonial-slide<?php echo 0 === $testimonial_index ? ' active' : ''; ?>" aria-hidden="<?php echo 0 === $testimonial_index ? 'false' : 'true'; ?>">
<span aria-hidden="true" class="testimonial-quote-mark">“</span>
<div class="testimonial-text"><div class="testimonial-text-inner vava-richtext-content"><?php echo vava_richtext_output( (string) ( $testimonial['text'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div>
<div class="testimonial-meta">
<span aria-hidden="true" class="testimonial-stars">★★★★★</span>
<span class="testimonial-person"><span aria-hidden="true" class="testimonial-avatar"></span><b><?php echo esc_html( (string) ( $testimonial['author'] ?? '' ) ); ?></b></span>
</div>
</article>
<?php endforeach; ?>
</div>
</div>
<button aria-label="<?php echo esc_attr( 'en' === $lang ? 'Next story' : 'التجربة التالية' ); ?>" class="testimonial-arrow next" type="button">›</button>
</div>
<div aria-label="<?php echo esc_attr( 'en' === $lang ? 'Stories indicator' : 'مؤشر التجارب' ); ?>" class="testimonial-dots"></div>
</div>
</section>
<section class="section journal-section" id="journal">
<div aria-hidden="true" class="journal-paper-grain"></div>
<div aria-hidden="true" class="journal-organic-shape journal-organic-shape-sage"></div>
<div aria-hidden="true" class="journal-organic-shape journal-organic-shape-coral"></div>
<svg aria-hidden="true" class="journal-botanical journal-botanical-left" viewBox="0 0 190 250"><path d="M18 238C63 181 71 111 114 40"/><path d="M61 180c-27-4-42-17-49-39 29-3 48 9 55 34"/><path d="M79 143c-23-12-31-30-28-53 28 8 41 25 36 50"/><path d="M96 104c-16-18-18-37-7-57 23 15 30 34 17 56"/><path d="M43 207c24 0 41 10 50 30-25 6-45-3-56-24"/></svg>
<svg aria-hidden="true" class="journal-botanical journal-botanical-right" viewBox="0 0 190 250"><path d="M168 238C123 181 115 111 72 40"/><path d="M125 180c27-4 42-17 49-39-29-3-48 9-55 34"/><path d="M107 143c23-12 31-30 28-53-28 8-41 25-36 50"/><path d="M90 104c16-18 18-37 7-57-23 15-30 34-17 56"/><path d="M143 207c-24 0-41 10-50 30 25 6 45-3 56-24"/></svg>
<div class="journal-scene reveal">
<div class="journal-copy">
<h2><?php echo esc_html( $journal_title ); ?></h2>
<h3><?php echo esc_html( $journal_subtitle ); ?></h3>
<span aria-hidden="true" class="journal-divider"><i></i></span>
<div class="journal-description vava-richtext-content"><?php echo vava_richtext_output( $journal_description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
<div class="actions journal-actions"><a class="btn primary journal-button" href="<?php echo esc_url( $journal_button_url ); ?>"><span aria-hidden="true">←</span><?php echo esc_html( $journal_button_text ); ?></a></div>
</div>
<div class="journal-visual">
<figure class="journal-arch"><img alt="<?php echo esc_attr( $journal_image_alt ); ?>" decoding="async" loading="lazy" src="<?php echo esc_url( $journal_image_url ); ?>"/></figure>
<div class="journal-glass-card">
<div class="journal-feature-row"><strong><?php echo esc_html( $journal_features[0] ); ?></strong></div>
<div class="journal-feature-row"><strong><?php echo esc_html( $journal_features[1] ); ?></strong></div>
<div class="journal-feature-row"><strong><?php echo esc_html( $journal_features[2] ); ?></strong></div>
</div>
</div>
<img alt="" aria-hidden="true" class="journal-vava-mark" src="<?php echo esc_url( vava_asset_uri( 'assets/images/vava-logo.png' ) ); ?>"/>
</div>
</section>
<section class="section contact-section" id="contact">
<span class="blob b1"></span><span class="blob b2"></span><div class="grain"></div>
<div class="single reveal contact-card">
<div class="contact-copy">
<h2><?php echo esc_html( $contact_title ); ?></h2>
<div class="vava-richtext-content"><?php echo vava_richtext_output( $contact_description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
<div class="actions vava-inline-index-5"><a class="btn coral" href="<?php echo esc_url( $contact_button_url ); ?>"><?php echo esc_html( $contact_button_text ); ?></a></div>
</div>
<div aria-label="<?php echo esc_attr( $contact_image_alt ); ?>" class="contact-photo" role="img" style="background-image:url('<?php echo esc_url( $contact_image_url ); ?>')"></div>
</div>
<?php
get_footer( 'home' );
