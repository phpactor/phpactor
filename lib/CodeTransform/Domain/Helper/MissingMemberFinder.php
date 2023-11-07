<?php

namespace Phpactor\CodeTransform\Domain\Helper;

use Amp\Promise;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder\MissingMember;
use Phpactor\TextDocument\TextDocument;

interface MissingMemberFinder
{
    /**
     * @return Promise<MissingMember[]>
     */
    public function find(TextDocument $sourceCode): Promise;
}
