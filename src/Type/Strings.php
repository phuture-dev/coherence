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
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
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

    /**
     * Returns the portion of the string after the first occurrence of a search value.
     *
     * @param string $search The value to search for
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
     * @see Transformer::take()
     */
    public function take(int $count): self
    {
        $this->data = Transformer::take((string) $this->data, $count);

        return $this;
    }

    /**
     * Converts the string to camelCase.
     *
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
     * @see Transformer::upper()
     */
    public function upper(): self
    {
        $this->data = Transformer::upper((string) $this->data);

        return $this;
    }

    /**
     * Collapses consecutive duplicate occurrences of a character.
     *
     * @param string $character The character to collapse (default: space)
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @param array $replacements An associative array where each key is the text to find and each value is the text to substitute
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
     * @see Transformer::wrap()
     */
    public function wrap(string $wrapper): self
    {
        $this->data = Transformer::wrap((string) $this->data, $wrapper);

        return $this;
    }

    /**
     * Extracts a contextual excerpt of the string around a given phrase.
     *
     * @param string $phrase The phrase to centre the excerpt around
     * @param int $radius The number of characters to include on each side (default: 100)
     * @param string $omission The string to append at truncated ends (default: '...')
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
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
     * @return self Returns the current instance for method chaining
     * @see Transformer::wordWrap()
     */
    public function wordWrap(int $width = 75, string $break = "\n", bool $cutLongWords = false): self
    {
        $this->data = Transformer::wordWrap((string) $this->data, $width, $break, $cutLongWords);

        return $this;
    }

    /**
     * Transliterates the string to its ASCII representation.
     *
     * @param string $language The language code for locale-specific rules (default: 'en')
     * @return self Returns the current instance for method chaining
     * @see Transformer::ascii()
     */
    public function ascii(string $language = 'en'): self
    {
        $this->data = Transformer::ascii((string) $this->data, $language);

        return $this;
    }

    /**
     * Censors all occurrences of banned words by replacing them with a substitution.
     *
     * @param array $bannedWords List of word strings to replace; each element must be a string
     * @param string $replacement The string to substitute for each matched word (default: '***')
     * @return self Returns the current instance for method chaining
     * @see Transformer::censor()
     */
    public function censor(array $bannedWords, string $replacement = '***'): self
    {
        $this->data = Transformer::censor((string) $this->data, $bannedWords, $replacement);

        return $this;
    }

    /**
     * Fixes invalid UTF-8 byte sequences in the string.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::fixEncoding()
     */
    public function fixEncoding(): self
    {
        $this->data = Transformer::fixEncoding((string) $this->data);

        return $this;
    }

    /**
     * Encodes the string to its Base64 representation.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::toBase64()
     */
    public function toBase64(): self
    {
        $this->data = Transformer::toBase64((string) $this->data);

        return $this;
    }

    /**
     * Decodes a Base64-encoded string.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::fromBase64()
     */
    public function fromBase64(): self
    {
        $this->data = Transformer::fromBase64((string) $this->data);

        return $this;
    }

    /**
     * Highlights all occurrences of a phrase by wrapping them in tags.
     *
     * @param string $phrase The phrase to highlight
     * @param string $tagOpen The opening tag (default: '<mark>')
     * @param string $tagClose The closing tag (default: '</mark>')
     * @return self Returns the current instance for method chaining
     * @see Transformer::highlight()
     */
    public function highlight(string $phrase, string $tagOpen = '<mark>', string $tagClose = '</mark>'): self
    {
        $this->data = Transformer::highlight((string) $this->data, $phrase, $tagOpen, $tagClose);

        return $this;
    }

    /**
     * Adds indentation to each line of the string.
     *
     * @param int $level The number of times to repeat the indent character (default: 1)
     * @param string $indentChar The character(s) used for one level of indentation (default: "\t")
     * @return self Returns the current instance for method chaining
     * @see Transformer::indent()
     */
    public function indent(int $level = 1, string $indentChar = "\t"): self
    {
        $this->data = Transformer::indent((string) $this->data, $level, $indentChar);

        return $this;
    }

    /**
     * Normalizes line endings to Unix-style `\n`.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::normalizeNewLines()
     */
    public function normalizeNewLines(): self
    {
        $this->data = Transformer::normalizeNewLines((string) $this->data);

        return $this;
    }

    /**
     * Replaces successive occurrences of a search value using values from an array.
     *
     * @param string $search The value to search for
     * @param array $replacements Ordered list of string replacement values
     * @return self Returns the current instance for method chaining
     * @see Transformer::replaceArray()
     */
    public function replaceArray(string $search, array $replacements): self
    {
        $this->data = Transformer::replaceArray($search, $replacements, (string) $this->data);

        return $this;
    }

    /**
     * Replaces a portion of the string starting at a given character position.
     *
     * @param string $replacement The text to insert at the given position
     * @param int $position The character index at which to begin replacement (negative counts from end)
     * @param int|null $length The number of characters to replace (null replaces to end of string)
     * @return self Returns the current instance for method chaining
     * @see Transformer::replaceBetween()
     */
    public function replaceBetween(string $replacement, int $position, ?int $length = null): self
    {
        $this->data = Transformer::replaceBetween((string) $this->data, $replacement, $position, $length);

        return $this;
    }
}
