(function (document, $) {
    $(document).ready(function () {
        let generateInvite = function (email, message) {
            $('#invitation-message span').text("");
            let baseUrl = OC.generateUrl('/apps/invitation/generate-invite?email=' + email + '&message=' + message);
            let options = {
                'method': 'GET',
                'headers': {
                    'Content-type': 'application/json;charset=utf-8'
                }
            };
            let response = fetch(baseUrl, options)
                .then(
                    (response) => {
                        return response.json();
                    }
                ).then(
                    (json) => {
                        if (json.success == true) {
                            $('#invitation-message span.message').text(json.message);
                        } else {
                            $('#invitation-message span.error').text(json.error_message);
                        }
                        getInvitations([{ "status": "open" }], renderOpenInvitations);
                    }
                ).catch(
                    (response) => {
                        $('#invitation-error span').text('ERROR_UNSPECIFIED');
                    }
                );
        };

        let generateInviteButton = document.getElementById('create-invitation');
        generateInviteButton.addEventListener(
            "click", function (event) {
                generateInvite(
                    document.getElementById('create-invitation-email').value,
                    document.getElementById('create-invitation-message').value
                )
            }
        );

        /**
         * 
         * @param {*} criteria eg. [{ "status": "open" }, { "status": "new" }]
         */
        let getInvitations = function (criteria, renderer) {
            let endpoint = OC.generateUrl('/apps/invitation/find-all-invitations?fields=' + JSON.stringify(criteria));
            let options = {
                'method': 'GET',
                'headers': {
                    'Content-type': 'application/json;charset=utf-8'
                }
            };
            let response = fetch(endpoint, options)
                .then(
                    (response) => {
                        return response.json();
                    }
                ).then(
                    (json) => {
                        if (json.success == true) {
                            if (json.invitations) {
                                renderer(json.invitations);
                            }
                        } else {
                            $('#invitation-error span').text(json.error_message);
                        }
                    }
                ).catch(
                    (response) => {
                        $('#invitation-error span').text('ERROR_UNSPECIFIED');
                    }
                );
        };

        let updateInvite = function (token, status) {
            let endpoint = OC.generateUrl('/apps/invitation/update-invitation');
            let options = {
                method: 'PUT',
                headers: {
                    'Content-type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify({ token: token, status: status })
            };
            let response = fetch(endpoint, options)
                .then(
                    (response) => {
                        return response.json();
                    }
                ).then(
                    (json) => {
                        if (json.success == true) {
                            getInvitations([{ "status": "accepted" }], renderAcceptedInvitations)
                            getInvitations([{ "status": "open" }], renderOpenInvitations);
                        } else {
                            OC.dialogs.alert(json.error_message, 'Update invitation error');
                        }
                    }
                ).catch(
                    (response) => {
                        OC.dialogs.alert('ERROR_UNSPECIFIED', 'Update invitation error');
                    }
                );
        };

        let acceptInvite = function (token) {
            let endpoint = OC.generateUrl('/apps/invitation/accept-invite?token=' + token);
            let options = {
                method: 'GET',
                headers: {
                    'Content-type': 'application/json;charset=utf-8'
                }
            };
            let response = fetch(endpoint, options)
                .then(
                    (response) => {
                        return response.json();
                    }
                ).then(
                    (json) => {
                        if (json.success == true) {
                            getInvitations([{ "status": "accepted" }], renderAcceptedInvitations)
                            getInvitations([{ "status": "open" }], renderOpenInvitations);
                        } else {
                            OC.dialogs.alert(json.error_message, 'Accept invitation error');
                        }
                    }
                ).catch(
                    (response) => {
                        OC.dialogs.alert('ERROR_UNSPECIFIED', 'Accept invitation error');
                    }
                );
        };

        let renderOpenInvitations = function (invitations) {
            table = $('div.invites div.open tbody');
            table.empty();
            invitations.forEach((invitation) => {
                var acceptButton = $('<a class="pure-button" href="#">accept</a>');
                acceptButton.on(
                    "click", function (event) {
                        acceptInvite(invitation.token, 'accepted')
                    }
                );
                var declineButton = $('<a class="pure-button" href="#">decline</a>');
                declineButton.on(
                    "click", function (event) {
                        updateInvite(invitation.token, 'declined')
                    }
                );
                table.append(
                    '<tr><td>' + invitation.sentReceived
                    + '</td><td>' + invitation.token.substring(0, 12) + '...'
                    + '</td><td>' + invitation.remoteUserName
                    + '</td><td>' + invitation.remoteUserCloudId
                    + '</td><td>' + invitation.remoteUserEmail
                    + '</td><td class="button-holder" data-accept-invite="' + invitation.token + '">'
                    + '</td><td class="button-holder" data-decline-invite="' + invitation.token + '">'
                    + '</td></tr>');
                $('td[data-accept-invite="' + invitation.token + '"]').append(acceptButton);
                $('td[data-decline-invite="' + invitation.token + '"]').append(declineButton);
            });
        };

        let renderAcceptedInvitations = function (invitations) {
            table = $('div.invites div.accepted tbody');
            table.empty();
            invitations.forEach((invitation) => {
                table.append(
                    '<tr><td>' + invitation.sentReceived
                    + '</td><td>' + invitation.remoteUserName
                    + '</td><td>' + invitation.remoteUserCloudId
                    + '</td><td>' + invitation.remoteUserEmail
                    + '</td></tr>');
            });
        };

        getInvitations([{ "status": "accepted" }], renderAcceptedInvitations)
        getInvitations([{ "status": "open" }], renderOpenInvitations);
    });
})(document, jQuery);