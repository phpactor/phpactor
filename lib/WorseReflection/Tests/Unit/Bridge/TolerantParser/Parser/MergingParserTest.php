<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\TolerantParser\Parser;

use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Statement\EchoStatement;
use PHPUnit\Framework\TestCase;
use Phpactor\TolerantAstDiff\AstDiff;
use Phpactor\WorseReflection\Bridge\TolerantParser\Parser\MergingParser;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

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

        self::assertEquals($source, $ast->getText(), 'Updated AST is equal to target source');
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

            echo 'foo';

            if (true) {
                echo 'coming';
                echo 'going';
            }
        }
        PHP, 'file://path');

        self::assertEquals($source, $ast->getText());

        // retrieve secnd "echo 'hello'" (before the edit) - it should be the same node as the first example
        $node = $ast->getDescendantNodeAtPosition(46);
        self::assertInstanceOf(EchoStatement::class, $node);
        self::assertSame($node1OriginalId, spl_object_id($node));

        // TODO: it doesn't currently supprt list inserts (e.g. 1, <insert>, 2).
        // retrieve secnd "echo 'coming'" (after the edit) - it should be the same node as the first example
        $node = $ast->getDescendantNodeAtPosition(132);
        self::assertInstanceOf(EchoStatement::class, $node);
        self::assertNotEquals($node2OriginalId, spl_object_id($node));
    }

    public function testSmokeSession(): void
    {
        $parser = new MergingParser(new AstDiff());
        $sources = json_decode((string)file_get_contents(__DIR__ . '/smoke.json'));
        assert(is_array($sources));
        foreach ($sources as $source) {
            assert(is_string($source));
            $ast = $parser->parseSourceFile($source, __FILE__);
        }
        $node = $ast->getDescendantNodeAtPosition(1378);
        self::assertInstanceOf(Variable::class, $node);
        self::assertEquals('$this', $node->getText());
    }
}
