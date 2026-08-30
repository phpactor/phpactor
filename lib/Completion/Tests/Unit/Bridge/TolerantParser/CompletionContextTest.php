<?php

namespace Phpactor\Completion\Tests\Unit\Bridge\TolerantParser;

use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Bridge\TolerantParser\CompletionContext;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;

class CompletionContextTest extends TestCase
{
    #[DataProvider('provideExpression')]
    public function testExpression(string $source, bool $expected): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new TolerantAstProvider())->parseString($source)->getDescendantNodeAtPosition($offset);
        self::assertEquals($expected, CompletionContext::expression($node));
    }

    /**
     * @return Generator<string,array<int,mixed>>
     */
    public static function provideExpression(): Generator
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
            '<?php class Foo { private $foo; public function bar() {} public function boo() {} A<> public function baz() {}}',
            false,
        ];
        yield 'not after method 3' => [
            "<?php class Foo { public function bar() {\necho 'hello world'; \$bar = 12;} A<> }",
            false,
        ];
        yield 'not between if condition and body' => [
            '<?php if(1)<> {}',
            false,
        ];
        yield 'not in variable' => [
            '<?php $<>',
            false,
        ];
        yield 'not in string literal' => [
            '<?php strlen(\'<>',
            false,
        ];
        yield 'not in scoped property access expr' => [
            '<?php class Foo { public function foo() { $this->foo(self::<>) }',
            false,
        ];

        yield 'in class method body 1' => [
            '<?php class Foo { public function foo() { A<> }',
            true,
        ];
        yield 'in class method body 2' => [
            '<?php class Foo { public function bar() { if (true) { return false; } A<> } }',
            true,
        ];
        yield 'in function call' => [
            '<?php class Foo { public function foo() { $this->foo(<>) }',
            true,
        ];
        yield 'in foreach' => [
            '<?php class Foo { public function bar() { if (true) { return false; } foreach(<> } }',
            true,
        ];
    }

    #[DataProvider('provideStatement')]
    public function testStatement(string $source, bool $expected): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new TolerantAstProvider())->get(TextDocumentBuilder::fromString($source))->getDescendantNodeAtPosition((int)$offset);
        self::assertEquals($expected, CompletionContext::statement($node, ByteOffset::fromInt($offset)));
    }

    /**
     * @return Generator<string,array<int,mixed>>
     */
    public static function provideStatement(): Generator
    {
        yield 'root' => [
            '<?php <>',
            true,
        ];
        yield 'in namespace' => [
            '<?php namespace X; <>',
            false, // disabled while no idea how to distinguish this case from that if the cursor is inside comment
        ];
        yield 'in comment' => [
            '<?php 
            namespace X; 
            /**
            <>
            */',
            false,
        ];
        yield 'statement property' => [
            '<?php class Foo { pri<> }',
            false,
        ];
        yield 'statement visibility 1' => [
            '<?php class Foo { <> }',
            false,
        ];
        yield 'statement visibility 2' => [
            '<?php class Foo { private <> }',
            false,
        ];
        yield 'visibility 3' => [
            '<?php class Foo { private Foob<> }',
            false,
        ];
        yield 'statement method body' => [
            '<?php class Foo { private function foo() { <> } }',
            true,
        ];
        yield 'nested statement method body' => [
            '<?php class Foo { private function foo() { if (true) { $x = 1; re<> } } }',
            true,
        ];
        yield 'case statement' => [
            '<?php class Foo { private function foo() { switch (true) { case 1: <> } } }',
            true,
        ];
        yield 'case statement 2' => [
            '<?php class Foo { private function foo() { switch (true) { case 1: ret<> } } }',
            true,
        ];
        yield 'in while body' => [
            '<?php class Foo { private function foo() { while () {<>} } }',
            true,
        ];
        yield 'in while body descendant' => [
            '<?php class Foo { private function foo() { while () { re<>} } }',
            true,
        ];

        yield 'string literal' => [
            '<?php class Foo { private function foo() { "<>" }',
            false,
        ];
        yield 'string literal 2' => [
            '<?php class Foo { private function foo() { "rrr<>" }',
            false,
        ];
        yield 'switch condition' => [
            '<?php class Foo { private function foo() { switch (<>) {} } }',
            false,
        ];
        yield 'switch condition 2' => [
            '<?php class Foo { private function foo() { switch (re<>) {} } }',
            false,
        ];
        yield 'foreach condition' => [
            '<?php class Foo { private function foo() { foreach (<>) {} } }',
            false,
        ];
        yield 'foreach condition 2' => [
            '<?php class Foo { private function foo() { foreach (re<>) {} } }',
            false,
        ];
        yield 'foreach condition key' => [
            '<?php class Foo { private function foo() { foreach ($x as re<>) {} } }',
            false,
        ];
        yield 'for condition 1' => [
            '<?php class Foo { private function foo() { for (<>) {} } }',
            false,
        ];
        yield 'for condition 2' => [
            '<?php class Foo { private function foo() { for (true, <>) {} } }',
            false,
        ];
        yield 'for condition 1 descendant 1' => [
            '<?php class Foo { private function foo() { for (re<>) {} } }',
            false,
        ];
        yield 'for condition 2 descendant 1' => [
            '<?php class Foo { private function foo() { for (true, re<>) {} } }',
            false,
        ];
        yield 'for condition 1 descendant 2' => [
            '<?php class Foo { private function foo() { for ($x<>) {} } }',
            false,
        ];
        yield 'catch selector' => [
            '<?php class Foo { private function foo() { try {} catch (<>) {} } }',
            false,
        ];
        yield 'catch selector descendant' => [
            '<?php class Foo { private function foo() { try {} catch (re<>) {} } }',
            false,
        ];
        yield 'in do condition' => [
            '<?php class Foo { private function foo() { do {} while (<>) } }',
            false,
        ];
        yield 'in do condition descendant' => [
            '<?php class Foo { private function foo() { do {} while (re<>) } }',
            false,
        ];
        yield 'in if condition' => [
            '<?php class Foo { private function foo() { if (<>) {} } }',
            false,
        ];
        yield 'in if condition descendant' => [
            '<?php class Foo { private function foo() { if (tr<>) {} } }',
            false,
        ];
        yield 'in while condition' => [
            '<?php class Foo { private function foo() { while (<>) {} } }',
            false,
        ];
        yield 'in while condition descendant' => [
            '<?php class Foo { private function foo() { while (tr<>) {} } }',
            false,
        ];
        yield 'statement visibility 4' => [
            '<?php class Foo { private Foobles <> }',
            false,
        ];
        yield 'statement visibility 5' => [
            '<?php class Foo { private Foobles $<> }',
            false,
        ];
        yield 'statement after class' => [
            '<?php class Foo { private Foobles $foo; } $foo-><>',
            false,
        ];
        yield 'statement const value' => [
            '<?php class Foo { public const X = sel<> }',
            false,
        ];
        yield 'statement const value 2' => [
            '<?php class Foo { public const X = [sel<> }',
            false,
        ];
        yield 'statement attribute' => [
            '<?php class Foo { public function baz(){} #[Foo\<>]public function bar(){}}',
            false,
        ];
        yield 'after member access 1' => [
            '<?php class Foo { private function foo() { $this-><> } }',
            false,
        ];
        yield 'after member access 2' => [
            '<?php class Foo { private function foo() { return $this-><> } }',
            false,
        ];
        yield 'after static access 1' => [
            '<?php class Foo { private function foo() { return self::<> } }',
            false,
        ];
        yield 'echo 1' => [
            '<?php class Foo { private function foo() { $t = 1; echo <>; $t = 2; } }',
            false,
        ];
        yield 'echo 2' => [
            '<?php class Foo { private function foo() { echo <>; $t = 2; } }',
            false,
        ];
        yield 'echo 3' => [
            '<?php class Foo { private function foo() { echo <>; } }',
            false,
        ];
        yield 'echo 4' => [
            '<?php class Foo { private function foo() { $t = 1; echo <> } }',
            false,
        ];
        yield 'echo 5' => [
            '<?php class Foo { private function foo() { echo <> $t = 1;} }',
            false,
        ];
        yield 'at the end of full name' => [
            '<?php class F { public function foo() { while<> ',
            true,
        ];
        yield 'parent is stmt, inside parens' => [
            '<?php class F { public function foo() { while (<> ',
            false,
        ];
    }

    #[DataProvider('provideClassMemberBody')]
    public function testClassMemberBody(string $source, bool $expected): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new TolerantAstProvider())->parseString($source)->getDescendantNodeAtPosition((int)$offset);
        self::assertEquals($expected, CompletionContext::classMembersBody($node));
    }

    /**
     * @return Generator<string,array<int,mixed>>
     */
    public static function provideClassMemberBody(): Generator
    {
        yield 'property' => [
            '<?php class Foo { pri<> }',
            true,
        ];
        yield 'visibility 1' => [
            '<?php class Foo { <> }',
            true,
        ];
        yield 'visibility 2' => [
            '<?php class Foo { private <> }',
            true,
        ];
        yield 'visibility 3' => [
            '<?php class Foo { private Foob<> }',
            true,
        ];
        yield 'method body' => [
            '<?php class Foo { private function foo() { <> } }',
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
        yield 'after class' => [
            '<?php class Foo { private Foobles $foo; } $foo-><>',
            false,
        ];
        yield 'const value' => [
            '<?php class Foo { public const X = sel<> }',
            false,
        ];
        yield 'const value 2' => [
            '<?php class Foo { public const X = [sel<> }',
            false,
        ];
        yield 'attribute' => [
            '<?php class Foo { public function baz(){} #[Foo\<>]public function bar(){}}',
            false,
        ];
    }

    #[DataProvider('provideClassClause')]
    public function testClassClause(string $source, bool $expected): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new TolerantAstProvider())->parseString($source)->getDescendantNodeAtPosition((int)$offset);
        self::assertEquals($expected, CompletionContext::classClause($node, ByteOffset::fromInt((int)$offset)));
    }

    /**
     * @return Generator<string,array<int,mixed>>
     */
    public static function provideClassClause(): Generator
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

    #[DataProvider('provideAttribute')]
    public function testAttribute(string $source, bool $expected): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new TolerantAstProvider())->parseString($source)->getDescendantNodeAtPosition((int)$offset);
        self::assertEquals($expected, CompletionContext::attribute($node));
    }

    /**
     * @return Generator<string,array{string,bool}>
     */
    public static function provideAttribute(): Generator
    {
        yield 'not attribute' => [
            '<?php $hello<>',
            false,
        ];

        yield 'in not mapped attribute' => [
            '<?php #[Att<>]',
            true,
        ];

        yield 'in not mapped attribute, empty name' => [
            '<?php #[<>]',
            true,
        ];

        yield 'in not mapped attribute, empty name of the second' => [
            '<?php #[Attribute(), <>]',
            true,
        ];

        yield 'in method attribute' => [
            '<?php class X {#[Att<>] public function x()',
            true,
        ];
    }

    #[DataProvider('provideAnonymousUse')]
    public function testAnonymousUse(string $source, bool $expected): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new TolerantAstProvider())->parseString($source)->getDescendantNodeAtPosition((int)$offset);
        self::assertEquals($expected, CompletionContext::anonymousUse($node));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public static function provideAnonymousUse(): Generator
    {
        yield [
            '<?php function () use ($<>) { ',
            true,
        ];
        yield [
            '<?php function () use ($<>) {}',
            true,
        ];
        yield [
            '<?php function () use ($foo, $<>) { ',
            true,
        ];
        yield [
            '<?php function (<>) { ',
            false,
        ];
        yield [
            '<?php function ($<>) { ',
            false,
        ];
        yield [
            '<?php function ($<>) use ($foo) { ',
            false,
        ];
    }

    #[DataProvider('providePromotedProperty')]
    public function testPromotedProperty(string $source, bool $expected): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new TolerantAstProvider())->parseString($source)->getDescendantNodeAtPosition((int)$offset);
        self::assertEquals($expected, CompletionContext::promotedPropertyVisibility($node));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public static function providePromotedProperty(): Generator
    {
        yield [
            '<?php class A { public function __construct(<>',
            true,
        ];
        yield [
            '<?php class A { public function __construct($a, <> }',
            true,
        ];
        yield [
            '<?php class A { public function __construct($a, p<> }',
            true,
        ];

        yield [
            '<?php class A { public function a(<>',
            false,
        ];
        yield [
            '<?php class A { public function a(<>$a) { ',
            false,
        ];
    }

    #[DataProvider('provideMethodName')]
    public function testMethodName(string $source, bool $expected): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new TolerantAstProvider())->parseString($source)->getDescendantNodeAtPosition((int)$offset);
        self::assertEquals($expected, CompletionContext::methodName($node));
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public static function provideMethodName(): Generator
    {
        yield [
            '<?php class A { public function __construct(<>',
            false,
        ];
        yield [
            '<?php class A { public function __con<>',
            true,
        ];
    }
}
