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

.. note::

   Phpactors type inference is based on
   `WorseReflection <https://github.com/phpactor/worse-reflection>`__.

Type inference
--------------

Assert
~~~~~~

When encountering an ``assert`` with ``instanceof`` it will cast the
variable to that type, or a union of that type. See also
`#instanceof <#instanceof>`__.

.. code:: php

   <?php

   assert($foo instanceof Hello);
   assert($foo instanceof Hello || $foo instanceof Goodbye)

   $foo-> // type: Hello|Goodbye

Assignments
~~~~~~~~~~~

Phpactor will track assignemnts:

.. code:: php

   <?php

   $a = 'hello';
   $b = $a;
   $b; // type: string

… and assignments from method calls, class properties, anything
reflectable, etc.

Catch
~~~~~

.. code:: php

   <?php

   try {
      // something
   } catch (MyException $e) {
       $e-> // type: MyException
   }

Docblocks
~~~~~~~~~

Docblocks are supported for method parameters, return types, class properties
and inline declartaions

.. code:: php

   <?php

   /**
    * @var string
    */
   private $scalar;

   /**
    * @var string[]
    */
   private $arrayOfType;

   /**
    * @var Collection<MyThing>
    */
   private $iterableOfMyThing;


Foreach
~~~~~~~

Understands ``foreach`` with the docblock array annotation:

.. code:: php

   <?php

   /** @var Hello[] $foos */
   $foos = [];

   foreach ($foos as $foo) {
       $foo-> // type:Hello
   }

Also understands simple generics:

.. code:: php

   <?php

   /** @var ArrayIterator<Hello> $foos */
   $foos = new ArrayIterator([ new Hello() ]);

   foreach ($foos as $foo) {
       $foo-> // type:Hello
   }

FunctionLike
~~~~~~~~~~~~

Understands annonymous functions:

.. code:: php

   <?php

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

   <?php

   if ($foobar instanceof Hello) {
       $foobar-> // type: Hello
   }

.. code:: php

   <?php

   if (false === $foobar instanceof Hello) {
       return;
   }

   $foobar-> // type: Hello

.. code:: php

   <?php

   if ($foobar instanceof Hello || $foobar instanceof Goodbye) {
       $foobar-> // type: Hello|Goodbye
   }

Variables
~~~~~~~~~

Phpactor supports type injection via. docblock:

.. code:: php

   <?php

   /** @var Foobar $foobar */
   $foobar-> // type: Foobar

and inference from parameters:

.. code:: php

   <?php

   function foobar(Barfoo $foobar, $barbar = 'foofoo')
   {
       $foobar; // type: Barfoo
       $barbar; // type: foofoo
   }
