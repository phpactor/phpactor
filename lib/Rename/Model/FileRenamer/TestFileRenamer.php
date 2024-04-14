<?php

namespace Phpactor\Rename\Model\FileRenamer;

use Amp\Failure;
use Amp\Promise;
use Amp\Success;
use Phpactor\Rename\Model\Exception\CouldNotRename;
use Phpactor\Rename\Model\FileRenamer;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\Rename\Model\WorkspaceOperations;
use Phpactor\TextDocument\TextDocumentUri;

class TestFileRenamer implements FileRenamer
{
    private WorkspaceOperations $renameEdit;

    public function __construct(
        private bool $throw = false,
        ?WorkspaceOperations $renameEdit = null,
    ) {
        $this->renameEdit = $renameEdit ?: new WorkspaceOperations(
            LocatedTextEditsMap::create()->toLocatedTextEdits(),
        );
    }

    public function renameFile(TextDocumentUri $from, TextDocumentUri $to): Promise
    {
        if ($this->throw) {
            return new Failure(new CouldNotRename('There was a problem'));
        }

        return new Success($this->renameEdit);
    }
}
