<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Handler;

use function Amp\call;
use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\ImplementationParams;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\TextDocument\TextDocumentBuilder;

class GotoImplementationHandler implements Handler, CanRegisterCapabilities
{
    public function __construct(
        private Workspace $workspace,
        private ClassImplementationFinder $finder,
        private LocationConverter $locationConverter
    ) {
    }


    public function methods(): array
    {
        return [
            'textDocument/implementation' => 'gotoImplementation',
        ];
    }

    public function gotoImplementation(ImplementationParams $params): Promise
    {
        return call(function () use ($params) {
            $textDocument = $this->workspace->get($params->textDocument->uri);
            $phpactorDocument = TextDocumentBuilder::create(
                $textDocument->text
            )->uri(
                $textDocument->uri
            )->language(
                $textDocument->languageId ?? 'php'
            )->build();

            $offset = PositionConverter::positionToByteOffset($params->position, $textDocument->text);
            $locations = $this->finder->findImplementations(
                $phpactorDocument,
                $offset
            );

            return $this->locationConverter->toLspLocations($locations);
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->implementationProvider = true;
    }
}
