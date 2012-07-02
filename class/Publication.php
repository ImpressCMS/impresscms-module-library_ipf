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
		$this->quickInitVar("cover", XOBJ_DTYPE_IMAGE, FALSE);
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
		$this->setControl("cover", "image");
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
		
		/*$this->setControl('source', array(
			'itemHandler' => 'publication',
			'method' => 'getSourceOptions',
			'module' => 'library'));*/
		
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
}