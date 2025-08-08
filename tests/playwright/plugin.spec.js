const { test, expect } = require('@playwright/test');

/**
 * Plugin activation/deactivation tests
 * Migrated from tests/cypress/integration/activatePlugin.spec.js
 */
test.describe('CMB2 Plugin', () => {
  // Use authenticated state for all tests
  test.use({ storageState: 'tests/playwright/.auth/user.json' });

  test.beforeEach(async ({ page }) => {
    // Navigate to plugins page before each test
    await page.goto('/wp-admin/plugins.php');
    await expect(page).toHaveURL('/wp-admin/plugins.php');
  });

  test('Can be deactivated', async ({ page }) => {
    // Look for the deactivate link for CMB2
    const deactivateLink = page.locator('#deactivate-cmb2');
    
    // If CMB2 is active, deactivate it
    const isActive = await deactivateLink.isVisible({ timeout: 5000 });
    
    if (isActive) {
      await deactivateLink.click();
      
      // Wait for page to reload and verify activation link is visible
      await page.waitForLoadState('networkidle');
      await expect(page.locator('#activate-cmb2')).toBeVisible();
    } else {
      // If it's already deactivated, just verify the activate link is visible
      await expect(page.locator('#activate-cmb2')).toBeVisible();
    }
  });

  test('Can be activated', async ({ page }) => {
    // Look for the activate link for CMB2
    const activateLink = page.locator('#activate-cmb2');
    
    // If CMB2 is inactive, activate it
    const isInactive = await activateLink.isVisible({ timeout: 5000 });
    
    if (isInactive) {
      await activateLink.click();
      
      // Wait for page to reload and verify deactivation link is visible
      await page.waitForLoadState('networkidle');
      await expect(page.locator('#deactivate-cmb2')).toBeVisible();
    } else {
      // If it's already activated, just verify the deactivate link is visible
      await expect(page.locator('#deactivate-cmb2')).toBeVisible();
    }
  });

  test('Plugin information is displayed correctly', async ({ page }) => {
    // Additional test to verify plugin details are shown
    const pluginRow = page.locator('tr[data-slug="cmb2"]');
    await expect(pluginRow).toBeVisible();
    
    // Check that plugin name and description are present
    await expect(pluginRow.locator('.plugin-title strong')).toContainText('CMB2');
    await expect(pluginRow.locator('.plugin-description')).toBeVisible();
  });
});