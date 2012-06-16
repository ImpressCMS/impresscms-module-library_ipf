<?php
/**
 * Footer page included at the end of each page on user side of the mdoule
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

$icmsTpl->assign("library_adminpage", "<a href='" . ICMS_URL . "/modules/" . icms::$module->getVar("dirname") . "/admin/index.php'>" ._MD_LIBRARY_ADMIN_PAGE . "</a>");
$icmsTpl->assign("library_is_admin", icms_userIsAdmin(LIBRARY_DIRNAME));
$icmsTpl->assign('library_url', LIBRARY_URL);
$icmsTpl->assign('library_images_url', LIBRARY_IMAGES_URL);

$xoTheme->addStylesheet(LIBRARY_URL . 'module' . ((defined("_ADM_USE_RTL") && _ADM_USE_RTL) ? '_rtl' : '') . '.css');

include_once ICMS_ROOT_PATH . '/footer.php';