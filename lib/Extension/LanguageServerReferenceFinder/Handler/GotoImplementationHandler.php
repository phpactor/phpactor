<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Handler;

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
    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var ClassImplementationFinder
     */
    private $finder;

    /**
     * @var LocationConverter
     */
    private $locationConverter;

    public function __construct(Workspace $workspace, ClassImplementationFinder $finder, LocationConverter $locationConverter)
    {
        $this->workspace = $workspace;
        $this->finder = $finder;
        $this->locationConverter = $locationConverter;
    }

    /**
     * {@inheritDoc}
     */
    public function methods(): array
    {
        return [
            'textDocument/implementation' => 'gotoImplementation',
        ];
    }

    public function gotoImplementation(ImplementationParams $params): Promise
    {
        return \Amp\call(function () use ($params) {
            $textDocument = $this->workspace->get($params->textDocument->uri);
            $phpactorDocument = TextDocumentBuilder::create(
                $textDocument->text
            )->uri(
                $textDocument->uri
            )->language(
                $textDocument->languageId ?? 'php'
            )->build();

            $offset = PositionConverter::positionToByteOffset($params->position, $textDocument->text);
            $locations = $this->finder->findImplementations($phpactorDocument, $offset);

            return $this->locationConverter->toLspLocations($locations);
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->implementationProvider = true;
    }
}
