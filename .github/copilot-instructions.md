# CMB2 WordPress Plugin

CMB2 is a developer's toolkit for building metaboxes, custom fields, and forms for WordPress. It's a PHP-based WordPress plugin with npm-based build tools for JavaScript/CSS compilation and comprehensive testing infrastructure.

Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

## Working Effectively

### Bootstrap and Build the Repository
- Install NPM dependencies: `npm install`
- Install Composer dependencies: `composer install --no-interaction`
  - **KNOWN ISSUE**: May fail due to GitHub API rate limits or network restrictions in sandboxed environments.
  - **WORKAROUND**: Skip if fails - PHPUnit is available globally and core plugin functionality works without vendor dependencies.

### Build Tasks (npm scripts)
Run individual build tasks as needed:
- JavaScript linting: `npm run build:js:lint` -- takes ~0.5 seconds
- JavaScript minification: `npm run build:js:minify` -- takes ~0.6 seconds
- CSS compilation: `npm run build:css:compile` -- takes ~1 second
- Full CSS pipeline: `npm run build:css` -- takes ~2 seconds
- Full build (CSS + JS): `npm run build`
- Start file watcher: `npm run watch` -- runs continuously

### Testing
- **PHP Unit Tests**: `phpunit` or `./vendor/bin/phpunit` (if Composer install succeeded)
  - **KNOWN ISSUE**: Requires WordPress test suite installation via `bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest`
  - **WORKAROUND**: Run `php -l` on PHP files for basic syntax checking
- **Playwright E2E Tests**: `npm run test:e2e`
  - Requires Playwright browsers: `npx playwright install`
- **WordPress Environment**: Requires a WordPress installation with database access

### Development Workflow
- Watch for file changes: `npm run watch`
- Build minified assets: `npm run build`
- Lint JavaScript: `npm run build:js:lint`
- **NEVER CANCEL**: Watch tasks run continuously until manually stopped

## Validation

### Always Test Your Changes
- Run PHP syntax check: `find includes -name "*.php" -exec php -l {} \;`
- Test JavaScript: `npm run build:js:lint`
- Build minified files: `npm run build`
- **MANUAL VALIDATION**: Copy `example-functions.php` and test metabox functionality in a WordPress installation
- **CRITICAL**: CMB2 requires WordPress environment - syntax checks alone are insufficient
- **WORDPRESS REQUIRED**: CMB2 classes will not load outside of WordPress context

### Code Quality
- PHP follows WordPress Coding Standards (see `.phpcs.xml.dist`)
- JavaScript linting via JSHint (configured in `.jshintrc`)
- **KNOWN ISSUE**: `phpcs` not available by default - install via Composer if needed
- Always run `npm run build:js:lint` before committing changes

## Common Tasks

### Repository Structure
```
CMB2/
├── init.php                 # Main plugin entry point
├── includes/               # Core PHP classes (CMB2*.php)
│   ├── CMB2.php           # Main CMB2 class
│   ├── CMB2_Field.php     # Field handling
│   ├── CMB2_Types.php     # Field types
│   └── helper-functions.php
├── css/                   # Compiled CSS (including .min.css)
├── js/                    # JavaScript (including .min.js)
├── scripts/               # Build scripts (banner injection, minification)
├── tests/                 # PHPUnit tests and Playwright E2E tests
├── example-functions.php  # Usage examples
├── package.json          # NPM dependencies and build scripts
├── composer.json         # PHP dependencies
└── .jshintrc             # JSHint configuration
```

### Key Files to Understand
- **init.php**: Plugin bootstrap and version checking
- **includes/CMB2.php**: Main plugin class
- **includes/CMB2_Field.php**: Individual field logic
- **includes/CMB2_Types.php**: Field type definitions
- **example-functions.php**: Complete usage examples

### Frequently Run Commands
```bash
# Install dependencies (one-time setup)
npm install

# Development workflow
npm run watch                              # File watching
npm run build:js:lint                      # JS linting
npm run build:js:minify                    # JS minification
npm run build:css                          # Full CSS pipeline
npm run build                              # Full build

# Validation
php -l init.php                            # PHP syntax check
find includes -name "*.php" -exec php -l {} \;  # Check all PHP files
```

### Build Output Locations
- Minified CSS: `css/*.min.css`
- RTL CSS: `css/*-rtl.css` and `css/*-rtl.min.css`
- Minified JavaScript: `js/cmb2.min.js`

### Dependencies and Versions
- **PHP**: 7.4+ required
- **WordPress**: 3.8+ supported, tested up to 6.4
- **Node.js**: Any recent version (tested with 18.x+)
- **Sass**: Dart Sass (installed via npm)

## Troubleshooting Network Issues

In sandboxed environments, you may encounter:
- Composer install failures → Use global PHPUnit instead
- Playwright download failures → Run `npx playwright install`
- WordPress environment failures → Use existing WordPress installation

## Critical Reminders

- **ALWAYS** test actual WordPress functionality, not just syntax
- **VALIDATION**: Copy `example-functions.php` and test in real WordPress environment
- **WORDPRESS DEPENDENCY**: This is a WordPress plugin - CMB2 classes require WordPress to load
- **INTEGRATION TESTING**: Changes must be tested within WordPress admin interface for metabox functionality
