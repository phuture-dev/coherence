<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Closure;
use stdClass;
use RuntimeException;
use Phuture\Coherence\Callables;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

class CallablesTest extends TestCase
{
    public function testAfterExecutesHookAfterCallback(): void
    {
        $log = [];
        $callback = fn($x) => $x * 2;
        $after = function ($result, $x) use (&$log) {
            $log[] = "result={$result}, arg={$x}";
        };

        $wrapped = Callables::after($callback, $after);
        $result = $wrapped(5);

        Assert::same(10, $result);
        Assert::same(['result=10, arg=5'], $log);
    }

    public function testAfterReturnsMainFunctionResult(): void
    {
        $callback = fn($x) => $x * 2;
        $after = fn($result, $x) => 'ignored';

        $wrapped = Callables::after($callback, $after);
        Assert::same(10, $wrapped(5));
    }

    public function testAfterWithMultipleArguments(): void
    {
        $capturedArgs = [];
        $callback = fn($a, $b) => $a + $b;
        $after = function ($result, ...$args) use (&$capturedArgs) {
            $capturedArgs = $args;
        };

        $wrapped = Callables::after($callback, $after);
        $wrapped(3, 7);

        Assert::same([3, 7], $capturedArgs);
    }

    public function testApplySpreadsArrayAsArguments(): void
    {
        $callback = fn($a, $b, $c) => $a + $b + $c;
        Assert::same(6, Callables::apply($callback, [1, 2, 3]));
    }

    public function testApplyWithEmptyArray(): void
    {
        $callback = fn() => 42;
        Assert::same(42, Callables::apply($callback, []));
    }

    public function testApplyWithSingleArgument(): void
    {
        $callback = fn($x) => $x * 3;
        Assert::same(9, Callables::apply($callback, [3]));
    }

    public function testBeforeExecutesHookBeforeCallback(): void
    {
        $log = [];
        $before = function (...$args) use (&$log) {
            $log[] = 'before:' . implode(',', $args);
        };
        $callback = fn($x) => $x * 2;

        $wrapped = Callables::before($before, $callback);
        $result = $wrapped(5);

        Assert::same(10, $result);
        Assert::same(['before:5'], $log);
    }

    public function testBeforeReturnsMainFunctionResult(): void
    {
        $before = fn() => 'ignored';
        $callback = fn($x) => $x * 3;

        $wrapped = Callables::before($before, $callback);
        Assert::same(15, $wrapped(5));
    }

    public function testBinaryPassesOnlyFirstTwoArguments(): void
    {
        $callback = fn($a, $b) => "{$a}-{$b}";
        $binary = Callables::binary($callback);

        Assert::same('1-2', $binary(1, 2, 3, 4));
    }

    public function testBinaryWithExactlyTwoArguments(): void
    {
        $callback = fn($a, $b) => $a + $b;
        $binary = Callables::binary($callback);

        Assert::same(5, $binary(2, 3));
    }

    public function testBindClosureToObject(): void
    {
        $object = new class {
            public string $value = 'hello';
        };

        $closure = function () {
            assert($this instanceof object);

            return $this->value;
        };
        $bound = Callables::bind($closure, $object);

        Assert::same('hello', $bound());
    }

    public function testBindClosureToNull(): void
    {
        $captured = null;
        $closure = fn() => 'static result';
        $bound = Callables::bind($closure, null);

        Assert::same('static result', $bound());
    }

    public function testCallWithMultipleArguments(): void
    {
        $callback = fn($a, $b) => $a + $b;
        Assert::same(8, Callables::call($callback, 5, 3));
    }

    public function testCallWithNoArguments(): void
    {
        $callback = fn() => 'result';
        Assert::same('result', Callables::call($callback));
    }

    public function testCallWithSingleArgument(): void
    {
        $callback = fn($x) => $x * 2;
        Assert::same(10, Callables::call($callback, 5));
    }

    public function testCatchHandlesException(): void
    {
        $callback = fn() => throw new RuntimeException('test error');
        $handler = fn($e) => $e->getMessage();

        $wrapped = Callables::catch($callback, $handler);
        Assert::same('test error', $wrapped());
    }

    public function testCatchReturnsCallbackResultOnSuccess(): void
    {
        $callback = fn($x) => $x * 2;
        $handler = fn($e) => 'error';

        $wrapped = Callables::catch($callback, $handler);
        Assert::same(10, $wrapped(5));
    }

    public function testCatchPassesExceptionAndArgsToHandler(): void
    {
        $capturedArgs = [];
        $callback = fn($a, $b) => throw new RuntimeException('fail');
        $handler = function ($e, ...$args) use (&$capturedArgs) {
            $capturedArgs = $args;
            return $e->getMessage();
        };

        $wrapped = Callables::catch($callback, $handler);
        $wrapped('x', 'y');

        Assert::same(['x', 'y'], $capturedArgs);
    }

    public function testComposeAppliesFunctionsRightToLeft(): void
    {
        $add1 = fn($x) => $x + 1;
        $double = fn($x) => $x * 2;

        $composed = Callables::compose($add1, $double);
        Assert::same(11, $composed(5));
    }

    public function testComposeWithSingleFunction(): void
    {
        $double = fn($x) => $x * 2;
        $composed = Callables::compose($double);

        Assert::same(10, $composed(5));
    }

    public function testComposeWithMultipleFunctions(): void
    {
        $add1 = fn($x) => $x + 1;
        $double = fn($x) => $x * 2;
        $subtract3 = fn($x) => $x - 3;

        $composed = Callables::compose($add1, $double, $subtract3);
        Assert::same(15, $composed(10));
    }

    public function testComposeWithNoFunctions(): void
    {
        $composed = Callables::compose();
        Assert::same(42, $composed(42));
    }

    public function testConstantAlwaysReturnsSameValue(): void
    {
        $always42 = Callables::constant(42);

        Assert::same(42, $always42());
        Assert::same(42, $always42(1, 2, 3));
        Assert::same(42, $always42('ignored'));
    }

    public function testConstantWithDifferentTypes(): void
    {
        $alwaysArray = Callables::constant([1, 2, 3]);
        Assert::same([1, 2, 3], $alwaysArray());

        $alwaysString = Callables::constant('hello');
        Assert::same('hello', $alwaysString());

        $alwaysNull = Callables::constant(null);
        Assert::null($alwaysNull());
    }

    public function testCurryWithThreeArguments(): void
    {
        $add = fn($a, $b, $c) => $a + $b + $c;
        $curried = Callables::curry($add);

        $step1 = $curried(1);
        $step2 = $step1(2);
        $result = $step2(3);

        Assert::same(6, $result);
    }

    public function testCurryWithMultipleArgumentsAtOnce(): void
    {
        $add = fn($a, $b, $c) => $a + $b + $c;
        $curried = Callables::curry($add);

        Assert::same(35, $curried(10, 20)(5));
    }

    public function testCurryWithAllArgumentsAtOnce(): void
    {
        $add = fn($a, $b, $c) => $a + $b + $c;
        $curried = Callables::curry($add);

        Assert::same(6, $curried(1, 2, 3));
    }

    public function testCurryWithCustomArity(): void
    {
        $concat = fn($a, $b, $c = '') => $a . $b . $c;
        $curried = Callables::curry($concat, 2);

        Assert::same('ab', $curried('a')('b'));
        Assert::same('ab', $curried('a', 'b', 'c'));
    }

    public function testDeferDelaysExecution(): void
    {
        $callback = fn($x) => $x * 2;
        $deferred = Callables::defer($callback, 10);

        $start = microtime(true);
        $result = $deferred(5);
        $elapsed = (microtime(true) - $start) * 1000;

        Assert::same(10, $result);
        Assert::true($elapsed >= 8);
    }

    public function testDeferWithDefaultDelay(): void
    {
        $callback = fn() => 'done';
        $deferred = Callables::defer($callback);

        Assert::same('done', $deferred());
    }

    public function testFlipReversesArguments(): void
    {
        $divide = fn($a, $b) => $a / $b;
        $flipped = Callables::flip($divide);

        Assert::same(0.2, $flipped(10, 2));
    }

    public function testFlipWithThreeArguments(): void
    {
        $callback = fn($a, $b, $c) => "{$a}-{$b}-{$c}";
        $flipped = Callables::flip($callback);

        Assert::same('3-2-1', $flipped(1, 2, 3));
    }

    public function testIdentityReturnsInputUnchanged(): void
    {
        $identity = Callables::identity();

        Assert::same(5, $identity(5));
        Assert::same('hello', $identity('hello'));
        Assert::same([1, 2, 3], $identity([1, 2, 3]));
        Assert::null($identity(null));
    }

    public function testIfExecutesThenWhenConditionTrue(): void
    {
        $isEven = fn($n) => $n % 2 === 0;
        $sayEven = fn($n) => "{$n} is even";
        $sayOdd = fn($n) => "{$n} is odd";

        $check = Callables::if($isEven, $sayEven, $sayOdd);

        Assert::same('4 is even', $check(4));
        Assert::same('5 is odd', $check(5));
    }

    public function testIfReturnsNullWhenConditionFalseAndNoElse(): void
    {
        $isEven = fn($n) => $n % 2 === 0;
        $sayEven = fn($n) => "{$n} is even";

        $check = Callables::if($isEven, $sayEven, null);

        Assert::same('4 is even', $check(4));
        Assert::null($check(5));
    }

    public function testIsCallableWithFunctionString(): void
    {
        Assert::true(Callables::isCallable('strlen'));
    }

    public function testIsCallableWithClosure(): void
    {
        Assert::true(Callables::isCallable(fn() => true));
    }

    public function testIsCallableWithMethodArray(): void
    {
        Assert::true(Callables::isCallable([new \DateTime(), 'format']));
    }

    public function testIsCallableWithNonCallable(): void
    {
        Assert::false(Callables::isCallable('nonexistent_function_xyz'));
        Assert::false(Callables::isCallable(123));
        Assert::false(Callables::isCallable(null));
    }

    public function testIsClosureWithAnonymousFunction(): void
    {
        Assert::true(Callables::isClosure(fn() => true));
        Assert::true(Callables::isClosure(function () {
            return true;
        }));
    }

    public function testIsClosureWithNonClosure(): void
    {
        Assert::false(Callables::isClosure('strlen'));
        Assert::false(Callables::isClosure([\DateTime::class, 'format']));
        Assert::false(Callables::isClosure(123));
    }

    public function testIsFunctionWithBuiltinFunction(): void
    {
        Assert::true(Callables::isFunction('strlen'));
        Assert::true(Callables::isFunction('array_map'));
    }

    public function testIsFunctionWithNonFunction(): void
    {
        Assert::false(Callables::isFunction('nonexistent_function_xyz'));
        Assert::false(Callables::isFunction(fn() => true));
        Assert::false(Callables::isFunction([\DateTime::class, 'format']));
        Assert::false(Callables::isFunction(123));
    }

    public function testIsFunctionWithClassMethod(): void
    {
        Assert::false(Callables::isFunction('DateTime::format'));
    }

    public function testIsInvokableWithInvokableObject(): void
    {
        $invoker = new class {
            public function __invoke(mixed $x): mixed
            {
                return $x * 2;
            }
        };

        Assert::true(Callables::isInvokable($invoker));
    }

    public function testIsInvokableWithNonInvokable(): void
    {
        Assert::false(Callables::isInvokable(new stdClass()));
        Assert::false(Callables::isInvokable('strlen'));
        Assert::false(Callables::isInvokable(123));
    }

    public function testIsInvokableWithClosure(): void
    {
        Assert::true(Callables::isInvokable(fn() => true));
    }

    public function testIsMethodWithInstanceMethod(): void
    {
        $object = new \DateTime();
        Assert::true(Callables::isMethod([$object, 'format']));
    }

    public function testIsMethodWithStaticMethod(): void
    {
        Assert::true(Callables::isMethod([\DateTime::class, 'createFromFormat']));
    }

    public function testIsMethodWithNonMethod(): void
    {
        Assert::false(Callables::isMethod('strlen'));
        Assert::false(Callables::isMethod(fn() => true));
        Assert::false(Callables::isMethod(['only_one_element']));
        Assert::false(Callables::isMethod(['stdClass', 'nonexistent']));
    }

    public function testIsMethodWithInvalidArrayFormat(): void
    {
        Assert::false(Callables::isMethod([123, 'method']));
        Assert::false(Callables::isMethod(['class', 456]));
    }

    public function testIsStaticWithStaticMethodArray(): void
    {
        Assert::true(Callables::isStatic([\DateTime::class, 'createFromFormat']));
    }

    public function testIsStaticWithStaticMethodString(): void
    {
        Assert::true(Callables::isStatic('DateTime::createFromFormat'));
    }

    public function testIsStaticWithInstanceMethod(): void
    {
        Assert::false(Callables::isStatic([new \DateTime(), 'format']));
    }

    public function testIsStaticWithFunctionString(): void
    {
        Assert::true(Callables::isStatic('strlen'));
    }

    public function testIsStaticWithClosure(): void
    {
        Assert::false(Callables::isStatic(fn() => true));
    }

    public function testMemoizeCachesResults(): void
    {
        $memoized = Callables::memoize('strtoupper', null);

        Assert::same('HELLO', $memoized('hello'));
        Assert::same('HELLO', $memoized('hello'));
    }

    public function testMemoizeSeparatesCacheByArguments(): void
    {
        $memoized = Callables::memoize('strtoupper', null);

        Assert::same('HELLO', $memoized('hello'));
        Assert::same('WORLD', $memoized('world'));
    }

    public function testMemoizeCacheExpiresWithTtl(): void
    {
        $memoized = Callables::memoize('strtoupper', 1);

        Assert::same('HELLO', $memoized('hello'));
        Assert::same('HELLO', $memoized('hello'));

        sleep(2);

        Assert::same('HELLO', $memoized('hello'));
    }

    public function testNegateInvertsBooleanResult(): void
    {
        $isEven = fn($n) => $n % 2 === 0;
        $isOdd = Callables::negate($isEven);

        Assert::false($isOdd(4));
        Assert::true($isOdd(5));
    }

    public function testNegateWithTruthiness(): void
    {
        $isNotEmpty = Callables::negate(fn($arr) => empty($arr));

        Assert::true($isNotEmpty([1]));
        Assert::false($isNotEmpty([]));
    }

    public function testOnceExecutesOnlyFirstTime(): void
    {
        $once = Callables::once('strtoupper');

        Assert::same('HELLO', $once('hello'));
        Assert::null($once('world'));
        Assert::null($once('test'));
    }

    public function testOnceWithArguments(): void
    {
        $once = Callables::once('strtolower');

        Assert::same('hello', $once('HELLO'));
        Assert::null($once('WORLD'));
    }

    public function testPartialPreFillsArgumentsFromLeft(): void
    {
        $subtract = fn($a, $b, $c) => $a - $b - $c;
        $partial = Callables::partial($subtract, 10);

        Assert::same(5, $partial(2, 3));
    }

    public function testPartialWithMultiplePreFilledArgs(): void
    {
        $add = fn($a, $b, $c, $d) => $a + $b + $c + $d;
        $partial = Callables::partial($add, 1, 2);

        Assert::same(10, $partial(3, 4));
    }

    public function testPartialWithNoPreFilledArgs(): void
    {
        $add = fn($a, $b) => $a + $b;
        $partial = Callables::partial($add);

        Assert::same(3, $partial(1, 2));
    }

    public function testPartialRightPreFillsArgumentsFromRight(): void
    {
        $subtract = fn($a, $b, $c) => $a - $b - $c;
        $partial = Callables::partialRight($subtract, 3);

        Assert::same(5, $partial(10, 2));
    }

    public function testPartialRightWithMultiplePreFilledArgs(): void
    {
        $add = fn($a, $b, $c, $d) => $a + $b + $c + $d;
        $partial = Callables::partialRight($add, 3, 4);

        Assert::same(10, $partial(1, 2));
    }

    public function testPassthroughExecutesCallbackAndReturnsValue(): void
    {
        $captured = null;
        $callback = function ($value) use (&$captured) {
            $captured = $value;
        };

        $result = Callables::passthrough($callback, 'hello');

        Assert::same('hello', $result);
        Assert::same('hello', $captured);
    }

    public function testPassthroughReturnsOriginalValue(): void
    {
        $callback = fn($value) => strtoupper($value);
        $result = Callables::passthrough($callback, 'hello');

        Assert::same('hello', $result);
    }

    public function testPipeAppliesFunctionsLeftToRight(): void
    {
        $add1 = fn($x) => $x + 1;
        $double = fn($x) => $x * 2;

        $piped = Callables::pipe($add1, $double);
        Assert::same(12, $piped(5));
    }

    public function testPipeWithMultipleFunctions(): void
    {
        $add1 = fn($x) => $x + 1;
        $double = fn($x) => $x * 2;
        $subtract3 = fn($x) => $x - 3;

        $piped = Callables::pipe($add1, $double, $subtract3);
        Assert::same(19, $piped(10));
    }

    public function testPipeWithSingleFunction(): void
    {
        $double = fn($x) => $x * 2;
        $piped = Callables::pipe($double);

        Assert::same(10, $piped(5));
    }

    public function testPipeWithNoFunctions(): void
    {
        $piped = Callables::pipe();
        Assert::same(42, $piped(42));
    }

    public function testRateLimitAllowsUpToMaxAttempts(): void
    {
        $limited = Callables::rateLimit('strtoupper', 3, 60000);

        Assert::same('A', $limited('a'));
        Assert::same('B', $limited('b'));
        Assert::same('C', $limited('c'));
    }

    public function testRateLimitThrowsWhenExceeded(): void
    {
        $limited = Callables::rateLimit('strtolower', 2, 60000);

        $limited('A');
        $limited('B');

        Assert::throws(
            fn() => $limited('C'),
            RuntimeException::class
        );
    }

    public function testRetrySucceedsAfterFailures(): void
    {
        $attempt = 0;
        $callback = function () use (&$attempt) {
            $attempt++;
            if ($attempt < 3) {
                throw new RuntimeException('fail');
            }
            return 'success';
        };

        $retry = Callables::retry($callback, 5, 0);
        Assert::same('success', $retry());
        Assert::same(3, $attempt);
    }

    public function testRetryThrowsAfterAllAttemptsExhausted(): void
    {
        $callback = fn() => throw new RuntimeException('always fails');

        $retry = Callables::retry($callback, 3, 0);

        Assert::throws(
            fn() => $retry(),
            RuntimeException::class,
            'always fails'
        );
    }

    public function testRetrySucceedsOnFirstAttempt(): void
    {
        $callback = fn($x) => $x * 2;
        $retry = Callables::retry($callback, 3, 0);

        Assert::same(10, $retry(5));
    }

    public function testSafeReturnsResultTupleOnSuccess(): void
    {
        $callback = fn($a, $b) => $a + $b;
        $safe = Callables::safe($callback);

        [$error, $result] = $safe(3, 7);

        Assert::null($error);
        Assert::same(10, $result);
    }

    public function testSafeReturnsErrorTupleOnException(): void
    {
        $callback = fn() => throw new RuntimeException('fail');
        $safe = Callables::safe($callback);

        [$error, $result] = $safe();

        Assert::type(RuntimeException::class, $error);
        Assert::null($result);
    }

    public function testSafeWithDivisionByZero(): void
    {
        $callback = fn($a, $b) => $a / $b;
        $safe = Callables::safe($callback);

        [$error, $result] = $safe(10, 0);

        Assert::notNull($error);
        Assert::null($result);
    }

    public function testSpreadPassesArrayElementsAsArguments(): void
    {
        $add = fn($a, $b, $c) => $a + $b + $c;
        $spread = Callables::spread($add);

        Assert::same(6, $spread([1, 2, 3]));
    }

    public function testSpreadWithEmptyArray(): void
    {
        $callback = fn() => 'empty';
        $spread = Callables::spread($callback);

        Assert::same('empty', $spread([]));
    }

    public function testTapReturnsValueUnchanged(): void
    {
        $captured = null;
        $callback = function ($value) use (&$captured) {
            $captured = $value;
        };

        $tapper = Callables::tap($callback);
        $result = $tapper('hello');

        Assert::same('hello', $result);
        Assert::same('hello', $captured);
    }

    public function testTapWithNumericValue(): void
    {
        $tapper = Callables::tap(fn($v) => null);
        Assert::same(42, $tapper(42));
    }

    public function testThrottleExecutesOnFirstCall(): void
    {
        $callback = fn($x) => $x * 2;
        $throttled = Callables::throttle($callback, 1000);

        Assert::same(10, $throttled(5));
    }

    public function testThrottleReturnsNullWhenTooSoon(): void
    {
        $callback = fn($x) => $x * 2;
        $throttled = Callables::throttle($callback, 10000);

        Assert::same(10, $throttled(5));
        Assert::null($throttled(6));
    }

    public function testThrottleExecutesAgainAfterInterval(): void
    {
        $callback = fn($x) => $x * 2;
        $throttled = Callables::throttle($callback, 10);

        Assert::same(10, $throttled(5));

        usleep(15000);

        Assert::same(12, $throttled(6));
    }

    public function testTimeReturnsResultAndElapsedTime(): void
    {
        $callback = fn($x) => $x * 2;
        $result = Callables::time($callback, 5);

        Assert::same(10, $result['result']);
        Assert::true($result['time'] >= 0);
    }

    public function testTimeWithNoArgs(): void
    {
        $callback = fn() => 'done';
        $result = Callables::time($callback);

        Assert::same('done', $result['result']);
        Assert::true($result['time'] >= 0);
    }

    public function testTimeMeasuresActualDelay(): void
    {
        $callback = fn() => usleep(10000);
        $result = Callables::time($callback);

        Assert::true($result['time'] >= 8);
    }

    public function testToCallableWithSimpleClosure(): void
    {
        $closure = fn($x) => $x * 2;
        $result = Callables::toCallable($closure);

        Assert::same($closure, $result);
    }

    public function testToCallableWithNamedFunction(): void
    {
        $closure = Closure::fromCallable('strlen');
        $result = Callables::toCallable($closure);

        Assert::same('strlen', $result);
    }

    public function testToClosureWithFunctionString(): void
    {
        $closure = Callables::toClosure('strlen');

        Assert::type(Closure::class, $closure);
        Assert::same(5, $closure('hello'));
    }

    public function testToClosureWithMethodArray(): void
    {
        $object = new \DateTime();
        $closure = Callables::toClosure([$object, 'format']);

        Assert::type(Closure::class, $closure);
        Assert::true(is_string($closure('Y-m-d')));
    }

    public function testToStringWithFunctionString(): void
    {
        Assert::same('strlen', Callables::toString('strlen'));
    }

    public function testToStringWithStaticMethodArray(): void
    {
        Assert::same('DateTime::createFromFormat', Callables::toString([\DateTime::class, 'createFromFormat']));
    }

    public function testToStringWithClosure(): void
    {
        $result = Callables::toString(fn() => true);
        Assert::true(str_starts_with($result, '{closure'));
    }

    public function testToStringWithInvokableObject(): void
    {
        $invoker = new class {
            public function __invoke(): void
            {
            }
        };

        $result = Callables::toString($invoker);
        Assert::true(str_contains($result, '__invoke'));
    }

    public function testUnaryPassesOnlyFirstArgument(): void
    {
        $callback = fn($a) => $a * 2;
        $unary = Callables::unary($callback);

        Assert::same(10, $unary(5, 100));
    }

    public function testUnaryWithSingleArgument(): void
    {
        $callback = fn($x) => $x * 2;
        $unary = Callables::unary($callback);

        Assert::same(10, $unary(5));
    }

    public function testUnlessExecutesWhenConditionFalse(): void
    {
        $callback = fn($msg) => strtoupper($msg);
        $unless = Callables::unless($callback, false);

        Assert::same('HELLO', $unless('hello'));
    }

    public function testUnlessSkipsWhenConditionTrue(): void
    {
        $callback = fn($msg) => strtoupper($msg);
        $unless = Callables::unless($callback, true);

        Assert::same('hello', $unless('hello'));
    }

    public function testUnlessReturnsNullWhenNoArgsAndConditionTrue(): void
    {
        $callback = fn() => 'executed';
        $unless = Callables::unless($callback, true);

        Assert::null($unless());
    }

    public function testWhenExecutesWhenConditionTrue(): void
    {
        $callback = fn($msg) => strtoupper($msg);
        $when = Callables::when($callback, true);

        Assert::same('HELLO', $when('hello'));
    }

    public function testWhenSkipsWhenConditionFalse(): void
    {
        $callback = fn($msg) => strtoupper($msg);
        $when = Callables::when($callback, false);

        Assert::same('hello', $when('hello'));
    }

    public function testWhenReturnsNullWhenNoArgsAndConditionFalse(): void
    {
        $callback = fn() => 'executed';
        $when = Callables::when($callback, false);

        Assert::null($when());
    }

    public function testWrapWithBeforeAndAfter(): void
    {
        $log = [];
        $before = function () use (&$log) {
            $log[] = 'before';
        };
        $callback = fn($x) => $x * 2;
        $after = function ($result) use (&$log) {
            $log[] = "after:{$result}";
        };

        $wrapped = Callables::wrap($callback, $before, $after);
        $result = $wrapped(5);

        Assert::same(10, $result);
        Assert::same(['before', 'after:10'], $log);
    }

    public function testWrapWithOnlyBefore(): void
    {
        $log = [];
        $before = function () use (&$log) {
            $log[] = 'before';
        };
        $callback = fn($x) => $x * 2;

        $wrapped = Callables::wrap($callback, $before);
        $result = $wrapped(5);

        Assert::same(10, $result);
        Assert::same(['before'], $log);
    }

    public function testWrapWithOnlyAfter(): void
    {
        $log = [];
        $callback = fn($x) => $x * 2;
        $after = function ($result) use (&$log) {
            $log[] = "after:{$result}";
        };

        $wrapped = Callables::wrap($callback, null, $after);
        $result = $wrapped(5);

        Assert::same(10, $result);
        Assert::same(['after:10'], $log);
    }

    public function testWrapWithNoHooks(): void
    {
        $callback = fn($x) => $x * 2;
        $wrapped = Callables::wrap($callback);

        Assert::same(10, $wrapped(5));
    }
}

(new CallablesTest())->run();
