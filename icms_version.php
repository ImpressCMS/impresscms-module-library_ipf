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
	"hasSearch"					=> 0,
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
/** To come soon in imBuilding... */

/** Preferences information */
/** To come soon in imBuilding... */

/** Notification information */
/** To come soon in imBuilding... */