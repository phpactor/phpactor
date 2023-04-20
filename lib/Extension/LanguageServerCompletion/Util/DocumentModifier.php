<?php

namespace Phpactor\Extension\LanguageServerCompletion\Util;

use Phpactor\LanguageServerProtocol\TextDocumentItem;

interface DocumentModifier
{
    public function process(string $text, TextDocumentItem $document): ?TextDocumentModifierResponse;
}
