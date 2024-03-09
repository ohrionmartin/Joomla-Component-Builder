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
 * Dbtables Form Field class for the Componentbuilder component
 *
 * @since  1.6
 */
class DbtablesField extends ListField
{
	/**
	 * The dbtables field type.
	 *
	 * @var        string
	 */
	public $type = 'Dbtables';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// get db object
		$db = Factory::getDBO();
		// get all tables
		$tables= $db->getTableList();
		// get config
		$config = Factory::getConfig();
		$dbprefix = \version_compare(JVERSION,'3.0','lt') ? $config->getValue('config.dbprefix') : $config->get('dbprefix');
		$options = array();
		$options[] = Html::_('select.option', '', 'Select an option');
		for ($i=0; $i < count($tables); $i++)
		{
			//only tables with primary key
			$db->setQuery('SHOW FIELDS FROM `'.$tables[$i].'` WHERE LOWER( `Key` ) = \'pri\'');
			if ($db->loadResult())
			{
				$key = $i+1;
				$options[$key] = new \stdClass;
				$options[$key]->value = str_replace($dbprefix, '', $tables[$i]);
				$options[$key]->text = $tables[$i];
			}
		}
		return $options;
	}
}
