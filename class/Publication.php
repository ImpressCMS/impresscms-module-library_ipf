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
		$this->initCommonVar("counter");
		$this->initCommonVar("dohtml");
		$this->initCommonVar("dobr");
		$this->setControl("image", "image");
		$this->setControl("submitter", "user");

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
		
		// Hide the compact view control by default, it is only enabled for the collection type
		$this->doHideFieldFromForm('compact_view');
		$this->hideFieldFromSingleView ('compact_view');
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
		if ($format == "s" && in_array($key, array())) {
			return call_user_func(array ($this,	$key));
		}
		return parent::getVar($key, $format);
	}
	
	/*
     * Formats the date in a sane (non-American) way
	*/
	public function date()
	{
		$date = $this->getVar('date', 'e');
		$date = date('j/m/Y', $date);
		return $date;
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
	public function contextualiseFormFields() {

		// Disallowed fields must be purged in case the object type has been reassigned. Some fields 
		// may need to be set as required for certain publication types

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
				// Can support embedded videos, therefore identifier, file size and format are optional
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
}