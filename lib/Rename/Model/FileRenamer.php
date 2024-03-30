<?php

namespace Phpactor\Rename\Model;

use Amp\Promise;
use Phpactor\TextDocument\TextDocumentUri;

interface FileRenamer
{
    /**
     * Promise can throw a CouldNotRename exception
     *
     * @return Promise<WorkspaceRenameEdits>
     */
    public function renameFile(TextDocumentUri $from, TextDocumentUri $to): Promise;
}
