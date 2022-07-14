<?php

namespace Phpactor\Extension\Debug\Model;

use Phpactor\MapResolver\Resolver;
use ReflectionClass;
use RuntimeException;
use Phpactor\Extension\Rpc\Handler;

class RpcCommandDocumentor implements Documentor
{
    /**
     * @var array<class-string>
     */
    private array $handlerFqns;

    private DefinitionDocumentor $definitionDocumentor;

    /**
     * @param array<class-string> $handlerFqns
     */
    public function __construct(
        array $handlerFqns,
        DefinitionDocumentor $definitionDocumentor
    ) {
        $this->handlerFqns = $handlerFqns;
        $this->definitionDocumentor = $definitionDocumentor;
    }


    public function document(string $commandName=''): string
    {
        $docs = [
            'Commands',
            '========',
            "\n",
            ".. This document is generated via the `$commandName` command",
            "\n",
            '.. contents::',
            '   :depth: 2',
            '   :backlinks: none',
            '   :local:',
            "\n",
        ];
        foreach ($this->handlerFqns as $handlerFqn) {
            $documentation = $this->documentHandler($handlerFqn);
            if (null === $documentation) {
                continue;
            }
            $docs[] = $documentation;
        }
        return implode("\n", $docs);
    }

    /**
     * @param class-string $handlerClass
     */
    private function documentHandler(string $handlerClass): ?string
    {
        $parts = explode('\\', $handlerClass);
        $documentedName = end($parts);

        /** @phpstan-ignore-next-line */
        if (false === $documentedName) {
            throw new RuntimeException(sprintf(
                'Invalid extension class name "%s"',
                $handlerClass
            ));
        }
        $help = [
            '.. _' . $documentedName . ':',
            "\n",
            $documentedName,
            str_repeat('-', mb_strlen($documentedName)),
            "\n",
        ];


        $class = new ReflectionClass($handlerClass);
        $handler = $class->newInstanceWithoutConstructor();
        assert($handler instanceof Handler);

        $resolver = new Resolver();
        $handler->configure($resolver);

        $hasDocumentation = false;
        foreach ($resolver->definitions() as $definition) {
            $help[] = $this->definitionDocumentor->document('RpcCommand_GlobalDefinitionHandler', $definition);
            $hasDocumentation = true;
        }

        if (!$hasDocumentation) {
            return null;
        }

        return implode("\n", $help);
    }
}
