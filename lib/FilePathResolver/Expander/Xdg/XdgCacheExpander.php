<?php

namespace Phpactor\FilePathResolver\Expander\Xdg;

class XdgCacheExpander extends AbstractXdgExpander
{
    public function replacementValue(): string
    {
        return $this->xdg->getHomeCacheDir();
    }
}
