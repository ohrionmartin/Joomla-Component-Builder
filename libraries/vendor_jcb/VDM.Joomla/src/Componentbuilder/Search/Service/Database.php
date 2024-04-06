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

namespace VDM\Joomla\Componentbuilder\Search\Service;


use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use VDM\Joomla\Database\Load;
use VDM\Joomla\Componentbuilder\Search\Database\Load as LoadDatabase;
use VDM\Joomla\Componentbuilder\Search\Database\Insert as InsertDatabase;


/**
 * Database Service Provider
 * 
 * @since 3.2.0
 */
class Database implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	public function register(Container $container)
	{
		$container->alias(Load::class, 'Load')
			->share('Load', [$this, 'getLoad'], true);

		$container->alias(LoadDatabase::class, 'Load.Database')
			->share('Load.Database', [$this, 'getDatabaseLoad'], true);

		$container->alias(InsertDatabase::class, 'Insert.Database')
			->share('Insert.Database', [$this, 'getDatabaseInsert'], true);
	}

	/**
	 * Get the Core Load Database
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Load
	 * @since 3.2.0
	 */
	public function getLoad(Container $container): Load
	{
		return new Load();
	}

	/**
	 * Get the Load Database
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  LoadDatabase
	 * @since 3.2.0
	 */
	public function getDatabaseLoad(Container $container): LoadDatabase
	{
		return new LoadDatabase(
			$container->get('Config'),
			$container->get('Table'),
			$container->get('Load.Model'),
			$container->get('Load')
		);
	}

	/**
	 * Get the Insert Database
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  InsertDatabase
	 * @since 3.2.0
	 */
	public function getDatabaseInsert(Container $container): InsertDatabase
	{
		return new InsertDatabase(
			$container->get('Config'),
			$container->get('Table'),
			$container->get('Insert.Model')
		);
	}

}

