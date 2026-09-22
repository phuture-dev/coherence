<?php

declare(strict_types=1);

namespace Phuture\Coherence\Type;

use Override;
use Phuture\Coherence\Enum\EncodingMode;
use Phuture\Coherence\Interface\Htmlable;
use Phuture\Coherence\Html as Transformer;
use Phuture\Coherence\Support\FluentClass;

/**
 * A fluent wrapper around the Html utility class for chainable HTML manipulation.
 *
 * Each method delegates to the corresponding static method on `\Phuture\Coherence\Html`,
 * stores the result internally, and returns `$this` to enable method chaining. Retrieve
 * the final value by calling `get()`, `toString()`, or invoking the object directly.
 *
 * The wrapped value is always HTML. To build HTML from plain text or Markdown first,
 * use the static `\Phuture\Coherence\Html::toHtml()` and wrap the result, since on this
 * class `toHtml()` hands back the markup being held rather than converting anything.
 *
 * Example:
 * ```php
 * use Phuture\Coherence\Html;
 *
 * $result = Html::of('<p onclick="steal()">Hello <script>alert(1)</script>world</p>')
 *     ->sanitize()
 *     ->truncate(8)
 *     ->toString();
 * // '<p>Hello wo...</p>'
 *
 * $text = Html::of('<p>Hello <b>world</b></p>')->toText();
 * // 'Hello world'
 * ```
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Html extends FluentClass implements Htmlable
{
    /**
     * Returns the string representation of the wrapped HTML.
     *
     * @return string The wrapped HTML markup
     */
    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Decodes HTML entities in the wrapped value back to their characters.
     *
     * @param EncodingMode $mode The decoding mode (default: EncodingMode::All)
     * @param int $flags The bitmask of entity conversion flags (default: ENT_QUOTES)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Html::decode()
     */
    public function decode(
        EncodingMode $mode = EncodingMode::All,
        int $flags = ENT_QUOTES,
        string $encoding = 'UTF-8',
    ): self {
        $this->data = Transformer::decode($this->toString(), $mode, $flags, $encoding);

        return $this;
    }

    /**
     * Encodes characters in the wrapped value as HTML entities.
     *
     * @param EncodingMode $mode The encoding mode (default: EncodingMode::All)
     * @param int $flags The bitmask of entity conversion flags (default: ENT_QUOTES)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Html::encode()
     */
    public function encode(
        EncodingMode $mode = EncodingMode::All,
        int $flags = ENT_QUOTES,
        string $encoding = 'UTF-8',
    ): self {
        $this->data = Transformer::encode($this->toString(), $mode, $flags, $encoding);

        return $this;
    }

    /**
     * Lists the addresses of every image in the wrapped HTML.
     *
     * @return array The image addresses in the order they appear, without repeats
     * @see \Phuture\Coherence\Html::images()
     */
    public function images(): array
    {
        return Transformer::images($this->toString());
    }

    /**
     * Checks whether the wrapped HTML is already exactly what cleaning would produce.
     *
     * @param array $allowedTags The tag names to keep, or an empty array to keep every safe tag (default: [])
     * @param array $allowedSchemes The kinds of address allowed in links and images
     *   (default: ['http', 'https', 'mailto', 'tel'])
     * @param int $maxLength How many bytes of input to read, or -1 to read all of it (default: 20000)
     * @return bool True when cleaning would change nothing, false when it would change something
     * @see \Phuture\Coherence\Html::isSanitized()
     */
    public function isSanitized(
        array $allowedTags = [],
        array $allowedSchemes = ['http', 'https', 'mailto', 'tel'],
        int $maxLength = 20000,
    ): bool {
        return Transformer::isSanitized($this->toString(), $allowedTags, $allowedSchemes, $maxLength);
    }

    /**
     * Lists the addresses of every link in the wrapped HTML.
     *
     * @return array The link addresses in the order they appear, without repeats
     * @see \Phuture\Coherence\Html::links()
     */
    public function links(): array
    {
        return Transformer::links($this->toString());
    }

    /**
     * Makes the wrapped HTML smaller without changing how it looks.
     *
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Html::minify()
     */
    public function minify(): self
    {
        $this->data = Transformer::minify($this->toString());

        return $this;
    }

    /**
     * Removes dangerous code from the wrapped HTML.
     *
     * @param array $allowedTags The tag names to keep, or an empty array to keep every safe tag (default: [])
     * @param array $allowedSchemes The kinds of address allowed in links and images
     *   (default: ['http', 'https', 'mailto', 'tel'])
     * @param int $maxLength How many bytes of input to read, or -1 to read all of it (default: 20000)
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Html::sanitize()
     */
    public function sanitize(
        array $allowedTags = [],
        array $allowedSchemes = ['http', 'https', 'mailto', 'tel'],
        int $maxLength = 20000,
    ): self {
        $this->data = Transformer::sanitize($this->toString(), $allowedTags, $allowedSchemes, $maxLength);

        return $this;
    }

    /**
     * Cleans the wrapped HTML and hardens every link it contains.
     *
     * @param string $rel The relationship value to put on every link (default: 'noopener noreferrer')
     * @param bool $forceHttps Whether to rewrite insecure addresses to their secure form (default: false)
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Html::secureLinks()
     */
    public function secureLinks(string $rel = 'noopener noreferrer', bool $forceHttps = false): self
    {
        $this->data = Transformer::secureLinks($this->toString(), $rel, $forceHttps);

        return $this;
    }

    /**
     * Removes comments from the wrapped HTML, leaving the rest of the markup alone.
     *
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Html::stripComments()
     */
    public function stripComments(): self
    {
        $this->data = Transformer::stripComments($this->toString());

        return $this;
    }

    /**
     * Removes tags from the wrapped HTML, optionally keeping some of them.
     *
     * @param array $allowedTags A list of tag names to keep (without angle brackets, default: [])
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Html::stripTags()
     */
    public function stripTags(array $allowedTags = []): self
    {
        $this->data = Transformer::stripTags($this->toString(), $allowedTags);

        return $this;
    }

    /**
     * Lists the tag names used in the wrapped HTML.
     *
     * @return array The tag names in the order they appear, without repeats
     * @see \Phuture\Coherence\Html::tags()
     */
    public function tags(): array
    {
        return Transformer::tags($this->toString());
    }

    /**
     * Returns the wrapped HTML markup.
     *
     * This method hands back the markup itself, unchanged. It returns the same
     * value as `toString()`; both exist so that code reading HTML can say so by
     * name, while anything expecting a plain string still works.
     *
     * @return string The wrapped HTML markup
     * @see \Phuture\Coherence\Type\Html::toString()
     */
    #[Override]
    public function toHtml(): string
    {
        return $this->toString();
    }

    /**
     * Returns the wrapped HTML converted to Markdown.
     *
     * @return string The content written as Markdown
     * @see \Phuture\Coherence\Html::toMarkdown()
     */
    #[Override]
    public function toMarkdown(): string
    {
        return Transformer::toMarkdown($this->toString());
    }

    /**
     * Returns the wrapped HTML as a string.
     *
     * @return string The wrapped HTML markup
     */
    #[Override]
    public function toString(): string
    {
        // @phpstan-ignore-next-line
        return (string) $this->data;
    }

    /**
     * Returns just the readable text of the wrapped HTML, with the markup removed.
     *
     * @return string The readable text without any markup
     * @see \Phuture\Coherence\Html::toText()
     */
    #[Override]
    public function toText(): string
    {
        return Transformer::toText($this->toString());
    }

    /**
     * Shortens the wrapped HTML to a number of visible characters.
     *
     * @param int $limit The maximum number of visible characters to keep
     * @param string $end The string to append when shortening occurs (default: '...')
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Html::truncate()
     */
    public function truncate(int $limit, string $end = '...'): self
    {
        $this->data = Transformer::truncate($this->toString(), $limit, $end);

        return $this;
    }

    /**
     * Shortens the wrapped HTML to a number of visible words.
     *
     * @param int $limit The maximum number of visible words to keep
     * @param string $end The string to append when shortening occurs (default: '...')
     * @return self Returns the current instance for method chaining
     * @see \Phuture\Coherence\Html::truncateWords()
     */
    public function truncateWords(int $limit, string $end = '...'): self
    {
        $this->data = Transformer::truncateWords($this->toString(), $limit, $end);

        return $this;
    }
}
