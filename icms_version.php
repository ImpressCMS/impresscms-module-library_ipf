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

/** Comments information */
	"hasComments"				=> 1,
	"comments"					=> array(
									"itemName" => "publication_id",
									"pageName" => "publication.php",
									"callbackFile" => "include/comment.inc.php",
									"callback" => array("approve" => "library_com_approve",
														"update" => "library_com_update")));

/** Menu information */
$modversion['hasMain'] = 1;
$sprocketsModule = icms_getModuleInfo('sprockets');
if (icms_get_module_status("sprockets")) {
	$i = 0;
	$modversion['sub'][$i]['name'] = _MI_LIBRARY_TAG_DIRECTORY;
	$modversion['sub'][$i]['url'] = "tag.php";
	$i++;
	$modversion['sub'][$i]['name'] = _MI_LIBRARY_CATEGORY_DIRECTORY;
	$modversion['sub'][$i]['url'] = "tag.php?label_type=1";
	$i++;
	$modversion['sub'][$i]['name'] = _MI_LIBRARY_TIMELINE_DIRECTORY;
	$modversion['sub'][$i]['url'] = "timeline.php";
	$i++;
	$modversion['sub'][$i]['name'] = _MI_LIBRARY_OPEN_ARCHIVES_INITIATIVE;
	$modversion['sub'][$i]['url'] = "open-archives-initiative.php";
	unset($i);
}

/** Other possible types: testers, translators, documenters and other */
$modversion['people']['developers'][] = "Madfish (Simon Wilkinson)";

/** Manual */
$modversion['manual']['wiki'][] = "<a href='http://wiki.impresscms.org/index.php?title=Library' target='_blank'>English</a>";

/** Database information */
$modversion['object_items'][1] = 'publication';

$modversion["tables"] = icms_getTablesArray($modversion['dirname'], $modversion['object_items']);

/** Templates information - subtemplates that will be included must be declared before the calling file */
$modversion['templates'] = array(
	array("file" => "library_admin_publication.html", "description" => "Publication admin index"),
	array("file" => "library_publication_text.html", "description" => "Subtemplate for text publications"),
	array("file" => "library_publication_sound.html", "description" => "Subtemplate for sound publications"),
	array("file" => "library_publication_image.html", "description" => "Subtemplate for image publications"),
	array("file" => "library_publication_moving_image.html", "description" => "Subtemplate for video publications"),
	array("file" => "library_publication_collection.html", "description" => "Subtemplate for collection publications"),
	array("file" => "library_publication.html", "description" => "Publication container template"),
	array("file" => "library_timeline.html", "description" => "Publication timeline page"),
	array("file" => "library_tag.html", "description" => "Tag index page"),
	array("file" => "library_open_archive.html", "description" => "Open Archives Initiative page"),
	array("file" => "library_rss.html", "description" => "Generates RSS feeds"),
	array('file' => 'library_header.html', 'description' => 'Module header'),
	array('file' => 'library_footer.html', 'description' => 'Module footer'),
	array('file' => 'library_requirements.html', 'description' => 'Module requirements information'));

/** Blocks information */

// Displays recent publications

$modversion['blocks'][1] = array(
	'file' => 'library_recent.php',
	'name' => _MI_LIBRARY_RECENT,
	'description' => _MI_LIBRARY_RECENTDSC,
	'show_func' => 'show_recent_publications',
	'edit_func' => 'edit_recent_publications',
	'options' => '5|0',
	'template' => 'library_block_recent.html'
);

/** Preferences information */

// Prepare start page options
$start_options = array(0 => 'publication.php', 1 => 'tag.php', 2 => 'category.php', 3 => 'timeline.php');
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
	'name' => 'library_publication_index_display_mode',
	'title' => '_MI_LIBRARY_PUBLICATION_INDEX_DISPLAY_MODE',
	'description' => '_MI_LIBRARY_PUBLICATION_INDEX_DISPLAY_MODEDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'library_show_breadcrumb',
	'title' => '_MI_LIBRARY_SHOW_BREADCRUMB',
	'description' => '_MI_LIBRARY_SHOW_BREADCRUMB_DSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'library_show_tag_select_box',
	'title' => '_MI_LIBRARY_SHOW_TAG_SELECT_BOX',
	'description' => '_MI_LIBRARY_SHOW_TAG_SELECT_BOX_DSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

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
	'name' => 'number_publications_per_page',
	'title' => '_MI_LIBRARY_NUMBER_PUBLICATIONS',
	'description' => '_MI_LIBRARY_NUMBER_PUBLICATIONSSDSC',
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
	'name' => 'number_rss_items',
	'title' => '_MI_LIBRARY_NUMBER_RSS_ITEMS',
	'description' => '_MI_LIBRARY_NUMBER_RSS_ITEMSDSC',
	'formtype' => 'textbox',
	'valuetype' => 'int',
	'default' =>  '10');

// prepare language options
include ICMS_ROOT_PATH . '/modules/' . basename(dirname(__FILE__))
	. '/include/language.inc.php';
// The preference system displays keys rather than values for some reason, so lets flip it
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

// Affects both collection and publication objects

$modversion['config'][] = array(
	'name' => 'display_counter_field',
	'title' => '_MI_LIBRARY_DISPLAY_COUNTER',
	'description' => '_MI_LIBRARY_DISPLAY_COUNTERDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

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
	'name' => 'date_format',
	'title' => '_MI_LIBRARY_DATE_FORMAT',
	'description' => '_MI_LIBRARY_DATE_FORMAT_DSC',
	'formtype' => 'textbox',
	'valuetype' => 'string',
	'default' => 'j/n/Y');

$modversion['config'][] = array(
	'name' => 'display_file_size_field',
	'title' => '_MI_LIBRARY_DISPLAY_FILE_SIZE',
	'description' => '_MI_LIBRARY_DISPLAY_FILE_SIZEDSC',
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
	'name' => 'display_publisher_field',
	'title' => '_MI_LIBRARY_DISPLAY_PUBLISHER',
	'description' => '_MI_LIBRARY_DISPLAY_PUBLISHERDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'display_source_field',
	'title' => '_MI_LIBRARY_DISPLAY_SOURCE',
	'description' => '_MI_LIBRARY_DISPLAY_SOURCEDSC',
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

$modversion['config'][] = array(
	'name' => 'display_submitter_field',
	'title' => '_MI_LIBRARY_DISPLAY_SUBMITTER',
	'description' => '_MI_LIBRARY_DISPLAY_SUBMITTERDSC',
	'formtype' => 'yesno',
	'valuetype' => 'int',
	'default' =>  '1');

$modversion['config'][] = array(
	'name' => 'library_meta_description',
	'title' => '_MI_LIBRARY_META_DESCRIPTION',
	'description' => '_MI_LIBRARY_META_DESCRIPTIONDSC',
	'formtype' => 'textbox',
	'valuetype' => 'string',
	'default' =>  '');

$modversion['config'][] = array(
	'name' => 'library_meta_keywords',
	'title' => '_MI_LIBRARY_META_KEYWORDS',
	'description' => '_MI_LIBRARY_META_KEYWORDSDSC',
	'formtype' => 'textbox',
	'valuetype' => 'string',
	'default' =>  '');

/** Notification information */
$modversion['hasNotification'] = 1;

$modversion['notification'] = array (
	'lookup_file' => 'include/notification.inc.php',
	'lookup_func' => 'library_notify_iteminfo');

// Notification categories
$modversion['notification']['category'][1] = array (
	'name' => 'global',
	'title' => _MI_LIBRARY_GLOBAL_NOTIFY,
	'description' => _MI_LIBRARY_GLOBAL_NOTIFY_DSC,
	'subscribe_from' => array('publication.php'),
	'item_name' => '');

// Notification events: Global
$modversion['notification']['event'][1] = array(
	'name' => 'publication_published',
	'category'=> 'global',
	'title'=> _MI_LIBRARY_GLOBAL_PUBLICATION_PUBLISHED_NOTIFY,
	'caption'=> _MI_LIBRARY_GLOBAL_PUBLICATION_PUBLISHED_NOTIFY_CAP,
	'description'=> _MI_LIBRARY_GLOBAL_PUBLICATION_PUBLISHED_NOTIFY_DSC,
	'mail_template'=> 'global_publication_published',
	'mail_subject'=> _MI_LIBRARY_GLOBAL_PUBLICATION_PUBLISHED_NOTIFY_SBJ);