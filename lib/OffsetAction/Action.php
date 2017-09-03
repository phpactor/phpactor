<?php

namespace Phpactor\OffsetAction;

use Phpactor\WorseReflection\Core\Reflection\Inference\SymbolInformation;

interface Action
{
    public function perform(SymbolInformation $symbolInformation): Result;
}
