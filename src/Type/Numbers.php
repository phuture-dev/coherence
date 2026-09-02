<?php

declare(strict_types=1);

namespace Phuture\Coherence\Type;

use Override;
use RoundingMode;
use Phuture\Coherence\Enum\{ByteBase, Unit};
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
     * @param int|float $value The value to add
     * @return self Returns the current instance for method chaining
     * @see Transformer::add()
     */
    public function add(int|float $value): self
    {
        $this->data = Transformer::add($this->toString(), $value);

        return $this;
    }

    /**
     * Adds a percentage of the wrapped value to itself using BCMath for precision.
     *
     * @param int|float $percentage The percentage to add (e.g. 20 for 20%)
     * @return self Returns the current instance for method chaining
     * @see Transformer::addPercentage()
     */
    public function addPercentage(int|float $percentage): self
    {
        $this->data = Transformer::addPercentage($this->toString(), $percentage);

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
     * Converts the wrapped Celsius temperature to Fahrenheit using BCMath for precision.
     *
     * The conversion result replaces the wrapped value, enabling further
     * chaining with arithmetic or formatting methods.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::celsiusToFahrenheit()
     */
    public function celsiusToFahrenheit(): self
    {
        $this->data = Transformer::celsiusToFahrenheit($this->toString());

        return $this;
    }

    /**
     * Converts the wrapped Celsius temperature to Rankine using BCMath for precision.
     *
     * The conversion result replaces the wrapped value, enabling further
     * chaining with arithmetic or formatting methods.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::celsiusToRankine()
     */
    public function celsiusToRankine(): self
    {
        $this->data = Transformer::celsiusToRankine($this->toString());

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
     * @param int|float $value The value to compare against
     * @return int -1 when wrapped < $value, 0 when equal, 1 when wrapped > $value
     * @see Transformer::compare()
     */
    public function compare(int|float $value): int
    {
        return Transformer::compare($this->toString(), $value);
    }

    /**
     * Converts the wrapped value from one unit of measurement to another.
     *
     * The conversion result replaces the wrapped value, enabling further
     * chaining with arithmetic or formatting methods.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Enum\Unit;
     * use Phuture\Coherence\Type\Numbers;
     *
     * $fahrenheit = Numbers::from(100)
     *     ->convert(Unit::Celsius, Unit::Fahrenheit)
     *     ->get();
     * // '212.0000000000'
     *
     * $miles = Numbers::from(5)
     *     ->convert(Unit::Kilometer, Unit::Mile)
     *     ->round(2)
     *     ->get();
     * // '3.1100000000'
     * ```
     *
     * @param Unit $from The source unit to convert from
     * @param Unit $to The target unit to convert to
     * @return self Returns the current instance for method chaining
     * @see Transformer::convert() For the static conversion method
     * @see \Phuture\Coherence\Enum\Unit For all available unit cases
     */
    public function convert(Unit $from, Unit $to): self
    {
        $this->data = Transformer::convert($this->toString(), $from, $to);

        return $this;
    }

    /**
     * Divides the wrapped number by another using BCMath for precision.
     *
     * @param int|float $value The divisor (must not be zero)
     * @return self Returns the current instance for method chaining
     * @see Transformer::divide()
     */
    public function divide(int|float $value): self
    {
        $this->data = Transformer::divide($this->toString(), $value);

        return $this;
    }

    /**
     * Converts the wrapped Fahrenheit temperature to Celsius using BCMath for precision.
     *
     * The conversion result replaces the wrapped value, enabling further
     * chaining with arithmetic or formatting methods.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::fahrenheitToCelsius()
     */
    public function fahrenheitToCelsius(): self
    {
        $this->data = Transformer::fahrenheitToCelsius($this->toString());

        return $this;
    }

    /**
     * Converts the wrapped byte count into a human-readable file size string.
     *
     * @param int $precision The number of decimal places to show (default: 0)
     * @param \Phuture\Coherence\Enum\ByteBase $base The base for unit conversion — Binary (1024)
     *  or Decimal (1000) (default: ByteBase::Binary)
     * @return string The human-readable file size string
     * @see Transformer::fileSize()
     * @see \Phuture\Coherence\Enum\ByteBase
     */
    public function fileSize(int $precision = 0, ByteBase $base = ByteBase::Binary): string
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
     * Converts the wrapped number into a human-readable percentage string.
     *
     * @param int $precision The number of decimal places (default: 1)
     * @param int $multiplicand The value to multiply by before formatting (default: 100)
     * @return string The formatted percentage string
     * @see Transformer::formatPercentage()
     */
    public function formatPercentage(int $precision = 1, int $multiplicand = 100): string
    {
        return Transformer::formatPercentage($this->toString(), $precision, $multiplicand);
    }

    /**
     * Returns the higher of the wrapped number and another.
     *
     * @param int|float $value The value to compare against
     * @return self Returns the current instance for method chaining
     * @see Transformer::max()
     */
    public function max(int|float $value): self
    {
        $this->data = Transformer::max($this->toString(), $value);

        return $this;
    }

    /**
     * Returns the lower of the wrapped number and another.
     *
     * @param int|float $value The value to compare against
     * @return self Returns the current instance for method chaining
     * @see Transformer::min()
     */
    public function min(int|float $value): self
    {
        $this->data = Transformer::min($this->toString(), $value);

        return $this;
    }

    /**
     * Computes the modulus of the wrapped number divided by another using BCMath.
     *
     * @param int|float $value The divisor (must not be zero)
     * @return self Returns the current instance for method chaining
     * @see Transformer::modulus()
     */
    public function modulus(int|float $value): self
    {
        $this->data = Transformer::modulus($this->toString(), $value);

        return $this;
    }

    /**
     * Multiplies the wrapped number by another using BCMath for precision.
     *
     * @param int|float $value The value to multiply by
     * @return self Returns the current instance for method chaining
     * @see Transformer::multiply()
     */
    public function multiply(int|float $value): self
    {
        $this->data = Transformer::multiply($this->toString(), $value);

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
     * Calculates the given percentage of the wrapped value using BCMath for precision.
     *
     * Replaces the wrapped value with the computed percentage amount.
     *
     * @param int|float $percentage The percentage to calculate (e.g. 20 for 20%)
     * @return self Returns the current instance for method chaining
     * @see Transformer::percentage()
     */
    public function percentage(int|float $percentage): self
    {
        $this->data = Transformer::percentage($this->toString(), $percentage);

        return $this;
    }

    /**
     * Converts the wrapped Rankine temperature to Celsius using BCMath for precision.
     *
     * The conversion result replaces the wrapped value, enabling further
     * chaining with arithmetic or formatting methods.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::rankineToCelsius()
     */
    public function rankineToCelsius(): self
    {
        $this->data = Transformer::rankineToCelsius($this->toString());

        return $this;
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
     * @param int|float $value The value to subtract
     * @return self Returns the current instance for method chaining
     * @see Transformer::subtract()
     */
    public function subtract(int|float $value): self
    {
        $this->data = Transformer::subtract($this->toString(), $value);

        return $this;
    }

    /**
     * Subtracts a percentage of the wrapped value from itself using BCMath for precision.
     *
     * @param int|float $percentage The percentage to subtract (e.g. 20 for 20%)
     * @return self Returns the current instance for method chaining
     * @see Transformer::subtractPercentage()
     */
    public function subtractPercentage(int|float $percentage): self
    {
        $this->data = Transformer::subtractPercentage($this->toString(), $percentage);

        return $this;
    }

    /**
     * Converts the wrapped value to a float.
     *
     * @return float The float representation of the wrapped value
     */
    #[Override]
    public function toFloat(): float
    {
        if (is_float($this->data)) {
            return $this->data;
        }

        // @phpstan-ignore-next-line
        return (float) $this->data;
    }

    /**
     * Converts the wrapped value to an integer.
     *
     * @return int The integer representation of the wrapped value
     */
    #[Override]
    public function toInt(): int
    {
        if (is_int($this->data)) {
            return $this->data;
        }

        // @phpstan-ignore-next-line
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
    #[Override]
    public function toString(): string
    {
        if (is_string($this->data)) {
            return $this->data;
        }

        // @phpstan-ignore-next-line
        return (string) $this->data;
    }
}
