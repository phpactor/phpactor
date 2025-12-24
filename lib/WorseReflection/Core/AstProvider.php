<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node;
use Phpactor\TextDocument\TextDocument;

interface AstProvider
{
    public function get(TextDocument $document): Node;
}
