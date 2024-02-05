<?php

namespace Phpactor\Rename\Model;

use Amp\Promise;
use Generator;
use Phpactor\TextDocument\TextDocumentUri;

interface FileRenamer
{
    /**
     * Promise can throw a CouldNotRename exception
     *
     * @return Promise<LocatedTextEdit>
     */
    public function renameFile(TextDocumentUri $from, TextDocumentUri $to): Generator;
}
