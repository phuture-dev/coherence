<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Phuture\Coherence\Numbers;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Enum\RoundingMode;
use Phuture\Coherence\Exception\{InvalidArgumentException, LogicException};

require __DIR__ . '/bootstrap.php';

class NumbersTest extends TestCase
{
    public function testAbbreviate(): void
    {
        Assert::same('500.0', Numbers::abbreviate(500));
        Assert::same('1.5K', Numbers::abbreviate(1500));
        Assert::same('1.0M', Numbers::abbreviate(1000000));
        Assert::same('1.0B', Numbers::abbreviate(1000000000));
        Assert::same('1.0T', Numbers::abbreviate(1000000000000));
    }

    public function testAbbreviateNegative(): void
    {
        Assert::same('-1.5K', Numbers::abbreviate(-1500));
    }

    public function testAbbreviateWithPrecision(): void
    {
        Assert::same('1.50K', Numbers::abbreviate(1500, 2));
        Assert::same('123.5M', Numbers::abbreviate(123456789));
    }
    public function testAbsolute(): void
    {
        Assert::same(5, Numbers::absolute(-5));
        Assert::same(3.14, Numbers::absolute(3.14));
        Assert::same(0, Numbers::absolute(0));
        Assert::same(100, Numbers::absolute(-100));
        Assert::same(0.0, Numbers::absolute(-0.0));
    }

    public function testAdd(): void
    {
        Assert::same('0.3000000000', Numbers::add(0.1, 0.2));
        Assert::same('300.0000000000', Numbers::add(100, 200));
        Assert::same('4.0000000000', Numbers::add(1.5, 2.5));
        Assert::same('0.0000000000', Numbers::add(0, 0));
    }

    public function testAddNegativeNumbers(): void
    {
        Assert::same('-3.0000000000', Numbers::add(-1, -2));
        Assert::same('0.0000000000', Numbers::add(-5, 5));
    }

    public function testAreEqual(): void
    {
        Assert::true(Numbers::areEqual(0.1 + 0.2, 0.3));
        Assert::true(Numbers::areEqual(10, 10.0));
        Assert::true(Numbers::areEqual(0.0, 0));
        Assert::false(Numbers::areEqual(1.0, 2.0));
        Assert::false(Numbers::areEqual(0.1 + 0.2, 0.4));
    }

    public function testAreEqualWithNan(): void
    {
        Assert::exception(
            fn () => Numbers::areEqual(NAN, 1.0),
            LogicException::class
        );
        Assert::exception(
            fn () => Numbers::areEqual(1.0, NAN),
            LogicException::class
        );
    }

    public function testArithmeticPrecision(): void
    {
        $result = Numbers::add(0.1, 0.2);
        Assert::same('0.3000000000', $result);

        $result = Numbers::multiply(0.1, 0.2);
        Assert::same('0.0200000000', $result);

        $result = Numbers::subtract(0.3, 0.1);
        Assert::same('0.2000000000', $result);
    }

    public function testCeil(): void
    {
        Assert::same(4.0, Numbers::ceil(3.2));
        Assert::same(-1.0, Numbers::ceil(-1.1));
        Assert::same(5.0, Numbers::ceil(5.0));
        Assert::same(1.0, Numbers::ceil(0.001));
    }

    public function testClamp(): void
    {
        Assert::same(5, Numbers::clamp(5, 1, 10));
        Assert::same(0, Numbers::clamp(-3, 0, 100));
        Assert::same(100, Numbers::clamp(150, 0, 100));
        Assert::same(3.14, Numbers::clamp(3.14, 0.0, 10.0));
    }

    public function testClampAtBoundaries(): void
    {
        Assert::same(0, Numbers::clamp(0, 0, 10));
        Assert::same(10, Numbers::clamp(10, 0, 10));
    }

    public function testClampInvalidRange(): void
    {
        Assert::exception(
            fn () => Numbers::clamp(5, 10, 1),
            InvalidArgumentException::class
        );
    }

    public function testCompare(): void
    {
        Assert::same(-1, Numbers::compare(1.0, 2.0));
        Assert::same(1, Numbers::compare(2.0, 1.0));
        Assert::same(0, Numbers::compare(1.0, 1.0));
        Assert::same(0, Numbers::compare(0.1 + 0.2, 0.3));
    }

    public function testCompareWithNan(): void
    {
        Assert::exception(
            fn () => Numbers::compare(NAN, 1.0),
            LogicException::class
        );
    }

    public function testCompareWithUsort(): void
    {
        $arr = [3.0, 1.0, 2.0];
        usort($arr, [Numbers::class, 'compare']);
        Assert::same([1.0, 2.0, 3.0], $arr);
    }

    public function testDivide(): void
    {
        Assert::same('3.3333333333', Numbers::divide(10, 3));
        Assert::same('25.0000000000', Numbers::divide(100, 4));
        Assert::same('0.3333333333', Numbers::divide(1, 3));
    }

    public function testDivideByZero(): void
    {
        Assert::exception(
            fn () => Numbers::divide(10, 0),
            InvalidArgumentException::class
        );
    }

    public function testFileSize(): void
    {
        Assert::same('500 B', Numbers::fileSize(500));
        Assert::same('1 KB', Numbers::fileSize(1024));
        Assert::same('1 MB', Numbers::fileSize(1048576));
        Assert::same('1 GB', Numbers::fileSize(1073741824));
    }

    public function testFileSizeWithPrecision(): void
    {
        Assert::same('1.46 KB', Numbers::fileSize(1500, 2));
        Assert::same('1.50 MB', Numbers::fileSize(1572864, 2));
    }

    public function testFileSizeZeroBytes(): void
    {
        Assert::same('0 B', Numbers::fileSize(0));
    }

    public function testFloatComparisonPrecision(): void
    {
        $a = 0.1 + 0.2;
        $b = 0.3;

        Assert::true(Numbers::areEqual($a, $b));
        Assert::false(Numbers::isGreaterThan($a, $b));
        Assert::false(Numbers::isLessThan($a, $b));
        Assert::true(Numbers::isGreaterThanOrEqualTo($a, $b));
        Assert::true(Numbers::isLessThanOrEqualTo($a, $b));
    }

    public function testFloor(): void
    {
        Assert::same(3.0, Numbers::floor(3.8));
        Assert::same(-2.0, Numbers::floor(-1.1));
        Assert::same(5.0, Numbers::floor(5.0));
        Assert::same(0.0, Numbers::floor(0.9));
    }

    public function testForHumans(): void
    {
        Assert::same('500.0', Numbers::forHumans(500));
        Assert::same('1.5 thousand', Numbers::forHumans(1500));
        Assert::same('1.0 million', Numbers::forHumans(1000000));
        Assert::same('1.0 billion', Numbers::forHumans(1000000000));
        Assert::same('1.0 trillion', Numbers::forHumans(1000000000000));
    }

    public function testForHumansNegative(): void
    {
        Assert::same('-1.5 thousand', Numbers::forHumans(-1500));
    }

    public function testForHumansWithPrecision(): void
    {
        Assert::same('1.23 thousand', Numbers::forHumans(1234, 2));
    }

    public function testFormat(): void
    {
        Assert::same('1,234,567.89', Numbers::format(1234567.8912, 2));
        Assert::same('1,234,567', Numbers::format(1234567, 0));
        Assert::same('1,234.5678', Numbers::format(1234.5678, 4));
    }

    public function testFormatAutoPrecision(): void
    {
        Assert::same('3.14', Numbers::format(3.14));
        Assert::same('100', Numbers::format(100));
    }

    public function testIsFloat(): void
    {
        Assert::true(Numbers::isFloat(3.14));
        Assert::true(Numbers::isFloat(0.5));
        Assert::true(Numbers::isFloat(-0.1));
        Assert::false(Numbers::isFloat(5));
        Assert::false(Numbers::isFloat(5.0));
        Assert::false(Numbers::isFloat(-3.0));
        Assert::false(Numbers::isFloat(INF));
        Assert::false(Numbers::isFloat(NAN));
    }

    public function testIsGreaterThan(): void
    {
        Assert::true(Numbers::isGreaterThan(10.0, 5.0));
        Assert::false(Numbers::isGreaterThan(5.0, 10.0));
        Assert::false(Numbers::isGreaterThan(10.0, 10.0));
    }

    public function testIsGreaterThanOrEqualTo(): void
    {
        Assert::true(Numbers::isGreaterThanOrEqualTo(10.0, 5.0));
        Assert::true(Numbers::isGreaterThanOrEqualTo(10.0, 10.0));
        Assert::false(Numbers::isGreaterThanOrEqualTo(5.0, 10.0));
    }

    public function testIsGreaterThanWithNan(): void
    {
        Assert::exception(
            fn () => Numbers::isGreaterThan(NAN, 1.0),
            LogicException::class
        );
    }

    public function testIsInteger(): void
    {
        Assert::true(Numbers::isInteger(5));
        Assert::true(Numbers::isInteger(5.0));
        Assert::true(Numbers::isInteger(-3.0));
        Assert::true(Numbers::isInteger(0));
        Assert::false(Numbers::isInteger(3.14));
        Assert::false(Numbers::isInteger(INF));
        Assert::false(Numbers::isInteger(NAN));
    }

    public function testIsLessThan(): void
    {
        Assert::true(Numbers::isLessThan(5.0, 10.0));
        Assert::false(Numbers::isLessThan(10.0, 5.0));
        Assert::false(Numbers::isLessThan(10.0, 10.0));
    }

    public function testIsLessThanOrEqualTo(): void
    {
        Assert::true(Numbers::isLessThanOrEqualTo(5.0, 10.0));
        Assert::true(Numbers::isLessThanOrEqualTo(10.0, 10.0));
        Assert::false(Numbers::isLessThanOrEqualTo(15.0, 10.0));
    }

    public function testIsLessThanWithNan(): void
    {
        Assert::exception(
            fn () => Numbers::isLessThan(NAN, 1.0),
            LogicException::class
        );
    }

    public function testIsNegative(): void
    {
        Assert::true(Numbers::isNegative(-5));
        Assert::true(Numbers::isNegative(-0.1));
        Assert::false(Numbers::isNegative(0));
        Assert::false(Numbers::isNegative(3));
    }

    public function testIsNumber(): void
    {
        Assert::true(Numbers::isNumber(42));
        Assert::true(Numbers::isNumber(3.14));
        Assert::true(Numbers::isNumber('100'));
        Assert::true(Numbers::isNumber('3.14'));
        Assert::true(Numbers::isNumber('-7'));
        Assert::true(Numbers::isNumber('0'));
        Assert::false(Numbers::isNumber('abc'));
        Assert::false(Numbers::isNumber(''));
        Assert::false(Numbers::isNumber(null));
        Assert::false(Numbers::isNumber([]));
    }

    public function testIsPositive(): void
    {
        Assert::true(Numbers::isPositive(5));
        Assert::true(Numbers::isPositive(0.1));
        Assert::false(Numbers::isPositive(0));
        Assert::false(Numbers::isPositive(-3));
    }

    public function testIsZero(): void
    {
        Assert::true(Numbers::isZero(0));
        Assert::true(Numbers::isZero(0.0));
        Assert::false(Numbers::isZero(0.5));
        Assert::false(Numbers::isZero(-0.1));
    }

    public function testMax(): void
    {
        Assert::same(7, Numbers::max(3, 7));
        Assert::same(-2, Numbers::max(-5, -2));
        Assert::same(3.14, Numbers::max(3.14, 2.7));
    }

    public function testMin(): void
    {
        Assert::same(3, Numbers::min(3, 7));
        Assert::same(-5, Numbers::min(-5, -2));
        Assert::same(2.7, Numbers::min(3.14, 2.7));
    }

    public function testModulus(): void
    {
        Assert::same('1', Numbers::modulus(10, 3));
        Assert::same('0', Numbers::modulus(10, 2));
    }

    public function testModulusByZero(): void
    {
        Assert::exception(
            fn () => Numbers::modulus(10, 0),
            InvalidArgumentException::class
        );
    }

    public function testMultiply(): void
    {
        Assert::same('0.0200000000', Numbers::multiply(0.1, 0.2));
        Assert::same('12.0000000000', Numbers::multiply(3, 4));
        Assert::same('10.0000000000', Numbers::multiply(2.5, 4.0));
    }

    public function testOpposite(): void
    {
        Assert::same(-5, Numbers::opposite(5));
        Assert::same(3.2, Numbers::opposite(-3.2));
        Assert::same(0, Numbers::opposite(0));
    }

    public function testOrdinal(): void
    {
        Assert::same('1st', Numbers::ordinal(1));
        Assert::same('2nd', Numbers::ordinal(2));
        Assert::same('3rd', Numbers::ordinal(3));
        Assert::same('4th', Numbers::ordinal(4));
        Assert::same('11th', Numbers::ordinal(11));
        Assert::same('12th', Numbers::ordinal(12));
        Assert::same('13th', Numbers::ordinal(13));
        Assert::same('21st', Numbers::ordinal(21));
        Assert::same('22nd', Numbers::ordinal(22));
        Assert::same('23rd', Numbers::ordinal(23));
        Assert::same('111th', Numbers::ordinal(111));
        Assert::same('112th', Numbers::ordinal(112));
        Assert::same('113th', Numbers::ordinal(113));
    }

    public function testOrdinalNegative(): void
    {
        Assert::same('-1st', Numbers::ordinal(-1));
        Assert::same('-2nd', Numbers::ordinal(-2));
    }

    public function testParseFloat(): void
    {
        Assert::same(3.14, Numbers::parseFloat('3.14'));
        Assert::same(-2.5, Numbers::parseFloat('-2.5'));
        Assert::same(100.0, Numbers::parseFloat(100));
        Assert::same(3.14, Numbers::parseFloat(3.14));
    }

    public function testParseFloatInvalidArgument(): void
    {
        Assert::exception(
            fn () => Numbers::parseFloat('abc'),
            InvalidArgumentException::class
        );
        Assert::exception(
            fn () => Numbers::parseFloat(''),
            InvalidArgumentException::class
        );
        Assert::exception(
            fn () => Numbers::parseFloat(null),
            InvalidArgumentException::class
        );
    }

    public function testParseInt(): void
    {
        Assert::same(42, Numbers::parseInt('42'));
        Assert::same(-7, Numbers::parseInt('-7'));
        Assert::same(3, Numbers::parseInt('3.9'));
        Assert::same(100, Numbers::parseInt(100));
        Assert::same(5, Numbers::parseInt(5.9));
    }

    public function testParseIntInvalidArgument(): void
    {
        Assert::exception(
            fn () => Numbers::parseInt('abc'),
            InvalidArgumentException::class
        );
        Assert::exception(
            fn () => Numbers::parseInt(''),
            InvalidArgumentException::class
        );
        Assert::exception(
            fn () => Numbers::parseInt(null),
            InvalidArgumentException::class
        );
    }

    public function testPercentage(): void
    {
        Assert::same('75.0%', Numbers::percentage(0.75));
        Assert::same('75.00%', Numbers::percentage(0.75, 2));
        Assert::same('150.0%', Numbers::percentage(1.5, 1));
        Assert::same('3%', Numbers::percentage(3, 0, 1));
    }

    public function testRound(): void
    {
        Assert::same(3.46, Numbers::round(3.456, 2));
        Assert::same(3.0, Numbers::round(3.456, 0));
        Assert::same(4.0, Numbers::round(3.5, 0));
    }

    public function testRoundHalfDown(): void
    {
        Assert::same(3.0, Numbers::round(3.5, 0, RoundingMode::HalfDown));
    }

    public function testRoundNegative(): void
    {
        Assert::same(-3.46, Numbers::round(-3.456, 2));
        Assert::same(-3.0, Numbers::round(-3.456, 0));
    }

    public function testSpell(): void
    {
        Assert::same('zero', Numbers::spell(0));
        Assert::same('one', Numbers::spell(1));
        Assert::same('seven', Numbers::spell(7));
        Assert::same('thirteen', Numbers::spell(13));
        Assert::same('twenty', Numbers::spell(20));
        Assert::same('twenty-one', Numbers::spell(21));
        Assert::same('forty-two', Numbers::spell(42));
        Assert::same('one hundred', Numbers::spell(100));
        Assert::same('one hundred one', Numbers::spell(101));
        Assert::same('one hundred twenty-three', Numbers::spell(123));
        Assert::same('one thousand', Numbers::spell(1000));
        Assert::same('one thousand one', Numbers::spell(1001));
        Assert::same('one million', Numbers::spell(1000000));
    }

    public function testSpellLargeNumber(): void
    {
        Assert::same('1000000000', Numbers::spell(1000000000));
    }

    public function testSpellNegative(): void
    {
        Assert::same('negative five', Numbers::spell(-5));
    }

    public function testSquareRoot(): void
    {
        Assert::same('3.0000000000', Numbers::squareRoot(9));
        Assert::same('1.4142', Numbers::squareRoot(2, 4));
        Assert::same('0.0000000000', Numbers::squareRoot(0));
    }

    public function testSquareRootNegative(): void
    {
        Assert::exception(
            fn () => Numbers::squareRoot(-1),
            InvalidArgumentException::class
        );
    }

    public function testSubtract(): void
    {
        Assert::same('7.0000000000', Numbers::subtract(10, 3));
        Assert::same('3.0000000000', Numbers::subtract(5.5, 2.5));
        Assert::same('0.0000000000', Numbers::subtract(1, 1));
    }

    public function testTrimTrailingZeros(): void
    {
        Assert::same('3.14', Numbers::trimTrailingZeros('3.14000'));
        Assert::same('5', Numbers::trimTrailingZeros('5.00'));
        Assert::same('100', Numbers::trimTrailingZeros('100.000'));
    }

    public function testTrimTrailingZerosFromFloat(): void
    {
        Assert::same('7.5', Numbers::trimTrailingZeros(7.5));
    }

    public function testTrimTrailingZerosNoDecimal(): void
    {
        Assert::same('42', Numbers::trimTrailingZeros('42'));
        Assert::same('100', Numbers::trimTrailingZeros(100));
    }

    public function testToNumber(): void
    {
        // int and float pass through unchanged
        Assert::same(42, Numbers::toNumber(42));
        Assert::same(3.14, Numbers::toNumber(3.14));
        Assert::same(0, Numbers::toNumber(0));
        Assert::same(0.0, Numbers::toNumber(0.0));
    }

    public function testToNumberFromBool(): void
    {
        Assert::same(1, Numbers::toNumber(true));
        Assert::same(0, Numbers::toNumber(false));
    }

    public function testToNumberFromString(): void
    {
        Assert::same(3.14, Numbers::toNumber('3.14'));
        Assert::same(0.0, Numbers::toNumber('0'));
        Assert::same(42.0, Numbers::toNumber('42'));
        Assert::same(-7.5, Numbers::toNumber('-7.5'));
    }

    public function testToNumberFromArray(): void
    {
        Assert::same(3, Numbers::toNumber([1, 2, 3]));
        Assert::same(0, Numbers::toNumber([]));
        Assert::same(1, Numbers::toNumber(['only']));
    }
}

(new NumbersTest())->run();
