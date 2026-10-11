const { test, expect } = require('@playwright/test');

/**
 * Repeatable fields inside a repeatable group.
 *
 * Removing the only row of a repeatable field resets that row by clicking the
 * field's "Add Row" button and then its "Remove" button. Only that field should
 * change. See https://github.com/CMB2/CMB2/issues/1515
 */
test.describe('CMB2 Group Repeatable Fields', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin/post-new.php');
    await page.locator('#cmb2_integration_tests_group_repeatable').waitFor({ state: 'visible' });
  });

  test('Removing the only row of a field does not add rows to other fields', async ({ page }) => {
    const metabox = page.locator('#cmb2_integration_tests_group_repeatable');
    const groups = metabox.locator('.cmb-repeatable-grouping');
    const tables = metabox.locator('.cmb-repeat-table');

    // Add a second group entry so other entries are checked too.
    await metabox.locator('.cmb-add-group-row').click();
    await expect(groups).toHaveCount(2);
    await expect(tables).toHaveCount(4);

    // Each table starts with one row plus the hidden empty row.
    for (let i = 0; i < 4; i++) {
      await expect(tables.nth(i).locator('.cmb-row')).toHaveCount(2);
    }

    const firstTable = groups.nth(0).locator('.cmb-repeat-table').first();
    const firstInput = firstTable.locator('.cmb-repeat-row input[type="text"]');

    await firstInput.fill('Remove me');

    // The remove button only shows while its row is hovered.
    await firstTable.locator('.cmb-repeat-row').hover();
    await firstTable.locator('.cmb-repeat-row .cmb-remove-row-button').click();

    // The clicked field is reset to an empty row.
    await expect(firstInput).toHaveValue('');

    // No table gained a row.
    for (let i = 0; i < 4; i++) {
      await expect(tables.nth(i).locator('.cmb-row')).toHaveCount(2);
    }
  });
});
