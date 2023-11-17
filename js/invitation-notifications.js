(function (document, $) {
    $(document).ready(function () {
        let declineInvitationUrl = OC.generateUrl('/apps/invitation/decline-invite');
        let declineInvitationMethod = 'PUT';
        $('body').on('OCA.Notification.Action', function (e) {
            if (e.notification.app === 'invitation') {
                var token = e.notification.object_id;
                var declineLink;
                _.each(e.notification.actions, function (actionData) {
                    if (actionData.label.toLowerCase() == 'decline') {
                        declineLink = actionData.link;
                    }
                });

                OC.dialogs.confirm(
                    e.notification.subject + '\n'
                    + 'Do you want to decline?',
                    'Decline invitation?',
                    function (confirmed) {
                        if (confirmed) {
                            response = declineInvitation(declineInvitationUrl, declineInvitationMethod, token);
                        }
                    }
                );
            }
        });

        /**
         * 
         * @param {string} declineLink the decline link
         * @param {string} method the decline link HTTP method
         */
        let declineInvitation = function (declineLink, method, token) {
            let options = {
                method: method.toUpperCase(),
                headers: {
                    'Content-type': 'application/json'
                },
                body: JSON.stringify({ token: token })
            };
            let response = fetch(declineLink, options)
                .then(
                    (response) => {
                        return response.json();
                    }
                ).then(
                    (json) => {
                        if (json.success == true) {
                            console.log('successfully declined invitation');
                        } else {
                            OC.dialogs.alert(json.error_message, 'Decline invitation error');
                        }
                    }
                ).catch(
                    (response) => {
                        OC.dialogs.alert('ERROR_UNSPECIFIED', 'Decline invitation error');
                    }
                );
        };
    })
})(document, jQuery);
