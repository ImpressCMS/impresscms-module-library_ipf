<?php
/**
 * New comment form
 *
 * This file holds the configuration information of this module
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

include_once "header.php";
$com_itemid = isset($_GET["com_itemid"]) ? (int)$_GET["com_itemid"] : 0;
if ($com_itemid > 0) {
	$library_publication_handler = icms_getModuleHandler("publication", basename(dirname(__FILE__)), "library");
	$obj = $library_publication_handler->get($com_itemid);
	if ($obj && !$obj->isNew()) {
		$com_replytext = "";
		$bodytext = $obj->getVar('description');
		if ($bodytext != "") {
			$com_replytext .= "<br /><br />".$bodytext;
		}
		$com_replytitle = $obj->getVar("title");
		include_once ICMS_ROOT_PATH . "/include/comment_new.php";
	}
}