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
 * Fieldsfilterindexes Form Field class for the Componentbuilder component
 *
 * @since  1.6
 */
class FieldsfilterindexesField extends ListField
{
	/**
	 * The fieldsfilterindexes field type.
	 *
	 * @var        string
	 */
	public $type = 'Fieldsfilterindexes';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// Get a db connection.
		$db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select the text.
		$query->select($db->quoteName('indexes'));
		$query->from($db->quoteName('#__componentbuilder_field'));
		$query->order($db->quoteName('indexes') . ' ASC');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		$_results = $db->loadColumn();
		$_filter = [];
		$_filter[] = Html::_('select.option', '', '- ' . Text::_('COM_COMPONENTBUILDER_FILTER_SELECT_INDEXES_TYPE') . ' -');

		if ($_results)
		{
			// get fieldsmodel
			$_model = ComponentbuilderHelper::getModel('fields');
			$_results = array_unique($_results);
			foreach ($_results as $indexes)
			{
				// Translate the indexes selection
				$_text = $_model->selectionTranslation($indexes,'indexes');
				// Now add the indexes and its text to the options array
				$_filter[] = Html::_('select.option', $indexes, Text::_($_text));
			}
		}
		return $_filter;
	}
}
