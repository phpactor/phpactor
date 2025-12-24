<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\TextDocument\TextDocument;

interface AstProvider
{
    public function get(string|TextDocument $document, ?string $uri = null): SourceFileNode;
}
