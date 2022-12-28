<?php

namespace Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\CompletionContext;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\AttributeQualifier;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifiable;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;
use Phpactor\Completion\Core\DocumentPrioritizer\DocumentPrioritizer;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\NameSearcherType;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class AttributeCompletor implements TolerantCompletor, TolerantQualifiable
{
    public function __construct(
        private NameSearcher $nameSearcher,
        private DocumentPrioritizer $prioritizer
    ) {
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (!CompletionContext::attribute($node)) {
            return true;
        }

        $search = $node->getText();
        $type = NameSearcherType::CLASS_;

        foreach ($this->nameSearcher->search($search, $type) as $result) {
            if (!$result->type()->isClass()) {
                continue;
            }

            yield Suggestion::createWithOptions($result->name()->head(), [
                'type' => Suggestion::TYPE_CLASS,
                'priority' => $this->prioritizer->priority($result->uri(), $source->uri()),
                'short_description' => sprintf('%s %s', $type, $result->name()->__toString()),
                'class_import' => $result->name()->__toString(),
                'name_import' => $result->name()->__toString(),
            ]);
        }

        return true;
    }

    public function qualifier(): TolerantQualifier
    {
        return new AttributeQualifier();
    }
}
