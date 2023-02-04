<?php

namespace Phpactor\Completion\Bridge\TolerantParser\ReferenceFinder;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\Completion\Bridge\TolerantParser\CompletionContext;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Completor\NameSearcherCompletor;
use Phpactor\Name\NameUtil;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class AttributeCompletor extends NameSearcherCompletor implements TolerantCompletor
{
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (!CompletionContext::attribute($node)) {
            return true;
        }

        $name = $node->__toString();
        if ($node instanceof QualifiedName && NameUtil::isQualified($name)) {
            $name = NameUtil::toFullyQualfiied((string)$node->getResolvedName());
        }

        yield from $this->completeName($name, $source->uri(), $node);

        return true;
    }
}
