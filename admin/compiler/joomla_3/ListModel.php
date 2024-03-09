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
?>
###BOM###

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

###ADMIN_VIEWS_MODEL_HEADER###

/**
 * ###Views### List Model
 */
class ###Component###Model###Views### extends ListModel
{
	public function __construct($config = [])
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				###FILTER_FIELDS###
			);
		}

		parent::__construct($config);
	}###ADMIN_CUSTOM_BUTTONS_METHOD_LIST###

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}###POPULATESTATE###

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{###LICENSE_LOCKED_CHECK######CHECKINCALL###
		// load parent items
		$items = parent::getItems();###GET_ITEMS_METHOD_STRING_FIX######SELECTIONTRANSLATIONFIX######GET_ITEMS_METHOD_AFTER_ALL###

		// return items
		return $items;
	}###SELECTIONTRANSLATIONFIXFUNC###

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return    string    An SQL query
	 */
	protected function getListQuery()
	{###LICENSE_LOCKED_CHECK###
		###LISTQUERY###
	}###MODELEXPORTMETHOD######LICENSE_LOCKED_SET_BOOL###

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @return  string  A store id.
	 *
	 */
	protected function getStoreId($id = '')
	{
		###STOREDID###

		return parent::getStoreId($id);
	}###AUTOCHECKIN###
}
