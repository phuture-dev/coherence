<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Type;

use DateTimeImmutable;
use Phuture\Coherence\Dates;
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

    // -------------------------------------------------------------------------
    // Factory and base
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Timezone
    // -------------------------------------------------------------------------

    public function testToTimezoneConvertsTimezone(): void
    {
        $result = $this->date('2026-04-21 12:00:00')
            ->toTimezone('America/New_York')
            ->toDateTimeString();

        Assert::same('2026-04-21 08:00:00', $result);
    }

    // -------------------------------------------------------------------------
    // Addition
    // -------------------------------------------------------------------------

    public function testAddSecondsChains(): void
    {
        Assert::same(
            '2026-04-21 14:31:30',
            $this->date('2026-04-21 14:30:00')->addSeconds(90)->toDateTimeString()
        );
    }

    public function testAddMinutesChains(): void
    {
        Assert::same(
            '2026-04-21 15:15:00',
            $this->date('2026-04-21 14:30:00')->addMinutes(45)->toDateTimeString()
        );
    }

    public function testAddHoursChains(): void
    {
        Assert::same(
            '2026-04-21 17:30:00',
            $this->date('2026-04-21 14:30:00')->addHours(3)->toDateTimeString()
        );
    }

    public function testAddDaysChains(): void
    {
        Assert::same(
            '2026-05-01',
            $this->date('2026-04-21')->addDays(10)->toDateString()
        );
    }

    public function testAddWeeksChains(): void
    {
        Assert::same(
            '2026-05-05',
            $this->date('2026-04-21')->addWeeks(2)->toDateString()
        );
    }

    public function testAddMonthsChains(): void
    {
        Assert::same(
            '2026-04-15',
            $this->date('2026-01-15')->addMonths(3)->toDateString()
        );
    }

    public function testAddYearsChains(): void
    {
        Assert::same(
            '2031-04-21',
            $this->date('2026-04-21')->addYears(5)->toDateString()
        );
    }

    // -------------------------------------------------------------------------
    // Subtraction
    // -------------------------------------------------------------------------

    public function testSubSecondsChains(): void
    {
        Assert::same(
            '2026-04-21 14:29:30',
            $this->date('2026-04-21 14:30:00')->subSeconds(30)->toDateTimeString()
        );
    }

    public function testSubMinutesChains(): void
    {
        Assert::same(
            '2026-04-21 14:15:00',
            $this->date('2026-04-21 14:30:00')->subMinutes(15)->toDateTimeString()
        );
    }

    public function testSubHoursChains(): void
    {
        Assert::same(
            '2026-04-21 12:30:00',
            $this->date('2026-04-21 14:30:00')->subHours(2)->toDateTimeString()
        );
    }

    public function testSubDaysChains(): void
    {
        Assert::same(
            '2026-04-16',
            $this->date('2026-04-21')->subDays(5)->toDateString()
        );
    }

    public function testSubWeeksChains(): void
    {
        Assert::same(
            '2026-04-14',
            $this->date('2026-04-21')->subWeeks(1)->toDateString()
        );
    }

    public function testSubMonthsChains(): void
    {
        Assert::same(
            '2026-04-15',
            $this->date('2026-06-15')->subMonths(2)->toDateString()
        );
    }

    public function testSubYearsChains(): void
    {
        Assert::same(
            '2016-04-21',
            $this->date('2026-04-21')->subYears(10)->toDateString()
        );
    }

    // -------------------------------------------------------------------------
    // Boundaries
    // -------------------------------------------------------------------------

    public function testStartOfDayChains(): void
    {
        Assert::same(
            '2026-04-21 00:00:00',
            $this->date('2026-04-21 14:30:45')->startOfDay()->toDateTimeString()
        );
    }

    public function testEndOfDayChains(): void
    {
        Assert::same(
            '2026-04-21 23:59:59',
            $this->date('2026-04-21 14:30:45')->endOfDay()->toDateTimeString()
        );
    }

    public function testStartOfWeekChains(): void
    {
        // 2026-04-21 is Tuesday → Monday 2026-04-20
        Assert::same(
            '2026-04-20 00:00:00',
            $this->date('2026-04-21 14:00:00')->startOfWeek()->toDateTimeString()
        );
    }

    public function testEndOfWeekChains(): void
    {
        // Sunday of that week is 2026-04-26
        Assert::same(
            '2026-04-26 23:59:59',
            $this->date('2026-04-21 14:00:00')->endOfWeek()->toDateTimeString()
        );
    }

    public function testStartOfMonthChains(): void
    {
        Assert::same(
            '2026-04-01 00:00:00',
            $this->date('2026-04-21 14:30:00')->startOfMonth()->toDateTimeString()
        );
    }

    public function testEndOfMonthChains(): void
    {
        Assert::same(
            '2026-04-30 23:59:59',
            $this->date('2026-04-15 14:30:00')->endOfMonth()->toDateTimeString()
        );
    }

    public function testStartOfYearChains(): void
    {
        Assert::same(
            '2026-01-01 00:00:00',
            $this->date('2026-09-15 10:00:00')->startOfYear()->toDateTimeString()
        );
    }

    public function testEndOfYearChains(): void
    {
        Assert::same(
            '2026-12-31 23:59:59',
            $this->date('2026-04-21 14:00:00')->endOfYear()->toDateTimeString()
        );
    }

    // -------------------------------------------------------------------------
    // Formatting
    // -------------------------------------------------------------------------

    public function testFormatAppliesPattern(): void
    {
        Assert::same('21/04/2026', $this->date('2026-04-21 14:30:00')->format('d/m/Y'));
    }

    public function testToDateStringReturnsYmd(): void
    {
        Assert::same('2026-04-21', $this->date('2026-04-21 14:30:00')->toDateString());
    }

    public function testToTimeStringReturnsHis(): void
    {
        Assert::same('14:30:45', $this->date('2026-04-21 14:30:45')->toTimeString());
    }

    public function testToDateTimeStringReturnsFull(): void
    {
        Assert::same('2026-04-21 14:30:45', $this->date('2026-04-21 14:30:45')->toDateTimeString());
    }

    public function testToIso8601ContainsOffset(): void
    {
        Assert::same('2026-04-21T12:00:00+00:00', $this->date('2026-04-21 12:00:00')->toIso8601());
    }

    public function testToRfc2822ContainsDayName(): void
    {
        Assert::contains('Tue, 21 Apr 2026', $this->date('2026-04-21 00:00:00')->toRfc2822());
    }

    public function testToUnixTimestampReturnsZeroForEpoch(): void
    {
        $fluent = FluentDates::from(Dates::fromTimestamp(0, 'UTC'));
        Assert::same(0, $fluent->toUnixTimestamp());
    }

    // -------------------------------------------------------------------------
    // Inspection
    // -------------------------------------------------------------------------

    public function testGetYearReturnsYear(): void
    {
        Assert::same(2026, $this->date('2026-04-21')->getYear());
    }

    public function testGetMonthReturnsMonth(): void
    {
        Assert::same(4, $this->date('2026-04-21')->getMonth());
    }

    public function testGetDayReturnsDay(): void
    {
        Assert::same(21, $this->date('2026-04-21')->getDay());
    }

    public function testGetHourReturnsHour(): void
    {
        Assert::same(14, $this->date('2026-04-21 14:30:00')->getHour());
    }

    public function testGetMinuteReturnsMinute(): void
    {
        Assert::same(30, $this->date('2026-04-21 14:30:00')->getMinute());
    }

    public function testGetSecondReturnsSecond(): void
    {
        Assert::same(45, $this->date('2026-04-21 14:30:45')->getSecond());
    }

    public function testGetDayOfWeekReturnsTuesdayAsTwo(): void
    {
        Assert::same(2, $this->date('2026-04-21')->getDayOfWeek());
    }

    public function testGetDayOfYearCountsFromOne(): void
    {
        Assert::same(1, $this->date('2026-01-01')->getDayOfYear());
    }

    public function testGetWeekOfYearReturnsCorrectWeek(): void
    {
        Assert::same(2, $this->date('2026-01-05')->getWeekOfYear());
    }

    public function testGetDaysInMonthCountsCorrectly(): void
    {
        Assert::same(30, $this->date('2026-04-01')->getDaysInMonth());
    }

    public function testGetTimezoneReturnsName(): void
    {
        Assert::same('Asia/Tokyo', $this->date('2026-04-21', 'Asia/Tokyo')->getTimezone());
    }

    // -------------------------------------------------------------------------
    // Boolean checks
    // -------------------------------------------------------------------------

    public function testIsLeapYearDetectsLeapYear(): void
    {
        Assert::true($this->date('2024-01-01')->isLeapYear());
        Assert::false($this->date('2026-01-01')->isLeapYear());
    }

    public function testIsTodayMatchesCurrentDate(): void
    {
        Assert::true(FluentDates::from(new DateTimeImmutable('today', new \DateTimeZone('UTC')))->isToday());
        Assert::false($this->date('2020-01-01')->isToday());
    }

    public function testIsYesterdayMatchesYesterday(): void
    {
        Assert::true(FluentDates::from(new DateTimeImmutable('yesterday', new \DateTimeZone('UTC')))->isYesterday());
    }

    public function testIsTomorrowMatchesTomorrow(): void
    {
        Assert::true(FluentDates::from(new DateTimeImmutable('tomorrow', new \DateTimeZone('UTC')))->isTomorrow());
    }

    public function testIsPastReturnsTrueForOldDate(): void
    {
        Assert::true($this->date('2020-01-01')->isPast());
        Assert::false($this->date('2099-01-01')->isPast());
    }

    public function testIsFutureReturnsTrueForFutureDate(): void
    {
        Assert::true($this->date('2099-01-01')->isFuture());
        Assert::false($this->date('2020-01-01')->isFuture());
    }

    public function testIsWeekendIdentifiesSaturdayAndSunday(): void
    {
        Assert::true($this->date('2026-04-18')->isWeekend());
        Assert::false($this->date('2026-04-21')->isWeekend());
    }

    public function testIsWeekdayIsOppositeOfIsWeekend(): void
    {
        Assert::true($this->date('2026-04-21')->isWeekday());
        Assert::false($this->date('2026-04-18')->isWeekday());
    }

    // -------------------------------------------------------------------------
    // Comparison
    // -------------------------------------------------------------------------

    public function testIsBeforeReturnsTrueForEarlierDate(): void
    {
        $later = Dates::parse('2026-12-31', 'UTC');
        Assert::true($this->date('2026-01-01')->isBefore($later));
        Assert::false(FluentDates::from($later)->isBefore(Dates::parse('2026-01-01', 'UTC')));
    }

    public function testIsAfterReturnsTrueForLaterDate(): void
    {
        $earlier = Dates::parse('2026-01-01', 'UTC');
        Assert::true($this->date('2026-12-31')->isAfter($earlier));
    }

    public function testEqualsComparesTimestamps(): void
    {
        $ny = Dates::parse('2026-04-21 08:00:00', 'America/New_York');
        Assert::true($this->date('2026-04-21 12:00:00')->equals($ny));
    }

    public function testIsSameDayMatchesCalendarDay(): void
    {
        $evening = Dates::parse('2026-04-21 22:00:00', 'UTC');
        Assert::true($this->date('2026-04-21 08:00:00')->isSameDay($evening));

        $tomorrow = Dates::parse('2026-04-22', 'UTC');
        Assert::false($this->date('2026-04-21')->isSameDay($tomorrow));
    }

    public function testIsSameMonthMatchesYearAndMonth(): void
    {
        Assert::true($this->date('2026-04-01')->isSameMonth(Dates::parse('2026-04-30', 'UTC')));
        Assert::false($this->date('2026-04-01')->isSameMonth(Dates::parse('2026-05-01', 'UTC')));
    }

    public function testIsSameYearMatchesYear(): void
    {
        Assert::true($this->date('2026-01-01')->isSameYear(Dates::parse('2026-12-31', 'UTC')));
        Assert::false($this->date('2026-01-01')->isSameYear(Dates::parse('2027-01-01', 'UTC')));
    }

    // -------------------------------------------------------------------------
    // Difference
    // -------------------------------------------------------------------------

    public function testDiffInSecondsIsAbsolute(): void
    {
        $other = Dates::parse('2026-04-21 14:01:30', 'UTC');
        Assert::same(90, $this->date('2026-04-21 14:00:00')->diffInSeconds($other));
    }

    public function testDiffInMinutesDropsPartialMinutes(): void
    {
        $other = Dates::parse('2026-04-21 15:30:00', 'UTC');
        Assert::same(90, $this->date('2026-04-21 14:00:00')->diffInMinutes($other));
    }

    public function testDiffInHoursDropsPartialHours(): void
    {
        $other = Dates::parse('2026-04-21 20:30:00', 'UTC');
        Assert::same(12, $this->date('2026-04-21 08:00:00')->diffInHours($other));
    }

    public function testDiffInDaysDropsPartialDays(): void
    {
        $other = Dates::parse('2026-04-21 00:00:00', 'UTC');
        Assert::same(20, $this->date('2026-04-01 00:00:00')->diffInDays($other));
    }

    public function testDiffInWeeksDropsPartialWeeks(): void
    {
        $other = Dates::parse('2026-04-21 00:00:00', 'UTC');
        Assert::same(2, $this->date('2026-04-07 00:00:00')->diffInWeeks($other));
    }

    public function testDiffInMonthsUsesCalendarMonths(): void
    {
        $other = Dates::parse('2026-04-01 00:00:00', 'UTC');
        Assert::same(3, $this->date('2026-01-01 00:00:00')->diffInMonths($other));
    }

    public function testDiffInYearsUsesCalendarYears(): void
    {
        $other = Dates::parse('2026-01-01 00:00:00', 'UTC');
        Assert::same(6, $this->date('2020-01-01 00:00:00')->diffInYears($other));
    }

    // -------------------------------------------------------------------------
    // Chaining
    // -------------------------------------------------------------------------

    public function testFullChain(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'UTC')
            ->addDays(10)
            ->subHours(2)
            ->startOfDay()
            ->toDateTimeString();

        Assert::same('2026-05-01 00:00:00', $result);
    }

    public function testChainPreservesTimezone(): void
    {
        $tz = Dates::of('2026-04-21 12:00:00', 'UTC')
            ->toTimezone('Asia/Tokyo')
            ->addDays(1)
            ->getTimezone();

        Assert::same('Asia/Tokyo', $tz);
    }
}

(new DatesTest())->run();
