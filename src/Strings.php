<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use Phuture\Coherence\Class\StaticClass;

/**
 * String utility class providing consistent wrappers around native PHP string functions.
 *
 * This class offers static methods for comprehensive string operations using native PHP
 * string functions, following camelCase naming conventions.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Strings extends StaticClass
{
    /**
     * Converts a string to camelCase.
     *
     * Converts a string from any format (spaces, snake_case, kebab-case, PascalCase, etc.)
     * to camelCase format (first word lowercase, subsequent words capitalized, no separators).
     *
     * @param string $string The string to convert
     * @return string Returns the string in camelCase format
     */
    public static function camelCase(string $string): string
    {
        // Replace common separators with spaces
        $string = preg_replace('/[_\-]+/', ' ', $string);

        // Split on word boundaries (spaces, uppercase letters)
        $string = preg_replace('/([a-z])([A-Z])/', '$1 $2', $string);

        // Convert to lowercase and split by spaces
        $words = array_filter(explode(' ', strtolower($string)));

        // Capitalize first letter of each word except the first
        $words = array_map(function ($word, $index) {
            return $index === 0 ? $word : ucfirst($word);
        }, $words, array_keys($words));

        return implode('', $words);
    }

    /**
     * Converts a string to snake_case.
     *
     * Converts a string from any format (spaces, camelCase, kebab-case, PascalCase, etc.)
     * to snake_case format (lowercase words separated by underscores).
     *
     * @param string $string The string to convert
     * @return string Returns the string in snake_case format
     */
    public static function snakeCase(string $string): string
    {
        // Replace hyphens with underscores
        $string = str_replace('-', '_', $string);

        // Insert underscore before uppercase letters that follow lowercase letters
        $string = preg_replace('/([a-z])([A-Z])/', '$1_$2', $string);

        // Replace multiple underscores and spaces with single underscore
        $string = preg_replace('/[_\s]+/', '_', $string);

        // Convert to lowercase and trim underscores
        return trim(strtolower($string), '_');
    }

    /**
     * Converts a string to kebab-case.
     *
     * Converts a string from any format (spaces, camelCase, snake_case, PascalCase, etc.)
     * to kebab-case format (lowercase words separated by hyphens).
     *
     * @param string $string The string to convert
     * @return string Returns the string in kebab-case format
     */
    public static function kebabCase(string $string): string
    {
        // Replace underscores with hyphens
        $string = str_replace('_', '-', $string);

        // Insert hyphen before uppercase letters that follow lowercase letters
        $string = preg_replace('/([a-z])([A-Z])/', '$1-$2', $string);

        // Replace multiple hyphens and spaces with single hyphen
        $string = preg_replace('/[\-\s]+/', '-', $string);

        // Convert to lowercase and trim hyphens
        return trim(strtolower($string), '-');
    }

    /**
     * Converts a string to PascalCase.
     *
     * Converts a string from any format (spaces, camelCase, snake_case, kebab-case, etc.)
     * to PascalCase format (all words capitalized, no separators).
     *
     * @param string $string The string to convert
     * @return string Returns the string in PascalCase format
     */
    public static function pascalCase(string $string): string
    {
        // Replace common separators with spaces
        $string = preg_replace('/[_\-]+/', ' ', $string);

        // Split on word boundaries (spaces, uppercase letters)
        $string = preg_replace('/([a-z])([A-Z])/', '$1 $2', $string);

        // Convert to lowercase and split by spaces
        $words = array_filter(explode(' ', strtolower($string)));

        // Capitalize first letter of each word
        $words = array_map('ucfirst', $words);

        return implode('', $words);
    }









    /**
     * Finds the first occurrence of a string.
     *
     * Provides a consistent wrapper around the native function strstr.
     *
     * @param string $string The string to search in
     * @param string $search The string to search for
     * @param bool $before Return part before needle if true (default: false)
     * @return string|false Returns the portion of string, or false if not found
     * @see https://www.php.net/manual/en/function.strstr.php
     */
    public static function str(
        string $string,
        string $search,
        bool $before = false
    ): string|false {
        return strstr($string, $search, $before);
    }

    /**
     * Finds the first occurrence of a string (case-insensitive).
     *
     * Provides a consistent wrapper around the native function stristr.
     *
     * @param string $string The string to search in
     * @param string $search The string to search for
     * @param bool $before Return part before needle if true (default: false)
     * @return string|false Returns the portion of string, or false if not found
     * @see https://www.php.net/manual/en/function.stristr.php
     */
    public static function istr(
        string $string,
        string $search,
        bool $before = false
    ): string|false {
        return stristr($string, $search, $before);
    }

    /**
     * Checks if a string contains a given substring.
     *
     * Provides a consistent wrapper around the native function str_contains.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @return bool Returns true if substring is found, false otherwise
     * @see https://www.php.net/manual/en/function.str-contains.php
     */
    public static function contains(string $string, string $search): bool
    {
        return $search !== '' && strpos($string, $search) !== false;
    }

    /**
     * Checks if a string contains a given substring (case-insensitive).
     *
     * Provides a case-insensitive version of str_contains.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @return bool Returns true if substring is found, false otherwise
     */
    public static function icontains(string $string, string $search): bool
    {
        return $search !== '' && stripos($string, $search) !== false;
    }

    /**
     * Finds the position of the first occurrence of a substring.
     *
     * Provides a consistent wrapper around the native function strpos.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.strpos.php
     */
    public static function position(string $string, string $search, int $offset = 0): int|false
    {
        return strpos($string, $search, $offset);
    }

    /**
     * Finds the position of the first occurrence of a substring (case-insensitive).
     *
     * Provides a consistent wrapper around the native function stripos.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.stripos.php
     */
    public static function iposition(string $string, string $search, int $offset = 0): int|false
    {
        return stripos($string, $search, $offset);
    }

    /**
     * Finds the position of the last occurrence of a substring.
     *
     * Provides a consistent wrapper around the native function strrpos.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.strrpos.php
     */
    public static function lastPos(
        string $string,
        string $search,
        int $offset = 0
    ): int|false {
        return strrpos($string, $search, $offset);
    }

    /**
     * Finds the position of the last occurrence of a substring (case-insensitive).
     *
     * Provides a consistent wrapper around the native function strripos.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @param int $offset The search offset position (default: 0)
     * @return int|false Returns the position or false if not found
     * @see https://www.php.net/manual/en/function.strripos.php
     */
    public static function lastIpos(
        string $string,
        string $search,
        int $offset = 0
    ): int|false {
        return strripos($string, $search, $offset);
    }

    /**
     * Gets the length of a string.
     *
     * Provides a consistent wrapper around the native function strlen.
     *
     * @param string $string The string to measure
     * @return int Returns the length of the string
     * @see https://www.php.net/manual/en/function.strlen.php
     */
    public static function len(string $string): int
    {
        return strlen($string);
    }

    /**
     * Converts a string to lowercase.
     *
     * Provides a consistent wrapper around the native function strtolower.
     *
     * @param string $string The string to convert
     * @return string Returns the lowercased string
     * @see https://www.php.net/manual/en/function.strtolower.php
     */
    public static function lower(string $string): string
    {
        return strtolower($string);
    }

    /**
     * Converts a string to uppercase.
     *
     * Provides a consistent wrapper around the native function strtoupper.
     *
     * @param string $string The string to convert
     * @return string Returns the uppercased string
     * @see https://www.php.net/manual/en/function.strtoupper.php
     */
    public static function upper(string $string): string
    {
        return strtoupper($string);
    }

    /**
     * Uppercases the first character of a string.
     *
     * Provides a consistent wrapper around the native function ucfirst.
     *
     * @param string $string The string to convert
     * @return string Returns the string with first character uppercased
     * @see https://www.php.net/manual/en/function.ucfirst.php
     */
    public static function upperFirst(string $string): string
    {
        return ucfirst($string);
    }

    /**
     * Lowercases the first character of a string.
     *
     * Provides a consistent wrapper around the native function lcfirst.
     *
     * @param string $string The string to convert
     * @return string Returns the string with first character lowercased
     * @see https://www.php.net/manual/en/function.lcfirst.php
     */
    public static function lowerFirst(string $string): string
    {
        return lcfirst($string);
    }

    /**
     * Uppercases the first character of each word in a string.
     *
     * Provides a consistent wrapper around the native function ucwords.
     *
     * @param string $string The string to convert
     * @return string Returns the string with each word capitalized
     * @see https://www.php.net/manual/en/function.ucwords.php
     */
    public static function upperWords(string $string): string
    {
        return ucwords($string);
    }

    /**
     * Lowercases the first character of each word in a string.
     *
     * Provides a custom implementation for lowercasing first character of words.
     *
     * @param string $string The string to convert
     * @return string Returns the string with each word's first character lowercased
     */
    public static function lowerWords(string $string): string
    {
        return preg_replace_callback('/\b\w/', function ($matches) {
            return strtolower($matches[0]);
        }, $string);
    }

    /**
     * Parses a query string into variables.
     *
     * Provides a consistent wrapper around the native function parse_str.
     *
     * @param string $string The query string to parse
     * @param array $result The array where parsed variables will be stored
     * @return void
     * @see https://www.php.net/manual/en/function.parse-str.php
     */
    public static function parse(string $string, &$result): void
    {
        parse_str($string, $result);
    }

    /**
     * Returns part of a string.
     *
     * Provides a consistent wrapper around the native function substr.
     *
     * @param string $string The input string
     * @param int $offset The start position
     * @param int|null $length The length to extract (default: null for remaining string)
     * @return string Returns the extracted part of string
     * @see https://www.php.net/manual/en/function.substr.php
     */
    public static function sub(string $string, int $offset, ?int $length = null): string
    {
        return substr($string, $offset, $length);
    }

    /**
     * Strips whitespace (or other characters) from the beginning and end of a string.
     *
     * Provides a consistent wrapper around the native function trim.
     *
     * @param string $string The string to trim
     * @param string $characters Characters to strip (default: " \n\r\t\v\0")
     * @return string Returns the trimmed string
     * @see https://www.php.net/manual/en/function.trim.php
     */
    public static function trim(string $string, string $characters = " \n\r\t\v\0"): string
    {
        return trim($string, $characters);
    }

    /**
     * Strips whitespace (or other characters) from the beginning of a string.
     *
     * Provides a consistent wrapper around the native function ltrim.
     *
     * @param string $string The string to trim
     * @param string $characters Characters to strip (default: " \n\r\t\v\0")
     * @return string Returns the trimmed string
     * @see https://www.php.net/manual/en/function.ltrim.php
     */
    public static function ltrim(string $string, string $characters = " \n\r\t\v\0"): string
    {
        return ltrim($string, $characters);
    }

    /**
     * Strips whitespace (or other characters) from the end of a string.
     *
     * Provides a consistent wrapper around the native function rtrim.
     *
     * @param string $string The string to trim
     * @param string $characters Characters to strip (default: " \n\r\t\v\0")
     * @return string Returns the trimmed string
     * @see https://www.php.net/manual/en/function.rtrim.php
     */
    public static function rtrim(string $string, string $characters = " \n\r\t\v\0"): string
    {
        return rtrim($string, $characters);
    }

    /**
     * Counts the number of substring occurrences.
     *
     * Provides a consistent wrapper around the native function substr_count.
     *
     * @param string $string The string to search in
     * @param string $search The substring to search for
     * @param int $offset The offset where to start counting (default: 0)
     * @param int|null $length Maximum length to search (default: null for entire string)
     * @return int Returns the number of times the substring occurs
     * @see https://www.php.net/manual/en/function.substr-count.php
     */
    public static function count(
        string $string,
        string $search,
        int $offset = 0,
        ?int $length = null
    ): int {
        return substr_count($string, $search, $offset, $length);
    }

    /**
     * Replaces all occurrences of the search string with the replacement string.
     *
     * Provides a consistent wrapper around the native function str_replace.
     *
     * @param string $string The string being searched and replaced on
     * @param string|array $search The value being searched for
     * @param string|array $replace The replacement value
     * @param int|null $count Number of replacements performed (passed by reference)
     * @return string|array Returns a string or array with replaced values
     * @see https://www.php.net/manual/en/function.str-replace.php
     */
    public static function rep(
        string $string,
        string|array $search,
        string|array $replace,
        ?int &$count = null
    ): string|array {
        return str_replace($search, $replace, $string, $count);
    }

    /**
     * Replaces all occurrences of the search string with the replacement string (case-insensitive).
     *
     * Provides a consistent wrapper around the native function str_ireplace.
     *
     * @param string $string The string being searched and replaced on
     * @param string|array $search The value being searched for
     * @param string|array $replace The replacement value
     * @param int|null $count Number of replacements performed (passed by reference)
     * @return string|array Returns a string or array with replaced values
     * @see https://www.php.net/manual/en/function.str-ireplace.php
     */
    public static function irep(
        string $string,
        string|array $search,
        string|array $replace,
        ?int &$count = null
    ): string|array {
        return str_ireplace($search, $replace, $string, $count);
    }

    /**
     * Reverses a string.
     *
     * Provides a consistent wrapper around the native function strrev.
     *
     * @param string $string The string to reverse
     * @return string Returns the reversed string
     * @see https://www.php.net/manual/en/function.strrev.php
     */
    public static function reverse(string $string): string
    {
        return strrev($string);
    }

    /**
     * Splits a string by a given separator.
     *
     * Provides a consistent wrapper around the native function explode.
     *
     * @param string $string The string to split
     * @param string $separator The character to use to split the string by
     * @param int $limit The maximum limit of elements with the last element containing the rest of the string
     * @return array Returns a list containing the string split by the separator
     * @see https://www.php.net/manual/en/function.explode.php
     */
    public static function split(string $string, string $separator, int $limit = PHP_INT_MAX): array
    {
        return explode($string, $separator, $limit);
    }

    /**
     * Splits a string into smaller chunks.
     *
     * Provides a consistent wrapper around the native function chunk_split.
     *
     * @param string $string The string to chunk
     * @param int $length The chunk length (default: 76)
     * @param string $separator Sequence to append after each chunk (default: "\r\n")
     * @return string Returns the chunked string
     * @see https://www.php.net/manual/en/function.chunk-split.php
     */
    public static function chunkSplit(string $string, int $length = 76, string $separator = "\r\n"): string
    {
        return chunk_split($string, $length, $separator);
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
     * Provides a consistent wrapper around the native function strrchr.
     *
     * @param string $string The string to search in
     * @param string $search The character to find
     * @return string|false Returns the portion of string, or false if not found
     * @see https://www.php.net/manual/en/function.strrchr.php
     */
    public static function lastChr(
        string $string,
        string $search
    ): string|false {
        return strrchr($string, $search);
    }

    /**
     * Replaces text within a portion of a string.
     *
     * Provides a consistent wrapper around the native function substr_replace.
     *
     * @param string|array $string The string or array being replaced on
     * @param string|array $replace The replacement string or array
     * @param int $offset The offset where replacement begins
     * @param int|null $length Length of the portion to replace (default: null for end of string)
     * @return string|array Returns the result string or array
     * @see https://www.php.net/manual/en/function.substr-replace.php
     */
    public static function replaceSub(
        string|array $string,
        string|array $replace,
        int $offset,
        ?int $length = null
    ): string|array {
        return substr_replace($string, $replace, $offset, $length);
    }

    /**
     * Wraps a string to a given number of characters.
     *
     * Provides a consistent wrapper around the native function wordwrap.
     *
     * @param string $string The input string
     * @param int $width The column width (default: 75)
     * @param string $break The line break string (default: "\n")
     * @param bool $cut_long_words Cut words longer than width (default: false)
     * @return string Returns the wrapped string
     * @see https://www.php.net/manual/en/function.wordwrap.php
     */
    public static function wrap(
        string $string,
        int $width = 75,
        string $break = "\n",
        bool $cut_long_words = false
    ): string {
        return wordwrap($string, $width, $break, $cut_long_words);
    }

    /**
     * Translates characters or replaces substrings.
     *
     * Provides a consistent wrapper around the native function strtr.
     *
     * @param string $string The string being translated
     * @param array|string $from Translation pairs array or characters to translate from
     * @param string|null $to Characters to translate to (default: null when using array)
     * @return string Returns the translated string
     * @see https://www.php.net/manual/en/function.strtr.php
     */
    public static function translate(string $string, array|string $from, ?string $to = null): string
    {
        return strtr($string, $from, $to);
    }

    /**
     * Gets the ASCII code point of a character.
     *
     * Provides a consistent wrapper around the native function ord.
     *
     * @param string $string The character to get the code point from
     * @return int Returns the code point
     * @see https://www.php.net/manual/en/function.ord.php
     */
    public static function ord(string $string): int
    {
        return ord($string);
    }

    /**
     * Returns a character from an ASCII code point.
     *
     * Provides a consistent wrapper around the native function chr.
     *
     * @param int $codepoint The ASCII code point
     * @return string Returns the character
     * @see https://www.php.net/manual/en/function.chr.php
     */
    public static function chr(int $codepoint): string
    {
        return chr($codepoint);
    }

    /**
     * Inserts HTML line breaks before all newlines in a string.
     *
     * Provides a consistent wrapper around the native function nl2br.
     *
     * @param string $string The input string
     * @param bool $use_xhtml Use XHTML compatible line breaks (default: true)
     * @return string Returns the string with inserted line breaks
     * @see https://www.php.net/manual/en/function.nl2br.php
     */
    public static function nl2br(string $string, bool $use_xhtml = true): string
    {
        return nl2br($string, $use_xhtml);
    }

    /**
     * Quotes meta characters.
     *
     * Provides a consistent wrapper around the native function quotemeta.
     *
     * @param string $string The input string
     * @return string Returns the string with meta characters quoted
     * @see https://www.php.net/manual/en/function.quotemeta.php
     */
    public static function quoteMeta(string $string): string
    {
        return quotemeta($string);
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
     * @param string $string The input string
     * @return string Returns the soundex key as a string
     * @see https://www.php.net/manual/en/function.soundex.php
     */
    public static function soundex(string $string): string
    {
        return soundex($string);
    }

    /**
     * Calculates the metaphone key of a string.
     *
     * Provides a consistent wrapper around the native function metaphone.
     *
     * @param string $string The input string
     * @param int $max_phonemes Maximum number of phonemes (default: 0 for no limit)
     * @return string|false Returns the metaphone key or false on failure
     * @see https://www.php.net/manual/en/function.metaphone.php
     */
    public static function metaphone(string $string, int $max_phonemes = 0): string|false
    {
        return metaphone($string, $max_phonemes);
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
     * @param string $string The string to encode
     * @return string Returns the uuencoded string
     * @see https://www.php.net/manual/en/function.convert-uuencode.php
     */
    public static function convertUuencode(string $string): string
    {
        return convert_uuencode($string);
    }

    /**
     * Decodes a uuencoded string.
     *
     * Provides a consistent wrapper around the native function convert_uudecode.
     *
     * @param string $string The uuencoded string
     * @return string|false Returns the decoded string or false on failure
     * @see https://www.php.net/manual/en/function.convert-uudecode.php
     */
    public static function convertUudecode(string $string): string|false
    {
        return convert_uudecode($string);
    }

    /**
     * Tokenizes a string.
     *
     * Provides a consistent wrapper around the native function strtok.
     *
     * @param string $string The string to tokenize
     * @param string $token The delimiter characters
     * @return string|false Returns the next token or false if no more tokens
     * @see https://www.php.net/manual/en/function.strtok.php
     */
    public static function tok(string $string, string $token): string|false
    {
        return strtok($string, $token);
    }
}
