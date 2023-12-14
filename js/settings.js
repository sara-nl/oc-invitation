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
                        document.getElementById('allow-sharing-with-invited-users-only-error').innerText = "ERROR";
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

                window.INVITATION.call(
                    '/endpoint',
                    'PUT',
                    { endpoint: document.getElementById('invitation-service-endpoint').value },
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
                        document.getElementById('invitation-service-settings-endpoint-error').innerText = "ERROR";
                        console.log(response.toString());
                    }
                )
            }
        );

    });
})(window, jQuery);