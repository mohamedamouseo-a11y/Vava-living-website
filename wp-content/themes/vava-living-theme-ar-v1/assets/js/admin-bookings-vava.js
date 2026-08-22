(function () {
  'use strict';

  const config = window.VavaBookingsAdmin || {};
  const ajaxUrl = config.ajaxUrl || window.ajaxurl;
  if (!ajaxUrl) return;

  const detailsPage = document.querySelector('[data-vava-booking-details-page]');
  const activeBooking = Number(detailsPage?.dataset.bookingId || 0);

  function post(data, files) {
    const body = files || new FormData();
    Object.entries(data).forEach(([key, value]) => body.append(key, value));
    return fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body }).then((response) => response.json());
  }

  function toast(message, isError) {
    let node = document.querySelector('.vava-booking-admin-toast');
    if (!node) {
      node = document.createElement('div');
      node.className = 'vava-booking-admin-toast';
      document.body.appendChild(node);
    }
    node.textContent = message || '';
    node.classList.toggle('is-error', Boolean(isError));
    requestAnimationFrame(() => node.classList.add('is-visible'));
    clearTimeout(node._timer);
    node._timer = setTimeout(() => node.classList.remove('is-visible'), 3600);
  }

  function updateCounts(counts) {
    if (!counts) return;
    Object.entries(counts).forEach(([key, value]) => {
      document.querySelectorAll(`[data-vava-count-key="${key}"], [data-booking-count="${key}"]`).forEach((node) => { node.textContent = value; });
    });
  }

  function updateRow(data) {
    const id = Number(data.bookingId || 0);
    if (!id) return;
    const row = document.querySelector(`#post-${id}`);
    if (!row) return;
    if (data.stateHtml) {
      const cell = row.querySelector('.column-vava_booking_state');
      if (cell) cell.innerHTML = data.stateHtml;
    }
    if (data.receiptHtml) {
      const cell = row.querySelector('.column-vava_booking_receipt');
      if (cell) cell.innerHTML = data.receiptHtml;
    }
    if (data.actionsHtml) {
      const cell = row.querySelector('.column-vava_booking_actions');
      if (cell) cell.innerHTML = data.actionsHtml;
    }
  }

  function finishDetailsAction(message) {
    toast(message || 'تم تحديث الحجز.');
    window.setTimeout(() => window.location.reload(), 550);
  }

  function runAction(button) {
    const booking = Number(button.dataset.bookingId || activeBooking || 0);
    const decision = button.dataset.decision || '';
    if (!booking || !decision || button.disabled) return;
    const confirmation = decision === 'cancel_booking'
      ? config.confirmCancel
      : (decision === 'reject_bank' ? config.confirmReject : config.confirmApprove);
    if (confirmation && !window.confirm(confirmation)) return;
    const note = document.querySelector('[name="action_note"]');
    button.classList.add('is-loading');
    button.disabled = true;
    post({ action: 'vava_booking_admin_action', nonce: config.nonce || '', booking, decision, note: note ? note.value : '' })
      .then((json) => {
        if (!json.success) throw new Error(json.data?.message || 'تعذر تحديث الحجز.');
        if (detailsPage) { finishDetailsAction(json.data.message); return; }
        updateRow(json.data || {});
        updateCounts(json.data?.counts);
        toast(json.data?.message || 'تم تحديث الحجز.');
      })
      .catch((error) => toast(error.message, true))
      .finally(() => { button.classList.remove('is-loading'); button.disabled = false; });
  }

  function executeSelectedAction(button) {
    const wrap = button.closest('[data-booking-actions]');
    const select = wrap?.querySelector('.vava-booking-action-select');
    const decision = String(select?.value || button.dataset.decision || '');
    if (!decision) {
      toast('اختر إجراءً أولًا.', true);
      return;
    }
    if ('view_details' === decision) {
      const option = select?.options?.[select.selectedIndex];
      const detailsUrl = select?.dataset.selectedUrl || button.dataset.selectedUrl || option?.dataset.url || '';
      if (detailsUrl) {
        window.location.assign(detailsUrl);
      } else {
        toast('تعذر فتح صفحة التفاصيل.', true);
      }
      return;
    }
    button.dataset.decision = decision;
    button.dataset.bookingId = select?.dataset.bookingId || button.dataset.bookingId || '';
    runAction(button);
  }

  function saveNote(button) {
    const booking = Number(button.dataset.bookingId || activeBooking || 0);
    const note = document.querySelector('[name="action_note"]');
    if (!booking || button.disabled) return;
    button.disabled = true;
    button.classList.add('is-loading');
    post({ action: 'vava_booking_admin_action', nonce: config.nonce || '', booking, decision: 'save_note', note: note ? note.value : '' })
      .then((json) => {
        if (!json.success) throw new Error(json.data?.message || 'تعذر حفظ الملاحظة.');
        if (detailsPage) { finishDetailsAction(json.data.message); return; }
        toast(json.data?.message || 'تم حفظ الملاحظة.');
      })
      .catch((error) => toast(error.message, true))
      .finally(() => { button.disabled = false; button.classList.remove('is-loading'); });
  }

  function toggleRefundPanel(show) {
    const panel = document.querySelector('[data-vava-refund-panel]');
    if (!panel) return;
    panel.hidden = !show;
    if (show) panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function submitRefund(button) {
    const panel = button.closest('[data-vava-refund-panel]');
    const booking = Number(button.dataset.bookingId || activeBooking || 0);
    if (!panel || !booking || button.disabled) return;
    const body = new FormData();
    const file = panel.querySelector('[data-refund-proof]')?.files?.[0];
    if (file) body.append('proof', file);
    button.disabled = true;
    button.classList.add('is-loading');
    post({
      action: 'vava_booking_admin_refund', nonce: config.nonce || '', booking,
      refund_type: panel.querySelector('[data-refund-type]')?.value || 'full',
      amount: panel.querySelector('[data-refund-amount]')?.value || '',
      date: panel.querySelector('[data-refund-date]')?.value || '',
      method: panel.querySelector('[data-refund-method]')?.value || '',
      reference: panel.querySelector('[data-refund-reference]')?.value || '',
      note: panel.querySelector('[data-refund-note]')?.value || ''
    }, body)
      .then((json) => {
        if (!json.success) throw new Error(json.data?.message || 'تعذر تسجيل الاسترداد.');
        if (detailsPage) { finishDetailsAction(json.data.message); return; }
        updateRow(json.data || {});
        updateCounts(json.data?.counts);
        toast(json.data?.message || 'تم تسجيل الاسترداد.');
      })
      .catch((error) => toast(error.message, true))
      .finally(() => { button.disabled = false; button.classList.remove('is-loading'); });
  }

  function getActionPickerMenu(picker) {
    if (!picker) return null;
    return picker._vavaActionMenu || picker.querySelector('[data-vava-action-picker-menu]');
  }

  function mountActionMenu(picker, menu) {
    if (!picker || !menu || menu.parentElement === document.body) return;
    const marker = document.createComment('vava-action-menu');
    menu.parentNode.insertBefore(marker, menu);
    menu._vavaMarker = marker;
    menu._vavaPicker = picker;
    picker._vavaActionMenu = menu;
    menu.classList.add('is-portal');
    document.body.appendChild(menu);
  }

  function restoreActionMenu(menu) {
    if (!menu) return;
    const marker = menu._vavaMarker;
    if (marker?.parentNode) {
      marker.parentNode.insertBefore(menu, marker);
      marker.remove();
    }
    menu.classList.remove('is-portal');
    menu.style.removeProperty('position');
    menu.style.removeProperty('left');
    menu.style.removeProperty('right');
    menu.style.removeProperty('top');
    menu.style.removeProperty('bottom');
    menu.style.removeProperty('width');
    menu.style.removeProperty('z-index');
    menu.style.removeProperty('--vava-action-menu-left');
    menu.style.removeProperty('--vava-action-menu-top');
    menu.style.removeProperty('--vava-action-menu-width');
    menu._vavaMarker = null;
  }

  function closeActionPicker(picker) {
    if (!picker) return;
    const menu = getActionPickerMenu(picker);
    const trigger = picker.querySelector('[data-vava-action-picker-trigger]');
    if (menu) {
      menu.hidden = true;
      restoreActionMenu(menu);
    }
    if (trigger) trigger.setAttribute('aria-expanded', 'false');
  }

  function setActionPickerSelection(picker, pickerOption, closeMenu) {
    if (!picker || !pickerOption) return false;
    const value = String(pickerOption.dataset.vavaActionValue || '');
    const url = String(pickerOption.dataset.url || '');
    const select = picker.querySelector('.vava-booking-action-select');
    const label = picker.querySelector('[data-vava-action-picker-label]');
    const wrap = picker.closest('[data-booking-actions]');
    const execute = wrap?.querySelector('[data-vava-booking-action-execute]');

    if (!value || !select) return false;
    select.value = value;
    select.dataset.selectedUrl = url;
    select.setAttribute('data-selected-action', value);
    select.dispatchEvent(new Event('change', { bubbles: true }));

    if (label) label.textContent = pickerOption.textContent.trim();
    getActionPickerMenu(picker)?.querySelectorAll('[data-vava-action-value]').forEach((node) => {
      node.setAttribute('aria-selected', node === pickerOption ? 'true' : 'false');
    });
    picker.classList.add('has-selection');

    if (execute) {
      execute.dataset.decision = value;
      execute.dataset.selectedUrl = url;
      execute.dataset.bookingId = select.dataset.bookingId || execute.dataset.bookingId || '';
      execute.disabled = false;
    }

    if (closeMenu) closeActionPicker(picker);
    return true;
  }

  function positionActionMenu(picker, menu) {
    if (!picker || !menu) return;
    const trigger = picker.querySelector('[data-vava-action-picker-trigger]');
    if (!trigger) return;

    mountActionMenu(picker, menu);
    const rect = trigger.getBoundingClientRect();
    const viewportPadding = 12;
    const menuWidth = Math.min(Math.max(190, rect.width), window.innerWidth - (viewportPadding * 2));
    menu.style.setProperty('--vava-action-menu-width', `${Math.round(menuWidth)}px`);
    const measuredHeight = Math.min(300, Math.max(80, menu.getBoundingClientRect().height || menu.scrollHeight || 180));
    const roomBelow = window.innerHeight - rect.bottom;
    const openBelow = roomBelow >= measuredHeight + 10 || rect.top < measuredHeight + 10;
    const top = openBelow
      ? Math.min(window.innerHeight - measuredHeight - viewportPadding, rect.bottom + 6)
      : Math.max(viewportPadding, rect.top - measuredHeight - 6);
    const pageDirection = window.getComputedStyle(document.documentElement).direction || document.documentElement.dir || 'rtl';
    let left = pageDirection === 'rtl' ? rect.right - menuWidth : rect.left;
    left = Math.min(Math.max(viewportPadding, left), window.innerWidth - menuWidth - viewportPadding);

    menu.style.setProperty('--vava-action-menu-left', `${Math.round(left)}px`);
    menu.style.setProperty('--vava-action-menu-top', `${Math.round(top)}px`);
    menu.style.position = 'fixed';
    menu.style.left = `${Math.round(left)}px`;
    menu.style.right = 'auto';
    menu.style.top = `${Math.round(top)}px`;
    menu.style.bottom = 'auto';
    menu.style.width = `${Math.round(menuWidth)}px`;
    menu.style.zIndex = '1001000';
  }

  function closeActionMenus(except) {
    document.querySelectorAll('[data-vava-action-picker]').forEach((picker) => {
      const menu = getActionPickerMenu(picker);
      if (menu !== except) closeActionPicker(picker);
    });
  }

  document.addEventListener('pointerdown', (event) => {
    const pickerOption = event.target.closest('[data-vava-action-value]');
    if (!pickerOption) return;
    const menu = pickerOption.closest('[data-vava-action-picker-menu]');
    const picker = menu?._vavaPicker || pickerOption.closest('[data-vava-action-picker]');
    setActionPickerSelection(picker, pickerOption, false);
  }, true);

  document.addEventListener('click', (event) => {
    const pickerTrigger = event.target.closest('[data-vava-action-picker-trigger]');
    if (pickerTrigger) {
      event.preventDefault();
      event.stopPropagation();
      const picker = pickerTrigger.closest('[data-vava-action-picker]');
      const menu = getActionPickerMenu(picker);
      if (!menu) return;
      const willOpen = menu.hidden;
      closeActionMenus(willOpen ? menu : null);
      if (!willOpen) {
        closeActionPicker(picker);
        return;
      }
      menu.hidden = false;
      pickerTrigger.setAttribute('aria-expanded', 'true');
      requestAnimationFrame(() => positionActionMenu(picker, menu));
      return;
    }

    const pickerOption = event.target.closest('[data-vava-action-value]');
    if (pickerOption) {
      event.preventDefault();
      event.stopPropagation();
      const menu = pickerOption.closest('[data-vava-action-picker-menu]');
      const picker = menu?._vavaPicker || pickerOption.closest('[data-vava-action-picker]');
      setActionPickerSelection(picker, pickerOption, true);
      return;
    }

    const clickedMenu = event.target.closest('[data-vava-action-picker-menu]');
    const clickedPicker = event.target.closest('[data-vava-action-picker]');
    if (!clickedMenu && !clickedPicker) closeActionMenus();

    const refundToggle = event.target.closest('[data-vava-refund-toggle]');
    if (refundToggle) { event.preventDefault(); toggleRefundPanel(true); return; }
    if (event.target.closest('[data-vava-refund-close]')) { event.preventDefault(); toggleRefundPanel(false); return; }

    const refundSubmit = event.target.closest('[data-vava-booking-refund-submit]');
    if (refundSubmit) { event.preventDefault(); submitRefund(refundSubmit); return; }

    const execute = event.target.closest('[data-vava-booking-action-execute]');
    if (execute) { event.preventDefault(); executeSelectedAction(execute); return; }

    const action = event.target.closest('[data-vava-booking-action]');
    if (action) { event.preventDefault(); runAction(action); return; }

    const save = event.target.closest('.vava-booking-save-note');
    if (save) { event.preventDefault(); saveNote(save); return; }
  });

  document.addEventListener('change', (event) => {
    const proof = event.target.closest('[data-refund-proof]');
    if (proof) {
      const name = proof.closest('.vava-booking-upload-control')?.querySelector('[data-refund-proof-name]');
      if (name) name.textContent = proof.files?.[0]?.name || 'لم يتم اختيار ملف';
      return;
    }

    const refundType = event.target.closest('[data-refund-type]');
    if (refundType) {
      const panel = refundType.closest('[data-vava-refund-panel]');
      const amount = panel?.querySelector('[data-refund-amount]');
      if (amount) {
        const isFull = refundType.value === 'full';
        amount.readOnly = isFull;
        if (isFull) amount.value = amount.max || amount.value;
        else amount.focus();
      }
      return;
    }

    const select = event.target.closest('.vava-booking-action-select');
    if (!select) return;
    const picker = select.closest('[data-vava-action-picker]');
    const option = select.options?.[select.selectedIndex];
    const label = picker?.querySelector('[data-vava-action-picker-label]');
    const execute = picker?.closest('[data-booking-actions]')?.querySelector('[data-vava-booking-action-execute]');
    if (label && option) label.textContent = option.textContent.trim();
    if (execute) {
      execute.dataset.decision = select.value || '';
      execute.dataset.selectedUrl = select.dataset.selectedUrl || option?.dataset.url || '';
      execute.disabled = !select.value;
    }
  });

  document.querySelectorAll('[data-refund-type]').forEach((select) => {
    const amount = select.closest('[data-vava-refund-panel]')?.querySelector('[data-refund-amount]');
    if (amount) amount.readOnly = select.value === 'full';
  });

  window.addEventListener('resize', function () { closeActionMenus(); });
  window.addEventListener('scroll', function () { closeActionMenus(); }, true);

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeActionMenus();
      toggleRefundPanel(false);
    }
  });
})();
