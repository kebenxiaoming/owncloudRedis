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
		//根据数据创建行
			_createRow: function(fileData, options) {
				var td, simpleSize, basename, extension, sizeColor,
					icon = OC.Util.replaceSVGIcon(fileData.icon),
					name = fileData.name,
					type = fileData.type || 'file',
					mtime = parseInt(fileData.mtime, 10),
					mime = fileData.mimetype,
					path = fileData.path,
					linkUrl;
				options = options || {};

				if (isNaN(mtime)) {
					mtime = new Date().getTime()
				}

				if (type === 'dir') {
					mime = mime || 'httpd/unix-directory';
				}

				//containing tr
				var tr = $('<tr></tr>').attr({
					"data-id" : fileData.id,
					"data-type": type,
					"data-size": fileData.size,
					"data-path":fileData.dir,
					"data-file": name,
					"data-mime": mime,
					"data-mtime": mtime,
					"data-etag": fileData.etag,
					"data-permissions": fileData.permissions || this.getDirectoryPermissions()
				});

				if (fileData.mountType) {
					tr.attr('data-mounttype', fileData.mountType);
				}

				if (!_.isUndefined(path)) {
					tr.attr('data-path', path);
				}
				else {
					path = this.getCurrentDirectory();
				}

				if (type === 'dir') {
					// use default folder icon
					icon = icon || OC.imagePath('core', 'filetypes/folder');
				}
				else {
					icon = icon || OC.imagePath('core', 'filetypes/file');
				}

				// filename td
				td = $('<td class="filename"></td>');
				// linkUrl
				if (type === 'dir') {
					linkUrl = this.linkTo(path + '/' + name);
				}
				else {
					linkUrl = this.getDownloadUrl(name, path);
				}
				if (this._allowSelection) {
					td.append(
						'<input id="select-' + this.id + '-' + fileData.id +
						'" type="checkbox" class="selectCheckBox"/><label for="select-' + this.id + '-' + fileData.id + '">' +
						'<div class="thumbnail" style="background-image:url(' + icon + '); background-size: 32px;"></div>' +
						'<span class="hidden-visually">' + t('files', 'Select') + '</span>' +
						'</label>'
					);
				} else {
					td.append('<div class="thumbnail" style="background-image:url(' + icon + '); background-size: 32px;"></div>');
				}
				var linkElem = $('<a></a>').attr({
					"class": "name",
					"href": linkUrl
				});

				// from here work on the display name
				name = fileData.displayName || name;

				// split extension from filename for non dirs
				if (type !== 'dir' && name.indexOf('.') !== -1) {
					basename = name.substr(0, name.lastIndexOf('.'));
					extension = name.substr(name.lastIndexOf('.'));
				} else {
					basename = name;
					extension = false;
				}
				var nameSpan=$('<span></span>').addClass('nametext');
				var innernameSpan = $('<span></span>').addClass('innernametext').text(basename);
				nameSpan.append(innernameSpan);
				linkElem.append(nameSpan);
				if (extension) {
					nameSpan.append($('<span></span>').addClass('extension').text(extension));
				}
				if (fileData.extraData) {
					if (fileData.extraData.charAt(0) === '/') {
						fileData.extraData = fileData.extraData.substr(1);
					}
					nameSpan.addClass('extra-data').attr('title', fileData.extraData);
				}
				// dirs can show the number of uploaded files
				if (type === 'dir') {
					linkElem.append($('<span></span>').attr({
						'class': 'uploadtext',
						'currentUploads': 0
					}));
				}
				td.append(linkElem);
				tr.append(td);

				// size column
				if (typeof(fileData.size) !== 'undefined' && fileData.size >= 0) {
					simpleSize = humanFileSize(parseInt(fileData.size, 10), true);
					sizeColor = Math.round(160-Math.pow((fileData.size/(1024*1024)),2));
				} else {
					simpleSize = t('files', 'Pending');
				}

				td = $('<td></td>').attr({
					"class": "filesize",
					"style": 'color:rgb(' + sizeColor + ',' + sizeColor + ',' + sizeColor + ')'
				}).text(simpleSize);
				tr.append(td);

				//path加入
				td = $('<td></td>').attr({
					"class": "filepath",
					"style": 'color:rgb(' + sizeColor + ',' + sizeColor + ',' + sizeColor + ')'
				}).text(path);
				tr.append(td);
				// date column (1000 milliseconds to seconds, 60 seconds, 60 minutes, 24 hours)
				// difference in days multiplied by 5 - brightest shade for files older than 32 days (160/5)
				var modifiedColor = Math.round(((new Date()).getTime() - mtime )/1000/60/60/24*5 );
				// ensure that the brightest color is still readable
				if (modifiedColor >= '160') {
					modifiedColor = 160;
				}
				var formatted;
				var text;
				if (mtime > 0) {
					formatted = formatDate(mtime);
					text = OC.Util.relativeModifiedDate(mtime);
				} else {
					formatted = t('files', 'Unable to determine date');
					text = '?';
				}
				td = $('<td></td>').attr({ "class": "date" });
				td.append($('<span></span>').attr({
					"class": "modified",
					"title": formatted,
					"style": 'color:rgb('+modifiedColor+','+modifiedColor+','+modifiedColor+')'
				}).text(text));
				tr.find('.filesize').text(simpleSize);
				tr.append(td);
				return tr;
			},
			//查找文件方法
			findFileEl: function(fileName,dir){
				// use filterAttr to avoid escaping issues
				return this.$fileList.find('tr').filterAttr2('data-file', fileName,'data-path',dir);
			},
			//移除元素方法
			remove: function(name,dir, options){
				options = options || {};
				var fileEl = this.findFileEl(name,dir);
				var index = fileEl.index();
				if (!fileEl.length) {
					return null;
				}
				if (this._selectedFiles[fileEl.data('id')]) {
					// remove from selection first
					this._selectFileEl(fileEl, false);
					this.updateSelectionSummary();
				}
				if (this._dragOptions && (fileEl.data('permissions') & OC.PERMISSION_DELETE)) {
					// file is only draggable when delete permissions are set
					fileEl.find('td.filename').draggable('destroy');
				}
				this.files.splice(index, 1);
				fileEl.remove();
				// TODO: improve performance on batch update
				this.isEmpty = !this.files.length;
				if (typeof(options.updateSummary) === 'undefined' || !!options.updateSummary) {
					this.updateEmptyContent();
					this.fileSummary.remove({type: fileEl.attr('data-type'), size: fileEl.attr('data-size')}, true);
				}

				var lastIndex = this.$fileList.children().length;
				// if there are less elements visible than one page
				// but there are still pending elements in the array,
				// then directly append the next page
				if (lastIndex < this.files.length && lastIndex < this.pageSize()) {
					this._nextPage(true);
				}
				return fileEl;
			},
			//删除
			do_delete:function(files, dir) {
			var self = this;
			var params;
			if (files && files.substr) {
				files=[files];
			}
			if (files) {
				for (var i=0; i<files.length; i++) {
					var deleteAction = this.findFileEl(files[i],dir).children("td.date").children(".action.delete");
					deleteAction.removeClass('icon-delete').addClass('icon-loading-small');
				}
			}
			// Finish any existing actions
			if (this.lastAction) {
				this.lastAction();
			}

			params = {
				dir: dir || this.getCurrentDirectory()
			};
			if (files) {
				params.files = JSON.stringify(files);
			}
			else {
				// no files passed, delete all in current dir
				params.allfiles = true;
				// show spinner for all files
				this.$fileList.find('tr>td.date .action.delete').removeClass('icon-delete').addClass('icon-loading-small');
			}

			$.post(OC.filePath('files', 'ajax', 'delete.php'),
				params,
				function(result) {
					if (result.status === 'success') {
						if (params.allfiles) {
							self.setFiles([]);
						}
						else {
							$.each(files,function(index,file) {
								var fileEl = self.remove(file,dir, {updateSummary: false});
								// FIXME: not sure why we need this after the
								// element isn't even in the DOM any more
								fileEl.find('.selectCheckBox').prop('checked', false);
								fileEl.removeClass('selected');
								self.fileSummary.remove({type: fileEl.attr('data-type'), size: fileEl.attr('data-size')});
							});
						}
						// TODO: this info should be returned by the ajax call!
						self.updateEmptyContent();
						self.fileSummary.update();
						self.updateSelectionSummary();
						self.updateStorageStatistics();
					} else {
						if (result.status === 'error' && result.data.message) {
							OC.Notification.show(result.data.message);
						}
						else {
							OC.Notification.show(t('files', 'Error deleting file.'));
						}
						// hide notification after 10 sec
						setTimeout(function() {
							OC.Notification.hide();
						}, 10000);
						if (params.allfiles) {
							// reload the page as we don't know what files were deleted
							// and which ones remain
							self.reload();
						}
						else {
							$.each(files,function(index,file,dir) {
								var deleteAction = self.findFileEl(file,dir).find('.action.delete');
								deleteAction.removeClass('icon-loading-small').addClass('icon-delete');
							});
						}
					}
				});
		},
			//重命名的方法
			rename: function(oldname,dir) {
				var self = this;
				var tr, td, input, form;
				tr = this.findFileEl(oldname,dir);
				var oldFileInfo = this.files[tr.index()];
				tr.data('renaming',true);
				td = tr.children('td.filename');
				input = $('<input type="text" class="filename"/>').val(oldname);
				form = $('<form></form>');
				form.append(input);
				td.children('a.name').hide();
				td.append(form);
				input.focus();
				//preselect input
				var len = input.val().lastIndexOf('.');
				if ( len === -1 ||
					tr.data('type') === 'dir' ) {
					len = input.val().length;
				}
				input.selectRange(0, len);
				var checkInput = function () {
					var filename = input.val();
					if (filename !== oldname) {
						// Files.isFileNameValid(filename) throws an exception itself
						OCA.Files.Files.isFileNameValid(filename);
						if (self.inList(filename)) {
							throw t('files', '{new_name} already exists', {new_name: filename});
						}
					}
					return true;
				};

				function restore() {
					input.tipsy('hide');
					tr.data('renaming',false);
					form.remove();
					td.children('a.name').show();
				}

				form.submit(function(event) {
					event.stopPropagation();
					event.preventDefault();
					if (input.hasClass('error')) {
						return;
					}

					try {
						var newName = input.val();
						var $thumbEl = tr.find('.thumbnail');
						input.tipsy('hide');
						form.remove();

						if (newName !== oldname) {
							checkInput();
							// mark as loading (temp element)
							$thumbEl.css('background-image', 'url('+ OC.imagePath('core', 'loading.gif') + ')');
							tr.attr('data-file', newName);
							var basename = newName;
							if (newName.indexOf('.') > 0 && tr.data('type') !== 'dir') {
								basename = newName.substr(0, newName.lastIndexOf('.'));
							}
							td.find('a.name span.nametext').text(basename);
							td.children('a.name').show();
							tr.find('.fileactions, .action').addClass('hidden');

							$.ajax({
								url: OC.filePath('files','ajax','rename.php'),
								data: {
									dir : tr.attr('data-path') || self.getCurrentDirectory(),
									newname: newName,
									file: oldname
								},
								success: function(result) {
									var fileInfo;
									if (!result || result.status === 'error') {
										OC.dialogs.alert(result.data.message, t('files', 'Could not rename file'));
										fileInfo = oldFileInfo;
										if (result.data.code === 'sourcenotfound') {
											self.remove(result.data.newname, {updateSummary: true});
											return;
										}
									}
									else {
										fileInfo = result.data;
									}
									// reinsert row
									self.files.splice(tr.index(), 1);
									tr.remove();
									tr = self.add(fileInfo, {updateSummary: false, silent: true});
									self.$fileList.trigger($.Event('fileActionsReady', {fileList: self, $files: $(tr)}));
								}
							});
						} else {
							// add back the old file info when cancelled
							self.files.splice(tr.index(), 1);
							tr.remove();
							tr = self.add(oldFileInfo, {updateSummary: false, silent: true});
							self.$fileList.trigger($.Event('fileActionsReady', {fileList: self, $files: $(tr)}));
						}
					} catch (error) {
						input.attr('title', error);
						input.tipsy({gravity: 'w', trigger: 'manual'});
						input.tipsy('show');
						input.addClass('error');
					}
					return false;
				});
				input.keyup(function(event) {
					// verify filename on typing
					try {
						checkInput();
						input.tipsy('hide');
						input.removeClass('error');
					} catch (error) {
						input.attr('title', error);
						input.tipsy({gravity: 'w', trigger: 'manual'});
						input.tipsy('show');
						input.addClass('error');
					}
					if (event.keyCode === 27) {
						restore();
					}
				});
				input.click(function(event) {
					event.stopPropagation();
					event.preventDefault();
				});
				input.blur(function() {
					form.trigger('submit');
				});
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
