<?php

declare(strict_types=1);

/**
 * These functions offer a convenient and more consistent procedural interface to
 * the native HTML API.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */

if (!function_exists('html_entity_encode')) {
    /**
     * Converts all applicable characters to HTML entities.
     *
     * Provides a consistent wrapper around the native function htmlentities.
     *
     * @param string $string The input string
     * @param int $flags Flags for controlling conversion behavior (default: ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401)
     * @param string|null $encoding Character encoding (default: null for ini default)
     * @param bool $double_encode Whether to encode existing HTML entities (default: true)
     * @return string|false Returns the encoded string or false on failure
     * @see https://www.php.net/manual/en/function.htmlentities.php
     */
    function html_entity_encode(
        string $string,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
        ?string $encoding = null,
        bool $double_encode = true
    ): string
    {
        return htmlentities($string, $flags, $encoding, $double_encode);
    }
}

if (!function_exists('html_special_chars_encode')) {
    /**
     * Converts special characters to HTML entities.
     *
     * Provides a consistent wrapper around the native function htmlspecialchars.
     *
     * @param string $string The input string
     * @param int $flags Flags for controlling conversion behavior (default: ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401)
     * @param string|null $encoding Character encoding (default: null for ini default)
     * @param bool $double_encode Whether to encode existing HTML entities (default: true)
     * @return string|false Returns the encoded string or false on failure
     * @see https://www.php.net/manual/en/function.htmlspecialchars.php
     */
    function html_special_chars_encode(
        string $string,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
        ?string $encoding = null,
        bool $double_encode = true
    ): string
    {
        return htmlspecialchars($string, $flags, $encoding, $double_encode);
    }
}

if (!function_exists('html_special_chars_decode')) {
    /**
     * Converts special HTML entities back to characters.
     *
     * Provides a consistent wrapper around the native function htmlspecialchars_decode.
     *
     * @param string $string The string to decode
     * @param int $flags Flags for controlling conversion behavior (default: ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401)
     * @return string|false Returns the decoded string or false on failure
     * @see https://www.php.net/manual/en/function.htmlspecialchars-decode.php
     */
    function html_special_chars_decode(
        string $string,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401
    ): string
    {
        return htmlspecialchars_decode($string, $flags);
    }
}

if (!function_exists('html_strip_tags')) {
    /**
     * Strips HTML and PHP tags from a string.
     *
     * Provides a consistent wrapper around the native function strip_tags.
     *
     * @param string $string The input string
     * @param array|string|null $allowed_tags Tags that should not be stripped (default: null)
     * @return string Returns the stripped string
     * @see https://www.php.net/manual/en/function.strip-tags.php
     */
    function html_strip_tags(string $string, array|string|null $allowed_tags = null): string
    {
        return strip_tags($string, $allowed_tags);
    }
}
