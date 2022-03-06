<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\ResolvedName;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\ClassQualifier;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\Qualifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifiable;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Reflector;

class WorseClassAliasCompletor implements TolerantCompletor, TolerantQualifiable
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var ClassQualifier
     */
    private $qualifier;

    public function __construct(Reflector $reflector, ?ClassQualifier $qualifier = null)
    {
        $this->reflector = $reflector;
        $this->qualifier = $qualifier ?: new ClassQualifier(0);
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $namespaceImports = $node->getImportTablesForCurrentScope()[0];

        /** @var ResolvedName $resolvedName */
        foreach ($namespaceImports as $alias => $resolvedName) {
            $parts = $resolvedName->getNameParts();
            if (empty($parts)) {
                continue;
            }

            $lastPart = array_pop($parts);

            if ($alias === $lastPart) {
                continue;
            }

            yield Suggestion::createWithOptions(
                $alias,
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'short_description' => sprintf('Alias for: %s', (string) $resolvedName)
                ]
            );
        }

        return true;
    }

    public function qualifier(): TolerantQualifier
    {
        return $this->qualifier;
    }
}
