<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Tests\Unit\Handler;

use Phpactor\Extension\LanguageServerBridge\TextDocument\WorkspaceTextDocumentLocator;
use Phpactor\LanguageServerProtocol\Location as LspLocation;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\ReferenceContext;
use Phpactor\LanguageServerProtocol\ReferencesRequest;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\ReferencesHandler;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\ReferenceFinder\PotentialLocation;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\TypeFactory;
use Prophecy\Prophecy\ObjectProphecy;

class ReferencesHandlerTest extends TestCase
{
    const EXAMPLE_URI = 'file:///test';
    const EXAMPLE_TEXT = 'hello';

    /**
     * @var ObjectProphecy<ReferenceFinder>
     */
    private ObjectProphecy $finder;

    /**
     * @var ObjectProphecy<DefinitionLocator>
     */
    private ObjectProphecy $locator;

    protected function setUp(): void
    {
        $this->finder = $this->prophesize(ReferenceFinder::class);
        $this->locator = $this->prophesize(DefinitionLocator::class);
    }

    public function testFindsReferences(): void
    {
        $document = TextDocumentBuilder::create(self::EXAMPLE_TEXT)
            ->language('php')
            ->uri(self::EXAMPLE_URI)
            ->build()
        ;

        $document2 = TextDocumentBuilder::create(self::EXAMPLE_TEXT)
            ->language('php')
            ->uri(self::EXAMPLE_URI.'2')
            ->build()
        ;

        $this->finder->findReferences(
            $document,
            ByteOffset::fromInt(0)
        )->willYield([
            PotentialLocation::surely(LocationRange::fromPathAndOffsets($document->uriOrThrow(), 2, 2)),
            PotentialLocation::surely(LocationRange::fromPathAndOffsets($document2->uriOrThrow(), 3, 3)),
            PotentialLocation::surely(LocationRange::fromPathAndOffsets($document->uriOrThrow(), 5, 5)),
        ])->shouldBeCalled();

        $tester = $this->createTester();
        $tester->textDocument()->open(self::EXAMPLE_URI.'2', self::EXAMPLE_TEXT);

        $response = $tester->requestAndWait(ReferencesRequest::METHOD, [
            'textDocument' => ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_URI),
            'position' => ProtocolFactory::position(0, 0),
            'context' => new ReferenceContext(false),
        ]);

        $locations = $response->result;
        $this->assertIsArray($locations);
        $this->assertEquals([
            new LspLocation(
                (string) $document->uri(),
                new Range(new Position(0, 2), new Position(0, 2)),
            ),
            new LspLocation(
                (string) $document->uri(),
                new Range(new Position(0, 5), new Position(0, 5)),
            ),
            new LspLocation(
                (string) $document2->uri(),
                new Range(new Position(0, 3), new Position(0, 3)),
            ),
        ], $locations);
    }

    public function testFindsReferencesIncludingDeclaration(): void
    {
        $document = TextDocumentBuilder::create(self::EXAMPLE_TEXT)
            ->uri(self::EXAMPLE_URI)
            ->language('php')
            ->build()
        ;

        $this->finder->findReferences(
            $document,
            ByteOffset::fromInt(0)
        )->willYield([
            PotentialLocation::surely(new LocationRange($document->uri(), ByteOffsetRange::fromInts(2, 5)))
        ])->shouldBeCalled();

        $this->locator->locateDefinition(
            $document,
            ByteOffset::fromInt(0)
        )->willReturn(
            TypeLocations::forLocation(
                new TypeLocation(
                    TypeFactory::class('Foo'),
                    new LocationRange($document->uri(), ByteOffsetRange::fromInts(2, 5))
                )
            )
        )->shouldBeCalled();

        $response = $this->createTester()->requestAndWait(ReferencesRequest::METHOD, [
            'textDocument' => ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_URI),
            'position' => ProtocolFactory::position(0, 0),
            'context' => new ReferenceContext(true),
        ]);
        $locations = $response->result;
        $this->assertIsArray($locations);
        $this->assertCount(2, $locations);
        $lspLocation = reset($locations);
        $this->assertInstanceOf(LspLocation::class, $lspLocation);
    }

    public function testFindsReferencesIncludingDeclarationWhenDeclarationNotFound(): void
    {
        $document = TextDocumentBuilder::create(self::EXAMPLE_TEXT)
            ->language('php')
            ->uri(self::EXAMPLE_URI)
            ->build()
        ;

        $this->finder->findReferences(
            $document,
            ByteOffset::fromInt(0)
        )->willYield([
            PotentialLocation::surely(new LocationRange($document->uriOrThrow(), ByteOffsetRange::fromInts(2, 2)))
        ])->shouldBeCalled();

        $this->locator->locateDefinition(
            $document,
            ByteOffset::fromInt(0)
        )->willReturn(new Location($document->uri(), ByteOffset::fromInt(2)))->willThrow(new CouldNotLocateDefinition('nope'));

        $tester = $this->createTester();

        $response = $tester->requestAndWait('textDocument/references', [
            'textDocument' => ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_URI),
            'position' => ProtocolFactory::position(0, 0),
            'context' => new ReferenceContext(true),
        ]);
        $locations = $response->result;
        $this->assertIsArray($locations);
        $this->assertCount(1, $locations);
        $lspLocation = reset($locations);
        $this->assertInstanceOf(LspLocation::class, $lspLocation);
    }

    private function createTester(): LanguageServerTester
    {
        $builder = LanguageServerTesterBuilder::create();
        $builder->addHandler(
            new ReferencesHandler(
                $builder->workspace(),
                $this->finder->reveal(),
                $this->locator->reveal(),
                new LocationConverter(new WorkspaceTextDocumentLocator($builder->workspace())),
                new ClientApi(TestRpcClient::create())
            )
        );
        $tester = $builder->build();
        $tester->textDocument()->open(self::EXAMPLE_URI, self::EXAMPLE_TEXT);
        return $tester;
    }
}
