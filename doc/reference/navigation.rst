.. _navigation:

Navigation
==========

Phpactor provides some functionality for navigating to (and generating)
contextually relevant files such as parent classes, definitions, unit
tests, references etc.

.. contents::
   :depth: 1
   :backlinks: none
   :local:

.. _navigation_class_references:

Class References
----------------

Navigate / list all references to a given class.

-  **Command**: ``$ phpactor references:class path/to/Class.php``
-  **VIM Context Menu**: *Class context menu > Find references*.
-  **VIM Command**:``:PhpactorFindReferences``

Description
~~~~~~~~~~~

Keep track of where a class is being used or perform an initial survey
before deciding to rename a class.

The VIM plugin will load the class references into a quick fix list
which you can navigate through (see ``:help quickfix``).

The CLI command will list the references and show a highlighted line
where the references were found.

.. figure:: images/class-referenes.png
   :alt: Class references

   Class references

.. _navigation_class_member_references:

Class Member References
-----------------------

Navigate / list all references to a given class member (method, property
or constant).

-  **Command**:
   ``$ phpactor references:member path/to/Class.php memberName``
-  **VIM Context Menu**: *Member context menu > Find references*.
-  **VIM Command**:``:PhpactorFindReferences``

.. _description-1:

Description
~~~~~~~~~~~

Scan for all references to a class member in the project.

This functionality is very similar to `Class
References <#class-references>`__ with the exception that it is possible
that not all members will be found as PHP is a loosely typed language
and it may not be possible to determine all the class types of methods
matching the query.

Hover
-----

While not a navigation function as such, this RPC command will show
brief information about the symbol underneath the cursor.

-  **Command**: *RPC Only*
-  **VIM Context Menu**: *Context menu* > Hover_.
-  **VIM Command**:``:PhpactorHover``

Jump to definition
------------------

Jump to the definition of a class or class member.

-  **Command**: *RPC Only*
-  **VIM Context Menu**: *Member/class context menu > Goto definition*.
-  **VIM Command**:``:PhpactorGotoDefinition``

.. _description-2:

Description
~~~~~~~~~~~

Open the file containing the class or class member under the cursor and
move the cursor to the place where class or class member is defined.

This feature is **extremely useful**! Be sure to map it to a keyboard
shortcut and use it often to quickly navigate through your source code.

Jump to type
------------

Jump to the type of the symbol under the cursor.

-  **Command**: *RPC Only*
-  **VIM Context Menu**: \_Member/class context menu > Goto type.
-  **VIM Command**:``:PhpactorGotoType()``

.. _description-3:

Description
~~~~~~~~~~~

Sometimes you will want to jump to the type (i.e. the class) of a
symbol, for example if you reference a property in code,
``$this->locator``, you can invoke *goto type* on the property and jump
to the, for example, ``Locator`` type.

Jump to Implementation
----------------------

Jump to the implementatoin(s) of an interface or class

-  **Command**: *RPC Only*
-  **VIM Context Menu**: *Member/class context menu > Goto
   implementation*.
-  **VIM Command**:``:PhpactorGotoImplementations`` n ### Description

Jump to implementations of the interface or class under the cursor.

NOTE: This is a hugely expensive operation if not optimised. We apply
the following filters to avoid parsing all classes in the entire
project:

-  Filter files which do not contain classes which extend or implement
   something.
-  Filter files whoses classes do not contain share at least one “word”
   with the interface’s FQN from the last 2 path segments.

So, given the interface ``Phpactor\Rpc\Handler``, the following will be
considered:

-  ``/src/Completion/Rpc/CompletionHandler.php``
-  ``/src/Completion/Handler/Completion.php``

Where as the following will not:

-  ``/src/Completion/Completion.php``
-  ``/src/Handler/Completion/Completion.php``

The filters can be disabled via the following configuration options:

-  ``worse_reference_finder.implementation_finder.abstractness_filter``:
   Enable abstractness filter (default ``true``)
-  ``worse_reference_finder.implementation_finder.similarity_filter``
   Enable similarity filter (default ``true``)

Jump to or generate related file
--------------------------------

Jump to a related file (e.g. parent class, interfaces, unit test,
integration test, benchmark), and optionally generate it if it doesn’t
exist (where supported).

-  **Command**: *RPC Only*
-  **VIM Context Menu**: *Class context menu > Navigate*.
-  **VIM Command**:``:PhpactorNavigate``

.. _description-4:

Description
~~~~~~~~~~~

Often classes will have a one-to-one relationship with another class,
for example a single class will often have a matching unit test.

Phpactor provides a way to define this relationship:

.. code:: yaml

   # .phpactor.yml
   navigator.destinations:
       source: lib/<kernel>.php
       unit_test: tests/Unit/<kernel>Test.php

   navigator.autocreate:
       source: default
       unit_test: phpunit_test

Above we define a pattern which will match the source code of the
project (and assign it an identifier ``source``). We also identify a
pattern to identify ``unit_test`` classes.

When you are in a ``source`` file, the navigate option will offer you
the possiblity of jumping to the unit test, and vice-versa.

Above we additionally (and optionally) tell Phpactor that it can
autogenerate these classes based on `templates <templates.md>`__.
