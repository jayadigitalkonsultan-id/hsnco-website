import {chromium} from 'playwright';
import fs from 'node:fs';
const origin=process.env.QA_BASE_URL || 'http://127.0.0.1:8000';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const page=await browser.newPage({reducedMotion:'reduce'});
const jsErrors=[],httpErrors=[];
page.on('pageerror',e=>jsErrors.push(e.message));
page.on('response',r=>{if(r.status()>=400)httpErrors.push(`${r.status()} ${r.url()}`)});
const indonesian=/\b(kami|anda|yang|belum|keahlian|layanan|bidang|sampaikan|hubungi|kebutuhan|pelajari|selengkapnya|gambaran)\b/i;
const paths=['/','/about','/practice-areas','/industries','/team','/billing-fees','/clients','/insights','/contact','/sitemap'];
const widths=[1920,1440,1366,768,390,375];
fs.mkdirSync('storage/app/qa-english',{recursive:true});
try {
 for(const width of widths)for(const path of paths){
  await page.setViewportSize({width,height:950});await page.goto(origin+path,{waitUntil:'networkidle'});await page.evaluate(()=>document.fonts.ready);
  if(await page.locator('html').getAttribute('lang')!=='en')throw Error('Document language '+path);
  const text=await page.locator('body').innerText();if(indonesian.test(text))throw Error('Untranslated UI '+path+': '+text.match(indonesian)[0]);
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Overflow '+path+' '+width);
  if(width>=992&&await page.locator('.site-header .navbar').evaluate(e=>e.scrollWidth>e.clientWidth))throw Error('Navigation overflow');
  if(path==='/'&&await page.locator('#clients-title').innerText()!=='Our Clients')throw Error('Client heading or unwanted arrow');
  if(width===1440&&path==='/')await page.screenshot({path:'storage/app/qa-english/home-desktop.png'});
  if(width===390&&path==='/about')await page.screenshot({path:'storage/app/qa-english/about-mobile.png'});
 }
 await page.goto(origin+'/practice-areas',{waitUntil:'networkidle'});
 for(const trigger of await page.locator('.practice-row-trigger').all()){
  await trigger.click();await page.locator('#practiceModal.show').waitFor();
  if(indonesian.test(await page.locator('#practiceModal').innerText()))throw Error('Practice modal not English');
  await page.locator('.practice-modal-close').click();await page.waitForFunction(()=>!document.body.classList.contains('modal-open'));
 }
 const practiceModalCount=await page.locator('.practice-row-trigger').count();
 await page.goto(origin+'/team',{waitUntil:'networkidle'});
 const profileData=await page.evaluate(()=>window.HSNCO_PROFESSIONALS);
 for(const [slug,profile] of Object.entries(profileData))if(indonesian.test(profile.biography || ''))throw Error('Untranslated biography: '+slug);
 await page.locator('.professional-name-trigger').first().click();await page.locator('#detailModal.show').waitFor();
 if(!await page.locator('#detailModal').innerText().then(t=>/professional portfolio/i.test(t)))throw Error('Portfolio UI not English');
 await page.locator('.portfolio-close').click();await page.waitForFunction(()=>!document.body.classList.contains('modal-open'));
 await page.setViewportSize({width:375,height:950});await page.locator('.navbar-toggler').click();await page.locator('#mainNavigation .nav-link').first().waitFor({state:'visible'});
 if(indonesian.test(await page.locator('#mainNavigation').innerText()))throw Error('Mobile navigation not English');
 const report={pages:paths.length,viewports:widths,practiceModals:practiceModalCount,profileCount:Object.keys(profileData).length,jsErrors,httpErrors:[...new Set(httpErrors)]};
 fs.writeFileSync('storage/app/qa-english/report.json',JSON.stringify(report,null,2));
 if(jsErrors.length)throw Error(jsErrors.join('\n'));
 const unexpected=report.httpErrors.filter(e=>!e.includes('/storage/media/01M5E47N7WFVN83FF49HKDFJJP.jpg'));
 if(unexpected.length)throw Error(unexpected.join('\n'));
 console.log('PASS: 10 public pages x 6 viewports, English UI/biographies, practice and portfolio modals, client title without arrow, mobile navigation, no page/navbar overflow or JS errors.');
 if(report.httpErrors.length)console.log('Existing media issue: '+report.httpErrors.join('; '));
} finally {await browser.close();}