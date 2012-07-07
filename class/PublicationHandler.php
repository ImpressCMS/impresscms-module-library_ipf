<?php
/**
 * Classes responsible for managing Library publication objects
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

defined("ICMS_ROOT_PATH") or die("ICMS root path not defined");

class mod_library_PublicationHandler extends icms_ipf_Handler {
	/**
	 * Constructor
	 *
	 * @param icms_db_legacy_Database $db database connection object
	 */
	public function __construct(&$db) {
		parent::__construct($db, "publication", "publication_id", "title", "description", "library");
		$this->enableUpload(array("image/gif", "image/jpeg", "image/pjpeg", "image/png"), 512000, 800, 600);
	}
	
	/**
	 * Provides the global search functionality for the Library module
	 *
	 * @param array $queryarray
	 * @param string $andor
	 * @param int $limit
	 * @param int $offset
	 * @param int $userid
	 * @return array 
	 */
	public function getPublicationsForSearch($queryarray, $andor, $limit, $offset, $userid)
	{		
		$criteria = new icms_db_criteria_Compo();
		$criteria->setStart($offset);
		$criteria->setLimit($limit);
		$criteria->setSort('title');
		$criteria->setOrder('ASC');

		if ($userid != 0) 
		{
			$criteria->add(new icms_db_criteria_Item('submitter', $userid));
		}
		
		if ($queryarray) 
		{
			$criteriaKeywords = new icms_db_criteria_Compo();
			for ($i = 0; $i < count($queryarray); $i++) {
				$criteriaKeyword = new icms_db_criteria_Compo();
				$criteriaKeyword->add(new icms_db_criteria_Item('title', '%' . $queryarray[$i] . '%',
					'LIKE'), 'OR');
				$criteriaKeyword->add(new icms_db_criteria_Item('description', '%' . $queryarray[$i]
					. '%', 'LIKE'), 'OR');
				$criteriaKeywords->add($criteriaKeyword, $andor);
				unset ($criteriaKeyword);
			}
			$criteria->add($criteriaKeywords);
		}
		
		$criteria->add(new icms_db_criteria_Item('online_status', TRUE));
		
		return $this->getObjects($criteria, TRUE, TRUE);
	}
	
	/**
	 * Returns a list of document types, using the Dublin Core Type Vocabulary
	 * 
	 * On selection of a type, the publication entry page reloads to display appropriate fields.
	 *
	 * @return array mixed
	 */
	public function getTypeOptions()
	{
		$options = array(
			'Text' => 'Text',
			'Image' => 'Image',
			'MovingImage' => 'Moving Image',
			'Sound' => 'Sound',
			'Software' => 'Software',
			'Dataset' => 'Dataset',
			'Collection' => 'Collection'			
			//'Event' => 'Event',
			//'InteractiveResource' => 'Interactive Resource',
			//'Service' => 'Service',
			//'PhysicalObject' = 'Physical Object'
		);
		
		return $options;
	}
	
	/**
	 * Returns a list of authorised mimetypes (using extension as the user side proxy)
	 *
	 * @return array mixed
	 */
	public function getFormatOptions()
	{
		$mimetypeObjArray = $mimetypeArray = array();
		
		$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
		$criteria = new icms_db_criteria_Compo(new icms_db_criteria_Item('dirname', '%' . basename(dirname(dirname(__FILE__))) . '%', 'LIKE'));
		$mimetypeObjArray = $system_mimetype_handler->getObjects($criteria);
		
		foreach($mimetypeObjArray as $mimetypeObj)
		{
			$mimetypeArray[$mimetypeObj->getVar('mimetypeid')] = '.' . $mimetypeObj->getVar('extension');
		}
		
		return $mimetypeArray;
	}
	
	/**
	 * Returns a list of collections (publications that are aggregates of others, eg. a music album)
	 * 
	 * @return array
	 */
	
	public function getSourceList()
	{
		$library_publication_handler = icms_getModuleHandler('publication', basename(dirname(dirname(__FILE__))), 'library');
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('type', 'Collection'));
		$collectionList = array( 0 => '---') + $library_publication_handler->getList($criteria);
		return $collectionList;
	}
	
	/**
	 * Returns a list of language options (using ISO 639-1 language codes)
	 *
	 * @return array
	 */
	
	public function getLanguageOptions()
	{
		include ICMS_ROOT_PATH . '/modules/' . basename(dirname(dirname(__FILE__))) . '/include/language.inc.php';
		return $language_options;
	}
	
	/**
	 * Used to assemble a unique identifier for a record as per the OAIPMH specs
	 * 
	 * The identifier takes the form oai:domain.com:timestamp
	 *
	 * @return string
	 */
	public function setOaiId()
	{		
		$id = $prefix = $namespace = $timestamp = '';
		
		$prefix = $this->getMetadataPrefix();
		$namespace = $this->getNamespace();
		$timestamp = time();
		$id = $prefix . ":" . $namespace . ":" . $timestamp;
		
		return $id;
	}
	
	/**
	 * Returns the available metadata prefixes for this archive (currently only 'oai')
	 *
	 * @return string 
	 */
	public function getMetadataPrefix()
	{		
		$metadataPrefix = '';
		
		$metadataPrefix = 'oai';
		return $metadataPrefix;
	}

	/**
	 * Used to assemble a unique oai_identifier for a record as per the OAIPMH specification
	 *
	 * @return string
	 */
	public function getNamespace()
	{		
		$namespace = '';
		
		$namespace = ICMS_URL;
		$namespace = str_replace('http://', '', $namespace);
		$namespace = str_replace('https://', '', $namespace);
		$namespace = str_replace('www.', '', $namespace);
		
		return $namespace;
	}
	

	/**
	 * Prepares a publication for user-side display
	 */
	
	public function prepareForDisplay($type)
	{
		// Convert properties to human readable
		
	}
	
	/**
	 * Sets a contextually appropriate template for this publication, based on its type
	 * 
	 * Most publication types have similar display requirements and can use text.html as a 
	 * common template. Some (sound, images) have more specialised requirements that warrant 
	 * a separate template. However, it is possible to create specialised templates for all 
	 * types simply by i) specifying the template name in the switch below, ii) creating a 
	 * matching template file and iii) declaring it in icms_version.php. Templates are 
	 * dynamically assigned at display time.
	 * 
	 * @param string $type
	 * @return String
	 */
	public function assignTemplate($type)
	{
		switch ($type)
		{
			case "Text":
			case "Collection":
			case "Dataset":
			case "Event":
			case "Software":
			case "Dataset":
				return "text.html";
				break;
			case "Sound":
				return "sound.html";
				break;
			case "Image":
			case "MovingImage":
				return "image.html";
				break;			
			case "Event":
				break;
			// case "InteractiveResource":\
			//	break;
			// case "Service":
			//	break;
			// case "PhysicalObject":
			//	break;
		}
	}
	
	/**
	 * Toggles the publication online_status and federated properties
	 *
	 * @param int $publication_id
	 * @param str $field
	 * @return int $visibility
	 */
	public function changeStatus($id, $field) {
		
		$visibility = $publicationObj = '';
		
		$publicationObj = $this->get($id);
		if ($publicationObj->getVar($field, 'e') == 1) {
			$publicationObj->setVar($field, 0);
			$visibility = 0;
		} else {
			$publicationObj->setVar($field, 1);
			$visibility = 1;
		}
		$this->insert($publicationObj, TRUE);
		
		return $visibility;
	}
	
	/**
	 * Allows the publications admin table to be sorted by publication status
	 */
	public function online_status_filter() {
		return array(0 =>  _CO_LIBRARY_PUBLICATION_OFFLINE, 1 =>  _CO_LIBRARY_PUBLICATION_ONLINE);
	}
	
	/**
	 * Allows the publications admin table to be sorted by publication format
	 */
	public function format_filter() {
		return $this->getFormatOptions();
	}
	
	/**
	 * Allows the publications admin table to be sorted by type
	 */
	public function type_filter() {
		return $this->getTypeOptions();
	}
	
	/**
	 * Allows the publications admin table to be sorted by rights
	 */
	public function rights_filter() {
		$rights_array = array();
		$sprockets_rights_handler = '';
		
		$sprockets_rights_handler = icms_getModuleHandler('rights', 'sprockets', 'sprockets');
		$rights_array = $sprockets_rights_handler->getList();
		
		return $rights_array;
	}
	
	/**
	 * Allows the publications admin table to be sorted by federation status
	 */
	public function federated_filter() {
		return array(0 =>  _CO_LIBRARY_PUBLICATION_FEDERATION_DISABLED,
			1 =>  _CO_LIBRARY_PUBLICATION_FEDERATION_ENABLED);
	}
	
	/**
	 * Updates comments
	 *
	 * @param int $id
	 * @param int $total_num
	 */
	public function updateComments($id, $total_num) {
			
		$obj = '';
		
		$obj = $this->get($id);
		if ($obj && !$obj->isNew()) {
			$obj->setVar('publication_comments', $total_num);
			$this->insert($obj, TRUE);
		}
	}
	
	/**
	 * Manages tracking of categories (via taglinks), called when a message is inserted or updated
	 *
	 * @param object $obj ContactMessage object
	 * @return bool
	 */
	protected function afterSave(& $obj)
	{		
		$sprockets_taglink_handler = '';

		$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
		
		// Only update the taglinks if the object is being updated from the add/edit form (POST).
		// Database updates are not permitted from GET requests and will trigger an error
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && icms_get_module_status("sprockets")) {
			$sprockets_taglink_handler = icms_getModuleHandler('taglink', 
					$sprocketsModule->getVar('dirname'), $sprocketsModule->getVar('dirname'), 'sprockets');
			
			// Store tags
			$sprockets_taglink_handler->storeTagsForObject($obj, 'tag', '0');
			
			// Store categories
			$sprockets_taglink_handler->storeTagsForObject($obj, 'category', '1');
		}
	
		return TRUE;
	}
	
	/**
	 * Deletes notification subscriptions and taglinks, called when an object is deleted
	 *
	 * @param object $obj object
	 * @return bool
	 */
	protected function afterDelete(& $obj) {
		
		$sprocketsModule = $notification_handler = $module_handler = $module = $module_id
				= $category = $item_id = '';
		
		$sprocketsModule = icms_getModuleInfo('sprockets');

		// Delete taglinks
		if (icms_get_module_status("sprockets")) {
			$sprockets_taglink_handler = icms_getModuleHandler('taglink',
					$sprocketsModule->getVar('dirname'), 'sprockets');
			$sprockets_taglink_handler->deleteAllForObject($obj);
		}
		
		return TRUE;
	}
}