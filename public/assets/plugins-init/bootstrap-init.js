(() => {
  'use strict';
  const panel = document.getElementById('mainNavigation');
  const trigger = document.querySelector('.navbar-toggler');
  if (!panel || !trigger || !window.bootstrap) return;
  panel.addEventListener('show.bs.offcanvas', () => trigger.setAttribute('aria-expanded', 'true'));
  panel.addEventListener('hidden.bs.offcanvas', () => trigger.setAttribute('aria-expanded', 'false'));
  panel.querySelectorAll('a').forEach(link => link.addEventListener('click', () => bootstrap.Offcanvas.getInstance(panel)?.hide()));
  matchMedia('(min-width: 992px)').addEventListener('change', event => {
    if (event.matches) bootstrap.Offcanvas.getInstance(panel)?.hide();
  });
})();
