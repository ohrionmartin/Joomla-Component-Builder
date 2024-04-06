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

namespace VDM\Joomla\Componentbuilder\Compiler\Service;


use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\GetScriptInterface;
use VDM\Joomla\Componentbuilder\Compiler\Extension\JoomlaThree\InstallScript as J3InstallScript;
use VDM\Joomla\Componentbuilder\Compiler\Extension\JoomlaFour\InstallScript as J4InstallScript;
use VDM\Joomla\Componentbuilder\Compiler\Extension\JoomlaFive\InstallScript as J5InstallScript;


/**
 * Extension Script Service Provider
 * 
 * @since 3.2.0
 */
class Extension implements ServiceProviderInterface
{
	/**
	 * Current Joomla Version Being Build
	 *
	 * @var     int
	 * @since 3.2.0
	 **/
	protected $targetVersion;

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
		$container->alias(GetScriptInterface::class, 'Extension.InstallScript')
			->share('Extension.InstallScript', [$this, 'getExtensionInstallScript'], true);

		$container->alias(J3InstallScript::class, 'J3.Extension.InstallScript')
			->share('J3.Extension.InstallScript', [$this, 'getJ3ExtensionInstallScript'], true);

		$container->alias(J4InstallScript::class, 'J4.Extension.InstallScript')
			->share('J4.Extension.InstallScript', [$this, 'getJ4ExtensionInstallScript'], true);

		$container->alias(J5InstallScript::class, 'J5.Extension.InstallScript')
			->share('J5.Extension.InstallScript', [$this, 'getJ5ExtensionInstallScript'], true);
	}

	/**
	 * Get the Joomla 3 Extension Install Script
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J3InstallScript
	 * @since 3.2.0
	 */
	public function getJ3ExtensionInstallScript(Container $container): J3InstallScript
	{
		return new J3InstallScript();
	}

	/**
	 * Get the Joomla 4 Extension Install Script
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J4InstallScript
	 * @since 3.2.0
	 */
	public function getJ4ExtensionInstallScript(Container $container): J4InstallScript
	{
		return new J4InstallScript();
	}

	/**
	 * Get the Joomla 5 Extension Install Script
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  J5InstallScript
	 * @since 3.2.0
	 */
	public function getJ5ExtensionInstallScript(Container $container): J5InstallScript
	{
		return new J5InstallScript();
	}

	/**
	 * Get the Joomla Extension Install Script
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  GetScriptInterface
	 * @since 3.2.0
	 */
	public function getExtensionInstallScript(Container $container): GetScriptInterface
	{
		if (empty($this->targetVersion))
		{
			$this->targetVersion = $container->get('Config')->joomla_version;
		}

		return $container->get('J' . $this->targetVersion . '.Extension.InstallScript');
	}

}

