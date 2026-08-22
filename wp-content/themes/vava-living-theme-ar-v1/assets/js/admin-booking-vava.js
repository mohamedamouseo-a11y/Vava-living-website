// VAVA_BOOKING_POLICY_CONSENT_PREVIEW_V1
(function () {
  'use strict';

  const root = document.querySelector('[data-booking-admin]');
  if (!root) return;

  const tabs = Array.from(root.querySelectorAll('[data-booking-tab]'));
  const langButtons = Array.from(root.querySelectorAll('[data-booking-lang]'));
  const preview = document.querySelector('[data-booking-admin-preview]');
  const postbox = document.querySelector('#vava_homepage_settings');
  let activeTab = 'customer';
  let activeLang = 'ar';

  function nestedField(path) {
    const name = `vava_booking[${activeLang}]${path.map((part) => `[${part}]`).join('')}`;
    return root.querySelector(`[name="${name}"]`);
  }

  function value(path, fallback) {
    const field = nestedField(path);
    return field && String(field.value).trim() ? String(field.value).trim() : fallback;
  }

  function setText(selector, text) {
    if (!preview) return;
    const node = preview.querySelector(selector);
    if (node) node.textContent = text;
  }

  function setAll(selector, text) {
    if (!preview) return;
    preview.querySelectorAll(selector).forEach((node) => { node.textContent = text; });
  }

  function stepForTab(tab) {
    if (tab === 'availability') return 2;
    if (tab === 'payment') return 3;
    if (tab === 'messages') return 4;
    if (tab === 'questionnaires') return 1;
    return 1;
  }

  function previewService() {
    if (!preview) return {};
    const raw = activeLang === 'en' ? preview.dataset.previewServiceEn : preview.dataset.previewServiceAr;
    try { return raw ? JSON.parse(raw) : {}; } catch (error) { return {}; }
  }

  function updateServicePreview(isEnglish) {
    const service = previewService();
    const labels = isEnglish
      ? { duration: 'Duration', session_type: 'Type', price: 'Price' }
      : { duration: 'المدة', session_type: 'النوع', price: 'السعر' };
    Object.keys(labels).forEach((key) => {
      setAll(`[data-preview-service-label="${key}"]`, labels[key]);
      setAll(`[data-preview-service-value="${key}"]`, service[key] || '—');
    });
    setAll('[data-preview-service-title]', service.title || (isEnglish ? 'VAVA session' : 'جلسة VAVA'));
    setAll('[data-preview-service-description]', service.description || '');
    setAll('[data-preview-summary-service-value]', service.title || '—');
    setAll('[data-preview-review-service-value]', service.title || '—');
    setAll('[data-preview-review-duration-value]', service.duration || '—');
    setAll('[data-preview-review-total-value]', service.price || '—');
    setAll('[data-preview-review-amount-value]', service.price || '—');
    const finalTotal = preview && preview.querySelector('[data-preview-review-final-total]');
    if (finalTotal) {
      finalTotal.innerHTML = `${isEnglish ? 'Final total' : 'الإجمالي النهائي'} <b>${service.price || '—'}</b>`;
    }
  }

  function updateReviewPreview(isEnglish) {
    const copy = isEnglish ? {
      titles: { customer: 'Customer details', service: 'Service and appointment details', payment: 'Payment details', transfer: 'Transfer details' },
      labels: { name: 'Full name', email: 'Email', phone: 'Mobile number', service: 'Service', appointment: 'Appointment', duration: 'Duration', method: 'Payment method', status: 'Status', total: 'Total', bank: 'Bank', amount: 'Amount', receipt: 'Receipt' },
      customer: 'Name and contact details', appointment: '27 July — 11:30 am', method: 'Bank transfer', status: 'Awaiting transfer review', bank: 'National Bank', submit: 'Submit booking for transfer review', paths: 'Back to VAVA Paths', back: 'Previous step'
    } : {
      titles: { customer: 'تفاصيل العميل', service: 'تفاصيل الخدمة والموعد', payment: 'تفاصيل الدفع', transfer: 'تفاصيل التحويل' },
      labels: { name: 'الاسم الكامل', email: 'البريد الإلكتروني', phone: 'رقم الجوال', service: 'الخدمة', appointment: 'الموعد', duration: 'المدة', method: 'طريقة الدفع', status: 'الحالة', total: 'الإجمالي', bank: 'البنك', amount: 'المبلغ', receipt: 'الإيصال' },
      customer: 'الاسم وبيانات التواصل', appointment: '27 يوليو — 11:30 am', method: 'تحويل بنكي', status: 'بانتظار مراجعة التحويل', bank: 'البنك الأهلي', submit: 'إرسال الحجز لمراجعة التحويل', paths: 'العودة إلى مسارات VAVA', back: 'رجوع للمرحلة السابقة'
    };
    Object.entries(copy.titles).forEach(([key, text]) => setText(`[data-preview-review-title="${key}"]`, text));
    Object.entries(copy.labels).forEach(([key, text]) => setAll(`[data-preview-review-label="${key}"]`, text));
    setText('[data-preview-summary-customer-value]', copy.customer);
    setText('[data-preview-summary-appointment-value]', copy.appointment);
    setText('[data-preview-review-appointment-value]', copy.appointment);
    setText('[data-preview-review-method-value]', copy.method);
    setText('[data-preview-review-status-value]', copy.status);
    setText('[data-preview-review-bank-value]', copy.bank);
    setText('[data-preview-review-consent]', `☑ ${value(['consent_text'], isEnglish ? 'I agree to the Terms & Conditions, Privacy Policy, and Booking Policy.' : 'أوافق على الشروط والأحكام وسياسة الخصوصية وسياسة الحجز.')}`);
    setText('[data-preview-review-submit]', copy.submit);
    setText('[data-preview-review-paths]', copy.paths);
    setText('[data-preview-review-back]', copy.back);
  }

  function updatePreview() {
    if (!preview) return;
    const step = stepForTab(activeTab);
    const isEnglish = activeLang === 'en';
    preview.dataset.previewLanguage = activeLang;
    preview.dir = isEnglish ? 'ltr' : 'rtl';

    preview.querySelectorAll('[data-preview-step]').forEach((item) => {
      item.classList.toggle('is-active', Number(item.dataset.previewStep) === step);
      item.classList.toggle('is-complete', Number(item.dataset.previewStep) < step);
    });
    preview.querySelectorAll('[data-preview-pane]').forEach((pane) => {
      pane.classList.toggle('is-active', Number(pane.dataset.previewPane) === step);
    });

    const stepLabels = [
      value(['steps', 0], isEnglish ? 'Service & details' : 'الخدمة وبياناتك'),
      value(['steps', 1], isEnglish ? 'Choose a time' : 'اختيار الموعد'),
      value(['steps', 2], isEnglish ? 'Payment method' : 'طريقة الدفع'),
      value(['steps', 3], isEnglish ? 'Review & confirm' : 'مراجعة وتأكيد الحجز')
    ];
    stepLabels.forEach((label, index) => setText(`[data-preview-step-label="${index + 1}"]`, label));
    setText('[data-preview-section-label]', stepLabels[step - 1]);
    setText('[data-preview-header]', isEnglish ? 'Live preview' : 'معاينة مباشرة');
    setText('[data-preview-eyebrow]', value(['eyebrow'], isEnglish ? 'A calm booking experience' : 'تجربة حجز هادئة وواضحة'));
    setText('[data-preview-title]', value(['title'], isEnglish ? 'Book your VAVA session' : 'حجز جلسة مع VAVA'));
    setText('[data-preview-intro]', value(['intro'], isEnglish ? 'Complete the four booking steps below.' : 'أكمل خطوات الحجز الأربع.'));
    setText('[data-preview-selected-service]', value(['selected_service'], isEnglish ? 'Selected service' : 'الخدمة المختارة'));
    setText('[data-preview-fields-title]', value(['fields_title'], isEnglish ? 'Your details' : 'بيانات الحجز'));
    setText('[data-preview-name-label]', value(['fields', 'name', 'label'], isEnglish ? 'Full name' : 'الاسم الكامل'));
    setText('[data-preview-email-label]', value(['fields', 'email', 'label'], isEnglish ? 'Email' : 'البريد الإلكتروني'));
    setText('[data-preview-whatsapp-label]', value(['fields', 'whatsapp', 'label'], isEnglish ? 'WhatsApp number' : 'رقم WhatsApp'));
    setText('[data-preview-whatsapp-country]', isEnglish ? '🇸🇦 Saudi Arabia (+966)' : '🇸🇦 المملكة العربية السعودية (+966)');
    setText('[data-preview-whatsapp-placeholder]', isEnglish ? 'Enter number without country code' : 'اكتب الرقم بدون كود الدولة');
    setText('[data-preview-previous-label]', value(['fields', 'previous', 'label'], isEnglish ? 'Have you tried VAVA before?' : 'هل سبق لك تجربة VAVA؟'));
    setText('[data-preview-notes-label]', value(['fields', 'notes', 'label'], isEnglish ? 'What would you like support with?' : 'الاحتياج أو الملاحظات'));
    setText('[data-preview-appointment-title]', value(['appointment_title'], isEnglish ? 'Choose your appointment' : 'اختيار الموعد المناسب'));
    setText('[data-preview-calendar-month]', isEnglish ? 'July 2026' : 'يوليو 2026');
    setText('[data-preview-confirm-title]', value(['confirm_title'], isEnglish ? 'Confirm your booking' : 'تأكيد الحجز'));
    setText('[data-preview-summary-service]', value(['summary_service'], isEnglish ? 'Service' : 'الخدمة'));
    setText('[data-preview-summary-customer]', value(['summary_customer'], isEnglish ? 'Your details' : 'بيانات العميل'));
    setText('[data-preview-summary-appointment]', value(['summary_appointment'], isEnglish ? 'Appointment' : 'الموعد'));
    setText('[data-preview-payment-title]', value(['payment_title'], isEnglish ? 'Payment method' : 'طريقة الدفع'));
    setText('[data-preview-paymob-label]', value(['paymob_label'], isEnglish ? 'Pay securely online' : 'الدفع الإلكتروني الآمن'));
    setText('[data-preview-paymob-note]', value(['paymob_note'], isEnglish ? 'Secure card payment through Paymob.' : 'الدفع بالبطاقة من خلال بوابة Paymob الآمنة.'));
    setText('[data-preview-bank-label]', value(['bank_label'], isEnglish ? 'Bank transfer' : 'تحويل بنكي'));
    setText('[data-preview-bank-note]', value(['bank_note'], isEnglish ? 'Pending until transfer review.' : 'يظل الحجز قيد المراجعة حتى تأكيد التحويل.'));

    updateServicePreview(isEnglish);
    updateReviewPreview(isEnglish);

    const primary = step === 1
      ? value(['continue'], isEnglish ? 'Choose appointment' : 'اختيار الموعد')
      : (step === 2
        ? value(['continue_payment'], isEnglish ? 'Review and pay' : 'مراجعة الحجز والدفع')
        : (step === 3
          ? value(['submit'], isEnglish ? 'Continue to review' : 'متابعة إلى المراجعة')
          : value(['final_confirm'], isEnglish ? 'Final booking confirmation' : 'تأكيد الحجز النهائي')));
    setText('[data-preview-primary]', primary);
    updateMessagePreview();
  }

  function updatePostboxTitle() {
    if (!postbox) return;
    const title = postbox.querySelector('.postbox-header h2, .postbox-header .hndle');
    if (title) title.textContent = activeLang === 'en' ? 'Booking page settings' : 'إعدادات صفحة الحجز';
    const update = document.querySelector('.vava-booking-update-button span');
    if (update) update.textContent = activeLang === 'en' ? 'Update' : 'تحديث';
  }

  function updateLocalizedAdminCopy() {
    const isEnglish = activeLang === 'en';
    root.dir = isEnglish ? 'ltr' : 'rtl';
    root.querySelectorAll('[data-booking-i18n]').forEach((node) => {
      const next = isEnglish ? node.dataset.i18nEn : node.dataset.i18nAr;
      if (typeof next === 'string') node.textContent = next;
    });
    root.querySelectorAll('[data-vava-i18n-ar][data-vava-i18n-en]').forEach((node) => {
      node.textContent = isEnglish ? node.dataset.vavaI18nEn : node.dataset.vavaI18nAr;
    });
    root.querySelectorAll('[data-vava-i18n-aria-ar][data-vava-i18n-aria-en]').forEach((node) => {
      node.setAttribute('aria-label', isEnglish ? node.dataset.vavaI18nAriaEn : node.dataset.vavaI18nAriaAr);
    });
    root.querySelectorAll('[data-vava-page-title-pane]').forEach((pane) => {
      const active = pane.dataset.vavaPageTitlePane === activeLang;
      pane.classList.toggle('is-active', active);
      pane.hidden = !active;
    });
    root.querySelectorAll('[data-placeholder-ar][data-placeholder-en]').forEach((field) => {
      field.placeholder = isEnglish ? field.dataset.placeholderEn : field.dataset.placeholderAr;
    });
    root.querySelectorAll('[data-title-ar][data-title-en]').forEach((node) => {
      node.title = isEnglish ? node.dataset.titleEn : node.dataset.titleAr;
    });
    root.querySelectorAll('[data-localized-value]').forEach((field) => {
      field.hidden = field.dataset.localizedValue !== activeLang;
    });
    updatePaymentStatuses();
    syncAvailabilitySelectEditor();
    syncMessageSelectEditors();
  }

  function syncSelectEditor(container, selectSelector, fieldSelector) {
    if (!container) return;
    const select = container.querySelector(selectSelector);
    if (!select) return;
    container.querySelectorAll(fieldSelector).forEach((field) => {
      const key = field.dataset.availabilitySettingField || field.dataset.messageSettingField || '';
      field.classList.toggle('is-active', key === select.value);
    });
  }

  function syncAvailabilitySelectEditor() {
    root.querySelectorAll('[data-availability-select-editor]').forEach((container) => {
      syncSelectEditor(container, '[data-availability-setting-select]', '[data-availability-setting-field]');
    });
  }

  function syncMessageSelectEditors() {
    root.querySelectorAll('[data-message-select-editor]').forEach((container) => {
      syncSelectEditor(container, '[data-message-setting-select]', '[data-message-setting-field]');
    });
  }

  function syncWorkingDay(row) {
    if (!row) return;
    const toggle = row.querySelector('[data-working-day-toggle]');
    if (!toggle) return;
    const enabled = toggle.checked;
    row.classList.toggle('is-open', enabled);
    row.classList.toggle('is-closed', !enabled);
    row.querySelectorAll('[data-working-day-time]').forEach((field) => {
      field.readOnly = !enabled;
      field.setAttribute('aria-disabled', enabled ? 'false' : 'true');
      if (enabled) field.removeAttribute('tabindex');
      else field.setAttribute('tabindex', '-1');
    });
  }

  function syncWorkingDays() {
    root.querySelectorAll('[data-working-day-row]').forEach(syncWorkingDay);
  }

  function activeMessageSelection() {
    const pane = root.querySelector(`[data-booking-lang-pane="${activeLang}"]`);
    if (!pane) return null;
    const select = pane.querySelector('[data-message-setting-select]');
    if (!select) return null;
    const field = pane.querySelector(`[data-message-setting-field="${select.value}"]`);
    const input = field ? field.querySelector('[data-message-setting-input]') : null;
    const option = select.options[select.selectedIndex];
    return { label: option ? option.textContent.trim() : '', value: input ? String(input.value || '').trim() : '' };
  }

  function updateMessagePreview() {
    if (!preview) return;
    const box = preview.querySelector('[data-preview-message-box]');
    if (!box) return;
    const show = activeTab === 'messages';
    box.hidden = !show;
    if (!show) return;
    const selected = activeMessageSelection();
    setText('[data-preview-message-label]', selected && selected.label ? selected.label : (activeLang === 'en' ? 'Selected message' : 'الرسالة المختارة'));
    setText('[data-preview-message-value]', selected && selected.value ? selected.value : (activeLang === 'en' ? 'Enter the message content.' : 'اكتب محتوى الرسالة.'));
  }

  function updatePaymentStatuses() {
    root.querySelectorAll('[data-payment-method-card]').forEach((card) => {
      const enabled = card.querySelector('[data-payment-enabled]');
      const status = card.querySelector('[data-payment-status]');
      if (!enabled || !status) return;
      const isEnabled = enabled.checked;
      status.classList.toggle('is-enabled', isEnabled);
      status.classList.toggle('is-disabled', !isEnabled);
      const enabledLabel = activeLang === 'en' ? status.dataset.enabledEn : status.dataset.enabledAr;
      const disabledLabel = activeLang === 'en' ? status.dataset.disabledEn : status.dataset.disabledAr;
      status.textContent = isEnabled ? enabledLabel : disabledLabel;
      card.classList.toggle('is-enabled', isEnabled);
      card.classList.toggle('is-disabled', !isEnabled);
    });
  }

  function render() {
    tabs.forEach((button) => button.classList.toggle('is-active', button.dataset.bookingTab === activeTab));
    langButtons.forEach((button) => button.classList.toggle('is-active', button.dataset.bookingLang === activeLang));
    root.querySelectorAll('[data-booking-lang-pane]').forEach((pane) => pane.classList.toggle('is-active', pane.dataset.bookingLangPane === activeLang));
    root.querySelectorAll('[data-booking-panel]').forEach((panel) => panel.classList.toggle('is-active', panel.dataset.bookingPanel === activeTab));
    root.querySelectorAll('[data-questionnaire-admin-lang]').forEach((pane) => {
      const active = pane.dataset.questionnaireAdminLang === activeLang;
      pane.hidden = !active;
      pane.classList.toggle('is-active', active);
    });
    root.querySelectorAll('[data-questionnaire-lang-copy]').forEach((node) => {
      node.hidden = node.dataset.questionnaireLangCopy !== activeLang;
    });

    const tabLabels = activeLang === 'en'
      ? ['Customer fields', 'Availability', 'Payment methods', 'Operational messages', 'Questionnaires']
      : ['بيانات العميل', 'المواعيد والتوافر', 'طرق الدفع', 'الرسائل التشغيلية', 'الاستبيانات'];
    tabs.forEach((button, index) => {
      const label = button.querySelector('b');
      if (label && tabLabels[index]) label.textContent = tabLabels[index];
    });
    const hiddenLanguage = root.querySelector('[data-vava-active-language-input]');
    if (hiddenLanguage) hiddenLanguage.value = activeLang;
    root.dataset.activeLanguage = activeLang;
    root.dataset.activeSection = activeTab;
    updatePostboxTitle();
    updateLocalizedAdminCopy();
    syncWorkingDays();
    updatePreview();
  }

  function installHeaderActions() {
    if (!postbox) return;
    const header = postbox.querySelector('.postbox-header');
    const actions = root.querySelector('.vava-booking-postbox-actions');
    if (!header || !actions) return;
    actions.classList.add('is-in-postbox-header');
    header.appendChild(actions);
    const update = actions.querySelector('.vava-booking-update-button');
    if (update) update.classList.add('is-in-postbox-header');
    const language = actions.querySelector('.vava-booking-language');
    if (language) language.classList.add('is-in-postbox-header');
  }

  function toggleOnlyOne(trigger, cardSelector, openClass) {
    const card = trigger.closest(cardSelector);
    if (!card) return;
    const container = card.parentElement;
    const wasOpen = card.classList.contains(openClass);
    Array.from(container.children).forEach((item) => {
      if (!item.matches(cardSelector)) return;
      item.classList.remove(openClass);
      const button = item.querySelector('[data-payment-accordion]');
      if (button) button.setAttribute('aria-expanded', 'false');
    });
    if (!wasOpen) {
      card.classList.add(openClass);
      trigger.setAttribute('aria-expanded', 'true');
    }
  }

  tabs.forEach((button) => button.addEventListener('click', () => {
    activeTab = button.dataset.bookingTab || 'customer';
    render();
    const panel = root.querySelector(`[data-booking-panel="${activeTab}"].is-active`);
    if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }));

  langButtons.forEach((button) => button.addEventListener('click', () => {
    activeLang = button.dataset.bookingLang === 'en' ? 'en' : 'ar';
    render();
  }));

  root.addEventListener('click', (event) => {
    const paymentTrigger = event.target.closest('[data-payment-accordion]');
    if (paymentTrigger) toggleOnlyOne(paymentTrigger, '[data-payment-method-card]', 'is-open');

    const questionnaireTrigger = event.target.closest('[data-questionnaire-admin-toggle]');
    if (questionnaireTrigger) {
      const card = questionnaireTrigger.closest('[data-questionnaire-admin-accordion]');
      const container = card && card.parentElement;
      const wasOpen = card && card.classList.contains('is-open');
      if (container) Array.from(container.children).forEach((item) => {
        if (!item.matches('[data-questionnaire-admin-accordion]')) return;
        item.classList.remove('is-open');
        const body = item.querySelector('.vava-questionnaire-admin-body');
        const button = item.querySelector('[data-questionnaire-admin-toggle]');
        if (body) body.hidden = true;
        if (button) button.setAttribute('aria-expanded', 'false');
      });
      if (card && !wasOpen) {
        card.classList.add('is-open');
        const body = card.querySelector('.vava-questionnaire-admin-body');
        if (body) body.hidden = false;
        questionnaireTrigger.setAttribute('aria-expanded', 'true');
      }
    }

    const groupTrigger = event.target.closest('[data-questionnaire-group-toggle]');
    if (groupTrigger) {
      const card = groupTrigger.closest('[data-questionnaire-group-admin]');
      const container = card && card.parentElement;
      const wasOpen = card && card.classList.contains('is-open');
      if (container) Array.from(container.children).forEach((item) => {
        if (!item.matches('[data-questionnaire-group-admin]')) return;
        item.classList.remove('is-open');
        const body = item.querySelector('.vava-questionnaire-group-admin-body');
        const button = item.querySelector('[data-questionnaire-group-toggle]');
        if (body) body.hidden = true;
        if (button) button.setAttribute('aria-expanded', 'false');
      });
      if (card && !wasOpen) {
        card.classList.add('is-open');
        const body = card.querySelector('.vava-questionnaire-group-admin-body');
        if (body) body.hidden = false;
        groupTrigger.setAttribute('aria-expanded', 'true');
      }
    }
  });

  root.addEventListener('input', updatePreview);
  root.addEventListener('change', (event) => {
    if (event.target.matches('[data-payment-enabled]')) updatePaymentStatuses();
    if (event.target.matches('[data-working-day-toggle]')) syncWorkingDay(event.target.closest('[data-working-day-row]'));
    if (event.target.matches('[data-availability-setting-select]')) syncAvailabilitySelectEditor();
    if (event.target.matches('[data-message-setting-select]')) syncMessageSelectEditors();
    updatePreview();
  });

  installHeaderActions();
  render();
})();
