<?php
/**
* Timeline page - displays a list of all publications by month
*
* @copyright	(c) The Xoops Project - www.xoops.org
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @author Xoops Modules Dev Team
* @author		Madfish 28/6/2011
* @since		1.0
* @package		Library
* @version		$Id$
*/

######################################################################
# Original version:
# [11-may-2001] Kenneth Lee - http://www.nexgear.com/
######################################################################

include_once "header.php";
$xoopsOption["template_main"] = "library_timeline.html";
include_once ICMS_ROOT_PATH . "/header.php";

// Set page title
$icmsTpl->assign("library_page_title", _MD_LIBRARY_TIMELINE);

global $icmsConfig;

$library_publication_handler = icms_getModuleHandler('publication', basename(dirname(__FILE__)), 'library');

$lastyear = 0;
$lastmonth = 0;

$months_arr = array(1 => _CO_LIBRARY_CAL_JANUARY, 2 => _CO_LIBRARY_CAL_FEBRUARY, 3 => _CO_LIBRARY_CAL_MARCH,
	4 => _CO_LIBRARY_CAL_APRIL, 5 => _CO_LIBRARY_CAL_MAY, 6 => _CO_LIBRARY_CAL_JUNE, 7 => _CO_LIBRARY_CAL_JULY,
	8 => _CO_LIBRARY_CAL_AUGUST, 9 => _CO_LIBRARY_CAL_SEPTEMBER, 10 => _CO_LIBRARY_CAL_OCTOBER,
	11 => _CO_LIBRARY_CAL_NOVEMBER, 12 => _CO_LIBRARY_CAL_DECEMBER);

$fromyear = (isset($_GET['year'])) ? intval ($_GET['year']): 0;
$frommonth = (isset($_GET['month'])) ? intval($_GET['month']) : 0;

$pgtitle = '';
if ($fromyear && $frommonth) {
	$pgtitle = sprintf(" - %d - %d",$fromyear,$frommonth);
}

$dateformat = icms::$module->config['date_format'];
if ($dateformat == '') {
	$dateformat = 'm';
}

$icmsTpl->assign('xoops_pagetitle', icms_core_DataFilter::htmlSpecialchars(_CO_LIBRARY_TIMELINE) . $pgtitle);

$useroffset = '';
if (is_object(icms::$user)) {
	$timezone = icms::$user->getVar("timezone_offset");
	if (isset($timezone)) {
		$useroffset = icms::$user->getVar("timezone_offset");
	} else {
		$useroffset = $xoopsConfig['default_TZ'];
	}
}

$sql = "SELECT `submission_time` FROM " . $library_publication_handler->table . " WHERE (`submission_time` > '0' AND `submission_time` <= '"
	. time() . "') ORDER BY `submission_time` DESC";

$rows = $library_publication_handler->query($sql, null);

if (!$rows) {
	echo _CO_LIBRARY_NO_TIMELINE;
} else {
	$years = array();
	$months = array();
	$i = 0;
	
	foreach ($rows as $row) {
		$time = $row['submission_time'];
		$time = formatTimestamp($time, 'mysql', $useroffset);
		// Do not insert a line break or you will break the regex!
			if (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $time, $datetime)) {
				$this_year  = intval($datetime[1]);
				$this_month = intval($datetime[2]);
				
			if (empty($lastyear)) {
				$lastyear = $this_year;
			}
			if ($lastmonth == 0) {
				$lastmonth = $this_month;
				$months[$lastmonth]['string'] = $months_arr[$lastmonth];
				$months[$lastmonth]['number'] = $lastmonth;
			}
			if ($lastyear != $this_year) {
				$years[$i]['number'] = $lastyear;
				$years[$i]['months'] = $months;
				$months = array();
				$lastmonth = 0;
				$lastyear = $this_year;
				$i++;
			}
			if ($lastmonth != $this_month) {
				$lastmonth = $this_month;
				$months[$lastmonth]['string'] = $months_arr[$lastmonth];
				$months[$lastmonth]['number'] = $lastmonth;
			}
		}
	}

	$years[$i]['number'] = $this_year;
	$years[$i]['months'] = $months;
	$icmsTpl->assign('years', $years);
}

if ($fromyear != 0 && $frommonth != 0) {
	$icmsTpl->assign('show_publications', TRUE);
	$icmsTpl->assign('lang_publications', _CO_LIBRARY_TIMELINE_PUBLICATIONS);
	$icmsTpl->assign('currentmonth', $months_arr[$frommonth]);
	$icmsTpl->assign('currentyear', $fromyear);
	$icmsTpl->assign('lang_actions', _CO_LIBRARY_TIMELINE_ACTIONS);
	$icmsTpl->assign('lang_date', _CO_LIBRARY_TIMELINE_DATE);
	$icmsTpl->assign('lang_views', _CO_LIBRARY_TIMELINE_VIEWS);

	// Must adjust the selected time to server timestamp
	$timeoffset = $useroffset - $icmsConfig['server_TZ'];
	$monthstart = mktime(0 - $timeoffset, 0, 0, $frommonth, 1, $fromyear);
	$monthend = mktime(23 - $timeoffset, 59, 59, $frommonth + 1, 0, $fromyear);
	$monthend = ($monthend > time()) ? time() : $monthend;

	$count = 0;
	$criteria = new icms_db_criteria_Compo();
	$criteria->add(new icms_db_criteria_Item('submission_time', $monthstart, '>'));
	$criteria->add(new icms_db_criteria_Item('submission_time', $monthend, '<'));
	$criteria->add(new icms_db_criteria_Item('online_status', '1'));
	$criteria->setSort('date');
	$criteria->setOrder('DESC');
	$publicationArray = $library_publication_handler->getObjects($criteria, TRUE);

	$count = count($publicationArray);
	
	if (is_array($publicationArray) && $count > 0) {
		
		// If Sprockets is installed, prepare tag buffers to reduce database lookups
		if (icms_get_module_status("sprockets")) {
			$publication_ids = array_keys($publicationArray);
			$publication_tags_multi_array = array();
			$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
			$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
			
			// Only get tag type tags (not categories)
			$tag_criteria = icms_buildCriteria(array('label_type' => '0'));
			$tag_buffer = $sprockets_tag_handler->getObjects($tag_criteria, TRUE);
			$tag_ids = array_keys($tag_buffer);
			$tag_ids = "('" . implode("','", $tag_ids) . "')";
			
			// Only get taglinks relevant to the publications being listed
			$publication_ids = "('" . implode("','", $publication_ids) . "')";
			$criteria = new icms_db_criteria_Compo();
			$criteria->add(new icms_db_criteria_Item('mid', icms::$module->getVar('mid')));
			$criteria->add(new icms_db_criteria_Item('tid', $tag_ids, 'IN'));
			$criteria->add(new icms_db_criteria_Item('item', 'publication'));
			$criteria->add(new icms_db_criteria_Item('iid', $publication_ids, 'IN'));
			$taglink_buffer = $sprockets_taglink_handler->getObjects($criteria, TRUE, FALSE);
			
			// Prepare a multidimensional array holding the tags for each publication
			foreach ($taglink_buffer as $taglink) {
				if (!array_key_exists($taglink['iid'], $publication_tags_multi_array)) {
					$publication_tags_multi_array[$taglink['iid']] = array();				
				}
				$publication_tags_multi_array[$taglink['iid']][] = '<a href="' . ICMS_URL
					. '/modules/' . basename(dirname(__FILE__)) . '/publication.php?tag_id='
					. $taglink['tid'] . '" title="' . $tag_buffer[$taglink['tid']]->getVar('title') . '">'
					. $tag_buffer[$taglink['tid']]->getVar('title') . '</a>';
			}
		}
		
		foreach ($publicationArray as $publicationObj) {
	    	$htmltitle = '';
			$publication = array();

	    	$publication['title'] = $publicationObj->getItemLinkWithSEOString();
	    	$publication['counter'] = $publicationObj->getVar('counter');
			if (icms_get_module_status("sprockets")) {
				// Use the publication_id to extract the array of tags relevant to this publication
				if (isset($publication_tags_multi_array[$publicationObj->getVar('publication_id')])) {
					$publication['tags'] = implode(', ', $publication_tags_multi_array[$publicationObj->getVar('publication_id')]);
				}
			} else {
				$publication['tags'] = FALSE;
			}
	    	$publication['submission_time'] = formatTimestamp($publicationObj->getVar('submission_time', 'e'),$dateformat, $useroffset);
	
	    	$icmsTpl->append('publications', $publication);
		}
	}
	$icmsTpl->assign('lang_tags', _CO_LIBRARY_TIMELINE_TAGS);
	$icmsTpl->assign('lang_publicationtotal', _CO_LIBRARY_TIMELINE_THEREAREINTOTAL . $count
		. _CO_LIBRARY_TIMELINE_PUBLICATIONS_LOWER);
} else {
    $icmsTpl->assign('show_publications', FALSE);
}

$icmsTpl->assign('lang_libraryarchives', _CO_LIBRARY_TIMELINES);

// Check if the module's breadcrumb should be displayed
if (icms::$module->config['library_show_breadcrumb'] == TRUE) {
	$icmsTpl->assign('library_show_breadcrumb', icms::$module->config['library_show_breadcrumb']);
} else {
	$icmsTpl->assign('library_show_breadcrumb', FALSE);
}

// RSS feed
$icmsTpl->assign('library_rss_link', 'rss.php');
$icmsTpl->assign('library_rss_title', _CO_LIBRARY_SUBSCRIBE_RSS);

// Add RSS auto-discovery link to module header
$rss_link = LIBRARY_URL . 'rss.php';
$rss_attributes = array('type' => 'application/rss+xml', 'title' => $icmsConfig['sitename'] 
	. ' - ' .  _CO_LIBRARY_NEW);
$xoTheme->addLink('alternate', $rss_link, $rss_attributes);

// Generate page metadata (can be customised in module preferences)
global $icmsConfigMetaFooter;
$library_meta_keywords = '';

if (icms::$module->config['library_meta_keywords']) {
	$library_meta_keywords = icms::$module->config['library_meta_keywords'];
} else {
	$library_meta_keywords = $icmsConfigMetaFooter['meta_keywords'];
}
$icms_metagen = new icms_ipf_Metagen(icms::$module->getVar('name'), $library_meta_keywords, 
	_CO_LIBRARY_META_TIMELINE_INDEX_DESCRIPTION);
$icms_metagen->createMetaTags();

$icmsTpl->assign("library_module_home", '<a href="' . ICMS_URL . "/modules/" . icms::$module->getVar("dirname") . '/">' . icms::$module->getVar("name") . "</a>");
$icmsTpl->assign("library_show_breadcrumb", icms::$module->config['library_show_breadcrumb']);
$icmsTpl->assign('library_category_path', _CO_LIBRARY_TIMELINE);

/**
 * Generating meta information for this page
 */
$icms_metagen = new icms_ipf_Metagen(_CO_LIBRARY_TIMELINES, FALSE, _CO_LIBRARY_TIMELINE_DESCRIPTION);
$icms_metagen->createMetaTags();

include_once "footer.php";