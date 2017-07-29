<?php

namespace Phpactor\Application;

use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\ClassToFile;
use Phpactor\TypeInference\Domain\InferredType;
use Phpactor\TypeInference\Domain\TypeInferer;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\SourceCode;
use Phpactor\WorseReflection\Offset;

final class FileInfoAtOffset
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var ClassToFileFileToClass
     */
    private $classToFileConverter;

    /**
     * @var Helper\FilesystemHelper
     */
    private $filesystemHelper;

    public function __construct(
        Reflector $reflector,
        ClassToFileFileToClass $classToFileConverter
    )
    {
        $this->reflector = $reflector;
        $this->classToFileConverter = $classToFileConverter;
        $this->filesystemHelper = new Helper\FilesystemHelper();
    }

    public function infoForOffset(string $sourcePath, int $offset, $showFrame = false): array
    {
        $result = $this->reflector->reflectOffset(
            SourceCode::fromString(
                $this->filesystemHelper->contentsFromFileOrStdin($sourcePath)
            ),
            Offset::fromInt($offset)
        );

        $return = [
            'type' => (string) $result->value()->type(),
            'value' => (string) $result->value()->value(),
            'offset' => $offset,
            'path' => null,
        ];

        if ($showFrame) {
            $frame = [];

            foreach (['locals', 'properties'] as $assignmentType) {
                foreach ($result->frame()->$assignmentType() as $local) {
                    $info = sprintf(
                        '%s = (%s) %s', 
                        $local->name(),
                        $local->value()->type(),
                        str_replace(PHP_EOL, '', var_export($local->value()->value(), true))
                    );

                    $frame[$assignmentType][$local->offset()->toInt()] = $info;
                }
            }
            $return['frame'] = $frame;
        }

        if (InferredType::unknown() == $result->value()->type()) {
            return $return;
        }

        $fileCandidates = $this->classToFileConverter->classToFileCandidates(ClassName::fromString((string) $result->value()->type()));
        foreach ($fileCandidates as $candidate) {
            if (file_exists((string) $candidate)) {
                $return['path'] = (string) $candidate;
            }
        }

        return $return;
    }
}
