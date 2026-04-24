<?php

declare(strict_types=1);

namespace Phuture\Coherence\Interface;

/**
 * Interface for objects that represent a file and provide file metadata.
 *
 * This interface provides a standardized set of methods for retrieving
 * information about a file, including its size, extension, absolute path,
 * name, last modification time, MIME type, and contents. Any class that
 * wraps or represents a file should implement this interface so that
 * consumers can reliably obtain file information regardless of the
 * underlying implementation.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
interface Fileable
{
    /**
     * Returns the size of the file in bytes.
     *
     * The size represents the total number of bytes stored in the file
     * on disk. For directories or non-existent files, implementations
     * should throw an exception.
     *
     * @return int The file size in bytes
     */
    public function size(): int;

    /**
     * Returns the file extension without the leading dot.
     *
     * When the file has no extension, an empty string is returned.
     * For compound extensions like `.tar.gz`, only the last segment
     * (`gz`) is returned.
     *
     * @return string The file extension without the leading dot, or an empty string
     */
    public function extension(): string;

    /**
     * Returns the full absolute path to the file.
     *
     * The path includes the complete directory structure from the root
     * of the filesystem to the file itself, with no relative segments
     * like `..` or `.`.
     *
     * @return string The full absolute path to the file
     */
    public function path(): string;

    /**
     * Returns the name of the file including its extension.
     *
     * This is the final segment of the path, without any parent
     * directory components.
     *
     * @return string The file name with extension
     */
    public function name(): string;

    /**
     * Returns the last modification time of the file as a Unix timestamp.
     *
     * The timestamp represents the number of seconds since the Unix epoch
     * (January 1, 1970, 00:00:00 UTC) when the file's content was last
     * modified.
     *
     * @return int The last modification time as a Unix timestamp
     */
    public function lastModified(): int;

    /**
     * Returns the MIME type of the file detected from its content.
     *
     * The MIME type describes the nature of the file's content using a
     * standardized string format like `text/plain` or `image/png`. Detection
     * should be based on the file's actual content rather than its extension
     * when possible.
     *
     * @return string The MIME type of the file (e.g., 'text/plain', 'image/png')
     */
    public function mimeType(): string;

    /**
     * Reads and returns the entire contents of the file as a string.
     *
     * The complete file contents are loaded into memory and returned as
     * a single string. For large files, consider using a streaming
     * approach instead.
     *
     * @return string The complete contents of the file
     */
    public function read(): string;
}
