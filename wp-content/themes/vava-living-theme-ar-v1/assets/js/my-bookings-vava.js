(() => {
  'use strict';
  const root = document.querySelector('[data-my-bookings-root]');
  if (!root) return;

  const selectors = Array.from(root.querySelectorAll('[data-booking-selector]'));
  const items = Array.from(root.querySelectorAll('[data-booking-item]'));
  const details = Array.from(root.querySelectorAll('[data-booking-detail]'));
  const search = root.querySelector('[data-booking-search]');
  const status = root.querySelector('[data-booking-status-filter]');
  const empty = root.querySelector('[data-booking-no-results]');
  const showAll = root.querySelector('[data-bookings-show-all]');
  const cancelModal = root.querySelector('[data-booking-cancel-modal]');
  const cancelId = cancelModal?.querySelector('[data-booking-cancel-id]');
  const cancelTitle = cancelModal?.querySelector('[data-booking-cancel-title]');
  const cancelCopy = cancelModal?.querySelector('[data-booking-cancel-copy]');
  const cancelSubmit = cancelModal?.querySelector('[data-booking-cancel-submit]');
  const cancelNonceSlot = cancelModal?.querySelector('[data-booking-cancel-nonce]');
  const nonceDataNode = root.querySelector('[data-booking-cancel-nonces]');
  let cancelNonces = {};
  try { cancelNonces = nonceDataNode ? JSON.parse(nonceDataNode.textContent || '{}') : {}; } catch (_) { cancelNonces = {}; }
  const isEnglish = document.documentElement.lang?.toLowerCase().startsWith('en') || root.dir === 'ltr';
  const accountAjaxUrl = root.dataset.accountAjaxUrl || '';


  const initAccountCollection = () => {
    const dashboard = root.querySelector('[data-vava-account-dashboard]');
    if (!dashboard) return;
    const section = dashboard.dataset.activeSection === 'products' ? 'products' : 'bookings';
    const collection = dashboard.querySelector(section === 'products' ? '#vava-account-products' : '#vava-account-bookings');
    const filterGroup = dashboard.querySelector(`[data-account-filter-group="${section}"]`);
    const cards = collection ? Array.from(collection.querySelectorAll('[data-account-card]')) : [];
    const pagination = collection?.querySelector('[data-account-pagination]');
    const noResults = collection?.querySelector('[data-account-filter-empty]');
    if (!collection || !filterGroup || !cards.length || !pagination) return;

    const pageSize = 5;
    const params = new URLSearchParams(window.location.search);
    const validFilters = Array.from(filterGroup.querySelectorAll('[data-account-filter]')).map((button) => button.dataset.accountFilter || 'all');
    let activeFilter = params.get('account_filter') || 'all';
    if (!validFilters.includes(activeFilter)) activeFilter = 'all';
    let currentPage = Math.max(1, Number.parseInt(params.get('account_page') || '1', 10) || 1);

    const syncUrl = () => {
      const url = new URL(window.location.href);
      if (activeFilter === 'all') url.searchParams.delete('account_filter');
      else url.searchParams.set('account_filter', activeFilter);
      if (currentPage <= 1) url.searchParams.delete('account_page');
      else url.searchParams.set('account_page', String(currentPage));
      window.history.replaceState({}, '', `${url.pathname}${url.search}${url.hash}`);
    };

    const createPageButton = (label, page, options = {}) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = label;
      button.dataset.accountPage = String(page);
      if (options.active) {
        button.classList.add('is-active');
        button.setAttribute('aria-current', 'page');
      }
      if (options.className) button.classList.add(options.className);
      if (options.disabled) button.disabled = true;
      return button;
    };

    const render = ({ scroll = false } = {}) => {
      const matching = cards.filter((card) => activeFilter === 'all' || card.dataset.accountFilterKey === activeFilter);
      const totalPages = Math.max(1, Math.ceil(matching.length / pageSize));
      currentPage = Math.min(Math.max(1, currentPage), totalPages);
      const start = (currentPage - 1) * pageSize;
      const visible = new Set(matching.slice(start, start + pageSize));
      cards.forEach((card) => {
        card.hidden = !visible.has(card);
        if (card.hidden) {
          const panel = card.querySelector('[data-account-details-panel]');
          const toggle = card.querySelector('[data-account-details-toggle]');
          if (panel) panel.hidden = true;
          if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
      });
      if (noResults) noResults.hidden = matching.length !== 0;
      pagination.hidden = matching.length <= pageSize;
      pagination.innerHTML = '';
      if (matching.length > pageSize) {
        pagination.appendChild(createPageButton(isEnglish ? 'Previous' : 'السابق', currentPage - 1, { className: 'is-nav', disabled: currentPage === 1 }));
        const pageNumbers = [];
        for (let page = 1; page <= totalPages; page += 1) {
          if (page === 1 || page === totalPages || Math.abs(page - currentPage) <= 1) pageNumbers.push(page);
        }
        let previous = 0;
        pageNumbers.forEach((page) => {
          if (previous && page - previous > 1) {
            const dots = document.createElement('span');
            dots.textContent = '…';
            dots.className = 'vava-account-pagination-dots';
            pagination.appendChild(dots);
          }
          pagination.appendChild(createPageButton(String(page), page, { active: page === currentPage }));
          previous = page;
        });
        pagination.appendChild(createPageButton(isEnglish ? 'Next' : 'التالي', currentPage + 1, { className: 'is-nav', disabled: currentPage === totalPages }));
      }
      filterGroup.querySelectorAll('[data-account-filter]').forEach((button) => {
        const active = button.dataset.accountFilter === activeFilter;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
      syncUrl();
      if (scroll) collection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    filterGroup.addEventListener('click', (event) => {
      const button = event.target.closest('[data-account-filter]');
      if (!button || !filterGroup.contains(button)) return;
      activeFilter = button.dataset.accountFilter || 'all';
      currentPage = 1;
      render({ scroll: true });
    });
    pagination.addEventListener('click', (event) => {
      const button = event.target.closest('[data-account-page]');
      if (!button || button.disabled) return;
      currentPage = Math.max(1, Number.parseInt(button.dataset.accountPage || '1', 10) || 1);
      render({ scroll: true });
    });
    render();
  };

  initAccountCollection();

  root.querySelectorAll('[data-customer-async-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      if (!accountAjaxUrl || typeof window.fetch !== 'function') return;
      event.preventDefault();
      if (!form.reportValidity()) return;

      const button = form.querySelector('button[type="submit"]');
      const statusBox = form.querySelector('.vava-customer-form-status');
      const idleLabel = button?.dataset.idleLabel || button?.textContent || '';
      const loadingLabel = button?.dataset.loadingLabel || (isEnglish ? 'Sending…' : 'جارٍ الإرسال…');
      if (button?.disabled) return;

      if (button) {
        button.disabled = true;
        button.classList.add('is-loading');
        button.textContent = loadingLabel;
      }
      if (statusBox) {
        statusBox.hidden = true;
        statusBox.className = 'vava-customer-form-status';
        statusBox.textContent = '';
      }

      try {
        const response = await fetch(accountAjaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: new FormData(form),
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        const message = payload?.data?.message || (payload?.success
          ? (isEnglish ? 'The secure message was sent.' : 'تم إرسال الرسالة الآمنة.')
          : (isEnglish ? 'The request could not be completed. Please try again.' : 'تعذر إتمام الطلب. حاول مرة أخرى.'));
        if (statusBox) {
          statusBox.hidden = false;
          statusBox.classList.add(payload?.success ? 'is-success' : 'is-error');
          statusBox.textContent = message;
        }
        if (payload?.success) {
          const emailInput = form.querySelector('input[type="email"]');
          if (emailInput) emailInput.value = '';
        }
      } catch (_) {
        if (statusBox) {
          statusBox.hidden = false;
          statusBox.classList.add('is-error');
          statusBox.textContent = isEnglish
            ? 'A connection error occurred. Please try again.'
            : 'حدث خطأ في الاتصال. حاول مرة أخرى.';
        }
      } finally {
        if (button) {
          button.disabled = false;
          button.classList.remove('is-loading');
          button.textContent = idleLabel;
        }
      }
    });
  });

  const closeMenus = (except = null) => {
    root.querySelectorAll('[data-booking-menu]').forEach((menu) => {
      if (menu !== except) menu.hidden = true;
    });
  };

  const activate = (id, shouldScroll = false) => {
    selectors.forEach((button) => {
      const active = button.dataset.bookingSelector === String(id);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
      button.closest('[data-booking-item]')?.classList.toggle('is-active', active);
    });
    details.forEach((panel) => {
      const active = panel.dataset.bookingDetail === String(id);
      panel.hidden = !active;
      panel.classList.toggle('is-active', active);
      if (active && shouldScroll && window.matchMedia('(max-width: 1050px)').matches) {
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
    closeMenus();
  };

  selectors.forEach((button) => button.addEventListener('click', () => activate(button.dataset.bookingSelector, true)));

  const filter = () => {
    const query = (search?.value || '').trim().toLocaleLowerCase();
    const wanted = status?.value || 'all';
    let firstVisible = null;
    let visibleCount = 0;
    items.forEach((item) => {
      const matchText = !query || (item.dataset.bookingSearchText || '').toLocaleLowerCase().includes(query);
      const matchStatus = wanted === 'all' || item.dataset.bookingStatus === wanted;
      const show = matchText && matchStatus;
      item.hidden = !show;
      if (show) {
        visibleCount += 1;
        if (!firstVisible) firstVisible = item;
      }
    });
    if (empty) empty.hidden = visibleCount !== 0;
    const activeVisible = items.find((item) => item.classList.contains('is-active') && !item.hidden);
    if (!activeVisible && firstVisible) {
      const button = firstVisible.querySelector('[data-booking-selector]');
      if (button) activate(button.dataset.bookingSelector);
    }
    if (!firstVisible) details.forEach((panel) => { panel.hidden = true; panel.classList.remove('is-active'); });
  };
  search?.addEventListener('input', filter);
  status?.addEventListener('change', filter);
  showAll?.addEventListener('click', () => {
    if (search) search.value = '';
    if (status) status.value = 'all';
    filter();
  });

  const closeCancelModal = () => {
    if (!cancelModal) return;
    cancelModal.classList.remove('is-open');
    document.body.classList.remove('vava-cancel-modal-open');
    window.setTimeout(() => { cancelModal.hidden = true; }, 180);
  };

  const openCancelModal = (trigger) => {
    if (!cancelModal || !cancelId || !cancelNonceSlot) return;
    const booking = String(trigger.dataset.cancelBooking || '');
    const mode = trigger.dataset.cancelMode || 'cancel';
    if (!booking || !cancelNonces[booking]) return;
    cancelId.value = booking;
    cancelNonceSlot.innerHTML = '';
    const nonce = document.createElement('input');
    nonce.type = 'hidden'; nonce.name = '_wpnonce'; nonce.value = cancelNonces[booking];
    cancelNonceSlot.appendChild(nonce);
    if (mode === 'request') {
      cancelTitle.textContent = isEnglish ? `Request cancellation for booking #${booking}?` : `طلب إلغاء الحجز #${booking}`;
      cancelCopy.textContent = isEnglish ? 'This confirmed or paid booking will be sent to the VAVA team for review.' : 'الحجز مؤكد أو مدفوع؛ سيتم إرسال طلب الإلغاء إلى فريق VAVA للمراجعة.';
      cancelSubmit.textContent = isEnglish ? 'Send cancellation request' : 'إرسال طلب الإلغاء';
    } else {
      cancelTitle.textContent = isEnglish ? `Cancel booking #${booking}?` : `إلغاء الحجز #${booking}؟`;
      cancelCopy.textContent = isEnglish ? 'The appointment will be released and made available again.' : 'سيُحرر الموعد ويصبح متاحًا للحجز من جديد.';
      cancelSubmit.textContent = isEnglish ? 'Confirm cancellation' : 'تأكيد الإلغاء';
    }
    cancelModal.hidden = false;
    document.body.classList.add('vava-cancel-modal-open');
    window.requestAnimationFrame(() => cancelModal.classList.add('is-open'));
    cancelModal.querySelector('textarea')?.focus();
  };

  root.addEventListener('click', (event) => {
    const menuToggle = event.target.closest('[data-booking-menu-toggle]');
    if (menuToggle) {
      event.preventDefault(); event.stopPropagation();
      const menu = menuToggle.parentElement?.querySelector('[data-booking-menu]');
      if (menu) {
        const willOpen = menu.hidden;
        closeMenus(menu);
        menu.hidden = !willOpen;
      }
      return;
    }
    const cancel = event.target.closest('[data-cancel-booking]');
    if (cancel) { event.preventDefault(); closeMenus(); openCancelModal(cancel); return; }
    if (event.target.closest('[data-booking-cancel-close]')) { event.preventDefault(); closeCancelModal(); return; }
    if (!event.target.closest('[data-booking-menu]')) closeMenus();
  });

  document.addEventListener('click', (event) => {
    if (!root.contains(event.target)) closeMenus();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeMenus();
      if (cancelModal && !cancelModal.hidden) closeCancelModal();
    }
  });

  root.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-account-details-toggle]');
    if (!toggle) return;
    event.preventDefault();
    const panelId = toggle.getAttribute('aria-controls') || '';
    const panel = panelId ? document.getElementById(panelId) : null;
    if (!panel) return;
    const opening = panel.hidden;
    panel.hidden = !opening;
    toggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
    toggle.textContent = opening
      ? (isEnglish ? 'Hide details' : 'إخفاء التفاصيل')
      : (toggle.dataset.defaultLabel || (isEnglish ? 'View details' : 'عرض التفاصيل'));
  });

  root.querySelectorAll('.vava-booking-receipt-upload input[type="file"]').forEach((input) => {
    input.addEventListener('change', () => {
      const strong = input.closest('label')?.querySelector('strong');
      if (strong && input.files?.[0]) strong.textContent = input.files[0].name;
    });
  });


  const avatarInput = root.querySelector('[data-profile-avatar-input]');
  const avatarPreview = root.querySelector('[data-profile-avatar-preview]');
  avatarInput?.addEventListener('change', () => {
    const file = avatarInput.files?.[0];
    if (!file || !avatarPreview || !file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.addEventListener('load', () => {
      avatarPreview.innerHTML = '';
      const image = document.createElement('img');
      image.src = String(reader.result || '');
      image.alt = '';
      avatarPreview.appendChild(image);
    });
    reader.readAsDataURL(file);
  });

  root.querySelectorAll('[data-account-details-toggle]').forEach((toggle) => {
    toggle.dataset.defaultLabel = toggle.textContent.trim();
  });

  if (window.location.hash && root.querySelector(window.location.hash)) {
    window.setTimeout(() => root.querySelector(window.location.hash)?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 120);
  }

  filter();
})();
