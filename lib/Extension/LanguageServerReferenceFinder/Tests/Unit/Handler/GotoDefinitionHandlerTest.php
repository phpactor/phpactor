<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Tests\Unit\Handler;

use Phpactor\Extension\LanguageServerBridge\TextDocument\WorkspaceTextDocumentLocator;
use Phpactor\LanguageServerProtocol\DefinitionRequest;
use Phpactor\LanguageServerProtocol\Location;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\GotoDefinitionHandler;
use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\ReferenceFinder\TestDefinitionLocator;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\TextDocument\Location as PhpactorLocation;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\TypeFactory;
use function Amp\Promise\wait;

class GotoDefinitionHandlerTest extends TestCase
{
    const EXAMPLE_URI = 'file:///test';
    const EXAMPLE_TEXT = 'hello';

    public function testGoesToDefinition(): void
    {
        $document = TextDocumentBuilder::create(self::EXAMPLE_TEXT)->uri(self::EXAMPLE_URI)->build();

        $locations = [
            new TypeLocation(
                TypeFactory::class('Foo'),
                PhpactorLocation::fromPathAndOffsets((string) $document->uriOrThrow(), 2, 2)
            )
        ];
        [$tester, $_] = $this->createTester($locations);

        $response = $tester->requestAndWait(DefinitionRequest::METHOD, [
            'textDocument' => ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_URI),
            'position' => ProtocolFactory::position(0, 0),
        ]);

        $location = $response->result;

        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals(self::EXAMPLE_URI, $location->uri);
        $this->assertEquals(2, $location->range->start->character);
    }

    public function testPresentChoiceIfAmbiguous(): void
    {
        $locations = [
            new TypeLocation(TypeFactory::class('Foobar'), PhpactorLocation::fromPathAndOffsets(self::EXAMPLE_URI, 2, 2)),
            new TypeLocation(TypeFactory::class('Barfoo'), PhpactorLocation::fromPathAndOffsets(self::EXAMPLE_URI, 2, 2)),
        ];
        [$tester, $builder] = $this->createTester($locations);
        $watcher = $builder->responseWatcher();
        $promise = $tester->request(DefinitionRequest::METHOD, [
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
     * @return array{LanguageServerTester,LanguageServerTesterBuilder}
     * @param TypeLocation[] $locations
     */
    private function createTester(array $locations): array
    {
        $builder = LanguageServerTesterBuilder::create();

        $tester = $builder->addHandler(new GotoDefinitionHandler(
            $builder->workspace(),
            new TestDefinitionLocator(new TypeLocations($locations)),
            new LocationConverter(new WorkspaceTextDocumentLocator($builder->workspace())),
            $builder->clientApi()
        ))->build();
        $tester->textDocument()->open(self::EXAMPLE_URI, self::EXAMPLE_TEXT);
        return [$tester, $builder];
    }
}
