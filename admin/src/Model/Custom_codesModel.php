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
namespace VDM\Component\Componentbuilder\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use Joomla\CMS\Helper\TagsHelper;
use VDM\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use VDM\Joomla\Utilities\ObjectHelper;
use VDM\Joomla\Utilities\StringHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Custom_codes List Model
 *
 * @since  1.6
 */
class Custom_codesModel extends ListModel
{
	/**
	 * The application object.
	 *
	 * @var   CMSApplicationInterface  The application instance.
	 * @since 3.2.0
	 */
	protected CMSApplicationInterface $app;

	/**
	 * The styles array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $styles = [
		'administrator/components/com_componentbuilder/assets/css/admin.css',
		'administrator/components/com_componentbuilder/assets/css/custom_codes.css'
 	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'administrator/components/com_componentbuilder/assets/js/admin.js'
 	];

	/**
	 * Constructor
	 *
	 * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   ?MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'a.id','id',
				'a.published','published',
				'a.access','access',
				'a.ordering','ordering',
				'a.created_by','created_by',
				'a.modified_by','modified_by',
				'g.system_name','component',
				'a.target','target',
				'a.type','type',
				'a.comment_type','comment_type',
				'a.path','path',
				'a.joomla_version','joomla_version'
			);
		}

		parent::__construct($config, $factory);

		$this->app ??= Factory::getApplication();
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 * @since   1.7.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = $this->app;

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		// Check if the form was submitted
		$formSubmited = $app->input->post->get('form_submited');

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		if ($formSubmited)
		{
			$access = $app->input->post->get('access');
			$this->setState('filter.access', $access);
		}

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$created_by = $this->getUserStateFromRequest($this->context . '.filter.created_by', 'filter_created_by', '');
		$this->setState('filter.created_by', $created_by);

		$created = $this->getUserStateFromRequest($this->context . '.filter.created', 'filter_created');
		$this->setState('filter.created', $created);

		$sorting = $this->getUserStateFromRequest($this->context . '.filter.sorting', 'filter_sorting', 0, 'int');
		$this->setState('filter.sorting', $sorting);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$component = $this->getUserStateFromRequest($this->context . '.filter.component', 'filter_component');
		if ($formSubmited)
		{
			$component = $app->input->post->get('component');
			$this->setState('filter.component', $component);
		}

		$target = $this->getUserStateFromRequest($this->context . '.filter.target', 'filter_target');
		if ($formSubmited)
		{
			$target = $app->input->post->get('target');
			$this->setState('filter.target', $target);
		}

		$type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type');
		if ($formSubmited)
		{
			$type = $app->input->post->get('type');
			$this->setState('filter.type', $type);
		}

		$comment_type = $this->getUserStateFromRequest($this->context . '.filter.comment_type', 'filter_comment_type');
		if ($formSubmited)
		{
			$comment_type = $app->input->post->get('comment_type');
			$this->setState('filter.comment_type', $comment_type);
		}

		$path = $this->getUserStateFromRequest($this->context . '.filter.path', 'filter_path');
		if ($formSubmited)
		{
			$path = $app->input->post->get('path');
			$this->setState('filter.path', $path);
		}

		$joomla_version = $this->getUserStateFromRequest($this->context . '.filter.joomla_version', 'filter_joomla_version');
		if ($formSubmited)
		{
			$joomla_version = $app->input->post->get('joomla_version');
			$this->setState('filter.joomla_version', $joomla_version);
		}

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 * @since   1.6
	 */
	public function getItems()
	{
		// Check in items
		$this->checkInNow();

		// load parent items
		$items = parent::getItems();

		// Set values to display correctly.
		if (UtilitiesArrayHelper::check($items))
		{
			// Get the user object if not set.
			if (!isset($user) || !ObjectHelper::check($user))
			{
				$user = $this->getCurrentUser();
			}
			foreach ($items as $nr => &$item)
			{
				// Remove items the user can't access.
				$access = ($user->authorise('custom_code.access', 'com_componentbuilder.custom_code.' . (int) $item->id) && $user->authorise('custom_code.access', 'com_componentbuilder'));
				if (!$access)
				{
					unset($items[$nr]);
					continue;
				}

				// [1641]=> Target (code action)
				$item->target_code = $item->target;
			}
		}

		if (UtilitiesArrayHelper::check($items) && !isset($_export))
		{
			foreach ($items as $nr => &$item) 
			{
				if ($item->target == 2)
				{
					$item->component_system_name = $item->system_name;
					$item->path = '[CUSTO'.'MCODE='.$item->id.']'; // so it is not detected
					if (StringHelper::check($item->function_name))
					{
						$item->path =  '[CUSTO'.'MCODE='.$item->function_name.']'; // so it is not detected
					}
					$item->type = 2;
				}
			}
		}

		// set selection value to a translatable value
		if (UtilitiesArrayHelper::check($items))
		{
			foreach ($items as $nr => &$item)
			{
				// convert target
				$item->target = $this->selectionTranslation($item->target, 'target');
				// convert type
				$item->type = $this->selectionTranslation($item->type, 'type');
				// convert comment_type
				$item->comment_type = $this->selectionTranslation($item->comment_type, 'comment_type');
			}
		}


		// return items
		return $items;
	}

	/**
	 * Method to convert selection values to translatable string.
	 *
	 * @return  string   The translatable string.
	 */
	public function selectionTranslation($value,$name)
	{
		// Array of target language strings
		if ($name === 'target')
		{
			$targetArray = array(
				2 => 'COM_COMPONENTBUILDER_CUSTOM_CODE_JCB_MANUAL',
				1 => 'COM_COMPONENTBUILDER_CUSTOM_CODE_HASH_AUTOMATION'
			);
			// Now check if value is found in this array
			if (isset($targetArray[$value]) && StringHelper::check($targetArray[$value]))
			{
				return $targetArray[$value];
			}
		}
		// Array of type language strings
		if ($name === 'type')
		{
			$typeArray = array(
				1 => 'COM_COMPONENTBUILDER_CUSTOM_CODE_REPLACEMENT',
				2 => 'COM_COMPONENTBUILDER_CUSTOM_CODE_INSERTION'
			);
			// Now check if value is found in this array
			if (isset($typeArray[$value]) && StringHelper::check($typeArray[$value]))
			{
				return $typeArray[$value];
			}
		}
		// Array of comment_type language strings
		if ($name === 'comment_type')
		{
			$comment_typeArray = array(
				1 => 'COM_COMPONENTBUILDER_CUSTOM_CODE_PHPJS',
				2 => 'COM_COMPONENTBUILDER_CUSTOM_CODE_HTML'
			);
			// Now check if value is found in this array
			if (isset($comment_typeArray[$value]) && StringHelper::check($comment_typeArray[$value]))
			{
				return $comment_typeArray[$value];
			}
		}
		return $value;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return  string    An SQL query
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Get the user object.
		$user = $this->getCurrentUser();
		// Create a new query object.
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('a.*');

		// From the componentbuilder_item table
		$query->from($db->quoteName('#__componentbuilder_custom_code', 'a'));

		// From the componentbuilder_joomla_component table.
		$query->select($db->quoteName('g.system_name','component_system_name'));
		$query->join('LEFT', $db->quoteName('#__componentbuilder_joomla_component', 'g') . ' ON (' . $db->quoteName('a.component') . ' = ' . $db->quoteName('g.id') . ')');

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published = 0 OR a.published = 1)');
		}

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		// Filter by access level.
		$_access = $this->getState('filter.access');
		if ($_access && is_numeric($_access))
		{
			$query->where('a.access = ' . (int) $_access);
		}
		elseif (UtilitiesArrayHelper::check($_access))
		{
			// Secure the array for the query
			$_access = ArrayHelper::toInteger($_access);
			// Filter by the Access Array.
			$query->where('a.access IN (' . implode(',', $_access) . ')');
		}
		// Implement View Level Access
		if (!$user->authorise('core.options', 'com_componentbuilder'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}
		// Filter by search.
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search) . '%');
				$query->where('(a.component LIKE '.$search.' OR g.system_name LIKE '.$search.' OR a.path LIKE '.$search.' OR a.comment_type LIKE '.$search.' OR a.joomla_version LIKE '.$search.' OR a.function_name LIKE '.$search.' OR a.system_name LIKE '.$search.')');
			}
		}

		// Filter by Component.
		$_component = $this->getState('filter.component');
		if (is_numeric($_component))
		{
			if (is_float($_component))
			{
				$query->where('a.component = ' . (float) $_component);
			}
			else
			{
				$query->where('a.component = ' . (int) $_component);
			}
		}
		elseif (StringHelper::check($_component))
		{
			$query->where('a.component = ' . $db->quote($db->escape($_component)));
		}
		// Filter by Target.
		$_target = $this->getState('filter.target');
		if (is_numeric($_target))
		{
			if (is_float($_target))
			{
				$query->where('a.target = ' . (float) $_target);
			}
			else
			{
				$query->where('a.target = ' . (int) $_target);
			}
		}
		elseif (StringHelper::check($_target))
		{
			$query->where('a.target = ' . $db->quote($db->escape($_target)));
		}
		// Filter by Type.
		$_type = $this->getState('filter.type');
		if (is_numeric($_type))
		{
			if (is_float($_type))
			{
				$query->where('a.type = ' . (float) $_type);
			}
			else
			{
				$query->where('a.type = ' . (int) $_type);
			}
		}
		elseif (StringHelper::check($_type))
		{
			$query->where('a.type = ' . $db->quote($db->escape($_type)));
		}
		// Filter by Comment_type.
		$_comment_type = $this->getState('filter.comment_type');
		if (is_numeric($_comment_type))
		{
			if (is_float($_comment_type))
			{
				$query->where('a.comment_type = ' . (float) $_comment_type);
			}
			else
			{
				$query->where('a.comment_type = ' . (int) $_comment_type);
			}
		}
		elseif (StringHelper::check($_comment_type))
		{
			$query->where('a.comment_type = ' . $db->quote($db->escape($_comment_type)));
		}

		// Add the list ordering clause.
		$orderCol = $this->getState('list.ordering', 'a.id');
		$orderDirn = $this->getState('list.direction', 'desc');
		if ($orderCol != '')
		{
			// Check that the order direction is valid encase we have a field called direction as part of filers.
			$orderDirn = (is_string($orderDirn) && in_array(strtolower($orderDirn), ['asc', 'desc'])) ? $orderDirn : 'desc';
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @return  string  A store id.
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.id');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		// Check if the value is an array
		$_access = $this->getState('filter.access');
		if (UtilitiesArrayHelper::check($_access))
		{
			$id .= ':' . implode(':', $_access);
		}
		// Check if this is only an number or string
		elseif (is_numeric($_access)
		 || StringHelper::check($_access))
		{
			$id .= ':' . $_access;
		}
		$id .= ':' . $this->getState('filter.ordering');
		$id .= ':' . $this->getState('filter.created_by');
		$id .= ':' . $this->getState('filter.modified_by');
		$id .= ':' . $this->getState('filter.component');
		$id .= ':' . $this->getState('filter.target');
		$id .= ':' . $this->getState('filter.type');
		$id .= ':' . $this->getState('filter.comment_type');
		$id .= ':' . $this->getState('filter.path');
		$id .= ':' . $this->getState('filter.joomla_version');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get the styles that have to be included on the view
	 *
	 * @return  array    styles files
	 * @since   4.3
	 */
	public function getStyles(): array
	{
		return $this->styles;
	}

	/**
	 * Method to set the styles that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setStyles(string $path): void
	{
		$this->styles[] = $path;
	}

	/**
	 * Method to get the script that have to be included on the view
	 *
	 * @return  array    script files
	 * @since   4.3
	 */
	public function getScripts(): array
	{
		return $this->scripts;
	}

	/**
	 * Method to set the script that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setScript(string $path): void
	{
		$this->scripts[] = $path;
	}

	/**
	 * Build an SQL query to checkin all items left checked out longer then a set time.
	 *
	 * @return bool
	 * @since 3.2.0
	 */
	protected function checkInNow(): bool
	{
		// Get set check in time
		$time = ComponentHelper::getParams('com_componentbuilder')->get('check_in');

		if ($time)
		{
			// Get a db connection.
			$db = $this->getDatabase();
			// Reset query.
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__componentbuilder_custom_code'));
			// Only select items that are checked out.
			$query->where($db->quoteName('checked_out') . '!=0');
			$db->setQuery($query, 0, 1);
			$db->execute();
			if ($db->getNumRows())
			{
				// Get Yesterdays date.
				$date = Factory::getDate()->modify($time)->toSql();
				// Reset query.
				$query = $db->getQuery(true);

				// Fields to update.
				$fields = array(
					$db->quoteName('checked_out_time') . '=\'0000-00-00 00:00:00\'',
					$db->quoteName('checked_out') . '=0'
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('checked_out') . '!=0', 
					$db->quoteName('checked_out_time') . '<\''.$date.'\''
				);

				// Check table.
				$query->update($db->quoteName('#__componentbuilder_custom_code'))->set($fields)->where($conditions); 

				$db->setQuery($query);

				return $db->execute();
			}
		}

		return false;
	}
}
