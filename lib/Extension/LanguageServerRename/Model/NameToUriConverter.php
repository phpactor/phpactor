<?php

namespace Phpactor\Extension\LanguageServerRename\Model;

use Phpactor\Extension\LanguageServerRename\Model\Exception\CouldNotConvertUriToClass;
use Phpactor\TextDocument\TextDocumentUri;

interface NameToUriConverter
{
    /**
     * @throws CouldNotConvertUriToClass
     */
    public function convert(string $uri): TextDocumentUri;
}
