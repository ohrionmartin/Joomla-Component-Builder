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
 * Componentadminviews Form Field class for the Componentbuilder component
 *
 * @since  1.6
 */
class ComponentadminviewsField extends ListField
{
	/**
	 * The componentadminviews field type.
	 *
	 * @var        string
	 */
	public $type = 'Componentadminviews';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// load the db opbject
		$db = Factory::getDBO();		
		// get the input from url
		$jinput = Factory::getApplication()->input;
		// get the id
		$ID = $jinput->getInt('id', 0);
		// rest the fields ids
		$viewids = array();
		if (is_numeric($ID) && $ID >= 1)
		{
			// get the joomla component ID
			$joomlacomponent = ComponentbuilderHelper::getVar('component_mysql_tweaks', (int) $ID, 'id', 'joomla_component');
		}
		else
		{
			// get the joomla component ID
			$joomlacomponent = $jinput->getInt('refid', 0);
		}
		if (is_numeric($joomlacomponent) && $joomlacomponent >= 1)
		{
			// get all the admin views linked to the joomla component
			if ($addAdminViews = ComponentbuilderHelper::getVar('component_admin_views', (int) $joomlacomponent, 'joomla_component', 'addadmin_views'))
			{
				if (ComponentbuilderHelper::checkJson($addAdminViews))
				{
					$addAdminViews = json_decode($addAdminViews, true);
					if (ComponentbuilderHelper::checkArray($addAdminViews))
					{
						foreach($addAdminViews as $addAdminView)
						{
							if (isset($addAdminView['adminview']))
							{
								$viewids[] = (int) $addAdminView['adminview'];
							}
						}
					}
				}
			}
		}
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.id','a.system_name'),array('id','name')));
		$query->from($db->quoteName('#__componentbuilder_admin_view', 'a'));
		$query->where($db->quoteName('a.published') . ' >= 1');
		// filter by fields linked
		if (ComponentbuilderHelper::checkArray($viewids))
		{
			// only load these fields
			$query->where($db->quoteName('a.id') . ' IN (' . implode(',', $viewids) . ')');
		}
		$query->order('a.system_name ASC');
		$db->setQuery((string)$query);
		$items = $db->loadObjectList();
		$options = array();
		if ($items)
		{
			$options[] = Html::_('select.option', '', 'Select an option');
			foreach($items as $item)
			{
				$options[] = Html::_('select.option', $item->id, $item->name);
			}
		}
		
		return $options;
	}
}
