/*
 * author@sunny
 * 2015-11-16
 */

if (!OCA.Filefilter) {
	/**
	 * @namespace
	 */
	OCA.Filefilter = {};
}
/**
 * @namespace
 */
OCA.Filefilter.App = {

	//_docFileList: null,
	_picFileList: null,
	_vidFileList: null,
	_audFileList: null,
	_othFileList: null,


	initDocumentsList: function($el) {
		if (this._docFileList) {
			return this._docFileList;
		}

		this._docFileList = new OCA.Filefilter.FileList(
			$el,
			{
				id:'filefilter.documents',
				scrollContainer: $('#app-content'),
				fileActions: this._createFileActions()
			}
		);

		this._extendFileList(this._docFileList);
		this._docFileList.appName = t('filefilter', 'Documents');
		return this._docFileList;
	},
	initPicturesList: function($el) {
		if (this._picFileList) {
			return this._picFileList;
		}

		this._picFileList = new OCA.Filefilter.FileList(
			$el,
			{
				id:'filefilter.pictures',
				scrollContainer: $('#app-content'),
				fileActions: this._createFileActions()
			}
		);
		this._extendFileList(this._picFileList);
		this._picFileList.appName = t('filefilter', 'Pictures');
		return this._picFileList;
	},
	initVideosList: function($el) {
		if (this._vidFileList) {
			return this._vidFileList;
		}

		this._vidFileList = new OCA.Filefilter.FileList(
			$el,
			{
				id:'filefilter.videos',
				scrollContainer: $('#app-content'),
				fileActions: this._createFileActions()
			}
		);

		this._extendFileList(this._vidFileList);
		this._vidFileList.appName = t('filefilter', 'Videos');
		return this._vidFileList;
	},
	initAudiosList: function($el) {
		if (this._audFileList) {
			return this._audFileList;
		}

		this._audFileList = new OCA.Filefilter.FileList(
			$el,
			{
				id:'filefilter.audios',
				scrollContainer: $('#app-content'),
				fileActions: this._createFileActions()
			}
		);

		this._extendFileList(this._audFileList);
		this._audFileList.appName = t('filefilter', 'Audios');
		return this._audFileList;
	},
	initOthersList: function($el) {
		if (this._othFileList) {
			return this._othFileList;
		}

		this._othFileList = new OCA.Filefilter.FileList(
			$el,
			{
				id:'filefilter.others',
				scrollContainer: $('#app-content'),
				fileActions: this._createFileActions(),
			}
		);
		this._extendFileList(this._othFileList);
		this._othFileList.appName = t('filefilter', 'Others');
		return this._othFileList;
	},


	removeDocumentsList: function() {
		if (this._docFileList) {
			this._docFileList.$fileList.empty();
		}
	},
	removePicturesList: function() {
		if (this._picFileList) {
			this._picFileList.$fileList.empty();
		}
	},
	removeVideosList: function() {
		if (this._vidFileList) {
			this._vidFileList.$fileList.empty();
		}
	},
	removeAudiosList: function() {
		if (this._audFileList) {
			this._audFileList.$fileList.empty();
		}
	},
	removeOthersList: function() {
		if (this._othFileList) {
			this._othFileList.$fileList.empty();
		}
	},

	/**
	 * Destroy the app
	 */
	destroy: function() {
		OCA.Files.fileActions.off('setDefault.app-files', this._onActionsUpdated);
		OCA.Files.fileActions.off('registerAction.app-files', this._onActionsUpdated);
		window.FileActions.off('setDefault.app-files', this._onActionsUpdated);
		window.FileActions.off('registerAction.app-files', this._onActionsUpdated);

		this.removePicturesList();
		this.removeDocumentsList();
		this.removeVideosList();
		this.removeAudiosList();
		this.removeOthersList();
		this._picFileList = null;
		this._docFileList=null;
		this._vidFileList=null;
		this._audFileList=null;
		this._othFileList=null;
		delete this._globalActionsInitialized;
	},
	_createFileActions: function() {
		// inherit file actions from the files app
		var fileActions = new OCA.Files.FileActions();
		fileActions.registerDefaultActions();

		// legacy actions
		fileActions.merge(window.FileActions);
		// regular actions
		fileActions.merge(OCA.Files.fileActions);

		if (!this._globalActionsInitialized) {
			this._onActionsUpdated = _.bind(this._onActionsUpdated, this);
			OCA.Files.fileActions.on('setDefault.app-files', this._onActionsUpdated);
			OCA.Files.fileActions.on('registerAction.app-files', this._onActionsUpdated);
			window.FileActions.on('setDefault.app-files', this._onActionsUpdated);
			window.FileActions.on('registerAction.app-files', this._onActionsUpdated);
			this._globalActionsInitialized = true;
		}

		// when the user clicks on a folder, redirect to the corresponding
		// folder in the files app instead of opening it directly
		fileActions.register('dir', 'Open', OC.PERMISSION_READ, '', function (filename, context) {
			OCA.Files.App.setActiveView('files', {silent: true});
			OCA.Files.App.fileList.changeDirectory(context.$file.attr('data-path') + '/' + filename, true, true);
		});

		OC.Plugins.attach('OCA.Filefilter.App', this);
		fileActions.setDefault('dir', 'Open');
		//for(pro in fileActions) {
		//	document.write("<p>" + fileActions[pro] + "</p>");
		//}
		return fileActions;
	},

	_onActionsUpdated: function(ev) {
		_.each([this._docFileList, this._picFileList, this._vidFileList,this._audFileList,this._othFileList], function(list) {
			if (!list) {
				return;
			}

			if (ev.action) {
				list.fileActions.registerAction(ev.action);
			} else if (ev.defaultAction) {
				list.fileActions.setDefault(
					ev.defaultAction.mime,
					ev.defaultAction.name
				);
			}
		});
	},

	_extendFileList: function(fileList) {
		// remove size column from summary
		fileList.fileSummary.$el.find('.filesize').remove();
	}
};

$(document).ready(function() {
	$('#app-content-documents').on('show', function(e) {
		//alert($(e.target));
		OCA.Filefilter.App.initDocumentsList($(e.target));
	});
	$('#app-content-documents').on('hide', function() {
		OCA.Filefilter.App.removeDocumentsList();
	});
	$('#app-content-pictures').on('show', function(e) {
		OCA.Filefilter.App.initPicturesList($(e.target));
	});
	$('#app-content-pictures').on('hide', function() {
		OCA.Filefilter.App.removePicturesList();
	});
	$('#app-content-videos').on('show', function(e) {
		OCA.Filefilter.App.initVideosList($(e.target));
	});
	$('#app-content-videos').on('hide', function() {
		OCA.Filefilter.App.removeVideosList();
	});
	$('#app-content-audios').on('show', function(e) {
		OCA.Filefilter.App.initAudiosList($(e.target));
	});
	$('#app-content-audios').on('hide', function() {
		OCA.Filefilter.App.removeAudiosList();
	});
	$('#app-content-others').on('show', function(e) {
		OCA.Filefilter.App.initOthersList($(e.target));
	});
	$('#app-content-others').on('hide', function() {
		OCA.Filefilter.App.removeOthersList();
	});
});

