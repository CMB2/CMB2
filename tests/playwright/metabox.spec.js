const { test, expect } = require('@playwright/test');

/**
 * Meta Boxes functionality tests
 * Migrated from tests/cypress/integration/metaBoxes.spec.js
 */
test.describe('CMB2 Meta Boxes', () => {
  // Auth state is provided by the setup project in playwright.config.js

  test.beforeEach(async ({ page }) => {
    // Navigate to new post page
    await page.goto('/wp-admin/post-new.php');
    await expect(page).toHaveURL(/\/wp-admin\/post-new\.php/);

    // Wait for the CMB2 metabox to appear (don't use networkidle — Gutenberg never idles)
    await page.locator('#cmb2_integration_tests_default_closed').waitFor({ state: 'attached', timeout: 15000 });

    // Scroll the metabox into view — in Gutenberg it's below the editor fold
    await page.locator('#cmb2_integration_tests_default_closed').scrollIntoViewIfNeeded();
  });

  test.describe('Default Closed Box', () => {
    test('Should show its title and allow field interaction', async ({ page }) => {
      const metabox = page.locator('#cmb2_integration_tests_default_closed');
      const boxTitle = metabox.locator('.hndle');
      const fieldInput = page.locator('#cmb2_integration_tests_field_text');
      const toggleButton = metabox.locator('button.handlediv');

      // Verify metabox title is visible
      await expect(boxTitle).toBeVisible();

      // Initially, the field should not be visible (box is closed by default)
      await expect(fieldInput).not.toBeVisible();

      // Click the toggle button to open the metabox
      await toggleButton.click();

      // Now the field should be visible
      await expect(fieldInput).toBeVisible();

      // Type a value into the field
      await fieldInput.fill('Test Value');
      await expect(fieldInput).toHaveValue('Test Value');
    });

    test('Can handle multiple field interactions', async ({ page }) => {
      const metabox = page.locator('#cmb2_integration_tests_default_closed');
      const toggleButton = metabox.locator('button.handlediv');

      // Open the metabox
      await toggleButton.click();

      const textField = page.locator('#cmb2_integration_tests_field_text');
      await expect(textField).toBeVisible();

      // Test clearing and refilling the field
      await textField.fill('First Value');
      await expect(textField).toHaveValue('First Value');

      await textField.fill('');
      await expect(textField).toHaveValue('');

      await textField.fill('Final Value');
      await expect(textField).toHaveValue('Final Value');
    });
  });

  test('Should handle metabox visibility states correctly', async ({ page }) => {
    const metabox = page.locator('#cmb2_integration_tests_default_closed');
    await expect(metabox).toBeVisible();

    // Test the metabox header structure
    await expect(metabox.locator('.hndle')).toBeVisible();
    await expect(metabox.locator('.handlediv')).toBeVisible();
  });
});
