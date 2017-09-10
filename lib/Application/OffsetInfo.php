<?php

namespace Phpactor\Application;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\Inference\Variable;

final class OffsetInfo
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var Helper\FilesystemHelper
     */
    private $filesystemHelper;

    /**
     * @var Helper\ClassFileNormalizer
     */
    private $classFileNormalizer;

    public function __construct(
        Reflector $reflector,
        Helper\ClassFileNormalizer $classFileNormalizer
    ) {
        $this->reflector = $reflector;
        $this->classFileNormalizer = $classFileNormalizer;
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
            'symbol' => $symbolInformation->symbol()->name(),
            'symbol_type' => $symbolInformation->symbol()->symbolType(),
            'start' => $symbolInformation->symbol()->position()->start(),
            'end' => $symbolInformation->symbol()->position()->end(),
            'type' => (string) $symbolInformation->type(),
            'class_type' => (string) $symbolInformation->classType(),
            'value' => var_export($symbolInformation->value(), true),
            'offset' => $offset,
            'type_path' => null,
        ];

        if ($showFrame) {
            $frame = [];

            foreach (['locals', 'properties'] as $assignmentType) {
                /** @var $local Variable */
                foreach ($result->frame()->$assignmentType() as $local) {
                    $info = sprintf(
                        '%s = (%s) %s',
                        $local->name(),
                        $local->symbolInformation()->type(),
                        str_replace(PHP_EOL, '', var_export($local->symbolInformation()->value(), true))
                    );

                    $frame[$assignmentType][$local->offset()->toInt()] = $info;
                }
            }
            $return['frame'] = $frame;
        }

        if (Type::unknown() === $symbolInformation->type()) {
            return $return;
        }

        $return['type_path'] = $symbolInformation->type()->isClass() ? $this->classFileNormalizer->classToFile((string) $symbolInformation->type(), true) : null;
        $return['class_type_path'] = $symbolInformation->classType() && false === $symbolInformation->classType()->isPrimitive() ? $this->classFileNormalizer->classToFile($return['class_type'], true) : null;

        return $return;
    }
}
