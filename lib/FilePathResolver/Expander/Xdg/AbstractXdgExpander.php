<?php

namespace Phpactor\FilePathResolver\Expander\Xdg;

use Phpactor\FilePathResolver\Expander;
use XdgBaseDir\Xdg;

abstract class AbstractXdgExpander implements Expander
{
    protected Xdg $xdg;

    private string $name;

    public function __construct(string $name, Xdg $xdg = null)
    {
        $this->xdg = $xdg ?: new Xdg();
        $this->name = $name;
    }

    public function tokenName(): string
    {
        return $this->name;
    }
}
