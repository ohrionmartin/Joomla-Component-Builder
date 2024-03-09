<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this JCB template file (EVER)
defined('_JCB_TEMPLATE') or die;
?>
###BOM###

###SITE_ADMIN_VIEW_HEADER###

// No direct access to this file
defined('_JEXEC') or die;

?>
<div class="###component###-###view###">
<?php echo $this->toolbar->render(); ?>
<form action="<?php echo Route::_('index.php?option=com_###component###&layout=edit&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
###EDITBODY###
</form>
</div>###EDITBODYSCRIPT###
