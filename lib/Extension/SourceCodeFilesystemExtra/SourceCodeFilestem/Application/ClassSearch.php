<?php

namespace Phpactor\Extension\SourceCodeFilesystemExtra\SourceCodeFilestem\Application;

use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use SplFileInfo;

class ClassSearch
{
    public function __construct(
        private readonly FilesystemRegistry $filesystemRegistry,
        private readonly FileToClass $fileToClass,
        private readonly Reflector $reflector
    ) {
    }

    public function classSearch(string $filesystemName, string $name)
    {
        $name = $this->convertFqnToRelativePath($name);
        $filesystem = $this->filesystemRegistry->get($filesystemName);

        /** @var FileList<SplFileInfo> $files */
        $files = $filesystem->fileList('{' . $name . '}')->named($name . '.php');

        $results = [];
        $results = $this->builtInResults($results, $name);

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

    private function tryAndReflect(string $name)
    {
        try {
            $reflectionClass = $this->reflector->reflectClassLike($name);
        } catch (NotFound) {
            return;
        }

        return [
            'file_path' => (string) $reflectionClass->sourceCode()->uri()?->path(),
            'class' => (string) $reflectionClass->name(),
            'class_name' => $reflectionClass->name()->short(),
            'class_namespace' => (string) $reflectionClass->name()->namespace(),
        ];
    }

    private function builtInResults(array $results, string $name)
    {
        $declared = array_merge(
            get_declared_classes(),
            get_declared_traits(),
            get_declared_interfaces()
        );

        foreach ($declared as $declaredClass) {
            $short = $this->resolveShortName($declaredClass);

            $namespace = substr($declaredClass, 0, intval(strrpos($declaredClass, '\\')));

            if ($name !== $short) {
                continue;
            }

            if (!$this->tryAndReflect($name)) {
                continue;
            }

            $results[] = $this->tryAndReflect($name);
        }

        return $results;
    }

    private function resolveShortName($declaredClass): string
    {
        $offset = strrpos($declaredClass, '\\');

        if (false === $offset) {
            return $declaredClass;
        }

        return substr($declaredClass, $offset + 1);
    }

    private function convertFqnToRelativePath(string $name)
    {
        return str_replace('\\', '/', $name);
    }
}
