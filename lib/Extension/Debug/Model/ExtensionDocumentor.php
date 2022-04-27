<?php

namespace Phpactor\Extension\Debug\Model;

use Phpactor\MapResolver\Definition;
use Phpactor\MapResolver\Resolver;
use RuntimeException;

class ExtensionDocumentor
{
    /**
     * @var array<string>
     */
    private array $extensionFqns;

    /**
     * @param array<string> $extensionFqns
     */
    public function __construct(array $extensionFqns)
    {
        $this->extensionFqns = $extensionFqns;
    }

    /**
     * Return an RST document documenting all the extensions
     */
    public function document(): string
    {
        $docs = [
            'Configuration',
            '=============',
            "\n",
            '.. This document is generated via. the `documentation:configuration-reference` command',
            "\n",
            '.. contents::',
            '   :depth: 2',
            '   :backlinks: none',
            '   :local:',
            "\n",
        ];
        foreach ($this->extensionFqns as $extensionFqn) {
            $documentation = $this->documentExtension($extensionFqn);
            if (null === $documentation) {
                continue;
            }
            $docs[] = $documentation;
        }
        return implode("\n", $docs);
    }

    private function documentExtension(string $extensionClass): ?string
    {
        $parts = explode('\\', $extensionClass);
        $documentedName = end($parts);

        /** @phpstan-ignore-next-line */
        if (false === $documentedName) {
            throw new RuntimeException(sprintf(
                'Invalid extension class name "%s"',
                $extensionClass
            ));
        }
        $help = [
            '.. _' . $documentedName . ':',
            "\n",
            $documentedName,
            str_repeat('-', mb_strlen($documentedName)),
            "\n",
        ];

        $extension = new $extensionClass;

        $resolver = new Resolver();
        $extension->configure($resolver);

        $hasDefinitions = false;
        foreach ($resolver->definitions() as $definition) {
            assert($definition instanceof Definition);
            if (null === $definition->description()) {
            }
            $hasDefinitions = true;
            $help[] = '.. _param_' . $definition->name(). ':';
            $help[] = "\n";
            $help[] = '``' . $definition->name() . '``';
            $help[] = str_repeat('"', mb_strlen($definition->name()) + 4);
            $help[] = "\n";
            if ($definition->types()) {
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
        }

        if (!$hasDefinitions) {
            return null;
        }

        return implode("\n", $help);
    }
}
