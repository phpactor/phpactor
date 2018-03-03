<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Handler\ContextMenuHandler;
use Phpactor\WorseReflection\Reflector;
use PhpBench\DependencyInjection\Container;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\Rpc\Response\EchoResponse;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\Rpc\Response\InputCallbackResponse;
use Phpactor\Rpc\Request;
use Phpactor\Container\RpcExtension;
use Phpactor\Rpc\RequestHandler\RequestHandler;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\WorseReflection\ReflectorBuilder;

class ContextMenuHandlerTest extends HandlerTestCase
{
    const VARIABLE_ACTION = 'do_something';
    const SOURCE = '<?php $hello = "world"; echo $hello;';

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var array
     */
    private $menu = [];

    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * @var ClassFileNormalizer
     */
    private $classFileNormalizer;

    public function setUp()
    {
        $this->reflector = ReflectorBuilder::create()->addSource(SourceCode::fromPath(__FILE__))->build();
        $this->classFileNormalizer = $this->prophesize(ClassFileNormalizer::class);
        $this->container = $this->prophesize(Container::class);
        $this->requestHandler = $this->prophesize(RequestHandler::class);
    }

    public function createHandler(): Handler
    {
        return new ContextMenuHandler(
            $this->reflector,
            $this->classFileNormalizer->reveal(),
            $this->menu,
            $this->container->reveal()
        );
    }

    public function testNoActionsAvailable()
    {
        $action = $this->handle(ContextMenuHandler::NAME, [
            'source' => '<?php $hello = "world"; echo $hello;',
            'offset' => 4,
            'current_path' => '/hello.php',
        ]);

        $this->assertInstanceOf(EchoResponse::class, $action);
        $this->assertContains('No context actions', $action->message());
    }

    public function testReturnMenu()
    {
        $this->menu = [
            Symbol::VARIABLE => [
                'action' => self::VARIABLE_ACTION,
                'parameters' => [
                    'one' => 1,
                ],
            ]
        ];
        $action = $this->handle(ContextMenuHandler::NAME, [
            'source' => '<?php $hello = "world"; echo $hello;',
            'offset' => 8,
            'current_path' => '/hello.php',
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $this->assertInstanceOf(Request::class, $action->callbackAction());
        $this->assertEquals(ContextMenuHandler::NAME, $action->callbackAction()->name());
    }

    public function testReplaceTokens()
    {
        $this->container->get(RpcExtension::SERVICE_REQUEST_HANDLER)->willReturn(
            $this->requestHandler->reveal()
        );

        $this->classFileNormalizer->classToFile('string')->willReturn(__FILE__);

        $this->requestHandler->handle(
            Request::fromNameAndParameters(
                self::VARIABLE_ACTION,
                [
                    'some_source' => self::SOURCE,
                    'some_offset' => 8,
                    'some_path' => __FILE__
                ]
            )
        )->willReturn(
            EchoResponse::fromMessage('Hello')
        );

        $this->menu = [
            Symbol::VARIABLE => [
                self::VARIABLE_ACTION => [
                    'action' => self::VARIABLE_ACTION,
                    'parameters' => [
                        'some_source' => '%source%',
                        'some_offset' => '%offset%',
                        'some_path' => '%path%',
                    ],
                ],
            ]
        ];

        $action = $this->handle(ContextMenuHandler::NAME, [
            'action' => self::VARIABLE_ACTION,
            'source' => self::SOURCE,
            'offset' => 8,
            'current_path' => '/hello.php',
        ]);

        $parameters = $action->parameters();
        $this->assertEquals([
            'message' => 'Hello',
        ], $parameters);
    }
}
