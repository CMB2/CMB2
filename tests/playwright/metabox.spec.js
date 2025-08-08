const { test, expect } = require('@playwright/test');

/**
 * Meta Boxes functionality tests
 * Migrated from tests/cypress/integration/metaBoxes.spec.js
 */
test.describe('CMB2 Meta Boxes', () => {
  // Use authenticated state for all tests
  test.use({ storageState: 'tests/playwright/.auth/user.json' });

  test.beforeEach(async ({ page }) => {
    // Navigate to new post page before each test
    await page.goto('/wp-admin/post-new.php');
    await expect(page).toHaveURL('/wp-admin/post-new.php');
    
    // Wait for the page to fully load
    await page.waitForLoadState('networkidle');
  });

  test.describe('Default Closed Box', () => {
    test('Should show its title and allow field interaction', async ({ page }) => {
      // Find the CMB2 metabox
      const metabox = page.locator('#cmb2_integration_tests_default_closed');
      const boxTitle = metabox.locator('h2').first();
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

      // Add a post title (required for saving draft)
      const postTitle = page.locator('.editor-post-title__input').first();
      await expect(postTitle).toBeVisible();
      await postTitle.fill('Test Post Title');

      // Save as draft
      const saveDraftButton = page.locator('.editor-post-save-draft');
      await expect(saveDraftButton).toBeVisible();
      await saveDraftButton.click();
      
      // Wait for the post to be saved
      await expect(page.locator('.editor-post-saved-state.is-saved')).toBeVisible();
      
      // Reload the page to verify persistence
      await page.reload();
      await page.waitForLoadState('networkidle');

      // Re-locate elements after page reload
      const reloadedMetabox = page.locator('#cmb2_integration_tests_default_closed');
      const reloadedToggle = reloadedMetabox.locator('button.handlediv');
      const reloadedField = page.locator('#cmb2_integration_tests_field_text');

      // Open the metabox again
      await reloadedToggle.click();
      
      // Verify the value was saved and persisted
      await expect(reloadedField).toHaveValue('Test Value');

      // Close the metabox to verify toggle functionality
      await reloadedToggle.click();
      
      // Field should be hidden again
      await expect(reloadedField).not.toBeVisible();
    });

    test('Can handle multiple field interactions', async ({ page }) => {
      const metabox = page.locator('#cmb2_integration_tests_default_closed');
      const toggleButton = metabox.locator('button.handlediv');

      // Open the metabox
      await toggleButton.click();

      // Test multiple field interactions if there are multiple fields
      const textField = page.locator('#cmb2_integration_tests_field_text');
      
      if (await textField.isVisible()) {
        // Test clearing and refilling the field
        await textField.fill('First Value');
        await expect(textField).toHaveValue('First Value');
        
        await textField.fill('');
        await expect(textField).toHaveValue('');
        
        await textField.fill('Final Value');
        await expect(textField).toHaveValue('Final Value');
      }
    });
  });

  test('Should handle metabox visibility states correctly', async ({ page }) => {
    // Test that metaboxes are present on the page
    const metabox = page.locator('#cmb2_integration_tests_default_closed');
    await expect(metabox).toBeVisible();
    
    // Test the metabox header structure
    await expect(metabox.locator('.hndle')).toBeVisible();
    await expect(metabox.locator('.handlediv')).toBeVisible();
  });
});