<?php

declare(strict_types=1);

namespace Phuture\Coherence\Type;

use Phuture\Coherence\Interface\Stringable;
use Phuture\Coherence\Support\FluentClass;
use Phuture\Coherence\Strings as Transformer;

/**
 * A fluent wrapper around the Strings utility class for chainable string manipulation.
 *
 * Each method delegates to the corresponding static method on `Strings`, stores the
 * result internally, and returns `$this` to enable method chaining. Retrieve the final
 * value by calling `get()` or invoking the object directly.
 *
 * Example:
 * ```php
 * use Phuture\Coherence\Type\Strings;
 *
 * $result = Strings::from('  hello_world  ')
 *     ->trim()
 *     ->replace('_', ' ')
 *     ->upper()
 *     ->take(5)
 *     ->get();
 * // 'HELLO'
 * ```
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Strings extends FluentClass implements Stringable, \Stringable
{
    /**
     * Returns the string representation of the wrapped value.
     *
     * @return string The wrapped string value
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Converts the wrapped value to a string.
     *
     * @return string The wrapped string value
     */
    public function toString(): string
    {
        return (string) $this->data;
    }

    // =========================================================================
    // Extraction
    // =========================================================================

    /**
     * Returns the portion of the string after the first occurrence of a search value.
     *
     * @param string $search The value to search for
     * @return self
     * @see Transformer::after()
     */
    public function after(string $search): self
    {
        $this->data = Transformer::after((string) $this->data, $search);

        return $this;
    }

    /**
     * Returns the portion of the string after the last occurrence of a search value.
     *
     * @param string $search The value to search for
     * @return self
     * @see Transformer::afterLast()
     */
    public function afterLast(string $search): self
    {
        $this->data = Transformer::afterLast((string) $this->data, $search);

        return $this;
    }

    /**
     * Returns the portion of the string before the first occurrence of a search value.
     *
     * @param string $search The value to search for
     * @return self
     * @see Transformer::before()
     */
    public function before(string $search): self
    {
        $this->data = Transformer::before((string) $this->data, $search);

        return $this;
    }

    /**
     * Returns the portion of the string before the last occurrence of a search value.
     *
     * @param string $search The value to search for
     * @return self
     * @see Transformer::beforeLast()
     */
    public function beforeLast(string $search): self
    {
        $this->data = Transformer::beforeLast((string) $this->data, $search);

        return $this;
    }

    /**
     * Returns the portion of the string between two delimiter values.
     *
     * @param string $start The opening delimiter
     * @param string $end The closing delimiter
     * @return self
     * @see Transformer::between()
     */
    public function between(string $start, string $end): self
    {
        $this->data = Transformer::between((string) $this->data, $start, $end);

        return $this;
    }

    /**
     * Returns the character at the given index position.
     *
     * @param int $index The zero-based character index (negative counts from the end)
     * @return self
     * @see Transformer::charAt()
     */
    public function charAt(int $index): self
    {
        $this->data = Transformer::charAt((string) $this->data, $index);

        return $this;
    }

    /**
     * Returns the first N characters of the string.
     *
     * @param int $count The number of characters to return (default: 1)
     * @return self
     * @see Transformer::first()
     */
    public function first(int $count = 1): self
    {
        $this->data = Transformer::first((string) $this->data, $count);

        return $this;
    }

    /**
     * Returns the last N characters of the string.
     *
     * @param int $count The number of characters to return (default: 1)
     * @return self
     * @see Transformer::last()
     */
    public function last(int $count = 1): self
    {
        $this->data = Transformer::last((string) $this->data, $count);

        return $this;
    }

    /**
     * Extracts a portion of the string by start position and optional length.
     *
     * @param int $start The starting position (negative counts from the end)
     * @param int|null $length The number of characters to return (null returns to the end)
     * @return self
     * @see Transformer::slice()
     */
    public function slice(int $start, ?int $length = null): self
    {
        $this->data = Transformer::slice((string) $this->data, $start, $length);

        return $this;
    }

    /**
     * Returns the first or last N characters based on the sign of count.
     *
     * @param int $count Positive returns first N; negative returns last N characters
     * @return self
     * @see Transformer::take()
     */
    public function take(int $count): self
    {
        $this->data = Transformer::take((string) $this->data, $count);

        return $this;
    }

    /**
     * Returns the last N characters of the string.
     *
     * @param int $count The number of characters to return from the end
     * @return self
     * @see Transformer::takeRight()
     */
    public function takeRight(int $count): self
    {
        $this->data = Transformer::takeRight((string) $this->data, $count);

        return $this;
    }

    // =========================================================================
    // Case conversion
    // =========================================================================

    /**
     * Converts the string to camelCase.
     *
     * @return self
     * @see Transformer::camel()
     */
    public function camel(): self
    {
        $this->data = Transformer::camel((string) $this->data);

        return $this;
    }

    /**
     * Converts every word in the string to Title Case.
     *
     * @return self
     * @see Transformer::capitalize()
     */
    public function capitalize(): self
    {
        $this->data = Transformer::capitalize((string) $this->data);

        return $this;
    }

    /**
     * Converts the string to a human-readable headline format.
     *
     * @return self
     * @see Transformer::headline()
     */
    public function headline(): self
    {
        $this->data = Transformer::headline((string) $this->data);

        return $this;
    }

    /**
     * Converts the string to kebab-case.
     *
     * @return self
     * @see Transformer::kebab()
     */
    public function kebab(): self
    {
        $this->data = Transformer::kebab((string) $this->data);

        return $this;
    }

    /**
     * Converts the string to lowercase.
     *
     * @return self
     * @see Transformer::lower()
     */
    public function lower(): self
    {
        $this->data = Transformer::lower((string) $this->data);

        return $this;
    }

    /**
     * Converts only the first character of the string to lowercase.
     *
     * @return self
     * @see Transformer::lowerFirst()
     */
    public function lowerFirst(): self
    {
        $this->data = Transformer::lowerFirst((string) $this->data);

        return $this;
    }

    /**
     * Converts the string to PascalCase (StudlyCase).
     *
     * @return self
     * @see Transformer::pascal()
     */
    public function pascal(): self
    {
        $this->data = Transformer::pascal((string) $this->data);

        return $this;
    }

    /**
     * Converts the string to snake_case with a configurable delimiter.
     *
     * @param string $delimiter The word separator character (default: '_')
     * @return self
     * @see Transformer::snake()
     */
    public function snake(string $delimiter = '_'): self
    {
        $this->data = Transformer::snake((string) $this->data, $delimiter);

        return $this;
    }

    /**
     * Converts every word in the string to Title Case.
     *
     * @return self
     * @see Transformer::title()
     */
    public function title(): self
    {
        $this->data = Transformer::title((string) $this->data);

        return $this;
    }

    /**
     * Converts the string to uppercase.
     *
     * @return self
     * @see Transformer::upper()
     */
    public function upper(): self
    {
        $this->data = Transformer::upper((string) $this->data);

        return $this;
    }

    // =========================================================================
    // Modification
    // =========================================================================

    /**
     * Collapses consecutive duplicate occurrences of a character.
     *
     * @param string $character The character to collapse (default: space)
     * @return self
     * @see Transformer::dedupe()
     */
    public function dedupe(string $character = ' '): self
    {
        $this->data = Transformer::dedupe((string) $this->data, $character);

        return $this;
    }

    /**
     * Ensures the string ends with exactly one occurrence of the given suffix.
     *
     * @param string $suffix The suffix to ensure is present exactly once
     * @return self
     * @see Transformer::finish()
     */
    public function finish(string $suffix): self
    {
        $this->data = Transformer::finish((string) $this->data, $suffix);

        return $this;
    }

    /**
     * Inserts a substring into the string at the given index position.
     *
     * @param string $substring The substring to insert
     * @param int $index The zero-based position to insert at (negative counts from the end)
     * @return self
     * @see Transformer::insert()
     */
    public function insert(string $substring, int $index): self
    {
        $this->data = Transformer::insert((string) $this->data, $substring, $index);

        return $this;
    }

    /**
     * Masks a portion of the string with a repeated mask character.
     *
     * @param string $mask The mask character to use (default: '*')
     * @param int $offset The start position to begin masking
     * @param int|null $length The number of characters to mask (null masks to the end)
     * @return self
     * @see Transformer::mask()
     */
    public function mask(string $mask = '*', int $offset = 0, ?int $length = null): self
    {
        $this->data = Transformer::mask((string) $this->data, $mask, $offset, $length);

        return $this;
    }

    /**
     * Pads the string to a given length using a pad string.
     *
     * @param int $length The target total length
     * @param string $padString The string to pad with (default: space)
     * @param int $padType One of STR_PAD_RIGHT, STR_PAD_LEFT, STR_PAD_BOTH
     * @return self
     * @see Transformer::pad()
     */
    public function pad(int $length, string $padString = ' ', int $padType = STR_PAD_RIGHT): self
    {
        $this->data = Transformer::pad((string) $this->data, $length, $padString, $padType);

        return $this;
    }

    /**
     * Pads the string to a given length by adding equal padding on both sides.
     *
     * @param int $length The target total length
     * @param string $padString The string to pad with (default: space)
     * @return self
     * @see Transformer::padBoth()
     */
    public function padBoth(int $length, string $padString = ' '): self
    {
        $this->data = Transformer::padBoth((string) $this->data, $length, $padString);

        return $this;
    }

    /**
     * Pads the string to a given length by prepending on the left.
     *
     * @param int $length The target total length
     * @param string $padString The string to pad with (default: space)
     * @return self
     * @see Transformer::padLeft()
     */
    public function padLeft(int $length, string $padString = ' '): self
    {
        $this->data = Transformer::padLeft((string) $this->data, $length, $padString);

        return $this;
    }

    /**
     * Pads the string to a given length by appending on the right.
     *
     * @param int $length The target total length
     * @param string $padString The string to pad with (default: space)
     * @return self
     * @see Transformer::padRight()
     */
    public function padRight(int $length, string $padString = ' '): self
    {
        $this->data = Transformer::padRight((string) $this->data, $length, $padString);

        return $this;
    }

    /**
     * Removes all occurrences of a search value from the string.
     *
     * @param string $search The value to remove
     * @param bool $caseSensitive Whether the removal is case-sensitive (default: true)
     * @return self
     * @see Transformer::remove()
     */
    public function remove(string $search, bool $caseSensitive = true): self
    {
        $this->data = Transformer::remove((string) $this->data, $search, $caseSensitive);

        return $this;
    }

    /**
     * Repeats the string a given number of times.
     *
     * @param int $times The number of repetitions (must be zero or greater)
     * @return self
     * @see Transformer::repeat()
     */
    public function repeat(int $times): self
    {
        $this->data = Transformer::repeat((string) $this->data, $times);

        return $this;
    }

    /**
     * Replaces all occurrences of a search value with a replacement.
     *
     * @param string $search The value to search for
     * @param string $replace The replacement value
     * @param bool $caseSensitive Whether the replacement is case-sensitive (default: true)
     * @return self
     * @see Transformer::replace()
     */
    public function replace(string $search, string $replace, bool $caseSensitive = true): self
    {
        $this->data = Transformer::replace((string) $this->data, $search, $replace, $caseSensitive);

        return $this;
    }

    /**
     * Replaces the first occurrence of a search value with a replacement.
     *
     * @param string $search The value to search for
     * @param string $replace The replacement value
     * @return self
     * @see Transformer::replaceFirst()
     */
    public function replaceFirst(string $search, string $replace): self
    {
        $this->data = Transformer::replaceFirst((string) $this->data, $search, $replace);

        return $this;
    }

    /**
     * Replaces the last occurrence of a search value with a replacement.
     *
     * @param string $search The value to search for
     * @param string $replace The replacement value
     * @return self
     * @see Transformer::replaceLast()
     */
    public function replaceLast(string $search, string $replace): self
    {
        $this->data = Transformer::replaceLast((string) $this->data, $search, $replace);

        return $this;
    }

    /**
     * Reverses the string character by character.
     *
     * @return self
     * @see Transformer::reverse()
     */
    public function reverse(): self
    {
        $this->data = Transformer::reverse((string) $this->data);

        return $this;
    }

    /**
     * Removes dangerous control characters from the string.
     *
     * @return self
     * @see Transformer::scrub()
     */
    public function scrub(): self
    {
        $this->data = Transformer::scrub((string) $this->data);

        return $this;
    }

    /**
     * Generates a URL-friendly slug from the string.
     *
     * @param string $separator The separator character between words (default: '-')
     * @param string $language The language code for transliteration (default: 'en')
     * @return self
     * @see Transformer::slug()
     */
    public function slug(string $separator = '-', string $language = 'en'): self
    {
        $this->data = Transformer::slug((string) $this->data, $separator, $language);

        return $this;
    }

    /**
     * Collapses all whitespace sequences into a single space and trims the result.
     *
     * @return self
     * @see Transformer::squish()
     */
    public function squish(): self
    {
        $this->data = Transformer::squish((string) $this->data);

        return $this;
    }

    /**
     * Ensures the string begins with exactly one occurrence of the given prefix.
     *
     * @param string $prefix The prefix to ensure is present exactly once
     * @return self
     * @see Transformer::start()
     */
    public function start(string $prefix): self
    {
        $this->data = Transformer::start((string) $this->data, $prefix);

        return $this;
    }

    /**
     * Strips HTML and PHP tags from the string.
     *
     * @param string $allowedTags HTML tags to preserve (default: '' = strip all)
     * @return self
     * @see Transformer::strip()
     */
    public function strip(string $allowedTags = ''): self
    {
        $this->data = Transformer::strip((string) $this->data, $allowedTags);

        return $this;
    }

    /**
     * Performs multiple simultaneous search-and-replace operations.
     *
     * @param array<string, string> $replacements A map of search => replacement pairs
     * @return self
     * @see Transformer::swap()
     */
    public function swap(array $replacements): self
    {
        $this->data = Transformer::swap((string) $this->data, $replacements);

        return $this;
    }

    /**
     * Strips whitespace (or given characters) from both ends of the string.
     *
     * @param string $characters The characters to strip (default: whitespace)
     * @return self
     * @see Transformer::trim()
     */
    public function trim(string $characters = " \t\n\r\0\x0B"): self
    {
        $this->data = Transformer::trim((string) $this->data, $characters);

        return $this;
    }

    /**
     * Strips whitespace (or given characters) from the beginning of the string.
     *
     * @param string $characters The characters to strip (default: whitespace)
     * @return self
     * @see Transformer::trimLeft()
     */
    public function trimLeft(string $characters = " \t\n\r\0\x0B"): self
    {
        $this->data = Transformer::trimLeft((string) $this->data, $characters);

        return $this;
    }

    /**
     * Strips whitespace (or given characters) from the end of the string.
     *
     * @param string $characters The characters to strip (default: whitespace)
     * @return self
     * @see Transformer::trimRight()
     */
    public function trimRight(string $characters = " \t\n\r\0\x0B"): self
    {
        $this->data = Transformer::trimRight((string) $this->data, $characters);

        return $this;
    }

    /**
     * Truncates the string to an exact character length.
     *
     * @param int $length The maximum number of characters to keep
     * @param string $end The string to append after truncation (default: '')
     * @return self
     * @see Transformer::truncate()
     */
    public function truncate(int $length, string $end = ''): self
    {
        $this->data = Transformer::truncate((string) $this->data, $length, $end);

        return $this;
    }

    /**
     * Removes a surrounding wrapper string from both ends of the string.
     *
     * @param string $wrapper The wrapper string to remove from both ends
     * @return self
     * @see Transformer::unwrap()
     */
    public function unwrap(string $wrapper): self
    {
        $this->data = Transformer::unwrap((string) $this->data, $wrapper);

        return $this;
    }

    /**
     * Wraps the string with a given wrapper string on both sides.
     *
     * @param string $wrapper The string to prepend and append
     * @return self
     * @see Transformer::wrap()
     */
    public function wrap(string $wrapper): self
    {
        $this->data = Transformer::wrap((string) $this->data, $wrapper);

        return $this;
    }

    // =========================================================================
    // Truncation
    // =========================================================================

    /**
     * Extracts a contextual excerpt of the string around a given phrase.
     *
     * @param string $phrase The phrase to centre the excerpt around
     * @param int $radius The number of characters to include on each side (default: 100)
     * @param string $omission The string to append at truncated ends (default: '...')
     * @return self
     * @see Transformer::excerpt()
     */
    public function excerpt(string $phrase, int $radius = 100, string $omission = '...'): self
    {
        $this->data = Transformer::excerpt((string) $this->data, $phrase, $radius, $omission);

        return $this;
    }

    /**
     * Limits the string to a given number of characters, appending an omission marker.
     *
     * @param int $limit The maximum number of characters before truncation
     * @param string $end The string to append after truncation (default: '...')
     * @return self
     * @see Transformer::limit()
     */
    public function limit(int $limit, string $end = '...'): self
    {
        $this->data = Transformer::limit((string) $this->data, $limit, $end);

        return $this;
    }

    /**
     * Wraps the string at a given number of characters.
     *
     * @param int $width The number of characters at which to wrap (default: 75)
     * @param string $break The line break string to insert (default: "\n")
     * @param bool $cutLongWords Whether to cut words longer than width (default: false)
     * @return self
     * @see Transformer::wordWrap()
     */
    public function wordWrap(int $width = 75, string $break = "\n", bool $cutLongWords = false): self
    {
        $this->data = Transformer::wordWrap((string) $this->data, $width, $break, $cutLongWords);

        return $this;
    }

    // =========================================================================
    // Splitting & conversion
    // =========================================================================

    /**
     * Splits the string into an array of chunks of the given size.
     *
     * @param int $size The number of characters per chunk
     * @return self
     * @see Transformer::chunk()
     */
    public function chunk(int $size): self
    {
        $this->data = Transformer::chunk((string) $this->data, $size);

        return $this;
    }

    /**
     * Splits the string into an array using a delimiter.
     *
     * @param string $delimiter The boundary string
     * @param int $limit Maximum number of returned elements (default: PHP_INT_MAX)
     * @return self
     * @see Transformer::explode()
     */
    public function explode(string $delimiter, int $limit = PHP_INT_MAX): self
    {
        $this->data = Transformer::explode((string) $this->data, $delimiter, $limit);

        return $this;
    }

    /**
     * Splits the string into an array by a literal pattern.
     *
     * @param string $pattern The literal separator to split on
     * @param int $limit Maximum number of elements to return (default: -1 = no limit)
     * @return self
     * @see Transformer::split()
     */
    public function split(string $pattern, int $limit = -1): self
    {
        $this->data = Transformer::split((string) $this->data, $pattern, $limit);

        return $this;
    }

    /**
     * Converts the string to an array of individual characters.
     *
     * @return self
     * @see Transformer::toArray()
     */
    public function toArray(): self
    {
        $this->data = Transformer::toArray((string) $this->data);

        return $this;
    }

    /**
     * Extracts the words from the string into an array.
     *
     * @param int $limit Maximum number of words to return, -1 = no limit (default: -1)
     * @param string $end String appended after the word list when limited (default: '')
     * @return self
     * @see Transformer::words()
     */
    public function words(int $limit = -1, string $end = ''): self
    {
        $this->data = Transformer::words((string) $this->data, $limit, $end);

        return $this;
    }

    // =========================================================================
    // Encoding & conversion
    // =========================================================================

    /**
     * Transliterates the string to its ASCII representation.
     *
     * @param string $language The language code for locale-specific rules (default: 'en')
     * @return self
     * @see Transformer::ascii()
     */
    public function ascii(string $language = 'en'): self
    {
        $this->data = Transformer::ascii((string) $this->data, $language);

        return $this;
    }

    // =========================================================================
    // Testing & checking (stores bool in data)
    // =========================================================================

    /**
     * Determines whether the string ends with a given search value.
     *
     * @param string $search The expected suffix
     * @return self
     * @see Transformer::endsWith()
     */
    public function endsWith(string $search): self
    {
        $this->data = Transformer::endsWith((string) $this->data, $search);

        return $this;
    }

    /**
     * Determines whether the string contains a given search value.
     *
     * @param string $search The value to look for
     * @param bool $caseSensitive Whether the search is case-sensitive (default: true)
     * @return self
     * @see Transformer::has()
     */
    public function has(string $search, bool $caseSensitive = true): self
    {
        $this->data = Transformer::has((string) $this->data, $search, $caseSensitive);

        return $this;
    }

    /**
     * Determines whether the string contains all of the given search values.
     *
     * @param array<int, string> $searches The values to look for
     * @param bool $caseSensitive Whether the searches are case-sensitive (default: true)
     * @return self
     * @see Transformer::hasAll()
     */
    public function hasAll(array $searches, bool $caseSensitive = true): self
    {
        $this->data = Transformer::hasAll((string) $this->data, $searches, $caseSensitive);

        return $this;
    }

    /**
     * Determines whether the string contains none of the given search values.
     *
     * @param array<int, string> $searches The values to check for absence
     * @param bool $caseSensitive Whether the searches are case-sensitive (default: true)
     * @return self
     * @see Transformer::hasNone()
     */
    public function hasNone(array $searches, bool $caseSensitive = true): self
    {
        $this->data = Transformer::hasNone((string) $this->data, $searches, $caseSensitive);

        return $this;
    }

    /**
     * Determines whether the string matches a wildcard pattern.
     *
     * @param string $pattern The wildcard pattern (use `*` as wildcard)
     * @return self
     * @see Transformer::is()
     */
    public function is(string $pattern): self
    {
        $this->data = Transformer::is((string) $this->data, $pattern);

        return $this;
    }

    /**
     * Determines whether the string contains only alphabetic characters.
     *
     * @return self
     * @see Transformer::isAlpha()
     */
    public function isAlpha(): self
    {
        $this->data = Transformer::isAlpha((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string contains only alphanumeric characters.
     *
     * @return self
     * @see Transformer::isAlphanumeric()
     */
    public function isAlphanumeric(): self
    {
        $this->data = Transformer::isAlphanumeric((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string contains only ASCII characters.
     *
     * @return self
     * @see Transformer::isAscii()
     */
    public function isAscii(): self
    {
        $this->data = Transformer::isAscii((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string contains only whitespace characters (or is empty).
     *
     * @return self
     * @see Transformer::isBlank()
     */
    public function isBlank(): self
    {
        $this->data = Transformer::isBlank((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string is a valid email address.
     *
     * @return self
     * @see Transformer::isEmail()
     */
    public function isEmail(): self
    {
        $this->data = Transformer::isEmail((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string is exactly empty (zero-length).
     *
     * @return self
     * @see Transformer::isEmpty()
     */
    public function isEmpty(): self
    {
        $this->data = Transformer::isEmpty((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string is non-empty and contains at least one non-whitespace character.
     *
     * @return self
     * @see Transformer::isFilled()
     */
    public function isFilled(): self
    {
        $this->data = Transformer::isFilled((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string is valid JSON.
     *
     * @return self
     * @see Transformer::isJson()
     */
    public function isJson(): self
    {
        $this->data = Transformer::isJson((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string is entirely lowercase.
     *
     * @return self
     * @see Transformer::isLower()
     */
    public function isLower(): self
    {
        $this->data = Transformer::isLower((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string matches a regular expression pattern.
     *
     * @param string $pattern The full regular expression pattern including delimiters
     * @return self
     * @see Transformer::matches()
     */
    public function matches(string $pattern): self
    {
        $this->data = Transformer::matches((string) $this->data, $pattern);

        return $this;
    }

    /**
     * Determines whether the string is not empty (has at least one character).
     *
     * @return self
     * @see Transformer::isNotEmpty()
     */
    public function isNotEmpty(): self
    {
        $this->data = Transformer::isNotEmpty((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string represents a numeric value.
     *
     * @return self
     * @see Transformer::isNumeric()
     */
    public function isNumeric(): self
    {
        $this->data = Transformer::isNumeric((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string is entirely uppercase.
     *
     * @return self
     * @see Transformer::isUpper()
     */
    public function isUpper(): self
    {
        $this->data = Transformer::isUpper((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string is a valid URL.
     *
     * @return self
     * @see Transformer::isUrl()
     */
    public function isUrl(): self
    {
        $this->data = Transformer::isUrl((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string is a valid UUID.
     *
     * @return self
     * @see Transformer::isUuid()
     */
    public function isUuid(): self
    {
        $this->data = Transformer::isUuid((string) $this->data);

        return $this;
    }

    /**
     * Determines whether the string begins with a given search value.
     *
     * @param string $search The expected prefix
     * @return self
     * @see Transformer::startsWith()
     */
    public function startsWith(string $search): self
    {
        $this->data = Transformer::startsWith((string) $this->data, $search);

        return $this;
    }

    // =========================================================================
    // Counting & comparison (stores int/bool in data)
    // =========================================================================

    /**
     * Compares the string lexicographically against another string.
     *
     * @param string $other The string to compare against
     * @param bool $caseSensitive Whether the comparison is case-sensitive (default: true)
     * @return self
     * @see Transformer::compare()
     */
    public function compare(string $other, bool $caseSensitive = true): self
    {
        $this->data = Transformer::compare((string) $this->data, $other, $caseSensitive);

        return $this;
    }

    /**
     * Counts the number of non-overlapping occurrences of a substring.
     *
     * @param string $substring The substring to count
     * @return self
     * @see Transformer::countSubstring()
     */
    public function countSubstring(string $substring): self
    {
        $this->data = Transformer::countSubstring((string) $this->data, $substring);

        return $this;
    }

    /**
     * Determines whether the string is equal to another string.
     *
     * @param string $other The string to compare against
     * @param bool $caseSensitive Whether the comparison is case-sensitive (default: true)
     * @return self
     * @see Transformer::equals()
     */
    public function equals(string $other, bool $caseSensitive = true): self
    {
        $this->data = Transformer::equals((string) $this->data, $other, $caseSensitive);

        return $this;
    }

    /**
     * Returns the number of characters in the string.
     *
     * @return self
     * @see Transformer::length()
     */
    public function length(): self
    {
        $this->data = Transformer::length((string) $this->data);

        return $this;
    }

    /**
     * Returns the position of the first occurrence of a search value.
     *
     * @param string $search The value to search for
     * @param int $offset The offset from the start to begin searching (default: 0)
     * @return self
     * @see Transformer::position()
     */
    public function position(string $search, int $offset = 0): self
    {
        $this->data = Transformer::position((string) $this->data, $search, $offset);

        return $this;
    }

    /**
     * Returns the position of the last occurrence of a search value.
     *
     * @param string $search The value to search for
     * @param int $offset The offset from the start to begin searching (default: 0)
     * @return self
     * @see Transformer::lastPosition()
     */
    public function lastPosition(string $search, int $offset = 0): self
    {
        $this->data = Transformer::lastPosition((string) $this->data, $search, $offset);

        return $this;
    }

    /**
     * Returns the number of words in the string.
     *
     * @return self
     * @see Transformer::wordCount()
     */
    public function wordCount(): self
    {
        $this->data = Transformer::wordCount((string) $this->data);

        return $this;
    }
}
