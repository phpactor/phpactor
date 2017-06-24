<?php

namespace Phpactor\Application\SearchName;

use DTL\Filesystem\Domain\Filesystem;
use DTL\ClassFileConverter\FileToClass;

final class SearchName
{
    private $fileToClass;
    private $filesystem;

    public function __construct(Filesystem $filesystem, FileToClass $fileToClass)
    {
        $this->filesystem = $filesystem;
        $this->fileToClass = $fileToClass;
    }

    public function searchName(string $name)
    {
        $files = $this->filesystem->fileList()->named($name);


        $results = [];
        foreach ($files as $file) {
            $result = [
                'path' => (string) $file,
                'name' => null,
            ];

            $candidates = $this->fileToClass((string) $file);

            if (false === $candidates->hasNone()) {
                $result['name'] = (string) $candidates->best();
            }

            $results[] = $result;
        }

        return $results;
    }
}
