<?php

namespace Phpactor\TextDocument\Exception;

use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;

final class TextDocumentNotFound extends RuntimeException
{
    public static function fromUri(TextDocumentUri $uri): self
    {
        return new self(sprintf(
            'Text document "%s" not found',
            $uri
        ));
    }
}
