(function ($) {
  'use strict';

  var config = window.VAVA_DIGITAL_PRODUCTS_ADMIN || {};
  var pollers = {};

  $('#post').attr('enctype', 'multipart/form-data');

  function formatBytes(bytes) {
    var value = Number(bytes || 0);
    if (!value) return '—';
    var units = ['B', 'KB', 'MB', 'GB'];
    var power = Math.min(units.length - 1, Math.floor(Math.log(value) / Math.log(1024)));
    return (value / Math.pow(1024, power)).toFixed(power ? 1 : 0) + ' ' + units[power];
  }

  function progress(field) {
    return field.find('[data-vava-upload-progress]').first();
  }

  function update(field, percent, label, meta, state) {
    var box = progress(field);
    percent = Math.max(0, Math.min(100, Number(percent || 0)));
    box.removeAttr('hidden').addClass('is-active').removeClass('is-error is-complete');
    if (state === 'error') box.addClass('is-error');
    if (state === 'complete') box.addClass('is-complete');
    box.find('[data-upload-progress-bar]').css('width', percent + '%');
    box.find('[data-upload-progress-percent]').text(Math.round(percent) + '%');
    if (label) box.find('[data-upload-progress-label]').text(label);
    if (typeof meta !== 'undefined') box.find('[data-upload-progress-meta]').text(meta || '');
  }

  function request(field, action, extra, file, onProgress) {
    return new Promise(function (resolve, reject) {
      var xhr = new XMLHttpRequest();
      var data = new FormData();
      data.append('action', action);
      data.append('nonce', config.nonce || '');
      data.append('post_id', field.attr('data-post-id') || config.postId || '');
      data.append('uid', field.attr('data-product-uid') || '');
      Object.keys(extra || {}).forEach(function (key) { data.append(key, extra[key]); });
      if (file) data.append('file', file, file.name);
      xhr.open('POST', config.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php', true);
      xhr.withCredentials = true;
      if (xhr.upload && typeof onProgress === 'function') {
        xhr.upload.addEventListener('progress', function (event) {
          if (event.lengthComputable) onProgress(event.loaded / event.total * 100);
        });
      }
      xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) return;
        var response = null;
        try { response = JSON.parse(xhr.responseText || '{}'); } catch (e) {}
        if (xhr.status >= 200 && xhr.status < 300 && response && response.success) resolve(response.data || {});
        else reject(new Error(response && response.data && response.data.message ? response.data.message : 'تعذر تنفيذ العملية.'));
      };
      xhr.send(data);
    });
  }

  function syncFields(uid, callback) {
    $('[data-vava-private-pdf-field][data-product-uid="' + uid + '"]').each(function () { callback($(this)); });
  }

  function renderRecord(field, record) {
    record = record || {};
    var status = String(record.processing_status || 'empty');
    var percent = Number(record.processing_progress || 0);
    var hasFile = Boolean(record.relative_path || record.fingerprint);
    var message = record.processing_message || '';
    var thumbnail = String(record.admin_thumbnail_url || '');
    field.attr('data-processing-status', status);
    var state = field.find('.vava-private-file-state').first();
    if (thumbnail) {
      var image = state.find('[data-private-file-thumbnail]');
      if (!image.length) { image = $('<img>', { class: 'vava-private-file-thumbnail', 'data-private-file-thumbnail': '', alt: '' }).prependTo(state); }
      image.attr('src', thumbnail + (thumbnail.indexOf('?') === -1 ? '?' : '&') + 'v=' + Date.now());
      state.addClass('has-thumbnail');
      state.find('[data-private-file-icon]').remove();
    } else {
      state.removeClass('has-thumbnail');
      state.find('[data-private-file-thumbnail]').remove();
      if (!state.find('[data-private-file-icon]').length) $('<span>', { class: 'dashicons dashicons-pdf', 'data-private-file-icon': '', 'aria-hidden': 'true' }).prependTo(state);
    }
    if (hasFile) {
      field.find('.vava-private-file-state').addClass('has-file');
      field.find('[data-private-file-select]').text(document.documentElement.lang === 'en' ? 'Replace' : 'استبدال');
      if (!field.find('[data-private-file-delete]').length) {
        $('<button>', { type: 'button', class: 'button vava-media-remove vava-private-file-delete', 'data-private-file-delete': '', text: document.documentElement.lang === 'en' ? 'Delete' : 'حذف' }).appendTo(field.find('.vava-media-actions'));
      }
    }
    if (status === 'ready') {
      progress(field).removeClass('is-active is-complete is-error').attr('hidden', true);
    } else if (status === 'queued' || status === 'processing' || status === 'failed') {
      progress(field).removeAttr('hidden');
      update(field, percent, message || (config.labels && config.labels.processing), '', status === 'failed' ? 'error' : '');
    }
    if (status === 'failed' && !field.find('[data-private-file-reprocess]').length) {
      $('<button>', { type: 'button', class: 'button', 'data-private-file-reprocess': '', text: document.documentElement.lang === 'en' ? 'Retry processing' : 'إعادة تجهيز الملف' }).appendTo(field.find('.vava-media-actions'));
    }
    if (status !== 'failed') field.find('[data-private-file-reprocess]').remove();
  }

  function poll(field) {
    var uid = field.attr('data-product-uid') || '';
    if (!uid || pollers[uid]) return;
    pollers[uid] = window.setInterval(function () {
      request(field, 'vava_digital_private_pdf_status').then(function (data) {
        var record = data.record || {};
        syncFields(uid, function (target) { renderRecord(target, record); });
        if (record.processing_status === 'ready' || record.processing_status === 'failed' || !record.processing_status) {
          window.clearInterval(pollers[uid]);
          delete pollers[uid];
        }
      }).catch(function () {
        window.clearInterval(pollers[uid]);
        delete pollers[uid];
      });
    }, 1800);
  }

  function upload(field, file) {
    if (!file) return;
    if (!/\.pdf$/i.test(file.name) || file.type && file.type !== 'application/pdf') {
      update(field, 0, document.documentElement.lang === 'en' ? 'PDF files only' : 'يُسمح بملفات PDF فقط', file.name, 'error');
      return;
    }
    if (file.size > 50 * 1024 * 1024) {
      update(field, 0, document.documentElement.lang === 'en' ? 'The file exceeds 50 MB' : 'حجم الملف يتجاوز 50 ميجابايت', '', 'error');
      return;
    }
    field.addClass('is-uploading');
    field.find('button').prop('disabled', true);
    update(field, 1, config.labels && config.labels.uploading || 'جارٍ رفع ملف PDF…', '');
    request(field, 'vava_digital_private_pdf_upload', {}, file, function (value) {
      update(field, Math.max(2, Math.min(88, value * .88)), config.labels && config.labels.uploading || 'جارٍ رفع ملف PDF…', '');
    }).then(function (data) {
      var uid = field.attr('data-product-uid') || '';
      syncFields(uid, function (target) { renderRecord(target, data.record || {}); target.removeClass('is-uploading').find('button').prop('disabled', false); });
      poll(field);
    }).catch(function (error) {
      field.removeClass('is-uploading').find('button').prop('disabled', false);
      update(field, 0, error.message, '', 'error');
    });
  }

  $(document).on('click', '[data-private-file-select]', function (event) {
    event.preventDefault();
    $(this).closest('[data-vava-private-pdf-field]').find('.vava-private-file-input').trigger('click');
  });

  $(document).on('click keydown', '.vava-private-file-dropzone', function (event) {
    if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
    if ($(event.target).is('input')) return;
    event.preventDefault();
    $(this).find('.vava-private-file-input').trigger('click');
  });

  $(document).on('change', '.vava-private-file-input', function () {
    var field = $(this).closest('[data-vava-private-pdf-field]');
    var file = this.files && this.files[0] ? this.files[0] : null;
    if (file) upload(field, file);
    this.value = '';
  });

  $(document).on('dragenter dragover', '.vava-private-file-dropzone', function (event) { event.preventDefault(); $(this).addClass('is-dragover'); });
  $(document).on('dragleave drop', '.vava-private-file-dropzone', function (event) { event.preventDefault(); $(this).removeClass('is-dragover'); });
  $(document).on('drop', '.vava-private-file-dropzone', function (event) {
    var files = event.originalEvent && event.originalEvent.dataTransfer ? event.originalEvent.dataTransfer.files : null;
    if (files && files[0]) upload($(this).closest('[data-vava-private-pdf-field]'), files[0]);
  });

  $(document).on('click', '[data-private-file-delete]', function (event) {
    event.preventDefault();
    var field = $(this).closest('[data-vava-private-pdf-field]');
    if (!window.confirm(document.documentElement.lang === 'en' ? 'Delete this protected file?' : 'هل تريد حذف الملف المحمي؟')) return;
    request(field, 'vava_digital_private_pdf_delete').then(function () {
      var uid = field.attr('data-product-uid') || '';
      syncFields(uid, function (target) {
        target.attr('data-processing-status', 'empty');
        target.find('.vava-private-file-state').removeClass('has-file has-thumbnail').find('[data-private-file-thumbnail]').remove();
        if (!target.find('[data-private-file-icon]').length) $('<span>', { class: 'dashicons dashicons-pdf', 'data-private-file-icon': '', 'aria-hidden': 'true' }).prependTo(target.find('.vava-private-file-state'));
        target.find('[data-private-file-delete],[data-private-file-reprocess]').remove();
        target.find('[data-private-file-select]').text(document.documentElement.lang === 'en' ? 'Choose PDF' : 'اختيار ملف PDF');
        progress(target).removeClass('is-active is-complete is-error').attr('hidden', true);
      });
    }).catch(function (error) { update(field, 0, error.message, '', 'error'); });
  });

  $(document).on('click', '[data-private-file-reprocess]', function (event) {
    event.preventDefault();
    var field = $(this).closest('[data-vava-private-pdf-field]');
    request(field, 'vava_digital_private_pdf_reprocess').then(function () {
      update(field, 1, config.labels && config.labels.processing || 'جارٍ تجهيز صفحات المشاهدة المحمية…', '');
      poll(field);
    }).catch(function (error) { update(field, 0, error.message, '', 'error'); });
  });

  $('[data-vava-private-pdf-field]').each(function () {
    var field = $(this);
    var status = field.attr('data-processing-status');
    if (status === 'queued' || status === 'processing') poll(field);
  });
}(jQuery));
