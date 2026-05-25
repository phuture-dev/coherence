<?php

declare(strict_types=1);

namespace Phuture\Coherence\Interface;

/**
 * Interface for objects that can be converted to numeric types.
 *
 * This interface provides a standardized way for objects to be converted
 * to different numeric representations. Classes implementing this interface
 * can be treated as numbers in various contexts, allowing for flexible
 * type conversion and numeric operations.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
interface Numberable
{
    /**
     * Converts the object to a float.
     *
     * This method should convert the object to a floating-point number.
     * Use this method when you need decimal precision or want to ensure
     * the result can contain fractional parts.
     *
     * Example:
     * ```php
     * $number = new SomeNumberableObject();
     * $value = $number->toFloat(); // Returns: 42.0 or 42.5
     * ```
     *
     * @return float The float representation of the object
     */
    public function toFloat(): float;

    /**
     * Converts the object to an integer.
     *
     * This method should convert the object to an integer by truncating
     * any decimal portion if necessary. Use this method when you need
     * the whole number representation of the object.
     *
     * Example:
     * ```php
     * $number = new SomeNumberableObject();
     * $value = $number->toInt(); // Returns: 42 (even if original was 42.9)
     * ```
     *
     * @return int The integer representation of the object
     */
    public function toInt(): int;

    /**
     * Converts the object to a string.
     *
     * This method should convert the object to its string representation.
     * Use this method when you need to display the number as text or
     * concatenate it with other strings.
     *
     * Example:
     * ```php
     * $number = new SomeNumberableObject();
     * $value = $number->toString(); // Returns: "42" or "42.5"
     * ```
     *
     * @return string The string representation of the object
     */
    public function toString(): string;
}
