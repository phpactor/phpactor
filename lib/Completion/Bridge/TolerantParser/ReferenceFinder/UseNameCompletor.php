<?php

namespace Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\CompletionContext;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\DocumentPrioritizer\DocumentPrioritizer;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

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

        if (!CompletionContext::useImport($node)) {
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
