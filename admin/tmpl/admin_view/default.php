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

<?php echo LayoutHelper::render('admin_view.details_above', $this); ?>
<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'admin_viewTab', ['active' => 'details', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'details', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_DETAILS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_view.details_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_view.details_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.details_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'settings', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_SETTINGS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.settings_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'fields', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_FIELDS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_view.fields_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_view.fields_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.fields_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'css', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_CSS', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.css_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'javascript', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_JAVASCRIPT', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.javascript_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'custom_buttons', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_CUSTOM_BUTTONS', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.custom_buttons_left', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.custom_buttons_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'php', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_PHP', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.php_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'mysql', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_MYSQL', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.mysql_left', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.mysql_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'custom_import', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_CUSTOM_IMPORT', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('admin_view.custom_import_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'admin_viewTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('admin_view.edit.created_by') || $this->canDo->get('admin_view.edit.created') || $this->canDo->get('admin_view.edit.state') || ($this->canDo->get('admin_view.delete') && $this->canDo->get('admin_view.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'publishing', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_view.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('admin_view.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'admin_viewTab', 'permissions', Text::_('COM_COMPONENTBUILDER_ADMIN_VIEW_PERMISSION', true)); ?>
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
		<input type="hidden" name="task" value="admin_view.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>

<div class="clearfix"></div>
<?php echo LayoutHelper::render('admin_view.details_under', $this); ?>
</form>
</div>

<script type="text/javascript">

// #jform_add_css_view listeners for add_css_view_vvvvvyw function
jQuery('#jform_add_css_view').on('keyup',function()
{
	var add_css_view_vvvvvyw = jQuery("#jform_add_css_view input[type='radio']:checked").val();
	vvvvvyw(add_css_view_vvvvvyw);

});
jQuery('#adminForm').on('change', '#jform_add_css_view',function (e)
{
	e.preventDefault();
	var add_css_view_vvvvvyw = jQuery("#jform_add_css_view input[type='radio']:checked").val();
	vvvvvyw(add_css_view_vvvvvyw);

});

// #jform_add_css_views listeners for add_css_views_vvvvvyx function
jQuery('#jform_add_css_views').on('keyup',function()
{
	var add_css_views_vvvvvyx = jQuery("#jform_add_css_views input[type='radio']:checked").val();
	vvvvvyx(add_css_views_vvvvvyx);

});
jQuery('#adminForm').on('change', '#jform_add_css_views',function (e)
{
	e.preventDefault();
	var add_css_views_vvvvvyx = jQuery("#jform_add_css_views input[type='radio']:checked").val();
	vvvvvyx(add_css_views_vvvvvyx);

});

// #jform_add_javascript_view_file listeners for add_javascript_view_file_vvvvvyy function
jQuery('#jform_add_javascript_view_file').on('keyup',function()
{
	var add_javascript_view_file_vvvvvyy = jQuery("#jform_add_javascript_view_file input[type='radio']:checked").val();
	vvvvvyy(add_javascript_view_file_vvvvvyy);

});
jQuery('#adminForm').on('change', '#jform_add_javascript_view_file',function (e)
{
	e.preventDefault();
	var add_javascript_view_file_vvvvvyy = jQuery("#jform_add_javascript_view_file input[type='radio']:checked").val();
	vvvvvyy(add_javascript_view_file_vvvvvyy);

});

// #jform_add_javascript_views_file listeners for add_javascript_views_file_vvvvvyz function
jQuery('#jform_add_javascript_views_file').on('keyup',function()
{
	var add_javascript_views_file_vvvvvyz = jQuery("#jform_add_javascript_views_file input[type='radio']:checked").val();
	vvvvvyz(add_javascript_views_file_vvvvvyz);

});
jQuery('#adminForm').on('change', '#jform_add_javascript_views_file',function (e)
{
	e.preventDefault();
	var add_javascript_views_file_vvvvvyz = jQuery("#jform_add_javascript_views_file input[type='radio']:checked").val();
	vvvvvyz(add_javascript_views_file_vvvvvyz);

});

// #jform_add_javascript_view_footer listeners for add_javascript_view_footer_vvvvvza function
jQuery('#jform_add_javascript_view_footer').on('keyup',function()
{
	var add_javascript_view_footer_vvvvvza = jQuery("#jform_add_javascript_view_footer input[type='radio']:checked").val();
	vvvvvza(add_javascript_view_footer_vvvvvza);

});
jQuery('#adminForm').on('change', '#jform_add_javascript_view_footer',function (e)
{
	e.preventDefault();
	var add_javascript_view_footer_vvvvvza = jQuery("#jform_add_javascript_view_footer input[type='radio']:checked").val();
	vvvvvza(add_javascript_view_footer_vvvvvza);

});

// #jform_add_javascript_views_footer listeners for add_javascript_views_footer_vvvvvzb function
jQuery('#jform_add_javascript_views_footer').on('keyup',function()
{
	var add_javascript_views_footer_vvvvvzb = jQuery("#jform_add_javascript_views_footer input[type='radio']:checked").val();
	vvvvvzb(add_javascript_views_footer_vvvvvzb);

});
jQuery('#adminForm').on('change', '#jform_add_javascript_views_footer',function (e)
{
	e.preventDefault();
	var add_javascript_views_footer_vvvvvzb = jQuery("#jform_add_javascript_views_footer input[type='radio']:checked").val();
	vvvvvzb(add_javascript_views_footer_vvvvvzb);

});

// #jform_add_php_ajax listeners for add_php_ajax_vvvvvzc function
jQuery('#jform_add_php_ajax').on('keyup',function()
{
	var add_php_ajax_vvvvvzc = jQuery("#jform_add_php_ajax input[type='radio']:checked").val();
	vvvvvzc(add_php_ajax_vvvvvzc);

});
jQuery('#adminForm').on('change', '#jform_add_php_ajax',function (e)
{
	e.preventDefault();
	var add_php_ajax_vvvvvzc = jQuery("#jform_add_php_ajax input[type='radio']:checked").val();
	vvvvvzc(add_php_ajax_vvvvvzc);

});

// #jform_add_php_getitem listeners for add_php_getitem_vvvvvzd function
jQuery('#jform_add_php_getitem').on('keyup',function()
{
	var add_php_getitem_vvvvvzd = jQuery("#jform_add_php_getitem input[type='radio']:checked").val();
	vvvvvzd(add_php_getitem_vvvvvzd);

});
jQuery('#adminForm').on('change', '#jform_add_php_getitem',function (e)
{
	e.preventDefault();
	var add_php_getitem_vvvvvzd = jQuery("#jform_add_php_getitem input[type='radio']:checked").val();
	vvvvvzd(add_php_getitem_vvvvvzd);

});

// #jform_add_php_getitems listeners for add_php_getitems_vvvvvze function
jQuery('#jform_add_php_getitems').on('keyup',function()
{
	var add_php_getitems_vvvvvze = jQuery("#jform_add_php_getitems input[type='radio']:checked").val();
	vvvvvze(add_php_getitems_vvvvvze);

});
jQuery('#adminForm').on('change', '#jform_add_php_getitems',function (e)
{
	e.preventDefault();
	var add_php_getitems_vvvvvze = jQuery("#jform_add_php_getitems input[type='radio']:checked").val();
	vvvvvze(add_php_getitems_vvvvvze);

});

// #jform_add_php_getitems_after_all listeners for add_php_getitems_after_all_vvvvvzf function
jQuery('#jform_add_php_getitems_after_all').on('keyup',function()
{
	var add_php_getitems_after_all_vvvvvzf = jQuery("#jform_add_php_getitems_after_all input[type='radio']:checked").val();
	vvvvvzf(add_php_getitems_after_all_vvvvvzf);

});
jQuery('#adminForm').on('change', '#jform_add_php_getitems_after_all',function (e)
{
	e.preventDefault();
	var add_php_getitems_after_all_vvvvvzf = jQuery("#jform_add_php_getitems_after_all input[type='radio']:checked").val();
	vvvvvzf(add_php_getitems_after_all_vvvvvzf);

});

// #jform_add_php_getlistquery listeners for add_php_getlistquery_vvvvvzg function
jQuery('#jform_add_php_getlistquery').on('keyup',function()
{
	var add_php_getlistquery_vvvvvzg = jQuery("#jform_add_php_getlistquery input[type='radio']:checked").val();
	vvvvvzg(add_php_getlistquery_vvvvvzg);

});
jQuery('#adminForm').on('change', '#jform_add_php_getlistquery',function (e)
{
	e.preventDefault();
	var add_php_getlistquery_vvvvvzg = jQuery("#jform_add_php_getlistquery input[type='radio']:checked").val();
	vvvvvzg(add_php_getlistquery_vvvvvzg);

});

// #jform_add_php_getform listeners for add_php_getform_vvvvvzh function
jQuery('#jform_add_php_getform').on('keyup',function()
{
	var add_php_getform_vvvvvzh = jQuery("#jform_add_php_getform input[type='radio']:checked").val();
	vvvvvzh(add_php_getform_vvvvvzh);

});
jQuery('#adminForm').on('change', '#jform_add_php_getform',function (e)
{
	e.preventDefault();
	var add_php_getform_vvvvvzh = jQuery("#jform_add_php_getform input[type='radio']:checked").val();
	vvvvvzh(add_php_getform_vvvvvzh);

});

// #jform_add_php_before_save listeners for add_php_before_save_vvvvvzi function
jQuery('#jform_add_php_before_save').on('keyup',function()
{
	var add_php_before_save_vvvvvzi = jQuery("#jform_add_php_before_save input[type='radio']:checked").val();
	vvvvvzi(add_php_before_save_vvvvvzi);

});
jQuery('#adminForm').on('change', '#jform_add_php_before_save',function (e)
{
	e.preventDefault();
	var add_php_before_save_vvvvvzi = jQuery("#jform_add_php_before_save input[type='radio']:checked").val();
	vvvvvzi(add_php_before_save_vvvvvzi);

});

// #jform_add_php_save listeners for add_php_save_vvvvvzj function
jQuery('#jform_add_php_save').on('keyup',function()
{
	var add_php_save_vvvvvzj = jQuery("#jform_add_php_save input[type='radio']:checked").val();
	vvvvvzj(add_php_save_vvvvvzj);

});
jQuery('#adminForm').on('change', '#jform_add_php_save',function (e)
{
	e.preventDefault();
	var add_php_save_vvvvvzj = jQuery("#jform_add_php_save input[type='radio']:checked").val();
	vvvvvzj(add_php_save_vvvvvzj);

});

// #jform_add_php_postsavehook listeners for add_php_postsavehook_vvvvvzk function
jQuery('#jform_add_php_postsavehook').on('keyup',function()
{
	var add_php_postsavehook_vvvvvzk = jQuery("#jform_add_php_postsavehook input[type='radio']:checked").val();
	vvvvvzk(add_php_postsavehook_vvvvvzk);

});
jQuery('#adminForm').on('change', '#jform_add_php_postsavehook',function (e)
{
	e.preventDefault();
	var add_php_postsavehook_vvvvvzk = jQuery("#jform_add_php_postsavehook input[type='radio']:checked").val();
	vvvvvzk(add_php_postsavehook_vvvvvzk);

});

// #jform_add_php_allowadd listeners for add_php_allowadd_vvvvvzl function
jQuery('#jform_add_php_allowadd').on('keyup',function()
{
	var add_php_allowadd_vvvvvzl = jQuery("#jform_add_php_allowadd input[type='radio']:checked").val();
	vvvvvzl(add_php_allowadd_vvvvvzl);

});
jQuery('#adminForm').on('change', '#jform_add_php_allowadd',function (e)
{
	e.preventDefault();
	var add_php_allowadd_vvvvvzl = jQuery("#jform_add_php_allowadd input[type='radio']:checked").val();
	vvvvvzl(add_php_allowadd_vvvvvzl);

});

// #jform_add_php_allowedit listeners for add_php_allowedit_vvvvvzm function
jQuery('#jform_add_php_allowedit').on('keyup',function()
{
	var add_php_allowedit_vvvvvzm = jQuery("#jform_add_php_allowedit input[type='radio']:checked").val();
	vvvvvzm(add_php_allowedit_vvvvvzm);

});
jQuery('#adminForm').on('change', '#jform_add_php_allowedit',function (e)
{
	e.preventDefault();
	var add_php_allowedit_vvvvvzm = jQuery("#jform_add_php_allowedit input[type='radio']:checked").val();
	vvvvvzm(add_php_allowedit_vvvvvzm);

});

// #jform_add_php_before_cancel listeners for add_php_before_cancel_vvvvvzn function
jQuery('#jform_add_php_before_cancel').on('keyup',function()
{
	var add_php_before_cancel_vvvvvzn = jQuery("#jform_add_php_before_cancel input[type='radio']:checked").val();
	vvvvvzn(add_php_before_cancel_vvvvvzn);

});
jQuery('#adminForm').on('change', '#jform_add_php_before_cancel',function (e)
{
	e.preventDefault();
	var add_php_before_cancel_vvvvvzn = jQuery("#jform_add_php_before_cancel input[type='radio']:checked").val();
	vvvvvzn(add_php_before_cancel_vvvvvzn);

});

// #jform_add_php_after_cancel listeners for add_php_after_cancel_vvvvvzo function
jQuery('#jform_add_php_after_cancel').on('keyup',function()
{
	var add_php_after_cancel_vvvvvzo = jQuery("#jform_add_php_after_cancel input[type='radio']:checked").val();
	vvvvvzo(add_php_after_cancel_vvvvvzo);

});
jQuery('#adminForm').on('change', '#jform_add_php_after_cancel',function (e)
{
	e.preventDefault();
	var add_php_after_cancel_vvvvvzo = jQuery("#jform_add_php_after_cancel input[type='radio']:checked").val();
	vvvvvzo(add_php_after_cancel_vvvvvzo);

});

// #jform_add_php_batchcopy listeners for add_php_batchcopy_vvvvvzp function
jQuery('#jform_add_php_batchcopy').on('keyup',function()
{
	var add_php_batchcopy_vvvvvzp = jQuery("#jform_add_php_batchcopy input[type='radio']:checked").val();
	vvvvvzp(add_php_batchcopy_vvvvvzp);

});
jQuery('#adminForm').on('change', '#jform_add_php_batchcopy',function (e)
{
	e.preventDefault();
	var add_php_batchcopy_vvvvvzp = jQuery("#jform_add_php_batchcopy input[type='radio']:checked").val();
	vvvvvzp(add_php_batchcopy_vvvvvzp);

});

// #jform_add_php_batchmove listeners for add_php_batchmove_vvvvvzq function
jQuery('#jform_add_php_batchmove').on('keyup',function()
{
	var add_php_batchmove_vvvvvzq = jQuery("#jform_add_php_batchmove input[type='radio']:checked").val();
	vvvvvzq(add_php_batchmove_vvvvvzq);

});
jQuery('#adminForm').on('change', '#jform_add_php_batchmove',function (e)
{
	e.preventDefault();
	var add_php_batchmove_vvvvvzq = jQuery("#jform_add_php_batchmove input[type='radio']:checked").val();
	vvvvvzq(add_php_batchmove_vvvvvzq);

});

// #jform_add_php_before_publish listeners for add_php_before_publish_vvvvvzr function
jQuery('#jform_add_php_before_publish').on('keyup',function()
{
	var add_php_before_publish_vvvvvzr = jQuery("#jform_add_php_before_publish input[type='radio']:checked").val();
	vvvvvzr(add_php_before_publish_vvvvvzr);

});
jQuery('#adminForm').on('change', '#jform_add_php_before_publish',function (e)
{
	e.preventDefault();
	var add_php_before_publish_vvvvvzr = jQuery("#jform_add_php_before_publish input[type='radio']:checked").val();
	vvvvvzr(add_php_before_publish_vvvvvzr);

});

// #jform_add_php_after_publish listeners for add_php_after_publish_vvvvvzs function
jQuery('#jform_add_php_after_publish').on('keyup',function()
{
	var add_php_after_publish_vvvvvzs = jQuery("#jform_add_php_after_publish input[type='radio']:checked").val();
	vvvvvzs(add_php_after_publish_vvvvvzs);

});
jQuery('#adminForm').on('change', '#jform_add_php_after_publish',function (e)
{
	e.preventDefault();
	var add_php_after_publish_vvvvvzs = jQuery("#jform_add_php_after_publish input[type='radio']:checked").val();
	vvvvvzs(add_php_after_publish_vvvvvzs);

});

// #jform_add_php_before_delete listeners for add_php_before_delete_vvvvvzt function
jQuery('#jform_add_php_before_delete').on('keyup',function()
{
	var add_php_before_delete_vvvvvzt = jQuery("#jform_add_php_before_delete input[type='radio']:checked").val();
	vvvvvzt(add_php_before_delete_vvvvvzt);

});
jQuery('#adminForm').on('change', '#jform_add_php_before_delete',function (e)
{
	e.preventDefault();
	var add_php_before_delete_vvvvvzt = jQuery("#jform_add_php_before_delete input[type='radio']:checked").val();
	vvvvvzt(add_php_before_delete_vvvvvzt);

});

// #jform_add_php_after_delete listeners for add_php_after_delete_vvvvvzu function
jQuery('#jform_add_php_after_delete').on('keyup',function()
{
	var add_php_after_delete_vvvvvzu = jQuery("#jform_add_php_after_delete input[type='radio']:checked").val();
	vvvvvzu(add_php_after_delete_vvvvvzu);

});
jQuery('#adminForm').on('change', '#jform_add_php_after_delete',function (e)
{
	e.preventDefault();
	var add_php_after_delete_vvvvvzu = jQuery("#jform_add_php_after_delete input[type='radio']:checked").val();
	vvvvvzu(add_php_after_delete_vvvvvzu);

});

// #jform_add_php_document listeners for add_php_document_vvvvvzv function
jQuery('#jform_add_php_document').on('keyup',function()
{
	var add_php_document_vvvvvzv = jQuery("#jform_add_php_document input[type='radio']:checked").val();
	vvvvvzv(add_php_document_vvvvvzv);

});
jQuery('#adminForm').on('change', '#jform_add_php_document',function (e)
{
	e.preventDefault();
	var add_php_document_vvvvvzv = jQuery("#jform_add_php_document input[type='radio']:checked").val();
	vvvvvzv(add_php_document_vvvvvzv);

});

// #jform_add_sql listeners for add_sql_vvvvvzw function
jQuery('#jform_add_sql').on('keyup',function()
{
	var add_sql_vvvvvzw = jQuery("#jform_add_sql input[type='radio']:checked").val();
	vvvvvzw(add_sql_vvvvvzw);

});
jQuery('#adminForm').on('change', '#jform_add_sql',function (e)
{
	e.preventDefault();
	var add_sql_vvvvvzw = jQuery("#jform_add_sql input[type='radio']:checked").val();
	vvvvvzw(add_sql_vvvvvzw);

});

// #jform_source listeners for source_vvvvvzx function
jQuery('#jform_source').on('keyup',function()
{
	var source_vvvvvzx = jQuery("#jform_source input[type='radio']:checked").val();
	var add_sql_vvvvvzx = jQuery("#jform_add_sql input[type='radio']:checked").val();
	vvvvvzx(source_vvvvvzx,add_sql_vvvvvzx);

});
jQuery('#adminForm').on('change', '#jform_source',function (e)
{
	e.preventDefault();
	var source_vvvvvzx = jQuery("#jform_source input[type='radio']:checked").val();
	var add_sql_vvvvvzx = jQuery("#jform_add_sql input[type='radio']:checked").val();
	vvvvvzx(source_vvvvvzx,add_sql_vvvvvzx);

});

// #jform_add_sql listeners for add_sql_vvvvvzx function
jQuery('#jform_add_sql').on('keyup',function()
{
	var source_vvvvvzx = jQuery("#jform_source input[type='radio']:checked").val();
	var add_sql_vvvvvzx = jQuery("#jform_add_sql input[type='radio']:checked").val();
	vvvvvzx(source_vvvvvzx,add_sql_vvvvvzx);

});
jQuery('#adminForm').on('change', '#jform_add_sql',function (e)
{
	e.preventDefault();
	var source_vvvvvzx = jQuery("#jform_source input[type='radio']:checked").val();
	var add_sql_vvvvvzx = jQuery("#jform_add_sql input[type='radio']:checked").val();
	vvvvvzx(source_vvvvvzx,add_sql_vvvvvzx);

});

// #jform_source listeners for source_vvvvvzz function
jQuery('#jform_source').on('keyup',function()
{
	var source_vvvvvzz = jQuery("#jform_source input[type='radio']:checked").val();
	var add_sql_vvvvvzz = jQuery("#jform_add_sql input[type='radio']:checked").val();
	vvvvvzz(source_vvvvvzz,add_sql_vvvvvzz);

});
jQuery('#adminForm').on('change', '#jform_source',function (e)
{
	e.preventDefault();
	var source_vvvvvzz = jQuery("#jform_source input[type='radio']:checked").val();
	var add_sql_vvvvvzz = jQuery("#jform_add_sql input[type='radio']:checked").val();
	vvvvvzz(source_vvvvvzz,add_sql_vvvvvzz);

});

// #jform_add_sql listeners for add_sql_vvvvvzz function
jQuery('#jform_add_sql').on('keyup',function()
{
	var source_vvvvvzz = jQuery("#jform_source input[type='radio']:checked").val();
	var add_sql_vvvvvzz = jQuery("#jform_add_sql input[type='radio']:checked").val();
	vvvvvzz(source_vvvvvzz,add_sql_vvvvvzz);

});
jQuery('#adminForm').on('change', '#jform_add_sql',function (e)
{
	e.preventDefault();
	var source_vvvvvzz = jQuery("#jform_source input[type='radio']:checked").val();
	var add_sql_vvvvvzz = jQuery("#jform_add_sql input[type='radio']:checked").val();
	vvvvvzz(source_vvvvvzz,add_sql_vvvvvzz);

});

// #jform_add_custom_import listeners for add_custom_import_vvvvwab function
jQuery('#jform_add_custom_import').on('keyup',function()
{
	var add_custom_import_vvvvwab = jQuery("#jform_add_custom_import input[type='radio']:checked").val();
	vvvvwab(add_custom_import_vvvvwab);

});
jQuery('#adminForm').on('change', '#jform_add_custom_import',function (e)
{
	e.preventDefault();
	var add_custom_import_vvvvwab = jQuery("#jform_add_custom_import input[type='radio']:checked").val();
	vvvvwab(add_custom_import_vvvvwab);

});

// #jform_add_custom_import listeners for add_custom_import_vvvvwac function
jQuery('#jform_add_custom_import').on('keyup',function()
{
	var add_custom_import_vvvvwac = jQuery("#jform_add_custom_import input[type='radio']:checked").val();
	vvvvwac(add_custom_import_vvvvwac);

});
jQuery('#adminForm').on('change', '#jform_add_custom_import',function (e)
{
	e.preventDefault();
	var add_custom_import_vvvvwac = jQuery("#jform_add_custom_import input[type='radio']:checked").val();
	vvvvwac(add_custom_import_vvvvwac);

});

// #jform_add_custom_button listeners for add_custom_button_vvvvwad function
jQuery('#jform_add_custom_button').on('keyup',function()
{
	var add_custom_button_vvvvwad = jQuery("#jform_add_custom_button input[type='radio']:checked").val();
	vvvvwad(add_custom_button_vvvvwad);

});
jQuery('#adminForm').on('change', '#jform_add_custom_button',function (e)
{
	e.preventDefault();
	var add_custom_button_vvvvwad = jQuery("#jform_add_custom_button input[type='radio']:checked").val();
	vvvvwad(add_custom_button_vvvvwad);

});



<?php $numberAddtables = range(0, count( (array) $this->item->addtables) + 3, 1);?>

// for the values already set
jQuery(document).ready(function(){
<?php foreach($numberAddtables as $fieldNr): ?>
	jQuery('#adminForm').on('change', '#jform_addtables__addtables<?php echo $fieldNr ?>__table',function (e) {
		e.preventDefault();
		getTableColumns(<?php echo $fieldNr ?>, "_", "_");
	});
<?php endforeach; ?>
	jQuery(document).on('subform-row-add', function(event, row){
		var groupName = jQuery(row).data('group');
		var fieldName = groupName.replace(/([0-9])/g, '');
		var fieldNr = groupName.replace(/([A-z_])/g, '');
		if ('addtables' === fieldName) {
			jQuery('#adminForm').on('change', '#jform_addtables_addtables'+fieldNr+'_table',function (e) {
				e.preventDefault();
				getTableColumns(fieldNr, "", "");
			});
		}
	});
});

// #jform_add_custom_import listeners
jQuery('#jform_add_custom_import').on('change',function() {
	var valueSwitch = jQuery("#jform_add_custom_import input[type='radio']:checked").val();
	getDynamicScripts(valueSwitch);
});

<?php
	$app = Factory::getApplication();
?>
function JRouter(link) {
<?php
	if ($app->isClient('site'))
	{
		echo 'var url = "'. \Joomla\CMS\Uri\Uri::root() . '";';
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
jQuery(document).ready(function(){
	jQuery(document).on('subform-row-add', function(event, row){
		getIconImage(jQuery(row).find('.icomoon342'));
	});
});

function getIconImage(field) {
	// get the ID
	var id = jQuery(field).attr('id');
	// remove old one 
	jQuery('#image_'+id).remove();
	// get value
	var value = jQuery('#'+id).val();
	// build new one
	var span = '<span id="image_'+id+'" class="icon-'+value+'" style="position: absolute; top: 8px; right: -20px;"></span>';
	// add the icon
	jQuery('#'+id+'_chzn').append(span);
}
</script>