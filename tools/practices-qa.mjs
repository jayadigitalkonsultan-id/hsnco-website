import {chromium} from 'playwright';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const page=await browser.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));page.on('response',r=>{if(r.status()>=400)errors.push(r.status()+' '+r.url());});
fs.mkdirSync('docs/qa-practices',{recursive:true});
for(const width of [1920,1440,1366,768,390,375]){
 await page.setViewportSize({width,height:1000});await page.goto('http://127.0.0.1:4182/practice-areas');await page.evaluate(()=>document.fonts.ready);
 await page.locator('.services-scope').scrollIntoViewIfNeeded();await page.waitForTimeout(1000);
 if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))errors.push('overflow '+width);
 if(await page.locator('.services-items li').count()!==41)errors.push('incomplete list');
 for (const image of await page.locator('.services-visual img').all()) { await image.scrollIntoViewIfNeeded(); await image.evaluate(img => img.decode()); }
 if(await page.locator('.services-panel').count()!==8)errors.push('missing category');
 if(width===1440||width===390)await page.screenshot({path:`docs/qa-practices/services-${width}.png`,fullPage:true});
}
await browser.close();if(errors.length)throw Error(errors.join('\n'));console.log('PASS: six viewports, 41 services present, no horizontal overflow, HTTP or JavaScript errors.');