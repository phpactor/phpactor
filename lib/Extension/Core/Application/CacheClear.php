<?php

namespace Phpactor\Extension\Core\Application;

use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

class CacheClear
{
    /**
     * @var string
     */
    private $cachePath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(string $cachePath)
    {
        $this->cachePath = Path::canonicalize($cachePath);
        $this->filesystem = new Filesystem();
    }

    public function clearCache()
    {
        $this->filesystem->remove($this->cachePath);
    }

    public function cachePath()
    {
        return $this->cachePath;
    }
}
