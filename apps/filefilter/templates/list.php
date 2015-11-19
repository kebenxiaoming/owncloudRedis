<div id="controls">
	<div class="actions creatable hidden">
	</div>
	<div id="file_action_panel"></div>
	<div class="notCreatable notPublic hidden">
		<?php p($l->t('You don’t have permission to upload or create files here'))?>
	</div>
	<input type="hidden" name="permissions" value="" id="permissions">
</div>

<div id="emptycontent" class="hidden">
	<div class="icon-folder"></div>
	<h2><?php p($l->t('No files yet')); ?></h2>
	<p><?php p($l->t('Upload some content or sync with your devices!')); ?></p>
</div>

<div class="nofilterresults hidden">
	<div class="icon-search"></div>
	<h2><?php p($l->t('No entries found in this folder')); ?></h2>
	<p></p>
</div>

<table id="filestable" data-preview-x="36" data-preview-y="36">
	<thead>
	<tr>
		<th id='headerName' class="hidden column-name">
			<div id="headerName-container">
<!--				<input type="checkbox" id="select_all_files" class="select-all"/>-->
				<label for="select_all_files">
					<span class="hidden-visually"><?php p($l->t('Select all'))?></span>
				</label>
				<a class="name sort columntitle" data-sort="name"><span><?php p($l->t( 'Name' )); ?></span><span class="sort-indicator"></span></a>
<!--					<span id="selectedActionsList" class="selectedActions">-->
<!--						<a href="" class="download">-->
<!--							<img class="svg" alt=""-->
<!--								 src="--><?php //print_unescaped(OCP\image_path("core", "actions/download.svg")); ?><!--" />-->
<!--							--><?php //p($l->t('Download'))?>
<!--						</a>-->
<!--					</span>-->
			</div>
		</th>

		<th id="headerSize" class="hidden column-size">
			<a class="size sort columntitle" data-sort="size"><span><?php p($l->t('Size')); ?></span><span class="sort-indicator"></span></a>
		</th>

		<th id="headerPath" class="hidden column-path">
			<a class="path sort columntitle" data-sort="path"><span><?php p($l->t('Path')); ?></span><span class="sort-indicator"></span></a>
		</th>

		<th id="headerDate" class="hidden column-mtime">
			<a id="modified" class="columntitle" data-sort="mtime"><span><?php p($l->t( 'Modified' )); ?></span><span class="sort-indicator"></span></a>
<!--					<span class="selectedActions"><a href="" class="delete-selected">-->
<!--							--><?php //p($l->t('Delete'))?>
<!--							<img class="svg" alt=""-->
<!--								 src="--><?php //print_unescaped(OCP\image_path("core", "actions/delete.svg")); ?><!--" />-->
<!--						</a></span>-->
		</th>


	</tr>
	</thead>
	<tbody id="fileList">
	</tbody>
	<tfoot>
	</tfoot>
</table>
<input type="hidden" name="dir" id="dir" value="" />
<div id="editor"></div><!-- FIXME Do not use this div in your app! It is deprecated and will be removed in the future! -->
<div id="uploadsize-message" title="<?php p($l->t('Upload too large'))?>">
	<p>
		<?php p($l->t('The files you are trying to upload exceed the maximum size for file uploads on this server.'));?>
	</p>
</div>
<div id="scanning-message">
	<h3>
		<?php p($l->t('Files are being scanned, please wait.'));?> <span id='scan-count'></span>
	</h3>
	<p>
		<?php p($l->t('Currently scanning'));?> <span id='scan-current'></span>
	</p>
</div>
