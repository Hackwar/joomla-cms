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
use Joomla\Filesystem\File;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller driving the batched file integrity check (both discovery-based and
 * upload-based), one AJAX call per batch.
 *
 * @since  6.2.0
 */
class CheckController extends BaseController
{
    /**
     * Start a discovery-based check: verifies every file listed in every discovered
     * checksums.txt source, then scans the whole installation for unlisted files.
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
            $data = $this->getFilecheckModel()->startDiscoveryCheck();
            $this->sendJson(['success' => true] + $data);
        } catch (\Throwable $e) {
            $this->sendJsonError($e);
        }
    }

    /**
     * Process the next batch of the active check (shared by discovery and upload modes).
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
            $data = $this->getFilecheckModel()->batchCheck();
            $this->sendJson(['success' => true] + $data);
        } catch (\Throwable $e) {
            $this->sendJsonError($e);
        }
    }

    /**
     * Handle the checksums.txt upload and start an upload-based check: the uploaded
     * file is the sole datasource, compared against the whole installation.
     *
     * @return  void
     *
     * @since   6.2.0
     */
    public function upload(): void
    {
        if (!$this->authorize()) {
            return;
        }

        $upload = $this->input->files->get('checksumsfile');

        if (!\is_array($upload) || (int) $upload['error'] !== \UPLOAD_ERR_OK || (int) $upload['size'] < 1) {
            $this->sendJsonError(new \RuntimeException(Text::_('COM_ADMIN_FILECHECK_ERROR_UPLOAD_FAILED')));

            return;
        }

        $tmpDest = $this->app->get('tmp_path') . '/' . uniqid('filecheck_', true) . '.txt';

        // Note: sendJson()/sendJsonError() call $this->app->close(), which exits the script
        // without running any pending finally blocks — so the temp file is cleaned up
        // explicitly on every path below rather than relying on try/finally.
        try {
            File::upload($upload['tmp_name'], $tmpDest, false, true);
            $lines = file($tmpDest, \FILE_IGNORE_NEW_LINES) ?: [];
        } catch (\Throwable $e) {
            if (is_file($tmpDest)) {
                File::delete($tmpDest);
            }

            $this->sendJsonError(new \RuntimeException(Text::_('COM_ADMIN_FILECHECK_ERROR_UPLOAD_FAILED')));

            return;
        }

        if (is_file($tmpDest)) {
            File::delete($tmpDest);
        }

        try {
            $data = $this->getFilecheckModel()->startUploadCheck($lines);
            $this->sendJson(['success' => true] + $data);
        } catch (\Throwable $e) {
            $this->sendJsonError($e);
        }
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
     * Common CSRF + permission guard for every AJAX task in this controller.
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
