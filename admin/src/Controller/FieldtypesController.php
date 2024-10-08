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
use VDM\Joomla\Componentbuilder\Fieldtype\Factory as FieldtypeFactory;
use VDM\Joomla\Utilities\GetHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Fieldtypes Admin Controller
 *
 * @since  1.6
 */
class FieldtypesController extends AdminController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_COMPONENTBUILDER_FIELDTYPES';

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
	public function getModel($name = 'Fieldtype', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}


	/**
	 * Initializes all remote Joomla Field Types and syncs them with the local database.
	 *
	 * This function performs several checks and operations:
	 * 1. It verifies the authenticity of the request to prevent request forgery.
	 * 2. It checks whether the current user has the necessary permissions to initialize the Joomla Field Types.
	 * 3. If the user is authorized, it attempts to initialize the remote Joomla Field Types.
	 * 4. Depending on the result of the initialization operation, it sets the appropriate success or error message.
	 * 5. It redirects the user to a specified URL with the result message and status.
	 *
	 * @return bool True on successful initialization, false on failure.
	 */
	public function initPowers()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// check if user has the right
		$user = Factory::getUser();

		// set default error message
		$message = '<h1>' . Text::_('COM_COMPONENTBUILDER_PERMISSION_DENIED') . '</h1>';
		$message .= '<p>' . Text::_('COM_COMPONENTBUILDER_YOU_DO_NOT_HAVE_PERMISSION_TO_INITIALIZE_JOOMLA_FIELD_TYPES') . '</p>';
		$status = 'error';
		$success = false;

		if($user->authorise('fieldtype.init', 'com_componentbuilder'))
		{
			try {
				if (FieldtypeFactory::_('Joomla.Fieldtype.Remote.Get')->init())
				{
					// set success message
					$message = '<h1>' . Text::_('COM_COMPONENTBUILDER_SUCCESSFULLY_INITIALIZED_ALL_REMOTE_JOOMLA_FIELD_TYPES') . '</h1>';
					$message .= '<p>' . Text::_('COM_COMPONENTBUILDER_THE_LOCAL_DATABASE_JOOMLA_FIELD_TYPES_HAS_SUCCESSFULLY_BEEN_SYNCED_WITH_THE_REMOTE_REPOSITORIES') . '</p>';

					$status = 'success';
					$success = true;
				}
				else
				{
					$message = '<h1>' . Text::_('COM_COMPONENTBUILDER_INITIALIZATION_FAILED') . '</h1>';
					$message .= '<p>' . Text::_('COM_COMPONENTBUILDER_THE_INITIALIZATION_OF_THIS_JOOMLA_FIELD_TYPES_HAS_FAILED') . '</p>';
				}
			} catch (\Exception $e) {
				$message = '<h1>' . Text::_('COM_COMPONENTBUILDER_INITIALIZATION_FAILED') . '</h1>';
				$message .= '<p>' . \htmlspecialchars($e->getMessage()) . '</p>';
			}
		}

		// set redirect
		$redirect_url = Route::_('index.php?option=com_componentbuilder&view=fieldtypes', $success);
		$this->setRedirect($redirect_url, $message, $status);

		return $success;
	}

	/**
	 * Resets the selected Joomla Field Types.
	 *
	 * This function performs several checks and operations:
	 * 1. It verifies the authenticity of the request to prevent request forgery.
	 * 2. It retrieves the IDs of the selected powers from the user input.
	 * 3. It sanitizes the input by converting the IDs to integers.
	 * 4. It checks whether any powers have been selected.
	 * 5. It checks whether the current user has the necessary permissions to reset the selected Joomla Field Types.
	 * 6. If the user is authorized and powers are selected, it attempts to reset the selected Joomla Field Types.
	 * 7. Depending on the result of the reset operation, it sets the appropriate success or error message.
	 * 8. It redirects the user to a specified URL with the result message and status.
	 *
	 * @return bool True on successful reset, false on failure.
	 */
	public function resetPowers()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// get IDS of the selected powers
		$pks = $this->input->post->get('cid', [], 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);

		// check if there is any selections
		if ($pks === [])
		{
			// set error message
			$message = '<h1>'.Text::_('COM_COMPONENTBUILDER_NO_SELECTION_DETECTED').'</h1>';
			$message .= '<p>'.Text::_('COM_COMPONENTBUILDER_PLEASE_FIRST_MAKE_A_SELECTION_FROM_THE_LIST').'</p>';
			// set redirect
			$redirect_url = Route::_('index.php?option=com_componentbuilder&view=fieldtypes', false);
			$this->setRedirect($redirect_url, $message, 'error');
			return false;
		}

		$status = 'error';
		$success = false;

		// check if user has the right
		$user = Factory::getUser();
		if($user->authorise('fieldtype.reset', 'com_componentbuilder'))
		{
			$guids = GetHelper::vars('fieldtype', $pks, 'id', 'guid');

			try {
				if (FieldtypeFactory::_('Joomla.Fieldtype.Remote.Get')->reset($guids))
				{
					// set success message
					$message = '<h1>'.Text::_('COM_COMPONENTBUILDER_SUCCESS').'</h1>';
					$message .= '<p>'.Text::_('COM_COMPONENTBUILDER_THESE_JOOMLA_FIELD_TYPES_HAVE_SUCCESSFULLY_BEEN_RESET').'</p>';
					$status = 'success';
					$success = true;
				}
				else
				{
					$message = '<h1>' . Text::_('COM_COMPONENTBUILDER_RESET_FAILED') . '</h1>';
					$message .= '<p>' . Text::_('COM_COMPONENTBUILDER_THE_RESET_OF_THESE_JOOMLA_FIELD_TYPES_HAS_FAILED') . '</p>';
				}
			} catch (\Exception $e) {
				$message = '<h1>' . Text::_('COM_COMPONENTBUILDER_RESET_FAILED') . '</h1>';
				$message .= '<p>' . \htmlspecialchars($e->getMessage()) . '</p>';
			}

			// set redirect
			$redirect_url = Route::_('index.php?option=com_componentbuilder&view=fieldtypes', $success);
			$this->setRedirect($redirect_url, $message, $status);

			return $success;
		}

		// set redirect
		$redirect_url = Route::_('index.php?option=com_componentbuilder&view=fieldtypes', false);
		$this->setRedirect($redirect_url);
		return $success;
	}

	/**
	 * Pushes the selected Joomla Field Types.
	 *
	 * This function performs several checks and operations:
	 * 1. It verifies the authenticity of the request to prevent request forgery.
	 * 2. It retrieves the IDs of the selected powers from the user input.
	 * 3. It sanitizes the input by converting the IDs to integers.
	 * 4. It checks whether any powers have been selected.
	 * 5. It checks whether the current user has the necessary permissions to push the selected Joomla Field Types.
	 * 6. If the user is authorized and powers are selected, it attempts to push the selected Joomla Field Types.
	 * 7. Depending on the result of the push operation, it sets the appropriate success or error message.
	 * 8. It redirects the user to a specified URL with the result message and status.
	 *
	 * @return bool True on successful push, false on failure.
	 */
	public function pushPowers()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// get IDS of the selected powers
		$pks = $this->input->post->get('cid', [], 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);

		// check if there is any selections
		if ($pks === [])
		{
			// set error message
			$message = '<h1>'.Text::_('COM_COMPONENTBUILDER_NO_SELECTION_DETECTED').'</h1>';
			$message .= '<p>'.Text::_('COM_COMPONENTBUILDER_PLEASE_FIRST_MAKE_A_SELECTION_FROM_THE_LIST').'</p>';
			// set redirect
			$redirect_url = Route::_('index.php?option=com_componentbuilder&view=fieldtypes', false);
			$this->setRedirect($redirect_url, $message, 'error');
			return false;
		}

		$status = 'error';
		$success = false;

		// check if user has the right
		$user = Factory::getUser();
		if($user->authorise('fieldtype.push', 'com_componentbuilder'))
		{
			$guids = GetHelper::vars('fieldtype', $pks, 'id', 'guid');

			try {
				if (FieldtypeFactory::_('Joomla.Fieldtype.Remote.Set')->items($guids))
				{
					// set success message
					$message = '<h1>'.Text::_('COM_COMPONENTBUILDER_SUCCESS').'</h1>';
					$message .= '<p>'.Text::_('COM_COMPONENTBUILDER_THESE_JOOMLA_FIELD_TYPES_HAVE_SUCCESSFULLY_BEEN_PUSHED').'</p>';
					$status = 'success';
					$success = true;
				}
				else
				{
					$message = '<h1>' . Text::_('COM_COMPONENTBUILDER_PUSH_FAILED') . '</h1>';
					$message .= '<p>' . Text::_('COM_COMPONENTBUILDER_THE_PUSH_OF_THESE_JOOMLA_FIELD_TYPES_HAS_FAILED') . '</p>';
				}
			} catch (\Exception $e) {
				$message = '<h1>' . Text::_('COM_COMPONENTBUILDER_PUSH_FAILED') . '</h1>';
				$message .= '<p>' . \htmlspecialchars($e->getMessage()) . '</p>';
			}

			// set redirect
			$redirect_url = Route::_('index.php?option=com_componentbuilder&view=fieldtypes', $success);
			$this->setRedirect($redirect_url, $message, $status);

			return $success;
		}

		// set redirect
		$redirect_url = Route::_('index.php?option=com_componentbuilder&view=fieldtypes', false);
		$this->setRedirect($redirect_url);
		return $success;
	}
}