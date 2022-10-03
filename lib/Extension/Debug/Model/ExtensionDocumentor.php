<?php

namespace Phpactor\Extension\Debug\Model;

use Phpactor\MapResolver\Resolver;
use RuntimeException;

class ExtensionDocumentor implements Documentor
{
    /**
     * @var array<string>
     */
    private array $extensionFqns;

    private DefinitionDocumentor $definitionDocumentor;

    /**
     * @param array<string> $extensionFqns
     */
    public function __construct(array $extensionFqns, DefinitionDocumentor $definitionDocumentor)
    {
        $this->extensionFqns = $extensionFqns;
        $this->definitionDocumentor = $definitionDocumentor;
    }

    public function document(string $commandName=''): string
    {
        $docs = [
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

        $resolver = new Resolver();
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
