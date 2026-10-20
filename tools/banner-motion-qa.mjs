import{chromium}from'playwright';import fs from'node:fs';
const b=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});const p=await b.newPage({reducedMotion:'no-preference'});const errors=[];p.on('pageerror',e=>errors.push(e.message));p.on('response',r=>{if(r.status()>=400)errors.push(r.status()+' '+r.url())});fs.mkdirSync('docs/qa-motion',{recursive:true});
for(const width of[1440,390]){await p.setViewportSize({width,height:900});for(const path of['/about','/practice-areas','/team','/insights','/contact','/sitemap']){
 await p.goto('http://127.0.0.1:4182'+path);const banner=p.locator('.inner-hero-animated');await banner.locator('.banner-visual img').evaluate(i=>i.decode());
 const animations=await banner.locator('.banner-visual').evaluate(e=>e.getAnimations().map(a=>a.animationName));if(!animations.includes('hsnco-banner-drift'))throw Error('Static banner '+path);
 if(await p.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Overflow '+width+path);
 if(path==='/practice-areas'){await p.waitForTimeout(1600);await banner.screenshot({path:`docs/qa-motion/banner-${width}.png`});}
}}
await p.emulateMedia({reducedMotion:'reduce'});if(await p.locator('.banner-visual').evaluate(e=>e.getAnimations().length))throw Error('Reduced motion ignored');
await b.close();if(errors.length)throw Error(errors.join('\n'));console.log('PASS: animated banner on six internal pages / two widths, all images decoded, reduced motion, no overflow/HTTP/JS errors.');