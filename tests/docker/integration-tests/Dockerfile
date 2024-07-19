FROM php:8.2-cli

RUN apt update \
    && apt install -y curl

COPY oc-1/oc-1.nl/certs/cert.pem /usr/share/ca-certificates/rd-mesh/oc-1.pem
COPY oc-2/oc-2.nl/certs/cert.pem /usr/share/ca-certificates/rd-mesh/oc-2.pem
RUN echo 'rd-mesh/oc-1.pem' >> /etc/ca-certificates.conf \
    && echo 'rd-mesh/oc-2.pem' >> /etc/ca-certificates.conf \
    && update-ca-certificates \
    && export CURL_CA_BUNDLE=/etc/ssl/certs/ca-certificates.crt

RUN mkdir -p /tmp/tests
COPY integration-tests/ /tmp/tests/
RUN chmod u+r+x  /tmp/tests/tests.sh

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN cd /tmp/tests/src && composer install

WORKDIR /

# RUN mkdir -p /tmp \
#     && git clone 
# COPY oc-1:/var/www/owncloud/apps/dav /tmp/cp-test

# - fixed paths because it's unclear to me what the correct user (context) is.
# - we give OC some time to setup the database
# RUN mkdir -p /tmp/apps/collaboration/tests \
#     && touch /tmp/apps/collaboration/tests/run-test.sh \
#     && echo '#!/bin/sh' >> /tmp/apps/collaboration/tests/run-test.sh \
#     && sed -i -e '$a echo sleeping 20s ... giving oc the time to setup db && sleep 20 && curl -vv https://oc-1.nl' /tmp/apps/collaboration/tests/run-test.sh
#     # && sed -i -e '$a curl -vv https://oc-1.nl/apps/collaboration/registry/invitation-service-provider' /tmp/apps/collaboration/tests/run-test.sh
