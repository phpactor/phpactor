<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\Qualifier;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\DocblockQualifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;

class DocblockQualifierTest extends TolerantQualifierTestCase
{
    public function provideCouldComplete(): Generator
    {
        yield 'no docblock' => [
            '<?php $hello<>',
            function (?Node $node): void {
                $this->assertNull($node);
            }
        ];

        yield 'docblock' => [
            '<?php /** @foo */$hello<>',
            function (?Node $node): void {
                self::assertInstanceOf(Node::class, $node);
            }
        ];
    }

    public function createQualifier(): TolerantQualifier
    {
        return new DocblockQualifier();
    }
}
