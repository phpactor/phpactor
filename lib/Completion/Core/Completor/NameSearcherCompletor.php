<?php

namespace Phpactor\Completion\Core\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Phpactor\Completion\Core\DocumentPrioritizer\DefaultResultPrioritizer;
use Phpactor\Completion\Core\DocumentPrioritizer\DocumentPrioritizer;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Name\NameUtil;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\NameSearcherType;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\TextDocument\TextDocumentUri;

abstract class NameSearcherCompletor
{
    private DocumentPrioritizer $prioritizer;

    public function __construct(protected NameSearcher $nameSearcher, DocumentPrioritizer $prioritizer = null)
    {
        $this->prioritizer = $prioritizer ?: new DefaultResultPrioritizer();
    }

    /**
     * @return Generator<Suggestion>
     * @param NameSearcherType::* $type
     */
    protected function completeName(
        string $name,
        ?TextDocumentUri $sourceUri = null,
        ?Node $node = null,
        ?string $type = null,
    ): Generator {
        $wasQualified = NameUtil::isQualified($name);
        $visitedChildSegments = [];
        foreach ($this->nameSearcher->search($name, $type) as $result) {
            // if the child segment relative to the search is not the last segment
            // then suggest the child segment only
            [$segment, $isLast] = NameUtil::childSegmentAtSearch($result->name(), $name);
            if ($wasQualified && $segment && false === $isLast) {
                yield from $this->suggestChildSegment($visitedChildSegments, $name, $result, $sourceUri, $segment);
                continue;
            }

            yield $this->createSuggestion(
                $name,
                $result,
                $wasQualified,
                $node,
                $this->createSuggestionOptions($result, $sourceUri, $node, $wasQualified),
            );
        }

        return true;
    }

    /**
     * @param array<string,string> $options
     */
    protected function createSuggestion(string $search, NameSearchResult $result, bool $wasQualified, ?Node $node = null, array $options = []): Suggestion
    {
        $options = array_merge($this->createSuggestionOptions($result, null, $node), $options);

        if ($node !== null && $wasQualified) {
            $name = NameUtil::relativeToSearch(ltrim($search, '\\'), $result->name()->__toString());
            /** @phpstan-ignore-next-line */
            return Suggestion::createWithOptions($name, $options);
        }

        /** @phpstan-ignore-next-line */
        return Suggestion::createWithOptions($result->name()->head(), $options);
    }

    /**
     * @return array<string,mixed>
     */
    protected function createSuggestionOptions(NameSearchResult $result, ?TextDocumentUri $sourceUri = null, ?Node $node = null, bool $wasFullyQualified = false): array
    {
        $options = [
            'short_description' => $result->name()->__toString(),
            'type' => $this->suggestionType($result),
            'class_import' => null,
            'name_import' => null,
            'priority' => $this->prioritizer->priority($result->uri(), $sourceUri)
        ];

        $options += ($node !== null && $node->parent instanceof ObjectCreationExpression) 
            ? ['snippet' => $result->name()->head() . '($1)$0'] 
            : [];

        if (!$wasFullyQualified && ($node === null || !($node->getParent() instanceof NamespaceUseClause))) {
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

        if ($result->type()->isConstant()) {
            return Suggestion::TYPE_CONSTANT;
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
    /**
     * @param array<string,bool> $visitedSegments
     * @return Generator<Suggestion>
     */
    private function suggestChildSegment(&$visitedSegments, string $search, NameSearchResult $result, ?TextDocumentUri $sourceUri, string $segment): Generator
    {
        if (isset($visitedSegments[$segment])) {
            return;
        }
        $visitedSegments[$segment] = true;

        yield Suggestion::createWithOptions($segment, [
            'short_description' => NameUtil::join(
                NameUtil::relativeToSearch($result->name()->__toString(), $search),
                $segment
            ),
            'type' => Suggestion::TYPE_MODULE,
            'priority' => $this->prioritizer->priority($result->uri(), $sourceUri)
        ]);
    }
}
