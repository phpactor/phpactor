<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\Rpc\Editor\InformationAction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Type;

class OffsetInfoHandler implements Handler
{
    /**
     * @var Reflector
     */
    private $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function name(): string
    {
        return 'offset_info';
    }

    public function defaultParameters(): array
    {
        return [
            'offset' => null,
            'source' => null,
        ];
    }

    public function handle(array $arguments)
    {
        $offset = $this->reflector->reflectOffset(
            SourceCode::fromString($arguments['source']),
            Offset::fromInt($arguments['offset'])
        );

        return InformationAction::fromString(json_encode(
            $this->serialize(
                $arguments['offset'],
                $offset
            ),
            JSON_PRETTY_PRINT
        ));
    }

    private function serialize(int $offset, ReflectionOffset $reflectionOffset)
    {
        $symbolInformation = $reflectionOffset->symbolInformation();

        $return = [
            'symbol' => $symbolInformation->symbol()->name(),
            'symbol_type' => $symbolInformation->symbol()->symbolType(),
            'start' => $symbolInformation->symbol()->position()->start(),
            'end' => $symbolInformation->symbol()->position()->end(),
            'type' => (string) $symbolInformation->type(),
            'class_type' => (string) $symbolInformation->containerType(),
            'value' => var_export($symbolInformation->value(), true),
            'offset' => $offset,
            'type_path' => null,
        ];

        $frame = [];

        foreach (['locals', 'properties'] as $assignmentType) {
            foreach ($reflectionOffset->frame()->$assignmentType() as $local) {
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

        if (Type::unknown() === $symbolInformation->type()) {
            return $return;
        }

        return $return;
    }
}
