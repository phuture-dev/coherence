<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Phuture\Coherence\Strings;
use Phuture\Coherence\Type\Strings as FluentStrings;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

/**
 * Comprehensive tests for the Strings helper class.
 *
 * Tests cover all 38 Phase 1 methods including case conversion,
 * checking, trimming & cleaning, and extraction operations.
 */
class StringsTest extends TestCase
{
    // ============================================================
    // CASE CONVERSION TESTS (8 methods)
    // ============================================================

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

    public function testLowerUpperOpposites(): void
    {
        // upper() makes everything uppercase, lower() makes everything lowercase
        // They are opposites in terms of operation, not reversible
        Assert::same('HELLO WORLD', Strings::upper('hello world'));
        Assert::same('hello world', Strings::lower('HELLO WORLD'));
        // Double transformation leads to the last operation applied
        Assert::same('hello world', Strings::lower(Strings::upper('HELLO WORLD')));
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

    public function testLowerFirst(): void
    {
        Assert::same('hello World', Strings::lowerFirst('Hello World'));
        Assert::same('hello', Strings::lowerFirst('Hello'));
        Assert::same('hELLO', Strings::lowerFirst('HELLO'));
        Assert::same('ñoño', Strings::lowerFirst('Ñoño'));
        Assert::same('aBC', Strings::lowerFirst('ABC'));
    }

    public function testLowerFirstEmpty(): void
    {
        Assert::same('', Strings::lowerFirst(''));
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

    public function testSnake(): void
    {
        Assert::same('hello_world', Strings::snake('helloWorld'));
        Assert::same('hello_world', Strings::snake('HelloWorld'));
        Assert::same('hello_world', Strings::snake('hello-world'));
        Assert::same('hello_world', Strings::snake('hello world'));
        Assert::same('user_profile_data', Strings::snake('UserProfileData'));
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

    public function testSnakeCamelOpposites(): void
    {
        $original = 'hello_world';
        Assert::same($original, Strings::snake(Strings::camel($original)));
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

    public function testPascal(): void
    {
        Assert::same('HelloWorld', Strings::pascal('hello world'));
        Assert::same('HelloWorld', Strings::pascal('hello_world'));
        Assert::same('HelloWorld', Strings::pascal('hello-world'));
        Assert::same('HelloWorld', Strings::pascal('helloWorld'));
        Assert::same('UserProfileData', Strings::pascal('user_profile_data'));
    }

    public function testPascalEmpty(): void
    {
        Assert::same('', Strings::pascal(''));
    }

    public function testPascalCamelOpposites(): void
    {
        $pascal = 'HelloWorld';
        $camel = Strings::camel($pascal);
        Assert::same('helloWorld', $camel);
        Assert::same($pascal, Strings::pascal($camel));
    }

    // ============================================================
    // CHECKING TESTS (12 methods)
    // ============================================================

    public function testHas(): void
    {
        Assert::true(Strings::has('hello world', 'world'));
        Assert::true(Strings::has('hello world', 'hello'));
        Assert::true(Strings::has('hello world', 'o w'));
        Assert::false(Strings::has('hello world', 'xyz'));
        Assert::false(Strings::has('hello world', 'World'));
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

    public function testHasHasNoneOpposites(): void
    {
        $search = ['hello', 'world'];
        $string = 'hello world';
        Assert::true(Strings::hasAll($string, $search));
        Assert::false(Strings::hasNone($string, $search));
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

    public function testStartsWithEndsWithOpposites(): void
    {
        $string = 'hello world';
        Assert::true(Strings::startsWith($string, 'hello'));
        Assert::false(Strings::endsWith($string, 'hello'));
        Assert::false(Strings::startsWith($string, 'world'));
        Assert::true(Strings::endsWith($string, 'world'));
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

    public function testIsEmpty(): void
    {
        Assert::true(Strings::isEmpty(''));
        Assert::false(Strings::isEmpty('0'));
        Assert::false(Strings::isEmpty(' '));
        Assert::false(Strings::isEmpty('hello'));
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

    public function testIsFilled(): void
    {
        Assert::true(Strings::isFilled('hello'));
        Assert::true(Strings::isFilled('  hello  '));
        Assert::true(Strings::isFilled('0'));
        Assert::false(Strings::isFilled(''));
        Assert::false(Strings::isFilled('   '));
        Assert::false(Strings::isFilled("\t\n"));
    }

    public function testIsBlankIsFilledOpposites(): void
    {
        Assert::false(Strings::isBlank('hello'));
        Assert::true(Strings::isFilled('hello'));
        Assert::true(Strings::isBlank('  '));
        Assert::false(Strings::isFilled('  '));
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

    public function testMatches(): void
    {
        Assert::true(Strings::matches('user_123', '/^user_\d+$/'));
        Assert::true(Strings::matches('test@example.com', '/^[a-z]+@[a-z]+\.[a-z]+$/'));
        Assert::false(Strings::matches('user_abc', '/^user_\d+$/'));
        Assert::false(Strings::matches('invalid email', '/^[a-z]+@[a-z]+\.[a-z]+$/'));
    }

    // ============================================================
    // TRIMMING & CLEANING TESTS (8 methods)
    // ============================================================

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

    public function testTrimLeftTrimRightOpposites(): void
    {
        $string = '  hello  ';
        Assert::same('hello', Strings::trim($string));
        Assert::same('hello  ', Strings::trimLeft($string));
        Assert::same('  hello', Strings::trimRight($string));
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

    public function testMask(): void
    {
        Assert::same('**********', Strings::mask('1234567890'));
        Assert::same('123*******', Strings::mask('1234567890', '*', 3));
        Assert::same('123****890', Strings::mask('1234567890', '*', 3, 4));
        Assert::same('****@example.com', Strings::mask('john@example.com', '*', 0, 4));
    }

    public function testMaskNegativeOffset(): void
    {
        Assert::same('123456****', Strings::mask('1234567890', '*', -4));
        Assert::same('123456***0', Strings::mask('1234567890', '*', -4, 3));
    }

    public function testMaskEmpty(): void
    {
        Assert::same('', Strings::mask(''));
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

    // ============================================================
    // EXTRACTION TESTS (10 methods)
    // ============================================================

    public function testBefore(): void
    {
        Assert::same('hello', Strings::before('hello world', ' '));
        Assert::same('user', Strings::before('user@example.com', '@'));
        Assert::same('2023', Strings::before('2023-12-25', '-'));
    }

    public function testBeforeNotFound(): void
    {
        Assert::same('hello', Strings::before('hello', 'x'));
        Assert::same('hello world', Strings::before('hello world', 'xyz'));
    }

    public function testBeforeEmptySearch(): void
    {
        Assert::same('hello', Strings::before('hello', ''));
    }

    public function testAfter(): void
    {
        Assert::same('world', Strings::after('hello world', ' '));
        Assert::same('example.com', Strings::after('user@example.com', '@'));
        Assert::same('12-25', Strings::after('2023-12-25', '-'));
    }

    public function testAfterNotFound(): void
    {
        Assert::same('', Strings::after('hello', 'x'));
        Assert::same('', Strings::after('hello world', 'xyz'));
    }

    public function testAfterEmptySearch(): void
    {
        Assert::same('hello', Strings::after('hello', ''));
    }

    public function testBeforeAfterOpposites(): void
    {
        $string = 'hello world';
        $delimiter = ' ';
        Assert::same('hello', Strings::before($string, $delimiter));
        Assert::same('world', Strings::after($string, $delimiter));
    }

    public function testBeforeLast(): void
    {
        Assert::same('hello world', Strings::beforeLast('hello world foo', ' '));
        Assert::same('path/to', Strings::beforeLast('path/to/file.txt', '/'));
        Assert::same('a.b', Strings::beforeLast('a.b.c', '.'));
    }

    public function testBeforeLastNotFound(): void
    {
        Assert::same('hello', Strings::beforeLast('hello', 'x'));
        Assert::same('hello world', Strings::beforeLast('hello world', 'xyz'));
    }

    public function testBeforeLastEmptySearch(): void
    {
        Assert::same('hello', Strings::beforeLast('hello', ''));
    }

    public function testAfterLast(): void
    {
        Assert::same('foo', Strings::afterLast('hello world foo', ' '));
        Assert::same('file.txt', Strings::afterLast('path/to/file.txt', '/'));
        Assert::same('c', Strings::afterLast('a.b.c', '.'));
    }

    public function testAfterLastNotFound(): void
    {
        Assert::same('', Strings::afterLast('hello', 'x'));
        Assert::same('', Strings::afterLast('hello world', 'xyz'));
    }

    public function testAfterLastEmptySearch(): void
    {
        Assert::same('hello', Strings::afterLast('hello', ''));
    }

    public function testBeforeLastAfterLastOpposites(): void
    {
        $string = 'path/to/file.txt';
        $delimiter = '/';
        Assert::same('path/to', Strings::beforeLast($string, $delimiter));
        Assert::same('file.txt', Strings::afterLast($string, $delimiter));
    }

    public function testBetween(): void
    {
        Assert::same('hello', Strings::between('[hello]', '[', ']'));
        Assert::same('example', Strings::between('user@example.com', '@', '.'));
        Assert::same('1b2', Strings::between('a1b2c3', 'a', 'c'));
    }

    public function testBetweenNotFound(): void
    {
        Assert::same('hello', Strings::between('hello', '{', '}'));
        Assert::same('hello', Strings::between('hello', 'x', 'y'));
    }

    public function testBetweenEmptyDelimiters(): void
    {
        Assert::same('hello', Strings::between('hello', '', ''));
    }

    public function testTake(): void
    {
        Assert::same('hello', Strings::take('hello world', 5));
        Assert::same('hello', Strings::take('hello', 10));
        Assert::same('ña', Strings::take('ñaño', 2));
    }

    public function testTakeZero(): void
    {
        Assert::same('', Strings::take('hello', 0));
    }

    public function testTakeEmpty(): void
    {
        Assert::same('', Strings::take('', 5));
    }

    public function testTakeRight(): void
    {
        Assert::same('world', Strings::takeRight('hello world', 5));
        Assert::same('hello', Strings::takeRight('hello', 10));
        Assert::same('ño', Strings::takeRight('ñaño', 2));
    }

    public function testTakeRightZero(): void
    {
        Assert::same('', Strings::takeRight('hello', 0));
    }

    public function testTakeTakeRightOpposites(): void
    {
        $string = 'hello world';
        Assert::same('hello', Strings::take($string, 5));
        Assert::same('world', Strings::takeRight($string, 5));
    }

    public function testSlice(): void
    {
        Assert::same('hello', Strings::slice('hello world', 0, 5));
        Assert::same('world', Strings::slice('hello world', 6));
        Assert::same('world', Strings::slice('hello world', -5));
        Assert::same('añ', Strings::slice('ñaño', 1, 2));
    }

    public function testSliceNegativeStart(): void
    {
        Assert::same('world', Strings::slice('hello world', -5));
        Assert::same('el', Strings::slice('hello', -4, 2));
    }

    public function testSliceEmpty(): void
    {
        Assert::same('', Strings::slice('', 0, 5));
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

    public function testFirstLastOpposites(): void
    {
        $string = 'hello world';
        Assert::same('h', Strings::first($string));
        Assert::same('d', Strings::last($string));
        Assert::same('hello', Strings::first($string, 5));
        Assert::same('world', Strings::last($string, 5));
    }

    // ============================================================
    // LENGTH TEST
    // ============================================================

    public function testLength(): void
    {
        Assert::same(5, Strings::length('hello'));
        Assert::same(4, Strings::length('ñaño'));
        Assert::same(0, Strings::length(''));
        Assert::same(2, Strings::length('你好'));
    }

    // ============================================================
    // FLUENT INTERFACE TESTS
    // ============================================================

    public function testFluentLower(): void
    {
        Assert::same('hello', FluentStrings::from('HELLO')->lower()->get());
    }

    public function testFluentUpper(): void
    {
        Assert::same('HELLO', FluentStrings::from('hello')->upper()->get());
    }

    public function testFluentCapitalize(): void
    {
        Assert::same('Hello World', FluentStrings::from('hello world')->capitalize()->get());
    }

    public function testFluentLowerFirst(): void
    {
        Assert::same('hello World', FluentStrings::from('Hello World')->lowerFirst()->get());
    }

    public function testFluentCamel(): void
    {
        Assert::same('helloWorld', FluentStrings::from('hello_world')->camel()->get());
    }

    public function testFluentSnake(): void
    {
        Assert::same('hello_world', FluentStrings::from('helloWorld')->snake()->get());
    }

    public function testFluentKebab(): void
    {
        Assert::same('hello-world', FluentStrings::from('helloWorld')->kebab()->get());
    }

    public function testFluentPascal(): void
    {
        Assert::same('HelloWorld', FluentStrings::from('hello_world')->pascal()->get());
    }

    public function testFluentHas(): void
    {
        Assert::true(FluentStrings::from('hello world')->has('world')->get());
        Assert::false(FluentStrings::from('hello world')->has('xyz')->get());
    }

    public function testFluentStartsWith(): void
    {
        Assert::true(FluentStrings::from('hello world')->startsWith('hello')->get());
    }

    public function testFluentEndsWith(): void
    {
        Assert::true(FluentStrings::from('hello world')->endsWith('world')->get());
    }

    public function testFluentIsEmpty(): void
    {
        Assert::true(FluentStrings::from('')->isEmpty()->get());
    }

    public function testFluentIsBlank(): void
    {
        Assert::true(FluentStrings::from('   ')->isBlank()->get());
    }

    public function testFluentIsFilled(): void
    {
        Assert::true(FluentStrings::from('hello')->isFilled()->get());
    }

    public function testFluentTrim(): void
    {
        Assert::same('hello', FluentStrings::from('  hello  ')->trim()->get());
    }

    public function testFluentSquish(): void
    {
        Assert::same('hello world', FluentStrings::from('hello    world')->squish()->get());
    }

    public function testFluentBefore(): void
    {
        Assert::same('hello', FluentStrings::from('hello world')->before(' ')->get());
    }

    public function testFluentAfter(): void
    {
        Assert::same('world', FluentStrings::from('hello world')->after(' ')->get());
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

    // ============================================================
    // EDGE CASES
    // ============================================================

    public function testMultibyteCharacters(): void
    {
        Assert::same('ñaño', Strings::lower('ÑAÑO'));
        Assert::same('ÑAÑO', Strings::upper('ñaño'));
        Assert::same(4, Strings::length('ñaño'));
        Assert::same('ña', Strings::take('ñaño', 2));
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
}

// Run the tests
(new StringsTest())->run();
