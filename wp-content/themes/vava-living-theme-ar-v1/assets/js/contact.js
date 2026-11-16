(function () {
  'use strict';

  var form = document.querySelector('[data-contact-form]');
  if (!form) return;

  var email = form.querySelector('input[type="email"]');
  var emailMessage = form.getAttribute('data-email-invalid') || 'Please enter a valid email address.';
  if (email) {
    email.addEventListener('invalid', function () {
      if (email.validity.typeMismatch || email.validity.valueMissing) email.setCustomValidity(emailMessage);
    });
    email.addEventListener('input', function () { email.setCustomValidity(''); });
  }


  var guideCards = Array.prototype.slice.call(document.querySelectorAll('[data-guide-card]'));
  function clearGuideHighlight() { guideCards.forEach(function (card) { card.classList.remove('is-field-active'); }); }
  function highlightGuideForField(fieldId) {
    clearGuideHighlight();
    if (!fieldId) return;
    guideCards.forEach(function (card) {
      var fields = String(card.getAttribute('data-guide-fields') || '').split(/\s+/).filter(Boolean);
      if (fields.indexOf(fieldId) !== -1) card.classList.add('is-field-active');
    });
  }
  Array.prototype.slice.call(form.querySelectorAll('[data-contact-field] input, [data-contact-field] select, [data-contact-field] textarea')).forEach(function (control) {
    var wrapper = control.closest('[data-contact-field]');
    var fieldId = wrapper ? String(wrapper.getAttribute('data-contact-field') || '') : '';
    control.addEventListener('focus', function () { highlightGuideForField(fieldId); });
    control.addEventListener('blur', function () { window.setTimeout(function () { if (!form.contains(document.activeElement)) clearGuideHighlight(); }, 40); });
  });

  var hold = form.querySelector('[data-contact-hold]');
  var submit = form.querySelector('[data-contact-submit]');
  var tokenInput = form.querySelector('[data-contact-hold-token]');
  if (!hold || !submit || !tokenInput || typeof window.VAVA_CONTACT === 'undefined') return;

  var label = hold.querySelector('[data-contact-hold-label]');
  var percent = hold.querySelector('[data-contact-hold-percent]');
  var requiredSeconds = Math.max(3, parseInt(hold.getAttribute('data-duration') || '4', 10));
  var isEnglish = String(window.VAVA_CONTACT.lang || 'ar').toLowerCase() === 'en';
  // Press-and-hold is unreliable on touch devices (pointerup/cancel can fire
  // during the first AJAX request). Use one tap and a timed server challenge.
  var mobileTapMode = !!(window.matchMedia && window.matchMedia('(pointer: coarse)').matches);
  var tapIdleText = isEnglish ? 'Tap once to verify' : 'اضغط مرة واحدة للتحقق';
  var tapActiveText = isEnglish ? 'Checking automatically…' : 'جارٍ التحقق تلقائيًا…';
  var subLabel = hold.querySelector('.vava-contact-hold-copy small');
  var idleText = hold.getAttribute('data-idle') || '';
  var activeText = hold.getAttribute('data-active') || idleText;
  var verifiedText = hold.getAttribute('data-verified') || idleText;
  var errorText = hold.getAttribute('data-error') || idleText;
  var state = 'idle';
  var isPressed = false;
  var activePointerId = null;
  var challenge = '';
  var startedAt = 0;
  var raf = 0;
  var startController = null;
  var verifyController = null;
  function updateInputMode() {
    hold.classList.toggle('is-tap-mode', mobileTapMode);
    if (subLabel && mobileTapMode) {
      subLabel.textContent = isEnglish
        ? 'Tap once and wait ' + requiredSeconds + ' seconds (no holding needed)'
        : 'اضغط مرة واحدة وانتظر ' + requiredSeconds + ' ثوانٍ بدون ضغط مطوّل';
    }
    if (state === 'idle' && label) label.textContent = mobileTapMode ? tapIdleText : idleText;
  }
  updateInputMode();

  function post(action, payload, controller) {
    var body = new URLSearchParams();
    body.set('action', action);
    body.set('pageId', String(window.VAVA_CONTACT.pageId || ''));
    body.set('nonce', String(window.VAVA_CONTACT.nonce || ''));
    Object.keys(payload || {}).forEach(function (key) { body.set(key, String(payload[key])); });
    return fetch(window.VAVA_CONTACT.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString(),
      signal: controller ? controller.signal : undefined
    }).then(function (response) { return response.json(); });
  }

  function setProgress(value) {
    var normalized = Math.max(0, Math.min(1, value));
    hold.style.setProperty('--hold-progress', String(normalized));
    if (percent) percent.textContent = Math.round(normalized * 100) + '%';
  }

  function stopAnimation() {
    if (raf) window.cancelAnimationFrame(raf);
    raf = 0;
  }

  function setIdle(message, showError) {
    stopAnimation();
    state = 'idle';
    challenge = '';
    isPressed = false;
    releasePointerCapture();
    setProgress(0);
    hold.classList.remove('is-holding', 'is-verifying', 'is-verified', 'is-error');
    if (showError) hold.classList.add('is-error');
    if (label) label.textContent = showError ? (message || errorText) : (mobileTapMode ? tapIdleText : (message || idleText));
    if (showError) {
      window.setTimeout(function () {
        if (state !== 'idle') return;
        hold.classList.remove('is-error');
        if (label) label.textContent = mobileTapMode ? tapIdleText : idleText;
      }, 1700);
    }
  }

  function releasePointerCapture() {
    if (activePointerId === null || typeof hold.releasePointerCapture !== 'function') return;
    try {
      if (hold.hasPointerCapture(activePointerId)) hold.releasePointerCapture(activePointerId);
    } catch (error) {}
    activePointerId = null;
  }

  function unlock(token) {
    state = 'verified';
    isPressed = false;
    releasePointerCapture();
    tokenInput.value = token;
    setProgress(1);
    hold.classList.remove('is-holding', 'is-verifying', 'is-error');
    hold.classList.add('is-verified');
    if (label) label.textContent = verifiedText;
    if (percent) percent.textContent = '100%';
    window.setTimeout(function () {
      hold.hidden = true;
      submit.hidden = false;
      submit.disabled = false;
      submit.classList.remove('is-locked');
      try { submit.focus({ preventScroll: true }); } catch (error) { submit.focus(); }
    }, 420);
  }

  function finishVerification() {
    if (state !== 'holding' || !challenge) return;
    state = 'verifying';
    stopAnimation();
    hold.classList.remove('is-holding');
    hold.classList.add('is-verifying');
    if (label) label.textContent = mobileTapMode ? tapActiveText : activeText;
    verifyController = new AbortController();
    window.setTimeout(function () {
      if (state === 'verifying') {
        if (verifyController) verifyController.abort();
        setIdle(errorText, true);
      }
    }, 16000);
    post('vava_contact_hold_verify', { challenge: challenge }, verifyController).then(function (result) {
      if (result && result.success && result.data && result.data.token) unlock(String(result.data.token));
      else setIdle(errorText, true);
    }).catch(function (error) {
      if (error && error.name === 'AbortError' && state !== 'verifying') return;
      setIdle(errorText, true);
    });
  }

  function frame() {
    if (state !== 'holding') return;
    if (!isPressed) {
      setIdle(idleText, false);
      return;
    }
    var elapsed = (performance.now() - startedAt) / 1000;
    // Slight grace above the server threshold prevents "too_fast" on mobile.
    var progress = elapsed / (mobileTapMode ? requiredSeconds + 0.35 : requiredSeconds);
    setProgress(progress);
    if (progress >= 1) {
      finishVerification();
      return;
    }
    raf = window.requestAnimationFrame(frame);
  }

  function begin(event) {
    if (state === 'verified' || state === 'starting' || state === 'holding' || state === 'verifying') return;
    if (event && event.type === 'keydown') {
      if (event.key !== ' ' && event.key !== 'Enter') return;
      if (event.repeat) return;
    }
    if (event && event.type === 'pointerdown' && typeof event.button === 'number' && event.button !== 0) return;
    if (event && event.pointerType === 'touch' && !mobileTapMode) {
      mobileTapMode = true;
      updateInputMode();
    }
    if (event) event.preventDefault();

    isPressed = true;
    if (event && event.type === 'pointerdown') {
      activePointerId = event.pointerId;
      if (typeof hold.setPointerCapture === 'function') {
        try { hold.setPointerCapture(activePointerId); } catch (error) {}
      }
    }

    state = 'starting';
    hold.classList.remove('is-error');
    hold.classList.add('is-verifying');
    if (label) label.textContent = mobileTapMode ? tapActiveText : activeText;
    startController = new AbortController();
    window.setTimeout(function () {
      if (state === 'starting') {
        if (startController) startController.abort();
        setIdle(errorText, true);
      }
    }, 16000);
    post('vava_contact_hold_start', {}, startController).then(function (result) {
      if (state !== 'starting' || (!mobileTapMode && !isPressed)) {
        if (state === 'starting') setIdle(idleText, false);
        return;
      }
      if (!result || !result.success || !result.data) throw new Error('start_failed');
      if (result.data.disabled) {
        unlock('disabled');
        return;
      }
      challenge = String(result.data.challenge || '');
      if (!challenge) throw new Error('challenge_missing');
      requiredSeconds = Math.max(3, parseInt(result.data.duration || requiredSeconds, 10));
      updateInputMode();
      startedAt = performance.now();
      state = 'holding';
      hold.classList.remove('is-verifying');
      hold.classList.add('is-holding');
      raf = window.requestAnimationFrame(frame);
    }).catch(function (error) {
      if (error && error.name === 'AbortError' && state !== 'starting') return;
      setIdle(errorText, true);
    });
  }

  function cancel(event) {
    if (event && event.type === 'keyup' && event.key !== ' ' && event.key !== 'Enter') return;
    if (mobileTapMode) {
      // The finger can be lifted immediately: verification continues.
      releasePointerCapture();
      return;
    }
    isPressed = false;
    releasePointerCapture();
    if (state === 'starting') {
      if (startController) startController.abort();
      setIdle(idleText, false);
      return;
    }
    if (state === 'holding') {
      setIdle(idleText, false);
    }
  }

  hold.addEventListener('pointerdown', begin);
  // Legacy touch browsers without Pointer Events can still verify with a tap.
  if (typeof window.PointerEvent === 'undefined') {
    hold.addEventListener('click', function (event) { if (mobileTapMode) begin(event); });
  }
  hold.addEventListener('pointerup', cancel);
  hold.addEventListener('pointercancel', cancel);
  hold.addEventListener('lostpointercapture', function () {
    if (!mobileTapMode && (state === 'starting' || state === 'holding')) cancel();
  });
  hold.addEventListener('keydown', begin);
  hold.addEventListener('keyup', cancel);
  hold.addEventListener('blur', function () {
    if (!mobileTapMode && (state === 'starting' || state === 'holding')) cancel();
  });
  hold.addEventListener('contextmenu', function (event) { event.preventDefault(); });
  hold.addEventListener('dragstart', function (event) { event.preventDefault(); });

  form.addEventListener('submit', function (event) {
    if (state !== 'verified' || !tokenInput.value) {
      event.preventDefault();
      hold.hidden = false;
      submit.hidden = true;
      submit.disabled = true;
      setIdle(errorText, true);
    }
  });
}());
