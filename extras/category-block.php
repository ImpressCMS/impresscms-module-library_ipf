// Custom PHP block that displays sprockets tags OR categories in a list. You must DISABLE
// HTML PURIFIER when saving or editing this block (you can turn it back on afterwards).
// Only tags actually in use by a module will be displayed. However, ALL categories associated with
// a module will be displayed to keep the tree structure intact

// Config options
$module_directory_name = 'cms'; // Filters results for a specific module (essential).
$item_name = 'start'; // name of the relevant module IPF object, eg 'article' for the News module
$label_type = 0; // 0 = show tags, 1 = show category tree
$navigation_element = 0; // 0 = only show tags marked as navigation elements, 1 = show all tags
$sort = "ASC"; // Sort order of the tags, ASC = ascending, DESC = descending

// Initialise
$tags = $taglinks = $tag_ids = '';
$criteria = new icms_db_criteria_Compo();
$sprockets_tag_handler = icms_getModuleHandler('tag', 'sprockets', 'sprockets');
$sprockets_taglink_handler = icms_getModuleHandler('taglink', 'sprockets', 'sprockets');
$module = icms::handler("icms_module")->getByDirname($module_directory_name);
$module_id = $module->getVar('mid');
icms_loadLanguageFile($module_directory_name, 'common');
icms_loadLanguageFile('sprockets', 'common');

// If a custom PHP block throws an error it crashes the control panel (unless you have the blocks 
// admin page open, in which case you can restore functionality by marking the problem block 
// invisible. I have added a try/catch block on the code as a convenience to prevent this behaviour.
// If you *do* crash your admin page, manually edit the database table new_blocks and mark your custom 
// PHP block as invisible to restore access.
try
{

	if ($label_type == 0 && $module_directory_name) // Retrieve list of TAGS
	{
		// Get a list of unique tag ids that are in use by the specified module
		$relevant_tag_ids = array();
		$criteria = icms_buildCriteria(array('mid' => $module_id));
		$taglinks = $sprockets_taglink_handler->getObjects($criteria, TRUE, TRUE);
		foreach ($taglinks as $key => $value)
		{
			$tag_ids[] = $value->getVar('tid', 'e');
		}
		$tag_ids = array_unique($tag_ids);
		$tag_ids = '(' . implode(',', $tag_ids) . ')';
		unset($criteria);

		// Retrieve the relevant tags, using the tag_id list as a criteria
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('tag_id', $tag_ids, 'IN'));
		$criteria->add(new icms_db_criteria_Item('label_type', $label_type));
		$criteria->setSort('title');
		$criteria->setOrder($sort);
		$tags = $sprockets_tag_handler->getObjects($criteria);

		// Build links for display
		if ($tags) {
			echo '<ul>';
			foreach ($tags as $tag) {
				echo '<li>';
				echo '<a href="' . ICMS_URL . '/modules/' . $module_directory_name . '/' . $item_name 
						. '.php?tag_id=' .  $tag->getVar('tag_id') .  '">' . $tag->getVar('title', 'e') . '</a>';
				echo '</li>';			
			}
			echo '</ul>';
		}
	}
	else if ($label_type == 1 && $module_directory_name) // Retrieve CATEGORY TREE
	{
		include_once ICMS_ROOT_PATH . '/modules/sprockets/include/angry_tree.php';

		$categoryTree = $parentCategories = '';
		$criteria = new icms_db_criteria_Compo();
		$criteria->add(new icms_db_criteria_Item('label_type', $label_type));
		$criteria->add(new icms_db_criteria_Item('mid', $module_id));
		$criteria->setSort('title');
		$criteria->setOrder($sort);
		$tags = $sprockets_tag_handler->getObjects($criteria);
		$categoryTree = new IcmsPersistableTree(&$tags, 'tag_id', 'parent_id', $rootId = null);

		// Generate a representation of the tree for display
		$viewTree = $categoryTree->makeSelBox("Categories", 'title', $prefix='-', $selected='', $addEmptyOption = FALSE, $key=0);
		unset($viewTree[0]);	
		foreach ($viewTree as $key => $value) {
			echo '<a href="' . ICMS_URL . '/modules/' . $module_directory_name . '/' . $item_name
					. '.php?tag_id=' . $key . '&amp;label_type=1">' . $value . '</a><br />';
		}
	}
} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}