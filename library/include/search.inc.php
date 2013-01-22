<?php
/**
 * Library version infomation
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

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

function library_search($queryarray, $andor, $limit, $offset, $userid)
{
	$library_publication_handler = icms_getModuleHandler("publication", basename(dirname(dirname(__FILE__))), "library");
	$publicationArray = $library_publication_handler->getPublicationsForSearch($queryarray, $andor, $limit, $offset, $userid);

	$ret = array();

	foreach ($publicationArray as $publication) {
		$item['image'] = "images/publication.png";
		$item['link'] = $publication->getItemLink(TRUE);
		$item['title'] = $publication->getVar("title");
		$item['time'] = $publication->getVar("date", "e");
		$item['uid'] = $publication->getVar("creator");
		$ret[] = $item;
		unset($item);
	}

	return $ret;
}