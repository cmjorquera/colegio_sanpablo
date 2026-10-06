// Local smoke test: actual template/assets, isolated PHP fixture, no database.
// Run from the project root: node tests/pagina_menu_browser.cjs
const {spawn} = require('node:child_process');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const pause = ms => new Promise(resolve => setTimeout(resolve, ms));
const root = path.resolve(__dirname, '..');
const profile = fs.mkdtempSync(path.join(os.tmpdir(), 'sp-menu-browser-'));
const serverPort = 19000 + Math.floor(Math.random() * 1000);
const debugPort = serverPort + 1000;
const base = `http://127.0.0.1:${serverPort}`;
let server, chrome, socket, checks = 0;
const pending = new Map(); let sequence = 0;
const errors = [];
function check(value, label) { if (!value) throw new Error(label); checks++; }
function call(method, params = {}) {
  return new Promise((resolve, reject) => {
    const id = ++sequence; pending.set(id, {resolve, reject});
    socket.send(JSON.stringify({id, method, params}));
    setTimeout(() => { if (pending.has(id)) { pending.delete(id); reject(new Error(`Timeout ${method}`)); } }, 10000).unref();
  });
}
async function evaluate(expression) {
  const result = await call('Runtime.evaluate', {expression, returnByValue:true, awaitPromise:true});
  if (result.exceptionDetails) throw new Error(JSON.stringify(result.exceptionDetails));
  return result.result.value;
}
async function navigate(route) { await call('Page.navigate', {url:base + route}); await pause(900); }
(async () => {
  server = spawn('php', ['-n', '-S', `127.0.0.1:${serverPort}`, 'tests/pagina_menu.php'], {
    cwd:root, windowsHide:true, env:{...process.env, SP_MENU_TEST_SERVER:'1'}, stdio:'ignore'
  });
  chrome = spawn('C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe', [
    '--headless=new', '--disable-extensions', '--no-first-run', '--no-default-browser-check',
    `--remote-debugging-port=${debugPort}`, `--user-data-dir=${profile}`, 'about:blank'
  ], {windowsHide:true, stdio:'ignore'});
  let target;
  for (let i=0; i<60 && !target; i++) {
    try { target = (await (await fetch(`http://127.0.0.1:${debugPort}/json/list`)).json()).find(t=>t.type==='page'); } catch {}
    if (!target) await pause(200);
  }
  if (!target) throw new Error('Chrome no disponible');
  socket = new WebSocket(target.webSocketDebuggerUrl);
  await new Promise((resolve,reject)=>{socket.onopen=resolve;socket.onerror=reject;});
  socket.onmessage = event => {
    const message = JSON.parse(event.data);
    if (message.id && pending.has(message.id)) {
      const item = pending.get(message.id); pending.delete(message.id);
      message.error ? item.reject(new Error(JSON.stringify(message.error))) : item.resolve(message.result);
    }
    if (message.method==='Runtime.exceptionThrown') errors.push(message.params.exceptionDetails.text);
  };
  await call('Page.enable'); await call('Runtime.enable');
  for (const width of [1920,1600,1366,1024,768,390,320]) {
    await call('Emulation.setDeviceMetricsOverride', {width,height:1000,deviceScaleFactor:1,mobile:false});
    for (const [route,count] of [['/biblioteca',3],['/confesionalidad',6]]) {
      await navigate(route);
      const state = await evaluate(`(() => ({sections:document.querySelectorAll('[data-menu-section]').length,
        overflow:document.documentElement.scrollWidth>innerWidth+1,
        hero:Math.round(document.querySelector('.sp-menu-hero').getBoundingClientRect().height),
        images:[...document.querySelectorAll('.sp-menu-gallery-slide img')].every(i=>!i.complete||i.naturalWidth>0),
        anchors:[...document.querySelectorAll('[data-menu-section]')].map(n=>n.id)}))()`);
      check(state.sections===count, `${route} secciones ${width}`);
      if (state.overflow) console.error(await evaluate(`[...document.querySelectorAll('body *')].filter(n=>n.getBoundingClientRect().right>innerWidth+1 && getComputedStyle(n).position!=='fixed').map(n=>({tag:n.tagName,cls:n.className,right:n.getBoundingClientRect().right})).slice(0,15)`));
      check(!state.overflow, `${route} overflow ${width}`);
      check(state.hero===(width<576?310:width<992?340:410), `${route} hero ${width}`);
      check(state.images, `${route} imagenes ${width}`);
      check(new Set(state.anchors).size===count, `${route} anchors ${width}`);
      await evaluate(`document.querySelector('.carousel-control-next').click()`); await pause(700);
      check(await evaluate(`document.querySelector('[data-menu-carousel] .carousel-item.active').getAttribute('aria-label')==='2 de 3'`), `Carrusel ${width}`);
    }
  }
  await navigate('/mi-san-pablo');
  check(await evaluate(`document.querySelectorAll('[data-menu-section]').length===1`), 'Mi San Pablo interno');
  check(await evaluate(`[...document.querySelectorAll('a')].some(a=>/ceibal/i.test(a.href))`), 'Portal externo Ceibal');
  await navigate('/confesionalidad?scenario=edited');
  check(await evaluate(`document.body.textContent.includes('Edicion reflejada.')`), 'Edicion dinamica');
  for (const route of ['/institucional','/maternal','/inicial','/primaria','/3er-ciclo-ebi','/bachillerato','/libre-asistido','/biblioteca.php']) {
    check((await fetch(base+route)).status===200, `HTTP ${route}`);
  }
  check((await fetch(base+'/ruta-inexistente')).status===404, '404 desconocido');
  check(errors.length===0, 'Sin excepciones JS: '+errors.join(','));
  console.log(JSON.stringify({checks,widths:[1920,1600,1366,1024,768,390,320],database:'simulada',javascriptErrors:errors},null,2));
})().catch(error=>{console.error(error);process.exitCode=1;}).finally(()=>{
  if(socket) socket.close(); if(chrome) chrome.kill(); if(server) server.kill();
  // The browser profile is outside the repository; leave OS-managed temporary data.
});
