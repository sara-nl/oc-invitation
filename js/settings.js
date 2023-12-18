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

                window.INVITATION.call(
                    '/share-with-invited-users-only',
                    'PUT',
                    { allow: _allow },
                    (result) => {
                        if (result.success == true) {
                            console.log('allow sharing with invited users only updated to "' + result.data + '"');
                        } else {
                            document.getElementById('allow-sharing-with-invited-users-only-error').innerText = result.error_message;
                        }
                    },
                    (response) => {
                        document.getElementById('allow-sharing-with-invited-users-only-error').innerText = 'UNSPECIFIED ERROR';
                        console.log(response.toString());
                    }
                )
            }
        );

        let saveInvitationServiceEndpointButton = document.getElementById('save-invitation-service-endpoint');
        saveInvitationServiceEndpointButton.addEventListener(
            "click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                document.getElementById('invitation-service-settings-endpoint-success').innerText = "";
                $('[id="invitation-service-settings-endpoint-success"]').removeClass('fade-out');
                document.getElementById('invitation-service-settings-endpoint-error').innerText = "";

                var _endpoint = document.getElementById('invitation-service-endpoint').value.trim();
                if (_endpoint !== "") {
                    window.INVITATION.call(
                        '/endpoint',
                        'PUT',
                        { endpoint: _endpoint },
                        (result) => {
                            if (result.success == true) {
                                console.log('endpoint updated to "' + result.data + '"');
                                $('[id="invitation-service-settings-endpoint-success"]').text('saved');
                                $('[id="invitation-service-settings-endpoint-success"]').addClass('fade-out');
                            } else {
                                document.getElementById('invitation-service-settings-endpoint-error').innerText = result.error_message;
                            }
                        },
                        (response) => {
                            document.getElementById('invitation-service-settings-endpoint-error').innerText = 'UNSPECIFIED ERROR';
                            console.log(response.toString());
                        }
                    )
                }
            }
        );

        // display remote service providers
        let getAllProviders = function () {
            window.INVITATION.call(
                '/registry/invitation-service-providers',
                'GET',
                null,
                (result) => {
                    if (result.success == true) {
                        _ul = $('#invitation-remote-service-providers');
                        _ul.empty();
                        result.data.forEach((isp) => {
                            // TODO: it should be possible to display all properties, eg. in expanded view
                            _li = $('<li>' + isp.name + '</li>');
                            _deleteButton = $('<span class="icon icon-delete"></span>');
                            _errorElement = $('<span class="settings-error"></span>');
                            _li.append(_deleteButton);
                            _li.append(_errorElement);
                            _ul.append().append(_li);
                            addIspDeleteAction(_deleteButton[0], isp.endpoint, _errorElement[0]);
                        });
                    } else {
                        document.getElementById('invitation-remote-service-providers-error').innerText = result.error_message;
                    }
                },
                function (response) {
                    document.getElementById('invitation-remote-service-providers-error').innerText = 'UNSPECIFIED ERROR';
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
                                    window.INVITATION.call(
                                        '/registry/invitation-service-provider',
                                        'DELETE',
                                        { endpoint: _endpoint },
                                        (result) => {
                                            if (result.success == true) {
                                                getAllProviders();
                                                console.log('invitation service provider with endpoint ' + endpoint + ' deleted');
                                            } else {
                                                errorElement.innerText = result.error_message;
                                            }
                                        },
                                        (response) => {
                                            errorElement.innerText = 'UNSPECIFIED ERROR';
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
                    window.INVITATION.call(
                        '/registry/invitation-service-provider',
                        'POST',
                        { endpoint: _endpoint },
                        (result) => {
                            if (result.success == true) {
                                $('div#invitation-add-remote-service-provider').addClass('hide');
                                document.getElementById('invitation-add-remote-service-provider-endpoint').value = "";
                                getAllProviders();
                            } else {
                                document.getElementById('invitation-remote-service-provider-save-error').innerText = result.error_message;
                            }
                        },
                        (response) => {
                            console.log(response.toString());
                            document.getElementById('invitation-remote-service-provider-save-error').innerText = 'UNSPECIFIED ERROR';
                        }
                    );
                }
            }
        );

        getAllProviders();

    });
})(window, jQuery);