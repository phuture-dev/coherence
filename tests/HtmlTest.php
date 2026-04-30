<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Phuture\Coherence\Html;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

class HtmlTest extends TestCase
{
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

    public function testTagSelfClosingImg(): void
    {
        Assert::same('<img src="photo.jpg">', Html::tag('img', '', ['src' => 'photo.jpg']));
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

    public function testBuildSingleElement(): void
    {
        $result = Html::build([
            ['tag' => 'p', 'content' => 'Hello'],
        ]);

        Assert::same('<p>Hello</p>', $result);
    }

    public function testBuildMultipleElements(): void
    {
        $result = Html::build([
            ['tag' => 'h1', 'content' => 'Title'],
            ['tag' => 'p', 'content' => 'Paragraph'],
        ]);

        Assert::same('<h1>Title</h1><p>Paragraph</p>', $result);
    }

    public function testBuildWithAttributes(): void
    {
        $result = Html::build([
            ['tag' => 'a', 'attributes' => ['href' => 'https://example.com'], 'content' => 'Link'],
        ]);

        Assert::same('<a href="https://example.com">Link</a>', $result);
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

    public function testBuildEmptyArray(): void
    {
        Assert::same('', Html::build([]));
    }

    public function testLinkWithHref(): void
    {
        $result = Html::link('styles.css');

        Assert::contains('href="styles.css"', $result);
        Assert::contains('rel="stylesheet"', $result);
        Assert::contains('type="text/css"', $result);
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

    public function testScriptWithSrc(): void
    {
        $result = Html::script('app.js');

        Assert::contains('src="app.js"', $result);
        Assert::contains('<script', $result);
        Assert::contains('</script>', $result);
    }

    public function testScriptWithContent(): void
    {
        $result = Html::script(content: 'alert("hi");');

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
        $result = Html::script();

        Assert::same('<script></script>', $result);
    }
}

(new HtmlTest())->run();
