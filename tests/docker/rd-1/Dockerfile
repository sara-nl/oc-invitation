FROM owncloud/server:latest as builder

# it needs the certs from all mesh hosts
COPY rd-1/rd-1.nl/certs/cert.pem /usr/share/ca-certificates/rd-mesh/rd-1.pem
COPY rd-2/rd-2.nl/certs/cert.pem /usr/share/ca-certificates/rd-mesh/rd-2.pem
RUN echo 'rd-mesh/rd-1.pem' >> /etc/ca-certificates.conf \
    && echo 'rd-mesh/rd-2.pem' >> /etc/ca-certificates.conf \
    && update-ca-certificates \
    && export CURL_CA_BUNDLE=/etc/ssl/certs/ca-certificates.crt

# add the invitations remote users search plugin to the config
# RUN sed -i 's/\($CONFIG.*\)/\1\n   'sharing.remoteShareesSearch' => '\\\\\\\\OCA\\\\\\\\RDMesh\\\\\\\\Service\\\\\\\\InvitationService',/' config/config.php

ENV DEBUG=true