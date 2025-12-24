<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\TextDocument\TextDocument;

final class TolerantAstProvider implements AstProvider
{
    public function __construct(private Parser $parser = new Parser())
    {
    }

    public function get(string|TextDocument $document, ?string $uri = null): Node
    {
        if (is_string($document)) {
            return $this->parser->parseSourceFile($document, $uri);
        }

        return $this->parser->parseSourceFile(
            $document->__toString(),
            $document->uri()?->__toString(),
        );
    }
}
