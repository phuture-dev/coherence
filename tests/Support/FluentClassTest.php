<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Support;

use ReflectionClass;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Support\FluentClass;
use Phuture\Coherence\Exception\MemberAccessException;

require __DIR__ . '/../bootstrap.php';

class FluentClassTest extends TestCase
{
    public function testClassIsAbstract(): void
    {
        // Verify that FluentClass itself is not meant to be instantiated
        $reflection = new ReflectionClass(FluentClass::class);
        Assert::true($reflection->isAbstract());
    }

    public function testConstructor(): void
    {
        // Test with initial data
        $fluent = new TestFluentClass('hello');
        Assert::same('hello', $fluent->get());

        // Test with initial data and static method
        $fluent = TestFluentClass::from('hello');
        Assert::same('hello', $fluent->get());

        // Test with no data
        $fluent = new TestFluentClass();
        Assert::null($fluent->get());

        // Test with different data types
        Assert::same(42, (new TestFluentClass(42))->get());
        Assert::same([1, 2, 3], (new TestFluentClass([1, 2, 3]))->get());
    }

    public function testGetMethod(): void
    {
        $fluent = new TestFluentClass('test');
        Assert::same('test', $fluent->get());
    }

    public function testMethodChaining(): void
    {
        // Test custom method chaining
        $result = (new TestFluentClass('  hello  '))
            ->trim()
            ->upper()
            ->reverse()
            ->get();

        Assert::same('OLLEH', $result);
    }

    public function testUndefinedMethodCall(): void
    {
        Assert::exception(function () {
            $fluent = new TestFluentClass();
            $fluent->nonExistentMethod();
        }, MemberAccessException::class);

        Assert::exception(function () {
            TestFluentClass::nonExistentStaticMethod();
        }, MemberAccessException::class);
    }
}

/**
 * Test implementation of FluentClass with custom methods
 */
class TestFluentClass extends FluentClass
{
    public function reverse(): self
    {
        $this->data = strrev($this->data);

        return $this;
    }

    public function trim(): self
    {
        $this->data = trim($this->data);

        return $this;
    }

    public function upper(): self
    {
        $this->data = strtoupper($this->data);

        return $this;
    }
}

(new FluentClassTest())->run();
