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
```bash
# Run all PHPUnit tests
vendor/bin/phpunit

# Run tests via npm
npm run phptests

# Run tests via Composer
composer test

# Install WordPress test environment
bash tests/bin/install-wp-tests.sh <db_name> <db_user> <db_pass> [db_host] [wp_version]

# Run Cypress end-to-end tests
npm run cypress
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
- Cypress for end-to-end testing

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

- WordPress test environment installs to `tests/tmp/wordpress/`
- Test database is separate from development database
- Some tests may require specific WordPress versions
- Ajax and embed tests are excluded by default in Grunt phpunit task

## Meta Best Practices

- Never commit work-planning documents

## Compatibility Considerations

- Always keep in mind that the CMB2 library needs to work on all versions of php from 7.4 to the latest