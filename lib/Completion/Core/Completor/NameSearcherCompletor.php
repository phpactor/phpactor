<?php

namespace Phpactor\Completion\Core\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Phpactor\Completion\Core\DocumentPrioritizer\DefaultResultPrioritizer;
use Phpactor\Completion\Core\DocumentPrioritizer\DocumentPrioritizer;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\TextDocument\TextDocumentUri;

abstract class NameSearcherCompletor
{
    protected NameSearcher $nameSearcher;

    private DocumentPrioritizer $prioritizer;

    public function __construct(NameSearcher $nameSearcher, DocumentPrioritizer $prioritizer = null)
    {
        $this->nameSearcher = $nameSearcher;
        $this->prioritizer = $prioritizer ?: new DefaultResultPrioritizer();
    }

    /**
     * @return Generator<Suggestion>
     */
    protected function completeName(string $name, ?TextDocumentUri $sourceUri = null, ?Node $node = null): Generator
    {
        foreach ($this->nameSearcher->search($name) as $result) {
            yield $this->createSuggestion(
                $result,
                $node,
                $this->createSuggestionOptions($result, $sourceUri, $node),
            );
        }

        return true;
    }

    protected function createSuggestion(NameSearchResult $result, ?Node $node = null, array $options = []): Suggestion
    {
        $options = array_merge($this->createSuggestionOptions($result, null, $node), $options);

        if ($node !== null && $node->getParent() instanceof NamespaceUseClause) {
            return Suggestion::createWithOptions($result->name()->__toString(), $options);
        }

        return Suggestion::createWithOptions($result->name()->head(), $options);
    }

    /**
     * @return array<string,mixed>
     */
    protected function createSuggestionOptions(NameSearchResult $result, ?TextDocumentUri $sourceUri = null, ?Node $node = null): array
    {
        $options = [
            'short_description' => $result->name()->__toString(),
            'type' => $this->suggestionType($result),
            'class_import' => null,
            'name_import' => null,
            'priority' => $this->prioritizer->priority($result->uri(), $sourceUri)
        ];

        if ($node === null || !($node->getParent() instanceof NamespaceUseClause)) {
            $options['class_import'] = $this->classImport($result);
            $options['name_import'] = $result->name()->__toString();
        }

        return $options;
    }

    protected function suggestionType(NameSearchResult $result): ?string
    {
        if ($result->type()->isClass()) {
            return Suggestion::TYPE_CLASS;
        }

        if ($result->type()->isFunction()) {
            return Suggestion::TYPE_FUNCTION;
        }

        return null;
    }

    protected function classImport(NameSearchResult $result): ?string
    {
        if ($result->type()->isClass()) {
            return $result->name()->__toString();
        }

        return null;
    }
}
