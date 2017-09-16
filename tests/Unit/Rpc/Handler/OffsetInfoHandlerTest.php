<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Rpc\Handler;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\Rpc\Handler\OffsetInfoHandler;
use Phpactor\Rpc\Editor\InformationAction;


class OffsetInfoHandlerTest extends HandlerTestCase
{
    const SOURCE = <<<'EOT'
<?php $foo = 1234;

EOT
    ;

    public function createHandler(): Handler
    {
        return new OffsetInfoHandler(
            Reflector::create(new StringSourceLocator(SourceCode::fromString(self::SOURCE)))
        );
    }

    public function testOffsetInfo()
    {
        $action = $this->createHandler('offset_info')->handle([
            'offset' => 19,
            'source' => self::SOURCE
        ]);

        $this->assertInstanceOf(InformationAction::class, $action);
        $this->assertContains('symbol', $action->information());
    }
}

