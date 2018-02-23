<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Rpc\Handler\GotoDefinitionHandler;
use Phpactor\Rpc\Handler;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\ReflectorBuilder;

class GotoDefinitionHandlerTest extends HandlerTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $reflector;

    /**
     * @var ObjectProphecy
     */
    private $symbolContext;

    public function setUp()
    {
        $this->reflector = ReflectorBuilder::create()->addSource(SourceCode::fromPath(__FILE__))->build();
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
            'offset' => 840,
            'source' => file_get_contents(__FILE__),
        ]);

        $this->assertEquals('open_file', $action->name());
    }
}
