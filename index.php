<?php
/**
 * User index page of the module
 *
 * Including the publication page
 *
 * @copyright	Copyright Madfish (Simon Wilkinson) 2012
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since		1.0
 * @author		Madfish (Simon Wilkinson) <simon@isengard.biz>
 * @package		library
 * @version		$Id$
 */

include_once "../../mainfile.php";
include_once ICMS_ROOT_PATH . "/header.php";

$start_options = array(0 => 'publication.php', 1 => 'tag.php', 2 => 'tag.php?label_type=1', 3 => 'timeline.php');
$start_page = $start_options[icms::$module->config['library_start_page']];

header('location: ' . $start_page);
exit();