<?php

namespace Phpactor\Extension\LanguageServerSymbolProvider\Model;

use Phpactor\LanguageServerProtocol\DocumentSymbol;
use Phpactor\TextDocument\TextDocument;

interface DocumentSymbolProvider
{
    /**
     * @return array<DocumentSymbol>
     */
    public function provideFor(TextDocument $document): array;
}
