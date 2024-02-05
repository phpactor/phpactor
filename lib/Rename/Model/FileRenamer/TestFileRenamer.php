<?php

namespace Phpactor\Rename\Model\FileRenamer;

use Amp\Failure;
use Amp\Promise;
use Amp\Success;
use Generator;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\Rename\Model\Exception\CouldNotRename;
use Phpactor\Rename\Model\FileRenamer;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\TextDocument\TextDocumentUri;

class TestFileRenamer implements FileRenamer
{
    private WorkspaceEdit $workspaceEdit;

    public function __construct(private bool $throw = false, ?WorkspaceEdit $workspaceEdit = null)
    {
        $this->workspaceEdit = $workspaceEdit ?: new WorkspaceEdit(documentChanges: []);
    }

    public function renameFile(TextDocumentUri $from, TextDocumentUri $to): Generator
    {
        if ($this->throw) {
            return new Failure(new CouldNotRename('There was a problem'));
        }
        return new Success($this->workspaceEdit);
    }
}
