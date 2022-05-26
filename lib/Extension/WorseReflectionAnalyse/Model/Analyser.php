<?php

namespace Phpactor\Extension\WorseReflectionAnalyse\Model;

use Generator;
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
        $filesystem = $this->filesystem->get('git');
        $cwd = (string)getcwd();
        $absPath = Path::makeAbsolute($path, $cwd);
        if (file_exists($absPath) && is_file($absPath)) {
            yield $path => $this->reflector->diagnostics((string)file_get_contents($absPath));
            return;
        }

        foreach ($filesystem->fileList()->phpFiles()->within(FilePath::fromString($absPath)) as $file) {
            yield Path::makeRelative(
                $file->path(),
                $cwd
            ) => $this->reflector->diagnostics((string)file_get_contents($file->path()));
        }
    }
}
