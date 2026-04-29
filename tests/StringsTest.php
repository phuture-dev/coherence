<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Phuture\Coherence\Strings;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Type\Strings as FluentStrings;

require __DIR__ . '/bootstrap.php';

/**
 * Comprehensive tests for the Strings helper class.
 *
 * Tests cover all 38 Phase 1 methods including case conversion,
 * checking, trimming & cleaning, and extraction operations.
 */
class StringsTest extends TestCase
{
    public function testAfter(): void
    {
        Assert::same('world', Strings::after('hello world', ' '));
        Assert::same('example.com', Strings::after('user@example.com', '@'));
        Assert::same('12-25', Strings::after('2023-12-25', '-'));
    }

    public function testAfterEmptySearch(): void
    {
        Assert::same('hello', Strings::after('hello', ''));
    }

    public function testAfterLast(): void
    {
        Assert::same('foo', Strings::afterLast('hello world foo', ' '));
        Assert::same('file.txt', Strings::afterLast('path/to/file.txt', '/'));
        Assert::same('c', Strings::afterLast('a.b.c', '.'));
    }

    public function testAfterLastEmptySearch(): void
    {
        Assert::same('hello', Strings::afterLast('hello', ''));
    }

    public function testAfterLastNotFound(): void
    {
        Assert::same('', Strings::afterLast('hello', 'x'));
        Assert::same('', Strings::afterLast('hello world', 'xyz'));
    }

    public function testAfterNotFound(): void
    {
        Assert::same('', Strings::after('hello', 'x'));
        Assert::same('', Strings::after('hello world', 'xyz'));
    }

    public function testAscii(): void
    {
        Assert::same('hello', Strings::ascii('héllo'));
        Assert::same('nano', Strings::ascii('ñaño'));
        Assert::same('hello', Strings::ascii('hello'));
    }

    public function testAsciiArtGlyphWidth(): void
    {
        $output = Strings::asciiArt('A');
        $lines = explode("\n", $output);
        Assert::count(5, $lines);

        foreach ($lines as $line) {
            Assert::same(6, mb_strlen(trim($line), 'UTF-8'));
        }
    }

    public function testAsciiArtMultipleChars(): void
    {
        $output = Strings::asciiArt('AB');
        $lines = explode("\n", $output);

        // Two 6-wide glyphs plus 1 space between = 13 chars per line
        foreach ($lines as $line) {
            Assert::same(13, mb_strlen($line, 'UTF-8'));
        }
    }

    public function testAsciiEmpty(): void
    {
        Assert::same('', Strings::ascii(''));
    }

    public function testAsciiGermanLanguage(): void
    {
        Assert::same('Ae', Strings::ascii('Ä', 'de'));
        Assert::same('ueber', Strings::ascii('über', 'de'));
    }

    public function testBefore(): void
    {
        Assert::same('hello', Strings::before('hello world', ' '));
        Assert::same('user', Strings::before('user@example.com', '@'));
        Assert::same('2023', Strings::before('2023-12-25', '-'));
    }

    public function testBeforeAfterOpposites(): void
    {
        $string = 'hello world';
        $delimiter = ' ';
        Assert::same('hello', Strings::before($string, $delimiter));
        Assert::same('world', Strings::after($string, $delimiter));
    }

    public function testBeforeEmptySearch(): void
    {
        Assert::same('hello', Strings::before('hello', ''));
    }

    public function testBeforeLast(): void
    {
        Assert::same('hello world', Strings::beforeLast('hello world foo', ' '));
        Assert::same('path/to', Strings::beforeLast('path/to/file.txt', '/'));
        Assert::same('a.b', Strings::beforeLast('a.b.c', '.'));
    }

    public function testBeforeLastAfterLastOpposites(): void
    {
        $string = 'path/to/file.txt';
        $delimiter = '/';
        Assert::same('path/to', Strings::beforeLast($string, $delimiter));
        Assert::same('file.txt', Strings::afterLast($string, $delimiter));
    }

    public function testBeforeLastEmptySearch(): void
    {
        Assert::same('hello', Strings::beforeLast('hello', ''));
    }

    public function testBeforeLastNotFound(): void
    {
        Assert::same('hello', Strings::beforeLast('hello', 'x'));
        Assert::same('hello world', Strings::beforeLast('hello world', 'xyz'));
    }

    public function testBeforeNotFound(): void
    {
        Assert::same('hello', Strings::before('hello', 'x'));
        Assert::same('hello world', Strings::before('hello world', 'xyz'));
    }

    public function testBetween(): void
    {
        Assert::same('hello', Strings::between('[hello]', '[', ']'));
        Assert::same('example', Strings::between('user@example.com', '@', '.'));
        Assert::same('1b2', Strings::between('a1b2c3', 'a', 'c'));
    }

    public function testBetweenEmptyDelimiters(): void
    {
        Assert::same('hello', Strings::between('hello', '', ''));
    }

    public function testBetweenNotFound(): void
    {
        Assert::same('hello', Strings::between('hello', '{', '}'));
        Assert::same('hello', Strings::between('hello', 'x', 'y'));
    }

    public function testCamel(): void
    {
        Assert::same('helloWorld', Strings::camel('hello world'));
        Assert::same('helloWorld', Strings::camel('hello_world'));
        Assert::same('helloWorld', Strings::camel('hello-world'));
        Assert::same('helloWorld', Strings::camel('HelloWorld'));
        Assert::same('helloWorldTest', Strings::camel('hello-world_test'));
        Assert::same('userProfile', Strings::camel('user_profile'));
    }

    public function testCamelEmpty(): void
    {
        Assert::same('', Strings::camel(''));
    }

    public function testCamelSingleWord(): void
    {
        Assert::same('hello', Strings::camel('hello'));
    }

    public function testCapitalize(): void
    {
        Assert::same('Hello World', Strings::capitalize('hello world'));
        Assert::same('Hello World', Strings::capitalize('HELLO WORLD'));
        Assert::same('Hello World', Strings::capitalize('hELLO wORLD'));
        Assert::same('Ñaño Ñoño', Strings::capitalize('ñaño ñoño'));
        Assert::same('A B C', Strings::capitalize('a b c'));
    }

    public function testCapitalizeEmpty(): void
    {
        Assert::same('', Strings::capitalize(''));
    }

    public function testCapitalizeSingleWord(): void
    {
        Assert::same('Hello', Strings::capitalize('hello'));
    }

    public function testCensor(): void
    {
        Assert::same('This is *** and ***', Strings::censor('This is bad and awful', ['bad', 'awful']));
        Assert::same('#### language', Strings::censor('BAD language', ['bad'], '####'));
    }

    public function testCensorCaseInsensitive(): void
    {
        Assert::same('This is *** language', Strings::censor('This is BAD language', ['bad']));
    }

    public function testCensorEmptyBannedWords(): void
    {
        Assert::same('hello world', Strings::censor('hello world', []));
    }

    public function testCensorNoMatch(): void
    {
        Assert::same('hello world', Strings::censor('hello world', ['foo', 'bar']));
    }

    public function testCharAt(): void
    {
        Assert::same('h', Strings::charAt('hello', 0));
        Assert::same('e', Strings::charAt('hello', 1));
        Assert::same('o', Strings::charAt('hello', 4));
    }

    public function testCharAtEmpty(): void
    {
        Assert::same('', Strings::charAt('', 0));
    }

    public function testCharAtMultibyte(): void
    {
        Assert::same('ñ', Strings::charAt('ñaño', 0));
        Assert::same('a', Strings::charAt('ñaño', 1));
        Assert::same('o', Strings::charAt('ñaño', -1));
    }

    public function testCharAtNegativeIndex(): void
    {
        Assert::same('o', Strings::charAt('hello', -1));
        Assert::same('l', Strings::charAt('hello', -2));
    }

    public function testCharAtOutOfBounds(): void
    {
        Assert::same('', Strings::charAt('hello', 10));
        Assert::same('', Strings::charAt('hello', -10));
    }

    public function testChunk(): void
    {
        Assert::same(['ab', 'cd', 'ef'], Strings::chunk('abcdef', 2));
        Assert::same(['hel', 'lo'], Strings::chunk('hello', 3));
    }

    public function testChunkEmptyString(): void
    {
        Assert::same([], Strings::chunk('', 3));
    }

    public function testChunkMultibyte(): void
    {
        Assert::same(['ña', 'ño'], Strings::chunk('ñaño', 2));
    }

    public function testChunkNegativeSizeThrows(): void
    {
        Assert::exception(function () {
            Strings::chunk('hello', -1);
        }, \Phuture\Coherence\Exception\InvalidArgumentException::class);
    }

    public function testChunkZeroSizeThrows(): void
    {
        Assert::exception(function () {
            Strings::chunk('hello', 0);
        }, \Phuture\Coherence\Exception\InvalidArgumentException::class);
    }

    public function testCompare(): void
    {
        Assert::same(0, Strings::compare('hello', 'hello'));
        Assert::true(Strings::compare('apple', 'banana') < 0);
        Assert::true(Strings::compare('banana', 'apple') > 0);
    }

    public function testCompareCaseInsensitive(): void
    {
        Assert::same(0, Strings::compare('Hello', 'hello', false));
        Assert::true(Strings::compare('Hello', 'hello', true) !== 0);
    }

    public function testCompareEmpty(): void
    {
        Assert::same(0, Strings::compare('', ''));
        Assert::true(Strings::compare('', 'a') < 0);
        Assert::true(Strings::compare('a', '') > 0);
    }

    public function testCountOccurrences(): void
    {
        Assert::same(2, Strings::countOccurrences('hello world hello', 'hello'));
        Assert::same(2, Strings::countOccurrences('aaaa', 'aa'));
        Assert::same(0, Strings::countOccurrences('hello', 'xyz'));
        Assert::same(1, Strings::countOccurrences('hello', 'hello'));
    }

    public function testCountOccurrencesEmptySearch(): void
    {
        Assert::same(0, Strings::countOccurrences('hello', ''));
        Assert::same(0, Strings::countOccurrences('', 'hello'));
    }

    public function testCountOccurrencesMultibyte(): void
    {
        Assert::same(2, Strings::countOccurrences('ñaño', 'ñ'));
    }

    public function testDedupe(): void
    {
        Assert::same('hello world', Strings::dedupe('hello    world'));
        Assert::same('a,b,c', Strings::dedupe('a,,,b,,,c', ','));
        Assert::same('-test-', Strings::dedupe('---test---', '-'));
        Assert::same(' hello ', Strings::dedupe('  hello  '));
    }

    public function testDedupeEmpty(): void
    {
        Assert::same('', Strings::dedupe(''));
    }

    public function testEmojiCharacters(): void
    {
        Assert::same(7, Strings::length('hello 😀')); // 5 letters + 1 space + 1 emoji
        Assert::same('h', Strings::first('hello 😀'));
    }

    public function testEmptyStringAllMethods(): void
    {
        Assert::same('', Strings::lower(''));
        Assert::same('', Strings::upper(''));
        Assert::same('', Strings::capitalize(''));
        Assert::same('', Strings::camel(''));
        Assert::same('', Strings::snake(''));
        Assert::same('', Strings::kebab(''));
        Assert::same('', Strings::pascal(''));
        Assert::true(Strings::isEmpty(''));
        Assert::true(Strings::isBlank(''));
        Assert::false(Strings::isFilled(''));
        Assert::same(0, Strings::length(''));
        Assert::same('', Strings::trim(''));
        Assert::same('', Strings::first(''));
        Assert::same('', Strings::last(''));
    }

    public function testEndsWith(): void
    {
        Assert::true(Strings::endsWith('hello world', 'world'));
        Assert::true(Strings::endsWith('hello', 'hello'));
        Assert::true(Strings::endsWith('image.jpg', '.jpg'));
        Assert::false(Strings::endsWith('hello world', 'hello'));
        Assert::false(Strings::endsWith('hello', 'Hello'));
    }

    public function testEndsWithEmptySearch(): void
    {
        Assert::true(Strings::endsWith('hello', ''));
        Assert::true(Strings::endsWith('', ''));
    }

    public function testEquals(): void
    {
        Assert::true(Strings::equals('hello', 'hello'));
        Assert::false(Strings::equals('hello', 'world'));
        Assert::false(Strings::equals('Hello', 'hello'));
    }

    public function testEqualsCaseInsensitive(): void
    {
        Assert::true(Strings::equals('Hello', 'hello', false));
        Assert::true(Strings::equals('HELLO', 'hello', false));
        Assert::false(Strings::equals('hello', 'world', false));
    }

    public function testEqualsEmpty(): void
    {
        Assert::true(Strings::equals('', ''));
        Assert::false(Strings::equals('', 'hello'));
    }

    public function testExcerpt(): void
    {
        Assert::same('...rown fox jump...', Strings::excerpt('The quick brown fox jumps', 'fox', 5));
    }

    public function testExcerptCustomOmission(): void
    {
        Assert::same('---rown fox jump---', Strings::excerpt('The quick brown fox jumps', 'fox', 5, '---'));
    }

    public function testExcerptEmpty(): void
    {
        Assert::same('', Strings::excerpt('', 'word'));
    }

    public function testExcerptEmptyPhrase(): void
    {
        Assert::same('Hello Worl...', Strings::excerpt('Hello World Test', '', 5));
    }

    public function testExcerptNegativeRadiusThrows(): void
    {
        Assert::throws(
            static fn () => Strings::excerpt('hello world', 'world', -1),
            \Phuture\Coherence\Exception\InvalidArgumentException::class
        );
    }

    public function testExcerptPhraseAtStart(): void
    {
        Assert::same('The qui...', Strings::excerpt('The quick brown fox', 'The', 4));
    }

    public function testExcerptPhraseNotFound(): void
    {
        Assert::same('The q...', Strings::excerpt('The quick brown fox', 'missing', 5));
    }

    public function testExplode(): void
    {
        Assert::same(['a', 'b', 'c'], Strings::explode('a,b,c', ','));
        Assert::same(['hello', 'world'], Strings::explode('hello world', ' '));
    }

    public function testExplodeEmptyDelimiterThrows(): void
    {
        Assert::exception(function () {
            Strings::explode('hello', '');
        }, \Phuture\Coherence\Exception\InvalidArgumentException::class);
    }

    public function testExplodeWithLimit(): void
    {
        Assert::same(['a', 'b,c'], Strings::explode('a,b,c', ',', 2));
    }

    public function testFinish(): void
    {
        Assert::same('path/to/', Strings::finish('path/to', '/'));
        Assert::same('path/to/', Strings::finish('path/to/', '/'));
        Assert::same('path/to/', Strings::finish('path/to///', '/'));
    }

    public function testFinishEmptySuffix(): void
    {
        Assert::same('hello', Strings::finish('hello', ''));
    }

    public function testFirst(): void
    {
        Assert::same('h', Strings::first('hello'));
        Assert::same('ñ', Strings::first('ñaño'));
        Assert::same('hel', Strings::first('hello', 3));
    }

    public function testFirstEmpty(): void
    {
        Assert::same('', Strings::first(''));
    }

    public function testFirstLastOpposites(): void
    {
        $string = 'hello world';
        Assert::same('h', Strings::first($string));
        Assert::same('d', Strings::last($string));
        Assert::same('hello', Strings::first($string, 5));
        Assert::same('world', Strings::last($string, 5));
    }

    public function testFixEncoding(): void
    {
        Assert::same('hello world', Strings::fixEncoding('hello world'));
        Assert::same('valid utf-8 ñoño', Strings::fixEncoding('valid utf-8 ñoño'));
    }

    public function testFixEncodingEmpty(): void
    {
        Assert::same('', Strings::fixEncoding(''));
    }

    public function testFluentAfter(): void
    {
        Assert::same('world', FluentStrings::from('hello world')->after(' ')->get());
    }

    public function testFluentBefore(): void
    {
        Assert::same('hello', FluentStrings::from('hello world')->before(' ')->get());
    }

    public function testFluentCamel(): void
    {
        Assert::same('helloWorld', FluentStrings::from('hello_world')->camel()->get());
    }

    public function testFluentCapitalize(): void
    {
        Assert::same('Hello World', FluentStrings::from('hello world')->capitalize()->get());
    }

    public function testFluentChaining(): void
    {
        $result = FluentStrings::from('  hello_world  ')
            ->trim()
            ->replace('_', ' ')
            ->upper()
            ->take(5)
            ->get();
        Assert::same('HELLO', $result);
    }

    public function testFluentInvoke(): void
    {
        $result = FluentStrings::from('hello')->upper()->__invoke();
        Assert::same('HELLO', $result);
    }

    public function testFluentKebab(): void
    {
        Assert::same('hello-world', FluentStrings::from('helloWorld')->kebab()->get());
    }

    public function testFluentLower(): void
    {
        Assert::same('hello', FluentStrings::from('HELLO')->lower()->get());
    }

    public function testFluentLowerFirst(): void
    {
        Assert::same('hello World', FluentStrings::from('Hello World')->lowerFirst()->get());
    }

    public function testFluentPascal(): void
    {
        Assert::same('HelloWorld', FluentStrings::from('hello_world')->pascal()->get());
    }

    public function testFluentSnake(): void
    {
        Assert::same('hello_world', FluentStrings::from('helloWorld')->snake()->get());
    }

    public function testFluentSquish(): void
    {
        Assert::same('hello world', FluentStrings::from('hello    world')->squish()->get());
    }

    public function testFluentTrim(): void
    {
        Assert::same('hello', FluentStrings::from('  hello  ')->trim()->get());
    }

    public function testFluentUpper(): void
    {
        Assert::same('HELLO', FluentStrings::from('hello')->upper()->get());
    }

    public function testFromBase64(): void
    {
        Assert::same('hello', Strings::fromBase64('aGVsbG8='));
        Assert::same('', Strings::fromBase64(''));
    }

    public function testFromBase64Invalid(): void
    {
        Assert::same('', Strings::fromBase64('not-base64!!!'));
    }

    public function testHas(): void
    {
        Assert::true(Strings::has('hello world', 'world'));
        Assert::true(Strings::has('hello world', 'hello'));
        Assert::true(Strings::has('hello world', 'o w'));
        Assert::false(Strings::has('hello world', 'xyz'));
        Assert::false(Strings::has('hello world', 'World'));
    }

    public function testHasAll(): void
    {
        Assert::true(Strings::hasAll('hello world', ['hello', 'world']));
        Assert::true(Strings::hasAll('hello world foo', ['hello', 'world', 'foo']));
        Assert::false(Strings::hasAll('hello world', ['hello', 'xyz']));
        Assert::false(Strings::hasAll('hello', ['hello', 'world']));
    }

    public function testHasAllCaseInsensitive(): void
    {
        Assert::true(Strings::hasAll('Hello World', ['hello', 'world'], caseSensitive: false));
        Assert::false(Strings::hasAll('Hello World', ['hello', 'world'], caseSensitive: true));
    }

    public function testHasAllEmptyArray(): void
    {
        Assert::true(Strings::hasAll('hello world', []));
    }

    public function testHasCaseInsensitive(): void
    {
        Assert::true(Strings::has('hello world', 'World', caseSensitive: false));
        Assert::true(Strings::has('HELLO', 'hello', caseSensitive: false));
        Assert::true(Strings::has('hello', 'HELLO', caseSensitive: false));
    }

    public function testHasEmptySearch(): void
    {
        Assert::true(Strings::has('hello', ''));
        Assert::true(Strings::has('', ''));
    }

    public function testHasHasNoneOpposites(): void
    {
        $search = ['hello', 'world'];
        $string = 'hello world';
        Assert::true(Strings::hasAll($string, $search));
        Assert::false(Strings::hasNone($string, $search));
    }

    public function testHasNone(): void
    {
        Assert::true(Strings::hasNone('hello world', ['foo', 'bar', 'baz']));
        Assert::false(Strings::hasNone('hello world', ['hello', 'bar']));
        Assert::false(Strings::hasNone('hello world', ['foo', 'world']));
    }

    public function testHasNoneCaseInsensitive(): void
    {
        Assert::false(Strings::hasNone('Hello World', ['HELLO'], caseSensitive: false));
        Assert::true(Strings::hasNone('Hello World', ['HELLO'], caseSensitive: true));
    }

    public function testHasNoneEmptyArray(): void
    {
        Assert::true(Strings::hasNone('hello world', []));
    }

    public function testHeadline(): void
    {
        Assert::same('Hello World', Strings::headline('hello_world'));
        Assert::same('Foo Bar Baz', Strings::headline('foo-bar-baz'));
        Assert::same('Hello World', Strings::headline('hello world'));
        Assert::same('User Profile Data', Strings::headline('user_profile_data'));
    }

    public function testHeadlineEmpty(): void
    {
        Assert::same('', Strings::headline(''));
    }

    public function testHeadlineMultibyte(): void
    {
        Assert::same('Ñaño Ñoño', Strings::headline('ñaño_ñoño'));
    }

    public function testHighlight(): void
    {
        Assert::same('The <mark>quick</mark> brown fox', Strings::highlight('The quick brown fox', 'quick'));
        Assert::same('Hello <b>World</b>', Strings::highlight('Hello World', 'world', '<b>', '</b>'));
    }

    public function testHighlightCaseInsensitive(): void
    {
        Assert::same('Hello <mark>World</mark>', Strings::highlight('Hello World', 'WORLD'));
    }

    public function testHighlightEmptyPhrase(): void
    {
        Assert::same('hello world', Strings::highlight('hello world', ''));
    }

    public function testHighlightMultipleOccurrences(): void
    {
        Assert::same('<mark>fox</mark> and <mark>fox</mark>', Strings::highlight('fox and fox', 'fox'));
    }

    public function testIndent(): void
    {
        Assert::same("\thello", Strings::indent('hello'));
        Assert::same("\t\thello", Strings::indent('hello', 2));
        Assert::same("  hello", Strings::indent('hello', 1, '  '));
    }

    public function testIndentEmpty(): void
    {
        Assert::same('', Strings::indent(''));
    }

    public function testIndentMultiLine(): void
    {
        Assert::same("\tline1\n\tline2", Strings::indent("line1\nline2"));
        Assert::same("  line1\n  line2", Strings::indent("line1\nline2", 1, '  '));
    }

    public function testIndentNegativeLevelThrows(): void
    {
        Assert::throws(
            static fn () => Strings::indent('hello', -1),
            \Phuture\Coherence\Exception\InvalidArgumentException::class
        );
    }

    public function testIndentZeroLevel(): void
    {
        Assert::same('hello', Strings::indent('hello', 0));
    }

    public function testInsert(): void
    {
        Assert::same('hello! world', Strings::insert('hello world', '!', 5));
        Assert::same('hello world!', Strings::insert('hello world', '!', 11));
    }

    public function testInsertAtStart(): void
    {
        Assert::same('!hello', Strings::insert('hello', '!', 0));
    }

    public function testInsertBeyondEnd(): void
    {
        Assert::same('hello!', Strings::insert('hello', '!', 100));
    }

    public function testInsertMultibyte(): void
    {
        Assert::same('ña!ño', Strings::insert('ñaño', '!', 2));
    }

    public function testInsertNegativeIndex(): void
    {
        Assert::same('hello worl!d', Strings::insert('hello world', '!', -1));
    }

    public function testIs(): void
    {
        Assert::true(Strings::is('user_123', 'user_*'));
        Assert::true(Strings::is('user_', 'user_*'));
        Assert::false(Strings::is('user', 'user_*')); // Missing the underscore
        Assert::false(Strings::is('admin_123', 'user_*'));
        Assert::true(Strings::is('photo.jpg', '*.jpg'));
        Assert::true(Strings::is('test.jpg', 'test.*'));
        Assert::true(Strings::is('test.jpg', '*.*'));
    }

    public function testIsAlpha(): void
    {
        Assert::true(Strings::isAlpha('hello'));
        Assert::true(Strings::isAlpha('héllo'));
        Assert::true(Strings::isAlpha('ñaño'));
        Assert::false(Strings::isAlpha('hello1'));
        Assert::false(Strings::isAlpha('hello world'));
        Assert::false(Strings::isAlpha('hello!'));
    }

    public function testIsAlphaEmpty(): void
    {
        Assert::false(Strings::isAlpha(''));
    }

    public function testIsAlphaIsAlphanumericRelationship(): void
    {
        Assert::true(Strings::isAlpha('hello'));
        Assert::true(Strings::isAlphanumeric('hello'));
        Assert::false(Strings::isAlpha('hello123'));
        Assert::true(Strings::isAlphanumeric('hello123'));
    }

    public function testIsAlphanumeric(): void
    {
        Assert::true(Strings::isAlphanumeric('hello123'));
        Assert::true(Strings::isAlphanumeric('hello'));
        Assert::true(Strings::isAlphanumeric('123'));
        Assert::false(Strings::isAlphanumeric('hello!'));
        Assert::false(Strings::isAlphanumeric('hello world'));
    }

    public function testIsAlphanumericEmpty(): void
    {
        Assert::false(Strings::isAlphanumeric(''));
    }

    public function testIsAscii(): void
    {
        Assert::true(Strings::isAscii('hello'));
        Assert::true(Strings::isAscii('hello123'));
        Assert::true(Strings::isAscii('!@#$%^&*()'));
        Assert::true(Strings::isAscii(''));
        Assert::false(Strings::isAscii('héllo'));
        Assert::false(Strings::isAscii('ñaño'));
        Assert::false(Strings::isAscii('你好'));
    }

    public function testIsBlank(): void
    {
        Assert::true(Strings::isBlank(''));
        Assert::true(Strings::isBlank('   '));
        Assert::true(Strings::isBlank("\t\n"));
        Assert::true(Strings::isBlank("  \t  \n  "));
        Assert::false(Strings::isBlank('hello'));
        Assert::false(Strings::isBlank(' hello '));
    }

    public function testIsBlankIsFilledOpposites(): void
    {
        Assert::false(Strings::isBlank('hello'));
        Assert::true(Strings::isFilled('hello'));
        Assert::true(Strings::isBlank('  '));
        Assert::false(Strings::isFilled('  '));
    }

    public function testIsEmail(): void
    {
        Assert::true(Strings::isEmail('user@example.com'));
        Assert::true(Strings::isEmail('user.name+tag@sub.domain.com'));
        Assert::false(Strings::isEmail('not-an-email'));
        Assert::false(Strings::isEmail('missing@'));
        Assert::false(Strings::isEmail('@domain.com'));
    }

    public function testIsEmailEmpty(): void
    {
        Assert::false(Strings::isEmail(''));
    }

    public function testIsEmpty(): void
    {
        Assert::true(Strings::isEmpty(''));
        Assert::false(Strings::isEmpty('0'));
        Assert::false(Strings::isEmpty(' '));
        Assert::false(Strings::isEmpty('hello'));
    }

    public function testIsFilled(): void
    {
        Assert::true(Strings::isFilled('hello'));
        Assert::true(Strings::isFilled('  hello  '));
        Assert::true(Strings::isFilled('0'));
        Assert::false(Strings::isFilled(''));
        Assert::false(Strings::isFilled('   '));
        Assert::false(Strings::isFilled("\t\n"));
    }

    public function testIsJson(): void
    {
        Assert::true(Strings::isJson('{"name":"John"}'));
        Assert::true(Strings::isJson('["a", "b", "c"]'));
        Assert::true(Strings::isJson('123'));
        Assert::true(Strings::isJson('null'));
        Assert::true(Strings::isJson('true'));
        Assert::false(Strings::isJson('not json'));
        Assert::false(Strings::isJson('{"invalid": }'));
        Assert::false(Strings::isJson(''));
    }

    public function testIsLower(): void
    {
        Assert::true(Strings::isLower('hello'));
        Assert::true(Strings::isLower('hello world'));
        Assert::false(Strings::isLower('Hello'));
        Assert::false(Strings::isLower('HELLO'));
        Assert::false(Strings::isLower('hELLO'));
    }

    public function testIsLowerEmpty(): void
    {
        Assert::true(Strings::isLower(''));
    }

    public function testIsLowerIsUpperOpposites(): void
    {
        Assert::true(Strings::isLower('hello'));
        Assert::false(Strings::isUpper('hello'));
        Assert::false(Strings::isLower('HELLO'));
        Assert::true(Strings::isUpper('HELLO'));
    }

    public function testIsLowerMultibyte(): void
    {
        Assert::true(Strings::isLower('ñaño'));
        Assert::false(Strings::isLower('Ñaño'));
    }

    public function testIsNotEmpty(): void
    {
        Assert::true(Strings::isNotEmpty('hello'));
        Assert::true(Strings::isNotEmpty(' '));
        Assert::true(Strings::isNotEmpty('0'));
        Assert::false(Strings::isNotEmpty(''));
    }

    public function testIsNotEmptyIsEmptyOpposites(): void
    {
        Assert::true(Strings::isEmpty(''));
        Assert::false(Strings::isNotEmpty(''));
        Assert::false(Strings::isEmpty('hello'));
        Assert::true(Strings::isNotEmpty('hello'));
    }

    public function testIsNumeric(): void
    {
        Assert::true(Strings::isNumeric('123'));
        Assert::true(Strings::isNumeric('45.6'));
        Assert::true(Strings::isNumeric('-45.6'));
        Assert::false(Strings::isNumeric('abc'));
        Assert::false(Strings::isNumeric('12abc'));
        Assert::false(Strings::isNumeric('1.2.3'));
    }

    public function testIsNumericEmpty(): void
    {
        Assert::false(Strings::isNumeric(''));
    }

    public function testIsUlid(): void
    {
        Assert::true(Strings::isUlid('01ARZ3NDEKTSV4RRFFQ69G5FAV'));
        Assert::true(Strings::isUlid('01ARZ3NDEKTSV4RRFFQ69G5FAV'));
        Assert::false(Strings::isUlid('not-a-ulid'));
        Assert::false(Strings::isUlid(''));
    }

    public function testIsUlidCaseInsensitive(): void
    {
        Assert::true(Strings::isUlid('01arz3ndektsv4rrffq69g5fav'));
    }

    public function testIsUlidWrongLength(): void
    {
        Assert::false(Strings::isUlid('01ARZ3NDEKTSV4RRFFQ69G5FA'));
        Assert::false(Strings::isUlid('01ARZ3NDEKTSV4RRFFQ69G5FAVX'));
    }

    public function testIsUpper(): void
    {
        Assert::true(Strings::isUpper('HELLO'));
        Assert::true(Strings::isUpper('HELLO WORLD'));
        Assert::false(Strings::isUpper('hello'));
        Assert::false(Strings::isUpper('Hello'));
        Assert::false(Strings::isUpper('HELLo'));
    }

    public function testIsUpperEmpty(): void
    {
        Assert::true(Strings::isUpper(''));
    }

    public function testIsUpperMultibyte(): void
    {
        Assert::true(Strings::isUpper('ÑAÑO'));
        Assert::false(Strings::isUpper('ñAÑO'));
    }

    public function testIsUrl(): void
    {
        Assert::true(Strings::isUrl('https://example.com'));
        Assert::true(Strings::isUrl('http://example.com/path?query=1'));
        Assert::false(Strings::isUrl('not-a-url'));
        Assert::false(Strings::isUrl('example.com'));
    }

    public function testIsUrlEmpty(): void
    {
        Assert::false(Strings::isUrl(''));
    }

    public function testIsUuid(): void
    {
        Assert::true(Strings::isUuid('550e8400-e29b-41d4-a716-446655440000'));
        Assert::true(Strings::isUuid('550E8400-E29B-41D4-A716-446655440000'));
        Assert::false(Strings::isUuid('not-a-uuid'));
        Assert::false(Strings::isUuid('550e8400-e29b-41d4-a716'));
        Assert::false(Strings::isUuid(''));
    }

    public function testKebab(): void
    {
        Assert::same('hello-world', Strings::kebab('helloWorld'));
        Assert::same('hello-world', Strings::kebab('HelloWorld'));
        Assert::same('hello-world', Strings::kebab('hello_world'));
        Assert::same('hello-world', Strings::kebab('hello world'));
        Assert::same('user-profile-data', Strings::kebab('UserProfileData'));
    }

    public function testKebabEmpty(): void
    {
        Assert::same('', Strings::kebab(''));
    }

    public function testKebabSnakeOpposites(): void
    {
        $test = 'hello_world_test';
        $kebab = Strings::kebab($test);
        Assert::same('hello-world-test', $kebab);
        Assert::same($test, Strings::snake($kebab));
    }

    public function testLast(): void
    {
        Assert::same('o', Strings::last('hello'));
        Assert::same('o', Strings::last('ñaño'));
        Assert::same('llo', Strings::last('hello', 3));
    }

    public function testLastEmpty(): void
    {
        Assert::same('', Strings::last(''));
    }

    public function testLastPosition(): void
    {
        Assert::same(12, Strings::lastPosition('hello world hello', 'hello'));
        Assert::same(0, Strings::lastPosition('hello', 'hello'));
        Assert::false(Strings::lastPosition('hello', 'xyz'));
    }

    public function testLastPositionMultibyte(): void
    {
        Assert::same(2, Strings::lastPosition('ñaño', 'ñ'));
    }

    public function testLength(): void
    {
        Assert::same(5, Strings::length('hello'));
        Assert::same(4, Strings::length('ñaño'));
        Assert::same(0, Strings::length(''));
        Assert::same(2, Strings::length('你好'));
    }

    public function testLimit(): void
    {
        Assert::same('Hello...', Strings::limit('Hello World', 5));
        Assert::same('Hi', Strings::limit('Hi', 5));
        Assert::same('Hello', Strings::limit('Hello', 5));
    }

    public function testLimitCustomEnd(): void
    {
        Assert::same('Hello [+]', Strings::limit('Hello World', 5, ' [+]'));
    }

    public function testLimitEmpty(): void
    {
        Assert::same('', Strings::limit('', 5));
    }

    public function testLimitMultibyte(): void
    {
        Assert::same('ñ...', Strings::limit('ñaño', 1));
        Assert::same('ñaño', Strings::limit('ñaño', 10));
    }

    public function testLimitNegativeThrows(): void
    {
        Assert::throws(
            static fn () => Strings::limit('hello', -1),
            \Phuture\Coherence\Exception\InvalidArgumentException::class
        );
    }

    public function testLower(): void
    {
        Assert::same('hello world', Strings::lower('HELLO WORLD'));
        Assert::same('hello world', Strings::lower('Hello World'));
        Assert::same('ñoño', Strings::lower('ÑOÑO'));
        Assert::same('äöü', Strings::lower('ÄÖÜ'));
        Assert::same('already lower', Strings::lower('already lower'));
    }

    public function testLowerEmpty(): void
    {
        Assert::same('', Strings::lower(''));
    }

    public function testLowerFirst(): void
    {
        Assert::same('hello World', Strings::lowerFirst('Hello World'));
        Assert::same('hello', Strings::lowerFirst('Hello'));
        Assert::same('hELLO', Strings::lowerFirst('HELLO'));
        Assert::same('ñoño', Strings::lowerFirst('Ñoño'));
        Assert::same('aBC', Strings::lowerFirst('ABC'));
    }

    public function testLowerFirstCapitalizeOpposites(): void
    {
        // capitalize() capitalizes all words (first letter uppercase, rest lowercase)
        // lowerFirst() only lowercases the first character
        $test = 'HELLO WORLD';
        Assert::same('Hello World', Strings::capitalize($test));
        Assert::same('hello World', Strings::lowerFirst(Strings::capitalize($test)));
        Assert::same('hELLO WORLD', Strings::lowerFirst($test)); // Only first char changes
    }

    public function testLowerFirstEmpty(): void
    {
        Assert::same('', Strings::lowerFirst(''));
    }

    public function testLowerUpperOpposites(): void
    {
        // upper() makes everything uppercase, lower() makes everything lowercase
        // They are opposites in terms of operation, not reversible
        Assert::same('HELLO WORLD', Strings::upper('hello world'));
        Assert::same('hello world', Strings::lower('HELLO WORLD'));
        // Double transformation leads to the last operation applied
        Assert::same('hello world', Strings::lower(Strings::upper('HELLO WORLD')));
    }

    public function testMask(): void
    {
        Assert::same('**********', Strings::mask('1234567890'));
        Assert::same('123*******', Strings::mask('1234567890', '*', 3));
        Assert::same('123****890', Strings::mask('1234567890', '*', 3, 4));
        Assert::same('****@example.com', Strings::mask('john@example.com', '*', 0, 4));
    }

    public function testMaskEmpty(): void
    {
        Assert::same('', Strings::mask(''));
    }

    public function testMaskNegativeLengthThrows(): void
    {
        Assert::throws(
            static fn () => Strings::mask('hello', '*', 0, -1),
            \Phuture\Coherence\Exception\InvalidArgumentException::class
        );
    }

    public function testMaskNegativeOffset(): void
    {
        Assert::same('123456****', Strings::mask('1234567890', '*', -4));
        Assert::same('123456***0', Strings::mask('1234567890', '*', -4, 3));
    }

    public function testMatches(): void
    {
        Assert::true(Strings::matches('user_123', '/^user_\d+$/'));
        Assert::true(Strings::matches('test@example.com', '/^[a-z]+@[a-z]+\.[a-z]+$/'));
        Assert::false(Strings::matches('user_abc', '/^user_\d+$/'));
        Assert::false(Strings::matches('invalid email', '/^[a-z]+@[a-z]+\.[a-z]+$/'));
    }

    public function testMultibyteCharacters(): void
    {
        Assert::same('ñaño', Strings::lower('ÑAÑO'));
        Assert::same('ÑAÑO', Strings::upper('ñaño'));
        Assert::same(4, Strings::length('ñaño'));
        Assert::same('ña', Strings::take('ñaño', 2));
    }

    public function testNormalizeNewLines(): void
    {
        Assert::same("line1\nline2\nline3", Strings::normalizeNewLines("line1\r\nline2\rline3"));
        Assert::same("line1\nline2", Strings::normalizeNewLines("line1\r\nline2"));
        Assert::same("line1\nline2", Strings::normalizeNewLines("line1\nline2"));
    }

    public function testNormalizeNewLinesEmpty(): void
    {
        Assert::same('', Strings::normalizeNewLines(''));
    }

    public function testNormalizeNewLinesNoLineEndings(): void
    {
        Assert::same('hello world', Strings::normalizeNewLines('hello world'));
    }

    public function testPad(): void
    {
        Assert::same('hello     ', Strings::pad('hello', 10));
        Assert::same('hello-----', Strings::pad('hello', 10, '-'));
    }

    public function testPadBoth(): void
    {
        Assert::same('--hello---', Strings::pad('hello', 10, '-', STR_PAD_BOTH));
    }

    public function testPadBothDirect(): void
    {
        Assert::same('---hello---', Strings::padBoth('hello', 11, '-'));
        Assert::same('   hello   ', Strings::padBoth('hello', 11));
    }

    public function testPadBothNoOpWhenAlreadyLongEnough(): void
    {
        Assert::same('hello', Strings::padBoth('hello', 3));
    }

    public function testPadBothOddPaddingGoesToRight(): void
    {
        Assert::same('--hello---', Strings::padBoth('hello', 10, '-'));
    }

    public function testPadLeft(): void
    {
        Assert::same('     hello', Strings::pad('hello', 10, ' ', STR_PAD_LEFT));
        Assert::same('-----hello', Strings::pad('hello', 10, '-', STR_PAD_LEFT));
    }

    public function testPadLeftDirect(): void
    {
        Assert::same('     hello', Strings::padLeft('hello', 10));
        Assert::same('00005', Strings::padLeft('5', 5, '0'));
    }

    public function testPadLeftNoOpWhenAlreadyLongEnough(): void
    {
        Assert::same('hello', Strings::padLeft('hello', 3));
    }

    public function testPadNoOpWhenAlreadyLongEnough(): void
    {
        Assert::same('hello', Strings::pad('hello', 3));
        Assert::same('hello', Strings::pad('hello', 5));
    }

    public function testPadRightDirect(): void
    {
        Assert::same('hello     ', Strings::padRight('hello', 10));
        Assert::same('hello-----', Strings::padRight('hello', 10, '-'));
    }

    public function testPadRightNoOpWhenAlreadyLongEnough(): void
    {
        Assert::same('hello', Strings::padRight('hello', 3));
    }

    public function testPascal(): void
    {
        Assert::same('HelloWorld', Strings::pascal('hello world'));
        Assert::same('HelloWorld', Strings::pascal('hello_world'));
        Assert::same('HelloWorld', Strings::pascal('hello-world'));
        Assert::same('HelloWorld', Strings::pascal('helloWorld'));
        Assert::same('UserProfileData', Strings::pascal('user_profile_data'));
    }

    public function testPascalCamelOpposites(): void
    {
        $pascal = 'HelloWorld';
        $camel = Strings::camel($pascal);
        Assert::same('helloWorld', $camel);
        Assert::same($pascal, Strings::pascal($camel));
    }

    public function testPascalEmpty(): void
    {
        Assert::same('', Strings::pascal(''));
    }

    public function testPosition(): void
    {
        Assert::same(6, Strings::position('hello world', 'world'));
        Assert::same(0, Strings::position('hello world', 'hello'));
        Assert::false(Strings::position('hello world', 'xyz'));
    }

    public function testPositionLastPositionOpposites(): void
    {
        $string = 'hello world hello';
        Assert::same(0, Strings::position($string, 'hello'));
        Assert::same(12, Strings::lastPosition($string, 'hello'));
    }

    public function testPositionMultibyte(): void
    {
        Assert::same(0, Strings::position('ñaño', 'ñ'));
        Assert::same(2, Strings::position('ñaño', 'ñ', 1));
    }

    public function testPositionWithOffset(): void
    {
        Assert::same(6, Strings::position('hello hello', 'hello', 3));
    }

    public function testRandom(): void
    {
        $result = Strings::random(16);
        Assert::same(16, strlen($result));
        Assert::true(Strings::isAlphanumeric($result));
    }

    public function testRandomCustomLength(): void
    {
        Assert::same(8, strlen(Strings::random(8)));
        Assert::same(32, strlen(Strings::random(32)));
    }

    public function testRandomDefaultLength(): void
    {
        Assert::same(16, strlen(Strings::random()));
    }

    public function testRandomNegativeLengthThrows(): void
    {
        Assert::throws(
            static fn () => Strings::random(-5),
            \Phuture\Coherence\Exception\InvalidArgumentException::class
        );
    }

    public function testRandomProducesUniqueValues(): void
    {
        Assert::notSame(Strings::random(32), Strings::random(32));
    }

    public function testRandomZeroLengthThrows(): void
    {
        Assert::throws(
            static fn () => Strings::random(0),
            \Phuture\Coherence\Exception\InvalidArgumentException::class
        );
    }

    public function testRemove(): void
    {
        Assert::same('hell wrld', Strings::remove('hello world', 'o'));
        Assert::same('hllo world', Strings::remove('hello world', 'e'));
    }

    public function testRemoveCaseInsensitive(): void
    {
        Assert::same('Hello ', Strings::remove('Hello World', 'world', false));
        Assert::same('Hello World', Strings::remove('Hello World', 'world', true));
    }

    public function testRemoveEmpty(): void
    {
        Assert::same('', Strings::remove('', 'x'));
    }

    public function testRemoveNotFound(): void
    {
        Assert::same('hello', Strings::remove('hello', 'xyz'));
    }

    public function testRepeat(): void
    {
        Assert::same('ababab', Strings::repeat('ab', 3));
        Assert::same('hello', Strings::repeat('hello', 1));
    }

    public function testRepeatEmptyString(): void
    {
        Assert::same('', Strings::repeat('', 5));
    }

    public function testRepeatNegativeThrows(): void
    {
        Assert::exception(function () {
            Strings::repeat('ab', -1);
        }, \Phuture\Coherence\Exception\InvalidArgumentException::class);
    }

    public function testRepeatZero(): void
    {
        Assert::same('', Strings::repeat('ab', 0));
    }

    public function testReplace(): void
    {
        Assert::same('hello PHP', Strings::replace('hello world', 'world', 'PHP'));
        Assert::same('hell wrld', Strings::replace('hello world', 'o', ''));
        Assert::same('Hello PHP', Strings::replace('Hello World', 'World', 'PHP'));
    }

    public function testReplaceArray(): void
    {
        Assert::same('Year: 2026, Month: April', Strings::replaceArray('?', ['2026', 'April'], 'Year: ?, Month: ?'));
        Assert::same('a b c', Strings::replaceArray('?', ['a', 'b', 'c'], '? ? ?'));
    }

    public function testReplaceArrayEmptyReplacements(): void
    {
        Assert::same('hello ?', Strings::replaceArray('?', [], 'hello ?'));
    }

    public function testReplaceArrayEmptySearch(): void
    {
        Assert::same('hello', Strings::replaceArray('', ['world'], 'hello'));
    }

    public function testReplaceArrayFewerReplacementsThanOccurrences(): void
    {
        Assert::same('Year: 2026, Month: ?', Strings::replaceArray('?', ['2026'], 'Year: ?, Month: ?'));
    }

    public function testReplaceAt(): void
    {
        Assert::same('hello PHP', Strings::replaceAt('hello world', 'PHP', 6));
        Assert::same('hello PHP', Strings::replaceAt('hello world', 'PHP', 6, 5));
        Assert::same('hello', Strings::replaceAt('hello world', '', 5, 6));
    }

    public function testReplaceAtMultibyte(): void
    {
        Assert::same('héllo PHP', Strings::replaceAt('héllo monde', 'PHP', 6));
    }

    public function testReplaceAtNegativePosition(): void
    {
        Assert::same('hello PHP', Strings::replaceAt('hello world', 'PHP', -5));
    }

    public function testReplaceCaseInsensitive(): void
    {
        Assert::same('Hello PHP', Strings::replace('Hello World', 'world', 'PHP', false));
        Assert::same('Hello World', Strings::replace('Hello World', 'world', 'PHP', true));
    }

    public function testReplaceCaseInsensitiveMultibyte(): void
    {
        Assert::same('xBER', Strings::replace('ÜBER', 'ü', 'x', false));
        Assert::same('x wörld', Strings::replace('héllo wörld', 'HÉLLO', 'x', false));
        Assert::same('hello world', Strings::replace('hello world', 'HELLO', 'hello', true));
    }

    public function testReplaceEmptySearch(): void
    {
        Assert::same('hello', Strings::replace('hello', '', 'x'));
    }

    public function testReplaceFirst(): void
    {
        Assert::same('world hello', Strings::replaceFirst('hello hello', 'hello', 'world'));
        Assert::same('hello', Strings::replaceFirst('hello', 'xyz', 'world'));
    }

    public function testReplaceFirstEmptySearch(): void
    {
        Assert::same('hello', Strings::replaceFirst('hello', '', 'x'));
    }

    public function testReplaceFirstMultibyte(): void
    {
        Assert::same('Xaño', Strings::replaceFirst('ñaño', 'ñ', 'X'));
    }

    public function testReplaceFirstReplaceLastOpposites(): void
    {
        Assert::same('world hello', Strings::replaceFirst('hello hello', 'hello', 'world'));
        Assert::same('hello world', Strings::replaceLast('hello hello', 'hello', 'world'));
    }

    public function testReplaceLast(): void
    {
        Assert::same('hello world', Strings::replaceLast('hello hello', 'hello', 'world'));
        Assert::same('hello', Strings::replaceLast('hello', 'xyz', 'world'));
    }

    public function testReplaceLastEmptySearch(): void
    {
        Assert::same('hello', Strings::replaceLast('hello', '', 'x'));
    }

    public function testReplaceLastMultibyte(): void
    {
        Assert::same('ñañX', Strings::replaceLast('ñaño', 'o', 'X'));
    }

    public function testReplaceMultibyte(): void
    {
        Assert::same('hXllo', Strings::replace('héllo', 'é', 'X'));
        Assert::same('nan', Strings::replace('ñañ', 'ñ', 'n'));
    }

    public function testReverse(): void
    {
        Assert::same('olleh', Strings::reverse('hello'));
        Assert::same('dlrow olleh', Strings::reverse('hello world'));
    }

    public function testReverseEmpty(): void
    {
        Assert::same('', Strings::reverse(''));
    }

    public function testReverseMultibyte(): void
    {
        Assert::same('oñañ', Strings::reverse('ñaño'));
        Assert::same('好你', Strings::reverse('你好'));
    }

    public function testReverseRoundTrip(): void
    {
        $original = 'hello world';
        Assert::same($original, Strings::reverse(Strings::reverse($original)));
    }

    public function testScrub(): void
    {
        Assert::same('helloworld', Strings::scrub("hello\x00world"));
        Assert::same('text[0m', Strings::scrub("text\x1B[0m")); // ESC char removed, but [0m remains
        Assert::same('clean text', Strings::scrub('clean text'));
    }

    public function testScrubEmpty(): void
    {
        Assert::same('', Strings::scrub(''));
    }

    public function testSlice(): void
    {
        Assert::same('hello', Strings::slice('hello world', 0, 5));
        Assert::same('world', Strings::slice('hello world', 6));
        Assert::same('world', Strings::slice('hello world', -5));
        Assert::same('añ', Strings::slice('ñaño', 1, 2));
    }

    public function testSliceEmpty(): void
    {
        Assert::same('', Strings::slice('', 0, 5));
    }

    public function testSliceNegativeStart(): void
    {
        Assert::same('world', Strings::slice('hello world', -5));
        Assert::same('el', Strings::slice('hello', -4, 2));
    }

    public function testSlug(): void
    {
        Assert::same('hello-world', Strings::slug('Hello World'));
        Assert::same('hello-world', Strings::slug('hello world'));
        Assert::same('hello-world', Strings::slug('héllo wörld'));
    }

    public function testSlugCustomSeparator(): void
    {
        Assert::same('hello_world', Strings::slug('Hello World', '_'));
    }

    public function testSlugEmpty(): void
    {
        Assert::same('', Strings::slug(''));
    }

    public function testSlugMultipleSpaces(): void
    {
        Assert::same('hello-world-foo', Strings::slug('Hello  World  Foo'));
    }

    public function testSnake(): void
    {
        Assert::same('hello_world', Strings::snake('helloWorld'));
        Assert::same('hello_world', Strings::snake('HelloWorld'));
        Assert::same('hello_world', Strings::snake('hello-world'));
        Assert::same('hello_world', Strings::snake('hello world'));
        Assert::same('user_profile_data', Strings::snake('UserProfileData'));
    }

    public function testSnakeCamelOpposites(): void
    {
        $original = 'hello_world';
        Assert::same($original, Strings::snake(Strings::camel($original)));
    }

    public function testSnakeCustomDelimiter(): void
    {
        Assert::same('hello-world', Strings::snake('helloWorld', '-'));
        Assert::same('hello.world', Strings::snake('helloWorld', '.'));
    }

    public function testSnakeEmpty(): void
    {
        Assert::same('', Strings::snake(''));
    }

    public function testSplit(): void
    {
        Assert::same(['a', 'b', 'c'], Strings::split('a.b.c', '.'));
        Assert::same(['hello', 'world'], Strings::split('hello world', ' '));
    }

    public function testSplitEmptyPattern(): void
    {
        Assert::same(['a.b.c'], Strings::split('a.b.c', ''));
    }

    public function testSplitMultibyte(): void
    {
        Assert::same(['ña', 'ño'], Strings::split('ña ño', ' '));
    }

    public function testSplitWithLimit(): void
    {
        Assert::same(['a', 'b.c'], Strings::split('a.b.c', '.', 2));
    }

    public function testSquish(): void
    {
        Assert::same('hello world', Strings::squish('hello    world'));
        Assert::same('hello world', Strings::squish("  hello   \n   world  "));
        Assert::same('a b c', Strings::squish("a\t\tb\n\nc"));
        Assert::same('hello', Strings::squish('  hello  '));
    }

    public function testSquishEmpty(): void
    {
        Assert::same('', Strings::squish(''));
        Assert::same('', Strings::squish('   '));
    }

    public function testStart(): void
    {
        Assert::same('/path/to', Strings::start('/path/to', '/'));
        Assert::same('/path/to', Strings::start('path/to', '/'));
        Assert::same('/path/to', Strings::start('///path/to', '/'));
    }

    public function testStartEmptySuffix(): void
    {
        Assert::same('hello', Strings::start('hello', ''));
    }

    public function testStartFinishOpposites(): void
    {
        Assert::same('/path', Strings::start('path', '/'));
        Assert::same('path/', Strings::finish('path', '/'));
    }

    public function testStartsWith(): void
    {
        Assert::true(Strings::startsWith('hello world', 'hello'));
        Assert::true(Strings::startsWith('hello', 'hello'));
        Assert::true(Strings::startsWith('https://example.com', 'https://'));
        Assert::false(Strings::startsWith('hello world', 'world'));
        Assert::false(Strings::startsWith('hello', 'Hello'));
    }

    public function testStartsWithEmptySearch(): void
    {
        Assert::true(Strings::startsWith('hello', ''));
        Assert::true(Strings::startsWith('', ''));
    }

    public function testStartsWithEndsWithOpposites(): void
    {
        $string = 'hello world';
        Assert::true(Strings::startsWith($string, 'hello'));
        Assert::false(Strings::endsWith($string, 'hello'));
        Assert::false(Strings::startsWith($string, 'world'));
        Assert::true(Strings::endsWith($string, 'world'));
    }

    public function testStrip(): void
    {
        Assert::same('Hello world', Strings::strip('<p>Hello <b>world</b></p>'));
        Assert::same('HelloWorld', Strings::strip('<p>Hello</p><p>World</p>'));
        Assert::same('<a href="#">Link</a>', Strings::strip('<a href="#">Link</a>', '<a>'));
        Assert::same('<p>Hello there</p>', Strings::strip('<p>Hello <span class="red">there</span></p>', '<p>'));
    }

    public function testStripEmpty(): void
    {
        Assert::same('', Strings::strip(''));
    }

    public function testSwap(): void
    {
        Assert::same('hi earth', Strings::swap('hello world', ['hello' => 'hi', 'world' => 'earth']));
        Assert::same('hello world', Strings::swap('foo bar', ['foo' => 'hello', 'bar' => 'world']));
    }

    public function testSwapChainedReplacementDoesNotCorrupt(): void
    {
        Assert::same('world', Strings::swap('hello', ['hello' => 'world', 'world' => 'hi']));
        Assert::same('world hi', Strings::swap('hello world', ['hello' => 'world', 'world' => 'hi']));
        Assert::same('b c', Strings::swap('a b', ['a' => 'b', 'b' => 'c']));
    }

    public function testSwapEmptyReplacements(): void
    {
        Assert::same('hello', Strings::swap('hello', []));
    }

    public function testSwapMultibyte(): void
    {
        Assert::same('nano', Strings::swap('ñaño', ['ñ' => 'n', 'o' => 'o']));
    }

    public function testTake(): void
    {
        Assert::same('hello', Strings::take('hello world', 5));
        Assert::same('hello', Strings::take('hello', 10));
        Assert::same('ña', Strings::take('ñaño', 2));
    }

    public function testTakeEmpty(): void
    {
        Assert::same('', Strings::take('', 5));
    }

    public function testTakeNegativeCount(): void
    {
        Assert::same('world', Strings::take('hello world', -5));
        Assert::same('ño', Strings::take('ñaño', -2));
    }

    public function testTakeZero(): void
    {
        Assert::same('', Strings::take('hello', 0));
    }

    public function testTitle(): void
    {
        Assert::same('Hello World', Strings::title('hello world'));
        Assert::same('Hello World', Strings::title('HELLO WORLD'));
        Assert::same('Ñaño Ñoño', Strings::title('ñaño ñoño'));
        Assert::same('Hello', Strings::title('hello'));
    }

    public function testTitleEmpty(): void
    {
        Assert::same('', Strings::title(''));
    }

    public function testToArray(): void
    {
        Assert::same(['h', 'e', 'l', 'l', 'o'], Strings::toArray('hello'));
    }

    public function testToArrayEmpty(): void
    {
        Assert::same([], Strings::toArray(''));
    }

    public function testToArrayMultibyte(): void
    {
        Assert::same(['ñ', 'a', 'ñ', 'o'], Strings::toArray('ñaño'));
    }

    public function testToBase64(): void
    {
        Assert::same('aGVsbG8=', Strings::toBase64('hello'));
        Assert::same('', Strings::toBase64(''));
    }

    public function testToBase64FromBase64RoundTrip(): void
    {
        $original = 'Hello World!';
        Assert::same($original, Strings::fromBase64(Strings::toBase64($original)));
    }

    public function testTrim(): void
    {
        Assert::same('hello world', Strings::trim('  hello world  '));
        Assert::same('hello', Strings::trim('  hello  '));
        Assert::same("hello", Strings::trim("\thello\t"));
        Assert::same("hello", Strings::trim("\nhello\n"));
    }

    public function testTrimCustomChars(): void
    {
        Assert::same('hello', Strings::trim('***hello***', '*'));
        Assert::same('hello', Strings::trim('xxxhelloxxx', 'x'));
        Assert::same('hello', Strings::trim('---hello---', '-'));
    }

    public function testTrimEmpty(): void
    {
        Assert::same('', Strings::trim(''));
        Assert::same('', Strings::trim('   '));
    }

    public function testTrimLeft(): void
    {
        Assert::same('hello world  ', Strings::trimLeft('  hello world  '));
        Assert::same('hello  ', Strings::trimLeft('  hello  '));
        Assert::same("hello\t", Strings::trimLeft("\thello\t"));
    }

    public function testTrimLeftCustomChars(): void
    {
        Assert::same('hello***', Strings::trimLeft('***hello***', '*'));
        Assert::same('helloxxx', Strings::trimLeft('xxxhelloxxx', 'x'));
    }

    public function testTrimLeftTrimRightOpposites(): void
    {
        $string = '  hello  ';
        Assert::same('hello', Strings::trim($string));
        Assert::same('hello  ', Strings::trimLeft($string));
        Assert::same('  hello', Strings::trimRight($string));
    }

    public function testTrimRight(): void
    {
        Assert::same('  hello world', Strings::trimRight('  hello world  '));
        Assert::same('  hello', Strings::trimRight('  hello  '));
        Assert::same("\thello", Strings::trimRight("\thello\t"));
    }

    public function testTrimRightCustomChars(): void
    {
        Assert::same('***hello', Strings::trimRight('***hello***', '*'));
        Assert::same('xxxhello', Strings::trimRight('xxxhelloxxx', 'x'));
    }

    public function testTruncate(): void
    {
        Assert::same('Hello', Strings::truncate('Hello World', 5));
        Assert::same('Hi', Strings::truncate('Hi', 5));
    }

    public function testTruncateMultibyte(): void
    {
        Assert::same('ña', Strings::truncate('ñaño', 2));
    }

    public function testTruncateNegativeThrows(): void
    {
        Assert::throws(
            static fn () => Strings::truncate('hello', -1),
            \Phuture\Coherence\Exception\InvalidArgumentException::class
        );
    }

    public function testTruncateNoOpWhenShort(): void
    {
        Assert::same('Hi', Strings::truncate('Hi', 10));
        Assert::same('Hello', Strings::truncate('Hello', 5));
    }

    public function testTruncateWithEnd(): void
    {
        Assert::same('Hello…', Strings::truncate('Hello World', 5, '…'));
    }

    public function testUnwrap(): void
    {
        Assert::same('hello', Strings::unwrap('"hello"', '"'));
        Assert::same('hello', Strings::unwrap('***hello***', '***'));
    }

    public function testUnwrapEmptyWrapper(): void
    {
        Assert::same('"hello"', Strings::unwrap('"hello"', ''));
    }

    public function testUnwrapMissingEnd(): void
    {
        Assert::same('[hello]', Strings::unwrap('[hello]', '['));
    }

    public function testUnwrapWhenNotWrapped(): void
    {
        Assert::same('hello', Strings::unwrap('hello', '"'));
    }

    public function testUpper(): void
    {
        Assert::same('HELLO WORLD', Strings::upper('hello world'));
        Assert::same('HELLO WORLD', Strings::upper('Hello World'));
        Assert::same('ÑOÑO', Strings::upper('ñoño'));
        Assert::same('ÄÖÜ', Strings::upper('äöü'));
        Assert::same('ALREADY UPPER', Strings::upper('ALREADY UPPER'));
    }

    public function testUpperEmpty(): void
    {
        Assert::same('', Strings::upper(''));
    }

    public function testUuid(): void
    {
        $uuid = Strings::uuid();
        Assert::same(36, Strings::length($uuid));
        Assert::true(Strings::isUuid($uuid));
    }

    public function testUuidIsValidV4(): void
    {
        // Version 4 UUID has '4' at position 14 and [89ab] at position 19
        $uuid = Strings::uuid();
        Assert::same('4', Strings::charAt($uuid, 14));
        Assert::true(Strings::has('89ab', Strings::charAt($uuid, 19)));
    }

    public function testUuidProducesUniqueValues(): void
    {
        Assert::notSame(Strings::uuid(), Strings::uuid());
    }

    public function testWordCount(): void
    {
        Assert::same(2, Strings::wordCount('hello world'));
        Assert::same(1, Strings::wordCount('hello'));
        Assert::same(3, Strings::wordCount('a b c'));
    }

    public function testWordCountEmptyAndBlank(): void
    {
        Assert::same(0, Strings::wordCount(''));
        Assert::same(0, Strings::wordCount('   '));
    }

    public function testWordCountMultibyte(): void
    {
        Assert::same(2, Strings::wordCount('ñaño mundo'));
    }

    public function testWords(): void
    {
        Assert::same(['hello', 'world'], Strings::words('hello world'));
        Assert::same(["it's", 'a', 'test'], Strings::words("it's a test"));
    }

    public function testWordsEmptyAndBlank(): void
    {
        Assert::same([], Strings::words(''));
        Assert::same([], Strings::words('   '));
    }

    public function testWordsWithLimit(): void
    {
        Assert::same(['hello', 'world'], Strings::words('hello world foo', 2));
    }

    public function testWordsWithLimitAndEnd(): void
    {
        Assert::same(['hello', 'world', '…'], Strings::words('hello world foo', 2, '…'));
    }

    public function testWordsWordCountConsistency(): void
    {
        $string = 'hello world foo bar';
        Assert::same(count(Strings::words($string)), Strings::wordCount($string));
    }

    public function testWordWrap(): void
    {
        Assert::same("The quick\nbrown fox", Strings::wordWrap('The quick brown fox', 10));
    }

    public function testWordWrapCustomBreak(): void
    {
        Assert::same("The quick<br>brown fox", Strings::wordWrap('The quick brown fox', 10, '<br>'));
    }

    public function testWordWrapCutLongWords(): void
    {
        Assert::same("superlongw\nord", Strings::wordWrap('superlongword', 10, "\n", true));
    }

    public function testWordWrapMultibyte(): void
    {
        Assert::same("héllo\nwörld\ntesting", Strings::wordWrap('héllo wörld testing', 8));
        Assert::same("héllo wörld", Strings::wordWrap('héllo wörld', 75));
    }

    public function testWordWrapMultibyteCutLongWords(): void
    {
        Assert::same("héllo\nwörld", Strings::wordWrap('héllowörld', 5, "\n", true));
    }

    public function testWordWrapShortString(): void
    {
        Assert::same('Hi', Strings::wordWrap('Hi', 75));
    }

    public function testWordWrapZeroWidthThrows(): void
    {
        Assert::throws(
            static fn () => Strings::wordWrap('hello', 0),
            \Phuture\Coherence\Exception\InvalidArgumentException::class
        );
    }

    public function testWrap(): void
    {
        Assert::same('"hello"', Strings::wrap('hello', '"'));
        Assert::same('[]hello[]', Strings::wrap('hello', '[]'));
    }

    public function testWrapEmpty(): void
    {
        Assert::same('""', Strings::wrap('', '"'));
    }

    public function testWrapUnwrapRoundTrip(): void
    {
        $original = 'hello';
        Assert::same($original, Strings::unwrap(Strings::wrap($original, '"'), '"'));
    }
}

// Run the tests
(new StringsTest())->run();
