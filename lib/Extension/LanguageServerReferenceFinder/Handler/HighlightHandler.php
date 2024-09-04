<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Handler;

use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerReferenceFinder\Model\Highlighter;
use Phpactor\LanguageServerProtocol\DocumentHighlight;
use Phpactor\LanguageServerProtocol\DocumentHighlightParams;
use Phpactor\LanguageServerProtocol\DocumentHighlightRequest;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use function Amp\call;

class HighlightHandler implements Handler, CanRegisterCapabilities
{
    public function __construct(private Workspace $workspace, private Highlighter $highlighter)
    {
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

        return call(function () use ($textDocument, $offset) {
            return (yield $this->highlighter->highlightsFor($textDocument->text, $offset))->toArray();
        });
    }

    public function registerCapabilties(ServerCapabilities $capabilities): void
    {
        $capabilities->documentHighlightProvider = true;
    }
}
