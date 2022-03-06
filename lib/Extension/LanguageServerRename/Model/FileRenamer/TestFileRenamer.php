<?php

namespace Phpactor\Extension\LanguageServerRename\Model\FileRenamer;

use Amp\Failure;
use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServerRename\Model\Exception\CouldNotRename;
use Phpactor\Extension\LanguageServerRename\Model\FileRenamer;
use Phpactor\Extension\LanguageServerRename\Model\LocatedTextEditsMap;
use Phpactor\TextDocument\TextDocumentUri;

class TestFileRenamer implements FileRenamer
{
    /**
     * @var bool
     */
    private $throw;

    /**
     * @var LocatedTextEditsMap
     */
    private $workspaceEdits;

    public function __construct(bool $throw = false, ?LocatedTextEditsMap $workspaceEdits = null)
    {
        $this->throw = $throw;
        $this->workspaceEdits = $workspaceEdits ?: LocatedTextEditsMap::create();
    }

    public function renameFile(TextDocumentUri $from, TextDocumentUri $to): Promise
    {
        if ($this->throw) {
            return new Failure(new CouldNotRename('There was a problem'));
        }
        return new Success($this->workspaceEdits);
    }
}
