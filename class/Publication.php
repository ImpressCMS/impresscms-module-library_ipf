<?php
/**
 * Class representing Library publication objects
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

class mod_library_Publication extends icms_ipf_seo_Object {
	/**
	 * Constructor
	 *
	 * @param mod_library_Publication $handler Object handler
	 */
	public function __construct(&$handler)
	{
		icms_ipf_object::__construct($handler);
		
		$libraryModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));

		$this->quickInitVar("publication_id", XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar("type", XOBJ_DTYPE_TXTBOX, TRUE);
		$this->quickInitVar("title", XOBJ_DTYPE_TXTBOX, TRUE);
		$this->quickInitVar("identifier", XOBJ_DTYPE_TXTBOX, FALSE);
		$this->quickInitVar("creator", XOBJ_DTYPE_TXTBOX, FALSE);
		$this->initNonPersistableVar('tag', XOBJ_DTYPE_INT, 'tag', FALSE, FALSE, FALSE, TRUE);
		$this->initNonPersistableVar('category', XOBJ_DTYPE_INT, 'category', FALSE, FALSE, FALSE,
				TRUE);
		$this->quickInitVar("description", XOBJ_DTYPE_TXTAREA, FALSE);
		$this->quickInitVar("extended_text", XOBJ_DTYPE_TXTAREA, FALSE);
		$this->quickInitVar("format", XOBJ_DTYPE_TXTBOX, TRUE);
		$this->quickInitVar("file_size", XOBJ_DTYPE_INT, FALSE);
		$this->quickInitVar("image", XOBJ_DTYPE_IMAGE, FALSE);
		$this->quickInitVar("date", XOBJ_DTYPE_STIME, FALSE);
		$this->quickInitVar("source", XOBJ_DTYPE_TXTBOX, FALSE);
		$this->quickInitVar("language", XOBJ_DTYPE_TXTBOX, FALSE, FALSE, FALSE,
				$libraryModule->config['default_language']);
		$this->quickInitVar("rights", XOBJ_DTYPE_TXTBOX, TRUE);
		$this->quickInitVar("publisher", XOBJ_DTYPE_TXTBOX, FALSE);
		$this->quickInitVar("compact_view", XOBJ_DTYPE_INT, FALSE, FALSE, FALSE, 0);
		$this->quickInitVar("online_status", XOBJ_DTYPE_INT, TRUE, FALSE, FALSE, 1);		
		$this->quickInitVar("federated", XOBJ_DTYPE_INT, TRUE, FALSE, FALSE,
				$libraryModule->config['library_default_federation']);
		$this->quickInitVar("submission_time", XOBJ_DTYPE_LTIME, TRUE);
		$this->quickInitVar("submitter", XOBJ_DTYPE_INT, TRUE);
		$this->quickInitVar("oai_identifier", XOBJ_DTYPE_TXTBOX, TRUE, FALSE, FALSE,
				$this->handler->setOaiId());
		$this->quickInitVar('notification_sent', XOBJ_DTYPE_INT, TRUE, FALSE, FALSE, 0);
		$this->initCommonVar("counter");
		$this->initCommonVar("dohtml");
		$this->initCommonVar("dobr");

		$this->initiateSEO();
		
		// Enable HTML editors
		$this->setControl('description', 'dhtmltextarea');
		$this->setControl('extended_text', 'dhtmltextarea');

		$this->setControl('type', array(
			'name' => 'select',
			'itemHandler' => 'publication',
			'method' => 'getTypeOptions',
			'module' => 'library',
			'onSelect' => 'submit'));
		
		// Only display the tag / category fields if the sprockets module is installed
		$sprocketsModule = icms_getModuleInfo('sprockets');
		if (icms_get_module_status("sprockets"))
		{
			$this->setControl('tag', array(
			'name' => 'selectmulti',
			'itemHandler' => 'tag',
			'method' => 'getTags',
			'module' => 'sprockets'));
			
			$this->setControl('category', array(
			'name' => 'selectmulti',
			'itemHandler' => 'tag',
			'method' => 'getCategoryOptions',
			'module' => 'sprockets'));
		}
		else 
		{
			$this->hideFieldFromForm('tag');
			$this->hideFieldFromSingleView ('tag');
			$this->hideFieldFromForm('category');
			$this->hideFieldFromSingleView ('category');
		}		
		
		$this->setControl('format', array(
			'name' => 'select',
			'itemHandler' => 'publication',
			'method' => 'getFormatOptions',
			'module' => 'library'));
		
		$this->setControl("image", "image");
		
		// Set uploads directory for images
		$this->setControl('image', array('name' => 'image'));
		$url = ICMS_URL . '/uploads/' . basename(dirname(dirname(__FILE__))) . '/';
		$path = ICMS_ROOT_PATH . '/uploads/' . basename(dirname(dirname(__FILE__))) . '/';
		$this->setImageDir($url, $path);
		
		$this->setControl('source', array(
			'itemHandler' => 'publication',
			'method' => 'getSourceList',
			'module' => 'library'));
		
		$this->setControl('rights', array(
			'itemHandler' => 'rights',
			'method' => 'getRights',
			'module' => 'sprockets'));
		
		$this->setControl('language', array(
			'name' => 'select',
			'itemHandler' => 'publication',
			'method' => 'getLanguageOptions',
			'module' => 'library'));
		
		$this->setControl('submitter', 'user');
		$this->setControl('compact_view', 'yesno');
		$this->setControl('online_status', 'yesno');
		$this->setControl('federated', 'yesno');
		
		// MANDATORY CONTROL VIEW SETTINGS:
		
		// Make the oai_identifier read only for OAIPMH archive integrity purposes. These must 
		// never change as external harvesters use them as markers to detect duplicate records
		$this->doMakeFieldreadOnly('oai_identifier');
		
		// Force html and don't allow user to change; necessary for RSS feed integrity
		$this->doHideFieldFromForm('dohtml');
		
		// For backend use only - tracking notifications for this object
		$this->hideFieldFromForm ('notification_sent');
		$this->hideFieldFromSingleView ('notification_sent');
		
		// For backend use only - compact view is only available to Collection type publications
		$this->doHideFieldFromForm('compact_view');
		$this->hideFieldFromSingleView('compact_view');
	}

	/**
	 * Overriding the icms_ipf_Object::getVar method to assign a custom method on some
	 * specific fields to handle the value before returning it
	 *
	 * @param str $key key of the field
	 * @param str $format format that is requested
	 * @return mixed value of the field that is requested
	 */
	public function getVar($key, $format = "s") {
		if ($format == "s" && in_array($key, array(
			'creator',
			'date',
			'file_size',
			'image',
			'language',
			'rights',
			'source',
			'online_status',
			'federated',
			'submitter',
			'format',
			'oai_identifier'))) {
			return call_user_func(array ($this,	$key));
		}
		return parent::getVar($key, $format);
	}
	
	/*
     * Converts pipe-delimited creator field to comma separated for user side presentation
	*/
	public function creator() {
		$creator = $this->getVar('creator', 'e');
		if ($creator) {
			$creator = str_replace("|", ", ",  $creator);
		}
		return $creator;
	}
	
	/*
     * Formats the date in a sane (non-American) way
	*/
	public function date()
	{
		$libraryModule = icms_getModuleInfo(basename(dirname(dirname(__FILE__))));
		$date = $this->getVar('date', 'e');
		if ($date) {
			$date = date($libraryModule->config['date_format'], $date);
		}
		return $date;
	}
	
	/*
     * Converts federated field to human readable value
	*/

	public function federated() {
		$button = '';
		$type = $this->getVar('type', 'e');
		$federated = $this->getVar('federated', 'e');

		if ($type == 'Collection') {
			$button = '<a href="' . ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/admin/publication.php?publication_id=' . $this->getVar('publication_id')
				. '&amp;op=changeFederated">';
		} else {
			$button = '<a href="' . ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/admin/publication.php?publication_id=' . $this->getVar('publication_id')
				. '&amp;op=changeFederated">';
		}
		if ($federated == FALSE) {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_cancel.png" alt="' 
				. _CO_LIBRARY_PUBLICATION_OFFLINE . '" title="'
				. _CO_LIBRARY_PUBLICATION_NOT_FEDERATED . '" /></a>';
		} else {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_ok.png" alt="'
				. _CO_LIBRARY_PUBLICATION_ONLINE . '" title="' 
				. _CO_LIBRARY_PUBLICATION_FEDERATED . '" /></a>';
		}
		return $button;
	}
	
	/*
     * Utility to convert bytes to a more readable form (KB, MB etc)
	*/
	public function file_size() {
		$unit = $value = $output = '';
		$bytes = $this->getVar('file_size', 'e');

		if ($bytes == 0 || $bytes < 1024) {
			$unit = ' bytes';
			$value = $bytes;
		} elseif ($bytes > 1023 && $bytes < 1048576) {
			$unit = ' KB';
			$value = ($bytes / 1024);
		} elseif ($bytes > 1048575 && $bytes < 1073741824) {
			$unit = ' MB';
			$value = ($bytes / 1048576);
		} else {
			$unit = ' GB';
			$value = ($bytes / 1073741824);
		}
		$value = round($value, 2);
		$output = $value . ' ' . $unit;
		
		return $output;
	}
	
	/*
     * Converts mimetype id to human readable value (extension)
	*/
	public function format() {
		if ($this->getVar('format', 'e') !== 0) {
		$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
		$mimetypeObj = $system_mimetype_handler->get($this->getVar('format', 'e'));
		$mimetype = $mimetypeObj->getVar('extension');
		return $mimetype;
		} else {
			return FALSE;
		}
	}
	
	/*
	 * Generates a html snippet for visualising the image
	 */
	public function image() {
		$image = $this->getVar('image', 'e');
		$image_for_display = '<img src="' . $this->getImageDir() . $image 
				. '" alt="' . $this->getVar('title') 
				. '" title="' . $this->getVar('title') . '" />';
		return $image_for_display;
	}
	
	/*
     * Converts the language key to a human readable title
	*/
	public function language() {
		$language_key = $this->getVar('language', 'e');
		if ($language_key) {
			$language_list = $this->handler->getLanguageOptions();
		return $language_list[$language_key];
		}
	}
	
	/*
     * Converts the rights id to a human readable title
	*/
	public function rights() {
		$sprocketsModule = icms_getModuleInfo('sprockets');
		
		if (icms_get_module_status("sprockets")) {
			$rights_id = $this->getVar('rights', 'e');
			$sprockets_rights_handler = icms_getModuleHandler('rights',
				$sprocketsModule->getVar('dirname'), 'sprockets');
			$rights_object = $sprockets_rights_handler->get($rights_id);
			$rights = $rights_object->getItemLink();
			return $rights;
		} else {
			return FALSE;
		}
	}
	
	/*
	 * Converts the oai_identifier to a permalink
	 */
	public function oai_identifier() {
		$oai_identifier = $this->getVar('oai_identifier', 'e');
		$permalink = '<a href="' . ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__))) 
				. '/permalink.php?id=' . $oai_identifier . '">' 
				. _CO_LIBRARY_PUBLICATION_PERMALINK . '</a>';
		return $permalink;
	}
	
	/*
     * Converts the source (publication/collection) id to a human readable title with link
	*/
	public function source() {
		$source = $this->getVar('source', 'e');
		$library_publication_handler = icms_getModuleHandler('publication',
			basename(dirname(dirname(__FILE__))), 'library');
		$publication_object = $library_publication_handler->get($source);
		if ($publication_object)
		{
			return $publication_object->getItemLink();
		}
		return FALSE;
	}
	
	/*
     * Converts status field to clickable icon that can change status
	*/
	public function online_status() {
		$button = '';
		$type = $this->getVar('type', 'e');
		$status = $this->getVar('online_status', 'e');

		if ($type == 'Collection') {
			$button = '<a href="' . ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/admin/publication.php?publication_id=' . $this->getVar('publication_id')
				. '&amp;op=changeStatus">';
		} else {
			$button = '<a href="' . ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/admin/publication.php?publication_id=' . $this->getVar('publication_id')
				. '&amp;op=changeStatus">';
		}
		if ($status == '1') {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_ok.png" alt="' 
				. _CO_LIBRARY_PUBLICATION_ONLINE . '" title="'
				. _CO_LIBRARY_PUBLICATION_ONLINE . '" /></a>';
		} else {
			$button .= '<img src="' . ICMS_IMAGES_SET_URL . '/actions/button_cancel.png" alt="'
				. _CO_LIBRARY_PUBLICATION_ONLINE . '" title="'
				. _CO_LIBRARY_PUBLICATION_ONLINE . '" /></a>';
		}
		return $button;
	}
	
	/*
     * Converts user id to human readable user name
	*/
	public function submitter() {
		return icms_member_user_Handler::getUserLink($this->getVar('submitter', 'e'));
	}
	
	/**
	 * Load tags linked to this publication
	 *
	 * @return void
	 */
	public function loadTags() {
		
		$ret = array();
		
		// Retrieve the tags for this object (which will include both tags and category label_type)
		$sprocketsModule = icms_getModuleInfo('sprockets');
		if (icms_get_module_status("sprockets")) {
			$sprockets_taglink_handler = icms_getModuleHandler('taglink',
					$sprocketsModule->getVar('dirname'), 'sprockets');
			$ret = $sprockets_taglink_handler->getTagsForObject($this->id(), $this->handler, '0'); // label_type = 0 means only return tags
			$this->setVar('tag', $ret);
		}
	}
	
	/**
	 * Load categories linked to this publication
	 *
	 * @return void
	 */
	public function loadCategories() {
		
		$ret = array();
		
		// Retrieve the tags for this object (which will include both tags and category label_type)
		$sprocketsModule = icms_getModuleInfo('sprockets');
		if (icms_get_module_status("sprockets")) {
			$sprockets_taglink_handler = icms_getModuleHandler('taglink',
					$sprocketsModule->getVar('dirname'), 'sprockets');
			$ret = $sprockets_taglink_handler->getTagsForObject($this->id(), $this->handler, '1'); // label_type = 1 means only return categories
			$this->setVar('category', $ret);
		}
	}
	
	/**
	 * Shows, hides fields and sets requirement status of fields according to publication type.
	 *
	 * Modifies the publication submission form to suit the type of publication currently selected.
	 * It also tries to protect the user by enforcing required fields where possible. However, the
	 * user must still use their brain occasionally to make sensible decisions. See the user manual
	 * for guidance on use of the publication submission form.
	 */
	public function contextualiseFormFields()
	{
		switch ($this->getVar('type', 'e')) {
			case 'Text':
				// Identifier and file size are optional, for example a text-only article displayed
				// on screen doesn't need them. However, a downloadable PDF version of an
				// article *does*. Admins need to make intelligent choices when submitting items.
				break;

			case 'Image':
				// If the object is an image, then the image field is required. Only local images
				// are allowed for integrity reasons, therefore the identifier field is hidden.
				// IPF bug: Setting image fields as required doesn't seem to work.
				$this->setFieldAsRequired('image', TRUE);
				$this->setFieldAsRequired('file_size', TRUE);
				$this->setFieldAsRequired('format', TRUE);

				$this->doHidefieldFromForm('identifier');
				$this->hideFieldFromSingleView ('identifier');
				$this->setVar('identifier', '');
				
				$this->doHidefieldFromForm('language');
				$this->setVar('language', 0);				
				break;

			case 'MovingImage':
				// Can support embedded or downloadable videos, therefore identifier, file size and
				// format are optional
				break;
			
			case 'Sound':
				// Sound files require a URL to the resource. The format and file size are required
				// as these are important for correct representation of media enclosures in RSS / 
				// podcasting fields.
				$this->setFieldAsRequired('identifier', TRUE);
				$this->setFieldAsRequired('file_size', TRUE);
				$this->setFieldAsRequired('format', TRUE);
				break;

			case 'Dataset':
			case 'Software':
				// Downloadable items, identifier, file size and format are required.
				$this->setFieldAsRequired('identifier', TRUE);
				$this->setFieldAsRequired('file_size', TRUE);
				$this->setFieldAsRequired('format', TRUE);
				break;

			case 'Collection';
				// Collections do not *have* to be downloadable entities (for example, could be a
				// text description of an album, with individually downloadable soundtracks, 
				// so identifier, file size and format are optional. Compact view is a setting that
				// is only available to collections; it displays the related works as a simple list
				// to save space (or in cases where the related works don't have a description, 
				// which is often the case with soundtracks that are part of an album).
				$this->setVar('source', 0);
				$this->doHideFieldFromForm('source');
				$this->hideFieldFromSingleView ('source');
				$this->doShowFieldOnForm('compact_view');
				break;

			default:
		}
	}
		
		/**
		 * Unsets publication properties that have been toggled off in Library preferences
		 * 
		 * Prevents unwanted or inappropriate fields from being displayed on the user side. Called 
		 * whenever a publication is viewed from the front end. Basically the vars are set to NULL
		 * and when Smarty tests for their existance they will be removed from the template.
		 */
		public function setFieldDisplayPreferences()
		{
			$library = basename(dirname(dirname(__FILE__)));
			
			if (icms_getConfig('display_creator_field', $library) == '0') {
				echo $this->getVar('creator', 'e');
				$this->setVar('creator', '');
			}
			if (icms_getConfig('display_date_field', $library) == '0') {
				$this->setVar('date', '');
			}
			if (icms_getConfig('display_language_field', $library) == '0') {
				$this->setVar('language', '');
			}
			if (icms_getConfig('display_format_field', $library) == '0') {
				$this->setVar('format', '');
				$this->setVar('file_size', '');
			}
			if (icms_getConfig('display_rights_field', $library) == '0') {
				$this->setVar('rights', '');
			}
			if (icms_getConfig('display_counter_field', $library) == '0') {
				$this->setVar('counter', '');
			}
			if (icms_getConfig('display_publisher_field', $library) == '0') {
				$this->setVar('publisher', '');
			}
			if (icms_getConfig('display_source_field', $library) == '0') {
				$this->setVar('source', '');
			}
			if (icms_getConfig('display_submitter_field', $library) == '0') {
				$this->setVar('submitter', '');
			}
		}
	
	/**
	 * View publication within admin page
	 */
	public function getAdminViewItemLink() {
		$ret = '<a href="' . LIBRARY_ADMIN_URL . 'publication.php?op=view&amp;publication_id=' 
			. $this->getVar('publication_id', 'e') . '" title="' . _CO_LIBRARY_PUBLICATION_VIEW 
			. '">' . $this->getVar('title') . '</a>';
		return $ret;
	}
}