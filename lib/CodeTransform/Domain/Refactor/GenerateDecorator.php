<?php

namespace Phpactor\CodeTransform\Domain\Refactor;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextEdits;

interface GenerateDecorator
{
    public function getTextEdits(SourceCode $source, string $interface): TextEdits;
}
