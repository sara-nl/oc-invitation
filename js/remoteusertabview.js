(function () {

    var TEMPLATE =
        '<ul class="remoteUser"></ul>' +
        '<div class="clear-float"></div>' +
        '<div class="remote-user-container">' +
        '   <p><b>{{header}}</b></p>' +
        '   <p class="cloud-id"><label>Cloud ID:</label>{{remoteUserCloudId}}</p>' +
		'   <p><label>Name:</label><input type="text" placeholder="{{remoteUserNamePlaceholder}}">{{remoteUserName}}</input></p>' +
		'   <p><label>Institute:</label><input type="text" placeholder="{{remoteUserInstitutePlaceholder}}">{{remoteUserInstitute}}</input></p>' +
		'   <input class="submit" type="submit" value="{{submitText}}" />' +
        '</div>' +
        '<div class="loading hidden" style="height: 50px"></div>';

    var RemoteUserTabView = OCA.Files.DetailTabView.extend(
		    /** @lends OCA.RemoteUser.RemoteUserTabView.prototype */ {
            id: 'remoteUserTabView',
            className: 'tab versionsTabView',

            _template: null,

            template: function (data) {
                if (!this._template) {
                    this._template = Handlebars.compile(TEMPLATE);
                }

                return this._template(data);
            },

            initialize: function () {
                OCA.Files.DetailTabView.prototype.initialize.apply(this, arguments);

            },

            getLabel: function () {
                return t('invitation', 'Remote User');
            },

            template: function (params) {
                if (!this._template) {
                    this._template = Handlebars.compile(TEMPLATE);
                }
                var currentUser = OC.getCurrentUser();
                return this._template(_.extend({
                    avatarEnabled: this._avatarsEnabled,
                    actorId: currentUser.uid,
                    actorDisplayName: currentUser.displayName
                }, params));
            },

            canDisplay: function (fileInfo) {
                // only display for federated share
                return fileInfo && (typeof(fileInfo.get('shareOwner')) !== 'undefined');
            },

            render: function () {
                this.$el.html(this.template({
                    header: t('invitation', 'Add the remote user to your address book'),
                    remoteUserCloudId: this.model.get('shareOwner'),
                    remoteUserNamePlaceholder: t('invitation', 'name'),
                    remoteUserName: '',
                    remoteUserInstitutePlaceholder: t('invitation', 'institute'),
                    remoteUserInstitute: '',
                    submitText: t('invitation', 'Save'),
                }));
            },

        }
    );
    OCA.RemoteUser = OCA.RemoteUser || {};

    OCA.RemoteUser.RemoteUserTabView = RemoteUserTabView;
})();