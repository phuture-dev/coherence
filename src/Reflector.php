<?php

namespace Phuture\Coherence;

use Closure;
use ReflectionEnum;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionProperty;
use ReflectionParameter;
use Phuture\Coherence\Support\StaticClass;
use Nette\PhpGenerator\{GlobalFunction, Literal};
use Phuture\Coherence\Exception\{InvalidArgumentException, ReflectionException};

class Reflector extends StaticClass
{
    public static function alias(object|string $class, string $alias): bool
    {
        $className = is_object($class) ? get_class($class) : $class;

        if (class_exists($alias)) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given alias already exists"
            );
        }

        if (!class_exists($className)) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class does not exist"
            );
        }

        return class_alias($className, $alias, true);
    }

    public static function aliasFunction(string $function, string $alias): bool
    {
        if (function_exists($alias)) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given alias already exists"
            );
        }

        if (!function_exists($function)) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given function does not exist"
            );
        }

        if (!function_exists($alias)) {
            $closure = (new GlobalFunction($alias))
                ->setBody(
                    (string) new Literal(
                        'return call_user_func_array(?, func_get_args());',
                        [$function]
                    )
                );
            eval($closure);

            return true;
        }

        return false;
    }

    public static function arity(callable $method): int
    {
        try {
            if ($method instanceof Closure) {
                $reflection = new ReflectionFunction($method);
            } elseif (is_array($method)) {
                $reflection = new ReflectionMethod($method[0], $method[1]);
            } elseif (is_string($method)) {
                $reflection = new ReflectionFunction($method);
            } else {
                $reflection = new ReflectionMethod($method, '__invoke');
            }

            return $reflection->getNumberOfParameters();
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }
    public static function basename(object|string $class): string
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return $reflection->getShortName();
    }

    public static function hasMethod(object|string $class, string $method): bool
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return method_exists($class, $method);
    }

    public static function hasProperty(object|string $class, string $property): bool
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return property_exists($class, $property);
    }

    public static function isClass(string $class): bool
    {
        try {
            new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            return false;
        }

        return class_exists($class);
    }

    public static function isMethodPrivate(object|string $class, string $method): bool
    {
        return self::methodVisibility($class, $method) === 'private';
    }

    public static function isMethodProtected(object|string $class, string $method): bool
    {
        return self::methodVisibility($class, $method) === 'protected';
    }

    public static function isMethodPublic(object|string $class, string $method): bool
    {
        return self::methodVisibility($class, $method) === 'public';
    }

    public static function isPropertyPrivate(object|string $class, string $property): bool
    {
        return self::propertyVisibility($class, $property) === 'private';
    }

    public static function isPropertyProtected(object|string $class, string $property): bool
    {
        return self::propertyVisibility($class, $property) === 'protected';
    }

    public static function isPropertyPublic(object|string $class, string $property): bool
    {
        return self::propertyVisibility($class, $property) === 'public';
    }

    public static function methods(object|string $class): array
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return get_class_methods($class);
    }

    public static function methodVisibility(object|string $class, string $method): string
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        try {
            return match (true) {
                (new ReflectionMethod($class, $method))->isPublic() => 'public',
                (new ReflectionMethod($class, $method))->isPrivate() => 'private',
                (new ReflectionMethod($class, $method))->isProtected() => 'protected'
            };
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }
    public static function name(object $class): string
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return get_class($class);
    }

    public static function namespace(object|string $class): string
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return $reflection->getNamespaceName();
    }

    public static function parameters(callable $method): array
    {
        try {
            if ($method instanceof Closure) {
                $reflection = new ReflectionFunction($method);
            } elseif (is_array($method)) {
                $reflection = new ReflectionMethod($method[0], $method[1]);
            } elseif (is_string($method)) {
                $reflection = new ReflectionFunction($method);
            } else {
                $reflection = new ReflectionMethod($method, '__invoke');
            }

            return array_map(
                fn ($param) => $param->getName(),
                $reflection->getParameters()
            );
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    public static function parent(object|string $class): string
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        $parent = get_parent_class($class);

        if ($parent === false) {
            throw new InvalidArgumentException(
                "Invalid Argument: {$class} does not have a parent or is not a valid class"
            );
        }

        return $parent;
    }

    public static function properties(object|string $class): array
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return is_string($class) ? get_class_vars($class) : get_class_vars(get_class($class));
    }

    public static function propertyVisibility(object|string $class, string $property): string
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        try {
            return match (true) {
                (new ReflectionProperty($class, $property))->isPublic() => 'public',
                (new ReflectionProperty($class, $property))->isPrivate() => 'private',
                (new ReflectionProperty($class, $property))->isProtected() => 'protected'
            };
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    public static function reflectClass(object|string $class): ReflectionClass
    {
        try {
            return new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    public static function reflectEnum(object|string $enum): ReflectionEnum
    {
        try {
            return new ReflectionEnum($enum);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    public static function reflectFunction(callable $function): ReflectionFunction
    {
        try {
            return new ReflectionFunction($function);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    public static function reflectMethod(object|string $class, string $method): ReflectionMethod
    {
        try {
            return new ReflectionMethod($class, $method);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    public static function reflectParameter(
        object|string $class,
        string $method,
        string $parameter
    ): ReflectionParameter {
        try {
            return new ReflectionParameter([$class, $method], $parameter);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    public static function reflectProperty(object|string $class, string $property): ReflectionProperty
    {
        try {
            return new ReflectionProperty($class, $property);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    public static function traits(object|string $class): array
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return class_uses($class);
    }
}
