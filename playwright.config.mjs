import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,
  workers: 1,
  retries: 0,
  reporter: 'list',
  use: {
    baseURL: process.env.IOTNEST_TEST_URL,
    browserName: process.env.IOTNEST_BROWSER || 'chromium',
    channel: process.env.IOTNEST_BROWSER_CHANNEL || undefined,
    headless: true,
    trace: 'retain-on-failure',
  },
});
