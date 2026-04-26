<?php

declare(strict_types=1);

namespace Phuture\Coherence\Type;

use Phuture\Coherence\Enum\RoundingMode;
use Phuture\Coherence\Support\FluentClass;
use Phuture\Coherence\Interface\Numberable;
use Phuture\Coherence\Numbers as Transformer;

/**
 * A fluent wrapper around the Numbers utility class for chainable number manipulation.
 *
 * Each method delegates to the corresponding static method on `Numbers`, stores the
 * result internally, and returns `$this` to enable method chaining. Retrieve the final
 * value by calling `get()`, `toFloat()`, `toInt()`, or `toNumber()`.
 *
 * Arithmetic methods (add, subtract, multiply, divide, modulus, squareRoot) use BCMath
 * internally and store their result as a string. Subsequent operations that require a
 * numeric value will cast the stored string automatically.
 *
 * Example:
 * ```php
 * use Phuture\Coherence\Type\Numbers;
 *
 * $result = Numbers::from(10)
 *     ->add(5)
 *     ->multiply(2)
 *     ->subtract(3)
 *     ->round(0)
 *     ->get();
 * // 27.0
 * ```
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Numbers extends FluentClass implements Numberable
{
    /**
     * Abbreviates the wrapped number using suffix letters (K, M, B, T).
     *
     * @param int $precision The number of decimal places to keep (default: 1)
     * @return string The abbreviated number string
     * @see Transformer::abbreviate()
     */
    public function abbreviate(int $precision = 1): string
    {
        return Transformer::abbreviate($this->numericValue(), $precision);
    }
    /**
     * Returns the absolute (non-negative) value of the wrapped number.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::absolute()
     */
    public function absolute(): self
    {
        $this->data = Transformer::absolute($this->numericValue());

        return $this;
    }

    /**
     * Adds a number to the wrapped value using BCMath for precision.
     *
     * @param int|float $b The addend
     * @return self Returns the current instance for method chaining
     * @see Transformer::add()
     */
    public function add(int|float $b): self
    {
        $this->data = Transformer::add($this->numericValue(), $b);

        return $this;
    }

    /**
     * Determines whether the wrapped number is equal to another within epsilon tolerance.
     *
     * @param int|float $b The value to compare against
     * @return bool True when both values are equal within epsilon tolerance
     * @see Transformer::areEqual()
     */
    public function areEqual(int|float $b): bool
    {
        return Transformer::areEqual($this->numericValue(), $b);
    }

    /**
     * Returns the smallest integer value greater than or equal to the wrapped number.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::ceil()
     */
    public function ceil(): self
    {
        $this->data = Transformer::ceil($this->numericValue());

        return $this;
    }

    /**
     * Restricts the wrapped number to be within the given minimum and maximum bounds.
     *
     * @param int|float $min The lower bound
     * @param int|float $max The upper bound
     * @return self Returns the current instance for method chaining
     * @see Transformer::clamp()
     */
    public function clamp(int|float $min, int|float $max): self
    {
        $this->data = Transformer::clamp($this->numericValue(), $min, $max);

        return $this;
    }

    /**
     * Compares the wrapped number with another and returns their relative order.
     *
     * @param int|float $b The value to compare against
     * @return int -1 when wrapped < $b, 0 when equal, 1 when wrapped > $b
     * @see Transformer::compare()
     */
    public function compare(int|float $b): int
    {
        return Transformer::compare($this->numericValue(), $b);
    }

    /**
     * Divides the wrapped number by another using BCMath for precision.
     *
     * @param int|float $b The divisor (must not be zero)
     * @return self Returns the current instance for method chaining
     * @see Transformer::divide()
     */
    public function divide(int|float $b): self
    {
        $this->data = Transformer::divide($this->numericValue(), $b);

        return $this;
    }

    /**
     * Converts the wrapped byte count into a human-readable file size string.
     *
     * @param int $precision The number of decimal places to show (default: 0)
     * @param int $base The base for unit conversion: 1024 or 1000 (default: 1024)
     * @return string The human-readable file size string
     * @see Transformer::fileSize()
     */
    public function fileSize(int $precision = 0, int $base = 1024): string
    {
        return Transformer::fileSize($this->numericValue(), $precision, $base);
    }

    /**
     * Returns the largest integer value less than or equal to the wrapped number.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::floor()
     */
    public function floor(): self
    {
        $this->data = Transformer::floor($this->numericValue());

        return $this;
    }

    /**
     * Converts the wrapped number into a human-readable string with unit names.
     *
     * @param int $precision The number of decimal places to keep (default: 1)
     * @return string The human-readable number string
     * @see Transformer::forHumans()
     */
    public function forHumans(int $precision = 1): string
    {
        return Transformer::forHumans($this->numericValue(), $precision);
    }

    /**
     * Formats the wrapped number with grouped thousands and a specified precision.
     *
     * @param int|null $precision The number of decimal places (default: null — preserve original)
     * @return string The formatted number string
     * @see Transformer::format()
     */
    public function format(?int $precision = null): string
    {
        return Transformer::format($this->numericValue(), $precision);
    }

    /**
     * Determines whether the wrapped number is a float with a fractional part.
     *
     * @return bool True when the wrapped value has a fractional part
     * @see Transformer::isFloat()
     */
    public function isFloat(): bool
    {
        return Transformer::isFloat($this->numericValue());
    }

    /**
     * Determines whether the wrapped number is greater than another within epsilon tolerance.
     *
     * @param int|float $b The value to compare against
     * @return bool True when the wrapped value is strictly greater than $b
     * @see Transformer::isGreaterThan()
     */
    public function isGreaterThan(int|float $b): bool
    {
        return Transformer::isGreaterThan($this->numericValue(), $b);
    }

    /**
     * Determines whether the wrapped number is greater than or equal to another.
     *
     * @param int|float $b The value to compare against
     * @return bool True when the wrapped value is greater than or equal to $b
     * @see Transformer::isGreaterThanOrEqualTo()
     */
    public function isGreaterThanOrEqualTo(int|float $b): bool
    {
        return Transformer::isGreaterThanOrEqualTo($this->numericValue(), $b);
    }

    /**
     * Determines whether the wrapped number is an integer (has no fractional part).
     *
     * @return bool True when the wrapped value has no fractional part
     * @see Transformer::isInteger()
     */
    public function isInteger(): bool
    {
        return Transformer::isInteger($this->numericValue());
    }

    /**
     * Determines whether the wrapped number is less than another within epsilon tolerance.
     *
     * @param int|float $b The value to compare against
     * @return bool True when the wrapped value is strictly less than $b
     * @see Transformer::isLessThan()
     */
    public function isLessThan(int|float $b): bool
    {
        return Transformer::isLessThan($this->numericValue(), $b);
    }

    /**
     * Determines whether the wrapped number is less than or equal to another.
     *
     * @param int|float $b The value to compare against
     * @return bool True when the wrapped value is less than or equal to $b
     * @see Transformer::isLessThanOrEqualTo()
     */
    public function isLessThanOrEqualTo(int|float $b): bool
    {
        return Transformer::isLessThanOrEqualTo($this->numericValue(), $b);
    }

    /**
     * Determines whether the wrapped number is negative (strictly less than zero).
     *
     * @return bool True when the wrapped value is strictly less than zero
     * @see Transformer::isNegative()
     */
    public function isNegative(): bool
    {
        return Transformer::isNegative($this->numericValue());
    }

    /**
     * Determines whether the wrapped value is a valid numeric representation.
     *
     * @return bool True when the wrapped value is numeric
     * @see Transformer::isNumber()
     */
    public function isNumber(): bool
    {
        return Transformer::isNumber($this->data);
    }

    /**
     * Determines whether the wrapped number is positive (strictly greater than zero).
     *
     * @return bool True when the wrapped value is strictly greater than zero
     * @see Transformer::isPositive()
     */
    public function isPositive(): bool
    {
        return Transformer::isPositive($this->numericValue());
    }

    /**
     * Determines whether the wrapped number is equal to zero within epsilon tolerance.
     *
     * @return bool True when the wrapped value is zero within epsilon tolerance
     * @see Transformer::isZero()
     */
    public function isZero(): bool
    {
        return Transformer::isZero($this->numericValue());
    }

    /**
     * Returns the higher of the wrapped number and another.
     *
     * @param int|float $b The number to compare against
     * @return self Returns the current instance for method chaining
     * @see Transformer::max()
     */
    public function max(int|float $b): self
    {
        $this->data = Transformer::max($this->numericValue(), $b);

        return $this;
    }

    /**
     * Returns the lower of the wrapped number and another.
     *
     * @param int|float $b The number to compare against
     * @return self Returns the current instance for method chaining
     * @see Transformer::min()
     */
    public function min(int|float $b): self
    {
        $this->data = Transformer::min($this->numericValue(), $b);

        return $this;
    }

    /**
     * Computes the modulus of the wrapped number divided by another using BCMath.
     *
     * @param int|float $b The divisor (must not be zero)
     * @return self Returns the current instance for method chaining
     * @see Transformer::modulus()
     */
    public function modulus(int|float $b): self
    {
        $this->data = Transformer::modulus($this->numericValue(), $b);

        return $this;
    }

    /**
     * Multiplies the wrapped number by another using BCMath for precision.
     *
     * @param int|float $b The multiplier
     * @return self Returns the current instance for method chaining
     * @see Transformer::multiply()
     */
    public function multiply(int|float $b): self
    {
        $this->data = Transformer::multiply($this->numericValue(), $b);

        return $this;
    }

    /**
     * Returns the arithmetic opposite (negation) of the wrapped number.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::opposite()
     */
    public function opposite(): self
    {
        $this->data = Transformer::opposite($this->numericValue());

        return $this;
    }

    /**
     * Converts the wrapped integer to its ordinal string representation.
     *
     * @return string The ordinal string with the appropriate suffix
     * @see Transformer::ordinal()
     */
    public function ordinal(): string
    {
        return Transformer::ordinal((int) $this->numericValue());
    }

    /**
     * Converts the wrapped number into a human-readable percentage string.
     *
     * @param int $precision The number of decimal places (default: 1)
     * @param int $multiplicand The value to multiply by before formatting (default: 100)
     * @return string The formatted percentage string
     * @see Transformer::percentage()
     */
    public function percentage(int $precision = 1, int $multiplicand = 100): string
    {
        return Transformer::percentage($this->numericValue(), $precision, $multiplicand);
    }

    /**
     * Rounds the wrapped number to the specified precision using the given rounding mode.
     *
     * @param int $precision The number of decimal places (default: 0)
     * @param RoundingMode $mode The rounding mode (default: RoundingMode::HalfUp)
     * @return self Returns the current instance for method chaining
     * @see Transformer::round()
     */
    public function round(int $precision = 0, RoundingMode $mode = RoundingMode::HalfUp): self
    {
        $this->data = Transformer::round($this->numericValue(), $precision, $mode);

        return $this;
    }

    /**
     * Spells out the wrapped number in English words.
     *
     * @return string The English word representation of the wrapped number
     * @see Transformer::spell()
     */
    public function spell(): string
    {
        return Transformer::spell($this->numericValue());
    }

    /**
     * Computes the square root of the wrapped number using BCMath for precision.
     *
     * @param int $scale The number of decimal places in the result (default: 10)
     * @return self Returns the current instance for method chaining
     * @see Transformer::squareRoot()
     */
    public function squareRoot(int $scale = 10): self
    {
        $this->data = Transformer::squareRoot($this->numericValue(), $scale);

        return $this;
    }

    /**
     * Subtracts a number from the wrapped value using BCMath for precision.
     *
     * @param int|float $b The subtrahend
     * @return self Returns the current instance for method chaining
     * @see Transformer::subtract()
     */
    public function subtract(int|float $b): self
    {
        $this->data = Transformer::subtract($this->numericValue(), $b);

        return $this;
    }

    /**
     * Converts the wrapped value to a float.
     *
     * @return float The float representation of the wrapped value
     */
    public function toFloat(): float
    {
        return (float) $this->data;
    }

    /**
     * Converts the wrapped value to an integer.
     *
     * @return int The integer representation of the wrapped value
     */
    public function toInt(): int
    {
        return (int) $this->data;
    }

    /**
     * Converts the wrapped value to either an integer or float.
     *
     * Returns the most appropriate numeric representation: if the stored data
     * is a BCMath string, it is cast to float; otherwise the native type is returned.
     *
     * @return int|float The numeric representation of the wrapped value
     */
    public function toNumber(): int|float
    {
        if (is_string($this->data)) {
            return (float) $this->data;
        }

        return $this->data;
    }

    /**
     * Removes trailing zeros from the wrapped numeric string representation.
     *
     * @return string The trimmed number string
     * @see Transformer::trimTrailingZeros()
     */
    public function trimTrailingZeros(): string
    {
        return Transformer::trimTrailingZeros($this->data);
    }

    /**
     * Returns the stored value as an int|float for use with Transformer methods.
     *
     * When the stored data is a BCMath result string, it is cast to float.
     * Otherwise the original int or float is preserved.
     *
     * @return int|float The numeric value of the stored data
     */
    private function numericValue(): int|float
    {
        if (is_string($this->data)) {
            return (float) $this->data;
        }

        return $this->data;
    }
}
