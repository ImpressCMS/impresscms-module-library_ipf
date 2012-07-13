<?php

include_once "header.php";
$xoopsOption["template_main"] = "library_open_archive.html";
include_once ICMS_ROOT_PATH . "/header.php";

// Set page title
$icmsTpl->assign("library_page_title", _MD_LIBRARY_OPEN_ARCHIVES_INITIATIVE);

$sprockets_archive_handler = icms_getModuleHandler('archive', 'sprockets', 'sprockets');
$criteria = icms_buildCriteria(array('module_id' => icms::$module->getVar('mid')));
$archiveObj = array_shift($sprockets_archive_handler->getObjects($criteria));
if ($archiveObj)
{
	icms_loadLanguageFile("sprockets", "common");
	$archive = $archiveObj->toArray();
	$archive['admin_email'] = str_replace('@', ' "at" ', $archive['admin_email']);
	$icmsTpl->assign('archive', $archive);
}

// Breadcrumb
$icmsTpl->assign("library_show_breadcrumb", icms::$module->config['library_show_breadcrumb']);
$icmsTpl->assign("library_module_home", '<a href="' . ICMS_URL . "/modules/" 
		. icms::$module->getVar("dirname") . '/">' . icms::$module->getVar("name") . "</a>");

include_once "footer.php";