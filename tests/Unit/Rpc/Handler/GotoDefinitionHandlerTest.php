<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Rpc\Handler\GotoDefinitionHandler;
use Phpactor\Rpc\Handler;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;

class GotoDefinitionHandlerTest extends HandlerTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $reflector;

    /**
     * @var ObjectProphecy
     */
    private $symbolInformation;

    public function setUp()
    {
        $this->reflector = Reflector::create(new StringSourceLocator(SourceCode::fromPath(__FILE__)));
    }

    public function createHandler(): Handler
    {
        return new GotoDefinitionHandler(
            $this->reflector
        );
    }

    public function testHandler()
    {
        $action = $this->handle('goto_definition', [
            'offset' => 1264,
            'source' => file_get_contents(__FILE__),
        ]);

        $this->assertEquals('open_file', $action->name());
    }
}
