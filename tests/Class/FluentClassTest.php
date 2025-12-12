<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Class;

use Phuture\Coherence\Class\FluentClass;
use Phuture\Coherence\Exception\BadMethodCallException;
use Phuture\Coherence\Exception\ClassNotFoundException;
use Phuture\Coherence\Exception\MemberAccessException;
use Phuture\Coherence\Arrays;
use Phuture\Coherence\Exception\InvalidDataTypeException;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

class FluentClassTest extends TestCase
{
    public function testConstructor(): void
    {
        // Test with initial data
        $fluent = new TestFluentClass('hello');
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

    public function testDynamicMethodForwarding(): void
    {
        // Test with Arrays class
        $arrays = new TestArraysFluentClass([1, 2, 3]);

        $result = $arrays
            ->reverse()
            ->filter(fn($v) => $v > 1)
            ->get();

        Assert::same([3, 2], $result);
    }

    public function testDynamicMethodWithArguments(): void
    {
        $arrays = new TestArraysFluentClass([1, 2, 3, 4, 5]);

        $result = $arrays
            ->slice(1, 3)
            ->get();

        Assert::same([2, 3, 4], $result);
    }

    public function testCallWithoutClass(): void
    {
        $fluent = new TestFluentClass('test');

        Assert::exception(function () use ($fluent) {
            $fluent->nonExistentMethod();
        }, BadMethodCallException::class);
    }

    public function testCallWithInvalidClass(): void
    {
        $fluent = new class extends FluentClass {
            protected ?string $class = 'NonExistentClass';
        };

        Assert::exception(function () use ($fluent) {
            $fluent->anyMethod();
        }, ClassNotFoundException::class);
    }

    public function testClassIsAbstract(): void
    {
        // Verify that FluentClass itself is not meant to be instantiated
        $reflection = new \ReflectionClass(FluentClass::class);
        Assert::true($reflection->isAbstract());
    }

    public function testCallNonPublicMethod(): void
    {
        $fluent = new TestFluentClassWithPrivateMethod('test');

        Assert::exception(function () use ($fluent) {
            $fluent->privateMethod();
        }, MemberAccessException::class);
    }

    public function testCallWithInvalidType(): void
    {
        $arrays = new TestArraysFluentClass([1, 2, 3]);

        // This should fail because length() returns int, not array (breaking the fluent chain)
        Assert::exception(function () use ($arrays) {
            $arrays->length();
        }, InvalidDataTypeException::class);
    }
}

/**
 * Test implementation of FluentClass with custom methods
 */
class TestFluentClass extends FluentClass
{
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

    public function reverse(): self
    {
        $this->data = strrev($this->data);
        return $this;
    }
}

/**
 * Test implementation using Arrays fallback
 */
class TestArraysFluentClass extends FluentClass
{
    protected ?string $class = Arrays::class;
}

/**
 * Test implementation for private method access
 */
class TestFluentClassWithPrivateMethod extends FluentClass
{
    protected ?string $class = Arrays::class;

    private function privateMethod(): self
    {
        return $this;
    }
}

(new FluentClassTest())->run();