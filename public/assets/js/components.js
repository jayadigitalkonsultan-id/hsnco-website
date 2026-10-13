(() => {
  'use strict';
  const content = window.HSNCO_CONTENT;
  const modal = document.getElementById('detailModal');
  if (modal && content) modal.addEventListener('show.bs.modal', event => {
    const trigger = event.relatedTarget;
    if (!trigger) return;
    let title, description, list, label, subtitle, image;
    if (trigger.hasAttribute('data-practice')) {
      const practice = content.practices.find(item => item.id === trigger.dataset.practice);
      if (!practice) return;
      ({ title, description } = practice); list = practice.services;
      label = 'Practice overview'; subtitle = 'Scope of services';
      image = practice.image;
    } else if (trigger.hasAttribute('data-profile')) {
      title = content.roles[Number(trigger.dataset.profile)];
      description = 'Profile placeholder. This stock portrait and preview do not identify an HSNCO lawyer. The firm must supply and approve the actual profile.';
      list = ['Approved name and role', 'Verified biography and practice areas', 'Approved portrait and qualifications', 'Verified representative experience'];
      label = 'Profile preview'; subtitle = 'Content to supply';
      image = `team${Number(trigger.dataset.profile) + 1}`;
    } else {
      title = content.newsTitles[Number(trigger.dataset.insight)];
      description = 'Editorial title retained from the source design. A full article has not been supplied; no legal analysis or publication date is asserted.';
      list = ['Approved article and summary', 'Verified author and publication date', 'Reviewed references and legal content'];
      label = 'Editorial preview'; subtitle = 'Publication requirements';
      image = ['investment', 'transactions', 'data'][Number(trigger.dataset.insight)];
    }
    modal.querySelector('#detailTitle').textContent = title;
    modal.querySelector('#detailDescription').textContent = description;
    modal.querySelector('[data-detail-label]').textContent = label;
    modal.querySelector('[data-detail-subtitle]').textContent = subtitle;
    modal.querySelector('[data-detail-image]').src = new URL('images/' + encodeURIComponent(image) + '.jpg', document.body.dataset.assetBase).href;
    modal.querySelector('[data-detail-list]').replaceChildren(...list.map(text => { const li = document.createElement('li'); li.textContent = text; return li; }));
  });
  const search = document.getElementById('professionalSearch');
  if (search) {
    let filter = 'all';
    const cards = [...document.querySelectorAll('[data-profile-card]')];
    const apply = () => {
      let visible = 0;
      cards.forEach(card => {
        const match = (filter === 'all' || card.dataset.level === filter) && card.dataset.search.toLowerCase().includes(search.value.trim().toLowerCase());
        card.hidden = !match; visible += Number(match);
      });
      document.querySelector('.empty-state').hidden = visible !== 0;
      document.querySelector('[data-results]').textContent = `${visible} profile preview${visible === 1 ? '' : 's'}`;
    };
    search.addEventListener('input', apply);
    document.querySelectorAll('[data-filter]').forEach(button => button.addEventListener('click', () => {
      filter = button.dataset.filter;
      document.querySelectorAll('[data-filter]').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
      apply();
    }));
  }
  const values = [['Straightforward approach.', 'We understand the business behind the legal question. Our role is to make complexity clear and the next decision easier.'], ['Integrity, without compromise.', 'We protect our clients’ interests with independence, discretion, and integrity.'], ['Reliability in every matter.', 'Responsive counsel, senior attention, and advice you can act on from beginning to outcome.']];
  document.querySelectorAll('[data-value]').forEach(button => button.addEventListener('click', () => {
    const [title, description] = values[Number(button.dataset.value)];
    document.getElementById('value-title').textContent = title;
    document.getElementById('value-description').textContent = description;
    document.querySelectorAll('[data-value]').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
  }));
})();
