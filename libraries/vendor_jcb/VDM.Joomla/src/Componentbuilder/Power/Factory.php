<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September, 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace VDM\Joomla\Componentbuilder\Power;


use Joomla\DI\Container;
use VDM\Joomla\Componentbuilder\Power\Service\Power;
use VDM\Joomla\Componentbuilder\Service\Database;
use VDM\Joomla\Componentbuilder\Power\Service\Database as PowerDatabase;
use VDM\Joomla\Componentbuilder\Power\Service\Generator;
use VDM\Joomla\Componentbuilder\Service\Gitea;
use VDM\Joomla\Componentbuilder\Power\Service\Gitea as GiteaPower;
use VDM\Joomla\Gitea\Service\Utilities as GiteaUtilities;
use VDM\Joomla\Interfaces\FactoryInterface;


/**
 * Power Factory
 * 
 * @since 3.2.0
 */
abstract class Factory implements FactoryInterface
{
	/**
	 * Global Package Container
	 *
	 * @var     Container
	 * @since 3.2.0
	 **/
	protected static $container = null;

	/**
	 * Get any class from the package container
	 *
	 * @param   string  $key  The container class key
	 *
	 * @return  Mixed
	 * @since 3.2.0
	 */
	public static function _($key)
	{
		return self::getContainer()->get($key);
	}

	/**
	 * Get the global package container
	 *
	 * @return  Container
	 * @since 3.2.0
	 */
	public static function getContainer(): Container
	{
		if (!self::$container)
		{
			self::$container = self::createContainer();
		}

		return self::$container;
	}

	/**
	 * Create a container object
	 *
	 * @return  Container
	 * @since 3.2.0
	 */
	protected static function createContainer(): Container
	{
		return (new Container())
			->registerServiceProvider(new Power())
			->registerServiceProvider(new Database())
			->registerServiceProvider(new PowerDatabase())
			->registerServiceProvider(new Generator())
			->registerServiceProvider(new Gitea())
			->registerServiceProvider(new GiteaPower())
			->registerServiceProvider(new GiteaUtilities());
	}
}

