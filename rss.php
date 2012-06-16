<?php
/**
 * Generating an RSS feed
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

/** Include the module's header for all pages */
include_once 'header.php';
include_once ICMS_ROOT_PATH . '/header.php';

/** To come soon in imBuilding...

$clean_post_uid = isset($_GET['uid']) ? intval($_GET['uid']) : FALSE;

$library_feed = new icms_feeds_Rss();

$library_feed->title = $icmsConfig['sitename'] . ' - ' . $icmsModule->name();
$library_feed->url = XOOPS_URL;
$library_feed->description = $icmsConfig['slogan'];
$library_feed->language = _LANGCODE;
$library_feed->charset = _CHARSET;
$library_feed->category = $icmsModule->name();

$library_post_handler = icms_getModuleHandler("post", basename(dirname(__FILE__)), "library");
//LibraryPostHandler::getPosts($start = 0, $limit = 0, $post_uid = FALSE, $year = FALSE, $month = FALSE
$postsArray = $library_post_handler->getPosts(0, 10, $clean_post_uid);

foreach($postsArray as $postArray) {
	$library_feed->feeds[] = array (
	  'title' => $postArray['post_title'],
	  'link' => str_replace('&', '&amp;', $postArray['itemUrl']),
	  'description' => htmlspecialchars(str_replace('&', '&amp;', $postArray['post_lead']), ENT_QUOTES),
	  'pubdate' => $postArray['post_published_date_int'],
	  'guid' => str_replace('&', '&amp;', $postArray['itemUrl']),
	);
}

$library_feed->render();
*/