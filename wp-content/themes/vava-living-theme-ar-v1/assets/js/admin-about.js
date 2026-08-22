(function ($) {
  'use strict';

  var root = $('.vava-about-admin');
  if (!root.length) return;
  var config = window.vavaAboutAdmin || {};

  $('body').addClass('vava-homepage-classic vava-about-classic');

  function currentLanguage() {
    return String(root.attr('data-active-language') || 'ar') === 'en' ? 'en' : 'ar';
  }

  function translated(arabic, english, language) {
    return language === 'en' ? english : arabic;
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

    var settingsTitle = root.attr('data-settings-title-' + language);
    if (settingsTitle) $('#vava_homepage_settings .postbox-header h2').first().text(settingsTitle);
  }

  function previewFor(section, language) {
    return $('.vava-live-preview[data-about-preview-panel][data-preview-section="' + section + '"][data-preview-language="' + language + '"]').first();
  }

  function fitPreview(preview) {
    if (!preview || !preview.length || preview.attr('hidden')) return;
    var viewport = preview.find('.vava-preview-viewport').first();
    var stage = preview.find('.vava-preview-stage').first();
    var canvas = preview.find('.vava-preview-canvas').first();
    if (!viewport.length || !stage.length || !canvas.length) return;

    window.requestAnimationFrame(function () {
      var designWidth = parseFloat(canvas.attr('data-preview-design-width')) || 900;
      var availableWidth = Math.max(320, viewport.innerWidth() - 24);
      var scale = Math.min(1, availableWidth / designWidth);
      canvas.css({ width: designWidth + 'px', transform: 'scale(' + scale + ')' });
      window.requestAnimationFrame(function () {
        var designHeight = Math.max(1, canvas.get(0).scrollHeight || 0);
        stage.css({
          width: Math.round(designWidth * scale) + 'px',
          height: Math.round(designHeight * scale) + 'px'
        });
      });
    });
  }

  function fitCurrent() {
    var section = String(root.attr('data-active-section') || 'hero');
    fitPreview(previewFor(section, currentLanguage()));
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
    box.off('.vavaAboutLock').on('click.vavaAboutLock mousedown.vavaAboutLock', '.postbox-header', function (event) {
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
    $('.vava-live-preview[data-about-preview-panel]').attr('hidden', true).removeClass('is-sidebar-active');
    var active = previewFor(section, language).removeAttr('hidden').addClass('is-sidebar-active');
    fitPreview(active);
  }

  function setupSidebar() {
    var side = $('#side-sortables');
    var previews = root.find('.vava-live-preview[data-about-preview-panel]');
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
      var observer = new window.ResizeObserver(function () { fitCurrent(); });
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
    try { localStorage.setItem('vavaAboutSection', section); } catch (e) {}
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
    try { localStorage.setItem('vavaAboutLanguage', language); } catch (e) {}
    updateSidebarPreview();
  }

  root.on('click', '[data-section]', function () {
    activateSection(String($(this).data('section') || 'hero'));
  });

  $('#vava_homepage_settings').on('click.vavaAboutLanguage', '.vava-language-switch button', function () {
    activateLanguage(String($(this).data('language') || 'ar'));
  });

  $('#vava_homepage_settings').on('click', '[data-vava-submit]', function (event) {
    event.preventDefault();
    var button = $(this);
    var savingText = translated('جارٍ الحفظ...', 'Saving...', currentLanguage());
    button.addClass('is-saving').prop('disabled', true).find('span').text(savingText);
    var publish = $('#publish');
    if (publish.length) publish.trigger('click');
    else $('#post').trigger('submit');
  });

  root.on('input change', '[data-about-preview]', function () {
    var input = $(this);
    var pane = input.closest('[data-language-pane]');
    var section = input.closest('[data-section-panel]').attr('data-section-panel') || 'hero';
    var language = pane.attr('data-language-pane') || 'ar';
    var key = input.attr('data-about-preview');
    var target = previewFor(section, language).find('[data-preview-output="' + key + '"]');
    if (input.hasClass('vava-richtext-source')) target.html(input.val() || '');
    else target.text(input.val() || '');
    fitPreview(previewFor(section, language));
  });

  function renumberRepeater(repeater) {
    var isFixed = repeater.is('[data-fixed-count]');
    repeater.find('[data-about-repeat-item]').each(function (index) {
      var item = $(this);
      item.find('[data-about-item-number]').text(isFixed ? String(index + 1).padStart(2, '0') : index + 1);
      item.find('[name]').each(function () {
        this.name = this.name.replace(/\[[0-9]+\]/, '[' + index + ']');
      });
    });
  }

  function syncAccordionTitle(item) {
    var value = item.find('[name$="[title]"]').first().val() || '';
    item.find('[data-about-accordion-title]').first().text(value);
  }

  function setAccordionState(item, open) {
    item.toggleClass('is-open', open);
    item.find('[data-about-accordion-toggle]').first().attr('aria-expanded', open ? 'true' : 'false');
    item.find('[data-about-accordion-body]').first().prop('hidden', !open);
  }

  function setupFixedAccordions() {
    root.find('.vava-about-fixed-cards').each(function () {
      var repeater = $(this);
      var items = repeater.find('[data-about-accordion-item]');
      items.each(function (index) {
        syncAccordionTitle($(this));
        setAccordionState($(this), index === 0);
      });
    });
  }

  function rebuildRepeater(repeater) {
    var pane = repeater.closest('[data-language-pane]');
    var section = repeater.closest('[data-section-panel]').attr('data-section-panel') || 'story';
    var language = pane.attr('data-language-pane') || 'ar';
    var outputName = repeater.attr('data-preview-list');
    var kind = repeater.attr('data-kind');
    var preview = previewFor(section, language);
    var output = preview.find('[data-preview-repeat-output="' + outputName + '"]');

    if (output.length) {
      output.empty();
      repeater.find('[data-about-repeat-item]').each(function () {
        var item = $(this);
        var text = item.find('[name$="[text]"]').val() || '';
        if (kind === 'cards') {
          var title = item.find('[name$="[title]"]').val() || '';
          if (!title && !text) return;
          $('<article/>', { class: outputName === 'features' ? 'vava-about-preview-feature-card' : (outputName === 'vision' ? 'vava-about-preview-vision-step' : '') }).append($('<strong/>').text(title), $('<div/>').html(text)).appendTo(output);
        } else if (kind === 'phrases') {
          if (!text) return;
          $('<b/>').text(text).appendTo(output);
        } else {
          var style = item.find('[name$="[style]"]').val() || 'normal';
          if (!text) return;
          $('<p/>').addClass(style).text(text).appendTo(output);
        }
      });
      fitPreview(preview);
    }

  }

  function fixedCardSourceIndex(item) {
    var name = String(item.find('[name]').first().attr('name') || '');
    var match = name.match(/\[(\d+)\]/);
    return match ? String(match[1]) : '';
  }

  function syncSharedFixedCardOrder(sourceRepeater) {
    var outputName = String(sourceRepeater.attr('data-preview-list') || '');
    if (outputName !== 'features' && outputName !== 'vision') return;
    var sourcePane = sourceRepeater.closest('[data-language-pane]');
    var language = String(sourcePane.attr('data-language-pane') || 'ar');
    var targetLanguage = language === 'en' ? 'ar' : 'en';
    var section = String(sourceRepeater.closest('[data-section-panel]').attr('data-section-panel') || '');
    var targetRepeater = root.find('[data-section-panel="' + section + '"] [data-language-pane="' + targetLanguage + '"] [data-about-repeater][data-preview-list="' + outputName + '"]').first();
    if (!targetRepeater.length) return;

    var order = [];
    sourceRepeater.find('[data-about-repeat-list] > [data-about-repeat-item]').each(function () {
      order.push(fixedCardSourceIndex($(this)));
    });
    var targetItems = {};
    targetRepeater.find('[data-about-repeat-list] > [data-about-repeat-item]').each(function () {
      targetItems[fixedCardSourceIndex($(this))] = this;
    });
    var targetList = targetRepeater.find('[data-about-repeat-list]').first();
    $.each(order, function (_, index) {
      if (targetItems[index]) targetList.append(targetItems[index]);
    });
    renumberRepeater(targetRepeater);
    targetRepeater.find('[data-about-repeat-item]').each(function () { syncAccordionTitle($(this)); });
    rebuildRepeater(targetRepeater);
  }

  root.find('[data-about-repeat-list]').sortable({
    handle: '.vava-repeater-handle',
    placeholder: 'vava-repeater-placeholder',
    update: function () {
      var repeater = $(this).closest('[data-about-repeater]');
      syncSharedFixedCardOrder(repeater);
      renumberRepeater(repeater);
      repeater.find('[data-about-repeat-item]').each(function () { syncAccordionTitle($(this)); });
      rebuildRepeater(repeater);
    }
  });

  root.on('click', '[data-about-accordion-toggle]', function () {
    var item = $(this).closest('[data-about-accordion-item]');
    var repeater = item.closest('.vava-about-fixed-cards');
    var shouldOpen = !item.hasClass('is-open');
    repeater.find('[data-about-accordion-item]').each(function () { setAccordionState($(this), false); });
    if (shouldOpen) setAccordionState(item, true);
  });

  root.on('input change', '.vava-about-fixed-cards [name$="[title]"]', function () {
    syncAccordionTitle($(this).closest('[data-about-accordion-item]'));
  });

  root.on('input change', '[data-about-repeater] input, [data-about-repeater] textarea, [data-about-repeater] select', function () {
    rebuildRepeater($(this).closest('[data-about-repeater]'));
  });

  function updateSectionImage(section, url) {
    var target = $('.vava-live-preview[data-about-preview-panel][data-preview-section="' + section + '"] [data-about-preview-image="' + section + '"]');
    url = String(url || '');
    if (url) {
      target.css('background-image', 'url(' + JSON.stringify(url) + ')').removeClass('is-empty');
    } else {
      target.css('background-image', 'none').addClass('is-empty');
    }
  }

  function emptyMediaPreview() {
    var language = currentLanguage();
    return '<div class="vava-media-empty"><svg viewBox="0 0 48 48"><rect x="5" y="7" width="38" height="34" rx="5"/><circle cx="17" cy="18" r="4"/><path d="m9 36 11-11 8 8 5-5 7 8"/></svg><strong>' + translated('اسحب الصورة وأفلتها هنا', 'Drag and drop the image here', language) + '</strong><span>' + translated('أو اضغط للاختيار من مكتبة الوسائط', 'Or click to choose from the media library', language) + '</span></div>';
  }

  function mediaWrapper(element) {
    return element.closest('[data-about-media-field]');
  }

  function setAboutMedia(wrapper, attachment) {
    var field = wrapper.find('.vava-media-field').first();
    var input = wrapper.find('[data-about-media-id]').first();
    var url = attachment.sizes && attachment.sizes.medium_large ? attachment.sizes.medium_large.url : (attachment.url || '');
    var title = attachment.title || attachment.filename || '';
    input.val(parseInt(attachment.id, 10) || 0).attr('data-media-url', url).trigger('change');
    wrapper.find('.vava-media-preview').empty().append($('<img>', { alt: '', src: url })).append($('<span>', { class: 'vava-media-file-name', text: title }));
    field.removeClass('is-uploading has-error').find('.vava-media-error').remove();
    var section = wrapper.closest('[data-section-panel]').attr('data-section-panel') || 'hero';
    updateSectionImage(section, url);
    fitCurrent();
  }

  function clearAboutMedia(wrapper) {
    var fallback = wrapper.attr('data-fallback-url') || '';
    var field = wrapper.find('.vava-media-field').first();
    wrapper.find('[data-about-media-id]').val('0').attr('data-media-url', fallback).trigger('change');
    wrapper.find('.vava-media-preview').html(emptyMediaPreview());
    field.removeClass('is-uploading has-error').find('.vava-media-error').remove();
    field.find('.vava-upload-progress span').css('width', '0%');
    var section = wrapper.closest('[data-section-panel]').attr('data-section-panel') || 'hero';
    updateSectionImage(section, fallback);
    fitCurrent();
  }

  function aboutMediaError(wrapper, message) {
    var field = wrapper.find('.vava-media-field').first();
    field.removeClass('is-uploading').addClass('has-error').find('.vava-media-error').remove();
    field.append($('<div>', { class: 'vava-media-error', text: message }));
    field.find('.vava-upload-progress span').css('width', '0%');
  }

  function uploadAboutImage(wrapper, file) {
    if (!file || String(file.type).indexOf('image/') !== 0) {
      aboutMediaError(wrapper, translated('يرجى اختيار ملف صورة صالح.', 'Please choose a valid image file.', currentLanguage()));
      return;
    }
    var maxMb = config.maxImageMb || 20;
    if (file.size > maxMb * 1024 * 1024) {
      aboutMediaError(wrapper, translated('حجم الصورة يتجاوز الحد المسموح.', 'The image exceeds the allowed size.', currentLanguage()));
      return;
    }
    if (!config.uploadUrl || !config.uploadNonce) {
      aboutMediaError(wrapper, translated('تعذر تهيئة رفع الوسائط.', 'Media upload could not be initialized.', currentLanguage()));
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
      try { response = JSON.parse(xhr.responseText); } catch (error) { aboutMediaError(wrapper, translated('استجابة رفع الوسائط غير صالحة.', 'Invalid media upload response.', currentLanguage())); return; }
      if (xhr.status >= 200 && xhr.status < 300 && response && response.success && response.data) {
        setAboutMedia(wrapper, response.data);
        field.find('.vava-upload-progress span').css('width', '100%');
        window.setTimeout(function () { field.find('.vava-upload-progress span').css('width', '0%'); }, 500);
      } else {
        var message = response && response.data && response.data.message ? $('<div>').html(response.data.message).text() : translated('فشل رفع الصورة.', 'Image upload failed.', currentLanguage());
        aboutMediaError(wrapper, message);
      }
    };
    xhr.onerror = function () { aboutMediaError(wrapper, translated('حدث خطأ أثناء رفع الصورة.', 'An error occurred while uploading the image.', currentLanguage())); };
    xhr.send(formData);
  }

  function openAboutMediaFrame(wrapper) {
    var language = currentLanguage();
    var frame = wp.media({
      title: translated('اختيار صورة', 'Choose image', language),
      button: { text: translated('استخدام الصورة', 'Use image', language) },
      library: { type: 'image' },
      multiple: false
    });
    frame.on('select', function () { setAboutMedia(wrapper, frame.state().get('selection').first().toJSON()); });
    frame.open();
  }

  root.on('click', '.vava-media-select', function (event) { event.preventDefault(); openAboutMediaFrame(mediaWrapper($(this))); });
  root.on('click', '.vava-media-remove', function (event) { event.preventDefault(); clearAboutMedia(mediaWrapper($(this))); });
  root.on('click', '.vava-media-dropzone', function (event) {
    if ($(event.target).closest('.vava-media-actions, button, a, input').length) return;
    openAboutMediaFrame(mediaWrapper($(this)));
  });
  root.on('keydown', '.vava-media-dropzone', function (event) {
    if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); openAboutMediaFrame(mediaWrapper($(this))); }
  });
  root.on('dragenter dragover', '.vava-media-dropzone', function (event) { event.preventDefault(); event.stopPropagation(); $(this).addClass('is-dragover'); });
  root.on('dragleave dragend', '.vava-media-dropzone', function (event) { event.preventDefault(); event.stopPropagation(); $(this).removeClass('is-dragover'); });
  root.on('drop', '.vava-media-dropzone', function (event) {
    event.preventDefault(); event.stopPropagation(); $(this).removeClass('is-dragover');
    var files = event.originalEvent && event.originalEvent.dataTransfer ? event.originalEvent.dataTransfer.files : null;
    if (files && files.length) uploadAboutImage(mediaWrapper($(this)), files[0]);
  });

  function toggleLinkField(field) {
    var manual = field.find('[data-about-link-type]').val() === 'manual';
    field.find('[data-about-page-choice]').toggle(!manual);
    field.find('[data-about-manual-url]').toggle(manual);
  }

  function syncSharedAboutLink(source) {
    var key = String(source.attr('data-vava-shared-setting') || '');
    if (!key) return;
    var type = source.find('[data-about-link-type]').val() || 'manual';
    var page = source.find('[data-about-page-choice]').val() || '0';
    var manual = source.find('[data-about-manual-url]').val() || '';
    root.find('[data-vava-shared-setting="' + key + '"]').not(source).each(function () {
      var target = $(this);
      target.find('[data-about-link-type]').val(type);
      target.find('[data-about-page-choice]').val(page);
      target.find('[data-about-manual-url]').val(manual);
      toggleLinkField(target);
    });
  }

  root.find('.vava-about-link-field').each(function () { toggleLinkField($(this)); });
  root.on('change input', '[data-about-link-type], [data-about-page-choice], [data-about-manual-url]', function () {
    var field = $(this).closest('.vava-about-link-field');
    toggleLinkField(field);
    syncSharedAboutLink(field);
  });

  var resizeTimer = 0;
  $(window).on('resize.vavaAboutPreview', function () {
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(fitCurrent, 80);
  });

  var savedSection = 'hero';
  var savedLanguage = 'ar';
  try {
    savedSection = localStorage.getItem('vavaAboutSection') || 'hero';
    savedLanguage = localStorage.getItem('vavaAboutLanguage') || 'ar';
  } catch (e) {}

  setupPageIdentity();
  lockPostbox();
  setupSettingsHeader();
  setupSidebar();
  setupFixedAccordions();
  root.find('[data-about-repeater]').each(function () { rebuildRepeater($(this)); });
  activateSection(savedSection);
  activateLanguage(savedLanguage);
  updateSidebarPreview();
})(jQuery);
