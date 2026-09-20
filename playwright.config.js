const { defineConfig, devices } = require('@playwright/test');
const path = require('path');
const dotenv = require('dotenv');

dotenv.config({
  path: path.join(__dirname, '.env.local'),
  override: false,
  quiet: true,
});

const isEnabled = (value) => ['1', 'true', 'yes'].includes(String(value).toLowerCase());

/**
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
  testDir: './tests/playwright',
  /* Run tests in files in parallel */
  fullyParallel: true,
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,
  /* Exploratory QA runs are invoked directly and never collected as durable tests. */
  testIgnore: ['**/.qa/**'],
  /* Retry on CI only */
  retries: process.env.CI ? 2 : 0,
  /* Single worker on CI — tests share one WordPress instance so parallel
     execution causes session/state interference (e.g., logout kills other sessions). */
  workers: process.env.CI ? 1 : undefined,
  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  reporter: process.env.CI ? 'github' : 'list',
  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: process.env.WP_BASE_URL || 'http://localhost:2623',

    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: 'on-first-retry',

    /* Screenshot on failure */
    screenshot: 'only-on-failure',

    /* Video on retry */
    video: 'retain-on-failure',
  },

  /* Configure projects for major browsers */
  projects: [
    /* Auth setup — runs first, saves login state for other projects */
    {
      name: 'setup',
      testMatch: /auth\.setup\.js/,
      testIgnore: /(^|\/)\.qa\//,
    },

    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'tests/playwright/.auth/user.json',
      },
      dependencies: ['setup'],
      testIgnore: [/auth\.setup\.js/, /(^|\/)\.qa\//],
    },

    {
      name: 'firefox',
      use: {
        ...devices['Desktop Firefox'],
        storageState: 'tests/playwright/.auth/user.json',
      },
      dependencies: ['setup'],
      testIgnore: [/auth\.setup\.js/, /(^|\/)\.qa\//],
    },

    {
      name: 'webkit',
      use: {
        ...devices['Desktop Safari'],
        storageState: 'tests/playwright/.auth/user.json',
      },
      dependencies: ['setup'],
      testIgnore: [/auth\.setup\.js/, /(^|\/)\.qa\//],
    },
  ],

  /* Run your local dev server before starting the tests */
  webServer: isEnabled(process.env.SKIP_WP_SERVER) ? undefined : {
    command: 'npm run env:tests:start',
    url: process.env.WP_BASE_URL || 'http://localhost:2623',
    reuseExistingServer: !process.env.CI,
    timeout: 180 * 1000,
    stdout: 'pipe',
    stderr: 'pipe',
  },

  /* Global setup and teardown */
  globalSetup: './tests/playwright/global-setup.js',
  globalTeardown: './tests/playwright/global-teardown.js',
});
