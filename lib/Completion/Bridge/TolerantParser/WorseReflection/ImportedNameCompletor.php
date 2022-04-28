<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\ResolvedName;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\ClassQualifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifiable;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class ImportedNameCompletor implements TolerantCompletor, TolerantQualifiable
{
    private ClassQualifier $qualifier;

    public function __construct(?ClassQualifier $qualifier = null)
    {
        $this->qualifier = $qualifier ?: new ClassQualifier(0);
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $namespaceImports = $node->getImportTablesForCurrentScope()[0];

        /** @var ResolvedName $resolvedName */
        foreach ($namespaceImports as $alias => $resolvedName) {
            yield Suggestion::createWithOptions(
                $alias,
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'short_description' => sprintf('%s', $resolvedName->__toString()),
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
