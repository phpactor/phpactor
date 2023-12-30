<?php

namespace Phpactor\Tests\Unit\Extension\WorseReflection\Rpc;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\WorseReflectionExtra\Rpc\OffsetInfoHandler;
use Phpactor\Extension\Rpc\Response\InformationResponse;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;

class OffsetInfoHandlerTest extends HandlerTestCase
{
    const SOURCE = <<<'EOT'
        <?php $foo = 1234;

        EOT
    ;

    public function createHandler(): Handler
    {
        return new OffsetInfoHandler(
            ReflectorBuilder::create()->addSource(self::SOURCE)->build()
        );
    }

    public function testOffsetInfo(): void
    {
        $action = $this->createHandler()->handle([
            'offset' => 19,
            'source' => self::SOURCE
        ]);

        $this->assertInstanceOf(InformationResponse::class, $action);
        $this->assertStringContainsString('symbol', $action->information());
    }
}
