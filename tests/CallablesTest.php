<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Closure;
use DateTime;
use stdClass;
use RuntimeException;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Callables;

require __DIR__ . '/bootstrap.php';

class CallablesTest extends TestCase
{
    public function testAfter(): void
    {
        $log = [];
        $main = fn ($x) => $x * 2;
        $after = function ($result, $x) use (&$log) {
            $log[] = "result=$result, x=$x";
        };

        $wrapped = Callables::after($main, $after);
        $result = $wrapped(5);

        Assert::same(10, $result);
        Assert::same(['result=10, x=5'], $log);
    }

    public function testAfterReturnsMainResult(): void
    {
        $after = fn ($result) => 'ignored';
        $wrapped = Callables::after(fn ($x) => $x + 1, $after);
        Assert::same(6, $wrapped(5));
    }

    public function testAfterWithMultipleArgs(): void
    {
        $captured = [];
        $after = function ($result, ...$args) use (&$captured) {
            $captured = $args;
        };

        $wrapped = Callables::after(fn ($a, $b, $c) => $a + $b + $c, $after);
        $wrapped(1, 2, 3);

        Assert::same([1, 2, 3], $captured);
    }

    public function testApply(): void
    {
        $fn = fn ($a, $b, $c) => $a + $b + $c;
        Assert::same(6, Callables::apply($fn, [1, 2, 3]));
    }

    public function testApplyWithEmptyArgs(): void
    {
        $fn = fn () => 42;
        Assert::same(42, Callables::apply($fn, []));
    }

    public function testApplyWithNamedFunction(): void
    {
        Assert::same(5, Callables::apply('strlen', ['hello']));
    }

    public function testBefore(): void
    {
        $log = [];
        $before = function (...$args) use (&$log) {
            $log[] = $args;
        };
        $main = fn ($x) => $x * 3;

        $wrapped = Callables::before($main, $before);
        $result = $wrapped(4);

        Assert::same(12, $result);
        Assert::same([[4]], $log);
    }

    public function testBeforeReturnIgnored(): void
    {
        $before = fn () => 'ignored';
        $main = fn ($x) => $x + 10;

        $wrapped = Callables::before($main, $before);
        Assert::same(15, $wrapped(5));
    }

    public function testBinary(): void
    {
        $fn = fn ($a, $b) => $a + $b;
        $binary = Callables::binary($fn);

        Assert::same(3, $binary(1, 2));
    }

    public function testBinaryIgnoresExtraArgs(): void
    {
        $fn = fn ($a, $b) => $a * $b;
        $binary = Callables::binary($fn);

        Assert::same(6, $binary(2, 3, 4, 5));
    }

    public function testBind(): void
    {
        $obj = new class () {
            public string $name = 'test';
        };

        $closure = fn () => $this->name;
        $bound = Callables::bind($closure, $obj);

        Assert::same('test', $bound());
    }

    public function testBindWithNull(): void
    {
        $closure = fn () => 'hello';
        $bound = Callables::bind($closure, null);

        Assert::same('hello', $bound());
    }

    public function testCacheTtlConstant(): void
    {
        Assert::same(60000, Callables::CACHE_TTL);
    }

    public function testCall(): void
    {
        $fn = fn ($a, $b) => $a + $b;
        Assert::same(8, Callables::call($fn, 5, 3));
    }

    public function testCallWithNamedFunction(): void
    {
        Assert::same(5, Callables::call('strlen', 'hello'));
    }

    public function testCallWithNoArgs(): void
    {
        Assert::same(42, Callables::call(fn () => 42));
    }

    public function testCatchHandlesException(): void
    {
        $fn = fn () => throw new RuntimeException('boom');
        $safe = Callables::catch($fn, fn ($e) => 'caught: ' . $e->getMessage());

        Assert::same('caught: boom', $safe());
    }

    public function testCatchNoException(): void
    {
        $fn = fn ($a, $b) => $a / $b;
        $safe = Callables::catch($fn, fn ($e) => 'error');

        Assert::same(5, $safe(10, 2));
    }

    public function testCatchPassesArgsToHandler(): void
    {
        $fn = fn () => throw new RuntimeException('fail');
        $captured = [];
        $safe = Callables::catch($fn, function ($e, ...$args) use (&$captured) {
            $captured = $args;

            return 'error';
        });

        $safe('a', 'b');
        Assert::same(['a', 'b'], $captured);
    }

    public function testCompose(): void
    {
        $add1 = fn ($x) => $x + 1;
        $double = fn ($x) => $x * 2;

        $composed = Callables::compose($add1, $double);
        Assert::same(11, $composed(5));
    }

    public function testComposeMultiple(): void
    {
        $add1 = fn ($x) => $x + 1;
        $double = fn ($x) => $x * 2;
        $sub3 = fn ($x) => $x - 3;

        $composed = Callables::compose($add1, $double, $sub3);
        Assert::same(15, $composed(10));
    }

    public function testComposeSingle(): void
    {
        $double = fn ($x) => $x * 2;
        $composed = Callables::compose($double);
        Assert::same(10, $composed(5));
    }

    public function testConstant(): void
    {
        $always42 = Callables::constant(42);
        Assert::same(42, $always42());
        Assert::same(42, $always42(1, 2, 3));
    }

    public function testConstantWithDifferentTypes(): void
    {
        $constantFn = Callables::constant(['a', 'b']);
        Assert::same(['a', 'b'], $constantFn());

        $null = Callables::constant(null);
        Assert::null($null());
    }

    public function testCurry(): void
    {
        $add = fn ($a, $b, $c) => $a + $b + $c;
        $curried = Callables::curry($add);

        Assert::same(6, $curried(1, 2, 3));
        Assert::same(6, $curried(1)(2)(3));
        Assert::same(6, $curried(1, 2)(3));
        Assert::same(6, $curried(1)(2, 3));
    }

    public function testCurryExceedsArity(): void
    {
        $add = fn ($a, $b) => $a + $b;
        $curried = Callables::curry($add);

        Assert::same(3, $curried(1, 2, 999));
    }

    public function testCurryWithCustomArity(): void
    {
        $add = fn ($a, $b, $c = 0) => $a + $b + $c;
        $curried = Callables::curry($add, 2);

        Assert::same(3, $curried(1)(2));
    }

    public function testDefer(): void
    {
        $fn = fn ($x) => $x * 2;
        $deferred = Callables::defer($fn, 10);

        $start = microtime(true);
        $result = $deferred(5);
        $elapsed = (microtime(true) - $start) * 1000;

        Assert::same(10, $result);
        Assert::true($elapsed >= 9);
    }

    public function testDeferDefaultDelay(): void
    {
        $deferred = Callables::defer(fn () => 'done');
        $start = microtime(true);
        $result = $deferred();
        $elapsed = (microtime(true) - $start) * 1000;

        Assert::same('done', $result);
        Assert::true($elapsed >= 400);
    }

    public function testExecutionDelayConstant(): void
    {
        Assert::same(500, Callables::EXECUTION_DELAY);
    }

    public function testFlip(): void
    {
        $divide = fn ($a, $b) => $a / $b;
        $flipped = Callables::flip($divide);

        Assert::same(0.2, $flipped(10, 2));
    }

    public function testFlipWithMultipleArgs(): void
    {
        $fn = fn ($a, $b, $c) => "$a-$b-$c";
        $flipped = Callables::flip($fn);

        Assert::same('3-2-1', $flipped(1, 2, 3));
    }

    public function testIdentity(): void
    {
        $id = Callables::identity();

        Assert::same(5, $id(5));
        Assert::same('hello', $id('hello'));
        Assert::same([1, 2], $id([1, 2]));
        Assert::null($id(null));
    }

    public function testIfElseFalseCondition(): void
    {
        $check = Callables::if(
            fn ($n) => $n % 2 === 0,
            fn ($n) => 'even',
            fn ($n) => 'odd'
        );

        Assert::same('odd', $check(5));
    }

    public function testIfElseNullElseBranch(): void
    {
        $check = Callables::if(
            fn ($n) => $n > 0,
            fn ($n) => 'positive',
            null
        );

        Assert::same('positive', $check(5));
        Assert::null($check(-1));
    }

    public function testIfElseTrueCondition(): void
    {
        $check = Callables::if(
            fn ($n) => $n % 2 === 0,
            fn ($n) => 'even',
            fn ($n) => 'odd'
        );

        Assert::same('even', $check(4));
    }

    public function testIsCallableFalse(): void
    {
        Assert::false(Callables::isCallable('not_a_function_xyz'));
        Assert::false(Callables::isCallable(42));
        Assert::false(Callables::isCallable([]));
        Assert::false(Callables::isCallable(null));
    }

    public function testIsCallableTrue(): void
    {
        Assert::true(Callables::isCallable('strlen'));
        Assert::true(Callables::isCallable(fn ($x) => $x));
        Assert::true(Callables::isCallable(function () {
        }));
    }

    public function testIsClosureFalse(): void
    {
        Assert::false(Callables::isClosure('strlen'));
        Assert::false(Callables::isClosure(42));
        Assert::false(Callables::isClosure(null));
    }

    public function testIsClosureTrue(): void
    {
        Assert::true(Callables::isClosure(fn ($x) => $x));
        Assert::true(Callables::isClosure(function () {
        }));
    }

    public function testIsFunctionFalse(): void
    {
        Assert::false(Callables::isFunction('not_a_function_xyz'));
        Assert::false(Callables::isFunction(fn ($x) => $x));
        Assert::false(Callables::isFunction(['DateTime', 'format']));
        Assert::false(Callables::isFunction(42));
        Assert::false(Callables::isFunction(null));
    }

    public function testIsFunctionTrue(): void
    {
        Assert::true(Callables::isFunction('strlen'));
        Assert::true(Callables::isFunction('array_map'));
    }

    public function testIsInvokableClosureReturnsTrue(): void
    {
        Assert::true(Callables::isInvokable(fn ($x) => $x));
    }

    public function testIsInvokableFalse(): void
    {
        Assert::false(Callables::isInvokable(new DateTime()));
        Assert::false(Callables::isInvokable('strlen'));
        Assert::false(Callables::isInvokable(42));
    }

    public function testIsInvokableTrue(): void
    {
        $invoker = new class () {
            public function __invoke($x)
            {
                return $x * 2;
            }
        };

        Assert::true(Callables::isInvokable($invoker));
    }

    public function testIsMethodFalse(): void
    {
        Assert::false(Callables::isMethod('strlen'));
        Assert::false(Callables::isMethod(['only_one_element']));
        Assert::false(Callables::isMethod(fn ($x) => $x));
        Assert::false(Callables::isMethod([new stdClass(), 'nonexistent']));
        Assert::false(Callables::isMethod(42));
    }

    public function testIsMethodTrue(): void
    {
        $obj = new DateTime();
        Assert::true(Callables::isMethod([$obj, 'format']));
        Assert::true(Callables::isMethod([DateTime::class, 'createFromFormat']));
    }

    public function testIsStaticFalse(): void
    {
        Assert::false(Callables::isStatic([new DateTime(), 'format']));
        Assert::false(Callables::isStatic(fn ($x) => $x));
    }

    public function testIsStaticTrue(): void
    {
        Assert::true(Callables::isStatic([DateTime::class, 'createFromFormat']));
        Assert::true(Callables::isStatic('strlen'));
    }

    public function testMaxAttemptsConstant(): void
    {
        Assert::same(10, Callables::MAX_ATTEMPTS);
    }

    public function testMemoize(): void
    {
        $calls = 0;
        $memoized = Callables::memoize('strtoupper');

        Assert::same('HELLO', $memoized('hello'));
        Assert::same('HELLO', $memoized('hello'));
    }

    public function testMemoizeDifferentArgs(): void
    {
        $memoized = Callables::memoize('strtoupper');

        Assert::same('HELLO', $memoized('hello'));
        Assert::same('WORLD', $memoized('world'));
    }

    public function testMemoizeNullTtlNeverExpires(): void
    {
        $memoized = Callables::memoize('strtoupper', null);
        $result1 = $memoized('test');
        $result2 = $memoized('test');

        Assert::same('TEST', $result1);
        Assert::same('TEST', $result2);
    }

    public function testNegate(): void
    {
        $isEven = fn ($n) => $n % 2 === 0;
        $isOdd = Callables::negate($isEven);

        Assert::false($isOdd(4));
        Assert::true($isOdd(5));
    }

    public function testNegateWithTruthyValues(): void
    {
        $alwaysTrue = fn () => true;
        $negated = Callables::negate($alwaysTrue);

        Assert::false($negated());
    }

    public function testOnce(): void
    {
        $once = Callables::once('strtoupper');

        Assert::same('HELLO', $once('hello'));
        Assert::null($once('hello'));
        Assert::null($once('world'));
    }

    public function testOnceDifferentCallbacks(): void
    {
        $once1 = Callables::once('strtolower');
        $once2 = Callables::once('trim');

        Assert::same('hello', $once1('HELLO'));
        Assert::null($once1('WORLD'));
        Assert::same('hello', $once2('  hello  '));
        Assert::null($once2('  world  '));
    }

    public function testPartial(): void
    {
        $subtract = fn ($a, $b, $c) => $a - $b - $c;
        $partial = Callables::partial($subtract, 10);

        Assert::same(5, $partial(2, 3));
    }

    public function testPartialMultipleArgs(): void
    {
        $add = fn ($a, $b, $c, $d) => $a + $b + $c + $d;
        $partial = Callables::partial($add, 1, 2);

        Assert::same(10, $partial(3, 4));
    }

    public function testPartialRight(): void
    {
        $subtract = fn ($a, $b, $c) => $a - $b - $c;
        $partial = Callables::partialRight($subtract, 3);

        Assert::same(5, $partial(10, 2));
    }

    public function testPartialRightMultipleArgs(): void
    {
        $add = fn ($a, $b, $c, $d) => $a + $b + $c + $d;
        $partial = Callables::partialRight($add, 3, 4);

        Assert::same(10, $partial(1, 2));
    }

    public function testPassthrough(): void
    {
        $captured = null;
        $callback = function ($value) use (&$captured) {
            $captured = $value;
        };

        $result = Callables::passthrough($callback, 'hello');

        Assert::same('hello', $result);
        Assert::same('hello', $captured);
    }

    public function testPassthroughPreservesType(): void
    {
        $inputArray = [1, 2, 3];
        $result = Callables::passthrough(fn ($v) => null, $inputArray);
        Assert::same([1, 2, 3], $result);
    }

    public function testPipe(): void
    {
        $add1 = fn ($x) => $x + 1;
        $double = fn ($x) => $x * 2;

        $piped = Callables::pipe($add1, $double);
        Assert::same(12, $piped(5));
    }

    public function testPipeMultiple(): void
    {
        $add1 = fn ($x) => $x + 1;
        $double = fn ($x) => $x * 2;
        $sub3 = fn ($x) => $x - 3;

        $piped = Callables::pipe($add1, $double, $sub3);
        Assert::same(19, $piped(10));
    }

    public function testPipeSingle(): void
    {
        $double = fn ($x) => $x * 2;
        $piped = Callables::pipe($double);
        Assert::same(10, $piped(5));
    }

    public function testRateLimit(): void
    {
        $limited = Callables::rateLimit('strlen', 3, 10000);

        Assert::same(5, $limited('hello'));
        Assert::same(5, $limited('world'));
        Assert::same(3, $limited('foo'));
    }

    public function testRateLimitExceeded(): void
    {
        $limited = Callables::rateLimit('trim', 2, 10000);

        $limited('  a  ');
        $limited('  b  ');

        Assert::exception(
            fn () => $limited('  c  '),
            RuntimeException::class
        );
    }

    public function testRetryAllAttemptsFail(): void
    {
        $fn = fn () => throw new RuntimeException('always fail');
        $retry = Callables::retry($fn, 3, 0);

        Assert::exception(
            fn () => $retry(),
            RuntimeException::class,
            'always fail'
        );
    }

    public function testRetrySucceedsAfterFailures(): void
    {
        $attempts = 0;
        $fn = function () use (&$attempts) {
            $attempts++;
            if ($attempts < 3) {
                throw new RuntimeException('fail');
            }

            return 'success';
        };

        $retry = Callables::retry($fn, 3, 0);
        Assert::same('success', $retry());
    }

    public function testRetrySucceedsImmediately(): void
    {
        $fn = fn ($x) => $x * 2;
        $retry = Callables::retry($fn, 3, 0);

        Assert::same(10, $retry(5));
    }

    public function testSafeFailure(): void
    {
        $fn = fn () => throw new RuntimeException('boom');
        $safe = Callables::safe($fn);

        [$error, $result] = $safe();

        Assert::type(RuntimeException::class, $error);
        Assert::null($result);
        Assert::same('boom', $error->getMessage());
    }

    public function testSafeSuccess(): void
    {
        $fn = fn ($a, $b) => $a + $b;
        $safe = Callables::safe($fn);

        [$error, $result] = $safe(3, 4);

        Assert::null($error);
        Assert::same(7, $result);
    }

    public function testSpread(): void
    {
        $add = fn ($a, $b, $c) => $a + $b + $c;
        $spread = Callables::spread($add);

        Assert::same(6, $spread([1, 2, 3]));
    }

    public function testSpreadWithEmptyArray(): void
    {
        $fn = fn () => 42;
        $spread = Callables::spread($fn);

        Assert::same(42, $spread([]));
    }

    public function testTap(): void
    {
        $captured = null;
        $tap = Callables::tap(function ($value) use (&$captured) {
            $captured = $value;
        });

        $result = $tap('hello');

        Assert::same('hello', $result);
        Assert::same('hello', $captured);
    }

    public function testTapInPipeline(): void
    {
        $log = [];
        $pipeline = Callables::pipe(
            fn ($x) => $x * 2,
            Callables::tap(function ($x) use (&$log) {
                $log[] = $x;
            }),
            fn ($x) => $x + 1
        );

        Assert::same(11, $pipeline(5));
        Assert::same([10], $log);
    }

    public function testThrottle(): void
    {
        $calls = 0;
        $fn = function () use (&$calls) {
            $calls++;

            return 'result';
        };

        $throttled = Callables::throttle($fn, 100);

        Assert::same('result', $throttled());
        Assert::null($throttled());
        Assert::same(1, $calls);
    }

    public function testThrottleAllowsAfterInterval(): void
    {
        $calls = 0;
        $fn = function () use (&$calls) {
            $calls++;

            return 'result';
        };

        $throttled = Callables::throttle($fn, 50);

        $throttled();
        $throttled();
        usleep(60000);
        $throttled();

        Assert::same(2, $calls);
    }

    public function testTime(): void
    {
        $fn = fn ($x) => $x * 2;
        $result = Callables::time($fn, 5);

        Assert::same(10, $result['result']);
        Assert::true($result['time'] >= 0);
    }

    public function testTimeMeasuresDuration(): void
    {
        $fn = function () {
            usleep(50000);

            return 'done';
        };

        $result = Callables::time($fn);

        Assert::same('done', $result['result']);
        Assert::true($result['time'] >= 40);
    }

    public function testToCallableWithClosure(): void
    {
        $closure = fn ($x) => $x * 2;
        $result = Callables::toCallable($closure);

        Assert::true($result instanceof Closure);
    }

    public function testToCallableWithNamedFunction(): void
    {
        $closure = Closure::fromCallable('strlen');
        $result = Callables::toCallable($closure);

        Assert::same('strlen', $result);
    }

    public function testToClosure(): void
    {
        $closure = Callables::toClosure('strlen');

        Assert::true($closure instanceof Closure);
        Assert::same(5, $closure('hello'));
    }

    public function testToClosureWithMethod(): void
    {
        $obj = new DateTime('2023-01-01');
        $closure = Callables::toClosure([$obj, 'format']);

        Assert::true($closure instanceof Closure);
        Assert::same('2023-01-01', $closure('Y-m-d'));
    }

    public function testToStringWithClosure(): void
    {
        Assert::same('{closure}', Callables::toString(fn ($x) => $x));
    }

    public function testToStringWithFunction(): void
    {
        Assert::same('strlen', Callables::toString('strlen'));
    }

    public function testToStringWithInvokable(): void
    {
        $invoker = new class () {
            public function __invoke()
            {
            }
        };

        $result = Callables::toString($invoker);
        Assert::true(str_contains($result, '__invoke'));
    }

    public function testToStringWithStaticMethod(): void
    {
        Assert::same('DateTime::createFromFormat', Callables::toString([DateTime::class, 'createFromFormat']));
    }

    public function testUnary(): void
    {
        $fn = fn ($a) => $a * 2;
        $unary = Callables::unary($fn);

        Assert::same(10, $unary(5));
    }

    public function testUnaryIgnoresExtraArgs(): void
    {
        $fn = fn ($x) => $x * 2;
        $unary = Callables::unary($fn);

        Assert::same(10, $unary(5, 'ignored', null));
    }

    public function testUnlessFalseCondition(): void
    {
        $fn = fn ($msg) => strtoupper($msg);
        $unless = Callables::unless($fn, false);

        Assert::same('HELLO', $unless('hello'));
    }

    public function testUnlessTrueCondition(): void
    {
        $ran = false;
        $fn = function () use (&$ran) {
            $ran = true;

            return 'executed';
        };

        $unless = Callables::unless($fn, true);
        $result = $unless('input');

        Assert::false($ran);
        Assert::same('input', $result);
    }

    public function testWhenFalseCondition(): void
    {
        $ran = false;
        $fn = function () use (&$ran) {
            $ran = true;

            return 'executed';
        };

        $when = Callables::when($fn, false);
        $result = $when('input');

        Assert::false($ran);
        Assert::same('input', $result);
    }

    public function testWhenFalseConditionNoArgs(): void
    {
        $when = Callables::when(fn () => 'x', false);
        Assert::null($when());
    }

    public function testWhenTrueCondition(): void
    {
        $fn = fn ($msg) => strtoupper($msg);
        $when = Callables::when($fn, true);

        Assert::same('HELLO', $when('hello'));
    }

    public function testWrapWithBeforeAndAfter(): void
    {
        $log = [];
        $before = function () use (&$log) {
            $log[] = 'before';
        };
        $main = fn ($x) => $x * 2;
        $after = function ($result) use (&$log) {
            $log[] = "after:$result";
        };

        $wrapped = Callables::wrap($main, $before, $after);
        $result = $wrapped(5);

        Assert::same(10, $result);
        Assert::same(['before', 'after:10'], $log);
    }

    public function testWrapWithNoHooks(): void
    {
        $wrapped = Callables::wrap(fn ($x) => $x + 1);
        Assert::same(6, $wrapped(5));
    }

    public function testWrapWithOnlyAfter(): void
    {
        $log = [];
        $after = function ($result) use (&$log) {
            $log[] = $result;
        };

        $wrapped = Callables::wrap(fn ($x) => $x + 1, null, $after);
        $result = $wrapped(5);

        Assert::same(6, $result);
        Assert::same([6], $log);
    }

    public function testWrapWithOnlyBefore(): void
    {
        $log = [];
        $before = function () use (&$log) {
            $log[] = 'before';
        };

        $wrapped = Callables::wrap(fn ($x) => $x + 1, $before);
        $result = $wrapped(5);

        Assert::same(6, $result);
        Assert::same(['before'], $log);
    }
}

(new CallablesTest())->run();
