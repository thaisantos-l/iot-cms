import {test,expect} from '@playwright/test';
import {execFileSync} from 'node:child_process';
import {readFile,writeFile} from 'node:fs/promises';
const php=(file,action,patch={})=>execFileSync(process.env.PHP_BIN||'php',[`tests/php/${file}.php`,action,JSON.stringify(patch)],{encoding:'utf8'});
test.beforeAll(()=>php('home-fixture','create'));
test.beforeEach(()=>{php('home-fixture','restore');php('quality-fixture','restore');php('home-fixture','settings',{motion:false,autoplay:false,lenis:false});});
test.afterAll(()=>{php('quality-fixture','restore');php('home-fixture','delete');});

test('T12: todas as cores editáveis chegam no HTML inicial sem build',async({page})=>{
  const keys=JSON.parse(php('quality-fixture','palette'));await page.goto('/');const tokens=await page.evaluate(keys=>Object.fromEntries(keys.map(key=>[key,getComputedStyle(document.documentElement).getPropertyValue('--'+key.replaceAll('_','-')).trim()])),keys);for(const value of Object.values(tokens))expect(value).toBe('#123456');
  expect(await page.locator('.calc').evaluate(el=>getComputedStyle(el).backgroundColor)).toBe('rgb(18, 52, 86)');
});

test('HTML público sem cache não certificado; IDs únicos e controles nomeados',async({page,request})=>{
  const response=await page.goto('/');expect(response.headers()['cache-control']).toContain('no-store');await expect(page.locator('main h1')).toHaveCount(1);
  expect(await page.locator('[id]').evaluateAll(nodes=>{const ids=nodes.map(node=>node.id);return ids.filter((id,index)=>ids.indexOf(id)!==index);})).toEqual([]);
  const token=await request.get('/wp-json/iotnest/v1/contact-token?page_id=0');expect(token.headers()['cache-control']).toContain('no-store');
  const missing=await page.locator('button:visible,input:visible,textarea:visible').evaluateAll(nodes=>nodes.filter(node=>!node.labels?.length&&!node.getAttribute('aria-label')&&!node.getAttribute('aria-labelledby')&&!node.textContent.trim()).map(node=>node.outerHTML));expect(missing).toEqual([]);
});

test('contraste inicial é medido contra a referência; reflow equivalente a zoom 200%',async({page,context},testInfo)=>{
  test.skip(!process.env.IOTNEST_REFERENCE_URL);const reference=await context.newPage();let fonts=await readFile('wp-content/themes/iotnest/assets/src/scss/base/_fonts.scss','utf8');for(const name of ['inter-latin','inter-latin-ext'])fonts=fonts.replace(`../fonts/${name}.woff2`,`data:font/woff2;base64,${(await readFile(`wp-content/themes/iotnest/assets/fonts/${name}.woff2`)).toString('base64')}`);await reference.route('https://fonts.googleapis.com/**',route=>route.fulfill({contentType:'text/css',body:fonts}));
  await page.goto('/');await reference.goto(process.env.IOTNEST_REFERENCE_URL);const samples=[];
  for(const [label,current]of [['wordpress',page],['reference',reference]]){await current.evaluate(()=>document.fonts.ready);const colors=await current.locator('.hero .btn,.lead,.calc__title').evaluateAll(nodes=>nodes.slice(0,6).map(node=>({selector:node.className,text:getComputedStyle(node).color,background:getComputedStyle(node).backgroundColor})));samples.push({label,colors});}
  const ratio=(fg,bg)=>{const linear=value=>{value/=255;return value<=0.04045?value/12.92:((value+0.055)/1.055)**2.4;};const luminance=values=>values.map(linear).reduce((sum,value,index)=>sum+value*[0.2126,0.7152,0.0722][index],0);const a=luminance(fg),b=luminance(bg);return (Math.max(a,b)+0.05)/(Math.min(a,b)+0.05);};
  const brand=await page.evaluate(()=>getComputedStyle(document.documentElement).getPropertyValue('--brand').trim());expect(brand).toBe('#02afef');const whiteOnBrand=ratio([255,255,255],[2,175,239]);
  await page.setViewportSize({width:720,height:450});await page.locator('.nav-toggle').click();await expect(page.locator('.nav')).toBeVisible();await page.keyboard.press('Escape');await page.locator('[data-calc-form] button').click();await expect(page.locator('[data-calc-error]')).toBeVisible();
  await writeFile(testInfo.outputPath('contrast-reflow.json'),JSON.stringify({samples,whiteOnBrand,normalTextAA:whiteOnBrand>=4.5,largeTextAA:whiteOnBrand>=3,decision:'Cores originais preservadas por R14; medir não equivale a declarar conformidade AA.',reflow:{physicalWidth:1440,cssWidth:720,cssHeight:450,description:'Equivalência de layout a 200%, sem afirmar acionamento do zoom nativo.'}},null,2));await reference.close();
});
