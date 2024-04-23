<?php

namespace Phpactor\Extension\Debug\Model;

use Phpactor\Container\Extension;
use Phpactor\Container\OptionalExtension;
use Phpactor\MapResolver\Resolver;
use RuntimeException;

class ExtensionDocumentor implements Documentor
{
    /**
     * @param array<string> $extensionFqns
     */
    public function __construct(private array $extensionFqns, private DefinitionDocumentor $definitionDocumentor)
    {
    }

    public function document(string $commandName=''): string
    {
        $docs = [
            '.. _ref_configuration:',
            '',
            'Configuration',
            '=============',
            "\n",
            ".. This document is generated via the `$commandName` command",
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

        $extension = new $extensionClass();

        if (!$extension instanceof Extension) {
            throw new RuntimeException(sprintf(
                'Expected "%s" to be an instanceof Phpactor\Container\Extension',
                get_class($extension)
            ));
        }

        $resolver = new Resolver();
        if ($extension instanceof OptionalExtension) {
            (function (string $key) use ($resolver): void {
                $resolver->setDefaults([$key => false]);
                $resolver->setTypes([$key => 'boolean']);
                $resolver->setDescriptions([$key => 'Enable or disable this extension']);
            })(sprintf('%s.enabled', $extension->name()));
        }
        $extension->configure($resolver);

        $hasDefinitions = false;
        foreach ($resolver->definitions() as $definition) {
            $hasDefinitions = true;
            $help[] = $this->definitionDocumentor->document('param', $definition);
        }

        if (!$hasDefinitions) {
            return null;
        }

        return implode("\n", $help);
    }
}
