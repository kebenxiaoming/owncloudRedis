<?php
/**
 * sunny
 * 2015-11-13
 */

// Check if we are a user
OCP\User::checkLoggedIn();

$l = \OC::$server->getL10N('filefilter');

OCP\Util::addScript('filefilter', 'app');
OCP\Util::addScript('filefilter', 'filterfilelist');

// renders the controls and table headers template
$tmpl = new OCP\Template('filefilter', 'list', '');
$tmpl->printPage();