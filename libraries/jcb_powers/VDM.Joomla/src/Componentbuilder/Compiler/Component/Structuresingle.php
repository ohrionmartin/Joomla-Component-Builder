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

namespace VDM\Joomla\Componentbuilder\Compiler\Component;


use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Registry;
use VDM\Joomla\Componentbuilder\Compiler\Placeholder;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\Component\SettingsInterface as Settings;
use VDM\Joomla\Componentbuilder\Compiler\Component;
use VDM\Joomla\Componentbuilder\Compiler\Builder\ContentOne as Content;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Counter;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Paths;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Files;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;


/**
 * Single Files and Folders Builder Class
 * 
 * @since 3.2.0
 */
final class Structuresingle
{
	/**
	 * The new name
	 *
	 * @var    string
	 * @since 3.2.0
	 */
	protected string $newName;

	/**
	 * Current Full Path
	 *
	 * @var    string
	 * @since 3.2.0
	 */
	protected string $currentFullPath;

	/**
	 * Package Full Path
	 *
	 * @var    string
	 * @since 3.2.0
	 */
	protected string $packageFullPath;

	/**
	 * ZIP Full Path
	 *
	 * @var    string
	 * @since 3.2.0
	 */
	protected string $zipFullPath;

	/**
	 * The Config Class.
	 *
	 * @var   Config
	 * @since 3.2.0
	 */
	protected Config $config;

	/**
	 * The Registry Class.
	 *
	 * @var   Registry
	 * @since 3.2.0
	 */
	protected Registry $registry;

	/**
	 * The Placeholder Class.
	 *
	 * @var   Placeholder
	 * @since 3.2.0
	 */
	protected Placeholder $placeholder;

	/**
	 * The SettingsInterface Class.
	 *
	 * @var   Settings
	 * @since 3.2.0
	 */
	protected Settings $settings;

	/**
	 * The Component Class.
	 *
	 * @var   Component
	 * @since 3.2.0
	 */
	protected Component $component;

	/**
	 * The ContentOne Class.
	 *
	 * @var   Content
	 * @since 3.2.0
	 */
	protected Content $content;

	/**
	 * The Counter Class.
	 *
	 * @var   Counter
	 * @since 3.2.0
	 */
	protected Counter $counter;

	/**
	 * The Paths Class.
	 *
	 * @var   Paths
	 * @since 3.2.0
	 */
	protected Paths $paths;

	/**
	 * The Files Class.
	 *
	 * @var   Files
	 * @since 3.2.0
	 */
	protected Files $files;

	/**
	 * Application object.
	 *
	 * @var    CMSApplication
	 * @since 3.2.0
	 **/
	protected CMSApplication $app;

	/**
	 * Constructor.
	 *
	 * @param Config                $config        The Config Class.
	 * @param Registry              $registry      The Registry Class.
	 * @param Placeholder           $placeholder   The Placeholder Class.
	 * @param Settings              $settings      The SettingsInterface Class.
	 * @param Component             $component     The Component Class.
	 * @param Content               $content       The ContentOne Class.
	 * @param Counter               $counter       The Counter Class.
	 * @param Paths                 $paths         The Paths Class.
	 * @param Files                 $files         The Files Class.
	 * @param CMSApplication|null   $app           The CMS Application object.
	 *
	 * @since 3.2.0
	 */
	public function __construct(Config $config, Registry $registry,
		Placeholder $placeholder, Settings $settings,
		Component $component, Content $content, Counter $counter,
		Paths $paths, Files $files, ?CMSApplication $app = null)
	{
		$this->config = $config;
		$this->registry = $registry;
		$this->placeholder = $placeholder;
		$this->settings = $settings;
		$this->component = $component;
		$this->content = $content;
		$this->counter = $counter;
		$this->paths = $paths;
		$this->files = $files;
		$this->app = $app ?: Factory::getApplication();
	}

	/**
	 * Build the Single Files & Folders
	 *
	 * @return  bool
	 * @since 3.2.0
	 */
	public function build(): bool
	{
		if ($this->settings->exists())
		{
			// TODO needs more looking at this must be dynamic actually
			$this->registry->appendArray('files.not.new', 'LICENSE.txt');

			// do license check
			$LICENSE = $this->doLicenseCheck();

			// do README check
			$README = $this->doReadmeCheck();

			// do CHANGELOG check
			$CHANGELOG = $this->doChangelogCheck();

			// start moving
			foreach ($this->settings->single() as $target => $details)
			{
				// if not gnu/gpl license dont add the LICENSE.txt file
				if ($details->naam === 'LICENSE.txt' && !$LICENSE)
				{
					continue;
				}

				// if not needed do not add
				if (($details->naam === 'README.md' || $details->naam === 'README.txt')
					&& !$README)
				{
					continue;
				}

				// if not needed do not add
				if ($details->naam === 'CHANGELOG.md' && !$CHANGELOG)
				{
					continue;
				}

				// set new name
				$this->setNewName($details);

				// set all paths
				$this->setPaths($details);

				// check if the path exists
				if ($this->pathExist($details))
				{
					// set the target
					$this->setTarget($target, $details);
				}

				// set dynamic target as needed
				$this->setDynamicTarget($details);
			}

			return true;
		}

		return false;
	}

	/**
	 * Check if license must be added
	 *
	 * @return  bool
	 * @since 3.2.0
	 */
	private function doLicenseCheck(): bool
	{
		$licenseChecker = strtolower((string) $this->component->get('license', ''));

		if (strpos($licenseChecker, 'gnu') !== false
			&& strpos(
				$licenseChecker, '2'
			) !== false
			&& (strpos($licenseChecker, 'gpl') !== false
			|| strpos(
				$licenseChecker, 'general public license'
			) !== false))
		{
			return true;
		}

		return false;
	}

	/**
	 * Check if readme must be added
	 *
	 * @return  bool
	 * @since 3.2.0
	 */
	private function doReadmeCheck(): bool
	{
		return (bool) $this->component->get('addreadme', false);
	}

	/**
	 * Check if changelog must be added
	 *
	 * @return  bool
	 * @since 3.2.0
	 */
	private function doChangelogCheck(): bool
	{
		return (bool) $this->component->get('changelog', false);
	}

	/**
	 * Set the new name
	 *
	 * @param object $details
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function setNewName(object $details)
	{
		// do the file renaming
		if (isset($details->rename) && $details->rename)
		{
			if ($details->rename === 'new')
			{
				$newName = $details->newName;
			}
			else
			{
				$naam = $details->naam ?? 'error';
				$newName = str_replace(
					$details->rename,
					$this->config->component_code_name,
					(string) $naam
				);
			}
		}
		else
		{
			$newName = $details->naam ?? 'error';
		}

		$this->newName = $this->placeholder->update_($newName);
	}

	/**
	 * Set all needed paths
	 *
	 * @param object $details
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function setPaths(object $details)
	{
		// check if we have a target value
		if (isset($details->_target))
		{
			// set destination path
			$zipPath = str_replace(
					$details->_target['type'] . '/', '', (string) $details->path
				);
			$path = str_replace(
				$details->_target['type'] . '/',
				$this->registry->get('dynamic_paths.' . $details->_target['key'], '') . '/',
					(string) $details->path
				);
		}
		else
		{
			// set destination path
			$zipPath = str_replace('c0mp0n3nt/', '', (string) $details->path);
			$path = str_replace(
					'c0mp0n3nt/', $this->paths->component_path . '/', (string) $details->path
				);
		}

		// set the template folder path
		$templatePath = (isset($details->custom) && $details->custom)
			? (($details->custom !== 'full') ? $this->paths->template_path_custom
				. '/' : '') : $this->paths->template_path . '/';

		// set the final paths
		$currentFullPath = (preg_match('/^[a-z]:/i', (string) $details->naam)) ? $details->naam
			: $templatePath . '/' . $details->naam;

		$this->currentFullPath = str_replace('//', '/', (string) $currentFullPath);

		$this->packageFullPath = str_replace('//', '/', $path . '/' . $this->newName);

		$this->zipFullPath     = str_replace(
			'//', '/', $zipPath . '/' . $this->newName
		);
	}

	/**
	 * Check if path exists
	 *
	 * @param object $details
	 *
	 * @return  bool
	 * @since 3.2.0
	 */
	private function pathExist(object $details): bool
	{
		// check if this has a type
		if (!isset($details->type))
		{
			return false;
		}
		// take action based on type
		elseif ($details->type === 'file' && !File::exists($this->currentFullPath))
		{
			$this->app->enqueueMessage(
				Text::_('COM_COMPONENTBUILDER_HR_HTHREEFILE_PATH_ERRORHTHREE'), 'Error'
			);
			$this->app->enqueueMessage(
				Text::sprintf('COM_COMPONENTBUILDER_THE_FILE_PATH_BSB_DOES_NOT_EXIST_AND_WAS_NOT_ADDED',
					$this->currentFullPath
				), 'Error'
			);

			return false;
		}
		elseif ($details->type === 'folder' && !Folder::exists($this->currentFullPath))
		{
			$this->app->enqueueMessage(
				Text::_('COM_COMPONENTBUILDER_HR_HTHREEFOLDER_PATH_ERRORHTHREE'),
				'Error'
			);
			$this->app->enqueueMessage(
				Text::sprintf('COM_COMPONENTBUILDER_THE_FOLDER_PATH_BSB_DOES_NOT_EXIST_AND_WAS_NOT_ADDED',
					$this->currentFullPath
				), 'Error'
			);

			return false;
		}

		return true;
	}

	/**
	 * Set the target based on target type
	 *
	 * @param string   $target
	 * @param object  $details
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function setTarget(string $target, object $details)
	{
		// take action based on type
		if ($details->type === 'file')
		{
			// move the file
			$this->moveFile();

			// register the file
			$this->registerFile($target, $details);
		}
		elseif ($details->type === 'folder')
		{
			// move the folder to its place
			Folder::copy(
				$this->currentFullPath, $this->packageFullPath, '', true
			);

			// count the folder created
			$this->counter->folder++;
		}
	}

	/**
	 * Move/Copy the file into place
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function moveFile()
	{
		// get base name && get the path only
		$packageFullPath0nly = str_replace(
			basename($this->packageFullPath), '', $this->packageFullPath
		);

		// check if path exist, if not creat it
		if (!Folder::exists($packageFullPath0nly))
		{
			Folder::create($packageFullPath0nly);
		}

		// move the file to its place
		File::copy($this->currentFullPath, $this->packageFullPath);

		// count the file created
		$this->counter->file++;
	}

	/**
	 * Register the file
	 *
	 * @param string   $target
	 * @param object  $details
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function registerFile(string $target, object $details)
	{
		// store the new files
		if (!in_array($target, $this->registry->get('files.not.new', [])))
		{
			if (isset($details->_target))
			{
				$this->files->appendArray($details->_target['key'],
					[
						'path' => $this->packageFullPath,
						'name' => $this->newName,
						'zip'  => $this->zipFullPath
					]
				);
			}
			else
			{
				$this->files->appendArray('static',
					[
						'path' => $this->packageFullPath,
						'name' => $this->newName,
						'zip'  => $this->zipFullPath
					]
				);
			}
		}

		// ensure we update this file if needed
		if ($this->registry->exists('update.file.content.' . $target))
		{
			// remove the pointer
			$this->registry->remove('update.file.content.' . $target);

			// set the full path
			$this->registry->set('update.file.content.' . $this->packageFullPath, $this->packageFullPath);
		}
	}

	/**
	 * Set Dynamic Target
	 *
	 * @param object  $details
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function setDynamicTarget(object $details)
	{
		// only add if no target found since those belong to plugins and modules
		if (!isset($details->_target))
		{
			// check if we should add the dynamic folder moving script to the installer script
			$checker = array_values((array) explode('/', $this->zipFullPath));

			// TODO <-- this may not be the best way, will keep an eye on this.
			// We basicly only want to check if a folder is added that is not in the stdFolders array
			if (isset($checker[0])
				&& StringHelper::check($checker[0])
				&& !$this->settings->standardFolder($checker[0]))
			{
				// activate dynamic folders
				$this->setDynamicFolders();
			}
			elseif (count((array) $checker) == 2
				&& StringHelper::check($checker[0]))
			{
				$add_to_extra = false;

				// set the target
				$eNAME = 'FILES';
				$ename = 'filename';

				// this should not happen and must have been caught by the above if statment
				if ($details->type === 'folder')
				{
					// only folders outside the standard folder are added
					$eNAME        = 'FOLDERS';
					$ename        = 'folder';
					$add_to_extra = true;
				}
				// if this is a file, it can only be added to the admin/site/media folders
				// all other folders are moved as a whole so their files do not need to be declared
				elseif ($this->settings->standardFolder($checker[0])
					&& !$this->settings->standardRootFile($checker[1]))
				{
					$add_to_extra = true;
				}

				// add if valid folder/file
				if ($add_to_extra)
				{
					// set the tab
					$eTab = Indent::_(2);
					if ('admin' === $checker[0])
					{
						$eTab = Indent::_(3);
					}

					// set the xml file
					$key_ = 'EXSTRA_'
						. StringHelper::safe(
							$checker[0], 'U'
						) . '_' . $eNAME;
					$this->content->add($key_,
						PHP_EOL . $eTab . "<" . $ename . ">"
						. $checker[1] . "</" . $ename . ">");
				}
			}
		}
	}

	/**
	 * Add the dynamic folders
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	private function setDynamicFolders()
	{
		// check if we should add the dynamic folder moving script to the installer script
		if (!$this->registry->get('set_move_folders_install_script'))
		{
			// add the setDynamicF0ld3rs() method to the install scipt.php file
			$this->registry->set('set_move_folders_install_script', true);

			$function = 'setDynamicF0ld3rs';
			$script = 'script.php';
			if ($this->config->get('joomla_version', 3) != 3)
			{
				$function = 'moveFolders';
				$script = 'ComponentnameInstallerScript.php';
			}

			// set message that this was done (will still add a tutorial link later)
			$this->app->enqueueMessage(
				Text::_('COM_COMPONENTBUILDER_HR_HTHREEDYNAMIC_FOLDERS_WERE_DETECTEDHTHREE'),
				'Notice'
			);

			$this->app->enqueueMessage(
				Text::sprintf('COM_COMPONENTBUILDER_A_METHOD_S_WAS_ADDED_TO_THE_INSTALL_BSB_OF_THIS_PACKAGE_TO_INSURE_THAT_THE_FOLDERS_ARE_COPIED_INTO_THE_CORRECT_PLACE_WHEN_THIS_COMPONENT_IS_INSTALLED',
					$function, $script
				),
				'Notice'
			);
		}
	}
}

