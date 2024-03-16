<?php

namespace Phpactor\Completion\Tests\Unit\Bridge\WorseReflection\Completor;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Bridge\TolerantParser\TolerantArrayCompletor;
use Phpactor\Completion\Bridge\WorseReflection\Completor\ContextSensitiveCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

class ContextSensitiveCompletorTest extends TestCase
{
    /**
     * @dataProvider provideComplete
     * @param string[] $suggestions
     * @param string[] $expected
     */
    public function testComplete(array $suggestions, string $source, array $expected): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new Parser())->parseSourceFile($source);
        $node = $node->getDescendantNodeAtPosition($offset);
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        $inner = new TolerantArrayCompletor(array_map(
            fn (string $name) => Suggestion::createWithOptions($name, ['name_import' => $name]),
            $suggestions
        ));
        $suggestions = iterator_to_array((new ContextSensitiveCompletor(
            $inner,
            $reflector
        ))->complete(
            $node,
            TextDocumentBuilder::create($source)->build(),
            ByteOffset::fromInt($offset)
        ));
        self::assertEquals($expected, array_map(
            fn (Suggestion $suggestion) => $suggestion->fqn(),
            $suggestions
        ));
    }
    /**
     * @return Generator<string,array{array<int,string>,string,array<int,string>}>
     */
    public static function provideComplete(): Generator
    {
        yield 'method call' => [
            [
                'Bar\Foo',
                'Bar\Obj',
            ],
            <<<'EOT'
                <?php
                namespace Bar;
                class Obj {}
                class Foo { public function bar(Obj $obj){}}

                $f = new Foo();
                $f->bar(<>)
                EOT,
            [
                'Bar\Obj',
            ],
        ];
        yield 'namespaced open method call' => [
            [
                'Bar\Foo',
                'Bar\Obj',
            ],
            <<<'EOT'
                <?php
                namespace Bar;
                class Obj {}
                class Foo { public function bar(Obj $obj){}}

                $f = new Foo();
                $f->bar(<>
                EOT,
            [
                'Bar\Obj',
            ],
        ];
        yield 'no namespace' => [
            [
                'Foo',
                'Obj',
            ],
            <<<'EOT'
                <?php
                class Obj {}
                class Foo { public function bar(Obj $obj){}}

                $f = new Foo();
                $f->bar(<>
                EOT,
            [
                'Obj',
            ],
        ];
        yield 'partial' => [
            [
                'Foo',
                'Obj',
            ],
            <<<'EOT'
                <?php
                class Obj {}
                class Foo { public function bar(Obj $obj){}}

                $f = new Foo();
                $f->bar(O<>
                EOT,
            [
                'Obj',
            ],
        ];
        yield 'no type hint' => [
            [
                'Foo',
                'Obj',
            ],
            <<<'EOT'
                <?php
                class Obj {}
                class Foo { public function bar($obj){}}

                $f = new Foo();
                $f->bar(O<>
                EOT,
            [
                'Foo',
                'Obj',
            ],
        ];
        yield '2nd arg' => [
            [
                'Obj',
                'Baz',
            ],
            <<<'EOT'
                <?php
                class Obj {}
                class Baz {}
                class Foo { public function bar(Obj $obj, Baz $baz){}}

                $f = new Foo();
                $f->bar(Obj::new(),<>
                EOT,
            [
                'Baz',
            ],
        ];
        yield '2nd arg partial' => [
            [
                'Obj',
                'Baz',
            ],
            <<<'EOT'
                <?php
                class Obj {}
                class Baz {}
                class Foo { public function bar(Obj $obj, Baz $baz){}}

                $f = new Foo();
                $f->bar(Obj::new(),B<>
                EOT,
            [
                'Baz',
            ],
        ];
        yield 'variadic' => [
            [
                'Obj',
                'Baz',
            ],
            <<<'EOT'
                <?php
                class Obj {}
                class Baz {}
                class Foo { public function bar(Obj $obj, Baz ...$baz){}}

                $f = new Foo();
                $f->bar(Obj::new(),B<>
                EOT,
            [
                'Baz',
            ],
        ];
        yield 'new' => [
            [
                'Obj',
                'Baz',
            ],
            <<<'EOT'
                <?php
                class Obj {}
                class Baz {}
                class Foo { public function bar(Obj $obj, Baz ...$baz){}}

                $f = new Foo();
                $f->bar(new O<>
                EOT,
            [
                'Obj',
            ],
        ];
        yield 'enum' => [
            [
                'Obj',
                'Baz',
            ],
            <<<'EOT'
                <?php
                enum Obj {}
                class Baz {}
                class Foo { public function bar(Obj $obj, Baz ...$baz){}}

                $f = new Foo();
                $f->bar(new O<>
                EOT,
            [
                'Obj',
            ],
        ];
        yield 'static' => [
            [
                'Obj',
                'Baz',
            ],
            <<<'EOT'
                <?php
                enum Obj {}
                class Baz {}
                class Foo { public static function bar(Obj $obj, Baz ...$baz){}}

                Foo::bar(new O<>
                EOT,
            [
                'Obj',
            ],
        ];
        yield 'unresolvable method' => [
            [
                'Obj',
                'Baz',
            ],
            <<<'EOT'
                <?php
                class Obj {}
                class Baz {}
                class Foo { public function boo(){}}

                $f = new Foo();
                $f->bar(new O<>
                EOT,
            [
                'Obj',
                'Baz',
            ],
        ];
        yield 'constructor argument' => [
            [
                'Object1',
                'Object2',
            ],
            <<<'EOT'
                <?php
                class Object2 {}
                interface Object3 {}
                class Object1 implements Object3 {}
                class Foo { public function __construct(Object3 $foo){}}

                $f = new Foo(new O<>);
                EOT,
            [
                'Object1',
            ],
        ];
    }
}
