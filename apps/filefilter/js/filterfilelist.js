/*
 * Copyright (c) 2014 Vincent Petry <pvince81@owncloud.com>
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */
(function() {

	/**
	 * @class OCA.Filefilter.FileList
	 * @augments OCA.Files.FileList
	 * 列出所有不同类型文件信息
	 **/
	var FileList = function($el, options) {
		this.initialize($el, options);
	};


	FileList.prototype = _.extend({}, OCA.Files.FileList.prototype,
		/** @lends OCA.Filefilter.FileList.prototype */ {
		appName: 'Filefilter',

		/**
		 * @private
		 */
		initialize: function($el, options) {
			OCA.Files.FileList.prototype.initialize.apply(this, arguments);
			if (this.initialized) {
				return;
			}
		},

		updateEmptyContent: function() {
			var dir = this.getCurrentDirectory();
			if (dir === '/') {
				// root has special permissions
				this.$el.find('#emptycontent').toggleClass('hidden', !this.isEmpty);
				this.$el.find('#filestable thead th').toggleClass('hidden', this.isEmpty);
			}
			else {
				OCA.Files.FileList.prototype.updateEmptyContent.apply(this, arguments);
			}
		},

		getDirectoryPermissions: function() {
			return OC.PERMISSION_READ | OC.PERMISSION_DELETE;
		},

		updateStorageStatistics: function() {
			// no op because it doesn't have
			// storage info like free space / used space
		},

		reload: function() {
			this.showMask();
			if (this._reloadCall) {
				this._reloadCall.abort();
			}
			//获取当前页面view参数
			var url = window.location.search;
			var loc = url.substring(url.lastIndexOf('=')+1, url.length);
			//将view参数也放到参数中
			this._reloadCall = $.ajax({
				url: OC.filePath('filefilter', 'ajax', 'list.php')+"?view="+loc,
				data: {
					format: 'json',
					sort: this._sort,
					sortdirection: this._sortDirection
				},
				type: 'GET',
			});
			var callBack = this.reloadCallback.bind(this);
			return this._reloadCall.then(callBack, callBack);
		},

		reloadCallback: function(result) {
			delete this._reloadCall;
			this.hideMask();

			if (!result || result.status === 'error') {
				// if the error is not related to folder we're trying to load, reload the page to handle logout etc
				if (result.data.error === 'authentication_error' ||
					result.data.error === 'token_expired' ||
					result.data.error === 'application_not_enabled'
				) {
					OC.redirect(OC.generateUrl('apps/files'));
				}
				OC.Notification.show(result.data.message);
				return false;
			}

			if (result.status === 404) {
				// go back home
				this.changeDirectory('/');
				return false;
			}
			// aborted ?
			if (result.status === 0){
				return true;
			}

			// TODO: should rather return upload file size through
			// the files list ajax call
			this.updateStorageStatistics(true);

			//if (result.data.permissions) {
			//	this.setDirectoryPermissions(result.data.permissions);
			//}
			if(result.data!=null){
			this.setFiles(result.data.files);
			}
			return true;
		},
	});

	/**
	 * Mount point info attributes.
	 *
	 * @typedef {Object} OCA.Filefilter.MountPointInfo
	 *
	 * @property {String} name mount point name
	 * @property {String} scope mount point scope "personal" or "system"
	 * @property {String} backend Filefilter storage backend name
	 */
	OCA.Filefilter.FileList = FileList;
})();
