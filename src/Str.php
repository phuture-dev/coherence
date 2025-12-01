<?php

namespace Advandz\Kernel;

use Advandz\Kernel\Class\StaticClass;

/**
 * String utility class providing consistent wrappers around native PHP string functions.
 *
 * This class offers static methods for comprehensive string operations with automatic multibyte
 * detection. All methods automatically detect multibyte strings and use appropriate underlying
 * functions (mb_* for multibyte, native for ASCII), following camelCase naming conventions.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */
class Str extends StaticClass
{
    /**
     * Finds the first occurrence of a string.
     *
     * Provides a unified wrapper with automatic multibyte detection around strstr and mb_strstr.
     *
     * @param string $subject The string to search in
     * @param string $search The string to search for
     * @param bool $before Return part before needle if true (default: false)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string|false Returns the portion of string, or false if not found
     * @see https://www.php.net/manual/en/function.strstr.php
     * @see https://www.php.net/manual/en/function.mb-strstr.php
     */
    public static function str(
        string $subject,
        string $search,
        bool $before = false,
        ?string $encoding = null
    ): string|false
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_strstr($subject, $search, $before, $encoding ?? mb_internal_encoding());
        }

        return strstr($subject, $search, $before);
    }

    /**
     * Finds the first occurrence of a string (case-insensitive).
     *
     * Provides a unified wrapper with automatic multibyte detection around stristr and mb_stristr.
     *
     * @param string $subject The string to search in
     * @param string $search The string to search for
     * @param bool $before Return part before needle if true (default: false)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string|false Returns the portion of string, or false if not found
     * @see https://www.php.net/manual/en/function.stristr.php
     * @see https://www.php.net/manual/en/function.mb-stristr.php
     */
    public static function istr(
        string $subject,
        string $search,
        bool $before = false,
        ?string $encoding = null
    ): string|false
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_stristr($subject, $search, $before, $encoding ?? mb_internal_encoding());
        }

        return stristr($subject, $search, $before);
    }

    /**
     * Checks if a string contains a given substring.
     *
     * Provides a consistent wrapper around the native function str_contains.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @return bool Returns true if substring is found, false otherwise
     * @see https://www.php.net/manual/en/function.str-contains.php
     */
    public static function contains(string $subject, string $search): bool
    {
        return $search !== '' && strpos($subject, $search) !== false;
    }

    /**
     * Checks if a string contains a given substring (case-insensitive).
     *
     * Provides a case-insensitive version of str_contains.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @return bool Returns true if substring is found, false otherwise
     */
    public static function icontains(string $subject, string $search): bool
    {
        return $search !== '' && stripos($subject, $search) !== false;
    }

    /**
     * Finds the position of the first occurrence of a substring.
     *
     * Provides a unified wrapper with automatic multibyte detection around strpos and mb_strpos.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.strpos.php
     * @see https://www.php.net/manual/en/function.mb-strpos.php
     */
    public static function pos(string $subject, string $search, int $offset = 0, ?string $encoding = null): int|false
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_strpos($subject, $search, $offset, $encoding ?? mb_internal_encoding());
        }

        return strpos($subject, $search, $offset);
    }

    /**
     * Finds the position of the first occurrence of a substring (case-insensitive).
     *
     * Provides a unified wrapper with automatic multibyte detection around stripos and mb_stripos.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.stripos.php
     * @see https://www.php.net/manual/en/function.mb-stripos.php
     */
    public static function ipos(string $subject, string $search, int $offset = 0, ?string $encoding = null): int|false
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_stripos($subject, $search, $offset, $encoding ?? mb_internal_encoding());
        }

        return stripos($subject, $search, $offset);
    }

    /**
     * Finds the position of the last occurrence of a substring.
     *
     * Provides a unified wrapper with automatic multibyte detection around strrpos and mb_strrpos.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.strrpos.php
     * @see https://www.php.net/manual/en/function.mb-strrpos.php
     */
    public static function lastPos(
        string $subject,
        string $search,
        int $offset = 0,
        ?string $encoding = null
    ): int|false
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_strrpos($subject, $search, $offset, $encoding ?? mb_internal_encoding());
        }

        return strrpos($subject, $search, $offset);
    }

    /**
     * Finds the position of the last occurrence of a substring (case-insensitive).
     *
     * Provides a unified wrapper with automatic multibyte detection around strripos and mb_strripos.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.strripos.php
     * @see https://www.php.net/manual/en/function.mb-strripos.php
     */
    public static function lastIpos(
        string $subject,
        string $search,
        int $offset = 0,
        ?string $encoding = null
    ): int|false
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_strripos($subject, $search, $offset, $encoding ?? mb_internal_encoding());
        }

        return strripos($subject, $search, $offset);
    }

    /**
     * Gets the length of a string.
     *
     * Provides a unified wrapper with automatic multibyte detection around strlen and mb_strlen.
     *
     * @param string $subject The string to measure
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return int Returns the length of the string
     * @see https://www.php.net/manual/en/function.strlen.php
     * @see https://www.php.net/manual/en/function.mb-strlen.php
     */
    public static function len(string $subject, ?string $encoding = null): int
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_strlen($subject, $encoding ?? mb_internal_encoding());
        }

        return strlen($subject);
    }

    /**
     * Converts a string to lowercase.
     *
     * Provides a unified wrapper with automatic multibyte detection around strtolower and mb_strtolower.
     *
     * @param string $subject The string to convert
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string Returns the lowercased string
     * @see https://www.php.net/manual/en/function.strtolower.php
     * @see https://www.php.net/manual/en/function.mb-strtolower.php
     */
    public static function lower(string $subject, ?string $encoding = null): string
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_strtolower($subject, $encoding ?? mb_internal_encoding());
        }

        return strtolower($subject);
    }

    /**
     * Converts a string to uppercase.
     *
     * Provides a unified wrapper with automatic multibyte detection around strtoupper and mb_strtoupper.
     *
     * @param string $subject The string to convert
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string Returns the uppercased string
     * @see https://www.php.net/manual/en/function.strtoupper.php
     * @see https://www.php.net/manual/en/function.mb-strtoupper.php
     */
    public static function upper(string $subject, ?string $encoding = null): string
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_strtoupper($subject, $encoding ?? mb_internal_encoding());
        }

        return strtoupper($subject);
    }

    /**
     * Uppercases the first character of a string.
     *
     * Provides a unified wrapper with automatic multibyte detection around ucfirst and mb_* equivalents.
     *
     * @param string $subject The string to convert
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string Returns the string with first character uppercased
     * @see https://www.php.net/manual/en/function.ucfirst.php
     */
    public static function upperFirst(string $subject, ?string $encoding = null): string
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            $first = mb_substr($subject, 0, 1, $encoding ?? mb_internal_encoding());
            $rest = mb_substr($subject, 1, null, $encoding ?? mb_internal_encoding());

            return mb_strtoupper($first, $encoding ?? mb_internal_encoding()) . $rest;
        }

        return ucfirst($subject);
    }

    /**
     * Lowercases the first character of a string.
     *
     * Provides a unified wrapper with automatic multibyte detection around lcfirst and mb_* equivalents.
     *
     * @param string $subject The string to convert
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string Returns the string with first character lowercased
     * @see https://www.php.net/manual/en/function.lcfirst.php
     */
    public static function lowerFirst(string $subject, ?string $encoding = null): string
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            $first = mb_substr($subject, 0, 1, $encoding ?? mb_internal_encoding());
            $rest = mb_substr($subject, 1, null, $encoding ?? mb_internal_encoding());

            return mb_strtolower($first, $encoding ?? mb_internal_encoding()) . $rest;
        }

        return lcfirst($subject);
    }

    /**
     * Uppercases the first character of each word in a string.
     *
     * Provides a unified wrapper with automatic multibyte detection around ucwords and mb_convert_case.
     *
     * @param string $subject The string to convert
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string Returns the string with each word capitalized
     * @see https://www.php.net/manual/en/function.ucwords.php
     * @see https://www.php.net/manual/en/function.mb-convert-case.php
     */
    public static function upperWords(string $subject, ?string $encoding = null): string
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_convert_case($subject, MB_CASE_TITLE, $encoding ?? mb_internal_encoding());
        }

        return ucwords($subject);
    }

    /**
     * Lowercases the first character of each word in a string.
     *
     * Provides a custom implementation with automatic multibyte detection.
     *
     * @param string $subject The string to convert
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string Returns the string with each word's first character lowercased
     */
    public static function lowerWords(string $subject, ?string $encoding = null): string
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return preg_replace_callback('/\b\w/u', function ($matches) use ($encoding) {
                return mb_strtolower($matches[0], $encoding ?? mb_internal_encoding());
            }, $subject);
        }

        return preg_replace_callback('/\b\w/', function ($matches) {
            return strtolower($matches[0]);
        }, $subject);
    }

    /**
     * Parses a query string into variables.
     *
     * Provides a unified wrapper with automatic multibyte detection around parse_str and mb_parse_str.
     *
     * @param string $subject The query string to parse
     * @param array $result The array where parsed variables will be stored
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return void
     * @see https://www.php.net/manual/en/function.parse-str.php
     * @see https://www.php.net/manual/en/function.mb-parse-str.php
     */
    public static function parse(string $subject, &$result, ?string $encoding = null): void
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            mb_parse_str($subject, $result);
        } else {
            parse_str($subject, $result);
        }
    }

    /**
     * Returns part of a string.
     *
     * Provides a unified wrapper with automatic multibyte detection around substr and mb_substr.
     *
     * @param string $subject The input string
     * @param int $offset The start position
     * @param int|null $length The length to extract (default: null for remaining string)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string Returns the extracted part of string
     * @see https://www.php.net/manual/en/function.substr.php
     * @see https://www.php.net/manual/en/function.mb-substr.php
     */
    public static function sub(string $subject, int $offset, ?int $length = null, ?string $encoding = null): string
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_substr($subject, $offset, $length, $encoding ?? mb_internal_encoding());
        }

        return substr($subject, $offset, $length);
    }

    /**
     * Strips whitespace (or other characters) from the beginning and end of a string.
     *
     * Provides a consistent wrapper around the native function trim.
     *
     * @param string $subject The string to trim
     * @param string $characters Characters to strip (default: " \n\r\t\v\0")
     * @return string Returns the trimmed string
     * @see https://www.php.net/manual/en/function.trim.php
     */
    public static function trim(string $subject, string $characters = " \n\r\t\v\0"): string
    {
        return trim($subject, $characters);
    }

    /**
     * Strips whitespace (or other characters) from the beginning of a string.
     *
     * Provides a consistent wrapper around the native function ltrim.
     *
     * @param string $subject The string to trim
     * @param string $characters Characters to strip (default: " \n\r\t\v\0")
     * @return string Returns the trimmed string
     * @see https://www.php.net/manual/en/function.ltrim.php
     */
    public static function ltrim(string $subject, string $characters = " \n\r\t\v\0"): string
    {
        return ltrim($subject, $characters);
    }

    /**
     * Strips whitespace (or other characters) from the end of a string.
     *
     * Provides a consistent wrapper around the native function rtrim.
     *
     * @param string $subject The string to trim
     * @param string $characters Characters to strip (default: " \n\r\t\v\0")
     * @return string Returns the trimmed string
     * @see https://www.php.net/manual/en/function.rtrim.php
     */
    public static function rtrim(string $subject, string $characters = " \n\r\t\v\0"): string
    {
        return rtrim($subject, $characters);
    }

    /**
     * Counts the number of substring occurrences.
     *
     * Provides a unified wrapper with automatic multibyte detection around substr_count and mb_substr_count.
     *
     * @param string $subject The string to search in
     * @param string $search The substring to search for
     * @param int $offset The offset where to start counting (default: 0)
     * @param int|null $length Maximum length to search (default: null for entire string)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return int Returns the number of times the substring occurs
     * @see https://www.php.net/manual/en/function.substr-count.php
     * @see https://www.php.net/manual/en/function.mb-substr-count.php
     */
    public static function count(
        string $subject,
        string $search,
        int $offset = 0,
        ?int $length = null,
        ?string $encoding = null
    ): int
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            // Multibyte: emulate offset/length using mb_substr
            if ($offset !== 0 || $length !== null) {
                $subject = mb_substr($subject, $offset, $length, $encoding ?? mb_internal_encoding());
            }

            return mb_substr_count($subject, $search, $encoding ?? mb_internal_encoding());
        }

        return substr_count($subject, $search, $offset, $length);
    }

    /**
     * Replaces all occurrences of the search string with the replacement string.
     *
     * Provides a unified wrapper with automatic multibyte detection around str_replace and mb_str_replace.
     *
     * @param string $subject The string being searched and replaced on
     * @param string|array $search The value being searched for
     * @param string|array $replace The replacement value
     * @param int|null $count Number of replacements performed (passed by reference)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string|array Returns a string or array with replaced values
     * @see https://www.php.net/manual/en/function.str-replace.php
     */
    public static function rep(
        string $subject,
        string|array $search,
        string|array $replace,
        ?int &$count = null,
        ?string $encoding = null
    ): string|array
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_str_replace($search, $replace, $subject, $count, $encoding ?? mb_internal_encoding());
        }

        return str_replace($search, $replace, $subject, $count);
    }

    /**
     * Replaces all occurrences of the search string with the replacement string (case-insensitive).
     *
     * Provides a unified wrapper with automatic multibyte detection around str_ireplace and mb_str_ireplace.
     *
     * @param string $subject The string being searched and replaced on
     * @param string|array $search The value being searched for
     * @param string|array $replace The replacement value
     * @param int|null $count Number of replacements performed (passed by reference)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string|array Returns a string or array with replaced values
     * @see https://www.php.net/manual/en/function.str-ireplace.php
     */
    public static function irep(
        string $subject,
        string|array $search,
        string|array $replace,
        ?int &$count = null,
        ?string $encoding = null
    ): string|array
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_str_ireplace($search, $replace, $subject, $count, $encoding ?? mb_internal_encoding());
        }

        return str_ireplace($search, $replace, $subject, $count);
    }

    /**
     * Reverses a string.
     *
     * Provides a consistent wrapper around the native function strrev.
     *
     * @param string $subject The string to reverse
     * @return string Returns the reversed string
     * @see https://www.php.net/manual/en/function.strrev.php
     */
    public static function reverse(string $subject): string
    {
        return strrev($subject);
    }

    /**
     * Splits a string by a given separator.
     *
     * Provides a consistent wrapper around the native function explode.
     *
     * @param string $subject The string to split
     * @param string $separator The character to use to split the string by
     * @param int $limit The maximum limit of elements with the last element containing the rest of the string
     * @return array Returns a list containing the string split by the separator
     * @see https://www.php.net/manual/en/function.explode.php
     */
    public static function split(string $subject, string $separator, int $limit = PHP_INT_MAX): array
    {
        return explode($subject, $separator, $limit);
    }

    /**
     * Splits a string into smaller chunks.
     *
     * Provides a consistent wrapper around the native function chunk_split.
     *
     * @param string $subject The string to chunk
     * @param int $length The chunk length (default: 76)
     * @param string $separator Sequence to append after each chunk (default: "\r\n")
     * @return string Returns the chunked string
     * @see https://www.php.net/manual/en/function.chunk-split.php
     */
    public static function chunkSplit(string $subject, int $length = 76, string $separator = "\r\n"): string
    {
        return chunk_split($subject, $length, $separator);
    }

    /**
     * Performs binary safe string comparison.
     *
     * Provides a consistent wrapper around the native function strcmp.
     *
     * @param string $string1 The first string
     * @param string $string2 The second string
     * @return int Returns < 0 if string1 is less than string2; > 0 if greater; 0 if equal
     * @see https://www.php.net/manual/en/function.strcmp.php
     */
    public static function compare(string $string1, string $string2): int
    {
        return strcmp($string1, $string2);
    }

    /**
     * Performs binary safe case-insensitive string comparison.
     *
     * Provides a consistent wrapper around the native function strcasecmp.
     *
     * @param string $string1 The first string
     * @param string $string2 The second string
     * @return int Returns < 0 if string1 is less than string2; > 0 if greater; 0 if equal
     * @see https://www.php.net/manual/en/function.strcasecmp.php
     */
    public static function icompare(string $string1, string $string2): int
    {
        return strcasecmp($string1, $string2);
    }

    /**
     * Performs binary safe string comparison of the first n characters.
     *
     * Provides a consistent wrapper around the native function strncmp.
     *
     * @param string $string1 The first string
     * @param string $string2 The second string
     * @param int $length Number of characters to compare
     * @return int Returns < 0 if string1 is less than string2; > 0 if greater; 0 if equal
     * @see https://www.php.net/manual/en/function.strncmp.php
     */
    public static function ncompare(string $string1, string $string2, int $length): int
    {
        return strncmp($string1, $string2, $length);
    }

    /**
     * Performs binary safe case-insensitive string comparison of the first n characters.
     *
     * Provides a consistent wrapper around the native function strncasecmp.
     *
     * @param string $string1 The first string
     * @param string $string2 The second string
     * @param int $length Number of characters to compare
     * @return int Returns < 0 if string1 is less than string2; > 0 if greater; 0 if equal
     * @see https://www.php.net/manual/en/function.strncasecmp.php
     */
    public static function incompare(string $string1, string $string2, int $length): int
    {
        return strncasecmp($string1, $string2, $length);
    }

    /**
     * Finds the last occurrence of a character in a string.
     *
     * Provides a unified wrapper with automatic multibyte detection around strrchr and mb_strrchr.
     *
     * @param string $subject The string to search in
     * @param string $search The character to find
     * @param bool $before Return part before needle if true (default: false)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string|false Returns the portion of string, or false if not found
     * @see https://www.php.net/manual/en/function.strrchr.php
     * @see https://www.php.net/manual/en/function.mb-strrchr.php
     */
    public static function lastChr(
        string $subject,
        string $search,
        bool $before = false,
        ?string $encoding = null
    ): string|false
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_strrchr($subject, $search, $before, $encoding ?? mb_internal_encoding());
        }

        return strrchr($subject, $search);
    }

    /**
     * Replaces text within a portion of a string.
     *
     * Provides a consistent wrapper around the native function substr_replace.
     *
     * @param string|array $subject The string or array being replaced on
     * @param string|array $replace The replacement string or array
     * @param int $offset The offset where replacement begins
     * @param int|null $length Length of the portion to replace (default: null for end of string)
     * @return string|array Returns the result string or array
     * @see https://www.php.net/manual/en/function.substr-replace.php
     */
    public static function replaceSub(
        string|array $subject,
        string|array $replace,
        int $offset,
        ?int $length = null
    ): string|array
    {
        return substr_replace($subject, $replace, $offset, $length);
    }

    /**
     * Wraps a string to a given number of characters.
     *
     * Provides a consistent wrapper around the native function wordwrap.
     *
     * @param string $subject The input string
     * @param int $width The column width (default: 75)
     * @param string $break The line break string (default: "\n")
     * @param bool $cut_long_words Cut words longer than width (default: false)
     * @return string Returns the wrapped string
     * @see https://www.php.net/manual/en/function.wordwrap.php
     */
    public static function wrap(
        string $subject,
        int $width = 75,
        string $break = "\n",
        bool $cut_long_words = false
    ): string
    {
        return wordwrap($subject, $width, $break, $cut_long_words);
    }

    /**
     * Translates characters or replaces substrings.
     *
     * Provides a consistent wrapper around the native function strtr.
     *
     * @param string $subject The string being translated
     * @param array|string $from Translation pairs array or characters to translate from
     * @param string|null $to Characters to translate to (default: null when using array)
     * @return string Returns the translated string
     * @see https://www.php.net/manual/en/function.strtr.php
     */
    public static function translate(string $subject, array|string $from, ?string $to = null): string
    {
        return strtr($subject, $from, $to);
    }

    /**
     * Gets the Unicode code point of a character.
     *
     * Provides a unified wrapper with automatic multibyte detection around ord and mb_ord.
     *
     * @param string $subject The character to get the code point from
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return int|false Returns the code point or false on failure
     * @see https://www.php.net/manual/en/function.ord.php
     * @see https://www.php.net/manual/en/function.mb-ord.php
     */
    public static function ord(string $subject, ?string $encoding = null): int|false
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_ord($subject, $encoding ?? mb_internal_encoding());
        }

        return ord($subject);
    }

    /**
     * Returns a character from a Unicode code point.
     *
     * Provides a wrapper around chr and mb_chr with encoding support.
     *
     * @param int $codepoint The Unicode code point
     * @param string|null $encoding Character encoding (default: null for ASCII)
     * @return string|false Returns the character or false on failure
     * @see https://www.php.net/manual/en/function.chr.php
     * @see https://www.php.net/manual/en/function.mb-chr.php
     */
    public static function chr(int $codepoint, ?string $encoding = null): string|false
    {
        if ($encoding !== null) {
            return mb_chr($codepoint, $encoding);
        }

        return chr($codepoint);
    }

    /**
     * Inserts HTML line breaks before all newlines in a string.
     *
     * Provides a consistent wrapper around the native function nl2br.
     *
     * @param string $subject The input string
     * @param bool $use_xhtml Use XHTML compatible line breaks (default: true)
     * @return string Returns the string with inserted line breaks
     * @see https://www.php.net/manual/en/function.nl2br.php
     */
    public static function nl2br(string $subject, bool $use_xhtml = true): string
    {
        return nl2br($subject, $use_xhtml);
    }

    /**
     * Quotes meta characters.
     *
     * Provides a consistent wrapper around the native function quotemeta.
     *
     * @param string $subject The input string
     * @return string Returns the string with meta characters quoted
     * @see https://www.php.net/manual/en/function.quotemeta.php
     */
    public static function quoteMeta(string $subject): string
    {
        return quotemeta($subject);
    }

    /**
     * Returns a formatted string.
     *
     * Provides a consistent wrapper around the native function sprintf.
     *
     * @param string $format The format string
     * @param mixed ...$values Values to insert into format string
     * @return string Returns the formatted string
     * @see https://www.php.net/manual/en/function.sprintf.php
     */
    public static function format(string $format, mixed ...$values): string
    {
        return sprintf($format, ...$values);
    }

    /**
     * Calculates the similarity between two strings.
     *
     * Provides a consistent wrapper around the native function similar_text.
     *
     * @param string $string1 The first string
     * @param string $string2 The second string
     * @param float|null $percent Similarity percentage stored here (passed by reference)
     * @return int Returns the number of matching characters
     * @see https://www.php.net/manual/en/function.similar-text.php
     */
    public static function similar(string $string1, string $string2, ?float &$percent = null): int
    {
        return similar_text($string1, $string2, $percent);
    }

    /**
     * Calculates Levenshtein distance between two strings.
     *
     * Provides a consistent wrapper around the native function levenshtein.
     *
     * @param string $string1 The first string
     * @param string $string2 The second string
     * @param int $insertion_cost Cost of insertion (default: 1)
     * @param int $replacement_cost Cost of replacement (default: 1)
     * @param int $deletion_cost Cost of deletion (default: 1)
     * @return int Returns the Levenshtein distance between the two strings
     * @see https://www.php.net/manual/en/function.levenshtein.php
     */
    public static function levenshtein(
        string $string1,
        string $string2,
        int $insertion_cost = 1,
        int $replacement_cost = 1,
        int $deletion_cost = 1
    ): int {
        return levenshtein($string1, $string2, $insertion_cost, $replacement_cost, $deletion_cost);
    }

    /**
     * Calculates the soundex key of a string.
     *
     * Provides a consistent wrapper around the native function soundex.
     *
     * @param string $subject The input string
     * @return string Returns the soundex key as a string
     * @see https://www.php.net/manual/en/function.soundex.php
     */
    public static function soundex(string $subject): string
    {
        return soundex($subject);
    }

    /**
     * Calculates the metaphone key of a string.
     *
     * Provides a consistent wrapper around the native function metaphone.
     *
     * @param string $subject The input string
     * @param int $max_phonemes Maximum number of phonemes (default: 0 for no limit)
     * @return string|false Returns the metaphone key or false on failure
     * @see https://www.php.net/manual/en/function.metaphone.php
     */
    public static function metaphone(string $subject, int $max_phonemes = 0): string|false
    {
        return metaphone($subject, $max_phonemes);
    }

    /**
     * Locale based string comparison.
     *
     * Provides a consistent wrapper around the native function strcoll.
     *
     * @param string $string1 The first string
     * @param string $string2 The second string
     * @return int Returns < 0 if string1 is less than string2; > 0 if greater; 0 if equal
     * @see https://www.php.net/manual/en/function.strcoll.php
     */
    public static function localeCompare(string $string1, string $string2): int
    {
        return strcoll($string1, $string2);
    }

    /**
     * Outputs a formatted string using an array of values.
     *
     * Provides a consistent wrapper around the native function vprintf.
     *
     * @param string $format The format string
     * @param array $values Array of values to insert into format string
     * @return int Returns the length of the outputted string
     * @see https://www.php.net/manual/en/function.vprintf.php
     */
    public static function printf(string $format, array $values): int
    {
        return vprintf($format, $values);
    }

    /**
     * Returns a formatted string using an array of values.
     *
     * Provides a consistent wrapper around the native function vsprintf.
     *
     * @param string $format The format string
     * @param array $values Array of values to insert into format string
     * @return string Returns the formatted string
     * @see https://www.php.net/manual/en/function.vsprintf.php
     */
    public static function formatSprintf(string $format, array $values): string
    {
        return vsprintf($format, $values);
    }

    /**
     * Uuencodes a string.
     *
     * Provides a consistent wrapper around the native function convert_uuencode.
     *
     * @param string $subject The string to encode
     * @return string Returns the uuencoded string
     * @see https://www.php.net/manual/en/function.convert-uuencode.php
     */
    public static function convertUuencode(string $subject): string
    {
        return convert_uuencode($subject);
    }

    /**
     * Decodes a uuencoded string.
     *
     * Provides a consistent wrapper around the native function convert_uudecode.
     *
     * @param string $subject The uuencoded string
     * @return string|false Returns the decoded string or false on failure
     * @see https://www.php.net/manual/en/function.convert-uudecode.php
     */
    public static function convertUudecode(string $subject): string|false
    {
        return convert_uudecode($subject);
    }

    /**
     * Tokenizes a string.
     *
     * Provides a consistent wrapper around the native function strtok.
     *
     * @param string $subject The string to tokenize
     * @param string $token The delimiter characters
     * @return string|false Returns the next token or false if no more tokens
     * @see https://www.php.net/manual/en/function.strtok.php
     */
    public static function tok(string $subject, string $token): string|false
    {
        return strtok($subject, $token);
    }

    /**
     * Gets the display width of a string.
     *
     * Provides a unified wrapper with automatic multibyte detection around strlen and mb_strwidth.
     *
     * @param string $subject The string to measure
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return int Returns the width of the string
     * @see https://www.php.net/manual/en/function.mb-strwidth.php
     */
    public static function width(string $subject, ?string $encoding = null): int
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_strwidth($subject, $encoding ?? mb_internal_encoding());
        }

        return strlen($subject);
    }

    /**
     * Truncates a string to a specified width.
     *
     * Provides a unified wrapper with automatic multibyte detection around substr and mb_strimwidth.
     *
     * @param string $subject The string to truncate
     * @param int $start The start position
     * @param int $width The desired width
     * @param string $trim_marker String to append when truncated (default: '')
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string Returns the truncated string
     * @see https://www.php.net/manual/en/function.mb-strimwidth.php
     */
    public static function cut(
        string $subject,
        int $start,
        int $width,
        string $trim_marker = '',
        ?string $encoding = null
    ): string
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_strimwidth($subject, $start, $width, $trim_marker, $encoding ?? mb_internal_encoding());
        }

        $result = substr($subject, $start, $width);
        if (strlen($result) >= $width && strlen($trim_marker) > 0) {
            $result = substr($result, 0, $width - strlen($trim_marker)) . $trim_marker;
        }

        return $result;
    }

    /**
     * Performs case folding on a string.
     *
     * Provides a unified wrapper with automatic multibyte detection around case functions and mb_convert_case.
     *
     * @param string $subject The string to convert
     * @param int $mode The case mode (STR_CASE_UPPER, STR_CASE_LOWER, or STR_CASE_TITLE)
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string Returns the converted string
     * @see https://www.php.net/manual/en/function.mb-convert-case.php
     */
    public static function convertCase(string $subject, int $mode, ?string $encoding = null): string
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_convert_case($subject, $mode, $encoding ?? mb_internal_encoding());
        }

        return match ($mode) {
            MB_CASE_UPPER, STR_CASE_UPPER => strtoupper($subject),
            MB_CASE_LOWER, STR_CASE_LOWER => strtolower($subject),
            MB_CASE_TITLE, STR_CASE_TITLE => ucwords(strtolower($subject)),
            default => $subject,
        };
    }

    /**
     * Detects character encoding of a string.
     *
     * Provides a consistent wrapper around the native function mb_detect_encoding.
     *
     * @param string $subject The string to check
     * @param array|string|null $encodings List of encodings to test (default: null for auto)
     * @param bool $strict Use strict detection mode (default: false)
     * @return string|false Returns the detected encoding or false on failure
     * @see https://www.php.net/manual/en/function.mb-detect-encoding.php
     */
    public static function detectEncoding(
        string $subject,
        array|string|null $encodings = null,
        bool $strict = false
    ): string|false
    {
        return mb_detect_encoding($subject, $encodings, $strict);
    }

    /**
     * Converts character encoding of a string or array.
     *
     * Provides a consistent wrapper around the native function mb_convert_encoding.
     *
     * @param array|string $subject The string or array to convert
     * @param string $to_encoding The target encoding
     * @param array|string|null $from_encoding The source encoding (default: null for auto-detect)
     * @return array|string|false Returns the converted value or false on failure
     * @see https://www.php.net/manual/en/function.mb-convert-encoding.php
     */
    public static function convertEncoding(
        array|string $subject,
        string $to_encoding,
        array|string|null $from_encoding = null
    ): array|string|false
    {
        return mb_convert_encoding($subject, $to_encoding, $from_encoding);
    }

    /**
     * Replaces invalid characters in a string with substitute characters.
     *
     * Provides a unified wrapper with automatic multibyte detection around mb_scrub.
     *
     * @param string $subject The string to scrub
     * @param string|null $encoding Character encoding (default: null for UTF-8)
     * @return string Returns the scrubbed string
     * @see https://www.php.net/manual/en/function.mb-scrub.php
     */
    public static function scrub(string $subject, ?string $encoding = null): string
    {
        if (strlen($subject) !== mb_strlen($subject, $encoding ?? mb_internal_encoding())) {
            return mb_scrub($subject, $encoding ?? mb_internal_encoding());
        }

        return $subject;
    }
}
