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

namespace VDM\Joomla\Componentbuilder\Compiler\Extension\JoomlaFour;


use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Extension\InstallInterface;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\GetScriptInterface;


/**
 * Loading the Extension Installation Script Class
 * 
 * @since 3.2.0
 */
final class InstallScript implements GetScriptInterface
{
	/**
	 * The extension
	 *
	 * @var     InstallInterface|Object
	 * @since 3.2.0
	 */
	protected object $extension;

	/**
	 * The methods
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $methods = ['php_script', 'php_preflight', 'php_postflight', 'php_method'];

	/**
	 * The types
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $types = ['construct', 'install', 'update', 'uninstall', 'discover_install'];

	/**
	 * The construct bucket
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $construct = [];

	/**
	 * The install bucket
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $install = [];

	/**
	 * The update bucket
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $update = [];

	/**
	 * The uninstall bucket
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $uninstall = [];

	/**
	 * The preflight switch
	 *
	 * @var     bool
	 * @since 3.2.0
	 */
	protected bool $preflightActive = false;

	/**
	 * The preflight bucket
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $preflightBucket = ['install' => [], 'uninstall' => [], 'discover_install' => [], 'update' => []];

	/**
	 * The postflight switch
	 *
	 * @var     bool
	 * @since 3.2.0
	 */
	protected bool $postflightActive = false;

	/**
	 * The postflight bucket
	 *
	 * @var     array
	 * @since 3.2.0
	 */
	protected array $postflightBucket = ['install' => [], 'uninstall' => [], 'discover_install' => [], 'update' => []];

	/**
	 * get install script
	 *
	 * @param   Object       $extension     The extension object
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	public function get(object $extension): string
	{
		// purge the object
		$this->rest();

		// loop over methods and types
		foreach ($this->methods as $method)
		{
			foreach ($this->types as $type)
			{
				if (isset($extension->{'add_' . $method . '_' . $type})
					&& $extension->{'add_' . $method . '_' . $type} == 1
					&& StringHelper::check(
						$extension->{$method . '_' . $type}
					))
				{
					// add to the main methods
					if ('php_method' === $method || 'php_script' === $method)
					{
						$this->{$type}[] = $extension->{$method . '_' . $type};
					}
					else
					{
						// get the flight key
						$flight = str_replace('php_', '', (string) $method);
						// load the script to our bucket
						$this->{$flight . 'Bucket'}[$type][]  = $extension->{$method . '_' . $type};
						// show that the method is active
						$this->{$flight . 'Active'} = true;
					}
				}
			}
		}

		$this->extension = $extension;

		// return the class
		return $this->build();
	}

	/**
	 * Reset all bucket at the start of each build
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	protected function rest(): void
	{
		$this->construct = [];
		$this->install = [];
		$this->update = [];
		$this->uninstall = [];
		$this->preflightActive = false;
		$this->preflightBucket = ['install' => [], 'uninstall' => [], 'discover_install' => [], 'update' => []];
		$this->postflightActive = false;
		$this->postflightBucket = ['install' => [], 'uninstall' => [], 'discover_install' => [], 'update' => []];
	}

	/**
	 * build the install class
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	protected function build(): string
	{
		// start build
		$script = $this->head();

		// load constructor if set
		$script .= $this->construct();

		// load install method if set
		$script .= $this->main('install');

		// load update method if set
		$script .= $this->main('update');

		// load uninstall method if set
		$script .= $this->main('uninstall');

		// load preflight method if set
		$script .= $this->flight('preflight');

		// load postflight method if set
		$script .= $this->flight('postflight');

		// close the class
		$script .= PHP_EOL . '}' . PHP_EOL;

		return $script;
	}

	/**
	 * get install script head
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	protected function head(): string
	{
		// get the extension
		$extension = $this->extension;

		// start build
		$script = PHP_EOL . 'use Joomla\CMS\Factory;';
		$script .= PHP_EOL . 'use Joomla\CMS\Language\Text;';
		$script .= PHP_EOL . 'use Joomla\CMS\Filesystem\File;';
		$script .= PHP_EOL . 'use Joomla\CMS\Filesystem\Folder;' . PHP_EOL;
		$script .= PHP_EOL . '/**';
		$script .= PHP_EOL . ' * ' . $extension->official_name
			. ' script file.';
		$script .= PHP_EOL . ' *';
		$script .= PHP_EOL . ' * @package ' . $extension->class_name;
		$script .= PHP_EOL . ' */';
		$script .= PHP_EOL . 'class ' . $extension->installer_class_name;
		$script .= PHP_EOL . '{';

		return $script;
	}

	/**
	 * get constructor
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	protected function construct(): string
	{
		// return empty string if not set
		if (!ArrayHelper::check($this->construct))
		{
			return '';
		}

		// the __construct script
		$script = PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
		$script .= PHP_EOL . Indent::_(1) . ' * Constructor';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1)
			. ' * @param   Joomla\CMS\Installer\InstallerAdapter  $adapter  The object responsible for running this script';
		$script .= PHP_EOL . Indent::_(1) . ' */';
		$script .= PHP_EOL . Indent::_(1)
			. 'public function __construct($adapter)';
		$script .= PHP_EOL . Indent::_(1) . '{';
		$script .= PHP_EOL . implode(PHP_EOL . PHP_EOL, $this->construct);
		// close the function
		$script .= PHP_EOL . Indent::_(1) . '}';

		return $script;
	}

	/**
	 * build main methods
	 *
	 * @param   string  $name   the method being called
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	protected function main(string $name): string
	{
		// return empty string if not set
		if (!ArrayHelper::check($this->{$name}))
		{
			return '';
		}
		// load the install method
		$script = PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
		$script .= PHP_EOL . Indent::_(1) . " * Called on $name";
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1)
			. ' * @param   Joomla\CMS\Installer\InstallerAdapter  $adapter  The object responsible for running this script';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1)
			. ' * @return  boolean  True on success';
		$script .= PHP_EOL . Indent::_(1) . ' */';
		$script .= PHP_EOL . Indent::_(1) . 'public function '
			. $name . '($adapter)';
		$script .= PHP_EOL . Indent::_(1) . '{';
		$script .= PHP_EOL . implode(PHP_EOL . PHP_EOL, $this->{$name});
		// return true
		if ('uninstall' !== $name)
		{
			$script .= PHP_EOL . Indent::_(2) . 'return true;';
		}
		// close the function
		$script .= PHP_EOL . Indent::_(1) . '}';

		return $script;
	}

	/**
	 * build flight methods
	 *
	 * @param   string  $name   the method being called
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	protected function flight(string $name): string
	{
		// return empty string if not set
		if (empty($this->{$name . 'Active'}))
		{
			return '';
		}

		// the pre/post function types
		$script = PHP_EOL . PHP_EOL . Indent::_(1) . '/**';
		$script .= PHP_EOL . Indent::_(1)
			. ' * Called before any type of action';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1)
			. ' * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)';
		$script .= PHP_EOL . Indent::_(1)
			. ' * @param   Joomla\CMS\Installer\InstallerAdapter  $adapter  The object responsible for running this script';
		$script .= PHP_EOL . Indent::_(1) . ' *';
		$script .= PHP_EOL . Indent::_(1)
			. ' * @return  boolean  True on success';
		$script .= PHP_EOL . Indent::_(1) . ' */';
		$script .= PHP_EOL . Indent::_(1) . 'public function '
			. $name . '($route, $adapter)';
		$script .= PHP_EOL . Indent::_(1) . '{';
		$script .= PHP_EOL . Indent::_(2) . '//' . Line::_(__Line__, __Class__)
			. ' get application';
		$script .= PHP_EOL . Indent::_(2)
			. '$app = Factory::getApplication();' . PHP_EOL;

		// add the default version check (TODO) must make this dynamic
		if ('preflight' === $name)
		{
			$script .= PHP_EOL . Indent::_(2) . '//' . Line::_(__Line__, __Class__)
				.' the default for both install and update';
			$script .= PHP_EOL . Indent::_(2)
				. '$jversion = new JVersion();';
			$script .= PHP_EOL . Indent::_(2)
				. "if (!\$jversion->isCompatible('3.8.0'))";
			$script .= PHP_EOL . Indent::_(2) . '{';
			$script .= PHP_EOL . Indent::_(3)
				. "\$app->enqueueMessage('Please upgrade to at least Joomla! 3.8.0 before continuing!', 'error');";
			$script .= PHP_EOL . Indent::_(3) . 'return false;';
			$script .= PHP_EOL . Indent::_(2) . '}' . PHP_EOL;
		}

		// now add the scripts
		foreach ($this->{$name . 'Bucket'} as $route => $_script)
		{
			if (ArrayHelper::check($_script))
			{
				// set the if and script
				$script .= PHP_EOL . Indent::_(2) . "if ('" . $route
					. "' === \$route)";
				$script .= PHP_EOL . Indent::_(2) . '{';
				$script .= PHP_EOL . implode(
						PHP_EOL . PHP_EOL, $_script
					);
				$script .= PHP_EOL . Indent::_(2) . '}' . PHP_EOL;
			}
		}

		// return true
		$script .= PHP_EOL . Indent::_(2) . 'return true;';
		// close the function
		$script .= PHP_EOL . Indent::_(1) . '}';

		return $script;
	}
}

