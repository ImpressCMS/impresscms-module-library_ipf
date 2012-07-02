<?php


/**
 * Admin page to manage categories (only if Sprockets module is installed)
 *
 * List, add, edit and delete category objects
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

include_once "admin_header.php";

// Check if Sprockets module is installed, this page is only functional if it is.
$sprocketsModule = icms_getModuleInfo('sprockets');
		
if (icms_get_module_status("sprockets"))
{
	$clean_op = "";
	echo 'yes';
}
else
{
	exit;
}

icms_cp_footer();

/**
 * If you want to have a specific action taken because the user input was invalid,
 * place it at this point. Otherwise, a blank page will be displayed
 */