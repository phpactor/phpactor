<?php

namespace Phpactor\Application\TypeFromOffset;

use DTL\ClassFileConverter\CompositeTransformer;
use DTL\TypeInference\TypeInference;
use DTL\TypeInference\Domain\Offset;
use DTL\TypeInference\Domain\SourceCode;
use DTL\Filesystem\Domain\Filesystem;

final class TypeFromOffset
{
    private $inference;
    private $classToFileConverter;
    private $filesystem;

    public function __construct(
        TypeInference $inference,
        CompositeTransformer $classToFileConverter,
        Filesystem $filesystem
    )
    {
        $this->inference = $inference;
        $this->classToFileConverter = $classToFileConverter;
        $this->filesystem = $filesystem;
    }

    public function inferTypeFromOffset(string $sourcePath, int $offset)
    {
        $path = $this->filesystem->createPath($sourcePath);
        $type = $this->inference->inferTypeFromOffset(
            $this->filesystem->getContents($path),
            $offset
        );

        return (string) $type;
    }
}
