<?php

namespace Phpactor\FilePathResolver\Expander;

use Phpactor\FilePathResolver\Expander;

class ValueExpander implements Expander
{
    /**
     * @var string
     */
    private $tokenName;

    /**
     * @var string
     */
    private $value;

    public function __construct(string $tokenName, string $value)
    {
        $this->tokenName = $tokenName;
        $this->value = $value;
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
