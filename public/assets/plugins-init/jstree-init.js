(() => {
  'use strict';
  const container=document.getElementById('siteTree'),$=window.jQuery;
  if(!container||!$?.fn.jstree)return;
  const links=[...document.querySelectorAll('[data-site-page]')];
  const tree=$(container);
  tree.on('ready.jstree',()=>{document.getElementById('siteTreeFallback').hidden=true;container.setAttribute('aria-label','Halaman situs HSNCO');})
    .on('select_node.jstree',(_event,event)=>{const link=links[Number(event.node.data?.index)];const action=document.getElementById('openSitePage');const title=document.getElementById('sitePageTitle');if(!link){title.textContent='Jelajahi HSNCO';action.hidden=true;return;}title.textContent=link.textContent;action.href=link.href;action.hidden=false;});
  tree.jstree({core:{force_text:true,multiple:false,themes:{dots:false,responsive:true},data:[{id:'site-root',text:'HSNCO',state:{opened:true},children:links.map((link,index)=>({id:'site-page-'+index,text:link.textContent,data:{index}}))}]},plugins:['search']});
  document.getElementById('siteTreeSearch').addEventListener('input',event=>tree.jstree(true).search(event.target.value));
  document.getElementById('expandSiteTree').addEventListener('click',()=>tree.jstree(true).open_all());
  document.getElementById('collapseSiteTree').addEventListener('click',()=>tree.jstree(true).close_all());
})();
