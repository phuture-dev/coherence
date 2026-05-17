<?php

declare(strict_types=1);

namespace Phuture\Coherence\Enum;

/**
 * Enumeration for file compression and archive formats.
 *
 * This enum defines the supported archive and compression formats for
 * \Phuture\Coherence\Files::compress() and \Phuture\Coherence\Files::decompress().
 * Each case maps to a specific format and its underlying implementation.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
enum CompressionFormat
{
    /**
     * ZIP archive format (.zip).
     *
     * Supports compressing files and directories. Uses the nelexa/zip
     * pure-PHP library, so no system zip command is required.
     */
    case Zip;

    /**
     * TAR archive format (.tar).
     *
     * Supports archiving files and directories without compression.
     * Uses PHP's built-in PharData class.
     */
    case Tar;

    /**
     * GZIP-compressed TAR archive format (.tar.gz).
     *
     * Supports compressing both files and directories by first building a TAR
     * archive and then applying GZIP compression. Uses PHP's built-in PharData
     * class for TAR creation and the zlib extension for GZIP compression.
     */
    case Gzip;
}
