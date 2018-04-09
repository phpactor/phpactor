<?php

namespace Phpactor\Extension\DependencyFinder;

use Phpactor\Container\Extension;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\NameImports;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\ClassFileConverter\Domain\FilePath as ClassToFileFilePath;
use Phpactor\Phpactor;

class DependencyFinder
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var FileToClass
     */
    private $fileToClass;

    public function __construct(Filesystem $filesystem, Reflector $reflector, FileToClass $fileToClass)
    {
        $this->filesystem = $filesystem;
        $this->reflector = $reflector;
        $this->fileToClass = $fileToClass;
    }

    public function detect(string $path)
    {
        $path = Phpactor::normalizePath($path);
        $baseName = $this->getBaseName($path);
        $fileList = $this->filesystem->fileList()->phpFiles()->within(FilePath::fromString($path));
        $foreignClasses = [];
        $cleanFiles = [];

        /** @var FilePath $file */
        foreach ($fileList as $file) {

            $source = file_get_contents($file->path());
            /** @var ReflectionClassCollection<ReflectionClass> $classes */
            $classes = $this->reflector->reflectClassesIn($source);

            foreach ($classes as $class) {

                /** @var NameImports<Name> $nameImports */
                $nameImports = $class->scope()->nameImports();
                $namespace = $class->name()->namespace();

                foreach ($nameImports as $nameImport) {
                    $name = $nameImport->full();

                    if (0 === strpos($name, $baseName)) {
                        continue;
                    }

                    if (!isset($foreignClasses[$file->path()])) {
                        $foreignClasses[$file->path()] = [];
                    }

                    $foreignClasses[$file->path()][] = $name;
                }

            }

            if (empty($foreignClasses[$file->path()])) {
                $cleanFiles[] = $file;
            }
        }

        asort($foreignClasses);
        asort($cleanFiles);

        return [
            array_map(function ($dependencies) {
                asort($dependencies);
                return array_unique($dependencies);
            }, $foreignClasses),
            $cleanFiles
        ];
    }

    private function getBaseName(string $path)
    {
        $candidate = $this->fileToClass->fileToClassCandidates(ClassToFileFilePath::fromString($path));
        $bestClassName = $candidate->best();
        return $bestClassName->__toString();
    }
}
