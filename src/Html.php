<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use League\HTMLToMarkdown\HtmlConverter;
use Phuture\Coherence\Enum\EncodingMode;
use League\CommonMark\CommonMarkConverter;
use Phuture\Coherence\Support\StaticClass;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Phuture\Coherence\Exception\InvalidArgumentException;

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
 * - **Sanitization**: Remove scripts and unsafe markup from untrusted HTML
 * - **Text Extraction**: Strip HTML tags and decode entities back to readable text
 * - **HTML Conversion**: Convert plain text or Markdown to HTML
 * - **Markdown Conversion**: Convert HTML to Markdown
 * - **Encoding & Decoding**: Encode and decode HTML entities with mode selection via enum
 * - **Tag Generation**: Generate arbitrary HTML tags with attributes
 * - **Structured Building**: Generate complex HTML trees from multi-dimensional arrays
 * - **Inspection**: List the links, images and tag names a document contains
 * - **Size Reduction**: Shrink markup and remove comments without changing how it renders
 * - **Fluent Interface**: Chain operations with `of()` for readable transformations
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
     * Turns a list of attributes into a string you can drop inside a tag.
     *
     * Each key becomes the attribute name and each value becomes the attribute
     * value, escaped so that quotes and angle brackets cannot break out of the
     * tag. A value of `true` writes the name on its own, which is how HTML marks
     * on/off options such as `disabled`. A value of `false` or `null` leaves the
     * attribute out altogether.
     *
     * When there is at least one attribute the result begins with a space, so it
     * can be placed straight after a tag name without adding one yourself. Note
     * that attribute names are written out as given, so they must come from your
     * own code rather than from user input.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::attributes(['class' => 'btn', 'id' => 'save']);
     * // Returns: ' class="btn" id="save"'
     *
     * Html::attributes(['disabled' => true, 'hidden' => false]);
     * // Returns: ' disabled'
     *
     * Html::attributes([]);
     * // Returns: ''
     * ```
     *
     * @param array $attributes The attribute names and the values to give them
     * @return string The attributes as text beginning with a space, or an empty string when there are none
     * @see \Phuture\Coherence\Html::tag()
     * @see \Phuture\Coherence\Html::build()
     */
    public static function attributes(array $attributes): string
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
     * Void elements (such as `<br>`, `<img>`, `<input>`, `<hr>`, `<meta>`, etc.)
     * are rendered without a closing tag and without any content, regardless of
     * whether a `content` key is provided — it is silently ignored.
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
     *
     * Html::build([
     *     ['tag' => 'p', 'content' => 'Line one'],
     *     ['tag' => 'br'],
     *     ['tag' => 'img', 'attributes' => ['src' => 'photo.jpg', 'alt' => 'Photo']],
     * ]);
     * // '<p>Line one</p><br><img src="photo.jpg" alt="Photo">'
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

            if (in_array(strtolower($name), self::VOID_ELEMENTS, true)) {
                $html .= self::tag($name, '', $attributes);
                continue;
            }

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
     * Wraps text in an HTML comment.
     *
     * A comment is a note in the markup that the browser does not display. Any
     * run of two dashes inside the text is broken apart first, because `-->` and
     * `--!>` both end a comment early, which would let whatever follows run as
     * real markup.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::comment('Section starts here');
     * // Returns: '<!-- Section starts here -->'
     *
     * Html::comment('a --> b');
     * // Returns: '<!-- a - -> b -->'
     * ```
     *
     * @param string $content The text to place inside the comment
     * @return string The text wrapped in comment markers
     * @see \Phuture\Coherence\Html::stripComments()
     */
    public static function comment(string $content): string
    {
        return '<!-- ' . str_replace('--', '- -', $content) . ' -->';
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
     * Lists the addresses of every image in the HTML.
     *
     * The HTML is cleaned first, so anything hidden inside a comment or a script
     * is not reported, and addresses that cleaning rejects, such as
     * `javascript:` ones, are left out. Each value appears once, in the order it
     * first shows up.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::images('<img src="a.png"><p>x</p><img src="b.png">');
     * // Returns: ['a.png', 'b.png']
     *
     * Html::images('<p>No pictures here</p>');
     * // Returns: []
     * ```
     *
     * @param string $html The HTML to read the image addresses from
     * @return array The image addresses in the order they appear, without repeats
     * @see \Phuture\Coherence\Html::links()
     * @see \Phuture\Coherence\Html::sanitize()
     */
    public static function images(string $html): array
    {
        return self::extractAttributeValues($html, 'img', 'src');
    }

    /**
     * Checks whether HTML is already exactly what cleaning would produce.
     *
     * Returns true only when `sanitize()` would leave the HTML untouched. A
     * false result does **not** mean the HTML is dangerous: cleaning also tidies
     * harmless things, so `<p class="x">y</p>`, `<br>` and `Tom & Jerry` all
     * come back as false simply because cleaning would rewrite them. Use it to
     * tell whether stored HTML still matches what cleaning produces today, not
     * as a test for whether something is safe.
     *
     * The options are the same as `sanitize()` and must match the ones used to
     * clean the HTML in the first place, otherwise the answer is meaningless.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::isSanitized('<p>Hello</p>');
     * // Returns: true
     *
     * Html::isSanitized('<p onclick="steal()">Hello</p>');
     * // Returns: false
     *
     * Html::isSanitized('<p class="lead">Hello</p>');
     * // Returns: false, because cleaning removes the class
     * ```
     *
     * @param string $html The HTML to check
     * @param array $allowedTags The tag names to keep, or an empty array to keep every safe tag (default: [])
     * @param array $allowedSchemes The kinds of address allowed in links and images
     *   (default: ['http', 'https', 'mailto', 'tel'])
     * @param int $maxLength How many bytes of input to read, or -1 to read all of it (default: 20000)
     * @return bool True when cleaning would change nothing, false when it would change something
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the maximum length is below -1
     * @see \Phuture\Coherence\Html::sanitize()
     */
    public static function isSanitized(
        string $html,
        array $allowedTags = [],
        array $allowedSchemes = ['http', 'https', 'mailto', 'tel'],
        int $maxLength = 20000,
    ): bool {
        return self::sanitize($html, $allowedTags, $allowedSchemes, $maxLength) === $html;
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
     * Lists the addresses of every link in the HTML.
     *
     * The HTML is cleaned first, so anything hidden inside a comment or a script
     * is not reported, and addresses that cleaning rejects, such as
     * `javascript:` ones, are left out. Each value appears once, in the order it
     * first shows up.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::links('<a href="/about">About</a> and <a href="https://e.com">E</a>');
     * // Returns: ['/about', 'https://e.com']
     *
     * Html::links('<a href="javascript:alert(1)">Bad</a>');
     * // Returns: []
     * ```
     *
     * @param string $html The HTML to read the link addresses from
     * @return array The link addresses in the order they appear, without repeats
     * @see \Phuture\Coherence\Html::images()
     * @see \Phuture\Coherence\Html::sanitize()
     */
    public static function links(string $html): array
    {
        return self::extractAttributeValues($html, 'a', 'href');
    }

    /**
     * Makes HTML smaller without changing how it looks.
     *
     * Squeezes every run of spaces, tabs and newlines down to a single space and
     * drops comments. A browser already treats any run of spacing as one space,
     * so the page renders exactly as before — the spacing is never removed
     * outright, because deleting the space in `<b>a</b> <b>b</b>` would join the
     * two words together.
     *
     * Everything inside `<pre>`, `<textarea>`, `<script>` and `<style>` is left
     * exactly as it was, since spacing matters there. Spacing is still reduced
     * if your stylesheet makes some other element preserve it.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::minify("<div>\n    <p>Hello</p>\n</div>");
     * // Returns: '<div> <p>Hello</p> </div>'
     *
     * Html::minify('<p>a</p><!-- note --><p>b</p>');
     * // Returns: '<p>a</p><p>b</p>'
     * ```
     *
     * @param string $html The HTML to shrink
     * @return string The HTML with spacing reduced and comments removed
     * @see \Phuture\Coherence\Html::stripComments()
     */
    public static function minify(string $html): string
    {
        $parts = preg_split(
            '#(<(?:pre|textarea|script|style)\b.*?</(?:pre|textarea|script|style)>)#is',
            $html,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        if ($parts === false) {
            return $html;
        }

        $minified = '';

        foreach ($parts as $index => $part) {
            if ($index % 2 === 1) {
                $minified .= $part;
                continue;
            }

            $minified .= preg_replace('/\s+/', ' ', self::stripComments($part));
        }

        return trim($minified);
    }

    /**
     * Creates a fluent wrapper around the given HTML for method chaining.
     *
     * Returns a `Type\Html` instance that wraps the provided markup and exposes
     * the HTML-returning methods as chainable calls.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * $result = Html::of('<p onclick="steal()">Hello</p>')
     *     ->sanitize()
     *     ->toString();
     * // '<p>Hello</p>'
     * ```
     *
     * @param string $html The HTML to wrap for fluent operations
     * @return Type\Html A fluent wrapper instance that enables method chaining
     * @see \Phuture\Coherence\Type\Html For the fluent wrapper implementation
     */
    public static function of(string $html): Type\Html
    {
        return new Type\Html($html);
    }

    /**
     * Lists the tag names that cleaning treats as safe.
     *
     * These are the names `sanitize()` keeps when you do not narrow the list
     * yourself, useful for building a tag picker or explaining to someone why
     * their markup disappeared. The names are returned in alphabetical order.
     *
     * Four of them describe the head of a page rather than its content: `head`,
     * `link`, `meta` and `title`. Those never survive `sanitize()`, which treats
     * its input as page content, so treat this as the list of names the cleaner
     * recognises rather than a promise that each one survives.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * in_array('em', Html::safeTags(), true);
     * // Returns: true
     *
     * in_array('script', Html::safeTags(), true);
     * // Returns: false
     * ```
     *
     * @return array The safe tag names in alphabetical order
     * @see \Phuture\Coherence\Html::sanitize()
     */
    public static function safeTags(): array
    {
        $tags = array_map(
            'strval',
            array_keys((new HtmlSanitizerConfig())->allowSafeElements()->getAllowedElements())
        );

        sort($tags);

        return $tags;
    }

    /**
     * Removes dangerous code from HTML that came from an untrusted source.
     *
     * Takes HTML written by someone you do not trust, such as a comment or a
     * profile description, and returns a version that is safe to put on a page.
     * Scripts, click handlers like `onclick`, and links that try to run code are
     * removed. Broken or half-written HTML is repaired instead of rejected.
     *
     * By default every tag considered safe is kept, which is what you want for
     * text a user has formatted themselves. Attributes are filtered the same
     * way, and two common ones are not on the safe list: `class` and `style`
     * are always removed, because either can be used to cover the page with an
     * invisible clickable layer. Plan for styling by tag name, not by class.
     *
     * Pass a list of tag names in
     * `$allowedTags` to keep fewer: the other safe tags are removed but their
     * text stays, the same way `stripTags()` behaves. Tags that are never safe,
     * such as `script` and `style`, are always removed together with everything
     * inside them, even when you list them.
     *
     * A link or image keeps its address only when the address starts with one of
     * the `$allowedSchemes`, which is what stops `javascript:` links from working.
     * Addresses with no scheme, such as `/about`, are always kept, and the ones
     * that are kept may come back with characters like `@` written as `&#64;`,
     * which a browser displays the same way.
     *
     * Input longer than `$maxLength` bytes is cut before anything is read, so a
     * huge page cannot be used to slow the server down. Pass `-1` to read it all.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::sanitize('<p onclick="steal()">Hello <script>alert(1)</script>world</p>');
     * // Returns: '<p>Hello world</p>'
     *
     * Html::sanitize('<a href="javascript:alert(1)">Click</a>');
     * // Returns: '<a>Click</a>'
     *
     * Html::sanitize('<p>Keep <em>this</em> and <b>that</b></p>', ['em']);
     * // Returns: 'Keep <em>this</em> and that'
     * ```
     *
     * @param string $html The untrusted HTML to clean
     * @param array $allowedTags The tag names to keep, written without angle brackets,
     *   or an empty array to keep every safe tag (default: [])
     * @param array $allowedSchemes The kinds of address allowed in links and images
     *   (default: ['http', 'https', 'mailto', 'tel'])
     * @param int $maxLength How many bytes of input to read, or -1 to read all of it (default: 20000)
     * @return string The cleaned HTML, safe to display on a page
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the maximum length is below -1
     * @see \Phuture\Coherence\Html::stripTags()
     * @see \Phuture\Coherence\Html::encode()
     */
    public static function sanitize(
        string $html,
        array $allowedTags = [],
        array $allowedSchemes = ['http', 'https', 'mailto', 'tel'],
        int $maxLength = 20000,
    ): string {
        $config = self::buildSanitizerConfig($allowedTags, $allowedSchemes, $maxLength);

        if ($html === '') {
            return $html;
        }

        return (new HtmlSanitizer($config))->sanitize($html);
    }

    /**
     * Generates an HTML script element.
     *
     * Creates a `<script>` tag. When a source URL is provided, the script
     * references an external file. When null is passed as the source, inline
     * content can be provided instead. Only one of `src` or `content` should
     * be used at a time.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::script('app.js'); // '<script src="app.js"></script>'
     * Html::script('app.js', '', ['defer' => true]); // '<script src="app.js" defer></script>'
     * Html::script(null, 'alert("hi");'); // '<script>alert("hi");</script>'
     * ```
     *
     * @param string|null $src The script source URL, or null for inline scripts
     * @param string $content The inline script content (default: '')
     * @param array $attributes Additional HTML attributes (default: [])
     * @return string The generated `<script>` element
     * @see \Phuture\Coherence\Html::tag()
     * @see \Phuture\Coherence\Html::link()
     */
    public static function script(
        ?string $src,
        string $content = '',
        array $attributes = [],
    ): string {
        if ($src !== null) {
            $attributes['src'] = $src;
        }

        return self::tag('script', $content, $attributes);
    }

    /**
     * Cleans HTML and hardens every link it contains.
     *
     * This does everything `sanitize()` does — the whole document is cleaned, so
     * scripts go, and `class` and `style` are removed along with them — and then
     * adds a `rel` attribute to every link. The usual value, `noopener
     * noreferrer`, stops a page you link to from reaching back into the page
     * that opened it and hides where the visitor came from.
     *
     * Turning on `$forceHttps` rewrites `http://` addresses to `https://` in
     * links and images alike. Anchors without an address, such as in-page jump
     * targets, still receive the `rel` attribute.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::secureLinks('<a href="https://example.com">Visit</a>');
     * // Returns: '<a href="https://example.com" rel="noopener noreferrer">Visit</a>'
     *
     * Html::secureLinks('<a href="http://example.com">Visit</a>', 'nofollow', true);
     * // Returns: '<a href="https://example.com" rel="nofollow">Visit</a>'
     * ```
     *
     * @param string $html The untrusted HTML to clean and harden
     * @param string $rel The relationship value to put on every link, which tells the browser how
     *   the linked page relates to this one (default: 'noopener noreferrer')
     * @param bool $forceHttps Whether to rewrite insecure addresses to their secure form (default: false)
     * @return string The cleaned HTML with every link hardened
     * @see \Phuture\Coherence\Html::sanitize()
     */
    public static function secureLinks(
        string $html,
        string $rel = 'noopener noreferrer',
        bool $forceHttps = false,
    ): string {
        if ($html === '') {
            return $html;
        }

        $config = self::buildSanitizerConfig([], ['http', 'https', 'mailto', 'tel'], 20000)
            ->forceAttribute('a', 'rel', $rel);

        if ($forceHttps) {
            $config = $config->forceHttpsUrls();
        }

        return (new HtmlSanitizer($config))->sanitize($html);
    }

    /**
     * Removes HTML comments, leaving the rest of the markup alone.
     *
     * Comments are the notes between `<!--` and `-->` that a browser does not
     * display. Unlike `stripTags()` and `sanitize()`, which also drop comments
     * but change the surrounding markup too, this touches nothing else — useful
     * for trimming trusted templates you do not want otherwise rewritten.
     *
     * A `<!--` with no closing `-->` is left in place rather than swallowing the
     * rest of the document.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::stripComments('<p class="lead">Hello<!-- note --></p>');
     * // Returns: '<p class="lead">Hello</p>'
     *
     * Html::stripComments('<p>Kept<!-- unfinished');
     * // Returns: '<p>Kept<!-- unfinished'
     * ```
     *
     * @param string $html The HTML to remove comments from
     * @return string The HTML without its comments
     * @see \Phuture\Coherence\Html::comment()
     * @see \Phuture\Coherence\Html::minify()
     */
    public static function stripComments(string $html): string
    {
        return (string) preg_replace('/<!--.*?-->/s', '', $html);
    }

    /**
     * Removes HTML tags from a string while optionally preserving specific allowed tags.
     *
     * This method strips all HTML and PHP tags from the given string. You can provide
     * a list of tag names (without angle brackets) that should be kept in the result.
     * Any attributes on the allowed tags are preserved as-is.
     *
     * Note: HTML comments and PHP tags are always removed, even if not explicitly listed.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::stripTags('<p>Hello <b>world</b></p>');
     * // Returns: 'Hello world'
     *
     * Html::stripTags('<p>Hello <b>world</b></p>', ['b']);
     * // Returns: 'Hello <b>world</b>'
     *
     * Html::stripTags('<div><a href="#">Link</a> text</div>', ['a']);
     * // Returns: '<a href="#">Link</a> text'
     *
     * Html::stripTags('<p>Keep <em>this</em> <strong>bold</strong></p>', ['em', 'strong']);
     * // Returns: 'Keep <em>this</em> <strong>bold</strong>'
     * ```
     *
     * @param string $html The HTML string to strip tags from
     * @param array $allowedTags A list of tag names to keep (without angle brackets, default: [])
     * @return string The string with HTML tags removed, keeping only the allowed tags
     * @see \Phuture\Coherence\Html::toText()
     */
    public static function stripTags(string $html, array $allowedTags = []): string
    {
        $allowedTags = array_map(fn (string $tag) => '<' . $tag . '>', $allowedTags);

        return strip_tags($html, $allowedTags);
    }

    /**
     * Generates an HTML table from a two-dimensional array.
     *
     * Converts a list of rows (each row being an array of cell values) into a
     * complete HTML `<table>` element. If column headers are provided, they are
     * rendered as `<th>` cells inside a `<thead>` section; all data rows are
     * wrapped in a `<tbody>`. Cell values are cast to string before output.
     *
     * Every inner array must have the same number of elements. If a column headers
     * array is given, it should also match that length — extra or missing headers
     * are not validated and will produce misaligned columns.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * $rows = [
     *     ['Alice', 30, 'Engineer'],
     *     ['Bob', 25, 'Designer'],
     * ];
     *
     * Html::table($rows);
     * // '<table><tbody><tr><td>Alice</td><td>30</td><td>Engineer</td></tr>
     * //  <tr><td>Bob</td><td>25</td><td>Designer</td></tr></tbody></table>'
     *
     * Html::table($rows, ['Name', 'Age', 'Role'], ['class' => 'data-table']);
     * // '<table class="data-table"><thead><tr><th>Name</th><th>Age</th><th>Role</th></tr></thead>
     * //  <tbody><tr><td>Alice</td>...</td></tr></tbody></table>'
     * ```
     *
     * @param array $rows A list of rows, where each row is an array of cell values
     * @param array $headers Optional column header labels rendered as `<th>` cells in a `<thead>` (default: [])
     * @param array $attributes Optional HTML attributes applied to the `<table>` element (default: [])
     * @return string The generated HTML table string
     * @see \Phuture\Coherence\Html::tag()
     * @see \Phuture\Coherence\Html::build()
     */
    public static function table(array $rows, array $headers = [], array $attributes = []): string
    {
        $thead = '';

        if ($headers !== []) {
            $cells = '';
            foreach ($headers as $header) {
                $cells .= self::tag('th', (string) $header);
            }
            $thead = self::tag('thead', self::tag('tr', $cells));
        }

        $tbody = '';

        foreach ($rows as $row) {
            $cells = '';
            foreach ($row as $cell) {
                $cells .= self::tag('td', (string) $cell);
            }
            $tbody .= self::tag('tr', $cells);
        }

        return self::tag('table', $thead . self::tag('tbody', $tbody), $attributes);
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
        $attributeString = self::attributes($attributes);

        if (in_array($name, self::VOID_ELEMENTS, true)) {
            return '<' . $name . $attributeString . '>';
        }

        return '<' . $name . $attributeString . '>' . $content . '</' . $name . '>';
    }

    /**
     * Lists the tag names used in the HTML.
     *
     * The HTML is cleaned first, so anything hidden inside a comment or a script
     * is not reported, and addresses that cleaning rejects, such as
     * `javascript:` ones, are left out. Each value appears once, in the order it
     * first shows up.
     * Opening and closing tags of the same element count once, and the names
     * come back in lower case.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::tags('<div><p>Hi</p><p>There</p></div>');
     * // Returns: ['div', 'p']
     *
     * Html::tags('Just text');
     * // Returns: []
     * ```
     *
     * @param string $html The HTML to read the tag names from
     * @return array The tag names in the order they appear, without repeats
     * @see \Phuture\Coherence\Html::safeTags()
     * @see \Phuture\Coherence\Html::sanitize()
     */
    public static function tags(string $html): array
    {
        $clean = self::sanitize($html, [], ['http', 'https', 'mailto', 'tel'], -1);

        preg_match_all('/<([a-z][a-z0-9]*)\b/i', $clean, $matches);

        return array_values(array_unique(array_map('strtolower', $matches[1])));
    }

    /**
     * Converts plain text or Markdown to HTML.
     *
     * When `$isMarkdown` is `true`, the content is converted using the CommonMark parser.
     * When `$isMarkdown` is `false`, the content is treated as plain text. When `$isMarkdown`
     * is `null` (the default), Markdown is detected automatically by looking for common
     * Markdown syntax patterns such as headings (#), emphasis (*, _), links ([text](url)),
     * images (![alt](src)), lists (-, *), code blocks (```), and blockquotes (>).
     *
     * Plain text is HTML-encoded and newlines are converted to `<br>` tags.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::toHtml('Hello & goodbye'); // 'Hello &amp; goodbye'
     * Html::toHtml("Line 1\nLine 2"); // 'Line 1<br>\nLine 2'
     * Html::toHtml('# Heading'); // '<h1>Heading</h1>'
     * Html::toHtml('**bold**'); // '<p><strong>bold</strong></p>'
     * Html::toHtml('# Not Markdown', false); // '# Not Markdown'
     * Html::toHtml('**bold**', true); // '<p><strong>bold</strong></p>'
     * ```
     *
     * @param string $content The plain text or Markdown content to convert
     * @param bool|null $isMarkdown Force Markdown (true), force plain text (false), or auto-detect (null, default)
     * @return string The resulting HTML string
     * @see \Phuture\Coherence\Html::toText()
     * @see \Phuture\Coherence\Html::toMarkdown()
     */
    public static function toHtml(string $content, ?bool $isMarkdown = null): string
    {
        if ($isMarkdown ?? self::isMarkdown($content)) {
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
     * Truncates an HTML string to a given number of visible characters.
     *
     * Counts only the characters that a reader can see — HTML tags and their
     * angle brackets do not count toward the limit. HTML entities like `&amp;`
     * or `&eacute;` each count as one visible character, because they display
     * as a single character in the browser.
     *
     * When the text is cut short, any HTML tags that were left open are
     * automatically closed so the returned string is always valid HTML. The
     * suffix is appended after all closing tags.
     *
     * Returns the original string unchanged when the visible text is already
     * within the limit. The suffix is only appended when truncation actually occurs.
     *
     * Note: HTML comments containing a `>` character inside them may not be
     * handled correctly.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::truncate('<p>Hello <b>world</b></p>', 7);
     * // Returns: '<p>Hello <b>w...</b></p>'
     *
     * Html::truncate('<p>Hello</p>', 10);
     * // Returns: '<p>Hello</p>'
     *
     * Html::truncate('<p>Tom &amp; Jerry</p>', 5);
     * // Returns: '<p>Tom &amp;...</p>'
     * ```
     *
     * @param string $html The HTML string to truncate
     * @param int $limit The maximum number of visible characters to keep
     * @param string $end The string to append when truncation occurs (default: '...')
     * @return string The truncated HTML string with all open tags properly closed
     * @see \Phuture\Coherence\Html::truncateWords()
     * @see \Phuture\Coherence\Html::toText()
     */
    public static function truncate(string $html, int $limit, string $end = '...'): string
    {
        if ($html === '') {
            return $html;
        }

        $visibleText = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (mb_strlen($visibleText, 'UTF-8') <= $limit) {
            return $html;
        }

        $openTags = [];
        $result = '';
        $count = 0;

        preg_match_all(
            '/(<[^>]+>)|(&(?:[a-zA-Z][a-zA-Z0-9]*|#(?:x[0-9a-fA-F]+|[0-9]+));)|(.)/su',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $token) {
            $segment = $token[0];

            if (str_starts_with($segment, '<')) {
                $result .= $segment;
                self::updateOpenTagStack($segment, $openTags);
            } else {
                $result .= $segment;
                $count++;

                if ($count >= $limit) {
                    break;
                }
            }
        }

        if ($count >= $limit) {
            return $result . $end . self::closeOpenTags($openTags);
        }

        return $result;
    }

    /**
     * Truncates an HTML string to a given number of visible words.
     *
     * Counts only the words that a reader can see — HTML tags do not count
     * toward the limit. A word is any sequence of non-whitespace characters.
     * HTML entities like `&amp;` are treated as word characters.
     *
     * When the text is cut short, any HTML tags that were left open are
     * automatically closed so the returned string is always valid HTML. The
     * suffix is appended after all closing tags.
     *
     * Returns the original string unchanged when the visible word count is
     * already within the limit. The suffix is only appended when truncation
     * actually occurs.
     *
     * Note: HTML comments containing a `>` character inside them may not be
     * handled correctly.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Html;
     *
     * Html::truncateWords('<p>One Two Three Four</p>', 2);
     * // Returns: '<p>One Two...</p>'
     *
     * Html::truncateWords('<p>Hello World</p>', 5);
     * // Returns: '<p>Hello World</p>'
     * ```
     *
     * @param string $html The HTML string to truncate
     * @param int $limit The maximum number of visible words to keep
     * @param string $end The string to append when truncation occurs (default: '...')
     * @return string The truncated HTML string with all open tags properly closed
     * @see \Phuture\Coherence\Html::truncate()
     * @see \Phuture\Coherence\Html::toText()
     */
    public static function truncateWords(string $html, int $limit, string $end = '...'): string
    {
        if ($html === '') {
            return $html;
        }

        if (str_word_count(strip_tags($html)) <= $limit) {
            return $html;
        }

        $openTags = [];
        $result = '';
        $count = 0;
        $inWord = false;

        preg_match_all(
            '/(<[^>]+>)|(&(?:[a-zA-Z][a-zA-Z0-9]*|#(?:x[0-9a-fA-F]+|[0-9]+));)|(.)/su',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $token) {
            $segment = $token[0];

            if (str_starts_with($segment, '<')) {
                $result .= $segment;
                self::updateOpenTagStack($segment, $openTags);
            } else {
                $isSpace = trim($segment) === '';

                if ($inWord && $isSpace) {
                    $inWord = false;
                    $count++;

                    if ($count >= $limit) {
                        break;
                    }
                } elseif (!$inWord && !$isSpace) {
                    $inWord = true;
                }

                $result .= $segment;
            }
        }

        if ($count >= $limit) {
            return $result . $end . self::closeOpenTags($openTags);
        }

        return $result;
    }

    /**
     * Builds the sanitizer configuration shared by every cleaning method.
     *
     * @param array $allowedTags The tag names to keep, or an empty array for every safe tag
     * @param array $allowedSchemes The kinds of address allowed in links and images
     * @param int $maxLength The maximum input length in bytes, or -1 for no limit
     * @return HtmlSanitizerConfig The configuration to hand to the sanitizer
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the maximum length is below -1
     */
    private static function buildSanitizerConfig(
        array $allowedTags,
        array $allowedSchemes,
        int $maxLength,
    ): HtmlSanitizerConfig {
        if ($maxLength < -1) {
            throw new InvalidArgumentException(
                'Invalid Argument: Maximum length must be -1 or greater'
            );
        }

        $config = (new HtmlSanitizerConfig())
            ->allowSafeElements()
            ->allowLinkSchemes($allowedSchemes)
            ->allowMediaSchemes($allowedSchemes)
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->withMaxInputLength($maxLength);

        if ($allowedTags === []) {
            return $config;
        }

        $allowedTags = array_map(
            static fn (string $tag): string => strtolower(trim($tag, '<>')),
            $allowedTags
        );

        foreach (array_keys($config->getAllowedElements()) as $element) {
            if (!in_array((string) $element, $allowedTags, true)) {
                $config = $config->blockElement((string) $element);
            }
        }

        return $config;
    }

    /**
     * Serialises the open-tag stack as a sequence of closing tags.
     *
     * @param array $openTags The stack of unclosed tag names (innermost last)
     * @return string A string of closing tags in reverse order, or empty string when none
     */
    private static function closeOpenTags(array $openTags): string
    {
        $closing = '';

        foreach (array_reverse($openTags) as $tag) {
            $closing .= '</' . $tag . '>';
        }

        return $closing;
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
     * Collects one attribute's values from every matching tag in cleaned HTML.
     *
     * @param string $html The HTML to read
     * @param string $tag The tag name to look for
     * @param string $attribute The attribute whose value to collect
     * @return array The attribute values in document order, without repeats
     */
    private static function extractAttributeValues(string $html, string $tag, string $attribute): array
    {
        $clean = self::sanitize($html, [], ['http', 'https', 'mailto', 'tel'], -1);
        $pattern = '/<' . $tag . '\b[^>]*\b' . $attribute . '="([^"]*)"/i';

        preg_match_all($pattern, $clean, $matches);

        $values = array_map(
            static fn (string $value): string => self::decode($value),
            $matches[1]
        );

        return array_values(array_unique($values));
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
     * Updates the open-tag stack based on a parsed HTML tag token.
     *
     * @param string $tag The raw HTML tag string (e.g. '<p>', '</p>', '<br />')
     * @param array &$openTags The open-tag stack, passed by reference
     */
    private static function updateOpenTagStack(string $tag, array &$openTags): void
    {
        preg_match('/<\/?([a-zA-Z][a-zA-Z0-9]*)/i', $tag, $nameMatch);
        $tagName = strtolower($nameMatch[1] ?? '');

        if ($tagName === '') {
            return;
        }

        $isClosing = str_starts_with(ltrim($tag), '</');
        $isSelfClosing = str_ends_with(rtrim($tag), '/>') || in_array($tagName, self::VOID_ELEMENTS, true);

        if ($isClosing) {
            for ($i = count($openTags) - 1; $i >= 0; $i--) {
                if ($openTags[$i] === $tagName) {
                    array_splice($openTags, $i, 1);
                    break;
                }
            }
        } elseif (!$isSelfClosing) {
            $openTags[] = $tagName;
        }
    }
}
