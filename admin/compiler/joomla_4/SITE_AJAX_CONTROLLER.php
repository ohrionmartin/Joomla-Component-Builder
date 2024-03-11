<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this JCB template file (EVER)
defined('_JCB_TEMPLATE') or die;
?>
###BOM###
namespace ###NAMESPACEPREFIX###\Component\###ComponentNamespace###\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Session\Session;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * ###Component### Ajax Base Controller
 *
 * @since  1.6
 */
class AjaxController extends BaseController
{
    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     *                                          Recognized key values include 'name', 'default_task', 'model_path', and
     *                                          'view_path' (this list is not meant to be comprehensive).
     * @param   ?MVCFactoryInterface  $factory  The factory.
     * @param   ?CMSApplication       $app      The Application for the dispatcher
     * @param   ?Input                $input    Input
     *
     * @since   3.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		// make sure all json stuff are set
		$this->app->getDocument()->setMimeEncoding( 'application/json' );
		$this->app->setHeader('Content-Disposition','attachment;filename="getajax.json"');
		$this->app->setHeader('Access-Control-Allow-Origin', '*');
		// load the tasks ###REGISTER_SITE_AJAX_TASK###
	}

	public function ajax()
	{
		// get the user for later use
		$user         = $this->app->getIdentity();
		// get the input values
		$jinput       = $this->input ?? $this->app->input;
		// check if we should return raw (DEFAULT TRUE SINCE J4)
		$returnRaw    = $jinput->get('raw', true, 'BOOLEAN');
		// return to a callback function
		$callback     = $jinput->get('callback', null, 'CMD');
		// Check Token!
		$token        = Session::getFormToken();
		$call_token   = $jinput->get('token', 0, 'ALNUM');
		if($jinput->get($token, 0, 'ALNUM') || $token === $call_token)
		{
			// get the task
			$task = $this->getTask();
			switch($task)
			{###AJAX_SITE_INPUT_RETURN###
			}
		}
		else
		{
			// return to a callback function
			if($callback)
			{
				echo $callback."(".json_encode(['error' => 'There was an error! [139]']).");";
			}
			elseif($returnRaw)
			{
				echo json_encode(['error' => 'There was an error! [139]']);
			}
			else
			{
				echo "(".json_encode(['error' => 'There was an error! [139]']).");";
			}
		}
	}
}
