.. _completion:

Completion
==========

.. contents::
   :depth: 2
   :backlinks: none
   :local:

Completors
----------

.. note::

    Completors can be enabled or disabled in configuration f.e. :ref:`param_completion_worse.completor.worse_parameter.enabled` configuration.

``worse_parameter``
~~~~~~~~~~~~~~~~~~~

Provides suggestions for arguments.

If there are suitable variables in the current scope they will be given
priority if they match the type of the method parameter.

.. code:: php

    <?php

    function foobar(string $string) {}

    $foobar = 'foobar';
    $barfoo = 1234;

    foobar(/** completion invoked here */);

Given the cursor is at ``<>`` ``$foobar`` will be suggested with a higher
priority than ``$barfoo`` and the parameter index will be shown in the
completion description.

``worse_constructor``
~~~~~~~~~~~~~~~~~~~~~

As with ``worse_parameter`` but for constructor arguments.

``worse_class_member``
~~~~~~~~~~~~~~~~~~~~~~

Provides class member (methods, properties and constants) suggestions.
Triggered on ``::`` and ``->``.

``indexed_name``
~~~~~~~~~~~~~~~~

Provides class and function name completion from the :ref:`indexer`.

``scf_class``
~~~~~~~~~~~~~

This completor will provide class names by *scanning the vendor directory* and
transposing the file names into class names.

This completor is disabled by default when using the :ref:`language_server`.

``worse_local_variable``
~~~~~~~~~~~~~~~~~~~~~~~~

Provide completion for local variables in a scope. Triggered on ``$``.

``declared_function``
~~~~~~~~~~~~~~~~~~~~~

Provide function name completion based on functions defined at _runtime_ in the
Phpactor process.

Note that any functions which are not loaded when _Phpactor_
loads will not be available. So this is mainly useful for built-in functions.

This completor is disabled by default when using the :ref:`language_server`.

``declared_constant``
~~~~~~~~~~~~~~~~~~~~~~

Provide constant name completion based on constants defined at _runtime_ in the
Phpactor process.

This is mainly useful for built-in constants (e.g. ``JSON_PRETTY_PRINT`` or
``PHP_INT_MAX``).

``worse_class_alias``
~~~~~~~~~~~~~~~~~~~~~~

Provide suggestions for any classes imported into the current class with
aliases.


``declared_class``
~~~~~~~~~~~~~~~~~~

Provide completion for class names from class names defined in the Phpactor
process.

This is mainly useful when used with the ``scf_class`` completor to provide
built-in classes.

This completor is disabled by default when using the :ref:`language_server`.

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

Phpactor will track assignments:

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

Understands anonymous functions:

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

Phpactor supports type injection via docblock:

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

Generics
~~~~~~~~

Phpactor currently supports the `@implements` and `@extends` generic tags:

.. code:: php

   <?php

   namespace Foo;

   /**
    * @template T
    * @extends IteratorAggregate<T>
    */
   interface ReflectionCollection extends \IteratorAggregate, \Countable
   {
   }

   /**
    * @template T of ReflectionMember
    * @extends ReflectionCollection<T>
    */
   interface ReflectionMemberCollection extends ReflectionCollection
   {
       /**
        * @return ReflectionMemberCollection<T>
        */
       public function byName(string $name): ReflectionMemberCollection;

       /**
        * @return ReflectionMemberCollection<T>
        */
       public function byMemberType(string $type): ReflectionMemberCollection;
   }

   interface ReflectionClassLike
   {
       public function members(): ReflectionMemberCollection;
   }


   /** @var ReflectionClassLike $reflection */
   $reflection;
   foreach ($reflection->members()->byMemberType('fii')->byName('__construct') as $constructor) {
        $reflection-><>
   }
