<?php

namespace Phpactor\WorseReflection\DocblockParser;

use Phpactor\WorseReflection\DocblockParser\Ast\Node;

interface Printer
{
    public function print(Node $node): string;
}
