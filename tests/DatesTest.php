<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use DateTimeImmutable;
use Phuture\Coherence\Dates;
use Phuture\Coherence\Exception\InvalidArgumentException;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

class DatesTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Creation
    // -------------------------------------------------------------------------

    public function testNowReturnsCurrentDateTime(): void
    {
        $before = new DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $now = Dates::now('UTC');
        $after = new DateTimeImmutable('now', new \DateTimeZone('UTC'));

        Assert::true($now->getTimestamp() >= $before->getTimestamp());
        Assert::true($now->getTimestamp() <= $after->getTimestamp());
    }

    public function testNowRespectsTimezone(): void
    {
        $now = Dates::now('Asia/Tokyo');
        Assert::same('Asia/Tokyo', $now->getTimezone()->getName());
    }

    public function testNowWithInvalidTimezoneThrows(): void
    {
        Assert::exception(
            fn() => Dates::now('Not/ATimezone'),
            InvalidArgumentException::class
        );
    }

    public function testCreateBuildsDateFromComponents(): void
    {
        $date = Dates::create(2026, 4, 21, 14, 30, 0, 'UTC');
        Assert::same('2026-04-21 14:30:00', $date->format('Y-m-d H:i:s'));
    }

    public function testCreateDefaultsToMidnight(): void
    {
        $date = Dates::create(2026, 4, 21, timezone: 'UTC');
        Assert::same('2026-04-21 00:00:00', $date->format('Y-m-d H:i:s'));
    }

    public function testCreateRespectsTimezone(): void
    {
        $date = Dates::create(2026, 4, 21, 12, 0, 0, 'America/New_York');
        Assert::same('America/New_York', $date->getTimezone()->getName());
    }

    public function testParseHandlesDateString(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        Assert::same('2026-04-21', $date->format('Y-m-d'));
    }

    public function testParseHandlesDateTimeString(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('2026-04-21 14:30:00', $date->format('Y-m-d H:i:s'));
    }

    public function testParseRespectsTimezone(): void
    {
        $date = Dates::parse('2026-04-21 12:00:00', 'Europe/Berlin');
        Assert::same('Europe/Berlin', $date->getTimezone()->getName());
    }

    public function testFromTimestampBuildsDate(): void
    {
        $date = Dates::fromTimestamp(0, 'UTC');
        Assert::same('1970-01-01 00:00:00', $date->format('Y-m-d H:i:s'));
    }

    public function testFromTimestampRespectsTimezone(): void
    {
        $date = Dates::fromTimestamp(0, 'America/New_York');
        Assert::same('America/New_York', $date->getTimezone()->getName());
        Assert::same('1969-12-31 19:00:00', $date->format('Y-m-d H:i:s'));
    }

    public function testFromFormatParsesCorrectly(): void
    {
        $date = Dates::fromFormat('d/m/Y', '21/04/2026', 'UTC');
        Assert::same('2026-04-21', $date->format('Y-m-d'));
    }

    public function testFromFormatThrowsOnMismatch(): void
    {
        Assert::exception(
            fn() => Dates::fromFormat('Y-m-d', 'not-a-date', 'UTC'),
            InvalidArgumentException::class
        );
    }

    // -------------------------------------------------------------------------
    // Timezone
    // -------------------------------------------------------------------------

    public function testToTimezonePreservesMoment(): void
    {
        $utc = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $converted = Dates::toTimezone($utc, 'America/New_York');

        Assert::same($utc->getTimestamp(), $converted->getTimestamp());
        Assert::same('America/New_York', $converted->getTimezone()->getName());
        Assert::same('2026-04-21 08:00:00', $converted->format('Y-m-d H:i:s'));
    }

    public function testGetTimezoneReturnsName(): void
    {
        $date = Dates::parse('2026-04-21', 'Asia/Tokyo');
        Assert::same('Asia/Tokyo', Dates::getTimezone($date));
    }

    // -------------------------------------------------------------------------
    // Formatting
    // -------------------------------------------------------------------------

    public function testFormatAppliesPattern(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('21/04/2026', Dates::format($date, 'd/m/Y'));
        Assert::same('14:30', Dates::format($date, 'H:i'));
    }

    public function testToDateStringReturnsYmd(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('2026-04-21', Dates::toDateString($date));
    }

    public function testToTimeStringReturnsHis(): void
    {
        $date = Dates::parse('2026-04-21 14:30:45', 'UTC');
        Assert::same('14:30:45', Dates::toTimeString($date));
    }

    public function testToDateTimeStringReturnsFull(): void
    {
        $date = Dates::parse('2026-04-21 14:30:45', 'UTC');
        Assert::same('2026-04-21 14:30:45', Dates::toDateTimeString($date));
    }

    public function testToIso8601ContainsOffset(): void
    {
        $date = Dates::parse('2026-04-21 12:00:00', 'UTC');
        Assert::same('2026-04-21T12:00:00+00:00', Dates::toIso8601($date));
    }

    public function testToRfc2822ContainsDayName(): void
    {
        $date = Dates::parse('2026-04-21 00:00:00', 'UTC');
        Assert::contains('Tue, 21 Apr 2026', Dates::toRfc2822($date));
    }

    public function testToUnixTimestampReturnsZeroForEpoch(): void
    {
        $date = Dates::fromTimestamp(0, 'UTC');
        Assert::same(0, Dates::toUnixTimestamp($date));
    }

    // -------------------------------------------------------------------------
    // Addition
    // -------------------------------------------------------------------------

    public function testAddSecondsIncreasesTime(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::addSeconds($date, 90);
        Assert::same('2026-04-21 14:31:30', Dates::toDateTimeString($result));
    }

    public function testAddMinutesIncreasesTime(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::addMinutes($date, 45);
        Assert::same('2026-04-21 15:15:00', Dates::toDateTimeString($result));
    }

    public function testAddHoursIncreasesTime(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::addHours($date, 3);
        Assert::same('2026-04-21 17:30:00', Dates::toDateTimeString($result));
    }

    public function testAddDaysIncreasesDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $result = Dates::addDays($date, 10);
        Assert::same('2026-05-01', Dates::toDateString($result));
    }

    public function testAddWeeksIncreasesDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $result = Dates::addWeeks($date, 2);
        Assert::same('2026-05-05', Dates::toDateString($result));
    }

    public function testAddMonthsIncreasesDate(): void
    {
        $date = Dates::parse('2026-01-15', 'UTC');
        $result = Dates::addMonths($date, 3);
        Assert::same('2026-04-15', Dates::toDateString($result));
    }

    public function testAddYearsIncreasesDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $result = Dates::addYears($date, 5);
        Assert::same('2031-04-21', Dates::toDateString($result));
    }

    public function testAddDoesNotMutateOriginal(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        Dates::addDays($date, 10);
        Assert::same('2026-04-21', Dates::toDateString($date));
    }

    // -------------------------------------------------------------------------
    // Subtraction
    // -------------------------------------------------------------------------

    public function testSubSecondsDecreasesTime(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::subSeconds($date, 30);
        Assert::same('2026-04-21 14:29:30', Dates::toDateTimeString($result));
    }

    public function testSubMinutesDecreasesTime(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::subMinutes($date, 15);
        Assert::same('2026-04-21 14:15:00', Dates::toDateTimeString($result));
    }

    public function testSubHoursDecreasesTime(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::subHours($date, 2);
        Assert::same('2026-04-21 12:30:00', Dates::toDateTimeString($result));
    }

    public function testSubDaysDecreasesDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $result = Dates::subDays($date, 5);
        Assert::same('2026-04-16', Dates::toDateString($result));
    }

    public function testSubWeeksDecreasesDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $result = Dates::subWeeks($date, 1);
        Assert::same('2026-04-14', Dates::toDateString($result));
    }

    public function testSubMonthsDecreasesDate(): void
    {
        $date = Dates::parse('2026-06-15', 'UTC');
        $result = Dates::subMonths($date, 2);
        Assert::same('2026-04-15', Dates::toDateString($result));
    }

    public function testSubYearsDecreasesDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $result = Dates::subYears($date, 10);
        Assert::same('2016-04-21', Dates::toDateString($result));
    }

    // -------------------------------------------------------------------------
    // Comparison
    // -------------------------------------------------------------------------

    public function testIsBeforeReturnsTrueForEarlierDate(): void
    {
        $earlier = Dates::parse('2026-01-01', 'UTC');
        $later = Dates::parse('2026-12-31', 'UTC');
        Assert::true(Dates::isBefore($earlier, $later));
        Assert::false(Dates::isBefore($later, $earlier));
    }

    public function testIsAfterReturnsTrueForLaterDate(): void
    {
        $later = Dates::parse('2026-12-31', 'UTC');
        $earlier = Dates::parse('2026-01-01', 'UTC');
        Assert::true(Dates::isAfter($later, $earlier));
        Assert::false(Dates::isAfter($earlier, $later));
    }

    public function testEqualsComparesTimestamps(): void
    {
        $utc = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $ny = Dates::parse('2026-04-21 08:00:00', 'America/New_York');
        Assert::true(Dates::equals($utc, $ny));

        $different = Dates::parse('2026-04-21 13:00:00', 'UTC');
        Assert::false(Dates::equals($utc, $different));
    }

    public function testIsSameDayMatchesCalendarDay(): void
    {
        $morning = Dates::parse('2026-04-21 08:00:00', 'UTC');
        $evening = Dates::parse('2026-04-21 22:00:00', 'UTC');
        $tomorrow = Dates::parse('2026-04-22 00:00:00', 'UTC');

        Assert::true(Dates::isSameDay($morning, $evening));
        Assert::false(Dates::isSameDay($morning, $tomorrow));
    }

    public function testIsSameMonthMatchesYearAndMonth(): void
    {
        $first = Dates::parse('2026-04-01', 'UTC');
        $last = Dates::parse('2026-04-30', 'UTC');
        $next = Dates::parse('2026-05-01', 'UTC');

        Assert::true(Dates::isSameMonth($first, $last));
        Assert::false(Dates::isSameMonth($first, $next));
    }

    public function testIsSameYearMatchesYear(): void
    {
        $jan = Dates::parse('2026-01-01', 'UTC');
        $dec = Dates::parse('2026-12-31', 'UTC');
        $nextYear = Dates::parse('2027-01-01', 'UTC');

        Assert::true(Dates::isSameYear($jan, $dec));
        Assert::false(Dates::isSameYear($jan, $nextYear));
    }

    // -------------------------------------------------------------------------
    // Difference
    // -------------------------------------------------------------------------

    public function testDiffInSecondsIsAbsolute(): void
    {
        $start = Dates::parse('2026-04-21 14:00:00', 'UTC');
        $end = Dates::parse('2026-04-21 14:01:30', 'UTC');

        Assert::same(90, Dates::diffInSeconds($start, $end));
        Assert::same(90, Dates::diffInSeconds($end, $start));
    }

    public function testDiffInMinutesDropsPartialMinutes(): void
    {
        $start = Dates::parse('2026-04-21 14:00:00', 'UTC');
        $end = Dates::parse('2026-04-21 15:30:00', 'UTC');
        Assert::same(90, Dates::diffInMinutes($start, $end));
    }

    public function testDiffInHoursDropsPartialHours(): void
    {
        $start = Dates::parse('2026-04-21 08:00:00', 'UTC');
        $end = Dates::parse('2026-04-21 20:30:00', 'UTC');
        Assert::same(12, Dates::diffInHours($start, $end));
    }

    public function testDiffInDaysDropsPartialDays(): void
    {
        $start = Dates::parse('2026-04-01 00:00:00', 'UTC');
        $end = Dates::parse('2026-04-21 00:00:00', 'UTC');
        Assert::same(20, Dates::diffInDays($start, $end));
    }

    public function testDiffInWeeksDropsPartialWeeks(): void
    {
        $start = Dates::parse('2026-04-07 00:00:00', 'UTC');
        $end = Dates::parse('2026-04-21 00:00:00', 'UTC');
        Assert::same(2, Dates::diffInWeeks($start, $end));
    }

    public function testDiffInMonthsUsesCalendarMonths(): void
    {
        $start = Dates::parse('2026-01-01 00:00:00', 'UTC');
        $end = Dates::parse('2026-04-01 00:00:00', 'UTC');
        Assert::same(3, Dates::diffInMonths($start, $end));
    }

    public function testDiffInYearsUsesCalendarYears(): void
    {
        $start = Dates::parse('2020-01-01 00:00:00', 'UTC');
        $end = Dates::parse('2026-01-01 00:00:00', 'UTC');
        Assert::same(6, Dates::diffInYears($start, $end));
    }

    // -------------------------------------------------------------------------
    // Inspection — components
    // -------------------------------------------------------------------------

    public function testGetYearReturnsYear(): void
    {
        Assert::same(2026, Dates::getYear(Dates::parse('2026-04-21', 'UTC')));
    }

    public function testGetMonthReturnsMonth(): void
    {
        Assert::same(4, Dates::getMonth(Dates::parse('2026-04-21', 'UTC')));
    }

    public function testGetDayReturnsDay(): void
    {
        Assert::same(21, Dates::getDay(Dates::parse('2026-04-21', 'UTC')));
    }

    public function testGetHourReturnsHour(): void
    {
        Assert::same(14, Dates::getHour(Dates::parse('2026-04-21 14:30:00', 'UTC')));
    }

    public function testGetMinuteReturnsMinute(): void
    {
        Assert::same(30, Dates::getMinute(Dates::parse('2026-04-21 14:30:00', 'UTC')));
    }

    public function testGetSecondReturnsSecond(): void
    {
        Assert::same(45, Dates::getSecond(Dates::parse('2026-04-21 14:30:45', 'UTC')));
    }

    public function testGetDayOfWeekReturnsTuesdayAsTwo(): void
    {
        // 2026-04-21 is a Tuesday
        Assert::same(2, Dates::getDayOfWeek(Dates::parse('2026-04-21', 'UTC')));
    }

    public function testGetDayOfWeekReturnsSundayAsSeven(): void
    {
        // 2026-04-19 is a Sunday
        Assert::same(7, Dates::getDayOfWeek(Dates::parse('2026-04-19', 'UTC')));
    }

    public function testGetDayOfYearCountsFromOne(): void
    {
        Assert::same(1, Dates::getDayOfYear(Dates::parse('2026-01-01', 'UTC')));
        Assert::same(31, Dates::getDayOfYear(Dates::parse('2026-01-31', 'UTC')));
    }

    public function testGetWeekOfYearReturnsCorrectWeek(): void
    {
        // 2026-01-05 is in ISO week 2
        Assert::same(2, Dates::getWeekOfYear(Dates::parse('2026-01-05', 'UTC')));
    }

    public function testGetDaysInMonthCountsCorrectly(): void
    {
        Assert::same(30, Dates::getDaysInMonth(Dates::parse('2026-04-01', 'UTC')));
        Assert::same(31, Dates::getDaysInMonth(Dates::parse('2026-01-01', 'UTC')));
        Assert::same(28, Dates::getDaysInMonth(Dates::parse('2026-02-01', 'UTC')));
        Assert::same(29, Dates::getDaysInMonth(Dates::parse('2024-02-01', 'UTC')));
    }

    // -------------------------------------------------------------------------
    // Inspection — boolean checks
    // -------------------------------------------------------------------------

    public function testIsLeapYearDetectsLeapYears(): void
    {
        Assert::true(Dates::isLeapYear(Dates::parse('2024-01-01', 'UTC')));
        Assert::false(Dates::isLeapYear(Dates::parse('2026-01-01', 'UTC')));
        Assert::false(Dates::isLeapYear(Dates::parse('1900-01-01', 'UTC')));
        Assert::true(Dates::isLeapYear(Dates::parse('2000-01-01', 'UTC')));
    }

    public function testIsTodayMatchesTodayOnly(): void
    {
        Assert::true(Dates::isToday(new DateTimeImmutable('today', new \DateTimeZone('UTC'))));
        Assert::false(Dates::isToday(Dates::parse('2020-01-01', 'UTC')));
    }

    public function testIsYesterdayMatchesYesterdayOnly(): void
    {
        Assert::true(Dates::isYesterday(new DateTimeImmutable('yesterday', new \DateTimeZone('UTC'))));
        Assert::false(Dates::isYesterday(new DateTimeImmutable('today', new \DateTimeZone('UTC'))));
    }

    public function testIsTomorrowMatchesTomorrowOnly(): void
    {
        Assert::true(Dates::isTomorrow(new DateTimeImmutable('tomorrow', new \DateTimeZone('UTC'))));
        Assert::false(Dates::isTomorrow(new DateTimeImmutable('today', new \DateTimeZone('UTC'))));
    }

    public function testIsPastReturnsTrueForOldDate(): void
    {
        Assert::true(Dates::isPast(Dates::parse('2020-01-01', 'UTC')));
        Assert::false(Dates::isPast(Dates::parse('2099-01-01', 'UTC')));
    }

    public function testIsFutureReturnsTrueForFutureDate(): void
    {
        Assert::true(Dates::isFuture(Dates::parse('2099-01-01', 'UTC')));
        Assert::false(Dates::isFuture(Dates::parse('2020-01-01', 'UTC')));
    }

    public function testIsWeekendIdentifiesSaturdayAndSunday(): void
    {
        // 2026-04-18 Saturday, 2026-04-19 Sunday, 2026-04-21 Tuesday
        Assert::true(Dates::isWeekend(Dates::parse('2026-04-18', 'UTC')));
        Assert::true(Dates::isWeekend(Dates::parse('2026-04-19', 'UTC')));
        Assert::false(Dates::isWeekend(Dates::parse('2026-04-21', 'UTC')));
    }

    public function testIsWeekdayIsOppositeOfIsWeekend(): void
    {
        Assert::false(Dates::isWeekday(Dates::parse('2026-04-18', 'UTC')));
        Assert::true(Dates::isWeekday(Dates::parse('2026-04-21', 'UTC')));
    }

    // -------------------------------------------------------------------------
    // Boundaries
    // -------------------------------------------------------------------------

    public function testStartOfDaySetsToMidnight(): void
    {
        $date = Dates::parse('2026-04-21 14:30:45', 'UTC');
        $result = Dates::startOfDay($date);
        Assert::same('2026-04-21 00:00:00', Dates::toDateTimeString($result));
    }

    public function testEndOfDaySetsTo235959(): void
    {
        $date = Dates::parse('2026-04-21 14:30:45', 'UTC');
        $result = Dates::endOfDay($date);
        Assert::same('2026-04-21 23:59:59', Dates::toDateTimeString($result));
    }

    public function testStartOfWeekSetsToMonday(): void
    {
        // 2026-04-21 is Tuesday, so Monday of that week is 2026-04-20
        $date = Dates::parse('2026-04-21 14:00:00', 'UTC');
        $result = Dates::startOfWeek($date);
        Assert::same('2026-04-20 00:00:00', Dates::toDateTimeString($result));
    }

    public function testEndOfWeekSetsToSunday(): void
    {
        // Sunday of 2026-04-21's week is 2026-04-26
        $date = Dates::parse('2026-04-21 14:00:00', 'UTC');
        $result = Dates::endOfWeek($date);
        Assert::same('2026-04-26 23:59:59', Dates::toDateTimeString($result));
    }

    public function testStartOfMonthSetsToFirstDay(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::startOfMonth($date);
        Assert::same('2026-04-01 00:00:00', Dates::toDateTimeString($result));
    }

    public function testEndOfMonthSetsToLastDay(): void
    {
        $date = Dates::parse('2026-04-15 14:30:00', 'UTC');
        $result = Dates::endOfMonth($date);
        Assert::same('2026-04-30 23:59:59', Dates::toDateTimeString($result));
    }

    public function testEndOfMonthHandlesFebruary(): void
    {
        $regular = Dates::parse('2026-02-10', 'UTC');
        Assert::same('2026-02-28', Dates::toDateString(Dates::endOfMonth($regular)));

        $leap = Dates::parse('2024-02-10', 'UTC');
        Assert::same('2024-02-29', Dates::toDateString(Dates::endOfMonth($leap)));
    }

    public function testStartOfYearSetsToJanuaryFirst(): void
    {
        $date = Dates::parse('2026-09-15 10:00:00', 'UTC');
        $result = Dates::startOfYear($date);
        Assert::same('2026-01-01 00:00:00', Dates::toDateTimeString($result));
    }

    public function testEndOfYearSetsToDecemberThirtyFirst(): void
    {
        $date = Dates::parse('2026-04-21 14:00:00', 'UTC');
        $result = Dates::endOfYear($date);
        Assert::same('2026-12-31 23:59:59', Dates::toDateTimeString($result));
    }

    // -------------------------------------------------------------------------
    // Fluent entry point
    // -------------------------------------------------------------------------

    public function testOfReturnsFluentWrapper(): void
    {
        $fluent = Dates::of('2026-04-21 14:30:00', 'UTC');
        Assert::type(\Phuture\Coherence\Type\Dates::class, $fluent);
    }

    public function testOfAcceptsDateTimeImmutable(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $fluent = Dates::of($date);
        Assert::same('2026-04-21', $fluent->toDateString());
    }

    public function testFluentChaining(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'UTC')
            ->addDays(10)
            ->startOfDay()
            ->toDateTimeString();

        Assert::same('2026-05-01 00:00:00', $result);
    }
}

(new DatesTest())->run();
