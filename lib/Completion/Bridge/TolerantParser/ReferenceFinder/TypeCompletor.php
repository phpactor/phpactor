<?php

namespace Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\CompletionContext;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TypeSuggestionProvider;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class TypeCompletor implements TolerantCompletor
{
    private TypeSuggestionProvider $provider;

    public function __construct(TypeSuggestionProvider $provider)
    {
        $this->provider = $provider;
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (!CompletionContext::type($node)) {
            return true;
        }

        yield from $this->provider->provide($node, $node->getText());
    }
}
