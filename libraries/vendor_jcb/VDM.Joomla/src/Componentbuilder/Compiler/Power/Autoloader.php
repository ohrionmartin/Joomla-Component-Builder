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

namespace VDM\Joomla\Componentbuilder\Compiler\Power;


use VDM\Joomla\Componentbuilder\Compiler\Power;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne as Content;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Utilities\ArrayHelper;


/**
 * Compiler Autoloader
 * 
 * @since 3.2.0
 */
class Autoloader
{
	/**
	 * The Power Class.
	 *
	 * @var   Power
	 * @since 3.2.0
	 */
	protected Power $power;

	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 3.2.0
	 */
	protected Config $config;

	/**
	 * The ContentOne Class.
	 *
	 * @var   Content
	 * @since 3.2.0
	 */
	protected Content $content;

	/**
	 * Helper Class Autoloader
	 *
	 * @var    string
	 * @since 3.2.0
	 **/
	protected string $helper = '';

	/**
	 * Constructor.
	 *
	 * @param Power     $power     The Power Class.
	 * @param Config    $config    The Config Class.
	 * @param Content   $content   The ContentOne Class.
	 *
	 * @since 3.2.0
	 */
	public function __construct(Power $power, Config $config, Content $content)
	{
		$this->power = $power;
		$this->config = $config;
		$this->content = $content;

		// reset all autoloaders power placeholders
		$this->content->set('ADMIN_POWER_HELPER', '');
		$this->content->set('SITE_POWER_HELPER', '');
		$this->content->set('CUSTOM_POWER_AUTOLOADER', '');
	}

	/**
	 * Set the autoloader into the active content array
	 *
	 * @return void
	 * @since 3.2.0
	 */
	public function set()
	{
		// make sure we only load this once
		if (ArrayHelper::check($this->power->namespace) && !$this->content->isString('CUSTOM_POWER_AUTOLOADER'))
		{
			/************************* IMPORTANT SORT NOTICE ***********************************************
			 *   make sure the name space values are sorted from the longest string to the shortest
			 *   so that the search do not mistakenly match a shorter namespace before a longer one
			 *   that has the same short namespace for example:
			 *        NameSpace\SubName\Sub <- will always match first
			 *        NameSpace\SubName\SubSubName
			 *   Should the shorter namespace be listed [first] it will match both of these:
			 *        NameSpace\SubName\Sub\ClassName
			 *        ^^^^^^^^^^^^^^^^^^^^^^
			 *        NameSpace\SubName\SubSubName\ClassName
			 *        ^^^^^^^^^^^^^^^^^^^^^^
			 ***********************************************************************************************/
			uksort($this->power->namespace, fn($a, $b) => strlen((string) $b) - strlen((string) $a));

			// check if we are using a plugin
			if ($this->loadPluginAutoloader())
			{
				$this->content->set('PLUGIN_POWER_AUTOLOADER', $this->getPluginAutoloader());
			}

			// load to the helper class
			if ($this->loadHelperAutoloader())
			{
				// load to admin helper class
				$this->content->add('ADMIN_POWER_HELPER', $this->getHelperAutoloader());

				// load to site helper class if needed
				if ($this->loadSiteAutoloader())
				{
					$this->content->add('SITE_POWER_HELPER', $this->getHelperAutoloader());
				}
			}

			// to add to custom files
			$this->content->add('CUSTOM_POWER_AUTOLOADER', $this->getHelperAutoloader());
		}
	}

	/**
	 * Should we load the plugin autoloader
	 *
	 * @return bool
	 * @since 3.2.0
	 */
	private function loadPluginAutoloader(): bool
	{
		return $this->content->exists('PLUGIN_POWER_AUTOLOADER');
	}

	/**
	 * Should we load the helper class autoloader
	 *
	 * @return bool
	 * @since 3.2.0
	 */
	private function loadHelperAutoloader(): bool
	{
		// for now we load it if the plugin is not loaded
		// but we may want to add a switch that
		// controls this behaviour.
		// return !$this->loadPluginAutoloader();
		// lets load it anyway (can't break anything)
		// but we will still like a switch for this
		return true;
	}

	/**
	 * Should we load the autoloader in site area
	 *
	 * @return bool
	 * @since 3.2.0
	 */
	private function loadSiteAutoloader(): bool
	{
		return (!$this->config->remove_site_folder || !$this->config->remove_site_edit_folder);
	}

	/**
	 * Get helper autoloader code
	 *
	 * @return string
	 * @since 3.2.0
	 */
	private function getPluginAutoloader(): string
	{
		// load the code
		$code = [];

		// if we should not load in the site are
		if (($script = $this->getBLockSiteLoading()) !== null)
		{
			$code[] = $script;
		}

		// add the composer stuff here
		if (($script = $this->getComposer(2)) !== null)
		{
			$code[] = $script;
		}

		// get the helper autoloader
		if (($script = $this->getAutoloader(2)) !== null)
		{
			$code[] = $script;
		}

		// if we have any
		if (!empty($code))
		{
			return PHP_EOL . PHP_EOL . implode(PHP_EOL . PHP_EOL, $code);
		}

		return '';
	}

	/**
	 * Get helper autoloader code
	 *
	 * @return string
	 * @since 3.2.0
	 */
	private function getHelperAutoloader(): string
	{
		// check if it was already build
		if (!empty($this->helper))
		{
			return $this->helper;
		}

		// load the code
		$code = [];

		// add the composer stuff here
		if (($script = $this->getComposer(0)) !== null)
		{
			$code[] = $script;
		}

		// get the helper autoloader
		if (($script = $this->getAutoloader(0)) !== null)
		{
			$code[] = $script;
		}

		// if we have any
		if (!empty($code))
		{
			$this->helper = PHP_EOL . PHP_EOL . implode(PHP_EOL . PHP_EOL, $code);
		}

		return $this->helper;
	}

	/**
	 * Get code that will block the plugin from loading
	 *   the autoloader in the site area
	 *
	 * @return string|null
	 * @since 3.2.0
	 */
	private function getBLockSiteLoading(): ?string
	{
		// if we should not load in the site are
		if (!$this->loadSiteAutoloader())
		{
			// we add code to prevent this plugin from triggering on the site area
			$not_site = [];
			$not_site[] = Indent::_(2) . '//'
				. Line::_(__Line__, __Class__) . ' do not run the autoloader in the site area';
			$not_site[] = Indent::_(2) . 'if ($this->app->isClient(\'site\'))';
			$not_site[] = Indent::_(2) . '{';
			$not_site[] = Indent::_(3) . 'return;';
			$not_site[] = Indent::_(2) . '}';

			return implode(PHP_EOL, $not_site);
		}

		return null;
	}

	/**
	 * Get autoloader code
	 *
	 * @param int    $tabSpace   The dynamic tab spacer
	 *
	 * @return string|null
	 * @since 3.2.0
	 */
	private function getAutoloader(int $tabSpace): ?string
	{
		if (($size = ArrayHelper::check($this->power->namespace)) > 0)
		{
			// we start building the spl_autoload_register function call
			$autoload_method = [];
			$autoload_method[] = Indent::_($tabSpace) . '//'
				. Line::_(__Line__, __Class__) . ' register additional namespace';
			$autoload_method[] = Indent::_($tabSpace) . '\spl_autoload_register(function ($class) {';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' project-specific base directories and namespace prefix';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '$search = [';

			// counter to manage the comma in the actual array
			$counter = 1;
			foreach ($this->power->namespace as $base_dir => $prefix)
			{
				// don't add the ending comma on last value
				if ($size == $counter)
				{
					$autoload_method[] = Indent::_($tabSpace) . Indent::_(2) . "'" . $this->config->get('jcb_powers_path', 'libraries/jcb_powers') . "/$base_dir' => '" . implode('\\\\', $prefix) . "'";
				}
				else
				{
					$autoload_method[] = Indent::_($tabSpace) . Indent::_(2) . "'" . $this->config->get('jcb_powers_path', 'libraries/jcb_powers') . "/$base_dir' => '" . implode('\\\\', $prefix) . "',";
				}
				$counter++;
			}
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '];';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '// Start the search and load if found';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '$found = false;';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '$found_base_dir = "";';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '$found_len = 0;';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . 'foreach ($search as $base_dir => $prefix)';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '{';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(2) . '//'
				. Line::_(__Line__, __Class__) . ' does the class use the namespace prefix?';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(2) . '$len = strlen($prefix);';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(2) . 'if (strncmp($prefix, $class, $len) === 0)';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(2) . '{';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(3) . '//'
				. Line::_(__Line__, __Class__) . ' we have a match so load the values';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(3) . '$found = true;';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(3) . '$found_base_dir = $base_dir;';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(3) . '$found_len = $len;';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(3) . '//'
				. Line::_(__Line__, __Class__) . ' done here';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(3) . 'break;';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(2) . '}';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '}';

			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' check if we found a match';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . 'if (!$found)';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '{';

			$autoload_method[] = Indent::_($tabSpace) . Indent::_(2) . '//'
				. Line::_(__Line__, __Class__) . ' not found so move to the next registered autoloader';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(2) . 'return;';

			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '}';

			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' get the relative class name';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '$relative_class = substr($class, $found_len);';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' replace the namespace prefix with the base directory, replace namespace';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '// separators with directory separators in the relative class name, append';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '// with .php';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . "\$file = JPATH_ROOT . '/' . \$found_base_dir . '/src' . str_replace('\\\\', '/', \$relative_class) . '.php';";
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '//'
				. Line::_(__Line__, __Class__) . ' if the file exists, require it';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . 'if (file_exists($file))';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '{';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(2) . 'require $file;';
			$autoload_method[] = Indent::_($tabSpace) . Indent::_(1) . '}';
			$autoload_method[] = Indent::_($tabSpace) . '});';

			return implode(PHP_EOL, $autoload_method);
		}

		return null;
	}

	/**
	 * Get the composer autoloader routine
	 *
	 * @param int    $tabSpace    The dynamic tab spacer
	 *
	 * @return string|null
	 * @since 3.2.0
	 */
	private function getComposer(int $tabSpace): ?string
	{
		if (ArrayHelper::check($this->power->composer))
		{
			// load the composer routine
			$composer_routine = [];

			// counter to manage the comma in the actual array
			$add_once = [];
			foreach ($this->power->composer as $access_point)
			{
				// don't add the ending comma on last value
				if (empty($add_once[$access_point]))
				{
					$composer_routine[] = Indent::_($tabSpace) . "\$composer_autoloader = JPATH_LIBRARIES . '/$access_point';";
					$composer_routine[] = Indent::_($tabSpace) . 'if (file_exists($composer_autoloader))';
					$composer_routine[] = Indent::_($tabSpace) . "{";
					$composer_routine[] = Indent::_($tabSpace) . Indent::_(1) . 'require_once $composer_autoloader;';
					$composer_routine[] = Indent::_($tabSpace) . "}";

					$add_once[$access_point] = true;
				}
			}

			// this is just about the [autoloader or autoloaders] in the comment ;)
			if (count($add_once) == 1)
			{
				array_unshift($composer_routine, Indent::_($tabSpace) . '//'
					. Line::_(__Line__, __Class__) . ' add the autoloader for the composer classes');
			}
			else
			{
				array_unshift($composer_routine, Indent::_($tabSpace) . '//'
					. Line::_(__Line__, __Class__) . ' add the autoloaders for the composer classes');
			}

			return implode(PHP_EOL, $composer_routine);
		}

		return null;
	}
}

