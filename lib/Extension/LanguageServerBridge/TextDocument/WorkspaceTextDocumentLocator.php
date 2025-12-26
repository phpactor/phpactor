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
    public function __construct(private readonly PhpactorWorkspace $workspace)
    {
    }

    public function get(TextDocumentUri $uri): TextDocument
    {
        try {
            return TextDocumentConverter::fromLspTextItem($this->workspace->get($uri->__toString()));
        } catch (UnknownDocument) {
        }

        throw TextDocumentNotFound::fromUri($uri);
    }
}
