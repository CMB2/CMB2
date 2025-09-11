# CMB2 WordPress Plugin

CMB2 is a developer's toolkit for building metaboxes, custom fields, and forms for WordPress. It's a PHP-based WordPress plugin with Grunt-based build tools for JavaScript/CSS compilation and comprehensive testing infrastructure.

Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

## Working Effectively

### Bootstrap and Build the Repository
- Install NPM dependencies: `npm install --no-optional --ignore-scripts`
  - Takes ~3 minutes. NEVER CANCEL. Set timeout to 5+ minutes.
  - Note: Cypress will fail to download due to network restrictions, but other dependencies will install successfully.
- Install Composer dependencies: `composer install --no-interaction`
  - **KNOWN ISSUE**: May fail due to GitHub API rate limits or network restrictions in sandboxed environments.
  - **WORKAROUND**: Skip if fails - PHPUnit is available globally and core plugin functionality works without vendor dependencies.

### Build Tasks (Grunt)
Run individual build tasks as needed:
- JavaScript linting: `./node_modules/.bin/grunt jshint` -- takes ~0.5 seconds
- JavaScript minification: `./node_modules/.bin/grunt uglify` -- takes ~0.6 seconds  
- CSS minification: `./node_modules/.bin/grunt cssmin` -- takes ~0.6 seconds
- Start file watcher: `./node_modules/.bin/grunt watch` -- runs continuously
- **KNOWN ISSUE**: `grunt sass` fails without Ruby Sass gem. CSS files are pre-compiled in the repository.

### Testing
- **PHP Unit Tests**: `phpunit` or `./vendor/bin/phpunit` (if Composer install succeeded)
  - **KNOWN ISSUE**: Requires WordPress test suite installation via `bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest`
  - **WORKAROUND**: Run `php -l` on PHP files for basic syntax checking
- **Cypress E2E Tests**: `npm run cypress`  
  - **KNOWN ISSUE**: Cypress binary download fails in network-restricted environments
  - **WORKAROUND**: Test functionality manually using WordPress environment if available
- **WordPress Environment**: `npm run env start`
  - **KNOWN ISSUE**: Requires network access to api.wordpress.org - will fail in sandboxed environments
  - **WORKAROUND**: Test plugin by installing in existing WordPress instance

### Development Workflow
- Watch for file changes: `npm run watch` or `./node_modules/.bin/grunt watch`
- Build minified assets: `./node_modules/.bin/grunt js css`
- Lint JavaScript: `./node_modules/.bin/grunt jshint`
- **NEVER CANCEL**: Watch tasks run continuously until manually stopped

## Validation

### Always Test Your Changes
- Run PHP syntax check: `find includes -name "*.php" -exec php -l {} \;`
- Test JavaScript: `./node_modules/.bin/grunt jshint`
- Build minified files: `./node_modules/.bin/grunt uglify cssmin`
- **MANUAL VALIDATION**: Copy `example-functions.php` and test metabox functionality in a WordPress installation
- **CRITICAL**: CMB2 requires WordPress environment - syntax checks alone are insufficient
- **WORDPRESS REQUIRED**: CMB2 classes will not load outside of WordPress context

### Code Quality
- PHP follows WordPress Coding Standards (see `.phpcs.xml.dist`)
- JavaScript linting via JSHint (configured in `Gruntfile.js`)
- **KNOWN ISSUE**: `phpcs` not available by default - install via Composer if needed
- Always run `grunt jshint` before committing changes

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
├── tests/                 # PHPUnit tests and utilities
├── example-functions.php  # Usage examples
├── package.json          # NPM dependencies
├── composer.json         # PHP dependencies  
├── Gruntfile.js          # Build configuration
└── .wp-env.json          # WordPress environment config
```

### Key Files to Understand
- **init.php**: Plugin bootstrap and version checking
- **includes/CMB2.php**: Main plugin class
- **includes/CMB2_Field.php**: Individual field logic
- **includes/CMB2_Types.php**: Field type definitions
- **example-functions.php**: Complete usage examples
- **Gruntfile.js**: All build task definitions

### Frequently Run Commands
```bash
# Install dependencies (one-time setup)
npm install --no-optional --ignore-scripts  # ~3 minutes

# Development workflow
./node_modules/.bin/grunt watch              # File watching
./node_modules/.bin/grunt jshint            # ~0.5s - JS linting
./node_modules/.bin/grunt uglify             # ~0.6s - JS minification
./node_modules/.bin/grunt cssmin             # ~0.6s - CSS minification

# Validation
php -l init.php                             # PHP syntax check
find includes -name "*.php" -exec php -l {} \;  # Check all PHP files
```

### Build Output Locations
- Minified CSS: `css/*.min.css`
- Minified JavaScript: `js/cmb2.min.js`  
- Source maps: `css/*.css.map`

### Dependencies and Versions
- **PHP**: 7.4+ required
- **WordPress**: 3.8+ supported, tested up to 6.4
- **Node.js**: Any recent version (tested with 20.x)
- **Ruby/Sass**: Required for CSS compilation (often pre-compiled)

## Troubleshooting Network Issues

In sandboxed environments, you may encounter:
- Composer install failures → Use global PHPUnit instead
- Cypress download failures → Skip E2E tests or test manually  
- WordPress environment failures → Use existing WordPress installation
- Sass compilation failures → Use pre-compiled CSS files

## Critical Reminders

- **NEVER CANCEL** long-running commands - they complete within documented times
- **ALWAYS** test actual WordPress functionality, not just syntax
- **TIMEOUT VALUES**: Use 5+ minutes for `npm install`, 2+ minutes for any Grunt task
- **VALIDATION**: Copy `example-functions.php` and test in real WordPress environment
- **WORDPRESS DEPENDENCY**: This is a WordPress plugin - CMB2 classes require WordPress to load
- **INTEGRATION TESTING**: Changes must be tested within WordPress admin interface for metabox functionality