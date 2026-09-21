<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Type;

use Phuture\Coherence\Html;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Enum\EncodingMode;
use Phuture\Coherence\Interface\Htmlable;
use Phuture\Coherence\Type\Html as FluentHtml;

require __DIR__ . '/../bootstrap.php';

class HtmlTest extends TestCase
{
    public function testChainsTransformations(): void
    {
        $result = FluentHtml::from('<p onclick="steal()">Hello <script>alert(1)</script>world</p>')
            ->sanitize()
            ->truncate(8)
            ->toString();

        Assert::same('<p>Hello wo...</p>', $result);
    }

    public function testDecodeDelegates(): void
    {
        Assert::same(
            Html::decode('&lt;p&gt;'),
            FluentHtml::from('&lt;p&gt;')->decode()->get()
        );
        Assert::same(
            Html::decode('&amp;&eacute;', EncodingMode::SpecialChars),
            FluentHtml::from('&amp;&eacute;')->decode(EncodingMode::SpecialChars)->get()
        );
    }

    public function testEncodeDelegates(): void
    {
        Assert::same(
            Html::encode('<p>a & b</p>'),
            FluentHtml::from('<p>a & b</p>')->encode()->get()
        );
    }

    public function testImagesDelegates(): void
    {
        $html = '<img src="a.png"><img src="b.png">';

        Assert::same(Html::images($html), FluentHtml::from($html)->images());
    }

    public function testImplementsHtmlable(): void
    {
        $fluent = FluentHtml::from('<p>x</p>');

        Assert::type(Htmlable::class, $fluent);
        Assert::type(\Stringable::class, $fluent);
    }

    public function testIsSanitizedDelegates(): void
    {
        Assert::true(FluentHtml::from('<p>Hello</p>')->isSanitized());
        Assert::false(FluentHtml::from('<p onclick="x()">Hello</p>')->isSanitized());
    }

    public function testLinksDelegates(): void
    {
        $html = '<a href="/a">a</a><a href="/b">b</a>';

        Assert::same(Html::links($html), FluentHtml::from($html)->links());
    }

    public function testMinifyDelegates(): void
    {
        $html = "<div>\n    <p>Hello</p>\n</div>";

        Assert::same(Html::minify($html), FluentHtml::from($html)->minify()->get());
    }

    public function testOfCreatesWrapper(): void
    {
        $fluent = Html::of('<p>Hello</p>');

        Assert::type(FluentHtml::class, $fluent);
        Assert::same('<p>Hello</p>', $fluent->get());
    }

    public function testSanitizeDelegates(): void
    {
        $html = '<p onclick="steal()">Hello</p>';

        Assert::same(Html::sanitize($html), FluentHtml::from($html)->sanitize()->get());
        Assert::same(
            Html::sanitize($html, ['em']),
            FluentHtml::from($html)->sanitize(['em'])->get()
        );
    }

    public function testSecureLinksDelegates(): void
    {
        $html = '<a href="http://example.com">Visit</a>';

        Assert::same(Html::secureLinks($html), FluentHtml::from($html)->secureLinks()->get());
        Assert::same(
            Html::secureLinks($html, 'nofollow', true),
            FluentHtml::from($html)->secureLinks('nofollow', true)->get()
        );
    }

    public function testStripCommentsDelegates(): void
    {
        $html = '<p>Hello<!-- note --></p>';

        Assert::same(Html::stripComments($html), FluentHtml::from($html)->stripComments()->get());
    }

    public function testStripTagsDelegates(): void
    {
        $html = '<p>Hello <b>world</b></p>';

        Assert::same(Html::stripTags($html), FluentHtml::from($html)->stripTags()->get());
        Assert::same(Html::stripTags($html, ['b']), FluentHtml::from($html)->stripTags(['b'])->get());
    }

    public function testTagsDelegates(): void
    {
        $html = '<div><p>Hi</p></div>';

        Assert::same(Html::tags($html), FluentHtml::from($html)->tags());
    }

    public function testTerminalMethodsDoNotMutate(): void
    {
        $fluent = FluentHtml::from('<p>Hello <a href="/a">link</a></p>');

        $fluent->links();
        $fluent->tags();
        $fluent->toText();
        $fluent->toMarkdown();

        Assert::same('<p>Hello <a href="/a">link</a></p>', $fluent->get());
    }

    public function testToHtmlReturnsWrappedMarkup(): void
    {
        $fluent = FluentHtml::from('<p>Hello</p>');

        Assert::same('<p>Hello</p>', $fluent->toHtml());
        Assert::same($fluent->toString(), $fluent->toHtml());
    }

    public function testToMarkdownDelegates(): void
    {
        $html = '<h1>Title</h1>';

        Assert::same(Html::toMarkdown($html), FluentHtml::from($html)->toMarkdown());
    }

    public function testToStringAndCastAgree(): void
    {
        $fluent = FluentHtml::from('<p>Hello</p>');

        Assert::same('<p>Hello</p>', $fluent->toString());
        Assert::same('<p>Hello</p>', (string) $fluent);
        Assert::same($fluent->toString(), (string) $fluent);
    }

    public function testToTextDelegates(): void
    {
        $html = '<p>Hello <b>world</b></p>';

        Assert::same(Html::toText($html), FluentHtml::from($html)->toText());
        Assert::same('Hello world', FluentHtml::from($html)->toText());
    }

    public function testTruncateDelegates(): void
    {
        $html = '<p>Hello world</p>';

        Assert::same(Html::truncate($html, 5), FluentHtml::from($html)->truncate(5)->get());
        Assert::same(
            Html::truncate($html, 5, '!'),
            FluentHtml::from($html)->truncate(5, '!')->get()
        );
    }

    public function testTruncateWordsDelegates(): void
    {
        $html = '<p>Hello brave new world</p>';

        Assert::same(Html::truncateWords($html, 2), FluentHtml::from($html)->truncateWords(2)->get());
    }
}

(new HtmlTest())->run();
