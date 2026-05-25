<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Type;

use RoundingMode;
use Phuture\Coherence\Numbers;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Enum\Unit;
use Phuture\Coherence\Type\Numbers as FluentNumbers;
use Phuture\Coherence\Exception\{InvalidArgumentException};

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

    public function testAddPercentage(): void
    {
        Assert::same('120.0000000000', FluentNumbers::from(100)->addPercentage(20)->get());
        Assert::same('55.0000000000', FluentNumbers::from(50)->addPercentage(10)->get());
        Assert::same('105.5000000000', FluentNumbers::from(100)->addPercentage(5.5)->get());
    }

    public function testBcmathPrecisionAfterAbsolute(): void
    {
        // absolute() on BCMath string coerces to int/float; BCMath re-entry still works
        $result = FluentNumbers::from(-1)->add(-2)->absolute()->multiply(3)->get();
        Assert::same('9.0000000000', $result);
    }

    public function testBcmathPrecisionAfterCeil(): void
    {
        $result = FluentNumbers::from(3)->divide(2)->ceil()->multiply(3)->get();
        Assert::same('6.0000000000', $result);
    }

    public function testBcmathPrecisionAfterClamp(): void
    {
        // multiply gives "15.0000000000"; clamp(0,10) caps it to int 10; add(2) re-enters BCMath
        $result = FluentNumbers::from(5)->multiply(3)->clamp(0, 10)->add(2)->get();
        Assert::same('12.0000000000', $result);
    }

    public function testBcmathPrecisionAfterFloor(): void
    {
        $result = FluentNumbers::from(3)->divide(2)->floor()->multiply(3)->get();
        Assert::same('3.0000000000', $result);
    }

    public function testBcmathPrecisionAfterMax(): void
    {
        // multiply gives "6.0000000000"; max(10) picks int 10; subtract(1) re-enters BCMath
        $result = FluentNumbers::from(3)->multiply(2)->max(10)->subtract(1)->get();
        Assert::same('9.0000000000', $result);
    }

    public function testBcmathPrecisionAfterMin(): void
    {
        // multiply gives "6.0000000000"; min(3) picks int 3; add(1) re-enters BCMath
        $result = FluentNumbers::from(3)->multiply(2)->min(3)->add(1)->get();
        Assert::same('4.0000000000', $result);
    }

    public function testBcmathPrecisionAfterOpposite(): void
    {
        $result = FluentNumbers::from(1)->add(2)->opposite()->subtract(1)->get();
        Assert::same('-4.0000000000', $result);
    }

    public function testBcmathPrecisionAfterRound(): void
    {
        // round(4) gives float 0.3333; add(0) re-enters BCMath and pads to 10 decimals
        $result = FluentNumbers::from(1)->divide(3)->round(4)->add(0)->get();
        Assert::same('0.3333000000', $result);
    }

    public function testBcmathPrecisionAfterToNumber(): void
    {
        // toNumber() converts "0.2500000000" → float 0.25; BCMath re-entry multiplies precisely
        $result = FluentNumbers::from(1)->divide(4)->toNumber()->multiply(4)->get();
        Assert::same('1.0000000000', $result);
    }

    public function testBcmathPrecisionDivideMultiplyRoundTrip(): void
    {
        Assert::same('0.9999999999', FluentNumbers::from(1)->divide(3)->multiply(3)->get());
    }

    public function testBcmathPrecisionLargeChain(): void
    {
        $expected = Numbers::divide(
            Numbers::subtract(
                Numbers::multiply(Numbers::add('1', '0.1'), '3'),
                '0.3'
            ),
            '3'
        );

        Assert::same($expected, FluentNumbers::from(1)->add(0.1)->multiply(3)->subtract(0.3)->divide(3)->get());
    }

    // --- BCMath precision validation ---

    public function testBcmathPrecisionMultiStep(): void
    {
        $result = FluentNumbers::from(1)
            ->add(2)
            ->multiply(4)
            ->subtract(3)
            ->divide(2)
            ->get();

        Assert::same('4.5000000000', $result);
    }

    public function testBcmathPrecisionNegativeChain(): void
    {
        $result = FluentNumbers::from(-5)->multiply(3)->add(1)->get();
        Assert::same('-14.0000000000', $result);
    }

    public function testBcmathPrecisionSquareRootChained(): void
    {
        // sqrt result feeds into add(0) unchanged — BCMath string round-trip
        Assert::same(Numbers::squareRoot(2), FluentNumbers::from(2)->squareRoot()->add(0)->get());
    }

    public function testBcmathPrecisionSubtractToZero(): void
    {
        Assert::same('0.0000000000', FluentNumbers::from(0.1)->add(0.2)->subtract(0.3)->get());
    }

    public function testBcmathPrecisionTinyFraction(): void
    {
        Assert::same('0.0000000010', FluentNumbers::from('0.0000000001')->multiply(10)->get());
    }

    public function testBcmathPrecisionUnchangedAfterToNumberWithRepeatingDecimal(): void
    {
        // toNumber() on "0.3333333333" → float; bcmul still gives same result as without toNumber
        $withToNumber    = FluentNumbers::from(1)->divide(3)->toNumber()->multiply(3)->get();
        $withoutToNumber = FluentNumbers::from(1)->divide(3)->multiply(3)->get();
        Assert::same($withoutToNumber, $withToNumber);
    }

    public function testBcmathPrecisionZeroThroughChain(): void
    {
        Assert::same('0.0000000000', FluentNumbers::from(0)->add(0)->multiply(1000)->divide(5)->get());
    }

    public function testBcmathStringPreserved(): void
    {
        $result = FluentNumbers::from(0.1)->add(0.2)->get();
        Assert::same('0.3000000000', $result);
    }

    public function testCeil(): void
    {
        Assert::same('4', FluentNumbers::from(3.2)->ceil()->get());
        Assert::same('-1', FluentNumbers::from(-1.1)->ceil()->get());
        Assert::same('5', FluentNumbers::from(5.0)->ceil()->get());
    }

    public function testChaining(): void
    {
        $result = FluentNumbers::from(10)
            ->add(5)
            ->multiply(2)
            ->subtract(3)
            ->round(0)
            ->get();

        Assert::same('27', $result);
    }

    public function testChainingAbsoluteAndOpposite(): void
    {
        Assert::same(5, FluentNumbers::from(-5)->absolute()->get());
        Assert::same(5, FluentNumbers::from(-5)->opposite()->absolute()->get());
        Assert::same(-5, FluentNumbers::from(5)->opposite()->get());
    }

    public function testChainingClampAndRound(): void
    {
        Assert::same('100', FluentNumbers::from(150.7)->clamp(0, 100)->round(0)->get());
        Assert::same('0', FluentNumbers::from(-5.3)->clamp(0, 100)->round(0)->get());
    }

    public function testClamp(): void
    {
        Assert::same('5', FluentNumbers::from(5)->clamp(1, 10)->get());
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

    public function testConvertChaining(): void
    {
        $result = FluentNumbers::from(100)
            ->convert(Unit::Celsius, Unit::Fahrenheit)
            ->get();
        Assert::same('212.0000000000', $result);
    }

    public function testConvertDistanceChaining(): void
    {
        $result = FluentNumbers::from(1)
            ->convert(Unit::Mile, Unit::Kilometer)
            ->get();
        Assert::same('1.6093440000', $result);
    }

    public function testConvertThenArithmeticChaining(): void
    {
        $result = FluentNumbers::from(10)
            ->convert(Unit::Kilometer, Unit::Mile)
            ->add((float) Numbers::convert(5, Unit::Kilometer, Unit::Mile))
            ->round(2)
            ->get();
        Assert::same('9.32', $result);
    }

    public function testConvertWithRoundChaining(): void
    {
        $result = FluentNumbers::from(100)
            ->convert(Unit::Kilometer, Unit::Mile)
            ->round(2)
            ->get();
        Assert::same('62.14', $result);
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
        Assert::same('3', FluentNumbers::from(3.8)->floor()->get());
        Assert::same('-2', FluentNumbers::from(-1.1)->floor()->get());
        Assert::same('5', FluentNumbers::from(5.0)->floor()->get());
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

    public function testFormatPercentage(): void
    {
        Assert::same(Numbers::formatPercentage(0.75), FluentNumbers::from(0.75)->formatPercentage());
        Assert::same(Numbers::formatPercentage(0.75, 2), FluentNumbers::from(0.75)->formatPercentage(2));
        Assert::same(Numbers::formatPercentage(1.5, 1), FluentNumbers::from(1.5)->formatPercentage(1));
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

    public function testMax(): void
    {
        Assert::same(7, FluentNumbers::from(3)->max(7)->get());
        Assert::same(-2, FluentNumbers::from(-5)->max(-2)->get());
        Assert::same('3.14', FluentNumbers::from(3.14)->max(2.7)->get());
    }

    public function testMin(): void
    {
        Assert::same('3', FluentNumbers::from(3)->min(7)->get());
        Assert::same('-5', FluentNumbers::from(-5)->min(-2)->get());
        Assert::same(2.7, FluentNumbers::from(3.14)->min(2.7)->get());
    }

    public function testModulus(): void
    {
        Assert::same('1.0000000000', FluentNumbers::from(10)->modulus(3)->get());
        Assert::same('0.0000000000', FluentNumbers::from(10)->modulus(2)->get());
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
        Assert::same('40.0000000000', FluentNumbers::from(200)->percentage(20)->get());
        Assert::same('5.0000000000', FluentNumbers::from(50)->percentage(10)->get());
        Assert::same('5.5000000000', FluentNumbers::from(100)->percentage(5.5)->get());
    }

    public function testRound(): void
    {
        Assert::same('3.46', FluentNumbers::from(3.456)->round(2)->get());
        Assert::same('3', FluentNumbers::from(3.456)->round(0)->get());
        Assert::same('4', FluentNumbers::from(3.5)->round(0)->get());
    }

    public function testRoundWithMode(): void
    {
        Assert::same('3', FluentNumbers::from(3.5)->round(0, RoundingMode::HalfTowardsZero)->get());
        Assert::same('4', FluentNumbers::from(3.5)->round(0, RoundingMode::HalfAwayFromZero)->get());
        Assert::same('4', FluentNumbers::from(3.5)->round(0, RoundingMode::HalfEven)->get());
        Assert::same('2', FluentNumbers::from(2.5)->round(0, RoundingMode::HalfEven)->get());
        Assert::same('3', FluentNumbers::from(3.5)->round(0, RoundingMode::HalfOdd)->get());
        Assert::same('3', FluentNumbers::from(2.5)->round(0, RoundingMode::HalfOdd)->get());
    }

    public function testSquareRoot(): void
    {
        Assert::same('3.0000000000', FluentNumbers::from(9)->squareRoot()->get());
        Assert::same('1.4142', FluentNumbers::from(2)->squareRoot(4)->get());
    }

    public function testSquareRootCustomScaleChained(): void
    {
        // scale=2 gives "1.41"; bcmul feeds it back as string → full 10-decimal result
        $result = FluentNumbers::from(2)->squareRoot(2)->multiply(2)->get();
        Assert::same('2.8200000000', $result);
    }

    public function testSquareRootFullPrecisionMultiply(): void
    {
        $result = FluentNumbers::from(9)->squareRoot()->multiply(2)->get();
        Assert::same('6.0000000000', $result);
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

    public function testSubtractPercentage(): void
    {
        Assert::same('80.0000000000', FluentNumbers::from(100)->subtractPercentage(20)->get());
        Assert::same('45.0000000000', FluentNumbers::from(50)->subtractPercentage(10)->get());
        Assert::same('94.5000000000', FluentNumbers::from(100)->subtractPercentage(5.5)->get());
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
        Assert::same('5.0000000000', FluentNumbers::from(5)->toNumber()->get());
        Assert::same('3.1400000000', FluentNumbers::from(3.14)->toNumber()->get());
    }

    public function testToNumberAfterBcmath(): void
    {
        Assert::same('0.3000000000', FluentNumbers::from(0.1)->add(0.2)->toNumber()->get());
    }
}

(new NumbersTest())->run();
