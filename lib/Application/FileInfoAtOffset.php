<?php

namespace Phpactor\Application;

use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Type;

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
    ) {
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

        $symbolInformation = $result->symbolInformation();
        $return = [
            'type' => (string) $symbolInformation->type(),
            'value' => var_export($symbolInformation->value(), true),
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

        if (Type::unknown() === $symbolInformation->type()) {
            return $return;
        }

        $fileCandidates = $this->classToFileConverter->classToFileCandidates(ClassName::fromString((string) $symbolInformation->type()));
        foreach ($fileCandidates as $candidate) {
            if (file_exists((string) $candidate)) {
                $return['path'] = (string) $candidate;
            }
        }

        return $return;
    }
}
