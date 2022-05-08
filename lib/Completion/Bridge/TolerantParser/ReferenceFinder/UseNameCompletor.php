<?php

namespace Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder;

use DTL\ArgumentResolver\ArgumentResolver;
use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Completor\NameSearcherCompletor as CoreNameSearcherCompletor;
use Phpactor\Completion\Core\DocumentPrioritizer\DocumentPrioritizer;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\Rpc\Request;
use Phpactor\LanguageServer\Core\CodeAction\AggregateCodeActionProvider;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\ReferenceFinder\Search\NameSearchResultType;
use Phpactor\ReferenceFinder\Tests\Unit\Search\NameSearchResultTest;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;

class UseNameCompletor implements TolerantCompletor
{
    private NameSearcher $nameSearcher;
    private DocumentPrioritizer $prioritizer;

    public function __construct(
        NameSearcher $nameSearcher,
        DocumentPrioritizer $prioritizer
    ) {
        $this->nameSearcher = $nameSearcher;
        $this->prioritizer = $prioritizer;
    }

    
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $parent = $node->parent;

        if (
            !$parent instanceof NamespaceUseClause
        ) {
            return true;
        }

        $search = $node->getText();
        foreach ($this->nameSearcher->search($search) as $result) {
            if (!$result->type()->isClass()) {
                continue;
            }

            yield Suggestion::createWithOptions($result->name()->__toString(), [
                'type' => Suggestion::TYPE_CLASS,
                'priority' => $this->prioritizer->priority($result->uri(), $source->uri())
            ]);
        }

        return true;
    }
}
