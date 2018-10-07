<?php

namespace Phpactor\Extension\LanguageServer\Extension;

use Generator;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\LanguageServer\Core\Transport\NotificationMessage;
use Phpactor\LanguageServer\Extension\Core\TextDocument\DidChange;

class DidChangeHandler extends DidChange
{
    /**
     * @var Manager
     */
    private $sessionManager;

    public function __construct(Manager $sessionManager)
    {
        parent::__construct($sessionManager);
        $this->sessionManager = $sessionManager;
    }

    public function name(): string
    {
        return 'textDocument/didChange';
    }

    public function __invoke(VersionedTextDocumentIdentifier $textDocument, array $contentChanges): Generator
    {
        parent::__invoke($textDocument, $contentChanges);

        yield $this->clearDiagnostics($textDocument);;
    }

    private function clearDiagnostics(VersionedTextDocumentIdentifier $textDocument)
    {
        return new NotificationMessage('textDocument/publishDiagnostics', [
            'uri' => $textDocument->uri,
            'diagnostics' =>  [],
        ]);
    }
}
