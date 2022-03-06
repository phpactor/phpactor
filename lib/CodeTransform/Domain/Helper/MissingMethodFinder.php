<?php

namespace Phpactor\CodeTransform\Domain\Helper;

use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder\MissingMethod;
use Phpactor\TextDocument\TextDocument;

interface MissingMethodFinder
{
    /**
     * @return MissingMethod[]
     */
    public function find(TextDocument $sourceCode): array;
}
