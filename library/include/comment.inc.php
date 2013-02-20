<?php
/**
 * Comment include file
 *
 * File holding functions used by the module to hook with the comment system of ImpressCMS
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

function library_com_update($item_id, $total_num) {
    $library_publication_handler = icms_getModuleHandler("publication", basename(dirname(dirname(__FILE__))), "library");
    $library_publication_handler->updateComments($item_id, $total_num);
}

function library_com_approve(&$comment) {
    // notification mail here
}