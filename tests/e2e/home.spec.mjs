import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { readFile, writeFile } from 'node:fs/promises';

const fixture = (action, patch = {}) => execFileSync(process.env.PHP_BIN || 'php', ['tests/php/home-fixture.php', action, JSON.stringify(patch)], { encoding: 'utf8' });
test.beforeAll(() => fixture('create'));
test.beforeEach(() => fixture('restore'));
test.afterAll(() => fixture('delete'));

test('home importada: HTML, CTAs, ordem, títulos apagados e nenhum exemplo de blog/cases', async ({ page }) => {
  await page.goto('/');
  await expect(page.locator('main > section')).toHaveCount(18);
  await expect(page.locator('h1')).toHaveText('Use a ciência para transformar dados em produtividade');
  await expect(page.locator('#cases, #blog, [data-contact-form]')).toHaveCount(0);
  await expect(page.locator('[data-step-item]')).toHaveCount(4);
  await expect(page.locator('img[src=""], a[href="#"]')).toHaveCount(0);
  fixture('mutate', { hero: { enabled: true, title: '' }, how_it_works: { enabled: false, title: 'Hidden' }, order: ['faq', 'solution', 'hero', 'how_it_works'] });
  await page.reload();
  await expect(page.locator('main > section')).toHaveCount(2);
  await expect(page.locator('main > section').first()).toHaveAttribute('id', 'faq');
  await expect(page.locator('#topo, #como-funciona')).toHaveCount(0);
  await expect(page.locator('a[href$="#como-funciona"]')).toHaveCount(0);
  await expect(page.locator('h1')).toHaveClass('screen-reader-text');
});

test('home sem JavaScript: todas as etapas e módulos permanecem legíveis', async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false, viewport: { width: 390, height: 844 } });
  try {
    const page = await context.newPage();
    await page.goto(process.env.IOTNEST_TEST_URL);
    await expect(page.getByRole('heading', { name: 'Captura e processamento de dados', exact: true })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Plataforma e aplicativo', exact: true })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Paradas', exact: true })).toBeVisible();
    await page.locator('.faq__item').nth(1).locator('summary').click();
    await expect(page.locator('.faq__item').nth(1)).toHaveAttribute('open', '');
  } finally { await context.close(); }
});

for (const width of [360, 390, 768, 1024, 1440]) {
  test(`T24 home: composição institucional em ${width}px`, async ({ page, context }, testInfo) => {
    test.setTimeout(120000);
    test.skip(!process.env.IOTNEST_REFERENCE_URL, 'Referência local necessária para T24.');
    await context.setOffline(false);
    const reference = await context.newPage();
    for (const current of [page, reference]) {
      await current.setViewportSize({ width, height: 1000 });
      await current.emulateMedia({ reducedMotion: 'reduce' });
      await current.route('**/*.{webm,mp4}', route => route.fulfill({ status: 204 }));
    }
    let fonts = await readFile('wp-content/themes/iotnest/assets/src/scss/base/_fonts.scss', 'utf8');
    for (const name of ['inter-latin', 'inter-latin-ext']) {
      const bytes = await readFile(`wp-content/themes/iotnest/assets/fonts/${name}.woff2`);
      fonts = fonts.replace(`../fonts/${name}.woff2`, `data:font/woff2;base64,${bytes.toString('base64')}`);
    }
    await reference.route('https://fonts.googleapis.com/**', route => route.fulfill({ contentType: 'text/css', body: fonts }));
    // Compara a mesma imagem-fonte: o produto mantém srcset; o diff não compara compressões distintas.
    await page.route(/\/uploads\/.*-\d+x\d+\.webp(?:\?.*)?$/, async route => {
      const response = await route.fetch({ url: route.request().url().replace(/-\d+x\d+(?=\.webp)/, '') });
      await route.fulfill({ response });
    });
    await reference.goto(process.env.IOTNEST_REFERENCE_URL);
    await page.goto('/');
    for (const current of [page, reference]) {
      await current.evaluate(() => document.fonts.ready);
      await current.evaluate(() => { window.iotnestSections = [...document.querySelector('main').children].filter(node => node.matches('section')); });
    }
    const report = [];
    const selectors = ['#topo', '.clients', '#solucao', '#hardware', '#ia', '.dada-full', '#apontamento', '#energia', '#relatorios', '#mess', '#calculadora', '#comparativo', '#como-funciona', '#depoimentos', '#videos', '#sobre', '#faq'];
    for (const [index, selector] of selectors.entries()) {
      for (const current of [page, reference]) {
        await current.evaluate(selector => {
          const section = window.iotnestSections.find(node => node.matches(selector));
          document.body.replaceChildren(section);
          scrollTo(0, 0);
          section.querySelectorAll('img').forEach(img => img.loading = 'eager');
        }, selector);
        await current.locator(`${selector} img`).evaluateAll(nodes => Promise.all(nodes.map(img => img.decode().catch(() => {}))));
      }
      const expected = await reference.locator(selector).boundingBox();
      const actual = await page.locator(selector).boundingBox();
      const record = { selector, expected, actual, heightDifference: actual.height - expected.height, widthDifference: actual.width - expected.width };
      report.push(record);
      await reference.locator(selector).screenshot({ path: testInfo.outputPath(`${index}-reference.png`), animations: 'disabled' });
      await page.locator(selector).screenshot({ path: testInfo.outputPath(`${index}-wordpress.png`), animations: 'disabled' });
    }
    await writeFile(testInfo.outputPath('composition.json'), JSON.stringify({ width, report, provisional: ['controls/interactions: stage 06', 'contact form: stage 07', 'legal pages remain drafts'] }, null, 2));
    await reference.close();
    expect(report.filter(row => Math.abs(row.heightDifference) > 1 || Math.abs(row.widthDifference) > 1)).toEqual([]);
  });
}
