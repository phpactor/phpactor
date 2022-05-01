<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifiable;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class DocblockTagCompletor implements TolerantCompletor, TolerantQualifiable
{
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
    }
}
