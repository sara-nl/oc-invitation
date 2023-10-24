(function (document, $) {
    $(document).ready(function () {
        // document.getElementById('elem').onclick = function () {

        let generateInvite = function (email, senderName) {
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
                        // console.log(response);
                        if (response.ok) {
                            response.json().then(
                                (data) => {
                                    console.log("The following invite link has been send: " + data.inviteLink);
                                }
                            );
                        } else {
                            throw new Error(response);
                        }
                    }
                ).catch(
                    (response) => {
                        console.log('error response: ' + response);
                    }
                );
            console.log(response);
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