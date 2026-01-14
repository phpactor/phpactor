<?php

namespace Phpactor\Rename\Model;

use Amp\Promise;
use Phpactor\TextDocument\TextDocumentUri;

interface FileRenamer
{
    /**
     * Promise can throw a CouldNotRename exception
     *
     * @return Promise<WorkspaceOperations>
     */
    public function renameFile(TextDocumentUri $from, TextDocumentUri $to): Promise;
}
