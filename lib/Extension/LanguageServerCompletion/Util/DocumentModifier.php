<?php

namespace Phpactor\Extension\LanguageServerCompletion\Util;

use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\Position;

interface DocumentModifier
{
    public function process(
        string $text,
        TextDocumentItem $document,
        Position $position
    ): ?TextDocumentModifierResponse;
}
