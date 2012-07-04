<?php
/**
 * Admin page to manage publications
 *
 * List, add, edit and delete publication objects
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

/**
 * Edit a Publication
 *
 * @param int $publication_id Publicationid to be edited
*/
function editPublication($pubObj) 
{
	global $library_publication_handler, $icmsModule, $icmsAdminTpl;

	$pubObj->contextualiseFormFields();

	if (!$pubObj->isNew()){
		$pubObj->loadTags();
		$pubObj->loadCategories();
		$icmsModule->displayAdminMenu(0, _AM_LIBRARY_PUBLICATIONS . " > " . _CO_ICMS_EDITING);
		$sform = $pubObj->getForm(_AM_LIBRARY_PUBLICATION_EDIT, "addpublication");
		$sform->assign($icmsAdminTpl);
	} else {
		$icmsModule->displayAdminMenu(0, _AM_LIBRARY_PUBLICATIONS . " > " . _CO_ICMS_CREATINGNEW);
		$sform = $pubObj->getForm(_AM_LIBRARY_PUBLICATION_CREATE, "addpublication");
		$sform->assign($icmsAdminTpl);

	}
	$icmsAdminTpl->display("db:library_admin_publication.html");
}

include_once "admin_header.php";

$library_publication_handler = icms_getModuleHandler("publication", basename(dirname(dirname(__FILE__))), "library");

$clean_op = "";
/** Create a whitelist of valid values, be sure to use appropriate types for each value
 * Be sure to include a value for no parameter, if you have a default condition
 */
$valid_op = array ("mod", "changedField", "addpublication", "del", "view", "");

if (isset($_GET["op"])) $clean_op = htmlentities($_GET["op"]);
if (isset($_POST["op"])) $clean_op = htmlentities($_POST["op"]);

$clean_publication_id = isset($_GET["publication_id"]) ? (int)$_GET["publication_id"] : 0 ;
$clean_tag_id = isset($_GET['tag_id']) ? intval($_GET['tag_id']) : 0 ;

if (in_array($clean_op, $valid_op, TRUE)) {
	switch ($clean_op)
	{
		case "mod":
				icms_cp_header();
				$publicationObj = $library_publication_handler->get($clean_publication_id);
				editPublication($publicationObj);
			break;
		
		case "changedField":
			icms_cp_header();
			$publicationObj = $library_publication_handler->get($clean_publication_id);
			if (isset($_POST['op']))
			{
				$controller = new icms_ipf_Controller($library_publication_handler);
				$controller->postDataToObject($publicationObj);
			}
			editpublication($publicationObj);
			break;

		case "addpublication":
			$controller = new icms_ipf_Controller($library_publication_handler);
			$controller->storeFromDefaultForm(_AM_LIBRARY_PUBLICATION_CREATED, _AM_LIBRARY_PUBLICATION_MODIFIED);
			break;

		case "del":
			$controller = new icms_ipf_Controller($library_publication_handler);
			$controller->handleObjectDeletion();
			break;

		case "view" :
			$publicationObj = $library_publication_handler->get($clean_publication_id);
			icms_cp_header();
			$publicationObj->displaySingleObject();
			break;

		default:
			icms_cp_header();
			$icmsModule->displayAdminMenu(0, _AM_LIBRARY_PUBLICATIONS);
			
		// Display a category select filter (if the Sprockets module is installed)
		$module = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));
		$sprocketsModule = icms_getModuleInfo('sprockets');
		
		if (icms_get_module_status("sprockets"))
		{
			////////////////////////////////////
			////////// TAG SELECT BOX //////////
			////////////////////////////////////
			$tag_select_box = '';
			$taglink_array = $tagged_publication_list = array();
			$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
			$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');

			$tag_select_box = $sprockets_tag_handler->getTagSelectBox('publication.php', $clean_tag_id,
				_AM_LIBRARY_PUBLICATION_ALL_PUBLICATIONS, FALSE, icms::$module->getVar('mid'), 'publication');

			if ($clean_tag_id)
			{
				// Get a list of publication IDs belonging to this tag
				$criteria = new icms_db_criteria_Compo();
				$criteria->add(new icms_db_criteria_Item('tid', $clean_tag_id));
				$criteria->add(new icms_db_criteria_Item('mid', icms::$module->getVar('mid')));
				$criteria->add(new icms_db_criteria_Item('item', 'publication'));
				$taglink_array = $sprockets_taglink_handler->getObjects($criteria);
				foreach ($taglink_array as $taglink) {
					$tagged_publication_list[] = $taglink->getVar('iid');
				}
				$tagged_publication_list = "('" . implode("','", $tagged_publication_list) . "')";

				// Use the list to filter the persistable table
				$criteria = new icms_db_criteria_Compo();
				$criteria->add(new icms_db_criteria_Item('publication_id', $tagged_publication_list, 'IN'));
			}
			
			/////////////////////////////////////////
			////////// CATEGORY SELECT BOX //////////
			/////////////////////////////////////////
			$category_select_box = '';
			$taglink_array = $categorised_publication_list = array();
			$sprockets_tag_handler = icms_getModuleHandler('tag', $sprocketsModule->getVar('dirname'),
				'sprockets');
			$sprockets_taglink_handler = icms_getModuleHandler('taglink',
					$sprocketsModule->getVar('dirname'), 'sprockets');
			
			$category_select_box = $sprockets_tag_handler->getCategorySelectBox('publication.php', 
						$clean_tag_id, _AM_LIBRARY_PUBLICATION_ALL_PUBLICATIONS, icms::$module->getVar('mid'));
			
			if ($clean_tag_id)
			{
				// Get a list of message IDs belonging to this tag
				$criteria = new icms_db_criteria_Compo();
				$criteria->add(new icms_db_criteria_Item('tid', $clean_tag_id));
				$criteria->add(new icms_db_criteria_Item('mid', $module->getVar('mid')));
				$criteria->add(new icms_db_criteria_Item('item', 'publication'));
				$taglink_array = $sprockets_taglink_handler->getObjects($criteria);
				foreach ($taglink_array as $taglink) {
					$categorised_publication_list[] = $taglink->getVar('iid');
				}
				$categorised_publication_list = "('" . implode("','", $categorised_publication_list) . "')";
				
				// Use the list to filter the persistable table
				$criteria = new icms_db_criteria_Compo();
				$criteria->add(new icms_db_criteria_Item('publication_id', $categorised_publication_list, 'IN'));
			}
		}
		
		// Display the tag/category select boxes in a table, side by side to save space
		if (!empty($tag_select_box) || !empty($category_select_box))
		{
			$select_box_code = '<table><tr>';
			if (!empty($tag_select_box)) {
				$select_box_code .= '<td><h3>' . _AM_LIBRARY_PUBLICATION_FILTER_BY_TAG . '</h3></td>';
			}
			if (!empty($category_select_box)) {
				$select_box_code .= '<td><h3>' . _AM_LIBRARY_PUBLICATION_FILTER_BY_CATEGORY . '</h3></td>';
			}
			$select_box_code .= '</tr><tr>';
			if (!empty($tag_select_box)) {
				$select_box_code .= '<td>' . $tag_select_box . '</td>';
			}
			if (!empty($category_select_box)) {
				$select_box_code .= '<td>' . $category_select_box . '</td>';
			}
			echo $select_box_code . '</tr></table>';
		}
		
		if (empty($criteria)) {
			$criteria = null;
		}
		
			$objectTable = new icms_ipf_view_Table($library_publication_handler, $criteria);
			$objectTable->addColumn(new icms_ipf_view_Column("title"));
			$objectTable->addIntroButton("addpublication", "publication.php?op=mod", _AM_LIBRARY_PUBLICATION_CREATE);
			$icmsAdminTpl->assign("library_publication_table", $objectTable->fetch());
			$icmsAdminTpl->display("db:library_admin_publication.html");
			break;
	}
	icms_cp_footer();
}
/**
 * If you want to have a specific action taken because the user input was invalid,
 * place it at this point. Otherwise, a blank page will be displayed
 */