.. _diagnostics:

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
        
        Diagnostic(s):
        
        - ``ERROR``: ``Method "bar" does not exist on class "Foobar"``
        
    .. tab:: missing method for static invocation
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
            }
            
            Foobar::bar();
        
        Diagnostic(s):
        
        - ``ERROR``: ``Method "bar" does not exist on class "Foobar"``
        
    .. tab:: missing enum case
        
        .. code-block:: php
        
            <?php
            
            enum Foobar
            {
                case Foo;
            }
            
            Foobar::Foo;
            Foobar::Bar;
        
        Diagnostic(s):
        
        - ``ERROR``: ``Case "Bar" does not exist on enum "Foobar"``
        
    .. tab:: enum contains const and case
        
        .. code-block:: php
        
            <?php
            
            enum Foobar
            {
                case Foo;
                public const Bar = 'Bar';
            }
            
            Foobar::Foo;
            Foobar::Bar;
        
    .. tab:: enum static method not existing
        
        .. code-block:: php
        
            <?php
            
            enum Foobar
            {
            }
            
            Foobar::foobar();
        
        Diagnostic(s):
        
        - ``ERROR``: ``Method "foobar" does not exist on enum "Foobar"``
        
    .. tab:: missing constant on class
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
                const FOO = 'bar';
            }
            
            Foobar::FOO;
            Foobar::BAR;
        
        Diagnostic(s):
        
        - ``ERROR``: ``Constant "BAR" does not exist on class "Foobar"``
        
    .. tab:: missing property on class is not supported yet
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
                public int $foo;
            }
            
            $f = new Foobar();
            $f->foo = 12;
            $f->barfoo = 'string';
        
``docblock_missing_return``
---------------------------

Report when a method has a return type should be augmented by a docblock tag

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
        
        Diagnostic(s):
        
        - ``WARN``: ``Method "foo" is missing docblock return type: string``
        
``docblock_missing_param``
--------------------------

Report when a method has a parameter with a type that should be augmented by a docblock tag.

.. tabs::

    .. tab:: closure
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
                public function foo(Closure $foobar) {
                }
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Method "foo" is missing @param $foobar``
        
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
        
        Diagnostic(s):
        
        - ``WARN``: ``Method "foo" is missing @param $foobar``
        
    .. tab:: iterable
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
                public function foo(iterable $foobar) {
                }
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Method "foo" is missing @param $foobar``
        
    .. tab:: array
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
                public function foo(array $foobar) {
                }
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Method "foo" is missing @param $foobar``
        
    .. tab:: no false positive for vardoc on promoted property
        
        .. code-block:: php
        
            <?php
            
            class Foobar
            {
                public function __construct(
                    /**
                     * @var array<'GET'|'POST'>
                     */
                    private array $foobar,
                    private array $barfoo
                ) {
                }
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Method "__construct" is missing @param $barfoo``
        
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
        
        Diagnostic(s):
        
        - ``WARN``: ``Property "bar" has not been defined``
        
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
        
        Diagnostic(s):
        
        - ``WARN``: ``Missing return type `string```
        
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
        
        Diagnostic(s):
        
        - ``WARN``: ``Method "foo" is missing return type and the type could not be determined``
        
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
        
        Diagnostic(s):
        
        - ``ERROR``: ``Class "Foobar" not found``
        
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
        
        Diagnostic(s):
        
        - ``ERROR``: ``Class "Foo" not found``
        
    .. tab:: unresolvable function
        
        .. code-block:: php
        
            <?php
            
            foobar();
        
        Diagnostic(s):
        
        - ``ERROR``: ``Function "foobar" not found``
        
    .. tab:: instanceof class
        
        .. code-block:: php
        
            <?php
            
            namespace Foo;
            
            if ($f instanceof Foobar) {
            }
        
        Diagnostic(s):
        
        - ``ERROR``: ``Class "Foobar" not found``
        
    .. tab:: unresolvable class
        
        .. code-block:: php
        
            <?php
            
            Foobar::class;
        
        Diagnostic(s):
        
        - ``ERROR``: ``Class "Foobar" not found``
        
    .. tab:: unresolvable namespaced function
        
        .. code-block:: php
        
            <?php
            
            namespace Foo;
            
            foobar();
        
        Diagnostic(s):
        
        - ``ERROR``: ``Function "foobar" not found``
        
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
            
        
        Diagnostic(s):
        
        - ``WARN``: ``Name "Bazgar" is imported but not used``
        
    .. tab:: imported in one namespace but used in another
        
        .. code-block:: php
        
            <?php
            
            namespace One {
                use Foo;
            }
            
            namespace Two {
                new Foo();
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Name "Foo" is imported but not used``
        
    .. tab:: compact use unused
        
        .. code-block:: php
        
            <?php
            
            use Foobar\{Barfoo};
            
            new Foobar();
        
        Diagnostic(s):
        
        - ``WARN``: ``Name "Barfoo" is imported but not used``
        
    .. tab:: namespaced unused imports
        
        .. code-block:: php
        
            <?php
            
            namespace Foo;
            
            use Bar\Foobar;
            use Bag\Boo;
            
            new Boo();
        
        Diagnostic(s):
        
        - ``WARN``: ``Name "Foobar" is imported but not used``
        
    .. tab:: unused import
        
        .. code-block:: php
        
            <?php
            
            use Foobar;
        
        Diagnostic(s):
        
        - ``WARN``: ``Name "Foobar" is imported but not used``
        
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
        
        Diagnostic(s):
        
        - ``WARN``: ``Call to deprecated class "Deprecated"``
        - ``WARN``: ``Call to deprecated class "Deprecated"``
        
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
        
        Diagnostic(s):
        
        - ``WARN``: ``Call to deprecated constant "FOO": This is deprecated``
        
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
        
        Diagnostic(s):
        
        - ``WARN``: ``Call to deprecated enum "Deprecated"``
        - ``WARN``: ``Call to deprecated enum "Deprecated"``
        
    .. tab:: deprecated function
        
        .. code-block:: php
        
            <?php
            
            /** @deprecated */
            function bar(): void {}
            
            function notDeprecated(): void {}
            
            bar();
            
            notDeprecated();
        
        Diagnostic(s):
        
        - ``WARN``: ``Call to deprecated function "bar"``
        
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
        
        Diagnostic(s):
        
        - ``WARN``: ``Call to deprecated method "deprecated": This is deprecated``
        
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
        
        Diagnostic(s):
        
        - ``WARN``: ``Call to deprecated method "deprecated": This is deprecated``
        
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
        
        Diagnostic(s):
        
        - ``WARN``: ``Call to deprecated property "deprecated": This is deprecated``
        
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
        
        Diagnostic(s):
        
        - ``ERROR``: ``Undefined variable "$foo", did you mean "$foa"``
        
    .. tab:: many undefined variables
        
        .. code-block:: php
        
            <?php
            
            $foz = 'one';
            $foa = 'two';
            $fob = 'three';
            
            if ($foo) {
            }
        
        Diagnostic(s):
        
        - ``ERROR``: ``Undefined variable "$foo", did you mean one of "$foz", "$foa", "$fob"``
        
    .. tab:: this in anonymous class
        
        .. code-block:: php
        
                <?php
                new class
                {
                    public function foo(): void
                    {
                        $this
                    }
                };
        
    .. tab:: undefined and no suggestions
        
        .. code-block:: php
        
            <?php
            
            if ($foa) {
            }
        
        Diagnostic(s):
        
        - ``ERROR``: ``Undefined variable "$foa"``
        
    .. tab:: after for loop
        
        .. code-block:: php
        
            <?php
            
            $plainArray = [];
            $list = [];
            foreach ($plainArray as $index => $data) {
                $list[$index] = $data;
            }
            
            return $list;
        
``docblock_missing_extends_tag``
--------------------------------

Report when a class extends a generic class but does not provide an @extends tag.

.. tabs::

    .. tab:: extends class requiring generic annotation
        
        .. code-block:: php
        
            <?php
            
            /**
             * @template T
             */
            class NeedGeneric
            {
            }
            
            class Foobar extends NeedGeneric
            {
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Missing generic tag `@extends NeedGeneric<mixed>```
        
    .. tab:: does not provide enough arguments
        
        .. code-block:: php
        
            <?php
            
            /**
             * @template T
             * @template P
             */
            class NeedGeneric
            {
            }
            
            /**
             * @extends NeedGeneric<int>
             */
            class Foobar extends NeedGeneric
            {
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Generic tag `@extends NeedGeneric<int>` should be compatible with `@extends NeedGeneric<mixed,mixed>```
        
    .. tab:: does not provide any arguments
        
        .. code-block:: php
        
            <?php
            
            /**
             * @template T of int
             */
            class NeedGeneric
            {
            }
            
            /**
             * @extends NeedGeneric
             */
            class Foobar extends NeedGeneric
            {
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Generic tag `@extends NeedGeneric` should be compatible with `@extends NeedGeneric<int>```
        
    .. tab:: provides empty arguments
        
        .. code-block:: php
        
            <?php
            
            /**
             * @template T of int
             */
            class NeedGeneric
            {
            }
            
            /**
             * @extends NeedGeneric<>
             */
            class Foobar extends NeedGeneric
            {
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Missing generic tag `@extends NeedGeneric<int>```
        
    .. tab:: wrong class
        
        .. code-block:: php
        
            <?php
            
            /**
             * @template T of int
             */
            class NeedGeneric
            {
            }
            
            /**
             * @extends NeedGeneric<int>
             */
            class Foobar extends NeedGeneric
            {
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Missing generic tag `@extends NeedGeneric<int>```
        
    .. tab:: does not provide multiple arguments
        
        .. code-block:: php
        
            <?php
            
            /**
             * @template T
             * @template P
             * @template Q
             */
            class NeedGeneric
            {
            }
            
            /**
             * @extends NeedGeneric<int>
             */
            class Foobar extends NeedGeneric
            {
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Generic tag `@extends NeedGeneric<int>` should be compatible with `@extends NeedGeneric<mixed,mixed,mixed>```
        
``docblock_missing_implements_tag``
-----------------------------------

Report when a class extends a generic class but does not provide an @extends tag.

.. tabs::

    .. tab:: implements class requiring generic annotation
        
        .. code-block:: php
        
            <?php
            
            /**
             * @template T
             */
            interface NeedGeneric
            {
            }
            
            class Foobar implements NeedGeneric
            {
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Missing generic tag `@implements NeedGeneric<mixed>```
        
    .. tab:: does not provide enough arguments
        
        .. code-block:: php
        
            <?php
            
            /**
             * @template T
             * @template P
             */
            interface NeedGeneric
            {
            }
            
            /**
             * @implements NeedGeneric<int>
             */
            class Foobar implements NeedGeneric
            {
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Generic tag `@implements NeedGeneric<int>` should be compatible with `@implements NeedGeneric<mixed,mixed>```
        
    .. tab:: provides one but not another
        
        .. code-block:: php
        
            <?php
            
            /**
             * @template T
             */
            interface NeedGeneric1
            {
            }
            
            /**
             * @template T
             */
            interface NeedGeneric2
            {
            }
            
            
            /**
             * @implements NeedGeneric1<int>
             */
            class Foobar implements NeedGeneric1, NeedGeneric2
            {
            }
        
        Diagnostic(s):
        
        - ``WARN``: ``Missing generic tag `@implements NeedGeneric2<mixed>```
        