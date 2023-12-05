FROM owncloud/server:latest AS release-builder
ARG version
ARG branch
WORKDIR /tmp
RUN apt update \
    && apt install git make -y
USER www-data
RUN git clone -b ${branch} https://github.com/sara-nl/oc-invitation.git
RUN cd /tmp/oc-invitation \
    && composer install \
    && make -e version=${version} buildapp

FROM scratch AS export-stage
USER www-data
COPY --from=release-builder /tmp/oc-invitation/build/artifacts/app/* /
