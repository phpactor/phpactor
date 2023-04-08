<?php

namespace Phpactor\CodeTransform\Domain\Helper;

use Amp\Promise;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder\MissingMethod;
use Phpactor\TextDocument\TextDocument;

interface MissingMethodFinder
{
    /**
     * @return Promise<MissingMethod[]>
     */
    public function find(TextDocument $sourceCode): Promise;
}
