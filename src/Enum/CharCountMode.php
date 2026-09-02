<?php

declare(strict_types=1);

namespace Phuture\Coherence\Enum;

/**
 * Enumeration for byte-frequency reporting modes.
 *
 * Controls which byte values are reported and in what shape. Use this enum with
 * \Phuture\Coherence\Strings::charCounts() to select between a full frequency
 * table, a filtered table, or a string of the distinct bytes found.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
enum CharCountMode
{
    /**
     * Report only the byte values that do not occur in the string.
     *
     * The result is an array keyed by byte value, containing every one of the 256
     * possible bytes whose frequency is zero. Each value in the array is therefore 0.
     */
    case Absent;

    /**
     * Report every one of the 256 possible byte values.
     *
     * This is the default mode. The result is an array keyed by byte value from 0 to
     * 255, whose values are the number of times each byte occurs in the string,
     * including the bytes that never occur.
     */
    case All;

    /**
     * Report only the byte values that occur at least once in the string.
     *
     * The result is an array keyed by byte value, containing only the bytes whose
     * frequency is greater than zero, mapped to their number of occurrences.
     */
    case Present;

    /**
     * Report the distinct bytes found in the string as a string.
     *
     * The result is a string containing each byte that occurs at least once, listed a
     * single time and ordered by ascending byte value.
     */
    case Unique;
}
