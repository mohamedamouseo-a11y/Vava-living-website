(function ($) {
  'use strict';

  var root = $('.vava-selections-admin');
  if (!root.length) return;

  $('body').addClass('vava-homepage-classic vava-selections-classic');

  function currentLanguage() {
    return String(root.attr('data-active-language') || 'ar') === 'en' ? 'en' : 'ar';
  }

  function previewFor(section, language) {
    return $('.vava-live-preview[data-selections-preview-panel][data-preview-section="' + section + '"][data-preview-language="' + language + '"]').first();
  }

  function fitPreview(preview) {
    if (!preview || !preview.length || preview.attr('hidden')) return;
    var viewport = preview.find('.vava-preview-viewport').first();
    var stage = preview.find('.vava-preview-stage').first();
    var canvas = preview.find('.vava-preview-canvas').first();
    if (!viewport.length || !stage.length || !canvas.length) return;
    window.requestAnimationFrame(function () {
      var designWidth = parseFloat(canvas.attr('data-preview-design-width')) || 900;
      var available = Math.max(320, viewport.innerWidth() - 24);
      var scale = Math.min(1, available / designWidth);
      canvas.css({ width: designWidth + 'px', transform: 'scale(' + scale + ')' });
      window.requestAnimationFrame(function () {
        var height = Math.max(1, canvas.get(0).scrollHeight || 0);
        stage.css({ width: Math.round(designWidth * scale) + 'px', height: Math.round(height * scale) + 'px' });
      });
    });
  }

  function fitCurrent() {
    fitPreview(previewFor(String(root.attr('data-active-section') || 'hero'), currentLanguage()));
  }

  function applyInterfaceLanguage(language) {
    root.find('[data-vava-i18n-ar][data-vava-i18n-en]').each(function () {
      var node = $(this);
      var value = node.attr('data-vava-i18n-' + language);
      if (typeof value !== 'undefined') node.text(value);
    });
    root.find('[data-vava-i18n-aria-ar][data-vava-i18n-aria-en]').each(function () {
      var node = $(this);
      var value = node.attr('data-vava-i18n-aria-' + language);
      if (typeof value !== 'undefined') node.attr('aria-label', value);
    });
    var title = root.attr('data-settings-title-' + language);
    if (title) $('#vava_homepage_settings .postbox-header h2').first().text(title);
  }

  function updatePageTitleLanguage(language) {
    root.find('[data-vava-page-title-pane]').removeClass('is-active').attr('hidden', true);
    root.find('[data-vava-page-title-pane="' + language + '"]').addClass('is-active').removeAttr('hidden');
  }

  function setupPageIdentity() {
    var arabic = root.find('[data-vava-page-title-language="ar"]');
    var nativeTitle = $('#title');
    $('.wrap > h1.wp-heading-inline, .wrap > .page-title-action, #titlediv').hide();
    updatePageTitleLanguage(currentLanguage());
    if (!arabic.length || !nativeTitle.length) return;
    nativeTitle.attr('aria-hidden', 'true').val(arabic.val() || '');
    arabic.on('input change', function () {
      nativeTitle.val($(this).val() || '').trigger('input');
    });
  }

  function lockPostbox() {
    var box = $('#vava_homepage_settings');
    box.removeClass('closed').addClass('vava-homepage-postbox-is-locked');
    box.find('.handle-actions, .handlediv').remove();
    box.find('.postbox-header .hndle').removeClass('hndle ui-sortable-handle').attr('aria-disabled', 'true');
    $('#postimagediv').remove();
  }

  function setupHeaderActions() {
    var box = $('#vava_homepage_settings');
    var header = box.find('.postbox-header').first();
    var toolbar = root.find('.vava-toolbar-actions').first();
    var switcher = toolbar.find('.vava-language-switch').first();
    var submit = toolbar.find('[data-vava-submit]').first();
    if (!header.length || !switcher.length || !submit.length) return;
    var actions = header.find('.vava-postbox-header-actions');
    if (!actions.length) actions = $('<div>', { class: 'vava-postbox-header-actions' }).appendTo(header);
    switcher.addClass('is-in-postbox-header').appendTo(actions);
    submit.addClass('is-in-postbox-header').appendTo(actions);
    toolbar.remove();
  }

  function updateSidebarPreview() {
    var section = String(root.attr('data-active-section') || 'hero');
    var language = currentLanguage();
    $('.vava-live-preview[data-selections-preview-panel]').attr('hidden', true).removeClass('is-sidebar-active');
    var active = previewFor(section, language).removeAttr('hidden').addClass('is-sidebar-active');
    fitPreview(active);
  }

  function setupSidebar() {
    var side = $('#side-sortables');
    var previews = root.find('.vava-live-preview[data-selections-preview-panel]');
    if (!side.length || !previews.length) return;
    $('#submitdiv, #pageparentdiv, #postimagediv').hide();
    var dock = $('#vava_live_preview_box');
    if (!dock.length) dock = $('<div>', { id: 'vava_live_preview_box', class: 'postbox vava-live-preview-postbox' }).append($('<div>', { class: 'inside' })).prependTo(side);
    dock.find('.inside').append(previews);
    updateSidebarPreview();
    if (window.ResizeObserver) {
      var observer = new window.ResizeObserver(fitCurrent);
      observer.observe(dock.get(0));
    }
  }

  function activateSection(section) {
    if (!root.find('[data-section="' + section + '"]').length) section = 'hero';
    root.attr('data-active-section', section);
    root.find('[data-section]').removeClass('is-active').attr('aria-selected', 'false');
    root.find('[data-section="' + section + '"]').addClass('is-active').attr('aria-selected', 'true');
    root.find('[data-section-panel]').removeClass('is-active');
    root.find('[data-section-panel="' + section + '"]').addClass('is-active');
    try { localStorage.setItem('vavaSelectionsSection', section); } catch (e) {}
    updateSidebarPreview();
  }

  function activateLanguage(language) {
    language = language === 'en' ? 'en' : 'ar';
    root.attr('data-active-language', language);
    root.find('[data-vava-active-language-input]').val(language);
    $('#vava_homepage_settings .vava-language-switch button').removeClass('is-active');
    $('#vava_homepage_settings .vava-language-switch button[data-language="' + language + '"]').addClass('is-active');
    root.find('[data-language-pane]').removeClass('is-active');
    root.find('[data-language-pane="' + language + '"]').addClass('is-active');
    updatePageTitleLanguage(language);
    applyInterfaceLanguage(language);
    try { localStorage.setItem('vavaSelectionsLanguage', language); } catch (e) {}
    updateSidebarPreview();
  }

  function createMediaFrame(callback) {
    var frame = wp.media({ title: currentLanguage() === 'en' ? 'Choose image' : 'اختيار صورة', button: { text: currentLanguage() === 'en' ? 'Use image' : 'استخدام الصورة' }, multiple: false, library: { type: 'image' } });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      callback(attachment);
    });
    frame.open();
  }

  function ensureMediaProgress(field) {
    var progress = field.find('[data-vava-upload-progress]').first();
    if (!progress.length) {
      progress = $('<div>', { class: 'vava-upload-progress', 'data-vava-upload-progress': '', 'aria-live': 'polite' }).append(
        $('<div>', { class: 'vava-upload-progress-head' }).append(
          $('<strong>', { 'data-upload-progress-label': '', text: currentLanguage() === 'en' ? 'Ready' : 'جاهز' }),
          $('<span>', { 'data-upload-progress-percent': '', text: '0%' })
        ),
        $('<div>', { class: 'vava-upload-progress-track' }).append($('<i>', { 'data-upload-progress-bar': '' })),
        $('<small>', { 'data-upload-progress-meta': '' })
      );
      var actions = field.find('.vava-media-actions').first();
      if (actions.length) progress.insertBefore(actions); else field.append(progress);
    }
    return progress;
  }

  function completeMediaProgress(field, fileName) {
    var progress = ensureMediaProgress(field);
    progress.addClass('is-active is-complete').removeClass('is-error');
    progress.find('[data-upload-progress-label]').text(currentLanguage() === 'en' ? 'Image ready' : 'تم تجهيز الصورة');
    progress.find('[data-upload-progress-percent]').text('100%');
    progress.find('[data-upload-progress-bar]').css('width', '100%');
    progress.find('[data-upload-progress-meta]').text(fileName || '');
  }

  function resetMediaProgress(field) {
    var progress = field.find('[data-vava-upload-progress]').first();
    if (!progress.length) return;
    progress.removeClass('is-active is-complete is-error');
    progress.find('[data-upload-progress-percent]').text('0%');
    progress.find('[data-upload-progress-bar]').css('width', '0%');
    progress.find('[data-upload-progress-meta]').text('');
  }

  function setGlobalMedia(field, id, url, fileName) {
    var fallback = field.attr('data-fallback-url') || '';
    var effective = url || fallback;
    var key = field.attr('data-preview-key') || '';
    var fields = key ? root.find('[data-selections-media-field][data-preview-key="' + key + '"]') : field;
    fields.each(function () {
      var target = $(this);
      target.find('[data-selections-media-id]').val(id || 0).attr('data-media-url', effective);
      target.find('.vava-media-preview').html(effective ? $('<img>', { src: effective, alt: '' }) : $('<div>', { class: 'vava-media-empty' }));
      if (id || url) completeMediaProgress(target, fileName || ''); else resetMediaProgress(target);
    });
    if (key && effective) {
      $('.vava-live-preview[data-selections-preview-panel] [data-preview-image="' + key + '"]').css('background-image', 'url("' + effective.replace(/"/g, '\\"') + '")');
    }
    fitCurrent();
  }

  root.on('click keydown', '[data-selections-media-field] .vava-media-dropzone, [data-selections-media-field] .vava-media-select', function (event) {
    if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
    event.preventDefault();
    var field = $(this).closest('[data-selections-media-field]');
    createMediaFrame(function (attachment) { setGlobalMedia(field, attachment.id || 0, attachment.url || '', attachment.filename || attachment.title || ''); });
  });

  root.on('click', '[data-selections-media-field] .vava-media-remove', function (event) {
    event.preventDefault();
    setGlobalMedia($(this).closest('[data-selections-media-field]'), 0, '', '');
  });

  root.on('input change', '[data-selections-preview]', function () {
    var input = $(this);
    var section = input.closest('[data-section-panel]').attr('data-section-panel') || 'hero';
    var language = input.closest('[data-language-pane]').attr('data-language-pane') || 'ar';
    var key = input.attr('data-selections-preview');
    root.find('.vava-live-preview[data-preview-language="' + language + '"] [data-preview-output="' + key + '"]').text(input.val() || '');
    fitPreview(previewFor(section, language));
    if (key.indexOf('collection-') === 0) { fitPreview(previewFor('digital', language)); fitPreview(previewFor('tangible', language)); }
  });

  function editorFor(group, language) {
    return root.find('[data-products-editor][data-products-group="' + group + '"][data-products-language="' + language + '"]').first();
  }

  function itemFor(group, language, uid) {
    return editorFor(group, language).find('[data-selections-product-item][data-product-uid="' + uid + '"]').first();
  }

  function productUid() {
    return 'product-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);
  }

  function reindexEditor(editor) {
    var group = editor.attr('data-products-group');
    editor.find('[data-selections-product-item]').each(function (index) {
      var item = $(this);
      item.find('[name]').each(function () {
        this.name = this.name.replace(new RegExp('(\\[' + group + '\\])\\[[0-9]+\\]'), '$1[' + index + ']');
      });
    });
  }

  function setCollapsed(item, collapsed) {
    item.toggleClass('is-collapsed', collapsed);
    item.find('.vava-repeater-toggle').first().attr('aria-expanded', collapsed ? 'false' : 'true');
  }

  function htmlEscape(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
  }

  function rebuildPreview(group, language) {
    var editor = editorFor(group, language);
    var target = previewFor(group, language).find('[data-preview-products="' + group + '"]');
    if (!editor.length || !target.length) return;
    var cards = [];
    editor.find('[data-selections-product-item]').each(function () {
      var item = $(this);
      if (!item.find('[data-product-shared-field="enabled"]').is(':checked')) return;
      var image = item.find('[data-product-image-preview] img').attr('src') || '';
      var title = item.find('[data-product-local-field="title"]').val() || '';
      var description = item.find('[data-product-local-field="description"]').val() || '';
      var price = item.find('[data-product-shared-field="price"]').val() || '';
      var currency = item.find('[data-product-local-field="currency"]').val() || '';
      var button = item.find('[data-product-local-field="button_text"]').val() || '';
      var isDigital = group === 'digital';
      var priceClass = price ? '' : ' is-price-text';
      var priceHtml = price
        ? '<b data-preview-product-field="price">' + htmlEscape(price) + '</b><em data-preview-product-field="currency">' + htmlEscape(currency) + '</em>'
        : '<em data-preview-product-field="currency">' + htmlEscape(currency) + '</em>';
      cards.push('<article class="vava-selections-preview-product' + (isDigital ? ' is-digital-cover' : '') + '" data-preview-product data-product-group="' + htmlEscape(group) + '" data-product-uid="' + htmlEscape(item.attr('data-product-uid') || '') + '">' +
        (isDigital ? '' : '<div class="vava-selections-preview-product-image"' + (image ? ' style="background-image:url(\'' + htmlEscape(image) + '\')"' : '') + '></div>') +
        '<div class="vava-selections-preview-product-content">' +
          '<h4 data-preview-product-field="title">' + htmlEscape(title) + '</h4>' +
          '<p data-preview-product-field="description">' + htmlEscape(description) + '</p>' +
          '<div class="vava-selections-preview-product-meta">' +
            '<strong data-preview-product-field="button_text">' + htmlEscape(button) + '</strong>' +
            '<span class="vava-selections-preview-product-price' + priceClass + '">' + priceHtml + '</span>' +
          '</div>' +
        '</div></article>');
    });
    target.html(cards.length ? cards.join('') : '<p class="vava-selections-preview-empty">' + (language === 'en' ? 'There are no visible products in this section yet.' : 'لا توجد منتجات ظاهرة في هذا القسم حاليًا.') + '</p>');
    fitPreview(previewFor(group, language));
  }

  function rebuildBoth(group) {
    rebuildPreview(group, 'ar');
    rebuildPreview(group, 'en');
  }

  function appendProduct(group, language, uid) {
    var editor = editorFor(group, language);
    var template = editor.find('template[data-product-template]').html() || '';
    var index = editor.find('[data-selections-product-item]').length;
    var html = template.replace(/__INDEX__/g, String(index)).replace(/__UID__/g, uid);
    var item = $(html);
    item.attr('data-product-uid', uid);
    item.find('.vava-repeater-item-header').attr({ tabindex: '0', role: 'button' });
    item.find('[data-product-uid-input]').val(uid);
    editor.find('[data-products-list]').append(item);
    setCollapsed(item, false);
    reindexEditor(editor);
    return item;
  }

  root.on('click', '[data-product-add]', function () {
    var editor = $(this).closest('[data-products-editor]');
    var group = editor.attr('data-products-group');
    var uid = productUid();
    appendProduct(group, 'ar', uid);
    appendProduct(group, 'en', uid);
    rebuildBoth(group);
  });

  root.on('click', '[data-product-remove]', function () {
    var item = $(this).closest('[data-selections-product-item]');
    var group = item.attr('data-product-group');
    var uid = item.attr('data-product-uid');
    itemFor(group, 'ar', uid).remove();
    itemFor(group, 'en', uid).remove();
    reindexEditor(editorFor(group, 'ar'));
    reindexEditor(editorFor(group, 'en'));
    rebuildBoth(group);
  });

  function openExclusive(item) {
    var group = item.attr('data-product-group');
    var uid = item.attr('data-product-uid');
    root.find('[data-products-editor][data-products-group="' + group + '"] [data-selections-product-item]').each(function () {
      var candidate = $(this);
      setCollapsed(candidate, candidate.attr('data-product-uid') !== uid);
    });
  }

  root.on('click', '.vava-repeater-toggle', function (event) {
    event.preventDefault();
    event.stopPropagation();
    var item = $(this).closest('[data-selections-product-item]');
    if (item.hasClass('is-collapsed')) openExclusive(item); else setCollapsed(item, true);
  });

  root.on('click', '.vava-repeater-item-header', function (event) {
    if ($(event.target).closest('.vava-repeater-actions,button,input,label,a').length) return;
    var item = $(this).closest('[data-selections-product-item]');
    if (item.hasClass('is-collapsed')) openExclusive(item); else setCollapsed(item, true);
  });

  root.on('keydown', '.vava-repeater-item-header', function (event) {
    if (event.key !== 'Enter' && event.key !== ' ') return;
    if ($(event.target).closest('.vava-repeater-actions,button,input,label,a').length) return;
    event.preventDefault();
    var item = $(this).closest('[data-selections-product-item]');
    if (item.hasClass('is-collapsed')) openExclusive(item); else setCollapsed(item, true);
  });

  root.on('input change', '[data-product-local-field]', function () {
    var item = $(this).closest('[data-selections-product-item]');
    if ($(this).attr('data-product-local-field') === 'title') { var title = String($(this).val() || '').trim(); var header = item.find('[data-product-header-title]'); header.text(title || header.attr('data-empty-title') || (item.attr('data-product-language') === 'en' ? 'New product' : 'منتج جديد')); }
    rebuildPreview(item.attr('data-product-group'), item.attr('data-product-language'));
  });

  root.on('input change', '[data-product-shared-field]', function () {
    var field = $(this);
    var item = field.closest('[data-selections-product-item]');
    var group = item.attr('data-product-group');
    var language = item.attr('data-product-language');
    var other = language === 'en' ? 'ar' : 'en';
    var uid = item.attr('data-product-uid');
    var key = field.attr('data-product-shared-field');
    var counterpart = itemFor(group, other, uid).find('[data-product-shared-field="' + key + '"]').first();
    if (field.is(':checkbox')) counterpart.prop('checked', field.is(':checked'));
    else counterpart.val(field.val());
    rebuildBoth(group);
  });

  function setProductImage(group, uid, id, url, fileName) {
    ['ar', 'en'].forEach(function (language) {
      var item = itemFor(group, language, uid);
      var fallback = item.find('[data-product-media-field]').attr('data-fallback-url') || '';
      var effective = url || fallback;
      item.find('[data-product-image-id]').val(id || 0);
      item.find('[data-product-image-preview]').html(effective ? $('<img>', { src: effective, alt: '' }) : $('<span>').text(language === 'en' ? 'Product image' : 'صورة المنتج'));
      var mediaField = item.find('[data-product-media-field]').first();
      if (id || url) completeMediaProgress(mediaField, fileName || ''); else resetMediaProgress(mediaField);
    });
    rebuildBoth(group);
  }

  root.on('click', '[data-product-image-select]', function (event) {
    event.preventDefault();
    var item = $(this).closest('[data-selections-product-item]');
    var group = item.attr('data-product-group');
    var uid = item.attr('data-product-uid');
    createMediaFrame(function (attachment) { setProductImage(group, uid, attachment.id || 0, attachment.url || '', attachment.filename || attachment.title || ''); });
  });

  root.on('click', '[data-product-image-remove]', function (event) {
    event.preventDefault();
    var item = $(this).closest('[data-selections-product-item]');
    setProductImage(item.attr('data-product-group'), item.attr('data-product-uid'), 0, '', '');
  });

  root.on('click', '[data-section]', function () { activateSection(String($(this).data('section') || 'hero')); });
  $('#vava_homepage_settings').on('click.vavaSelectionsLanguage', '.vava-language-switch button', function () { activateLanguage(String($(this).data('language') || 'ar')); });
  $('#vava_homepage_settings').on('click', '[data-vava-submit]', function (event) {
    event.preventDefault();
    var button = $(this);
    button.addClass('is-saving').prop('disabled', true).find('span').text(currentLanguage() === 'en' ? 'Saving...' : 'جارٍ الحفظ...');
    if ($('#publish').length) $('#publish').trigger('click'); else $('#post').trigger('submit');
  });

  lockPostbox();
  setupHeaderActions();
  setupPageIdentity();
  setupSidebar();
  root.find('[data-selections-product-item]').each(function (index) { $(this).find('.vava-repeater-item-header').attr({ tabindex: '0', role: 'button' }); setCollapsed($(this), index > 0); });
  rebuildBoth('digital');
  rebuildBoth('tangible');

  var savedLanguage = 'ar';
  var savedSection = 'hero';
  try { savedLanguage = localStorage.getItem('vavaSelectionsLanguage') || 'ar'; savedSection = localStorage.getItem('vavaSelectionsSection') || 'hero'; } catch (e) {}
  activateLanguage(savedLanguage);
  activateSection(savedSection);
  $(window).on('resize.vavaSelections', fitCurrent);
})(jQuery);
