<?php

namespace Phpactor\Tests\Unit\Extension\ContextMenu\Handler;

use Phpactor\CodeTransform\Domain\Helper\InterestingOffsetFinder;
use Phpactor\Container\Container;
use Phpactor\Extension\ContextMenu\ContextMenuExtension;
use Phpactor\Extension\ContextMenu\Handler\ContextMenuHandler;
use Phpactor\Extension\ContextMenu\Model\ContextMenu;
use Phpactor\Extension\Core\Application\Helper\ClassFileNormalizer;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\RequestHandler\RequestHandler;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\Prophecy\ObjectProphecy;

class ContextMenuHandlerTest extends HandlerTestCase
{
    const VARIABLE_ACTION = 'do_something';
    const SOURCE = '<?php $hello = "world"; echo $hello;';
    const FOUND_OFFSET = 10;
    const ORIGINAL_OFFSET = 8;

    private Reflector $reflector;

    private ObjectProphecy $container;

    private ?ContextMenu $menu = null;

    private ObjectProphecy $requestHandler;

    private ObjectProphecy $classFileNormalizer;

    private ObjectProphecy $offsetFinder;

    public function setUp(): void
    {
        $this->reflector = ReflectorBuilder::create()->addSource(TextDocumentBuilder::fromUri(__FILE__)->build())->build();
        $this->offsetFinder = $this->prophesize(InterestingOffsetFinder::class);
        $this->classFileNormalizer = $this->prophesize(ClassFileNormalizer::class);
        $this->container = $this->prophesize(Container::class);
        $this->requestHandler = $this->prophesize(RequestHandler::class);
    }

    public function createHandler(): Handler
    {
        return new ContextMenuHandler(
            $this->reflector,
            $this->offsetFinder->reveal(),
            $this->classFileNormalizer->reveal(),
            $this->menu,
            $this->container->reveal()
        );
    }

    public function testNoActionsAvailable(): void
    {
        $this->menu = ContextMenu::fromArray([
            'actions' => [
                Symbol::VARIABLE => [
                    'action' => self::VARIABLE_ACTION,
                    'parameters' => [
                        'one' => 1,
                    ],
                ]
            ],
            'contexts' => [
                Symbol::VARIABLE => [
                ]
            ]
        ]);
        $source = TextDocumentBuilder::create('<?php $hello = "world"; echo $hello;')->uri('/hello.php')->build();
        $offset = ByteOffset::fromInt(4);

        $this->offsetFinder->find($source, $offset)
            ->willReturn($offset);

        $action = $this->handle(ContextMenuHandler::NAME, [
            'source' => (string) $source,
            'offset' => $offset->toInt(),
            'current_path' => $source->uri()?->path(),
        ]);

        $this->assertInstanceOf(EchoResponse::class, $action);
        $this->assertStringContainsString('No context actions', $action->message());
    }

    public function testReturnMenu(): void
    {
        $this->menu = ContextMenu::fromArray([
            'actions' => [
                Symbol::VARIABLE => [
                    'action' => self::VARIABLE_ACTION,
                    'parameters' => [
                        'one' => 1,
                    ],
                ]
            ],
            'contexts' => [
                Symbol::VARIABLE => [
                    Symbol::VARIABLE
                ]
            ]
        ]);

        $source = TextDocumentBuilder::create(
            '<?php $hello = "world"; echo $hello;'
        )->uri(
            '/hello.php',
        )->build();
        $offset = ByteOffset::fromInt(self::ORIGINAL_OFFSET);

        $this->offsetFinder->find($source, $offset)
            ->willReturn($offset);

        $action = $this->handle(ContextMenuHandler::NAME, [
            'source' => (string) $source,
            'offset' => $offset->toInt(),
            'current_path' => $source->uri()->path(),
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $this->assertInstanceOf(Request::class, $action->callbackAction());
        $this->assertEquals(ContextMenuHandler::NAME, $action->callbackAction()->name());
    }

    public function testReturnMenuWithOriginalOffset(): void
    {
        $this->menu = ContextMenu::fromArray([
            'actions' => [
                Symbol::VARIABLE => [
                    'action' => self::VARIABLE_ACTION,
                    'parameters' => [
                        'one' => 1,
                    ],
                ]
            ],
            'contexts' => [
                Symbol::VARIABLE => [
                    Symbol::VARIABLE
                ]
            ]
        ]);

        $source = TextDocumentBuilder::create(
            '<?php $hello = "world"; echo $hello;'
        )->uri(
            '/hello.php'
        )->build();
        $offset = ByteOffset::fromInt(self::ORIGINAL_OFFSET);

        $this->offsetFinder->find($source, $offset)
            ->willReturn(ByteOffset::fromInt(self::FOUND_OFFSET));

        $action = $this->handle(ContextMenuHandler::NAME, [
            'source' => (string) $source,
            'offset' => self::ORIGINAL_OFFSET,
            'current_path' => $source->uri()?->path(),
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $this->assertEquals(self::ORIGINAL_OFFSET, $action->callbackAction()->parameters()['offset']);
    }

    public function testReplaceTokens(): void
    {
        $this->container->get(ContextMenuExtension::SERVICE_REQUEST_HANDLER)->willReturn(
            $this->requestHandler->reveal()
        );

        $this->classFileNormalizer->classToFile('string')->willReturn(__FILE__);

        $source = TextDocumentBuilder::create(self::SOURCE)->uri('/hello.php')->build();
        $offset = ByteOffset::fromInt(self::ORIGINAL_OFFSET);

        $this->offsetFinder->find($source, $offset)
            ->willReturn($offset);

        $this->requestHandler->handle(
            Request::fromNameAndParameters(
                self::VARIABLE_ACTION,
                [
                    'some_source' => (string) $source,
                    'some_offset' => $offset->toInt(),
                    'some_path' => __FILE__
                ]
            )
        )->willReturn(
            EchoResponse::fromMessage('Hello')
        );

        $this->menu = ContextMenu::fromArray([
            'actions' => [
                self::VARIABLE_ACTION => [
                    'action' => self::VARIABLE_ACTION,
                    'parameters' => [
                        'some_source' => '%source%',
                        'some_offset' => '%offset%',
                        'some_path' => '%path%',
                    ],
                ]
            ],
            'contexts' => [
                Symbol::VARIABLE => [
                    self::VARIABLE_ACTION
                ]
            ]
        ]);

        $action = $this->handle(ContextMenuHandler::NAME, [
            'action' => self::VARIABLE_ACTION,
            'source' => (string) $source,
            'offset' => $offset->toInt(),
            'current_path' => $source->uri()?->path(),
        ]);

        $parameters = $action->parameters();
        $this->assertEquals([
            'message' => 'Hello',
        ], $parameters);
    }
}
