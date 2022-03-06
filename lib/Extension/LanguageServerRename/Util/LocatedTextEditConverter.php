<?php

namespace Phpactor\Extension\LanguageServerRename\Util;

use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\Extension\LanguageServerRename\Model\LocatedTextEditsMap;
use Phpactor\LanguageServerProtocol\TextDocumentEdit;
use Phpactor\LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentLocator;

final class LocatedTextEditConverter
{
    /**
     * @var Workspace
     */
    private $workspace;
    /**
     * @var TextDocumentLocator
     */
    private $locator;

    public function __construct(Workspace $workspace, TextDocumentLocator $locator)
    {
        $this->workspace = $workspace;
        $this->locator = $locator;
    }

    public function toWorkspaceEdit(LocatedTextEditsMap $map): WorkspaceEdit
    {
        $documentEdits = [];
        foreach ($map->toLocatedTextEdits() as $result) {
            $version = $this->getDocumentVersion((string)$result->documentUri());
            $documentEdits[] = new TextDocumentEdit(
                new VersionedTextDocumentIdentifier(
                    (string)$result->documentUri(),
                    $version
                ),
                TextEditConverter::toLspTextEdits(
                    $result->textEdits(),
                    (string)$this->locator->get($result->documentUri())
                )
            );
        }
        return new WorkspaceEdit(null, $documentEdits);
    }

    private function getDocumentVersion(string $uri): int
    {
        return $this->workspace->has($uri) ? $this->workspace->get($uri)->version : 0;
    }
}
