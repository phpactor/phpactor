<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\Qualifier\AlwaysQualfifier;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\ClassName;

class DebugTolerantCompletor implements TolerantCompletor, TolerantQualifiable
{
    public function __construct(private readonly TolerantCompletor $innerCompletor)
    {
    }
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $generator = $this->innerCompletor->complete($node, $source, $offset);
        foreach ($generator as $result) {
            yield $result->withShortDescription(
                sprintf(
                    '[c: %s,n:%s<%s<%s] %s',
                    ClassName::fromString(get_class($this->innerCompletor))->short(),
                    ClassName::fromString(get_class($node))->short(),
                    $node->parent ? ClassName::fromString(get_class($node->parent))->short() : '-',
                    $node->parent->parent ? ClassName::fromString(get_class($node->parent->parent))->short() : '-',
                    $result->shortDescription(),
                )
            );
        }

        return $generator->getReturn();
    }

    public function qualifier(): TolerantQualifier
    {
        if ($this->innerCompletor instanceof TolerantQualifiable) {
            return $this->innerCompletor->qualifier();
        }

        return new AlwaysQualfifier();
    }
}
