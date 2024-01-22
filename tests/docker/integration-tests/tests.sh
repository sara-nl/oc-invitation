#!/bin/sh

# start the actual tests
echo Start Invitation app integration tests
echo sleeping 15s ... giving owncloud time to startup && sleep 15 &&

# if this succeeds we know we have connectivity
# curl -vv https://oc-1.nl/apps/invitation/registry/invitation-service-provider
echo 
echo
echo "Starting integration unit tests"
cd /tmp/tests/src
./vendor/phpunit/phpunit/phpunit -c phpunit.xml

# and exit with the phpunit exit code
exit $?
