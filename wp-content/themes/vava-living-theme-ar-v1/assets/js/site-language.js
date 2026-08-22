(function () {
  'use strict';

  var config = window.VAVA_SITE_LANGUAGE || {};
  var cookieName = config.cookieName || 'vava_site_language';
  var storageKey = config.storageKey || 'vavaSiteLanguage';
  var current = config.current === 'en' ? 'en' : 'ar';

  function setCookie(language) {
    var secure = window.location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = cookieName + '=' + encodeURIComponent(language) + '; Path=/; Max-Age=31536000; SameSite=Lax' + secure;
  }

  function store(language) {
    try {
      window.localStorage.setItem(storageKey, language);
    } catch (error) {}
    setCookie(language);
  }

  function savedLanguage() {
    try {
      var saved = window.localStorage.getItem(storageKey);
      return saved === 'en' || saved === 'ar' ? saved : '';
    } catch (error) {
      return '';
    }
  }

  function urlFor(language, sourceUrl) {
    var url;
    try {
      url = new URL(sourceUrl || window.location.href, window.location.href);
    } catch (error) {
      return sourceUrl || window.location.href;
    }
    url.searchParams.set('vava_lang', language);
    url.searchParams.set('from', 'lang');
    if (window.location.hash && !url.hash) {
      url.hash = window.location.hash;
    }
    return url.toString();
  }

  function cleanLanguageQuery() {
    try {
      var url = new URL(window.location.href);
      if (!url.searchParams.has('vava_lang')) return;
      url.searchParams.delete('vava_lang');
      if (url.searchParams.get('from') === 'lang') {
        url.searchParams.delete('from');
      }
      window.history.replaceState(null, document.title, url.pathname + url.search + url.hash);
    } catch (error) {}
  }

  function bindSwitches() {
    document.querySelectorAll('[data-vava-language]').forEach(function (link) {
      link.addEventListener('click', function (event) {
        var language = link.getAttribute('data-vava-language') === 'en' ? 'en' : 'ar';
        event.preventDefault();
        store(language);
        window.location.assign(urlFor(language, link.href));
      });
    });
  }

  function syncPreference() {
    var saved = savedLanguage();
    if (!saved) {
      store(current);
      return;
    }

    // A stored choice can restore the site language after cookies are cleared.
    if (!config.hasServerPreference && saved !== current) {
      window.location.replace(urlFor(saved, window.location.href));
      return;
    }

    store(current);
    cleanLanguageQuery();
  }

  function init() {
    syncPreference();
    bindSwitches();
    document.documentElement.lang = current;
    document.documentElement.dir = current === 'en' ? 'ltr' : 'rtl';
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
