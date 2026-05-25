<?php

declare(strict_types=1);

namespace Phuture\Coherence\Interface;

/**
 * Interface for objects that can be converted to strings.
 *
 * This interface provides a standardized way for objects to be converted
 * to their string representation, making it easier to display, log, or
 * serialize objects as text.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
interface Stringable
{
    /**
     * Convert the object to a string representation.
     *
     * This method should return a meaningful string representation
     * of the object's data or state. The implementation should be
     * consistent and provide useful information for debugging,
     * logging, or display purposes.
     *
     * @return string The string representation of the object
     */
    public function toString(): string;
}
