<?php

namespace Phpactor\CodeBuilder\Domain\TemplatePathResolver;

use FilesystemIterator;

class PhpVersionPathResolver
{
    /**
     * @var string In the form of "major.minor.release[extra]"
     * @see https://www.php.net/manual/en/reserved.constants.php#reserved.constants.core
     */
    private string $phpVersion;

    public function __construct(string $phpVersion)
    {
        $this->phpVersion = $phpVersion;
    }

    public function resolve(iterable $paths): iterable
    {
        $resolvedPaths = [];

        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }

            $phpDirectoriesIterator = new FilterPhpVersionDirectoryIterator(
                new FilesystemIterator($path),
                $this->phpVersion
            );
            $phpDirectories = array_keys(iterator_to_array($phpDirectoriesIterator));
            rsort($phpDirectories, SORT_NATURAL);

            $resolvedPaths = array_merge($resolvedPaths, $phpDirectories);
            $resolvedPaths[] = $path;
        }

        return $resolvedPaths;
    }
}
