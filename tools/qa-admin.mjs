import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import path from 'node:path';
import {spawn} from 'node:child_process';
import {randomUUID} from 'node:crypto';
import {fileURLToPath} from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const origin = process.env.HSNCO_ADMIN_URL || 'http://127.0.0.1:8000';
assert(['127.0.0.1', 'localhost', '[::1]'].includes(new URL(origin).hostname), 'QA is restricted to a local development server.');
const output = path.join(root, 'docs', 'qa-admin');
const temporaryRoot = path.resolve(root, 'storage/framework/testing');
const profile = path.resolve(temporaryRoot, `admin-browser-${randomUUID()}`);
assert(profile.startsWith(temporaryRoot + path.sep));
await fs.mkdir(output, {recursive: true});
await fs.mkdir(profile, {recursive: true});
const chrome = spawn(process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe', [
  '--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
  '--remote-debugging-port=0', `--user-data-dir=${profile}`, 'about:blank'
], {windowsHide: true, stdio: 'ignore'});
let launchError;
chrome.on('error', error => { launchError = error; });
const delay = ms => new Promise(resolve => setTimeout(resolve, ms));
async function until(check, description, timeout = 12000) {
  const started = Date.now();
  while (Date.now() - started < timeout) {
    const result = await check();
    if (result) return result;
    if (launchError) throw launchError;
    await delay(60);
  }
  throw new Error(`Timed out: ${description}`);
}
let socket;
let sessionId;
let counter = 0;
const pending = new Map();
const errors = [];
const report = {origin, browser: 'Chrome headless via CDP', pages: [], interactions: [], errors};
function call(method, params = {}, session = sessionId) {
  const id = ++counter;
  return new Promise((resolve, reject) => {
    const timeout = setTimeout(() => { pending.delete(id); reject(new Error(`CDP timeout: ${method}`)); }, 15000);
    pending.set(id, {resolve, reject, timeout});
    socket.send(JSON.stringify({id, method, params, ...(session ? {sessionId: session} : {})}));
  });
}
async function evaluate(expression) {
  const result = await call('Runtime.evaluate', {expression, awaitPromise: true, returnByValue: true});
  if (result.exceptionDetails) throw new Error(result.exceptionDetails.text + ': ' + (result.exceptionDetails.exception?.description || expression));
  return result.result.value;
}
async function click(selector) {
  const box = await evaluate(`(() => { const el = document.querySelector(${JSON.stringify(selector)}); if (!el) return null; const r = el.getBoundingClientRect(); return {x:r.x+r.width/2,y:r.y+r.height/2}; })()`);
  assert(box, `Missing visible control: ${selector}`);
  await call('Input.dispatchMouseEvent', {type: 'mousePressed', ...box, button: 'left', clickCount: 1});
  await call('Input.dispatchMouseEvent', {type: 'mouseReleased', ...box, button: 'left', clickCount: 1});
}
async function navigate(route) {
  await call('Page.navigate', {url: origin + route});
  await until(async () => evaluate(`document.readyState === 'complete' && location.pathname === ${JSON.stringify(route.split('?')[0])}`), route);
  await evaluate('document.fonts.ready.then(() => true)');
}
try {
  const portText = await until(async () => {
    try { return await fs.readFile(path.join(profile, 'DevToolsActivePort'), 'utf8'); } catch { return null; }
  }, 'Chrome startup');
  const [port, browserPath] = portText.trim().split(/\r?\n/);
  socket = new WebSocket(`ws://127.0.0.1:${port}${browserPath}`);
  socket.addEventListener('message', event => {
    const message = JSON.parse(event.data);
    if (message.id) {
      const request = pending.get(message.id);
      if (request) {
        clearTimeout(request.timeout);
        pending.delete(message.id);
        message.error ? request.reject(new Error(JSON.stringify(message.error))) : request.resolve(message.result);
      }
    }
    if (message.method === 'Runtime.exceptionThrown') errors.push({kind: 'runtime', message: message.params.exceptionDetails.exception?.description || message.params.exceptionDetails.text});
    if (message.method === 'Network.responseReceived' && message.params.response.status >= 400) errors.push({kind: 'http', status: message.params.response.status, url: message.params.response.url});
  });
  await new Promise((resolve, reject) => { socket.addEventListener('open', resolve, {once: true}); socket.addEventListener('error', reject, {once: true}); });
  const target = await call('Target.createTarget', {url: 'about:blank'}, null);
  sessionId = (await call('Target.attachToTarget', {targetId: target.targetId, flatten: true}, null)).sessionId;
  await Promise.all([call('Page.enable'), call('Runtime.enable'), call('Network.enable')]);
  await call('Emulation.setEmulatedMedia', {features: [{name: 'prefers-reduced-motion', value: 'reduce'}]});
  const routes = ['/admin', '/admin/pages', '/admin/practice-areas', '/admin/professionals', '/admin/insights', '/admin/media', '/admin/enquiries', '/admin/settings'];
  for (const width of [1440, 390]) {
    await call('Emulation.setDeviceMetricsOverride', {width, height: width === 390 ? 844 : 1000, deviceScaleFactor: 1, mobile: width === 390});
    for (const route of routes) {
      await navigate(route);
      await evaluate(`Promise.all([...document.images].map(image => { image.loading = 'eager'; return image.complete ? Promise.resolve() : new Promise(resolve => {image.addEventListener('load', resolve, {once:true}); image.addEventListener('error', resolve, {once:true});}); })).then(() => true)`);
      const state = await evaluate(`({title:document.querySelector('h1')?.textContent,headings:document.querySelectorAll('h1').length,width:innerWidth,scroll:document.documentElement.scrollWidth,bootstrap:document.querySelectorAll('script[src*="bootstrap.bundle.min.js"]').length,brokenImages:[...document.images].filter(i=>!i.naturalWidth).map(i=>i.src),unlabelled:[...document.querySelectorAll('input:not([type=hidden]),select,textarea')].filter(i=>!i.labels?.length&&!i.getAttribute('aria-label')).map(i=>i.id),demo:document.body.innerText.includes('Editor Demo')})`);
      assert.equal(state.headings, 1, route);
      assert(state.scroll <= state.width + 1, `Horizontal overflow: ${route} at ${width}: ${JSON.stringify(state)}`);
      assert.equal(state.bootstrap, 1);
      assert.equal(state.demo, false);
      assert.deepEqual(state.brokenImages, [], route);
      assert.deepEqual(state.unlabelled, [], route);
      if (route === '/admin' && width === 1440) {
        report.sidebar = await evaluate(`[...document.querySelectorAll('#adminSidebar .sidebar-brand, #adminSidebar .sidebar-brand img, #adminSidebar nav, #adminSidebar .sidebar-link, #adminSidebar .workspace-card')].map(el=>({tag:el.tagName,text:el.innerText?.trim(),display:getComputedStyle(el).display,visibility:getComputedStyle(el).visibility,opacity:getComputedStyle(el).opacity,rect:el.getBoundingClientRect().toJSON()}))`);
      }
      if (route === '/admin') await until(() => evaluate('!document.getElementById("editorialCanvasWrap").hidden'), 'Editorial chart');
      if (route === '/admin/pages') await until(() => evaluate('document.getElementById("contentTreeFallback").hidden'), 'Page tree');
      const metrics = await call('Page.getLayoutMetrics');
      const png = await call('Page.captureScreenshot', {format: 'png', captureBeyondViewport: true, clip: {x: 0, y: 0, width, height: Math.ceil(metrics.cssContentSize.height), scale: 1}});
      const filename = `${route.split('/').filter(Boolean).join('-')}-${width}.png`;
      await fs.writeFile(path.join(output, filename), Buffer.from(png.data, 'base64'));
      report.pages.push({route, width, title: state.title, screenshot: filename, horizontalOverflow: false, brokenImages: 0, unlabelledFields: 0});
    }
  }
  await call('Emulation.setDeviceMetricsOverride', {width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false});
  await navigate('/admin/pages');
  await until(() => evaluate('document.querySelectorAll("#contentTree .jstree-anchor").length > 1'), 'Page tree items');
  const selection = await evaluate(`(() => {const a=[...document.querySelectorAll('#contentTree .jstree-anchor')].find(a=>a.textContent.trim()==='Tentang Firma'); if(!a)return null; return '#'+CSS.escape(a.id);})()`);
  assert(selection);
  await click(selection);
  await until(() => evaluate('document.getElementById("selectedPageTitle").textContent === "Tentang Firma"'), 'Tree selection');
  report.interactions.push('jsTree selection opens the matching database page link');
  await navigate('/admin/practice-areas?q=Merger');
  assert.equal(await evaluate('document.querySelectorAll(".admin-table tbody tr").length'), 1);
  assert(await evaluate('document.querySelector(".admin-table tbody").textContent.includes("Merger")'));
  await navigate('/admin/practice-areas?status=published');
  assert(await evaluate('document.querySelector("main").textContent.includes("Belum ada konten yang sesuai")'));
  report.interactions.push('Server-side search, status filtering and empty state');
  for (const module of ['pages', 'practice-areas', 'professionals', 'insights', 'media']) {
    await navigate(`/admin/${module}/create`);
    assert(await evaluate('!!document.querySelector("fieldset[disabled]")'));
  }
  report.interactions.push('Local preview forms are disabled for writes');
  await call('Emulation.setDeviceMetricsOverride', {width: 390, height: 844, deviceScaleFactor: 1, mobile: true});
  await navigate('/admin');
  await click('button[aria-label="Buka navigasi"]');
  await until(() => evaluate('document.querySelector("#adminSidebar").classList.contains("show")'), 'Mobile navigation');
  assert.equal(await evaluate('document.querySelector("[aria-controls=adminSidebar]").getAttribute("aria-expanded")'), 'true');
  await call('Input.dispatchKeyEvent', {type: 'keyDown', key: 'Tab', code: 'Tab', windowsVirtualKeyCode: 9});
  await call('Input.dispatchKeyEvent', {type: 'keyUp', key: 'Tab', code: 'Tab', windowsVirtualKeyCode: 9});
  assert(await evaluate('document.getElementById("adminSidebar").contains(document.activeElement)'));
  await call('Input.dispatchKeyEvent', {type: 'keyDown', key: 'Escape', code: 'Escape', windowsVirtualKeyCode: 27});
  await call('Input.dispatchKeyEvent', {type: 'keyUp', key: 'Escape', code: 'Escape', windowsVirtualKeyCode: 27});
  await until(() => evaluate('!document.querySelector("#adminSidebar").classList.contains("show")'), 'Close mobile navigation');
  report.interactions.push('Mobile offcanvas, aria-expanded, keyboard focus and Escape');
  assert.deepEqual(errors, []);
  report.passed = true;
  console.log(JSON.stringify({passed: true, pages: report.pages.length, interactions: report.interactions, errors}));
} catch (error) {
  report.passed = false;
  report.failure = error.stack;
  console.error(error.stack);
  process.exitCode = 1;
} finally {
  await fs.writeFile(path.join(output, 'report.json'), JSON.stringify(report, null, 2));
  if (socket?.readyState === WebSocket.OPEN) {
    try { await call('Browser.close', {}, null); } catch {}
    socket.close();
  }
  chrome.kill();
  await delay(500);
  // Re-check containment before deleting only this invocation's generated browser profile.
  if (path.resolve(profile).startsWith(temporaryRoot + path.sep) && path.basename(profile).startsWith('admin-browser-')) {
    await fs.rm(profile, {recursive: true, force: true, maxRetries: 3, retryDelay: 200}).catch(() => {});
  }
}
