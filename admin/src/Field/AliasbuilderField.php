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
use VDM\Joomla\Utilities\JsonHelper;
use VDM\Joomla\Utilities\ArrayHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Aliasbuilder Form Field class for the Componentbuilder component
 *
 * @since  1.6
 */
class AliasbuilderField extends ListField
{
	/**
	 * The aliasbuilder field type.
	 *
	 * @var        string
	 */
	public $type = 'Aliasbuilder';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// load the db object
		$db = Factory::getDBO();		
		// get the input from url
		$jinput = Factory::getApplication()->input;
		// get the id
		$adminView = $jinput->getInt('id', 0);
		// rest the fields ids
		$fieldIds = array();
		if (is_numeric($adminView) && $adminView >= 1)
		{
			// get all the fields linked to the admin view
			if ($addFields = ComponentbuilderHelper::getVar('admin_fields', (int) $adminView, 'admin_view', 'addfields'))
			{
				if (JsonHelper::check($addFields))
				{
					$addFields = json_decode($addFields, true);
					if (ArrayHelper::check($addFields))
					{
						foreach($addFields as $addField)
						{
							if (isset($addField['field']) && (!isset($addField['alias']) || 1 != $addField['alias']))
							{
								$fieldIds[] = (int) $addField['field'];
							}
						}
					}
				}
			}
		}
		// filter by fields linked
		if (ArrayHelper::check($fieldIds))
		{
			// get list of field types that does not work in list views (note, spacer)
			$spacers = ComponentbuilderHelper::getSpacerIds();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('a.id','a.name','b.name'),array('id','name','type')));
			$query->from($db->quoteName('#__componentbuilder_field', 'a'));
			$query->join('LEFT', $db->quoteName('#__componentbuilder_fieldtype', 'b') . ' ON (' . $db->quoteName('a.fieldtype') . ' = ' . $db->quoteName('b.id') . ')');
			$query->where($db->quoteName('a.published') . ' >= 1');
			// only load these fields
			$query->where($db->quoteName('a.id') . ' IN (' . implode(',', $fieldIds) . ')');
			// none of these field types
			if (ArrayHelper::check($spacers))
			{
				$query->where($db->quoteName('a.fieldtype') . ' NOT IN (' . implode(',', $spacers) . ')');
			}
			$query->order('a.name ASC');
			$db->setQuery((string)$query);
			$items = $db->loadObjectList();
			$options = array();
			if ($items)
			{
				foreach($items as $item)
				{
					$options[] = Html::_('select.option', $item->id, $item->name . ' [' . $item->type . ']');
				}
				return $options;
			}
		}
		return array(Html::_('select.option', '', Text::_('COM_COMPONENTBUILDER_ADD_MORE_FIELDS_TO_THIS_ADMIN_VIEW')));
	}
}
