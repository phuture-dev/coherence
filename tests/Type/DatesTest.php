<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Type;

use DateTimeImmutable;
use Phuture\Coherence\Dates;
use Phuture\Coherence\Exception\InvalidArgumentException;
use Phuture\Coherence\Type\Dates as FluentDates;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

class DatesTest extends TestCase
{
    private function date(string $dateString, string $timezone = 'UTC'): FluentDates
    {
        return Dates::of($dateString, $timezone);
    }

    public function testFromCreatesInstance(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $fluent = FluentDates::from($date);
        Assert::type(FluentDates::class, $fluent);
    }

    public function testGetReturnsDateTimeImmutable(): void
    {
        $fluent = $this->date('2026-04-21 14:30:00');
        Assert::type(DateTimeImmutable::class, $fluent->get());
    }

    public function testToDateTimeImmutableReturnsValue(): void
    {
        $fluent = $this->date('2026-04-21 14:30:00');
        Assert::type(DateTimeImmutable::class, $fluent->toDateTimeImmutable());
    }

    public function testGetAndToDateTimeImmutableReturnSameValue(): void
    {
        $fluent = $this->date('2026-04-21 14:30:00');
        Assert::same($fluent->get()->getTimestamp(), $fluent->toDateTimeImmutable()->getTimestamp());
    }

    public function testToTimezoneConvertsTimezone(): void
    {
        $result = $this->date('2026-04-21 12:00:00')
            ->toTimezone('America/New_York')
            ->toDateTimeImmutable();

        Assert::same('2026-04-21 08:00:00', $result->format('Y-m-d H:i:s'));
        Assert::same('America/New_York', $result->getTimezone()->getName());
    }

    public function testAddSecondsChains(): void
    {
        $result = $this->date('2026-04-21 14:30:00')->addSeconds(90)->get();
        Assert::same('2026-04-21 14:31:30', $result->format('Y-m-d H:i:s'));
    }

    public function testAddMinutesChains(): void
    {
        $result = $this->date('2026-04-21 14:30:00')->addMinutes(45)->get();
        Assert::same('2026-04-21 15:15:00', $result->format('Y-m-d H:i:s'));
    }

    public function testAddHoursChains(): void
    {
        $result = $this->date('2026-04-21 14:30:00')->addHours(3)->get();
        Assert::same('2026-04-21 17:30:00', $result->format('Y-m-d H:i:s'));
    }

    public function testAddDaysChains(): void
    {
        $result = $this->date('2026-04-21')->addDays(10)->get();
        Assert::same('2026-05-01', $result->format('Y-m-d'));
    }

    public function testAddWeeksChains(): void
    {
        $result = $this->date('2026-04-21')->addWeeks(2)->get();
        Assert::same('2026-05-05', $result->format('Y-m-d'));
    }

    public function testAddMonthsChains(): void
    {
        $result = $this->date('2026-01-15')->addMonths(3)->get();
        Assert::same('2026-04-15', $result->format('Y-m-d'));
    }

    public function testAddYearsChains(): void
    {
        $result = $this->date('2026-04-21')->addYears(5)->get();
        Assert::same('2031-04-21', $result->format('Y-m-d'));
    }

    public function testRemoveSecondsChains(): void
    {
        $result = $this->date('2026-04-21 14:30:00')->removeSeconds(30)->get();
        Assert::same('2026-04-21 14:29:30', $result->format('Y-m-d H:i:s'));
    }

    public function testRemoveSecondsThrowsOnNegative(): void
    {
        Assert::exception(
            fn() => $this->date('2026-04-21 14:30:00')->removeSeconds(-1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveMinutesChains(): void
    {
        $result = $this->date('2026-04-21 14:30:00')->removeMinutes(15)->get();
        Assert::same('2026-04-21 14:15:00', $result->format('Y-m-d H:i:s'));
    }

    public function testRemoveMinutesThrowsOnNegative(): void
    {
        Assert::exception(
            fn() => $this->date('2026-04-21 14:30:00')->removeMinutes(-1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveHoursChains(): void
    {
        $result = $this->date('2026-04-21 14:30:00')->removeHours(2)->get();
        Assert::same('2026-04-21 12:30:00', $result->format('Y-m-d H:i:s'));
    }

    public function testRemoveHoursThrowsOnNegative(): void
    {
        Assert::exception(
            fn() => $this->date('2026-04-21 14:30:00')->removeHours(-1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveDaysChains(): void
    {
        $result = $this->date('2026-04-21')->removeDays(5)->get();
        Assert::same('2026-04-16', $result->format('Y-m-d'));
    }

    public function testRemoveDaysThrowsOnNegative(): void
    {
        Assert::exception(
            fn() => $this->date('2026-04-21')->removeDays(-1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveWeeksChains(): void
    {
        $result = $this->date('2026-04-21')->removeWeeks(1)->get();
        Assert::same('2026-04-14', $result->format('Y-m-d'));
    }

    public function testRemoveMonthsChains(): void
    {
        $result = $this->date('2026-06-15')->removeMonths(2)->get();
        Assert::same('2026-04-15', $result->format('Y-m-d'));
    }

    public function testRemoveYearsChains(): void
    {
        $result = $this->date('2026-04-21')->removeYears(10)->get();
        Assert::same('2016-04-21', $result->format('Y-m-d'));
    }

    public function testStartOfDayChains(): void
    {
        $result = $this->date('2026-04-21 14:30:45')->startOfDay()->get();
        Assert::same('2026-04-21 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testEndOfDayChains(): void
    {
        $result = $this->date('2026-04-21 14:30:45')->endOfDay()->get();
        Assert::same('2026-04-21 23:59:59', $result->format('Y-m-d H:i:s'));
    }

    public function testStartOfWeekChains(): void
    {
        $result = $this->date('2026-04-21 14:00:00')->startOfWeek()->get();
        Assert::same('2026-04-20 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testEndOfWeekChains(): void
    {
        $result = $this->date('2026-04-21 14:00:00')->endOfWeek()->get();
        Assert::same('2026-04-26 23:59:59', $result->format('Y-m-d H:i:s'));
    }

    public function testStartOfMonthChains(): void
    {
        $result = $this->date('2026-04-21 14:30:00')->startOfMonth()->get();
        Assert::same('2026-04-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testEndOfMonthChains(): void
    {
        $result = $this->date('2026-04-15 14:30:00')->endOfMonth()->get();
        Assert::same('2026-04-30 23:59:59', $result->format('Y-m-d H:i:s'));
    }

    public function testStartOfYearChains(): void
    {
        $result = $this->date('2026-09-15 10:00:00')->startOfYear()->get();
        Assert::same('2026-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testEndOfYearChains(): void
    {
        $result = $this->date('2026-04-21 14:00:00')->endOfYear()->get();
        Assert::same('2026-12-31 23:59:59', $result->format('Y-m-d H:i:s'));
    }

    public function testFullChainAddAndRemove(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'UTC')
            ->addDays(10)
            ->removeHours(2)
            ->startOfDay()
            ->get();

        Assert::same('2026-05-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testChainPreservesTimezone(): void
    {
        $result = Dates::of('2026-04-21 12:00:00', 'UTC')
            ->toTimezone('Asia/Tokyo')
            ->addDays(1)
            ->get();

        Assert::same('Asia/Tokyo', $result->getTimezone()->getName());
    }

    public function testChainResultIsImmutable(): void
    {
        $fluent = $this->date('2026-04-21');
        $original = $fluent->get();
        $fluent->addDays(10);
        $after = $fluent->get();

        Assert::notSame($original->format('Y-m-d'), $after->format('Y-m-d'));
    }

    public function testRemoveWeeksThrowsOnNegative(): void
    {
        Assert::exception(
            fn() => $this->date('2026-04-21')->removeWeeks(-1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveMonthsThrowsOnNegative(): void
    {
        Assert::exception(
            fn() => $this->date('2026-04-21')->removeMonths(-1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveYearsThrowsOnNegative(): void
    {
        Assert::exception(
            fn() => $this->date('2026-04-21')->removeYears(-1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveZeroIsNoop(): void
    {
        $result = $this->date('2026-04-21 14:30:00')
            ->removeSeconds(0)
            ->removeMinutes(0)
            ->removeHours(0)
            ->removeDays(0)
            ->removeWeeks(0)
            ->removeMonths(0)
            ->removeYears(0)
            ->get();

        Assert::same('2026-04-21 14:30:00', $result->format('Y-m-d H:i:s'));
    }

    public function testAddZeroIsNoop(): void
    {
        $result = $this->date('2026-04-21 14:30:00')
            ->addSeconds(0)
            ->addMinutes(0)
            ->addHours(0)
            ->addDays(0)
            ->addWeeks(0)
            ->addMonths(0)
            ->addYears(0)
            ->get();

        Assert::same('2026-04-21 14:30:00', $result->format('Y-m-d H:i:s'));
    }

    public function testToTimezoneWithInvalidTimezoneThrows(): void
    {
        Assert::exception(
            fn() => $this->date('2026-04-21 14:30:00')->toTimezone('Not/ATimezone'),
            InvalidArgumentException::class
        );
    }

    public function testEndOfMonthFebruaryChains(): void
    {
        $result = $this->date('2026-02-10')->endOfMonth()->get();
        Assert::same('2026-02-28 23:59:59', $result->format('Y-m-d H:i:s'));
    }

    public function testEndOfMonthFebruaryLeapYearChains(): void
    {
        $result = $this->date('2024-02-10')->endOfMonth()->get();
        Assert::same('2024-02-29 23:59:59', $result->format('Y-m-d H:i:s'));
    }

    public function testFromAcceptsDateTimeImmutable(): void
    {
        $date = new DateTimeImmutable('2026-04-21 14:30:00', new \DateTimeZone('UTC'));
        $fluent = FluentDates::from($date);
        Assert::same('2026-04-21 14:30:00', $fluent->get()->format('Y-m-d H:i:s'));
    }

    public function testInvokeReturnsData(): void
    {
        $fluent = $this->date('2026-04-21 14:30:00');
        Assert::type(DateTimeImmutable::class, $fluent());
    }

    public function testLongChainOperations(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'UTC')
            ->addYears(1)
            ->addMonths(2)
            ->addDays(5)
            ->addHours(3)
            ->addMinutes(15)
            ->addSeconds(30)
            ->removeDays(1)
            ->startOfDay()
            ->get();

        Assert::same('2027-06-25 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testFormatReturnsStringFromPhpNative(): void
    {
        $result = $this->date('2026-04-21 14:30:00')
            ->addDays(10)
            ->format('Y-m-d');

        Assert::same('2026-05-01', $result);
    }

    public function testFormatReturnsStringFromDayJsTokens(): void
    {
        $result = $this->date('2026-04-21 14:30:00')
            ->addDays(10)
            ->format('YYYY-MM-DD');

        Assert::same('2026-05-01', $result);
    }

    public function testToDateStringReturnsString(): void
    {
        $result = $this->date('2026-04-21 14:30:00')
            ->addDays(10)
            ->toDate();

        Assert::same('2026-05-01', $result);
    }

    public function testToTimeStringReturnsString(): void
    {
        $result = $this->date('2026-04-21 14:30:45')
            ->addMinutes(15)
            ->toTime();

        Assert::same('14:45:45', $result);
    }

    public function testToDateTimeStringReturnsString(): void
    {
        $result = $this->date('2026-04-21 14:30:00')
            ->addDays(1)
            ->toDateTime();

        Assert::same('2026-04-22 14:30:00', $result);
    }

    public function testToIso8601ReturnsString(): void
    {
        $result = Dates::of('2026-04-21 12:00:00', 'UTC')
            ->toIso8601();

        Assert::same('2026-04-21T12:00:00+00:00', $result);
    }

    public function testToRfc2822ReturnsString(): void
    {
        $result = Dates::of('2026-04-21 12:00:00', 'UTC')
            ->toRfc2822();

        Assert::contains('Apr 2026', $result);
    }

    public function testToRfc822ReturnsString(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'UTC')
            ->toRfc822();

        Assert::same('Tue, 21 Apr 26 14:30:00 +0000', $result);
    }

    public function testToRfc850ReturnsString(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'UTC')
            ->toRfc850();

        Assert::same('Tuesday, 21-Apr-26 14:30:00 UTC', $result);
    }

    public function testToRfc1036ReturnsString(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'UTC')
            ->toRfc1036();

        Assert::same('Tue, 21 Apr 26 14:30:00 +0000', $result);
    }

    public function testToRfc1123ReturnsString(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'UTC')
            ->toRfc1123();

        Assert::same('Tue, 21 Apr 2026 14:30:00 +0000', $result);
    }

    public function testToRfc7231ConvertsToGmt(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'America/New_York')
            ->toRfc7231();

        Assert::same('Tue, 21 Apr 2026 18:30:00 GMT', $result);
    }

    public function testToW3cReturnsString(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'UTC')
            ->toW3c();

        Assert::same('2026-04-21T14:30:00+00:00', $result);
    }

    public function testGetTimezoneReturnsString(): void
    {
        $result = Dates::of('2026-04-21', 'Asia/Tokyo')
            ->getTimezone();

        Assert::same('Asia/Tokyo', $result);
    }

    public function testChainedFormatAsTerminalOperation(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'UTC')
            ->addDays(10)
            ->startOfDay()
            ->format('dddd, MMMM D, YYYY');

        Assert::same('Friday, May 1, 2026', $result);
    }
}

(new DatesTest())->run();
