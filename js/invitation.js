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
                event.preventDefault();
                event.stopPropagation();
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
            let baseUrl = OC.generateUrl('/apps/invitation/find-all-invitations?fields=' + JSON.stringify(criteria));
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

        let renderOpenInvitations = function (invitations) {
            console.log("invitations: " + JSON.stringify(invitations));
            table = $('div.invites div.open tbody');
            table.empty();
            invitations.forEach((invitation) => {
                table.append(
                    '<tr><td>' + invitation.sentReceived
                    + '</td><td>' + invitation.token.substring(0, 12) + '...'
                    + '</td><td>' + invitation.remoteUserName
                    + '</td><td>' + invitation.remoteUserCloudId
                    + '</td><td>' + invitation.remoteUserEmail
                    + '</td></tr>');
            });
        };

        let renderAcceptedInvitations = function (invitations) {
            console.log("invitations: " + JSON.stringify(invitations));
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