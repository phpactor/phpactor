<?php

namespace Phpactor\Application;

use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\Filesystem\Domain\FilesystemRegistry;

final class ClassSearch
{
    /**
     * @var FileToClass
     */
    private $fileToClass;

    /**
     * @var FilesystemRegistry
     */
    private $filesystemRegistry;

    public function __construct(FilesystemRegistry $filesystemRegistry, FileToClass $fileToClass)
    {
        $this->filesystemRegistry = $filesystemRegistry;
        $this->fileToClass = $fileToClass;
    }

    public function classSearch(string $filesystemName, string $name)
    {
        $filesystem = $this->filesystemRegistry->get($filesystemName);

        $files = $filesystem->fileList('{' . $name . '}')->named($name . '.php');

        $results = [];
        foreach ($files as $file) {
            if (isset($results[(string) $file->path()])) {
                continue;
            }

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

            $results[(string) $file->path()] = $result;
        }

        return array_values($results);
    }
}
