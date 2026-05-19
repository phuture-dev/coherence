<?php

declare(strict_types=1);

namespace Phuture\Coherence\Type;

use RoundingMode;
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
        return Transformer::abbreviate($this->toString(), $precision);
    }

    /**
     * Returns the absolute (non-negative) value of the wrapped number.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::absolute()
     */
    public function absolute(): self
    {
        $this->data = Transformer::absolute($this->toString());

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
        $this->data = Transformer::add($this->toString(), $b);

        return $this;
    }

    /**
     * Returns the smallest integer value greater than or equal to the wrapped number.
     *
     * @return self Returns the current instance for method chaining (stores result as a BCMath string)
     * @see Transformer::ceil()
     */
    public function ceil(): self
    {
        $this->data = Transformer::ceil($this->toString());

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
        $this->data = Transformer::clamp($this->toString(), $min, $max);

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
        return Transformer::compare($this->toString(), $b);
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
        $this->data = Transformer::divide($this->toString(), $b);

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
        return Transformer::fileSize($this->toString(), $precision, $base);
    }

    /**
     * Returns the largest integer value less than or equal to the wrapped number.
     *
     * @return self Returns the current instance for method chaining (stores result as a BCMath string)
     * @see Transformer::floor()
     */
    public function floor(): self
    {
        $this->data = Transformer::floor($this->toString());

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
        return Transformer::forHumans($this->toString(), $precision);
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
        return Transformer::format($this->toString(), $precision);
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
        $this->data = Transformer::max($this->toString(), $b);

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
        $this->data = Transformer::min($this->toString(), $b);

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
        $this->data = Transformer::modulus($this->toString(), $b);

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
        $this->data = Transformer::multiply($this->toString(), $b);

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
        $this->data = Transformer::opposite($this->toString());

        return $this;
    }

    /**
     * Computes the arithmetic mean (average) of a list of numbers.
     *
     * @param array $values The list of numbers to average
     * @return string The arithmetic mean as a BCMath string
     * @throws \InvalidArgumentException When the values array is empty
     * @see Transformer::mean()
     */
    public function mean(array $values): string
    {
        return Transformer::mean($values);
    }

    /**
     * Computes the median (middle value) of a list of numbers.
     *
     * @param array $values The list of numbers to find the median of
     * @return string The median as a BCMath string
     * @throws \InvalidArgumentException When the values array is empty
     * @see Transformer::median()
     */
    public function median(array $values): string
    {
        return Transformer::median($values);
    }

    /**
     * Finds the mode (most frequently occurring value) of a list of numbers.
     *
     * When multiple values share the highest frequency, all of them are returned.
     *
     * @param array $values The list of numbers to find the mode of
     * @return array An array containing the most frequently occurring value(s)
     * @throws \InvalidArgumentException When the values array is empty
     * @see Transformer::mode()
     */
    public function mode(array $values): array
    {
        return Transformer::mode($values);
    }

    /**
     * Computes the population variance of a list of numbers.
     *
     * @param array $values The list of numbers to compute variance for
     * @return string The population variance as a BCMath string
     * @throws \InvalidArgumentException When the values array is empty
     * @see Transformer::variance()
     */
    public function variance(array $values): string
    {
        return Transformer::variance($values);
    }

    /**
     * Computes the sample variance of a list of numbers using Bessel's correction.
     *
     * @param array $values The list of numbers to compute sample variance for
     * @return string The sample variance as a BCMath string
     * @throws \InvalidArgumentException When the values array has fewer than 2 elements
     * @see Transformer::sampleVariance()
     */
    public function sampleVariance(array $values): string
    {
        return Transformer::sampleVariance($values);
    }

    /**
     * Computes the population standard deviation of a list of numbers.
     *
     * @param array $values The list of numbers to compute standard deviation for
     * @return string The population standard deviation as a BCMath string
     * @throws \InvalidArgumentException When the values array is empty
     * @see Transformer::standardDeviation()
     */
    public function standardDeviation(array $values): string
    {
        return Transformer::standardDeviation($values);
    }

    /**
     * Computes the sample standard deviation of a list of numbers.
     *
     * @param array $values The list of numbers to compute sample standard deviation for
     * @return string The sample standard deviation as a BCMath string
     * @throws \InvalidArgumentException When the values array has fewer than 2 elements
     * @see Transformer::sampleStandardDeviation()
     */
    public function sampleStandardDeviation(array $values): string
    {
        return Transformer::sampleStandardDeviation($values);
    }

    /**
     * Computes a specific percentile of a list of numbers using linear interpolation.
     *
     * @param array $values The list of numbers to compute the percentile for
     * @param int|float $percentile The percentile to compute, from 0 to 100
     * @return string The value at the given percentile as a BCMath string
     * @throws \InvalidArgumentException When the values array is empty or percentile is out of range
     * @see Transformer::percentile()
     */
    public function percentile(array $values, int|float $percentile): string
    {
        return Transformer::percentile($values, $percentile);
    }

    /**
     * Computes the range (difference between maximum and minimum) of a list of numbers.
     *
     * @param array $values The list of numbers to compute the range for
     * @return string The range (max minus min) as a BCMath string
     * @throws \InvalidArgumentException When the values array is empty
     * @see Transformer::range()
     */
    public function range(array $values): string
    {
        return Transformer::range($values);
    }

    /**
     * Converts the wrapped integer to its ordinal string representation.
     *
     * @return string The ordinal string with the appropriate suffix
     * @see Transformer::ordinal()
     */
    public function ordinal(): string
    {
        return Transformer::ordinal((int) $this->toString());
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
        return Transformer::percentage($this->toString(), $precision, $multiplicand);
    }

    /**
     * Rounds the wrapped number to the specified precision using the given rounding mode.
     *
     * @param int $precision The number of decimal places (default: 0)
     * @param RoundingMode $mode The rounding mode (default: \RoundingMode::HalfAwayFromZero)
     * @return self Returns the current instance for method chaining (stores result as a BCMath string)
     * @see Transformer::round()
     */
    public function round(
        int $precision = 0,
        // @phpstan-ignore-next-line
        RoundingMode $mode = RoundingMode::HalfAwayFromZero
    ): self {
        $this->data = Transformer::round($this->toString(), $precision, $mode);

        return $this;
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
        $this->data = Transformer::squareRoot($this->toString(), $scale);

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
        $this->data = Transformer::subtract($this->toString(), $b);

        return $this;
    }

    /**
     * Converts the wrapped value to a float.
     *
     * @return float The float representation of the wrapped value
     */
    public function toFloat(): float
    {
        if (is_float($this->data)) {
            return $this->data;
        }

        return (float) $this->data;
    }

    /**
     * Converts the wrapped value to an integer.
     *
     * @return int The integer representation of the wrapped value
     */
    public function toInt(): int
    {
        if (is_integer($this->data)) {
            return $this->data;
        }

        return (int) $this->data;
    }

    /**
     * Normalizes the wrapped value to its most appropriate numeric type.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::toNumber()
     */
    public function toNumber(): self
    {
        $this->data = Transformer::toNumber($this->toString());

        return $this;
    }

    /**
     * Converts the wrapped value to a string.
     *
     * @return string The string representation of the wrapped value
     */
    public function toString(): string
    {
        if (is_string($this->data)) {
            return $this->data;
        }

        return (string) $this->data;
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
}
