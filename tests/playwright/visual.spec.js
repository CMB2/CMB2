const { test, expect } = require('@playwright/test');

/**
 * Visual regression tests for CMB2
 * Tests the visual appearance of metaboxes and forms
 */
test.describe('CMB2 Visual Regression Tests', () => {
  // Auth state is provided by the setup project in playwright.config.js

  // Skip in CI — visual baselines are platform-specific and not committed
  test.skip(!!process.env.CI, 'Visual tests require local baseline screenshots');

  test('Admin metabox visual appearance', async ({ page }) => {
    // Navigate to new post page
    await page.goto('/wp-admin/post-new.php');
    await page.waitForLoadState('networkidle');

    // Find CMB2 metaboxes
    const metabox = page.locator('#cmb2_integration_tests_default_closed');
    
    if (await metabox.isVisible()) {
      // Take screenshot of the closed metabox
      await expect(metabox).toHaveScreenshot('cmb2-metabox-closed.png');
      
      // Open the metabox
      await metabox.locator('button.handlediv').click();
      
      // Take screenshot of the open metabox
      await expect(metabox).toHaveScreenshot('cmb2-metabox-open.png');
    }
  });

  test('Plugin page visual appearance', async ({ page }) => {
    await page.goto('/wp-admin/plugins.php');
    await page.waitForLoadState('networkidle');

    // Find CMB2 plugin row
    const pluginRow = page.locator('tr[data-slug="cmb2"]');
    
    if (await pluginRow.isVisible()) {
      await expect(pluginRow).toHaveScreenshot('cmb2-plugin-row.png');
    }
  });

  test('Mobile responsive design', async ({ page }) => {
    // Test the CMB2 metabox at mobile width (iPhone SE). Scope the screenshot to
    // the metabox element rather than the full page so we regression-test CMB2's
    // responsive rendering, not the surrounding WordPress editor chrome (which
    // drifts across WP versions and editor states).
    await page.setViewportSize({ width: 375, height: 667 });

    await page.goto('/wp-admin/post-new.php');
    await page.waitForLoadState('networkidle');

    const metabox = page.locator('#cmb2_integration_tests_default_closed');
    if (await metabox.isVisible()) {
      // Open the box so the screenshot captures the fields reflowing at mobile width.
      await metabox.locator('button.handlediv').click();
      await expect(metabox).toHaveScreenshot('cmb2-mobile-view.png');
    }
  });

  test('Tablet responsive design', async ({ page }) => {
    // Test the CMB2 metabox at tablet width (iPad). Element-scoped for the same
    // reason as the mobile test above.
    await page.setViewportSize({ width: 768, height: 1024 });

    await page.goto('/wp-admin/post-new.php');
    await page.waitForLoadState('networkidle');

    const metabox = page.locator('#cmb2_integration_tests_default_closed');
    if (await metabox.isVisible()) {
      // Open the box so the screenshot captures the fields reflowing at tablet width.
      await metabox.locator('button.handlediv').click();
      await expect(metabox).toHaveScreenshot('cmb2-tablet-view.png');
    }
  });

  test('Cross-browser metabox consistency', async ({ page, browserName }) => {
    await page.goto('/wp-admin/post-new.php');
    await page.waitForLoadState('networkidle');

    // Find CMB2 metaboxes and take browser-specific screenshots
    const metabox = page.locator('#cmb2_integration_tests_default_closed');
    
    if (await metabox.isVisible()) {
      // Open the metabox
      await metabox.locator('button.handlediv').click();
      
      // Take browser-specific screenshot
      await expect(metabox).toHaveScreenshot(`cmb2-metabox-${browserName}.png`);
    }
  });
});