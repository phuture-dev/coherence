<?php

declare(strict_types=1);

if (!function_exists('date_time')) {
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
            return date($format, $timestamp);
        }
    }
}
