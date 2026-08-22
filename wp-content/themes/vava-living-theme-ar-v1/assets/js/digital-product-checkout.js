(function () {
  'use strict';

  var root = document.querySelector('[data-vava-digital-checkout]');
  if (!root) return;

  var form = root.querySelector('.vava-digital-checkout-form');
  if (!form) return;

  var currentStep = 1;
  var stages = Array.prototype.slice.call(form.querySelectorAll('[data-checkout-stage]'));
  var indicators = Array.prototype.slice.call(root.querySelectorAll('[data-checkout-step-indicator]'));
  var result = root.querySelector('[data-checkout-result]');
  var receiptInput = form.querySelector('[data-receipt-input]');
  var receiptName = form.querySelector('[data-receipt-name]');
  var receiptDropzone = form.querySelector('[data-receipt-dropzone]');
  var receiptProgress = form.querySelector('[data-receipt-progress]');
  var isEnglish = document.documentElement.lang === 'en' || root.getAttribute('dir') === 'ltr';

  var whatsappCountry = form.elements.whatsapp_country || null;
  var whatsappLocal = form.elements.whatsapp_local || null;
  var whatsappCombined = form.elements.whatsapp || null;
  var countryPicker = form.querySelector('[data-country-picker]');
  var countryTrigger = countryPicker && countryPicker.querySelector('[data-country-trigger]');
  var countryMenu = countryPicker && countryPicker.querySelector('[data-country-menu]');
  var countrySearch = countryPicker && countryPicker.querySelector('[data-country-search]');
  var countryOptions = countryPicker ? Array.prototype.slice.call(countryPicker.querySelectorAll('[data-country-option]')) : [];
  var countrySelectedFlag = countryPicker && countryPicker.querySelector('[data-country-selected-flag]');
  var countrySelectedName = countryPicker && countryPicker.querySelector('[data-country-selected-name]');
  var countrySelectedDial = countryPicker && countryPicker.querySelector('[data-country-selected-dial]');
  var digitMap = { '٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9','۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9' };

  function phoneDigits(value) {
    return String(value || '').replace(/[٠-٩۰-۹]/g, function (digit) { return digitMap[digit] || ''; }).replace(/\D+/g, '');
  }

  function selectedDialDigits() {
    if (!whatsappCountry) return '';
    var option = whatsappCountry.options[whatsappCountry.selectedIndex];
    return phoneDigits(option ? option.getAttribute('data-dial') : '');
  }

  function updateCountryPicker() {
    if (!countryPicker || !whatsappCountry) return;
    var selected = null;
    countryOptions.forEach(function (option) {
      var active = option.getAttribute('data-iso') === whatsappCountry.value;
      option.setAttribute('aria-selected', active ? 'true' : 'false');
      if (active) selected = option;
    });
    if (!selected && countryOptions.length) selected = countryOptions[0];
    if (!selected) return;
    var flag = selected.querySelector('img');
    var name = selected.querySelector('span');
    var dial = selected.querySelector('b');
    if (countrySelectedFlag && flag) {
      countrySelectedFlag.src = flag.src;
      countrySelectedFlag.alt = '';
    }
    if (countrySelectedName && name) countrySelectedName.textContent = name.textContent;
    if (countrySelectedDial && dial) countrySelectedDial.textContent = dial.textContent;
  }

  function closeCountryPicker(focusTrigger) {
    if (!countryPicker || !countryMenu || !countryTrigger) return;
    countryPicker.classList.remove('is-open');
    countryMenu.hidden = true;
    countryTrigger.setAttribute('aria-expanded', 'false');
    if (countrySearch) {
      countrySearch.value = '';
      countryOptions.forEach(function (option) { option.hidden = false; });
    }
    if (focusTrigger) countryTrigger.focus();
  }

  function openCountryPicker() {
    if (!countryPicker || !countryMenu || !countryTrigger) return;
    countryPicker.classList.add('is-open');
    countryMenu.hidden = false;
    countryTrigger.setAttribute('aria-expanded', 'true');
    window.requestAnimationFrame(function () {
      if (countrySearch) countrySearch.focus();
      var selected = countryOptions.filter(function (option) { return option.getAttribute('aria-selected') === 'true'; })[0];
      if (selected) selected.scrollIntoView({ block: 'nearest' });
    });
  }

  function chooseCountry(option) {
    if (!option || !whatsappCountry) return;
    whatsappCountry.value = option.getAttribute('data-iso') || '';
    updateCountryPicker();
    syncWhatsAppField();
    whatsappCountry.dispatchEvent(new Event('change', { bubbles: true }));
    closeCountryPicker(true);
  }

  function initCountryPicker() {
    if (!countryPicker || !countryTrigger || !countryMenu || !whatsappCountry) return;
    updateCountryPicker();
    countryTrigger.addEventListener('click', function () {
      if (countryMenu.hidden) openCountryPicker();
      else closeCountryPicker(false);
    });
    countryOptions.forEach(function (option) {
      option.addEventListener('click', function () { chooseCountry(option); });
    });
    if (countrySearch) {
      countrySearch.addEventListener('input', function () {
        var query = String(countrySearch.value || '').trim().toLowerCase();
        countryOptions.forEach(function (option) {
          option.hidden = Boolean(query) && String(option.getAttribute('data-search') || '').indexOf(query) === -1;
        });
      });
      countrySearch.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowDown') {
          event.preventDefault();
          var firstVisible = countryOptions.filter(function (option) { return !option.hidden; })[0];
          if (firstVisible) firstVisible.focus();
        }
      });
    }
    countryMenu.addEventListener('keydown', function (event) {
      var visible = countryOptions.filter(function (option) { return !option.hidden; });
      var index = visible.indexOf(document.activeElement);
      if (event.key === 'Escape') {
        event.preventDefault();
        closeCountryPicker(true);
      } else if (event.key === 'ArrowDown' && index >= 0) {
        event.preventDefault();
        if (visible[Math.min(index + 1, visible.length - 1)]) visible[Math.min(index + 1, visible.length - 1)].focus();
      } else if (event.key === 'ArrowUp' && index >= 0) {
        event.preventDefault();
        if (visible[Math.max(index - 1, 0)]) visible[Math.max(index - 1, 0)].focus();
      }
    });
    document.addEventListener('click', function (event) {
      if (countryPicker && !countryPicker.contains(event.target)) closeCountryPicker(false);
    });
  }

  function syncWhatsAppField() {
    if (!whatsappLocal || !whatsappCombined) return '';
    var localDigits = phoneDigits(whatsappLocal.value).slice(0, 15);
    if (localDigits.indexOf('00') === 0) localDigits = localDigits.slice(2);
    var dialDigits = selectedDialDigits();
    if (dialDigits && localDigits.indexOf(dialDigits) === 0) localDigits = localDigits.slice(dialDigits.length);
    whatsappLocal.value = localDigits;
    var nationalDigits = localDigits.replace(/^0+/, '');
    var fullDigits = dialDigits + nationalDigits;
    var maxLocalLength = Math.max(6, 15 - dialDigits.length);
    var valid = nationalDigits.length >= 6 && nationalDigits.length <= maxLocalLength && fullDigits.length <= 15;
    whatsappLocal.setCustomValidity(localDigits && !valid ? (isEnglish ? 'Enter a valid phone number without the country code.' : 'اكتب رقم هاتف صحيح بدون كود الدولة.') : '');
    whatsappCombined.value = valid ? '+' + fullDigits : '';
    return whatsappCombined.value;
  }

  function formatBytes(bytes) {
    var value = Number(bytes || 0);
    if (!value) return '—';
    var units = ['B', 'KB', 'MB', 'GB'];
    var power = Math.min(units.length - 1, Math.floor(Math.log(value) / Math.log(1024)));
    return (value / Math.pow(1024, power)).toFixed(power ? 1 : 0) + ' ' + units[power];
  }

  function updateReceiptProgress(percent, label, meta, state) {
    if (!receiptProgress) return;
    percent = Math.max(0, Math.min(100, Number(percent || 0)));
    receiptProgress.hidden = false;
    receiptProgress.classList.add('is-active');
    receiptProgress.classList.remove('is-error', 'is-complete');
    if (state === 'error') receiptProgress.classList.add('is-error');
    if (state === 'complete') receiptProgress.classList.add('is-complete');
    var bar = receiptProgress.querySelector('[data-receipt-progress-bar]');
    var pct = receiptProgress.querySelector('[data-receipt-progress-percent]');
    var labelNode = receiptProgress.querySelector('[data-receipt-progress-label]');
    var metaNode = receiptProgress.querySelector('[data-receipt-progress-meta]');
    if (bar) bar.style.width = percent + '%';
    if (pct) pct.textContent = Math.round(percent) + '%';
    if (labelNode && label) labelNode.textContent = label;
    if (metaNode && typeof meta !== 'undefined') metaNode.textContent = meta || '';
  }

  function showStep(step) {
    currentStep = step;
    root.setAttribute('data-current-step', String(step));
    stages.forEach(function (stage) {
      var active = Number(stage.getAttribute('data-checkout-stage')) === step;
      stage.hidden = !active;
      stage.classList.toggle('is-active', active);
    });
    indicators.forEach(function (item) {
      var number = Number(item.getAttribute('data-checkout-step-indicator'));
      item.classList.toggle('is-active', number === step);
      item.classList.toggle('is-complete', number < step);
    });
    if (step === 3) updateReview();
    window.scrollTo({ top: Math.max(0, root.offsetTop - 90), behavior: 'smooth' });
  }

  function fieldsForStep(step) {
    var stage = form.querySelector('[data-checkout-stage="' + step + '"]');
    return stage ? Array.prototype.slice.call(stage.querySelectorAll('input,select,textarea')) : [];
  }

  function validateStep(step) {
    if (step === 1) syncWhatsAppField();
    var valid = true;
    fieldsForStep(step).forEach(function (field) {
      if (field.disabled || field.type === 'hidden') return;
      if (!field.checkValidity()) {
        valid = false;
        field.classList.add('has-error');
      } else {
        field.classList.remove('has-error');
      }
    });
    if (step === 1 && countryPicker) {
      countryPicker.classList.toggle('is-invalid', !whatsappCountry || !whatsappCountry.value);
    }
    if (!valid) {
      var first = form.querySelector('[data-checkout-stage="' + step + '"] :invalid');
      if (first) first.focus();
    }
    return valid;
  }

  function fieldValue(name) {
    var field = form.elements[name];
    if (!field) return '—';
    if (typeof RadioNodeList !== 'undefined' && field instanceof RadioNodeList) return field.value || '—';
    return field.value || '—';
  }

  function reviewValue(key) {
    if (key === 'previous') {
      var previous = fieldValue('previous');
      if (previous === 'yes') return isEnglish ? 'Yes' : 'نعم';
      if (previous === 'no') return isEnglish ? 'No' : 'لا';
      return '—';
    }
    return fieldValue(key);
  }

  function updateReview() {
    syncWhatsAppField();
    Array.prototype.slice.call(form.querySelectorAll('[data-review]')).forEach(function (node) {
      var key = node.getAttribute('data-review');
      if (key === 'receipt') {
        node.textContent = receiptInput && receiptInput.files && receiptInput.files[0] ? receiptInput.files[0].name : '—';
      } else {
        node.textContent = reviewValue(key);
      }
    });
  }

  root.addEventListener('click', function (event) {
    var next = event.target.closest('[data-checkout-next]');
    if (next) {
      var target = Number(next.getAttribute('data-checkout-next'));
      if (validateStep(currentStep)) showStep(target);
      return;
    }
    var back = event.target.closest('[data-checkout-back]');
    if (back) showStep(Number(back.getAttribute('data-checkout-back')));
  });

  form.addEventListener('input', function (event) {
    if (event.target === whatsappLocal) syncWhatsAppField();
    event.target.classList.remove('has-error');
  });

  form.addEventListener('change', function (event) {
    if (event.target === whatsappCountry) {
      updateCountryPicker();
      syncWhatsAppField();
      if (countryPicker) countryPicker.classList.remove('is-invalid');
    }
  });

  if (receiptInput && receiptName) {
    receiptInput.addEventListener('change', function () {
      var file = receiptInput.files && receiptInput.files[0] ? receiptInput.files[0] : null;
      receiptName.textContent = file ? file.name : receiptName.getAttribute('data-empty-label') || receiptName.textContent;
      if (receiptProgress) {
        receiptProgress.classList.remove('is-active', 'is-error', 'is-complete');
        receiptProgress.hidden = true;
      }
    });
  }

  if (receiptDropzone && receiptInput) {
    ['dragenter', 'dragover'].forEach(function (name) {
      receiptDropzone.addEventListener(name, function (event) {
        event.preventDefault();
        receiptDropzone.classList.add('is-dragover');
      });
    });
    ['dragleave', 'drop'].forEach(function (name) {
      receiptDropzone.addEventListener(name, function (event) {
        event.preventDefault();
        receiptDropzone.classList.remove('is-dragover');
      });
    });
    receiptDropzone.addEventListener('drop', function (event) {
      if (!event.dataTransfer || !event.dataTransfer.files || !event.dataTransfer.files.length) return;
      try {
        receiptInput.files = event.dataTransfer.files;
        receiptInput.dispatchEvent(new Event('change', { bubbles: true }));
      } catch (e) {
        receiptInput.click();
      }
    });
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    syncWhatsAppField();
    if (!validateStep(3)) return;

    var button = form.querySelector('.vava-digital-submit');
    var idle = button ? button.getAttribute('data-submit-label') : '';
    var loading = button ? button.getAttribute('data-loading-label') : '';
    if (button) {
      button.disabled = true;
      button.textContent = loading || idle;
    }

    if (result) { result.hidden = true; result.setAttribute('aria-hidden', 'true'); }
    var payload = new FormData(form);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', (window.VAVA_DIGITAL_CHECKOUT && window.VAVA_DIGITAL_CHECKOUT.ajaxUrl) || '/wp-admin/admin-ajax.php', true);
    xhr.withCredentials = true;
    if (xhr.upload) {
      xhr.upload.addEventListener('progress', function (progressEvent) {
        if (!progressEvent.lengthComputable) return;
        var percent = Math.max(1, Math.min(96, progressEvent.loaded / progressEvent.total * 96));
        var file = receiptInput && receiptInput.files && receiptInput.files[0] ? receiptInput.files[0] : null;
        updateReceiptProgress(percent, isEnglish ? 'Uploading receipt and order…' : 'جارٍ رفع الإيصال وإرسال الطلب…', file ? file.name + ' · ' + formatBytes(file.size) : '');
      });
    }
    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;
      var response = null;
      try { response = JSON.parse(xhr.responseText || '{}'); } catch (e) {}
      if (xhr.status >= 200 && xhr.status < 300 && response && response.success) {
        updateReceiptProgress(100, isEnglish ? 'Uploaded successfully' : 'تم الرفع بنجاح', '', 'complete');
        form.hidden = true;
        if (result) {
          result.hidden = false;
          result.setAttribute('aria-hidden', 'false');
          var title = result.querySelector('[data-result-title]');
          var message = result.querySelector('[data-result-message]');
          var account = result.querySelector('[data-result-account]');
          if (title && response.data.title) title.textContent = response.data.title;
          if (message && response.data.message) message.textContent = response.data.message;
          if (account && response.data.accountUrl) account.href = response.data.accountUrl;
        }
      } else {
        var messageText = response && response.data && response.data.message ? response.data.message : (isEnglish ? 'Could not submit the order.' : 'تعذر إرسال الطلب.');
        updateReceiptProgress(0, messageText, '', 'error');
        window.alert(messageText);
        if (button) { button.disabled = false; button.textContent = idle; }
      }
    };
    xhr.onerror = function () {
      var messageText = isEnglish ? 'Could not submit the order.' : 'تعذر إرسال الطلب.';
      updateReceiptProgress(0, messageText, '', 'error');
      window.alert(messageText);
      if (button) { button.disabled = false; button.textContent = idle; }
    };
    xhr.send(payload);
  });

  if (result) { result.hidden = true; result.setAttribute('aria-hidden', 'true'); }
  if (receiptProgress) { receiptProgress.hidden = true; receiptProgress.classList.remove('is-active', 'is-error', 'is-complete'); }
  initCountryPicker();
  syncWhatsAppField();
  showStep(1);
}());
