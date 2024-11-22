(function (window) {
    var trimSlashes = function (text) {
        return text.trim().replace(/^\/+|\/+$/g, '');
    }

    /**
     * @callback errorCallback
     * @param string errorMessage
     */
    /**
     * 
     * @param {Response|string} response the actual response or in case of serious errors an error string
     * @param {errorCallback} errorCallback the error callback, accepts the error message 
     */
    let fetchError = function (response, errorCallback) {
        var status = response.status;
        var statusText = response.statusText;
        if (response.json) {
            response.json().then(
                (json) => {
                    if (json.error_message) {
                        // message for user, to be returned to actual errorCallback
                        errorCallback(json.error_message);
                    }
                    // dev status log
                    console.log("status " + status + " - " + statusText);
                }
            ).catch((error) => {
                // ultimate dev error log
                console.log("status " + status + " - " + statusText);
                console.log(error);
                // return generic error message
                errorCallback("ERROR_UNSPECIFIED");
            });
        } else {
            // serious error, eg. network error
            console.log("status " + status + " - " + statusText);
            console.log(response);
        }
    };

    /**
     * @callback successCallback the success callback
     * @param {Object} result the response object returned by response.json()
     * 
     * @callback fetchError the fetch error
     * @param {Response|string} the response or an error message
     * @param {errorCallback} errorCallback the errorCallback
     */
    /**
     * Tailored for the invitation service response format.
     * Only when response.json.success is true will the successCallback be triggered.
     * All server side errors and response with success equal to false will trigger the errorCallback.
     *  
     * 
     * @param {string} route 
     * @param {string} method 
     * @param {Object} paramsObject 
     * @param {successCallback} successCallback calls successCallback(response.json)
     * @param {errorCallback} errorCallback error callback()
     */
    let _call = function (route, method, paramsObject, successCallback, errorCallback) {
        var path = trimSlashes(route);
        var endpoint = OC.generateUrl('/apps/collaboration/' + path);
        var options = {
            method: method,
            headers: {
                'Content-type': 'application/json;charset=utf-8'
            },
            body: null
        };
        // Ignore body when method is GET
        if (method.toLowerCase() !== 'get') {
            options.body = JSON.stringify(paramsObject);
        }
        fetch(endpoint, options).then((response) => {
            if (response.ok) {
                return response.json();
            }
            return response;
        }).then((json) => {
            // check for valid response format
            if (json.success) {
                successCallback(json);
            } else {
                fetchError(json, errorCallback);
            }
        }).catch((response) => {
            fetchError(response, errorCallback);
        });
    }

    let _SERVICE = {
        call: _call
    };

    window.INVITATION_SERVICE = _SERVICE;
})(window);