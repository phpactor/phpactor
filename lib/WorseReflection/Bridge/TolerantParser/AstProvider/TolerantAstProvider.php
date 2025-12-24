<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\AstProvider;

final class TolerantAstProvider implements AstProvider
{
    public function __construct(private Parser $parser = new Parser())
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
