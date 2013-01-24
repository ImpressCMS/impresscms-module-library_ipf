<?php
/**
* Increments counter for downloadable publications and hands off the file
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

// Get the id of the requested publication
$clean_publication_id = isset($_GET["publication_id"]) ? (int)$_GET["publication_id"] : 0 ;

// Check if the publication is online and published
$library_publication_handler = icms_getModuleHandler('publication', basename(dirname(__FILE__)), 'library');
$publicationObj = $library_publication_handler->get($clean_publication_id);
if ($publicationObj && !$publicationObj->isNew())
{
	$online = $publicationObj->getVar('online_status', 'e');
	$time = time();
	$date = $publicationObj->getVar('date', 'e');
	$identifier = $publicationObj->getVar('identifier', 'e');
	
	if ($online == '1' && ($date < $time) && !empty($identifier)) {
		
		// Increment visit counter
		if (!icms_userIsAdmin(icms::$module->getVar('dirname'))) {
			$library_publication_handler->updateCounter($publicationObj);
		}
		
		// Redirect to the file
		$url = filter_var(FILTER_SANITIZE_URL);
		$url = filter_var(FILTER_VALIDATE_URL);
		if ($url) {
			header("Location: " . $publicationObj->getVar('identifier', 'e'));
		}
		exit;
		
		// WF-Downloads style header hand off (experimental)
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", FALSE);
		header("Pragma: no-cache"); // HTTP 1.0 backwards compatibility
		
		// Always modified
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Refresh: 3; url=$url");
		header("Pragma: public"); // HTTP 1.0 backwards compatibility
		
		$header_file = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ? preg_replace('/\./', '%2e', $file_name, substr_count($file_name, '.') - 1) : $file_name;
	
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Length: ".(string)(filesize($filePath)));
		header("Content-Transfer-Encoding: binary");
		if(isset($mimeType)) {header("Content-Type: " . $mimeType);}
		header("Content-Disposition: attachment; filename=".$header_file);
	}
}

$icmsTpl->assign('library_publication_unavailable', TRUE);
$icmsTpl->assign("library_page_title", _MD_LIBRARY_DOWNLOAD);

include_once "footer.php";





