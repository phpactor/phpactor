<?php

namespace Phpactor\Application\FileInfo;

use DTL\ClassFileConverter\CompositeTransformer;
use DTL\TypeInference\TypeInference;
use DTL\TypeInference\Domain\Offset;
use DTL\TypeInference\Domain\SourceCode;
use DTL\Filesystem\Domain\Filesystem;
use DTL\ClassFileConverter\FilePath;
use DTL\ClassFileConverter\ClassName;
use DTL\ClassFileConverter\ClassToFile;
use DTL\TypeInference\Domain\InferredType;

final class FileInfo
{
    private $inference;
    private $classToFileConverter;
    private $filesystem;

    public function __construct(
        TypeInference $inference,
        ClassToFile $classToFileConverter,
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
        $classCandidates = $this->classToFileConverter->fileToClass(FilePath::fromString((string) $path));

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

    public function infoForOffset(string $sourcePath, int $offset): array
    {
        $path = $this->filesystem->createPath($sourcePath);
        $type = $this->inference->inferTypeAtOffset(
            $this->filesystem->getContents($path),
            $offset
        );

        $return = [
            'type' => (string) $type,
            'path' => null,
        ];

        if (InferredType::unknown() == $type) {
            return $return;
        }

        $fileCandidates = $this->classToFileConverter->classToFileCandidates(ClassName::fromString((string) $type));

        foreach ($fileCandidates as $candidate) {
            if (file_exists((string) $candidate)) {
                $return['path'] = (string) $candidate;
            }
        }

        return $return;

    }
}
