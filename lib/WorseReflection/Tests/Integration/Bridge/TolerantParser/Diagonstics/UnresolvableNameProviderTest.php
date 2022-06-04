<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Closure;
use Generator;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnresolvableNameProvider;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;

class UnresolvableNameProviderTest extends DiagnosticsTestCase
{
    protected function provider(): DiagnosticProvider
    {
        return new UnresolvableNameProvider(true);
    }

    public function checkUnresolvableName(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Class "Foobar" not found', $diagnostics->at(0)->message());
    }

    public function checkUnresolvableFunction(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Function "foobar" not found', $diagnostics->at(0)->message());
    }

    public function checkUnresolvableNamespacedFunction(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Function "foobar" not found', $diagnostics->at(0)->message());
    }

    public function checkReservedNames(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    public function checkConstants(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    public function checkClassNameConstant(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
    }

    /**
     * @dataProvider provideReturnsUnresolableFunctions
     * @dataProvider provideReturnsUnresolableClass
     * @dataProvider provideConstants
     */
    public function testReturnsUnresolableClass(string $manifest, ?Closure $assertion = null): void
    {
        $diagnostics = $this->diagnosticsFromManifest($manifest);
        if (null === $assertion) {
            self::assertCount(0, $diagnostics);
            return;
        }
        $assertion($diagnostics);
    }

    public function provideReturnsUnresolableClass(): Generator
    {
        yield 'no classes' => [
            <<<'EOT'
                // File: test.php
                <?
                EOT
        ];

        yield 'empty segment' => [
            <<<'EOT'
                // File: test.php
                <?php
                $a = new \
                EOT
        ];

        yield 'resolvable class in method' => [
            <<<'EOT'
                // File: test.php
                <?php class Foo { public function bar(Bar $bar) {} }
                EOT
            ,  function (Diagnostics $diagnostics) {
                self::assertCount(1, $diagnostics);
            }
        ];
        
        yield 'class imported in list' => [
            <<<'EOT'
                // File: test.php
                <?php use Bar\{Foo}; Foo::foo();
                // File: Bar.php
                <?php namespace Bar; class Foo {}
                EOT
        ];

        return;
        yield 'unresolvable class' => [
            <<<'EOT'
                // File: test.php
                <?php new NotFound();
                EOT
            ,[
                new NameWithByteOffset(
                    QualifiedName::fromString('NotFound'),
                    ByteOffset::fromInt(10)
                ),
            ]
        ];

        yield 'namespaced unresolvable class' => [
            <<<'EOT'
                // File: test.php
                <?php namespace Foo; new NotFound();
                EOT
            , [
                new NameWithByteOffset(
                    QualifiedName::fromString('Foo\\NotFound'),
                    ByteOffset::fromInt(25)
                ),
            ]
        ];

        yield 'multiple unresolvable classes' => [
            <<<'EOT'
                // File: test.php
                <?php 

                new Bar\NotFound();

                class Bar {}

                new NotFound36();
                EOT
            ,
            [
                new NameWithByteOffset(
                    QualifiedName::fromString('Bar\\NotFound'),
                    ByteOffset::fromInt(12)
                ),
                new NameWithByteOffset(
                    QualifiedName::fromString('NotFound36'),
                    ByteOffset::fromInt(47)
                ),
            ]
        ];

        yield 'interface not found' => [
            <<<'EOT'
                // File: test.php
                <?php 

                class Bar implements Sugar {}
                EOT
            ,
            [
                new NameWithByteOffset(
                    QualifiedName::fromString('Sugar'),
                    ByteOffset::fromInt(29)
                ),
            ]
        ];

        yield 'interface found' => [
            <<<'EOT'
                // File: Interface.php
                <?php

                interface Sugar {}
                // File: test.php
                <?php 

                class Bar implements Sugar {}
                EOT
            ,
            [
            ]
        ];

        yield 'interface not found but located source contains name' => [
            <<<'EOT'
                // File: Interface.php
                <?php

                Sugar::class;
                // File: test.php
                <?php 

                class Bar implements Sugar {}
                EOT
            ,
            [
                new NameWithByteOffset(
                    QualifiedName::fromString('Sugar'),
                    ByteOffset::fromInt(29)
                ),
            ]
        ];

        yield 'parent' => [
            <<<'EOT'
                // File: test.php
                <?php 

                class Bar extends Sugar {}
                EOT
            ,
            [
                new NameWithByteOffset(
                    QualifiedName::fromString('Sugar'),
                    ByteOffset::fromInt(26)
                ),
            ]
        ];

        yield 'unresolvable trait' => [
            <<<'EOT'
                // File: test.php
                <?php 

                class Bar {
                    use Sugar;
                }
                EOT
            ,
            [
                new NameWithByteOffset(
                    QualifiedName::fromString('Sugar'),
                    ByteOffset::fromInt(28)
                ),
            ]
        ];

        yield 'resolvable trait' => [
            <<<'EOT'
                // File: Interface.php
                <?php

                trait Sugar {}
                // File: test.php
                <?php 

                class Bar {
                    use Sugar;
                }
                EOT
            ,
            [
            ]
        ];

        if (defined('T_ENUM')) {
            yield 'unresolvable enum' => [
                <<<'EOT'
                    // File: test.php
                    <?php 

                    enum Bar extends Sugar {
                    }
                    EOT
                ,
                [
                    new NameWithByteOffset(
                        QualifiedName::fromString('Sugar'),
                        ByteOffset::fromInt(25)
                    ),
                ]
            ];

            yield 'resolvable enum' => [
                <<<'EOT'
                    // File: Sugar.php
                    <?php 

                    enum Sugar {
                    }
                    // File: test.php
                    <?php 

                    enum Bar extends Sugar {
                    }
                    EOT
                ,
                [
                ]
            ];
        }

        yield 'filter duplicates' => [
            <<<'EOT'
                // File: test.php
                <?php 

                class Bar {
                    use Sugar;
                }
                class Baz {
                    use Sugar;
                }
                EOT
            ,
            [
                new NameWithByteOffset(
                    QualifiedName::fromString('Sugar'),
                    ByteOffset::fromInt(28)
                ),
            ]
        ];

        yield 'method reutrn type' => [
            <<<'EOT'
                // File: test.php
                <?php

                namespace Foobar;

                class Barfoo { 
                    public function foo(): Baz {}
                }
                EOT
            ,
            [
                new NameWithByteOffset(
                    QualifiedName::fromString('Foobar\\Baz'),
                    ByteOffset::fromInt(69)
                ),
            ]
        ];

        yield 'resolvable fully qualified trait' => [
            <<<'EOT'
                // File: test.php
                <?php 

                namespace Test;

                class Bar {
                    use \App\Sugar;
                }
                // File: Sugar.php
                <?php 

                namespace App;

                trait Sugar {
                }
                EOT
            ,
        ];

        yield 'resolvable partially qualified trait' => [
            <<<'EOT'
                // File: test.php
                <?php 

                namespace Test;

                use App;

                class Bar {
                    use App\Sugar;
                }
                // File: Sugar.php
                <?php 

                namespace App;

                trait Sugar {
                }
                EOT
            ,
        ];

        yield 'resolvable unqualified trait' => [
            <<<'EOT'
                // File: test.php
                <?php 

                namespace Test;

                use App\Sugar;

                class Bar {
                    use Sugar;
                }
                // File: Sugar.php
                <?php 

                namespace App;

                trait Sugar {
                }
                EOT
            ,
        ];

        yield 'resolvable alias trait' => [
            <<<'EOT'
                // File: test.php
                <?php 

                namespace Test;

                use App\Sugar as SweetSugar;

                class Bar {
                    use SweetSugar;
                }
                // File: Sugar.php
                <?php 

                namespace App;

                trait Sugar {
                }
                EOT
            ,
        ];

        yield 'external resolvable class' => [
            <<<'EOT'
                // File: Foobar.php
                <?php

                namespace Foobar;

                class Barfoo {}
                // File: test.php
                <?php 

                use Foobar\Barfoo;

                new Barfoo();
                EOT
            ,
            [
            ]
        ];

        yield 'reserved names' => [
            <<<'EOT'
                // File: test.php
                <?php

                namespace Foobar;

                class Barfoo { 
                    public function foo(): self {}
                    public function bar(): {
                        static::foo();
                        parent::foo();
                    }
                }
                EOT
            ,
            [
            ]
        ];

        if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
            yield 'attributes' => [
                <<<'EOT'
                    // File: test.php
                    <?php

                    namespace Foobar;

                    #[NotResolvable()]
                    class Barfoo { 
                    }
                    EOT
                ,
                [
                    new NameWithByteOffset(
                        QualifiedName::fromString('Foobar\NotResolvable'),
                        ByteOffset::fromInt(28)
                    ),
                ]
            ];
        }
    }

    public function provideReturnsUnresolableFunctions(): Generator
    {
        yield 'resolvable function' => [
            <<<'EOT'
                // File: test.php
                <?php function bar() {} bar();
                EOT
        ];

        yield 'unresolvable function' => [
            <<<'EOT'
                // File: test.php
                <?php foo();
                EOT
            ,
            function (Diagnostics $diagnostics) {
                self::assertCount(1, $diagnostics);
                self::assertEquals('Function "foo" not found', $diagnostics->at(0)->message());
            }
        ];

        yield 'namespaced unresolveable function' => [
            <<<'EOT'
                // File: test.php
                <?php namespace Foobar; foo();
                EOT
            ,
            function (Diagnostics $diagnostics) {
                self::assertCount(1, $diagnostics);
                self::assertEquals('Foobar\foo', $diagnostics->at(0)->name());
                self::assertEquals(24, $diagnostics->at(0)->range()->start()->toInt());
            }
        ];

        yield 'resolveable namespaced function' => [
            <<<'EOT'
                // File: test.php
                <?php namespace Foobar; function foo() {} foo();
                EOT
        ];

        yield 'function imported in list' => [
            <<<'EOT'
                // File: test.php
                <?php use function Bar\{foo}; foo();
                // File: Bar.php
                <?php namespace Bar; function foo() {}
                EOT
                , function (Diagnostics $diagnostics) {
                    self::assertCount(0, $diagnostics);
                }
        ];
    }

    public function provideConstants(): Generator
    {
        yield 'global constant' => [
            <<<'EOT'
                // File: test.php
                <?php namespace Foobar; INF;
                EOT
        ];
    }

    public function testSourceCanBeFoundButNoClassIsContainedInIt(): void
    {
        $reflector = ReflectorBuilder::create()->addDiagnosticProvider($this->provider())->addLocator(
            new StringSourceLocator(SourceCode::fromString(''))
        )->build();

        $found = $reflector->diagnostics(
            TextDocumentBuilder::create('<?php Foobar::class;')->build()
        );
        self::assertCount(1, $found);
    }

    public function testSourceCanBeFoundButNoFunctionIsContainedInIt(): void
    {
        $reflector = ReflectorBuilder::create()->addDiagnosticProvider($this->provider())->addLocator(
            new StringSourceLocator(SourceCode::fromString(''))
        )->build();

        $found = $reflector->diagnostics(
            TextDocumentBuilder::create('<?php barboo();')->build()
        );
        self::assertCount(1, $found);
    }
}
