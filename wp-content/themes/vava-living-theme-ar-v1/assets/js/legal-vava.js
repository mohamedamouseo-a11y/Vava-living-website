(function () {
  'use strict';

  const root = document.querySelector('[data-vava-legal-page]');
  const toc = document.querySelector('[data-vava-legal-toc]');
  if (!root || !toc) return;

  const links = Array.from(toc.querySelectorAll('a[href^="#vava-legal-section-"]'));
  const sections = links
    .map((link) => {
      const id = link.getAttribute('href');
      return id ? document.querySelector(id) : null;
    })
    .filter(Boolean);

  const setActive = (id) => {
    links.forEach((link) => {
      const active = link.getAttribute('href') === `#${id}`;
      link.classList.toggle('is-active', active);
      if (active) {
        link.setAttribute('aria-current', 'true');
      } else {
        link.removeAttribute('aria-current');
      }
    });
  };

  links.forEach((link) => {
    link.addEventListener('click', (event) => {
      const target = document.querySelector(link.getAttribute('href'));
      if (!target) return;
      event.preventDefault();
      const headerOffset = window.innerWidth <= 820 ? 96 : 126;
      const top = target.getBoundingClientRect().top + window.scrollY - headerOffset;
      window.scrollTo({ top, behavior: 'smooth' });
      setActive(target.id);
      history.replaceState(null, '', `#${target.id}`);
    });
  });

  if ('IntersectionObserver' in window && sections.length) {
    const observer = new IntersectionObserver(
      (entries) => {
        const visible = entries
          .filter((entry) => entry.isIntersecting)
          .sort((a, b) => Math.abs(a.boundingClientRect.top) - Math.abs(b.boundingClientRect.top));
        if (visible[0]) setActive(visible[0].target.id);
      },
      { rootMargin: '-24% 0px -62% 0px', threshold: [0, 0.08, 0.25] }
    );
    sections.forEach((section) => observer.observe(section));
  }

  if (window.location.hash && /^#vava-legal-section-\d+$/.test(window.location.hash)) {
    const initial = document.querySelector(window.location.hash);
    if (initial) {
      window.setTimeout(() => {
        const headerOffset = window.innerWidth <= 820 ? 96 : 126;
        window.scrollTo({ top: initial.getBoundingClientRect().top + window.scrollY - headerOffset });
        setActive(initial.id);
      }, 80);
    }
  }
})();
