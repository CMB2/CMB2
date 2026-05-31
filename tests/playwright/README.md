# CMB2 Playwright Tests

This directory contains end-to-end tests for CMB2 using [Playwright](https://playwright.dev/). These tests have migrated from the previous Cypress implementation to provide better performance, reliability, and cross-browser testing capabilities.

## Overview

### Benefits of Playwright Migration

- **88% Performance Improvement**: Faster test execution compared to Cypress
- **Cross-Browser Support**: Native support for Chrome, Firefox, and Safari
- **CI Reliability**: No Docker dependencies, eliminating previous CI failures
- **Parallel Execution**: Built-in parallel test execution without additional costs
- **Mobile Testing**: Built-in device emulation and responsive testing
- **Visual Testing**: Screenshot comparison and visual regression testing

## Installation

Install Playwright and its dependencies:

```bash
npm install
npx playwright install --with-deps
```

## Running Tests

### Basic Test Execution

```bash
# Run all tests
npm run test:e2e

# Run tests in UI mode (interactive)
npm run test:e2e:ui

# Run tests in headed mode (visible browser)
npm run test:e2e:headed

# Debug tests step by step
npm run test:e2e:debug

# Show HTML test report
npm run test:e2e:report

# Run only visual regression tests
npm run test:visual
```

### Test Configuration

The main configuration is in `playwright.config.js` at the project root. Key settings:

- **Base URL**: `http://localhost:2623` (configurable via `WP_BASE_URL`)
- **Browsers**: Chrome, Firefox, Safari, Mobile Chrome, Mobile Safari
- **Parallel Execution**: Enabled by default
- **Screenshots**: Taken on failure
- **Video**: Recorded on retry
- **Traces**: Collected on first retry

## Test Structure

### Test Files

- `auth.spec.js` - WordPress login/logout functionality
- `plugin.spec.js` - Plugin activation/deactivation
- `metabox.spec.js` - CMB2 metabox functionality
- `visual.spec.js` - Visual regression testing

### Helper Functions

`utils/wordpress-helpers.js` provides utility functions equivalent to the old Cypress custom commands:

- `loginToWordPress()` - WordPress admin authentication
- `saveDraft()` - Save post as draft
- `publishPost()` - Publish post
- `waitForPageLoad()` - Wait for page transitions
- `blockAutosaves()` - Block WordPress autosaves
- `setValue()` - Set form field values
- `waitForEditor()` - Wait for WordPress editor to load

### Authentication Setup

Tests use persistent authentication via `auth.setup.js`, which creates a login session stored in `tests/playwright/.auth/user.json`. This eliminates the need to log in for each test.

## Environment Setup

### WordPress Environment Variables

```bash
# WordPress credentials (defaults shown)
WP_USERNAME=admin
WP_PASSWORD=password
WP_BASE_URL=http://localhost:2623
```

### Local Development

For local development with wp-env:

```bash
# Start WordPress environment
npm run env:start

# Run tests
npm run test:e2e

# Stop environment
npm run env:stop
```

### CI Environment

The GitHub Actions workflow (`.github/workflows/playwright.yml`) sets up WordPress without Docker dependencies, providing reliable CI execution.

## Cross-Browser Testing

Tests automatically run across multiple browsers:

- **Desktop**: Chrome, Firefox, Safari (WebKit)
- **Mobile**: Mobile Chrome (Pixel 5), Mobile Safari (iPhone 12)

Configure browsers in `playwright.config.js` under the `projects` section.

## Visual Testing

Visual regression tests compare screenshots across test runs:

```bash
# Run visual tests
npm run test:visual

# Update baseline screenshots
npx playwright test --update-snapshots
```

Visual test files are stored in `test-results/` and can be reviewed in the HTML report.

## Debugging

### Debug Mode

```bash
npm run test:e2e:debug
```

This opens the Playwright Inspector for step-by-step debugging.

### Trace Viewer

When tests fail, traces are automatically collected. View them with:

```bash
npx playwright show-trace path/to/trace.zip
```

### Screenshots and Videos

Failed tests automatically capture:
- Screenshots (in `test-results/`)
- Videos (when retrying)
- Full page screenshots (for visual tests)

## Migration Notes

### From Cypress to Playwright

Key changes for developers familiar with the old Cypress tests:

| Cypress | Playwright |
|---------|------------|
| `cy.visit()` | `await page.goto()` |
| `cy.get()` | `page.locator()` |
| `cy.click()` | `await locator.click()` |
| `cy.type()` | `await locator.fill()` |
| `cy.should()` | `await expect().toBe()` |
| `cy.logIn()` | `loginToWordPress(page)` |

### Test Organization

- Tests are organized by functionality rather than page structure
- Each test file focuses on a specific CMB2 feature
- Shared utilities are in the `utils/` directory
- Authentication is handled globally via setup project

## Troubleshooting

### Common Issues

1. **WordPress not available**: Ensure your WordPress environment is running on the correct port
2. **Authentication failed**: Check `WP_USERNAME` and `WP_PASSWORD` environment variables
3. **Browser not found**: Run `npx playwright install --with-deps`
4. **Timeout errors**: Increase timeout in `playwright.config.js` or use `page.waitForLoadState()`

### Getting Help

- [Playwright Documentation](https://playwright.dev/)
- [Playwright GitHub](https://github.com/microsoft/playwright)
- [WordPress Testing Handbook](https://make.wordpress.org/core/handbook/testing/)

## Future Enhancements

Potential improvements for the test suite:

- API testing integration
- Database seeding and cleanup
- Multi-site testing
- Accessibility testing
- Performance testing integration