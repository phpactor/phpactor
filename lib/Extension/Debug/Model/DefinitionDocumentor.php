<?php

namespace Phpactor\Extension\Debug\Model;

use Phpactor\MapResolver\Definition;

class DefinitionDocumentor
{
    public function document(string $prefix, Definition $definition): string
    {
        $help[] = sprintf('.. _%s_%s:', $prefix, $definition->name());
        $help[] = "\n";
        $help[] = '``' . $definition->name() . '``';
        $help[] = str_repeat('"', mb_strlen($definition->name()) + 4);

        if ($definition->types()) {
            $help[] = "\n";
            $help[] = sprintf('Type: %s', implode('|', $definition->types()));
        }

        if ($definition->description()) {
            $help[] = "\n";
            $help[] = $definition->description();
        }

        $help[] = "\n";
        $help[] = sprintf(
            '**Default**: ``%s``',
            json_encode($definition->defaultValue())
        );
        $help[] = "\n";

        $enum = $definition->enum();
        if ($enum) {
            $help[] = sprintf(
                '**Allowed values**: %s',
                implode(', ', array_map(fn ($v) => json_encode($v), $enum))
            );
            $help[] = "\n";
        }

        return implode("\n", $help);
    }
}
