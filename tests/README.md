# CMB Test Suite [![Travis](http://img.shields.io/travis/CMB2/CMB2.svg?style=flat)]()

The CMB Test Suite uses PHPUnit to help us maintain the best possible code quality.

Travis-CI Automated Testing
-----------------------------

The master branch of CMB is automatically tested on [travis-ci.org](http://travis-ci.org). The image above will show you the latest test's output. Travis-CI will also automatically test all new Pull Requests to make sure they will not break our build.

Quick Start (For Manual Runs)
-----------------------------

### 1. Clone this repository
```bash
git clone git@github.com:CMB2/CMB2.git ./
```

### 2. Install Dependencies
Use Composer to install PHPUnit and other dependencies:
```bash
composer install
```

This installs PHPUnit 9.6+ and the required polyfills for compatibility.

### 3. Initialize local testing environment
If you haven't already installed the WordPress testing library, we have a helpful script to do so for you.

Note: you'll need to already have `svn`, `wget`, and `mysql` available.

Change to the CMB directory:
```bash
cd CMB2
```

```bash
# Standard MySQL setup
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest

# For Local by Flywheel (adjust path to your site's MySQL socket)
bash tests/bin/install-wp-tests.sh wordpress_test root root "/Users/USERNAME/Library/Application Support/Local/run/SITE_ID/mysql/mysqld.sock" latest
```

Parameters:
* `wordpress_test` is the name of the test database (**all data will be deleted!**)
* `root` is the MySQL user name  
* `''` or `root` is the MySQL user password
* `localhost` or socket path is the MySQL server host
* `latest` is the WordPress version; could also be `6.4`, `6.3` etc.

### 4. Run the tests manually
Note: MySQL must be running in order for tests to run.
```bash
composer test
# or directly
vendor/bin/phpunit
```

### 5. Bonus Round: Run tests automatically before each commit
All you need to do is run these two commands, and then prior to accepting any commit grunt will run phpunit.
If a test fails, the commit will be rejected, giving you the opportunity to fix the problem first.

```bash
npm install
grunt githooks
```
**Note:** You'll need to install [npm](https://www.npmjs.org/) if that's not available. You could also install this via [homebrew](http://brew.sh/) using `brew install npm`.
