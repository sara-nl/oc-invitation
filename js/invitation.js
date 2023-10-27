(function (document, $) {
    $(document).ready(function () {
        // document.getElementById('elem').onclick = function () {

        let generateInvite = function (email, senderName) {
            $('#invitation-error span').text("");
            let baseUrl = OC.generateUrl('/apps/rd-mesh/generate-invite?email=' + email + '&senderName=' + senderName);
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
                            console.log("invite link '" + json.inviteLink + "' has been send to " + email);
                        } else {
                            $('#invitation-error span').text(json.error_code);
                        }
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
                    document.getElementById('create-invitation-senderName').value
                )
            }
        );
    });
})(document, jQuery);