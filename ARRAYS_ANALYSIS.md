# ARRAYS_ANALYSIS.md

## Arrays Class - Exhaustive Analysis

**Date**: 2025-02-04
**Class**: `Phuture\Coherence\Arrays`
**Version**: Feature Branch (arrays)
**Lines of Code**: ~4,215
**Total Methods**: 79 public methods, 3 private helper methods

---

## Table of Contents

1. [Functionality](#1-functionality)
2. [Performance](#2-performance)
3. [Security](#3-security)

---

## 1. Functionality

### 1.1 Overview

The `Arrays` class is a comprehensive static utility class for array manipulation in PHP. It provides a rich set of methods covering:

- **Access & Navigation**: `get()`, `getReference()`, `has()`, `exists()`, `accessible()`
- **Data Transformation**: `map()`, `mapKeys()`, `mapWithKeys()`, `filter()`, `reduce()`
- **Set Operations**: `difference()`, `intersect()`, `union` operations via `merge()`
- **Sorting**: `sort()`, `sortAssoc()`, `sortKeys()`, `sortNatural()`
- **Structural Operations**: `flatten()`, `collapse()`, `notation()`, `denote()`
- **Stack/Queue Operations**: `push()`, `pull()`, `shift()`, `unshift()`
- **Utility**: `first()`, `last()`, `random()`, `shuffle()`, `split()`, `join()`
- **Type Conversion**: `toArray()`, `toObject()`, `normalize()`
- **Fluent Interface**: `of()` returning `Type\Arrays` wrapper

### 1.2 Comparison with Major Frameworks

#### 1.2.1 Laravel Collection Methods

| Laravel | Phuture Coherence | Status | Notes |
|---------|-------------------|--------|-------|
| `all()` | N/A | ❌ Missing | Use `toArray()` on fluent wrapper |
| `average()` | N/A | ❌ Missing | Use `reduce()` or calculate manually |
| `chunk()` | `split()` | ✅ Equivalent | Different naming |
| `collapse()` | `collapse()` | ✅ Same | |
| `combine()` | `combine()` | ✅ Same | |
| `concat()` | N/A | ❌ Missing | Use `join()` or `merge()` |
| `contains()` | `contains()` | ✅ Same | |
| `containsOneItem()` | N/A | ❌ Missing | |
| `count()` | `length()` | ✅ Same | |
| `crossJoin()` | `crossJoin()` | ✅ Same | |
| `dd()` | N/A | ❌ Missing | Debug helper - out of scope |
| `diff()` | `difference()` | ✅ Same | |
| `diffAssoc()` | `differenceAssoc()` | ✅ Same | |
| `diffKeys()` | `differenceKeys()` | ✅ Same | |
| `dump()` | N/A | ❌ Missing | Debug helper - out of scope |
| `duplicates()` | N/A | ❌ Missing | Use `unique()` for opposite |
| `each()` | `iterate()` | ✅ Same | |
| `eachSpread()` | N/A | ❌ Missing | |
| `every()` | `every()` | ✅ Same | |
| `except()` | N/A | ❌ Missing | Use `only()` inverse |
| `filter()` | `filter()` | ✅ Same | |
| `first()` | `first()` | ✅ Same | |
| `firstOrFail()` | N/A | ❌ Missing | Use `first()` with exception check |
| `firstWhere()` | `find()` | ✅ Same | |
| `flatMap()` | N/A | ❌ Missing | Can use `map()` + `flatten()` combo |
| `flatten()` | `flatten()` | ✅ Same | |
| `flip()` | `flip()` | ✅ Same | |
| `forget()` | `remove()` | ✅ Same | |
| `forPage()` | N/A | ❌ Missing | Pagination helper |
| `get()` | `get()` | ✅ Same | |
| `groupBy()` | N/A | ❌ Missing | **Notable Gap** |
| `has()` | `has()` | ✅ Same | |
| `implode()` | N/A | ❌ Missing | Use native `implode()` |
| `intersect()` | `intersect()` | ✅ Same | |
| `intersectAssoc()` | `intersectAssoc()` | ✅ Same | |
| `intersectByKeys()` | `intersectKeys()` | ✅ Same | |
| `isEmpty()` | `isBlank()` | ✅ Same | |
| `isNotEmpty()` | `isFilled()` | ✅ Same | |
| `join()` | `join()` | ✅ Same | |
| `keyBy()` | `associate()` | ✅ Same | |
| `keys()` | `keys()` | ✅ Same | |
| `last()` | `last()` | ✅ Same | |
| `lazy()` | N/A | ❌ Missing | Lazy evaluation - advanced |
| `macro()` | N/A | ❌ Missing | Runtime method extension |
| `make()` | `of()` | ✅ Same | |
| `map()` | `map()` | ✅ Same | |
| `mapInto()` | N/A | ❌ Missing | Object transformation |
| `mapSpread()` | N/A | ❌ Missing | |
| `mapToDictionary()` | N/A | ❌ Missing | Advanced grouping |
| `mapToGroups()` | N/A | ❌ Missing | Advanced grouping |
| `mapWithKeys()` | `mapWithKeys()` | ✅ Same | |
| `median()` | N/A | ❌ Missing | Statistical operation |
| `merge()` | `merge()` | ⚠️ Different | Always recursive in Coherence |
| `mergeRecursive()` | N/A | ❌ Missing | Coherence's `merge()` is always recursive |
| `mode()` | N/A | ❌ Missing | Statistical operation |
| `nth()` | N/A | ❌ Missing | |
| `only()` | `only()` | ✅ Same | |
| `pad()` | `pad()` | ✅ Same | |
| `partition()` | N/A | ❌ Missing | **Notable Gap** |
| `pipe()` | N/A | ❌ Missing | Fluent pipeline helper |
| `pluck()` | `column()` | ✅ Same | Different naming |
| `pop()` | `pull()` | ✅ Same | Different naming |
| `prepend()` | `prepend()` | ✅ Same | |
| `pull()` | N/A | ❌ Missing | Get and remove element |
| `push()` | `push()` | ✅ Same | |
| `put()` | N/A | ❌ Missing | Use array access or merge |
| `random()` | `random()` | ✅ Same | |
| `range()` | N/A | ❌ Missing | Use native `range()` |
| `reduce()` | `reduce()` | ✅ Same | |
| `reduceSpread()` | N/A | ❌ Missing | |
| `reject()` | N/A | ❌ Missing | Use `filter()` with inverted callback |
| `replace()` | `replace()` | ⚠️ Different | Different signature |
| `reverse()` | `reverse()` | ✅ Same | |
| `search()` | `search()` | ✅ Same | |
| `shift()` | `shift()` | ✅ Same | |
| `shuffle()` | `shuffle()` | ✅ Same | |
| `skip()` | N/A | ❌ Missing | Slice helper |
| `slice()` | `slice()` | ✅ Same | |
| `some()` | `some()` | ✅ Same | |
| `sort()` | `sort()` | ✅ Same | |
| `sortDesc()` | `sort($reverse=true)` | ✅ Same | |
| `sortBy()` | N/A | ❌ Missing | **Notable Gap** - multi-column sort |
| `sortKeys()` | `sortKeys()` | ✅ Same | |
| `sortKeysDesc()` | `sortKeys($reverse=true)` | ✅ Same | |
| `splice()` | `splice()` | ✅ Same | |
| `split()` | `split()` | ⚠️ Different | Coherence: chunk, Laravel: split by value |
| `sum()` | `sum()` | ✅ Same | |
| `take()` | N/A | ❌ Missing | Slice helper |
| `tap()` | N/A | ❌ Missing | Fluent helper |
| `times()` | N/A | ❌ Missing | Factory helper |
| `toArray()` | `toArray()` | ✅ Same | |
| `toJson()` | N/A | ❌ Missing | Use `json_encode()` |
| `transform()` | N/A | ❌ Missing | In-place modification |
| `union()` | N/A | ❌ Missing | Use `merge()` |
| `unique()` | `unique()` | ✅ Same | |
| `unless()` | N/A | ❌ Missing | Conditional helper |
| `unlessEmpty()` | N/A | ❌ Missing | Conditional helper |
| `unlessNotEmpty()` | N/A | ❌ Missing | Conditional helper |
| `unwrap()` | N/A | ❌ Missing | Static class doesn't wrap |
| `values()` | `values()` | ✅ Same | |
| `when()` | N/A | ❌ Missing | Conditional helper |
| `whenEmpty()` | N/A | ❌ Missing | Conditional helper |
| `whenNotEmpty()` | N/A | ❌ Missing | Conditional helper |
| `where()` | `grep()` | ⚠️ Partial | `grep()` is regex only, not predicate |
| `whereBetween()` | N/A | ❌ Missing | Range filtering |
| `whereIn()` | N/A | ❌ Missing | In-array filtering |
| `whereInstanceOf()` | N/A | ❌ Missing | Type filtering |
| `whereNotBetween()` | N/A | ❌ Missing | Range filtering |
| `whereNotIn()` | N/A | ❌ Missing | In-array filtering |
| `whereNotNull()` | N/A | ❌ Missing | Use `filter()` |
| `whereNull()` | N/A | ❌ Missing | Use `filter()` |
| `wrap()` | `wrap()` | ⚠️ Different | Coherence: string wrapping, Laravel: array wrapping |
| `zip()` | N/A | ❌ Missing | Array pairing |

#### 1.2.2 Symfony/Yii Array Helper Methods

| Symfony/Utils | Phuture Coherence | Status | Notes |
|---------------|-------------------|--------|-------|
| `arrayAssume()` | N/A | ❌ Missing | |
| `arrayAssumeRecursive()` | N/A | ❌ Missing | |
| `toArray()` | `toArray()` | ✅ Same | |
| `isIndexed()` | `isList()` | ✅ Same | |
| `isAssoc()` | `isAssoc()` | ✅ Same | |
| `isIterable()` | `accessible()` | ⚠️ Partial | Different semantics |
| `insertBefore()` | `insertBefore()` | ✅ Same | |
| `insertAfter()` | `insertAfter()` | ✅ Same | |
| `keyBy()` | `associate()` | ✅ Same | |
| `map()` | `map()` | ✅ Same | |
| `mapWithKeys()` | `mapWithKeys()` | ✅ Same | |
| `mapToGroups()` | N/A | ❌ Missing | |
| `groupBy()` | N/A | ❌ Missing | **Notable Gap** |
| `flatten()` | `flatten()` | ⚠️ Different | Symfony: optional preserve keys |
| `filter()` | `filter()` | ✅ Same | |
| `find()` | `find()` | ✅ Same | |
| `pick()` | `only()` | ✅ Same | |
| `renameKey()` | `rename()` | ✅ Same | |
| `mergeRecursive()` | `merge()` | ✅ Same | |
| `mergeRecursiveDistinct()` | N/A | ❌ Missing | |
| `nest()` | `denote()` | ⚠️ Partial | Different semantics |
| `unflatten()` | `notation()` | ⚠️ Partial | Different semantics |

#### 1.2.3 Nette Utils Arrays

| Nette Utils | Phuture Coherence | Status | Notes |
|-------------|-------------------|--------|-------|
| `get()` | `get()` | ✅ Same | Recently implemented inline |
| `getRef()` | `getReference()` | ✅ Same | Recently implemented inline |
| `associate()` | `associate()` | ✅ Same | |
| `contains()` | `contains()` | ✅ Same | |
| `every()` | `every()` | ✅ Same | Recently implemented inline |
| `filter()` | `filter()` | ✅ Same | Recently implemented inline |
| `first()` | `first()` | ✅ Same | |
| `flatten()` | `flatten()` | ✅ Same | Recently implemented inline |
| `flip()` | `flip()` | ✅ Same | |
| `grep()` | `grep()` | ✅ Same | Recently implemented inline |
| `insertAfter()` | `insertAfter()` | ✅ Same | Recently implemented inline |
| `insertBefore()` | `insertBefore()` | ✅ Same | Recently implemented inline |
| `isList()` | `isList()` | ✅ Same | |
| `last()` | `last()` | ✅ Same | |
| `map()` | `map()` | ✅ Same | |
| `pick()` | `only()` | ✅ Same | |
| `renameKey()` | `rename()` | ✅ Same | |
| `some()` | `some()` | ✅ Same | Recently implemented inline |
| `sort()` | `sort()` | ✅ Same | Coherence has more options |
| `toKey()` | N/A | ❌ Missing | |
| `walk()` | `iterate()` | ✅ Same | |
| `wrap()` | `wrap()` | ❌ Different | Different semantics |

### 1.3 Notable Gaps and Recommended Solutions

#### 1.3.1 High Priority Missing Features

1. **`groupBy()`** - Critical for data aggregation
   ```php
   // Proposed implementation
   public static function groupBy(array $array, callable|string $groupBy): array
   {
       $result = [];

       foreach ($array as $key => $item) {
           $groupKey = is_string($groupBy)
               ? (is_array($item) ? $item[$groupBy] : $item->{$groupBy})
               : $groupBy($item, $key);

           $result[$groupKey][] = $item;
       }

       return $result;
   }
   ```

2. **`partition()`** - Split array by callback
   ```php
   // Proposed implementation
   public static function partition(array $array, callable $callback): array
   {
       $passed = [];
       $failed = [];

       foreach ($array as $key => $item) {
           if ($callback($item, $key)) {
               $passed[$key] = $item;
           } else {
               $failed[$key] = $item;
           }
       }

       return [$passed, $failed];
   }
   ```

3. **`where()` with predicate support** - Currently only `grep()` for regex
   ```php
   // Proposed implementation
   public static function where(array $array, callable $callback): array
   {
       return self::filter($array, $callback);
   }

   public static function whereIn(array $array, string $key, array $values): array
   {
       return self::filter($array, fn($item) =>
           in_array(is_array($item) ? $item[$key] : $item->{$key}, $values, true)
       );
   }
   ```

4. **`sortBy()` with multiple columns** - Multi-column sorting
   ```php
   // Proposed implementation
   public static function sortBy(array $array, string|array $criteria): array
   {
       $criteria = (array) $criteria;

       usort($array, function ($a, $b) use ($criteria) {
           foreach ($criteria as $column) {
               $aVal = is_array($a) ? $a[$column] : $a->{$column};
               $bVal = is_array($b) ? $b[$column] : $b->{$column};

               if ($aVal !== $bVal) {
                   return $aVal <=> $bVal;
               }
           }
           return 0;
       });

       return $array;
   }
   ```

5. **`pluck()` with nested path support** - Enhanced `column()`
   ```php
   // Proposed enhancement to column()
   public static function pluck(array $array, string $path): array
   {
       $result = [];

       foreach ($array as $item) {
           $result[] = self::get($item, explode('.', $path));
       }

       return $result;
   }
   ```

#### 1.3.2 Medium Priority Missing Features

1. **`zip()`** - Pair arrays
2. **`take()` / `skip()`** - Slice helpers
3. **`paginate()` / `forPage()`** - Pagination helpers
4. **`tap()`** - Fluent helper
5. **`average()` / `median()` / `mode()`** - Statistical operations

#### 1.3.3 Low Priority Missing Features

1. **Lazy evaluation** - `lazy()` for large datasets
2. **Macro system** - Runtime method extension
3. **Conditional helpers** - `when()`, `unless()`, etc.
4. **Debug helpers** - `dd()`, `dump()`, etc.

### 1.4 Unique Features (Advantages Over Frameworks)

| Feature | Description | Advantage |
|---------|-------------|-----------|
| `notation()` / `denote()` | Dot notation flattening/expansion | Not in Laravel/Symfony |
| `Reference operations` | `getReference()` with path creation | Unique memory-efficient approach |
| `normalize()` | Object to array conversion | More comprehensive than competitors |
| `toObject()` | Smart array to object (preserves lists) | Laravel converts all to stdClass |
| `ArrayComparator` enum | Type-safe comparison mode | No equivalent in competitors |
| `RECURSION_LIMIT` | Built-in recursion protection | Safety feature not in competitors |
| `wrap()` with prefix/suffix | String wrapping of scalar elements | Unique feature |

---

## 2. Performance

### 2.1 Method-by-Method Performance Analysis

#### 2.1.1 O(1) Operations - Constant Time

| Method | Complexity | Notes |
|--------|------------|-------|
| `first()` | O(1) | Uses `array_first()` - efficient |
| `firstKey()` | O(1) | Uses `array_key_first()` - efficient |
| `last()` | O(1) | Uses `array_last()` - efficient |
| `lastKey()` | O(1) | Uses `array_key_last()` - efficient |
| `length()` | O(1) | Uses native `count()` |
| `isBlank()` | O(1) | Uses `count() === 0` |
| `isFilled()` | O(1) | Uses `count() > 0` |
| `isList()` | O(1) | Uses `array_is_list()` (PHP 8.1+) |
| `accessible()` | O(1) | Type checks only |
| `exists()` | O(1) | Uses `array_key_exists()` |

**Recommendations**: All O(1) methods are optimally implemented.

#### 2.1.2 O(n) Operations - Linear Time

| Method | Complexity | Potential Issues | Recommendations |
|--------|------------|------------------|----------------|
| `contains()` | O(n) | Uses `in_array()` - optimal | ✅ Good |
| `every()` | O(n) | Short-circuits on false | ✅ Good |
| `some()` | O(n) | Short-circuits on true | ✅ Good |
| `filter()` | O(n) | Uses `array_filter()` | ✅ Good |
| `find()` | O(n) | Uses `array_find()` - short-circuits | ✅ Good |
| `findKey()` | O(n) | Uses `array_find_key()` - short-circuits | ✅ Good |
| `map()` | O(n) | Uses `array_map()` | ✅ Good |
| `keys()` | O(n) | Uses `array_keys()` | ✅ Good |
| `values()` | O(n) | Uses `array_values()` | ✅ Good |
| `sum()` | O(n) | Uses `array_sum()` | ✅ Good |
| `product()` | O(n) | Uses `array_product()` | ✅ Good |
| `flip()` | O(n) | Uses `array_flip()` | ✅ Good |
| `reverse()` | O(n) | Uses `array_reverse()` | ✅ Good |
| `shuffle()` | O(n) | Uses native `shuffle()` | ✅ Good |
| `unique()` | O(n log n) | Uses `array_unique()` | ⚠️ Sorting overhead |
| `search()` | O(n) | Uses `array_search()` | ✅ Good |
| `random()` | O(1) | Uses `array_rand()` | ✅ Good |
| `get()` | O(n) | Path traversal - n = depth | ✅ Good |
| `has()` | O(n) | Path traversal - n = depth | ✅ Good |
| `remove()` | O(n) | Path traversal + unset | ✅ Good |
| `rename()` | O(n) | Path traversal + array rebuild | ⚠️ Rebuild inefficient |

**Performance Concerns**:

1. **`rename()` (line 3068-3102)** - Rebuilds entire array to preserve key order
   ```php
   // Current implementation rebuilds entire array
   $result = [];
   foreach ($current as $key => $value) {
       if ($key === $keyToRename) {
           $result[$newKey] = $value;
       } else {
           $result[$key] = $value;
       }
   }
   ```
   **Impact**: O(n) where n is size of the target sub-array, not just the renamed key's position.
   **Recommendation**: This is acceptable for maintaining key order, but could use `array_splice()` for better performance with numeric keys.

2. **`flatten()` (line 1231-1246)** - Recursive with `array_merge`
   ```php
   foreach ($array as $value) {
       if (is_array($value)) {
           if (!empty($value)) {
               $result = array_merge($result, self::flatten($value));
           }
       } else {
           $result[] = $value;
       }
   }
   ```
   **Impact**: `array_merge()` is O(n) for each merge, making overall complexity O(n²) in worst case for deeply nested arrays.
   **Recommendation**: Use array spread operator or pass by reference for better performance.

#### 2.1.3 O(n log n) Operations - Sorting

| Method | Complexity | Notes |
|--------|------------|-------|
| `sort()` | O(n log n) | Uses native sort - optimal |
| `sortAssoc()` | O(n log n) | Uses `asort()`/`arsort()` - optimal |
| `sortKeys()` | O(n log n) | Uses `ksort()`/`krsort()` - optimal |
| `sortNatural()` | O(n log n) | Uses natural sort - optimal |
| `unique()` | O(n log n) | Uses `array_unique()` with sorting |

**Recommendations**: All sorting operations use native PHP functions which are highly optimized.

#### 2.1.4 O(n*m) Operations - Multi-Array Operations

| Method | Complexity | Potential Issues | Recommendations |
|--------|------------|------------------|----------------|
| `difference()` | O(n*m) | Native `array_diff()` | ✅ Acceptable |
| `differenceAssoc()` | O(n*m) | Native `array_diff_assoc()` | ✅ Acceptable |
| `differenceKeys()` | O(n*m) | Native `array_diff_key()` | ✅ Acceptable |
| `intersect()` | O(n*m) | Native `array_intersect()` | ✅ Acceptable |
| `intersectAssoc()` | O(n*m) | Native `array_intersect_assoc()` | ✅ Acceptable |
| `intersectKeys()` | O(n*m) | Native `array_intersect_key()` | ✅ Acceptable |
| `merge()` | O(n*m) | Uses `array_merge_recursive()` | ✅ Acceptable |
| `join()` | O(n*m) | Uses `array_merge()` | ✅ Acceptable |
| `crossJoin()` | O(n₁×n₂×...×nₖ) | Cartesian product - expected | ✅ Acceptable |

#### 2.1.5 Recursive Operations

| Method | Complexity | Recursion Protection | Notes |
|--------|------------|---------------------|-------|
| `notation()` | O(n) | ✅ Via `flattenToNotation()` | Uses depth limit |
| `denote()` | O(n) | ❌ None | **Potential Issue** |
| `normalize()` | O(n) | ✅ Via `normalizeRecursive()` | Uses depth limit |
| `toObject()` | O(n) | ✅ Via `toObjectRecursive()` | Uses depth limit |
| `flatten()` | O(n²) worst case | ❌ None | **Potential Issue** |

**Performance Concerns**:

1. **`flatten()` without recursion limit** - Could cause stack overflow on malicious input
   ```php
   public static function flatten(array $array): array
   {
       $result = [];
       foreach ($array as $value) {
           if (is_array($value)) {
               if (!empty($value)) {
                   $result = array_merge($result, self::flatten($value));
               }
           } else {
               $result[] = $value;
           }
       }
       return $result;
   }
   ```
   **Recommendation**: Add recursion depth tracking.

2. **`denote()` without recursion limit** - Could be exploited with deeply nested input
   ```php
   public static function denote(array $array, bool $strict = false): array
   {
       // No depth tracking in the main loop
   }
   ```
   **Recommendation**: Add recursion depth tracking similar to other methods.

3. **`flattenToNotation()` helper** - Uses recursion limit properly
   ```php
   private static function flattenToNotation(array $array, string $prefix, array &$result, int $depth = 0): void
   {
       if ($depth >= self::RECURSION_LIMIT) {
           throw new LogicException("Limit Exceeded...");
       }
       // ...
   }
   ```
   ✅ **Good implementation** - Protected against abuse.

#### 2.1.6 String Operations

| Method | Complexity | Notes |
|--------|------------|-------|
| `fromString()` | O(n) | Uses `explode()` - optimal |
| `toString()` | N/A | Not implemented |
| `wrap()` | O(n) | String concatenation in loop | ✅ Good |

#### 2.1.7 Reference Operations

| Method | Complexity | Notes |
|--------|------------|-------|
| `getReference()` | O(d) | d = depth of path | ✅ Optimal |
| `prepend()` | O(n) | Re-indexes all keys | ⚠️ Could be expensive |
| `append()` | O(n) | n = number of new keys | ✅ Good |
| `insertAfter()` | O(n) | Uses `array_slice()` + array rebuild | ⚠️ Expensive for large arrays |
| `insertBefore()` | O(n) | Uses `array_slice()` + array rebuild | ⚠️ Expensive for large arrays |

**Performance Concern** - `insertAfter()` and `insertBefore()`:
```php
// Current implementation for insertAfter
$keys = array_keys($array);  // O(n)
$position = array_search($key, $keys, true);  // O(n)
$before = array_slice($array, 0, $position + 1, true);  // O(n)
$after = array_slice($array, $position + 1, null, true);  // O(n)
$array = $before + $items + $after;  // O(n+m)
```
**Total**: O(5n) where n = array size. For large arrays (10,000+ elements), this could be slow.

**Recommendation**: Consider using SplFixedArray or native array functions for better performance on large arrays.

### 2.2 Memory Efficiency

#### 2.2.1 Memory-Intensive Operations

| Method | Memory Impact | Notes |
|--------|---------------|-------|
| `flatten()` | High | Creates new arrays at each level |
| `notation()` | Medium | Creates new flat array |
| `denote()` | Medium | Creates nested structure |
| `normalize()` | Medium | Recursively converts objects |
| `toObject()` | Medium | Creates new stdClass instances |
| `crossJoin()` | Very High | Creates n₁×n₂×...×nₖ elements |

**Memory Concerns**:

1. **`crossJoin()`** - Exponential memory growth
   ```php
   // With 5 arrays of 10 elements each: 10^5 = 100,000 elements
   // With 10 arrays of 5 elements each: 5^10 = 9,765,625 elements
   ```
   **Recommendation**: Add warning or limit for large inputs.

2. **`flatten()`** - Inefficient memory usage with `array_merge()`
   ```php
   $result = array_merge($result, self::flatten($value));
   ```
   Each `array_merge()` creates a new array. For deep nesting, this is wasteful.
   **Recommendation**: Use array spread operator or pass by reference:
   ```php
   public static function flatten(array $array, int $depth = 0): array
   {
       $result = [];
       foreach ($array as $value) {
           if (is_array($value)) {
               if ($depth >= self::RECURSION_LIMIT) {
                   continue;
               }
               if (!empty($value)) {
                   // More memory efficient
                                               $result[] = $v;
                                               }
                                           }
                                       } else {
                                           $result[] = $value;
                                       }
                                   }
                               }
                               return $result;
                           }
                       }
                   }
               }
           }
       }
       return $result;
   }
   ```

#### 2.2.2 Memory-Efficient Operations

| Method | Memory Impact | Notes |
|--------|---------------|-------|
| `getReference()` | Very Low | Returns reference - no copy |
| `iterate()` | Very Low | Uses `array_walk()` - in-place |
| `sort*()` methods | Very Low | In-place sorting |
| `shuffle()` | Very Low | In-place shuffling |
| `push()` | Low | Amortized O(1) |
| `pull()` | Low | Removes element |
| `shift()` | Medium | Re-indexes all keys |

### 2.3 Optimization Recommendations

#### 2.3.1 High Priority

1. **Add recursion limit to `flatten()`**
   ```php
   public static function flatten(array $array, int $depth = 0): array
   {
       if ($depth >= self::RECURSION_LIMIT) {
           throw new LogicException("Recursion limit exceeded");
       }
       // ... rest of implementation
   }
   ```

2. **Optimize `flatten()` to avoid `array_merge()` overhead**
   ```php
   // Use array spreading or pass-by-reference accumulation
   ```

3. **Add recursion limit to `denote()`**
   ```php
   public static function denote(array $array, bool $strict = false, int $depth = 0): array
   {
       if ($depth >= self::RECURSION_LIMIT) {
           throw new LogicException("Recursion limit exceeded");
       }
       // ... rest of implementation
   }
   ```

4. **Optimize `insertAfter()` and `insertBefore()` for large arrays**
   ```php
   // Consider using SplFixedArray for better performance
   // Or document the O(n) complexity
   ```

#### 2.3.2 Medium Priority

1. **Add `memoize()` decorator for expensive operations**
2. **Add `lazy()` wrapper for deferred evaluation**
3. **Use generator functions for large dataset operations**

#### 2.3.3 Low Priority

1. **Micro-optimizations** - Most operations already use native PHP functions
2. **JIT optimization hints** - PHP 8+ JIT is already effective

---

## 3. Security

### 3.1 Input Validation Issues

#### 3.1.1 Critical Issues

1. **`toArray()` - JSON Injection Vulnerability (line 3818-3874)**
   ```php
   // Handle JSON strings
   if (is_string($value)) {
       $decoded = json_decode($value, true);
       if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
           return $decoded;
       }
   }
   ```
   **Issue**: Accepts any valid JSON string, potentially allowing deserialization of malicious JSON.
   **Risk**: Medium - Could lead to unexpected behavior if JSON contains deeply nested structures.
   **Recommendation**: Add depth limit or size validation:
   ```php
   if (is_string($value)) {
       $decoded = json_decode($value, true, self::RECURSION_LIMIT);  // Add depth limit
       if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
           return $decoded;
       }
   }
   ```

2. **`grep()` - ReDoS Vulnerability (line 1428-1452)**
   ```php
   foreach ($array as $key => $value) {
       set_error_handler(function ($errno, $errstr) use ($pattern) {
           restore_error_handler();
           throw new LogicException("Invalid Pattern: ...");
       });
       try {
           $match = preg_match($pattern, (string)$value) === 1;
       } finally {
           restore_error_handler();
       }
   }
   ```
   **Issue**: The error handler is set and restored inside the loop, which is inefficient. More critically, there's no timeout for regex operations.
   **Risk**: High - ReDoS (Regular Expression Denial of Service) via malicious regex patterns.
   **Recommendation**:
   ```php
   public static function grep(array $array, string $pattern, bool $invert = false): array
   {
       // Validate pattern once before loop
       if (@preg_match($pattern, '') === false) {
           throw new LogicException("Invalid Pattern: ...");
       }

       $result = [];
       foreach ($array as $key => $value) {
           $match = preg_match($pattern, (string)$value) === 1;
           if ($invert !== $match) {
               $result[$key] = $value;
           }
       }
       return $result;
   }
   ```

3. **`denote()` - No recursion limit (line 552-590)**
   ```php
   public static function denote(array $array, bool $strict = false): array
   {
       $result = [];
       foreach ($array as $key => $value) {
           $keys = explode('.', (string) $key);
           // Recursively builds nested structure without depth tracking
       }
       return $result;
   }
   ```
   **Issue**: No protection against deeply nested input or malicious dot-notation keys.
   **Risk**: High - Stack overflow or memory exhaustion with crafted input like `{"a.b.c.d...x": 1}`.
   **Recommendation**: Add depth tracking similar to `flattenToNotation()`.

#### 3.1.2 Medium Priority Issues

1. **`flatten()` - No recursion limit (line 1231-1246)**
   ```php
   public static function flatten(array $array): array
   {
       $result = [];
       foreach ($array as $value) {
           if (is_array($value)) {
               if (!empty($value)) {
                   $result = array_merge($result, self::flatten($value));
               }
           } else {
               $result[] = $value;
           }
       }
       return $result;
   }
   ```
   **Issue**: Recursion without depth tracking.
   **Risk**: Medium - Stack overflow on deeply nested arrays.

2. **`getReference()` - Potential path traversal (line 90-114)**
   ```php
   public static function &getReference(array &$array, string|int|array $key): mixed
   {
       $keys = is_array($key) ? $key : [$key];
       $current = &$array;

       foreach ($keys as $i => $k) {
           if (!array_key_exists($k, $current)) {
               $current[$k] = ($i < count($keys) - 1) ? [] : null;
           }
           // ... rest of implementation
       }
       return $current;
   }
   ```
   **Issue**: No validation of key types. Integer strings vs actual integers could cause issues.
   **Risk**: Low - PHP's array handles this gracefully.

3. **`mapWithKeys()` - Potential key injection (line 2376-2397)**
   ```php
   public static function mapWithKeys(array $array, callable $callback): array
   {
       $result = [];
       foreach ($array as $key => $value) {
           $mapped = $callback($value, $key);
           if ($mapped === null) {
               continue;
           }
           if (!is_array($mapped) || count($mapped) !== 1) {
               throw new InvalidArgumentException("...");
           }
           $result[key($mapped)] = current($mapped);
       }
       return $result;
   }
   ```
   **Issue**: No validation that the key is a valid PHP array key (string or int).
   **Risk**: Low - PHP's array handles invalid keys gracefully.

#### 3.1.3 Low Priority Issues

1. **`crossJoin()` - Memory exhaustion potential (line 494-517)**
   ```php
   public static function crossJoin(array ...$arrays): array
   {
       // No size validation
       $combinations = array_map(fn ($item) => [$item], $arrays[0]);
       for ($i = 1; $i < count($arrays); $i++) {
           $newCombinations = [];
           foreach ($combinations as $combination) {
               foreach ($arrays[$i] as $item) {
                   $newCombinations[] = array_merge($combination, [$item]);
               }
           }
           $combinations = $newCombinations;
       }
       return $combinations;
   }
   ```
   **Issue**: No protection against exponential memory growth.
   **Risk**: Low - Only affects the caller, not system security.

### 3.2 Type Safety Issues

1. **`toArray()` - Type confusion (line 3818-3874)**
   ```php
   // Handle objects
   if ($value instanceof stdClass) {
       return (array) $value;
   }
   ```
   **Issue**: Casting objects to arrays may expose private/protected properties with null bytes.
   **Risk**: Low - Only affects stdClass, not complex objects.

2. **`merge()` - Type juggling with `array_merge_recursive()` (line 2430-2439)**
   ```php
   public static function merge(...$arrays): array
   {
       if (count($arrays) < 2) {
           throw new InvalidArgumentException("...");
       }
       return array_merge_recursive(...$arrays);
   }
   ```
   **Issue**: No type validation of array elements.
   **Risk**: Low - PHP's array_merge_recursive handles this.

3. **`combine()` - Key validation (line 386-403)**
   ```php
   public static function combine(array $keys, array $values): array
   {
       foreach ($keys as $value) {
           if (!is_int($value) && !is_string($value)) {
               throw new InvalidDataTypeException("...");
           }
       }
       return array_combine($keys, $values);
   }
   ```
   ✅ **Good** - Proper validation of key types.

### 3.3 Reference Handling Issues

1. **`prepend()` - Reference not updated correctly (line 2707-2713)**
   ```php
   public static function prepend(array &$array, array $items): void
   {
       if (empty($items)) {
           return;
       }
       $array = $items + $array;  // BUG: This doesn't modify the original array!
   }
   ```
   **Issue**: The reassignment doesn't modify the original array because of PHP's reference handling.
   **Risk**: High - Method doesn't work as intended.
   **Recommendation**:
   ```php
   public static function prepend(array &$array, array $items): void
   {
       if (empty($items)) {
           return;
       }
       $array = $items + $array;
       // Force reference update
       $temp = $array;
       unset($array);
       $array = $temp;
   }
   ```
   Or better:
   ```php
   public static function prepend(array &$array, array $items): void
   {
       if (empty($items)) {
           return;
       }
       foreach (array_reverse($items, true) as $key => $value) {
           array_unshift($array, $value);
           if (is_string($key)) {
               $keys = array_keys($array);
               $keys[0] = $key;
               $array = array_combine($keys, $array);
               // This approach needs rework
           }
       }
   }
   ```

   **Note**: This is a known PHP limitation with references and the `+` operator.

### 3.4 Error Handling Issues

1. **Silent failures in some methods**
   - `associate()` skips null keys silently (line 222-246)
   - `mapWithKeys()` silently skips null returns (line 2376-2397)

   **Recommendation**: Document this behavior clearly.

2. **Inconsistent exception types**
   - Some methods throw `OutOfBoundsException`
   - Some throw `InvalidArgumentException`
   - Some throw `LogicException`
   - Some throw `InvalidDataTypeException`

   **Recommendation**: Establish consistent exception hierarchy.

### 3.5 Recommended Security Fixes

#### Priority 1 (Critical)

1. **Fix `grep()` error handler inefficiency**
   ```php
   public static function grep(array $array, string $pattern, bool $invert = false): array
   {
       // Validate pattern once
       set_error_handler(function ($errno, $errstr) use ($pattern) {
           restore_error_handler();
           throw new LogicException("Invalid Pattern: ...");
       });

       $testResult = @preg_match($pattern, '');
       restore_error_handler();

       if ($testResult === false) {
           throw new LogicException("Invalid Pattern: ...");
       }

       $result = [];
       foreach ($array as $key => $value) {
           $match = preg_match($pattern, (string)$value) === 1;
           if ($invert !== $match) {
               $result[$key] = $value;
           }
       }
       return $result;
   }
   ```

2. **Add recursion limit to `flatten()`**
   ```php
   public static function flatten(array $array, int $depth = 0): array
   {
       if ($depth >= self::RECURSION_LIMIT) {
           throw new LogicException("Recursion limit exceeded");
       }
       $result = [];
       foreach ($array as $value) {
           if (is_array($value)) {
               if (!empty($value)) {
                   foreach (self::flatten($value, $depth + 1) as $v) {
                       $result[] = $v;
                   }
               }
           } else {
               $result[] = $value;
           }
       }
       return $result;
   }
   ```

3. **Add recursion limit to `denote()`**
   ```php
   public static function denote(array $array, bool $strict = false, int $depth = 0): array
   {
       if ($depth >= self::RECURSION_LIMIT) {
           throw new LogicException("Recursion limit exceeded");
       }
       // ... rest of implementation with recursive call: self::denote($value, $strict, $depth + 1)
   }
   ```

#### Priority 2 (High)

1. **Add JSON depth limit to `toArray()`**
   ```php
   $decoded = json_decode($value, true, self::RECURSION_LIMIT);
   ```

2. **Fix `prepend()` reference issue**
   - Document the limitation
   - Or implement using `array_unshift()` loop

3. **Add input size validation to `crossJoin()`**
   ```php
   public static function crossJoin(array ...$arrays): array
   {
       if (count($arrays) < 2) {
           throw new InvalidArgumentException("...");
       }

       // Calculate potential result size
       $size = 1;
       foreach ($arrays as $arr) {
           $size *= count($arr);
           if ($size > 1000000) {  // 1 million element limit
               throw new LogicException("Cross join would produce too many elements");
           }
       }

       // ... rest of implementation
   }
   ```

#### Priority 3 (Medium)

1. **Consistent exception hierarchy**
2. **Document silent failures**
3. **Add type hints to all callback parameters**
4. **Add return type hints to all methods** (already done - good!)

### 3.6 Security Best Practices Followed

1. ✅ **Strict types declared** - `declare(strict_types=1);`
2. ✅ **Return type hints** on all public methods
3. ✅ **Parameter type hints** on all public methods
4. ✅ **Recursion limit** in most recursive methods
5. ✅ **Input validation** in many methods
6. ✅ **Immutable by default** - Most operations return new arrays
7. ✅ **Reference methods clearly documented**

---

## Summary

### Functionality Score: 8/10

**Strengths:**
- Comprehensive coverage of array operations
- Unique features not found in competitors (notation/denote, normalize, smart toObject)
- Fluent interface with `Type\Arrays` wrapper
- Good method naming consistency

**Gaps:**
- Missing `groupBy()` - critical for data aggregation
- Missing `partition()` - useful for splitting arrays
- Missing `where()` variants - limited filtering options
- Missing `sortBy()` with multiple columns

### Performance Score: 7/10

**Strengths:**
- Extensive use of native PHP functions (optimal)
- O(1) operations properly implemented
- Short-circuit evaluation in `every()`/`some()`
- Reference operations for memory efficiency

**Concerns:**
- `flatten()` uses inefficient `array_merge()` in loop
- `insertAfter()`/`insertBefore()` rebuild entire arrays
- `rename()` rebuilds array to preserve key order
- Missing recursion limits in `flatten()` and `denote()`
- `crossJoin()` has no protection against exponential growth

### Security Score: 7/10

**Strengths:**
- Strict types enabled
- Type hints on all methods
- Input validation in many methods
- Recursion limits in most recursive methods

**Concerns:**
- `grep()` error handler set in loop (inefficient)
- No recursion limit in `flatten()` and `denote()`
- `prepend()` reference bug (doesn't modify original array)
- `toArray()` accepts any JSON without depth limit
- `crossJoin()` vulnerable to memory exhaustion

### Overall Assessment

The `Arrays` class is a well-designed, comprehensive utility library that rivals major framework implementations. The recent removal of Nette Utils dependencies improves maintainability without sacrificing functionality. The main areas for improvement are:

1. **Security**: Add recursion limits to remaining recursive methods
2. **Performance**: Optimize `flatten()` and `insert*()` methods
3. **Functionality**: Add `groupBy()` and `partition()` methods

### Recommended Action Plan

1. **Immediate** (Security):
   - Fix `prepend()` reference bug
   - Add recursion limits to `flatten()` and `denote()`
   - Fix `grep()` error handler placement

2. **Short-term** (Performance):
   - Optimize `flatten()` implementation
   - Add size validation to `crossJoin()`

3. **Medium-term** (Functionality):
   - Add `groupBy()` method
   - Add `partition()` method
   - Add `where()` variants

4. **Long-term** (Enhancement):
   - Add lazy evaluation support
   - Add statistical methods (average, median)
   - Consider generator-based implementations for large datasets

---

**Report Generated**: 2025-02-04
**Analyst**: Claude Opus 4.5
**Repository**: Phuture Coherence
**Branch**: feature/arrays
