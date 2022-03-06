<?php

namespace Phpactor\Completion\Core;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

interface SignatureHelper
{
    public function signatureHelp(TextDocument $textDocument, ByteOffset $offset): SignatureHelp;
}
