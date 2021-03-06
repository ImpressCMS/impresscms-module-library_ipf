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
		icms_getConfig('display_creator_field', 'library');
		parent::__construct($db, "publication", "publication_id", "title", "description", "library");
		$this->enableUpload(array("image/gif", "image/jpeg", "image/pjpeg", "image/png"), 
				icms_getConfig('image_file_size', 'library'),
				icms_getConfig('image_upload_width', 'library'),
				icms_getConfig('image_upload_height', 'library'));
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
		$count = $results = '';
		$criteria = new icms_db_criteria_Compo();
		
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
				$criteriaKeyword->add(new icms_db_criteria_Item('creator', '%' . $queryarray[$i]
					. '%', 'LIKE'), 'OR');
				$criteriaKeyword->add(new icms_db_criteria_Item('description', '%' . $queryarray[$i]
					. '%', 'LIKE'), 'OR');
				$criteriaKeyword->add(new icms_db_criteria_Item('extended_text', '%' . $queryarray[$i]
					. '%', 'LIKE'), 'OR');
				$criteriaKeyword->add(new icms_db_criteria_Item('publisher', '%' . $queryarray[$i]
					. '%', 'LIKE'), 'OR');
				$criteriaKeywords->add($criteriaKeyword, $andor);
				unset ($criteriaKeyword);
			}
			$criteria->add($criteriaKeywords);
		}
		
		$criteria->add(new icms_db_criteria_Item('online_status', TRUE));
		
		/*
		 * Improving the efficiency of search
		 * 
		 * The general search function is not efficient, because it retrieves all matching records
		 * even when only a small subset is required, which is usually the case. The full records 
		 * are retrieved so that they can be counted, which is used to display the number of 
		 * search results and also to set up the pagination controls. The problem with this approach 
		 * is that a search generating a very large number of results (eg. > 650) will crash out. 
		 * Maybe its a memory allocation issue, I don't know.
		 * 
		 * A better approach is to run two queries: The first a getCount() to find out how many 
		 * records there are in total (without actually wasting resources to retrieve them), 
		 * followed by a getObjects() to retrieve the small subset that are actually needed. 
		 * Due to the way search works, the object array needs to be padded out 
		 * with the number of elements counted in order to preserve 'hits' information and to construct
		 * the pagination controls. So to minimise resources, we can just set their values to '1'.
		 * 
		 * In the long term it would be better to (say) pass the count back as element[0] of the 
		 * results array, but that will require modification to the core and will affect all modules.
		 * So for the moment, this hack is convenient.
		 */
		
		$criteria->setStart($offset);
		$criteria->setSort('date');
		$criteria->setOrder('DESC');
		
		// Count the number of search results WITHOUT actually retrieving the objects
		$count = $this->getCount($criteria);
		
		// Retrieve the subset of results that are actually required
		if (!$limit) {
			global $icmsConfigSearch;
			$limit = $icmsConfigSearch['search_per_page'];
		}

		$criteria->setLimit($limit);
		$results = $this->getObjects($criteria, FALSE, TRUE);
		
		// Pad the results array out to the counted length to preserve 'hits' and pagination controls.
		// This approach is not ideal, but it greatly reduces the load for queries with large result sets
		$results = array_pad($results, $count, 1);

		return $results;
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
	
	/*
	 * Counts the number of (online) publications for a tag to support pagination controls
	 */
	public function getPublicationCountForTag($tag_id)
	{
		// Sanitise the parameter
		$clean_tag_id = isset($tag_id) ? (int)$tag_id : 0 ;
		
		$libraryModule = $this->getModuleInfo();
		
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
		$group_query = "SELECT count(*) FROM " . $this->table . ", "
				. $sprockets_taglink_handler->table
				. " WHERE `publication_id` = `iid`"
				. " AND `online_status` = '1'"
				. " AND `tid` = '" . $clean_tag_id . "'"
				. " AND `mid` = '" . $libraryModule->getVar('mid') . "'"
				. " AND `item` = 'publication'";
		$result = icms::$xoopsDB->query($group_query);
		if (!$result) {
			echo 'Error';
			exit;
		}
		else {
			while ($row = icms::$xoopsDB->fetchArray($result)) {
				foreach ($row as $key => $count) {
					$publication_count = $count;
				}
			}
			return $publication_count;
		}
	}

	/*
	 * Retrieves a list of publications for a given tag, formatted for user-side display
	 * 
	 * @return array publications
	 */
	public function getPublicationsForTag($tag_id, $count, $start)
	{
		// Sanitise the parameters
		$clean_tag_id = isset($tag_id) ? (int)$tag_id : 0 ;
		$publication_count = isset($count) ? (int)$count : 0 ;
		$clean_start = isset($start) ? (int)$start : 0 ;
			
		$library_publication_summaries = array();
		$libraryModule = $this->getModuleInfo();
		
		$query = $rows = '';
		$linked_publication_ids = array();
		$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');

		$query = "SELECT * FROM " . $this->table . ", "
				. $sprockets_taglink_handler->table
				. " WHERE `publication_id` = `iid`"
				. " AND `online_status` = '1'"
				. " AND `tid` = '" . $clean_tag_id . "'"
				. " AND `mid` = '" . $libraryModule->getVar('mid') . "'"
				. " AND `item` = 'publication'"
				. " ORDER BY `submission_time` DESC"
				. " LIMIT " . $clean_start . ", " . $libraryModule->config['number_publications_per_page'];
		$result = icms::$xoopsDB->query($query);
		if (!$result) {
			echo 'Error';
			exit;
		}
		else
		{
			// Retrieve publications as objects, with id as key, and prepare for display
			$rows = $this->convertResultSet($result, TRUE, TRUE);
			
			// Prepare buffers to minimuse query looks due to certain getVar() overrides
			$system_mimetype_handler = icms_getModuleHandler('mimetype', 'system');
			$format_buffer = $system_mimetype_handler->getObjects(FALSE, TRUE, TRUE);
			if (icms_get_module_status("sprockets")) {
				$sprockets_rights_handler = icms_getModuleHandler('rights', 'sprockets', 'sprockets');
				$rights_buffer = $sprockets_rights_handler->getObjects(FALSE, TRUE, TRUE);
			}
			foreach ($rows as $pubObj) {
				$publication = $this->toArrayForDisplay($pubObj, FALSE);
				
				// Manually convert some fields to human readable from buffers, to reduce query load
				if (isset($publication['rights']) && isset($rights_buffer)) {
					$publication['rights'] = $rights_buffer[$publication['rights']]->getItemLink();
				}
				if (isset($publication['format'])) {
					$publication['format'] = $format_buffer[$publication['format']]->getVar('extension');
				}
				$library_publication_summaries[$pubObj->getVar('publication_id')] = $publication;
			}
			return $library_publication_summaries;
		}
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
			case "Event":
			case "Software":
			case "Dataset":
				return "db:library_publication_text.html";
				break;
			case "Sound":
				return "db:library_publication_sound.html";
				break;
			case "Image":
				return "db:library_publication_image.html";
				break;
			case "MovingImage":
				return "db:library_publication_moving_image.html";
				break;	
			case "Collection":
				return "db:library_publication_collection.html";
				break;
			//case "Event":
			//	break;
			// case "InteractiveResource":
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
	 * Unsets publication display fields as per the module preferences, on behalf of toArrayForDisplay()
	 * 
	 * @param array $publication
	 * @return array 
	 */
	private function unsetFieldPreferences($publication)
	{
		$library = basename(dirname(dirname(__FILE__)));
		
		if (icms_getConfig('display_counter_field', $library) == '0') {
			unset($publication['counter']);
		}
		if (icms_getConfig('display_creator_field', $library) == '0') {
			unset($publication['creator']);
		}
		if (icms_getConfig('display_date_field', $library) == '0') {
			unset($publication['date']);
		}
		if (icms_getConfig('display_language_field', $library) == '0') {
			unset($publication['language']);
		}
		if (icms_getConfig('display_file_size_field', $library) == '0') {
			unset($publication['file_size']);
		}
		if (icms_getConfig('display_format_field', $library) == '0') {
			unset($publication['format']);
		}
		if (icms_getConfig('display_publisher_field', $library) == '0') {
			unset($publication['publisher']);
		}
		if (icms_getConfig('display_rights_field', $library) == '0') {
			unset($publication['rights']);
		}
		if (icms_getConfig('display_source_field', $library) == '0') {
			unset($publication['source']);
		}
		if (icms_getConfig('display_submitter_field', $library) == '0') {
			unset($publication['submitter']);
		}
		
		return $publication;
	}
	
	/**
	 * Converts individual publication objects to array and unsets properties toggled off in 
	 * module preferences, optionally restrict getVar() overrides if manual buffering is required.
	 * 
	 * Prevents unwanted or inappropriate fields from being displayed on the user side, and adds 
	 * some additional type-speciic fields where required. Call it whenever a publication is viewed
	 * from the front end instead of using ->toArray(). Basically the vars are unset and when Smarty
	 * tests for their existance they will be removed from the template.
	 * 
	 * The $with_overrides parameter toggles getVar() overrides for certain fields (see the object
	 * method toArrayWithoutOverrides for a list). Some overrides require a query lookup to get 
	 * the value; if you are processing a large number of records, for example on an index page, 
	 * this can get expensive. In that case its better to toggle overrides off and manually 
	 * convert the values from buffers.
	 * 
	 * @param array $pubArray - a publication object that has been converted ->toArray()
	 * @return array
	 */
	public function toArrayForDisplay(&$pubObj, $with_overrides = TRUE)
	{
		$library = basename(dirname(dirname(__FILE__)));
		
		if ($with_overrides) {
			$publication = $pubObj->toArray();
		} else {
			$publication = $pubObj->toArrayWithoutOverrides();
		}
		
		// Unset any unwanted fields so that they don't display
		$publication = $this->unsetFieldPreferences($publication);
		
		// Add admin links
		$publication['editItemLink'] = $pubObj->getEditItemLink(FALSE, TRUE, FALSE);
		$publication['deleteItemLink'] = $pubObj->getDeleteItemLink(FALSE, TRUE, FALSE);
		
		// If an image is present, set resizing preferences
		if ($publication['image']) {
			$publication['image'] = '/uploads/' . basename(dirname(dirname(__FILE__))) . '/publication/'
				. $pubObj->getVar('image', 'e');
			$publication['screenshot_width'] = icms_getConfig('screenshot_width', $library);
			$publication['screenshot_height'] = icms_getConfig('screenshot_height', $library);
			$publication['image_width'] = icms_getConfig('image_width', $library);
			$publication['image_height'] = icms_getConfig('image_height', $library);
		}		
		
		// Assign an appropriate template for this publication type.
		$publication['subtemplate'] = $this->assignTemplate($publication['type']);
			
		////////////////////////////////////////////////////////////////
		////////// Add type-specific fields here, as required //////////
		////////////////////////////////////////////////////////////////
		
		// Create a streaming link for sound type publications
		if (($pubObj->getVar('type', 'e') == 'Sound') || ($pubObj->getVar('type', 'e') == 'MovingImage'
				&& $pubObj->getVar('identifier')))
		{
			if ($publication['itemUrl']) {
				$publication['streamingLink'] = '<a href="' . $publication['itemUrl'] 
						. '&amp;m3u=1';
				if (!empty($publication['short_url'])) {
					$publication['streamingLink'] .= "&amp;title=" . $publication['short_url'];
				}
				$publication['streamingLink'] .= '">' . _CO_LIBRARY_STREAMING . '</a>';
			}
		}
		
		// Add SEO friendly string to URL and itemLink
		if (!empty($publication['short_url'])) {
			$publication['itemUrl'] .= "&amp;title=" . $publication['short_url'];
			$publication['itemLink'] = '<a href="' . $publication['itemUrl'] . '">' 
					. $publication['title'] . '</a>';
		}
		
		return $publication;
	}
	
	/**
	 * Flush the cache for the Library module after adding, editing or deleting a publication.
	 * 
	 * Ensures that the index/block/single view cache is kept updated if caching is enabled.
	 * 
	 * @global array $icmsConfig
	 * @param type $obj 
	 */
	protected function clear_cache(& $obj)
	{
		global $icmsConfig;
		$cache_status = $icmsConfig['module_cache'];
		$module = icms::handler("icms_module")->getByDirname("library", TRUE);
		$module_id = $module->getVar("mid");
			
		// Check if caching is enabled for this module. The cache time is stored in a serialised 
		// string in config table (module_cache), and is indicated in seconds. Uncached = 0.
		if ($cache_status[$module_id] > 0)
		{			
			// As PHP's exec() function is often disabled for security reasons
			try 
			{	
				// Index pages, including for tags
				exec("find " . ICMS_CACHE_PATH . "/" . "library^%2Fmodules%2Flibrary%2Fpublication.php^* -delete &");
				exec("find " . ICMS_CACHE_PATH . "/" . "library^%2Fmodules%2Flibrary%2Fpublication.php%3Fstart* -delete &");
				exec("find " . ICMS_CACHE_PATH . "/" . "library^%2Fmodules%2Flibrary%2Fpublication.php%3Ftag_id* -delete &");
				
				// Tags and categories index pages
				exec("find " . ICMS_CACHE_PATH . "/" . "library^%2Fmodules%2Flibrary%2Ftag.php* -delete &");
				
				// Timeline pages
				exec("find " . ICMS_CACHE_PATH . "/" . "library^%2Fmodules%2Flibrary%2Ftimeline.php* -delete &");
				
				// Blocks
				exec("find " . ICMS_CACHE_PATH . "/" . "blk_library* -delete &");
				
				// Individual publication page
				if (!$obj->isNew())
				{
					exec("find " . ICMS_CACHE_PATH . "/" . "library^%2Fmodules%2Flibrary%2Fpublication.php%3Fpublication_id%3D" 
							. $obj->getVar('publication_id', 'e') . "%26* -delete &");
					exec("find " . ICMS_CACHE_PATH . "/" . "library^%2Fmodules%2Flibrary%2Fpublication.php%3Fpublication_id%3D" 
							. $obj->getVar('publication_id', 'e') . "^* -delete &");
				}				
			}
			catch(Exception $e)
			{
				$obj->setErrors($e->getMessage());
			}
		}		
	}
	
	/**
	 * Adjust data before saving or updating
	 * @param object $obj 
	 */
	protected function beforeSave(& $obj)
	{		
		// Strip non-numerical characters out of the file size field, so can paste in from Windows directly
		$file_size = $obj->getVar('file_size', 'e');
		if ($file_size) {
			$file_size = preg_replace('/\D/', '', $file_size);
			$obj->setVar('file_size', $file_size);
		}
		
		return TRUE;
	}
	
	/**
	 * Manages tracking of categories (via taglinks), called when a message is inserted or updated
	 *
	 * @param object $obj LibraryPublication object
	 * @return bool
	 */
	protected function afterSave(& $obj)
	{		
		$sprockets_taglink_handler = '';
		$sprocketsModule = icms::handler("icms_module")->getByDirname("sprockets");
		
		// Triggers notification event for subscribers. Add 10:01 minutes to the timestamp check
		// to account for the fact that submission time (XOBJ_DTYPE_LTIME) works on 10 minute 
		// increments. Otherwise, it is likely that the check will fail and no notification will be
		// sent.
		if($obj->getVar('submission_time', 'e') < (time() + 601)) {
			if (!$obj->getVar('notification_sent') && $obj->getVar ('online_status', 'e') == 1) {
			$obj->sendNotifPublicationPublished();
			$obj->setVar('notification_sent', TRUE);
			$this->insert($obj);
			}
		}
		
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
		
		// Clear cache
		$this->clear_cache($obj);	
	
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
		
		// Delete global notifications
		$notification_handler = icms::handler('icms_data_notification');
		$libraryModule = icms::handler("icms_module")->getByDirname("library");
		$module_id = $libraryModule->getVar('mid');
		$category = 'global';
		$item_id = $obj->getVar('publication_id');		
		$notification_handler->unsubscribeByItem($module_id, $category, $item_id);
		
		// Delete publication bookmarks
		$category = 'publication';
		$notification_handler->unsubscribeByItem($module_id, $category, $item_id);

		// Delete taglinks
		if (icms_get_module_status("sprockets")) {
			$sprockets_taglink_handler = icms_getModuleHandler('taglink',
					$sprocketsModule->getVar('dirname'), 'sprockets');
			$sprockets_taglink_handler->deleteAllForObject($obj);
		}
		
		// Clear cache
		$this->clear_cache($obj);	
		
		// To do: Need to search for other publications that have this one marked as source and
		// delete the reference
		
		return TRUE;
	}
}