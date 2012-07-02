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

// publication
define("_CO_LIBRARY_PUBLICATION_TYPE", "Type");
define("_CO_LIBRARY_PUBLICATION_TYPE_DSC", "Select the type of publication you wish to enter. The page will reload with appropriate data entry fields.");
define("_CO_LIBRARY_PUBLICATION_TITLE", "Title");
define("_CO_LIBRARY_PUBLICATION_TITLE_DSC", "Name of the publication.");
define("_CO_LIBRARY_PUBLICATION_IDENTIFIER", "URL");
define("_CO_LIBRARY_PUBLICATION_IDENTIFIER_DSC", "The link to download the associated file (if any).");
define("_CO_LIBRARY_PUBLICATION_CREATOR", "Creator");
define("_CO_LIBRARY_PUBLICATION_CREATOR_DSC", "Separate multiple authors with a pipe &#039;|&#039; character. Use a convention for consistency, eg. John Smith|Jane Doe.");
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
define("_CO_LIBRARY_PUBLICATION_COVER", "Image");
define("_CO_LIBRARY_PUBLICATION_COVER_DSC", "Upload &#039;Image&#039; type publications, publication covers and album art here. Maximum image width, height and file size can be adjusted in preferences. Image types are currently restricted to PNG, GIF and JPG.");
define("_CO_LIBRARY_PUBLICATION_DATE", "Date");
define("_CO_LIBRARY_PUBLICATION_DATE_DSC", "Publication date of this work.");
define("_CO_LIBRARY_PUBLICATION_SOURCE", "Collection");
define("_CO_LIBRARY_PUBLICATION_SOURCE_DSC", "A collection of which this publication is a part, for example, a scientific journal an article belongs to, an album a soundtrack is included in, or an event at which a presentation was made.");
define("_CO_LIBRARY_PUBLICATION_LANGUAGE", "Language");
define("_CO_LIBRARY_PUBLICATION_LANGUAGE_DSC", "Language of the publication, if any.");
define("_CO_LIBRARY_PUBLICATION_RIGHTS", "Rights");
define("_CO_LIBRARY_PUBLICATION_RIGHTS_DSC", "The license under which this publication is distributed. In most countries, artistic works are copyright (even if you don&#039;t declare it) unless you specify another license.");
define("_CO_LIBRARY_PUBLICATION_COMPACT_VIEW", "Compact view");
define("_CO_LIBRARY_PUBLICATION_COMPACT_VIEW_DSC", "Do you want to display this collection in compact form (a simple list of contents, best for albums and similar where member publications don&#039;t usually carry descriptions) or in expanded view with descriptions and other metadata?");
define("_CO_LIBRARY_PUBLICATION_ONLINE_STATUS", "Online status");
define("_CO_LIBRARY_PUBLICATION_ONLINE_STATUS_DSC", "Toggle this publication online or offline.");
define("_CO_LIBRARY_PUBLICATION_FEDERATED", "Federated");
define("_CO_LIBRARY_PUBLICATION_FEDERATED_DSC", "Syndicate this publication&#039;s metadata with other sites (cross site search) via the Open Archives Initiative Protocol for Metadata Harvesting?");
define("_CO_LIBRARY_PUBLICATION_SUBMISSION_TIME", "Submission time");
define("_CO_LIBRARY_PUBLICATION_SUBMISSION_TIME_DSC", "");
define("_CO_LIBRARY_PUBLICATION_SUBMITTER", "Submitter");
define("_CO_LIBRARY_PUBLICATION_SUBMITTER_DSC", "");
define("_CO_LIBRARY_PUBLICATION_OAI_IDENTIFIER", "OAI Identifier");
define("_CO_LIBRARY_PUBLICATION_OAI_IDENTIFIER_DSC", "Used to uniquely identify this publication across federated sites, and prevents publications being duplicated or imported multiple times. Should never be changed under any circumstance.");

// Dublic Core Metadata Initiative Type Vocabulary
define("_CO_LIBRARY_TEXT", "Text");
define("_CO_LIBRARY_SOUND", "Sound");
define("_CO_LIBRARY_IMAGE", "Image");
define("_CO_LIBRARY_MOVINGIMAGE", "Video");
define("_CO_LIBRARY_DATASET", "Dataset");
define("_CO_LIBRARY_SOFTWARE", "Software");
define("_CO_LIBRARY_COLLECTION", "Collection");