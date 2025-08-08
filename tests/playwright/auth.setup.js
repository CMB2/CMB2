const { test as setup, expect } = require('@playwright/test');

const authFile = 'tests/playwright/.auth/user.json';

/**
 * Authentication setup for WordPress
 * This creates a persistent login state that can be reused across tests
 */
setup('authenticate', async ({ page }) => {
  // WordPress admin credentials from environment
  const username = process.env.WP_USERNAME || 'admin';
  const password = process.env.WP_PASSWORD || 'password';
  
  console.log(`Authenticating as ${username}...`);
  
  // Navigate to login page
  await page.goto('/wp-admin/');
  
  // Check if we're already on the login page
  const isOnLoginPage = await page.locator('#loginform').isVisible({ timeout: 5000 }).catch(() => false);
  
  if (isOnLoginPage) {
    // Fill login form
    await page.fill('#user_login', username);
    await page.fill('#user_pass', password);
    await page.check('#rememberme'); // Keep login persistent
    await page.click('#wp-submit');
    
    // Wait for redirect to admin dashboard
    await page.waitForURL('/wp-admin/', { timeout: 10000 });
  }
  
  // Verify we're logged in by checking for admin bar
  await expect(page.locator('#wpadminbar')).toBeVisible();
  
  // Disable WordPress welcome guide if present (for Gutenberg editor)
  await page.evaluate(() => {
    if (window.localStorage) {
      window.localStorage.setItem('WP_DATA_USER_1', JSON.stringify({
        "core/edit-post": {
          "preferences": {
            "features": {
              "welcomeGuide": false,
            },
          },
        },
      }));
    }
  });
  
  console.log('Authentication successful');
  
  // Save authentication state
  await page.context().storageState({ path: authFile });
});