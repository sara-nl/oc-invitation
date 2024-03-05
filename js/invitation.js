(function (window, $) {
    $(window.document).ready(function () {
        var document = window.document;
        let generateInvite = function (email, message) {
            $('#invitation-message span').text("");
            // let baseUrl = OC.generateUrl('/apps/invitation/generate-invite?email=' + email + '&message=' + message);
            window.INVITATION.call(
                "generate-invite",
                "POST",
                { email: email, message: message },
                (result) => {
                    if (result.success == true) {
                        if ($('input[value="deploy_mode_test"]').size() === 1) {
                            $('#invitation-message span.message').html(
                                ' <div id="invitation-message-accordion">'
                                + '<h5>' + t('invitation', 'Your invitation has been sent to') + ' ' + result.email + '.</h5>'
                                + '<div><p>Invite link: <a href="' + result.inviteLink + '">' + result.inviteLink + '</a></p></div>'
                                + '</div>'
                            );
                            $("#invitation-message-accordion").accordion({ collapsible: true, active: false });
                        } else {
                            $('#invitation-message span.message').html(
                                ' <div">'
                                + '<h5>' + t('invitation', 'Your invitation has been sent to') + ' ' + result.email + '</h5>'
                                + '</div>'
                            );
                        }
                    } else {
                        $('#invitation-message span.error').text(t('invitation', result.error_message));
                    }
                    getInvitations([{ "status": "open" }], renderOpenInvitations);
                },
                (response) => {
                    if ($('input[value="deploy_mode_test"]').size() === 1) {
                        console.log(response.toString());
                    }
                    $('#invitation-message span.error').text(t('invitation', 'ERROR_UNSPECIFIED'));
                }
            );
        };

        let generateInviteButton = document.getElementById('create-invitation');
        generateInviteButton.addEventListener(
            "click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                generateInvite(
                    document.getElementById('create-invitation-email').value,
                    document.getElementById('create-invitation-message').value.replace(/\n/g, "__LINE_BREAK__")
                )
            }
        );

        /**
         * 
         * @param {*} criteria eg. [{ "status": "open" }, { "status": "new" }]
         */
        let getInvitations = function (criteria, renderer) {
            window.INVITATION.call(
                "find-all-invitations?fields=" + JSON.stringify(criteria),
                "GET",
                null,
                (result) => {
                    if (result.success == true) {
                        if (result.data) {
                            renderer(result.data);
                        }
                    } else {
                        $('#invitation-error span').text(result.error_message);
                    }
                },
                (response) => {
                    if ($('input[value="deploy_mode_test"]').size() === 1) {
                        console.log(response.toString());
                    }
                    $('#invitation-error span').text('ERROR_UNSPECIFIED');
                }
            );
        };

        let updateInvite = function (token, status) {
            $('#invitation-message span').text("");
            window.INVITATION.call(
                "update-invitation",
                "PUT",
                { token: token, status: status },
                (result) => {
                    if (result.success == true) {
                        getInvitations([{ "status": "accepted" }], renderAcceptedInvitations)
                        getInvitations([{ "status": "open" }], renderOpenInvitations);
                    } else {
                        OC.dialogs.alert(result.error_message, 'Update invitation error');
                    }
                },
                (response) => {
                    if ($('input[value="deploy_mode_test"]').size() === 1) {
                        console.log(response.toString());
                    }
                    OC.dialogs.alert('ERROR_UNSPECIFIED', 'Update invitation error');
                }
            );
        };

        let acceptInvite = function (token) {
            window.INVITATION.call(
                "accept-invite/" + token,
                "PUT",
                null,
                (result) => {
                    if (result.success == true) {
                        getInvitations([{ "status": "accepted" }], renderAcceptedInvitations)
                        getInvitations([{ "status": "open" }], renderOpenInvitations);
                    } else {
                        OC.dialogs.alert(t('invitation', result.error_message), t('invitation', 'Accept invitation error'));
                    }
                },
                (response) => {
                    if ($('input[value="deploy_mode_test"]').size() === 1) {
                        console.log(response.toString());
                    }
                    OC.dialogs.alert('ERROR_UNSPECIFIED', 'Accept invitation error');
                }
            );
        };

        let invitationButton = function (status, token) {
            if (status === 'accepted') {
                var acceptButton = $('<a class="pure-button" href="#">' + t('invitation', 'accept') + '</a>');
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
                var declineButton = $('<a class="pure-button" href="#">' + t('invitation', 'decline') + '</a>');
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
                var revokeButton = $('<a class="pure-button" href="#">' + t('invitation', 'revoke') + '</a>');
                revokeButton.on(
                    "click", function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        updateInvite(token, 'revoked')
                    }
                );
                return revokeButton;
            }
            if (status === 'withdrawn') {
                var revokeButton = $('<a class="pure-button" href="#">' + t('invitation', 'withdraw') + '</a>');
                revokeButton.on(
                    "click", function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        updateInvite(token, 'withdrawn')
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
                    '<tr><td>' + t('invitation', invitation.sentReceived)
                    + '</td><td>' + invitation.remoteUserName
                    + '</td><td>' + invitation.remoteUserProviderName
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
                    '<tr><td>' + t('invitation', invitation.sentReceived)
                    + '</td><td>' + invitation.remoteUserName
                    + '</td><td>' + invitation.remoteUserProviderName
                    + '</td><td>' + invitation.remoteUserEmail
                    + '</td><td>' + invitation.remoteUserCloudId
                    + '</td><td class="button-holder" data-withdraw-invite="' + invitation.token + '">'
                    + '</td></tr>');
                $('td[data-withdraw-invite="' + invitation.token + '"]').append(invitationButton('withdrawn', invitation.token));
            });
        };

        getInvitations([{ "status": "accepted" }], renderAcceptedInvitations);
        getInvitations([{ "status": "open" }], renderOpenInvitations);
    });
})(window, jQuery);