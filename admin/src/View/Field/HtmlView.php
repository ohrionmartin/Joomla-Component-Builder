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
namespace VDM\Component\Componentbuilder\Administrator\View\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Document\Document;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use VDM\Joomla\Utilities\StringHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Field Html View class
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Field view display method
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 * @since  1.6
	 */
	public function display($tpl = null)
	{
		// set params
		$this->params = ComponentHelper::getParams('com_componentbuilder');
		$this->useCoreUI = true;
		// Assign the variables
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->styles = $this->get('Styles');
		$this->scripts = $this->get('Scripts');
		$this->state = $this->get('State');
		// get action permissions
		$this->canDo = ComponentbuilderHelper::getActions('field', $this->item);
		// get input
		$jinput = Factory::getApplication()->input;
		$this->ref = $jinput->get('ref', 0, 'word');
		$this->refid = $jinput->get('refid', 0, 'int');
		$return = $jinput->get('return', null, 'base64');
		// set the referral string
		$this->referral = '';
		if ($this->refid && $this->ref)
		{
			// return to the item that referred to this item
			$this->referral = '&ref=' . (string) $this->ref . '&refid=' . (int) $this->refid;
		}
		elseif($this->ref)
		{
			// return to the list view that referred to this item
			$this->referral = '&ref=' . (string) $this->ref;
		}
		// check return value
		if (!is_null($return))
		{
			// add the return value
			$this->referral .= '&return=' . (string) $return;
		}

		// Set the toolbar
		$this->addToolBar();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors), 500);
		}

		// Set the html view document stuff
		$this->_prepareDocument();

		// Display the template
		parent::display($tpl);
	}


	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function addToolbar(): void
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$user = Factory::getApplication()->getIdentity();
		$userId	= $user->id;
		$isNew = $this->item->id == 0;

		ToolbarHelper::title( Text::_($isNew ? 'COM_COMPONENTBUILDER_FIELD_NEW' : 'COM_COMPONENTBUILDER_FIELD_EDIT'), 'pencil-2 article-add');
		// Built the actions for new and existing records.
		if (StringHelper::check($this->referral))
		{
			if ($this->canDo->get('field.create') && $isNew)
			{
				// We can create the record.
				ToolbarHelper::save('field.save', 'JTOOLBAR_SAVE');
			}
			elseif ($this->canDo->get('field.edit'))
			{
				// We can save the record.
				ToolbarHelper::save('field.save', 'JTOOLBAR_SAVE');
			}
			if ($isNew)
			{
				// Do not creat but cancel.
				ToolbarHelper::cancel('field.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				// We can close it.
				ToolbarHelper::cancel('field.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		else
		{
			if ($isNew)
			{
				// For new records, check the create permission.
				if ($this->canDo->get('field.create'))
				{
					ToolbarHelper::apply('field.apply', 'JTOOLBAR_APPLY');
					ToolbarHelper::save('field.save', 'JTOOLBAR_SAVE');
					ToolbarHelper::custom('field.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				};
				ToolbarHelper::cancel('field.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				if ($this->canDo->get('field.edit'))
				{
					// We can save the new record
					ToolbarHelper::apply('field.apply', 'JTOOLBAR_APPLY');
					ToolbarHelper::save('field.save', 'JTOOLBAR_SAVE');
					// We can save this record, but check the create permission to see
					// if we can return to make a new one.
					if ($this->canDo->get('field.create'))
					{
						ToolbarHelper::custom('field.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
					}
				}
				$canVersion = ($this->canDo->get('core.version') && $this->canDo->get('field.version'));
				if ($this->state->params->get('save_history', 1) && $this->canDo->get('field.edit') && $canVersion)
				{
					ToolbarHelper::versions('com_componentbuilder.field', $this->item->id);
				}
				if ($this->canDo->get('field.create'))
				{
					ToolbarHelper::custom('field.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
				ToolbarHelper::cancel('field.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		ToolbarHelper::divider();
		ToolbarHelper::inlinehelp();
		// set help url for this view if found
		$this->help_url = ComponentbuilderHelper::getHelpUrl('field');
		if (StringHelper::check($this->help_url))
		{
			ToolbarHelper::help('COM_COMPONENTBUILDER_HELP_MANAGER', false, $this->help_url);
		}
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var     The output to escape.
	 * @param   bool   $shorten The switch to shorten.
	 * @param   int    $length  The shorting length.
	 *
	 * @return  mixed  The escaped value.
	 * @since   1.6
	 */
	public function escape($var, bool $shorten = true, int $length = 30)
	{
		if (!is_string($var))
		{
			return $var;
		}

		return StringHelper::html($var, $this->_charset ?? 'UTF-8', $shorten, $length);
	}

	/**
	 * Prepare some document related stuff.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function _prepareDocument(): void
	{
		// Load jQuery
		Html::_('jquery.framework');
		$isNew = ($this->item->id < 1);
		$this->getDocument()->setTitle(Text::_($isNew ? 'COM_COMPONENTBUILDER_FIELD_NEW' : 'COM_COMPONENTBUILDER_FIELD_EDIT'));
		// add styles
		foreach ($this->styles as $style)
		{
			Html::_('stylesheet', $style, ['version' => 'auto']);
		}
		// Add Ajax Token
		$this->getDocument()->addScriptDeclaration("var token = '" . Session::getFormToken() . "';");
		// add scripts
		foreach ($this->scripts as $script)
		{
			Html::_('script', $script, ['version' => 'auto']);
		}


		// add the Uikit v2 style sheets
		Html::_('stylesheet', 'media/com_componentbuilder/uikit-v2/css/uikit.gradient.min.css', ['version' => 'auto']);
		// add Uikit v2 JavaScripts
		Html::_('script', 'media/com_componentbuilder/uikit-v2/js/uikit.min.js', ['version' => 'auto']);

		// add the Uikit v2 extra style sheets
		Html::_('stylesheet', 'media/com_componentbuilder/uikit-v2/css/components/notify.gradient.min.css', ['version' => 'auto']);
		// add Uikit v2 extra JavaScripts
		Html::_('script', 'media/com_componentbuilder/uikit-v2/js/components/lightbox.min.js', ['version' => 'auto']);
		Html::_('script', 'media/com_componentbuilder/uikit-v2/js/components/notify.min.js', ['version' => 'auto']);
		// add var key
		$this->getDocument()->addScriptDeclaration("var vastDevMod = '" . $this->get('VDM') . "';");
		// add return_here
		$this->getDocument()->addScriptDeclaration("var return_here = '" . urlencode(base64_encode((string) \JUri::getInstance())) . "';");
		// add the libs for subform (since not adding it via xml but ajax)
		Factory::getApplication()
			->getDocument()
			->getWebAssetManager()
			->useScript('webcomponent.field-subform')
			->usePreset('choicesjs')
			->useScript('webcomponent.field-fancy-select');
		Factory::getApplication()
			->getDocument()
			->addStyleDeclaration('.subform-table-sublayout-section .controls { margin-left: 0px }');
		// set some lang
		Text::script('COM_COMPONENTBUILDER_PROPERTY_ALREADY_SELECTED_TRY_ANOTHER');
		Text::script('COM_COMPONENTBUILDER_TYPE_OR_SELECT_SOME_OPTIONS');
		Text::script('COM_COMPONENTBUILDER_NO_RESULTS_MATCH');
		Text::script('COM_COMPONENTBUILDER_SELECT_A_PROPERTY');
		Text::script('COM_COMPONENTBUILDER_NO_DESCRIPTION_FOUND');
	}
}
