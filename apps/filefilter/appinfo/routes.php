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

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\Filefilter\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */

$this->create('filefilter_list', '/')
    ->actionInclude('filefilter/list.php');

$this->create('filefilter_ajax_list', 'ajax/list.php')
    ->actionInclude('filefilter/ajax/list.php');