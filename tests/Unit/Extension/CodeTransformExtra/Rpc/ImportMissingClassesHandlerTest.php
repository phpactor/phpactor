<?php

namespace Phpactor\Tests\Unit\Extension\CodeTransformExtra\Rpc;

use Phpactor\Extension\CodeTransformExtra\Rpc\ImportMissingClassesHandler;
use Phpactor\Extension\Rpc\Response\CollectionResponse;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\Rpc\Test\HandlerTester;
use Phpactor\Name\FullyQualifiedName;
use Phpactor\Tests\IntegrationTestCase;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnresolvableNameDiagnostic;
use Phpactor\WorseReflection\Core\DiagnosticProvider\InMemoryDiagnosticProvider;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\PhpUnit\ProphecyTrait;

class ImportMissingClassesHandlerTest extends IntegrationTestCase
{
    use ProphecyTrait;
    const EXAMPLE_PATH = '/example/path';
    const EXAMPLE_SOURCE = 'example-source';

    private $requestHandler;

    protected function setUp(): void
    {
        $this->requestHandler = $this->container()->get(RpcExtension::SERVICE_REQUEST_HANDLER);
    }

    public function testZeroUnresolvedClasses(): void
    {
        $reflector = ReflectorBuilder::create()->addDiagnosticProvider(new InMemoryDiagnosticProvider([]))->build();
        $tester = new HandlerTester(new ImportMissingClassesHandler(
            $this->requestHandler,
            $reflector,
        ));
        $response = $tester->handle(ImportMissingClassesHandler::NAME, [
            ImportMissingClassesHandler::PARAM_PATH => self::EXAMPLE_PATH,
            ImportMissingClassesHandler::PARAM_SOURCE => self::EXAMPLE_SOURCE,
        ]);

        $this->assertInstanceOf(EchoResponse::class, $response);
    }

    public function testImportsUnresolvedClasses(): void
    {
        $reflector = ReflectorBuilder::create()->addDiagnosticProvider(
            new InMemoryDiagnosticProvider([
                UnresolvableNameDiagnostic::forClass(ByteOffsetRange::fromInts(1, 1), FullyQualifiedName::fromString('foo'))
            ])
        )->build();
        $tester = new HandlerTester(new ImportMissingClassesHandler(
            $this->requestHandler,
            $reflector,
        ));
        $response = $tester->handle(ImportMissingClassesHandler::NAME, [
            ImportMissingClassesHandler::PARAM_PATH => self::EXAMPLE_PATH,
            ImportMissingClassesHandler::PARAM_SOURCE => self::EXAMPLE_SOURCE,
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $response);
    }
}
