<?php

namespace Phpactor\CodeBuilder\Tests\Functional\Adapter\TolerantParser\Util;

use Microsoft\PhpParser\Parser;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Util\NodeHelper;
use Phpactor\TestUtils\ExtractOffset;

class NodeHelperTest extends TestCase
{
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    public function testSelf(): void
    {
        list($methodNode, $nameNode) = $this->findSelfNode();
        $result = NodeHelper::resolvedShortName($methodNode, $nameNode);
        $this->assertEquals('self', $result);
    }

    private function findSelfNode()
    {
        list($source, $methodOffset, $nameOffset) = ExtractOffset::fromSource('<?php class Foobar { public function f<>oo(): sel<>f() { return $this; }}');
        $root = $this->parser->parseSourceFile($source);
        return [
            $root->getDescendantNodeAtPosition($methodOffset),
            $root->getDescendantNodeAtPosition($nameOffset),
        ];
    }
}
