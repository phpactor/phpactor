<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use RuntimeException;

class NodeContextFactory
{
    /**
     * @param array{
     *     container_type?: Type|null,
     *     type?: Type|null,
     *     name?: Name|null,
     * } $config
     */
    public static function create(string $symbolName, int $start, int $end, array $config = []): NodeContext
    {
        $defaultConfig = [
            'symbol_type' => Symbol::UNKNOWN,
            'container_type' => null,
            'type' => TypeFactory::unknown(),
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

        return self::contextFromParameters(
            $symbol,
            /** @phpstan-ignore-next-line */
            $config['type'],
            /** @phpstan-ignore-next-line */
            $config['container_type'],
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
            ]
        );
    }

    private static function contextFromParameters(
        Symbol $symbol,
        Type $type = null,
        Type $containerType = null
    ): NodeContext {
        $context = NodeContext::for($symbol);

        if ($type) {
            $context = $context->withType($type);
        }

        if ($containerType) {
            $context = $context->withContainerType($containerType);
        }

        return $context;
    }
}
