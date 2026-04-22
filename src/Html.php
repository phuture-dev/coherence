<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use League\CommonMark\CommonMarkConverter;
use League\HTMLToMarkdown\HtmlConverter;
use Phuture\Coherence\Exception\InvalidArgumentException;
use Phuture\Coherence\Support\StaticClass;

/**
 * Comprehensive HTML manipulation utility class.
 *
 * This utility class provides a complete toolkit for HTML generation, conversion,
 * encoding, decoding, and text extraction. It supports conversions between HTML,
 * plain text, and Markdown, as well as HTML tag generation inspired by CodeIgniter's
 * HTML helper.
 *
 * Key features:
 *
 * - **Text Extraction**: Strip HTML tags and decode entities back to readable text
 * - **HTML Conversion**: Convert plain text or Markdown to HTML
 * - **Markdown Conversion**: Convert HTML to Markdown
 * - **Encoding & Decoding**: Encode and decode HTML entities with optional special-character-only mode
 * - **Tag Generation**: Generate arbitrary HTML tags with attributes
 * - **Media Tags**: Generate img, video, audio, source, embed, object, param, track elements
 * - **Document Tags**: Generate doctype declarations, link, and script elements
 * - **List Generation**: Generate ordered and unordered lists from arrays
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Html extends StaticClass
{
    public const ENCODE_ALL = 0;

    public const ENCODE_SPECIAL_CHARS = 1;

    private const VOID_ELEMENTS = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
        'link', 'meta', 'param', 'source', 'track', 'wbr',
    ];

    private const DOCTYPES = [
        'html5' => '<!DOCTYPE html>',
        'xhtml11' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"'
            . ' "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
        'xhtml1-strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"'
            . ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
        'xhtml1-trans' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'
            . ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
        'xhtml1-frame' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"'
            . ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
        'xhtml-basic11' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN"'
            . ' "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">',
        'html4-strict' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"'
            . ' "http://www.w3.org/TR/html4/strict.dtd">',
        'html4-trans' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"'
            . ' "http://www.w3.org/TR/html4/loose.dtd">',
        'html4-frame' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"'
            . ' "http://www.w3.org/TR/html4/frameset.dtd">',
        'mathml1' => '<!DOCTYPE math SYSTEM "http://www.w3.org/Math/DTD/mathml1/mathml.dtd">',
        'mathml2' => '<!DOCTYPE math PUBLIC "-//W3C//DTD MathML 2.0//EN"'
            . ' "http://www.w3.org/Math/DTD/mathml2/mathml2.dtd">',
        'svg10' => '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN"'
            . ' "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">',
        'svg11' => '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"'
            . ' "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">',
        'svg11-basic' => '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Basic//EN"'
            . ' "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-basic.dtd">',
        'svg11-tiny' => '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Tiny//EN"'
            . ' "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11-tiny.dtd">',
        'xhtml-math-svg-xh' => '<!DOCTYPE html PUBLIC'
            . ' "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN"'
            . ' "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">',
        'xhtml-math-svg-sh' => '<!DOCTYPE svg:svg PUBLIC'
            . ' "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN"'
            . ' "http://www.w3.org/2002/04/xhtml-math-svg/xhtml-math-svg.dtd">',
        'xhtml-rdfa-1' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN"'
            . ' "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">',
        'xhtml-rdfa-2' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.1//EN"'
            . ' "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-2.dtd">',
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
     * HTML entities are encoded and newlines are converted to `<br>` tags.
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
     * Html::toHtml("Line 1\nLine 2"); // 'Line 1<br>Line 2'
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

        $html = self::encode($content, self::ENCODE_SPECIAL_CHARS);
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
     * Encodes special characters and HTML entities in a string.
     *
     * By default, this method encodes all characters that have HTML entity equivalents
     * using `htmlentities()`. When the `$mode` parameter is set to
     * `Html::ENCODE_SPECIAL_CHARS`, only the characters that have special meaning in
     * HTML (`&`, `"`, `'`, `<`, `>`) are encoded using `htmlspecialchars()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::encode('Tom & Jerry'); // 'Tom &amp; Jerry'
     * Html::encode('<p>Hello</p>'); // '&lt;p&gt;Hello&lt;/p&gt;'
     * Html::encode('Tom & Jerry', Html::ENCODE_SPECIAL_CHARS); // 'Tom &amp; Jerry'
     * Html::encode('café', Html::ENCODE_ALL); // 'caf&eacute;'
     * ```
     *
     * @param string $string The string to encode
     * @param int $mode The encoding mode: `Html::ENCODE_ALL` (default) or `Html::ENCODE_SPECIAL_CHARS`
     * @param int $flags The bitmask of entity conversion flags (default: ENT_QUOTES)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The encoded string with HTML entities or special characters replaced
     * @see \Phuture\Coherence\Html::decode()
     */
    public static function encode(
        string $string,
        int $mode = self::ENCODE_ALL,
        int $flags = ENT_QUOTES,
        string $encoding = 'UTF-8',
    ): string {
        if ($mode === self::ENCODE_SPECIAL_CHARS) {
            return htmlspecialchars($string, $flags, $encoding);
        }

        return htmlentities($string, $flags, $encoding);
    }

    /**
     * Decodes HTML entities and special characters back to their original form.
     *
     * By default, this method decodes all HTML entities using `html_entity_decode()`.
     * When the `$mode` parameter is set to `Html::ENCODE_SPECIAL_CHARS`, only the
     * special HTML characters (`&`, `"`, `'`, `<`, `>`) are decoded using
     * `htmlspecialchars_decode()`.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::decode('Tom &amp; Jerry'); // 'Tom & Jerry'
     * Html::decode('&lt;p&gt;Hello&lt;/p&gt;'); // '<p>Hello</p>'
     * Html::decode('Tom &amp; Jerry', Html::ENCODE_SPECIAL_CHARS); // 'Tom & Jerry'
     * Html::decode('caf&eacute;', Html::ENCODE_ALL); // 'café'
     * ```
     *
     * @param string $string The string to decode
     * @param int $mode The decoding mode: `Html::ENCODE_ALL` (default) or `Html::ENCODE_SPECIAL_CHARS`
     * @param int $flags The bitmask of entity conversion flags (default: ENT_QUOTES)
     * @param string $encoding The character encoding to use (default: 'UTF-8')
     * @return string The decoded string with entities or special characters restored
     * @see \Phuture\Coherence\Html::encode()
     */
    public static function decode(
        string $string,
        int $mode = self::ENCODE_ALL,
        int $flags = ENT_QUOTES,
        string $encoding = 'UTF-8',
    ): string {
        if ($mode === self::ENCODE_SPECIAL_CHARS) {
            return htmlspecialchars_decode($string, $flags);
        }

        return html_entity_decode($string, $flags, $encoding);
    }

    /**
     * Generates an HTML tag with attributes.
     *
     * Creates an opening and closing tag pair with the given content and attributes.
     * Void elements (like `<img>`, `<br>`, `<hr>`) are rendered as self-closing tags
     * and the content parameter is ignored. Boolean attributes (where the value is
     * `true`) are rendered as just the attribute name, and attributes with a `false`
     * or `null` value are omitted entirely.
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
     * @see \Phuture\Coherence\Html::img()
     * @see \Phuture\Coherence\Html::linkTag()
     * @see \Phuture\Coherence\Html::scriptTag()
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
     * Generates an HTML image element.
     *
     * Creates an `<img>` tag from either a source URL string or an associative
     * array of attributes. When using the array form, you have full control over
     * all attributes. If no `alt` attribute is provided, an empty one is added.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::img('photo.jpg'); // '<img src="photo.jpg" alt="">'
     * Html::img('photo.jpg', ['alt' => 'A photo', 'class' => 'thumbnail']);
     * Html::img(['src' => 'photo.jpg', 'width' => '200', 'height' => '150']);
     * ```
     *
     * @param string|array $src The image source URL, or an associative array of attributes
     * @param array $attributes Additional HTML attributes (used only when $src is a string)
     * @return string The generated `<img>` element
     * @see \Phuture\Coherence\Html::tag()
     * @see \Phuture\Coherence\Html::imgData()
     */
    public static function img(string|array $src, array $attributes = []): string
    {
        if (is_array($src)) {
            $attributes = $src;
        } else {
            $attributes = array_merge(['src' => $src], $attributes);
        }

        if (!isset($attributes['alt'])) {
            $attributes['alt'] = '';
        }

        return self::tag('img', '', $attributes);
    }

    /**
     * Generates a base64-encoded image source string using the data protocol.
     *
     * Reads the image file at the given path and encodes it as a base64 data URI
     * suitable for use as an `<img>` src attribute. The MIME type can be explicitly
     * provided or guessed from the file extension.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * $dataUri = Html::imgData('/path/to/image.png');
     * Html::img($dataUri);
     *
     * Html::imgData('/path/to/image', 'image/png');
     * ```
     *
     * @param string $path The file system path to the image
     * @param string|null $mime The MIME type, or null to guess from the extension (default: null)
     * @return string The base64 data URI string (e.g., 'data:image/png;base64,...')
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the file does not exist or is not readable
     * @see \Phuture\Coherence\Html::img()
     */
    public static function imgData(string $path, ?string $mime = null): string
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new InvalidArgumentException(
                'Invalid Argument: File does not exist or is not readable: ' . $path
            );
        }

        if ($mime === null) {
            $mime = self::guessMimeType($path);
        }

        $data = base64_encode(file_get_contents($path));

        return 'data:' . $mime . ';base64,' . $data;
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
     * Html::linkTag('styles.css');
     * // '<link href="styles.css" rel="stylesheet" type="text/css">'
     *
     * Html::linkTag('favicon.ico', 'shortcut icon', 'image/ico');
     * // '<link href="favicon.ico" rel="shortcut icon" type="image/ico">'
     *
     * Html::linkTag(['href' => 'print.css', 'rel' => 'stylesheet', 'media' => 'print']);
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
     * @see \Phuture\Coherence\Html::scriptTag()
     */
    public static function linkTag(
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
     * Creates a `<script>` tag from either a source URL string or an associative
     * array of attributes. When using the array form, you have full control over
     * all attributes.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::scriptTag('app.js'); // '<script src="app.js"></script>'
     * Html::scriptTag(['src' => 'app.js', 'defer' => true]);
     * Html::scriptTag(['src' => 'analytics.js', 'async' => true]);
     * ```
     *
     * @param string|array $src The script source URL, or an associative array of attributes
     * @return string The generated `<script>` element
     * @see \Phuture\Coherence\Html::tag()
     * @see \Phuture\Coherence\Html::linkTag()
     */
    public static function scriptTag(string|array $src): string
    {
        if (is_array($src)) {
            $attributes = $src;
        } else {
            $attributes = ['src' => $src];
        }

        return self::tag('script', '', $attributes);
    }

    /**
     * Generates an HTML unordered list.
     *
     * Creates a `<ul>` element from a simple or multi-dimensional array.
     * Numeric keys produce list items with just the value. String keys produce
     * list items where the key becomes the label and the array value becomes a
     * nested list. Attributes are applied to the outermost `<ul>` element.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::ul(['red', 'blue', 'green']);
     * // '<ul><li>red</li><li>blue</li><li>green</li></ul>'
     *
     * Html::ul(['colors' => ['red', 'blue']], ['class' => 'list']);
     * ```
     *
     * @param array $items The list items, which may be nested for multi-level lists
     * @param array $attributes HTML attributes for the outer `<ul>` element (default: [])
     * @return string The generated `<ul>` element
     * @see \Phuture\Coherence\Html::ol()
     * @see \Phuture\Coherence\Html::tag()
     */
    public static function ul(array $items, array $attributes = []): string
    {
        return self::buildList('ul', $items, $attributes);
    }

    /**
     * Generates an HTML ordered list.
     *
     * Identical to `ul()` but produces an `<ol>` element instead of `<ul>`.
     * Supports the same simple and multi-dimensional array structure for
     * nested lists.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::ol(['first', 'second', 'third']);
     * // '<ol><li>first</li><li>second</li><li>third</li></ol>'
     *
     * Html::ol(['steps' => ['one', 'two']], ['type' => '1']);
     * ```
     *
     * @param array $items The list items, which may be nested for multi-level lists
     * @param array $attributes HTML attributes for the outer `<ol>` element (default: [])
     * @return string The generated `<ol>` element
     * @see \Phuture\Coherence\Html::ul()
     * @see \Phuture\Coherence\Html::tag()
     */
    public static function ol(array $items, array $attributes = []): string
    {
        return self::buildList('ol', $items, $attributes);
    }

    /**
     * Generates an HTML video element.
     *
     * Creates a `<video>` tag from a source string or an array of `source()`
     * results. Supports fallback text for unsupported browsers, optional track
     * elements for subtitles, and arbitrary HTML attributes.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::video('movie.mp4', 'Your browser does not support video.', ['controls' => true]);
     *
     * Html::video(
     *     [Html::source('movie.mp4', 'video/mp4'), Html::source('movie.ogg', 'video/ogg')],
     *     'Unsupported browser.',
     *     ['controls' => true],
     *     [Html::track('subs.vtt', 'subtitles', 'en', 'English')]
     * );
     * ```
     *
     * @param string|array $src A single source URL, or an array of source elements
     * @param string $fallbackMessage Text shown when the browser does not support video (default: '')
     * @param array $attributes HTML attributes for the video element (default: [])
     * @param array $tracks An array of track elements generated by `track()` (default: [])
     * @return string The generated `<video>` element
     * @see \Phuture\Coherence\Html::audio()
     * @see \Phuture\Coherence\Html::source()
     * @see \Phuture\Coherence\Html::track()
     */
    public static function video(
        string|array $src,
        string $fallbackMessage = '',
        array $attributes = [],
        array $tracks = [],
    ): string {
        return self::buildMedia('video', $src, $fallbackMessage, $attributes, $tracks);
    }

    /**
     * Generates an HTML audio element.
     *
     * Identical to `video()` but produces an `<audio>` element instead of `<video>`.
     * Supports the same source, fallback, attribute, and track configuration.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::audio('song.mp3', 'Your browser does not support audio.', ['controls' => true]);
     *
     * Html::audio(
     *     [Html::source('song.mp3', 'audio/mpeg'), Html::source('song.ogg', 'audio/ogg')],
     *     'Unsupported browser.',
     *     ['controls' => true]
     * );
     * ```
     *
     * @param string|array $src A single source URL, or an array of source elements
     * @param string $fallbackMessage Text shown when the browser does not support audio (default: '')
     * @param array $attributes HTML attributes for the audio element (default: [])
     * @param array $tracks An array of track elements generated by `track()` (default: [])
     * @return string The generated `<audio>` element
     * @see \Phuture\Coherence\Html::video()
     * @see \Phuture\Coherence\Html::source()
     * @see \Phuture\Coherence\Html::track()
     */
    public static function audio(
        string|array $src,
        string $fallbackMessage = '',
        array $attributes = [],
        array $tracks = [],
    ): string {
        return self::buildMedia('audio', $src, $fallbackMessage, $attributes, $tracks);
    }

    /**
     * Generates an HTML source element for media tags.
     *
     * Creates a `<source>` element used inside `<video>` or `<audio>` elements
     * to specify alternative media resources.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::source('movie.mp4', 'video/mp4');
     * // '<source src="movie.mp4" type="video/mp4">'
     *
     * Html::source('movie.mp4', 'video/mp4', ['class' => 'primary']);
     * // '<source src="movie.mp4" type="video/mp4" class="primary">'
     * ```
     *
     * @param string $src The URL of the media resource
     * @param string $type The MIME type of the resource (default: 'unknown')
     * @param array $attributes Additional HTML attributes (default: [])
     * @return string The generated `<source>` element
     * @see \Phuture\Coherence\Html::video()
     * @see \Phuture\Coherence\Html::audio()
     */
    public static function source(string $src, string $type = 'unknown', array $attributes = []): string
    {
        $attributes['src'] = $src;
        $attributes['type'] = $type;

        return self::tag('source', '', $attributes);
    }

    /**
     * Generates an HTML embed element.
     *
     * Creates an `<embed>` element for embedding external content such as
     * multimedia or interactive plugins.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::embed('movie.swf', 'application/x-shockwave-flash');
     * // '<embed src="movie.swf" type="application/x-shockwave-flash">'
     *
     * Html::embed('animation.swf', 'application/x-shockwave-flash', ['class' => 'player']);
     * ```
     *
     * @param string $src The URL of the embedded resource
     * @param string|false $type The MIME type, or false to omit (default: false)
     * @param array $attributes Additional HTML attributes (default: [])
     * @return string The generated `<embed>` element
     * @see \Phuture\Coherence\Html::object()
     */
    public static function embed(string $src, string|false $type = false, array $attributes = []): string
    {
        $attributes['src'] = $src;

        if ($type !== false) {
            $attributes['type'] = $type;
        }

        return self::tag('embed', '', $attributes);
    }

    /**
     * Generates an HTML object element.
     *
     * Creates an `<object>` element for embedding external resources such as
     * multimedia, images, or plugins. Optional `<param>` children can be
     * included for configuration.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::object('movie.swf', 'application/x-shockwave-flash');
     * // '<object data="movie.swf" type="application/x-shockwave-flash"></object>'
     *
     * Html::object(
     *     'movie.swf',
     *     'application/x-shockwave-flash',
     *     ['class' => 'player'],
     *     [Html::param('autoplay', 'true')]
     * );
     * ```
     *
     * @param string $data The URL of the resource to embed
     * @param string $type The MIME type of the resource (default: 'unknown')
     * @param array $attributes Additional HTML attributes (default: [])
     * @param array $params An array of param elements generated by `param()` (default: [])
     * @return string The generated `<object>` element
     * @see \Phuture\Coherence\Html::embed()
     * @see \Phuture\Coherence\Html::param()
     */
    public static function object(
        string $data,
        string $type = 'unknown',
        array $attributes = [],
        array $params = [],
    ): string {
        $attributes['data'] = $data;
        $attributes['type'] = $type;

        $content = implode('', $params);

        return self::tag('object', $content, $attributes);
    }

    /**
     * Generates an HTML param element for use inside object elements.
     *
     * Creates a `<param>` element that defines configuration parameters for
     * an `<object>` element.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::param('autoplay', 'true');
     * // '<param name="autoplay" value="true">'
     *
     * Html::param('quality', 'high', ['class' => 'config']);
     * // '<param name="quality" value="high" class="config">'
     * ```
     *
     * @param string $name The parameter name
     * @param string $value The parameter value
     * @param array $attributes Additional HTML attributes (default: [])
     * @return string The generated `<param>` element
     * @see \Phuture\Coherence\Html::object()
     */
    public static function param(string $name, string $value, array $attributes = []): string
    {
        $attributes['name'] = $name;
        $attributes['value'] = $value;

        return self::tag('param', '', $attributes);
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
     * @see \Phuture\Coherence\Html::video()
     * @see \Phuture\Coherence\Html::audio()
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
     * Generates an HTML document type declaration.
     *
     * Returns the DOCTYPE string for the specified document type. HTML5 is
     * used by default. Supports XHTML, HTML4, MathML, SVG, and RDFa doctypes.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::doctype(); // '<!DOCTYPE html>'
     * Html::doctype('html4-strict');
     * Html::doctype('xhtml1-trans');
     * ```
     *
     * @param string $type The document type identifier (default: 'html5')
     * @return string The DOCTYPE declaration string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When an unsupported document type is requested
     */
    public static function doctype(string $type = 'html5'): string
    {
        if (!isset(self::DOCTYPES[$type])) {
            throw new InvalidArgumentException(
                'Invalid Argument: Unsupported doctype: ' . $type
            );
        }

        return self::DOCTYPES[$type];
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

            $html .= ' ' . $key . '="' . self::encode((string) $value, self::ENCODE_SPECIAL_CHARS) . '"';
        }

        return $html;
    }

    /**
     * Builds an HTML list (ordered or unordered) from an array.
     *
     * Numeric keys produce simple list items. String keys produce items
     * where the key is the label text and the value array becomes a nested list.
     *
     * @param string $tag The list tag name ('ul' or 'ol')
     * @param array $items The list items
     * @param array $attributes HTML attributes for the list element
     * @return string The generated list HTML
     */
    private static function buildList(string $tag, array $items, array $attributes): string
    {
        $content = '';

        foreach ($items as $key => $value) {
            if (is_array($value)) {
                $content .= '<li>' . self::encode((string) $key, self::ENCODE_SPECIAL_CHARS)
                    . self::buildList($tag, $value, [])
                    . '</li>';
            } else {
                $content .= '<li>' . self::encode((string) $value, self::ENCODE_SPECIAL_CHARS) . '</li>';
            }
        }

        return self::tag($tag, $content, $attributes);
    }

    /**
     * Builds an HTML media element (video or audio).
     *
     * @param string $tag The media tag name ('video' or 'audio')
     * @param string|array $src A single source URL or an array of source elements
     * @param string $fallbackMessage Text for unsupported browsers
     * @param array $attributes HTML attributes for the media element
     * @param array $tracks Track elements for subtitles
     * @return string The generated media element HTML
     */
    private static function buildMedia(
        string $tag,
        string|array $src,
        string $fallbackMessage,
        array $attributes,
        array $tracks,
    ): string {
        $content = '';

        if (is_array($src)) {
            $content .= implode('', $src);
        } else {
            $attributes['src'] = $src;
        }

        $content .= implode('', $tracks);

        if ($fallbackMessage !== '') {
            $content .= self::encode($fallbackMessage, self::ENCODE_SPECIAL_CHARS);
        }

        return self::tag($tag, $content, $attributes);
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

    /**
     * Guesses the MIME type of a file from its extension.
     *
     * @param string $path The file path to inspect
     * @return string The guessed MIME type, defaults to 'application/octet-stream'
     */
    private static function guessMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'ico' => 'image/x-icon',
            'tiff', 'tif' => 'image/tiff',
            'avif' => 'image/avif',
            default => 'application/octet-stream',
        };
    }
}
