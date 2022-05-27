Types
=====

This document provides a non-exhaustive list of PHP and docblock types and
indicates if Phpactor supports them or not. For more information about these
types see:

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
    Resource         (internal type)     ``*``      ✘
    String           ``string``          ``*``      ✔
    Self             ``self``            ``*``      ✔
    Parent           ``parent``          ``*``      🤷
    Callable         ``callable``        ``*``      ✘
    Iterable         ``iterable``        ``7.1``    ✘
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
    Static                               ``8.0``    ✔
    Never            ``never``           ``8.1+``   ✔
    False            ``false``           ``8.2+``   ✘         Pseudo-type before 8.2
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
    Generics         ``Foobar<Barfoo>``              ✔ 
    Int Literal      ``1234``                        ✔ 
    Float Literal    ``1234.12``                     ✔ 
    String Literal   ``"hello"``                     ✔ 
    Parenthesized    ``(Foo&Bar)|object``            ✔ 
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
