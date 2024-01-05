FROM ubuntu:latest

RUN apt update \
    && apt install -y curl git

COPY oc-1/oc-1.nl/certs/cert.pem /usr/share/ca-certificates/rd-mesh/oc-1.pem
COPY rd-2/rd-2.nl/certs/cert.pem /usr/share/ca-certificates/rd-mesh/rd-2.pem
RUN echo 'rd-mesh/oc-1.pem' >> /etc/ca-certificates.conf \
    && echo 'rd-mesh/rd-2.pem' >> /etc/ca-certificates.conf \
    && update-ca-certificates \
    && export CURL_CA_BUNDLE=/etc/ssl/certs/ca-certificates.crt

# RUN mkdir -p /tmp \
#     && git clone 
# COPY oc-1:/var/www/owncloud/apps/dav /tmp/cp-test

# - fixed paths because it's unclear to me what the correct user (context) is.
# - we give OC some time to setup the database
RUN mkdir -p /tmp/apps/invitation/tests \
    && touch /tmp/apps/invitation/tests/run-test.sh \
    && echo '#!/bin/sh' >> /tmp/apps/invitation/tests/run-test.sh \
    && sed -i -e '$a echo sleeping 20s ... giving oc the time to setup db && sleep 20 && curl -vv https://oc-1.nl' /tmp/apps/invitation/tests/run-test.sh
    # && sed -i -e '$a curl -vv https://oc-1.nl/apps/invitation/registry/invitation-service-provider' /tmp/apps/invitation/tests/run-test.sh
