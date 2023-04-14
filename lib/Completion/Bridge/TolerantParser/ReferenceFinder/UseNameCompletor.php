<?php

namespace Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\CompletionContext;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Completor\NameSearcherCompletor;
use Phpactor\Name\NameUtil;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class UseNameCompletor extends NameSearcherCompletor implements TolerantCompletor
{
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $parent = $node->parent;

        if (!CompletionContext::useImport($node)) {
            return true;
        }

        $search = $node->getText();
        $search = NameUtil::toFullyQualified($search);
        yield from $this->completeName($search, $source->uri(), $node);

        return true;
    }
}
