import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';

const fixture = (action, id = '') => {
  const result = execFileSync(process.env.PHP_BIN || 'php', ['tests/php/editor-fixture.php', action, String(id)], { encoding: 'utf8' });
  return result ? JSON.parse(result) : null;
};
const field = (root, key) => root.locator(`[data-key="${key}"] > [data-input]`);
async function login(page) {
  await page.goto('/wp-login.php');
  await page.locator('#user_login').fill('iotnest-qa');
  await page.locator('#user_pass').fill(process.env.IOTNEST_TEST_ADMIN_PASSWORD);
  await Promise.all([page.waitForURL(/\/wp-admin\/$/), page.locator('#wp-submit').click()]);
}
async function save(page) {
  await Promise.all([page.waitForURL(/message=1/), page.locator('#publish').click()]);
  await expect(page.locator('.notice-error:visible')).toHaveCount(0);
}

test('campos nativos: teclado, repetidor, gravação, limpeza e preview isolado', async ({ page }) => {
  const { id } = fixture('create');
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  try {
    await login(page);
    await page.goto(`/wp-admin/post.php?post=${id}&action=edit`);
    await expect(page.locator('#content')).toHaveCount(0);
    const hero = page.locator('[data-meta="_iotnest_home_hero"]');
    const faq = page.locator('[data-meta="_iotnest_home_faq"]');
    await hero.locator('summary').click();
    await field(hero, 'title').fill('Título gravado');
    await faq.locator('summary').click();
    await field(faq, 'enabled').check();
    await field(faq, 'title').fill('Perguntas');
    const repeater = faq.locator('[data-key="items"] > fieldset');
    const rows = repeater.locator(':scope > [data-items] > [data-item]');
    await repeater.locator(':scope > [data-add]').focus();
    await page.keyboard.press('Enter');
    await expect(field(rows.nth(0), 'question')).toBeFocused();
    await field(rows.nth(0), 'question').fill('Primeira');
    await field(rows.nth(0), 'answer').fill('Resposta um');
    await repeater.locator(':scope > [data-add]').click();
    await field(rows.nth(1), 'question').fill('Segunda');
    await field(rows.nth(1), 'answer').fill('Resposta dois');
    const identifiers = await rows.locator('[data-kind="id"] input').evaluateAll(nodes => nodes.map(node => node.value));
    await rows.nth(1).locator('[data-up]').focus();
    await page.keyboard.press('Enter');
    await expect(rows.nth(0).locator('[data-up]')).toBeFocused();
    await expect(field(rows.nth(0), 'question')).toHaveValue('Segunda');
    await save(page);
    let state = fixture('read', id);
    expect(state.meta._iotnest_home_faq.items.map(item => item.id)).toEqual(identifiers.reverse());
    expect(state.meta._iotnest_home_hero.description).toBe('Descrição preservada');
    await hero.locator('summary').click();
    await field(hero, 'title').fill('Somente autosave');
    await page.evaluate(() => window.wp.heartbeat.connectNow());
    await expect.poll(() => fixture('read', id).autosave?.title).toBe('Somente autosave');
    expect(fixture('read', id).meta._iotnest_home_hero.title).toBe('Título gravado');
    await field(hero, 'title').fill('Somente preview');
    const popupPromise = page.waitForEvent('popup');
    await page.locator('#post-preview').click();
    const preview = await popupPromise;
    await preview.waitForLoadState('networkidle');
    state = fixture('read', id);
    expect(state.meta._iotnest_home_hero.title).toBe('Título gravado');
    expect(state.autosave.title).toBe('Somente preview');
    await preview.close();
    await field(hero, 'title').fill('Segundo preview');
    const secondPromise = page.waitForEvent('popup');
    await page.locator('#post-preview').click();
    const second = await secondPromise;
    await second.waitForLoadState('networkidle');
    expect(fixture('read', id).autosave.title).toBe('Segundo preview');
    expect(fixture('read', id).meta._iotnest_home_hero.title).toBe('Título gravado');
    await second.close();
    await field(hero, 'title').fill('');
    await save(page);
    expect(fixture('read', id).meta._iotnest_home_hero.title).toBe('');
    expect(errors).toEqual([]);
  } finally { fixture('delete', id); }
});

test('configurações operacionais: opção válida persiste e ID inválido preserva estado', async ({ page }) => {
  await login(page);
  await page.goto('/wp-admin/options-general.php?page=iotnest');
  const settings = page.locator('[data-settings]');
  await field(settings, 'phone').fill('Contato de QA');
  await field(settings, 'ga4_id').fill('G-QATEST');
  await Promise.all([page.waitForURL(/settings-updated=true/), page.locator('#submit').click()]);
  await expect(field(settings, 'phone')).toHaveValue('Contato de QA');
  await field(settings, 'phone').fill('Não salvar');
  await field(settings, 'ga4_id').fill('<script>invalid</script>');
  await Promise.all([page.waitForNavigation(), page.locator('#submit').click()]);
  await expect(page.locator('.notice-error:visible')).toContainText('Identificador de integração inválido');
  await expect(field(settings, 'phone')).toHaveValue('Contato de QA');
});
