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
 * method chaining. Retrieve the final `DateTimeImmutable` by calling `get()` or
 * `toDateTimeImmutable()`, then use the static `Dates` class for formatting or inspection.
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
 *
 * $formatted = Dates::toDateTimeString(
 *     Dates::of('2026-12-25', 'America/New_York')
 *         ->addHours(9)
 *         ->get()
 * );
 * // '2026-12-25 09:00:00'
 * ```
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Dates extends FluentClass
{
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

    /**
     * Removes a number of seconds from the wrapped date/time value.
     *
     * @param int $seconds The number of seconds to remove (must be >= 0)
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::removeSeconds()
     */
    public function removeSeconds(int $seconds): self
    {
        $this->data = Transformer::removeSeconds($this->data, $seconds);

        return $this;
    }

    /**
     * Removes a number of minutes from the wrapped date/time value.
     *
     * @param int $minutes The number of minutes to remove (must be >= 0)
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::removeMinutes()
     */
    public function removeMinutes(int $minutes): self
    {
        $this->data = Transformer::removeMinutes($this->data, $minutes);

        return $this;
    }

    /**
     * Removes a number of hours from the wrapped date/time value.
     *
     * @param int $hours The number of hours to remove (must be >= 0)
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::removeHours()
     */
    public function removeHours(int $hours): self
    {
        $this->data = Transformer::removeHours($this->data, $hours);

        return $this;
    }

    /**
     * Removes a number of days from the wrapped date/time value.
     *
     * @param int $days The number of days to remove (must be >= 0)
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::removeDays()
     */
    public function removeDays(int $days): self
    {
        $this->data = Transformer::removeDays($this->data, $days);

        return $this;
    }

    /**
     * Removes a number of weeks from the wrapped date/time value.
     *
     * @param int $weeks The number of weeks to remove (must be >= 0)
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::removeWeeks()
     */
    public function removeWeeks(int $weeks): self
    {
        $this->data = Transformer::removeWeeks($this->data, $weeks);

        return $this;
    }

    /**
     * Removes a number of months from the wrapped date/time value.
     *
     * @param int $months The number of months to remove (must be >= 0)
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::removeMonths()
     */
    public function removeMonths(int $months): self
    {
        $this->data = Transformer::removeMonths($this->data, $months);

        return $this;
    }

    /**
     * Removes a number of years from the wrapped date/time value.
     *
     * @param int $years The number of years to remove (must be >= 0)
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Dates::removeYears()
     */
    public function removeYears(int $years): self
    {
        $this->data = Transformer::removeYears($this->data, $years);

        return $this;
    }

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

    /**
     * Formats the wrapped date/time using either PHP native or day.js-style format tokens.
     *
     * @param string $format The format string using either PHP date() characters or
     *   day.js-style tokens (auto-detected)
     * @return string The formatted date/time string
     * @see \Phuture\Coherence\Dates::format()
     */
    public function format(string $format): string
    {
        return Transformer::format($this->data, $format);
    }

    /**
     * Returns the date portion of the wrapped date/time as a Y-m-d string.
     *
     * @return string The date portion formatted as 'Y-m-d'
     * @see \Phuture\Coherence\Dates::toDateString()
     */
    public function toDateString(): string
    {
        return Transformer::toDateString($this->data);
    }

    /**
     * Returns the time portion of the wrapped date/time as an H:i:s string.
     *
     * @return string The time portion formatted as 'H:i:s'
     * @see \Phuture\Coherence\Dates::toTimeString()
     */
    public function toTimeString(): string
    {
        return Transformer::toTimeString($this->data);
    }

    /**
     * Returns the wrapped date/time as a combined date and time string.
     *
     * @return string The date and time formatted as 'Y-m-d H:i:s'
     * @see \Phuture\Coherence\Dates::toDateTimeString()
     */
    public function toDateTimeString(): string
    {
        return Transformer::toDateTimeString($this->data);
    }

    /**
     * Returns the wrapped date/time formatted as an ISO 8601 string.
     *
     * @return string The date and time formatted according to ISO 8601
     * @see \Phuture\Coherence\Dates::toIso8601()
     */
    public function toIso8601(): string
    {
        return Transformer::toIso8601($this->data);
    }

    /**
     * Returns the wrapped date/time formatted as an RFC 2822 string.
     *
     * @return string The date and time formatted according to RFC 2822
     * @see \Phuture\Coherence\Dates::toRfc2822()
     */
    public function toRfc2822(): string
    {
        return Transformer::toRfc2822($this->data);
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
