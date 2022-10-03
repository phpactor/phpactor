<?php

namespace Phpactor\Tests\Unit\Extension\WorseReflection\Rpc;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\InformationResponse;
use Phpactor\Extension\WorseReflectionExtra\Rpc\OffsetInfoHandler;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Phpactor\WorseReflection\ReflectorBuilder;

class OffsetInfoHandlerTest extends HandlerTestCase
{
    public const SOURCE = <<<'EOT'
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
        $action = $this->createHandler('offset_info')->handle([
            'offset' => 19,
            'source' => self::SOURCE
        ]);

        $this->assertInstanceOf(InformationResponse::class, $action);
        $this->assertStringContainsString('symbol', $action->information());
    }
}
