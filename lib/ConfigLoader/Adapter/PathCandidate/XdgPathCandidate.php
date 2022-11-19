<?php

namespace Phpactor\ConfigLoader\Adapter\PathCandidate;

use Phpactor\ConfigLoader\Core\PathCandidate;
use Symfony\Component\Filesystem\Path;
use XdgBaseDir\Xdg;

class XdgPathCandidate implements PathCandidate
{
    public function __construct(
        private string $appName,
        private string $filename,
        private string $loader,
        private Xdg $xdg
    ) {
    }

    public function path(): string
    {
        return Path::join($this->xdg->getHomeConfigDir(), $this->appName, $this->filename);
    }

    public function loader(): string
    {
        return $this->loader;
    }
}
