<?php

namespace Phpactor\Application;

use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\WorseReflection\Reflector;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Phpactor;

class Linter
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var FilesystemRegistry
     */
    private $filesystem;

    public function __construct(Reflector $reflector, FilesystemRegistry $filesystem)
    {
        $this->reflector = $reflector;
        $this->filesystem = $filesystem;
    }

    public function lint(string $path, string $filesystem = 'git')
    {
        $path = Phpactor::normalizePath($path);
        $problems = [];
        $filesystem = $this->filesystem->get($filesystem);

        $files = $filesystem->fileList()->within(FilePath::fromString($path));

        foreach ($files as $file) {
            $problems[$file->path()] = $this->reflector->lint(file_get_contents($file->path()));
        }

        return $problems;
    }
}
