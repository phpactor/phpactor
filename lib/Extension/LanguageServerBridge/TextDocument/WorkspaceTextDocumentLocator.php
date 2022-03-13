<?php

namespace Phpactor\Extension\LanguageServerBridge\TextDocument;

use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\LanguageServer\Core\Workspace\Exception\UnknownDocument;
use Phpactor\LanguageServer\Core\Workspace\Workspace as PhpactorWorkspace;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;

class WorkspaceTextDocumentLocator implements TextDocumentLocator
{
    private PhpactorWorkspace $workspace;

    public function __construct(PhpactorWorkspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function get(TextDocumentUri $uri): TextDocument
    {
        try {
            return TextDocumentConverter::fromLspTextItem($this->workspace->get($uri->__toString()));
        } catch (UnknownDocument $unknown) {
        }

        throw TextDocumentNotFound::fromUri($uri);
    }
}
