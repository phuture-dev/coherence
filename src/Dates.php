<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Phuture\Coherence\Exception\InvalidArgumentException;
use Phuture\Coherence\Support\StaticClass;

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
 * - **Arithmetic**: Add or subtract any unit from seconds to years
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
    // -------------------------------------------------------------------------
    // Creation
    // -------------------------------------------------------------------------

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
     * $now = Dates::now();                      // e.g. 2026-04-21 14:30:00 UTC
     * $nowInTokyo = Dates::now('Asia/Tokyo');   // same moment, Tokyo time
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
     * $date = Dates::create(2026, 12, 25);                              // Christmas midnight UTC
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
        $dateString = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second);

        return new DateTimeImmutable($dateString, self::buildTimezone($timezone));
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
        $parsed = new DateTimeImmutable($dateString, self::buildTimezone($timezone));

        return $parsed;
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
     * $date = Dates::fromTimestamp(1745236800);                          // UTC
     * $date = Dates::fromTimestamp(1745236800, 'America/Los_Angeles');   // same moment, LA time
     * ```
     *
     * @param int $timestamp The number of seconds since the Unix epoch (1970-01-01 00:00:00 UTC)
     * @param string|null $timezone A valid PHP timezone identifier for display (default: null — UTC)
     * @return DateTimeImmutable The date and time represented by the timestamp
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the timezone string is invalid
     * @see \Phuture\Coherence\Dates::toUnixTimestamp()
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
     * Creates a date/time value from a string using an explicit format pattern.
     *
     * Use this when you know the exact format of your date string and want
     * strict parsing. For example, if your source data always looks like "21/04/2026",
     * pass "d/m/Y" as the format and "21/04/2026" as the date string.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::fromFormat('d/m/Y', '21/04/2026');
     * $date = Dates::fromFormat('Y-m-d H:i:s', '2026-04-21 14:30:00', 'Europe/London');
     * ```
     *
     * @param string $format The format pattern using PHP's date format characters (e.g. 'Y-m-d')
     * @param string $dateString The date string to parse according to the format
     * @param string|null $timezone A valid PHP timezone identifier (default: null — system default)
     * @return DateTimeImmutable The parsed date and time value
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the format does not match
     *   the date string or the timezone is invalid
     * @see \Phuture\Coherence\Dates::parse()
     */
    public static function fromFormat(string $format, string $dateString, ?string $timezone = null): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat($format, $dateString, self::buildTimezone($timezone));

        if ($date === false) {
            throw new InvalidArgumentException(
                "The date string \"{$dateString}\" does not match the format \"{$format}\"."
            );
        }

        return $date;
    }

    // -------------------------------------------------------------------------
    // Timezone
    // -------------------------------------------------------------------------

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
     * $utc  = Dates::parse('2026-04-21 12:00:00', 'UTC');
     * $ny   = Dates::toTimezone($utc, 'America/New_York');
     * // $ny displays as '2026-04-21 08:00:00' but represents the same moment
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to convert
     * @param string $timezone A valid PHP timezone identifier to convert into
     * @return DateTimeImmutable A new date/time value in the requested timezone
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the timezone string is invalid
     * @see \Phuture\Coherence\Dates::getTimezone()
     */
    public static function toTimezone(DateTimeImmutable $date, string $timezone): DateTimeImmutable
    {
        return $date->setTimezone(self::buildTimezone($timezone));
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
     * $tz   = Dates::getTimezone($date); // 'Asia/Tokyo'
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to read the timezone from
     * @return string The timezone identifier string (e.g. 'Europe/Paris')
     * @see \Phuture\Coherence\Dates::toTimezone()
     */
    public static function getTimezone(DateTimeImmutable $date): string
    {
        return $date->getTimezone()->getName();
    }

    // -------------------------------------------------------------------------
    // Formatting
    // -------------------------------------------------------------------------

    /**
     * Formats a date/time value using a custom format string.
     *
     * The format string uses the same characters as PHP's date() function
     * (e.g. 'Y' for four-digit year, 'm' for two-digit month, 'd' for two-digit day).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 14:30:00');
     * Dates::format($date, 'Y-m-d');         // '2026-04-21'
     * Dates::format($date, 'd/m/Y H:i');     // '21/04/2026 14:30'
     * Dates::format($date, 'l, F j, Y');     // 'Tuesday, April 21, 2026'
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to format
     * @param string $format The output format string using PHP date() characters
     * @return string The formatted date/time string
     * @see \Phuture\Coherence\Dates::toDateString()
     * @see \Phuture\Coherence\Dates::toDateTimeString()
     */
    public static function format(DateTimeImmutable $date, string $format): string
    {
        return $date->format($format);
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
     * Dates::toDateString($date); // '2026-04-21'
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to convert
     * @return string The date portion formatted as 'Y-m-d' (e.g. '2026-04-21')
     * @see \Phuture\Coherence\Dates::toTimeString()
     * @see \Phuture\Coherence\Dates::toDateTimeString()
     */
    public static function toDateString(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d');
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
     * Dates::toTimeString($date); // '14:30:00'
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to convert
     * @return string The time portion formatted as 'H:i:s' (e.g. '14:30:00')
     * @see \Phuture\Coherence\Dates::toDateString()
     * @see \Phuture\Coherence\Dates::toDateTimeString()
     */
    public static function toTimeString(DateTimeImmutable $date): string
    {
        return $date->format('H:i:s');
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
     * Dates::toDateTimeString($date); // '2026-04-21 14:30:00'
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to convert
     * @return string The date and time formatted as 'Y-m-d H:i:s' (e.g. '2026-04-21 14:30:00')
     * @see \Phuture\Coherence\Dates::toDateString()
     * @see \Phuture\Coherence\Dates::toTimeString()
     */
    public static function toDateTimeString(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d H:i:s');
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
     * @param DateTimeImmutable $date The date/time value to convert
     * @return string The date and time formatted according to ISO 8601 (e.g. '2026-04-21T14:30:00+00:00')
     * @see \Phuture\Coherence\Dates::toRfc2822()
     */
    public static function toIso8601(DateTimeImmutable $date): string
    {
        return $date->format(DateTimeInterface::ATOM);
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
     * @param DateTimeImmutable $date The date/time value to convert
     * @return string The date and time formatted according to RFC 2822
     * @see \Phuture\Coherence\Dates::toIso8601()
     */
    public static function toRfc2822(DateTimeImmutable $date): string
    {
        return $date->format(DateTimeInterface::RFC2822);
    }

    /**
     * Returns the Unix timestamp representation of a date/time value.
     *
     * The Unix timestamp is the number of seconds elapsed since
     * 1 January 1970 00:00:00 UTC, regardless of timezone.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date = Dates::parse('2026-04-21 00:00:00', 'UTC');
     * Dates::toUnixTimestamp($date); // 1745193600
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to convert
     * @return int The number of seconds since the Unix epoch (1970-01-01 00:00:00 UTC)
     * @see \Phuture\Coherence\Dates::fromTimestamp()
     */
    public static function toUnixTimestamp(DateTimeImmutable $date): int
    {
        return $date->getTimestamp();
    }

    // -------------------------------------------------------------------------
    // Addition
    // -------------------------------------------------------------------------

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
     * $date   = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::addSeconds($date, 90); // '2026-04-21 14:31:30'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $seconds The number of seconds to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the seconds added
     * @see \Phuture\Coherence\Dates::subSeconds()
     */
    public static function addSeconds(DateTimeImmutable $date, int $seconds): DateTimeImmutable
    {
        return $date->modify("+{$seconds} seconds");
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
     * $date   = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::addMinutes($date, 45); // '2026-04-21 15:15:00'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $minutes The number of minutes to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the minutes added
     * @see \Phuture\Coherence\Dates::subMinutes()
     */
    public static function addMinutes(DateTimeImmutable $date, int $minutes): DateTimeImmutable
    {
        return $date->modify("+{$minutes} minutes");
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
     * $date   = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::addHours($date, 3); // '2026-04-21 17:30:00'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $hours The number of hours to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the hours added
     * @see \Phuture\Coherence\Dates::subHours()
     */
    public static function addHours(DateTimeImmutable $date, int $hours): DateTimeImmutable
    {
        return $date->modify("+{$hours} hours");
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
     * $date   = Dates::parse('2026-04-21');
     * $result = Dates::addDays($date, 10); // '2026-05-01'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $days The number of days to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the days added
     * @see \Phuture\Coherence\Dates::subDays()
     */
    public static function addDays(DateTimeImmutable $date, int $days): DateTimeImmutable
    {
        return $date->modify("+{$days} days");
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
     * $date   = Dates::parse('2026-04-21');
     * $result = Dates::addWeeks($date, 2); // '2026-05-05'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $weeks The number of weeks to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the weeks added
     * @see \Phuture\Coherence\Dates::subWeeks()
     */
    public static function addWeeks(DateTimeImmutable $date, int $weeks): DateTimeImmutable
    {
        return $date->modify("+{$weeks} weeks");
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
     * $date   = Dates::parse('2026-01-15');
     * $result = Dates::addMonths($date, 3); // '2026-04-15'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $months The number of months to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the months added
     * @see \Phuture\Coherence\Dates::subMonths()
     */
    public static function addMonths(DateTimeImmutable $date, int $months): DateTimeImmutable
    {
        return $date->modify("+{$months} months");
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
     * $date   = Dates::parse('2026-04-21');
     * $result = Dates::addYears($date, 5); // '2031-04-21'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $years The number of years to add (use a negative value to subtract)
     * @return DateTimeImmutable A new date/time value with the years added
     * @see \Phuture\Coherence\Dates::subYears()
     */
    public static function addYears(DateTimeImmutable $date, int $years): DateTimeImmutable
    {
        return $date->modify("+{$years} years");
    }

    // -------------------------------------------------------------------------
    // Subtraction
    // -------------------------------------------------------------------------

    /**
     * Subtracts a number of seconds from a date/time value.
     *
     * Returns a new date/time value that is the given number of seconds earlier
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date   = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::subSeconds($date, 30); // '2026-04-21 14:29:30'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $seconds The number of seconds to subtract
     * @return DateTimeImmutable A new date/time value with the seconds subtracted
     * @see \Phuture\Coherence\Dates::addSeconds()
     */
    public static function subSeconds(DateTimeImmutable $date, int $seconds): DateTimeImmutable
    {
        return $date->modify("-{$seconds} seconds");
    }

    /**
     * Subtracts a number of minutes from a date/time value.
     *
     * Returns a new date/time value that is the given number of minutes earlier
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date   = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::subMinutes($date, 15); // '2026-04-21 14:15:00'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $minutes The number of minutes to subtract
     * @return DateTimeImmutable A new date/time value with the minutes subtracted
     * @see \Phuture\Coherence\Dates::addMinutes()
     */
    public static function subMinutes(DateTimeImmutable $date, int $minutes): DateTimeImmutable
    {
        return $date->modify("-{$minutes} minutes");
    }

    /**
     * Subtracts a number of hours from a date/time value.
     *
     * Returns a new date/time value that is the given number of hours earlier
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date   = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::subHours($date, 2); // '2026-04-21 12:30:00'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $hours The number of hours to subtract
     * @return DateTimeImmutable A new date/time value with the hours subtracted
     * @see \Phuture\Coherence\Dates::addHours()
     */
    public static function subHours(DateTimeImmutable $date, int $hours): DateTimeImmutable
    {
        return $date->modify("-{$hours} hours");
    }

    /**
     * Subtracts a number of days from a date/time value.
     *
     * Returns a new date/time value that is the given number of days earlier
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date   = Dates::parse('2026-04-21');
     * $result = Dates::subDays($date, 5); // '2026-04-16'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $days The number of days to subtract
     * @return DateTimeImmutable A new date/time value with the days subtracted
     * @see \Phuture\Coherence\Dates::addDays()
     */
    public static function subDays(DateTimeImmutable $date, int $days): DateTimeImmutable
    {
        return $date->modify("-{$days} days");
    }

    /**
     * Subtracts a number of weeks from a date/time value.
     *
     * Returns a new date/time value that is the given number of weeks earlier
     * than the original. One week equals exactly 7 days.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date   = Dates::parse('2026-04-21');
     * $result = Dates::subWeeks($date, 1); // '2026-04-14'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $weeks The number of weeks to subtract
     * @return DateTimeImmutable A new date/time value with the weeks subtracted
     * @see \Phuture\Coherence\Dates::addWeeks()
     */
    public static function subWeeks(DateTimeImmutable $date, int $weeks): DateTimeImmutable
    {
        return $date->modify("-{$weeks} weeks");
    }

    /**
     * Subtracts a number of months from a date/time value.
     *
     * Returns a new date/time value that is the given number of months earlier
     * than the original. When the resulting day does not exist in the target month,
     * PHP overflows to the next month.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date   = Dates::parse('2026-06-15');
     * $result = Dates::subMonths($date, 2); // '2026-04-15'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $months The number of months to subtract
     * @return DateTimeImmutable A new date/time value with the months subtracted
     * @see \Phuture\Coherence\Dates::addMonths()
     */
    public static function subMonths(DateTimeImmutable $date, int $months): DateTimeImmutable
    {
        return $date->modify("-{$months} months");
    }

    /**
     * Subtracts a number of years from a date/time value.
     *
     * Returns a new date/time value that is the given number of years earlier
     * than the original. The original value is never modified.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date   = Dates::parse('2026-04-21');
     * $result = Dates::subYears($date, 10); // '2016-04-21'
     * ```
     *
     * @param DateTimeImmutable $date The starting date/time value
     * @param int $years The number of years to subtract
     * @return DateTimeImmutable A new date/time value with the years subtracted
     * @see \Phuture\Coherence\Dates::addYears()
     */
    public static function subYears(DateTimeImmutable $date, int $years): DateTimeImmutable
    {
        return $date->modify("-{$years} years");
    }

    // -------------------------------------------------------------------------
    // Comparison
    // -------------------------------------------------------------------------

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
     * $later   = Dates::parse('2026-12-31');
     * Dates::isBefore($earlier, $later); // true
     * Dates::isBefore($later, $earlier); // false
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to test
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return bool Returns true if $date is before $comparedTo
     * @see \Phuture\Coherence\Dates::isAfter()
     * @see \Phuture\Coherence\Dates::equals()
     */
    public static function isBefore(DateTimeImmutable $date, DateTimeImmutable $comparedTo): bool
    {
        return $date < $comparedTo;
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
     * $later   = Dates::parse('2026-12-31');
     * $earlier = Dates::parse('2026-01-01');
     * Dates::isAfter($later, $earlier); // true
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to test
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return bool Returns true if $date is after $comparedTo
     * @see \Phuture\Coherence\Dates::isBefore()
     * @see \Phuture\Coherence\Dates::equals()
     */
    public static function isAfter(DateTimeImmutable $date, DateTimeImmutable $comparedTo): bool
    {
        return $date > $comparedTo;
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
     * $ny  = Dates::parse('2026-04-21 08:00:00', 'America/New_York');
     * Dates::equals($utc, $ny); // true — same moment, different timezones
     * ```
     *
     * @param DateTimeImmutable $date The first date/time value
     * @param DateTimeImmutable $comparedTo The second date/time value
     * @return bool Returns true if both values represent the same point in time
     * @see \Phuture\Coherence\Dates::isBefore()
     * @see \Phuture\Coherence\Dates::isAfter()
     */
    public static function equals(DateTimeImmutable $date, DateTimeImmutable $comparedTo): bool
    {
        return $date->getTimestamp() === $comparedTo->getTimestamp();
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
     * $morning   = Dates::parse('2026-04-21 08:00:00');
     * $evening   = Dates::parse('2026-04-21 22:00:00');
     * $tomorrow  = Dates::parse('2026-04-22 08:00:00');
     * Dates::isSameDay($morning, $evening);  // true
     * Dates::isSameDay($morning, $tomorrow); // false
     * ```
     *
     * @param DateTimeImmutable $date The first date/time value
     * @param DateTimeImmutable $comparedTo The second date/time value
     * @return bool Returns true if both values fall on the same calendar day
     * @see \Phuture\Coherence\Dates::isSameMonth()
     * @see \Phuture\Coherence\Dates::isSameYear()
     */
    public static function isSameDay(DateTimeImmutable $date, DateTimeImmutable $comparedTo): bool
    {
        return $date->format('Y-m-d') === $comparedTo->format('Y-m-d');
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
     * $first  = Dates::parse('2026-04-01');
     * $last   = Dates::parse('2026-04-30');
     * $next   = Dates::parse('2026-05-01');
     * Dates::isSameMonth($first, $last); // true
     * Dates::isSameMonth($first, $next); // false
     * ```
     *
     * @param DateTimeImmutable $date The first date/time value
     * @param DateTimeImmutable $comparedTo The second date/time value
     * @return bool Returns true if both values fall in the same calendar month and year
     * @see \Phuture\Coherence\Dates::isSameDay()
     * @see \Phuture\Coherence\Dates::isSameYear()
     */
    public static function isSameMonth(DateTimeImmutable $date, DateTimeImmutable $comparedTo): bool
    {
        return $date->format('Y-m') === $comparedTo->format('Y-m');
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
     * $ny  = Dates::parse('2027-01-01');
     * Dates::isSameYear($jan, $dec); // true
     * Dates::isSameYear($jan, $ny);  // false
     * ```
     *
     * @param DateTimeImmutable $date The first date/time value
     * @param DateTimeImmutable $comparedTo The second date/time value
     * @return bool Returns true if both values fall in the same calendar year
     * @see \Phuture\Coherence\Dates::isSameDay()
     * @see \Phuture\Coherence\Dates::isSameMonth()
     */
    public static function isSameYear(DateTimeImmutable $date, DateTimeImmutable $comparedTo): bool
    {
        return $date->format('Y') === $comparedTo->format('Y');
    }

    // -------------------------------------------------------------------------
    // Difference
    // -------------------------------------------------------------------------

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
     * $end   = Dates::parse('2026-04-21 14:01:30');
     * Dates::diffInSeconds($start, $end); // 90
     * ```
     *
     * @param DateTimeImmutable $date The first date/time value
     * @param DateTimeImmutable $comparedTo The second date/time value
     * @return int The number of complete seconds between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInMinutes()
     * @see \Phuture\Coherence\Dates::diffInHours()
     */
    public static function diffInSeconds(DateTimeImmutable $date, DateTimeImmutable $comparedTo): int
    {
        return (int) abs($date->getTimestamp() - $comparedTo->getTimestamp());
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
     * $end   = Dates::parse('2026-04-21 15:30:00');
     * Dates::diffInMinutes($start, $end); // 90
     * ```
     *
     * @param DateTimeImmutable $date The first date/time value
     * @param DateTimeImmutable $comparedTo The second date/time value
     * @return int The number of complete minutes between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInSeconds()
     * @see \Phuture\Coherence\Dates::diffInHours()
     */
    public static function diffInMinutes(DateTimeImmutable $date, DateTimeImmutable $comparedTo): int
    {
        return (int) (self::diffInSeconds($date, $comparedTo) / 60);
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
     * $end   = Dates::parse('2026-04-21 20:30:00');
     * Dates::diffInHours($start, $end); // 12
     * ```
     *
     * @param DateTimeImmutable $date The first date/time value
     * @param DateTimeImmutable $comparedTo The second date/time value
     * @return int The number of complete hours between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInMinutes()
     * @see \Phuture\Coherence\Dates::diffInDays()
     */
    public static function diffInHours(DateTimeImmutable $date, DateTimeImmutable $comparedTo): int
    {
        return (int) (self::diffInSeconds($date, $comparedTo) / 3600);
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
     * $end   = Dates::parse('2026-04-21');
     * Dates::diffInDays($start, $end); // 20
     * ```
     *
     * @param DateTimeImmutable $date The first date/time value
     * @param DateTimeImmutable $comparedTo The second date/time value
     * @return int The number of complete days between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInHours()
     * @see \Phuture\Coherence\Dates::diffInWeeks()
     */
    public static function diffInDays(DateTimeImmutable $date, DateTimeImmutable $comparedTo): int
    {
        return (int) (self::diffInSeconds($date, $comparedTo) / 86400);
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
     * $end   = Dates::parse('2026-04-21');
     * Dates::diffInWeeks($start, $end); // 2
     * ```
     *
     * @param DateTimeImmutable $date The first date/time value
     * @param DateTimeImmutable $comparedTo The second date/time value
     * @return int The number of complete weeks between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInDays()
     * @see \Phuture\Coherence\Dates::diffInMonths()
     */
    public static function diffInWeeks(DateTimeImmutable $date, DateTimeImmutable $comparedTo): int
    {
        return (int) (self::diffInDays($date, $comparedTo) / 7);
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
     * $end   = Dates::parse('2026-04-10');
     * Dates::diffInMonths($start, $end); // 2 (not 3, because April 10 < January 15 in day)
     * ```
     *
     * @param DateTimeImmutable $date The first date/time value
     * @param DateTimeImmutable $comparedTo The second date/time value
     * @return int The number of complete months between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInWeeks()
     * @see \Phuture\Coherence\Dates::diffInYears()
     */
    public static function diffInMonths(DateTimeImmutable $date, DateTimeImmutable $comparedTo): int
    {
        $interval = $date->diff($comparedTo);

        return abs(($interval->y * 12) + $interval->m);
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
     * $end   = Dates::parse('2026-04-10');
     * Dates::diffInYears($start, $end); // 5
     * ```
     *
     * @param DateTimeImmutable $date The first date/time value
     * @param DateTimeImmutable $comparedTo The second date/time value
     * @return int The number of complete years between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInMonths()
     */
    public static function diffInYears(DateTimeImmutable $date, DateTimeImmutable $comparedTo): int
    {
        return abs($date->diff($comparedTo)->y);
    }

    // -------------------------------------------------------------------------
    // Inspection — components
    // -------------------------------------------------------------------------

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
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return int The year as a four-digit integer (e.g. 2026)
     * @see \Phuture\Coherence\Dates::getMonth()
     * @see \Phuture\Coherence\Dates::getDay()
     */
    public static function getYear(DateTimeImmutable $date): int
    {
        return (int) $date->format('Y');
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
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return int The month as an integer from 1 (January) to 12 (December)
     * @see \Phuture\Coherence\Dates::getYear()
     * @see \Phuture\Coherence\Dates::getDay()
     */
    public static function getMonth(DateTimeImmutable $date): int
    {
        return (int) $date->format('n');
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
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return int The day of the month as an integer from 1 to 31
     * @see \Phuture\Coherence\Dates::getMonth()
     * @see \Phuture\Coherence\Dates::getYear()
     */
    public static function getDay(DateTimeImmutable $date): int
    {
        return (int) $date->format('j');
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
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return int The hour as an integer from 0 to 23
     * @see \Phuture\Coherence\Dates::getMinute()
     * @see \Phuture\Coherence\Dates::getSecond()
     */
    public static function getHour(DateTimeImmutable $date): int
    {
        return (int) $date->format('G');
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
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return int The minute as an integer from 0 to 59
     * @see \Phuture\Coherence\Dates::getHour()
     * @see \Phuture\Coherence\Dates::getSecond()
     */
    public static function getMinute(DateTimeImmutable $date): int
    {
        return (int) $date->format('i');
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
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return int The second as an integer from 0 to 59
     * @see \Phuture\Coherence\Dates::getHour()
     * @see \Phuture\Coherence\Dates::getMinute()
     */
    public static function getSecond(DateTimeImmutable $date): int
    {
        return (int) $date->format('s');
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
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return int The ISO 8601 day of the week: 1 (Monday) through 7 (Sunday)
     * @see \Phuture\Coherence\Dates::getDayOfYear()
     * @see \Phuture\Coherence\Dates::isWeekend()
     */
    public static function getDayOfWeek(DateTimeImmutable $date): int
    {
        return (int) $date->format('N');
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
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return int The day of the year as an integer from 1 to 366
     * @see \Phuture\Coherence\Dates::getDayOfWeek()
     * @see \Phuture\Coherence\Dates::getWeekOfYear()
     */
    public static function getDayOfYear(DateTimeImmutable $date): int
    {
        return (int) $date->format('z') + 1;
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
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return int The ISO 8601 week number from 1 to 53
     * @see \Phuture\Coherence\Dates::getDayOfYear()
     */
    public static function getWeekOfYear(DateTimeImmutable $date): int
    {
        return (int) $date->format('W');
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
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return int The number of days in the month, from 28 to 31
     * @see \Phuture\Coherence\Dates::isLeapYear()
     */
    public static function getDaysInMonth(DateTimeImmutable $date): int
    {
        return (int) $date->format('t');
    }

    // -------------------------------------------------------------------------
    // Inspection — boolean checks
    // -------------------------------------------------------------------------

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
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return bool Returns true if the year is a leap year
     * @see \Phuture\Coherence\Dates::getDaysInMonth()
     */
    public static function isLeapYear(DateTimeImmutable $date): bool
    {
        return $date->format('L') === '1';
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
     * $today     = Dates::now();
     * $yesterday = Dates::subDays($today, 1);
     * Dates::isToday($today);     // true
     * Dates::isToday($yesterday); // false
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return bool Returns true if the date falls on today's calendar date
     * @see \Phuture\Coherence\Dates::isYesterday()
     * @see \Phuture\Coherence\Dates::isTomorrow()
     */
    public static function isToday(DateTimeImmutable $date): bool
    {
        return self::isSameDay($date, new DateTimeImmutable('today', $date->getTimezone()));
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
     * $yesterday = Dates::subDays(Dates::now(), 1);
     * Dates::isYesterday($yesterday); // true
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return bool Returns true if the date falls on yesterday's calendar date
     * @see \Phuture\Coherence\Dates::isToday()
     * @see \Phuture\Coherence\Dates::isTomorrow()
     */
    public static function isYesterday(DateTimeImmutable $date): bool
    {
        return self::isSameDay($date, new DateTimeImmutable('yesterday', $date->getTimezone()));
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
     * $tomorrow = Dates::addDays(Dates::now(), 1);
     * Dates::isTomorrow($tomorrow); // true
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return bool Returns true if the date falls on tomorrow's calendar date
     * @see \Phuture\Coherence\Dates::isToday()
     * @see \Phuture\Coherence\Dates::isYesterday()
     */
    public static function isTomorrow(DateTimeImmutable $date): bool
    {
        return self::isSameDay($date, new DateTimeImmutable('tomorrow', $date->getTimezone()));
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
     * $past   = Dates::parse('2020-01-01');
     * $future = Dates::parse('2030-01-01');
     * Dates::isPast($past);   // true
     * Dates::isPast($future); // false
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return bool Returns true if the date is before the current moment
     * @see \Phuture\Coherence\Dates::isFuture()
     */
    public static function isPast(DateTimeImmutable $date): bool
    {
        return $date < new DateTimeImmutable('now', $date->getTimezone());
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
     * $past   = Dates::parse('2020-01-01');
     * Dates::isFuture($future); // true
     * Dates::isFuture($past);   // false
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return bool Returns true if the date is after the current moment
     * @see \Phuture\Coherence\Dates::isPast()
     */
    public static function isFuture(DateTimeImmutable $date): bool
    {
        return $date > new DateTimeImmutable('now', $date->getTimezone());
    }

    /**
     * Checks whether a date/time value falls on a weekend (Saturday or Sunday).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $saturday = Dates::parse('2026-04-18'); // Saturday
     * $tuesday  = Dates::parse('2026-04-21'); // Tuesday
     * Dates::isWeekend($saturday); // true
     * Dates::isWeekend($tuesday);  // false
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return bool Returns true if the date falls on a Saturday or Sunday
     * @see \Phuture\Coherence\Dates::isWeekday()
     * @see \Phuture\Coherence\Dates::getDayOfWeek()
     */
    public static function isWeekend(DateTimeImmutable $date): bool
    {
        $dayOfWeek = self::getDayOfWeek($date);

        return $dayOfWeek === 6 || $dayOfWeek === 7;
    }

    /**
     * Checks whether a date/time value falls on a weekday (Monday through Friday).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $tuesday  = Dates::parse('2026-04-21'); // Tuesday
     * $saturday = Dates::parse('2026-04-18'); // Saturday
     * Dates::isWeekday($tuesday);  // true
     * Dates::isWeekday($saturday); // false
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to inspect
     * @return bool Returns true if the date falls on Monday through Friday
     * @see \Phuture\Coherence\Dates::isWeekend()
     * @see \Phuture\Coherence\Dates::getDayOfWeek()
     */
    public static function isWeekday(DateTimeImmutable $date): bool
    {
        return !self::isWeekend($date);
    }

    // -------------------------------------------------------------------------
    // Boundaries
    // -------------------------------------------------------------------------

    /**
     * Returns a new date/time value set to the very start of its day (00:00:00).
     *
     * Keeps the same date and timezone but resets the time to midnight.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Dates;
     *
     * $date   = Dates::parse('2026-04-21 14:30:45');
     * $result = Dates::startOfDay($date); // '2026-04-21 00:00:00'
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at midnight on the same calendar day
     * @see \Phuture\Coherence\Dates::endOfDay()
     */
    public static function startOfDay(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->setTime(0, 0, 0);
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
     * $date   = Dates::parse('2026-04-21 14:30:45');
     * $result = Dates::endOfDay($date); // '2026-04-21 23:59:59'
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at 23:59:59 on the same calendar day
     * @see \Phuture\Coherence\Dates::startOfDay()
     */
    public static function endOfDay(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->setTime(23, 59, 59);
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
     * $date   = Dates::parse('2026-04-21'); // Tuesday
     * $result = Dates::startOfWeek($date); // '2026-04-20 00:00:00' (Monday)
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at Monday 00:00:00 of the same week
     * @see \Phuture\Coherence\Dates::endOfWeek()
     */
    public static function startOfWeek(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->modify('Monday this week')->setTime(0, 0, 0);
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
     * $date   = Dates::parse('2026-04-21'); // Tuesday
     * $result = Dates::endOfWeek($date); // '2026-04-26 23:59:59' (Sunday)
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at Sunday 23:59:59 of the same week
     * @see \Phuture\Coherence\Dates::startOfWeek()
     */
    public static function endOfWeek(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->modify('Sunday this week')->setTime(23, 59, 59);
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
     * $date   = Dates::parse('2026-04-21 14:30:00');
     * $result = Dates::startOfMonth($date); // '2026-04-01 00:00:00'
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at the first day of the month at 00:00:00
     * @see \Phuture\Coherence\Dates::endOfMonth()
     */
    public static function startOfMonth(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->modify('first day of this month')->setTime(0, 0, 0);
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
     * $date   = Dates::parse('2026-04-15');
     * $result = Dates::endOfMonth($date); // '2026-04-30 23:59:59'
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at the last day of the month at 23:59:59
     * @see \Phuture\Coherence\Dates::startOfMonth()
     */
    public static function endOfMonth(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->modify('last day of this month')->setTime(23, 59, 59);
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
     * $date   = Dates::parse('2026-09-15');
     * $result = Dates::startOfYear($date); // '2026-01-01 00:00:00'
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at January 1st of the same year at 00:00:00
     * @see \Phuture\Coherence\Dates::endOfYear()
     */
    public static function startOfYear(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->modify('first day of January this year')->setTime(0, 0, 0);
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
     * $date   = Dates::parse('2026-04-21');
     * $result = Dates::endOfYear($date); // '2026-12-31 23:59:59'
     * ```
     *
     * @param DateTimeImmutable $date The date/time value to adjust
     * @return DateTimeImmutable A new date/time value at December 31st of the same year at 23:59:59
     * @see \Phuture\Coherence\Dates::startOfYear()
     */
    public static function endOfYear(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->modify('last day of December this year')->setTime(23, 59, 59);
    }

    // -------------------------------------------------------------------------
    // Fluent entry point
    // -------------------------------------------------------------------------

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
     *     ->toDateTimeString();
     * // '2026-05-01 00:00:00'
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

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Builds a DateTimeZone from a timezone string, or returns the system default timezone.
     *
     * @param string|null $timezone A valid PHP timezone identifier, or null for the system default
     * @return DateTimeZone The resolved timezone object
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the timezone string is not a valid identifier
     */
    private static function buildTimezone(?string $timezone): DateTimeZone
    {
        if ($timezone === null) {
            return new DateTimeZone(date_default_timezone_get());
        }

        if (!in_array($timezone, timezone_identifiers_list(), true)) {
            throw new InvalidArgumentException(
                "The timezone \"{$timezone}\" is not a valid PHP timezone identifier."
            );
        }

        return new DateTimeZone($timezone);
    }
}
