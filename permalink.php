<?php

/**
 * Handles incoming permalink requests
 *
 * @copyright	Copyright Isengard.biz 2010, distributed under GNU GPL V2 or any later version
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since	1.0
 * @author	Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package	Library
 * @version	$Id$
 */

include_once "../../mainfile.php";
include_once ICMS_ROOT_PATH . "/header.php";

$dirty_oai_identifier = isset($_GET["id"]) ? $_GET["id"] : 0 ;
if ($dirty_oai_identifier)
{
	
	// Sanitise the parameter
	$dirty_oai_identifier = trim($dirty_oai_identifier);
	$dirty_oai_identifier = filter_var($dirty_oai_identifier, 
			FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
	$clean_oai_identifier = mysql_real_escape_string($dirty_oai_identifier);

	// Lookup the id of the requested publication
	if ($clean_oai_identifier)
	{
		$pub_id = $criteria = '';
		
		$criteria = icms_buildCriteria(array('oai_identifier' => $clean_oai_identifier));
		$library_publication_handler = icms_getModuleHandler('publication'
				, basename(dirname(__FILE__)), 'library');
		$publicationObj = array_shift($library_publication_handler->getObjects($criteria));		
		if ($publicationObj) {
			header('location: publication.php?publication_id=' . $publicationObj->getVar('publication_id'));
			exit();
		}
	}	
}

// If any test or result fails for any reason, can it
exit();