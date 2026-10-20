(() => {
  'use strict';
  const preference = matchMedia('(prefers-reduced-motion: reduce)');
  const main = document.querySelector('main');
  if (!main || !('IntersectionObserver' in window) || !Element.prototype.animate) return;

  const selectors = [
    '.hero-content > *', '.inner-hero .breadcrumb', '.inner-hero h1',
    '.eyebrow', '.banner-intro', '.section-title', '.section-heading',
    'section > .container > .row > [class*="col"] > p',
    '.editorial-copy p', '.services-intro-copy p', '.services-panel',
    '.client-card', '.insight-card', '.practice-tile', '.timeline-item',
    '.editorial-row', '[data-enquiry-form]', '.image-frame'
  ];
  const candidates = [...main.querySelectorAll(selectors.join(','))];
  // Reveal cards as a unit, and text individually outside them.
  const targets = candidates.filter(element => !candidates.some(parent => parent !== element && parent.contains(element)));
  const active = new Map();
  const entered = new WeakSet();
  let observer;
  let heroAnimation;

  const finish = () => {
    active.forEach(animation => animation.finish());
    heroAnimation?.finish();
  };
  const reveal = (element, delay) => {
    if (preference.matches || element.contains(document.activeElement)) return;
    active.get(element)?.cancel();
    const animation = element.animate([
      { opacity: 0, transform: 'translate3d(0, 38px, 0)', offset: 0 },
      { opacity: .8, transform: 'translate3d(0, 9px, 0)', offset: .6 },
      { opacity: 1, transform: 'translate3d(0, 0, 0)', offset: 1 }
    ], { duration: 1100, delay, easing: 'cubic-bezier(.2,.7,.2,1)', fill: 'backwards' });
    active.set(element, animation);
    const clean = () => { if (active.get(element) === animation) active.delete(element); };
    animation.onfinish = animation.oncancel = clean;
  };
  const connect = () => {
    observer?.disconnect();
    if (preference.matches) return;
    observer = new IntersectionObserver(entries => {
      const visible = entries.filter(entry => entry.isIntersecting)
        .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top || a.boundingClientRect.left - b.boundingClientRect.left);
      entries.filter(entry => !entry.isIntersecting).forEach(entry => {
        entered.delete(entry.target);
        active.get(entry.target)?.cancel();
      });
      let order = 0;
      visible.forEach(entry => {
        if (entered.has(entry.target)) return;
        entered.add(entry.target);
        const element = entry.target;
        const bannerDelay = element.closest('.inner-hero')
          ? element.matches('h1') ? 180 : element.matches('.banner-intro') ? 360 : element.matches('.eyebrow') ? 90 : 0
          : null;
        reveal(element, bannerDelay ?? Math.min(order++, 4) * 90);
      });
    }, { threshold: 0, rootMargin: '0px 0px -48px 0px' });
    targets.forEach(element => observer.observe(element));
  };
  const initialReady = Promise.race([document.fonts?.ready ?? Promise.resolve(), new Promise(resolve => setTimeout(resolve, 1500))]);
  initialReady.then(() => requestAnimationFrame(connect));
  const hero = main.querySelector('.hero-image');
  if (hero && !preference.matches) {
    heroAnimation = hero.animate([
      { transform: 'scale(1.075)' }, { transform: 'scale(1)' }
    ], { duration: 9000, easing: 'cubic-bezier(.2,.6,.3,1)' });
  }
  main.addEventListener('focusin', finish);
  preference.addEventListener('change', () => {
    finish();
    targets.forEach(element => entered.delete(element));
    connect();
  });
  window.addEventListener('beforeprint', () => { finish(); observer?.disconnect(); });
  window.addEventListener('afterprint', connect);
})();