<?php

namespace Phpactor\Extension\LanguageServerSymbolProvider\Model;

use Phpactor\LanguageServerProtocol\DocumentSymbol;

interface DocumentSymbolProvider
{
    /**
     * @return array<DocumentSymbol>
     */
    public function provideFor(string $source): array;
}
