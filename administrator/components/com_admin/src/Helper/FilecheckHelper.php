<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Helper;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\Exception\FilesystemException;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper class for the file integrity check feature.
 *
 * @since  6.2.0
 */
final class FilecheckHelper
{
    /**
     * Root-relative directories excluded from the whole-installation scan and from
     * the "generate clean checksums.txt" file enumeration. These hold runtime/cache
     * data that changes on normal operation and is not part of the shipped installation.
     *
     * @var    string[]
     * @since  6.2.0
     */
    public const EXCLUDED_DIRS = [
        'administrator/cache',
        'cache',
        'tmp',
        'logs',
        'media/cache',
        '.git',
        'node_modules',
    ];

    /**
     * Extension types this feature discovers checksums.txt files for. Joomla core itself
     * is discovered as the ordinary 'file' extension row with element 'joomla' — there is
     * no special-casing, it uses the exact same manifest-folder convention as any other
     * 'file' type extension.
     *
     * @var    string[]
     * @since  6.2.0
     */
    private const DISCOVERABLE_TYPES = [
        'component',
        'package',
        'plugin',
        'module',
        'template',
        'library',
        'language',
        'file',
    ];

    /**
     * Discover every known checksums.txt source: every installed extension of every
     * discoverable type that ships one at its expected location.
     *
     * @param   DatabaseInterface  $db  The database driver
     *
     * @return  \stdClass[]  Each with key, name, type, path (absolute) and mtime
     *
     * @since   6.2.0
     */
    public static function discoverSources(DatabaseInterface $db): array
    {
        $sources = [];

        $query = $db->createQuery()
            ->select($db->quoteName(['element', 'name', 'type', 'folder', 'client_id']))
            ->from($db->quoteName('#__extensions'))
            ->whereIn($db->quoteName('type'), self::DISCOVERABLE_TYPES, ParameterType::STRING);
        $db->setQuery($query);

        foreach ($db->loadObjectList() as $extension) {
            if (empty($extension->element)) {
                continue;
            }

            $path = self::resolveSourcePath($extension);

            if ($path === null || !is_file($path)) {
                continue;
            }

            $sources[] = (object) [
                'key'   => $extension->element,
                'name'  => $extension->name,
                'type'  => $extension->type,
                'path'  => $path,
                'mtime' => filemtime($path) ?: null,
            ];
        }

        return $sources;
    }

    /**
     * Resolve the expected absolute checksums.txt path for a single #__extensions row,
     * mirroring the folder conventions Joomla's own installer adapters use to locate an
     * extension's manifest (see InstallerHelper::getInstallationXML() and
     * PackageAdapter::setupInstallPaths()).
     *
     * @param   \stdClass  $extension  A row from #__extensions (element, type, folder, client_id)
     *
     * @return  string|null  The expected absolute path, or null for an unsupported type
     *
     * @since   6.2.0
     */
    private static function resolveSourcePath(\stdClass $extension): ?string
    {
        // client_id: 0 = site, 1 = administrator, 3 = api.
        $clientPath = match ((int) ($extension->client_id ?? 1)) {
            0       => JPATH_SITE,
            3       => \defined('JPATH_API') ? JPATH_API : JPATH_ADMINISTRATOR,
            default => JPATH_ADMINISTRATOR,
        };

        switch ($extension->type) {
            case 'component':
                // Administrator-side component folder only.
                return JPATH_ADMINISTRATOR . '/components/' . $extension->element . '/checksums.txt';

            case 'package':
                return JPATH_ADMINISTRATOR . '/manifests/packages/' . $extension->element . '/checksums.txt';

            case 'plugin':
                if (empty($extension->folder)) {
                    return null;
                }

                return JPATH_PLUGINS . '/' . $extension->folder . '/' . $extension->element . '/checksums.txt';

            case 'module':
                return $clientPath . '/modules/' . $extension->element . '/checksums.txt';

            case 'template':
                return $clientPath . '/templates/' . $extension->element . '/checksums.txt';

            case 'language':
                return $clientPath . '/language/' . $extension->element . '/checksums.txt';

            case 'library':
                return JPATH_ADMINISTRATOR . '/manifests/libraries/' . $extension->element . '/checksums.txt';

            case 'file':
                return JPATH_ADMINISTRATOR . '/manifests/files/' . $extension->element . '/checksums.txt';

            default:
                return null;
        }
    }

    /**
     * Parse and sanitize a single line of a checksums.txt file.
     *
     * @param   string  $rawLine  The raw line, as read from the file
     * @param   string  $root     The absolute installation root every path must resolve within
     *
     * @return  array{status: string, hash?: string, path?: string, reason?: string, raw?: string}
     *
     * @since   6.2.0
     */
    public static function sanitizeChecksumLine(string $rawLine, string $root): array
    {
        $line = rtrim($rawLine, "\r\n");

        if ($line === '' || str_starts_with(ltrim($line), '#')) {
            return ['status' => 'skip'];
        }

        if (!preg_match('/^([0-9a-fA-F]{64})  (.+)$/', $line, $matches)) {
            return ['status' => 'invalid', 'reason' => 'COM_ADMIN_FILECHECK_INVALID_MALFORMED', 'raw' => $line];
        }

        $relPath = str_replace('\\', '/', $matches[2]);

        if (
            $relPath === ''
            || $relPath[0] === '/'
            || preg_match('#^[A-Za-z]:#', $relPath) === 1
            || str_contains($relPath, "\0")
        ) {
            return ['status' => 'invalid', 'reason' => 'COM_ADMIN_FILECHECK_INVALID_ROOTED_PATH', 'raw' => $line];
        }

        $candidate = rtrim($root, '/\\') . '/' . $relPath;

        try {
            Path::check($candidate, $root);
        } catch (FilesystemException $e) {
            return ['status' => 'invalid', 'reason' => 'COM_ADMIN_FILECHECK_INVALID_TRAVERSAL', 'raw' => $line];
        }

        return ['status' => 'ok', 'hash' => strtolower($matches[1]), 'path' => $relPath];
    }

    /**
     * Determine whether a root-relative path (file or directory, directories end in '/')
     * falls under one of the excluded runtime directories.
     *
     * @param   string  $relPath  A root-relative path
     *
     * @return  boolean
     *
     * @since   6.2.0
     */
    public static function isExcluded(string $relPath): bool
    {
        $relPath = trim(str_replace('\\', '/', $relPath), '/');

        foreach (self::EXCLUDED_DIRS as $excluded) {
            if ($relPath === $excluded || str_starts_with($relPath, $excluded . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Recursively enumerate every file under an installation root, as root-relative
     * paths, skipping symlinks and the excluded runtime directories.
     *
     * @param   string  $root  The absolute installation root to walk
     *
     * @return  string[]  Sorted, root-relative file paths
     *
     * @since   6.2.0
     */
    public static function walkFiles(string $root): array
    {
        $root  = rtrim(Path::clean($root), '/\\');
        $files = [];

        $flags             = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        $directoryIterator = new \RecursiveDirectoryIterator($root, $flags);

        $filter = new \RecursiveCallbackFilterIterator(
            $directoryIterator,
            function (\SplFileInfo $current) use ($root) {
                if ($current->isLink()) {
                    return false;
                }

                $relPath = ltrim(str_replace('\\', '/', substr($current->getPathname(), \strlen($root))), '/');

                if ($current->isDir()) {
                    return !self::isExcluded($relPath . '/');
                }

                return !self::isExcluded($relPath);
            }
        );

        $iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($iterator as $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            if (!$fileInfo->isFile() || $fileInfo->isLink()) {
                continue;
            }

            $files[] = ltrim(str_replace('\\', '/', substr($fileInfo->getPathname(), \strlen($root))), '/');
        }

        sort($files);

        return $files;
    }

    /**
     * Compute the sha256 hash of a file.
     *
     * @param   string  $absolutePath  Absolute path of the file to hash
     *
     * @return  string|null  The lowercase hex hash, or null if the file could not be hashed
     *
     * @since   6.2.0
     */
    public static function hashFile(string $absolutePath): ?string
    {
        if (!is_file($absolutePath) || is_link($absolutePath)) {
            return null;
        }

        $hash = @hash_file('sha256', $absolutePath);

        return $hash === false ? null : $hash;
    }
}
