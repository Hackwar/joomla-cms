<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Admin\Administrator\View\Filecheck\HtmlView $this */
?>
<div id="filecheck-results-section" class="mt-4 d-none">
    <h2><?php echo Text::_('COM_ADMIN_FILECHECK_RESULTS_HEADING'); ?></h2>

    <div class="btn-toolbar mb-3 justify-content-between" role="toolbar">
        <div class="btn-group" role="group" aria-label="<?php echo Text::_('COM_ADMIN_FILECHECK_FILTER_GROUP_LABEL'); ?>">
            <button type="button" class="btn btn-outline-danger active" data-filecheck-filter="changed">
                <?php echo Text::_('COM_ADMIN_FILECHECK_FILTER_CHANGED'); ?> (<span data-filecheck-count="changed">0</span>)
            </button>
            <button type="button" class="btn btn-outline-warning active" data-filecheck-filter="missing">
                <?php echo Text::_('COM_ADMIN_FILECHECK_FILTER_MISSING'); ?> (<span data-filecheck-count="missing">0</span>)
            </button>
            <button type="button" class="btn btn-outline-info active" data-filecheck-filter="unknown">
                <?php echo Text::_('COM_ADMIN_FILECHECK_FILTER_UNKNOWN'); ?> (<span data-filecheck-count="unknown">0</span>)
            </button>
            <button type="button" class="btn btn-outline-secondary active" data-filecheck-filter="invalid">
                <?php echo Text::_('COM_ADMIN_FILECHECK_FILTER_INVALID'); ?> (<span data-filecheck-count="invalid">0</span>)
            </button>
        </div>
        <button type="button" class="btn btn-primary" id="filecheck-export-button">
            <?php echo Text::_('COM_ADMIN_FILECHECK_EXPORT_BUTTON'); ?>
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped" id="filecheck-results-table">
            <thead>
                <tr>
                    <th scope="col"><?php echo Text::_('COM_ADMIN_FILECHECK_HEADING_STATUS'); ?></th>
                    <th scope="col"><?php echo Text::_('COM_ADMIN_FILECHECK_HEADING_PATH'); ?></th>
                    <th scope="col"><?php echo Text::_('COM_ADMIN_FILECHECK_HEADING_EXPECTED'); ?></th>
                    <th scope="col"><?php echo Text::_('COM_ADMIN_FILECHECK_HEADING_ACTUAL'); ?></th>
                </tr>
            </thead>
            <tbody id="filecheck-results-body"></tbody>
        </table>
    </div>
</div>

<div id="filecheck-generate-download" class="mt-3 d-none">
    <a href="#" id="filecheck-generate-download-link" class="btn btn-success">
        <?php echo Text::_('COM_ADMIN_FILECHECK_GENERATE_DOWNLOAD_BUTTON'); ?>
    </a>
</div>
