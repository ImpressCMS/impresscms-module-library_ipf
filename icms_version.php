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

/**  General Information  */
$modversion = array(
	"name"						=> _MI_LIBRARY_MD_NAME,
	"version"					=> 1.0,
	"description"				=> _MI_LIBRARY_MD_DESC,
	"author"					=> "Madfish (Simon Wilkinson)",
	"credits"					=> "",
	"help"						=> "",
	"license"					=> "GNU General Public License (GPL)",
	"official"					=> 0,
	"dirname"					=> basename(dirname(__FILE__)),
	"modname"					=> "library",

/**  Images information  */
	"iconsmall"					=> "images/icon_small.png",
	"iconbig"					=> "images/icon_big.png",
	"image"						=> "images/icon_big.png", /* for backward compatibility */

/**  Development information */
	"status_version"			=> "1.0",
	"status"					=> "Beta",
	"date"						=> "Unreleased",
	"author_word"				=> "",
	"warning"					=> _CO_ICMS_WARNING_BETA,

/** Contributors */
	"developer_website_url"		=> "https://www.isengard.biz",
	"developer_website_name"	=> "Isengard.biz",
	"developer_email"			=> "simon@isengard.biz",

/** Administrative information */
	"hasAdmin"					=> 1,
	"adminindex"				=> "admin/index.php",
	"adminmenu"					=> "admin/menu.php",

/** Install and update informations */
	"onInstall"					=> "include/onupdate.inc.php",
	"onUpdate"					=> "include/onupdate.inc.php",

/** Search information */
	"hasSearch"					=> 1,
	"search"					=> array("file" => "include/search.inc.php", "func" => "library_search"),

/** Menu information */
	"hasMain"					=> 1,

/** Comments information */
	"hasComments"				=> 1,
	"comments"					=> array(
									"itemName" => "post_id",
									"pageName" => "post.php",
									"callbackFile" => "include/comment.inc.php",
									"callback" => array("approve" => "library_com_approve",
														"update" => "library_com_update")));

/** other possible types: testers, translators, documenters and other */
$modversion['people']['developers'][] = "Madfish (Simon Wilkinson)";

/** Manual */
$modversion['manual']['wiki'][] = "<a href='http://wiki.impresscms.org/index.php?title=Library' target='_blank'>English</a>";

/** Database information */
$modversion['object_items'][1] = 'publication';

$modversion["tables"] = icms_getTablesArray($modversion['dirname'], $modversion['object_items']);

/** Templates information */
$modversion['templates'] = array(
	array("file" => "library_admin_publication.html", "description" => "publication Admin Index"),
	array("file" => "library_publication.html", "description" => "publication Index"),

	array('file' => 'library_header.html', 'description' => 'Module Header'),
	array('file' => 'library_footer.html', 'description' => 'Module Footer'));

/** Blocks information */

// Displays recent publications

$modversion['blocks'][1] = array(
	'file' => 'library_recent.php',
	'name' => _MI_LIBRARY_RECENT,
	'description' => _MI_LIBRARY_RECENTDSC,
	'show_func' => 'library_recent_show',
	'edit_func' => 'library_recent_edit',
	'options' => 'All|5',
	'template' => 'library_block_recent.html'
);

/** Preferences information */

// Prepare start page options
$start_options = array(0 => 'publication.php', 1 => 'collection.php');
$start_options = array_flip($start_options);

// default start page for the module

$modversion['config'][3] = array(
	'name' => 'library_start_page',
	'title' => '_MI_LIBRARY_START_PAGE',
	'description' => '_MI_LIBRARY_START_PAGE_DSC',
	'formtype' => 'select',
	'valuetype' => 'text',
	'options' => $start_options,
	'default' =>  '0');

$modversion['config'][] = array(
	'name' => 'library_enable_archive',
	'title' => '_MI_LIBRARY_ENABLE_ARCHIVE',
	'description' => '_MI_LIBRARY_ENABLE_ARCHIVE_DSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'library_default_federation',
	'title' => '_MI_LIBRARY_FEDERATE',
	'description' => '_MI_LIBRARY_FEDERATE_DSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'new_publication_view_mode',
	'title' => '_MI_LIBRARY_NEW_PUBLICATION_VIEW_MODE',
	'description' => '_MI_LIBRARY_NEW_PUBLICATION_VIEW_MODEDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '0');

$modversion['config'][] = array(
	'name' => 'number_publications_per_page',
	'title' => '_MI_LIBRARY_NUMBER_PUBLICATIONS',
	'description' => '_MI_LIBRARY_NUMBER_PUBLICATIONSSDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '10');

$modversion['config'][] = array(
	'name' => 'collection_view_mode',
	'title' => '_MI_LIBRARY_COLLECTION_VIEW_MODE',
	'description' => '_MI_LIBRARY_COLLECTION_VIEW_MODEDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '0');

$modversion['config'][] = array(
	'name' => 'number_collections_per_page',
	'title' => '_MI_LIBRARY_NUMBER_COLLECTIONS',
	'description' => '_MI_LIBRARY_NUMBER_COLLECTIONSDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '10');

$modversion['config'][] = array(
	'name' => 'new_view_mode',
	'title' => '_MI_LIBRARY_NEW_VIEW_MODE',
	'description' => '_MI_LIBRARY_NEW_VIEW_MODEDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '0');

$modversion['config'][] = array(
	'name' => 'number_publications_in_rss',
	'title' => '_MI_LIBRARY_NUMBER_IN_RSS',
	'description' => '_MI_LIBRARY_NUMBER_IN_RSSDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '10');

// prepare language options
$language_options = include ICMS_ROOT_PATH . '/modules/' . basename(dirname(__FILE__))
	. '/include/language.inc.php';
// the preference system displays keys rather than values for some reason, so lets flip it
$language_options = array_flip($language_options);

$modversion['config'][] = array(
	'name' => 'default_language',
	'title' => '_MI_LIBRARY_DEFAULT_LANGUAGE',
	'description' => '_MI_LIBRARY_DEFAULT_LANGUAGE_DSC',
	'formtype' => 'select',
	'valuetype' => 'text',
	'options' => $language_options,
	'default' =>  'en');

$modversion['config'][] = array(
	'name' => 'thumbnail_width',
	'title' => '_MI_LIBRARY_THUMBNAIL_WIDTH',
	'description' => '_MI_LIBRARY_THUMBNAIL_WIDTHDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '50');

$modversion['config'][] = array(
	'name' => 'thumbnail_height',
	'title' => '_MI_LIBRARY_THUMBNAIL_HEIGHT',
	'description' => '_MI_LIBRARY_THUMBNAIL_HEIGHTDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '50');

$modversion['config'][] = array(
	'name' => 'screenshot_width',
	'title' => '_MI_LIBRARY_SCREENSHOT_WIDTH',
	'description' => '_MI_LIBRARY_SCREENSHOT_WIDTHDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '150');

$modversion['config'][] = array(
	'name' => 'screenshot_height',
	'title' => '_MI_LIBRARY_SCREENSHOT_HEIGHT',
	'description' => '_MI_LIBRARY_SCREENSHOT_HEIGHTDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '150');

$modversion['config'][] = array(
	'name' => 'image_width',
	'title' => '_MI_LIBRARY_IMAGE_WIDTH',
	'description' => '_MI_LIBRARY_IMAGE_WIDTHDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '400');

$modversion['config'][] = array(
	'name' => 'image_height',
	'title' => '_MI_LIBRARY_IMAGE_HEIGHT',
	'description' => '_MI_LIBRARY_IMAGE_HEIGHTDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '400');

$modversion['config'][] = array(
	'name' => 'image_upload_height',
	'title' => '_MI_LIBRARY_IMAGE_UPLOAD_HEIGHT',
	'description' => '_MI_LIBRARY_IMAGE_UPLOAD_HEIGHTDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '4000');

$modversion['config'][] = array(
	'name' => 'image_upload_width',
	'title' => '_MI_LIBRARY_IMAGE_UPLOAD_WIDTH',
	'description' => '_MI_LIBRARY_IMAGE_UPLOAD_WIDTHDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '4000');

$modversion['config'][] = array(
	'name' => 'image_file_size',
	'title' => '_MI_LIBRARY_IMAGE_FILE_SIZE',
	'description' => '_MI_LIBRARY_IMAGE_FILE_SIZEDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '8388608'); // 8MB max upload size

//// Template switches - show or hide particular fields ////

// Collections

$modversion['config'][] = array(
	'name' => 'display_released_field',
	'title' => '_MI_LIBRARY_DISPLAY_RELEASED',
	'description' => '_MI_LIBRARY_DISPLAY_RELEASEDDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_collection_publisher_field',
	'title' => '_MI_LIBRARY_DISPLAY_COLLECTION_PUBLISHER',
	'description' => '_MI_LIBRARY_DISPLAY_COLLECTION_PUBLISHERDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_trackcount_field',
	'title' => '_MI_LIBRARY_DISPLAY_TRACKCOUNT',
	'description' => '_MI_LIBRARY_DISPLAY_TRACKCOUNTDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

// affects both collection and publication objects

$modversion['config'][] = array(
	'name' => 'display_counter_field',
	'title' => '_MI_LIBRARY_DISPLAY_COUNTER',
	'description' => '_MI_LIBRARY_DISPLAY_COUNTERDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_download_button',
	'title' => '_MI_LIBRARY_DISPLAY_DOWNLOAD_BUTTON',
	'description' => '_MI_LIBRARY_DISPLAY_DOWNLOAD_BUTTONDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_streaming_button',
	'title' => '_MI_LIBRARY_DISPLAY_STREAMING_BUTTON',
	'description' => '_MI_LIBRARY_DISPLAY_STREAMING_BUTTONDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

// Publications

$modversion['config'][] = array(
	'name' => 'display_creator_field',
	'title' => '_MI_LIBRARY_DISPLAY_CREATOR',
	'description' => '_MI_LIBRARY_DISPLAY_CREATORDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_date_field',
	'title' => '_MI_LIBRARY_DISPLAY_DATE',
	'description' => '_MI_LIBRARY_DISPLAY_DATEDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_format_field',
	'title' => '_MI_LIBRARY_DISPLAY_FORMAT',
	'description' => '_MI_LIBRARY_DISPLAY_FORMATDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_publication_publisher_field',
	'title' => '_MI_LIBRARY_DISPLAY_PUBLICATION_PUBLISHER',
	'description' => '_MI_LIBRARY_DISPLAY_PUBLICATION_PUBLISHERDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_publication_source_field',
	'title' => '_MI_LIBRARY_DISPLAY_PUBLICATION_SOURCE',
	'description' => '_MI_LIBRARY_DISPLAY_PUBLICATION_SOURCEDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_language_field',
	'title' => '_MI_LIBRARY_DISPLAY_LANGUAGE',
	'description' => '_MI_LIBRARY_DISPLAY_LANGUAGEDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_rights_field',
	'title' => '_MI_LIBRARY_DISPLAY_RIGHTS',
	'description' => '_MI_LIBRARY_DISPLAY_RIGHTSDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

/** Notification information */
/** To come soon in imBuilding... */