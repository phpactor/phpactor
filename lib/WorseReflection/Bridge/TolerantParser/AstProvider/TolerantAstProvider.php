<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider;

use Microsoft\PhpParser\Node;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\AstProvider;

final class TolerantAstProvider implements AstProvider
{
    public function __construct(private AstProvider $parser = new TolerantAstProvider())
    {
    }

    public function get(TextDocument $document): Node
    {
        return $this->parser->parseSourceFile(
            $document->__toString(),
            $document->uri(),
        );
    }
}
