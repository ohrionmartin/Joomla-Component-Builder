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

namespace VDM\Joomla\Componentbuilder\Search;


use Joomla\DI\Container;
use VDM\Joomla\Componentbuilder\Search\Service\Search;
use VDM\Joomla\Componentbuilder\Search\Service\Model;
use VDM\Joomla\Componentbuilder\Search\Service\Database;
use VDM\Joomla\Componentbuilder\Search\Service\Agent;
use VDM\Joomla\Interfaces\FactoryInterface;


/**
 * Search Factory
 * 
 * @since 3.2.0
 */
abstract class Factory implements FactoryInterface
{
	/**
	 * Global Search Container
	 *
	 * @var     Container
	 * @since 3.2.0
	 **/
	protected static $container = null;

	/**
	 * Get any class from the search container
	 *
	 * @param   string  $key  The container class key
	 *
	 * @return  Mixed
	 * @since 3.2.0
	 */
	public static function _(string $key)
	{
		return self::getContainer()->get($key);
	}

	/**
	 * Get the global search container
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
			->registerServiceProvider(new Search())
			->registerServiceProvider(new Model())
			->registerServiceProvider(new Database())
			->registerServiceProvider(new Agent());
	}

}

