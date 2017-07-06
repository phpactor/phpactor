<?php

namespace Phpactor\Application;

use Phpactor\TypeInference\TypeInference;
use Phpactor\TypeInference\Domain\Offset;
use Phpactor\TypeInference\Domain\SourceCode;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\ClassToFile;
use Phpactor\TypeInference\Domain\InferredType;
use Phpactor\TypeInference\Domain\TypeInferer;
use Phpactor\ClassFileConverter\Domain\FileToClass;

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
