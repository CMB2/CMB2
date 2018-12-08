#!/usr/bin/env bash

cd "$(dirname "$0")/../../"

export PATH="$HOME/.composer/vendor/bin:$PATH"

echo -en "travis_fold:start:install_wp_tests\r"
bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost $WP_VERSION
echo -en "travis_fold:end:install_wp_tests\r"

source tests/bin/install-php-phpunit.sh

if [[ ! -z "$CC_TEST_REPORTER_ID" ]] && [[ ! -z $(php -i | grep xdebug) ]]; then
	echo -en "travis_fold:start:start_codeclimate_reporter\r"
	curl -L https://codeclimate.com/downloads/test-reporter/test-reporter-latest-linux-amd64 > ./cc-test-reporter
	chmod +x ./cc-test-reporter
	./cc-test-reporter before-build
	echo -en "travis_fold:end:start_codeclimate_reporter\r"
fi

echo "Running with the following versions:"
php -v
phpunit --version

# Run PHPUnit tests
if [[ latest == $WP_VERSION ]]; then
	phpunit --coverage-clover=clover.xml || exit 1;
else
	phpunit --exclude-group cmb2-rest-api --coverage-clover=clover.xml || exit 1;
fi
