Generics
========

- Resolve iterable type
- Resolve method type
- Accept input

## Iterable

Given:

```php
/**
 * @implements IteratorAggregate<int, Foobar>
 */
class Foobar {
}

foreach ($foobar as $bar) {
}
```

- Phpactor will call `resolveIterableValue` on the class type
- **Resolve template map for** `Traversable`:
    1. Foreach implement/extends reference
    2. Map any template vars (e.g. if `TKey` were a parameter `implement IteratorAggregate<TKey,int>` => `IteratoeAggregate<int,int>`)
    3. Is referenced class `Traversable`? Return template var map
    4. Switch to referenced class
    5. Goto 1
- Return type for `TValue` from template var map

## Method type

Given:

```php
/** @template T */
class Collection { /** @return T */public function foo() {} }

/** @extends Collection<int> */
class Bar {}

$foo = new Bar();
$foo = $bar->foo();
```

- **Resolve template map for** method's declaring class `Collection<int>`
- Return type for template var `T` from template map

## Param/Constructor injection

```php
/** @template T */
class Foobar {
    /** @param T $input */
    public function __construct($input) {}
}

$foo = "hello";
$foobar = new Foobar($foo); // Foobar<string>
```

- **Resolve template map for** constructed class `Foobar` - `Map{T:<undefined>}`
- Map parameters to template map `Map{T:"hello"}`
- Resolve new generic type `Foobar<"hello">`
-
## Param/Constructor injection with inheritance

```php
/** @template T */
class Barfoo {
}

/** @extends Barfoo */
class Foobar extends Barfoo {
    /** @param T $input */
    public function __construct($input) {}
}

$foo = "hello";
$foobar = new Foobar($foo); // Foobar<string>
```

- **Resolve template map for** constructed class `Foobar` - `Map{T:<undefined>}`
- Map parameters to template map `Map{T:"hello"}`
- Resolve new generic type `Foobar<"hello">`

## Method Injection

- Method template vars are local to the template.
- Input parameters cannot mutate class-level generics unless the method is the
  constructor.
