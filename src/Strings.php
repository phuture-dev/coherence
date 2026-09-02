<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use Random\RandomException;
use Phuture\Coherence\Support\StaticClass;
use Phuture\Coherence\Exception\InvalidArgumentException;
use Phuture\Coherence\Enum\{CharCountMode, PadDirection, UuidVersion};

/**
 * Comprehensive string manipulation utility class with multibyte-safe operations.
 *
 * This utility class provides a complete toolkit for string manipulation, including case
 * conversion, pattern matching, extraction, modification, splitting, joining, validation,
 * truncation, encoding conversion, and miscellaneous string operations.
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
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Strings extends StaticClass
{
    /**
     * Highest number of decimal places accepted by `numberFormat()`.
     *
     * Formatting with an arbitrarily large number of decimals allocates a string of that length,
     * which exhausts memory long before the result is useful. PHP 8.6 rejects out-of-range values
     * outright, so the limit is enforced here to keep the behaviour identical across versions.
     */
    public const MAX_DECIMALS = 100;

    /**
     * Escapes specific characters in a string using C-style backslash notation.
     *
     * Wraps PHP's native `addcslashes()`. Characters listed in `$characters` are
     * escaped with backslashes. Supports ranges like `\n..\r` and `\0..\31`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::addCSlashes('hello world', 'aeiou'); // 'h\\ell\\o w\\orld'
     * Strings::addCSlashes("hello\x00world", "\x00"); // 'hello\0world'
     * ```
     *
     * @param string $string The input string to escape
     * @param string $characters The list of characters to escape
     * @return string The C-style escaped string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$characters` is empty
     * @see \Phuture\Coherence\Strings::stripCSlashes()
     */
    public static function addCSlashes(string $string, string $characters): string
    {
        if ($characters === '') {
            throw new InvalidArgumentException(
                "Invalid Argument: Characters to escape must not be empty"
            );
        }

        return addcslashes($string, $characters);
    }

    /**
     * Escapes single quotes, double quotes, backslashes, and NUL bytes in a string.
     *
     * Wraps PHP's native `addslashes()`. Useful for preparing strings for database
     * queries or other contexts that require backslash escaping.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::addSlashes("hello 'world'"); // "hello \\'world\\'"
     * Strings::addSlashes('path\\to\\file'); // 'path\\\\to\\\\file'
     * ```
     *
     * @param string $string The input string to escape
     * @return string The escaped string
     * @see \Phuture\Coherence\Strings::stripSlashes()
     */
    public static function addSlashes(string $string): string
    {
        return addslashes($string);
    }
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
     * Strings::after('user@example.com', '@'); // 'example.com'
     * Strings::after('2023-12-25', '-'); // '12-25'
     * Strings::after('hello', 'x'); // ''
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to search for
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The substring after the first occurrence, or an empty string if not found
     * @see \Phuture\Coherence\Strings::afterLast()
     * @see \Phuture\Coherence\Strings::before()
     */
    public static function after(string $string, string $search, string $encoding = 'UTF-8'): string
    {
        if ($search === '') {
            return $string;
        }

        $position = mb_strpos($string, $search, 0, $encoding);

        if ($position === false) {
            return '';
        }

        return mb_substr($string, $position + mb_strlen($search, $encoding), null, $encoding);
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
     * Strings::afterLast('path/to/file.txt', '/'); // 'file.txt'
     * Strings::afterLast('a.b.c', '.'); // 'c'
     * Strings::afterLast('hello', 'x'); // ''
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to search for
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The substring after the last occurrence, or an empty string if not found
     * @see \Phuture\Coherence\Strings::after()
     * @see \Phuture\Coherence\Strings::beforeLast()
     */
    public static function afterLast(string $string, string $search, string $encoding = 'UTF-8'): string
    {
        if ($search === '') {
            return $string;
        }

        $position = mb_strrpos($string, $search, 0, $encoding);

        if ($position === false) {
            return '';
        }

        return mb_substr($string, $position + mb_strlen($search, $encoding), null, $encoding);
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
     * Strings::ascii('héllo'); // 'hello'
     * Strings::ascii('ñaño'); // 'nano'
     * Strings::ascii('über', 'de'); // 'ueber'
     * ```
     *
     * @param string $string The input string to transliterate
     * @param string $language The language code for locale-specific rules (default: 'en')
     * @return string The ASCII-safe string
     * @see \Phuture\Coherence\Strings::slug()
     */
    public static function ascii(string $string, string $language = 'en'): string
    {
        $string = self::transliterateToAscii($string, $language);

        return preg_replace('/[^\x20-\x7E]/', '', $string) ?? '';
    }

    /**
     * Generates an ASCII art representation of the given text using a block font.
     *
     * Renders each character of `$text` as a 5-row tall block-style ASCII art figure.
     * Supports uppercase and lowercase letters A–Z (normalised to uppercase), digits 0–9,
     * and common punctuation. Unsupported characters are rendered as blank columns.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * echo Strings::asciiArt('Hi');
     * // #    # ######
     * // #    #   ##
     * // ######   ##
     * // #    #   ##
     * // #    # ######
     * ```
     *
     * @param string $text The text to render as ASCII art
     * @param string $font The font name to use — currently only 'block' is supported
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The multi-line ASCII art string
     * @see \Phuture\Coherence\Strings::ascii()
     */
    public static function asciiArt(string $text, string $font = 'block', string $encoding = 'UTF-8'): string
    {
        $fontMap = self::getAsciiFontMap($font);
        $text = mb_strtoupper($text, $encoding);
        $chars = mb_str_split($text, 1, $encoding);

        $rows = 5;
        $lines = array_fill(0, $rows, '');

        foreach ($chars as $char) {
            $charData = $fontMap[$char] ?? $fontMap[' '];

            for ($i = 0; $i < $rows; $i++) {
                $lines[$i] .= ($lines[$i] !== '' ? ' ' : '') . $charData[$i];
            }
        }

        return implode("\n", $lines);
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
     * Strings::before('user@example.com', '@'); // 'user'
     * Strings::before('2023-12-25', '-'); // '2023'
     * Strings::before('hello', 'x'); // 'hello'
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to search for
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The substring before the first occurrence, or the original string if not found
     * @see \Phuture\Coherence\Strings::beforeLast()
     * @see \Phuture\Coherence\Strings::after()
     */
    public static function before(string $string, string $search, string $encoding = 'UTF-8'): string
    {
        if ($search === '') {
            return $string;
        }

        $position = mb_strpos($string, $search, 0, $encoding);

        if ($position === false) {
            return $string;
        }

        return mb_substr($string, 0, $position, $encoding);
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
     * Strings::beforeLast('path/to/file.txt', '/'); // 'path/to'
     * Strings::beforeLast('a.b.c', '.'); // 'a.b'
     * Strings::beforeLast('hello', 'x'); // 'hello'
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to search for
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The substring before the last occurrence, or the original string if not found
     * @see \Phuture\Coherence\Strings::before()
     * @see \Phuture\Coherence\Strings::afterLast()
     */
    public static function beforeLast(string $string, string $search, string $encoding = 'UTF-8'): string
    {
        if ($search === '') {
            return $string;
        }

        $position = mb_strrpos($string, $search, 0, $encoding);

        if ($position === false) {
            return $string;
        }

        return mb_substr($string, 0, $position, $encoding);
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
     * Strings::between('[hello]', '[', ']'); // 'hello'
     * Strings::between('user@example.com', '@', '.'); // 'example'
     * Strings::between('hello', '{', '}'); // 'hello'
     * ```
     *
     * @param string $string The input string to search within
     * @param string $start The opening delimiter
     * @param string $end The closing delimiter
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The substring between the delimiters, or the original string if not found
     * @see \Phuture\Coherence\Strings::before()
     * @see \Phuture\Coherence\Strings::after()
     */
    public static function between(string $string, string $start, string $end, string $encoding = 'UTF-8'): string
    {
        if ($start === '' || $end === '') {
            return $string;
        }

        $startPosition = mb_strpos($string, $start, 0, $encoding);

        if ($startPosition === false) {
            return $string;
        }

        $startPosition += mb_strlen($start, $encoding);
        $endPosition = mb_strpos($string, $end, $startPosition, $encoding);

        if ($endPosition === false) {
            return $string;
        }

        return mb_substr($string, $startPosition, $endPosition - $startPosition, $encoding);
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
     * Strings::camel('hello world'); // 'helloWorld'
     * Strings::camel('hello_world'); // 'helloWorld'
     * Strings::camel('hello-world'); // 'helloWorld'
     * Strings::camel('HelloWorld'); // 'helloWorld'
     * ```
     *
     * @param string $string The input string to convert
     * @return string The camelCase version of the string
     * @see \Phuture\Coherence\Strings::pascal()
     * @see \Phuture\Coherence\Strings::snake()
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
     * Censors all occurrences of banned words in a string by replacing them with a substitution.
     *
     * Matching is case-insensitive. Each matched word is replaced with `$replacement` in full,
     * regardless of the matched word's length. The string is returned unchanged when `$bannedWords`
     * is empty.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::censor('This is bad and awful', ['bad', 'awful']); // 'This is *** and ***'
     * Strings::censor('BAD language', ['bad'], '####'); // '#### language'
     * ```
     *
     * @param string $string The input string to censor
     * @param array $bannedWords List of word strings to replace
     * @param string $replacement The string to substitute for each matched word (default: '***')
     * @return string The censored string
     * @see \Phuture\Coherence\Strings::replace()
     */
    public static function censor(string $string, array $bannedWords, string $replacement = '***'): string
    {
        if (empty($bannedWords)) {
            return $string;
        }

        $pattern = '/\b(?:' . implode('|', array_map(
            static fn (string $word): string => preg_quote($word, '/'),
            $bannedWords
        )) . ')\b/iu';

        return preg_replace($pattern, $replacement, $string) ?? $string;
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
     * Strings::charAt('hello', 0); // 'h'
     * Strings::charAt('hello', -1); // 'o'
     * Strings::charAt('hello', 10); // ''
     * ```
     *
     * @param string $string The input string to index into
     * @param int $index The zero-based character index (negative counts from the end)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The character at the given position, or an empty string if out of bounds
     * @see \Phuture\Coherence\Strings::slice()
     */
    public static function charAt(string $string, int $index, string $encoding = 'UTF-8'): string
    {
        $length = self::length($string);

        if ($index < 0) {
            $index += $length;
        }

        if ($index < 0 || $index >= $length) {
            return '';
        }

        return mb_substr($string, $index, 1, $encoding);
    }

    /**
     * Returns information about the byte values used in a string.
     *
     * `CharCountMode::All` returns an array with all 256 possible byte values as keys and
     * their frequency as values. `CharCountMode::Present` returns only byte values with a
     * count greater than zero. `CharCountMode::Absent` returns only byte values with a
     * count of zero. `CharCountMode::Unique` returns a string containing all unique byte
     * values found.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     * use Phuture\Coherence\Enum\CharCountMode;
     *
     * $counts = Strings::charCounts('hello', CharCountMode::Present);
     * // $counts[104] is 1 (one 'h'), $counts[108] is 2 (two 'l's)
     *
     * $unique = Strings::charCounts('hello', CharCountMode::Unique);
     * // 'ehlo' — unique bytes sorted
     * ```
     *
     * @param string $string The input string to analyze
     * @param \Phuture\Coherence\Enum\CharCountMode $mode The return mode — All, Present, Absent
     *  or Unique (default: CharCountMode::All)
     * @return array|int|string The result depends on `$mode`
     * @see \Phuture\Coherence\Enum\CharCountMode
     */
    public static function charCounts(string $string, CharCountMode $mode = CharCountMode::All): array|int|string
    {
        return count_chars($string, match ($mode) {
            CharCountMode::All => 0,
            CharCountMode::Present => 1,
            CharCountMode::Absent => 2,
            CharCountMode::Unique => 3,
        });
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
     * Strings::chunk('abcdef', 2); // ['ab', 'cd', 'ef']
     * Strings::chunk('hello', 3); // ['hel', 'lo']
     * ```
     *
     * @param string $string The input string to split
     * @param int $size The number of characters per chunk
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return array Array of string chunks, indexed sequentially from zero
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$size` is less than or equal to zero
     * @see \Phuture\Coherence\Strings::split()
     */
    public static function chunk(string $string, int $size, string $encoding = 'UTF-8'): array
    {
        if ($size <= 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: Chunk size must be greater than zero"
            );
        }

        $chunks = [];
        $length = self::length($string);

        for ($i = 0; $i < $length; $i += $size) {
            $chunks[] = mb_substr($string, $i, $size, $encoding);
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
     * Strings::compare('apple', 'banana'); // negative
     * Strings::compare('banana', 'apple'); // positive
     * Strings::compare('hello', 'hello'); // 0
     * Strings::compare('Hello', 'hello', false); // 0
     * ```
     *
     * @param string $string The first string to compare
     * @param string $other The second string to compare against
     * @param bool $caseSensitive Whether the comparison is case-sensitive (default: true)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return int Negative if less than, 0 if equal, positive if greater than
     * @see \Phuture\Coherence\Strings::equals()
     */
    public static function compare(
        string $string,
        string $other,
        bool $caseSensitive = true,
        string $encoding = 'UTF-8'
    ): int {
        if (!$caseSensitive) {
            $string = self::lower($string);
            $other = self::lower($other);
        }

        $length = min(mb_strlen($string, $encoding), mb_strlen($other, $encoding));

        for ($i = 0; $i < $length; $i++) {
            $charA = mb_substr($string, $i, 1, $encoding);
            $charB = mb_substr($other, $i, 1, $encoding);
            $comparison = $charA <=> $charB;

            if ($comparison !== 0) {
                return $comparison;
            }
        }

        return mb_strlen($string, $encoding) - mb_strlen($other, $encoding);
    }

    /**
     * Compares two strings using a "natural order" algorithm.
     *
     * Natural order comparison arranges strings the way a human would. For example,
     * "img2" comes before "img10" in natural order (unlike lexicographic order).
     * Returns a negative integer, zero, or a positive integer depending on whether
     * the first string is less than, equal to, or greater than the second string.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::compareNatural('img2', 'img10'); // negative (img2 < img10)
     * Strings::compareNatural('img10', 'img2'); // positive (img10 > img2)
     * Strings::compareNatural('hello', 'hello'); // 0
     * Strings::compareNatural('Hello', 'hello', false); // 0
     * ```
     *
     * @param string $string The first string to compare
     * @param string $other The second string to compare against
     * @param bool $caseSensitive Whether the comparison is case-sensitive (default: true)
     * @return int Negative if less than, 0 if equal, positive if greater than
     * @see \Phuture\Coherence\Strings::compare()
     */
    public static function compareNatural(string $string, string $other, bool $caseSensitive = true): int
    {
        if ($caseSensitive) {
            return strnatcmp($string, $other);
        }

        return strnatcasecmp($string, $other);
    }

    /**
     * Determines whether a string contains a given search value.
     *
     * An empty `$search` always returns true. Supports optional case-insensitive matching.
     * This method wraps PHP's native `str_contains()` with multibyte-safe handling
     * and additional case-insensitive support.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::contains('hello world', 'world'); // true
     * Strings::contains('hello world', 'World', false); // true (case-insensitive)
     * Strings::contains('hello world', 'xyz'); // false
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to look for
     * @param bool $caseSensitive Whether the search is case-sensitive (default: true)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return bool True when the string contains the search value
     * @see \Phuture\Coherence\Strings::containsAll()
     * @see \Phuture\Coherence\Strings::containsNone()
     */
    public static function contains(
        string $string,
        string $search,
        bool $caseSensitive = true,
        string $encoding = 'UTF-8'
    ): bool {
        if ($search === '') {
            return true;
        }

        if ($caseSensitive) {
            return mb_strpos($string, $search, 0, $encoding) !== false;
        }

        return mb_stripos($string, $search, 0, $encoding) !== false;
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
     * Strings::containsAll('hello world', ['hello', 'world']); // true
     * Strings::containsAll('hello world', ['hello', 'xyz']); // false
     * ```
     *
     * @param string $string The input string to search within
     * @param array $searches The values to look for; each element must be a string
     * @param bool $caseSensitive Whether the searches are case-sensitive (default: true)
     * @return bool True when all search values are found
     * @see \Phuture\Coherence\Strings::contains()
     * @see \Phuture\Coherence\Strings::containsNone()
     */
    public static function containsAll(string $string, array $searches, bool $caseSensitive = true): bool
    {
        foreach ($searches as $search) {
            if (!self::contains($string, $search, $caseSensitive)) {
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
     * Strings::containsNone('hello world', ['foo', 'bar']); // true
     * Strings::containsNone('hello world', ['hello', 'bar']); // false
     * ```
     *
     * @param string $string The input string to search within
     * @param array $searches The values to check for absence; each element must be a string
     * @param bool $caseSensitive Whether the searches are case-sensitive (default: true)
     * @return bool True when none of the search values are found
     * @see \Phuture\Coherence\Strings::contains()
     * @see \Phuture\Coherence\Strings::containsAll()
     */
    public static function containsNone(string $string, array $searches, bool $caseSensitive = true): bool
    {
        foreach ($searches as $search) {
            if (self::contains($string, $search, $caseSensitive)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Counts the number of non-overlapping times a given text appears in a string.
     *
     * Returns zero when `$search` is an empty string or is not found in `$string`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::countOccurrences('hello world hello', 'hello'); // 2
     * Strings::countOccurrences('aaaa', 'aa'); // 2
     * Strings::countOccurrences('hello', 'xyz'); // 0
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The text to count
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return int The number of non-overlapping times the text appears
     * @see \Phuture\Coherence\Strings::contains()
     */
    public static function countOccurrences(string $string, string $search, string $encoding = 'UTF-8'): int
    {
        if ($search === '') {
            return 0;
        }

        $count = 0;
        $offset = 0;
        $searchLength = mb_strlen($search, $encoding);

        while (($position = mb_strpos($string, $search, $offset, $encoding)) !== false) {
            $count++;
            $offset = $position + $searchLength;
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
     * Strings::dedupe('hello    world'); // 'hello world'
     * Strings::dedupe('a,,,b,,,c', ','); // 'a,b,c'
     * Strings::dedupe('---test---', '-'); // '-test-'
     * ```
     *
     * @param string $string The input string to deduplicate
     * @param string $character The character to collapse (default: space)
     * @return string The string with consecutive duplicate characters collapsed
     * @see \Phuture\Coherence\Strings::squish()
     */
    public static function dedupe(string $string, string $character = ' '): string
    {
        if ($character === '') {
            return $string;
        }

        $escaped = preg_quote($character, '/');

        return preg_replace('/' . $escaped . '+/u', $character, $string) ?? '';
    }

    /**
     * Calculates the Levenshtein edit distance between two strings.
     *
     * The edit distance is the minimum number of single-character edits (insertions,
     * replacements, or deletions) required to transform one string into the other.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::distance('hello', 'hello'); // 0
     * Strings::distance('hello', 'hallo'); // 1
     * Strings::distance('kitten', 'sitting'); // 3
     * ```
     *
     * @param string $string The first string
     * @param string $other The second string
     * @return int The minimum number of edits needed to transform one string into the other
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When either string exceeds 255 bytes
     * @see \Phuture\Coherence\Strings::similar()
     */
    public static function distance(string $string, string $other): int
    {
        if (strlen($string) > 255 || strlen($other) > 255) {
            throw new InvalidArgumentException(
                "Invalid Argument: Strings must not exceed 255 bytes for Levenshtein distance"
            );
        }

        return levenshtein($string, $other);
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
     * Strings::endsWith('image.jpg', '.jpg'); // true
     * Strings::endsWith('hello world', 'world'); // true
     * Strings::endsWith('hello', 'Hello'); // false
     * ```
     *
     * @param string $string The input string to check
     * @param string $search The expected suffix
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return bool True when the string ends with the search value
     * @see \Phuture\Coherence\Strings::startsWith()
     */
    public static function endsWith(string $string, string $search, string $encoding = 'UTF-8'): bool
    {
        if ($search === '') {
            return true;
        }

        return mb_substr($string, -mb_strlen($search, $encoding), null, $encoding) === $search;
    }

    /**
     * Converts HTML entities back to their corresponding characters.
     *
     * Reverses the encoding performed by `entityEncode()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::entityDecode('hello &amp; &quot;world&quot;'); // 'hello & "world"'
     * Strings::entityDecode('caf&eacute;'); // 'café'
     * ```
     *
     * @param string $string The HTML-entity-encoded string to decode
     * @param int $flags Bitmask of ENT_* constants (default: ENT_QUOTES | ENT_SUBSTITUTE)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The decoded string
     * @see \Phuture\Coherence\Strings::entityEncode()
     */
    public static function entityDecode(
        string $string,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE,
        string $encoding = 'UTF-8'
    ): string {
        return html_entity_decode($string, $flags, $encoding);
    }

    /**
     * Converts all applicable characters to HTML entities.
     *
     * Translates characters that have HTML entity equivalents (like `&`, `<`, `>`,
     * accented characters, etc.) into their entity representations.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::entityEncode('hello & "world"'); // 'hello &amp; &quot;world&quot;'
     * Strings::entityEncode('café'); // 'caf&eacute;'
     * ```
     *
     * @param string $string The input string to encode
     * @param int $flags Bitmask of ENT_* constants (default: ENT_QUOTES | ENT_SUBSTITUTE)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The HTML-entity-encoded string
     * @see \Phuture\Coherence\Strings::entityDecode()
     */
    public static function entityEncode(
        string $string,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE,
        string $encoding = 'UTF-8'
    ): string {
        return htmlentities($string, $flags, $encoding);
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
     * Strings::equals('hello', 'hello'); // true
     * Strings::equals('Hello', 'hello'); // false
     * Strings::equals('Hello', 'hello', false); // true
     * ```
     *
     * @param string $string The first string to compare
     * @param string $other The second string to compare against
     * @param bool $caseSensitive Whether the comparison is case-sensitive (default: true)
     * @return bool True when the strings are equal
     * @see \Phuture\Coherence\Strings::compare()
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
     * @param int $radius The number of characters to include on each side; must be zero or greater (default: 100)
     * @param string $omission The string to append at truncated ends (default: '...')
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The contextual excerpt
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$radius` is negative
     * @see \Phuture\Coherence\Strings::limit()
     * @see \Phuture\Coherence\Strings::limitWords()
     */
    public static function excerpt(
        string $string,
        string $phrase,
        int $radius = 100,
        string $omission = '...',
        string $encoding = 'UTF-8'
    ): string {
        if ($radius < 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: Radius must be zero or greater"
            );
        }

        if ($string === '') {
            return '';
        }

        if ($phrase === '') {
            return self::limit($string, $radius * 2, $omission);
        }

        $loweredString = self::lower($string);
        $loweredPhrase = self::lower($phrase);
        $position = mb_strpos($loweredString, $loweredPhrase, 0, $encoding);

        if ($position === false) {
            return self::limit($string, $radius, $omission);
        }

        $start = max(0, $position - $radius);
        $end = min(self::length($string), $position + self::length($phrase) + $radius);
        $excerpt = mb_substr($string, $start, $end - $start, $encoding);

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
     * Strings::explode('a,b,c', ','); // ['a', 'b', 'c']
     * Strings::explode('a,b,c', ',', 2); // ['a', 'b,c']
     * ```
     *
     * @param string $string The input string to split
     * @param string $delimiter The boundary string
     * @param int $limit Maximum number of returned elements (default: PHP_INT_MAX)
     * @return array Array of substrings, indexed sequentially from zero
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$delimiter` is an empty string
     * @see \Phuture\Coherence\Strings::split()
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
     * Strings::finish('path/to', '/'); // 'path/to/'
     * Strings::finish('path/to/', '/'); // 'path/to/'
     * Strings::finish('path/to///', '/'); // 'path/to/'
     * ```
     *
     * @param string $string The input string
     * @param string $suffix The suffix to ensure is present exactly once
     * @return string The string guaranteed to end with the suffix
     * @see \Phuture\Coherence\Strings::start()
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
     * Strings::first('hello', 3); // 'hel'
     * Strings::first('ñaño', 2); // 'ña'
     * Strings::first('hello'); // 'h'
     * ```
     *
     * @param string $string The input string
     * @param int $count The number of characters to return (default: 1)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The first N characters
     * @see \Phuture\Coherence\Strings::last()
     * @see \Phuture\Coherence\Strings::take()
     */
    public static function first(string $string, int $count = 1, string $encoding = 'UTF-8'): string
    {
        if ($count <= 0) {
            return '';
        }

        return mb_substr($string, 0, $count, $encoding);
    }

    /**
     * Fixes invalid UTF-8 byte sequences in a string.
     *
     * Removes or replaces any byte sequences that are not valid UTF-8. The result is
     * guaranteed to be valid UTF-8.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::fixEncoding("hello\xc0world"); // 'helloworld' (invalid byte removed)
     * Strings::fixEncoding('valid utf-8 ñoño'); // 'valid utf-8 ñoño'
     * ```
     *
     * @param string $string The input string that may contain invalid encoding sequences
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string A valid string with invalid byte sequences removed
     * @see \Phuture\Coherence\Strings::scrub()
     */
    public static function fixEncoding(string $string, string $encoding = 'UTF-8'): string
    {
        $result = mb_convert_encoding($string, $encoding, $encoding);

        return $result !== false ? $result : $string;
    }

    /**
     * Returns a formatted string using sprintf semantics.
     *
     * Replaces placeholders in `$format` with the provided arguments. Supports
     * all standard sprintf format specifiers (`%s`, `%d`, `%f`, `%02d`, etc.).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::format('Hello, %s!', 'World'); // 'Hello, World!'
     * Strings::format('%04d-%02d-%02d', 2026, 5, 1); // '2026-05-01'
     * ```
     *
     * @param string $format The format string containing placeholders
     * @param mixed ...$args The values to substitute into the placeholders
     * @return string The formatted string
     */
    public static function format(string $format, mixed ...$args): string
    {
        return sprintf($format, ...$args);
    }

    /**
     * Decodes a Base64-encoded string.
     *
     * Returns an empty string when the input is not valid Base64.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::fromBase64('aGVsbG8='); // 'hello'
     * Strings::fromBase64('not-base64!!!'); // ''
     * ```
     *
     * @param string $string The Base64-encoded string to decode
     * @return string The decoded string, or an empty string when decoding fails
     * @see \Phuture\Coherence\Strings::toBase64()
     */
    public static function fromBase64(string $string): string
    {
        $decoded = base64_decode($string, true);

        return $decoded === false ? '' : $decoded;
    }

    /**
     * Decodes a hex-encoded binary string.
     *
     * Reverses the encoding performed by `toHex()`. The input must contain only
     * valid hexadecimal characters (0-9, a-f, A-F) and must have an even length.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::fromHex('68656c6c6f'); // 'hello'
     * ```
     *
     * @param string $string The hexadecimal string to decode
     * @return string|false The decoded binary string, or false when decoding fails
     * @see \Phuture\Coherence\Strings::toHex()
     */
    public static function fromHex(string $string): string|false
    {
        if ($string === '') {
            return '';
        }

        if (strlen($string) % 2 !== 0 || !preg_match('/^[0-9a-fA-F]+$/', $string)) {
            throw new InvalidArgumentException(
                "Invalid Argument: Input must be a valid hexadecimal string with even length"
            );
        }

        return hex2bin($string);
    }

    /**
     * Calculates the Hamming distance between two strings.
     *
     * The Hamming distance is the number of positions at which the corresponding
     * characters are different. Think of it as counting the minimum number of
     * character substitutions needed to turn one string into the other.
     *
     * Both strings must have the same number of characters. This method is
     * multibyte-safe and works correctly with accented characters and other
     * Unicode text — each Unicode character counts as one unit regardless of
     * how many bytes it uses.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::hamming('karolin', 'kathrin'); // 3
     * Strings::hamming('hello', 'hello');     // 0
     * Strings::hamming('', '');               // 0
     * ```
     *
     * @param string $string The first string to compare
     * @param string $other The second string to compare against
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return int The number of positions where the characters differ
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the strings have different character lengths
     * @see \Phuture\Coherence\Strings::distance()
     * @see \Phuture\Coherence\Strings::jaro()
     */
    public static function hamming(string $string, string $other, string $encoding = 'UTF-8'): int
    {
        $firstChars = mb_str_split($string, 1, $encoding);
        $secondChars = mb_str_split($other, 1, $encoding);

        if (count($firstChars) !== count($secondChars)) {
            throw new InvalidArgumentException(
                'Invalid Argument: Strings must be equal length for Hamming distance'
            );
        }

        $distance = 0;

        foreach ($firstChars as $index => $char) {
            if ($char !== $secondChars[$index]) {
                $distance++;
            }
        }

        return $distance;
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
     * Strings::headline('hello_world'); // 'Hello World'
     * Strings::headline('foo-bar-baz'); // 'Foo Bar Baz'
     * Strings::headline('hello world'); // 'Hello World'
     * Strings::headline('helloWorld'); // 'Hello World'
     * ```
     *
     * @param string $string The input string to convert
     * @return string The headline-formatted string
     * @see \Phuture\Coherence\Strings::pascal()
     * @see \Phuture\Coherence\Strings::title()
     */
    public static function headline(string $string): string
    {
        $parts = preg_split('/[\s\-_]+|(?<!^)(?=[A-Z])/u', $string, -1, PREG_SPLIT_NO_EMPTY);

        if ($parts === false || $parts === []) {
            return '';
        }

        $parts = array_map(fn ($part) => self::title($part), $parts);

        return implode(' ', $parts);
    }

    /**
     * Highlights all occurrences of a phrase within a string by wrapping them in tags.
     *
     * Matching is case-insensitive. The original casing of the matched text is preserved
     * inside the tags. Returns the string unchanged when `$phrase` is empty.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::highlight('The quick brown fox', 'quick'); // 'The <mark>quick</mark> brown fox'
     * Strings::highlight('Hello World', 'world', '<b>', '</b>'); // 'Hello <b>World</b>'
     * ```
     *
     * @param string $string The input string to search within
     * @param string $phrase The phrase to highlight
     * @param string $tagOpen The opening tag to insert before each match (default: '<mark>')
     * @param string $tagClose The closing tag to insert after each match (default: '</mark>')
     * @return string The string with all occurrences of `$phrase` wrapped in the given tags
     * @see \Phuture\Coherence\Strings::replace()
     */
    public static function highlight(
        string $string,
        string $phrase,
        string $tagOpen = '<mark>',
        string $tagClose = '</mark>'
    ): string {
        if ($phrase === '') {
            return $string;
        }

        $pattern = '/' . preg_quote($phrase, '/') . '/iu';

        return preg_replace($pattern, $tagOpen . '$0' . $tagClose, $string) ?? $string;
    }

    /**
     * Adds indentation to each line of a string.
     *
     * Prepends `$indentChar` repeated `$level` times to every line. A line is defined
     * as any sequence ending with `\n`. Throws when `$level` is negative.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::indent("line1\nline2"); // "\tline1\n\tline2"
     * Strings::indent("line1\nline2", 2); // "\t\tline1\n\t\tline2"
     * Strings::indent("line1\nline2", 1, '  '); // "  line1\n  line2"
     * ```
     *
     * @param string $string The input string to indent
     * @param int $level The number of times to repeat the indent character; must be zero or greater (default: 1)
     * @param string $indentChar The character(s) used for one level of indentation (default: "\t")
     * @return string The indented string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$level` is negative
     */
    public static function indent(string $string, int $level = 1, string $indentChar = "\t"): string
    {
        if ($level < 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: Indent level must be zero or greater"
            );
        }

        if ($level === 0 || $string === '') {
            return $string;
        }

        $prefix = str_repeat($indentChar, $level);

        return $prefix . str_replace("\n", "\n" . $prefix, $string);
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
     * Strings::insert('hello world', '!', 5); // 'hello! world'
     * Strings::insert('hello world', '!', -1); // 'hello worl!d'
     * ```
     *
     * @param string $string The input string to insert into
     * @param string $substring The substring to insert
     * @param int $index The zero-based position to insert at (negative counts from the end)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The string with the substring inserted
     * @see \Phuture\Coherence\Strings::slice()
     */
    public static function insert(string $string, string $substring, int $index, string $encoding = 'UTF-8'): string
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

        $before = mb_substr($string, 0, $index, $encoding);
        $after = mb_substr($string, $index, null, $encoding);

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
     * Strings::is('user_123', 'user_*'); // true
     * Strings::is('photo.jpg', '*.jpg'); // true
     * Strings::is('test.jpg', '*.*'); // true
     * Strings::is('admin', 'user_*'); // false
     * ```
     *
     * @param string $string The input string to test
     * @param string $pattern The wildcard pattern (use `*` as wildcard)
     * @return bool True when the string matches the pattern
     * @see \Phuture\Coherence\Strings::matches()
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
     * Strings::isAlpha('hello'); // true
     * Strings::isAlpha('héllo'); // true
     * Strings::isAlpha('hello1'); // false
     * Strings::isAlpha(''); // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string contains only Unicode letters
     * @see \Phuture\Coherence\Strings::isAlphanumeric()
     * @see \Phuture\Coherence\Strings::isNumeric()
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
     * Strings::isAlphanumeric('hello123'); // true
     * Strings::isAlphanumeric('hello'); // true
     * Strings::isAlphanumeric('hello!'); // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string contains only Unicode letters and numbers
     * @see \Phuture\Coherence\Strings::isAlpha()
     * @see \Phuture\Coherence\Strings::isNumeric()
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
     * Strings::isAscii('hello'); // true
     * Strings::isAscii('héllo'); // false
     * Strings::isAscii(''); // true
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string contains only ASCII characters
     * @see \Phuture\Coherence\Strings::ascii()
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
     * Strings::isBlank(''); // true
     * Strings::isBlank('   '); // true
     * Strings::isBlank("\t\n"); // true
     * Strings::isBlank('hello'); // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string is blank
     * @see \Phuture\Coherence\Strings::isEmpty()
     * @see \Phuture\Coherence\Strings::isFilled()
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
     * Strings::isEmail('user@example.com'); // true
     * Strings::isEmail('not-an-email'); // false
     * ```
     *
     * @param string $string The input string to validate
     * @return bool True when the string is a valid email address
     * @see \Phuture\Coherence\Strings::isUrl()
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
     * Strings::isEmpty(''); // true
     * Strings::isEmpty('0'); // false
     * Strings::isEmpty(' '); // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string has zero length
     * @see \Phuture\Coherence\Strings::isNotEmpty()
     * @see \Phuture\Coherence\Strings::isBlank()
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
     * Strings::isFilled('hello'); // true
     * Strings::isFilled(' hello '); // true
     * Strings::isFilled(''); // false
     * Strings::isFilled('   '); // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string contains at least one non-whitespace character
     * @see \Phuture\Coherence\Strings::isBlank()
     * @see \Phuture\Coherence\Strings::isEmpty()
     */
    public static function isFilled(string $string): bool
    {
        return trim($string) !== '';
    }

    /**
     * Determines whether a string is valid JSON.
     *
     * Returns false for empty strings and any string that is not valid JSON.
     * Uses PHP 8.3's `json_validate()` when available, falling back to
     * `json_decode()` on older versions.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isJson('{"name":"John"}'); // true
     * Strings::isJson('["a", "b"]'); // true
     * Strings::isJson('not json'); // false
     * Strings::isJson(''); // false
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

        return json_validate($string);
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
     * Strings::isLower('hello'); // true
     * Strings::isLower('Hello'); // false
     * Strings::isLower(''); // true
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string is entirely lowercase
     * @see \Phuture\Coherence\Strings::isUpper()
     * @see \Phuture\Coherence\Strings::lower()
     */
    public static function isLower(string $string): bool
    {
        return self::lower($string) === $string;
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
     * Strings::isNotEmpty('hello'); // true
     * Strings::isNotEmpty(' '); // true
     * Strings::isNotEmpty(''); // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string is not empty
     * @see \Phuture\Coherence\Strings::isEmpty()
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
     * Strings::isNumeric('123'); // true
     * Strings::isNumeric('-45.6'); // true
     * Strings::isNumeric('abc'); // false
     * Strings::isNumeric(''); // false
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string is numeric
     * @see \Phuture\Coherence\Strings::isAlpha()
     * @see \Phuture\Coherence\Strings::isAlphanumeric()
     */
    public static function isNumeric(string $string): bool
    {
        if ($string === '') {
            return false;
        }

        return preg_match('/^-?\d+(\.\d+)?$/', $string) === 1;
    }

    /**
     * Determines whether a string is a valid ULID (Universally Unique Lexicographically Sortable Identifier).
     *
     * A ULID is 26 characters long and uses Crockford's Base32 character set (0-9 and A-Z
     * excluding I, L, O, U). Matching is case-insensitive.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::isUlid('01ARZ3NDEKTSV4RRFFQ69G5FAV'); // true
     * Strings::isUlid('not-a-ulid'); // false
     * ```
     *
     * @param string $string The input string to validate
     * @return bool True when the string is a valid ULID
     * @see \Phuture\Coherence\Strings::isUuid()
     */
    public static function isUlid(string $string): bool
    {
        return (bool) preg_match('/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/i', $string);
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
     * Strings::isUpper('HELLO'); // true
     * Strings::isUpper('Hello'); // false
     * Strings::isUpper(''); // true
     * ```
     *
     * @param string $string The input string to test
     * @return bool True when the string is entirely uppercase
     * @see \Phuture\Coherence\Strings::isLower()
     * @see \Phuture\Coherence\Strings::upper()
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
     * Strings::isUrl('https://example.com'); // true
     * Strings::isUrl('not-a-url'); // false
     * ```
     *
     * @param string $string The input string to validate
     * @return bool True when the string is a valid URL
     * @see \Phuture\Coherence\Strings::isEmail()
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
     * Strings::isUuid('550e8400-e29b-41d4-a716-446655440000'); // true
     * Strings::isUuid('not-a-uuid'); // false
     * ```
     *
     * @param string $string The input string to validate
     * @return bool True when the string is a valid UUID
     * @see \Phuture\Coherence\Strings::uuid()
     */
    public static function isUuid(string $string): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $string
        ) === 1;
    }

    /**
     * Calculates the Jaro similarity between two strings.
     *
     * The Jaro similarity is a measure of how alike two strings are. It returns
     * a number between 0.0 (completely different) and 1.0 (identical). The
     * algorithm considers two characters to be a "match" when they appear within
     * a certain distance of each other in both strings.
     *
     * This method is multibyte-safe and works correctly with accented characters,
     * emoji, and other Unicode text — each Unicode character counts as one unit.
     *
     * Returns 1.0 when both strings are empty (they are identical). Returns 0.0
     * when one string is empty and the other is not.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::jaro('martha', 'marhta'); // ~0.9444
     * Strings::jaro('hello', 'hello'); // 1.0
     * Strings::jaro('foo', 'bar'); // 0.0
     * Strings::jaro('', ''); // 1.0
     * ```
     *
     * @param string $string The first string to compare
     * @param string $other The second string to compare against
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return float The Jaro similarity score from 0.0 (different) to 1.0 (identical)
     * @see \Phuture\Coherence\Strings::jaroWinkler()
     * @see \Phuture\Coherence\Strings::distance()
     * @see \Phuture\Coherence\Strings::similar()
     */
    public static function jaro(string $string, string $other, string $encoding = 'UTF-8'): float
    {
        if ($string === $other) {
            return 1.0;
        }

        if ($string === '' || $other === '') {
            return 0.0;
        }

        $firstChars = mb_str_split($string, 1, $encoding);
        $secondChars = mb_str_split($other, 1, $encoding);
        $firstLength = count($firstChars);
        $secondLength = count($secondChars);

        $matchWindow = max(0, (int) floor(max($firstLength, $secondLength) / 2) - 1);

        $firstCharsMatched = array_fill(0, $firstLength, false);
        $secondCharsMatched = array_fill(0, $secondLength, false);
        $matches = 0;

        for ($i = 0; $i < $firstLength; $i++) {
            $start = max(0, $i - $matchWindow);
            $end = min($i + $matchWindow + 1, $secondLength);

            for ($j = $start; $j < $end; $j++) {
                if ($secondCharsMatched[$j] || $firstChars[$i] !== $secondChars[$j]) {
                    continue;
                }

                $firstCharsMatched[$i] = true;
                $secondCharsMatched[$j] = true;
                $matches++;
                break;
            }
        }

        if ($matches === 0) {
            return 0.0;
        }

        $transpositions = 0;
        $secondCharIndex = 0;

        for ($i = 0; $i < $firstLength; $i++) {
            if (!$firstCharsMatched[$i]) {
                continue;
            }

            while (!$secondCharsMatched[$secondCharIndex]) {
                $secondCharIndex++;
            }

            if ($firstChars[$i] !== $secondChars[$secondCharIndex]) {
                $transpositions++;
            }

            $secondCharIndex++;
        }

        $similarity = ($matches / $firstLength)
            + ($matches / $secondLength)
            + (($matches - $transpositions / 2) / $matches);

        return $similarity / 3.0;
    }

    /**
     * Calculates the Jaro-Winkler similarity between two strings.
     *
     * Jaro-Winkler is an extension of the Jaro similarity that gives extra weight
     * to strings sharing a common prefix. A longer shared prefix results in a
     * higher similarity score. This makes it particularly useful for comparing
     * names or words where the beginning matters most.
     *
     * The prefix scale controls how much the shared prefix boosts the score. The
     * standard value is 0.1 and it must not exceed 0.25, otherwise the result
     * could fall outside the valid 0.0 to 1.0 range.
     *
     * This method is multibyte-safe and works correctly with accented characters,
     * emoji, and other Unicode text.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::jaroWinkler('martha', 'marhta'); // ~0.9611
     * Strings::jaroWinkler('hello', 'hello'); // 1.0
     * Strings::jaroWinkler('hello', 'helo', 0.0); // same as jaro()
     * ```
     *
     * @param string $string The first string to compare
     * @param string $other The second string to compare against
     * @param float $prefixScale How much weight to give the common prefix; must not exceed 0.25 (default: 0.1)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return float The Jaro-Winkler similarity score from 0.0 (different) to 1.0 (identical)
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$prefixScale` exceeds 0.25
     * @see \Phuture\Coherence\Strings::jaro()
     */
    public static function jaroWinkler(
        string $string,
        string $other,
        float $prefixScale = 0.1,
        string $encoding = 'UTF-8'
    ): float {
        if ($prefixScale > 0.25) {
            throw new InvalidArgumentException(
                'Invalid Argument: Prefix scale must not exceed 0.25 for Jaro-Winkler'
            );
        }

        $jaroScore = self::jaro($string, $other);

        if ($jaroScore === 0.0) {
            return 0.0;
        }

        $firstChars = mb_str_split($string, 1, $encoding);
        $secondChars = mb_str_split($other, 1, $encoding);
        $maxPrefix = min(4, count($firstChars), count($secondChars));
        $prefixLength = 0;

        for ($i = 0; $i < $maxPrefix; $i++) {
            if ($firstChars[$i] !== $secondChars[$i]) {
                break;
            }

            $prefixLength++;
        }

        return $jaroScore + ($prefixLength * $prefixScale * (1.0 - $jaroScore));
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
     * Strings::kebab('helloWorld'); // 'hello-world'
     * Strings::kebab('UserProfileData'); // 'user-profile-data'
     * Strings::kebab('hello world'); // 'hello-world'
     * ```
     *
     * @param string $string The input string to convert
     * @return string The kebab-case version of the string
     * @see \Phuture\Coherence\Strings::snake()
     * @see \Phuture\Coherence\Strings::camel()
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
     * Strings::last('hello', 3); // 'llo'
     * Strings::last('ñaño', 2); // 'ño'
     * Strings::last('hello'); // 'o'
     * ```
     *
     * @param string $string The input string
     * @param int $count The number of characters to return (default: 1)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The last N characters
     * @see \Phuture\Coherence\Strings::first()
     */
    public static function last(string $string, int $count = 1, string $encoding = 'UTF-8'): string
    {
        if ($count <= 0) {
            return '';
        }

        return mb_substr($string, -$count, null, $encoding);
    }

    /**
     * Returns the position of the last occurrence of a search value.
     *
     * Returns false when the search value is not found. Supports optional
     * case-insensitive matching.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::lastPosition('hello world hello', 'hello'); // 12
     * Strings::lastPosition('hello', 'xyz'); // false
     * Strings::lastPosition('Hello World', 'world', 0, false); // 6
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to search for
     * @param int $offset The offset from the start to begin searching (default: 0)
     * @param bool $caseSensitive Whether the search is case-sensitive (default: true)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return int|false The position of the last occurrence, or false if not found
     * @see \Phuture\Coherence\Strings::position()
     */
    public static function lastPosition(
        string $string,
        string $search,
        int $offset = 0,
        bool $caseSensitive = true,
        string $encoding = 'UTF-8'
    ): int|false {
        if ($caseSensitive) {
            return mb_strrpos($string, $search, $offset, $encoding);
        }

        return mb_strripos($string, $search, $offset, $encoding);
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
     * Strings::length('hello'); // 5
     * Strings::length('ñaño'); // 4
     * Strings::length('你好'); // 2
     * Strings::length(''); // 0
     * ```
     *
     * @param string $string The input string to measure
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return int The number of characters
     * @see \Phuture\Coherence\Strings::wordCount()
     */
    public static function length(string $string, string $encoding = 'UTF-8'): int
    {
        return mb_strlen($string, $encoding);
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
     * Strings::limit('Hello World', 5); // 'Hello'
     * Strings::limit('Hello World', 5, '...'); // 'Hello...'
     * Strings::limit('Hello World', 5, ' [+]'); // 'Hello [+]'
     * Strings::limit('Hi', 5); // 'Hi'
     * ```
     *
     * @param string $string The input string to limit
     * @param int $limit The maximum number of characters before truncation; must be zero or greater
     * @param string $end The string to append after truncation (default: '')
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The limited string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$limit` is negative
     * @see \Phuture\Coherence\Strings::limitWords()
     * @see \Phuture\Coherence\Strings::excerpt()
     */
    public static function limit(string $string, int $limit, string $end = '', string $encoding = 'UTF-8'): string
    {
        if ($limit < 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: Limit must be zero or greater"
            );
        }

        if (self::length($string) <= $limit) {
            return $string;
        }

        return rtrim(mb_substr($string, 0, $limit, $encoding)) . $end;
    }

    /**
     * Limits the number of words in a string, appending an end marker when truncated.
     *
     * Splits the string into words, keeps only the first `$limit` words, and joins them
     * back with spaces. Returns the string unchanged when the word count is within the limit.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::limitWords('The quick brown fox jumps', 3); // 'The quick brown'
     * Strings::limitWords('The quick brown fox jumps', 3, '...'); // 'The quick brown...'
     * Strings::limitWords('Hi there', 5); // 'Hi there' (no truncation, original string returned unchanged)
     * Strings::limitWords('Hi there', 5, '...'); // 'Hi there' (no truncation, end marker not appended)
     * ```
     *
     * @param string $string The input string to limit
     * @param int $limit The maximum number of words to keep; must be greater than zero
     * @param string $end The string to append after truncation (default: '')
     * @return string The word-limited string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$limit` is less than or equal to zero
     * @see \Phuture\Coherence\Strings::limit()
     * @see \Phuture\Coherence\Strings::words()
     */
    public static function limitWords(string $string, int $limit, string $end = ''): string
    {
        if ($limit <= 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: Word limit must be greater than zero"
            );
        }

        $words = self::words($string);

        if (count($words) <= $limit) {
            return $string;
        }

        return implode(' ', array_slice($words, 0, $limit)) . $end;
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
     * Strings::lower('HELLO'); // 'hello'
     * Strings::lower('ÑOÑO'); // 'ñoño'
     * Strings::lower('ÄÖÜ'); // 'äöü'
     * ```
     *
     * @param string $string The input string to lowercase
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The lowercased string
     * @see \Phuture\Coherence\Strings::upper()
     * @see \Phuture\Coherence\Strings::title()
     */
    public static function lower(string $string, string $encoding = 'UTF-8'): string
    {
        return mb_strtolower($string, $encoding);
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
     * Strings::lowerFirst('Hello World'); // 'hello World'
     * Strings::lowerFirst('HELLO'); // 'hELLO'
     * Strings::lowerFirst('Ñoño'); // 'ñoño'
     * ```
     *
     * @param string $string The input string
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The string with its first character lowercased
     * @see \Phuture\Coherence\Strings::lower()
     * @see \Phuture\Coherence\Strings::title()
     */
    public static function lowerFirst(string $string, string $encoding = 'UTF-8'): string
    {
        return mb_lcfirst($string, $encoding);
    }

    /**
     * Masks a portion of a string with a repeated mask character.
     *
     * Replaces characters at positions `($offset, $offset + $length)` with `$mask`.
     * Supports negative offsets to count from the end of the string. When `$length`
     * is null, all characters from `$offset` onwards are masked.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::mask('1234567890'); // '**********'
     * Strings::mask('1234567890', '*', 3); // '123*******'
     * Strings::mask('1234567890', '*', 3, 4); // '123****890'
     * Strings::mask('1234567890', '*', -4); // '123456****'
     * ```
     *
     * @param string $string The input string to mask
     * @param string $mask The mask character to use (default: '*')
     * @param int $offset The start position to begin masking (negative counts from the end)
     * @param int|null $length The number of characters to mask; must be zero or greater (null masks to the end)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The masked string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$length` is negative
     * @see \Phuture\Coherence\Strings::limit()
     */
    public static function mask(
        string $string,
        string $mask = '*',
        int $offset = 0,
        ?int $length = null,
        string $encoding = 'UTF-8'
    ): string {
        if ($length !== null && $length < 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: Length must be zero or greater"
            );
        }

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
                $masked .= mb_substr($string, $i, 1, $encoding);
            }
        }

        return $masked;
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
     * Strings::matches('user_123', '/^user_\d+$/'); // true
     * Strings::matches('user_abc', '/^user_\d+$/'); // false
     * ```
     *
     * @param string $string The input string to test
     * @param string $pattern The full regular expression pattern including delimiters
     * @return bool True when the pattern matches
     * @see \Phuture\Coherence\Strings::is()
     */
    public static function matches(string $string, string $pattern): bool
    {
        return preg_match($pattern, $string) === 1;
    }

    /**
     * Calculates the metaphone key of a string.
     *
     * Metaphone is a phonetic algorithm that encodes words based on how they
     * sound in English. Unlike soundex, metaphone produces keys of variable
     * length and is generally more accurate for English pronunciation. Two
     * words that sound the same will produce the same key.
     *
     * The string is transliterated to ASCII before processing, making this
     * method safe to use with accented or non-Latin characters — for example,
     * "héllo" is treated the same as "hello".
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::metaphone('World'); // 'WRLT'
     * Strings::metaphone('Thompson'); // '0MPSN'
     * Strings::metaphone('Smith'); // 'SM0'
     * Strings::metaphone('Smythe'); // 'SM0'
     * Strings::metaphone('héllo'); // 'HL'
     * ```
     *
     * @param string $string The input string to compute the metaphone key for
     * @param int $maxPhonemes The maximum number of phonemes to return; 0 means no limit (default: 0)
     * @return string The metaphone phonetic key
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$string` is empty
     *   or `$maxPhonemes` is negative
     * @see \Phuture\Coherence\Strings::soundex()
     * @see \Phuture\Coherence\Strings::ascii()
     */
    public static function metaphone(string $string, int $maxPhonemes = 0): string
    {
        if ($string === '') {
            throw new InvalidArgumentException(
                'Invalid Argument: String must not be empty for metaphone'
            );
        }

        if ($maxPhonemes < 0) {
            throw new InvalidArgumentException(
                'Invalid Argument: The maximum number of phonemes must not be negative for metaphone'
            );
        }

        return self::phonizeMetaphone(self::ascii($string), $maxPhonemes);
    }

    /**
     * Inserts HTML line breaks before all newlines in a string.
     *
     * Converts newline characters (`\n`) to `<br>` tags. When `$useXhtml` is true,
     * produces `<br />` instead of `<br>`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::nl2br("hello\nworld"); // 'hello<br />\nworld'
     * Strings::nl2br("hello\nworld", false); // 'hello<br>\nworld'
     * ```
     *
     * @param string $string The input string containing newlines
     * @param bool $useXhtml Whether to use XHTML-compatible `<br />` tags (default: true)
     * @return string The string with HTML line breaks inserted before newlines
     */
    public static function nl2br(string $string, bool $useXhtml = true): string
    {
        return nl2br($string, $useXhtml);
    }

    /**
     * Normalizes line endings to Unix-style `\n`.
     *
     * Converts Windows-style `\r\n` and old Mac-style `\r` to `\n`. The string is
     * returned unchanged when it contains no line endings.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::normalizeNewLines("line1\r\nline2\rline3"); // "line1\nline2\nline3"
     * ```
     *
     * @param string $string The input string whose line endings are to be normalized
     * @return string The string with all line endings replaced by `\n`
     * @see \Phuture\Coherence\Strings::strip()
     */
    public static function normalizeNewLines(string $string): string
    {
        return str_replace(["\r\n", "\r"], "\n", $string);
    }

    /**
     * Formats a number with grouped thousands and configurable separators.
     *
     * Rounds the number to `$decimals` decimal places and inserts `$thousandsSeparator`
     * between every group of three digits.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::numberFormat(1234.5678, 2); // '1,234.57'
     * Strings::numberFormat(1234.5678, 2, ',', '.'); // '1.234,57'
     * Strings::numberFormat(1000000); // '1,000,000'
     * ```
     *
     * @param float|int $number The number to format
     * @param int $decimals The number of decimal places (default: 0)
     * @param string $decimalSeparator The character for the decimal point (default: '.')
     * @param string $thousandsSeparator The character for thousands grouping (default: ',')
     * @return string The formatted number string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$decimals` is negative
     *   or greater than MAX_DECIMALS
     */
    public static function numberFormat(
        float|int $number,
        int $decimals = 0,
        string $decimalSeparator = '.',
        string $thousandsSeparator = ','
    ): string {
        if ($decimals < 0 || $decimals > self::MAX_DECIMALS) {
            throw new InvalidArgumentException(
                'Invalid Argument: The number of decimals must be between 0 and ' . self::MAX_DECIMALS
            );
        }

        return number_format($number, $decimals, $decimalSeparator, $thousandsSeparator);
    }

    /**
     * Creates a fluent wrapper around the given string for method chaining.
     *
     * Returns a `Type\Strings` instance that wraps the provided string value and
     * exposes every string-returning method as a chainable call.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * $result = Strings::of('  hello world  ')
     *     ->trim()
     *     ->upper()
     *     ->get();
     * // 'HELLO WORLD'
     * ```
     *
     * @param string $string The string to wrap for fluent operations
     * @return Type\Strings A fluent wrapper instance that enables method chaining
     * @see \Phuture\Coherence\Type\Strings For the fluent wrapper implementation
     */
    public static function of(string $string): Type\Strings
    {
        return new Type\Strings($string);
    }

    /**
     * Pads a string to a given length using a pad string.
     *
     * Extends the string with the `$padString` on the side specified by `$direction`
     * until the total character count reaches `$length`. When the string is already
     * at or beyond `$length`, or when `$padString` is empty, it is returned unchanged.
     * For `PadDirection::Both`, padding is split evenly; when the total padding is odd
     * the extra character goes to the right side.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     * use Phuture\Coherence\Enum\PadDirection;
     *
     * Strings::pad('hello', 10); // 'hello     '
     * Strings::pad('hello', 10, '-'); // 'hello-----'
     * Strings::pad('hello', 10, ' ', PadDirection::Left); // '     hello'
     * Strings::pad('5', 5, '0', PadDirection::Left); // '00005'
     * Strings::pad('hello', 11, '-', PadDirection::Both); // '---hello---'
     * Strings::pad('hello', 10, '-', PadDirection::Both); // '--hello---'
     * ```
     *
     * @param string $string The input string to pad
     * @param int $length The target total length in characters
     * @param string $padString The string to pad with (default: space)
     * @param \Phuture\Coherence\Enum\PadDirection $direction Which side to pad — Right, Left, or Both
     *  (default: PadDirection::Right)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The padded string
     * @see \Phuture\Coherence\Enum\PadDirection
     */
    public static function pad(
        string $string,
        int $length,
        string $padString = ' ',
        PadDirection $direction = PadDirection::Right,
        string $encoding = 'UTF-8'
    ): string {
        $padType = match ($direction) {
            PadDirection::Left => STR_PAD_LEFT,
            PadDirection::Both => STR_PAD_BOTH,
            PadDirection::Right => STR_PAD_RIGHT,
        };

        return mb_str_pad($string, $length, $padString, $padType, $encoding);
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
     * Strings::pascal('hello world'); // 'HelloWorld'
     * Strings::pascal('hello_world'); // 'HelloWorld'
     * Strings::pascal('hello-world'); // 'HelloWorld'
     * Strings::pascal('helloWorld'); // 'HelloWorld'
     * ```
     *
     * @param string $string The input string to convert
     * @return string The PascalCase version of the string
     * @see \Phuture\Coherence\Strings::camel()
     * @see \Phuture\Coherence\Strings::snake()
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
     * Generates a cryptographically secure password with configurable character requirements.
     *
     * Produces a random password that is guaranteed to contain at least one character from
     * each enabled character pool. The character pools are: uppercase letters, lowercase
     * letters, digits, and special characters. Throws when `$length` is too short to
     * satisfy all enabled requirements.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * $pw = Strings::password(16);
     * $pw = Strings::password(20, includeSpecialCharacters: '!@#$%^&*');
     * ```
     *
     * @param int $length The total length of the password; must be greater than zero (default: 16)
     * @param bool $includeUppercase Whether at least one uppercase letter is included (default: true)
     * @param bool $includeLowercase Whether at least one lowercase letter is included (default: true)
     * @param bool $includeDigits Whether at least one digit is included (default: true)
     * @param string $includeSpecialCharacters The set of special characters to include
     *  (default: '!@#$%^&*()-_=+[]{}|;:,.<>?')
     * @return string The generated password
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$length` is too short for
     *  the enabled requirements
     * @throws \Random\RandomException If the system entropy source is unavailable
     * @see \Phuture\Coherence\Strings::random()
     */
    public static function password(
        int $length = 16,
        bool $includeUppercase = true,
        bool $includeLowercase = true,
        bool $includeDigits = true,
        string $includeSpecialCharacters = '!@#$%^&*()-_=+[]{}|;:,.<>?'
    ): string {
        $requiredPools = [];
        $allCharacters = '';

        if ($includeLowercase) {
            $requiredPools[] = 'abcdefghijklmnopqrstuvwxyz';
            $allCharacters .= 'abcdefghijklmnopqrstuvwxyz';
        }

        if ($includeUppercase) {
            $requiredPools[] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $allCharacters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if ($includeDigits) {
            $requiredPools[] = '0123456789';
            $allCharacters .= '0123456789';
        }

        if ($includeSpecialCharacters !== '') {
            $requiredPools[] = $includeSpecialCharacters;
            $allCharacters .= $includeSpecialCharacters;
        }

        $requiredCount = count($requiredPools);

        if ($length < $requiredCount) {
            throw new InvalidArgumentException(
                "Invalid Argument: Password length must be at least {$requiredCount}"
                . " to satisfy all enabled requirements"
            );
        }

        $password = '';

        foreach ($requiredPools as $pool) {
            $password .= $pool[random_int(0, strlen($pool) - 1)];
        }

        $allLength = strlen($allCharacters);

        for ($i = $requiredCount; $i < $length; $i++) {
            $password .= $allCharacters[random_int(0, $allLength - 1)];
        }

        return str_shuffle($password);
    }

    /**
     * Returns the position of the first occurrence of a search value.
     *
     * Returns false when the search value is not found. Supports optional
     * case-insensitive matching.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::position('hello world', 'world'); // 6
     * Strings::position('hello world', 'xyz'); // false
     * Strings::position('hello hello', 'hello', 3); // 6
     * Strings::position('Hello World', 'world', 0, false); // 6
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to search for
     * @param int $offset The offset from the start to begin searching (default: 0)
     * @param bool $caseSensitive Whether the search is case-sensitive (default: true)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return int|false The position of the first occurrence, or false if not found
     * @see \Phuture\Coherence\Strings::lastPosition()
     */
    public static function position(
        string $string,
        string $search,
        int $offset = 0,
        bool $caseSensitive = true,
        string $encoding = 'UTF-8'
    ): int|false {
        if ($caseSensitive) {
            return mb_strpos($string, $search, $offset, $encoding);
        }

        return mb_stripos($string, $search, $offset, $encoding);
    }

    /**
     * Escapes regular expression meta-characters in a string.
     *
     * Adds a backslash before each of the characters: `. \ + * ? [ ^ ] ( $ )`.
     * Useful for preparing a literal string for use in a regular expression pattern.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::quoteMeta('hello (world)'); // 'hello \(world\)'
     * Strings::quoteMeta('price: $10.00'); // 'price: \$10\.00'
     * ```
     *
     * @param string $string The input string to escape
     * @return string The string with meta-characters escaped
     */
    public static function quoteMeta(string $string): string
    {
        return quotemeta($string);
    }

    /**
     * Generates a cryptographically random alphanumeric string.
     *
     * Uses `random_int()` for all character selection. Throws `RandomException`
     * if the system entropy source fails. The character pool is `[0-9a-zA-Z]` (62 characters).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::random(16); // e.g. 'aB3xK9mNpQ2rZ5wY'
     * Strings::random(8); // e.g. 'a1B2c3D4'
     * ```
     *
     * @param int $length The length of the random string to generate; must be greater than zero (default: 16)
     * @return string The random alphanumeric string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$length` is less than or equal to zero
     * @throws \Random\RandomException If the system entropy source is unavailable
     * @see \Phuture\Coherence\Strings::uuid()
     */
    public static function random(int $length = 16): string
    {
        if ($length <= 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: Length must be greater than zero"
            );
        }

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characterLength = strlen($characters);
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, $characterLength - 1)];
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
     * Strings::remove('hello world', 'o'); // 'hell wrld'
     * Strings::remove('Hello World', 'world', false); // 'Hello '
     * ```
     *
     * @param string $string The input string
     * @param string $search The value to remove
     * @param bool $caseSensitive Whether the removal is case-sensitive (default: true)
     * @return string The string with all occurrences removed
     * @see \Phuture\Coherence\Strings::replace()
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
     * Strings::repeat('ab', 3); // 'ababab'
     * Strings::repeat('ha', 0); // ''
     * ```
     *
     * @param string $string The input string to repeat
     * @param int $times The number of repetitions (must be zero or greater)
     * @return string The repeated string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$times` is negative
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
     * Strings::replace('hello world', 'world', 'PHP'); // 'hello PHP'
     * Strings::replace('Hello World', 'world', 'PHP', false); // 'Hello PHP'
     * ```
     *
     * @param string $string The input string
     * @param string $search The value to search for
     * @param string $replace The replacement value
     * @param bool $caseSensitive Whether the replacement is case-sensitive (default: true)
     * @return string The string with all occurrences replaced
     * @see \Phuture\Coherence\Strings::replaceFirst()
     * @see \Phuture\Coherence\Strings::replaceLast()
     * @see \Phuture\Coherence\Strings::remove()
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
            static fn () => $replace,
            $string
        ) ?? '';
    }

    /**
     * Replaces successive occurrences of a search value using values from an array.
     *
     * Each time `$search` is found, it is replaced with the next value from `$replacements`.
     * When the replacements array is exhausted, remaining occurrences are replaced with an
     * empty string. Returns the string unchanged when `$search` is empty or `$replacements`
     * is empty.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::replaceArray('Year: ?, Month: ?', '?', ['2026', 'April']); // 'Year: 2026, Month: April'
     * ```
     *
     * @param string $string The input string to perform replacements on
     * @param string $search The value to search for
     * @param array $replacements Ordered list of string replacement values
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The string with successive occurrences replaced
     * @see \Phuture\Coherence\Strings::replace()
     */
    public static function replaceArray(
        string $string,
        string $search,
        array $replacements,
        string $encoding = 'UTF-8'
    ): string {
        if ($search === '' || empty($replacements)) {
            return $string;
        }

        foreach ($replacements as $replacement) {
            $position = mb_strpos($string, $search, 0, $encoding);

            if ($position === false) {
                break;
            }

            $string = mb_substr($string, 0, $position, $encoding)
                . (string) $replacement
                . mb_substr($string, $position + mb_strlen($search, $encoding), null, $encoding);
        }

        return $string;
    }

    /**
     * Replaces a portion of a string starting at a given character position.
     *
     * When `$length` is null, replaces from `$position` to the end of the string.
     * A negative `$position` counts from the end of the string. A negative `$length`
     * stops that many characters before the end of the string.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::replaceAt('hello world', 'PHP', 6); // 'hello PHP'
     * Strings::replaceAt('hello world', 'PHP', 6, 5); // 'hello PHP'
     * Strings::replaceAt('hello world', '', 5, 6); // 'hello'
     * ```
     *
     * @param string $string The input string to modify
     * @param string $replacement The text to insert at the given position
     * @param int $position The character index at which to begin replacement (negative counts from end)
     * @param int|null $length The number of characters to replace (null replaces to end of string)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The modified string
     * @see \Phuture\Coherence\Strings::insert()
     * @see \Phuture\Coherence\Strings::slice()
     */
    public static function replaceAt(
        string $string,
        string $replacement,
        int $position,
        ?int $length = null,
        string $encoding = 'UTF-8'
    ): string {
        $stringLength = mb_strlen($string, $encoding);
        $actualStart = $position < 0 ? max(0, $stringLength + $position) : min($position, $stringLength);

        if ($length === null) {
            return mb_substr($string, 0, $actualStart, $encoding) . $replacement;
        }

        $actualEnd = $length < 0
            ? max($actualStart, $stringLength + $length)
            : $actualStart + $length;

        return mb_substr($string, 0, $actualStart, $encoding)
            . $replacement
            . mb_substr($string, $actualEnd, null, $encoding);
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
     * Strings::replaceFirst('hello hello', 'hello', 'world'); // 'world hello'
     * Strings::replaceFirst('hello', 'xyz', 'world'); // 'hello'
     * ```
     *
     * @param string $string The input string
     * @param string $search The value to search for
     * @param string $replace The replacement value
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The string with the first occurrence replaced
     * @see \Phuture\Coherence\Strings::replaceLast()
     * @see \Phuture\Coherence\Strings::replace()
     */
    public static function replaceFirst(
        string $string,
        string $search,
        string $replace,
        string $encoding = 'UTF-8'
    ): string {
        if ($search === '') {
            return $string;
        }

        $position = mb_strpos($string, $search, 0, $encoding);

        if ($position === false) {
            return $string;
        }

        return mb_substr($string, 0, $position, $encoding)
            . $replace
            . mb_substr($string, $position + mb_strlen($search, $encoding), null, $encoding);
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
     * Strings::replaceLast('hello hello', 'hello', 'world'); // 'hello world'
     * Strings::replaceLast('hello', 'xyz', 'world'); // 'hello'
     * ```
     *
     * @param string $string The input string
     * @param string $search The value to search for
     * @param string $replace The replacement value
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The string with the last occurrence replaced
     * @see \Phuture\Coherence\Strings::replaceFirst()
     * @see \Phuture\Coherence\Strings::replace()
     */
    public static function replaceLast(
        string $string,
        string $search,
        string $replace,
        string $encoding = 'UTF-8'
    ): string {
        if ($search === '') {
            return $string;
        }

        $position = mb_strrpos($string, $search, 0, $encoding);

        if ($position === false) {
            return $string;
        }

        return mb_substr($string, 0, $position, $encoding)
            . $replace
            . mb_substr($string, $position + mb_strlen($search, $encoding), null, $encoding);
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
     * Strings::reverse('hello'); // 'olleh'
     * Strings::reverse('ñaño'); // 'oñañ'
     * ```
     *
     * @param string $string The input string to reverse
     * @return string The reversed string
     * @see \Phuture\Coherence\Strings::swap()
     */
    public static function reverse(string $string): string
    {
        $characters = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);

        return implode('', array_reverse($characters));
    }

    /**
     * Applies the ROT13 encoding to a string.
     *
     * ROT13 shifts every ASCII letter by 13 positions, wrapping around the alphabet.
     * Applying it twice returns the original string. Non-alphabetic characters are
     * left unchanged.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::rot13('hello'); // 'uryyb'
     * Strings::rot13('uryyb'); // 'hello'
     * Strings::rot13('Hello World!'); // 'Uryyb Jbeyq!'
     * ```
     *
     * @param string $string The input string to encode
     * @return string The ROT13-encoded string
     */
    public static function rot13(string $string): string
    {
        return str_rot13($string);
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
     * Strings::scrub("hello\x00world"); // 'helloworld'
     * Strings::scrub("clean text"); // 'clean text'
     * ```
     *
     * @param string $string The input string to clean
     * @return string The string with control characters removed
     * @see \Phuture\Coherence\Strings::fixEncoding()
     */
    public static function scrub(string $string): string
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string) ?? '';
    }

    /**
     * Returns the portion of the string from the first occurrence of a search value.
     *
     * Searches for the first occurrence of `$search` in `$string` and returns the
     * portion from that position to the end (or everything before it if `$beforeNeedle`
     * is true). Returns `false` when `$search` is not found.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::search('user@example.com', '@'); // '@example.com'
     * Strings::search('user@example.com', '@', true); // 'user'
     * Strings::search('Hello World', 'world', false, false); // 'World'
     * Strings::search('hello', 'xyz'); // false
     * ```
     *
     * @param string $string The input string to search within
     * @param string $search The value to search for
     * @param bool $beforeNeedle Return the part before the search value instead of after (default: false)
     * @param bool $caseSensitive Whether the search is case-sensitive (default: true)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string|false The portion from the first occurrence, or false if not found
     * @see \Phuture\Coherence\Strings::position()
     */
    public static function search(
        string $string,
        string $search,
        bool $beforeNeedle = false,
        bool $caseSensitive = true,
        string $encoding = 'UTF-8'
    ): string|false {
        if ($search === '') {
            return $string;
        }

        if ($caseSensitive) {
            return mb_strstr($string, $search, $beforeNeedle, $encoding);
        }

        return mb_stristr($string, $search, $beforeNeedle, $encoding);
    }

    /**
     * Randomly shuffles the characters in a string.
     *
     * Multibyte-safe: splits on Unicode code points before shuffling.
     * Returns an empty string when the input is empty.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * $shuffled = Strings::shuffle('hello'); // e.g. 'lleoh'
     * $shuffled = Strings::shuffle('ñaño'); // multibyte-safe shuffle
     * ```
     *
     * @param string $string The input string to shuffle
     * @return string The shuffled string
     */
    public static function shuffle(string $string): string
    {
        if ($string === '') {
            return '';
        }

        $characters = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);

        if ($characters === false) {
            return '';
        }

        shuffle($characters);

        return implode('', $characters);
    }

    /**
     * Calculates the similarity between two strings as a percentage.
     *
     * Returns a value between 0.0 and 100.0 indicating how similar the two strings are.
     * A value of 100.0 means the strings are identical. Returns 0.0 when either string
     * is empty.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::similar('hello', 'hello'); // 100.0
     * Strings::similar('hello', 'hallo'); // ~80.0
     * Strings::similar('hello', ''); // 0.0
     * ```
     *
     * @param string $string The first string to compare
     * @param string $other The second string to compare against
     * @return float The similarity percentage from 0.0 to 100.0
     * @see \Phuture\Coherence\Strings::distance()
     */
    public static function similar(string $string, string $other): float
    {
        if ($string === '' || $other === '') {
            return 0.0;
        }

        similar_text($string, $other, $percent);

        return $percent;
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
     * Strings::slice('hello world', 0, 5); // 'hello'
     * Strings::slice('hello world', 6); // 'world'
     * Strings::slice('hello world', -5); // 'world'
     * ```
     *
     * @param string $string The input string to slice
     * @param int $start The starting position (negative counts from the end)
     * @param int|null $length The number of characters to return (null returns to the end)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The extracted substring
     * @see \Phuture\Coherence\Strings::first()
     * @see \Phuture\Coherence\Strings::last()
     * @see \Phuture\Coherence\Strings::charAt()
     */
    public static function slice(string $string, int $start, ?int $length = null, string $encoding = 'UTF-8'): string
    {
        return mb_substr($string, $start, $length, $encoding);
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
     * Strings::slug('Hello World'); // 'hello-world'
     * Strings::slug('Hello World', '_'); // 'hello_world'
     * Strings::slug('héllo wörld'); // 'hello-world'
     * ```
     *
     * @param string $string The input string to slugify
     * @param string $separator The separator character between words (default: '-')
     * @param string $language The language code for transliteration (default: 'en')
     * @return string The URL-friendly slug
     * @see \Phuture\Coherence\Strings::ascii()
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
     * Strings::snake('helloWorld'); // 'hello_world'
     * Strings::snake('HelloWorld', '-'); // 'hello-world'
     * Strings::snake('hello world'); // 'hello_world'
     * Strings::snake('XMLParser'); // 'xml_parser'
     * ```
     *
     * @param string $string The input string to convert
     * @param string $delimiter The word separator character (default: '_')
     * @return string The snake_case version of the string
     * @see \Phuture\Coherence\Strings::kebab()
     * @see \Phuture\Coherence\Strings::camel()
     */
    public static function snake(string $string, string $delimiter = '_'): string
    {
        $string = preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', ' ', $string);
        $string = preg_replace('/(?<=[A-Z])(?=[A-Z][a-z])/', ' ', $string);
        $string = preg_replace('/[^a-zA-Z0-9]+/', ' ', $string);
        $string = self::lower(trim($string));

        return preg_replace('/\s+/', $delimiter, $string) ?? '';
    }

    /**
     * Calculates the soundex key of a string.
     *
     * Soundex is a phonetic algorithm that indexes names by their English pronunciation.
     * The result is a 4-character string starting with a letter followed by three digits.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::soundex('Euler'); // 'E460'
     * Strings::soundex('Ellery'); // 'E460'
     * Strings::soundex('Knuth'); // 'K530'
     * ```
     *
     * @param string $string The input string to compute the soundex key for
     * @return string The 4-character soundex key
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$string` is empty
     */
    public static function soundex(string $string): string
    {
        if ($string === '') {
            throw new InvalidArgumentException(
                "Invalid Argument: String must not be empty for soundex"
            );
        }

        return \soundex($string);
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
     * Strings::split('a.b.c', '.'); // ['a', 'b', 'c']
     * Strings::split('a.b.c', '.', 2); // ['a', 'b.c']
     * ```
     *
     * @param string $string The input string to split
     * @param string $pattern The literal separator to split on
     * @param int $limit Maximum number of elements to return (default: -1 = no limit)
     * @return array Array of substrings, indexed sequentially from zero
     * @see \Phuture\Coherence\Strings::explode()
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
     * Strings::squish('hello    world'); // 'hello world'
     * Strings::squish("  hello   \n   world  "); // 'hello world'
     * Strings::squish("a\t\tb\n\nc"); // 'a b c'
     * ```
     *
     * @param string $string The input string to squish
     * @return string The string with collapsed whitespace
     * @see \Phuture\Coherence\Strings::trim()
     * @see \Phuture\Coherence\Strings::dedupe()
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
     * Strings::start('/path/to', '/'); // '/path/to'
     * Strings::start('path/to', '/'); // '/path/to'
     * Strings::start('///path/to', '/'); // '/path/to'
     * ```
     *
     * @param string $string The input string
     * @param string $prefix The prefix to ensure is present exactly once
     * @return string The string guaranteed to begin with the prefix
     * @see \Phuture\Coherence\Strings::finish()
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
     * Strings::startsWith('hello world', 'hello'); // true
     * Strings::startsWith('https://example.com', 'https://'); // true
     * Strings::startsWith('hello', 'Hello'); // false
     * ```
     *
     * @param string $string The input string to check
     * @param string $search The expected prefix
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return bool True when the string begins with the search value
     * @see \Phuture\Coherence\Strings::endsWith()
     */
    public static function startsWith(string $string, string $search, string $encoding = 'UTF-8'): bool
    {
        if ($search === '') {
            return true;
        }

        return mb_strpos($string, $search, 0, $encoding) === 0;
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
     * Strings::strip('<p>Hello <b>world</b></p>'); // 'Hello world'
     * Strings::strip('<p>Hello</p>', '<p>'); // '<p>Hello</p>'
     * ```
     *
     * @param string $string The input string to strip
     * @param string $allowedTags HTML tags to preserve (default: '' = strip all)
     * @return string The string with HTML/PHP tags removed
     * @see \Phuture\Coherence\Strings::normalizeNewLines()
     * @see \Phuture\Coherence\Strings::trim()
     */
    public static function strip(string $string, string $allowedTags = ''): string
    {
        return strip_tags($string, $allowedTags);
    }

    /**
     * Removes C-style backslash escapes from a string.
     *
     * Reverses the escaping performed by `addCSlashes()`, recognizing C-style
     * escape sequences like `\n`, `\r`, `\t`, `\0`, and octal/hex notations.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::stripCSlashes('h\\ell\\o'); // 'hello'
     * ```
     *
     * @param string $string The C-style escaped string to unescape
     * @return string The unescaped string
     * @see \Phuture\Coherence\Strings::addCSlashes()
     */
    public static function stripCSlashes(string $string): string
    {
        return stripcslashes($string);
    }

    /**
     * Removes backslash escapes added by `addSlashes()`.
     *
     * Reverses the escaping performed by `addSlashes()`, removing backslashes
     * before single quotes, double quotes, backslashes, and NUL bytes.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::stripSlashes("hello \\'world\\'"); // "hello 'world'"
     * ```
     *
     * @param string $string The escaped string to unescape
     * @return string The unescaped string
     * @see \Phuture\Coherence\Strings::addSlashes()
     */
    public static function stripSlashes(string $string): string
    {
        return stripslashes($string);
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
     * @param array $replacements A map of string search keys to string replacement values
     * @return string The string with all swaps applied
     * @see \Phuture\Coherence\Strings::replace()
     */
    public static function swap(string $string, array $replacements): string
    {
        if ($replacements === []) {
            return $string;
        }

        return strtr($string, $replacements);
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
     * Strings::take('hello world', 5); // 'hello'
     * Strings::take('hello world', -5); // 'world'
     * Strings::take('hello world', 0); // ''
     * ```
     *
     * @param string $string The input string
     * @param int $count The number of characters (negative returns from the end)
     * @return string The extracted characters
     * @see \Phuture\Coherence\Strings::first()
     * @see \Phuture\Coherence\Strings::last()
     */
    public static function take(string $string, int $count): string
    {
        if ($count < 0) {
            return self::last($string, abs($count));
        }

        return self::first($string, $count);
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
     * Strings::title('hello world'); // 'Hello World'
     * Strings::title('HELLO WORLD'); // 'Hello World'
     * ```
     *
     * @param string $string The input string to convert
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The title-cased string
     * @see \Phuture\Coherence\Strings::title()
     * @see \Phuture\Coherence\Strings::upper()
     */
    public static function title(string $string, string $encoding = 'UTF-8'): string
    {
        return mb_convert_case($string, MB_CASE_TITLE, $encoding);
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
     * Strings::toArray('hello'); // ['h', 'e', 'l', 'l', 'o']
     * Strings::toArray('ñaño'); // ['ñ', 'a', 'ñ', 'o']
     * Strings::toArray(''); // []
     * ```
     *
     * @param string $string The input string to convert
     * @return array Array of individual Unicode characters, indexed sequentially from zero
     * @see \Phuture\Coherence\Strings::split()
     * @see \Phuture\Coherence\Strings::chunk()
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
     * Encodes a string to its Base64 representation.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::toBase64('hello'); // 'aGVsbG8='
     * ```
     *
     * @param string $string The input string to encode
     * @return string The Base64-encoded string
     * @see \Phuture\Coherence\Strings::fromBase64()
     */
    public static function toBase64(string $string): string
    {
        return base64_encode($string);
    }

    /**
     * Converts binary data into its hexadecimal representation.
     *
     * Generates a hex string (lowercase) where each byte of the input is represented
     * by two hexadecimal digits.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::toHex('hello'); // '68656c6c6f'
     * Strings::toHex("\x00\xFF"); // '00ff'
     * ```
     *
     * @param string $string The input string to convert
     * @return string The hexadecimal representation
     * @see \Phuture\Coherence\Strings::fromHex()
     */
    public static function toHex(string $string): string
    {
        return bin2hex($string);
    }

    /**
     * Strips whitespace (or given characters) from the beginning and end of a string.
     *
     * Delegates to PHP's native `mb_trim()`. The `$characters` parameter specifies the
     * characters to strip (default: standard whitespace characters).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::trim('  hello  '); // 'hello'
     * Strings::trim('***hello***', '*'); // 'hello'
     * ```
     *
     * @param string $string The input string to trim
     * @param string|null $characters The characters to strip (default: whitespace)
     * @return string The trimmed string
     * @see \Phuture\Coherence\Strings::trimLeft()
     * @see \Phuture\Coherence\Strings::trimRight()
     * @see \Phuture\Coherence\Strings::squish()
     */
    public static function trim(string $string, ?string $characters = null): string
    {
        return mb_trim($string, $characters);
    }

    /**
     * Strips whitespace (or given characters) from the beginning of a string.
     *
     * Delegates to PHP's native `mb_ltrim()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::trimLeft('  hello  '); // 'hello  '
     * Strings::trimLeft('***hello***', '*'); // 'hello***'
     * ```
     *
     * @param string $string The input string to trim
     * @param string|null $characters The characters to strip (default: whitespace)
     * @return string The left-trimmed string
     * @see \Phuture\Coherence\Strings::trimRight()
     * @see \Phuture\Coherence\Strings::trim()
     */
    public static function trimLeft(string $string, ?string $characters = null): string
    {
        return mb_ltrim($string, $characters);
    }

    /**
     * Strips whitespace (or given characters) from the end of a string.
     *
     * Delegates to PHP's native `mb_rtrim()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::trimRight('  hello  '); // '  hello'
     * Strings::trimRight('***hello***', '*'); // '***hello'
     * ```
     *
     * @param string $string The input string to trim
     * @param string|null $characters The characters to strip (default: whitespace)
     * @return string The right-trimmed string
     * @see \Phuture\Coherence\Strings::trimLeft()
     * @see \Phuture\Coherence\Strings::trim()
     */
    public static function trimRight(string $string, ?string $characters = null): string
    {
        return mb_rtrim($string, $characters);
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
     * Strings::unwrap('"hello"', '"'); // 'hello'
     * Strings::unwrap('[hello]', '['); // '[hello]' (no matching end)
     * Strings::unwrap('hello', '"'); // 'hello'
     * ```
     *
     * @param string $string The input string to unwrap
     * @param string $wrapper The wrapper string to remove from both ends
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The unwrapped string
     * @see \Phuture\Coherence\Strings::wrap()
     */
    public static function unwrap(string $string, string $wrapper, string $encoding = 'UTF-8'): string
    {
        if ($wrapper === '') {
            return $string;
        }

        $wrapperLength = mb_strlen($wrapper, $encoding);
        $stringLength = self::length($string);

        if ($stringLength < $wrapperLength * 2) {
            return $string;
        }

        if (self::startsWith($string, $wrapper) && self::endsWith($string, $wrapper)) {
            return mb_substr($string, $wrapperLength, $stringLength - ($wrapperLength * 2), $encoding);
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
     * Strings::upper('hello'); // 'HELLO'
     * Strings::upper('ñoño'); // 'ÑOÑO'
     * Strings::upper('äöü'); // 'ÄÖÜ'
     * ```
     *
     * @param string $string The input string to uppercase
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The uppercased string
     * @see \Phuture\Coherence\Strings::lower()
     * @see \Phuture\Coherence\Strings::title()
     */
    public static function upper(string $string, string $encoding = 'UTF-8'): string
    {
        return mb_strtoupper($string, $encoding);
    }

    /**
     * Converts only the first character of a string to uppercase.
     *
     * The remainder of the string is left unchanged. Multibyte-safe.
     * This is the counterpart to `lowerFirst()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::upperFirst('hello World'); // 'Hello World'
     * Strings::upperFirst('HELLO'); // 'HELLO'
     * Strings::upperFirst('ñoño'); // 'Ñoño'
     * ```
     *
     * @param string $string The input string
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The string with its first character uppercased
     * @see \Phuture\Coherence\Strings::lowerFirst()
     * @see \Phuture\Coherence\Strings::upper()
     */
    public static function upperFirst(string $string, string $encoding = 'UTF-8'): string
    {
        if ($string === '') {
            return '';
        }

        return mb_ucfirst($string, $encoding);
    }

    /**
     * Generates a UUID (Universally Unique Identifier).
     *
     * Generates a UUID using cryptographically secure random data. Supports version 4
     * (fully random, default) and version 7 (time-ordered). The version and variant
     * bits are set according to RFC 4122.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     * use Phuture\Coherence\Enum\UuidVersion;
     *
     * Strings::uuid(); // e.g. 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d'
     * Strings::uuid(UuidVersion::V4); // e.g. 'f47ac10b-58cc-4372-a567-0e02b2c3d479'
     * Strings::uuid(UuidVersion::V7); // e.g. '019f3e7a-9b2c-7d4e-a5f6-7890123456ab'
     * ```
     *
     * @param \Phuture\Coherence\Enum\UuidVersion $version The UUID version to generate (default: V4)
     * @return string The generated UUID string
     * @throws \Random\RandomException If the system entropy source is unavailable
     * @see \Phuture\Coherence\Strings::random()
     * @see \Phuture\Coherence\Strings::isUuid()
     * @see \Phuture\Coherence\Enum\UuidVersion
     */
    public static function uuid(UuidVersion $version = UuidVersion::V4): string
    {
        if ($version === UuidVersion::V7) {
            $timestampMs = (int) (hrtime(true) / 1_000_000);
            $timestampHex = str_pad(dechex($timestampMs), 12, '0', STR_PAD_LEFT);

            $data = hex2bin($timestampHex) . random_bytes(10);

            $data[6] = chr(ord($data[6]) & 0x0f | 0x70);
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

        $data = random_bytes(16);

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
     * Strings::wordCount('hello world'); // 2
     * Strings::wordCount('  '); // 0
     * ```
     *
     * @param string $string The input string to count words in
     * @return int The number of words
     * @see \Phuture\Coherence\Strings::words()
     */
    public static function wordCount(string $string): int
    {
        if (self::isBlank($string)) {
            return 0;
        }

        return count(self::words($string));
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
     * Strings::words("hello world"); // ['hello', 'world']
     * Strings::words("it's a test", 2, '…'); // ['it\'s', 'a', '…']
     * ```
     *
     * @param string $string The input string to extract words from
     * @param int $limit Maximum number of words to return, -1 = no limit (default: -1)
     * @param string $end String appended after the word list when limited (default: '')
     * @return array Array of word strings, indexed sequentially from zero
     * @see \Phuture\Coherence\Strings::wordCount()
     * @see \Phuture\Coherence\Strings::split()
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
     * @param int $width The number of characters at which to wrap; must be greater than zero (default: 75)
     * @param string $break The line break string to insert (default: "\n")
     * @param bool $cutLongWords Whether to cut words longer than `$width` (default: false)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The word-wrapped string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When `$width` is less than or equal to zero
     * @see \Phuture\Coherence\Strings::limit()
     * @see \Phuture\Coherence\Strings::limitWords()
     */
    public static function wordWrap(
        string $string,
        int $width = 75,
        string $break = "\n",
        bool $cutLongWords = false,
        string $encoding = 'UTF-8'
    ): string {
        if ($width <= 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: Width must be greater than zero"
            );
        }

        if ($string === '') {
            return $string;
        }

        $lines = [];
        $currentLine = '';
        $currentLength = 0;

        $words = preg_split('/\s+/u', $string, -1, PREG_SPLIT_NO_EMPTY);
        if ($words !== false) {
            foreach ($words as $word) {
                $wordLength = mb_strlen($word, $encoding);

                if ($cutLongWords && $wordLength > $width) {
                    if ($currentLength > 0) {
                        $lines[] = $currentLine;
                        $currentLine = '';
                        $currentLength = 0;
                    }

                    while (mb_strlen($word, $encoding) > 0) {
                        $available = $width - $currentLength;
                        $chunk = mb_substr($word, 0, $available, $encoding);
                        $currentLine .= $chunk;
                        $currentLength += mb_strlen($chunk, $encoding);
                        $word = mb_substr($word, $available, null, $encoding);

                        if (mb_strlen($word, $encoding) > 0) {
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
        }

        $lines[] = $currentLine;

        return implode($break, $lines);
    }

    /**
     * Wraps a string with a given wrapper string on both sides.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Strings;
     *
     * Strings::wrap('hello', '"'); // '"hello"'
     * Strings::wrap('hello', '[]'); // '[]hello[]'
     * ```
     *
     * @param string $string The input string to wrap
     * @param string $wrapper The string to prepend and append
     * @return string The wrapped string
     * @see \Phuture\Coherence\Strings::unwrap()
     */
    public static function wrap(string $string, string $wrapper): string
    {
        return $wrapper . $string . $wrapper;
    }

    /**
     * Returns the character map for the given ASCII art font.
     *
     * Each character is represented as an array of exactly 5 strings of equal width,
     * forming a 5-row block glyph rendered using `#` and space characters.
     *
     * @param string $font The font name (currently only 'block' is supported)
     * @return array Map of single-character string keys to arrays of exactly five 6-column strings
     */
    private static function getAsciiFontMap(string $font): array
    {
        // 5-row block font using '#' and spaces; each glyph is 6 columns wide with no surrounding padding
        return [
            ' ' => ['      ', '      ', '      ', '      ', '      '],
            'A' => ['######', '#    #', '######', '#    #', '#    #'],
            'B' => ['##### ', '#    #', '##### ', '#    #', '##### '],
            'C' => [' #####', '#     ', '#     ', '#     ', ' #####'],
            'D' => ['##### ', '#    #', '#    #', '#    #', '##### '],
            'E' => ['######', '#     ', '##### ', '#     ', '######'],
            'F' => ['######', '#     ', '##### ', '#     ', '#     '],
            'G' => [' #####', '#     ', '#  ###', '#    #', ' #####'],
            'H' => ['#    #', '#    #', '######', '#    #', '#    #'],
            'I' => ['######', '  ##  ', '  ##  ', '  ##  ', '######'],
            'J' => ['######', '     #', '     #', '#    #', ' #### '],
            'K' => ['#   ##', '# ##  ', '###   ', '# ##  ', '#   ##'],
            'L' => ['#     ', '#     ', '#     ', '#     ', '######'],
            'M' => ['#    #', '##  ##', '# ## #', '#    #', '#    #'],
            'N' => ['#    #', '##   #', '# #  #', '#  # #', '#   ##'],
            'O' => [' #### ', '#    #', '#    #', '#    #', ' #### '],
            'P' => ['##### ', '#    #', '##### ', '#     ', '#     '],
            'Q' => [' #### ', '#    #', '#  # #', '#   ##', ' #####'],
            'R' => ['##### ', '#    #', '##### ', '# #   ', '#   ##'],
            'S' => [' #####', '#     ', ' #### ', '     #', '##### '],
            'T' => ['######', '  ##  ', '  ##  ', '  ##  ', '  ##  '],
            'U' => ['#    #', '#    #', '#    #', '#    #', ' #### '],
            'V' => ['#    #', '#    #', '#    #', ' #  # ', '  ##  '],
            'W' => ['#    #', '#    #', '# ## #', '##  ##', '#    #'],
            'X' => ['#    #', ' #  # ', '  ##  ', ' #  # ', '#    #'],
            'Y' => ['#    #', '#    #', ' #### ', '  ##  ', '  ##  '],
            'Z' => ['######', '    ##', '  ##  ', '##    ', '######'],
            '0' => [' #### ', '#    #', '#    #', '#    #', ' #### '],
            '1' => ['  ##  ', ' ###  ', '  ##  ', '  ##  ', '######'],
            '2' => [' #### ', '#    #', '  ### ', ' ##   ', '######'],
            '3' => ['##### ', '    ##', '  ### ', '    ##', '##### '],
            '4' => ['#    #', '#    #', '######', '     #', '     #'],
            '5' => ['######', '#     ', '##### ', '     #', '##### '],
            '6' => [' #### ', '#     ', '##### ', '#    #', ' #### '],
            '7' => ['######', '     #', '    # ', '   #  ', '   #  '],
            '8' => [' #### ', '#    #', ' #### ', '#    #', ' #### '],
            '9' => [' #### ', '#    #', ' #####', '     #', ' #### '],
            '!' => ['  ##  ', '  ##  ', '  ##  ', '      ', '  ##  '],
            '?' => [' #### ', '#    #', '  ### ', '      ', '  ##  '],
            '.' => ['      ', '      ', '      ', '  ##  ', '  ##  '],
            ',' => ['      ', '      ', '      ', '  ##  ', '  #   '],
            '-' => ['      ', '      ', '######', '      ', '      '],
            '_' => ['      ', '      ', '      ', '      ', '######'],
            ':' => ['      ', '  ##  ', '      ', '  ##  ', '      '],
            '/' => ['     #', '   ## ', '  ##  ', ' ##   ', '#     '],
            '\\' => ['#     ', ' ##   ', '  ##  ', '   ## ', '     #'],
            '(' => ['   ## ', '  ##  ', '  #   ', '  ##  ', '   ## '],
            ')' => [' ##   ', '  ##  ', '   #  ', '  ##  ', ' ##   '],
            '@' => [' #### ', '#    #', '# ## #', '#  ###', ' #### '],
            '#' => [' #  # ', '######', ' #  # ', '######', ' #  # '],
            '*' => ['#    #', ' #  # ', '######', ' #  # ', '#    #'],
            '+' => ['  ##  ', '  ##  ', '######', '  ##  ', '  ##  '],
            '=' => ['      ', '######', '      ', '######', '      '],
            '<' => ['   ###', '  ##  ', ' #    ', '  ##  ', '   ###'],
            '>' => ['###   ', '  ##  ', '    # ', '  ##  ', '###   '],
            '"' => [' #  # ', ' #  # ', '      ', '      ', '      '],
            "'" => [' ##   ', '  #   ', '      ', '      ', '      '],
            ';' => ['      ', '  ##  ', '      ', '  ##  ', '  #   '],
            '&' => [' #### ', '#  #  ', ' ##   ', '#  # #', ' ## ##'],
            '%' => ['#   ##', ' # #  ', '  #   ', '  # # ', '##   #'],
        ];
    }

    /**
     * Tests an upper-cased letter against a metaphone character class.
     *
     * The classes are the bit flags of the algorithm's lookup table: 1 marks a
     * vowel (AEIOU), 2 a letter passed through unchanged (FJMNR), 4 a letter
     * forming a diphthong before H (CGPST), 8 a letter making C and G soft
     * (EIY), and 16 a letter preventing GH from becoming F (BDH).
     *
     * @param string $letter The upper-cased letter to test
     * @param int $trait The character class bit flag to test for
     * @return bool True when `$letter` belongs to the given class
     */
    private static function hasMetaphoneTrait(string $letter, int $trait): bool
    {
        // Indexed by A-Z; taken from the reference implementation of the algorithm
        static $codes = [
            1, 16, 4, 16, 9, 2, 4, 16, 9, 2, 0, 2, 2, 2, 1, 4, 0, 2, 4, 4, 1, 0, 0, 0, 8, 0,
        ];

        if ($letter < 'A' || $letter > 'Z') {
            return false;
        }

        return ($codes[ord($letter) - 65] & $trait) !== 0;
    }

    /**
     * Determines whether a byte is an ASCII letter.
     *
     * @param string $byte The single byte to test
     * @return bool True when the byte is in A-Z or a-z
     */
    private static function isMetaphoneLetter(string $byte): bool
    {
        return ($byte >= 'A' && $byte <= 'Z') || ($byte >= 'a' && $byte <= 'z');
    }

    /**
     * Looks ahead a fixed number of bytes from an offset, stopping at the string end.
     *
     * @param string $word The ASCII string being encoded
     * @param int $index The byte offset to look ahead from
     * @param int $distance The number of bytes to look ahead
     * @return string The upper-cased byte found, or "\0" when the string ends first
     */
    private static function lookAheadMetaphone(string $word, int $index, int $distance): string
    {
        $offset = 0;

        while ($offset < $distance && self::readMetaphoneByte($word, $index + $offset) !== "\0") {
            $offset++;
        }

        return self::readMetaphoneLetter($word, $index + $offset);
    }

    /**
     * Computes the metaphone key of an ASCII string.
     *
     * Pure PHP implementation of the traditional metaphone algorithm, kept
     * byte-for-byte compatible with PHP's native `metaphone()`, which is
     * deprecated as of PHP 8.6. The input is treated as single-byte ASCII and
     * terminates at the first NUL byte, matching the native implementation.
     *
     * @param string $word The ASCII string to encode
     * @param int $maxPhonemes The maximum number of phonemes to return; 0 means no limit
     * @return string The metaphone phonetic key
     */
    private static function phonizeMetaphone(string $word, int $maxPhonemes): string
    {
        $phoned = '';
        $index = 0;

        // Skip leading non-letters; a word without any letter encodes to nothing
        while (true) {
            $raw = self::readMetaphoneByte($word, $index);

            if ($raw === "\0") {
                return '';
            }

            if (self::isMetaphoneLetter($raw)) {
                break;
            }

            $index++;
        }

        // The first phoneme is special-cased so that leading vowels are preserved
        $current = self::readMetaphoneLetter($word, $index);
        $next = self::readMetaphoneLetter($word, $index + 1);

        switch ($current) {
            case 'A':
                // AE becomes E, any other leading A is kept
                $phoned .= $next === 'E' ? 'E' : 'A';
                $index += $next === 'E' ? 2 : 1;
                break;
            case 'G':
            case 'K':
            case 'P':
                // [GKP]N becomes N
                if ($next === 'N') {
                    $phoned .= 'N';
                    $index += 2;
                }
                break;
            case 'W':
                // WR becomes R; WH and W before a vowel become W
                if ($next === 'R') {
                    $phoned .= 'R';
                    $index += 2;
                } elseif ($next === 'H' || self::hasMetaphoneTrait($next, 1)) {
                    $phoned .= 'W';
                    $index += 2;
                }
                break;
            case 'X':
                // X becomes S
                $phoned .= 'S';
                $index++;
                break;
            case 'E':
            case 'I':
            case 'O':
            case 'U':
                // Remaining leading vowels are kept as-is
                $phoned .= $current;
                $index++;
                break;
        }

        while (($raw = self::readMetaphoneByte($word, $index)) !== "\0") {
            if ($maxPhonemes !== 0 && strlen($phoned) >= $maxPhonemes) {
                break;
            }

            // Letters consumed by the current phoneme and skipped on the next pass
            $skip = 0;

            if (!self::isMetaphoneLetter($raw)) {
                $index++;

                continue;
            }

            $current = self::readMetaphoneLetter($word, $index);
            $previous = $index >= 1 ? self::readMetaphoneLetter($word, $index - 1) : "\0";
            $next = self::readMetaphoneLetter($word, $index + 1);
            $afterNext = $next === "\0" ? "\0" : self::readMetaphoneLetter($word, $index + 2);

            // Doubled letters collapse into one, except CC
            if ($current === $previous && $current !== 'C') {
                $index++;

                continue;
            }

            switch ($current) {
                case 'B':
                    // B unless it closes MB
                    if ($previous !== 'M') {
                        $phoned .= 'B';
                    }
                    break;
                case 'C':
                    if (self::hasMetaphoneTrait($next, 8)) {
                        if ($next === 'I' && $afterNext === 'A') {
                            // CIA
                            $phoned .= 'X';
                        } elseif ($previous !== 'S') {
                            // C[IEY], but dropped in SC[IEY]
                            $phoned .= 'S';
                        }
                    } elseif ($next === 'H') {
                        $phoned .= 'X';
                        $skip++;
                    } else {
                        $phoned .= 'K';
                    }
                    break;
                case 'D':
                    // J in DG[IEY], otherwise T
                    if ($next === 'G' && self::hasMetaphoneTrait($afterNext, 8)) {
                        $phoned .= 'J';
                        $skip++;
                    } else {
                        $phoned .= 'T';
                    }
                    break;
                case 'G':
                    if ($next === 'H') {
                        // GH is F unless preceded by B, D or H at distance 3, or by H at distance 4
                        $back3 = $index >= 3 ? self::readMetaphoneLetter($word, $index - 3) : "\0";
                        $back4 = $index >= 4 ? self::readMetaphoneLetter($word, $index - 4) : "\0";

                        if (!self::hasMetaphoneTrait($back3, 16) && $back4 !== 'H') {
                            $phoned .= 'F';
                            $skip++;
                        }
                    } elseif ($next === 'N') {
                        // Silent in GN at a word break and in GNED, otherwise K
                        $isGned = $afterNext === 'E' && self::lookAheadMetaphone($word, $index, 3) === 'D';

                        if (self::isMetaphoneLetter($afterNext) && !$isGned) {
                            $phoned .= 'K';
                        }
                    } elseif (self::hasMetaphoneTrait($next, 8) && $previous !== 'G') {
                        $phoned .= 'J';
                    } else {
                        $phoned .= 'K';
                    }
                    break;
                case 'H':
                    // H before a vowel, unless it forms a diphthong with C, G, P, S or T
                    if (self::hasMetaphoneTrait($next, 1) && !self::hasMetaphoneTrait($previous, 4)) {
                        $phoned .= 'H';
                    }
                    break;
                case 'K':
                    // Dropped after C
                    if ($previous !== 'C') {
                        $phoned .= 'K';
                    }
                    break;
                case 'P':
                    // F before H, otherwise P
                    $phoned .= $next === 'H' ? 'F' : 'P';
                    break;
                case 'Q':
                    $phoned .= 'K';
                    break;
                case 'S':
                    if ($next === 'I' && ($afterNext === 'O' || $afterNext === 'A')) {
                        // SIO and SIA
                        $phoned .= 'X';
                    } elseif ($next === 'H') {
                        $phoned .= 'X';
                        $skip++;
                    } else {
                        $phoned .= 'S';
                    }
                    break;
                case 'T':
                    if ($next === 'I' && ($afterNext === 'O' || $afterNext === 'A')) {
                        // TIO and TIA
                        $phoned .= 'X';
                    } elseif ($next === 'H') {
                        $phoned .= '0';
                        $skip++;
                    } elseif (!($next === 'C' && $afterNext === 'H')) {
                        // Dropped in TCH, otherwise T
                        $phoned .= 'T';
                    }
                    break;
                case 'V':
                    $phoned .= 'F';
                    break;
                case 'W':
                    // W before a vowel, otherwise dropped
                    if (self::hasMetaphoneTrait($next, 1)) {
                        $phoned .= 'W';
                    }
                    break;
                case 'X':
                    $phoned .= 'KS';
                    break;
                case 'Y':
                    // Y before a vowel, otherwise dropped
                    if (self::hasMetaphoneTrait($next, 1)) {
                        $phoned .= 'Y';
                    }
                    break;
                case 'Z':
                    $phoned .= 'S';
                    break;
                case 'F':
                case 'J':
                case 'L':
                case 'M':
                case 'N':
                case 'R':
                    // Passed through unchanged
                    $phoned .= $current;
                    break;
            }

            $index += $skip + 1;
        }

        return $phoned;
    }

    /**
     * Reads a single raw byte from a metaphone input string.
     *
     * Returns a NUL byte for out-of-range offsets, mirroring the NUL-terminated
     * string the native implementation walks.
     *
     * @param string $word The ASCII string being encoded
     * @param int $index The byte offset to read
     * @return string The byte at `$index`, or "\0" when out of range
     */
    private static function readMetaphoneByte(string $word, int $index): string
    {
        return $index >= 0 && $index < strlen($word) ? $word[$index] : "\0";
    }

    /**
     * Reads a single byte from a metaphone input string, upper-cased.
     *
     * @param string $word The ASCII string being encoded
     * @param int $index The byte offset to read
     * @return string The upper-cased byte at `$index`, or "\0" when out of range
     */
    private static function readMetaphoneLetter(string $word, int $index): string
    {
        $byte = self::readMetaphoneByte($word, $index);

        return $byte >= 'a' && $byte <= 'z' ? chr(ord($byte) - 32) : $byte;
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
