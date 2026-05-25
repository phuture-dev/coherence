<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Support;

use Error;
use ReflectionClass;
use ReflectionMethod;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Support\StaticClass;
use Phuture\Coherence\Exception\MemberAccessException;

require __DIR__ . '/../bootstrap.php';

class StaticClassTest extends TestCase
{
    public function testCannotInstantiate(): void
    {
        Assert::exception(function () {
            // Attempt to instantiate directly
            new TestStaticClass();
        }, Error::class);
    }

    public function testCannotInstantiateWithReflection(): void
    {
        Assert::exception(function () {
            // Attempt to instantiate via reflection
            $reflection = new ReflectionClass(TestStaticClass::class);
            $constructor = $reflection->getConstructor();
            $constructor->newInstance();
        }, Error::class);
    }

    public function testClassExistsAndIsAccessible(): void
    {
        Assert::true(class_exists(StaticClass::class));
        Assert::true(class_exists(TestStaticClass::class));
    }

    public function testClassIsAbstract(): void
    {
        // Verify that StaticClass itself is not meant to be instantiated
        $reflection = new ReflectionClass(StaticClass::class);
        Assert::true($reflection->isAbstract());
    }

    public function testClassName(): void
    {
        Assert::same('Phuture\Coherence\Support\StaticClass', StaticClass::class);
        Assert::same('Phuture\Coherence\Tests\Support\TestStaticClass', TestStaticClass::class);
    }

    public function testInheritanceWorks(): void
    {
        // Test that extending classes work normally
        $result = ExtendedStaticClass::extendedMethod('test');
        Assert::same('extended: test', $result);

        // Test that parent methods are accessible
        $result = ExtendedStaticClass::testMethod('parent');
        Assert::same('parent', $result);
    }

    public function testIsInstanceofStaticClass(): void
    {
        // Even though static classes aren't instantiated, we can check class hierarchy
        Assert::true(is_a(TestStaticClass::class, StaticClass::class, true));
        Assert::true(is_a(ExtendedStaticClass::class, StaticClass::class, true));
    }

    public function testMethodVisibility(): void
    {
        // Public method should work
        Assert::same('public', TestStaticClass::publicMethod());

        // Private method exists but should not be callable
        Assert::true(method_exists(TestStaticClass::class, 'privateMethod'));

        // Verify it's actually private
        $method = new ReflectionMethod(TestStaticClass::class, 'privateMethod');
        Assert::true($method->isPrivate());
    }

    public function testStaticConstantAccess(): void
    {
        Assert::same('CONSTANT_VALUE', TestStaticClass::TEST_CONSTANT);
    }

    public function testStaticMethodCall(): void
    {
        // Test that static method calls work normally
        $result = TestStaticClass::testMethod('hello');
        Assert::same('hello', $result);

        // Test with multiple parameters
        $result = TestStaticClass::testMethodWithParams(5, 10);
        Assert::same(15, $result);

        // Test method that returns boolean
        Assert::true(TestStaticClass::testBooleanMethod(true));
        Assert::false(TestStaticClass::testBooleanMethod(false));
    }

    public function testStaticPropertyAccess(): void
    {
        // Test accessing static properties works
        Assert::same('default', TestStaticClass::$staticProperty);

        // Test modifying static property
        TestStaticClass::$staticProperty = 'modified';
        Assert::same('modified', TestStaticClass::$staticProperty);
    }

    public function testUndefinedStaticMethodCall(): void
    {
        Assert::exception(function () {
            TestStaticClass::nonExistentMethod();
        }, MemberAccessException::class);
    }
}

/**
 * Test static class implementation
 */
class TestStaticClass extends StaticClass
{
    public const TEST_CONSTANT = 'CONSTANT_VALUE';
    public static string $staticProperty = 'default';

    public static function publicMethod(): string
    {
        return 'public';
    }

    public static function testBooleanMethod(bool $input): bool
    {
        return $input;
    }

    public static function testMethod(string $input): string
    {
        return $input;
    }

    public static function testMethodWithParams(int $a, int $b): int
    {
        return $a + $b;
    }

    private static function privateMethod(): string
    {
        return 'private';
    }
}

/**
 * Extended static class to test inheritance
 */
class ExtendedStaticClass extends TestStaticClass
{
    public static function extendedMethod(string $input): string
    {
        return 'extended: ' . $input;
    }
}

(new StaticClassTest())->run();
