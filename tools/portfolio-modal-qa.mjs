import { chromium } from 'playwright';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const page=await browser.newPage({reducedMotion:'reduce'});const errors=[];
page.on('pageerror',e=>errors.push(e.message));page.on('response',r=>{if(r.status()>=400)errors.push(r.status()+' '+r.url());});
fs.mkdirSync('docs/qa-professionals',{recursive:true});
try{
 for(const width of [1920,1440,1366,768,390,375]){
  await page.setViewportSize({width,height:950});await page.goto('http://127.0.0.1:8000/team',{waitUntil:'networkidle'});
  await page.locator('#professionalSearch').fill('Arvin Wicaksono');
  const trigger=page.locator('[data-profile-card]:visible [data-profile]');await trigger.click();
  const modal=page.locator('#detailModal');await modal.waitFor({state:'visible'});
  if(!(await page.locator('#detailTitle').textContent()).includes('Arvin'))throw Error('Wrong identity');
  if(!(await page.locator('#detailDescription').textContent()).includes('Pelindungan Data Pribadi'))throw Error('Missing biography');
  if(await modal.locator('[data-portfolio-qualifications-block]').isVisible())throw Error('Empty credentials shown');
  if(!await modal.locator('[data-portfolio-placeholder]').isVisible())throw Error('Placeholder missing');
  if(!(await modal.locator('[data-portfolio-contact]').getAttribute('href')).endsWith('/contact#enquiry'))throw Error('Contact');
  if(await modal.evaluate(el=>el.scrollWidth>el.clientWidth))throw Error('Modal overflow '+width);
  const focused=await page.evaluate(()=>document.querySelector('#detailModal').contains(document.activeElement));if(!focused)throw Error('Focus outside modal');
  for(let i=0;i<6;i++)await page.keyboard.press('Tab');
  if(!await page.evaluate(()=>document.querySelector('#detailModal').contains(document.activeElement)))throw Error('Focus trap');
  if([1440,390].includes(width))await page.screenshot({path:`docs/qa-professionals/portfolio-${width}.png`});
  await page.keyboard.press('Escape');await modal.waitFor({state:'hidden'});
  if(await page.evaluate(()=>document.body.classList.contains('modal-open')))throw Error('Scroll lock remained');
  if(!await trigger.evaluate(el=>el===document.activeElement))throw Error('Focus not restored');
 }
 // Reopening another profile must replace the first person's identity and content.
 await page.locator('#professionalSearch').fill('Dedi Purnawan');await page.locator('[data-profile-card]:visible [data-profile]').click();
 await page.locator('#detailModal').waitFor({state:'visible'});
 if(!(await page.locator('#detailDescription').textContent()).includes('Universitas Terbuka'))throw Error('Stale profile');
 if((await page.locator('#detailTitle').textContent()).includes('Arvin'))throw Error('Stale identity');
 if(errors.length)throw Error(errors.join('\n'));
 console.log('PASS: 6 viewports, content, empty fields hidden, contact link, focus trap/restoration, Escape, scroll unlock, profile switching; no HTTP/JS errors.');
}finally{await browser.close();}