<?php

declare(strict_types=1);

namespace Phuture\Coherence\Type;

use Override;
use Phuture\Coherence\Enum\PadDirection;
use Phuture\Coherence\Support\FluentClass;
use Phuture\Coherence\Interface\Stringable;
use Phuture\Coherence\Strings as Transformer;

/**
 * A fluent wrapper around the Strings utility class for chainable string manipulation.
 *
 * Each method delegates to the corresponding static method on `\Phuture\Coherence\Strings`, stores the
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
     * The character encoding used for all multibyte operations.
     *
     * @var string
     */
    protected string $encoding = 'UTF-8';

    /**
     * Returns the string representation of the wrapped value.
     *
     * @return string The wrapped string value
     */
    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Escapes specific characters using C-style backslash notation.
     *
     * @param string $characters The list of characters to escape
     * @return self Returns the current instance for method chaining
     * @see Transformer::addCSlashes()
     */
    public function addCSlashes(string $characters): self
    {
        $this->data = Transformer::addCSlashes($this->toString(), $characters);

        return $this;
    }

    /**
     * Escapes single quotes, double quotes, backslashes, and NUL bytes.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::addSlashes()
     */
    public function addSlashes(): self
    {
        $this->data = Transformer::addSlashes($this->toString());

        return $this;
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
        $this->data = Transformer::after($this->toString(), $search, $this->encoding);

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
        $this->data = Transformer::afterLast($this->toString(), $search, $this->encoding);

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
        $this->data = Transformer::ascii($this->toString(), $language);

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
        $this->data = Transformer::before($this->toString(), $search, $this->encoding);

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
        $this->data = Transformer::beforeLast($this->toString(), $search, $this->encoding);

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
        $this->data = Transformer::between($this->toString(), $start, $end, $this->encoding);

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
        $this->data = Transformer::camel($this->toString());

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
        $this->data = Transformer::censor($this->toString(), $bannedWords, $replacement);

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
        $this->data = Transformer::charAt($this->toString(), $index, $this->encoding);

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
        $this->data = Transformer::dedupe($this->toString(), $character);

        return $this;
    }

    /**
     * Calculates the Levenshtein edit distance between the string and another string.
     *
     * @param string $other The string to compare against
     * @return self Returns the current instance for method chaining
     * @see Transformer::distance()
     */
    public function distance(string $other): self
    {
        $this->data = Transformer::distance($this->toString(), $other);

        return $this;
    }

    /**
     * Converts HTML entities back to their corresponding characters.
     *
     * @param int $flags Bitmask of ENT_* constants (default: ENT_QUOTES | ENT_SUBSTITUTE)
     * @return self Returns the current instance for method chaining
     * @see Transformer::entityDecode()
     */
    public function entityDecode(int $flags = ENT_QUOTES | ENT_SUBSTITUTE): self
    {
        $this->data = Transformer::entityDecode($this->toString(), $flags, $this->encoding);

        return $this;
    }

    /**
     * Converts all applicable characters to HTML entities.
     *
     * @param int $flags Bitmask of ENT_* constants (default: ENT_QUOTES | ENT_SUBSTITUTE)
     * @return self Returns the current instance for method chaining
     * @see Transformer::entityEncode()
     */
    public function entityEncode(int $flags = ENT_QUOTES | ENT_SUBSTITUTE): self
    {
        $this->data = Transformer::entityEncode($this->toString(), $flags, $this->encoding);

        return $this;
    }

    /**
     * Extracts a contextual excerpt of the string around a given phrase.
     *
     * @param string $phrase The phrase to center the excerpt around
     * @param int $radius The number of characters to include on each side (default: 100)
     * @param string $omission The string to append at truncated ends (default: '...')
     * @return self Returns the current instance for method chaining
     * @see Transformer::excerpt()
     */
    public function excerpt(string $phrase, int $radius = 100, string $omission = '...'): self
    {
        $this->data = Transformer::excerpt($this->toString(), $phrase, $radius, $omission, $this->encoding);

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
        $this->data = Transformer::finish($this->toString(), $suffix);

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
        $this->data = Transformer::first($this->toString(), $count, $this->encoding);

        return $this;
    }

    /**
     * Fixes invalid byte sequences in the string using the current encoding.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::fixEncoding()
     */
    public function fixEncoding(): self
    {
        $this->data = Transformer::fixEncoding($this->toString(), $this->encoding);

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
        $this->data = Transformer::fromBase64($this->toString());

        return $this;
    }

    /**
     * Decodes a hex-encoded binary string.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::fromHex()
     */
    public function fromHex(): self
    {
        $this->data = Transformer::fromHex($this->toString());

        return $this;
    }

    /**
     * Calculates the Hamming distance between the string and another string.
     *
     * @param string $other The string to compare against; must have the same character length
     * @return self Returns the current instance for method chaining
     * @see Transformer::hamming()
     */
    public function hamming(string $other): self
    {
        $this->data = Transformer::hamming($this->toString(), $other, $this->encoding);

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
        $this->data = Transformer::headline($this->toString());

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
        $this->data = Transformer::highlight($this->toString(), $phrase, $tagOpen, $tagClose);

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
        $this->data = Transformer::indent($this->toString(), $level, $indentChar);

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
        $this->data = Transformer::insert($this->toString(), $substring, $index, $this->encoding);

        return $this;
    }

    /**
     * Calculates the Jaro similarity between the string and another string.
     *
     * @param string $other The string to compare against
     * @return self Returns the current instance for method chaining
     * @see Transformer::jaro()
     */
    public function jaro(string $other): self
    {
        $this->data = Transformer::jaro($this->toString(), $other, $this->encoding);

        return $this;
    }

    /**
     * Calculates the Jaro-Winkler similarity between the string and another string.
     *
     * @param string $other The string to compare against
     * @param float $prefixScale How much weight to give the common prefix; must not exceed 0.25 (default: 0.1)
     * @return self Returns the current instance for method chaining
     * @see Transformer::jaroWinkler()
     */
    public function jaroWinkler(string $other, float $prefixScale = 0.1): self
    {
        $this->data = Transformer::jaroWinkler($this->toString(), $other, $prefixScale, $this->encoding);

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
        $this->data = Transformer::kebab($this->toString());

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
        $this->data = Transformer::last($this->toString(), $count, $this->encoding);

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
    public function limit(int $limit, string $end = ''): self
    {
        $this->data = Transformer::limit($this->toString(), $limit, $end, $this->encoding);

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
        $this->data = Transformer::lower($this->toString(), $this->encoding);

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
        $this->data = Transformer::lowerFirst($this->toString(), $this->encoding);

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
        $this->data = Transformer::mask($this->toString(), $mask, $offset, $length, $this->encoding);

        return $this;
    }

    /**
     * Calculates the metaphone phonetic key of the string.
     *
     * @param int $maxPhonemes The maximum number of phonemes to return; 0 means no limit (default: 0)
     * @return self Returns the current instance for method chaining
     * @see Transformer::metaphone()
     */
    public function metaphone(int $maxPhonemes = 0): self
    {
        $this->data = Transformer::metaphone($this->toString(), $maxPhonemes);

        return $this;
    }

    /**
     * Inserts HTML line breaks before all newlines.
     *
     * @param bool $useXhtml Whether to use XHTML-compatible tags (default: true)
     * @return self Returns the current instance for method chaining
     * @see Transformer::nl2br()
     */
    public function nl2br(bool $useXhtml = true): self
    {
        $this->data = Transformer::nl2br($this->toString(), $useXhtml);

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
        $this->data = Transformer::normalizeNewLines($this->toString());

        return $this;
    }

    /**
     * Pads the string to a given length using a pad string.
     *
     * @param int $length The target total length in characters
     * @param string $padString The string to pad with (default: space)
     * @param \Phuture\Coherence\Enum\PadDirection $direction Which side to pad — Right, Left, or Both
     *  (default: PadDirection::Right)
     * @return self Returns the current instance for method chaining
     * @see Transformer::pad()
     * @see \Phuture\Coherence\Enum\PadDirection
     */
    public function pad(
        int $length,
        string $padString = ' ',
        PadDirection $direction = PadDirection::Right
    ): self {
        $this->data = Transformer::pad($this->toString(), $length, $padString, $direction, $this->encoding);

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
        $this->data = Transformer::pascal($this->toString());

        return $this;
    }

    /**
     * Escapes regular expression meta-characters.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::quoteMeta()
     */
    public function quoteMeta(): self
    {
        $this->data = Transformer::quoteMeta($this->toString());

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
        $this->data = Transformer::remove($this->toString(), $search, $caseSensitive);

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
        $this->data = Transformer::repeat($this->toString(), $times);

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
        $this->data = Transformer::replace($this->toString(), $search, $replace, $caseSensitive);

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
        $this->data = Transformer::replaceArray($this->toString(), $search, $replacements, $this->encoding);

        return $this;
    }

    /**
     * Replaces a portion of the string starting at a given character position.
     *
     * @param string $replacement The text to insert at the given position
     * @param int $position The character index at which to begin replacement (negative counts from end)
     * @param int|null $length The number of characters to replace (null replaces to end of string)
     * @return self Returns the current instance for method chaining
     * @see Transformer::replaceAt()
     */
    public function replaceAt(string $replacement, int $position, ?int $length = null): self
    {
        $this->data = Transformer::replaceAt($this->toString(), $replacement, $position, $length, $this->encoding);

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
        $this->data = Transformer::replaceFirst($this->toString(), $search, $replace, $this->encoding);

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
        $this->data = Transformer::replaceLast($this->toString(), $search, $replace, $this->encoding);

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
        $this->data = Transformer::reverse($this->toString());

        return $this;
    }

    /**
     * Applies the ROT13 encoding to the string.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::rot13()
     */
    public function rot13(): self
    {
        $this->data = Transformer::rot13($this->toString());

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
        $this->data = Transformer::scrub($this->toString());

        return $this;
    }

    /**
     * Returns the portion of the string from the first occurrence of a search value.
     *
     * @param string $search The value to search for
     * @param bool $beforeNeedle Return the part before the search value (default: false)
     * @param bool $caseSensitive Whether the search is case-sensitive (default: true)
     * @return self Returns the current instance for method chaining
     * @see Transformer::search()
     */
    public function search(
        string $search,
        bool $beforeNeedle = false,
        bool $caseSensitive = true
    ): self {
        $result = Transformer::search($this->toString(), $search, $beforeNeedle, $caseSensitive, $this->encoding);
        $this->data = $result === false ? '' : $result;

        return $this;
    }

    /**
     * Sets the character encoding used for all multibyte operations in the chain.
     *
     * Changes the encoding applied by every subsequent method call on this instance.
     * The default encoding is 'UTF-8'.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Type\Strings;
     *
     * $result = Strings::from('héllo')
     *     ->setEncoding('UTF-8')
     *     ->upper()
     *     ->get();
     * // Returns 'HÉLLO'
     * ```
     *
     * @param string $encoding The character encoding to use (e.g. 'UTF-8', 'ISO-8859-1')
     * @return self Returns the current instance for method chaining
     */
    public function setEncoding(string $encoding): self
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Randomly shuffles the characters in the string.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::shuffle()
     */
    public function shuffle(): self
    {
        $this->data = Transformer::shuffle($this->toString());

        return $this;
    }

    /**
     * Calculates the similarity percentage between the string and another string.
     *
     * @param string $other The string to compare against
     * @return self Returns the current instance for method chaining
     * @see Transformer::similar()
     */
    public function similar(string $other): self
    {
        $this->data = Transformer::similar($this->toString(), $other);

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
        $this->data = Transformer::slice($this->toString(), $start, $length, $this->encoding);

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
        $this->data = Transformer::slug($this->toString(), $separator, $language);

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
        $this->data = Transformer::snake($this->toString(), $delimiter);

        return $this;
    }

    /**
     * Calculates the soundex phonetic key of the string.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::soundex()
     */
    public function soundex(): self
    {
        $this->data = Transformer::soundex($this->toString());

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
        $this->data = Transformer::squish($this->toString());

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
        $this->data = Transformer::start($this->toString(), $prefix);

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
        $this->data = Transformer::strip($this->toString(), $allowedTags);

        return $this;
    }

    /**
     * Removes C-style backslash escapes.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::stripCSlashes()
     */
    public function stripCSlashes(): self
    {
        $this->data = Transformer::stripCSlashes($this->toString());

        return $this;
    }

    /**
     * Removes backslash escapes added by addSlashes.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::stripSlashes()
     */
    public function stripSlashes(): self
    {
        $this->data = Transformer::stripSlashes($this->toString());

        return $this;
    }

    /**
     * Performs multiple simultaneous search-and-replace operations.
     *
     * @param array $replacements An associative array where each key is the text to find
     *     and each value is the text to substitute
     * @return self Returns the current instance for method chaining
     * @see Transformer::swap()
     */
    public function swap(array $replacements): self
    {
        $this->data = Transformer::swap($this->toString(), $replacements);

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
        $this->data = Transformer::take($this->toString(), $count);

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
        $this->data = Transformer::title($this->toString(), $this->encoding);

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
        $this->data = Transformer::toBase64($this->toString());

        return $this;
    }

    /**
     * Converts the string to its hexadecimal representation.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::toHex()
     */
    public function toHex(): self
    {
        $this->data = Transformer::toHex($this->toString());

        return $this;
    }

    /**
     * Converts the wrapped value to a string.
     *
     * @return string The wrapped string value
     */
    #[Override]
    public function toString(): string
    {
        // @phpstan-ignore-next-line
        return (string) $this->data;
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
        $this->data = Transformer::trim($this->toString(), $characters);

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
        $this->data = Transformer::trimLeft($this->toString(), $characters);

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
        $this->data = Transformer::trimRight($this->toString(), $characters);

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
        $this->data = Transformer::unwrap($this->toString(), $wrapper, $this->encoding);

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
        $this->data = Transformer::upper($this->toString(), $this->encoding);

        return $this;
    }

    /**
     * Converts only the first character of the string to uppercase.
     *
     * @return self Returns the current instance for method chaining
     * @see Transformer::upperFirst()
     */
    public function upperFirst(): self
    {
        $this->data = Transformer::upperFirst($this->toString(), $this->encoding);

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
        $this->data = Transformer::wordWrap($this->toString(), $width, $break, $cutLongWords, $this->encoding);

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
        $this->data = Transformer::wrap($this->toString(), $wrapper);

        return $this;
    }
}
