<?php

namespace Phpactor\Tests\Unit\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\Helper\UnresolvableClassNameFinder;
use Phpactor\CodeTransform\Domain\NameWithByteOffset;
use Phpactor\Extension\CodeTransformExtra\Rpc\ImportUnresolvableClassesHandler;
use Phpactor\Extension\Rpc\Response\CollectionResponse;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\Rpc\Test\HandlerTester;
use Phpactor\Name\QualifiedName;
use Phpactor\Tests\IntegrationTestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Prophecy\Argument;

class ImportUnresolvableClassesHandlerTest extends IntegrationTestCase
{
    const EXAMPLE_PATH = '/example/path';
    const EXAMPLE_SOURCE = 'example-source';


    private $requestHandler;
    /**
     * @var ObjectProphecy
     */
    private $finder;

    /**
     * @var HandlerTester
     */
    private $tester;

    protected function setUp(): void
    {
        $this->requestHandler = $this->container()->get(RpcExtension::SERVICE_REQUEST_HANDLER);
        $this->finder = $this->prophesize(UnresolvableClassNameFinder::class);

        $this->tester = new HandlerTester(new ImportUnresolvableClassesHandler(
            $this->requestHandler,
            $this->finder->reveal()
        ));
    }

    public function testZeroUnresolvedClasses()
    {
        $this->finder->find(Argument::type(TextDocument::class))->willReturn([]);

        $response = $this->tester->handle(ImportUnresolvableClassesHandler::NAME, [
            ImportUnresolvableClassesHandler::PARAM_PATH => self::EXAMPLE_PATH,
            ImportUnresolvableClassesHandler::PARAM_SOURCE => self::EXAMPLE_SOURCE,
        ]);

        $this->assertInstanceOf(EchoResponse::class, $response);
    }

    public function testImportsUnresolvedClasses()
    {
        $this->finder->find(Argument::type(TextDocument::class))->willReturn([
            new NameWithByteOffset(
                QualifiedName::fromString('Foobar'),
                ByteOffset::fromInt(12)
            )
        ]);

        $response = $this->tester->handle(ImportUnresolvableClassesHandler::NAME, [
            ImportUnresolvableClassesHandler::PARAM_PATH => self::EXAMPLE_PATH,
            ImportUnresolvableClassesHandler::PARAM_SOURCE => self::EXAMPLE_SOURCE,
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $response);
    }
}
