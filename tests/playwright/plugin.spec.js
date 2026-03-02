const { test, expect } = require('@playwright/test');

/**
 * Plugin activation/deactivation tests
 * Migrated from tests/cypress/integration/activatePlugin.spec.js
 */
test.describe('CMB2 Plugin', () => {
  // Auth state is provided by the setup project in playwright.config.js

  // These tests must run in order (deactivate → activate → verify)
  test.describe.configure({ mode: 'serial' });

  test('Can be deactivated', async ({ page }) => {
    await page.goto('/wp-admin/plugins.php');
    await expect(page).toHaveURL(/\/wp-admin\/plugins\.php/);

    // Look for the deactivate link for CMB2
    const deactivateLink = page.locator('#deactivate-cmb2');
    const isActive = await deactivateLink.isVisible({ timeout: 5000 }).catch(() => false);

    if (isActive) {
      await deactivateLink.click();
      await page.waitForLoadState('domcontentloaded');
      await expect(page.locator('#activate-cmb2')).toBeVisible();
    } else {
      // Already deactivated
      await expect(page.locator('#activate-cmb2')).toBeVisible();
    }
  });

  test('Can be activated', async ({ page }) => {
    await page.goto('/wp-admin/plugins.php');
    await expect(page).toHaveURL(/\/wp-admin\/plugins\.php/);

    // Look for the activate link for CMB2
    const activateLink = page.locator('#activate-cmb2');
    const isInactive = await activateLink.isVisible({ timeout: 5000 }).catch(() => false);

    if (isInactive) {
      await activateLink.click();
      await page.waitForLoadState('domcontentloaded');
      await expect(page.locator('#deactivate-cmb2')).toBeVisible();
    } else {
      // Already activated
      await expect(page.locator('#deactivate-cmb2')).toBeVisible();
    }
  });

  test('Plugin information is displayed correctly', async ({ page }) => {
    await page.goto('/wp-admin/plugins.php');
    await expect(page).toHaveURL(/\/wp-admin\/plugins\.php/);

    const pluginRow = page.locator('tr[data-slug="cmb2"]');
    await expect(pluginRow).toBeVisible();

    // Check that plugin name and description are present
    await expect(pluginRow.locator('.plugin-title strong')).toContainText('CMB2');
    await expect(pluginRow.locator('.plugin-description')).toBeVisible();
  });
});
