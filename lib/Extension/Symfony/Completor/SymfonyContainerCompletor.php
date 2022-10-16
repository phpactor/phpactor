<?php

namespace Phpactor\Extension\Symfony\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Range;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class SymfonyContainerCompletor implements TolerantCompletor
{
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (!$node instanceof CallExpression) {
            return;
        }

        $memberAccess = $node->callableExpression;

        if (!$memberAccess instanceof MemberAccessExpression) {
            return;
        }

        $expression = $memberAccess->dereferencableExpression;


        yield Suggestion::createWithOptions('foo', [
            'label' => 'asd',
            'short_description' => 'asd',
            'type' => Suggestion::TYPE_SNIPPET,
            'range' => Range::fromStartAndEnd(
                0,
                0
            )
        ]);
    }
}
