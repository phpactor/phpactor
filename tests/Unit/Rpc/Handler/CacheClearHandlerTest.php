<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Tests\Unit\Rpc\Handler\HandlerTestCase;
use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Handler\CacheClearHandler;
use Phpactor\Application\CacheClear;
use Prophecy\Prophecy\ObjectProphecy;

class CacheClearHandlerTest extends HandlerTestCase
{
    /**
     * @var CacheClear|ObjectProphecy
     */
    private $clearCache;

    public function setUp()
    {
        $this->clearCache = $this->prophesize(CacheClear::class);
    }

    public function createHandler(): Handler
    {
        return new CacheClearHandler($this->clearCache->reveal());
    }

    public function testClearCache()
    {
        $this->clearCache->clearCache()->shouldBeCalled();
        $this->clearCache->cachePath()->willReturn('/path/to');
        $this->handle('cache_clear', []);
    }
}
