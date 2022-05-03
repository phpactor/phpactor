<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Handler;

use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerReferenceFinder\Model\Highlighter;
use Phpactor\LanguageServerProtocol\DocumentHighlight;
use Phpactor\LanguageServerProtocol\DocumentHighlightParams;
use Phpactor\LanguageServerProtocol\DocumentHighlightRequest;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;

class HighlightHandler implements Handler, CanRegisterCapabilities
{
    private Workspace $workspace;

    private Highlighter $highlighter;

    public function __construct(Workspace $workspace, Highlighter $highlighter)
    {
        $this->workspace = $workspace;
        $this->highlighter = $highlighter;
    }

    
    public function methods(): array
    {
        return [
            DocumentHighlightRequest::METHOD => 'highlight',
        ];
    }

    /**
     * @return Promise<array<DocumentHighlight>|null>
     */
    public function highlight(DocumentHighlightParams $params): Promise
    {
        $textDocument = $this->workspace->get($params->textDocument->uri);
        $offset = PositionConverter::positionToByteOffset($params->position, $textDocument->text);

        return new Success($this->highlighter->highlightsFor($textDocument->text, $offset)->toArray());
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->documentHighlightProvider = true;
    }
}
