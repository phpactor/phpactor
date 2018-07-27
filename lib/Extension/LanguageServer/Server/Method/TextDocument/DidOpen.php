<?php

namespace Phpactor\Extension\LanguageServer\Server\Method\TextDocument;

use Phpactor\Extension\LanguageServer\Protocol\TextDocumentItem;
use Phpactor\Extension\LanguageServer\Server\Method;
use Phpactor\Extension\LanguageServer\Server\Workspace;

class DidOpen implements Method
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function name(): string
    {
        return 'textDocument/didOpen';
    }

    public function __invoke(TextDocumentItem $textDocument)
    {
        $this->workspace->open($textDocument);
    }
}
