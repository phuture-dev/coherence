<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use Random\RandomException;
use Phuture\Coherence\Support\StaticClass;
use Phuture\Coherence\Exception\InvalidArgumentException;

/**
 * Comprehensive string manipulation utility class with multibyte-safe operations.
 *
 * This utility class provides a complete toolkit for string manipulation, including case
 * conversion, pattern matching, extraction, modification, splitting, joining, validation,
 * truncation, encoding conversion, and miscellaneous string operations. All methods are
 * multibyte-safe using the mb_* functions via symfony/polyfill-mbstring.
 *
 * Key features:
 *
 * - **Core Operations**: Length, case conversion, reversal, and repetition
 * - **Search & Matching**: Find substrings, check for containment, and locate positions
 * - **Extraction**: Extract portions before, after, or between delimiters
 * - **Modification**: Replace, remove, trim, pad, and insert substrings
 * - **Case Conversion**: camel, snake, kebab, pascal, headline
 * - **Splitting & Joining**: Split strings into arrays by patterns or delimiters
 * - **Counting & Comparison**: Count occurrences, compare strings, check equality
 * - **Truncation & Wrapping**: Limit length, wrap text, and extract excerpts
 * - **Testing & Checking**: Validate URLs, emails, UUIDs, ASCII, JSON, and more
 * - **Encoding & Conversion**: Transliterate to ASCII, generate slugs
 * - **Miscellaneous**: Mask, random strings, UUIDs, chunking, and swapping
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Strings extends StaticClass
{
    /**
     * Returns the portion of the string after the first occurrence of a search value.
     *
     * Searches for the first occurrence of `$search` in `$string` and returns everything
     * that follows it. Returns an empty string when the search value is not found.
     * Returns the original string unchanged when `$search` is an empty string.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::after('user@example.com', '@');  // 'example.com'
     * Strings::after('2023-12-25', '-');         // '12-25'
     * Strings::after('hello', 'x');              // ''
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to search for
     * @return string The substring after the first occurrence, or an empty string if not found
     * @see Strings::afterLast()
     * @see Strings::before()
     */
    public static function after(string $string, string $search): string
    {
        if ($search === '') {
            return $string;
        }

        $position = mb_strpos($string, $search, 0, 'UTF-8');

        if ($position === false) {
            return '';
        }

        return mb_substr($string, $position + mb_strlen($search, 'UTF-8'), null, 'UTF-8');
    }

    /**
     * Returns the portion of the string after the last occurrence of a search value.
     *
     * Searches for the last occurrence of `$search` in `$string` and returns everything
     * that follows it. Returns an empty string when the search value is not found.
     * Returns the original string unchanged when `$search` is an empty string.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::afterLast('path/to/file.txt', '/');  // 'file.txt'
     * Strings::afterLast('a.b.c', '.');              // 'c'
     * Strings::afterLast('hello', 'x');              // ''
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to search for
     * @return string The substring after the last occurrence, or an empty string if not found
     * @see Strings::after()
     * @see Strings::beforeLast()
     */
    public static function afterLast(string $string, string $search): string
    {
        if ($search === '') {
            return $string;
        }

        $position = mb_strrpos($string, $search, 0, 'UTF-8');

        if ($position === false) {
            return '';
        }

        return mb_substr($string, $position + mb_strlen($search, 'UTF-8'), null, 'UTF-8');
    }

    /**
     * Transliterates a string to its ASCII representation.
     *
     * Converts accented and non-ASCII characters to their closest ASCII equivalents,
     * then strips any remaining non-printable ASCII characters. Supports language-specific
     * transliteration rules (e.g., German umlauts).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::ascii('héllo');        // 'hello'
     * Strings::ascii('ñaño');         // 'nano'
     * Strings::ascii('über', 'de');   // 'ueber'
     * ```
     *
     * @param string $string The input string to transliterate
     * @param string $language The language code for locale-specific rules (default: 'en')
     * @return string The ASCII-safe string
     * @see Strings::slug()
     */
    public static function ascii(string $string, string $language = 'en'): string
    {
        $string = self::transliterateToAscii($string, $language);

        return preg_replace('/[^\x20-\x7E]/', '', $string);
    }

    /**
     * Returns the portion of the string before the first occurrence of a search value.
     *
     * Searches for the first occurrence of `$search` in `$string` and returns everything
     * that precedes it. Returns the original string when the search value is not found
     * or when `$search` is an empty string.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::before('user@example.com', '@');  // 'user'
     * Strings::before('2023-12-25', '-');         // '2023'
     * Strings::before('hello', 'x');              // 'hello'
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to search for
     * @return string The substring before the first occurrence, or the original string if not found
     * @see Strings::beforeLast()
     * @see Strings::after()
     */
    public static function before(string $string, string $search): string
    {
        if ($search === '') {
            return $string;
        }

        $position = mb_strpos($string, $search, 0, 'UTF-8');

        if ($position === false) {
            return $string;
        }

        return mb_substr($string, 0, $position, 'UTF-8');
    }

    /**
     * Returns the portion of the string before the last occurrence of a search value.
     *
     * Searches for the last occurrence of `$search` in `$string` and returns everything
     * that precedes it. Returns the original string when the search value is not found
     * or when `$search` is an empty string.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::beforeLast('path/to/file.txt', '/');  // 'path/to'
     * Strings::beforeLast('a.b.c', '.');              // 'a.b'
     * Strings::beforeLast('hello', 'x');              // 'hello'
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to search for
     * @return string The substring before the last occurrence, or the original string if not found
     * @see Strings::before()
     * @see Strings::afterLast()
     */
    public static function beforeLast(string $string, string $search): string
    {
        if ($search === '') {
            return $string;
        }

        $position = mb_strrpos($string, $search, 0, 'UTF-8');

        if ($position === false) {
            return $string;
        }

        return mb_substr($string, 0, $position, 'UTF-8');
    }

    /**
     * Returns the portion of the string between two delimiter values.
     *
     * Finds the first occurrence of `$start` and the first occurrence of `$end` after it,
     * and returns everything in between. Returns the original string when either delimiter
     * is empty or not found.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::between('[hello]', '[', ']');           // 'hello'
     * Strings::between('user@example.com', '@', '.');  // 'example'
     * Strings::between('hello', '{', '}');             // 'hello'
     * ```
     *
     * @param string $string The input string to search within
     * @param string $start The opening delimiter
     * @param string $end The closing delimiter
     * @return string The substring between the delimiters, or the original string if not found
     * @see Strings::before()
     * @see Strings::after()
     */
    public static function between(string $string, string $start, string $end): string
    {
        if ($start === '' || $end === '') {
            return $string;
        }

        $startPosition = mb_strpos($string, $start, 0, 'UTF-8');

        if ($startPosition === false) {
            return $string;
        }

        $startPosition += mb_strlen($start, 'UTF-8');
        $endPosition = mb_strpos($string, $end, $startPosition, 'UTF-8');

        if ($endPosition === false) {
            return $string;
        }

        return mb_substr($string, $startPosition, $endPosition - $startPosition, 'UTF-8');
    }

    /**
     * Converts a string to camelCase.
     *
     * Words separated by spaces, hyphens, underscores, or CamelCase boundaries are joined
     * together with each word (except the first) capitalised. The result starts with a
     * lowercase letter.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::camel('hello world');   // 'helloWorld'
     * Strings::camel('hello_world');   // 'helloWorld'
     * Strings::camel('hello-world');   // 'helloWorld'
     * Strings::camel('HelloWorld');    // 'helloWorld'
     * ```
     *
     * @param string $string The input string to convert
     * @return string The camelCase version of the string
     * @see Strings::pascal()
     * @see Strings::snake()
     */
    public static function camel(string $string): string
    {
        $string = preg_replace('/(?<!^)(?=[A-Z])/', ' ', $string);
        $string = self::lower($string);
        $string = preg_replace('/[^a-z0-9]+/i', ' ', $string);
        $string = self::title($string);
        $string = str_replace(' ', '', $string);

        return self::lowerFirst($string);
    }

    /**
     * Converts a string to Title Case (every word capitalised).
     *
     * Each word in the string has its first letter converted to uppercase and the
     * remaining letters converted to lowercase. Multibyte-safe.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::capitalize('hello world');  // 'Hello World'
     * Strings::capitalize('HELLO WORLD');  // 'Hello World'
     * Strings::capitalize('ñaño ñoño');   // 'Ñaño Ñoño'
     * ```
     *
     * @param string $string The input string to capitalize
     * @return string The title-cased version of the string
     * @see Strings::lower()
     * @see Strings::upper()
     */
    public static function capitalize(string $string): string
    {
        return self::title($string);
    }

    /**
     * Returns the character at the given index position.
     *
     * Supports negative indices to count from the end of the string.
     * Returns an empty string when the index is out of bounds.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::charAt('hello', 0);   // 'h'
     * Strings::charAt('hello', -1);  // 'o'
     * Strings::charAt('hello', 10);  // ''
     * ```
     *
     * @param string $string The input string to index into
     * @param int $index The zero-based character index (negative counts from the end)
     * @return string The character at the given position, or an empty string if out of bounds
     * @see Strings::slice()
     */
    public static function charAt(string $string, int $index): string
    {
        $length = self::length($string);

        if ($index < 0) {
            $index += $length;
        }

        if ($index < 0 || $index >= $length) {
            return '';
        }

        return mb_substr($string, $index, 1, 'UTF-8');
    }

    /**
     * Splits the string into an array of chunks of the given size.
     *
     * Divides the string into sequential chunks of `$size` characters each. The last
     * chunk may be shorter if the string length is not evenly divisible by `$size`.
     * Throws when `$size` is less than or equal to zero.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::chunk('abcdef', 2);  // ['ab', 'cd', 'ef']
     * Strings::chunk('hello', 3);   // ['hel', 'lo']
     * ```
     *
     * @param string $string The input string to split
     * @param int $size The number of characters per chunk
     * @return array<int, string> The array of string chunks
     * @throws InvalidArgumentException When `$size` is less than or equal to zero
     * @see Strings::split()
     */
    public static function chunk(string $string, int $size): array
    {
        if ($size <= 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: Chunk size must be greater than zero"
            );
        }

        $chunks = [];
        $length = self::length($string);

        for ($i = 0; $i < $length; $i += $size) {
            $chunks[] = mb_substr($string, $i, $size, 'UTF-8');
        }

        return $chunks;
    }

    /**
     * Compares two strings lexicographically.
     *
     * Returns a negative integer, zero, or a positive integer depending on whether
     * the first string is less than, equal to, or greater than the second string.
     * Supports optional case-insensitive comparison.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::compare('apple', 'banana');            // negative
     * Strings::compare('banana', 'apple');            // positive
     * Strings::compare('hello', 'hello');             // 0
     * Strings::compare('Hello', 'hello', false);      // 0
     * ```
     *
     * @param string $string The first string to compare
     * @param string $other The second string to compare against
     * @param bool $caseSensitive Whether the comparison is case-sensitive (default: true)
     * @return int Negative if less than, 0 if equal, positive if greater than
     * @see Strings::equals()
     */
    public static function compare(string $string, string $other, bool $caseSensitive = true): int
    {
        if (!$caseSensitive) {
            $string = self::lower($string);
            $other = self::lower($other);
        }

        $length = min(mb_strlen($string, 'UTF-8'), mb_strlen($other, 'UTF-8'));

        for ($i = 0; $i < $length; $i++) {
            $charA = mb_substr($string, $i, 1, 'UTF-8');
            $charB = mb_substr($other, $i, 1, 'UTF-8');
            $cmp = strcmp($charA, $charB);

            if ($cmp !== 0) {
                return $cmp;
            }
        }

        return mb_strlen($string, 'UTF-8') - mb_strlen($other, 'UTF-8');
    }

    /**
     * Counts the number of non-overlapping occurrences of a substring.
     *
     * Returns zero when `$substring` is an empty string or is not found in `$string`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::countSubstring('hello world hello', 'hello');  // 2
     * Strings::countSubstring('aaaa', 'aa');                  // 2
     * Strings::countSubstring('hello', 'xyz');                // 0
     * ```
     *
     * @param string $string The input string to search within
     * @param string $substring The substring to count
     * @return int The number of non-overlapping occurrences
     * @see Strings::has()
     */
    public static function countSubstring(string $string, string $substring): int
    {
        if ($substring === '') {
            return 0;
        }

        $count = 0;
        $offset = 0;
        $substringLength = mb_strlen($substring, 'UTF-8');

        while (($position = mb_strpos($string, $substring, $offset, 'UTF-8')) !== false) {
            $count++;
            $offset = $position + $substringLength;
        }

        return $count;
    }

    /**
     * Removes duplicate consecutive occurrences of a character from the string.
     *
     * Replaces runs of two or more adjacent occurrences of `$character` with a single
     * occurrence. Returns the string unchanged when `$character` is an empty string.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::dedupe('hello    world');   // 'hello world'
     * Strings::dedupe('a,,,b,,,c', ',');  // 'a,b,c'
     * Strings::dedupe('---test---', '-'); // '-test-'
     * ```
     *
     * @param string $string The input string to deduplicate
     * @param string $character The character to collapse (default: space)
     * @return string The string with consecutive duplicate characters collapsed
     * @see Strings::squish()
     */
    public static function dedupe(string $string, string $character = ' '): string
    {
        if ($character === '') {
            return $string;
        }

        $escaped = preg_quote($character, '/');

        return preg_replace('/' . $escaped . '+/u', $character, $string);
    }

    /**
     * Determines whether a string ends with a given search value.
     *
     * Returns true when `$string` ends with exactly `$search`. An empty `$search`
     * always returns true.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::endsWith('image.jpg', '.jpg');    // true
     * Strings::endsWith('hello world', 'world'); // true
     * Strings::endsWith('hello', 'Hello');       // false
     * ```
     *
     * @param string $string The input string to check
     * @param string $search The expected suffix
     * @return bool True when the string ends with the search value
     * @see Strings::startsWith()
     */
    public static function endsWith(string $string, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        return mb_substr($string, -mb_strlen($search, 'UTF-8'), null, 'UTF-8') === $search;
    }

    /**
     * Determines whether two strings are equal.
     *
     * Supports optional case-insensitive comparison using multibyte-safe lowercasing.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::equals('hello', 'hello');         // true
     * Strings::equals('Hello', 'hello');         // false
     * Strings::equals('Hello', 'hello', false);  // true
     * ```
     *
     * @param string $string The first string to compare
     * @param string $other The second string to compare against
     * @param bool $caseSensitive Whether the comparison is case-sensitive (default: true)
     * @return bool True when the strings are equal
     * @see Strings::compare()
     */
    public static function equals(string $string, string $other, bool $caseSensitive = true): bool
    {
        if ($caseSensitive) {
            return $string === $other;
        }

        return self::lower($string) === self::lower($other);
    }

    /**
     * Extracts a contextual excerpt of a string around a given phrase.
     *
     * Finds the first occurrence of `$phrase` (case-insensitive) and returns a surrounding
     * excerpt bounded by `$radius` characters on each side. Truncated ends are indicated
     * by `$omission`. When `$phrase` is empty, returns the beginning of the string up to
     * `$radius * 2` characters.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::excerpt('The quick brown fox jumps', 'fox', 5);
     * // '...wn fox ju...'
     * ```
     *
     * @param string $string The input string to excerpt from
     * @param string $phrase The phrase to centre the excerpt around
     * @param int $radius The number of characters to include on each side (default: 100)
     * @param string $omission The string to append at truncated ends (default: '...')
     * @return string The contextual excerpt
     * @see Strings::limit()
     * @see Strings::truncate()
     */
    public static function excerpt(string $string, string $phrase, int $radius = 100, string $omission = '...'): string
    {
        if ($string === '') {
            return '';
        }

        if ($phrase === '') {
            return self::limit($string, $radius * 2, $omission);
        }

        $loweredString = self::lower($string);
        $loweredPhrase = self::lower($phrase);
        $position = mb_strpos($loweredString, $loweredPhrase, 0, 'UTF-8');

        if ($position === false) {
            return self::limit($string, $radius, $omission);
        }

        $start = max(0, $position - $radius);
        $end = min(self::length($string), $position + self::length($phrase) + $radius);
        $excerpt = mb_substr($string, $start, $end - $start, 'UTF-8');

        if ($start > 0) {
            $excerpt = $omission . $excerpt;
        }

        if ($end < self::length($string)) {
            $excerpt .= $omission;
        }

        return $excerpt;
    }

    /**
     * Splits a string into an array using a delimiter.
     *
     * Wraps PHP's native `explode()` with optional limit support. Throws when `$delimiter`
     * is an empty string.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::explode('a,b,c', ',');       // ['a', 'b', 'c']
     * Strings::explode('a,b,c', ',', 2);    // ['a', 'b,c']
     * ```
     *
     * @param string $string The input string to split
     * @param string $delimiter The boundary string
     * @param int $limit Maximum number of returned elements (default: PHP_INT_MAX)
     * @return array<int, string> The array of substrings
     * @throws InvalidArgumentException When `$delimiter` is an empty string
     * @see Strings::split()
     */
    public static function explode(string $string, string $delimiter, int $limit = PHP_INT_MAX): array
    {
        if ($delimiter === '') {
            throw new InvalidArgumentException(
                "Invalid Argument: Delimiter cannot be empty"
            );
        }

        return explode($delimiter, $string, $limit);
    }

    /**
     * Ensures a string ends with exactly one occurrence of the given suffix.
     *
     * If `$string` already ends with one or more occurrences of `$suffix`, they are
     * removed before the suffix is appended once. Returns the string unchanged when
     * `$suffix` is empty.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::finish('path/to', '/');      // 'path/to/'
     * Strings::finish('path/to/', '/');     // 'path/to/'
     * Strings::finish('path/to///', '/');   // 'path/to/'
     * ```
     *
     * @param string $string The input string
     * @param string $suffix The suffix to ensure is present exactly once
     * @return string The string guaranteed to end with the suffix
     * @see Strings::start()
     */
    public static function finish(string $string, string $suffix): string
    {
        if ($suffix === '') {
            return $string;
        }

        $quoted = preg_quote($suffix, '/');

        return preg_replace('/(?:' . $quoted . ')+$/u', '', $string) . $suffix;
    }

    /**
     * Returns the first N characters of a string.
     *
     * Returns an empty string when `$count` is zero or less, or when the input is empty.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::first('hello', 3);  // 'hel'
     * Strings::first('ñaño', 2);   // 'ña'
     * Strings::first('hello');     // 'h'
     * ```
     *
     * @param string $string The input string
     * @param int $count The number of characters to return (default: 1)
     * @return string The first N characters
     * @see Strings::last()
     * @see Strings::take()
     */
    public static function first(string $string, int $count = 1): string
    {
        if ($count <= 0) {
            return '';
        }

        return mb_substr($string, 0, $count, 'UTF-8');
    }

    /**
     * Determines whether a string contains a given search value.
     *
     * An empty `$search` always returns true. Supports optional case-insensitive matching.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::has('hello world', 'world');             // true
     * Strings::has('hello world', 'World', false);      // true (case-insensitive)
     * Strings::has('hello world', 'xyz');               // false
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to look for
     * @param bool $caseSensitive Whether the search is case-sensitive (default: true)
     * @return bool True when the string contains the search value
     * @see Strings::hasAll()
     * @see Strings::hasNone()
     */
    public static function has(string $string, string $search, bool $caseSensitive = true): bool
    {
        if ($search === '') {
            return true;
        }

        if ($caseSensitive) {
            return mb_strpos($string, $search, 0, 'UTF-8') !== false;
        }

        return mb_stripos($string, $search, 0, 'UTF-8') !== false;
    }

    /**
     * Determines whether a string contains all of the given search values.
     *
     * Returns true only when every value in `$searches` is found within `$string`.
     * An empty `$searches` array always returns true.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::hasAll('hello world', ['hello', 'world']);  // true
     * Strings::hasAll('hello world', ['hello', 'xyz']);    // false
     * ```
     *
     * @param string $string The input string to search within
     * @param array<int, string> $searches The values to look for
     * @param bool $caseSensitive Whether the searches are case-sensitive (default: true)
     * @return bool True when all search values are found
     * @see Strings::has()
     * @see Strings::hasNone()
     */
    public static function hasAll(string $string, array $searches, bool $caseSensitive = true): bool
    {
        foreach ($searches as $search) {
            if (!self::has($string, $search, $caseSensitive)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determines whether a string contains none of the given search values.
     *
     * Returns true only when every value in `$searches` is absent from `$string`.
     * An empty `$searches` array always returns true.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::hasNone('hello world', ['foo', 'bar']);  // true
     * Strings::hasNone('hello world', ['hello', 'bar']); // false
     * ```
     *
     * @param string $string The input string to search within
     * @param array<int, string> $searches The values to check for absence
     * @param bool $caseSensitive Whether the searches are case-sensitive (default: true)
     * @return bool True when none of the search values are found
     * @see Strings::has()
     * @see Strings::hasAll()
     */
    public static function hasNone(string $string, array $searches, bool $caseSensitive = true): bool
    {
        foreach ($searches as $search) {
            if (self::has($string, $search, $caseSensitive)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Converts a string to a human-readable headline format.
     *
     * Splits on spaces, hyphens, and underscores, capitalises each word, and joins them
     * with single spaces. Useful for converting identifiers into display labels.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::headline('hello_world');    // 'Hello World'
     * Strings::headline('foo-bar-baz');    // 'Foo Bar Baz'
     * Strings::headline('hello world');   // 'Hello World'
     * ```
     *
     * @param string $string The input string to convert
     * @return string The headline-formatted string
     * @see Strings::pascal()
     * @see Strings::capitalize()
     */
    public static function headline(string $string): string
    {
        $parts = preg_split('/[\s\-_]+/u', $string, -1, PREG_SPLIT_NO_EMPTY);

        if ($parts === false || $parts === []) {
            return '';
        }

        $parts = array_map(fn ($part) => self::title($part), $parts);

        return implode(' ', $parts);
    }

    /**
     * Inserts a substring into a string at the given index position.
     *
     * Supports negative indices to insert relative to the end of the string.
     * When `$index` is beyond the end, the substring is appended. When `$index`
     * is before the start (after negative adjustment), the substring is prepended.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::insert('hello world', '!', 5);   // 'hello! world'
     * Strings::insert('hello world', '!', -1);  // 'hello worl!d'
     * ```
     *
     * @param string $string The input string to insert into
     * @param string $substring The substring to insert
     * @param int $index The zero-based position to insert at (negative counts from the end)
     * @return string The string with the substring inserted
     * @see Strings::slice()
     */
    public static function insert(string $string, string $substring, int $index): string
    {
        $length = self::length($string);

        if ($index < 0) {
            $index += $length;
        }

        if ($index <= 0) {
            return $substring . $string;
        }

        if ($index >= $length) {
            return $string . $substring;
        }

        $before = mb_substr($string, 0, $index, 'UTF-8');
        $after = mb_substr($string, $index, null, 'UTF-8');

        return $before . $substring . $after;
    }

    /**
     * Determines whether a string matches a wildcard pattern.
     *
     * The `*` character acts as a wildcard matching zero or more characters.
     * All other characters are treated as literals.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::is('user_123', 'user_*');   // true
     * Strings::is('photo.jpg', '*.jpg');   // true
     * Strings::is('test.jpg', '*.*');      // true
     * Strings::is('admin', 'user_*');      // false
     * ```
     *
     * @param string $string The input string to test
     * @param string $pattern The wildcard pattern (use `*` as wildcard)
     * @return bool True when the string matches the pattern
     * @see Strings::matches()
     */
    public static function is(string $string, string $pattern): bool
    {
        $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/u';

        return preg_match($regex, $string) === 1;
    }

    /**
     * Determines whether a string contains only alphabetic characters.
     *
     * Returns false for empty strings. Supports multibyte Unicode letters via the `\p{L}`
     * character class.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isAlpha('hello');   // true
     * Strings::isAlpha('héllo');   // true
     * Strings::isAlpha('hello1');  // false
     * Strings::isAlpha('');        // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string contains only Unicode letters
     * @see Strings::isAlphanumeric()
     * @see Strings::isNumeric()
     */
    public static function isAlpha(string $string): bool
    {
        if ($string === '') {
            return false;
        }

        return preg_match('/^\p{L}+$/u', $string) === 1;
    }

    /**
     * Determines whether a string contains only alphanumeric characters.
     *
     * Returns false for empty strings. Supports multibyte Unicode letters and numbers
     * via the `\p{L}\p{N}` character classes.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isAlphanumeric('hello123');  // true
     * Strings::isAlphanumeric('hello');     // true
     * Strings::isAlphanumeric('hello!');    // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string contains only Unicode letters and numbers
     * @see Strings::isAlpha()
     * @see Strings::isNumeric()
     */
    public static function isAlphanumeric(string $string): bool
    {
        if ($string === '') {
            return false;
        }

        return preg_match('/^[\p{L}\p{N}]+$/u', $string) === 1;
    }

    /**
     * Determines whether a string contains only ASCII characters.
     *
     * Returns true for empty strings. A string is ASCII-only when all of its bytes
     * fall within the 0x00–0x7F range.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isAscii('hello');   // true
     * Strings::isAscii('héllo');   // false
     * Strings::isAscii('');        // true
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string contains only ASCII characters
     * @see Strings::ascii()
     */
    public static function isAscii(string $string): bool
    {
        return preg_match('/[^\x00-\x7F]/', $string) === 0;
    }

    /**
     * Determines whether a string contains only whitespace characters (or is empty).
     *
     * Uses PHP's native `trim()` to detect blank strings. Returns true for the empty
     * string as well as strings containing only spaces, tabs, and newlines.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isBlank('');        // true
     * Strings::isBlank('   ');     // true
     * Strings::isBlank("\t\n");    // true
     * Strings::isBlank('hello');   // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string is blank
     * @see Strings::isEmpty()
     * @see Strings::isFilled()
     */
    public static function isBlank(string $string): bool
    {
        return trim($string) === '';
    }

    /**
     * Determines whether a string is a valid email address.
     *
     * Delegates to PHP's `filter_var()` with `FILTER_VALIDATE_EMAIL`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isEmail('user@example.com');  // true
     * Strings::isEmail('not-an-email');      // false
     * ```
     *
     * @param string $string The input string to validate
     * @return bool True when the string is a valid email address
     * @see Strings::isUrl()
     */
    public static function isEmail(string $string): bool
    {
        return filter_var($string, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Determines whether a string is exactly empty (zero-length).
     *
     * A string consisting only of whitespace is NOT considered empty. Use `isBlank()`
     * to check for whitespace-only strings.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isEmpty('');      // true
     * Strings::isEmpty('0');     // false
     * Strings::isEmpty(' ');     // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string has zero length
     * @see Strings::isNotEmpty()
     * @see Strings::isBlank()
     */
    public static function isEmpty(string $string): bool
    {
        return $string === '';
    }

    /**
     * Determines whether a string is non-empty and contains at least one non-whitespace character.
     *
     * Returns false for empty strings and for strings that consist only of whitespace.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isFilled('hello');   // true
     * Strings::isFilled(' hello '); // true
     * Strings::isFilled('');        // false
     * Strings::isFilled('   ');     // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string contains at least one non-whitespace character
     * @see Strings::isBlank()
     * @see Strings::isEmpty()
     */
    public static function isFilled(string $string): bool
    {
        return trim($string) !== '';
    }

    /**
     * Determines whether a string is valid JSON.
     *
     * Returns false for empty strings and any string that cannot be decoded with
     * `json_decode()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isJson('{"name":"John"}');   // true
     * Strings::isJson('["a", "b"]');        // true
     * Strings::isJson('not json');          // false
     * Strings::isJson('');                  // false
     * ```
     *
     * @param string $string The input string to validate
     * @return bool True when the string is valid JSON
     */
    public static function isJson(string $string): bool
    {
        if ($string === '') {
            return false;
        }

        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Determines whether a string is entirely lowercase.
     *
     * Compares the lowercased version of the string against itself using multibyte-safe
     * lowercasing.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isLower('hello');   // true
     * Strings::isLower('Hello');   // false
     * Strings::isLower('');        // true
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string is entirely lowercase
     * @see Strings::isUpper()
     * @see Strings::lower()
     */
    public static function isLower(string $string): bool
    {
        return self::lower($string) === $string;
    }

    /**
     * Determines whether a string matches a regular expression pattern.
     *
     * The `$pattern` must include delimiters (e.g., `/^user_\d+$/`). Returns true when
     * the pattern matches the string.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::matches('user_123', '/^user_\d+$/');   // true
     * Strings::matches('user_abc', '/^user_\d+$/');   // false
     * ```
     *
     * @param string $string The input string to test
     * @param string $pattern The full regular expression pattern including delimiters
     * @return bool True when the pattern matches
     * @see Strings::is()
     */
    public static function matches(string $string, string $pattern): bool
    {
        return preg_match($pattern, $string) === 1;
    }

    /**
     * Determines whether a string is not empty (has at least one character).
     *
     * The inverse of `isEmpty()`. A string consisting only of whitespace is NOT empty.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isNotEmpty('hello');  // true
     * Strings::isNotEmpty(' ');      // true
     * Strings::isNotEmpty('');       // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string is not empty
     * @see Strings::isEmpty()
     */
    public static function isNotEmpty(string $string): bool
    {
        return $string !== '';
    }

    /**
     * Determines whether a string represents a numeric value.
     *
     * Returns false for empty strings. Accepts optional leading minus sign and an
     * optional decimal point.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isNumeric('123');    // true
     * Strings::isNumeric('-45.6');  // true
     * Strings::isNumeric('abc');    // false
     * Strings::isNumeric('');       // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string is numeric
     * @see Strings::isAlpha()
     * @see Strings::isAlphanumeric()
     */
    public static function isNumeric(string $string): bool
    {
        if ($string === '') {
            return false;
        }

        return preg_match('/^-?\d+\.?\d*$/', $string) === 1;
    }

    /**
     * Determines whether a string is entirely uppercase.
     *
     * Compares the uppercased version of the string against itself using multibyte-safe
     * uppercasing.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isUpper('HELLO');   // true
     * Strings::isUpper('Hello');   // false
     * Strings::isUpper('');        // true
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string is entirely uppercase
     * @see Strings::isLower()
     * @see Strings::upper()
     */
    public static function isUpper(string $string): bool
    {
        return self::upper($string) === $string;
    }

    /**
     * Determines whether a string is a valid URL.
     *
     * Delegates to PHP's `filter_var()` with `FILTER_VALIDATE_URL`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isUrl('https://example.com');  // true
     * Strings::isUrl('not-a-url');            // false
     * ```
     *
     * @param string $string The input string to validate
     * @return bool True when the string is a valid URL
     * @see Strings::isEmail()
     */
    public static function isUrl(string $string): bool
    {
        return filter_var($string, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Determines whether a string is a valid UUID (version 1–5).
     *
     * Validates the standard 8-4-4-4-12 hexadecimal format using a case-insensitive
     * regular expression.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isUuid('550e8400-e29b-41d4-a716-446655440000');  // true
     * Strings::isUuid('not-a-uuid');                             // false
     * ```
     *
     * @param string $string The input string to validate
     * @return bool True when the string is a valid UUID
     * @see Strings::uuid()
     */
    public static function isUuid(string $string): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $string
        ) === 1;
    }

    /**
     * Converts a string to kebab-case.
     *
     * Words are lowercased and joined with hyphens. Delegates to `snake()` with a
     * hyphen delimiter.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::kebab('helloWorld');     // 'hello-world'
     * Strings::kebab('UserProfileData'); // 'user-profile-data'
     * Strings::kebab('hello world');    // 'hello-world'
     * ```
     *
     * @param string $string The input string to convert
     * @return string The kebab-case version of the string
     * @see Strings::snake()
     * @see Strings::camel()
     */
    public static function kebab(string $string): string
    {
        return self::snake($string, '-');
    }

    /**
     * Returns the last N characters of a string.
     *
     * Returns an empty string when `$count` is zero or less, or when the input is empty.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::last('hello', 3);  // 'llo'
     * Strings::last('ñaño', 2);   // 'ño'
     * Strings::last('hello');     // 'o'
     * ```
     *
     * @param string $string The input string
     * @param int $count The number of characters to return (default: 1)
     * @return string The last N characters
     * @see Strings::first()
     * @see Strings::takeRight()
     */
    public static function last(string $string, int $count = 1): string
    {
        if ($count <= 0) {
            return '';
        }

        return mb_substr($string, -$count, null, 'UTF-8');
    }

    /**
     * Returns the position of the last occurrence of a search value.
     *
     * Returns false when the search value is not found.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::lastPosition('hello world hello', 'hello');  // 12
     * Strings::lastPosition('hello', 'xyz');                 // false
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to search for
     * @param int $offset The offset from the start to begin searching (default: 0)
     * @return int|false The position of the last occurrence, or false if not found
     * @see Strings::position()
     */
    public static function lastPosition(string $string, string $search, int $offset = 0): int|false
    {
        return mb_strrpos($string, $search, $offset, 'UTF-8');
    }

    /**
     * Returns the number of characters in a string.
     *
     * Multibyte-safe: counts Unicode code points, not bytes.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::length('hello');  // 5
     * Strings::length('ñaño');   // 4
     * Strings::length('你好');    // 2
     * Strings::length('');       // 0
     * ```
     *
     * @param string $string The input string to measure
     * @return int The number of characters
     * @see Strings::wordCount()
     */
    public static function length(string $string): int
    {
        return mb_strlen($string, 'UTF-8');
    }

    /**
     * Limits the string to a given number of characters, appending an omission marker.
     *
     * Returns the string unchanged when its length is within the limit.
     * Trailing whitespace is trimmed before the omission marker is appended.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::limit('Hello World', 5);         // 'Hello...'
     * Strings::limit('Hello World', 5, ' [+]'); // 'Hello [+]'
     * Strings::limit('Hi', 5);                  // 'Hi'
     * ```
     *
     * @param string $string The input string to limit
     * @param int $limit The maximum number of characters before truncation
     * @param string $end The string to append after truncation (default: '...')
     * @return string The limited string
     * @see Strings::truncate()
     * @see Strings::excerpt()
     */
    public static function limit(string $string, int $limit, string $end = '...'): string
    {
        if (self::length($string) <= $limit) {
            return $string;
        }

        return rtrim(mb_substr($string, 0, $limit, 'UTF-8')) . $end;
    }

    /**
     * Converts a string to lowercase.
     *
     * Multibyte-safe: uses `mb_strtolower()` with UTF-8 encoding.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::lower('HELLO');  // 'hello'
     * Strings::lower('ÑOÑO');   // 'ñoño'
     * Strings::lower('ÄÖÜ');   // 'äöü'
     * ```
     *
     * @param string $string The input string to lowercase
     * @return string The lowercased string
     * @see Strings::upper()
     * @see Strings::capitalize()
     */
    public static function lower(string $string): string
    {
        return mb_strtolower($string, 'UTF-8');
    }

    /**
     * Converts only the first character of a string to lowercase.
     *
     * The remainder of the string is left unchanged. Multibyte-safe.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::lowerFirst('Hello World');  // 'hello World'
     * Strings::lowerFirst('HELLO');        // 'hELLO'
     * Strings::lowerFirst('Ñoño');         // 'ñoño'
     * ```
     *
     * @param string $string The input string
     * @return string The string with its first character lowercased
     * @see Strings::lower()
     * @see Strings::capitalize()
     */
    public static function lowerFirst(string $string): string
    {
        $firstChar = mb_substr($string, 0, 1, 'UTF-8');
        $rest = mb_substr($string, 1, null, 'UTF-8');

        return self::lower($firstChar) . $rest;
    }

    /**
     * Masks a portion of a string with a repeated mask character.
     *
     * Replaces characters at positions `[$offset, $offset + $length)` with `$mask`.
     * Supports negative offsets to count from the end of the string. When `$length`
     * is null, all characters from `$offset` onwards are masked.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::mask('1234567890');            // '**********'
     * Strings::mask('1234567890', '*', 3);    // '123*******'
     * Strings::mask('1234567890', '*', 3, 4); // '123****890'
     * Strings::mask('1234567890', '*', -4);   // '123456****'
     * ```
     *
     * @param string $string The input string to mask
     * @param string $mask The mask character to use (default: '*')
     * @param int $offset The start position to begin masking (negative counts from the end)
     * @param int|null $length The number of characters to mask (null masks to the end)
     * @return string The masked string
     */
    public static function mask(string $string, string $mask = '*', int $offset = 0, ?int $length = null): string
    {
        if ($mask === '') {
            return $string;
        }

        $stringLength = self::length($string);

        if ($length === null) {
            $length = $stringLength;
        }

        if ($offset < 0) {
            $offset = max(0, $stringLength + $offset);
        }

        $masked = '';

        for ($i = 0; $i < $stringLength; $i++) {
            if ($i >= $offset && $i < $offset + $length) {
                $masked .= $mask;
            } else {
                $masked .= mb_substr($string, $i, 1, 'UTF-8');
            }
        }

        return $masked;
    }

    /**
     * Pads a string to a given length using a pad string.
     *
     * The `$padType` parameter accepts `STR_PAD_RIGHT` (default), `STR_PAD_LEFT`,
     * or `STR_PAD_BOTH`. Delegates to `padRight()`, `padLeft()`, or `padBoth()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::pad('hello', 10);                      // 'hello     '
     * Strings::pad('hello', 10, '-', STR_PAD_BOTH);   // '--hello---'
     * ```
     *
     * @param string $string The input string to pad
     * @param int $length The target total length
     * @param string $padString The string to pad with (default: space)
     * @param int $padType One of STR_PAD_RIGHT, STR_PAD_LEFT, STR_PAD_BOTH (default: STR_PAD_RIGHT)
     * @return string The padded string
     * @see Strings::padLeft()
     * @see Strings::padRight()
     * @see Strings::padBoth()
     */
    public static function pad(
        string $string,
        int $length,
        string $padString = ' ',
        int $padType = STR_PAD_RIGHT
    ): string {
        return match ($padType) {
            STR_PAD_LEFT => self::padLeft($string, $length, $padString),
            STR_PAD_BOTH => self::padBoth($string, $length, $padString),
            default => self::padRight($string, $length, $padString),
        };
    }

    /**
     * Pads a string to a given length by adding equal amounts of padding on both sides.
     *
     * When the required padding is odd, the extra character is added to the right side.
     * Returns the string unchanged when it is already at or beyond the target length.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::padBoth('hello', 11);        // '   hello   '
     * Strings::padBoth('hello', 11, '-');   // '---hello---'
     * ```
     *
     * @param string $string The input string to pad
     * @param int $length The target total length
     * @param string $padString The string to pad with (default: space)
     * @return string The symmetrically padded string
     * @see Strings::padLeft()
     * @see Strings::padRight()
     */
    public static function padBoth(string $string, int $length, string $padString = ' '): string
    {
        $paddingNeeded = $length - self::length($string);

        if ($paddingNeeded <= 0) {
            return $string;
        }

        $leftPadding = (int) floor($paddingNeeded / 2);
        $rightPadding = $paddingNeeded - $leftPadding;
        $padStringLength = mb_strlen($padString, 'UTF-8');

        if ($padStringLength === 0) {
            return $string;
        }

        $leftRepeats = (int) ceil($leftPadding / $padStringLength);
        $rightRepeats = (int) ceil($rightPadding / $padStringLength);
        $leftPad = mb_substr(str_repeat($padString, $leftRepeats), 0, $leftPadding, 'UTF-8');
        $rightPad = mb_substr(str_repeat($padString, $rightRepeats), 0, $rightPadding, 'UTF-8');

        return $leftPad . $string . $rightPad;
    }

    /**
     * Pads a string to a given length by prepending a pad string on the left.
     *
     * Returns the string unchanged when it is already at or beyond the target length.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::padLeft('hello', 10);        // '     hello'
     * Strings::padLeft('5', 3, '0');        // '005'
     * ```
     *
     * @param string $string The input string to pad
     * @param int $length The target total length
     * @param string $padString The string to pad with (default: space)
     * @return string The left-padded string
     * @see Strings::padRight()
     * @see Strings::padBoth()
     */
    public static function padLeft(string $string, int $length, string $padString = ' '): string
    {
        $paddingNeeded = $length - self::length($string);

        if ($paddingNeeded <= 0) {
            return $string;
        }

        $padStringLength = mb_strlen($padString, 'UTF-8');

        if ($padStringLength === 0) {
            return $string;
        }

        $repeats = (int) ceil($paddingNeeded / $padStringLength);
        $padding = mb_substr(str_repeat($padString, $repeats), 0, $paddingNeeded, 'UTF-8');

        return $padding . $string;
    }

    /**
     * Pads a string to a given length by appending a pad string on the right.
     *
     * Returns the string unchanged when it is already at or beyond the target length.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::padRight('hello', 10);       // 'hello     '
     * Strings::padRight('hello', 10, '-');  // 'hello-----'
     * ```
     *
     * @param string $string The input string to pad
     * @param int $length The target total length
     * @param string $padString The string to pad with (default: space)
     * @return string The right-padded string
     * @see Strings::padLeft()
     * @see Strings::padBoth()
     */
    public static function padRight(string $string, int $length, string $padString = ' '): string
    {
        $paddingNeeded = $length - self::length($string);

        if ($paddingNeeded <= 0) {
            return $string;
        }

        $padStringLength = mb_strlen($padString, 'UTF-8');

        if ($padStringLength === 0) {
            return $string;
        }

        $repeats = (int) ceil($paddingNeeded / $padStringLength);
        $padding = mb_substr(str_repeat($padString, $repeats), 0, $paddingNeeded, 'UTF-8');

        return $string . $padding;
    }

    /**
     * Converts a string to PascalCase (StudlyCase).
     *
     * Words separated by spaces, hyphens, underscores, or CamelCase boundaries are
     * joined together with each word capitalised (including the first).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::pascal('hello world');   // 'HelloWorld'
     * Strings::pascal('hello_world');   // 'HelloWorld'
     * Strings::pascal('hello-world');   // 'HelloWorld'
     * Strings::pascal('helloWorld');    // 'HelloWorld'
     * ```
     *
     * @param string $string The input string to convert
     * @return string The PascalCase version of the string
     * @see Strings::camel()
     * @see Strings::snake()
     */
    public static function pascal(string $string): string
    {
        $string = preg_replace('/(?<!^)(?=[A-Z])/', ' ', $string);
        $string = self::lower($string);
        $string = preg_replace('/[^a-z0-9]+/i', ' ', $string);
        $string = self::title($string);

        return str_replace(' ', '', $string);
    }

    /**
     * Returns the position of the first occurrence of a search value.
     *
     * Returns false when the search value is not found.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::position('hello world', 'world');   // 6
     * Strings::position('hello world', 'xyz');     // false
     * Strings::position('hello hello', 'hello', 3); // 6
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to search for
     * @param int $offset The offset from the start to begin searching (default: 0)
     * @return int|false The position of the first occurrence, or false if not found
     * @see Strings::lastPosition()
     */
    public static function position(string $string, string $search, int $offset = 0): int|false
    {
        return mb_strpos($string, $search, $offset, 'UTF-8');
    }

    /**
     * Generates a cryptographically random alphanumeric string.
     *
     * Uses `random_int()` where available, falling back to `mt_rand()` on failure.
     * The character pool is `[0-9a-zA-Z]` (62 characters).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::random(16);  // e.g. 'aB3xK9mNpQ2rZ5wY'
     * Strings::random(8);   // e.g. 'a1B2c3D4'
     * ```
     *
     * @param int $length The length of the random string to generate (default: 16)
     * @return string The random alphanumeric string
     * @see Strings::uuid()
     */
    public static function random(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characterLength = strlen($characters);
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            try {
                $result .= $characters[random_int(0, $characterLength - 1)];
            } catch (RandomException) {
                $result .= $characters[mt_rand(0, $characterLength - 1)];
            }
        }

        return $result;
    }

    /**
     * Removes all occurrences of a search value from a string.
     *
     * Delegates to `replace()` with an empty replacement string.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::remove('hello world', 'o');                 // 'hell wrld'
     * Strings::remove('Hello World', 'world', false);      // 'Hello '
     * ```
     *
     * @param string $string The input string
     * @param string $search The value to remove
     * @param bool $caseSensitive Whether the removal is case-sensitive (default: true)
     * @return string The string with all occurrences removed
     * @see Strings::replace()
     */
    public static function remove(string $string, string $search, bool $caseSensitive = true): string
    {
        return self::replace($string, $search, '', $caseSensitive);
    }

    /**
     * Repeats a string a given number of times.
     *
     * Returns an empty string when `$times` is zero. Throws when `$times` is negative.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::repeat('ab', 3);  // 'ababab'
     * Strings::repeat('ha', 0);  // ''
     * ```
     *
     * @param string $string The input string to repeat
     * @param int $times The number of repetitions (must be zero or greater)
     * @return string The repeated string
     * @throws InvalidArgumentException When `$times` is negative
     */
    public static function repeat(string $string, int $times): string
    {
        if ($times < 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: Number of times must be zero or greater"
            );
        }

        return str_repeat($string, $times);
    }

    /**
     * Replaces all occurrences of a search value with a replacement.
     *
     * Returns the string unchanged when `$search` is empty. Supports optional
     * case-insensitive replacement.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::replace('hello world', 'world', 'PHP');          // 'hello PHP'
     * Strings::replace('Hello World', 'world', 'PHP', false);   // 'Hello PHP'
     * ```
     *
     * @param string $string The input string
     * @param string $search The value to search for
     * @param string $replace The replacement value
     * @param bool $caseSensitive Whether the replacement is case-sensitive (default: true)
     * @return string The string with all occurrences replaced
     * @see Strings::replaceFirst()
     * @see Strings::replaceLast()
     * @see Strings::remove()
     */
    public static function replace(string $string, string $search, string $replace, bool $caseSensitive = true): string
    {
        if ($search === '') {
            return $string;
        }

        if ($caseSensitive) {
            return str_replace($search, $replace, $string);
        }

        return preg_replace_callback(
            '/' . preg_quote($search, '/') . '/iu',
            static fn() => $replace,
            $string
        );
    }

    /**
     * Replaces the first occurrence of a search value with a replacement.
     *
     * Returns the string unchanged when `$search` is empty or not found.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::replaceFirst('hello hello', 'hello', 'world');  // 'world hello'
     * Strings::replaceFirst('hello', 'xyz', 'world');           // 'hello'
     * ```
     *
     * @param string $string The input string
     * @param string $search The value to search for
     * @param string $replace The replacement value
     * @return string The string with the first occurrence replaced
     * @see Strings::replaceLast()
     * @see Strings::replace()
     */
    public static function replaceFirst(string $string, string $search, string $replace): string
    {
        if ($search === '') {
            return $string;
        }

        $position = mb_strpos($string, $search, 0, 'UTF-8');

        if ($position === false) {
            return $string;
        }

        return mb_substr($string, 0, $position, 'UTF-8')
            . $replace
            . mb_substr($string, $position + mb_strlen($search, 'UTF-8'), null, 'UTF-8');
    }

    /**
     * Replaces the last occurrence of a search value with a replacement.
     *
     * Returns the string unchanged when `$search` is empty or not found.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::replaceLast('hello hello', 'hello', 'world');  // 'hello world'
     * Strings::replaceLast('hello', 'xyz', 'world');           // 'hello'
     * ```
     *
     * @param string $string The input string
     * @param string $search The value to search for
     * @param string $replace The replacement value
     * @return string The string with the last occurrence replaced
     * @see Strings::replaceFirst()
     * @see Strings::replace()
     */
    public static function replaceLast(string $string, string $search, string $replace): string
    {
        if ($search === '') {
            return $string;
        }

        $position = mb_strrpos($string, $search, 0, 'UTF-8');

        if ($position === false) {
            return $string;
        }

        return mb_substr($string, 0, $position, 'UTF-8')
            . $replace
            . mb_substr($string, $position + mb_strlen($search, 'UTF-8'), null, 'UTF-8');
    }

    /**
     * Reverses a string character by character.
     *
     * Multibyte-safe: splits on Unicode code points before reversing.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::reverse('hello');  // 'olleh'
     * Strings::reverse('ñaño');   // 'oñañ'
     * ```
     *
     * @param string $string The input string to reverse
     * @return string The reversed string
     */
    public static function reverse(string $string): string
    {
        $characters = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);

        return implode('', array_reverse($characters));
    }

    /**
     * Removes dangerous control characters from a string.
     *
     * Strips bytes in the ranges 0x00–0x08, 0x0B, 0x0C, 0x0E–0x1F, and 0x7F
     * (all ASCII control characters except tab 0x09, LF 0x0A, and CR 0x0D).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::scrub("hello\x00world");  // 'helloworld'
     * Strings::scrub("clean text");      // 'clean text'
     * ```
     *
     * @param string $string The input string to clean
     * @return string The string with control characters removed
     */
    public static function scrub(string $string): string
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
    }

    /**
     * Extracts a portion of a string by start position and optional length.
     *
     * Supports negative `$start` to count from the end of the string. When `$length`
     * is null, returns all characters from `$start` to the end.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::slice('hello world', 0, 5);   // 'hello'
     * Strings::slice('hello world', 6);       // 'world'
     * Strings::slice('hello world', -5);      // 'world'
     * ```
     *
     * @param string $string The input string to slice
     * @param int $start The starting position (negative counts from the end)
     * @param int|null $length The number of characters to return (null returns to the end)
     * @return string The extracted substring
     * @see Strings::first()
     * @see Strings::last()
     * @see Strings::charAt()
     */
    public static function slice(string $string, int $start, ?int $length = null): string
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * Generates a URL-friendly slug from a string.
     *
     * Transliterates non-ASCII characters, strips non-word characters, collapses
     * separators, and lowercases the result.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::slug('Hello World');      // 'hello-world'
     * Strings::slug('Hello World', '_'); // 'hello_world'
     * Strings::slug('héllo wörld');      // 'hello-world'
     * ```
     *
     * @param string $string The input string to slugify
     * @param string $separator The separator character between words (default: '-')
     * @param string $language The language code for transliteration (default: 'en')
     * @return string The URL-friendly slug
     * @see Strings::ascii()
     */
    public static function slug(string $string, string $separator = '-', string $language = 'en'): string
    {
        $string = self::ascii($string, $language);
        $string = preg_replace('/[^\w\s-]+/', '', $string);
        $string = preg_replace('/[\s-]+/', $separator, $string);

        return self::lower(trim($string, $separator));
    }

    /**
     * Converts a string to snake_case with a configurable delimiter.
     *
     * Words separated by spaces, hyphens, underscores, or CamelCase boundaries are
     * lowercased and joined with the given `$delimiter` (default: underscore).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::snake('helloWorld');         // 'hello_world'
     * Strings::snake('HelloWorld', '-');    // 'hello-world'
     * Strings::snake('hello world');        // 'hello_world'
     * ```
     *
     * @param string $string The input string to convert
     * @param string $delimiter The word separator character (default: '_')
     * @return string The snake_case version of the string
     * @see Strings::kebab()
     * @see Strings::camel()
     */
    public static function snake(string $string, string $delimiter = '_'): string
    {
        $string = preg_replace('/(?<!^)(?=[A-Z])/', ' ', $string);
        $string = preg_replace('/[^a-zA-Z0-9]+/', ' ', $string);
        $string = self::lower(trim($string));

        return preg_replace('/\s+/', $delimiter, $string);
    }

    /**
     * Splits a string into an array by a literal pattern.
     *
     * The `$pattern` is treated as a literal string (not a regex). Returns an array
     * containing the original string when `$pattern` is empty. The `$limit` parameter
     * controls the maximum number of elements returned.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::split('a.b.c', '.');     // ['a', 'b', 'c']
     * Strings::split('a.b.c', '.', 2); // ['a', 'b.c']
     * ```
     *
     * @param string $string The input string to split
     * @param string $pattern The literal separator to split on
     * @param int $limit Maximum number of elements to return (default: -1 = no limit)
     * @return array<int, string> The array of substrings
     * @see Strings::explode()
     */
    public static function split(string $string, string $pattern, int $limit = -1): array
    {
        if ($pattern === '') {
            return [$string];
        }

        $result = preg_split('/' . preg_quote($pattern, '/') . '/u', $string, $limit);

        return $result === false ? [$string] : $result;
    }

    /**
     * Collapses all whitespace sequences into a single space and trims the result.
     *
     * Replaces any sequence of one or more whitespace characters (including tabs and
     * newlines) with a single space, then trims the leading and trailing whitespace.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::squish('hello    world');         // 'hello world'
     * Strings::squish("  hello   \n   world  "); // 'hello world'
     * Strings::squish("a\t\tb\n\nc");            // 'a b c'
     * ```
     *
     * @param string $string The input string to squish
     * @return string The string with collapsed whitespace
     * @see Strings::trim()
     * @see Strings::dedupe()
     */
    public static function squish(string $string): string
    {
        return trim(preg_replace('/\s+/u', ' ', $string));
    }

    /**
     * Ensures a string begins with exactly one occurrence of the given prefix.
     *
     * If `$string` already begins with one or more occurrences of `$prefix`, they are
     * removed before the prefix is prepended once. Returns the string unchanged when
     * `$prefix` is empty.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::start('/path/to', '/');    // '/path/to'
     * Strings::start('path/to', '/');     // '/path/to'
     * Strings::start('///path/to', '/');  // '/path/to'
     * ```
     *
     * @param string $string The input string
     * @param string $prefix The prefix to ensure is present exactly once
     * @return string The string guaranteed to begin with the prefix
     * @see Strings::finish()
     */
    public static function start(string $string, string $prefix): string
    {
        if ($prefix === '') {
            return $string;
        }

        $quoted = preg_quote($prefix, '/');

        return $prefix . preg_replace('/^(?:' . $quoted . ')+/u', '', $string);
    }

    /**
     * Determines whether a string begins with a given search value.
     *
     * Returns true when `$string` starts with exactly `$search`. An empty `$search`
     * always returns true.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::startsWith('hello world', 'hello');      // true
     * Strings::startsWith('https://example.com', 'https://'); // true
     * Strings::startsWith('hello', 'Hello');            // false
     * ```
     *
     * @param string $string The input string to check
     * @param string $search The expected prefix
     * @return bool True when the string begins with the search value
     * @see Strings::endsWith()
     */
    public static function startsWith(string $string, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        return mb_strpos($string, $search, 0, 'UTF-8') === 0;
    }

    /**
     * Strips HTML and PHP tags from a string.
     *
     * Delegates to PHP's native `strip_tags()`. An optional list of allowed tags
     * can be provided to preserve specific tags.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::strip('<p>Hello <b>world</b></p>');        // 'Hello world'
     * Strings::strip('<p>Hello</p>', '<p>');              // '<p>Hello</p>'
     * ```
     *
     * @param string $string The input string to strip
     * @param string $allowedTags HTML tags to preserve (default: '' = strip all)
     * @return string The string with HTML/PHP tags removed
     */
    public static function strip(string $string, string $allowedTags = ''): string
    {
        return strip_tags($string, $allowedTags);
    }

    /**
     * Performs multiple simultaneous search-and-replace operations.
     *
     * Keys of `$replacements` are searched for and replaced with their corresponding
     * values. All replacements happen in a single pass.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::swap('hello world', ['hello' => 'hi', 'world' => 'earth']);
     * // 'hi earth'
     * ```
     *
     * @param string $string The input string
     * @param array<string, string> $replacements A map of search => replacement pairs
     * @return string The string with all swaps applied
     * @see Strings::replace()
     */
    public static function swap(string $string, array $replacements): string
    {
        return str_replace(array_keys($replacements), array_values($replacements), $string);
    }

    /**
     * Returns the first or last N characters of a string based on the sign of `$count`.
     *
     * A positive `$count` returns the first N characters; a negative `$count` returns
     * the last N characters (using the absolute value).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::take('hello world', 5);   // 'hello'
     * Strings::take('hello world', -5);  // 'world'
     * Strings::take('hello world', 0);   // ''
     * ```
     *
     * @param string $string The input string
     * @param int $count The number of characters (negative returns from the end)
     * @return string The extracted characters
     * @see Strings::first()
     * @see Strings::takeRight()
     */
    public static function take(string $string, int $count): string
    {
        if ($count < 0) {
            return self::last($string, abs($count));
        }

        return self::first($string, $count);
    }

    /**
     * Returns the last N characters of a string.
     *
     * Equivalent to `last()` but named for symmetry with `take()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::takeRight('hello world', 5);  // 'world'
     * Strings::takeRight('ñaño', 2);         // 'ño'
     * ```
     *
     * @param string $string The input string
     * @param int $count The number of characters to return from the end
     * @return string The last N characters
     * @see Strings::take()
     * @see Strings::last()
     */
    public static function takeRight(string $string, int $count): string
    {
        return self::last($string, $count);
    }

    /**
     * Converts a string to an array of individual characters.
     *
     * Returns an empty array for an empty string. Each element of the returned array
     * is a single Unicode code point. Multibyte-safe.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::toArray('hello');  // ['h', 'e', 'l', 'l', 'o']
     * Strings::toArray('ñaño');   // ['ñ', 'a', 'ñ', 'o']
     * Strings::toArray('');       // []
     * ```
     *
     * @param string $string The input string to convert
     * @return array<int, string> The array of individual characters
     * @see Strings::split()
     * @see Strings::chunk()
     */
    public static function toArray(string $string): array
    {
        if ($string === '') {
            return [];
        }

        $characters = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);

        return $characters === false ? [] : $characters;
    }

    /**
     * Converts every word in a string to Title Case.
     *
     * Multibyte-safe: uses `mb_convert_case()` with `MB_CASE_TITLE` and UTF-8 encoding.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::title('hello world');  // 'Hello World'
     * Strings::title('HELLO WORLD');  // 'Hello World'
     * ```
     *
     * @param string $string The input string to convert
     * @return string The title-cased string
     * @see Strings::capitalize()
     * @see Strings::upper()
     */
    public static function title(string $string): string
    {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Strips whitespace (or given characters) from the beginning and end of a string.
     *
     * Delegates to PHP's native `trim()`. The `$characters` parameter specifies the
     * characters to strip (default: standard whitespace characters).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::trim('  hello  ');          // 'hello'
     * Strings::trim('***hello***', '*');   // 'hello'
     * ```
     *
     * @param string $string The input string to trim
     * @param string $characters The characters to strip (default: whitespace)
     * @return string The trimmed string
     * @see Strings::trimLeft()
     * @see Strings::trimRight()
     * @see Strings::squish()
     */
    public static function trim(string $string, string $characters = " \t\n\r\0\x0B"): string
    {
        return trim($string, $characters);
    }

    /**
     * Strips whitespace (or given characters) from the beginning of a string.
     *
     * Delegates to PHP's native `ltrim()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::trimLeft('  hello  ');          // 'hello  '
     * Strings::trimLeft('***hello***', '*');   // 'hello***'
     * ```
     *
     * @param string $string The input string to trim
     * @param string $characters The characters to strip (default: whitespace)
     * @return string The left-trimmed string
     * @see Strings::trimRight()
     * @see Strings::trim()
     */
    public static function trimLeft(string $string, string $characters = " \t\n\r\0\x0B"): string
    {
        return ltrim($string, $characters);
    }

    /**
     * Strips whitespace (or given characters) from the end of a string.
     *
     * Delegates to PHP's native `rtrim()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::trimRight('  hello  ');          // '  hello'
     * Strings::trimRight('***hello***', '*');   // '***hello'
     * ```
     *
     * @param string $string The input string to trim
     * @param string $characters The characters to strip (default: whitespace)
     * @return string The right-trimmed string
     * @see Strings::trimLeft()
     * @see Strings::trim()
     */
    public static function trimRight(string $string, string $characters = " \t\n\r\0\x0B"): string
    {
        return rtrim($string, $characters);
    }

    /**
     * Truncates a string to an exact character length without appending any marker.
     *
     * Returns the string unchanged when its length is within the limit. Unlike `limit()`,
     * no omission marker is added — the string is simply cut at `$length`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::truncate('Hello World', 5);       // 'Hello'
     * Strings::truncate('Hello World', 5, '…'); // 'Hello…'
     * Strings::truncate('Hi', 5);               // 'Hi'
     * ```
     *
     * @param string $string The input string to truncate
     * @param int $length The maximum number of characters to keep
     * @param string $end The string to append after truncation (default: '')
     * @return string The truncated string
     * @see Strings::limit()
     */
    public static function truncate(string $string, int $length, string $end = ''): string
    {
        if (self::length($string) <= $length) {
            return $string;
        }

        return mb_substr($string, 0, $length, 'UTF-8') . $end;
    }

    /**
     * Removes a surrounding wrapper string from both ends of a string.
     *
     * Only removes the wrapper when `$string` starts AND ends with `$wrapper`. Returns
     * the string unchanged when `$wrapper` is empty or the string is too short to be
     * wrapped.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::unwrap('"hello"', '"');       // 'hello'
     * Strings::unwrap('[hello]', '[');       // '[hello]' (no matching end)
     * Strings::unwrap('hello', '"');         // 'hello'
     * ```
     *
     * @param string $string The input string to unwrap
     * @param string $wrapper The wrapper string to remove from both ends
     * @return string The unwrapped string
     * @see Strings::wrap()
     */
    public static function unwrap(string $string, string $wrapper): string
    {
        if ($wrapper === '') {
            return $string;
        }

        $wrapperLength = mb_strlen($wrapper, 'UTF-8');
        $stringLength = self::length($string);

        if ($stringLength < $wrapperLength * 2) {
            return $string;
        }

        if (self::startsWith($string, $wrapper) && self::endsWith($string, $wrapper)) {
            return mb_substr($string, $wrapperLength, $stringLength - ($wrapperLength * 2), 'UTF-8');
        }

        return $string;
    }

    /**
     * Converts a string to uppercase.
     *
     * Multibyte-safe: uses `mb_strtoupper()` with UTF-8 encoding.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::upper('hello');  // 'HELLO'
     * Strings::upper('ñoño');   // 'ÑOÑO'
     * Strings::upper('äöü');    // 'ÄÖÜ'
     * ```
     *
     * @param string $string The input string to uppercase
     * @return string The uppercased string
     * @see Strings::lower()
     * @see Strings::capitalize()
     */
    public static function upper(string $string): string
    {
        return mb_strtoupper($string, 'UTF-8');
    }

    /**
     * Generates a version 4 UUID (random).
     *
     * Uses `random_bytes()` where available, falling back to `mt_rand()` on failure.
     * Sets the version (4) and variant bits per RFC 4122.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::uuid();  // e.g. '550e8400-e29b-41d4-a716-446655440000'
     * ```
     *
     * @return string A random UUID v4 string
     * @see Strings::random()
     * @see Strings::isUuid()
     */
    public static function uuid(): string
    {
        try {
            $data = random_bytes(16);
        } catch (RandomException) {
            $data = '';

            for ($i = 0; $i < 16; $i++) {
                $data .= chr(mt_rand(0, 255));
            }
        }

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return sprintf(
            '%s-%s-%s-%s-%s',
            bin2hex(mb_substr($data, 0, 4, '8bit')),
            bin2hex(mb_substr($data, 4, 2, '8bit')),
            bin2hex(mb_substr($data, 6, 2, '8bit')),
            bin2hex(mb_substr($data, 8, 2, '8bit')),
            bin2hex(mb_substr($data, 10, 6, '8bit'))
        );
    }

    /**
     * Returns the number of words in a string.
     *
     * A word is any sequence of Unicode letters and numbers. Returns zero for blank strings.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::wordCount('hello world');   // 2
     * Strings::wordCount('  ');            // 0
     * ```
     *
     * @param string $string The input string to count words in
     * @return int The number of words
     * @see Strings::words()
     */
    public static function wordCount(string $string): int
    {
        if (self::isBlank($string)) {
            return 0;
        }

        return count(self::words($string));
    }

    /**
     * Wraps a string at a given number of characters, inserting a break string.
     *
     * Delegates to PHP's native `wordwrap()`. When `$cutLongWords` is true, words
     * longer than `$width` characters are broken at exactly `$width`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::wordWrap('The quick brown fox', 10);
     * // "The quick\nbrown fox"
     * ```
     *
     * @param string $string The input string to wrap
     * @param int $width The number of characters at which to wrap (default: 75)
     * @param string $break The line break string to insert (default: "\n")
     * @param bool $cutLongWords Whether to cut words longer than `$width` (default: false)
     * @return string The word-wrapped string
     * @see Strings::truncate()
     * @see Strings::limit()
     */
    public static function wordWrap(
        string $string,
        int $width = 75,
        string $break = "\n",
        bool $cutLongWords = false
    ): string {
        if ($string === '') {
            return $string;
        }

        $lines = [];
        $currentLine = '';
        $currentLength = 0;

        foreach (explode(' ', $string) as $word) {
            $wordLength = mb_strlen($word, 'UTF-8');

            if ($cutLongWords && $wordLength > $width) {
                if ($currentLength > 0) {
                    $lines[] = $currentLine;
                    $currentLine = '';
                    $currentLength = 0;
                }

                while (mb_strlen($word, 'UTF-8') > 0) {
                    $available = $width - $currentLength;
                    $chunk = mb_substr($word, 0, $available, 'UTF-8');
                    $currentLine .= $chunk;
                    $currentLength += mb_strlen($chunk, 'UTF-8');
                    $word = mb_substr($word, $available, null, 'UTF-8');

                    if (mb_strlen($word, 'UTF-8') > 0) {
                        $lines[] = $currentLine;
                        $currentLine = '';
                        $currentLength = 0;
                    }
                }
            } elseif ($currentLength === 0) {
                $currentLine = $word;
                $currentLength = $wordLength;
            } elseif ($currentLength + 1 + $wordLength <= $width) {
                $currentLine .= ' ' . $word;
                $currentLength += 1 + $wordLength;
            } else {
                $lines[] = $currentLine;
                $currentLine = $word;
                $currentLength = $wordLength;
            }
        }

        $lines[] = $currentLine;

        return implode($break, $lines);
    }

    /**
     * Extracts the words from a string into an array.
     *
     * A word is any sequence of Unicode letters, numbers, and apostrophes. Returns an
     * empty array for blank strings. When `$limit` is non-negative, only the first
     * `$limit` words are returned, and `$end` is appended as a final element if provided.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::words("hello world");        // ['hello', 'world']
     * Strings::words("it's a test", 2, '…'); // ['it\'s', 'a', '…']
     * ```
     *
     * @param string $string The input string to extract words from
     * @param int $limit Maximum number of words to return, -1 = no limit (default: -1)
     * @param string $end String appended after the word list when limited (default: '')
     * @return array<int, string> The array of words
     * @see Strings::wordCount()
     * @see Strings::split()
     */
    public static function words(string $string, int $limit = -1, string $end = ''): array
    {
        if (self::isBlank($string)) {
            return [];
        }

        preg_match_all('/[\p{L}\p{N}\']+/u', $string, $matches);
        $words = $matches[0];

        if ($limit >= 0 && count($words) > $limit) {
            $words = array_slice($words, 0, $limit);
        }

        if ($end !== '' && $limit >= 0) {
            $words[] = $end;
        }

        return $words;
    }

    /**
     * Wraps a string with a given wrapper string on both sides.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::wrap('hello', '"');   // '"hello"'
     * Strings::wrap('hello', '[]'); // '[]hello[]'
     * ```
     *
     * @param string $string The input string to wrap
     * @param string $wrapper The string to prepend and append
     * @return string The wrapped string
     * @see Strings::unwrap()
     */
    public static function wrap(string $string, string $wrapper): string
    {
        return $wrapper . $string . $wrapper;
    }

    /**
     * Transliterates accented and non-ASCII characters to their ASCII equivalents.
     *
     * Applies a language-specific substitution table. Currently supports 'en' (default)
     * and 'de' (German umlaut expansion: ä → ae, ö → oe, ü → ue).
     *
     * @param string $string The input string to transliterate
     * @param string $language The language code for locale-specific rules
     * @return string The transliterated string (may still contain non-ASCII characters)
     */
    private static function transliterateToAscii(string $string, string $language = 'en'): string
    {
        $transliterations = [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y', 'Þ' => 'Th', 'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y',
            'Ł' => 'L', 'ł' => 'l',
            'Ń' => 'N', 'ń' => 'n', 'Ś' => 'S', 'ś' => 's',
            'Ź' => 'Z', 'ź' => 'z', 'Ż' => 'Z', 'ż' => 'z',
        ];

        if ($language === 'de') {
            $transliterations['Ä'] = 'Ae';
            $transliterations['Ö'] = 'Oe';
            $transliterations['Ü'] = 'Ue';
            $transliterations['ä'] = 'ae';
            $transliterations['ö'] = 'oe';
            $transliterations['ü'] = 'ue';
        }

        return strtr($string, $transliterations);
    }
}
