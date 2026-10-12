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

  // Count the add/remove row events fired after this point.
  async function countRowEvents(page) {
    await page.evaluate(() => {
      window.cmb2RowEvents = { add: 0, remove: 0 };
      jQuery(document)
        .on('cmb2_add_row', () => window.cmb2RowEvents.add++)
        .on('cmb2_remove_row', () => window.cmb2RowEvents.remove++);
    });
  }

  async function clickRemove(table) {
    // The remove button only shows while its row is hovered.
    await table.locator('.cmb-repeat-row').hover();
    await table.locator('.cmb-repeat-row .cmb-remove-row-button').click();
  }

  test('Removing the only row of a field does not add rows to other fields', async ({ page }) => {
    const metabox = page.locator('#cmb2_integration_tests_group_repeatable');
    const groups = metabox.locator('.cmb-repeatable-grouping');
    const tables = metabox.locator('.cmb-repeat-table');

    // Add a second group entry so other entries are checked too.
    await metabox.locator('.cmb-add-group-row').click();
    await expect(groups).toHaveCount(2);

    // 2 group entries x 2 fields, plus the top-level field.
    await expect(tables).toHaveCount(5);

    // Each table starts with one row plus the hidden empty row.
    for (let i = 0; i < 5; i++) {
      await expect(tables.nth(i).locator('.cmb-row')).toHaveCount(2);
    }

    const firstTable = groups.nth(0).locator('.cmb-repeat-table').first();
    const firstInput = firstTable.locator('.cmb-repeat-row input[type="text"]');

    // Fill the other fields so we can check they keep their values.
    const others = [
      groups.nth(0).locator('.cmb-repeat-table').nth(1),
      groups.nth(1).locator('.cmb-repeat-table').nth(0),
      groups.nth(1).locator('.cmb-repeat-table').nth(1),
      page.locator('#top_repeat_repeat'),
    ];
    for (let i = 0; i < others.length; i++) {
      await others[i].locator('.cmb-repeat-row input[type="text"]').fill('keep-' + i);
    }

    await expect(firstInput).toHaveValue('DEF-A');
    await firstInput.fill('Remove me');

    await countRowEvents(page);
    await clickRemove(firstTable);

    // The clicked field is reset to its default.
    await expect(firstInput).toHaveValue('DEF-A');

    // No table gained a row.
    for (let i = 0; i < 5; i++) {
      await expect(tables.nth(i).locator('.cmb-row')).toHaveCount(2);
    }

    // The other fields keep their values.
    for (let i = 0; i < others.length; i++) {
      await expect(others[i].locator('.cmb-repeat-row input[type="text"]')).toHaveValue('keep-' + i);
    }

    // One add and one remove, for the clicked field only.
    expect(await page.evaluate(() => window.cmb2RowEvents)).toEqual({ add: 1, remove: 1 });
  });

  test('Reset still works when the add button is not next to the table', async ({ page }) => {
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));

    // Some plugins and themes wrap the table, so the add button is no longer a sibling.
    await page.evaluate(() => jQuery('#top_repeat_repeat').wrap('<div class="third-party-wrap"></div>'));

    const table = page.locator('#top_repeat_repeat');
    const input = table.locator('.cmb-repeat-row input[type="text"]');
    await input.fill('typed');

    await countRowEvents(page);
    await clickRemove(table);

    await expect(input).toHaveValue('TOP-DEF');
    await expect(table.locator('.cmb-row')).toHaveCount(2);
    expect(await page.evaluate(() => window.cmb2RowEvents)).toEqual({ add: 1, remove: 1 });
    expect(errors).toEqual([]);
  });

  test('Reset does nothing when the add button is disabled', async ({ page }) => {
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));

    // A common snippet to limit rows.
    await page.evaluate(() => jQuery('[data-selector="top_repeat_repeat"]').prop('disabled', true));

    const table = page.locator('#top_repeat_repeat');
    const input = table.locator('.cmb-repeat-row input[type="text"]');
    await input.fill('typed');

    await clickRemove(table);

    await expect(input).toHaveValue('typed');
    await expect(table.locator('.cmb-row')).toHaveCount(2);
    expect(errors).toEqual([]);
  });
});
