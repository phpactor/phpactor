<?php

namespace Phpactor\Extension\WorseReflectionAnalyse\Model;

use Generator;
use Phpactor\Filesystem\Domain\FileList;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Webmozart\PathUtil\Path;

class Analyser
{
    private SourceCodeReflector $reflector;

    private FilesystemRegistry $filesystem;

    public function __construct(FilesystemRegistry $filesystem, SourceCodeReflector $reflector)
    {
        $this->reflector = $reflector;
        $this->filesystem = $filesystem;
    }

    /**
     * @return Generator<string,Diagnostics>
     */
    public function analyse(string $path): Generator
    {
        $cwd = (string)getcwd();
        $absPath = Path::makeAbsolute($path, $cwd);
        if (file_exists($absPath) && is_file($absPath)) {
            yield $path => $this->reflector->diagnostics((string)file_get_contents($absPath));
            return;
        }

        foreach ($this->fileList($absPath) as $file) {
            yield Path::makeRelative(
                $file->path(),
                $cwd
            ) => $this->reflector->diagnostics((string)file_get_contents($file->path()));
        }
    }

    public function fileList(string $path): FileList
    {
        $cwd = (string)getcwd();
        $absPath = Path::makeAbsolute($path, $cwd);

        $filesystem = $this->filesystem->get('git');
        return $filesystem->fileList()->phpFiles()->within(FilePath::fromString($absPath));
    }
}
