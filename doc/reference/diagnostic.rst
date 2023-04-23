Diagnostics
===========


.. This document is generated via the `development:generate-documentation` command


.. contents::
   :depth: 2
   :backlinks: none
   :local:


``missing_method``
------------------

Report if trying to call a class method which does not exist.

.. tabs::

    .. tab:: missing method on instance 
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
            }
            
            $f = new Foobar();
            $f->bar();
        
        Will show diagnostic(s):
        
        - `ERROR`: Method "bar" does not exist on class "Foobar"
        
    .. tab:: missing method for static invocation
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
            }
            
            Foobar::bar();
        
        Will show diagnostic(s):
        
        - `ERROR`: Method "bar" does not exist on class "Foobar"
        
``missing_phpdoc_return``
-------------------------

Report when a method has a return type should beaugmented by a phpdoc.

.. tabs::

    .. tab:: method without return type
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
                public function foo() {
                    return 'foobar';
                }
            }
        
        Will show diagnostic(s):
        
        - `WARN`: Method "foo" is missing docblock return type: string
        
``missing_phpdoc_param``
------------------------

Report when a method has a parameter with a type that should beaugmented by a phpdoc.

.. tabs::

    .. tab:: closure
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
                public function foo(Closure $foobar) {
                }
            }
        
        Will show diagnostic(s):
        
        - `WARN`: Method "foo" is missing @param $foobar
        
    .. tab:: generator
        
        .. code-block:: php
        
            <?php
            
            /**
             * @template TKey
             * @template TValue of string
             */
            class Generator {
            }
            
            class Foobar
            {
                public function foo(Generator $foobar) {
                }
            }
        
        Will show diagnostic(s):
        
        - `WARN`: Method "foo" is missing @param $foobar
        
    .. tab:: array
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
                public function foo(array $foobar) {
                }
            }
        
        Will show diagnostic(s):
        
        - `WARN`: Method "foo" is missing @param $foobar
        
``assignment_to_missing_property``
----------------------------------

Report when assigning to a missing property definition.

.. tabs::

    .. tab:: to non-existing property
        
        .. code-block:: php
        
            <?php
            
            class Foobar {
                public function baz(){ 
                    $this->bar = 'foo';
                }
            }
        
        Will show diagnostic(s):
        
        - `WARN`: Property "bar" has not been defined
        
``missing_return_type``
-----------------------

Report if a method is missing a return type.

.. tabs::

    .. tab:: missing return type
        
        .. code-block:: php
        
            <?php
            
            class Foobar {
                public function foo()
                {
                    return 'string';
                }
            }
        
        Will show diagnostic(s):
        
        - `WARN`: Missing return type `string`
        
    .. tab:: unable to infer return type
        
        .. code-block:: php
        
            <?php
            
            class Foobar {
                public function foo()
                {
                    return foo();
                }
            }
            
            function foo() {
            }
        
        Will show diagnostic(s):
        
        - `WARN`: Method "foo" is missing return type and the type could not be determined
        
``unresolvable_name``
---------------------

Report if a name (class, function, constant etc) can not be resolved.

.. tabs::

    .. tab:: class name constant unresolvable
        
        .. code-block:: php
        
            <?php
            
            function foo(string $name)
            }
            
            
            foo(Foobar::class);
        
        Will show diagnostic(s):
        
        - `ERROR`: Class "Foobar" not found
        
    .. tab:: parameter
        
        .. code-block:: php
        
            <?php
            
            class RpcCommand
            {
                public function __construct(
                    $inputStream = Foo::BAR
                ) {
                }
            }
        
        Will show diagnostic(s):
        
        - `ERROR`: Class "Foo" not found
        
    .. tab:: unresolvable function
        
        .. code-block:: php
        
            <?php
            
            foobar();
        
        Will show diagnostic(s):
        
        - `ERROR`: Function "foobar" not found
        
    .. tab:: instanceof class
        
        .. code-block:: php
        
            <?php
            
            namespace Foo;
            
            if ($f instanceof Foobar) {
            }
        
        Will show diagnostic(s):
        
        - `ERROR`: Class "Foobar" not found
        
    .. tab:: unresolvable class
        
        .. code-block:: php
        
            <?php
            
            Foobar::class;
        
        Will show diagnostic(s):
        
        - `ERROR`: Class "Foobar" not found
        
    .. tab:: unresolvable namespaced function
        
        .. code-block:: php
        
            <?php
            
            namespace Foo;
            
            foobar();
        
        Will show diagnostic(s):
        
        - `ERROR`: Function "foobar" not found
        
``unused_import``
-----------------

Report if a use statement is not required.

.. tabs::

    .. tab:: aliased import
        
        .. code-block:: php
        
            <?php
            
            use Foobar as Barfoo;
            use Bagggg as Bazgar;
            
            new Barfoo();
            
        
        Will show diagnostic(s):
        
        - `WARN`: Name "Bazgar" is imported but not used
        
    .. tab:: imported in one namespace but used in another
        
        .. code-block:: php
        
            <?php
            
            namespace One {
                use Foo;
            }
            
            namespace Two {
                new Foo();
            }
        
        Will show diagnostic(s):
        
        - `WARN`: Name "Foo" is imported but not used
        
    .. tab:: compact use unused
        
        .. code-block:: php
        
            <?php
            
            use Foobar\{Barfoo};
            
            new Foobar();
        
        Will show diagnostic(s):
        
        - `WARN`: Name "Barfoo" is imported but not used
        
    .. tab:: namespaced unused imports
        
        .. code-block:: php
        
            <?php
            
            namespace Foo;
            
            use Bar\Foobar;
            use Bag\Boo;
            
            new Boo();
        
        Will show diagnostic(s):
        
        - `WARN`: Name "Foobar" is imported but not used
        
    .. tab:: unused imort
        
        .. code-block:: php
        
            <?php
            
            use Foobar;
        
        Will show diagnostic(s):
        
        - `WARN`: Name "Foobar" is imported but not used
        
``deprecated usage``
--------------------

Report when a deprecated symbol (class, method, constant, function etc) is used.

.. tabs::

    .. tab:: deprecated class
        
        .. code-block:: php
        
            <?php
            
            /** @deprecated */
            class Deprecated {
                public static foo(): void {}
            }
            
            class NotDeprecated {
                public static foo(): void {}
            }
            
            $fo = new Deprecated();
            Deprecated::foo();
            new NotDeprecated();
        
        Will show diagnostic(s):
        
        - `WARN`: Call to deprecated class "Deprecated"
        - `WARN`: Call to deprecated class "Deprecated"
        
    .. tab:: deprecated constant
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
                /** @deprecated This is deprecated */
                const FOO = 'BAR';
            
                const BAR = 'BAR';
            
                public function foo(Closure $foobar) {
                    $fo = self::FOO;
                    $ba = self::BAR;
                }
            }
        
        Will show diagnostic(s):
        
        - `WARN`: Call to deprecated constant "FOO": This is deprecated
        
    .. tab:: deprecated enum
        
        .. code-block:: php
        
            <?php
            
            /** @deprecated */
            enum Deprecated {
                case FOO;
            }
            
            enum NotDeprecated {
                case BAR;
            }
            
            $fo = Deprecated::FOO();
            Deprecated::foo();
            new NotDeprecated();
        
        Will show diagnostic(s):
        
        - `WARN`: Call to deprecated enum "Deprecated"
        - `WARN`: Call to deprecated enum "Deprecated"
        
    .. tab:: deprecated function
        
        .. code-block:: php
        
            <?php
            
            /** @deprecated */
            function bar(): void {}
            
            function notDeprecated(): void {}
            
            bar();
            
            notDeprecated();
        
        Will show diagnostic(s):
        
        - `WARN`: Call to deprecated function "bar"
        
    .. tab:: deprecated method
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
                public function foo(Closure $foobar) {
                    $this->deprecated();
                    $this->notDeprecated();
                }
            
                /** @deprecated This is deprecated */
                public function deprecated(): void {}
            
                public function notDeprecated(): void {}
            }
        
        Will show diagnostic(s):
        
        - `WARN`: Call to deprecated method "deprecated": This is deprecated
        
    .. tab:: deprecated on trait
        
        .. code-block:: php
        
            <?php
            
            trait FoobarTrait {
                /** @deprecated This is deprecated */
                public function deprecated(): void {}
            }
            
            class Foobar
            {
                use FoobarTrait;
                public function foo(Closure $foobar) {
                    $this->deprecated();
                    $this->notDeprecated();
                }
            
                public function notDeprecated(): void {}
            }
        
        Will show diagnostic(s):
        
        - `WARN`: Call to deprecated method "deprecated": This is deprecated
        
    .. tab:: deprecated on property
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
                /** @deprecated This is deprecated */
                public string $deprecated;
            
                public string $notDeprecated;
            
                public function foo(Closure $foobar) {
                    $fo = $this->deprecated;
                    $ba = $this->notDeprecated;
                }
            }
        
        Will show diagnostic(s):
        
        - `WARN`: Call to deprecated property "deprecated": This is deprecated
        
``undefined_variable``
----------------------

Report if a variable is undefined and suggest variables with similar names.

.. tabs::

    .. tab:: undefined variable
        
        .. code-block:: php
        
            <?php
            
            $zebra = 'one';
            $foa = 'two';
            
            if ($foo) {
            }
        
        Will show diagnostic(s):
        
        - `ERROR`: Undefined variable "$foo", did you mean "$foa"
        
    .. tab:: many undefined variables
        
        .. code-block:: php
        
            <?php
            
            $foz = 'one';
            $foa = 'two';
            $fob = 'three';
            
            if ($foo) {
            }
        
        Will show diagnostic(s):
        
        - `ERROR`: Undefined variable "$foo", did you mean one of "$foz", "$foa", "$fob"
        
    .. tab:: undefined and no suggestions
        
        .. code-block:: php
        
            <?php
            
            if ($foa) {
            }
        
        Will show diagnostic(s):
        
        - `ERROR`: Undefined variable "$foa"
        