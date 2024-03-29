<?php

namespace Phpactor\Rename\Model\FileRenamer;

use Amp\Failure;
use Amp\Promise;
use Amp\Success;
use Phpactor\Rename\Model\Exception\CouldNotRename;
use Phpactor\Rename\Model\FileRenamer;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\Rename\Model\WorkspaceRenameEdits;
use Phpactor\TextDocument\TextDocumentUri;

class TestFileRenamer implements FileRenamer
{
    private WorkspaceRenameEdits $renameEdit;

    public function __construct(
        private bool $throw = false,
        ?WorkspaceRenameEdits $renameEdit = null,
    ) {
        $this->renameEdit = $renameEdit ?: new WorkspaceRenameEdits(
            LocatedTextEditsMap::create(),
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
