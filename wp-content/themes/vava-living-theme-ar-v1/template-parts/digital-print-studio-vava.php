<?php
/** Standalone VAVA print workspace. Loaded only after verified paid access. */
defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html lang="<?php echo esc_attr( $lang ); ?>" dir="<?php echo esc_attr( $is_en ? 'ltr' : 'rtl' ); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <meta name="theme-color" content="#faf7f1">
    <title><?php echo esc_html( ( $is_en ? 'Print preview — ' : 'معاينة الطباعة — ' ) . $title . ' | VAVA Living' ); ?></title>
    <link rel="stylesheet" href="<?php echo esc_url( $style_url ); ?>">
</head>
<body class="vava-print-studio">
    <header class="vava-print-topbar">
        <div class="vava-print-topbar-inner">
            <a class="vava-print-brand" href="<?php echo esc_url( $reader_url ); ?>" aria-label="<?php echo esc_attr( $is_en ? 'Back to VAVA reader' : 'العودة إلى قارئ VAVA' ); ?>">
                <span class="vava-print-brand-symbol" aria-hidden="true">V</span>
                <span class="vava-print-brand-type"><strong>VAVA</strong><small>LIVING</small></span>
            </a>
            <span class="vava-print-topbar-divider" aria-hidden="true"></span>
            <span class="vava-print-topbar-title"><?php echo esc_html( $is_en ? 'Your print workspace' : 'مساحة تجهيز الطباعة' ); ?></span>
            <a class="vava-print-topbar-back" href="<?php echo esc_url( $reader_url ); ?>">
                <span aria-hidden="true">↩</span><?php echo esc_html( $is_en ? 'Back to reading' : 'العودة للقراءة' ); ?>
            </a>
        </div>
    </header>
    <main class="vava-print-main">
        <div class="vava-print-breadcrumb"><?php echo esc_html( $is_en ? 'My library' : 'مكتبتي الرقمية' ); ?> <span aria-hidden="true">/</span> <?php echo esc_html( $is_en ? 'Print preview' : 'معاينة الطباعة' ); ?></div>
        <div class="vava-print-heading">
            <div>
                <div class="vava-print-eyebrow"><span class="vava-print-live-dot" aria-hidden="true"></span><?php echo esc_html( $is_en ? 'PURCHASE VERIFIED · READY TO PRINT' : 'الشراء معتمد · جاهز للطباعة' ); ?></div>
                <h1><?php echo esc_html( $is_en ? 'Prepare your digital product' : 'جهّزي منتجك الرقمي للطباعة' ); ?></h1>
                <p><?php echo esc_html( $is_en ? 'Preview the original pages, check the layout, then open the PDF in your device viewer to print.' : 'راجعي تنسيق الصفحات الأصلية، وبعدها افتحي ملف PDF في عارض جهازك واختاري الطباعة.' ); ?></p>
            </div>
            <div class="vava-print-verified"><span aria-hidden="true">✓</span><span><?php echo esc_html( $is_en ? 'Secure access' : 'وصول آمن' ); ?></span></div>
        </div>
        <div class="vava-print-grid">
            <section class="vava-print-preview" aria-labelledby="vava-print-preview-title">
                <div class="vava-print-preview-bar">
                    <div><span class="vava-print-file-dot" aria-hidden="true"></span><strong id="vava-print-preview-title"><?php echo esc_html( $is_en ? 'Page preview' : 'معاينة صفحات الملف' ); ?></strong></div>
                    <span class="vava-print-page-count"><?php echo esc_html( sprintf( $is_en ? '%d pages' : '%d صفحات', $page_count ) ); ?></span>
                </div>
                <div class="vava-print-pdf-surface is-lightweight">
                    <div class="vava-print-first-page">
                        <span class="vava-print-sheet-badge"><?php echo esc_html( $is_en ? 'Real page preview · 1 / ' . $page_count : 'معاينة الصفحة الأولى · 1 / ' . $page_count ); ?></span>
                        <?php if ( $preview_page_url ) : ?>
                            <img class="vava-print-first-page-image" src="<?php echo esc_url( $preview_page_url ); ?>" loading="eager" decoding="async"
                                 alt="<?php echo esc_attr( $is_en ? 'First page of your purchased digital PDF' : 'الصفحة الأولى من ملف PDF الذي اشتريته' ); ?>">
                        <?php elseif ( $cover_url ) : ?>
                            <img class="vava-print-first-page-image" src="<?php echo esc_url( $cover_url ); ?>" loading="eager" decoding="async"
                                 alt="<?php echo esc_attr( $title ); ?>">
                        <?php else : ?>
                            <div class="vava-print-first-page-fallback" aria-hidden="true">PDF</div>
                        <?php endif; ?>
                        <p><?php echo esc_html( $is_en ? 'A lightweight preview is shown here. Open the PDF to review every page and print from your device.' : 'دي معاينة خفيفة للصفحة الأولى. افتحي ملف PDF لمراجعة كل الصفحات والطباعة من جهازك.' ); ?></p>
                        <a class="vava-print-preview-open" href="<?php echo esc_url( $print_url ); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html( $is_en ? 'Open all PDF pages' : 'فتح كل صفحات PDF' ); ?> <span aria-hidden="true">↗</span>
                        </a>
                    </div>
                </div>
                <div class="vava-print-preview-foot">
                    <span><span aria-hidden="true">◉</span><?php echo esc_html( $is_en ? 'Original page proportions' : 'أبعاد الصفحات الأصلية' ); ?></span>
                    <span><?php echo esc_html( $is_en ? 'Private customer copy' : 'نسخة خاصة بالعميل' ); ?></span>
                </div>
            </section>
            <aside class="vava-print-sidebar" aria-label="<?php echo esc_attr( $is_en ? 'Product and print actions' : 'بيانات المنتج وخيارات الطباعة' ); ?>">
                <div class="vava-print-product-summary">
                    <?php if ( $cover_url ) : ?><img class="vava-print-cover" src="<?php echo esc_url( $cover_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy"><?php else : ?><span class="vava-print-cover-fallback" aria-hidden="true">PDF</span><?php endif; ?>
                    <div><span class="vava-print-summary-label"><?php echo esc_html( $is_en ? 'Your purchased product' : 'المنتج الذي اشتريته' ); ?></span>
                        <h2><?php echo esc_html( $title ); ?></h2>
                        <span class="vava-print-order"><?php echo esc_html( sprintf( $is_en ? 'Order #%d' : 'الطلب رقم #%d', $order_id ) ); ?></span>
                    </div>
                </div>
                <div class="vava-print-meta">
                    <div><span><?php echo esc_html( $is_en ? 'File' : 'الملف' ); ?></span><strong>PDF</strong></div>
                    <div><span><?php echo esc_html( $is_en ? 'Pages' : 'الصفحات' ); ?></span><strong><?php echo esc_html( (string) $page_count ); ?></strong></div>
                    <div><span><?php echo esc_html( $is_en ? 'Size' : 'الحجم' ); ?></span><strong><?php echo esc_html( $filesize ); ?></strong></div>
                </div>
                <div class="vava-print-actions">
                    <a class="vava-print-primary" href="<?php echo esc_url( $print_url ); ?>" target="_blank" rel="noopener noreferrer">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6zM18 12h.01"/></svg>
                        <span><?php echo esc_html( $is_en ? 'Open PDF to print' : 'فتح الملف للطباعة' ); ?></span><span aria-hidden="true">↗</span>
                    </a>
                    <?php if ( $download_url ) : ?>
                        <a class="vava-print-secondary" href="<?php echo esc_url( $download_url ); ?>">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m-5-5 5 5 5-5M4 16v4h16v-4"/></svg>
                            <span><?php echo esc_html( $is_en ? 'Download PDF' : 'تحميل ملف PDF' ); ?></span>
                        </a>
                    <?php endif; ?>
                    <a class="vava-print-link" href="<?php echo esc_url( $reader_url ); ?>"><?php echo esc_html( $is_en ? 'Continue reading online' : 'استكمال القراءة أونلاين' ); ?></a>
                </div>
                <div class="vava-print-guide">
                    <strong><?php echo esc_html( $is_en ? 'How to print' : 'إزاي تطبعي الملف؟' ); ?></strong>
                    <ol>
                        <li><?php echo esc_html( $is_en ? 'Select “Open PDF to print”.' : 'اختاري «فتح الملف للطباعة».' ); ?></li>
                        <li><?php echo esc_html( $is_en ? 'Use the print button in your browser or PDF app.' : 'اضغطي «طباعة» من المتصفح أو تطبيق PDF.' ); ?></li>
                        <li><?php echo esc_html( $is_en ? 'Choose your paper size, preview the pages, then confirm.' : 'حددي حجم الورق، وراجعي الصفحات، ثم أكّدي الطباعة.' ); ?></li>
                    </ol>
                </div>
                <p class="vava-print-privacy"><span aria-hidden="true">♧</span><?php echo esc_html( $is_en ? 'This PDF is accessible only through your verified, paid VAVA account. Print-only access may still allow a browser to save a copy.' : 'ملف PDF متاح فقط من حسابك المعتمد بعد الدفع. قد يسمح المتصفح بحفظ نسخة حتى لو الصلاحية للطباعة فقط.' ); ?></p>
            </aside>
        </div>
        <div class="vava-print-bottom-links">
            <a href="<?php echo esc_url( $reader_url ); ?>"><?php echo esc_html( $is_en ? 'Return to product' : 'العودة للمنتج' ); ?></a>
            <a href="<?php echo esc_url( $library_url ); ?>"><?php echo esc_html( $is_en ? 'My digital library' : 'مكتبتي الرقمية' ); ?></a>
        </div>
    </main>
    <footer class="vava-print-footer">VAVA LIVING <span aria-hidden="true">·</span> <?php echo esc_html( $is_en ? 'Your content, beautifully presented.' : 'تجربة هادئة وواضحة لكل صفحة.' ); ?></footer>
</body>
</html>
