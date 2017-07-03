<?php

namespace Phpactor\Application;

use DTL\TypeInference\TypeInference;
use DTL\TypeInference\Domain\Offset;
use DTL\TypeInference\Domain\SourceCode;
use DTL\Filesystem\Domain\Filesystem;
use DTL\ClassFileConverter\Domain\FilePath;
use DTL\ClassFileConverter\Domain\ClassName;
use DTL\ClassFileConverter\Domain\ClassToFile;
use DTL\TypeInference\Domain\InferredType;
use DTL\TypeInference\Domain\TypeInferer;
use DTL\ClassFileConverter\Domain\FileToClass;

final class FileInfo
{
    /**
     * @var TypeInference
     */
    private $inference;

    /**
     * @var FileToClass
     */
    private $classToFileConverter;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        TypeInferer $inference,
        FileToClass $classToFileConverter,
        Filesystem $filesystem
    )
    {
        $this->inference = $inference;
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
