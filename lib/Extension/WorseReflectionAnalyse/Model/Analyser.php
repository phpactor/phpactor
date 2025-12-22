<?php

namespace Phpactor\Extension\WorseReflectionAnalyse\Model;

use Generator;
use Phpactor\Filesystem\Domain\FileList;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use RuntimeException;
use Symfony\Component\Filesystem\Path;
use Throwable;
use function Amp\Promise\wait;

class Analyser
{
    public function __construct(
        private FilesystemRegistry $filesystem,
        private SourceCodeReflector $reflector
    ) {
    }

    /**
     * @return Generator<string,Diagnostics<Diagnostic>>
     */
    public function analyse(string $path): Generator
    {
        $cwd = (string)getcwd();
        $absPath = Path::makeAbsolute($path, $cwd);
        if (file_exists($absPath) && is_file($absPath)) {
            yield $path => wait($this->reflector->diagnostics(TextDocumentBuilder::fromUri($absPath)->build()));
            return;
        }

        foreach ($this->fileList($absPath) as $file) {
            try {
                yield Path::makeRelative(
                    $file->path(),
                    $cwd
                ) => wait($this->reflector->diagnostics(TextDocumentBuilder::fromUri($file->path())->build()));
            } catch (Throwable $error) {
                throw new RuntimeException(sprintf(
                    'Error while analysing file "%s": %s',
                    $file,
                    $error->getMessage()
                ), 0, $error);
            }
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
