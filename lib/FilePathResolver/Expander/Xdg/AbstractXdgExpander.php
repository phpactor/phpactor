<?php

namespace Phpactor\FilePathResolver\Expander\Xdg;

use Phpactor\FilePathResolver\Expander;
use XdgBaseDir\Xdg;

abstract class AbstractXdgExpander implements Expander
{
    /**
     * @var Xdg
     */
    protected $xdg;

    /**
     * @var string
     */
    private $name;

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
