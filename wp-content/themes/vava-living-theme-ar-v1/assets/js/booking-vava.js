/* VAVA_BOOKING_WIZARD_PAYMENT_REVIEW_V1R5 */
/* VAVA_BOOKING_WIZARD_B1_B2_V1R1 */
(function () {
  'use strict';

  const root = document.querySelector('[data-booking-root]');
  const pending = root && root.querySelector('[data-booking-pending]');
  if (pending) {
    window.setTimeout(() => window.location.reload(), 5000);
    return;
  }

  const form = root && root.querySelector('[data-booking-form]');
  if (!form || typeof VAVA_BOOKING === 'undefined') return;

  const steps = Array.from(form.querySelectorAll('[data-booking-step]'));
  const progress = Array.from(root.querySelectorAll('[data-progress-step]'));
  const progressWrap = root.querySelector('.vava-booking-progress');
  const progressHome = root.querySelector('[data-booking-progress-home]');
  const progressSlots = {};
  form.querySelectorAll('[data-booking-progress-slot]').forEach((slot) => { progressSlots[Number(slot.dataset.bookingProgressSlot)] = slot; });
  const datesWrap = form.querySelector('[data-booking-dates]');
  const timesWrap = form.querySelector('[data-booking-times]');
  const monthLabel = form.querySelector('[data-booking-month]');
  const datePrev = form.querySelector('[data-booking-date-prev]');
  const dateNext = form.querySelector('[data-booking-date-next]');
  const datePickerButton = form.querySelector('[data-booking-date-picker-button]');
  const datePicker = form.querySelector('[data-booking-date-picker]');
  const errorBox = form.querySelector('[data-booking-error]');
  const finalErrorBox = form.querySelector('[data-booking-final-error]');
  const successBox = root.querySelector('[data-booking-success]');
  const successClose = root.querySelector('[data-booking-success-close]');
  const nextToPayment = form.querySelector('[data-next-schedule]');
  const nextToReview = form.querySelector('[data-next-payment]');
  const submitButton = form.querySelector('[data-booking-submit]');
  const receiptProgress = form.querySelector('[data-booking-upload-progress]');
  const receiptProgressLabel = receiptProgress && receiptProgress.querySelector('[data-booking-upload-label]');
  const receiptProgressPercent = receiptProgress && receiptProgress.querySelector('[data-booking-upload-percent]');
  const receiptProgressBar = receiptProgress && receiptProgress.querySelector('[data-booking-upload-bar]');
  const receiptProgressMeta = receiptProgress && receiptProgress.querySelector('[data-booking-upload-meta]');
  const heroTitle = root.querySelector('[data-booking-hero-title]');
  const heroIntro = root.querySelector('[data-booking-hero-intro]');
  const isEnglish = VAVA_BOOKING.lang === 'en';
  const maxSteps = Math.max(4, Number(form.dataset.maxSteps || 4));
  const scheduleStep = Math.max(2, Number(form.dataset.scheduleStep || 2));
  const paymentStep = Math.max(3, Number(form.dataset.paymentStep || 3));
  const reviewStep = Math.max(4, Number(form.dataset.reviewStep || 4));
  const questionnaireMode = String(form.dataset.questionnaireMode || 'none');
  const questionnaireStage = form.querySelector('[data-questionnaire-stage]');
  const questionnaireTypeInput = form.querySelector('[data-questionnaire-type-input]');
  const questionnaireError = form.querySelector('[data-questionnaire-error]');
  const questionnaireGroups = questionnaireStage ? Array.from(questionnaireStage.querySelectorAll('[data-questionnaire-group]')) : [];
  const questionnaireButtons = questionnaireStage ? Array.from(questionnaireStage.querySelectorAll('[data-questionnaire-group-button]')) : [];
  const questionnaireReviewCard = form.querySelector('[data-review-questionnaire-card]');
  const questionnaireReviewSummary = form.querySelector('[data-review-questionnaire-summary]');
  let questionnaireGroupIndex = 0;
  let questionnaireCompleted = false;
  let questionnaireRequired = questionnaireMode === 'midpoint';
  const whatsappCountry = form.elements.whatsapp_country || null;
  const whatsappLocal = form.elements.whatsapp_local || null;
  const whatsappCombined = form.elements.whatsapp || null;
  const countryPicker = form.querySelector('[data-country-picker]');
  const countryTrigger = countryPicker && countryPicker.querySelector('[data-country-trigger]');
  const countryMenu = countryPicker && countryPicker.querySelector('[data-country-menu]');
  const countrySearch = countryPicker && countryPicker.querySelector('[data-country-search]');
  const countryOptions = countryPicker ? Array.from(countryPicker.querySelectorAll('[data-country-option]')) : [];
  const countrySelectedFlag = countryPicker && countryPicker.querySelector('[data-country-selected-flag]');
  const countrySelectedName = countryPicker && countryPicker.querySelector('[data-country-selected-name]');
  const countrySelectedDial = countryPicker && countryPicker.querySelector('[data-country-selected-dial]');
  const locale = VAVA_BOOKING.locale || (isEnglish ? 'en-US' : 'ar-SA-u-nu-latn');
  const dayKeys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
  const stateKey = `vavaBooking:v21:${VAVA_BOOKING.service || 'unknown'}:${VAVA_BOOKING.lang || 'ar'}`;
  const pad = (value) => String(value).padStart(2, '0');
  const digitMap = { '٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9','۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9' };

  function formatUploadBytes(bytes) {
    const value = Number(bytes || 0);
    if (!value) return '—';
    const units = ['B', 'KB', 'MB', 'GB'];
    const power = Math.min(units.length - 1, Math.floor(Math.log(value) / Math.log(1024)));
    return `${(value / Math.pow(1024, power)).toFixed(power ? 1 : 0)} ${units[power]}`;
  }

  function updateReceiptProgress(percent, label, meta, state = '') {
    if (!receiptProgress) return;
    const value = Math.max(0, Math.min(100, Number(percent || 0)));
    receiptProgress.hidden = false;
    receiptProgress.classList.toggle('is-complete', state === 'complete');
    receiptProgress.classList.toggle('is-error', state === 'error');
    if (receiptProgressLabel && label) receiptProgressLabel.textContent = label;
    if (receiptProgressPercent) receiptProgressPercent.textContent = `${Math.round(value)}%`;
    if (receiptProgressBar) receiptProgressBar.style.width = `${value}%`;
    if (receiptProgressMeta && typeof meta !== 'undefined') receiptProgressMeta.textContent = meta || '';
  }

  function submitBookingRequest(data, onProgress) {
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', VAVA_BOOKING.ajaxUrl, true);
      xhr.withCredentials = true;
      xhr.upload.addEventListener('progress', (uploadEvent) => {
        if (uploadEvent.lengthComputable && typeof onProgress === 'function') onProgress(uploadEvent.loaded / uploadEvent.total * 100);
      });
      xhr.onreadystatechange = () => {
        if (xhr.readyState !== 4) return;
        let json = null;
        try { json = JSON.parse(xhr.responseText || '{}'); } catch (e) {}
        if (xhr.status >= 200 && xhr.status < 300 && json) resolve(json);
        else reject(new Error(json && json.data && json.data.message ? json.data.message : VAVA_BOOKING.error));
      };
      xhr.onerror = () => reject(new Error(VAVA_BOOKING.error));
      xhr.send(data);
    });
  }

  function phoneDigits(value) {
    return String(value || '').replace(/[٠-٩۰-۹]/g, (digit) => digitMap[digit] || '').replace(/\D+/g, '');
  }

  function selectedDialDigits() {
    if (!whatsappCountry) return '';
    const option = whatsappCountry.options[whatsappCountry.selectedIndex];
    return phoneDigits(option ? option.dataset.dial : '');
  }

  function selectedCountryOption() {
    if (!whatsappCountry) return null;
    return whatsappCountry.options[whatsappCountry.selectedIndex] || null;
  }

  function updateCountryPicker() {
    if (!countryPicker || !whatsappCountry) return;
    const selected = countryOptions.find((option) => option.dataset.iso === whatsappCountry.value) || countryOptions[0] || null;
    countryOptions.forEach((option) => option.setAttribute('aria-selected', option === selected ? 'true' : 'false'));
    if (!selected) return;
    const flag = selected.querySelector('img');
    const name = selected.querySelector('span');
    const dial = selected.querySelector('b');
    if (countrySelectedFlag && flag) {
      countrySelectedFlag.src = flag.src;
      countrySelectedFlag.alt = '';
    }
    if (countrySelectedName && name) countrySelectedName.textContent = name.textContent;
    if (countrySelectedDial && dial) countrySelectedDial.textContent = dial.textContent;
  }

  function closeCountryPicker({ focusTrigger = false } = {}) {
    if (!countryPicker || !countryMenu || !countryTrigger) return;
    countryPicker.classList.remove('is-open');
    countryMenu.hidden = true;
    countryTrigger.setAttribute('aria-expanded', 'false');
    if (countrySearch) {
      countrySearch.value = '';
      countryOptions.forEach((option) => { option.hidden = false; });
    }
    if (focusTrigger) countryTrigger.focus();
  }

  function openCountryPicker() {
    if (!countryPicker || !countryMenu || !countryTrigger) return;
    countryPicker.classList.add('is-open');
    countryMenu.hidden = false;
    countryTrigger.setAttribute('aria-expanded', 'true');
    window.requestAnimationFrame(() => {
      if (countrySearch) countrySearch.focus();
      const selected = countryOptions.find((option) => option.getAttribute('aria-selected') === 'true');
      if (selected) selected.scrollIntoView({ block: 'nearest' });
    });
  }

  function chooseCountry(option) {
    if (!option || !whatsappCountry) return;
    whatsappCountry.value = option.dataset.iso || '';
    updateCountryPicker();
    syncWhatsAppField();
    whatsappCountry.dispatchEvent(new Event('change', { bubbles: true }));
    closeCountryPicker({ focusTrigger: true });
  }

  function initCountryPicker() {
    if (!countryPicker || !countryTrigger || !countryMenu || !whatsappCountry) return;
    updateCountryPicker();
    countryTrigger.addEventListener('click', () => {
      if (countryMenu.hidden) openCountryPicker();
      else closeCountryPicker();
    });
    countryOptions.forEach((option) => {
      option.addEventListener('click', () => chooseCountry(option));
    });
    if (countrySearch) {
      countrySearch.addEventListener('input', () => {
        const query = String(countrySearch.value || '').trim().toLowerCase();
        countryOptions.forEach((option) => {
          option.hidden = Boolean(query) && !String(option.dataset.search || '').includes(query);
        });
      });
      countrySearch.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
          event.preventDefault();
          const firstVisible = countryOptions.find((option) => !option.hidden);
          if (firstVisible) firstVisible.focus();
        }
      });
    }
    countryMenu.addEventListener('keydown', (event) => {
      const visible = countryOptions.filter((option) => !option.hidden);
      const current = document.activeElement;
      const index = visible.indexOf(current);
      if (event.key === 'Escape') {
        event.preventDefault();
        closeCountryPicker({ focusTrigger: true });
      } else if (event.key === 'ArrowDown' && index >= 0) {
        event.preventDefault();
        visible[Math.min(index + 1, visible.length - 1)]?.focus();
      } else if (event.key === 'ArrowUp' && index >= 0) {
        event.preventDefault();
        visible[Math.max(index - 1, 0)]?.focus();
      }
    });
    document.addEventListener('click', (event) => {
      if (!countryPicker.contains(event.target)) closeCountryPicker();
    });
  }

  function syncWhatsAppField() {
    if (!whatsappLocal || !whatsappCombined) return '';
    let localDigits = phoneDigits(whatsappLocal.value).slice(0, 15);
    if (localDigits.startsWith('00')) localDigits = localDigits.slice(2);
    const dialDigits = selectedDialDigits();
    if (dialDigits && localDigits.startsWith(dialDigits)) localDigits = localDigits.slice(dialDigits.length);
    whatsappLocal.value = localDigits;
    const nationalDigits = localDigits.replace(/^0+/, '');
    const fullDigits = `${dialDigits}${nationalDigits}`;
    const maxLocalLength = Math.max(6, 15 - dialDigits.length);
    const valid = nationalDigits.length >= 6 && nationalDigits.length <= maxLocalLength && fullDigits.length <= 15;
    whatsappLocal.setCustomValidity(localDigits && !valid ? (isEnglish ? 'Enter a valid phone number without the country code.' : 'اكتب رقم هاتف صحيح بدون كود الدولة.') : '');
    whatsappCombined.value = valid ? `+${fullDigits}` : '';
    return whatsappCombined.value;
  }

  let currentStep = 1;
  let maxReachedStep = 1;
  let selectedDate = '';
  let selectedTime = '';
  let selectedTimeLabel = '';
  let rangeStart = null;
  let slotsRequestId = 0;
  let datesRequestId = 0;

  function parseIsoDate(value) {
    const parts = String(value || '').split('-').map(Number);
    if (parts.length !== 3 || parts.some((part) => !Number.isFinite(part))) return null;
    const date = new Date(parts[0], parts[1] - 1, parts[2], 12, 0, 0, 0);
    return Number.isNaN(date.getTime()) ? null : date;
  }

  function isoDate(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
  }

  function addDays(date, count) {
    const next = new Date(date.getFullYear(), date.getMonth(), date.getDate() + count, 12, 0, 0, 0);
    return next;
  }

  function clampDate(date, min, max) {
    if (date < min) return new Date(min.getTime());
    if (date > max) return new Date(max.getTime());
    return new Date(date.getTime());
  }

  // V4R7R2: never abort the booking UI when an older cache/localization omits `today`.
  const configuredToday = parseIsoDate(VAVA_BOOKING.today);
  const browserNow = new Date();
  const serverToday = configuredToday || new Date(
    browserNow.getFullYear(),
    browserNow.getMonth(),
    browserNow.getDate(),
    12, 0, 0, 0
  );
  const maxDays = Math.max(1, Number(VAVA_BOOKING.maxDays || 60));
  const serverMaxDate = addDays(serverToday, maxDays);
  rangeStart = new Date(serverToday.getTime());

  function readState() {
    try {
      return JSON.parse(sessionStorage.getItem(stateKey) || '{}');
    } catch (error) {
      return {};
    }
  }

  function saveState() {
    const fields = {};
    ['name', 'email', 'whatsapp', 'whatsapp_country', 'whatsapp_local', 'previous', 'notes', 'bank_transfer_name', 'bank_from_bank', 'bank_from_account', 'bank_reference', 'bank_transfer_date', 'bank_transfer_time', 'bank_amount'].forEach((name) => {
      if (form.elements[name]) fields[name] = form.elements[name].value;
    });
    fields.terms = Boolean(form.elements.terms && form.elements.terms.checked);
    fields.payment_method = form.elements.payment_method ? (form.elements.payment_method.value || '') : '';
    const questionnaire = Array.from(form.querySelectorAll('[name^="questionnaire_answers"]')).map((control) => ({
      name: control.name,
      value: control.value,
      type: control.type,
      checked: Boolean(control.checked),
    }));
    sessionStorage.setItem(stateKey, JSON.stringify({
      step: currentStep,
      maxStep: maxReachedStep,
      date: selectedDate,
      time: selectedTime,
      timeLabel: selectedTimeLabel,
      rangeStart: rangeStart ? isoDate(rangeStart) : '',
      fields,
      questionnaire,
      questionnaireCompleted,
      questionnaireGroupIndex,
    }));
  }

  function restoreFields(state) {
    Object.entries(state.fields || {}).forEach(([name, value]) => {
      if (!form.elements[name]) return;
      if (name === 'terms') {
        form.elements[name].checked = Boolean(value);
        return;
      }
      if (name === 'payment_method') {
        const radio = form.querySelector(`[name="payment_method"][value="${CSS.escape(String(value))}"]`);
        if (radio && !radio.disabled) radio.checked = true;
        return;
      }
      form.elements[name].value = value;
    });
    (state.questionnaire || []).forEach((saved) => {
      const candidates = Array.from(form.querySelectorAll(`[name="${CSS.escape(String(saved.name || ''))}"]`));
      candidates.forEach((control) => {
        if (control.type === 'radio' || control.type === 'checkbox') {
          if (String(control.value) === String(saved.value)) control.checked = Boolean(saved.checked);
        } else if (candidates.length === 1) control.value = saved.value || '';
      });
    });
    questionnaireCompleted = Boolean(state.questionnaireCompleted);
    questionnaireGroupIndex = Math.max(0, Number(state.questionnaireGroupIndex || 0));
    if (whatsappLocal && !whatsappLocal.value && whatsappCombined && whatsappCombined.value) {
      const dialDigits = selectedDialDigits();
      const restoredDigits = phoneDigits(whatsappCombined.value);
      whatsappLocal.value = dialDigits && restoredDigits.startsWith(dialDigits) ? restoredDigits.slice(dialDigits.length) : restoredDigits;
    }
    syncWhatsAppField();
  }

  function positionProgress(step) {
    if (!progressWrap) return;
    const slot = progressSlots[step];
    if (slot) {
      slot.appendChild(progressWrap);
      progressWrap.classList.add('is-in-stage');
      return;
    }
    if (progressHome) {
      progressHome.insertAdjacentElement('afterend', progressWrap);
      progressWrap.classList.remove('is-in-stage');
    }
  }

  function updateHero(step) {
    if (heroTitle) heroTitle.textContent = heroTitle.dataset[`step${step}Title`] || heroTitle.textContent;
    if (heroIntro) heroIntro.textContent = heroIntro.dataset[`step${step}Intro`] || heroIntro.textContent;
  }

  function showStep(step, scroll = true) {
    currentStep = Math.min(maxSteps, Math.max(1, Number(step) || 1));
    maxReachedStep = Math.max(maxReachedStep, currentStep);
    root.dataset.currentStep = String(currentStep);
    updateHero(currentStep);

    steps.forEach((panel) => {
      const number = Number(panel.dataset.bookingStep);
      const isActive = number === currentStep;
      panel.hidden = !isActive;
      panel.classList.toggle('is-active', isActive);
      panel.classList.toggle('is-complete', number < currentStep);
      panel.classList.toggle('is-locked', number > maxReachedStep);
      panel.setAttribute('aria-current', isActive ? 'step' : 'false');
      panel.setAttribute('aria-hidden', isActive ? 'false' : 'true');
    });

    positionProgress(currentStep);

    progress.forEach((item) => {
      const number = Number(item.dataset.progressStep);
      item.classList.toggle('is-active', number === currentStep);
      item.classList.toggle('is-complete', number < currentStep);
      item.disabled = number > maxReachedStep;
    });

    if (currentStep === scheduleStep && !selectedDate) selectFirstAvailableDate();
    if (currentStep === 2 && questionnaireStage) { prefillQuestionnaire(); showQuestionnaireGroup(questionnaireGroupIndex, false); }
    updateSummary();
    saveState();

    if (scroll) {
      const active = form.querySelector(`[data-booking-step="${currentStep}"]`);
      const target = currentStep >= 3 ? root.querySelector('.vava-booking-progress') : (window.matchMedia('(min-width: 1101px)').matches ? root : active);
      window.setTimeout(() => target && target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 30);
    }
  }

  function previousAnswer() {
    const checked = form.querySelector('[name="previous"]:checked');
    return checked ? String(checked.value || '') : '';
  }

  function refreshQuestionnaireRequirement() {
    questionnaireRequired = questionnaireMode === 'midpoint' || (questionnaireMode === 'beginning' && previousAnswer() === 'no');
    if (questionnaireStage) {
      questionnaireStage.dataset.questionnaireRequired = questionnaireRequired ? '1' : '0';
      questionnaireStage.querySelectorAll('input, select, textarea, button').forEach((control) => {
        if (control === questionnaireTypeInput) return;
        if (control.matches('[data-questionnaire-prev], [data-questionnaire-next], [data-questionnaire-finish], [data-questionnaire-group-button]')) return;
        control.disabled = !questionnaireRequired;
      });
    }
    if (questionnaireTypeInput) {
      questionnaireTypeInput.disabled = !questionnaireRequired;
      questionnaireTypeInput.value = questionnaireRequired ? questionnaireMode : 'none';
    }
    let visibleNumber = 0;
    progress.forEach((item) => {
      const raw = Number(item.dataset.progressStep);
      const hidden = Boolean(questionnaireStage) && raw === 2 && !questionnaireRequired;
      item.hidden = hidden;
      if (!hidden) {
        visibleNumber += 1;
        const number = item.querySelector('span');
        if (number) number.textContent = String(visibleNumber);
      }
    });
    updateQuestionnaireSummary();
    if (!questionnaireRequired && currentStep === 2) showStep(1, false);
  }

  function prefillQuestionnaire() {
    if (!questionnaireStage) return;
    const fullName = questionnaireStage.querySelector('[data-questionnaire-field="full_name"]');
    if (fullName && !fullName.value && form.elements.name) fullName.value = form.elements.name.value || '';
    const cityCountry = questionnaireStage.querySelector('[data-questionnaire-field="city_country"]');
    if (cityCountry && !cityCountry.value) {
      const selected = countryOptions.find((option) => option.dataset.iso === (whatsappCountry?.value || '')) || null;
      const countryName = selected?.querySelector('span')?.textContent || selectedCountryOption()?.textContent || '';
      const dialCode = selected?.querySelector('b')?.textContent || '';
      const cleanCountry = String(countryName).replace(/\s+/g, ' ').trim();
      const cleanDial = String(dialCode).replace(/\s+/g, ' ').trim();
      cityCountry.value = cleanCountry ? `${cleanCountry}${cleanDial ? ` (${cleanDial})` : ''}` : '';
    }
  }

  function questionnaireFieldValue(field) {
    if (field.type === 'radio') {
      const selected = form.querySelector(`[name="${CSS.escape(field.name)}"]:checked`);
      return selected ? selected.value : '';
    }
    if (field.type === 'checkbox') {
      return Array.from(form.querySelectorAll(`[name="${CSS.escape(field.name)}"]:checked`)).map((item) => item.value);
    }
    return field.value || '';
  }

  function validateQuestionnaireGroup(index = questionnaireGroupIndex) {
    if (!questionnaireRequired || !questionnaireGroups.length) return true;
    const group = questionnaireGroups[Math.max(0, Math.min(questionnaireGroups.length - 1, index))];
    if (!group) return true;
    let valid = true;
    let first = null;
    const required = Array.from(group.querySelectorAll('input[required], select[required], textarea[required]'));
    const checkedNames = new Set();
    required.forEach((field) => {
      if ((field.type === 'radio' || field.type === 'checkbox') && checkedNames.has(field.name)) return;
      if (field.type === 'radio' || field.type === 'checkbox') checkedNames.add(field.name);
      const value = questionnaireFieldValue(field);
      const fieldValid = Array.isArray(value) ? value.length > 0 : String(value || '').trim() !== '';
      const wrap = field.closest('[data-questionnaire-field-wrap]') || field.closest('label');
      if (wrap) wrap.classList.toggle('is-invalid', !fieldValid);
      if (!fieldValid && !first) first = field;
      valid = valid && fieldValid;
    });
    if (!valid) {
      if (questionnaireError) {
        questionnaireError.textContent = isEnglish ? 'Please complete the required questionnaire fields.' : 'يرجى استكمال الحقول الإلزامية في الاستبيان.';
        questionnaireError.classList.add('is-visible');
      }
      if (first) first.focus();
    } else if (questionnaireError) {
      questionnaireError.textContent = '';
      questionnaireError.classList.remove('is-visible');
    }
    return valid;
  }

  function allQuestionnaireGroupsValid() {
    if (!questionnaireRequired) return true;
    for (let i = 0; i < questionnaireGroups.length; i += 1) {
      if (!validateQuestionnaireGroup(i)) { showQuestionnaireGroup(i); return false; }
    }
    return true;
  }

  function showQuestionnaireGroup(index, scroll = true) {
    if (!questionnaireGroups.length) return;
    questionnaireGroupIndex = Math.max(0, Math.min(questionnaireGroups.length - 1, Number(index) || 0));
    questionnaireGroups.forEach((group, groupIndex) => {
      const active = groupIndex === questionnaireGroupIndex;
      group.hidden = !active;
      group.classList.toggle('is-active', active);
      const bar = group.querySelector('.vava-questionnaire-progress b');
      const label = group.querySelector('.vava-questionnaire-progress span');
      const percent = Math.round((questionnaireGroupIndex + 1) / questionnaireGroups.length * 100);
      if (bar) bar.style.width = `${percent}%`;
      if (label) label.textContent = `${percent}%`;
    });
    questionnaireButtons.forEach((button, buttonIndex) => {
      button.classList.toggle('is-active', buttonIndex === questionnaireGroupIndex);
      button.classList.toggle('is-complete', buttonIndex < questionnaireGroupIndex || questionnaireCompleted);
    });
    updateQuestionnaireSummary();
    saveState();
    if (scroll) questionnaireStage?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function answerLabel(control) {
    const optionLabel = control.closest('label')?.querySelector('span');
    return optionLabel ? optionLabel.textContent.trim() : control.value;
  }

  function updateQuestionnaireSummary() {
    if (!questionnaireStage) return;
    const summary = questionnaireStage.querySelector('[data-questionnaire-live-summary]');
    const rows = [];
    questionnaireStage.querySelectorAll('[data-questionnaire-field-wrap]').forEach((wrap) => {
      const label = wrap.querySelector('.vava-questionnaire-label')?.textContent.replace('*', '').trim() || '';
      const controls = Array.from(wrap.querySelectorAll('[data-questionnaire-field]'));
      if (!controls.length) return;
      let value = '';
      if (controls[0].type === 'radio' || controls[0].type === 'checkbox') value = controls.filter((c) => c.checked).map(answerLabel).join('، ');
      else value = controls[0].value.trim();
      if (value) rows.push({ label, value });
    });
    if (summary) summary.innerHTML = rows.slice(0, 6).map((row) => `<div><small>${escapeHtml(row.label)}</small><b>${escapeHtml(row.value)}</b></div>`).join('');
    if (questionnaireReviewSummary) {
      questionnaireReviewSummary.innerHTML = rows.map((row) => `<div><dt>${escapeHtml(row.label)}</dt><dd>${escapeHtml(row.value)}</dd></div>`).join('');
    }
    if (questionnaireReviewCard) questionnaireReviewCard.hidden = !questionnaireRequired || !rows.length;
  }

  function escapeHtml(value) {
    const node = document.createElement('div');
    node.textContent = String(value || '');
    return node.innerHTML;
  }

  function validateStepOne() {
    const normalizedWhatsApp = syncWhatsAppField();
    let valid = true;
    let firstInvalid = null;
    const requiredFields = Array.from(form.querySelectorAll('[data-booking-step="1"] input[required]:not([type="hidden"]), [data-booking-step="1"] select[required], [data-booking-step="1"] textarea[required]'))
      .filter((field) => field !== whatsappCountry);

    requiredFields.forEach((field) => {
      const fieldValid = field.checkValidity();
      field.classList.toggle('is-invalid', !fieldValid);
      const label = field.closest('label');
      if (label) label.classList.toggle('is-invalid', !fieldValid);
      if (!fieldValid && !firstInvalid) firstInvalid = field;
      valid = valid && fieldValid;
    });

    const whatsappRequired = Boolean(whatsappLocal && whatsappLocal.required);
    const whatsappValid = !whatsappRequired || Boolean(normalizedWhatsApp);
    if (whatsappLocal) {
      whatsappLocal.classList.toggle('is-invalid', !whatsappValid);
      whatsappLocal.closest('label')?.classList.toggle('is-invalid', !whatsappValid);
      if (!whatsappValid && !firstInvalid) firstInvalid = whatsappLocal;
    }
    if (countryPicker) countryPicker.classList.toggle('is-invalid', !whatsappCountry || !whatsappCountry.value);
    valid = valid && whatsappValid && Boolean(whatsappCountry && whatsappCountry.value);

    if (!valid) {
      setError(isEnglish ? 'Please complete the required fields with valid information.' : 'يرجى استكمال الحقول الإلزامية ببيانات صحيحة.');
      if (firstInvalid) {
        firstInvalid.focus();
        window.setTimeout(() => firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' }), 20);
      }
    } else {
      setError('');
    }
    return valid;
  }

  function stepOneIsComplete() {
    syncWhatsAppField();
    const requiredFields = Array.from(form.querySelectorAll('[data-booking-step="1"] input[required]:not([type="hidden"]), [data-booking-step="1"] select[required], [data-booking-step="1"] textarea[required]'))
      .filter((field) => field !== whatsappCountry);
    return requiredFields.every((field) => field.checkValidity())
      && Boolean(whatsappCountry && whatsappCountry.value)
      && (!whatsappLocal || !whatsappLocal.required || Boolean(whatsappCombined && whatsappCombined.value));
  }

  function setError(message) {
    [errorBox, finalErrorBox].filter(Boolean).forEach((box) => {
      box.textContent = message || '';
      box.classList.toggle('is-visible', Boolean(message));
    });
  }

  function formatDate(value) {
    const date = parseIsoDate(value);
    if (!date) return '';
    return new Intl.DateTimeFormat(locale, {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    }).format(date);
  }

  function formatTime(value) {
    const [rawHour, rawMinute] = String(value || '').split(':').map(Number);
    if (!Number.isFinite(rawHour) || !Number.isFinite(rawMinute)) return value || '';
    const suffix = rawHour < 12 ? 'am' : 'pm';
    const displayHour = rawHour % 12 || 12;
    return `${displayHour}:${pad(rawMinute)} ${suffix}`;
  }

  function updateMonthLabel(date) {
    if (!monthLabel || !date) return;
    const end = addDays(date, 6);
    const monthFormatter = new Intl.DateTimeFormat(locale, { month: 'long' });
    const yearFormatter = new Intl.DateTimeFormat(locale, { year: 'numeric' });
    const startMonth = monthFormatter.format(date);
    const endMonth = monthFormatter.format(end);
    const year = yearFormatter.format(end);
    monthLabel.textContent = startMonth === endMonth ? `${startMonth} ${year}` : `${startMonth} – ${endMonth} ${year}`;
  }

  function periodForTime(value) {
    const hour = Number(String(value).split(':')[0]);
    if (hour < 12) return 'morning';
    if (hour < 16) return 'afternoon';
    return 'evening';
  }

  function periodLabel(period) {
    const labels = isEnglish
      ? { morning: 'Morning', afternoon: 'Afternoon', evening: 'Evening' }
      : { morning: 'صباحًا', afternoon: 'ظهرًا', evening: 'مساءً' };
    return labels[period];
  }

  function periodIcon(period) {
    return period === 'evening' ? '☾' : '☼';
  }

  function renderSlotGroups(slots, restoreTime = '') {
    if (!timesWrap) return;
    timesWrap.innerHTML = '';
    if (!slots.length) {
      timesWrap.innerHTML = `<p class="vava-booking-empty">${VAVA_BOOKING.noSlots}</p>`;
      return;
    }

    const groups = { morning: [], afternoon: [], evening: [] };
    slots.forEach((slot) => groups[periodForTime(slot.value)].push(slot));

    Object.entries(groups).forEach(([period, periodSlots]) => {
      const group = document.createElement('section');
      group.className = `vava-booking-time-group is-${period}`;

      const heading = document.createElement('header');
      heading.className = 'vava-booking-time-group-heading';
      const first = periodSlots.length ? periodSlots[0].value : '';
      const last = periodSlots.length ? periodSlots[periodSlots.length - 1].value : '';
      const range = first && last ? `<small>${formatTime(first)} – ${formatTime(last)}</small>` : '';
      heading.innerHTML = `<span class="vava-booking-period-title"><i aria-hidden="true">${periodIcon(period)}</i><b>${periodLabel(period)}</b>${range}</span>`;

      const list = document.createElement('div');
      list.className = 'vava-booking-time-list';

      if (!periodSlots.length) {
        const empty = document.createElement('p');
        empty.className = 'vava-booking-period-empty';
        empty.textContent = isEnglish ? 'No times available' : 'لا توجد مواعيد';
        list.appendChild(empty);
      }

      periodSlots.forEach((slot) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'vava-booking-time';
        button.dataset.value = slot.value;
        button.innerHTML = `<span>${formatTime(slot.value)}</span><b aria-hidden="true">✓</b>`;
        button.addEventListener('click', () => {
          selectedTime = slot.value;
          selectedTimeLabel = formatTime(slot.value);
          timesWrap.querySelectorAll('.vava-booking-time').forEach((item) => item.classList.toggle('is-selected', item === button));
          if (nextToPayment) nextToPayment.disabled = false;
          updateSummary();
          saveState();
        });
        list.appendChild(button);
        if (restoreTime && restoreTime === slot.value) button.click();
      });

      group.append(heading, list);
      timesWrap.appendChild(group);
    });
  }

  async function fetchSlots(date, restoreTime = '') {
    if (!timesWrap) return;
    const requestId = ++slotsRequestId;
    selectedTime = '';
    selectedTimeLabel = '';
    if (nextToPayment) nextToPayment.disabled = true;
    timesWrap.innerHTML = `<span class="vava-booking-loading">${VAVA_BOOKING.loading}</span>`;
    const payload = new URLSearchParams({
      action: 'vava_booking_slots',
      nonce: VAVA_BOOKING.nonce,
      service: VAVA_BOOKING.service,
      date,
      lang: VAVA_BOOKING.lang,
    });
    try {
      const response = await fetch(VAVA_BOOKING.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: payload.toString(),
      });
      const json = await response.json();
      if (requestId !== slotsRequestId) return;
      const slots = json.success && Array.isArray(json.data.slots) ? json.data.slots : [];
      renderSlotGroups(slots, restoreTime);
    } catch (error) {
      if (requestId === slotsRequestId) renderSlotGroups([]);
    }
  }

  function isWorkingDate(date) {
    return Boolean(VAVA_BOOKING.workingDays && VAVA_BOOKING.workingDays[dayKeys[date.getDay()]]);
  }

  function chooseDateButton(button, restoreTime = '') {
    if (!button || button.disabled) return;
    selectedDate = button.dataset.date || '';
    selectedTime = '';
    selectedTimeLabel = '';
    datesWrap.querySelectorAll('.vava-booking-date').forEach((item) => item.classList.toggle('is-selected', item === button));
    if (nextToPayment) nextToPayment.disabled = true;
    updateSummary();
    saveState();
    fetchSlots(selectedDate, restoreTime);
  }

  function renderDates(options = {}) {
    if (!datesWrap) return;
    const preserveSelection = options.preserveSelection !== false;
    const autoSelect = Boolean(options.autoSelect);
    datesWrap.innerHTML = '';
    rangeStart = clampDate(rangeStart || serverToday, serverToday, serverMaxDate);

    for (let offset = 0; offset < 7; offset += 1) {
      const date = addDays(rangeStart, offset);
      const value = isoDate(date);
      const withinRange = date >= serverToday && date <= serverMaxDate;
      const working = withinRange && isWorkingDate(date);
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'vava-booking-date';
      button.dataset.date = value;
      button.disabled = !working;
      button.setAttribute('aria-disabled', working ? 'false' : 'true');
      button.innerHTML = `<small>${new Intl.DateTimeFormat(locale, { weekday: 'long' }).format(date)}</small><strong>${date.getDate()}</strong><span>${new Intl.DateTimeFormat(locale, { month: 'long' }).format(date)}</span>`;
      if (!working) button.classList.add('is-unavailable');
      if (preserveSelection && selectedDate === value) button.classList.add('is-selected');
      button.addEventListener('click', () => chooseDateButton(button));
      datesWrap.appendChild(button);
    }

    updateMonthLabel(rangeStart);
    if (datePrev) {
      const prevUnavailable = rangeStart <= serverToday;
      datePrev.disabled = prevUnavailable;
      datePrev.hidden = prevUnavailable;
    }
    if (dateNext) {
      const nextUnavailable = addDays(rangeStart, 7) > serverMaxDate;
      dateNext.disabled = nextUnavailable;
      dateNext.hidden = nextUnavailable;
    }
    if (datePicker) {
      datePicker.min = isoDate(serverToday);
      datePicker.max = isoDate(serverMaxDate);
      datePicker.value = selectedDate || isoDate(rangeStart);
    }

    refreshVisibleDateAvailability(autoSelect);
  }

  async function refreshVisibleDateAvailability(autoSelect = false) {
    if (!datesWrap || !rangeStart) return;
    const requestId = ++datesRequestId;
    const payload = new URLSearchParams({
      action: 'vava_booking_dates',
      nonce: VAVA_BOOKING.nonce,
      service: VAVA_BOOKING.service,
      start: isoDate(rangeStart),
      lang: VAVA_BOOKING.lang,
    });
    try {
      const response = await fetch(VAVA_BOOKING.ajaxUrl, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: payload.toString(),
      });
      const json = await response.json();
      if (requestId !== datesRequestId) return;
      const availability = json.success && json.data && json.data.availability ? json.data.availability : {};
      datesWrap.querySelectorAll('.vava-booking-date').forEach((button) => {
        if (button.disabled || availability[button.dataset.date] !== false) return;
        button.disabled = true;
        button.setAttribute('aria-disabled', 'true');
        button.classList.add('is-unavailable');
        if (button.dataset.date === selectedDate) {
          selectedDate = '';
          selectedTime = '';
          selectedTimeLabel = '';
          button.classList.remove('is-selected');
          if (nextToPayment) nextToPayment.disabled = true;
          renderSlotGroups([]);
          updateSummary();
          saveState();
        }
      });
      if ((autoSelect || currentStep === scheduleStep) && !selectedDate) selectFirstAvailableDate();
    } catch (error) {
      if (autoSelect && !selectedDate) selectFirstAvailableDate();
    }
  }

  function selectFirstAvailableDate() {
    if (!datesWrap) return;
    const first = datesWrap.querySelector('.vava-booking-date:not(:disabled)');
    if (first) chooseDateButton(first);
  }

  function setAll(selector, value) {
    form.querySelectorAll(selector).forEach((node) => { node.textContent = value || ''; });
  }

  function selectedPaymentMethod() {
    const selected = form.querySelector('[name="payment_method"]:checked');
    return selected ? selected.value : '';
  }

  function paymentMethodLabel(method) {
    const labels = isEnglish
      ? { paymob: 'Online payment through Paymob', bank: 'Bank transfer', cash: 'Pay later', free: 'Free session' }
      : { paymob: 'الدفع الإلكتروني عبر Paymob', bank: 'تحويل بنكي', cash: 'الدفع لاحقًا', free: 'جلسة مجانية' };
    return labels[method] || '';
  }

  function paymentStatusLabel(method) {
    const labels = isEnglish
      ? { paymob: 'Pending electronic payment', bank: 'Awaiting transfer review', cash: 'Unpaid — awaiting approval', free: 'Confirmed — no payment required' }
      : { paymob: 'بانتظار إتمام الدفع الإلكتروني', bank: 'بانتظار مراجعة التحويل', cash: 'غير مدفوع — بانتظار الاعتماد', free: 'مؤكد — لا يتطلب دفعًا' };
    return labels[method] || '';
  }

  function fieldValue(name) {
    return form.elements[name] ? String(form.elements[name].value || '').trim() : '';
  }

  function updateSummary() {
    const name = fieldValue('name');
    const email = fieldValue('email');
    const whatsapp = fieldValue('whatsapp');
    const contact = [email, whatsapp].filter(Boolean).join(' · ');
    const emptyName = isEnglish ? 'Not entered yet' : 'لم تُدخل البيانات بعد';
    const emptyDate = isEnglish ? 'Not selected yet' : 'لم يتم اختيار التاريخ';
    const emptyValue = isEnglish ? 'Not provided' : 'غير مضاف';
    setAll('[data-summary-name]', name || emptyName);
    setAll('[data-summary-contact]', contact);
    setAll('[data-summary-whatsapp]', whatsapp || emptyValue);
    setAll('[data-summary-email]', email || emptyValue);
    setAll('[data-summary-date]', selectedDate ? formatDate(selectedDate) : emptyDate);
    setAll('[data-summary-time]', selectedTimeLabel || selectedTime);
    setAll('[data-review-previous]', fieldValue('previous') || emptyValue);
    setAll('[data-review-notes]', fieldValue('notes') || emptyValue);

    const method = selectedPaymentMethod();
    setAll('[data-review-payment-method]', paymentMethodLabel(method));
    setAll('[data-review-payment-status]', paymentStatusLabel(method));
    const bankReview = form.querySelector('[data-review-bank]');
    if (bankReview) bankReview.hidden = method !== 'bank';
    if (method === 'bank') {
      setAll('[data-review-bank-name]', fieldValue('bank_transfer_name') || emptyValue);
      setAll('[data-review-bank-from]', fieldValue('bank_from_bank') || emptyValue);
      setAll('[data-review-bank-account]', fieldValue('bank_from_account') || emptyValue);
      setAll('[data-review-bank-amount]', fieldValue('bank_amount') || emptyValue);
      setAll('[data-review-bank-date]', fieldValue('bank_transfer_date') || emptyValue);
      setAll('[data-review-bank-time]', fieldValue('bank_transfer_time') ? formatTime(fieldValue('bank_transfer_time')) : emptyValue);
      setAll('[data-review-bank-reference]', fieldValue('bank_reference') || emptyValue);
      const receipt = form.elements.bank_receipt && form.elements.bank_receipt.files && form.elements.bank_receipt.files[0];
      setAll('[data-review-bank-receipt]', receipt ? receipt.name : emptyValue);
    }
  }

  function updateSubmitLabel() {
    if (!submitButton) return;
    const method = selectedPaymentMethod() || 'paymob';
    const label = submitButton.dataset[`label${method.charAt(0).toUpperCase()}${method.slice(1)}`] || '';
    const labelNode = submitButton.querySelector('[data-submit-label]');
    if (labelNode && label) labelNode.textContent = label;
  }

  function updatePaymentSelection() {
    form.querySelectorAll('.vava-booking-payment-options label').forEach((label) => {
      const radio = label.querySelector('input[type="radio"]');
      label.classList.toggle('is-selected', Boolean(radio && radio.checked));
    });
    const bankPanel = form.querySelector('[data-bank-transfer-panel]');
    const bankSelected = Boolean(form.querySelector('[name="payment_method"][value="bank"]:checked'));
    if (bankPanel) {
      bankPanel.hidden = !bankSelected;
      bankPanel.querySelectorAll('[data-bank-required]').forEach((field) => {
        field.required = bankSelected;
        if (!bankSelected) {
          field.classList.remove('is-invalid');
          const label = field.closest('label');
          if (label) label.classList.remove('is-invalid');
        }
      });
    }
    updateSubmitLabel();
    updateSummary();
  }

  function validatePaymentDetails() {
    const bankSelected = Boolean(form.querySelector('[name="payment_method"][value="bank"]:checked'));
    if (!bankSelected) return true;
    let valid = true;
    form.querySelectorAll('[data-bank-transfer-panel] [data-bank-required]').forEach((field) => {
      const fieldValid = field.checkValidity();
      field.classList.toggle('is-invalid', !fieldValid);
      const label = field.closest('label');
      if (label) label.classList.toggle('is-invalid', !fieldValid);
      valid = valid && fieldValid;
    });
    if (!valid) {
      const firstInvalid = form.querySelector('[data-bank-transfer-panel] [data-bank-required].is-invalid');
      if (firstInvalid) firstInvalid.focus();
    }
    return valid;
  }

  form.addEventListener('input', (event) => {
    if (event.target === whatsappLocal) syncWhatsAppField();
    event.target.classList.remove('is-invalid');
    const label = event.target.closest('label');
    if (label) label.classList.remove('is-invalid');
    if (event.target.matches('[data-questionnaire-field]')) updateQuestionnaireSummary();
    updateSummary();
    saveState();
  });

  form.addEventListener('change', (event) => {
    if (event.target === whatsappCountry) {
      updateCountryPicker();
      syncWhatsAppField();
    }
    if (event.target.matches('[name="previous"]')) refreshQuestionnaireRequirement();
    if (event.target.matches('[name="payment_method"]')) updatePaymentSelection();
    if (event.target.matches('[name="bank_receipt"]')) {
      const fileName = form.querySelector('[data-bank-receipt-name]');
      const file = event.target.files && event.target.files[0];
      if (fileName) fileName.textContent = file ? file.name : (document.documentElement.lang === 'en' ? 'No file selected' : 'لم يتم اختيار ملف');
      if (file) updateReceiptProgress(0, isEnglish ? 'Ready to upload' : 'جاهز للرفع', `${file.name} · ${formatUploadBytes(file.size)}`);
      else if (receiptProgress) receiptProgress.hidden = true;
    }
    if (event.target.matches('[data-questionnaire-field]')) updateQuestionnaireSummary();
    updateSummary();
    saveState();
  });

  questionnaireButtons.forEach((button, index) => {
    button.addEventListener('click', () => { if (index <= questionnaireGroupIndex || questionnaireCompleted) showQuestionnaireGroup(index); });
  });
  questionnaireStage?.addEventListener('click', (event) => {
    if (event.target.closest('[data-questionnaire-next]')) { if (validateQuestionnaireGroup()) showQuestionnaireGroup(questionnaireGroupIndex + 1); }
    if (event.target.closest('[data-questionnaire-prev]')) showQuestionnaireGroup(questionnaireGroupIndex - 1);
    if (event.target.closest('[data-questionnaire-finish]')) {
      if (!allQuestionnaireGroupsValid()) return;
      questionnaireCompleted = true;
      updateQuestionnaireSummary();
      showStep(scheduleStep);
    }
  });

  progress.forEach((button) => {
    button.addEventListener('click', () => {
      const target = Number(button.dataset.progressStep);
      if (target <= maxReachedStep) showStep(target);
    });
  });

  if (datePrev) {
    datePrev.addEventListener('click', () => {
      rangeStart = clampDate(addDays(rangeStart, -7), serverToday, serverMaxDate);
      renderDates();
      saveState();
    });
  }

  if (dateNext) {
    dateNext.addEventListener('click', () => {
      rangeStart = clampDate(addDays(rangeStart, 7), serverToday, serverMaxDate);
      renderDates();
      saveState();
    });
  }

  if (datePickerButton && datePicker) {
    datePickerButton.addEventListener('click', () => {
      if (typeof datePicker.showPicker === 'function') datePicker.showPicker();
      else datePicker.click();
    });
    datePicker.addEventListener('change', () => {
      const chosen = parseIsoDate(datePicker.value);
      if (!chosen) return;
      rangeStart = clampDate(chosen, serverToday, serverMaxDate);
      renderDates({ preserveSelection: false });
      const exact = datesWrap.querySelector(`[data-date="${CSS.escape(isoDate(chosen))}"]`);
      if (exact && !exact.disabled) chooseDateButton(exact);
      saveState();
    });
  }

  form.addEventListener('click', (event) => {
    const next = event.target.closest('[data-next-step]');
    const prev = event.target.closest('[data-prev-step]');
    if (next) {
      let target = Number(next.dataset.nextStep);
      if (next.matches('[data-step-one-next]')) {
        if (!validateStepOne()) return;
        refreshQuestionnaireRequirement();
        target = questionnaireRequired ? 2 : scheduleStep;
      }
      if (target === scheduleStep && questionnaireRequired && currentStep === 2 && !allQuestionnaireGroupsValid()) return;
      if (target === paymentStep && (!selectedDate || !selectedTime)) return;
      if (target === reviewStep) {
        if (!selectedPaymentMethod()) { setError(VAVA_BOOKING.error); return; }
        if (!validatePaymentDetails()) return;
      }
      setError('');
      updateSummary();
      updateQuestionnaireSummary();
      showStep(target);
    }
    if (prev) showStep(Number(prev.dataset.prevStep));
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    syncWhatsAppField();
    setError('');
    refreshQuestionnaireRequirement();
    if (!validateStepOne() || !allQuestionnaireGroupsValid() || !selectedDate || !selectedTime || !validatePaymentDetails()) return;
    const terms = form.elements.terms;
    if (!terms || !terms.checked) {
      if (terms) { terms.focus(); terms.closest('label')?.classList.add('is-invalid'); }
      setError(VAVA_BOOKING.error);
      return;
    }

    const original = submitButton ? submitButton.innerHTML : '';
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = VAVA_BOOKING.processing;
    }
    const data = new FormData(form);
    data.append('action', 'vava_booking_submit');
    data.append('nonce', VAVA_BOOKING.nonce);
    data.append('date', selectedDate);
    data.append('time', selectedTime);

    try {
      const receipt = form.elements.bank_receipt && form.elements.bank_receipt.files ? form.elements.bank_receipt.files[0] : null;
      if (receipt) updateReceiptProgress(1, isEnglish ? 'Uploading receipt…' : 'جارٍ رفع الإيصال…', `${receipt.name} · ${formatUploadBytes(receipt.size)}`);
      const json = await submitBookingRequest(data, (value) => {
        if (receipt) updateReceiptProgress(Math.max(2, Math.min(96, value)), isEnglish ? 'Uploading receipt…' : 'جارٍ رفع الإيصال…', `${receipt.name} · ${formatUploadBytes(receipt.size)}`);
      });
      if (receipt) updateReceiptProgress(100, isEnglish ? 'Receipt uploaded successfully' : 'تم رفع الإيصال بنجاح', `${receipt.name} · ${formatUploadBytes(receipt.size)}`, 'complete');
      if (!json.success) throw new Error(json.data && json.data.message ? json.data.message : VAVA_BOOKING.error);
      if (json.data.redirect) {
        window.location.href = json.data.redirect;
        return;
      }
      sessionStorage.removeItem(stateKey);
      form.classList.add('is-submitted');
      form.querySelectorAll('button, input, select, textarea').forEach((control) => {
        control.disabled = true;
      });
      if (successBox) {
        const title = successBox.querySelector('[data-success-title]');
        const message = successBox.querySelector('[data-success-message]');
        if (title && json.data.title) title.textContent = json.data.title;
        if (message && json.data.message) message.textContent = json.data.message;
        const number = successBox.querySelector('[data-success-booking-number]');
        const status = successBox.querySelector('[data-success-booking-status]');
        const myBookings = successBox.querySelector('[data-success-my-bookings]');
        if (number && json.data.bookingId) number.textContent = `#${json.data.bookingId}`;
        if (status) status.textContent = paymentStatusLabel(selectedPaymentMethod());
        if (myBookings && json.data.myBookingsUrl) myBookings.href = json.data.myBookingsUrl;
        successBox.hidden = false;
        window.requestAnimationFrame(() => successBox.classList.add('is-visible'));
        window.setTimeout(() => successClose && successClose.focus(), 260);
      }
    } catch (error) {
      const receipt = form.elements.bank_receipt && form.elements.bank_receipt.files ? form.elements.bank_receipt.files[0] : null;
      if (receipt) updateReceiptProgress(0, error.message || VAVA_BOOKING.error, receipt.name, 'error');
      setError(error.message || VAVA_BOOKING.error);
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.innerHTML = original;
        updateSubmitLabel();
      }
    }
  });

  function closeSuccessToast() {
    if (!successBox || successBox.hidden) return;
    successBox.classList.remove('is-visible');
    window.setTimeout(() => { successBox.hidden = true; }, 240);
  }

  if (successClose) successClose.addEventListener('click', closeSuccessToast);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && successBox && !successBox.hidden) closeSuccessToast();
  });

  initCountryPicker();
  const restored = readState();
  restoreFields(restored);
  updateCountryPicker();
  selectedDate = typeof restored.date === 'string' ? restored.date : '';
  selectedTime = typeof restored.time === 'string' ? restored.time : '';
  selectedTimeLabel = selectedTime ? formatTime(selectedTime) : '';
  maxReachedStep = Math.min(maxSteps, Math.max(1, Number(restored.maxStep || restored.step || 1)));
  const restoredRange = parseIsoDate(restored.rangeStart);
  if (restoredRange) rangeStart = clampDate(restoredRange, serverToday, serverMaxDate);
  if (selectedDate) {
    const selectedDateObject = parseIsoDate(selectedDate);
    if (!selectedDateObject || selectedDateObject < serverToday || selectedDateObject > serverMaxDate) {
      selectedDate = '';
      selectedTime = '';
      selectedTimeLabel = '';
      maxReachedStep = Math.min(maxReachedStep, scheduleStep);
    } else if (selectedDateObject < rangeStart || selectedDateObject > addDays(rangeStart, 6)) {
      rangeStart = new Date(selectedDateObject.getTime());
    }
  }

  renderDates();
  if (selectedDate) {
    const dateButton = datesWrap && datesWrap.querySelector(`[data-date="${CSS.escape(selectedDate)}"]`);
    if (dateButton && !dateButton.disabled) {
      dateButton.classList.add('is-selected');
      fetchSlots(selectedDate, selectedTime);
    } else {
      selectedDate = '';
      selectedTime = '';
      selectedTimeLabel = '';
      maxReachedStep = Math.min(maxReachedStep, scheduleStep);
    }
  }

  updateSummary();
  updatePaymentSelection();
  refreshQuestionnaireRequirement();
  showQuestionnaireGroup(questionnaireGroupIndex, false);
  const restoredStep = Math.min(maxSteps, Math.max(1, Number(restored.step || 1)));
  const validStepOne = stepOneIsComplete();
  const bankNeedsReceipt = selectedPaymentMethod() === 'bank' && !(form.elements.bank_receipt && form.elements.bank_receipt.files && form.elements.bank_receipt.files.length);
  let safeStep = restoredStep;
  if (restoredStep > 1 && !validStepOne) safeStep = 1;
  else if (restoredStep === 2 && !questionnaireRequired) safeStep = scheduleStep;
  else if (restoredStep > scheduleStep && (!selectedDate || !selectedTime)) safeStep = scheduleStep;
  else if (restoredStep > paymentStep && bankNeedsReceipt) safeStep = paymentStep;
  showStep(safeStep, false);
})();

/* VAVA_BOOKING_RECEIPT_ADMIN_SUCCESS_TOAST_V1R15 */
