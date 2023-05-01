<?php

namespace Phpactor\FilePathResolver\Tests\Unit\Filter;

use PHPUnit\Framework\TestCase;
use Phpactor\FilePathResolver\Expander;
use Phpactor\FilePathResolver\Expanders;
use Phpactor\FilePathResolver\Filter\TokenExpandingFilter;
use Prophecy\PhpUnit\ProphecyTrait;

class TokenExpandingFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testIdentity(): void
    {
        $this->assertEquals('/foo', $this->create()->apply('/foo'));
    }

    public function testAppliesExpanders(): void
    {
        $expander1 = $this->prophesize(Expander::class);
        $expander2 = $this->prophesize(Expander::class);
        $expander3 = $this->prophesize(Expander::class);

        $expander1->tokenName()->willReturn('foo');
        $expander1->replacementValue()->willReturn('baz');

        $expander2->tokenName()->willReturn('zed');
        $expander2->replacementValue()->shouldNotBeCalled();

        $expander3->tokenName()->willReturn('bar');
        $expander3->replacementValue()->willReturn('fab');

        $path = $this->create([
            $expander1->reveal(),
            $expander2->reveal(),
            $expander3->reveal(),
        ])->apply('/start/%foo%/%bar%/end');

        $this->assertEquals('/start/baz/fab/end', $path);
    }

    /**
     * @param Expander[] $expanders
     */
    private function create(array $expanders = []): TokenExpandingFilter
    {
        return new TokenExpandingFilter(new Expanders($expanders));
    }
}
