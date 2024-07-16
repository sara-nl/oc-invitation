/*
 * Copyright (c) 2015
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

(function () {
	OCA.RemoteUser = OCA.RemoteUser || {};

	/**
	 * @namespace
	 */
	OCA.RemoteUser.Util = {
		/**
		 * Initialize the versions plugin.
		 *
		 * @param {OCA.Files.FileList} fileList file list to be extended
		 */
		attach: function (fileList) {
			if (fileList.id === 'trashbin' || fileList.id === 'files.public') {
				return;
			}

			fileList.registerTabView(new OCA.RemoteUser.RemoteUserTabView('remoteUserTabView', { order: -20 }));

			$.each($('a[data-action="Share"]'), function (key, actionShare) {
				let share = actionShare.closest('tr')
				$(actionShare).on('click', function () {
					if ($(share).attr('data-share-owner')) {
						console.log('remote share');
						$('[data-tabid="remoteUserTabView"]').css('display', 'block');
					} else {
						console.log('local share');
						$('[data-tabid="remoteUserTabView"]').css('display', 'none');
					}
				});
			});
		}
	};
})();

OC.Plugins.register('OCA.Files.FileList', OCA.RemoteUser.Util);