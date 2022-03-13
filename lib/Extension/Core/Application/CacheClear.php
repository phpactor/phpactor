<?php

namespace Phpactor\Extension\Core\Application;

use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

class CacheClear
{
    private string $cachePath;

    private Filesystem $filesystem;

    public function __construct(string $cachePath)
    {
        $this->cachePath = Path::canonicalize($cachePath);
        $this->filesystem = new Filesystem();
    }

    public function clearCache(): void
    {
        $this->filesystem->remove($this->cachePath);
    }

    public function cachePath()
    {
        return $this->cachePath;
    }
}
