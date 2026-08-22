(function ($) {
  'use strict';

  var root = $('.vava-legal-admin');
  if (!root.length) return;
  $('body').addClass('vava-homepage-classic vava-legal-classic');

  function language() { return root.attr('data-active-language') === 'en' ? 'en' : 'ar'; }
  function section() { return String(root.attr('data-active-section') || 'hero'); }

  function fitPreview(preview) {
    if (!preview.length || preview.attr('hidden')) return;
    var viewport = preview.find('.vava-preview-viewport').first();
    var stage = preview.find('.vava-preview-stage').first();
    var canvas = preview.find('.vava-preview-canvas').first();
    if (!viewport.length || !stage.length || !canvas.length) return;
    window.requestAnimationFrame(function () {
      var width = parseFloat(canvas.attr('data-preview-design-width')) || 850;
      var available = Math.max(300, viewport.innerWidth() - 24);
      var scale = Math.min(1, available / width);
      canvas.css({ width: width + 'px', transform: 'scale(' + scale + ')' });
      window.requestAnimationFrame(function () {
        stage.css({ width: Math.round(width * scale) + 'px', height: Math.round((canvas.get(0).scrollHeight || 1) * scale) + 'px' });
      });
    });
  }

  function activePreview() {
    return $('.vava-live-preview[data-legal-preview-panel][data-preview-section="' + section() + '"][data-preview-language="' + language() + '"]').first();
  }

  function updatePreviewDock() {
    $('.vava-live-preview[data-legal-preview-panel]').attr('hidden', true).removeClass('is-sidebar-active');
    var active = activePreview().removeAttr('hidden').addClass('is-sidebar-active');
    fitPreview(active);
  }

  function translateInterface(lang) {
    root.find('[data-vava-i18n-ar][data-vava-i18n-en]').each(function () {
      var node = $(this), value = node.attr('data-vava-i18n-' + lang);
      if (typeof value !== 'undefined') node.text(value);
    });
    var title = root.attr('data-settings-title-' + lang);
    if (title) $('#vava_homepage_settings .postbox-header h2').first().text(title);
  }

  function titlePane(lang) {
    root.find('[data-vava-page-title-pane]').removeClass('is-active').attr('hidden', true);
    root.find('[data-vava-page-title-pane="' + lang + '"]').addClass('is-active').removeAttr('hidden');
  }

  function activateLanguage(lang) {
    lang = lang === 'en' ? 'en' : 'ar';
    root.attr('data-active-language', lang);
    root.find('[data-vava-active-language-input]').val(lang);
    $('#vava_homepage_settings .vava-language-switch button').removeClass('is-active');
    $('#vava_homepage_settings .vava-language-switch button[data-language="' + lang + '"]').addClass('is-active');
    root.find('[data-language-pane]').removeClass('is-active');
    root.find('[data-language-pane="' + lang + '"]').addClass('is-active');
    titlePane(lang);
    translateInterface(lang);
    updatePreviewDock();
  }

  function activateSection(id) {
    id = id === 'content' ? 'content' : 'hero';
    root.attr('data-active-section', id);
    root.find('[data-section]').removeClass('is-active').attr('aria-selected', 'false');
    root.find('[data-section="' + id + '"]').addClass('is-active').attr('aria-selected', 'true');
    root.find('[data-section-panel]').removeClass('is-active');
    root.find('[data-section-panel="' + id + '"]').addClass('is-active');
    updatePreviewDock();
  }

  function setupSidebar() {
    var side = $('#side-sortables');
    var previews = root.find('.vava-live-preview[data-legal-preview-panel]');
    if (!side.length || !previews.length) return;
    $('#submitdiv, #pageparentdiv, #postimagediv').hide();
    var dock = $('#vava_live_preview_box');
    if (!dock.length) dock = $('<div>', { id: 'vava_live_preview_box', class: 'postbox vava-live-preview-postbox' }).append($('<div>', { class: 'inside' })).prependTo(side);
    dock.find('.inside').append(previews);
    updatePreviewDock();
    if (window.ResizeObserver) new window.ResizeObserver(function () { fitPreview(activePreview()); }).observe(dock.get(0));
  }

  function lockPostbox() {
    var box = $('#vava_homepage_settings');
    box.removeClass('closed').addClass('vava-homepage-postbox-is-locked');
    box.find('.handle-actions,.handlediv').remove();
    box.find('.postbox-header .hndle').removeClass('hndle ui-sortable-handle').attr('aria-disabled', 'true');
    $('.wrap > h1.wp-heading-inline,.wrap > .page-title-action,#titlediv').hide();
  }

  function setupHeaderActions() {
    var header = $('#vava_homepage_settings .postbox-header').first();
    var toolbar = root.find('.vava-toolbar-actions').first();
    if (!header.length || !toolbar.length) return;
    var actions = header.find('.vava-postbox-header-actions');
    if (!actions.length) actions = $('<div>', { class: 'vava-postbox-header-actions' }).appendTo(header);
    toolbar.find('.vava-language-switch').addClass('is-in-postbox-header').appendTo(actions);
    toolbar.find('[data-vava-submit]').addClass('is-in-postbox-header').appendTo(actions);
    toolbar.remove();
  }

  function syncField(field, value, lang) {
    $('.vava-live-preview[data-preview-language="' + lang + '"] [data-legal-preview="' + field + '"]').each(function () {
      if (field === 'content') $(this).html(value); else $(this).text(value);
    });
    fitPreview(activePreview());
  }

  root.on('click', '[data-language]', function () { activateLanguage($(this).attr('data-language')); });
  root.on('click', '[data-section]', function () { activateSection($(this).attr('data-section')); });
  root.on('click', '[data-vava-submit]', function () { $('#publish').trigger('click'); });
  root.on('input change', '[data-legal-field]', function () { syncField($(this).attr('data-legal-field'), $(this).val() || '', language()); });
  root.on('input', '.vava-legal-richtext-source', function () { syncField('content', $(this).val() || '', language()); });
  root.on('input', '[data-vava-richtext-editor]', function () {
    var control = $(this).closest('[data-vava-richtext]');
    var source = control.find('.vava-legal-richtext-source').first();
    window.setTimeout(function () { syncField('content', source.val() || control.find('[data-vava-richtext-editor]').html() || '', language()); }, 0);
  });

  $(function () {
    lockPostbox(); setupHeaderActions(); setupSidebar(); activateLanguage('ar'); activateSection('hero');
    var arabic = root.find('[data-vava-page-title-language="ar"]'), nativeTitle = $('#title');
    if (arabic.length && nativeTitle.length) arabic.on('input change', function () { nativeTitle.val($(this).val() || ''); });
  });
})(jQuery);
