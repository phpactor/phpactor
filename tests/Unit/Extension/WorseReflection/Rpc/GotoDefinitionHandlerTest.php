<?php

namespace Phpactor\Tests\Unit\Extension\WorseReflection\Rpc;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\Tests\Unit\Extension\Rpc\Handler\HandlerTestCase;
use Phpactor\Extension\WorseReflection\Rpc\GotoDefinitionHandler;

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
            'offset' => 881,
            'path' => __FILE__,
            'source' => file_get_contents(__FILE__),
        ]);

        $this->assertEquals('open_file', $action->name());
    }
}
