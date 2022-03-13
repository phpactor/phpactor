<?php

namespace Phpactor\FilePathResolver\Tests\Unit\Expander\Xdg;

use Phpactor\FilePathResolver\Expander;
use Phpactor\FilePathResolver\Expander\Xdg\SuffixExpanderDecorator;
use Phpactor\FilePathResolver\Tests\Unit\Expander\ExpanderTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class SuffixExpanderDecoratorTest extends ExpanderTestCase
{
    use ProphecyTrait;
    
    /**
     * @var ObjectProphecy<Expander>
     */
    private ObjectProphecy $expander;

    public function setUp(): void
    {
        $this->expander = $this->prophesize(Expander::class);
    }

    public function createExpander(): Expander
    {
        return new SuffixExpanderDecorator($this->expander->reveal(), '/foo');
    }

    public function testAddsSuffixToInnerExpanderValue(): void
    {
        $this->expander->tokenName()->willReturn('bar');
        $this->expander->replacementValue()->willReturn('bar');
        $this->assertEquals('/bar/foo', $this->expand('/%bar%'));
    }
}
