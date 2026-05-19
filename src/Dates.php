<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use Exception;
use DateTimeZone;
use DateTimeImmutable;
use DateTimeInterface;
use Phuture\Coherence\Support\StaticClass;
use Phuture\Coherence\Exception\InvalidArgumentException;

/**
 * Comprehensive date and time manipulation utility class with full timezone support.
 *
 * This utility class provides a complete toolkit for date and time operations, covering
 * creation, formatting, arithmetic, comparison, and inspection of date values. Every method
 * works with PHP's built-in `DateTimeImmutable` to keep original values unchanged and prevent
 * accidental side effects.
 *
 * Key features:
 *
 * - **Creation**: Build date/time values from scratch, strings, timestamps, or custom formats
 * - **Timezone awareness**: Every creation method accepts an explicit timezone; all operations
 *   preserve or convert timezones without data loss
 * - **Formatting**: Common output formats plus fully custom strftime-style format strings
 * - **Arithmetic**: Add or remove any unit from seconds to years
 * - **Comparison**: Check order, equality, and same-period relationships between two dates
 * - **Difference**: Compute elapsed time in any unit between two dates
 * - **Inspection**: Read individual components (year, month, day, hour …) and ask boolean
 *   questions (is today?, is weekend?, is leap year? …)
 * - **Boundaries**: Jump to the start or end of any period (day, week, month, year)
 * - **Fluent interface**: Call `Dates::of()` to obtain a chainable `\Phuture\Coherence\Type\Dates` wrapper
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Dates extends StaticClass
{
    /**
     * Marker byte sequence for day.js `d` token — day of week (0–6) with no leading zero.
     * PHP has `w` but it must not be used in format strings that go through strtr.
     */
    private const MARKER_DAY_OF_WEEK = "\xFD\x00";

    /**
     * Marker byte sequence for day.js `m` token — minutes (0–59) with no leading zero.
     * PHP's `i` always produces a leading zero; there is no zero-padded alternative.
     */
    private const MARKER_MINUTES_NO_PAD = "\xFD\x02";

    /**
     * Marker byte sequence for day.js `s` token — seconds (0–59) with no leading zero.
     * PHP's `s` always produces a leading zero; there is no zero-padded alternative.
     */
    private const MARKER_SECONDS_NO_PAD = "\xFD\x03";

    /**
     * Marker byte sequence for day.js `dd` token — two-letter day name (Su, Mo, Tu …).
     * PHP has no native format character for two-letter day abbreviations.
     */
    private const MARKER_SHORT_DAY_NAME = "\xFD\x01";

    /**
     * Common holidays observed on fixed calendar dates every year.
     *
     * Contains New Year's Day (January 1), Christmas Day (December 25),
     * and New Year's Eve (December 31). Each entry is an associative array
     * with `month` and `day` keys. Used as the default holiday list by
     * `isHoliday()` and `isBusinessDay()`.
     *
     * @var array
     */
    public static array $holidays = [
        ['month' => 1, 'day' => 1],
        ['month' => 12, 'day' => 25],
        ['month' => 12, 'day' => 31],
    ];

    /**
     * Adds a number of business days to a date/time value.
     *
     * Returns a new date/time value that is the given number of business days
     * (Monday through Friday) later than the original. Weekends (Saturday and
     * Sunday) are skipped and do not count toward the total. A negative value
     * moves backward through the calendar, also skipping weekends.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * // Wednesday + 3 business days = Monday
     * $date = Dates::parse('2026-04-22'); // Wednesday
     * $result = Dates::addBusinessDays($date, 3); // 2026-04-27 (Monday)
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $days The number of business days to add (negative moves backward)
     * @return DateTimeImmutable A new date/time value with the business days added
     * @see \Phuture\Coherence\Dates::addDays()
     * @see \Phuture\Coherence\Dates::diffInBusinessDays()
     */
    public static function addBusinessDays(DateTimeImmutable|string $date, int $days): DateTimeImmutable
    {
        $resolved = self::resolveDate($date);
        $direction = $days >= 0 ? 1 : -1;
        $remaining = abs($days);
        $current = $resolved;

        while ($remaining > 0) {
            $current = $current->modify(($direction > 0 ? '+1' : '-1') . ' day');

            if (self::isWeekday($current)) {
                $remaining--;
            }
        }

        return $current;
    }

    /**
     * Adds a number of days to a date/time value.
     *
     * Returns a new date/time value that is the given number of days later
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21');
     * $result = Dates::addDays($date, 10); // '2026-05-01'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $days The number of days to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the days added
     * @see \Phuture\Coherence\Dates::removeDays()
     */
    public static function addDays(DateTimeImmutable|string $date, int $days): DateTimeImmutable
    {
        return self::resolveDate($date)->modify("+{$days} days");
    }

    /**
     * Adds a number of hours to a date/time value.
     *
     * Returns a new date/time value that is the given number of hours later
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::addHours($date, 3); // '2026-04-21 17:30:00'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $hours The number of hours to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the hours added
     * @see \Phuture\Coherence\Dates::removeHours()
     */
    public static function addHours(DateTimeImmutable|string $date, int $hours): DateTimeImmutable
    {
        return self::resolveDate($date)->modify("+{$hours} hours");
    }

    /**
     * Adds a number of minutes to a date/time value.
     *
     * Returns a new date/time value that is the given number of minutes later
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::addMinutes($date, 45); // '2026-04-21 15:15:00'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $minutes The number of minutes to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the minutes added
     * @see \Phuture\Coherence\Dates::removeMinutes()
     */
    public static function addMinutes(DateTimeImmutable|string $date, int $minutes): DateTimeImmutable
    {
        return self::resolveDate($date)->modify("+{$minutes} minutes");
    }

    /**
     * Adds a number of months to a date/time value.
     *
     * Returns a new date/time value that is the given number of months later
     * than the original. When the resulting day does not exist in the target month
     * (e.g. adding 1 month to January 31 gives March 3 or 2 in a leap year),
     * PHP overflows to the next month.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-01-15');
     * $result = Dates::addMonths($date, 3); // '2026-04-15'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $months The number of months to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the months added
     * @see \Phuture\Coherence\Dates::removeMonths()
     */
    public static function addMonths(DateTimeImmutable|string $date, int $months): DateTimeImmutable
    {
        return self::resolveDate($date)->modify("+{$months} months");
    }

    /**
     * Adds a number of seconds to a date/time value.
     *
     * Returns a new date/time value that is the given number of seconds later
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::addSeconds($date, 90); // '2026-04-21 14:31:30'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $seconds The number of seconds to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the seconds added
     * @see \Phuture\Coherence\Dates::removeSeconds()
     */
    public static function addSeconds(DateTimeImmutable|string $date, int $seconds): DateTimeImmutable
    {
        return self::resolveDate($date)->modify("{$seconds} seconds");
    }

    /**
     * Adds a number of weeks to a date/time value.
     *
     * Returns a new date/time value that is the given number of weeks later
     * than the original. One week equals exactly 7 days.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21');
     * $result = Dates::addWeeks($date, 2); // '2026-05-05'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $weeks The number of weeks to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the weeks added
     * @see \Phuture\Coherence\Dates::removeWeeks()
     */
    public static function addWeeks(DateTimeImmutable|string $date, int $weeks): DateTimeImmutable
    {
        return self::resolveDate($date)->modify("+{$weeks} weeks");
    }

    /**
     * Adds a number of years to a date/time value.
     *
     * Returns a new date/time value that is the given number of years later
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21');
     * $result = Dates::addYears($date, 5); // '2031-04-21'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $years The number of years to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the years added
     * @see \Phuture\Coherence\Dates::removeYears()
     */
    public static function addYears(DateTimeImmutable|string $date, int $years): DateTimeImmutable
    {
        return self::resolveDate($date)->modify("+{$years} years");
    }

    /**
     * Creates a date/time value from individual date and time components.
     *
     * Builds a precise moment in time by specifying each component separately.
     * This is the safest way to create dates when you have distinct year, month,
     * day, hour, minute, and second values from separate sources.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::create(2026, 12, 25); // Christmas midnight UTC
     * $meeting = Dates::create(2026, 4, 21, 14, 30, 0, 'Europe/Paris'); // 14:30 Paris time
     * ```
     *
     * @param int $year The four-digit year (e.g. 2026)
     * @param int $month The month number from 1 (January) to 12 (December)
     * @param int $day The day of the month from 1 to 31
     * @param int $hour The hour from 0 to 23 (default: 0)
     * @param int $minute The minute from 0 to 59 (default: 0)
     * @param int $second The second from 0 to 59 (default: 0)
     * @param string|null $timezone A valid PHP timezone identifier (default: null — system default)
     * @return DateTimeImmutable The constructed date/time value
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the timezone string is invalid
     *   or any component is out of its valid range
     * @see \Phuture\Coherence\Dates::now()
     * @see \Phuture\Coherence\Dates::parse()
     */
    public static function create(
        int $year,
        int $month,
        int $day,
        int $hour = 0,
        int $minute = 0,
        int $second = 0,
        ?string $timezone = null
    ): DateTimeImmutable {
        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException(
                "Invalid Argument: The month \"{$month}\" is out of range. Expected a value between 1 and 12."
            );
        }

        if ($day < 1 || $day > 31) {
            throw new InvalidArgumentException(
                "Invalid Argument: The day \"{$day}\" is out of range. Expected a value between 1 and 31."
            );
        }

        if ($hour < 0 || $hour > 23) {
            throw new InvalidArgumentException(
                "Invalid Argument: The hour \"{$hour}\" is out of range. Expected a value between 0 and 23."
            );
        }

        if ($minute < 0 || $minute > 59) {
            throw new InvalidArgumentException(
                "Invalid Argument: The minute \"{$minute}\" is out of range. Expected a value between 0 and 59."
            );
        }

        if ($second < 0 || $second > 59) {
            throw new InvalidArgumentException(
                "Invalid Argument: The second \"{$second}\" is out of range. Expected a value between 0 and 59."
            );
        }

        $dateString = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second);

        return new DateTimeImmutable($dateString, self::buildTimezone($timezone));
    }

    /**
     * Calculates the number of business days between two date/time values.
     *
     * Returns the absolute (always positive) number of weekdays (Monday through Friday)
     * that fall strictly between the two calendar dates. Both the start and end dates
     * are excluded from the count. Weekends (Saturday and Sunday) are never counted.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $start = Dates::parse('2026-04-20'); // Monday
     * $end = Dates::parse('2026-04-25');   // Saturday
     * Dates::diffInBusinessDays($start, $end); // 4 (Tue, Wed, Thu, Fri)
     * ```
     *
     * @param DateTimeImmutable|string $date The first date/time value
     * @param DateTimeImmutable|string $comparedTo The second date/time value
     * @return int The number of business days between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInDays()
     * @see \Phuture\Coherence\Dates::addBusinessDays()
     */
    public static function diffInBusinessDays(
        DateTimeImmutable|string $date,
        DateTimeImmutable|string $comparedTo
    ): int {
        $resolved = self::resolveDate($date);
        $resolvedTo = self::resolveDate($comparedTo);

        $start = self::startOfDay($resolved);
        $end = self::startOfDay($resolvedTo);

        if ($start > $end) {
            $temp = $start;
            $start = $end;
            $end = $temp;
        }

        $count = 0;
        $current = $start->modify('+1 day');

        while ($current < $end) {
            if (self::isWeekday($current)) {
                $count++;
            }

            $current = $current->modify('+1 day');
        }

        return $count;
    }

    /**
     * Calculates the number of complete days between two date/time values.
     *
     * Returns the absolute (always positive) number of full days between the two dates.
     * Partial days are discarded — for example, 23 hours returns 0.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $start = Dates::parse('2026-04-01');
     * $end = Dates::parse('2026-04-21');
     * Dates::diffInDays($start, $end); // 20
     * ```
     *
     * @param DateTimeImmutable|string $date The first date/time value
     * @param DateTimeImmutable|string $comparedTo The second date/time value
     * @return int The number of complete days between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInHours()
     * @see \Phuture\Coherence\Dates::diffInWeeks()
     */
    public static function diffInDays(DateTimeImmutable|string $date, DateTimeImmutable|string $comparedTo): int
    {
        return (int) (self::diffInSeconds($date, $comparedTo) / 86400);
    }

    /**
     * Calculates the number of complete hours between two date/time values.
     *
     * Returns the absolute (always positive) number of full hours between the two dates.
     * Partial hours are discarded — for example, 59 minutes returns 0.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $start = Dates::parse('2026-04-21 08:00:00');
     * $end = Dates::parse('2026-04-21 20:30:00');
     * Dates::diffInHours($start, $end); // 12
     * ```
     *
     * @param DateTimeImmutable|string $date The first date/time value
     * @param DateTimeImmutable|string $comparedTo The second date/time value
     * @return int The number of complete hours between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInMinutes()
     * @see \Phuture\Coherence\Dates::diffInDays()
     */
    public static function diffInHours(DateTimeImmutable|string $date, DateTimeImmutable|string $comparedTo): int
    {
        return (int) (self::diffInSeconds($date, $comparedTo) / 3600);
    }

    /**
     * Calculates the number of complete minutes between two date/time values.
     *
     * Returns the absolute (always positive) number of full minutes between the two dates.
     * Partial minutes are discarded — for example, 89 seconds returns 1.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $start = Dates::parse('2026-04-21 14:00:00');
     * $end = Dates::parse('2026-04-21 15:30:00');
     * Dates::diffInMinutes($start, $end); // 90
     * ```
     *
     * @param DateTimeImmutable|string $date The first date/time value
     * @param DateTimeImmutable|string $comparedTo The second date/time value
     * @return int The number of complete minutes between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInSeconds()
     * @see \Phuture\Coherence\Dates::diffInHours()
     */
    public static function diffInMinutes(DateTimeImmutable|string $date, DateTimeImmutable|string $comparedTo): int
    {
        return (int) (self::diffInSeconds($date, $comparedTo) / 60);
    }

    /**
     * Calculates the number of complete months between two date/time values.
     *
     * Returns the absolute (always positive) number of full calendar months between
     * the two dates using PHP's DateInterval. Partial months are discarded.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $start = Dates::parse('2026-01-15');
     * $end = Dates::parse('2026-04-10');
     * Dates::diffInMonths($start, $end); // 2 (not 3, because April 10 < January 15 in day)
     * ```
     *
     * @param DateTimeImmutable|string $date The first date/time value
     * @param DateTimeImmutable|string $comparedTo The second date/time value
     * @return int The number of complete months between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInWeeks()
     * @see \Phuture\Coherence\Dates::diffInYears()
     */
    public static function diffInMonths(DateTimeImmutable|string $date, DateTimeImmutable|string $comparedTo): int
    {
        $interval = self::resolveDate($date)->diff(self::resolveDate($comparedTo));

        return abs(($interval->y * 12) + $interval->m);
    }

    /**
     * Calculates the number of complete seconds between two date/time values.
     *
     * Returns the absolute (always positive) number of full seconds between the two dates.
     * The order of the arguments does not matter — the result is always non-negative.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $start = Dates::parse('2026-04-21 14:00:00');
     * $end = Dates::parse('2026-04-21 14:01:30');
     * Dates::diffInSeconds($start, $end); // 90
     * ```
     *
     * @param DateTimeImmutable|string $date The first date/time value
     * @param DateTimeImmutable|string $comparedTo The second date/time value
     * @return int The number of complete seconds between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInMinutes()
     * @see \Phuture\Coherence\Dates::diffInHours()
     */
    public static function diffInSeconds(DateTimeImmutable|string $date, DateTimeImmutable|string $comparedTo): int
    {
        return (int) abs(self::resolveDate($date)->getTimestamp() - self::resolveDate($comparedTo)->getTimestamp());
    }

    /**
     * Calculates the number of complete weeks between two date/time values.
     *
     * Returns the absolute (always positive) number of full weeks between the two dates.
     * Partial weeks are discarded — for example, 6 days returns 0.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $start = Dates::parse('2026-04-07');
     * $end = Dates::parse('2026-04-21');
     * Dates::diffInWeeks($start, $end); // 2
     * ```
     *
     * @param DateTimeImmutable|string $date The first date/time value
     * @param DateTimeImmutable|string $comparedTo The second date/time value
     * @return int The number of complete weeks between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInDays()
     * @see \Phuture\Coherence\Dates::diffInMonths()
     */
    public static function diffInWeeks(DateTimeImmutable|string $date, DateTimeImmutable|string $comparedTo): int
    {
        return (int) (self::diffInDays($date, $comparedTo) / 7);
    }

    /**
     * Calculates the number of complete years between two date/time values.
     *
     * Returns the absolute (always positive) number of full calendar years between
     * the two dates using PHP's DateInterval. Partial years are discarded.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $start = Dates::parse('2020-06-15');
     * $end = Dates::parse('2026-04-10');
     * Dates::diffInYears($start, $end); // 5
     * ```
     *
     * @param DateTimeImmutable|string $date The first date/time value
     * @param DateTimeImmutable|string $comparedTo The second date/time value
     * @return int The number of complete years between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInMonths()
     */
    public static function diffInYears(DateTimeImmutable|string $date, DateTimeImmutable|string $comparedTo): int
    {
        return abs(self::resolveDate($date)->diff(self::resolveDate($comparedTo))->y);
    }

    /**
     * Returns a new date/time value set to the very end of its day (23:59:59).
     *
     * Keeps the same date and timezone but sets the time to one second before midnight.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:45');
     * $result = Dates::endOfDay($date); // '2026-04-21 23:59:59'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at 23:59:59 on the same calendar day
     * @see \Phuture\Coherence\Dates::startOfDay()
     */
    public static function endOfDay(DateTimeImmutable|string $date): DateTimeImmutable
    {
        return self::resolveDate($date)->setTime(23, 59, 59);
    }

    /**
     * Returns a new date/time value set to the last day of the same month at 23:59:59.
     *
     * Automatically accounts for months with different lengths, including February in leap years.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-15');
     * $result = Dates::endOfMonth($date); // '2026-04-30 23:59:59'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at the last day of the month at 23:59:59
     * @see \Phuture\Coherence\Dates::startOfMonth()
     */
    public static function endOfMonth(DateTimeImmutable|string $date): DateTimeImmutable
    {
        return self::resolveDate($date)->modify('last day of this month')->setTime(23, 59, 59);
    }

    /**
     * Returns a new date/time value set to the Sunday of the same ISO week at 23:59:59.
     *
     * The ISO week ends on Sunday. The time is set to 23:59:59.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21'); // Tuesday
     * $result = Dates::endOfWeek($date); // '2026-04-26 23:59:59' (Sunday)
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at Sunday 23:59:59 of the same week
     * @see \Phuture\Coherence\Dates::startOfWeek()
     */
    public static function endOfWeek(DateTimeImmutable|string $date): DateTimeImmutable
    {
        return self::resolveDate($date)->modify('Sunday this week')->setTime(23, 59, 59);
    }

    /**
     * Returns a new date/time value set to December 31st of the same year at 23:59:59.
     *
     * Advances to December 31 and sets the time to 23:59:59.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21');
     * $result = Dates::endOfYear($date); // '2026-12-31 23:59:59'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at December 31st of the same year at 23:59:59
     * @see \Phuture\Coherence\Dates::startOfYear()
     */
    public static function endOfYear(DateTimeImmutable|string $date): DateTimeImmutable
    {
        return self::resolveDate($date)->modify('last day of December this year')->setTime(23, 59, 59);
    }

    /**
     * Checks whether two date/time values represent the exact same moment in time.
     *
     * Both dates are compared as absolute points in time (Unix timestamps).
     * Two dates in different timezones that represent the same moment will be equal.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $utc = Dates::parse('2026-04-21 12:00:00', 'UTC');
     * $ny = Dates::parse('2026-04-21 08:00:00', 'America/New_York');
     * Dates::equals($utc, $ny); // true — same moment, different timezones
     * ```
     *
     * @param DateTimeImmutable|string $date The first date/time value
     * @param DateTimeImmutable|string $comparedTo The second date/time value
     * @return bool Returns true if both values represent the same point in time
     * @see \Phuture\Coherence\Dates::isBefore()
     * @see \Phuture\Coherence\Dates::isAfter()
     */
    public static function equals(DateTimeImmutable|string $date, DateTimeImmutable|string $comparedTo): bool
    {
        return self::resolveDate($date)->getTimestamp() === self::resolveDate($comparedTo)->getTimestamp();
    }

    /**
     * Formats a date/time value using either PHP native or day.js-style format tokens.
     *
     * This method supports two format styles and automatically detects which one
     * you are using based on the format string contents:
     *
     * **PHP native format** — when the string contains only single-character format
     * codes (the same characters as PHP's `date()` function):
     *
     * | Character | Output       | Description                     |
     * |-----------|--------------|---------------------------------|
     * | Y         | 2026         | Four-digit year                 |
     * | y         | 26           | Two-digit year                  |
     * | m         | 04           | Two-digit month                 |
     * | d         | 21           | Two-digit day of month          |
     * | H         | 14           | Two-digit hour (24-hour clock)  |
     * | i         | 30           | Two-digit minute                |
     * | s         | 00           | Two-digit second                |
     * | ...       |              | See PHP date() for all chars    |
     *
     * **day.js-style tokens** — when the string contains multi-character tokens
     * or bracket-escaped text (`[...]`):
     *
     * | Token | Output          | Description                          |
     * |-------|-----------------|--------------------------------------|
     * | YYYY  | 2026            | Four-digit year                      |
     * | YY    | 26              | Two-digit year                       |
     * | MMMM  | January         | Full month name                      |
     * | MMM   | Jan             | Abbreviated month name               |
     * | MM    | 01-12           | Two-digit month                      |
     * | M     | 1-12            | Month without leading zero           |
     * | DD    | 01-31           | Two-digit day of month               |
     * | D     | 1-31            | Day of month without leading zero    |
     * | dddd  | Sunday          | Full day of week name                |
     * | ddd   | Sun             | Abbreviated day of week name         |
     * | dd    | Su              | Two-letter day of week name          |
     * | d     | 0-6             | Day of week (Sunday = 0)             |
     * | HH    | 00-23           | Two-digit hour (24-hour clock)       |
     * | H     | 0-23            | Hour without leading zero (24-hour)  |
     * | hh    | 01-12           | Two-digit hour (12-hour clock)       |
     * | h     | 1-12            | Hour without leading zero (12-hour)  |
     * | mm    | 00-59           | Two-digit minute                     |
     * | m     | 0-59            | Minute without leading zero          |
     * | ss    | 00-59           | Two-digit second                     |
     * | s     | 0-59            | Second without leading zero          |
     * | SSS   | 000-999         | Three-digit milliseconds             |
     * | Z     | +01:00          | UTC offset with colon                |
     * | ZZ    | +0100           | UTC offset without colon             |
     * | A     | AM              | Uppercase AM/PM marker               |
     * | a     | am              | Lowercase am/pm marker               |
     *
     * To include literal text in a day.js-style format, wrap it in square brackets.
     * For example, `[at]` outputs the word "at" without transforming the letters.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
     *
     * // PHP native format (single-character codes)
     * Dates::format($date, 'Y-m-d'); // '2026-04-21'
     * Dates::format($date, 'd/m/Y H:i'); // '21/04/2026 14:30'
     * Dates::format($date, 'l, F j, Y'); // 'Tuesday, April 21, 2026'
     *
     * // day.js-style tokens (multi-character codes)
     * Dates::format($date, 'YYYY-MM-DD'); // '2026-04-21'
     * Dates::format($date, 'DD/MM/YYYY HH:mm'); // '21/04/2026 14:30'
     * Dates::format($date, 'dddd, MMMM D, YYYY'); // 'Tuesday, April 21, 2026'
     * Dates::format($date, 'h:mm A'); // '2:30 PM'
     * Dates::format($date, '[Today is] dddd'); // 'Today is Tuesday'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to format
     * @param string $format The format string using either PHP date() characters or
     *   day.js-style tokens (auto-detected)
     * @return string The formatted date/time string
     * @see \Phuture\Coherence\Dates::toDate()
     * @see \Phuture\Coherence\Dates::toDateTime()
     */
    public static function format(DateTimeImmutable|string $date, string $format): string
    {
        $resolved = self::resolveDate($date);

        if (!self::isDayJsFormat($format)) {
            return $resolved->format($format);
        }

        $phpFormat = self::convertDayJsFormatToPhp($format);

        $shortDayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

        return str_replace(
            [
                self::MARKER_DAY_OF_WEEK,
                self::MARKER_SHORT_DAY_NAME,
                self::MARKER_MINUTES_NO_PAD,
                self::MARKER_SECONDS_NO_PAD,
            ],
            [
                (string) (int) $resolved->format('w'),
                $shortDayNames[(int) $resolved->format('w')],
                (string) (int) $resolved->format('i'),
                (string) (int) $resolved->format('s'),
            ],
            $resolved->format($phpFormat)
        );
    }

    /**
     * Creates a date/time value from a string using an explicit format pattern.
     *
     * Use this when you know the exact format of your date string and want
     * strict parsing. Supports both PHP native format characters and day.js-style
     * tokens — the format style is auto-detected the same way as {@see format()}.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * // PHP native format
     * $date = Dates::fromFormat('d/m/Y', '21/04/2026');
     * $date = Dates::fromFormat('Y-m-d H:i:s', '2026-04-21 14:30:00', 'Europe/London');
     *
     * // day.js-style tokens
     * $date = Dates::fromFormat('DD/MM/YYYY', '21/04/2026');
     * $date = Dates::fromFormat('YYYY-MM-DD HH:mm:ss', '2026-04-21 14:30:00', 'Europe/London');
     * ```
     *
     * @param string $format The format pattern using either PHP date() characters or
     *   day.js-style tokens (auto-detected)
     * @param string $dateString The date string to parse according to the format
     * @param string|null $timezone A valid PHP timezone identifier (default: null — system default)
     * @return DateTimeImmutable The parsed date and time value
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the format does not match
     *   the date string or the timezone is invalid
     * @see \Phuture\Coherence\Dates::parse()
     * @see \Phuture\Coherence\Dates::format()
     */
    public static function fromFormat(string $format, string $dateString, ?string $timezone = null): DateTimeImmutable
    {
        $phpFormat = self::isDayJsFormat($format)
            ? str_replace(
                [
                    self::MARKER_DAY_OF_WEEK,
                    self::MARKER_SHORT_DAY_NAME,
                    self::MARKER_MINUTES_NO_PAD,
                    self::MARKER_SECONDS_NO_PAD,
                ],
                ['w', 'D', 'i', 's'],
                self::convertDayJsFormatToPhp($format)
            )
            : $format;

        $date = DateTimeImmutable::createFromFormat($phpFormat, $dateString, self::buildTimezone($timezone));

        if ($date === false) {
            throw new InvalidArgumentException(
                "Invalid Argument: The date string \"{$dateString}\" does not match the format \"{$format}\"."
            );
        }

        return $date;
    }

    /**
     * Creates a date/time value from a Unix timestamp.
     *
     * A Unix timestamp is the number of seconds that have elapsed since
     * 1 January 1970 00:00:00 UTC. This is the format returned by PHP's time() function.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::fromTimestamp(1745236800); // UTC
     * $date = Dates::fromTimestamp(1745236800, 'America/Los_Angeles'); // same moment, LA time
     * ```
     *
     * @param int $timestamp The number of seconds since the Unix epoch (1970-01-01 00:00:00 UTC)
     * @param string|null $timezone A valid PHP timezone identifier for display (default: null — UTC)
     * @return DateTimeImmutable The date and time represented by the timestamp
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the timezone string is invalid
     * @see \Phuture\Coherence\Dates::toTimestamp()
     */
    public static function fromTimestamp(int $timestamp, ?string $timezone = null): DateTimeImmutable
    {
        $date = new DateTimeImmutable('@' . $timestamp);

        if ($timezone !== null) {
            $date = $date->setTimezone(self::buildTimezone($timezone));
        }

        return $date;
    }

    /**
     * Returns the day of the month for a date/time value.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21');
     * Dates::getDay($date); // 21
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return int The day of the month as an integer from 1 to 31
     * @see \Phuture\Coherence\Dates::getMonth()
     * @see \Phuture\Coherence\Dates::getYear()
     */
    public static function getDay(DateTimeImmutable|string $date): int
    {
        return (int) self::resolveDate($date)->format('j');
    }

    /**
     * Returns the day of the week for a date/time value.
     *
     * The returned value follows the ISO 8601 standard where Monday is 1
     * and Sunday is 7.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21'); // Tuesday
     * Dates::getDayOfWeek($date); // 2
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return int The ISO 8601 day of the week: 1 (Monday) through 7 (Sunday)
     * @see \Phuture\Coherence\Dates::getDayOfYear()
     * @see \Phuture\Coherence\Dates::isWeekend()
     */
    public static function getDayOfWeek(DateTimeImmutable|string $date): int
    {
        return (int) self::resolveDate($date)->format('N');
    }

    /**
     * Returns the day of the year for a date/time value.
     *
     * January 1 is day 1, December 31 is day 365 (or 366 in a leap year).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-01-31');
     * Dates::getDayOfYear($date); // 31
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return int The day of the year as an integer from 1 to 366
     * @see \Phuture\Coherence\Dates::getDayOfWeek()
     * @see \Phuture\Coherence\Dates::getWeekOfYear()
     */
    public static function getDayOfYear(DateTimeImmutable|string $date): int
    {
        return (int) self::resolveDate($date)->format('z') + 1;
    }

    /**
     * Returns the number of days in the month of a date/time value.
     *
     * Takes leap years into account when calculating February.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-02-01');
     * Dates::getDaysInMonth($date); // 28
     *
     * $leapDate = Dates::parse('2024-02-01');
     * Dates::getDaysInMonth($leapDate); // 29
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return int The number of days in the month, from 28 to 31
     * @see \Phuture\Coherence\Dates::isLeapYear()
     */
    public static function getDaysInMonth(DateTimeImmutable|string $date): int
    {
        return (int) self::resolveDate($date)->format('t');
    }

    /**
     * Returns the hour of a date/time value.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00');
     * Dates::getHour($date); // 14
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return int The hour as an integer from 0 to 23
     * @see \Phuture\Coherence\Dates::getMinute()
     * @see \Phuture\Coherence\Dates::getSecond()
     */
    public static function getHour(DateTimeImmutable|string $date): int
    {
        return (int) self::resolveDate($date)->format('G');
    }

    /**
     * Returns the minute of a date/time value.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00');
     * Dates::getMinute($date); // 30
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return int The minute as an integer from 0 to 59
     * @see \Phuture\Coherence\Dates::getHour()
     * @see \Phuture\Coherence\Dates::getSecond()
     */
    public static function getMinute(DateTimeImmutable|string $date): int
    {
        return (int) self::resolveDate($date)->format('i');
    }

    /**
     * Returns the month number of a date/time value.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21');
     * Dates::getMonth($date); // 4
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return int The month as an integer from 1 (January) to 12 (December)
     * @see \Phuture\Coherence\Dates::getYear()
     * @see \Phuture\Coherence\Dates::getDay()
     */
    public static function getMonth(DateTimeImmutable|string $date): int
    {
        return (int) self::resolveDate($date)->format('n');
    }

    /**
     * Returns the second of a date/time value.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:45');
     * Dates::getSecond($date); // 45
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return int The second as an integer from 0 to 59
     * @see \Phuture\Coherence\Dates::getHour()
     * @see \Phuture\Coherence\Dates::getMinute()
     */
    public static function getSecond(DateTimeImmutable|string $date): int
    {
        return (int) self::resolveDate($date)->format('s');
    }

    /**
     * Returns the timezone identifier of a date/time value.
     *
     * Extracts the name of the timezone that is associated with the given date,
     * such as 'America/New_York' or 'UTC'.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::now('Asia/Tokyo');
     * $tz = Dates::getTimezone($date); // 'Asia/Tokyo'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to read the timezone from
     * @return string The timezone identifier string (e.g. 'Europe/Paris')
     * @see \Phuture\Coherence\Dates::toTimezone()
     */
    public static function getTimezone(DateTimeImmutable|string $date): string
    {
        return self::resolveDate($date)->getTimezone()->getName();
    }

    /**
     * Returns the ISO 8601 week number of the year for a date/time value.
     *
     * Weeks start on Monday. The first week of the year is the week containing
     * the year's first Thursday (ISO 8601 definition).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-01-01');
     * Dates::getWeekOfYear($date); // 1
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return int The ISO 8601 week number from 1 to 53
     * @see \Phuture\Coherence\Dates::getDayOfYear()
     */
    public static function getWeekOfYear(DateTimeImmutable|string $date): int
    {
        return (int) self::resolveDate($date)->format('W');
    }

    /**
     * Returns the four-digit year of a date/time value.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21');
     * Dates::getYear($date); // 2026
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return int The year as a four-digit integer (e.g. 2026)
     * @see \Phuture\Coherence\Dates::getMonth()
     * @see \Phuture\Coherence\Dates::getDay()
     */
    public static function getYear(DateTimeImmutable|string $date): int
    {
        return (int) self::resolveDate($date)->format('Y');
    }

    /**
     * Checks whether a date/time value is after another.
     *
     * Returns true if the first date comes later in time than the second date.
     * Both dates are compared as absolute points in time, regardless of timezone.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $later = Dates::parse('2026-12-31');
     * $earlier = Dates::parse('2026-01-01');
     * Dates::isAfter($later, $earlier); // true
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to test
     * @param DateTimeImmutable|string $comparedTo The date/time value to compare against
     * @return bool Returns true if $date is after $comparedTo
     * @see \Phuture\Coherence\Dates::isBefore()
     * @see \Phuture\Coherence\Dates::equals()
     */
    public static function isAfter(DateTimeImmutable|string $date, DateTimeImmutable|string $comparedTo): bool
    {
        return self::resolveDate($date) > self::resolveDate($comparedTo);
    }

    /**
     * Checks whether a date/time value is before another.
     *
     * Returns true if the first date comes earlier in time than the second date.
     * Both dates are compared as absolute points in time, regardless of timezone.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $earlier = Dates::parse('2026-01-01');
     * $later = Dates::parse('2026-12-31');
     * Dates::isBefore($earlier, $later); // true
     * Dates::isBefore($later, $earlier); // false
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to test
     * @param DateTimeImmutable|string $comparedTo The date/time value to compare against
     * @return bool Returns true if $date is before $comparedTo
     * @see \Phuture\Coherence\Dates::isAfter()
     * @see \Phuture\Coherence\Dates::equals()
     */
    public static function isBefore(DateTimeImmutable|string $date, DateTimeImmutable|string $comparedTo): bool
    {
        return self::resolveDate($date) < self::resolveDate($comparedTo);
    }

    /**
     * Checks whether a date/time value falls on a business day.
     *
     * A business day is a weekday (Monday through Friday) that is not a holiday.
     * When no holiday list is given, the static `Dates::$holidays` property is
     * used to exclude common holidays. Pass a custom list to override the defaults.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * // Uses default holidays (Jan 1, Dec 25, Dec 31)
     * Dates::isBusinessDay('2026-01-01'); // false (holiday)
     * Dates::isBusinessDay('2026-01-02'); // true (Friday, not a holiday)
     * Dates::isBusinessDay('2026-04-18'); // false (Saturday)
     *
     * // Custom holidays override the defaults
     * $custom = [['month' => 7, 'day' => 4]];
     * Dates::isBusinessDay('2026-07-04', $custom); // false (holiday)
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @param array<array{month: int, day: int}>|null $holidays A list of holidays,
     *  each defined as an array with `month` and `day` keys.
     *  Pass null to use the default `Dates::$holidays` (default: null)
     * @return bool Returns true if the date is a weekday and not a holiday
     * @see \Phuture\Coherence\Dates::$holidays
     * @see \Phuture\Coherence\Dates::isWeekday()
     * @see \Phuture\Coherence\Dates::isWeekend()
     * @see \Phuture\Coherence\Dates::isHoliday()
     */
    public static function isBusinessDay(
        DateTimeImmutable|string $date,
        ?array $holidays = null
    ): bool {
        return self::isWeekday($date) && !self::isHoliday($date, $holidays);
    }

    /**
     * Checks whether a date/time value is in the future.
     *
     * Returns true if the given date/time is strictly after the current moment.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $future = Dates::parse('2030-01-01');
     * $past = Dates::parse('2020-01-01');
     * Dates::isFuture($future); // true
     * Dates::isFuture($past); // false
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return bool Returns true if the date is after the current moment
     * @see \Phuture\Coherence\Dates::isPast()
     */
    public static function isFuture(DateTimeImmutable|string $date): bool
    {
        $resolved = self::resolveDate($date);

        return $resolved > new DateTimeImmutable('now', $resolved->getTimezone());
    }

    /**
     * Checks whether a date/time value falls on a holiday.
     *
     * Compares the month and day of the given date against a list of fixed-date
     * holidays. Each holiday is defined as an associative array with `month`
     * (1–12) and `day` (1–31) keys. The year is not considered, so the same
     * holiday definition matches every year.
     *
     * When no holiday list is given, the static `Dates::$holidays` property is
     * used. That property ships with three common holidays: New Year's Day
     * (January 1), Christmas Day (December 25), and New Year's Eve (December 31).
     * You can override it globally or pass a custom list to this method.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * // Uses the default holidays (Jan 1, Dec 25, Dec 31)
     * Dates::isHoliday('2026-01-01'); // true
     * Dates::isHoliday('2026-12-25'); // true
     * Dates::isHoliday('2026-12-31'); // true
     * Dates::isHoliday('2026-03-15'); // false
     *
     * // Custom holidays override the defaults
     * $custom = [['month' => 7, 'day' => 4]];
     * Dates::isHoliday('2026-07-04', $custom); // true
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @param array<array{month: int, day: int}>|null $holidays A list of holidays,
     *  each defined as an array with `month` and `day` keys.
     *  Pass null to use the default `Dates::$holidays` (default: null)
     * @return bool Returns true if the date matches any holiday in the list
     * @see \Phuture\Coherence\Dates::$holidays
     * @see \Phuture\Coherence\Dates::isBusinessDay()
     * @see \Phuture\Coherence\Dates::isWeekend()
     */
    public static function isHoliday(
        DateTimeImmutable|string $date,
        ?array $holidays = null
    ): bool {
        $holidays = $holidays ?? self::$holidays;
        $resolved = self::resolveDate($date);
        $month = (int) $resolved->format('n');
        $day = (int) $resolved->format('j');

        foreach ($holidays as $holiday) {
            if ($holiday['month'] === $month && $holiday['day'] === $day) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the year of a date/time value is a leap year.
     *
     * A leap year has 366 days. It occurs when the year is divisible by 4,
     * except for years divisible by 100, which must also be divisible by 400.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * Dates::isLeapYear(Dates::parse('2024-01-01')); // true
     * Dates::isLeapYear(Dates::parse('2026-01-01')); // false
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return bool Returns true if the year is a leap year
     * @see \Phuture\Coherence\Dates::getDaysInMonth()
     */
    public static function isLeapYear(DateTimeImmutable|string $date): bool
    {
        return self::resolveDate($date)->format('L') === '1';
    }

    /**
     * Checks whether a date/time value is in the past.
     *
     * Returns true if the given date/time is strictly before the current moment.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $past = Dates::parse('2020-01-01');
     * $future = Dates::parse('2030-01-01');
     * Dates::isPast($past); // true
     * Dates::isPast($future); // false
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return bool Returns true if the date is before the current moment
     * @see \Phuture\Coherence\Dates::isFuture()
     */
    public static function isPast(DateTimeImmutable|string $date): bool
    {
        $resolved = self::resolveDate($date);

        return $resolved < new DateTimeImmutable('now', $resolved->getTimezone());
    }

    /**
     * Checks whether two date/time values fall on the same calendar day.
     *
     * Compares only the year, month, and day. The time portions and timezones
     * are ignored in this comparison. The comparison is done in each date's own timezone.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $morning = Dates::parse('2026-04-21 08:00:00');
     * $evening = Dates::parse('2026-04-21 22:00:00');
     * $tomorrow = Dates::parse('2026-04-22 08:00:00');
     * Dates::isSameDay($morning, $evening); // true
     * Dates::isSameDay($morning, $tomorrow); // false
     * ```
     *
     * @param DateTimeImmutable|string $date The first date/time value
     * @param DateTimeImmutable|string $comparedTo The second date/time value
     * @return bool Returns true if both values fall on the same calendar day
     * @see \Phuture\Coherence\Dates::isSameMonth()
     * @see \Phuture\Coherence\Dates::isSameYear()
     */
    public static function isSameDay(DateTimeImmutable|string $date, DateTimeImmutable|string $comparedTo): bool
    {
        return self::resolveDate($date)->format('Y-m-d') === self::resolveDate($comparedTo)->format('Y-m-d');
    }

    /**
     * Checks whether two date/time values fall in the same calendar month and year.
     *
     * Compares the year and month only. The day, time, and timezone are ignored.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $first = Dates::parse('2026-04-01');
     * $last = Dates::parse('2026-04-30');
     * $next = Dates::parse('2026-05-01');
     * Dates::isSameMonth($first, $last); // true
     * Dates::isSameMonth($first, $next); // false
     * ```
     *
     * @param DateTimeImmutable|string $date The first date/time value
     * @param DateTimeImmutable|string $comparedTo The second date/time value
     * @return bool Returns true if both values fall in the same calendar month and year
     * @see \Phuture\Coherence\Dates::isSameDay()
     * @see \Phuture\Coherence\Dates::isSameYear()
     */
    public static function isSameMonth(DateTimeImmutable|string $date, DateTimeImmutable|string $comparedTo): bool
    {
        return self::resolveDate($date)->format('Y-m') === self::resolveDate($comparedTo)->format('Y-m');
    }

    /**
     * Checks whether two date/time values fall in the same calendar year.
     *
     * Compares only the year. Month, day, time, and timezone are ignored.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $jan = Dates::parse('2026-01-01');
     * $dec = Dates::parse('2026-12-31');
     * $ny = Dates::parse('2027-01-01');
     * Dates::isSameYear($jan, $dec); // true
     * Dates::isSameYear($jan, $ny); // false
     * ```
     *
     * @param DateTimeImmutable|string $date The first date/time value
     * @param DateTimeImmutable|string $comparedTo The second date/time value
     * @return bool Returns true if both values fall in the same calendar year
     * @see \Phuture\Coherence\Dates::isSameDay()
     * @see \Phuture\Coherence\Dates::isSameMonth()
     */
    public static function isSameYear(DateTimeImmutable|string $date, DateTimeImmutable|string $comparedTo): bool
    {
        return self::resolveDate($date)->format('Y') === self::resolveDate($comparedTo)->format('Y');
    }

    /**
     * Checks whether a date/time value falls on today's date.
     *
     * Compares only the calendar date (year, month, day) in the date's own timezone.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $today = Dates::now();
     * $yesterday = Dates::removeSeconds($today, 86400);
     * Dates::isToday($today); // true
     * Dates::isToday($yesterday); // false
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return bool Returns true if the date falls on today's calendar date
     * @see \Phuture\Coherence\Dates::isYesterday()
     * @see \Phuture\Coherence\Dates::isTomorrow()
     */
    public static function isToday(DateTimeImmutable|string $date): bool
    {
        $resolved = self::resolveDate($date);

        return self::isSameDay($resolved, new DateTimeImmutable('today', $resolved->getTimezone()));
    }

    /**
     * Checks whether a date/time value falls on tomorrow's date.
     *
     * Compares only the calendar date (year, month, day) in the date's own timezone.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $tomorrow = Dates::addSeconds(Dates::now(), 86400);
     * Dates::isTomorrow($tomorrow); // true
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return bool Returns true if the date falls on tomorrow's calendar date
     * @see \Phuture\Coherence\Dates::isToday()
     * @see \Phuture\Coherence\Dates::isYesterday()
     */
    public static function isTomorrow(DateTimeImmutable|string $date): bool
    {
        $resolved = self::resolveDate($date);

        return self::isSameDay($resolved, new DateTimeImmutable('tomorrow', $resolved->getTimezone()));
    }

    /**
     * Checks whether a date/time value falls on a weekday (Monday through Friday).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $tuesday = Dates::parse('2026-04-21'); // Tuesday
     * $saturday = Dates::parse('2026-04-18'); // Saturday
     * Dates::isWeekday($tuesday); // true
     * Dates::isWeekday($saturday); // false
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return bool Returns true if the date falls on Monday through Friday
     * @see \Phuture\Coherence\Dates::isWeekend()
     * @see \Phuture\Coherence\Dates::getDayOfWeek()
     */
    public static function isWeekday(DateTimeImmutable|string $date): bool
    {
        return !self::isWeekend($date);
    }

    /**
     * Checks whether a date/time value falls on a weekend (Saturday or Sunday).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $saturday = Dates::parse('2026-04-18'); // Saturday
     * $tuesday = Dates::parse('2026-04-21'); // Tuesday
     * Dates::isWeekend($saturday); // true
     * Dates::isWeekend($tuesday); // false
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return bool Returns true if the date falls on a Saturday or Sunday
     * @see \Phuture\Coherence\Dates::isWeekday()
     * @see \Phuture\Coherence\Dates::getDayOfWeek()
     */
    public static function isWeekend(DateTimeImmutable|string $date): bool
    {
        $dayOfWeek = self::getDayOfWeek($date);

        return $dayOfWeek === 6 || $dayOfWeek === 7;
    }

    /**
     * Checks whether a date/time value falls on yesterday's date.
     *
     * Compares only the calendar date (year, month, day) in the date's own timezone.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $yesterday = Dates::removeSeconds(Dates::now(), 86400);
     * Dates::isYesterday($yesterday); // true
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to inspect
     * @return bool Returns true if the date falls on yesterday's calendar date
     * @see \Phuture\Coherence\Dates::isToday()
     * @see \Phuture\Coherence\Dates::isTomorrow()
     */
    public static function isYesterday(DateTimeImmutable|string $date): bool
    {
        $resolved = self::resolveDate($date);

        return self::isSameDay($resolved, new DateTimeImmutable('yesterday', $resolved->getTimezone()));
    }

    /**
     * Returns the current date and time.
     *
     * Creates a new date/time value representing the exact moment this method is called.
     * When no timezone is given, the system's default timezone is used.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $now = Dates::now(); // e.g. 2026-04-21 14:30:00 UTC
     * $nowInTokyo = Dates::now('Asia/Tokyo'); // same moment, Tokyo time
     * ```
     *
     * @param string|null $timezone A valid PHP timezone identifier such as 'America/New_York'
     *   (default: null, which uses the system default timezone)
     * @return DateTimeImmutable The current date and time in the requested timezone
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the timezone string is invalid
     * @see \Phuture\Coherence\Dates::create()
     * @see \Phuture\Coherence\Dates::parse()
     */
    public static function now(?string $timezone = null): DateTimeImmutable
    {
        return new DateTimeImmutable('now', self::buildTimezone($timezone));
    }

    /**
     * Returns a fluent wrapper around a date/time value for chainable operations.
     *
     * This is the recommended way to work with multiple operations on a single date.
     * Pass either a `DateTimeImmutable` instance or a date string. When a string is provided,
     * it is parsed using `Dates::parse()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $result = Dates::of('2026-04-21 14:30:00')
     *     ->addDays(10)
     *     ->startOfDay()
     *     ->get();
     * // DateTimeImmutable for '2026-05-01 00:00:00'
     * ```
     *
     * @param DateTimeImmutable|string $date A DateTimeImmutable instance or a parseable date string
     * @param string|null $timezone A valid PHP timezone identifier — only used when $date is a string
     *   (default: null — system default)
     * @return \Phuture\Coherence\Type\Dates A fluent wrapper that enables method chaining
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the string cannot be parsed
     *   or the timezone is invalid
     */
    public static function of(DateTimeImmutable|string $date, ?string $timezone = null): Type\Dates
    {
        if (is_string($date)) {
            $date = self::parse($date, $timezone);
        }

        return new Type\Dates($date);
    }

    /**
     * Parses a relative date expression into an absolute date/time value.
     *
     * Converts a human-readable relative expression like '3 days', '2 hours ago',
     * or 'next Monday' into a concrete DateTimeImmutable value. The expression
     * is evaluated relative to a reference date, which defaults to the current
     * moment when not provided.
     *
     * This method supports everything PHP's `strtotime()` accepts, plus the
     * 'in X units' pattern that PHP does not handle natively:
     *
     * - PHP native: '+2 days', '-1 week', 'next Monday', 'last day of next month'
     * - Natural language: '3 days ago', '2 hours ago', '1 week'
     * - Extended: 'in 3 days', 'in 2 hours', 'in 1 week', 'in 5 minutes'
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
     *
     * // PHP native relative expressions
     * Dates::parseRelative('+3 days', $ref);  // 2026-04-24 12:00:00
     * Dates::parseRelative('-1 week', $ref);  // 2026-04-14 12:00:00
     *
     * // Natural language
     * Dates::parseRelative('2 days ago', $ref); // 2026-04-19 12:00:00
     *
     * // Extended 'in X units' pattern
     * Dates::parseRelative('in 5 hours', $ref); // 2026-04-21 17:00:00
     *
     * // Without a reference date, resolves against the current moment
     * Dates::parseRelative('tomorrow');
     * ```
     *
     * @param string $expression A relative date expression (e.g. '+2 days', 'in 3 hours', 'yesterday')
     * @param DateTimeImmutable|string|null $relativeTo The reference date/time to resolve against.
     *   Pass null to use the current moment (default: null)
     * @param string|null $timezone A valid PHP timezone identifier used only when $relativeTo
     *   is null and the current moment is needed (default: null — UTC)
     * @return DateTimeImmutable The absolute date/time that the expression resolves to
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the expression cannot be parsed
     *   or the timezone is invalid
     * @see \Phuture\Coherence\Dates::parse()
     * @see \Phuture\Coherence\Dates::toRelative()
     */
    public static function parseRelative(
        string $expression,
        DateTimeImmutable|string|null $relativeTo = null,
        ?string $timezone = null
    ): DateTimeImmutable {
        if ($expression === '') {
            throw new InvalidArgumentException(
                'Invalid Argument: The relative date expression must not be empty.'
            );
        }

        $baseDate = self::resolveRelativeBaseDate($relativeTo, $timezone);
        $phpExpression = self::normalizeRelativeExpression($expression);

        try {
            $result = $baseDate->modify($phpExpression);
        } catch (Exception $e) {
            throw new InvalidArgumentException(
                "Invalid Argument: The relative date expression \"{$expression}\" could not be parsed."
            );
        }

        if ($result === false) {
            throw new InvalidArgumentException(
                "Invalid Argument: The relative date expression \"{$expression}\" could not be parsed."
            );
        }

        return $result;
    }

    /**
     * Parses a date/time string into a DateTimeImmutable value.
     *
     * Accepts any date/time string that PHP's DateTimeImmutable constructor understands,
     * such as '2026-04-21', 'next Monday', 'yesterday', or '+2 days'.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-12-25');
     * $date = Dates::parse('next Friday', 'America/New_York');
     * $date = Dates::parse('2026-04-21 14:30:00', 'Europe/Berlin');
     * ```
     *
     * @param string $dateString Any date/time string understood by PHP's date parser
     * @param string|null $timezone A valid PHP timezone identifier (default: null — system default)
     * @return DateTimeImmutable The parsed date and time value
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the timezone string is invalid
     *   or the date string cannot be parsed
     * @see \Phuture\Coherence\Dates::fromFormat()
     * @see \Phuture\Coherence\Dates::fromTimestamp()
     */
    public static function parse(string $dateString, ?string $timezone = null): DateTimeImmutable
    {
        if ($dateString === '') {
            throw new InvalidArgumentException(
                'Invalid Argument: The date string must not be empty.'
            );
        }

        try {
            return new DateTimeImmutable($dateString, self::buildTimezone($timezone));
        } catch (Exception $e) {
            throw new InvalidArgumentException(
                "Invalid Argument: The date string \"{$dateString}\" could not be parsed into a valid date."
            );
        }
    }

    /**
     * Removes a number of days from a date/time value.
     *
     * Returns a new date/time value that is the given number of days earlier
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21');
     * $result = Dates::removeDays($date, 5); // '2026-04-16'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $days The number of days to remove (must be >= 0)
     * @return DateTimeImmutable A new date/time value with the days removed
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When $days is negative
     * @see \Phuture\Coherence\Dates::addDays()
     */
    public static function removeDays(DateTimeImmutable|string $date, int $days): DateTimeImmutable
    {
        if ($days < 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: The days value must be >= 0. Use addDays() to move forward in time."
            );
        }

        return self::resolveDate($date)->modify("-{$days} days");
    }

    /**
     * Removes a number of hours from a date/time value.
     *
     * Returns a new date/time value that is the given number of hours earlier
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::removeHours($date, 2); // '2026-04-21 12:30:00'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $hours The number of hours to remove (must be >= 0)
     * @return DateTimeImmutable A new date/time value with the hours removed
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When $hours is negative
     * @see \Phuture\Coherence\Dates::addHours()
     */
    public static function removeHours(DateTimeImmutable|string $date, int $hours): DateTimeImmutable
    {
        if ($hours < 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: The hours value must be >= 0. Use addHours() to move forward in time."
            );
        }

        return self::resolveDate($date)->modify("-{$hours} hours");
    }

    /**
     * Removes a number of minutes from a date/time value.
     *
     * Returns a new date/time value that is the given number of minutes earlier
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::removeMinutes($date, 15); // '2026-04-21 14:15:00'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $minutes The number of minutes to remove (must be >= 0)
     * @return DateTimeImmutable A new date/time value with the minutes removed
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When $minutes is negative
     * @see \Phuture\Coherence\Dates::addMinutes()
     */
    public static function removeMinutes(DateTimeImmutable|string $date, int $minutes): DateTimeImmutable
    {
        if ($minutes < 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: The minutes value must be >= 0. Use addMinutes() to move forward in time."
            );
        }

        return self::resolveDate($date)->modify("-{$minutes} minutes");
    }

    /**
     * Removes a number of months from a date/time value.
     *
     * Returns a new date/time value that is the given number of months earlier
     * than the original. When the resulting day does not exist in the target month,
     * PHP overflows to the next month.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-06-15');
     * $result = Dates::removeMonths($date, 2); // '2026-04-15'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $months The number of months to remove (must be >= 0)
     * @return DateTimeImmutable A new date/time value with the months removed
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When $months is negative
     * @see \Phuture\Coherence\Dates::addMonths()
     */
    public static function removeMonths(DateTimeImmutable|string $date, int $months): DateTimeImmutable
    {
        if ($months < 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: The months value must be >= 0. Use addMonths() to move forward in time."
            );
        }

        return self::resolveDate($date)->modify("-{$months} months");
    }

    /**
     * Removes a number of seconds from a date/time value.
     *
     * Returns a new date/time value that is the given number of seconds earlier
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::removeSeconds($date, 30); // '2026-04-21 14:29:30'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $seconds The number of seconds to remove (must be >= 0)
     * @return DateTimeImmutable A new date/time value with the seconds removed
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When $seconds is negative
     * @see \Phuture\Coherence\Dates::addSeconds()
     */
    public static function removeSeconds(DateTimeImmutable|string $date, int $seconds): DateTimeImmutable
    {
        if ($seconds < 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: The seconds value must be >= 0. Use addSeconds() to move forward in time."
            );
        }

        return self::resolveDate($date)->modify("-{$seconds} seconds");
    }

    /**
     * Removes a number of weeks from a date/time value.
     *
     * Returns a new date/time value that is the given number of weeks earlier
     * than the original. One week equals exactly 7 days.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21');
     * $result = Dates::removeWeeks($date, 1); // '2026-04-14'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $weeks The number of weeks to remove (must be >= 0)
     * @return DateTimeImmutable A new date/time value with the weeks removed
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When $weeks is negative
     * @see \Phuture\Coherence\Dates::addWeeks()
     */
    public static function removeWeeks(DateTimeImmutable|string $date, int $weeks): DateTimeImmutable
    {
        if ($weeks < 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: The weeks value must be >= 0. Use addWeeks() to move forward in time."
            );
        }

        return self::resolveDate($date)->modify("-{$weeks} weeks");
    }

    /**
     * Removes a number of years from a date/time value.
     *
     * Returns a new date/time value that is the given number of years earlier
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21');
     * $result = Dates::removeYears($date, 10); // '2016-04-21'
     * ```
     *
     * @param DateTimeImmutable|string $date The starting date/time value
     * @param int $years The number of years to remove (must be >= 0)
     * @return DateTimeImmutable A new date/time value with the years removed
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When $years is negative
     * @see \Phuture\Coherence\Dates::addYears()
     */
    public static function removeYears(DateTimeImmutable|string $date, int $years): DateTimeImmutable
    {
        if ($years < 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: The years value must be >= 0. Use addYears() to move forward in time."
            );
        }

        return self::resolveDate($date)->modify("-{$years} years");
    }

    /**
     * Returns a new date/time value set to the very start of its day (00:00:00).
     *
     * Keeps the same date and timezone but resets the time to midnight.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:45');
     * $result = Dates::startOfDay($date); // '2026-04-21 00:00:00'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at midnight on the same calendar day
     * @see \Phuture\Coherence\Dates::endOfDay()
     */
    public static function startOfDay(DateTimeImmutable|string $date): DateTimeImmutable
    {
        return self::resolveDate($date)->setTime(0, 0, 0);
    }

    /**
     * Returns a new date/time value set to the first day of the same month at midnight.
     *
     * Resets the day to 1 and the time to 00:00:00 while preserving the year and month.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::startOfMonth($date); // '2026-04-01 00:00:00'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at the first day of the month at 00:00:00
     * @see \Phuture\Coherence\Dates::endOfMonth()
     */
    public static function startOfMonth(DateTimeImmutable|string $date): DateTimeImmutable
    {
        return self::resolveDate($date)->modify('first day of this month')->setTime(0, 0, 0);
    }

    /**
     * Returns a new date/time value set to the Monday of the same ISO week at midnight.
     *
     * The ISO week starts on Monday. The time is reset to 00:00:00.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21'); // Tuesday
     * $result = Dates::startOfWeek($date); // '2026-04-20 00:00:00' (Monday)
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at Monday 00:00:00 of the same week
     * @see \Phuture\Coherence\Dates::endOfWeek()
     */
    public static function startOfWeek(DateTimeImmutable|string $date): DateTimeImmutable
    {
        return self::resolveDate($date)->modify('Monday this week')->setTime(0, 0, 0);
    }

    /**
     * Returns a new date/time value set to January 1st of the same year at midnight.
     *
     * Resets the month and day to January 1 and the time to 00:00:00.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-09-15');
     * $result = Dates::startOfYear($date); // '2026-01-01 00:00:00'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at January 1st of the same year at 00:00:00
     * @see \Phuture\Coherence\Dates::endOfYear()
     */
    public static function startOfYear(DateTimeImmutable|string $date): DateTimeImmutable
    {
        return self::resolveDate($date)->modify('first day of January this year')->setTime(0, 0, 0);
    }

    /**
     * Returns the date portion of a date/time value as a string in Y-m-d format.
     *
     * Extracts only the year, month, and day from the given date/time value,
     * dropping any time information.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00');
     * Dates::toDate($date); // '2026-04-21'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to convert
     * @return string The date portion formatted as 'Y-m-d' (e.g. '2026-04-21')
     * @see \Phuture\Coherence\Dates::toTime()
     * @see \Phuture\Coherence\Dates::toDateTime()
     */
    public static function toDate(DateTimeImmutable|string $date): string
    {
        return self::resolveDate($date)->format('Y-m-d');
    }

    /**
     * Returns a date/time value as a combined date and time string.
     *
     * Formats the date/time as a human-readable string containing both
     * the date and time components separated by a space.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00');
     * Dates::toDateTime($date); // '2026-04-21 14:30:00'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to convert
     * @return string The date and time formatted as 'Y-m-d H:i:s' (e.g. '2026-04-21 14:30:00')
     * @see \Phuture\Coherence\Dates::toDate()
     * @see \Phuture\Coherence\Dates::toTime()
     */
    public static function toDateTime(DateTimeImmutable|string $date): string
    {
        return self::resolveDate($date)->format('Y-m-d H:i:s');
    }

    /**
     * Returns a date/time value formatted as an ISO 8601 string.
     *
     * ISO 8601 is an international standard for representing dates and times.
     * The output includes timezone offset information, making it ideal for
     * data exchange between systems (APIs, JSON payloads, etc.).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00', 'America/New_York');
     * Dates::toIso8601($date); // '2026-04-21T14:30:00-04:00'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to convert
     * @return string The date and time formatted according to ISO 8601 (e.g. '2026-04-21T14:30:00+00:00')
     * @see \Phuture\Coherence\Dates::toRfc2822()
     */
    public static function toIso8601(DateTimeImmutable|string $date): string
    {
        return self::resolveDate($date)->format(DateTimeInterface::ATOM);
    }

    /**
     * Returns a date/time value formatted as an RFC 1036 string.
     *
     * RFC 1036 is the standard format used in Usenet news messages (NNTP).
     * It produces a string like "Tue, 21 Apr 26 14:30:00 +0000" with a two-digit year,
     * similar to RFC 822 but used specifically in news article headers.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
     * Dates::toRfc1036($date); // 'Tue, 21 Apr 26 14:30:00 +0000'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to convert
     * @return string The date and time formatted according to RFC 1036
     * @see \Phuture\Coherence\Dates::toRfc2822()
     */
    public static function toRfc1036(DateTimeImmutable|string $date): string
    {
        return self::resolveDate($date)->format(DateTimeInterface::RFC1036);
    }

    /**
     * Returns a date/time value formatted as an RFC 1123 string.
     *
     * RFC 1123 is the standard format for HTTP date headers.
     * It produces a string like "Tue, 21 Apr 2026 14:30:00 +0000" with a four-digit year,
     * essentially the same as RFC 2822 but requiring a four-digit year.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
     * Dates::toRfc1123($date); // 'Tue, 21 Apr 2026 14:30:00 +0000'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to convert
     * @return string The date and time formatted according to RFC 1123
     * @see \Phuture\Coherence\Dates::toRfc2822()
     */
    public static function toRfc1123(DateTimeImmutable|string $date): string
    {
        return self::resolveDate($date)->format(DateTimeInterface::RFC1123);
    }

    /**
     * Returns a date/time value formatted as an RFC 2822 string.
     *
     * RFC 2822 is the standard format used in email headers and HTTP dates.
     * The output always includes the three-letter day name, day of the month,
     * three-letter month abbreviation, four-digit year, time, and timezone offset.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
     * Dates::toRfc2822($date); // 'Tue, 21 Apr 2026 14:30:00 +0000'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to convert
     * @return string The date and time formatted according to RFC 2822
     * @see \Phuture\Coherence\Dates::toIso8601()
     */
    public static function toRfc2822(DateTimeImmutable|string $date): string
    {
        return self::resolveDate($date)->format(DateTimeInterface::RFC2822);
    }

    /**
     * Returns a date/time value formatted as an RFC 7231 string.
     *
     * RFC 7231 is the current standard for HTTP/1.1 date headers.
     * It produces a string like "Tue, 21 Apr 2026 14:30:00 GMT" using the preferred
     * IMF-fixdate format with "GMT" as the fixed timezone indicator. The input date
     * is automatically converted to GMT before formatting.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00', 'America/New_York');
     * Dates::toRfc7231($date); // 'Tue, 21 Apr 2026 18:30:00 GMT'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to convert
     * @return string The date and time formatted according to RFC 7231 (IMF-fixdate)
     * @see \Phuture\Coherence\Dates::toRfc1123()
     */
    public static function toRfc7231(DateTimeImmutable|string $date): string
    {
        return self::resolveDate($date)->setTimezone(new DateTimeZone('GMT'))->format('D, d M Y H:i:s \G\M\T');
    }

    /**
     * Returns a date/time value formatted as an RFC 822 string.
     *
     * RFC 822 is the original standard for date and time in email messages.
     * It produces a string like "Tue, 21 Apr 26 14:30:00 +0000" with a two-digit year.
     * For most modern use cases, RFC 2822 (four-digit year) is preferred.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
     * Dates::toRfc822($date); // 'Tue, 21 Apr 26 14:30:00 +0000'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to convert
     * @return string The date and time formatted according to RFC 822
     * @see \Phuture\Coherence\Dates::toRfc2822()
     */
    public static function toRfc822(DateTimeImmutable|string $date): string
    {
        return self::resolveDate($date)->format(DateTimeInterface::RFC822);
    }

    /**
     * Returns a date/time value formatted as an RFC 850 string.
     *
     * RFC 850 is a format used in some older systems and protocols.
     * It produces a string like "Tuesday, 21-Apr-26 14:30:00 UTC" with the full
     * day name and a two-digit year.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
     * Dates::toRfc850($date); // 'Tuesday, 21-Apr-26 14:30:00 UTC'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to convert
     * @return string The date and time formatted according to RFC 850
     * @see \Phuture\Coherence\Dates::toRfc2822()
     */
    public static function toRfc850(DateTimeImmutable|string $date): string
    {
        return self::resolveDate($date)->format(DateTimeInterface::RFC850);
    }

    /**
     * Returns the time portion of a date/time value as a string in H:i:s format.
     *
     * Extracts only the hour, minute, and second from the given date/time value,
     * dropping any date information.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00');
     * Dates::toTime($date); // '14:30:00'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to convert
     * @return string The time portion formatted as 'H:i:s' (e.g. '14:30:00')
     * @see \Phuture\Coherence\Dates::toDate()
     * @see \Phuture\Coherence\Dates::toDateTime()
     */
    public static function toTime(DateTimeImmutable|string $date): string
    {
        return self::resolveDate($date)->format('H:i:s');
    }

    /**
     * Returns a human-readable string describing how far a date is from a reference point.
     *
     * Produces a relative time string like '2 days ago', 'in 3 hours', or 'just now'.
     * The comparison point defaults to the current moment. The output automatically
     * picks the largest whole unit that fits (seconds, minutes, hours, days, weeks,
     * months, or years) and uses singular or plural form.
     *
     * Output format for past dates: '{n} {unit} ago' (e.g. '5 minutes ago')
     * Output format for future dates: 'in {n} {unit}' (e.g. 'in 2 days')
     * Output for very recent dates: 'just now'
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
     *
     * Dates::toRelative('2026-04-21 11:55:00', $ref); // '5 minutes ago'
     * Dates::toRelative('2026-04-21 12:45:00', $ref); // 'in 45 minutes'
     * Dates::toRelative('2026-04-19 12:00:00', $ref); // '2 days ago'
     * Dates::toRelative('2026-04-21 12:00:30', $ref); // 'just now'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to describe
     * @param DateTimeImmutable|string|null $comparedTo The reference date/time to compare against.
     *   Pass null to use the current moment (default: null)
     * @return string A human-readable relative time string
     * @see \Phuture\Coherence\Dates::parseRelative()
     */
    public static function toRelative(
        DateTimeImmutable|string $date,
        DateTimeImmutable|string|null $comparedTo = null
    ): string {
        $resolvedDate = self::resolveDate($date);
        $resolvedComparedTo = $comparedTo !== null
            ? self::resolveDate($comparedTo)
            : self::now($resolvedDate->getTimezone()->getName());

        $diffSeconds = $resolvedComparedTo->getTimestamp() - $resolvedDate->getTimestamp();
        $isFuture = $diffSeconds < 0;
        $absoluteSeconds = abs($diffSeconds);

        if ($absoluteSeconds < 60) {
            return 'just now';
        }

        $thresholds = self::relativeThresholds();

        foreach ($thresholds as $threshold) {
            if ($absoluteSeconds >= $threshold['minimum']) {
                $value = (int) floor($absoluteSeconds / $threshold['divisor']);

                return self::formatRelativeString($value, $threshold['unit'], $isFuture);
            }
        }

        $value = (int) floor($absoluteSeconds / 31536000);

        return self::formatRelativeString($value, 'year', $isFuture);
    }

    /**
     * The Unix timestamp is the number of seconds elapsed since
     * 1 January 1970 00:00:00 UTC, regardless of timezone.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 00:00:00', 'UTC');
     * Dates::toTimestamp($date); // 1745193600
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to convert
     * @return int The number of seconds since the Unix epoch (1970-01-01 00:00:00 UTC)
     * @see \Phuture\Coherence\Dates::fromTimestamp()
     */
    public static function toTimestamp(DateTimeImmutable|string $date): int
    {
        return self::resolveDate($date)->getTimestamp();
    }

    /**
     * Converts a date/time value to a different timezone.
     *
     * The underlying point in time remains exactly the same — only the timezone
     * context used to display it changes. Useful when you need to present a UTC
     * timestamp in a user's local timezone.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $utc = Dates::parse('2026-04-21 12:00:00', 'UTC');
     * $ny = Dates::toTimezone($utc, 'America/New_York');
     * // $ny displays as '2026-04-21 08:00:00' but represents the same moment
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to convert
     * @param string $timezone A valid PHP timezone identifier to convert into
     * @return DateTimeImmutable A new date/time value in the requested timezone
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the timezone string is invalid
     * @see \Phuture\Coherence\Dates::getTimezone()
     */
    public static function toTimezone(DateTimeImmutable|string $date, string $timezone): DateTimeImmutable
    {
        return self::resolveDate($date)->setTimezone(self::buildTimezone($timezone));
    }

    /**
     * Returns a date/time value formatted as a W3C string.
     *
     * The W3C format is a simplified subset of ISO 8601 commonly used in
     * HTML documents, XML schemas, and web APIs. It produces a string like
     * "2026-04-21T14:30:00+00:00" with the timezone offset included.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
     * Dates::toW3c($date); // '2026-04-21T14:30:00+00:00'
     * ```
     *
     * @param DateTimeImmutable|string $date The date/time value to convert
     * @return string The date and time formatted according to the W3C standard
     * @see \Phuture\Coherence\Dates::toIso8601()
     */
    public static function toW3c(DateTimeImmutable|string $date): string
    {
        return self::resolveDate($date)->format(DateTimeInterface::W3C);
    }

    /**
     * Builds a DateTimeZone from a timezone string, or returns the system default timezone.
     *
     * @param string|null $timezone A valid PHP timezone identifier, or null for the system default
     * @return DateTimeZone The resolved timezone object
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the timezone string is not a valid identifier
     */
    private static function buildTimezone(?string $timezone): DateTimeZone
    {
        if ($timezone === null || $timezone === '') {
            return new DateTimeZone('UTC');
        }

        if (!in_array($timezone, timezone_identifiers_list(), true)) {
            throw new InvalidArgumentException(
                "Invalid Argument: The timezone \"{$timezone}\" is not a valid PHP timezone identifier."
            );
        }

        return new DateTimeZone($timezone);
    }

    /**
     * Converts a day.js-style format string to PHP native date() format characters.
     *
     * @param string $dayJsFormat The format string using day.js-style tokens
     * @return string The equivalent format string using PHP date() characters
     */
    private static function convertDayJsFormatToPhp(string $dayJsFormat): string
    {
        $escaped = [];
        $working = preg_replace_callback('/\[([^\]]*)\]/', function ($matches) use (&$escaped) {
            $placeholder = "\x00ESC" . count($escaped) . "\x00";
            $escaped[] = $matches[1];

            return $placeholder;
        }, $dayJsFormat);

        $conversions = [
            'YYYY' => 'Y',
            'YY' => 'y',
            'MMMM' => 'F',
            'MMM' => 'M',
            'MM' => 'm',
            'M' => 'n',
            'DD' => 'd',
            'D' => 'j',
            'dddd' => 'l',
            'ddd' => 'D',
            'dd' => self::MARKER_SHORT_DAY_NAME,
            'd' => self::MARKER_DAY_OF_WEEK,
            'HH' => 'H',
            'H' => 'G',
            'hh' => 'h',
            'h' => 'g',
            'mm' => 'i',
            'm' => self::MARKER_MINUTES_NO_PAD,
            'ss' => 's',
            's' => self::MARKER_SECONDS_NO_PAD,
            'SSS' => 'v',
            'ZZ' => 'O',
            'Z' => 'P',
            'A' => 'A',
            'a' => 'a',
        ];

        $result = strtr($working, $conversions);

        $phpFormatChars = array_flip(str_split('dDjlNwWFS mMntLoYy aABgGhHiseIOPTZcrUuv'));

        foreach ($escaped as $index => $text) {
            $escapedText = '';
            $length = strlen($text);
            for ($i = 0; $i < $length; $i++) {
                if (isset($phpFormatChars[$text[$i]])) {
                    $escapedText .= '\\' . $text[$i];
                } else {
                    $escapedText .= $text[$i];
                }
            }
            $result = str_replace("\x00ESC{$index}\x00", $escapedText, $result);
        }

        return $result;
    }

    /**
     * Determines whether a format string contains day.js-style multi-character tokens.
     *
     * @param string $format The format string to inspect
     * @return bool Returns true if the format contains day.js tokens or bracket escapes
     */
    private static function isDayJsFormat(string $format): bool
    {
        if (str_contains($format, '[')) {
            return true;
        }

        return preg_match('/YYYY|MMMM|dddd|SSS|MMM|ddd|YY|MM|DD|dd|HH|hh|mm|ss|ZZ/', $format) === 1;
    }

    /**
     * Resolves a DateTimeImmutable|string argument to a DateTimeImmutable instance.
     *
     * @param DateTimeImmutable|string $date A DateTimeImmutable instance or a parseable date string
     * @return DateTimeImmutable The resolved date/time value
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the string cannot be parsed
     */
    private static function resolveDate(DateTimeImmutable|string $date): DateTimeImmutable
    {
        if (is_string($date)) {
            return self::parse($date);
        }

        return $date;
    }

    /**
     * Resolves the base date for parseRelative from the $relativeTo parameter.
     *
     * @param DateTimeImmutable|string|null $relativeTo The user-supplied reference date
     * @param string|null $timezone Timezone used when $relativeTo is null
     * @return DateTimeImmutable The resolved reference date
     */
    private static function resolveRelativeBaseDate(
        DateTimeImmutable|string|null $relativeTo,
        ?string $timezone
    ): DateTimeImmutable {
        if ($relativeTo === null) {
            return self::now($timezone);
        }

        return self::resolveDate($relativeTo);
    }

    private static function normalizeRelativeExpression(string $expression): string
    {
        $trimmed = trim($expression);

        $pattern = '/^in\s+(\d+)\s+(seconds?|minutes?|hours?|days?|weeks?|months?|years?)$/i';

        if (preg_match($pattern, $trimmed, $matches)) {
            return "+{$matches[1]} {$matches[2]}";
        }

        return $trimmed;
    }

    /**
     * Returns the ordered threshold definitions used by toRelative.
     *
     * Each entry maps a time unit to its divisor (in seconds) and the minimum
     * number of seconds that must have elapsed before that unit is chosen.
     * Ordered from largest unit to smallest so the first match wins.
     *
     * @return array<int, array{unit: string, divisor: int, minimum: int}>
     */
    private static function relativeThresholds(): array
    {
        return [
            ['unit' => 'year', 'divisor' => 31536000, 'minimum' => 31536000],
            ['unit' => 'month', 'divisor' => 2592000, 'minimum' => 2592000],
            ['unit' => 'week', 'divisor' => 604800, 'minimum' => 604800],
            ['unit' => 'day', 'divisor' => 86400, 'minimum' => 86400],
            ['unit' => 'hour', 'divisor' => 3600, 'minimum' => 3600],
            ['unit' => 'minute', 'divisor' => 60, 'minimum' => 60],
        ];
    }

    /**
     * Formats a relative time string with proper singular/plural and direction.
     *
     * @param int $value The quantity of the time unit
     * @param string $unit The time unit name in singular form (e.g. 'day', 'hour')
     * @param bool $isFuture Whether the target date is in the future
     * @return string The formatted relative string (e.g. 'in 2 days', '5 hours ago')
     */
    private static function formatRelativeString(int $value, string $unit, bool $isFuture): string
    {
        $unitLabel = $value === 1 ? $unit : $unit . 's';
        $formatted = "{$value} {$unitLabel}";

        return $isFuture ? "in {$formatted}" : "{$formatted} ago";
    }
}
