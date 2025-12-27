<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Handler;

use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServer\WorkDoneProgress\WorkDoneToken;
use function Amp\call;
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
    public function __construct(
        private readonly Workspace $workspace,
        private readonly ReferenceFinder $finder,
        private readonly DefinitionLocator $definitionLocator,
        private readonly LocationConverter $locationConverter,
        private readonly ClientApi $clientApi,
        private readonly float $timeoutSeconds = 30.0,
        private readonly float $softTimeoutSeconds = 10.0,
    ) {
    }


    public function methods(): array
    {
        return [
            'textDocument/references' => 'references',
        ];
    }

    /**
     * @return Promise<array<LspLocation>>
     */
    public function references(
        TextDocumentIdentifier $textDocument,
        Position $position,
        ReferenceContext $context
    ): Promise {
        return call(function () use ($textDocument, $position, $context) {
            $textDocument = $this->workspace->get($textDocument->uri);
            $phpactorDocument = TextDocumentBuilder::create(
                $textDocument->text
            )->uri(
                $textDocument->uri
            )->language(
                $textDocument->languageId
            )->build();

            $offset = PositionConverter::positionToByteOffset($position, $textDocument->text);

            $locations = [];
            if ($context->includeDeclaration) {
                try {
                    $potentialLocation = $this->definitionLocator->locateDefinition($phpactorDocument, $offset)->first()->location();
                    $locations[] = new Location($potentialLocation->uri(), $potentialLocation->range());
                } catch (CouldNotLocateDefinition) {
                }
            }

            $token = WorkDoneToken::generate();
            $this->clientApi->workDoneProgress()->begin($token, 'Finding references');
            $start = microtime(true);
            $count = 0;
            $risky = 0;
            $dontAsk = false;
            foreach ($this->finder->findReferences($phpactorDocument, $offset) as $potentialLocation) {
                if ($potentialLocation->isSurely()) {
                    $locations[] = $potentialLocation->location();
                }

                if ($potentialLocation->isMaybe()) {
                    $risky++;
                }

                $count++;
                $this->clientApi->workDoneProgress()->report($token, sprintf(
                    '... analysed %s references confirmed %s ...',
                    $count - 1,
                    count($locations)
                ));

                if (false === $dontAsk && microtime(true) - $start > $this->softTimeoutSeconds) {
                    $no = new MessageActionItem('No, show me what you got');
                    $another = new MessageActionItem(sprintf('Another %.2f seconds', $this->softTimeoutSeconds));
                    $until = new MessageActionItem(sprintf('Keep going until the %.2f second hard timeout', $this->timeoutSeconds));
                    $selection = yield $this->clientApi->window()->showMessageRequest()->info(
                        sprintf(
                            'Finding references is taking a while, scanned %d and confirmed %d - do you want to continue?',
                            $count - 1,
                            count($locations),
                        ),
                        $no,
                        $another,
                        $until,
                    );
                    if ($selection == $no) {
                        break;
                    }
                    if ($selection == $another) {
                        $this->clientApi->workDoneProgress()->report(
                            $token,
                            sprintf('searching for another %.2f seconds', $this->softTimeoutSeconds)
                        );
                        $start = microtime(true);
                        continue;
                    }
                    if ($selection == $until) {
                        $dontAsk = true;
                    }
                }

                if (microtime(true) - $start > $this->timeoutSeconds) {
                    $this->clientApi->workDoneProgress()->end(
                        $token,
                        sprintf(
                            'Reference finding stopped, %s/%s references confirmed but took too long (%s/%s seconds). Adjust `%s`',
                            count($locations),
                            $count,
                            number_format(microtime(true) - $start, 2),
                            $this->timeoutSeconds,
                            LanguageServerReferenceFinderExtension::PARAM_REFERENCE_TIMEOUT
                        )
                    );
                    return $this->toLocations($locations);
                }

                if ($count % 10) {
                    // give other co-routines a chance
                    yield new Delayed(0);
                }
            }

            $this->clientApi->workDoneProgress()->end($token, sprintf(
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
     * @param array<Location> $ranges
     * @return LspLocation[]
     */
    private function toLocations(array $ranges): array
    {
        return $this->locationConverter->toLspLocations((new Locations($ranges))->sorted());
    }
}
