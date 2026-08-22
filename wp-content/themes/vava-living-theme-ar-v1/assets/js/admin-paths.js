(function ($) {
  'use strict';

  var root = $('.vava-paths-admin').first();
  if (!root.length) return;

  var config = window.vavaPathsAdmin || {};
  $('body').addClass('vava-homepage-classic vava-paths-classic');

  function currentLanguage() {
    return String(root.attr('data-active-language') || 'ar') === 'en' ? 'en' : 'ar';
  }

  function translated(arabic, english, language) {
    return (language || currentLanguage()) === 'en' ? english : arabic;
  }

  function pathFromName(name) {
    var matches = String(name || '').match(/^vava_paths\[(ar|en)\]((?:\[[^\]]+\])+)/);
    if (!matches) return null;
    var parts = [];
    String(matches[2]).replace(/\[([^\]]+)\]/g, function (_, part) { parts.push(part); });
    return { language: matches[1], path: parts.join('.') };
  }

  function isSharedFunctionalPath(path) {
    return /^packages\.\d+\.(?:featured|link_url)$/.test(path)
      || /^compare\.plans\.\d+\.featured$/.test(path)
      || /^compare\.plans\.\d+\.features\.\d+\.visible$/.test(path)
      || /^closing\.button_[12]_url$/.test(path);
  }

  function syncSharedFunctionalField(field) {
    var meta = pathFromName(field.attr('name'));
    if (!meta || !isSharedFunctionalPath(meta.path)) return;
    var targetLanguage = meta.language === 'en' ? 'ar' : 'en';
    var targetName = String(field.attr('name') || '').replace('vava_paths[' + meta.language + ']', 'vava_paths[' + targetLanguage + ']');
    var target = root.find('[name="' + targetName + '"]').first();
    if (!target.length) return;
    if (field.is(':checkbox')) target.prop('checked', field.is(':checked'));
    else target.val(field.val());
    updatePreview(target);
  }

  function normalizePreviewSection(section) {
    var map = { pathways: 'pathways', sessions: 'packages', questions: 'questions' };
    return map[section] || section;
  }

  function previewFor(section, language) {
    section = normalizePreviewSection(section);
    return $('[data-paths-preview-root][data-preview-section="' + section + '"][data-preview-language="' + language + '"]').first();
  }

  function fitPreview(preview) {
    if (!preview || !preview.length || preview.attr('hidden')) return;
    var viewport = preview.find('.vava-preview-viewport').first();
    var stage = preview.find('.vava-preview-stage').first();
    var canvas = preview.find('.vava-preview-canvas').first();
    if (!viewport.length || !stage.length || !canvas.length) return;

    window.requestAnimationFrame(function () {
      var designWidth = parseFloat(canvas.attr('data-preview-design-width')) || 900;
      var availableWidth = Math.max(280, viewport.innerWidth() - 24);
      var scale = Math.min(1, availableWidth / designWidth);
      canvas.css({
        width: designWidth + 'px',
        transform: 'scale(' + scale + ')'
      });
      window.requestAnimationFrame(function () {
        var designHeight = Math.max(1, canvas.get(0).scrollHeight || 0);
        stage.css({
          width: Math.round(designWidth * scale) + 'px',
          height: Math.round(designHeight * scale) + 'px'
        });
      });
    });
  }

  function fitCurrentPreview() {
    fitPreview(previewFor(String(root.attr('data-active-section') || 'hero'), currentLanguage()));
  }

  function applyInterfaceLanguage(language) {
    language = language === 'en' ? 'en' : 'ar';
    root.find('[data-vava-i18n-ar][data-vava-i18n-en]').each(function () {
      var element = $(this);
      var text = element.attr('data-vava-i18n-' + language);
      if (typeof text !== 'undefined') element.text(text);
    });
    root.find('[data-vava-i18n-aria-ar][data-vava-i18n-aria-en]').each(function () {
      var element = $(this);
      var label = element.attr('data-vava-i18n-aria-' + language);
      if (typeof label !== 'undefined') element.attr('aria-label', label);
    });
    var title = root.attr('data-settings-title-' + language);
    if (title) $('#vava_homepage_settings .postbox-header h2').first().text(title);
  }

  function updatePageTitleLanguage(language) {
    language = language === 'en' ? 'en' : 'ar';
    root.find('[data-vava-page-title-pane]').removeClass('is-active').attr('hidden', true);
    root.find('[data-vava-page-title-pane="' + language + '"]').addClass('is-active').removeAttr('hidden');
  }

  function setupPageIdentity() {
    var arabicTitle = root.find('[data-vava-page-title-language="ar"]');
    var nativeTitle = $('#title');
    $('.wrap > h1.wp-heading-inline, .wrap > .page-title-action, #titlediv').hide();
    updatePageTitleLanguage(currentLanguage());
    if (!arabicTitle.length || !nativeTitle.length) return;
    nativeTitle.attr('aria-hidden', 'true').val(arabicTitle.val() || '');
    arabicTitle.on('input change', function () {
      nativeTitle.val($(this).val() || '').trigger('input');
    });
  }

  function lockPostbox() {
    var box = $('#vava_homepage_settings');
    box.removeClass('closed').addClass('vava-homepage-postbox-is-locked');
    box.find('.handle-actions, .handlediv').remove();
    box.find('.postbox-header .hndle')
      .removeClass('hndle ui-sortable-handle')
      .attr('aria-disabled', 'true');
    box.off('.vavaPathsLock').on('click.vavaPathsLock mousedown.vavaPathsLock', '.postbox-header', function (event) {
      if ($(event.target).closest('button, a, input, select, textarea').length) return;
      event.preventDefault();
      event.stopImmediatePropagation();
    });
    $('#postimagediv').remove();
  }

  function setupSettingsHeader() {
    var box = $('#vava_homepage_settings');
    var header = box.find('.postbox-header').first();
    var toolbarActions = root.find('.vava-toolbar-actions').first();
    var languageSwitch = toolbarActions.find('.vava-language-switch').first();
    var button = toolbarActions.find('[data-vava-submit]').first();
    if (!header.length || !languageSwitch.length || !button.length) return;

    var headerActions = header.find('.vava-postbox-header-actions');
    if (!headerActions.length) {
      headerActions = $('<div>', { class: 'vava-postbox-header-actions' }).appendTo(header);
    }
    languageSwitch.addClass('is-in-postbox-header').appendTo(headerActions);
    button.addClass('is-in-postbox-header').appendTo(headerActions);
    toolbarActions.remove();
  }

  function updateSidebarPreview() {
    var section = String(root.attr('data-active-section') || 'hero');
    var language = currentLanguage();
    $('[data-paths-preview-root]').attr('hidden', true).removeClass('is-sidebar-active');
    var active = previewFor(section, language).removeAttr('hidden').addClass('is-sidebar-active');
    fitPreview(active);
  }

  function setupSidebar() {
    var side = $('#side-sortables');
    var previews = root.find('[data-paths-preview-root]');
    if (!side.length || !previews.length) return;

    $('#submitdiv, #pageparentdiv, #postimagediv').hide();
    var dock = $('#vava_live_preview_box');
    if (!dock.length) {
      dock = $('<div>', { id: 'vava_live_preview_box', class: 'postbox vava-live-preview-postbox' })
        .append($('<div>', { class: 'inside' }));
      side.prepend(dock);
    }
    dock.find('.inside').append(previews);
    updateSidebarPreview();

    if (window.ResizeObserver) {
      var observer = new window.ResizeObserver(function () { fitCurrentPreview(); });
      observer.observe(dock.get(0));
    }
  }

  function switchLanguage(language) {
    language = language === 'en' ? 'en' : 'ar';
    root.attr('data-active-language', language);
    root.find('[data-vava-active-language-input]').val(language);
    $('#vava_homepage_settings .vava-language-switch button').removeClass('is-active');
    $('#vava_homepage_settings .vava-language-switch button[data-language="' + language + '"]').addClass('is-active');
    root.find('[data-language-pane]').removeClass('is-active');
    root.find('[data-language-pane="' + language + '"]').addClass('is-active');
    updatePageTitleLanguage(language);
    applyInterfaceLanguage(language);
    updateSidebarPreview();
    try { localStorage.setItem('vavaPathsLanguage', language); } catch (error) {}
  }

  function switchSection(section) {
    if (!root.find('[data-section="' + section + '"]').length) section = 'hero';
    root.attr('data-active-section', section);
    root.find('[data-section]').removeClass('is-active').attr('aria-selected', 'false');
    root.find('[data-section="' + section + '"]').addClass('is-active').attr('aria-selected', 'true');
    root.find('[data-section-panel]').removeClass('is-active');
    root.find('[data-section-panel="' + section + '"]').addClass('is-active');
    updateSidebarPreview();
    try { localStorage.setItem('vavaPathsSection', section); } catch (error) {}
  }

  function updatePreview(field) {
    var meta = pathFromName(field.attr('name'));
    if (!meta) return;
    var section = String(field.closest('[data-section-panel]').attr('data-section-panel') || 'hero');
    var preview = previewFor(section, meta.language);
    if (!preview.length) return;

    var isCheckbox = field.is(':checkbox');
    var checked = isCheckbox && field.is(':checked');
    var value = isCheckbox ? (checked ? '1' : '') : field.val();

    preview.find('[data-paths-preview="' + meta.path + '"]').text(value || '');
    preview.find('[data-paths-preview-html="' + meta.path + '"]').html(value || '');
    preview.find('[data-paths-preview-class="' + meta.path + '"]').toggleClass('featured', checked);
    preview.find('[data-paths-preview-enabled="' + meta.path + '"]')
      .toggleClass('yes', checked)
      .toggleClass('no', !checked)
      .text(checked ? '✓' : '—');

    if (/\.(?:title|question)$/.test(meta.path)) {
      field.closest('[data-paths-accordion-item]').find('.vava-paths-accordion-head strong').first().text(value || '');
    }
    fitPreview(preview);
  }

  function reindexList(list) {
    var pattern = String(list.attr('data-paths-list-pattern') || '');
    if (!pattern) return;
    var escaped = pattern.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    var matcher = new RegExp(escaped + '\\[\\d+\\]');
    list.children('.vava-paths-accordion-item').each(function (index) {
      var item = $(this);
      item.find('[name]').each(function () {
        var field = $(this);
        field.attr('name', String(field.attr('name') || '').replace(matcher, pattern + '[' + index + ']'));
      });
      item.find('.vava-paths-item-number').text(String(index + 1).padStart(2, '0'));
    });
  }

  function itemSourceIndex(item, pattern) {
    var name = String(item.find('[name]').first().attr('name') || '');
    var escaped = pattern.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    var match = name.match(new RegExp(escaped + '\\[(\\d+)\\]'));
    return match ? String(match[1]) : '';
  }

  function syncFixedListOrder(source) {
    var pattern = String(source.attr('data-paths-list-pattern') || '');
    if (!pattern) return;
    var languagePane = source.closest('[data-language-pane]');
    var language = String(languagePane.attr('data-language-pane') || 'ar');
    var targetLanguage = language === 'en' ? 'ar' : 'en';
    var section = String(source.closest('[data-section-panel]').attr('data-section-panel') || '');
    var target = root.find('[data-section-panel="' + section + '"] [data-language-pane="' + targetLanguage + '"] [data-paths-list-pattern="' + pattern + '"]').first();
    if (!target.length) return;
    var order = [];
    source.children('.vava-paths-accordion-item').each(function () { order.push(itemSourceIndex($(this), pattern)); });
    var items = {};
    target.children('.vava-paths-accordion-item').each(function () { items[itemSourceIndex($(this), pattern)] = this; });
    $.each(order, function (_, index) { if (items[index]) target.append(items[index]); });
    reindexList(target);
  }

  function initSortable() {
    $('.vava-paths-fixed-list[data-paths-list-pattern]').each(function () {
      var list = $(this);
      if (list.attr('data-sort-ready') === '1') return;
      list.attr('data-sort-ready', '1');
      list.sortable({
        items: '> .vava-paths-accordion-item',
        handle: '.vava-repeater-handle',
        axis: 'y',
        tolerance: 'pointer',
        stop: function () {
          syncFixedListOrder(list);
          reindexList(list);
          fitCurrentPreview();
        }
      });
    });
  }

  function emptyMediaPreview() {
    var language = currentLanguage();
    return '<div class="vava-media-empty"><svg viewBox="0 0 48 48"><rect x="5" y="7" width="38" height="34" rx="5"/><circle cx="17" cy="18" r="4"/><path d="m9 36 11-11 8 8 5-5 7 8"/></svg><strong>' + translated('اسحب الصورة وأفلتها هنا', 'Drag and drop the image here', language) + '</strong><span>' + translated('أو اضغط للاختيار من مكتبة الوسائط', 'Or click to choose from the media library', language) + '</span></div>';
  }

  function mediaWrapper(element) {
    return element.closest('[data-paths-media-field]');
  }

  function updateHeroPreviewImage(url) {
    url = String(url || '');
    $('[data-paths-preview-image="hero"]').css('background-image', url ? 'url(' + JSON.stringify(url) + ')' : 'none');
    fitCurrentPreview();
  }

  function setMedia(wrapper, attachment) {
    var field = wrapper.find('.vava-media-field').first();
    var input = wrapper.find('[data-paths-media-id]').first();
    var url = attachment.sizes && attachment.sizes.medium_large ? attachment.sizes.medium_large.url : (attachment.url || '');
    var title = attachment.title || attachment.filename || '';
    input.val(parseInt(attachment.id, 10) || 0).attr('data-media-url', url).trigger('change');
    wrapper.find('.vava-media-preview').empty().append($('<img>', { alt: '', src: url })).append($('<span>', { class: 'vava-media-file-name', text: title }));
    field.removeClass('is-uploading has-error').find('.vava-media-error').remove();
    updateHeroPreviewImage(url);
  }

  function clearMedia(wrapper) {
    var fallback = wrapper.attr('data-fallback-url') || '';
    var field = wrapper.find('.vava-media-field').first();
    wrapper.find('[data-paths-media-id]').val('0').attr('data-media-url', fallback).trigger('change');
    wrapper.find('.vava-media-preview').html(emptyMediaPreview());
    field.removeClass('is-uploading has-error').find('.vava-media-error').remove();
    field.find('.vava-upload-progress span').css('width', '0%');
    updateHeroPreviewImage(fallback);
  }

  function mediaError(wrapper, message) {
    var field = wrapper.find('.vava-media-field').first();
    field.removeClass('is-uploading').addClass('has-error').find('.vava-media-error').remove();
    field.append($('<div>', { class: 'vava-media-error', text: message }));
    field.find('.vava-upload-progress span').css('width', '0%');
  }

  function uploadImage(wrapper, file) {
    if (!file || String(file.type).indexOf('image/') !== 0) {
      mediaError(wrapper, translated('يرجى اختيار ملف صورة صالح.', 'Please choose a valid image file.'));
      return;
    }
    var maxMb = config.maxImageMb || 20;
    if (file.size > maxMb * 1024 * 1024) {
      mediaError(wrapper, translated('حجم الصورة يتجاوز الحد المسموح.', 'The image exceeds the allowed size.'));
      return;
    }
    if (!config.uploadUrl || !config.uploadNonce) {
      mediaError(wrapper, translated('تعذر تهيئة رفع الوسائط.', 'Media upload could not be initialized.'));
      return;
    }

    var formData = new FormData();
    formData.append('name', file.name);
    formData.append('action', 'upload-attachment');
    formData.append('_wpnonce', config.uploadNonce);
    formData.append('post_id', config.postId || 0);
    formData.append('async-upload', file, file.name);

    var xhr = new XMLHttpRequest();
    var field = wrapper.find('.vava-media-field').first();
    field.addClass('is-uploading').removeClass('has-error').find('.vava-media-error').remove();
    field.find('.vava-upload-progress span').css('width', '3%');
    xhr.open('POST', config.uploadUrl, true);
    xhr.upload.onprogress = function (event) {
      if (event.lengthComputable) field.find('.vava-upload-progress span').css('width', Math.max(3, Math.round((event.loaded / event.total) * 100)) + '%');
    };
    xhr.onload = function () {
      var response;
      try {
        response = JSON.parse(xhr.responseText);
      } catch (error) {
        mediaError(wrapper, translated('استجابة رفع الوسائط غير صالحة.', 'Invalid media upload response.'));
        return;
      }
      if (xhr.status >= 200 && xhr.status < 300 && response && response.success && response.data) {
        setMedia(wrapper, response.data);
        field.find('.vava-upload-progress span').css('width', '100%');
        window.setTimeout(function () { field.find('.vava-upload-progress span').css('width', '0%'); }, 500);
      } else {
        var message = response && response.data && response.data.message ? $('<div>').html(response.data.message).text() : translated('فشل رفع الصورة.', 'Image upload failed.');
        mediaError(wrapper, message);
      }
    };
    xhr.onerror = function () { mediaError(wrapper, translated('حدث خطأ أثناء رفع الصورة.', 'An error occurred while uploading the image.')); };
    xhr.send(formData);
  }

  function openMediaFrame(wrapper) {
    var frame = wp.media({
      title: translated('اختيار صورة', 'Choose image'),
      button: { text: translated('استخدام الصورة', 'Use image') },
      multiple: false,
      library: { type: 'image' }
    });
    frame.on('select', function () {
      setMedia(wrapper, frame.state().get('selection').first().toJSON());
    });
    frame.open();
  }

  $('#vava_homepage_settings').on('click.vavaPathsLanguage', '.vava-language-switch [data-language]', function () {
    switchLanguage(String($(this).attr('data-language') || 'ar'));
  });

  root.on('click', '[data-section]', function () {
    switchSection(String($(this).attr('data-section') || 'hero'));
  });

  $('#vava_homepage_settings').on('click.vavaPathsSave', '[data-vava-submit]', function (event) {
    event.preventDefault();
    var button = $(this);
    button.addClass('is-saving').prop('disabled', true).find('span').text(translated('جارٍ التحديث…', 'Updating…'));
    if ($('#publish').length) $('#publish').trigger('click');
    else $('#post').trigger('submit');
  });

  root.on('click', '[data-paths-accordion-toggle]', function () {
    var item = $(this).closest('[data-paths-accordion-item]');
    var list = item.parent();
    var shouldOpen = !item.hasClass('is-open');
    list.children('[data-paths-accordion-item]').removeClass('is-open').find('[data-paths-accordion-toggle]').attr('aria-expanded', 'false');
    if (shouldOpen) item.addClass('is-open').find('[data-paths-accordion-toggle]').attr('aria-expanded', 'true');
  });

  root.on('input change', '.vava-paths-field', function () {
    var field = $(this);
    updatePreview(field);
    syncSharedFunctionalField(field);
  });

  root.on('click', '.vava-media-select', function (event) {
    event.preventDefault();
    openMediaFrame(mediaWrapper($(this)));
  });
  root.on('click', '.vava-media-remove', function (event) {
    event.preventDefault();
    clearMedia(mediaWrapper($(this)));
  });
  root.on('click', '.vava-media-dropzone', function (event) {
    if ($(event.target).closest('.vava-media-actions, button, a, input').length) return;
    openMediaFrame(mediaWrapper($(this)));
  });
  root.on('keydown', '.vava-media-dropzone', function (event) {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      openMediaFrame(mediaWrapper($(this)));
    }
  });
  root.on('dragenter dragover', '.vava-media-dropzone', function (event) {
    event.preventDefault();
    event.stopPropagation();
    $(this).addClass('is-dragover');
  });
  root.on('dragleave dragend', '.vava-media-dropzone', function (event) {
    event.preventDefault();
    event.stopPropagation();
    $(this).removeClass('is-dragover');
  });
  root.on('drop', '.vava-media-dropzone', function (event) {
    event.preventDefault();
    event.stopPropagation();
    $(this).removeClass('is-dragover');
    var files = event.originalEvent && event.originalEvent.dataTransfer ? event.originalEvent.dataTransfer.files : null;
    if (files && files.length) uploadImage(mediaWrapper($(this)), files[0]);
  });

  var resizeTimer = 0;
  $(window).on('resize.vavaPathsPreview', function () {
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(fitCurrentPreview, 80);
  });

  $(function () {
    setupPageIdentity();
    lockPostbox();
    setupSettingsHeader();
    setupSidebar();
    initSortable();

    var savedLanguage = 'ar';
    var savedSection = 'hero';
    try {
      savedLanguage = localStorage.getItem('vavaPathsLanguage') || 'ar';
      savedSection = localStorage.getItem('vavaPathsSection') || 'hero';
    } catch (error) {}
    switchLanguage(savedLanguage);
    switchSection(savedSection);
    updateSidebarPreview();
  });
})(jQuery);
