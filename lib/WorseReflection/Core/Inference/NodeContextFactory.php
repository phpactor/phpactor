<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;
use RuntimeException;

class NodeContextFactory
{
    /**
     * @param array{
     *     container_type?: Type|null,
     *     type?: Type|null,
     *     types?: Types|null,
     *     symbol_type?: Symbol::*|null,
     *     name?: Name|null,
     *     value?: mixed|null,
     * } $config
     */
    public static function create(string $symbolName, int $start, int $end, array $config = []): NodeContext
    {
        $defaultConfig = [
            'symbol_type' => Symbol::UNKNOWN,
            'container_type' => null,
            'type' => null,
            'types' => null,
            'value' => null,
        ];

        if ($diff = array_diff(array_keys($config), array_keys($defaultConfig))) {
            throw new RuntimeException(sprintf(
                'Invalid keys "%s", valid keys "%s"',
                implode('", "', $diff),
                implode('", "', array_keys($defaultConfig))
            ));
        }

        $config = array_merge($defaultConfig, $config);
        $position = Position::fromStartAndEnd($start, $end);
        $symbol = Symbol::fromTypeNameAndPosition(
            /** @phpstan-ignore-next-line */
            $config['symbol_type'],
            $symbolName,
            $position
        );

        if ($config['type'] && !$config['types']) {
            $config['types'] = Types::fromTypes([$config['type']]);
        }

        return self::contextFromParameters(
            $symbol,
            $config['types'],
            $config['container_type'],
            $config['value'],
        );
    }

    public static function forVariableAt(Frame $frame, int $start, int $end, string $name): NodeContext
    {
        $varName = ltrim($name, '$');
        $variables = $frame->locals()->lessThanOrEqualTo($end)->byName($varName);

        if (0 === $variables->count()) {
            return NodeContextFactory::create(
                $name,
                $start,
                $end,
                [
                    'symbol_type' => Symbol::VARIABLE
                ]
            )->withIssue(sprintf('Variable "%s" is undefined', $varName));
        }

        $variable = $variables->last();
        return NodeContextFactory::create(
            $name,
            $start,
            $end,
            [
                'type' => $variable->type(),
                'symbol_type' => Symbol::VARIABLE,
                'value' => $variable->value(),
            ]
        )->withIssue(sprintf('Variable "%s" is undefined', $varName));
    }

    /**
     * @param mixed $value
     */
    private static function contextFromParameters(
        Symbol $symbol,
        Types $types = null,
        Type $containerType = null,
        $value = null
    ): NodeContext {
        $context = NodeContext::for($symbol);

        if ($types) {
            $context = $context->withTypes($types);
        }

        if ($containerType) {
            $context = $context->withContainerType($containerType);
        }

        if (null !== $value) {
            $context = $context->withValue($value);
        }

        return $context;
    }
}
