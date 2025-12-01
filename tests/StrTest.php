<?php

declare(strict_types=1);

namespace Advandz\Kernel\Tests;

use Advandz\Kernel\Str;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

/**
 * Test case for the Str utility class.
 */
class StrTest extends TestCase
{
    public function testStr(): void
    {
        $subject = 'Hello World';
        Assert::equal(strstr($subject, 'World'), Str::str($subject, 'World'));
    }

    public function testIstr(): void
    {
        $subject = 'Hello World';
        Assert::equal(stristr($subject, 'world'), Str::istr($subject, 'world'));
    }

    public function testContains(): void
    {
        $subject = 'Hello World';
        $expected = str_contains($subject, 'World');
        Assert::equal($expected, Str::contains($subject, 'World'));
    }

    public function testIcontains(): void
    {
        $subject = 'Hello World';
        $expected = stripos($subject, 'world') !== false;
        Assert::equal($expected, Str::icontains($subject, 'world'));
    }

    public function testPos(): void
    {
        $subject = 'Hello World';
        Assert::equal(strpos($subject, 'World'), Str::pos($subject, 'World'));
    }

    public function testIpos(): void
    {
        $subject = 'Hello World';
        Assert::equal(stripos($subject, 'world'), Str::ipos($subject, 'world'));
    }

    public function testLastPos(): void
    {
        $subject = 'Hello World World';
        Assert::equal(strrpos($subject, 'World'), Str::lastPos($subject, 'World'));
    }

    public function testLastIpos(): void
    {
        $subject = 'Hello World World';
        Assert::equal(strripos($subject, 'world'), Str::lastIpos($subject, 'world'));
    }

    public function testLen(): void
    {
        $subject = 'Hello World';
        Assert::equal(strlen($subject), Str::len($subject));
    }

    public function testLower(): void
    {
        $subject = 'HELLO WORLD';
        Assert::equal(strtolower($subject), Str::lower($subject));
    }

    public function testUpper(): void
    {
        $subject = 'hello world';
        Assert::equal(strtoupper($subject), Str::upper($subject));
    }

    public function testUpperFirst(): void
    {
        $subject = 'hello world';
        Assert::equal(ucfirst($subject), Str::upperFirst($subject));
    }

    public function testLowerFirst(): void
    {
        $subject = 'HELLO WORLD';
        Assert::equal(lcfirst($subject), Str::lowerFirst($subject));
    }

    public function testUpperWords(): void
    {
        $subject = 'hello world';
        Assert::equal(ucwords($subject), Str::upperWords($subject));
    }

    public function testLowerWords(): void
    {
        $subject = 'Hello World';
        $expected = preg_replace_callback('/\b\w/', fn($m) => strtolower($m[0]), $subject);
        Assert::equal($expected, Str::lowerWords($subject));
    }

    public function testParse(): void
    {
        $subject = 'name=John&age=30';
        $result1 = [];
        $result2 = [];
        parse_str($subject, $result1);
        Str::parse($subject, $result2);
        Assert::equal($result1, $result2);
    }

    public function testSub(): void
    {
        $subject = 'Hello World';
        Assert::equal(substr($subject, 0, 5), Str::sub($subject, 0, 5));
    }

    public function testTrim(): void
    {
        $subject = '  Hello World  ';
        Assert::equal(trim($subject), Str::trim($subject));
    }

    public function testLtrim(): void
    {
        $subject = '  Hello World';
        Assert::equal(ltrim($subject), Str::ltrim($subject));
    }

    public function testRtrim(): void
    {
        $subject = 'Hello World  ';
        Assert::equal(rtrim($subject), Str::rtrim($subject));
    }

    public function testCount(): void
    {
        $subject = 'Hello World World';
        Assert::equal(substr_count($subject, 'World'), Str::count($subject, 'World'));
    }

    public function testRep(): void
    {
        $subject = 'Hello World';
        Assert::equal(str_replace('World', 'PHP', $subject), Str::rep($subject, 'World', 'PHP'));
    }

    public function testIrep(): void
    {
        $subject = 'Hello World';
        Assert::equal(str_ireplace('world', 'PHP', $subject), Str::irep($subject, 'world', 'PHP'));
    }

    public function testReverse(): void
    {
        $subject = 'Hello';
        Assert::equal(strrev($subject), Str::reverse($subject));
    }

    public function testSplit(): void
    {
        $subject = 'Hello,World';
        Assert::equal(explode('Hello,World', ','), Str::split($subject, ','));
    }

    public function testChunkSplit(): void
    {
        $subject = 'HelloWorld';
        Assert::equal(chunk_split($subject, 5), Str::chunkSplit($subject, 5));
    }

    public function testCompare(): void
    {
        Assert::equal(strcmp('abc', 'abc'), Str::compare('abc', 'abc'));
    }

    public function testIcompare(): void
    {
        Assert::equal(strcasecmp('ABC', 'abc'), Str::icompare('ABC', 'abc'));
    }

    public function testNcompare(): void
    {
        Assert::equal(strncmp('abc', 'abd', 2), Str::ncompare('abc', 'abd', 2));
    }

    public function testIncompare(): void
    {
        Assert::equal(strncasecmp('ABC', 'abd', 2), Str::incompare('ABC', 'abd', 2));
    }

    public function testLastChr(): void
    {
        $subject = 'Hello World';
        Assert::equal(strrchr($subject, 'o'), Str::lastChr($subject, 'o'));
    }

    public function testReplaceSub(): void
    {
        $subject = 'Hello World';
        Assert::equal(substr_replace($subject, 'PHP', 6, 5), Str::replaceSub($subject, 'PHP', 6, 5));
    }

    public function testWrap(): void
    {
        $subject = 'Hello World Test';
        Assert::equal(wordwrap($subject, 10), Str::wrap($subject, 10));
    }

    public function testTranslate(): void
    {
        $subject = 'hello';
        Assert::equal(strtr($subject, 'helo', 'HELO'), Str::translate($subject, 'helo', 'HELO'));
    }

    public function testOrd(): void
    {
        $subject = 'A';
        Assert::equal(ord($subject), Str::ord($subject));
    }

    public function testChr(): void
    {
        Assert::equal(chr(65), Str::chr(65));
    }

    public function testNl2br(): void
    {
        $subject = "Hello\nWorld";
        Assert::equal(nl2br($subject), Str::nl2br($subject));
    }

    public function testQuoteMeta(): void
    {
        $subject = 'Hello . World';
        Assert::equal(quotemeta($subject), Str::quoteMeta($subject));
    }

    public function testFormat(): void
    {
        Assert::equal(sprintf('Hello %s', 'World'), Str::format('Hello %s', 'World'));
    }

    public function testSimilar(): void
    {
        $percent1 = 0;
        $percent2 = 0;
        similar_text('Hello', 'Hallo', $percent1);
        Str::similar('Hello', 'Hallo', $percent2);
        Assert::equal($percent1, $percent2);
    }

    public function testLevenshtein(): void
    {
        Assert::equal(levenshtein('Hello', 'Hallo'), Str::levenshtein('Hello', 'Hallo'));
    }

    public function testSoundex(): void
    {
        $subject = 'Hello';
        Assert::equal(soundex($subject), Str::soundex($subject));
    }

    public function testMetaphone(): void
    {
        $subject = 'Hello';
        Assert::equal(metaphone($subject), Str::metaphone($subject));
    }

    public function testLocaleCompare(): void
    {
        Assert::equal(strcoll('a', 'b'), Str::localeCompare('a', 'b'));
    }

    public function testPrintf(): void
    {
        ob_start();
        vprintf('Hello %s', ['World']);
        $expected = ob_get_clean();

        ob_start();
        Str::printf('Hello %s', ['World']);
        $actual = ob_get_clean();

        Assert::equal($expected, $actual);
    }

    public function testFormatSprintf(): void
    {
        Assert::equal(vsprintf('Hello %s', ['World']), Str::formatSprintf('Hello %s', ['World']));
    }

    public function testConvertUuencode(): void
    {
        $subject = 'Hello';
        Assert::equal(convert_uuencode($subject), Str::convertUuencode($subject));
    }

    public function testConvertUudecode(): void
    {
        $subject = convert_uuencode('Hello');
        Assert::equal(convert_uudecode($subject), Str::convertUudecode($subject));
    }

    public function testTok(): void
    {
        $subject = 'Hello World';
        Assert::equal(strtok($subject, ' '), Str::tok($subject, ' '));
    }

    public function testWidth(): void
    {
        $subject = 'Hello';
        Assert::equal(strlen($subject), Str::width($subject));
    }

    public function testCut(): void
    {
        $subject = 'Hello World';
        $expected = substr($subject, 0, 5);
        if (strlen($expected) >= 5 && strlen('...') > 0) {
            $expected = substr($expected, 0, 5 - strlen('...')) . '...';
        }
        Assert::equal($expected, Str::cut($subject, 0, 5, '...'));
    }

    public function testConvertCase(): void
    {
        $subject = 'hello';
        Assert::equal(strtoupper($subject), Str::convertCase($subject, MB_CASE_UPPER));
    }

    public function testDetectEncoding(): void
    {
        $subject = 'Hello World';
        Assert::equal(mb_detect_encoding($subject), Str::detectEncoding($subject));
    }

    public function testConvertEncoding(): void
    {
        $subject = 'Hello';
        Assert::equal(mb_convert_encoding($subject, 'UTF-8'), Str::convertEncoding($subject, 'UTF-8'));
    }

    public function testScrub(): void
    {
        $subject = 'Hello World';
        // For ASCII strings, scrub returns the same value
        Assert::equal($subject, Str::scrub($subject));
    }
}

// Run the test
(new StrTest())->run();
