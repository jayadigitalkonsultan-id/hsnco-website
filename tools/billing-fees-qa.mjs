import {chromium} from 'playwright';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const page=await browser.newPage({reducedMotion:'reduce'});const errors=[];
page.on('pageerror',e=>errors.push(e.message));page.on('response',r=>{if(r.status()>=400)errors.push(r.status()+' '+r.url());});
fs.mkdirSync('docs/qa-billing',{recursive:true});
try{
 for(const width of [1920,1440,1366,768,390,375]){
  await page.setViewportSize({width,height:950});await page.goto('http://127.0.0.1:8000/billing-fees',{waitUntil:'networkidle'});await page.evaluate(()=>document.fonts.ready);
  if(await page.locator('h1').count()!==1||await page.locator('.fee-card').count()!==6||await page.locator('.fee-card svg').count()!==6)throw Error('Page structure');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Overflow '+width);
  if(!await page.locator('.fee-editorial-image img').evaluate(img=>img.complete&&img.naturalWidth>0))throw Error('Missing image');
  await page.locator('a[href="#skema-biaya"]').click();if(!page.url().endsWith('#skema-biaya'))throw Error('Anchor');
  await page.evaluate(()=>{document.activeElement?.blur();window.scrollTo(0,0);});
  if([1440,390].includes(width))await page.screenshot({path:`docs/qa-billing/billing-${width}.png`,fullPage:true});
 }
 for(const path of ['/team','/practice-areas']){await page.goto('http://127.0.0.1:8000'+path);await page.locator('.fee-callout a').click();await page.waitForURL('**/billing-fees');}
 if(errors.length)throw Error(errors.join('\n'));
 console.log('PASS: 6 schemes and SVG illustrations, 6 viewports, hero/editorial images, anchors and entry links, no overflow/HTTP/JS errors.');
}finally{await browser.close();}