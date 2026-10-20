import { chromium } from 'playwright';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const page=await browser.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));page.on('response',r=>{if(r.status()>=400)errors.push(r.status()+' '+r.url());});
fs.mkdirSync('../docs/qa-login',{recursive:true});
for(const width of [1920,1440,1366,768,390,375]){
 await page.setViewportSize({width,height:900});await page.goto('http://127.0.0.1:4180/login');await page.evaluate(()=>document.fonts.ready);
 if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Overflow '+width);
 await page.screenshot({path:`../docs/qa-login/login-${width}.png`,fullPage:true});
}
await page.locator('[data-password-toggle]').click();if(await page.locator('#password').getAttribute('type')!=='text')throw Error('Password toggle');
await page.locator('[data-password-toggle]').click();if(await page.locator('#password').getAttribute('type')!=='password')throw Error('Password hide');
for(const path of ['/forgot-password','/activate']){await page.goto('http://127.0.0.1:4180'+path);if(await page.locator('h1').count()!==1)throw Error(path);}
await browser.close();if(errors.length)throw Error(errors.join('\n'));console.log('PASS: 6 viewports, assets HTTP, no overflow/runtime errors, password toggle, recovery and activation pages.');