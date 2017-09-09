<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Core\GotoDefinition\GotoDefinition;
use Phpactor\Rpc\Handler\GotoDefinitionHandler;

class GotoDefinitionHandlerTest extends HandlerTestCase
{
    private $gotoDefiniton;

    public function create()
    {
        $this->gotoDefition = $this->prophesize(GotoDefinition::class);
        return new GotoDefinitionHandler($this->gotoDefiniton->reveal());
    }

    public function testHandler()
    {
    }
}
