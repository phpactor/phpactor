<?php

namespace Phpactor\Extension\LanguageServer\Server\Method\TextDocument;

use Phpactor\Extension\LanguageServer\Protocol\TextDocumentItem;
use Phpactor\Extension\LanguageServer\Server\Method;
use Phpactor\Extension\LanguageServer\Server\Workspace;

class DidChange implements Method
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
        return 'textDocument/didChange';
    }

    public function __invoke(TextDocumentItem $textDocument, array $contentChanges)
    {
        foreach ($contentChanges as $contentChange) {
            $this->workspace->update($textDocument, $contentChange['text']);
        }
    }
}
