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
namespace VDM\Component\Componentbuilder\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\StringHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Custom_codes Admin Controller
 *
 * @since  1.6
 */
class Custom_codesController extends AdminController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_COMPONENTBUILDER_CUSTOM_CODES';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Custom_code', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}

	public function exportData()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
		// check if export is allowed for this user.
		$user = Factory::getApplication()->getIdentity();
		if ($user->authorise('custom_code.export', 'com_componentbuilder') && $user->authorise('core.export', 'com_componentbuilder'))
		{
			// Get the input
			$input = Factory::getApplication()->input;
			$pks = $input->post->get('cid', array(), 'array');
			// Sanitize the input
			$pks = ArrayHelper::toInteger($pks);
			// Get the model
			$model = $this->getModel('Custom_codes');
			// get the data to export
			$data = $model->getExportData($pks);
			if (UtilitiesArrayHelper::check($data))
			{
				// now set the data to the spreadsheet
				$date = Factory::getDate();
				ComponentbuilderHelper::xls($data,'Custom_codes_'.$date->format('jS_F_Y'),'Custom codes exported ('.$date->format('jS F, Y').')','custom codes');
			}
		}
		// Redirect to the list screen with error.
		$message = Text::_('COM_COMPONENTBUILDER_EXPORT_FAILED');
		$this->setRedirect(Route::_('index.php?option=com_componentbuilder&view=custom_codes', false), $message, 'error');
		return;
	}


	public function importData()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
		// check if import is allowed for this user.
		$user = Factory::getApplication()->getIdentity();
		if ($user->authorise('custom_code.import', 'com_componentbuilder') && $user->authorise('core.import', 'com_componentbuilder'))
		{
			// Get the import model
			$model = $this->getModel('Custom_codes');
			// get the headers to import
			$headers = $model->getExImPortHeaders();
			if (ObjectHelper::check($headers))
			{
				// Load headers to session.
				$session = Factory::getSession();
				$headers = json_encode($headers);
				$session->set('custom_code_VDM_IMPORTHEADERS', $headers);
				$session->set('backto_VDM_IMPORT', 'custom_codes');
				$session->set('dataType_VDM_IMPORTINTO', 'custom_code');
				// Redirect to import view.
				$message = Text::_('COM_COMPONENTBUILDER_IMPORT_SELECT_FILE_FOR_CUSTOM_CODES');
				$this->setRedirect(Route::_('index.php?option=com_componentbuilder&view=import', false), $message);
				return;
			}
		}
		// Redirect to the list screen with error.
		$message = Text::_('COM_COMPONENTBUILDER_IMPORT_FAILED');
		$this->setRedirect(Route::_('index.php?option=com_componentbuilder&view=custom_codes', false), $message, 'error');
		return;
	}


	/**
	 * Run the Expansion
	 *
	 * @return  void
	 */
	public function runExpansion()
	{
		// Check for request forgeries
		Session::checkToken() or \jexit(Text::_('JINVALID_TOKEN'));
		// check if user has the right
		$user = Factory::getUser();
		// set page redirect
		$redirect_url = \JRoute::_('index.php?option=com_componentbuilder&view=custom_codes', false);
		// set massage
		$message = Text::_('COM_COMPONENTBUILDER_YOU_DO_NOT_HAVE_PERMISSION_TO_RUN_THE_EXPANSION_MODULE');
		// check if this user has the right to run expansion
		if($user->authorise('custom_codes.run_expansion', 'com_componentbuilder'))
		{
			// set massage
			$message = Text::_('COM_COMPONENTBUILDER_EXPANSION_FAILED_PLEASE_CHECK_YOUR_SETTINGS_IN_THE_GLOBAL_OPTIONS_OF_JCB_UNDER_THE_DEVELOPMENT_METHOD_TAB');
			// run expansion via API
			$result = ComponentbuilderHelper::getFileContents(\JUri::root() . 'index.php?option=com_componentbuilder&task=api.expand');
			// is there a message returned
			if (!is_numeric($result) && StringHelper::check($result))
			{
				$this->setRedirect($redirect_url, $result);
				return true;
			}
			elseif (is_numeric($result) && 1 == $result)
			{
				$message = Text::_('COM_COMPONENTBUILDER_BTHE_EXPANSION_WAS_SUCCESSFULLYB_TO_SEE_MORE_INFORMATION_CHANGE_THE_BRETURN_OPTIONS_FOR_BUILDB_TO_BDISPLAY_MESSAGEB_IN_THE_GLOBAL_OPTIONS_OF_JCB_UNDER_THE_DEVELOPMENT_METHOD_TABB');
				$this->setRedirect($redirect_url, $message, 'message');
				return true;
			}
		}
		$this->setRedirect($redirect_url, $message, 'error');
		return false;
	}

}