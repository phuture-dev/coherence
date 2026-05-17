<?php

declare(strict_types=1);

namespace Phuture\Coherence\Type;

use Phuture\Coherence\Interface\Fileable;
use Phuture\Coherence\Support\FluentClass;
use Phuture\Coherence\Files as Transformer;

/**
 * A fluent wrapper around the Files utility class for chainable file manipulation.
 *
 * Each method delegates to the corresponding static method on `\Phuture\Coherence\Files`, stores the
 * result internally, and returns `$this` to enable method chaining. The wrapped
 * value is always the current file path as a string.
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
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Files extends FluentClass implements Fileable
{
    /**
     * Appends content to the end of the wrapped file.
     *
     * @param string $content The content to append to the file
     * @return self Returns the current instance for method chaining
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file cannot be written
     * @see \Phuture\Coherence\Files::append()
     */
    public function append(string $content): self
    {
        Transformer::append($this->data, $content);

        return $this;
    }

    /**
     * Changes the group ownership of the wrapped file.
     *
     * @param string|int $group The new group name or numeric group ID
     * @return self Returns the current instance for method chaining
     * @throws \Phuture\Coherence\Exception\RuntimeException When the group cannot be changed
     * @see \Phuture\Coherence\Files::chgrp()
     */
    public function chgrp(string|int $group): self
    {
        Transformer::chgrp($this->data, $group);

        return $this;
    }

    /**
     * Changes the permission mode of the wrapped file.
     *
     * @param int $mode The permission mode (octal notation, e.g. 0644)
     * @return self Returns the current instance for method chaining
     * @throws \Phuture\Coherence\Exception\RuntimeException When permissions cannot be changed
     * @see \Phuture\Coherence\Files::chmod()
     */
    public function chmod(int $mode): self
    {
        Transformer::chmod($this->data, $mode);

        return $this;
    }

    /**
     * Changes the owner of the wrapped file.
     *
     * @param string|int $user The new owner name or numeric user ID
     * @return self Returns the current instance for method chaining
     * @throws \Phuture\Coherence\Exception\RuntimeException When the owner cannot be changed
     * @see \Phuture\Coherence\Files::chown()
     */
    public function chown(string|int $user): self
    {
        Transformer::chown($this->data, $user);

        return $this;
    }

    /**
     * Copies the wrapped file to a new location and updates the internal path.
     *
     * After copying, the internal path remains unchanged (it still points to
     * the source). Use `copyTo()` when you want the path to switch to the
     * destination after copying.
     *
     * @param string $destination The destination file or directory path to copy to
     * @param bool $overwrite Whether to overwrite existing files at the destination (default: true)
     * @return self Returns the current instance for method chaining
     * @throws \Phuture\Coherence\Exception\RuntimeException
     *     When the source does not exist or the destination cannot be written
     * @see \Phuture\Coherence\Files::copy()
     */
    public function copy(string $destination, bool $overwrite = true): self
    {
        Transformer::copy($this->data, $destination, $overwrite);

        return $this;
    }

    /**
     * Copies the wrapped file to a new location and switches the internal path to the destination.
     *
     * This is the same as `copy()` but after copying, the internal path is updated
     * to point to the destination, so subsequent operations act on the copy.
     *
     * @param string $destination The destination file or directory path to copy to
     * @param bool $overwrite Whether to overwrite existing files at the destination (default: true)
     * @return self Returns the current instance for method chaining
     * @throws \Phuture\Coherence\Exception\RuntimeException
     *     When the source does not exist or the destination cannot be written
     * @see \Phuture\Coherence\Files::copy()
     */
    public function copyTo(string $destination, bool $overwrite = true): self
    {
        Transformer::copy($this->data, $destination, $overwrite);

        if (is_dir($destination)) {
            $destination = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($this->data);
        }

        $this->data = $destination;

        return $this;
    }

    /**
     * Deletes the file or directory at the current path.
     *
     * After deletion, the internal path is set to an empty string. This method
     * returns void because no further chaining is possible after the path is removed.
     *
     * @throws \Phuture\Coherence\Exception\RuntimeException When the path cannot be deleted
     * @see \Phuture\Coherence\Files::delete()
     */
    public function delete(): void
    {
        Transformer::delete($this->data);
        $this->data = '';
    }

    /**
     * Returns the file extension without the leading dot.
     *
     * @return string The file extension without the leading dot, or an empty string when there is none
     * @see \Phuture\Coherence\Files::extension()
     */
    public function extension(): string
    {
        return Transformer::extension($this->data);
    }

    /**
     * Returns the last modification time of the file as a Unix timestamp.
     *
     * @return int The last modification time as a Unix timestamp
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file does not exist or the time cannot be read
     * @see \Phuture\Coherence\Files::lastModified()
     */
    public function lastModified(): int
    {
        return Transformer::lastModified($this->data);
    }

    /**
     * Sets file permissions to make the current path writable.
     *
     * @param int $directoryMode The permission mode for directories (default: 0777)
     * @param int $fileMode The permission mode for files (default: 0666)
     * @return self Returns the current instance for method chaining
     * @throws \Phuture\Coherence\Exception\RuntimeException
     *     When the path does not exist or permissions cannot be changed
     * @see \Phuture\Coherence\Files::makeWritable()
     */
    public function makeWritable(int $directoryMode = 0777, int $fileMode = 0666): self
    {
        Transformer::makeWritable($this->data, $directoryMode, $fileMode);

        return $this;
    }

    /**
     * Returns the MIME type of the file detected from its content.
     *
     * @return string The MIME type of the file (e.g., 'text/plain', 'image/png')
     * @throws \Phuture\Coherence\Exception\RuntimeException
     *     When the file does not exist or the MIME type cannot be detected
     * @see \Phuture\Coherence\Files::mimeType()
     */
    public function mimeType(): string
    {
        return Transformer::mimeType($this->data);
    }

    /**
     * Moves the wrapped file to a new location and updates the internal path to the destination.
     *
     * @param string $destination The new file or directory path
     * @param bool $overwrite Whether to overwrite existing files at the destination (default: true)
     * @return self Returns the current instance for method chaining
     * @throws \Phuture\Coherence\Exception\RuntimeException
     *     When the source does not exist, the destination cannot be written, or the move fails
     * @see \Phuture\Coherence\Files::move()
     */
    public function move(string $destination, bool $overwrite = true): self
    {
        $resolvedDestination = $destination;
        if (file_exists($destination) && is_dir($destination)) {
            $resolvedDestination = rtrim($destination, DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR . basename($this->data);
        }

        Transformer::move($this->data, $destination, $overwrite);
        $this->data = $resolvedDestination;

        return $this;
    }

    /**
     * Returns the name of the file.
     *
     * @param bool $includeExtension Whether to include the file extension (default: true)
     * @return string The file name with or without extension
     * @see \Phuture\Coherence\Files::name()
     */
    public function name(bool $includeExtension = true): string
    {
        return Transformer::name($this->data, $includeExtension);
    }

    /**
     * Returns the full absolute path to the file.
     *
     * @return string The full absolute path to the file
     */
    public function path(): string
    {
        return (string) $this->data;
    }

    /**
     * Prepends content to the beginning of the wrapped file.
     *
     * @param string $content The content to prepend to the file
     * @return self Returns the current instance for method chaining
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file cannot be written
     * @see \Phuture\Coherence\Files::prepend()
     */
    public function prepend(string $content): self
    {
        Transformer::prepend($this->data, $content);

        return $this;
    }

    /**
     * Reads and returns the entire contents of the file as a string.
     *
     * @return string The complete contents of the file
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file does not exist or cannot be read
     * @see \Phuture\Coherence\Files::read()
     */
    public function read(): string
    {
        return Transformer::read($this->data);
    }

    /**
     * Renames the file within its current directory and updates the internal path.
     *
     * @param string $newName The new name (without directory path)
     * @param bool $overwrite Whether to overwrite an existing file with the new name (default: true)
     * @return self Returns the current instance for method chaining
     * @throws \Phuture\Coherence\Exception\RuntimeException
     *     When the path does not exist, the new name is empty, or the rename fails
     * @see \Phuture\Coherence\Files::rename()
     */
    public function rename(string $newName, bool $overwrite = true): self
    {
        $directory = dirname($this->data);

        Transformer::rename($this->data, $newName, $overwrite);
        $this->data = $directory . DIRECTORY_SEPARATOR . $newName;

        return $this;
    }

    /**
     * Replaces all occurrences of a search string within the wrapped file.
     *
     * @param string|array $search The value or values to search for
     * @param string|array $replace The replacement value or values
     * @return self Returns the current instance for method chaining
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file cannot be read or written
     * @see \Phuture\Coherence\Files::replaceInFile()
     */
    public function replaceInFile(string|array $search, string|array $replace): self
    {
        Transformer::replaceInFile($this->data, $search, $replace);

        return $this;
    }

    /**
     * Returns the size of the file in bytes.
     *
     * @return int The file size in bytes
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file does not exist or the size cannot be read
     * @see \Phuture\Coherence\Files::size()
     */
    public function size(): int
    {
        return Transformer::size($this->data);
    }

    /**
     * Writes content to the wrapped file, creating it if it does not exist.
     *
     * @param string $content The content to write to the file
     * @param int $mode The permission mode for the file (default: 0666)
     * @param bool $lock Whether to acquire an exclusive lock before writing (default: false)
     * @return self Returns the current instance for method chaining
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file cannot be written
     * @see \Phuture\Coherence\Files::write()
     */
    public function write(string $content, int $mode = 0666, bool $lock = false): self
    {
        Transformer::write($this->data, $content, $mode, $lock);

        return $this;
    }
}
