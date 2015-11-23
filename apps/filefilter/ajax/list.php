<?php

OCP\JSON::checkLoggedIn();
\OC::$server->getSession()->close();

$l = \OC::$server->getL10N('filefilter');

// Load the files
$dir = isset($_GET['dir']) ? (string)$_GET['dir'] : '';

$view=isset($_GET['view']) ? (string)$_GET['view'] : 'pictures';

////更改载入数据的目录
//$rootname=\OC::$server->getSystemConfig()->getValue('rootname');
//$dir="/".$rootname;
$dir="/";

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

	try {
		//首先选择出所有符合文档要求的mimetype 的id
		$queryDoc = \OC_DB::prepare('SELECT * FROM `*PREFIX*mimetypes` WHERE `mimetype` in (' . $mimetypes . ')');

		$Mimeresult = $queryDoc->execute();

		$mimetypesrow = $Mimeresult->fetchAll();
	}catch(\Exception $e) {
		\OCP\Util::writeLog('filefilter', __METHOD__.', exception: '.$e->getMessage(),
			\OCP\Util::ERROR);
		return false;
	}

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
	try{
		//查询当前数据库中当前用的的本地存储信息
		$queryDelFast = \OC_DB::prepare('SELECT * FROM `*PREFIX*storages` WHERE `id` = ?');
		$result =$queryDelFast->execute(array("home::".$_SESSION['user_id']));
		$row = $result->fetchRow();

		$stoarr[]=$row['numeric_id'];

		$locastorage=$row['numeric_id'];
		//查询出当前用户的mount信息
		$mounts= OC_Mount_Config::getAbsoluteMountPoints($_SESSION['user_id']);

		//将mount信息中的storage_id变为数组
		foreach($mounts as $key=>$val){
			$stoarr[]=$val['storage_id'];
		}

		$storagestr=implode(',',$stoarr);
		//组合where条件
		$where='WHERE path NOT LIKE "%thumbnails%" AND storage IN ('.$storagestr.') AND mimetype IN ('.$mimetypesid.')';
		$queryAllFiles = \OC_DB::prepare('SELECT * FROM `*PREFIX*filecache`  '.$where.' order by  '.$order);
		$result =$queryAllFiles->execute();
		$row = $result->fetchAll();

		} catch(\Exception $e) {
		\OCP\Util::writeLog('filefilter', __METHOD__.', exception: '.$e->getMessage(),
		\OCP\Util::ERROR);
		return false;
		}
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
			//重组path
			if($file['storage']==$locastorage){
				$files[$key]['path']="/";
			}else {
				foreach ($mounts as $mountkey=>$mount) {
					if($file['storage']==$mount['storage_id']){
						$newdir=substr($mountkey,strrpos($mountkey,'/'));
					if (substr($file['path'], 0, strrpos($file['path'], '/')) != "") {
						$files[$key]['path'] = $newdir . '/' . substr($file['path'], 0, strrpos($file['path'], '/'));
					} else {
						$files[$key]['path'] = $newdir;
					}
					}
				}
			}
			unset($files[$key]['storage']);
			//unset($files[$key]['fileid']);
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
			//重组path
			if($file['storage']==$locastorage){
				$files[$key]['path']="/";
			}else {
				foreach ($mounts as $mountkey=>$mount) {
					if($file['storage']==$mount['storage_id']){
						$newdir=substr($mountkey,strrpos($mountkey,'/'));
						if (substr($file['path'], 0, strrpos($file['path'], '/')) != "") {
							$files[$key]['path'] = $newdir . '/' . substr($file['path'], 0, strrpos($file['path'], '/'));
						} else {
							$files[$key]['path'] = $newdir;
						}
					}
				}
			}
			unset($files[$key]['storage']);
			//unset($files[$key]['fileid']);
			$files[$key]['type'] = 'file';
			$files[$key]['date'] = \OCP\Util::formatDate($file['storage_mtime']);
			unset($files[$key]['storage_mtime']);
			$files[$key]['mtime'] = $file['mtime'] * 1000;
			$files[$key]['parentId'] = $file['parent'];
			unset($files[$key]['parent']);
			$files[$key]['permissions'] = (int)$file['permissions'];
		}
	}
	if(empty($files)){
		$data['directory'] = $dir;
		$data['files'] = $files;
		//\OCA\Files\Helper::formatFileInfos($files);
		$data['permissions'] = (int)$permissions;

		OCP\JSON::success(array('data' => $data));
		die("所查询文件类型不存在");
	}
	//按照数据格式要求返回数据
	//将当前files信息转换为以fileid为key的数组
	foreach($files as $file){
		$filesById[$file['fileid']] = $file;
	}
	try {
		$conn = \OC_DB::getConnection();
		$chunks = array_chunk(array_keys($filesById), 900, false);
		foreach ($chunks as $chunk) {
			$result = $conn->executeQuery(
				'SELECT `category`, `categoryid`, `objid` ' .
				'FROM `' .'*PREFIX*vcategory_to_object' . '` r, `' . '*PREFIX*vcategory' . '` ' .
				'WHERE `categoryid` = `id` AND `uid` = ? AND r.`type` = ? AND `objid` IN (?)',
				array($_SESSION['user_id'], 'files', $chunk),
				array(null, null, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
			);
			while ($row = $result->fetch()) {
				$objId = (int)$row['objid'];
				if (!isset($entries[$objId])) {
					$entry = $entries[$objId] = array();
				}
				$entry = $entries[$objId][] = $row['category'];
			}
			if (\OCP\DB::isError($result)) {
				\OCP\Util::writeLog('filefilter', __METHOD__. 'DB error: ' . \OCP\DB::getErrorMessage($result), \OCP\Util::ERROR);
				return false;
			}
		}
	} catch(\Exception $e) {
		\OCP\Util::writeLog('filefilter', __METHOD__.', exception: '.$e->getMessage(),
			\OCP\Util::ERROR);
		return false;
	}
	//将tags赋值给files数组
	if (isset($entries)) {
		foreach ($entries as $fileId => $fileTags) {
			$filesById[$fileId]['tags'] = $fileTags;
		}
	}
	//将最后值变为filelist
	foreach($filesById as $val){
		$filelist[]=$val;
	}

	$data['directory'] = $dir;
	$data['files'] = $filelist;
	//\OCA\Files\Helper::formatFileInfos($files);
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
