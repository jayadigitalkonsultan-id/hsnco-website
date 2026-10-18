(() => {
  'use strict';

  const container = document.getElementById('siteTree');
  const fallback = document.getElementById('siteTreeFallback');
  const controls = document.getElementById('siteTreeControls');
  const $ = window.jQuery;
  if (!container || !fallback || !controls || !$?.fn.jstree) return;

  const readItems = list => [...list.children].flatMap(item => {
    const link = item.querySelector(':scope > a[data-site-page]');
    if (!link) return [];
    const nested = item.querySelector(':scope > ul');
    return [{
      text: link.textContent.trim(),
      data: { href: link.href, description: link.dataset.description || '' },
      ...(nested ? { children: readItems(nested) } : {}),
    }];
  });
  const treeData = [{
    id: 'site-root',
    text: 'HSNCO',
    state: { opened: true },
    children: readItems(fallback),
  }];
  const tree = $(container);

  tree.on('ready.jstree', () => {
    fallback.hidden = true;
    container.hidden = false;
    controls.hidden = false;
    container.setAttribute('aria-label', 'Struktur halaman publik HSNCO');
  }).on('select_node.jstree', (_event, event) => {
    const node = event.node;
    const action = document.getElementById('openSitePage');
    const title = document.getElementById('sitePageTitle');
    const description = document.getElementById('sitePageDescription');
    const href = node.data?.href;
    if (!href) {
      title.textContent = 'Jelajahi HSNCO';
      description.textContent = 'Pilih halaman dari daftar untuk membaca informasi tujuan.';
      action.hidden = true;
      return;
    }
    title.textContent = node.text;
    description.textContent = node.data.description || 'Buka halaman publik ini untuk melihat informasinya.';
    action.href = href;
    action.hidden = false;
  });

  tree.jstree({
    core: { force_text: true, multiple: false, themes: { dots: false, responsive: true }, data: treeData },
    plugins: ['search'],
  });

  document.getElementById('siteTreeSearch')?.addEventListener('input', event => {
    tree.jstree(true).search(event.target.value);
  });
  document.getElementById('expandSiteTree')?.addEventListener('click', () => tree.jstree(true).open_all());
  document.getElementById('collapseSiteTree')?.addEventListener('click', () => tree.jstree(true).close_all());
})();
