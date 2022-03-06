<?php

namespace Phpactor\Completion\Tests\Unit\Bridge\TolerantParser\Helper;

use Closure;
use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Bridge\TolerantParser\Helper\NodeQuery;
use Phpactor\TestUtils\ExtractOffset;

class NodeQueryTest extends TestCase
{
    /**
     * @var Parser
     */
    private $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    /**
     * @dataProvider provideFirstAncestorVia
     */
    public function testFirstAncestorVia(string $source, Closure $assertion): void
    {
        $node = $this->nodeFromSource($source);
        $assertion($node);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideFirstAncestorVia(): Generator
    {
        yield [
            '<?php new Barfoo(new Foobar($foo, $ba<>));',
            function (Node $node): void {
                $node = NodeQuery::firstAncestorVia($node, ObjectCreationExpression::class, [
                    ArgumentExpression::class,
                    ArgumentExpressionList::class,
                ]);
                self::assertNotNull($node);
                self::assertInstanceOf(ObjectCreationExpression::class, $node);
                self::assertEquals('Foobar', $node->classTypeDesignator->getText());
            }
        ];

        yield [
            '<?php new Barfoo(new Foobar($foo, array_map($ba<>, [])));',
            function (Node $node): void {
                $node = NodeQuery::firstAncestorVia($node, ObjectCreationExpression::class, [
                    ArgumentExpression::class,
                    ArgumentExpressionList::class,
                ]);
                self::assertNull($node);
            }
        ];
    }

    private function nodeFromSource(string $source): Node
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        return $this->parser->parseSourceFile($source)->getDescendantNodeAtPosition($offset);
    }
}
