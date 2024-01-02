FROM ubuntu:latest

RUN mkdir -p /tmp/apps/invitation/tests \
    && touch /tmp/apps/invitation/tests/run-test.sh \
    && echo '#!/bin/sh' >> /tmp/apps/invitation/tests/run-test.sh \
    && sed -i -e '$a echo "Success !!!"' /tmp/apps/invitation/tests/run-test.sh
