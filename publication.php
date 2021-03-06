<?php
/**
* Publication display page.
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2012
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		library
* @version		$Id$
*/

/**
 * Adjust category itemLinks to point at publication page
 * 
 * @param int $id
 * @param string $title
 * @param string $short_url
 * @return string 
 */
function modifyItemLink($id, $title, $short_url) {	
	$itemLink = '<a href="' . LIBRARY_URL . 'publication.php?tag_id=' . $id . '&amp;label_type=1';
	if ($short_url) {
		$itemLink .= '&amp;title=' . $short_url;
	}
	$itemLink .= '">' . $title . '</a>';
	return $itemLink;
}

include_once "header.php";
$xoopsOption["template_main"] = "library_publication.html";
include_once ICMS_ROOT_PATH . "/header.php";

global $xoTheme, $icmsConfig;

// Sanitise the tag_id and start (pagination) parameters
$clean_publication_id = isset($_GET["publication_id"]) ? (int)$_GET["publication_id"] : 0 ;
$untagged_content = FALSE;
if (isset($_GET['tag_id'])) {
	if ($_GET['tag_id'] == 'untagged') {
		$untagged_content = TRUE;
	}
}
$clean_tag_id = isset($_GET["tag_id"]) ? (int)$_GET["tag_id"] : 0 ;
$clean_start = isset($_GET["start"]) ? (int)$_GET["start"] : 0;
$clean_m3u = isset($_GET['m3u']) ? (int)$_GET['m3u'] : 0; // Flag indicating streamable content
$clean_label_type = isset($_GET['label_type']) ? (int)$_GET['label_type'] : 0 ; // View categories (1) or tags (0)

$library_publication_handler = icms_getModuleHandler("publication", basename(dirname(__FILE__)), "library");
if ($clean_publication_id) {
	$publicationObj = $library_publication_handler->get($clean_publication_id);
} else {
	$publicationObj = '';
}

// Optional tagging support (only if Sprockets module installed)
$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
$libraryModule = icms::handler("icms_module")->getByDirname("library");
if (icms_get_module_status("sprockets"))
{
	// Prepare common Sprockets handlers and buffers
	icms_loadLanguageFile("sprockets", "common");
	$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'), 'sprockets');
	$sprockets_taglink_handler = icms_getModuleHandler('taglink', $sprocketsModule->getVar('dirname'), 'sprockets');
	if ($clean_label_type) {
		$sprockets_tag_buffer = $sprockets_tag_handler->getCategoryBuffer($libraryModule->getVar('mid'));
	} else {
		$sprockets_tag_buffer = $sprockets_tag_handler->getTagBuffer(TRUE);
	}

	// Append the tag to the breadcrumb title
	if (array_key_exists($clean_tag_id, $sprockets_tag_buffer) && ($clean_tag_id !== 0))
	{
		$library_tag_name = $sprockets_tag_buffer[$clean_tag_id]->getVar('title');
		$icmsTpl->assign('library_tag_name', $library_tag_name);
		$icmsTpl->assign('library_category_path', $sprockets_tag_buffer[$clean_tag_id]->getVar('title'));
	} elseif ($untagged_content) {
		$icmsTpl->assign('library_tag_name', _CO_LIBRARY_PUBLICATION_UNTAGGED);
		$icmsTpl->assign('library_category_path', _CO_LIBRARY_PUBLICATION_UNTAGGED);
	}
}

// RSS feed links
if (icms_get_module_status("sprockets") && $clean_tag_id 
		&& array_key_exists($clean_tag_id, $sprockets_tag_buffer) 
		&& ($sprockets_tag_buffer[$clean_tag_id]->getVar('rss', 'e') == '1')) {
	$icmsTpl->assign('library_rss_link', 'rss.php?tag_id=' . $clean_tag_id);
	$icmsTpl->assign('library_rss_title', _CO_LIBRARY_SUBSCRIBE_RSS_ON
			. $sprockets_tag_buffer[$clean_tag_id]->getVar('title'));
	$rss_attributes = array('type' => 'application/rss+xml', 
		'title' => $icmsConfig['sitename'] . ' - ' 
		. $sprockets_tag_buffer[$clean_tag_id]->getVar('title', 'e'));
	$rss_link = LIBRARY_URL . 'rss.php?tag_id=' . $clean_tag_id;
} else {				
		$icmsTpl->assign('library_rss_link', 'rss.php');
		$icmsTpl->assign('library_rss_title', _CO_LIBRARY_SUBSCRIBE_RSS);
		$rss_attributes = array('type' => 'application/rss+xml', 
			'title' => $icmsConfig['sitename'] . ' - ' .  _CO_LIBRARY_NEW);
		$rss_link = LIBRARY_URL . 'rss.php';
}

// Add RSS auto-discovery link to module header
$xoTheme->addLink('alternate', $rss_link, $rss_attributes);

////////////////////////////////////////////////////////////////////
//////////////////// DISPLAY SINGLE PUBLICATION ////////////////////
////////////////////////////////////////////////////////////////////

if($publicationObj && !$publicationObj->isNew()) 
{
	// If the m3u flag is present stream the linked content should be STREAMED via m3u playlist
	if ($clean_m3u == 1) {
		$publicationObj->initiateStreaming();
	}
	// Display single object
	else
	{	
		// Prepare publication for display
		$publication = $library_publication_handler->toArrayForDisplay($publicationObj, TRUE);

		// update hits counter
		if (!icms_userIsAdmin(icms::$module->getVar('dirname')))
		{
			$library_publication_handler->updateCounter($publicationObj);
		}

		// Prepare tags for display (only if Sprockets module installed)
		if (icms_get_module_status("sprockets"))
		{
			$publication['tags'] = array();
			$publication_tag_array = $sprockets_taglink_handler->getTagsForObject($publicationObj->getVar('publication_id'), $library_publication_handler, '0');
			foreach ($publication_tag_array as $key => $value) {
				$publication['tags'][$value] = '<a href="' . LIBRARY_URL . 'publication.php?tag_id=' . $value 
						. '">' . $sprockets_tag_buffer[$value]->getVar('title') . '</a>';
			}
			$publication['tags'] = implode(', ', $publication['tags']);
		}
		
		// Collections - append links to related publications
		if ($publicationObj->getVar('type', 'e') == 'Collection')
		{
			$criteria = '';
			$related_work_objects = $related_works = array();
			
			$criteria = icms_buildCriteria(array('source' => $publication['publication_id'], 
				'online_status', '1'));
			$related_work_objects = $library_publication_handler->getObjects($criteria, TRUE);
			if ($related_work_objects) {
				foreach ($related_work_objects as $pubObj) {
					$related_works[] = '<a href="' . LIBRARY_URL . 'publication.php?publication_id=' 
							. $pubObj->getVar('publication_id') . '">' 
							. $pubObj->getVar('title', 'e') . '</a>';
				}
				$publication['related_works'] = $related_works;
			}
		}

		// Assign to template
		$icmsTpl->assign("library_publication_view_mode", "single");
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
}

////////////////////////////////////////////////////////////////////
//////////////////// DISPLAY INDEX PAGE ////////////////////////////
////////////////////////////////////////////////////////////////////

else
{
	// Set page title
	$icmsTpl->assign("library_page_title", _MD_LIBRARY_ALL_PUBLICATIONS);
	
	// Get a select box (if preferences allow, and only if Sprockets module installed)
	if (icms_get_module_status("sprockets") && icms::$module->config['library_show_tag_select_box'] == TRUE) {
		if ($clean_label_type == '0') { // Get tag select box
			if ($untagged_content) {
				$tag_select_box = $sprockets_tag_handler->getTagSelectBox('publication.php', 
						'untagged', _CO_LIBRARY_PUBLICATION_ALL_TAGS, TRUE, 
						icms::$module->getVar('mid'), 'publication', TRUE);					
			} else {
				$tag_select_box = $sprockets_tag_handler->getTagSelectBox('publication.php', 
						$clean_tag_id, _CO_LIBRARY_PUBLICATION_ALL_TAGS, TRUE, 
						icms::$module->getVar('mid'), 'publication', TRUE);
			}
		} else { // Get category select box
			$tag_select_box = $sprockets_tag_handler->getCategorySelectBox('publication.php', $clean_tag_id, 
				_CO_LIBRARY_PUBLICATION_ALL_TAGS, TRUE, icms::$module->getVar('mid'), 'publication',
					FALSE);
		}
		$icmsTpl->assign('library_tag_select_box', $tag_select_box);
	}
	
	// Generate page metadata (can be customised in module preferences)
	global $icmsConfigMetaFooter;
	$library_meta_keywords = $library_meta_description = '';
	
	if (icms::$module->config['library_meta_keywords']) {
		$library_meta_keywords = icms::$module->config['library_meta_keywords'];
	} else {
		$library_meta_keywords = $icmsConfigMetaFooter['meta_keywords'];
	}
	if (icms::$module->config['library_meta_description']) {
		$library_meta_description = icms::$module->config['library_meta_description'];
	} else {
		$library_meta_description = $icmsConfigMetaFooter['meta_description'];
	}
	$icms_metagen = new icms_ipf_Metagen(icms::$module->getVar('name'), $library_meta_keywords, 
		$library_meta_description);
	$icms_metagen->createMetaTags();
	
	// Check if there are any subcategories that should be displayed (immediate children only)
	if ($clean_tag_id && $clean_label_type && icms_get_module_status("sprockets")) {
		$subcategory_array = array();
		$criteria = icms_buildCriteria(array('parent_id' => $clean_tag_id, 'label_type' => '1'));
		$criteria->setSort('title');
		$criteria->setorder('ASC');
		$subcategory_array = $sprockets_tag_handler->getObjects($criteria, TRUE, TRUE);
		if ($subcategory_array)
		{
			// Get a count of the number of publications in each subcategory
			$subcategory_ids = $publication_ids = array();
			$subcategory_ids = "(" . implode(',', array_keys($subcategory_array)) . ")";
			$criteria = icms_buildCriteria(array('online_status' => '0'));
			$publication_ids = $library_publication_handler->getList($criteria);
			if ($publication_ids) {
				$publication_ids = array_keys($publication_ids);
				$publication_ids = "(" . implode(',', $publication_ids) . ")";
			}
			unset($criteria);

			$criteria = new icms_db_criteria_Compo();
			$criteria->add(new icms_db_criteria_Item('tid', $subcategory_ids, 'IN'));
			if ($publication_ids) {
				$criteria->add(new icms_db_criteria_Item('iid', $publication_ids, 'NOT IN'));
			}
			$criteria->add(new icms_db_criteria_Item('mid', icms::$module->getVar('mid')));
			$criteria->add(new icms_db_criteria_Item('item', 'publication'));
			$criteria->setGroupby('tid');
			$count = $sprockets_taglink_handler->getCount($criteria);
			unset($criteria);
			
			$i = 1;
			foreach ($subcategory_array as $subcatObj) {
				
				$subcat = array();
				
				// Add SEO to the link and a publication count
				$subcat['itemLink'] = modifyItemLink($subcatObj->getVar('tag_id'), 
						$subcatObj->getVar('title'), $subcatObj->getVar('short_url'));
				if (isset($count[$subcatObj->getVar('tag_id')])) {
					$publication_count = $count[$subcatObj->getVar('tag_id')];
				} else {
					$publication_count = 0;
				}
				$icmsTpl->append('library_subcategories', array('itemLink' => $subcat['itemLink'], 
					'count' => $i, 'publicationCount' => $publication_count));
				$i++;
			}
		}
	}
	
	// View publications index as a list of summaries
	if (icms::$module->config['library_publication_index_display_mode'] == '1')
	{
		// Initialise
		$criteria = $publication_count = '';
		$library_publication_summaries = array();
		
		// Sort publications by tag or category
		if (($clean_tag_id || $untagged_content) && icms_get_module_status("sprockets")) {
			
			// Retrieve publications for a given tag
			$publication_count = $library_publication_handler->getPublicationCountForTag($clean_tag_id);
			$library_publication_summaries = $library_publication_handler->getPublicationsForTag($clean_tag_id, 
					$publication_count, $clean_start);
			$icmsTpl->assign('library_publication_summaries', $library_publication_summaries);

			// Pagination control - adust for tag (and label_type), if present
			if ($clean_tag_id) {
				$extra_arg = 'tag_id=' . $clean_tag_id;
				if ($clean_label_type) {
					$extra_arg .= '&amp;label_type=1';
				} else {
					$extra_arg .= '&amp;label_type=0';
				}
			}
			else {
				$extra_arg = FALSE;
			}
			$pagenav = new icms_view_PageNav($publication_count, 
				icms::$module->config['number_publications_per_page'], $clean_start, 'start', $extra_arg);
			$icmsTpl->assign('library_navbar', $pagenav->renderNav());
		} else { // Do not sort by tag
			$criteria = new icms_db_criteria_Compo();
			$criteria->add(new icms_db_criteria_Item('online_status', '1'));

			// Count the number of online publications for the pagination control
			$publication_count = $library_publication_handler->getCount($criteria);

			// Continue to retrieve publications for this page view
			$criteria->setStart($clean_start);
			$criteria->setLimit(icms::$module->config['number_publications_per_page']);
			$criteria->setSort('date');
			$criteria->setOrder('DESC');
			$library_publication_summaries = $library_publication_handler->getObjects($criteria, TRUE, TRUE);			
			
			// Manually convert some fields to human readable from buffers, to reduce query load
			$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
			$format_buffer = $system_mimetype_handler->getObjects(FALSE, TRUE, TRUE);
			if (icms_get_module_status("sprockets")) {
				$sprockets_rights_handler = icms_getModuleHandler('rights', 'sprockets', 'sprockets');
				$rights_buffer = $sprockets_rights_handler->getObjects(FALSE, TRUE, TRUE);
			}
			foreach ($library_publication_summaries as &$publication) {
				$publication = $library_publication_handler->toArrayForDisplay($publication, FALSE);
				if (icms_get_module_status("sprockets") && isset($publication['rights'])) {
					$publication['rights'] = $rights_buffer[$publication['rights']]->getItemLink();
				}
				if (isset($publication['format'])) {
					$publication['format'] = $format_buffer[$publication['format']]->getVar('extension');
				}
			}

			// Assign publications to template
			$icmsTpl->assign('library_publication_summaries', $library_publication_summaries);

			// Pagination control
			$pagenav = new icms_view_PageNav($publication_count, 
					icms::$module->config['number_publications_per_page'], $clean_start, 'start', FALSE);
			$icmsTpl->assign('library_navbar', $pagenav->renderNav());
		}
		// If there are no publications, then display a message
		if (!$library_publication_summaries) {
			$icmsTpl->assign('library_no_publications', TRUE);
		}
	} else { // Display publication index as compact table
	
		$criteria = new icms_db_criteria_Compo();
		if (($clean_tag_id || $untagged_content) && array_key_exists($clean_tag_id, $sprockets_tag_buffer) 
				&& icms_get_module_status("sprockets")) {
			// Get a list of publication IDs belonging to this tag
			$criteria->add(new icms_db_criteria_Item('tid', $clean_tag_id));
			$criteria->add(new icms_db_criteria_Item('mid', icms::$module->getVar('mid')));
			$criteria->add(new icms_db_criteria_Item('item', 'publication'));			
			$taglink_array = $sprockets_taglink_handler->getObjects($criteria);
			foreach ($taglink_array as $taglink) {
				$categorised_publication_list[] = $taglink->getVar('iid');
			}
			$categorised_publication_list = "('" . implode("','", $categorised_publication_list) . "')";
			unset($criteria);

			// Use the list to filter the persistable table
			$criteria = new icms_db_criteria_Compo();
			$criteria->add(new icms_db_criteria_Item('online_status', '1'));
			$criteria->add(new icms_db_criteria_Item('publication_id', $categorised_publication_list, 'IN'));
		}
		
		if (empty($criteria)) {
			$criteria = null;
		}
		
		$objectTable = new icms_ipf_view_Table($library_publication_handler, $criteria, array());
		$objectTable->isForUserSide();
		$objectTable->addQuickSearch('title');
		$objectTable->addColumn(new icms_ipf_view_Column("title"));
		$objectTable->addColumn(new icms_ipf_view_Column("creator"));
		$objectTable->addColumn(new icms_ipf_view_Column("type"));
		$objectTable->addColumn(new icms_ipf_view_Column("date"));
		$objectTable->setDefaultSort('date');
		$objectTable->setDefaultOrder('DESC');
		$objectTable->addFilter('format', 'format_filter');
		$objectTable->addFilter('type' , 'type_filter');
		$objectTable->addFilter('rights', 'rights_filter');
		
		$icmsTpl->assign("library_publication_table", $objectTable->fetch());
	}
}

$icmsTpl->assign("library_show_breadcrumb", icms::$module->config['library_show_breadcrumb']);
$icmsTpl->assign("library_module_home", '<a href="' . ICMS_URL . "/modules/" 
		. icms::$module->getVar("dirname") . '/">' . icms::$module->getVar("name") . "</a>");
$icmsTpl->assign("page_displaytitle", "Publications");

include_once "footer.php";