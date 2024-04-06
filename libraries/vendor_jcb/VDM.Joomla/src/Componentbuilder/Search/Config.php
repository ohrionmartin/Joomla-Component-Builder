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


use Joomla\CMS\Factory;
use Joomla\Input\Input;
use VDM\Joomla\Abstraction\BaseConfig;


/**
 * Search Configurations
 * 
 * @since 3.2.0
 */
class Config extends BaseConfig
{
	/**
	 * Hold a JInput object for easier access to the input variables.
	 *
	 * @var    Input
	 * @since 3.2.0
	 */
	protected Input $input;

	/**
	 * Constructor
	 *
	 * @param Input|null    $input  Input
	 *
	 * @throws \Exception
	 * @since 3.2.0
	 */
	public function __construct(?Input $input = null)
	{
		$this->input = $input ?: Factory::getApplication()->input;

		// run parent constructor
		parent::__construct();
	}

	/**
	 * get type search being preformed
	 *
	 * @return  int   the search type 1 = search, 2 = search & replace
	 * @since 3.2.0
	 */
	protected function getTypesearch(): ?int
	{
		return $this->input->get('type_search', 1, 'INT');
	}

	/**
	 * get posted search value
	 *
	 * @return  string|null  Raw search value
	 * @since 3.2.0
	 */
	protected function getSearchvalue(): ?string
	{
		return $this->input->get('search_value', null, 'RAW');
	}

	/**
	 * get posted replace value
	 *
	 * @return  string  Raw replace value
	 * @since 3.2.0
	 */
	protected function getReplacevalue(): string
	{
		return $this->input->get('replace_value', '', 'RAW');
	}

	/**
	 * get posted search match case
	 *
	 * @return  int  Match case
	 * @since 3.2.0
	 */
	protected function getMatchcase(): int
	{
		return $this->input->get('match_case', 0, 'INT');
	}

	/**
	 * get posted search whole word
	 *
	 * @return  int  Whole word
	 * @since 3.2.0
	 */
	protected function getWholeword(): int
	{
		return $this->input->get('whole_word', 0, 'INT');
	}

	/**
	 * get posted search regex
	 *
	 * @return  int  Regex
	 * @since 3.2.0
	 */
	protected function getRegexsearch(): int
	{
		return $this->input->get('regex_search', 0, 'INT');
	}

	/**
	 * get posted component
	 *
	 * @return  int  Component ID
	 * @since 3.2.0
	 */
	protected function getComponentid(): int
	{
		return $this->input->get('component_id', 0, 'INT');
	}

	/**
	 * get posted area/table
	 *
	 * @return  string|null  Table name
	 * @since 3.2.0
	 */
	protected function getTablename(): ?string
	{
		return $this->input->get('table_name', null, 'word');
	}

	/**
	 * get posted field
	 *
	 * @return  string|null  Field name
	 * @since 3.2.0
	 */
	protected function getFieldname(): ?string
	{
		return $this->input->get('field_name', null, 'word');
	}

	/**
	 * get posted item id
	 *
	 * @return  int  Item id
	 * @since 3.2.0
	 */
	protected function getItemid(): int
	{
		return $this->input->get('item_id', 0, 'INT');
	}

	/**
	 * get field counter
	 *
	 * @return  int   we start at 0
	 * @since 3.2.0
	 */
	protected function getFieldcounter(): int
	{
		return 0;
	}

	/**
	 * get line counter
	 *
	 * @return  int   we start at 0
	 * @since 3.2.0
	 */
	protected function getLinecounter(): int
	{
		return 0;
	}

	/**
	 * get the start marker
	 *
	 * @return  string  The string to use as the start marker
	 * @since 3.2.0
	 */
	protected function getMarkerstart(): string
	{
		return '{+' . '|' . '=[';
	}

	/**
	 * get the end marker
	 *
	 * @return  string  The string to use as the end marker
	 * @since 3.2.0
	 */
	protected function getMarkerend(): string
	{
		return ']=' . '|' . '+}';
	}

}

