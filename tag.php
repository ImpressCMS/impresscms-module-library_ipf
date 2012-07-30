<?php
/**
* Tag/category index display page.
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2012
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		library
* @version		$Id$
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
$xoopsOption["template_main"] = "library_tag.html";
include_once ICMS_ROOT_PATH . "/header.php";

global $icmsConfig, $xoTheme;

// Check if Sprockets module is available, this page is dependent on it
if (icms_get_module_status("sprockets"))
{
	$clean_label_type = isset($_GET["label_type"]) ? (int)$_GET["label_type"] : 0 ;
	$clean_tag_id = isset($_GET["tag_id"]) ? (int)$_GET["tag_id"] : 0 ;
	$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
	$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
	$library_publication_handler = icms_getModuleHandler('publication', basename(dirname(__FILE__)), 'library');
	icms_loadLanguageFile("sprockets", "common");
	
	// Get a count of the total number of online publications
	$criteria = icms_buildCriteria(array('online_status' => '1'));
	$publicationCount = $library_publication_handler->getCount($criteria);
	unset($criteria);
	
	////////////////////////////////////////////////////////
	////////// Display tag or category index page //////////
	////////////////////////////////////////////////////////

	////////////////////////////////////
	////////// Category index //////////
	////////////////////////////////////
	if ($clean_label_type)
	{
		$criteria = '';
		$libraryCategories = $parentCategories = $subcategories = $tag_ids = $publication_ids = array();
		$sprocketsModule = icms::handler("icms_module")->getByDirName("sprockets");
		
		// Get the library category tree
		include ICMS_ROOT_PATH . '/modules/' . $sprocketsModule->getVar('dirname') . '/include/angry_tree.php';
		$criteria = icms_buildCriteria(array('mid' => icms::$module->getVar('mid'), 'label_type' => '1'));
		$libraryCategories = $sprockets_tag_handler->getObjects($criteria, TRUE, TRUE);		
		$categoryTree = new IcmsPersistableTree(&$libraryCategories, 'tag_id', 'parent_id', $rootId = null);
		unset($criteria);
		
		// Get a count of the number of publications in each category - need online publication IDs and category IDs
		$criteria = icms_buildCriteria(array('mid' => icms::$module->getVar('mid'), 'label_type' => '1'));
		$category_ids = $sprockets_tag_handler->getList($criteria, TRUE);
		if ($category_ids) {
			$category_ids = array_keys($category_ids);
			$category_ids = "(" . implode(',', $category_ids) . ")";
		}
		// Since offline publications are probably the minority, more efficient to use these to filter the list
		$criteria = icms_buildCriteria(array('online_status' => '0'));
		$publication_ids = $library_publication_handler->getList($criteria);
		if ($publication_ids) {
			$publication_ids = array_keys($publication_ids);
			$publication_ids = "(" . implode(',', $publication_ids) . ")";
		}
		unset($criteria);
		
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('tid', $category_ids, 'IN'));
		if ($publication_ids) {
			$criteria->add(new icms_db_criteria_Item('iid', $publication_ids, 'NOT IN'));
		}
		$criteria->add(new icms_db_criteria_Item('mid', icms::$module->getVar('mid')));
		$criteria->add(new icms_db_criteria_Item('item', 'publication'));
		$criteria->setGroupby('tid');
		$count = $sprockets_taglink_handler->getCount($criteria);
		unset($criteria);
		
		// Get the top level parent categories and convert to array for template insertion
		$parentCategories = $categoryTree->getFirstChild(0);
		$i = 1;
		foreach ($parentCategories as &$parent) {
			$parent = $parent->toArray();
			
			// Add SEO to the link
			$parent['itemLink'] = modifyItemLink($parent['tag_id'], $parent['title'], $parent['short_url']);
			
			// Add a count of the number of publications in this category
			if (isset($count[$parent['tag_id']])) {
				$parent['publicationCount'] = $count[$parent['tag_id']];
			} else {
				$parent['publicationCount'] = 0;
			}
			
			// Used to divide the page layout into two columns
			$parent['count'] = $i;
			
			// Get the first level child categories for each parent and covert to array for template
			$subcategories = $categoryTree->getFirstChild($parent['tag_id']);
			foreach ($subcategories as &$subcat) {
				$subcat = $subcat->toArray();
				$subcat['itemLink'] = modifyItemLink($subcat['tag_id'], $subcat['title'], $subcat['short_url']);
				$parent['subcategories'][] = $subcat;
			}
		}

		// Assign categories to template
		$icmsTpl->assign("library_page_title", _CO_LIBRARY_CATEGORY_INDEX);
		$icmsTpl->assign('library_category_list', $parentCategories);
		
	}
	///////////////////////////////
	////////// Tag index //////////
	///////////////////////////////
	else
	{
		// Get a list of unique tag_id associated with content for this module
		$query = $rows = $tag_ids = '';
		$query = "SELECT DISTINCT `tid` FROM " . $sprockets_taglink_handler->table
				. " WHERE `mid` = '" . icms::$module->getVar('mid') . "' AND `item` = 'publication'";
		$result = icms::$xoopsDB->query($query);
		if (!$result) {
			echo 'Error';
			exit;
		} else {
			$rows = $sprockets_taglink_handler->convertResultSet($result);
			foreach ($rows as $key => $row) {
				$tag_ids[] = $row->getVar('tid');
			}
			if ($tag_ids) {
				$tag_ids = '(' . implode(',', $tag_ids) . ')';
			}
		}

		// Get a list of tags
		$tag_list = array();
		if ($tag_ids) {
			$criteria = new icms_db_criteria_Compo();
			$criteria->add(new icms_db_criteria_Item('label_type', $clean_label_type));
			$criteria->add(new icms_db_criteria_Item('tag_id', $tag_ids, 'IN'));
			$criteria->setSort('title');
			$criteria->setOrder('ASC');
			$tagObjList = $sprockets_tag_handler->getObjects($criteria, TRUE, TRUE);
			
			// Get publication count for each tag
			$offline_publications = array();
			$criteria = icms_buildCriteria(array('online_status' => '0'));
			$offline_publications = $library_publication_handler->getList($criteria);
			if ($offline_publications) {
				$offline_publications = "(" . implode(',', array_keys($offline_publications)) . ")";
			}
			unset($criteria);
			
			$criteria = new icms_db_criteria_Compo();
			if ($offline_publications) {
				$criteria->add(new icms_db_criteria_Item('iid', $offline_publications, 'NOT IN'));
			}
			$criteria->add(new icms_db_criteria_Item('mid', icms::$module->getVar('mid')));
			$criteria->add(new icms_db_criteria_Item('item', 'publication'));
			$criteria->setGroupby('tid');
			$count = $sprockets_taglink_handler->getCount($criteria);
			unset($criteria);
			
			// Append an SEO-friendly URL (if available) and the label type (if a category) and publication count
			if ($tagObjList) {
				foreach ($tagObjList as $tagObj) {
					$tag = $tagObj->toArray();
					$tag['itemLink'] = '<a href="' . ICMS_URL . '/modules/' . icms::$module->getVar('dirname') 
							. '/publication.php?tag_id=' . $tagObj->getVar('tag_id', 'e');
					if ($tagObj->getVar('label_type', 'e') == '1') {
						$tag['itemLink'] .= '&amp;label_type=1';
					}
					if ($tag['short_url']) {
						$tag['itemLink'] .= '&amp;title=' . $tag['short_url'];
					}
					$tag['itemLink'] .= '">' . $tag['title'] . '</a>';
					if ($count[$tag['tag_id']]) {
						$tag['publicationCount'] = $count[$tag['tag_id']];
					} else {
						$tag['publicationCount'] = 0;
					}
					$tag_list[] = $tag;
				}
			}
			$icmsTpl->assign("library_page_title", _CO_LIBRARY_TAG_INDEX);
			$icmsTpl->assign('library_tag_list', $tag_list);
		}
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
		_CO_LIBRARY_META_TAG_INDEX_DESCRIPTION);
	$icms_metagen->createMetaTags();

	$icmsTpl->assign("library_show_breadcrumb", icms::$module->config['library_show_breadcrumb']);
	$icmsTpl->assign("library_module_home", '<a href="' . ICMS_URL . "/modules/" 
			. icms::$module->getVar("dirname") . '/">' . icms::$module->getVar("name") . "</a>");
	$icmsTpl->assign('library_publication_count', $publicationCount);
}
else
{
	// Sprockets module unavailable - nothing to do
	exit;
}

include_once "footer.php";