(function ($) {
  'use strict';

  var root = $('.vava-paths-advanced-admin').first();
  if (!root.length) return;

  function languagePane(element) {
    return $(element).closest('[data-language-pane]');
  }

  function activeLanguage() {
    return String(root.attr('data-active-language') || 'ar') === 'en' ? 'en' : 'ar';
  }

  function fieldValue(card, key) {
    var fields = card.find('[name$="[' + key + ']"]');
    var field = fields.filter(':not([type="hidden"])').first();
    if (!field.length) field = fields.first();
    if (!field.length) return '';
    if (field.is(':checkbox')) return field.is(':checked') ? '1' : '';
    return String(field.val() || '');
  }

  function fieldChecked(card, key) {
    return card.find('input[type="checkbox"][name$="[' + key + ']"]').first().is(':checked');
  }

  function escapeHtml(value) {
    return $('<div>').text(String(value || '')).html();
  }

  function comparisonBookingLabel(value, lang) {
    void value;
    return lang === 'en' ? 'Book package' : 'حجز الباقة';
  }

  function refreshComparisonGuidancePreview(source) {
    var pane = languagePane(source);
    var lang = String(pane.attr('data-language-pane') || 'ar');
    var preview = $('[data-paths-preview-root][data-preview-language="' + lang + '"][data-preview-section="comparison"]').first();
    if (!preview.length) return;
    var message = String(pane.find('[name$="[compare][guidance_html]"]').first().val() || '').trim();
    var select = pane.find('[name$="[compare][guidance_session_uid]"]').first();
    var title = String(select.find('option:selected').text() || '').trim();
    var note = preview.find('.vava-paths-preview-guidance-note').first();
    if (!message) {
      note.empty().hide();
      requestPreviewFit();
      return;
    }
    var link = title ? '<a class="vava-paths-guidance-session-link" href="#">' + escapeHtml(title) + '</a>' : '';
    if (link && message.indexOf('{session}') !== -1) {
      message = message.split('{session}').join(link);
    } else if (link && /<a\b[^>]*>[\s\S]*?<\/a>/i.test(message)) {
      message = message.replace(/<a\b[^>]*>[\s\S]*?<\/a>/i, link);
    } else if (link && message.indexOf(title) !== -1) {
      message = message.replace(title, link);
    } else if (link) {
      message += ' ' + link;
    }
    note.html(message).show();
    requestPreviewFit();
  }


  function basicIconSvg(icon) {
    var paths = {
      clock: '<circle cx="12" cy="12" r="8.25"/><path d="M12 7.5v5l3.2 1.9"/>',
      person: '<circle cx="12" cy="8" r="3.2"/><path d="M5.8 19c.8-3.7 2.9-5.5 6.2-5.5s5.4 1.8 6.2 5.5"/>',
      location: '<path d="M12 20s6-5.1 6-11a6 6 0 1 0-12 0c0 5.9 6 11 6 11Z"/><circle cx="12" cy="9" r="2"/>',
      price: '<path d="M4.5 7.5V4.8h6.4l8.6 8.6-6.1 6.1-8.9-8.9V7.5Z"/><circle cx="8.2" cy="8.2" r="1.2"/>',
      calendar: '<rect x="4" y="5.5" width="16" height="14" rx="2"/><path d="M8 3.8v3.4m8-3.4v3.4M4 9.5h16"/>',
      video: '<rect x="3.5" y="6" width="12" height="12" rx="2"/><path d="m15.5 10 5-3v10l-5-3"/>',
      leaf: '<path d="M19 4.5C12 4.8 7.6 8.1 7.2 13.2c-.2 2.7 1.7 5 4.5 5.1 5.2.2 7.4-5.3 7.3-13.8Z"/><path d="M5 20c2.2-4.9 5.7-8.6 10.4-11.1"/>',
      info: '<circle cx="12" cy="12" r="8.25"/><path d="M12 10.7v5.1M12 8h.01"/>'
    };
    icon = Object.prototype.hasOwnProperty.call(paths, icon) ? icon : 'info';
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round">' + paths[icon] + '</g></svg>';
  }

  function basicKeyFromLabel(label) {
    label = String(label || '').trim().toLowerCase();
    if (/(المدة|مدة الجلسة|duration)/i.test(label)) return 'duration';
    if (/(نوع\s*الجلسة|session\s*type)/i.test(label)) return 'session_type';
    if (/(المكان|الموقع|location|venue)/i.test(label)) return 'location';
    if (/(السعر|الاستثمار|price|investment)/i.test(label)) return 'price';
    return 'custom';
  }

  function defaultIconForBasic(key) {
    return { duration: 'clock', session_type: 'person', location: 'location', price: 'price' }[key] || 'info';
  }

  function basicItems(card) {
    var items = [];
    card.find('[data-session-basic-builder] [data-session-basic-row]').each(function () {
      var row = $(this);
      var label = String(row.find('[data-field-key="label"]').val() || '').trim();
      var value = String(row.find('[data-field-key="value"]').val() || '').trim();
      var icon = String(row.find('[data-field-key="icon"]').val() || 'info');
      var key = String(row.find('[data-field-key="key"]').val() || basicKeyFromLabel(label));
      if (label || value) items.push({ label: label, value: value, icon: icon, key: key });
    });
    return items;
  }

  function basicRowKey(row) {
    row = $(row);
    var label = String(row.find('[data-field-key="label"]').val() || '');
    var key = String(row.find('[data-field-key="key"]').val() || basicKeyFromLabel(label));
    if (!key || key === 'custom') key = basicKeyFromLabel(label);
    return key || 'custom';
  }

  function dedupeBasicRows(scope) {
    $(scope).find('[data-session-editor]').addBack('[data-session-editor]').each(function () {
      var card = $(this);
      var seen = {};
      card.find('[data-session-basic-row]').each(function () {
        var row = $(this);
        var key = basicRowKey(row);
        if (key === 'custom') return;
        if (!seen[key]) {
          seen[key] = row;
          return;
        }
        var first = seen[key];
        var firstValue = String(first.find('[data-field-key="value"]').val() || '').trim();
        var duplicateValue = String(row.find('[data-field-key="value"]').val() || '').trim();
        if (!firstValue && duplicateValue) {
          first.find('[data-field-key="value"]').val(duplicateValue);
          first.find('[data-field-key="label"]').val(String(row.find('[data-field-key="label"]').val() || ''));
          first.find('[data-field-key="icon"]').val(String(row.find('[data-field-key="icon"]').val() || defaultIconForBasic(key)));
        }
        row.remove();
      });
      var repeater = card.find('[data-session-basic-builder]').first();
      if (repeater.length) reindexSimple(repeater);
    });
  }

  function categoryLabel(value, lang) {
    var labels = lang === 'en'
      ? { quick: 'Quick consultations', followup: 'Follow-up sessions', comprehensive: 'Comprehensive sessions' }
      : { quick: 'استشارات سريعة', followup: 'جلسات متابعة', comprehensive: 'جلسات شاملة' };
    return labels[value] || labels.comprehensive;
  }

  function removeDurationBasicRows(scope) {
    $(scope).find('[data-session-basic-row]').each(function () {
      var row = $(this);
      var key = String(row.find('[data-field-key="key"]').val() || basicKeyFromLabel(row.find('[data-field-key="label"]').val()));
      if (key === 'duration') row.remove();
    });
    $(scope).find('[data-session-basic-builder]').each(function () { reindexSimple($(this)); });
  }

  function initBasicIcons(scope) {
    $(scope).find('[data-session-basic-row]').each(function () {
      var row = $(this);
      var label = String(row.find('[data-field-key="label"]').val() || '');
      var keyInput = row.find('[data-field-key="key"]');
      var key = String(keyInput.val() || basicKeyFromLabel(label));
      if (!key || key === 'custom') key = basicKeyFromLabel(label);
      keyInput.val(key || 'custom');
      row.attr('data-basic-key', key || 'custom');
      var select = row.find('[data-field-key="icon"]');
      var icon = String(select.val() || defaultIconForBasic(key));
      if (!select.val()) select.val(icon);
      row.find('[data-basic-icon-preview]').html(basicIconSvg(icon));
    });
  }

  function legacyField(card, key) {
    return card.find('[name$="[' + key + ']"]').filter(function () {
      return !$(this).closest('[data-session-basic-row]').length;
    }).first();
  }

  function splitPriceValue(value) {
    var original = String(value || '').trim();
    var latin = original.replace(/[٠-٩۰-۹]/g, function (digit) {
      return '٠١٢٣٤٥٦٧٨٩'.indexOf(digit) >= 0 ? String('٠١٢٣٤٥٦٧٨٩'.indexOf(digit)) : String('۰۱۲۳۴۵۶۷۸۹'.indexOf(digit));
    });
    var match = latin.match(/([0-9][0-9,.\s]*)/);
    if (!match) return { price: original, currency: '' };
    return { price: match[1].replace(/\s+/g, ''), currency: latin.replace(match[0], '').trim() };
  }

  function syncLegacyFromBasicRow(row) {
    row = $(row);
    var card = row.closest('[data-session-editor]');
    if (!card.length) return;
    var label = String(row.find('[data-field-key="label"]').val() || '');
    var value = String(row.find('[data-field-key="value"]').val() || '');
    var keyInput = row.find('[data-field-key="key"]');
    var key = basicKeyFromLabel(label);
    if (key === 'custom') key = String(keyInput.val() || 'custom');
    keyInput.val(key);
    row.attr('data-basic-key', key);
    if (key === 'duration' || key === 'session_type' || key === 'location') {
      legacyField(card, key).val(value);
    } else if (key === 'price') {
      var parts = splitPriceValue(value);
      legacyField(card, 'price').val(parts.price);
      if (parts.currency) legacyField(card, 'currency').val(parts.currency);
    }
  }

  function clearLegacyForBasicRow(row) {
    row = $(row);
    var card = row.closest('[data-session-editor]');
    var key = String(row.find('[data-field-key="key"]').val() || basicKeyFromLabel(row.find('[data-field-key="label"]').val()));
    if (key === 'session_type' || key === 'location') legacyField(card, key).val('');
    if (key === 'price') { legacyField(card, 'price').val(''); legacyField(card, 'currency').val(''); }
  }

  function syncBasicFromLegacy(card, key) {
    card = $(card);
    var row = card.find('[data-session-basic-row]').filter(function () {
      var item = $(this);
      var itemKey = String(item.find('[data-field-key="key"]').val() || basicKeyFromLabel(item.find('[data-field-key="label"]').val()));
      return itemKey === key;
    }).first();
    if (!row.length) return;
    if (key === 'price') {
      var price = String(legacyField(card, 'price').val() || '').trim();
      var currency = String(legacyField(card, 'currency').val() || '').trim();
      row.find('[data-field-key="value"]').val([price, currency].filter(Boolean).join(' '));
    } else {
      row.find('[data-field-key="value"]').val(String(legacyField(card, key).val() || ''));
    }
  }

  function requestPreviewFit() {
    window.setTimeout(function () { $(window).trigger('resize'); }, 20);
  }

  function repeaterTexts(card, group, key) {
    var values = [];
    card.find('[name*="[' + group + ']"][name$="[' + key + ']"]').each(function () {
      var value = String($(this).val() || '').trim();
      if (value) values.push(value);
    });
    return values;
  }

  function closeSiblingAccordions(item) {
    var list = item.parent();
    list.children('[data-vava-accordion]').not(item).removeClass('is-open').find('> header [data-advanced-accordion-toggle]').attr('aria-expanded', 'false');
  }

  function focusedSessionPreview(card, tab) {
    if (!card || !card.length) return;
    var pane = languagePane(card);
    var lang = String(pane.attr('data-language-pane') || 'ar');
    var preview = $('[data-paths-preview-root][data-preview-language="' + lang + '"][data-preview-section="packages"]').first();
    var focused = preview.find('[data-session-focused-preview]').first();
    if (!focused.length) return;
    var canvas = preview.find('.vava-paths-preview-canvas').first();
    if (tab === 'card') {
      canvas.removeClass('is-session-focused');
      focused.attr('hidden', true);
      return;
    }
    canvas.addClass('is-session-focused');
    focused.removeAttr('hidden').attr('data-focused-tab', tab);
    focused.find('[data-session-preview-title]').text(fieldValue(card, 'title'));
    focused.find('[data-session-preview-description]').text(tab === 'details' ? fieldValue(card, 'overview') : '');
    var facts = [];
    if (tab === 'details') {
      basicItems(card).forEach(function (item) {
        facts.push('<span class="vava-session-focused-fact"><i>' + basicIconSvg(item.icon) + '</i><small>' + escapeHtml(item.label) + '</small><b>' + escapeHtml(item.value) + '</b></span>');
      });
      var availability = fieldValue(card, 'availability');
      if (availability) facts.push('<span class="vava-session-focused-fact is-availability"><i>' + basicIconSvg('calendar') + '</i><small>' + (activeLanguage() === 'en' ? 'Availability' : 'حالة الحجز') + '</small><b>' + escapeHtml(availability) + '</b></span>');
    } else {
      ['duration', 'session_type', 'location', 'price'].forEach(function (key) {
        var value = fieldValue(card, key);
        if (value) facts.push('<span>' + escapeHtml(value) + '</span>');
      });
    }
    focused.find('[data-session-preview-facts]').html(facts.join(''));
    var content = [];
    if (tab === 'details' || tab === 'journey') {
      var overviewTitle = fieldValue(card, 'overview_title') || (lang === 'en' ? 'Session overview' : 'وصف الجلسة');
      var overview = fieldValue(card, 'overview');
      if (overview) content.push('<section><h4>' + escapeHtml(overviewTitle) + '</h4><p>' + escapeHtml(overview) + '</p></section>');
      [['audience','audience_title'],['outcomes','outcomes_title']].forEach(function (pair) {
        var items = repeaterTexts(card, pair[0], 'text');
        var fallback = pair[0] === 'audience' ? (lang === 'en' ? 'Suitable for you if…' : 'مناسبة لك إذا كنت...') : (lang === 'en' ? 'What does it include?' : 'ماذا تشمل؟');
        if (items.length) content.push('<section><h4>' + $('<div>').text(fieldValue(card, pair[1]) || fallback).html() + '</h4><ul>' + items.map(function (item) { return '<li>' + $('<div>').text(item).html() + '</li>'; }).join('') + '</ul></section>');
      });
      var img = card.find('.vava-session-media-preview img').attr('src') || '';
      focused.find('[data-session-preview-image]').css('background-image', img ? 'url(' + JSON.stringify(img) + ')' : 'none');
    } else if (tab === 'booking') {
      content.push('<section><h4>' + (activeLanguage() === 'en' ? 'Booking actions' : 'إجراءات الحجز') + '</h4><p>' + $('<div>').text(fieldValue(card, 'booking_url')).html() + '</p></section>');
    }
    focused.find('[data-session-preview-content]').html(content.join(''));
    var actions = [];
    var booking = fieldValue(card, 'booking_text');
    var back = fieldValue(card, 'return_text');
    if (booking) actions.push('<b>' + $('<div>').text(booking).html() + '</b>');
    if (back) actions.push('<span>' + $('<div>').text(back).html() + '</span>');
    focused.find('[data-session-preview-actions]').html(actions.join(''));
  }

  function uuid() {
    if (window.crypto && window.crypto.randomUUID) return window.crypto.randomUUID().replace(/[^a-zA-Z0-9-]/g, '');
    return 'vava-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
  }

  function replaceIndex(name, pattern, index) {
    return String(name || '').replace(pattern, function (_, prefix) { return prefix + '[' + index + ']'; });
  }

  function reindexSimple(repeater) {
    var base = String(repeater.attr('data-repeater-base') || '');
    if (!base) return;
    var language = String(languagePane(repeater).attr('data-language-pane') || 'ar');
    var bracketBase = base.split('.').map(function (part) { return '[' + part + ']'; }).join('');
    repeater.find('> [data-repeater-items] > [data-repeater-row]').each(function (index) {
      $(this).find('[data-field-key]').each(function () {
        var key = $(this).attr('data-field-key');
        $(this).attr('name', 'vava_paths[' + language + ']' + bracketBase + '[' + index + '][' + key + ']');
      });
    });
  }

  function reindexSessionList(list) {
    var lang = String(languagePane(list).attr('data-language-pane') || 'ar');
    list.children('[data-session-editor]').each(function (index) {
      var card = $(this);
      card.find('[name]').each(function () {
        var name = String($(this).attr('name') || '');
        name = replaceIndex(name, /(vava_paths\[(?:ar|en)\]\[packages\])\[\d+\]/, index);
        name = name.replace(/^vava_paths\[(?:ar|en)\]/, 'vava_paths[' + lang + ']');
        $(this).attr('name', name);
      });
      card.find('[data-vava-repeater]').each(function () { var repeater=$(this); repeater.attr('data-repeater-base', String(repeater.attr('data-repeater-base')||'').replace(/^packages\.\d+/, 'packages.'+index)); reindexSimple(repeater); });
    });
  }

  function sessionUid(card) {
    return String(card.find('[name$="[uid]"]').first().val() || '');
  }

  function setSessionUid(card, uid) {
    card.find('[name$="[uid]"]').first().val(String(uid || ''));
  }

  function pairedSessionList(list) {
    var pane = languagePane(list);
    var lang = String(pane.attr('data-language-pane') || 'ar');
    var other = lang === 'en' ? 'ar' : 'en';
    return root.find('[data-section-panel="sessions"] [data-language-pane="' + other + '"] [data-session-list]').first();
  }

  function sessionByUid(list, uid, fallbackIndex) {
    var matches = list.children('[data-session-editor]').filter(function () {
      return sessionUid($(this)) === uid;
    });
    if (matches.length === 1) return matches.first();
    if (matches.length > 1) {
      var exact = list.children('[data-session-editor]').eq(fallbackIndex);
      if (exact.length && sessionUid(exact) === uid) return exact;
      return matches.last();
    }
    return uid ? $() : list.children('[data-session-editor]').eq(fallbackIndex);
  }

  function reindexComparisonList(list) {
    var lang = String(languagePane(list).attr('data-language-pane') || 'ar');
    list.children('[data-comparison-plan]').each(function (index) {
      var card = $(this);
      card.find('[name]').each(function () {
        var name = String($(this).attr('name') || '');
        name = replaceIndex(name, /(vava_paths\[(?:ar|en)\]\[compare\]\[plans\])\[\d+\]/, index);
        name = name.replace(/^vava_paths\[(?:ar|en)\]/, 'vava_paths[' + lang + ']');
        $(this).attr('name', name);
      });
      card.find('[data-vava-repeater]').each(function () { var repeater=$(this); repeater.attr('data-repeater-base', String(repeater.attr('data-repeater-base')||'').replace(/^compare\.plans\.\d+/, 'compare.plans.'+index)); reindexSimple(repeater); });
    });
  }

  function cloneSession(source, lang, sharedUid) {
    var clone = source.clone(false, false);
    clone.find('input, textarea').each(function () {
      var input = $(this);
      var name = String(input.attr('name') || '');
      if (input.attr('type') === 'hidden' && /\[uid\]$/.test(name)) input.val(sharedUid || uuid());
      else if (input.attr('type') === 'checkbox') input.prop('checked', /\[(enabled|booking_enabled)\]$/.test(name));
      else if (input.attr('type') === 'hidden' && /\[(price|currency|duration|session_type|location|booking_url)\]$/.test(name)) input.val('');
      else if (input.attr('type') !== 'hidden') input.val('');
    });
    var basicDefaults = lang === 'en'
      ? [{ label: 'Session type', key: 'session_type', icon: 'person' }, { label: 'Location', key: 'location', icon: 'location' }, { label: 'Price', key: 'price', icon: 'price' }]
      : [{ label: 'نوع الجلسة', key: 'session_type', icon: 'person' }, { label: 'المكان', key: 'location', icon: 'location' }, { label: 'السعر', key: 'price', icon: 'price' }];
    clone.find('[data-session-basic-row]').each(function (index) {
      var row = $(this); var preset = basicDefaults[index];
      if (!preset) return;
      row.find('[data-field-key="label"]').val(preset.label);
      row.find('[data-field-key="key"]').val(preset.key);
      row.find('[data-field-key="icon"]').val(preset.icon);
      row.find('[data-field-key="value"]').val('');
      row.attr('data-basic-key', preset.key);
    });
    clone.find('select[name$="[category]"]').val('comprehensive');
    clone.find('[data-session-media-id]').val('0');
    clone.find('.vava-session-media-preview').html('<em>' + (lang === 'en' ? 'No image selected' : 'لم يتم اختيار صورة') + '</em>');
    clone.find('.vava-session-editor-head .vava-guide-summary b').text(lang === 'en' ? 'New session' : 'جلسة جديدة');
    clone.removeClass('is-open').find('> header [data-advanced-accordion-toggle]').attr('aria-expanded', 'false');
    clone.find('.vava-session-tabs button').removeClass('is-active').first().addClass('is-active');
    clone.find('[data-session-panel]').removeClass('is-active').first().addClass('is-active');
    setSessionUid(clone, sharedUid || sessionUid(clone) || uuid());
    return clone;
  }

  root.on('click', '[data-session-tab]', function () {
    var card = $(this).closest('[data-session-editor]');
    var tab = $(this).attr('data-session-tab');
    card.addClass('is-open').find('> header [data-advanced-accordion-toggle]').attr('aria-expanded', 'true');
    closeSiblingAccordions(card);
    card.find('[data-session-tab]').removeClass('is-active');
    $(this).addClass('is-active');
    card.find('[data-session-panel]').removeClass('is-active');
    card.find('[data-session-panel="' + tab + '"]').addClass('is-active');
    focusedSessionPreview(card, tab);
  });

  root.on('click', '[data-advanced-accordion-toggle]', function () {
    var item = $(this).closest('[data-vava-accordion]');
    var shouldOpen = !item.hasClass('is-open');
    closeSiblingAccordions(item);
    item.toggleClass('is-open', shouldOpen);
    $(this).attr('aria-expanded', shouldOpen ? 'true' : 'false');
    if (shouldOpen && item.is('[data-session-editor]')) focusedSessionPreview(item, item.find('[data-session-tab].is-active').attr('data-session-tab') || 'card');
    if (item.is('[data-comparison-plan]')) refreshComparisonPreview(item);
  });


  root.on('click', '[data-subaccordion-toggle]', function () {
    var item = $(this).closest('[data-vava-subaccordion]');
    var shouldOpen = !item.hasClass('is-open');
    item.siblings('[data-vava-subaccordion]').removeClass('is-open').find('> header [data-subaccordion-toggle]').attr('aria-expanded', 'false');
    item.toggleClass('is-open', shouldOpen);
    $(this).attr('aria-expanded', shouldOpen ? 'true' : 'false');
    if (item.closest('[data-section-panel="questions"]').length) refreshQuestionsPreview(item);
    if (item.closest('[data-section-panel="comparison"]').length) refreshComparisonPreview(item);
    var sessionCard = item.closest('[data-session-editor]');
    if (sessionCard.length && sessionCard.hasClass('is-open')) focusedSessionPreview(sessionCard, sessionCard.find('[data-session-tab].is-active').attr('data-session-tab') || 'journey');
  });

  root.on('click', '[data-repeater-add]', function () {
    var repeater = $(this).closest('[data-vava-repeater]');
    var template = repeater.children('template[data-repeater-template]').get(0);
    if (!template) return;
    var newRow = $(template.content.cloneNode(true));
    repeater.children('[data-repeater-items]').append(newRow);
    var accordion = repeater.children('[data-repeater-items]').children('[data-vava-subaccordion]').last();
    if (accordion.length) {
      accordion.siblings('[data-vava-subaccordion]').removeClass('is-open').find('> header [data-subaccordion-toggle]').attr('aria-expanded', 'false');
      accordion.addClass('is-open').find('> header [data-subaccordion-toggle]').attr('aria-expanded', 'true');
    }
    initBasicIcons(repeater);
    reindexSimple(repeater);
    if (repeater.closest('[data-section-panel="questions"]').length) refreshQuestionsPreview(repeater);
    if (repeater.closest('[data-section-panel="comparison"]').length) refreshComparisonPreview(repeater);
    var sessionCard = repeater.closest('[data-session-editor]');
    if (sessionCard.length) focusedSessionPreview(sessionCard, sessionCard.find('[data-session-tab].is-active').attr('data-session-tab') || 'journey');
  });

  root.on('click', '[data-repeater-remove]', function () {
    var repeater = $(this).closest('[data-vava-repeater]');
    var row = $(this).closest('[data-repeater-row]');
    if (row.is('[data-session-basic-row]')) clearLegacyForBasicRow(row);
    row.remove();
    if (repeater.is('[data-session-basic-builder]')) {
      var basicCard = repeater.closest('[data-session-editor]');
      dedupeBasicRows(basicCard);
      basicCard.find('[data-session-basic-row]').each(function () { syncLegacyFromBasicRow($(this)); });
    }
    reindexSimple(repeater);
    if (repeater.closest('[data-section-panel="questions"]').length) refreshQuestionsPreview(repeater);
    if (repeater.closest('[data-section-panel="comparison"]').length) refreshComparisonPreview(repeater);
    var sessionCard = repeater.closest('[data-session-editor]');
    if (sessionCard.length) focusedSessionPreview(sessionCard, sessionCard.find('[data-session-tab].is-active').attr('data-session-tab') || 'journey');
  });

  root.on('click', '[data-session-add]', function () {
    var pane = languagePane(this);
    var list = pane.find('[data-session-list]').first();
    var source = list.children('[data-session-editor]').last();
    if (!source.length) return;
    var lang = String(pane.attr('data-language-pane') || 'ar');
    var newUid = uuid();
    var clone = cloneSession(source, lang, newUid);
    list.append(clone);

    var otherList = pairedSessionList(list);
    var otherSource = otherList.children('[data-session-editor]').last();
    if (otherSource.length) {
      var otherLang = lang === 'en' ? 'ar' : 'en';
      otherList.append(cloneSession(otherSource, otherLang, newUid));
      reindexSessionList(otherList);
      refreshSessionsPreview(otherList);
    }

    closeSiblingAccordions(clone);
    clone.addClass('is-open').find('> header [data-advanced-accordion-toggle]').attr('aria-expanded', 'true');
    reindexSessionList(list);
    initSortables();
    refreshSessionsPreview(list);
    focusedSessionPreview(clone, 'card');
  });

  root.on('click', '[data-session-remove]', function () {
    var list = $(this).closest('[data-session-list]');
    if (list.children('[data-session-editor]').length <= 1) return;
    var card = $(this).closest('[data-session-editor]');
    var index = list.children('[data-session-editor]').index(card);
    var uid = sessionUid(card);
    var otherList = pairedSessionList(list);
    var otherCard = sessionByUid(otherList, uid, index);

    card.remove();
    if (otherCard.length && otherList.children('[data-session-editor]').length > 1) {
      otherCard.remove();
      reindexSessionList(otherList);
      refreshSessionsPreview(otherList);
    }
    reindexSessionList(list);
    refreshSessionsPreview(list);
  });

  root.on('click', '[data-comparison-add]', function () {
    var list = languagePane(this).find('[data-comparison-list]').first();
    var source = list.children('[data-comparison-plan]').last();
    if (!source.length) return;
    var clone = source.clone(false, false);
    clone.find('input, textarea').each(function () {
      var input = $(this);
      if (input.attr('type') === 'hidden' && /\[uid\]$/.test(input.attr('name') || '')) input.val(uuid());
      else if (input.attr('type') === 'checkbox') input.prop('checked', false);
      else if (input.attr('type') !== 'hidden') input.val('');
    });
    clone.removeClass('is-open').find('> header [data-advanced-accordion-toggle]').attr('aria-expanded', 'false');
    clone.find('header > b').text(activeLanguage() === 'en' ? 'New package' : 'باقة جديدة');
    list.append(clone);
    closeSiblingAccordions(clone);
    clone.addClass('is-open').find('> header [data-advanced-accordion-toggle]').attr('aria-expanded', 'true');
    reindexComparisonList(list);
    refreshComparisonPreview(list);
  });

  root.on('click', '[data-comparison-remove]', function () {
    var list = $(this).closest('[data-comparison-list]');
    if (list.children('[data-comparison-plan]').length <= 1) return;
    $(this).closest('[data-comparison-plan]').remove();
    reindexComparisonList(list);
    refreshComparisonPreview(list);
  });

  function reindexPathways(list) {
    var lang = String(languagePane(list).attr('data-language-pane') || 'ar');
    list.children('[data-pathway-card]').each(function (index) {
      var card = $(this);
      card.find('[name]').each(function () {
        var name = String($(this).attr('name') || '');
        name = replaceIndex(name, /(vava_paths\[(?:ar|en)\]\[pathways\])\[\d+\]/, index);
        name = name.replace(/^vava_paths\[(?:ar|en)\]/, 'vava_paths[' + lang + ']');
        $(this).attr('name', name);
      });
    });
  }

  function syncPathwayOrder(source) {
    var pane = languagePane(source);
    var lang = String(pane.attr('data-language-pane') || 'ar');
    var other = lang === 'ar' ? 'en' : 'ar';
    var target = root.find('[data-section-panel="pathways"] [data-language-pane="' + other + '"] [data-pathway-sort]').first();
    if (!target.length) return;
    var order = [];
    source.children('[data-pathway-card]').each(function () { order.push(String($(this).find('[name$="[uid]"]').val() || '')); });
    var map = {};
    target.children('[data-pathway-card]').each(function () { map[String($(this).find('[name$="[uid]"]').val() || '')] = this; });
    order.forEach(function (uid) { if (map[uid]) target.append(map[uid]); });
    reindexPathways(target);
    refreshPathwaysPreview(target);
  }

  function syncSessionOrder(source) {
    var pane = languagePane(source);
    var lang = String(pane.attr('data-language-pane') || 'ar');
    var other = lang === 'ar' ? 'en' : 'ar';
    var target = root.find('[data-section-panel="sessions"] [data-language-pane="' + other + '"] [data-session-list]').first();
    if (!target.length) return;
    var order = [];
    source.children('[data-session-editor]').each(function () { order.push(String($(this).find('[name$="[uid]"]').val() || '')); });
    var map = {};
    target.children('[data-session-editor]').each(function () { map[String($(this).find('[name$="[uid]"]').val() || '')] = this; });
    order.forEach(function (uid) { if (map[uid]) target.append(map[uid]); });
    reindexSessionList(target);
    refreshSessionsPreview(target);
  }

  function refreshPathwaysPreview(list) {
    var lang = String(languagePane(list).attr('data-language-pane') || 'ar');
    var preview = $('[data-paths-preview-root][data-preview-language="' + lang + '"][data-preview-section="pathways"]').first();
    var grid = preview.find('.vava-paths-preview-pathways-grid').first();
    if (!grid.length) return;
    var cards = list.children('[data-pathway-card]');
    var articles = grid.children('article');
    cards.each(function (index) {
      var card = $(this);
      var article = articles.eq(index);
      if (!article.length) return;
      article.toggleClass('featured', fieldChecked(card, 'featured'));
      article.toggle(fieldChecked(card, 'enabled'));
      article.attr('data-paths-preview-class', 'pathways.' + index + '.featured');
      article.find('[data-paths-preview]').each(function () {
        var node = $(this);
        var oldPath = String(node.attr('data-paths-preview') || '');
        node.attr('data-paths-preview', oldPath.replace(/pathways\.\d+\./, 'pathways.' + index + '.'));
      });
      article.find('[data-paths-preview-html]').each(function () {
        var node = $(this);
        var oldPath = String(node.attr('data-paths-preview-html') || '');
        node.attr('data-paths-preview-html', oldPath.replace(/pathways\.\d+\./, 'pathways.' + index + '.'));
      });
      article.find('[data-paths-preview$=".badge"]').text(fieldValue(card, 'badge'));
      article.find('[data-paths-preview$=".title"]').text(fieldValue(card, 'title'));
      article.find('[data-paths-preview-html$=".description"]').html(fieldValue(card, 'description'));
      article.find('[data-paths-preview$=".button_text"]').text(fieldValue(card, 'button_text'));
    });
    requestPreviewFit();
  }

  function refreshSessionsPreview(list) {
    var lang = String(languagePane(list).attr('data-language-pane') || 'ar');
    var preview = $('[data-paths-preview-root][data-preview-language="' + lang + '"][data-preview-section="packages"]').first();
    var grid = preview.find('.vava-paths-preview-package-grid').first();
    if (!grid.length) return;
    var cards = list.children('[data-session-editor]');
    var html = [];
    cards.each(function (index) {
      var card = $(this);
      if (!fieldChecked(card, 'enabled')) return;
      var featured = fieldChecked(card, 'featured') ? ' featured' : '';
      html.push('<article class="vava-paths-preview-package-card is-simple' + featured + '" data-paths-preview-class="packages.' + index + '.featured">' +
        '<strong data-paths-preview="packages.' + index + '.title">' + escapeHtml(fieldValue(card, 'title')) + '</strong>' +
        '<div class="vava-paths-preview-package-bottom"><div class="vava-paths-preview-price"><b data-paths-preview="packages.' + index + '.price">' + escapeHtml(fieldValue(card, 'price')) + '</b><span data-paths-preview="packages.' + index + '.currency">' + escapeHtml(fieldValue(card, 'currency')) + '</span></div><span class="vava-paths-preview-details-link" data-paths-preview="packages.' + index + '.link_text">' + escapeHtml(fieldValue(card, 'link_text')) + '</span></div>' +
      '</article>');
    });
    grid.html(html.join(''));
    requestPreviewFit();
  }


  function refreshComparisonPreview(source) {
    var pane = languagePane(source);
    var lang = String(pane.attr('data-language-pane') || 'ar');
    var preview = $('[data-paths-preview-root][data-preview-language="' + lang + '"][data-preview-section="comparison"]').first();
    var grid = preview.find('.vava-paths-preview-compare-grid').first();
    if (!grid.length) return;
    var html = [];
    pane.find('[data-comparison-list] > [data-comparison-plan]').each(function (index) {
      var card = $(this);
      if (!fieldChecked(card, 'enabled')) return;
      var featured = fieldChecked(card, 'featured') ? ' featured' : '';
      var features = [];
      card.find('.vava-comparison-feature-builder [data-repeater-items] > [data-repeater-row]').each(function () {
        var feature = $(this);
        var status = feature.find('input[type="checkbox"][data-field-key="visible"]').first();
        var available = !status.length || status.is(':checked');
        var label = String(feature.find('[data-field-key="text"]').val() || '').trim();
        var value = String(feature.find('[data-field-key="value"]').val() || '').trim();
        if (!label && !value) return;
        features.push('<li class="' + (available ? 'is-available' : 'is-unavailable') + '"><span class="vava-paths-preview-feature-mark ' + (available ? 'yes' : 'no') + '">' + (available ? '✓' : '×') + '</span><span>' + escapeHtml(label) + '</span>' + (value ? '<b>' + escapeHtml(value) + '</b>' : '') + '</li>');
      });
      html.push('<article class="vava-paths-preview-compare-plan' + featured + '">' +
        '<span class="vava-paths-preview-compare-badge">' + escapeHtml(fieldValue(card, 'badge')) + '</span>' +
        '<div class="vava-paths-preview-compare-icon" aria-hidden="true">✦</div>' +
        '<strong class="vava-paths-preview-compare-title">' + escapeHtml(fieldValue(card, 'title')) + '</strong>' +
        '<div class="vava-paths-preview-compare-description">' + fieldValue(card, 'description') + '</div>' +
        '<div class="vava-paths-preview-core">' + escapeHtml(fieldValue(card, 'core_label')) + '</div>' +
        '<ul class="vava-paths-preview-feature-list">' + features.join('') + '</ul>' +
        '<div class="vava-paths-preview-plan-action"><div class="vava-paths-preview-plan-price"><b>' + escapeHtml(fieldValue(card, 'price')) + '</b></div><span class="vava-paths-preview-plan-button">' + escapeHtml(comparisonBookingLabel(fieldValue(card, 'button_text'), lang)) + '</span></div>' +
      '</article>');
    });
    grid.html(html.join(''));
    requestPreviewFit();
  }


  function refreshQuestionsPreview(pane) {
    pane = $(pane).closest('[data-language-pane]');
    var lang = String(pane.attr('data-language-pane') || 'ar');
    var preview = $('[data-paths-preview-root][data-preview-language="' + lang + '"][data-preview-section="questions"]').first();
    if (!preview.length) return;
    var faq = [];
    pane.find('.vava-booking-faq-builder [data-repeater-row]').each(function (index) {
      var row = $(this);
      var question = String(row.find('[data-field-key="question"]').val() || '').trim();
      var answer = String(row.find('[data-field-key="answer"]').val() || '').trim();
      if (!question && !answer) return;
      faq.push('<article><div class="vava-paths-preview-faq-question"><span>' + escapeHtml(question) + '</span><b>' + (index === 0 ? '−' : '+') + '</b></div>' + (index === 0 ? '<div class="vava-paths-preview-faq-answer">' + escapeHtml(answer) + '</div>' : '') + '</article>');
    });
    preview.find('[data-question-preview-faq]').html(faq.join(''));
    requestPreviewFit();
  }

  function sortableOptions(stopHandler) {
    return {
      handle: '.vava-repeater-handle',
      cancel: 'input,textarea,select,option,a,[data-session-remove],[data-comparison-remove],[data-repeater-remove]',
      axis: 'y',
      tolerance: 'pointer',
      forcePlaceholderSize: true,
      placeholder: 'vava-sort-placeholder',
      start: function (event, ui) { ui.item.addClass('is-sorting'); },
      stop: function (event, ui) { ui.item.removeClass('is-sorting'); stopHandler.call(this, event, ui); }
    };
  }

  function initSortables() {
    root.find('[data-pathway-sort]').each(function () {
      var list = $(this);
      if (list.hasClass('ui-sortable')) list.sortable('destroy');
      list.sortable($.extend(sortableOptions(function () {
        reindexPathways(list);
        syncPathwayOrder(list);
        refreshPathwaysPreview(list);
      }), { items: '> [data-pathway-card]' }));
    });
    root.find('[data-session-list]').each(function () {
      var list = $(this);
      if (list.hasClass('ui-sortable')) list.sortable('destroy');
      list.sortable($.extend(sortableOptions(function () {
        reindexSessionList(list);
        syncSessionOrder(list);
        refreshSessionsPreview(list);
      }), { items: '> [data-session-editor]' }));
    });
    root.find('[data-comparison-list]').each(function () {
      var list = $(this);
      if (list.hasClass('ui-sortable')) list.sortable('destroy');
      list.sortable($.extend(sortableOptions(function () { reindexComparisonList(list); requestPreviewFit(); }), { items: '> [data-comparison-plan]' }));
    });
    root.find('[data-vava-repeater] > [data-repeater-items]').each(function () {
      var list = $(this);
      var repeater = list.closest('[data-vava-repeater]');
      if (list.hasClass('ui-sortable')) list.sortable('destroy');
      if (repeater.is('[data-no-sort]') || repeater.hasClass('vava-answer-builder') || repeater.hasClass('vava-booking-faq-builder')) return;
      list.sortable($.extend(sortableOptions(function () {
        reindexSimple(repeater);
        if (repeater.is('[data-session-basic-builder]')) {
          var card = repeater.closest('[data-session-editor]');
          focusedSessionPreview(card, card.find('[data-session-tab].is-active').attr('data-session-tab') || 'details');
        }
      }), { items: '> [data-repeater-row]' }));
    });
  }

  root.on('click', '[data-session-media-select]', function () {
    var media = $(this).closest('[data-vava-session-media]');
    var frame = wp.media({ title: activeLanguage() === 'en' ? 'Choose image' : 'اختيار صورة', button: { text: activeLanguage() === 'en' ? 'Use image' : 'استخدام الصورة' }, multiple: false, library: { type: 'image' } });
    frame.on('select', function () {
      var attachment = frame.state().get('selection').first().toJSON();
      var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
      media.find('[data-session-media-id]').val(attachment.id).trigger('change');
      media.find('.vava-session-media-preview').html($('<img>', { src: url, alt: '' }));
    });
    frame.open();
  });

  root.on('click', '[data-session-media-remove]', function () {
    var media = $(this).closest('[data-vava-session-media]');
    media.find('[data-session-media-id]').val('0').trigger('change');
    media.find('.vava-session-media-preview').html('<em>' + (activeLanguage() === 'en' ? 'No image selected' : 'لم يتم اختيار صورة') + '</em>');
  });

  root.on('input change', '[data-session-editor] input, [data-session-editor] textarea, [data-session-editor] select', function () {
    var input = $(this);
    var card = input.closest('[data-session-editor]');
    var basicRow = input.closest('[data-session-basic-row]');
    if (basicRow.length) {
      if (input.is('[data-field-key="label"]')) {
        var key = basicKeyFromLabel(input.val());
        if (key === 'duration') {
          var builder = basicRow.closest('[data-session-basic-builder]');
          basicRow.remove();
          if (builder.length) reindexSimple(builder);
          return;
        }
        basicRow.find('[data-field-key="key"]').val(key);
        basicRow.attr('data-basic-key', key);
        var iconSelect = basicRow.find('[data-field-key="icon"]');
        if (String(iconSelect.val() || 'info') === 'info' && key !== 'custom') iconSelect.val(defaultIconForBasic(key));
      }
      if (input.is('[data-field-key="icon"]') || input.is('[data-field-key="label"]')) initBasicIcons(basicRow);
      syncLegacyFromBasicRow(basicRow);
      if (input.is('[data-field-key="label"]')) {
        dedupeBasicRows(card);
        card.find('[data-session-basic-row]').each(function () { syncLegacyFromBasicRow($(this)); });
      }
    } else {
      var name = String(input.attr('name') || '');
      if (/\[price\]$/.test(name) || /\[currency\]$/.test(name)) syncBasicFromLegacy(card, 'price');
    }
    var inputName = String(input.attr('name') || '');
    if (/\[packages\]\[\d+\]\[title\]$/.test(inputName)) card.find('> header .vava-guide-summary b').first().text(String(input.val() || ''));
    if (/\[packages\]\[\d+\]\[category\]$/.test(inputName)) card.find('[data-session-category-summary]').first().text(categoryLabel(String(input.val() || 'comprehensive'), String(languagePane(card).attr('data-language-pane') || 'ar')));
    var nested = input.closest('[data-vava-subaccordion]');
    if (nested.length && input.is('[data-field-key="title"], [data-field-key="question"]')) nested.find('> header .vava-guide-subcard-toggle strong').first().text(String(input.val() || (activeLanguage() === 'en' ? 'New item' : 'عنصر جديد')));
    if (!card.hasClass('is-open')) return;
    focusedSessionPreview(card, card.find('[data-session-tab].is-active').attr('data-session-tab') || 'card');
  });

  root.on('input change', '[data-section-panel="questions"] input, [data-section-panel="questions"] textarea, [data-section-panel="questions"] select', function () {
    var pane = languagePane(this);
    if (/\[question\]$/.test(String($(this).attr('name') || ''))) $(this).closest('[data-vava-subaccordion]').find('> header .vava-guide-summary b, > header .vava-guide-subcard-toggle strong').first().text(String($(this).val() || ''));
    refreshQuestionsPreview(pane);
  });
  root.on('input change', '[data-comparison-plan] input, [data-comparison-plan] textarea, [data-comparison-plan] select', function () {
    var card = $(this).closest('[data-comparison-plan]');
    if (/\[title\]$/.test(String($(this).attr('name') || ''))) card.find('> header .vava-guide-summary b').first().text(String($(this).val() || ''));
    var feature = $(this).closest('.vava-comparison-feature-card');
    if (feature.length && $(this).is('[data-field-key="text"]')) feature.find('> header strong').text(String($(this).val() || ''));
    refreshComparisonPreview(card);
  });
  root.on('input change', '[data-section-panel="comparison"] input, [data-section-panel="comparison"] textarea, [data-section-panel="comparison"] select', function () {
    if ($(this).closest('[data-comparison-plan]').length) return;
    refreshComparisonGuidancePreview(this);
  });
  root.on('input change', '[data-pathway-card] input, [data-pathway-card] textarea, [data-pathway-card] select', function () { var list=$(this).closest('[data-pathway-sort]'); if (/\[title\]$/.test(String($(this).attr('name') || ''))) $(this).closest('[data-pathway-card]').find('> header b').first().text(String($(this).val() || '')); refreshPathwaysPreview(list); });

  $(function () {
    removeDurationBasicRows(root);
    initBasicIcons(root);
    dedupeBasicRows(root);
    root.find('[data-session-basic-row]').each(function () { syncLegacyFromBasicRow($(this)); });
    root.find('[data-session-editor]').each(function () {
      var card = $(this);
      var select = card.find('select[name$="[category]"]').first();
      card.find('[data-session-category-summary]').first().text(categoryLabel(String(select.val() || 'comprehensive'), String(languagePane(card).attr('data-language-pane') || 'ar')));
    });
    initSortables();
    root.find('[data-pathway-sort]').each(function () { reindexPathways($(this)); });
    root.find('[data-vava-repeater]').each(function () { reindexSimple($(this)); });
    root.find('[data-session-list]').each(function () { reindexSessionList($(this)); });
    root.find('[data-comparison-list]').each(function () { reindexComparisonList($(this)); refreshComparisonPreview($(this)); });
    root.find('[data-section-panel="comparison"] [data-language-pane]').each(function () { refreshComparisonGuidancePreview($(this)); });
    root.find('[data-question-items]').each(function () { reindexQuestions($(this)); });
    root.find('[data-pathway-sort]').each(function () { refreshPathwaysPreview($(this)); });
    root.find('[data-session-list]').each(function () { refreshSessionsPreview($(this)); });
    root.find('[data-session-editor].is-open').each(function () { focusedSessionPreview($(this), 'card'); });
    root.find('[data-section-panel="questions"] [data-language-pane]').each(function () { refreshQuestionsPreview($(this)); });
  });
})(jQuery);
