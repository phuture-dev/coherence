<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use DateTime;
use stdClass;
use ReflectionEnum;
use ReflectionClass;
use ReflectionMethod;
use DateTimeImmutable;
use ReflectionFunction;
use ReflectionProperty;
use ReflectionParameter;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Reflector;
use Phuture\Coherence\Exception\{InvalidArgumentException, ReflectionException};

require __DIR__ . '/bootstrap.php';

trait ReflectorTestTrait
{
    public function traitMethod(): string
    {
        return 'trait';
    }
}

enum ReflectorTestStatus
{
    case Active;
    case Inactive;
}

class ReflectorTestClass
{
    private string $privateProp = 'private';
    protected string $protectedProp = 'protected';
    public string $publicProp = 'public';

    public function publicMethod(): void
    {
    }

    protected function protectedMethod(): void
    {
    }

    private function privateMethod(): void
    {
    }
}

class ReflectorTestChildClass extends ReflectorTestClass
{
    use ReflectorTestTrait;
}

class ReflectorTestInvokable
{
    public function __invoke(string $a, int $b, bool $c = true): void
    {
    }
}

class ReflectorTestNoParent
{
}

class ReflectorTestStaticMethod
{
    public static function compute(int $a, int $b): int
    {
        return $a + $b;
    }
}

class ReflectorTest extends TestCase
{
    public function testAliasAlreadyExists(): void
    {
        Assert::exception(function () {
            Reflector::alias(stdClass::class, 'stdClass');
        }, InvalidArgumentException::class, 'Invalid Argument: The given alias already exists');
    }

    public function testAliasClassDoesNotExist(): void
    {
        Assert::exception(function () {
            Reflector::alias('NonExistentClass', 'DTAlias');
        }, InvalidArgumentException::class, 'Invalid Argument: The given class does not exist');
    }

    public function testAliasFromInstance(): void
    {
        $result = Reflector::alias(new DateTime(), 'DTAlias');
        Assert::true($result);
        Assert::true(class_exists('DTAlias'));
    }

    public function testAliasFromString(): void
    {
        $result = Reflector::alias(DateTime::class, 'ReflectorTestAlias');
        Assert::true($result);
        Assert::true(class_exists('ReflectorTestAlias'));
    }

    public function testAliasFunctionAlreadyExists(): void
    {
        Assert::exception(function () {
            Reflector::aliasFunction('strlen', 'strlen');
        }, InvalidArgumentException::class, 'Invalid Argument: The given alias already exists');
    }

    public function testAliasFunctionCreatesWorkingAlias(): void
    {
        $result = Reflector::aliasFunction('strlen', 'str_length_alias');
        Assert::true($result);
        Assert::true(function_exists('str_length_alias'));
        Assert::same(5, str_length_alias('hello'));
    }

    public function testAliasFunctionDoesNotExist(): void
    {
        Assert::exception(function () {
            Reflector::aliasFunction('non_existent_function_xyz', 'str_length_alias');
        }, InvalidArgumentException::class, 'Invalid Argument: The given function does not exist');
    }

    public function testArityClosureVsFunctionConsistency(): void
    {
        $closureArity = Reflector::arity(fn ($a) => $a);
        $functionArity = Reflector::arity('strlen');

        Assert::same(1, $closureArity);
        Assert::same(1, $functionArity);
    }

    public function testArityWithArrayMethod(): void
    {
        Assert::same(1, Reflector::arity([new DateTime(), 'format']));
    }

    public function testArityWithClosure(): void
    {
        Assert::same(2, Reflector::arity(fn ($a, $b) => $a + $b));
    }

    public function testArityWithClosureIncludingOptional(): void
    {
        Assert::same(3, Reflector::arity(fn ($a, $b, $c = null) => $a + $b));
    }

    public function testArityWithFunctionString(): void
    {
        Assert::same(1, Reflector::arity('strlen'));
    }

    public function testArityWithInvokableObject(): void
    {
        Assert::same(3, Reflector::arity(new ReflectorTestInvokable()));
    }

    public function testArityWithNoParameters(): void
    {
        Assert::same(0, Reflector::arity(fn () => null));
    }

    public function testBasenameAndNamespaceCombineToFullName(): void
    {
        $basename = Reflector::basename(Reflector::class);
        $namespace = Reflector::namespace(Reflector::class);

        Assert::same('Reflector', $basename);
        Assert::same('Phuture\Coherence', $namespace);
    }

    public function testBasenameAnonymousThrows(): void
    {
        $anonymous = new class () {};

        Assert::exception(function () use ($anonymous) {
            Reflector::basename($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testBasenameFromInstance(): void
    {
        Assert::same('DateTime', Reflector::basename(new DateTime()));
    }

    public function testBasenameFromString(): void
    {
        Assert::same('DateTimeImmutable', Reflector::basename(DateTimeImmutable::class));
    }

    public function testBasenameOfTestClass(): void
    {
        Assert::same('ReflectorTestClass', Reflector::basename(ReflectorTestClass::class));
    }

    public function testBasenameVsNameConsistency(): void
    {
        $instance = new DateTimeImmutable();
        Assert::same('DateTimeImmutable', Reflector::basename($instance));
        Assert::same('DateTimeImmutable', Reflector::name($instance));
    }

    public function testBasenameWithNamespace(): void
    {
        Assert::same('Reflector', Reflector::basename(Reflector::class));
    }

    public function testHasMethodAnonymousThrows(): void
    {
        $anonymous = new class () {
            public function test(): void
            {
            }
        };

        Assert::exception(function () use ($anonymous) {
            Reflector::hasMethod($anonymous, 'test');
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testHasMethodExistingMethod(): void
    {
        Assert::true(Reflector::hasMethod(DateTime::class, 'format'));
    }

    public function testHasMethodFromInstance(): void
    {
        Assert::true(Reflector::hasMethod(new DateTime(), 'getTimestamp'));
    }

    public function testHasMethodInheritedMethods(): void
    {
        Assert::true(Reflector::hasMethod(DateTimeImmutable::class, 'format'));
        Assert::true(Reflector::hasMethod(ReflectorTestChildClass::class, 'publicMethod'));
    }

    public function testHasMethodNonExistentMethod(): void
    {
        Assert::false(Reflector::hasMethod(DateTime::class, 'nonExistentMethod'));
    }

    public function testHasMethodOnTestClass(): void
    {
        Assert::true(Reflector::hasMethod(ReflectorTestClass::class, 'publicMethod'));
        Assert::false(Reflector::hasMethod(ReflectorTestClass::class, 'nonExistentMethod'));
    }

    public function testHasPropertyAnonymousThrows(): void
    {
        $anonymous = new class () {
            public string $name = '';
        };

        Assert::exception(function () use ($anonymous) {
            Reflector::hasProperty($anonymous, 'name');
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testHasPropertyExistingProperty(): void
    {
        Assert::true(Reflector::hasProperty(ReflectorTestClass::class, 'publicProp'));
        Assert::true(Reflector::hasProperty(ReflectorTestClass::class, 'protectedProp'));
        Assert::true(Reflector::hasProperty(ReflectorTestClass::class, 'privateProp'));
    }

    public function testHasPropertyFromInstance(): void
    {
        Assert::true(Reflector::hasProperty(new ReflectorTestClass(), 'publicProp'));
    }

    public function testHasPropertyInheritedProperties(): void
    {
        Assert::true(Reflector::hasProperty(ReflectorTestChildClass::class, 'publicProp'));
    }

    public function testHasPropertyNonExistentProperty(): void
    {
        Assert::false(Reflector::hasProperty(stdClass::class, 'nonExistentProperty'));
    }

    public function testIsClassWithExistingClass(): void
    {
        Assert::true(Reflector::isClass(DateTime::class));
    }

    public function testIsClassWithNonExistentClass(): void
    {
        Assert::false(Reflector::isClass('NonExistentClass'));
    }

    public function testIsClassWithStdClass(): void
    {
        Assert::true(Reflector::isClass(stdClass::class));
    }

    public function testIsMethodPrivate(): void
    {
        Assert::true(Reflector::isMethodPrivate(ReflectorTestClass::class, 'privateMethod'));
    }

    public function testIsMethodPrivateReturnsFalseForPublic(): void
    {
        Assert::false(Reflector::isMethodPrivate(ReflectorTestClass::class, 'publicMethod'));
    }

    public function testIsMethodProtected(): void
    {
        Assert::true(Reflector::isMethodProtected(ReflectorTestClass::class, 'protectedMethod'));
    }

    public function testIsMethodProtectedReturnsFalseForPublic(): void
    {
        Assert::false(Reflector::isMethodProtected(ReflectorTestClass::class, 'publicMethod'));
    }

    public function testIsMethodPublic(): void
    {
        Assert::true(Reflector::isMethodPublic(ReflectorTestClass::class, 'publicMethod'));
    }

    public function testIsMethodPublicReturnsFalseForProtected(): void
    {
        Assert::false(Reflector::isMethodPublic(ReflectorTestClass::class, 'protectedMethod'));
    }

    public function testIsPropertyPrivate(): void
    {
        Assert::true(Reflector::isPropertyPrivate(ReflectorTestClass::class, 'privateProp'));
    }

    public function testIsPropertyPrivateReturnsFalseForPublic(): void
    {
        Assert::false(Reflector::isPropertyPrivate(ReflectorTestClass::class, 'publicProp'));
    }

    public function testIsPropertyProtected(): void
    {
        Assert::true(Reflector::isPropertyProtected(ReflectorTestClass::class, 'protectedProp'));
    }

    public function testIsPropertyProtectedReturnsFalseForPublic(): void
    {
        Assert::false(Reflector::isPropertyProtected(ReflectorTestClass::class, 'publicProp'));
    }

    public function testIsPropertyPublic(): void
    {
        Assert::true(Reflector::isPropertyPublic(ReflectorTestClass::class, 'publicProp'));
    }

    public function testIsPropertyPublicReturnsFalseForPrivate(): void
    {
        Assert::false(Reflector::isPropertyPublic(ReflectorTestClass::class, 'privateProp'));
    }

    public function testMethodsAnonymousThrows(): void
    {
        $anonymous = new class () {};

        Assert::exception(function () use ($anonymous) {
            Reflector::methods($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testMethodsFromInstance(): void
    {
        $methods = Reflector::methods(new DateTime());
        Assert::type('array', $methods);
        Assert::true(count($methods) > 0);
    }

    public function testMethodsInheritedMethods(): void
    {
        $methods = Reflector::methods(ReflectorTestChildClass::class);
        Assert::true(in_array('publicMethod', $methods));
        Assert::true(in_array('traitMethod', $methods));
    }

    public function testMethodsOnTestClass(): void
    {
        $methods = Reflector::methods(ReflectorTestClass::class);
        Assert::true(in_array('publicMethod', $methods));
    }

    public function testMethodsReturnsArray(): void
    {
        $methods = Reflector::methods(DateTime::class);
        Assert::type('array', $methods);
        Assert::true(in_array('format', $methods));
        Assert::true(in_array('getTimestamp', $methods));
    }

    public function testMethodVisibilityAnonymousThrows(): void
    {
        $anonymous = new class () {
            public function test(): void
            {
            }
        };

        Assert::exception(function () use ($anonymous) {
            Reflector::methodVisibility($anonymous, 'test');
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testMethodVisibilityBooleanConsistency(): void
    {
        $className = ReflectorTestClass::class;

        Assert::true(Reflector::isMethodPublic($className, 'publicMethod'));
        Assert::false(Reflector::isMethodProtected($className, 'publicMethod'));
        Assert::false(Reflector::isMethodPrivate($className, 'publicMethod'));

        Assert::false(Reflector::isMethodPublic($className, 'protectedMethod'));
        Assert::true(Reflector::isMethodProtected($className, 'protectedMethod'));
        Assert::false(Reflector::isMethodPrivate($className, 'protectedMethod'));

        Assert::false(Reflector::isMethodPublic($className, 'privateMethod'));
        Assert::false(Reflector::isMethodProtected($className, 'privateMethod'));
        Assert::true(Reflector::isMethodPrivate($className, 'privateMethod'));
    }

    public function testMethodVisibilityPrivate(): void
    {
        Assert::same('private', Reflector::methodVisibility(ReflectorTestClass::class, 'privateMethod'));
    }

    public function testMethodVisibilityProtected(): void
    {
        Assert::same('protected', Reflector::methodVisibility(ReflectorTestClass::class, 'protectedMethod'));
    }

    public function testMethodVisibilityPublic(): void
    {
        Assert::same('public', Reflector::methodVisibility(ReflectorTestClass::class, 'publicMethod'));
    }

    public function testNameAnonymousThrows(): void
    {
        $anonymous = new class () {};

        Assert::exception(function () use ($anonymous) {
            Reflector::name($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testNameReturnsClassName(): void
    {
        Assert::same('DateTime', Reflector::name(new DateTime()));
    }

    public function testNamespaceAnonymousThrows(): void
    {
        $anonymous = new class () {};

        Assert::exception(function () use ($anonymous) {
            Reflector::namespace($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testNamespaceFromInstance(): void
    {
        Assert::same('Phuture\Coherence\Tests', Reflector::namespace(new ReflectorTestClass()));
    }

    public function testNamespaceGlobalClassReturnsEmptyString(): void
    {
        Assert::same('', Reflector::namespace(DateTime::class));
    }

    public function testNamespaceReturnsNamespace(): void
    {
        Assert::same('Phuture\Coherence', Reflector::namespace(Reflector::class));
    }

    public function testNameWithNamespacedClass(): void
    {
        Assert::same(ReflectorTestClass::class, Reflector::name(new ReflectorTestClass()));
    }

    public function testParametersWithArrayMethod(): void
    {
        $params = Reflector::parameters([new DateTime(), 'format']);
        Assert::same(['format'], $params);
    }

    public function testParametersWithClosure(): void
    {
        $params = Reflector::parameters(fn ($name, $age, $active = true) => null);
        Assert::same(['name', 'age', 'active'], $params);
    }

    public function testParametersWithFunctionString(): void
    {
        $params = Reflector::parameters('strlen');
        Assert::same(['string'], $params);
    }

    public function testParametersWithInvokableObject(): void
    {
        $params = Reflector::parameters(new ReflectorTestInvokable());
        Assert::same(['a', 'b', 'c'], $params);
    }

    public function testParentAnonymousThrows(): void
    {
        $anonymous = new class () {};

        Assert::exception(function () use ($anonymous) {
            Reflector::parent($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testParentClassWithNoParentThrows(): void
    {
        Assert::exception(function () {
            Reflector::parent(ReflectorTestNoParent::class);
        }, InvalidArgumentException::class);
    }

    public function testParentFromInstance(): void
    {
        Assert::same(ReflectorTestClass::class, Reflector::parent(new ReflectorTestChildClass()));
    }

    public function testParentOfChildClass(): void
    {
        Assert::same(ReflectorTestClass::class, Reflector::parent(ReflectorTestChildClass::class));
    }

    public function testPropertiesAnonymousThrows(): void
    {
        $anonymous = new class () {
            public string $name = '';
        };

        Assert::exception(function () use ($anonymous) {
            Reflector::properties($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testPropertiesFromInstance(): void
    {
        $props = Reflector::properties(new ReflectorTestClass());
        Assert::true(array_key_exists('publicProp', $props));
        Assert::same('public', $props['publicProp']);
    }

    public function testPropertiesReturnsArray(): void
    {
        $props = Reflector::properties(ReflectorTestClass::class);
        Assert::type('array', $props);
        Assert::true(array_key_exists('publicProp', $props));
        Assert::false(array_key_exists('privateProp', $props));
    }

    public function testPropertyVisibilityAnonymousThrows(): void
    {
        $anonymous = new class () {
            public string $name = '';
        };

        Assert::exception(function () use ($anonymous) {
            Reflector::propertyVisibility($anonymous, 'name');
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testPropertyVisibilityBooleanConsistency(): void
    {
        $className = ReflectorTestClass::class;

        Assert::true(Reflector::isPropertyPublic($className, 'publicProp'));
        Assert::false(Reflector::isPropertyProtected($className, 'publicProp'));
        Assert::false(Reflector::isPropertyPrivate($className, 'publicProp'));

        Assert::false(Reflector::isPropertyPublic($className, 'protectedProp'));
        Assert::true(Reflector::isPropertyProtected($className, 'protectedProp'));
        Assert::false(Reflector::isPropertyPrivate($className, 'protectedProp'));

        Assert::false(Reflector::isPropertyPublic($className, 'privateProp'));
        Assert::false(Reflector::isPropertyProtected($className, 'privateProp'));
        Assert::true(Reflector::isPropertyPrivate($className, 'privateProp'));
    }

    public function testPropertyVisibilityPrivate(): void
    {
        Assert::same('private', Reflector::propertyVisibility(ReflectorTestClass::class, 'privateProp'));
    }

    public function testPropertyVisibilityProtected(): void
    {
        Assert::same('protected', Reflector::propertyVisibility(ReflectorTestClass::class, 'protectedProp'));
    }

    public function testPropertyVisibilityPublic(): void
    {
        Assert::same('public', Reflector::propertyVisibility(ReflectorTestClass::class, 'publicProp'));
    }

    public function testReflectClassFromInstance(): void
    {
        $reflection = Reflector::reflectClass(new DateTime());
        Assert::type(ReflectionClass::class, $reflection);
    }

    public function testReflectClassNonExistentThrows(): void
    {
        Assert::exception(function () {
            Reflector::reflectClass('NonExistentClass');
        }, ReflectionException::class);
    }

    public function testReflectClassProvidesWorkingReflection(): void
    {
        $reflection = Reflector::reflectClass(DateTime::class);
        Assert::true($reflection->hasMethod('format'));
        Assert::true($reflection->isInstantiable());
    }

    public function testReflectClassReturnsReflectionClass(): void
    {
        $reflection = Reflector::reflectClass(DateTime::class);
        Assert::type(ReflectionClass::class, $reflection);
        Assert::same('DateTime', $reflection->getShortName());
    }

    public function testReflectEnumInstance(): void
    {
        $reflection = Reflector::reflectEnum(ReflectorTestStatus::class);
        $cases = $reflection->getCases();
        Assert::count(2, $cases);
    }

    public function testReflectEnumNonEnumThrows(): void
    {
        Assert::exception(function () {
            Reflector::reflectEnum(DateTime::class);
        }, ReflectionException::class);
    }

    public function testReflectEnumReturnsReflectionEnum(): void
    {
        $reflection = Reflector::reflectEnum(ReflectorTestStatus::class);
        Assert::type(ReflectionEnum::class, $reflection);
    }

    public function testReflectFunctionReturnsReflectionFunction(): void
    {
        $reflection = Reflector::reflectFunction('strlen');
        Assert::type(ReflectionFunction::class, $reflection);
        Assert::same('strlen', $reflection->getName());
    }

    public function testReflectFunctionWithClosure(): void
    {
        $reflection = Reflector::reflectFunction(fn ($a, $b) => $a + $b);
        Assert::type(ReflectionFunction::class, $reflection);
        Assert::same(2, $reflection->getNumberOfParameters());
    }

    public function testReflectMethodFromInstance(): void
    {
        $reflection = Reflector::reflectMethod(new DateTime(), 'format');
        Assert::type(ReflectionMethod::class, $reflection);
    }

    public function testReflectMethodNonExistentThrows(): void
    {
        Assert::exception(function () {
            Reflector::reflectMethod(DateTime::class, 'nonExistentMethod');
        }, ReflectionException::class);
    }

    public function testReflectMethodProvidesWorkingReflection(): void
    {
        $reflection = Reflector::reflectMethod(DateTime::class, 'format');
        Assert::true($reflection->isPublic());
        Assert::same(1, $reflection->getNumberOfParameters());
    }

    public function testReflectMethodReturnsReflectionMethod(): void
    {
        $reflection = Reflector::reflectMethod(DateTime::class, 'format');
        Assert::type(ReflectionMethod::class, $reflection);
        Assert::same('format', $reflection->getName());
    }

    public function testReflectParameterNonExistentThrows(): void
    {
        Assert::exception(function () {
            Reflector::reflectParameter(DateTime::class, 'format', 'nonExistentParam');
        }, ReflectionException::class);
    }

    public function testReflectParameterReturnsReflectionParameter(): void
    {
        $reflection = Reflector::reflectParameter(DateTime::class, 'format', 'format');
        Assert::type(ReflectionParameter::class, $reflection);
        Assert::same('format', $reflection->getName());
    }

    public function testReflectPropertyNonExistentThrows(): void
    {
        Assert::exception(function () {
            Reflector::reflectProperty(stdClass::class, 'nonExistentProperty');
        }, ReflectionException::class);
    }

    public function testReflectPropertyReturnsReflectionProperty(): void
    {
        $reflection = Reflector::reflectProperty(ReflectorTestClass::class, 'publicProp');
        Assert::type(ReflectionProperty::class, $reflection);
        Assert::same('publicProp', $reflection->getName());
    }

    public function testTraitsAnonymousThrows(): void
    {
        $anonymous = new class () {};

        Assert::exception(function () use ($anonymous) {
            Reflector::traits($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testTraitsFromClassUsingTrait(): void
    {
        $traits = Reflector::traits(ReflectorTestChildClass::class);
        Assert::true(in_array(ReflectorTestTrait::class, $traits));
    }

    public function testTraitsReturnsArray(): void
    {
        $traits = Reflector::traits(Reflector::class);
        Assert::type('array', $traits);
    }
}

(new ReflectorTest())->run();
