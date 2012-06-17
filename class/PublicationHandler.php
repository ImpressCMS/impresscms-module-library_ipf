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
			'Collection',
			'Dataset',
			'Event',
			'Image',
			//'InteractiveResource',
			//'Service',
			'Software',
			'Sound',
			'Text',
			//'PhysicalObject'
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
	
	public function getCollectionOptions()
	{
		$library_publication_handler = icms_getModuleHandler('publication', basename(dirname(dirname(__FILE__))), 'library');
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('type', '0'));
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
	public function setOaiId() {
		
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
	public function getMetadataPrefix() {
		
		$metadataPrefix = '';
		
		$metadataPrefix = 'oai';
		return $metadataPrefix;
	}

	/**
	 * Used to assemble a unique oai_identifier for a record as per the OAIPMH specification
	 *
	 * @return string
	 */
	public function getNamespace() {
		
		$namespace = '';
		
		$namespace = ICMS_URL;
		$namespace = str_replace('http://', '', $namespace);
		$namespace = str_replace('https://', '', $namespace);
		$namespace = str_replace('www.', '', $namespace);
		
		return $namespace;
	}
}