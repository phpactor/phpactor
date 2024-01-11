<?php

declare(strict_types=1);

namespace Phpactor\Extension\Pest\Completion;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class PestCompletion implements TolerantCompletor
{
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        yield null;
    }
}
