import {test,expect,chromium} from '@playwright/test';
import {execFileSync} from 'node:child_process';
import {mkdtemp,mkdir,readFile,writeFile,rm} from 'node:fs/promises';
import {tmpdir} from 'node:os';
import path from 'node:path';

// Extensão temporária exclusiva do QA: aciona o zoom real da aba, sem modificar
// o perfil do usuário ou substituir zoom por deviceScaleFactor/viewport.
test('zoom nativo 200%: referência, reflow, menu e calculadora operáveis',async({browserName},testInfo)=>{
  test.skip(browserName!=='chromium');
  test.skip(!process.env.IOTNEST_REFERENCE_URL);
  test.setTimeout(60000);
  const temporary=await mkdtemp(path.join(tmpdir(),'iotnest-zoom-'));
  const extension=path.join(temporary,'extension');await mkdir(extension);
  await writeFile(path.join(extension,'manifest.json'),JSON.stringify({manifest_version:3,name:'iotnest QA zoom',version:'1.0',permissions:['tabs'],background:{service_worker:'worker.js'}}));
  await writeFile(path.join(extension,'worker.js'),'chrome.runtime.onInstalled.addListener(() => {});');
  const fixture=(action,patch={})=>execFileSync(process.env.PHP_BIN||'php',['tests/php/home-fixture.php',action,JSON.stringify(patch)],{encoding:'utf8'});
  let context,created=false;
  try {
    fixture('create');created=true;fixture('settings',{motion:false,autoplay:false,lenis:false});
    context=await chromium.launchPersistentContext(path.join(temporary,'profile'),{channel:'chromium',headless:true,viewport:{width:1440,height:900},reducedMotion:'reduce',args:[`--disable-extensions-except=${extension}`,`--load-extension=${extension}`]});
    const worker=context.serviceWorkers()[0]||await context.waitForEvent('serviceworker');
    let fonts=await readFile('wp-content/themes/iotnest/assets/src/scss/base/_fonts.scss','utf8');
    for(const name of ['inter-latin','inter-latin-ext'])fonts=fonts.replace(`../fonts/${name}.woff2`,`data:font/woff2;base64,${(await readFile(`wp-content/themes/iotnest/assets/fonts/${name}.woff2`)).toString('base64')}`);
    await context.route('https://fonts.googleapis.com/**',route=>route.fulfill({contentType:'text/css',body:fonts}));
    const measurements=[];
    for(const [label,url]of [['wordpress',process.env.IOTNEST_TEST_URL],['reference',process.env.IOTNEST_REFERENCE_URL]]) {
      const page=await context.newPage();await page.goto(url);await page.evaluate(()=>document.fonts.ready);
      const zoom=await worker.evaluate(async url=>{const tabs=await globalThis.chrome.tabs.query({});const tab=tabs.find(tab=>tab.url===url);if(!tab)throw Error('Aba de QA não encontrada');await globalThis.chrome.tabs.setZoom(tab.id,2);return globalThis.chrome.tabs.getZoom(tab.id);},page.url());
      expect(zoom).toBe(2);await expect.poll(()=>page.evaluate(()=>innerWidth)).toBe(720);
      const geometry=await page.locator('.calc').evaluate(el=>({width:el.getBoundingClientRect().width,fontSize:getComputedStyle(el.querySelector('h2')).fontSize}));
      measurements.push({label,zoom,...await page.evaluate(()=>({innerWidth,innerHeight,devicePixelRatio})),calculator:geometry});
      await page.locator('.nav-toggle').focus();await page.keyboard.press('Enter');await expect(page.locator('.nav')).toBeVisible();await page.keyboard.press('Escape');
      if(label==='wordpress') {
        await expect(page.locator('.nav-toggle')).toBeFocused();
        for(const[name,value]of Object.entries({rate:200,hours:22,days:22,atual:60,iot:80}))await page.locator(`[name="${name}"]`).fill(String(value));
        await page.locator('[data-calc-form] button').focus();await page.keyboard.press('Enter');await expect(page.locator('[data-sc="iot"] [data-loss]')).toHaveText('R$ 19.360');
      }
      await page.locator('.calc').screenshot({path:testInfo.outputPath(`${label}-200.png`)});await page.close();
    }
    expect(measurements[0].calculator).toEqual(measurements[1].calculator);
    await writeFile(testInfo.outputPath('zoom.json'),JSON.stringify({browser:context.browser().version(),method:'chrome.tabs.setZoom(tabId, 2); getZoom=2; viewport inicial 1440×900 sem alteração por emulação',measurements},null,2));
  } finally {
    if(context)await context.close();if(created)fixture('delete');await rm(temporary,{recursive:true,force:true});
  }
});
