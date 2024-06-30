.. _refactoring:

Refactoring
***********

.. contents::
   :depth: 2
   :backlinks: none
   :local:

Fixes
=====

.. _refactoring_add_missing_assignments:

Add Missing Assignments
-----------------------

Automatically add any missing properties to a class.

.. tabs::

   .. tab:: CLI

       .. code-block::

           $ phpactor class:transform path/to/Class.php --transform=add_missing_assignments

   .. tab:: VIM Context Menu

       *Class context menu > Transform > Add missing properties*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorTransform


Motivation
~~~~~~~~~~

When authoring a class it is redundant effort to add a property and
documentation tag when making an assignment. This refactoring will scan
for any assignments which have do not have corresponding properties and
add the required properties with docblocks based on inferred types where
possible.

Before and After
~~~~~~~~~~~~~~~~

.. code:: php

   <?php

   class AcmeBlogTest extends TestCase
   {
       public function setUp()
       {
           $this->blog = new Blog();
       }
   }

Becomes:

.. code:: php

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

.. _refactoring_add_missing_docblock:

Add Missing Docblock
--------------------

This refactoring will add docblocks:

- If there is an array return type and an iterator value can be inferred from
  the function's return statement.
- If there is an class return type and a generic type can be inferred from the
  function's return statement.

.. tabs::

   .. tab:: CLI

       .. code-block::

           $ phpactor class:transform path/to/Class.php --transform=add_missing_docblocks

   .. tab:: VIM Context Menu

       *Class context menu > Transform > Add missing docblocks*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorTransform

   .. tab:: Language Server

       Request code actions when there is a candidate

.. _refactoring_complete_constructor:

Add Missing Return Types
------------------------

This refactoring add missing return types.

.. tabs::

   .. tab:: CLI

       .. code-block::

           $ phpactor class:transform path/to/Class.php --transform=add_missing_return_types

   .. tab:: VIM Context Menu

       *Class context menu > Transform > Add missing return types*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorTransform

   .. tab:: Language Server

       Request code actions when there is a candidate

Complete Constructor
--------------------

Complete the assignments and add properties for an incomplete
constructor.

.. tabs::

   .. tab:: CLI

       .. code-block::

           $ phpactor class:transform path/to/class.php --transform=complete_constructor

   .. tab:: VIM Context Menu

       *Class context menu > Transform > Complete  Constructor*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorTransform


.. _motivation-5:

Motivation
~~~~~~~~~~

When authoring a new class, it is often required to:

1. Create a constructor method with typed arguments.
2. Assign the arguments to class properties.
3. Create the class properties with docblocks.

This refactoring will automatically take care of 2 and 3.

.. _before-and-after-5:

Before and After
~~~~~~~~~~~~~~~~

.. code:: php

   <?php

   class Post
   {
       public function __construct(Hello $hello, Goodbye $goodbye)
       {
       }
   }

After:

.. code:: php

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

.. _refactoring_fix_namespace_and_class:

Fix Namespace or Class Name
---------------------------

Update a file’s namespace (and/or class name) based on the composer
configuration.

.. tabs::

   .. tab:: CLI

       .. code-block::

           $ phpactor class:transform path/to/class.php --transform=fix_namespace_class_name

   .. tab:: VIM Context Menu

       *Class context menu > Transform > Fix namespace or class name*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorTransform

.. warning::

   This refactoring will currently only work fully on Composer based
   projects.

.. _motivation-6:

Motivation
~~~~~~~~~~

Phpactor already has the possibility of generating new classes, and
moving classes. But sometimes your project may get into a state where
class-containing files have an incorrect namespace or class name.

This refactoring will:

-  Update the namespace based on the file path (and the autoloading
   config).
-  Update the class name.
-  When given an empty file, it will generate a PHP tag and the
   namespace.

.. _before-and-after-6:

Before and After
~~~~~~~~~~~~~~~~

.. code:: php

   // lib/Barfoo/Hello.php
   <?php

   class Foobar
   {
       public function hello()
       {
           echo 'hello';
       }
   }

After:

.. code:: php

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

.. _generation_generate_accessors:

Generate Accessors
------------------

Generate accessors for a class.

.. tabs::

   .. tab:: Language Server

       Select a range and request code actions

   .. tab:: VIM Context Menu

       *Class context menu > Generate accessor*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorGenerateAccessor


.. _motivation-11:

Motivation
~~~~~~~~~~

When creating entities and value objects it is frequently necessary to
add accessors.

This refactoring automates the generation of accessors.

.. _before-and-after-11:

Before and After
~~~~~~~~~~~~~~~~

Cursor position shown as ``<>``:

.. code:: php

   <?php

   class Foo<>bar
   {
       /**
        * @var Barfoo
        */
       private $barfoo;
   }

After selecting `one or more
accessors </vim-plugin.html#fzf-multi-selection>`__

.. code:: php

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

Note the accessor template can be customized see
`Templates <templates.md>`__.

.. _generation_method:

Generate Method
---------------

Generate or update a method based on the method call under the cursor.

.. tabs::

   .. tab:: CLI

       *RPC only*

   .. tab:: VIM Context Menu

       *Method context menu > Generate method*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorContextMenu


.. _motivation-12:

Motivation
~~~~~~~~~~

When initially authoring a package you will often write a method call
which doesn't exist and then add the method to the corresponding class.

This refactoring will automatically generate the method inferring any
type information that it can.

.. _before-and-after-12:

Before and After
~~~~~~~~~~~~~~~~

Cursor position shown as ``<>``:

.. code:: php

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

After generating the method:

.. code:: php

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

.. _generateo_constructor:

Generate Constructor
--------------------

Generate a constructor from a new object instance expression

.. tabs::

   .. tab:: LSP

      Invoke code action on new class expression for class with no constructor



Before and After
~~~~~~~~~~~~~~~~

Assuming `MyFancyObject` exists and has no constructor.

Cursor position shown as ``<>``:

.. code:: php

   <?php

   use App\MyFancyObject;

   $barfoo = 'barfor?';

   new My<>FancyObject($barfoo, 'foobar', 1234);

After choosing the "Generate Constructor" code action the `MyFancyObject`
class should have a constructor:

.. code:: php

   <?php

   namespace App;

   class MyFancyObject
   {
       public function __construct(string $barfoo, string $string, int $int)
       {
       }
   }


.. _implement_contracts:

Implement Contracts
-------------------

Add any non-implemented methods from interfaces or abstract classes.

.. tabs::

   .. tab:: CLI

       ``$ phpactor class:transform /path/to/class.php --transform=implement_contracts``

   .. tab:: VIM Context Menu

       *Class context menu > Transform > Implement contracts*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorTransform

.. _motivation-13:

Motivation
~~~~~~~~~~

It can be very tiresome to manually implement contracts for interfaces
and abstract classes, especially interfaces with many methods
(e.g. ``ArrayAccess``).

This refactoring will automatically add the required methods to your
class. If the interface uses any foreign classes, the necessary ``use``
statements will also be added.

.. _before-and-after-13:

Before and After
~~~~~~~~~~~~~~~~

.. code:: php

   <?php

   class Foobar implements Countable
   {
   }

After:

.. code:: php

   <?php

   class Foobar implements Countable
   {
       public function count()
       {
       }
   }

.. _generate_decorator:

Generate Decorator
-------------------

Given a skeleton class which implements an interface, add the methods required
to convert that class into a `decorator
<https://en.wikipedia.org/wiki/Decorator_pattern>`_.

.. tabs::

   .. tab:: LSP

       Invoke code action on a class which has implemented no methods and
       implements one or more interfaces.

Before and After
~~~~~~~~~~~~~~~~

.. code:: php

   <?php

   class Foobar implements Countable
   {
   }

After:

.. code:: php

   <?php

   class Foobar implements Countable
   {
       private Countable $innerCounter;

       public function __construct(Countable $innerCounter)
       {
           $this->innerCounter = $innerCounter;
       }

       public function count(): int
       {
           return $this->innerCounter->count();
       }
   }

.. _refactoring_import_missing_class:

Import Class
------------

Import a class into the current namespace based on the class name under
the cursor.

.. tabs::

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorImportClass

.. _motivation-14:

Motivation
~~~~~~~~~~

It is easy to remember the name of a class, but more difficult to
remember its namespace, and certainly it is time consuming to manually
code class imports:

Manually one would:

1. Perform a fuzzy search for the class by its short name.
2. Identify the class you want to import.
3. Copy the namespace.
4. Paste it into your current file
5. Add the class name to the new ``use`` statement.

This refactoring covers steps 1, 3, 4 and 5.

.. _before-and-after-14:

Before and After
~~~~~~~~~~~~~~~~

Cursor position shown as ``<>``:

.. code:: php

   <?php

   class Hello
   {
       public function index(Re<>quest $request)
       {
       }

   }

After selecting ``Symfony\Component\HttpFoundation\Request`` from the
list of candidates:

.. code:: php

   <?php

   use Symfony\Component\HttpFoundation\Request;

   class Hello
   {
       public function index(Request $request)
       {
       }
   }

Expand Class
------------

Expand the class name from unqualified name to fully qualified name.

.. tabs::

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorClassExpand

.. _motivation-15:

Motivation
~~~~~~~~~~

Although importing classes can make code cleaner, sometimes the code can
be more readable if the fully qualified name is specified. For example,
we might register a list of listeners in a file.

.. _before-and-after-15:

Before and After
~~~~~~~~~~~~~~~~

Cursor position shown as ``<>``:

.. code:: php

   <?php

   namespace App\Event;

   class UserCreatedEvent
   {
       protected $listenrs = [
           AssignDefaultRole<>ToNewUser::class
       ];
   }

After selecting ``App\Listeners\AssignDefaultRoleToNewUser`` from the
list of candidates:

.. code:: php

   <?php

   namespace App\Event;

   class UserCreatedEvent
   {
       protected $listenrs = [
           \App\Listeners\AssignDefaultRoleToNewUser::class
       ];
   }

Import Missing Classes
----------------------

Import all missing classes in the current file.

.. tabs::

   .. tab:: VIM Context Menu

       *Class context menu > Import Missing*

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorImportMissingClasses


.. _motivation-16:

Motivation
~~~~~~~~~~

You may copy and paste some code from one file to another and
subsequently need to import all the foreign classes into the current
namespace. This refactoring will identify all unresolvable classes and
import them.

Fill Object
-----------

Fill a new objects constructor with default arguments.

.. tabs::

   .. tab:: LSP

      Invoke code action on new class expression with no constructor arguments


Motivation
~~~~~~~~~~

This refactoring is especially useful if you need to either create or map a
DTO (data transfer object).

Before and After
~~~~~~~~~~~~~~~~

Cursor position shown as ``<>``:

.. code:: php

   <?php

   new My<>FancyDTO();

After choosing the "Fill Object" code action:

.. code:: php

   <?php

   new MyFancyDTO(title: '', cost: 0);

Override Method
---------------

Override a method from a parent class.

.. tabs::

   .. tab:: LSP

      Invoke code action on file with classes with overridable methods

   .. tab:: VIM Context Menu

       *Class context menu > Override method*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorContextMenu

**Multiple selection**: Supports selecting multiple methods.

.. _motivation-17:

Motivation
~~~~~~~~~~

Sometimes it is expected or necessary that you override a parent class's
method (for example when authoring a Symfony Command class).

This refactoring will allow you to select a method to override and
generate that method in your class.

.. _before-and-after-16:

Before and After
~~~~~~~~~~~~~~~~

.. code:: php

   <?php

   use Symfony\Component\Console\Command\Command;

   class MyCommand extends Command
   {
   }

Override method ``execute``:

.. code:: php

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

Generation
==========

.. _generation_class_new:

Class New
---------

Generate a new class with a name and namespace at a given location or
from a class name.

.. tabs::

   .. tab:: Language Server

       Invoke the code action menu on a non-existing class name

   .. tab:: CLI

       .. code-block::

           $ phpactor class:new path/To/ClassName.php

       (Note that class FQN is also accepted)

   .. tab:: VIM context menu

       When on a class name (preferable a non-existing one) you can create a
       new class via. the context menu.

       *Class context menu > New Class*

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorClassNew

.. _motivation-4:

Motivation
~~~~~~~~~~

Creating classes is one of the most general actions we perform:

1. Create a new file.
2. Code the namespace, ensuring that it is compatible with the
   autoloading scheme.
3. Code the class name, ensuring that it is the same as the file name.

This refactoring will perform steps 1, 2 and 3 for:

-  Any given file name.
-  Any given class name.
-  A class name under the cursor.

It is also possible to choose a class template, see
`templates <templates.md>`__ for more information.

.. _before-and-after-4:

Before and After
~~~~~~~~~~~~~~~~

.. container:: alert alert-success

   This example is from an existing, empty, file. Note that you can also
   use the context menu to generate classes from non-existing class
   names in the current file

Given a new file:

.. code:: php

   # src/Blog/Post.php

After invoking *class new* using the ``default`` variant:

.. code:: php

   <?php

   namespace Acme/Blog;

   class Post
   {
   }

Class Copy
----------

Copy an existing class to another location updating its name and
namespace.

.. tabs::

   .. tab:: CLI

       .. code-block::

           $ phpactor class:copy path/to/ClassA.php path/to/ClassB.php

       Note that class FQNs are also accepted.

   .. tab:: VIM context menu

       *Class context menu > Copy Class*

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorCopyFile

.. _motivation-1:

Motivation
~~~~~~~~~~

Sometimes you find that an existing class is a good starting point for a
new class. In this situation you may:

1. Copy the class to a new file location.
2. Update the class name and namespace.
3. Adjust the copied class as necessary.

This refactoring performs steps 1 and 2.

.. _before-and-after-1:

Before and After
~~~~~~~~~~~~~~~~

.. code:: php

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

After moving to ``src/Cms/Article.php``:

.. code:: php

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

Extract Interface
-----------------

Extract an interface from a class. If a wildcard is given (CLI only)
generate an interface per class.

.. tabs::

   .. tab:: CLI

       .. code-block::

           $ phpactor class:inflect path/to/Class.php path/to/Interface.php

       (wild card accepted).

   .. tab:: VIM Context Menu

      *Class context menu > Inflect > Extract interface*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorClassInflect


.. _motivation-10:

Motivation
~~~~~~~~~~

It is sometimes unwise to preemptively create interfaces for all your
classes, as doing so adds maintenance overhead, and the interfaces may
never be needed.

This refactoring allows you to generate an interface from an existing
class. All public methods will be added to generated interface.

.. _before-and-after-10:

Before and After
~~~~~~~~~~~~~~~~

.. code:: php

   <?php

   class Foobar
   {
       public function foobar(string $bar): Barfoo
       {
       }
   }

Generated interface (suffix added for illustration):

.. code:: php

   <?php

   interface FoobarInterface
   {
       public function foobar(string $bar): Barfoo;
   }

Refactoring
===========

Change Visibility
-----------------

Change the visibility of a class member

.. tabs::

   .. tab:: VIM context menu

       *Class member context menu > Change Visibility*

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorChangeVisibility


Currently this will cycle through the 3 visibilities: ``public``,
``protected`` and ``private``.

.. _motivation-2:

Motivation
~~~~~~~~~~

Sometimes you may want to extract a class from an existing class in
order to isolate some of it’s responsibility. When doing this you may:

1. Create a new class using `Class New <#class-new>`__.
2. Copy the method(s) which you want to extract to the new class.
3. Change the visibility of the main method from ``private`` to
   ``public``.

.. _before-and-after-2:

Before and After
~~~~~~~~~~~~~~~~

Cursor position shown as ``<>``:

.. code:: php

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

After invoking “change visibility” on or within the method.

.. code:: php

   # src/Blog/FoobarResolver.php
   <?php

   namespace Acme\Blog;

   class FoobarResolver
   {
       public function resolveFoobar();
       {
       }
   }

*Note*: This also works on constants and properties. It will NOT change
the visibility of any related parent or child class members.

Class Move
----------

Move a class (or folder containing classes) from one location to
another.

.. tabs::

   .. tab:: CLI

       .. code-block::

           $ phpactor class:move path/to/ClassA.php path/to/ClassB.php

       (class FQN also accepted).

   .. tab:: VIM context menu

       *Class context menu > Move Class*

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorMoveFile

.. _motivation-3:

Motivation
~~~~~~~~~~

When authoring classes, it is often difficult to determine really
appropriate names and namespaces, this is unfortunate as a class name
can quickly propagate through your code, making the class name harder to
change as time goes on.

This problem is multiplied if you have chosen an incorrect namespace.

This refactoring will move either a class, class-containing-file or
folder to a new location, updating the classes namespace and all
references to that class where possible in a given *scope* (i.e. files
known by GIT: ``git``, files known by Composer: ``composer``, or all PHP
files under the current CWD: ``simple``).

If you have defined file relationships with
`navigator.destinations <https://phpactor.github.io/phpactor/navigation.html#jump-to-or-generate-related-file>`__,
then you have the option to move the related files in addition to the
specified file. If using the command then specify ``--related``, or if
using the RPC interface (f.e. VIM) you will be prompted.

.. container:: alert alert-danger

   This is a dangerous refactoring! Ensure that you commit your work
   before executing it and be aware that success is not guaranteed
   (e.g. class references in non-PHP files or docblocks are not
   currently updated).

   This refactoring works best when you have a well tested code base.

.. _before-and-after-3:

Before and After
~~~~~~~~~~~~~~~~

.. code:: php

   # src/Blog/Post.php
   <?php

   namespace Acme\Blog;

   class Post
   {
   }

After moving to ``src/Writer.php``:

.. code:: php

   # src/Writer.php
   <?php

   namespace Acme;

   class Writer
   {
   }

.. _generation_extract_constant:

Extract Constant
----------------

Extract a constant from a scalar value.

.. tabs::

   .. tab:: Language Server

       Select a range and request code actions

   .. tab:: VIM Context Menu

       *Symbol context menu > Extract Constant*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorContextMenu

.. _motivation-7:

Motivation
~~~~~~~~~~

Each time a value is duplicated in a class a fairy dies. Duplicated
values increase the fragility of your code. Replacing them with a
constant helps to ensure runtime integrity.

This refactoring includes `Replace Magic Number with Symbolic
Constant <https://refactoring.com/catalog/replaceMagicNumberWithSymbolicConstant.html>`__
(Fowler, Refactoring).

.. _before-and-after-7:

Before and After
~~~~~~~~~~~~~~~~

Cursor position shown as ``<>``:

.. code:: php

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

After:

.. code:: php

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

.. _generation_extract_expression:

Extract Expression
------------------

Extract an expression

.. tabs::

   .. tab:: CLI

       *VIM Plugin only*

   .. tab:: VIM Context Menu

       *VIM Plugin only*

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorExtractExpression

       Call with ``v:true`` to invoke on a selection.

.. _motivation-8:

Motivation
~~~~~~~~~~

Sometimes you find yourself using inline expressions, and later you
realise that you want to re-use that value.

.. _before-and-after-8:

Before and After
~~~~~~~~~~~~~~~~

Cursor position shown as ``<>``:

.. code:: php

   <?php

   if (<>1 + 2 + 3 + 5 === 6) {
       echo 'You win!';
   }

After (entering ``$hasWon`` as a variable name):

.. code:: php

   <?php

   $hasWon = 1 + 2 + 3 + 5 === 6;
   if ($hasWon) {
       echo 'You win!';
   }

Note that this also works with a visual selection if you invoke the VIM
function with ``v:true``:

.. code:: php

   <?php

   if (<>1 + 2 + 3 + 5<> === 6) {
       echo 'You win!';
   }

After (using ``$winningCombination`` as a variable name):

.. code:: php

   <?php

   $winningCombination = 1 + 2 + 3 + 5;
   if ($winningCombination == 6) {
       echo 'You win!';
   }

.. _generation_extract_method:

Extract Method
--------------

Extract a method from a selection.

.. tabs::

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorExtractMethod

This refactoring is NOT currently available through the context menu.
You will need to `map it to a keyboard
shortcut <vim-plugin.md#keyboard-mappings>`__ or invoke it manually.

.. _motivation-9:

Motivation
~~~~~~~~~~

This is one of the most common refactorings. Decomposing code into
discrete methods helps to make code understandable and maintainable.

Extracting a method manually involves:

1. Creating a new method
2. Moving the relevant block of code to that method.
3. Scanning the code for variables which are from the original code.
4. Adding these variables as parameters to your new method.
5. Calling the new method in place of the moved code.

This refactoring takes care of steps 1 through 5 and:

-  If a *single* variable that is declared in the selection which is
   used in the parent scope, it will be returned.
-  If *multiple* variables are used, the extracted method will return a
   tuple.
-  In both cases the variable(s) will be assigned at the point of
   extraction.
-  Any class parameters which are not already imported, will be
   imported.

.. _before-and-after-9:

Before and After
~~~~~~~~~~~~~~~~

Selection shown between the two ``<>`` markers:

.. code:: php

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

After extracting method ``newMethod``:

.. code:: php

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

.. _refactoring_rename_variable:

Rename Variable
---------------

Rename a variable in the local or class scope.

.. tabs::

   .. tab:: VIM Context Menu

       *Variable context menu > Rename*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorContextMenu

.. _motivation-18:

Motivation
~~~~~~~~~~

Having meaningful and descriptive variable names makes the intention of
code clearer and therefore easier to maintain. Renaming variables is a
frequent refactoring, but doing this with a simple search and replace
can often have unintended consequences (e.g. renaming the variable
``$class`` also changes the ``class`` keyword).

This refactoring will rename a variable, and only variables, in either
the method scope or the class scope.

.. _before-and-after-17:

Before and After
~~~~~~~~~~~~~~~~

Cursor position shown as ``<>``:

.. code:: php

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

Rename the variable ``$hellos`` to ``$foobars`` in the local scope:

.. code:: php

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

.. _refactoring_rename_class:

Rename Class
------------

Rename a class.

.. tabs::

   .. tab:: CLI

       .. code-block::

           $ phpactor references:class path/to/Class.php --replace="NewName"

       Class FQN accepted

   .. tab:: VIM Context Menu

       *Class context menu > Replace references*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorContextMenu

.. _motivation-19:

Motivation
~~~~~~~~~~

This refactoring is *similar* to `Move Class <#class-move>`__, but
without renaming the file. This is a useful refactoring when a dependant
library has changed a class name and you need to update that class name
in your project.

.. _before-and-after-18:

Before and After
~~~~~~~~~~~~~~~~

Cursor position shown as ``<>``:

.. code:: php

   <?php

   class Hel<>lo
   {
       public function say()
       {

       }

   }

   $hello = new Hello();
   $hello->say();

Rename ``Hello`` to ``Goodbye``

.. code:: php

   <?php

   class Goodbye
   {
       public function say()
       {

       }

   }

   $hello = new Goodbye();
   $hello->say();

.. container:: alert alert-danger

   When renaming classes in your project use Class Move.

.. _refactoring_rename_member:

Rename Class Member
-------------------

Rename a class member.

.. tabs::

   .. tab:: CLI

       .. code-block::

           $ phpactor references:member path/to/Class.php memberName --type="method" --replace="newMemberName"

       Class FQNs are also accepted

   .. tab:: VIM Context Menu

      *Member context menu > Replace references*.

   .. tab:: VIM Plugin

       .. code-block::

           :PhpactorContextMenu

.. _motivation-20:

Motivation
~~~~~~~~~~

Having an API which is expressive of the intent of the class is
important, and contributes to making your code more consistent and
maintainable.

When renaming members global search and replace can be used, but is a
shotgun approach and you may end up replacing many things you did not
mean to replace (e.g. imagine renaming the method ``name()``).

This refactoring will:

1. Scan for files in your project which contain the member name text.
2. Parse all of the candidate files.
3. Identify the members, and try and identify the containing class.
4. Replace only the members which certainly belong to the target class.

When replacing *private* and *protected* members, only the related
classes will be updated.

Due to the loosely typed nature of PHP this refactoring may not find all
of the member accesses for the given class. Run your tests before and
after applying this refactoring.

.. container:: alert alert-info

   Hint: Use the CLI command to list all of the risky references. Risky
   references are those member accesses which match the query but whose
   containing classes could not be resolved.

.. figure:: images/risky.png
   :alt: Risky references

   Risky references

.. _before-and-after-19:

Before and After
~~~~~~~~~~~~~~~~

Cursor position shown as ``<>``:

.. code:: php

   <?php

   class Hello
   {
       public function sa<>y()
       {

       }

   }

   $hello = new Hello();
   $hello->say();

Rename ``Hello#say()`` to ``Hello#speak()``

.. code:: php

   <?php

   class Hello
   {
       public function speak()
       {

       }

   }

   $hello = new Hello();
   $hello->speak();

