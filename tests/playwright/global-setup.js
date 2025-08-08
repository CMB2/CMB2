const { chromium } = require('@playwright/test');

/**
 * Global setup for Playwright tests
 * Sets up the WordPress environment and handles authentication
 */
async function globalSetup(config) {
  console.log('Setting up WordPress test environment...');
  
  // If we're in CI, we may need to wait for WordPress to be available
  const baseURL = process.env.WP_BASE_URL || 'http://localhost:8889';
  
  try {
    // Launch browser for setup
    const browser = await chromium.launch();
    const page = await browser.newPage();
    
    // Wait for WordPress to be available
    await page.goto(baseURL, { waitUntil: 'networkidle' });
    
    // Check if WordPress is properly initialized
    await page.waitForSelector('#adminmenumain, .login', { timeout: 30000 });
    
    console.log('WordPress is available at:', baseURL);
    
    await browser.close();
  } catch (error) {
    console.error('WordPress setup failed:', error.message);
    throw new Error(`WordPress is not available at ${baseURL}. Please ensure your WordPress environment is running.`);
  }
  
  console.log('Global setup completed successfully.');
}

module.exports = globalSetup;