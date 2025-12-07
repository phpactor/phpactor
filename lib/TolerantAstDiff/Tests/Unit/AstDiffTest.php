<?php

namespace Phpactor\TolerantAstDiff\Tests\Unit;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use Phpactor\ConfigLoader\Tests\TestCase;
use Phpactor\TolerantAstDiff\AstDiff;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

final class AstDiffTest extends TestCase
{
    #[DataProvider('provideDiffTree')]
    public function testDiffTree(string $source1, string $source2, ?\Closure $assertion = null): void
    {
        $parser = new Parser();
        $ast1 = $parser->parseSourceFile($source1);
        $ast2 = $parser->parseSourceFile($source2);

        $diff = (new AstDiff());
        $diff->merge($ast1, $ast2);

        self::assertSame($source2, $ast1->getFullText(), 'AST content matches');
        self::assertSame($ast2->getFullText(), $ast1->getFullText(), 'AST content matches');
        self::assertSame($ast2->getFullWidth(), $ast1->getFullWidth(), 'AST width matches');

        if ($assertion === null) {
            return;
        }
        $assertion->bindTo($this)->__invoke($ast1);
    }
    /**
     * @return Generator<string,array{string,string}>
     */
    public static function provideDiffTree(): Generator
    {
        yield 'same' => [
            '<?php function hello(): string { echo "hello"; } ',
            '<?php function hello(): string { echo "hello"; } ',
        ];

        yield 'remove 1' => [
            '<?php function hello(): string {echo "hello";}',
            '<?php function hello(): string {}',
        ];
        yield 'remove 2' => [
            <<<'PHP'
                <?php
                class Foo
                {
                    public function bar()
                    {
                        echo 'foobar';
                    }

                    public function foo()
                    {
                    }
                }
                PHP,
            <<<'PHP'
                <?php
                class Foo
                {
                    public function bar()
                    {
                        echo 'foobar';
                    }
                }
                PHP
        ];

        yield 'add 1 node' => [
            '<?php function hello(): string {echo "hello";}',
            '<?php function hello(): string {echo "hello";echo 2;}',
        ];

        yield 'change 1 node' => [
            '<?php function hello(): string {echo "hello";echo 2;}',
            '<?php function hello(): string {echo "hello";echo 3;}',
        ];

        yield 'update array' => [
            '<?php function hello(): string { $foo = [1, 2, 3]; }',
            '<?php function hello(): string { $foo = [5, 10]; }',
        ];

        yield 'replace node' => [
            '<?php function hello(): string {echo "hello";echo 2;}',
            '<?php class Bar {}',
        ];

        yield 'insert infix' => [
            '<?php function hello(): string {echo "hello";echo 2;}',
            '<?php function hello(): string {echo "hello";echo 5;echo 2;}',
        ];

        yield 'artbitrary change' => [
            <<<'PHP'
                <?php
                class Foo
                {
                    public function bar()
                    {
                        echo 'foobar';
                    }
                }
                PHP,
            <<<'PHP'
                <?php
                class Foo
                {
                    public function baz()
                    {
                        echo 'baz';
                    }
                }
                PHP
        ];

        yield 'intoduce new line' => [
            <<<'PHP'
                <?php
                class Foo
                {
                    public function bar()
                    {
                        echo 'foobar';
                    }
                }
                PHP,
            <<<'PHP'
                <?php
                class Foo
                {
                    public function bar()
                    {


                        echo 'foobar';
                    }
                }
                PHP
        ];

        yield 'misc' => [
            <<<'PHP'
            <?php
            function a() {
                if (true) {
                    echo 'hello';
                    echo 'goodbye';
                }
                if (true) {
                    echo 'coming';
                    echo 'going';
                }
            }
            PHP,
            <<<'PHP'
            <?php
            function a() {
                if (true) {
                    echo 'hello';
                    echo 'goodbye';
                }


                if (true) {
                    echo 'coming';
                    echo 'going';
                }
            }
            PHP,
        ];

        yield 'node text is aligned' => [
            <<<'PHP'
            <?php
            if ($uri === 'file://'.__FILE__) {
                $this->merger->merge($node1,   $node2);
                dump(NodeUtil::dump($node1));
            }
            PHP,
            <<<'PHP'
            <?php
            if ($uri === 'file://'.__FILE__) {
                $this->merger->merge($node1,   $node2);
                dump(NodeUtil::dump($node1));
            }
            PHP,
            function (Node $node) {
                $node = $node->getDescendantNodeAtPosition(79);
                self::assertEquals('$node2', $node->getText());
            },
        ];
    }
}
