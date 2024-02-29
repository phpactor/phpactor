<?php

namespace Phpactor\Extension\LanguageServerCompletion\Util;

class TextDocumentModifierResponse
{
    public function __construct(public string $body, public int $additionalOffset, public string $language)
    {
    }
}
