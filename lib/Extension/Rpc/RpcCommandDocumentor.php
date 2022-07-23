<?php

namespace Phpactor\Extension\Rpc;

use Phpactor\Extension\Debug\Model\DefinitionDocumentor;
use Phpactor\Extension\Debug\Model\Documentor;
use Phpactor\MapResolver\Resolver;
use RuntimeException;

class RpcCommandDocumentor implements Documentor
{
    private HandlerRegistry $handlerRegistry;

    private DefinitionDocumentor $definitionDocumentor;

    public function __construct(
        HandlerRegistry $handlerRegistry,
        DefinitionDocumentor $definitionDocumentor
    ) {
        $this->handlerRegistry = $handlerRegistry;
        $this->definitionDocumentor = $definitionDocumentor;
    }

    public function document(string $commandName=''): string
    {
        $docs = [
            'Legacy RPC Commands',
            '===================',
            "\n",
            ".. This document is generated via the `$commandName` command",
            "\n",
            '.. contents::',
            '   :depth: 2',
            '   :backlinks: none',
            '   :local:',
            "\n",
        ];
        foreach ($this->handlerRegistry->all() as $handler) {
            $documentation = $this->documentHandler($handler);
            if (null === $documentation) {
                continue;
            }
            $docs[] = $documentation;
        }
        return implode("\n", $docs);
    }

    private function documentHandler(Handler $handler): ?string
    {
        $handlerClass = get_class($handler);
        $parts = explode('\\', $handlerClass);
        $documentedName = '_RpcHandler_'.end($parts);

        /** @phpstan-ignore-next-line */
        if (false === $documentedName) {
            throw new RuntimeException(sprintf(
                'Invalid extension class name "%s"',
                $handlerClass
            ));
        }

        $help = [
            '.. ' . $documentedName . ':',
            "\n",
            $documentedName,
            str_repeat('-', mb_strlen($documentedName)),
            "\n",
        ];

        $resolver = new Resolver();
        $handler->configure($resolver);

        $hasDocumentation = false;
        foreach ($resolver->definitions() as $definition) {
            $help[] = $this->definitionDocumentor->document('RpcCommand_'.$handler->name(), $definition);
            $hasDocumentation = true;
        }

        if (!$hasDocumentation) {
            return null;
        }

        return implode("\n", $help);
    }
}
