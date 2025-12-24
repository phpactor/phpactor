<?php

namespace Phpactor\CodeBuilder\Tests\Functional\Adapter\TolerantParser\Util;

use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\NodeHelper;
use Phpactor\TestUtils\ExtractOffset;
use Microsoft\PhpParser\Node;

class NodeHelperTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    public function testSelf(): void
    {
        [$methodNode, $nameNode] = $this->findSelfNode();
        $result = NodeHelper::resolvedShortName($methodNode, $nameNode);
        $this->assertEquals('self', $result);
    }

    /**
     * @return array{Node, Node}
     */
    private function findSelfNode(): array
    {
        [$source, $methodOffset, $nameOffset] = ExtractOffset::fromSource('<?php class Foobar { public function f<>oo(): sel<>f() { return $this; }}');
        $root = $this->parser->get($source);
        return [
            $root->getDescendantNodeAtPosition($methodOffset),
            $root->getDescendantNodeAtPosition($nameOffset),
        ];
    }
}
