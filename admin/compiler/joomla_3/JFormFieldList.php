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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * ###Type### Form Field class for the ###Component### component
 */
class ###JPREFIX###FormField###Type### extends JFormFieldList
{
	/**
	 * The ###type### field type.
	 *
	 * @var        string
	 */
	public $type = '###type###';###ADD_BUTTON###

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return    array    An array of Html options.
	 */
	protected function getOptions()
	{
		###JFORM_GETOPTIONS_PHP###
	}
}
