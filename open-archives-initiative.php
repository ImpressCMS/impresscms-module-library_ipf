<?php

include_once "header.php";
$xoopsOption["template_main"] = "library_publication.html";
include_once ICMS_ROOT_PATH . "/header.php";

// Set page title
$icmsTpl->assign("library_page_title", _MD_LIBRARY_OPEN_ARCHIVES_INITIATIVE);

$icmsTpl->assign("library_show_breadcrumb", icms::$module->config['library_show_breadcrumb']);
$icmsTpl->assign("library_module_home", '<a href="' . ICMS_URL . "/modules/" 
		. icms::$module->getVar("dirname") . '/">' . icms::$module->getVar("name") . "</a>");

include_once "footer.php";