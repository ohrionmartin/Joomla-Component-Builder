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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;

?>
<tr>
	<?php if ($this->canEdit&& $this->canState): ?>
		<th width="1%" class="nowrap center hidden-phone">
			<?php echo Html::_('searchtools.sort', '', 'a.ordering', $this->listDirn, $this->listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
		</th>
		<th width="20" class="nowrap center">
			<?php echo Html::_('grid.checkall'); ?>
		</th>
	<?php else: ?>
		<th width="20" class="nowrap center hidden-phone">
			&#9662;
		</th>
		<th width="20" class="nowrap center">
			&#9632;
		</th>
	<?php endif; ?>
	<th class="nowrap" >
			<?php echo Text::_('COM_COMPONENTBUILDER_REPOSITORY_SYSTEM_NAME_LABEL'); ?>
	</th>
	<th class="nowrap hidden-phone" >
			<?php echo Html::_('searchtools.sort', 'COM_COMPONENTBUILDER_REPOSITORY_ORGANISATION_LABEL', 'a.organisation', $this->listDirn, $this->listOrder); ?>
	</th>
	<th class="nowrap hidden-phone" >
			<?php echo Html::_('searchtools.sort', 'COM_COMPONENTBUILDER_REPOSITORY_REPOSITORY_LABEL', 'a.repository', $this->listDirn, $this->listOrder); ?>
	</th>
	<th class="nowrap hidden-phone" >
			<?php echo Html::_('searchtools.sort', 'COM_COMPONENTBUILDER_REPOSITORY_TARGET_LABEL', 'a.target', $this->listDirn, $this->listOrder); ?>
	</th>
	<th class="nowrap hidden-phone" >
			<?php echo Html::_('searchtools.sort', 'COM_COMPONENTBUILDER_REPOSITORY_TYPE_LABEL', 'a.type', $this->listDirn, $this->listOrder); ?>
	</th>
	<th class="nowrap hidden-phone" >
			<?php echo Html::_('searchtools.sort', 'COM_COMPONENTBUILDER_REPOSITORY_BASE_LABEL', 'a.base', $this->listDirn, $this->listOrder); ?>
	</th>
	<?php if ($this->canState): ?>
		<th width="10" class="nowrap center" >
			<?php echo Html::_('searchtools.sort', 'COM_COMPONENTBUILDER_REPOSITORY_STATUS', 'a.published', $this->listDirn, $this->listOrder); ?>
		</th>
	<?php else: ?>
		<th width="10" class="nowrap center" >
			<?php echo Text::_('COM_COMPONENTBUILDER_REPOSITORY_STATUS'); ?>
		</th>
	<?php endif; ?>
	<th width="5" class="nowrap center hidden-phone" >
			<?php echo Html::_('searchtools.sort', 'COM_COMPONENTBUILDER_REPOSITORY_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
	</th>
</tr>