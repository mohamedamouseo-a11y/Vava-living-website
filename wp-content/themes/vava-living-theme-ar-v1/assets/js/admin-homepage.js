(function ($) {
  'use strict';

  var root = $('.vava-homepage-admin');
  if (!root.length) return;

  var config = window.vavaHomepageAdmin || {};
  var socialPlatforms = config.socialPlatforms || {};
  var menuPreviews = config.menus || {};
  var journalPreviewTimers = {};
  $('body').addClass('vava-homepage-classic');

  function currentLanguage() {
    return String(root.attr('data-active-language') || 'ar') === 'en' ? 'en' : 'ar';
  }

  function translated(arabic, english, language) {
    return (language || currentLanguage()) === 'en' ? english : arabic;
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

    root.find('[data-vava-i18n-title-ar][data-vava-i18n-title-en]').each(function () {
      var element = $(this);
      var title = element.attr('data-vava-i18n-title-' + language);
      if (typeof title !== 'undefined') element.attr('title', title);
    });

    var settingsTitle = root.attr('data-settings-title-' + language);
    if (settingsTitle) $('#vava_homepage_settings .postbox-header h2').first().text(settingsTitle);
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
    updatePageTitleLanguage(root.attr('data-active-language') || 'ar');
    if (!arabicTitle.length || !nativeTitle.length) return;

    nativeTitle.attr('aria-hidden', 'true').val(arabicTitle.val() || '');
    arabicTitle.on('input change', function () {
      nativeTitle.val($(this).val() || '').trigger('input');
    });
  }

  function fitLivePreview(preview) {
    if (!preview || !preview.length || preview.attr('hidden')) return;
    var viewport = preview.find('.vava-preview-viewport').first();
    var stage = preview.find('.vava-preview-stage').first();
    var canvas = preview.find('.vava-preview-canvas').first();
    if (!viewport.length || !stage.length || !canvas.length) return;

    window.requestAnimationFrame(function () {
      var designWidth = parseFloat(canvas.attr('data-preview-design-width')) || 900;
      var availableWidth = Math.max(320, viewport.innerWidth() - 24);
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

  function fitActivePreview() {
    var section = String(root.attr('data-active-section') || 'hero');
    var language = String(root.attr('data-active-language') || 'ar');
    fitLivePreview(previewFor(section, language));
  }

  var previewResizeTimer = 0;
  $(window).on('resize.vavaHomepagePreview', function () {
    window.clearTimeout(previewResizeTimer);
    previewResizeTimer = window.setTimeout(fitActivePreview, 80);
  });

  function lockHomepagePostbox() {
    var box = $('#vava_homepage_settings');
    box.removeClass('closed').addClass('vava-homepage-postbox-is-locked');
    box.find('.handle-actions, .handlediv').remove();
    box.find('.postbox-header .hndle')
      .removeClass('hndle ui-sortable-handle')
      .attr('aria-disabled', 'true');
    box.off('.vavaHomepageLock').on('click.vavaHomepageLock mousedown.vavaHomepageLock', '.postbox-header', function (event) {
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
    if (!header.length || !button.length || !languageSwitch.length) return;

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
    var language = String(root.attr('data-active-language') || 'ar');
    $('.vava-live-preview').attr('hidden', true).removeClass('is-sidebar-active');
    var activePreview = previewFor(section, language).removeAttr('hidden').addClass('is-sidebar-active');
    fitLivePreview(activePreview);
  }

  function setupHomepageSidebar() {
    var side = $('#side-sortables');
    var previews = root.find('.vava-live-preview');
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
      var observer = new window.ResizeObserver(function () { fitActivePreview(); });
      observer.observe(dock.get(0));
    }
  }

  function placeInternalHeaderMenuField() {
    var field = root.find('[data-vava-internal-header-menu-field]').first();
    if (!field.length) return;

    var language = currentLanguage();
    var pane = root.find('.vava-section-panel[data-section-panel="hero"] .vava-language-pane[data-language-pane="' + language + '"]').first();
    var grid = pane.find('.vava-editor-controls > .vava-fields-grid').first();
    if (!grid.length) return;

    field.detach().prependTo(grid);
  }

  function activateSection(section) {
    if (!root.find('.vava-section-tab[data-section="' + section + '"]').length) {
      section = 'hero';
    }
    root.attr('data-active-section', section);
    root.find('.vava-section-tab').removeClass('is-active').attr('aria-selected', 'false');
    root.find('.vava-section-tab[data-section="' + section + '"]').addClass('is-active').attr('aria-selected', 'true');
    root.find('.vava-section-panel').removeClass('is-active');
    root.find('.vava-section-panel[data-section-panel="' + section + '"]').addClass('is-active');
    try { localStorage.setItem('vavaHomepageSection', section); } catch (e) {}
    if (section === 'hero') placeInternalHeaderMenuField();
    updateSidebarPreview();
  }

  function activateLanguage(language) {
    language = language === 'en' ? 'en' : 'ar';
    root.attr('data-active-language', language);
    root.find('[data-vava-active-language-input]').val(language);
    $('#vava_homepage_settings .vava-language-switch button').removeClass('is-active');
    $('#vava_homepage_settings .vava-language-switch button[data-language="' + language + '"]').addClass('is-active');
    root.find('.vava-language-pane').removeClass('is-active');
    root.find('.vava-language-pane[data-language-pane="' + language + '"]').addClass('is-active');
    updatePageTitleLanguage(language);
    applyInterfaceLanguage(language);
    try { localStorage.setItem('vavaHomepageLanguage', language); } catch (e) {}
    placeInternalHeaderMenuField();
    refreshAllLivePreviews();
    updateSidebarPreview();
  }

  root.on('click', '.vava-section-tab', function () {
    activateSection(String($(this).data('section') || 'hero'));
  });

  $('#vava_homepage_settings').on('click.vavaHomepageLanguage', '.vava-language-switch button', function () {
    activateLanguage(String($(this).data('language') || 'ar'));
  });

  function syncSharedLinkSelector(source) {
    var key = String(source.attr('data-vava-shared-setting') || '');
    if (!key) return;
    var type = source.find('[data-link-type-control]').val() || 'manual';
    var page = source.find('[data-link-pane="page"] select').val() || '0';
    var manual = source.find('[data-link-pane="manual"] input').val() || '';
    root.find('[data-vava-shared-setting="' + key + '"]').not(source).each(function () {
      var target = $(this);
      target.find('[data-link-type-control]').val(type);
      target.find('[data-link-pane="page"] select').val(page);
      target.find('[data-link-pane="manual"] input').val(manual);
      refreshLinkSelector(target);
      refreshButtonHref(target);
    });
  }

  function syncSharedFooterMenu(source) {
    var key = String(source.attr('data-vava-shared-setting') || '');
    if (!key) return;
    root.find('[data-vava-shared-setting="' + key + '"]').not(source).val(source.val());
  }

  function syncSharedJournal(source) {
    var container = source.closest('[data-vava-shared-setting="journal-query"]');
    if (!container.length) return;
    var mode = container.find('[data-journal-mode]').val() || 'latest';
    var latest = container.find('[data-journal-latest]').val() || '0';
    var random = container.find('[data-journal-random]').val() || [];
    root.find('[data-vava-shared-setting="journal-query"]').not(container).each(function () {
      var target = $(this);
      target.find('[data-journal-mode]').val(mode);
      target.find('[data-journal-latest]').val(latest);
      target.find('[data-journal-random]').val(random);
      refreshJournalQuery(target);
    });
  }

  function refreshLinkSelector(selector) {
    var type = selector.find('[data-link-type-control]').val() || 'manual';
    selector.attr('data-link-type', type);
    selector.find('[data-link-pane]').attr('hidden', true);
    selector.find('[data-link-pane="' + type + '"]').removeAttr('hidden');
  }

  root.find('[data-link-selector]').each(function () {
    refreshLinkSelector($(this));
  });
  root.on('change input', '[data-link-type-control], [data-link-pane] select, [data-link-pane] input', function () {
    var selector = $(this).closest('[data-link-selector]');
    refreshLinkSelector(selector);
    refreshButtonHref(selector);
    syncSharedLinkSelector(selector);
  });

  function refreshHeroMediaFields(forcedType) {
    var controller = root.find('[data-hero-media-controller]').first();
    var switcher = controller.find('.vava-media-type-switch').first();
    var checked = switcher.find('input[name="_vava_home_hero_media_type"]:checked');
    var type = forcedType || checked.val() || switcher.find('input').first().val() || 'video';
    if (type !== 'image' && type !== 'video') type = 'video';
    switcher.find('input[value="' + type + '"]').prop('checked', true);
    switcher.attr('data-selected-media', type);
    controller.find('[data-hero-media]').each(function () {
      var field = $(this);
      var visible = String(field.attr('data-hero-media') || '') === type;
      field.prop('hidden', !visible).attr('aria-hidden', visible ? 'false' : 'true').toggleClass('is-media-hidden', !visible);
      this.style.setProperty('display', visible ? 'block' : 'none', 'important');
    });
    controller.find('.vava-hero-media-grid').attr('data-active-media', type);
    $('.vava-live-preview[data-preview-section="hero"] .vava-preview-hero-media').attr('data-preview-hero-type', type);
  }
  root.on('click', '.vava-media-type-switch label', function (event) {
    var input = $(this).find('input[name="_vava_home_hero_media_type"]');
    if (!input.length) return;
    if (!$(event.target).is('input')) {
      event.preventDefault();
      input.prop('checked', true).trigger('change');
    }
  });
  root.on('change', 'input[name="_vava_home_hero_media_type"]', function () { refreshHeroMediaFields($(this).val()); });
  refreshHeroMediaFields();

  function refreshJournalQuery(container) {
    var mode = container.find('[data-journal-mode]').val() || 'latest';
    container.attr('data-journal-mode', mode);
    container.find('[data-journal-pane]').attr('hidden', true);
    container.find('[data-journal-pane="' + mode + '"]').removeAttr('hidden');
  }

  root.find('[data-journal-query]').each(function () {
    refreshJournalQuery($(this));
  });
  root.on('change', '[data-journal-mode], [data-journal-latest], [data-journal-random]', function () {
    var container = $(this).closest('[data-journal-query]');
    refreshJournalQuery(container);
    syncSharedJournal($(this));
    scheduleJournalPreview(container);
  });

  function updateDocumentNumber(value, source) {
    root.find('[data-vava-document-number-source]').val(value);
    $('.vava-live-preview [data-preview-output="_vava_home_footer_document_number"]').text(value);
    root.find('[data-vava-document-number]').each(function () {
      if (this !== source) $(this).val(value);
    });
  }
  root.on('input change', '[data-vava-document-number]', function () {
    updateDocumentNumber($(this).val(), this);
  });

  function escapeRegExp(value) {
    return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  }

  function normalizeSocialValue(platform, value) {
    value = String(value || '').trim();
    if (platform === 'email') return value.replace(/^(?:mailto:|https?:\/\/)+/i, '').replace(/^\/+/, '');
    return value;
  }

  function updateSocialIcon(item) {
    var platform = String(item.find('[data-social-platform]').val() || 'instagram');
    var data = socialPlatforms[platform] || {};
    var input = item.find('[data-social-preview-field="url"]');
    item.find('[data-social-icon]').html(data.icon || '');
    input.attr('type', platform === 'email' ? 'email' : 'text');
    input.attr('placeholder', platform === 'email' ? 'name@example.com' : 'https://');
    if (platform === 'email') input.val(normalizeSocialValue(platform, input.val()));
  }

  function reindexRepeater(repeater) {
    var base = String(repeater.attr('data-name-base') || '');
    var matcher = base ? new RegExp(escapeRegExp(base) + '\\[[^\\]]+\\]') : null;
    repeater.find('[data-repeater-list] > [data-repeater-item]').each(function (index) {
      var item = $(this);
      item.find('[data-repeater-number]').text(index + 1);
      if (String(repeater.attr('data-repeater-kind') || '') === 'testimonials') {
        var author = String(item.find('[data-testimonial-field="author"]').val() || '').trim();
        item.find('[data-testimonial-card-title]').text(author || translated('تجربة عميل', 'Customer testimonial', languageFor(item)));
      }
      if (matcher) {
        item.find('[name]').each(function () {
          var name = String($(this).attr('name') || '');
          if (name) $(this).attr('name', name.replace(matcher, base + '[' + index + ']'));
        });
      }
      if (repeater.attr('data-repeater-kind') === 'social') updateSocialIcon(item);
    });
  }

  function initRepeater(repeater) {
    var list = repeater.find('[data-repeater-list]').first();
    if ($.fn.sortable) {
      list.sortable({
        items: '> [data-repeater-item]',
        handle: '.vava-repeater-drag',
        axis: 'y',
        tolerance: 'pointer',
        placeholder: 'vava-repeater-placeholder',
        forcePlaceholderSize: true,
        update: function () { reindexRepeater(repeater); refreshRepeaterPreview(repeater); }
      });
    }
    reindexRepeater(repeater);
  }


  function setTestimonialExpanded(item, expanded, animate) {
    if (!item || !item.length) return;
    var collapsed = !expanded;
    var body = item.find('.vava-repeater-item-body').first();
    var button = item.find('.vava-repeater-toggle').first();
    item.toggleClass('is-collapsed', collapsed);
    if (animate) body.stop(true, true)[collapsed ? 'slideUp' : 'slideDown'](140);
    else body.toggle(expanded);
    var actionLabel = collapsed
      ? translated('فتح التجربة', 'Expand testimonial', languageFor(item))
      : translated('طي التجربة', 'Collapse testimonial', languageFor(item));
    button.attr('aria-expanded', collapsed ? 'false' : 'true').attr('aria-label', actionLabel).attr('title', actionLabel);
  }

  function initTestimonialAccordion(repeater) {
    var items = repeater.find('[data-repeater-list] > [data-repeater-item]');
    items.each(function (index) { setTestimonialExpanded($(this), index === 0, false); });
    if (items.length) activateTestimonialItem(items.first());
  }

  root.find('[data-repeater]').each(function () {
    var repeater = $(this);
    initRepeater(repeater);
    if (String(repeater.attr('data-repeater-kind') || '') === 'testimonials') initTestimonialAccordion(repeater);
  });

  root.on('click', '.vava-repeater-add', function () {
    var repeater = $(this).closest('[data-repeater]');
    var template = repeater.find('template[data-repeater-template]').get(0);
    var list = repeater.find('[data-repeater-list]').first();
    var index = list.children('[data-repeater-item]').length;
    if (!template) return;
    var html = String(template.innerHTML || '').replace(/__INDEX__/g, String(index));
    var item = $(html).filter('[data-repeater-item]').first();
    if (!item.length) item = $('<div>').html(html).find('[data-repeater-item]').first();
    list.append(item);
    if (String(repeater.attr('data-repeater-kind') || '') === 'testimonials') {
      list.children('[data-repeater-item]').each(function () { setTestimonialExpanded($(this), false, false); }).removeAttr('data-preview-active');
      item.attr('data-preview-active', '1');
      setTestimonialExpanded(item, true, false);
    }
    reindexRepeater(repeater);
    refreshRepeaterPreview(repeater);
    item.find('textarea, input, select').filter(':visible').first().trigger('focus');
  });

  root.on('click', '.vava-repeater-remove', function () {
    var repeater = $(this).closest('[data-repeater]');
    $(this).closest('[data-repeater-item]').remove();
    reindexRepeater(repeater);
    refreshRepeaterPreview(repeater);
  });

  root.on('click', '.vava-repeater-toggle', function () {
    var button = $(this);
    var item = button.closest('[data-repeater-item]');
    var repeater = item.closest('[data-repeater-kind="testimonials"]');
    var shouldExpand = item.hasClass('is-collapsed');
    if (shouldExpand && repeater.length) {
      repeater.find('[data-repeater-list] > [data-repeater-item]').not(item).each(function () {
        setTestimonialExpanded($(this), false, true);
      });
    }
    setTestimonialExpanded(item, shouldExpand, true);
  });


  root.closest('form').on('submit', function () {
    root.find('[data-repeater-kind="social"] [data-repeater-item]').each(function () {
      var item = $(this);
      var platform = String(item.find('[data-social-platform]').val() || 'instagram');
      var input = item.find('[data-social-preview-field="url"]');
      input.val(normalizeSocialValue(platform, input.val()));
    });
  });

  root.on('change input', '[data-social-platform], [data-social-preview-field="url"]', function () {
    var item = $(this).closest('[data-repeater-item]');
    updateSocialIcon(item);
    refreshSocialPreview();
  });

  function activateTestimonialItem(item) {
    if (!item || !item.length) return;
    var repeater = item.closest('[data-repeater-kind="testimonials"]');
    repeater.find('[data-repeater-list] > [data-repeater-item]').removeAttr('data-preview-active');
    item.attr('data-preview-active', '1');
  }

  root.on('focusin click', '[data-repeater-kind="testimonials"] [data-repeater-item]', function (event) {
    if ($(event.target).closest('.vava-repeater-remove, .vava-repeater-drag').length) return;
    var item = $(this);
    activateTestimonialItem(item);
    refreshTestimonialsPreview(item.closest('[data-repeater]'));
  });

  root.on('input change', '[data-testimonial-field], .vava-testimonial-richtext', function () {
    var item = $(this).closest('[data-repeater-item]');
    if ($(this).is('[data-testimonial-field="author"]')) {
      var author = String($(this).val() || '').trim();
      item.find('[data-testimonial-card-title]').text(author || translated('تجربة عميل', 'Customer testimonial', languageFor(item)));
    }
    activateTestimonialItem(item);
    refreshTestimonialsPreview(item.closest('[data-repeater]'));
  });

  function previewFor(section, language) {
    return $('.vava-live-preview[data-preview-section="' + section + '"][data-preview-language="' + language + '"]');
  }

  function languageFor(element) {
    var pane = element.closest('[data-language-pane]');
    return String(pane.attr('data-language-pane') || root.attr('data-active-language') || 'ar');
  }

  function sectionFor(element) {
    return String(element.closest('[data-section-panel]').attr('data-section-panel') || root.attr('data-active-section') || 'hero');
  }

  function escapeHtml(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
  }

  function fieldValue(field) {
    if (field.is(':radio')) {
      var checked = root.find('input[name="' + field.attr('name') + '"]:checked');
      return checked.val() || '';
    }
    return field.val() == null ? '' : String(field.val());
  }

  function updateTextPreview(field) {
    if (field.is(':radio') && !field.is(':checked')) return;
    var key = String(field.attr('data-vava-preview-field') || '');
    if (!key) return;
    var section = sectionFor(field);
    var language = languageFor(field);
    var targets;
    if (field.closest('.vava-shared-fields').length || key === '_vava_home_footer_document_number') {
      targets = $('.vava-live-preview [data-preview-output="' + key + '"]');
    } else {
      targets = previewFor(section, language).find('[data-preview-output="' + key + '"]');
    }
    if (field.hasClass('vava-richtext-source')) targets.html(fieldValue(field));
    else targets.text(fieldValue(field));
    if (key === '_vava_home_hero_media_type') refreshHeroMediaFields();
  }

  root.on('input change', '[data-vava-preview-field]', function () {
    updateTextPreview($(this));
  });

  function refreshButtonHref(selector) {
    if (!selector || !selector.length) return;
    var section = String(selector.attr('data-preview-link-section') || sectionFor(selector));
    var language = languageFor(selector);
    var type = selector.find('[data-link-type-control]').val() || 'manual';
    var href = '';
    if (type === 'page') {
      href = selector.find('[data-link-pane="page"] select option:selected').attr('data-permalink') || '#';
    } else {
      href = selector.find('[data-link-pane="manual"] input').val() || '#';
    }
    previewFor(section, language).find('[data-preview-button="' + section + '"]').attr('href', href || '#');
  }

  function refreshMediaPreview(input) {
    var name = String(input.attr('name') || '');
    var url = String(input.attr('data-media-url') || input.attr('data-fallback-url') || '');
    if (name === '_vava_home_hero_image_id') {
      $('.vava-live-preview [data-preview-hero-image]').attr('src', url);
    } else if (name === '_vava_home_hero_poster_id') {
      $('.vava-live-preview [data-preview-hero-video]').attr('poster', url);
    } else if (name === '_vava_home_hero_video_id') {
      $('.vava-live-preview [data-preview-hero-video]').each(function () {
        var video = this;
        $(video).find('source').attr('src', url);
        try { video.load(); video.play().catch(function () {}); } catch (e) {}
      });
    } else if (name === '_vava_home_paths_image_id') {
      $('.vava-live-preview [data-preview-paths-image]').attr('src', url);
    } else if (name === '_vava_home_journal_image_id') {
      $('.vava-live-preview [data-preview-journal-image]').attr('src', url);
    } else if (name === '_vava_home_contact_image_id') {
      $('.vava-live-preview [data-preview-contact-image]').attr('src', url);
    }
  }

  root.on('change', '.vava-media-id', function () {
    refreshMediaPreview($(this));
  });

  function refreshTestimonialsPreview(repeater) {
    if (!repeater || !repeater.length) return;
    var language = languageFor(repeater);
    var preview = previewFor('testimonials', language);
    var items = repeater.find('[data-repeater-list] > [data-repeater-item]');
    var item = items.filter('[data-preview-active="1"]').first();
    if (!item.length) {
      item = items.first();
      if (item.length) item.attr('data-preview-active', '1');
    }
    var text = item.length ? (item.find('[data-testimonial-field="text"], .vava-testimonial-richtext').first().val() || '') : '';
    var author = item.length ? (item.find('[data-testimonial-field="author"]').val() || '') : '';
    preview.find('[data-preview-testimonial-text]').html(text || (language === 'en' ? 'Add a testimonial to preview it here.' : 'أضف تجربة لمعاينتها هنا.'));
    preview.find('[data-preview-testimonial-author]').text(author || (language === 'en' ? 'Customer name' : 'اسم العميل'));
    window.requestAnimationFrame(function () { fitLivePreview(preview); });
  }

  function refreshSocialPreview() {
    var html = '';
    var repeater = root.find('[data-repeater-kind="social"]');
    repeater.find('[data-repeater-list] > [data-repeater-item]').each(function () {
      var item = $(this);
      var platform = String(item.find('[data-social-platform]').val() || 'instagram');
      var data = socialPlatforms[platform] || {};
      var value = normalizeSocialValue(platform, item.find('[data-social-preview-field="url"]').val() || '');
      var url = platform === 'email' && value ? 'mailto:' + value : value;
      html += '<a href="' + escapeHtml(url || '#') + '" title="' + escapeHtml(data.label || platform) + '">' + (data.icon || '') + '</a>';
    });
    if (!html) html = '<span class="vava-preview-empty-social">—</span>';
    $('.vava-live-preview [data-preview-social-list]').html(html);
  }

  function refreshRepeaterPreview(repeater) {
    var kind = String(repeater.attr('data-repeater-kind') || '');
    if (kind === 'testimonials') refreshTestimonialsPreview(repeater);
    if (kind === 'social') refreshSocialPreview();
  }

  function renderInternalHeaderMenu(select) {
    if (!select || !select.length) return;
    var id = String(select.val() || '0');
    var items = menuPreviews[id] || [];

    $.each(['ar', 'en'], function (_, language) {
      var html = '';
      $.each(items, function (index, item) {
        if (parseInt(item.parent || 0, 10) !== 0) return;
        var label = item['label_' + language] || item.label || '';
        if (!label) return;
        html += '<a href="' + escapeHtml(item.url || '#') + '">' + escapeHtml(label) + '</a>';
      });
      if (!html) {
        html = '<span class="vava-fe-internal-menu-empty">' + (language === 'en' ? 'No menu selected' : 'لم يتم اختيار قائمة') + '</span>';
      }
      var preview = previewFor('hero', language);
      preview.find('[data-preview-internal-header-menu]').html(html);
      window.requestAnimationFrame(function () { fitLivePreview(preview); });
    });
  }

  root.on('change', '[data-internal-header-menu-select]', function () {
    renderInternalHeaderMenu($(this));
  });

  function renderFooterMenu(select) {
    var group = String(select.attr('data-footer-menu-group') || 'primary');
    var language = languageFor(select);
    var id = String(select.val() || '0');
    var items = menuPreviews[id] || [];
    var html = '';
    $.each(items, function (_, item) {
      var label = item['label_' + language] || item.label || '';
      html += '<a href="' + escapeHtml(item.url || '#') + '">' + escapeHtml(label) + '</a>';
    });
    if (!html) html = '<span class="vava-preview-muted">' + (language === 'en' ? 'No menu selected' : 'لم يتم اختيار قائمة') + '</span>';
    previewFor('footer', language).find('[data-preview-footer-menu="' + group + '"]').html(html);
  }

  root.on('change', '[data-footer-menu-group]', function () {
    var source = $(this);
    syncSharedFooterMenu(source);
    root.find('[data-footer-menu-group="' + String(source.attr('data-footer-menu-group') || 'primary') + '"]').each(function () { renderFooterMenu($(this)); });
  });

  function renderJournalItems(language, items) {
    var list = previewFor('journal', language).find('[data-preview-journal-list]');
    var html = '';
    $.each(items || [], function (_, item) {
      var thumbStyle = item.image ? ' style="background-image:url(\'' + escapeHtml(item.image) + '\')"' : '';
      html += '<a class="vava-fe-article" href="#"><span class="vava-fe-thumb"' + thumbStyle + '></span><span><strong>' + escapeHtml(item.title || '') + '</strong><small>' + escapeHtml(item.label || '') + '</small></span></a>';
    });
    if (!html) html = '<div class="vava-preview-empty">' + translated('لا توجد مقالات مطابقة للإعدادات الحالية.', 'No articles match the current settings.', language) + '</div>';
    list.html(html);
  }

  function scheduleJournalPreview(container) {
    if (!container || !container.length || !config.ajaxUrl || !config.previewNonce) return;
    var language = String(container.attr('data-journal-language') || languageFor(container));
    window.clearTimeout(journalPreviewTimers[language]);
    journalPreviewTimers[language] = window.setTimeout(function () {
      var mode = container.find('[data-journal-mode]').val() || 'latest';
      var latest = container.find('[data-journal-latest]').val() || 0;
      var random = container.find('[data-journal-random]').val() || [];
      var list = previewFor('journal', language).find('[data-preview-journal-list]').addClass('is-loading');
      $.ajax({
        url: config.ajaxUrl,
        method: 'POST',
        dataType: 'json',
        data: {
          action: 'vava_homepage_journal_preview', nonce: config.previewNonce, post_id: config.postId || 0,
          lang: language, mode: mode, latest_category: latest, random_categories: random
        }
      }).done(function (response) {
        if (response && response.success && response.data) renderJournalItems(language, response.data.items || []);
      }).always(function () { list.removeClass('is-loading'); });
    }, 220);
  }

  function refreshAllLivePreviews() {
    root.find('[data-vava-preview-field]').each(function () { updateTextPreview($(this)); });
    root.find('.vava-media-id').each(function () { refreshMediaPreview($(this)); });
    root.find('[data-link-selector]').each(function () { refreshButtonHref($(this)); });
    root.find('[data-repeater]').each(function () { refreshRepeaterPreview($(this)); });
    renderInternalHeaderMenu(root.find('[data-internal-header-menu-select]').first());
    refreshHeroMediaFields();
    fitActivePreview();
  }

  function emptyPreview(mediaType) {
    var icon = mediaType === 'video'
      ? '<svg viewBox="0 0 48 48"><rect x="5" y="9" width="38" height="30" rx="5"/><path d="m20 17 12 7-12 7Z"/></svg>'
      : '<svg viewBox="0 0 48 48"><rect x="5" y="7" width="38" height="34" rx="5"/><circle cx="17" cy="18" r="4"/><path d="m9 36 11-11 8 8 5-5 7 8"/></svg>';
    var language = currentLanguage();
    var title = mediaType === 'video'
      ? translated('اسحب ملف الفيديو وأفلته هنا', 'Drag and drop the video here', language)
      : translated('اسحب الصورة وأفلتها هنا', 'Drag and drop the image here', language);
    var hint = translated('أو اضغط للاختيار من مكتبة الوسائط', 'Or click to choose from the media library', language);
    return '<div class="vava-media-empty">' + icon + '<strong>' + title + '</strong><span>' + hint + '</span></div>';
  }

  function attachmentUrl(attachment, mediaType) {
    if (mediaType === 'image' && attachment.sizes) {
      if (attachment.sizes.medium_large) return attachment.sizes.medium_large.url;
      if (attachment.sizes.medium) return attachment.sizes.medium.url;
    }
    return attachment.url || '';
  }

  function setMedia(field, attachment) {
    var mediaType = String(field.data('media-type') || 'image');
    var input = field.find('.vava-media-id');
    var preview = field.find('.vava-media-preview');
    var id = parseInt(attachment.id, 10) || 0;
    var url = attachmentUrl(attachment, mediaType);
    var title = attachment.title || attachment.filename || '';

    input.attr('data-media-url', url).val(id).trigger('change');
    if (mediaType === 'video') {
      preview.empty().append($('<video>', { controls: true, preload: 'metadata', src: url }))
        .append($('<span>', { class: 'vava-media-file-name', text: title }));
    } else {
      preview.empty().append($('<img>', { alt: '', src: url }))
        .append($('<span>', { class: 'vava-media-file-name', text: title }));
    }
    field.removeClass('is-uploading has-error').find('.vava-media-error').remove();
  }

  function clearMedia(field) {
    var mediaType = String(field.data('media-type') || 'image');
    var input = field.find('.vava-media-id');
    input.attr('data-media-url', input.attr('data-fallback-url') || '').val('').trigger('change');
    field.find('.vava-media-preview').html(emptyPreview(mediaType));
    field.removeClass('is-uploading has-error').find('.vava-media-error').remove();
    field.find('.vava-upload-progress span').css('width', '0%');
  }

  function mediaError(field, message) {
    field.removeClass('is-uploading').addClass('has-error');
    field.find('.vava-media-error').remove();
    field.append($('<div>', { class: 'vava-media-error', text: message }));
    field.find('.vava-upload-progress span').css('width', '0%');
  }

  function validateFile(file, mediaType) {
    if (!file) return 'لم يتم اختيار ملف.';
    if (mediaType === 'image' && String(file.type).indexOf('image/') !== 0) return 'يرجى اختيار ملف صورة صالح.';
    if (mediaType === 'video' && String(file.type).indexOf('video/') !== 0) return 'يرجى اختيار ملف فيديو صالح.';
    var maxMb = mediaType === 'video' ? (config.maxVideoMb || 200) : (config.maxImageMb || 20);
    if (file.size > maxMb * 1024 * 1024) return 'حجم الملف يتجاوز الحد المسموح (' + maxMb + ' MB).';
    return '';
  }

  function uploadDroppedFile(field, file) {
    var mediaType = String(field.data('media-type') || 'image');
    var validation = validateFile(file, mediaType);
    if (validation) { mediaError(field, validation); return; }
    if (!config.uploadUrl || !config.uploadNonce) {
      mediaError(field, 'تعذر تهيئة رفع الوسائط. أعد تحميل الصفحة وحاول مرة أخرى.');
      return;
    }

    var formData = new FormData();
    formData.append('name', file.name);
    formData.append('action', 'upload-attachment');
    formData.append('_wpnonce', config.uploadNonce);
    formData.append('post_id', config.postId || 0);
    formData.append('async-upload', file, file.name);

    var xhr = new XMLHttpRequest();
    field.addClass('is-uploading').removeClass('has-error').find('.vava-media-error').remove();
    field.find('.vava-upload-progress span').css('width', '3%');
    xhr.open('POST', config.uploadUrl, true);
    xhr.upload.onprogress = function (event) {
      if (event.lengthComputable) {
        field.find('.vava-upload-progress span').css('width', Math.max(3, Math.round((event.loaded / event.total) * 100)) + '%');
      }
    };
    xhr.onload = function () {
      var response;
      try { response = JSON.parse(xhr.responseText); } catch (error) {
        mediaError(field, 'استجابة رفع الوسائط غير صالحة.');
        return;
      }
      if (xhr.status >= 200 && xhr.status < 300 && response && response.success && response.data) {
        setMedia(field, response.data);
        field.find('.vava-upload-progress span').css('width', '100%');
        window.setTimeout(function () { field.find('.vava-upload-progress span').css('width', '0%'); }, 500);
      } else {
        var message = response && response.data && response.data.message ? response.data.message : 'فشل رفع الملف إلى مكتبة الوسائط.';
        mediaError(field, $('<div>').html(message).text());
      }
    };
    xhr.onerror = function () { mediaError(field, 'حدث خطأ في الاتصال أثناء رفع الملف.'); };
    xhr.send(formData);
  }

  function openMediaFrame(field) {
    var mediaType = String(field.data('media-type') || 'image');
    var language = currentLanguage();
    var frame = wp.media({
      title: mediaType === 'video'
        ? translated('اختيار فيديو الهيرو', 'Choose hero video', language)
        : translated('اختيار صورة', 'Choose image', language),
      button: { text: translated('استخدام الملف', 'Use this file', language) },
      library: { type: mediaType },
      multiple: false
    });
    frame.on('select', function () {
      setMedia(field, frame.state().get('selection').first().toJSON());
    });
    frame.open();
  }

  root.on('click', '.vava-media-select', function (event) {
    event.preventDefault();
    openMediaFrame($(this).closest('.vava-media-field'));
  });
  root.on('click', '.vava-media-dropzone', function (event) {
    if ($(event.target).closest('video, .vava-media-actions, button, a, input').length) return;
    openMediaFrame($(this).closest('.vava-media-field'));
  });
  root.on('keydown', '.vava-media-dropzone', function (event) {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      openMediaFrame($(this).closest('.vava-media-field'));
    }
  });
  root.on('click', '.vava-media-remove', function (event) {
    event.preventDefault();
    clearMedia($(this).closest('.vava-media-field'));
  });
  root.on('dragenter dragover', '.vava-media-dropzone', function (event) {
    event.preventDefault(); event.stopPropagation(); $(this).addClass('is-dragover');
  });
  root.on('dragleave dragend', '.vava-media-dropzone', function (event) {
    event.preventDefault(); event.stopPropagation(); $(this).removeClass('is-dragover');
  });
  root.on('drop', '.vava-media-dropzone', function (event) {
    event.preventDefault(); event.stopPropagation(); $(this).removeClass('is-dragover');
    var files = event.originalEvent && event.originalEvent.dataTransfer ? event.originalEvent.dataTransfer.files : null;
    if (files && files.length) uploadDroppedFile($(this).closest('.vava-media-field'), files[0]);
  });

  $('#vava_homepage_settings').on('click', '[data-vava-submit]', function (event) {
    event.preventDefault();
    var button = $(this);
    button.addClass('is-saving').prop('disabled', true).find('span').text(translated('جارٍ التحديث…', 'Updating…'));
    var publish = $('#publish');
    if (publish.length) {
      publish.trigger('click');
    } else {
      $('#post').trigger('submit');
    }
  });

  $('#post').on('submit', function () {
    root.find('[data-repeater]').each(function () { reindexRepeater($(this)); });
    root.find(':input[name^="_vava_home_"]').prop('disabled', false);
    var number = root.find('[data-vava-document-number]').first().val();
    if (typeof number !== 'undefined') updateDocumentNumber(number, null);
  });

  var savedSection = 'hero';
  var savedLanguage = 'ar';
  try {
    savedSection = localStorage.getItem('vavaHomepageSection') || 'hero';
    savedLanguage = localStorage.getItem('vavaHomepageLanguage') || 'ar';
  } catch (e) {}

  setupPageIdentity();
  lockHomepagePostbox();
  setupSettingsHeader();
  setupHomepageSidebar();
  activateSection(savedSection);
  activateLanguage(savedLanguage);
  refreshAllLivePreviews();
  updateSidebarPreview();
})(jQuery);
