<?php
/**
* Publication page
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2012
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		library
* @version		$Id$
*/

include_once "header.php";

$xoopsOption["template_main"] = "library_publication.html";
include_once ICMS_ROOT_PATH . "/header.php";

$library_publication_handler = icms_getModuleHandler("publication", basename(dirname(__FILE__)), "library");

$clean_publication_id = isset($_GET["publication_id"]) ? (int)$_GET["publication_id"] : 0 ;
$publicationObj = $library_publication_handler->get($clean_publication_id);

////////////////////////////////////////////////////////////////////
//////////////////// DISPLAY SINGLE PUBLICATION ////////////////////
////////////////////////////////////////////////////////////////////

if($publicationObj && !$publicationObj->isNew()) 
{
	// Prepare publication for display
	
	$icmsTpl->assign("library_publication", $publicationObj->toArray());
	
	// Display comments
	if (icms::$module->config['com_rule']) {
		$icmsTpl->assign('library_publication_comment', TRUE);
		include_once ICMS_ROOT_PATH . '/include/comment_view.php';
	}

	// Generate page metadata
	$icms_metagen = new icms_ipf_Metagen($publicationObj->getVar("title"), 
	$publicationObj->getVar("meta_keywords", "n"), 
	$publicationObj->getVar("meta_description", "n"));
	$icms_metagen->createMetaTags();
}

////////////////////////////////////////////////////////////////////
//////////////////// DISPLAY INDEX PAGE ////////////////////////////
////////////////////////////////////////////////////////////////////

else
{
	$icmsTpl->assign("library_title", _MD_LIBRARY_ALL_PUBLICATIONS);

	$objectTable = new icms_ipf_view_Table($library_publication_handler, FALSE, array());
	$objectTable->isForUserSide();
	$objectTable->addColumn(new icms_ipf_view_Column("title"));
	$icmsTpl->assign("library_publication_table", $objectTable->fetch());
}

$icmsTpl->assign("library_module_home", '<a href="' . ICMS_URL . "/modules/" . icms::$module->getVar("dirname") . '/">' . icms::$module->getVar("name") . "</a>");

include_once "footer.php";