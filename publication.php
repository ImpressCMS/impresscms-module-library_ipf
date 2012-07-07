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

$clean_publication_id = isset($_GET["publication_id"]) ? (int)$_GET["publication_id"] : 0 ;
$library_publication_handler = icms_getModuleHandler("publication", basename(dirname(__FILE__)), "library");
$publicationObj = $library_publication_handler->get($clean_publication_id);

// Optional tagging support (only if Sprockets module installed)
$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
if (icms_get_module_status("sprockets"))
{
	// Prepare common Sprockets handlers and buffers
	icms_loadLanguageFile("sprockets", "common");
	$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'), 'sprockets');
	$sprockets_taglink_handler = icms_getModuleHandler('taglink', $sprocketsModule->getVar('dirname'), 'sprockets');
	$criteria = icms_buildCriteria(array('label_type' => '0'));
	$sprockets_tag_buffer = $sprockets_tag_handler->getObjects($criteria, TRUE, FALSE);
	
	// Append the tag to the breadcrumb title
	if (array_key_exists($clean_tag_id, $sprockets_tag_buffer) && ($clean_tag_id !== 0))
	{
		$library_tag_name = $sprockets_tag_buffer[$clean_tag_id]['title'];
		$icmsTpl->assign('library_tag_name', $library_tag_name);
		$icmsTpl->assign('library_category_path', $sprockets_tag_buffer[$clean_tag_id]['title']);
	}
}

////////////////////////////////////////////////////////////////////
//////////////////// DISPLAY SINGLE PUBLICATION ////////////////////
////////////////////////////////////////////////////////////////////

if($publicationObj && !$publicationObj->isNew()) 
{
	// Update views counter
	if (!icms_userIsAdmin(icms::$module->getVar('dirname'))) {
		$library_publication_handler->updateCounter($publicationObj);
	}

	// Prepare publication for display
	$publicationObj->setFieldDisplayPreferences();
	$publication = $publicationObj->toArray();
	
	// Add SEO friendly string to URL
	if (!empty($publication['short_url'])) {
		$publication['itemUrl'] .= "&amp;title=" . $publication['short_url'];
	}
	
	// Prepare tags for display (only if Sprockets module installed)
	if (icms_get_module_status("sprockets"))
	{
		$publication['tags'] = array();
		$publication_tag_array = $sprockets_taglink_handler->getTagsForObject($publicationObj->getVar('publication_id'), $library_publication_handler, '0');
		foreach ($publication_tag_array as $key => $value) {
			$publication['tags'][$value] = '<a href="' . LIBRARY_URL . 'publication.php?tag_id=' . $value 
					. '">' . $sprockets_tag_buffer[$value]['title'] . '</a>';
		}
		$publication['tags'] = implode(', ', $publication['tags']);
	}
	
	// Assign to template
	$icmsTpl->assign("library_publication", $publication);
	
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