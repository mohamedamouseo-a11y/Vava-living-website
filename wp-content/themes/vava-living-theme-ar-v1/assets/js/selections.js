(function () {
  'use strict';

  var root = document.querySelector('[data-vava-selection-blocks]');
  if (!root) return;

  var buttons = Array.prototype.slice.call(document.querySelectorAll('[data-selection-toggle]'));
  var panels = Array.prototype.slice.call(document.querySelectorAll('[data-selection-panel]'));
  var blocks = Array.prototype.slice.call(document.querySelectorAll('[data-selection-block]'));

  function closeAll(except) {
    buttons.forEach(function (button) {
      var group = button.getAttribute('data-selection-toggle');
      var active = group === except;
      button.setAttribute('aria-expanded', active ? 'true' : 'false');
    });

    panels.forEach(function (panel) {
      var active = panel.getAttribute('data-selection-panel') === except;
      panel.hidden = !active;
      panel.setAttribute('aria-hidden', active ? 'false' : 'true');
    });

    blocks.forEach(function (block) {
      block.classList.toggle('is-active', block.getAttribute('data-selection-block') === except);
    });
  }

  function openFromHash() {
    var prefix = '#vava-selection-panel-';
    if (!window.location.hash || window.location.hash.indexOf(prefix) !== 0) return;

    var group = window.location.hash.slice(prefix.length);
    var panel = document.querySelector('[data-selection-panel="' + group + '"]');
    if (!panel) return;

    closeAll(group);
    window.requestAnimationFrame(function () {
      var target = group === 'digital' ? (panel.querySelector('.product-grid') || panel) : panel;
      target.scrollIntoView({ block: 'start' });
    });
  }

  /* VAVA_SELECTIONS_SMOOTH_COLLECTION_JUMP_V1 */
  function scrollToPanel(panel, group) {
    if (!panel) return;
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var target = group === 'digital' ? (panel.querySelector('.product-grid') || panel) : panel;
    window.requestAnimationFrame(function () {
      target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
    });
  }

  buttons.forEach(function (button) {
    button.addEventListener('click', function (event) {
      event.preventDefault();

      var group = button.getAttribute('data-selection-toggle');
      var panel = document.querySelector('[data-selection-panel="' + group + '"]');
      if (!panel) return;

      closeAll(group);
      try {
        var url = new window.URL(window.location.href);
        url.hash = 'vava-selection-panel-' + group;
        window.history.replaceState(window.history.state, '', url.pathname + url.search + url.hash);
      } catch (error) {
        // Hash updates are an enhancement; scrolling must still work without them.
      }
      scrollToPanel(panel, group);
    });
  });

  openFromHash();

  /* Inline digital product reader — same interaction model as the Journal. */
  var reader = document.querySelector('[data-vava-product-reader]');
  var dialog = reader ? reader.querySelector('[data-vava-product-reader-dialog]') : null;
  var articles = reader ? Array.prototype.slice.call(reader.querySelectorAll('[data-vava-product-reader-article]')) : [];
  var originalTitle = document.title;
  var readerOpen = false;
  var activeUid = '';
  var lastFocused = null;
  var returnState = null;
  var bodyOverflow = '';
  var bodyPaddingRight = '';
  var closeTimer = 0;

  function normalizedUid(value) {
    return String(value || '').toLowerCase().replace(/[^a-z0-9_-]/g, '');
  }

  function articleFor(uid) {
    uid = normalizedUid(uid);
    return articles.filter(function (article) {
      return article.getAttribute('data-product-uid') === uid;
    })[0] || null;
  }

  function queryUid() {
    try {
      return normalizedUid(new window.URL(window.location.href).searchParams.get('vava_product'));
    } catch (error) {
      return '';
    }
  }

  function productUrl(uid) {
    var url = new window.URL(window.location.href);
    url.searchParams.set('vava_product', normalizedUid(uid));
    return url.pathname + url.search + url.hash;
  }

  function baseUrl() {
    var url = new window.URL(window.location.href);
    url.searchParams.delete('vava_product');
    return url.pathname + url.search + url.hash;
  }

  function lockBody() {
    if (document.body.classList.contains('vava-product-reader-open')) return;
    bodyOverflow = document.body.style.overflow;
    bodyPaddingRight = document.body.style.paddingRight;
    var scrollbarWidth = Math.max(0, window.innerWidth - document.documentElement.clientWidth);
    document.body.style.overflow = 'hidden';
    if (scrollbarWidth) document.body.style.paddingRight = scrollbarWidth + 'px';
    document.body.classList.add('vava-product-reader-open');
  }

  function unlockBody() {
    document.body.classList.remove('vava-product-reader-open');
    document.body.style.overflow = bodyOverflow;
    document.body.style.paddingRight = bodyPaddingRight;
  }

  function updateHistory(uid, mode) {
    if (mode === 'none') return;
    var state = {
      vavaProductOverlay: true,
      productUid: uid,
      scrollX: returnState ? returnState.scrollX : window.scrollX,
      scrollY: returnState ? returnState.scrollY : window.scrollY
    };
    if (mode === 'replace') window.history.replaceState(state, '', productUrl(uid));
    else window.history.pushState(state, '', productUrl(uid));
  }

  function openProduct(uid, options) {
    options = options || {};
    uid = normalizedUid(uid);
    var article = articleFor(uid);
    if (!reader || !dialog || !article) return false;

    window.clearTimeout(closeTimer);
    if (!readerOpen || !returnState) {
      returnState = {
        scrollX: window.scrollX,
        scrollY: window.scrollY
      };
      lastFocused = options.opener || document.activeElement;
    }

    closeAll(article.getAttribute('data-product-group') || 'digital');
    articles.forEach(function (item) {
      var active = item === article;
      item.hidden = !active;
      item.setAttribute('aria-hidden', active ? 'false' : 'true');
    });

    activeUid = uid;
    readerOpen = true;
    reader.hidden = false;
    reader.setAttribute('aria-hidden', 'false');
    var title = article.querySelector('h2[id]');
    if (title) dialog.setAttribute('aria-labelledby', title.id);
    else dialog.removeAttribute('aria-labelledby');
    dialog.scrollTop = 0;
    lockBody();

    var productTitle = article.getAttribute('data-product-title') || '';
    document.title = productTitle ? productTitle + ' — ' + originalTitle : originalTitle;
    updateHistory(uid, options.historyMode || 'push');

    window.requestAnimationFrame(function () {
      reader.classList.add('is-open');
      var closeButton = article.querySelector('[data-vava-product-reader-close]');
      if (closeButton && typeof closeButton.focus === 'function') {
        try { closeButton.focus({ preventScroll: true }); } catch (error) { closeButton.focus(); }
      } else {
        try { dialog.focus({ preventScroll: true }); } catch (error) { dialog.focus(); }
      }
    });
    return true;
  }

  function closeReader(options) {
    options = options || {};
    if (!reader || !readerOpen) return;

    reader.classList.remove('is-open');
    reader.setAttribute('aria-hidden', 'true');
    unlockBody();
    document.title = originalTitle;
    readerOpen = false;
    activeUid = '';

    var restore = returnState;
    returnState = null;
    closeTimer = window.setTimeout(function () {
      if (readerOpen) return;
      reader.hidden = true;
      articles.forEach(function (article) {
        article.hidden = true;
        article.setAttribute('aria-hidden', 'true');
      });
    }, 290);

    window.requestAnimationFrame(function () {
      if (options.restoreScroll !== false && restore) window.scrollTo(restore.scrollX, restore.scrollY);
      if (options.restoreFocus !== false && lastFocused && typeof lastFocused.focus === 'function') {
        try { lastFocused.focus({ preventScroll: true }); } catch (error) { lastFocused.focus(); }
      }
      lastFocused = null;
    });
  }

  function requestClose() {
    var state = window.history.state || {};
    if (state.vavaProductOverlay) {
      window.history.back();
      return;
    }
    window.history.replaceState({ vavaProductBase: true }, '', baseUrl());
    closeReader();
  }

  function focusableElements() {
    if (!dialog) return [];
    return Array.prototype.slice.call(dialog.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])')).filter(function (element) {
      return !element.closest('[hidden]') && element.offsetParent !== null;
    });
  }

  document.addEventListener('click', function (event) {
    var openLink = event.target.closest('[data-vava-product-open]');
    if (openLink) {
      if (event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      event.preventDefault();
      openProduct(openLink.getAttribute('data-product-uid'), {
        opener: openLink,
        historyMode: 'push'
      });
      return;
    }

    if (!reader || !readerOpen) return;
    var nav = event.target.closest('[data-vava-product-nav]');
    if (nav) {
      event.preventDefault();
      openProduct(nav.getAttribute('data-product-uid'), { historyMode: 'replace' });
      return;
    }
    if (event.target.closest('[data-vava-product-reader-close]')) {
      event.preventDefault();
      requestClose();
    }
  });

  if (reader) {
    reader.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        event.preventDefault();
        requestClose();
        return;
      }
      if (event.key !== 'Tab') return;
      var focusable = focusableElements();
      if (!focusable.length) {
        event.preventDefault();
        dialog.focus();
        return;
      }
      var first = focusable[0];
      var last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    });
  }

  window.addEventListener('popstate', function () {
    var uid = queryUid();
    if (uid && articleFor(uid)) {
      openProduct(uid, { historyMode: 'none' });
    } else {
      closeReader();
    }
  });

  if (reader) {
    var initialUid = queryUid();
    if (initialUid && articleFor(initialUid)) {
      window.history.replaceState({ vavaProductOverlay: true, productUid: initialUid }, '', productUrl(initialUid));
      openProduct(initialUid, { historyMode: 'none' });
    } else {
      window.history.replaceState({ vavaProductBase: true }, '', baseUrl());
    }
  }
})();
