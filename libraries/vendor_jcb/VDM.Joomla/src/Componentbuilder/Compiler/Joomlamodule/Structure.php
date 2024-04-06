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

namespace VDM\Joomla\Componentbuilder\Compiler\Joomlamodule;


use VDM\Joomla\Componentbuilder\Compiler\Joomlamodule\Data as Module;
use VDM\Joomla\Componentbuilder\Compiler\Component;
use VDM\Joomla\Componentbuilder\Compiler\Config;
use VDM\Joomla\Componentbuilder\Compiler\Registry;
use VDM\Joomla\Componentbuilder\Compiler\Customcode\Dispenser;
use VDM\Joomla\Componentbuilder\Compiler\Interfaces\EventInterface as Event;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Counter;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Folder;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\File;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Files;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Indent;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Placefix;
use VDM\Joomla\Componentbuilder\Compiler\Utilities\Line;
use VDM\Joomla\Componentbuilder\Compiler\Builder\TemplateData;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\StringHelper;
use VDM\Joomla\Utilities\FileHelper;


/**
 * Joomla Module Structure Builder Class
 * 
 * @since 3.2.0
 */
class Structure
{
	/**
	 * The Data Class.
	 *
	 * @var   Module
	 * @since 3.2.0
	 */
	protected Module $module;

	/**
	 * The Component Class.
	 *
	 * @var   Component
	 * @since 3.2.0
	 */
	protected Component $component;

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
	 * The Dispenser Class.
	 *
	 * @var   Dispenser
	 * @since 3.2.0
	 */
	protected Dispenser $dispenser;

	/**
	 * The EventInterface Class.
	 *
	 * @var   Event
	 * @since 3.2.0
	 */
	protected Event $event;

	/**
	 * The Counter Class.
	 *
	 * @var   Counter
	 * @since 3.2.0
	 */
	protected Counter $counter;

	/**
	 * The Folder Class.
	 *
	 * @var   Folder
	 * @since 3.2.0
	 */
	protected Folder $folder;

	/**
	 * The File Class.
	 *
	 * @var   File
	 * @since 3.2.0
	 */
	protected File $file;

	/**
	 * The Files Class.
	 *
	 * @var   Files
	 * @since 3.2.0
	 */
	protected Files $files;

	/**
	 * The TemplateData Class.
	 *
	 * @var   TemplateData
	 * @since 3.2.0
	 */
	protected TemplateData $templatedata;

	/**
	 * Constructor.
	 *
	 * @param Module         $module         The Data Class.
	 * @param Component      $component      The Component Class.
	 * @param Config         $config         The Config Class.
	 * @param Registry       $registry       The Registry Class.
	 * @param Dispenser      $dispenser      The Dispenser Class.
	 * @param Event          $event          The EventInterface Class.
	 * @param Counter        $counter        The Counter Class.
	 * @param Folder         $folder         The Folder Class.
	 * @param File           $file           The File Class.
	 * @param Files          $files          The Files Class.
	 * @param TemplateData   $templatedata   The TemplateData Class.
	 *
	 * @since 3.2.0
	 */
	public function __construct(Module $module, Component $component, Config $config,
		Registry $registry, Dispenser $dispenser, Event $event,
		Counter $counter, Folder $folder, File $file,
		Files $files, TemplateData $templatedata)
	{
		$this->module = $module;
		$this->component = $component;
		$this->config = $config;
		$this->registry = $registry;
		$this->dispenser = $dispenser;
		$this->event = $event;
		$this->counter = $counter;
		$this->folder = $folder;
		$this->file = $file;
		$this->files = $files;
		$this->templatedata = $templatedata;
	}

	/**
	 * Build the Modules files, folders, url's and config
	 *
	 * @return  void
	 * @since 3.2.0
	 */
	public function build()
	{
		if ($this->module->exists())
		{
			// for plugin event TODO change event api signatures
			$component_context = $this->config->component_context;
			$modules = $this->module->get();

			// Trigger Event: jcb_ce_onBeforeSetModules
			$this->event->trigger(
				'jcb_ce_onBeforeBuildModules',
				array(&$component_context, &$modules)
			);

			foreach ($modules as $module)
			{
				if (ObjectHelper::check($module)
					&& isset($module->folder_name)
					&& StringHelper::check($module->folder_name))
				{
					// module path
					$this->modulePath($module);

					// set the module paths
					$this->registry->set('dynamic_paths.' . $module->key, $module->folder_path);

					// make sure there is no old build
					$this->folder->remove($module->folder_path);

					// create the main module folder
					$this->folder->create($module->folder_path);

					// create the main module file
					$this->setMainModFile($module);

					// creat the custom get file
					$this->setCustomGet($module);

					// set helper file
					$this->setHelperFile($module);

					// set main xml file
					$this->setMainXmlFile($module);

					// set tmpl folder
					$this->folder->create($module->folder_path . '/tmpl');

					// set default file
					$this->setDefaultFile($module);

					// set custom default files
					$this->setTemplateFiles($module);

					// set install script if needed
					$this->setInstallScript($module);

					// set readme if found
					$this->setReadme($module);

					// set the CSS and JavaScript in form
					$this->setCssJsForm($module);

					// set rules folders if needed
					if (isset($module->fields_rules_paths)
						&& $module->fields_rules_paths == 2)
					{
						// create rules folder
						$this->folder->create($module->folder_path . '/rules');
					}

					// set forms folder/files if needed
					$this->setForms($module);

					// set SQL stuff if needed
					$this->setSQL($module);

					// create the language folder
					$this->folder->create($module->folder_path . '/language');

					// also create the lang tag folder
					$this->folder->create(
						$module->folder_path . '/language/' . $this->config->get('lang_tag', 'en-GB')
					);

					// check if this module has files
					$this->setFiles($module);

					// check if this module has folders
					$this->setFolders($module);

					// check if this module has urls
					$this->setUrls($module);
				}
			}
		}
	}

	/**
	 * get the module xml template
	 *
	 * @param   object   $module    The module object
	 *
	 * @return  string
	 * @since 3.2.0
	 */
	protected function getXML(object &$module): string
	{
		$xml = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= PHP_EOL . '<extension type="module" version="'
			. $this->config->joomla_versions[$this->config->joomla_version]['xml_version'] . '" client="'
			. $module->target_client . '" method="upgrade">';
		$xml .= PHP_EOL . Indent::_(1) . '<name>' . $module->lang_prefix
			. '</name>';
		$xml .= PHP_EOL . Indent::_(1) . '<creationDate>' . Placefix::_h('BUILDDATE') . '</creationDate>';
		$xml .= PHP_EOL . Indent::_(1) . '<author>' . Placefix::_h('AUTHOR') . '</author>';
		$xml .= PHP_EOL . Indent::_(1) . '<authorEmail>' . Placefix::_h('AUTHOREMAIL') . '</authorEmail>';
		$xml .= PHP_EOL . Indent::_(1) . '<authorUrl>' . Placefix::_h('AUTHORWEBSITE') . '</authorUrl>';
		$xml .= PHP_EOL . Indent::_(1) . '<copyright>' . Placefix::_h('COPYRIGHT') . '</copyright>';
		$xml .= PHP_EOL . Indent::_(1) . '<license>' . Placefix::_h('LICENSE') . '</license>';
		$xml .= PHP_EOL . Indent::_(1) . '<version>' . $module->module_version
			. '</version>';
		$xml .= PHP_EOL . Indent::_(1) . '<description>' . $module->lang_prefix
			. '_XML_DESCRIPTION</description>';
		$xml .= Placefix::_h('MAINXML');
		$xml .= PHP_EOL . '</extension>';

		return $xml;
	}

	/**
	 * get the module admin custom script field
	 *
	 * @param   array   $fieldScriptBucket    The field
	 *
	 * @return  string
	 * @since 3.2.0
	 *
	 */
	protected function getCustomScriptField(array $fieldScriptBucket): string
	{
		$form_field_class   = [];
		$form_field_class[] = Placefix::_h('BOM') . PHP_EOL;
		$form_field_class[] = "//" . Line::_(__Line__, __Class__)
			. " No direct access to this file";
		$form_field_class[] = "defined('_JEXEC') or die('Restricted access');";
		$form_field_class[] = PHP_EOL . "use Joomla\CMS\Form\FormField;";
		$form_field_class[] = "use Joomla\CMS\Factory;";
		$form_field_class[] = PHP_EOL
			. "class JFormFieldModadminvvvvvvvdm extends FormField";
		$form_field_class[] = "{";
		$form_field_class[] = Indent::_(1)
			. "protected \$type = 'modadminvvvvvvvdm';";
		$form_field_class[] = PHP_EOL . Indent::_(1)
			. "protected function getLabel()";
		$form_field_class[] = Indent::_(1) . "{";
		$form_field_class[] = Indent::_(2) . "return;";
		$form_field_class[] = Indent::_(1) . "}";
		$form_field_class[] = PHP_EOL . Indent::_(1)
			. "protected function getInput()";
		$form_field_class[] = Indent::_(1) . "{";
		$form_field_class[] = Indent::_(2) . "//" . Line::_(__Line__, __Class__)
			. " Get the document";
		$form_field_class[] = Indent::_(2)
			. "\$document = Factory::getDocument();";
		$form_field_class[] = implode(PHP_EOL, $fieldScriptBucket);
		$form_field_class[] = Indent::_(2) . "return; // noting for now :)";
		$form_field_class[] = Indent::_(1) . "}";
		$form_field_class[] = "}";

		return implode(PHP_EOL, $form_field_class);
	}

	/**
	 * Set the module path
	 *
	 * @param object $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function modulePath(object &$module): void
	{
		$module->folder_path = $this->config->get('compiler_path', JPATH_COMPONENT_ADMINISTRATOR . '/compiler') . '/'
			. $module->folder_name;
	}

	/**
	 * Set the main module file
	 *
	 * @param object $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setMainModFile(object $module): void
	{
		// set main mod file
		$file_details = [
			'path' => $module->folder_path . '/' . $module->file_name . '.php',
			'name' => $module->file_name . '.php',
			'zip' => $module->file_name . '.php'
		];

		$this->file->write(
			$file_details['path'],
			'<?php' . PHP_EOL . '// main modfile' .
			PHP_EOL . Placefix::_h('BOM') . PHP_EOL .
			PHP_EOL . '// No direct access to this file' . PHP_EOL .
			"defined('_JEXEC') or die('Restricted access');"
			. PHP_EOL .
			Placefix::_h('MODCODE')
		);

		$this->files->appendArray($module->key, $file_details);

		// count the file created
		$this->counter->file++;
	}

	/**
	 * Set the custom get file
	 *
	 * @param object $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setCustomGet(object $module): void
	{
		if ($module->custom_get)
		{
			$file_details = [
				'path' => $module->folder_path . '/data.php',
				'name' => 'data.php',
				'zip' => 'data.php'
			];

			$this->file->write(
				$file_details['path'],
				'<?php' . PHP_EOL . '// get data file' . PHP_EOL . Placefix::_h('BOM') . PHP_EOL
				. PHP_EOL . '// No direct access to this file'
				. PHP_EOL . "defined('_JEXEC') or die('Restricted access');"
				. PHP_EOL . PHP_EOL . '/**' . PHP_EOL . ' * Module ' . $module->official_name . ' Data'
				. PHP_EOL . ' */' . PHP_EOL . "class " . $module->class_data_name
				. ' extends \JObject' . PHP_EOL . "{" . Placefix::_h('DYNAMICGETS') . "}"
				. PHP_EOL
			);

			$this->files->appendArray($module->key, $file_details);

			// count the file created
			$this->counter->file++;
		}
	}

	/**
	 * Set the helper file
	 *
	 * @param object $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setHelperFile(object $module): void
	{
		if ($module->add_class_helper >= 1)
		{
			$file_details = [
				'path' => $module->folder_path . '/helper.php',
				'name' => 'helper.php',
				'zip' => 'helper.php'
			];

			$this->file->write(
				$file_details['path'],
				'<?php' . PHP_EOL . '// helper file' . PHP_EOL . Placefix::_h('BOM') . PHP_EOL
				. PHP_EOL . '// No direct access to this file'
				. PHP_EOL . "defined('_JEXEC') or die('Restricted access');"
				. PHP_EOL . Placefix::_h('HELPERCODE')
			);

			$this->files->appendArray($module->key, $file_details);

			// count the file created
			$this->counter->file++;
		}
	}

	/**
	 * Set the main XML file
	 *
	 * @param object $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setMainXmlFile(object $module): void
	{
		$file_details = [
			'path' => $module->folder_path . '/' . $module->file_name . '.xml',
			'name' => $module->file_name . '.xml',
			'zip' => $module->file_name . '.xml'
		];

		$this->file->write(
			$file_details['path'],
			$this->getXML($module)
		);

		$this->files->appendArray($module->key, $file_details);

		// count the file created
		$this->counter->file++;
	}

	/**
	 * Set the main default template file
	 *
	 * @param object $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setDefaultFile(object $module): void
	{
		$file_details = [
			'path' => $module->folder_path . '/tmpl/default.php',
			'name' => 'default.php',
			'zip' => 'tmpl/default.php'
		];

		$this->file->write(
			$file_details['path'],
			'<?php' . PHP_EOL . '// default tmpl' . PHP_EOL . Placefix::_h('BOM') . PHP_EOL
			. PHP_EOL . '// No direct access to this file' . PHP_EOL
			. "defined('_JEXEC') or die('Restricted access');"
			. PHP_EOL . Placefix::_h('MODDEFAULT')
		);

		$this->files->appendArray($module->key, $file_details);

		// count the file created
		$this->counter->file++;
	}

	/**
	 * Set the additional template files
	 *
	 * @param object $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setTemplateFiles(object $module): void
	{
		if (($data_ = $this->templatedata->
			get($module->key . '.' . $module->code_name)) !== null)
		{
			foreach ($data_ as $template => $data)
			{
				$file_details = [
					'path' => $module->folder_path . "/tmpl/default_{$template}.php",
					'name' => "default_{$template}.php",
					'zip' => "tmpl/default_{$template}.php"
				];

				$this->file->write(
					$file_details['path'],
					'<?php' . PHP_EOL . '// default tmpl' . PHP_EOL . Placefix::_h('BOM') . PHP_EOL
					. PHP_EOL . '// No direct access to this file' . PHP_EOL
					. "defined('_JEXEC') or die('Restricted access');"
					. PHP_EOL . Placefix::_h(StringHelper::safe("MODDEFAULT_{$template}", 'U'))
				);

				$this->files->appendArray($module->key, $file_details);

				// count the file created
				$this->counter->file++;
			}
		}
	}

	/**
	 * Set the install script file
	 *
	 * @param object $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setInstallScript(object $module): void
	{
		if ($module->add_install_script)
		{
			$file_details = [
				'path' => $module->folder_path . '/script.php',
				'name' => 'script.php',
				'zip' => 'script.php'
			];

			$this->file->write(
				$file_details['path'],
				'<?php' . PHP_EOL . '// Script template'
				. PHP_EOL . Placefix::_h('BOM') . PHP_EOL
				. PHP_EOL . '// No direct access to this file' . PHP_EOL
				. "defined('_JEXEC') or die('Restricted access');" . PHP_EOL
				. Placefix::_h('INSTALLCLASS')
			);

			$this->files->appendArray($module->key, $file_details);

			// count the file created
			$this->counter->file++;
		}
	}

	/**
	 * Set the readme file
	 *
	 * @param object $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setReadme(object $module): void
	{
		if ($module->addreadme)
		{
			$file_details = [
				'path' => $module->folder_path . '/README.md',
				'name' => 'README.md',
				'zip' => 'README.md'
			];

			$this->file->write($file_details['path'], $module->readme);
			$this->files->appendArray($module->key, $file_details);

			// count the file created
			$this->counter->file++;
		}
	}

	/**
	 * Set the css and javascript in form
	 *
	 * @param object $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setCssJsForm(object $module): void
	{
		// set the folders target path
		$target_path = '';
		if ($module->target_client === 'administrator')
		{
			$target_path = '/administrator';
		}

		// check if we have custom fields needed for scripts
		$module->add_scripts_field = false;
		$field_script_bucket       = [];

		// add any css from the fields
		$this->setCssForm($module, $target_path, $field_script_bucket);

		// add any JavaScript from the fields
		$this->setJsForm($module, $target_path, $field_script_bucket);

		// set fields folders if needed
		if ($module->add_scripts_field
			|| (isset($module->fields_rules_paths)
				&& $module->fields_rules_paths == 2))
		{
			// create fields folder
			$this->folder->create($module->folder_path . '/fields');

			// add the custom script field
			if ($module->add_scripts_field)
			{
				$file_details = [
					'path' => $module->folder_path . '/fields/modadminvvvvvvvdm.php',
					'name' => 'modadminvvvvvvvdm.php',
					'zip'  => 'modadminvvvvvvvdm.php'
				];

				$this->file->write(
					$file_details['path'],
					$this->getCustomScriptField(
						$field_script_bucket
					)
				);

				$this->files->appendArray($module->key, $file_details);

				// count the file created
				$this->counter->file++;
			}
		}
	}

	/**
	 * Set the css in form
	 *
	 * @param object  $module
	 * @param string  $targetPath
	 * @param array   $bucket
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setCssForm(object &$module, string $targetPath, array &$bucket): void
	{
		if (($css = $this->dispenser->get(
			'css_view', $module->key
			)) !== null && StringHelper::check($css))
		{
			// make sure this script does not have PHP
			if (strpos((string) $css, '<?php') === false)
			{
				// make sure the field is added
				$module->add_scripts_field = true;

				// create the css folder
				$this->folder->create($module->folder_path . '/css');

				// add the CSS file
				$file_details = [
					'path' => $module->folder_path . '/css/mod_admin.css',
					'name' => 'mod_admin.css',
					'zip'  => 'mod_admin.css'
				];

				$this->file->write(
					$file_details['path'],
					Placefix::_h('BOM') . PHP_EOL
					. PHP_EOL . $css
				);

				$this->files->appendArray($module->key, $file_details);

				// count the file created
				$this->counter->file++;

				// add the field script
				$bucket[] = Indent::_(2) . "//"
					. Line::_(__Line__, __Class__) . " Custom CSS";
				$bucket[] = Indent::_(2)
					. "Html::_('stylesheet', "
					. "modules/" . $module->folder_name
					. "/css/mod_admin.css', ['version' => 'auto', 'relative' => true]);";
			}
		}	
	}

	/**
	 * Set the javascript in form
	 *
	 * @param object  $module
	 * @param string  $targetPath
	 * @param array   $bucket
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setJsForm(object &$module, string $targetPath, array &$bucket): void
	{
		if (($javascript = $this->dispenser->get(
				'view_footer', $module->key
			)) !== null
			&& StringHelper::check($javascript))
		{
			// make sure this script does not have PHP
			if (strpos((string) $javascript, '<?php') === false)
			{
				// make sure the field is added
				$module->add_scripts_field = true;

				// add the JavaScript file
				$this->folder->create($module->folder_path . '/js');

				// add the CSS file
				$file_details = [
					'path' => $module->folder_path . '/js/mod_admin.js',
					'name' => 'mod_admin.js',
					'zip'  => 'mod_admin.js'
				];

				$this->file->write(
					$file_details['path'],
					Placefix::_h('BOM') . PHP_EOL
					. PHP_EOL . $javascript
				);

				$this->files->appendArray($module->key, $file_details);

				// count the file created
				$this->counter->file++;

				// add the field script
				$bucket[] = Indent::_(2) . "//"
					. Line::_(__Line__, __Class__) . " Custom JS";
				$bucket[] = Indent::_(2)
					. "Html::_('script', "
					. "/modules/" . $module->folder_name
					. "/js/mod_admin.js', ['version' => 'auto', 'relative' => true]);";
			}
		}	
	}

	/**
	 * Set the form folders and files as needed
	 *
	 * @param object  $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setForms(object &$module): void
	{
		if (isset($module->form_files)
			&& ArrayHelper::check(
				$module->form_files
			))
		{
			// create forms folder
			$this->folder->create($module->folder_path . '/forms');

			// set the template files
			foreach ($module->form_files as $file => $fields)
			{
				// set file details
				$file_details = [
					'path' => $module->folder_path . '/forms/' . $file . '.xml',
					'name' => $file . '.xml',
					'zip' => 'forms/' . $file . '.xml'
				];

				// build basic XML
				$xml = '<?xml version="1.0" encoding="utf-8"?>';
				$xml .= PHP_EOL . '<!--' . Line::_(__Line__, __Class__)
					. ' default paths of ' . $file
					. ' form points to ' . $this->config->component_code_name
					. ' -->';

				// search if we must add the component path
				$add_component_path = false;
				foreach ($fields as $field_name => $fieldsets)
				{
					if (!$add_component_path)
					{
						foreach ($fieldsets as $fieldset => $field)
						{
							if (!$add_component_path
								&& isset(
									$module->fieldsets_paths[$file
									. $field_name . $fieldset]
								)
								&& $module->fieldsets_paths[$file
								. $field_name . $fieldset] == 1)
							{
								$add_component_path = true;
							}
						}
					}
				}

				// only add if part of the component field types path is required
				if ($add_component_path)
				{
					$xml .= PHP_EOL . '<form';

					if ($this->config->get('joomla_version', 3) == 3)
					{
						$xml .= PHP_EOL . Indent::_(1)
							. 'addrulepath="/administrator/components/com_'
							. $this->config->component_code_name
							. '/models/rules"';
						$xml .= PHP_EOL . Indent::_(1)
							. 'addfieldpath="/administrator/components/com_'
							. $this->config->component_code_name
							. '/models/fields"';
					}
					else
					{
						$xml .= PHP_EOL . Indent::_(1)
							. 'addruleprefix="' . $this->config->namespace_prefix
							. '\Component\\' . StringHelper::safe($this->config->component_code_name, 'F')
							. '\Administrator\Rule"';
						$xml .= PHP_EOL . Indent::_(1)
							.'addfieldprefix="' . $this->config->namespace_prefix
							. '\Component\\' . StringHelper::safe($this->config->component_code_name, 'F')
							. '\Administrator\Field"';
					}

					$xml .= PHP_EOL . '>';
				}
				else
				{
					$xml .= PHP_EOL . '<form>';
				}

				// add the fields
				foreach ($fields as $field_name => $fieldsets)
				{
					// check if we have an double fields naming set
					$field_name_inner = '';
					$field_name_outer = $field_name;
					if (strpos((string)$field_name, '.') !== false)
					{
						$field_names = explode('.', (string)$field_name);
						if (count((array)$field_names) == 2)
						{
							$field_name_outer = $field_names[0];
							$field_name_inner = $field_names[1];
						}
					}
					$xml .= PHP_EOL . Indent::_(1)
						. '<fields name="' . $field_name_outer
						. '">';
					foreach ($fieldsets as $fieldset => $field)
					{
						// default to the field set name
						$label = $fieldset;
						if (isset($module->fieldsets_label[$file . $field_name . $fieldset]))
						{
							$label = $module->fieldsets_label[$file . $field_name . $fieldset];
						}

						// add path to module rules and custom fields
						if (isset($module->fieldsets_paths[$file . $field_name . $fieldset])
							&& ($module->fieldsets_paths[$file . $field_name . $fieldset] == 2
								|| $module->fieldsets_paths[$file . $field_name . $fieldset] == 3))
						{
							if ($module->target == 2)
							{
								if (!isset($module->add_rule_path[$file . $field_name . $fieldset]))
								{
									$module->add_rule_path[$file . $field_name . $fieldset] =
										'/administrator/modules/' . $module->file_name . '/rules';
								}

								if (!isset($module->add_field_path[$file . $field_name . $fieldset]))
								{
									$module->add_field_path[$file . $field_name . $fieldset] =
										'/administrator/modules/' . $module->file_name . '/fields';
								}
							}
							else
							{
								if (!isset($module->add_rule_path[$file . $field_name . $fieldset]))
								{
									$module->add_rule_path[$file . $field_name . $fieldset] =
										'/modules/' . $module->file_name . '/rules';
								}

								if (!isset($module->add_field_path[$file . $field_name . $fieldset]))
								{
									$module->add_field_path[$file . $field_name . $fieldset] =
										'/modules/' . $module->file_name . '/fields';
								}
							}
						}

						// add path to module rules and custom fields
						if (isset($module->add_rule_path[$file . $field_name . $fieldset])
							|| isset($module->add_field_path[$file . $field_name . $fieldset]))
						{

							$xml .= PHP_EOL . Indent::_(1) . '<!--'
								. Line::_(__Line__, __Class__) . ' default paths of '
								. $fieldset . ' fieldset points to the module -->';

							$xml .= PHP_EOL . Indent::_(1) . '<fieldset name="'
								. $fieldset . '" label="' . $label . '"';

							if (isset($module->add_rule_path[$file . $field_name . $fieldset]))
							{
								$xml .= PHP_EOL . Indent::_(2)
									. 'addrulepath="' . $module->add_rule_path[$file . $field_name . $fieldset] . '"';
							}

							if (isset($module->add_field_path[$file . $field_name . $fieldset]))
							{
								$xml .= PHP_EOL . Indent::_(2)
									. 'addfieldpath="' . $module->add_field_path[$file . $field_name . $fieldset] . '"';
							}

							$xml .= PHP_EOL . Indent::_(1) . '>';
						}
						else
						{
							$xml .= PHP_EOL . Indent::_(1) . '<fieldset name="'
								. $fieldset . '" label="' . $label . '">';
						}

						// check if we have an inner field set
						if (StringHelper::check(
							$field_name_inner
						))
						{
							$xml .= PHP_EOL . Indent::_(1)
								. '<fields name="'
								. $field_name_inner . '">';
						}

						// add the placeholder of the fields
						$xml .= Placefix::_h('FIELDSET_' . $file
							. $field_name . $fieldset);

						// check if we have an inner field set
						if (StringHelper::check(
							$field_name_inner
						))
						{
							$xml .= PHP_EOL . Indent::_(1)
								. '</fields>';
						}
						$xml .= PHP_EOL . Indent::_(1)
							. '</fieldset>';
					}
					$xml .= PHP_EOL . Indent::_(1) . '</fields>';
				}
				$xml .= PHP_EOL . '</form>';

				// add xml to file
				$this->file->write($file_details['path'], $xml);
				$this->files->appendArray($module->key, $file_details);

				// count the file created
				$this->counter->file++;
			}
		}
	}

	/**
	 * Set the sql stuff
	 *
	 * @param object  $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setSQL(object $module): void
	{
		if ($module->add_sql || $module->add_sql_uninstall)
		{
			// create SQL folder
			$this->folder->create($module->folder_path . '/sql');

			// create mysql folder
			$this->folder->create(
				$module->folder_path . '/sql/mysql'
			);

			// now set the install file
			if ($module->add_sql)
			{
				$this->file->write(
					$module->folder_path . '/sql/mysql/install.sql',
					$module->sql
				);

				// count the file created
				$this->counter->file++;
			}

			// now set the uninstall file
			if ($module->add_sql_uninstall)
			{
				$this->file->write(
					$module->folder_path
					. '/sql/mysql/uninstall.sql',
					$module->sql_uninstall
				);

				// count the file created
				$this->counter->file++;
			}
		}
	}

	/**
	 * Set the files
	 *
	 * @param object  $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setFiles(object $module): void
	{
		if (isset($module->files)
			&& ArrayHelper::check($module->files))
		{
			// add to component files
			foreach ($module->files as $file)
			{
				// set the pathfinder
				$file['target_type'] = $module->target_type;
				$file['target_id'] = $module->id;

				$this->component->appendArray('files', $file);
			}
		}
	}

	/**
	 * Set the folders
	 *
	 * @param object  $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setFolders(object $module): void
	{
		if (isset($module->folders)
			&& ArrayHelper::check($module->folders))
		{
			// add to component folders
			foreach ($module->folders as $folder)
			{
				// set the pathfinder
				$folder['target_type'] = $module->target_type;
				$folder['target_id'] = $module->id;

				$this->component->appendArray('folders', $folder);
			}
		}
	}

	/**
	 * Set the urls
	 *
	 * @param object  $module
	 *
	 * @return void
	 * @since 3.2.0
	 */
	protected function setUrls(object &$module): void
	{
		if (isset($module->urls)
			&& ArrayHelper::check($module->urls))
		{
			// add to component urls
			foreach ($module->urls as &$url)
			{
				// should we add the local folder
				if (isset($url['type']) && $url['type'] > 1
					&& isset($url['url'])
					&& StringHelper::check(
						$url['url']
					))
				{
					// set file name
					$fileName = basename((string)$url['url']);

					// get the file contents
					$data = FileHelper::getContent(
						$url['url']
					);

					// build sub path
					if (strpos($fileName, '.js') !== false)
					{
						$path = '/js';
					}
					elseif (strpos($fileName, '.css') !== false)
					{
						$path = '/css';
					}
					else
					{
						$path = '';
					}

					// create sub media path if not set
					$this->folder->create(
						$module->folder_path . $path
					);

					// set the path to module file
					$url['path'] = $module->folder_path . $path
						. '/' . $fileName; // we need this for later

					// write data to path
					$this->file->write($url['path'], $data);

					// count the file created
					$this->counter->file++;
				}
			}
		}
	}
}

