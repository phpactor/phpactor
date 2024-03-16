<?php

namespace Phpactor\Extension\LanguageServerRename\Util;

use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\OptionalVersionedTextDocumentIdentifier;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\Rename\Model\RenameEdit;
use Phpactor\Rename\Model\RenameResult;
use Phpactor\LanguageServerProtocol\RenameFile;
use Phpactor\LanguageServerProtocol\TextDocumentEdit;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentLocator;

final class RenameEditConverter
{
    public function __construct(private Workspace $workspace, private TextDocumentLocator $locator)
    {
    }

    public function toWorkspaceEdit(RenameEdit $edits): WorkspaceEdit
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
            if ($edit instanceof LocatedTextEditsMap) {
                foreach ($this->prepareDocumentEdits($edit) as $textEdit) {
                    $documentEdits[] = $textEdit;
                }
            }
        }

        return new WorkspaceEdit(null, $documentEdits);
    }

    /**
     * @return TextDocumentEdit[]
     */
    private function prepareDocumentEdits(LocatedTextEditsMap $map): array
    {
        $documentEdits = [];
        foreach ($map->toLocatedTextEdits() as $result) {
            $version = $this->getDocumentVersion((string)$result->documentUri());
            $documentEdits[] = new TextDocumentEdit(
                new OptionalVersionedTextDocumentIdentifier(
                    uri: (string)$result->documentUri(),
                    version: $version,
                ),
                TextEditConverter::toLspTextEdits(
                    $result->textEdits(),
                    (string)$this->locator->get($result->documentUri())
                )
            );
        }

        // deduplicate the edits: with renaming we currently have multiple
        // references to the declaration.
        return array_map(
            function (TextDocumentEdit $documentEdit) {
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
            }, $documentEdits
        );
    }

    private function getDocumentVersion(string $uri): int
    {
        return $this->workspace->has($uri) ? $this->workspace->get($uri)->version : 0;
    }
}
