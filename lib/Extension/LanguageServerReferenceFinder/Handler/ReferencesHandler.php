<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Handler;

use Amp\Delayed;
use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\Location as LspLocation;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\ReferenceContext;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\Extension\LanguageServerReferenceFinder\LanguageServerReferenceFinderExtension;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\Locations;
use Phpactor\TextDocument\TextDocumentBuilder;

class ReferencesHandler implements Handler, CanRegisterCapabilities
{
    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var ReferenceFinder
     */
    private $finder;

    /**
     * @var DefinitionLocator
     */
    private $definitionLocator;

    /**
     * @var float
     */
    private $timeoutSeconds;

    /**
     * @var LocationConverter
     */
    private $locationConverter;

    /**
     * @var ClientApi
     */
    private $clientApi;

    public function __construct(
        Workspace $workspace,
        ReferenceFinder $finder,
        DefinitionLocator $definitionLocator,
        LocationConverter $locationConverter,
        ClientApi $clientApi,
        float $timeoutSeconds = 5.0
    ) {
        $this->workspace = $workspace;
        $this->finder = $finder;
        $this->definitionLocator = $definitionLocator;
        $this->timeoutSeconds = $timeoutSeconds;
        $this->locationConverter = $locationConverter;
        $this->clientApi = $clientApi;
    }

    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [
            'textDocument/references' => 'references',
        ];
    }

    public function references(
        TextDocumentIdentifier $textDocument,
        Position $position,
        ReferenceContext $context
    ): Promise {
        return \Amp\call(function () use ($textDocument, $position, $context) {
            $textDocument = $this->workspace->get($textDocument->uri);
            $phpactorDocument = TextDocumentBuilder::create(
                $textDocument->text
            )->uri(
                $textDocument->uri
            )->language(
                $textDocument->languageId ?? 'php'
            )->build();

            $offset = PositionConverter::positionToByteOffset($position, $textDocument->text);

            $locations = [];
            if ($context->includeDeclaration) {
                try {
                    $potentialLocation = $this->definitionLocator->locateDefinition($phpactorDocument, $offset);
                    $locations[] = new Location($potentialLocation->uri(), $potentialLocation->offset());
                } catch (CouldNotLocateDefinition $notFound) {
                }
            }

            $start = microtime(true);
            $count = 0;
            $risky = 0;
            foreach ($this->finder->findReferences($phpactorDocument, $offset) as $potentialLocation) {
                if ($potentialLocation->isSurely()) {
                    $locations[] = $potentialLocation->location();
                }

                if ($potentialLocation->isMaybe()) {
                    $risky++;
                }

                if ($count++ % 100 === 0 && $count > 0) {
                    $this->clientApi->window()->showMessage()->info(sprintf(
                        '... scanned %s references confirmed %s ...',
                        $count - 1,
                        count($locations)
                    ));
                }

                if (microtime(true) - $start > $this->timeoutSeconds) {
                    $this->clientApi->window()->showMessage()->info(sprintf(
                        'Reference find stopped, %s/%s references confirmed but took too long (%s/%s seconds). Adjust `%s`',
                        count($locations),
                        $count,
                        number_format(microtime(true) - $start, 2),
                        $this->timeoutSeconds,
                        LanguageServerReferenceFinderExtension::PARAM_REFERENCE_TIMEOUT
                    ));
                    return $this->toLocations($locations);
                }

                if ($count++ % 10) {
                    // give other co-routines a chance
                    yield new Delayed(0);
                }
            }

            $this->clientApi->window()->showMessage()->info(sprintf(
                'Found %s reference(s)%s',
                count($locations),
                $risky ? sprintf(' %s unresolvable references excluded', $risky) : ''
            ));

            return $this->toLocations($locations);
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->referencesProvider = true;
    }

    /**
     * @param Location[] $locations
     * @return LspLocation[]
     */
    private function toLocations(array $locations): array
    {
        return $this->locationConverter->toLspLocations(
            (new Locations($locations))->sorted()
        );
    }
}
