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

# Legacy Cypress tests (deprecated)
npm run cypress
```

#### WordPress Environment
```bash
# Start WordPress environment
npm run env:start

# Stop WordPress environment  
npm run env:stop

# Clean WordPress environment
npm run env:clean
```

### Development
```bash
# Install dependencies
npm install
composer install

# Start development environment
npm run env start

# Watch for file changes and rebuild assets
npm run watch

# Build assets
npm run grunt

# Clean test environment
npm run env clean tests
```

### Code Quality
```bash
# Run PHP CodeSniffer
vendor/bin/phpcs

# Fix PHP CodeSniffer issues automatically
vendor/bin/phpcbf

# JavaScript linting (via Grunt)
npm run grunt jshint
```

### Build & Translation
```bash
# Generate .pot file for translations
npm run grunt makepot

# Compile .po files to .mo
npm run grunt potomo

# Build CSS from Sass
npm run grunt sass

# Minify CSS and JS
npm run grunt uglify
npm run grunt cssmin
```

## Development Environment

The project uses WordPress's standard testing framework and includes:
- PHPUnit configuration in `phpunit.xml.dist`
- WordPress test environment setup via `tests/bin/install-wp-tests.sh`
- Grunt for asset building and task automation
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
- Ajax and embed tests are excluded by default in Grunt phpunit task

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