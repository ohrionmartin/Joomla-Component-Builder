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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
Html::addIncludePath(JPATH_COMPONENT.'/helpers/html');
Html::_('behavior.formvalidator');
Html::_('formbehavior.chosen', 'select');
Html::_('behavior.keepalive');

$componentParams = $this->params; // will be removed just use $this->params instead
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

<?php echo LayoutHelper::render('dynamic_get.main_above', $this); ?>
<div class="form-horizontal">

	<?php echo Html::_('bootstrap.startTabSet', 'dynamic_getTab', ['active' => 'main', 'recall' => true]); ?>

	<?php echo Html::_('bootstrap.addTab', 'dynamic_getTab', 'main', Text::_('COM_COMPONENTBUILDER_DYNAMIC_GET_MAIN', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo LayoutHelper::render('dynamic_get.main_left', $this); ?>
			</div>
			<div class="span6">
				<?php echo LayoutHelper::render('dynamic_get.main_right', $this); ?>
			</div>
		</div>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo LayoutHelper::render('dynamic_get.main_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('bootstrap.endTab'); ?>

	<?php echo Html::_('bootstrap.addTab', 'dynamic_getTab', 'tweak', Text::_('COM_COMPONENTBUILDER_DYNAMIC_GET_TWEAK', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
		</div>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo LayoutHelper::render('dynamic_get.tweak_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('bootstrap.endTab'); ?>

	<?php echo Html::_('bootstrap.addTab', 'dynamic_getTab', 'joint', Text::_('COM_COMPONENTBUILDER_DYNAMIC_GET_JOINT', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
		</div>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo LayoutHelper::render('dynamic_get.joint_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('bootstrap.endTab'); ?>

	<?php echo Html::_('bootstrap.addTab', 'dynamic_getTab', 'custom_script', Text::_('COM_COMPONENTBUILDER_DYNAMIC_GET_CUSTOM_SCRIPT', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
		</div>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo LayoutHelper::render('dynamic_get.custom_script_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('bootstrap.endTab'); ?>

	<?php echo Html::_('bootstrap.addTab', 'dynamic_getTab', 'abacus', Text::_('COM_COMPONENTBUILDER_DYNAMIC_GET_ABACUS', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo LayoutHelper::render('dynamic_get.abacus_left', $this); ?>
			</div>
		</div>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo LayoutHelper::render('dynamic_get.abacus_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('bootstrap.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'dynamic_getTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('core.edit.created_by') || $this->canDo->get('core.edit.created') || $this->canDo->get('dynamic_get.edit.state') || ($this->canDo->get('dynamic_get.delete') && $this->canDo->get('dynamic_get.edit.state'))) : ?>
	<?php echo Html::_('bootstrap.addTab', 'dynamic_getTab', 'publishing', Text::_('COM_COMPONENTBUILDER_DYNAMIC_GET_PUBLISHING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo LayoutHelper::render('dynamic_get.publishing', $this); ?>
			</div>
			<div class="span6">
				<?php echo LayoutHelper::render('dynamic_get.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('bootstrap.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('bootstrap.addTab', 'dynamic_getTab', 'permissions', Text::_('COM_COMPONENTBUILDER_DYNAMIC_GET_PERMISSION', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
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
	<?php echo Html::_('bootstrap.endTab'); ?>
	<?php endif; ?>

	<?php echo Html::_('bootstrap.endTabSet'); ?>

	<div>
		<input type="hidden" name="task" value="dynamic_get.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>

<div class="clearfix"></div>
<?php echo LayoutHelper::render('dynamic_get.main_under', $this); ?>
</form>
</div>

<script type="text/javascript">

// #jform_gettype listeners for gettype_vvvvvzg function
jQuery('#jform_gettype').on('keyup',function()
{
	var gettype_vvvvvzg = jQuery("#jform_gettype").val();
	vvvvvzg(gettype_vvvvvzg);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var gettype_vvvvvzg = jQuery("#jform_gettype").val();
	vvvvvzg(gettype_vvvvvzg);

});

// #jform_main_source listeners for main_source_vvvvvzh function
jQuery('#jform_main_source').on('keyup',function()
{
	var main_source_vvvvvzh = jQuery("#jform_main_source").val();
	vvvvvzh(main_source_vvvvvzh);

});
jQuery('#adminForm').on('change', '#jform_main_source',function (e)
{
	e.preventDefault();
	var main_source_vvvvvzh = jQuery("#jform_main_source").val();
	vvvvvzh(main_source_vvvvvzh);

});

// #jform_main_source listeners for main_source_vvvvvzi function
jQuery('#jform_main_source').on('keyup',function()
{
	var main_source_vvvvvzi = jQuery("#jform_main_source").val();
	vvvvvzi(main_source_vvvvvzi);

});
jQuery('#adminForm').on('change', '#jform_main_source',function (e)
{
	e.preventDefault();
	var main_source_vvvvvzi = jQuery("#jform_main_source").val();
	vvvvvzi(main_source_vvvvvzi);

});

// #jform_main_source listeners for main_source_vvvvvzj function
jQuery('#jform_main_source').on('keyup',function()
{
	var main_source_vvvvvzj = jQuery("#jform_main_source").val();
	vvvvvzj(main_source_vvvvvzj);

});
jQuery('#adminForm').on('change', '#jform_main_source',function (e)
{
	e.preventDefault();
	var main_source_vvvvvzj = jQuery("#jform_main_source").val();
	vvvvvzj(main_source_vvvvvzj);

});

// #jform_main_source listeners for main_source_vvvvvzk function
jQuery('#jform_main_source').on('keyup',function()
{
	var main_source_vvvvvzk = jQuery("#jform_main_source").val();
	vvvvvzk(main_source_vvvvvzk);

});
jQuery('#adminForm').on('change', '#jform_main_source',function (e)
{
	e.preventDefault();
	var main_source_vvvvvzk = jQuery("#jform_main_source").val();
	vvvvvzk(main_source_vvvvvzk);

});

// #jform_main_source listeners for main_source_vvvvvzl function
jQuery('#jform_main_source').on('keyup',function()
{
	var main_source_vvvvvzl = jQuery("#jform_main_source").val();
	vvvvvzl(main_source_vvvvvzl);

});
jQuery('#adminForm').on('change', '#jform_main_source',function (e)
{
	e.preventDefault();
	var main_source_vvvvvzl = jQuery("#jform_main_source").val();
	vvvvvzl(main_source_vvvvvzl);

});

// #jform_addcalculation listeners for addcalculation_vvvvvzm function
jQuery('#jform_addcalculation').on('keyup',function()
{
	var addcalculation_vvvvvzm = jQuery("#jform_addcalculation input[type='radio']:checked").val();
	vvvvvzm(addcalculation_vvvvvzm);

});
jQuery('#adminForm').on('change', '#jform_addcalculation',function (e)
{
	e.preventDefault();
	var addcalculation_vvvvvzm = jQuery("#jform_addcalculation input[type='radio']:checked").val();
	vvvvvzm(addcalculation_vvvvvzm);

});

// #jform_addcalculation listeners for addcalculation_vvvvvzn function
jQuery('#jform_addcalculation').on('keyup',function()
{
	var addcalculation_vvvvvzn = jQuery("#jform_addcalculation input[type='radio']:checked").val();
	var gettype_vvvvvzn = jQuery("#jform_gettype").val();
	vvvvvzn(addcalculation_vvvvvzn,gettype_vvvvvzn);

});
jQuery('#adminForm').on('change', '#jform_addcalculation',function (e)
{
	e.preventDefault();
	var addcalculation_vvvvvzn = jQuery("#jform_addcalculation input[type='radio']:checked").val();
	var gettype_vvvvvzn = jQuery("#jform_gettype").val();
	vvvvvzn(addcalculation_vvvvvzn,gettype_vvvvvzn);

});

// #jform_gettype listeners for gettype_vvvvvzn function
jQuery('#jform_gettype').on('keyup',function()
{
	var addcalculation_vvvvvzn = jQuery("#jform_addcalculation input[type='radio']:checked").val();
	var gettype_vvvvvzn = jQuery("#jform_gettype").val();
	vvvvvzn(addcalculation_vvvvvzn,gettype_vvvvvzn);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var addcalculation_vvvvvzn = jQuery("#jform_addcalculation input[type='radio']:checked").val();
	var gettype_vvvvvzn = jQuery("#jform_gettype").val();
	vvvvvzn(addcalculation_vvvvvzn,gettype_vvvvvzn);

});

// #jform_addcalculation listeners for addcalculation_vvvvvzo function
jQuery('#jform_addcalculation').on('keyup',function()
{
	var addcalculation_vvvvvzo = jQuery("#jform_addcalculation input[type='radio']:checked").val();
	var gettype_vvvvvzo = jQuery("#jform_gettype").val();
	vvvvvzo(addcalculation_vvvvvzo,gettype_vvvvvzo);

});
jQuery('#adminForm').on('change', '#jform_addcalculation',function (e)
{
	e.preventDefault();
	var addcalculation_vvvvvzo = jQuery("#jform_addcalculation input[type='radio']:checked").val();
	var gettype_vvvvvzo = jQuery("#jform_gettype").val();
	vvvvvzo(addcalculation_vvvvvzo,gettype_vvvvvzo);

});

// #jform_gettype listeners for gettype_vvvvvzo function
jQuery('#jform_gettype').on('keyup',function()
{
	var addcalculation_vvvvvzo = jQuery("#jform_addcalculation input[type='radio']:checked").val();
	var gettype_vvvvvzo = jQuery("#jform_gettype").val();
	vvvvvzo(addcalculation_vvvvvzo,gettype_vvvvvzo);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var addcalculation_vvvvvzo = jQuery("#jform_addcalculation input[type='radio']:checked").val();
	var gettype_vvvvvzo = jQuery("#jform_gettype").val();
	vvvvvzo(addcalculation_vvvvvzo,gettype_vvvvvzo);

});

// #jform_main_source listeners for main_source_vvvvvzr function
jQuery('#jform_main_source').on('keyup',function()
{
	var main_source_vvvvvzr = jQuery("#jform_main_source").val();
	vvvvvzr(main_source_vvvvvzr);

});
jQuery('#adminForm').on('change', '#jform_main_source',function (e)
{
	e.preventDefault();
	var main_source_vvvvvzr = jQuery("#jform_main_source").val();
	vvvvvzr(main_source_vvvvvzr);

});

// #jform_main_source listeners for main_source_vvvvvzs function
jQuery('#jform_main_source').on('keyup',function()
{
	var main_source_vvvvvzs = jQuery("#jform_main_source").val();
	vvvvvzs(main_source_vvvvvzs);

});
jQuery('#adminForm').on('change', '#jform_main_source',function (e)
{
	e.preventDefault();
	var main_source_vvvvvzs = jQuery("#jform_main_source").val();
	vvvvvzs(main_source_vvvvvzs);

});

// #jform_add_php_before_getitem listeners for add_php_before_getitem_vvvvvzt function
jQuery('#jform_add_php_before_getitem').on('keyup',function()
{
	var add_php_before_getitem_vvvvvzt = jQuery("#jform_add_php_before_getitem input[type='radio']:checked").val();
	var gettype_vvvvvzt = jQuery("#jform_gettype").val();
	vvvvvzt(add_php_before_getitem_vvvvvzt,gettype_vvvvvzt);

});
jQuery('#adminForm').on('change', '#jform_add_php_before_getitem',function (e)
{
	e.preventDefault();
	var add_php_before_getitem_vvvvvzt = jQuery("#jform_add_php_before_getitem input[type='radio']:checked").val();
	var gettype_vvvvvzt = jQuery("#jform_gettype").val();
	vvvvvzt(add_php_before_getitem_vvvvvzt,gettype_vvvvvzt);

});

// #jform_gettype listeners for gettype_vvvvvzt function
jQuery('#jform_gettype').on('keyup',function()
{
	var add_php_before_getitem_vvvvvzt = jQuery("#jform_add_php_before_getitem input[type='radio']:checked").val();
	var gettype_vvvvvzt = jQuery("#jform_gettype").val();
	vvvvvzt(add_php_before_getitem_vvvvvzt,gettype_vvvvvzt);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var add_php_before_getitem_vvvvvzt = jQuery("#jform_add_php_before_getitem input[type='radio']:checked").val();
	var gettype_vvvvvzt = jQuery("#jform_gettype").val();
	vvvvvzt(add_php_before_getitem_vvvvvzt,gettype_vvvvvzt);

});

// #jform_add_php_after_getitem listeners for add_php_after_getitem_vvvvvzu function
jQuery('#jform_add_php_after_getitem').on('keyup',function()
{
	var add_php_after_getitem_vvvvvzu = jQuery("#jform_add_php_after_getitem input[type='radio']:checked").val();
	var gettype_vvvvvzu = jQuery("#jform_gettype").val();
	vvvvvzu(add_php_after_getitem_vvvvvzu,gettype_vvvvvzu);

});
jQuery('#adminForm').on('change', '#jform_add_php_after_getitem',function (e)
{
	e.preventDefault();
	var add_php_after_getitem_vvvvvzu = jQuery("#jform_add_php_after_getitem input[type='radio']:checked").val();
	var gettype_vvvvvzu = jQuery("#jform_gettype").val();
	vvvvvzu(add_php_after_getitem_vvvvvzu,gettype_vvvvvzu);

});

// #jform_gettype listeners for gettype_vvvvvzu function
jQuery('#jform_gettype').on('keyup',function()
{
	var add_php_after_getitem_vvvvvzu = jQuery("#jform_add_php_after_getitem input[type='radio']:checked").val();
	var gettype_vvvvvzu = jQuery("#jform_gettype").val();
	vvvvvzu(add_php_after_getitem_vvvvvzu,gettype_vvvvvzu);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var add_php_after_getitem_vvvvvzu = jQuery("#jform_add_php_after_getitem input[type='radio']:checked").val();
	var gettype_vvvvvzu = jQuery("#jform_gettype").val();
	vvvvvzu(add_php_after_getitem_vvvvvzu,gettype_vvvvvzu);

});

// #jform_gettype listeners for gettype_vvvvvzw function
jQuery('#jform_gettype').on('keyup',function()
{
	var gettype_vvvvvzw = jQuery("#jform_gettype").val();
	vvvvvzw(gettype_vvvvvzw);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var gettype_vvvvvzw = jQuery("#jform_gettype").val();
	vvvvvzw(gettype_vvvvvzw);

});

// #jform_add_php_getlistquery listeners for add_php_getlistquery_vvvvvzx function
jQuery('#jform_add_php_getlistquery').on('keyup',function()
{
	var add_php_getlistquery_vvvvvzx = jQuery("#jform_add_php_getlistquery input[type='radio']:checked").val();
	var gettype_vvvvvzx = jQuery("#jform_gettype").val();
	vvvvvzx(add_php_getlistquery_vvvvvzx,gettype_vvvvvzx);

});
jQuery('#adminForm').on('change', '#jform_add_php_getlistquery',function (e)
{
	e.preventDefault();
	var add_php_getlistquery_vvvvvzx = jQuery("#jform_add_php_getlistquery input[type='radio']:checked").val();
	var gettype_vvvvvzx = jQuery("#jform_gettype").val();
	vvvvvzx(add_php_getlistquery_vvvvvzx,gettype_vvvvvzx);

});

// #jform_gettype listeners for gettype_vvvvvzx function
jQuery('#jform_gettype').on('keyup',function()
{
	var add_php_getlistquery_vvvvvzx = jQuery("#jform_add_php_getlistquery input[type='radio']:checked").val();
	var gettype_vvvvvzx = jQuery("#jform_gettype").val();
	vvvvvzx(add_php_getlistquery_vvvvvzx,gettype_vvvvvzx);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var add_php_getlistquery_vvvvvzx = jQuery("#jform_add_php_getlistquery input[type='radio']:checked").val();
	var gettype_vvvvvzx = jQuery("#jform_gettype").val();
	vvvvvzx(add_php_getlistquery_vvvvvzx,gettype_vvvvvzx);

});

// #jform_add_php_before_getitems listeners for add_php_before_getitems_vvvvvzy function
jQuery('#jform_add_php_before_getitems').on('keyup',function()
{
	var add_php_before_getitems_vvvvvzy = jQuery("#jform_add_php_before_getitems input[type='radio']:checked").val();
	var gettype_vvvvvzy = jQuery("#jform_gettype").val();
	vvvvvzy(add_php_before_getitems_vvvvvzy,gettype_vvvvvzy);

});
jQuery('#adminForm').on('change', '#jform_add_php_before_getitems',function (e)
{
	e.preventDefault();
	var add_php_before_getitems_vvvvvzy = jQuery("#jform_add_php_before_getitems input[type='radio']:checked").val();
	var gettype_vvvvvzy = jQuery("#jform_gettype").val();
	vvvvvzy(add_php_before_getitems_vvvvvzy,gettype_vvvvvzy);

});

// #jform_gettype listeners for gettype_vvvvvzy function
jQuery('#jform_gettype').on('keyup',function()
{
	var add_php_before_getitems_vvvvvzy = jQuery("#jform_add_php_before_getitems input[type='radio']:checked").val();
	var gettype_vvvvvzy = jQuery("#jform_gettype").val();
	vvvvvzy(add_php_before_getitems_vvvvvzy,gettype_vvvvvzy);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var add_php_before_getitems_vvvvvzy = jQuery("#jform_add_php_before_getitems input[type='radio']:checked").val();
	var gettype_vvvvvzy = jQuery("#jform_gettype").val();
	vvvvvzy(add_php_before_getitems_vvvvvzy,gettype_vvvvvzy);

});

// #jform_add_php_after_getitems listeners for add_php_after_getitems_vvvvvzz function
jQuery('#jform_add_php_after_getitems').on('keyup',function()
{
	var add_php_after_getitems_vvvvvzz = jQuery("#jform_add_php_after_getitems input[type='radio']:checked").val();
	var gettype_vvvvvzz = jQuery("#jform_gettype").val();
	vvvvvzz(add_php_after_getitems_vvvvvzz,gettype_vvvvvzz);

});
jQuery('#adminForm').on('change', '#jform_add_php_after_getitems',function (e)
{
	e.preventDefault();
	var add_php_after_getitems_vvvvvzz = jQuery("#jform_add_php_after_getitems input[type='radio']:checked").val();
	var gettype_vvvvvzz = jQuery("#jform_gettype").val();
	vvvvvzz(add_php_after_getitems_vvvvvzz,gettype_vvvvvzz);

});

// #jform_gettype listeners for gettype_vvvvvzz function
jQuery('#jform_gettype').on('keyup',function()
{
	var add_php_after_getitems_vvvvvzz = jQuery("#jform_add_php_after_getitems input[type='radio']:checked").val();
	var gettype_vvvvvzz = jQuery("#jform_gettype").val();
	vvvvvzz(add_php_after_getitems_vvvvvzz,gettype_vvvvvzz);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var add_php_after_getitems_vvvvvzz = jQuery("#jform_add_php_after_getitems input[type='radio']:checked").val();
	var gettype_vvvvvzz = jQuery("#jform_gettype").val();
	vvvvvzz(add_php_after_getitems_vvvvvzz,gettype_vvvvvzz);

});

// #jform_gettype listeners for gettype_vvvvwab function
jQuery('#jform_gettype').on('keyup',function()
{
	var gettype_vvvvwab = jQuery("#jform_gettype").val();
	vvvvwab(gettype_vvvvwab);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var gettype_vvvvwab = jQuery("#jform_gettype").val();
	vvvvwab(gettype_vvvvwab);

});

// #jform_gettype listeners for gettype_vvvvwac function
jQuery('#jform_gettype').on('keyup',function()
{
	var gettype_vvvvwac = jQuery("#jform_gettype").val();
	vvvvwac(gettype_vvvvwac);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var gettype_vvvvwac = jQuery("#jform_gettype").val();
	vvvvwac(gettype_vvvvwac);

});

// #jform_gettype listeners for gettype_vvvvwad function
jQuery('#jform_gettype').on('keyup',function()
{
	var gettype_vvvvwad = jQuery("#jform_gettype").val();
	vvvvwad(gettype_vvvvwad);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var gettype_vvvvwad = jQuery("#jform_gettype").val();
	vvvvwad(gettype_vvvvwad);

});

// #jform_gettype listeners for gettype_vvvvwae function
jQuery('#jform_gettype').on('keyup',function()
{
	var gettype_vvvvwae = jQuery("#jform_gettype").val();
	var add_php_router_parse_vvvvwae = jQuery("#jform_add_php_router_parse input[type='radio']:checked").val();
	vvvvwae(gettype_vvvvwae,add_php_router_parse_vvvvwae);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var gettype_vvvvwae = jQuery("#jform_gettype").val();
	var add_php_router_parse_vvvvwae = jQuery("#jform_add_php_router_parse input[type='radio']:checked").val();
	vvvvwae(gettype_vvvvwae,add_php_router_parse_vvvvwae);

});

// #jform_add_php_router_parse listeners for add_php_router_parse_vvvvwae function
jQuery('#jform_add_php_router_parse').on('keyup',function()
{
	var gettype_vvvvwae = jQuery("#jform_gettype").val();
	var add_php_router_parse_vvvvwae = jQuery("#jform_add_php_router_parse input[type='radio']:checked").val();
	vvvvwae(gettype_vvvvwae,add_php_router_parse_vvvvwae);

});
jQuery('#adminForm').on('change', '#jform_add_php_router_parse',function (e)
{
	e.preventDefault();
	var gettype_vvvvwae = jQuery("#jform_gettype").val();
	var add_php_router_parse_vvvvwae = jQuery("#jform_add_php_router_parse input[type='radio']:checked").val();
	vvvvwae(gettype_vvvvwae,add_php_router_parse_vvvvwae);

});

// #jform_gettype listeners for gettype_vvvvwag function
jQuery('#jform_gettype').on('keyup',function()
{
	var gettype_vvvvwag = jQuery("#jform_gettype").val();
	vvvvwag(gettype_vvvvwag);

});
jQuery('#adminForm').on('change', '#jform_gettype',function (e)
{
	e.preventDefault();
	var gettype_vvvvwag = jQuery("#jform_gettype").val();
	vvvvwag(gettype_vvvvwag);

});



<?php $fieldNrs = range(0,50,1); ?>
<?php $fieldNames = array('db' => 'Db','view' => 'View'); ?>
// for the vlaues already set
jQuery(document).ready(function(){
<?php foreach($fieldNames as $fieldName => $funcName): ?>
 	<?php foreach($fieldNrs as $fieldNr): ?>
		updateSubItems('<?php echo $fieldName ?>', <?php echo $fieldNr ?>, '_', '_');
	<?php endforeach; ?>
<?php endforeach; ?>
});
// for the values the will still be set
jQuery(document).ready(function(){
	jQuery(document).on('subform-row-add', function(event, row){
		var groupName = jQuery(row).data('group');
		var fieldName = groupName.replace('join_', '').replace('_table', '').replace(/([0-9])/g, '');
		var fieldNr = groupName.replace(/([A-z_])/g, '');
		updateSubItems(fieldName, fieldNr, '_', '_');
	});

});
<?php foreach($fieldNames as $fieldName => $funcName): ?>jQuery('#adminForm').on('change', '#jform_<?php echo $fieldName ?>_table_main',function (e) {
	// get options
	var value_<?php echo $fieldName ?> = jQuery("#jform_<?php echo $fieldName ?>_table_main option:selected").val();
	get<?php echo $funcName; ?>TableColumns(value_<?php echo $fieldName ?>, 'a', '<?php echo $fieldName ?>', 3, true, '', '');
});
<?php endforeach; ?>
// #jform_add_php_router_parse listeners
jQuery('#jform_add_php_router_parse').on('change',function() {
	var valueSwitch = jQuery("#jform_add_php_router_parse input[type='radio']:checked").val();
	getDynamicScripts(valueSwitch);
});
jQuery('#adminForm').on('change', '#jform_select_all',function (e)
{
	e.preventDefault();
	// get the selected value
	var select_all =  jQuery("#jform_select_all input[type='radio']:checked").val();
	setSelectAll(select_all);

});

<?php
	$app = Factory::getApplication();
?>
function JRouter(link) {
<?php
	if ($app->isClient('site'))
	{
		echo 'var url = "'. Uri::root() . '";';
	}
	else
	{
		echo 'var url = "";';
	}
?>
	return url+link;
}

document.addEventListener("DOMContentLoaded", function() {
	document.querySelectorAll(".loading-dots").forEach(function(loading_dots) {
		let x = 0;
		let intervalId = setInterval(function() {
			if (!loading_dots.classList.contains("loading-dots")) {
				clearInterval(intervalId);
				return;
			}
			let dots = ".".repeat(x % 8);
			loading_dots.textContent = dots;
			x++;
		}, 500);
	});
}); 
</script>
