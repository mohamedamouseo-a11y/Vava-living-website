(function () {
  'use strict';
  function element(tag, className, html) {
    var node = document.createElement(tag);
    if (className) node.className = className;
    if (html !== undefined) node.innerHTML = html;
    return node;
  }
  function buildSidebar(config) {
    var menu = document.getElementById('adminmenu');
    var wrap = document.getElementById('adminmenuwrap');
    var main = document.getElementById('adminmenumain');
    if (!menu || !wrap || !main) return;

    /* On the isolated customer screens the lightweight bookings entry uses an
       absolute URL as its WordPress slug. Reorder the rendered nodes here so
       Bookings stays immediately above Products without changing the PHP slug
       that WordPress already handles safely. */
    var productsItem = document.getElementById('toplevel_page_vava-products-orders');
    var bookingsItem = Array.prototype.slice.call(menu.children).filter(function (item) {
      var link = item.querySelector(':scope > a.menu-top');
      return link && (link.getAttribute('href') || '').indexOf('post_type=vava_booking') !== -1 &&
        (link.getAttribute('href') || '').indexOf('vava_order_scope=products') === -1;
    })[0];
    if (productsItem && bookingsItem && bookingsItem.nextElementSibling !== productsItem) {
      menu.insertBefore(bookingsItem, productsItem);
    }
    var oldLogo = document.getElementById('vava-admin-logo');
    if (!oldLogo) {
      var logo = element('a');
      logo.id = 'vava-admin-logo';
      logo.href = config.homeUrl || '/';
      logo.innerHTML = '<img alt="VAVA Living">';
      logo.querySelector('img').src = config.logo || '';
      main.insertBefore(logo, wrap);
    }
    var oldAccount = document.getElementById('vava-admin-account');
    if (!oldAccount) {
      var account = element('div');
      account.id = 'vava-admin-account';
      account.innerHTML = '<a class="vava-account-logout"><span class="dashicons dashicons-exit"></span><b></b></a>';
      account.querySelector('.vava-account-logout').href = config.logoutUrl || '#';
      account.querySelector('.vava-account-logout b').textContent = config.logoutText || 'تسجيل الخروج';
      main.appendChild(account);
    }

    /* Treat every top-level group as a real accordion. In particular, the
       Products parent must open its own children instead of navigating to the
       Bookings post-type screen and inheriting that group's active state. */
    var parents = Array.prototype.slice.call(menu.querySelectorAll('li.wp-has-submenu'));
    function setOpen(item, open) {
      item.classList.toggle('vava-menu-open', open);
      var trigger = item.querySelector(':scope > a.menu-top');
      if (trigger) trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    parents.forEach(function (item) {
      var trigger = item.querySelector(':scope > a.menu-top');
      if (!trigger) return;
      trigger.setAttribute('aria-haspopup', 'true');
      setOpen(item, item.classList.contains('wp-has-current-submenu'));
      trigger.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var opening = !item.classList.contains('vava-menu-open');
        parents.forEach(function (other) { setOpen(other, false); });
        setOpen(item, opening);
      });
    });

    /* Correct stale WordPress classes on the scoped product-orders screen.
       PHP supplies the same state; this also survives cached admin markup. */
    var query = new URLSearchParams(window.location.search);
    if (query.get('post_type') === 'vava_booking' && query.get('vava_order_scope') === 'products') {
      parents.forEach(function (item) {
        var topLink = item.querySelector(':scope > a.menu-top');
        var topHref = topLink ? (topLink.getAttribute('href') || '') : '';
        var isProducts = item.id === 'toplevel_page_vava-products-orders' ||
          topHref.indexOf('vava-products-orders') !== -1 ||
          !!item.querySelector('.wp-submenu a[href*="vava_order_scope=products"]');
        item.classList.toggle('wp-has-current-submenu', isProducts);
        item.classList.toggle('current', isProducts);
        var childLinks = item.querySelectorAll(':scope > .wp-submenu a');
        Array.prototype.forEach.call(childLinks, function (link) {
          var childIsCurrent = isProducts && (link.getAttribute('href') || '').indexOf('vava_order_scope=products') !== -1;
          link.classList.toggle('current', childIsCurrent);
          if (link.parentElement) link.parentElement.classList.toggle('current', childIsCurrent);
          if (childIsCurrent) link.setAttribute('aria-current', 'page');
          else link.removeAttribute('aria-current');
        });
        setOpen(item, isProducts);
      });
    }
  }

  function buildProfile(config) {
    var isOwnProfile = document.body.classList.contains('profile-php');
    var isCustomerEdit = document.body.classList.contains('user-edit-php');
    if (!isOwnProfile && !isCustomerEdit) return;
    var content = document.getElementById('wpbody-content');
    var form = document.getElementById('your-profile');
    if (!content || !form || form.classList.contains('vava-profile-form')) return;

    content.classList.add('vava-profile-wrap');
    form.classList.add('vava-profile-form');

    var title = content.querySelector('.wrap > h1') || content.querySelector('h1');
    var addCustomerButton = isCustomerEdit ? content.querySelector('.page-title-action') : null;
    if (title) title.remove();
    var hero = element('section', 'vava-profile-hero');
    hero.innerHTML = '<div class="vava-profile-person"><img alt=""><div><span>VAVA LIVING</span><h1></h1><p>إدارة بيانات الحساب وصورة الملف الشخصي بأمان</p></div></div><div class="vava-profile-mark">V</div>';
    hero.querySelector('img').src = config.avatar || '';
    var editedName = form.querySelector('#display_name');
    hero.querySelector('h1').textContent = editedName && editedName.value ? editedName.value : (config.displayName || 'حسابك');
    if (isCustomerEdit) {
      hero.querySelector('p').textContent = 'إدارة بيانات العميل وصورة الحساب بأمان';
      content.classList.add('vava-customer-edit-wrap');
      var heroActions = element('div', 'vava-profile-hero-actions');
      if (addCustomerButton) {
        addCustomerButton.classList.add('vava-profile-add-customer');
        addCustomerButton.textContent = 'إضافة عميل';
        heroActions.appendChild(addCustomerButton);
      }
      hero.appendChild(heroActions);
    } else if (isOwnProfile) {
      hero.appendChild(element('div', 'vava-profile-hero-actions'));
    }
    form.parentNode.insertBefore(hero, form);

    var groups = [];
    var current = null;
    Array.prototype.slice.call(form.children).forEach(function (node) {
      if (/^H[23]$/.test(node.tagName)) {
        current = element('section', 'vava-profile-card');
        current.appendChild(node);
        form.appendChild(current);
        groups.push(current);
      } else if (node.classList && node.classList.contains('submit')) {
        var actions = element('div', 'vava-profile-actions');
        actions.appendChild(node);
        form.appendChild(actions);
      } else if (current && node.tagName !== 'SCRIPT') {
        current.appendChild(node);
      }
    });

    groups.forEach(function (card, index) {
      var heading = (card.querySelector('h2,h3') || {}).textContent || '';
      if (index === 0 || /خيارات شخصية|إعدادات شخصية|Personal Options/i.test(heading)) card.classList.add('is-personal');
      if (/^\s*(الاسم|Name)\s*$/i.test(heading)) card.classList.add('is-identity');
      if (/تواصل|Contact/i.test(heading)) card.classList.add('is-contact');
      if (/نبذة عن (?:نفسك|العضو|المستخدم|العميل)|About (?:Yourself|the User|User|Customer)/i.test(heading)) card.classList.add('is-about');
      if (/إدارة الحساب|Account Management/i.test(heading)) card.classList.add('is-account-management');
      if (/Application|تطبيق/i.test(heading)) card.classList.add('is-application-passwords');
    });

    var personalCard = groups.filter(function (card) { return card.classList.contains('is-personal'); })[0];
    var identityCard = groups.filter(function (card) { return card.classList.contains('is-identity'); })[0];
    var aboutCard = groups.filter(function (card) { return card.classList.contains('is-about'); })[0];
    if (identityCard) {
      var accountHeading = identityCard.querySelector('h2,h3');
      if (accountHeading) accountHeading.textContent = 'البيانات الشخصية';
    }
    if (personalCard) {
      var personalHeading = personalCard.querySelector('h2,h3');
      if (personalHeading) personalHeading.textContent = 'إعدادات شخصية';
    }

    function pairRows(card, firstSelector, secondSelector) {
      if (!card) return;
      var first = card.querySelector(firstSelector);
      var second = card.querySelector(secondSelector);
      if (!first || !second || !first.parentNode || first.parentNode !== second.parentNode) return;
      var row = element('tr', 'vava-profile-row-pair');
      var cell = element('td');
      cell.colSpan = 2;
      var pair = element('div', 'vava-profile-field-pair');
      first.classList.add('vava-profile-paired-field');
      second.classList.add('vava-profile-paired-field');
      first.parentNode.insertBefore(row, first);
      pair.appendChild(first);
      pair.appendChild(second);
      cell.appendChild(pair);
      row.appendChild(cell);
    }
    pairRows(identityCard, '.user-first-name-wrap', '.user-last-name-wrap');
    pairRows(identityCard, '.user-nickname-wrap', '.user-display-name-wrap');

    if (personalCard) {
      var personalTable = personalCard.querySelector('.form-table tbody');
      var bioRow = form.querySelector('.user-description-wrap');
      if (personalTable && bioRow) personalTable.appendChild(bioRow);
      var avatarTable = form.querySelector('.vava-native-avatar-table');
      if (avatarTable) {
        personalCard.appendChild(avatarTable);
        var editedAvatar = avatarTable.querySelector('.vava-avatar-control > img');
        if (editedAvatar && editedAvatar.src) hero.querySelector('img').src = editedAvatar.src;
      }
    }
    /* The biography field now lives in Personal Settings. Remove its former,
       empty "About Yourself" card instead of leaving a duplicate block. */
    if (aboutCard) aboutCard.remove();

    /* On customer editing, keep the primary save action beside Add Customer in
       the hero while retaining its association with the native profile form. */
    if (isCustomerEdit) {
      var submitWrap = form.querySelector('.vava-profile-actions .submit');
      var submitControl = submitWrap ? submitWrap.querySelector('input[type="submit"],button[type="submit"]') : null;
      var customerHeroActions = hero.querySelector('.vava-profile-hero-actions');
      if (submitWrap && submitControl && customerHeroActions) {
        if (!form.id) form.id = 'vava-customer-profile-form';
        submitControl.setAttribute('form', form.id);
        submitControl.classList.add('vava-profile-update-customer');
        submitControl.value = 'تحديث بيانات العميل';
        if (submitControl.tagName === 'BUTTON') submitControl.textContent = 'تحديث بيانات العميل';
        customerHeroActions.insertBefore(submitWrap, customerHeroActions.firstChild);
        var emptyActions = form.querySelector('.vava-profile-actions');
        if (emptyActions && !emptyActions.children.length) emptyActions.remove();
      }
    }
    if (isOwnProfile) {
      var profileSubmitWrap = form.querySelector('.vava-profile-actions .submit');
      var profileSubmitControl = profileSubmitWrap ? profileSubmitWrap.querySelector('input[type="submit"],button[type="submit"]') : null;
      var profileHeroActions = hero.querySelector('.vava-profile-hero-actions');
      if (profileSubmitWrap && profileSubmitControl && profileHeroActions) {
        if (!form.id) form.id = 'vava-own-profile-form';
        profileSubmitControl.setAttribute('form', form.id);
        profileSubmitControl.classList.add('vava-profile-update-own');
        profileSubmitControl.value = 'تحديث ملفك الشخصي';
        if (profileSubmitControl.tagName === 'BUTTON') profileSubmitControl.textContent = 'تحديث ملفك الشخصي';
        profileHeroActions.appendChild(profileSubmitWrap);
        var emptyProfileActions = form.querySelector('.vava-profile-actions');
        if (emptyProfileActions && !emptyProfileActions.children.length) emptyProfileActions.remove();
      }
    }
  }
  function buildPageScrollbar() {
    if (window.matchMedia('(max-width: 782px)').matches) return;
    var root = document.documentElement;
    var old = document.querySelector('.vava-page-scrollbar');
    if (old) old.remove();
    var track = element('div', 'vava-page-scrollbar');
    var thumb = element('span');
    track.setAttribute('aria-hidden', 'true');
    track.appendChild(thumb);
    document.body.appendChild(track);
    root.classList.add('vava-admin-scroll-custom');

    function metrics() {
      var height = Math.max(root.scrollHeight, document.body.scrollHeight);
      var viewport = window.innerHeight;
      var maxScroll = Math.max(0, height - viewport);
      var trackHeight = track.clientHeight;
      var thumbHeight = maxScroll ? Math.max(42, Math.round(trackHeight * viewport / height)) : trackHeight;
      var maxTop = Math.max(0, trackHeight - thumbHeight);
      thumb.style.height = thumbHeight + 'px';
      thumb.style.top = (maxScroll ? Math.round(window.scrollY / maxScroll * maxTop) : 0) + 'px';
      track.hidden = maxScroll === 0;
      return { maxScroll: maxScroll, maxTop: maxTop };
    }
    function scrollFromPointer(clientY, offset) {
      var box = track.getBoundingClientRect();
      var values = metrics();
      var top = Math.max(0, Math.min(values.maxTop, clientY - box.top - offset));
      window.scrollTo(0, values.maxTop ? top / values.maxTop * values.maxScroll : 0);
    }
    thumb.addEventListener('pointerdown', function (event) {
      event.preventDefault();
      var offset = event.clientY - thumb.getBoundingClientRect().top;
      thumb.setPointerCapture(event.pointerId);
      function move(moveEvent) { scrollFromPointer(moveEvent.clientY, offset); }
      function done(upEvent) {
        thumb.removeEventListener('pointermove', move);
        thumb.removeEventListener('pointerup', done);
        thumb.removeEventListener('pointercancel', done);
        if (thumb.hasPointerCapture(upEvent.pointerId)) thumb.releasePointerCapture(upEvent.pointerId);
      }
      thumb.addEventListener('pointermove', move);
      thumb.addEventListener('pointerup', done);
      thumb.addEventListener('pointercancel', done);
    });
    track.addEventListener('pointerdown', function (event) {
      if (event.target === track) scrollFromPointer(event.clientY, thumb.offsetHeight / 2);
    });
    window.addEventListener('scroll', metrics, { passive: true });
    window.addEventListener('resize', metrics);
    new MutationObserver(metrics).observe(document.body, { childList: true, subtree: true });
    metrics();
  }
  function buildDashboard(config) {
    if (!document.body.classList.contains('index-php')) return;
    var content = document.getElementById('wpbody-content');
    if (!content || content.querySelector('.vava-dashboard-wrap')) return;
    var dashboard = config.dashboard || {};
    var labels = {
      pages: ['الصفحات', 'dashicons-admin-page', 'إدارة صفحات الموقع'], bookings: ['الحجوزات', 'dashicons-calendar-alt', 'مراجعة الحجوزات والطلبات'],
      orders: ['المنتجات', 'dashicons-cart', 'متابعة طلبات المنتجات الرقمية'], journal: ['المجلة', 'dashicons-book-alt', 'تحرير المجلة والمقالات'], booking: ['الحجز', 'dashicons-clock', 'إعدادات صفحة الحجز']
    };
    var shell = element('div', 'vava-dashboard-wrap');
    shell.innerHTML = '<main class="vava-dashboard-stage"><div class="vava-stage-wash"></div><header class="vava-stage-heading"><span>VAVA LIVING</span><h1>لوحة التحكم</h1><p>إدارة محتوى وتجربة VAVA من مكان واحد</p></header><div class="vava-layer-scene"><div class="vava-code-glow"></div><div class="vava-tree-coordinate-layer"><img class="vava-tree-art" alt="" /><div class="vava-sparks" aria-hidden="true"></div></div></div><div class="vava-stage-status"><i></i><span>النظام يعمل بصورة طبيعية</span></div></main>';

    var quickActions = element('nav', 'vava-dashboard-quick-actions');
    quickActions.setAttribute('aria-label', 'روابط الحساب والموقع');
    quickActions.innerHTML = '<a class="vava-dashboard-quick-profile"><i class="dashicons dashicons-admin-users" aria-hidden="true"></i><span>الملف الشخصي</span></a><a class="vava-dashboard-quick-home"><i class="dashicons dashicons-admin-home" aria-hidden="true"></i><span>العودة للموقع</span></a>';
    quickActions.querySelector('.vava-dashboard-quick-profile').href = config.profileUrl || '#';
    quickActions.querySelector('.vava-dashboard-quick-home').href = config.homeUrl || '/';
    shell.querySelector('.vava-dashboard-stage').appendChild(quickActions);
    shell.querySelector('.vava-tree-art').src = config.treeImage || '';
    if (config.backgroundImage) shell.querySelector('.vava-dashboard-stage').style.setProperty('--vava-dashboard-bg', 'url("' + config.backgroundImage.replace(/"/g, '%22') + '")');
    var layer = shell.querySelector('.vava-tree-coordinate-layer');
    var sparks = shell.querySelector('.vava-sparks');
    var sparkPoints = [
      [50,82],[49,74],[51,66],[49,58],[50,50],[49,42],[50,34],
      [44,53],[39,47],[34,39],[29,32],[24,27],[18,23],
      [56,52],[61,45],[66,37],[72,31],[78,27],[83,24],
      [42,63],[36,68],[29,71],[22,74],[58,64],[65,68],[72,72],[80,75]
    ];
    sparkPoints.forEach(function (point, index) {
      var spark = element('i', 'vava-spark vava-spark-' + ((index % 3) + 1));
      spark.style.left = point[0] + '%';
      spark.style.top = point[1] + '%';
      spark.style.setProperty('--vava-spark-delay', (-0.23 * index) + 's');
      spark.style.setProperty('--vava-spark-x', ((index % 2 ? 1 : -1) * (4 + index % 5)) + 'px');
      spark.style.setProperty('--vava-spark-y', (-(6 + index % 7)) + 'px');
      sparks.appendChild(spark);
    });
    Object.keys(labels).forEach(function (key) {
      var node = element('button', 'vava-layer-node vava-layer-node-' + key);
      node.type = 'button';
      node.setAttribute('aria-expanded', 'false');
      node.setAttribute('aria-controls', 'vava-panel-' + key);
      node.innerHTML = '<span class="vava-node-orbit"></span><span class="vava-node-disc"><i class="dashicons ' + labels[key][1] + '"></i></span><strong></strong><small>فتح القسم</small>';
      node.querySelector('strong').textContent = labels[key][0];
      layer.appendChild(node);

      var stat = (dashboard.stats && dashboard.stats[key]) || {};
      var panel = element('section', 'vava-hover-panel');
      panel.id = 'vava-panel-' + key;
      panel.setAttribute('data-vava-panel', key);
      panel.setAttribute('aria-hidden', 'true');
      panel.innerHTML = '<button type="button" class="vava-panel-close" aria-label="إغلاق">×</button><header><i class="dashicons ' + labels[key][1] + '"></i><div><small>VAVA LIVING</small><h2></h2></div></header><p></p><div class="vava-panel-stats"><div><b></b><span>الإجمالي</span></div><div><b></b><span>بانتظار المراجعة</span></div></div><div class="vava-panel-activity"><i></i><div><small>آخر نشاط</small><strong></strong></div></div><a class="vava-panel-enter"><span>فتح القسم</span><i class="dashicons dashicons-arrow-left-alt2"></i></a>';
      panel.querySelector('h2').textContent = labels[key][0];
      panel.querySelector('p').textContent = labels[key][2];
      panel.querySelector('.vava-panel-enter').href = dashboard[key] || '#';
      panel.querySelector('.vava-panel-stats b').textContent = stat.primary === undefined ? '—' : stat.primary;
      panel.querySelectorAll('.vava-panel-stats b')[1].textContent = stat.secondary === undefined ? '—' : stat.secondary;
      panel.querySelector('.vava-panel-activity strong').textContent = stat.activity || '—';
      if (stat.hideStats) panel.querySelector('.vava-panel-stats').hidden = true;
      layer.appendChild(panel);

      function closePanel() {
        panel.classList.remove('is-open'); node.classList.remove('is-active');
        panel.setAttribute('aria-hidden', 'true'); node.setAttribute('aria-expanded', 'false');
      }
      node.addEventListener('click', function (event) {
        event.preventDefault();
        layer.querySelectorAll('.vava-hover-panel.is-open').forEach(function (item) { if (item !== panel) item.classList.remove('is-open'); });
        layer.querySelectorAll('.vava-layer-node.is-active').forEach(function (item) { if (item !== node) { item.classList.remove('is-active'); item.setAttribute('aria-expanded', 'false'); } });
        var opening = !panel.classList.contains('is-open'); closePanel();
        if (opening) { panel.classList.add('is-open'); node.classList.add('is-active'); panel.setAttribute('aria-hidden', 'false'); node.setAttribute('aria-expanded', 'true'); }
      });
      panel.querySelector('.vava-panel-close').addEventListener('click', closePanel);
    });
    document.addEventListener('click', function (event) {
      if (!layer.contains(event.target)) {
        layer.querySelectorAll('.vava-hover-panel.is-open').forEach(function (panel) { panel.classList.remove('is-open'); panel.setAttribute('aria-hidden', 'true'); });
        layer.querySelectorAll('.vava-layer-node.is-active').forEach(function (node) { node.classList.remove('is-active'); node.setAttribute('aria-expanded', 'false'); });
      }
    });
    content.insertBefore(shell, content.firstChild);
  }
  function start() {
    var config = window.vavaAdminBrand || {};
    buildSidebar(config);
    buildDashboard(config);
    buildProfile(config);
    buildPageScrollbar();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
}());
