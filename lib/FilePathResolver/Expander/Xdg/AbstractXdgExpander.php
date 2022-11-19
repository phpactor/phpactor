<?php

namespace Phpactor\FilePathResolver\Expander\Xdg;

use Phpactor\FilePathResolver\Expander;
use XdgBaseDir\Xdg;

abstract class AbstractXdgExpander implements Expander
{
    protected Xdg $xdg;

    public function __construct(private string $name, Xdg $xdg = null)
    {
        $this->xdg = $xdg ?: new Xdg();
    }

    public function tokenName(): string
    {
        return $this->name;
    }
}
