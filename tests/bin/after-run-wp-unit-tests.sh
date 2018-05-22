#!/usr/bin/env bash

cd "$(dirname "$0")/../../"

echo "Tests complete ($TRAVIS_TEST_RESULT). Sending code coverage reports."

wget https://scrutinizer-ci.com/ocular.phar
php ocular.phar code-coverage:upload --format=php-clover clover.xml

if [[ "$TRAVIS_PULL_REQUEST" == "false" ]] && [[ ! -z "$CC_TEST_REPORTER_ID" ]] && [[ ! -z $(php -i | grep xdebug) ]]; then
	./cc-test-reporter after-build -t clover --exit-code $TRAVIS_TEST_RESULT;
fi
