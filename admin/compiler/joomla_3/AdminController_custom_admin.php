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

###CUSTOM_ADMIN_VIEWS_CONTROLLER_HEADER###

/**
 * ###SViews### Admin Controller
 */
class ###Component###Controller###SViews### extends AdminController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_###COMPONENT###_###SVIEWS###';

	/**
	 * Proxy for getModel.
	 * @since    2.5
	 */
	public function getModel($name = '###SView###', $prefix = '###Component###Model', $config = [])
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	public function dashboard()
	{
		$this->setRedirect(Route::_('index.php?option=com_###component###', false));
		return;
	}###CUSTOM_ADMIN_CUSTOM_BUTTONS_CONTROLLER###
}
