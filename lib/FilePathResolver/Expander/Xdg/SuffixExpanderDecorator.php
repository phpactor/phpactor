<?php

namespace Phpactor\FilePathResolver\Expander\Xdg;

use Phpactor\FilePathResolver\Expander;

class SuffixExpanderDecorator implements Expander
{
    /**
     * @var Expander
     */
    private $innerExpander;

    /**
     * @var string
     */
    private $suffix;

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
