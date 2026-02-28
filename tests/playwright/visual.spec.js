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
    // Test mobile view
    await page.setViewportSize({ width: 375, height: 667 }); // iPhone SE size
    
    await page.goto('/wp-admin/post-new.php');
    await page.waitForLoadState('networkidle');

    // Take full page screenshot on mobile
    await expect(page).toHaveScreenshot('cmb2-mobile-view.png', { fullPage: true });
  });

  test('Tablet responsive design', async ({ page }) => {
    // Test tablet view
    await page.setViewportSize({ width: 768, height: 1024 }); // iPad size
    
    await page.goto('/wp-admin/post-new.php');
    await page.waitForLoadState('networkidle');

    // Take full page screenshot on tablet
    await expect(page).toHaveScreenshot('cmb2-tablet-view.png', { fullPage: true });
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