import {chromium} from 'playwright';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const page=await browser.newPage({reducedMotion:'reduce'});const errors=[];
page.on('pageerror',e=>errors.push(e.message));page.on('response',r=>{if(r.status()>=400)errors.push(r.status()+' '+r.url());});
fs.mkdirSync('docs/qa-practices',{recursive:true});
try{
 for(const width of [1920,1440,1366,768,390,375]){
  await page.setViewportSize({width,height:950});await page.goto('http://127.0.0.1:8000/practice-areas',{waitUntil:'networkidle'});await page.evaluate(()=>document.fonts.ready);
  if(await page.locator('[data-practice-entry]').count()!==8)throw Error('Expected 8 categories');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Page overflow '+width);
  const triggers=page.locator('.practice-row-trigger');
  for(let i=0;i<await triggers.count();i++){
   const trigger=triggers.nth(i);const id=await trigger.getAttribute('data-practice-template');
   const expected=await page.locator('#'+id).evaluate(el=>({name:el.content.querySelector('[data-practice-name]').textContent,count:el.content.querySelectorAll('li').length}));
   await trigger.click();const modal=page.locator('#practiceModal');await modal.waitFor({state:'visible'});
   if(await page.locator('#practiceModalTitle').textContent()!==expected.name)throw Error('Wrong category');
   if(await page.locator('[data-practice-modal-services] li').count()!==expected.count)throw Error('Missing service items');
   if(!await page.locator('[data-practice-modal-image]').evaluate(img=>img.complete&&img.naturalWidth>0))await page.waitForTimeout(150);
   if(width>=768 && !await page.locator('#practiceModalTitle').evaluate(el=>el.getBoundingClientRect().bottom<=document.querySelector('.practice-portfolio').getBoundingClientRect().bottom))throw Error('Identity clipped');
   if(await modal.evaluate(el=>el.scrollWidth>el.clientWidth))throw Error('Modal overflow '+width);
   await page.keyboard.press('Tab');if(!await modal.evaluate(el=>el.contains(document.activeElement)))throw Error('Focus escaped');
   if(i===0&&[1440,390].includes(width))await page.screenshot({path:`docs/qa-practices/modal-${width}.png`});
   await page.keyboard.press('Escape');await modal.waitFor({state:'hidden'});
   if(!await trigger.evaluate(el=>el===document.activeElement))throw Error('Focus not restored');
  }
  if([1440,390].includes(width)){await page.evaluate(()=>{document.activeElement?.blur();window.scrollTo(0,0)});await page.screenshot({path:`docs/qa-practices/directory-${width}.png`,fullPage:true});}
 }
 if(errors.length)throw Error(errors.join('\n'));
 console.log('PASS: 8 categories across 6 viewports; every modal title/service count, keyboard focus, Escape/return, no horizontal overflow, HTTP or JS errors.');
}finally{await browser.close();}