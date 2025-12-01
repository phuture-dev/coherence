<?php

declare(strict_types=1);

/**
 * These functions offer a convenient and more consistent procedural interface to
 * the native multibyte strings API.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */

if (!function_exists('mb_str_str')) {
    /**
     * Finds the first occurrence of a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strstr.
     *
     * @param string $subject The string to search in
     * @param string $search The string to search for
     * @param bool $before Return part before needle if true (default: false)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string|false Returns the portion of string, or false if not found
     * @see https://www.php.net/manual/en/function.mb-strstr.php
     */
    function mb_str_str(string $subject, string $search, bool $before = false, ?string $encoding = null): string|false
    {
        return mb_strstr($subject, $search, $before, $encoding);
    }
}

if (!function_exists('mb_str_istr')) {
    /**
     * Finds the first occurrence of a string (case-insensitive, multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_stristr.
     *
     * @param string $subject The string to search in
     * @param string $search The string to search for
     * @param bool $before Return part before needle if true (default: false)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string|false Returns the portion of string, or false if not found
     * @see https://www.php.net/manual/en/function.mb-stristr.php
     */
    function mb_str_istr(string $subject, string $search, bool $before = false, ?string $encoding = null): string|false
    {
        return mb_stristr($subject, $search, $before, $encoding);
    }
}

if (!function_exists('mb_str_contains')) {
    /**
     * Checks if a string contains a given substring (multibyte safe).
     *
     * Provides a multibyte-safe contains check using mb_strpos.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return bool Returns true if substring is found, false otherwise
     * @see https://www.php.net/manual/en/function.mb-strpos.php
     */
    function mb_str_contains(string $subject, string $search, ?string $encoding = null): bool
    {
        return mb_strpos($subject, $search, 0, $encoding) !== false;
    }
}

if (!function_exists('mb_str_icontains')) {
    /**
     * Checks if a string contains a given substring (case-insensitive, multibyte safe).
     *
     * Provides a multibyte-safe case-insensitive contains check using mb_stripos.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return bool Returns true if substring is found, false otherwise
     * @see https://www.php.net/manual/en/function.mb-stripos.php
     */
    function mb_str_icontains(string $subject, string $search, ?string $encoding = null): bool
    {
        return mb_stripos($subject, $search, 0, $encoding) !== false;
    }
}

if (!function_exists('mb_str_pos')) {
    /**
     * Finds the position of the first occurrence of a substring (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strpos.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.mb-strpos.php
     */
    function mb_str_pos(string $subject, string $search, int $offset = 0, ?string $encoding = null): int|false
    {
        return mb_strpos($subject, $search, $offset, $encoding);
    }
}

if (!function_exists('mb_str_ipos')) {
    /**
     * Finds the position of the first occurrence of a substring (case-insensitive, multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_stripos.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.mb-stripos.php
     */
    function mb_str_ipos(string $subject, string $search, int $offset = 0, ?string $encoding = null): int|false
    {
        return mb_stripos($subject, $search, $offset, $encoding);
    }
}

if (!function_exists('mb_str_last_pos')) {
    /**
     * Finds the position of the last occurrence of a substring (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strrpos.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.mb-strrpos.php
     */
    function mb_str_last_pos(string $subject, string $search, int $offset = 0, ?string $encoding = null): int|false
    {
        return mb_strrpos($subject, $search, $offset, $encoding);
    }
}

if (!function_exists('mb_str_last_ipos')) {
    /**
     * Finds the position of the last occurrence of a substring (case-insensitive, multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strripos.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.mb-strripos.php
     */
    function mb_str_last_ipos(string $subject, string $search, int $offset = 0, ?string $encoding = null): int|false
    {
        return mb_strripos($subject, $search, $offset, $encoding);
    }
}

if (!function_exists('mb_str_len')) {
    /**
     * Gets the length of a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strlen.
     *
     * @param string $subject The string to measure
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int Returns the length of the string
     * @see https://www.php.net/manual/en/function.mb-strlen.php
     */
    function mb_str_len(string $subject, ?string $encoding = null): int
    {
        return mb_strlen($subject, $encoding);
    }
}

if (!function_exists('mb_str_lower')) {
    /**
     * Converts a string to lowercase (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strtolower.
     *
     * @param string $subject The string to convert
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the lowercased string
     * @see https://www.php.net/manual/en/function.mb-strtolower.php
     */
    function mb_str_lower(string $subject, ?string $encoding = null): string
    {
        return mb_strtolower($subject, $encoding);
    }
}

if (!function_exists('mb_str_upper')) {
    /**
     * Converts a string to uppercase (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strtoupper.
     *
     * @param string $subject The string to convert
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the uppercased string
     * @see https://www.php.net/manual/en/function.mb-strtoupper.php
     */
    function mb_str_upper(string $subject, ?string $encoding = null): string
    {
        return mb_strtoupper($subject, $encoding);
    }
}

if (!function_exists('mb_str_upper_first')) {
    /**
     * Uppercases the first character of a string (multibyte safe).
     *
     * Provides a multibyte-safe implementation using mb_substr and mb_strtoupper.
     *
     * @param string $subject The string to convert
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the string with first character uppercased
     * @see https://www.php.net/manual/en/function.mb-substr.php
     * @see https://www.php.net/manual/en/function.mb-strtoupper.php
     */
    function mb_str_upper_first(string $subject, ?string $encoding = null): string
    {
        $first = mb_substr($subject, 0, 1, $encoding);
        $rest = mb_substr($subject, 1, null, $encoding);
        return mb_strtoupper($first, $encoding) . $rest;
    }
}

if (!function_exists('mb_str_upper_words')) {
    /**
     * Uppercases the first character of each word in a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_convert_case.
     *
     * @param string $subject The string to convert
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the string with each word capitalized
     * @see https://www.php.net/manual/en/function.mb-convert-case.php
     */
    function mb_str_upper_words(string $subject, ?string $encoding = null): string
    {
        return mb_convert_case($subject, MB_CASE_TITLE, $encoding);
    }
}

if (!function_exists('mb_str_lower_first')) {
    /**
     * Lowercases the first character of a string (multibyte safe).
     *
     * Provides a multibyte-safe implementation using mb_substr and mb_strtolower.
     *
     * @param string $subject The string to convert
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string Returns the string with first character lowercased
     * @see https://www.php.net/manual/en/function.mb-substr.php
     * @see https://www.php.net/manual/en/function.mb-strtolower.php
     */
    function mb_str_lower_first(string $subject, ?string $encoding = null): string
    {
        $encoding = $encoding ?? 'UTF-8';
        $first = mb_substr($subject, 0, 1, $encoding);
        $rest = mb_substr($subject, 1, null, $encoding);

        return mb_strtolower($first, $encoding) . $rest;
    }
}

if (!function_exists('mb_str_lower_words')) {
    /**
     * Lowercases the first character of each word in a string (multibyte safe).
     *
     * Provides a custom multibyte-safe implementation using preg_replace_callback.
     *
     * @param string $subject The string to convert
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string Returns the string with each word's first character lowercased
     */
    function mb_str_lower_words(string $subject, ?string $encoding = null): string
    {
        $encoding = $encoding ?? 'UTF-8';

        return preg_replace_callback('/\b\w/u', function ($matches) use ($encoding) {
            return mb_strtolower($matches[0], $encoding);
        }, $subject);
    }
}

if (!function_exists('mb_str_parse')) {
    /**
     * Parses a query string into variables (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_parse_str.
     *
     * @param string $subject The query string to parse
     * @param array $result The array where parsed variables will be stored
     * @return void
     * @see https://www.php.net/manual/en/function.mb-parse-str.php
     */
    function mb_str_parse(string $subject, &$result): void
    {
        mb_parse_str($subject, $result);
    }
}

if (!function_exists('mb_str_sub')) {
    /**
     * Returns part of a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_substr.
     *
     * @param string $subject The input string
     * @param int $offset The start position
     * @param int|null $length The length to extract (default: null for remaining string)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the extracted part of string
     * @see https://www.php.net/manual/en/function.mb-substr.php
     */
    function mb_str_sub(string $subject, int $offset, ?int $length = null, ?string $encoding = null): string
    {
        return mb_substr($subject, $offset, $length, $encoding);
    }
}

if (!function_exists('mb_str_count')) {
    /**
     * Counts the number of substring occurrences (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_substr_count.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int Returns the number of times the substring occurs
     * @see https://www.php.net/manual/en/function.mb-substr-count.php
     */
    function mb_str_count(string $subject, string $search, ?string $encoding = null): int
    {
        return mb_substr_count($subject, $search, $encoding);
    }
}

if (!function_exists('mb_str_last_chr')) {
    /**
     * Finds the last occurrence of a character in a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strrchr.
     *
     * @param string $subject The string to search in
     * @param string $search The character to find
     * @param bool $before Return part before needle if true (default: false)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string|false Returns the portion of string, or false if not found
     * @see https://www.php.net/manual/en/function.mb-strrchr.php
     */
    function mb_str_last_chr(string $subject, string $search, bool $before = false, ?string $encoding = null): string|false
    {
        return mb_strrchr($subject, $search, $before, $encoding);
    }
}

if (!function_exists('mb_str_convert_case')) {
    /**
     * Performs case folding on a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_convert_case.
     *
     * @param string $subject The string to convert
     * @param int $mode The case mode (MB_CASE_UPPER, MB_CASE_LOWER, or MB_CASE_TITLE)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the converted string
     * @see https://www.php.net/manual/en/function.mb-convert-case.php
     */
    function mb_str_convert_case(string $subject, int $mode, ?string $encoding = null): string
    {
        return mb_convert_case($subject, $mode, $encoding);
    }
}

if (!function_exists('mb_str_detect_encoding')) {
    /**
     * Detects character encoding of a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_detect_encoding.
     *
     * @param string $subject The string to check
     * @param array|string|null $encodings List of encodings to test (default: null for auto)
     * @param bool $strict Use strict detection mode (default: false)
     * @return string|false Returns the detected encoding or false on failure
     * @see https://www.php.net/manual/en/function.mb-detect-encoding.php
     */
    function mb_str_detect_encoding(string $subject, array|string|null $encodings = null, bool $strict = false): string|false
    {
        return mb_detect_encoding($subject, $encodings, $strict);
    }
}

if (!function_exists('mb_str_convert_encoding')) {
    /**
     * Converts character encoding of a string or array (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_convert_encoding.
     *
     * @param array|string $subject The string or array to convert
     * @param string $to_encoding The target encoding
     * @param array|string|null $from_encoding The source encoding (default: null for auto-detect)
     * @return array|string|false Returns the converted value or false on failure
     * @see https://www.php.net/manual/en/function.mb-convert-encoding.php
     */
    function mb_str_convert_encoding(array|string $subject, string $to_encoding, array|string|null $from_encoding = null): array|string|false
    {
        return mb_convert_encoding($subject, $to_encoding, $from_encoding);
    }
}

if (!function_exists('mb_str_chr')) {
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
    function mb_str_chr(int $codepoint, ?string $encoding = null): string|false
    {
        return mb_chr($codepoint, $encoding);
    }
}

if (!function_exists('mb_str_ord')) {
    /**
     * Gets the Unicode code point of a character (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_ord.
     *
     * @param string $subject The character to get the code point from
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int|false Returns the code point or false on failure
     * @see https://www.php.net/manual/en/function.mb-ord.php
     */
    function mb_str_ord(string $subject, ?string $encoding = null): int|false
    {
        return mb_ord($subject, $encoding);
    }
}

if (!function_exists('mb_str_scrub')) {
    /**
     * Replaces invalid characters in a string with substitute characters (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_scrub.
     *
     * @param string $subject The string to scrub
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the scrubbed string
     * @see https://www.php.net/manual/en/function.mb-scrub.php
     */
    function mb_str_scrub(string $subject, ?string $encoding = null): string
    {
        return mb_scrub($subject, $encoding);
    }
}

if (!function_exists('mb_str_width')) {
    /**
     * Gets the display width of a string (multibyte safe).
     *
     * Provides a consistent wrapper around the native function mb_strwidth.
     *
     * @param string $subject The string to measure
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return int Returns the width of the string
     * @see https://www.php.net/manual/en/function.mb-strwidth.php
     */
    function mb_str_width(string $subject, ?string $encoding = null): int
    {
        return mb_strwidth($subject, $encoding);
    }
}

if (!function_exists('mb_strimwidth')) {
    /**
     * Truncates a string to a specified width (multibyte safe polyfill).
     *
     * Provides a polyfill implementation of mb_strimwidth with proper width calculation.
     *
     * @param string $string The string to truncate
     * @param int $start The start position
     * @param int $width The desired width
     * @param string $trim_marker String to append when truncated (default: '')
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string Returns the truncated string
     * @see https://www.php.net/manual/en/function.mb-strimwidth.php
     */
    function mb_strimwidth(string $string, int $start, int $width, string $trim_marker = '', ?string $encoding = null): string
    {
        $encoding = $encoding ?? 'UTF-8';

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
}

if (!function_exists('mb_str_cut')) {
    /**
     * Truncates a string to a specified width (multibyte safe).
     *
     * Provides a consistent wrapper around mb_strimwidth.
     *
     * @param string $subject The string to truncate
     * @param int $start The start position
     * @param int $width The desired width
     * @param string $trim_marker String to append when truncated (default: '')
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string Returns the truncated string
     * @see https://www.php.net/manual/en/function.mb-strimwidth.php
     */
    function mb_str_cut(string $subject, int $start, int $width, string $trim_marker = '', ?string $encoding = null): string
    {
        return mb_strimwidth($subject, $start, $width, $trim_marker, $encoding);
    }
}

if (!function_exists('mb_str_replace')) {
    /**
     * Replaces all occurrences of the search string with the replacement string (multibyte safe polyfill).
     *
     * Provides a polyfill implementation of mb_str_replace using preg_replace_callback.
     *
     * @param array|string $search The value being searched for
     * @param array|string $replace The replacement value
     * @param array|string $subject The string or array being searched and replaced on
     * @param int|null $count Number of replacements performed (passed by reference)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string|array Returns a string or array with replaced values
     */
    function mb_str_replace(
        array|string $search,
        array|string $replace,
        array|string $subject,
        int &$count = null,
        ?string $encoding = null
    ): string|array
    {
        $encoding = $encoding ?? 'UTF-8';

        // Convert to arrays for uniform handling
        $searches = is_array($search) ? $search : [$search];
        $replaces = is_array($replace) ? $replace : [$replace];
        $subjects = is_array($subject) ? $subject : [$subject];

        $count = 0;
        $result = [];

        foreach ($subjects as $subj) {
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

        return is_array($subject) ? $result : $result[0];
    }
}

if (!function_exists('mb_str_rep')) {
    /**
     * Replaces all occurrences of the search string with the replacement string (multibyte safe).
     *
     * Provides a consistent wrapper around mb_str_replace.
     *
     * @param string $subject The string being searched and replaced on
     * @param string|array $search The value being searched for
     * @param string|array $replace The replacement value
     * @param int|null $count Number of replacements performed (passed by reference)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string|array Returns a string or array with replaced values
     */
    function mb_str_rep(
        string $subject,
        string|array $search,
        string|array $replace,
        int &$count = null,
        ?string $encoding = null
    ): string|array
    {
        return mb_str_replace($search, $replace, $subject, $count, $encoding);
    }
}

if (!function_exists('mb_str_ireplace')) {
    /**
     * Replaces all occurrences of the search string with the replacement string (case-insensitive, multibyte safe polyfill).
     *
     * Provides a polyfill implementation of mb_str_ireplace using preg_replace_callback with case-insensitive flag.
     *
     * @param array|string $search The value being searched for
     * @param array|string $replace The replacement value
     * @param array|string $subject The string or array being searched and replaced on
     * @param int|null $count Number of replacements performed (passed by reference)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string|array Returns a string or array with replaced values
     */
    function mb_str_ireplace(
        array|string $search,
        array|string $replace,
        array|string $subject,
        int &$count = null,
        ?string $encoding = null
    ): string|array
    {
        $encoding = $encoding ?? 'UTF-8';

        // Convert to arrays for uniform handling
        $searches = is_array($search) ? $search : [$search];
        $replaces = is_array($replace) ? $replace : [$replace];
        $subjects = is_array($subject) ? $subject : [$subject];

        $count = 0;
        $result = [];

        foreach ($subjects as $subj) {
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

        return is_array($subject) ? $result : $result[0];
    }
}

if (!function_exists('mb_str_irep')) {
    /**
     * Replaces all occurrences of the search string with the replacement string (case-insensitive, multibyte safe).
     *
     * Provides a consistent wrapper around mb_str_ireplace.
     *
     * @param string $subject The string being searched and replaced on
     * @param string|array $search The value being searched for
     * @param string|array $replace The replacement value
     * @param int|null $count Number of replacements performed (passed by reference)
     * @param string|null $encoding Character encoding (default: null for internal encoding)
     * @return string|array Returns a string or array with replaced values
     */
    function mb_str_irep(
        string $subject,
        string|array $search,
        string|array $replace,
        int &$count = null,
        ?string $encoding = null
    ): string|array
    {
        return mb_str_ireplace($search, $replace, $subject, $count, $encoding);
    }
}
