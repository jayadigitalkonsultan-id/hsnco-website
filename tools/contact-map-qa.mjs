import {chromium} from 'playwright';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const page=await browser.newPage({reducedMotion:'reduce'});const errors=[];const remoteFailures=[];
page.on('pageerror',e=>errors.push(e.message));page.on('response',r=>{if(r.url().startsWith('http://127.0.0.1:8000/')&&r.status()>=400)errors.push(r.status()+' '+r.url());});
page.on('requestfailed',r=>{if(r.url().includes('google'))remoteFailures.push(r.failure()?.errorText);});
fs.mkdirSync('docs/qa-contact',{recursive:true});
try{
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:950});await page.goto('http://127.0.0.1:8000/contact',{waitUntil:'domcontentloaded'});
  const panel=page.locator('#lokasi-kantor');await panel.scrollIntoViewIfNeeded();await page.waitForTimeout(2500);
  const frame=page.locator('.contact-map-frame iframe');if(await frame.count()!==1)throw Error('Map missing');
  if(!(await frame.getAttribute('title')).includes('HS'))throw Error('Missing accessible title');
  const url=await page.locator('.contact-map-link').getAttribute('href');if(!url.startsWith('https://www.google.com/maps/search/?api=1&query='))throw Error('Map link');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Overflow '+width);
  await panel.screenshot({path:`docs/qa-contact/map-${width}.png`});
 }
 if(errors.length)throw Error(errors.join('\n'));
 console.log('PASS: desktop/mobile map layout, iframe title, external map URL, no local asset errors or overflow.');
 console.log('Google network failures: '+remoteFailures.length+' (external map availability is separate from local rendering).');
}finally{await browser.close();}