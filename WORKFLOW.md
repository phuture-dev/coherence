# Developer Workflow Guide

This document establishes the workflow, coding standards, and architectural patterns for this project.

## Table of Contents

1. [Project Overview](#project-overview)
2. [Architecture & Design Principles](#architecture--design-principles)
3. [Argument Ordering Pattern](#argument-ordering-pattern)
4. [Method Naming Conventions](#method-naming-conventions)
5. [Pass-by-Reference Methods](#pass-by-reference-methods)
6. [Documentation Standards](#documentation-standards)
7. [Class-Specific Patterns](#class-specific-patterns)
8. [Testing Requirements](#testing-requirements)
9. [Code Quality Standards](#code-quality-standards)
10. [Adding New Methods](#adding-new-methods)
11. [Adding New Utility Classes](#adding-new-utility-classes)
12. [Common Patterns & Examples](#common-patterns--examples)
13. [Anti-Patterns to Avoid](#anti-patterns-to-avoid)

---

## Project Overview

**Phuture Coherence** is a consistency-focused PHP utility library that provides standardized, predictable, and uniform
function wrappers and polyfills. The library enables cleaner APIs and more maintainable codebases without modifying native PHP behavior.

### Key Principles

- **Consistency**: All methods follow the same patterns and conventions
- **Predictability**: Method signatures and behavior are intuitive and uniform
- **Uniformity**: Naming conventions and documentation are standardized
- **Non-invasive**: Native PHP behavior is wrapped, not modified

---

## Architecture & Design Principles

### Static-Only Utility Classes

All utility classes in `./src` (excluding subdirectories) follow these rules:

1. **Static Methods Only**: All methods must be declared as `public static`
2. **No Instantiation**: Classes extend `StaticClass` to prevent instantiation
3. **No State**: Classes maintain no instance state
4. **Namespace**: All classes use the `Phuture\Coherence` namespace

### Class Location

- **Main classes location**: `./src/` (root level only)
- **Excluded**: Subdirectories like `./src/Class/`, `./src/Exception/`, etc.

---

## Argument Ordering Pattern

### CRITICAL: Standard Pattern

**All methods MUST follow this argument ordering:**

```
($data, [required], [optional], [variadics])
```

### Detailed Breakdown

#### 1. Primary Data Source (ALWAYS FIRST)

The data being operated on must always be the first parameter:

| Class            | Primary Parameter | Type     |
|------------------|-------------------|----------|
| Arrays           | `$array`          | `array`  |
| Strings          | `$string`         | `string` |
| MultibyteStrings | `$string`         | `string` |
| Hash             | `$data`           | `string` |
| Html             | `$string`         | `string` |
| Url              | `$string`         | `string` |

**Example:**
```php
Arrays::search(array $array, mixed $needle, bool $strict = false)
Strings::position(string $subject, string $search, int $offset = 0)
```

#### 2. Required Parameters

Search terms, needle values, patterns, or other required operation data:

```php
Arrays::search(array $array, mixed $needle, ...)
Strings::contains(string $subject, string $search, ...)
Hash::md5(string $data, ...)
```

#### 3. Optional Parameters

Flags, modes, offsets, encoding, and other optional modifiers:

```php
Arrays::search(array $array, mixed $needle, bool $strict = false)
Strings::position(string $subject, string $search, int $offset = 0)
MultibyteStrings::position(string $subject, string $search, int $offset = 0, ?string $encoding = null)
Hash::md5(string $data, bool $binary = false)
```

#### 4. Variadic Parameters (ALWAYS LAST)

Variadic parameters using the `...` operator must always be the last parameter:

```php
Arrays::merge(bool $recursive = false, ...$arrays)
Arrays::difference(array $array, ?callable $callback = null, array ...$arrays)
```

---

## Method Naming Conventions

### CamelCase Naming

All method names use camelCase:

| Native PHP Function  | Coherence Method | Class            |
|----------------------|------------------|------------------|
| `array_search()`     | `search()`       | Arrays           |
| `array_key_exists()` | `containsKey()`  | Arrays           |
| `str_contains()`     | `contains()`     | Strings          |
| `strpos()`           | `position()`     | Strings          |
| `mb_strpos()`        | `position()`     | MultibyteStrings |
| `htmlentities()`     | `entityEncode()` | Html             |
| `urlencode()`        | `encode()`       | Url              |
| `hash()`             | `make()`         | Hash             |

### Descriptive Names

- Use clear, descriptive names that convey the method's purpose
- Avoid abbreviations unless commonly understood
- Maintain consistency with related methods

**Examples:**
- `Arrays::sort()` - Simple sort
- `Arrays::sortKeys()` - Sort by keys
- `Arrays::sortAssoc()` - Sort maintaining index association
- `Strings::camelCase()` - Convert to camelCase
- `Strings::snakeCase()` - Convert to snake_case

---

## Pass-by-Reference Methods

### When to Use Pass-by-Reference

Methods that **modify the original data structure** must use pass-by-reference:

```php
public static function sort(array &$array, int $flags = SORT_REGULAR): bool
public static function push(array &$array, mixed ...$values): int
public static function shift(array &$array): mixed
```

### Documentation Requirements

Always clearly document pass-by-reference in PHPDoc:

```php
/**
 * Sorts an array in ascending order.
 *
 * Modifies the original array passed by reference.
 *
 * @param array &$array The array to sort (passed by reference)
 * @param int $flags Sorting type flags (default: SORT_REGULAR)
 * @return bool Returns true on success, false on failure
 */
public static function sort(array &$array, int $flags = SORT_REGULAR): bool
{
    return sort($array, $flags);
}
```

---

## Documentation Standards

### Required PHPDoc Format

Every method MUST include complete PHPDoc with the following structure:

````php
/**
 * Brief one-line description of what the method does.
 *
 * Extended description explaining what this method does in simple, clear language.
 * Avoid complex technical terms. If you must use a technical term, explain what it means.
 * Describe the behavior in a way that anyone can understand.
 *
 * Example:
 * ```php
 * use NameSpace\ClassName;
 * 
 * $result = ClassName::methodName($data, $param);
 * 
 * // Returns: [1, 2, 3]
 * ```
 *
 * @param type $param Clear description of what this parameter is and what it's used for
 * @param type $param Clear description (mention default values if applicable)
 * @return type Clear description of what gets returned and what it means
 */
````

### PHPDoc Requirements

1. **Brief Description**: One-line summary in simple language
2. **Extended Description**: Detailed explanation using everyday words (required)
3. **Example**: Always include a short usage example wrapped in ```php code blocks
4. **Other Classes**: When referring to other classes, always use a Fully Qualified Class Name
4. **@param Tags**: For EVERY parameter with clear, simple descriptions
5. **@return Tag**: Clear description of what is returned and what it represents, omit when void
6. **Callback Documentation**: When a parameter requires a callback function, the @param description MUST include the callback signature in the format: `The callback has the signature \`function (mixed $value): mixed\``
7. **Additional Tags** (when applicable):
   - `@see` - For methods that have other related methods, like first() being related to last(), or flatten() to unflatten()
   - `@throws` - For methods that throw exceptions
   - `@deprecated` - For deprecated methods

### Documentation Best Practices

- **Use simple, clear language** - Write as if explaining to someone new to programming
- **Avoid jargon** - Don't use technical terms unless necessary
- **Explain technical terms** - If you must use a complex term, explain it immediately
- **Include examples** - Every method must have at least one usage example
- **Be specific** - Instead of "processes data", say "converts text to lowercase"
- **Mention defaults** - Document default parameter values in plain English
- **Describe edge cases** - Explain what happens with empty inputs or special cases

### Examples of Good Documentation

**Example 1: Simple Method**

````php
/**
 * Checks if a value exists anywhere in an array.
 *
 * This method searches through an array to see if a specific value is present.
 * Think of it like looking through a list to find a specific item.
 *
 * Example:
 * ```php
 * use Phuture\Coherence\Arrays;
 * 
 * $fruits = ['apple', 'banana', 'orange'];
 * $hasApple = Arrays::contains($fruits, 'apple');
 * 
 * // Returns true
 * ```
 *
 * @param array $array The list of items to search through
 * @param mixed $value The item you're looking for
 * @param bool $strict If true, checks both value and type (default: false)
 * @return bool Returns true if found, false if not found
 */
public static function contains(array $array, mixed $value, bool $strict = false): bool
{
    ...
}
````

**Example 2: Method with Callback Parameter**

````php
/**
 * Applies a filter to all values in a nested array.
 *
 * This method goes through every item in an array, including items inside nested arrays
 * (arrays within arrays), and applies a transformation to each value. A "callback" is a
 * function you provide that tells this method how to transform each value.
 *
 * For example, you could use this to sanitize (clean) all user input in a complex form,
 * or convert all values to uppercase.
 *
 * Example:
 * ```php
 * use Phuture\Coherence\Arrays;
 *
 * $data = ['name' => 'John', 'address' => ['city' => 'NYC', 'zip' => '10001']];
 * $clean = Arrays::filterRecursive($data, fn($val) => htmlspecialchars($val));
 * ```
 *
 * @param array $array The array to process, which may contain nested arrays
 * @param callable $callback A function that receives each value and returns the transformed value.
 *  The callback has the signature `function (mixed $value): mixed`
 * @return array Returns a new array with all values transformed by your callback function
 */
public static function filterRecursive(array $array, callable $callback): array
{
    ...
}
````

**Example 3: Method That Modifies Original Data**

````php
/**
 * Sorts an array in alphabetical or numerical order.
 *
 * This method arranges the items in an array from smallest to largest (or A to Z).
 * IMPORTANT: This changes the original array that you pass in - it doesn't create a copy.
 *
 * Example:
 * ```php
 * use Phuture\Coherence\Arrays;
 * 
 * $numbers = [3, 1, 4, 1, 5];
 * Arrays::sort($numbers);
 * 
 * // $numbers is now [1, 1, 3, 4, 5]
 * ```
 *
 * @param array $array The array to sort (this will be modified directly)
 * @param int $flags How to compare items - use SORT_REGULAR for normal sorting (default: SORT_REGULAR)
 * @return bool Returns true if sorting succeeded, false if it failed
 */
public static function sort(array &$array, int $flags = SORT_REGULAR): bool
{
    ...
}
````

### Bad Examples vs. Good Examples

**Bad: Too technical and unclear**
```php
/**
 * Performs a binary search on a sorted array utilizing a comparison function.
 *
 * @param array $array The haystack
 * @param mixed $needle The value to locate
 * @return int|false The index or false
 */
```

**Good: Clear and simple**
````php
/**
 * Finds the position of a value in an array.
 *
 * Searches through an array to find where a specific value is located.
 * Returns the position number (starting from 0) if found.
 *
 * Example:
 * ```php
 * $colors = ['red', 'blue', 'green'];
 * $position = Arrays::search($colors, 'blue'); // Returns 1
 * ```
 *
 * @param array $array The array to search through
 * @param mixed $needle The value you're looking for
 * @param bool $strict If true, also checks that the type matches (default: false)
 * @return int|string|false Returns the position if found, or false if not found
 */
````

---

## Testing Requirements

### MANDATORY RULE

**Every new method MUST have corresponding tests in `./tests` using Nette Tester.**

A method is NOT considered complete until its tests are written and passing.

### Testing Framework

- **Framework**: Nette Tester
- **Test Directory**: `./tests/`
- **Test File Naming**: Match class name (e.g., `ArraysTest.php` for `Arrays.php`)
- **Test Method Naming**: `test{MethodName}()` pattern

### Test Coverage Requirements

Tests must cover:
1. **Happy path**: Normal expected usage
2. **Edge cases**: Boundary conditions, empty inputs, null values
3. **Error conditions**: Invalid inputs, expected exceptions
4. **All code paths**: Every branch and condition in the method

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage report
composer test-coverage

# Run specific test file
composer test tests/ArraysTest.php
```

### Test Example

```php
<?php

namespace Phuture\Coherence\Tests;

use Phuture\Coherence\Arrays;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

class ArraysTest extends TestCase
{
    public function testContains(): void
    {
        // Happy path
        Assert::true(Arrays::contains([1, 2, 3], 2));
        Assert::false(Arrays::contains([1, 2, 3], 4));

        // Strict comparison
        Assert::true(Arrays::contains([1, 2, '3'], 3, false));
        Assert::false(Arrays::contains([1, 2, '3'], 3, true));

        // Edge cases
        Assert::false(Arrays::contains([], 1));
        Assert::true(Arrays::contains([null], null));
    }
}

(new ArraysTest())->run();
```

---

## Code Quality Standards

### Required Standards

1. **PSR-12**: Mandatory coding style standard
2. **PHPStan**: Static analysis at level `max`
3. **PHP CodeSniffer**: Automatic PSR-12 enforcement

### Quality Check Commands

```bash
# Run PHPStan static analysis
composer phpstan

# Check PSR-12 compliance
composer cs-check

# Auto-fix PSR-12 violations
composer cs-fix

# Run all quality checks
composer phpstan && composer cs-check && composer test
```

### Pre-Commit Checklist

Before committing code, ensure:
- [ ] All tests pass (`composer test`)
- [ ] PHPStan passes with no errors (`composer phpstan`)
- [ ] Code follows PSR-12 (`composer cs-check`)
- [ ] All new methods have tests
- [ ] All methods have complete PHPDoc

---

## Adding New Methods

### Step-by-Step Checklist

1. **Determine Primary Data Parameter**
   - Identify what data the method operates on
   - This becomes the first parameter

2. **Order Arguments According to Pattern**
   - `($data, [required], [callables], [optional], [variadics])`
   - Follow the standard pattern strictly

3. **Add Comprehensive PHPDoc**
   - Brief description in simple language
   - Extended description explaining clearly (avoid jargon)
   - **Usage example wrapped in ```php code blocks (REQUIRED)**
   - All `@param` tags with clear descriptions
   - `@return` tag explaining what is returned
   - `@see` tag linking other related methods, like first() being related to last(), or flatten() to unflatten()

4. **Implement Method Body**
   - Keep it simple and focused
   - Add type safety where beneficial

5. **Write Tests (MANDATORY)**
   - Create tests in `./tests/` using Nette Tester
   - Cover all code paths and edge cases
   - Method is incomplete without tests

6. **Run Tests**
   - Execute `composer test`
   - Verify all tests pass
   - Check test coverage if needed

7. **Run Quality Checks**
   - Execute `composer phpstan`
   - Execute `composer cs-check`
   - Fix any violations

8. **Verify PSR-12 Compliance**
   - Ensure code formatting is correct
   - Run `composer cs-fix` if needed

9. **Ensure Complete Coverage**
   - Review test coverage
   - Add tests for any missed code paths

### Example: Adding a New Method

```php
public static function containsKey(array $array, string|int $key): bool
{
    return array_key_exists($key, $array);
}
```

**Corresponding Test:**

```php
public function testContainsKey(): void
{
    $array = ['foo' => 'bar', 'baz' => 'qux'];

    Assert::true(Arrays::containsKey($array, 'foo'));
    Assert::false(Arrays::containsKey($array, 'nonexistent'));
    Assert::true(Arrays::containsKey([0 => 'a', 1 => 'b'], 0));
    Assert::false(Arrays::containsKey([], 'key'));
}
```

---

## Adding New Utility Classes

### Requirements Checklist

1. **Place in `./src/` (Root Level Only)**
   - Do not create subdirectories
   - Keep all main utility classes at root level

2. **Extend `StaticClass`**
   ```php
use Phuture\Coherence\Support\Class\StaticClass;

   class NewUtility extends StaticClass
   {
       // ...
   }
   ```

3. **All Methods Must Be `public static`**
   - No instance methods
   - No constructors
   - No properties

4. **Follow Namespace Convention**
   ```php
   namespace Phuture\Coherence;
   ```

5. **Add Class-Level PHPDoc**
   ```php
   /**
    * Brief description of the utility class purpose.
    *
    * Extended description of what this class provides and when to use it.
    *
    * @copyright Copyright (c) 2025, Advandz Technologies, LLC
    * @license https://opensource.org/licenses/MIT MIT License
    * @link https://www.phuture.dev/ Phuture
    */
   class NewUtility extends StaticClass
   ```

6. **Follow Argument Ordering Pattern**
   - Apply the standard pattern to all methods
   - Maintain consistency with existing classes

7. **Create Comprehensive Test File**
   - Create `tests/NewUtilityTest.php`
   - Test all methods thoroughly

### New Class Template

```php
<?php

namespace Phuture\Coherence;

use Phuture\Coherence\Support\StaticClass;

/**
 * Brief description of the utility class purpose.
 *
 * Extended description of what this class provides and when to use it.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 */
class NewUtility extends StaticClass
{
    /**
     * Brief method description.
     *
     * Extended description of what this method does.
     *
     * @param type $primaryData The primary data to operate on
     * @param type $param Additional parameters
     * @return type Description of return value
     */
    public static function methodName(type $primaryData, type $param): returnType
    {
        // Implementation
    }
}
```

---

## Anti-Patterns to Avoid

### Wrong Argument Ordering

```php
// WRONG: Optional parameter before required parameter
public static function search(array $array, bool $strict = false, mixed $needle)

// WRONG: Variadic not last
public static function merge(array ...$arrays, bool $recursive = false)

// WRONG: Primary data not first
public static function contains(mixed $needle, array $array)
```

### Non-Static Methods

```php
// L WRONG: Instance method in utility class
class Arrays extends StaticClass
{
    public function contains(array $array, mixed $value): bool
    {
        return in_array($value, $array);
    }
}
```

### Missing PHPDoc

```php
// WRONG: No documentation
public static function search(array $array, mixed $needle, bool $strict = false)
{
    return array_search($needle, $array, $strict);
}
```

### Incomplete PHPDoc

```php
// WRONG: Missing return and parameter descriptions
/**
 * Searches for a value in an array.
 */
public static function search(array $array, mixed $needle, bool $strict = false)
```

### Inconsistent Naming

```php
// WRONG: Using snake_case instead of camelCase
public static function array_search(array $array, mixed $needle): int|false

// WRONG: Not descriptive enough
public static function srch(array $array, mixed $needle): int|false
```

---

## Summary

This workflow guide establishes the standards for maintaining consistency, predictability, and quality across the Phuture Coherence project. By following these patterns and practices, we ensure that the library remains:

- **Consistent**: All methods follow the same conventions
- **Maintainable**: Clear documentation and tests make updates easy
- **Reliable**: Comprehensive testing and quality checks prevent regressions
- **Professional**: High code quality standards reflect well on the project

Remember the key principles:

1. **Static methods only** in `./src/` utility classes
2. **Argument ordering**: `($data, [required], [callables], [optional], [variadics])`
3. **Complete PHPDoc** for every method
4. **Tests are mandatory** for every new method
5. **PSR-12 compliance** enforced through tooling

When in doubt, refer to existing classes as examples of proper implementation.

---

**Last Updated**: 2025-12-05
**Version**: 1.0