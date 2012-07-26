<?php
/**
 * Common functions used by the module
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

/**
 * Notification lookup function
 *
 * This function is called by the notification process to get an array contaning information
 * about the item for which there is a notification
 *
 * @param string $category category of the notification
 * @param int $item_id id f the item related to this notification
 *
 * @return array containing 'name' and 'url' of the related item
 */
function library_notify_iteminfo($category, $item_id){
    global $icmsModule, $icmsModuleConfig, $icmsConfig;

    if ($category == 'global') {
        $item['name'] = '';
        $item['url'] = '';
        return $item;
    }
	
	if ($category == 'publication') {

		$library_publication_handler = icms_getModuleHandler('publication',
			basename(dirname(dirname(__FILE__))), 'library');
		$publicationObj = $library_publication_handler->get($item_id);
		if ($publicationObj) {
			$item['name'] = $publicationObj->title();
			$item['url'] = ICMS_URL . '/modules/' . basename(dirname(dirname(__FILE__)))
				. '/publication.php?publication_id=' . intval($item_id);
			return $item;
		} else {
			return null;
		}
	}
}