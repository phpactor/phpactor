<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\TolerantParser\Parser;

use Microsoft\PhpParser\Node\Statement\EchoStatement;
use PHPUnit\Framework\TestCase;
use Phpactor\TolerantAstDiff\AstDiff;
use Phpactor\WorseReflection\Bridge\TolerantParser\Parser\MergingParser;

class MergingParserTest extends TestCase
{
    public function testUpdate(): void
    {
        $parser = new MergingParser(new AstDiff());

        $ast = $parser->parseSourceFile(<<<'PHP'
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
        PHP, 'file://path');

        $node = $ast->getDescendantNodeAtPosition(46);
        self::assertInstanceOf(EchoStatement::class, $node);
        $node1OriginalId = spl_object_id($node);
        $node = $ast->getDescendantNodeAtPosition(114);
        self::assertInstanceOf(EchoStatement::class, $node);
        $node2OriginalId = spl_object_id($node);

        // new source code introduces new line between the two nodes
        $ast = $parser->parseSourceFile($source = <<<'PHP'
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
        PHP, 'file://path');

        self::assertEquals($source, $ast->getText());
        $node = $ast->getDescendantNodeAtPosition(46);
        self::assertInstanceOf(EchoStatement::class, $node);
        self::assertSame($node1OriginalId, spl_object_id($node));
        $node = $ast->getDescendantNodeAtPosition(115);
        self::assertInstanceOf(EchoStatement::class, $node);
        self::assertEquals($node2OriginalId, spl_object_id($node));

        // new source code introduces a new statement
        $ast = $parser->parseSourceFile($source = <<<'PHP'
        <?php
        function a() {
            if (true) {
                echo 'hello';
                echo 'goodbye';
            }

            echo "fo";

            if (true) {
                echo 'coming';
                echo 'going';
            }
        }
        PHP, 'file://path');

        self::assertEquals($source, $ast->getText());
        $node = $ast->getDescendantNodeAtPosition(46);
        self::assertInstanceOf(EchoStatement::class, $node);
        self::assertSame($node1OriginalId, spl_object_id($node));
        $node = $ast->getDescendantNodeAtPosition(126);
        self::assertInstanceOf(EchoStatement::class, $node);
        self::assertEquals($node2OriginalId, spl_object_id($node));
    }
}
