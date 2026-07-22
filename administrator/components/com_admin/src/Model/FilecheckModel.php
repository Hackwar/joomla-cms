<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\Admin\Administrator\Helper\FilecheckHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model for the file integrity check feature.
 *
 * @since  6.2.0
 */
class FilecheckModel extends BaseDatabaseModel
{
    private const SESSION_CHECK_STATE     = 'com_admin.filecheck.check.state';
    private const SESSION_GENERATE_STATE  = 'com_admin.filecheck.generate.state';
    private const SESSION_GENERATE_BUFFER = 'com_admin.filecheck.generate.buffer';

    /**
     * Get every discovered checksums.txt source (Joomla core, components, packages).
     *
     * @return  \stdClass[]
     *
     * @since   6.2.0
     */
    public function getSources(): array
    {
        return FilecheckHelper::discoverSources($this->getDatabase());
    }

    /**
     * Start a discovery-based check: build the combined known-file map from every
     * discovered checksums.txt source, and the whole-installation unknown-file scan queue.
     *
     * @return  array
     *
     * @since   6.2.0
     */
    public function startDiscoveryCheck(): array
    {
        $root         = JPATH_ROOT;
        $knownSet     = [];
        $invalidLines = [];

        foreach ($this->getSources() as $source) {
            $handle = @fopen($source->path, 'r');

            if ($handle === false) {
                continue;
            }

            while (($rawLine = fgets($handle)) !== false) {
                $result = FilecheckHelper::sanitizeChecksumLine($rawLine, $root);

                if ($result['status'] === 'ok') {
                    $knownSet[$result['path']] = $result['hash'];
                } elseif ($result['status'] === 'invalid') {
                    $result['source'] = $source->name;
                    $invalidLines[]   = $result;
                }
            }

            fclose($handle);
        }

        return $this->initializeCheckState('discovery', $knownSet, $invalidLines);
    }

    /**
     * Start an upload-based check: the uploaded file is the sole datasource for the
     * combined known-file map, compared against the whole installation.
     *
     * @param   string[]  $lines  The uploaded checksums.txt file, split into raw lines
     *
     * @return  array
     *
     * @since   6.2.0
     */
    public function startUploadCheck(array $lines): array
    {
        $root         = JPATH_ROOT;
        $knownSet     = [];
        $invalidLines = [];

        foreach ($lines as $rawLine) {
            $result = FilecheckHelper::sanitizeChecksumLine($rawLine, $root);

            if ($result['status'] === 'ok') {
                $knownSet[$result['path']] = $result['hash'];
            } elseif ($result['status'] === 'invalid') {
                $invalidLines[] = $result;
            }
        }

        return $this->initializeCheckState('upload', $knownSet, $invalidLines);
    }

    /**
     * Shared state initialization for both discovery-based and upload-based checks.
     *
     * @param   string    $mode          'discovery' or 'upload'
     * @param   string[]  $knownSet      Map of root-relative path => expected sha256 hash
     * @param   array     $invalidLines  Rejected/malformed lines encountered while building the known set
     *
     * @return  array
     *
     * @since   6.2.0
     */
    private function initializeCheckState(string $mode, array $knownSet, array $invalidLines): array
    {
        $unknownQueue = FilecheckHelper::walkFiles(JPATH_ROOT);

        $state = (object) [
            'mode'         => $mode,
            'phase'        => 'verify',
            'knownSet'     => $knownSet,
            'verifyQueue'  => array_keys($knownSet),
            'verifyIndex'  => 0,
            'unknownQueue' => $unknownQueue,
            'unknownIndex' => 0,
            'counts'       => [
                'ok'      => 0,
                'changed' => 0,
                'missing' => 0,
                'unknown' => 0,
                'invalid' => \count($invalidLines),
            ],
        ];

        $this->setCheckState($state);

        return [
            'phase'        => $state->phase,
            'grandTotal'   => \count($state->verifyQueue) + \count($unknownQueue),
            'invalidLines' => $invalidLines,
            'counts'       => $state->counts,
        ];
    }

    /**
     * Process the next batch of the active check phase (verify, then unknown-scan).
     * Shared by both discovery-based and upload-based checks.
     *
     * @param   integer  $batchSize  Maximum number of items to process in this call
     *
     * @return  array
     *
     * @throws  \RuntimeException  If no check is currently in progress for this session
     *
     * @since   6.2.0
     */
    public function batchCheck(int $batchSize = 300): array
    {
        $state = $this->getCheckState();

        if ($state === null) {
            throw new \RuntimeException(Text::_('COM_ADMIN_FILECHECK_ERROR_NO_STATE'));
        }

        $results   = [];
        $processed = 0;
        $root      = JPATH_ROOT;

        if ($state->phase === 'verify') {
            $slice = \array_slice($state->verifyQueue, $state->verifyIndex, $batchSize);

            foreach ($slice as $relPath) {
                $absolute = $root . '/' . $relPath;
                $expected = $state->knownSet[$relPath];

                if (!is_file($absolute)) {
                    $results[] = ['status' => 'missing', 'path' => $relPath, 'expected' => $expected, 'actual' => null];
                    $state->counts['missing']++;
                } else {
                    $actual = FilecheckHelper::hashFile($absolute);

                    if ($actual === $expected) {
                        $state->counts['ok']++;
                    } else {
                        $results[] = ['status' => 'changed', 'path' => $relPath, 'expected' => $expected, 'actual' => $actual];
                        $state->counts['changed']++;
                    }
                }

                $processed++;
            }

            $state->verifyIndex += $processed;

            if ($state->verifyIndex >= \count($state->verifyQueue)) {
                $state->phase = 'unknown';
            }
        } elseif ($state->phase === 'unknown') {
            $slice = \array_slice($state->unknownQueue, $state->unknownIndex, $batchSize);

            foreach ($slice as $relPath) {
                if (!isset($state->knownSet[$relPath])) {
                    $results[] = ['status' => 'unknown', 'path' => $relPath, 'expected' => null, 'actual' => null];
                    $state->counts['unknown']++;
                } else {
                    $state->counts['ok']++;
                }

                $processed++;
            }

            $state->unknownIndex += $processed;

            if ($state->unknownIndex >= \count($state->unknownQueue)) {
                $state->phase = 'complete';
            }
        }

        $grandOffset = $state->verifyIndex + $state->unknownIndex;
        $grandTotal  = \count($state->verifyQueue) + \count($state->unknownQueue);
        $done        = $state->phase === 'complete';

        if ($done) {
            $this->resetCheckState();
        } else {
            $this->setCheckState($state);
        }

        return [
            'phase'       => $state->phase,
            'processed'   => $processed,
            'grandOffset' => $grandOffset,
            'grandTotal'  => $grandTotal,
            'results'     => $results,
            'counts'      => $state->counts,
            'done'        => $done,
        ];
    }

    /**
     * Start generating a clean checksums.txt of the whole installation. Builds the file
     * queue and resets the session output buffer; nothing is written to server disk.
     *
     * @return  array
     *
     * @since   6.2.0
     */
    public function startGenerate(): array
    {
        $queue = FilecheckHelper::walkFiles(JPATH_ROOT);

        $state = (object) [
            'queue' => $queue,
            'index' => 0,
        ];

        $session = Factory::getApplication()->getSession();
        $session->set(self::SESSION_GENERATE_STATE, $state);
        $session->set(self::SESSION_GENERATE_BUFFER, '');

        return ['total' => \count($queue)];
    }

    /**
     * Hash the next batch of files and append the resulting lines to the session buffer.
     *
     * @param   integer  $batchSize  Maximum number of files to hash in this call
     *
     * @return  array
     *
     * @throws  \RuntimeException  If no generate run is currently in progress for this session
     *
     * @since   6.2.0
     */
    public function batchGenerate(int $batchSize = 150): array
    {
        $session = Factory::getApplication()->getSession();
        $state   = $session->get(self::SESSION_GENERATE_STATE);

        if ($state === null) {
            throw new \RuntimeException(Text::_('COM_ADMIN_FILECHECK_ERROR_NO_STATE'));
        }

        $root  = JPATH_ROOT;
        $slice = \array_slice($state->queue, $state->index, $batchSize);
        $lines = '';

        foreach ($slice as $relPath) {
            $hash = FilecheckHelper::hashFile($root . '/' . $relPath);

            if ($hash !== null) {
                $lines .= $hash . '  ' . $relPath . "\n";
            }
        }

        $buffer = (string) $session->get(self::SESSION_GENERATE_BUFFER, '');
        $session->set(self::SESSION_GENERATE_BUFFER, $buffer . $lines);

        $state->index += \count($slice);
        $session->set(self::SESSION_GENERATE_STATE, $state);

        $total = \count($state->queue);

        return [
            'processed' => \count($slice),
            'offset'    => $state->index,
            'total'     => $total,
            'done'      => $state->index >= $total,
        ];
    }

    /**
     * Retrieve the fully generated checksums.txt content and clear it from the session.
     * This is the only place the generated content leaves session storage; it is never
     * written to a file on the server.
     *
     * @return  string
     *
     * @since   6.2.0
     */
    public function takeGenerateBuffer(): string
    {
        $session = Factory::getApplication()->getSession();
        $buffer  = (string) $session->get(self::SESSION_GENERATE_BUFFER, '');

        $session->set(self::SESSION_GENERATE_BUFFER, null);
        $session->set(self::SESSION_GENERATE_STATE, null);

        return $buffer;
    }

    /**
     * @return  \stdClass|null
     *
     * @since   6.2.0
     */
    private function getCheckState(): ?\stdClass
    {
        return Factory::getApplication()->getSession()->get(self::SESSION_CHECK_STATE);
    }

    /**
     * @param   \stdClass  $state  The check state to persist
     *
     * @return  void
     *
     * @since   6.2.0
     */
    private function setCheckState(\stdClass $state): void
    {
        Factory::getApplication()->getSession()->set(self::SESSION_CHECK_STATE, $state);
    }

    /**
     * @return  void
     *
     * @since   6.2.0
     */
    private function resetCheckState(): void
    {
        Factory::getApplication()->getSession()->set(self::SESSION_CHECK_STATE, null);
    }
}
