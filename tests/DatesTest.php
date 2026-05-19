<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use DateTimeZone;
use DateTimeImmutable;
use Phuture\Coherence\Dates;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Exception\InvalidArgumentException;

require __DIR__ . '/bootstrap.php';

class DatesTest extends TestCase
{
    public function testAddBusinessDaysAcceptsStringDate(): void
    {
        $result = Dates::addBusinessDays('2026-04-20', 3);
        // Mon Apr 20 + 3 business days = Thu Apr 23
        Assert::same('2026-04-23', Dates::toDate($result));
    }

    public function testAddBusinessDaysMultiWeek(): void
    {
        // Mon Apr 20 + 10 = Mon May 4 (skips 2 weekends)
        $date = Dates::parse('2026-04-20', 'UTC');
        $result = Dates::addBusinessDays($date, 10);
        Assert::same('2026-05-04', Dates::toDate($result));
    }

    public function testAddBusinessDaysSkipsWeekends(): void
    {
        // Wed Apr 22 + 3 = Mon Apr 27 (skips Sat 25, Sun 26)
        $date = Dates::parse('2026-04-22', 'UTC');
        $result = Dates::addBusinessDays($date, 3);
        Assert::same('2026-04-27', Dates::toDate($result));
    }

    public function testAddBusinessDaysStartingOnSaturday(): void
    {
        // Sat Apr 18 + 1 = Mon Apr 20
        $date = Dates::parse('2026-04-18', 'UTC');
        $result = Dates::addBusinessDays($date, 1);
        Assert::same('2026-04-20', Dates::toDate($result));
    }

    public function testAddBusinessDaysStartingOnSunday(): void
    {
        // Sun Apr 19 + 1 = Mon Apr 20
        $date = Dates::parse('2026-04-19', 'UTC');
        $result = Dates::addBusinessDays($date, 1);
        Assert::same('2026-04-20', Dates::toDate($result));
    }

    public function testAddBusinessDaysWithNegativeMovesBackward(): void
    {
        // Wed Apr 22 - 3 = Fri Apr 17 (skips Sat 18, Sun 19)
        $date = Dates::parse('2026-04-22', 'UTC');
        $result = Dates::addBusinessDays($date, -3);
        Assert::same('2026-04-17', Dates::toDate($result));
    }

    public function testAddBusinessDaysWithZeroIsNoop(): void
    {
        $date = Dates::parse('2026-04-22', 'UTC');
        $result = Dates::addBusinessDays($date, 0);
        Assert::same('2026-04-22', Dates::toDate($result));
    }
    public function testAddDaysAcceptsStringDate(): void
    {
        $result = Dates::addDays('2026-04-21', 10);
        Assert::same('2026-05-01', Dates::toDate($result));
    }

    public function testAddDaysIncreasesDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $result = Dates::addDays($date, 10);
        Assert::same('2026-05-01', Dates::toDate($result));
    }

    public function testAddDoesNotMutateOriginal(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        Dates::addDays($date, 10);
        Assert::same('2026-04-21', Dates::toDate($date));
    }

    public function testAddHoursAcceptsStringDate(): void
    {
        $result = Dates::addHours('2026-04-21 14:30:00', 3);
        Assert::same('2026-04-21 17:30:00', Dates::toDateTime($result));
    }

    public function testAddHoursIncreasesTime(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::addHours($date, 3);
        Assert::same('2026-04-21 17:30:00', Dates::toDateTime($result));
    }

    public function testAddMinutesAcceptsStringDate(): void
    {
        $result = Dates::addMinutes('2026-04-21 14:30:00', 45);
        Assert::same('2026-04-21 15:15:00', Dates::toDateTime($result));
    }

    public function testAddMinutesIncreasesTime(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::addMinutes($date, 45);
        Assert::same('2026-04-21 15:15:00', Dates::toDateTime($result));
    }

    public function testAddMonthsAcceptsStringDate(): void
    {
        $result = Dates::addMonths('2026-01-15', 3);
        Assert::same('2026-04-15', Dates::toDate($result));
    }

    public function testAddMonthsIncreasesDate(): void
    {
        $date = Dates::parse('2026-01-15', 'UTC');
        $result = Dates::addMonths($date, 3);
        Assert::same('2026-04-15', Dates::toDate($result));
    }

    public function testAddSecondsAcceptsStringDate(): void
    {
        $result = Dates::addSeconds('2026-04-21 14:30:00', 90);
        Assert::same('2026-04-21 14:31:30', Dates::toDateTime($result));
    }

    public function testAddSecondsIncreasesTime(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::addSeconds($date, 90);
        Assert::same('2026-04-21 14:31:30', Dates::toDateTime($result));
    }

    public function testAddSecondsWithNegativeGoesBackward(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::addSeconds($date, -30);
        Assert::same('2026-04-21 14:29:30', Dates::toDateTime($result));
    }

    public function testAddSecondsWithZeroIsNoop(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('2026-04-21 14:30:00', Dates::toDateTime(Dates::addSeconds($date, 0)));
    }

    public function testAddWeeksAcceptsStringDate(): void
    {
        $result = Dates::addWeeks('2026-04-21', 2);
        Assert::same('2026-05-05', Dates::toDate($result));
    }

    public function testAddWeeksIncreasesDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $result = Dates::addWeeks($date, 2);
        Assert::same('2026-05-05', Dates::toDate($result));
    }

    public function testAddYearsAcceptsStringDate(): void
    {
        $result = Dates::addYears('2026-04-21', 5);
        Assert::same('2031-04-21', Dates::toDate($result));
    }

    public function testAddYearsIncreasesDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $result = Dates::addYears($date, 5);
        Assert::same('2031-04-21', Dates::toDate($result));
    }

    public function testCreateAcceptsBoundaryValues(): void
    {
        Assert::noError(fn () => Dates::create(2026, 1, 1, 0, 0, 0));
        Assert::noError(fn () => Dates::create(2026, 12, 31, 23, 59, 59));
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

    public function testCreateThrowsOnInvalidDay(): void
    {
        Assert::exception(fn () => Dates::create(2026, 1, 0), InvalidArgumentException::class);
        Assert::exception(fn () => Dates::create(2026, 1, 32), InvalidArgumentException::class);
    }

    public function testCreateThrowsOnInvalidHour(): void
    {
        Assert::exception(fn () => Dates::create(2026, 1, 1, -1), InvalidArgumentException::class);
        Assert::exception(fn () => Dates::create(2026, 1, 1, 24), InvalidArgumentException::class);
    }

    public function testCreateThrowsOnInvalidMinute(): void
    {
        Assert::exception(fn () => Dates::create(2026, 1, 1, 0, -1), InvalidArgumentException::class);
        Assert::exception(fn () => Dates::create(2026, 1, 1, 0, 60), InvalidArgumentException::class);
    }

    public function testCreateThrowsOnInvalidMonth(): void
    {
        Assert::exception(fn () => Dates::create(2026, 0, 1), InvalidArgumentException::class);
        Assert::exception(fn () => Dates::create(2026, 13, 1), InvalidArgumentException::class);
    }

    public function testCreateThrowsOnInvalidSecond(): void
    {
        Assert::exception(fn () => Dates::create(2026, 1, 1, 0, 0, -1), InvalidArgumentException::class);
        Assert::exception(fn () => Dates::create(2026, 1, 1, 0, 0, 60), InvalidArgumentException::class);
    }

    public function testDiffInBusinessDaysAcceptsStringDates(): void
    {
        // Mon Apr 20 -> Sat Apr 25: Tue, Wed, Thu, Fri = 4
        Assert::same(4, Dates::diffInBusinessDays('2026-04-20', '2026-04-25'));
    }

    public function testDiffInBusinessDaysExcludesWeekends(): void
    {
        // Fri Apr 17 -> Mon Apr 20: nothing between = 0
        $start = Dates::parse('2026-04-17', 'UTC');
        $end = Dates::parse('2026-04-20', 'UTC');
        Assert::same(0, Dates::diffInBusinessDays($start, $end));
    }

    public function testDiffInBusinessDaysIsAbsolute(): void
    {
        // Mon Apr 20 -> Fri Apr 24: Tue, Wed, Thu = 3
        $start = Dates::parse('2026-04-20', 'UTC');
        $end = Dates::parse('2026-04-24', 'UTC');
        Assert::same(3, Dates::diffInBusinessDays($end, $start));
    }

    public function testDiffInBusinessDaysMultiWeek(): void
    {
        // Mon Apr 20 -> Mon May 4: 9 business days in between
        $start = Dates::parse('2026-04-20', 'UTC');
        $end = Dates::parse('2026-05-04', 'UTC');
        Assert::same(9, Dates::diffInBusinessDays($start, $end));
    }

    public function testDiffInBusinessDaysZeroForSameDate(): void
    {
        Assert::same(0, Dates::diffInBusinessDays('2026-04-21', '2026-04-21'));
    }

    public function testDiffInDaysAcceptsStringDates(): void
    {
        Assert::same(20, Dates::diffInDays('2026-04-01', '2026-04-21'));
    }

    public function testDiffInDaysDropsPartialDay(): void
    {
        $start = Dates::parse('2026-04-21 00:00:00', 'UTC');
        $end = Dates::parse('2026-04-21 23:59:59', 'UTC');
        Assert::same(0, Dates::diffInDays($start, $end));
    }

    public function testDiffInDaysDropsPartialDays(): void
    {
        $start = Dates::parse('2026-04-01 00:00:00', 'UTC');
        $end = Dates::parse('2026-04-21 00:00:00', 'UTC');
        Assert::same(20, Dates::diffInDays($start, $end));
    }

    public function testDiffInDaysZeroForSameDate(): void
    {
        Assert::same(0, Dates::diffInDays('2026-04-21', '2026-04-21'));
    }

    public function testDiffInHoursAcceptsStringDates(): void
    {
        Assert::same(12, Dates::diffInHours('2026-04-21 08:00:00', '2026-04-21 20:30:00'));
    }

    public function testDiffInHoursDropsPartialHour(): void
    {
        $start = Dates::parse('2026-04-21 14:00:00', 'UTC');
        $end = Dates::parse('2026-04-21 14:45:00', 'UTC');
        Assert::same(0, Dates::diffInHours($start, $end));
    }

    public function testDiffInHoursDropsPartialHours(): void
    {
        $start = Dates::parse('2026-04-21 08:00:00', 'UTC');
        $end = Dates::parse('2026-04-21 20:30:00', 'UTC');
        Assert::same(12, Dates::diffInHours($start, $end));
    }

    public function testDiffInHoursZeroForSameDate(): void
    {
        Assert::same(0, Dates::diffInHours('2026-04-21 14:00:00', '2026-04-21 14:00:00'));
    }

    public function testDiffInMinutesAcceptsStringDates(): void
    {
        Assert::same(90, Dates::diffInMinutes('2026-04-21 14:00:00', '2026-04-21 15:30:00'));
    }

    public function testDiffInMinutesDropsPartialMinute(): void
    {
        $start = Dates::parse('2026-04-21 14:00:00', 'UTC');
        $end = Dates::parse('2026-04-21 14:00:45', 'UTC');
        Assert::same(0, Dates::diffInMinutes($start, $end));
    }

    public function testDiffInMinutesDropsPartialMinutes(): void
    {
        $start = Dates::parse('2026-04-21 14:00:00', 'UTC');
        $end = Dates::parse('2026-04-21 15:30:00', 'UTC');
        Assert::same(90, Dates::diffInMinutes($start, $end));
    }

    public function testDiffInMinutesZeroForSameDate(): void
    {
        Assert::same(0, Dates::diffInMinutes('2026-04-21 14:00:00', '2026-04-21 14:00:00'));
    }

    public function testDiffInMonthsAcceptsStringDates(): void
    {
        Assert::same(3, Dates::diffInMonths('2026-01-01', '2026-04-01'));
    }

    public function testDiffInMonthsPartialMonthNotCounted(): void
    {
        $start = Dates::parse('2026-01-15', 'UTC');
        $end = Dates::parse('2026-04-10', 'UTC');
        Assert::same(2, Dates::diffInMonths($start, $end));
    }

    public function testDiffInMonthsUsesCalendarMonths(): void
    {
        $start = Dates::parse('2026-01-01 00:00:00', 'UTC');
        $end = Dates::parse('2026-04-01 00:00:00', 'UTC');
        Assert::same(3, Dates::diffInMonths($start, $end));
    }

    public function testDiffInMonthsZeroForSameDate(): void
    {
        Assert::same(0, Dates::diffInMonths('2026-04-21', '2026-04-21'));
    }

    public function testDiffInSecondsAcceptsStringDates(): void
    {
        Assert::same(90, Dates::diffInSeconds('2026-04-21 14:00:00', '2026-04-21 14:01:30'));
    }

    public function testDiffInSecondsIsAbsolute(): void
    {
        $start = Dates::parse('2026-04-21 14:00:00', 'UTC');
        $end = Dates::parse('2026-04-21 14:01:30', 'UTC');

        Assert::same(90, Dates::diffInSeconds($start, $end));
        Assert::same(90, Dates::diffInSeconds($end, $start));
    }

    public function testDiffInSecondsIsAlwaysNonNegative(): void
    {
        $earlier = Dates::parse('2026-01-01', 'UTC');
        $later = Dates::parse('2026-12-31', 'UTC');
        $forward = Dates::diffInSeconds($earlier, $later);
        $backward = Dates::diffInSeconds($later, $earlier);
        Assert::same($forward, $backward);
        Assert::true($forward >= 0);
    }

    public function testDiffInSecondsZeroForSameDate(): void
    {
        $date = Dates::parse('2026-04-21 14:00:00', 'UTC');
        Assert::same(0, Dates::diffInSeconds($date, $date));
    }

    public function testDiffInWeeksAcceptsStringDates(): void
    {
        Assert::same(2, Dates::diffInWeeks('2026-04-07', '2026-04-21'));
    }

    public function testDiffInWeeksDropsPartialWeeks(): void
    {
        $start = Dates::parse('2026-04-07 00:00:00', 'UTC');
        $end = Dates::parse('2026-04-21 00:00:00', 'UTC');
        Assert::same(2, Dates::diffInWeeks($start, $end));
    }

    public function testDiffInWeeksZeroForSameDate(): void
    {
        Assert::same(0, Dates::diffInWeeks('2026-04-21', '2026-04-21'));
    }

    public function testDiffInYearsAcceptsStringDates(): void
    {
        Assert::same(6, Dates::diffInYears('2020-01-01', '2026-01-01'));
    }

    public function testDiffInYearsUsesCalendarYears(): void
    {
        $start = Dates::parse('2020-01-01 00:00:00', 'UTC');
        $end = Dates::parse('2026-01-01 00:00:00', 'UTC');
        Assert::same(6, Dates::diffInYears($start, $end));
    }

    public function testDiffInYearsZeroForSameDate(): void
    {
        Assert::same(0, Dates::diffInYears('2026-04-21', '2026-04-21'));
    }

    public function testEndOfDayAcceptsStringDate(): void
    {
        $result = Dates::endOfDay('2026-04-21 14:30:45');
        Assert::same('2026-04-21 23:59:59', Dates::toDateTime($result));
    }

    public function testEndOfDaySetsTo235959(): void
    {
        $date = Dates::parse('2026-04-21 14:30:45', 'UTC');
        $result = Dates::endOfDay($date);
        Assert::same('2026-04-21 23:59:59', Dates::toDateTime($result));
    }

    public function testEndOfMonthFebruaryLeap(): void
    {
        $result = Dates::endOfMonth('2024-02-10');
        Assert::same('2024-02-29 23:59:59', Dates::toDateTime($result));
    }

    public function testEndOfMonthFebruaryNonLeap(): void
    {
        $result = Dates::endOfMonth('2026-02-10');
        Assert::same('2026-02-28 23:59:59', Dates::toDateTime($result));
    }

    public function testEndOfMonthHandlesFebruary(): void
    {
        $regular = Dates::parse('2026-02-10', 'UTC');
        Assert::same('2026-02-28', Dates::toDate(Dates::endOfMonth($regular)));

        $leap = Dates::parse('2024-02-10', 'UTC');
        Assert::same('2024-02-29', Dates::toDate(Dates::endOfMonth($leap)));
    }

    public function testEndOfMonthSetsToLastDay(): void
    {
        $date = Dates::parse('2026-04-15 14:30:00', 'UTC');
        $result = Dates::endOfMonth($date);
        Assert::same('2026-04-30 23:59:59', Dates::toDateTime($result));
    }

    public function testEndOfWeekAcceptsStringDate(): void
    {
        $result = Dates::endOfWeek('2026-04-21 14:00:00');
        Assert::same('2026-04-26 23:59:59', Dates::toDateTime($result));
    }

    public function testEndOfWeekAlreadySunday(): void
    {
        $date = Dates::parse('2026-04-19 09:00:00', 'UTC');
        $result = Dates::endOfWeek($date);
        Assert::same('2026-04-19 23:59:59', Dates::toDateTime($result));
    }

    public function testEndOfWeekSetsToSunday(): void
    {
        $date = Dates::parse('2026-04-21 14:00:00', 'UTC');
        $result = Dates::endOfWeek($date);
        Assert::same('2026-04-26 23:59:59', Dates::toDateTime($result));
    }

    public function testEndOfYearAcceptsStringDate(): void
    {
        $result = Dates::endOfYear('2026-04-21');
        Assert::same('2026-12-31 23:59:59', Dates::toDateTime($result));
    }

    public function testEndOfYearSetsToDecemberThirtyFirst(): void
    {
        $date = Dates::parse('2026-04-21 14:00:00', 'UTC');
        $result = Dates::endOfYear($date);
        Assert::same('2026-12-31 23:59:59', Dates::toDateTime($result));
    }

    public function testEqualsAcceptsStringDates(): void
    {
        Assert::true(Dates::equals('2026-04-21 12:00:00', '2026-04-21 12:00:00'));
    }

    public function testEqualsComparesTimestamps(): void
    {
        $utc = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $ny = Dates::parse('2026-04-21 08:00:00', 'America/New_York');
        Assert::true(Dates::equals($utc, $ny));

        $different = Dates::parse('2026-04-21 13:00:00', 'UTC');
        Assert::false(Dates::equals($utc, $different));
    }

    public function testEqualsSameDateReturnsTrue(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::true(Dates::equals($date, $date));
    }

    public function testFluentChaining(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'UTC')
            ->addDays(10)
            ->startOfDay()
            ->get();

        Assert::same('2026-05-01 00:00:00', Dates::toDateTime($result));
    }

    public function testFormatAcceptsStringDate(): void
    {
        Assert::same('2026-04-21', Dates::format('2026-04-21 14:30:00', 'Y-m-d'));
    }

    public function testFormatAppliesPattern(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('21/04/2026', Dates::format($date, 'd/m/Y'));
        Assert::same('14:30', Dates::format($date, 'H:i'));
    }

    public function testFormatAutoDetectsDayJsFromBracketEscaping(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        Assert::same('Y is 2026', Dates::format($date, '[Y is] YYYY'));
    }

    public function testFormatAutoDetectsDayJsFromMultiCharTokens(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('2026-04-21', Dates::format($date, 'YYYY-MM-DD'));
        Assert::same('21/04/2026 14:30', Dates::format($date, 'DD/MM/YYYY HH:mm'));
        Assert::same('2:30 PM', Dates::format($date, 'h:mm A'));
    }

    public function testFormatAutoDetectsPhpNativeFromSingleChars(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('2026-04-21 14:30:00', Dates::format($date, 'Y-m-d H:i:s'));
        Assert::same('21/04/2026 14:30', Dates::format($date, 'd/m/Y H:i'));
        Assert::same('Tuesday, April 21, 2026', Dates::format($date, 'l, F j, Y'));
    }

    public function testFormatDayJs12HourFormatWithAmPm(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('2:30 PM', Dates::format($date, 'h:mm A'));
    }

    public function testFormatDayJsAcceptsStringDate(): void
    {
        Assert::same('2026-04-21', Dates::format('2026-04-21 14:30:00', 'YYYY-MM-DD'));
    }

    public function testFormatDayJsAmPmTokens(): void
    {
        $pm = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('PM', Dates::format($pm, 'A'));
        Assert::same('pm', Dates::format($pm, 'a'));

        $am = Dates::parse('2026-04-21 08:30:00', 'UTC');
        Assert::same('AM', Dates::format($am, 'A'));
        Assert::same('am', Dates::format($am, 'a'));
    }

    public function testFormatDayJsBracketEscaping(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('Today is Tuesday', Dates::format($date, '[Today is] dddd'));
        Assert::same('YYYY is the year 2026', Dates::format($date, '[YYYY is the year] YYYY'));
    }

    public function testFormatDayJsCompoundFormat(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('2026-04-21', Dates::format($date, 'YYYY-MM-DD'));
        Assert::same('21/04/2026 14:30', Dates::format($date, 'DD/MM/YYYY HH:mm'));
        Assert::same('Tuesday, April 21, 2026', Dates::format($date, 'dddd, MMMM D, YYYY'));
    }

    public function testFormatDayJsDayOfMonthTokens(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        Assert::same('21', Dates::format($date, 'DD'));
        Assert::same('2026-04-21', Dates::format($date, 'YYYY-MM-D'));

        $singleDigit = Dates::parse('2026-04-05', 'UTC');
        Assert::same('05', Dates::format($singleDigit, 'DD'));
        Assert::same('2026-04-5', Dates::format($singleDigit, 'YYYY-MM-D'));
    }

    public function testFormatDayJsDayOfWeekTokens(): void
    {
        $tuesday = Dates::parse('2026-04-21', 'UTC');
        Assert::same('Tuesday', Dates::format($tuesday, 'dddd'));
        Assert::same('Tue', Dates::format($tuesday, 'ddd'));
        Assert::same('Tu', Dates::format($tuesday, 'dd'));
        Assert::same('2026-2', Dates::format($tuesday, 'YYYY-d'));

        $sunday = Dates::parse('2026-04-19', 'UTC');
        Assert::same('Sunday', Dates::format($sunday, 'dddd'));
        Assert::same('2026-0', Dates::format($sunday, 'YYYY-d'));
    }

    public function testFormatDayJsEmptyFormatReturnsEmptyString(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        Assert::same('', Dates::format($date, ''));
    }

    public function testFormatDayJsFullOutput(): void
    {
        $date = Dates::parse('2026-04-21 14:30:45', 'UTC');
        $expected = '2026-04-21 14:30:45 +00:00';
        Assert::same($expected, Dates::format($date, 'YYYY-MM-DD HH:mm:ss Z'));
    }

    public function testFormatDayJsHourTokens12(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('02', Dates::format($date, 'hh'));
        Assert::same('2:30', Dates::format($date, 'h:mm'));

        $noon = Dates::parse('2026-04-21 12:00:00', 'UTC');
        Assert::same('12', Dates::format($noon, 'hh'));
    }

    public function testFormatDayJsHourTokens24(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('14', Dates::format($date, 'HH'));
        Assert::same('14:30', Dates::format($date, 'H:mm'));

        $midnight = Dates::parse('2026-04-21 00:30:00', 'UTC');
        Assert::same('00', Dates::format($midnight, 'HH'));
        Assert::same('0:30', Dates::format($midnight, 'H:mm'));
    }

    public function testFormatDayJsLiteralCharactersPassThrough(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        Assert::same('2026/04/21', Dates::format($date, 'YYYY/MM/DD'));
        Assert::same('2026.04.21', Dates::format($date, 'YYYY.MM.DD'));
    }

    public function testFormatDayJsMilliseconds(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('000', Dates::format($date, 'SSS'));
    }

    public function testFormatDayJsMinuteTokens(): void
    {
        $date = Dates::parse('2026-04-21 14:05:00', 'UTC');
        Assert::same('05', Dates::format($date, 'mm'));
        Assert::same('14:5', Dates::format($date, 'HH:m'));
    }

    public function testFormatDayJsMonthTokens(): void
    {
        $date = Dates::parse('2026-01-21', 'UTC');
        Assert::same('January', Dates::format($date, 'MMMM'));
        Assert::same('Jan', Dates::format($date, 'MMM'));
        Assert::same('01', Dates::format($date, 'MM'));
        Assert::same('2026-1-21', Dates::format($date, 'YYYY-M-DD'));
    }

    public function testFormatDayJsMultipleEscapedSections(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('At 14:30 on April 21', Dates::format($date, '[At] HH:mm [on] MMMM DD'));
    }

    public function testFormatDayJsSecondTokens(): void
    {
        $date = Dates::parse('2026-04-21 14:30:07', 'UTC');
        Assert::same('07', Dates::format($date, 'ss'));
        Assert::same('30:7', Dates::format($date, 'mm:s'));
    }

    public function testFormatDayJsTimezoneOffsetTokens(): void
    {
        $utc = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('2026-04-21 +00:00', Dates::format($utc, 'YYYY-MM-DD Z'));
        Assert::same('2026-04-21 +0000', Dates::format($utc, 'YYYY-MM-DD ZZ'));
    }

    public function testFormatDayJsYearTokens(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        Assert::same('2026', Dates::format($date, 'YYYY'));
        Assert::same('26', Dates::format($date, 'YY'));
    }

    public function testFromFormatDayJsTokensParsesCorrectly(): void
    {
        $date = Dates::fromFormat('DD/MM/YYYY', '21/04/2026', 'UTC');
        Assert::same('2026-04-21', $date->format('Y-m-d'));
    }

    public function testFromFormatDayJsTokensSingleDigitDay(): void
    {
        $date = Dates::fromFormat('YYYY-MM-D', '2026-04-5', 'UTC');
        Assert::same('2026-04-05', $date->format('Y-m-d'));
    }

    public function testFromFormatDayJsTokensSingleDigitMonth(): void
    {
        $date = Dates::fromFormat('YYYY-M-DD', '2026-4-21', 'UTC');
        Assert::same('2026-04-21', $date->format('Y-m-d'));
    }

    public function testFromFormatDayJsTokensThrowsOnMismatch(): void
    {
        Assert::exception(
            fn () => Dates::fromFormat('YYYY-MM-DD', 'not-a-date', 'UTC'),
            InvalidArgumentException::class
        );
    }

    public function testFromFormatDayJsTokensWith12HourClock(): void
    {
        $date = Dates::fromFormat('YYYY-MM-DD hh:mm:ss A', '2026-04-21 02:30:00 PM', 'UTC');
        Assert::same('2026-04-21 14:30:00', $date->format('Y-m-d H:i:s'));
    }

    public function testFromFormatDayJsTokensWithFullMonthName(): void
    {
        $date = Dates::fromFormat('DD MMMM YYYY', '21 April 2026', 'UTC');
        Assert::same('2026-04-21', $date->format('Y-m-d'));
    }

    public function testFromFormatDayJsTokensWithLowercaseAmPm(): void
    {
        $date = Dates::fromFormat('YYYY-MM-DD hh:mm:ss a', '2026-04-21 02:30:00 am', 'UTC');
        Assert::same('2026-04-21 02:30:00', $date->format('Y-m-d H:i:s'));
    }

    public function testFromFormatDayJsTokensWithMonthName(): void
    {
        $date = Dates::fromFormat('DD MMM YYYY', '21 Apr 2026', 'UTC');
        Assert::same('2026-04-21', $date->format('Y-m-d'));
    }

    public function testFromFormatDayJsTokensWithTime(): void
    {
        $date = Dates::fromFormat('YYYY-MM-DD HH:mm:ss', '2026-04-21 14:30:00', 'UTC');
        Assert::same('2026-04-21 14:30:00', $date->format('Y-m-d H:i:s'));
    }

    public function testFromFormatParsesCorrectly(): void
    {
        $date = Dates::fromFormat('d/m/Y', '21/04/2026', 'UTC');
        Assert::same('2026-04-21', $date->format('Y-m-d'));
    }

    public function testFromFormatThrowsOnMismatch(): void
    {
        Assert::exception(
            fn () => Dates::fromFormat('Y-m-d', 'not-a-date', 'UTC'),
            InvalidArgumentException::class
        );
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

    public function testFromTimestampZeroIsEpoch(): void
    {
        $date = Dates::fromTimestamp(0, 'UTC');
        Assert::same(0, $date->getTimestamp());
    }

    public function testGetDayAcceptsStringDate(): void
    {
        Assert::same(21, Dates::getDay('2026-04-21'));
    }

    public function testGetDayOfWeekAcceptsStringDate(): void
    {
        Assert::same(2, Dates::getDayOfWeek('2026-04-21'));
    }

    public function testGetDayOfWeekReturnsMondayAsOne(): void
    {
        Assert::same(1, Dates::getDayOfWeek(Dates::parse('2026-04-20', 'UTC')));
    }

    public function testGetDayOfWeekReturnsSundayAsSeven(): void
    {
        Assert::same(7, Dates::getDayOfWeek(Dates::parse('2026-04-19', 'UTC')));
    }

    public function testGetDayOfWeekReturnsTuesdayAsTwo(): void
    {
        Assert::same(2, Dates::getDayOfWeek(Dates::parse('2026-04-21', 'UTC')));
    }

    public function testGetDayOfYearAcceptsStringDate(): void
    {
        Assert::same(111, Dates::getDayOfYear('2026-04-21'));
    }

    public function testGetDayOfYearCountsFromOne(): void
    {
        Assert::same(1, Dates::getDayOfYear(Dates::parse('2026-01-01', 'UTC')));
        Assert::same(31, Dates::getDayOfYear(Dates::parse('2026-01-31', 'UTC')));
        Assert::same(365, Dates::getDayOfYear(Dates::parse('2026-12-31', 'UTC')));
    }

    public function testGetDayOfYearLeapYear(): void
    {
        Assert::same(366, Dates::getDayOfYear(Dates::parse('2024-12-31', 'UTC')));
    }

    public function testGetDayReturnsDay(): void
    {
        Assert::same(21, Dates::getDay(Dates::parse('2026-04-21', 'UTC')));
    }

    public function testGetDaysInMonthAcceptsStringDate(): void
    {
        Assert::same(30, Dates::getDaysInMonth('2026-04-01'));
    }

    public function testGetDaysInMonthCountsCorrectly(): void
    {
        Assert::same(30, Dates::getDaysInMonth(Dates::parse('2026-04-01', 'UTC')));
        Assert::same(31, Dates::getDaysInMonth(Dates::parse('2026-01-01', 'UTC')));
        Assert::same(28, Dates::getDaysInMonth(Dates::parse('2026-02-01', 'UTC')));
        Assert::same(29, Dates::getDaysInMonth(Dates::parse('2024-02-01', 'UTC')));
    }

    public function testGetHourAcceptsStringDate(): void
    {
        Assert::same(14, Dates::getHour('2026-04-21 14:30:00'));
    }

    public function testGetHourReturnsHour(): void
    {
        Assert::same(14, Dates::getHour(Dates::parse('2026-04-21 14:30:00', 'UTC')));
    }

    public function testGetMinuteAcceptsStringDate(): void
    {
        Assert::same(30, Dates::getMinute('2026-04-21 14:30:00'));
    }

    public function testGetMinuteReturnsMinute(): void
    {
        Assert::same(30, Dates::getMinute(Dates::parse('2026-04-21 14:30:00', 'UTC')));
    }

    public function testGetMonthAcceptsStringDate(): void
    {
        Assert::same(4, Dates::getMonth('2026-04-21'));
    }

    public function testGetMonthReturnsMonth(): void
    {
        Assert::same(4, Dates::getMonth(Dates::parse('2026-04-21', 'UTC')));
    }

    public function testGetSecondAcceptsStringDate(): void
    {
        Assert::same(45, Dates::getSecond('2026-04-21 14:30:45'));
    }

    public function testGetSecondReturnsSecond(): void
    {
        Assert::same(45, Dates::getSecond(Dates::parse('2026-04-21 14:30:45', 'UTC')));
    }

    public function testGetTimezoneAcceptsStringDate(): void
    {
        Assert::type('string', Dates::getTimezone('2026-04-21'));
    }

    public function testGetTimezoneReturnsName(): void
    {
        $date = Dates::parse('2026-04-21', 'Asia/Tokyo');
        Assert::same('Asia/Tokyo', Dates::getTimezone($date));
    }

    public function testGetWeekOfYearAcceptsStringDate(): void
    {
        Assert::same(17, Dates::getWeekOfYear('2026-04-21'));
    }

    public function testGetWeekOfYearReturnsCorrectWeek(): void
    {
        Assert::same(2, Dates::getWeekOfYear(Dates::parse('2026-01-05', 'UTC')));
    }

    public function testGetYearAcceptsStringDate(): void
    {
        Assert::same(2026, Dates::getYear('2026-04-21'));
    }

    public function testGetYearReturnsYear(): void
    {
        Assert::same(2026, Dates::getYear(Dates::parse('2026-04-21', 'UTC')));
    }

    public function testIsAfterReturnsFalseForSameDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        Assert::false(Dates::isAfter($date, $date));
    }

    public function testIsAfterReturnsTrueForLaterDate(): void
    {
        $later = Dates::parse('2026-12-31', 'UTC');
        $earlier = Dates::parse('2026-01-01', 'UTC');
        Assert::true(Dates::isAfter($later, $earlier));
        Assert::false(Dates::isAfter($earlier, $later));
    }

    public function testIsBeforeAcceptsStringDates(): void
    {
        Assert::true(Dates::isBefore('2026-01-01', '2026-12-31'));
    }

    public function testIsBeforeReturnsFalseForSameDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        Assert::false(Dates::isBefore($date, $date));
    }

    public function testIsBeforeReturnsTrueForEarlierDate(): void
    {
        $earlier = Dates::parse('2026-01-01', 'UTC');
        $later = Dates::parse('2026-12-31', 'UTC');
        Assert::true(Dates::isBefore($earlier, $later));
        Assert::false(Dates::isBefore($later, $earlier));
    }

    public function testIsBusinessDayAcceptsStringDate(): void
    {
        // Tuesday Apr 21 — weekday, not a default holiday
        Assert::true(Dates::isBusinessDay('2026-04-21'));
        // Saturday Apr 18 — weekend
        Assert::false(Dates::isBusinessDay('2026-04-18'));
    }

    public function testIsBusinessDayMatchesIsWeekday(): void
    {
        $tuesday = Dates::parse('2026-04-21', 'UTC');
        $saturday = Dates::parse('2026-04-18', 'UTC');
        Assert::true(Dates::isBusinessDay($tuesday));
        Assert::false(Dates::isBusinessDay($saturday));
    }

    public function testIsBusinessDayReturnsFalseForChristmas(): void
    {
        // Fri Dec 25 2026 — weekday but a default holiday
        Assert::false(Dates::isBusinessDay('2026-12-25'));
    }

    public function testIsBusinessDayReturnsFalseForDefaultHoliday(): void
    {
        // Thu Jan 1 2026 — weekday but a default holiday
        Assert::false(Dates::isBusinessDay('2026-01-01'));
    }

    public function testIsBusinessDayReturnsFalseForNewYearsEve(): void
    {
        // Thu Dec 31 2026 — weekday but a default holiday
        Assert::false(Dates::isBusinessDay('2026-12-31'));
    }

    public function testIsBusinessDayReturnsFalseForWeekendWithHolidays(): void
    {
        // Sat Apr 18 — weekend regardless of holidays
        Assert::false(Dates::isBusinessDay('2026-04-18'));
    }

    public function testIsBusinessDayReturnsTrueForWeekdayNotHoliday(): void
    {
        // Fri Jan 2 2026 — weekday and not a default holiday
        Assert::true(Dates::isBusinessDay('2026-01-02'));
    }

    public function testIsBusinessDayWithCustomHolidays(): void
    {
        $custom = [['month' => 7, 'day' => 4]];

        // Sat Jul 4 — weekend regardless
        Assert::false(Dates::isBusinessDay('2026-07-04', $custom));
        // Mon Jul 6 — weekday, not in custom list
        Assert::true(Dates::isBusinessDay('2026-07-06', $custom));
    }

    public function testIsBusinessDayWithEmptyHolidays(): void
    {
        // Thu Jan 1 — weekday, no holidays => business day
        Assert::true(Dates::isBusinessDay('2026-01-01', []));
    }

    public function testIsFutureAcceptsStringDate(): void
    {
        Assert::true(Dates::isFuture('2099-01-01'));
        Assert::false(Dates::isFuture('2020-01-01'));
    }

    public function testIsFutureReturnsTrueForFutureDate(): void
    {
        Assert::true(Dates::isFuture(Dates::parse('2099-01-01', 'UTC')));
        Assert::false(Dates::isFuture(Dates::parse('2020-01-01', 'UTC')));
    }

    public function testIsHolidayAcceptsStringDate(): void
    {
        // Uses default holidays (Jan 1, Dec 25, Dec 31)
        Assert::true(Dates::isHoliday('2026-01-01'));
        Assert::true(Dates::isHoliday('2026-12-25'));
        Assert::true(Dates::isHoliday('2026-12-31'));
        Assert::false(Dates::isHoliday('2026-03-15'));
    }

    public function testIsHolidayMatchesAnyHolidayInList(): void
    {
        $custom = [
            ['month' => 1, 'day' => 1],
            ['month' => 7, 'day' => 4],
            ['month' => 12, 'day' => 25],
        ];

        $date = Dates::parse('2026-07-04', 'UTC');
        Assert::true(Dates::isHoliday($date, $custom));
    }

    public function testIsHolidayPropertyIsMutable(): void
    {
        $original = Dates::$holidays;

        Dates::$holidays[] = ['month' => 7, 'day' => 4];
        Assert::true(Dates::isHoliday('2026-07-04'));

        Dates::$holidays = $original;
        Assert::false(Dates::isHoliday('2026-07-04'));
    }

    public function testIsHolidayReturnsFalseForEmptyList(): void
    {
        $date = Dates::parse('2026-01-01', 'UTC');
        Assert::false(Dates::isHoliday($date, []));
    }

    public function testIsHolidayReturnsFalseForNonHolidayWithDefaults(): void
    {
        Assert::false(Dates::isHoliday('2026-06-15'));
    }

    public function testIsHolidayWithCustomListOverridesDefaults(): void
    {
        $custom = [['month' => 7, 'day' => 4]];

        // Jul 4 is in the custom list
        Assert::true(Dates::isHoliday('2026-07-04', $custom));
        // Jan 1 is not in the custom list (overridden)
        Assert::false(Dates::isHoliday('2026-01-01', $custom));
    }

    public function testIsLeapYearAcceptsStringDate(): void
    {
        Assert::true(Dates::isLeapYear('2024-06-15'));
        Assert::false(Dates::isLeapYear('2026-06-15'));
    }

    public function testIsLeapYearDetectsLeapYears(): void
    {
        Assert::true(Dates::isLeapYear(Dates::parse('2024-01-01', 'UTC')));
        Assert::false(Dates::isLeapYear(Dates::parse('2026-01-01', 'UTC')));
        Assert::false(Dates::isLeapYear(Dates::parse('1900-01-01', 'UTC')));
        Assert::true(Dates::isLeapYear(Dates::parse('2000-01-01', 'UTC')));
    }

    public function testIsPastAcceptsStringDate(): void
    {
        Assert::true(Dates::isPast('2020-01-01'));
        Assert::false(Dates::isPast('2099-01-01'));
    }

    public function testIsPastReturnsTrueForOldDate(): void
    {
        Assert::true(Dates::isPast(Dates::parse('2020-01-01', 'UTC')));
        Assert::false(Dates::isPast(Dates::parse('2099-01-01', 'UTC')));
    }

    public function testIsSameDayAcceptsStringDates(): void
    {
        Assert::true(Dates::isSameDay('2026-04-21 08:00:00', '2026-04-21 22:00:00'));
        Assert::false(Dates::isSameDay('2026-04-21', '2026-04-22'));
    }

    public function testIsSameDayMatchesCalendarDay(): void
    {
        $morning = Dates::parse('2026-04-21 08:00:00', 'UTC');
        $evening = Dates::parse('2026-04-21 22:00:00', 'UTC');
        $tomorrow = Dates::parse('2026-04-22 00:00:00', 'UTC');

        Assert::true(Dates::isSameDay($morning, $evening));
        Assert::false(Dates::isSameDay($morning, $tomorrow));
    }

    public function testIsSameMonthAcceptsStringDates(): void
    {
        Assert::true(Dates::isSameMonth('2026-04-01', '2026-04-30'));
        Assert::false(Dates::isSameMonth('2026-04-01', '2026-05-01'));
    }

    public function testIsSameMonthMatchesYearAndMonth(): void
    {
        $first = Dates::parse('2026-04-01', 'UTC');
        $last = Dates::parse('2026-04-30', 'UTC');
        $next = Dates::parse('2026-05-01', 'UTC');

        Assert::true(Dates::isSameMonth($first, $last));
        Assert::false(Dates::isSameMonth($first, $next));
    }

    public function testIsSameYearAcceptsStringDates(): void
    {
        Assert::true(Dates::isSameYear('2026-01-01', '2026-12-31'));
        Assert::false(Dates::isSameYear('2026-01-01', '2027-01-01'));
    }

    public function testIsSameYearMatchesYear(): void
    {
        $jan = Dates::parse('2026-01-01', 'UTC');
        $dec = Dates::parse('2026-12-31', 'UTC');
        $nextYear = Dates::parse('2027-01-01', 'UTC');

        Assert::true(Dates::isSameYear($jan, $dec));
        Assert::false(Dates::isSameYear($jan, $nextYear));
    }

    public function testIsTodayAcceptsStringDate(): void
    {
        $today = (new DateTimeImmutable('today', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        Assert::true(Dates::isToday($today));
    }

    public function testIsTodayMatchesTodayOnly(): void
    {
        Assert::true(Dates::isToday(new DateTimeImmutable('today', new DateTimeZone('UTC'))));
        Assert::false(Dates::isToday(Dates::parse('2020-01-01', 'UTC')));
    }

    public function testIsTomorrowAcceptsStringDate(): void
    {
        $tomorrow = (new DateTimeImmutable('tomorrow', new DateTimeZone('UTC')))->format('Y-m-d');
        Assert::true(Dates::isTomorrow($tomorrow));
    }

    public function testIsTomorrowMatchesTomorrowOnly(): void
    {
        Assert::true(Dates::isTomorrow(new DateTimeImmutable('tomorrow', new DateTimeZone('UTC'))));
        Assert::false(Dates::isTomorrow(new DateTimeImmutable('today', new DateTimeZone('UTC'))));
    }

    public function testIsWeekdayAcceptsStringDate(): void
    {
        Assert::true(Dates::isWeekday('2026-04-21'));
        Assert::false(Dates::isWeekday('2026-04-18'));
    }

    public function testIsWeekdayIsOppositeOfIsWeekend(): void
    {
        Assert::false(Dates::isWeekday(Dates::parse('2026-04-18', 'UTC')));
        Assert::true(Dates::isWeekday(Dates::parse('2026-04-21', 'UTC')));
    }

    public function testIsWeekendAcceptsStringDate(): void
    {
        Assert::true(Dates::isWeekend('2026-04-18'));
        Assert::false(Dates::isWeekend('2026-04-21'));
    }

    public function testIsWeekendIdentifiesSaturdayAndSunday(): void
    {
        Assert::true(Dates::isWeekend(Dates::parse('2026-04-18', 'UTC')));
        Assert::true(Dates::isWeekend(Dates::parse('2026-04-19', 'UTC')));
        Assert::false(Dates::isWeekend(Dates::parse('2026-04-21', 'UTC')));
    }

    public function testIsYesterdayAcceptsStringDate(): void
    {
        $yesterday = (new DateTimeImmutable('yesterday', new DateTimeZone('UTC')))->format('Y-m-d');
        Assert::true(Dates::isYesterday($yesterday));
    }

    public function testIsYesterdayMatchesYesterdayOnly(): void
    {
        Assert::true(Dates::isYesterday(new DateTimeImmutable('yesterday', new DateTimeZone('UTC'))));
        Assert::false(Dates::isYesterday(new DateTimeImmutable('today', new DateTimeZone('UTC'))));
    }

    public function testNowRespectsTimezone(): void
    {
        $now = Dates::now('Asia/Tokyo');
        Assert::same('Asia/Tokyo', $now->getTimezone()->getName());
    }
    public function testNowReturnsCurrentDateTime(): void
    {
        $before = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $now = Dates::now('UTC');
        $after = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        Assert::true($now->getTimestamp() >= $before->getTimestamp());
        Assert::true($now->getTimestamp() <= $after->getTimestamp());
    }

    public function testNowWithInvalidTimezoneThrows(): void
    {
        Assert::exception(
            fn () => Dates::now('Not/ATimezone'),
            InvalidArgumentException::class
        );
    }

    public function testOfAcceptsDateTimeImmutable(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $fluent = Dates::of($date);
        Assert::type(DateTimeImmutable::class, $fluent->get());
    }

    public function testOfChaining(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'UTC')
            ->addDays(10)
            ->startOfDay()
            ->get();

        Assert::same('2026-05-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testOfReturnsFluentDates(): void
    {
        $result = Dates::of('2026-04-21 14:30:00', 'UTC');

        Assert::type(\Phuture\Coherence\Type\Dates::class, $result);
    }

    public function testOfReturnsFluentWrapper(): void
    {
        $fluent = Dates::of('2026-04-21 14:30:00', 'UTC');
        Assert::type(\Phuture\Coherence\Type\Dates::class, $fluent);
    }

    public function testOfWithDateTimeImmutable(): void
    {
        $dt = new DateTimeImmutable('2026-04-21 14:30:00', new DateTimeZone('UTC'));
        $result = Dates::of($dt);

        Assert::type(\Phuture\Coherence\Type\Dates::class, $result);
    }

    public function testOfWithInvalidStringThrows(): void
    {
        Assert::exception(
            fn () => Dates::of('not-a-date'),
            InvalidArgumentException::class
        );
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

    public function testParseHandlesPlusModifier(): void
    {
        $date = Dates::parse('+1 day', 'UTC');
        Assert::type(DateTimeImmutable::class, $date);
    }

    public function testParseHandlesRelativeDateString(): void
    {
        $date = Dates::parse('yesterday', 'UTC');
        Assert::type(DateTimeImmutable::class, $date);
    }

    public function testParseRespectsTimezone(): void
    {
        $date = Dates::parse('2026-04-21 12:00:00', 'Europe/Berlin');
        Assert::same('Europe/Berlin', $date->getTimezone()->getName());
    }

    public function testParseThrowsOnEmptyString(): void
    {
        Assert::exception(
            fn () => Dates::parse(''),
            InvalidArgumentException::class
        );
    }

    public function testParseThrowsOnGarbageString(): void
    {
        Assert::exception(
            fn () => Dates::parse('zzzzzz'),
            InvalidArgumentException::class
        );
    }

    public function testParseThrowsOnInvalidString(): void
    {
        Assert::exception(
            fn () => Dates::parse('not-a-date'),
            InvalidArgumentException::class
        );
    }

    public function testParseRelativeAddsDaysFromReference(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $result = Dates::parseRelative('+3 days', $ref);
        Assert::same('2026-04-24 12:00:00', Dates::toDateTime($result));
    }

    public function testParseRelativeSubtractsWeeksFromReference(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $result = Dates::parseRelative('-1 week', $ref);
        Assert::same('2026-04-14 12:00:00', Dates::toDateTime($result));
    }

    public function testParseRelativeHandlesDaysAgo(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $result = Dates::parseRelative('2 days ago', $ref);
        Assert::same('2026-04-19 12:00:00', Dates::toDateTime($result));
    }

    public function testParseRelativeHandlesInDays(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $result = Dates::parseRelative('in 5 days', $ref);
        Assert::same('2026-04-26 12:00:00', Dates::toDateTime($result));
    }

    public function testParseRelativeHandlesInHours(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $result = Dates::parseRelative('in 3 hours', $ref);
        Assert::same('2026-04-21 15:00:00', Dates::toDateTime($result));
    }

    public function testParseRelativeHandlesInMinutes(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $result = Dates::parseRelative('in 45 minutes', $ref);
        Assert::same('2026-04-21 12:45:00', Dates::toDateTime($result));
    }

    public function testParseRelativeHandlesInWeeks(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $result = Dates::parseRelative('in 2 weeks', $ref);
        Assert::same('2026-05-05 12:00:00', Dates::toDateTime($result));
    }

    public function testParseRelativeHandlesInMonths(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $result = Dates::parseRelative('in 6 months', $ref);
        Assert::same('2026-10-21 12:00:00', Dates::toDateTime($result));
    }

    public function testParseRelativeHandlesInYears(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $result = Dates::parseRelative('in 1 year', $ref);
        Assert::same('2027-04-21 12:00:00', Dates::toDateTime($result));
    }

    public function testParseRelativeHandlesInSingleSecond(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $result = Dates::parseRelative('in 1 second', $ref);
        Assert::same('2026-04-21 12:00:01', Dates::toDateTime($result));
    }

    public function testParseRelativeDefaultsToNowWithoutReference(): void
    {
        $result = Dates::parseRelative('tomorrow');
        Assert::type(DateTimeImmutable::class, $result);
        Assert::true(Dates::isTomorrow($result));
    }

    public function testParseRelativeAcceptsStringReference(): void
    {
        $result = Dates::parseRelative('+1 day', '2026-04-21');
        Assert::same('2026-04-22', Dates::toDate($result));
    }

    public function testParseRelativePreservesReferenceTimezone(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'Europe/Paris');
        $result = Dates::parseRelative('+1 day', $ref);
        Assert::same('Europe/Paris', Dates::getTimezone($result));
    }

    public function testParseRelativeThrowsOnEmptyString(): void
    {
        Assert::exception(
            fn () => Dates::parseRelative(''),
            InvalidArgumentException::class
        );
    }

    public function testParseRelativeThrowsOnInvalidExpression(): void
    {
        Assert::exception(
            fn () => Dates::parseRelative('not valid relative'),
            InvalidArgumentException::class
        );
    }

    public function testRemoveDaysAcceptsStringDate(): void
    {
        $result = Dates::removeDays('2026-04-21', 5);
        Assert::same('2026-04-16', Dates::toDate($result));
    }

    public function testRemoveDaysDecreasesDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $result = Dates::removeDays($date, 5);
        Assert::same('2026-04-16', Dates::toDate($result));
    }

    public function testRemoveDaysThrowsOnNegativeValue(): void
    {
        Assert::exception(
            fn () => Dates::removeDays(Dates::now(), -1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveDoesNotMutateOriginal(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        Dates::removeDays($date, 5);
        Assert::same('2026-04-21', Dates::toDate($date));
    }

    public function testRemoveHoursAcceptsStringDate(): void
    {
        $result = Dates::removeHours('2026-04-21 14:30:00', 2);
        Assert::same('2026-04-21 12:30:00', Dates::toDateTime($result));
    }

    public function testRemoveHoursDecreasesTime(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::removeHours($date, 2);
        Assert::same('2026-04-21 12:30:00', Dates::toDateTime($result));
    }

    public function testRemoveHoursThrowsOnNegativeValue(): void
    {
        Assert::exception(
            fn () => Dates::removeHours(Dates::now(), -1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveMinutesAcceptsStringDate(): void
    {
        $result = Dates::removeMinutes('2026-04-21 14:30:00', 15);
        Assert::same('2026-04-21 14:15:00', Dates::toDateTime($result));
    }

    public function testRemoveMinutesDecreasesTime(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::removeMinutes($date, 15);
        Assert::same('2026-04-21 14:15:00', Dates::toDateTime($result));
    }

    public function testRemoveMinutesThrowsOnNegativeValue(): void
    {
        Assert::exception(
            fn () => Dates::removeMinutes(Dates::now(), -1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveMonthsAcceptsStringDate(): void
    {
        $result = Dates::removeMonths('2026-06-15', 2);
        Assert::same('2026-04-15', Dates::toDate($result));
    }

    public function testRemoveMonthsDecreasesDate(): void
    {
        $date = Dates::parse('2026-06-15', 'UTC');
        $result = Dates::removeMonths($date, 2);
        Assert::same('2026-04-15', Dates::toDate($result));
    }

    public function testRemoveMonthsThrowsOnNegativeValue(): void
    {
        Assert::exception(
            fn () => Dates::removeMonths(Dates::now(), -1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveSecondsAcceptsStringDate(): void
    {
        $result = Dates::removeSeconds('2026-04-21 14:30:00', 30);
        Assert::same('2026-04-21 14:29:30', Dates::toDateTime($result));
    }

    public function testRemoveSecondsDecreasesTime(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::removeSeconds($date, 30);
        Assert::same('2026-04-21 14:29:30', Dates::toDateTime($result));
    }

    public function testRemoveSecondsThrowsOnNegativeValue(): void
    {
        Assert::exception(
            fn () => Dates::removeSeconds(Dates::now(), -1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveWeeksAcceptsStringDate(): void
    {
        $result = Dates::removeWeeks('2026-04-21', 1);
        Assert::same('2026-04-14', Dates::toDate($result));
    }

    public function testRemoveWeeksDecreasesDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $result = Dates::removeWeeks($date, 1);
        Assert::same('2026-04-14', Dates::toDate($result));
    }

    public function testRemoveWeeksThrowsOnNegativeValue(): void
    {
        Assert::exception(
            fn () => Dates::removeWeeks(Dates::now(), -1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveYearsAcceptsStringDate(): void
    {
        $result = Dates::removeYears('2026-04-21', 10);
        Assert::same('2016-04-21', Dates::toDate($result));
    }

    public function testRemoveYearsDecreasesDate(): void
    {
        $date = Dates::parse('2026-04-21', 'UTC');
        $result = Dates::removeYears($date, 10);
        Assert::same('2016-04-21', Dates::toDate($result));
    }

    public function testRemoveYearsThrowsOnNegativeValue(): void
    {
        Assert::exception(
            fn () => Dates::removeYears(Dates::now(), -1),
            InvalidArgumentException::class
        );
    }

    public function testRemoveZeroIsAllowed(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('2026-04-21 14:30:00', Dates::toDateTime(Dates::removeSeconds($date, 0)));
        Assert::same('2026-04-21', Dates::toDate(Dates::removeDays($date, 0)));
    }

    public function testStartOfDayAcceptsStringDate(): void
    {
        $result = Dates::startOfDay('2026-04-21 14:30:45');
        Assert::same('00:00:00', Dates::toTime($result));
    }

    public function testStartOfDaySetsToMidnight(): void
    {
        $date = Dates::parse('2026-04-21 14:30:45', 'UTC');
        $result = Dates::startOfDay($date);
        Assert::same('2026-04-21 00:00:00', Dates::toDateTime($result));
    }

    public function testStartOfMonthAcceptsStringDate(): void
    {
        $result = Dates::startOfMonth('2026-04-21 14:30:00');
        Assert::same('2026-04-01 00:00:00', Dates::toDateTime($result));
    }

    public function testStartOfMonthAlreadyFirstDay(): void
    {
        $date = Dates::parse('2026-04-01 00:00:00', 'UTC');
        $result = Dates::startOfMonth($date);
        Assert::same('2026-04-01 00:00:00', Dates::toDateTime($result));
    }

    public function testStartOfMonthSetsToFirstDay(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        $result = Dates::startOfMonth($date);
        Assert::same('2026-04-01 00:00:00', Dates::toDateTime($result));
    }

    public function testStartOfWeekAcceptsStringDate(): void
    {
        $result = Dates::startOfWeek('2026-04-21 14:00:00');
        Assert::same('2026-04-20 00:00:00', Dates::toDateTime($result));
    }

    public function testStartOfWeekAlreadyMonday(): void
    {
        $date = Dates::parse('2026-04-20 09:00:00', 'UTC');
        $result = Dates::startOfWeek($date);
        Assert::same('2026-04-20 00:00:00', Dates::toDateTime($result));
    }

    public function testStartOfWeekSetsToMonday(): void
    {
        $date = Dates::parse('2026-04-21 14:00:00', 'UTC');
        $result = Dates::startOfWeek($date);
        Assert::same('2026-04-20 00:00:00', Dates::toDateTime($result));
    }

    public function testStartOfYearAcceptsStringDate(): void
    {
        $result = Dates::startOfYear('2026-09-15 10:00:00');
        Assert::same('2026-01-01 00:00:00', Dates::toDateTime($result));
    }

    public function testStartOfYearAlreadyJanuaryFirst(): void
    {
        $date = Dates::parse('2026-01-01 00:00:00', 'UTC');
        $result = Dates::startOfYear($date);
        Assert::same('2026-01-01 00:00:00', Dates::toDateTime($result));
    }

    public function testStartOfYearSetsToJanuaryFirst(): void
    {
        $date = Dates::parse('2026-09-15 10:00:00', 'UTC');
        $result = Dates::startOfYear($date);
        Assert::same('2026-01-01 00:00:00', Dates::toDateTime($result));
    }

    public function testToDateStringAcceptsStringDate(): void
    {
        Assert::same('2026-04-21', Dates::toDate('2026-04-21 14:30:00'));
    }

    public function testToDateStringReturnsYmd(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('2026-04-21', Dates::toDate($date));
    }

    public function testToDateTimeStringAcceptsStringDate(): void
    {
        Assert::same('2026-04-21 14:30:45', Dates::toDateTime('2026-04-21 14:30:45'));
    }

    public function testToDateTimeStringReturnsFull(): void
    {
        $date = Dates::parse('2026-04-21 14:30:45', 'UTC');
        Assert::same('2026-04-21 14:30:45', Dates::toDateTime($date));
    }

    public function testToIso8601AcceptsStringDate(): void
    {
        $result = Dates::toIso8601('2026-04-21 12:00:00');
        Assert::contains('2026-04-21T12:00:00', $result);
    }

    public function testToIso8601ContainsOffset(): void
    {
        $date = Dates::parse('2026-04-21 12:00:00', 'UTC');
        Assert::same('2026-04-21T12:00:00+00:00', Dates::toIso8601($date));
    }

    public function testToRfc1036AcceptsStringDate(): void
    {
        $result = Dates::toRfc1036('2026-04-21 14:30:00');
        Assert::contains('Apr 26', $result);
    }

    public function testToRfc1036ContainsTwoDigitYear(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('Tue, 21 Apr 26 14:30:00 +0000', Dates::toRfc1036($date));
    }

    public function testToRfc1123AcceptsStringDate(): void
    {
        $result = Dates::toRfc1123('2026-04-21 14:30:00');
        Assert::contains('Apr 2026', $result);
    }

    public function testToRfc1123ContainsFourDigitYear(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('Tue, 21 Apr 2026 14:30:00 +0000', Dates::toRfc1123($date));
    }

    public function testToRfc2822AcceptsStringDate(): void
    {
        $result = Dates::toRfc2822('2026-04-21 12:00:00');
        Assert::contains('Apr 2026', $result);
    }

    public function testToRfc2822ContainsDayName(): void
    {
        $date = Dates::parse('2026-04-21 00:00:00', 'UTC');
        Assert::contains('Tue, 21 Apr 2026', Dates::toRfc2822($date));
    }

    public function testToRfc7231AcceptsStringDate(): void
    {
        $result = Dates::toRfc7231('2026-04-21 14:30:00');
        Assert::contains('GMT', $result);
    }

    public function testToRfc7231UsesGmtTimezone(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'America/New_York');
        Assert::same('Tue, 21 Apr 2026 18:30:00 GMT', Dates::toRfc7231($date));
    }

    public function testToRfc7231UtcDateStaysUnchanged(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('Tue, 21 Apr 2026 14:30:00 GMT', Dates::toRfc7231($date));
    }

    public function testToRfc822AcceptsStringDate(): void
    {
        $result = Dates::toRfc822('2026-04-21 14:30:00');
        Assert::contains('Apr 26', $result);
    }

    public function testToRfc822ContainsTwoDigitYear(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('Tue, 21 Apr 26 14:30:00 +0000', Dates::toRfc822($date));
    }

    public function testToRfc850AcceptsStringDate(): void
    {
        $result = Dates::toRfc850('2026-04-21 14:30:00');
        Assert::contains('Apr-26', $result);
    }

    public function testToRfc850ContainsFullDayName(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('Tuesday, 21-Apr-26 14:30:00 UTC', Dates::toRfc850($date));
    }

    public function testToTimeStringAcceptsStringDate(): void
    {
        Assert::same('14:30:45', Dates::toTime('2026-04-21 14:30:45'));
    }

    public function testToTimeStringReturnsHis(): void
    {
        $date = Dates::parse('2026-04-21 14:30:45', 'UTC');
        Assert::same('14:30:45', Dates::toTime($date));
    }

    public function testToTimezoneAcceptsStringDate(): void
    {
        $converted = Dates::toTimezone('2026-04-21 12:00:00', 'America/New_York');
        Assert::same('2026-04-21 08:00:00', $converted->format('Y-m-d H:i:s'));
    }

    public function testToTimezonePreservesMoment(): void
    {
        $utc = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $converted = Dates::toTimezone($utc, 'America/New_York');

        Assert::same($utc->getTimestamp(), $converted->getTimestamp());
        Assert::same('America/New_York', $converted->getTimezone()->getName());
        Assert::same('2026-04-21 08:00:00', $converted->format('Y-m-d H:i:s'));
    }

    public function testToUnixTimestampAcceptsStringDate(): void
    {
        Assert::same(0, Dates::toTimestamp('1970-01-01 00:00:00'));
    }

    public function testToUnixTimestampReturnsZeroForEpoch(): void
    {
        $date = Dates::fromTimestamp(0, 'UTC');
        Assert::same(0, Dates::toTimestamp($date));
    }

    public function testToW3cAcceptsStringDate(): void
    {
        $result = Dates::toW3c('2026-04-21 12:00:00');
        Assert::contains('2026-04-21T12:00:00', $result);
    }

    public function testToW3cContainsOffset(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same('2026-04-21T14:30:00+00:00', Dates::toW3c($date));
    }

    public function testToW3cMatchesIso8601Format(): void
    {
        $date = Dates::parse('2026-04-21 14:30:00', 'UTC');
        Assert::same(Dates::toIso8601($date), Dates::toW3c($date));
    }

    public function testToRelativeReturnsJustNowForRecentDate(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-04-21 12:00:30', 'UTC');
        Assert::same('just now', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsJustNowForFutureUnderMinute(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-04-21 12:00:45', 'UTC');
        Assert::same('just now', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsMinutesAgo(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-04-21 11:55:00', 'UTC');
        Assert::same('5 minutes ago', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsSingleMinuteAgo(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-04-21 11:59:00', 'UTC');
        Assert::same('1 minute ago', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsInMinutes(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-04-21 12:45:00', 'UTC');
        Assert::same('in 45 minutes', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsInSingleMinute(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-04-21 12:01:00', 'UTC');
        Assert::same('in 1 minute', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsHoursAgo(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-04-21 08:00:00', 'UTC');
        Assert::same('4 hours ago', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsInHours(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-04-21 17:00:00', 'UTC');
        Assert::same('in 5 hours', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsDaysAgo(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-04-19 12:00:00', 'UTC');
        Assert::same('2 days ago', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsInDays(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-04-26 12:00:00', 'UTC');
        Assert::same('in 5 days', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsWeeksAgo(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-04-07 12:00:00', 'UTC');
        Assert::same('2 weeks ago', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsInWeeks(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-05-05 12:00:00', 'UTC');
        Assert::same('in 2 weeks', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsMonthsAgo(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-01-21 12:00:00', 'UTC');
        Assert::same('3 months ago', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsInMonths(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2026-10-21 12:00:00', 'UTC');
        Assert::same('in 6 months', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsYearsAgo(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2021-04-21 12:00:00', 'UTC');
        Assert::same('5 years ago', Dates::toRelative($date, $ref));
    }

    public function testToRelativeReturnsInYears(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        $date = Dates::parse('2031-04-21 12:00:00', 'UTC');
        Assert::same('in 5 years', Dates::toRelative($date, $ref));
    }

    public function testToRelativeAcceptsStringDate(): void
    {
        $ref = Dates::parse('2026-04-21 12:00:00', 'UTC');
        Assert::same('3 days ago', Dates::toRelative('2026-04-18 12:00:00', $ref));
    }

    public function testToRelativeAcceptsStringReference(): void
    {
        Assert::same('2 days ago', Dates::toRelative('2026-04-18 12:00:00', '2026-04-20 12:00:00'));
    }

    public function testToRelativeDefaultsToNowWithoutReference(): void
    {
        $past = Dates::addYears(Dates::now('UTC'), -1);
        $result = Dates::toRelative($past);
        Assert::contains('year', $result);
        Assert::contains('ago', $result);
    }
}

(new DatesTest())->run();
