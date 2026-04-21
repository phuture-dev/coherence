<?php

declare(strict_types=1);

namespace Phuture\Coherence\Type;

use DateTimeImmutable;
use Phuture\Coherence\Dates as Transformer;
use Phuture\Coherence\Support\FluentClass;

/**
 * A fluent, chainable wrapper around the Dates utility class for date and time manipulation.
 *
 * Each method delegates to the corresponding static method on `\Phuture\Coherence\Dates`,
 * stores the resulting `DateTimeImmutable` internally, and returns `$this` to enable
 * method chaining. Retrieve the final value by calling `get()`, `toDateTimeString()`,
 * or any other terminal method.
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
 *
 * $formatted = Dates::of('2026-12-25', 'America/New_York')
 *     ->addHours(9)
 *     ->toIso8601();
 * // '2026-12-25T09:00:00-05:00'
 * ```
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Dates extends FluentClass
{
    // -------------------------------------------------------------------------
    // Timezone
    // -------------------------------------------------------------------------

    /**
     * Converts the wrapped date/time to a different timezone.
     *
     * @param string $timezone A valid PHP timezone identifier (e.g. 'America/New_York')
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::toTimezone()
     */
    public function toTimezone(string $timezone): self
    {
        $this->data = Transformer::toTimezone($this->data, $timezone);

        return $this;
    }

    // -------------------------------------------------------------------------
    // Addition
    // -------------------------------------------------------------------------

    /**
     * Adds a number of seconds to the wrapped date/time value.
     *
     * @param int $seconds The number of seconds to add
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::addSeconds()
     */
    public function addSeconds(int $seconds): self
    {
        $this->data = Transformer::addSeconds($this->data, $seconds);

        return $this;
    }

    /**
     * Adds a number of minutes to the wrapped date/time value.
     *
     * @param int $minutes The number of minutes to add
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::addMinutes()
     */
    public function addMinutes(int $minutes): self
    {
        $this->data = Transformer::addMinutes($this->data, $minutes);

        return $this;
    }

    /**
     * Adds a number of hours to the wrapped date/time value.
     *
     * @param int $hours The number of hours to add
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::addHours()
     */
    public function addHours(int $hours): self
    {
        $this->data = Transformer::addHours($this->data, $hours);

        return $this;
    }

    /**
     * Adds a number of days to the wrapped date/time value.
     *
     * @param int $days The number of days to add
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::addDays()
     */
    public function addDays(int $days): self
    {
        $this->data = Transformer::addDays($this->data, $days);

        return $this;
    }

    /**
     * Adds a number of weeks to the wrapped date/time value.
     *
     * @param int $weeks The number of weeks to add
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::addWeeks()
     */
    public function addWeeks(int $weeks): self
    {
        $this->data = Transformer::addWeeks($this->data, $weeks);

        return $this;
    }

    /**
     * Adds a number of months to the wrapped date/time value.
     *
     * @param int $months The number of months to add
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::addMonths()
     */
    public function addMonths(int $months): self
    {
        $this->data = Transformer::addMonths($this->data, $months);

        return $this;
    }

    /**
     * Adds a number of years to the wrapped date/time value.
     *
     * @param int $years The number of years to add
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::addYears()
     */
    public function addYears(int $years): self
    {
        $this->data = Transformer::addYears($this->data, $years);

        return $this;
    }

    // -------------------------------------------------------------------------
    // Subtraction
    // -------------------------------------------------------------------------

    /**
     * Subtracts a number of seconds from the wrapped date/time value.
     *
     * @param int $seconds The number of seconds to subtract
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::subSeconds()
     */
    public function subSeconds(int $seconds): self
    {
        $this->data = Transformer::subSeconds($this->data, $seconds);

        return $this;
    }

    /**
     * Subtracts a number of minutes from the wrapped date/time value.
     *
     * @param int $minutes The number of minutes to subtract
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::subMinutes()
     */
    public function subMinutes(int $minutes): self
    {
        $this->data = Transformer::subMinutes($this->data, $minutes);

        return $this;
    }

    /**
     * Subtracts a number of hours from the wrapped date/time value.
     *
     * @param int $hours The number of hours to subtract
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::subHours()
     */
    public function subHours(int $hours): self
    {
        $this->data = Transformer::subHours($this->data, $hours);

        return $this;
    }

    /**
     * Subtracts a number of days from the wrapped date/time value.
     *
     * @param int $days The number of days to subtract
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::subDays()
     */
    public function subDays(int $days): self
    {
        $this->data = Transformer::subDays($this->data, $days);

        return $this;
    }

    /**
     * Subtracts a number of weeks from the wrapped date/time value.
     *
     * @param int $weeks The number of weeks to subtract
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::subWeeks()
     */
    public function subWeeks(int $weeks): self
    {
        $this->data = Transformer::subWeeks($this->data, $weeks);

        return $this;
    }

    /**
     * Subtracts a number of months from the wrapped date/time value.
     *
     * @param int $months The number of months to subtract
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::subMonths()
     */
    public function subMonths(int $months): self
    {
        $this->data = Transformer::subMonths($this->data, $months);

        return $this;
    }

    /**
     * Subtracts a number of years from the wrapped date/time value.
     *
     * @param int $years The number of years to subtract
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::subYears()
     */
    public function subYears(int $years): self
    {
        $this->data = Transformer::subYears($this->data, $years);

        return $this;
    }

    // -------------------------------------------------------------------------
    // Boundaries
    // -------------------------------------------------------------------------

    /**
     * Moves the wrapped date/time to midnight (00:00:00) on the same calendar day.
     *
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::startOfDay()
     */
    public function startOfDay(): self
    {
        $this->data = Transformer::startOfDay($this->data);

        return $this;
    }

    /**
     * Moves the wrapped date/time to 23:59:59 on the same calendar day.
     *
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::endOfDay()
     */
    public function endOfDay(): self
    {
        $this->data = Transformer::endOfDay($this->data);

        return $this;
    }

    /**
     * Moves the wrapped date/time to Monday 00:00:00 of the same ISO week.
     *
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::startOfWeek()
     */
    public function startOfWeek(): self
    {
        $this->data = Transformer::startOfWeek($this->data);

        return $this;
    }

    /**
     * Moves the wrapped date/time to Sunday 23:59:59 of the same ISO week.
     *
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::endOfWeek()
     */
    public function endOfWeek(): self
    {
        $this->data = Transformer::endOfWeek($this->data);

        return $this;
    }

    /**
     * Moves the wrapped date/time to the first day of the same month at 00:00:00.
     *
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::startOfMonth()
     */
    public function startOfMonth(): self
    {
        $this->data = Transformer::startOfMonth($this->data);

        return $this;
    }

    /**
     * Moves the wrapped date/time to the last day of the same month at 23:59:59.
     *
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::endOfMonth()
     */
    public function endOfMonth(): self
    {
        $this->data = Transformer::endOfMonth($this->data);

        return $this;
    }

    /**
     * Moves the wrapped date/time to January 1st of the same year at 00:00:00.
     *
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::startOfYear()
     */
    public function startOfYear(): self
    {
        $this->data = Transformer::startOfYear($this->data);

        return $this;
    }

    /**
     * Moves the wrapped date/time to December 31st of the same year at 23:59:59.
     *
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::endOfYear()
     */
    public function endOfYear(): self
    {
        $this->data = Transformer::endOfYear($this->data);

        return $this;
    }

    // -------------------------------------------------------------------------
    // Terminal — formatting
    // -------------------------------------------------------------------------

    /**
     * Formats the wrapped date/time value using a custom format string.
     *
     * @param string $format The output format string using PHP date() characters
     * @return string The formatted date/time string
     * @see \Phuture\Coherence\Dates::format()
     */
    public function format(string $format): string
    {
        return Transformer::format($this->data, $format);
    }

    /**
     * Returns the wrapped date/time as a 'Y-m-d' string.
     *
     * @return string The date portion formatted as 'Y-m-d'
     * @see \Phuture\Coherence\Dates::toDateString()
     */
    public function toDateString(): string
    {
        return Transformer::toDateString($this->data);
    }

    /**
     * Returns the wrapped date/time as an 'H:i:s' string.
     *
     * @return string The time portion formatted as 'H:i:s'
     * @see \Phuture\Coherence\Dates::toTimeString()
     */
    public function toTimeString(): string
    {
        return Transformer::toTimeString($this->data);
    }

    /**
     * Returns the wrapped date/time as a 'Y-m-d H:i:s' string.
     *
     * @return string The date and time formatted as 'Y-m-d H:i:s'
     * @see \Phuture\Coherence\Dates::toDateTimeString()
     */
    public function toDateTimeString(): string
    {
        return Transformer::toDateTimeString($this->data);
    }

    /**
     * Returns the wrapped date/time as an ISO 8601 string.
     *
     * @return string The date and time formatted according to ISO 8601
     * @see \Phuture\Coherence\Dates::toIso8601()
     */
    public function toIso8601(): string
    {
        return Transformer::toIso8601($this->data);
    }

    /**
     * Returns the wrapped date/time as an RFC 2822 string.
     *
     * @return string The date and time formatted according to RFC 2822
     * @see \Phuture\Coherence\Dates::toRfc2822()
     */
    public function toRfc2822(): string
    {
        return Transformer::toRfc2822($this->data);
    }

    /**
     * Returns the Unix timestamp of the wrapped date/time value.
     *
     * @return int The number of seconds since the Unix epoch
     * @see \Phuture\Coherence\Dates::toUnixTimestamp()
     */
    public function toUnixTimestamp(): int
    {
        return Transformer::toUnixTimestamp($this->data);
    }

    // -------------------------------------------------------------------------
    // Terminal — inspection
    // -------------------------------------------------------------------------

    /**
     * Returns the four-digit year of the wrapped date/time value.
     *
     * @return int The year as a four-digit integer
     * @see \Phuture\Coherence\Dates::getYear()
     */
    public function getYear(): int
    {
        return Transformer::getYear($this->data);
    }

    /**
     * Returns the month number of the wrapped date/time value.
     *
     * @return int The month from 1 (January) to 12 (December)
     * @see \Phuture\Coherence\Dates::getMonth()
     */
    public function getMonth(): int
    {
        return Transformer::getMonth($this->data);
    }

    /**
     * Returns the day of the month of the wrapped date/time value.
     *
     * @return int The day from 1 to 31
     * @see \Phuture\Coherence\Dates::getDay()
     */
    public function getDay(): int
    {
        return Transformer::getDay($this->data);
    }

    /**
     * Returns the hour of the wrapped date/time value.
     *
     * @return int The hour from 0 to 23
     * @see \Phuture\Coherence\Dates::getHour()
     */
    public function getHour(): int
    {
        return Transformer::getHour($this->data);
    }

    /**
     * Returns the minute of the wrapped date/time value.
     *
     * @return int The minute from 0 to 59
     * @see \Phuture\Coherence\Dates::getMinute()
     */
    public function getMinute(): int
    {
        return Transformer::getMinute($this->data);
    }

    /**
     * Returns the second of the wrapped date/time value.
     *
     * @return int The second from 0 to 59
     * @see \Phuture\Coherence\Dates::getSecond()
     */
    public function getSecond(): int
    {
        return Transformer::getSecond($this->data);
    }

    /**
     * Returns the ISO 8601 day of the week of the wrapped date/time value.
     *
     * @return int The ISO 8601 day of the week: 1 (Monday) to 7 (Sunday)
     * @see \Phuture\Coherence\Dates::getDayOfWeek()
     */
    public function getDayOfWeek(): int
    {
        return Transformer::getDayOfWeek($this->data);
    }

    /**
     * Returns the day of the year of the wrapped date/time value.
     *
     * @return int The day of the year from 1 to 366
     * @see \Phuture\Coherence\Dates::getDayOfYear()
     */
    public function getDayOfYear(): int
    {
        return Transformer::getDayOfYear($this->data);
    }

    /**
     * Returns the ISO 8601 week number of the wrapped date/time value.
     *
     * @return int The ISO 8601 week number from 1 to 53
     * @see \Phuture\Coherence\Dates::getWeekOfYear()
     */
    public function getWeekOfYear(): int
    {
        return Transformer::getWeekOfYear($this->data);
    }

    /**
     * Returns the number of days in the month of the wrapped date/time value.
     *
     * @return int The number of days in the month from 28 to 31
     * @see \Phuture\Coherence\Dates::getDaysInMonth()
     */
    public function getDaysInMonth(): int
    {
        return Transformer::getDaysInMonth($this->data);
    }

    /**
     * Returns the timezone identifier of the wrapped date/time value.
     *
     * @return string The timezone identifier string (e.g. 'Europe/Paris')
     * @see \Phuture\Coherence\Dates::getTimezone()
     */
    public function getTimezone(): string
    {
        return Transformer::getTimezone($this->data);
    }

    // -------------------------------------------------------------------------
    // Terminal — boolean checks
    // -------------------------------------------------------------------------

    /**
     * Checks whether the wrapped date/time is a leap year.
     *
     * @return bool Returns true if the year is a leap year
     * @see \Phuture\Coherence\Dates::isLeapYear()
     */
    public function isLeapYear(): bool
    {
        return Transformer::isLeapYear($this->data);
    }

    /**
     * Checks whether the wrapped date/time falls on today's date.
     *
     * @return bool Returns true if the date is today
     * @see \Phuture\Coherence\Dates::isToday()
     */
    public function isToday(): bool
    {
        return Transformer::isToday($this->data);
    }

    /**
     * Checks whether the wrapped date/time falls on yesterday's date.
     *
     * @return bool Returns true if the date is yesterday
     * @see \Phuture\Coherence\Dates::isYesterday()
     */
    public function isYesterday(): bool
    {
        return Transformer::isYesterday($this->data);
    }

    /**
     * Checks whether the wrapped date/time falls on tomorrow's date.
     *
     * @return bool Returns true if the date is tomorrow
     * @see \Phuture\Coherence\Dates::isTomorrow()
     */
    public function isTomorrow(): bool
    {
        return Transformer::isTomorrow($this->data);
    }

    /**
     * Checks whether the wrapped date/time is in the past.
     *
     * @return bool Returns true if the date is before the current moment
     * @see \Phuture\Coherence\Dates::isPast()
     */
    public function isPast(): bool
    {
        return Transformer::isPast($this->data);
    }

    /**
     * Checks whether the wrapped date/time is in the future.
     *
     * @return bool Returns true if the date is after the current moment
     * @see \Phuture\Coherence\Dates::isFuture()
     */
    public function isFuture(): bool
    {
        return Transformer::isFuture($this->data);
    }

    /**
     * Checks whether the wrapped date/time falls on a weekend.
     *
     * @return bool Returns true if the date falls on Saturday or Sunday
     * @see \Phuture\Coherence\Dates::isWeekend()
     */
    public function isWeekend(): bool
    {
        return Transformer::isWeekend($this->data);
    }

    /**
     * Checks whether the wrapped date/time falls on a weekday.
     *
     * @return bool Returns true if the date falls on Monday through Friday
     * @see \Phuture\Coherence\Dates::isWeekday()
     */
    public function isWeekday(): bool
    {
        return Transformer::isWeekday($this->data);
    }

    // -------------------------------------------------------------------------
    // Terminal — comparison
    // -------------------------------------------------------------------------

    /**
     * Checks whether the wrapped date/time is before another date/time value.
     *
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return bool Returns true if the wrapped date is before $comparedTo
     * @see \Phuture\Coherence\Dates::isBefore()
     */
    public function isBefore(DateTimeImmutable $comparedTo): bool
    {
        return Transformer::isBefore($this->data, $comparedTo);
    }

    /**
     * Checks whether the wrapped date/time is after another date/time value.
     *
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return bool Returns true if the wrapped date is after $comparedTo
     * @see \Phuture\Coherence\Dates::isAfter()
     */
    public function isAfter(DateTimeImmutable $comparedTo): bool
    {
        return Transformer::isAfter($this->data, $comparedTo);
    }

    /**
     * Checks whether the wrapped date/time represents the same moment as another.
     *
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return bool Returns true if both values represent the same point in time
     * @see \Phuture\Coherence\Dates::equals()
     */
    public function equals(DateTimeImmutable $comparedTo): bool
    {
        return Transformer::equals($this->data, $comparedTo);
    }

    /**
     * Checks whether the wrapped date/time falls on the same calendar day as another.
     *
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return bool Returns true if both values fall on the same calendar day
     * @see \Phuture\Coherence\Dates::isSameDay()
     */
    public function isSameDay(DateTimeImmutable $comparedTo): bool
    {
        return Transformer::isSameDay($this->data, $comparedTo);
    }

    /**
     * Checks whether the wrapped date/time falls in the same calendar month and year as another.
     *
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return bool Returns true if both values fall in the same calendar month and year
     * @see \Phuture\Coherence\Dates::isSameMonth()
     */
    public function isSameMonth(DateTimeImmutable $comparedTo): bool
    {
        return Transformer::isSameMonth($this->data, $comparedTo);
    }

    /**
     * Checks whether the wrapped date/time falls in the same calendar year as another.
     *
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return bool Returns true if both values fall in the same calendar year
     * @see \Phuture\Coherence\Dates::isSameYear()
     */
    public function isSameYear(DateTimeImmutable $comparedTo): bool
    {
        return Transformer::isSameYear($this->data, $comparedTo);
    }

    // -------------------------------------------------------------------------
    // Terminal — difference
    // -------------------------------------------------------------------------

    /**
     * Calculates the number of complete seconds between the wrapped date/time and another.
     *
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return int The number of complete seconds between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInSeconds()
     */
    public function diffInSeconds(DateTimeImmutable $comparedTo): int
    {
        return Transformer::diffInSeconds($this->data, $comparedTo);
    }

    /**
     * Calculates the number of complete minutes between the wrapped date/time and another.
     *
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return int The number of complete minutes between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInMinutes()
     */
    public function diffInMinutes(DateTimeImmutable $comparedTo): int
    {
        return Transformer::diffInMinutes($this->data, $comparedTo);
    }

    /**
     * Calculates the number of complete hours between the wrapped date/time and another.
     *
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return int The number of complete hours between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInHours()
     */
    public function diffInHours(DateTimeImmutable $comparedTo): int
    {
        return Transformer::diffInHours($this->data, $comparedTo);
    }

    /**
     * Calculates the number of complete days between the wrapped date/time and another.
     *
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return int The number of complete days between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInDays()
     */
    public function diffInDays(DateTimeImmutable $comparedTo): int
    {
        return Transformer::diffInDays($this->data, $comparedTo);
    }

    /**
     * Calculates the number of complete weeks between the wrapped date/time and another.
     *
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return int The number of complete weeks between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInWeeks()
     */
    public function diffInWeeks(DateTimeImmutable $comparedTo): int
    {
        return Transformer::diffInWeeks($this->data, $comparedTo);
    }

    /**
     * Calculates the number of complete months between the wrapped date/time and another.
     *
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return int The number of complete months between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInMonths()
     */
    public function diffInMonths(DateTimeImmutable $comparedTo): int
    {
        return Transformer::diffInMonths($this->data, $comparedTo);
    }

    /**
     * Calculates the number of complete years between the wrapped date/time and another.
     *
     * @param DateTimeImmutable $comparedTo The date/time value to compare against
     * @return int The number of complete years between the two values (always non-negative)
     * @see \Phuture\Coherence\Dates::diffInYears()
     */
    public function diffInYears(DateTimeImmutable $comparedTo): int
    {
        return Transformer::diffInYears($this->data, $comparedTo);
    }

    /**
     * Returns the wrapped DateTimeImmutable value.
     *
     * @return DateTimeImmutable The wrapped date and time value
     */
    public function toDateTimeImmutable(): DateTimeImmutable
    {
        return $this->data;
    }
}
