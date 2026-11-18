import {test,expect} from '@playwright/test';
import {execFileSync} from 'node:child_process';
const php=(script,action,payload={})=>execFileSync(process.env.PHP_BIN||'php',[`tests/php/${script}.php`,action,JSON.stringify(payload)],{encoding:'utf8'});
let fixture;
test.beforeAll(()=>{php('publication-fixture','create');fixture=JSON.parse(php('publication-fixture','many'));});
test.afterAll(()=>{php('seo-fixture','deactivate');php('publication-fixture','delete');});
test('Yoast ativo/desativado: uma fonte de metadados, breadcrumbs iguais, 404 e sitemap público',async({page,request})=>{
  test.setTimeout(120000);const urls=['/','/blog/','/cases/','/privacidade/'];const before=[];
  for(const url of urls){await page.goto(url);await expect(page.locator('head title')).toHaveCount(1);before.push(await page.locator('.legal__crumb').count()?await page.locator('.legal__crumb').innerHTML():null);}
  php('seo-fixture','activate');php('seo-fixture','flush');
  for(const [index,url]of urls.entries()){await page.goto(url);await expect(page.locator('head title')).toHaveCount(1);await expect(page.locator('link[rel=canonical]')).toHaveCount(1);await expect(page.locator('meta[property="og:title"]')).toHaveCount(1);await expect(page.locator('script.yoast-schema-graph')).toHaveCount(1);if(before[index]!==null)expect(await page.locator('.legal__crumb').innerHTML()).toBe(before[index]);}
  const missing=await page.goto('/qa-never-published-route/');expect(missing.status()).toBe(404);await expect(page.locator('meta[name=robots]')).toHaveAttribute('content',/noindex/);
  const id=fixture.posts.post.password;await page.goto(`/?p=${id}`);await expect(page.locator('meta[name=robots]')).toHaveAttribute('content',/noindex/);
  const index=await request.get('/sitemap_index.xml');expect(index.status()).toBe(200);const xml=await index.text();expect(xml).toContain('post-sitemap.xml');
  const sitemap=await request.get('/post-sitemap.xml');expect(sitemap.status()).toBe(200);const text=await sitemap.text();expect(text).not.toContain('qa-post-password');expect(text).not.toContain('qa-post-draft');expect(text).toContain('qa-post-0');
  php('seo-fixture','deactivate');await page.goto('/blog/');await expect(page.locator('head title')).toHaveCount(1);await expect(page.locator('script.yoast-schema-graph')).toHaveCount(0);expect(await page.locator('.legal__crumb').innerHTML()).toBe(before[1]);
});

test('análise real do Yoast recebe campos habilitados e acompanha edição antes de salvar',async({page})=>{
  test.setTimeout(120000);php('seo-fixture','activate');const create=execFileSync(process.env.PHP_BIN||'php',['tests/php/editor-fixture.php','create'],{encoding:'utf8'});const {id}=JSON.parse(create);
  try{
    await page.goto('/wp-login.php');await expect(page.locator('#user_login')).toBeFocused();await page.locator('#user_login').fill('iotnest-qa');await page.locator('#user_pass').fill(process.env.IOTNEST_TEST_ADMIN_PASSWORD);await Promise.all([page.waitForURL(/\/wp-admin\/$/),page.locator('#wp-submit').click()]);
    await page.goto(`/wp-admin/post.php?post=${id}&action=edit`);await page.waitForFunction(()=>window.YoastSEO?.app?.registerModification);
    await page.evaluate(()=>{const app=window.YoastSEO.app;app.registerPlugin('iotnest-qa-observer',{status:'ready'});app.registerModification('content',data=>{window.iotnestQaAnalysis=data;return data;},'iotnest-qa-observer',99);app.pluginReloaded('iotnest-qa-observer');});
    await expect.poll(()=>page.evaluate(()=>window.iotnestQaAnalysis),{timeout:30000}).toContain('Descrição preservada');
    const hero=page.locator('[data-meta="_iotnest_home_hero"]');await hero.locator('summary').click();const description=hero.locator('[data-key="description"] > [data-input]');await description.fill('Texto exclusivo da análise antes de salvar');await expect.poll(()=>page.evaluate(()=>window.iotnestQaAnalysis),{timeout:15000}).toContain('Texto exclusivo da análise antes de salvar');
    await hero.locator('[data-key="enabled"] input').uncheck();await expect.poll(()=>page.evaluate(()=>window.iotnestQaAnalysis),{timeout:15000}).not.toContain('Texto exclusivo da análise antes de salvar');
  }finally{execFileSync(process.env.PHP_BIN||'php',['tests/php/editor-fixture.php','delete',String(id)]);php('seo-fixture','deactivate');}
});
