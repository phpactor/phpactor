<?php

namespace Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Phpactor\Completion\Bridge\TolerantParser\CompletionContext;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Completor\NameSearcherCompletor as CoreNameSearcherCompletor;
use Phpactor\Completion\Core\DocumentPrioritizer\DocumentPrioritizer;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;

class ExpressionNameCompletor extends CoreNameSearcherCompletor implements TolerantCompletor
{
    private ObjectFormatter $snippetFormatter;

    public function __construct(
        NameSearcher $nameSearcher,
        ObjectFormatter $snippetFormatter,
        DocumentPrioritizer $prioritizer = null
    ) {
        parent::__construct($nameSearcher, $prioritizer);

        $this->snippetFormatter = $snippetFormatter;
    }

    
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $parent = $node->parent;

        if (!CompletionContext::expression($node)) {
            return true;
        }

        $suggestions = $this->completeName($node, $source->uri(), $node);

        yield from $suggestions;

        return $suggestions->getReturn();
    }

    protected function createSuggestionOptions(
        NameSearchResult $result,
        ?TextDocumentUri $sourceUri = null,
        ?Node $node = null
    ): array {
        $suggestionOptions = parent::createSuggestionOptions($result, $sourceUri, $node);

        if ($this->isNonObjectCreationClassResult($result, $node) ||
            !$this->snippetFormatter->canFormat($result)) {
            return $suggestionOptions;
        }

        return array_merge(
            $suggestionOptions,
            [
                'snippet' => $this->snippetFormatter->format($result)
            ]
        );
    }

    private function isNonObjectCreationClassResult(NameSearchResult $result, ?Node $node): bool
    {
        if (!$result->type()->isClass()) {
            return false;
        }

        if ($node === null) {
            return true;
        }

        $parent = $node->getParent();

        if ($parent === null) {
            return true;
        }

        return !($parent instanceof ObjectCreationExpression);
    }
}
