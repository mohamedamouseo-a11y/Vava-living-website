(function () {
  'use strict';

  var root = document.querySelector('[data-protected-reader]');
  var reader = root && root.querySelector('[data-vava-canvas-reader]');
  if (!root || !reader) return;

  var config = window.VAVA_DIGITAL_READER || {};
  var canvas = reader.querySelector('[data-reader-canvas]');
  var wrap = reader.querySelector('[data-reader-frame-wrap]');
  var loading = reader.querySelector('[data-reader-loading]');
  var error = reader.querySelector('[data-reader-error]');
  var currentLabel = reader.querySelector('[data-reader-current]');
  var zoomLabel = reader.querySelector('[data-reader-zoom-label]');
  var prevButton = reader.querySelector('[data-reader-prev]');
  var nextButton = reader.querySelector('[data-reader-next]');
  var total = Number(config.pageCount || root.getAttribute('data-page-count') || 0);
  var page = 1;
  var zoom = 1;
  var sourceImage = null;
  var brandLogo = null;
  var loadingRequest = 0;
  var storageKey = 'vavaReader:' + String(config.userId || 'guest') + ':' + String(config.uid || root.getAttribute('data-product-uid') || 'product');

  try {
    var saved = JSON.parse(window.localStorage.getItem(storageKey) || '{}');
    if (saved && Number(saved.page) >= 1 && Number(saved.page) <= total) page = Number(saved.page);
    if (saved && Number(saved.zoom) >= .7 && Number(saved.zoom) <= 2) zoom = Number(saved.zoom);
  } catch (e) {}

  function saveState() {
    try { window.localStorage.setItem(storageKey, JSON.stringify({ page: page, zoom: zoom })); } catch (e) {}
  }

  function setBusy(active) {
    if (loading) loading.hidden = !active;
    reader.classList.toggle('is-loading', active);
  }

  function showError(message) {
    if (!error) return;
    error.hidden = false;
    error.textContent = message || (config.labels && config.labels.error) || 'تعذر تحميل الصفحة.';
  }

  function clearError() {
    if (error) { error.hidden = true; error.textContent = ''; }
  }

  function updateControls() {
    if (currentLabel) currentLabel.textContent = String(page);
    if (zoomLabel) zoomLabel.textContent = Math.round(zoom * 100) + '%';
    if (prevButton) prevButton.disabled = page <= 1;
    if (nextButton) nextButton.disabled = page >= total;
  }

  function drawWatermark(ctx, width, height) {
    var customer = String(config.watermark || '');
    var brand = String(config.brand || 'VAVA LIVING');
    var positions = [
      [.20, .17, -0.20], [.80, .17, -0.20],
      [.30, .52, -0.20], [.72, .68, -0.20],
      [.20, .86, -0.20], [.82, .88, -0.20]
    ];
    ctx.save();
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    positions.forEach(function (point) {
      var x = width * point[0];
      var y = height * point[1];
      ctx.save();
      ctx.translate(x, y);
      ctx.rotate(point[2]);
      if (brandLogo && brandLogo.complete && brandLogo.naturalWidth) {
        var logoWidth = Math.max(58, width * .065);
        var logoHeight = logoWidth * brandLogo.naturalHeight / brandLogo.naturalWidth;
        ctx.globalAlpha = .055;
        ctx.drawImage(brandLogo, -logoWidth / 2, -logoHeight - 10, logoWidth, logoHeight);
      }
      ctx.globalAlpha = .048;
      ctx.fillStyle = '#505a38';
      ctx.font = '700 ' + Math.max(12, Math.round(width / 82)) + 'px sans-serif';
      ctx.fillText(brand, 0, 3);
      if (customer) {
        ctx.globalAlpha = .052;
        ctx.font = '600 ' + Math.max(10, Math.round(width / 100)) + 'px sans-serif';
        ctx.fillText(customer, 0, 26);
      }
      ctx.restore();
    });
    ctx.restore();
  }

  function render() {
    if (!sourceImage || !canvas || !wrap) return;
    var ratio = window.devicePixelRatio || 1;
    var available = Math.max(320, wrap.clientWidth - 36);
    var cssWidth = Math.min(sourceImage.naturalWidth * zoom, available * zoom);
    var cssHeight = cssWidth * sourceImage.naturalHeight / sourceImage.naturalWidth;
    canvas.width = Math.max(1, Math.round(cssWidth * ratio));
    canvas.height = Math.max(1, Math.round(cssHeight * ratio));
    canvas.style.width = cssWidth + 'px';
    canvas.style.height = cssHeight + 'px';
    var ctx = canvas.getContext('2d', { alpha: false });
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    ctx.fillStyle = '#fffdf8';
    ctx.fillRect(0, 0, cssWidth, cssHeight);
    ctx.drawImage(sourceImage, 0, 0, cssWidth, cssHeight);
    drawWatermark(ctx, cssWidth, cssHeight);
    updateControls();
  }

  function requestPageUrl(targetPage) {
    var data = new URLSearchParams();
    data.set('action', 'vava_digital_reader_page_token');
    data.set('nonce', config.nonce || '');
    data.set('product', config.uid || root.getAttribute('data-product-uid') || '');
    data.set('page', String(targetPage));
    return fetch(config.ajaxUrl || '/wp-admin/admin-ajax.php', {
      method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: data.toString()
    }).then(function (response) { return response.json(); }).then(function (response) {
      if (!response || !response.success || !response.data || !response.data.url) throw new Error(response && response.data && response.data.message ? response.data.message : 'تعذر إنشاء رابط الصفحة.');
      return response.data.url;
    });
  }

  function loadPage(targetPage) {
    targetPage = Math.max(1, Math.min(total, Number(targetPage || 1)));
    if (!targetPage || !total) return;
    var requestId = ++loadingRequest;
    clearError();
    setBusy(true);
    requestPageUrl(targetPage).then(function (url) {
      return new Promise(function (resolve, reject) {
        var image = new Image();
        image.decoding = 'async';
        image.onload = function () { resolve(image); };
        image.onerror = function () { reject(new Error((config.labels && config.labels.error) || 'تعذر تحميل الصفحة المحمية.')); };
        image.src = url;
      });
    }).then(function (image) {
      if (requestId !== loadingRequest) return;
      sourceImage = image;
      page = targetPage;
      saveState();
      render();
      setBusy(false);
      wrap.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
    }).catch(function (reason) {
      if (requestId !== loadingRequest) return;
      setBusy(false);
      showError(reason && reason.message ? reason.message : 'تعذر تحميل الصفحة.');
    });
  }

  if (prevButton) prevButton.addEventListener('click', function () { if (page > 1) loadPage(page - 1); });
  if (nextButton) nextButton.addEventListener('click', function () { if (page < total) loadPage(page + 1); });

  var zoomIn = reader.querySelector('[data-reader-zoom-in]');
  var zoomOut = reader.querySelector('[data-reader-zoom-out]');
  if (zoomIn) zoomIn.addEventListener('click', function () { zoom = Math.min(2, Math.round((zoom + .1) * 10) / 10); saveState(); render(); });
  if (zoomOut) zoomOut.addEventListener('click', function () { zoom = Math.max(.7, Math.round((zoom - .1) * 10) / 10); saveState(); render(); });

  var fullscreen = reader.querySelector('[data-reader-fullscreen]');
  function updateFullscreenControl() {
    if (!fullscreen) return;
    var active = document.fullscreenElement === reader;
    fullscreen.textContent = active ? (fullscreen.getAttribute('data-exit-label') || 'إغلاق') : (fullscreen.getAttribute('data-enter-label') || 'ملء الشاشة');
    fullscreen.setAttribute('aria-pressed', active ? 'true' : 'false');
  }
  if (fullscreen) fullscreen.addEventListener('click', function () {
    if (!document.fullscreenElement && reader.requestFullscreen) reader.requestFullscreen();
    else if (document.exitFullscreen) document.exitFullscreen();
  });

  reader.addEventListener('contextmenu', function (event) { event.preventDefault(); });
  reader.addEventListener('dragstart', function (event) { event.preventDefault(); });
  canvas.addEventListener('selectstart', function (event) { event.preventDefault(); });
  document.addEventListener('keydown', function (event) {
    var key = String(event.key || '').toLowerCase();
    if ((event.ctrlKey || event.metaKey) && (key === 's' || key === 'p' || key === 'u')) { event.preventDefault(); event.stopPropagation(); }
    if (event.key === 'ArrowRight') {
      if (root.getAttribute('dir') === 'rtl') { if (page > 1) loadPage(page - 1); }
      else if (page < total) loadPage(page + 1);
    }
    if (event.key === 'ArrowLeft') {
      if (root.getAttribute('dir') === 'rtl') { if (page < total) loadPage(page + 1); }
      else if (page > 1) loadPage(page - 1);
    }
  }, true);

  var resizeTimer = 0;
  window.addEventListener('resize', function () { window.clearTimeout(resizeTimer); resizeTimer = window.setTimeout(render, 120); });

  if (config.logoUrl) {
    brandLogo = new Image();
    brandLogo.decoding = 'async';
    brandLogo.onload = function () { if (sourceImage) render(); };
    brandLogo.src = config.logoUrl;
  }

  document.addEventListener('fullscreenchange', function () {
    reader.classList.toggle('is-fullscreen', document.fullscreenElement === reader);
    updateFullscreenControl();
    window.setTimeout(render, 80);
  });

  updateFullscreenControl();
  updateControls();
  loadPage(page);
}());
