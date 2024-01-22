(function (window, $) {
    $(window.document).ready(function () {
        var document = window.document;
        // TODO: use the inv library for the calls

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
                            // $('#invitation-message span.message').html('Your invitation has been sent to ' + json.email + '.');
                            $('#invitation-message span.message').html(
                                ' <div id="invitation-message-accordion">'
                                + '<h5>Your invitation has been sent to ' + json.email + '.</h5>'
                                + '<div><p>Invite link: <a href="' + json.inviteLink + '">' + json.inviteLink + '</a></p></div>'
                                + '</div>'
                            );
                            $("#invitation-message-accordion").accordion({ collapsible: true, active: false });
                        } else {
                            $('#invitation-message span.error').text(json.error_message);
                        }
                        getInvitations([{ "status": "open" }], renderOpenInvitations);
                    }
                ).catch(
                    (response) => {
                        $('#invitation-message span.error').text('ERROR_UNSPECIFIED');
                    }
                );
        };

        let generateInviteButton = document.getElementById('create-invitation');
        // FIXME: get message linebreaks: document.getElementById('create-invitation-message').value.replace(/\n/g, "<br/>");
        generateInviteButton.addEventListener(
            "click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                generateInvite(
                    document.getElementById('create-invitation-email').value,
                    encodeURI(document.getElementById('create-invitation-message').value)
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
                            if (json.data) {
                                renderer(json.data);
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
            $('#invitation-message span').text("");
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
            let endpoint = OC.generateUrl('/apps/invitation/accept-invite/' + token);
            let options = {
                method: 'POST',
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

        let invitationButton = function (status, token) {
            if (status === 'accepted') {
                var acceptButton = $('<a class="pure-button" href="#">accept</a>');
                acceptButton.on(
                    "click", function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        acceptInvite(token, 'accepted')
                    }
                );
                return acceptButton;
            }
            if (status === 'declined') {
                var declineButton = $('<a class="pure-button" href="#">decline</a>');
                declineButton.on(
                    "click", function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        updateInvite(token, 'declined')
                    }
                );
                return declineButton;
            }
            if (status === 'revoked') {
                var revokeButton = $('<a class="pure-button" href="#">revoke</a>');
                revokeButton.on(
                    "click", function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        updateInvite(token, 'revoked')
                    }
                );
                return revokeButton;
            }
        }

        let renderOpenInvitations = function (invitations) {
            table = $('div.invites div.open tbody');
            table.empty();
            invitations.forEach((invitation) => {
                table.append(
                    '<tr><td>' + invitation.sentReceived
                    + '</td><td>' + invitation.token.substring(0, 12) + '...'
                    + '</td><td>' + invitation.remoteUserName
                    + '</td><td>' + invitation.remoteUserCloudId
                    + '</td><td>' + invitation.remoteUserEmail
                    + '</td><td class="button-holder" data-accept-invite="' + invitation.token + '">'
                    + '</td><td class="button-holder" data-decline-revoke-invite="' + invitation.token + '">'
                    + '</td></tr>');
                if (invitation.sentReceived === 'received') {
                    $('td[data-accept-invite="' + invitation.token + '"]').append(invitationButton('accepted', invitation.token));
                    $('td[data-decline-revoke-invite="' + invitation.token + '"]').append(invitationButton('declined', invitation.token));
                }
                if (invitation.sentReceived === 'sent') {
                    $('td[data-decline-revoke-invite="' + invitation.token + '"]').append(invitationButton('revoked', invitation.token));
                }
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
})(window, jQuery);