<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use Generator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Phuture\Coherence\Support\StaticClass;
use Phuture\Coherence\Exception\{InvalidArgumentException, RuntimeException};

/**
 * Comprehensive file system utility class with file manipulation, searching, and information capabilities.
 *
 * This utility class provides a complete toolkit for working with files and directories,
 * combining file system manipulation, file searching, file information retrieval, MIME type
 * detection, and file upload handling into a single cohesive interface.
 *
 * Key features:
 *
 * - **File Manipulation**: Copy, create, delete, move, read, and write files and directories
 * - **Path Utilities**: Normalize, join, and convert path separators across platforms
 * - **File Searching**: Find files and directories using glob-style patterns with optional recursion
 * - **File Information**: Retrieve size, extension, modification time, and other metadata
 * - **MIME Type Detection**: Identify file types using the system's MIME database
 * - **File Uploads**: Handle single and multiple file uploads from HTTP requests
 * - **Directory Listing**: List and filter directory contents
 * - **Fluent Interface**: Call `Files::of()` to obtain a chainable `\Phuture\Coherence\Type\Files` wrapper
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Files extends StaticClass
{
    /**
     * Copies a file or an entire directory to a new location.
     *
     * When copying a directory, all files and subdirectories within it are copied
     * recursively. By default, existing files at the destination are overwritten.
     * When `$overwrite` is false and the destination already exists, a
     * `\Phuture\Coherence\Exception\RuntimeException` is thrown.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::copy('/path/to/source.txt', '/path/to/destination.txt');
     * Files::copy('/path/to/source_dir', '/path/to/destination_dir');
     * Files::copy('/path/to/file.txt', '/path/to/existing.txt', overwrite: false);
     * ```
     *
     * @param string $source The source file or directory path to copy from
     * @param string $destination The destination file or directory path to copy to
     * @param bool $overwrite Whether to overwrite existing files at the destination (default: true)
     * @throws \Phuture\Coherence\Exception\RuntimeException
     *     When the source does not exist or the destination cannot be written
     * @see \Phuture\Coherence\Files::move()
     */
    public static function copy(string $source, string $destination, bool $overwrite = true): void
    {
        if (!file_exists($source)) {
            throw new RuntimeException(
                "Runtime Error: Source path {$source} does not exist"
            );
        }

        if (is_dir($source)) {
            self::copyDirectory($source, $destination, $overwrite);

            return;
        }

        if (file_exists($destination) && is_dir($destination)) {
            $destination = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($source);
        }

        $destinationDirectory = dirname($destination);

        if (!is_dir($destinationDirectory)) {
            self::createDirectory($destinationDirectory);
        }

        if (!$overwrite && file_exists($destination)) {
            throw new RuntimeException(
                "Runtime Error: Destination file {$destination} already exists"
            );
        }

        $tempPath = $destination . '.tmp.' . uniqid('', true);

        if (!copy($source, $tempPath)) {
            if (file_exists($tempPath)) {
                @\unlink($tempPath);
            }

            throw new RuntimeException(
                "Runtime Error: Unable to copy {$source} to {$destination}"
            );
        }

        if (!rename($tempPath, $destination)) {
            @\unlink($tempPath);

            throw new RuntimeException(
                "Runtime Error: Unable to copy {$source} to {$destination}"
            );
        }
    }

    /**
     * Creates a directory at the given path, including any parent directories that do not exist.
     *
     * If the directory already exists, this method does nothing. Throws when the
     * directory cannot be created due to permission issues or other filesystem errors.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::createDirectory('/path/to/new/directory');
     * Files::createDirectory('/path/to/dir', 0755);
     * ```
     *
     * @param string $path The directory path to create
     * @param int $mode The permission mode for the directory (default: 0777)
     * @throws \Phuture\Coherence\Exception\RuntimeException When the directory cannot be created
     * @see \Phuture\Coherence\Files::delete()
     * @see \Phuture\Coherence\Files::isDirectory()
     */
    public static function createDirectory(string $path, int $mode = 0777): void
    {
        if (is_dir($path)) {
            return;
        }

        if (!mkdir($path, $mode, true) && !is_dir($path)) {
            throw new RuntimeException(
                "Runtime Error: Unable to create directory {$path}"
            );
        }
    }

    /**
     * Deletes a file or an entire directory at the given path.
     *
     * When the path points to a directory, all of its contents (files and subdirectories)
     * are deleted recursively before the directory itself is removed. If the path does not
     * exist, this method does nothing.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::delete('/path/to/file.txt');
     * Files::delete('/path/to/directory');
     * ```
     *
     * @param string $path The file or directory path to delete
     * @throws \Phuture\Coherence\Exception\RuntimeException When the path cannot be deleted
     * @see \Phuture\Coherence\Files::copy()
     * @see \Phuture\Coherence\Files::move()
     */
    public static function delete(string $path): void
    {
        if (!file_exists($path) && !is_link($path)) {
            return;
        }

        if (is_link($path)) {
            if (!\unlink($path)) {
                throw new RuntimeException(
                    "Runtime Error: Unable to delete link {$path}"
                );
            }

            return;
        }

        if (is_dir($path)) {
            $tempPath = $path . '.deleting.' . uniqid('', true);

            if (!rename($path, $tempPath)) {
                self::deleteDirectory($path);

                return;
            }

            self::deleteDirectory($tempPath);

            return;
        }

        $tempPath = $path . '.deleting.' . uniqid('', true);

        if (rename($path, $tempPath)) {
            if (!\unlink($tempPath)) {
                @rename($tempPath, $path);

                throw new RuntimeException(
                    "Runtime Error: Unable to delete file {$path}"
                );
            }

            return;
        }

        if (!\unlink($path)) {
            throw new RuntimeException(
                "Runtime Error: Unable to delete file {$path}"
            );
        }
    }

    /**
     * Returns the parent directory path of a file or directory.
     *
     * Optionally, you can specify the number of levels to go up. For example,
     * a `$levels` of 2 goes up two parent directories.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::directory('/path/to/file.txt'); // '/path/to'
     * Files::directory('/path/to/file.txt', 2); // '/path'
     * Files::directory('/path/to/directory/'); // '/path/to'
     * ```
     *
     * @param string $path The file or directory path
     * @param int $levels The number of parent directories to go up (default: 1)
     * @return string The parent directory path
     * @see \Phuture\Coherence\Files::name()
     */
    public static function directory(string $path, int $levels = 1): string
    {
        return dirname($path, $levels);
    }

    /**
     * Determines whether a file or directory exists at the given path.
     *
     * Returns true for both files and directories. Use `isFile()` or
     * `isDirectory()` for type-specific checks.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::exists('/path/to/file.txt'); // true or false
     * Files::exists('/path/to/directory'); // true or false
     * ```
     *
     * @param string $path The path to check for existence
     * @return bool True when a file or directory exists at the path
     * @see \Phuture\Coherence\Files::isFile()
     * @see \Phuture\Coherence\Files::isDirectory()
     */
    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Extracts the file extension from a path.
     *
     * Returns the extension without the leading dot. When the file has no
     * extension, an empty string is returned.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::extension('/path/to/file.txt'); // 'txt'
     * Files::extension('/path/to/archive.tar.gz'); // 'gz'
     * Files::extension('/path/to/README'); // ''
     * ```
     *
     * @param string $path The file path to extract the extension from
     * @return string The file extension without the leading dot, or an empty string when there is none
     * @see \Phuture\Coherence\Files::name()
     * @see \Phuture\Coherence\Files::mimeType()
     */
    public static function extension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Finds files and directories matching the given glob-style patterns.
     *
     * Returns all files and directories within the specified directory that match
     * any of the provided masks. When `$recursive` is true, subdirectories are
     * searched as well. Masks use glob patterns: `*` matches any characters, `?`
     * matches a single character, and `[...]` matches a character class.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * $all = Files::find('*', '/path/to/dir');
     * $phpAndMd = Files::find(['*.php', '*.md'], '/path/to/src', recursive: true);
     * ```
     *
     * @param string|array $masks One or more glob patterns to match against (default: '*')
     * @param string $directory The directory to search in (default: '.')
     * @param bool $recursive Whether to search subdirectories (default: false)
     * @return array Array of file and directory paths matching the patterns
     * @throws \Phuture\Coherence\Exception\RuntimeException When the directory does not exist
     * @see \Phuture\Coherence\Files::findFiles()
     * @see \Phuture\Coherence\Files::findDirectories()
     */
    public static function find(string|array $masks = '*', string $directory = '.', bool $recursive = false): array
    {
        return self::findByType($masks, $directory, $recursive, null);
    }

    /**
     * Finds only directories matching the given glob-style patterns.
     *
     * Works like `find()` but excludes files from the results. Only
     * directories that match any of the provided masks are returned.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * $dirs = Files::findDirectories('*', '/path/to/project');
     * $srcDirs = Files::findDirectories('src*', '/path/to', recursive: true);
     * ```
     *
     * @param string|array $masks One or more glob patterns to match against (default: '*')
     * @param string $directory The directory to search in (default: '.')
     * @param bool $recursive Whether to search subdirectories (default: false)
     * @return array Array of directory paths matching the patterns
     * @throws \Phuture\Coherence\Exception\RuntimeException When the directory does not exist
     * @see \Phuture\Coherence\Files::find()
     * @see \Phuture\Coherence\Files::findFiles()
     */
    public static function findDirectories(
        string|array $masks = '*',
        string $directory = '.',
        bool $recursive = false
    ): array {
        return self::findByType($masks, $directory, $recursive, 'dir');
    }

    /**
     * Finds only files matching the given glob-style patterns.
     *
     * Works like `find()` but excludes directories from the results. Only
     * regular files that match any of the provided masks are returned.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * $phpFiles = Files::findFiles('*.php', '/path/to/src');
     * $allCode = Files::findFiles(['*.php', '*.js'], '/path/to/project', recursive: true);
     * ```
     *
     * @param string|array $masks One or more glob patterns to match against (default: '*')
     * @param string $directory The directory to search in (default: '.')
     * @param bool $recursive Whether to search subdirectories (default: false)
     * @return array Array of file paths matching the patterns
     * @throws \Phuture\Coherence\Exception\RuntimeException When the directory does not exist
     * @see \Phuture\Coherence\Files::find()
     * @see \Phuture\Coherence\Files::findDirectories()
     */
    public static function findFiles(string|array $masks = '*', string $directory = '.', bool $recursive = false): array
    {
        return self::findByType($masks, $directory, $recursive, 'file');
    }

    /**
     * Determines whether a path is absolute.
     *
     * An absolute path starts with a forward slash on Unix systems or a drive
     * letter followed by a colon on Windows (for example, `C:/`).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::isAbsolute('/usr/local/bin'); // true
     * Files::isAbsolute('relative/path'); // false
     * Files::isAbsolute('C:/Windows'); // true
     * ```
     *
     * @param string $path The path to check
     * @return bool True when the path is absolute, false when it is relative
     * @see \Phuture\Coherence\Files::normalizePath()
     * @see \Phuture\Coherence\Files::joinPaths()
     */
    public static function isAbsolute(string $path): bool
    {
        return strlen($path) > 0 && (
            $path[0] === '/' || $path[0] === '\\'
            || (
                strlen($path) >= 3
                && ctype_alpha($path[0])
                && $path[1] === ':'
                && ($path[2] === '/' || $path[2] === '\\')
            )
        );
    }

    /**
     * Determines whether the given path is a directory.
     *
     * Returns false for regular files, symlinks pointing to files, and
     * non-existent paths.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::isDirectory('/path/to/directory'); // true
     * Files::isDirectory('/path/to/file.txt'); // false
     * ```
     *
     * @param string $path The path to check
     * @return bool True when the path is a directory
     * @see \Phuture\Coherence\Files::isFile()
     * @see \Phuture\Coherence\Files::exists()
     */
    public static function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Determines whether a directory is empty (contains no files or subdirectories).
     *
     * Returns true when the directory exists and contains no entries. Returns
     * false when the directory contains at least one file or subdirectory.
     * Throws when the path is not a valid directory.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::isEmpty('/path/to/empty/dir'); // true
     * Files::isEmpty('/path/to/full/dir'); // false
     * ```
     *
     * @param string $path The directory path to check
     * @return bool True when the directory is empty, false when it contains entries
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the path is not a directory
     * @see \Phuture\Coherence\Files::isDirectory()
     * @see \Phuture\Coherence\Files::listing()
     */
    public static function isEmpty(string $path): bool
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException(
                "Invalid Argument: Path {$path} is not a directory"
            );
        }

        $handle = opendir($path);

        if ($handle === false) {
            throw new RuntimeException(
                "Runtime Error: Unable to open directory {$path}"
            );
        }

        $isEmpty = true;

        while (($entry = readdir($handle)) !== false) {
            if ($entry !== '.' && $entry !== '..') {
                $isEmpty = false;
                break;
            }
        }

        closedir($handle);

        return $isEmpty;
    }

    /**
     * Determines whether the given path is a regular file (not a directory).
     *
     * Returns false for directories, symlinks pointing to directories, and
     * non-existent paths.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::isFile('/path/to/file.txt'); // true
     * Files::isFile('/path/to/directory'); // false
     * ```
     *
     * @param string $path The path to check
     * @return bool True when the path is a regular file
     * @see \Phuture\Coherence\Files::isDirectory()
     * @see \Phuture\Coherence\Files::exists()
     */
    public static function isFile(string $path): bool
    {
        return is_file($path);
    }

    /**
     * Joins multiple path segments into a single normalized path.
     *
     * Segments are joined with forward slashes and the resulting path is
     * normalized to resolve `.` and `..` references. Trailing slashes on
     * individual segments are handled correctly.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::joinPaths('a', 'b', 'file.txt'); // 'a/b/file.txt'
     * Files::joinPaths('/a/', '/b/'); // '/a/b/'
     * Files::joinPaths('/a/', '/../b'); // '/b'
     * ```
     *
     * @param string ...$segments The path segments to join together
     * @return string The joined and normalized path
     * @see \Phuture\Coherence\Files::normalizePath()
     * @see \Phuture\Coherence\Files::isAbsolute()
     */
    public static function joinPaths(string ...$segments): string
    {
        return self::normalizePath(implode('/', $segments));
    }

    /**
     * Returns the last modification time of a file as a Unix timestamp.
     *
     * The timestamp represents the number of seconds since the Unix epoch
     * (January 1, 1970, 00:00:00 UTC) when the file was last modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * $timestamp = Files::lastModified('/path/to/file.txt');
     * echo date('Y-m-d H:i:s', $timestamp);
     * ```
     *
     * @param string $path The file path to check
     * @return int The last modification time as a Unix timestamp
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file does not exist or the time cannot be read
     * @see \Phuture\Coherence\Files::size()
     */
    public static function lastModified(string $path): int
    {
        if (!file_exists($path)) {
            throw new RuntimeException(
                "Runtime Error: Path {$path} does not exist"
            );
        }

        $time = filemtime($path);

        if ($time === false) {
            throw new RuntimeException(
                "Runtime Error: Unable to get modification time for {$path}"
            );
        }

        return $time;
    }

    /**
     * Lists the contents of a directory, optionally filtered by a pattern or callback.
     *
     * Returns an array of file and directory paths within the specified directory.
     * When `$filter` is a string, only entries matching the glob pattern are included.
     * When `$filter` is a callable, it receives each entry's full path as the first
     * argument and the entry name as the second argument, and must return true to include it.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * $all = Files::listing('/path/to/dir');
     * $phpFiles = Files::listing('/path/to/dir', '*.php');
     * $largeFiles = Files::listing('/path/to/dir', fn($path, $name) => filesize($path) > 1024);
     * ```
     *
     * @param string $path The directory path to list
     * @param string|callable|null $filter A glob pattern string, a callback function,
     *     or null for no filtering (default: null). The callback has the signature
     *     `function (string $fullPath, string $entryName): bool`
     * @return array Array of file and directory paths within the directory
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the path is not a directory
     * @throws \Phuture\Coherence\Exception\RuntimeException When the directory cannot be opened
     * @see \Phuture\Coherence\Files::findFiles()
     * @see \Phuture\Coherence\Files::isEmpty()
     */
    public static function listing(string $path, string|callable|null $filter = null): array
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException(
                "Invalid Argument: Path {$path} is not a directory"
            );
        }

        $handle = opendir($path);

        if ($handle === false) {
            throw new RuntimeException(
                "Runtime Error: Unable to open directory {$path}"
            );
        }

        $results = [];

        while (($entry = readdir($handle)) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $fullPath = $path . DIRECTORY_SEPARATOR . $entry;

            if ($filter === null) {
                $results[] = $fullPath;
            } elseif (is_string($filter)) {
                if (fnmatch($filter, $entry)) {
                    $results[] = $fullPath;
                }
            } elseif ($filter($fullPath, $entry)) {
                $results[] = $fullPath;
            }
        }

        closedir($handle);

        sort($results);

        return $results;
    }

    /**
     * Sets file and directory permissions to make a path writable.
     *
     * When the path points to a directory, permissions are applied recursively to
     * all files and subdirectories within it. Directories receive `$directoryMode`
     * and files receive `$fileMode`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::makeWritable('/path/to/file.txt');
     * Files::makeWritable('/path/to/directory', 0755, 0644);
     * ```
     *
     * @param string $path The file or directory path to make writable
     * @param int $directoryMode The permission mode for directories (default: 0777)
     * @param int $fileMode The permission mode for files (default: 0666)
     * @throws \Phuture\Coherence\Exception\RuntimeException
     *     When the path does not exist or permissions cannot be changed
     * @see \Phuture\Coherence\Files::createDirectory()
     */
    public static function makeWritable(string $path, int $directoryMode = 0777, int $fileMode = 0666): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException(
                "Runtime Error: Path {$path} does not exist"
            );
        }

        if (is_dir($path)) {
            if (!chmod($path, $directoryMode)) {
                throw new RuntimeException(
                    "Runtime Error: Unable to change permissions for {$path}"
                );
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                $itemMode = $item->isDir() ? $directoryMode : $fileMode;

                if (!chmod($item->getPathname(), $itemMode)) {
                    throw new RuntimeException(
                        "Runtime Error: Unable to change permissions for {$item->getPathname()}"
                    );
                }
            }

            return;
        }

        if (!chmod($path, $fileMode)) {
            throw new RuntimeException(
                "Runtime Error: Unable to change permissions for {$path}"
            );
        }
    }

    /**
     * Returns the MIME type of a file detected from the file's content.
     *
     * Uses the system's MIME database to determine the file type by examining
     * the file's actual content rather than relying on the file extension. This
     * provides a more accurate result than extension-based detection.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::mimeType('/path/to/image.png'); // 'image/png'
     * Files::mimeType('/path/to/document.pdf'); // 'application/pdf'
     * Files::mimeType('/path/to/script.php'); // 'text/x-php'
     * ```
     *
     * @param string $path The file path to detect the MIME type for
     * @return string The MIME type of the file (for example, 'text/plain', 'image/png')
     * @throws \Phuture\Coherence\Exception\RuntimeException
     *     When the file does not exist or the MIME type cannot be detected
     * @see \Phuture\Coherence\Files::extension()
     */
    public static function mimeType(string $path): string
    {
        if (!file_exists($path)) {
            throw new RuntimeException(
                "Runtime Error: File {$path} does not exist"
            );
        }

        if (!is_readable($path)) {
            throw new RuntimeException(
                "Runtime Error: File {$path} is not readable"
            );
        }

        $mimeType = mime_content_type($path);

        if ($mimeType === false) {
            throw new RuntimeException(
                "Runtime Error: Unable to detect MIME type for {$path}"
            );
        }

        return $mimeType;
    }

    /**
     * Moves a file or directory to a new location.
     *
     * This is equivalent to renaming the path. By default, existing files at the
     * destination are overwritten. When `$overwrite` is false and the destination
     * already exists, a `\Phuture\Coherence\Exception\RuntimeException` is thrown.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::move('/path/to/old.txt', '/path/to/new.txt');
     * Files::move('/path/to/old_dir', '/path/to/new_dir');
     * ```
     *
     * @param string $source The current file or directory path
     * @param string $destination The new file or directory path
     * @param bool $overwrite Whether to overwrite existing files at the destination (default: true)
     * @throws \Phuture\Coherence\Exception\RuntimeException
     *     When the source does not exist, the destination cannot be written, or the move fails
     * @see \Phuture\Coherence\Files::copy()
     * @see \Phuture\Coherence\Files::rename()
     */
    public static function move(string $source, string $destination, bool $overwrite = true): void
    {
        if (!file_exists($source)) {
            throw new RuntimeException(
                "Runtime Error: Source path {$source} does not exist"
            );
        }

        if (file_exists($destination) && is_dir($destination) && !is_dir($source)) {
            $destination = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($source);
        }

        if (!$overwrite && file_exists($destination)) {
            throw new RuntimeException(
                "Runtime Error: Destination path {$destination} already exists"
            );
        }

        $destinationDirectory = dirname($destination);

        if (!is_dir($destinationDirectory)) {
            self::createDirectory($destinationDirectory);
        }

        if (is_dir($source) && file_exists($destination)) {
            $tempDestination = $destination . '.replacing.' . uniqid('', true);
            rename($destination, $tempDestination);
            $moveSuccess = rename($source, $destination);
            self::deleteDirectory($tempDestination);

            if (!$moveSuccess) {
                @rename($tempDestination, $destination);

                throw new RuntimeException(
                    "Runtime Error: Unable to move {$source} to {$destination}"
                );
            }

            return;
        }

        if (!rename($source, $destination)) {
            throw new RuntimeException(
                "Runtime Error: Unable to move {$source} to {$destination}"
            );
        }
    }

    /**
     * Returns the name of a file or directory from a path.
     *
     * By default, returns the full basename including the extension. When
     * `$includeExtension` is false, the extension is stripped from the result.
     * Optionally, a custom suffix can be removed by passing it as the second
     * argument.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::name('/path/to/file.txt'); // 'file.txt'
     * Files::name('/path/to/file.txt', includeExtension: false); // 'file'
     * Files::name('/path/to/file.txt', '.txt'); // 'file'
     * Files::name('/path/to/directory/'); // 'directory'
     * ```
     *
     * @param string $path The file path to extract the name from
     * @param string|null $suffix An optional suffix to remove from the name (default: null)
     * @param bool $includeExtension Whether to include the file extension in the result (default: true)
     * @return string The name of the file or directory without the parent path
     * @see \Phuture\Coherence\Files::directory()
     * @see \Phuture\Coherence\Files::extension()
     */
    public static function name(string $path, ?string $suffix = null, bool $includeExtension = true): string
    {
        if ($suffix !== null) {
            return basename($path, $suffix);
        }

        if (!$includeExtension) {
            return pathinfo($path, PATHINFO_FILENAME);
        }

        return basename($path);
    }

    /**
     * Normalizes a path by resolving `.` and `..` references and converting slashes.
     *
     * Removes `.` segments, resolves `..` by removing the preceding directory,
     * and converts all directory separators to the system's standard separator.
     * A trailing slash is preserved only when the original path ends with a separator.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::normalizePath('/file/.'); // '/file'
     * Files::normalizePath('\\file\\..'); // '/'
     * Files::normalizePath('/file/../..'); // '/..'
     * Files::normalizePath('file/../../bar'); // '../bar'
     * ```
     *
     * @param string $path The path to normalize
     * @return string The normalized path using the system's directory separator
     * @see \Phuture\Coherence\Files::joinPaths()
     * @see \Phuture\Coherence\Files::unixSlashes()
     */
    public static function normalizePath(string $path): string
    {
        $parts = $path === '' ? [] : preg_split('~[/\\\\]+~', $path);
        $resolved = [];

        foreach ($parts as $part) {
            if ($part === '..' && $resolved && end($resolved) !== '..' && end($resolved) !== '') {
                array_pop($resolved);
            } elseif ($part !== '.') {
                $resolved[] = $part;
            }
        }

        return $resolved === ['']
            ? DIRECTORY_SEPARATOR
            : implode(DIRECTORY_SEPARATOR, $resolved);
    }

    /**
     * Creates a fluent wrapper around the given file path for method chaining.
     *
     * Returns a `\Phuture\Coherence\Type\Files` instance that wraps the provided
     * file path and exposes chainable file manipulation methods alongside the
     * `\Phuture\Coherence\Interface\Fileable` inspection methods.
     *
     * The path must point to an existing file. Directories are not accepted.
     * The path is resolved to its full absolute real path before being passed
     * to the wrapper.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * $content = Files::of('/path/to/draft.txt')
     *     ->copy('/path/to/backup.txt')
     *     ->rename('final.txt')
     *     ->write('Updated content')
     *     ->read();
     * // 'Updated content'
     * ```
     *
     * @param string $path The file path to wrap for fluent operations
     * @return \Phuture\Coherence\Type\Files A fluent wrapper instance that enables method chaining
     * @throws \Phuture\Coherence\Exception\RuntimeException When the path does not exist or is a directory
     * @see \Phuture\Coherence\Type\Files For the fluent wrapper implementation
     */
    public static function of(string $path): Type\Files
    {
        $realPath = realpath($path);

        if ($realPath === false) {
            throw new RuntimeException(
                "Runtime Error: File {$path} does not exist"
            );
        }

        if (is_dir($realPath)) {
            throw new RuntimeException(
                "Runtime Error: Path {$path} is a directory, not a file"
            );
        }

        return new Type\Files($realPath);
    }

    /**
     * Converts all directory separators in a path to the current platform's standard.
     *
     * On Windows, backslashes are used. On all other platforms, forward slashes
     * are used.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * // On Linux/macOS:
     * Files::platformSlashes('path\\to\\file.txt'); // 'path/to/file.txt'
     * // On Windows:
     * Files::platformSlashes('path/to/file.txt'); // 'path\to\file.txt'
     * ```
     *
     * @param string $path The path to convert
     * @return string The path with platform-specific slashes
     * @see \Phuture\Coherence\Files::unixSlashes()
     */
    public static function platformSlashes(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Reads and returns the entire contents of a file.
     *
     * Loads the complete file contents into a string. For large files, consider
     * using `readLines()` which processes the file line by line without loading
     * it all into memory at once.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * $content = Files::read('/path/to/file.txt');
     * echo $content;
     * ```
     *
     * @param string $path The file path to read
     * @return string The complete contents of the file
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file does not exist or cannot be read
     * @see \Phuture\Coherence\Files::readLines()
     * @see \Phuture\Coherence\Files::write()
     */
    public static function read(string $path): string
    {
        if (!file_exists($path)) {
            throw new RuntimeException(
                "Runtime Error: File {$path} does not exist"
            );
        }

        if (!is_readable($path)) {
            throw new RuntimeException(
                "Runtime Error: File {$path} is not readable"
            );
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException(
                "Runtime Error: Unable to read file {$path}"
            );
        }

        return $content;
    }

    /**
     * Reads a file line by line, yielding each line as a string.
     *
     * Returns a generator that produces one line at a time, making it
     * memory-efficient for large files. By default, trailing newline characters
     * (`\r` and `\n`) are stripped from each line.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * foreach (Files::readLines('/path/to/file.txt') as $lineNumber => $line) {
     *     echo "Line {$lineNumber}: {$line}\n";
     * }
     * ```
     *
     * @param string $path The file path to read
     * @param bool $stripNewLines Whether to remove trailing `\r` and `\n` from each line (default: true)
     * @return Generator<int, string> A generator yielding line numbers (zero-based) and line content
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file does not exist or cannot be opened
     * @see \Phuture\Coherence\Files::read()
     */
    public static function readLines(string $path, bool $stripNewLines = true): Generator
    {
        if (!file_exists($path)) {
            throw new RuntimeException(
                "Runtime Error: File {$path} does not exist"
            );
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException(
                "Runtime Error: Unable to open file {$path}"
            );
        }

        try {
            $lineNumber = 0;

            while (($line = fgets($handle)) !== false) {
                if ($stripNewLines) {
                    $line = rtrim($line, "\r\n");
                }

                yield $lineNumber => $line;
                $lineNumber++;
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * Renames a file or directory to a new name within the same directory.
     *
     * Unlike `move()`, which accepts a full destination path, this method takes
     * only the new name and keeps the file in its current parent directory. When
     * `$overwrite` is false and a file with the new name already exists, a
     * `\Phuture\Coherence\Exception\RuntimeException` is thrown.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::rename('/path/to/old.txt', 'new.txt');
     * Files::rename('/path/to/old_dir', 'new_dir', overwrite: false);
     * ```
     *
     * @param string $path The current file or directory path
     * @param string $newName The new name (without directory path)
     * @param bool $overwrite Whether to overwrite an existing file with the new name (default: true)
     * @throws \Phuture\Coherence\Exception\RuntimeException
     *     When the path does not exist, the new name is empty, or the rename fails
     * @see \Phuture\Coherence\Files::move()
     * @see \Phuture\Coherence\Files::name()
     */
    public static function rename(string $path, string $newName, bool $overwrite = true): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException(
                "Runtime Error: Path {$path} does not exist"
            );
        }

        if ($newName === '') {
            throw new RuntimeException(
                "Runtime Error: New name cannot be empty"
            );
        }

        $directory = dirname($path);
        $newPath = $directory . DIRECTORY_SEPARATOR . $newName;

        if (!$overwrite && file_exists($newPath)) {
            throw new RuntimeException(
                "Runtime Error: File {$newPath} already exists"
            );
        }

        if (!rename($path, $newPath)) {
            throw new RuntimeException(
                "Runtime Error: Unable to rename {$path} to {$newPath}"
            );
        }
    }

    /**
     * Returns the size of a file or directory in bytes.
     *
     * When the path points to a file, returns its exact size. When the path
     * points to a directory, returns the total combined size of all files
     * within it recursively. Throws when the path does not exist.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * $fileBytes = Files::size('/path/to/file.txt');
     * $dirBytes = Files::size('/path/to/directory');
     * ```
     *
     * @param string $path The file or directory path to check
     * @return int The size in bytes
     * @throws \Phuture\Coherence\Exception\RuntimeException When the path does not exist or the size cannot be read
     * @see \Phuture\Coherence\Files::lastModified()
     * @see \Phuture\Coherence\Files::mimeType()
     */
    public static function size(string $path): int
    {
        if (!file_exists($path)) {
            throw new RuntimeException(
                "Runtime Error: Path {$path} does not exist"
            );
        }

        if (is_file($path)) {
            $size = filesize($path);

            if ($size === false) {
                throw new RuntimeException(
                    "Runtime Error: Unable to get size of {$path}"
                );
            }

            return $size;
        }

        if (is_dir($path)) {
            return self::directorySize($path);
        }

        throw new RuntimeException(
            "Runtime Error: Path {$path} is not a regular file or directory"
        );
    }

    /**
     * Converts all directory separators in a path to forward slashes (Unix style).
     *
     * Useful for normalizing paths for display or for use in contexts that
     * require forward slashes regardless of the operating system.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::unixSlashes('path\\to\\file.txt'); // 'path/to/file.txt'
     * ```
     *
     * @param string $path The path to convert
     * @return string The path with forward slashes
     * @see \Phuture\Coherence\Files::platformSlashes()
     * @see \Phuture\Coherence\Files::normalizePath()
     */
    public static function unixSlashes(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Changes the group ownership of a file or directory.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::chgrp('/path/to/file.txt', 'www-data');
     * Files::chgrp('/path/to/directory', 1000);
     * ```
     *
     * @param string $path The file or directory path
     * @param string|int $group The new group name or numeric group ID
     * @throws \Phuture\Coherence\Exception\RuntimeException When the path does not exist or the group cannot be changed
     * @see \Phuture\Coherence\Files::chmod()
     * @see \Phuture\Coherence\Files::chown()
     */
    public static function chgrp(string $path, string|int $group): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException(
                "Runtime Error: Path {$path} does not exist"
            );
        }

        if (!chgrp($path, $group)) {
            throw new RuntimeException(
                "Runtime Error: Unable to change group of {$path}"
            );
        }
    }

    /**
     * Changes the permission mode of a file or directory.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::chmod('/path/to/file.txt', 0644);
     * Files::chmod('/path/to/directory', 0755);
     * ```
     *
     * @param string $path The file or directory path
     * @param int $mode The permission mode (octal notation, e.g. 0755)
     * @throws \Phuture\Coherence\Exception\RuntimeException When the path does not exist or permissions cannot be changed
     * @see \Phuture\Coherence\Files::chown()
     * @see \Phuture\Coherence\Files::chgrp()
     * @see \Phuture\Coherence\Files::makeWritable()
     */
    public static function chmod(string $path, int $mode): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException(
                "Runtime Error: Path {$path} does not exist"
            );
        }

        if (!chmod($path, $mode)) {
            throw new RuntimeException(
                "Runtime Error: Unable to change permissions of {$path}"
            );
        }
    }

    /**
     * Changes the owner of a file or directory.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::chown('/path/to/file.txt', 'www-data');
     * Files::chown('/path/to/directory', 1000);
     * ```
     *
     * @param string $path The file or directory path
     * @param string|int $user The new owner name or numeric user ID
     * @throws \Phuture\Coherence\Exception\RuntimeException When the path does not exist or the owner cannot be changed
     * @see \Phuture\Coherence\Files::chmod()
     * @see \Phuture\Coherence\Files::chgrp()
     */
    public static function chown(string $path, string|int $user): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException(
                "Runtime Error: Path {$path} does not exist"
            );
        }

        if (!chown($path, $user)) {
            throw new RuntimeException(
                "Runtime Error: Unable to change owner of {$path}"
            );
        }
    }

    /**
     * Creates a file or directory at the given path.
     *
     * When the path ends with a directory separator or contains no extension,
     * a directory is created (including parent directories). Otherwise, an
     * empty file is created using `touch()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::create('/path/to/new/file.txt'); // creates empty file
     * Files::create('/path/to/new/directory/'); // creates directory
     * ```
     *
     * @param string $path The file or directory path to create
     * @param int $mode The permission mode (default: 0777 for directories, 0666 for files)
     * @throws \Phuture\Coherence\Exception\RuntimeException When the path cannot be created
     * @see \Phuture\Coherence\Files::createDirectory()
     * @see \Phuture\Coherence\Files::delete()
     */
    public static function create(string $path, int $mode = 0777): void
    {
        if (file_exists($path)) {
            return;
        }

        $isDirectory = str_ends_with($path, '/') || str_ends_with($path, '\\') || (
            !str_contains(basename($path), '.') && !str_contains($path, '.')
        );

        if ($isDirectory) {
            self::createDirectory($path, $mode);

            return;
        }

        $directory = dirname($path);

        if (!is_dir($directory)) {
            self::createDirectory($directory, $mode);
        }

        if (!touch($path)) {
            throw new RuntimeException(
                "Runtime Error: Unable to create file {$path}"
            );
        }

        chmod($path, $mode & 0666);
    }

    /**
     * Returns the target of a symbolic link.
     *
     * Returns the path that the symbolic link points to. The returned path
     * may be relative or absolute depending on how the link was created.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * $target = Files::getLink('/path/to/symlink');
     * ```
     *
     * @param string $path The symbolic link path
     * @return string The target path that the link points to
     * @throws \Phuture\Coherence\Exception\RuntimeException When the path is not a symbolic link or cannot be read
     * @see \Phuture\Coherence\Files::isLink()
     * @see \Phuture\Coherence\Files::link()
     */
    public static function getLink(string $path): string
    {
        if (!is_link($path)) {
            throw new RuntimeException(
                "Runtime Error: Path {$path} is not a symbolic link"
            );
        }

        $target = readlink($path);

        if ($target === false) {
            throw new RuntimeException(
                "Runtime Error: Unable to read symbolic link {$path}"
            );
        }

        return $target;
    }

    /**
     * Determines whether a file has an exclusive lock.
     *
     * Attempts to acquire a non-blocking shared lock on the file. When the
     * lock cannot be acquired because another process holds an exclusive
     * lock, returns true.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * if (Files::isLocked('/path/to/file.txt')) {
     *     echo 'File is locked by another process';
     * }
     * ```
     *
     * @param string $path The file path to check
     * @return bool True when the file appears to be exclusively locked
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file does not exist or cannot be opened
     * @see \Phuture\Coherence\Files::write()
     */
    public static function isLocked(string $path): bool
    {
        if (!file_exists($path)) {
            throw new RuntimeException(
                "Runtime Error: File {$path} does not exist"
            );
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException(
                "Runtime Error: Unable to open file {$path}"
            );
        }

        $wouldBlock = false;
        $locked = !flock($handle, LOCK_SH | LOCK_NB, $wouldBlock);

        if (!$locked) {
            flock($handle, LOCK_UN);
        }

        fclose($handle);

        return $locked;
    }

    /**
     * Determines whether the given path is a symbolic link.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::isLink('/path/to/symlink'); // true or false
     * ```
     *
     * @param string $path The path to check
     * @return bool True when the path is a symbolic link
     * @see \Phuture\Coherence\Files::link()
     * @see \Phuture\Coherence\Files::getLink()
     * @see \Phuture\Coherence\Files::unlink()
     */
    public static function isLink(string $path): bool
    {
        return is_link($path);
    }

    /**
     * Determines whether a file or directory is readable.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::isReadable('/path/to/file.txt'); // true or false
     * ```
     *
     * @param string $path The path to check
     * @return bool True when the path exists and is readable
     * @see \Phuture\Coherence\Files::isWritable()
     */
    public static function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    /**
     * Determines whether a file or directory is writable.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::isWritable('/path/to/file.txt'); // true or false
     * ```
     *
     * @param string $path The path to check
     * @return bool True when the path exists and is writable
     * @see \Phuture\Coherence\Files::isReadable()
     */
    public static function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    /**
     * Creates a symbolic link from the target to the link path.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::link('/path/to/target', '/path/to/symlink');
     * ```
     *
     * @param string $target The path that the link will point to
     * @param string $link The path where the symbolic link will be created
     * @throws \Phuture\Coherence\Exception\RuntimeException When the link cannot be created
     * @see \Phuture\Coherence\Files::isLink()
     * @see \Phuture\Coherence\Files::getLink()
     * @see \Phuture\Coherence\Files::unlink()
     */
    public static function link(string $target, string $link): void
    {
        $directory = dirname($link);

        if (!is_dir($directory)) {
            self::createDirectory($directory);
        }

        if (!symlink($target, $link)) {
            throw new RuntimeException(
                "Runtime Error: Unable to create symbolic link from {$target} to {$link}"
            );
        }
    }

    /**
     * Replaces all occurrences of a search string with a replacement string within a file.
     *
     * Reads the file, performs the replacement, and writes the result back.
     * When `$search` is an array, each occurrence of any search value is
     * replaced with the corresponding value in `$replace`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::replaceInFile('/path/to/config.php', 'old-value', 'new-value');
     * Files::replaceInFile('/path/to/template.html', ['{{name}}', '{{email}}'], ['John', 'john@example.com']);
     * ```
     *
     * @param string $path The file path to modify
     * @param string|array $search The value or values to search for
     * @param string|array $replace The replacement value or values
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file does not exist or cannot be written
     * @see \Phuture\Coherence\Files::write()
     * @see \Phuture\Coherence\Files::read()
     */
    public static function replaceInFile(string $path, string|array $search, string|array $replace): void
    {
        $content = self::read($path);
        $replaced = str_replace($search, $replace, $content);

        self::write($path, $replaced);
    }

    /**
     * Removes a symbolic link.
     *
     * Validates that the path is a symbolic link before removing it. Throws
     * when the path is not a symbolic link or cannot be removed.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::unlink('/path/to/symlink');
     * ```
     *
     * @param string $path The symbolic link path to remove
     * @throws \Phuture\Coherence\Exception\RuntimeException When the path is not a symbolic link or cannot be removed
     * @see \Phuture\Coherence\Files::isLink()
     * @see \Phuture\Coherence\Files::link()
     */
    public static function unlink(string $path): void
    {
        if (!is_link($path)) {
            throw new RuntimeException(
                "Runtime Error: Path {$path} is not a symbolic link"
            );
        }

        if (!\unlink($path)) {
            throw new RuntimeException(
                "Runtime Error: Unable to remove symbolic link {$path}"
            );
        }
    }

    /**
     * Handles file uploads from an HTTP request, saving files to a destination directory.
     *
     * Supports both single file uploads and multiple file uploads (when the request
     * field uses array notation like `files[]`). When the upload field contains
     * multiple files (detected automatically), all files are saved and an array of
     * file information arrays is returned.
     *
     * When the first parameter is `"files"` and the request contains `files[]`,
     * the method detects the array structure and processes all uploaded files.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * // Single file upload: <input type="file" name="avatar">
     * $result = Files::upload('avatar', '/path/to/uploads');
     * // Returns: ['name' => 'photo.jpg', 'path' => '...', 'size' => 12345, ...]
     *
     * // Multiple file upload: <input type="file" name="files[]" multiple>
     * $results = Files::upload('files', '/path/to/uploads');
     * // Returns: [['name' => 'a.jpg', ...], ['name' => 'b.jpg', ...]]
     * ```
     *
     * @param string $key The form field name from the upload request
     * @param string $destination The directory path where uploaded files should be saved
     * @param array|null $files The files array to use (default: null, which uses $_FILES)
     * @return array|false A single file info array, an array of file info arrays
     *     for multiple uploads, or false on failure
     * @see \Phuture\Coherence\Files::move()
     * @see \Phuture\Coherence\Files::createDirectory()
     */
    public static function upload(string $key, string $destination, ?array $files = null): array|false
    {
        $files = $files ?? $_FILES;

        if (!isset($files[$key])) {
            return false;
        }

        $file = $files[$key];

        self::createDirectory($destination);

        if (is_array($file['name'])) {
            return self::handleMultipleUpload($file, $destination);
        }

        return self::handleSingleUpload($file, $destination);
    }

    /**
     * Appends content to the end of an existing file.
     *
     * When the file does not exist, it is created. Parent directories are
     * created automatically when they do not exist.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::write('/path/to/log.txt', 'First line');
     * Files::append('/path/to/log.txt', 'Second line');
     * ```
     *
     * @param string $path The file path to append to
     * @param string $content The content to append to the file
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file cannot be written
     * @see \Phuture\Coherence\Files::write()
     * @see \Phuture\Coherence\Files::prepend()
     */
    public static function append(string $path, string $content): void
    {
        $directory = dirname($path);

        if (!is_dir($directory)) {
            self::createDirectory($directory);
        }

        $handle = fopen($path, 'a');

        if ($handle === false) {
            throw new RuntimeException(
                "Runtime Error: Unable to open file {$path} for appending"
            );
        }

        try {
            $result = fwrite($handle, $content);

            if ($result === false) {
                throw new RuntimeException(
                    "Runtime Error: Unable to append to file {$path}"
                );
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * Prepends content to the beginning of an existing file.
     *
     * When the file does not exist, it is created with the given content.
     * Parent directories are created automatically when they do not exist.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::write('/path/to/file.txt', 'Original content');
     * Files::prepend('/path/to/file.txt', 'Header: ');
     * ```
     *
     * @param string $path The file path to prepend to
     * @param string $content The content to prepend to the file
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file cannot be written
     * @see \Phuture\Coherence\Files::write()
     * @see \Phuture\Coherence\Files::append()
     */
    public static function prepend(string $path, string $content): void
    {
        $existing = file_exists($path) ? self::read($path) : '';

        self::write($path, $content . $existing);
    }

    /**
     * Writes content to a file, creating the file if it does not exist.
     *
     * If the file already exists, its contents are replaced entirely. Parent
     * directories are created automatically when they do not exist.
     *
     * When `$lock` is true, an exclusive lock is acquired before writing to
     * prevent concurrent writes from corrupting the file.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Files;
     *
     * Files::write('/path/to/file.txt', 'Hello, World!');
     * Files::write('/path/to/new/file.txt', 'New content', 0644);
     * Files::write('/path/to/file.txt', 'Locked write', lock: true);
     * ```
     *
     * @param string $path The file path to write to
     * @param string $content The content to write to the file
     * @param int $mode The permission mode for the file (default: 0666)
     * @param bool $lock Whether to acquire an exclusive lock before writing (default: false)
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file cannot be written
     * @see \Phuture\Coherence\Files::read()
     * @see \Phuture\Coherence\Files::append()
     * @see \Phuture\Coherence\Files::prepend()
     */
    public static function write(string $path, string $content, int $mode = 0666, bool $lock = false): void
    {
        $directory = dirname($path);

        if (!is_dir($directory)) {
            self::createDirectory($directory);
        }

        $flags = $lock ? LOCK_EX : 0;
        $tempPath = $path . '.tmp.' . uniqid('', true);
        $result = file_put_contents($tempPath, $content, $flags);

        if ($result === false) {
            if (file_exists($tempPath)) {
                @\unlink($tempPath);
            }

            throw new RuntimeException(
                "Runtime Error: Unable to write to file {$path}"
            );
        }

        if (!rename($tempPath, $path)) {
            @\unlink($tempPath);

            throw new RuntimeException(
                "Runtime Error: Unable to write to file {$path}"
            );
        }

        if (file_exists($path)) {
            chmod($path, $mode);
        }
    }

    /**
     * Calculates the total size of all files in a directory recursively.
     *
     * @param string $path The directory path to calculate size for
     * @return int The total size in bytes
     */
    private static function directorySize(string $path): int
    {
        $totalSize = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $totalSize += $item->getSize();
            }
        }

        return $totalSize;
    }

    /**
     * Copies a directory recursively to a new location.
     *
     * @param string $source The source directory to copy
     * @param string $destination The destination directory path
     * @param bool $overwrite Whether to overwrite existing files
     */
    private static function copyDirectory(string $source, string $destination, bool $overwrite): void
    {
        $source = rtrim($source, '/\\');

        self::createDirectory($destination);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = substr($item->getPathname(), strlen($source) + 1);
            $targetPath = $destination . DIRECTORY_SEPARATOR . $relativePath;

            if ($item->isDir()) {
                self::createDirectory($targetPath);
            } else {
                $targetDir = dirname($targetPath);

                if (!is_dir($targetDir)) {
                    self::createDirectory($targetDir);
                }

                if (!$overwrite && file_exists($targetPath)) {
                    continue;
                }

                if (!copy($item->getPathname(), $targetPath)) {
                    throw new RuntimeException(
                        "Runtime Error: Unable to copy {$item->getPathname()} to {$targetPath}"
                    );
                }
            }
        }
    }

    /**
     * Deletes a directory and all of its contents recursively.
     *
     * @param string $path The directory to delete
     */
    private static function deleteDirectory(string $path): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                if (!rmdir($item->getPathname())) {
                    throw new RuntimeException(
                        "Runtime Error: Unable to remove directory {$item->getPathname()}"
                    );
                }
            } else {
                if (!unlink($item->getPathname())) {
                    throw new RuntimeException(
                        "Runtime Error: Unable to delete file {$item->getPathname()}"
                    );
                }
            }
        }

        if (!rmdir($path)) {
            throw new RuntimeException(
                "Runtime Error: Unable to remove directory {$path}"
            );
        }
    }

    /**
     * Finds files and/or directories by type, matching the given masks.
     *
     * @param string|array $masks The glob patterns to match
     * @param string $directory The directory to search in
     * @param bool $recursive Whether to search subdirectories
     * @param string|null $type The type filter: 'file', 'dir', or null for both
     * @return array Array of matching paths
     */
    private static function findByType(string|array $masks, string $directory, bool $recursive, ?string $type): array
    {
        if (!is_dir($directory)) {
            throw new RuntimeException(
                "Runtime Error: Directory {$directory} does not exist"
            );
        }

        $masks = (array) $masks;
        $results = [];

        if ($recursive) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                if ($type === 'file' && !$item->isFile()) {
                    continue;
                }

                if ($type === 'dir' && !$item->isDir()) {
                    continue;
                }

                if (self::matchesAnyMask($item->getFilename(), $masks)) {
                    $results[] = $item->getPathname();
                }
            }
        } else {
            $handle = opendir($directory);

            if ($handle === false) {
                throw new RuntimeException(
                    "Runtime Error: Unable to open directory {$directory}"
                );
            }

            while (($entry = readdir($handle)) !== false) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $fullPath = $directory . DIRECTORY_SEPARATOR . $entry;

                if ($type === 'file' && !is_file($fullPath)) {
                    continue;
                }

                if ($type === 'dir' && !is_dir($fullPath)) {
                    continue;
                }

                if (self::matchesAnyMask($entry, $masks)) {
                    $results[] = $fullPath;
                }
            }

            closedir($handle);
        }

        $results = array_unique($results);
        sort($results);

        return $results;
    }

    /**
     * Handles a multiple file upload by processing each file individually.
     *
     * @param array $files The upload information array with array values from $_FILES
     * @param string $destination The directory to save files in
     * @return array Array of file information arrays for each successfully uploaded file
     */
    private static function handleMultipleUpload(array $files, string $destination): array
    {
        $results = [];
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];

            $result = self::handleSingleUpload($file, $destination);

            if ($result !== false) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Handles a single file upload by moving it to the destination directory.
     *
     * @param array $file The upload information array from $_FILES
     * @param string $destination The directory to save the file in
     * @return array|false File information array or false on failure
     */
    private static function handleSingleUpload(array $file, string $destination): array|false
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $filename = basename($file['name']);
        $targetPath = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        $moved = is_uploaded_file($file['tmp_name'])
            ? move_uploaded_file($file['tmp_name'], $targetPath)
            : rename($file['tmp_name'], $targetPath);

        if (!$moved) {
            return false;
        }

        return [
            'name' => $filename,
            'path' => $targetPath,
            'size' => (int) $file['size'],
            'type' => $file['type'],
            'extension' => pathinfo($filename, PATHINFO_EXTENSION),
        ];
    }

    /**
     * Checks whether a filename matches any of the given glob patterns.
     *
     * @param string $filename The filename to test
     * @param array $masks The glob patterns to match against
     * @return bool True when the filename matches at least one pattern
     */
    private static function matchesAnyMask(string $filename, array $masks): bool
    {
        foreach ($masks as $mask) {
            if (fnmatch($mask, $filename)) {
                return true;
            }
        }

        return false;
    }
}
