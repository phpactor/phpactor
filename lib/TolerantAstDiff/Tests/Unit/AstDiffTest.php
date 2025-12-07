<?php

namespace Phpactor\TolerantAstDiff\Tests\Unit;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use Phpactor\ConfigLoader\Tests\TestCase;
use Phpactor\TolerantAstDiff\AstDiff;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

final class AstDiffTest extends TestCase
{
    #[DataProvider('provideDiffTree')]
    public function testDiffTree(string $source1, string $source2): void
    {
        $parser = new Parser();
        $ast1 = $parser->parseSourceFile($source1);
        $ast2 = $parser->parseSourceFile($source2);

        $diff = (new AstDiff());
        $diff->merge($ast1, $ast2);

        self::assertEquals($ast2->getText(), $ast1->getText());
    }
    /**
     * @return Generator<string,array{string,string}>
     */
    public static function provideDiffTree(): Generator
    {
        yield 'same' => [
            '<?php function hello(): string { echo "hello"; }',
            '<?php function hello(): string { echo "hello"; }',
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
    }
}
