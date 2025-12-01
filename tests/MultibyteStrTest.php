<?php

declare(strict_types=1);

namespace Advandz\Kernel\Tests;

use Advandz\Kernel\MultibyteStr;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

/**
 * Test case for the MultibyteStr utility class.
 */
class MultibyteStrTest extends TestCase
{
    public function testStr(): void
    {
        $subject = 'Привет мир';
        Assert::equal(mb_strstr($subject, 'мир'), MultibyteStr::str($subject, 'мир'));
    }

    public function testIstr(): void
    {
        $subject = 'Привет Мир';
        Assert::equal(mb_stristr($subject, 'мир'), MultibyteStr::istr($subject, 'мир'));
    }

    public function testContains(): void
    {
        $subject = 'こんにちは世界';
        $expected = mb_strpos($subject, '世界', 0) !== false;
        Assert::equal($expected, MultibyteStr::contains($subject, '世界'));
    }

    public function testIcontains(): void
    {
        $subject = 'Привет Мир';
        $expected = mb_stripos($subject, 'мир', 0) !== false;
        Assert::equal($expected, MultibyteStr::icontains($subject, 'мир'));
    }

    public function testPos(): void
    {
        $subject = 'Héllo Wörld';
        Assert::equal(mb_strpos($subject, 'Wörld'), MultibyteStr::pos($subject, 'Wörld'));
    }

    public function testIpos(): void
    {
        $subject = 'Héllo Wörld';
        Assert::equal(mb_stripos($subject, 'wörld'), MultibyteStr::ipos($subject, 'wörld'));
    }

    public function testLastPos(): void
    {
        $subject = 'Привет мир мир';
        Assert::equal(mb_strrpos($subject, 'мир'), MultibyteStr::lastPos($subject, 'мир'));
    }

    public function testLastIpos(): void
    {
        $subject = 'Привет Мир мир';
        Assert::equal(mb_strripos($subject, 'мир'), MultibyteStr::lastIpos($subject, 'мир'));
    }

    public function testLen(): void
    {
        $subject = 'こんにちは';
        Assert::equal(mb_strlen($subject), MultibyteStr::len($subject));
    }

    public function testLower(): void
    {
        $subject = 'ПРИВЕТ МИР';
        Assert::equal(mb_strtolower($subject), MultibyteStr::lower($subject));
    }

    public function testUpper(): void
    {
        $subject = 'привет мир';
        Assert::equal(mb_strtoupper($subject), MultibyteStr::upper($subject));
    }

    public function testUpperFirst(): void
    {
        $subject = 'привет мир';
        $expected = 'Привет мир';
        Assert::equal($expected, MultibyteStr::upperFirst($subject));
    }

    public function testUpperWords(): void
    {
        $subject = 'привет мир';
        Assert::equal(mb_convert_case($subject, MB_CASE_TITLE), MultibyteStr::upperWords($subject));
    }

    public function testLowerFirst(): void
    {
        $subject = 'ПРИВЕТ МИР';
        $expected = 'пРИВЕТ МИР';
        Assert::equal($expected, MultibyteStr::lowerFirst($subject));
    }

    public function testLowerWords(): void
    {
        $subject = 'Привет Мир';
        $expected = 'привет мир';
        Assert::equal($expected, MultibyteStr::lowerWords($subject));
    }

    public function testParse(): void
    {
        $subject = 'имя=Иван&возраст=30';
        $result1 = [];
        $result2 = [];
        mb_parse_str($subject, $result1);
        MultibyteStr::parse($subject, $result2);
        Assert::equal($result1, $result2);
    }

    public function testSub(): void
    {
        $subject = 'こんにちは世界';
        Assert::equal(mb_substr($subject, 0, 5), MultibyteStr::sub($subject, 0, 5));
    }

    public function testCount(): void
    {
        $subject = 'Привет мир мир';
        Assert::equal(mb_substr_count($subject, 'мир'), MultibyteStr::count($subject, 'мир'));
    }

    public function testLastChr(): void
    {
        $subject = 'Héllo Wörld';
        Assert::equal(mb_strrchr($subject, 'ö'), MultibyteStr::lastChr($subject, 'ö'));
    }

    public function testConvertCase(): void
    {
        $subject = 'привет мир';
        Assert::equal(mb_convert_case($subject, MB_CASE_UPPER), MultibyteStr::convertCase($subject, MB_CASE_UPPER));
    }

    public function testDetectEncoding(): void
    {
        $subject = 'こんにちは';
        Assert::equal(mb_detect_encoding($subject), MultibyteStr::detectEncoding($subject));
    }

    public function testConvertEncoding(): void
    {
        $subject = 'Привет';
        Assert::equal(mb_convert_encoding($subject, 'UTF-8'), MultibyteStr::convertEncoding($subject, 'UTF-8'));
    }

    public function testChr(): void
    {
        Assert::equal(mb_chr(12371), MultibyteStr::chr(12371)); // Unicode for 'こ'
    }

    public function testOrd(): void
    {
        $subject = 'こ';
        Assert::equal(mb_ord($subject), MultibyteStr::ord($subject));
    }

    public function testScrub(): void
    {
        $subject = 'Привет мир';
        Assert::equal(mb_scrub($subject), MultibyteStr::scrub($subject));
    }

    public function testWidth(): void
    {
        $subject = 'こんにちは';
        Assert::equal(mb_strwidth($subject), MultibyteStr::width($subject));
    }

    public function testStrimwidth(): void
    {
        $subject = 'こんにちは世界';
        $expected = mb_strimwidth($subject, 0, 8, '...');
        Assert::equal($expected, MultibyteStr::strimwidth($subject, 0, 8, '...'));
    }

    public function testCut(): void
    {
        $subject = 'こんにちは世界';
        Assert::equal(mb_strimwidth($subject, 0, 8, '...'), MultibyteStr::cut($subject, 0, 8, '...'));
    }

    public function testReplace(): void
    {
        $subject = 'Привет мир';
        $expected = mb_str_replace('мир', 'PHP', $subject);
        Assert::equal($expected, MultibyteStr::replace('мир', 'PHP', $subject));
    }

    public function testRep(): void
    {
        $subject = 'Привет мир';
        $expected = mb_str_replace('мир', 'PHP', $subject);
        Assert::equal($expected, MultibyteStr::rep($subject, 'мир', 'PHP'));
    }

    public function testIreplace(): void
    {
        $subject = 'Привет Мир';
        $expected = 'Привет PHP';
        Assert::equal($expected, MultibyteStr::ireplace('мир', 'PHP', $subject));
    }

    public function testIrep(): void
    {
        $subject = 'Привет Мир';
        $expected = mb_str_ireplace('мир', 'PHP', $subject);
        Assert::equal($expected, MultibyteStr::irep($subject, 'мир', 'PHP'));
    }
}

// Run the test
(new MultibyteStrTest())->run();
