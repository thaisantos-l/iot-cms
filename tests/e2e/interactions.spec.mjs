import { pixels } from '../helpers/pixels.mjs';
import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { readFile, writeFile } from 'node:fs/promises';
const fixture=(action,patch={})=>execFileSync(process.env.PHP_BIN||'php',['tests/php/home-fixture.php',action,JSON.stringify(patch)],{encoding:'utf8'});
let original;
test.beforeAll(()=>{fixture('create');original=JSON.parse(fixture('read'));});
test.beforeEach(()=>{fixture('restore');fixture('settings',{motion:true,autoplay:true,counters:true,lenis:true,dada_scroll:true});});
test.afterAll(()=>fixture('delete'));
const errors=page=>{const messages=[];page.on('pageerror',error=>messages.push(error.message));return messages;};

test('teclado, conteúdo dinâmico, painéis inativos e limites da calculadora',async({page})=>{
  const messages=errors(page);await page.emulateMedia({reducedMotion:'reduce'});await page.goto('/');
  const ai=page.locator('.ai-tabs__item');await ai.first().focus();await page.keyboard.press('End');await expect(ai.last()).toHaveAttribute('aria-selected','true');
  await expect(page.locator('.ai-tabs__stage .is-on')).toHaveAttribute('aria-labelledby',await ai.last().getAttribute('id'));
  const mes=page.locator('.mess__tab');await mes.first().focus();await page.keyboard.press('End');await expect(page.locator('[data-mess-panel].is-active')).toHaveAttribute('aria-labelledby',await mes.last().getAttribute('id'));
  const how=page.locator('.how__tabs .pill');await how.first().focus();await page.keyboard.press('End');await expect(page.locator('[data-step-item]:visible [data-how-next]')).toBeDisabled();
  await expect(page.locator('[data-step-item][inert]')).toHaveCount(3);await page.keyboard.press('Home');await expect(page.locator('[data-step-item]:visible [data-how-prev]')).toBeDisabled();
  await page.locator('[data-calc-form] button').click();await expect(page.locator('[data-calc-error]')).toContainText('Confira');
  for(const [name,value] of Object.entries({rate:200,hours:22,days:22,atual:60,iot:80})) await page.locator(`[name="${name}"]`).fill(String(value));
  await page.locator('[data-calc-form] button').click();await expect(page.locator('[data-sc="atual"] [data-loss]')).toHaveText('R$ 38.720');await expect(page.locator('[data-sc="iot"] [data-loss]')).toHaveText('R$ 19.360');
  await page.locator('[name="iot"]').fill('50');await page.locator('[data-calc-form] button').click();await expect(page.locator('[data-calc-error]')).toContainText('igual ou maior');
  expect(messages).toEqual([]);
});

test('zero, um, vários e reordenação não dependem de quatro ou cinco itens',async({page})=>{
  const messages=errors(page);await page.emulateMedia({reducedMotion:'reduce'});
  for(const count of [0,1,7]) {
    const patch={order:['hardware','ai','mes','how_it_works']};
    for(const key of ['hardware','ai','mes','how_it_works']) {
      const section=structuredClone(original[`_iotnest_home_${key}`]), list=key==='hardware'?'images':'items';
      section[list]=Array.from({length:count},(_,index)=>({...section[list][index%section[list].length],id:`qa-${index}`})).reverse();patch[key]=section;
    }
    fixture('mutate',patch);await page.goto('/');
    await expect(page.locator('.motion-toggle')).toHaveCount(0);
    if(!count) {await expect(page.locator('[data-ai-tabs],[data-mess],[data-how]')).toHaveCount(0);continue;}
    await expect(page.locator('[data-step-item]')).toHaveCount(count);await expect(page.locator('.hw-carousel__slide[data-pos="0"]')).toHaveCount(1);
    if(count===1) {await expect(page.locator('.how__tabs,.hw-carousel__dots,.mess__tabs')).toHaveCount(0);}
    else {await page.locator('.how__tabs button').last().click();await expect(page.locator('[data-step-item]:visible')).toHaveAttribute('data-step-item','qa-0');await expect(page.locator('.hw-carousel__slide[data-pos="3"]')).toHaveCount(4);}
  }
  expect(messages).toEqual([]);
});

test('autoplay mantém 2s/4s e pausa por foco, aba oculta e ação explícita',async({page})=>{
  fixture('settings',{motion:false,lenis:false,dada_scroll:false});await page.clock.install();await page.goto('/');
  const mes=page.locator('.mess__tab');await expect(mes.first()).toHaveAttribute('aria-selected','true');await page.clock.runFor(2100);await expect(mes.nth(1)).toHaveAttribute('aria-selected','true');
  await mes.nth(1).focus();await page.clock.runFor(4500);await expect(mes.nth(1)).toHaveAttribute('aria-selected','true');
  await page.locator('.hero__title').evaluate(el=>{el.tabIndex=-1;el.focus();});
  await page.evaluate(()=>{Object.defineProperty(document,'hidden',{configurable:true,value:true});document.dispatchEvent(new Event('visibilitychange'));});await page.clock.runFor(5000);await expect(mes.nth(1)).toHaveAttribute('aria-selected','true');
  await page.evaluate(()=>{Object.defineProperty(document,'hidden',{configurable:true,value:false});document.dispatchEvent(new Event('visibilitychange'));});await page.clock.runFor(2100);await expect(mes.nth(2)).toHaveAttribute('aria-selected','true');
  const ai=page.locator('.ai-tabs__item');await page.locator('[data-ai-tabs]').scrollIntoViewIfNeeded();await page.mouse.move(0,0);await page.clock.runFor(4100);await expect(ai.nth(1)).toHaveAttribute('aria-selected','true');
  await page.locator('[data-ai-tabs] .motion-toggle').focus();await page.keyboard.press('Enter');await page.locator('.hero__title').focus();await page.clock.runFor(9000);await expect(ai.nth(1)).toHaveAttribute('aria-selected','true');
});

test('mídia indisponível e observers ausentes mantêm interações funcionais',async({page})=>{
  const messages=errors(page);await page.addInitScript(()=>{delete window.IntersectionObserver;delete window.ResizeObserver;});await page.route('**/*.{mp4,webm}',route=>route.abort());await page.goto('/');await page.locator('[data-dada-toggle]').click();await expect(page.locator('[data-dada-progress]')).toHaveAttribute('role','slider');await page.locator('.how__tabs button').last().click();await expect(page.locator('[data-how]')).toHaveAttribute('data-step','4');expect(messages).toEqual([]);
});

test('CLS institucional: 3 repetições frio/quente, carga e interações reais',async({browser},testInfo)=>{
  test.setTimeout(900000);const samples=[];
  for(const width of [390,1440]) {
    const context=await browser.newContext({viewport:{width,height:900}});
    try {
      const page=await context.newPage(),cdp=await context.newCDPSession(page);
      await cdp.send('Network.enable');await cdp.send('Network.emulateNetworkConditions',{offline:false,latency:150,downloadThroughput:1600000/8,uploadThroughput:750000/8});await cdp.send('Emulation.setCPUThrottlingRate',{rate:4});
      await page.addInitScript(()=>{window.iotnestShifts=[];new PerformanceObserver(list=>{for(const e of list.getEntries()) if(!e.hadRecentInput) window.iotnestShifts.push({value:e.value,time:e.startTime,sources:e.sources.map(s=>({node:s.node?.className,before:s.previousRect,after:s.currentRect}))});}).observe({type:'layout-shift',buffered:true});});
      for(let repetition=1;repetition<=3;repetition++) {
        await cdp.send('Network.clearBrowserCache');
        for(const cache of ['cold','warm']) {
          await page.goto(process.env.IOTNEST_TEST_URL,{waitUntil:'domcontentloaded'});await page.evaluate(()=>document.fonts.ready);await page.waitForTimeout(2500);
          for(const selector of ['#hardware','#ia','#mess','#como-funciona','#calculadora','#sobre']) {
            await page.locator(selector).scrollIntoViewIfNeeded();await page.waitForTimeout(selector==='#ia'?4400:2200);
            if(selector==='#como-funciona') {await page.locator('.how__tabs button').last().click();await page.waitForTimeout(1100);}
            if(selector==='#calculadora') {for(const [name,value] of Object.entries({rate:200,hours:22,days:22,atual:60,iot:80})) await page.locator(`[name="${name}"]`).fill(String(value));await page.locator('[data-calc-form] button').click();await page.waitForTimeout(2200);}
          }
          const shifts=await page.evaluate(()=>window.iotnestShifts);let score=0,sum=0,start=0,last=-Infinity;
          for(const shift of shifts){if(shift.time-last>1000 || shift.time-start>5000){start=shift.time;sum=0;}sum+=shift.value;last=shift.time;score=Math.max(score,sum);}
          samples.push({width,repetition,cache,cls:score,shifts});await writeFile(testInfo.outputPath('home-cls.json'),JSON.stringify({browser:browser.version(),network:{latencyMs:150,downMbps:1.6,upMbps:0.75},cpuSlowdown:4,samples},null,2));expect(score).toBeLessThanOrEqual(0.05);
        }
      }
    } finally {await context.close();}
  }
});

for(const width of [360,390,768,1024,1440]) {
  test(`T24 interações: tabs, hardware e calculadora em ${width}px`,async({page,context},testInfo)=>{
    test.setTimeout(240000);test.skip(!process.env.IOTNEST_REFERENCE_URL);
    const reference=await context.newPage();let fonts=await readFile('wp-content/themes/iotnest/assets/src/scss/base/_fonts.scss','utf8');
    for(const name of ['inter-latin','inter-latin-ext']) fonts=fonts.replace(`../fonts/${name}.woff2`,`data:font/woff2;base64,${(await readFile(`wp-content/themes/iotnest/assets/fonts/${name}.woff2`)).toString('base64')}`);
    await reference.route('https://fonts.googleapis.com/**',route=>route.fulfill({contentType:'text/css',body:fonts}));
    for(const current of [page,reference]) {await current.setViewportSize({width,height:1000});await current.emulateMedia({reducedMotion:'reduce'});await current.route('**/*.{webm,mp4}',route=>route.fulfill({status:204}));}
    await reference.goto(process.env.IOTNEST_REFERENCE_URL);await page.goto('/');
    for(const current of [page,reference]) {await current.evaluate(()=>document.fonts.ready);await current.evaluate(()=>{window.sections=[...document.querySelector('main').children].filter(node=>node.matches('section'));});}
    const report=[];
    for(const selector of ['#ia','#mess','#hardware','#calculadora']) {
      for(const current of [page,reference]) {
        await current.evaluate(selector=>{document.body.replaceChildren(window.sections.find(node=>node.matches(selector)));scrollTo(0,0);document.querySelectorAll('img').forEach(img=>{img.loading='eager';if(img.src.includes('/uploads/')){img.removeAttribute('srcset');img.removeAttribute('sizes');img.src=img.src.replace(/-\d+x\d+(?=\.webp)/,'');}});},selector);
        await current.locator('img').evaluateAll(images=>Promise.all(images.map(img=>img.decode().catch(()=>{}))));
      }
      const controls={'#ia':'.ai-tabs__item','#mess':'.mess__tab','#hardware':'.hw-carousel__dots button'};
      const count=controls[selector]?await page.locator(controls[selector]).count():1;
      for(let index=0;index<count;index++) {
        for(const current of [page,reference]) {
          if(controls[selector]) await current.locator(controls[selector]).nth(index).click();
          else {for(const [name,value] of Object.entries({rate:200,hours:22,days:22,atual:60,iot:80})) await current.locator(`[name="${name}"]`).fill(String(value));await current.locator('[data-calc-form] button').click();}
          await current.locator('button').evaluateAll(buttons=>buttons.forEach(button=>button.blur()));await current.mouse.move(0,0);await current.waitForTimeout(1000);
        }
        const expected=await reference.locator(selector).screenshot({path:testInfo.outputPath(`${selector.slice(1)}-${index}-reference.png`),animations:'disabled'});
        const actual=await page.locator(selector).screenshot({path:testInfo.outputPath(`${selector.slice(1)}-${index}-wordpress.png`),animations:'disabled'});
        const diff=await pixels(page,expected,actual);report.push({selector,index,diff});
      }
    }
    await writeFile(testInfo.outputPath('interactions-visual.json'),JSON.stringify({width,report},null,2));await reference.close();expect(report.filter(row=>row.diff.changed!==0)).toEqual([]);
  });
}
