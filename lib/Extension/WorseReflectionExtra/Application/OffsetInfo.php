<?php

namespace Phpactor\Extension\WorseReflectionExtra\Application;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\Extension\Core\Application\Helper\ClassFileNormalizer;
use Phpactor\Extension\Core\Application\Helper\FilesystemHelper;
use Phpactor\WorseReflection\TypeUtil;

final class OffsetInfo
{
    private FilesystemHelper $filesystemHelper;

    public function __construct(
        private Reflector $reflector,
        private ClassFileNormalizer $classFileNormalizer
    ) {
        $this->filesystemHelper = new FilesystemHelper();
    }

    /** @return array<string, mixed> */
    public function infoForOffset(string $sourcePath, int $offset, bool $showFrame = false): array
    {
        $result = $this->reflector->reflectOffset(
            SourceCode::fromString(
                $this->filesystemHelper->contentsFromFileOrStdin($sourcePath)
            ),
            Offset::fromInt($offset)
        );

        $nodeContext = $result->nodeContext();
        $return = [
            'symbol' => $nodeContext->symbol()->name(),
            'symbol_type' => $nodeContext->symbol()->symbolType(),
            'start' => $nodeContext->symbol()->position()->start(),
            'end' => $nodeContext->symbol()->position()->end(),
            'type' => (string) $nodeContext->type(),
            'class_type' => (string) $nodeContext->containerType(),
            'value' => var_export(TypeUtil::valueOrNull($nodeContext->type()), true),
            'offset' => $offset,
            'type_path' => null,
        ];

        if ($showFrame) {
            $frame = [];

            foreach (['locals', 'properties'] as $assignmentType) {
                foreach ($result->frame()->$assignmentType() as $local) {
                    $info = sprintf(
                        '%s = (%s) %s',
                        $local->name(),
                        $local->nodeContext()->type(),
                        str_replace(PHP_EOL, '', var_export($local->nodeContext()->value(), true))
                    );

                    $frame[$assignmentType][$local->offset()->toInt()] = $info;
                }
            }
            $return['frame'] = $frame;
        }

        if (false === ($nodeContext->type()->isDefined())) {
            return $return;
        }

        $return['type_path'] = $nodeContext->type()->isClass() ? $this->classFileNormalizer->classToFile((string) $nodeContext->type(), true) : null;
        $return['class_type_path'] = $nodeContext->containerType()->isDefined() && $nodeContext->containerType()->isClass() ? $this->classFileNormalizer->classToFile($return['class_type'], true) : null;

        return $return;
    }
}
