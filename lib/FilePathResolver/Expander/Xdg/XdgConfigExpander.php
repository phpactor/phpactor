<?php

namespace Phpactor\FilePathResolver\Expander\Xdg;

class XdgConfigExpander extends AbstractXdgExpander
{
    public function replacementValue(): string
    {
        return $this->xdg->getHomeConfigDir();
    }
}
