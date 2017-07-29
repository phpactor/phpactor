<?php

namespace Phpactor\Application;

use Phpactor\TypeReflector\TypeReflector;
use Phpactor\TypeReflector\Domain\Offset;
use Phpactor\TypeReflector\Domain\SourceCode;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\ClassToFile;
use Phpactor\TypeReflector\Domain\InferredType;
use Phpactor\TypeReflector\Domain\TypeInferer;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\WorseReflection\Reflector;

final class FileInfo
{
    /**
     * @var FileToClass
     */
    private $classToFileConverter;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        FileToClass $classToFileConverter,
        Filesystem $filesystem
    )
    {
        $this->classToFileConverter = $classToFileConverter;
        $this->filesystem = $filesystem;
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
