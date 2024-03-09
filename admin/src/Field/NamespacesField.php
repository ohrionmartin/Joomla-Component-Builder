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
 * Namespaces Form Field class for the Componentbuilder component
 *
 * @since  1.6
 */
class NamespacesField extends ListField
{
	/**
	 * The namespaces field type.
	 *
	 * @var        string
	 */
	public $type = 'Namespaces';

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
		$query->select($db->quoteName(array('a.guid','a.name','a.namespace','a.type','a.power_version'),array('guid','use_name','namespace','type','version')));
		$query->from($db->quoteName('#__componentbuilder_power', 'a'));
		$query->where($db->quoteName('a.published') . ' >= 1');
		$query->order('a.name ASC');
		$query->order('a.type ASC');
		// Implement View Level Access (if set in table)
		if (!$user->authorise('core.options', 'com_componentbuilder'))
		{
			$columns = $db->getTableColumns('#__componentbuilder_power');
			if(isset($columns['access']))
			{
				$groups = implode(',', $user->getAuthorisedViewLevels());
				$query->where('a.access IN (' . $groups . ')');
			}
		}
		// get the input
		$jinput = Factory::getApplication()->input;
		// get the id
		$power_id = $jinput->getInt('id', 0);
		// if we have an id we remove all classes of the same namespace and name
		if ($power_id > 0 && ($exclude_powers = ComponentbuilderHelper::excludePowers($power_id)) !== false)
		{
			$query->where('a.id NOT IN (' . implode(',', $exclude_powers) . ')');
		}
		$db->setQuery((string)$query);
		$items = $db->loadObjectList();
		$options = array();
		// if none was found, we add this to set an alternative to set custom
		if (!$items)
		{
			$options[] = Html::_('select.option', '', Text::_('COM_COMPONENTBUILDER_NONE_FOUND'));
		}
		if ($items)
		{
			if ($this->multiple === false)
			{
				$options[] = Html::_('select.option', '', Text::_('COM_COMPONENTBUILDER_SELECT_AN_OPTION'));
			}
			foreach($items as $item)
			{
				$options[] = Html::_('select.option', $item->guid, str_replace('.','\\', $item->namespace) . ' [' . $item->use_name . '] (v' . $item->version . ' - ' . $item->type . ')');
			}
		}
		return $options;

	}
}
