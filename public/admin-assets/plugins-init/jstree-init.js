(() => {
  'use strict';
  const element = document.getElementById('adminTreeData');
  const container = document.getElementById('contentTree');
  const $ = window.jQuery;
  if (!element || !container || !$?.fn.jstree) return;
  const nodes = JSON.parse(element.textContent);
  const tree = $(container);
  tree.on('ready.jstree', () => {
    document.getElementById('contentTreeFallback').hidden = true;
    ['treeSearch', 'expandTree', 'collapseTree'].forEach(id => { document.getElementById(id).disabled = false; });
  });
  tree.on('select_node.jstree', (_event, selection) => {
    const data = selection.node.data;
    const link = document.getElementById('selectedPageEdit');
    document.getElementById('selectedPageTitle').textContent = data?.title || 'Struktur halaman website';
    document.getElementById('selectedPageSummary').textContent = data?.summary || 'Pilih halaman untuk melihat bagiannya.';
    link.hidden = !data?.url;
    if (data?.url) link.href = data.url;
  });
  tree.jstree({core: {data: [{id: 'website-root', text: 'Website HSNCO', state: {opened: true}, children: nodes}], force_text: true, multiple: false, themes: {dots: false, responsive: true}}, plugins: ['search']});
  document.getElementById('treeSearch').addEventListener('input', event => tree.jstree(true).search(event.target.value));
  document.getElementById('expandTree').addEventListener('click', () => tree.jstree(true).open_all());
  document.getElementById('collapseTree').addEventListener('click', () => tree.jstree(true).close_all());
})();
