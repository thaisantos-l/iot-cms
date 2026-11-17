import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { readFile, writeFile } from 'node:fs/promises';

const fixture = (action, patch = {}) => {
  const data = execFileSync(process.env.PHP_BIN || 'php', ['tests/php/navigation-fixture.php', action, JSON.stringify(patch)], { encoding: 'utf8' });
  return data ? JSON.parse(data) : null;
};

test.beforeEach(() => fixture('create'));
test.afterEach(() => fixture('delete'));

test('menus condicionais, destinos internos, identidade inicial e teclado', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/');
  const toggle = page.locator('.nav-toggle');
  await toggle.focus();
  await page.keyboard.press('Enter');
  await expect(page.locator('#menu')).toHaveClass(/is-open/);
  const dropdown = page.locator('.nav__dropdown-btn');
  await expect(dropdown).toBeVisible();
  await dropdown.focus();
  await expect(dropdown).toBeFocused();
  await page.keyboard.press('ArrowDown');
  await expect(page.getByRole('link', { name: 'Alertas', exact: true })).toBeFocused();
  await page.keyboard.press('Escape');
  await expect(dropdown).toBeFocused();
  await expect(dropdown).toHaveAttribute('aria-expanded', 'false');
  await page.keyboard.press('Escape');
  await expect(toggle).toBeFocused();
  await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  await page.goto('/?pagename=privacidade');
  await expect(page.locator('.nav a[href$="/#calculadora"]')).toHaveCount(1);
  fixture('mutate', { case_status: 'draft', sections: Object.fromEntries(['solution', 'ai', 'assistant', 'defects', 'energy', 'reports'].map(key => [key, { enabled: false, title: 'QA' }])) });
  await page.reload();
  await expect(page.locator('.nav__dropdown')).toHaveCount(0);
  await expect(page.locator('.nav').getByText('Cases', { exact: true })).toHaveCount(0);
  fixture('mutate', { colors: { brand: '#123456', green: '#345678', ink: '#223344', surface: '#fefefe', surface_soft: '#f0f1f2', surface_gray: '#eeeeee', slate: '#667788', danger: '#aa0000', border: '#445566', contrast: '#fffafa' } });
  await page.reload();
  expect(await page.evaluate(() => getComputedStyle(document.documentElement).getPropertyValue('--brand').trim())).toBe('#123456');
  expect(await page.locator('body').evaluate(el => getComputedStyle(el).color)).toBe('rgb(34, 51, 68)');
  expect(await page.locator('style#iotnest-app-inline-css').textContent()).toContain('--border:#445566');
  await expect(page.locator('.logo img')).toHaveCount(10);
  expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
});

test('navegação sem JavaScript e com movimento reduzido', async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false, viewport: { width: 360, height: 800 }, reducedMotion: 'reduce' });
  try {
    const page = await context.newPage();
    await page.goto(process.env.IOTNEST_TEST_URL);
    await expect(page.getByRole('link', { name: 'Alertas', exact: true })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Calculadora', exact: true })).toBeVisible();
    await expect(page.locator('.nav-toggle')).toBeHidden();
  } finally { await context.close(); }
});

test('logos extremos, arquivo removido, header estável e escala 200%', async ({ page, browser }) => {
  await page.setViewportSize({ width: 360, height: 800 });
  await page.goto('/');
  const original = await page.locator('.site-header').boundingBox();
  for (const shape of [[800, 60], [60, 800]]) {
    fixture('mutate', { logo_shape: shape });
    await page.reload();
    await page.evaluate(() => document.fonts.ready);
    await expect(page.locator('.custom-logo')).toHaveCount(2);
    expect(await page.locator('.site-header').boundingBox()).toEqual(original);
    for (const image of await page.locator('.custom-logo').all()) {
      const box = await image.boundingBox();
      expect(box.width).toBe(188);
      expect(box.height).toBe(47);
    }
  }
  fixture('mutate', { logo_missing: true });
  await page.reload();
  await expect(page.locator('.custom-logo')).toHaveCount(0);
  await expect(page.locator('.logo img')).toHaveCount(10);
  expect(await page.locator('.site-header').boundingBox()).toEqual(original);
  // 1440 px físicos a 200%: viewport CSS 720, DPR 2 (mesma condição de layout).
  const zoom = await browser.newContext({ viewport: { width: 720, height: 450 }, deviceScaleFactor: 2 });
  try {
    const zoomPage = await zoom.newPage();
    await zoomPage.goto(process.env.IOTNEST_TEST_URL);
    await zoomPage.locator('.nav-toggle').click();
    expect(await zoomPage.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    await expect(zoomPage.getByRole('link', { name: 'Calculadora', exact: true })).toBeVisible();
  } finally { await zoom.close(); }
});

test('rolagem conserva Lenis 1.1.20 e parâmetros da referência, com flag e movimento reduzido', async ({ page, browser }) => {
  await page.goto('/');
  await expect(page.locator('html')).toHaveClass(/lenis/);
  const config = await page.evaluate(() => {
    const options = window[Symbol.for('iotnest.scroll')].lenis.options;
    return { version: window.lenisVersion, duration: options.duration, wheel: options.wheelMultiplier, touch: options.touchMultiplier };
  });
  expect(config).toEqual({ version: '1.1.20', duration: 1.5, wheel: 0.85, touch: 1.4 });
  fixture('mutate', { settings: { lenis: false } });
  await page.reload();
  await expect(page.locator('html')).not.toHaveClass(/lenis/);
  fixture('mutate', { settings: { lenis: true } });
  const context = await browser.newContext({ reducedMotion: 'reduce' });
  try {
    const reduced = await context.newPage();
    await reduced.goto(process.env.IOTNEST_TEST_URL);
    await expect(reduced.locator('html')).not.toHaveClass(/lenis/);
  } finally { await context.close(); }
});

test('CLS da composição de navegação: três repetições, cache frio/quente e perfil declarado', async ({ browser }, testInfo) => {
  test.setTimeout(120000);
  const samples = [];
  for (const width of [390, 1440]) {
    const context = await browser.newContext({ viewport: { width, height: 900 } });
    try {
      const page = await context.newPage();
      const cdp = await context.newCDPSession(page);
      await cdp.send('Network.enable');
      await cdp.send('Network.emulateNetworkConditions', { offline: false, latency: 150, downloadThroughput: 1600000 / 8, uploadThroughput: 750000 / 8 });
      await cdp.send('Emulation.setCPUThrottlingRate', { rate: 4 });
      await page.addInitScript(() => {
        window.iotnestShifts = [];
        new PerformanceObserver(list => {
          for (const entry of list.getEntries()) if (!entry.hadRecentInput) window.iotnestShifts.push({ value: entry.value, time: entry.startTime });
        }).observe({ type: 'layout-shift', buffered: true });
      });
      for (let repetition = 1; repetition <= 3; repetition++) {
        await cdp.send('Network.clearBrowserCache');
        for (const cache of ['cold', 'warm']) {
          await page.goto(process.env.IOTNEST_TEST_URL, { waitUntil: 'domcontentloaded' });
          const initial = await page.locator('.site-header').boundingBox();
          await page.evaluate(() => document.fonts.ready);
          await page.waitForLoadState('networkidle');
          const loaded = await page.locator('.site-header').boundingBox();
          expect(loaded).toEqual(initial);
          if (width <= 1200) await page.locator('.nav-toggle').click();
          await page.locator('.nav__dropdown-btn').click();
          await page.keyboard.press('Escape');
          const shifts = await page.evaluate(() => window.iotnestShifts);
          let score = 0, sum = 0, start = 0, last = -Infinity;
          for (const shift of shifts) {
            if (shift.time - last > 1000 || shift.time - start > 5000) { start = shift.time; sum = 0; }
            sum += shift.value; last = shift.time; score = Math.max(score, sum);
          }
          samples.push({ width, repetition, cache, cls: score, shifts, header: loaded });
          await writeFile(testInfo.outputPath('navigation-cls.json'), JSON.stringify({ browser: browser.version(), network: { latencyMs: 150, downMbps: 1.6, upMbps: 0.75 }, cpuSlowdown: 4, samples }, null, 2));
          expect(score).toBeLessThanOrEqual(0.05);
        }
      }
    } finally { await context.close(); }
  }
});

async function pixels(page, first, second) {
  return page.evaluate(async ([a, b]) => {
    const read = async value => {
      const image = new Image();
      image.src = `data:image/png;base64,${value}`;
      await image.decode();
      const canvas = document.createElement('canvas');
      canvas.width = image.width; canvas.height = image.height;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(image, 0, 0);
      return { width: image.width, height: image.height, data: ctx.getImageData(0, 0, image.width, image.height).data };
    };
    const [x, y] = await Promise.all([read(a), read(b)]);
    if (x.width !== y.width || x.height !== y.height) return { dimensions: [x.width, x.height, y.width, y.height], changed: -1 };
    let changed = 0;
    for (let i = 0; i < x.data.length; i += 4) if ([0, 1, 2, 3].some(offset => Math.abs(x.data[i + offset] - y.data[i + offset]) > 2)) changed++;
    return { dimensions: [x.width, x.height], changed, total: x.width * x.height };
  }, [first.toString('base64'), second.toString('base64')]);
}

for (const width of [360, 390, 768, 1024, 1440]) {
  test(`T24 identidade: referência versus WordPress em ${width}px`, async ({ page, context }, testInfo) => {
    test.skip(!process.env.IOTNEST_REFERENCE_URL, 'Referência local ausente neste checkout; comparação exige iotnest-site original.');
    await page.setViewportSize({ width, height: 1000 });
    const reference = await context.newPage();
    await reference.setViewportSize({ width, height: 1000 });
    let fonts = (await readFile('wp-content/themes/iotnest/assets/src/scss/base/_fonts.scss', 'utf8')).replaceAll('../fonts/', `${process.env.IOTNEST_TEST_URL}/wp-content/themes/iotnest/assets/fonts/`);
    for (const name of ['inter-latin', 'inter-latin-ext']) {
      const bytes = await readFile(`wp-content/themes/iotnest/assets/fonts/${name}.woff2`);
      fonts = fonts.replace(`${process.env.IOTNEST_TEST_URL}/wp-content/themes/iotnest/assets/fonts/${name}.woff2`, `data:font/woff2;base64,${bytes.toString('base64')}`);
    }
    // Mesmos arquivos oficiais Inter v20 em ambos; apenas a resposta remota é local no QA.
    await reference.route('https://fonts.googleapis.com/**', route => route.fulfill({ contentType: 'text/css', body: fonts }));
    await reference.route('**/assets/video/**', route => route.fulfill({ status: 204 }));
    await reference.goto(process.env.IOTNEST_REFERENCE_URL);
    await page.goto('/');
    await Promise.all([page.evaluate(() => document.fonts.ready), reference.evaluate(() => document.fonts.ready)]);
    // Comparação de componentes: mesmo fundo/posição, sem conteúdo alheio sob bordas translúcidas.
    for (const current of [page, reference]) await current.evaluate(() => {
      window.iotnestCaptureFooter = document.querySelector('.footer');
      document.body.replaceChildren(document.querySelector('.site-header'));
      scrollTo(0, 0);
    });
    const report = [];
    for (const state of ['header', ...(width <= 1200 ? ['menu'] : ['dropdown']), 'footer']) {
      if (state === 'menu') {
        await page.locator('.nav-toggle').click();
        await reference.locator('.nav-toggle').click();
      }
      if (state === 'dropdown') {
        await page.locator('.nav__dropdown-btn').hover();
        await reference.locator('.nav__dropdown-btn').hover();
      }
      let selector = state === 'footer' ? '.footer' : '.site-header';
      if (state === 'menu') selector = '#menu';
      if (state === 'dropdown') selector = '.nav__panel';
      if (state === 'footer') {
        for (const current of [page, reference]) await current.evaluate(() => {
          document.body.replaceChildren(window.iotnestCaptureFooter);
          scrollTo(0, 0);
        });
        // Placeholders # são omitidos por contrato; igualar apenas esse estado editorial para o diff.
        await reference.locator('.reveal').evaluateAll(nodes => nodes.forEach(node => node.classList.add('is-in')));
        await reference.locator('.social li').evaluateAll(nodes => nodes.forEach(node => { if (node.querySelector('a').getAttribute('href') === '#') node.remove(); }));
      }
      await page.locator(selector).scrollIntoViewIfNeeded();
      await reference.locator(selector).scrollIntoViewIfNeeded();
      for (const current of [page, reference]) await current.locator(`${selector} img`).evaluateAll(nodes => Promise.all(nodes.map(image => image.decode().catch(() => {}))));
      const expected = await reference.locator(selector).screenshot({ animations: 'disabled' });
      const actual = await page.locator(selector).screenshot({ animations: 'disabled' });
      await writeFile(testInfo.outputPath(`${state}-reference.png`), expected);
      await writeFile(testInfo.outputPath(`${state}-wordpress.png`), actual);
      const result = await pixels(page, expected, actual);
      report.push({ state, ...result });
      await writeFile(testInfo.outputPath('comparison.json'), JSON.stringify(report, null, 2));
      expect(result.changed, `${state}: ${JSON.stringify(result)}`).toBe(0);
    }
    await reference.close();
  });
}
