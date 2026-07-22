<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\View\Filecheck;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Admin\Administrator\Model\FilecheckModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Filecheck View class for the Admin component.
 *
 * @since  6.2.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The discovered checksums.txt sources.
     *
     * @var    \stdClass[]
     * @since  6.2.0
     */
    protected $sources = [];

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse
     *
     * @return  void
     *
     * @since   6.2.0
     *
     * @throws  \Exception
     */
    public function display($tpl = null): void
    {
        if (!$this->getCurrentUser()->authorise('core.admin')) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        /** @var FilecheckModel $model */
        $model         = $this->getModel();
        $this->sources = $model->getSources();

        $this->addToolbar();
        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Setup the Toolbar.
     *
     * @return  void
     *
     * @since   6.2.0
     */
    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_ADMIN_FILECHECK_VIEW_TITLE'), 'shield-alt filecheck');

        $toolbar = $this->getDocument()->getToolbar();

        $toolbar->basicButton('filecheck-check', 'COM_ADMIN_FILECHECK_TOOLBAR_CHECK')
            ->icon('icon-shield-alt');

        $toolbar->basicButton('filecheck-generate', 'COM_ADMIN_FILECHECK_TOOLBAR_GENERATE')
            ->icon('icon-file-alt');
    }

    /**
     * Push language strings and the web asset onto the document for the JS runner.
     *
     * @return  void
     *
     * @since   6.2.0
     */
    protected function prepareDocument(): void
    {
        foreach (
            [
                'COM_ADMIN_FILECHECK_CHECKER_HEADER_INIT',
                'COM_ADMIN_FILECHECK_CHECKER_HEADER_COMPLETE',
                'COM_ADMIN_FILECHECK_CHECKER_HEADER_ERROR',
                'COM_ADMIN_FILECHECK_CHECKER_MESSAGE_VERIFY_PHASE',
                'COM_ADMIN_FILECHECK_CHECKER_MESSAGE_UNKNOWN_PHASE',
                'COM_ADMIN_FILECHECK_CHECKER_MESSAGE_COMPLETE',
                'COM_ADMIN_FILECHECK_CHECKER_MESSAGE_GENERATE_RUNNING',
                'COM_ADMIN_FILECHECK_CHECKER_MESSAGE_GENERATE_COMPLETE',
                'COM_ADMIN_FILECHECK_STATUS_CHANGED',
                'COM_ADMIN_FILECHECK_STATUS_MISSING',
                'COM_ADMIN_FILECHECK_STATUS_UNKNOWN',
                'COM_ADMIN_FILECHECK_STATUS_INVALID',
                'COM_ADMIN_FILECHECK_INVALID_MALFORMED',
                'COM_ADMIN_FILECHECK_INVALID_ROOTED_PATH',
                'COM_ADMIN_FILECHECK_INVALID_TRAVERSAL',
                'COM_ADMIN_FILECHECK_ERROR_UPLOAD_FAILED',
                'JLIB_JS_AJAX_ERROR_OTHER',
                'JLIB_JS_AJAX_ERROR_PARSE',
            ] as $string
        ) {
            Text::script($string);
        }

        $wa = $this->getDocument()->getWebAssetManager();
        $wa->useScript('keepalive')
            ->useScript('com_admin.filecheck');
    }
}
