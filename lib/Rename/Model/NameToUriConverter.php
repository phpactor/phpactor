<?php

namespace Phpactor\Rename\Model;

use Phpactor\Rename\Model\Exception\CouldNotConvertUriToClass;
use Phpactor\TextDocument\TextDocumentUri;

interface NameToUriConverter
{
    /**
     * @throws CouldNotConvertUriToClass
     */
    public function convert(string $className): TextDocumentUri;
}
