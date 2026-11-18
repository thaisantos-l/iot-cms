import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import { rename } from 'node:fs/promises';
import path from 'node:path';

test.beforeAll(() => {
  if (!process.env.IOTNEST_TEST_WP_ROOT || !process.env.IOTNEST_TEST_URL) {
    throw new Error('Execute npm run test:integration para usar uma instalação descartável.');
  }
});

const plugin = state => execFileSync(process.env.PHP_BIN || 'php', ['tests/php/plugin-state.php', state], { stdio: 'pipe' });

async function checkPage(page) {
  const errors = [];
  const failed = [];
  page.on('pageerror', error => errors.push(error.message));
  page.on('requestfailed', request => failed.push(request.url()));
  page.on('response', response => { if (response.status() >= 400) failed.push(`${response.status()} ${response.url()}`); });
  const response = await page.goto('/');
  await page.waitForLoadState('networkidle');
  expect(response.status()).toBe(200);
  await expect(page.locator('main#main')).toHaveCount(1);
  await expect(page.locator('section')).toHaveCount(0);
  expect(await page.content()).not.toMatch(/Fatal error|Warning:|Notice:|Deprecated:/);
  expect(errors).toEqual([]);
  expect(failed).toEqual([]);
}

test('T01: home sem campos, assets compilados e hooks nativos', async ({ page }) => {
  await checkPage(page);
  await expect(page.locator('link[id="iotnest-app-css"]')).toHaveCount(1);
  await expect(page.locator('script[id="iotnest-app-js"]')).toHaveCount(1);
  await expect(page.locator('script[id="iotnest-home-js"]')).toHaveCount(1);
  await expect(page).toHaveTitle(/iotnest QA/);
  await page.keyboard.press('Tab');
  await expect(page.locator('.skip-link')).toBeFocused();
  await page.keyboard.press('Enter');
  await expect(page.locator('main')).toBeFocused();
});

test('T01: plugin desativado preserva fallback e mostra aviso só no admin', async ({ page }) => {
  plugin('off');
  try {
    await checkPage(page);
    await expect(page.locator('body')).not.toContainText('Ative o plugin');
    await page.goto('/wp-login.php');
    await expect(page.locator('#user_login')).toBeFocused();
    await page.locator('#user_login').fill('iotnest-qa');
    await page.locator('#user_pass').fill(process.env.IOTNEST_TEST_ADMIN_PASSWORD);
    await Promise.all([page.waitForURL(/\/wp-admin\/$/), page.locator('#wp-submit').click()]);
    await expect(page.locator('.notice-warning')).toContainText('Ative o plugin iotnest-core');
  } finally {
    plugin('on');
  }
});

test('manifesto ausente não gera fatal nem requisições inexistentes', async ({ page }) => {
  const manifest = path.join(process.env.IOTNEST_TEST_WP_ROOT, 'wp-content/themes/iotnest/assets/dist/manifest.json');
  await rename(manifest, `${manifest}.test-backup`);
  try {
    await checkPage(page);
    await expect(page.locator('script[id^="iotnest-"]')).toHaveCount(0);
    await expect(page.locator('link[id="iotnest-app-css"]')).toHaveCount(0);
  } finally {
    await rename(`${manifest}.test-backup`, manifest);
  }
});
