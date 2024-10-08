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

namespace VDM\Joomla\Componentbuilder\Interfaces\Plugin;


/**
 * Structure Interface
 * 
 * @since 5.0.0
 */
interface StructureInterface
{
	/**
	 * Build the Plug-ins files, folders, url's and configure
	 *
	 * @return   void
	 * @since    5.0.0
	 */
	public function build();
}

