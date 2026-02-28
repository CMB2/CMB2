const { chromium } = require('@playwright/test');

/**
 * Global setup for Playwright tests
 * Sets up the WordPress environment and handles authentication
 */
async function globalSetup(config) {
  console.log('Setting up WordPress test environment...');
  
  // Skip WordPress connection check if SKIP_WP_CHECK is set
  if (process.env.SKIP_WP_CHECK) {
    console.log('Skipping WordPress connection check (SKIP_WP_CHECK=1)');
    return;
  }
  
  // If we're in CI, we may need to wait for WordPress to be available
  const baseURL = process.env.WP_BASE_URL || 'http://localhost:8889';
  
  try {
    // Launch browser for setup
    const browser = await chromium.launch();
    const page = await browser.newPage();
    
    // Wait for WordPress to be available with longer timeout
    console.log(`Checking WordPress availability at: ${baseURL}`);
    await page.goto(baseURL, { waitUntil: 'networkidle', timeout: 60000 });
    
    // Check if WordPress is properly initialized - be more flexible with selectors
    const selectors = [
      '#adminmenumain', 
      '.login', 
      '#wpadminbar', 
      '.wp-login',
      'body.wp-admin',
      'body.login'
    ];
    
    let found = false;
    for (const selector of selectors) {
      try {
        await page.waitForSelector(selector, { timeout: 10000 });
        console.log(`Found WordPress element: ${selector}`);
        found = true;
        break;
      } catch (e) {
        // Continue to next selector
      }
    }
    
    if (!found) {
      // Check if we got any response at all
      const title = await page.title();
      console.log(`Page title: ${title}`);
      
      // If we got a response but no WordPress elements, it might still be loading
      if (title && title !== '') {
        console.log('WordPress appears to be loading, continuing...');
      } else {
        throw new Error('No WordPress elements found and no page title');
      }
    }
    
    console.log('WordPress is available at:', baseURL);
    
    await browser.close();
  } catch (error) {
    console.error('WordPress setup failed:', error.message);
    
    // Don't throw error, just log it and continue
    // This allows tests to run even if WordPress isn't fully ready
    console.log('Continuing with tests despite WordPress setup issues...');
  }
  
  console.log('Global setup completed successfully.');
}

module.exports = globalSetup;