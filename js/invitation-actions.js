(function (window, $) {

    /**
     * @callback errorCallback the general call error callback
     * @param {string} jsonString the error as json string
     */

    /**
     * 
     * @param string email
     * @param string senderName
     * @param string message
     */
    let _sendInvite = function (email, senderName, message, successCallback, errorCallback) {
        $('#invitation-message span').text("");
        window.INVITATION_SERVICE.call(
            "generate-invite",
            "POST",
            { email: email, senderName: senderName, message: message },
            successCallback,
            errorCallback
        );
    };

    /**
     * @callback renderer renders the specified data
     * @param {Object} responseJsonDataObject response json data object
     * 
     * @callback errorCallback the error callback
     * @param {string} errorMessage the error message
     */
    /**
     * 
     * @param {*} criteria eg. [{ "status": "open" }, { "status": "new" }]
     * @param {renderer} renderer the renderer
     * @param {errorCallback} errorCallback the error callback
     */
    let _getInvitations = function (criteria, renderer, errorCallback) {
        window.INVITATION_SERVICE.call(
            "find-all-invitations?fields=" + JSON.stringify(criteria),
            "GET",
            null,
            (result) => {
                renderer(result.data);
            },
            errorCallback
        );
    };

    /**
     * @callback updateInviteCallback
     * @param {JSON} result the result {"success": bool, "data": object}
     */
    /**
     * 
     * @param {string} token 
     * @param {string} status 
     * @param {updateInviteCallback} updateInviteCallback callback on success
     * @param {errorCallback} errorCallback the error callback
     */
    let _updateInvite = function (token, status, updateInviteCallback, errorCallback) {
        window.INVITATION_SERVICE.call(
            "update-invitation",
            "PUT",
            { token: token, status: status },
            updateInviteCallback,
            errorCallback
        );
    };

    let _acceptInvite = function (token, successCallback, errorCallback) {
        window.INVITATION_SERVICE.call(
            "accept-invite/" + token,
            "PUT",
            null,
            successCallback,
            errorCallback
        );
    };

    let _INVITATION_ACTIONS = {
        sendInvite: _sendInvite,
        getInvitations: _getInvitations,
        updateInvite: _updateInvite,
        acceptInvite: _acceptInvite
    };

    window.INVITATION_ACTIONS = _INVITATION_ACTIONS;

})(window, jQuery);