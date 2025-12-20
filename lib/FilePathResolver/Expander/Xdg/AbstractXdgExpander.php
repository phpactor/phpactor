<?php

namespace Phpactor\FilePathResolver\Expander\Xdg;

use Phpactor\FilePathResolver\Expander;
use XdgBaseDir\Xdg;

abstract class AbstractXdgExpander implements Expander
{
    public function __construct(private string $name, protected Xdg $xdg = new Xdg())
    {
    }

    public function tokenName(): string
    {
        return $this->name;
    }
}
