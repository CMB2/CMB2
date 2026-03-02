const { test, expect } = require('@playwright/test');

/**
 * Meta Boxes functionality tests
 * Migrated from tests/cypress/integration/metaBoxes.spec.js
 *
 * These tests run against the classic editor (Gutenberg is disabled in the
 * test-fields.php mu-plugin fixture) so metaboxes render inline.
 */
test.describe('CMB2 Meta Boxes', () => {
  // Auth state is provided by the setup project in playwright.config.js

  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/post-new.php');
    await expect(page).toHaveURL(/\/wp-admin\/post-new\.php/);

    // In the classic editor, metaboxes render inline and are immediately visible.
    await page.locator('#cmb2_integration_tests_default_closed').waitFor({ state: 'visible' });
  });

  test.describe('Default Closed Box', () => {
    test('Should show its title and allow field interaction', async ({ page }) => {
      const metabox = page.locator('#cmb2_integration_tests_default_closed');
      const boxTitle = metabox.locator('.hndle');
      const fieldInput = page.locator('#cmb2_integration_tests_field_text');
      const toggleButton = metabox.locator('button.handlediv');

      // Box is configured as closed — title visible, field hidden
      await expect(boxTitle).toBeVisible();
      await expect(fieldInput).not.toBeVisible();

      // Open the box via the toggle button
      await toggleButton.click();
      await expect(fieldInput).toBeVisible();

      // Type a value into the field
      await fieldInput.fill('Test Value');
      await expect(fieldInput).toHaveValue('Test Value');

      // Close again
      await toggleButton.click();
      await expect(fieldInput).not.toBeVisible();
    });

    test('Can handle multiple field interactions', async ({ page }) => {
      const metabox = page.locator('#cmb2_integration_tests_default_closed');
      const toggleButton = metabox.locator('button.handlediv');
      const textField = page.locator('#cmb2_integration_tests_field_text');

      // Open the closed box first
      await toggleButton.click();
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

    // The metabox container is visible even when closed
    await expect(metabox).toBeVisible();

    // The header elements should be visible
    await expect(metabox.locator('.hndle')).toBeVisible();
    await expect(metabox.locator('button.handlediv')).toBeVisible();
  });
});
