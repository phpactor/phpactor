<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Inference\NodeReflector;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\SourceFileNode;
use Prophecy\PhpUnit\ProphecyTrait;

class NodeReflectorTest extends TestCase
{
    use ProphecyTrait;

    public function testUnknown(): void
    {
        $this->expectException(CouldNotResolveNode::class);
        $this->expectExceptionMessage('Did not know how');
        $frame = new Frame();
        $locator = $this->prophesize(ServiceLocator::class);
        $nodeReflector = new NodeReflector($locator->reveal());

        $nodeReflector->reflectNode($frame, new SourceFileNode());
    }
}
