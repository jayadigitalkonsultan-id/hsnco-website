(() => {
  'use strict';
  const modal = document.getElementById('practiceModal');
  if (!modal) return;
  modal.addEventListener('show.bs.modal', event => {
    const template = document.getElementById(event.relatedTarget?.dataset.practiceTemplate);
    if (!(template instanceof HTMLTemplateElement)) { event.preventDefault(); return; }
    const content = template.content;
    const text = selector => content.querySelector(selector)?.textContent.trim() || '';
    modal.querySelector('#practiceModalTitle').textContent = text('[data-practice-name]');
    modal.querySelector('[data-practice-modal-number]').textContent = text('[data-practice-number]');
    modal.querySelector('#practiceModalOverview').textContent = text('[data-practice-overview]');
    modal.querySelector('[data-practice-overview-block]').hidden = !text('[data-practice-overview]');
    const items = [...content.querySelectorAll('[data-practice-items] li')];
    modal.querySelector('[data-practice-modal-services]').replaceChildren(...items.map(item => item.cloneNode(true)));
    modal.querySelector('[data-practice-services-block]').hidden = !items.length;
    const image = modal.querySelector('[data-practice-modal-image]');
    image.hidden = false;
    image.onerror = () => { image.hidden = true; };
    image.src = text('[data-practice-image]');
    modal.querySelector('.practice-portfolio-content').scrollTop = 0;
  });
  modal.addEventListener('shown.bs.modal', () => { modal.querySelector('.practice-portfolio-content').scrollTop = 0; });
})();