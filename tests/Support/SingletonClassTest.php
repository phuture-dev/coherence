<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Support;

use Throwable;
use Tester\Assert;
use ReflectionClass;
use Tester\TestCase;
use Phuture\Coherence\Support\SingletonClass;
use Phuture\Coherence\Exception\MemberAccessException;
use Phuture\Coherence\Exception\SerializationException;

require __DIR__ . '/../bootstrap.php';

class SingletonClassTest extends TestCase
{
    public function testCannotClone(): void
    {
        $instance = TestSingletonClass::getInstance();

        Assert::exception(function () use ($instance) {
            // Attempt to clone
            $clone = clone $instance;
        }, Throwable::class);
    }

    public function testCannotInstantiate(): void
    {
        Assert::exception(function () {
            // Attempt to instantiate directly
            $reflection = new ReflectionClass(TestSingletonClass::class);
            $constructor = $reflection->getConstructor();
            $constructor->setAccessible(true);
            $constructor->newInstance();
        }, Throwable::class);
    }

    public function testCannotSerialize(): void
    {
        $instance = TestSingletonClass::getInstance();

        Assert::exception(function () use ($instance) {
            // Attempt to serialize
            $serialized = serialize($instance);
        }, SerializationException::class);
    }

    public function testCannotUnserialize(): void
    {
        Assert::exception(function () {
            // Attempt to unserialize - even with dummy data
            $data = 'O:50:"Phuture\Coherence\Tests\Support\TestSingletonClass":0:{}';
            $unserialized = unserialize($data);
        }, SerializationException::class);
    }

    public function testClassIsAbstract(): void
    {
        // Verify that SingletonClass itself is not meant to be instantiated
        $reflection = new ReflectionClass(SingletonClass::class);
        Assert::true($reflection->isAbstract());
    }

    public function testInheritanceWorks(): void
    {
        // Test that extending classes maintain their own instances
        $instance1 = TestSingletonClass::getInstance();
        $instance2 = AnotherTestSingletonClass::getInstance();

        // Different classes should have different instances
        Assert::true($instance1 === $instance2);
    }

    public function testInstanceIsOfClassType(): void
    {
        $instance = TestSingletonClass::getInstance();
        Assert::type(TestSingletonClass::class, $instance);
        Assert::type(SingletonClass::class, $instance);
    }

    public function testMultipleCallsReturnSameInstance(): void
    {
        $instances = [];

        // Get multiple instances
        for ($i = 0; $i < 5; $i++) {
            $instances[] = TestSingletonClass::getInstance();
        }

        // All should be the same
        $firstId = spl_object_id($instances[0]);
        foreach ($instances as $instance) {
            Assert::same($firstId, spl_object_id($instance));
        }
    }
    public function testSingletonInstance(): void
    {
        // Get two instances
        $instance1 = TestSingletonClass::getInstance();
        $instance2 = TestSingletonClass::getInstance();

        // They should be the same object
        Assert::true($instance1 === $instance2);
        Assert::same(spl_object_id($instance1), spl_object_id($instance2));
    }

    public function testStaticMethodCall(): void
    {
        // Test that static method calls are properly forwarded
        $result = TestSingletonClass::testMethod('hello');
        Assert::same('hello', $result);
    }

    public function testUndefinedStaticMethodCall(): void
    {
        Assert::exception(function () {
            TestSingletonClass::nonExistentMethod();
        }, MemberAccessException::class);
    }
}

/**
 * Test singleton implementation
 */
class TestSingletonClass extends SingletonClass
{
    public static function testMethod(string $input): string
    {
        return $input;
    }
}

/**
 * Another test singleton implementation
 */
class AnotherTestSingletonClass extends SingletonClass
{
    public static function doSomething(): string
    {
        return 'done';
    }
}

(new SingletonClassTest())->run();
