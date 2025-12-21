<?php

namespace Phpactor\Extension\ClassToFileExtra\Application;

use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\FileToClass;

class FileInfo
{
    public function __construct(
        private FileToClass $classToFileConverter,
        private Filesystem $filesystem
    ) {
    }

    public function infoForFile(string $sourcePath)
    {
        $path = $this->filesystem->createPath($sourcePath);
        $classCandidates = $this->classToFileConverter->fileToClassCandidates(FilePath::fromString((string) $path));
        $return = [
            'class' => null,
            'class_name' => null,
            'class_namespace' => null,
        ];

        if ($classCandidates->noneFound()) {
            return $return;
        }

        $best = $classCandidates->best();

        return [
            'class' => (string) $best,
            'class_name' => $best->name(),
            'class_namespace' => $best->namespace(),
        ];
    }
}
