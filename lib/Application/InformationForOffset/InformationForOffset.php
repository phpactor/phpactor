<?php

namespace Phpactor\Application\InformationForOffset;

use DTL\ClassFileConverter\CompositeTransformer;
use DTL\TypeInference\TypeInference;
use DTL\TypeInference\Domain\Offset;
use DTL\TypeInference\Domain\SourceCode;
use DTL\Filesystem\Domain\Filesystem;
use DTL\ClassFileConverter\FilePath;
use DTL\ClassFileConverter\ClassName;
use DTL\ClassFileConverter\ClassToFile;
use DTL\TypeInference\Domain\InferredType;

final class InformationForOffset
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
