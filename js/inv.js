(function () {
    var trimSlashes = function (text) {
        return text.trim().replace(/^\/+|\/+$/g, '');
    }

    /**
     * 
     * @param {*} route 
     * @param {*} method 
     * @param {*} paramsObject 
     * @param {*} callback calls callBack(response.json)
     * @param {*} errorCallback calls errorCallback(response)
     */
    let _call = function (route, method, paramsObject, callback, errorCallback) {
        var path = trimSlashes(route);
        var endpoint = OC.generateUrl('/apps/invitation/' + path);
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
            return response.json();
        }).then((json) => {
            if (json.success == true || json.success == false) {
                callback(json);
            } else {
                console.log('could not set the endpoint');
                errorCallback(JSON.stringify(json));
            }
        }).catch((response) => {
            console.log('status: ' + response.status);
            errorCallback(response);
        });
    }

    let _INV = {
        call: _call
    };

    window.INVITATION = _INV;
})(window);