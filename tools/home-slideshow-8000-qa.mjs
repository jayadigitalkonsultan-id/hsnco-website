import{chromium}from'playwright';import fs from'node:fs';
const b=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});const p=await b.newPage({reducedMotion:'no-preference'});const errors=[];p.on('pageerror',e=>errors.push(e.message));p.on('response',r=>{if(r.status()>=400)errors.push(r.status()+' '+r.url())});
for(const width of[1440,390]){
 await p.setViewportSize({width,height:1000});await p.goto('http://127.0.0.1:8000/');
 if(await p.locator('.hero-slide').count()!==3)throw Error('Missing slideshow in actual homepage');
 await p.locator('.hero-slide').evaluateAll(imgs=>Promise.all(imgs.map(img=>img.decode())));
 const getState=()=>p.locator('.hero-slide').evaluateAll(imgs=>imgs.map(img=>+getComputedStyle(img).opacity));
 const before=await getState();await p.locator('.home-hero').screenshot({path:`docs/qa-motion/home-before-${width}.png`});
 await p.waitForTimeout(5000);const after=await getState();
 if(before.every((v,i)=>Math.abs(v-after[i])<.15))throw Error('Static homepage after five seconds');
 await p.locator('.home-hero').screenshot({path:`docs/qa-motion/home-after-${width}.png`});
 if(await p.evaluate(()=>document.documentElement.scrollWidth>innerWidth))throw Error('Overflow');
 console.log(width,JSON.stringify({before,after}));
}
await p.emulateMedia({reducedMotion:'reduce'});await p.waitForTimeout(100);const still=await p.locator('.hero-slide').evaluateAll(imgs=>imgs.map(img=>+getComputedStyle(img).opacity));if(still[0]!==1||still[1]!==0||still[2]!==0)throw Error('Reduced motion fallback failed');
await b.close();if(errors.length)throw Error(errors.join('\n'));console.log('PASS: actual port 8000 homepage changes images automatically on desktop/mobile; reduced motion; no overflow or HTTP/JS errors.');