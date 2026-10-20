import { chromium } from 'playwright';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const page=await browser.newPage({reducedMotion:'reduce'});const errors=[];
page.on('pageerror',e=>errors.push(e.message));page.on('response',r=>{if(r.status()>=400)errors.push(r.status()+' '+r.url());});
fs.mkdirSync('docs/qa-about',{recursive:true});
try {
 for(const width of [1920,1440,1366,768,390,375]){
  await page.setViewportSize({width,height:900});
  await page.goto('http://127.0.0.1:8000/about',{waitUntil:'networkidle'});
  await page.evaluate(()=>document.fonts.ready);
  if(await page.locator('.about-profile').count()!==1)throw Error('About profile missing');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Overflow '+width);
  if(!await page.locator('.about-profile-figure img').evaluate(img=>img.complete&&img.naturalWidth>0))throw Error('Image missing');
  if([1440,390].includes(width))await page.screenshot({path:`docs/qa-about/about-${width}.png`,fullPage:true});
 }
 if(errors.length)throw Error(errors.join('\n'));
 console.log('PASS: 6 viewport sizes, profile image, no horizontal overflow, no HTTP asset errors or JavaScript errors.');
}finally{await browser.close();}