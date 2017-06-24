<?php

namespace Phpactor\Application\ClassSearch;

use DTL\Filesystem\Domain\Filesystem;
use DTL\ClassFileConverter\FileToClass;
use DTL\ClassFileConverter\FilePath;

final class ClassSearch
{
    private $fileToClass;
    private $filesystem;

    public function __construct(Filesystem $filesystem, FileToClass $fileToClass)
    {
        $this->filesystem = $filesystem;
        $this->fileToClass = $fileToClass;
    }

    public function classSearch(string $name)
    {
        $files = $this->filesystem->fileList()->named($name . '.php');

        $results = [];
        foreach ($files as $file) {
            $result = [
                'path' => (string) $file->absolutePath(),
                'name' => null,
            ];

            $candidates = $this->fileToClass->fileToClass(FilePath::fromString((string) $file->absolutePath()));

            if (false === $candidates->noneFound()) {
                $result['name'] = (string) $candidates->best();
            }

            $results[] = $result;
        }

        return $results;
    }
}
