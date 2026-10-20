import {chromium} from 'playwright';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const failures=[];const page=await browser.newPage();page.on('pageerror',e=>failures.push(e.message));page.on('response',r=>{if(r.status()>=400)failures.push(`${r.status()} ${r.url()}`)});
let observed=0;
await page.addInitScript(()=>{const original=Element.prototype.animate;window.motionCount=0;Element.prototype.animate=function(...args){window.motionCount++;return original.apply(this,args);};});
for(const width of [1440,390]){
 await page.setViewportSize({width,height:900});
 for(const path of ['/','/about','/practice-areas','/team','/insights','/contact','/sitemap']){
  await page.goto('http://127.0.0.1:4182'+path);await page.waitForTimeout(120);
  observed+=await page.evaluate(()=>window.motionCount);
  await page.evaluate(()=>window.scrollTo(0,document.body.scrollHeight));await page.waitForTimeout(900);
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))failures.push('Overflow '+width+path);
 }
}
await page.emulateMedia({reducedMotion:'reduce'});await page.goto('http://127.0.0.1:4182/');await page.waitForTimeout(200);if(await page.evaluate(()=>window.motionCount)!==0)failures.push('Reduced motion ignored');
const context=await browser.newContext({javaScriptEnabled:false});const fallback=await context.newPage();await fallback.goto('http://127.0.0.1:4182/');if(!await fallback.locator('h1').isVisible())failures.push('No-JS content hidden');await context.close();
await browser.close();if(!observed)failures.push('No animations triggered');if(failures.length)throw Error(failures.join('\n'));console.log('PASS: 14 page/viewport combinations, animations triggered, reduced motion, no-JS fallback, no HTTP/JS errors or overflow.');