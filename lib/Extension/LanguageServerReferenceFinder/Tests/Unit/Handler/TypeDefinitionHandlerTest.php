<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Tests\Unit\Handler;

use Phpactor\Extension\LanguageServerBridge\TextDocument\WorkspaceTextDocumentLocator;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\TypeDefinitionHandler;
use Phpactor\LanguageServerProtocol\Location;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServerProtocol\TypeDefinitionRequest;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\ReferenceFinder\TestTypeLocator;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\TextDocument\Location as PhpactorLocation;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\TypeFactory;
use function Amp\Promise\wait;

class TypeDefinitionHandlerTest extends TestCase
{
    const EXAMPLE_URI = 'file:///test';
    const EXAMPLE_TEXT = 'hello';

    public function testGoesToSingleType(): void
    {
        $locations = [
            new TypeLocation(
                TypeFactory::class('Foobar'),
                PhpactorLocation::fromPathAndOffsets(self::EXAMPLE_URI, 2, 5)
            )
        ];

        [$tester, $_] = $this->createTester($locations);
        $response = $tester->requestAndWait(TypeDefinitionRequest::METHOD, [
            'textDocument' => ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_URI),
            'position' => ProtocolFactory::position(0, 0),
        ]);

        $location = $response->result;

        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals(self::EXAMPLE_URI, $location->uri);
        $this->assertEquals(2, $location->range->start->character);
        $this->assertEquals(5, $location->range->end->character);
    }

    public function testGoesToMultipleTypes(): void
    {
        $locations = [
            new TypeLocation(
                TypeFactory::class('Foobar'),
                PhpactorLocation::fromPathAndOffsets(self::EXAMPLE_URI, 2, 2),
            ),
            new TypeLocation(
                TypeFactory::class('Barfoo'),
                PhpactorLocation::fromPathAndOffsets(self::EXAMPLE_URI, 2, 2),
            )
        ];
        [$tester, $watcher] = $this->createTester($locations);
        $promise = $tester->request(TypeDefinitionRequest::METHOD, [
            'textDocument' => ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_URI),
            'position' => ProtocolFactory::position(0, 0),
        ]);
        $watcher->resolveLastResponse(new MessageActionItem('Foobar'));
        $response = wait($promise);

        $location = $response->result;

        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals(self::EXAMPLE_URI, $location->uri);
        $this->assertEquals(2, $location->range->start->character);
    }

    /**
     * @return array{LanguageServerTester,TestResponseWatcher}
     * @param TypeLocation[] $locations
     */
    private function createTester(array $locations): array
    {
        $document = TextDocumentBuilder::create(self::EXAMPLE_TEXT)->uri(self::EXAMPLE_URI)->build();
        $builder = LanguageServerTesterBuilder::create();
        $tester = $builder->addHandler(new TypeDefinitionHandler(
            $builder->workspace(),
            new TestTypeLocator(
                new TypeLocations($locations)
            ),
            new LocationConverter(new WorkspaceTextDocumentLocator($builder->workspace())),
            $builder->clientApi(),
        ))->build();
        $tester->textDocument()->open(self::EXAMPLE_URI, self::EXAMPLE_TEXT);

        return [$tester, $builder->responseWatcher()];
    }
}
