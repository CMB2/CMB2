#!/usr/bin/env bash

cd "$(dirname "$0")/../../"

echo "Tests complete ($TRAVIS_TEST_RESULT). Sending code coverage reports."

echo -en "travis_fold:start:scrutinizer_report\r"
wget https://scrutinizer-ci.com/ocular.phar
php ocular.phar code-coverage:upload --format=php-clover clover.xml
echo -en "travis_fold:end:scrutinizer_report\r"

if [[ "$TRAVIS_PULL_REQUEST" == "false" ]] && [[ ! -z "$CC_TEST_REPORTER_ID" ]] && [[ ! -z $(php -i | grep xdebug) ]]; then
	echo -en "travis_fold:start:codeclimate_report\r"
	./cc-test-reporter after-build -t clover --exit-code $TRAVIS_TEST_RESULT;
	echo -en "travis_fold:end:codeclimate_report\r"
fi
