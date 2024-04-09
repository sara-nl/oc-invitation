(function (window, $) {

    let _updateInvite = function (token, status) {
        window.INVITATION_ACTIONS.updateInvite(
            token,
            status,
            (result) => {
                if (result.success == true) { // should always be the case
                    window.INVITATION_ACTIONS.getInvitations([{ "status": "open" }], window.INVITATION.renderOpenInvitations);
                    window.INVITATION_ACTIONS.getInvitations([{ "status": "accepted" }], window.INVITATION.renderAcceptedInvitations)
                }
            },
            (errorMessage) => {
                if ($('input[value="deploy_mode_test"]').size() === 1) {
                    console.log(errorMessage);
                }
                OC.dialogs.alert(t('invitation', errorMessage), t('invitation', 'UPDATE_INVITATION_ERROR'));
            }
        );
    }

    let _acceptInvite = function (token) {
        window.INVITATION_ACTIONS.acceptInvite(
            token,
            (result) => {
                if (result.success == true) { // should always be the case
                    window.INVITATION_ACTIONS.getInvitations([{ "status": "open" }], window.INVITATION.renderOpenInvitations);
                    window.INVITATION_ACTIONS.getInvitations([{ "status": "accepted" }], window.INVITATION.renderAcceptedInvitations)
                }
            },
            (errorMessage) => {
                if ($('input[value="deploy_mode_test"]').size() === 1) {
                    console.log(errorMessage);
                }
                OC.dialogs.alert(t('invitation', errorMessage), t('invitation', 'ACCEPT_INVITATION_ERROR'));
            }

        );
    }

    let invitationButton = function (status, token, recipientCloudId, recipientName, recipientEmail) {
        if (status === 'accepted') {
            var acceptButton = $('<a class="pure-button" data-recipientCloudId="' + recipientCloudId + '" data-recipientName="' + recipientName + '" data-recipientEmail="' + recipientEmail + '" href="#">' + t('invitation', 'accept') + '</a>');
            acceptButton.on(
                "click", function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    var recipientCloudId = event.currentTarget.dataset.recipientcloudid;
                    var recipientName = event.currentTarget.dataset.recipientname;
                    var recipientEmail = event.currentTarget.dataset.recipientemail;
                    $('div#invitation-index-message span.message').empty();
                    let dialogClass = "accept-invitation-confirmation-dialog";
                    OC.dialogs.message(
                        '',
                        'Please note that the following information will be shared with the sender of the invitation: ',
                        '',
                        OCdialogs.YES_BUTTON,
                        function () {
                            _acceptInvite(token);
                        },
                        true,
                        dialogClass
                    ).done(() => { // here we render the actual message which includes html
                        $('.' + dialogClass).empty().append(
                            '<p>'
                            + t('invitation', 'Your cloud ID: {cloudId}', { "cloudId": recipientCloudId }) + '<br>'
                            + t('invitation', 'Your name: {name}', { "name": recipientName }) + '<br>'
                            + t('invitation', 'Your email: {email}', { "email": recipientEmail }) + '<br>'
                            + '<br>'
                            + '</p>'
                        );
                    });
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
                    $('div#invitation-index-message span.message').empty();
                    _updateInvite(token, 'declined');
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
                    $('div#invitation-index-message span.message').empty();
                    _updateInvite(token, 'revoked');
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
                    $('div#invitation-index-message span.message').empty();
                    _updateInvite(token, 'withdrawn');
                }
            );
            return revokeButton;
        }
    };

    /**
     * These fields must be set.
     * @param {string} email the email of the recipient
     * @param {string} senderName the name of the sender
     * @returns {string} empty string if fields are valid, else a translated error message
     */
    let validateInvitationFields = function (email, senderName) {
        if (typeof email !== "string" || email.trim() === "") {
            return t('invitation', "CREATE_INVITATION_NO_RECIPIENT_EMAIL")
        }
        if (typeof senderName !== "string" || senderName.trim() === "") {
            return t('invitation', "CREATE_INVITATION_NO_SENDER_NAME")
        }
        return "";
    }

    let _renderOpenInvitations = function (invitations) {
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
                $('td[data-accept-invite="' + invitation.token + '"]').append(invitationButton('accepted', invitation.token, invitation.recipientCloudId, invitation.recipientName, invitation.recipientEmail));
                $('td[data-decline-revoke-invite="' + invitation.token + '"]').append(invitationButton('declined', invitation.token));
            }
            if (invitation.sentReceived === 'sent') {
                $('td[data-decline-revoke-invite="' + invitation.token + '"]').append(invitationButton('revoked', invitation.token));
            }
        });
    };

    let _renderAcceptedInvitations = function (invitations) {
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

    /**
     * 
     * @param {string} cloudId the cloud ID of the current user
     */
    let _initInvitationForm = function (cloudId) {
        let createInviteButton = document.getElementById('create-invitation');
        createInviteButton.addEventListener(
            "click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                let dialogClass = "create-invitation-confirmation-dialog";
                let email = document.getElementById('create-invitation-email').value;
                let yourName = document.getElementById('create-invitation-senderName').value;
                let message = document.getElementById('create-invitation-message').value.replace(/\n/g, "__LINE_BREAK__");
                let validateErrorMessage = validateInvitationFields(email, yourName);
                if ("" === validateErrorMessage) {
                    $('#invitation-message span.error').text("");
                    OC.dialogs.message(
                        '',
                        t('invitation', 'confirmation-header'),
                        '',
                        OCdialogs.YES_BUTTON,
                        function () {
                            window.INVITATION_ACTIONS.sendInvite(
                                email,
                                yourName,
                                message,
                                (result) => {
                                    if ($('input[value="deploy_mode_test"]').size() === 1) {
                                        $('#invitation-index-message span.message').html(
                                            ' <div id="invitation-message-accordion">'
                                            + '<h5>' + t('invitation', 'Your invitation has been sent to', { recipient: result.data.email }) + '</h5>'
                                            + '<div><p>Invite link: <a href="' + result.data.inviteLink + '">' + result.data.inviteLink + '</a></p></div>'
                                            + '</div>'
                                        );
                                        $("#invitation-message-accordion").accordion({ collapsible: true, active: false });
                                    } else {
                                        $('#invitation-index-message span.message').html(
                                            ' <div">'
                                            + '<h5>' + t('invitation', 'Your invitation has been sent to', { recipient: result.data.email }) + '</h5>'
                                            + '</div>'
                                        );
                                    }
                                    window.INVITATION_ACTIONS.getInvitations([{ "status": "open" }], window.INVITATION.renderOpenInvitations);
                                    window.INVITATION.closeInvitationForm();
                                },
                                function (errorMessage) {
                                    $('#invitation-message span.error').append(' ' + t('invitation', errorMessage));
                                }
                            );
                        },
                        true,
                        dialogClass
                    ).done(() => { // this prevents the message dialog to remove the html present in the translation
                        $('.' + dialogClass).empty().append(
                            t('invitation', 'confirmation', { "cloudId": cloudId, "name": yourName, "email": email })
                        );
                    });
                } else {
                    $('#invitation-message span.error').text(validateErrorMessage);
                }
            }
        );
    };

    let _closeInvitationForm = function closeInvitationForm() {
        $('#invitation-form-container').ocdialog('destroy').remove();
        $("div#create-invitation-overlay").removeClass("create-invitation-overlay-on");
    };

    /**
     * 
     * @param {array} status one or more statuses, eg. [{ "status": "open" }, { "status": "new" }]
     */
    let _listInvitations = function (status, renderer) {
        window.INVITATION_ACTIONS.getInvitations(
            status,
            renderer,
            (errorMessage) => {
                if ($('input[value="deploy_mode_test"]').size() === 1) {
                    console.log(errorMessage);
                }
                v = [];
                status.forEach((e) => v.push(t('invitation', e.status)));
                $('#invitation-index-message span.error').append(' ' + t('invitation', 'GET_INVITATIONS_ERROR_UNSPECIFIED', { status: v.join() }) + '<br>');
            }
        );
    };
    /**
     * This prevents the default ocdialog behaviour for enter keypress
     * @param {*} event 
     */
    let _catchEnter = function (event) {
        if (event.keyCode === 13) {
            event.stopImmediatePropagation();
        }
    }

    let _INVITATION = {
        renderOpenInvitations: _renderOpenInvitations,
        renderAcceptedInvitations: _renderAcceptedInvitations,
        initInvitationForm: _initInvitationForm,
        closeInvitationForm: _closeInvitationForm,
        listInvitations: _listInvitations,
        catchEnter: _catchEnter
    };

    window.INVITATION = _INVITATION;

    $(window.document).ready(function () {
        var document = window.document;

        let openInvitationFormButton = document.getElementById('open-invitation-form');
        openInvitationFormButton.addEventListener(
            "click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                $('div#invitation-index-message span.message').empty();
                $("div#create-invitation-overlay").addClass("create-invitation-overlay-on");
                window.INVITATION_SERVICE.call(
                    "invitation-form",
                    "GET",
                    null,
                    (result) => {
                        if (result.success == true) {
                            if (result.data) {
                                $('#content').append('<div id="invitation-form-container">' + result.data + '</div>');
                                $('#invitation-form-container').ocdialog({
                                    width: 500,
                                    closeOnEscape: true,
                                    modal: true,
                                    buttons: [{
                                        text: t('core', 'Cancel'),
                                        classes: 'cancel',
                                        click: function () {
                                            window.INVITATION.closeInvitationForm();
                                        }
                                    }],
                                    close: function () {
                                        window.INVITATION.closeInvitationForm();
                                    }
                                });
                            }
                        } else {
                            $('#invitation-error span').text(result.error_message);
                        }
                    },
                    (response) => {
                        if ($('input[value="deploy_mode_test"]').size() === 1) {
                            console.log(response.toString());
                        }
                        $('#invitation-error span').text(t('invitation', 'ERROR_UNSPECIFIED'));
                    }
                );
            }
        );

        window.INVITATION.listInvitations([{ "status": "open" }], window.INVITATION.renderOpenInvitations);
        window.INVITATION.listInvitations([{ "status": "accepted" }], window.INVITATION.renderAcceptedInvitations);

        $(".information").html(t('invitation', 'explanation'));
    });
})(window, jQuery);