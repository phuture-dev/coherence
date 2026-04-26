<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use League\CommonMark\CommonMarkConverter;
use League\HTMLToMarkdown\HtmlConverter;
use Phuture\Coherence\Enum\EncodingMode;
use Phuture\Coherence\Exception\InvalidArgumentException;
use Phuture\Coherence\Support\StaticClass;

/**
 * Comprehensive HTML manipulation utility class.
 *
 * This utility class provides a complete toolkit for HTML generation, conversion,
 * encoding, decoding, and text extraction. It supports conversions between HTML,
 * plain text, and Markdown, as well as HTML tag generation from individual tags
 * or structured arrays.
 *
 * Key features:
 *
 * - **Text Extraction**: Strip HTML tags and decode entities back to readable text
 * - **HTML Conversion**: Convert plain text or Markdown to HTML
 * - **Markdown Conversion**: Convert HTML to Markdown
 * - **Encoding & Decoding**: Encode and decode HTML entities with mode selection via enum
 * - **Tag Generation**: Generate arbitrary HTML tags with attributes
 * - **Structured Building**: Generate complex HTML trees from multi-dimensional arrays
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Html extends StaticClass
{
    /**
     * HTML void elements that cannot have closing tags.
     *
     * These elements are rendered without a closing tag (e.g., `<br>` instead of
     * `<br></br>`). The content parameter is ignored for void elements.
     */
    private const VOID_ELEMENTS = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
        'link', 'meta', 'param', 'source', 'track', 'wbr',
    ];

    /**
     * Converts HTML to plain text.
     *
     * Removes all HTML tags and returns readable text. The `<br>` tag is
     * converted to a newline character before stripping, and all HTML entities
     * are decoded. If the content between block-level tags appears on separate
     * lines, the spacing is preserved for readability.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::toText('<p>Hello <b>world</b></p>'); // 'Hello world'
     * Html::toText('Line 1<br>Line 2'); // "Line 1\nLine 2"
     * Html::toText('Tom &amp; Jerry'); // 'Tom & Jerry'
     * ```
     *
     * @param string $html The HTML string to convert to plain text
     * @return string The plain text with all tags removed and entities decoded
     * @see \Phuture\Coherence\Html::toHtml()
     * @see \Phuture\Coherence\Html::decode()
     */
    public static function toText(string $html): string
    {
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $blockTags = 'p|div|h[1-6]|li|tr|blockquote|pre|section'
            . '|article|header|footer|aside|main|nav|address'
            . '|figcaption|figure|details|summary';
        $html = preg_replace(
            '/<\/?(' . $blockTags . ')[^>]*>/i',
            "\n",
            $html
        );

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    /**
     * Converts plain text or Markdown to HTML.
     *
     * When the input is detected as Markdown (contains Markdown syntax patterns),
     * it is converted using the CommonMark parser. When the input is plain text,
     * HTML special characters are encoded and newlines are converted to `<br>` tags.
     *
     * Markdown detection looks for common Markdown patterns such as headings (#),
     * emphasis (*, _), links ([text](url)), images (![alt](src)), lists (-, *),
     * code blocks (```), and blockquotes (>).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::toHtml('Hello & goodbye'); // 'Hello &amp; goodbye'
     * Html::toHtml("Line 1\nLine 2"); // 'Line 1<br>\nLine 2'
     * Html::toHtml('# Heading'); // '<h1>Heading</h1>'
     * Html::toHtml('**bold**'); // '<p><strong>bold</strong></p>'
     * ```
     *
     * @param string $content The plain text or Markdown content to convert
     * @return string The resulting HTML string
     * @see \Phuture\Coherence\Html::toText()
     * @see \Phuture\Coherence\Html::toMarkdown()
     */
    public static function toHtml(string $content): string
    {
        if (self::isMarkdown($content)) {
            return self::convertMarkdownToHtml($content);
        }

        $html = self::encode($content, EncodingMode::SpecialChars);
        $html = nl2br($html, false);

        return $html;
    }

    /**
     * Converts HTML to Markdown.
     *
     * Uses the league/html-to-markdown library to convert the given HTML string
     * into its Markdown representation. This is useful for generating Markdown
     * from rich HTML content.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::toMarkdown('<h1>Heading</h1>'); // '# Heading'
     * Html::toMarkdown('<p><strong>bold</strong></p>'); // '**bold**'
     * Html::toMarkdown('<a href="https://example.com">Link</a>'); // '[Link](https://example.com)'
     * ```
     *
     * @param string $html The HTML string to convert to Markdown
     * @return string The Markdown representation of the HTML
     * @see \Phuture\Coherence\Html::toHtml()
     */
    public static function toMarkdown(string $html): string
    {
        $converter = new HtmlConverter();
        $converter->getConfig()->setOption('header_style', 'atx');
        $converter->getConfig()->setOption('strip_tags', true);
        $converter->getConfig()->setOption('remove_nodes', '');

        return trim($converter->convert($html));
    }

    /**
     * Encodes characters in a string to their HTML entity equivalents.
     *
     * The encoding behaviour is controlled by the `$mode` parameter using the
     * `\Phuture\Coherence\Enum\EncodingMode` enum. When set to `All`, every character
     * that has an HTML entity equivalent is converted using `htmlentities()`. When set
     * to `SpecialChars`, only the five characters with special meaning in HTML
     * (`&`, `"`, `'`, `<`, `>`) are encoded using `htmlspecialchars()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     * use Phuture\Coherence\Enum\EncodingMode;
     *
     * Html::encode('Tom & Jerry'); // 'Tom &amp; Jerry'
     * Html::encode('<p>Hello</p>'); // '&lt;p&gt;Hello&lt;/p&gt;'
     * Html::encode('Tom & Jerry', EncodingMode::SpecialChars); // 'Tom &amp; Jerry'
     * Html::encode('café', EncodingMode::All); // 'caf&eacute;'
     * ```
     *
     * @param string $string The string to encode
     * @param \Phuture\Coherence\Enum\EncodingMode $mode The encoding mode (default: EncodingMode::All)
     * @param int $flags The bitmask of entity conversion flags (default: ENT_QUOTES)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The encoded string with characters replaced by HTML entities
     * @see \Phuture\Coherence\Html::decode()
     * @see \Phuture\Coherence\Enum\EncodingMode
     */
    public static function encode(
        string $string,
        EncodingMode $mode = EncodingMode::All,
        int $flags = ENT_QUOTES,
        string $encoding = 'UTF-8',
    ): string {
        if ($mode === EncodingMode::SpecialChars) {
            return htmlspecialchars($string, $flags, $encoding);
        }

        return htmlentities($string, $flags, $encoding);
    }

    /**
     * Decodes HTML entities back to their original characters.
     *
     * The decoding behaviour is controlled by the `$mode` parameter using the
     * `\Phuture\Coherence\Enum\EncodingMode` enum. When set to `All`, all HTML entities
     * are decoded using `html_entity_decode()`. When set to `SpecialChars`, only the
     * special HTML character entities (`&amp;`, `&quot;`, `&#039;`, `&lt;`, `&gt;`)
     * are decoded using `htmlspecialchars_decode()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     * use Phuture\Coherence\Enum\EncodingMode;
     *
     * Html::decode('Tom &amp; Jerry'); // 'Tom & Jerry'
     * Html::decode('&lt;p&gt;Hello&lt;/p&gt;'); // '<p>Hello</p>'
     * Html::decode('Tom &amp; Jerry', EncodingMode::SpecialChars); // 'Tom & Jerry'
     * Html::decode('caf&eacute;', EncodingMode::All); // 'café'
     * ```
     *
     * @param string $string The string to decode
     * @param \Phuture\Coherence\Enum\EncodingMode $mode The decoding mode (default: EncodingMode::All)
     * @param int $flags The bitmask of entity conversion flags (default: ENT_QUOTES)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The decoded string with HTML entities restored to characters
     * @see \Phuture\Coherence\Html::encode()
     * @see \Phuture\Coherence\Enum\EncodingMode
     */
    public static function decode(
        string $string,
        EncodingMode $mode = EncodingMode::All,
        int $flags = ENT_QUOTES,
        string $encoding = 'UTF-8',
    ): string {
        if ($mode === EncodingMode::SpecialChars) {
            return htmlspecialchars_decode($string, $flags);
        }

        return html_entity_decode($string, $flags, $encoding);
    }

    /**
     * Generates an HTML tag with attributes.
     *
     * Creates an opening and closing tag pair with the given content and attributes.
     * Void elements (like `<img>`, `<br>`, `<hr>`) are rendered without a closing
     * tag and the content parameter is ignored. Boolean attributes (where the value
     * is `true`) are rendered as just the attribute name, and attributes with a
     * `false` or `null` value are omitted entirely.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::tag('p', 'Hello'); // '<p>Hello</p>'
     * Html::tag('p', 'Hello', ['class' => 'greeting']); // '<p class="greeting">Hello</p>'
     * Html::tag('br'); // '<br>'
     * Html::tag('input', '', ['type' => 'text', 'required' => true]); // '<input type="text" required>'
     * ```
     *
     * @param string $name The tag name (e.g., 'p', 'div', 'img')
     * @param string $content The inner content of the tag (default: '')
     * @param array $attributes Associative array of HTML attributes (default: [])
     * @return string The generated HTML tag
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the tag name is empty
     * @see \Phuture\Coherence\Html::build()
     * @see \Phuture\Coherence\Html::link()
     * @see \Phuture\Coherence\Html::script()
     */
    public static function tag(string $name, string $content = '', array $attributes = []): string
    {
        if ($name === '') {
            throw new InvalidArgumentException(
                'Invalid Argument: Tag name cannot be empty'
            );
        }

        $name = strtolower($name);
        $attributeString = self::buildAttributes($attributes);

        if (in_array($name, self::VOID_ELEMENTS, true)) {
            return '<' . $name . $attributeString . '>';
        }

        return '<' . $name . $attributeString . '>' . $content . '</' . $name . '>';
    }

    /**
     * Generates an HTML link element.
     *
     * Creates a `<link>` tag, typically used for stylesheets, favicons, and other
     * resource links. When using the array form, you have full control over all
     * attributes. When using the string form, sensible defaults are applied for
     * stylesheets.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::link('styles.css');
     * // '<link href="styles.css" rel="stylesheet" type="text/css">'
     *
     * Html::link('favicon.ico', 'shortcut icon', 'image/ico');
     * // '<link href="favicon.ico" rel="shortcut icon" type="image/ico">'
     *
     * Html::link(['href' => 'print.css', 'rel' => 'stylesheet', 'media' => 'print']);
     * ```
     *
     * @param string|array $href The URL of the linked resource, or an associative array of attributes
     * @param string $rel The relationship type (default: 'stylesheet')
     * @param string $type The content type (default: 'text/css')
     * @param string $title The title of the link (default: '')
     * @param string $media The media type the link applies to (default: '')
     * @param string $hreflang The language of the linked resource (default: '')
     * @return string The generated `<link>` element
     * @see \Phuture\Coherence\Html::tag()
     * @see \Phuture\Coherence\Html::script()
     */
    public static function link(
        string|array $href,
        string $rel = 'stylesheet',
        string $type = 'text/css',
        string $title = '',
        string $media = '',
        string $hreflang = '',
    ): string {
        if (is_array($href)) {
            return self::tag('link', '', $href);
        }

        $attributes = ['href' => $href, 'rel' => $rel, 'type' => $type];

        if ($title !== '') {
            $attributes['title'] = $title;
        }

        if ($media !== '') {
            $attributes['media'] = $media;
        }

        if ($hreflang !== '') {
            $attributes['hreflang'] = $hreflang;
        }

        return self::tag('link', '', $attributes);
    }

    /**
     * Generates an HTML script element.
     *
     * Creates a `<script>` tag. Accepts either a source URL, inline content,
     * or an associative array of attributes. When a source URL is provided,
     * the script references an external file. When content is provided, it
     * is used as inline script body. Only one of `src` or `content` should
     * be provided, not both.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::script('app.js'); // '<script src="app.js"></script>'
     * Html::script(['src' => 'app.js', 'defer' => true]);
     * Html::script(content: 'alert("hi");'); // '<script>alert("hi");</script>'
     * ```
     *
     * @param string|null $src The script source URL (default: null)
     * @param string $content The inline script content (default: '')
     * @param array $attributes Additional HTML attributes (default: [])
     * @return string The generated `<script>` element
     * @see \Phuture\Coherence\Html::tag()
     * @see \Phuture\Coherence\Html::link()
     */
    public static function script(
        ?string $src = null,
        string $content = '',
        array $attributes = [],
    ): string {
        if (is_array($src)) {
            $attributes = $src;
            $src = null;
        }

        if ($src !== null) {
            $attributes['src'] = $src;
        }

        return self::tag('script', $content, $attributes);
    }

    /**
     * Generates an HTML track element for media elements.
     *
     * Creates a `<track>` element used inside `<video>` or `<audio>` elements
     * to specify timed text tracks such as subtitles or captions.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::track('subtitles.vtt', 'subtitles', 'en', 'English');
     * // '<track src="subtitles.vtt" kind="subtitles" srclang="en" label="English">'
     * ```
     *
     * @param string $src The URL of the track file (WebVTT format)
     * @param string $kind The kind of timed track (e.g., 'subtitles', 'captions')
     * @param string $srcLanguage The language code of the track
     * @param string $label A human-readable title for the track
     * @return string The generated `<track>` element
     * @see \Phuture\Coherence\Html::tag()
     */
    public static function track(string $src, string $kind, string $srcLanguage, string $label): string
    {
        return self::tag('track', '', [
            'src' => $src,
            'kind' => $kind,
            'srclang' => $srcLanguage,
            'label' => $label,
        ]);
    }

    /**
     * Builds HTML from a multi-dimensional array structure.
     *
     * Recursively generates HTML elements from an array of tag definitions.
     * Each element in the array must contain a `tag` key with the tag name,
     * and may optionally contain `attributes` (an associative array) and
     * `content` (either a string or another tag definition array for nesting).
     *
     * When `content` is an array matching the tag definition format, it is
     * recursively processed. When `content` is a plain array of tag definitions,
     * each element is processed and concatenated.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::build([
     *     ['tag' => 'div', 'attributes' => ['class' => 'container'], 'content' => [
     *         ['tag' => 'h1', 'content' => 'Title'],
     *         ['tag' => 'p', 'content' => 'Paragraph text'],
     *     ]],
     * ]);
     * // '<div class="container"><h1>Title</h1><p>Paragraph text</p></div>'
     *
     * Html::build([
     *     ['tag' => 'ul', 'content' => [
     *         ['tag' => 'li', 'content' => 'Item 1'],
     *         ['tag' => 'li', 'content' => 'Item 2'],
     *     ]],
     * ]);
     * // '<ul><li>Item 1</li><li>Item 2</li></ul>'
     * ```
     *
     * @param array $elements An array of tag definition arrays to build
     * @return string The concatenated HTML string for all elements
     * @see \Phuture\Coherence\Html::tag()
     */
    public static function build(array $elements): string
    {
        $html = '';

        foreach ($elements as $element) {
            $name = $element['tag'] ?? '';
            $attributes = $element['attributes'] ?? [];
            $content = $element['content'] ?? '';

            if (is_array($content) && isset($content['tag'])) {
                $content = self::build([$content]);
            } elseif (is_array($content)) {
                $content = self::build($content);
            }

            $html .= self::tag($name, (string) $content, $attributes);
        }

        return $html;
    }

    /**
     * Builds the HTML attribute string from an associative array.
     *
     * Converts an associative array of attribute names and values into a
     * properly escaped HTML attribute string. Boolean `true` values render
     * as standalone attributes, `false` and `null` values are omitted.
     *
     * @param array $attributes The attributes to format
     * @return string A space-prefixed attribute string, or an empty string when no attributes remain
     */
    private static function buildAttributes(array $attributes): string
    {
        $html = '';

        foreach ($attributes as $key => $value) {
            if ($value === false || $value === null) {
                continue;
            }

            if ($value === true) {
                $html .= ' ' . $key;
                continue;
            }

            $html .= ' ' . $key . '="' . self::encode((string) $value, EncodingMode::SpecialChars) . '"';
        }

        return $html;
    }

    /**
     * Determines whether the given content appears to be Markdown.
     *
     * Checks for common Markdown syntax patterns that would not typically
     * appear in plain text. Returns false when no Markdown patterns are found,
     * indicating the content should be treated as plain text.
     *
     * @param string $content The content to inspect
     * @return bool True when the content appears to contain Markdown syntax
     */
    private static function isMarkdown(string $content): bool
    {
        $patterns = [
            '/^#{1,6}\s/m',
            '/\*\*[^*]+\*\*/',
            '/\*[^*]+\*/',
            '/\[[^\]]+\]\([^)]+\)/',
            '/!\[[^\]]*\]\([^)]+\)/',
            '/^\s*[-*+]\s/m',
            '/^\s*\d+\.\s/m',
            '/^```/m',
            '/^\s*>/m',
            '/`[^`]+`/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Converts Markdown content to HTML using the CommonMark parser.
     *
     * @param string $markdown The Markdown string to convert
     * @return string The resulting HTML
     */
    private static function convertMarkdownToHtml(string $markdown): string
    {
        $converter = new CommonMarkConverter();

        return trim((string) $converter->convert($markdown));
    }
}
