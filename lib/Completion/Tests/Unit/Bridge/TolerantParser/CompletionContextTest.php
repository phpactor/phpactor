<?php

namespace Phpactor\Completion\Tests\Unit\Bridge\TolerantParser;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Bridge\TolerantParser\CompletionContext;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;

class CompletionContextTest extends TestCase
{
    /**
     * @dataProvider provideExpression
     */
    public function testExpression(string $source, bool $expected): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new Parser())->parseSourceFile($source)->getDescendantNodeAtPosition((int)$offset);
        self::assertEquals($expected, CompletionContext::expression($node));
    }

    /**
     * @return Generator<string,array<int,mixed>>
     */
    public function provideExpression(): Generator
    {
        yield 'not class clause' => [
            '<?php class Foo i<>',
            false,
        ];

        yield 'not class clause 2' => [
            '<?php class Foo <>',
            false,
        ];
        yield 'not class clause 3' => [
            '<?php class Foo implements Foo <>',
            false,
        ];
        yield 'not class clause 4' => [
            '<?php class Foo implements Foo,Bar <>',
            false,
        ];

        yield 'not class clause on new line' => [
            "<?php class Foo \ni<>",
            false,
        ];

        yield 'not class member body' => [
            '<?php class Foo { A<>',
            false,
        ];

        yield 'not class member body after property' => [
            '<?php class Foo { private $foo; A<>',
            false,
        ];
        yield 'not after method 1' => [
            '<?php class Foo { public function bar() {} A<> }',
            false,
        ];
        yield 'not after method 2' => [
            '<?php class Foo { private $foo; public function bar() {} public function boo() {} A<> public functoin baz() {}}',
            false,
        ];
        yield 'not after method 3' => [
            "<?php class Foo { public function bar() {\necho 'hello world'; \$bar = 12;} A<> }",
            false,
        ];

        yield 'in class method body 1' => [
            '<?php class Foo { public function foo() { A<> }',
            true
        ];
        yield 'in class method body 2' => [
            '<?php class Foo { public function bar() { if (true) { return false; } A<> } }',
            true,
        ];
    }

    /**
     * @dataProvider provideClassMemberBody
     */
    public function testClassMemberBody(string $source, bool $expected): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new Parser())->parseSourceFile($source)->getDescendantNodeAtPosition((int)$offset);
        self::assertEquals($expected, CompletionContext::classMembersBody($node));
    }

    /**
     * @return Generator<string,array<int,mixed>>
     */
    public function provideClassMemberBody(): Generator
    {
        yield 'property' => [
            '<?php class Foo { pri<> }',
            true
        ];
        yield 'visibility 1' => [
            '<?php class Foo { <> }',
            true
        ];
        yield 'visibility 2' => [
            '<?php class Foo { private <> }',
            true
        ];
        yield 'visibility 3' => [
            '<?php class Foo { private Foob<> }',
            true,
        ];

        // todo...
        yield 'visibility 4' => [
            '<?php class Foo { private Foobles <> }',
            true,
        ];
        yield 'visibility 5' => [
            '<?php class Foo { private Foobles $<> }',
            false,
        ];
    }

    /**
     * @dataProvider provideClassClause
     */
    public function testClassClause(string $source, bool $expected): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new Parser())->parseSourceFile($source)->getDescendantNodeAtPosition((int)$offset);
        self::assertEquals($expected, CompletionContext::classClause($node, ByteOffset::fromInt((int)$offset)));
    }

    /**
     * @return Generator<string,array<int,mixed>>
     */
    public function provideClassClause(): Generator
    {
        yield 'clause' => [
            '<?php class Foo i<>',
            true,
        ];

        yield 'clause 2' => [
            '<?php class Foo <>',
            true,
        ];
        yield 'clause 3' => [
            '<?php class Foo implements Foo <>',
            true,
        ];
        yield 'clause 4' => [
            '<?php class Foo extends Foo <>',
            true,
        ];
        yield 'clause 5' => [
            '<?php class Foo extends Foo, Bar <>',
            true,
        ];
    }
}
