<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\ResolvedName;
use Phpactor\Completion\Core\NameSuggestionProvider;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\ReferenceFinder\NameSearcher;

class TypeSuggestionProvider
{
    const BUILT_IN_TYPES = [
        'string',
        'float',
        'int',
        'bool',
        'callable',
        'array',
    ];
    private NameSearcher $nameSearcher;

    public function __construct(NameSearcher $nameSearcher)
    {
        $this->nameSearcher = $nameSearcher;
    }

    /**
     * @return Generator<Suggestion>
     */
    public function provide(Node $node, string $search): Generator
    {
        $search = $this->resolveSingleType($search);
        yield from $this->builtInTypes();
        yield from $this->nameImports($node);
        yield from $this->nameResults($search);
    }

    private function nameResults(string $search): Generator
    {
        if (!$search) {
            return;
        }

        foreach ($this->nameSearcher->search($search) as $result) {
            if (!$result->type()->isClass()) {
                continue;
            }

            yield Suggestion::createWithOptions($result->name()->head(), [
                'name_import' => $result->name()->__toString(),
                'type' => Suggestion::TYPE_CLASS,
            ]);
        }
    }

    private function nameImports(Node $node): Generator
    {
        $namespaceImports = $node->getImportTablesForCurrentScope()[0];
        
        foreach ($namespaceImports as $alias => $resolvedName) {
            yield Suggestion::createWithOptions(
                $alias,
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'short_description' => sprintf('%s', $resolvedName->__toString()),
                ]
            );
        }
    }

    private function builtInTypes(): Generator
    {
        foreach (self::BUILT_IN_TYPES as $type) {
            yield Suggestion::createWithOptions(
                $type,
                [
                    'type' => Suggestion::TYPE_KEYWORD,
                ]
            );
        }
    }

    private function resolveSingleType(string $search): string
    {
        $split = preg_split('{[|&<>]}', $search);
        if (!$split) {
            return '';
        }
        return $split[array_key_last($split)];
    }
}
