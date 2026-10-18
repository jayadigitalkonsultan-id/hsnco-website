(() => {
  'use strict';
  if(!window.bootstrap)return;
  const sidebar=document.getElementById('adminSidebar');
  const toggle=document.querySelector('[data-bs-toggle="offcanvas"]');
  if(!sidebar)return;
  for(const overlay of [sidebar,...document.querySelectorAll('.modal')])overlay.addEventListener('keydown',event=>{
    if(event.key!=='Tab'||!overlay.classList.contains('show'))return;
    const controls=[...overlay.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),textarea:not([disabled]),select:not([disabled]),[tabindex]:not([tabindex="-1"])')].filter(el=>el.getClientRects().length&&getComputedStyle(el).visibility!=='hidden');
    if(!controls.length)return;
    const first=controls[0],last=controls[controls.length-1];
    if(event.shiftKey&&(document.activeElement===first||document.activeElement===overlay)){event.preventDefault();last.focus();}
    else if(!event.shiftKey&&document.activeElement===last){event.preventDefault();first.focus();}
  });
  sidebar.addEventListener('show.bs.offcanvas',()=>toggle?.setAttribute('aria-expanded','true'));
  sidebar.addEventListener('hidden.bs.offcanvas',()=>toggle?.setAttribute('aria-expanded','false'));
  matchMedia('(min-width:992px)').addEventListener('change',event=>{if(event.matches)bootstrap.Offcanvas.getInstance(sidebar)?.hide();});
})();
