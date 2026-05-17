<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use RoundingMode;
use Phuture\Coherence\Support\StaticClass;
use Phuture\Coherence\Exception\{InvalidArgumentException, LogicException};

/**
 * Comprehensive number manipulation utility class with precise arithmetic and formatting.
 *
 * This utility class provides a complete toolkit for working with numbers, combining
 * float comparison with epsilon tolerance, precise arithmetic via BCMath, number
 * formatting, and human-readable output into a single cohesive interface.
 *
 * Key features:
 *
 * - **BCMath Comparison**: Compare numbers with BCMath precision to avoid floating-point errors
 * - **Precise Arithmetic**: Add, subtract, multiply, divide, and compute modulus using BCMath strings
 * - **State & Validation**: Check if a number is zero, positive, negative, or an integer
 * - **Clamping & Limits**: Constrain numbers to a minimum, maximum, or both
 * - **Formatting**: Abbreviate numbers, format file sizes, percentages, ordinals, and more
 * - **Human-Readable Output**: Convert numbers into readable strings like "1.5K" or "2.5 MB"
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Numbers extends StaticClass
{
    /**
     * Number of decimal places used by default in BCMath arithmetic operations.
     */
    private const DEFAULT_SCALE = 10;

    /**
     * Abbreviates a number using suffix letters (K, M, B, T).
     *
     * Converts large numbers into shorter human-readable strings by dividing
     * the value and appending a suffix. For example, 1500 becomes "1.5K".
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::abbreviate(1500); // '1.5K'
     * Numbers::abbreviate(1000000); // '1.0M'
     * Numbers::abbreviate(123456789); // '123.5M'
     * Numbers::abbreviate(1500, 2); // '1.50K'
     * ```
     *
     * @param int|float|string $number The number to abbreviate
     * @param int $precision The number of decimal places to keep (default: 1)
     * @return string The abbreviated number string
     * @see \Phuture\Coherence\Numbers::forHumans()
     */
    public static function abbreviate(int|float|string $number, int $precision = 1): string
    {
        $suffixes = ['', 'K', 'M', 'B', 'T'];
        $number = (float) $number;
        $absolute = abs($number);
        $sign = $number < 0 ? '-' : '';

        if ($absolute < 1000) {
            return $sign . number_format($number, $precision);
        }

        $exp = (int) floor(log($absolute, 1000));
        $exp = min($exp, count($suffixes) - 1);
        $divisor = pow(1000, $exp);
        $abbreviated = round($absolute / $divisor, $precision);

        return $sign . number_format($abbreviated, $precision) . $suffixes[$exp];
    }

    /**
     * Returns the absolute (non-negative) value of a number.
     *
     * Converts negative numbers to their positive equivalent. Positive numbers
     * and zero are returned unchanged.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::absolute(-5); // 5
     * Numbers::absolute(3.14); // 3.14
     * Numbers::absolute(0); // 0
     * ```
     *
     * @param int|float|string $number The number to convert
     * @return int|float|string The non-negative value of the number
     * @see \Phuture\Coherence\Numbers::opposite()
     */
    public static function absolute(int|float|string $number): int|float|string
    {
        if (is_string($number)) {
            $result = abs((float) $number);

            return floor($result) === $result ? (int) $result : $result;
        }

        return abs($number);
    }

    /**
     * Adds two numbers using BCMath for precision and returns the result as a string.
     *
     * Both values are converted to strings and added using BCMath to avoid
     * floating-point precision loss.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::add(0.1, 0.2); // '0.3000000000'
     * Numbers::add(100, 200); // '300.0000000000'
     * Numbers::add(1.5, 2.5); // '4.0000000000'
     * ```
     *
     * @param int|float|string $a The first addend
     * @param int|float|string $b The second addend
     * @return string The sum as a string
     * @see \Phuture\Coherence\Numbers::subtract()
     */
    public static function add(int|float|string $a, int|float|string $b): string
    {
        return bcadd((string) $a, (string) $b, self::DEFAULT_SCALE);
    }

    /**
     * Determines whether two numbers are equal at BCMath precision.
     *
     * Compares two numbers using BCMath at the default scale.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::areEqual(0.1 + 0.2, 0.3); // true
     * Numbers::areEqual(10, 10.0); // true
     * Numbers::areEqual(1.0, 2.0); // false
     * ```
     *
     * @param int|float|string $a The first value to compare
     * @param int|float|string $b The second value to compare
     * @return bool True when both values are equal at BCMath precision
     * @throws \Phuture\Coherence\Exception\LogicException When either value is NAN
     * @see \Phuture\Coherence\Numbers::compare()
     * @see \Phuture\Coherence\Numbers::isZero()
     */
    public static function areEqual(int|float|string $a, int|float|string $b): bool
    {
        self::assertNotNan($a, 'a');
        self::assertNotNan($b, 'b');

        return bccomp((string) $a, (string) $b, self::DEFAULT_SCALE) === 0;
    }

    /**
     * Returns the smallest integer value greater than or equal to the given number.
     *
     * Rounds up to the nearest integer using BCMath for precision. For example,
     * 3.2 becomes "4" and -3.2 becomes "-3".
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::ceil(3.2); // '4'
     * Numbers::ceil(-1.1); // '-1'
     * Numbers::ceil(5.0); // '5'
     * ```
     *
     * @param int|float|string $number The number to round up
     * @return string The smallest integer greater than or equal to the number as a BCMath string
     * @see \Phuture\Coherence\Numbers::floor()
     * @see \Phuture\Coherence\Numbers::round()
     */
    public static function ceil(int|float|string $number): string
    {
        return bcceil((string) $number);
    }

    /**
     * Restricts a number to be within the given minimum and maximum bounds.
     *
     * When the number is below `$min`, `$min` is returned. When the number is
     * above `$max`, `$max` is returned. Otherwise, the number itself is returned.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::clamp(5, 1, 10); // 5
     * Numbers::clamp(-3, 0, 100); // 0
     * Numbers::clamp(150, 0, 100); // 100
     * ```
     *
     * @param int|float|string $number The number to restrict
     * @param int|float|string $min The lower bound
     * @param int|float|string $max The upper bound
     * @return int|float|string The clamped value
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When min is greater than max
     * @see \Phuture\Coherence\Numbers::max()
     * @see \Phuture\Coherence\Numbers::min()
     */
    public static function clamp(
        int|float|string $number,
        int|float|string $min,
        int|float|string $max
    ): int|float|string {
        if (bccomp((string) $min, (string) $max, self::DEFAULT_SCALE) > 0) {
            throw new InvalidArgumentException(
                'Invalid Argument: The minimum value cannot be greater than the maximum value'
            );
        }

        if (bccomp((string) $number, (string) $min, self::DEFAULT_SCALE) < 0) {
            return $min;
        }

        if (bccomp((string) $number, (string) $max, self::DEFAULT_SCALE) > 0) {
            return $max;
        }

        return $number;
    }

    /**
     * Compares two numbers and returns their relative order.
     *
     * Returns -1 when `$a` is less than `$b`, 0 when they are equal at BCMath precision, and 1
     * when `$a` is greater than `$b`. Suitable for use with sorting functions like `usort()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::compare(1.0, 2.0); // -1
     * Numbers::compare(2.0, 1.0); // 1
     * Numbers::compare(1.0, 1.0); // 0
     *
     * $arr = [3, 1, 2];
     * usort($arr, [Numbers::class, 'compare']); // [1, 2, 3]
     * ```
     *
     * @param int|float|string $a The first value to compare
     * @param int|float|string $b The second value to compare
     * @return int -1 when $a < $b, 0 when equal, 1 when $a > $b
     * @throws \Phuture\Coherence\Exception\LogicException When either value is NAN
     * @see \Phuture\Coherence\Numbers::areEqual()
     */
    public static function compare(int|float|string $a, int|float|string $b): int
    {
        self::assertNotNan($a, 'a');
        self::assertNotNan($b, 'b');

        return bccomp((string) $a, (string) $b, self::DEFAULT_SCALE);
    }

    /**
     * Divides the first number by the second using BCMath for precision.
     *
     * Both values are converted to strings and divided using BCMath to avoid
     * floating-point precision loss. Throws when dividing by zero.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::divide(10, 3); // '3.3333333333'
     * Numbers::divide(100, 4); // '25.0000000000'
     * Numbers::divide(1, 3); // '0.3333333333'
     * ```
     *
     * @param int|float|string $a The dividend
     * @param int|float|string $b The divisor (must not be zero)
     * @return string The quotient as a string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the divisor is zero
     * @see \Phuture\Coherence\Numbers::multiply()
     */
    public static function divide(int|float|string $a, int|float|string $b): string
    {
        if ((float) $b === 0.0) {
            throw new InvalidArgumentException(
                'Invalid Argument: Division by zero is not allowed'
            );
        }

        return bcdiv((string) $a, (string) $b, self::DEFAULT_SCALE);
    }

    /**
     * Converts a byte count into a human-readable file size string.
     *
     * Expresses the byte count using the largest appropriate unit (B, KB, MB, GB, TB, PB).
     * Uses base 1024 by default (binary prefixes). Use base 1000 for decimal prefixes.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::fileSize(500); // '500 B'
     * Numbers::fileSize(1024); // '1 KB'
     * Numbers::fileSize(1048576); // '1 MB'
     * Numbers::fileSize(1073741824); // '1 GB'
     * Numbers::fileSize(1500, 2); // '1.46 KB'
     * ```
     *
     * @param int|float|string $bytes The file size in bytes
     * @param int $precision The number of decimal places to show (default: 0)
     * @param int $base The base for unit conversion: 1024 or 1000 (default: 1024)
     * @return string The human-readable file size string
     * @see \Phuture\Coherence\Numbers::forHumans()
     */
    public static function fileSize(int|float|string $bytes, int $precision = 0, int $base = 1024): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $bytes = (float) $bytes;

        if ($bytes < (float) $base) {
            return round($bytes, $precision) . ' B';
        }

        $exp = (int) floor(log($bytes, $base));
        $exp = min($exp, count($units) - 1);

        $value = round($bytes / pow($base, $exp), $precision);

        return number_format($value, $precision) . ' ' . $units[$exp];
    }

    /**
     * Returns the largest integer value less than or equal to the given number.
     *
     * Rounds down to the nearest integer using BCMath for precision. For example,
     * 3.8 becomes "3" and -3.8 becomes "-4".
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::floor(3.8); // '3'
     * Numbers::floor(-1.1); // '-2'
     * Numbers::floor(5.0); // '5'
     * ```
     *
     * @param int|float|string $number The number to round down
     * @return string The largest integer less than or equal to the number as a BCMath string
     * @see \Phuture\Coherence\Numbers::ceil()
     * @see \Phuture\Coherence\Numbers::round()
     */
    public static function floor(int|float|string $number): string
    {
        return bcfloor((string) $number);
    }

    /**
     * Converts a number into a human-readable string with unit suffixes.
     *
     * Similar to `abbreviate()` but uses full unit names instead of suffix
     * letters. For example, 1500 becomes "1.5 thousand".
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::forHumans(1500); // '1.5 thousand'
     * Numbers::forHumans(1000000); // '1.0 million'
     * Numbers::forHumans(1234, 2); // '1.23 thousand'
     * ```
     *
     * @param int|float|string $number The number to format
     * @param int $precision The number of decimal places to keep (default: 1)
     * @return string The human-readable number string
     * @see \Phuture\Coherence\Numbers::abbreviate()
     */
    public static function forHumans(int|float|string $number, int $precision = 1): string
    {
        $units = ['', 'thousand', 'million', 'billion', 'trillion'];
        $number = (float) $number;
        $absolute = abs($number);
        $sign = $number < 0 ? '-' : '';

        if ($absolute < 1000) {
            return $sign . number_format($number, $precision);
        }

        $exp = (int) floor(log($absolute, 1000));
        $exp = min($exp, count($units) - 1);
        $divisor = pow(1000, $exp);
        $formatted = round($absolute / $divisor, $precision);
        $unit = $units[$exp];

        return $sign . number_format($formatted, $precision) . ($unit !== '' ? ' ' . $unit : '');
    }

    /**
     * Formats a number with grouped thousands and a specified number of decimal places.
     *
     * Wraps PHP's `number_format()` to produce locale-independent formatted strings.
     * When `$precision` is null, the original precision of the number is preserved.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::format(1234567.8912, 2); // '1,234,567.89'
     * Numbers::format(1234567, 0); // '1,234,567'
     * Numbers::format(1234.5678, 4); // '1,234.5678'
     * ```
     *
     * @param int|float|string $number The number to format
     * @param int|null $precision The number of decimal places (default: null — preserve original)
     * @return string The formatted number string
     * @see \Phuture\Coherence\Numbers::percentage()
     * @see \Phuture\Coherence\Numbers::abbreviate()
     */
    public static function format(int|float|string $number, ?int $precision = null): string
    {
        if ($precision === null) {
            $precision = self::detectPrecision($number);
        }

        return number_format((float) $number, $precision, '.', ',');
    }

    /**
     * Determines whether a value is a floating-point number (has a fractional part).
     *
     * Returns true when the value is a finite float that is not a whole number.
     * Integer values, infinity, and NAN return false. This is the logical
     * inverse of `isInteger()` for finite numeric values.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::isFloat(3.14); // true
     * Numbers::isFloat(0.5); // true
     * Numbers::isFloat(5); // false
     * Numbers::isFloat(5.0); // false
     * Numbers::isFloat(INF); // false
     * ```
     *
     * @param int|float|string $value The value to check
     * @return bool True when the value is a float with a fractional part
     * @see \Phuture\Coherence\Numbers::isInteger()
     */
    public static function isFloat(int|float|string $value): bool
    {
        return is_finite((float) $value) && floor((float) $value) !== (float) $value;
    }

    /**
     * Determines whether a number is greater than another at BCMath precision.
     *
     * Returns true when `$a` is strictly greater than `$b`.
     *
     * Throws a `\Phuture\Coherence\Exception\LogicException` when either value is `NAN`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::isGreaterThan(10.0, 5.0); // true
     * Numbers::isGreaterThan(5.0, 10.0); // false
     * Numbers::isGreaterThan(10.0, 10.0); // false
     * ```
     *
     * @param int|float|string $a The value to test
     * @param int|float|string $b The value to compare against
     * @return bool True when $a is strictly greater than $b
     * @throws \Phuture\Coherence\Exception\LogicException When either value is NAN
     * @see \Phuture\Coherence\Numbers::isGreaterThanOrEqualTo()
     * @see \Phuture\Coherence\Numbers::isLessThan()
     */
    public static function isGreaterThan(int|float|string $a, int|float|string $b): bool
    {
        self::assertNotNan($a, 'a');
        self::assertNotNan($b, 'b');

        return self::compare($a, $b) === 1;
    }

    /**
     * Determines whether a number is greater than or equal to another at BCMath precision.
     *
     * Returns true when `$a` is greater than or equal to `$b`.
     *
     * Throws a `\Phuture\Coherence\Exception\LogicException` when either value is `NAN`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::isGreaterThanOrEqualTo(10.0, 5.0); // true
     * Numbers::isGreaterThanOrEqualTo(10.0, 10.0); // true
     * Numbers::isGreaterThanOrEqualTo(5.0, 10.0); // false
     * ```
     *
     * @param int|float|string $a The value to test
     * @param int|float|string $b The value to compare against
     * @return bool True when $a is greater than or equal to $b
     * @throws \Phuture\Coherence\Exception\LogicException When either value is NAN
     * @see \Phuture\Coherence\Numbers::isGreaterThan()
     * @see \Phuture\Coherence\Numbers::isLessThanOrEqualTo()
     */
    public static function isGreaterThanOrEqualTo(int|float|string $a, int|float|string $b): bool
    {
        self::assertNotNan($a, 'a');
        self::assertNotNan($b, 'b');

        return self::compare($a, $b) >= 0;
    }

    /**
     * Determines whether a number is an integer (has no fractional part).
     *
     * Returns true when the value is a whole number with no decimal component.
     * Returns false for infinity and NAN.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::isInteger(5); // true
     * Numbers::isInteger(5.0); // true
     * Numbers::isInteger(-3.0); // true
     * Numbers::isInteger(3.14); // false
     * Numbers::isInteger(INF); // false
     * ```
     *
     * @param int|float|string $value The value to check
     * @return bool True when the value has no fractional part
     * @see \Phuture\Coherence\Numbers::isZero()
     */
    public static function isInteger(int|float|string $value): bool
    {
        return is_finite((float) $value) && floor((float) $value) === (float) $value;
    }

    /**
     * Determines whether a number is less than another at BCMath precision.
     *
     * Returns true when `$a` is strictly less than `$b`.
     *
     * Throws a `\Phuture\Coherence\Exception\LogicException` when either value is `NAN`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::isLessThan(5.0, 10.0); // true
     * Numbers::isLessThan(10.0, 5.0); // false
     * Numbers::isLessThan(10.0, 10.0); // false
     * ```
     *
     * @param int|float|string $a The value to test
     * @param int|float|string $b The value to compare against
     * @return bool True when $a is strictly less than $b
     * @throws \Phuture\Coherence\Exception\LogicException When either value is NAN
     * @see \Phuture\Coherence\Numbers::isLessThanOrEqualTo()
     * @see \Phuture\Coherence\Numbers::isGreaterThan()
     */
    public static function isLessThan(int|float|string $a, int|float|string $b): bool
    {
        self::assertNotNan($a, 'a');
        self::assertNotNan($b, 'b');

        return self::compare($a, $b) === -1;
    }

    /**
     * Determines whether a number is less than or equal to another at BCMath precision.
     *
     * Returns true when `$a` is less than or equal to `$b`.
     *
     * Throws a `\Phuture\Coherence\Exception\LogicException` when either value is `NAN`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::isLessThanOrEqualTo(5.0, 10.0); // true
     * Numbers::isLessThanOrEqualTo(10.0, 10.0); // true
     * Numbers::isLessThanOrEqualTo(15.0, 10.0); // false
     * ```
     *
     * @param int|float|string $a The value to test
     * @param int|float|string $b The value to compare against
     * @return bool True when $a is less than or equal to $b
     * @throws \Phuture\Coherence\Exception\LogicException When either value is NAN
     * @see \Phuture\Coherence\Numbers::isLessThan()
     * @see \Phuture\Coherence\Numbers::isGreaterThanOrEqualTo()
     */
    public static function isLessThanOrEqualTo(int|float|string $a, int|float|string $b): bool
    {
        self::assertNotNan($a, 'a');
        self::assertNotNan($b, 'b');

        return self::compare($a, $b) <= 0;
    }

    /**
     * Determines whether a number is negative (strictly less than zero).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::isNegative(-5); // true
     * Numbers::isNegative(-0.1); // true
     * Numbers::isNegative(0); // false
     * Numbers::isNegative(3); // false
     * ```
     *
     * @param int|float|string $number The number to check
     * @return bool True when the number is strictly less than zero
     * @see \Phuture\Coherence\Numbers::isPositive()
     * @see \Phuture\Coherence\Numbers::isZero()
     */
    public static function isNegative(int|float|string $number): bool
    {
        return bccomp((string) $number, '0', self::DEFAULT_SCALE) < 0;
    }

    /**
     * Determines whether a value is a valid numeric representation.
     *
     * Accepts integers, floats, and numeric strings. Returns false for
     * non-numeric strings, NAN, infinity, arrays, objects, and null.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::isNumber(42); // true
     * Numbers::isNumber(3.14); // true
     * Numbers::isNumber('100'); // true
     * Numbers::isNumber('abc'); // false
     * Numbers::isNumber(null); // false
     * ```
     *
     * @param mixed $value The value to check
     * @return bool True when the value is a valid number or numeric string
     * @see \Phuture\Coherence\Numbers::parseInt()
     * @see \Phuture\Coherence\Numbers::parseFloat()
     */
    public static function isNumber(mixed $value): bool
    {
        return is_numeric($value);
    }

    /**
     * Determines whether a number is positive (strictly greater than zero).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::isPositive(5); // true
     * Numbers::isPositive(0.1); // true
     * Numbers::isPositive(0); // false
     * Numbers::isPositive(-3); // false
     * ```
     *
     * @param int|float|string $number The number to check
     * @return bool True when the number is strictly greater than zero
     * @see \Phuture\Coherence\Numbers::isNegative()
     * @see \Phuture\Coherence\Numbers::isZero()
     */
    public static function isPositive(int|float|string $number): bool
    {
        return bccomp((string) $number, '0', self::DEFAULT_SCALE) > 0;
    }

    /**
     * Determines whether a number is equal to zero at BCMath precision.
     *
     * Compares the value against zero using BCMath at the default scale.
     * Values smaller than the default scale are treated as zero.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::isZero(0); // true
     * Numbers::isZero(0.0); // true
     * Numbers::isZero(0.5); // false
     * Numbers::isZero('0.0000000000'); // true
     * ```
     *
     * @param int|float|string $number The number to check
     * @return bool True when the number is zero at BCMath precision
     * @see \Phuture\Coherence\Numbers::isPositive()
     * @see \Phuture\Coherence\Numbers::isNegative()
     */
    public static function isZero(int|float|string $number): bool
    {
        return bccomp((string) $number, '0', self::DEFAULT_SCALE) === 0;
    }

    /**
     * Returns the higher of two numbers.
     *
     * Compares two numbers and returns the one with the higher value.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::max(3, 7); // 7
     * Numbers::max(-5, -2); // -2
     * Numbers::max(3.14, 2.7); // 3.14
     * ```
     *
     * @param int|float|string $a The first number
     * @param int|float|string $b The second number
     * @return int|float|string The higher of the two numbers
     * @see \Phuture\Coherence\Numbers::min()
     * @see \Phuture\Coherence\Numbers::clamp()
     */
    public static function max(int|float|string $a, int|float|string $b): int|float|string
    {
        return bccomp((string) $a, (string) $b, self::DEFAULT_SCALE) >= 0 ? $a : $b;
    }

    /**
     * Returns the lower of two numbers.
     *
     * Compares two numbers and returns the one with the lower value.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::min(3, 7); // 3
     * Numbers::min(-5, -2); // -5
     * Numbers::min(3.14, 2.7); // 2.7
     * ```
     *
     * @param int|float|string $a The first number
     * @param int|float|string $b The second number
     * @return int|float|string The lower of the two numbers
     * @see \Phuture\Coherence\Numbers::max()
     * @see \Phuture\Coherence\Numbers::clamp()
     */
    public static function min(int|float|string $a, int|float|string $b): int|float|string
    {
        return bccomp((string) $a, (string) $b, self::DEFAULT_SCALE) <= 0 ? $a : $b;
    }

    /**
     * Computes the modulus (remainder) of dividing the first number by the second using BCMath.
     *
     * Returns the remainder of `$a` divided by `$b`. Throws when the divisor is zero.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::modulus(10, 3); // '1.0000000000'
     * Numbers::modulus(10, 2); // '0.0000000000'
     * Numbers::modulus(7.5, 2); // '1.5000000000'
     * ```
     *
     * @param int|float|string $a The dividend
     * @param int|float|string $b The divisor (must not be zero)
     * @return string The remainder as a string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the divisor is zero
     * @see \Phuture\Coherence\Numbers::divide()
     */
    public static function modulus(int|float|string $a, int|float|string $b): string
    {
        if ((float) $b === 0.0) {
            throw new InvalidArgumentException(
                'Invalid Argument: Modulus by zero is not allowed'
            );
        }

        return bcmod((string) $a, (string) $b, self::DEFAULT_SCALE);
    }

    /**
     * Multiplies two numbers using BCMath for precision and returns the result as a string.
     *
     * Both values are converted to strings and multiplied using BCMath to avoid
     * floating-point precision loss.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::multiply(0.1, 0.2); // '0.0200000000'
     * Numbers::multiply(3, 4); // '12.0000000000'
     * Numbers::multiply(2.5, 4.0); // '10.0000000000'
     * ```
     *
     * @param int|float|string $a The first factor
     * @param int|float|string $b The second factor
     * @return string The product as a BCMath string at BCMath precision
     * @see \Phuture\Coherence\Numbers::divide()
     */
    public static function multiply(int|float|string $a, int|float|string $b): string
    {
        return bcmul((string) $a, (string) $b, self::DEFAULT_SCALE);
    }

    /**
     * Creates a fluent Numbers instance for chaining number operations.
     *
     * This method provides a convenient entry point for building a sequence of number
     * operations using method chaining. Instead of calling static methods one by one,
     * you can chain operations together in a single readable expression.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * $result = Numbers::of(10)
     *     ->add(5)
     *     ->multiply(2)
     *     ->get();
     *
     * // Returns: '30.0000000000'
     * ```
     *
     * @param int|float|string $number The starting number to wrap in the fluent interface
     * @return \Phuture\Coherence\Type\Numbers Returns a fluent Numbers instance for chaining
     * @see \Phuture\Coherence\Type\Numbers
     */
    public static function of(int|float|string $number): Type\Numbers
    {
        return new Type\Numbers($number);
    }

    /**
     * Returns the arithmetic opposite (negation) of a number.
     *
     * Flips the sign of the number: positive values become negative and
     * negative values become positive. Zero remains zero.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::opposite(5); // -5
     * Numbers::opposite(-3.2); // 3.2
     * Numbers::opposite(0); // 0
     * ```
     *
     * @param int|float|string $number The number to negate
     * @return int|float|string The negated value
     * @see \Phuture\Coherence\Numbers::absolute()
     */
    public static function opposite(int|float|string $number): int|float|string
    {
        if (is_string($number)) {
            $result = -(float) $number;

            return floor($result) === $result ? (int) $result : $result;
        }

        return -$number;
    }

    /**
     * Converts an integer to its ordinal string representation.
     *
     * Appends the correct English ordinal suffix to the given integer.
     * Handles the special cases for 11th, 12th, and 13th correctly.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::ordinal(1); // '1st'
     * Numbers::ordinal(2); // '2nd'
     * Numbers::ordinal(3); // '3rd'
     * Numbers::ordinal(4); // '4th'
     * Numbers::ordinal(11); // '11th'
     * Numbers::ordinal(21); // '21st'
     * ```
     *
     * @param int $number The integer to convert
     * @return string The ordinal string with the appropriate suffix
     * @see \Phuture\Coherence\Numbers::spell()
     */
    public static function ordinal(int $number): string
    {
        $absNumber = abs($number);

        if ($absNumber % 100 >= 11 && $absNumber % 100 <= 13) {
            return $number . 'th';
        }

        return match ($absNumber % 10) {
            1 => $number . 'st',
            2 => $number . 'nd',
            3 => $number . 'rd',
            default => $number . 'th',
        };
    }

    /**
     * Parses a string to a float using PHP's floatval function.
     *
     * Converts the given value to a floating-point number. Throws when the value
     * is not a valid numeric representation (non-numeric strings, null, arrays, etc.).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::parseFloat('3.14'); // 3.14
     * Numbers::parseFloat('-2.5'); // -2.5
     * Numbers::parseFloat('abc'); // throws InvalidArgumentException
     * ```
     *
     * @param mixed $value The value to parse
     * @return float The parsed float value
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the value is not numeric
     * @see \Phuture\Coherence\Numbers::parseInt()
     * @see \Phuture\Coherence\Numbers::isNumber()
     */
    public static function parseFloat(mixed $value): float
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                'Invalid Argument: The value must be a valid numeric representation'
            );
        }

        return floatval($value);
    }

    /**
     * Parses a string to an integer using PHP's intval function.
     *
     * Converts the given value to an integer. Throws when the value is not
     * a valid numeric representation (non-numeric strings, null, arrays, etc.).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::parseInt('42'); // 42
     * Numbers::parseInt('-7'); // -7
     * Numbers::parseInt('3.9'); // 3
     * Numbers::parseInt('abc'); // throws InvalidArgumentException
     * ```
     *
     * @param mixed $value The value to parse
     * @return int The parsed integer value
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the value is not numeric
     * @see \Phuture\Coherence\Numbers::parseFloat()
     * @see \Phuture\Coherence\Numbers::isNumber()
     */
    public static function parseInt(mixed $value): int
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                'Invalid Argument: The value must be a valid numeric representation'
            );
        }

        return intval($value);
    }

    /**
     * Converts a number into a human-readable percentage string.
     *
     * Multiplies the number by the given multiplicand (default 100) and appends
     * the percent sign. Useful for displaying ratios as percentages.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::percentage(0.75); // '75.0%'
     * Numbers::percentage(0.75, 2); // '75.00%'
     * Numbers::percentage(1.5, 1); // '150.0%'
     * Numbers::percentage(3, 0, 1); // '300%'
     * ```
     *
     * @param int|float|string $number The number to convert to a percentage
     * @param int $precision The number of decimal places (default: 1)
     * @param int $multiplicand The value to multiply by before formatting (default: 100)
     * @return string The formatted percentage string with a percent sign
     * @see \Phuture\Coherence\Numbers::format()
     */
    public static function percentage(int|float|string $number, int $precision = 1, int $multiplicand = 100): string
    {
        return number_format((float) $number * $multiplicand, $precision) . '%';
    }

    /**
     * Rounds a number to the specified precision using the given rounding mode.
     *
     * Delegates to the polyfilled/native `bcround()` for full BCMath precision.
     * The result has exactly `$precision` decimal places.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::round(3.456, 2); // '3.46'
     * Numbers::round(3.456, 0); // '3'
     * Numbers::round(3.5, 0, RoundingMode::HalfTowardsZero); // '3'
     * ```
     *
     * @param int|float|string $number The number to round
     * @param int $precision The number of decimal places (default: 0)
     * @param RoundingMode $mode The rounding mode (default: \RoundingMode::HalfAwayFromZero)
     * @return string The rounded value as a BCMath string with exactly `$precision` decimal places
     * @see \Phuture\Coherence\Numbers::ceil()
     * @see \Phuture\Coherence\Numbers::floor()
     */
    public static function round(
        int|float|string $number,
        int $precision = 0,
        // @phpstan-ignore-next-line
        RoundingMode $mode = RoundingMode::HalfAwayFromZero
    ): string {
        return bcround((string) $number, $precision, $mode);
    }

    /**
     * Spells out a number in English words.
     *
     * Converts an integer to its English word representation. Handles negative
     * numbers by prefixing "negative". Returns the numeric string for numbers
     * outside the supported range.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::spell(0); // 'zero'
     * Numbers::spell(7); // 'seven'
     * Numbers::spell(42); // 'forty-two'
     * Numbers::spell(-5); // 'negative five'
     * Numbers::spell(100); // 'one hundred'
     * Numbers::spell(1000); // 'one thousand'
     * ```
     *
     * @param int $number The number to spell out
     * @return string The English word representation of the number
     * @see \Phuture\Coherence\Numbers::ordinal()
     */
    public static function spell(int $number): string
    {
        $number = (int) $number;

        if ($number === 0) {
            return 'zero';
        }

        if ($number < 0) {
            return 'negative ' . self::spell(abs($number));
        }

        if ($number > 999999999) {
            return (string) $number;
        }

        return self::convertNumberToWords($number);
    }

    /**
     * Computes the square root of a number using BCMath for precision.
     *
     * Returns the square root as a string with the specified number of decimal places.
     * Throws when the number is negative.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::squareRoot(9); // '3.0000000000'
     * Numbers::squareRoot(2, 4); // '1.4142'
     * Numbers::squareRoot(0); // '0.0000000000'
     * ```
     *
     * @param int|float|string $number The number to compute the square root of (must be non-negative)
     * @param int $scale The number of decimal places in the result (default: 10)
     * @return string The square root as a string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the number is negative
     * @see \Phuture\Coherence\Numbers::multiply()
     * @see \Phuture\Coherence\Numbers::round()
     */
    public static function squareRoot(int|float|string $number, int $scale = self::DEFAULT_SCALE): string
    {
        if ((float) $number < 0) {
            throw new InvalidArgumentException(
                'Invalid Argument: Square root of a negative number is not supported'
            );
        }

        return bcsqrt((string) $number, $scale);
    }

    /**
     * Subtracts the second number from the first using BCMath for precision.
     *
     * Both values are converted to strings and subtracted using BCMath to avoid
     * floating-point precision loss.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::subtract(10, 3); // '7.0000000000'
     * Numbers::subtract(5.5, 2.5); // '3.0000000000'
     * Numbers::subtract(1, 1); // '0.0000000000'
     * ```
     *
     * @param int|float|string $a The minuend
     * @param int|float|string $b The subtrahend
     * @return string The difference as a string
     * @see \Phuture\Coherence\Numbers::add()
     */
    public static function subtract(int|float|string $a, int|float|string $b): string
    {
        return bcsub((string) $a, (string) $b, self::DEFAULT_SCALE);
    }

    /**
     * Normalizes any value into a BCMath-compatible numeric string.
     *
     * Each input type is handled differently:
     * - **array**: returns the element count as a BCMath string (e.g. `[1,2,3]` → `'3.0000000000'`)
     * - **bool**: returns `'1.0000000000'` for `true`, `'0.0000000000'` for `false`
     * - **string**: passed directly into BCMath without a float round-trip, preserving all digits up to the scale
     * - **int / float**: converted to a BCMath string (e.g. `42` → `'42.0000000000'`)
     *
     * The result is safe to pass directly into any other BCMath method without precision loss.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::toNumber(true); // '1.0000000000'
     * Numbers::toNumber('3.14'); // '3.1400000000'
     * Numbers::toNumber([1, 2, 3]); // '3.0000000000'
     * Numbers::toNumber(42); // '42.0000000000'
     * Numbers::toNumber('0.3333333333'); // '0.3333333333'
     * ```
     *
     * @param int|float|string|bool|array $number The value to normalize
     * @return string The BCMath string representation
     * @see \Phuture\Coherence\Numbers::parseInt()
     * @see \Phuture\Coherence\Numbers::parseFloat()
     * @see \Phuture\Coherence\Numbers::isNumber()
     */
    public static function toNumber(int|float|string|bool|array $number): string
    {
        if (is_array($number)) {
            return bcadd((string) count($number), '0', self::DEFAULT_SCALE);
        }

        if (is_bool($number)) {
            return bcadd((string) ((int) $number), '0', self::DEFAULT_SCALE);
        }

        if (is_string($number)) {
            if (is_numeric($number)) {
                return bcadd($number, '0', self::DEFAULT_SCALE);
            }

            return bcadd((string) strlen($number), '0', self::DEFAULT_SCALE);
        }

        return bcadd((string) $number, '0', self::DEFAULT_SCALE);
    }

    /**
     * Removes trailing zeros from a numeric string representation.
     *
     * Converts the number to a string and strips any trailing zeros after
     * the decimal point. If all decimal digits are zeros, the decimal point
     * itself is also removed.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Numbers;
     *
     * Numbers::trimTrailingZeros('3.14000'); // '3.14'
     * Numbers::trimTrailingZeros('5.00'); // '5'
     * Numbers::trimTrailingZeros('100.000'); // '100'
     * Numbers::trimTrailingZeros(7.500); // '7.5'
     * ```
     *
     * @param int|float|string $number The number or numeric string to trim
     * @return string The trimmed number string
     * @see \Phuture\Coherence\Numbers::format()
     * @see \Phuture\Coherence\Numbers::abbreviate()
     */
    public static function trimTrailingZeros(int|float|string $number): string
    {
        $string = (string) $number;

        if (!str_contains($string, '.')) {
            return $string;
        }

        $string = rtrim($string, '0');
        $string = rtrim($string, '.');

        return $string;
    }

    /**
     * Asserts that the given value is not NAN.
     *
     * NAN cannot be meaningfully compared with any value, including itself.
     * This method throws a clear exception when a NAN value is detected.
     *
     * @param float $value The value to check
     * @param string $label The parameter label for the error message
     * @throws \Phuture\Coherence\Exception\LogicException When the value is NAN
     */
    private static function assertNotNan(int|float|string $value, string $label): void
    {
        if (is_nan((float) $value)) {
            throw new LogicException(
                "Logic Error: NAN is not a comparable value (parameter \${$label})"
            );
        }
    }

    /**
     * Converts an integer between 0 and 999,999,999 into English words.
     *
     * Breaks the number into groups of three digits and converts each group
     * separately, combining them with the appropriate scale words (thousand,
     * million).
     *
     * @param int $number The non-negative integer to convert
     * @return string The English word representation
     */
    private static function convertNumberToWords(int $number): string
    {
        $ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
            'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen',
            'seventeen', 'eighteen', 'nineteen'];

        $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

        if ($number < 20) {
            return $ones[$number];
        }

        if ($number < 100) {
            $ten = (int) ($number / 10);
            $unit = $number % 10;

            return $tens[$ten] . ($unit > 0 ? '-' . $ones[$unit] : '');
        }

        if ($number < 1000) {
            $hundred = (int) ($number / 100);
            $remainder = $number % 100;

            return $ones[$hundred] . ' hundred' . ($remainder > 0 ? ' ' . self::convertNumberToWords($remainder) : '');
        }

        if ($number < 1000000) {
            $thousands = (int) ($number / 1000);
            $remainder = $number % 1000;

            $result = self::convertNumberToWords($thousands) . ' thousand';
            if ($remainder > 0) {
                $result .= ' ' . self::convertNumberToWords($remainder);
            }

            return $result;
        }

        $millions = (int) ($number / 1000000);
        $remainder = $number % 1000000;

        $result = self::convertNumberToWords($millions) . ' million';
        if ($remainder > 0) {
            $result .= ' ' . self::convertNumberToWords($remainder);
        }

        return $result;
    }

    /**
     * Detects the number of decimal places in a numeric value.
     *
     * Examines the string representation of the number to determine how many
     * digits follow the decimal point.
     *
     * @param int|float|string $number The number to inspect
     * @return int The number of decimal places found
     */
    private static function detectPrecision(int|float|string $number): int
    {
        $string = (string) $number;

        if (!str_contains($string, '.')) {
            return 0;
        }

        return strlen(substr($string, strpos($string, '.') + 1));
    }
}
