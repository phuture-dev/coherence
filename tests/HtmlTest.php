<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Phuture\Coherence\Html;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

class HtmlTest extends TestCase
{
    public function testToTextBasic(): void
    {
        Assert::same('Hello world', Html::toText('<p>Hello <b>world</b></p>'));
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

    public function testToTextEntities(): void
    {
        Assert::same('Tom & Jerry', Html::toText('Tom &amp; Jerry'));
        Assert::same('<script>', Html::toText('&lt;script&gt;'));
    }

    public function testToTextBlockElements(): void
    {
        Assert::same("Para 1\n\nPara 2", Html::toText('<p>Para 1</p><p>Para 2</p>'));
    }

    public function testToTextEmpty(): void
    {
        Assert::same('', Html::toText(''));
    }

    public function toHtmlPlainText(): void
    {
        Assert::same('Hello &amp; goodbye', Html::toHtml('Hello & goodbye'));
    }

    public function testToHtmlPlainTextWithNewlines(): void
    {
        Assert::same("Line 1<br>\nLine 2", Html::toHtml("Line 1\nLine 2"));
    }

    public function testToHtmlPlainTextEncodesEntities(): void
    {
        Assert::same('&lt;p&gt;Hello&lt;/p&gt;', Html::toHtml('<p>Hello</p>'));
    }

    public function testToHtmlMarkdownHeading(): void
    {
        Assert::same('<h1>Heading</h1>', Html::toHtml('# Heading'));
    }

    public function testToHtmlMarkdownBold(): void
    {
        Assert::same('<p><strong>bold</strong></p>', Html::toHtml('**bold**'));
    }

    public function testToHtmlMarkdownLink(): void
    {
        $result = Html::toHtml('[Link](https://example.com)');
        Assert::true(str_contains($result, '<a href="https://example.com">Link</a>'));
    }

    public function testToHtmlEmpty(): void
    {
        Assert::same('', Html::toHtml(''));
    }

    public function testToMarkdownHeading(): void
    {
        Assert::same('# Heading', Html::toMarkdown('<h1>Heading</h1>'));
    }

    public function testToMarkdownBold(): void
    {
        Assert::same('**bold**', Html::toMarkdown('<strong>bold</strong>'));
    }

    public function testToMarkdownLink(): void
    {
        Assert::same('[Link](https://example.com)', Html::toMarkdown('<a href="https://example.com">Link</a>'));
    }

    public function testToMarkdownParagraph(): void
    {
        Assert::same('Hello world', Html::toMarkdown('<p>Hello world</p>'));
    }

    public function testToMarkdownEmpty(): void
    {
        Assert::same('', Html::toMarkdown(''));
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

    public function testEncodeSpecialCharsOnly(): void
    {
        Assert::same('Tom &amp; Jerry', Html::encode('Tom & Jerry', Html::ENCODE_SPECIAL_CHARS));
        Assert::same('café', Html::encode('café', Html::ENCODE_SPECIAL_CHARS));
    }

    public function testEncodeQuotes(): void
    {
        Assert::same('a &quot;b&quot; c', Html::encode('a "b" c'));
        Assert::same("a &#039;b&#039; c", Html::encode("a 'b' c"));
    }

    public function testEncodeEmpty(): void
    {
        Assert::same('', Html::encode(''));
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

    public function testDecodeSpecialCharsOnly(): void
    {
        Assert::same('Tom & Jerry', Html::decode('Tom &amp; Jerry', Html::ENCODE_SPECIAL_CHARS));
        Assert::same('caf&eacute;', Html::decode('caf&eacute;', Html::ENCODE_SPECIAL_CHARS));
    }

    public function testDecodeEmpty(): void
    {
        Assert::same('', Html::decode(''));
    }

    public function testTagBasic(): void
    {
        Assert::same('<p>Hello</p>', Html::tag('p', 'Hello'));
    }

    public function testTagWithAttributes(): void
    {
        Assert::same('<p class="greeting">Hello</p>', Html::tag('p', 'Hello', ['class' => 'greeting']));
    }

    public function testTagVoidElement(): void
    {
        Assert::same('<br>', Html::tag('br'));
    }

    public function testTagVoidElementWithAttributes(): void
    {
        Assert::same('<input type="text" required>', Html::tag('input', '', ['type' => 'text', 'required' => true]));
    }

    public function testTagBooleanFalseAttribute(): void
    {
        Assert::same('<input type="text">', Html::tag('input', '', ['type' => 'text', 'disabled' => false]));
    }

    public function testTagNullAttribute(): void
    {
        Assert::same('<input type="text">', Html::tag('input', '', ['type' => 'text', 'disabled' => null]));
    }

    public function testTagEmptyNameThrows(): void
    {
        Assert::throws(
            static fn () => Html::tag('', 'content'),
            \Phuture\Coherence\Exception\InvalidArgumentException::class
        );
    }

    public function testTagSelfClosingImg(): void
    {
        Assert::same('<img src="photo.jpg">', Html::tag('img', '', ['src' => 'photo.jpg']));
    }

    public function testTagMultipleAttributes(): void
    {
        $result = Html::tag('div', 'Content', ['id' => 'main', 'class' => 'container']);
        Assert::true(str_contains($result, 'id="main"'));
        Assert::true(str_contains($result, 'class="container"'));
        Assert::true(str_contains($result, '>Content</div>'));
    }

    public function testTagEscapesAttributeValues(): void
    {
        Assert::same(
            '<a href="https://example.com?a=1&amp;b=2">Link</a>',
            Html::tag('a', 'Link', ['href' => 'https://example.com?a=1&b=2'])
        );
    }

    public function testImgBasic(): void
    {
        Assert::same('<img src="photo.jpg" alt="">', Html::img('photo.jpg'));
    }

    public function testImgWithAlt(): void
    {
        Assert::same('<img src="photo.jpg" alt="A photo">', Html::img('photo.jpg', ['alt' => 'A photo']));
    }

    public function testImgWithArrayAttributes(): void
    {
        Assert::same(
            '<img src="photo.jpg" alt="Photo" class="thumbnail">',
            Html::img(['src' => 'photo.jpg', 'alt' => 'Photo', 'class' => 'thumbnail'])
        );
    }

    public function testImgAddsEmptyAltWhenMissing(): void
    {
        Assert::same('<img src="photo.jpg" alt="">', Html::img('photo.jpg'));
    }

    public function testLinkTagBasic(): void
    {
        Assert::same(
            '<link href="styles.css" rel="stylesheet" type="text/css">',
            Html::linkTag('styles.css')
        );
    }

    public function testLinkTagCustomRel(): void
    {
        Assert::same(
            '<link href="favicon.ico" rel="shortcut icon" type="image/ico">',
            Html::linkTag('favicon.ico', 'shortcut icon', 'image/ico')
        );
    }

    public function testLinkTagWithMedia(): void
    {
        $result = Html::linkTag('print.css', 'stylesheet', 'text/css', '', 'print');
        Assert::true(str_contains($result, 'media="print"'));
    }

    public function testLinkTagWithHreflang(): void
    {
        $result = Html::linkTag('alt.css', 'stylesheet', 'text/css', '', '', 'en');
        Assert::true(str_contains($result, 'hreflang="en"'));
    }

    public function testLinkTagWithArray(): void
    {
        $result = Html::linkTag(['href' => 'print.css', 'rel' => 'stylesheet', 'media' => 'print']);
        Assert::true(str_contains($result, 'href="print.css"'));
        Assert::true(str_contains($result, 'media="print"'));
    }

    public function testScriptTagBasic(): void
    {
        Assert::same('<script src="app.js"></script>', Html::scriptTag('app.js'));
    }

    public function testScriptTagWithArray(): void
    {
        Assert::same(
            '<script src="app.js" defer></script>',
            Html::scriptTag(['src' => 'app.js', 'defer' => true])
        );
    }

    public function testScriptTagAsync(): void
    {
        Assert::same(
            '<script src="analytics.js" async></script>',
            Html::scriptTag(['src' => 'analytics.js', 'async' => true])
        );
    }

    public function testUlBasic(): void
    {
        Assert::same(
            '<ul><li>red</li><li>blue</li><li>green</li></ul>',
            Html::ul(['red', 'blue', 'green'])
        );
    }

    public function testUlWithAttributes(): void
    {
        $result = Html::ul(['a', 'b'], ['class' => 'list', 'id' => 'items']);
        Assert::true(str_contains($result, 'class="list"'));
        Assert::true(str_contains($result, 'id="items"'));
        Assert::true(str_contains($result, '<li>a</li>'));
    }

    public function testUlNested(): void
    {
        $result = Html::ul(['colors' => ['red', 'blue']]);
        Assert::true(str_contains($result, '<li>colors'));
        Assert::true(str_contains($result, '<li>red</li>'));
        Assert::true(str_contains($result, '<li>blue</li>'));
    }

    public function testUlEmpty(): void
    {
        Assert::same('<ul></ul>', Html::ul([]));
    }

    public function testOlBasic(): void
    {
        Assert::same(
            '<ol><li>first</li><li>second</li><li>third</li></ol>',
            Html::ol(['first', 'second', 'third'])
        );
    }

    public function testOlWithAttributes(): void
    {
        $result = Html::ol(['a', 'b'], ['type' => '1']);
        Assert::true(str_contains($result, 'type="1"'));
    }

    public function testOlNested(): void
    {
        $result = Html::ol(['steps' => ['one', 'two']]);
        Assert::true(str_contains($result, '<li>steps'));
        Assert::true(str_contains($result, '<li>one</li>'));
    }

    public function testVideoBasic(): void
    {
        $result = Html::video('movie.mp4', 'Unsupported browser.', ['controls' => true]);
        Assert::true(str_contains($result, '<video'));
        Assert::true(str_contains($result, 'src="movie.mp4"'));
        Assert::true(str_contains($result, 'controls'));
        Assert::true(str_contains($result, '>Unsupported browser.</video>'));
    }

    public function testVideoWithSources(): void
    {
        $result = Html::video(
            [Html::source('movie.mp4', 'video/mp4'), Html::source('movie.ogg', 'video/ogg')],
            'Unsupported.',
            ['controls' => true]
        );
        Assert::true(str_contains($result, '<source'));
        Assert::true(str_contains($result, 'type="video/mp4"'));
        Assert::true(str_contains($result, 'type="video/ogg"'));
    }

    public function testVideoWithTracks(): void
    {
        $result = Html::video(
            'movie.mp4',
            'Unsupported.',
            ['controls' => true],
            [Html::track('subs.vtt', 'subtitles', 'en', 'English')]
        );
        Assert::true(str_contains($result, '<track'));
        Assert::true(str_contains($result, 'srclang="en"'));
        Assert::true(str_contains($result, 'label="English"'));
    }

    public function testAudioBasic(): void
    {
        $result = Html::audio('song.mp3', 'Unsupported.', ['controls' => true]);
        Assert::true(str_contains($result, '<audio'));
        Assert::true(str_contains($result, 'src="song.mp3"'));
        Assert::true(str_contains($result, 'controls'));
        Assert::true(str_contains($result, '>Unsupported.</audio>'));
    }

    public function testAudioWithSources(): void
    {
        $result = Html::audio(
            [Html::source('song.mp3', 'audio/mpeg'), Html::source('song.ogg', 'audio/ogg')],
            'Unsupported.',
            ['controls' => true]
        );
        Assert::true(str_contains($result, 'type="audio/mpeg"'));
        Assert::true(str_contains($result, 'type="audio/ogg"'));
    }

    public function testSourceBasic(): void
    {
        Assert::same('<source src="movie.mp4" type="video/mp4">', Html::source('movie.mp4', 'video/mp4'));
    }

    public function testSourceWithAttributes(): void
    {
        $result = Html::source('movie.mp4', 'video/mp4', ['class' => 'primary']);
        Assert::true(str_contains($result, 'src="movie.mp4"'));
        Assert::true(str_contains($result, 'type="video/mp4"'));
        Assert::true(str_contains($result, 'class="primary"'));
    }

    public function testSourceDefaultType(): void
    {
        Assert::same('<source src="file.xyz" type="unknown">', Html::source('file.xyz'));
    }

    public function testEmbedBasic(): void
    {
        Assert::same(
            '<embed src="movie.swf" type="application/x-shockwave-flash">',
            Html::embed('movie.swf', 'application/x-shockwave-flash')
        );
    }

    public function testEmbedWithAttributes(): void
    {
        $result = Html::embed('movie.swf', 'application/x-shockwave-flash', ['class' => 'player']);
        Assert::true(str_contains($result, 'src="movie.swf"'));
        Assert::true(str_contains($result, 'type="application/x-shockwave-flash"'));
        Assert::true(str_contains($result, 'class="player"'));
    }

    public function testEmbedWithoutType(): void
    {
        Assert::same('<embed src="content.html">', Html::embed('content.html'));
    }

    public function testObjectBasic(): void
    {
        Assert::same(
            '<object data="movie.swf" type="application/x-shockwave-flash"></object>',
            Html::object('movie.swf', 'application/x-shockwave-flash')
        );
    }

    public function testObjectWithParams(): void
    {
        $result = Html::object(
            'movie.swf',
            'application/x-shockwave-flash',
            ['class' => 'player'],
            [Html::param('autoplay', 'true')]
        );
        Assert::true(str_contains($result, '<param'));
        Assert::true(str_contains($result, 'name="autoplay"'));
        Assert::true(str_contains($result, 'value="true"'));
    }

    public function testParamBasic(): void
    {
        Assert::same('<param name="autoplay" value="true">', Html::param('autoplay', 'true'));
    }

    public function testParamWithAttributes(): void
    {
        $result = Html::param('quality', 'high', ['class' => 'config']);
        Assert::true(str_contains($result, 'name="quality"'));
        Assert::true(str_contains($result, 'value="high"'));
        Assert::true(str_contains($result, 'class="config"'));
    }

    public function testTrackBasic(): void
    {
        Assert::same(
            '<track src="subs.vtt" kind="subtitles" srclang="en" label="English">',
            Html::track('subs.vtt', 'subtitles', 'en', 'English')
        );
    }

    public function testDoctypeHtml5(): void
    {
        Assert::same('<!DOCTYPE html>', Html::doctype());
        Assert::same('<!DOCTYPE html>', Html::doctype('html5'));
    }

    public function testDoctypeHtml4Strict(): void
    {
        $result = Html::doctype('html4-strict');
        Assert::true(str_contains($result, 'HTML 4.01'));
        Assert::true(str_contains($result, 'strict'));
    }

    public function testDoctypeXhtml1Trans(): void
    {
        $result = Html::doctype('xhtml1-trans');
        Assert::true(str_contains($result, 'XHTML 1.0'));
        Assert::true(str_contains($result, 'Transitional'));
    }

    public function testDoctypeInvalidThrows(): void
    {
        Assert::throws(
            static fn () => Html::doctype('invalid'),
            \Phuture\Coherence\Exception\InvalidArgumentException::class
        );
    }

    public function testEncodeDecodeRoundTrip(): void
    {
        $original = '<p>Hello & "world"</p>';
        Assert::same($original, Html::decode(Html::encode($original)));
    }

    public function testEncodeDecodeSpecialCharsRoundTrip(): void
    {
        $original = 'Tom & Jerry <chat>';
        Assert::same($original, Html::decode(Html::encode($original, Html::ENCODE_SPECIAL_CHARS), Html::ENCODE_SPECIAL_CHARS));
    }

    public function testToTextToHtmlRoundTrip(): void
    {
        $original = 'Hello & goodbye';
        $html = Html::toHtml($original);
        Assert::same($original, Html::toText($html));
    }

    public function testTagDivWithContent(): void
    {
        Assert::same('<div class="wrapper"><span>inner</span></div>', Html::tag('div', '<span>inner</span>', ['class' => 'wrapper']));
    }

    public function testTagHr(): void
    {
        Assert::same('<hr>', Html::tag('hr'));
    }

    public function testTagImgVoid(): void
    {
        Assert::same('<img src="test.png" alt="Test">', Html::tag('img', '', ['src' => 'test.png', 'alt' => 'Test']));
    }

    public function testUlDeepNesting(): void
    {
        $items = [
            'level1' => [
                'level2' => [
                    'deep' => ['value'],
                ],
            ],
        ];
        $result = Html::ul($items);
        Assert::true(str_contains($result, '<li>level1'));
        Assert::true(str_contains($result, '<li>level2'));
        Assert::true(str_contains($result, '<li>deep'));
        Assert::true(str_contains($result, '<li>value</li>'));
    }

    public function testToHtmlMarkdownItalic(): void
    {
        $result = Html::toHtml('*italic*');
        Assert::true(str_contains($result, '<em>italic</em>'));
    }

    public function testToHtmlMarkdownUnorderedList(): void
    {
        $result = Html::toHtml("- item 1\n- item 2");
        Assert::true(str_contains($result, '<ul>'));
        Assert::true(str_contains($result, '<li>'));
    }

    public function testToHtmlMarkdownBlockquote(): void
    {
        $result = Html::toHtml('> quoted text');
        Assert::true(str_contains($result, '<blockquote>'));
    }

    public function testToMarkdownItalic(): void
    {
        Assert::same('*italic*', Html::toMarkdown('<em>italic</em>'));
    }

    public function testToMarkdownH2(): void
    {
        Assert::same('## Heading', Html::toMarkdown('<h2>Heading</h2>'));
    }

    public function testVideoNoFallback(): void
    {
        $result = Html::video('movie.mp4', '', ['controls' => true]);
        Assert::true(str_contains($result, 'src="movie.mp4"'));
        Assert::true(str_contains($result, 'controls'));
        Assert::true(str_contains($result, '></video>'));
        Assert::false(str_contains($result, '>Unsupported'));
    }
}

(new HtmlTest())->run();
