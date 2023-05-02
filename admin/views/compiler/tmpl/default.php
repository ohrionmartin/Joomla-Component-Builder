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
use VDM\Joomla\Utilities\StringHelper;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

$this->app->input->set('hidemainmenu', false);
$selectNotice = '<h3>' . JText::_('COM_COMPONENTBUILDER_HI') . ' ' . $this->user->name . '</h3>';
$selectNotice .= '<p>' . JText::_('COM_COMPONENTBUILDER_PLEASE_SELECT_A_COMPONENT_THAT_YOU_WOULD_LIKE_TO_COMPILE') . '</p>';

// set the noticeboard options
$noticeboardOptions = array('vdm', 'pro');
?>
<?php if ($this->canDo->get('compiler.access')): ?>

<script type="text/javascript">
Joomla.submitbutton = function(task, key)
{
	if (task == ''){
		return false;
	} else {
		var component = jQuery('#component_id').val();
		var isValid = true;

		if(component == '' && task == 'compiler.compiler'){
			isValid = false;
		}
		if (isValid){
			jQuery('#form').hide();
			// get correct form based on task
			var form = document.getElementById('adminForm');
			// set the plugin id
			if (task == 'compiler.installCompiledModule' || task == 'compiler.installCompiledPlugin') {
				form.install_item_id.value = key;
			}
			// set the task value
			form.task.value = task;
			// seems we need a little delay here
			setTimeout(function() {
				form.submit();
			}, 100);
			// some ui movements
			if (task == 'compiler.compiler'){
				// get the component name
				let component_name = jQuery("#component_id option:selected").text();
				// set the component name
				jQuery(".component-name").text(component_name);
				// wait a little since to much is happening...
				setTimeout(function() {
					jQuery('#compiler').show();
					jQuery('#compiling').css('display', 'block');
					// wait a little since to much is happening...
					setTimeout(function() {
						jQuery('#compiler-spinner').show();
						jQuery('#compiler-notice').show();
					}, 100);
				}, 100);
			} else if (task == 'compiler.clearTmp'){
				jQuery('#clear').show();
				jQuery('#loading').css('display', 'block');
			} else if (task == 'compiler.getCompilerAnimations'){
				jQuery('#get-compiler-animations').show();
				jQuery('#loading').css('display', 'block');
			} else {
				jQuery('#loading').css('display', 'block');
			}
			return true;
		} else {
			jQuery('.notice').show();
			return false;
		}
	}
}
// Add spindle-wheel for importations:
jQuery(document).ready(function($) {

// waiting spinner
var outerDiv = jQuery('body');
jQuery('<div id="loading"></div>')
	.css("background", "rgba(255, 255, 255, .8) url('components/com_componentbuilder/assets/images/import.gif') 50% 15% no-repeat")
	.css("top", outerDiv.position().top - jQuery(window).scrollTop())
	.css("left", outerDiv.position().left - jQuery(window).scrollLeft())
	.css("width", outerDiv.width())
	.css("height", outerDiv.height())
	.css("position", "fixed")
	.css("opacity", "0.80")
	.css("-ms-filter", "progid:DXImageTransform.Microsoft.Alpha(Opacity = 80)")
	.css("filter", "alpha(opacity = 80)")
	.css("display", "none")
	.appendTo(outerDiv);
// for the compiler
var outerDiv = jQuery('body');
jQuery('<div id="compiling"></div>')
        .css("background", "rgba(16, 164, 230, .4)")
        .css("top", outerDiv.position().top - jQuery(window).scrollTop())
        .css("left", outerDiv.position().left - jQuery(window).scrollLeft())
        .css("width", outerDiv.width())
        .css("height", outerDiv.height())
        .css("position", "fixed")
        .css("opacity", "0.40")
        .css("-ms-filter", "progid:DXImageTransform.Microsoft.Alpha(Opacity = 40)")
        .css("filter", "alpha(opacity = 40)")
        .css("display", "none")
        .appendTo(outerDiv);
});
</script>
<?php if(!empty( $this->sidebar)): ?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<?php else : ?>
<div id="j-main-container">
<?php endif; ?>
	<?php if (StringHelper::check($this->SuccessMessage)): ?>
		<div class="alert alert-success">
		<button type="button" class="close" data-dismiss="alert">×</button>
			<?php echo $this->SuccessMessage; ?>
		</div>
	<?php endif; ?>
	<form action="<?php echo JRoute::_('index.php?option=com_componentbuilder&view=compiler'); ?>"
		method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
		<div id="form" >
			<div class="span4">
				<h3><?php echo JText::_('COM_COMPONENTBUILDER_READY_TO_COMPILE_A_COMPONENT'); ?></h3>
				<div id="compilerForm">
					<div>
					<span class="notice" style="display:none; color:red;"><?php echo JText::_('COM_COMPONENTBUILDER_YOU_MUST_SELECT_A_COMPONENT'); ?></span><br />
					<?php if ($this->form): ?>
						<?php echo $this->form->renderFieldset('builder'); ?>
					<?php endif; ?>
					</div>
					<br />
					<div class="clearfix"></div>
					<button class="btn btn-small btn-success" onclick="Joomla.submitbutton('compiler.compiler')"><span class="icon-cog icon-white"></span>
						<?php echo JText::_('COM_COMPONENTBUILDER_COMPILE_COMPONENT'); ?>
					</button>
					<input type="hidden" name="install_item_id" value="0"> 
					<input type="hidden" name="version" value="3" />
				</div>
			</div>
			<div class="span7">
				<div id="advance-details"><?php echo $this->form->renderFieldset('advanced'); ?></div>
				<div id="component-details"><?php echo $selectNotice; ?></div>
				<?php echo JLayoutHelper::render('jcbnoticeboardtabs', array('id' => 'noticeboard' , 'active' => $noticeboardOptions[array_rand($noticeboardOptions)])); ?>
			</div>
		</div>
		<div id="get-compiler-animations" style="display:none;">
			<h1><?php echo JText::_('COM_COMPONENTBUILDER_PLEASE_WAIT'); ?></h1>
			<h4><?php echo JText::_('COM_COMPONENTBUILDER_WHILE_WE_DOWNLOAD_ALL_TWENTY_SIX_COMPILER_GIF_ANIMATIONS_RANDOMLY_USED_IN_THE_COMPILER_GUI_DURING_COMPILATION'); ?> <span class="loading-dots">.</span></h4>
			<div class="clearfix"></div>
		</div>
		<div id="clear" style="display:none;">
			<h1><?php echo JText::_('COM_COMPONENTBUILDER_PLEASE_WAIT'); ?></h1>
			<h4><?php echo JText::_('COM_COMPONENTBUILDER_REMOVING_ALL_ZIP_PACKAGES_FROM_THE_TEMPORARY_FOLDER_OF_THE_JOOMLA_INSTALL'); ?> <span class="loading-dots">.</span></h4>
			<div class="clearfix"></div>
		</div>
		<div id="compiler" style="display:none;">
			<div id="compiler-spinner" class="span4" style="display:none;">
				<h3><?php echo JText::sprintf('COM_COMPONENTBUILDER_S_PLEASE_WAIT', $this->user->name); ?></h3>
				<p style="font-size: smaller;"><?php echo JText::_('COM_COMPONENTBUILDER_THIS_MAY_TAKE_A_WHILE_DEPENDING_ON_THE_SIZE_OF_YOUR_PROJECT'); ?></p>
				<p><b><span class="component-name"><?php echo JText::_('COM_COMPONENTBUILDER_THE_COMPONENT'); ?></span></b> <?php echo JText::_('COM_COMPONENTBUILDER_IS_BEING_COMPILED'); ?> <span class="loading-dots">.</span></p>
				<div style="text-align: center;"><?php echo ComponentbuilderHelper::getDynamicContent('builder-gif', $this->builder_gif_size); ?></div>
				<div class="clearfix"></div>
			</div>
			<div id="compiler-notice" class="span7" style="display:none;">
				<?php echo JLayoutHelper::render('jcbnoticeboard' . $noticeboardOptions[array_rand($noticeboardOptions)], null); ?>
				<div><?php echo ComponentbuilderHelper::getDynamicContent('banner', '728-90'); ?></div>
			</div>
		</div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
<script type="text/javascript">
// token 
var token = '<?php echo JSession::getFormToken(); ?>';
var all_is_good = '<?php echo JText::_('COM_COMPONENTBUILDER_ALL_IS_GOOD_THERE_IS_NO_NOTICE_AT_THIS_TIME'); ?>';
jQuery('#compilerForm').on('change', '#component_id',function (e)
{
	var component = jQuery('#component_id').val();
	if(component == "") {
		jQuery('#component-details').html("<?php echo $selectNotice; ?>");
		jQuery("#noticeboard").show();
		jQuery('.notice').show();
	} else {
		getComponentDetails(component);
		jQuery("#noticeboard").hide();
		jQuery('.notice').hide();
	}
});

// nice little dot trick :)
jQuery(document).ready( function($) {
  var x=0;
  setInterval(function() {
	var dots = "";
	x++;
	for (var y=0; y < x%8; y++) {
		dots+=".";
	}
	$(".loading-dots").text(dots);
  } , 500);
});

<?php
	$app = JFactory::getApplication();
?>
function JRouter(link) {
<?php
	if ($app->isClient('site'))
	{
		echo 'var url = "'.JURI::root().'";';
	}
	else
	{
		echo 'var url = "";';
	}
?>
	return url+link;
}
</script>
<?php else: ?>
        <h1><?php echo JText::_('COM_COMPONENTBUILDER_NO_ACCESS_GRANTED'); ?></h1>
<?php endif; ?>
