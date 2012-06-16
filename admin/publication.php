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
function editpublication($publication_id = 0) {
	global $library_publication_handler, $icmsModule, $icmsAdminTpl;

	$publicationObj = $library_publication_handler->get($publication_id);

	if (!$publicationObj->isNew()){
		$icmsModule->displayAdminMenu(0, _AM_LIBRARY_PUBLICATIONS . " > " . _CO_ICMS_EDITING);
		$sform = $publicationObj->getForm(_AM_LIBRARY_PUBLICATION_EDIT, "addpublication");
		$sform->assign($icmsAdminTpl);
	} else {
		$icmsModule->displayAdminMenu(0, _AM_LIBRARY_PUBLICATIONS . " > " . _CO_ICMS_CREATINGNEW);
		$sform = $publicationObj->getForm(_AM_LIBRARY_PUBLICATION_CREATE, "addpublication");
		$sform->assign($icmsAdminTpl);

	}
	$icmsAdminTpl->display("db:library_admin_publication.html");
}

include_once "admin_header.php";

$library_publication_handler = icms_getModuleHandler("publication", basename(dirname(dirname(__FILE__))), "library");
/** Use a naming convention that indicates the source of the content of the variable */
$clean_op = "";
/** Create a whitelist of valid values, be sure to use appropriate types for each value
 * Be sure to include a value for no parameter, if you have a default condition
 */
$valid_op = array ("mod", "changedField", "addpublication", "del", "view", "");

if (isset($_GET["op"])) $clean_op = htmlentities($_GET["op"]);
if (isset($_POST["op"])) $clean_op = htmlentities($_POST["op"]);

/** Again, use a naming convention that indicates the source of the content of the variable */
$clean_publication_id = isset($_GET["publication_id"]) ? (int)$_GET["publication_id"] : 0 ;

/**
 * in_array() is a native PHP function that will determine if the value of the
 * first argument is found in the array listed in the second argument. Strings
 * are case sensitive and the 3rd argument determines whether type matching is
 * required
*/
if (in_array($clean_op, $valid_op, TRUE)) {
	switch ($clean_op) {
		case "mod":
		case "changedField":
			icms_cp_header();
			editpublication($clean_publication_id);
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
			$objectTable = new icms_ipf_view_Table($library_publication_handler);
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