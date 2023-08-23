<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Handler;

use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\LanguageServerProtocol\Location;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\DefinitionParams;
use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
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
        return \Amp\call(function () use ($params): Range {
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
                return RangeConverter::toLspRange($typeLocations->first()->range(), $textDocument->text);
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

            return RangeConverter::toLspRange($typeLocations->byTypeName($item->title)->range(), $textDocument->text);
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->definitionProvider = true;
    }
}
