<?php

namespace Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\Node;

interface Printer
{
    public function print(Node $node): string;
}
