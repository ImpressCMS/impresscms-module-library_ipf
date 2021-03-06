<?php

/**
 * Displays basic repository information regarding the Open Archives Initiative functionality of
 * this module. For more information visit the Open Archives Initiative, http://www.openarchives.org
 *
 * @copyright	Copyright Isengard.biz 2012, distributed under GNU GPL V2 or any later version
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since	1.0
 * @author	Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package	Library
 * @version	$Id$
 */

include_once "header.php";
$xoopsOption["template_main"] = "library_open_archive.html";
include_once ICMS_ROOT_PATH . "/header.php";

// Check if Sprockets module is available, this page is dependent on it
if (icms_get_module_status("sprockets"))
{
	global $icmsConfig, $xoTheme;

	// Set page title
	$icmsTpl->assign("library_page_title", _MD_LIBRARY_OPEN_ARCHIVES_INITIATIVE);
	icms_loadLanguageFile("sprockets", "common");

	$sprockets_archive_handler = icms_getModuleHandler('archive', 'sprockets', 'sprockets');
	$criteria = icms_buildCriteria(array('module_id' => icms::$module->getVar('mid')));
	$archiveObj = array_shift($sprockets_archive_handler->getObjects($criteria));
	if ($archiveObj)
	{
		$archive = $archiveObj->toArray();
		$archive['admin_email'] = str_replace('@', ' "at" ', $archive['admin_email']);
		$icmsTpl->assign('archive', $archive);
	}

	// RSS feed
	$icmsTpl->assign('library_rss_link', 'rss.php');
	$icmsTpl->assign('library_rss_title', _CO_LIBRARY_SUBSCRIBE_RSS);
	$rss_attributes = array('type' => 'application/rss+xml', 
		'title' => $icmsConfig['sitename'] . ' - ' .  _CO_LIBRARY_NEW);
	$rss_link = LIBRARY_URL . 'rss.php';

	// Add RSS auto-discovery link to module header
	$xoTheme->addLink('alternate', $rss_link, $rss_attributes);

	// Generate page metadata (can be customised in module preferences)
	global $icmsConfigMetaFooter;
	$library_meta_keywords = '';

	if (icms::$module->config['library_meta_keywords']) {
		$library_meta_keywords = icms::$module->config['library_meta_keywords'];
	} else {
		$library_meta_keywords = $icmsConfigMetaFooter['meta_keywords'];
	}
	$icms_metagen = new icms_ipf_Metagen(_MD_LIBRARY_OPEN_ARCHIVES_INITIATIVE, $library_meta_keywords, 
			_CO_LIBRARY_META_ARCHIVE_INDEX_DESCRIPTION);
	$icms_metagen->createMetaTags();

	// Breadcrumb
	$icmsTpl->assign("library_show_breadcrumb", icms::$module->config['library_show_breadcrumb']);
	$icmsTpl->assign("library_module_home", '<a href="' . ICMS_URL . "/modules/" 
			. icms::$module->getVar("dirname") . '/">' . icms::$module->getVar("name") . "</a>");
}
else
{
	// Sprockets not installed - nothing to do
	exit;
}

include_once "footer.php";