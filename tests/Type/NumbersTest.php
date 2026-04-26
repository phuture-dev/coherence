<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Type;

use Phuture\Coherence\Enum\RoundingMode;
use Phuture\Coherence\Numbers;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Type\Numbers as FluentNumbers;
use Phuture\Coherence\Exception\{InvalidArgumentException, LogicException};

require __DIR__ . '/../bootstrap.php';

class NumbersTest extends TestCase
{
    public function testAbsolute(): void
    {
        Assert::same(5, FluentNumbers::from(-5)->absolute()->get());
        Assert::same(3.14, FluentNumbers::from(3.14)->absolute()->get());
        Assert::same(0, FluentNumbers::from(0)->absolute()->get());
    }

    public function testAdd(): void
    {
        Assert::same('0.3000000000', FluentNumbers::from(0.1)->add(0.2)->get());
        Assert::same('300.0000000000', FluentNumbers::from(100)->add(200)->get());
        Assert::same('4.0000000000', FluentNumbers::from(1.5)->add(2.5)->get());
    }

    public function testAbbreviate(): void
    {
        Assert::same(Numbers::abbreviate(1500), FluentNumbers::from(1500)->abbreviate());
        Assert::same(Numbers::abbreviate(1000000), FluentNumbers::from(1000000)->abbreviate());
        Assert::same(Numbers::abbreviate(1500, 2), FluentNumbers::from(1500)->abbreviate(2));
    }

    public function testAreEqual(): void
    {
        Assert::true(FluentNumbers::from(0.1 + 0.2)->areEqual(0.3));
        Assert::true(FluentNumbers::from(10)->areEqual(10.0));
        Assert::false(FluentNumbers::from(1.0)->areEqual(2.0));
    }

    public function testCeil(): void
    {
        Assert::same(4.0, FluentNumbers::from(3.2)->ceil()->get());
        Assert::same(-1.0, FluentNumbers::from(-1.1)->ceil()->get());
        Assert::same(5.0, FluentNumbers::from(5.0)->ceil()->get());
    }

    public function testClamp(): void
    {
        Assert::same(5, FluentNumbers::from(5)->clamp(1, 10)->get());
        Assert::same(0, FluentNumbers::from(-3)->clamp(0, 100)->get());
        Assert::same(100, FluentNumbers::from(150)->clamp(0, 100)->get());
    }

    public function testClampInvalidRange(): void
    {
        Assert::exception(
            fn () => FluentNumbers::from(5)->clamp(10, 1),
            InvalidArgumentException::class
        );
    }

    public function testCompare(): void
    {
        Assert::same(-1, FluentNumbers::from(1.0)->compare(2.0));
        Assert::same(1, FluentNumbers::from(2.0)->compare(1.0));
        Assert::same(0, FluentNumbers::from(1.0)->compare(1.0));
    }

    public function testDivide(): void
    {
        Assert::same('3.3333333333', FluentNumbers::from(10)->divide(3)->get());
        Assert::same('25.0000000000', FluentNumbers::from(100)->divide(4)->get());
    }

    public function testDivideByZero(): void
    {
        Assert::exception(
            fn () => FluentNumbers::from(10)->divide(0),
            InvalidArgumentException::class
        );
    }

    public function testFileSize(): void
    {
        Assert::same(Numbers::fileSize(500), FluentNumbers::from(500)->fileSize());
        Assert::same(Numbers::fileSize(1024), FluentNumbers::from(1024)->fileSize());
        Assert::same(Numbers::fileSize(1500, 2), FluentNumbers::from(1500)->fileSize(2));
    }

    public function testFloor(): void
    {
        Assert::same(3.0, FluentNumbers::from(3.8)->floor()->get());
        Assert::same(-2.0, FluentNumbers::from(-1.1)->floor()->get());
        Assert::same(5.0, FluentNumbers::from(5.0)->floor()->get());
    }

    public function testForHumans(): void
    {
        Assert::same(Numbers::forHumans(1500), FluentNumbers::from(1500)->forHumans());
        Assert::same(Numbers::forHumans(1000000), FluentNumbers::from(1000000)->forHumans());
        Assert::same(Numbers::forHumans(1234, 2), FluentNumbers::from(1234)->forHumans(2));
    }

    public function testFormat(): void
    {
        Assert::same(Numbers::format(1234567.8912, 2), FluentNumbers::from(1234567.8912)->format(2));
        Assert::same(Numbers::format(1234567, 0), FluentNumbers::from(1234567)->format(0));
    }

    public function testIsGreaterThan(): void
    {
        Assert::true(FluentNumbers::from(10.0)->isGreaterThan(5.0));
        Assert::false(FluentNumbers::from(5.0)->isGreaterThan(10.0));
        Assert::false(FluentNumbers::from(10.0)->isGreaterThan(10.0));
    }

    public function testIsGreaterThanOrEqualTo(): void
    {
        Assert::true(FluentNumbers::from(10.0)->isGreaterThanOrEqualTo(5.0));
        Assert::true(FluentNumbers::from(10.0)->isGreaterThanOrEqualTo(10.0));
        Assert::false(FluentNumbers::from(5.0)->isGreaterThanOrEqualTo(10.0));
    }

    public function testIsInteger(): void
    {
        Assert::true(FluentNumbers::from(5)->isInteger());
        Assert::true(FluentNumbers::from(5.0)->isInteger());
        Assert::false(FluentNumbers::from(3.14)->isInteger());
    }

    public function testIsLessThan(): void
    {
        Assert::true(FluentNumbers::from(5.0)->isLessThan(10.0));
        Assert::false(FluentNumbers::from(10.0)->isLessThan(5.0));
        Assert::false(FluentNumbers::from(10.0)->isLessThan(10.0));
    }

    public function testIsLessThanOrEqualTo(): void
    {
        Assert::true(FluentNumbers::from(5.0)->isLessThanOrEqualTo(10.0));
        Assert::true(FluentNumbers::from(10.0)->isLessThanOrEqualTo(10.0));
        Assert::false(FluentNumbers::from(15.0)->isLessThanOrEqualTo(10.0));
    }

    public function testIsNegative(): void
    {
        Assert::true(FluentNumbers::from(-5)->isNegative());
        Assert::true(FluentNumbers::from(-0.1)->isNegative());
        Assert::false(FluentNumbers::from(0)->isNegative());
        Assert::false(FluentNumbers::from(3)->isNegative());
    }

    public function testIsPositive(): void
    {
        Assert::true(FluentNumbers::from(5)->isPositive());
        Assert::true(FluentNumbers::from(0.1)->isPositive());
        Assert::false(FluentNumbers::from(0)->isPositive());
        Assert::false(FluentNumbers::from(-3)->isPositive());
    }

    public function testIsZero(): void
    {
        Assert::true(FluentNumbers::from(0)->isZero());
        Assert::true(FluentNumbers::from(0.0)->isZero());
        Assert::false(FluentNumbers::from(0.5)->isZero());
    }

    public function testMax(): void
    {
        Assert::same(7, FluentNumbers::from(3)->max(7)->get());
        Assert::same(-2, FluentNumbers::from(-5)->max(-2)->get());
        Assert::same(3.14, FluentNumbers::from(3.14)->max(2.7)->get());
    }

    public function testMin(): void
    {
        Assert::same(3, FluentNumbers::from(3)->min(7)->get());
        Assert::same(-5, FluentNumbers::from(-5)->min(-2)->get());
        Assert::same(2.7, FluentNumbers::from(3.14)->min(2.7)->get());
    }

    public function testModulus(): void
    {
        Assert::same('1', FluentNumbers::from(10)->modulus(3)->get());
        Assert::same('0', FluentNumbers::from(10)->modulus(2)->get());
    }

    public function testMultiply(): void
    {
        Assert::same('0.0200000000', FluentNumbers::from(0.1)->multiply(0.2)->get());
        Assert::same('12.0000000000', FluentNumbers::from(3)->multiply(4)->get());
        Assert::same('10.0000000000', FluentNumbers::from(2.5)->multiply(4.0)->get());
    }

    public function testOpposite(): void
    {
        Assert::same(-5, FluentNumbers::from(5)->opposite()->get());
        Assert::same(3.2, FluentNumbers::from(-3.2)->opposite()->get());
        Assert::same(0, FluentNumbers::from(0)->opposite()->get());
    }

    public function testOrdinal(): void
    {
        Assert::same('1st', FluentNumbers::from(1)->ordinal());
        Assert::same('2nd', FluentNumbers::from(2)->ordinal());
        Assert::same('3rd', FluentNumbers::from(3)->ordinal());
        Assert::same('4th', FluentNumbers::from(4)->ordinal());
        Assert::same('11th', FluentNumbers::from(11)->ordinal());
    }

    public function testPercentage(): void
    {
        Assert::same(Numbers::percentage(0.75), FluentNumbers::from(0.75)->percentage());
        Assert::same(Numbers::percentage(0.75, 2), FluentNumbers::from(0.75)->percentage(2));
        Assert::same(Numbers::percentage(1.5, 1), FluentNumbers::from(1.5)->percentage(1));
    }

    public function testRound(): void
    {
        Assert::same(3.46, FluentNumbers::from(3.456)->round(2)->get());
        Assert::same(3.0, FluentNumbers::from(3.456)->round(0)->get());
        Assert::same(4.0, FluentNumbers::from(3.5)->round(0)->get());
    }

    public function testRoundWithMode(): void
    {
        Assert::same(3.0, FluentNumbers::from(3.5)->round(0, RoundingMode::HalfDown)->get());
        Assert::same(4.0, FluentNumbers::from(3.5)->round(0, RoundingMode::HalfUp)->get());
    }

    public function testSpell(): void
    {
        Assert::same('zero', FluentNumbers::from(0)->spell());
        Assert::same('seven', FluentNumbers::from(7)->spell());
        Assert::same('forty-two', FluentNumbers::from(42)->spell());
        Assert::same('negative five', FluentNumbers::from(-5)->spell());
    }

    public function testSquareRoot(): void
    {
        Assert::same('3.0000000000', FluentNumbers::from(9)->squareRoot()->get());
        Assert::same('1.4142', FluentNumbers::from(2)->squareRoot(4)->get());
    }

    public function testSquareRootNegative(): void
    {
        Assert::exception(
            fn () => FluentNumbers::from(-1)->squareRoot(),
            InvalidArgumentException::class
        );
    }

    public function testSubtract(): void
    {
        Assert::same('7.0000000000', FluentNumbers::from(10)->subtract(3)->get());
        Assert::same('3.0000000000', FluentNumbers::from(5.5)->subtract(2.5)->get());
        Assert::same('0.0000000000', FluentNumbers::from(1)->subtract(1)->get());
    }

    public function testToFloat(): void
    {
        Assert::same(5.0, FluentNumbers::from(5)->toFloat());
        Assert::same(3.14, FluentNumbers::from(3.14)->toFloat());
    }

    public function testToFloatAfterBcmath(): void
    {
        Assert::same(0.3, FluentNumbers::from(0.1)->add(0.2)->toFloat());
    }

    public function testToInt(): void
    {
        Assert::same(5, FluentNumbers::from(5)->toInt());
        Assert::same(3, FluentNumbers::from(3.9)->toInt());
        Assert::same(-7, FluentNumbers::from(-7.5)->toInt());
    }

    public function testToNumber(): void
    {
        Assert::same(5, FluentNumbers::from(5)->toNumber());
        Assert::same(3.14, FluentNumbers::from(3.14)->toNumber());
    }

    public function testToNumberAfterBcmath(): void
    {
        Assert::same(0.3, FluentNumbers::from(0.1)->add(0.2)->toNumber());
    }

    public function testTrimTrailingZeros(): void
    {
        Assert::same('13.14', FluentNumbers::from(10)->add(3.14)->trimTrailingZeros());
        Assert::same('5', FluentNumbers::from(2.5)->add(2.5)->trimTrailingZeros());
    }

    public function testChaining(): void
    {
        $result = FluentNumbers::from(10)
            ->add(5)
            ->multiply(2)
            ->subtract(3)
            ->round(0)
            ->get();

        Assert::same(27.0, $result);
    }

    public function testChainingWithComparison(): void
    {
        $num = FluentNumbers::from(10)->add(5);
        Assert::true($num->isPositive());
        Assert::false($num->isZero());
        Assert::true($num->isGreaterThan(10));
    }

    public function testChainingAbsoluteAndOpposite(): void
    {
        Assert::same(5, FluentNumbers::from(-5)->absolute()->get());
        Assert::same(5, FluentNumbers::from(-5)->opposite()->absolute()->get());
        Assert::same(-5, FluentNumbers::from(5)->opposite()->get());
    }

    public function testChainingClampAndRound(): void
    {
        Assert::same(100.0, FluentNumbers::from(150.7)->clamp(0, 100)->round(0)->get());
        Assert::same(0.0, FluentNumbers::from(-5.3)->clamp(0, 100)->round(0)->get());
    }

    public function testGetReturnsStoredData(): void
    {
        Assert::same(42, FluentNumbers::from(42)->get());
        Assert::same(3.14, FluentNumbers::from(3.14)->get());
    }

    public function testInvokeReturnsStoredData(): void
    {
        $num = FluentNumbers::from(42);
        Assert::same(42, $num());
    }

    public function testBcmathStringPreserved(): void
    {
        $result = FluentNumbers::from(0.1)->add(0.2)->get();
        Assert::same('0.3000000000', $result);
    }
}

(new NumbersTest())->run();
