<?php
/**
 * Recent publications block file
 *
 * This file holds the functions needed for the recent publications block
 *
 * @copyright	http://smartfactory.ca The SmartFactory
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		marcan aka Marc-André Lanciault <marcan@smartfactory.ca>
 * @author		Madfish aka Simon Wilkinson <simon@isengard.biz>
 * Modified for use in the Library module by Madfish
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ICMS root path not defined");

/**
 * Prepare recent publications block for display
 *
 * @param array $options
 * @return array 
 */
function show_recent_publications($options)
{
	$publicationObjects = array();
	$libraryModule = icms::handler("icms_module")->getByDirname('library');
	$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
		
	include_once(ICMS_ROOT_PATH . '/modules/' . $libraryModule->getVar('dirname') . '/include/common.php');
	$library_publication_handler = icms_getModuleHandler('publication', $libraryModule->getVar('dirname'), 'library');
	
	if (icms_get_module_status("sprockets"))
	{
		icms_loadLanguageFile("sprockets", "common");
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', $sprocketsModule->getVar('dirname'), 'sprockets');
	}
	
	$publicationList = $publications = array();
	$criteria = new icms_db_criteria_Compo();
	
	// Sanitise the options as a precaution, since they are used in a manual query string
	$clean_limit = isset($options[0]) ? (int)$options[0] : 0;
	$clean_tag_id = isset($options[1]) ? (int)$options[1] : 0 ;

	// Get a list of publications filtered by tag
	if (icms_get_module_status("sprockets") && $clean_tag_id != 0)
	{
		$query = "SELECT * FROM " . $library_publication_handler->table . ", "
			. $sprockets_taglink_handler->table
			. " WHERE `publication_id` = `iid`"
			. " AND `tid` = '" . $clean_tag_id . "'"
			. " AND `mid` = '" . $libraryModule->getVar('mid') . "'"
			. " AND `item` = 'publication'"
			. " AND `online_status` = '1'"
			. " ORDER BY `submission_time` DESC"
			. " LIMIT 0," . $clean_limit;

		$result = icms::$xoopsDB->query($query);

		if (!$result) 
		{
			echo 'Error: Recent publications block';
			exit;
		}
		else
		{
			$rows = $library_publication_handler->convertResultSet($result, TRUE, TRUE);
			foreach ($rows as $key => $row) 
			{
				$publicationObjects[$key] = $row;
			}
		}
	}
	// Otherwise just get a list of all publications
	
	else 
	{
		$criteria->add(new icms_db_criteria_Item('online_status', '1'));
		$criteria->setSort('date');
		$criteria->setOrder('DESC');
		$criteria->setLimit($clean_limit);
		$publicationObjects = $library_publication_handler->getObjects($criteria, TRUE, TRUE);
	}

	// Prepare publication for display
	$publication_list = array();
	foreach ($publicationObjects as $key => $object)
	{
		$publication = array();
		$publication['title'] = $object->getVar('title');
		$publication['submission_time'] = $object->getVar('submission_time');
		
		// Add SEO friendly string to URL
		$short_url = $object->getVar('short_url', 'e');
		if (!empty($short_url))
		{
			$publication['itemUrl'] = $object->getItemLink(TRUE) . "&amp;title=" . $short_url;
		}
		$publication_list[] = $publication;
	}
	
	// Assign to template
	$block['library_recent_publications'] = $publication_list;

	return $block;
}

/**
 * Edit recent publications block options
 *
 * @param array $options
 * @return string 
 */
function edit_recent_publications($options) 
{
	$libraryModule = icms::handler("icms_module")->getByDirname('library');
	include_once(ICMS_ROOT_PATH . '/modules/' . $libraryModule->getVar('dirname') . '/include/common.php');
	$library_publication_handler = icms_getModuleHandler('publication', $libraryModule->getVar('dirname'), 'library');
	
	// Select number of recent publications to display in the block
	$form = '<table>';
	$form .= '<tr><td>' . _MB_LIBRARY_RANDOM_LIMIT . '</td>';
	$form .= '<td>' . '<input type="text" name="options[0]" value="' . $options[0] . '"/></td></tr>';	
	
	// Optionally display results from a single tag - but only if sprockets module is installed
	$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");

	if (icms_get_module_status("sprockets"))
	{
		$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'), 'sprockets');
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', $sprocketsModule->getVar('dirname'), 'sprockets');
		
		// Get only those tags that contain content from this module
		$criteria = '';
		$relevant_tag_ids = array();
		$criteria = icms_buildCriteria(array('mid' => $libraryModule->getVar('mid')));
		$library_module_taglinks = $sprockets_taglink_handler->getObjects($criteria, TRUE, TRUE);
		foreach ($library_module_taglinks as $key => $value)
		{
			$relevant_tag_ids[] = $value->getVar('tid');
		}
		$relevant_tag_ids = array_unique($relevant_tag_ids);
		$relevant_tag_ids = '(' . implode(',', $relevant_tag_ids) . ')';
		unset($criteria);

		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('tag_id', $relevant_tag_ids, 'IN'));
		$criteria->add(new icms_db_criteria_Item('label_type', '0'));
		$tagList = $sprockets_tag_handler->getList($criteria);

		$tagList = array(0 => _MB_LIBRARY_RECENT_ALL) + $tagList;
		$form .= '<tr><td>' . _MB_LIBRARY_RECENT_TAG . '</td>';
		// Parameters icms_form_elements_Select: ($caption, $name, $value = null, $size = 1, $multiple = TRUE)
		$form_select = new icms_form_elements_Select('', 'options[1]', $options[1], '1', FALSE);
		$form_select->addOptionArray($tagList);
		$form .= '<td>' . $form_select->render() . '</td></tr>';
	}
	
	$form .= '</table>';
	
	return $form;
}