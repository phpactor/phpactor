<?php

namespace Phpactor\FilePathResolver\Tests\Unit\Expander\Xdg;

use PHPUnit\Framework\TestCase;
use Phpactor\FilePathResolver\Expander\Xdg\XdgCacheExpander;
use Phpactor\FilePathResolver\Expander\Xdg\XdgConfigExpander;
use Phpactor\FilePathResolver\Expander\Xdg\XdgDataExpander;
use Phpactor\FilePathResolver\Expanders;
use Phpactor\FilePathResolver\Filter\TokenExpandingFilter;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use XdgBaseDir\Xdg;

class XdgExpanderTest extends TestCase
{
    use ProphecyTrait;

    private TokenExpandingFilter $expander;
    
    /**
     * @var ObjectProphecy<Xdg>
     */
    private ObjectProphecy $xdg;

    public function setUp(): void
    {
        $this->xdg = $this->prophesize(Xdg::class);
        $this->xdg->getHomeDataDir()->willReturn('/home/data');
        $this->xdg->getHomeConfigDir()->willReturn('/home/config');
        $this->xdg->getHomeCacheDir()->willReturn('/home/cache');

        $this->expander = new TokenExpandingFilter(new Expanders([
            new XdgCacheExpander('cache', $this->xdg->reveal()),
            new XdgDataExpander('data', $this->xdg->reveal()),
            new XdgConfigExpander('config', $this->xdg->reveal()),
        ]));
    }

    public function testExpandXdgDirs(): void
    {
        $this->assertEquals('/home/cache/foo', $this->expander->apply('%cache%/foo'));
        $this->assertEquals('/home/data/foo', $this->expander->apply('%data%/foo'));
        $this->assertEquals('/home/config/foo', $this->expander->apply('%config%/foo'));
    }
}
