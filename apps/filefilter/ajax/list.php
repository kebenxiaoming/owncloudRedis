<?php

OCP\JSON::checkLoggedIn();
\OC::$server->getSession()->close();

$l = \OC::$server->getL10N('documents');

// Load the files
$dir = isset($_GET['dir']) ? (string)$_GET['dir'] : '';

$view=isset($_GET['view']) ? (string)$_GET['view'] : 'pictures';

////更改载入数据的目录
$rootname=\OC::$server->getSystemConfig()->getValue('rootname');
$dir="/".$rootname;

$dir = \OC\Files\Filesystem::normalizePath($dir);

try {
	$dirInfo = \OC\Files\Filesystem::getFileInfo($dir);
	if (!$dirInfo || !$dirInfo->getType() === 'dir') {
		header("HTTP/1.0 404 Not Found");
		exit();
	}

	$data = array();
	$baseUrl = OCP\Util::linkTo('files', 'index.php') . '?dir=';

	$permissions = $dirInfo->getPermissions();

	$sortAttribute = isset($_GET['sort']) ? $_GET['sort'] : 'name';
	$sortDirection = isset($_GET['sortdirection']) ? ($_GET['sortdirection'] === 'desc') : false;
	$mimetypeFilters = isset($_GET['mimetypes']) ? json_decode($_GET['mimetypes']) : '';

	$files = [];
		// create file list without mimetype filter
	//$files = \OCA\Files\Helper::getFiles($dir);
	if($sortDirection==true) {
		$order = $sortAttribute. " desc";
	}else{
		$order = $sortAttribute. ' asc';
	}
	//从config.php中获取所有满足文档信息要求的mimetype
	$mimetypesarr=\OC::$server->getSystemConfig()->getValue('mimetype');

	$mimetypes=$mimetypesarr[$view];

	//首先选择出所有符合文档要求的mimetype 的id
	$queryDoc = \OC_DB::prepare('SELECT * FROM `*PREFIX*mimetypes` WHERE `mimetype` in ('.$mimetypes.')');

	$Mimeresult =$queryDoc->execute();

	$mimetypesrow = $Mimeresult->fetchAll();

	if(empty($mimetypesrow)){
		$data['directory'] = $dir;
		$data['files'] = "";//\OCA\Files\Helper::formatFileInfos($files);
		$data['permissions'] = (int)$permissions;
		OCP\JSON::success(array('data' => $data));
		die("没有当前类型文件");
	};
	//组合获取的mimetype的ID
	$mimetypesid="";

	foreach($mimetypesrow as $val){
		$mimetypesid=$mimetypesid.$val['id'].',';
	}

	$mimetypesid=substr($mimetypesid,0,(strrpos($mimetypesid,',')));
	//选择出当前用户对应的mount数据
	$queryDelFast = \OC_DB::prepare('SELECT * FROM `*PREFIX*mount` WHERE `name` = ?');
	$result =$queryDelFast->execute(array($_SESSION['user_id']));
	$row = $result->fetchRow();
	//组合where条件
	$where='WHERE storage= ? AND mimetype in ('.$mimetypesid.')';
	$queryAllFiles = \OC_DB::prepare('SELECT * FROM `*PREFIX*filecache`  '.$where.' order by  '.$order);

	$result =$queryAllFiles->execute(array($row['storage_id']));
	$row = $result->fetchAll();

	$files=$row;
	if($view=="pictures") {
		//将mimetype转换为字符串
		foreach ($files as $key => $file) {
			foreach ($mimetypesrow as $mime) {
				if ($file['mimetype'] == $mime['id']) {
					$files[$key]['mimetype'] = $mime['mimetype'];
					$files[$key]['icon'] = \OC_Helper::mimetypeIcon($mime['mimetype']);
				}
			}
			$files[$key]['id'] = $file['fileid'];
			unset($files[$key]['path_hash']);
			unset($files[$key]['storage']);
			if (substr($file['path'], 0, strrpos($file['path'], '/')) != "") {
				$files[$key]['path'] = $dir . '/' . substr($file['path'], 0, strrpos($file['path'], '/'));
				$files[$key]['dir']=substr($file['path'], 0, strrpos($file['path'], '/'));
			} else {
				$files[$key]['path'] = $dir;
				$files[$key]['dir']="全部文件";
			}
			unset($files[$key]['fileid']);
			$files[$key]['type'] = 'file';
			$files[$key]['date'] = \OCP\Util::formatDate($file['storage_mtime']);
			unset($files[$key]['storage_mtime']);
			$files[$key]['mtime'] = $file['mtime'] * 1000;
			$files[$key]['parentId'] = $file['parent'];
			unset($files[$key]['parent']);
			$files[$key]['permissions'] = (int)$file['permissions'];
			$files[$key]['isPreviewAvailable']=true;
		}
	}else{
		//将mimetype转换为字符串
		foreach ($files as $key => $file) {
			foreach ($mimetypesrow as $mime) {
				if ($file['mimetype'] == $mime['id']) {
					$files[$key]['mimetype'] = $mime['mimetype'];
					$files[$key]['icon'] = \OC_Helper::mimetypeIcon($mime['mimetype']);
				}
			}
			$files[$key]['id'] = $file['fileid'];
			unset($files[$key]['path_hash']);
			unset($files[$key]['storage']);
			if (substr($file['path'], 0, strrpos($file['path'], '/')) != "") {
				$files[$key]['path'] = $dir . '/' . substr($file['path'], 0, strrpos($file['path'], '/'));
				$files[$key]['dir']=substr($file['path'], 0, strrpos($file['path'], '/'));
			} else {
				$files[$key]['path'] = $dir;
				$files[$key]['dir']="全部文件";
			}
			unset($files[$key]['fileid']);
			$files[$key]['type'] = 'file';
			$files[$key]['date'] = \OCP\Util::formatDate($file['storage_mtime']);
			unset($files[$key]['storage_mtime']);
			$files[$key]['mtime'] = $file['mtime'] * 1000;
			$files[$key]['parentId'] = $file['parent'];
			unset($files[$key]['parent']);
			$files[$key]['permissions'] = (int)$file['permissions'];
		}
	}
	//按照数据格式要求返回数据
	$data['directory'] = $dir;
	$data['files'] = $files;//\OCA\Files\Helper::formatFileInfos($files);
	$data['permissions'] = (int)$permissions;

	OCP\JSON::success(array('data' => $data));
} catch (\OCP\Files\StorageNotAvailableException $e) {
	\OCP\Util::logException('files', $e);
	OCP\JSON::error(array(
		'data' => array(
			'exception' => '\OCP\Files\StorageNotAvailableException',
			'message' => $l->t('Storage not available')
		)
	));
} catch (\OCP\Files\StorageInvalidException $e) {
	\OCP\Util::logException('files', $e);
	OCP\JSON::error(array(
		'data' => array(
			'exception' => '\OCP\Files\StorageInvalidException',
			'message' => $l->t('Storage invalid')
		)
	));
} catch (\Exception $e) {
	\OCP\Util::logException('files', $e);
	OCP\JSON::error(array(
		'data' => array(
			'exception' => '\Exception',
			'message' => $l->t('Unknown error')
		)
	));
}
