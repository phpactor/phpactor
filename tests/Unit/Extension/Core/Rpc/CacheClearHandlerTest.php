<?php

namespace Phpactor\Tests\Unit\Extension\Core\Rpc;

use Phpactor\Extension\Core\Application\CacheClear;
use Phpactor\Extension\Core\Rpc\CacheClearHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class CacheClearHandlerTest extends HandlerTestCase
{
    /**
     * @var CacheClear|ObjectProphecy
     */
    private $clearCache;

    public function setUp(): void
    {
        $this->clearCache = $this->prophesize(CacheClear::class);
    }

    public function createHandler(): Handler
    {
        return new CacheClearHandler($this->clearCache->reveal());
    }

    public function testClearCache(): void
    {
        $this->clearCache->clearCache()->shouldBeCalled();
        $this->clearCache->cachePath()->willReturn('/path/to');
        $this->handle('cache_clear', []);
    }
}
