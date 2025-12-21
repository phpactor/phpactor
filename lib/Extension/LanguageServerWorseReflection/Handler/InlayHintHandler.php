<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Handler;

use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerWorseReflection\InlayHint\InlayHintProvider;
use Phpactor\LanguageServerProtocol\InlayHint;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Workspace\Workspace;

class InlayHintHandler implements Handler, CanRegisterCapabilities
{
    public function __construct(
        private InlayHintProvider $provider,
        private Workspace $workspace
    ) {
    }
    public function methods(): array
    {
        return [
            'textDocument/inlayHint' => 'inlayHint',
        ];
    }

    /**
     * @return Promise<list<InlayHint>>
     */
    public function inlayHint(TextDocumentIdentifier $textDocument, Range $range): Promise
    {
        $document = $this->workspace->get($textDocument->uri);

        return $this->provider->inlayHints(
            TextDocumentConverter::fromLspTextItem($document),
            RangeConverter::toPhpactorRange($range, $document->text)
        );
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->inlayHintProvider = true;
    }
}
