<?php

namespace Phpactor\Application\Helper;

use DTL\ClassFileConverter\Domain\ClassName;
use DTL\ClassFileConverter\Domain\FilePath;
use DTL\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\Phpactor;

class ClassFileNormalizer
{
    private $fileToClassConverter;

    public function __construct(
        ClassToFileFileToClass $fileClassConverter
    )
    {
        $this->fileClassConverter = $fileClassConverter;
    }

    public function normalizeToFile(string $classOrFile)
    {
        if (false === Phpactor::isFile($classOrFile)) {
            return (string) $this->classToFile($classOrFile);
        }

        return Phpactor::normalizePath($classOrFile);
    }

    public function normalizeToClass(string $classOrFile)
    {
        if (true === Phpactor::isFile($classOrFile)) {
            return (string) $this->fileToClass(Phpactor::normalizePath($classOrFile));
        }

        return $classOrFile;
    }

    public function classToFile(string $class)
    {
        $filePathCandidates = $this->fileClassConverter->classToFileCandidates(
            ClassName::fromString($class)
        );

        return (string) $filePathCandidates->best();
    }

    public function fileToClass(string $file)
    {
        $classCandidates = $this->fileClassConverter->fileToClassCandidates(
            FilePath::fromString($file)
        );

        return (string) $classCandidates->best();
    }
}
