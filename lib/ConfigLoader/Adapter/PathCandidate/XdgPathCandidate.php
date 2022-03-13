<?php

namespace Phpactor\ConfigLoader\Adapter\PathCandidate;

use Phpactor\ConfigLoader\Core\PathCandidate;
use Webmozart\PathUtil\Path;
use XdgBaseDir\Xdg;

class XdgPathCandidate implements PathCandidate
{
    private Xdg $xdg;

    private string $filename;

    private string $loader;

    private string $appName;

    public function __construct(string $appName, string $filename, string $loader, Xdg $xdg = null)
    {
        $this->xdg = $xdg ?: new Xdg();
        $this->filename = $filename;
        $this->loader = $loader;
        $this->appName = $appName;
    }

    public function path(): string
    {
        return Path::join([$this->xdg->getHomeConfigDir(), $this->appName, $this->filename]);
    }

    public function loader(): string
    {
        return $this->loader;
    }
}
