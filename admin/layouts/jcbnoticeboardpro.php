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



use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\LayoutHelper;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;

// No direct access to this file
defined('JPATH_BASE') or die;



?>
<div  class="well well-small">
	<h2 class="module-title nav-header"><?= Text::_('COM_COMPONENTBUILDER_JCB_PRO_NOTICE_BOARD') ?></h2>
	<div class="proboard-md"><small><?= Text::_('COM_COMPONENTBUILDER_THE_PRO_BOARD_IS_LOADING') ?><span class="loading-dots">.</span></small></div>
	<div style="text-align:right;"><small><a href="https://vdm.bz/get-jcb-pro-membership" target="_blank" style="color:gray">JCB PRO</a></small></div>
</div>
