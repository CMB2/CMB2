const { test, expect } = require('@playwright/test');

/**
 * A wysiwyg field inside a group is rendered into a placeholder textarea, which
 * cmb2-wysiwyg.js swaps for an editor built from a template. Saved markup must
 * come back as the textarea's text, not as page markup.
 */
test.describe('CMB2 Group Wysiwyg Field', () => {
  test('Saved markup round-trips as editor text', async ({ page }) => {
    const value = '<p>a &amp; b</p></textarea><b class="cmb2-e2e-markup">x</b>';

    await page.goto('/wp-admin/post-new.php?post_type=page');
    const metabox = page.locator('#cmb2_integration_tests_group_wysiwyg');
    const editor = metabox.locator('textarea.wp-editor-area');
    await editor.waitFor({ state: 'visible' });

    await page.locator('#title').fill('Group wysiwyg round-trip');
    await editor.fill(value);
    await page.locator('#save-post').click();
    await page.waitForURL(/post\.php\?post=\d+&action=edit/);

    await editor.waitFor({ state: 'visible' });
    await expect(editor).toHaveValue(value);
    await expect(page.locator('.cmb2-e2e-markup')).toHaveCount(0);

    // Adding a group row re-renders the editors from the template too.
    await metabox.locator('.cmb-add-group-row').click();
    await expect(metabox.locator('textarea.wp-editor-area')).toHaveCount(2);
    await expect(editor.first()).toHaveValue(value);
    await expect(page.locator('.cmb2-e2e-markup')).toHaveCount(0);
  });
});
