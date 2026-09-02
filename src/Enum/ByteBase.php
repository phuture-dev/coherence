<?php

declare(strict_types=1);

namespace Phuture\Coherence\Enum;

/**
 * Enumeration for the base used when scaling a byte count into larger units.
 *
 * Selects how many bytes make up the next unit when a raw byte count is expressed in
 * kilobytes, megabytes, and beyond. Use this enum with
 * \Phuture\Coherence\Numbers::fileSize() to choose between the binary base used by
 * operating systems and the decimal base used on storage packaging.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
enum ByteBase
{
    /**
     * Scale each unit by 1024 bytes.
     *
     * This is the default base. It matches how operating systems and filesystems
     * usually report sizes, so one kilobyte is 1024 bytes and one megabyte is 1048576
     * bytes.
     */
    case Binary;

    /**
     * Scale each unit by 1000 bytes.
     *
     * It matches the decimal prefixes used by storage manufacturers and network
     * equipment, so one kilobyte is 1000 bytes and one megabyte is 1000000 bytes.
     */
    case Decimal;
}
