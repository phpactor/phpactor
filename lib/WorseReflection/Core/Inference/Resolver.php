<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Type;

interface Resolver
{
    public function resolve(SymbolContextResolver $resolver, Frame $frame, Node $node): Type;
}
