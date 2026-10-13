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
      label = 'Gambaran bidang praktik'; subtitle = 'Lingkup layanan';
      image = practice.image;
    } else if (trigger.hasAttribute('data-profile')) {
      title = content.roles[Number(trigger.dataset.profile)];
      description = 'Profil contoh. Foto ilustrasi dan pratinjau ini tidak menunjukkan identitas advokat HSNCO. Firma perlu menyediakan dan menyetujui profil yang sebenarnya.';
      list = ['Nama dan peran yang disetujui', 'Biografi dan bidang praktik yang terverifikasi', 'Potret dan kualifikasi yang disetujui', 'Pengalaman penanganan perkara yang terverifikasi'];
      label = 'Pratinjau profil'; subtitle = 'Konten yang perlu dilengkapi';
      image = `team${Number(trigger.dataset.profile) + 1}`;
    } else {
      title = content.newsTitles[Number(trigger.dataset.insight)];
      description = 'Judul publikasi berasal dari desain sumber. Artikel lengkap belum tersedia; tidak ada klaim analisis hukum atau tanggal terbit.';
      list = ['Artikel dan ringkasan yang disetujui', 'Penulis dan tanggal terbit yang terverifikasi', 'Referensi dan isi hukum yang telah ditinjau'];
      label = 'Pratinjau publikasi'; subtitle = 'Persyaratan publikasi';
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
      document.querySelector('[data-results]').textContent = `${visible} profil contoh`;
    };
    search.addEventListener('input', apply);
    document.querySelectorAll('[data-filter]').forEach(button => button.addEventListener('click', () => {
      filter = button.dataset.filter;
      document.querySelectorAll('[data-filter]').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
      apply();
    }));
  }
  const values = [['Pendekatan yang jelas.', 'Kami memahami konteks bisnis di balik persoalan hukum. Peran kami adalah menjelaskan kompleksitas dan memudahkan pengambilan keputusan berikutnya.'], ['Integritas tanpa kompromi.', 'Kami menjaga kepentingan klien dengan independensi, kehati-hatian, dan integritas.'], ['Keandalan dalam setiap perkara.', 'Pendampingan yang tanggap, perhatian profesional berpengalaman, dan nasihat yang dapat diterapkan dari awal hingga hasil akhir.']];
  document.querySelectorAll('[data-value]').forEach(button => button.addEventListener('click', () => {
    const [title, description] = values[Number(button.dataset.value)];
    document.getElementById('value-title').textContent = title;
    document.getElementById('value-description').textContent = description;
    document.querySelectorAll('[data-value]').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
  }));
})();
