<?php

namespace Phpactor\Rename\Model;

use Amp\Promise;
use Phpactor\TextDocument\TextDocumentUri;

interface FileRenamer
{
    /**
     * Promise can throw a CouldNotRename exception
     *
     * @return Promise<RenameEdit>
     */
    public function renameFile(TextDocumentUri $from, TextDocumentUri $to): Promise;
}
