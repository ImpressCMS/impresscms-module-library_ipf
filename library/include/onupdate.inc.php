<?php
/**
 * File containing onUpdate and onInstall functions for the module
 *
 * This file is included by the core in order to trigger onInstall or onUpdate functions when needed.
 * Of course, onUpdate function will be triggered when the module is updated, and onInstall when
 * the module is originally installed. The name of this file needs to be defined in the
 * icms_version.php
 *
 * <code>
 * $modversion['onInstall'] = "include/onupdate.inc.php";
 * $modversion['onUpdate'] = "include/onupdate.inc.php";
 * </code>
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

// this needs to be the latest db version
define('LIBRARY_DB_VERSION', 1);

/**
 * it is possible to define custom functions which will be call when the module is updating at the
 * correct time in update incrementation. Simpy define a function named <direname_db_upgrade_db_version>
 */
/*
function library_db_upgrade_1() {
}
function library_db_upgrade_2() {
}
*/

function icms_module_update_library($module) {
    return TRUE;
}

function icms_module_install_library($module) {
	
	// create an uploads directory for images
	$path = ICMS_ROOT_PATH . '/uploads/' . basename(dirname(dirname(__FILE__)));
	$directory_exists = $writeable = true;

	// check if upload directory exists, make one if not
	if (!is_dir($path)) {
		$directory_exists = mkdir($path, 0777);
	}

	// authorise some audio mimetypes for convenience
	library_authorise_mimetypes();
	
	return TRUE;
}

/**
 * Authorises some commonly used mimetypes on install
 *
 * Helps reduce the need for post-install configuration, its just a convenience for the end user.
 * It grants the module permission to use some common audio (and image) mimetypes that will
 * probably be needed for audio tracks and collection cover art.
 */

function library_authorise_mimetypes() {
	$dirname = basename(dirname(dirname(__FILE__)));
	$extension_list = array(
		'mp3', // sound formats
		'wav',
		'wma',
		'png', // image formats
		'gif',
		'jpg',
		'doc', // document formats
		'pdf',
		'html',
		'zip', // archives
		'tar',
		'wmv', // video formats
		'mpeg',
		'mpg',
		'avi'
	);
	$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
	foreach ($extension_list as $extension) {
		$allowed_modules = array();
		$mimetypeObj = '';

		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('extension', $extension));
		$mimetypeObj = array_shift($system_mimetype_handler->getObjects($criteria));

		if ($mimetypeObj) {
			$allowed_modules = $mimetypeObj->getVar('dirname');
			if (empty($allowed_modules)) {
				$mimetypeObj->setVar('dirname', $dirname);
				$mimetypeObj->store();
			} else {
				if (!in_array($dirname, $allowed_modules)) {
					$allowed_modules[] = $dirname;
					$mimetypeObj->setVar('dirname', $allowed_modules);
					$mimetypeObj->store();
				}
			}
		}
	}
}