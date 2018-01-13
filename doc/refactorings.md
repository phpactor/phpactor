Refactorings
============

- [Add Missing Assignments](#add-missing-assignments)
- [Class Move](#class-move)
- [Complete Constructor](#complete-constructor)
- [Extract Constant](#extract-constant)
- [Extract Interface](#extract-interface)
- [Generate Accessor](#generate-accessor)
- [Generate Method](#generate-method)
- [Implement Contracts](#implement-contracts)
- [Override Method](#override-method)
- [Rename Variable](#rename-variable)

Add Missing Assignments
-----------------------

Automatically add any missing properties to a class.

- **Command**: `$ phpactor class:transform /path/to/class.php --transform=add_missing_assignments`
- **VIM Context Menu**: _Class context menu > Implement Contracts_.
- **VIM Command**: `call phpactor#Transform()`

### Motivation

When authoring a class it is redundant effort to add a property and documentation tag when making an assignment. This
refactoring will scan for any assignments which have not been assigned, and add a property, inferring the type if possible.

### Before and After

```php
<?php

class AcmeBlogTest extends TestCase
{
    public function setUp()
    {
        $this->blog = new Blog();
    }
}
```

Becomes:

```php
<?php

class AcmeBlogTest extends TestCase
{
    /**
     * @var Blog
     */
    private $blog;

    public function setUp()
    {
        $this->blog = new Blog();
    }
}
```

Class Move
----------

Move a class (or folder containing classes) from one location to another.

- **Command**: `$ phpactor class:move path/to/ClassA.php path/to/ClassB.php` (class FQN also accepted).
- **VIM context menu**: _Class context menu > Move Class_
- **VIM function**: `call phpactor#MoveFile()`

### Motivation

When authoring classes, it is often difficult to determine really appropriate
names and namespaces, this is unfortunate as a class name can quickly propagate
through your code, making the class name harder to change as time goes on.

This problem is multiplied if you have chosen an incorrect namespace.

This refactoring will move either a class, class-containing-file or folder to a
new location, updating the classes namespace and all references to that class
where possible.

<div class="alert alert-warning">
This is a dangerous refactoring! Ensure that you commit your work before
executing it and be aware that success is not guaranteed (e.g. class references
in non-PHP files or docblocks are not currently updated).
</div>

### Before and After

```php
# src/Blog/Post.php
<?php

namespace Acme\Blog;

class Post
{
}
```

After moving to `src/Writer.php`:

```php
# src/Writer/Page.php
<?php

namespace Writer\Page;

class Page
{
}
```

Complete Constructor
--------------------

Complete the assignments and add properties for an incomplete constructor.

- **Command**: `$ phpactor class:transform path/to/class.php --transform=complete_constructor`
- **VIM plugin**: _Class context menu > Complete Constructor_.
- **VIM function**: `call phpactor#Transform()`

### Motivation

When authoring a new class, it is often required to:

1. Create a constructor method with typed arguments.
2. Assign the arguments to class properties.
3. Create the class properties with docblocks.

This refactoring will automatically take care of 2 and 3.

### Before and After

```php
<?php

class Post
{
	public function __construct(Hello $hello, Goodbye $goodbye)
	{
	}
}
```

After moving to `src/Writer.php`:

```php
<?php

class Post
{
    /**
     * @var Hello
     */
    private $hello;

    /**
     * @var Goodbye
     */
    private $goodbye;

	public function __construct(Hello $hello, Goodbye $goodbye)
	{
        $this->hello = $hello;
        $this->goodbye = $goodbye;
	}
}
```

Extract Constant
----------------

Extract a constant from a scalar value.

- **Command**: _RPC only_
- **VIM plugin**: _Symbol context menu > Extract Constant_.
- **VIM function**: `call phpactor#ContextMenu()`

### Motivation

Each time a value is duplicated in a class a fairy dies. Duplicated values
increase the fragility of your code. Replacing them with a constant ensures
runtime integrity.

This refactoring includes _Replace Magic Number with Symbolic Constant (204)_
(Fowler, Refactoring).

### Before and After

Cursor position shown as `<>`:

```php
<?php

class DecisionMaker
{
	public function yesOrNo($arg)
	{
        if ($arg == 'y<>es') {
            return true;
		}

        return false;
	}

	public function yes()
	{
        return 'yes';
	}
}
```

After:

```php
<?php

class DecisionMaker
{
    const YES = 'yes';

	public function yesOrNo($arg)
	{
        if ($arg == self::YES) {
            return true;
		}

        return false;
	}

	public function yes()
	{
        return self::YES;
	}
}
```

Extract Interface
-----------------

Extract an interface from a class. If a wildcard is given (CLI only) generate an interface per class.

- **Command**: `$ phpactor class:inflect path/to/Class.php path/to/Interface.php` (wild card accepted).
- **VIM plugin**: _Class context menu > Inflect > Extract interface_.
- **VIM function**: `call phpactor#ClassInflect()`

### Motivation

It is sometimes unwise to preemptively create interfaces for all your classes,
as doing so adds maintainance overhead, and the interfaces may never be needed.

This refactoring allows you to generate an interface from an existing class. All public methods
will be added to generated interface.

### Before and After

```php
<?php

class Foobar
{
    public function foobar(string $bar): Barfoo
	{
	}
}
```

Generated interface (suffix added for illustration):

```php
<?php

interface FoobarInterface
{
    public function foobar(string $bar): Barfoo;
}
```

Generate Accessor
-----------------

Generate an accessor for a class property.

- **Command**: _RPC only_
- **VIM plugin**: _Property context menu > Generate accessor_.
- **VIM function**: `call phpactor#ContextMenu()`

### Motivation

When creating entities and value objects it is frequently necessary to add accessors.

This refactoring automates the generation of accessors.

### Before and After

Cursor position shown as `<>`:

```php
<?php

class Foobar
{
    /**
     * @var Barfoo
     */
    private $bar<>foo;
}
```

Generated interface (suffix added for illustration):

```php
<?php

interface FoobarInterface
{
    /**
     * @var Barfoo
     */
    private $barfoo;

    public function barfoo(): Barfoo
	{
        return $this->barfoo;
	}
}
```

<div class="alert alert-primary">
Note the accessor template can be customized see [Templates](templates.md).
</div>

Generate Method
---------------

Generate or update a method based on the method call under the cursor.

- **Command**: _RPC only_
- **VIM plugin**: _Method context menu > Generate method_.
- **VIM function**: `call phpactor#ContextMenu()`

### Motivation

When initially authoring a package you will often write a method call which
doesn't exist and then add the method to the corresponding class.

This refactoring will automatically generate the method inferring any type
information that it can.

### Before and After

Cursor position shown as `<>`:

```php
<?php

class Foobar
{
    /**
     * @var Barfoo
     */
    private $barfoo;

    // ...

    public function hello(Hello $hello)
    {
         $this->barfoo->good<>bye($hello);
    }
}

class Barfoo
{
}
```

After generating the method:

```php
<?php

class Foobar
{
    /**
     * @var Barfoo
     */
    private $barfoo;

    // ...

    public function hello(Hello $hello)
    {
         $this->barfoo->good<>bye($hello);
    }
}

class Barfoo
{
    public function goodbye(Hello $hello)
    {
    }
}
```

Implement Contracts
-------------------

Add any not implemented methods from interfaces or abstract classes.

- **Command**: `$ phpactor class:transform /path/to/class.php --transform=implement_contracts`
- **VIM plugin**: _Class context menu > Transform > Implement contracts_.
- **VIM function**: `call phpactor#Transform()`

### Motivation

It can be very tiresome to manually implement contracts for interfaces,
especially interfaces with many methods (e.g. `ArrayAccess`).

This refactoring will automatically add the required methods to your class. If
the interface uses any foreign classes, the necessary `use` statements will
also be added.

### Before and After

```php
<?php

class Foobar implements Countable
{
}

class Barfoo
{
}
```

After:

```php
<?php

class Foobar implements Countable
{
    public function count()
    {
    }
}
```

Override Method
---------------

Ovvride a method from a parent class.

- **Command**: _RPC only_
- **VIM plugin**: _Class context menu > Override method_.
- **VIM function**: `call phpactor#ContextMenu()`

### Motivation

Sometimes it is expected or necessary that you override a parent classes method
(for example when authoring a Symfony Command class).

This refactoring will allow you to select a method to override and generate that method
in your class.

### Before and After

```php
<?php

use Symfony\Component\Console\Command\Command;

class MyCommand extends Command
{
}
```

Override method `execute`:

```php
<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MyCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
	{
	}
}
```
