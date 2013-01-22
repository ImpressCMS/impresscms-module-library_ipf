<?php
/**
 * Common file of the module included on all pages of the module
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

if (!defined("LIBRARY_DIRNAME")) define("LIBRARY_DIRNAME", $modversion["dirname"] = basename(dirname(dirname(__FILE__))));
if (!defined("LIBRARY_URL")) define("LIBRARY_URL", ICMS_URL."/modules/".LIBRARY_DIRNAME."/");
if (!defined("LIBRARY_ROOT_PATH")) define("LIBRARY_ROOT_PATH", ICMS_ROOT_PATH."/modules/".LIBRARY_DIRNAME."/");
if (!defined("LIBRARY_IMAGES_URL")) define("LIBRARY_IMAGES_URL", LIBRARY_URL."images/");
if (!defined("LIBRARY_ADMIN_URL")) define("LIBRARY_ADMIN_URL", LIBRARY_URL."admin/");

// Include the common language file of the module
icms_loadLanguageFile("library", "common");