<?php

namespace Phpactor\Extension\LanguageServerRename\Util;

use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\OptionalVersionedTextDocumentIdentifier;
use Phpactor\Rename\Model\LocatedTextEdits;
use Phpactor\Rename\Model\WorkspaceOperations;
use Phpactor\Rename\Model\RenameResult;
use Phpactor\LanguageServerProtocol\RenameFile;
use Phpactor\LanguageServerProtocol\TextDocumentEdit;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentLocator;

final class WorkspaceOperationsConverter
{
    public function __construct(
        private Workspace $workspace,
        private TextDocumentLocator $locator
    ) {
    }

    public function toWorkspaceEdit(WorkspaceOperations $edits): WorkspaceEdit
    {
        $documentEdits = [];

        foreach ($edits as $edit) {
            if ($edit instanceof RenameResult) {
                $documentEdits[] = new RenameFile(
                    'rename',
                    $edit->oldUri(),
                    $edit->newUri(),
                );
            }
            if ($edit instanceof LocatedTextEdits) {
                $documentEdits[] = $this->toTextDocumentEdit($edit);
            }
        }

        return new WorkspaceEdit(null, $documentEdits);
    }

    public function toTextDocumentEdit(LocatedTextEdits $locatedTextEdits): TextDocumentEdit
    {
        $version = $this->getDocumentVersion((string)$locatedTextEdits->documentUri());
        $documentEdit = new TextDocumentEdit(
            new OptionalVersionedTextDocumentIdentifier(
                uri: (string)$locatedTextEdits->documentUri(),
                version: $version,
            ),
            TextEditConverter::toLspTextEdits(
                $locatedTextEdits->textEdits(),
                (string)$this->locator->get($locatedTextEdits->documentUri())
            )
        );

        $new = [];
        foreach ($documentEdit->edits as $edit) {
            $new[sprintf(
                '%s-%s-%s',
                $edit->range->start->line,
                $edit->range->start->character,
                $edit->newText
            )] = $edit;
        }
        $documentEdit->edits = array_values($new);
        return $documentEdit;
    }

    private function getDocumentVersion(string $uri): int
    {
        return $this->workspace->has($uri) ? $this->workspace->get($uri)->version : 0;
    }
}
