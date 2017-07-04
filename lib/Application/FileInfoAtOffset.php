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
     * @var TypeInferer
     */
    private $inference;

    /**
     * @var ClassToFileFileToClass
     */
    private $classToFileConverter;

    /**
     * @var Helper\FilesystemHelper
     */
    private $filesystemHelper;

    public function __construct(
        TypeInferer $inference,
        ClassToFileFileToClass $classToFileConverter
    )
    {
        $this->inference = $inference;
        $this->classToFileConverter = $classToFileConverter;
        $this->filesystemHelper = new Helper\FilesystemHelper();
    }

    public function infoForOffset(string $sourcePath, int $offset, $showFrame = false): array
    {
        $result = $this->inference->inferTypeAtOffset(
            SourceCode::fromString(
                $this->filesystemHelper->contentsFromFileOrStdin($sourcePath)
            ),
            Offset::fromInt($offset)
        );

        $return = [
            'type' => (string) $result->type(),
            'offset' => $offset,
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
