import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { readFile, writeFile } from 'node:fs/promises';

const fixture = (action, patch = {}) => execFileSync(process.env.PHP_BIN || 'php', ['tests/php/home-fixture.php', action, JSON.stringify(patch)], { encoding: 'utf8' });
test.beforeAll(() => fixture('create'));
test.beforeEach(() => fixture('restore'));
test.afterAll(() => fixture('delete'));

async function comparePixels(page, expected, actual, provisionalRegions, rasterEdges) {
  return page.evaluate(async ([a, b, regions, edges]) => {
    const read = async value => {
      const image = new Image(); image.src = `data:image/png;base64,${value}`; await image.decode();
      const canvas = document.createElement('canvas'); canvas.width = image.width; canvas.height = image.height;
      const ctx = canvas.getContext('2d'); ctx.drawImage(image, 0, 0);
      return { width: image.width, height: image.height, data: ctx.getImageData(0, 0, image.width, image.height).data };
    };
    const [x, y] = await Promise.all([read(a), read(b)]);
    if (x.width !== y.width || x.height !== y.height) return { outsideProvisional: -1, dimensions: [x.width, x.height, y.width, y.height] };
    let changed = 0, outsideProvisional = 0, rasterization = 0, rasterizationMaxChannel = 0;
    for (let i = 0; i < x.data.length; i += 4) {
      if (![0, 1, 2, 3].some(k => Math.abs(x.data[i + k] - y.data[i + k]) > 2)) continue;
      changed++;
      const px = i / 4 % x.width, py = Math.floor(i / 4 / x.width);
      if (regions.some(r => px >= r.x && px < r.x + r.width && py >= r.y && py < r.y + r.height)) continue;
      const onCurve = edges.some(r => {
        const xx = px + .5, yy = py + .5;
        const left = xx < r.x + r.radius, right = xx > r.x + r.width - r.radius;
        const top = yy < r.y + r.radius, bottom = yy > r.y + r.height - r.radius;
        if (!(left || right) || !(top || bottom)) return false;
        const cx = left ? r.x + r.radius : r.x + r.width - r.radius;
        const cy = top ? r.y + r.radius : r.y + r.height - r.radius;
        return Math.abs(Math.hypot(xx - cx, yy - cy) - r.radius) <= 1;
      });
      if (onCurve) {
        rasterization++;
        rasterizationMaxChannel = Math.max(rasterizationMaxChannel, ...[0, 1, 2].map(k => Math.abs(x.data[i + k] - y.data[i + k])));
      } else outsideProvisional++;
    }
    return { changed, outsideProvisional, rasterization, rasterizationMaxChannel, total: x.width * x.height, provisionalRegions: regions, rasterEdges: edges };
  }, [expected.toString('base64'), actual.toString('base64'), provisionalRegions, rasterEdges]);
}

test('home importada: HTML, CTAs, ordem, títulos apagados e nenhum exemplo de blog/cases', async ({ page }) => {
  await page.goto('/');
  await expect(page.locator('main > section')).toHaveCount(17);
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

test('home: textos longos e imagem atrasada preservam leitura e espaço reservado', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.goto('/');
  const initialWidth = await page.evaluate(() => document.documentElement.scrollWidth);
  fixture('mutate', { hero: { enabled: true, title: 'Dados industriais para decisões de produção. '.repeat(12), description: 'Conteúdo editorial longo e legível. '.repeat(15) } });
  await page.route('**/*.webp', async route => { await new Promise(resolve => setTimeout(resolve, 1500)); await route.continue(); });
  await page.goto('/', { waitUntil: 'domcontentloaded' });
  await page.evaluate(() => document.fonts.ready);
  // O carrossel original alcança 404px no viewport de 390px; texto longo não deve ampliar isso.
  expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(initialWidth);
  const title = await page.locator('h1').boundingBox(), hero = await page.locator('#topo').boundingBox();
  expect(title.y + title.height).toBeLessThanOrEqual(hero.y + hero.height);
  const media = page.locator('#solucao figure img');
  await media.scrollIntoViewIfNeeded();
  const initial = await media.boundingBox();
  await expect.poll(() => media.evaluate(img => img.complete && img.naturalWidth > 0)).toBe(true);
  expect(await media.boundingBox()).toEqual(initial);
});

test('importação administrativa: nonce nativo e relatório de reexecução pelo navegador', async ({ page }) => {
  await page.goto('/wp-login.php');
  await expect(page.locator('#user_login')).toBeFocused();
  await page.locator('#user_login').fill('iotnest-qa');
  await page.locator('#user_pass').fill(process.env.IOTNEST_TEST_ADMIN_PASSWORD);
  await Promise.all([page.waitForURL(/\/wp-admin\/$/), page.locator('#wp-submit').click()]);
  await page.goto('/wp-admin/tools.php?page=iotnest-import');
  await expect(page.locator('form').first().locator('[name="_wpnonce"]')).toHaveCount(1);
  await Promise.all([page.waitForNavigation(), page.getByRole('button', { name: 'Importar para revisão', exact: true }).click()]);
  await expect(page.locator('.notice')).toContainText('Importação concluída');
  await expect(page.getByRole('heading', { name: 'Reutilizados', exact: true })).toBeVisible();
  await expect(page.locator('.wrap')).toContainText('page:home');
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
    await reference.goto(process.env.IOTNEST_REFERENCE_URL);
    await page.goto('/');
    for (const current of [page, reference]) {
      await current.evaluate(() => document.fonts.ready);
      await current.evaluate(() => { window.iotnestSections = [...document.querySelector('main').children].filter(node => node.matches('section')); });
    }
    const report = [];
    const selectors = ['#topo', '.clients', '#solucao', '#hardware', '#ia', '.dada-full', '#apontamento', '#energia', '#relatorios', '#mess', '#calculadora', '#comparativo', '#como-funciona', '#videos', '#sobre', '#faq'];
    for (const [index, selector] of selectors.entries()) {
      for (const current of [page, reference]) {
        await current.evaluate(selector => {
          const section = window.iotnestSections.find(node => node.matches(selector));
          document.body.replaceChildren(section);
          scrollTo(0, 0);
          section.querySelectorAll('img').forEach(img => {
            img.loading = 'eager';
            // Mesmos bytes e densidade intrínseca no diff; srcset permanece no produto.
            if (img.src.includes('/uploads/')) {
              img.removeAttribute('srcset'); img.removeAttribute('sizes');
              img.src = img.src.replace(/-\d+x\d+(?=\.webp)/, '');
            }
          });
        }, selector);
        await current.locator(`${selector} img`).evaluateAll(nodes => Promise.all(nodes.map(img => img.decode().catch(() => {}))));
      }
      const expected = await reference.locator(selector).boundingBox();
      const actual = await page.locator(selector).boundingBox();
      const provisionalRegions = [], rasterEdges = [];
      if (selector === '#como-funciona') {
        const inspect = current => current.locator('.how__media').first().evaluate(el => {
          const style = getComputedStyle(el), img = el.querySelector('img');
          return { rect: el.getBoundingClientRect().toJSON(), image: img.getBoundingClientRect().toJSON(), style: Object.fromEntries([...style].map(k => [k, style[k]])) };
        });
        const expectedMedia = await inspect(reference), actualMedia = await inspect(page);
        expect(actualMedia.rect).toEqual(expectedMedia.rect);
        expect(actualMedia.image).toEqual(expectedMedia.image);
        for (const property of ['border-radius', 'background-color', 'overflow', 'transform', 'opacity']) {
          expect(actualMedia.style[property]).toEqual(expectedMedia.style[property]);
        }
        rasterEdges.push({ x: actualMedia.rect.x - actual.x, y: actualMedia.rect.y - actual.y,
          width: actualMedia.rect.width, height: actualMedia.rect.height, radius: 16 });
        await writeFile(testInfo.outputPath('how-geometry.json'), JSON.stringify({reference: expectedMedia, wordpress: actualMedia}, null, 2));
      }
      if (selector === '#como-funciona') {
        // Etapa 04 exige controles ainda sem JS desabilitados. Única região provisória do diff;
        // a etapa 06 deve remover essa exceção e validar a interação completa.
        const button = await page.locator('[data-how-next]').first().boundingBox();
        if (button) {
          const x = Math.floor(button.x - actual.x), y = Math.floor(button.y - actual.y);
          provisionalRegions.push({ x, y, width: Math.ceil(button.x - actual.x + button.width) - x, height: Math.ceil(button.y - actual.y + button.height) - y });
        }
      }
      const expectedImage = await reference.locator(selector).screenshot({ path: testInfo.outputPath(`${index}-reference.png`), animations: 'disabled' });
      const actualImage = await page.locator(selector).screenshot({ path: testInfo.outputPath(`${index}-wordpress.png`), animations: 'disabled' });
      const pixels = await comparePixels(page, expectedImage, actualImage, provisionalRegions, rasterEdges);
      expect(pixels.rasterization || 0).toBeLessThanOrEqual(160);
      expect(pixels.rasterizationMaxChannel || 0).toBeLessThanOrEqual(50);
      const record = { selector, expected, actual, heightDifference: actual.height - expected.height, widthDifference: actual.width - expected.width, pixels };
      report.push(record);
    }
    await writeFile(testInfo.outputPath('composition.json'), JSON.stringify({ width, report, provisional: ['controls/interactions: stage 06', 'contact form: stage 07', 'legal pages remain drafts'] }, null, 2));
    await reference.close();
    expect(report.filter(row => Math.abs(row.heightDifference) > 1 || Math.abs(row.widthDifference) > 1)).toEqual([]);
    expect(report.filter(row => row.pixels.outsideProvisional !== 0)).toEqual([]);
  });
}
