<?php

namespace Phpactor\Filesystem\Adapter\Composer;

use Phpactor\Filesystem\Adapter\Simple\SimpleFilesystem;
use Composer\Autoload\ClassLoader;
use Phpactor\Filesystem\Domain\FilePath;

class ComposerFilesystem extends SimpleFilesystem
{
    public function __construct($path, ClassLoader $classLoader)
    {
        $path = FilePath::fromUnknown($path);
        parent::__construct($path, new ComposerFileListProvider($path, $classLoader));
    }
}
