<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use Phuture\Coherence\Support\StaticClass;

/**
 * Multibyte string utility class providing consistent wrappers around native PHP multibyte string functions.
 *
 * This class offers static methods for comprehensive multibyte string operations with proper character
 * encoding support. All methods follow camelCase naming conventions and provide a clean, object-oriented
 * interface to PHP's native mb_* functions.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class MultibyteStrings extends StaticClass
{
    /**
     * Returns a character from a Unicode code point (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_chr.
     *
     * @param int $codepoint The Unicode code point
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string|false Returns the character or false on failure
     * @see https://www.php.net/manual/en/function.mb-chr.php
     */
    public static function chr(int $codepoint, ?string $encoding = null): string|false
    {
        return mb_chr($codepoint, $encoding);
    }

    /**
     * Checks if a string contains a given substring (multibyte safe).
     *
     * Provides a multibyte-safe contains check using mb_strpos.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return bool Returns true if substring is found, false otherwise
     * @see https://www.php.net/manual/en/function.mb-strpos.php
     */
    public static function contains(
        string $string,
        string $search,
        ?string $encoding = null
    ): bool {
        return mb_strpos($string, $search, 0, $encoding) !== false;
    }

    /**
     * Performs case folding on a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_convert_case.
     *
     * @param string $string The string to convert
     * @param int $mode The case mode (MB_CASE_UPPER, MB_CASE_LOWER, or MB_CASE_TITLE)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the converted string
     * @see https://www.php.net/manual/en/function.mb-convert-case.php
     */
    public static function convertCase(string $string, int $mode, ?string $encoding = null): string
    {
        return mb_convert_case($string, $mode, $encoding);
    }

    /**
     * Converts character encoding of a string or array (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_convert_encoding.
     *
     * @param array|string $string The string or array to convert
     * @param string $to_encoding The target encoding
     * @param array|string|null $from_encoding The source encoding (default: null for auto-detect)
     * @return array|string|false Returns the converted value or false on failure
     * @see https://www.php.net/manual/en/function.mb-convert-encoding.php
     */
    public static function convertEncoding(
        array|string $string,
        string $to_encoding,
        array|string|null $from_encoding = null
    ): array|string|false {
        return mb_convert_encoding($string, $to_encoding, $from_encoding);
    }

    /**
     * Counts the number of substring occurrences (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_substr_count.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int Returns the number of times the substring occurs
     * @see https://www.php.net/manual/en/function.mb-substr-count.php
     */
    public static function count(string $string, string $search, ?string $encoding = null): int
    {
        return mb_substr_count($string, $search, $encoding);
    }

    /**
     * Truncates a string to a specified width (multibyte safe).
     *
     * Provides a consistent wrapper around mb_strimwidth.
     *
     * @param string $string The string to truncate
     * @param int $start The start position
     * @param int $width The desired width
     * @param string $trim_marker String to append when truncated (default: '')
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the truncated string
     * @see https://www.php.net/manual/en/function.mb-strimwidth.php
     */
    public static function cut(
        string $string,
        int $start,
        int $width,
        string $trim_marker = '',
        ?string $encoding = null
    ): string {
        return mb_strimwidth($string, $start, $width, $trim_marker, $encoding);
    }

    /**
     * Detects character encoding of a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_detect_encoding.
     *
     * @param string $string The string to check
     * @param array|string|null $encodings List of encodings to test (default: null for auto)
     * @param bool $strict Use strict detection mode (default: false)
     * @return string|false Returns the detected encoding or false on failure
     * @see https://www.php.net/manual/en/function.mb-detect-encoding.php
     */
    public static function detectEncoding(
        string $string,
        array|string|null $encodings = null,
        bool $strict = false
    ): string|false {
        return mb_detect_encoding($string, $encodings, $strict);
    }

    /**
     * Checks if a string contains a given substring (case-insensitive, multibyte safe).
     *
     * Provides a multibyte-safe case-insensitive contains check using mb_stripos.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return bool Returns true if substring is found, false otherwise
     * @see https://www.php.net/manual/en/function.mb-stripos.php
     */
    public static function icontains(string $string, string $search, ?string $encoding = null): bool
    {
        return mb_stripos($string, $search, 0, $encoding) !== false;
    }

    /**
     * Finds the position of the first occurrence of a substring (case-insensitive, multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_stripos.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.mb-stripos.php
     */
    public static function iposition(
        string $string,
        string $search,
        int $offset = 0,
        ?string $encoding = null
    ): int|false {
        return mb_stripos($string, $search, $offset, $encoding);
    }

    /**
     * Replaces all occurrences of the search string with the replacement string (case-insensitive).
     *
     * Provides a consistent wrapper around mb_str_ireplace.
     *
     * @param string $string The string being searched and replaced on
     * @param string|array $search The value being searched for
     * @param string|array $replace The replacement value
     * @param int|null $count Number of replacements performed (passed by reference)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string|array Returns a string or array with replaced values
     */
    public static function irep(
        string $string,
        string|array $search,
        string|array $replace,
        ?int &$count = null,
        ?string $encoding = null
    ): string|array {
        return mb_str_ireplace($search, $replace, $string, $count, $encoding);
    }

    /**
     * Replaces all occurrences of the search string with the replacement string (case-insensitive).
     *
     * Provides a polyfill implementation of mb_str_ireplace using preg_replace_callback with case-insensitive flag.
     *
     * @param array|string $search The value being searched for
     * @param array|string $replace The replacement value
     * @param array|string $string The string or array being searched and replaced on
     * @param int|null $count Number of replacements performed (passed by reference)
     * @param string|null $encoding Character encoding
     * @return string|array Returns a string or array with replaced values
     */
    public static function ireplace(
        array|string $search,
        array|string $replace,
        array|string $string,
        ?int &$count = null,
        ?string $encoding = null
    ): string|array {
        $searches = is_array($search) ? $search : [$search];
        $replaces = is_array($replace) ? $replace : [$replace];
        $strings = is_array($string) ? $string : [$string];

        $count = 0;
        $result = [];

        foreach ($strings as $subj) {
            foreach ($searches as $i => $srch) {
                $repl = $replaces[$i] ?? '';
                // Use preg_replace with case-insensitive flag and UTF-8 support
                $pattern = '/' . preg_quote($srch, '/') . '/ui';
                $subj = preg_replace_callback($pattern, function () use ($repl, &$count) {
                    $count++;

                    return $repl;
                }, $subj);
            }
            $result[] = $subj;
        }

        return is_array($string) ? $result : $result[0];
    }

    /**
     * Finds the first occurrence of a string (case-insensitive, multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_stristr.
     *
     * @param string $string The string to search in
     * @param string $search The string to search for
     * @param bool $before Return part before needle if true (default: false)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string|false Returns the portion of string, or false if not found
     * @see https://www.php.net/manual/en/function.mb-stristr.php
     */
    public static function istr(
        string $string,
        string $search,
        bool $before = false,
        ?string $encoding = null
    ): string|false {
        return mb_stristr($string, $search, $before, $encoding);
    }

    /**
     * Finds the last occurrence of a character in a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strrchr.
     *
     * @param string $string The string to search in
     * @param string $search The character to find
     * @param bool $before Return part before needle if true (default: false)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string|false Returns the portion of string, or false if not found
     * @see https://www.php.net/manual/en/function.mb-strrchr.php
     */
    public static function lastChr(
        string $string,
        string $search,
        bool $before = false,
        ?string $encoding = null
    ): string|false {
        return mb_strrchr($string, $search, $before, $encoding);
    }

    /**
     * Finds the position of the last occurrence of a substring (case-insensitive, multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strripos.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.mb-strripos.php
     */
    public static function lastIposition(
        string $string,
        string $search,
        int $offset = 0,
        ?string $encoding = null
    ): int|false {
        return mb_strripos($string, $search, $offset, $encoding);
    }

    /**
     * Finds the position of the last occurrence of a substring (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strrpos.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.mb-strrpos.php
     */
    public static function lastPos(
        string $string,
        string $search,
        int $offset = 0,
        ?string $encoding = null
    ): int|false {
        return mb_strrpos($string, $search, $offset, $encoding);
    }

    /**
     * Gets the length of a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strlen.
     *
     * @param string $string The string to measure
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int Returns the length of the string
     * @see https://www.php.net/manual/en/function.mb-strlen.php
     */
    public static function len(string $string, ?string $encoding = null): int
    {
        return mb_strlen($string, $encoding);
    }

    /**
     * Converts a string to lowercase (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strtolower.
     *
     * @param string $string The string to convert
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the lowercased string
     * @see https://www.php.net/manual/en/function.mb-strtolower.php
     */
    public static function lower(string $string, ?string $encoding = null): string
    {
        return mb_strtolower($string, $encoding);
    }

    /**
     * Lowercases the first character of a string (multibyte safe).
     *
     * Provides a multibyte-safe implementation using mb_substr and mb_strtolower.
     *
     * @param string $string The string to convert
     * @param string|null $encoding Character encoding
     * @return string Returns the string with first character lowercased
     * @see https://www.php.net/manual/en/function.mb-substr.php
     * @see https://www.php.net/manual/en/function.mb-strtolower.php
     */
    public static function lowerFirst(string $string, ?string $encoding = null): string
    {
        $encoding = $encoding ?? mb_internal_encoding();
        $first = mb_substr($string, 0, 1, $encoding);
        $rest = mb_substr($string, 1, null, $encoding);

        return mb_strtolower($first, $encoding) . $rest;
    }

    /**
     * Lowercases the first character of each word in a string (multibyte safe).
     *
     * Provides a custom multibyte-safe implementation using preg_replace_callback.
     *
     * @param string $string The string to convert
     * @param string|null $encoding Character encoding
     * @return string Returns the string with each word's first character lowercased
     */
    public static function lowerWords(string $string, ?string $encoding = null): string
    {
        $encoding = $encoding ?? mb_internal_encoding();

        return preg_replace_callback('/\b\w/u', function ($matches) use ($encoding) {
            return mb_strtolower($matches[0], $encoding);
        }, $string);
    }

    /**
     * Gets the Unicode code point of a character (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_ord.
     *
     * @param string $string The character to get the code point from
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int|false Returns the code point or false on failure
     * @see https://www.php.net/manual/en/function.mb-ord.php
     */
    public static function ord(string $string, ?string $encoding = null): int|false
    {
        return mb_ord($string, $encoding);
    }

    /**
     * Parses a query string into variables (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_parse_str.
     *
     * @param string $string The query string to parse
     * @param array $result The array where parsed variables will be stored
     * @return void
     * @see https://www.php.net/manual/en/function.mb-parse-str.php
     */
    public static function parse(string $string, &$result): void
    {
        mb_parse_str($string, $result);
    }

    /**
     * Finds the position of the first occurrence of a substring (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strpos.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.mb-strpos.php
     */
    public static function position(
        string $string,
        string $search,
        int $offset = 0,
        ?string $encoding = null
    ): int|false {
        return mb_strpos($string, $search, $offset, $encoding);
    }

    /**
     * Replaces all occurrences of the search string with the replacement string (multibyte safe).
     *
     * Provides a consistent wrapper around mb_str_replace.
     *
     * @param string $string The string being searched and replaced on
     * @param string|array $search The value being searched for
     * @param string|array $replace The replacement value
     * @param int|null $count Number of replacements performed (passed by reference)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string|array Returns a string or array with replaced values
     */
    public static function rep(
        string $string,
        string|array $search,
        string|array $replace,
        ?int &$count = null,
        ?string $encoding = null
    ): string|array {
        return mb_str_replace($search, $replace, $string, $count, $encoding);
    }

    /**
     * Replaces all occurrences of the search string with the replacement string (multibyte safe polyfill).
     *
     * Provides a polyfill implementation of mb_str_replace using preg_replace_callback.
     *
     * @param array|string $search The value being searched for
     * @param array|string $replace The replacement value
     * @param array|string $string The string or array being searched and replaced on
     * @param int|null $count Number of replacements performed (passed by reference)
     * @param string|null $encoding Character encoding
     * @return string|array Returns a string or array with replaced values
     */
    public static function replace(
        array|string $search,
        array|string $replace,
        array|string $string,
        ?int &$count = null,
        ?string $encoding = null
    ): string|array {
        $searches = is_array($search) ? $search : [$search];
        $replaces = is_array($replace) ? $replace : [$replace];
        $strings = is_array($string) ? $string : [$string];

        $count = 0;
        $result = [];

        foreach ($strings as $subj) {
            foreach ($searches as $i => $srch) {
                $repl = $replaces[$i] ?? '';
                // Use preg_replace for multibyte-safe replacement
                $pattern = '/' . preg_quote($srch, '/') . '/u';
                $subj = preg_replace_callback($pattern, function () use ($repl, &$count) {
                    $count++;

                    return $repl;
                }, $subj);
            }
            $result[] = $subj;
        }

        return is_array($string) ? $result : $result[0];
    }

    /**
     * Replaces invalid characters in a string with substitute characters (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_scrub.
     *
     * @param string $string The string to scrub
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the scrubbed string
     * @see https://www.php.net/manual/en/function.mb-scrub.php
     */
    public static function scrub(string $string, ?string $encoding = null): string
    {
        return mb_scrub($string, $encoding);
    }
    /**
     * Finds the first occurrence of a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strstr.
     *
     * @param string $string The string to search in
     * @param string $search The string to search for
     * @param bool $before Return part before needle if true (default: false)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string|false Returns the portion of string, or false if not found
     * @see https://www.php.net/manual/en/function.mb-strstr.php
     */
    public static function str(
        string $string,
        string $search,
        bool $before = false,
        ?string $encoding = null
    ): string|false {
        return mb_strstr($string, $search, $before, $encoding);
    }

    /**
     * Truncates a string to a specified width (multibyte safe polyfill).
     *
     * Provides a polyfill implementation of mb_strimwidth with proper width calculation.
     *
     * @param string $string The string to truncate
     * @param int $start The start position
     * @param int $width The desired width
     * @param string $trim_marker String to append when truncated (default: '')
     * @param string|null $encoding Character encoding
     * @return string Returns the truncated string
     * @see https://www.php.net/manual/en/function.mb-strimwidth.php
     */
    public static function strimwidth(
        string $string,
        int $start,
        int $width,
        string $trim_marker = '',
        ?string $encoding = null
    ): string {
        $encoding = $encoding ?? mb_internal_encoding();

        // Extract substring from start position
        $string = mb_substr($string, $start, null, $encoding);

        // Get the visual width of the string
        $string_width = mb_strwidth($string, $encoding);

        // If string is already within width, return as-is
        if ($string_width <= $width) {
            return $string;
        }

        // Calculate width available for content (accounting for trim marker)
        $trim_marker_width = mb_strwidth($trim_marker, $encoding);
        $available_width = $width - $trim_marker_width;

        // Truncate string to fit available width
        $truncated = '';
        $current_width = 0;
        $length = mb_strlen($string, $encoding);

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($string, $i, 1, $encoding);
            $char_width = mb_strwidth($char, $encoding);

            if ($current_width + $char_width > $available_width) {
                break;
            }

            $truncated .= $char;
            $current_width += $char_width;
        }

        return $truncated . $trim_marker;
    }

    /**
     * Returns part of a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_substr.
     *
     * @param string $string The input string
     * @param int $offset The start position
     * @param int|null $length The length to extract (default: null for remaining string)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the extracted part of string
     * @see https://www.php.net/manual/en/function.mb-substr.php
     */
    public static function sub(string $string, int $offset, ?int $length = null, ?string $encoding = null): string
    {
        return mb_substr($string, $offset, $length, $encoding);
    }

    /**
     * Converts a string to uppercase (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strtoupper.
     *
     * @param string $string The string to convert
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the uppercased string
     * @see https://www.php.net/manual/en/function.mb-strtoupper.php
     */
    public static function upper(string $string, ?string $encoding = null): string
    {
        return mb_strtoupper($string, $encoding);
    }

    /**
     * Uppercases the first character of a string (multibyte safe).
     *
     * Provides a multibyte-safe implementation using mb_substr and mb_strtoupper.
     *
     * @param string $string The string to convert
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the string with first character uppercased
     * @see https://www.php.net/manual/en/function.mb-substr.php
     * @see https://www.php.net/manual/en/function.mb-strtoupper.php
     */
    public static function upperFirst(string $string, ?string $encoding = null): string
    {
        $first = mb_substr($string, 0, 1, $encoding);
        $rest = mb_substr($string, 1, null, $encoding);

        return mb_strtoupper($first, $encoding) . $rest;
    }

    /**
     * Uppercases the first character of each word in a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_convert_case.
     *
     * @param string $string The string to convert
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the string with each word capitalized
     * @see https://www.php.net/manual/en/function.mb-convert-case.php
     */
    public static function upperWords(string $string, ?string $encoding = null): string
    {
        return mb_convert_case($string, MB_CASE_TITLE, $encoding);
    }

    /**
     * Gets the display width of a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strwidth.
     *
     * @param string $string The string to measure
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int Returns the width of the string
     * @see https://www.php.net/manual/en/function.mb-strwidth.php
     */
    public static function width(string $string, ?string $encoding = null): int
    {
        return mb_strwidth($string, $encoding);
    }
}
