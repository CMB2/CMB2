/**
 * Global teardown for Playwright tests
 * Cleans up any resources after all tests are complete
 */
async function globalTeardown(config) {
  console.log('Cleaning up test environment...');
  
  // In CI environments, cleanup is usually handled by the container lifecycle
  // For local development, you might want to stop wp-env here
  if (!process.env.CI) {
    // Could add cleanup logic here if needed
    console.log('Local environment cleanup (if needed) would go here');
  }
  
  console.log('Global teardown completed.');
}

module.exports = globalTeardown;