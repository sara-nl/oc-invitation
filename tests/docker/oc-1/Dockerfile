FROM owncloud/server:latest as builder

ARG BRANCH

RUN apt update \
    && apt install -y git make

# it needs the certs from all mesh hosts
COPY oc-1/oc-1.nl/certs/cert.pem /usr/share/ca-certificates/rd-mesh/oc-1.pem
COPY rd-2/rd-2.nl/certs/cert.pem /usr/share/ca-certificates/rd-mesh/rd-2.pem
RUN echo 'rd-mesh/oc-1.pem' >> /etc/ca-certificates.conf \
    && echo 'rd-mesh/rd-2.pem' >> /etc/ca-certificates.conf \
    && update-ca-certificates \
    && export CURL_CA_BUNDLE=/etc/ssl/certs/ca-certificates.crt

# add the Invitation app
RUN mkdir -p /tmp/invitation \
    # && git clone -b github-action-integration-tests https://github.com/sara-nl/oc-invitation.git /tmp/invitation \
    && git clone -b ${BRANCH} https://github.com/sara-nl/oc-invitation.git /tmp/invitation \
    && cd /tmp/invitation/ \
    && make -e version=test buildapp \
    && tar -xvf /tmp/invitation/build/artifacts/app/invitation_test.tar.gz -C /var/www/owncloud/apps


# add the invitations remote users search plugin to the config
# RUN sed -i 's/\($CONFIG.*\)/\1\n   'sharing.remoteShareesSearch' => '\\\\\\\\OCA\\\\\\\\RDMesh\\\\\\\\Service\\\\\\\\InvitationService',/' config/config.php

ENV DEBUG=true