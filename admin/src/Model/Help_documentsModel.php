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
 * Help_documents List Model
 *
 * @since  1.6
 */
class Help_documentsModel extends ListModel
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
		'administrator/components/com_componentbuilder/assets/css/help_documents.css'
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
				'a.ordering','ordering',
				'a.created_by','created_by',
				'a.modified_by','modified_by',
				'a.type','type',
				'a.location','location',
				'a.admin_view','admin_view',
				'a.site_view','site_view',
				'a.title','title'
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

		$type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type');
		if ($formSubmited)
		{
			$type = $app->input->post->get('type');
			$this->setState('filter.type', $type);
		}

		$location = $this->getUserStateFromRequest($this->context . '.filter.location', 'filter_location');
		if ($formSubmited)
		{
			$location = $app->input->post->get('location');
			$this->setState('filter.location', $location);
		}

		$admin_view = $this->getUserStateFromRequest($this->context . '.filter.admin_view', 'filter_admin_view');
		if ($formSubmited)
		{
			$admin_view = $app->input->post->get('admin_view');
			$this->setState('filter.admin_view', $admin_view);
		}

		$site_view = $this->getUserStateFromRequest($this->context . '.filter.site_view', 'filter_site_view');
		if ($formSubmited)
		{
			$site_view = $app->input->post->get('site_view');
			$this->setState('filter.site_view', $site_view);
		}

		$title = $this->getUserStateFromRequest($this->context . '.filter.title', 'filter_title');
		if ($formSubmited)
		{
			$title = $app->input->post->get('title');
			$this->setState('filter.title', $title);
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
				$access = ($user->authorise('help_document.access', 'com_componentbuilder.help_document.' . (int) $item->id) && $user->authorise('help_document.access', 'com_componentbuilder'));
				if (!$access)
				{
					unset($items[$nr]);
					continue;
				}

				// decode groups
				$groupsArray = json_decode($item->groups, true);
				if (UtilitiesArrayHelper::check($groupsArray))
				{
					$groupsNames = [];
					foreach ($groupsArray as $groups)
					{
						$groupsNames[] = ComponentbuilderHelper::getGroupName($groups);
					}
					$item->groups =  implode(', ', $groupsNames);
				}
			}
		}

		// set selection value to a translatable value
		if (UtilitiesArrayHelper::check($items))
		{
			foreach ($items as $nr => &$item)
			{
				// convert type
				$item->type = $this->selectionTranslation($item->type, 'type');
				// convert location
				$item->location = $this->selectionTranslation($item->location, 'location');
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
		// Array of type language strings
		if ($name === 'type')
		{
			$typeArray = array(
				0 => 'COM_COMPONENTBUILDER_HELP_DOCUMENT_SELECT_AN_OPTION',
				1 => 'COM_COMPONENTBUILDER_HELP_DOCUMENT_JOOMLA_ARTICLE',
				2 => 'COM_COMPONENTBUILDER_HELP_DOCUMENT_TEXT',
				3 => 'COM_COMPONENTBUILDER_HELP_DOCUMENT_URL'
			);
			// Now check if value is found in this array
			if (isset($typeArray[$value]) && StringHelper::check($typeArray[$value]))
			{
				return $typeArray[$value];
			}
		}
		// Array of location language strings
		if ($name === 'location')
		{
			$locationArray = array(
				1 => 'COM_COMPONENTBUILDER_HELP_DOCUMENT_ADMIN',
				2 => 'COM_COMPONENTBUILDER_HELP_DOCUMENT_SITE'
			);
			// Now check if value is found in this array
			if (isset($locationArray[$value]) && StringHelper::check($locationArray[$value]))
			{
				return $locationArray[$value];
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
		$query->from($db->quoteName('#__componentbuilder_help_document', 'a'));

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
				$query->where('(a.title LIKE '.$search.' OR a.type LIKE '.$search.' OR a.location LIKE '.$search.' OR a.admin_view LIKE '.$search.' OR g. LIKE '.$search.' OR a.site_view LIKE '.$search.' OR h. LIKE '.$search.')');
			}
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
		// Filter by Location.
		$_location = $this->getState('filter.location');
		if (is_numeric($_location))
		{
			if (is_float($_location))
			{
				$query->where('a.location = ' . (float) $_location);
			}
			else
			{
				$query->where('a.location = ' . (int) $_location);
			}
		}
		elseif (StringHelper::check($_location))
		{
			$query->where('a.location = ' . $db->quote($db->escape($_location)));
		}
		// Filter by Admin_view.
		$_admin_view = $this->getState('filter.admin_view');
		if (is_numeric($_admin_view))
		{
			if (is_float($_admin_view))
			{
				$query->where('a.admin_view = ' . (float) $_admin_view);
			}
			else
			{
				$query->where('a.admin_view = ' . (int) $_admin_view);
			}
		}
		elseif (StringHelper::check($_admin_view))
		{
			$query->where('a.admin_view = ' . $db->quote($db->escape($_admin_view)));
		}
		// Filter by Site_view.
		$_site_view = $this->getState('filter.site_view');
		if (is_numeric($_site_view))
		{
			if (is_float($_site_view))
			{
				$query->where('a.site_view = ' . (float) $_site_view);
			}
			else
			{
				$query->where('a.site_view = ' . (int) $_site_view);
			}
		}
		elseif (StringHelper::check($_site_view))
		{
			$query->where('a.site_view = ' . $db->quote($db->escape($_site_view)));
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
		$id .= ':' . $this->getState('filter.ordering');
		$id .= ':' . $this->getState('filter.created_by');
		$id .= ':' . $this->getState('filter.modified_by');
		$id .= ':' . $this->getState('filter.type');
		$id .= ':' . $this->getState('filter.location');
		$id .= ':' . $this->getState('filter.admin_view');
		$id .= ':' . $this->getState('filter.site_view');
		$id .= ':' . $this->getState('filter.title');

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
			$query->from($db->quoteName('#__componentbuilder_help_document'));
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
				$query->update($db->quoteName('#__componentbuilder_help_document'))->set($fields)->where($conditions); 

				$db->setQuery($query);

				return $db->execute();
			}
		}

		return false;
	}
}
