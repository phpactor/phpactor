<?php

namespace Phpactor\Rename\Model\FileRenamer;

use Generator;
use Phpactor\Rename\Model\Exception\CouldNotRename;
use Phpactor\Rename\Model\FileRenamer;
use Phpactor\Rename\Model\LocatedTextEdit;
use Phpactor\Rename\Model\LocatedTextEdits;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\Rename\Model\RenameResult;
use Phpactor\TextDocument\TextDocumentUri;

class TestFileRenamer implements FileRenamer
{
    private LocatedTextEditsMap $workspaceEdits;

    public function __construct(
        private bool $throw = false,
        private ?RenameResult $renameResult = null,
        ?LocatedTextEditsMap $workspaceEdits = null,
    ) {
        $this->workspaceEdits = $workspaceEdits ?: LocatedTextEditsMap::create();
    }

    public function renameFile(TextDocumentUri $from, TextDocumentUri $to): Generator
    {
        if ($this->throw) {
            throw new CouldNotRename('There was a problem');
        }

        foreach ($this->workspaceEdits->toLocatedTextEdits() as $locatedTextEdits) {
            foreach ($locatedTextEdits->textEdits() as $textEdit) {
                yield new LocatedTextEdit($locatedTextEdits->documentUri(), $textEdit);
            }
        }

        if (null === $this->renameResult) {
            return;
        }

        return $this->renameResult;
    }
}
