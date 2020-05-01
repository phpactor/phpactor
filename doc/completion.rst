Completion
==========

Phpactor provides completion for:

-  **Class names**: All PSR compliant classes in the project and vendor
   tree.
-  **Class members**: Methods, constants, properties of auto-loadable
   classes.
-  **Functions**: Built-in and bootstrapped.
-  **Constants**: Built-in and bootstrapped.
-  **Parameters**: Will suggest appropriate local variables for method
   parameters.

Uniquely, Phpactor does not pre-index anything, completion happens in
*real time*, file locations are guessed based on composer locations (or
brute forced if not using composer). For non-autoloadable entities
(e.g. functions) it is assumed that these are defined during bootstrap.

Type inference
--------------

Phpactors type inference is based on
`WorseReflection <https://github.com/phpactor/worse-reflection>`__.

Assert
~~~~~~

When encountering an ``assert`` with ``instanceof`` it will cast the
variable to that type, or a union of that type. See also
`#instanceof <#instanceof>`__.

.. code:: php

   assert($foo instanceof Hello);
   assert($foo instanceof Hello || $foo instanceof Goodbye)

   $foo-> // type: Hello|Goodbye

Assignments
~~~~~~~~~~~

Phpactor will track assignemnts:

.. code:: php

   $a = 'hello';
   $b = $a;
   $b; // type: string

… and assignments from method calls, class properties, anything
reflectable, etc.

Catch
~~~~~

.. code:: php


   try {
      // something
   } catch (MyException $e) {
       $e-> // type: MyException
   }

Foreach
~~~~~~~

Understands ``foreach`` with the docblock array annotation:

.. code:: php

   /** @var Hello[] $foos */
   $foos = [];

   foreach ($foos as $foo) {
       $foo-> // type:Hello
   }

Also understands simple generics:

.. code:: php

   /** @var ArrayIterator<Hello> $foos */
   $foos = new ArrayIterator([ new Hello() ]);

   foreach ($foos as $foo) {
       $foo-> // type:Hello
   }

FunctionLike
~~~~~~~~~~~~

Understands annonymous functions:

::

   $barfoo = new Barfoo();
   $function = function (Foobar $foobar) use ($barfoo) {
       $foobar-> // type: Foobar
       $barfoo-> // type: Barfoo
   }

InstanceOf
~~~~~~~~~~

``if`` statements are evaluated, if they contain ``instanceof`` then the
type is inferred:

.. code:: php

   if ($foobar instanceof Hello) {
       $foobar-> // type: Hello
   }

.. code:: php

   if (false === $foobar instanceof Hello) {
       return;
   }

   $foobar-> // type: Hello

.. code:: php

   if ($foobar instanceof Hello || $foobar instanceof Goodbye) {
       $foobar-> // type: Hello|Goodbye
   }

Variables
~~~~~~~~~

Phpactor supports type injection via. docblock:

::

   /** @var Foobar $foobar */
   $foobar-> // type: Foobar

and inference from parameters:

::

   function foobar(Barfoo $foobar, $barbar = 'foofoo')
   {
       $foobar; // type: Barfoo
       $barbar; // type: foofoo
   }
