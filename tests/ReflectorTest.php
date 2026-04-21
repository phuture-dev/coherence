<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use stdClass;
use Phuture\Coherence\Reflector;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Exception\InvalidArgumentException;

require __DIR__ . '/bootstrap.php';

class ReflectorTest extends TestCase
{
    public function testAliasAcceptsObjectInstance(): void
    {
        $object = new stdClass();
        $result = Reflector::alias($object, 'StdClassAliasFromObject');
        Assert::true($result);
        Assert::true(class_exists('StdClassAliasFromObject'));
    }

    public function testAliasAcceptsClassName(): void
    {
        $result = Reflector::alias(stdClass::class, 'StdClassAliasFromString');
        Assert::true($result);
        Assert::true(class_exists('StdClassAliasFromString'));
    }

    public function testAliasThrowsWhenAliasAlreadyExists(): void
    {
        Assert::exception(
            fn () => Reflector::alias(stdClass::class, stdClass::class),
            InvalidArgumentException::class,
            'Invalid Argument: The given alias already exists'
        );
    }

    public function testAliasThrowsWhenClassDoesNotExist(): void
    {
        Assert::exception(
            fn () => Reflector::alias('NonExistentClass', 'SomeAlias'),
            InvalidArgumentException::class,
            'Invalid Argument: The given class does not exist'
        );
    }
}

(new ReflectorTest())->run();
