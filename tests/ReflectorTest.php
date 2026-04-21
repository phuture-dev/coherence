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
    public function testAliasCreatesWorkingClassAlias(): void
    {
        $alias = 'ReflectorTestAliasTarget_' . uniqid();
        $result = Reflector::alias(stdClass::class, $alias);

        Assert::true($result);
        Assert::true(class_exists($alias));
    }

    public function testAliasAcceptsObjectInstance(): void
    {
        $alias = 'ReflectorTestAliasFromObject_' . uniqid();
        $result = Reflector::alias(new stdClass(), $alias);

        Assert::true($result);
    }

    public function testAliasThrowsWhenAliasAlreadyExists(): void
    {
        Assert::exception(function () {
            Reflector::alias(stdClass::class, \Exception::class);
        }, InvalidArgumentException::class, 'Invalid Argument: The given alias already exists');
    }

    public function testAliasThrowsWhenClassDoesNotExist(): void
    {
        Assert::exception(function () {
            Reflector::alias('NonExistentClassXyz123', 'SomeAlias_' . uniqid());
        }, InvalidArgumentException::class, 'Invalid Argument: The given class does not exist');
    }
}

(new ReflectorTest())->run();
