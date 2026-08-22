(function ($) {
  'use strict';
  var root = $('.vava-contact-admin');
  if (!root.length) return;
  $('body').addClass('vava-homepage-classic vava-contact-classic');

  function parseJSON(value, fallback) { try { var parsed = JSON.parse(value || ''); return parsed && typeof parsed === 'object' ? parsed : fallback; } catch (error) { return fallback; } }
  function currentLanguage() { return String(root.attr('data-active-language') || 'ar') === 'en' ? 'en' : 'ar'; }
  function previewFor(section, language) { return $('.vava-live-preview[data-contact-preview-panel][data-preview-section="' + section + '"][data-preview-language="' + language + '"]').first(); }
  function fitPreview(preview) {
    if (!preview || !preview.length || preview.attr('hidden')) return;
    var viewport = preview.find('.vava-preview-viewport').first(); var stage = preview.find('.vava-preview-stage').first(); var canvas = preview.find('.vava-preview-canvas').first();
    if (!viewport.length || !stage.length || !canvas.length) return;
    window.requestAnimationFrame(function () {
      var designWidth = parseFloat(canvas.attr('data-preview-design-width')) || 900; var available = Math.max(320, viewport.innerWidth() - 24); var scale = Math.min(1, available / designWidth);
      canvas.css({ width: designWidth + 'px', transform: 'scale(' + scale + ')' });
      window.requestAnimationFrame(function () { var height = Math.max(1, canvas.get(0).scrollHeight || 0); stage.css({ width: Math.round(designWidth * scale) + 'px', height: Math.round(height * scale) + 'px' }); });
    });
  }
  function fitCurrent() { fitPreview(previewFor(String(root.attr('data-active-section') || 'hero'), currentLanguage())); }
  function applyInterfaceLanguage(language) {
    root.find('[data-vava-i18n-ar][data-vava-i18n-en]').each(function () { var node = $(this); var value = node.attr('data-vava-i18n-' + language); if (typeof value !== 'undefined') node.text(value); });
    root.find('[data-vava-i18n-aria-ar][data-vava-i18n-aria-en]').each(function () { var node = $(this); var value = node.attr('data-vava-i18n-aria-' + language); if (typeof value !== 'undefined') node.attr('aria-label', value); });
    var title = root.attr('data-settings-title-' + language); if (title) $('#vava_homepage_settings .postbox-header h2').first().text(title);
    root.find('[data-vava-mail-settings]').attr('dir', language === 'en' ? 'ltr' : 'rtl');
    root.find('[data-vava-toggle]').each(function () { updateToggleState(this); });
    updateMailPreview();
  }
  function updatePageTitleLanguage(language) { root.find('[data-vava-page-title-pane]').removeClass('is-active').attr('hidden', true); root.find('[data-vava-page-title-pane="' + language + '"]').addClass('is-active').removeAttr('hidden'); }
  function setupPageIdentity() {
    var arabic = root.find('[data-vava-page-title-language="ar"]'); var nativeTitle = $('#title'); $('.wrap > h1.wp-heading-inline, .wrap > .page-title-action, #titlediv').hide(); updatePageTitleLanguage(currentLanguage());
    if (!arabic.length || !nativeTitle.length) return; nativeTitle.attr('aria-hidden', 'true').val(arabic.val() || ''); arabic.on('input change', function () { nativeTitle.val($(this).val() || '').trigger('input'); });
  }
  function lockPostbox() { var box = $('#vava_homepage_settings'); box.removeClass('closed').addClass('vava-homepage-postbox-is-locked'); box.find('.handle-actions, .handlediv').remove(); box.find('.postbox-header .hndle').removeClass('hndle ui-sortable-handle').attr('aria-disabled', 'true'); $('#postimagediv').remove(); }
  function setupHeaderActions() {
    var box = $('#vava_homepage_settings'); var header = box.find('.postbox-header').first(); var toolbar = root.find('.vava-toolbar-actions').first(); var switcher = toolbar.find('.vava-language-switch').first(); var submit = toolbar.find('[data-vava-submit]').first();
    if (!header.length || !switcher.length || !submit.length) return; var actions = header.find('.vava-postbox-header-actions'); if (!actions.length) actions = $('<div>', { class: 'vava-postbox-header-actions' }).appendTo(header); switcher.addClass('is-in-postbox-header').appendTo(actions); submit.addClass('is-in-postbox-header').appendTo(actions); toolbar.remove();
  }
  function updateSidebarPreview() { var section = String(root.attr('data-active-section') || 'hero'); var language = currentLanguage(); var dock = $('#vava_live_preview_box'); $('.vava-live-preview[data-contact-preview-panel]').attr('hidden', true).removeClass('is-sidebar-active'); dock.show(); var active = previewFor(section, language).removeAttr('hidden').addClass('is-sidebar-active'); fitPreview(active); }
  function setupSidebar() {
    var side = $('#side-sortables'); var previews = root.find('.vava-live-preview[data-contact-preview-panel]'); if (!side.length || !previews.length) return; $('#submitdiv, #pageparentdiv, #postimagediv').hide();
    var dock = $('#vava_live_preview_box'); if (!dock.length) dock = $('<div>', { id: 'vava_live_preview_box', class: 'postbox vava-live-preview-postbox' }).append($('<div>', { class: 'inside' })).prependTo(side); dock.find('.inside').append(previews); updateSidebarPreview(); if (window.ResizeObserver) { var observer = new window.ResizeObserver(fitCurrent); observer.observe(dock.get(0)); }
  }
  function activateSection(section) {
    if (!root.find('[data-section="' + section + '"]').length) section = 'hero'; root.attr('data-active-section', section).toggleClass('is-mail-section', section === 'mail'); $('#vava_homepage_settings .vava-language-switch').show(); root.find('[data-section]').removeClass('is-active').attr('aria-selected', 'false'); root.find('[data-section="' + section + '"]').addClass('is-active').attr('aria-selected', 'true'); root.find('[data-section-panel]').removeClass('is-active'); root.find('[data-section-panel="' + section + '"]').addClass('is-active'); try { localStorage.setItem('vavaContactSection', section); } catch (error) {} updateSidebarPreview();
  }
  function activateLanguage(language) {
    language = language === 'en' ? 'en' : 'ar'; root.attr('data-active-language', language); root.find('[data-vava-active-language-input]').val(language); $('#vava_homepage_settings .vava-language-switch button').removeClass('is-active'); $('#vava_homepage_settings .vava-language-switch button[data-language="' + language + '"]').addClass('is-active'); root.find('[data-language-pane]').removeClass('is-active'); root.find('[data-language-pane="' + language + '"]').addClass('is-active'); updatePageTitleLanguage(language); applyInterfaceLanguage(language); try { localStorage.setItem('vavaContactLanguage', language); } catch (error) {} updateCopyEditors(language); updateSidebarPreview();
  }
  function createMediaFrame(callback) { var frame = wp.media({ title: currentLanguage() === 'en' ? 'Choose image' : 'اختيار صورة', button: { text: currentLanguage() === 'en' ? 'Use image' : 'استخدام الصورة' }, multiple: false, library: { type: 'image' } }); frame.on('select', function () { callback(frame.state().get('selection').first().toJSON()); }); frame.open(); }
  function setMedia(field, id, url) { var input = field.find('[data-contact-media-id]'); var fallback = field.attr('data-fallback-url') || ''; var effective = url || fallback; input.val(id || 0).attr('data-media-url', effective); field.find('.vava-media-preview').html($('<img>', { src: effective, alt: '' })); $('.vava-live-preview[data-contact-preview-panel] [data-preview-image="hero"]').css('background-image', 'url("' + effective.replace(/"/g, '\\"') + '")'); fitCurrent(); }

  function compactSortableHelper(item, bodySelector, helperClass) {
    var width = Math.max(1, Math.round(item.outerWidth()));
    var helper = item.clone(false, false)
      .removeClass('is-open')
      .addClass(helperClass || 'is-compact-sort-helper')
      .css({ width: width + 'px', minWidth: width + 'px', maxWidth: width + 'px' });
    helper.find(bodySelector).attr('hidden', true).hide();
    return helper;
  }
  function prepareSortableStart(container, ui, headSelector) {
    var width = Math.max(1, Math.round(ui.item.outerWidth()));
    var height = Math.max(56, Math.round(ui.item.find(headSelector).first().outerHeight() || 0));
    container.addClass('is-sorting');
    ui.helper.css({ width: width + 'px', minWidth: width + 'px', maxWidth: width + 'px' });
    ui.placeholder.css({ width: width + 'px', minWidth: width + 'px', maxWidth: width + 'px', height: height + 'px' });
  }
  function finishSortable(container) { container.removeClass('is-sorting'); }

  var schemaInput = root.find('[data-contact-schema-json]').first();
  var initialSchemaScript = root.find('[data-contact-initial-schema]').first();
  var schema = parseJSON(schemaInput.val() || initialSchemaScript.text(), []);
  var fieldTexts = { ar: {}, en: {} };
  ['ar', 'en'].forEach(function (lang) { var input = root.find('[data-language-pane="' + lang + '"] [data-contact-field-texts-json]').first(); fieldTexts[lang] = parseJSON(input.val(), {}); });
  var guideSchemaInputs = root.find('[data-contact-guide-schema-json]');
  var guideSchema = parseJSON(guideSchemaInputs.first().val(), []);
  var guideTexts = { ar: {}, en: {} };
  ['ar', 'en'].forEach(function (lang) {
    var input = root.find('[data-language-pane="' + lang + '"] [data-contact-guide-texts-json]').first();
    var items = parseJSON(input.val(), []);
    (Array.isArray(items) ? items : []).forEach(function (card, index) {
      var id = String(card && card.id ? card.id : 'guide_' + (index + 1));
      guideTexts[lang][id] = { title: String(card && card.title || ''), body: String(card && card.body || '') };
    });
  });

  function labels(lang) { return (window.VAVA_CONTACT_ADMIN && window.VAVA_CONTACT_ADMIN.labels && window.VAVA_CONTACT_ADMIN.labels[lang]) || {}; }
  function ensureFieldText(lang, id) { if (!fieldTexts[lang][id]) fieldTexts[lang][id] = { label: lang === 'en' ? 'New field' : 'حقل جديد', placeholder: '', options: [] }; if (!Array.isArray(fieldTexts[lang][id].options)) fieldTexts[lang][id].options = []; return fieldTexts[lang][id]; }
  function guideTextArray(lang) {
    return guideSchema.map(function (card) {
      var text = ensureGuideText(lang, String(card.id));
      return { id: String(card.id), title: text.title || '', body: text.body || '' };
    });
  }
  function syncJSON() {
    schemaInput.val(JSON.stringify(schema));
    ['ar', 'en'].forEach(function (lang) {
      root.find('[data-language-pane="' + lang + '"] [data-contact-field-texts-json]').val(JSON.stringify(fieldTexts[lang]));
      root.find('[data-language-pane="' + lang + '"] [data-contact-guide-texts-json]').val(JSON.stringify(guideTextArray(lang)));
    });
    guideSchemaInputs.val(JSON.stringify(guideSchema));
  }

  // VAVA_CONTACT_GUIDE_CARD_PERSISTENCE_V2
  function captureGuideEditors() {
    ['ar', 'en'].forEach(function (lang) {
      root.find('[data-language-pane="' + lang + '"] [data-guide-id]').each(function () {
        var cardEl = $(this);
        var id = String(cardEl.attr('data-guide-id') || '');
        if (!id) return;
        var text = ensureGuideText(lang, id);
        text.title = String(cardEl.find('[data-guide-text-prop="title"]').val() || '');
        text.body = String(cardEl.find('[data-guide-text-prop="body"]').val() || '');
        var definition = guideById(id);
        if (definition) {
          definition.field_ids = cardEl.find('[data-guide-field-id]:checked').map(function () {
            return String($(this).attr('data-guide-field-id') || '');
          }).get().filter(Boolean);
        }
      });
    });
  }

  function syncBeforeSubmit() {
    captureGuideEditors();
    syncJSON();
  }
  function fieldById(id) { for (var i = 0; i < schema.length; i += 1) if (String(schema[i].id) === String(id)) return schema[i]; return null; }
  function protectedField(field) { return field && (String(field.id) === 'name' || String(field.id) === 'email' || String(field.id) === 'subject' || String(field.id) === 'message' || Number(field.protected) === 1); }

  function typeLabel(type, lang) { var l = labels(lang); if (type === 'email') return lang === 'en' ? 'Email field' : 'بريد إلكتروني'; return l[type] || type; }
  function makeSelect(options, value, attrs) { var select = $('<select>', attrs || {}); options.forEach(function (item) { $('<option>', { value: item[0], text: item[1], selected: String(item[0]) === String(value) }).appendTo(select); }); return select; }
  function wrapControl(title, control, className) { return $('<label>', { class: className || '' }).append($('<strong>', { text: title })).append(control); }

  var openFieldByLanguage = { ar: '', en: '' };
  var coreFieldOrder = ['name', 'email', 'subject'];

  function normalizeSchemaOrder() {
    var map = {};
    var extras = [];
    schema.forEach(function (field) {
      var id = String(field.id || '');
      if (!id || map[id]) return;
      map[id] = field;
      if (coreFieldOrder.indexOf(id) === -1 && id !== 'message') extras.push(field);
    });
    var defaults = {
      name: { id: 'name', type: 'text', required: 1, visible: 1, width: 'half', protected: 1 },
      email: { id: 'email', type: 'email', required: 1, visible: 1, width: 'half', protected: 1 },
      subject: { id: 'subject', type: 'text', required: 0, visible: 1, width: 'full', protected: 1 },
      message: { id: 'message', type: 'textarea', required: 1, visible: 1, width: 'full', protected: 1 }
    };
    coreFieldOrder.concat(['message']).forEach(function (id) {
      if (!map[id]) map[id] = $.extend({}, defaults[id]);
      map[id].protected = 1;
      if (id === 'name') { map[id].type = 'text'; map[id].required = 1; map[id].visible = 1; }
      if (id === 'email') { map[id].type = 'email'; map[id].required = 1; map[id].visible = 1; }
      if (id === 'subject') { map[id].type = 'text'; map[id].required = 0; map[id].visible = 1; }
      if (id === 'message') { map[id].type = 'textarea'; map[id].required = 1; map[id].visible = 1; }
    });
    schema = coreFieldOrder.map(function (id) { return map[id]; }).concat(extras).concat([map.message]);
  }

  function additionalSchemaFields() {
    return schema.filter(function (field) { return !protectedField(field); });
  }

  function deleteIcon() {
    return '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="M7 7l1 13h8l1-13"/><path d="M10 11v5M14 11v5"/></svg>';
  }

  function createFieldCard(field, lang) {
    var l = labels(lang);
    var id = String(field.id);
    var text = ensureFieldText(lang, id);
    var isProtected = protectedField(field);
    var isOpen = openFieldByLanguage[lang] === id;
    var card = $('<article>', {
      class: 'vava-contact-field-card ' + (isProtected ? 'is-protected' : 'is-additional') + (isOpen ? ' is-open' : ''),
      'data-field-id': id
    });
    var head = $('<div>', { class: 'vava-contact-field-card-head' }).appendTo(card);

    if (isProtected) {
      $('<span>', {
        class: 'vava-contact-field-lock',
        title: l.protected,
        'aria-hidden': 'true',
        html: '<svg viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="10" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>'
      }).appendTo(head);
    } else {
      $('<button>', {
        type: 'button',
        class: 'vava-contact-drag',
        title: lang === 'en' ? 'Drag to reorder' : 'اسحب لإعادة الترتيب',
        'aria-label': lang === 'en' ? 'Drag to reorder' : 'اسحب لإعادة الترتيب',
        html: '<span></span><span></span><span></span>'
      }).appendTo(head);
    }

    var toggle = $('<button>', {
      type: 'button',
      class: 'vava-contact-field-toggle',
      'aria-expanded': isOpen ? 'true' : 'false'
    }).appendTo(head);
    $('<span>', { class: 'vava-contact-field-summary' })
      .append($('<strong>', { text: text.label || id }))
      .append($('<small>', { text: typeLabel(field.type, lang) + ' · ' + (field.width === 'half' ? l.half : l.full) }))
      .appendTo(toggle);
    $('<span>', { class: 'vava-contact-field-chevron', 'aria-hidden': 'true' }).appendTo(toggle);

    if (isProtected) {
      $('<span>', { class: 'vava-contact-protected-badge', text: l.protected }).appendTo(head);
    } else {
      $('<button>', {
        type: 'button',
        class: 'vava-contact-delete-field',
        title: l.delete,
        'aria-label': l.delete,
        html: deleteIcon()
      }).appendTo(head);
    }

    var body = $('<div>', { class: 'vava-contact-field-card-body' });
    if (!isOpen) body.attr('hidden', true);
    body.appendTo(card);
    wrapControl(l.label, $('<input>', { type: 'text', class: 'widefat', value: text.label || '', 'data-field-text-prop': 'label' })).appendTo(body);
    wrapControl(l.placeholder, $('<input>', { type: 'text', class: 'widefat', value: text.placeholder || '', 'data-field-text-prop': 'placeholder' })).appendTo(body);
    var typeOptions = [['text', l.text], ['tel', l.tel], ['select', l.select], ['textarea', l.textarea]];
    if (isProtected && field.type === 'email') typeOptions.unshift(['email', typeLabel('email', lang)]);
    var typeSelect = makeSelect(typeOptions, field.type, { class: 'widefat', 'data-field-prop': 'type', disabled: isProtected });
    wrapControl(l.type, typeSelect).appendTo(body);
    var widthSelect = makeSelect([['half', l.half], ['full', l.full]], field.width, { class: 'widefat', 'data-field-prop': 'width' });
    wrapControl(l.width, widthSelect).appendTo(body);
    var toggles = $('<div>', { class: 'vava-contact-field-toggles' }).appendTo(body);
    $('<label>').append($('<input>', { type: 'checkbox', checked: Number(field.required) === 1, 'data-field-prop': 'required', disabled: isProtected })).append($('<span>', { text: l.required })).appendTo(toggles);
    $('<label>').append($('<input>', { type: 'checkbox', checked: Number(field.visible) !== 0, 'data-field-prop': 'visible', disabled: isProtected })).append($('<span>', { text: l.visible })).appendTo(toggles);
    var optionsWrap = $('<label>', { class: 'vava-contact-options' + (field.type === 'select' ? '' : ' is-hidden') })
      .append($('<strong>', { text: l.options }))
      .append($('<textarea>', { class: 'widefat', rows: 4, 'data-field-text-prop': 'options', value: (text.options || []).join('\n') }));
    optionsWrap.appendTo(body);
    return card;
  }

  function renderBuilder(lang) {
    normalizeSchemaOrder();
    var builder = root.find('[data-language-pane="' + lang + '"] [data-contact-builder]').first();
    if (!builder.length) return;
    if ($.fn.sortable && builder.data('ui-sortable')) builder.sortable('destroy');
    builder.empty();

    coreFieldOrder.forEach(function (id) {
      var field = fieldById(id);
      if (field) builder.append(createFieldCard(field, lang));
    });

    var additionalList = $('<div>', { class: 'vava-contact-additional-list', 'data-contact-additional-list': '' }).appendTo(builder);
    additionalSchemaFields().forEach(function (field) { additionalList.append(createFieldCard(field, lang)); });
    if (!additionalList.children().length) {
      $('<p>', {
        class: 'vava-contact-empty-fields',
        text: lang === 'en' ? 'No additional fields yet. New fields will appear here before the message field.' : 'لا توجد حقول إضافية بعد. ستظهر الحقول الجديدة هنا قبل حقل الرسالة.'
      }).appendTo(additionalList);
    }

    var message = fieldById('message');
    if (message) builder.append(createFieldCard(message, lang));

    if ($.fn.sortable) {
      additionalList.sortable({
        items: '> .vava-contact-field-card.is-additional',
        handle: '.vava-contact-drag',
        axis: 'y',
        tolerance: 'pointer',
        distance: 3,
        containment: 'parent',
        helper: function (event, item) { return compactSortableHelper(item, '.vava-contact-field-card-body', 'is-contact-field-sort-helper'); },
        appendTo: additionalList,
        zIndex: 100000,
        scroll: true,
        scrollSensitivity: 70,
        scrollSpeed: 14,
        cancel: 'input,textarea,select,option,.vava-contact-field-toggle,.vava-contact-delete-field',
        forcePlaceholderSize: true,
        placeholder: 'ui-sortable-placeholder',
        start: function (event, ui) { prepareSortableStart(additionalList, ui, '.vava-contact-field-card-head'); },
        beforeStop: function () { finishSortable(additionalList); },
        stop: function () { finishSortable(additionalList); },
        update: function () {
          var order = additionalList.children('[data-field-id]').map(function () { return String($(this).attr('data-field-id')); }).get();
          var map = {};
          additionalSchemaFields().forEach(function (field) { map[String(field.id)] = field; });
          var orderedExtras = order.map(function (id) { return map[id]; }).filter(Boolean);
          schema = coreFieldOrder.map(fieldById).concat(orderedExtras).concat([fieldById('message')]).filter(Boolean);
          syncJSON();
          renderBuilder(lang);
          renderBuilder(lang === 'ar' ? 'en' : 'ar');
          renderPreviewFields('ar');
          renderPreviewFields('en');
        }
      });
    }
  }


  var openGuideByLanguage = { ar: '', en: '' };
  function ensureGuideText(lang, id) {
    if (!guideTexts[lang][id]) guideTexts[lang][id] = { title: lang === 'en' ? 'New guide card' : 'بطاقة إرشادية جديدة', body: '' };
    return guideTexts[lang][id];
  }
  function normalizeGuideSchema() {
    var seen = {};
    var allowed = {};
    schema.forEach(function (field) { allowed[String(field.id)] = true; });
    guideSchema = (Array.isArray(guideSchema) ? guideSchema : []).slice(0, 12).map(function (card, index) {
      var id = String(card && card.id || 'guide_' + (index + 1)).replace(/[^a-zA-Z0-9_-]/g, '');
      if (!id || seen[id]) id = 'guide_' + Date.now().toString(36) + '_' + index;
      seen[id] = true;
      var fields = Array.isArray(card && card.field_ids) ? card.field_ids.map(String).filter(function (fieldId, pos, all) { return allowed[fieldId] && all.indexOf(fieldId) === pos; }) : [];
      ensureGuideText('ar', id); ensureGuideText('en', id);
      return { id: id, visible: card && Number(card.visible) === 0 ? 0 : 1, field_ids: fields };
    });
  }
  function guideById(id) {
    for (var i = 0; i < guideSchema.length; i += 1) if (String(guideSchema[i].id) === String(id)) return guideSchema[i];
    return null;
  }
  function fieldDisplayName(lang, id) { return ensureFieldText(lang, id).label || id; }
  function guideDeleteIcon() { return deleteIcon(); }
  function guideVisibilityIcon(visible) {
    if (visible) return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path><circle cx="12" cy="12" r="2.6"></circle></svg>';
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18"></path><path d="M10.6 6.2A9.8 9.8 0 0 1 12 6c6 0 9.5 6 9.5 6a17.5 17.5 0 0 1-3 3.8"></path><path d="M6.2 6.2C3.8 8 2.5 12 2.5 12s3.5 6 9.5 6a9.5 9.5 0 0 0 3.1-.5"></path><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"></path></svg>';
  }
  function createGuideCard(card, lang) {
    var text = ensureGuideText(lang, String(card.id));
    var isOpen = openGuideByLanguage[lang] === String(card.id);
    var linkedCount = Array.isArray(card.field_ids) ? card.field_ids.length : 0;
    var article = $('<article>', { class: 'vava-contact-guide-card' + (isOpen ? ' is-open' : '') + (Number(card.visible) === 0 ? ' is-hidden-card' : ''), 'data-guide-id': String(card.id) });
    var head = $('<div>', { class: 'vava-contact-guide-card-head' }).appendTo(article);
    $('<button>', {
      type: 'button', class: 'vava-contact-guide-drag',
      title: lang === 'en' ? 'Drag to reorder' : 'اسحب لإعادة الترتيب',
      'aria-label': lang === 'en' ? 'Drag to reorder' : 'اسحب لإعادة الترتيب',
      html: '<span></span><span></span><span></span>'
    }).appendTo(head);
    var toggle = $('<button>', { type: 'button', class: 'vava-contact-guide-toggle', 'aria-expanded': isOpen ? 'true' : 'false' }).appendTo(head);
    $('<span>', { class: 'vava-contact-guide-summary' })
      .append($('<strong>', { text: text.title || (lang === 'en' ? 'Guide card' : 'بطاقة إرشادية') }))
      .append($('<small>', { text: linkedCount ? (lang === 'en' ? linkedCount + ' linked field(s)' : linkedCount + ' حقل مرتبط') : (lang === 'en' ? 'General guidance' : 'إرشاد عام') }))
      .appendTo(toggle);
    $('<span>', { class: 'vava-contact-field-chevron', 'aria-hidden': 'true' }).appendTo(toggle);
    var actions = $('<div>', { class: 'vava-contact-guide-actions' }).appendTo(head);
    var isVisible = Number(card.visible) !== 0;
    $('<button>', {
      type: 'button',
      class: 'vava-contact-guide-visibility' + (isVisible ? ' is-visible' : ' is-hidden'),
      title: isVisible ? (lang === 'en' ? 'Hide card' : 'إخفاء البطاقة') : (lang === 'en' ? 'Show card' : 'إظهار البطاقة'),
      'aria-label': isVisible ? (lang === 'en' ? 'Hide card' : 'إخفاء البطاقة') : (lang === 'en' ? 'Show card' : 'إظهار البطاقة'),
      'aria-pressed': isVisible ? 'true' : 'false',
      html: guideVisibilityIcon(isVisible)
    }).appendTo(actions);
    $('<button>', { type: 'button', class: 'vava-contact-delete-guide', title: lang === 'en' ? 'Delete card' : 'حذف البطاقة', 'aria-label': lang === 'en' ? 'Delete card' : 'حذف البطاقة', html: guideDeleteIcon() }).appendTo(actions);
    var body = $('<div>', { class: 'vava-contact-guide-card-body' }); if (!isOpen) body.attr('hidden', true); body.appendTo(article);
    var directName = '_vava_contact_' + lang + '_guide_cards[' + String(card.id) + ']';
    wrapControl(lang === 'en' ? 'Card title' : 'عنوان البطاقة', $('<input>', { type: 'text', class: 'widefat', name: directName + '[title]', value: text.title || '', 'data-guide-text-prop': 'title' })).appendTo(body);
    wrapControl(lang === 'en' ? 'Card description' : 'وصف البطاقة', $('<textarea>', { class: 'widefat', rows: 5, name: directName + '[body]', 'data-guide-text-prop': 'body' }).val(text.body || ''), 'is-wide').appendTo(body);
    var links = $('<fieldset>', { class: 'vava-contact-guide-links' }).append($('<legend>', { text: lang === 'en' ? 'Linked fields' : 'الحقول المرتبطة' })).appendTo(body);
    $('<p>', { class: 'description', text: lang === 'en' ? 'Leave all unchecked to keep this as general guidance.' : 'اتركها بدون تحديد لتكون البطاقة إرشادًا عامًا.' }).appendTo(links);
    var choices = $('<div>', { class: 'vava-contact-guide-field-choices' }).appendTo(links);
    schema.forEach(function (field) {
      var id = String(field.id);
      var checked = Array.isArray(card.field_ids) && card.field_ids.indexOf(id) !== -1;
      $('<label>').append($('<input>', { type: 'checkbox', checked: checked, 'data-guide-field-id': id })).append($('<span>', { 'data-guide-field-label-for': id, text: fieldDisplayName(lang, id) })).appendTo(choices);
    });
    return article;
  }
  function renderGuideBuilder(lang) {
    normalizeGuideSchema();
    var builder = root.find('[data-language-pane="' + lang + '"] [data-contact-guide-builder]').first();
    if (!builder.length) return;
    if ($.fn.sortable && builder.data('ui-sortable')) builder.sortable('destroy');
    builder.empty();
    guideSchema.forEach(function (card) { builder.append(createGuideCard(card, lang)); });
    if (!guideSchema.length) $('<p>', { class: 'vava-contact-empty-fields', text: lang === 'en' ? 'No guide cards yet. Add a card when you need contextual guidance.' : 'لا توجد بطاقات إرشادية بعد. أضف بطاقة عند الحاجة.' }).appendTo(builder);
    if ($.fn.sortable && guideSchema.length) {
      builder.sortable({
        items: '> .vava-contact-guide-card', handle: '.vava-contact-guide-drag', axis: 'y', tolerance: 'pointer', distance: 3,
        containment: 'parent',
        helper: function (event, item) { return compactSortableHelper(item, '.vava-contact-guide-card-body', 'is-contact-guide-sort-helper'); },
        appendTo: builder, zIndex: 100000, scroll: true, scrollSensitivity: 70, scrollSpeed: 14,
        cancel: 'input,textarea,select,option,.vava-contact-guide-toggle,.vava-contact-delete-guide,.vava-contact-guide-visibility',
        forcePlaceholderSize: true, placeholder: 'ui-sortable-placeholder',
        start: function (event, ui) { prepareSortableStart(builder, ui, '.vava-contact-guide-card-head'); },
        beforeStop: function () { finishSortable(builder); },
        stop: function () { finishSortable(builder); },
        update: function () {
          var order = builder.children('[data-guide-id]').map(function () { return String($(this).attr('data-guide-id')); }).get();
          var map = {}; guideSchema.forEach(function (card) { map[String(card.id)] = card; });
          guideSchema = order.map(function (id) { return map[id]; }).filter(Boolean);
          syncJSON(); renderGuideBuilder('ar'); renderGuideBuilder('en'); renderPreviewGuide('ar'); renderPreviewGuide('en');
        }
      });
    }
  }
  function renderPreviewGuide(lang) {
    var list = previewFor('guide', lang).find('[data-contact-preview-guide-cards]').first();
    if (!list.length) return;
    list.empty();
    guideSchema.forEach(function (card) {
      if (Number(card.visible) === 0) return;
      var text = ensureGuideText(lang, String(card.id));
      var article = $('<article>', { 'data-preview-guide-id': String(card.id) });
      $('<strong>', { text: text.title || '' }).appendTo(article);
      $('<p>', { text: text.body || '' }).appendTo(article);
      if (Array.isArray(card.field_ids) && card.field_ids.length) {
        $('<small>', { text: card.field_ids.map(function (id) { return fieldDisplayName(lang, id); }).join(' · ') }).appendTo(article);
      }
      article.appendTo(list);
    });
    fitPreview(previewFor('guide', lang));
  }

  function renderPreviewFields(lang) {
    var grid = previewFor('form', lang).find('[data-contact-preview-fields]').first(); if (!grid.length) return; grid.empty();
    schema.forEach(function (field) { if (Number(field.visible) === 0) return; var text = ensureFieldText(lang, String(field.id)); var label = $('<label>', { class: (field.width === 'full' ? 'is-wide ' : '') + (field.type === 'textarea' ? 'is-message' : ''), 'data-preview-field-id': field.id }); $('<span>', { text: text.label || '' }).appendTo(label); $('<i>').appendTo(label); grid.append(label); }); fitPreview(previewFor('form', lang));
  }

  function copyValue(lang, key) {
    if (key.indexOf('field:') === 0) return ensureFieldText(lang, key.split(':')[1]).label || '';
    return root.find('[data-language-pane="' + lang + '"] [data-contact-copy-value="' + key + '"]').val() || '';
  }
  function setCopyValue(lang, key, value) {
    if (key.indexOf('field:') === 0) {
      var id = key.split(':')[1]; ensureFieldText(lang, id).label = value;
      root.find('[data-language-pane="' + lang + '"] [data-field-id="' + id + '"] [data-field-text-prop="label"]').val(value);
      root.find('[data-language-pane="' + lang + '"] [data-field-id="' + id + '"] .vava-contact-field-summary strong').text(value);
      root.find('[data-language-pane="' + lang + '"] [data-guide-field-label-for="' + id + '"]').text(value);
      renderPreviewFields(lang); syncJSON(); return;
    }
    root.find('[data-language-pane="' + lang + '"] [data-contact-copy-value="' + key + '"]').val(value);
    var previewMap = { title: 'form-title', submit_label: 'form-submit-label', social_eyebrow: 'form-social-eyebrow', hold_idle: 'form-hold-idle', success: 'form-success', error: 'form-error' };
    if (previewMap[key]) previewFor('form', lang).find('[data-preview-output="' + previewMap[key] + '"]').text(value);
    fitPreview(previewFor('form', lang));
  }
  function longCopyKey(key) { return ['success', 'error', 'hold_error', 'email_invalid'].indexOf(key) !== -1; }
  function updateCopyEditor(lang) {
    var editor = root.find('[data-language-pane="' + lang + '"] [data-contact-copy-editor]').first();
    if (!editor.length) return;
    var key = String(editor.find('[data-contact-copy-select]').val() || 'title');
    var isLong = longCopyKey(key);
    editor.find('[data-contact-copy-input-short]').prop('hidden', isLong).val(isLong ? '' : copyValue(lang, key));
    editor.find('[data-contact-copy-input-long]').prop('hidden', !isLong).val(isLong ? copyValue(lang, key) : '');
  }
  function updateCopyEditors(lang) { updateCopyEditor(lang); }

  root.on('click keydown', '[data-contact-media-field] .vava-media-dropzone, [data-contact-media-field] .vava-media-select', function (event) { if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return; event.preventDefault(); var field = $(this).closest('[data-contact-media-field]'); createMediaFrame(function (attachment) { setMedia(field, attachment.id || 0, attachment.url || ''); }); });
  root.on('click', '[data-contact-media-field] .vava-media-remove', function (event) { event.preventDefault(); setMedia($(this).closest('[data-contact-media-field]'), 0, ''); });
  root.on('input change', '[data-contact-preview]', function () { var input = $(this); var section = input.closest('[data-section-panel]').attr('data-section-panel') || 'hero'; var language = input.closest('[data-language-pane]').attr('data-language-pane') || 'ar'; var key = input.attr('data-contact-preview'); previewFor(section, language).find('[data-preview-output="' + key + '"]').text(input.val() || ''); fitCurrent(); });
  root.on('change', '[data-contact-copy-select]', function () { updateCopyEditor(String($(this).closest('[data-language-pane]').attr('data-language-pane') || 'ar')); });
  root.on('input', '[data-contact-copy-input-short], [data-contact-copy-input-long]', function () { var pane = $(this).closest('[data-language-pane]'); var lang = String(pane.attr('data-language-pane') || 'ar'); var key = String(pane.find('[data-contact-copy-select]').val() || 'title'); setCopyValue(lang, key, $(this).val() || ''); });

  root.on('click', '.vava-contact-field-toggle', function () {
    var button = $(this);
    var card = button.closest('[data-field-id]');
    var pane = button.closest('[data-language-pane]');
    var lang = String(pane.attr('data-language-pane') || 'ar');
    var id = String(card.attr('data-field-id') || '');
    var shouldOpen = !card.hasClass('is-open');
    pane.find('.vava-contact-field-card.is-open').removeClass('is-open').find('.vava-contact-field-toggle').attr('aria-expanded', 'false').end().find('.vava-contact-field-card-body').attr('hidden', true);
    if (shouldOpen) {
      card.addClass('is-open');
      button.attr('aria-expanded', 'true');
      card.find('.vava-contact-field-card-body').removeAttr('hidden');
      openFieldByLanguage[lang] = id;
    } else {
      openFieldByLanguage[lang] = '';
    }
  });
  root.on('click', '[data-contact-add-field]', function () {
    normalizeSchemaOrder();
    var id = 'field_' + Date.now().toString(36);
    var messageIndex = schema.findIndex(function (field) { return String(field.id) === 'message'; });
    var field = { id: id, type: 'text', required: 0, visible: 1, width: 'full', protected: 0 };
    if (messageIndex < 0) schema.push(field); else schema.splice(messageIndex, 0, field);
    fieldTexts.ar[id] = { label: 'حقل جديد', placeholder: '', options: [] };
    fieldTexts.en[id] = { label: 'New field', placeholder: '', options: [] };
    openFieldByLanguage.ar = id;
    openFieldByLanguage.en = id;
    normalizeSchemaOrder(); normalizeGuideSchema(); syncJSON(); renderBuilder('ar'); renderBuilder('en'); renderGuideBuilder('ar'); renderGuideBuilder('en'); renderPreviewFields('ar'); renderPreviewFields('en'); renderPreviewGuide('ar'); renderPreviewGuide('en');
  });
  root.on('click', '.vava-contact-delete-field', function () {
    var id = String($(this).closest('[data-field-id]').attr('data-field-id') || '');
    var field = fieldById(id);
    if (!field || protectedField(field)) return;
    var lang = String($(this).closest('[data-language-pane]').attr('data-language-pane') || 'ar');
    var promptText = lang === 'en' ? 'Delete this field?' : 'هل تريد حذف هذا الحقل؟';
    if (!window.confirm(promptText)) return;
    schema = schema.filter(function (item) { return String(item.id) !== id; });
    guideSchema.forEach(function (card) { card.field_ids = (card.field_ids || []).filter(function (fieldId) { return String(fieldId) !== id; }); });
    delete fieldTexts.ar[id]; delete fieldTexts.en[id];
    if (openFieldByLanguage.ar === id) openFieldByLanguage.ar = '';
    if (openFieldByLanguage.en === id) openFieldByLanguage.en = '';
    normalizeSchemaOrder(); syncJSON(); renderBuilder('ar'); renderBuilder('en'); renderGuideBuilder('ar'); renderGuideBuilder('en'); renderPreviewFields('ar'); renderPreviewFields('en'); renderPreviewGuide('ar'); renderPreviewGuide('en');
  });
  root.on('input change', '[data-field-text-prop]', function () {
    var input = $(this); var card = input.closest('[data-field-id]'); var id = String(card.attr('data-field-id') || ''); var lang = String(input.closest('[data-language-pane]').attr('data-language-pane') || 'ar'); var prop = String(input.attr('data-field-text-prop') || ''); var text = ensureFieldText(lang, id); if (prop === 'options') text.options = String(input.val() || '').split(/\r?\n/).map(function (item) { return item.trim(); }).filter(Boolean); else text[prop] = String(input.val() || ''); if (prop === 'label') { card.find('.vava-contact-field-summary strong').text(text.label || id); renderPreviewFields(lang); var selector = input.closest('[data-language-pane]').find('[data-contact-copy-select]'); if (String(selector.val()) === 'field:' + id) { input.closest('[data-language-pane]').find('[data-contact-copy-input-short]').val(text.label || ''); } root.find('[data-language-pane="' + lang + '"] [data-guide-field-label-for="' + id + '"]').text(text.label || id); renderPreviewGuide(lang); } syncJSON();
  });
  root.on('change', '[data-field-prop]', function () {
    var input = $(this); var id = String(input.closest('[data-field-id]').attr('data-field-id') || ''); var field = fieldById(id); if (!field) return; var prop = String(input.attr('data-field-prop') || ''); if (protectedField(field) && (prop === 'type' || prop === 'required' || prop === 'visible')) return; field[prop] = input.is(':checkbox') ? (input.is(':checked') ? 1 : 0) : String(input.val() || ''); syncJSON(); renderBuilder('ar'); renderBuilder('en'); renderPreviewFields('ar'); renderPreviewFields('en');
  });

  root.on('click', '.vava-contact-guide-toggle', function () {
    var button = $(this), card = button.closest('[data-guide-id]'), pane = button.closest('[data-language-pane]');
    var lang = String(pane.attr('data-language-pane') || 'ar'), id = String(card.attr('data-guide-id') || ''), shouldOpen = !card.hasClass('is-open');
    pane.find('.vava-contact-guide-card.is-open').removeClass('is-open').find('.vava-contact-guide-toggle').attr('aria-expanded', 'false').end().find('.vava-contact-guide-card-body').attr('hidden', true);
    if (shouldOpen) { card.addClass('is-open'); button.attr('aria-expanded', 'true'); card.find('.vava-contact-guide-card-body').removeAttr('hidden'); openGuideByLanguage[lang] = id; } else openGuideByLanguage[lang] = '';
  });
  root.on('click', '[data-contact-add-guide-card]', function () {
    var id = 'guide_' + Date.now().toString(36);
    guideSchema.push({ id: id, visible: 1, field_ids: [] });
    guideTexts.ar[id] = { title: 'بطاقة إرشادية جديدة', body: '' };
    guideTexts.en[id] = { title: 'New guide card', body: '' };
    openGuideByLanguage.ar = id; openGuideByLanguage.en = id;
    normalizeGuideSchema(); syncJSON(); renderGuideBuilder('ar'); renderGuideBuilder('en'); renderPreviewGuide('ar'); renderPreviewGuide('en');
  });
  root.on('click', '.vava-contact-guide-visibility', function () {
    var button = $(this), cardEl = button.closest('[data-guide-id]'), id = String(cardEl.attr('data-guide-id') || ''), card = guideById(id); if (!card) return;
    card.visible = Number(card.visible) === 0 ? 1 : 0;
    syncJSON();
    renderGuideBuilder('ar'); renderGuideBuilder('en');
    renderPreviewGuide('ar'); renderPreviewGuide('en');
  });
  root.on('click', '.vava-contact-delete-guide', function () {
    var cardEl = $(this).closest('[data-guide-id]'), id = String(cardEl.attr('data-guide-id') || ''), lang = String(cardEl.closest('[data-language-pane]').attr('data-language-pane') || 'ar');
    if (!window.confirm(lang === 'en' ? 'Delete this guide card?' : 'هل تريد حذف هذه البطاقة؟')) return;
    guideSchema = guideSchema.filter(function (card) { return String(card.id) !== id; }); delete guideTexts.ar[id]; delete guideTexts.en[id];
    if (openGuideByLanguage.ar === id) openGuideByLanguage.ar = ''; if (openGuideByLanguage.en === id) openGuideByLanguage.en = '';
    normalizeGuideSchema(); syncJSON(); renderGuideBuilder('ar'); renderGuideBuilder('en'); renderPreviewGuide('ar'); renderPreviewGuide('en');
  });
  root.on('input', '[data-guide-text-prop]', function () {
    var input = $(this), cardEl = input.closest('[data-guide-id]'), id = String(cardEl.attr('data-guide-id') || ''), lang = String(input.closest('[data-language-pane]').attr('data-language-pane') || 'ar'), prop = String(input.attr('data-guide-text-prop') || '');
    var text = ensureGuideText(lang, id); text[prop] = String(input.val() || '');
    if (prop === 'title') cardEl.find('.vava-contact-guide-summary strong').text(text.title || id);
    syncJSON(); renderPreviewGuide(lang);
  });
  root.on('change', '[data-guide-prop]', function () {
    var input = $(this), id = String(input.closest('[data-guide-id]').attr('data-guide-id') || ''), card = guideById(id); if (!card) return;
    card[String(input.attr('data-guide-prop') || '')] = input.is(':checked') ? 1 : 0; syncJSON(); renderPreviewGuide('ar'); renderPreviewGuide('en');
  });
  root.on('change', '[data-guide-field-id]', function () {
    var input = $(this), cardEl = input.closest('[data-guide-id]'), id = String(cardEl.attr('data-guide-id') || ''), fieldId = String(input.attr('data-guide-field-id') || ''), card = guideById(id); if (!card) return;
    var fields = Array.isArray(card.field_ids) ? card.field_ids.slice() : [];
    if (input.is(':checked') && fields.indexOf(fieldId) === -1) fields.push(fieldId); else if (!input.is(':checked')) fields = fields.filter(function (item) { return item !== fieldId; });
    card.field_ids = fields; syncJSON(); renderGuideBuilder('ar'); renderGuideBuilder('en'); renderPreviewGuide('ar'); renderPreviewGuide('en');
  });

  function updateToggleState(input) {
    var toggle = $(input);
    var enabled = toggle.is(':checked');
    var pane = toggle.closest('[data-language-pane]');
    var language = pane.length ? String(pane.attr('data-language-pane') || 'ar') : currentLanguage();
    var label = toggle.closest('.vava-contact-toggle').find('em').first();
    if (label.length) label.text(enabled ? (label.attr('data-toggle-label-on-' + language) || 'On') : (label.attr('data-toggle-label-off-' + language) || 'Off'));
    toggle.closest('[data-vava-toggle-row]').toggleClass('is-enabled', enabled).toggleClass('is-disabled', !enabled);
  }
  function syncHoldControls(source) {
    var primary = root.find('[data-vava-hold-primary]').first();
    var enabled = source ? $(source).is(':checked') : primary.is(':checked');
    root.find('[data-vava-hold-enabled-control]').prop('checked', enabled).each(function () { updateToggleState(this); });
    root.find('[data-contact-hold-duration]').toggleClass('is-hidden', !enabled);
    $('.vava-contact-preview-hold').toggle(enabled);
    fitCurrent();
  }
  function updateMailPreview() {
    var phone = String(root.find('[data-mail-preview-phone-input]').val() || '—');
    var labels = { ar: String(root.find('[data-mail-preview-whatsapp-label="ar"]').val() || ''), en: String(root.find('[data-mail-preview-whatsapp-label="en"]').val() || '') };
    ['ar', 'en'].forEach(function (language) {
      var preview = previewFor('mail', language);
      preview.find('[data-mail-preview-phone-output]').text(phone);
      preview.find('[data-mail-preview-whatsapp-output]').text(labels[language]);
      ['contact', 'bookings', 'products', 'admin'].forEach(function (channel) {
        var enabled = root.find('[name="_vava_contact_notify_' + channel + '"]').is(':checked');
        var row = preview.find('[data-mail-preview-channel="' + channel + '"]');
        var state = row.find('[data-mail-preview-channel-state]');
        state.text(enabled ? (state.attr('data-toggle-label-on-' + language) || 'On') : (state.attr('data-toggle-label-off-' + language) || 'Off'));
        row.toggleClass('is-enabled', enabled).toggleClass('is-disabled', !enabled);
      });
    });
    fitCurrent();
  }
  function syncMailLanguageField() {
    var language = currentLanguage();
    root.find('[data-mail-language-field]').each(function () {
      var field = $(this), active = String(field.attr('data-mail-language-field') || '') === language;
      field.prop('hidden', !active).toggleClass('is-active', active);
    });
  }
  root.on('change', '[data-vava-toggle]', function () {
    updateToggleState(this);
    if ($(this).is('[data-vava-hold-enabled-control]')) syncHoldControls(this);
    updateMailPreview();
  });
  root.on('input change', '[data-vava-hold-duration-control]', function () {
    var value = String($(this).val() || '4');
    root.find('[data-vava-hold-duration-control]').val(value);
  });
  root.on('input change', '[data-mail-preview-phone-input], [data-mail-preview-whatsapp-label]', updateMailPreview);
  root.on('click', '[data-section]', function () { activateSection(String($(this).attr('data-section') || 'hero')); });
  $('#vava_homepage_settings').on('click', '.vava-language-switch button', function () { activateLanguage(String($(this).attr('data-language') || 'ar')); syncMailLanguageField(); updateMailPreview(); });
  $('#vava_homepage_settings').on('click', '[data-vava-submit]', function () { syncBeforeSubmit(); $('#publish').trigger('click'); });

  normalizeSchemaOrder(); normalizeGuideSchema(); syncJSON(); renderBuilder('ar'); renderBuilder('en'); renderGuideBuilder('ar'); renderGuideBuilder('en'); renderPreviewFields('ar'); renderPreviewFields('en'); renderPreviewGuide('ar'); renderPreviewGuide('en'); updateCopyEditor('ar'); updateCopyEditor('en');
  root.find('[data-vava-toggle]').each(function () { updateToggleState(this); });
  syncHoldControls(); updateMailPreview();
  $('#post').on('submit.vavaContactGuideSave', syncBeforeSubmit);
  $('#publish, #save-post').on('mousedown.vavaContactGuideSave click.vavaContactGuideSave', syncBeforeSubmit);
  lockPostbox(); setupPageIdentity(); setupHeaderActions(); setupSidebar();
  var storedLanguage = 'ar'; var storedSection = 'hero'; try { storedLanguage = localStorage.getItem('vavaContactLanguage') || 'ar'; storedSection = localStorage.getItem('vavaContactSection') || 'hero'; } catch (error) {}
  activateLanguage(storedLanguage); syncMailLanguageField(); activateSection(storedSection); $(window).on('resize', fitCurrent);
})(jQuery);
