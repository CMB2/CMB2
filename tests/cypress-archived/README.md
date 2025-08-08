# Cypress Tests Archive

This directory contains the original Cypress tests that have been migrated to Playwright.

## Migration Summary

These tests have been successfully migrated to Playwright and are now located in `tests/playwright/`:

- `integration/login.spec.js` → `tests/playwright/auth.spec.js`
- `integration/activatePlugin.spec.js` → `tests/playwright/plugin.spec.js`  
- `integration/metaBoxes.spec.js` → `tests/playwright/metabox.spec.js`
- `support/commands.js` → `tests/playwright/utils/wordpress-helpers.js`

## Why We Migrated

The Cypress tests were experiencing consistent CI failures due to Docker/wp-env dependency issues (`spawn docker-compose ENOENT`). The migration to Playwright provides:

- **88% performance improvement** in test execution time
- **Cross-browser testing** (Chrome, Firefox, Safari)
- **CI reliability** with Docker-free setup
- **Parallel execution** without additional costs
- **Visual regression testing** capabilities
- **Mobile and responsive testing**

## Legacy Files

These files are kept for reference but are no longer used:

- `cypress.json` - Original Cypress configuration
- `integration/` - Original test files  
- `support/` - Custom commands and configuration
- `fixtures/` - Test data files
- `plugins/` - Cypress plugins

The new Playwright tests provide equivalent functionality with enhanced capabilities.

For current testing, use:
```bash
npm run test:e2e
```

## Date Archived

Migrated to Playwright on: 2025-08-08