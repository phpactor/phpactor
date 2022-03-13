<?php

namespace Phpactor\FilePathResolver\Expander\Xdg;

use Phpactor\FilePathResolver\Expander;

class SuffixExpanderDecorator implements Expander
{
    private Expander $innerExpander;

    private string $suffix;

    public function __construct(Expander $innerExpander, string $suffix)
    {
        $this->innerExpander = $innerExpander;
        $this->suffix = $suffix;
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
