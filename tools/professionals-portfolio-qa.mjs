import { chromium } from 'playwright';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const page=await browser.newPage({reducedMotion:'reduce'});const errors=[];
page.on('pageerror',e=>errors.push(e.message));page.on('response',r=>{if(r.status()>=400)errors.push(r.status()+' '+r.url());});
fs.mkdirSync('docs/qa-professionals',{recursive:true});
try{
 for(const width of [1920,1440,1366,768,390,375]){
  await page.setViewportSize({width,height:950});await page.goto('http://127.0.0.1:8000/team',{waitUntil:'networkidle'});await page.evaluate(()=>document.fonts.ready);
  const contrast = await page.locator('.team-directory').evaluate(section => {
    const rgb = color => (color.match(/[\d.]+/g) || []).slice(0,3).map(Number);
    const luminance = color => rgb(color).map(n => { const c=n/255; return c<=0.04045?c/12.92:((c+0.055)/1.055)**2.4; }).reduce((sum,n,i)=>sum+n*[0.2126,0.7152,0.0722][i],0);
    const background = luminance(getComputedStyle(section).backgroundColor);
    return [...section.querySelectorAll('.section-title,.team-group-heading h3,.professional-position,.professional-name-trigger,.team-tools .form-label')].every(el => {
      const text=luminance(getComputedStyle(el).color);return (Math.max(text,background)+0.05)/(Math.min(text,background)+0.05)>=4.5;
    });
  });
  if(!contrast)throw Error('Directory text contrast below 4.5:1 at '+width);
  if(await page.locator('[data-profile-card]').count()!==19)throw Error('Expected 19 profiles');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Overflow '+width);
  for(const [category,count] of Object.entries({managing_partner:1,partner:2,associate:9,trainee_associate:2,paralegal:4,professional:1})){
   await page.locator(`[data-filter="${category}"]`).click();
   if(await page.locator('[data-profile-card]:visible').count()!==count)throw Error('Filter '+category+' '+width);
   if(await page.locator('[data-profile-group]:visible').count()!==1)throw Error('Empty group visible');
  }
  await page.locator('[data-filter="all"]').click();await page.locator('#professionalSearch').fill('Arvin Wicaksono');
  if(await page.locator('[data-profile-card]:visible').count()!==1)throw Error('Search');
  await page.locator('[data-profile-card]:visible .text-link[data-profile]').click();await page.locator('#detailModal').waitFor({state:'visible'});
  if(!(await page.locator('#detailDescription').textContent()).includes('Pelindungan Data Pribadi'))throw Error('Biography not rendered');
  await page.locator('#detailModal [data-bs-dismiss="modal"]').first().click();await page.locator('#detailModal').waitFor({state:'hidden'});
  await page.locator('#professionalSearch').fill('');
  if([1440,390].includes(width))await page.screenshot({path:`docs/qa-professionals/team-${width}.png`,fullPage:true});
 }
 if(errors.length)throw Error(errors.join('\n'));
 console.log('PASS: 19 profiles, 6 categories and 6 viewports; search, modal biography, no overflow, no HTTP/runtime errors.');
}finally{await browser.close();}