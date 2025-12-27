<?php

namespace Phpactor\Extension\LanguageServerSymbolProvider\Handler;

use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerSymbolProvider\Model\DocumentSymbolProvider;
use Phpactor\LanguageServerProtocol\DocumentSymbolParams;
use Phpactor\LanguageServerProtocol\DocumentSymbolRequest;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;

class DocumentSymbolProviderHandler implements Handler, CanRegisterCapabilities
{
    public function __construct(
        private readonly Workspace $workspace,
        private readonly DocumentSymbolProvider $provider
    ) {
    }


    public function methods(): array
    {
        return [
            DocumentSymbolRequest::METHOD => 'documentSymbols',
        ];
    }

    /**
     * @return Promise<array>
     */
    public function documentSymbols(DocumentSymbolParams $params): Promise
    {
        $textDocument = $this->workspace->get($params->textDocument->uri);

        return new Success($this->provider->provideFor(TextDocumentConverter::fromLspTextItem($textDocument)));
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->documentSymbolProvider = true;
    }
}
