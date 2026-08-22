(function () {
  'use strict';

  var config = window.VAVA_JOURNAL || {};
  var results = document.querySelector('[data-vava-journal-results]');
  if (!results) return;

  var loader = document.querySelector('[data-journal-loader]');
  var reader = document.querySelector('[data-journal-reader]');
  var readerDialog = reader ? reader.querySelector('[data-journal-reader-dialog]') : null;
  var readerOutput = reader ? reader.querySelector('[data-journal-reader-output]') : null;
  var readerLoading = reader ? reader.querySelector('[data-journal-reader-loading]') : null;
  var pageRequest = null;
  var articleRequest = null;
  var pageSequence = 0;
  var articleSequence = 0;
  var currentPage = Math.max(1, parseInt(results.getAttribute('data-current-page'), 10) || 1);
  var readerOpen = false;
  var returnState = null;
  var lastFocused = null;
  var originalTitle = document.title;
  var bodyOverflow = '';
  var bodyPaddingRight = '';

  function pageId() {
    return results.getAttribute('data-page-id') || config.pageId || '0';
  }

  function language() {
    return results.getAttribute('data-language') || config.lang || 'ar';
  }

  function setPageLoading(loading) {
    if (loading) results.classList.remove('has-error');
    results.classList.toggle('is-loading', loading);
    results.setAttribute('aria-busy', loading ? 'true' : 'false');
    if (loader) {
      loader.hidden = !loading;
      loader.setAttribute('aria-hidden', loading ? 'false' : 'true');
    }
    results.querySelectorAll('[data-journal-page]').forEach(function (button) {
      if (loading) {
        button.setAttribute('data-journal-was-disabled', button.disabled ? '1' : '0');
        button.disabled = true;
        return;
      }
      var wasDisabled = button.getAttribute('data-journal-was-disabled') === '1';
      button.disabled = wasDisabled || button.hasAttribute('aria-current');
      button.removeAttribute('data-journal-was-disabled');
    });
  }

  function rememberBaseHistory() {
    if (readerOpen) return;
    var state = Object.assign({}, window.history.state || {}, {
      vavaJournalBase: true,
      journalPage: currentPage
    });
    window.history.replaceState(state, '', window.location.href);
  }

  function loadPage(page, options) {
    options = options || {};
    page = Math.max(1, parseInt(page, 10) || 1);
    if (!config.ajaxUrl || !config.nonce) return Promise.reject(new Error('Journal is not configured.'));

    if (pageRequest && typeof pageRequest.abort === 'function') pageRequest.abort();
    pageRequest = typeof window.AbortController === 'function' ? new window.AbortController() : null;
    pageSequence += 1;
    var sequence = pageSequence;

    var scrollX = window.scrollX;
    var scrollY = window.scrollY;
    var body = new window.URLSearchParams();
    body.set('action', 'vava_journal_load_articles');
    body.set('nonce', config.nonce);
    body.set('pageId', pageId());
    body.set('page', String(page));
    body.set('lang', language());

    setPageLoading(true);
    var fetchOptions = {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString()
    };
    if (pageRequest) fetchOptions.signal = pageRequest.signal;

    return window.fetch(config.ajaxUrl, fetchOptions).then(function (response) {
      if (!response.ok) throw new Error('Journal pagination request failed.');
      return response.json();
    }).then(function (payload) {
      if (sequence !== pageSequence) return payload;
      if (!payload || !payload.success || !payload.data || typeof payload.data.html !== 'string') {
        throw new Error('Invalid Journal pagination response.');
      }
      results.innerHTML = payload.data.html;
      currentPage = Math.max(1, parseInt(payload.data.currentPage, 10) || page);
      results.setAttribute('data-current-page', String(currentPage));
      if (options.updateHistory !== false) rememberBaseHistory();
      window.requestAnimationFrame(function () {
        if (options.preserveScroll !== false) window.scrollTo(scrollX, scrollY);
        var current = results.querySelector('[aria-current="page"]');
        if (options.focusCurrent !== false && current && typeof current.focus === 'function') {
          try { current.focus({ preventScroll: true }); } catch (error) {}
        }
      });
      return payload;
    }).catch(function (error) {
      if (error && error.name === 'AbortError') return null;
      if (sequence === pageSequence) results.classList.add('has-error');
      throw error;
    }).finally(function () {
      if (sequence === pageSequence) setPageLoading(false);
    });
  }

  function lockBody() {
    if (document.body.classList.contains('vava-journal-reader-open')) return;
    bodyOverflow = document.body.style.overflow;
    bodyPaddingRight = document.body.style.paddingRight;
    var scrollbarWidth = Math.max(0, window.innerWidth - document.documentElement.clientWidth);
    document.body.style.overflow = 'hidden';
    if (scrollbarWidth) document.body.style.paddingRight = scrollbarWidth + 'px';
    document.body.classList.add('vava-journal-reader-open');
  }

  function unlockBody() {
    document.body.classList.remove('vava-journal-reader-open');
    document.body.style.overflow = bodyOverflow;
    document.body.style.paddingRight = bodyPaddingRight;
  }

  function showReaderLoading() {
    if (!reader || !readerDialog || !readerOutput) return;
    reader.hidden = false;
    reader.setAttribute('aria-hidden', 'false');
    reader.classList.add('is-loading');
    reader.classList.remove('has-error');
    readerOutput.innerHTML = '';
    if (readerLoading) readerLoading.hidden = false;
    lockBody();
    window.requestAnimationFrame(function () {
      reader.classList.add('is-open');
      try { readerDialog.focus({ preventScroll: true }); } catch (error) { readerDialog.focus(); }
    });
  }

  function articleUrl(postId, page) {
    var url = new window.URL(window.location.href);
    url.searchParams.set('journal_article', String(postId));
    url.searchParams.set('journal_page', String(page));
    return url.pathname + url.search + url.hash;
  }

  function baseUrl() {
    var url = new window.URL(window.location.href);
    url.searchParams.delete('journal_article');
    url.searchParams.delete('journal_page');
    return url.pathname + url.search + url.hash;
  }

  function updateArticleHistory(postId, mode) {
    if ('none' === mode) return;
    var state = {
      vavaJournalOverlay: true,
      articleId: postId,
      journalPage: returnState ? returnState.page : currentPage,
      scrollX: returnState ? returnState.scrollX : window.scrollX,
      scrollY: returnState ? returnState.scrollY : window.scrollY
    };
    if ('replace' === mode) {
      window.history.replaceState(state, '', articleUrl(postId, state.journalPage));
    } else {
      window.history.pushState(state, '', articleUrl(postId, state.journalPage));
    }
  }

  function openArticle(postId, options) {
    options = options || {};
    postId = Math.max(1, parseInt(postId, 10) || 0);
    if (!postId || !reader || !config.ajaxUrl || !config.nonce) {
      if (options.fallbackUrl) window.location.assign(options.fallbackUrl);
      return Promise.reject(new Error('Article reader is unavailable.'));
    }

    if (!readerOpen || !returnState) {
      returnState = {
        page: currentPage,
        scrollX: window.scrollX,
        scrollY: window.scrollY
      };
      lastFocused = options.opener || document.activeElement;
    }
    readerOpen = true;
    showReaderLoading();

    if (articleRequest && typeof articleRequest.abort === 'function') articleRequest.abort();
    articleRequest = typeof window.AbortController === 'function' ? new window.AbortController() : null;
    articleSequence += 1;
    var sequence = articleSequence;

    var body = new window.URLSearchParams();
    body.set('action', 'vava_journal_load_article');
    body.set('nonce', config.nonce);
    body.set('pageId', pageId());
    body.set('postId', String(postId));
    body.set('lang', language());

    var fetchOptions = {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString()
    };
    if (articleRequest) fetchOptions.signal = articleRequest.signal;

    return window.fetch(config.ajaxUrl, fetchOptions).then(function (response) {
      if (!response.ok) throw new Error('Article request failed.');
      return response.json();
    }).then(function (payload) {
      if (sequence !== articleSequence) return payload;
      if (!payload || !payload.success || !payload.data || typeof payload.data.html !== 'string') {
        throw new Error('Invalid article response.');
      }
      readerOutput.innerHTML = payload.data.html;
      reader.classList.remove('is-loading', 'has-error');
      if (readerLoading) readerLoading.hidden = true;
      readerDialog.scrollTop = 0;
      document.title = payload.data.title ? payload.data.title + ' — ' + originalTitle : originalTitle;
      updateArticleHistory(postId, options.historyMode || 'push');
      window.requestAnimationFrame(function () {
        var closeButton = readerOutput.querySelector('[data-journal-reader-close]');
        if (closeButton && typeof closeButton.focus === 'function') {
          try { closeButton.focus({ preventScroll: true }); } catch (error) {}
        }
      });
      return payload;
    }).catch(function (error) {
      if (error && error.name === 'AbortError') return null;
      if (sequence !== articleSequence) return null;
      reader.classList.remove('is-loading');
      reader.classList.add('has-error');
      if (readerLoading) readerLoading.hidden = true;
      if (options.fallbackUrl) {
        closeReader({ restoreScroll: false, restoreFocus: false });
        window.location.assign(options.fallbackUrl);
      }
      throw error;
    });
  }

  function closeReader(options) {
    options = options || {};
    if (!reader || !readerOpen) return;
    if (articleRequest && typeof articleRequest.abort === 'function') articleRequest.abort();
    articleSequence += 1;
    reader.classList.remove('is-open', 'is-loading', 'has-error');
    reader.hidden = true;
    reader.setAttribute('aria-hidden', 'true');
    if (readerOutput) readerOutput.innerHTML = '';
    if (readerLoading) readerLoading.hidden = false;
    unlockBody();
    document.title = originalTitle;
    readerOpen = false;

    var restore = returnState;
    returnState = null;
    window.requestAnimationFrame(function () {
      if (options.restoreScroll !== false && restore) window.scrollTo(restore.scrollX, restore.scrollY);
      if (options.restoreFocus !== false && lastFocused && typeof lastFocused.focus === 'function') {
        try { lastFocused.focus({ preventScroll: true }); } catch (error) {}
      }
      lastFocused = null;
    });
  }

  function requestClose() {
    var state = window.history.state || {};
    if (state.vavaJournalOverlay) {
      window.history.back();
      return;
    }
    window.history.replaceState({ vavaJournalBase: true, journalPage: currentPage }, '', baseUrl());
    closeReader();
  }

  function focusableElements() {
    if (!readerDialog) return [];
    return Array.prototype.slice.call(readerDialog.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])')).filter(function (element) {
      return element.offsetParent !== null;
    });
  }

  results.addEventListener('click', function (event) {
    var articleLink = event.target.closest('[data-journal-article]');
    if (articleLink) {
      if (event.defaultPrevented || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      event.preventDefault();
      openArticle(articleLink.getAttribute('data-journal-post-id'), {
        opener: articleLink,
        fallbackUrl: articleLink.href,
        historyMode: 'push'
      }).catch(function () {});
      return;
    }

    var button = event.target.closest('[data-journal-page]');
    if (!button || button.disabled) return;
    event.preventDefault();
    loadPage(button.getAttribute('data-journal-page')).catch(function () {});
  });

  if (reader) {
    reader.addEventListener('click', function (event) {
      var closeButton = event.target.closest('[data-journal-reader-close]');
      if (closeButton) {
        event.preventDefault();
        requestClose();
        return;
      }
      var navButton = event.target.closest('[data-journal-reader-nav]');
      if (!navButton) return;
      event.preventDefault();
      openArticle(navButton.getAttribute('data-journal-post-id'), { historyMode: 'replace' }).catch(function () {});
    });

    reader.addEventListener('keydown', function (event) {
      if ('Escape' === event.key) {
        event.preventDefault();
        requestClose();
        return;
      }
      if ('Tab' !== event.key) return;
      var focusable = focusableElements();
      if (!focusable.length) {
        event.preventDefault();
        readerDialog.focus();
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

  window.addEventListener('popstate', function (event) {
    var url = new window.URL(window.location.href);
    var postId = Math.max(0, parseInt(url.searchParams.get('journal_article'), 10) || 0);
    var page = Math.max(1, parseInt(url.searchParams.get('journal_page'), 10) || (event.state && event.state.journalPage) || currentPage);
    if (!postId) {
      closeReader();
      return;
    }
    var prepare = page !== currentPage
      ? loadPage(page, { updateHistory: false, preserveScroll: false, focusCurrent: false })
      : Promise.resolve();
    prepare.then(function () {
      returnState = {
        page: page,
        scrollX: event.state && typeof event.state.scrollX === 'number' ? event.state.scrollX : window.scrollX,
        scrollY: event.state && typeof event.state.scrollY === 'number' ? event.state.scrollY : window.scrollY
      };
      return openArticle(postId, { historyMode: 'none' });
    }).catch(function () {});
  });

  (function initialiseHistoryAndDeepLink() {
    var url = new window.URL(window.location.href);
    var postId = Math.max(0, parseInt(url.searchParams.get('journal_article'), 10) || 0);
    var page = Math.max(1, parseInt(url.searchParams.get('journal_page'), 10) || currentPage);
    if (!postId) {
      rememberBaseHistory();
      return;
    }
    var prepare = page !== currentPage
      ? loadPage(page, { updateHistory: false, preserveScroll: false, focusCurrent: false })
      : Promise.resolve();
    prepare.then(function () {
      returnState = { page: page, scrollX: window.scrollX, scrollY: window.scrollY };
      window.history.replaceState({
        vavaJournalOverlay: true,
        articleId: postId,
        journalPage: page,
        scrollX: returnState.scrollX,
        scrollY: returnState.scrollY
      }, '', window.location.href);
      return openArticle(postId, { historyMode: 'none' });
    }).catch(function () {});
  })();
})();
