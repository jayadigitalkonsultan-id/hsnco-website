(() => {
 'use strict';
 document.querySelectorAll('[data-client-showcase]').forEach(section => {
  const rail=section.querySelector('.clients-rail'), cards=[...rail.querySelectorAll('.client-card')], reduced=matchMedia('(prefers-reduced-motion: reduce)');
  let timer=null;
  const pause=()=>{clearInterval(timer);timer=null;};
  const move=direction=>{
   const step=rail.clientWidth;
   if(direction>0 && rail.scrollLeft+rail.clientWidth>=rail.scrollWidth-5)rail.scrollTo({left:0,behavior:reduced.matches?'auto':'smooth'});
   else rail.scrollBy({left:direction*step,behavior:reduced.matches?'auto':'smooth'});
  };
  const start=()=>{pause();if(document.hidden || reduced.matches || section.matches(':hover') || section.contains(document.activeElement))return;timer=setInterval(()=>move(1),6000);};
  section.querySelector('.clients-filters').hidden=false;section.querySelector('.clients-controls').hidden=false;
  section.querySelector('[data-client-prev]').addEventListener('click',()=>{pause();move(-1);});
  section.querySelector('[data-client-next]').addEventListener('click',()=>{pause();move(1);});
  section.querySelectorAll('[data-client-filter]').forEach(button=>button.addEventListener('click',()=>{
   pause();cards.forEach(card=>card.hidden=button.dataset.clientFilter!=='all'&&card.dataset.clientCategory!==button.dataset.clientFilter);
   section.querySelectorAll('[data-client-filter]').forEach(item=>item.setAttribute('aria-pressed',String(item===button)));
   rail.scrollTo({left:0,behavior:'instant'});
   section.querySelector('.clients-status').textContent=`${cards.filter(card=>!card.hidden).length} klien · Geser untuk menjelajahi`;
  }));
  section.addEventListener('pointerenter',pause);section.addEventListener('pointerleave',start);section.addEventListener('focusin',pause);section.addEventListener('focusout',()=>setTimeout(start,0));rail.addEventListener('pointerdown',pause,{passive:true});
  document.addEventListener('visibilitychange',start);reduced.addEventListener('change',start);
  if(!reduced.matches)start();
 });
 const modal=document.getElementById('clientDetailModal');
 modal?.addEventListener('show.bs.modal',event=>{
  const trigger=event.relatedTarget;if(!trigger)return;
  modal.querySelector('#clientDetailTitle').textContent=trigger.dataset.clientName;
  modal.querySelector('[data-client-modal-sector]').textContent=trigger.dataset.clientSector;
  modal.querySelector('[data-client-modal-body]').textContent=trigger.dataset.clientDescription;
 });
})();