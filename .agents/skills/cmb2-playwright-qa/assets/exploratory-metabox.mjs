import fs from 'node:fs';
import path from 'node:path';
import { chromium } from '@playwright/test';
import dotenv from 'dotenv';

dotenv.config({
  path: path.resolve(process.cwd(), '.env.local'),
  override: false,
  quiet: true,
});

const baseURL = process.env.WP_BASE_URL || 'http://localhost:2623';
const authFile = path.resolve(process.cwd(), 'tests/playwright/.auth/user.json');
const runDir = path.resolve(process.cwd(), 'tests/playwright/.qa/example-metabox');

if (!fs.existsSync(authFile)) {
  throw new Error('Refresh Playwright auth state before running this script.');
}

await fs.promises.mkdir(runDir, { recursive: true });

const browser = await chromium.launch();
const context = await browser.newContext({ storageState: authFile });
const page = await context.newPage();
const metabox = page.locator('#cmb2_integration_tests_default_closed');
const field = page.locator('#cmb2_integration_tests_field_text');

try {
  await page.goto(new URL('/wp-admin/post-new.php', baseURL).toString(), {
    waitUntil: 'domcontentloaded',
  });
  await metabox.waitFor({ state: 'visible' });
  await metabox.locator('.hndle').waitFor({ state: 'visible' });
  await page.screenshot({
    path: path.join(runDir, 'claim-01-text-field-hidden-before.png'),
    fullPage: true,
  });

  await metabox.locator('button.handlediv').click();
  await field.waitFor({ state: 'visible' });
  await field.fill('Exploratory value');
  await page.screenshot({
    path: path.join(runDir, 'claim-01-text-field-filled-after.png'),
    fullPage: true,
  });
} catch (error) {
  await page.screenshot({
    path: path.join(runDir, 'failure-final-state.png'),
    fullPage: true,
  });
  throw error;
} finally {
  await browser.close();
}
