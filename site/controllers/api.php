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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\StringHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Componentbuilder Api Form Controller
 */
class ComponentbuilderControllerApi extends FormController
{
	/**
	 * Current or most recently performed task.
	 *
	 * @var    string
	 * @since  12.2
	 * @note   Replaces _task.
	 */
	protected $task;

	public function __construct($config = [])
	{
		$this->view_list = ''; // safeguard for setting the return view listing to the default site view.
		parent::__construct($config);
	}

	/**
	 * The methods that are allowed to be called
	 * 
	 * @var     array
	 */
	protected $allowedMethods = array('compileInstall', 'translate');

	/**
	 * The local methods that should be triggered
	 * 
	 * @var     array
	 */
	protected $localMethodTrigger = array('compileInstall' => '_autoloader');

	/**
	 * Run the Translator
	 *
	 * @return  mix
	 */
	public function translator()
	{
		// get params first
		if (!isset($this->params) || !ObjectHelper::check($this->params))
		{
			$this->params = ComponentHelper::getParams('com_componentbuilder');
		}
		// get model
		$model = $this->getModel('api');
		// check if user has the right
		$user = $this->getApiUser();
		// get components that have translation tools
		if ($user->authorise('core.admin', 'com_componentbuilder') && ($components = $model->getTranslationLinkedComponents()) !== false)
		{
			// the message package
			$message = array();
			// make sure to not unlock
			$unlock = false;
			// get messages
			$callback = function($messages) use (&$message, &$unlock) {
				// unlock messages if needed
				if ($unlock) {
					$messages = ComponentbuilderHelper::unlock($messages);
				}
				// check if we have any messages
				if (UtilitiesArrayHelper::check($messages)) {
					$message[] = implode("<br />\n", $messages);
				} else {
					// var_dump($messages); // error debug message
				}
			};
			// we have two options, doing them one at a time, or using curl to do them somewhat asynchronously 
			if (count ( (array) $components) > 1 && function_exists('curl_version'))
			{
				// line up the translations
				foreach ($components as $component)
				{
					ComponentbuilderHelper::setWorker($component, 'translate');
				}
				// make sure to unlock
				$unlock = true;
				// run workers
				ComponentbuilderHelper::runWorker('translate', 1, $callback);
			}
			else
			{
				foreach ($components as $component)
				{
					$model->translate($component);
				}
				// check if we have any messages
				$callback($model->messages);
			}
			// return messages if found
			if (UtilitiesArrayHelper::check($message))
			{
				echo implode("<br />\n", $message);
			}
			else
			{
				echo 1;
			}
			// clear session
			Factory::getApplication()->getSession()->destroy();
			jexit();
		}
		// clear session
		Factory::getApplication()->getSession()->destroy();
		// return bool
		echo 0;
		jexit();
	}

	/**
	 * Run the Expansion
	 *
	 * @return  mix
	 */
	public function expand()
	{
		// get params first
		if (!isset($this->params) || !ObjectHelper::check($this->params))
		{
			$this->params = ComponentHelper::getParams('com_componentbuilder');
		}
		// check if expansion is enabled
		$method = $this->params->get('development_method', 1);
		// check what kind of return values show we give
		$returnOptionsBuild = $this->params->get('return_options_build', 2);
		if (2 == $method)
		{
			// get expansion components
			$expansion = $this->params->get('expansion', null);
			// check if they are set
			if (ObjectHelper::check($expansion))
			{
				// check if user has the right
				$user = $this->getApiUser();
				// the message package
				$message = array();
				if ($user->authorise('core.admin', 'com_componentbuilder'))
				{
					// make sure to not unlock
					$unlock = false;
					// get messages
					$callback = function($messages) use (&$message, &$unlock) {
						// unlock messages if needed
						if ($unlock) {
							$messages = ComponentbuilderHelper::unlock($messages);
						}
						// check if we have any messages
						if (UtilitiesArrayHelper::check($messages)) {
							$message[] = implode("<br />\n", $messages);
						} else {
							// var_dump($messages); // error debug message
						}
					};
					// we have two options, doing them one at a time, or using curl to do them somewhat asynchronously 
					if (count ( (array) $expansion) > 1 && function_exists('curl_version'))
					{
						// set workers
						foreach ($expansion as $component)
						{
							ComponentbuilderHelper::setWorker($component, 'compileInstall');
						}
						// make sure to unlock
						$unlock = true;
						// run workers
						ComponentbuilderHelper::runWorker('compileInstall', 1, $callback);
					}
					else
					{
						// get model
						$model = $this->getModel('api');
						// load the compiler
						$this->_autoloader();
						// set workers
						foreach ($expansion as $component)
						{
							// compile and install
							$model->compileInstall($component);
						}
						// check if we have any messages
						$callback($model->messages);
					}
					// return messages if found
					if (1== $returnOptionsBuild && UtilitiesArrayHelper::check($message))
					{
						echo implode("<br />\n", $message);
					}
					else
					{
						echo 1;
					}
					// clear session
					Factory::getApplication()->getSession()->destroy();
					jexit();
				}
				// check if message is to be returned
				if (1== $returnOptionsBuild)
				{
					// clear session
					Factory::getApplication()->getSession()->destroy();
					jexit('Access Denied!');
				}
			}
		}
		// clear session
		Factory::getApplication()->getSession()->destroy();
		// check if message is to be returned
		if (1== $returnOptionsBuild)
		{
			jexit('Expansion Disabled! Expansion can be enabled by your system administrator in the global Options of JCB under the Development Method tab.');
		}
		// return bool
		echo 0;
		jexit();
	}

	/**
	 * Get API User
	 *
	 * @return  object
	 */
	protected function getApiUser()
	{
		// return user object
		return Factory::getUser($this->params->get('api', 0, 'INT'));
	}

	/**
	 * Load the needed script
	 *
	 * @return  void
	 */
	protected function _autoloader()
	{
		// include component compiler
		require_once JPATH_ADMINISTRATOR.'/components/com_componentbuilder/helpers/compiler.php';
	}

	public function backup()
	{
		// get params first
		if (!isset($this->params) || !ObjectHelper::check($this->params))
		{
			$this->params = ComponentHelper::getParams('com_componentbuilder');
		}
		// Get the model
		$model = componentbuilderHelper::getModel('joomla_components', JPATH_ADMINISTRATOR . '/components/com_componentbuilder');
		// set user
		$model->user = $this->getApiUser();
		// make sure to set active type (adding this script from custom code :)
		$model->activeType = 'backup';
		// check if export is allowed for this user. (we need this sorry)
		if ($model->user->authorise('joomla_component.export_jcb_packages', 'com_componentbuilder') && $model->user->authorise('core.export', 'com_componentbuilder'))
		{
			// get all component IDs to backup
			$pks = componentbuilderHelper::getComponentIDs();
			// set auto loader
			ComponentbuilderHelper::autoLoader('smart');
			// manual backup message
			$backupNotice = [];
			// get the data to export
			if (UtilitiesArrayHelper::check($pks) && $model->getSmartExport($pks))
			{
				$backupNotice[] = Text::_('COM_COMPONENTBUILDER_BACKUP_WAS_DONE_SUCCESSFULLY');
				$backupNoticeStatus = 'Success';
				// set the key string
				if (StringHelper::check($model->key) && strlen($model->key) == 32)
				{
					$textNotice = array();
					$keyNotice = '<h1>' . Text::sprintf('COM_COMPONENTBUILDER_THE_PACKAGE_KEY_IS_CODESCODE', $model->key) . '</h1>';
					$keyNotice .= '<p>' . Text::_('COM_COMPONENTBUILDER_YOUR_DATA_IS_ENCRYPTED_WITH_A_AES_TWO_HUNDRED_AND_FIFTY_SIX_BIT_ENCRYPTION_USING_THE_ABOVE_THIRTY_TWO_CHARACTER_KEY') . '</p>';
					$textNotice[] = Text::sprintf('COM_COMPONENTBUILDER_THE_PACKAGE_KEY_IS_S', $model->key);
					// set the package owner info
					if ((isset($model->info['getKeyFrom']['company']) && StringHelper::check($model->info['getKeyFrom']['company'])) || (isset($model->info['getKeyFrom']['owner']) && StringHelper::check($model->info['getKeyFrom']['owner'])))
					{
						$ownerDetails = '<h2>' . Text::_('COM_COMPONENTBUILDER_PACKAGE_OWNER_DETAILS') . '</h2>';
						$textNotice[] = '# ' . Text::_('COM_COMPONENTBUILDER_PACKAGE_OWNER_DETAILS');
						$ownerDetails .= '<ul>';
						if (isset($model->info['getKeyFrom']['company']) && StringHelper::check($model->info['getKeyFrom']['company']))
						{
							$ownerDetails .= '<li>' . Text::sprintf('COM_COMPONENTBUILDER_EMCOMPANYEM_BSB', $model->info['getKeyFrom']['company']) . '</li>';
							$textNotice[] = '- ' . Text::sprintf('COM_COMPONENTBUILDER_COMPANY_S', $model->info['getKeyFrom']['company']);
						}
						// add value only if set
						if (isset($model->info['getKeyFrom']['owner']) && StringHelper::check($model->info['getKeyFrom']['owner']))
						{
							$ownerDetails .= '<li>' . Text::sprintf('COM_COMPONENTBUILDER_EMOWNEREM_BSB', $model->info['getKeyFrom']['owner']) . '</li>';
							$textNotice[] = '- ' . Text::sprintf('COM_COMPONENTBUILDER_OWNER_S', $model->info['getKeyFrom']['owner']);
						}
						// add value only if set
						if (isset($model->info['getKeyFrom']['website']) && StringHelper::check($model->info['getKeyFrom']['website']))
						{
							$ownerDetails .= '<li>' . Text::sprintf('COM_COMPONENTBUILDER_EMWEBSITEEM_BSB', $model->info['getKeyFrom']['website']) . '</li>';
							$textNotice[] = '- ' . Text::sprintf('COM_COMPONENTBUILDER_WEBSITE_S', $model->info['getKeyFrom']['website']);
						}
						// add value only if set
						if (isset($model->info['getKeyFrom']['email']) && StringHelper::check($model->info['getKeyFrom']['email']))
						{
							$ownerDetails .= '<li>' . Text::sprintf('COM_COMPONENTBUILDER_EMEMAILEM_BSB', $model->info['getKeyFrom']['email']) . '</li>';
							$textNotice[] = '- ' . Text::sprintf('COM_COMPONENTBUILDER_EMAIL_S', $model->info['getKeyFrom']['email']);
						}
						// add value only if set
						if (isset($model->info['getKeyFrom']['license']) && StringHelper::check($model->info['getKeyFrom']['license']))
						{
							$ownerDetails .= '<li>' . Text::sprintf('COM_COMPONENTBUILDER_EMLICENSEEM_BSB', $model->info['getKeyFrom']['license']) . '</li>';
							$textNotice[] = '- ' . Text::sprintf('COM_COMPONENTBUILDER_LICENSE_S', $model->info['getKeyFrom']['license']);
						}
						// add value only if set
						if (isset($model->info['getKeyFrom']['copyright']) && StringHelper::check($model->info['getKeyFrom']['copyright']))
						{
							$ownerDetails .= '<li>' . Text::sprintf('COM_COMPONENTBUILDER_EMCOPYRIGHTEM_BSB', $model->info['getKeyFrom']['copyright']) . '</li>';
							$textNotice[] = '- ' . Text::sprintf('COM_COMPONENTBUILDER_COPYRIGHT_S', $model->info['getKeyFrom']['copyright']);
						}							
						$ownerDetails .= '</ul>';
						$backupNotice[] = Text::_('COM_COMPONENTBUILDER_OWNER_DETAILS_WAS_SET');
					}
					else
					{
						$ownerDetails = '<h2>' . Text::_('COM_COMPONENTBUILDER_PACKAGE_OWNER_NOT_SET') . '</h2>';
						$textNotice[] = '# ' . Text::_('COM_COMPONENTBUILDER_PACKAGE_OWNER_DETAILS');
						$ownerDetails .= Text::_('COM_COMPONENTBUILDER_TO_CHANGE_THE_PACKAGE_OWNER_DEFAULTS_OPEN_THE_BJCB_GLOBAL_OPTIONSB_GO_TO_THE_BCOMPANYB_TAB_AND_ADD_THE_CORRECT_COMPANY_DETAILS_THERE') . '<br />';
						$textNotice[] = Text::_('COM_COMPONENTBUILDER_TO_CHANGE_THE_PACKAGE_OWNER_DEFAULTS_OPEN_THE_JCB_GLOBAL_OPTIONS_GO_TO_THE_COMPANY_TAB_AND_ADD_THE_CORRECT_COMPANY_DETAILS_THERE');
						$ownerDetails .= '<h3>' . Text::_('COM_COMPONENTBUILDER_YOU_SHOULD_ADD_THE_CORRECT_OWNER_DETAILS') . '</h3>';
						$textNotice[] = '## ' .  Text::_('COM_COMPONENTBUILDER_YOU_SHOULD_ADD_THE_CORRECT_OWNER_DETAILS');
						$ownerDetails .= Text::_('COM_COMPONENTBUILDER_SINCE_THE_OWNER_DETAILS_ARE_DISPLAYED_DURING_BIMPORT_PROCESSB_BEFORE_ADDING_THE_KEY_THIS_WAY_IF_THE_USERDEV_BDOES_NOTB_HAVE_THE_KEY_THEY_CAN_SEE_BWHERE_TO_GET_ITB') . '<br />';
						$textNotice[] = Text::_('COM_COMPONENTBUILDER_SINCE_THE_OWNER_DETAILS_ARE_DISPLAYED_DURING_IMPORT_PROCESS_BEFORE_ADDING_THE_KEY_THIS_WAY_IF_THE_USERDEV_DOES_NOT_HAVE_THE_KEY_THEY_CAN_SEE_WHERE_TO_GET_IT');
						$backupNotice[] = Text::_('COM_COMPONENTBUILDER_CHECK_YOUR_OWNER_DETAILS_IT_HAS_NOT_BEEN_SET_OPEN_THE_JCB_GLOBAL_OPTIONS_GO_TO_THE_COMPANY_TAB_AND_ADD_THE_CORRECT_COMPANY_DETAILS_THERE');
					}
				}
				else
				{
					$keyNotice = '<h1>' . Text::_('COM_COMPONENTBUILDER_THIS_PACKAGE_HAS_NO_KEY') . '</h1>';
					$textNotice[] = '# ' . Text::_('COM_COMPONENTBUILDER_THIS_PACKAGE_HAS_NO_KEY');
					$ownerDetails = Text::_('COM_COMPONENTBUILDER_THAT_MEANS_ANYONE_WHO_HAS_THIS_PACKAGE_CAN_INSTALL_IT_INTO_JCB_TO_ADD_AN_EXPORT_KEY_SIMPLY_OPEN_THE_COMPONENT_GO_TO_THE_TAB_CALLED_BSETTINGSB_BOTTOM_RIGHT_THERE_IS_A_FIELD_CALLED_BEXPORT_KEYB') . '<br />';
					$textNotice[] = Text::_('COM_COMPONENTBUILDER_THAT_MEANS_ANYONE_WHO_HAS_THIS_PACKAGE_CAN_INSTALL_IT_INTO_JCB_TO_ADD_AN_EXPORT_KEY_SIMPLY_OPEN_THE_COMPONENT_GO_TO_THE_TAB_CALLED_SETTINGS_BOTTOM_RIGHT_THERE_IS_A_FIELD_CALLED_EXPORT_KEY');
					$backupNotice[] = Text::_('COM_COMPONENTBUILDER_NO_KEYS_WERE_FOUND_TO_ADD_AN_EXPORT_KEY_SIMPLY_OPEN_THE_COMPONENT_GO_TO_THE_TAB_CALLED_SETTINGS_BOTTOM_RIGHT_THERE_IS_A_FIELD_CALLED_EXPORT_KEY');
				}
				// get email
				if ($email = $this->params->get('backup_email', null))
				{
					// plain text
					$plainText = implode("\n", $textNotice);
					// set hash to track changes
					$hashTracker = md5($plainText);
					if (ComponentbuilderHelper::newHash($hashTracker))
					{
						// Build final massage.
						$message = $keyNotice . $ownerDetails . '<br /><small>HASH: ' . $hashTracker . '</small>';
						// set the subject
						$subject = Text::_('COM_COMPONENTBUILDER_JOOMLA_COMPONENT_BUILDER_BACKUP_KEY');
						// email the message
						componentbuilderEmail::send($email, $subject, componentbuilderEmail::setTableBody($message, $subject), $plainText, 1);
						$backupNotice[] = Text::_('COM_COMPONENTBUILDER_EMAIL_WITH_THE_NEW_KEY_WAS_SENT');
					}
					else
					{
						$backupNotice[] = Text::_('COM_COMPONENTBUILDER_KEY_HAS_NOT_CHANGED');
					}
				}
			}
			else
			{
				$backupNotice[] = Text::_('COM_COMPONENTBUILDER_BACKUP_FAILED_PLEASE_TRY_AGAIN_IF_THE_ERROR_CONTINUE_PLEASE_CONTACT_YOUR_SYSTEM_ADMINISTRATOR');
				$backupNoticeStatus = 'Error';
				if (StringHelper::check($model->packagePath))
				{
					// clear all if not successful
					ComponentbuilderHelper::removeFolder($model->packagePath);
				}
				if (StringHelper::check($model->zipPath))
				{
					// clear all if not successful
					File::delete($model->zipPath);
				}				
			}
			// quite only if auto backup (adding this script from custom code :)
			if ('backup' === $model->activeType)
			{
				echo "# " . $backupNoticeStatus . "\n" .implode("\n", $backupNotice);
				// clear session
				Factory::getApplication()->getSession()->destroy();
				jexit();
			}
			$this->setRedirect(Route::_('index.php?option=com_componentbuilder&view=joomla_components', false), implode("<br />", $backupNotice), $backupNoticeStatus);
			return;
		}
		// quite only if auto backup (adding this script from custom code :)
		if ('backup' === $model->activeType)
		{
			echo "# Error\n" . Text::_('COM_COMPONENTBUILDER_ACCESS_DENIED');
			// clear session
			Factory::getApplication()->getSession()->destroy();
			jexit();
		}
		$this->setRedirect(Route::_('index.php?option=com_componentbuilder&view=joomla_components', false), Text::_('COM_COMPONENTBUILDER_ACCESS_DENIED'), 'Error');
		return;
	}


	/**
	 * Run worker request
	 *
	 * @return  mix
	 */
	public function worker()
	{
		// get input values
		$input = Factory::getApplication()->input;
		// get DATA
		$DATA = $input->post->get('VDM_DATA', null, 'STRING');
		// get TASK
		$TASK = $input->server->get('HTTP_VDM_TASK', null, 'STRING');
		// get TYPE
		$TYPE = $input->server->get('HTTP_VDM_VALUE_TYPE', null, 'STRING');
		// check if correct value is given
		if (StringHelper::check($DATA) && StringHelper::check($TASK) && StringHelper::check($TYPE))
		{
			// get the type of values we are working with ( 2 = array; 1 = string)
			$type = ComponentbuilderHelper::unlock($TYPE);
			// get data value
			$dataValues = ComponentbuilderHelper::unlock($DATA);
			// get the task
			$task = ComponentbuilderHelper::unlock($TASK);
			// check the for a string
			if (1 == $type && ObjectHelper::check($dataValues) && StringHelper::check($task))
			{
				// get model
				$model = $this->getModel('api');
				// check if allowed and method exist and is callable
				if (in_array($task, $this->allowedMethods) && method_exists($model, $task) && is_callable(array($model, $task)))
				{
					// trigger local method
					if (isset($this->localMethodTrigger[$task]))
					{
						// run the local method
						$this->{$this->localMethodTrigger[$task]}();
					}
					// run the model method
					$result = $model->{$task}($dataValues);
					// check if we have messages
					if (UtilitiesArrayHelper::check($model->messages))
					{
						// return locked values
						echo ComponentbuilderHelper::lock($model->messages);
						// clear session
						Factory::getApplication()->getSession()->destroy();
						jexit();
					}
					elseif ($result)
					{
						echo 1;
						// clear session
						Factory::getApplication()->getSession()->destroy();
						jexit();
					}
				}
			}
		}
		// not success
		echo 0;
		// clear session
		Factory::getApplication()->getSession()->destroy();
		jexit();
	}


	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowEdit($data = [], $key = 'id')
	{
		// to insure no other tampering
		return false;
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowAdd($data = [])
	{
		// to insure no other tampering
		return false;
	}

	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowSave($data, $key = 'id')
	{
		// to insure no other tampering
		return false;
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = [])
	{
	}
}
