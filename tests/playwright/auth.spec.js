const { test, expect } = require('@playwright/test');

/**
 * Authentication tests for WordPress
 * Migrated from tests/cypress/integration/login.spec.js
 */
test.describe('WordPress Authentication', () => {
  test('Can log in', async ({ page }) => {
    const username = process.env.WP_USERNAME || 'admin';
    const password = process.env.WP_PASSWORD || 'password';
    
    // Navigate to admin area
    await page.goto('/wp-admin/');
    
    // Check if we're already logged in
    const isLoggedIn = await page.locator('#wpadminbar').isVisible({ timeout: 5000 }).catch(() => false);
    
    if (isLoggedIn) {
      console.log('Already logged in, skipping login test');
      await expect(page).toHaveURL('/wp-admin/');
      await expect(page.locator('#wpadminbar')).toBeVisible();
      return;
    }
    
    // Should be redirected to login page
    await expect(page).toHaveURL(/.*wp-login\.php.*/);
    
    // Check remember me checkbox
    const rememberCheckbox = page.locator('#rememberme');
    await expect(rememberCheckbox).not.toBeChecked();
    await rememberCheckbox.check();
    
    // Fill login form
    await page.fill('#user_login', username);
    await page.fill('#user_pass', password);
    
    // Submit form by pressing Enter (as in original Cypress test)
    await page.press('#user_pass', 'Enter');
    
    // Should be redirected to admin dashboard
    await expect(page).toHaveURL('/wp-admin/');
    await expect(page.locator('#wpadminbar')).toBeVisible();
  });

  test('Can log out', async ({ page }) => {
    // First log in using our auth setup
    await page.goto('/wp-admin/');
    
    // Check if already logged in, if not, log in
    const isLoggedIn = await page.locator('#wpadminbar').isVisible({ timeout: 5000 }).catch(() => false);
    
    if (!isLoggedIn) {
      const username = process.env.WP_USERNAME || 'admin';
      const password = process.env.WP_PASSWORD || 'password';
      
      // Navigate to login page
      await page.goto('/wp-login.php');
      
      await page.fill('#user_login', username);
      await page.fill('#user_pass', password);
      await page.press('#user_pass', 'Enter');
      await page.waitForURL('/wp-admin/');
    }
    
    // Verify we're on admin page
    await expect(page).toHaveURL('/wp-admin/');
    await expect(page.locator('#wpadminbar')).toBeVisible();
    
    // Hover over the user menu to reveal the logout link, then click it
    await page.locator('#wp-admin-bar-my-account').hover();
    await page.locator('#wp-admin-bar-logout > a').click();
    
    // Should be redirected to login page with logged out message
    await expect(page).toHaveURL(/.*wp-login\.php.*loggedout=true.*/);
  });
});