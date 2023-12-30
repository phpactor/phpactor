<?php

namespace Phpactor\Extension\Core\Application\Helper;

use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\Phpactor;

class ClassFileNormalizer
{
    public function __construct(private ClassToFileFileToClass $fileClassConverter)
    {
    }

    public function normalizeToFile(string $classOrFile): string
    {
        if (false === Phpactor::isFile($classOrFile)) {
            return (string) $this->classToFile($classOrFile);
        }

        return $classOrFile;
    }

    public function normalizeToClass(string $classOrFile): string
    {
        if (true === $resp = Phpactor::isFile($classOrFile)) {
            return (string) $this->fileToClass(Phpactor::normalizePath($classOrFile));
        }

        return $classOrFile;
    }

    /**
     * @return string
     */
    public function classToFile(string $class, bool $hasToExist = false)
    {
        $filePathCandidates = $this->fileClassConverter->classToFileCandidates(
            ClassName::fromString($class)
        );

        if ($hasToExist) {
            foreach ($filePathCandidates as $candidate) {
                if (file_exists((string) $candidate)) {
                    return (string) $candidate;
                }
            }

            return null;
        }

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
