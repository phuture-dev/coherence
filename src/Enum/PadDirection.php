<?php

declare(strict_types=1);

namespace Phuture\Coherence\Enum;

/**
 * Enumeration for string padding direction.
 *
 * Controls which side of a string padding characters are added to.
 * Use this enum with \Phuture\Coherence\Strings::pad() to specify
 * where the pad string is applied.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
enum PadDirection
{
    /**
     * Padding is added to both sides of the string equally.
     *
     * When the required padding is an odd number, the extra character
     * is added to the right side.
     */
    case Both;

    /**
     * Padding is prepended to the left (start) of the string.
     *
     * Characters are added before the first character of the string
     * until the target length is reached.
     */
    case Left;

    /**
     * Padding is appended to the right (end) of the string.
     *
     * This is the default direction. Characters are added after the last
     * character of the string until the target length is reached.
     */
    case Right;
}
