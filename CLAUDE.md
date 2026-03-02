# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CMB2 (Custom Metaboxes 2) is a WordPress metabox, custom fields, and forms library. It allows developers to create custom fields for posts, pages, users, terms, comments, and options pages with a clean, extensible API.

## Architecture

- **Main entry point**: `init.php` - handles plugin initialization and bootstrapping
- **Core classes**: Located in `includes/` directory:
  - `CMB2.php` - Main CMB2 class
  - `CMB2_Field.php` - Individual field handling
  - `CMB2_Types.php` - Field type rendering
  - `CMB2_Boxes.php` - Metabox management
  - `CMB2_Options.php` - Options page functionality
- **Field types**: Located in `includes/types/` - individual classes for each field type
- **REST API**: Located in `includes/rest-api/` - REST API integration
- **Frontend assets**: CSS in `css/`, JavaScript in `js/`

## Common Commands

### Testing

#### PHPUnit Tests (Unit & Integration)
```bash
# Run all PHPUnit tests
vendor/bin/phpunit

# Run tests via npm
npm run phptests

# Run tests via Composer
composer test

# Install WordPress test environment
bash tests/bin/install-wp-tests.sh <db_name> <db_user> <db_pass> [db_host] [wp_version]
```

#### End-to-End Tests (Playwright)
```bash
# Run all E2E tests (recommended)
npm run test:e2e

# Run tests with UI (interactive debugging)
npm run test:e2e:ui

# Run tests in headed mode (visible browser)
npm run test:e2e:headed

# Debug tests step by step
npm run test:e2e:debug

# View test report
npm run test:e2e:report

# Run visual regression tests
npm run test:visual
```

### Development
```bash
# Install dependencies
npm install
composer install

# Watch for file changes and rebuild assets
npm run watch

# Full build (CSS + JS)
npm run build
```

### Code Quality
```bash
# Run PHP CodeSniffer
vendor/bin/phpcs

# Fix PHP CodeSniffer issues automatically
vendor/bin/phpcbf

# JavaScript linting
npm run build:js:lint
```

### Build
```bash
# Full CSS pipeline (compile SCSS, generate RTL, add banners, minify)
npm run build:css

# Individual CSS steps
npm run build:css:compile   # SCSS → CSS
npm run build:css:rtl       # Generate RTL variants
npm run build:css:banner    # Add license headers
npm run build:css:minify    # Generate .min.css files

# Full JS pipeline (lint + minify)
npm run build:js

# Individual JS steps
npm run build:js:lint       # JSHint
npm run build:js:minify     # Concatenate + minify → cmb2.min.js
```

### Translation (release-time only)
```bash
# Generate .pot file
npm run build:i18n:pot

# Compile .po → .mo (requires system gettext)
npm run build:i18n:mo

# Both
npm run build:i18n
```

## Development Environment

The project uses WordPress's standard testing framework and includes:
- PHPUnit configuration in `phpunit.xml.dist`
- WordPress test environment setup via `tests/bin/install-wp-tests.sh`
- npm scripts for asset building (Sass, RTL, minification)
- Playwright for end-to-end testing (migrated from Cypress for better performance and reliability)
- Visual regression testing with screenshot comparison

## Code Standards

- Follows WordPress PHP Coding Standards with some modifications (see `.phpcs.xml.dist`)
- Uses PHP 7.4+ features
- Field types follow consistent naming patterns: `CMB2_Type_*`
- All classes are prefixed with `CMB2_`

## Key Files for Understanding

- `example-functions.php` - Shows how to use CMB2 API
- `includes/CMB2.php` - Main class with core functionality
- `includes/helper-functions.php` - Global helper functions
- `includes/CMB2_Field.php` - Field object and rendering logic

## Testing Notes

### PHPUnit Tests
- WordPress test environment installs to `tests/tmp/wordpress/`
- Test database is separate from development database
- Some tests may require specific WordPress versions
- Ajax and embed tests are excluded by default

### Playwright E2E Tests
- Tests are located in `tests/playwright/`
- Cross-browser testing: Chrome, Firefox, Safari, Mobile Chrome, Mobile Safari
- Visual regression testing with screenshot comparison
- Authentication state is persisted across tests for better performance
- Docker-free CI implementation eliminates previous reliability issues
- Tests run in parallel for faster execution
- Detailed reporting with traces, screenshots, and videos on failure

## Meta Best Practices

- Never commit work-planning documents

## Compatibility Considerations

- Always keep in mind that the CMB2 library needs to work on all versions of php from 7.4 to the latest