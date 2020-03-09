---
currentMenu: refactorings
---
Refactorings
============

Fixes:

- [Add Missing Assignments](#add-missing-assignments)
- [Complete Constructor](#complete-constructor)
- [Fix namespace and/or class name](#fix-namespace-or-class-name)
- [Generate Accessors](#generate-accessors)
- [Generate Method](#generate-method)
- [Implement Contracts](#implement-contracts)
- [Import Class](#import-class)
- [Expand Class](#expand-class)
- [Import Missing Classes](#import-missing-classes)
- [Override Method](#override-method)

Generation:

- [Class New](#class-new)
- [Class Copy](#class-copy)
- [Extract Interface](#extract-interface)

Refactoring:

- [Change Visibility](#change-visibility)
- [Class Move](#class-move)
- [Extract Constant](#extract-constant)
- [Extract Expression](#extract-expression)
- [Extract Method](#extract-method)
- [Rename Class](#rename-class)
- [Rename Class Member](#rename-class-member)
- [Rename Variable](#rename-variable)

Add Missing Assignments
-----------------------

Automatically add any missing properties to a class.

- **Command**: `$ phpactor class:transform path/to/Class.php --transform=add_missing_assignments`
- **VIM Context Menu**: _Class context menu > Transform > Add missing properties_.
- **VIM Command**:`:PhpactorTransform`

### Motivation

When authoring a class it is redundant effort to add a property and documentation tag when making an assignment. This
refactoring will scan for any assignments which have do not have corresponding properties and add the required properties with docblocks based on inferred types where possible.

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

Class Copy
----------

Copy an existing class to another location updating its name and namespace.

- **Command**: `$ phpactor class:copy path/to/ClassA.php path/to/ClassB.php` (class FQN also accepted).
- **VIM context menu**: _Class context menu > Copy Class_
- **VIM function**:`:PhpactorCopyFile`

### Motivation

Sometimes you find that an existing class is a good starting point for a new class. In this situation you may:

1. Copy the class to a new file location.
2. Update the class name and namespace.
3. Adjust the copied class as necessary.

This refactoring performs steps 1 and 2.

### Before and After

```php
# src/Blog/Post.php
<?php

namespace Acme\Blog;

class Post
{
    public function title()
    {
        return 'Hello';
	}
}
```

After moving to `src/Cms/Article.php`:

```php
# src/Cms/Article.php
<?php

namespace Acme\Cms;

class Article
{
    public function title()
    {
        return 'Hello';
	}
}
```

Change Visibility
-----------------

Change the visibility of a class member

- **Command**: __RPC only__
- **VIM context menu**: _Class member context menu > Change Visiblity_
- **VIM function**:`:PhpactorChangeVisibility`

Currently this will cycle through the 3 visibilities: `public`, `protected` and
`private`.

### Motivation

Sometimes you may want to extract a class from an existing class in order to
isolate some of it's responsibility. When doing this you may:

1. Create a new class using [Class New](#class-new).
2. Copy the method(s) which you want to extract to the new class.
3. Change the visibility of the main method from `private` to `public`.

### Before and After

Cursor position shown as `<>`:

```php
# src/Blog/FoobarResolver.php
<?php

namespace Acme\Blog;

class FoobarResolver
{
    private function resolveFoobar()
    {
        <>
    }
}
```

After invoking "change visibility" on or within the method.

```php
# src/Blog/FoobarResolver.php
<?php

namespace Acme\Blog;

class FoobarResolver
{
    public function resolveFoobar();
    {
    }
}
```

*Note*: This also works on constants and properties. It will NOT change
        the visibility of any related parent or child class members.

Class Move
----------

Move a class (or folder containing classes) from one location to another.

- **Command**: `$ phpactor class:move path/to/ClassA.php path/to/ClassB.php` (class FQN also accepted).
- **VIM context menu**: _Class context menu > Move Class_
- **VIM function**:`:PhpactorMoveFile`

### Motivation

When authoring classes, it is often difficult to determine really appropriate
names and namespaces, this is unfortunate as a class name can quickly propagate
through your code, making the class name harder to change as time goes on.

This problem is multiplied if you have chosen an incorrect namespace.

This refactoring will move either a class, class-containing-file or folder to a
new location, updating the classes namespace and all references to that class
where possible in a given _scope_ (i.e. files known by GIT: `git`, files known by Composer: `composer`, or all PHP files under
the current CWD: `simple`).

If you have defined file relationships with
[navigator.destinations](https://phpactor.github.io/phpactor/navigation.html#jump-to-or-generate-related-file), then you have the option to move the related files in addition to the specified file. If using the command then specify `--related`, or if using the RPC interface (f.e. VIM) you will be prompted.

<div class="alert alert-danger">
This is a dangerous refactoring! Ensure that you commit your work before
executing it and be aware that success is not guaranteed (e.g. class references
in non-PHP files or docblocks are not currently updated).

This refactoring works best when you have a well tested code base.
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
# src/Writer.php
<?php

namespace Acme;

class Writer
{
}
```

Class New
---------

Generate a new class with a name and namespace at a given location or from a class name.

- **Command**: `$ phpactor class:new path/To/ClassName.php` (class FQN also accepted).
- **VIM context menu**: _Class context menu > New Class_
- **VIM function**:`:PhpactorClassNew`

### Motivation

Creating classes is one of the most general actions we perform:

1. Create a new file.
2. Code the namespace, ensuring that it is compatible with the autoloading scheme.
3. Code the class name, ensuring that it is the same as the file name.

This refactoring will perform steps 1, 2 and 3 for:

- Any given file name.
- Any given class name.
- A class name under the cursor.

It is also possible to choose a class template, see [templates](templates.md)
for more information.

### Before and After

<div class="alert alert-success">
This example is from an existing, empty, file. Note that you can also use
the context menu to generate classes from non-existing class names in the current
file
</div>

Given a new file:

```php
# src/Blog/Post.php
```

After invoking _class new_ using the `default` variant:

```php
<?php

namespace Acme/Blog;

class Post
{
}
```

Complete Constructor
--------------------

Complete the assignments and add properties for an incomplete constructor.

- **Command**: `$ phpactor class:transform path/to/class.php --transform=complete_constructor`
- **VIM plugin**: _Class context menu > Transform > Complete Constructor_.
- **VIM function**:`:PhpactorTransform`

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

After:

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

Fix Namespace or Class Name
---------------------------

Update a file's namespace (and/or class name) based on the composer
configuration.

- **Command**: `$ phpactor class:transform path/to/class.php --transform=fix_namespace_class_name`
- **VIM plugin**: _Class context menu > Transform > Fix namespace or classname_.
- **VIM function**: `:PhpactorTransform`

<div class="alert alert-warning">
This refactoring will currently only work fully on Composer based projects.
</div>

### Motivation

Phpactor already has the possibility of generating new classes, and moving
classes. But sometimes your project may get into a state where
class-containing files have an incorrect namespace or class name.

This refactoring will:

- Update the namespace based on the file path (and the autoloading config).
- Update the class name.
- When given an empty file, it will generate a PHP tag and the namespace.

### Before and After

```php
// lib/Barfoo/Hello.php
<?php

class Foobar
{
    public function hello()
    {
        echo 'hello';
    }
}
```

After:

```php
// lib/Barfoo/Hello.php
<?php

namespace Barfoo;

class Hello
{
    public function hello()
    {
        echo 'hello';
    }
}
```

Extract Constant
----------------

Extract a constant from a scalar value.

- **Command**: _RPC only_
- **VIM plugin**: _Symbol context menu > Extract Constant_.
- **VIM function**:`:PhpactorContextMenu`

### Motivation

Each time a value is duplicated in a class a fairy dies. Duplicated values
increase the fragility of your code. Replacing them with a constant helps to
ensure runtime integrity.

This refactoring includes [Replace Magic Number with Symbolic
Constant](https://refactoring.com/catalog/replaceMagicNumberWithSymbolicConstant.html)
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

Extract Expression
------------------

Extract an expression

- **Command**: _VIM function only_
- **VIM plugin**: _VIM function only_
- **VIM function**:`:PhpactorExtractExpression` (call with
  `v:true` to invoke on a selection)

### Motivation

Sometimes you find yourself using inline expressions, and later you realise
that you want to re-use that value.

### Before and After

Cursor position shown as `<>`:

```php
<?php

if (<>1 + 2 + 3 + 5 === 6) {
    echo 'You win!';
}
```

After (entering `$hasWon` as a variable name):

```php
<?php

$hasWon = 1 + 2 + 3 + 5 === 6;
if ($hasWon) {
    echo 'You win!';
}
```

Note that this also works with a visual selection if you invoke the VIM
function with `v:true`:

```php
<?php

if (<>1 + 2 + 3 + 5<> === 6) {
    echo 'You win!';
}
```

After (using `$winningCombination` as a variable name):

```php
<?php

$winningCombination = 1 + 2 + 3 + 5;
if ($winningCombination == 6) {
    echo 'You win!';
}
```

Extract Method
--------------

Extract a method from a selection.

- **Command**: _RPC only_
- **VIM plugin**: _Function only_
- **VIM function**:`:PhpactorExtractMethod`

This refactoring is NOT currently available through the context menu. You
will need to [map it to a keyboard shortcut](vim-plugin.md#keyboard-mappings)
or invoke it manually.

### Motivation

This is one of the most common refactorings. Decomposing code into discrete
methods helps to make code understandable and maintainable.

Extracting a method manually involes:

1. Creating a new method
2. Moving the relevant block of code to that method.
3. Scanning the code for variables which are from the original code.
4. Adding these variables as parameters to your new method.
5. Calling the new method in place of the moved code.

This refactoring takes care of steps 1 through 5 and:

- If a _single_ variable that is declared in the selection which is used in the parent
  scope, it will be returned.
- If _multiple_ variables are used, the extracted method will return a tuple.
- In both cases the variable(s) will be assigned at the point of extraction.
- Any class parameters which are not already imported, will be imported.

### Before and After

Selection shown between the two `<>` markers:

```php
<?php

class extractMethod
{
    public function bigMethod()
    {
        $foobar = 'yes';

        <>
        if ($foobar) {
            return 'yes';
        }

        return $foobar;
        <>

    }
}
```

After extracting method `newMethod`:

```php
<?php

class extractMethod
{
    public function bigMethod()
    {
        $foobar = 'yes';

        $this->newMethod($foobar);

    }

    private function newMethod(string $foobar)
    {
        if ($foobar) {
            return 'yes';
        }
        
        return $foobar;
    }
}
```

Extract Interface
-----------------

Extract an interface from a class. If a wildcard is given (CLI only) generate an interface per class.

- **Command**: `$ phpactor class:inflect path/to/Class.php path/to/Interface.php` (wild card accepted).
- **VIM plugin**: _Class context menu > Inflect > Extract interface_.
- **VIM function**:`:PhpactorClassInflect`

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

Generate Accessors
------------------

Generate accessors for a class.

- **Command**: _RPC only_
- **VIM plugin**: _Class context menu > Generate accessor_.
- **VIM function**:`:PhpactorGenerateAccessor`

### Motivation

When creating entities and value objects it is frequently necessary to add accessors.

This refactoring automates the generation of accessors.

### Before and After

Cursor position shown as `<>`:

```php
<?php

class Foo<>bar
{
    /**
     * @var Barfoo
     */
    private $barfoo;
}
```

After selecting [one or more accessors](/vim-plugin.html#fzf-multi-selection)

```php
<?php

class Foobar
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

Note the accessor template can be customized see [Templates](templates.md).

Generate Method
---------------

Generate or update a method based on the method call under the cursor.

- **Command**: _RPC only_
- **VIM plugin**: _Method context menu > Generate method_.
- **VIM function**:`:PhpactorContextMenu`

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
         $this->barfoo->goodbye($hello);
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

Add any non-implemented methods from interfaces or abstract classes.

- **Command**: `$ phpactor class:transform /path/to/class.php --transform=implement_contracts`
- **VIM plugin**: _Class context menu > Transform > Implement contracts_.
- **VIM function**:`:PhpactorTransform`

### Motivation

It can be very tiresome to manually implement contracts for interfaces and abstract classes,
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

Import Class
------------

Import a class into the current namespace based on the class name under the cursor.

- **Command**: _VIM function only_
- **VIM plugin**: _VIM function only_
- **VIM function**:`:PhpactorUseAdd`

### Motivation

It is easy to remember the name of a class, but more difficult to remember its
namespace, and certainly it is time consuming to manually code class imports:

Manually one would:

1. Perform a fuzzy search for the class by its short name.
2. Identify the class you want to import.
3. Copy the namespace.
4. Paste it into your current file
5. Add the class name to the new `use` statement.

This refactoring covers steps 1, 3, 4 and 5.

### Before and After

Cursor position shown as `<>`:

```php
<?php

class Hello
{
    public function index(Re<>quest $request)
    {
	}

}
```

After selecting `Symfony\Component\HttpFoundation\Request` from the list of candidates:

```php
<?php

use Symfony\Component\HttpFoundation\Request;

class Hello
{
    public function index(Request $request)
    {
	}
}
```

Expand Class
------------

Expand the class name from unqualified name to fully qualified name.

- **Command**: _VIM function only_
- **VIM plugin**: _VIM function only_
- **VIM function**:`:PhpactorClassExpand`

### Motivation

Although importing classes can make code cleaner, sometimes the code can be more
readable if the fully qualified name is specified. For example, we might register
a list of listeners in a file.

### Before and After

Cursor position shown as `<>`:

```php
<?php

namespace App\Event;

class UserCreatedEvent
{
    protected $listenrs = [
        AssignDefaultRole<>ToNewUser::class
    ];
}
```

After selecting `App\Listeners\AssignDefaultRoleToNewUser` from the list of candidates:

```php
<?php

namespace App\Event;

class UserCreatedEvent
{
    protected $listenrs = [
        \App\Listeners\AssignDefaultRoleToNewUser::class
    ];
}
```

Import Missing Classes
----------------------

Import all missing classes in the current file.

- **Command**: **RPC Only**
- **VIM plugin**: _Class context menu > Import Missing_
- **VIM function**:`:PhpactorImportMissingClasses`

### Motivation

You may copy and paste some code from one file to another and subsequently
need to import all the foreign classes into the current namespace. This
refactoring will identify all unresolvable classes and import them.

Override Method
---------------

Overide a method from a parent class.

- **Command**: _RPC only_
- **VIM plugin**: _Class context menu > Override method_.
- **VIM function**:`:PhpactorContextMenu`
- **Multiple selection**: Supports selecting multiple methods.

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

Rename Variable
---------------

Rename a variable in the local or class scope.

- **Command**: _RPC only_
- **VIM plugin**: _Variable context menu > Rename_.
- **VIM function**:`:PhpactorContextMenu`

### Motivation

Having meaningful and descriptive variable names makes the intention of
code clearer and therefore easier to maintain. Renaming variables is a frequent
refactoring, but doing this with a simple search and replace can often have unintended
consequences (e.g. renaming the variable `$class` also changes the `class` keyword).

This refactoring will rename a variable, and only variables, in either the method scope
or the class scope.

### Before and After

Cursor position shown as `<>`:

```php
<?php

class Hello
{
    public function say(array $hell<>os)
    {
        foreach ($hellos as $greeting) {
            echo $greeting;
		}

        return $hellos;
	}

}
```

Rename the variable `$hellos` to `$foobars` in the local scope:

```php
<?php

class Hello
{
    public function say(array $foobars)
    {
        foreach ($foobars as $greeting) {
            echo $greeting;
		}

        return $foobars;
	}

}
```

Rename Class
------------

Rename a class.

- **Command**: `$ phpactor references:class path/to/Class.php --replace="NewName"` (class FQN accepted)
- **VIM plugin**: _Class context menu > Replace references_.
- **VIM function**:`:PhpactorContextMenu`

### Motivation

This refactoring is _similar_ to [Move Class](#class-move), but without
renaming the file. This is a useful refactoring when a dependant library has
changed a class name and you need to update that class name in your project.

### Before and After

Cursor position shown as `<>`:

```php
<?php

class Hel<>lo
{
    public function say()
    {
        
	}

}

$hello = new Hello();
$hello->say();
```

Rename `Hello` to `Goodbye`

```php
<?php

class Goodbye
{
    public function say()
    {
        
	}

}

$hello = new Goodbye();
$hello->say();
```

<div class="alert alert-danger">
When renaming classes in your project use <a href="#class-move">Class Move</a>.
</div>

Rename Class Member
-------------------

Rename a class member.

- **Command**: `$ phpactor references:member path/to/Class.php memberName --type="method" --replace="newMemberName"` (FQN accepted)
- **VIM plugin**: _Member context menu > Replace references_.
- **VIM function**:`:PhpactorContextMenu`

### Motivation

Having an API which is expressive of the intent of the class is important, and
contributes to making your code more consistent and maintainable.

When renaming members global search and replace can be used, but is a shotgun approach and
you may end up replacing many things you did not mean to replace (e.g. imagine renaming the method `name()`).

This refactoring will:

1. Scan for files in your project which contain the member name text.
2. Parse all of the candidate files.
3. Identify the members, and try and identify the containing class.
4. Replace only the members which certainly belong to the target class.

When replacing _private_ and _protected_ members, only the related classes
will be updated.

Due to the loosely typed nature of PHP this refactoring may not find all of
the member accesses for the given class. Run your tests before and after
applying this refactoring.

<div class="alert alert-info">
<b>Hint</b>: Use the CLI command to list all of the <b>risky</b> references. Risky references are those member accesses which match
the query but whose containing classes could not be resolved.
</div>

![Risky references](images/risky.png)

### Before and After

Cursor position shown as `<>`:

```php
<?php

class Hello
{
    public function sa<>y()
    {
        
	}

}

$hello = new Hello();
$hello->say();
```

Rename `Hello#say()` to `Hello#speak()`

```php
<?php

class Hello
{
    public function speak()
    {
        
	}

}

$hello = new Hello();
$hello->speak();
```
