<?php

namespace Phpactor\Filesystem\Adapter\Composer;

use Phpactor\Filesystem\Domain\FileList;
use Phpactor\Filesystem\Domain\FilePath;
use Composer\Autoload\ClassLoader;
use Phpactor\Filesystem\Domain\FileListProvider;
use Phpactor\Filesystem\Iterator\AppendIterator;
use Webmozart\PathUtil\Path;
use SplFileInfo;
use ArrayIterator;
use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ComposerFileListProvider implements FileListProvider
{
    private $classLoader;
    private $path;

    public function __construct(FilePath $path, ClassLoader $classLoader)
    {
        $this->path = $path;
        $this->classLoader = $classLoader;
    }

    public function fileList(): FileList
    {
        $prefixes = array_merge(
            $this->classLoader->getPrefixes(),
            $this->classLoader->getPrefixesPsr4(),
            $this->classLoader->getClassMap(),
            $this->classLoader->getFallbackDirs(),
            $this->classLoader->getFallbackDirsPsr4()
        );

        $appendIterator = new AppendIterator();
        $files = [];
        $seenPaths = [];
        foreach ($prefixes as $paths) {
            $paths = (array) $paths;
            foreach ($paths as $path) {
                $path = Path::canonicalize($path);

                if (false === file_exists($path)) {
                    continue;
                }

                if (is_file($path)) {
                    if (isset($files[$path])) {
                        continue;
                    }

                    $files[$path] = new SplFileInfo($path);
                    continue;
                }

                // do not add a directory iterator if a parent directory
                // has already been iterated.
                //
                // TODO: This could be more efficient.
                foreach ($seenPaths as $seenPath) {
                    if (0 === strpos($path, $seenPath)) {
                        continue 2;
                    }
                }

                $iterator = $this->createFileIterator(
                    $this->path->makeAbsoluteFromString($path)
                );

                $appendIterator->append($iterator);

                $seenPaths[$path] = $path;
            }
        }

        if ($files) {
            $appendIterator->append(new ArrayIterator(array_values($files)));
        }

        return FileList::fromIterator($appendIterator);
    }

    private function createFileIterator(string $path): Iterator
    {
        $path = $path ? $this->path->makeAbsoluteFromString($path) : $this->path->path();
        $files = new RecursiveDirectoryIterator($path);
        $files = new RecursiveIteratorIterator($files);

        return $files;
    }
}
