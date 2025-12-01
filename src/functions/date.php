<?php

declare(strict_types=1);

/**
 * These functions offer a convenient and more consistent procedural interface to
 * the native date API.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */

if (!function_exists('date_time')) {
    /**
     * Formats a timestamp with timezone support.
     *
     * Provides an enhanced wrapper around the native function date with timezone handling.
     *
     * @param string $format The format string (default: 'c' for ISO 8601)
     * @param int|null $timestamp The Unix timestamp (default: null for current time)
     * @param string|null $timezone The timezone identifier (default: null for system default)
     * @return string Returns the formatted date string
     * @see https://www.php.net/manual/en/function.date.php
     * @see https://www.php.net/manual/en/class.datetime.php
     */
    function date_time(string $format = 'c', ?int $timestamp = null, ?string $timezone = null): string
    {
        if (is_null($timezone)) {
            $timezone = date_default_timezone_get();
        }

        date_default_timezone_set($timezone);

        try {
            $now = new DateTime(date('Y-m-d H:i:s', time()), new DateTimeZone('UTC'));
            if (!is_null($timestamp)) {
                $now = new DateTime(date('Y-m-d H:i:s', $timestamp), new DateTimeZone($timezone));
            }
            $now->setTimezone(new DateTimeZone($timezone));

            return $now->format($format);
        } catch (Throwable $exception) {
            throw new RuntimeException($exception->getMessage());
        }
    }
}
