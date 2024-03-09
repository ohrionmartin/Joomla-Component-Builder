<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace VDM\Component\Componentbuilder\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Component\ComponentHelper;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Joomlaplugins Form Field class for the Componentbuilder component
 *
 * @since  1.6
 */
class JoomlapluginsField extends ListField
{
	/**
	 * The joomlaplugins field type.
	 *
	 * @var        string
	 */
	public $type = 'Joomlaplugins';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// Get the user object.
		$user = Factory::getApplication()->getIdentity();
		// Get the databse object.
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.id','a.system_name','a.name','b.name','c.name'),array('id','plugin_system_name','name','class_extends_name','joomla_plugin_group_name')));
		$query->from($db->quoteName('#__componentbuilder_joomla_plugin', 'a'));
		$query->join('LEFT', $db->quoteName('#__componentbuilder_class_extends', 'b') . ' ON (' . $db->quoteName('a.class_extends') . ' = ' . $db->quoteName('b.id') . ')');
		$query->join('LEFT', $db->quoteName('#__componentbuilder_joomla_plugin_group', 'c') . ' ON (' . $db->quoteName('a.joomla_plugin_group') . ' = ' . $db->quoteName('c.id') . ')');
		$query->where($db->quoteName('a.published') . ' >= 1');
		$query->order('a.system_name ASC');
		// Implement View Level Access (if set in table)
		if (!$user->authorise('core.options', 'com_componentbuilder'))
		{
			$columns = $db->getTableColumns('#__componentbuilder_joomla_plugin');
			if(isset($columns['access']))
			{
				$groups = implode(',', $user->getAuthorisedViewLevels());
				$query->where('a.access IN (' . $groups . ')');
			}
		}
		$db->setQuery((string)$query);
		$items = $db->loadObjectList();
		$options = array();
		if ($items)
		{
			$options[] = Html::_('select.option', '', 'Select a plugin');
			foreach($items as $item)
			{
				// set a full class name
				$options[] = Html::_('select.option', $item->id, '( ' . $item->plugin_system_name . ' ) class Plg' . ucfirst($item->joomla_plugin_group_name) . $item->name . ' extends ' . $item->class_extends_name);
			}
		}
		return $options;
	}
}
