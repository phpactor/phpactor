<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Handler;

use Amp\Promise;
use LanguageServerProtocol\Location;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\DefinitionParams;
use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateType;
use Phpactor\TextDocument\TextDocumentBuilder;

class GotoDefinitionHandler implements Handler, CanRegisterCapabilities
{
    private DefinitionLocator $definitionLocator;

    private Workspace $workspace;

    private LocationConverter $locationConverter;

    private ClientApi $clientApi;

    public function __construct(
        Workspace $workspace,
        DefinitionLocator $definitionLocator,
        LocationConverter $locationConverter,
        ClientApi $clientApi
    ) {
        $this->definitionLocator = $definitionLocator;
        $this->workspace = $workspace;
        $this->locationConverter = $locationConverter;
        $this->clientApi = $clientApi;
    }

    public function methods(): array
    {
        return [
            'textDocument/definition' => 'definition',
        ];
    }

    /**
     * @return Promise<Location>
     */
    public function definition(DefinitionParams $params): Promise
    {
        return \Amp\call(function () use ($params) {
            $textDocument = $this->workspace->get($params->textDocument->uri);

            $offset = PositionConverter::positionToByteOffset($params->position, $textDocument->text);

            try {
                $typeLocations = $this->definitionLocator->locateDefinition(
                    TextDocumentBuilder::create(
                        $textDocument->text
                    )->uri($textDocument->uri)->language('php')->build(),
                    $offset
                );
            } catch (CouldNotLocateDefinition $couldNotLocateDefinition) {
                return null;
            }

            if ($typeLocations->count() === 1) {
                return $this->locationConverter->toLspLocation($typeLocations->first()->location());
            }

            $actions = [];
            foreach ($typeLocations as $typeLocation) {
                $actions[] = new MessageActionItem(sprintf('%s', $typeLocation->type()->__toString()));
            }

            $item = yield $this->clientApi->window()->showMessageRequest()->info('Goto type', ...$actions);

            if (!$item instanceof MessageActionItem) {
                throw new CouldNotLocateType(
                    'Client did not return an action item'
                );
            }

            return $this->locationConverter->toLspLocation(
                $typeLocations->byTypeName($item->title)->location()
            );
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->definitionProvider = true;
    }
}
