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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Dynamicgets Form Field class for the Componentbuilder component
 */
class JFormFieldDynamicgets extends JFormFieldList
{
	/**
	 * The dynamicgets field type.
	 *
	 * @var        string
	 */
	public $type = 'dynamicgets';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return    array    An array of Html options.
	 */
	protected function getOptions()
	{
		// Get the databse object.
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.id','a.name','a.gettype'),array('id','dynamic_get_name','type')));
		$query->from($db->quoteName('#__componentbuilder_dynamic_get', 'a'));
		$query->where($db->quoteName('a.published') . ' >= 1');
		$query->order('a.name ASC');
		$db->setQuery((string)$query);
		$items = $db->loadObjectList();
		$options = [];
		if ($items)
		{
			$model = ComponentbuilderHelper::getModel('dynamic_gets');
			if ($this->multiple === false)
			{
				$options[] = Html::_('select.option', '', Text::_('COM_COMPONENTBUILDER_SELECT_AN_OPTION'));
			}
			foreach($items as $item)
			{
				$type = $model->selectionTranslation($item->type, 'gettype');
				$options[] = Html::_('select.option', $item->id, $item->dynamic_get_name . ' (' . Text::_($type) . ')');
			}
		}
		return $options;
	}
}
