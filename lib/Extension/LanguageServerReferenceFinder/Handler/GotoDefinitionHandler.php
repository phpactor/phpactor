<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Handler;

use function Amp\call;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Location;
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
    public function __construct(
        private Workspace $workspace,
        private DefinitionLocator $definitionLocator,
        private LocationConverter $locationConverter,
        private ClientApi $clientApi
    ) {
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
        return call(function () use ($params) {
            $textDocument = $this->workspace->get($params->textDocument->uri);

            $offset = PositionConverter::positionToByteOffset($params->position, $textDocument->text);

            try {
                $typeLocations = $this->definitionLocator->locateDefinition(
                    TextDocumentBuilder::create(
                        $textDocument->text
                    )->uri($textDocument->uri)->language(
                        $textDocument->languageId,
                    )->build(),
                    $offset
                );
            } catch (CouldNotLocateDefinition) {
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
