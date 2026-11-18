import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { readFile, writeFile } from 'node:fs/promises';
const fixture = (action, patch = {}) => JSON.parse(execFileSync(process.env.PHP_BIN || 'php', ['tests/php/publication-fixture.php', action, JSON.stringify(patch)], {encoding:'utf8'}));
let records;
test.beforeAll(() => {records = fixture('create');});
test.afterAll(() => fixture('delete'));

test('publicações: ciclo zero → um → vários → zero, busca, paginação, senha e preview', async ({page}) => {
  test.setTimeout(90000);
  for (const route of ['/blog/', '/cases/']) {
    const response = await page.goto(route);
    expect(response.status()).toBe(200);
    await expect(page.locator('.empty-state')).toBeVisible();
    await expect(page.locator('.case')).toHaveCount(0);
  }
  await page.goto('/');
  await expect(page.locator('#cases, #blog, .nav a')).toHaveCount(0);
  fixture('status', {post:{draft:{post_status:'publish'}},iot_case:{draft:{post_status:'publish'}}});
  await page.reload();
  await expect(page.locator('#cases .case, #blog .case')).toHaveCount(2);
  await expect(page.locator('.nav a')).toHaveCount(2);
  fixture('many');
  await page.goto('/cases/');
  await expect(page.locator('.listing > .case')).toHaveCount(2);
  await expect(page.locator('.pagination')).toBeVisible();
  const first = await page.locator('.listing h3').allTextContents();
  await page.goto('/cases/page/2/');
  const second = await page.locator('.listing h3').allTextContents();
  expect(first.some(title => second.includes(title))).toBe(false);
  await page.locator('.case__link').first().click();
  await expect(page.locator('.article__cover')).toHaveCount(0);
  await expect(page.locator('.related .case')).toHaveCount(2);
  await expect(page.locator('.article__body')).toContainText('Conteúdo real de teste');
  expect((await page.goto('/nao-existe-qa/')).status()).toBe(404);
  expect((await page.goto('/artigo-1.html')).status()).toBe(404);
  const old = await page.request.get('/cases.html', {maxRedirects:0});
  expect(old.status()).toBe(301); expect(old.headers().location).toMatch(/\/cases\/$/);
  await page.goto(`/?p=${records.posts.post.password}`);
  await expect(page.locator('.post-password-form')).toBeVisible();
  await expect(page.locator('.article__body')).not.toContainText('confidencial');
  await page.goto('/wp-login.php');
  await expect(page.locator('#user_login')).toBeFocused();
  await page.locator('#user_login').fill('iotnest-qa'); await page.locator('#user_pass').fill(process.env.IOTNEST_TEST_ADMIN_PASSWORD);
  await Promise.all([page.waitForURL(/\/wp-admin\/$/),page.locator('#wp-submit').click()]);
  await page.goto('/?s=confidencial');
  const searchTitles = await page.locator('.listing h3').allTextContents();
  expect(searchTitles.every(title => title.endsWith('draft'))).toBe(true);
  await page.goto(`/?p=${records.posts.post.pending}&preview=true`);
  await expect(page.locator('.article__body')).toContainText('confidencial');
  fixture('empty'); await page.goto('/');
  await expect(page.locator('#cases, #blog, .nav a')).toHaveCount(0);
  await page.goto('/?s=QA'); await expect(page.locator('.listing .case')).toHaveCount(1); // only public QA Home page.
});

test('documentos legais: sumário, IDs, URLs reais e 301 somente para equivalente publicado', async ({page}) => {
  await page.goto('/privacidade/');
  await expect(page.locator('.legal__toc ol li')).toHaveCount(11);
  expect(await page.locator('.legal__body [id]').evaluateAll(nodes => new Set(nodes.map(n=>n.id)).size===nodes.length)).toBe(true);
  await expect(page.locator('a[href$=".html"]')).toHaveCount(0);
  await page.locator('.legal__toc ol a').first().click();
  expect(new URL(page.url()).hash).toBe('#documento-1');
  const response=await page.request.get('/privacidade.html',{maxRedirects:0}); expect(response.status()).toBe(301);
});

for(const width of [360,390,768,1024,1440]) test(`T24 páginas internas: documento legal em ${width}px`, async({page,context},testInfo)=>{
  test.skip(!process.env.IOTNEST_REFERENCE_URL);
  const reference=await context.newPage();
  let fonts=await readFile('wp-content/themes/iotnest/assets/src/scss/base/_fonts.scss','utf8');
  for(const name of ['inter-latin','inter-latin-ext']) fonts=fonts.replace(`../fonts/${name}.woff2`,`data:font/woff2;base64,${(await readFile(`wp-content/themes/iotnest/assets/fonts/${name}.woff2`)).toString('base64')}`);
  await reference.route('https://fonts.googleapis.com/**',route=>route.fulfill({contentType:'text/css',body:fonts}));
  for(const p of [page,reference]) {await p.setViewportSize({width,height:1000});await p.emulateMedia({reducedMotion:'reduce'});}
  await reference.goto(process.env.IOTNEST_REFERENCE_URL+'/privacidade.html');await page.goto('/privacidade/');
  for(const p of [page,reference]) {await p.evaluate(()=>document.fonts.ready);await p.evaluate(()=>{document.body.replaceChildren(document.querySelector('main'));scrollTo(0,0);});}
  const expected=await reference.locator('main').boundingBox(),actual=await page.locator('main').boundingBox();
  await reference.locator('main').screenshot({path:testInfo.outputPath('reference.png')});await page.locator('main').screenshot({path:testInfo.outputPath('wordpress.png')});
  await writeFile(testInfo.outputPath('geometry.json'),JSON.stringify({width,expected,actual},null,2));
  expect(actual).toEqual(expected);
  await reference.close();
});

let visual;
for(const width of [360,390,768,1024,1440]) {
  for(const name of ['blog','cases','artigo-1','case-plasticos-nordeste']) test(`T24 páginas internas: ${name} em ${width}px`,async({page,context},testInfo)=>{
    test.setTimeout(60000); test.skip(!process.env.IOTNEST_REFERENCE_URL);
    if(!visual) visual=fixture('visual').visual;
    const reference=await context.newPage();
    let fonts=await readFile('wp-content/themes/iotnest/assets/src/scss/base/_fonts.scss','utf8');
    for(const font of ['inter-latin','inter-latin-ext']) fonts=fonts.replace(`../fonts/${font}.woff2`,`data:font/woff2;base64,${(await readFile(`wp-content/themes/iotnest/assets/fonts/${font}.woff2`)).toString('base64')}`);
    await reference.route('https://fonts.googleapis.com/**',route=>route.fulfill({contentType:'text/css',body:fonts}));
    for(const p of [page,reference]) {await p.setViewportSize({width,height:1000});await p.emulateMedia({reducedMotion:'reduce'});}
    await reference.goto(process.env.IOTNEST_REFERENCE_URL+'/'+name+'.html');
    await page.goto(visual[name]?.url || '/'+name+'/');
    // Diferenças exigidas pela spec: tempo calculado, título nativo no breadcrumb e capa ausente sem arquivo.
    await reference.evaluate(({visual,name})=>{
      document.querySelector('.article__cover')?.remove();
      document.querySelectorAll('.case').forEach(card=>{
        const slug=card.querySelector('a')?.getAttribute('href')?.replace('.html','');
        if(slug?.startsWith('artigo') && visual[slug]) card.querySelector('.case__company').textContent=visual[slug].reading;
      });
      if(name.startsWith('artigo')) {
        const eyebrow=document.querySelector('.article__head .eyebrow');
        eyebrow.textContent=eyebrow.textContent.replace(/\d+ min de leitura/,visual[name].reading);
        document.querySelector('.legal__crumb > span:last-child').textContent=visual[name].title;
      }
    },{visual,name});
    for(const p of [page,reference]) {await p.evaluate(()=>document.fonts.ready);await p.evaluate(()=>{document.body.replaceChildren(document.querySelector('main'));scrollTo(0,0);});}
    const expected=await reference.locator('main').boundingBox(),actual=await page.locator('main').boundingBox();
    const a=await reference.locator('main').screenshot({path:testInfo.outputPath('reference.png')}),b=await page.locator('main').screenshot({path:testInfo.outputPath('wordpress.png')});
    const diff=await page.evaluate(async([a,b])=>{
      const read=async s=>{const i=new Image();i.src='data:image/png;base64,'+s;await i.decode();const c=document.createElement('canvas');c.width=i.width;c.height=i.height;const x=c.getContext('2d');x.drawImage(i,0,0);return{w:i.width,h:i.height,data:x.getImageData(0,0,i.width,i.height).data};};
      const [x,y]=await Promise.all([read(a),read(b)]);if(x.w!==y.w||x.h!==y.h)return{dimensions:false};let changed=0;
      for(let j=0;j<x.data.length;j+=4)if([0,1,2].some(k=>Math.abs(x.data[j+k]-y.data[j+k])>2))changed++;
      return{dimensions:true,changed,total:x.w*x.h};
    },[a.toString('base64'),b.toString('base64')]);
    await writeFile(testInfo.outputPath('comparison.json'),JSON.stringify({width,name,expected,actual,diff,normalization:['reading time calculated','native article breadcrumb','missing cover omitted']},null,2));
    expect(actual).toEqual(expected);expect(diff.changed).toBe(0);
    await reference.close();
  });
}
