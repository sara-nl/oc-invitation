#!/bin/sh

# start the actual tests
echo Start Collaboration app integration tests
echo waiting for 60s ... giving owncloud time to startup && sleep 20 &&
echo
echo "Starting integration unit tests"
cd /tmp/tests/src
./vendor/phpunit/phpunit/phpunit -c phpunit.xml

# and exit with the phpunit exit code
exit $?
