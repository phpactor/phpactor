<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Tests\Unit\Handler;

use Phpactor\Extension\LanguageServerBridge\TextDocument\WorkspaceTextDocumentLocator;
use Phpactor\LanguageServerProtocol\Location as LspLocation;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\GotoImplementationHandler;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\LocationRanges;
use Phpactor\TextDocument\Locations;
use Phpactor\TextDocument\TextDocumentBuilder;
use Prophecy\Prophecy\ObjectProphecy;

class GotoImplementationHandlerTest extends TestCase
{
    const EXAMPLE_URI = 'file:///test.php';
    const EXAMPLE_TEXT = 'hello';

    /**
     * @var ObjectProphecy<ClassImplementationFinder>
     */
    private ObjectProphecy $finder;

    protected function setUp(): void
    {
        $this->finder = $this->prophesize(ClassImplementationFinder::class);
    }

    public function testGoesToImplementation(): void
    {
        $document = TextDocumentBuilder::create(self::EXAMPLE_TEXT)
            ->language('php')
            ->uri(self::EXAMPLE_URI)
            ->build()
        ;

        $this->finder->findImplementations(
            $document,
            ByteOffset::fromInt(0)
        )->willReturn(new LocationRanges([
            new LocationRange($document->uriOrThrow(), ByteOffsetRange::fromInts(2, 2))
        ]));

        $builder = LanguageServerTesterBuilder::create();
        $tester = $builder->addHandler(new GotoImplementationHandler(
            $builder->workspace(),
            $this->finder->reveal(),
            new LocationConverter(new WorkspaceTextDocumentLocator($builder->workspace()))
        ))->build();
        $tester->textDocument()->open(self::EXAMPLE_URI, self::EXAMPLE_TEXT);

        $response = $tester->requestAndWait('textDocument/implementation', [
            'textDocument' => ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_URI),
            'position' => ProtocolFactory::position(0, 0),
        ]);

        $locations = $response->result;

        $this->assertIsArray($locations);
        $this->assertCount(1, $locations);
        $lspLocation = reset($locations);
        $this->assertInstanceOf(LspLocation::class, $lspLocation);
    }
}
