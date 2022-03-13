<?php

namespace Phpactor\Tests\Unit\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\Helper\UnresolvableClassNameFinder;
use Phpactor\CodeTransform\Domain\NameWithByteOffset;
use Phpactor\CodeTransform\Domain\NameWithByteOffsets;
use Phpactor\Extension\CodeTransformExtra\Rpc\ImportMissingClassesHandler;
use Phpactor\Extension\Rpc\Response\CollectionResponse;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\Rpc\Test\HandlerTester;
use Phpactor\Name\QualifiedName;
use Phpactor\Tests\IntegrationTestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ImportMissingClassesHandlerTest extends IntegrationTestCase
{
    use ProphecyTrait;
    const EXAMPLE_PATH = '/example/path';
    const EXAMPLE_SOURCE = 'example-source';

    private $requestHandler;

    private ObjectProphecy $finder;

    private HandlerTester $tester;

    protected function setUp(): void
    {
        $this->requestHandler = $this->container()->get(RpcExtension::SERVICE_REQUEST_HANDLER);
        $this->finder = $this->prophesize(UnresolvableClassNameFinder::class);

        $this->tester = new HandlerTester(new ImportMissingClassesHandler(
            $this->requestHandler,
            $this->finder->reveal()
        ));
    }

    public function testZeroUnresolvedClasses(): void
    {
        $this->finder->find(Argument::type(TextDocument::class))->willReturn(new NameWithByteOffsets(...[]));

        $response = $this->tester->handle(ImportMissingClassesHandler::NAME, [
            ImportMissingClassesHandler::PARAM_PATH => self::EXAMPLE_PATH,
            ImportMissingClassesHandler::PARAM_SOURCE => self::EXAMPLE_SOURCE,
        ]);

        $this->assertInstanceOf(EchoResponse::class, $response);
    }

    public function testImportsUnresolvedClasses(): void
    {
        $this->finder->find(Argument::type(TextDocument::class))->willReturn(new NameWithByteOffsets(...[
            new NameWithByteOffset(
                QualifiedName::fromString('Foobar'),
                ByteOffset::fromInt(12)
            )
        ]));

        $response = $this->tester->handle(ImportMissingClassesHandler::NAME, [
            ImportMissingClassesHandler::PARAM_PATH => self::EXAMPLE_PATH,
            ImportMissingClassesHandler::PARAM_SOURCE => self::EXAMPLE_SOURCE,
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $response);
    }
}
