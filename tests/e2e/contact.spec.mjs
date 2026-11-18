import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
const run=(file,args=[])=>execFileSync(process.env.PHP_BIN||'php',[`tests/php/${file}.php`,...args],{encoding:'utf8'});
const home=(action,patch={})=>run('home-fixture',[action,JSON.stringify(patch)]);
const transport=(action,value)=>run('contact-fixture',[action,...(value?[value]:[])]);
test.beforeAll(()=>home('create'));
test.beforeEach(()=>{home('restore');transport('reset');home('settings',{contact_enabled:true,contact_recipient:'recipient@example.test',contact_subject:'QA contact',motion:false,lenis:false,youtube:true,youtube_consent:false,analytics:false,tag_manager:false});});
test.afterAll(()=>home('delete'));
const fill=async page=>{await page.locator('#f-nome').fill('Pessoa QA');await page.locator('#f-email').fill('person@example.test');await page.locator('#f-msg').fill('Mensagem QA descartável');};
const form=page=>page.locator('[data-contact-form]');
// offsetTop mede layout e preserva o translateY(-1px) intencional do hover original.

test('contato com JS: validação, erro preservado, renovação e envio único',async({page})=>{
  const errors=[];page.on('pageerror',e=>errors.push(e.message));await page.goto('/');
  const button=form(page).locator('[type=submit]');await button.click();await expect(page.locator('#f-nome')).toBeFocused();await expect(page.locator('#f-email')).toHaveAttribute('aria-invalid','true');expect(transport('count')).toBe('0');
  await fill(page);transport('mode','failure');const before=await button.evaluate(el=>el.offsetTop);await button.click();await expect(page.locator('[data-contact-status]')).toHaveAttribute('data-code','failed');await expect(page.locator('#f-msg')).toHaveValue('Mensagem QA descartável');expect(await button.evaluate(el=>el.offsetTop)).toBe(before);
  transport('reset');await page.locator('[name=nonce]').evaluate(el=>el.value='expired-cached-html');await button.dblclick();await expect(page.locator('[data-contact-status]')).toHaveAttribute('data-code','accepted');await expect(button).toBeDisabled();expect(transport('count')).toBe('1');expect(errors).toEqual([]);
});

test('HTML sem JS conserva campos com nonce vencido e não duplica reenvio',async({browser})=>{
  const context=await browser.newContext({javaScriptEnabled:false});try {const page=await context.newPage();await page.goto(process.env.IOTNEST_TEST_URL);await fill(page);await page.locator('[name=nonce]').evaluate(el=>el.value='expired');await form(page).locator('[type=submit]').click();await expect(page.locator('[data-contact-status]')).toContainText('Sessão renovada');await expect(page.locator('#f-nome')).toHaveValue('Pessoa QA');expect(transport('count')).toBe('0');
  await form(page).locator('[type=submit]').click();await expect(page.locator('[data-contact-status]')).toContainText('encaminhada ao serviço');expect(transport('count')).toBe('1');await expect(form(page).locator('[type=submit]')).toBeDisabled();}finally{await context.close();}
});

test('timeout preserva campos e consulta o token incerto sem duplicar transporte',async({page})=>{
  await page.goto('/');await fill(page);let first=true;
  await page.route('**/wp-json/iotnest/v1/contact',async route=>{if(first){first=false;await route.fetch();await route.abort('timedout');}else await route.continue();});
  await form(page).locator('[type=submit]').click();await expect(page.locator('[data-contact-status]')).toHaveAttribute('data-code','uncertain');await expect(page.locator('#f-msg')).toHaveValue('Mensagem QA descartável');await form(page).locator('[type=submit]').click();await expect(page.locator('[data-contact-status]')).toHaveAttribute('data-code','accepted');expect(transport('count')).toBe('1');
});

test('YouTube sob demanda: teclado, Escape, retorno de foco e consentimento negado',async({page})=>{
  const requests=[];await page.route('https://www.youtube-nocookie.com/**',route=>{requests.push(route.request().url());return route.fulfill({contentType:'text/html',body:'<html><body>QA player</body></html>'});});await page.goto('/');expect(requests).toEqual([]);const trigger=page.locator('[data-video-provider=youtube]').first();await trigger.focus();await page.keyboard.press('Enter');await expect(page.locator('dialog')).toBeVisible();await expect(page.locator('dialog iframe')).toHaveCount(1);await page.keyboard.press('Escape');await expect(page.locator('dialog')).not.toBeVisible();await expect(trigger).toBeFocused();await expect(page.locator('dialog iframe')).toHaveCount(0);expect(requests.length).toBe(1);
  home('settings',{youtube_consent:true});transport('cmp');await page.addInitScript(()=>{window.iotnestConsent={has:()=>false,request:purpose=>window.requestedConsent=purpose};});await page.goto('/');await page.locator('[data-video-provider=youtube]').first().click();expect(requests.length).toBe(1);expect(await page.evaluate(()=>window.requestedConsent)).toBe('youtube');
});

test('analytics exige flag e bridge; consentimento autoriza uma tag e revogação interrompe',async({page})=>{
  const requests=[];await page.route('https://www.googletagmanager.com/**',route=>{requests.push(route.request().url());return route.fulfill({contentType:'text/javascript',body:'/* offline QA */'});});
  home('settings',{analytics:true,ga4_id:'G-QATEST',tag_manager:true,gtm_id:'GTM-QATEST'});await page.addInitScript(()=>{window.iotnestConsent={has:()=>sessionStorage.getItem('qa-consent')==='yes'};});await page.goto('/');expect(requests).toEqual([]);
  transport('cmp');await page.goto('/');expect(requests).toEqual([]);await page.evaluate(()=>{sessionStorage.setItem('qa-consent','yes');dispatchEvent(new Event('iotnest:consent'));dispatchEvent(new Event('iotnest:consent'));});await expect.poll(()=>requests.length).toBe(1);expect(requests[0]).toContain('/gtm.js?id=GTM-QATEST');
  await Promise.all([page.waitForNavigation(),page.evaluate(()=>{sessionStorage.removeItem('qa-consent');dispatchEvent(new Event('iotnest:consent'));})]);await page.waitForTimeout(150);expect(requests.length).toBe(1);await expect(page.locator('[data-iotnest-provider]')).toHaveCount(0);
});

for(const width of [360,390,768,1024,1440]) test(`T24 contato: formulário e mensagens em ${width}px`,async({page,context},testInfo)=>{
  test.skip(!process.env.IOTNEST_REFERENCE_URL);const {readFile,writeFile}=await import('node:fs/promises');const {pixels}=await import('../helpers/pixels.mjs');const reference=await context.newPage();let fonts=await readFile('wp-content/themes/iotnest/assets/src/scss/base/_fonts.scss','utf8');
  for(const name of ['inter-latin','inter-latin-ext']) fonts=fonts.replace(`../fonts/${name}.woff2`,`data:font/woff2;base64,${(await readFile(`wp-content/themes/iotnest/assets/fonts/${name}.woff2`)).toString('base64')}`);
  await reference.route('https://fonts.googleapis.com/**',route=>route.fulfill({contentType:'text/css',body:fonts}));
  for(const current of [page,reference]){await current.setViewportSize({width,height:1000});await current.emulateMedia({reducedMotion:'reduce'});}
  await reference.goto(process.env.IOTNEST_REFERENCE_URL);await page.goto('/');
  for(const current of [page,reference]){await current.evaluate(()=>document.fonts.ready);await current.evaluate(()=>{document.body.replaceChildren(document.querySelector('#contato'));scrollTo(0,0);document.querySelectorAll('img').forEach(img=>{img.loading='eager';if(img.src.includes('/uploads/')){img.removeAttribute('srcset');img.removeAttribute('sizes');img.src=img.src.replace(/-\d+x\d+(?=\.webp)/,'');}});});await current.locator('img').evaluateAll(images=>Promise.all(images.map(img=>img.decode().catch(()=>{}))));}
  const expected=await reference.locator('#contato').screenshot({path:testInfo.outputPath('reference.png')}),actual=await page.locator('#contato').screenshot({path:testInfo.outputPath('wordpress.png')});const diff=await pixels(page,expected,actual);await writeFile(testInfo.outputPath('contact-visual.json'),JSON.stringify({width,diff},null,2));expect(diff.changed).toBe(0);
  const button=form(page).locator('[type=submit]'),before=await button.evaluate(el=>el.offsetTop);await button.click();await expect(page.locator('[data-contact-status]')).toHaveAttribute('data-code','invalid');expect(await button.evaluate(el=>el.offsetTop)).toBe(before);await reference.close();
});

test('flag YouTube desligada e bridge ausente não ativam fornecedor; dialog ausente mantém link permitido',async({page})=>{
  const requests=[];await page.route('https://www.youtube-nocookie.com/**',route=>{requests.push(route.request().url());return route.fulfill({body:''});});
  home('settings',{youtube:false});await page.goto('/');await expect(page.locator('[data-video-provider=youtube]').first()).not.toHaveAttribute('href',/.+/);await page.locator('[data-video-provider=youtube]').first().dispatchEvent('click');expect(requests).toEqual([]);
  home('settings',{youtube:true,youtube_consent:true});await page.goto('/');await expect(page.locator('[data-video-provider=youtube]').first()).not.toHaveAttribute('href',/.+/);expect(requests).toEqual([]);
  home('settings',{youtube_consent:false});await page.addInitScript(()=>{HTMLDialogElement.prototype.showModal=undefined;});await page.goto('/');await expect(page.locator('[data-video-provider=youtube]').first()).toHaveAttribute('href',/^https:\/\/www.youtube.com\/watch/);await expect(page.locator('dialog')).toHaveCount(0);expect(requests).toEqual([]);
});

test('CLS contato: mensagens reservadas em 3 repetições frio/quente mobile e desktop',async({browser},testInfo)=>{
  test.setTimeout(300000);const {writeFile}=await import('node:fs/promises');home('mutate',{order:['contact']});const samples=[];
  for(const width of [390,1440]){const context=await browser.newContext({viewport:{width,height:900}});try{const page=await context.newPage(),cdp=await context.newCDPSession(page);await cdp.send('Network.enable');await cdp.send('Network.emulateNetworkConditions',{offline:false,latency:150,downloadThroughput:1600000/8,uploadThroughput:750000/8});await cdp.send('Emulation.setCPUThrottlingRate',{rate:4});
    await page.addInitScript(()=>{window.shifts=[];new PerformanceObserver(list=>{for(const e of list.getEntries())if(!e.hadRecentInput)window.shifts.push({value:e.value,time:e.startTime});}).observe({type:'layout-shift',buffered:true});});
    await page.route('**/wp-json/iotnest/v1/contact',async route=>{const response=await route.fetch();await new Promise(resolve=>setTimeout(resolve,700));await route.fulfill({response});});
    for(let repetition=1;repetition<=3;repetition++){await cdp.send('Network.clearBrowserCache');for(const cache of ['cold','warm']){transport('reset');transport('mode','failure');await page.goto(process.env.IOTNEST_TEST_URL);await page.evaluate(()=>document.fonts.ready);await page.waitForTimeout(700);await fill(page);const button=form(page).locator('[type=submit]');await button.scrollIntoViewIfNeeded();const before=await button.evaluate(el=>el.offsetTop);await button.click();await expect(page.locator('[data-contact-status]')).toHaveAttribute('data-code','failed');await page.waitForTimeout(700);expect(await button.evaluate(el=>el.offsetTop)).toBe(before);
      const shifts=await page.evaluate(()=>window.shifts);let cls=0,sum=0,start=0,last=-Infinity;for(const shift of shifts){if(shift.time-last>1000||shift.time-start>5000){start=shift.time;sum=0;}sum+=shift.value;last=shift.time;cls=Math.max(cls,sum);}samples.push({width,repetition,cache,cls,shifts});await writeFile(testInfo.outputPath('contact-cls.json'),JSON.stringify({browser:browser.version(),network:{latencyMs:150,downMbps:1.6,upMbps:0.75},cpuSlowdown:4,samples},null,2));expect(cls).toBeLessThanOrEqual(0.05);
    }}
  }finally{await context.close();}}
});
