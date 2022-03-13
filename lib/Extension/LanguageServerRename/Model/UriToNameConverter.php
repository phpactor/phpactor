<?php

namespace Phpactor\Extension\LanguageServerRename\Model;

use Phpactor\Extension\LanguageServerRename\Model\Exception\CouldNotConvertUriToClass;
use Phpactor\TextDocument\TextDocumentUri;

interface UriToNameConverter
{
    /**
     * @throws CouldNotConvertUriToClass
     */
    public function convert(TextDocumentUri $uri): string;
}
