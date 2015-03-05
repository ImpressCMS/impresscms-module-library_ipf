<?php
/**
 * English language constants commonly used in the module
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

// Publication
define("_CO_LIBRARY_PUBLICATION_TYPE", "Type");
define("_CO_LIBRARY_PUBLICATION_TYPE_DSC", "Select the type of publication you wish to enter. The page will reload with appropriate data entry fields.");
define("_CO_LIBRARY_PUBLICATION_TITLE", "Title");
define("_CO_LIBRARY_PUBLICATION_TITLE_DSC", "Name of the publication.");
define("_CO_LIBRARY_PUBLICATION_IDENTIFIER", "URL");
define("_CO_LIBRARY_PUBLICATION_IDENTIFIER_DSC", "The link to download the associated file (if any).");
define("_CO_LIBRARY_PUBLICATION_CREATOR", "Creator");
define("_CO_LIBRARY_PUBLICATION_CREATOR_DSC", "Separate multiple authors with a pipe &#039;|&#039; character. Use a convention for consistency, eg. John Smith|Jane Doe.");
define("_CO_LIBRARY_PUBLICATION_TAG", "Tags");
define("_CO_LIBRARY_PUBLICATION_TAG_DSC", "Select the tags (subjects) you wish to label this object with.");
define("_CO_LIBRARY_PUBLICATION_CATEGORY", "Categories");
define("_CO_LIBRARY_PUBLICATION_CATEGORY_DSC", "Select the categories this object belongs to.");
define("_CO_LIBRARY_PUBLICATION_DESCRIPTION", "Description (summary)");
define("_CO_LIBRARY_PUBLICATION_DESCRIPTION_DSC", "A summary description or abstract of the publication. It is displayed when multiple publications are listed on a page and in OAIPMH responses. It is an important field for user-side presentation. Always supply a description if you can.");
define("_CO_LIBRARY_PUBLICATION_EXTENDED_TEXT", "Extended (full) text");
define("_CO_LIBRARY_PUBLICATION_EXTENDED_TEXT_DSC", "Optional. This is an alternate description that is shown in single publication view. If it is left empty, the description field will be used instead. You only need to use this field if you want to have a full description that is too long to view comfortably when multiple publications are listed on a page.");
define("_CO_LIBRARY_PUBLICATION_PUBLISHER", "Publisher");
define("_CO_LIBRARY_PUBLICATION_PUBLISHER_DSC", "The agency responsible for publishing this work.");
define("_CO_LIBRARY_PUBLICATION_FORMAT", "Format");
define("_CO_LIBRARY_PUBLICATION_FORMAT_DSC", "You can add more file formats (mimetypes) to this list by authorising the Library module to use them in System module Mimetype Manager.");
define("_CO_LIBRARY_PUBLICATION_FILE_SIZE", "File size");
define("_CO_LIBRARY_PUBLICATION_FILE_SIZE_DSC", "Enter in BYTES, it will be converted to human readable automatically. This is the size of the file specified in the URL field, if any.");
define("_CO_LIBRARY_PUBLICATION_IMAGE", "Image");
define("_CO_LIBRARY_PUBLICATION_IMAGE_DSC", "Upload &#039;Image&#039; type publications, publication covers and album art here. Maximum image width, height and file size can be adjusted in preferences. Image types are currently restricted to PNG, GIF and JPG.");
define("_CO_LIBRARY_PUBLICATION_DATE", "Date");
define("_CO_LIBRARY_PUBLICATION_DATE_DSC", "Publication date of this work.");
define("_CO_LIBRARY_PUBLICATION_SOURCE", "Source");
define("_CO_LIBRARY_PUBLICATION_SOURCE_DSC", "A collection of which this publication is a part, for example, a scientific journal an article belongs to, an album a soundtrack is included in, or an event at which a presentation was made.");
define("_CO_LIBRARY_PUBLICATION_LANGUAGE", "Language");
define("_CO_LIBRARY_PUBLICATION_LANGUAGE_DSC", "Language of the publication, if any.");
define("_CO_LIBRARY_PUBLICATION_RIGHTS", "Rights");
define("_CO_LIBRARY_PUBLICATION_RIGHTS_DSC", "The license under which this publication is distributed. In most countries, artistic works are copyright (even if you don&#039;t declare it) unless you specify another license.");
define("_CO_LIBRARY_PUBLICATION_COMPACT_VIEW", "Compact view");
define("_CO_LIBRARY_PUBLICATION_COMPACT_VIEW_DSC", "Do you want to display this collection in compact form (a simple list of contents, best for albums and similar where member publications don&#039;t usually carry descriptions) or in expanded view with descriptions and other metadata?");
define("_CO_LIBRARY_PUBLICATION_ONLINE_STATUS", "Online status");
define("_CO_LIBRARY_PUBLICATION_ONLINE", "Online");
define("_CO_LIBRARY_PUBLICATION_OFFLINE", "Offline");
define("_CO_LIBRARY_PUBLICATION_ONLINE_STATUS_DSC", "Toggle this publication online or offline.");
define("_CO_LIBRARY_PUBLICATION_FEDERATED", "Federated");
define("_CO_LIBRARY_PUBLICATION_NOT_FEDERATED", "Not federated");
define("_CO_LIBRARY_PUBLICATION_FEDERATED_DSC", "Syndicate this publication&#039;s metadata with other sites (cross site search) via the Open Archives Initiative Protocol for Metadata Harvesting?");
define("_CO_LIBRARY_PUBLICATION_SUBMISSION_TIME", "Submission time");
define("_CO_LIBRARY_PUBLICATION_SUBMISSION_TIME_DSC", "");
define("_CO_LIBRARY_PUBLICATION_SUBMITTER", "Submitter");
define("_CO_LIBRARY_PUBLICATION_SUBMITTER_DSC", "");
define("_CO_LIBRARY_PUBLICATION_OAI_IDENTIFIER", "OAI Identifier");
define("_CO_LIBRARY_PUBLICATION_OAI_IDENTIFIER_DSC", "Used to uniquely identify this publication across federated sites, and prevents publications being duplicated or imported multiple times. Should never be changed under any circumstance.");
define("_CO_LIBRARY_PUBLICATION_VIEW", "View publication");
define("_CO_LIBRARY_PUBLICATION_SUBCATEGORY_LISTING", "Subcategory listing");
define("_CO_LIBRARY_PUBLICATION_NO_PUBLICATIONS", "Sorry there are no publications to display.");

// Dublin Core Metadata Initiative Type Vocabulary
define("_CO_LIBRARY_TEXT", "Text");
define("_CO_LIBRARY_SOUND", "Sound");
define("_CO_LIBRARY_IMAGE", "Image");
define("_CO_LIBRARY_MOVINGIMAGE", "Video");
define("_CO_LIBRARY_DATASET", "Dataset");
define("_CO_LIBRARY_SOFTWARE", "Software");
define("_CO_LIBRARY_COLLECTION", "Collection");

// Filters
define("_CO_LIBRARY_PUBLICATION_FEDERATION_ENABLED", "Federation enabled");
define("_CO_LIBRARY_PUBLICATION_FEDERATION_DISABLED", "Federation disabled");

// User side presentation aids
define("_CO_LIBRARY_PUBLICATION_AUTHORS", "Author(s):");
define("_CO_LIBRARY_PUBLICATION_PUBLISHED", "Published:");
define("_CO_LIBRARY_PUBLICATION_VIEWS", "views");
define("_CO_LIBRARY_PUBLICATION_DOWNLOAD", "Download");
define("_CO_LIBRARY_PUBLICATION_PERMALINK", "Permalink");
define("_CO_LIBRARY_STREAMING", "Streaming");
define("_CO_LIBRARY_RELATED_WORKS", "Related works");

// RSS feeds
define("_CO_LIBRARY_NEW", "Recent publications");
define("_CO_LIBRARY_NEW_DSC", "The latest publications from ");
define("_CO_LIBRARY_SUBSCRIBE_RSS", "Subscribe to RSS feed");
define("_CO_LIBRARY_SUBSCRIBE_RSS_ON", "Subscribe to our RSS feed on ");
define("_CO_LIBRARY_ALL", "All publications");

// Tags
define("_CO_LIBRARY_PUBLICATION_ALL_TAGS", "-- All publications --");

// Open Archives Initiative Protocol for Metadata Harvesting
define("_CO_LIBRARY_ARCHIVE_MUST_CREATE", "Error: An archive object must be created before OAIPMH
    requests can be handled. Please create one via the Open Archive tab in Sprockets administration.");
define("_CO_LIBRARY_NO_ARCHIVE", "Sorry there are no articles to display yet.");
define("_CO_LIBRARY_META_ARCHIVE_INDEX_DESCRIPTION", "Open Archives Initiative repository information");

// Timeline page
define("_CO_LIBRARY_TIMELINE", "Publication timeline");
define("_CO_LIBRARY_TIMELINE_DESCRIPTION", "Archived publications sorted by month.");
define("_CO_LIBRARY_TIMELINES", "Publication timeline");
define("_CO_LIBRARY_NO_TIMELINE", "Sorry there are no publications to display yet.");
define("_CO_LIBRARY_TIMELINE_PUBLICATIONS", "Publications");
define("_CO_LIBRARY_TIMELINE_ACTIONS", "Actions");
define("_CO_LIBRARY_TIMELINE_DATE", "Date");
define("_CO_LIBRARY_TIMELINE_VIEWS", "Views");
define("_CO_LIBRARY_TIMELINE_TAGS", "Tags");
define("_CO_LIBRARY_TIMELINE_TAGS_DESCRIPTION", "View publications sorted by tag.");
define("_CO_LIBRARY_TIMELINE_THEREAREINTOTAL", "There are a total of ");
define("_CO_LIBRARY_TIMELINE_PUBLICATIONS_LOWER", " publications:");
define("_CO_LIBRARY_CAL_JANUARY", "January");
define("_CO_LIBRARY_CAL_FEBRUARY", "February");
define("_CO_LIBRARY_CAL_MARCH", "March");
define("_CO_LIBRARY_CAL_APRIL", "April");
define("_CO_LIBRARY_CAL_MAY", "May");
define("_CO_LIBRARY_CAL_JUNE", "June");
define("_CO_LIBRARY_CAL_JULY", "July");
define("_CO_LIBRARY_CAL_AUGUST", "August");
define("_CO_LIBRARY_CAL_SEPTEMBER", "September");
define("_CO_LIBRARY_CAL_OCTOBER", "October");
define("_CO_LIBRARY_CAL_NOVEMBER", "November");
define("_CO_LIBRARY_CAL_DECEMBER", "December");
define("_CO_LIBRARY_META_TIMELINE_INDEX_DESCRIPTION", "Index of publications sorted by date");

// Download page
define("_CO_LIBRARY_PUBLICATION_UNAVAILABLE", "Sorry, no such publication available");

// Tag index page
define("_CO_LIBRARY_TAG_INDEX", "Publications: Tags");
define("_CO_LIBRARY_META_TAG_INDEX_DESCRIPTION", "Index of publications sorted by tag");
define("_CO_LIBRARY_CATEGORY_INDEX", "Publications: Categories");

// Notification mail template
define("_CO_LIBRARY_PUBLICATION_UPDATE_YOUR_SUBSCRIPTIONS", "update your subscriptions");
define("_CO_LIBRARY_PUBLICATION_UNTAGGED", "Untagged");

