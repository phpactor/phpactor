.. _completion:

Completion
==========

Phpactor provides completion for:

- **Class names**: All PSR compliant classes in the project and vendor tree.
- **Class members**: Methods, constants, properties of auto-loadable classes.
- **Functions**: Built-in and bootstrapped.
- **Constants**: Built-in and bootstrapped.
- **Parameters**: Will suggest appropriate local variables for method parameters.
- **Array Keys**: For array-shapes (`array{key1:value1}`) complete the keys.

Uniquely, Phpactor does not pre-index anything, completion happens in _real
time_, file locations are guessed based on composer locations (or brute forced
if not using composer). For non-autoloadable entities (e.g. functions) it is
assumed that these are defined during bootstrap.

Type inference
--------------

Phpactors type inference is based on
[WorseReflection](https://github.com/phpactor/worse-reflection).

### Assert

When encountering an `assert` with `instanceof` it will cast the variable
to that type, or a union of that type. See also [#instanceof](#instanceof).

```php
assert($foo instanceof Hello);
assert($foo instanceof Hello || $foo instanceof Goodbye)

$foo-> // type: Hello|Goodbye
```

### Assignments

Phpactor will track assignments:

```php
$a = 'hello';
$b = $a;
$b; // type: string
```

... and assignments from method calls, class properties, anything reflectable, etc.

### Catch

```php

try {
   // something
} catch (MyException $e) {
    $e-> // type: MyException
}
```

### Foreach

Understands `foreach` with the docblock array annotation:

```php
/** @var Hello[] $foos */
$foos = [];

foreach ($foos as $foo) {
    $foo-> // type:Hello
}
```

Also understands simple generics:

```php
/** @var ArrayIterator<Hello> $foos */
$foos = new ArrayIterator([ new Hello() ]);

foreach ($foos as $foo) {
    $foo-> // type:Hello
}
```

### FunctionLike

Understands anonymous functions:

```php
$barfoo = new Barfoo();
$function = function (Foobar $foobar) use ($barfoo) {
    $foobar-> // type: Foobar
    $barfoo-> // type: Barfoo
}
```

### InstanceOf

`if` statements are evaluated, if they contain `instanceof` then the type is
inferred:

```php
if ($foobar instanceof Hello) {
    $foobar-> // type: Hello
}
```

```php
if (false === $foobar instanceof Hello) {
    return;
}

$foobar-> // type: Hello
```

```php
if ($foobar instanceof Hello || $foobar instanceof Goodbye) {
    $foobar-> // type: Hello|Goodbye
}
```

### Variables

Phpactor supports type injection via. docblock:

```php
/** @var Foobar $foobar */
$foobar-> // type: Foobar
```

and inference from parameters:

```php
function foobar(Barfoo $foobar, $barbar = 'foofoo')
{
    $foobar; // type: Barfoo
    $barbar; // type: foofoo
}
```

