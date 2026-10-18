(() => {
  'use strict';
  const modal = document.getElementById('detailModal');
  if (modal) modal.addEventListener('show.bs.modal', event => {
    const trigger = event.relatedTarget;
    if (!trigger) return;
    let title, description, list, label, subtitle, image;
    if (trigger.hasAttribute('data-practice')) {
      title = trigger.dataset.practiceTitle || trigger.dataset.practice;
      description = trigger.dataset.practiceSummary || '';
      list = (trigger.dataset.practiceBody || '').split(/\r?\n/).map(item => item.trim()).filter(item => item && item !== description);
      label = 'Gambaran bidang praktik'; subtitle = 'Lingkup layanan';
      image = trigger.dataset.practiceImage || new URL('images/office.jpg', document.body.dataset.assetBase).href;
      modal.querySelector('.content-notice').hidden = true;
    } else if (trigger.hasAttribute('data-profile')) {
      const profile = window.HSNCO_PROFESSIONALS?.[trigger.dataset.profile];
      if (!profile) return;
      title = profile.name;
      description = profile.biography || profile.summary || '';
      list = [
        ...(profile.practiceAreas || []).map(item => `Bidang praktik: ${item}`),
        ...(profile.qualifications || []).map(item => `Kualifikasi: ${item}`),
        ...(profile.languages || []).map(item => `Bahasa: ${item}`),
      ];
      label = profile.position; subtitle = 'Bidang praktik & kualifikasi';
      image = profile.photo;
      modal.querySelector('.content-notice').hidden = true;
    } else if (trigger.hasAttribute('data-insight')) {
      const insight = window.HSNCO_INSIGHTS?.[trigger.dataset.insight];
      if (!insight) return;
      title = insight.title;
      description = insight.body || insight.excerpt || '';
      list = [
        ...(insight.author ? [`Penulis: ${insight.author}`] : []),
        ...(insight.publishedDate ? [`Terbit: ${insight.publishedDate}`] : []),
      ];
      label = 'Publikasi HSNCO'; subtitle = 'Informasi publikasi';
      image = insight.cover;
      modal.querySelector('.content-notice').hidden = true;
    } else {
      return;
    }
    modal.querySelector('#detailTitle').textContent = title;
    modal.querySelector('#detailDescription').textContent = description;
    modal.querySelector('[data-detail-label]').textContent = label;
    modal.querySelector('[data-detail-subtitle]').textContent = subtitle;
    const detailImage = modal.querySelector('[data-detail-image]');
    detailImage.parentElement.hidden = !image;
    if (image) detailImage.src = trigger.hasAttribute('data-practice') || trigger.hasAttribute('data-profile') ? image : new URL('images/' + encodeURIComponent(image) + '.jpg', document.body.dataset.assetBase).href;
    detailImage.alt = trigger.dataset.practiceImageAlt
      || (trigger.hasAttribute('data-profile') ? window.HSNCO_PROFESSIONALS?.[trigger.dataset.profile]?.photoAlt : null)
      || (trigger.hasAttribute('data-insight') ? window.HSNCO_INSIGHTS?.[trigger.dataset.insight]?.coverAlt : null)
      || 'Foto ilustrasi';
    modal.querySelector('#detailDescription').style.whiteSpace = 'pre-line';
    modal.querySelector('[data-detail-list]').replaceChildren(...list.map(text => { const li = document.createElement('li'); li.textContent = text; return li; }));
  });
  const search = document.getElementById('professionalSearch');
  if (search) {
    let filter = 'all';
    const cards = [...document.querySelectorAll('[data-profile-card]')];
    const apply = () => {
      let visible = 0;
      cards.forEach(card => {
        const match = (filter === 'all' || card.dataset.level === filter) && (card.dataset.search || '').toLowerCase().includes(search.value.trim().toLowerCase());
        card.hidden = !match; visible += Number(match);
      });
      const emptyState = document.querySelector('.empty-state');
      if (emptyState) emptyState.hidden = visible !== 0;
      const results = document.querySelector('[data-results]');
      if (results) results.textContent = `${visible} profesional`;
    };
    search.addEventListener('input', apply);
    document.querySelectorAll('[data-filter]').forEach(button => button.addEventListener('click', () => {
      filter = button.dataset.filter;
      document.querySelectorAll('[data-filter]').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
      apply();
    }));
  }
  document.querySelectorAll('[data-value]').forEach(button => button.addEventListener('click', () => {
    const title = button.dataset.valueTitle;
    const description = button.dataset.valueDescription;
    const titleTarget = document.getElementById('value-title');
    const descriptionTarget = document.getElementById('value-description');
    if (titleTarget && title) titleTarget.textContent = title;
    if (descriptionTarget && description !== undefined) descriptionTarget.textContent = description;
    document.querySelectorAll('[data-value]').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
  }));
})();
