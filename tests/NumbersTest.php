<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use RoundingMode;
use Phuture\Coherence\Numbers;
use Phuture\Coherence\Enum\Unit;
use Tester\{Assert, TestCase};
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
        Assert::same('4', Numbers::ceil(3.2));
        Assert::same('-1', Numbers::ceil(-1.1));
        Assert::same('5', Numbers::ceil(5.0));
        Assert::same('1', Numbers::ceil(0.001));
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
        Assert::same('3', Numbers::floor(3.8));
        Assert::same('-2', Numbers::floor(-1.1));
        Assert::same('5', Numbers::floor(5.0));
        Assert::same('0', Numbers::floor(0.9));
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
        Assert::same('1.0000000000', Numbers::modulus(10, 3));
        Assert::same('0.0000000000', Numbers::modulus(10, 2));
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

    public function testOfChaining(): void
    {
        $result = Numbers::of(10)
            ->add(5)
            ->multiply(2)
            ->toNumber()
            ->get();

        Assert::same('30.0000000000', $result);
    }

    public function testOfReturnsFluentNumbers(): void
    {
        $result = Numbers::of(10);

        Assert::type(\Phuture\Coherence\Type\Numbers::class, $result);
    }

    public function testOfWithFloat(): void
    {
        $result = Numbers::of(3.14)
            ->floor()
            ->toNumber()
            ->get();

        Assert::same('3.0000000000', $result);
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
        Assert::same('3.46', Numbers::round(3.456, 2));
        Assert::same('3', Numbers::round(3.456, 0));
        Assert::same('4', Numbers::round(3.5, 0));
    }

    public function testRoundHalfEven(): void
    {
        Assert::same('4', Numbers::round(3.5, 0, RoundingMode::HalfEven));
        Assert::same('2', Numbers::round(2.5, 0, RoundingMode::HalfEven));
    }

    public function testRoundHalfOdd(): void
    {
        Assert::same('3', Numbers::round(3.5, 0, RoundingMode::HalfOdd));
        Assert::same('3', Numbers::round(2.5, 0, RoundingMode::HalfOdd));
    }

    public function testRoundHalfTowardsZero(): void
    {
        Assert::same('3', Numbers::round(3.5, 0, RoundingMode::HalfTowardsZero));
        Assert::same('-3', Numbers::round(-3.5, 0, RoundingMode::HalfTowardsZero));
    }

    public function testRoundNegative(): void
    {
        Assert::same('-3.46', Numbers::round(-3.456, 2));
        Assert::same('-3', Numbers::round(-3.456, 0));
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

    public function testToNumber(): void
    {
        Assert::same('42.0000000000', Numbers::toNumber(42));
        Assert::same('3.1400000000', Numbers::toNumber(3.14));
        Assert::same('0.0000000000', Numbers::toNumber(0));
        Assert::same('0.0000000000', Numbers::toNumber(0.0));
    }

    public function testToNumberFromArray(): void
    {
        Assert::same('3.0000000000', Numbers::toNumber([1, 2, 3]));
        Assert::same('0.0000000000', Numbers::toNumber([]));
        Assert::same('1.0000000000', Numbers::toNumber(['only']));
    }

    public function testToNumberFromBool(): void
    {
        Assert::same('1.0000000000', Numbers::toNumber(true));
        Assert::same('0.0000000000', Numbers::toNumber(false));
    }

    public function testToNumberFromString(): void
    {
        Assert::same('3.1400000000', Numbers::toNumber('3.14'));
        Assert::same('0.0000000000', Numbers::toNumber('0'));
        Assert::same('42.0000000000', Numbers::toNumber('42'));
        Assert::same('-7.5000000000', Numbers::toNumber('-7.5'));
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

    public function testConvertSameUnit(): void
    {
        Assert::same('100.0000000000', Numbers::convert(100, Unit::Celsius, Unit::Celsius));
        Assert::same('5.0000000000', Numbers::convert(5, Unit::Meter, Unit::Meter));
    }

    public function testConvertTemperatureCelsiusToFahrenheit(): void
    {
        Assert::same('212.0000000000', Numbers::convert(100, Unit::Celsius, Unit::Fahrenheit));
        Assert::same('32.0000000000', Numbers::convert(0, Unit::Celsius, Unit::Fahrenheit));
        Assert::same('-40.0000000000', Numbers::convert(-40, Unit::Celsius, Unit::Fahrenheit));
    }

    public function testConvertTemperatureFahrenheitToCelsius(): void
    {
        Assert::same('100.0000000000', Numbers::convert(212, Unit::Fahrenheit, Unit::Celsius));
        Assert::same('0.0000000000', Numbers::convert(32, Unit::Fahrenheit, Unit::Celsius));
        Assert::same('-40.0000000000', Numbers::convert(-40, Unit::Fahrenheit, Unit::Celsius));
    }

    public function testConvertTemperatureCelsiusToKelvin(): void
    {
        Assert::same('373.1500000000', Numbers::convert(100, Unit::Celsius, Unit::Kelvin));
        Assert::same('273.1500000000', Numbers::convert(0, Unit::Celsius, Unit::Kelvin));
        Assert::same('0.0000000000', Numbers::convert(-273.15, Unit::Celsius, Unit::Kelvin));
    }

    public function testConvertTemperatureKelvinToCelsius(): void
    {
        Assert::same('100.0000000000', Numbers::convert(373.15, Unit::Kelvin, Unit::Celsius));
        Assert::same('0.0000000000', Numbers::convert(273.15, Unit::Kelvin, Unit::Celsius));
    }

    public function testConvertTemperatureFahrenheitToRankine(): void
    {
        Assert::same('671.6700000000', Numbers::convert(212, Unit::Fahrenheit, Unit::Rankine));
        Assert::same('491.6700000000', Numbers::convert(32, Unit::Fahrenheit, Unit::Rankine));
    }

    public function testConvertTemperatureRoundTrip(): void
    {
        $original = 37.5;
        $kelvin = Numbers::convert($original, Unit::Celsius, Unit::Kelvin);
        $fahrenheit = Numbers::convert($kelvin, Unit::Kelvin, Unit::Fahrenheit);
        $result = Numbers::convert($fahrenheit, Unit::Fahrenheit, Unit::Celsius);
        Assert::same('37.5000000000', $result);
    }

    public function testConvertDistanceKilometerToMile(): void
    {
        $result = Numbers::convert(1, Unit::Kilometer, Unit::Mile);
        Assert::same('0.6213711922', $result);
    }

    public function testConvertDistanceMileToKilometer(): void
    {
        $result = Numbers::convert(1, Unit::Mile, Unit::Kilometer);
        Assert::same('1.6093440000', $result);
    }

    public function testConvertDistanceMeterToFoot(): void
    {
        $result = Numbers::convert(1, Unit::Meter, Unit::Foot);
        Assert::same('3.2808398950', $result);
    }

    public function testConvertDistanceInchToCentimeter(): void
    {
        $result = Numbers::convert(1, Unit::Inch, Unit::Centimeter);
        Assert::same('2.5400000000', $result);
    }

    public function testConvertDistanceNauticalMileToKilometer(): void
    {
        $result = Numbers::convert(1, Unit::NauticalMile, Unit::Kilometer);
        Assert::same('1.8520000000', $result);
    }

    public function testConvertDistanceYardToMeter(): void
    {
        $result = Numbers::convert(100, Unit::Yard, Unit::Meter);
        Assert::same('91.4400000000', $result);
    }

    public function testConvertMassKilogramToPound(): void
    {
        $result = Numbers::convert(1, Unit::Kilogram, Unit::Pound);
        Assert::same('2.2046226218', $result);
    }

    public function testConvertMassPoundToKilogram(): void
    {
        $result = Numbers::convert(1, Unit::Pound, Unit::Kilogram);
        Assert::same('0.4535923700', $result);
    }

    public function testConvertMassOunceToGram(): void
    {
        $result = Numbers::convert(1, Unit::Ounce, Unit::Gram);
        Assert::same('28.3495231000', $result);
    }

    public function testConvertMassMetricTonToKilogram(): void
    {
        $result = Numbers::convert(1, Unit::MetricTon, Unit::Kilogram);
        Assert::same('1000.0000000000', $result);
    }

    public function testConvertMassStoneToPound(): void
    {
        $result = Numbers::convert(1, Unit::Stone, Unit::Pound);
        Assert::same('14.0000000000', $result);
    }

    public function testConvertVolumeLiterToGallonUs(): void
    {
        $result = Numbers::convert(1, Unit::Liter, Unit::GallonUs);
        Assert::same('0.2641720523', $result);
    }

    public function testConvertVolumeGallonUsToLiter(): void
    {
        $result = Numbers::convert(1, Unit::GallonUs, Unit::Liter);
        Assert::same('3.7854117840', $result);
    }

    public function testConvertVolumeCupUsToMilliliter(): void
    {
        $result = Numbers::convert(1, Unit::CupUs, Unit::Milliliter);
        Assert::same('236.5882365000', $result);
    }

    public function testConvertVolumeTablespoonToTeaspoon(): void
    {
        $result = Numbers::convert(1, Unit::Tablespoon, Unit::Teaspoon);
        Assert::same('2.9999999835', $result);
    }

    public function testConvertTimeHourToSecond(): void
    {
        Assert::same('3600.0000000000', Numbers::convert(1, Unit::Hour, Unit::Second));
    }

    public function testConvertTimeDayToHour(): void
    {
        Assert::same('24.0000000000', Numbers::convert(1, Unit::Day, Unit::Hour));
    }

    public function testConvertTimeWeekToDay(): void
    {
        Assert::same('7.0000000000', Numbers::convert(1, Unit::Week, Unit::Day));
    }

    public function testConvertTimeMillisecondToSecond(): void
    {
        Assert::same('0.0010000000', Numbers::convert(1, Unit::Millisecond, Unit::Second));
    }

    public function testConvertAreaSquareMeterToSquareFoot(): void
    {
        $result = Numbers::convert(1, Unit::SquareMeter, Unit::SquareFoot);
        Assert::same('10.7639104167', $result);
    }

    public function testConvertAreaHectareToAcre(): void
    {
        $result = Numbers::convert(1, Unit::Hectare, Unit::Acre);
        Assert::same('2.4710538146', $result);
    }

    public function testConvertAreaSquareKilometerToSquareMile(): void
    {
        $result = Numbers::convert(1, Unit::SquareKilometer, Unit::SquareMile);
        Assert::same('0.3861021585', $result);
    }

    public function testConvertSpeedKilometerPerHourToMeterPerSecond(): void
    {
        $result = Numbers::convert(100, Unit::KilometerPerHour, Unit::MeterPerSecond);
        Assert::same('27.7777777800', $result);
    }

    public function testConvertSpeedMilePerHourToKnot(): void
    {
        $result = Numbers::convert(100, Unit::MilePerHour, Unit::Knot);
        Assert::same('86.8976241975', $result);
    }

    public function testConvertPressureAtmosphereToPascal(): void
    {
        $result = Numbers::convert(1, Unit::Atmosphere, Unit::Pascal);
        Assert::same('101325.0000000000', $result);
    }

    public function testConvertPressureBarToPsi(): void
    {
        $result = Numbers::convert(1, Unit::Bar, Unit::Psi);
        Assert::same('14.5037737730', $result);
    }

    public function testConvertEnergyKilowattHourToJoule(): void
    {
        $result = Numbers::convert(1, Unit::KilowattHour, Unit::Joule);
        Assert::same('3600000.0000000000', $result);
    }

    public function testConvertEnergyKilocalorieToCalorie(): void
    {
        $result = Numbers::convert(1, Unit::Kilocalorie, Unit::Calorie);
        Assert::same('1000.0000000000', $result);
    }

    public function testConvertPowerHorsepowerToWatt(): void
    {
        $result = Numbers::convert(1, Unit::HorsepowerMechanical, Unit::Watt);
        Assert::same('745.7000000000', $result);
    }

    public function testConvertForceNewtonToPoundForce(): void
    {
        $result = Numbers::convert(100, Unit::Newton, Unit::PoundForce);
        Assert::same('22.4808943099', $result);
    }

    public function testConvertForceKilogramForceToNewton(): void
    {
        $result = Numbers::convert(1, Unit::KilogramForce, Unit::Newton);
        Assert::same('9.8066500000', $result);
    }

    public function testConvertElectricPotentialKilovoltToVolt(): void
    {
        $result = Numbers::convert(1, Unit::Kilovolt, Unit::Volt);
        Assert::same('1000.0000000000', $result);
    }

    public function testConvertElectricCurrentAmpereToMilliampere(): void
    {
        $result = Numbers::convert(1, Unit::Ampere, Unit::Milliampere);
        Assert::same('1000.0000000000', $result);
    }

    public function testConvertLuminousIntensityCandelaToMillicandela(): void
    {
        $result = Numbers::convert(1, Unit::Candela, Unit::Millicandela);
        Assert::same('1000.0000000000', $result);
    }

    public function testConvertCrossCategoryRejected(): void
    {
        Assert::exception(function () {
            Numbers::convert(1, Unit::Celsius, Unit::Meter);
        }, InvalidArgumentException::class, "Invalid Argument: Cannot convert between different categories: Celsius (temperature) and Meter (distance)");
    }

    public function testConvertRoundTripDistance(): void
    {
        $original = '42.195';
        $miles = Numbers::convert($original, Unit::Kilometer, Unit::Mile);
        $result = Numbers::convert($miles, Unit::Mile, Unit::Kilometer);
        Assert::same('42.1949999999', $result);
    }

    public function testConversionUnitsReturnsCategories(): void
    {
        $units = Numbers::conversionUnits();

        Assert::true(array_key_exists('temperature', $units));
        Assert::true(array_key_exists('distance', $units));
        Assert::true(array_key_exists('mass', $units));
        Assert::true(array_key_exists('volume', $units));
        Assert::true(array_key_exists('time', $units));
        Assert::true(array_key_exists('area', $units));
        Assert::true(array_key_exists('speed', $units));
        Assert::true(array_key_exists('pressure', $units));
        Assert::true(array_key_exists('energy', $units));
        Assert::true(array_key_exists('power', $units));
        Assert::true(array_key_exists('force', $units));
        Assert::true(array_key_exists('electric_potential', $units));
        Assert::true(array_key_exists('electric_current', $units));
        Assert::true(array_key_exists('luminous_intensity', $units));
        Assert::true(in_array(Unit::Celsius, $units['temperature']));
        Assert::true(in_array(Unit::Kilometer, $units['distance']));
        Assert::true(in_array(Unit::Kilogram, $units['mass']));
    }

    public function testMeanBasic(): void
    {
        Assert::same('5.0000000000', Numbers::mean([2, 4, 6, 8]));
    }

    public function testMeanWithFloats(): void
    {
        Assert::same('2.5000000000', Numbers::mean([1.5, 2.5, 3.5]));
    }

    public function testMeanSingleValue(): void
    {
        Assert::same('42.0000000000', Numbers::mean([42]));
    }

    public function testMeanEmptyThrows(): void
    {
        Assert::exception(function () {
            Numbers::mean([]);
        }, InvalidArgumentException::class, 'Invalid Argument: Cannot compute mean of an empty array');
    }

    public function testMeanNegativeValues(): void
    {
        Assert::same('0.0000000000', Numbers::mean([-2, 0, 2]));
    }

    public function testMedianOddCount(): void
    {
        Assert::same('3.0000000000', Numbers::median([1, 3, 5]));
    }

    public function testMedianEvenCount(): void
    {
        Assert::same('4.0000000000', Numbers::median([1, 3, 5, 7]));
    }

    public function testMedianUnsorted(): void
    {
        Assert::same('4.0000000000', Numbers::median([7, 1, 5, 3]));
    }

    public function testMedianSingleValue(): void
    {
        Assert::same('10.0000000000', Numbers::median([10]));
    }

    public function testMedianEmptyThrows(): void
    {
        Assert::exception(function () {
            Numbers::median([]);
        }, InvalidArgumentException::class, 'Invalid Argument: Cannot compute median of an empty array');
    }

    public function testMedianWithFloats(): void
    {
        Assert::same('3.5000000000', Numbers::median([1.5, 2.5, 4.5, 5.5]));
    }

    public function testModeSingleMode(): void
    {
        $result = Numbers::mode([1, 2, 2, 3, 3, 3]);
        Assert::same([3], $result);
    }

    public function testModeMultipleModes(): void
    {
        $result = Numbers::mode([1, 1, 2, 2, 3]);
        Assert::same([1, 2], $result);
    }

    public function testModeAllSameFrequency(): void
    {
        $result = Numbers::mode([1, 2, 3]);
        sort($result);
        Assert::same([1, 2, 3], $result);
    }

    public function testModeSingleValue(): void
    {
        Assert::same([5], Numbers::mode([5]));
    }

    public function testModeEmptyThrows(): void
    {
        Assert::exception(function () {
            Numbers::mode([]);
        }, InvalidArgumentException::class, 'Invalid Argument: Cannot compute mode of an empty array');
    }

    public function testModeWithFloats(): void
    {
        Assert::same([2.5], Numbers::mode([1.0, 2.5, 2.5, 3.0]));
    }

    public function testVarianceBasic(): void
    {
        Assert::same('4.0000000000', Numbers::variance([2, 4, 4, 4, 5, 5, 7, 9]));
    }

    public function testVarianceSimple(): void
    {
        Assert::same('2.0000000000', Numbers::variance([1, 2, 3, 4, 5]));
    }

    public function testVarianceZeroVariance(): void
    {
        Assert::same('0.0000000000', Numbers::variance([5, 5, 5, 5]));
    }

    public function testVarianceSingleValue(): void
    {
        Assert::same('0.0000000000', Numbers::variance([42]));
    }

    public function testVarianceEmptyThrows(): void
    {
        Assert::exception(function () {
            Numbers::variance([]);
        }, InvalidArgumentException::class, 'Invalid Argument: Cannot compute variance of an empty array');
    }

    public function testSampleVarianceBasic(): void
    {
        $result = Numbers::sampleVariance([2, 4, 4, 4, 5, 5, 7, 9]);
        Assert::same('4.5714285714', $result);
    }

    public function testSampleVarianceSimple(): void
    {
        Assert::same('2.5000000000', Numbers::sampleVariance([1, 2, 3, 4, 5]));
    }

    public function testSampleVarianceSingleValueThrows(): void
    {
        Assert::exception(function () {
            Numbers::sampleVariance([5]);
        }, InvalidArgumentException::class, 'Invalid Argument: Sample variance requires at least 2 values');
    }

    public function testStandardDeviationBasic(): void
    {
        Assert::same('2.0000000000', Numbers::standardDeviation([2, 4, 4, 4, 5, 5, 7, 9]));
    }

    public function testStandardDeviationSimple(): void
    {
        $result = Numbers::standardDeviation([1, 2, 3, 4, 5]);
        Assert::true(bccomp($result, '1.4142135623', 9) >= 0);
        Assert::true(bccomp($result, '1.4142135625', 9) <= 0);
    }

    public function testStandardDeviationZeroVariance(): void
    {
        Assert::same('0.0000000000', Numbers::standardDeviation([5, 5, 5, 5]));
    }

    public function testSampleStandardDeviationBasic(): void
    {
        $result = Numbers::sampleStandardDeviation([2, 4, 4, 4, 5, 5, 7, 9]);
        Assert::same('2.1380899352', $result);
    }

    public function testSampleStandardDeviationSimple(): void
    {
        $result = Numbers::sampleStandardDeviation([1, 2, 3, 4, 5]);
        Assert::true(bccomp($result, '1.5811388300', 9) >= 0);
        Assert::true(bccomp($result, '1.5811388302', 9) <= 0);
    }

    public function testPercentileMedian(): void
    {
        Assert::same('3.0000000000', Numbers::percentile([1, 2, 3, 4, 5], 50));
    }

    public function testPercentile25th(): void
    {
        $result = Numbers::percentile([1, 2, 3, 4, 5, 6], 25);
        Assert::same('2.2500000000', $result);
    }

    public function testPercentile75th(): void
    {
        $result = Numbers::percentile([1, 2, 3, 4, 5, 6], 75);
        Assert::same('4.7500000000', $result);
    }

    public function testPercentileZero(): void
    {
        Assert::same('1.0000000000', Numbers::percentile([1, 2, 3, 4, 5], 0));
    }

    public function testPercentileHundred(): void
    {
        Assert::same('5.0000000000', Numbers::percentile([1, 2, 3, 4, 5], 100));
    }

    public function testPercentileUnsorted(): void
    {
        Assert::same('3.0000000000', Numbers::percentile([5, 1, 3, 2, 4], 50));
    }

    public function testPercentileEmptyThrows(): void
    {
        Assert::exception(function () {
            Numbers::percentile([], 50);
        }, InvalidArgumentException::class, 'Invalid Argument: Cannot compute percentile of an empty array');
    }

    public function testPercentileOutOfRangeHigh(): void
    {
        Assert::exception(function () {
            Numbers::percentile([1, 2, 3], 101);
        }, InvalidArgumentException::class, 'Invalid Argument: Percentile must be between 0 and 100');
    }

    public function testPercentileOutOfRangeNegative(): void
    {
        Assert::exception(function () {
            Numbers::percentile([1, 2, 3], -1);
        }, InvalidArgumentException::class, 'Invalid Argument: Percentile must be between 0 and 100');
    }

    public function testRangeBasic(): void
    {
        Assert::same('7.0000000000', Numbers::range([3, 7, 2, 9, 5]));
    }

    public function testRangeWithFloats(): void
    {
        Assert::same('3.0000000000', Numbers::range([1.5, 4.5]));
    }

    public function testRangeSingleValue(): void
    {
        Assert::same('0.0000000000', Numbers::range([5]));
    }

    public function testRangeEmptyThrows(): void
    {
        Assert::exception(function () {
            Numbers::range([]);
        }, InvalidArgumentException::class, 'Invalid Argument: Cannot compute range of an empty array');
    }

    public function testRangeNegativeValues(): void
    {
        Assert::same('6.0000000000', Numbers::range([-3, -1, 0, 2, 3]));
    }
}

(new NumbersTest())->run();
