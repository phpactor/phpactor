<?php

namespace Phpactor\Application\Helper;

use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\Phpactor;

class ClassFileNormalizer
{
    /**
     * @var ClassToFileFileToClass
     */
    private $fileClassConverter;

    public function __construct(
        ClassToFileFileToClass $fileClassConverter
    ) {
        $this->fileClassConverter = $fileClassConverter;
    }

    public function normalizeToFile(string $classOrFile): string
    {
        if (false === Phpactor::isFile($classOrFile)) {
            return (string) $this->classToFile($classOrFile);
        }

        return Phpactor::normalizePath($classOrFile);
    }

    public function normalizeToClass(string $classOrFile): string
    {
        if (true === $resp = Phpactor::isFile($classOrFile)) {
            return (string) $this->fileToClass(Phpactor::normalizePath($classOrFile));
        }

        return $classOrFile;
    }

    public function classToFile(string $class): string
    {
        $filePathCandidates = $this->fileClassConverter->classToFileCandidates(
            ClassName::fromString($class)
        );

        return (string) $filePathCandidates->best();
    }

    public function fileToClass(string $file): string
    {
        $classCandidates = $this->fileClassConverter->fileToClassCandidates(
            FilePath::fromString($file)
        );

        return (string) $classCandidates->best();
    }
}
