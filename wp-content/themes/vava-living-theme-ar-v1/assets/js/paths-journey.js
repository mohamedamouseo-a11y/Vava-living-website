(() => {
  'use strict';

  const root = document.querySelector('[data-paths-journey]');
  if (!root) return;

  const stages = Array.from(root.querySelectorAll('[data-paths-stage]'));
  const categoryCards = Array.from(root.querySelectorAll('[data-session-category-card]'));
  const sessionCards = Array.from(root.querySelectorAll('[data-paths-session-card]'));
  const comparison = root.querySelector('[data-comprehensive-comparison]');
  const selectedTitle = root.querySelector('[data-selected-category-title]');
  const selectedDuration = root.querySelector('[data-selected-category-duration]');
  const selectedIntro = root.querySelector('[data-selected-category-intro]');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const topOffset = () => Math.max(90, document.querySelector('.fixed-header')?.offsetHeight || 90);
  const allowedCategories = Array.from(new Set(categoryCards.map((card) => card.dataset.sessionCategoryCard).filter(Boolean)));
  let currentStage = 1;
  let currentCategory = 'comprehensive';
  let transitioning = false;

  const parseHash = () => {
    const match = window.location.hash.match(/^#vava-path-stage-(\d)(?:-([a-z0-9_-]+))?$/i);
    if (!match) return { stage: 1, category: currentCategory };
    let stage = Number(match[1]);
    let category = allowedCategories.includes(match[2]) ? match[2] : currentCategory;
    // Preserve old stage-4/stage-5 links by opening comprehensive sessions.
    if (stage >= 4) {
      stage = 3;
      category = 'comprehensive';
    }
    if (stage < 1 || stage > 3) stage = 1;
    return { stage, category };
  };

  const categoryDefinition = (category) => {
    const card = categoryCards.find((item) => item.dataset.sessionCategoryCard === category);
    return {
      title: card?.dataset.categoryTitle || '',
      duration: card?.dataset.categoryDuration || '',
      intro: card?.dataset.categoryIntro || ''
    };
  };

  const applyCategory = (category) => {
    currentCategory = allowedCategories.includes(category) ? category : (allowedCategories[0] || category);
    const definition = categoryDefinition(currentCategory);
    if (selectedTitle) selectedTitle.textContent = definition.title;
    if (selectedDuration) selectedDuration.textContent = definition.duration;
    if (selectedIntro) selectedIntro.textContent = definition.intro;

    categoryCards.forEach((card) => card.classList.remove('is-selected'));
    const visibleCards = [];
    sessionCards.forEach((card) => {
      card.hidden = card.dataset.sessionCategory !== currentCategory;
      card.classList.remove('is-last-single', 'is-last-pair-first', 'is-last-pair-second', 'is-tablet-last-single');
      if (!card.hidden) visibleCards.push(card);
    });
    const remainder = visibleCards.length % 3;
    if (remainder === 1) visibleCards[visibleCards.length - 1]?.classList.add('is-last-single');
    if (remainder === 2) {
      visibleCards[visibleCards.length - 2]?.classList.add('is-last-pair-first');
      visibleCards[visibleCards.length - 1]?.classList.add('is-last-pair-second');
    }
    if (visibleCards.length % 2 === 1) visibleCards[visibleCards.length - 1]?.classList.add('is-tablet-last-single');
    if (comparison) comparison.hidden = currentCategory !== 'comprehensive';
    root.dataset.selectedCategory = currentCategory;
  };

  const hashFor = (stage) => stage === 3
    ? `#vava-path-stage-3-${currentCategory}`
    : `#vava-path-stage-${stage}`;

  const showStage = (stage, options = {}) => {
    if (options.category) applyCategory(options.category);
    const target = stages.find((item) => Number(item.dataset.pathsStage) === Number(stage));
    const current = stages.find((item) => !item.hidden);
    if (!target || transitioning) return;

    if (current === target) {
      currentStage = Number(stage);
      if (options.history !== false) {
        window.history[options.replace ? 'replaceState' : 'pushState'](
          { vavaPathStage: currentStage, vavaPathCategory: currentCategory },
          '',
          hashFor(currentStage)
        );
      }
      return;
    }

    transitioning = true;
    const duration = reduceMotion || options.instant ? 0 : 360;
    const activate = () => {
      stages.forEach((item) => {
        const active = item === target;
        item.hidden = !active;
        item.setAttribute('aria-hidden', active ? 'false' : 'true');
        item.classList.toggle('is-active', active);
        item.classList.remove('is-leaving');
      });
      target.classList.add('is-entering');
      requestAnimationFrame(() => target.classList.remove('is-entering'));
      currentStage = Number(stage);
      root.dataset.currentStage = String(currentStage);
      if (options.history !== false) {
        window.history[options.replace ? 'replaceState' : 'pushState'](
          { vavaPathStage: currentStage, vavaPathCategory: currentCategory },
          '',
          hashFor(currentStage)
        );
      }
      if (options.scroll !== false) {
        requestAnimationFrame(() => {
          const y = root.getBoundingClientRect().top + window.scrollY - topOffset() - 12;
          window.scrollTo({ top: y, behavior: reduceMotion || options.instant ? 'auto' : 'smooth' });
        });
      }
      window.setTimeout(() => {
        target.querySelector('button, a, summary')?.focus({ preventScroll: true });
        transitioning = false;
      }, duration);
    };

    if (current && duration) {
      current.classList.add('is-leaving');
      window.setTimeout(activate, duration * 0.55);
    } else {
      activate();
    }
  };

  root.addEventListener('click', (event) => {
    const categoryTrigger = event.target.closest('[data-paths-category-select]');
    if (categoryTrigger && root.contains(categoryTrigger)) {
      event.preventDefault();
      showStage(3, { category: categoryTrigger.dataset.pathsCategorySelect });
      return;
    }

    const stageTrigger = event.target.closest('[data-paths-stage-target]');
    if (!stageTrigger || !root.contains(stageTrigger)) return;
    event.preventDefault();
    showStage(Number(stageTrigger.dataset.pathsStageTarget));
  });

  window.addEventListener('popstate', () => {
    const state = parseHash();
    applyCategory(state.category);
    showStage(state.stage, { history: false });
  });

  const initial = parseHash();
  applyCategory(initial.category);
  stages.forEach((item) => {
    const active = Number(item.dataset.pathsStage) === initial.stage;
    item.hidden = !active;
    item.classList.toggle('is-active', active);
    item.setAttribute('aria-hidden', active ? 'false' : 'true');
  });
  currentStage = initial.stage;
  root.dataset.currentStage = String(initial.stage);
})();
