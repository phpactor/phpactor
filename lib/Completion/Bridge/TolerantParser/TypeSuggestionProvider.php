<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Generator;
use Microsoft\PhpParser\Node;
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
        'void',
        'never',
    ];

    public function __construct(private readonly NameSearcher $nameSearcher)
    {
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

    /**
     * @return Generator<Suggestion>
     */
    private function nameResults(string $search): Generator
    {
        if (!$search) {
            return;
        }

        foreach ($this->nameSearcher->search($search) as $result) {
            if (!$result->type()->isClass()) {
                continue;
            }

            $wasAbsolute = str_starts_with($search, '\\');
            yield Suggestion::createWithOptions($result->name()->head(), [
                'short_description' => $result->name()->__toString(),
                'name_import' => $wasAbsolute ? null : $result->name()->__toString(),
                'type' => Suggestion::TYPE_CLASS,
                'priority' => Suggestion::PRIORITY_MEDIUM,
            ]);
        }
    }

    /**
     * @return Generator<Suggestion>
     */
    private function nameImports(Node $node): Generator
    {
        $namespaceImports = $node->getImportTablesForCurrentScope()[0];

        foreach ($namespaceImports as $alias => $resolvedName) {
            yield Suggestion::createWithOptions(
                $alias,
                [
                    'type' => Suggestion::TYPE_CLASS,
                    'short_description' => sprintf('%s', $resolvedName->__toString()),
                    'priority' => Suggestion::PRIORITY_HIGH,
                ]
            );
        }
    }

    /**
     * @return Generator<Suggestion>
     */
    private function builtInTypes(): Generator
    {
        foreach (self::BUILT_IN_TYPES as $type) {
            yield Suggestion::createWithOptions(
                $type,
                [
                    'type' => Suggestion::TYPE_KEYWORD,
                    'priority' => Suggestion::PRIORITY_HIGH,
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
