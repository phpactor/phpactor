<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\Rpc\Handler\OffsetInfoHandler;
use Phpactor\Rpc\Response\InformationResponse;
use Phpactor\WorseReflection\ReflectorBuilder;

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

    public function testOffsetInfo()
    {
        $action = $this->createHandler('offset_info')->handle([
            'offset' => 19,
            'source' => self::SOURCE
        ]);

        $this->assertInstanceOf(InformationResponse::class, $action);
        $this->assertContains('symbol', $action->information());
    }
}
