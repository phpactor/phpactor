<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseSignatureHelper;
use Phpactor\Completion\Core\Exception\CouldNotHelpWithSignature;
use Phpactor\Completion\Core\ParameterInformation;
use Phpactor\Completion\Core\SignatureHelp;
use Phpactor\Completion\Core\SignatureInformation;
use Phpactor\Completion\Tests\Integration\IntegrationTestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseSignatureHelperTest extends IntegrationTestCase
{
    #[DataProvider('provideSignatureHelper')]
    #[DataProvider('providePhp8')]
    public function testSignatureHelper(string $source, ?SignatureHelp $expected): void
    {
        if ($expected === null) {
            $this->expectException(CouldNotHelpWithSignature::class);
        }

        [ $source, $offset ] = ExtractOffset::fromSource($source);
        $source = TextDocumentBuilder::create($source)->language('php')->uri('file:///tmp/test')->build();
        $reflector = ReflectorBuilder::create()->addSource($source)->build();

        $helper = new WorseSignatureHelper($reflector, $this->formatter());

        $help = $helper->signatureHelp(
            $source,
            ByteOffset::fromInt((int)$offset)
        );

        $this->assertEquals($expected, $help);
    }

    public static function provideSignatureHelper(): Generator
    {
        yield 'not a signature' => [
            '<?php echo "h<>ello";',
            null
        ];

        yield 'not existing function' => [
            '<?php foobar(<>',
            null
        ];

        yield 'function signature with no parameters' => [
            '<?php function hello() {}; hello(<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'hello()',
                    []
                )],
                0
            )
        ];

        yield 'function signature with no parameters inside another function' => [
            '<?php function hello() {}; function hi() {}; hello(hi(<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'hi()',
                    []
                )],
                0
            )
        ];

        yield 'function signature with no parameters inside another function 2' => [
            '<?php function hello() {}; function hi() {}; hello(hi(<>)',
            new SignatureHelp(
                [new SignatureInformation(
                    'hi()',
                    []
                )],
                0
            )
        ];

        yield 'constructor signature with no parameters inside another function' => [
            '<?php function hello() {}; class Hi { function __construct() {} }; hello(new Hi(<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'pub __construct()',
                    []
                )],
                0
            )
        ];

        yield 'constructor signature with no parameters inside another function 2' => [
            '<?php function hello() {}; class Hi { function __construct() {} }; hello(new Hi(<>)',

            new SignatureHelp(
                [new SignatureInformation(
                    'pub __construct()',
                    []
                )],
                0
            )
        ];

        yield 'static method signature with no parameters inside another function' => [
            '<?php function hello() {}; class Hi { public static function hi() }; hello(Hi::hi(<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'pub hi()',
                    []
                )],
                0
            )
        ];
        yield 'self method signature with no parameters inside another function' => [
            '<?php function hello() {}; class Hi { public static function hi() public function f() { self::hi(<> }',
            new SignatureHelp(
                [new SignatureInformation(
                    'pub hi()',
                    []
                )],
                0
            )
        ];

        yield 'function with parameter' => [
            '<?php function hello(string $foo) {}; hello(<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'hello(string $foo)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                    ]
                )],
                0,
                0
            )
        ];

        yield 'function with parameters' => [
            '<?php function hello(string $foo, int $bar) {}; hello(<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'hello(string $foo, int $bar)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                    ]
                )],
                0,
                0
            )
        ];

        yield 'function with parameters, 2nd active' => [
            '<?php function hello(string $foo, int $bar) {}; hello("hello",<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'hello(string $foo, int $bar)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                    ]
                )],
                0,
                1
            )
        ];

        yield 'function with parameters, 2nd active and already filled' => [
            '<?php function hello(string $foo, int $bar) {}; hello("hello", $test<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'hello(string $foo, int $bar)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                    ]
                )],
                0,
                1
            )
        ];

        yield 'function with parameters, 2nd active within other nodes' => [
            '<?php function hello(string $foo, int $bar) {}; hello("hello", [1, [<>]]',
            new SignatureHelp(
                [new SignatureInformation(
                    'hello(string $foo, int $bar)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                    ]
                )],
                0,
                1
            )
        ];

        yield 'function with parameters, 2nd active on multiple lines' => [
            <<<'EOT'
                <?php
                function hello(string $foo, int $bar) {};
                hello(
                    "hello",
                    <>
                );
                EOT,
            new SignatureHelp(
                [new SignatureInformation(
                    'hello(string $foo, int $bar)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                    ]
                )],
                0,
                1
            )
        ];

        yield 'nested function with parameters, 2nd active' => [
            <<<'EOT'
                <?php
                function hello(string $foo, int $bar) {};
                function goodbye(string $good, int $by) {};
                hello(goodbye(
                    "hello",
                    <>
                ));
                EOT,
            new SignatureHelp(
                [new SignatureInformation(
                    'goodbye(string $good, int $by)',
                    [
                        new ParameterInformation('good', 'string $good'),
                        new ParameterInformation('by', 'int $by'),
                    ]
                )],
                0,
                1
            )
        ];

        yield 'nested function on the second function, 2nd arg of 1st call active' => [
            <<<'EOT'
                <?php
                function hello(string $foo, int $bar) {};
                function goodbye(string $good, int $by) {};
                hello(
                    "hello",
                    goo<>dbye("good", "by")
                );
                EOT,
            new SignatureHelp(
                [new SignatureInformation(
                    'hello(string $foo, int $bar)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                    ]
                )],
                0,
                1
            )
        ];

        yield 'function with parameters, 1st contains comma and 2nd active' => [
            '<?php function hello(string $foo, int $bar, bool $foobar) {}; hello("hello, ",<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'hello(string $foo, int $bar, bool $foobar)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                        new ParameterInformation('foobar', 'bool $foobar'),
                    ]
                )],
                0,
                1
            )
        ];

        yield 'static method call' => [
            '<?php class Foo { static function hello(string $foo, int $bar) {} }; Foo::hello(<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'pub hello(string $foo, int $bar)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                    ]
                )],
                0,
                0
            )
        ];

        yield 'static method call on non existing class' => [
            '<?php class Foo::hello(<>',
            null
        ];

        yield 'static method call, 2nd active' => [
            '<?php class Foo { static function hello(string $foo, int $bar) {} }; Foo::hello("hello",<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'pub hello(string $foo, int $bar)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                    ]
                )],
                0,
                1
            )
        ];

        yield 'static method call, on variable' => [
            '<?php $foo = "Foo"; $foo::hello("hello",<>',
            null
        ];

        yield 'instance method' => [
            '<?php class Foo { function hello(string $foo, int $bar) {} }; $foo = new Foo(); $foo->hello(<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'pub hello(string $foo, int $bar)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                    ]
                )],
                0,
                0
            )
        ];

        yield 'instance from an interface' => [
            '<?php interface Foo { function hello(string $foo, int $bar): void }; function (Foo $foo) { $foo->hello(<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'pub hello(string $foo, int $bar): void',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                    ]
                )],
                0,
                0
            )
        ];

        yield 'non existing method throws exception' => [
            '<?php interface Foo { function hello(string $foo, int $bar): void }; function (Foo $foo) { $foo->bads(<>',
            null,
        ];

        yield 'class no constructor' => [
            '<?php class Foo {}; new Foo(<>',
            null
        ];

        yield 'class with construct' => [
            '<?php class Foo {public function __construct(string $foo) {}}; new Foo(<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'pub __construct(string $foo)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                    ]
                )],
                0,
                0
            )
        ];

        yield 'class with construct 2nd pos' => [
            '<?php class Foo {public function __construct(string $foo, int $bar) {}}; new Foo("asd",<>',
            new SignatureHelp(
                [new SignatureInformation(
                    'pub __construct(string $foo, int $bar)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                    ]
                )],
                0,
                1
            )
        ];

        yield 'class with construct 2nd pos after space' => [
            '<?php class Foo {public function __construct(string $foo, int $bar) {}}; new Foo("asd", <>',
            new SignatureHelp(
                [new SignatureInformation(
                    'pub __construct(string $foo, int $bar)',
                    [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                    ]
                )],
                0,
                1
            )
        ];

        yield 'class with namespaced' => [
            <<<'EOT'
                <?php
                namespace Bar {
                    class Foo {
                        public function __construct(string $foo, int $bar)
                        {}
                    }
                };
                namespace Foo {

                    new \Bar\Foo("asd",<>
                }
                EOT
        ,
        new SignatureHelp(
            [new SignatureInformation(
                'pub __construct(string $foo, int $bar)',
                [
                        new ParameterInformation('foo', 'string $foo'),
                        new ParameterInformation('bar', 'int $bar'),
                    ]
            )],
            0,
            1
        )
        ];

        yield 'non-existing static member' => [
            <<<'EOT'
                <?php
                class Foo {}

                Foo::bar(<>);
                EOT
        , null
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public static function providePhp8(): Generator
    {
        yield 'attribute 1' => [
            <<<'EOT'
                <?php

                #[Attribute]
                class FooAttr
                {
                    public function __construct(string $bar);
                }

                #[FooAttr(<>
                class Bar {}
                EOT
        , new SignatureHelp(
            [new SignatureInformation(
                'pub __construct(string $bar)',
                [
                        new ParameterInformation('bar', 'string $bar'),
                    ]
            )],
            0,
            0
        )
        ];

        yield 'attribute 2' => [
            <<<'EOT'
                <?php

                #[Attribute]
                class FooAttr
                {
                    public function __construct(string $bar, string $baz);
                }

                #[FooAttr($bar,<>
                class Bar {}
                EOT
        , new SignatureHelp(
            [new SignatureInformation(
                'pub __construct(string $bar, string $baz)',
                [
                        new ParameterInformation('bar', 'string $bar'),
                        new ParameterInformation('baz', 'string $baz'),
                ]
            )],
            0,
            1
        )
        ];
    }
}
