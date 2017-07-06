<?php

namespace Phpactor\Application;

use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\ClassFileConverter\Domain\FilePath;

final class ClassSearch
{
    /**
     * @var FileToClass
     */
    private $fileToClass;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem, FileToClass $fileToClass)
    {
        $this->filesystem = $filesystem;
        $this->fileToClass = $fileToClass;
    }

    public function classSearch(string $name)
    {
        $files = $this->filesystem->fileList('{' . $name . '}')->named($name . '.php');

        $results = [];
        foreach ($files as $file) {
            $result = [
                'file_path' => (string) $file->path(),
                'class' => null,
                'class_name' => null,
                'class_namespace' => null,
            ];

            $candidates = $this->fileToClass->fileToClassCandidates(FilePath::fromString((string) $file->path()));

            if (false === $candidates->noneFound()) {
                $result['class_name'] = (string) $candidates->best()->name();
                $result['class'] = (string) $candidates->best();
                $result['class_namespace'] = (string) $candidates->best()->namespace();
            }

            $results[] = $result;
        }

        return $results;
    }
}
