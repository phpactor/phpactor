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
use DTL\ClassFileConverter\Domain\ClassToFileFileToClass;

final class FileInfoAtOffset
{
    /**
     * @var TypeInference
     */
    private $inference;

    /**
     * @var ClassToFileFileToClass
     */
    private $classToFileConverter;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        TypeInferer $inference,
        ClassToFileFileToClass $classToFileConverter,
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

    public function infoForOffset(string $sourcePath, int $offset, $showFrame = false): array
    {
        $path = $this->filesystem->createPath($sourcePath);
        $result = $this->inference->inferTypeAtOffset(
            SourceCode::fromString(
                $this->filesystem->getContents($path)
            ),
            Offset::fromInt($offset)
        );

        $return = [
            'type' => (string) $result->type(),
            'path' => null,
            'messages' => $result->log()->messages()
        ];

        if ($showFrame) {
            $return['frame'] = $result->frame()->asDebugMap();
        }

        if (InferredType::unknown() == $result->type()) {
            return $return;
        }

        $fileCandidates = $this->classToFileConverter->classToFileCandidates(ClassName::fromString((string) $result->type()));
        foreach ($fileCandidates as $candidate) {
            if (file_exists((string) $candidate)) {
                $return['path'] = (string) $candidate;
            }
        }

        return $return;
    }
}
