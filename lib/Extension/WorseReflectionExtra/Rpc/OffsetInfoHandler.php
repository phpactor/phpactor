<?php

namespace Phpactor\Extension\WorseReflectionExtra\Rpc;

use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\Extension\Rpc\Response\InformationResponse;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\TypeUtil;

class OffsetInfoHandler implements Handler
{
    const NAME = 'offset_info';

    public function __construct(private Reflector $reflector)
    {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setRequired([
            'offset',
            'source',
        ]);
    }

    public function handle(array $arguments)
    {
        $offset = $this->reflector->reflectOffset(
            SourceCode::fromString($arguments['source']),
            Offset::fromInt($arguments['offset'])
        );

        return InformationResponse::fromString(json_encode(
            $this->serialize(
                $arguments['offset'],
                $offset
            ),
            JSON_PRETTY_PRINT
        ));
    }

    private function serialize(int $offset, ReflectionOffset $reflectionOffset)
    {
        $nodeContext = $reflectionOffset->nodeContext();

        $return = [
            'symbol' => $nodeContext->symbol()->name(),
            'symbol_type' => $nodeContext->symbol()->symbolType(),
            'start' => $nodeContext->symbol()->position()->start()->toInt(),
            'end' => $nodeContext->symbol()->position()->endAsInt(),
            'type' => (string) $nodeContext->type(),
            'container_type' => (string) $nodeContext->containerType(),
            'value' => var_export(TypeUtil::valueOrNull($nodeContext->type()), true),
            'offset' => $offset,
            'type_path' => null,
        ];

        $frame = [];

        foreach (['locals', 'properties'] as $assignmentType) {
            $assignments = $reflectionOffset->frame()->$assignmentType();
            foreach ($assignments as $local) {
                $info = sprintf(
                    '%s = (%s) %s',
                    $local->name(),
                    $local->type(),
                    str_replace(PHP_EOL, '', var_export(TypeUtil::valueOrNull($local->type()), true))
                );

                $frame[$assignmentType][$local->offset()] = $info;
            }
        }
        $return['frame'] = $frame;

        if (false === ($nodeContext->type()->isDefined())) {
            return $return;
        }

        return $return;
    }
}
