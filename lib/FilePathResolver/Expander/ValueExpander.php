<?php

namespace Phpactor\FilePathResolver\Expander;

use Phpactor\FilePathResolver\Expander;

class ValueExpander implements Expander
{
    public function __construct(
        private string $tokenName,
        private string $value
    ) {
    }

    public function tokenName(): string
    {
        return $this->tokenName;
    }

    public function replacementValue(): string
    {
        return $this->value;
    }
}
