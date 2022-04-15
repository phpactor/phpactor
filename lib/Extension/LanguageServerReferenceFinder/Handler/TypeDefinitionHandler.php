<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Handler;

use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\Location;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateType;
use Phpactor\ReferenceFinder\TypeLocator;
use Phpactor\TextDocument\TextDocumentBuilder;

class TypeDefinitionHandler implements Handler, CanRegisterCapabilities
{
    private TypeLocator $typeLocator;

    private Workspace $workspace;

    private LocationConverter $locationConverter;

    public function __construct(Workspace $workspace, TypeLocator $typeLocator, LocationConverter $locationConverter)
    {
        $this->typeLocator = $typeLocator;
        $this->workspace = $workspace;
        $this->locationConverter = $locationConverter;
    }

    /**
     * @return array<string,string>
     */
    public function methods(): array
    {
        return [
            'textDocument/typeDefinition' => 'type',
        ];
    }

    /**
     * @return Promise<Location>
     */
    public function type(
        TextDocumentIdentifier $textDocument,
        Position $position
    ): Promise {
        return \Amp\call(function () use ($textDocument, $position) {
            $textDocument = $this->workspace->get($textDocument->uri);

            $offset = PositionConverter::positionToByteOffset($position, $textDocument->text);

            try {
                $typeLocations = $this->typeLocator->locateTypes(
                    TextDocumentBuilder::create($textDocument->text)->uri($textDocument->uri)->language('php')->build(),
                    $offset
                );
            } catch (CouldNotLocateType $type) {
                return null;
            }

            return $this->locationConverter->toLspLocation($typeLocations->first()->location());
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->typeDefinitionProvider = true;
    }
}
