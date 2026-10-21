(() => {
  'use strict';
  document.querySelectorAll('[data-client-showcase]').forEach(section => {
    const rail = section.querySelector('.clients-rail');
    const track = section.querySelector('.clients-track');
    const group = section.querySelector('.clients-group');
    if (!rail || !track || !group || group.children.length < 2) return;
    rail.addEventListener('pointerdown', () => rail.focus({ preventScroll: true }), { passive: true });
    const copy = group.cloneNode(true);
    copy.setAttribute('aria-hidden', 'true');
    copy.querySelectorAll('img').forEach(img => { img.alt = ''; });
    track.append(copy);
    const resize = () => {
      track.style.setProperty('--clients-duration', `${Math.max(25, group.getBoundingClientRect().width / 30)}s`);
      rail.classList.toggle('is-enhanced', group.scrollWidth > rail.clientWidth);
    };
    new ResizeObserver(resize).observe(rail);
    resize();
    document.addEventListener('visibilitychange', () => {
      track.style.animationPlayState = document.hidden ? 'paused' : '';
    });
  });
})();