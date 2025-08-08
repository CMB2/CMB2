/**
 * WordPress utility functions for Playwright tests
 * These replace the custom commands from Cypress
 */

/**
 * Login to WordPress admin
 * Equivalent to cy.logIn() custom command
 */
async function loginToWordPress(page, username = null, password = null) {
  const wpUsername = username || process.env.WP_USERNAME || 'admin';
  const wpPassword = password || process.env.WP_PASSWORD || 'password';
  
  await page.goto('/wp-login.php');
  await page.fill('#user_login', wpUsername);
  await page.fill('#user_pass', wpPassword);
  await page.check('#rememberme');
  await page.click('#wp-submit');
  
  // Wait for redirect to admin dashboard
  await page.waitForURL('/wp-admin/', { timeout: 10000 });
  
  // Disable welcome guide
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
}

/**
 * Save post as draft
 * Equivalent to cy.saveDraft() custom command
 */
async function saveDraft(page) {
  // Set a flag to track page state
  await page.evaluate(() => window.stillOnCurrentPage = true);
  
  const saveDraftButton = page.locator('#save-post');
  await saveDraftButton.waitFor({ state: 'visible' });
  
  // Ensure button is not disabled
  await page.waitForFunction(() => {
    const button = document.querySelector('#save-post');
    return button && !button.classList.contains('disabled');
  });
  
  await saveDraftButton.click();
}

/**
 * Publish post
 * Equivalent to cy.publishPost() custom command
 */
async function publishPost(page) {
  // Set a flag to track page state
  await page.evaluate(() => window.stillOnCurrentPage = true);
  
  const publishButton = page.locator('#publish');
  await publishButton.waitFor({ state: 'visible' });
  
  // Ensure button is not disabled
  await page.waitForFunction(() => {
    const button = document.querySelector('#publish');
    return button && !button.classList.contains('disabled');
  });
  
  await publishButton.click();
}

/**
 * Wait for page load completion
 * Equivalent to cy.waitForPageLoad() custom command
 */
async function waitForPageLoad(page) {
  // Wait for the stillOnCurrentPage flag to be undefined (page changed)
  await page.waitForFunction(() => window.stillOnCurrentPage === undefined);
  
  // Dismiss any success notices
  const noticeDismiss = page.locator('#message .notice-dismiss');
  if (await noticeDismiss.isVisible()) {
    await noticeDismiss.click();
  }
}

/**
 * Block WordPress autosaves
 * Equivalent to cy.blockAutosaves() custom command
 */
async function blockAutosaves(page) {
  await page.route('/wp-admin/admin-ajax.php', async route => {
    const request = route.request();
    const postData = request.postData();
    
    if (postData && postData.includes('wp_autosave')) {
      await route.fulfill({
        status: 400,
        body: 'Autosave blocked for testing'
      });
    } else {
      await route.continue();
    }
  });
}

/**
 * Fill form field with value (equivalent to setValue)
 * This helper ensures the value is properly set
 */
async function setValue(page, selector, value) {
  await page.locator(selector).fill(value);
  
  // Verify the value was set
  await page.waitForFunction(
    ({ selector, value }) => {
      const element = document.querySelector(selector);
      return element && element.value === value;
    },
    { selector, value }
  );
}

/**
 * Wait for WordPress editor to be ready
 */
async function waitForEditor(page) {
  // Wait for either classic editor or Gutenberg editor
  try {
    // Try Gutenberg first
    await page.waitForSelector('.editor-post-title__input', { timeout: 5000 });
  } catch {
    // Fallback to classic editor
    await page.waitForSelector('#title', { timeout: 5000 });
  }
}

/**
 * Check if we're in Gutenberg editor
 */
async function isGutenbergEditor(page) {
  return await page.locator('.editor-post-title__input').isVisible({ timeout: 1000 }).catch(() => false);
}

/**
 * Check if we're in classic editor
 */
async function isClassicEditor(page) {
  return await page.locator('#title').isVisible({ timeout: 1000 }).catch(() => false);
}

module.exports = {
  loginToWordPress,
  saveDraft,
  publishPost,
  waitForPageLoad,
  blockAutosaves,
  setValue,
  waitForEditor,
  isGutenbergEditor,
  isClassicEditor,
};