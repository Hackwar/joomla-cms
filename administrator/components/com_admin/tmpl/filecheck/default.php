<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Admin\Administrator\View\Filecheck\HtmlView $this */
?>
<div class="main-card">
    <p><?php echo Text::_('COM_ADMIN_FILECHECK_INTRO'); ?></p>

    <h2><?php echo Text::_('COM_ADMIN_FILECHECK_SOURCES_HEADING'); ?></h2>
    <div class="table-responsive">
        <table class="table" id="filecheck-sources">
            <thead>
                <tr>
                    <th scope="col"><?php echo Text::_('COM_ADMIN_FILECHECK_HEADING_NAME'); ?></th>
                    <th scope="col"><?php echo Text::_('COM_ADMIN_FILECHECK_HEADING_TYPE'); ?></th>
                    <th scope="col"><?php echo Text::_('COM_ADMIN_FILECHECK_HEADING_LASTMODIFIED'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($this->sources)) : ?>
                <tr>
                    <td colspan="3"><?php echo Text::_('COM_ADMIN_FILECHECK_SOURCES_EMPTY'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($this->sources as $source) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars(Text::_($source->name), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($source->type, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php echo $source->mtime
                                ? HTMLHelper::_('date', (int) $source->mtime, Text::_('DATE_FORMAT_LC5'))
                                : '&#8212;'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h2><?php echo Text::_('COM_ADMIN_FILECHECK_UPLOAD_HEADING'); ?></h2>
    <p><?php echo Text::_('COM_ADMIN_FILECHECK_UPLOAD_DESC'); ?></p>
    <form id="filecheck-upload-form" enctype="multipart/form-data" method="post" class="mb-4">
        <div class="input-group" style="max-width: 40rem;">
            <input
                type="file"
                name="checksumsfile"
                id="filecheck-upload-input"
                class="form-control"
                accept=".txt"
                required
            >
            <button type="submit" class="btn btn-secondary" id="filecheck-upload-button">
                <?php echo Text::_('COM_ADMIN_FILECHECK_UPLOAD_BUTTON'); ?>
            </button>
        </div>
    </form>

    <?php echo $this->loadTemplate('modal'); ?>
    <?php echo $this->loadTemplate('results'); ?>
</div>
