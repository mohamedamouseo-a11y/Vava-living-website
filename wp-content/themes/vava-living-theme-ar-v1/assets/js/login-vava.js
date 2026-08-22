(function () {
  'use strict';

  function initVavaLogin() {
    var form = document.getElementById('loginform');
    if (!form) return;

    var remember = form.querySelector('.forgetmenot');
    var originalSwitcher = document.querySelector('.language-switcher');
    var originalSelect = originalSwitcher ? originalSwitcher.querySelector('select[name="wp_lang"]') : null;
    if (remember && !remember.closest('.vava-login-options')) {
      var row = document.createElement('div');
      row.className = 'vava-login-options';
      remember.parentNode.insertBefore(row, remember);
      row.appendChild(remember);
      if (originalSelect) {
        var language = document.createElement('div');
        var label = document.createElement('label');
        var select = originalSelect.cloneNode(true);
        language.className = 'vava-login-language';
        label.setAttribute('for', 'vava-login-language-select');
        label.textContent = document.documentElement.dir === 'rtl' ? 'اللغة' : 'Language';
        select.id = 'vava-login-language-select';
        select.removeAttribute('name');
        select.addEventListener('change', function () {
          var url = new URL(window.location.href);
          url.searchParams.set('wp_lang', select.value);
          window.location.assign(url.toString());
        });
        language.appendChild(label);
        language.appendChild(select);
        row.appendChild(language);
      }
    }

    var hold = form.querySelector('[data-vava-login-hold]');
    var token = form.querySelector('[data-vava-login-token]');
    var submitWrap = form.querySelector('.submit');
    var submit = submitWrap ? submitWrap.querySelector('input[type="submit"],button[type="submit"]') : null;
    if (!hold || !token || !submit || typeof window.vavaLoginGuard === 'undefined') return;

    var label = hold.querySelector('[data-vava-login-hold-label]');
    var percent = hold.querySelector('[data-vava-login-hold-percent]');
    var idleText = hold.getAttribute('data-idle') || '';
    var activeText = hold.getAttribute('data-active') || idleText;
    var verifiedText = hold.getAttribute('data-verified') || idleText;
    var errorText = hold.getAttribute('data-error') || idleText;
    var duration = Math.max(3, parseInt(window.vavaLoginGuard.duration || 3, 10));
    var state = 'idle';
    var pressed = false;
    var pointerId = null;
    var challenge = '';
    var startedAt = 0;
    var raf = 0;
    var controller = null;
    var inputMode = '';
    var touchId = null;

    submitWrap.hidden = true;
    submit.disabled = true;

    function post(action, payload) {
      var body = new URLSearchParams();
      body.set('action', action);
      body.set('nonce', String(window.vavaLoginGuard.nonce || ''));
      Object.keys(payload || {}).forEach(function (key) { body.set(key, String(payload[key])); });
      controller = new AbortController();
      return fetch(window.vavaLoginGuard.ajaxUrl, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString(), signal: controller.signal
      }).then(function (response) { return response.json(); });
    }

    function progress(value) {
      value = Math.max(0, Math.min(1, value));
      hold.style.setProperty('--hold-progress', String(value));
      if (percent) percent.textContent = Math.round(value * 100) + '%';
    }

    function releaseCapture() {
      if (pointerId === null || typeof hold.releasePointerCapture !== 'function') return;
      try { if (hold.hasPointerCapture(pointerId)) hold.releasePointerCapture(pointerId); } catch (error) {}
      pointerId = null;
    }

    function idle(message, error) {
      if (raf) cancelAnimationFrame(raf);
      raf = 0; state = 'idle'; challenge = ''; progress(0);
      hold.classList.remove('is-holding', 'is-verifying', 'is-verified', 'is-error');
      if (error) hold.classList.add('is-error');
      if (label) label.textContent = message || idleText;
      if (error) setTimeout(function () {
        if (state === 'idle') { hold.classList.remove('is-error'); if (label) label.textContent = idleText; }
      }, 1800);
    }

    function unlock(value) {
      state = 'verified'; pressed = false; inputMode = ''; touchId = null; releaseCapture(); token.value = value; progress(1);
      hold.classList.remove('is-holding', 'is-verifying', 'is-error');
      hold.classList.add('is-verified');
      if (label) label.textContent = verifiedText;
      setTimeout(function () {
        hold.hidden = true; submitWrap.hidden = false; submit.disabled = false;
        try { submit.focus({ preventScroll: true }); } catch (error) { submit.focus(); }
      }, 420);
    }

    function verify() {
      if (state !== 'holding' || !challenge) return;
      state = 'verifying'; if (raf) cancelAnimationFrame(raf);
      hold.classList.remove('is-holding'); hold.classList.add('is-verifying');
      post('vava_login_hold_verify', { challenge: challenge }).then(function (result) {
        if (result && result.success && result.data && result.data.token) unlock(String(result.data.token));
        else idle(errorText, true);
      }).catch(function (error) { if (!error || error.name !== 'AbortError') idle(errorText, true); });
    }

    function frame() {
      if (state !== 'holding') return;
      if (!pressed) { idle(idleText, false); return; }
      var value = ((performance.now() - startedAt) / 1000) / duration;
      progress(value);
      if (value >= 1) { verify(); return; }
      raf = requestAnimationFrame(frame);
    }

    function begin(event) {
      if (['starting', 'holding', 'verifying', 'verified'].indexOf(state) !== -1) return;
      if (event.type === 'keydown' && event.key !== ' ' && event.key !== 'Enter') return;
      if (event.type === 'keydown' && event.repeat) return;
      if (event.type === 'pointerdown' && event.button !== 0) return;
      event.preventDefault(); pressed = true;
      if (event.type === 'pointerdown') {
        inputMode = 'pointer';
        pointerId = event.pointerId;
        try { hold.setPointerCapture(pointerId); } catch (error) {}
      } else if (event.type === 'mousedown') {
        inputMode = 'mouse';
      } else if (event.type === 'touchstart') {
        inputMode = 'touch';
        touchId = event.changedTouches && event.changedTouches.length ? event.changedTouches[0].identifier : null;
      } else if (event.type === 'keydown') {
        inputMode = 'keyboard';
      }
      state = 'starting'; hold.classList.remove('is-error'); hold.classList.add('is-verifying');
      if (label) label.textContent = activeText;
      post('vava_login_hold_start', {}).then(function (result) {
        if (!pressed || state !== 'starting') { idle(idleText, false); return; }
        if (!result || !result.success || !result.data || !result.data.challenge) throw new Error('start');
        challenge = String(result.data.challenge); duration = Math.max(3, parseInt(result.data.duration || duration, 10));
        startedAt = performance.now(); state = 'holding';
        hold.classList.remove('is-verifying'); hold.classList.add('is-holding'); raf = requestAnimationFrame(frame);
      }).catch(function (error) { if (!error || error.name !== 'AbortError') idle(errorText, true); });
    }

    function cancel(event) {
      if (event.type === 'keyup' && event.key !== ' ' && event.key !== 'Enter') return;
      if (event.type === 'pointerup' && inputMode === 'pointer' && pointerId !== null && event.pointerId !== pointerId) return;
      if (event.type === 'mouseup' && inputMode !== 'mouse' && inputMode !== 'pointer') return;
      if (event.type === 'touchend' && inputMode === 'touch' && touchId !== null) {
        var ended = Array.prototype.some.call(event.changedTouches || [], function (touch) { return touch.identifier === touchId; });
        if (!ended) return;
      }
      if (event.type === 'keyup' && inputMode !== 'keyboard') return;
      pressed = false; releaseCapture();
      inputMode = ''; touchId = null;
      if (state === 'starting' && controller) controller.abort();
      if (state === 'starting' || state === 'holding') idle(idleText, false);
    }

    if (window.PointerEvent) {
      hold.addEventListener('pointerdown', begin);
      document.addEventListener('pointerup', cancel, true);
      /* A native mouseup remains available in some Chromium cancellation
       * paths even when the matching pointerup is suppressed. */
      document.addEventListener('mouseup', cancel, true);
      hold.addEventListener('pointercancel', function (event) {
        /* Chrome may emit a transient pointercancel during a long press.
         * Keep the verified physical press alive; document pointerup is the
         * authoritative release signal. */
        event.preventDefault();
      });
    } else {
      hold.addEventListener('mousedown', begin);
      document.addEventListener('mouseup', cancel, true);
      hold.addEventListener('touchstart', begin, { passive: false });
      document.addEventListener('touchend', cancel, { capture: true, passive: false });
    }
    hold.addEventListener('keydown', begin);
    document.addEventListener('keyup', cancel, true);
    hold.addEventListener('dragstart', function (event) { event.preventDefault(); });
    hold.addEventListener('selectstart', function (event) { event.preventDefault(); });
    hold.addEventListener('contextmenu', function (event) { event.preventDefault(); });

    form.addEventListener('submit', function (event) {
      if (state !== 'verified' || !token.value || submit.disabled) {
        event.preventDefault(); hold.hidden = false; submitWrap.hidden = true; submit.disabled = true; idle(errorText, true); return;
      }
      submit.disabled = true; submit.classList.add('is-processing');
      submit.value = document.documentElement.dir === 'rtl' ? 'جارٍ تسجيل الدخول…' : 'Logging in…';
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initVavaLogin);
  else initVavaLogin();
}());
