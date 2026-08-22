(function ($) {
  'use strict';

  var config = window.vavaAdminCommon || {};

  function syncRichText(control, notify) {
    var editor = control.find('[data-vava-richtext-editor]').first();
    var source = control.find('.vava-richtext-source').first();
    if (!editor.length || !source.length) return;
    source.val(editor.html());
    if (notify) source.trigger('input');
  }

  function initRichText(scope) {
    $(scope || document).find('[data-vava-richtext]').each(function () {
      var control = $(this);
      if (control.attr('data-rte-ready') === '1') return;
      control.attr('data-rte-ready', '1');
      var editor = control.find('[data-vava-richtext-editor]').first();

      editor.on('input blur keyup paste', function () {
        window.setTimeout(function () { syncRichText(control, true); }, 0);
      });

      control.on('mousedown', '.vava-richtext-toolbar button', function (event) {
        event.preventDefault();
      });

      control.on('click', '[data-rte-command]', function (event) {
        event.preventDefault();
        editor.trigger('focus');
        try { document.execCommand(String($(this).attr('data-rte-command') || ''), false, null); } catch (e) {}
        syncRichText(control, true);
      });

      control.on('change', '.vava-richtext-format', function () {
        editor.trigger('focus');
        try { document.execCommand('formatBlock', false, String($(this).val() || 'p')); } catch (e) {}
        syncRichText(control, true);
      });

      control.on('click', '[data-rte-link]', function (event) {
        event.preventDefault();
        editor.trigger('focus');
        var language = $('.vava-homepage-admin').attr('data-active-language') === 'en' ? 'en' : 'ar';
        var url = window.prompt(language === 'en' ? 'Enter the link URL' : 'أدخل رابط النص', 'https://');
        if (!url) return;
        try { document.execCommand('createLink', false, url); } catch (e) {}
        syncRichText(control, true);
      });

      syncRichText(control, false);
    });
  }


  function formatBytes(bytes) {
    var value = Number(bytes || 0);
    if (!value) return '—';
    var units = ['B', 'KB', 'MB', 'GB'];
    var power = Math.min(units.length - 1, Math.floor(Math.log(value) / Math.log(1024)));
    return (value / Math.pow(1024, power)).toFixed(power ? 1 : 0) + ' ' + units[power];
  }

  function ensureUploadProgress(input) {
    var $input = $(input);
    var host = $input.closest('[data-vava-private-pdf-field],.vava-media-field,.vava-booking-receipt-upload,.vava-digital-receipt-dropzone,.vava-admin-field-media,.vava-repeater-field');
    if (!host.length) host = $input.parent();
    var progress = host.find('[data-vava-upload-progress]').first();
    if (progress.length) return progress;
    progress = $('<div>', { class: 'vava-upload-progress', 'data-vava-upload-progress': '', 'aria-live': 'polite' });
    var head = $('<div>', { class: 'vava-upload-progress-head' }).appendTo(progress);
    $('<strong>', { 'data-upload-progress-label': '', text: 'جاهز للرفع' }).appendTo(head);
    $('<span>', { 'data-upload-progress-percent': '', text: '0%' }).appendTo(head);
    $('<div>', { class: 'vava-upload-progress-track' }).append($('<i>', { 'data-upload-progress-bar': '' })).appendTo(progress);
    $('<small>', { 'data-upload-progress-meta': '', text: '' }).appendTo(progress);
    progress.insertAfter($input.closest('.vava-media-dropzone,.vava-digital-receipt-dropzone,label').first().length ? $input.closest('.vava-media-dropzone,.vava-digital-receipt-dropzone,label').first() : $input);
    return progress;
  }

  function updateUploadProgress(progress, percent, label, meta, state) {
    if (!progress || !progress.length) return;
    percent = Math.max(0, Math.min(100, Number(percent || 0)));
    progress.addClass('is-active').removeClass('is-error is-complete');
    if (state === 'error') progress.addClass('is-error');
    if (state === 'complete') progress.addClass('is-complete');
    progress.find('[data-upload-progress-bar]').css('width', percent + '%');
    progress.find('[data-upload-progress-percent]').text(Math.round(percent) + '%');
    if (label) progress.find('[data-upload-progress-label]').text(label);
    if (typeof meta !== 'undefined') progress.find('[data-upload-progress-meta]').text(meta || '');
  }

  function initUploadProgress(scope) {
    $(scope || document).find('input[type="file"]').each(function () {
      if ($(this).attr('data-vava-progress-ready') === '1') return;
      $(this).attr('data-vava-progress-ready', '1');
      var input = this;
      $(input).on('change.vavaUploadProgress', function () {
        var file = input.files && input.files[0] ? input.files[0] : null;
        if (!file) return;
        var progress = ensureUploadProgress(input);
        var reader = new FileReader();
        updateUploadProgress(progress, 2, document.documentElement.lang === 'en' ? 'Preparing file…' : 'جارٍ تجهيز الملف…', file.name + ' · ' + formatBytes(file.size));
        reader.onprogress = function (event) {
          if (event.lengthComputable) updateUploadProgress(progress, Math.max(2, Math.round(event.loaded / event.total * 65)), document.documentElement.lang === 'en' ? 'Preparing file…' : 'جارٍ تجهيز الملف…', file.name + ' · ' + formatBytes(file.size));
        };
        reader.onload = function () { updateUploadProgress(progress, 70, document.documentElement.lang === 'en' ? 'Ready to upload' : 'جاهز للرفع', file.name + ' · ' + formatBytes(file.size)); };
        reader.onerror = function () { updateUploadProgress(progress, 0, document.documentElement.lang === 'en' ? 'Could not read the file' : 'تعذر قراءة الملف', file.name, 'error'); };
        try { reader.readAsArrayBuffer(file); } catch (e) { updateUploadProgress(progress, 0, document.documentElement.lang === 'en' ? 'Could not read the file' : 'تعذر قراءة الملف', file.name, 'error'); }
      });
    });
  }

  function installMutationObserver() {
    if (!window.MutationObserver) return;
    var observer = new window.MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) { initRichText(node); initUploadProgress(node); }
        });
      });
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  function successToast() {
    if (!config.showSuccess) return;
    $('#message.updated, .notice.notice-success.is-dismissible').hide();
    var language = $('.vava-homepage-admin').attr('data-active-language') === 'en' ? 'en' : 'ar';
    var title = language === 'en' ? 'Page updated successfully' : 'تم تحديث الصفحة بنجاح';
    var text = language === 'en' ? 'Your changes have been saved and published.' : 'تم حفظ التغييرات ونشرها.';
    var view = language === 'en' ? 'View page' : 'عرض الصفحة';
    var close = language === 'en' ? 'Close' : 'إغلاق';
    var toast = $('<section>', {
      class: 'vava-success-toast',
      role: 'status',
      'aria-live': 'polite',
      dir: language === 'en' ? 'ltr' : 'rtl'
    });

    $('<div>', { class: 'vava-success-toast-wave', 'aria-hidden': 'true' }).appendTo(toast);
    $('<button>', {
      class: 'vava-success-toast-close',
      type: 'button',
      'aria-label': close,
      html: '&times;'
    }).appendTo(toast);

    var check = $('<div>', { class: 'vava-success-toast-check', 'aria-hidden': 'true' }).appendTo(toast);
    check.html('<svg viewBox="0 0 48 48"><path d="m13 25 8 8 15-18"/></svg>');

    var content = $('<div>', { class: 'vava-success-toast-content' }).appendTo(toast);
    $('<strong>', { text: title }).appendTo(content);
    $('<span>', { text: text }).appendTo(content);
    var link = $('<a>', {
      class: 'vava-success-toast-view',
      href: config.viewUrl || '#',
      target: '_blank',
      rel: 'noopener',
      text: view
    }).appendTo(content);
    $('<span>', { class: 'vava-success-toast-arrow', 'aria-hidden': 'true', text: '→' }).appendTo(link);

    $('body').append(toast);
    window.requestAnimationFrame(function () { toast.addClass('is-visible'); });
    toast.on('click', '.vava-success-toast-close', function () {
      toast.removeClass('is-visible');
      window.setTimeout(function () { toast.remove(); }, 260);
    });
  }

  $(function () {
    initRichText(document);
    initUploadProgress(document);
    installMutationObserver();
    $('#post').on('submit.vavaRichText', function () {
      $('[data-vava-richtext]').each(function () { syncRichText($(this), false); });
      $('input[type="file"]').each(function () {
        if (this.files && this.files[0]) updateUploadProgress(ensureUploadProgress(this), 88, document.documentElement.lang === 'en' ? 'Uploading and saving…' : 'جارٍ الرفع والحفظ…', this.files[0].name + ' · ' + formatBytes(this.files[0].size));
      });
    });
    successToast();
  });
})(jQuery);
