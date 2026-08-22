/* VAVA_JOURNAL_ARTICLE_VISIBILITY_ADMIN_V1 */
(function ($) {
  'use strict';

  var root = $('.vava-journal-admin');
  if (!root.length) return;

  var config = window.vavaJournalAdmin || {};
  $('body').addClass('vava-homepage-classic vava-journal-classic');

  function currentLanguage() {
    return String(root.attr('data-active-language') || 'ar') === 'en' ? 'en' : 'ar';
  }

  function previewFor(section, language) {
    return $('.vava-live-preview[data-journal-preview-panel][data-preview-section="' + section + '"][data-preview-language="' + language + '"]').first();
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
    root.find('[data-vava-i18n-placeholder-ar][data-vava-i18n-placeholder-en]').each(function () {
      var node = $(this);
      var value = node.attr('data-vava-i18n-placeholder-' + language);
      if (typeof value !== 'undefined') node.attr('placeholder', value);
    });
    syncFeaturedPickerLabel();
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
    arabic.on('input change', function () { nativeTitle.val($(this).val() || '').trigger('input'); });
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
    $('.vava-live-preview[data-journal-preview-panel]').attr('hidden', true).removeClass('is-sidebar-active');
    var active = previewFor(section, language).removeAttr('hidden').addClass('is-sidebar-active');
    fitPreview(active);
  }

  function setupSidebar() {
    var side = $('#side-sortables');
    var previews = root.find('.vava-live-preview[data-journal-preview-panel]');
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
    try { localStorage.setItem('vavaJournalSection', section); } catch (error) {}
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
    try { localStorage.setItem('vavaJournalLanguage', language); } catch (error) {}
    rebuildArticlesPreview();
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

  function setMedia(field, id, url) {
    var input = field.find('[data-journal-media-id]');
    var fallback = field.attr('data-fallback-url') || '';
    var effective = url || fallback;
    input.val(id || 0).attr('data-media-url', effective);
    field.find('.vava-media-preview').html($('<img>', { src: effective, alt: '' }));
    var key = field.attr('data-preview-key');
    if (key && effective) $('.vava-live-preview[data-journal-preview-panel] [data-preview-image="' + key + '"]').css('background-image', 'url("' + effective.replace(/"/g, '\\"') + '")');
    fitCurrent();
  }

  function selectedCategories() {
    return root.find('[data-journal-category]:checked').map(function () { return parseInt($(this).val(), 10) || 0; }).get().filter(Boolean);
  }

  function selectedCategoryOrder() {
    var raw = String(root.find('[data-journal-category-order]').val() || '');
    var order = raw.split(',').map(function (value) { return parseInt(value, 10) || 0; }).filter(Boolean);
    var selected = selectedCategories();
    order = order.filter(function (id) { return selected.indexOf(id) !== -1; });
    selected.forEach(function (id) { if (order.indexOf(id) === -1) order.push(id); });
    return order;
  }

  function displayMode() {
    return String(root.find('[data-journal-display-mode]:checked').val() || 'priority') === 'random' ? 'random' : 'priority';
  }
  function articlesEnabled() {
    return root.find('[data-journal-show-articles]').first().is(':checked');
  }

  function syncArticleVisibility(source) {
    var enabled = source ? $(source).is(':checked') : articlesEnabled();
    root.find('[data-journal-show-articles]').prop('checked', enabled);
    root.toggleClass('is-articles-disabled', !enabled);
    return enabled;
  }

  function syncPriorityInput() {
    var ids = root.find('[data-journal-priority-list] [data-category-id]').map(function () { return parseInt($(this).attr('data-category-id'), 10) || 0; }).get().filter(Boolean);
    root.find('[data-journal-category-order]').val(ids.join(','));
    root.find('[data-journal-priority-empty]').attr('hidden', ids.length ? 'hidden' : null);
  }

  function rebuildPriorityList() {
    var list = root.find('[data-journal-priority-list]').first();
    if (!list.length) return;
    var existing = {};
    list.find('[data-category-id]').each(function () { existing[parseInt($(this).attr('data-category-id'), 10) || 0] = $(this); });
    var order = selectedCategoryOrder();
    order.forEach(function (id) {
      var node = existing[id];
      if (!node || !node.length) {
        var source = root.find('[data-journal-category][value="' + id + '"]').closest('[data-category-id]');
        var title = source.find('strong').first().text();
        var count = source.find('small').first().text().replace(/[^0-9]+/g, '') || '0';
        node = $('<div>', { class: 'vava-journal-priority-item', 'data-category-id': id })
          .append($('<span>', { class: 'vava-journal-priority-handle', 'aria-hidden': 'true', text: '⋮⋮' }))
          .append($('<strong>').text(title))
          .append($('<small>').text(count));
      }
      list.append(node);
      delete existing[id];
    });
    Object.keys(existing).forEach(function (id) { existing[id].remove(); });
    syncPriorityInput();
  }

  function updatePriorityVisibility() {
    root.find('[data-journal-priority-wrap]').toggleClass('is-disabled', displayMode() !== 'priority');
  }

  function setupCategoryPriority() {
    var list = root.find('[data-journal-priority-list]').first();
    if (list.length && $.fn.sortable) {
      list.sortable({
        axis: 'y',
        handle: '.vava-journal-priority-handle',
        placeholder: 'vava-journal-priority-placeholder',
        update: function () { syncPriorityInput(); rebuildArticlesPreview(); }
      });
    }
    rebuildPriorityList();
    updatePriorityVisibility();
  }

  function stableRandomValue(id) {
    var value = String((config.postId || 0) + ':journal:' + id);
    var hash = 2166136261;
    for (var i = 0; i < value.length; i += 1) { hash ^= value.charCodeAt(i); hash = Math.imul(hash, 16777619); }
    return hash >>> 0;
  }

  function escapeHtml(value) {
    return $('<div>').text(value == null ? '' : String(value)).html().replace(/'/g, '&#039;');
  }

  function articleCardHtml(item, readMore, featured, language) {
    var featuredLabel = language === 'en' ? 'Featured article' : 'مقال مميز';
    return '<article class="vava-journal-preview-card' + (featured ? ' is-featured' : '') + '" data-preview-article>' +
      '<div class="vava-journal-preview-thumb" style="background-image:url(\'' + escapeHtml(item.image || '') + '\')">' + (featured ? '<span class="vava-journal-preview-featured">' + escapeHtml(featuredLabel) + '</span>' : '') + '</div>' +
      '<div class="vava-journal-preview-card-body"><h4>' + escapeHtml(item.title || '') + '</h4><p>' + escapeHtml(item.excerpt || '') + '</p>' +
      '<div class="vava-journal-preview-card-footer"><span class="vava-journal-preview-category">' + escapeHtml(item.category || '') + '</span><b data-preview-read-more>' + escapeHtml(readMore || '') + '<i aria-hidden="true">←</i></b></div></div></article>';
  }

  function featuredPicker() {
    return root.find('[data-journal-featured-picker]').first();
  }

  function closeFeaturedPicker() {
    var picker = featuredPicker();
    if (!picker.length) return;
    picker.removeClass('is-open');
    picker.find('[data-journal-featured-trigger]').attr('aria-expanded', 'false');
    picker.find('[data-journal-featured-popover]').attr('hidden', true).removeAttr('style');
  }

  function optionLabel(option, language) {
    if (!option || !option.length) return '';
    var localized = option.attr('data-title-' + language);
    return localized || option.attr('data-title') || option.find('.vava-journal-featured-option-title').text() || '';
  }

  function syncFeaturedPickerLabel() {
    var picker = featuredPicker();
    if (!picker.length) return;
    var value = String(picker.find('[data-journal-featured-post]').val() || '0');
    var option = picker.find('[data-journal-featured-option]').filter(function () { return String($(this).attr('data-value') || '0') === value; }).first();
    if (!option.length) option = picker.find('[data-journal-featured-option][data-value="0"]').first();
    var label = optionLabel(option, currentLanguage());
    picker.find('[data-journal-featured-label]').text(label);
    picker.find('[data-journal-featured-trigger]').attr('title', label);
    picker.find('[data-journal-featured-option]').removeClass('is-selected').attr('aria-selected', 'false');
    option.addClass('is-selected').attr('aria-selected', 'true');
  }

  function normalizeFeaturedSearch(value) {
    value = String(value || '');
    if (typeof value.normalize === 'function') value = value.normalize('NFKD');
    return value
      .replace(/[\u0300-\u036f\u064b-\u065f\u0670\u06d6-\u06ed]/g, '')
      .replace(/[أإآٱ]/g, 'ا')
      .replace(/ى/g, 'ي')
      .replace(/ة/g, 'ه')
      .replace(/ؤ/g, 'و')
      .replace(/ئ/g, 'ي')
      .replace(/\s+/g, ' ')
      .trim()
      .toLocaleLowerCase();
  }

  function filterFeaturedOptions(query) {
    var picker = featuredPicker();
    if (!picker.length) return;
    var terms = normalizeFeaturedSearch(query).split(' ').filter(Boolean);
    var visible = 0;
    picker.find('[data-journal-featured-option]').each(function () {
      var option = $(this);
      var haystack = normalizeFeaturedSearch(option.attr('data-search') || option.text() || '');
      var match = !terms.length || terms.every(function (term) { return haystack.indexOf(term) !== -1; });
      option.attr('hidden', match ? null : 'hidden')
        .attr('aria-hidden', match ? 'false' : 'true')
        .toggleClass('is-filtered-out', !match);
      if (match) visible += 1;
    });
    picker.find('[data-journal-featured-no-match]').attr('hidden', visible > 0 ? 'hidden' : null);
    if (picker.hasClass('is-open')) window.requestAnimationFrame(positionFeaturedPopover);
  }

  function positionFeaturedPopover() {
    var picker = featuredPicker();
    if (!picker.length || !picker.hasClass('is-open')) return;
    var trigger = picker.find('[data-journal-featured-trigger]').get(0);
    var popover = picker.find('[data-journal-featured-popover]').get(0);
    if (!trigger || !popover || popover.hidden) return;

    var viewportWidth = window.innerWidth || document.documentElement.clientWidth || 1280;
    var viewportHeight = window.innerHeight || document.documentElement.clientHeight || 800;
    var triggerRect = trigger.getBoundingClientRect();
    var gutter = 16;
    var width = Math.min(580, Math.max(360, viewportWidth - (gutter * 2)));
    var left;

    if (viewportWidth <= 782) {
      width = Math.max(280, viewportWidth - (gutter * 2));
      left = gutter;
    } else {
      left = triggerRect.left - width - 18;
      if (left < gutter) left = gutter;
    }

    popover.style.position = 'fixed';
    popover.style.width = width + 'px';
    popover.style.maxWidth = 'calc(100vw - 32px)';
    popover.style.maxHeight = 'calc(100vh - 32px)';
    popover.style.left = Math.round(left) + 'px';
    popover.style.right = 'auto';
    popover.style.top = Math.round(Math.max(gutter, triggerRect.top)) + 'px';
    popover.style.margin = '0';
    popover.style.zIndex = '100000';

    var measured = popover.getBoundingClientRect();
    var top = Math.max(gutter, Math.min(triggerRect.top, viewportHeight - measured.height - gutter));
    popover.style.top = Math.round(top) + 'px';
  }

  function setupFeaturedPicker() {
    var picker = featuredPicker();
    if (!picker.length) return;
    syncFeaturedPickerLabel();
    picker.on('click', '[data-journal-featured-trigger]', function (event) {
      event.preventDefault();
      var open = !picker.hasClass('is-open');
      if (!open) { closeFeaturedPicker(); return; }
      picker.addClass('is-open');
      picker.find('[data-journal-featured-trigger]').attr('aria-expanded', 'true');
      picker.find('[data-journal-featured-popover]').removeAttr('hidden');
      positionFeaturedPopover();
      var search = picker.find('[data-journal-featured-search]');
      search.val('');
      filterFeaturedOptions('');
      window.setTimeout(function () { search.trigger('focus'); }, 0);
    });
    picker.on('input search', '[data-journal-featured-search]', function () { filterFeaturedOptions($(this).val()); });
    picker.on('click', '[data-journal-featured-option]', function (event) {
      event.preventDefault();
      var option = $(this);
      picker.find('[data-journal-featured-post]').val(String(option.attr('data-value') || '0')).trigger('change');
      syncFeaturedPickerLabel();
      closeFeaturedPicker();
    });
    picker.on('keydown', function (event) {
      if (event.key === 'Escape') { closeFeaturedPicker(); picker.find('[data-journal-featured-trigger]').trigger('focus'); }
    });
    $(document).on('mousedown.vavaJournalFeatured', function (event) {
      if (!picker.get(0).contains(event.target)) closeFeaturedPicker();
    });
    $(window).on('resize.vavaJournalFeatured orientationchange.vavaJournalFeatured', positionFeaturedPopover);
  }

  function selectedFeaturedPostId() {
    return Math.max(0, parseInt(root.find('[data-journal-featured-post]').val(), 10) || 0);
  }

  function rebuildArticlesPreview() {
    var language = currentLanguage();
    var preview = previewFor('articles', language);
    if (!preview.length) return;
    var all = config.posts && Array.isArray(config.posts[language]) ? config.posts[language] : [];
    var categories = selectedCategories();
    var count = Math.max(1, Math.min(24, parseInt(root.find('[data-journal-posts-per-page]').val(), 10) || 8));
    var categoryFiltered = all.filter(function (item) {
      if (!categories.length) return true;
      var itemCategories = Array.isArray(item.category_ids) ? item.category_ids.map(Number) : [];
      return categories.some(function (id) { return itemCategories.indexOf(Number(id)) !== -1; });
    });
    var originalIndex = {};
    categoryFiltered.forEach(function (item, index) { originalIndex[Number(item.id)] = index; });
    if (displayMode() === 'random') {
      categoryFiltered.sort(function (left, right) { return stableRandomValue(Number(left.id)) - stableRandomValue(Number(right.id)); });
    } else {
      var priority = selectedCategoryOrder();
      var ranks = {};
      priority.forEach(function (id, index) { ranks[Number(id)] = index; });
      categoryFiltered.sort(function (left, right) {
        function rank(item) {
          var ids = Array.isArray(item.category_ids) ? item.category_ids.map(Number) : [];
          var result = Number.MAX_SAFE_INTEGER;
          ids.forEach(function (id) { if (Object.prototype.hasOwnProperty.call(ranks, id)) result = Math.min(result, ranks[id]); });
          return result;
        }
        var difference = rank(left) - rank(right);
        return difference || ((originalIndex[Number(left.id)] || 0) - (originalIndex[Number(right.id)] || 0));
      });
    }
    var selectedId = selectedFeaturedPostId();
    var featured = selectedId ? all.find(function (item) { return Number(item.id) === selectedId; }) : categoryFiltered[0];
    var featuredId = featured ? Number(featured.id) : 0;
    var regular = categoryFiltered.filter(function (item) { return Number(item.id) !== featuredId; });
    var readMore = root.find('[data-language-pane="' + language + '"] [data-journal-preview="articles-read-more"]').val() || '';
    var emptyText = root.find('[data-language-pane="' + language + '"] [data-journal-preview="articles-empty"]').val() || (config.empty && config.empty[language]) || '';
    var featuredColumn = preview.find('[data-preview-featured-column]');
    var grid = preview.find('[data-preview-articles-grid]');
    var articlesHead = preview.find('[data-preview-articles-head]');
    if (!articlesEnabled()) {
      articlesHead.hide();
      featuredColumn.empty();
      grid.addClass('is-full-width').html('<p class="vava-journal-preview-empty" data-preview-output="articles-empty">' + escapeHtml(emptyText) + '</p>');
      preview.find('[data-preview-pagination]').hide().empty();
      fitPreview(preview);
      return;
    }
    articlesHead.show();
    featuredColumn.html(featured ? articleCardHtml(featured, readMore, true, language) : '');
    var visible = regular.slice(0, Math.min(6, count));
    grid.toggleClass('is-full-width', !featured).html(visible.length ? visible.map(function (item) { return articleCardHtml(item, readMore, false, language); }).join('') : (!featured ? '<p class="vava-journal-preview-empty" data-preview-output="articles-empty">' + escapeHtml(emptyText) + '</p>' : ''));
    var totalPages = Math.ceil(regular.length / count);
    var previous = language === 'en' ? 'Previous' : 'السابق';
    var next = language === 'en' ? 'Next' : 'التالي';
    var pageNumbers = Array.from({ length: Math.min(5, totalPages) }, function (_, index) { return '<i' + (index === 0 ? ' class="is-current"' : '') + '>' + (index + 1) + '</i>'; }).join('');
    preview.find('[data-preview-pagination]').toggle(totalPages > 1).html('<button type="button"><span>‹</span><b>' + escapeHtml(previous) + '</b></button>' + pageNumbers + '<button type="button"><b>' + escapeHtml(next) + '</b><span>›</span></button>');
    fitPreview(preview);
  }

  root.on('click keydown', '[data-journal-media-field] .vava-media-dropzone, [data-journal-media-field] .vava-media-select', function (event) {
    if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
    event.preventDefault();
    var field = $(this).closest('[data-journal-media-field]');
    createMediaFrame(function (attachment) { setMedia(field, attachment.id || 0, attachment.url || ''); });
  });

  root.on('click', '[data-journal-media-field] .vava-media-remove', function (event) {
    event.preventDefault();
    setMedia($(this).closest('[data-journal-media-field]'), 0, '');
  });

  root.on('input change', '[data-journal-preview]', function () {
    var input = $(this);
    var section = input.closest('[data-section-panel]').attr('data-section-panel') || 'hero';
    var language = input.closest('[data-language-pane]').attr('data-language-pane') || 'ar';
    var key = input.attr('data-journal-preview');
    previewFor(section, language).find('[data-preview-output="' + key + '"]').text(input.val() || '');
    if (key === 'articles-read-more') previewFor('articles', language).find('[data-preview-read-more]').text(input.val() || '');
    fitCurrent();
  });

  root.on('change', '[data-journal-show-articles]', function () { syncArticleVisibility(this); rebuildArticlesPreview(); });
  root.on('change', '[data-journal-category]', function () { rebuildPriorityList(); rebuildArticlesPreview(); });
  root.on('change', '[data-journal-display-mode]', function () { updatePriorityVisibility(); rebuildArticlesPreview(); });
  root.on('change input', '[data-journal-posts-per-page], [data-journal-featured-post]', rebuildArticlesPreview);
  root.on('click', '[data-section]', function () { activateSection(String($(this).attr('data-section') || 'hero')); });
  $('#vava_homepage_settings').on('click', '.vava-language-switch button', function () { activateLanguage(String($(this).attr('data-language') || 'ar')); });
  $('#vava_homepage_settings').on('click', '[data-vava-submit]', function () { $('#publish').trigger('click'); });

  lockPostbox();
  setupPageIdentity();
  setupHeaderActions();
  setupSidebar();
  setupFeaturedPicker();
  setupCategoryPriority();

  var storedLanguage = 'ar';
  var storedSection = 'hero';
  try { storedLanguage = localStorage.getItem('vavaJournalLanguage') || 'ar'; storedSection = localStorage.getItem('vavaJournalSection') || 'hero'; } catch (error) {}
  activateLanguage(storedLanguage);
  activateSection(storedSection);
  syncArticleVisibility();
  rebuildArticlesPreview();
  $(window).on('resize', fitCurrent);
})(jQuery);
