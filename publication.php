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
$clean_tag_id = isset($_GET["tag_id"]) ? (int)$_GET["tag_id"] : 0 ;
$clean_start = isset($_GET["start"]) ? intval($_GET["start"]) : 0;

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
	$publication = $library_publication_handler->toArrayForDisplay($publicationObj);
	
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
	// Set page title
	$icmsTpl->assign("library_page_title", _MD_LIBRARY_ALL_PUBLICATIONS);
				
	// Get a select box (if preferences allow, and only if Sprockets module installed)
	if (icms_get_module_status("sprockets") && icms::$module->config['library_show_tag_select_box'] == TRUE)  {
		$tag_select_box = $sprockets_tag_handler->getTagSelectBox('publication.php', $clean_tag_id, 
				_CO_LIBRARY_PUBLICATION_ALL_TAGS, TRUE, icms::$module->getVar('mid'));
		$icmsTpl->assign('library_tag_select_box', $tag_select_box);
	}
	
	if (icms::$module->config['library_publication_index_display_mode'] == '1')
	{
		// Initialise
		$criteria = $publication_count = '';
		$library_publication_summaries = array();

		// Retrieve publications for a given tag
		if ($clean_tag_id && icms_get_module_status("sprockets"))
		{
			// Retrieve a list of publications JOINED to taglinks by publication_id/tag_id/module_id/item
			$query = $rows = $publication_count = '';
			$linked_publication_ids = array();

			// First, count the number of publications for the pagination control
			$group_query = "SELECT count(*) FROM " . $library_publication_handler->table . ", "
					. $sprockets_taglink_handler->table
					. " WHERE `publication_id` = `iid`"
					. " AND `online_status` = '1'"
					. " AND `tid` = '" . $clean_tag_id . "'"
					. " AND `mid` = '" . icms::$module->getVar('mid') . "'"
					. " AND `item` = 'publication'";
			$result = icms::$xoopsDB->query($group_query);
			if (!$result) {
				echo 'Error';
				exit;
			}
			else {
				while ($row = icms::$xoopsDB->fetchArray($result)) {
					foreach ($row as $key => $count) {
						$publication_count = $count;
					}
				}
			}

			// Secondly, get the publications
			$query = "SELECT * FROM " . $library_publication_handler->table . ", "
					. $sprockets_taglink_handler->table
					. " WHERE `publication_id` = `iid`"
					. " AND `online_status` = '1'"
					. " AND `tid` = '" . $clean_tag_id . "'"
					. " AND `mid` = '" . icms::$module->getVar('mid') . "'"
					. " AND `item` = 'publication'"
					. " ORDER BY `date` DESC"
					. " LIMIT " . $clean_start . ", " . icms::$module->config['number_publications_per_page'];
			$result = icms::$xoopsDB->query($query);
			if (!$result) {
				echo 'Error';
				exit;
			}
			else {
				// Retrieve publications as objects, with id as key, and prepare for display
				$rows = $library_publication_handler->convertResultSet($result, TRUE, TRUE);
				foreach ($rows as $pubObj) {
					$library_publication_summaries[$pubObj->getVar('publication_id')] = $library_publication_handler->toArrayForDisplay($pubObj);
				}

				// Assign publications to template
				$icmsTpl->assign('library_publication_summaries', $library_publication_summaries);
			}
		}	
	else
	{	
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('online_status', TRUE));

		// Count the number of online publications for the pagination control
		$publication_count = $library_publication_handler->getCount($criteria);

		// Continue to retrieve publications for this page view
		$criteria->setStart($clean_start);
		$criteria->setLimit(icms::$module->config['number_publications_per_page']);
		$criteria->setSort('date');
		$criteria->setOrder('DESC');
		$library_publication_summaries = $library_publication_handler->getObjects($criteria, TRUE, TRUE);

		// Prepare publications for display
		foreach ($library_publication_summaries as &$publication) {
			$publication = $library_publication_handler->toArrayForDisplay($publication);
		}

			// Assign publications to template
			$icmsTpl->assign('library_publication_summaries', $library_publication_summaries);// Assign publications to template

			// Pagination control - adust for tag, if present
			if (!empty($clean_tag_id)) {
				$extra_arg = 'tag_id=' . $clean_tag_id;
			}
			else {
				$extra_arg = FALSE;
			}
			$pagenav = new icms_view_PageNav($publication_count, 
					icms::$module->config['number_publications_per_page'], $clean_start, 'start', $extra_arg);
			$icmsTpl->assign('library_navbar', $pagenav->renderNav());
		}
	}
	else // Display publication index as compact table
	{
		$objectTable = new icms_ipf_view_Table($library_publication_handler, FALSE, array());
		$objectTable->isForUserSide();
		$objectTable->addColumn(new icms_ipf_view_Column("title"));
		$icmsTpl->assign("library_publication_table", $objectTable->fetch());
		}
}

$icmsTpl->assign("library_show_breadcrumb", icms::$module->config['library_show_breadcrumb']);
$icmsTpl->assign("library_module_home", '<a href="' . ICMS_URL . "/modules/" 
		. icms::$module->getVar("dirname") . '/">' . icms::$module->getVar("name") . "</a>");

include_once "footer.php";