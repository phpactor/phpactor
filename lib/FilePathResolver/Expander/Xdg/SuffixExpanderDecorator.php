<?php

namespace Phpactor\FilePathResolver\Expander\Xdg;

use Phpactor\FilePathResolver\Expander;

class SuffixExpanderDecorator implements Expander
{
    public function __construct(
        private readonly Expander $innerExpander,
        private readonly string $suffix
    ) {
    }

    public function tokenName(): string
    {
        return $this->innerExpander->tokenName();
    }

    public function replacementValue(): string
    {
        return $this->innerExpander->replacementValue().$this->suffix;
    }
}
