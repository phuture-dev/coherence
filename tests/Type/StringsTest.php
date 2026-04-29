<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Type;

use Phuture\Coherence\Strings;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Type\Strings as FluentStrings;

require __DIR__ . '/../bootstrap.php';

/**
 * Tests for the Type\Strings fluent wrapper.
 *
 * Each test verifies that the fluent method produces the same result
 * as the corresponding static Strings method, and that method chaining works.
 */
class StringsTest extends TestCase
{
    public function testAfter(): void
    {
        Assert::same(Strings::after('hello world', ' '), FluentStrings::from('hello world')->after(' ')->get());
        Assert::same(Strings::after('user@example.com', '@'), FluentStrings::from('user@example.com')->after('@')->get());
        Assert::same('', FluentStrings::from('hello')->after('x')->get());
        Assert::same('hello', FluentStrings::from('hello')->after('')->get());
    }

    public function testAfterLast(): void
    {
        Assert::same(Strings::afterLast('hello world foo', ' '), FluentStrings::from('hello world foo')->afterLast(' ')->get());
        Assert::same(Strings::afterLast('path/to/file.txt', '/'), FluentStrings::from('path/to/file.txt')->afterLast('/')->get());
        Assert::same('', FluentStrings::from('hello')->afterLast('x')->get());
    }

    public function testAscii(): void
    {
        Assert::same(Strings::ascii('héllo'), FluentStrings::from('héllo')->ascii()->get());
        Assert::same(Strings::ascii('ñaño'), FluentStrings::from('ñaño')->ascii()->get());
        Assert::same(Strings::ascii('über', 'de'), FluentStrings::from('über')->ascii('de')->get());
        Assert::same('hello', FluentStrings::from('hello')->ascii()->get());
    }

    public function testBefore(): void
    {
        Assert::same(Strings::before('hello world', ' '), FluentStrings::from('hello world')->before(' ')->get());
        Assert::same(Strings::before('user@example.com', '@'), FluentStrings::from('user@example.com')->before('@')->get());
        Assert::same('hello', FluentStrings::from('hello')->before('x')->get());
        Assert::same('hello', FluentStrings::from('hello')->before('')->get());
    }

    public function testBeforeLast(): void
    {
        Assert::same(Strings::beforeLast('hello world foo', ' '), FluentStrings::from('hello world foo')->beforeLast(' ')->get());
        Assert::same(Strings::beforeLast('path/to/file.txt', '/'), FluentStrings::from('path/to/file.txt')->beforeLast('/')->get());
        Assert::same('hello', FluentStrings::from('hello')->beforeLast('x')->get());
    }

    public function testBetween(): void
    {
        Assert::same(Strings::between('[hello]', '[', ']'), FluentStrings::from('[hello]')->between('[', ']')->get());
        Assert::same(Strings::between('user@example.com', '@', '.'), FluentStrings::from('user@example.com')->between('@', '.')->get());
        Assert::same('hello', FluentStrings::from('hello')->between('{', '}')->get());
    }

    public function testCamel(): void
    {
        Assert::same(Strings::camel('hello world'), FluentStrings::from('hello world')->camel()->get());
        Assert::same(Strings::camel('hello_world'), FluentStrings::from('hello_world')->camel()->get());
        Assert::same(Strings::camel('hello-world'), FluentStrings::from('hello-world')->camel()->get());
        Assert::same(Strings::camel('HelloWorld'), FluentStrings::from('HelloWorld')->camel()->get());
        Assert::same('', FluentStrings::from('')->camel()->get());
    }

    public function testCapitalize(): void
    {
        Assert::same(Strings::capitalize('hello world'), FluentStrings::from('hello world')->capitalize()->get());
        Assert::same(Strings::capitalize('HELLO WORLD'), FluentStrings::from('HELLO WORLD')->capitalize()->get());
        Assert::same(Strings::capitalize('ñaño ñoño'), FluentStrings::from('ñaño ñoño')->capitalize()->get());
        Assert::same('', FluentStrings::from('')->capitalize()->get());
    }

    public function testChaining(): void
    {
        $result = FluentStrings::from('  hello_world  ')
            ->trim()
            ->replace('_', ' ')
            ->upper()
            ->take(5)
            ->get();
        Assert::same('HELLO', $result);
    }

    public function testChainingCaseConversions(): void
    {
        $result = FluentStrings::from('hello world')
            ->camel()
            ->snake()
            ->get();
        Assert::same('hello_world', $result);
    }

    public function testChainingExtractionAndModification(): void
    {
        $result = FluentStrings::from('user@example.com')
            ->after('@')
            ->before('.')
            ->upper()
            ->get();
        Assert::same('EXAMPLE', $result);
    }

    public function testChainingMultibyte(): void
    {
        $result = FluentStrings::from('ñaño')
            ->upper()
            ->reverse()
            ->lower()
            ->get();
        Assert::same(Strings::lower(Strings::reverse(Strings::upper('ñaño'))), $result);
    }

    public function testChainingTrimAndSquish(): void
    {
        $result = FluentStrings::from("  hello   world  ")
            ->squish()
            ->capitalize()
            ->get();
        Assert::same('Hello World', $result);
    }

    public function testCharAt(): void
    {
        Assert::same(Strings::charAt('hello', 0), FluentStrings::from('hello')->charAt(0)->get());
        Assert::same(Strings::charAt('hello', -1), FluentStrings::from('hello')->charAt(-1)->get());
        Assert::same(Strings::charAt('ñaño', 0), FluentStrings::from('ñaño')->charAt(0)->get());
        Assert::same('', FluentStrings::from('hello')->charAt(100)->get());
    }

    public function testDedupe(): void
    {
        Assert::same(Strings::dedupe('hello    world'), FluentStrings::from('hello    world')->dedupe()->get());
        Assert::same(Strings::dedupe('a,,,b,,,c', ','), FluentStrings::from('a,,,b,,,c')->dedupe(',')->get());
        Assert::same('', FluentStrings::from('')->dedupe()->get());
    }

    public function testExcerpt(): void
    {
        Assert::same(
            Strings::excerpt('The quick brown fox jumps', 'fox', 5),
            FluentStrings::from('The quick brown fox jumps')->excerpt('fox', 5)->get()
        );
        Assert::same(
            Strings::excerpt('hello world', 'world'),
            FluentStrings::from('hello world')->excerpt('world')->get()
        );
    }

    public function testFinish(): void
    {
        Assert::same(Strings::finish('path/to', '/'), FluentStrings::from('path/to')->finish('/')->get());
        Assert::same(Strings::finish('path/to/', '/'), FluentStrings::from('path/to/')->finish('/')->get());
        Assert::same(Strings::finish('path/to///', '/'), FluentStrings::from('path/to///')->finish('/')->get());
    }

    public function testFirst(): void
    {
        Assert::same(Strings::first('hello'), FluentStrings::from('hello')->first()->get());
        Assert::same(Strings::first('hello', 3), FluentStrings::from('hello')->first(3)->get());
        Assert::same(Strings::first('ñaño'), FluentStrings::from('ñaño')->first()->get());
        Assert::same('', FluentStrings::from('')->first()->get());
    }

    public function testFluentCensor(): void
    {
        Assert::same(
            Strings::censor('This is bad', ['bad']),
            FluentStrings::from('This is bad')->censor(['bad'])->get()
        );
    }

    public function testFluentFixEncoding(): void
    {
        Assert::same(
            Strings::fixEncoding('hello world'),
            FluentStrings::from('hello world')->fixEncoding()->get()
        );
    }

    public function testFluentFromBase64(): void
    {
        Assert::same(
            Strings::fromBase64('aGVsbG8='),
            FluentStrings::from('aGVsbG8=')->fromBase64()->get()
        );
    }

    public function testFluentHighlight(): void
    {
        Assert::same(
            Strings::highlight('The quick brown fox', 'quick'),
            FluentStrings::from('The quick brown fox')->highlight('quick')->get()
        );
    }

    public function testFluentIndent(): void
    {
        Assert::same(
            Strings::indent("line1\nline2"),
            FluentStrings::from("line1\nline2")->indent()->get()
        );
    }

    public function testFluentNormalizeNewLines(): void
    {
        Assert::same(
            Strings::normalizeNewLines("line1\r\nline2\rline3"),
            FluentStrings::from("line1\r\nline2\rline3")->normalizeNewLines()->get()
        );
    }

    public function testFluentReplaceArray(): void
    {
        Assert::same(
            Strings::replaceArray('?', ['2026', 'April'], 'Year: ?, Month: ?'),
            FluentStrings::from('Year: ?, Month: ?')->replaceArray('?', ['2026', 'April'])->get()
        );
    }

    public function testFluentReplaceAt(): void
    {
        Assert::same(
            Strings::replaceAt('hello world', 'PHP', 6),
            FluentStrings::from('hello world')->replaceAt('PHP', 6)->get()
        );
    }

    public function testFluentToBase64(): void
    {
        Assert::same(
            Strings::toBase64('hello'),
            FluentStrings::from('hello')->toBase64()->get()
        );
    }

    public function testFrom(): void
    {
        $fluent = FluentStrings::from('hello');
        Assert::same('hello', $fluent->get());
    }

    public function testGet(): void
    {
        Assert::same('hello', FluentStrings::from('hello')->get());
    }

    public function testHeadline(): void
    {
        Assert::same(Strings::headline('hello_world'), FluentStrings::from('hello_world')->headline()->get());
        Assert::same(Strings::headline('foo-bar-baz'), FluentStrings::from('foo-bar-baz')->headline()->get());
        Assert::same(Strings::headline('ñaño_ñoño'), FluentStrings::from('ñaño_ñoño')->headline()->get());
        Assert::same('', FluentStrings::from('')->headline()->get());
    }

    public function testInsert(): void
    {
        Assert::same(Strings::insert('hello world', '!', 5), FluentStrings::from('hello world')->insert('!', 5)->get());
        Assert::same(Strings::insert('hello world', '!', -1), FluentStrings::from('hello world')->insert('!', -1)->get());
        Assert::same(Strings::insert('ñaño', '!', 2), FluentStrings::from('ñaño')->insert('!', 2)->get());
    }

    public function testInvoke(): void
    {
        $fluent = FluentStrings::from('hello')->upper();
        Assert::same('HELLO', $fluent());
    }

    public function testKebab(): void
    {
        Assert::same(Strings::kebab('helloWorld'), FluentStrings::from('helloWorld')->kebab()->get());
        Assert::same(Strings::kebab('HelloWorld'), FluentStrings::from('HelloWorld')->kebab()->get());
        Assert::same(Strings::kebab('hello_world'), FluentStrings::from('hello_world')->kebab()->get());
        Assert::same('', FluentStrings::from('')->kebab()->get());
    }

    public function testLast(): void
    {
        Assert::same(Strings::last('hello'), FluentStrings::from('hello')->last()->get());
        Assert::same(Strings::last('hello', 3), FluentStrings::from('hello')->last(3)->get());
        Assert::same(Strings::last('ñaño'), FluentStrings::from('ñaño')->last()->get());
        Assert::same('', FluentStrings::from('')->last()->get());
    }

    public function testLimit(): void
    {
        Assert::same(Strings::limit('Hello World', 5), FluentStrings::from('Hello World')->limit(5)->get());
        Assert::same(Strings::limit('Hello World', 5, '---'), FluentStrings::from('Hello World')->limit(5, '---')->get());
        Assert::same(Strings::limit('Hi', 10), FluentStrings::from('Hi')->limit(10)->get());
    }

    public function testLower(): void
    {
        Assert::same(Strings::lower('HELLO WORLD'), FluentStrings::from('HELLO WORLD')->lower()->get());
        Assert::same(Strings::lower('ÑOÑO'), FluentStrings::from('ÑOÑO')->lower()->get());
        Assert::same(Strings::lower('ÄÖÜ'), FluentStrings::from('ÄÖÜ')->lower()->get());
        Assert::same('', FluentStrings::from('')->lower()->get());
    }

    public function testLowerFirst(): void
    {
        Assert::same(Strings::lowerFirst('Hello World'), FluentStrings::from('Hello World')->lowerFirst()->get());
        Assert::same(Strings::lowerFirst('HELLO'), FluentStrings::from('HELLO')->lowerFirst()->get());
        Assert::same(Strings::lowerFirst('Ñoño'), FluentStrings::from('Ñoño')->lowerFirst()->get());
        Assert::same('', FluentStrings::from('')->lowerFirst()->get());
    }

    public function testMask(): void
    {
        Assert::same(Strings::mask('1234567890'), FluentStrings::from('1234567890')->mask()->get());
        Assert::same(Strings::mask('1234567890', '*', 3), FluentStrings::from('1234567890')->mask('*', 3)->get());
        Assert::same(Strings::mask('1234567890', '*', 3, 4), FluentStrings::from('1234567890')->mask('*', 3, 4)->get());
        Assert::same(Strings::mask('john@example.com', '*', 0, 4), FluentStrings::from('john@example.com')->mask('*', 0, 4)->get());
    }

    public function testOf(): void
    {
        $fluent = Strings::of('hello');
        Assert::same('hello', $fluent->get());
    }

    public function testOfFactory(): void
    {
        $result = Strings::of('  hello world  ')
            ->trim()
            ->upper()
            ->get();
        Assert::same('HELLO WORLD', $result);
    }

    public function testOfMultibyte(): void
    {
        $result = Strings::of('ñaño')
            ->upper()
            ->get();
        Assert::same('ÑAÑO', $result);
    }

    public function testPad(): void
    {
        Assert::same(Strings::pad('hello', 10), FluentStrings::from('hello')->pad(10)->get());
        Assert::same(Strings::pad('hello', 10, '-'), FluentStrings::from('hello')->pad(10, '-')->get());
        Assert::same(Strings::pad('hello', 10, ' ', STR_PAD_LEFT), FluentStrings::from('hello')->pad(10, ' ', STR_PAD_LEFT)->get());
        Assert::same(Strings::pad('hello', 10, '-', STR_PAD_BOTH), FluentStrings::from('hello')->pad(10, '-', STR_PAD_BOTH)->get());
    }

    public function testPadBoth(): void
    {
        Assert::same(Strings::padBoth('hello', 11, '-'), FluentStrings::from('hello')->padBoth(11, '-')->get());
        Assert::same(Strings::padBoth('hello', 10, '-'), FluentStrings::from('hello')->padBoth(10, '-')->get());
    }

    public function testPadLeft(): void
    {
        Assert::same(Strings::padLeft('hello', 10), FluentStrings::from('hello')->padLeft(10)->get());
        Assert::same(Strings::padLeft('5', 5, '0'), FluentStrings::from('5')->padLeft(5, '0')->get());
    }

    public function testPadRight(): void
    {
        Assert::same(Strings::padRight('hello', 10), FluentStrings::from('hello')->padRight(10)->get());
        Assert::same(Strings::padRight('hello', 10, '-'), FluentStrings::from('hello')->padRight(10, '-')->get());
    }

    public function testPascal(): void
    {
        Assert::same(Strings::pascal('hello world'), FluentStrings::from('hello world')->pascal()->get());
        Assert::same(Strings::pascal('hello_world'), FluentStrings::from('hello_world')->pascal()->get());
        Assert::same(Strings::pascal('hello-world'), FluentStrings::from('hello-world')->pascal()->get());
        Assert::same('', FluentStrings::from('')->pascal()->get());
    }

    public function testRemove(): void
    {
        Assert::same(Strings::remove('hello world', 'o'), FluentStrings::from('hello world')->remove('o')->get());
        Assert::same(Strings::remove('Hello World', 'world', false), FluentStrings::from('Hello World')->remove('world', false)->get());
        Assert::same('', FluentStrings::from('')->remove('x')->get());
    }

    public function testRepeat(): void
    {
        Assert::same(Strings::repeat('ab', 3), FluentStrings::from('ab')->repeat(3)->get());
        Assert::same(Strings::repeat('hello', 1), FluentStrings::from('hello')->repeat(1)->get());
        Assert::same('', FluentStrings::from('ab')->repeat(0)->get());
    }

    public function testReplace(): void
    {
        Assert::same(Strings::replace('hello world', 'world', 'PHP'), FluentStrings::from('hello world')->replace('world', 'PHP')->get());
        Assert::same(Strings::replace('Hello World', 'world', 'PHP', false), FluentStrings::from('Hello World')->replace('world', 'PHP', false)->get());
        Assert::same(Strings::replace('héllo', 'é', 'X'), FluentStrings::from('héllo')->replace('é', 'X')->get());
        Assert::same(Strings::replace('ñañ', 'ñ', 'n'), FluentStrings::from('ñañ')->replace('ñ', 'n')->get());
    }

    public function testReplaceFirst(): void
    {
        Assert::same(Strings::replaceFirst('hello hello', 'hello', 'world'), FluentStrings::from('hello hello')->replaceFirst('hello', 'world')->get());
        Assert::same(Strings::replaceFirst('ñaño', 'ñ', 'X'), FluentStrings::from('ñaño')->replaceFirst('ñ', 'X')->get());
    }

    public function testReplaceLast(): void
    {
        Assert::same(Strings::replaceLast('hello hello', 'hello', 'world'), FluentStrings::from('hello hello')->replaceLast('hello', 'world')->get());
        Assert::same(Strings::replaceLast('ñaño', 'o', 'X'), FluentStrings::from('ñaño')->replaceLast('o', 'X')->get());
    }

    public function testReverse(): void
    {
        Assert::same(Strings::reverse('hello'), FluentStrings::from('hello')->reverse()->get());
        Assert::same(Strings::reverse('ñaño'), FluentStrings::from('ñaño')->reverse()->get());
        Assert::same('', FluentStrings::from('')->reverse()->get());
    }

    public function testScrub(): void
    {
        Assert::same(Strings::scrub("hello\x00world"), FluentStrings::from("hello\x00world")->scrub()->get());
        Assert::same(Strings::scrub('clean text'), FluentStrings::from('clean text')->scrub()->get());
        Assert::same('', FluentStrings::from('')->scrub()->get());
    }

    public function testSlice(): void
    {
        Assert::same(Strings::slice('hello world', 0, 5), FluentStrings::from('hello world')->slice(0, 5)->get());
        Assert::same(Strings::slice('hello world', 6), FluentStrings::from('hello world')->slice(6)->get());
        Assert::same(Strings::slice('hello world', -5), FluentStrings::from('hello world')->slice(-5)->get());
        Assert::same(Strings::slice('ñaño', 1, 2), FluentStrings::from('ñaño')->slice(1, 2)->get());
    }

    public function testSlug(): void
    {
        Assert::same(Strings::slug('Hello World'), FluentStrings::from('Hello World')->slug()->get());
        Assert::same(Strings::slug('Hello World', '_'), FluentStrings::from('Hello World')->slug('_')->get());
        Assert::same(Strings::slug('héllo wörld'), FluentStrings::from('héllo wörld')->slug()->get());
    }

    public function testSnake(): void
    {
        Assert::same(Strings::snake('helloWorld'), FluentStrings::from('helloWorld')->snake()->get());
        Assert::same(Strings::snake('HelloWorld'), FluentStrings::from('HelloWorld')->snake()->get());
        Assert::same(Strings::snake('helloWorld', '-'), FluentStrings::from('helloWorld')->snake('-')->get());
        Assert::same('', FluentStrings::from('')->snake()->get());
    }

    public function testSquish(): void
    {
        Assert::same(Strings::squish('hello    world'), FluentStrings::from('hello    world')->squish()->get());
        Assert::same(Strings::squish("  hello   \n   world  "), FluentStrings::from("  hello   \n   world  ")->squish()->get());
        Assert::same('', FluentStrings::from('')->squish()->get());
    }

    public function testStart(): void
    {
        Assert::same(Strings::start('/path/to', '/'), FluentStrings::from('/path/to')->start('/')->get());
        Assert::same(Strings::start('path/to', '/'), FluentStrings::from('path/to')->start('/')->get());
        Assert::same(Strings::start('///path/to', '/'), FluentStrings::from('///path/to')->start('/')->get());
    }

    public function testStrip(): void
    {
        Assert::same(Strings::strip('<p>Hello <b>world</b></p>'), FluentStrings::from('<p>Hello <b>world</b></p>')->strip()->get());
        Assert::same(Strings::strip('<a href="#">Link</a>', '<a>'), FluentStrings::from('<a href="#">Link</a>')->strip('<a>')->get());
        Assert::same('', FluentStrings::from('')->strip()->get());
    }

    public function testSwap(): void
    {
        Assert::same(
            Strings::swap('hello world', ['hello' => 'hi', 'world' => 'earth']),
            FluentStrings::from('hello world')->swap(['hello' => 'hi', 'world' => 'earth'])->get()
        );
        Assert::same(
            Strings::swap('ñaño', ['ñ' => 'n', 'o' => 'o']),
            FluentStrings::from('ñaño')->swap(['ñ' => 'n', 'o' => 'o'])->get()
        );
    }

    public function testSwapChainedReplacementDoesNotCorrupt(): void
    {
        Assert::same(
            Strings::swap('hello', ['hello' => 'world', 'world' => 'hi']),
            FluentStrings::from('hello')->swap(['hello' => 'world', 'world' => 'hi'])->get()
        );
        Assert::same(
            Strings::swap('a b', ['a' => 'b', 'b' => 'c']),
            FluentStrings::from('a b')->swap(['a' => 'b', 'b' => 'c'])->get()
        );
    }

    public function testTake(): void
    {
        Assert::same(Strings::take('hello world', 5), FluentStrings::from('hello world')->take(5)->get());
        Assert::same(Strings::take('ñaño', 2), FluentStrings::from('ñaño')->take(2)->get());
        Assert::same('', FluentStrings::from('hello')->take(0)->get());
    }

    public function testTakeNegativeCount(): void
    {
        Assert::same(Strings::take('hello world', -5), FluentStrings::from('hello world')->take(-5)->get());
        Assert::same(Strings::take('ñaño', -2), FluentStrings::from('ñaño')->take(-2)->get());
    }

    public function testTitle(): void
    {
        Assert::same(Strings::title('hello world'), FluentStrings::from('hello world')->title()->get());
        Assert::same(Strings::title('ñaño ñoño'), FluentStrings::from('ñaño ñoño')->title()->get());
        Assert::same('', FluentStrings::from('')->title()->get());
    }

    public function testToString(): void
    {
        $fluent = FluentStrings::from('hello')->upper();
        Assert::same('HELLO', (string) $fluent);
    }

    public function testToStringMethod(): void
    {
        Assert::same('hello', FluentStrings::from('hello')->toString());
    }

    public function testTrim(): void
    {
        Assert::same(Strings::trim('  hello world  '), FluentStrings::from('  hello world  ')->trim()->get());
        Assert::same(Strings::trim('***hello***', '*'), FluentStrings::from('***hello***')->trim('*')->get());
        Assert::same('', FluentStrings::from('')->trim()->get());
    }

    public function testTrimLeft(): void
    {
        Assert::same(Strings::trimLeft('  hello world  '), FluentStrings::from('  hello world  ')->trimLeft()->get());
        Assert::same(Strings::trimLeft('***hello***', '*'), FluentStrings::from('***hello***')->trimLeft('*')->get());
    }

    public function testTrimRight(): void
    {
        Assert::same(Strings::trimRight('  hello world  '), FluentStrings::from('  hello world  ')->trimRight()->get());
        Assert::same(Strings::trimRight('***hello***', '*'), FluentStrings::from('***hello***')->trimRight('*')->get());
    }

    public function testTruncate(): void
    {
        Assert::same(Strings::truncate('Hello World', 5), FluentStrings::from('Hello World')->truncate(5)->get());
        Assert::same(Strings::truncate('Hello World', 5, '…'), FluentStrings::from('Hello World')->truncate(5, '…')->get());
        Assert::same(Strings::truncate('ñaño', 2), FluentStrings::from('ñaño')->truncate(2)->get());
    }

    public function testUnwrap(): void
    {
        Assert::same(Strings::unwrap('"hello"', '"'), FluentStrings::from('"hello"')->unwrap('"')->get());
        Assert::same(Strings::unwrap('***hello***', '***'), FluentStrings::from('***hello***')->unwrap('***')->get());
        Assert::same('hello', FluentStrings::from('hello')->unwrap('"')->get());
    }

    public function testUpper(): void
    {
        Assert::same(Strings::upper('hello world'), FluentStrings::from('hello world')->upper()->get());
        Assert::same(Strings::upper('ñoño'), FluentStrings::from('ñoño')->upper()->get());
        Assert::same(Strings::upper('äöü'), FluentStrings::from('äöü')->upper()->get());
        Assert::same('', FluentStrings::from('')->upper()->get());
    }

    public function testWordWrap(): void
    {
        Assert::same(
            Strings::wordWrap('The quick brown fox', 10),
            FluentStrings::from('The quick brown fox')->wordWrap(10)->get()
        );
        Assert::same(
            Strings::wordWrap('The quick brown fox', 10, '<br>'),
            FluentStrings::from('The quick brown fox')->wordWrap(10, '<br>')->get()
        );
        Assert::same(
            Strings::wordWrap('superlongword', 10, "\n", true),
            FluentStrings::from('superlongword')->wordWrap(10, "\n", true)->get()
        );
        Assert::same(
            Strings::wordWrap('héllo wörld testing', 8),
            FluentStrings::from('héllo wörld testing')->wordWrap(8)->get()
        );
    }

    public function testWrap(): void
    {
        Assert::same(Strings::wrap('hello', '"'), FluentStrings::from('hello')->wrap('"')->get());
        Assert::same(Strings::wrap('hello', '[]'), FluentStrings::from('hello')->wrap('[]')->get());
        Assert::same('""', FluentStrings::from('')->wrap('"')->get());
    }
}

// Run the tests
(new StringsTest())->run();
