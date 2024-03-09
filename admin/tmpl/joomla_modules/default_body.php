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
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;
use VDM\Joomla\Utilities\ArrayHelper;
use VDM\Joomla\Utilities\GetHelper;

// No direct access to this file
defined('_JEXEC') or die;

$edit = "index.php?option=com_componentbuilder&view=joomla_modules&task=joomla_module.edit";

?>
<?php foreach ($this->items as $i => $item): ?>
	<?php
		$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $item->checked_out == $this->user->id || $item->checked_out == 0;
		$userChkOut = Factory::getContainer()->
			get(\Joomla\CMS\User\UserFactoryInterface::class)->
				loadUserById($item->checked_out);
		$canDo = ComponentbuilderHelper::getActions('joomla_module',$item,'joomla_modules');
	?>
	<tr class="row<?php echo $i % 2; ?>">
		<td class="order nowrap center hidden-phone">
		<?php if ($canDo->get('joomla_module.edit.state')): ?>
			<?php
				$iconClass = '';
				if (!$this->saveOrder)
				{
					$iconClass = ' inactive tip-top" hasTooltip" title="' . Html::tooltipText('JORDERINGDISABLED');
				}
			?>
			<span class="sortable-handler<?php echo $iconClass; ?>">
				<i class="icon-menu"></i>
			</span>
			<?php if ($this->saveOrder) : ?>
				<input type="text" style="display:none" name="order[]" size="5"
				value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
			<?php endif; ?>
		<?php else: ?>
			&#8942;
		<?php endif; ?>
		</td>
		<td class="nowrap center">
		<?php if ($canDo->get('joomla_module.edit')): ?>
				<?php if ($item->checked_out) : ?>
					<?php if ($canCheckin) : ?>
						<?php echo Html::_('grid.id', $i, $item->id); ?>
					<?php else: ?>
						&#9633;
					<?php endif; ?>
				<?php else: ?>
					<?php echo Html::_('grid.id', $i, $item->id); ?>
				<?php endif; ?>
		<?php else: ?>
			&#9633;
		<?php endif; ?>
		</td>
		<td class="nowrap">
			<div>
			<?php if ($canDo->get('joomla_module.edit')): ?>
				<a href="<?php echo $edit; ?>&id=<?php echo $item->id; ?>"><?php echo $this->escape($item->system_name); ?></a>
				<?php if ($item->checked_out): ?>
					<?php echo Html::_('jgrid.checkedout', $i, $userChkOut->name, $item->checked_out_time, 'joomla_modules.', $canCheckin); ?>
				<?php endif; ?>
			<?php else: ?>
				<?php echo $this->escape($item->system_name); ?>
			<?php endif; ?> - <b>
			<?php echo $this->escape($item->module_version); ?></b>
			</div>
			<?php
				// setup the buttons
				if (!isset($_buttons) || !ArrayHelper::check($_buttons))
				{
					$_buttons = array();
					$_buttons[0] = array(
						array(
							'view' => 'joomla_module_updates',
							'views' => 'joomla_modules_updates',
							'title' => Text::_('COM_COMPONENTBUILDER_THE_MODULE_UPDATES'),
							'icon' => 'database'),
						array(
							'view' => 'joomla_module_files_folders_urls',
							'views' => 'joomla_modules_files_folders_urls',
							'title' => Text::_('COM_COMPONENTBUILDER_THE_MODULE_FILES_FOLDERS'),
							'icon' => 'briefcase')
						);
				}
			?>
			<div class="btn-group" style="margin: 5px 0 0 0;">
			<?php foreach ($_buttons[0] as $_button): ?>
				<?php if ($canDo->get($_button['view'].'.edit') && ($id = GetHelper::var($_button['view'], $item->id, 'joomla_module', 'id')) !== false): ?>
					<a class="hasTooltip btn btn-mini" href="index.php?option=com_componentbuilder&view=<?php echo $_button['views'] ?>&task=<?php echo $_button['view'] ?>.edit&id=<?php echo $id; ?>&return=<?php echo $this->return_here; ?>" title="<?php echo $_button['title']; ?>" ><span class="icon-<?php echo $_button['icon']; ?>"></span></a>
				<?php elseif ($canDo->get($_button['view'].'.create')): ?>
					<a class="hasTooltip btn btn-mini" href="index.php?option=com_componentbuilder&view=<?php echo $_button['views'] ?>&task=<?php echo $_button['view'] ?>.edit&ref=joomla_module&refid=<?php echo $item->id; ?>&return=<?php echo $this->return_here; ?>" title="<?php echo $_button['title']; ?>" ><span class="icon-<?php echo $_button['icon']; ?>"></span></a>
				<?php endif; ?>
			<?php endforeach; ?>
			</div>
		</td>
		<td class="hidden-phone">
			<?php echo Text::_($item->target); ?>
		</td>
		<td class="hidden-phone">
			<?php echo $this->escape($item->description); ?>
		</td>
		<td class="center">
		<?php if ($canDo->get('joomla_module.edit.state')) : ?>
				<?php if ($item->checked_out) : ?>
					<?php if ($canCheckin) : ?>
						<?php echo Html::_('jgrid.published', $item->published, $i, 'joomla_modules.', true, 'cb'); ?>
					<?php else: ?>
						<?php echo Html::_('jgrid.published', $item->published, $i, 'joomla_modules.', false, 'cb'); ?>
					<?php endif; ?>
				<?php else: ?>
					<?php echo Html::_('jgrid.published', $item->published, $i, 'joomla_modules.', true, 'cb'); ?>
				<?php endif; ?>
		<?php else: ?>
			<?php echo Html::_('jgrid.published', $item->published, $i, 'joomla_modules.', false, 'cb'); ?>
		<?php endif; ?>
		</td>
		<td class="nowrap center hidden-phone">
			<?php echo $item->id; ?>
		</td>
	</tr>
<?php endforeach; ?>