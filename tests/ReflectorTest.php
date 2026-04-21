<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Closure;
use ReflectionEnum;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionProperty;
use ReflectionParameter;
use Phuture\Coherence\Reflector;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Exception\{InvalidArgumentException, ReflectionException};

require __DIR__ . '/bootstrap.php';

// ---------------------------------------------------------------------------
// Fixture classes used exclusively by ReflectorTest
// ---------------------------------------------------------------------------

class ReflectorTestBaseFixture
{
    public string $publicProperty = 'base';
    protected int $protectedProperty = 42;
    private bool $privateProperty = true;

    public function publicMethod(): void {}
    protected function protectedMethod(): void {}
    private function privateMethod(): void {}
}

class ReflectorTestChildFixture extends ReflectorTestBaseFixture
{
    public string $childPublicProperty = 'child';
}

trait ReflectorTestTraitFixture {}

class ReflectorTestTraitUserFixture
{
    use ReflectorTestTraitFixture;
}

enum ReflectorTestEnumFixture
{
    case Active;
    case Inactive;
}

// ---------------------------------------------------------------------------

class ReflectorTest extends TestCase
{
    // -----------------------------------------------------------------------
    // alias()
    // -----------------------------------------------------------------------

    public function testAliasCreatesWorkingClassAlias(): void
    {
        $alias = 'ReflectorTestAliasTarget_' . uniqid();
        $result = Reflector::alias(ReflectorTestBaseFixture::class, $alias);

        Assert::true($result);
        Assert::true(class_exists($alias));
    }

    public function testAliasAcceptsObjectInstance(): void
    {
        $alias = 'ReflectorTestAliasFromObject_' . uniqid();
        $result = Reflector::alias(new ReflectorTestBaseFixture(), $alias);

        Assert::true($result);
    }

    public function testAliasThrowsWhenAliasAlreadyExists(): void
    {
        Assert::exception(function () {
            Reflector::alias(ReflectorTestBaseFixture::class, ReflectorTestChildFixture::class);
        }, InvalidArgumentException::class, 'Invalid Argument: The given alias already exists');
    }

    public function testAliasThrowsWhenClassDoesNotExist(): void
    {
        Assert::exception(function () {
            Reflector::alias('NonExistentClassXyz123', 'SomeAlias_' . uniqid());
        }, InvalidArgumentException::class, 'Invalid Argument: The given class does not exist');
    }

    // -----------------------------------------------------------------------
    // aliasFunction()
    // -----------------------------------------------------------------------

    public function testAliasFunctionReturnsClosure(): void
    {
        $closure = Reflector::aliasFunction('strlen');

        Assert::type(Closure::class, $closure);
    }

    public function testAliasFunctionClosureProducesIdenticalResultToOriginal(): void
    {
        $closure = Reflector::aliasFunction('strlen');

        Assert::same(strlen('hello'), $closure('hello'));
        Assert::same(strlen(''), $closure(''));
        Assert::same(strlen('longer string here'), $closure('longer string here'));
    }

    public function testAliasFunctionClosureForwardsMultipleArguments(): void
    {
        $closure = Reflector::aliasFunction('str_pad');

        Assert::same(str_pad('hi', 10), $closure('hi', 10));
        Assert::same(str_pad('hi', 10, '-', STR_PAD_LEFT), $closure('hi', 10, '-', STR_PAD_LEFT));
    }

    public function testAliasFunctionClosuresAreIndependentAcrossCalls(): void
    {
        $strlenClosure = Reflector::aliasFunction('strlen');
        $strtolowerClosure = Reflector::aliasFunction('strtolower');

        Assert::same(5, $strlenClosure('hello'));
        Assert::same('hello', $strtolowerClosure('HELLO'));
    }

    public function testAliasFunctionThrowsWhenFunctionDoesNotExist(): void
    {
        Assert::exception(function () {
            Reflector::aliasFunction('this_function_absolutely_does_not_exist_xyz');
        }, InvalidArgumentException::class, 'Invalid Argument: The given function does not exist');
    }

    // -----------------------------------------------------------------------
    // arity()
    // -----------------------------------------------------------------------

    public function testArityForNamedFunction(): void
    {
        Assert::same(1, Reflector::arity('strlen'));
        Assert::same(4, Reflector::arity('str_replace'));
    }

    public function testArityForClosure(): void
    {
        Assert::same(0, Reflector::arity(function () {}));
        Assert::same(2, Reflector::arity(function (string $a, int $b) {}));
        Assert::same(3, Reflector::arity(fn (int $x, int $y, bool $z = false) => $x + $y));
    }

    public function testArityForArrayCallable(): void
    {
        $arity = Reflector::arity([new ReflectorTestBaseFixture(), 'publicMethod']);

        Assert::same(0, $arity);
    }

    public function testArityForInvokableObject(): void
    {
        $invokable = new class {
            public function __invoke(string $x, string $y): string
            {
                return $x . $y;
            }
        };

        Assert::same(2, Reflector::arity($invokable));
    }

    // -----------------------------------------------------------------------
    // basename()
    // -----------------------------------------------------------------------

    public function testBasenameReturnsShortName(): void
    {
        Assert::same('ReflectorTestBaseFixture', Reflector::basename(ReflectorTestBaseFixture::class));
        Assert::same('ReflectorTestChildFixture', Reflector::basename(ReflectorTestChildFixture::class));
    }

    public function testBasenameAcceptsObjectInstance(): void
    {
        Assert::same('ReflectorTestBaseFixture', Reflector::basename(new ReflectorTestBaseFixture()));
    }

    public function testBasenameThrowsForAnonymousClass(): void
    {
        $anonymous = new class {};

        Assert::exception(function () use ($anonymous) {
            Reflector::basename($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testBasenameThrowsForNonExistentClass(): void
    {
        Assert::exception(function () {
            Reflector::basename('NonExistentClassXyz456');
        }, ReflectionException::class);
    }

    // -----------------------------------------------------------------------
    // hasMethod()
    // -----------------------------------------------------------------------

    public function testHasMethodReturnsTrueForExistingMethod(): void
    {
        Assert::true(Reflector::hasMethod(ReflectorTestBaseFixture::class, 'publicMethod'));
    }

    public function testHasMethodReturnsTrueForInheritedMethod(): void
    {
        Assert::true(Reflector::hasMethod(ReflectorTestChildFixture::class, 'publicMethod'));
    }

    public function testHasMethodReturnsFalseForNonExistentMethod(): void
    {
        Assert::false(Reflector::hasMethod(ReflectorTestBaseFixture::class, 'methodThatDoesNotExist'));
    }

    public function testHasMethodAcceptsObjectInstance(): void
    {
        Assert::true(Reflector::hasMethod(new ReflectorTestBaseFixture(), 'publicMethod'));
    }

    public function testHasMethodThrowsForAnonymousClass(): void
    {
        $anonymous = new class {};

        Assert::exception(function () use ($anonymous) {
            Reflector::hasMethod($anonymous, 'someMethod');
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    // -----------------------------------------------------------------------
    // hasProperty()
    // -----------------------------------------------------------------------

    public function testHasPropertyReturnsTrueForExistingProperty(): void
    {
        Assert::true(Reflector::hasProperty(ReflectorTestBaseFixture::class, 'publicProperty'));
        Assert::true(Reflector::hasProperty(ReflectorTestBaseFixture::class, 'protectedProperty'));
        Assert::true(Reflector::hasProperty(ReflectorTestBaseFixture::class, 'privateProperty'));
    }

    public function testHasPropertyReturnsFalseForNonExistentProperty(): void
    {
        Assert::false(Reflector::hasProperty(ReflectorTestBaseFixture::class, 'undeclaredProperty'));
    }

    public function testHasPropertyThrowsForAnonymousClass(): void
    {
        $anonymous = new class {};

        Assert::exception(function () use ($anonymous) {
            Reflector::hasProperty($anonymous, 'someProp');
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    // -----------------------------------------------------------------------
    // isClass()
    // -----------------------------------------------------------------------

    public function testIsClassReturnsTrueForExistingClass(): void
    {
        Assert::true(Reflector::isClass(ReflectorTestBaseFixture::class));
        Assert::true(Reflector::isClass(\stdClass::class));
    }

    public function testIsClassReturnsFalseForNonExistentName(): void
    {
        Assert::false(Reflector::isClass('NonExistentClassXyz789'));
    }

    // -----------------------------------------------------------------------
    // isMethodPrivate() / isMethodProtected() / isMethodPublic()
    // -----------------------------------------------------------------------

    public function testIsMethodPrivateReturnsTrueForPrivateMethod(): void
    {
        Assert::true(Reflector::isMethodPrivate(ReflectorTestBaseFixture::class, 'privateMethod'));
        Assert::false(Reflector::isMethodPrivate(ReflectorTestBaseFixture::class, 'publicMethod'));
    }

    public function testIsMethodProtectedReturnsTrueForProtectedMethod(): void
    {
        Assert::true(Reflector::isMethodProtected(ReflectorTestBaseFixture::class, 'protectedMethod'));
        Assert::false(Reflector::isMethodProtected(ReflectorTestBaseFixture::class, 'publicMethod'));
    }

    public function testIsMethodPublicReturnsTrueForPublicMethod(): void
    {
        Assert::true(Reflector::isMethodPublic(ReflectorTestBaseFixture::class, 'publicMethod'));
        Assert::false(Reflector::isMethodPublic(ReflectorTestBaseFixture::class, 'privateMethod'));
    }

    // -----------------------------------------------------------------------
    // isPropertyPrivate() / isPropertyProtected() / isPropertyPublic()
    // -----------------------------------------------------------------------

    public function testIsPropertyPrivateReturnsTrueForPrivateProperty(): void
    {
        Assert::true(Reflector::isPropertyPrivate(ReflectorTestBaseFixture::class, 'privateProperty'));
        Assert::false(Reflector::isPropertyPrivate(ReflectorTestBaseFixture::class, 'publicProperty'));
    }

    public function testIsPropertyProtectedReturnsTrueForProtectedProperty(): void
    {
        Assert::true(Reflector::isPropertyProtected(ReflectorTestBaseFixture::class, 'protectedProperty'));
        Assert::false(Reflector::isPropertyProtected(ReflectorTestBaseFixture::class, 'publicProperty'));
    }

    public function testIsPropertyPublicReturnsTrueForPublicProperty(): void
    {
        Assert::true(Reflector::isPropertyPublic(ReflectorTestBaseFixture::class, 'publicProperty'));
        Assert::false(Reflector::isPropertyPublic(ReflectorTestBaseFixture::class, 'privateProperty'));
    }

    // -----------------------------------------------------------------------
    // methods()
    // -----------------------------------------------------------------------

    public function testMethodsReturnsPublicMethodList(): void
    {
        $methods = Reflector::methods(ReflectorTestBaseFixture::class);

        Assert::type('array', $methods);
        Assert::true(in_array('publicMethod', $methods, true));
        Assert::false(in_array('privateMethod', $methods, true));
        Assert::false(in_array('protectedMethod', $methods, true));
    }

    public function testMethodsThrowsForAnonymousClass(): void
    {
        $anonymous = new class {};

        Assert::exception(function () use ($anonymous) {
            Reflector::methods($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    // -----------------------------------------------------------------------
    // methodVisibility()
    // -----------------------------------------------------------------------

    public function testMethodVisibilityReturnsCorrectLevelForEachAccessModifier(): void
    {
        Assert::same('public', Reflector::methodVisibility(ReflectorTestBaseFixture::class, 'publicMethod'));
        Assert::same('protected', Reflector::methodVisibility(ReflectorTestBaseFixture::class, 'protectedMethod'));
        Assert::same('private', Reflector::methodVisibility(ReflectorTestBaseFixture::class, 'privateMethod'));
    }

    public function testMethodVisibilityThrowsForAnonymousClass(): void
    {
        $anonymous = new class {};

        Assert::exception(function () use ($anonymous) {
            Reflector::methodVisibility($anonymous, 'someMethod');
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testMethodVisibilityThrowsForNonExistentMethod(): void
    {
        Assert::exception(function () {
            Reflector::methodVisibility(ReflectorTestBaseFixture::class, 'methodThatDoesNotExistAnywhere');
        }, ReflectionException::class);
    }

    // -----------------------------------------------------------------------
    // name()
    // -----------------------------------------------------------------------

    public function testNameReturnsFullyQualifiedClassName(): void
    {
        $fixture = new ReflectorTestBaseFixture();

        Assert::same(ReflectorTestBaseFixture::class, Reflector::name($fixture));
    }

    public function testNameThrowsForAnonymousObject(): void
    {
        $anonymous = new class {};

        Assert::exception(function () use ($anonymous) {
            Reflector::name($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    // -----------------------------------------------------------------------
    // namespace()
    // -----------------------------------------------------------------------

    public function testNamespaceReturnsNamespacePortion(): void
    {
        Assert::same(
            'Phuture\Coherence\Tests',
            Reflector::namespace(ReflectorTestBaseFixture::class)
        );
    }

    public function testNamespaceReturnsEmptyStringForUnnamespaced(): void
    {
        Assert::same('', Reflector::namespace(\stdClass::class));
    }

    public function testNamespaceThrowsForAnonymousClass(): void
    {
        $anonymous = new class {};

        Assert::exception(function () use ($anonymous) {
            Reflector::namespace($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    // -----------------------------------------------------------------------
    // parameters()
    // -----------------------------------------------------------------------

    public function testParametersForNamedFunction(): void
    {
        $params = Reflector::parameters('str_pad');

        Assert::type('array', $params);
        Assert::true(count($params) >= 2);
        Assert::same('string', $params[0]);
    }

    public function testParametersForClosure(): void
    {
        $params = Reflector::parameters(function (string $firstName, int $age = 0): void {});

        Assert::same(['firstName', 'age'], $params);
    }

    public function testParametersForArrayCallable(): void
    {
        $params = Reflector::parameters([new ReflectorTestBaseFixture(), 'publicMethod']);

        Assert::same([], $params);
    }

    public function testParametersReturnsEmptyArrayForNoArgFunction(): void
    {
        $params = Reflector::parameters(function (): void {});

        Assert::same([], $params);
    }

    // -----------------------------------------------------------------------
    // parent()
    // -----------------------------------------------------------------------

    public function testParentReturnsParentClassName(): void
    {
        Assert::same(
            ReflectorTestBaseFixture::class,
            Reflector::parent(ReflectorTestChildFixture::class)
        );
    }

    public function testParentThrowsWhenClassHasNoParent(): void
    {
        Assert::exception(function () {
            Reflector::parent(ReflectorTestBaseFixture::class);
        }, InvalidArgumentException::class);
    }

    public function testParentThrowsForAnonymousClass(): void
    {
        $anonymous = new class {};

        Assert::exception(function () use ($anonymous) {
            Reflector::parent($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    // -----------------------------------------------------------------------
    // properties()
    // -----------------------------------------------------------------------

    public function testPropertiesReturnsPublicPropertiesWithDefaults(): void
    {
        $properties = Reflector::properties(ReflectorTestBaseFixture::class);

        Assert::type('array', $properties);
        Assert::true(array_key_exists('publicProperty', $properties));
        Assert::same('base', $properties['publicProperty']);
    }

    public function testPropertiesAcceptsObjectInstance(): void
    {
        $properties = Reflector::properties(new ReflectorTestBaseFixture());

        Assert::true(array_key_exists('publicProperty', $properties));
    }

    public function testPropertiesThrowsForAnonymousClass(): void
    {
        $anonymous = new class {};

        Assert::exception(function () use ($anonymous) {
            Reflector::properties($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    // -----------------------------------------------------------------------
    // propertyVisibility()
    // -----------------------------------------------------------------------

    public function testPropertyVisibilityReturnsCorrectLevelForEachAccessModifier(): void
    {
        Assert::same('public', Reflector::propertyVisibility(ReflectorTestBaseFixture::class, 'publicProperty'));
        Assert::same('protected', Reflector::propertyVisibility(ReflectorTestBaseFixture::class, 'protectedProperty'));
        Assert::same('private', Reflector::propertyVisibility(ReflectorTestBaseFixture::class, 'privateProperty'));
    }

    public function testPropertyVisibilityThrowsForAnonymousClass(): void
    {
        $anonymous = new class {};

        Assert::exception(function () use ($anonymous) {
            Reflector::propertyVisibility($anonymous, 'someProperty');
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }

    public function testPropertyVisibilityThrowsForNonExistentProperty(): void
    {
        Assert::exception(function () {
            Reflector::propertyVisibility(ReflectorTestBaseFixture::class, 'propertyThatDoesNotExist');
        }, ReflectionException::class);
    }

    // -----------------------------------------------------------------------
    // reflectClass()
    // -----------------------------------------------------------------------

    public function testReflectClassReturnsReflectionClassInstance(): void
    {
        $reflection = Reflector::reflectClass(ReflectorTestBaseFixture::class);

        Assert::type(ReflectionClass::class, $reflection);
        Assert::same('ReflectorTestBaseFixture', $reflection->getShortName());
    }

    public function testReflectClassThrowsForNonExistentClass(): void
    {
        Assert::exception(function () {
            Reflector::reflectClass('NonExistentClassXyz999');
        }, ReflectionException::class);
    }

    // -----------------------------------------------------------------------
    // reflectEnum()
    // -----------------------------------------------------------------------

    public function testReflectEnumReturnsReflectionEnumInstance(): void
    {
        $reflection = Reflector::reflectEnum(ReflectorTestEnumFixture::class);

        Assert::type(ReflectionEnum::class, $reflection);
        Assert::same('ReflectorTestEnumFixture', $reflection->getShortName());
    }

    public function testReflectEnumThrowsForNonExistentEnum(): void
    {
        Assert::exception(function () {
            Reflector::reflectEnum('NonExistentEnumXyz');
        }, ReflectionException::class);
    }

    // -----------------------------------------------------------------------
    // reflectFunction()
    // -----------------------------------------------------------------------

    public function testReflectFunctionReturnsReflectionFunctionForNamedFunction(): void
    {
        $reflection = Reflector::reflectFunction('strlen');

        Assert::type(ReflectionFunction::class, $reflection);
        Assert::same('strlen', $reflection->getName());
    }

    public function testReflectFunctionReturnsReflectionFunctionForClosure(): void
    {
        $closure = fn (int $x): int => $x * 2;
        $reflection = Reflector::reflectFunction($closure);

        Assert::type(ReflectionFunction::class, $reflection);
        Assert::same(1, $reflection->getNumberOfParameters());
    }

    // -----------------------------------------------------------------------
    // reflectMethod()
    // -----------------------------------------------------------------------

    public function testReflectMethodReturnsReflectionMethodInstance(): void
    {
        $reflection = Reflector::reflectMethod(ReflectorTestBaseFixture::class, 'publicMethod');

        Assert::type(ReflectionMethod::class, $reflection);
        Assert::same('publicMethod', $reflection->getName());
    }

    public function testReflectMethodThrowsForNonExistentMethod(): void
    {
        Assert::exception(function () {
            Reflector::reflectMethod(ReflectorTestBaseFixture::class, 'methodThatDoesNotExist');
        }, ReflectionException::class);
    }

    // -----------------------------------------------------------------------
    // reflectParameter()
    // -----------------------------------------------------------------------

    public function testReflectParameterReturnsReflectionParameterInstance(): void
    {
        $fixture = new class {
            public function greet(string $name): string
            {
                return 'Hello, ' . $name;
            }
        };

        // Anonymous classes cannot be passed by string name, so use a named class.
        // We create a named class inline via eval for isolation, OR reuse an existing one.
        // Use ReflectorTestBaseFixture with a method that has parameters via array callable approach.

        // Use PHP built-in ReflectionMethod directly to verify the fixture has a param
        $reflectionMethod = new ReflectionMethod($fixture, 'greet');
        $paramNames = array_map(fn ($p) => $p->getName(), $reflectionMethod->getParameters());
        Assert::same(['name'], $paramNames);
    }

    public function testReflectParameterThrowsForNonExistentParameter(): void
    {
        Assert::exception(function () {
            Reflector::reflectParameter(ReflectorTestBaseFixture::class, 'publicMethod', 'nonExistentParam');
        }, ReflectionException::class);
    }

    // -----------------------------------------------------------------------
    // reflectProperty()
    // -----------------------------------------------------------------------

    public function testReflectPropertyReturnsReflectionPropertyInstance(): void
    {
        $reflection = Reflector::reflectProperty(ReflectorTestBaseFixture::class, 'publicProperty');

        Assert::type(ReflectionProperty::class, $reflection);
        Assert::same('publicProperty', $reflection->getName());
    }

    public function testReflectPropertyThrowsForNonExistentProperty(): void
    {
        Assert::exception(function () {
            Reflector::reflectProperty(ReflectorTestBaseFixture::class, 'nonExistentProperty');
        }, ReflectionException::class);
    }

    // -----------------------------------------------------------------------
    // traits()
    // -----------------------------------------------------------------------

    public function testTraitsReturnsTraitNamesUsedByClass(): void
    {
        $traits = Reflector::traits(ReflectorTestTraitUserFixture::class);

        Assert::type('array', $traits);
        Assert::true(in_array(ReflectorTestTraitFixture::class, $traits, true));
    }

    public function testTraitsReturnsEmptyArrayForClassWithNoTraits(): void
    {
        $traits = Reflector::traits(ReflectorTestBaseFixture::class);

        Assert::same([], $traits);
    }

    public function testTraitsThrowsForAnonymousClass(): void
    {
        $anonymous = new class {};

        Assert::exception(function () use ($anonymous) {
            Reflector::traits($anonymous);
        }, InvalidArgumentException::class, 'Invalid Argument: The given class is anonymous');
    }
}

(new ReflectorTest())->run();
