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
     * Round halves away from zero.
     *
     * When the discarded fraction is exactly 0.5, the value is rounded away
     * from zero to the next higher absolute value. For example, 2.5 rounds
     * to 3 and -2.5 rounds to -3. This is the most common rounding mode.
     */
    case HalfAwayFromZero;
}
