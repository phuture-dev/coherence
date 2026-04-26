<?php

declare(strict_types=1);

namespace Phuture\Coherence\Enum;

/**
 * Enumeration for rounding mode strategies.
 *
 * This enum defines the rounding modes available when rounding numbers.
 * Each case maps to a corresponding PHP rounding constant.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
enum RoundingMode
{
    /**
     * Round halves up (away from zero).
     *
     * When the discarded fraction is exactly 0.5, the value is rounded up
     * to the next higher absolute value. This is the most common rounding mode.
     */
    case HalfUp;

    /**
     * Round halves down (toward zero).
     *
     * When the discarded fraction is exactly 0.5, the value is rounded down
     * to the next lower absolute value.
     */
    case HalfDown;

    /**
     * Round halves to the nearest even number.
     *
     * Also known as "banker's rounding". When the discarded fraction is exactly
     * 0.5, the value is rounded to the nearest even number.
     */
    case HalfEven;

    /**
     * Round halves to the nearest odd number.
     *
     * When the discarded fraction is exactly 0.5, the value is rounded to
     * the nearest odd number.
     */
    case HalfOdd;

    /**
     * Returns the corresponding PHP rounding constant for this mode.
     *
     * Maps the enum case to the native PHP constant used by `round()`.
     *
     * @return int The PHP rounding constant
     */
    public function toPhpConstant(): int
    {
        return match ($this) {
            self::HalfUp => PHP_ROUND_HALF_UP,
            self::HalfDown => PHP_ROUND_HALF_DOWN,
            self::HalfEven => PHP_ROUND_HALF_EVEN,
            self::HalfOdd => PHP_ROUND_HALF_ODD,
        };
    }

    /**
     * Returns the corresponding native PHP RoundingMode enum case.
     *
     * Maps this custom enum to the native `\RoundingMode` used by PHP 8.4+'s
     * `round()` function.
     *
     * @return \RoundingMode The native PHP RoundingMode enum case
     */
    public function toNativeRoundingMode(): \RoundingMode
    {
        return match ($this) {
            self::HalfUp => \RoundingMode::HalfAwayFromZero,
            self::HalfDown => \RoundingMode::HalfTowardsZero,
            self::HalfEven => \RoundingMode::HalfEven,
            self::HalfOdd => \RoundingMode::HalfOdd,
        };
    }
}
