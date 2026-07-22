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

$body = '<div class="text-center">'
    . '<h4 id="filecheck-progress-header" aria-live="assertive">' . Text::_('COM_ADMIN_FILECHECK_CHECKER_HEADER_INIT') . '</h4>'
    . '<p id="filecheck-progress-message" aria-live="polite"></p>'
    . '<div id="filecheck-progress" class="progress">'
    . '<div id="filecheck-progress-bar" class="progress-bar bg-success" role="progressbar"'
    . ' aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>'
    . '</div>'
    . '</div>';

echo HTMLHelper::_(
    'bootstrap.renderModal',
    'filecheck-progress-modal',
    [
        'title'    => Text::_('COM_ADMIN_FILECHECK_CHECKER_MODAL_TITLE'),
        'width'    => '600px',
        'height'   => '220px',
        'backdrop' => 'static',
        'keyboard' => false,
    ],
    $body
);

echo HTMLHelper::_('form.token', ['id' => 'filecheck-token']);
