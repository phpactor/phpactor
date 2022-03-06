<?php

namespace Phpactor\FilePathResolver\Expander\Xdg;

class XdgDataExpander extends AbstractXdgExpander
{
    public function replacementValue(): string
    {
        return $this->xdg->getHomeDataDir();
    }
}
