<?php

namespace Phpactor\Tests\Unit\Extension\Navigation\Rpc;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Navigation\Handler\NavigateHandler;
use Phpactor\Extension\Navigation\Application\Navigator;
use Prophecy\Prophecy\ObjectProphecy;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\Input\ConfirmInput;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;

class NavigateHandlerTest extends HandlerTestCase
{
    const TEST_PATH = 'path/to/file.php';
    const TEST_DEST1 = 'dest1';

    private ObjectProphecy $navigator;

    private array $destinations;

    public function setUp(): void
    {
        $this->navigator = $this->prophesize(Navigator::class);
        $this->destinations = [
            self::TEST_DEST1 => '/path/to/dest1',
            'dest2' => '/path/to/dest2',
        ];
    }

    public function createHandler(): Handler
    {
        return new NavigateHandler($this->navigator->reveal());
    }

    public function testDestinations(): void
    {
        $this->navigator->destinationsFor(self::TEST_PATH)->willReturn($this->destinations);
        $response = $this->handle('navigate', [
            NavigateHandler::PARAM_SOURCE_PATH => self::TEST_PATH,
        ]);

        /** @var InputCallbackResponse $response */
        $this->assertInstanceOf(InputCallbackResponse::class, $response);
        $inputs = $response->inputs();
        $input = reset($inputs);
        $this->assertInstanceOf(ChoiceInput::class, $input);
    }

    public function testCanCreateConfirm(): void
    {
        $this->navigator->destinationsFor(self::TEST_PATH)->willReturn($this->destinations);
        $this->navigator->canCreateNew(self::TEST_PATH, self::TEST_DEST1)->willReturn(true);

        $response = $this->handle('navigate', [
            NavigateHandler::PARAM_SOURCE_PATH => self::TEST_PATH,
            NavigateHandler::PARAM_DESTINATION => self::TEST_DEST1,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $response);
        $inputs = $response->inputs();
        $input = reset($inputs);
        $this->assertInstanceOf(ConfirmInput::class, $input);
    }

    public function testOpenFile(): void
    {
        $this->navigator->destinationsFor(self::TEST_PATH)->willReturn($this->destinations);
        $this->navigator->canCreateNew(self::TEST_PATH, self::TEST_DEST1)->willReturn(false);

        $response = $this->handle('navigate', [
            NavigateHandler::PARAM_SOURCE_PATH => self::TEST_PATH,
            NavigateHandler::PARAM_DESTINATION => self::TEST_DEST1,
        ]);

        $this->assertInstanceOf(OpenFileResponse::class, $response);
    }
}
