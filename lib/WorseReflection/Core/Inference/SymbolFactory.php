<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;
use RuntimeException;

class SymbolFactory
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
    public function context(string $symbolName, int $start, int $end, array $config = []): SymbolContext
    {
        $defaultConfig = [
            'symbol_type' => Symbol::UNKNOWN,
            'container_type' => null,
            'type' => null,
            'types' => null,
            'value' => null,
            'name' => null,
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
            $config['symbol_type'],
            $symbolName,
            $position
        );

        if ($config['type'] && !$config['types']) {
            $config['types'] = Types::fromTypes([$config['type']]);
        }

        return $this->contextFromParameters(
            $symbol,
            $config['types'],
            $config['container_type'],
            $config['value'],
            $config['name']
        );
    }

    private function contextFromParameters(
        Symbol $symbol,
        Types $types = null,
        Type $containerType = null,
        $value = null,
        Name $name = null
    ): SymbolContext {
        $context = SymbolContext::for($symbol);

        if ($types) {
            $context = $context->withTypes($types);
        }

        if ($containerType) {
            $context = $context->withContainerType($containerType);
        }

        if (null !== $value) {
            $context = $context->withValue($value);
        }

        if (null !== $name) {
            $context = $context->withName($name);
        }

        return $context;
    }
}
