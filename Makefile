app_name=invitation
# VERSION=please_set_version
version=$(version)
app_dir_name=$(notdir $(CURDIR))
build_tools_directory=$(CURDIR)/build/tools
source_build_directory=$(CURDIR)/build/artifacts/source
source_package_name=$(source_build_directory)/$(app_name)
appstore_build_directory=$(CURDIR)/build/artifacts/app
appstore_package_name=$(appstore_build_directory)/$(app_name)_$(version)

# Code sniffing: PSR-12 is followed 
# full check, gives all errors and warnings
.PHONY: php-codesniffer-full
php-codesniffer-full:
	$(CURDIR)/vendor/bin/phpcs appinfo/ lib/ templates/settings/ templates/wayf tests/docker/integration-tests/src/ --standard=PSR12 --report=full

# check for errors only, ignoring warnings
.PHONY: php-codesniffer-errors
php-codesniffer-errors:
	$(CURDIR)/vendor/bin/phpcs appinfo/ lib/ templates/settings/ templates/wayf tests/docker/integration-tests/src/ --standard=PSR12 --report=full --warning-severity=0

# should fix (most) errors
.PHONY: php-codesniffer-errors-fix
php-codesniffer-errors-fix:
	$(CURDIR)/vendor/bin/phpcbf appinfo/ lib/ templates/settings/ templates/wayf tests/docker/integration-tests/src/ --standard=PSR12

# Builds the source package for the app store, ignores php and js tests
# command: make version={version_number} buildapp
.PHONY: buildapp-tar
buildapp-tar:
	rm -rf $(appstore_build_directory)
	mkdir -p $(appstore_build_directory)
	# concatenate cd, ls and tar commands with '&&' otherwise the script context will remain the root instead of build
	cd build &&	\
	ln -s ../ $(app_name) && \
	tar cvfh $(appstore_package_name).tar \
	--exclude="$(app_name)/build" \
	--exclude="$(app_name)/release" \
	--exclude="$(app_name)/signature" \
	--exclude="$(app_name)/tests" \
	--exclude="$(app_name)/Makefile" \
	--exclude="$(app_name)/*.log" \
	--exclude="$(app_name)/phpunit*xml" \
	--exclude="$(app_name)/composer.*" \
	--exclude="$(app_name)/js/node_modules" \
	--exclude="$(app_name)/js/tests" \
	--exclude="$(app_name)/js/test" \
	--exclude="$(app_name)/js/*.log" \
	--exclude="$(app_name)/js/package.json" \
	--exclude="$(app_name)/js/bower.json" \
	--exclude="$(app_name)/js/karma.*" \
	--exclude="$(app_name)/js/protractor.*" \
	--exclude="$(app_name)/package.json" \
	--exclude="$(app_name)/bower.json" \
	--exclude="$(app_name)/karma.*" \
	--exclude="$(app_name)/protractor\.*" \
	--exclude="$(app_name)/.*" \
	--exclude="$(app_name)/js/.*" \
	--exclude-vcs \
	$(app_name) && \
	rm $(app_name)

.PHONY: buildapp
buildapp: buildapp-tar
	gzip $(appstore_package_name).tar

# Builds the source package for the app store, includes artifacts required for tests
# command: make version={version_number} buildapp
.PHONY: buildapp-tests
buildapp-tests:
	rm -rf $(appstore_build_directory)
	mkdir -p $(appstore_build_directory)
	# concatenate cd, ls and tar commands with '&&' otherwise the script context will remain the root instead of build
	cd build &&	\
	ln -s ../ $(app_name) && \
	tar cvzfh $(appstore_package_name).tar.gz \
	--exclude="$(app_name)/build" \
	--exclude="$(app_name)/Makefile" \
	--exclude="$(app_name)/*.log" \
	--exclude="$(app_name)/js/node_modules" \
	--exclude="$(app_name)/js/tests" \
	--exclude="$(app_name)/js/test" \
	--exclude="$(app_name)/js/*.log" \
	--exclude="$(app_name)/js/package.json" \
	--exclude="$(app_name)/js/bower.json" \
	--exclude="$(app_name)/js/karma.*" \
	--exclude="$(app_name)/js/protractor.*" \
	--exclude="$(app_name)/package.json" \
	--exclude="$(app_name)/bower.json" \
	--exclude="$(app_name)/karma.*" \
	--exclude="$(app_name)/protractor\.*" \
	--exclude="$(app_name)/.*" \
	--exclude="$(app_name)/js/.*" \
	--exclude-vcs \
	$(app_name) && \
	rm $(app_name)

.PHONY: buildapp-and-sign
buildapp-and-sign: buildapp-tar
	# extract the tar
	rm -rf tmp/${app_name} && \
	mkdir -p tmp && \
	tar -xf $(appstore_package_name).tar -C tmp
	# rm -rf tmp
	# mkdir -p /tmp/${app_name}/appinfo && \
	# echo '{boe: "bah"}' > /tmp/${app_name}/appinfo/invitation.test && \
	# ln -s /tmp/${app_name} ${app_name} && \
	# tar -rf $(appstore_package_name).tar ${app_name}/appinfo/invitation.test
	# rm ${app_name}

