# CMB Test Suite

The CMB Test Suite uses PHPUnit to help us maintain the best possible code quality.

GitHub Actions Automated Testing
---------------------------------

CMB2 is automatically tested using GitHub Actions on every push and pull request. The test suite runs against multiple PHP versions (7.4-8.3) and WordPress versions to ensure compatibility.

- **PHPUnit Tests**: Comprehensive unit tests for all CMB2 functionality
- **Cypress Tests**: End-to-end browser testing for UI interactions

You can view the latest test results in the [Actions tab](https://github.com/CMB2/CMB2/actions) of this repository.

Quick Start (For Manual Runs)
-----------------------------

### 1. Clone this repository
```bash
git clone git@github.com:CMB2/CMB2.git ./
```

### 2. Install dependencies with Composer
The recommended modern approach is to use Composer to install PHPUnit and other dependencies:

```bash
composer install
```

This will install PHPUnit 9.6 and all required dependencies including the Yoast PHPUnit polyfills for compatibility.

### 3. Initialize local testing environment
If you haven't already installed the WordPress testing library, we have a helpful script to do so for you.

Note: you'll need to already have `svn`, `wget`, and `mysql` available.

Change to the CMB directory:
```bash
cd CMB2
```

```bash
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```
* `wordpress_test` is the name of the test database (**all data will be deleted!**)
* `root` is the MySQL user name
* `''` is the MySQL user password
* `localhost` is the MySQL server host
* `latest` is the WordPress version; could also be `3.7`, `3.6.2` etc.

### 4. Run the tests manually
Note: MySQL must be running in order for tests to run.
```bash
vendor/bin/phpunit
```

Or use the Composer script:
```bash
composer test
```

### 5. Bonus Round: Run tests automatically before each commit
All you need to do is run these two commands, and then prior to accepting any commit grunt will run phpunit.
If a test fails, the commit will be rejected, giving you the opportunity to fix the problem first.

```bash
npm install
grunt githooks
```
**Note:** You'll need to install [npm](https://www.npmjs.org/) if that's not available. You could also install this via [homebrew](http://brew.sh/) using `brew install npm`.
