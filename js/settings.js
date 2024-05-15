(function (window, $) {
    $(window.document).ready(function () {
        var document = window.document;

        let toggleAllowSharingWithInvitedUsersOnlyButton = document.getElementById('allow-sharing-with-invited-users-only');
        toggleAllowSharingWithInvitedUsersOnlyButton.addEventListener(
            "click", function (event) {
                document.getElementById('allow-sharing-with-invited-users-only-error').innerText = "";
                _allow = false;
                if (document.getElementById('allow-sharing-with-invited-users-only').checked) {
                    _allow = true;
                } else {
                    _allow = false;
                }

                window.INVITATION_SERVICE.call(
                    '/share-with-invited-users-only',
                    'PUT',
                    { allow: _allow },
                    (result) => {
                        if (result.success == true) {
                            console.log('allow sharing with invited users only updated to "' + result.data + '"');
                        } else {
                            document.getElementById('allow-sharing-with-invited-users-only-error').innerText = t('invitation', result.error_message);
                        }
                    },
                    (response) => {
                        document.getElementById('allow-sharing-with-invited-users-only-error').innerText = t('invitation', 'SETTINGS_UPDATE_ERROR');
                        console.log(response.toString());
                    }
                )
            }
        );

        let saveInvitationServiceProviderButton = document.getElementById('save-invitation-service-name');
        saveInvitationServiceProviderButton.addEventListener(
            "click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                document.getElementById('invitation-service-settings-endpoint-error').innerText = "";
                document.getElementById('invitation-service-settings-name-error').innerText = "";
                document.getElementById('invitation-service-settings-success').innerText = "";
                $('[id="invitation-service-settings-success"]').removeClass('fade-out');
                document.getElementById('invitation-service-settings-error').innerText = "";

                var _endpoint = document.getElementById('invitation-service-endpoint').value.trim();
                var _name = document.getElementById('invitation-service-name').value.trim();
                if (_endpoint !== "" && _name !== "") {
                    window.INVITATION_SERVICE.call(
                        '/registry/invitation-service-provider',
                        'PUT',
                        { endpoint: _endpoint, name: _name },
                        (result) => {
                            if (result.success == true) {
                                console.log('name updated to "' + result.data + '"');
                                $('[id="invitation-service-settings-success"]').text('saved');
                                $('[id="invitation-service-settings-success"]').addClass('fade-out');
                            } else {
                                document.getElementById('invitation-service-settings-error').innerText = t('invitation', result.error_message);
                            }
                        },
                        (response) => {
                            document.getElementById('invitation-service-settings-error').innerText = t('invitation', 'MESH_REGISTRY_UPDATE_PROVIDER_ERROR');
                            console.log(response.toString());
                        }
                    )
                } else {
                    if (_endpoint === "") {
                        document.getElementById('invitation-service-settings-endpoint-error').innerText = t('invitation', 'MESH_REGISTRY_UPDATE_PROVIDER_REQUIRED_FIELD_ERROR');
                    }
                    if (_name === "") {
                        document.getElementById('invitation-service-settings-name-error').innerText = t('invitation', "MESH_REGISTRY_UPDATE_PROVIDER_REQUIRED_FIELD_ERROR");
                    }
                }
            }
        );

        // display remote service providers
        let getAllProviders = function () {
            window.INVITATION_SERVICE.call(
                '/registry/invitation-service-providers',
                'GET',
                null,
                (result) => {
                    var _endpoint = document.querySelector('[data-invitation-service-provider-endpoint]').dataset.invitationServiceProviderEndpoint.trim();
                    if (result.success == true) {
                        _ul = $('#invitation-remote-service-providers');
                        _ul.empty();
                        result.data.forEach((isp) => {
                            if (_endpoint != isp.endpoint) {
                                _li = $('<li>' + isp.name + '</li>');
                                _deleteButton = $('<span class="icon icon-delete"></span>');
                                _errorElement = $('<span class="settings-error"></span>');
                                _li.append(_deleteButton);
                                _li.append(_errorElement);
                                _ul.append().append(_li);
                                addIspDeleteAction(_deleteButton[0], isp.endpoint, _errorElement[0]);
                            }
                        });
                    } else {
                        document.getElementById('invitation-remote-service-providers-error').innerText = t('invitation', result.error_message);
                    }
                },
                function (response) {
                    document.getElementById('invitation-remote-service-providers-error').innerText = t('invitation', 'MESH_REGISTRY_GET_SERVICE_PROVIDERS_ERROR');
                    console.log(response.toString());
                }
            );
        }

        let addIspDeleteAction = function (button, endpoint, errorElement) {
            button.addEventListener(
                "click", function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    var _endpoint = endpoint.trim();
                    if (_endpoint !== "") {
                        OC.dialogs.confirm(
                            "Do you want to delete this service provider ?",
                            "Delete service provider",
                            function (confirm) {
                                if (confirm == true) {
                                    window.INVITATION_SERVICE.call(
                                        '/registry/invitation-service-provider',
                                        'DELETE',
                                        { endpoint: _endpoint },
                                        (result) => {
                                            if (result.success == true) {
                                                getAllProviders();
                                                console.log('invitation service provider with endpoint ' + endpoint + ' deleted');
                                            } else {
                                                errorElement.innerText = t('invitation', result.error_message);
                                            }
                                        },
                                        (response) => {
                                            errorElement.innerText = t('invitation', 'MESH_REGISTRY_DELETE_SERVICE_PROVIDER_ERROR');
                                            console.log(response.toString());
                                        }
                                    )
                                }
                            },
                            true
                        );
                    }
                }
            );
        }

        let addIspButton = document.getElementById('invitation-add-remote-service-provider');
        addIspButton.addEventListener(
            "click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                document.getElementById('invitation-remote-service-providers-error').innerText = "";
                $('div#invitation-add-remote-service-provider').removeClass('hide');
            }
        );

        let addIspSaveButton = document.getElementById('invitation-remote-service-provider-save');
        addIspSaveButton.addEventListener(
            "click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                document.getElementById('invitation-remote-service-provider-save-error').innerText = "";
                var _endpoint = document.getElementById('invitation-add-remote-service-provider-endpoint').value.trim();
                if (_endpoint !== "") {
                    window.INVITATION_SERVICE.call(
                        '/registry/invitation-service-provider',
                        'POST',
                        { endpoint: _endpoint },
                        (result) => {
                            if (result.success == true) {
                                $('div#invitation-add-remote-service-provider').addClass('hide');
                                document.getElementById('invitation-add-remote-service-provider-endpoint').value = "";
                                getAllProviders();
                            } else {
                                document.getElementById('invitation-remote-service-provider-save-error').innerText = t('invitation', result.error_message);
                            }
                        },
                        (response) => {
                            console.log(response.toString());
                            if (response == "SETTINGS_ADD_PROVIDER_IS_NOT_REMOTE_ERROR") {
                                document.getElementById('invitation-remote-service-provider-save-error').innerText = t('invitation', 'SETTINGS_ADD_PROVIDER_IS_NOT_REMOTE_ERROR');
                            } else {
                                document.getElementById('invitation-remote-service-provider-save-error').innerText = t('invitation', 'MESH_REGISTRY_ADD_PROVIDER_ERROR');
                            }
                        }
                    );
                }
            }
        );

        getAllProviders();

    });
})(window, jQuery);