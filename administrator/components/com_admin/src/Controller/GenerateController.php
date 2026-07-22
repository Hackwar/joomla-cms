<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\Component\Admin\Administrator\Model\FilecheckModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller driving the batched "generate a clean checksums.txt" process, and its
 * one-shot download. The generated content only ever exists in the PHP session and
 * in the browser download response — it is never written to a file on server disk.
 *
 * @since  6.2.0
 */
class GenerateController extends BaseController
{
    /**
     * Start a generate run: enumerate every hashable file in the installation.
     *
     * @return  void
     *
     * @since   6.2.0
     */
    public function start(): void
    {
        if (!$this->authorize()) {
            return;
        }

        try {
            $data = $this->getFilecheckModel()->startGenerate();
            $this->sendJson(['success' => true] + $data);
        } catch (\Throwable $e) {
            $this->sendJsonError($e);
        }
    }

    /**
     * Hash the next batch of files, appending the resulting lines to the session buffer.
     *
     * @return  void
     *
     * @since   6.2.0
     */
    public function batch(): void
    {
        if (!$this->authorize()) {
            return;
        }

        try {
            $data = $this->getFilecheckModel()->batchGenerate();
            $this->sendJson(['success' => true] + $data);
        } catch (\Throwable $e) {
            $this->sendJsonError($e);
        }
    }

    /**
     * Stream the completed checksums.txt from the session buffer as a download, then
     * clear it from the session. Not a JSON response.
     *
     * @return  void
     *
     * @since   6.2.0
     */
    public function download(): void
    {
        if (!Session::checkToken('request')) {
            throw new \Exception(Text::_('JINVALID_TOKEN_NOTICE'), 403);
        }

        if (!$this->app->getIdentity()->authorise('core.admin')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $buffer = $this->getFilecheckModel()->takeGenerateBuffer();

        $this->app->setHeader('Content-Type', 'text/plain; charset=utf-8', true);
        $this->app->setHeader('Content-Disposition', 'attachment; filename="checksums.txt"', true);
        $this->app->setHeader('Cache-Control', 'must-revalidate', true);
        $this->app->sendHeaders();

        echo $buffer;

        $this->app->close();
    }

    /**
     * @return  FilecheckModel
     *
     * @since   6.2.0
     */
    private function getFilecheckModel(): FilecheckModel
    {
        /** @var FilecheckModel $model */
        $model = $this->getModel('Filecheck');

        return $model;
    }

    /**
     * Common CSRF + permission guard for the batch AJAX tasks in this controller.
     *
     * @return  boolean
     *
     * @since   6.2.0
     */
    private function authorize(): bool
    {
        if (!Session::checkToken('request')) {
            $this->sendJsonError(new \RuntimeException(Text::_('JINVALID_TOKEN_NOTICE')), 403);

            return false;
        }

        if (!$this->app->getIdentity()->authorise('core.admin')) {
            $this->sendJsonError(new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR')), 403);

            return false;
        }

        $this->app->allowCache(false);

        if (\function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        return true;
    }

    /**
     * @param   array    $data    The response payload
     * @param   integer  $status  The HTTP status code
     *
     * @return  void
     *
     * @since   6.2.0
     */
    private function sendJson(array $data, int $status = 200): void
    {
        $this->app->setHeader('status', $status);
        $this->app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        $this->app->sendHeaders();

        echo json_encode($data);

        $this->app->close();
    }

    /**
     * @param   \Throwable  $e       The error to report
     * @param   integer     $status  The HTTP status code
     *
     * @return  void
     *
     * @since   6.2.0
     */
    private function sendJsonError(\Throwable $e, int $status = 500): void
    {
        $this->sendJson(['success' => false, 'message' => $e->getMessage()], $status);
    }
}
