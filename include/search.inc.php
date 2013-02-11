<?php
/**
 * Library search function
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

/**
 * Provides search functionality for the library module
 *
 * @param array $queryarray
 * @param string $andor
 * @param int $limit
 * @param int $offset
 * @param int $userid
 * @return array 
 */

function library_search($queryarray, $andor, $limit, $offset = 0, $userid = 0)
{
	global $icmsConfigSearch;
	
	$publicationArray = $ret = array();
	$count = $pubs_left = $number_to_process = '';
	
	$library_publication_handler = icms_getModuleHandler("publication", 
			basename(dirname(dirname(__FILE__))), "library");
	$publicationArray = $library_publication_handler->getPublicationsForSearch($queryarray, $andor, 
			$limit, $offset, $userid);
		
	// Count the number of records
	$count = count($publicationArray);
	
	// The number of records actually containing publication objects is <= $limit, the rest are padding
	// How to figure out how what the actual number of publications is? Important for pagination
	$pubs_left = ($count - ($offset + $icmsConfigSearch['search_per_page']));
	if ($pubs_left < 0) {
		$number_to_process = $icmsConfigSearch['search_per_page'] + $pubs_left; // $pubs_left is negative
	} else {
		$number_to_process = $icmsConfigSearch['search_per_page'];
	}
			
	// Process the actual publications (not the padding)
	for ($i = 0; $i < $number_to_process; $i++) {
		$item['image'] = "images/publication.png";
		$item['link'] = $publicationArray[$i]->getItemLink(TRUE);
		$item['title'] = $publicationArray[$i]->getVar("title");
		$item['time'] = $publicationArray[$i]->getVar("date", "e");
		$item['uid'] = $publicationArray[$i]->getVar("submitter", "e");
		$ret[] = $item;
		unset($item);
	}
	
	// Restore the padding (required for 'hits' information and pagination controls). The offset
	// must be padded to the left of the results, and the remainder to the right or else the search
	// pagination controls will display the wrong results (which will all be empty).
	// Left padding = -($limit + $offset)
	$ret = array_pad($ret, -($offset + $number_to_process), 1);
	
	// Right padding = $count
	$ret = array_pad($ret, $count, 1);

	return $ret;
}