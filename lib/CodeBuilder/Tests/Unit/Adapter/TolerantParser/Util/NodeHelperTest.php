<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Adapter\TolerantParser\Util;

use Generator;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\NodeHelper;
use Phpactor\TestUtils\ExtractOffset;

class NodeHelperTest extends TestCase
{
    /**
     * @dataProvider provideEmptyLinesPrecedingNode
     */
    public function testEmptyLinesPrecedingNode(string $source, int $expectedLines): void
    {
        [ $source, $offset ] = ExtractOffset::fromSource($source);
        $node = (new Parser())->parseSourceFile($source)->getDescendantNodeAtPosition($offset);
        self::assertEquals($expectedLines, NodeHelper::emptyLinesPrecedingNode($node));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideEmptyLinesPrecedingNode(): Generator
    {
        yield [
            "<?php \nfo<>obar();",
            0
        ];

        yield [
            "<?php \n\nfo<>obar();",
            1
        ];

        yield [
            "<?php \n\n\nfo<>obar();",
            2
        ];

        yield [
            <<<'EOT'
                <?php
                namespace Foobar;

                <>class Foobar
                {
                }
                EOT

            , 1
        ];
    }
}
