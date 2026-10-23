import {chromium} from 'playwright';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const origin=process.env.QA_BASE_URL || 'http://127.0.0.1:8000';
try {
 const page=await browser.newPage();const errors=[];
 page.on('pageerror',e=>errors.push(e.message));
 page.on('response',r=>{if(r.status()>=400)errors.push(`${r.status()} ${r.url()}`)});
 for(const width of [1440,768,390,375])for(const path of ['/','/about']){
  await page.setViewportSize({width,height:950});await page.goto(origin+path,{waitUntil:'networkidle'});
  const section=page.locator('[data-client-showcase]');await section.scrollIntoViewIfNeeded();
  await section.locator('img').evaluateAll(imgs=>imgs.forEach(img=>img.loading='eager'));
  await page.waitForFunction(()=>[...document.querySelectorAll('[data-client-showcase] img')].every(img=>img.complete&&img.naturalWidth>0));
  if((await section.innerText()).replace(/[↗\s]/g,'')!=='OurClients')throw Error('Unexpected visible text');
  if(await section.locator('.clients-rail').count()!==2)throw Error('Expected two logo rows');
  if(await section.locator('.clients-group').first().locator('img').count()<2)throw Error('Missing client logos');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Overflow');
 }
 await page.mouse.move(0,0);
 const track=page.locator('.clients-track').first();const transform=()=>track.evaluate(e=>getComputedStyle(e).transform);
 const initial=await transform();await page.waitForTimeout(500);if(initial===await transform())throw Error('Autoplay static');
 await page.locator('.clients-rail').first().hover();await page.waitForTimeout(60);const paused=await transform();await page.waitForTimeout(300);if(paused!==await transform())throw Error('Hover does not pause');
 await page.emulateMedia({reducedMotion:'reduce'});
 if(await track.evaluate(e=>getComputedStyle(e).animationName)!=='none')throw Error('Reduced motion ignored');
 const context=await browser.newContext({javaScriptEnabled:false});const fallback=await context.newPage();await fallback.goto(origin+'/');if(await fallback.locator('.clients-group img').count()<1)throw Error('No JavaScript fallback');await context.close();
 if(errors.length)throw Error(errors.join('\n'));
 console.log('PASS: Home/About logo-only carousel at 4 widths, images loaded, autoplay, hover pause, reduced motion, no-JS fallback, no overflow or JS/HTTP errors.');
} finally {await browser.close();}