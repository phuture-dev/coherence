<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Phuture\Coherence\Callables;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

error_reporting(E_ALL & ~E_DEPRECATED);

class CallablesTest extends TestCase
{
    public function testThrottleExecutesOnFirstCall(): void
    {
        $count = 0;
        $throttled = Callables::throttle(function () use (&$count) {
            $count++;

            return 'result';
        }, 1000);

        $result = $throttled();

        Assert::same('result', $result);
        Assert::same(1, $count);
    }

    public function testThrottleBlocksRapidRepeatCalls(): void
    {
        $count = 0;
        $throttled = Callables::throttle(function () use (&$count) {
            $count++;

            return 'result';
        }, 1000);

        $throttled();
        $result = $throttled();

        Assert::null($result);
        Assert::same(1, $count);
    }

    public function testThrottleAllowsAfterInterval(): void
    {
        $count = 0;
        $throttled = Callables::throttle(function () use (&$count) {
            $count++;

            return 'result';
        }, 50);

        $throttled();
        usleep(60000);
        $result = $throttled();

        Assert::same('result', $result);
        Assert::same(2, $count);
    }

    public function testThrottlePerArgumentIndependentTimers(): void
    {
        $count = 0;
        $throttled = Callables::throttle(function (string $key) use (&$count) {
            $count++;

            return $key;
        }, 1000);

        $resultA1 = $throttled('A');
        $resultB1 = $throttled('B');
        $resultA2 = $throttled('A');
        $resultB2 = $throttled('B');

        Assert::same('A', $resultA1);
        Assert::same('B', $resultB1);
        Assert::null($resultA2);
        Assert::null($resultB2);
        Assert::same(2, $count);
    }

    public function testThrottlePerArgumentResetsIndependently(): void
    {
        $calls = [];
        $throttled = Callables::throttle(function (string $key) use (&$calls) {
            $calls[] = $key;

            return $key;
        }, 50);

        $throttled('A');
        $throttled('B');

        usleep(60000);

        $resultA = $throttled('A');
        $resultB = $throttled('B');

        Assert::same('A', $resultA);
        Assert::same('B', $resultB);
        Assert::same(['A', 'B', 'A', 'B'], $calls);
    }

    public function testThrottleDifferentArgumentSetsDoNotInterfere(): void
    {
        $count = 0;
        $throttled = Callables::throttle(function (int $x, int $y) use (&$count) {
            $count++;

            return $x + $y;
        }, 1000);

        $result1 = $throttled(1, 2);
        $result2 = $throttled(3, 4);
        $result3 = $throttled(1, 2);
        $result4 = $throttled(3, 4);

        Assert::same(3, $result1);
        Assert::same(7, $result2);
        Assert::null($result3);
        Assert::null($result4);
        Assert::same(2, $count);
    }

    public function testThrottleNoArgumentsActsLikeGlobal(): void
    {
        $count = 0;
        $throttled = Callables::throttle(function () use (&$count) {
            $count++;

            return 'result';
        }, 1000);

        $throttled();
        $throttled();
        $throttled();

        Assert::same(1, $count);
    }

    public function testThrottleReturnsCallbackResult(): void
    {
        $throttled = Callables::throttle(fn ($x) => $x * 2, 1000);

        Assert::same(10, $throttled(5));
        Assert::null($throttled(5));
    }

    public function testThrottleArrayArgumentsTrackedSeparately(): void
    {
        $count = 0;
        $throttled = Callables::throttle(function (array $data) use (&$count) {
            $count++;

            return $data;
        }, 1000);

        $result1 = $throttled(['id' => 1]);
        $result2 = $throttled(['id' => 2]);
        $result3 = $throttled(['id' => 1]);

        Assert::same(['id' => 1], $result1);
        Assert::same(['id' => 2], $result2);
        Assert::null($result3);
        Assert::same(2, $count);
    }
}

(new CallablesTest())->run();
