<?php
/**
* Generating an RSS feed
*
* @copyright	Copyright Madfish (Simon Wilkinson) 2011
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @since		1.0
* @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
* @package		Library
* @version		$Id$
*/

/** Include the module's header for all pages */
include_once 'header.php';
include_once ICMS_ROOT_PATH.'/header.php';

/**
 * Encodes entities to ensure feed content complies with the RSS specification
 *
 * @param string $field
 * @return string
 */
function encode_entities($field) {
	$field = htmlspecialchars(html_entity_decode($field, ENT_QUOTES, 'UTF-8'),
		ENT_NOQUOTES, 'UTF-8');
	return $field;
}

$libraryModule = icms_getModuleInfo(basename(dirname(__FILE__)));
$clean_tag_id = $sort_order = '';

$clean_tag_id = isset($_GET['tag_id']) ? intval($_GET['tag_id']) : FALSE;

include_once ICMS_ROOT_PATH . '/modules/' . basename(dirname(__FILE__))
	. '/class/IcmsFeed.php';

$news_feed = new IcmsFeed();
$library_publication_handler = icms_getModuleHandler('publication', basename(dirname(__FILE__)), basename(dirname(__FILE__)));

$sprocketsModule = icms_getModuleInfo('sprockets');
if (icms_get_module_status("sprockets")) {
	$sprockets_taglink_handler = icms_getModuleHandler('taglink',
			$sprocketsModule->getVar('dirname'), 'sprockets');
	$sprockets_tag_handler = icms_getModuleHandler('tag',
			$sprocketsModule->getVar('dirname'), 'sprockets');
}

// Generates a feed of recent publications across all tags
if (empty($clean_tag_id) || !icms_get_module_status("sprockets")) {
	$feed_title = _CO_LIBRARY_NEW;
	$site_name = encode_entities($icmsConfig['sitename']);
	$tag_title = _CO_LIBRARY_ALL;

	$news_feed->title = $site_name . ' - ' . $feed_title;
	$news_feed->url = LIBRARY_URL . 'publication.php';
	$news_feed->description = _CO_LIBRARY_NEW_DSC . $site_name . '.';
	$news_feed->language = _LANGCODE;
	$news_feed->charset = _CHARSET;
	$news_feed->category = $libraryModule->getVar('name');

	$url = ICMS_URL . '/images/logo.gif';
	$news_feed->image = array('title' => $news_feed->title, 'url' => $url,
			'link' => $news_feed->url);
	$news_feed->width = 144;
	$news_feed->atom_link = '"' . LIBRARY_URL . 'rss.php"';

	$criteria = new icms_db_criteria_Compo();
	$criteria->add(new icms_db_criteria_Item('online_status', TRUE));
	$criteria->add(new icms_db_criteria_Item('date', time(), '<'));
	$criteria->setStart(0);
	$criteria->setLimit($libraryModule->config['number_rss_items']);

	$criteria->setSort('date');
	$criteria->setOrder('DESC');

	$publicationArray = $library_publication_handler->getObjects($criteria);
	
} else { // Generates tag-specific feeds
	
	// Need to remove html tags and problematic characters to meet RSS spec
	$tagObj = $sprockets_tag_handler->get($clean_tag_id);
	$site_name = encode_entities($icmsConfig['sitename']);
	$tag_title = encode_entities($tagObj->getVar('title'));
	$tag_description = strip_tags($tagObj->getVar('description'));
	$tag_description = encode_entities($tag_description);

	$news_feed->title = $site_name . ' - ' . $tag_title;
	$news_feed->url = LIBRARY_URL . 'publication.php?tag_id=' . $tagObj->getVar('tag_id');
	$news_feed->description = $tag_description;
	$news_feed->language = _LANGCODE;
	$news_feed->charset = _CHARSET;
	$news_feed->category = $libraryModule->getVar('name');

	// If there's a tag icon, use it as the feed image
	if ($tagObj->getVar('icon', 'e')) {
		$url = $tagObj->getImageDir() . $tagObj->getVar('icon', 'e');
	} else {
		$url = ICMS_URL . 'images/logo.gif';
	}
	$news_feed->image = array('title' => $news_feed->title, 'url' => $url,
			'link' => LIBRARY_URL . 'rss.php?tag_id='
			. $tagObj->getVar('tag_id'));
	$news_feed->width = 144;
	$news_feed->atom_link = '"' . LIBRARY_URL . 'rss.php?tag_id=' . $tagObj->getVar('tag_id') . '"';
	
	// Retrieve publications relevant to this tag using a JOIN to the taglinks table
	// Might be a good idea to move this to the class

	$query = $rows = $tag_publication_count = '';

	$query = "SELECT * FROM " . $library_publication_handler->table . ", "
			. $sprockets_taglink_handler->table
			. " WHERE `publication_id` = `iid`"
			. " AND `online_status` = '1'"
			. " AND `date` < '" . time() . "'"
			. " AND `tid` = '" . $clean_tag_id . "'"
			. " AND `mid` = '" . $libraryModule->getVar('mid') . "'"
			. " AND `item` = 'publication'"
			. " ORDER BY `date` DESC"
			. " LIMIT " . $libraryModule->config['number_rss_items'];

	$result = icms::$xoopsDB->query($query);

	if (!$result) {
		echo 'Error';
		exit;
	} else {
		$rows = $library_publication_handler->convertResultSet($result);
		foreach ($rows as $key => $row) {
			$publicationArray[$row->getVar('publication_id')] = $row;
		}
	}
}

// Prepare an array of publications
foreach($publicationArray as $publication) {
	$flattened_publication = $publication->toArray();

	// check if creator or submitter should be designated as author
	if ($libraryModule->config['display_creator'] == FALSE) {
		$creator = $site_name;
	} else {
		$creator = $publication->getVar('creator', 'e');
		$creator = explode('|', $creator);
		foreach ($creator as &$individual) {
			$individual = encode_entities($individual);
		}
	}

	// Check if there is an extended text
	$description = encode_entities($flattened_publication['description']);
	$title = encode_entities($flattened_publication['title']);
	$link = encode_entities($flattened_publication['itemUrl']);

	$news_feed->feeds[] = array (
		'title' => $title,
		'link' => $link,
		'description' => $description,
		'author' => $creator,
		// pubdate must be a RFC822-date-time EXCEPT with 4-digit year or the feed won't validate
		'pubdate' => date(DATE_RSS, $publication->getVar('date', 'e')),
		'guid' => $link,
		'category' => $tag_title
		);
}

$news_feed->render();