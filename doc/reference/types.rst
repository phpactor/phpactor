Types
=====

This document attempts to show all the types that Phpactor supports.

.. contents::
   :depth: 1
   :backlinks: none
   :local:

For more information about types see:


- `Official PHP Documentation <https://www.php.net/manual/en/language.types.declarations.php>`_
- `PHPStan Types <https://phpstan.org/writing-php-code/phpdoc-types>`_
- `Psalm Types <https://psalm.dev/docs/annotating_code/typing_in_psalm/>`_

Basic Types
-----------

.. table::
    :align: left

    ==============   ==================  =========  ========
    Name             Example             PHP        Phpactor
    ==============   ==================  =========  ========
    Array            ``array``           ``*``      ✔
    Boolean          ``bool``            ``*``      ✔
    Float            ``float``           ``*``      ✔
    Int              ``int``             ``*``      ✔
    Resource         (internal type)     ``*``      ✔
    String           ``string``          ``*``      ✔
    Self             ``self``            ``*``      ✔
    Parent           ``parent``          ``*``      ✔
    Callable         ``callable``        ``*``      ✔
    Iterable         ``iterable``        ``7.1``    ✔
    Nullable         ``?Foor``           ``7.1``    ✔
    Object           ``object``          ``7.2``    ✔
    Union            ``Foo|Bar``         ``8.0``    ✔
    Mixed            ``mixed``           ``8.0``    ✔
    Intersection     ``Foo&Bar``         ``8.1``    ✔
    ==============   ==================  =========  ========

Return Only Types
~~~~~~~~~~~~~~~~~

.. table::
    :align: left

    ==============   ==================  =========  ========  ========================
    Name             Example             PHP        Phpactor  Notes
    ==============   ==================  =========  ========  ========================
    Void             ``void``            ``7.4+``   ✔
    Static           ``static``          ``8.0``    ✔
    Never            ``never``           ``8.1+``   ✔
    False            ``false``           ``8.2+``   ✔         Pseudo-type before 8.2
    Null             ``null``            ``8.2+``   ✔
    ==============   ==================  =========  ========  ========================

Docblock Types
~~~~~~~~~~~~~~

.. table::
    :align: left

    ===============  ==============================  ========
    Name             Example                         Phpactor
    ===============  ==============================  ========
    Array Key        ``array-key``                   ✔
    Array Literal    ``array{string,int}``           ✔
    Array Shape      ``array{foo:string,baz:int}``   ✔
    List Syntax      ``string[]``                    ✔
    Class String     ``class-string<T>``             ✔
    Closure          ``Closure(string, int): void``  ✔
    Float Literal    ``1234.12``                     ✔
    Generics         ``Foobar<Barfoo>``              ✔
    Int Literal      ``1234``                        ✔
    Int Range        ``int<0,max>``                  ✔
    Int Positive     ``positive-int``                ✔
    Int Negative     ``negative-int``                ✔
    List             ``list<string>``                ✔
    Parenthesized    ``(Foo&Bar)|object``            ✔
    String Literal   ``"hello"``                     ✔
    This             ``$this`` (same as ``static``)  ✔
    ===============  ==============================  ========

Integer Types
-------------

.. table::
    :align: left

    ==============  =============  =========  ===========
    Example         PHP            Supported  Description
    ==============  =============  =========  ===========
    ``123``         ``*``          ✔          Integer
    ``0b0110``      ``*``          ✔          Binary type
    ``0x1a``        ``*``          ✔          Hexidecimal
    ``0123``        ``*``          ✔          Octal
    ``123_123``     ``7.4``        ✔          Decimal
    ``0o123``       ``8.1``        ✘          Octal
    ==============  =============  =========  ===========

Conditional Types
-----------------

Phpactor undestands conditional return types of the form:


.. code-block:: php

    /**
     * @return (
     *     $array is array<int>
     *     ? int
     *     : ($array is array<float>
     *         ? float
     *         : float|int
     *     )
     * )
     */
    function array_some(array $array) {
        return array_sum($array);
    }

Generic Types
-------------

Phpactor understands Generic (or templated) types. See `PHPStan <https://phpstan.org/blog/generics-in-php-using-phpdocs>`_ or
`Psalm <https://psalm.dev/docs/annotating_code/templated_annotations/>`_
documentation for what these are and how they work.

Phpactor supports:

- ``@implements`` and ``@extends`` in addition to ``@template-extends`` and
  ``@template-implements``.
- ``@template`` and ``@template T of Foo``
- Injecting template variables into the constructor.
- Method level template vars.
- ``class-string<T>``

For example:

.. code-block:: php

    <?php

    /**
     * @template T
     */
    class Foo {
        /**
         * @var T
         */
        private $a;

        /** @param T $a */
        public function __construct($a) {
            $this->a = $a;
        }

        /**
         * @return T
         */
        public function a()
        {
            return $this->a;
        }
    }

    $f = new Foo(new Bar());
    $bar = $f->a(); // Phpactor now knows that `$bar` is Bar

In addition Phpactor supports `class-string<T>` which allows you to capture a
class type from a class string (e.g. ``MyClass::class`` is interpreted as a
`class-string`. The following extract is from the Phpactor Container.

.. code-block:: php

    <?php

    interface Container
    {
        /**
         * @template T of object
         * @param class-string<T>|string $id
         * @return ($id is class-string<T> ? T : mixed)
         */
        public function get($id);
    }

The conditional type enables the return value of ``get`` to be an object of
class ``T`` if the ``$id`` is a ``class-string`` or ``mixed`` in any other
case.
