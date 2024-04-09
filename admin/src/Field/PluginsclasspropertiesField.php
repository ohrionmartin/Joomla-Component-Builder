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
 * Pluginsclassproperties Form Field class for the Componentbuilder component
 *
 * @since  1.6
 */
class PluginsclasspropertiesField extends ListField
{
	/**
	 * The pluginsclassproperties field type.
	 *
	 * @var        string
	 */
	public $type = 'Pluginsclassproperties';

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
		$query->select($db->quoteName(array('a.id','a.name','a.visibility'),array('id','property_name','visibility')));
		$query->from($db->quoteName('#__componentbuilder_class_property', 'a'));
		$query->where($db->quoteName('a.published') . ' >= 1');
		$query->where($db->quoteName('a.extension_type') . ' = ' . $db->quote('plugins'));
		$query->order('a.name ASC');
		// Implement View Level Access (if set in table)
		if (!$user->authorise('core.options', 'com_componentbuilder'))
		{
			$columns = $db->getTableColumns('#__componentbuilder_class_property');
			if(isset($columns['access']))
			{
				$groups = implode(',', $user->getAuthorisedViewLevels());
				$query->where('a.access IN (' . $groups . ')');
			}
		}
		$db->setQuery((string)$query);
		$items = $db->loadObjectList();
		$options = [];
		if ($items)
		{
			$options[] = Html::_('select.option', '', 'Select a property');
			foreach($items as $item)
			{
				if (!isset($item->visibility))
				{
					continue;
				}

				// we are using this code in more then one field JCB custom_code
				if ('method' === 'property')
				{
					$select = $item->visibility  . ' function ' . $item->property_name . '()';
				}
				else
				{
					$select = $item->visibility  . ' $' . $item->property_name;
				}

				$options[] = Html::_('select.option', $item->id, $select);
			}
		}
		return $options;
	}
}
