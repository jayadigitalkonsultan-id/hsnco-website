(() => {
  'use strict';
  document.querySelectorAll('[data-client-showcase] .clients-rail').forEach(rail => {
    const track = rail.querySelector('.clients-track');
    const group = rail.querySelector('.clients-group');
    if (!track || !group || group.children.length < 2) return;
    rail.addEventListener('pointerdown', () => rail.focus({ preventScroll: true }), { passive: true });
    const copy = group.cloneNode(true);
    copy.setAttribute('aria-hidden', 'true');
    copy.querySelectorAll('img').forEach(img => { img.alt = ''; });
    track.append(copy);
    const resize = () => {
      track.style.setProperty('--clients-duration', `${Math.max(28, group.getBoundingClientRect().width / 28)}s`);
      rail.classList.toggle('is-enhanced', group.scrollWidth > rail.clientWidth);
    };
    if ('ResizeObserver' in window) new ResizeObserver(resize).observe(rail);
    else window.addEventListener('resize', resize, { passive: true });
    resize();
    document.addEventListener('visibilitychange', () => {
      track.style.animationPlayState = document.hidden ? 'paused' : '';
    });
  });
})();