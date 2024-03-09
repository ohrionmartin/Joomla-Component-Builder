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
use Joomla\CMS\Router\Route;
use VDM\Component\Componentbuilder\Administrator\Helper\ComponentbuilderHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')->useScript('form.validate');
Html::_('bootstrap.tooltip');

// No direct access to this file
defined('_JEXEC') or die;

?>
<script type="text/javascript">
	// waiting spinner
	var outerDiv = document.querySelector('body');
	var loadingDiv = document.createElement('div');
	loadingDiv.id = 'loading';
	loadingDiv.style.cssText = "background: rgba(255, 255, 255, .8) url('components/com_componentbuilder/assets/images/import.gif') 50% 15% no-repeat; top: " + (outerDiv.getBoundingClientRect().top + window.pageYOffset) + "px; left: " + (outerDiv.getBoundingClientRect().left + window.pageXOffset) + "px; width: " + outerDiv.offsetWidth + "px; height: " + outerDiv.offsetHeight + "px; position: fixed; opacity: 0.80; -ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=80); filter: alpha(opacity=80); display: none;";
	outerDiv.appendChild(loadingDiv);
	loadingDiv.style.display = 'block';
	// when page is ready remove and show
	window.addEventListener('load', function() {
		var componentLoader = document.getElementById('componentbuilder_loader');
		if (componentLoader) componentLoader.style.display = 'block';
		loadingDiv.style.display = 'none';
	});
</script>
<div id="componentbuilder_loader" style="display: none;">
<form action="<?php echo Route::_('index.php?option=com_componentbuilder&layout=edit&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<?php echo LayoutHelper::render('component_plugins.plugins_above', $this); ?>
<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'component_pluginsTab', ['active' => 'plugins', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'component_pluginsTab', 'plugins', Text::_('COM_COMPONENTBUILDER_COMPONENT_PLUGINS_PLUGINS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('component_plugins.plugins_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'component_pluginsTab', 'clone', Text::_('COM_COMPONENTBUILDER_COMPONENT_PLUGINS_CLONE', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('component_plugins.clone_left', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'component_pluginsTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('component_plugins.edit.created_by') || $this->canDo->get('component_plugins.edit.created') || $this->canDo->get('component_plugins.edit.state') || ($this->canDo->get('component_plugins.delete') && $this->canDo->get('component_plugins.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'component_pluginsTab', 'publishing', Text::_('COM_COMPONENTBUILDER_COMPONENT_PLUGINS_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('component_plugins.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('component_plugins.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'component_pluginsTab', 'permissions', Text::_('COM_COMPONENTBUILDER_COMPONENT_PLUGINS_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset class="adminform">
					<div class="adminformlist">
					<?php foreach ($this->form->getFieldset('accesscontrol') as $field): ?>
						<div>
							<?php echo $field->label; echo $field->input;?>
						</div>
						<div class="clearfix"></div>
					<?php endforeach; ?>
					</div>
				</fieldset>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php echo Html::_('uitab.endTabSet'); ?>

	<div>
		<input type="hidden" name="task" value="component_plugins.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>
</form>
</div>

<script type="text/javascript">



jQuery(document).ready(function(){
	jQuery(document).on('subform-row-add', function(event, row){
		var group_key = jQuery(row).context.dataset.group;
		jQuery(row).find('#jform_addadmin_views__' + group_key + '__submenu').prop('checked', true);
		jQuery(row).find('#jform_addadmin_views__' + group_key + '__checkin').prop('checked', true);
		jQuery(row).find('#jform_addadmin_views__' + group_key + '__history').prop('checked', true);
		jQuery(row).find('#jform_addadmin_views__' + group_key + '__access').prop('checked', true);
		jQuery(row).find('#jform_addadmin_views__' + group_key + '__port').prop('checked', true);
	})
});
</script>
