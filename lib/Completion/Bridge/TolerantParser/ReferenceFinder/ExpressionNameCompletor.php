<?php

namespace Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\QualifiedName;
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
    public function __construct(
        NameSearcher $nameSearcher,
        private ObjectFormatter $snippetFormatter,
        DocumentPrioritizer $prioritizer = null
    ) {
        parent::__construct($nameSearcher, $prioritizer);
    }


    // 1. If no namespace separator  - search by short name
    // 2. If namespace separator - resolve namespace, search by FQN
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $parent = $node->parent;

        if (!CompletionContext::expression($node)) {
            return true;
        }

        $name = $node->__toString();
        if ($node instanceof QualifiedName && str_contains($name, '\\')) {
            $name = '\\' . $node->getResolvedName()->__toString();
        }

        $suggestions = $this->completeName($name, $source->uri(), $node);

        yield from $suggestions;

        return $suggestions->getReturn();
    }

    protected function createSuggestionOptions(
        NameSearchResult $result,
        ?TextDocumentUri $sourceUri = null,
        ?Node $node = null,
        bool $wasAbsolute = false
    ): array {
        $suggestionOptions = parent::createSuggestionOptions($result, $sourceUri, $node, $wasAbsolute);

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
