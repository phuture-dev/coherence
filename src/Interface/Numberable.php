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
 * Example:
 * ```php
 * namespace Phuture\Coherence;
 *
 * use Phuture\Coherence\Interface\Numberable;
 *
 * class Price implements Numberable
 * {
 *     public function __construct(private string $value) {}
 *
 *     public function toNumber(): int|float
 *     {
 *         return (float) $this->value;
 *     }
 *
 *     public function toInt(): int
 *     {
 *         return (int) $this->value;
 *     }
 *
 *     public function toFloat(): float
 *     {
 *         return (float) $this->value;
 *     }
 * }
 *
 * $price = new Price('19.99');
 *
 * // Get the most appropriate numeric type
 * $value = $price->toNumber(); // Returns: 19.99 (float)
 *
 * // Get integer representation (truncates decimals)
 * $wholePrice = $price->toInt(); // Returns: 19 (int)
 *
 * // Get float representation
 * $floatPrice = $price->toFloat(); // Returns: 19.99 (float)
 * ```
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
interface Numberable
{
    /**
     * Converts the object to either an integer or float.
     *
     * This method should return the most appropriate numeric representation
     * of the object. If the value contains decimal places, it should return
     * a float; otherwise, it should return an integer.
     *
     * Example:
     * ```php
     * $number = new SomeNumberableObject();
     * $value = $number->toNumber(); // Returns: 42 or 42.5
     * ```
     *
     * @return int|float The numeric representation of the object
     */
    public function toNumber(): int|float;

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
}
