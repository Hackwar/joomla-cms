<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', 'com_weblinks.category');
$saveOrder	= $listOrder == 'a.ordering';
?>
<form action="<?php echo Route::_('index.php?option=com_config&view=mails'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->items)) : ?>
					<joomla-alert type="warning"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
					<table class="table" id="mailList">
						<thead>
							<tr>
								<th scope="col" style="min-width:100px" class="nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:15%" class="nowrap d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_CONFIG_HEADING_COMPONENT', 'a.component', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:15%"  class="nowrap d-none d-md-table-cell">
									<?php echo Text::_('COM_CONFIG_HEADING_LANUAGES'); ?>
								</th>
								<th scope="col" class="d-none d-md-table-cell">
									<?php echo Text::_('COM_CONFIG_HEADING_DESCRIPTION'); ?>
								</th>
								<th scope="col" style="width:5%" class="nowrap d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($this->items as $i => $item) :
							list($component, $sub_id) = explode('.', $item->mail_id, 2);
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td scope="row">
									<?php echo Text::_($component . '_MAIL_' . $sub_id . '_TITLE'); ?>
								</td>
								<td scope="row">
									<?php echo Text::_($component); ?>
								</td>
								<td scope="row">
									<?php foreach ($this->languages as $language) : ?>
										<?php $exists = in_array($language->lang_code, $item->languages); ?>
										<a href="<?php echo JRoute::_('index.php?option=com_config&task=mail.edit&mail_id=' . $item->mail_id . '&language=' . $language->lang_code); ?>"
											<?php echo !$exists ? ' style=""' : ''; ?>>
											<?php if ($language->image) : ?>
												<?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', $language->title_native, array('title' => $language->title_native), true); ?>
											<?php else : ?>
												<span class="badge badge-secondary"><?php echo strtoupper($language->sef); ?></span>
											<?php endif; ?>
										</a>
									<?php endforeach; ?>
								</td>
								<td scope="row">
									<?php echo Text::_($component . '_MAIL_' . $sub_id . '_DESC'); ?>
								</td>
								<td scope="row">
									<?php echo $item->mail_id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
