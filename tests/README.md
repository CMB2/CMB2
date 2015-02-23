# CMB Test Suite [![Travis](http://img.shields.io/travis/WebDevStudios/CMB2.svg?style=flat)]()

The CMB Test Suite uses PHPUnit to help us maintain the best possible code quality.

Travis-CI Automated Testing
-----------------------------

The master branch of CMB is automatically tested on [travis-ci.org](http://travis-ci.org). The image above will show you the latest test's output. Travis-CI will also automatically test all new Pull Requests to make sure they will not break our build.

Quick Start (For Manual Runs)
-----------------------------

### 1. Clone this repository
```bash
git clone git@github.com:WebDevStudios/CMB2.git ./
```

### 2. [Install PHPUnit](https://github.com/sebastianbergmann/phpunit#installation)
This might be tricky. We recommend using [homebrew](http://brew.sh/) because it lets you install lots of things very easily.

If you use homebrew, you can just run `brew install phpunit`.

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
phpunit
```

### 5. Bonus Round: Run tests automatically before each commit
All you need to do is run these two commands, and then priort to accepting any commit grunt will run phpunit.
If a test fails, the commit will be rejected, giving you the opportunity to fix the problem first.

```bash
npm install
grunt githooks
```
**Note:** You'll need to install [npm](https://www.npmjs.org/) if that's not available. You could also install this via [homebrew](http://brew.sh/) using `brew install npm`.
