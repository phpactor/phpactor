<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;

interface ChangeVisiblity
{
    public function changeVisiblity(SourceCode $source, int $offset): SourceCode;
}
