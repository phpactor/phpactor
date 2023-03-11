<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Handler;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\InlayHint;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;

class InlayHintHandler implements Handler, CanRegisterCapabilities
{
    public function __construct()
    {
    }
    public function methods(): array
    {
        return [
            'textDocument/inlayHint' => 'inlayHint',
        ];
    }

    /**
     * @return Promise<InlayHint[]>
     */
    public function inlayHint(TextDocumentIdentifier $textDocument, Range $range): Promise
    {
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->inlayHintProvider = true;
    }
}
