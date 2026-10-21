import {chromium} from 'playwright';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const page=await browser.newPage({reducedMotion:'reduce'});const errors=[];
page.on('pageerror',e=>errors.push(e.message));page.on('response',r=>{if(r.status()>=400)errors.push(r.status()+' '+r.url());});
fs.mkdirSync('docs/qa-industries',{recursive:true});
try{
 for(const width of [1920,1440,1366,1024,768,390,375]){
  await page.setViewportSize({width,height:950});
  for(const path of ['/','/industries']){
   await page.goto('http://127.0.0.1:8000'+path,{waitUntil:'networkidle'});await page.evaluate(()=>document.fonts.ready);
   await page.locator('.industry-card>img').evaluateAll(es=>es.forEach(e=>e.loading='eager'));
   await page.waitForFunction(()=>[...document.querySelectorAll('.industry-card>img')].every(e=>e.complete&&e.naturalWidth>0));
   if(await page.locator('.industry-card').count()!==11)throw Error('Expected 11 industry cards '+path);
   if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Overflow '+width+' '+path);
   if(await page.locator('h1').count()!==1)throw Error('Heading hierarchy');
   if(width===1440&&path==='/industries')await page.screenshot({path:'docs/qa-industries/directory-1440.png',fullPage:true});
   if(width===390&&path==='/industries')await page.screenshot({path:'docs/qa-industries/directory-390.png',fullPage:true});
  }
  await page.locator('.industry-card').first().click();await page.waitForURL('**/industries/agribisnis-dan-komoditas');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Detail overflow '+width);
  const canonical=await page.locator('link[rel=canonical]').getAttribute('href');if(canonical.includes('{')||!canonical.endsWith('/industries/agribisnis-dan-komoditas'))throw Error('Dynamic canonical');
  if(!await page.locator('.industry-detail-hero>img').evaluate(img=>img.complete&&img.naturalWidth>0))throw Error('Hero image');
  if([1440,390].includes(width))await page.screenshot({path:`docs/qa-industries/detail-${width}.png`,fullPage:true});
 }
 if(errors.length)throw Error(errors.join('\n'));
 console.log('PASS: Home/index 11 cards; detail route and canonical, hero images, 7 viewport widths, no overflow, asset HTTP or JS errors.');
}finally{await browser.close();}