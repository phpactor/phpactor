<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Handler\NavigateHandler;
use Phpactor\ClassFileConverter\PathFinder;
use Phpactor\Extension\PathFinder\Application\Navigator;
use Prophecy\Prophecy\ObjectProphecy;
use Phpactor\Rpc\Response\Input\ChoiceInput;
use Phpactor\Rpc\Response\InputCallbackResponse;
use Phpactor\Rpc\Response\Input\ConfirmInput;
use Phpactor\Rpc\Response\OpenFileResponse;

class NavigateHandlerTest extends HandlerTestCase
{
    const TEST_PATH = 'path/to/file.php';
    const TEST_DEST1 = 'dest1';

    /**
     * @var ObjectProphecy
     */
    private $navigator;

    /**
     * @var array
     */
    private $destinations;

    public function setUp()
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

    public function testDestinations()
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

    public function testCanCreateConfirm()
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

    public function testOpenFile()
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
