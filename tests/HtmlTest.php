<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Phuture\Coherence\Html;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Type\Html as FluentHtml;

require __DIR__ . '/bootstrap.php';

class HtmlTest extends TestCase
{
    public function testAttributes(): void
    {
        Assert::same(' class="btn" id="save"', Html::attributes(['class' => 'btn', 'id' => 'save']));
        Assert::same(' disabled', Html::attributes(['disabled' => true]));
        Assert::same('', Html::attributes(['hidden' => false, 'extra' => null]));
        Assert::same('', Html::attributes([]));
    }

    public function testAttributesEscapesValues(): void
    {
        Assert::same(' title="a &amp; b"', Html::attributes(['title' => 'a & b']));
        Assert::same(' title="say &quot;hi&quot;"', Html::attributes(['title' => 'say "hi"']));
        Assert::notContains('<script>', Html::attributes(['title' => '<script>']));
    }

    public function testAttributesMatchesTagOutput(): void
    {
        // tag() is built on attributes(), so the two must stay in step
        Assert::same(
            '<p' . Html::attributes(['class' => 'greeting']) . '>Hello</p>',
            Html::tag('p', 'Hello', ['class' => 'greeting'])
        );
    }

    public function testBuildEmptyArray(): void
    {
        Assert::same('', Html::build([]));
    }

    public function testBuildMultipleElements(): void
    {
        $result = Html::build([
            ['tag' => 'h1', 'content' => 'Title'],
            ['tag' => 'p', 'content' => 'Paragraph'],
        ]);

        Assert::same('<h1>Title</h1><p>Paragraph</p>', $result);
    }

    public function testBuildNestedContent(): void
    {
        $result = Html::build([
            ['tag' => 'div', 'content' => [
                ['tag' => 'span', 'content' => 'Inner'],
            ]],
        ]);

        Assert::same('<div><span>Inner</span></div>', $result);
    }

    public function testBuildSingleElement(): void
    {
        $result = Html::build([
            ['tag' => 'p', 'content' => 'Hello'],
        ]);

        Assert::same('<p>Hello</p>', $result);
    }

    public function testBuildVoidElement(): void
    {
        // Standalone void element
        Assert::same('<br>', Html::build([['tag' => 'br']]));

        // Void element with attributes
        Assert::same(
            '<img src="photo.jpg" alt="Photo">',
            Html::build([['tag' => 'img', 'attributes' => ['src' => 'photo.jpg', 'alt' => 'Photo']]])
        );

        // Content key on a void element is silently ignored
        Assert::same('<br>', Html::build([['tag' => 'br', 'content' => 'ignored']]));

        // Mixed void and non-void siblings
        Assert::same(
            '<p>Text</p><br><p>More</p>',
            Html::build([
                ['tag' => 'p', 'content' => 'Text'],
                ['tag' => 'br'],
                ['tag' => 'p', 'content' => 'More'],
            ])
        );
    }

    public function testBuildWithAttributes(): void
    {
        $result = Html::build([
            ['tag' => 'a', 'attributes' => ['href' => 'https://example.com'], 'content' => 'Link'],
        ]);

        Assert::same('<a href="https://example.com">Link</a>', $result);
    }

    public function testComment(): void
    {
        Assert::same('<!-- Section starts here -->', Html::comment('Section starts here'));
        Assert::same('<!--  -->', Html::comment(''));
    }

    public function testCommentCannotBeEscaped(): void
    {
        // A naive implementation would let the script out of the comment
        $result = Html::comment('x --> <script>alert(1)</script> <!-- y');

        Assert::notContains('-->', substr($result, 5, -4));
        Assert::same('', Html::sanitize($result));
    }

    public function testCommentRoundTripsWithStripComments(): void
    {
        Assert::same('', Html::stripComments(Html::comment('note')));
    }

    public function testDecodeAll(): void
    {
        Assert::same('Tom & Jerry', Html::decode('Tom &amp; Jerry'));
        Assert::same('<p>Hello</p>', Html::decode('&lt;p&gt;Hello&lt;/p&gt;'));
    }

    public function testDecodeAllWithAccents(): void
    {
        Assert::same('café', Html::decode('caf&eacute;'));
    }

    public function testDecodeEmpty(): void
    {
        Assert::same('', Html::decode(''));
    }

    public function testEncodeAll(): void
    {
        Assert::same('Tom &amp; Jerry', Html::encode('Tom & Jerry'));
        Assert::same('&lt;p&gt;Hello&lt;/p&gt;', Html::encode('<p>Hello</p>'));
    }

    public function testEncodeAllWithAccents(): void
    {
        Assert::same('caf&eacute;', Html::encode('café'));
    }

    public function testEncodeDecodeRoundTrip(): void
    {
        $original = '<p>Hello & "world"</p>';
        Assert::same($original, Html::decode(Html::encode($original)));
    }

    public function testEncodeEmpty(): void
    {
        Assert::same('', Html::encode(''));
    }

    public function testEncodeQuotes(): void
    {
        Assert::same('a &quot;b&quot; c', Html::encode('a "b" c'));
        Assert::same("a &#039;b&#039; c", Html::encode("a 'b' c"));
    }

    public function testImages(): void
    {
        Assert::same(
            ['a.png', 'b.png'],
            Html::images('<img src="a.png"><p>x</p><img src="b.png">')
        );

        // Repeats collapse and the result is a plain list
        Assert::same(['a.png'], Html::images('<img src="a.png"><img src="a.png">'));
        Assert::same([], Html::images('<p>No pictures here</p>'));
    }

    public function testImagesIgnoresHiddenMarkup(): void
    {
        Assert::same([], Html::images('<!-- <img src="hidden.png"> -->'));
        Assert::same(['ok.png'], Html::images('<!-- <img src="no.png"> --><img src="ok.png">'));
    }

    public function testIsSanitized(): void
    {
        Assert::true(Html::isSanitized('<p>Hello</p>'));
        Assert::false(Html::isSanitized('<p onclick="steal()">Hello</p>'));
        Assert::true(Html::isSanitized(''));
    }

    public function testIsSanitizedAgreesWithSanitize(): void
    {
        $html = '<p onclick="x()">Hello</p>';

        Assert::true(Html::isSanitized(Html::sanitize($html)));
    }

    public function testIsSanitizedIsFalseForHarmlessDifferences(): void
    {
        // A false result does not mean the HTML is dangerous
        Assert::false(Html::isSanitized('<p class="lead">Hello</p>'));
        Assert::false(Html::isSanitized('<br>'));
        Assert::false(Html::isSanitized('<p>Tom & Jerry</p>'));
    }

    public function testLinks(): void
    {
        Assert::same(
            ['/about', 'https://e.com'],
            Html::links('<a href="/about">About</a> and <a href="https://e.com">E</a>')
        );
        Assert::same([], Html::links('<p>No links here</p>'));
    }

    public function testLinksDecodesEntities(): void
    {
        Assert::same(['/x?a=1&b=2'], Html::links('<a href="/x?a=1&amp;b=2">q</a>'));
        Assert::same(['mailto:a@b.c'], Html::links('<a href="mailto:a@b.c">m</a>'));
    }

    public function testLinksIgnoresHiddenAndUnsafeMarkup(): void
    {
        // Addresses cleaning rejects are not reported
        Assert::same([], Html::links('<a href="javascript:alert(1)">Bad</a>'));

        // Neither are links buried in comments or script strings
        $html = '<!-- <a href="/c">c</a> --><script>"<a href=\'/s\'>"</script><a href="/ok">o</a>';
        Assert::same(['/ok'], Html::links($html));
    }

    public function testLinkWithAllAttributes(): void
    {
        $result = Html::link('print.css', 'stylesheet', 'text/css', 'Print', 'print', 'en');

        Assert::contains('href="print.css"', $result);
        Assert::contains('title="Print"', $result);
        Assert::contains('media="print"', $result);
        Assert::contains('hreflang="en"', $result);
    }

    public function testLinkWithArrayAttributes(): void
    {
        $result = Html::link(['href' => 'custom.css', 'rel' => 'prefetch']);

        Assert::contains('href="custom.css"', $result);
        Assert::contains('rel="prefetch"', $result);
    }

    public function testLinkWithHref(): void
    {
        $result = Html::link('styles.css');

        Assert::contains('href="styles.css"', $result);
        Assert::contains('rel="stylesheet"', $result);
        Assert::contains('type="text/css"', $result);
    }

    public function testMinify(): void
    {
        Assert::same('<div> <p>Hello</p> </div>', Html::minify("<div>\n    <p>Hello</p>\n</div>"));
        Assert::same('<p>a</p><p>b</p>', Html::minify('<p>a</p><!-- note --><p>b</p>'));
        Assert::same('', Html::minify(''));
    }

    public function testMinifyKeepsRenderingIntact(): void
    {
        // The space between inline elements is significant and must survive
        Assert::same('<b>a</b> <i>b</i>', Html::minify("<b>a</b>\n<i>b</i>"));
        Assert::same('<span>a</span> <span>b</span>', Html::minify('<span>a</span>  <span>b</span>'));
    }

    public function testMinifyPreservesPreformattedBlocks(): void
    {
        Assert::same("<pre>keep   me\n  here</pre>", Html::minify("<pre>keep   me\n  here</pre>"));
        Assert::same("<textarea>  raw\n  text </textarea>", Html::minify("<textarea>  raw\n  text </textarea>"));
    }

    public function testOfReturnsFluentWrapper(): void
    {
        $fluent = Html::of('<p>Hello</p>');

        Assert::type(FluentHtml::class, $fluent);
        Assert::same('<p>Hello</p>', $fluent->get());
    }

    public function testSafeTags(): void
    {
        $tags = Html::safeTags();

        Assert::same(121, count($tags));
        Assert::contains('p', $tags);
        Assert::contains('em', $tags);
        Assert::notContains('script', $tags);
        Assert::notContains('style', $tags);
        Assert::notContains('form', $tags);
    }

    public function testSafeTagsIncludesHeadOnlyTagsThatNeverSurvive(): void
    {
        // These are recognised names, but sanitize() treats its input as page content
        Assert::contains('title', Html::safeTags());
        Assert::same('', Html::sanitize('<title>x</title>'));
    }

    public function testSafeTagsIsSortedList(): void
    {
        $tags = Html::safeTags();
        $sorted = $tags;
        sort($sorted);

        Assert::same($sorted, $tags);
        Assert::same(range(0, count($tags) - 1), array_keys($tags));
    }

    public function testSanitizeAllowedTagsUnwrapOthers(): void
    {
        // Safe tags outside the allow-list are unwrapped, keeping their text
        Assert::same(
            'Keep <em>this</em> and that',
            Html::sanitize('<p>Keep <em>this</em> and <b>that</b></p>', ['em'])
        );

        // Tag names are matched case-insensitively and tolerate angle brackets
        Assert::same(
            'Keep <em>this</em>',
            Html::sanitize('<p>Keep <em>this</em></p>', ['<EM>'])
        );

        // Unsafe tags cannot be re-enabled through the allow-list
        Assert::same('a', Html::sanitize('<p>a</p>', ['script']));
    }

    public function testSanitizeDropsUnsafeElementsWithContent(): void
    {
        Assert::same('<div>text</div>', Html::sanitize('<div><style>body{display:none}</style>text</div>'));
        Assert::same('<p>Hello world</p>', Html::sanitize('<p>Hello <script>alert(1)</script>world</p>'));
    }

    public function testSanitizeEmpty(): void
    {
        Assert::same('', Html::sanitize(''));
    }

    public function testSanitizeInvalidMaxLengthThrows(): void
    {
        Assert::throws(
            static fn () => Html::sanitize('<p>a</p>', [], ['https'], -2),
            \Phuture\Coherence\Exception\InvalidArgumentException::class
        );
    }

    public function testSanitizeKeepsSafeMarkup(): void
    {
        Assert::same(
            '<a href="https://example.com" title="Home">Link</a>',
            Html::sanitize('<a href="https://example.com" title="Home">Link</a>')
        );

        // Relative URLs are preserved
        Assert::same('<a href="/about">About</a>', Html::sanitize('<a href="/about">About</a>'));

        // Malformed markup is repaired rather than dropped
        Assert::same('<p>unclosed <b>bold</b></p>', Html::sanitize('<p>unclosed <b>bold'));
    }

    public function testSanitizeRemovesEventHandlers(): void
    {
        Assert::same('<p>Hello</p>', Html::sanitize('<p onclick="steal()">Hello</p>'));
        Assert::same('<img src="x" />', Html::sanitize('<img src="x" onerror="alert(1)">'));
    }

    public function testSanitizeRespectsAllowedSchemes(): void
    {
        // Schemes outside the list lose the attribute but keep the element
        Assert::same('<a>Click</a>', Html::sanitize('<a href="javascript:alert(1)">Click</a>'));
        Assert::same('<a>Mail</a>', Html::sanitize('<a href="mailto:a@b.c">Mail</a>', [], ['https']));

        // Obfuscated schemes are normalized before the check
        Assert::same('<img />', Html::sanitize('<IMG SRC=jAvAsCrIpT:alert(1)>'));

        // A scheme that is explicitly allowed survives, with the URL entity-encoded
        Assert::same(
            '<a href="mailto:a&#64;b.c">Mail</a>',
            Html::sanitize('<a href="mailto:a@b.c">Mail</a>', [], ['mailto'])
        );
    }

    public function testSanitizeTruncatesAtMaxLength(): void
    {
        $long = '<p>' . str_repeat('a', 100) . '</p>';

        // The limit applies to the raw input, so the tail is cut before parsing
        Assert::same('<p>' . str_repeat('a', 7) . '</p>', Html::sanitize($long, [], ['https'], 10));

        // -1 disables the limit entirely
        Assert::same($long, Html::sanitize($long, [], ['https'], -1));
    }

    public function testScriptWithContent(): void
    {
        $result = Html::script(null, 'alert("hi");');

        Assert::same('<script>alert("hi");</script>', $result);
    }

    public function testScriptWithExtraAttributes(): void
    {
        $result = Html::script('app.js', '', ['defer' => true, 'async' => true]);

        Assert::contains('defer', $result);
        Assert::contains('async', $result);
        Assert::contains('src="app.js"', $result);
    }

    public function testScriptWithNoSrc(): void
    {
        $result = Html::script(null);

        Assert::same('<script></script>', $result);
    }

    public function testScriptWithSrc(): void
    {
        $result = Html::script('app.js');

        Assert::contains('src="app.js"', $result);
        Assert::contains('<script', $result);
        Assert::contains('</script>', $result);
    }

    public function testSecureLinks(): void
    {
        Assert::same(
            '<a href="https://example.com" rel="noopener noreferrer">Visit</a>',
            Html::secureLinks('<a href="https://example.com">Visit</a>')
        );
        Assert::same('', Html::secureLinks(''));
    }

    public function testSecureLinksAlsoCleansTheDocument(): void
    {
        // It runs the full cleaner, so unsafe markup and class attributes go too
        $result = Html::secureLinks('<p class="lead">Hi</p><script>alert(1)</script>');

        Assert::same('<p>Hi</p>', $result);

        // Anchors without an address still receive the relationship value
        Assert::same('<a rel="noopener noreferrer">jump</a>', Html::secureLinks('<a>jump</a>'));
    }

    public function testSecureLinksForcesHttps(): void
    {
        Assert::same(
            '<a href="https://example.com" rel="nofollow">Visit</a>',
            Html::secureLinks('<a href="http://example.com">Visit</a>', 'nofollow', true)
        );

        // Media addresses are rewritten too
        Assert::contains('src="https://e.com/a.png"', Html::secureLinks('<img src="http://e.com/a.png">', 'x', true));
    }

    public function testStripComments(): void
    {
        Assert::same('<p class="lead">Hello</p>', Html::stripComments('<p class="lead">Hello<!-- note --></p>'));
        Assert::same('<p>a</p><p>b</p>', Html::stripComments('<p>a</p><!-- one --><!-- two --><p>b</p>'));
        Assert::same('', Html::stripComments(''));
    }

    public function testStripCommentsEdgeCases(): void
    {
        // Spans newlines
        Assert::same('<p>ab</p>', Html::stripComments("<p>a<!-- over\ntwo lines -->b</p>"));

        // Conditional comments go as well
        Assert::same('', Html::stripComments('<!--[if IE]><p>ie</p><![endif]-->'));

        // An unfinished comment is left alone rather than eating the document
        Assert::same('<p>Kept<!-- unfinished', Html::stripComments('<p>Kept<!-- unfinished'));
    }

    public function testStripTags(): void
    {
        // Strip all tags
        Assert::same('Hello world', Html::stripTags('<p>Hello <b>world</b></p>'));

        // Preserve a single allowed tag
        Assert::same('Hello <b>world</b>', Html::stripTags('<p>Hello <b>world</b></p>', ['b']));

        // Preserve multiple allowed tags
        Assert::same(
            'Keep <em>this</em> <strong>bold</strong>',
            Html::stripTags('<p>Keep <em>this</em> <strong>bold</strong></p>', ['em', 'strong'])
        );

        // Preserve tags with attributes
        Assert::same(
            '<a href="#">Link</a> text',
            Html::stripTags('<div><a href="#">Link</a> text</div>', ['a'])
        );

        // Empty string input
        Assert::same('', Html::stripTags(''));

        // No tags in plain text
        Assert::same('Hello world', Html::stripTags('Hello world'));

        // Void/self-closing elements are stripped
        Assert::same('Line1Line2', Html::stripTags('Line1<br>Line2'));
        Assert::same('Photo', Html::stripTags('<img src="photo.jpg" alt="Photo">Photo'));

        // Default allowedTags is empty
        Assert::same('text', Html::stripTags('<span>text</span>', []));
    }

    public function testTableBasic(): void
    {
        $rows = [['Alice', 30], ['Bob', 25]];
        $expected = '<table><tbody><tr><td>Alice</td><td>30</td></tr><tr><td>Bob</td><td>25</td></tr></tbody></table>';
        Assert::same($expected, Html::table($rows));
    }

    public function testTableCellsCastToString(): void
    {
        Assert::same('<table><tbody><tr><td>1</td><td>3.14</td><td></td></tr></tbody></table>', Html::table([[1, 3.14, null]]));
    }

    public function testTableEmpty(): void
    {
        Assert::same('<table><tbody></tbody></table>', Html::table([]));
    }

    public function testTableHeadersWithAttributes(): void
    {
        $rows = [['Alice', 'Engineer'], ['Bob', 'Designer']];
        $headers = ['Name', 'Role'];
        $result = Html::table($rows, $headers, ['border' => '1']);
        Assert::contains('<thead><tr><th>Name</th><th>Role</th></tr></thead>', $result);
        Assert::contains('<tbody>', $result);
        Assert::contains('border="1"', $result);
    }

    public function testTableSingleRow(): void
    {
        Assert::same('<table><tbody><tr><td>Hello</td></tr></tbody></table>', Html::table([['Hello']]));
    }

    public function testTableWithAttributes(): void
    {
        $result = Html::table([['A']], [], ['class' => 'grid', 'id' => 'main']);
        Assert::contains('class="grid"', $result);
        Assert::contains('id="main"', $result);
        Assert::contains('<table', $result);
    }

    public function testTableWithHeaders(): void
    {
        $rows = [['Alice', 30]];
        $headers = ['Name', 'Age'];
        $expected = '<table><thead><tr><th>Name</th><th>Age</th></tr></thead><tbody><tr><td>Alice</td><td>30</td></tr></tbody></table>';
        Assert::same($expected, Html::table($rows, $headers));
    }

    public function testTagBasic(): void
    {
        Assert::same('<p>Hello</p>', Html::tag('p', 'Hello'));
    }

    public function testTagBooleanFalseAttribute(): void
    {
        Assert::same('<input type="text">', Html::tag('input', '', ['type' => 'text', 'disabled' => false]));
    }

    public function testTagDivWithContent(): void
    {
        Assert::same('<div class="wrapper"><span>inner</span></div>', Html::tag('div', '<span>inner</span>', ['class' => 'wrapper']));
    }

    public function testTagEmptyNameThrows(): void
    {
        Assert::throws(
            static fn () => Html::tag('', 'content'),
            \Phuture\Coherence\Exception\InvalidArgumentException::class
        );
    }

    public function testTagEscapesAttributeValues(): void
    {
        Assert::same(
            '<a href="https://example.com?a=1&amp;b=2">Link</a>',
            Html::tag('a', 'Link', ['href' => 'https://example.com?a=1&b=2'])
        );
    }

    public function testTagHr(): void
    {
        Assert::same('<hr>', Html::tag('hr'));
    }

    public function testTagImgVoid(): void
    {
        Assert::same('<img src="test.png" alt="Test">', Html::tag('img', '', ['src' => 'test.png', 'alt' => 'Test']));
    }

    public function testTagMultipleAttributes(): void
    {
        $result = Html::tag('div', 'Content', ['id' => 'main', 'class' => 'container']);
        Assert::true(str_contains($result, 'id="main"'));
        Assert::true(str_contains($result, 'class="container"'));
        Assert::true(str_contains($result, '>Content</div>'));
    }

    public function testTagNullAttribute(): void
    {
        Assert::same('<input type="text">', Html::tag('input', '', ['type' => 'text', 'disabled' => null]));
    }

    public function testTags(): void
    {
        Assert::same(['div', 'p'], Html::tags('<div><p>Hi</p><p>There</p></div>'));
        Assert::same([], Html::tags('Just text'));
        Assert::same([], Html::tags(''));
    }

    public function testTagsAreLowercasedAndUnique(): void
    {
        Assert::same(['p'], Html::tags('<P>one</P><p>two</p>'));
    }

    public function testTagSelfClosingImg(): void
    {
        Assert::same('<img src="photo.jpg">', Html::tag('img', '', ['src' => 'photo.jpg']));
    }

    public function testTagsIgnoresHiddenMarkup(): void
    {
        Assert::same(['p'], Html::tags('<!-- <b>x</b> --><p>hi</p>'));
        Assert::same(['p'], Html::tags('<script>"<i>y</i>"</script><p>hi</p>'));
    }

    public function testTagVoidElement(): void
    {
        Assert::same('<br>', Html::tag('br'));
    }

    public function testTagVoidElementWithAttributes(): void
    {
        Assert::same('<input type="text" required>', Html::tag('input', '', ['type' => 'text', 'required' => true]));
    }

    public function testTagWithAttributes(): void
    {
        Assert::same('<p class="greeting">Hello</p>', Html::tag('p', 'Hello', ['class' => 'greeting']));
    }

    public function testToHtmlEmpty(): void
    {
        Assert::same('', Html::toHtml(''));
    }

    public function testToHtmlMarkdownBlockquote(): void
    {
        $result = Html::toHtml('> quoted text');
        Assert::true(str_contains($result, '<blockquote>'));
    }

    public function testToHtmlMarkdownBold(): void
    {
        Assert::same('<p><strong>bold</strong></p>', Html::toHtml('**bold**'));
    }

    public function testToHtmlMarkdownHeading(): void
    {
        Assert::same('<h1>Heading</h1>', Html::toHtml('# Heading'));
    }

    public function testToHtmlMarkdownItalic(): void
    {
        $result = Html::toHtml('*italic*');
        Assert::true(str_contains($result, '<em>italic</em>'));
    }

    public function testToHtmlMarkdownLink(): void
    {
        $result = Html::toHtml('[Link](https://example.com)');
        Assert::true(str_contains($result, '<a href="https://example.com">Link</a>'));
    }

    public function testToHtmlMarkdownUnorderedList(): void
    {
        $result = Html::toHtml("- item 1\n- item 2");
        Assert::true(str_contains($result, '<ul>'));
        Assert::true(str_contains($result, '<li>'));
    }

    public function testToHtmlPlainTextEncodesEntities(): void
    {
        Assert::same('&lt;p&gt;Hello&lt;/p&gt;', Html::toHtml('<p>Hello</p>'));
    }

    public function testToHtmlPlainTextWithNewlines(): void
    {
        Assert::same("Line 1<br>\nLine 2", Html::toHtml("Line 1\nLine 2"));
    }

    public function testToMarkdownBold(): void
    {
        Assert::same('**bold**', Html::toMarkdown('<strong>bold</strong>'));
    }

    public function testToMarkdownEmpty(): void
    {
        Assert::same('', Html::toMarkdown(''));
    }

    public function testToMarkdownH2(): void
    {
        Assert::same('## Heading', Html::toMarkdown('<h2>Heading</h2>'));
    }

    public function testToMarkdownHeading(): void
    {
        Assert::same('# Heading', Html::toMarkdown('<h1>Heading</h1>'));
    }

    public function testToMarkdownItalic(): void
    {
        Assert::same('*italic*', Html::toMarkdown('<em>italic</em>'));
    }

    public function testToMarkdownLink(): void
    {
        Assert::same('[Link](https://example.com)', Html::toMarkdown('<a href="https://example.com">Link</a>'));
    }

    public function testToMarkdownParagraph(): void
    {
        Assert::same('Hello world', Html::toMarkdown('<p>Hello world</p>'));
    }

    public function testToTextBasic(): void
    {
        Assert::same('Hello world', Html::toText('<p>Hello <b>world</b></p>'));
    }

    public function testToTextBlockElements(): void
    {
        Assert::same("Para 1\n\nPara 2", Html::toText('<p>Para 1</p><p>Para 2</p>'));
    }

    public function testToTextBrTag(): void
    {
        Assert::same("Line 1\nLine 2", Html::toText('Line 1<br>Line 2'));
    }

    public function testToTextBrTagSelfClosing(): void
    {
        Assert::same("Line 1\nLine 2", Html::toText('Line 1<br/>Line 2'));
    }

    public function testToTextBrTagWithSpace(): void
    {
        Assert::same("Line 1\nLine 2", Html::toText('Line 1<br />Line 2'));
    }

    public function testToTextEmpty(): void
    {
        Assert::same('', Html::toText(''));
    }

    public function testToTextEntities(): void
    {
        Assert::same('Tom & Jerry', Html::toText('Tom &amp; Jerry'));
        Assert::same('<script>', Html::toText('&lt;script&gt;'));
    }

    public function testToTextToHtmlRoundTrip(): void
    {
        $original = 'Hello & goodbye';
        $html = Html::toHtml($original);
        Assert::same($original, Html::toText($html));
    }

    public function testTruncate(): void
    {
        // No truncation when text fits or exactly equals limit
        Assert::same('<p>Hi</p>', Html::truncate('<p>Hi</p>', 10));
        Assert::same('<p>Hello</p>', Html::truncate('<p>Hello</p>', 5));
        Assert::same('', Html::truncate('', 5));

        // Plain text truncation
        Assert::same('Hello...', Html::truncate('Hello World', 5));

        // Custom end marker
        Assert::same('<p>Hel [more]</p>', Html::truncate('<p>Hello</p>', 3, ' [more]'));
        Assert::same('<p>Hel</p>', Html::truncate('<p>Hello</p>', 3, ''));

        // Open tags are closed after truncation
        Assert::same('<div><p>Hel...</p></div>', Html::truncate('<div><p>Hello</p></div>', 3));
    }

    public function testTruncateEntity(): void
    {
        // Entity counts as 1 visible character
        Assert::same('<p>Tom &amp;...</p>', Html::truncate('<p>Tom &amp; Jerry</p>', 5));
    }

    public function testTruncateMultibyte(): void
    {
        Assert::same('<p>hél...</p>', Html::truncate('<p>héllo</p>', 3));
    }

    public function testTruncateNestedTags(): void
    {
        $result = Html::truncate('<p>Hello <b>World</b></p>', 7);
        Assert::contains('<b>', $result);
        Assert::contains('</b>', $result);
        Assert::contains('</p>', $result);
        Assert::contains('...', $result);
    }

    public function testTruncateVoidElement(): void
    {
        $result = Html::truncate('<p>Hello<br>World</p>', 6);
        Assert::notContains('</br>', $result);
        Assert::contains('<br>', $result);
    }

    public function testTruncateWords(): void
    {
        // No truncation when word count fits
        Assert::same('<p>Hello World</p>', Html::truncateWords('<p>Hello World</p>', 5));
        Assert::same('', Html::truncateWords('', 5));

        // Plain text word truncation
        Assert::same('One Two...', Html::truncateWords('One Two Three', 2));

        // HTML with word truncation — open tags closed
        Assert::same('<p>One Two...</p>', Html::truncateWords('<p>One Two Three Four</p>', 2));

        // Multiple spaces preserved up to truncation point
        Assert::same('<p>One   Two...</p>', Html::truncateWords('<p>One   Two  Three</p>', 2));

        // Custom end marker
        Assert::same('<p>One [...]</p>', Html::truncateWords('<p>One Two Three</p>', 1, ' [...]'));
    }

    public function testTruncateWordsEntity(): void
    {
        Assert::same('<p>Tom &amp;...</p>', Html::truncateWords('<p>Tom &amp; Jerry</p>', 2));
    }

    public function testTruncateWordsNestedTags(): void
    {
        $result = Html::truncateWords('<p>Hello <b>World</b> Foo</p>', 2);
        Assert::contains('...', $result);
        Assert::notContains('Foo', $result);
    }

    public function testTruncateWordsVoidElement(): void
    {
        $result = Html::truncateWords('<p>Hello<br>World Foo</p>', 1);
        Assert::notContains('</br>', $result);
    }
}

(new HtmlTest())->run();
