<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Redirect\Administrator\Helper\RedirectHelper;

$displayData = [
    'textPrefix' => 'COM_REDIRECT',
    'formURL'    => 'index.php?option=com_redirect&view=links',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help4.x:Redirects:_Links',
    'icon'       => 'icon-map-signs redirect',
];

$app  = Factory::getApplication();
$user = $this->getCurrentUser();

if ($user->authorise('core.create', 'com_redirect')) {
    $displayData['createURL'] = 'index.php?option=com_redirect&task=link.add';
}

if (
    $user->authorise('core.create', 'com_redirect')
    && $user->authorise('core.edit', 'com_redirect')
    && $user->authorise('core.edit.state', 'com_redirect')
) {
    $displayData['formAppend'] = HTMLHelper::_(
        'bootstrap.renderModal',
        'collapseModal',
        [
            'title'  => Text::_('COM_REDIRECT_BATCH_OPTIONS'),
            'footer' => $this->loadTemplate('batch_footer'),
        ],
        $this->loadTemplate('batch_body')
    );
}

$collectUrlsEnabled = RedirectHelper::collectUrlsEnabled();
$redirectPluginId   = $this->redirectPluginId;

// Show messages about the enabled plugin and if the plugin should collect URLs
if (!$redirectPluginId && $collectUrlsEnabled) {
    $app->enqueueMessage(Text::sprintf('COM_REDIRECT_COLLECT_URLS_ENABLED', Text::_('COM_REDIRECT_PLUGIN_ENABLED')), 'warning');
} else {
    /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
    $wa = $this->document->getWebAssetManager();
    $wa->useScript('joomla.dialog-autocreate');

    $popupOptions = [
        'popupType'  => 'iframe',
        'textHeader' => Text::_('COM_REDIRECT_EDIT_PLUGIN_SETTINGS'),
        'src'        => Route::_('index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $redirectPluginId . '&tmpl=component&layout=modal', false),
    ];
    $link = HTMLHelper::_(
        'link',
        '#',
        Text::_('COM_REDIRECT_SYSTEM_PLUGIN'),
        [
            'class'                 => 'alert-link',
            'data-joomla-dialog'    => $this->escape(json_encode($popupOptions, JSON_UNESCAPED_SLASHES)),
            'data-checkin-url'      => Route::_('index.php?option=com_plugins&task=plugins.checkin&format=json&cid[]=' . $redirectPluginId),
            'data-close-on-message' => '',
            'data-reload-on-close'  => '',
        ],
    );

    if (!$redirectPluginId && !$collectUrlsEnabled) {
        $app->enqueueMessage(
            Text::sprintf('COM_REDIRECT_COLLECT_MODAL_URLS_DISABLED', Text::_('COM_REDIRECT_PLUGIN_ENABLED'), $link),
            'warning'
        );
    } else {
        $app->enqueueMessage(Text::sprintf('COM_REDIRECT_PLUGIN_MODAL_DISABLED', $link), 'error');
    }
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
