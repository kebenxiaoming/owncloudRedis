<?php
/**
 * ownCloud - filefilter
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author sunny <xiaoyao_xiao@126.com>
 * @copyright sunny 2015
 */

namespace OCA\Filefilter\AppInfo;

$l = \OC::$server->getL10N('filefilter');

\OCA\Files\App::getNavigationManager()->add(
	array(
		"id" => 'pictures',
		"appname" => 'filefilter',
		"script" => 'list.php',
		"order" => 50,
		"name" => $l->t('Pictures')
	)
);

\OCA\Files\App::getNavigationManager()->add(
	array(
		"id" => 'documents',
		"appname" => 'filefilter',
		"script" => 'list.php',
		"order" => 51,
		"name" => $l->t('Documents')
	)
);
\OCA\Files\App::getNavigationManager()->add(
	array(
		'id'=>'videos',
		'appname'=>'filefilter',
		'script'=>'list.php',
		"order" => 52,
		'name'=>$l->t('Videos'),
	)
);
\OCA\Files\App::getNavigationManager()->add(
	array(
		'id'=>'audios',
		'appname'=>'filefilter',
		'script'=>'list.php',
		"order" => 53,
		'name'=>$l->t('Audios'),
	)
);
\OCA\Files\App::getNavigationManager()->add(
	array(
		'id'=>'others',
		'appname'=>'filefilter',
		'script'=>'list.php',
		"order" => 54,
		'name'=>$l->t('Others'),
	)
);