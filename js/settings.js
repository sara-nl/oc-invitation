(function (document, $) {
    $(document).ready(function () {


        /**
         * 
         * @param {*} route 
         * @param {*} method 
         * @param {*} paramsObject 
         * @param {*} callback calls callBack(response.json)
         * @param {*} errorCallback calls errorCallback(response)
         */
        var call = function (route, method, paramsObject, callback, errorCallback) {
            var path = trimSlashes(route);
            var endpoint = OC.generateUrl('/apps/invitation/' + path);
            var options = {
                method: method,
                headers: {
                    'Content-type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify(paramsObject)
            };
            fetch(endpoint, options).then((response) => {
                return response.json();
            }).then((json) => {
                if (json.success == true || json.success == false) {
                    callback(json);
                } else {
                    console.log('could not set the domain');
                    errorCallback(JSON.stringify(json));
                }
            }).catch((response) => {
                console.log('status: ' + response.status);
                errorCallback(response);
            });
        }

        var trimSlashes = function (text) {
            return text.trim().replace(/^\/+|\/+$/g, '');
        }

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

                call(
                    '/service-provider/share-with-invited-users-only',
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

                call(
                    '/registry/domain',
                    'PUT',
                    { domain: document.getElementById('invitation-service-endpoint').value },
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
})(document, jQuery);