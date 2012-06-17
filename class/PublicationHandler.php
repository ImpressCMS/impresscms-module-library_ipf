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
	
	public function getTypeOptions()
	{
		$options = array(
			'Collection',
			'Dataset',
			//'Event',
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
}