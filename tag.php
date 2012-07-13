<?php
/**
* Tag/category display page. If label_type = true the page will display categories, otherwise tags
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2012
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		library
* @version		$Id$
*/

include_once "header.php";
$xoopsOption["template_main"] = "library_tag.html";
include_once ICMS_ROOT_PATH . "/header.php";

$clean_label_type = isset($_GET["label_type"]) ? (int)$_GET["label_type"] : 0 ;
$clean_tag_id = isset($_GET["tag_id"]) ? (int)$_GET["tag_id"] : 0 ;
$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');

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
	$tag_ids = '(' . implode(',', $tag_ids) . ')';
}

// Get a list of tags or categories
if ($tag_ids) {
	$criteria = new icms_db_criteria_Compo();
	$criteria->add(new icms_db_criteria_Item('label_type', $clean_label_type));
	$criteria->add(new icms_db_criteria_Item('tag_id', $tag_ids, 'IN'));
	$criteria->setSort('title');
	$criteria->setOrder('ASC');
	$tag_list = $sprockets_tag_handler->getObjects($criteria, TRUE, FALSE);
	$icmsTpl->assign('library_tag_list', $tag_list);
}

////////////////////////////////////////////////////////
////////// Display tag or category index page //////////
////////////////////////////////////////////////////////

if ($clean_tag_id == '0') // Indicates index page should be displayed
{
	if ($clean_label_type == '1') // Display the CATEGORY index page
	{
		$icmsTpl->assign("library_page_title", _CO_LIBRARY_CATEGORY_INDEX);
	}
	else // Display the TAG index page
	{
		$icmsTpl->assign("library_page_title", _CO_LIBRARY_TAG_INDEX);
	}
}

$icmsTpl->assign("library_show_breadcrumb", icms::$module->config['library_show_breadcrumb']);
$icmsTpl->assign("library_module_home", '<a href="' . ICMS_URL . "/modules/" 
		. icms::$module->getVar("dirname") . '/">' . icms::$module->getVar("name") . "</a>");

include_once "footer.php";